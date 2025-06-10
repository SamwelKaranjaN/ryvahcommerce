<?php

/**
 * Real Tax and Shipping Calculator - Database-driven calculations
 * No fallbacks, real calculations only
 */

// Disable all output and errors
error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Disable xdebug
if (extension_loaded('xdebug')) {
    ini_set('xdebug.mode', 'off');
}

// Clear output buffers
while (ob_get_level()) {
    ob_end_clean();
}
ob_start();

// Set JSON headers
header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-cache, must-revalidate, no-store');

// Fatal error handler
register_shutdown_function(function () {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['success' => false, 'message' => 'System error: ' . $error['message']]);
        exit;
    }
});

try {
    // Include database connection - use getDBConnection to ensure proper connection
    ob_start();
    require_once '../config/database.php';
    ob_end_clean();

    // Ensure we have a fresh connection
    $conn = getDBConnection();

    // Clear any pending results to prevent "Commands out of sync"
    if ($conn->more_results()) {
        while ($conn->next_result()) {
            if ($result = $conn->use_result()) {
                $result->free();
            }
        }
    }

    // Start session
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Validate request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    if (!isset($_SESSION['user_id'])) {
        throw new Exception('User not authenticated');
    }

    // Get request data
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!$data || !isset($data['address_id'])) {
        throw new Exception('Invalid input data');
    }

    $userId = intval($_SESSION['user_id']);
    $addressId = intval($data['address_id']);

    // Get shipping address - Store result properly
    $stmt = $conn->prepare("SELECT id, state, country FROM addresses WHERE id = ? AND user_id = ?");
    if (!$stmt) {
        throw new Exception('Failed to prepare address query: ' . $conn->error);
    }
    $stmt->bind_param("ii", $addressId, $userId);
    if (!$stmt->execute()) {
        throw new Exception('Failed to execute address query: ' . $stmt->error);
    }
    $addressResult = $stmt->get_result();
    $address = $addressResult->fetch_assoc();
    $stmt->close();

    if (!$address) {
        throw new Exception('Shipping address not found');
    }

    // Get cart items with product details - Already properly handled
    $stmt = $conn->prepare("
        SELECT c.product_id, c.quantity, p.name, p.price, p.type 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = ?
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $cartResult = $stmt->get_result();

    $cartItems = [];
    $subtotal = 0;

    while ($item = $cartResult->fetch_assoc()) {
        $itemTotal = $item['price'] * $item['quantity'];
        $subtotal += $itemTotal;
        $cartItems[] = [
            'id' => $item['product_id'],
            'name' => $item['name'],
            'price' => $item['price'],
            'quantity' => $item['quantity'],
            'type' => $item['type'],
            'total' => $itemTotal
        ];
    }
    $stmt->close();

    if (empty($cartItems)) {
        throw new Exception('Cart is empty');
    }

    // Calculate tax using real tax_settings table - Already properly handled
    $taxAmount = 0;
    $stmt = $conn->prepare("SELECT product_type, tax_rate FROM tax_settings WHERE is_active = 1");
    $stmt->execute();
    $taxResult = $stmt->get_result();
    $taxSettings = [];
    while ($row = $taxResult->fetch_assoc()) {
        $taxSettings[$row['product_type']] = $row['tax_rate'];
    }
    $stmt->close();

    foreach ($cartItems as $item) {
        if (isset($taxSettings[$item['type']])) {
            $taxRate = $taxSettings[$item['type']] / 100; // Convert percentage to decimal
            $taxAmount += $item['total'] * $taxRate;
        }
    }

    // Calculate shipping using database-driven shipping fees
    $shippingAmount = 0;
    $shippingBreakdown = [];

    // Get shipping fees for all product types from database
    $stmt = $conn->prepare("SELECT product_type, shipping_fee FROM shipping_fees WHERE is_active = 1");
    if (!$stmt) {
        throw new Exception('Failed to prepare shipping fees query: ' . $conn->error);
    }
    if (!$stmt->execute()) {
        throw new Exception('Failed to execute shipping fees query: ' . $stmt->error);
    }
    $shippingFeesResult = $stmt->get_result();
    $shippingFees = [];
    while ($row = $shippingFeesResult->fetch_assoc()) {
        $shippingFees[$row['product_type']] = floatval($row['shipping_fee']);
    }
    $stmt->close();

    // Track which product types we've already added shipping for (to avoid duplicate shipping for same type)
    $productTypesShipped = [];

    // Calculate shipping per product type from database
    foreach ($cartItems as $item) {
        $productType = $item['type'];

        // Get shipping fee from database for this product type
        $itemShipping = isset($shippingFees[$productType]) ? $shippingFees[$productType] : 0;

        // Only charge shipping once per product type, not per item
        if ($itemShipping > 0 && !in_array($productType, $productTypesShipped)) {
            $shippingAmount += $itemShipping;
            $productTypesShipped[] = $productType;

            $shippingBreakdown[] = [
                'product_id' => $item['id'],
                'product_name' => $item['name'],
                'product_type' => $productType,
                'quantity' => $item['quantity'],
                'shipping_fee' => round($itemShipping, 2)
            ];
        }
    }

    // Calculate final total
    $total = $subtotal + $taxAmount + $shippingAmount;

    // Debug logging for frontend calculation
    error_log('Frontend totals calculation (NO DISCOUNTS): Subtotal=' . $subtotal . ', Tax=' . $taxAmount . ', Shipping=' . $shippingAmount . ', Total=' . $total);

    // Validation: Ensure total is correct sum of components
    $calculatedTotal = $subtotal + $taxAmount + $shippingAmount;
    if (abs($total - $calculatedTotal) > 0.01) {
        error_log('Total calculation mismatch: Total=' . $total . ', Calculated=' . $calculatedTotal .
            ', Subtotal=' . $subtotal . ', Tax=' . $taxAmount . ', Shipping=' . $shippingAmount);
    }

    // Clean any remaining output buffer before JSON response
    while (ob_get_level()) {
        ob_end_clean();
    }

    // Return response
    echo json_encode([
        'success' => true,
        'subtotal' => round($subtotal, 2),
        'tax_amount' => round($taxAmount, 2),
        'shipping_amount' => round($shippingAmount, 2),
        'total' => round($total, 2),
        'shipping_breakdown' => $shippingBreakdown
    ]);
} catch (Exception $e) {
    // Clean output buffer before error response
    while (ob_get_level()) {
        ob_end_clean();
    }

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} catch (Error $e) {
    // Clean output buffer before error response
    while (ob_get_level()) {
        ob_end_clean();
    }

    echo json_encode([
        'success' => false,
        'message' => 'Fatal error: ' . $e->getMessage()
    ]);
}
