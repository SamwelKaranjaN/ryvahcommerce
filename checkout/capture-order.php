<?php
header('Content-Type: application/json');
require_once __DIR__ . '/PayPalHelper.php';

try {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data || !isset($data['order_id'])) {
        throw new \Exception('Invalid request data');
    }

    $paypal = new PayPalHelper();
    $response = $paypal->captureOrder($data['order_id']);
    echo json_encode($response);
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>