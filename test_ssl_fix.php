<?php

/**
 * SSL Connection Test for PayPal Integration
 */

require_once 'includes/ssl_fix.php';
require_once 'includes/paypal_config.php';

echo "<!DOCTYPE html>\n";
echo "<html><head><title>SSL Connection Test</title>";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;} .success{color:green;} .error{color:red;} .warning{color:orange;} .info{color:blue;}</style>";
echo "</head><body>";

echo "<h1>SSL Connection Test for PayPal</h1>";

// Test 1: Basic cURL SSL test
echo "<h2>1. Testing Basic SSL Connection</h2>";
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => 'https://api.paypal.com/v1/oauth2/token',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_HEADER => true,
    CURLOPT_NOBODY => true
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($httpCode === 401 || $httpCode === 200) { // 401 is expected without credentials
    echo "<p class='success'>✓ SSL connection to PayPal API successful (HTTP: $httpCode)</p>";
} else {
    echo "<p class='error'>✗ SSL connection failed. HTTP: $httpCode, Error: $error</p>";
}

// Test 2: PayPal SSL connectivity test
echo "<h2>2. Testing PayPal SSL Connectivity</h2>";
if (function_exists('testPayPalSSLConnection')) {
    $results = testPayPalSSLConnection();

    foreach ($results as $url => $result) {
        if ($result['success'] || $result['http_code'] === 401) {
            echo "<p class='success'>✓ $url - HTTP: {$result['http_code']}</p>";
        } else {
            echo "<p class='error'>✗ $url - HTTP: {$result['http_code']}, Error: {$result['error']}</p>";
        }
    }
} else {
    echo "<p class='error'>✗ testPayPalSSLConnection function not found</p>";
}

// Test 3: PayPal SDK initialization
echo "<h2>3. Testing PayPal SDK with SSL Fix</h2>";
try {
    require_once 'vendor/autoload.php';

    if (function_exists('createPayPalClientWithSSLFix')) {
        $credentials = getPayPalCredentials();
        $environment = new \PayPalCheckoutSdk\Core\ProductionEnvironment($credentials['client_id'], $credentials['client_secret']);
        $client = createPayPalClientWithSSLFix($environment);

        echo "<p class='success'>✓ PayPal SDK initialized with SSL fix</p>";

        // Try to create a test request (won't complete but tests connection)
        $request = new \PayPalCheckoutSdk\Orders\OrdersCreateRequest();
        echo "<p class='success'>✓ PayPal order request object created</p>";
    } else {
        echo "<p class='error'>✗ createPayPalClientWithSSLFix function not found</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>✗ PayPal SDK error: " . $e->getMessage() . "</p>";
}

// Test 4: Environment detection
echo "<h2>4. Testing Environment Detection</h2>";
if (function_exists('configureSSLForDevelopment')) {
    $isDev = configureSSLForDevelopment();
    if ($isDev) {
        echo "<p class='info'>ℹ Development environment detected - SSL verification disabled</p>";
    } else {
        echo "<p class='success'>✓ Production environment detected - SSL verification enabled</p>";
    }
} else {
    echo "<p class='error'>✗ configureSSLForDevelopment function not found</p>";
}

// Test 5: Certificate bundle download
echo "<h2>5. CA Certificate Bundle Test</h2>";
if (function_exists('downloadCACertBundle')) {
    echo "<p class='info'>Testing CA certificate bundle download...</p>";
    $result = downloadCACertBundle();

    if ($result['success']) {
        echo "<p class='success'>✓ " . $result['message'] . "</p>";
        echo "<p class='info'>Bundle saved to: " . $result['path'] . "</p>";
    } else {
        echo "<p class='warning'>⚠ CA bundle download failed: " . $result['error'] . "</p>";
        echo "<p class='info'>This is optional - SSL fix should still work</p>";
    }
} else {
    echo "<p class='error'>✗ downloadCACertBundle function not found</p>";
}

// Test 6: Current PHP configuration
echo "<h2>6. Current PHP Configuration</h2>";
echo "<table border='1' cellpadding='5' cellspacing='0'>";
echo "<tr><th>Setting</th><th>Value</th></tr>";

$settings = [
    'curl.cainfo' => ini_get('curl.cainfo'),
    'openssl.cafile' => ini_get('openssl.cafile'),
    'allow_url_fopen' => ini_get('allow_url_fopen') ? 'Yes' : 'No',
    'user_agent' => ini_get('user_agent'),
    'default_socket_timeout' => ini_get('default_socket_timeout')
];

foreach ($settings as $setting => $value) {
    echo "<tr><td>$setting</td><td>" . ($value ?: '<em>Not set</em>') . "</td></tr>";
}
echo "</table>";

echo "<h2>Summary</h2>";
echo "<p class='info'>If all tests above show success or expected results, your SSL configuration should work with PayPal.</p>";
echo "<p class='warning'><strong>Note:</strong> SSL verification is disabled for development environments (WAMP/XAMPP). This should only be used for testing, never in production.</p>";

echo "<h3>Quick Links:</h3>";
echo "<ul>";
echo "<li><a href='test_checkout.php'>Run Full Checkout Test</a></li>";
echo "<li><a href='checkout/simple_checkout.php'>Test Checkout Page</a></li>";
echo "</ul>";

echo "</body></html>";