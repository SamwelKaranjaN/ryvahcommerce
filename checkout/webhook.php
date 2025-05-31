<?php
require_once '../includes/bootstrap.php';
require_once '../includes/security.php';
require_once __DIR__ . '/includes/PayPalHelper.php';

// Get the webhook payload
$payload = file_get_contents('php://input');
$headers = getallheaders();

// Log webhook request
error_log("PayPal Webhook received");
error_log("Headers: " . print_r($headers, true));
error_log("Payload: " . $payload);

// Verify webhook signature
$paypal_config = require __DIR__ . '/config/paypal.php';
$webhook_id = $paypal_config['webhook_id'];
$webhook_secret = $paypal_config['webhook_secret'];

if (empty($webhook_id) || empty($webhook_secret)) {
    error_log("Webhook configuration missing");
    http_response_code(500);
    exit('Webhook configuration missing');
}

// Verify webhook signature
$transmission_id = $headers['Paypal-Transmission-Id'] ?? '';
$timestamp = $headers['Paypal-Transmission-Time'] ?? '';
$webhook_id = $headers['Paypal-Webhook-Id'] ?? '';
$actual_signature = $headers['Paypal-Transmission-Sig'] ?? '';

if (empty($transmission_id) || empty($timestamp) || empty($webhook_id) || empty($actual_signature)) {
    error_log("Missing required webhook headers");
    http_response_code(400);
    exit('Missing required headers');
}

// Verify webhook ID matches configuration
if ($webhook_id !== $paypal_config['webhook_id']) {
    error_log("Invalid webhook ID");
    http_response_code(400);
    exit('Invalid webhook ID');
}

// Process the webhook event
$event = json_decode($payload, true);
if (!$event) {
    error_log("Invalid webhook payload");
    http_response_code(400);
    exit('Invalid payload');
}

error_log("Processing webhook event: " . $event['event_type']);

// Handle different event types
switch ($event['event_type']) {
    case 'PAYMENT.CAPTURE.COMPLETED':
        // Payment was successfully captured
        $order_id = $event['resource']['id'];
        error_log("Payment completed for order: " . $order_id);
        handlePaymentCaptureCompleted($event);
        break;

    case 'PAYMENT.CAPTURE.DENIED':
        // Payment was denied
        $order_id = $event['resource']['id'];
        error_log("Payment denied for order: " . $order_id);
        handlePaymentCaptureDenied($event);
        break;

    case 'PAYMENT.CAPTURE.REFUNDED':
        // Payment was refunded
        $order_id = $event['resource']['id'];
        error_log("Payment refunded for order: " . $order_id);
        handlePaymentRefunded($event);
        break;

    default:
        error_log("Unhandled event type: " . $event['event_type']);
        // Log unhandled event type
        logSecurityEvent('unhandled_webhook', [
            'event_type' => $event['event_type']
        ]);
}

// Return 200 OK to acknowledge receipt
http_response_code(200);
exit('Webhook processed');

/**
 * Handle completed payment capture
 */
function handlePaymentCaptureCompleted($data)
{
    global $conn;

    $transaction_id = $data['resource']['id'];
    $status = $data['resource']['status'];
    $amount = $data['resource']['amount']['value'];

    // Update payment status
    $stmt = $conn->prepare("
        UPDATE order_payments 
        SET status = ?, 
            payment_date = NOW(),
            metadata = JSON_SET(
                COALESCE(metadata, '{}'),
                '$.webhook_data', ?
            )
        WHERE transaction_id = ?
    ");

    $metadata = json_encode($data);
    $stmt->bind_param("sss", $status, $metadata, $transaction_id);
    $stmt->execute();

    // Update order status
    $stmt = $conn->prepare("
        UPDATE orders o
        JOIN order_payments op ON o.id = op.order_id
        SET o.payment_status = 'completed',
            o.status = 'processing'
        WHERE op.transaction_id = ?
    ");
    $stmt->bind_param("s", $transaction_id);
    $stmt->execute();
}

/**
 * Handle denied payment capture
 */
function handlePaymentCaptureDenied($data)
{
    global $conn;

    $transaction_id = $data['resource']['id'];
    $status = $data['resource']['status'];

    // Update payment status
    $stmt = $conn->prepare("
        UPDATE order_payments 
        SET status = 'failed',
            metadata = JSON_SET(
                COALESCE(metadata, '{}'),
                '$.webhook_data', ?,
                '$.failure_reason', ?
            )
        WHERE transaction_id = ?
    ");

    $metadata = json_encode($data);
    $failure_reason = $data['resource']['status_details']['reason'] ?? 'unknown';
    $stmt->bind_param("sss", $metadata, $failure_reason, $transaction_id);
    $stmt->execute();

    // Update order status
    $stmt = $conn->prepare("
        UPDATE orders o
        JOIN order_payments op ON o.id = op.order_id
        SET o.payment_status = 'failed',
            o.status = 'cancelled'
        WHERE op.transaction_id = ?
    ");
    $stmt->bind_param("s", $transaction_id);
    $stmt->execute();
}

/**
 * Handle payment refund
 */
function handlePaymentRefunded($data)
{
    global $conn;

    $transaction_id = $data['resource']['id'];
    $status = $data['resource']['status'];
    $refund_amount = $data['resource']['amount']['value'];

    // Update payment status
    $stmt = $conn->prepare("
        UPDATE order_payments 
        SET status = 'refunded',
            metadata = JSON_SET(
                COALESCE(metadata, '{}'),
                '$.webhook_data', ?,
                '$.refund_amount', ?
            )
        WHERE transaction_id = ?
    ");

    $metadata = json_encode($data);
    $stmt->bind_param("sds", $metadata, $refund_amount, $transaction_id);
    $stmt->execute();

    // Update order status
    $stmt = $conn->prepare("
        UPDATE orders o
        JOIN order_payments op ON o.id = op.order_id
        SET o.payment_status = 'refunded',
            o.status = 'refunded'
        WHERE op.transaction_id = ?
    ");
    $stmt->bind_param("s", $transaction_id);
    $stmt->execute();
}
