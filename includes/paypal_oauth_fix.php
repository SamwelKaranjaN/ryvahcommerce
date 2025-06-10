<?php

/**
 * PayPal OAuth Authentication Fix
 * Handles OAuth token generation for PayPal Server SDK
 */

/**
 * Generate PayPal OAuth token manually
 */
function generatePayPalOAuthToken()
{
    $credentials = getPayPalCredentials();

    $baseUrl = (PAYPAL_ENVIRONMENT === 'production')
        ? 'https://api.paypal.com'
        : 'https://api.sandbox.paypal.com';

    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL => $baseUrl . '/v1/oauth2/token',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => 'grant_type=client_credentials',
        CURLOPT_HTTPHEADER => [
            'Accept: application/json',
            'Accept-Language: en_US',
        ],
        CURLOPT_USERPWD => $credentials['client_id'] . ':' . $credentials['client_secret'],
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => PAYPAL_ENVIRONMENT === 'production',
        CURLOPT_SSL_VERIFYHOST => PAYPAL_ENVIRONMENT === 'production' ? 2 : 0,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($response === false || !empty($error)) {
        throw new Exception('Failed to get PayPal access token: ' . $error);
    }

    if ($httpCode !== 200) {
        throw new Exception('PayPal OAuth failed with HTTP ' . $httpCode . ': ' . $response);
    }

    $tokenData = json_decode($response, true);
    if (!$tokenData || !isset($tokenData['access_token'])) {
        throw new Exception('Invalid PayPal OAuth response');
    }

    return $tokenData;
}

/**
 * Create PayPal order with manual OAuth
 */
function createPayPalOrderWithOAuth($orderData, $address, $currency, $orderReference)
{
    try {
        // Get OAuth token
        $tokenData = generatePayPalOAuthToken();
        $accessToken = $tokenData['access_token'];

        // Build order request
        $requestBody = [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'reference_id' => $orderReference,
                'description' => 'Order from ' . SITE_NAME . ' - ' . count($orderData['order_items']) . ' item(s)',
                'amount' => [
                    'currency_code' => $currency,
                    'value' => number_format($orderData['total'], 2, '.', ''),
                    'breakdown' => [
                        'item_total' => [
                            'currency_code' => $currency,
                            'value' => number_format($orderData['subtotal'], 2, '.', '')
                        ]
                    ]
                ],
                'items' => $orderData['order_items']
            ]],
            'application_context' => [
                'return_url' => PAYPAL_RETURN_URL,
                'cancel_url' => PAYPAL_CANCEL_URL,
                'brand_name' => SITE_NAME,
                'user_action' => 'PAY_NOW',
                'shipping_preference' => 'NO_SHIPPING',
                'landing_page' => 'BILLING'
            ]
        ];

        // Add tax breakdown only if tax amount > 0
        if (!empty($orderData['tax_amount']) && $orderData['tax_amount'] > 0) {
            $requestBody['purchase_units'][0]['amount']['breakdown']['tax_total'] = [
                'currency_code' => $currency,
                'value' => number_format($orderData['tax_amount'], 2, '.', '')
            ];
        }

        // Add shipping breakdown only if shipping amount > 0
        if (!empty($orderData['shipping_amount']) && $orderData['shipping_amount'] > 0) {
            $requestBody['purchase_units'][0]['amount']['breakdown']['shipping'] = [
                'currency_code' => $currency,
                'value' => number_format($orderData['shipping_amount'], 2, '.', '')
            ];
        }

        $baseUrl = (PAYPAL_ENVIRONMENT === 'production')
            ? 'https://api.paypal.com'
            : 'https://api.sandbox.paypal.com';

        // Create order via direct API call
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $baseUrl . '/v2/checkout/orders',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($requestBody),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $accessToken,
                'PayPal-Request-Id: ' . uniqid(),
                'Prefer: return=representation'
            ],
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => PAYPAL_ENVIRONMENT === 'production',
            CURLOPT_SSL_VERIFYHOST => PAYPAL_ENVIRONMENT === 'production' ? 2 : 0,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false || !empty($error)) {
            throw new Exception('Failed to create PayPal order: ' . $error);
        }

        if ($httpCode !== 201) {
            throw new Exception('PayPal order creation failed with HTTP ' . $httpCode . ': ' . $response);
        }

        $orderResponse = json_decode($response, true);
        if (!$orderResponse || !isset($orderResponse['id'])) {
            throw new Exception('Invalid PayPal order response');
        }

        logPayPalError('PayPal order created successfully via OAuth', [
            'paypal_order_id' => $orderResponse['id'],
            'environment' => PAYPAL_ENVIRONMENT,
            'order_total' => $requestBody['purchase_units'][0]['amount']['value']
        ]);

        return (object) $orderResponse;
    } catch (Exception $e) {
        logPayPalError('PayPal OAuth order creation failed', [
            'error' => $e->getMessage(),
            'environment' => PAYPAL_ENVIRONMENT
        ]);
        throw $e;
    }
}