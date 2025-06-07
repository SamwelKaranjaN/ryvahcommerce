<?php
/**
 * API endpoint to get shipping rates for frontend calculations
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Include database connection
require_once '../admin/php/db_connect.php';

try {
    $conn = getDBConnection();
    
    // Get active shipping rates
    $stmt = $conn->prepare("SELECT product_type, shipping_fee, applies_after_tax, description FROM shipping_fees WHERE is_active = 1");
    $stmt->execute();
    $result = $stmt->get_result();
    
    $shippingRates = [];
    while ($row = $result->fetch_assoc()) {
        $shippingRates[$row['product_type']] = [
            'fee' => floatval($row['shipping_fee']),
            'applies_after_tax' => intval($row['applies_after_tax']) === 1,
            'description' => $row['description']
        ];
    }
    $stmt->close();
    
    // If no rates found, provide defaults
    if (empty($shippingRates)) {
        $shippingRates = [
            'book' => [
                'fee' => 7.00,
                'applies_after_tax' => true,
                'description' => 'Standard shipping for physical books'
            ],
            'ebook' => [
                'fee' => 0.00,
                'applies_after_tax' => true,
                'description' => 'No shipping for digital ebooks'
            ],
            'paint' => [
                'fee' => 5.50,
                'applies_after_tax' => true,
                'description' => 'Standard shipping for paint products'
            ]
        ];
    }
    
    echo json_encode([
        'success' => true,
        'shipping_rates' => $shippingRates,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to load shipping rates',
        'message' => $e->getMessage()
    ]);
} 