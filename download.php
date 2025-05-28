<?php
session_start();
require_once 'config/database.php';
require_once 'includes/download/DownloadHandler.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=download.php');
    exit();
}

// Check if product ID is provided
if (!isset($_GET['product_id'])) {
    die('Product ID is required');
}

$product_id = (int)$_GET['product_id'];
$user_id = $_SESSION['user_id'];
$conn = getDBConnection();

try {
    // Initialize download handler
    $downloadHandler = new DownloadHandler($conn, $user_id);

    // Process download
    $result = $downloadHandler->processDownload($product_id);

    if (!$result['success']) {
        die($result['message']);
    }

    // Get file path and name
    $filepath = $result['filepath'];
    $filename = $result['filename'];

    // Check if file exists
    if (!file_exists($filepath)) {
        die('File not found');
    }

    // Get file extension
    $extension = pathinfo($filepath, PATHINFO_EXTENSION);

    // Set headers for download
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $filename . '.' . $extension . '"');
    header('Content-Length: ' . filesize($filepath));
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');

    // Output file
    readfile($filepath);
    exit();
} catch (Exception $e) {
    error_log("Download error: " . $e->getMessage());
    die('An error occurred while processing your download. Please try again later.');
}