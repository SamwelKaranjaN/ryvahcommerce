<?php

/**
 * PayPal Configuration
 * Enhanced security and validation for PayPal Server SDK integration
 */

// Environment Configuration
define('PAYPAL_ENVIRONMENT', 'production'); // 'sandbox' or 'production'

// Production Credentials
define('PAYPAL_PRODUCTION_CLIENT_ID', 'ARbQtWP1vIsYqgrKcL0v2hhlJA6NujGi26UWQz9Z4lsmPosxbSDPfzLSkaHtS8JRSvdysC99W0qvLyCI');
define('PAYPAL_PRODUCTION_CLIENT_SECRET', 'EChoMRhi0vy7L_Defl5dqinOMbiWHxRmPG2e3ArjXXRQqHR1vwkg1IvHGTDxzwrOOuQR4n-z8ZteQiGc');

// Set current client ID constant for production
define('PAYPAL_CLIENT_ID', PAYPAL_PRODUCTION_CLIENT_ID);
define('PAYPAL_CLIENT_SECRET', PAYPAL_PRODUCTION_CLIENT_SECRET);

// Site Configuration
define('SITE_DOMAIN', 'https://ryvahcommerce.com');
define('SITE_NAME', 'Ryvah Commerce');

// PayPal URLs
define('PAYPAL_RETURN_URL', SITE_DOMAIN . '/checkout/simple_success');
define('PAYPAL_CANCEL_URL', SITE_DOMAIN . '/checkout/simple_checkout');

// Currency Settings
define('PAYPAL_DEFAULT_CURRENCY', 'USD');
define('PAYPAL_SUPPORTED_CURRENCIES', ['USD', 'EUR', 'GBP', 'CAD', 'AUD']);

// Payment Settings
define('PAYPAL_MAX_AMOUNT', 10000.00);
define('PAYPAL_MIN_AMOUNT', 0.01);

// Logging
define('PAYPAL_LOG_ENABLED', true);
define('PAYPAL_LOG_FILE', '../logs/paypal.log');

/**
 * Get PayPal credentials based on environment
 * 
 * @return array PayPal credentials
 * @throws Exception If credentials are invalid
 */
function getPayPalCredentials()
{
    $credentials = [
        'client_id' => PAYPAL_PRODUCTION_CLIENT_ID,
        'client_secret' => PAYPAL_PRODUCTION_CLIENT_SECRET,
        'base_url' => 'https://api.paypal.com'
    ];

    // Validate credentials
    if (empty($credentials['client_id']) || empty($credentials['client_secret'])) {
        throw new Exception('PayPal production credentials are not properly configured');
    }

    if (strpos($credentials['client_id'], 'YOUR_') === 0 || strpos($credentials['client_secret'], 'YOUR_') === 0) {
        throw new Exception('PayPal credentials contain placeholder values');
    }

    return $credentials;
}

/**
 * Create PayPal Server SDK Client
 * 
 * @return \PaypalServerSdkLib\PaypalServerSdkClient
 * @throws Exception If SDK is not available
 */
function createPayPalServerClient()
{
    // Check if the SDK is available
    if (!class_exists('PaypalServerSdkLib\PaypalServerSdkClientBuilder')) {
        throw new Exception('PayPal Server SDK is not available. Please install it with: composer require paypal/paypal-server-sdk');
    }

    $credentials = getPayPalCredentials();

    try {
        // Determine environment
        $environment = (PAYPAL_ENVIRONMENT === 'production')
            ? \PaypalServerSdkLib\Environment::PRODUCTION
            : \PaypalServerSdkLib\Environment::SANDBOX;

        // Create authentication credentials with proper OAuth configuration
        $authCredentials = \PaypalServerSdkLib\Authentication\ClientCredentialsAuthCredentialsBuilder::init(
            $credentials['client_id'],
            $credentials['client_secret']
        );

        // Create the client using the official SDK with proper configuration
        $client = \PaypalServerSdkLib\PaypalServerSdkClientBuilder::init()
            ->clientCredentialsAuthCredentials($authCredentials)
            ->environment($environment)
            ->timeout(30) // 30 second timeout for production
            ->numberOfRetries(2) // Retry failed requests twice
            ->build();

        logPayPalError('PayPal Server SDK client created successfully', [
            'environment' => PAYPAL_ENVIRONMENT,
            'client_id' => substr($credentials['client_id'], 0, 10) . '...'
        ]);

        return $client;
    } catch (Exception $e) {
        logPayPalError('PayPal Server SDK client creation failed', [
            'error' => $e->getMessage(),
            'environment' => PAYPAL_ENVIRONMENT,
            'credentials_configured' => !empty($credentials['client_id']) && !empty($credentials['client_secret'])
        ]);
        throw new Exception('Failed to create PayPal client: ' . $e->getMessage());
    }
}

