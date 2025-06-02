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
    
    // Unset all defaults for this user
    $stmt = $conn->prepare("UPDATE addresses SET is_default = 0 WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    
    // Set new default
    $stmt = $conn->prepare("UPDATE addresses SET is_default = 1 WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, $user_id);
    
    if ($stmt->execute()) {
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Default address updated']);
    } else {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Failed to update default address']);
    }
    
} catch (Exception $e) {
    $conn->rollback();
    error_log("Error setting default address: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error'
    ]);
}
?> 