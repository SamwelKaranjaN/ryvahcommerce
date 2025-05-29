<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$user_id = $_SESSION['user_id'];
$conn = getDBConnection();

try {
    // Start transaction
    $conn->begin_transaction();

    // Get cart items
    $sql = "SELECT c.*, p.name, p.price, p.type 
            FROM cart c 
            JOIN products p ON c.product_id = p.id 
            WHERE c.user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $cart_items = $result->fetch_all(MYSQLI_ASSOC);

    if (empty($cart_items)) {
        throw new Exception('No items in cart');
    }

    // Calculate total
    $total = 0;
    foreach ($cart_items as $item) {
        $total += $item['price'] * $item['quantity'];
    }

    // Create order with failed status
    $sql = "INSERT INTO orders (user_id, total_amount, payment_status, notes) 
            VALUES (?, ?, 'failed', ?)";
    $stmt = $conn->prepare($sql);
    $notes = isset($data['cancelled_by_user']) ? 'Payment cancelled by user' : (isset($data['error_message']) ? $data['error_message'] : 'Payment failed');
    $stmt->bind_param("ids", $user_id, $total, $notes);
    $stmt->execute();
    $order_id = $conn->insert_id;

    // Insert order items
    foreach ($cart_items as $item) {
        $sql = "INSERT INTO order_items (order_id, product_id, quantity, price, subtotal) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $subtotal = $item['price'] * $item['quantity'];
        $stmt->bind_param("iiids", $order_id, $item['product_id'], $item['quantity'], $item['price'], $subtotal);
        $stmt->execute();
    }

    // Add status to history
    $sql = "INSERT INTO order_status_history (order_id, status, notes) VALUES (?, 'failed', ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $order_id, $notes);
    $stmt->execute();

    // Clear cart
    $sql = "DELETE FROM cart WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $conn->commit();
    echo json_encode(['success' => true, 'order_id' => $order_id]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
