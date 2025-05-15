<?php
require_once 'bootstrap.php';

if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

$stmt = $conn->prepare("SELECT full_name, email, phone, address FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'user' => $user]);
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'User not found']);
}
?> 