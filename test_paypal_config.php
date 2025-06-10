<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/bootstrap.php';
require_once 'includes/paypal_config.php';
require_once 'vendor/autoload.php';

echo "<h1>PayPal Configuration Test</h1>\n";

// Test 1: Check PHP Extensions
echo "<h2>1. PHP Extensions Test</h2>\n";
$extensions = ['curl', 'json', 'openssl'];
foreach ($extensions as $ext) {
    $status = extension_loaded($ext) ? '✓ Loaded' : '✗ Missing';
    echo "- {$ext}: {$status}<br>\n";
}

// Test 2: Check PayPal Constants
echo "<h2>2. PayPal Constants Test</h2>\n";
echo "- Environment: " . PAYPAL_ENVIRONMENT . "<br>\n";
echo "- Client ID: " . substr(PAYPAL_CLIENT_ID, 0, 10) . "...<br>\n";
echo "- Client Secret: " . (strlen(PAYPAL_CLIENT_SECRET) > 0 ? 'Set' : 'Not Set') . "<br>\n";

// Test 3: Check PayPal SDK Classes
echo "<h2>3. PayPal SDK Classes Test</h2>\n";
$sdkClasses = [
    'PaypalServerSdkLib\PaypalServerSdkClientBuilder',
    'PaypalServerSdkLib\Authentication\ClientCredentialsAuthCredentialsBuilder',
    'PaypalServerSdkLib\Environment'
];
foreach ($sdkClasses as $class) {
    $status = class_exists($class) ? '✓ Available' : '✗ Missing';
    echo "- {$class}: {$status}<br>\n";
}

// Test 4: Test PayPal Configuration Function
echo "<h2>4. PayPal Configuration Validation</h2>\n";
try {
    $isValid = validatePayPalConfig();
    echo "- Configuration Valid: " . ($isValid ? '✓ Yes' : '✗ No') . "<br>\n";
} catch (Exception $e) {
    echo "- Configuration Error: " . $e->getMessage() . "<br>\n";
}

// Test 5: Test PayPal Client Creation
echo "<h2>5. PayPal Client Creation Test</h2>\n";
try {
    $client = createPayPalServerClient();
    echo "- Client Created: ✓ Success<br>\n";
    echo "- Client Type: " . get_class($client) . "<br>\n";
    
    // Test 6: Test Orders Controller
    echo "<h2>6. Orders Controller Test</h2>\n";
    $ordersController = $client->getOrdersController();
    echo "- Orders Controller: ✓ Available<br>\n";
    echo "- Controller Type: " . get_class($ordersController) . "<br>\n";
    
} catch (Exception $e) {
    echo "- Client Creation Error: ✗ " . $e->getMessage() . "<br>\n";
    echo "- Error Type: " . get_class($e) . "<br>\n";
    echo "- Error File: " . $e->getFile() . ":" . $e->getLine() . "<br>\n";
}

// Test 7: Network Connectivity
echo "<h2>7. Network Connectivity Test</h2>\n";
try {
    $connectivity = testPayPalConnectivity();
    echo "- DNS Resolution: " . ($connectivity['can_resolve_dns'] ? '✓ Success' : '✗ Failed') . "<br>\n";
    echo "- SSL Connection: " . ($connectivity['can_connect_ssl'] ? '✓ Success' : '✗ Failed') . "<br>\n";
    echo "- API Reachable: " . ($connectivity['api_reachable'] ? '✓ Success' : '✗ Failed') . "<br>\n";
    
    if (!empty($connectivity['errors'])) {
        echo "- Errors:<br>\n";
        foreach ($connectivity['errors'] as $error) {
            echo "  • " . $error . "<br>\n";
        }
    }
} catch (Exception $e) {
    echo "- Network Test Error: " . $e->getMessage() . "<br>\n";
}

// Test 8: Environment Variables
echo "<h2>8. Environment Variables</h2>\n";
echo "- PHP Version: " . PHP_VERSION . "<br>\n";
echo "- Memory Limit: " . ini_get('memory_limit') . "<br>\n";
echo "- Max Execution Time: " . ini_get('max_execution_time') . "<br>\n";
echo "- cURL Version: " . (function_exists('curl_version') ? curl_version()['version'] : 'Unknown') . "<br>\n";

echo "<h2>Test Complete</h2>\n";
echo "If all tests pass ✓, PayPal integration should work correctly.<br>\n";
echo "If any tests fail ✗, those issues need to be resolved first.<br>\n"; 