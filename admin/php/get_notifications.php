<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

try {
    // Get pending orders count
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'");
    $stmt->execute();
    $orders = $stmt->get_result()->fetch_assoc()['count'];

    // Get pending support tickets count
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM support_tickets WHERE status = 'open'");
    $stmt->execute();
    $support = $stmt->get_result()->fetch_assoc()['count'];

    // Calculate total notifications
    $total = $orders + $support;

    echo json_encode([
        'success' => true,
        'notifications' => [
            'total' => $total,
            'orders' => $orders,
            'support' => $support
        ]
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching notifications'
    ]);
}
?> 