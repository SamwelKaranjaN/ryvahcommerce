<?php

/**
 * Shipping Helper Functions
 * Handles all shipping-related calculations and operations
 */

require_once 'db_connect.php';

/**
 * Get shipping fee for a specific product type
 * @param string $product_type - The product type (paint, ebook, book)
 * @return float - The shipping fee amount
 */
function getShippingFeeByProductType($product_type)
{
    $conn = getDBConnection();

    try {
        $stmt = $conn->prepare("SELECT shipping_fee FROM shipping_settings WHERE product_type = ? AND is_active = 1");
        $stmt->bind_param("s", $product_type);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            return floatval($row['shipping_fee']);
        }

        // Return default fees if not found in database
        $default_fees = [
            'paint' => 5.00,
            'ebook' => 0.00,
            'book' => 7.00
        ];

        return isset($default_fees[$product_type]) ? $default_fees[$product_type] : 0.00;
    } catch (Exception $e) {
        error_log("Error getting shipping fee: " . $e->getMessage());
        // Return default fees on error
        $default_fees = [
            'paint' => 5.00,
            'ebook' => 0.00,
            'book' => 7.00
        ];

        return isset($default_fees[$product_type]) ? $default_fees[$product_type] : 0.00;
    }
}

/**
 * Calculate total shipping for cart items
 * @param array $cart_items - Array of cart items with product details
 * @return array - Array containing total shipping and breakdown by product type
 */
function calculateCartShipping($cart_items)
{
    $shipping_breakdown = [];
    $total_shipping = 0.00;
    $processed_types = []; // Track which product types we've already processed

    foreach ($cart_items as $item) {
        $product_type = $item['type'];
        $quantity = intval($item['quantity']);

        // Only calculate shipping once per product type, not per item
        if (in_array($product_type, $processed_types)) {
            continue; // Skip if we've already processed this product type
        }

        // Get shipping fee for this product type from database
        $shipping_fee = getShippingFeeByProductType($product_type);

        // For digital products (ebooks), shipping is usually 0
        if ($product_type === 'ebook' || $shipping_fee <= 0) {
            $item_shipping = 0.00;
        } else {
            // Use flat database fee - no per-item calculation
            $item_shipping = $shipping_fee;
            $total_shipping += $shipping_fee;
            $processed_types[] = $product_type;
        }

        // Store shipping info for this item
        $shipping_breakdown['items'][] = [
            'product_id' => $item['id'],
            'product_type' => $product_type,
            'quantity' => $quantity,
            'shipping_fee' => $item_shipping
        ];
    }

    return [
        'total_shipping' => $total_shipping,
        'breakdown' => $shipping_breakdown
    ];
}

/**
 * Calculate shipping for a single product
 * @param int $product_id - The product ID
 * @param int $quantity - The quantity (no longer affects shipping calculation)
 * @return float - The shipping amount
 */
function calculateProductShipping($product_id, $quantity = 1)
{
    $conn = getDBConnection();

    try {
        // Get product type
        $stmt = $conn->prepare("SELECT type FROM products WHERE id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $product_type = $row['type'];
            // Return flat database fee regardless of quantity
            return getShippingFeeByProductType($product_type);
        }

        return 0.00;
    } catch (Exception $e) {
        error_log("Error calculating product shipping: " . $e->getMessage());
        return 0.00;
    }
}

/**
 * Get all active shipping settings
 * @return array - Array of all shipping settings
 */
function getAllShippingSettings()
{
    $conn = getDBConnection();

    try {
        $stmt = $conn->prepare("SELECT * FROM shipping_settings WHERE is_active = 1 ORDER BY product_type");
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_all(MYSQLI_ASSOC);
    } catch (Exception $e) {
        error_log("Error getting shipping settings: " . $e->getMessage());
        return [];
    }
}

/**
 * Check if shipping is required for a product type
 * @param string $product_type - The product type
 * @return bool - True if shipping is required, false otherwise
 */
function isShippingRequired($product_type)
{
    $shipping_fee = getShippingFeeByProductType($product_type);
    return $shipping_fee > 0;
}

/**
 * Format shipping fee for display
 * @param float $fee - The shipping fee amount
 * @return string - Formatted shipping fee string
 */
function formatShippingFee($fee)
{
    if ($fee <= 0) {
        return "FREE";
    }
    return "$" . number_format($fee, 2);
}

/**
 * Get shipping summary for order
 * @param array $order_items - Array of order items
 * @return array - Shipping summary with details
 */
function getOrderShippingSummary($order_items)
{
    $summary = [
        'total_shipping' => 0.00,
        'items_with_shipping' => [],
        'free_shipping_items' => [],
        'shipping_methods' => []
    ];

    $product_types_processed = [];

    foreach ($order_items as $item) {
        $product_type = $item['type'] ?? 'book'; // default to book if not specified
        $shipping_fee = getShippingFeeByProductType($product_type);

        if ($shipping_fee > 0) {
            // Only charge shipping once per product type
            if (!in_array($product_type, $product_types_processed)) {
                $summary['total_shipping'] += $shipping_fee;
                $summary['shipping_methods'][] = [
                    'product_type' => $product_type,
                    'fee' => $shipping_fee
                ];
                $product_types_processed[] = $product_type;
            }

            $summary['items_with_shipping'][] = $item;
        } else {
            $summary['free_shipping_items'][] = $item;
        }
    }

    return $summary;
}

/**
 * Update shipping settings
 * @param string $product_type - The product type
 * @param float $shipping_fee - The new shipping fee
 * @param int $admin_id - The admin user ID making the change
 * @return bool - True on success, false on failure
 */
function updateShippingSetting($product_type, $shipping_fee, $admin_id)
{
    $conn = getDBConnection();

    try {
        $conn->begin_transaction();

        // Get old fee for logging
        $stmt = $conn->prepare("SELECT shipping_fee FROM shipping_settings WHERE product_type = ?");
        $stmt->bind_param("s", $product_type);
        $stmt->execute();
        $result = $stmt->get_result();
        $old_fee = 0.00;

        if ($row = $result->fetch_assoc()) {
            $old_fee = $row['shipping_fee'];
        }

        // Update the shipping fee
        $stmt = $conn->prepare("
            INSERT INTO shipping_settings (product_type, shipping_fee, is_active) 
            VALUES (?, ?, 1) 
            ON DUPLICATE KEY UPDATE 
            shipping_fee = VALUES(shipping_fee), 
            updated_at = CURRENT_TIMESTAMP
        ");
        $stmt->bind_param("sd", $product_type, $shipping_fee);
        $stmt->execute();

        // Log the change
        $stmt = $conn->prepare("INSERT INTO admin_logs (admin_id, action, details) VALUES (?, 'update_shipping', ?)");
        $details = json_encode([
            'product_type' => $product_type,
            'old_fee' => $old_fee,
            'new_fee' => $shipping_fee
        ]);
        $stmt->bind_param("is", $admin_id, $details);
        $stmt->execute();

        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error updating shipping setting: " . $e->getMessage());
        return false;
    }
}
