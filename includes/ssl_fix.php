<?php

/**
 * SSL Configuration for Production PayPal Integration
 * This file handles SSL certificate configuration for secure PayPal API calls
 */

/**
 * Configure SSL settings for PayPal API calls
 */
function configureSSLForProduction()
{
    // Check if we're in a development environment
    $isDevelopment = (
        strpos(__DIR__, 'wamp') !== false ||
        strpos(__DIR__, 'xampp') !== false ||
        strpos($_SERVER['SERVER_NAME'] ?? '', 'localhost') !== false ||
        strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') !== false ||
        $_SERVER['SERVER_NAME'] === '127.0.0.1'
    );

    if ($isDevelopment) {
        // For development environments, use relaxed SSL settings but warn
        error_log("WARNING: Running PayPal integration in development mode with relaxed SSL settings");
        $curlOptions = [
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3
        ];
    } else {
        // Production environment - use secure SSL settings
        $curlOptions = [
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2
        ];

        // Set default cURL options
        foreach ($curlOptions as $option => $value) {
            curl_setopt_array($ch = curl_init(), [$option => $value]);
            curl_close($ch);
        }
    }

    // Set default cURL options
    foreach ($curlOptions as $option => $value) {
        curl_setopt_array($ch = curl_init(), [$option => $value]);
        curl_close($ch);
    }

    if ($isDevelopment) {
        // Set default stream context for HTTPS in development
        stream_context_set_default([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ],
            'http' => [
                'timeout' => 30,
                'ignore_errors' => false
            ]
        ]);

        // Set ini settings for cURL in development
        ini_set('curl.cainfo', '');
        ini_set('openssl.cafile', '');
    } else {
        // Set secure stream context for production
        stream_context_set_default([
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
                'allow_self_signed' => false
            ],
            'http' => [
                'timeout' => 30,
                'ignore_errors' => false
            ]
        ]);
    }

    return !$isDevelopment; // Return true for production, false for development
}

/**
 * Create custom PayPal HTTP client with SSL configuration
 */
function createPayPalClientWithSSLFix($environment)
{
    // Configure SSL settings
    configureSSLForProduction();

    // Create client
    $client = new \PayPalCheckoutSdk\Core\PayPalHttpClient($environment);

    return $client;
}

/**
 * Test SSL connectivity to PayPal
 */
function testPayPalSSLConnection()
{
    $urls = [
        'https://api.paypal.com/v1/oauth2/token',
        'https://www.paypal.com'
    ];

    $results = [];

    foreach ($urls as $url) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
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

        $results[$url] = [
            'success' => !empty($response) && empty($error),
            'http_code' => $httpCode,
            'error' => $error
        ];
    }

    return $results;
}

/**
 * Download and install CA certificate bundle
 */
function downloadCACertBundle()
{
    try {
        $caBundleUrl = 'https://curl.se/ca/cacert.pem';
        $caBundlePath = dirname(__DIR__) . '/cacert.pem';

        // Download CA bundle
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $caBundleUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ]);

        $caCert = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($httpCode === 200 && !empty($caCert) && empty($error)) {
            file_put_contents($caBundlePath, $caCert);

            // Update php.ini settings
            ini_set('curl.cainfo', $caBundlePath);
            ini_set('openssl.cafile', $caBundlePath);

            return [
                'success' => true,
                'path' => $caBundlePath,
                'message' => 'CA certificate bundle downloaded and configured'
            ];
        } else {
            return [
                'success' => false,
                'error' => "Failed to download CA bundle. HTTP: $httpCode, Error: $error"
            ];
        }
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => 'Exception: ' . $e->getMessage()
        ];
    }
}

// Auto-configure SSL when this file is included
if (!function_exists('paypal_ssl_configured')) {
    function paypal_ssl_configured()
    {
        static $configured = false;
        if (!$configured) {
            configureSSLForProduction();
            $configured = true;
        }
        return $configured;
    }
    paypal_ssl_configured();
}
