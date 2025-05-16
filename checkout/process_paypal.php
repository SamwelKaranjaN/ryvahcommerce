<?php
session_start();
require_once '../config/database.php';
require_once '../vendor/autoload.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Check if order ID exists
if (!isset($_SESSION['current_order_id'])) {
    header('Location: checkout.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = $_SESSION['current_order_id'];
$conn = getDBConnection();

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

try {
    // Update order status
    $sql = "UPDATE orders SET payment_status = 'completed', payment_method = 'paypal' WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();

    // Update payment status
    $sql = "UPDATE order_payments SET status = 'completed', transaction_id = ? WHERE order_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $data['payment_id'], $order_id);
    $stmt->execute();

    // Add order status history
    $sql = "INSERT INTO order_status_history (order_id, status, notes) VALUES (?, 'completed', 'Payment completed via PayPal')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();

    // Clear cart
    $sql = "DELETE FROM cart WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    // Clear session data
    unset($_SESSION['temp_billing']);
    unset($_SESSION['current_order_id']);

    echo json_encode([
        'success' => true,
        'message' => 'Payment successful',
        'order_id' => $order_id
    ]);
} catch (Exception $e) {
    // Log error
    error_log("PayPal payment error: " . $e->getMessage());

    // Update order status to failed
    $sql = "UPDATE orders SET payment_status = 'failed' WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();

    // Add failed status to history
    $sql = "INSERT INTO order_status_history (order_id, status, notes) VALUES (?, 'failed', 'Payment failed: " . $e->getMessage() . "')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();

    echo json_encode([
        'success' => false,
        'message' => 'Payment failed: ' . $e->getMessage()
    ]);
}