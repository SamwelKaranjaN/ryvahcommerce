<?php
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

    // Use flat database fee - no per-item or quantity calculations
    return round($baseShippingFee, 2);
}


function calculateTotalShipping($cartItems, $taxAmounts = [])
{
    $totalShipping = 0;
    $shippingBreakdown = [];
    $processedTypes = []; // Track which product types we've already processed

    foreach ($cartItems as $index => $item) {
        $productType = $item['type'] ?? 'book'; // Default to book if type not specified
        $quantity = intval($item['quantity']);

        // Only calculate shipping once per product type, not per item
        if (in_array($productType, $processedTypes)) {
            continue; // Skip if we've already processed this product type
        }

        $itemShipping = calculateItemShipping($productType, $quantity, 0, 0);

        if ($itemShipping > 0) {
            $totalShipping += $itemShipping;
            $processedTypes[] = $productType;

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

function calculateOrderShipping($validatedItems, $address = null)
{
    // Simply return the total shipping calculation without any location-based modifications
    // All calculations are flat rates from database - no discounts or special pricing
    return calculateTotalShipping($validatedItems);
}

/**
 * Apply location-based shipping modifications (DISABLED - No discounts offered)
 */
function applyLocationBasedShipping($shippingResult, $address)
{
    // No location-based discounts or modifications
    // All shipping calculations are flat rates from database
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
            return 'Standard shipping: $' . number_format($fee, 2) . ' per order';
        case 'paint':
            return 'Shipping: $' . number_format($fee, 2) . ' per order';
        default:
            return 'Shipping: $' . number_format($fee, 2) . ' per order';
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
