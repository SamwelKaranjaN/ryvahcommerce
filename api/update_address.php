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
    $required_fields = ['id', 'label', 'street', 'city', 'state', 'postal_code', 'country'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            echo json_encode(['success' => false, 'message' => ucfirst($field) . ' is required']);
            exit;
        }
    }

    $id = intval($_POST['id']);
    $label = trim($_POST['label']);
    $street = trim($_POST['street']);
    $city = trim($_POST['city']);
    $state = trim($_POST['state']);
    $postal_code = trim($_POST['postal_code']);
    $country = trim($_POST['country']);
    $is_default = isset($_POST['is_default']) ? 1 : 0;

    // Verify address belongs to user
    $stmt = $conn->prepare("SELECT id FROM addresses WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Address not found']);
        exit;
    }

    // Start transaction
    $conn->begin_transaction();

    // If this is set as default, unset other defaults
    if ($is_default) {
        $stmt = $conn->prepare("UPDATE addresses SET is_default = 0 WHERE user_id = ? AND id != ?");
        $stmt->bind_param("ii", $user_id, $id);
        $stmt->execute();
    }

    // Update address
    $stmt = $conn->prepare("UPDATE addresses SET label = ?, street = ?, city = ?, state = ?, postal_code = ?, country = ?, is_default = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ssssssiiii", $label, $street, $city, $state, $postal_code, $country, $is_default, $id, $user_id);

    if ($stmt->execute()) {
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Address updated successfully']);
    } else {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Failed to update address']);
    }
} catch (Exception $e) {
    $conn->rollback();
    error_log("Error updating address: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error'
    ]);
}
