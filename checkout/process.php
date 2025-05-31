<?php
require_once '../includes/bootstrap.php';
require_once '../includes/cart.php';
require_once '../includes/security.php';
require_once __DIR__ . '/includes/PayPalHelper.php';

header('Content-Type: application/json');

try {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        throw new Exception('Invalid CSRF token');
    }

    // Verify required fields
    if (!isset($_POST['order_id']) || !isset($_POST['action'])) {
        throw new Exception('Missing required fields');
    }

    $order_id = $_POST['order_id'];
    $action = $_POST['action'];

    error_log("Processing PayPal order: " . $order_id);
    error_log("Action: " . $action);

    // Initialize PayPal helper
    $paypal = new PayPalHelper();

    // Handle different actions
    switch ($action) {
        case 'process':
            // Capture the payment
            $result = $paypal->captureOrder($order_id);
            error_log("Capture result: " . print_r($result, true));

            if (!$result['success']) {
                throw new Exception($result['message']);
            }

            // Update order status in database
            $stmt = $conn->prepare("
                UPDATE orders 
                SET payment_status = 'completed',
                    status = 'processing',
                    updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->bind_param("s", $order_id);
            $stmt->execute();

            echo json_encode([
                'success' => true,
                'order_id' => $order_id,
                'message' => 'Payment processed successfully'
            ]);
            break;

        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    error_log("Payment processing error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}