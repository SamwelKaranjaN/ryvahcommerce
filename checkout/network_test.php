<?php

/**
 * PayPal Network Connectivity Diagnostic Tool
 * Simple test to diagnose PayPal API connection issues
 */

require_once '../includes/bootstrap.php';
require_once '../includes/paypal_config.php';

// Check if user is authorized (admin only)
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    http_response_code(403);
    die('Unauthorized access');
}

header('Content-Type: application/json');

try {
    $diagnostics = testPayPalConnectivity();

    // Add additional server information
    $diagnostics['server_info'] = [
        'php_version' => PHP_VERSION,
        'curl_version' => curl_version()['version'] ?? 'Unknown',
        'openssl_version' => OPENSSL_VERSION_TEXT ?? 'Unknown',
        'server_time' => date('Y-m-d H:i:s'),
        'paypal_environment' => PAYPAL_ENVIRONMENT
    ];

    // Test specific PayPal endpoints
    $endpoints = [
        'auth' => 'https://api.paypal.com/v1/oauth2/token',
        'orders' => 'https://api.paypal.com/v2/checkout/orders'
    ];

    foreach ($endpoints as $name => $url) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_NOBODY => true
        ]);

        $start = microtime(true);
        curl_exec($ch);
        $time = round((microtime(true) - $start) * 1000, 2);

        $diagnostics['endpoints'][$name] = [
            'url' => $url,
            'http_code' => curl_getinfo($ch, CURLINFO_HTTP_CODE),
            'response_time_ms' => $time,
            'error' => curl_error($ch) ?: null
        ];

        curl_close($ch);
    }

    echo json_encode([
        'success' => true,
        'diagnostics' => $diagnostics,
        'recommendations' => generateRecommendations($diagnostics)
    ], JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'recommendations' => [
            'Check server configuration',
            'Verify PHP cURL extension is enabled',
            'Check firewall settings for outbound HTTPS connections'
        ]
    ], JSON_PRETTY_PRINT);
}

/**
 * Generate recommendations based on diagnostic results
 */
function generateRecommendations($diagnostics)
{
    $recommendations = [];

    if (!$diagnostics['can_resolve_dns']) {
        $recommendations[] = 'DNS resolution failed - check your DNS settings or internet connection';
    }

    if (!$diagnostics['can_connect_ssl']) {
        $recommendations[] = 'SSL connection failed - check firewall settings for port 443';
        $recommendations[] = 'Verify outbound HTTPS connections are allowed';
    }

    if (!$diagnostics['api_reachable']) {
        $recommendations[] = 'PayPal API is unreachable - contact your hosting provider';
        $recommendations[] = 'Check if PayPal domains are blocked by security software';
    }

    if (empty($recommendations)) {
        $recommendations[] = 'All connectivity tests passed - PayPal integration should work';
    }

    return $recommendations;
}