/**
 * Validate PayPal configuration
 * 
 * @return bool True if configuration is valid
 */
function validatePayPalConfig()
{
    try {
        // Check credentials
        getPayPalCredentials();

        // Check required constants
        $requiredConstants = [
            'SITE_DOMAIN',
            'PAYPAL_RETURN_URL',
            'PAYPAL_CANCEL_URL',
            'PAYPAL_DEFAULT_CURRENCY'
        ];

        foreach ($requiredConstants as $constant) {
            if (!defined($constant) || empty(constant($constant))) {
                logPayPalError('Required constant not defined or empty: ' . $constant);
                return false;
            }
        }

        // Validate currency
        if (!in_array(PAYPAL_DEFAULT_CURRENCY, PAYPAL_SUPPORTED_CURRENCIES, true)) {
            logPayPalError('Invalid default currency: ' . PAYPAL_DEFAULT_CURRENCY);
            return false;
        }

        // Validate URLs
        if (!filter_var(PAYPAL_RETURN_URL, FILTER_VALIDATE_URL) || !filter_var(PAYPAL_CANCEL_URL, FILTER_VALIDATE_URL)) {
            logPayPalError('Invalid PayPal URLs configured');
            return false;
        }

        return true;
    } catch (Exception $e) {
        logPayPalError('PayPal configuration validation failed: ' . $e->getMessage());
        return false;
    }
}

/**
 * Get user currency preference
 * 
 * @param int $user_id User ID
 * @return string Currency code
 */
function getUserCurrency($user_id)
{
    global $conn;

    try {
        // Check if user has currency preference
        $stmt = $conn->prepare("SELECT currency FROM user_preferences WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $currency = $row['currency'];

            // Validate currency
            if (in_array($currency, PAYPAL_SUPPORTED_CURRENCIES, true)) {
                return $currency;
            }
        }

        return PAYPAL_DEFAULT_CURRENCY;
    } catch (Exception $e) {
        logPayPalError('Error getting user currency: ' . $e->getMessage());
        return PAYPAL_DEFAULT_CURRENCY;
    }
}

/**
 * Calculate tax rate based on location
 * 
 * @param string $state State/province
 * @param string $country Country code
 * @param string $product_type Product type
 * @return float Tax rate (decimal)
 */
function getTaxRate($state, $country, $product_type = null)
{
    global $conn;

    try {
        // eBooks are not taxed
        if ($product_type === 'ebook') {
            return 0.0;
        }

        // Get tax rate from database
        if ($product_type) {
            $stmt = $conn->prepare("SELECT tax_rate FROM tax_settings WHERE product_type = ? AND is_active = 1");
            $stmt->bind_param("s", $product_type);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                return floatval($row['tax_rate']) / 100;
            }
        }

        // Default tax logic based on location
        if ($country === 'US') {
            // US state tax rates (simplified)
            $stateTaxRates = [
                'CA' => 0.0875, // California
                'NY' => 0.08,   // New York
                'TX' => 0.0625, // Texas
                'FL' => 0.06,   // Florida
                // Add more states as needed
            ];

            return $stateTaxRates[$state] ?? 0.08; // Default 8% for US
        }

        // International tax rates (simplified)
        $countryTaxRates = [
            'CA' => 0.13,  // Canada (HST)
            'GB' => 0.20,  // UK (VAT)
            'AU' => 0.10,  // Australia (GST)
            'DE' => 0.19,  // Germany (VAT)
            // Add more countries as needed
        ];

        return $countryTaxRates[$country] ?? 0.10; // Default 10%

    } catch (Exception $e) {
        logPayPalError('Error calculating tax rate: ' . $e->getMessage());
        return 0.10; // Default 10% on error
    }
}

/**
 * Validate payment amount
 * 
 * @param float $amount Amount to validate
 * @return bool True if amount is valid
 */
function validatePaymentAmount($amount)
{
    $amount = floatval($amount);
    return $amount >= PAYPAL_MIN_AMOUNT && $amount <= PAYPAL_MAX_AMOUNT;
}

/**
 * Format currency amount
 * 
 * @param float $amount Amount to format
 * @param string $currency Currency code
 * @return string Formatted amount
 */
function formatCurrency($amount, $currency = null)
{
    $currency = $currency ?: PAYPAL_DEFAULT_CURRENCY;
    $amount = floatval($amount);

    switch ($currency) {
        case 'USD':
        case 'CAD':
        case 'AUD':
            return '$' . number_format($amount, 2);
        case 'EUR':
            return '€' . number_format($amount, 2);
        case 'GBP':
            return '£' . number_format($amount, 2);
        default:
            return $currency . ' ' . number_format($amount, 2);
    }
}

