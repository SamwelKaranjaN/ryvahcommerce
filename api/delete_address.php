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

    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['id']) || empty($input['id'])) {
        echo json_encode(['success' => false, 'message' => 'Address ID is required']);
        exit;
    }

    $id = intval($input['id']);

    // Verify address belongs to user and check if it's default
    $stmt = $conn->prepare("SELECT id, is_default FROM addresses WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $address = $result->fetch_assoc();

    if (!$address) {
        echo json_encode(['success' => false, 'message' => 'Address not found']);
        exit;
    }

    // Prevent deletion of default address
    if ($address['is_default']) {
        echo json_encode(['success' => false, 'message' => 'Cannot delete default address. Set another address as default first.']);
        exit;
    }

    // Delete address
    $stmt = $conn->prepare("DELETE FROM addresses WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, $user_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Address deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete address']);
    }
} catch (Exception $e) {
    error_log("Error deleting address: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error'
    ]);
}
