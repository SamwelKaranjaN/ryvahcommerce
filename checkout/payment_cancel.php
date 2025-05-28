<?php
session_start();
require_once '../config/database.php';
require_once '../includes/pending_orders.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php?redirect=checkout');
    exit();
}

$user_id = $_SESSION['user_id'];
$conn = getDBConnection();

// Move cart items to pending orders
$data = [
    'action' => 'add'
];

$ch = curl_init('http://' . $_SERVER['HTTP_HOST'] . '/includes/pending_orders.php');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);

if ($result['success']) {
    $_SESSION['message'] = 'Your payment was cancelled. Items have been moved to pending payments.';
    header('Location: ../pages/pending_payments.php');
} else {
    $_SESSION['error'] = 'Failed to process your request. Please try again.';
    header('Location: ../pages/cart.php');
}
exit();