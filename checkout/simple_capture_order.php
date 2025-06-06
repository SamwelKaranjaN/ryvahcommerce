<?php

/**
 * Enhanced PayPal Order Capture
 * Ryvah Commerce - Secure order capture with comprehensive validation
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
    logPayPalError('Order capture error: ' . $message, $context);
    sendResponse([
        'error' => true,
        'success' => false,
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
    if (!isset($data['orderID']) || empty($data['orderID'])) {
        handleApiError('Missing PayPal order ID', [], 400);
    }

    if (!isset($data['csrf_token']) || empty($data['csrf_token'])) {
        handleApiError('Missing CSRF token', [], 400);
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
 * Capture PayPal order
 */
function capturePayPalOrder($orderID)
{
    try {
        if (!validatePayPalConfig()) {
            handleApiError('PayPal configuration is invalid', [], 500);
        }

        if (!isPayPalSDKAvailable()) {
            handleApiError('PayPal SDK is not available', [], 500);
        }

        $credentials = getPayPalCredentials();

        // Initialize PayPal production environment
        $environment = new \PayPalCheckoutSdk\Core\ProductionEnvironment($credentials['client_id'], $credentials['client_secret']);
        $client = new \PayPalCheckoutSdk\Core\PayPalHttpClient($environment);

        // Create capture request
        $request = new \PayPalCheckoutSdk\Orders\OrdersCaptureRequest($orderID);
        $request->prefer('return=representation');

        // Execute PayPal capture
        $response = $client->execute($request);

        if (!isset($response->result)) {
            handleApiError('Invalid PayPal capture response', ['orderID' => $orderID], 500);
        }

        $result = $response->result;

        // Validate capture status
        if ($result->status !== 'COMPLETED') {
            handleApiError('Payment capture failed. Status: ' . $result->status, [
                'orderID' => $orderID,
                'status' => $result->status,
                'paypal_response' => $result
            ], 400);
        }

        // Validate purchase units
        if (!isset($result->purchase_units) || empty($result->purchase_units)) {
            handleApiError('No purchase units in PayPal response', ['orderID' => $orderID], 500);
        }

        $purchaseUnit = $result->purchase_units[0];

        // Validate capture details
        if (!isset($purchaseUnit->payments->captures) || empty($purchaseUnit->payments->captures)) {
            handleApiError('No capture details in PayPal response', ['orderID' => $orderID], 500);
        }

        $capture = $purchaseUnit->payments->captures[0];

        if ($capture->status !== 'COMPLETED') {
            handleApiError('Capture not completed. Status: ' . $capture->status, [
                'orderID' => $orderID,
                'capture_status' => $capture->status
            ], 400);
        }

        logPayPalError('PayPal order captured successfully', [
            'orderID' => $orderID,
            'capture_id' => $capture->id,
            'amount' => $capture->amount->value,
            'currency' => $capture->amount->currency_code,
            'environment' => PAYPAL_ENVIRONMENT
        ]);

        return $result;
    } catch (\PayPalHttp\HttpException $e) {
        $errorBody = json_decode($e->getMessage(), true);
        handleApiError('PayPal HTTP error: ' . ($errorBody['message'] ?? $e->getMessage()), [
            'orderID' => $orderID,
            'status_code' => $e->statusCode,
            'error_body' => $errorBody
        ], 500);
    } catch (Exception $e) {
        handleApiError('PayPal API error: ' . $e->getMessage(), [
            'orderID' => $orderID,
            'exception_type' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ], 500);
    }
}

/**
 * Validate order in database and update status
 */