/**
 * Log PayPal errors
 * 
 * @param string $message Error message
 * @param array $context Additional context
 */
function logPayPalError($message, $context = [])
{
    if (!PAYPAL_LOG_ENABLED) {
        return;
    }

    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'environment' => PAYPAL_ENVIRONMENT,
        'message' => $message,
        'context' => $context,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
    ];

    $logLine = json_encode($logEntry) . "\n";

    // Ensure log directory exists
    $logDir = dirname(PAYPAL_LOG_FILE);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }

    error_log($logLine, 3, PAYPAL_LOG_FILE);
    error_log($message); // Also log to system error log
}

/**
 * Generate secure order reference
 * 
 * @param int $user_id User ID
 * @return string Order reference
 */
function generateOrderReference($user_id)
{
    return 'RYV-' . date('Ymd') . '-' . $user_id . '-' . strtoupper(bin2hex(random_bytes(4)));
}

/**
 * Sanitize PayPal webhook data
 * 
 * @param array $data Webhook data
 * @return array Sanitized data
 */
function sanitizeWebhookData($data)
{
    if (!is_array($data)) {
        return [];
    }

    $sanitized = [];
    foreach ($data as $key => $value) {
        if (is_string($value)) {
            $sanitized[$key] = trim(strip_tags($value));
        } elseif (is_numeric($value)) {
            $sanitized[$key] = $value;
        } elseif (is_array($value)) {
            $sanitized[$key] = sanitizeWebhookData($value);
        }
    }

    return $sanitized;
}

/**
 * Check if PayPal Server SDK is properly loaded
 * 
 * @return bool True if SDK is available
 */
function isPayPalSDKAvailable()
{
    // Only skip SDK check in true CLI mode (not web requests)
    if (php_sapi_name() === 'cli' && !isset($_SERVER['HTTP_HOST'])) {
        return false;
    }

    // Check if the official PayPal Server SDK is available
    if (!class_exists('PaypalServerSdkLib\PaypalServerSdkClientBuilder')) {
        logPayPalError('PayPal Server SDK is not available');
        return false;
    }

    // Check if credentials are configured
    try {
        getPayPalCredentials();
        return true;
    } catch (Exception $e) {
        logPayPalError('PayPal credentials check failed: ' . $e->getMessage());
        return false;
    }
}

/**
 * Production error handler for PayPal operations
 */
function handlePayPalProductionError($error, $context = [])
{
    // Log detailed error for debugging
    logPayPalError('PayPal Production Error: ' . $error, $context);

    // Return user-friendly error messages based on error type
    if (strpos($error, 'authentication') !== false || strpos($error, 'OAuth') !== false) {
        return 'Payment authentication failed. Please contact support if this persists.';
    }

    if (strpos($error, 'network') !== false || strpos($error, 'connection') !== false) {
        return 'Payment system temporarily unavailable. Please try again in a few moments.';
    }

    if (strpos($error, 'timeout') !== false) {
        return 'Payment request timed out. Please try again.';
    }

    // Generic production error
    return 'Payment processing temporarily unavailable. Please try again later.';
}

/**
 * Validate production environment for PayPal
 */
function validatePayPalProductionEnvironment()
{
    $errors = [];

    // Check critical PHP extensions
    if (!extension_loaded('curl')) {
        $errors[] = 'cURL extension is required for PayPal integration';
    }

    if (!extension_loaded('openssl')) {
        $errors[] = 'OpenSSL extension is required for secure PayPal connections';
    }

    if (!extension_loaded('json')) {
        $errors[] = 'JSON extension is required for PayPal data processing';
    }

    // Check directory permissions
    $logDir = dirname(__DIR__) . '/logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }

    if (!is_writable($logDir)) {
        $errors[] = 'Logs directory must be writable for error tracking';
    }

    if (!empty($errors)) {
        foreach ($errors as $error) {
            logPayPalError('Environment validation error: ' . $error);
        }
        return false;
    }

    return true;
}

// Set production-specific PHP settings
if (PAYPAL_ENVIRONMENT === 'production') {
    ini_set('memory_limit', '256M');
    ini_set('max_execution_time', '60');
    ini_set('log_errors', '1');
    ini_set('display_errors', '0');
}

// Validate environment first
if (!validatePayPalProductionEnvironment()) {
    logPayPalError('PayPal production environment validation failed');
}

// Initialize configuration validation
if (!validatePayPalConfig()) {
    // In production, halt execution if config is invalid
    if (PAYPAL_ENVIRONMENT === 'production') {
        logPayPalError('PayPal configuration is invalid in production environment');
        // Don't throw exception in production, log error instead
    } else {
        throw new Exception('PayPal configuration is invalid. Please check your settings.');
    }
}

/**
 * Test PayPal API connectivity
 * 
 * @return array Connection test results
 */
