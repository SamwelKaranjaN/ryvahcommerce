
<?php

/**
 * Simple PayPal Connection Test
 * Run this in your browser to diagnose PayPal issues
 */

// Include required files
require_once '../includes/bootstrap.php';
require_once '../includes/paypal_config.php';

// Set headers
header('Content-Type: text/html; charset=UTF-8');

?>
<!DOCTYPE html>
<html>

<head>
    <title>PayPal Connection Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        .test {
            margin: 10px 0;
            padding: 10px;
            border: 1px solid #ddd;
        }

        .success {
            background-color: #d4edda;
            border-color: #c3e6cb;
        }

        .error {
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }

        .info {
            background-color: #d1ecf1;
            border-color: #bee5eb;
        }

        pre {
            background: #f8f9fa;
            padding: 10px;
            overflow-x: auto;
        }
    </style>
</head>

<body>
    <h1>PayPal Connection Test</h1>
    <p>Testing PayPal connectivity on hosted platform...</p>

    <?php

    // Test 1: Basic Configuration
    echo '<div class="test">';
    echo '<h3>Test 1: Configuration Check</h3>';
    try {
        $credentials = getPayPalCredentials();
        echo '<div class="success">✓ PayPal credentials loaded successfully</div>';
        echo '<div class="info">Environment: ' . PAYPAL_ENVIRONMENT . '</div>';
        echo '<div class="info">Client ID Length: ' . strlen($credentials['client_id']) . '</div>';
        echo '<div class="info">Base URL: ' . $credentials['base_url'] . '</div>';
    } catch (Exception $e) {
        echo '<div class="error">✗ Configuration Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
    echo '</div>';

    // Test 2: DNS Resolution
    echo '<div class="test">';
    echo '<h3>Test 2: DNS Resolution</h3>';
    $paypalHost = 'api.paypal.com';
    $ip = gethostbyname($paypalHost);
    if ($ip !== $paypalHost) {
        echo '<div class="success">✓ DNS resolution successful</div>';
        echo '<div class="info">PayPal API IP: ' . $ip . '</div>';
    } else {
        echo '<div class="error">✗ DNS resolution failed for ' . $paypalHost . '</div>';
    }
    echo '</div>';

    // Test 3: Basic Connectivity
    echo '<div class="test">';
    echo '<h3>Test 3: Basic Connectivity</h3>';
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => 'https://api.paypal.com',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_NOBODY => true,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($response !== false && empty($curlError)) {
        echo '<div class="success">✓ Basic connectivity successful</div>';
        echo '<div class="info">HTTP Code: ' . $httpCode . '</div>';
    } else {
        echo '<div class="error">✗ Connectivity failed</div>';
        echo '<div class="error">cURL Error: ' . htmlspecialchars($curlError) . '</div>';
    }
    echo '</div>';

    // Test 4: OAuth Token Test
    echo '<div class="test">';
    echo '<h3>Test 4: OAuth Token Generation</h3>';
    try {
        $credentials = getPayPalCredentials();

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://api.paypal.com/v1/oauth2/token',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => 'grant_type=client_credentials',
            CURLOPT_HTTPHEADER => ['Accept: application/json', 'Accept-Language: en_US'],
            CURLOPT_USERPWD => $credentials['client_id'] . ':' . $credentials['client_secret'],
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $tokenResponse = curl_exec($ch);
        $tokenHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $tokenCurlError = curl_error($ch);
        curl_close($ch);

        if ($tokenResponse !== false && empty($tokenCurlError)) {
            if ($tokenHttpCode === 200) {
                $tokenData = json_decode($tokenResponse, true);
                if (isset($tokenData['access_token'])) {
                    echo '<div class="success">✓ OAuth token generated successfully</div>';
                    echo '<div class="info">Token Type: ' . ($tokenData['token_type'] ?? 'unknown') . '</div>';
                    echo '<div class="info">Expires In: ' . ($tokenData['expires_in'] ?? 'unknown') . ' seconds</div>';
                } else {
                    echo '<div class="error">✗ Invalid OAuth response</div>';
                    echo '<pre>' . htmlspecialchars($tokenResponse) . '</pre>';
                }
            } else {
                echo '<div class="error">✗ OAuth failed with HTTP ' . $tokenHttpCode . '</div>';
                echo '<pre>' . htmlspecialchars($tokenResponse) . '</pre>';
            }
        } else {
            echo '<div class="error">✗ OAuth connection failed</div>';
            echo '<div class="error">cURL Error: ' . htmlspecialchars($tokenCurlError) . '</div>';
        }
    } catch (Exception $e) {
        echo '<div class="error">✗ OAuth test error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
    echo '</div>';

    // Test 5: Test Order Creation
    echo '<div class="test">';
    echo '<h3>Test 5: Test Order Creation</h3>';
    try {
        $credentials = getPayPalCredentials();

        // First get OAuth token
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://api.paypal.com/v1/oauth2/token',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => 'grant_type=client_credentials',
            CURLOPT_HTTPHEADER => ['Accept: application/json', 'Accept-Language: en_US'],
            CURLOPT_USERPWD => $credentials['client_id'] . ':' . $credentials['client_secret'],
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $tokenResponse = curl_exec($ch);
        $tokenHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($tokenHttpCode === 200) {
            $tokenData = json_decode($tokenResponse, true);
            if (isset($tokenData['access_token'])) {

                // Create test order
                $testOrderData = [
                    'intent' => 'CAPTURE',
                    'purchase_units' => [[
                        'reference_id' => 'TEST-' . time(),
                        'description' => 'Test order from diagnostic script',
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

                $ch = curl_init();
                curl_setopt_array($ch, [
                    CURLOPT_URL => 'https://api.paypal.com/v2/checkout/orders',
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
                    CURLOPT_SSL_VERIFYPEER => true,
                ]);

                $orderResponse = curl_exec($ch);
                $orderHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $orderCurlError = curl_error($ch);
                curl_close($ch);

                if ($orderResponse !== false && empty($orderCurlError)) {
                    if ($orderHttpCode === 201) {
                        $orderData = json_decode($orderResponse, true);
                        if (isset($orderData['id'])) {
                            echo '<div class="success">✓ Test order created successfully</div>';
                            echo '<div class="info">Order ID: ' . $orderData['id'] . '</div>';
                            echo '<div class="info">Status: ' . ($orderData['status'] ?? 'unknown') . '</div>';
                        } else {
                            echo '<div class="error">✗ Invalid order response</div>';
                            echo '<pre>' . htmlspecialchars($orderResponse) . '</pre>';
                        }
                    } else {
                        echo '<div class="error">✗ Order creation failed with HTTP ' . $orderHttpCode . '</div>';
                        echo '<pre>' . htmlspecialchars($orderResponse) . '</pre>';
                    }
                } else {
                    echo '<div class="error">✗ Order creation connection failed</div>';
                    echo '<div class="error">cURL Error: ' . htmlspecialchars($orderCurlError) . '</div>';
                }
            } else {
                echo '<div class="error">✗ No access token available for order test</div>';
            }
        } else {
            echo '<div class="error">✗ Cannot get OAuth token for order test</div>';
        }
    } catch (Exception $e) {
        echo '<div class="error">✗ Order creation test error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
    echo '</div>';

    // Test 6: Server Environment
    echo '<div class="test">';
    echo '<h3>Test 6: Server Environment</h3>';
    echo '<div class="info">PHP Version: ' . PHP_VERSION . '</div>';
    echo '<div class="info">cURL Version: ' . (function_exists('curl_version') ? curl_version()['version'] : 'Not available') . '</div>';
    echo '<div class="info">OpenSSL Version: ' . (defined('OPENSSL_VERSION_TEXT') ? OPENSSL_VERSION_TEXT : 'Not available') . '</div>';
    echo '<div class="info">Server Software: ' . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . '</div>';
    echo '<div class="info">HTTPS: ' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'Yes' : 'No') . '</div>';
    echo '</div>';

    ?>

    <div class="test">
        <h3>Summary</h3>
        <p>If all tests pass, your PayPal integration should work correctly.</p>
        <p>If any tests fail, the error messages above will help identify the specific issue.</p>
        <p><strong>Note:</strong> Test orders created by this script are for diagnostic purposes only.</p>
    </div>

</body>

</html>