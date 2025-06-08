<?php

/**
 * Enhanced PayPal Order Capture API
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
    $requiredFields = ['orderID', 'csrf_token'];
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

        // Validate network connectivity before attempting capture
        if (!validateNetworkConnectivity()) {
            handleApiError('Cannot connect to payment system. Please check your internet connection and try again.', [
                'network_test' => testPayPalConnectivity()
            ], 503);
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

        if ($response->result->status !== 'COMPLETED') {
            handleApiError('Payment capture failed. Status: ' . $response->result->status, [
                'paypal_order_id' => $orderID,
                'status' => $response->result->status
            ], 400);
        }

        logPayPalError('PayPal order captured successfully', [
            'paypal_order_id' => $orderID,
            'environment' => PAYPAL_ENVIRONMENT,
            'user_id' => $_SESSION['user_id']
        ]);

        return $response->result;
    } catch (Exception $e) {
        // Log detailed error for debugging
        logPayPalError('Order capture error: ' . $e->getMessage(), [
            'exception_type' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'paypal_order_id' => $orderID,
            'user_id' => $_SESSION['user_id'] ?? 'unknown'
        ]);

        // Return immediate error without fallback
        handleApiError('Payment capture failed. Please check your internet connection and try again.', [
            'error_type' => 'paypal_connection',
            'suggestion' => 'Check network connectivity'
        ], 503);
    }
}

/**
 * Update order status in database
 */
function updateOrderStatus($orderID, $userId)
{
    global $conn;

    try {
        $conn->begin_transaction();

        // Update order status
        $stmt = $conn->prepare("UPDATE orders SET payment_status = 'completed', paid_at = NOW() WHERE paypal_order_id = ? AND user_id = ?");
        if (!$stmt) {
            throw new Exception('Failed to prepare order update: ' . $conn->error);
        }

        $stmt->bind_param("si", $orderID, $userId);
        if (!$stmt->execute()) {
            throw new Exception('Failed to update order: ' . $stmt->error);
        }

        if ($stmt->affected_rows === 0) {
            throw new Exception('Order not found or not owned by user');
        }

        // Get order details
        $stmt = $conn->prepare("SELECT id, invoice_number FROM orders WHERE paypal_order_id = ? AND user_id = ?");
        if (!$stmt) {
            throw new Exception('Failed to prepare order select: ' . $conn->error);
        }

        $stmt->bind_param("si", $orderID, $userId);
        if (!$stmt->execute()) {
            throw new Exception('Failed to fetch order: ' . $stmt->error);
        }

        $order = $stmt->get_result()->fetch_assoc();
        if (!$order) {
            throw new Exception('Order not found after update');
        }

        // Insert order status history
        $stmt = $conn->prepare("INSERT INTO order_status_history (order_id, status, notes, created_at) VALUES (?, 'completed', 'Payment captured via PayPal', NOW())");
        if (!$stmt) {
            throw new Exception('Failed to prepare status history insert: ' . $conn->error);
        }

        $stmt->bind_param("i", $order['id']);
        if (!$stmt->execute()) {
            throw new Exception('Failed to insert order status: ' . $stmt->error);
        }

        $conn->commit();

        return $order;
    } catch (Exception $e) {
        $conn->rollback();
        handleApiError('Database error: ' . $e->getMessage(), [], 500);
    }
}

/**
 * Generate success token for secure redirect
 */
function generateSuccessToken($orderId, $userId)
{
    return hash('sha256', $orderId . $userId . $_SESSION['csrf_token'] . time());
}

// Main execution
try {
    validateRequest();
    $userId = validateSession();
    $requestData = getRequestData();
    
    validateCSRF($requestData['csrf_token']);
    
    $orderID = $requestData['orderID'];
    
    // Validate order ID format
    if (!preg_match('/^[A-Z0-9]{13,17}$/', $orderID)) {
        handleApiError('Invalid PayPal order ID format', [], 400);
    }
    
    // Capture PayPal order
    $captureResult = capturePayPalOrder($orderID);
    
    // Update database
    $order = updateOrderStatus($orderID, $userId);
    
    // Clear cart after successful payment
    clearCart();
    
    // Generate success token
    $successToken = generateSuccessToken($order['id'], $userId);
    
    // Return success response
    sendResponse([
        'success' => true,
        'order_id' => $order['id'],
        'success_token' => $successToken,
        'message' => 'Payment completed successfully',
        'invoice_number' => $order['invoice_number']
    ]);
    
} catch (Exception $e) {
    logPayPalError('Unexpected error in order capture: ' . $e->getMessage());
    handleApiError('An unexpected error occurred. Please try again.', [], 500);
} 