function testPayPalConnectivity()
{
    $results = [
        'can_resolve_dns' => false,
        'can_connect_ssl' => false,
        'api_reachable' => false,
        'errors' => []
    ];

    try {
        // Production API URL
        $apiHostname = 'api.paypal.com';
        $apiUrl = 'https://' . $apiHostname . '/v1/oauth2/token';

        // Test DNS resolution
        $ip = gethostbyname($apiHostname);
        if ($ip !== $apiHostname) {
            $results['can_resolve_dns'] = true;
        } else {
            $results['errors'][] = 'Cannot resolve PayPal API hostname';
        }

        // Test SSL connection
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
                'allow_self_signed' => false
            ]
        ]);

        $connection = @stream_socket_client(
            'ssl://' . $apiHostname . ':443',
            $errno,
            $errstr,
            30,
            STREAM_CLIENT_CONNECT,
            $context
        );

        if ($connection) {
            $results['can_connect_ssl'] = true;
            fclose($connection);
        } else {
            $results['errors'][] = "SSL connection failed: $errstr ($errno)";
        }

        // Test basic API endpoint with cURL
        $ch = curl_init();

        // Check if we're in a development environment
        $serverName = $_SERVER['SERVER_NAME'] ?? '';
        $httpHost = $_SERVER['HTTP_HOST'] ?? '';
        $isDevelopment = (
            $serverName === 'localhost' ||
            $serverName === '127.0.0.1' ||
            strpos($serverName, '.local') !== false ||
            strpos($httpHost, 'localhost') !== false ||
            php_sapi_name() === 'cli' // Also consider CLI as development
        );

        curl_setopt_array($ch, [
            CURLOPT_URL => $apiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => !$isDevelopment, // Disable SSL verification in development
            CURLOPT_SSL_VERIFYHOST => $isDevelopment ? 0 : 2, // Disable host verification in development
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => 'grant_type=client_credentials',
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Accept-Language: en_US',
            ],
            CURLOPT_DNS_CACHE_TIMEOUT => 120,
            CURLOPT_TCP_NODELAY => true,
            CURLOPT_TCP_KEEPALIVE => 1
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response !== false && ($httpCode === 200 || $httpCode === 401)) {
            $results['api_reachable'] = true;
        } else {
            $results['errors'][] = "API test failed: HTTP $httpCode, cURL error: $error";
        }
    } catch (Exception $e) {
        $results['errors'][] = 'Connectivity test exception: ' . $e->getMessage();
    }

    return $results;
}

/**
 * Validate network connectivity before PayPal operations
 * 
 * @return bool True if network is ready
 */
function validateNetworkConnectivity()
{
    $test = testPayPalConnectivity();

    if (!$test['can_resolve_dns']) {
        logPayPalError('Network diagnostic: DNS resolution failed', $test);
        return false;
    }

    if (!$test['can_connect_ssl']) {
        logPayPalError('Network diagnostic: SSL connection failed', $test);
        return false;
    }

    if (!$test['api_reachable']) {
        logPayPalError('Network diagnostic: PayPal API unreachable', $test);
        return false;
    }

    return true;
}

/**
 * Create PayPal order request with optimized structure
 * 
 * @param array $orderData Order data
 * @param array $address Shipping address
 * @param string $currency Currency code
 * @param string $orderReference Order reference
 * @return array PayPal order request body
 */
function createPayPalOrderRequest($orderData, $address, $currency, $orderReference)
{
    // Optimize item processing
    $paypalItems = [];
    foreach ($orderData['order_items'] as $item) {
        $paypalItems[] = [
            'name' => $item['name'],
            'description' => $item['description'] ?? 'Digital product from ' . SITE_NAME,
            'unit_amount' => $item['unit_amount'],
            'quantity' => $item['quantity'],
            'category' => $item['category'] ?? 'DIGITAL_GOODS'
        ];
    }

    // Build optimized request structure
    $requestBody = [
        'intent' => 'CAPTURE',
        'purchase_units' => [[
            'reference_id' => $orderReference,
            'description' => 'Order from ' . SITE_NAME . ' - ' . count($paypalItems) . ' item(s)',
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
            'items' => $paypalItems
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
    if ($orderData['tax_amount'] > 0) {
        $requestBody['purchase_units'][0]['amount']['breakdown']['tax_total'] = [
            'currency_code' => $currency,
            'value' => number_format($orderData['tax_amount'], 2, '.', '')
        ];
    }

    // Add shipping breakdown only if shipping amount > 0
    if ($orderData['shipping_amount'] > 0) {
        $requestBody['purchase_units'][0]['amount']['breakdown']['shipping'] = [
            'currency_code' => $currency,
            'value' => number_format($orderData['shipping_amount'], 2, '.', '')
        ];
    }

    return $requestBody;
}
