<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit();
}

// Validate required fields
$required_fields = ['name', 'email', 'phone', 'address', 'city', 'state', 'postal'];
foreach ($required_fields as $field) {
    if (empty($data[$field])) {
        echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
        exit();
    }
}

// Validate email
if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit();
}

// Validate phone (basic validation)
if (!preg_match('/^\+?[\d\s-]{10,}$/', $data['phone'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid phone number format']);
    exit();
}

// Validate postal code (Canadian format)
if (!preg_match('/^[A-Za-z]\d[A-Za-z][ -]?\d[A-Za-z]\d$/', $data['postal'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid postal code format']);
    exit();
}

// Save to session
$_SESSION['temp_billing'] = [
    'name' => $data['name'],
    'email' => $data['email'],
    'phone' => $data['phone'],
    'address' => $data['address'],
    'city' => $data['city'],
    'state' => $data['state'],
    'postal' => $data['postal']
];

// If user wants to save address for future orders
if (isset($data['save_address']) && $data['save_address']) {
    $conn = getDBConnection();

    // Update user's billing information
    $sql = "UPDATE users SET 
            address = ?, 
            city = ?, 
            state = ?, 
            postal_code = ? 
            WHERE id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "ssssi",
        $data['address'],
        $data['city'],
        $data['state'],
        $data['postal'],
        $_SESSION['user_id']
    );

    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'message' => 'Error saving address to database']);
        exit();
    }
}

echo json_encode(['success' => true]);
