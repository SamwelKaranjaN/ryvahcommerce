<?php
session_start();
require_once '../config/database.php';
require_once '../includes/order/OrderProcessor.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php?redirect=checkout/payment_success.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$conn = getDBConnection();

// Get the invoice number from the session
if (!isset($_SESSION['last_invoice'])) {
    header('Location: ../index.php');
    exit();
}

$invoice_number = $_SESSION['last_invoice'];

// Get order details
$sql = "SELECT o.*, GROUP_CONCAT(p.type) as product_types
        FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        JOIN products p ON oi.product_id = p.id
        WHERE o.invoice_number = ? AND o.user_id = ?
        GROUP BY o.id";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $invoice_number, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if (!$order) {
    header('Location: ../index.php');
    exit();
}

// Get order items for OrderProcessor
$sql = "SELECT oi.*, p.name, p.type, p.price 
        FROM order_items oi 
        JOIN products p ON oi.product_id = p.id 
        WHERE oi.order_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order['id']);
$stmt->execute();
$result = $stmt->get_result();
$order_items = $result->fetch_all(MYSQLI_ASSOC);

// Complete the order
$orderProcessor = new OrderProcessor(
    $conn,
    ['id' => $user_id],
    ['items' => $order_items, 'total' => $order['total_amount']]
);
$orderProcessor->completeOrder($invoice_number);

// Clear the cart
unset($_SESSION['cart']);

// Determine where to redirect based on product types
$product_types = explode(',', $order['product_types']);
$has_ebooks = in_array('ebook', $product_types);
$has_physical = in_array('book', $product_types) || in_array('paint', $product_types);

// Store success message in session
$_SESSION['success_message'] = "Payment successful! Your order has been placed.";

// Redirect based on product types
if ($has_ebooks && !$has_physical) {
    // If only ebooks, redirect to my_ebooks page
    header('Location: ../pages/my_ebooks.php');
} else {
    // If physical products or mixed, redirect to orders page
    header('Location: ../pages/orders.php');
}
exit();