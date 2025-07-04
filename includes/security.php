<?php

/**
 * Security helper functions for the application
 */

// Ensure bootstrap is loaded first to avoid function redeclaration
if (!function_exists('getConfig')) {
    require_once __DIR__ . '/bootstrap.php';
}

/**
 * Verify CSRF token
 * @param string $token The token to verify
 * @return bool Whether the token is valid
 */
function verifyCSRFToken($token)
{
    if (!isset($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Get configuration value
 * Note: This function is now defined in bootstrap.php to avoid redeclaration conflicts
 * @param string $key Configuration key
 * @return mixed Configuration value
 */
// function getConfig($key) - moved to bootstrap.php

/**
 * Log security events
 * @param string $event Event type
 * @param array $data Event data
 */
function logSecurityEvent($event, $data)
{
    $log_file = __DIR__ . '/../logs/security.log';
    $timestamp = date('Y-m-d H:i:s');
    $user_id = $_SESSION['user_id'] ?? 'guest';
    $ip = $_SERVER['REMOTE_ADDR'];

    $log_entry = sprintf(
        "[%s] Event: %s | User: %s | IP: %s | Data: %s\n",
        $timestamp,
        $event,
        $user_id,
        $ip,
        json_encode($data)
    );

    file_put_contents($log_file, $log_entry, FILE_APPEND);
}

/**
 * PayPal API Client
 */
class PayPalClient
{
    private $client_id;
    private $client_secret;
    private $base_url;

    public function __construct($client_id, $client_secret, $sandbox = false)
    {
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
        $this->base_url = $sandbox ? 'https://api-m.sandbox.paypal.com' : 'https://api-m.paypal.com';
    }

    public function createOrder($data)
    {
        $access_token = $this->getAccessToken();

        $ch = curl_init($this->base_url . '/v2/checkout/orders');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $access_token
        ]);

        // Enable SSL verification for production
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            throw new Exception('Failed to create PayPal order: ' . curl_error($ch));
        }

        curl_close($ch);

        if ($http_code !== 201) {
            throw new Exception('Failed to create PayPal order: ' . $response);
        }

        return json_decode($response, true);
    }

    private function getAccessToken()
    {
        $ch = curl_init($this->base_url . '/v1/oauth2/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=client_credentials');
        curl_setopt($ch, CURLOPT_USERPWD, $this->client_id . ':' . $this->client_secret);

        // Enable SSL verification for production
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            throw new Exception('Failed to get access token: ' . curl_error($ch));
        }

        curl_close($ch);

        if ($http_code !== 200) {
            throw new Exception('Failed to get PayPal access token: ' . $response);
        }

        $data = json_decode($response, true);
        return $data['access_token'];
    }
}
