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

    if (!$order_id) {
        echo json_encode(['success' => false, 'message' => 'Order ID is required']);
        exit;
    }

    // Fetch order details with user information
    $order_query = "SELECT o.*, u.full_name, u.email, u.phone 
                    FROM orders o 
                    JOIN users u ON o.user_id = u.id 
                    WHERE o.id = ?";

    $stmt = $conn->prepare($order_query);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $order_result = $stmt->get_result();

    if ($order_result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Order not found']);
        exit;
    }

    $order = $order_result->fetch_assoc();

    // Fetch order items with product details
    $items_query = "SELECT oi.*, p.name, p.sku, p.description, p.thumbs, p.type, p.author
                    FROM order_items oi 
                    JOIN products p ON oi.product_id = p.id 
                    WHERE oi.order_id = ?
                    ORDER BY oi.id";

    $stmt = $conn->prepare($items_query);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $items_result = $stmt->get_result();

    $items = [];
    while ($item = $items_result->fetch_assoc()) {
        $items[] = $item;
    }

    // Fetch order status history/notes
    $notes_query = "SELECT * FROM order_status_history 
                    WHERE order_id = ? 
                    ORDER BY created_at DESC";

    $stmt = $conn->prepare($notes_query);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $notes_result = $stmt->get_result();

    $notes = [];
    while ($note = $notes_result->fetch_assoc()) {
        $notes[] = $note;
    }

    // Format the shipping address if it exists
    if ($order['shipping_address']) {
        $shipping_data = json_decode($order['shipping_address'], true);
        if ($shipping_data) {
            $order['shipping_formatted'] = sprintf(
                "%s, %s, %s %s, %s",
                $shipping_data['street'] ?? '',
                $shipping_data['city'] ?? '',
                $shipping_data['state'] ?? '',
                $shipping_data['postal_code'] ?? '',
                $shipping_data['country'] ?? ''
            );
        }
    }

    echo json_encode([
        'success' => true,
        'order' => $order,
        'items' => $items,
        'notes' => $notes
    ]);
} catch (Exception $e) {
    error_log("Error fetching order details: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
}