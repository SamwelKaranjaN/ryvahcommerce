<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Check if purchase_id is provided
if (!isset($_GET['purchase_id'])) {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$purchase_id = $_GET['purchase_id'];
$conn = getDBConnection();

// Get purchase details and verify ownership
$sql = "SELECT up.*, p.filepath, p.name, p.type
        FROM user_purchases up
        JOIN products p ON up.product_id = p.id
        WHERE up.id = ? AND up.user_id = ? AND p.type = 'ebook'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $purchase_id, $user_id);
$stmt->execute();
$purchase = $stmt->get_result()->fetch_assoc();

if (!$purchase) {
    header('Location: index.php');
    exit();
}

// Check if download limit is reached (assuming 3 downloads per purchase)
if ($purchase['download_count'] >= 3) {
    header('Location: pages/order_success.php?order_id=' . $purchase['order_id'] . '&error=download_limit');
    exit();
}

// Update download count
$sql = "UPDATE user_purchases 
        SET download_count = download_count + 1,
            last_download = CURRENT_TIMESTAMP
        WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $purchase_id);
$stmt->execute();

// Get file path
$file_path = $purchase['filepath'];

// Check if file exists
if (!file_exists($file_path)) {
    header('Location: pages/order_success.php?order_id=' . $purchase['order_id'] . '&error=file_not_found');
    exit();
}

// Set headers for file download
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
header('Content-Length: ' . filesize($file_path));
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Output file
readfile($file_path);
exit();
