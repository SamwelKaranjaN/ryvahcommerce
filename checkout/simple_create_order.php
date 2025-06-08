<?php

/**
 * Enhanced PayPal Order Creation
 * Ryvah Commerce - Secure order creation with comprehensive validation
 */

// Prevent cart interference
define('PAYPAL_ORDER_PROCESSING', true);

// Start output buffering for clean JSON response
ob_start();

// Set headers early for security and API responses
header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-cache, must-revalidate, no-store');
header('Pragma: no-cache');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// Disable HTML error output for clean JSON
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// Include required files
require_once '../includes/bootstrap.php';
require_once '../includes/cart.php';
require_once '../includes/paypal_config.php';
require_once '../includes/ssl_fix.php';
require_once '../vendor/autoload.php';

/**
 * Send JSON response and exit
 */
function sendResponse($data, $httpCode = 200)
{
    ob_clean();
    http_response_code($httpCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    ob_end_flush();
    exit;
}

/**
 * Handle errors and send appropriate response
 */
function handleApiError($message, $context = [], $httpCode = 500)
{
    logPayPalError('Order creation error: ' . $message, $context);
    sendResponse([
        'error' => true,
        'message' => $message,
        'environment' => PAYPAL_ENVIRONMENT,
        'timestamp' => time()
    ], $httpCode);
}

/**
 * Validate request method and headers
 */
function validateRequest()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        handleApiError('Invalid request method', [], 405);
    }

    if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest') {
        handleApiError('Invalid request type', [], 400);
    }

    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    if (strpos($contentType, 'application/json') === false) {
        handleApiError('Invalid content type', [], 400);
    }
}

/**
 * Validate user session
 */
function validateSession()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['user_id']) || !is_numeric($_SESSION['user_id']) || $_SESSION['user_id'] <= 0) {
        handleApiError('User not authenticated', [], 401);
    }

    return intval($_SESSION['user_id']);
}

/**
 * Get and validate request data
 */
function getRequestData()
{
    $input = file_get_contents('php://input');
    if (empty($input)) {
        handleApiError('Empty request body', [], 400);
    }

    $data = json_decode($input, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        handleApiError('Invalid JSON data: ' . json_last_error_msg(), [], 400);
    }

    // Validate required fields
    $requiredFields = ['total', 'address_id', 'csrf_token'];
    foreach ($requiredFields as $field) {
        if (!isset($data[$field]) || $data[$field] === '') {
            handleApiError("Missing required field: {$field}", [], 400);
        }
    }

    return $data;
}

/**
 * Validate CSRF token
 */
function validateCSRF($token)
{
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        handleApiError('Invalid CSRF token', [], 403);
    }
}

/**
 * Validate and fetch shipping address
 */
function validateShippingAddress($addressId, $userId)
{
    global $conn;

    if (!is_numeric($addressId) || $addressId <= 0) {
        handleApiError('Invalid address ID', [], 400);
    }

    $stmt = $conn->prepare("SELECT id, label, street, city, state, postal_code, country FROM addresses WHERE id = ? AND user_id = ?");
    if (!$stmt) {
        handleApiError('Database prepare error: ' . $conn->error, [], 500);
    }

    $stmt->bind_param("ii", $addressId, $userId);
    if (!$stmt->execute()) {
        handleApiError('Database execute error: ' . $stmt->error, [], 500);
    }

    $address = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$address) {
        handleApiError('Shipping address not found or unauthorized', [], 404);
    }

    // Validate address completeness
    $requiredAddressFields = ['street', 'city', 'postal_code', 'country'];
    foreach ($requiredAddressFields as $field) {
        if (empty($address[$field])) {
            handleApiError("Incomplete shipping address: missing {$field}", [], 400);
        }
    }

    return $address;
}

/**
 * Validate cart and calculate totals
 */
