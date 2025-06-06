<?php

/**
 * PayPal Configuration
 * Enhanced security and validation for PayPal integration
 */

// Environment Configuration
define('PAYPAL_ENVIRONMENT', 'sandbox'); // 'sandbox' or 'production'

// Sandbox Credentials
define('PAYPAL_SANDBOX_CLIENT_ID', 'ARb4izn3jwTWc2j2x6UDmompOiO2Uq3HQKodHTR3Y6UKUN61daJD09G8JVrx6UWz11-CL2fcty8UJ2CJ');
define('PAYPAL_SANDBOX_CLIENT_SECRET', 'EDUXnHsBZ0L7gUXjdpI9l7oFnCTIftl0UORyDtsXIZqBb7reoiNhGlEI4U2Qv_lKsI_oaK1Z3eVhzOyW');

// Production Credentials (replace with actual values)
define('PAYPAL_PRODUCTION_CLIENT_ID', 'YOUR_PRODUCTION_CLIENT_ID');
define('PAYPAL_PRODUCTION_CLIENT_SECRET', 'YOUR_PRODUCTION_CLIENT_SECRET');

// Site Configuration
define('SITE_DOMAIN', 'http://localhost/ryvahcommerce');
define('SITE_NAME', 'Ryvah Commerce');

// PayPal URLs
define('PAYPAL_RETURN_URL', SITE_DOMAIN . '/checkout/simple_success.php');
define('PAYPAL_CANCEL_URL', SITE_DOMAIN . '/checkout/simple_checkout.php');

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
    $environment = PAYPAL_ENVIRONMENT;

    if ($environment === 'sandbox') {
        $credentials = [
            'client_id' => PAYPAL_SANDBOX_CLIENT_ID,
            'client_secret' => PAYPAL_SANDBOX_CLIENT_SECRET,
            'base_url' => 'https://api.sandbox.paypal.com'
        ];
    } elseif ($environment === 'production') {
        $credentials = [
            'client_id' => PAYPAL_PRODUCTION_CLIENT_ID,
            'client_secret' => PAYPAL_PRODUCTION_CLIENT_SECRET,
            'base_url' => 'https://api.paypal.com'
        ];
    } else {
        throw new Exception('Invalid PayPal environment: ' . $environment);
    }

    // Validate credentials
    if (empty($credentials['client_id']) || empty($credentials['client_secret'])) {
        throw new Exception('PayPal credentials are not properly configured for environment: ' . $environment);
    }

    if (strpos($credentials['client_id'], 'YOUR_') === 0 || strpos($credentials['client_secret'], 'YOUR_') === 0) {
        throw new Exception('PayPal credentials contain placeholder values for environment: ' . $environment);
    }

    return $credentials;
}

/**
 * Validate PayPal configuration
 * 
 * @return bool True if configuration is valid
 */
function validatePayPalConfig()
{
    try {
        // Check environment
        if (!in_array(PAYPAL_ENVIRONMENT, ['sandbox', 'production'], true)) {
            logPayPalError('Invalid PayPal environment: ' . PAYPAL_ENVIRONMENT);
            return false;
        }

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
        // Get tax rate from database
        if ($product_type) {
            $stmt = $conn->prepare("SELECT tax_rate FROM tax_settings WHERE product_type = ? AND is_active = 1");
            $stmt->bind_param("s", $product_type);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                return floatval($row['tax_rate']) / 100; // Convert percentage to decimal
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
            $sanitized[$key] = filter_var($value, FILTER_SANITIZE_STRING);
        } elseif (is_numeric($value)) {
            $sanitized[$key] = $value;
        } elseif (is_array($value)) {
            $sanitized[$key] = sanitizeWebhookData($value);
        }
    }

    return $sanitized;
}

/**
 * Check if PayPal SDK is properly loaded
 * 
 * @return bool True if SDK is available
 */
function isPayPalSDKAvailable()
{
    // Skip SDK check in CLI mode to avoid autoloader issues
    if (php_sapi_name() === 'cli') {
        return false;
    }

    // This would be called from JavaScript, but we can validate server-side requirements
    $requiredClasses = [
        'PayPalCheckoutSdk\Core\SandboxEnvironment',
        'PayPalCheckoutSdk\Core\ProductionEnvironment',
        'PayPalCheckoutSdk\Core\PayPalHttpClient',
        'PayPalCheckoutSdk\Orders\OrdersCreateRequest',
        'PayPalCheckoutSdk\Orders\OrdersCaptureRequest'
    ];

    foreach ($requiredClasses as $class) {
        if (!class_exists($class)) {
            logPayPalError('PayPal SDK class not found: ' . $class);
            return false;
        }
    }

    return true;
}

