<?php
header('Content-Type: application/json');
require_once __DIR__ . '/includes/PayPalHelper.php';

// Enable error logging
error_log("Starting PayPal order creation process");

try {
    $raw_data = file_get_contents('php://input');
    error_log("Received raw data: " . $raw_data);

    $data = json_decode($raw_data, true);
    error_log("Decoded data: " . print_r($data, true));

    if (!$data || !isset($data['items'], $data['total'], $data['tax'])) {
        error_log("Invalid request data - missing required fields");
        throw new \Exception('Invalid request data - missing required fields');
    }

    error_log("Creating PayPal order with total: {$data['total']}, tax: {$data['tax']}");
    $paypal = new PayPalHelper();
    $response = $paypal->createOrder($data['items'], $data['total'], $data['tax']);

    error_log("PayPal response: " . print_r($response, true));
    echo json_encode($response);
} catch (\Exception $e) {
    error_log("PayPal order creation error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}