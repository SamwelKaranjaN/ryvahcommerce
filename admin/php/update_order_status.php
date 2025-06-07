<?php
require_once 'db_connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $order_id = $input['order_id'] ?? null;
    $status = $input['status'] ?? null;
    $note = $input['note'] ?? '';

    if (!$order_id || !$status) {
        echo json_encode(['success' => false, 'message' => 'Order ID and status are required']);
        exit;
    }

    // Validate status
    $valid_statuses = ['pending', 'processing', 'completed', 'failed', 'refunded'];
    if (!in_array($status, $valid_statuses)) {
        echo json_encode(['success' => false, 'message' => 'Invalid status']);
        exit;
    }

    // Start transaction
    $conn->autocommit(false);

    try {
        // Update order status
        $update_query = "UPDATE orders SET payment_status = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("si", $status, $order_id);

        if (!$stmt->execute()) {
            throw new Exception('Failed to update order status');
        }

        // Check if order was found and updated
        if ($stmt->affected_rows === 0) {
            throw new Exception('Order not found');
        }

        // Add to status history
        $history_query = "INSERT INTO order_status_history (order_id, status, notes, created_at) VALUES (?, ?, ?, NOW())";
        $stmt = $conn->prepare($history_query);
        $stmt->bind_param("iss", $order_id, $status, $note);

        if (!$stmt->execute()) {
            throw new Exception('Failed to add status history');
        }

        // Commit transaction
        $conn->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Order status updated successfully'
        ]);
    } catch (Exception $e) {
        // Rollback transaction
        $conn->rollback();
        throw $e;
    }
} catch (Exception $e) {
    error_log("Error updating order status: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    $conn->autocommit(true);
}