function validateCartAndCalculateTotals($userId, $address, $requestData)
{
    global $conn;

    // Include shipping calculator
    require_once 'shipping_calculator.php';

    try {
        // Fetch cart items
        $cart_data = getCartItems();
        $cart_items = $cart_data['items'] ?? [];

        if (empty($cart_items)) {
            handleApiError('Cart is empty', [], 400);
        }

        $subtotal = 0;
        $validated_items = [];
        $order_items = [];

        foreach ($cart_items as $item) {
            // Validate item structure
            if (
                !isset($item['id'], $item['price'], $item['quantity'], $item['name']) ||
                !is_numeric($item['id']) || !is_numeric($item['price']) || !is_numeric($item['quantity'])
            ) {
                logPayPalError('Invalid cart item structure', $item);
                continue;
            }

            $itemId = intval($item['id']);
            $quantity = intval($item['quantity']);
            $price = floatval($item['price']);

            if ($quantity <= 0 || $price < 0) {
                logPayPalError('Invalid item quantity or price', $item);
                continue;
            }

            // Verify product exists and is available
            $stmt = $conn->prepare("SELECT id, name, price, stock_quantity, type FROM products WHERE id = ?");
            $stmt->bind_param("i", $itemId);
            $stmt->execute();
            $product = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$product) {
                logPayPalError('Product not found', ['product_id' => $itemId]);
                continue;
            }

            // Check stock availability
            if ($product['stock_quantity'] < $quantity) {
                handleApiError("Insufficient stock for product: {$product['name']}", [], 400);
            }

            // Verify price hasn't changed significantly
            if (abs($product['price'] - $price) > 0.01) {
                logPayPalError('Product price changed', [
                    'product_id' => $itemId,
                    'cart_price' => $price,
                    'current_price' => $product['price']
                ]);
                $price = $product['price']; // Use current price
            }

            $item_total = $price * $quantity;
            $subtotal += $item_total;

            $validated_items[] = [
                'id' => $itemId,
                'name' => $product['name'],
                'price' => $price,
                'quantity' => $quantity,
                'type' => $product['type'],
                'total' => $item_total
            ];

            // Prepare PayPal order item
            $order_items[] = [
                'name' => substr($product['name'], 0, 127), // PayPal limit
                'description' => 'Digital product from ' . SITE_NAME,
                'unit_amount' => [
                    'currency_code' => $requestData['currency'] ?? PAYPAL_DEFAULT_CURRENCY,
                    'value' => number_format($price, 2, '.', '')
                ],
                'quantity' => (string)$quantity,
                'category' => 'DIGITAL_GOODS'
            ];
        }

        if (empty($validated_items)) {
            handleApiError('No valid items in cart', [], 400);
        }

        // Calculate tax
        $tax_amount = 0;
        foreach ($validated_items as $item) {
            $tax_rate = getTaxRate($address['state'], $address['country'], $item['type']);
            $tax_amount += $item['total'] * $tax_rate;
        }

        // Calculate shipping (after tax as per requirement)
        $shipping_result = calculateOrderShipping($validated_items, $address);
        $shipping_amount = $shipping_result['total_shipping'];

        $total = $subtotal + $tax_amount + $shipping_amount;

        // Validate amounts against request
        if (isset($requestData['subtotal'])) {
            $expected_subtotal = floatval($requestData['subtotal']);
            if (abs($subtotal - $expected_subtotal) > 0.01) {
                handleApiError('Subtotal mismatch between request and calculation', [
                    'calculated' => $subtotal,
                    'expected' => $expected_subtotal
                ], 400);
            }
        }

        if (isset($requestData['tax_amount'])) {
            $expected_tax = floatval($requestData['tax_amount']);
            if (abs($tax_amount - $expected_tax) > 0.01) {
                handleApiError('Tax amount mismatch between request and calculation', [
                    'calculated' => $tax_amount,
                    'expected' => $expected_tax
                ], 400);
            }
        }

        if (isset($requestData['shipping_amount'])) {
            $expected_shipping = floatval($requestData['shipping_amount']);
            if (abs($shipping_amount - $expected_shipping) > 0.01) {
                handleApiError('Shipping amount mismatch between request and calculation', [
                    'calculated' => $shipping_amount,
                    'expected' => $expected_shipping
                ], 400);
            }
        }

        $expected_total = floatval($requestData['total']);
        if (abs($total - $expected_total) > 0.01) {
            handleApiError('Total amount mismatch between request and calculation', [
                'calculated' => $total,
                'expected' => $expected_total,
                'breakdown' => [
                    'subtotal' => $subtotal,
                    'tax' => $tax_amount,
                    'shipping' => $shipping_amount
                ]
            ], 400);
        }

        // Validate payment amount limits
        if (!validatePaymentAmount($total)) {
            handleApiError('Order total is outside acceptable payment range', [
                'total' => $total,
                'min' => PAYPAL_MIN_AMOUNT,
                'max' => PAYPAL_MAX_AMOUNT
            ], 400);
        }

        return [
            'items' => $validated_items,
            'order_items' => $order_items,
            'subtotal' => $subtotal,
            'tax_amount' => $tax_amount,
            'shipping_amount' => $shipping_amount,
            'shipping_breakdown' => $shipping_result['breakdown'],
            'total' => $total
        ];
    } catch (Exception $e) {
        handleApiError('Error validating cart: ' . $e->getMessage(), [], 500);
    }
}

