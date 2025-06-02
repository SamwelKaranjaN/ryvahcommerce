<?php
// Turn off error display and start output buffering before any other includes
ini_set('display_errors', 0);
ini_set('log_errors', 1);
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
$autoload_path = __DIR__ . '/../vendor/autoload.php';
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
define('PAYPAL_CLIENT_SECRET', 'EHHv6Yf6p65iSR_MNUVp9JDgK0Ma81N7Bu3mX6Tt_k7VQpq2TIM626vYTkF5rHwzofdEHxBLMmkOLhqe');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Get POST data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

error_log("PayPal Order Capture - Received data: " . print_r($data, true));

$orderID = $data['orderID'] ?? '';

if (empty($orderID)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid order ID']);
    exit;
}

try {
    // Initialize PayPal client
    $environment = new \PayPalCheckoutSdk\Core\SandboxEnvironment(PAYPAL_CLIENT_ID, PAYPAL_CLIENT_SECRET);
    $client = new \PayPalCheckoutSdk\Core\PayPalHttpClient($environment);
    error_log("PayPal - Capture client initialized successfully");

    // Capture order request
    $request = new \PayPalCheckoutSdk\Orders\OrdersCaptureRequest($orderID);
    $request->prefer('return=representation');

    error_log("PayPal - Attempting to capture order: " . $orderID);

    $response = $client->execute($request);
    error_log("PayPal - Capture API call successful");
    error_log("PayPal - Capture response: " . json_encode($response->result));

    if ($response->result->status === 'COMPLETED') {
        // Update order status in database
        $stmt = $conn->prepare("UPDATE orders SET payment_status = 'completed' WHERE paypal_order_id = ? AND user_id = ?");
        $stmt->bind_param("si", $orderID, $_SESSION['user_id']);

        if (!$stmt->execute()) {
            throw new Exception("Database error updating order: " . $stmt->error);
        }

        if ($stmt->affected_rows === 0) {
            throw new Exception('Order not found or not owned by user');
        }

        // Get order ID
        $stmt = $conn->prepare("SELECT id FROM orders WHERE paypal_order_id = ? AND user_id = ?");
        $stmt->bind_param("si", $orderID, $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $order = $result->fetch_assoc();

        if (!$order) {
            throw new Exception('Order not found in database');
        }

        error_log("PayPal - Order " . $order['id'] . " marked as completed");

        // Clear cart
        clearCart();
        error_log("PayPal - Cart cleared for user " . $_SESSION['user_id']);

        // Return success response
        ob_clean(); // Clear any unexpected output
        echo json_encode([
            'status' => 'COMPLETED',
            'order_id' => $order['id'],
            'message' => 'Payment completed successfully'
        ]);
    } else {
        throw new Exception('Payment not completed. Status: ' . $response->result->status);
    }
} catch (Exception $e) {
    error_log("PayPal Order Capture Error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());

    ob_clean(); // Clear any unexpected output
    http_response_code(500);
    echo json_encode([
        'error' => 'An error occurred while capturing the payment',
        'message' => $e->getMessage()
    ]);
}

ob_end_flush();