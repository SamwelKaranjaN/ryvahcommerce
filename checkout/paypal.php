<?php
class PayPal
{
    private $client_id;
    private $client_secret;
    private $base_url;
    private $access_token;

    public function __construct()
    {
        $this->client_id = 'ARbQtWP1vIsYqgrKcL0v2hhlJA6NujGi26UWQz9Z4lsmPosxbSDPfzLSkaHtS8JRSvdysC99W0qvLyCI';
        $this->client_secret = 'EChoMRhi0vy7L_Defl5dqinOMbiWHxRmPG2e3ArjXXRQqHR1vwkg1IvHGTDxzwrOOuQR4n-z8ZteQiGc';
        $this->base_url = 'https://api-m.paypal.com';
        $this->access_token = $this->getAccessToken();
    }

    private function getAccessToken()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->base_url . '/v1/oauth2/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=client_credentials');
        curl_setopt($ch, CURLOPT_USERPWD, $this->client_id . ':' . $this->client_secret);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json', 'Accept-Language: en_US']);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            error_log("PayPal cURL Error (getAccessToken): " . curl_error($ch));
            throw new Exception('Failed to get access token: ' . curl_error($ch));
        }
        curl_close($ch);

        $response = json_decode($result, true);
        if (!isset($response['access_token'])) {
            error_log("PayPal Invalid Response (getAccessToken): " . $result);
            throw new Exception('Invalid response from PayPal: ' . $result);
        }

        return $response['access_token'];
    }

    public function createOrder($orderData)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->base_url . '/v2/checkout/orders');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($orderData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->access_token
        ]);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            error_log("PayPal cURL Error (createOrder): " . curl_error($ch));
            throw new Exception('Failed to create order: ' . curl_error($ch));
        }
        curl_close($ch);

        $response = json_decode($result, true);
        if (!isset($response['id'])) {
            error_log("PayPal Invalid Response (createOrder): " . $result);
            throw new Exception('Invalid response from PayPal: ' . $result);
        }

        return $response;
    }

    public function captureOrder($orderId)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->base_url . '/v2/checkout/orders/' . $orderId . '/capture');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->access_token
        ]);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            error_log("PayPal cURL Error (captureOrder): " . curl_error($ch));
            throw new Exception('Failed to capture order: ' . curl_error($ch));
        }
        curl_close($ch);

        $response = json_decode($result, true);
        if (!isset($response['id'])) {
            error_log("PayPal Invalid Response (captureOrder): " . $result);
            throw new Exception('Invalid response from PayPal: ' . $result);
        }

        return $response;
    }

    public function getOrder($orderId)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->base_url . '/v2/checkout/orders/' . $orderId);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->access_token
        ]);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            error_log("PayPal cURL Error (getOrder): " . curl_error($ch));
            throw new Exception('Failed to get order: ' . curl_error($ch));
        }
        curl_close($ch);

        $response = json_decode($result, true);
        if (!isset($response['id'])) {
            error_log("PayPal Invalid Response (getOrder): " . $result);
            throw new Exception('Invalid response from PayPal: ' . $result);
        }

        return $response;
    }
}