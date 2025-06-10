<?php

/**
 * Performance Configuration for PayPal Integration
 * Ryvah Commerce - Production Optimizations
 */

// Performance settings
ini_set('memory_limit', '256M');
ini_set('max_execution_time', '60');
ini_set('default_socket_timeout', '30');

// Error handling for production
if (PAYPAL_ENVIRONMENT === 'production') {
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', '../logs/php_errors.log');
}

// PayPal SDK optimizations
define('PAYPAL_REQUEST_TIMEOUT', 30);
define('PAYPAL_MAX_RETRIES', 2);
define('PAYPAL_CONNECTION_TIMEOUT', 15);

/**
 * Optimized PayPal HTTP client configuration
 */
function getOptimizedHttpClientConfig()
{
    return [
        'timeout' => PAYPAL_REQUEST_TIMEOUT,
        'connect_timeout' => PAYPAL_CONNECTION_TIMEOUT,
        'max_retries' => PAYPAL_MAX_RETRIES,
        'verify' => PAYPAL_ENVIRONMENT === 'production', // SSL verification
        'headers' => [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Accept-Language' => 'en_US',
            'User-Agent' => SITE_NAME . ' PayPal Integration v1.0'
        ]
    ];
}

/**
 * Enhanced logging for production debugging
 */
function logPayPalDebug($message, $context = [])
{
    if (PAYPAL_ENVIRONMENT === 'production') {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'environment' => PAYPAL_ENVIRONMENT,
            'message' => $message,
            'context' => $context,
            'memory_usage' => memory_get_usage(true),
            'memory_peak' => memory_get_peak_usage(true),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ];

        $logLine = json_encode($logEntry) . "\n";
        error_log($logLine, 3, '../logs/paypal_debug.log');
    }
}

/**
 * Cache PayPal access tokens to reduce API calls
 */
class PayPalTokenCache
{
    private static $cacheFile = '../cache/paypal_tokens.json';
    private static $cacheTimeout = 3300; // 55 minutes (tokens expire in 1 hour)

    public static function getToken($clientId)
    {
        if (!file_exists(self::$cacheFile)) {
            return null;
        }

        $cache = json_decode(file_get_contents(self::$cacheFile), true);
        if (!$cache || !isset($cache[$clientId])) {
            return null;
        }

        $tokenData = $cache[$clientId];
        if (time() > $tokenData['expires_at']) {
            self::clearToken($clientId);
            return null;
        }

        return $tokenData['access_token'];
    }

    public static function setToken($clientId, $accessToken, $expiresIn = 3600)
    {
        $cache = [];
        if (file_exists(self::$cacheFile)) {
            $cache = json_decode(file_get_contents(self::$cacheFile), true) ?: [];
        }

        $cache[$clientId] = [
            'access_token' => $accessToken,
            'expires_at' => time() + min($expiresIn - 300, self::$cacheTimeout), // 5 min buffer
            'created_at' => time()
        ];

        // Ensure cache directory exists
        $cacheDir = dirname(self::$cacheFile);
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        file_put_contents(self::$cacheFile, json_encode($cache));
    }

    public static function clearToken($clientId)
    {
        if (!file_exists(self::$cacheFile)) {
            return;
        }

        $cache = json_decode(file_get_contents(self::$cacheFile), true);
        if ($cache && isset($cache[$clientId])) {
            unset($cache[$clientId]);
            file_put_contents(self::$cacheFile, json_encode($cache));
        }
    }
}

/**
 * Production error handler for PayPal operations
 */
function handlePayPalProductionError($error, $context = [])
{
    // Log detailed error for debugging
    logPayPalDebug('PayPal Production Error', [
        'error' => $error,
        'context' => $context,
        'backtrace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5)
    ]);

    // Return user-friendly error messages
    if (strpos($error, 'authentication') !== false || strpos($error, 'unauthorized') !== false) {
        return 'Payment system authentication error. Please contact support.';
    }

    if (strpos($error, 'network') !== false || strpos($error, 'connection') !== false) {
        return 'Payment system temporarily unavailable. Please try again in a few moments.';
    }

    if (strpos($error, 'timeout') !== false) {
        return 'Payment request timed out. Please try again.';
    }

    if (strpos($error, 'amount') !== false || strpos($error, 'currency') !== false) {
        return 'Invalid payment amount or currency. Please review your order.';
    }

    // Generic production error
    return 'Payment processing temporarily unavailable. Please try again later.';
}

/**
 * Performance monitoring for PayPal operations
 */
class PayPalPerformanceMonitor
{
    private static $startTime;
    private static $memoryStart;

    public static function start()
    {
        self::$startTime = microtime(true);
        self::$memoryStart = memory_get_usage();
    }

    public static function end($operation)
    {
        $duration = microtime(true) - self::$startTime;
        $memoryUsed = memory_get_usage() - self::$memoryStart;

        logPayPalDebug('PayPal Performance', [
            'operation' => $operation,
            'duration_seconds' => round($duration, 3),
            'memory_used_mb' => round($memoryUsed / 1024 / 1024, 2),
            'peak_memory_mb' => round(memory_get_peak_usage() / 1024 / 1024, 2)
        ]);

        // Alert if operation is slow
        if ($duration > 10) {
            logPayPalError('Slow PayPal operation detected', [
                'operation' => $operation,
                'duration' => $duration,
                'memory_used' => $memoryUsed
            ]);
        }
    }
}

/**
 * Validate PayPal environment configuration
 */
function validatePayPalEnvironment()
{
    $errors = [];

    // Check PHP extensions
    if (!extension_loaded('curl')) {
        $errors[] = 'cURL extension is required';
    }

    if (!extension_loaded('json')) {
        $errors[] = 'JSON extension is required';
    }

    if (!extension_loaded('openssl')) {
        $errors[] = 'OpenSSL extension is required';
    }

    // Check PHP version
    if (version_compare(PHP_VERSION, '7.4.0', '<')) {
        $errors[] = 'PHP 7.4 or higher is required';
    }

    // Check memory limit
    $memoryLimit = ini_get('memory_limit');
    if ($memoryLimit !== '-1' && intval($memoryLimit) < 128) {
        $errors[] = 'Memory limit should be at least 128M';
    }

    // Check directory permissions
    if (!is_writable('../logs')) {
        $errors[] = 'Logs directory is not writable';
    }

    if (!empty($errors)) {
        logPayPalError('PayPal environment validation failed', $errors);
        return false;
    }

    return true;
}

// Initialize performance monitoring
register_shutdown_function(function () {
    if (defined('PAYPAL_OPERATION_START')) {
        PayPalPerformanceMonitor::end('shutdown');
    }
});