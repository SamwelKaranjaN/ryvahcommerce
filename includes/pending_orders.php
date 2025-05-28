<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die(json_encode(['success' => false, 'message' => 'User not logged in']));
}

$user_id = $_SESSION['user_id'];
$conn = getDBConnection();

// Handle different actions
$action = $_POST['action'] ?? '';

switch ($action) {
    case 'add':
        // Add items from cart to pending orders
        $cart_sql = "SELECT c.*, p.price, p.type 
                    FROM cart c 
                    JOIN products p ON c.product_id = p.id 
                    WHERE c.user_id = ?";
        $stmt = $conn->prepare($cart_sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $cart_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        $conn->begin_transaction();
        try {
            foreach ($cart_items as $item) {
                // Insert into pending_orders
                $sql = "INSERT INTO pending_orders (user_id, product_id, quantity, price, status, source) 
                        VALUES (?, ?, ?, ?, 'pending', 'cart')";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iiid", $user_id, $item['product_id'], $item['quantity'], $item['price']);
                $stmt->execute();

                // Remove from cart
                $sql = "DELETE FROM cart WHERE user_id = ? AND product_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $user_id, $item['product_id']);
                $stmt->execute();
            }
            $conn->commit();
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['success' => false, 'message' => 'Failed to process pending orders']);
        }
        break;

    case 'remove':
        // Remove item from pending orders
        $order_id = $_POST['order_id'] ?? 0;
        if ($order_id) {
            $sql = "DELETE FROM pending_orders WHERE id = ? AND user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $order_id, $user_id);
            if ($stmt->execute()) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to remove item']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
        }
        break;

    case 'retry':
        // Move item from pending orders back to cart
        $order_id = $_POST['order_id'] ?? 0;
        if ($order_id) {
            $conn->begin_transaction();
            try {
                // Get pending order details
                $sql = "SELECT * FROM pending_orders WHERE id = ? AND user_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $order_id, $user_id);
                $stmt->execute();
                $order = $stmt->get_result()->fetch_assoc();

                if ($order) {
                    // Add to cart
                    $sql = "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("iii", $user_id, $order['product_id'], $order['quantity']);
                    $stmt->execute();

                    // Remove from pending orders
                    $sql = "DELETE FROM pending_orders WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $order_id);
                    $stmt->execute();

                    $conn->commit();
                    echo json_encode(['success' => true]);
                } else {
                    throw new Exception('Order not found');
                }
            } catch (Exception $e) {
                $conn->rollback();
                echo json_encode(['success' => false, 'message' => 'Failed to retry payment']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}