<?php
require_once __DIR__ . '/../includes/bootstrap.php';

// Require login
requireLogin();

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['item_id']) || !isset($input['quantity'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

$item_id = (int)$input['item_id'];
$quantity = (int)$input['quantity'];

if ($quantity < 1) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Quantity must be at least 1']);
    exit;
}

try {
    // Get current cart item using MySQLi
    $stmt = $conn->prepare("
        SELECT c.*, p.price 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.id = ? AND c.user_id = ?
    ");
    $stmt->bind_param("ii", $item_id, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $item = $result->fetch_assoc();

    if (!$item) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Cart item not found']);
        exit;
    }

    // Update quantity
    $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("iii", $quantity, $item_id, $_SESSION['user_id']);
    $stmt->execute();

    // Calculate new subtotal
    $subtotal = $item['price'] * $quantity;

    // Get updated cart total
    $stmt = $conn->prepare("
        SELECT SUM(c.quantity * p.price) as total, COUNT(*) as count
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = ?
    ");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $cartData = $result->fetch_assoc();

    echo json_encode([
        'success' => true,
        'subtotal' => $subtotal,
        'total' => $cartData['total'] ?? 0,
        'cart_count' => $cartData['count'] ?? 0
    ]);
} catch (Exception $e) {
    error_log("Cart update error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