/**
 * Create PayPal order
 */
function createPayPalOrder($orderData, $address, $currency)
{
    try {
        if (!validatePayPalConfig()) {
            handleApiError('PayPal configuration is invalid', [], 500);
        }

        if (!isPayPalSDKAvailable()) {
            handleApiError('PayPal SDK is not available', [], 500);
        }

        // Validate network connectivity before attempting payment
        if (!validateNetworkConnectivity()) {
            handleApiError('Cannot connect to payment system. Please check your internet connection and try again.', [
                'network_test' => testPayPalConnectivity()
            ], 503);
        }

        $credentials = getPayPalCredentials();

        // Initialize PayPal production environment
        $environment = new \PayPalCheckoutSdk\Core\ProductionEnvironment($credentials['client_id'], $credentials['client_secret']);

        // Use SSL-fixed client for development environments
        if (function_exists('createPayPalClientWithSSLFix')) {
            $client = createPayPalClientWithSSLFix($environment);
        } else {
            $client = new \PayPalCheckoutSdk\Core\PayPalHttpClient($environment);
        }

        // Create order request
        $request = new \PayPalCheckoutSdk\Orders\OrdersCreateRequest();
        $request->prefer('return=representation');

        $orderReference = generateOrderReference($_SESSION['user_id']);

        $request->body = [
            'intent' => 'CAPTURE',
            'application_context' => [
                'return_url' => PAYPAL_RETURN_URL,
                'cancel_url' => PAYPAL_CANCEL_URL,
                'brand_name' => SITE_NAME,
                'user_action' => 'PAY_NOW',
                'payment_method' => [
                    'payer_selected' => 'PAYPAL',
                    'payee_preferred' => 'IMMEDIATE_PAYMENT_REQUIRED'
                ],
                'shipping_preference' => 'NO_SHIPPING',
                'landing_page' => 'BILLING'
            ],
            'purchase_units' => [[
                'reference_id' => $orderReference,
                'description' => 'Order from ' . SITE_NAME,
                'amount' => [
                    'currency_code' => $currency,
                    'value' => number_format($orderData['total'], 2, '.', ''),
                    'breakdown' => [
                        'item_total' => [
                            'currency_code' => $currency,
                            'value' => number_format($orderData['subtotal'], 2, '.', '')
                        ],
                        'tax_total' => [
                            'currency_code' => $currency,
                            'value' => number_format($orderData['tax_amount'], 2, '.', '')
                        ],
                        'shipping' => [
                            'currency_code' => $currency,
                            'value' => number_format($orderData['shipping_amount'], 2, '.', '')
                        ]
                    ]
                ],
                'items' => $orderData['order_items']
            ]]
        ];

        // Execute PayPal request
        $response = $client->execute($request);

        if (!isset($response->result->id)) {
            handleApiError('PayPal order creation failed - no order ID returned', [
                'response_status' => $response->statusCode ?? 'unknown'
            ], 500);
        }

        logPayPalError('PayPal order created successfully', [
            'paypal_order_id' => $response->result->id,
            'environment' => PAYPAL_ENVIRONMENT,
            'user_id' => $_SESSION['user_id']
        ]);

        return $response->result;
    } catch (Exception $e) {
        // Log detailed error for debugging
        logPayPalError('Order creation error: ' . $e->getMessage(), [
            'exception_type' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'user_id' => $_SESSION['user_id'] ?? 'unknown'
        ]);

        // Return immediate error without fallback
        handleApiError('Payment system temporarily unavailable. Please check your internet connection and try again.', [
            'error_type' => 'paypal_connection',
            'suggestion' => 'Check network connectivity'
        ], 503);
    }
}

/**
 * Save order to database
 */
