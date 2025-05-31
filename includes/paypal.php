<?php
class PayPal
{
    private $client_id;
    private $client_secret;
    private $base_url;
    private $access_token;

    public function __construct()
    {
        $this->client_id = 'ATDlTjCCHr_gWONRi3MFpZPHn0g9qXyJ-fqcptCNq8gHdntR4R1u0s_e7xGUaMz0_W_c21J_lSpJKKh3';
        $this->client_secret = 'ENPuvo1FewTnDp8tGrCvez7nxwPM2kI5loLKfO7swl7XJJaEbtW3q7PAhMWiq7ySXL8347JcQg4IaQ82';
        $this->base_url = 'https://api-m.sandbox.paypal.com'; // Sandbox URL for testing
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
            throw new Exception('Failed to get access token: ' . curl_error($ch));
        }
        curl_close($ch);

        $response = json_decode($result, true);
        if (!isset($response['access_token'])) {
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
            throw new Exception('Failed to create order: ' . curl_error($ch));
        }
        curl_close($ch);

        $response = json_decode($result, true);
        if (!isset($response['id'])) {
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
            throw new Exception('Failed to capture order: ' . curl_error($ch));
        }
        curl_close($ch);

        $response = json_decode($result, true);
        if (!isset($response['id'])) {
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
            throw new Exception('Failed to get order: ' . curl_error($ch));
        }
        curl_close($ch);

        $response = json_decode($result, true);
        if (!isset($response['id'])) {
            throw new Exception('Invalid response from PayPal: ' . $result);
        }

        return $response;
    }
}
