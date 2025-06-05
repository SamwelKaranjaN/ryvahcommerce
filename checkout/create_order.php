<?php

ob_start();

// Clear any existing output
if (ob_get_level()) {
    ob_end_clean();
}
ob_start();

// Prevent cart.php POST handler from interfering
define('PAYPAL_ORDER_PROCESSING', true);

require_once '../includes/bootstrap.php';
require_once '../includes/cart.php';

// Check if vendor autoload exists
$autoload_path = __DIR__ . '../vendor/autoload.php';
if (!file_exists($autoload_path)) {
    ob_clean();
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['error' => 'PayPal SDK not installed. Please run composer install']);
    exit;
}

require_once $autoload_path;

// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Clear any previous output and set proper headers
ob_clean();
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// PayPal Configuration
define('PAYPAL_CLIENT_ID', 'ARb4izn3jwTWc2j2x6UDmompOiO2Uq3HQKodHTR3Y6UKUN61daJD09G8JVrx6UWz11-CL2fcty8UJ2CJ');
define('PAYPAL_CLIENT_SECRET', 'EDUXnHsBZ0L7gUXjdpI9l7oFnCTIftl0UORyDtsXIZqBb7reoiNhGlEI4U2Qv_lKsI_oaK1Z3eVhzOyW');

// Check if PayPal SDK is available
if (!class_exists('PayPalCheckoutSdk\Core\PayPalHttpClient')) {
    error_log("PayPal SDK not found. Please run 'composer install'.");
    http_response_code(500);
    echo json_encode(['error' => 'Payment system not properly configured']);
    exit;
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Get POST data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

error_log("PayPal Order Creation - Received data: " . print_r($data, true));

if (!isset($data['total']) || $data['total'] <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid total amount']);
    exit;
}

$total = $data['total'];
$address_id = $data['address_id'] ?? null;

// Validate and get shipping address if provided
$shipping_address = null;
if ($address_id) {
    $stmt = $conn->prepare("SELECT * FROM addresses WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $address_id, $_SESSION['user_id']);
    $stmt->execute();
    $shipping_address = $stmt->get_result()->fetch_assoc();

    if (!$shipping_address) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid shipping address']);
        exit;
    }
}

try {
    // Initialize PayPal client
    $environment = new \PayPalCheckoutSdk\Core\SandboxEnvironment(PAYPAL_CLIENT_ID, PAYPAL_CLIENT_SECRET);
    $client = new \PayPalCheckoutSdk\Core\PayPalHttpClient($environment);
    error_log("PayPal - Client initialized successfully");

    // Create order request
    $request = new \PayPalCheckoutSdk\Orders\OrdersCreateRequest();
    $request->prefer('return=representation');

    // Format total amount
    $formattedTotal = number_format($total, 2, '.', '');
    error_log("PayPal - Formatted total amount: " . $formattedTotal);

    // Get cart data
    $cart_data = getCartItems();
    $cart_items = $cart_data['items'];

    // Create items array for PayPal
    $paypal_items = [];
    $subtotal = 0;

    foreach ($cart_items as $item) {
        $item_total = $item['price'] * $item['quantity'];
        $subtotal += $item_total;

        $paypal_items[] = [
            'name' => $item['name'],
            'description' => substr($item['name'], 0, 127),
            'unit_amount' => [
                'currency_code' => 'USD',
                'value' => number_format($item['price'], 2, '.', '')
            ],
            'quantity' => (string)$item['quantity'],
            'category' => 'DIGITAL_GOODS'
        ];
    }

    // Calculate tax
    $tax_rates = [];
    $tax_amount = 0;

    $stmt = $conn->prepare("SELECT product_type, tax_rate FROM tax_settings WHERE is_active = 1");
    if (!$stmt->execute()) {
        throw new Exception("Database error fetching tax rates: " . $stmt->error);
    }
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $tax_rates[$row['product_type']] = $row['tax_rate'];
    }

    foreach ($cart_items as $item) {
        if (isset($tax_rates[$item['type']])) {
            $item_tax = ($item['price'] * $item['quantity']) * ($tax_rates[$item['type']] / 100);
            $tax_amount += $item_tax;
        }
    }

    $request->body = [
        'intent' => 'CAPTURE',
        'application_context' => [
            'return_url' => 'https://' . $_SERVER['HTTP_HOST'] . '/ryvahcommerce/checkout/success.php',
            'cancel_url' => 'https://' . $_SERVER['HTTP_HOST'] . '/ryvahcommerce/checkout/index.php',
            'brand_name' => 'Ryvah Commerce',
            'locale' => 'en-US',
            'landing_page' => 'BILLING',
            'shipping_preference' => 'NO_SHIPPING',
            'user_action' => 'PAY_NOW'
        ],
        'purchase_units' => [
            [
                'reference_id' => 'RYVAH_' . $_SESSION['user_id'] . '_' . time(),
                'description' => 'Order from Ryvah Commerce - ' . count($cart_items) . ' item(s)',
                'amount' => [
                    'currency_code' => 'USD',
                    'value' => $formattedTotal,
                    'breakdown' => [
                        'item_total' => [
                            'currency_code' => 'USD',
                            'value' => number_format($subtotal, 2, '.', '')
                        ],
                        'tax_total' => [
                            'currency_code' => 'USD',
                            'value' => number_format($tax_amount, 2, '.', '')
                        ]
                    ]
                ],
                'items' => $paypal_items
            ]
        ]
    ];

    error_log("PayPal - Order Creation Request: " . json_encode($request->body));

    // Call PayPal API
    $response = $client->execute($request);
    error_log("PayPal - API call successful");

    if (!isset($response->result->id)) {
        throw new Exception('PayPal response missing order ID');
    }

    // Generate invoice number
    $invoice_number = 'INV-' . date('Ymd') . '-' . str_pad($_SESSION['user_id'], 4, '0', STR_PAD_LEFT) . '-' . rand(1000, 9999);

    // Prepare shipping address data for storage
    $shipping_address_json = null;
    if ($shipping_address) {
        $shipping_address_json = json_encode([
            'label' => $shipping_address['label'],
            'street' => $shipping_address['street'],
            'city' => $shipping_address['city'],
            'state' => $shipping_address['state'],
            'postal_code' => $shipping_address['postal_code'],
            'country' => $shipping_address['country']
        ]);
    }

    // Insert order with shipping address
    $stmt = $conn->prepare("INSERT INTO orders (invoice_number, user_id, total_amount, tax_amount, payment_status, paypal_order_id, shipping_address) VALUES (?, ?, ?, ?, 'pending', ?, ?)");
    $stmt->bind_param("siddss", $invoice_number, $_SESSION['user_id'], $total, $tax_amount, $response->result->id, $shipping_address_json);

    if (!$stmt->execute()) {
        throw new Exception("Database error: " . $stmt->error);
    }

    $order_id = $conn->insert_id;
    error_log("PayPal - Order created in database with ID: " . $order_id);

    // Insert order items
    $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price, subtotal, tax_amount) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($cart_items as $item) {
        $item_subtotal = $item['price'] * $item['quantity'];
        $item_tax_amount = isset($tax_rates[$item['type']]) ? $item_subtotal * ($tax_rates[$item['type']] / 100) : 0;

        $stmt->bind_param("iiiddd", $order_id, $item['id'], $item['quantity'], $item['price'], $item_subtotal, $item_tax_amount);

        if (!$stmt->execute()) {
            throw new Exception("Error inserting order item: " . $stmt->error);
        }
    }

    // Return PayPal order ID
    ob_clean(); // Clear any unexpected output
    echo json_encode(['id' => $response->result->id]);
} catch (Exception $e) {
    error_log("PayPal Order Creation Error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());

    ob_clean(); // Clear any unexpected output
    http_response_code(500);

    // Provide more detailed error information for debugging
    $error_response = [
        'error' => 'An error occurred while creating the order',
        'message' => $e->getMessage()
    ];

    // Add more context for common issues
    if (strpos($e->getMessage(), 'PayPal SDK') !== false) {
        $error_response['details'] = 'PayPal SDK not found. Please install composer dependencies.';
    } elseif (strpos($e->getMessage(), 'Database') !== false) {
        $error_response['details'] = 'Database connection issue.';
    } elseif (strpos($e->getMessage(), 'CURL') !== false || strpos($e->getMessage(), 'HTTP') !== false) {
        $error_response['details'] = 'Network connectivity issue with PayPal API.';
    } else {
        $error_response['details'] = 'Check server logs for more information.';
    }

    echo json_encode($error_response);
}

ob_end_flush();