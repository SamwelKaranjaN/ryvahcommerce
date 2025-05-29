<?php
require_once '../includes/bootstrap.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Check if product ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_messages'] = ['Invalid product ID'];
    header('Location: downloads.php');
    exit();
}

$product_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// Verify that the user has purchased this product and payment is completed
$sql = "SELECT p.*, up.id as purchase_id 
        FROM products p
        JOIN user_purchases up ON p.id = up.product_id
        JOIN orders o ON up.order_id = o.id
        WHERE p.id = ? AND up.user_id = ? 
        AND o.payment_status = 'completed'
        AND p.type = 'ebook'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $product_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    $_SESSION['error_messages'] = ['You do not have permission to download this file'];
    header('Location: downloads.php');
    exit();
}

// Get the file path
$file_path = '../uploads/products/' . $product['file_path'];

if (!file_exists($file_path)) {
    $_SESSION['error_messages'] = ['File not found'];
    header('Location: downloads.php');
    exit();
}

// Update download statistics
$sql = "UPDATE user_purchases 
        SET download_count = download_count + 1,
            last_download = NOW()
        WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product['purchase_id']);
$stmt->execute();

// Set headers for file download
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($product['file_path']) . '"');
header('Content-Length: ' . filesize($file_path));
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Output file content
readfile($file_path);
exit(); 