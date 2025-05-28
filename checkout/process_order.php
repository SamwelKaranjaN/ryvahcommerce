<?php
session_start();
require_once '../config/database.php';
require_once '../vendor/autoload.php';
require_once '../includes/functions.php';
require_once '../includes/order/OrderProcessor.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$user_id = $_SESSION['user_id'];
$conn = getDBConnection();

// Validate required fields
$required_fields = ['billing_name', 'billing_email', 'billing_phone', 'billing_address', 'billing_city', 'billing_state', 'billing_postal', 'payment_method'];
$missing_fields = [];

foreach ($required_fields as $field) {
    if (!isset($data[$field]) || empty($data[$field])) {
        $missing_fields[] = $field;
    }
}

if (!empty($missing_fields)) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required fields: ' . implode(', ', $missing_fields)
    ]);
    exit();
}

// Get cart items
$sql = "SELECT c.*, p.name, p.type, p.thumbs, p.price 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$items = [];
$total = 0;
while ($row = $result->fetch_assoc()) {
    $items[] = $row;
    $total += $row['price'] * $row['quantity'];
}

if (empty($items)) {
    echo json_encode([
        'success' => false,
        'message' => 'Cart is empty'
    ]);
    exit();
}

try {
    // Create order processor
    $orderProcessor = new OrderProcessor($conn, ['id' => $user_id], ['items' => $items]);

    // Create pending order
    $result = $orderProcessor->createPendingOrder([
        'name' => $data['billing_name'],
        'email' => $data['billing_email'],
        'phone' => $data['billing_phone'],
        'address' => $data['billing_address'],
        'city' => $data['billing_city'],
        'state' => $data['billing_state'],
        'postal_code' => $data['billing_postal']
    ]);

    if ($result['success']) {
        // Store order ID in session
        $_SESSION['current_order_id'] = $result['order_id'];
        $_SESSION['current_invoice'] = $result['invoice_number'];

        // Return success response
        echo json_encode([
            'success' => true,
            'order_id' => $result['order_id'],
            'invoice_number' => $result['invoice_number'],
            'total' => $total,
            'redirect_url' => $data['payment_method'] === 'paypal' ? 'process_paypal.php' : 'process_stripe.php'
        ]);
    } else {
        throw new Exception($result['message']);
    }
} catch (Exception $e) {
    error_log("Error creating order: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to create order: ' . $e->getMessage()
    ]);
}