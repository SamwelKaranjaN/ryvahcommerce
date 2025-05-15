<?php
// PayPal configuration
define('PAYPAL_CLIENT_ID', 'AdGDLxpS2vOxrDQoMAtZ1lB4Ef2DfdQ7F_CP0o4LGFTTG6MgZgffNU_jErjwzGeRxV3KLSLlRcAEPMSW');
define('PAYPAL_CLIENT_SECRET', 'ENOT3vx3Jv3KXb-4SylYEPB_tfeUAA8HedDWT01CIS3TF_xvQ1O6W1is9pinoifJC0Y2DOP9opc1Ome2');
define('PAYPAL_MODE', 'live'); // Live mode for production
define('PAYPAL_CURRENCY', 'USD');

// PayPal API endpoints
define('PAYPAL_API_URL', PAYPAL_MODE === 'live' 
    ? 'https://api.paypal.com'
    : 'https://api.sandbox.paypal.com'
);

// PayPal API version
define('PAYPAL_API_VERSION', 'v2');

// PayPal API endpoints
define('PAYPAL_ORDERS_URL', PAYPAL_API_URL . '/' . PAYPAL_API_VERSION . '/checkout/orders');
define('PAYPAL_PAYMENTS_URL', PAYPAL_API_URL . '/' . PAYPAL_API_VERSION . '/payments');

// Enhanced error logging for live mode
function logPayPalError($message, $context = []) {
    $log_message = date('Y-m-d H:i:s') . " - PayPal Error: " . $message;
    if (!empty($context)) {
        $log_message .= " - Context: " . json_encode($context);
    }
    error_log($log_message, 3, __DIR__ . '/../logs/paypal.log');
}

// Get PayPal access token with enhanced error handling
function getPayPalAccessToken() {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, PAYPAL_API_URL . '/v1/oauth2/token');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
    curl_setopt($ch, CURLOPT_USERPWD, PAYPAL_CLIENT_ID . ":" . PAYPAL_CLIENT_SECRET);
    
    // Enhanced SSL verification for live mode
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($ch, CURLOPT_CAINFO, __DIR__ . '/cacert.pem');
    
    $headers = array();
    $headers[] = 'Accept: application/json';
    $headers[] = 'Accept-Language: en_US';
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        $error = curl_error($ch);
        curl_close($ch);
        logPayPalError("Failed to get access token: " . $error);
        throw new Exception("Failed to connect to PayPal. Please try again later.");
    }
    curl_close($ch);
    
    $json = json_decode($result);
    if (!$json || !isset($json->access_token)) {
        logPayPalError("Invalid access token response: " . $result);
        throw new Exception("Failed to authenticate with PayPal. Please try again later.");
    }
    return $json->access_token;
}

// Make PayPal API request with enhanced error handling
function makePayPalRequest($url, $method = 'GET', $data = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    
    // Enhanced SSL verification for live mode
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($ch, CURLOPT_CAINFO, __DIR__ . '/cacert.pem');
    
    $headers = array();
    $headers[] = 'Content-Type: application/json';
    $headers[] = 'Authorization: Bearer ' . getPayPalAccessToken();
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    if ($data) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        $error = curl_error($ch);
        curl_close($ch);
        logPayPalError("API request failed: " . $error, ['url' => $url, 'method' => $method]);
        throw new Exception("Failed to process payment. Please try again later.");
    }
    curl_close($ch);
    
    $response = json_decode($result);
    if (!$response) {
        logPayPalError("Invalid API response: " . $result, ['url' => $url, 'method' => $method]);
        throw new Exception("Invalid response from payment processor. Please try again later.");
    }
    return $response;
}

// Function to create PayPal order with enhanced validation
function createPayPalOrder($order_id, $amount, $description) {
    // Validate amount
    if (!is_numeric($amount) || $amount <= 0) {
        logPayPalError("Invalid amount for order creation", ['order_id' => $order_id, 'amount' => $amount]);
        throw new Exception("Invalid payment amount");
    }
    
    $data = array(
        'intent' => 'CAPTURE',
        'purchase_units' => array(
            array(
                'reference_id' => $order_id,
                'amount' => array(
                    'currency_code' => PAYPAL_CURRENCY,
                    'value' => number_format($amount, 2, '.', '')
                ),
                'description' => $description
            )
        )
    );
    
    try {
        $response = makePayPalRequest(PAYPAL_ORDERS_URL, 'POST', $data);
        if (!$response || !isset($response->id)) {
            throw new Exception("Failed to create payment order");
        }
        return $response;
    } catch (Exception $e) {
        logPayPalError("Order creation failed: " . $e->getMessage(), ['order_id' => $order_id]);
        throw $e;
    }
}

// Function to capture PayPal payment with enhanced validation
function capturePayPalPayment($order_id) {
    try {
        $response = makePayPalRequest(PAYPAL_ORDERS_URL . '/' . $order_id . '/capture', 'POST');
        if (!$response) {
            throw new Exception("Failed to capture payment");
        }
        
        // Log successful capture
        logPayPalError("Payment captured successfully", [
            'order_id' => $order_id,
            'status' => $response->status ?? 'unknown'
        ]);
        
        return $response;
    } catch (Exception $e) {
        logPayPalError("Payment capture failed: " . $e->getMessage(), ['order_id' => $order_id]);
        throw $e;
    }
} 