/**
 * Development SSL bypass helper function
 * FOR DEVELOPMENT ONLY - DO NOT USE IN PRODUCTION
 */
function applyDevelopmentSSLBypass($ch)
{
    $shouldBypass = (
        PAYPAL_ENVIRONMENT === 'sandbox' &&
        ((defined('DEVELOPMENT_MODE') && DEVELOPMENT_MODE) ||
            (defined('FORCE_DEVELOPMENT_MODE') && FORCE_DEVELOPMENT_MODE))
    );

    if ($shouldBypass) {
        curl_setopt_array($ch, [
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_CAINFO => '',
            CURLOPT_CAPATH => '',
            CURLOPT_TIMEOUT => 60,
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_DNS_CACHE_TIMEOUT => 120,
            CURLOPT_TCP_NODELAY => true,
            CURLOPT_TCP_KEEPALIVE => 1,
            CURLOPT_TCP_KEEPIDLE => 45,
            CURLOPT_TCP_KEEPINTVL => 10
        ]);
        logPayPalError('SSL verification bypassed for development environment');
        return true;
    }
    return false;
}

/**
 * Enhanced PayPal HTTP client with SSL bypass option
 * Only define if PayPal SDK is available
 */
if (class_exists('\PayPalCheckoutSdk\Core\PayPalHttpClient')) {
    class RyvahPayPalHttpClient extends \PayPalCheckoutSdk\Core\PayPalHttpClient
    {
        public function __construct($environment)
        {
            parent::__construct($environment);

            // Apply SSL bypass for development if needed
            $shouldBypass = (
                PAYPAL_ENVIRONMENT === 'sandbox' &&
                ((defined('DEVELOPMENT_MODE') && DEVELOPMENT_MODE) ||
                    (defined('FORCE_DEVELOPMENT_MODE') && FORCE_DEVELOPMENT_MODE))
            );

            if ($shouldBypass) {
                logPayPalError('PayPal client initialized with development SSL bypass enabled');
            }
        }
    }
}

// Development mode flag (set to true only for local development)
if (!defined('DEVELOPMENT_MODE')) {
    define('DEVELOPMENT_MODE', (
        PAYPAL_ENVIRONMENT === 'sandbox' &&
        in_array($_SERVER['SERVER_NAME'] ?? '', ['localhost', '127.0.0.1', 'local.dev', '::1'])
    ));
}

// Force development mode for SSL bypass (REMOVE IN PRODUCTION)
if (!defined('FORCE_DEVELOPMENT_MODE')) {
    define('FORCE_DEVELOPMENT_MODE', true); // Set to false in production
}

// Global SSL bypass for PayPal SDK (development only)
if ((defined('DEVELOPMENT_MODE') && DEVELOPMENT_MODE) || (defined('FORCE_DEVELOPMENT_MODE') && FORCE_DEVELOPMENT_MODE)) {
    // Set global curl options for PayPal SDK
    $curlDefaults = [
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_CONNECTTIMEOUT => 30,
        CURLOPT_DNS_CACHE_TIMEOUT => 120,
        CURLOPT_TCP_NODELAY => true,
        CURLOPT_TCP_KEEPALIVE => 1,
        CURLOPT_TCP_KEEPIDLE => 45,
        CURLOPT_TCP_KEEPINTVL => 10
    ];

    // Apply global defaults (this helps with PayPal SDK internal requests)
    foreach ($curlDefaults as $option => $value) {
        curl_setopt_array(curl_init(), [$option => $value]);
    }

    logPayPalError('Global SSL bypass enabled for development environment');
}

// Initialize configuration validation
if (!validatePayPalConfig()) {
    if (PAYPAL_ENVIRONMENT === 'production') {
        // In production, halt execution if config is invalid
        throw new Exception('PayPal configuration is invalid. Please check your settings.');
    } else {
        // In sandbox, just log the error
        logPayPalError('PayPal configuration validation failed in sandbox mode');
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
        // Test DNS resolution
        $ip = gethostbyname('api.sandbox.paypal.com');
        if ($ip !== 'api.sandbox.paypal.com') {
            $results['can_resolve_dns'] = true;
        } else {
            $results['errors'][] = 'Cannot resolve PayPal API hostname';
        }

        // Test SSL connection
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ]);

        $connection = @stream_socket_client(
            'ssl://api.sandbox.paypal.com:443',
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
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://api.sandbox.paypal.com/v1/oauth2/token',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
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