function saveOrderToDatabase($paypalOrder, $orderData, $address, $currency)
{
    global $conn;

    try {
        $conn->begin_transaction();

        $userId = $_SESSION['user_id'];
        $invoiceNumber = generateOrderReference($userId);
        $addressJson = json_encode($address);
        $paypalOrderId = $paypalOrder->id;

        // Insert main order
        $stmt = $conn->prepare("INSERT INTO orders (invoice_number, user_id, total_amount, tax_amount, shipping_amount, payment_status, paypal_order_id, shipping_address, payment_method, currency, created_at) VALUES (?, ?, ?, ?, ?, 'pending', ?, ?, 'paypal', ?, NOW())");

        if (!$stmt) {
            throw new Exception('Failed to prepare order insert: ' . $conn->error);
        }

        $shippingAmount = $orderData['shipping_amount'] ?? 0;
        $stmt->bind_param("sidddsss", $invoiceNumber, $userId, $orderData['subtotal'], $orderData['tax_amount'], $shippingAmount, $paypalOrderId, $addressJson, $currency);

        if (!$stmt->execute()) {
            throw new Exception('Failed to insert order: ' . $stmt->error);
        }

        $orderId = $conn->insert_id;
        $stmt->close();

        // Insert order items
        $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price, subtotal, tax_amount) VALUES (?, ?, ?, ?, ?, ?)");

        if (!$stmt) {
            throw new Exception('Failed to prepare order items insert: ' . $conn->error);
        }

        foreach ($orderData['items'] as $item) {
            $item_tax_rate = getTaxRate($address['state'], $address['country'], $item['type']);
            $item_tax = $item['total'] * $item_tax_rate;

            $stmt->bind_param("iiiddd", $orderId, $item['id'], $item['quantity'], $item['price'], $item['total'], $item_tax);

            if (!$stmt->execute()) {
                throw new Exception('Failed to insert order item: ' . $stmt->error);
            }
        }
        $stmt->close();

        // Insert order status history
        $stmt = $conn->prepare("INSERT INTO order_status_history (order_id, status, notes, created_at) VALUES (?, 'pending', 'Order created via PayPal', NOW())");
        if (!$stmt) {
            throw new Exception('Failed to prepare status history insert: ' . $conn->error);
        }

        $stmt->bind_param("i", $orderId);
        if (!$stmt->execute()) {
            throw new Exception('Failed to insert order status: ' . $stmt->error);
        }
        $stmt->close();

        // Create eBook downloads for digital products
        $stmt = $conn->prepare("INSERT INTO ebook_downloads (user_id, order_id, product_id, download_token, max_downloads, expires_at, created_at) VALUES (?, ?, ?, ?, 5, DATE_ADD(NOW(), INTERVAL 30 DAY), NOW())");

        if (!$stmt) {
            throw new Exception('Failed to prepare ebook downloads insert: ' . $conn->error);
        }

        foreach ($orderData['items'] as $item) {
            if ($item['type'] === 'ebook') {
                $downloadToken = bin2hex(random_bytes(32));
                $stmt->bind_param("iiis", $userId, $orderId, $item['id'], $downloadToken);

                if (!$stmt->execute()) {
                    logPayPalError('Failed to create ebook download', [
                        'order_id' => $orderId,
                        'product_id' => $item['id'],
                        'error' => $stmt->error
                    ]);
                }
            }
        }
        $stmt->close();

        $conn->commit();

        return $orderId;
    } catch (Exception $e) {
        $conn->rollback();
        handleApiError('Database error: ' . $e->getMessage(), [], 500);
    }
}

// Main execution
try {
    validateRequest();
    $userId = validateSession();
    $requestData = getRequestData();

    validateCSRF($requestData['csrf_token']);

    $address = validateShippingAddress($requestData['address_id'], $userId);
    $currency = $requestData['currency'] ?? getUserCurrency($userId);

    // Validate currency
    if (!in_array($currency, PAYPAL_SUPPORTED_CURRENCIES, true)) {
        handleApiError('Unsupported currency: ' . $currency, [], 400);
    }

    $orderData = validateCartAndCalculateTotals($userId, $address, $requestData);
    $paypalOrder = createPayPalOrder($orderData, $address, $currency);
    $databaseOrderId = saveOrderToDatabase($paypalOrder, $orderData, $address, $currency);

    // Success response
    sendResponse([
        'id' => $paypalOrder->id,
        'status' => 'success',
        'database_order_id' => $databaseOrderId,
        'environment' => PAYPAL_ENVIRONMENT,
        'currency' => $currency,
        'total' => $orderData['total']
    ]);
} catch (Exception $e) {
    handleApiError('Unexpected error: ' . $e->getMessage(), [
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ], 500);
}