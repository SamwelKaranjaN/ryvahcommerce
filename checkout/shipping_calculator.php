<?php

/**
 * Shipping Calculator for Product-Type Specific Shipping Fees
 * This file handles all shipping calculations based on product types
 */

// Note: This file assumes that a database connection ($conn) is already available
// It should be included after establishing a database connection

/**
 * Get shipping fees from database
 */
function getShippingSettings()
{
    static $shipping_settings = null;

    if ($shipping_settings === null) {
        global $conn;

        try {
            $stmt = $conn->prepare("SELECT product_type, shipping_fee, is_active, applies_after_tax FROM shipping_fees WHERE is_active = 1");
            $stmt->execute();
            $result = $stmt->get_result();

            $shipping_settings = [];
            while ($row = $result->fetch_assoc()) {
                $shipping_settings[$row['product_type']] = [
                    'fee' => floatval($row['shipping_fee']),
                    'applies_after_tax' => intval($row['applies_after_tax'])
                ];
            }
            $stmt->close();
        } catch (Exception $e) {
            error_log('Error fetching shipping settings: ' . $e->getMessage());
            $shipping_settings = [];
        }
    }

    return $shipping_settings;
}

/**
 * Calculate shipping for a single item
 */
function calculateItemShipping($productType, $quantity, $itemTotal, $taxAmount = 0)
{
    $shippingSettings = getShippingSettings();

    if (!isset($shippingSettings[$productType])) {
        return 0; // No shipping fee defined for this product type
    }

    $setting = $shippingSettings[$productType];
    $baseShippingFee = $setting['fee'];

    // If no shipping fee, return 0
    if ($baseShippingFee <= 0) {
        return 0;
    }

    // For digital products (ebooks), shipping is usually 0
    if ($productType === 'ebook') {
        return 0;
    }

    // Calculate shipping fee
    // For books and paint, typically per-item or flat rate
    $shippingFee = $baseShippingFee;

    // Apply quantity if needed (you can customize this logic)
    if ($productType === 'book' && $quantity > 1) {
        // Additional shipping for multiple books (e.g., $2 per additional book)
        $additionalFee = ($quantity - 1) * ($baseShippingFee * 0.3); // 30% of base fee for additional items
        $shippingFee += $additionalFee;
    } elseif ($productType === 'paint' && $quantity > 1) {
        // For paint, might have different logic due to weight/volume
        $additionalFee = ($quantity - 1) * ($baseShippingFee * 0.5); // 50% of base fee for additional items
        $shippingFee += $additionalFee;
    }

    return round($shippingFee, 2);
}

/**
 * Calculate total shipping for all cart items
 */
function calculateTotalShipping($cartItems, $taxAmounts = [])
{
    $totalShipping = 0;
    $shippingBreakdown = [];

    foreach ($cartItems as $index => $item) {
        $productType = $item['type'] ?? 'book'; // Default to book if type not specified
        $quantity = intval($item['quantity']);
        $itemTotal = floatval($item['price']) * $quantity; // Calculate total from price * quantity
        $taxAmount = isset($taxAmounts[$index]) ? floatval($taxAmounts[$index]) : 0;

        $itemShipping = calculateItemShipping($productType, $quantity, $itemTotal, $taxAmount);
        $totalShipping += $itemShipping;

        if ($itemShipping > 0) {
            $shippingBreakdown[] = [
                'product_id' => $item['id'],
                'product_name' => $item['name'],
                'product_type' => $productType,
                'quantity' => $quantity,
                'shipping_fee' => $itemShipping
            ];
        }
    }

    return [
        'total_shipping' => round($totalShipping, 2),
        'breakdown' => $shippingBreakdown
    ];
}

/**
 * Calculate shipping for order based on validated cart items
 */
function calculateOrderShipping($validatedItems, $address = null)
{
    $shippingResult = calculateTotalShipping($validatedItems);

    // You can add address-based shipping logic here if needed
    // For example, different rates for different countries/states
    if ($address) {
        $shippingResult = applyLocationBasedShipping($shippingResult, $address);
    }

    return $shippingResult;
}

/**
 * Apply location-based shipping modifications (optional)
 */
function applyLocationBasedShipping($shippingResult, $address)
{
    // You can implement location-based shipping logic here
    // For example:
    // - Free shipping for local area
    // - Higher rates for international shipping
    // - Express shipping options

    $country = $address['country'] ?? 'US';
    $state = $address['state'] ?? '';

    // Example: Free shipping for local state (modify as needed)
    if ($country === 'US' && $state === 'NY') {
        // Apply local discount or free shipping threshold
        if ($shippingResult['total_shipping'] > 0 && $shippingResult['total_shipping'] < 10) {
            // Free shipping for orders under $10 shipping in NY
            $shippingResult['total_shipping'] = 0;
            $shippingResult['breakdown'] = [];
            $shippingResult['discount_applied'] = 'Local free shipping';
        }
    }

    return $shippingResult;
}

/**
 * Get shipping information for display
 */
function getShippingInfo($productTypes = [])
{
    $shippingSettings = getShippingSettings();
    $info = [];

    foreach ($productTypes as $type) {
        if (isset($shippingSettings[$type])) {
            $info[$type] = [
                'fee' => $shippingSettings[$type]['fee'],
                'description' => getShippingDescription($type, $shippingSettings[$type]['fee'])
            ];
        }
    }

    return $info;
}

/**
 * Get shipping description for product type
 */
function getShippingDescription($productType, $fee)
{
    if ($fee <= 0) {
        return 'Free shipping';
    }

    switch ($productType) {
        case 'ebook':
            return 'Digital download - no shipping';
        case 'book':
            return 'Standard shipping: $' . number_format($fee, 2) . ' (additional books may have reduced shipping)';
        case 'paint':
            return 'Shipping: $' . number_format($fee, 2) . ' (additional items may have reduced shipping)';
        default:
            return 'Shipping: $' . number_format($fee, 2);
    }
}

/**
 * Validate shipping calculation
 */
function validateShippingCalculation($expectedShipping, $calculatedShipping, $tolerance = 0.01)
{
    return abs($expectedShipping - $calculatedShipping) <= $tolerance;
}

/**
 * Log shipping calculation for debugging
 */
function logShippingCalculation($cartItems, $shippingResult, $context = [])
{
    $logData = [
        'timestamp' => date('Y-m-d H:i:s'),
        'cart_items' => $cartItems,
        'shipping_result' => $shippingResult,
        'context' => $context
    ];

    // You can implement logging to file or database here
    error_log('Shipping Calculation: ' . json_encode($logData));
}