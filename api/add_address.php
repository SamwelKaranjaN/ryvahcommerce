<?php
require_once '../includes/bootstrap.php';

// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set JSON header
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $user_id = $_SESSION['user_id'];

    // Validate required fields
    $required_fields = ['label', 'street', 'city', 'state', 'postal_code', 'country'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            echo json_encode(['success' => false, 'message' => ucfirst($field) . ' is required']);
            exit;
        }
    }

    $label = trim($_POST['label']);
    $street = trim($_POST['street']);
    $city = trim($_POST['city']);
    $state = trim($_POST['state']);
    $postal_code = trim($_POST['postal_code']);
    $country = trim($_POST['country']);
    $is_default = isset($_POST['is_default']) ? 1 : 0;

    // Start transaction
    $conn->begin_transaction();

    // If this is set as default, unset other defaults
    if ($is_default) {
        $stmt = $conn->prepare("UPDATE addresses SET is_default = 0 WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
    }

    // Insert new address
    $stmt = $conn->prepare("INSERT INTO addresses (user_id, label, street, city, state, postal_code, country, is_default) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssssi", $user_id, $label, $street, $city, $state, $postal_code, $country, $is_default);

    if ($stmt->execute()) {
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Address added successfully']);
    } else {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Failed to add address']);
    }
} catch (Exception $e) {
    $conn->rollback();
    error_log("Error adding address: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error'
    ]);
}
