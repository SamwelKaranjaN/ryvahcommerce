<?php
require_once 'bootstrap.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate required fields
        $required_fields = ['label', 'street', 'city', 'state', 'postal_code', 'country'];
        foreach ($required_fields as $field) {
            if (!isset($_POST[$field]) || empty($_POST[$field])) {
                throw new Exception("Missing required field: {$field}");
            }
        }

        // Sanitize input
        $label = htmlspecialchars(trim($_POST['label']));
        $street = htmlspecialchars(trim($_POST['street']));
        $city = htmlspecialchars(trim($_POST['city']));
        $state = htmlspecialchars(trim($_POST['state']));
        $postal_code = htmlspecialchars(trim($_POST['postal_code']));
        $country = htmlspecialchars(trim($_POST['country']));
        $is_default = isset($_POST['is_default']) ? 1 : 0;

        // Start transaction
        $conn->begin_transaction();

        try {
            // If this is set as default, unset any existing default address
            if ($is_default) {
                $conn->query("UPDATE addresses SET is_default = 0 WHERE user_id = {$user_id}");
            }

            // Insert new address
            $stmt = $conn->prepare("INSERT INTO addresses (user_id, label, street, city, state, postal_code, country, is_default) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("issssssi", $user_id, $label, $street, $city, $state, $postal_code, $country, $is_default);
            $stmt->execute();
            $address_id = $conn->insert_id;

            // Get the newly created address
            $stmt = $conn->prepare("SELECT * FROM addresses WHERE id = ?");
            $stmt->bind_param("i", $address_id);
            $stmt->execute();
            $address = $stmt->get_result()->fetch_assoc();

            // Commit transaction
            $conn->commit();

            // Return success response
            echo json_encode([
                'success' => true,
                'message' => 'Address added successfully',
                'address' => $address
            ]);
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            throw $e;
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
    exit;
}

// Handle GET request
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        // Get all addresses for user
        $stmt = $conn->prepare("SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $addresses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        echo json_encode([
            'success' => true,
            'addresses' => $addresses
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
    exit;
}

// Handle DELETE request
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    try {
        // Get address ID from request
        $address_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$address_id) {
            throw new Exception('Invalid address ID');
        }

        // Check if address belongs to user
        $stmt = $conn->prepare("SELECT id FROM addresses WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $address_id, $user_id);
        $stmt->execute();
        if (!$stmt->get_result()->fetch_assoc()) {
            throw new Exception('Address not found');
        }

        // Delete address
        $stmt = $conn->prepare("DELETE FROM addresses WHERE id = ?");
        $stmt->bind_param("i", $address_id);
        $stmt->execute();

        echo json_encode([
            'success' => true,
            'message' => 'Address deleted successfully'
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
    exit;
}

// Handle PUT request (update address)
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    try {
        // Get address data from request
        parse_str(file_get_contents("php://input"), $_PUT);

        // Validate required fields
        $required_fields = ['id', 'label', 'street', 'city', 'state', 'postal_code', 'country'];
        foreach ($required_fields as $field) {
            if (!isset($_PUT[$field]) || empty($_PUT[$field])) {
                throw new Exception("Missing required field: {$field}");
            }
        }

        // Sanitize input
        $address_id = (int)$_PUT['id'];
        $label = htmlspecialchars(trim($_PUT['label']));
        $street = htmlspecialchars(trim($_PUT['street']));
        $city = htmlspecialchars(trim($_PUT['city']));
        $state = htmlspecialchars(trim($_PUT['state']));
        $postal_code = htmlspecialchars(trim($_PUT['postal_code']));
        $country = htmlspecialchars(trim($_PUT['country']));
        $is_default = isset($_PUT['is_default']) ? 1 : 0;

        // Check if address belongs to user
        $stmt = $conn->prepare("SELECT id FROM addresses WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $address_id, $user_id);
        $stmt->execute();
        if (!$stmt->get_result()->fetch_assoc()) {
            throw new Exception('Address not found');
        }

        // Start transaction
        $conn->begin_transaction();

        try {
            // If this is set as default, unset any existing default address
            if ($is_default) {
                $conn->query("UPDATE addresses SET is_default = 0 WHERE user_id = {$user_id}");
            }

            // Update address
            $stmt = $conn->prepare("UPDATE addresses SET label = ?, street = ?, city = ?, state = ?, postal_code = ?, country = ?, is_default = ? WHERE id = ?");
            $stmt->bind_param("ssssssii", $label, $street, $city, $state, $postal_code, $country, $is_default, $address_id);
            $stmt->execute();

            // Get updated address
            $stmt = $conn->prepare("SELECT * FROM addresses WHERE id = ?");
            $stmt->bind_param("i", $address_id);
            $stmt->execute();
            $address = $stmt->get_result()->fetch_assoc();

            // Commit transaction
            $conn->commit();

            echo json_encode([
                'success' => true,
                'message' => 'Address updated successfully',
                'address' => $address
            ]);
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            throw $e;
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
    exit;
}
