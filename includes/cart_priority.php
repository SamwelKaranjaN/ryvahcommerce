<?php

/**
 * Cart Transfer Priority Handler
 * 
 * This file ensures that cart transfer is the FIRST thing that happens
 * when a guest user logs in with items in their session cart.
 */

/**
 * IMMEDIATE cart transfer function that must be called first after authentication
 * This function MUST be called before any other operations after user login
 * 
 * @param int $user_id The user ID who just logged in
 * @return array Transfer result with success status and details
 */
function immediateCartTransfer($user_id)
{
    // Include cart functions
    require_once __DIR__ . '/cart.php';

    // Log the start of the priority transfer
    error_log("=== PRIORITY CART TRANSFER STARTED FOR USER {$user_id} ===");

    // Check what cart items exist in session before transfer
    $session_items = [];
    if (!empty($_SESSION['cart'])) {
        $session_items['cart'] = count($_SESSION['cart']);
    }
    if (!empty($_SESSION['checkout_cart'])) {
        $session_items['checkout_cart'] = count($_SESSION['checkout_cart']);
    }
    if (!empty($_SESSION['temp_cart'])) {
        $session_items['temp_cart'] = count($_SESSION['temp_cart']);
    }

    if (!empty($session_items)) {
        error_log("CART TRANSFER: Found session items: " . json_encode($session_items));

        // Perform the transfer immediately
        $result = transferSessionCartToDatabase($user_id);

        // Log the completion
        if ($result['success'] && $result['transferred'] > 0) {
            error_log("=== PRIORITY CART TRANSFER COMPLETED: {$result['transferred']} items transferred ===");
        } else if (!empty($result['errors'])) {
            error_log("=== PRIORITY CART TRANSFER FAILED: " . implode(', ', $result['errors']) . " ===");
        }

        return $result;
    } else {
        error_log("=== NO CART ITEMS TO TRANSFER FOR USER {$user_id} ===");
        return ['success' => true, 'transferred' => 0, 'errors' => [], 'merged' => 0];
    }
}

/**
 * Verify that cart transfer happened successfully
 * 
 * @param int $user_id User ID
 * @param array $transfer_result Result from cart transfer
 * @return bool True if transfer was successful or no items to transfer
 */
function verifyCartTransferSuccess($user_id, $transfer_result)
{
    global $conn;

    // If no items were transferred, check if sessions are actually empty
    if ($transfer_result['transferred'] === 0) {
        $has_session_items = !empty($_SESSION['cart']) ||
            !empty($_SESSION['checkout_cart']) ||
            !empty($_SESSION['temp_cart']);

        if ($has_session_items) {
            error_log("WARNING: Session still has cart items after transfer for user {$user_id}");
            return false;
        }
        return true; // No items to transfer is success
    }

    // Verify items are actually in the database
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $db_count = $result->fetch_assoc()['count'];

    if ($db_count > 0) {
        error_log("CART TRANSFER VERIFICATION: User {$user_id} now has {$db_count} items in database cart");
        return true;
    } else {
        error_log("ERROR: Cart transfer reported success but no items found in database for user {$user_id}");
        return false;
    }
}

/**
 * Emergency cart recovery function
 * If cart transfer fails, this tries to preserve the session cart
 * 
 * @param int $user_id User ID
 */
function emergencyCartPreservation($user_id)
{
    $emergency_cart = [];

    if (!empty($_SESSION['cart'])) {
        $emergency_cart['cart'] = $_SESSION['cart'];
    }
    if (!empty($_SESSION['checkout_cart'])) {
        $emergency_cart['checkout_cart'] = $_SESSION['checkout_cart'];
    }
    if (!empty($_SESSION['temp_cart'])) {
        $emergency_cart['temp_cart'] = $_SESSION['temp_cart'];
    }

    if (!empty($emergency_cart)) {
        // Store in session for later recovery
        $_SESSION['emergency_cart_backup'] = $emergency_cart;
        $_SESSION['emergency_cart_backup_user'] = $user_id;
        $_SESSION['emergency_cart_backup_time'] = time();

        error_log("EMERGENCY: Cart items preserved for user {$user_id}: " . json_encode($emergency_cart));

        // Set user message
        $_SESSION['cart_emergency'] = "We're having trouble transferring your cart items. Please contact support if items are missing.";
    }
}
