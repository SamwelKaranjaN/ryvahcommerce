<?php

/**
 * Enhanced PayPal Order Capture API
 * Ryvah Commerce - Secure order capture with comprehensive validation
 */

// Prevent cart interference
define('PAYPAL_ORDER_PROCESSING', true);

// Suppress PHP 8.2+ deprecation warnings for PayPal SDK
error_reporting(E_ALL & ~E_DEPRECATED);

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
 * Parse PayPal error details from exception
 */
function parsePayPalError($exception)
{
    $errorDetails = [
        'is_paypal_error' => false,
        'error_code' => null,
        'error_message' => null,
        'debug_id' => null,
        'issues' => []
    ];

    try {
        $message = $exception->getMessage();

        // Check if it's a PayPal HTTP exception with JSON response
        if (strpos($message, '{') !== false) {
            $jsonStart = strpos($message, '{');
            $jsonPart = substr($message, $jsonStart);
            $errorData = json_decode($jsonPart, true);

            if (is_array($errorData)) {
                $errorDetails['is_paypal_error'] = true;
                $errorDetails['error_code'] = $errorData['name'] ?? null;
                $errorDetails['error_message'] = $errorData['message'] ?? null;
                $errorDetails['debug_id'] = $errorData['debug_id'] ?? null;

                // Parse detailed issues
                if (isset($errorData['details']) && is_array($errorData['details'])) {
                    foreach ($errorData['details'] as $detail) {
                        if (isset($detail['issue'])) {
                            $errorDetails['issues'][] = [
                                'issue' => $detail['issue'],
                                'description' => $detail['description'] ?? ''
                            ];

                            // Use the first issue as the main error code if no name is set
                            if (!$errorDetails['error_code']) {
                                $errorDetails['error_code'] = $detail['issue'];
                            }
                        }
                    }
                }
            }
        }

        // Check if it's a PayPal SDK exception type
        $exceptionClass = get_class($exception);
        if (strpos($exceptionClass, 'PayPal') !== false || strpos($exceptionClass, 'HttpException') !== false) {
            $errorDetails['is_paypal_error'] = true;

            // Try to extract error code from message if not already found
            if (!$errorDetails['error_code']) {
                if (preg_match('/\b([A-Z_]+)\b/', $message, $matches)) {
                    $errorDetails['error_code'] = $matches[1];
                }
            }
        }
    } catch (Exception $e) {
        // If parsing fails, treat as generic error
        $errorDetails['is_paypal_error'] = false;
    }

    return $errorDetails;
}

/**
 * Capture PayPal order using Server SDK
 */
function capturePayPalOrder($orderID)
{
    try {
        if (!validatePayPalConfig()) {
            handleApiError('PayPal configuration is invalid', [], 500);
        }

        if (!isPayPalSDKAvailable()) {
            handleApiError('PayPal Server SDK is not available', [], 500);
        }

        // Validate network connectivity before attempting capture
        if (!validateNetworkConnectivity()) {
            handleApiError('Cannot connect to payment system. Please check your internet connection and try again.', [
                'network_test' => testPayPalConnectivity()
            ], 503);
        }

        // Create PayPal Server SDK client
        $client = createPayPalServerClient();

        // Execute PayPal capture (Server SDK doesn't require a separate capture request body for basic capture)
        $response = $client->getOrdersController()->captureOrder(['id' => $orderID]);

        if (!$response->isSuccess()) {
            $errorBody = $response->getBody();
            handleApiError('PayPal order capture failed', [
                'status_code' => $response->getStatusCode(),
                'response_body' => $errorBody,
                'paypal_order_id' => $orderID
            ], 400);
        }

        $result = $response->getResult();

        // Check if the order status is completed (using string comparison for compatibility)
        $orderStatus = $result->getStatus();
        if ($orderStatus !== 'COMPLETED') {
            handleApiError('Payment capture failed. Status: ' . $orderStatus, [
                'paypal_order_id' => $orderID,
                'status' => $orderStatus
            ], 400);
        }

        logPayPalError('PayPal order captured successfully', [
            'paypal_order_id' => $orderID,
            'environment' => PAYPAL_ENVIRONMENT,
            'user_id' => $_SESSION['user_id']
        ]);

        return $result;
    } catch (Exception $e) {
        // Parse PayPal specific errors
        $errorDetails = parsePayPalError($e);

        // Log detailed error for debugging
        logPayPalError('Order capture error: ' . $e->getMessage(), [
            'exception_type' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'paypal_order_id' => $orderID,
            'user_id' => $_SESSION['user_id'] ?? 'unknown',
            'error_details' => $errorDetails
        ]);

        // Handle specific PayPal errors
        if ($errorDetails['is_paypal_error']) {
            $errorCode = $errorDetails['error_code'] ?? 'UNKNOWN';
            $errorMessage = $errorDetails['error_message'] ?? $e->getMessage();

            switch ($errorCode) {
                case 'TRANSACTION_REFUSED':
                    handleApiError('Payment was declined. Please try with a different payment method or contact your bank.', [
                        'error_code' => $errorCode,
                        'debug_id' => $errorDetails['debug_id'] ?? null
                    ], 422);
                    break;

                case 'INSTRUMENT_DECLINED':
                    handleApiError('Payment method declined. Please try with a different payment method.', [
                        'error_code' => $errorCode,
                        'debug_id' => $errorDetails['debug_id'] ?? null
                    ], 422);
                    break;

                case 'PAYER_ACCOUNT_RESTRICTED':
                    handleApiError('PayPal account restricted. Please contact PayPal support.', [
                        'error_code' => $errorCode,
                        'debug_id' => $errorDetails['debug_id'] ?? null
                    ], 422);
                    break;

                case 'ORDER_NOT_APPROVED':
                    handleApiError('Order not approved by customer. Please try again.', [
                        'error_code' => $errorCode,
                        'debug_id' => $errorDetails['debug_id'] ?? null
                    ], 422);
                    break;

                case 'AUTHORIZATION_ERROR':
                case 'AUTHENTICATION_FAILURE':
                    handleApiError('Payment system configuration error. Please contact support.', [
                        'error_code' => $errorCode,
                        'debug_id' => $errorDetails['debug_id'] ?? null
                    ], 500);
                    break;

                default:
                    handleApiError('Payment processing failed: ' . $errorMessage, [
                        'error_code' => $errorCode,
                        'debug_id' => $errorDetails['debug_id'] ?? null
                    ], 422);
            }
        } else {
            // Generic connection/network error
            handleApiError('Payment capture failed. Please check your internet connection and try again.', [
                'error_type' => 'paypal_connection',
                'suggestion' => 'Check network connectivity'
            ], 503);
        }
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
        $stmt = $conn->prepare("INSERT INTO order_status_history (order_id, status, notes, created_at) VALUES (?, 'completed', 'Payment captured via PayPal Server SDK', NOW())");
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

    // Store success token in session for validation on success page
    $_SESSION['success_token'] = $successToken;

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
