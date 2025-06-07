<?php
// Disable any output buffering
while (ob_get_level()) {
    ob_end_clean();
}

// Set proper headers for JSON API
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

// Error handling for API
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors in output
ini_set('log_errors', 1); // Log errors instead

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include essential files only
require_once '../includes/bootstrap.php';
require_once '../includes/email_functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Get JSON input
$json_input = file_get_contents('php://input');
$input = json_decode($json_input, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
    exit;
}

$action = $input['action'] ?? '';

try {
    switch ($action) {
        case 'create_order':
            handleCreateOrder($input, $user_id);
            break;

        case 'capture_order':
            handleCaptureOrder($input, $user_id);
            break;

        case 'complete_payment':
            handleCompletePayment($input, $user_id);
            break;

        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    error_log("Process.php error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

function handleCreateOrder($input, $user_id)
{
    global $conn;

    // Get cart data
    $cart_data = getCartItems();
    $cart_items = $cart_data['items'];
    $cart_total = $cart_data['total'];

    if (empty($cart_items)) {
        throw new Exception('Cart is empty');
    }

    // Get checkout data
    $checkout_data = $input['checkout_data'] ?? [];

    // Validate checkout data
    if (empty($checkout_data['shipping_address'])) {
        throw new Exception('Shipping address is required');
    }

    // Calculate tax
    $tax_amount = calculateTaxAmount($cart_items);
    $shipping_cost = 0.00; // Free shipping
    $grand_total = $cart_total + $tax_amount + $shipping_cost;

    // Save addresses if needed
    $shipping_address_data = saveAddressIfNeeded($checkout_data['shipping_address'], $user_id);
    $billing_address_data = resolveBillingAddress($checkout_data['billing_address'], $shipping_address_data, $user_id);

    // Generate invoice number
    $invoice_number = generateInvoiceNumber();

    // Create local order record
    $conn->begin_transaction();

    try {
        // Insert order
        $stmt = $conn->prepare("
            INSERT INTO orders (invoice_number, user_id, total_amount, tax_amount, tax_rate, 
                              payment_status, payment_method, shipping_address, billing_address, order_date) 
            VALUES (?, ?, ?, ?, ?, 'pending', 'paypal', ?, ?, NOW())
        ");

        $tax_rate = 0; // Tax rate is calculated dynamically based on address and product type
        $shipping_json = json_encode($shipping_address_data);
        $billing_json = json_encode($billing_address_data);

        $stmt->bind_param(
            "siddsss",
            $invoice_number,
            $user_id,
            $grand_total,
            $tax_amount,
            $tax_rate,
            $shipping_json,
            $billing_json
        );
        $stmt->execute();
        $local_order_id = $conn->insert_id;

        // Insert order items
        $stmt = $conn->prepare("
            INSERT INTO order_items (order_id, product_id, quantity, price, subtotal, tax_amount) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        foreach ($cart_items as $item) {
            $item_subtotal = $item['price'] * $item['quantity'];
            $item_tax = calculateItemTax($item, $tax_rate);

            $stmt->bind_param(
                "iiiddd",
                $local_order_id,
                $item['id'],
                $item['quantity'],
                $item['price'],
                $item_subtotal,
                $item_tax
            );
            $stmt->execute();
        }

        // Create PayPal order
        $paypal = new PayPal();
        $paypal_order_data = [
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'reference_id' => $invoice_number,
                    'amount' => [
                        'currency_code' => 'USD',
                        'value' => number_format($grand_total, 2, '.', ''),
                        'breakdown' => [
                            'item_total' => [
                                'currency_code' => 'USD',
                                'value' => number_format($cart_total, 2, '.', '')
                            ],
                            'tax_total' => [
                                'currency_code' => 'USD',
                                'value' => number_format($tax_amount, 2, '.', '')
                            ],
                            'shipping' => [
                                'currency_code' => 'USD',
                                'value' => '0.00'
                            ]
                        ]
                    ],
                    'items' => array_map(function ($item) {
                        return [
                            'name' => $item['name'],
                            'unit_amount' => [
                                'currency_code' => 'USD',
                                'value' => number_format($item['price'], 2, '.', '')
                            ],
                            'quantity' => (string)$item['quantity'],
                            'category' => 'DIGITAL_GOODS'
                        ];
                    }, $cart_items),
                    'shipping' => [
                        'name' => [
                            'full_name' => $shipping_address_data['name'] ?? 'Customer'
                        ],
                        'address' => [
                            'address_line_1' => $shipping_address_data['street'],
                            'admin_area_2' => $shipping_address_data['city'],
                            'admin_area_1' => $shipping_address_data['state'],
                            'postal_code' => $shipping_address_data['postal_code'],
                            'country_code' => $shipping_address_data['country']
                        ]
                    ]
                ]
            ],
            'application_context' => [
                'brand_name' => 'Ryvah Commerce',
                'landing_page' => 'BILLING',
                'user_action' => 'PAY_NOW',
                'return_url' => 'http://ryvahcommerce.com/checkout/success.php',
                'cancel_url' => 'http://ryvahcommerce.com/checkout/cancel.php'
            ]
        ];

        $paypal_order = $paypal->createOrder($paypal_order_data);

        // Update order with PayPal order ID
        $stmt = $conn->prepare("UPDATE orders SET paypal_order_id = ? WHERE id = ?");
        $stmt->bind_param("si", $paypal_order['id'], $local_order_id);
        $stmt->execute();

        // Store local order ID in session for later use
        $_SESSION['pending_order_id'] = $local_order_id;

        $conn->commit();

        echo json_encode([
            'success' => true,
            'order_id' => $paypal_order['id'],
            'local_order_id' => $local_order_id
        ]);
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
}

function handleCaptureOrder($input, $user_id)
{
    global $conn;

    $paypal_order_id = $input['order_id'] ?? '';

    if (empty($paypal_order_id)) {
        throw new Exception('PayPal order ID is required');
    }

    // Get local order
    $stmt = $conn->prepare("SELECT * FROM orders WHERE paypal_order_id = ? AND user_id = ?");
    $stmt->bind_param("si", $paypal_order_id, $user_id);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();

    if (!$order) {
        throw new Exception('Order not found');
    }

    // Capture payment with PayPal
    $paypal = new PayPal();
    $capture_result = $paypal->captureOrder($paypal_order_id);

    // Check if payment was successful
    $payment_status = 'failed';
    if (isset($capture_result['status']) && $capture_result['status'] === 'COMPLETED') {
        $payment_status = 'completed';
    }

    $conn->begin_transaction();

    try {
        // Update order status
        $stmt = $conn->prepare("UPDATE orders SET payment_status = ?, updated_at = NOW() WHERE id = ?");
        $stmt->bind_param("si", $payment_status, $order['id']);
        $stmt->execute();

        // Add to order status history
        $stmt = $conn->prepare("INSERT INTO order_status_history (order_id, status, notes) VALUES (?, ?, ?)");
        $notes = $payment_status === 'completed' ? 'Payment completed successfully' : 'Payment failed';
        $stmt->bind_param("iss", $order['id'], $payment_status, $notes);
        $stmt->execute();

        if ($payment_status === 'completed') {
            // Clear the cart
            clearUserCart($user_id);

            // Create download records for digital products
            createDigitalDownloads($order['id'], $user_id);

            // Send order notification email to admin (only for non-ebook orders)
            sendOrderNotificationEmail($order['id']);
        }

        $conn->commit();

        echo json_encode([
            'success' => $payment_status === 'completed',
            'message' => $payment_status === 'completed' ? 'Payment completed successfully' : 'Payment failed',
            'local_order_id' => $order['id'],
            'payment_status' => $payment_status
        ]);
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
}

function handleCompletePayment($input, $user_id)
{
    global $conn;

    // Get cart data
    $cart_data = getCartItems();
    $cart_items = $cart_data['items'];
    $cart_total = $cart_data['total'];

    if (empty($cart_items)) {
        throw new Exception('Cart is empty');
    }

    // Get checkout data
    $checkout_data = $input['checkout_data'] ?? [];
    $paypal_details = $input['paypal_details'] ?? [];

    // Validate checkout data
    if (empty($checkout_data['shipping_address'])) {
        throw new Exception('Shipping address is required');
    }

    if (empty($paypal_details['id'])) {
        throw new Exception('PayPal payment details are required');
    }

    // Calculate tax
    $tax_amount = calculateTaxAmount($cart_items);
    $shipping_cost = 0.00; // Free shipping
    $grand_total = $cart_total + $tax_amount + $shipping_cost;

    // Save addresses if needed
    $shipping_address_data = saveAddressIfNeeded($checkout_data['shipping_address'], $user_id);
    $billing_address_data = resolveBillingAddress($checkout_data['billing_address'], $shipping_address_data, $user_id);

    // Generate invoice number
    $invoice_number = generateInvoiceNumber();

    // Create local order record
    $conn->begin_transaction();

    try {
        // Insert order
        $stmt = $conn->prepare("
            INSERT INTO orders (invoice_number, user_id, total_amount, tax_amount, tax_rate, 
                              payment_status, payment_method, paypal_order_id, shipping_address, billing_address, order_date) 
            VALUES (?, ?, ?, ?, ?, 'completed', 'paypal', ?, ?, ?, NOW())
        ");

        $tax_rate = 7.75; // Default tax rate
        $shipping_json = json_encode($shipping_address_data);
        $billing_json = json_encode($billing_address_data);
        $paypal_order_id = $paypal_details['id'];

        $stmt->bind_param(
            "siddssss",
            $invoice_number,
            $user_id,
            $grand_total,
            $tax_amount,
            $tax_rate,
            $paypal_order_id,
            $shipping_json,
            $billing_json
        );
        $stmt->execute();
        $local_order_id = $conn->insert_id;

        // Insert order items
        $stmt = $conn->prepare("
            INSERT INTO order_items (order_id, product_id, quantity, price, subtotal, tax_amount) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        foreach ($cart_items as $item) {
            $item_subtotal = $item['price'] * $item['quantity'];
            $item_tax = calculateItemTax($item, $tax_rate);

            $stmt->bind_param(
                "iiiddd",
                $local_order_id,
                $item['id'],
                $item['quantity'],
                $item['price'],
                $item_subtotal,
                $item_tax
            );
            $stmt->execute();
        }

        // Add to order status history
        $stmt = $conn->prepare("INSERT INTO order_status_history (order_id, status, notes) VALUES (?, 'completed', 'Payment completed via PayPal')");
        $stmt->bind_param("i", $local_order_id);
        $stmt->execute();

        // Clear the cart
        clearUserCart($user_id);

        // Create download records for digital products
        createDigitalDownloads($local_order_id, $user_id);

        // Send order notification email to admin (only for non-ebook orders)
        sendOrderNotificationEmail($local_order_id);

        $conn->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Payment processed successfully',
            'local_order_id' => $local_order_id,
            'invoice_number' => $invoice_number
        ]);
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
}

function calculateTaxAmount($cart_items)
{
    global $conn;

    // Get tax rates
    $tax_rates = [];
    $stmt = $conn->prepare("SELECT product_type, tax_rate FROM tax_settings WHERE is_active = 1");
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $tax_rates[$row['product_type']] = $row['tax_rate'];
    }

    $tax_amount = 0;
    foreach ($cart_items as $item) {
        if (isset($tax_rates[$item['type']])) {
            $item_tax = ($item['price'] * $item['quantity']) * ($tax_rates[$item['type']] / 100);
            $tax_amount += $item_tax;
        }
    }

    return $tax_amount;
}

function calculateItemTax($item, $default_tax_rate)
{
    global $conn;

    $stmt = $conn->prepare("SELECT tax_rate FROM tax_settings WHERE product_type = ? AND is_active = 1");
    $stmt->bind_param("s", $item['type']);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    $tax_rate = $result ? $result['tax_rate'] : $default_tax_rate;
    return ($item['price'] * $item['quantity']) * ($tax_rate / 100);
}

function saveAddressIfNeeded($address_data, $user_id)
{
    global $conn;

    if ($address_data['type'] === 'new') {
        $address_info = [
            'label' => $address_data['label'],
            'street' => $address_data['street'],
            'city' => $address_data['city'],
            'state' => $address_data['state'],
            'postal_code' => $address_data['postal_code'],
            'country' => $address_data['country']
        ];

        // Save to database if requested
        if ($address_data['save_address'] ?? false) {
            $stmt = $conn->prepare("
                INSERT INTO addresses (user_id, label, street, city, state, postal_code, country, is_default) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 0)
            ");
            $stmt->bind_param(
                "issssss",
                $user_id,
                $address_info['label'],
                $address_info['street'],
                $address_info['city'],
                $address_info['state'],
                $address_info['postal_code'],
                $address_info['country']
            );
            $stmt->execute();
        }

        return $address_info;
    } else {
        // Get existing address
        $stmt = $conn->prepare("SELECT * FROM addresses WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $address_data['id'], $user_id);
        $stmt->execute();
        $address = $stmt->get_result()->fetch_assoc();

        if (!$address) {
            throw new Exception('Selected address not found');
        }

        return [
            'label' => $address['label'],
            'street' => $address['street'],
            'city' => $address['city'],
            'state' => $address['state'],
            'postal_code' => $address['postal_code'],
            'country' => $address['country']
        ];
    }
}

function resolveBillingAddress($billing_data, $shipping_data, $user_id)
{
    if ($billing_data['type'] === 'same_as_shipping') {
        return $shipping_data;
    } else {
        return [
            'label' => $billing_data['label'],
            'street' => $billing_data['street'],
            'city' => $billing_data['city'],
            'state' => $billing_data['state'],
            'postal_code' => $billing_data['postal_code'],
            'country' => $billing_data['country']
        ];
    }
}

function generateInvoiceNumber()
{
    return 'RYV-' . date('Y') . '-' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
}

function clearUserCart($user_id)
{
    global $conn;

    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
}

function createDigitalDownloads($order_id, $user_id)
{
    global $conn;

    // Get order items that are digital products
    $stmt = $conn->prepare("
        SELECT oi.*, p.type, p.filepath 
        FROM order_items oi 
        JOIN products p ON oi.product_id = p.id 
        WHERE oi.order_id = ? AND p.type IN ('ebook', 'book')
    ");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $digital_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    foreach ($digital_items as $item) {
        // Generate download token
        $download_token = bin2hex(random_bytes(32));

        // Set expiration (30 days from now)
        $expires_at = date('Y-m-d H:i:s', strtotime('+30 days'));

        // Insert download record
        $stmt = $conn->prepare("
            INSERT INTO ebook_downloads (user_id, order_id, product_id, download_token, max_downloads, expires_at) 
            VALUES (?, ?, ?, ?, 5, ?)
        ");
        $stmt->bind_param("iiiss", $user_id, $order_id, $item['product_id'], $download_token, $expires_at);
        $stmt->execute();

        // Also add to user purchases for tracking
        $stmt = $conn->prepare("
            INSERT INTO user_purchases (user_id, product_id, order_id, purchase_date) 
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->bind_param("iii", $user_id, $item['product_id'], $order_id);
        $stmt->execute();
    }
}