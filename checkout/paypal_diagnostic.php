<?php

/**
 * PayPal Diagnostic Script
 * Run this script to diagnose PayPal connection issues on hosted platform
 */

// Prevent direct access
if (!defined('DIAGNOSTIC_MODE')) {
    define('DIAGNOSTIC_MODE', true);
}

// Include required files
require_once '../includes/bootstrap.php';
require_once '../includes/paypal_config.php';

// Set headers for JSON response
header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-cache, must-revalidate, no-store');

/**
 * Run comprehensive PayPal diagnostics
 */
function runPayPalDiagnostics()
{
    $results = [
        'timestamp' => date('Y-m-d H:i:s'),
        'environment' => PAYPAL_ENVIRONMENT,
        'php_version' => PHP_VERSION,
        'tests' => []
    ];

    // Test 1: Check PHP extensions
    $results['tests']['php_extensions'] = [
        'curl' => extension_loaded('curl'),
        'openssl' => extension_loaded('openssl'),
        'json' => extension_loaded('json'),
        'mbstring' => extension_loaded('mbstring')
    ];

    // Test 2: Check PayPal configuration
    try {
        $credentials = getPayPalCredentials();
        $results['tests']['paypal_config'] = [
            'has_client_id' => !empty($credentials['client_id']),
            'client_id_length' => strlen($credentials['client_id'] ?? ''),
            'has_client_secret' => !empty($credentials['client_secret']),
            'client_secret_length' => strlen($credentials['client_secret'] ?? ''),
            'environment' => PAYPAL_ENVIRONMENT
        ];
    } catch (Exception $e) {
        $results['tests']['paypal_config'] = [
            'error' => $e->getMessage()
        ];
    }

    // Test 3: Test DNS resolution
    $paypalHost = (PAYPAL_ENVIRONMENT === 'production') ? 'api.paypal.com' : 'api.sandbox.paypal.com';
    $results['tests']['dns_resolution'] = [
        'host' => $paypalHost,
        'can_resolve' => gethostbyname($paypalHost) !== $paypalHost
    ];

    // Test 4: Test basic connectivity
    $baseUrl = (PAYPAL_ENVIRONMENT === 'production')
        ? 'https://api.paypal.com'
        : 'https://api.sandbox.paypal.com';

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $baseUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_SSL_VERIFYPEER => PAYPAL_ENVIRONMENT === 'production',
        CURLOPT_NOBODY => true,
        CURLOPT_HEADER => true
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    $curlInfo = curl_getinfo($ch);
    curl_close($ch);

    $results['tests']['basic_connectivity'] = [
        'base_url' => $baseUrl,
        'http_code' => $httpCode,
        'curl_error' => $curlError,
        'connection_successful' => $response !== false && empty($curlError),
        'curl_info' => $curlInfo
    ];

    // Test 5: Test OAuth token generation
    try {
        $credentials = getPayPalCredentials();

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $baseUrl . '/v1/oauth2/token',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => 'grant_type=client_credentials',
            CURLOPT_HTTPHEADER => ['Accept: application/json', 'Accept-Language: en_US'],
            CURLOPT_USERPWD => $credentials['client_id'] . ':' . $credentials['client_secret'],
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => PAYPAL_ENVIRONMENT === 'production',
        ]);

        $tokenResponse = curl_exec($ch);
        $tokenHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $tokenCurlError = curl_error($ch);
        $tokenCurlInfo = curl_getinfo($ch);
        curl_close($ch);

        $tokenData = json_decode($tokenResponse, true);

        $results['tests']['oauth_token'] = [
            'http_code' => $tokenHttpCode,
            'curl_error' => $tokenCurlError,
            'response_length' => strlen($tokenResponse ?? ''),
            'has_access_token' => isset($tokenData['access_token']),
            'token_type' => $tokenData['token_type'] ?? null,
            'expires_in' => $tokenData['expires_in'] ?? null,
            'curl_info' => $tokenCurlInfo,
            'success' => $tokenHttpCode === 200 && isset($tokenData['access_token'])
        ];

        // Don't include the actual token in the response for security
        if (isset($tokenData['access_token'])) {
            $results['tests']['oauth_token']['token_length'] = strlen($tokenData['access_token']);
        }

        // If OAuth failed, include the error response
        if ($tokenHttpCode !== 200) {
            $results['tests']['oauth_token']['error_response'] = $tokenResponse;
            $results['tests']['oauth_token']['parsed_error'] = $tokenData;
        }
    } catch (Exception $e) {
        $results['tests']['oauth_token'] = [
            'error' => $e->getMessage(),
            'success' => false
        ];
    }

    // Test 6: Test minimal order creation (if OAuth succeeded)
    if ($results['tests']['oauth_token']['success'] ?? false) {
        try {
            $testOrderData = [
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    'reference_id' => 'TEST-' . time(),
                    'description' => 'Diagnostic test order',
                    'amount' => [
                        'currency_code' => 'USD',
                        'value' => '1.00'
                    ]
                ]],
                'application_context' => [
                    'return_url' => 'https://example.com/return',
                    'cancel_url' => 'https://example.com/cancel',
                    'brand_name' => 'Test Store',
                    'user_action' => 'PAY_NOW',
                    'shipping_preference' => 'NO_SHIPPING'
                ]
            ];

            // Get fresh token for order creation
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $baseUrl . '/v1/oauth2/token',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => 'grant_type=client_credentials',
                CURLOPT_HTTPHEADER => ['Accept: application/json', 'Accept-Language: en_US'],
                CURLOPT_USERPWD => $credentials['client_id'] . ':' . $credentials['client_secret'],
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYPEER => PAYPAL_ENVIRONMENT === 'production',
            ]);

            $tokenResponse = curl_exec($ch);
            $tokenData = json_decode($tokenResponse, true);
            curl_close($ch);

            if (isset($tokenData['access_token'])) {
                $ch = curl_init();
                curl_setopt_array($ch, [
                    CURLOPT_URL => $baseUrl . '/v2/checkout/orders',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => json_encode($testOrderData),
                    CURLOPT_HTTPHEADER => [
                        'Content-Type: application/json',
                        'Authorization: Bearer ' . $tokenData['access_token'],
                        'PayPal-Request-Id: ' . uniqid(),
                        'Prefer: return=representation'
                    ],
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_SSL_VERIFYPEER => PAYPAL_ENVIRONMENT === 'production',
                ]);

                $orderResponse = curl_exec($ch);
                $orderHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $orderCurlError = curl_error($ch);
                $orderCurlInfo = curl_getinfo($ch);
                curl_close($ch);

                $orderData = json_decode($orderResponse, true);

                $results['tests']['test_order_creation'] = [
                    'http_code' => $orderHttpCode,
                    'curl_error' => $orderCurlError,
                    'response_length' => strlen($orderResponse ?? ''),
                    'has_order_id' => isset($orderData['id']),
                    'order_status' => $orderData['status'] ?? null,
                    'curl_info' => $orderCurlInfo,
                    'success' => $orderHttpCode === 201 && isset($orderData['id'])
                ];

                // If order creation failed, include error details
                if ($orderHttpCode !== 201) {
                    $results['tests']['test_order_creation']['error_response'] = $orderResponse;
                    $results['tests']['test_order_creation']['parsed_error'] = $orderData;
                }
            }
        } catch (Exception $e) {
            $results['tests']['test_order_creation'] = [
                'error' => $e->getMessage(),
                'success' => false
            ];
        }
    }

    // Test 7: Check server environment
    $results['tests']['server_environment'] = [
        'php_sapi' => php_sapi_name(),
        'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown',
        'https' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'remote_addr' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'server_name' => $_SERVER['SERVER_NAME'] ?? 'unknown'
    ];

    return $results;
}

// Run diagnostics and output results
try {
    $diagnostics = runPayPalDiagnostics();
    echo json_encode($diagnostics, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
} catch (Exception $e) {
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT);
}