function validateAndUpdateOrder($paypalOrderID, $paypalResult, $userId)
{
    global $conn;

    try {
        // Fetch order from database
        $stmt = $conn->prepare("SELECT id, total_amount, tax_amount, currency, payment_status FROM orders WHERE paypal_order_id = ? AND user_id = ?");
        if (!$stmt) {
            handleApiError('Database prepare error: ' . $conn->error, [], 500);
        }

        $stmt->bind_param("si", $paypalOrderID, $userId);
        if (!$stmt->execute()) {
            handleApiError('Database execute error: ' . $stmt->error, [], 500);
        }

        $order = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$order) {
            handleApiError('Order not found in database', [
                'paypal_order_id' => $paypalOrderID,
                'user_id' => $userId
            ], 404);
        }

        // Check if order is already completed
        if ($order['payment_status'] === 'completed') {
            logPayPalError('Attempted to capture already completed order', [
                'order_id' => $order['id'],
                'paypal_order_id' => $paypalOrderID
            ]);

            // Return success for idempotency
            return [
                'order_id' => $order['id'],
                'already_completed' => true
            ];
        }

        // Validate captured amount and currency
        $purchaseUnit = $paypalResult->purchase_units[0];
        $capturedAmount = floatval($purchaseUnit->amount->value);
        $capturedCurrency = $purchaseUnit->amount->currency_code;
        $expectedTotal = floatval($order['total_amount'] + $order['tax_amount']);
        $expectedCurrency = $order['currency'] ?: PAYPAL_DEFAULT_CURRENCY;

        // Validate currency matches
        if ($capturedCurrency !== $expectedCurrency) {
            handleApiError('Currency mismatch', [
                'captured_currency' => $capturedCurrency,
                'expected_currency' => $expectedCurrency,
                'order_id' => $order['id']
            ], 400);
        }

        // Validate amount matches (allow small discrepancies for rounding)
        if (abs($capturedAmount - $expectedTotal) > 0.01) {
            handleApiError('Captured amount mismatch', [
                'captured_amount' => $capturedAmount,
                'expected_amount' => $expectedTotal,
                'difference' => abs($capturedAmount - $expectedTotal),
                'order_id' => $order['id']
            ], 400);
        }

        // Start database transaction
        $conn->begin_transaction();

        try {
            // Update order status
            $stmt = $conn->prepare("UPDATE orders SET payment_status = 'completed', updated_at = NOW() WHERE id = ?");
            if (!$stmt) {
                throw new Exception('Failed to prepare order update: ' . $conn->error);
            }

            $stmt->bind_param("i", $order['id']);
            if (!$stmt->execute()) {
                throw new Exception('Failed to update order status: ' . $stmt->error);
            }
            $stmt->close();

            // Add to order status history
            $stmt = $conn->prepare("INSERT INTO order_status_history (order_id, status, notes, created_at) VALUES (?, 'completed', 'Payment captured via PayPal', NOW())");
            if (!$stmt) {
                throw new Exception('Failed to prepare status history insert: ' . $conn->error);
            }

            $stmt->bind_param("i", $order['id']);
            if (!$stmt->execute()) {
                throw new Exception('Failed to insert order status history: ' . $stmt->error);
            }
            $stmt->close();

            // Record transaction details
            $captureId = $paypalResult->purchase_units[0]->payments->captures[0]->id ?? null;
            if ($captureId) {
                $stmt = $conn->prepare("INSERT INTO transactions (order_id, payment_method, status, transaction_id, created_at) VALUES (?, 'paypal', 'success', ?, NOW())");
                if ($stmt) {
                    $stmt->bind_param("is", $order['id'], $captureId);
                    $stmt->execute();
                    $stmt->close();
                }
            }

            // Update product stock quantities
            $stmt = $conn->prepare("
                UPDATE products p 
                INNER JOIN order_items oi ON p.id = oi.product_id 
                SET p.stock_quantity = p.stock_quantity - oi.quantity 
                WHERE oi.order_id = ? AND p.stock_quantity >= oi.quantity
            ");
            if ($stmt) {
                $stmt->bind_param("i", $order['id']);
                $stmt->execute();
                $stmt->close();
            }

            $conn->commit();

            logPayPalError('Order successfully updated in database', [
                'order_id' => $order['id'],
                'paypal_order_id' => $paypalOrderID,
                'captured_amount' => $capturedAmount,
                'currency' => $capturedCurrency
            ]);

            return [
                'order_id' => $order['id'],
                'already_completed' => false
            ];
        } catch (Exception $e) {
            $conn->rollback();
            throw $e;
        }
    } catch (Exception $e) {
        handleApiError('Database error during order update: ' . $e->getMessage(), [
            'paypal_order_id' => $paypalOrderID,
            'user_id' => $userId
        ], 500);
    }
}

/**
 * Clear user cart after successful payment
 */
function clearUserCart($userId)
{
    global $conn;

    try {
        // Clear session cart
        if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
            unset($_SESSION['cart']);
        }

        // Clear database cart
        $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $stmt->close();

            logPayPalError('User cart cleared successfully', ['user_id' => $userId]);
        }
    } catch (Exception $e) {
        logPayPalError('Failed to clear user cart: ' . $e->getMessage(), ['user_id' => $userId]);
        // Don't fail the whole process if cart clearing fails
    }
}

// Main execution
try {
    validateRequest();
    $userId = validateSession();
    $requestData = getRequestData();

    validateCSRF($requestData['csrf_token']);

    $orderID = $requestData['orderID'];

    // Validate PayPal order ID format
    if (!preg_match('/^[A-Z0-9]{17}$/', $orderID)) {
        handleApiError('Invalid PayPal order ID format', ['orderID' => $orderID], 400);
    }

    logPayPalError('Starting PayPal order capture', [
        'orderID' => $orderID,
        'user_id' => $userId,
        'environment' => PAYPAL_ENVIRONMENT
    ]);

    // Capture PayPal order
    $paypalResult = capturePayPalOrder($orderID);

    // Validate and update order in database
    $orderUpdate = validateAndUpdateOrder($orderID, $paypalResult, $userId);

    // Clear user cart
    clearUserCart($userId);

    // Generate success token for secure redirect
    $_SESSION['success_token'] = bin2hex(random_bytes(32));

    // Success response
    sendResponse([
        'success' => true,
        'order_id' => $orderUpdate['order_id'],
        'paypal_order_id' => $orderID,
        'status' => 'completed',
        'message' => $orderUpdate['already_completed'] ? 'Order was already completed' : 'Payment completed successfully',
        'success_token' => $_SESSION['success_token'],
        'environment' => PAYPAL_ENVIRONMENT,
        'captured_amount' => $paypalResult->purchase_units[0]->amount->value ?? null,
        'currency' => $paypalResult->purchase_units[0]->amount->currency_code ?? null
    ]);
} catch (Exception $e) {
    handleApiError('Unexpected error during order capture: ' . $e->getMessage(), [
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ], 500);
}
