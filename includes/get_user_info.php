<?php
require_once 'bootstrap.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Get user information
$stmt = $conn->prepare("
    SELECT 
        full_name, 
        email, 
        phone, 
        address,
        city,
        state,
        postal_code
    FROM users 
    WHERE id = ?
");

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($user = $result->fetch_assoc()) {
    echo json_encode([
        'success' => true,
        'data' => [
            'full_name' => $user['full_name'],
            'email' => $user['email'],
            'phone' => $user['phone'],
            'address' => $user['address'],
            'city' => $user['city'],
            'state' => $user['state'],
            'postal_code' => $user['postal_code']
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'User not found']);
}
