<?php
require_once __DIR__ . '/../vendor/autoload.php';

use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;

class PayPalHelper
{
    private $client;
    private $config;

    public function __construct()
    {
        error_log("Initializing PayPalHelper");
        $this->config = require __DIR__ . '/../config/paypal.php';
        error_log("PayPal config loaded: " . print_r($this->config, true));

        $environment = $this->config['mode'] === 'live'
            ? new ProductionEnvironment($this->config['client_id'], $this->config['client_secret'])
            : new SandboxEnvironment($this->config['client_id'], $this->config['client_secret']);

        error_log("PayPal environment created with mode: " . $this->config['mode']);
        $this->client = new PayPalHttpClient($environment);
    }

    public function createOrder($items, $total, $tax)
    {
        error_log("Creating PayPal order with items: " . print_r($items, true));
        $request = new OrdersCreateRequest();
        $request->prefer('return=representation');

        $request->body = [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'amount' => [
                    'currency_code' => 'USD',
                    'value' => number_format($total, 2, '.', ''),
                    'breakdown' => [
                        'item_total' => [
                            'currency_code' => 'USD',
                            'value' => number_format($total - $tax, 2, '.', '')
                        ],
                        'tax_total' => [
                            'currency_code' => 'USD',
                            'value' => number_format($tax, 2, '.', '')
                        ]
                    ]
                ],
                'items' => array_map(function ($item) {
                    return [
                        'name' => $item['name'],
                        'unit_amount' => [
                            'currency_code' => 'USD',
                            'value' => number_format($item['price'], 2, '.', '')
                        ],
                        'quantity' => $item['quantity'],
                        'category' => 'PHYSICAL_GOODS'
                    ];
                }, $items)
            ]],
            'application_context' => [
                'return_url' => 'https://' . $_SERVER['HTTP_HOST'] . '/checkout/success.php',
                'cancel_url' => 'https://' . $_SERVER['HTTP_HOST'] . '/checkout/cancel.php'
            ]
        ];

        error_log("PayPal request body: " . print_r($request->body, true));

        try {
            error_log("Executing PayPal request");
            $response = $this->client->execute($request);
            error_log("PayPal response received: " . print_r($response, true));

            return [
                'success' => true,
                'order_id' => $response->result->id
            ];
        } catch (\Exception $e) {
            error_log('PayPal Create Order Error: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            return [
                'success' => false,
                'message' => 'Failed to create PayPal order: ' . $e->getMessage()
            ];
        }
    }

    public function captureOrder($orderId)
    {
        error_log("Capturing PayPal order: " . $orderId);
        $request = new OrdersCaptureRequest($orderId);

        try {
            error_log("Executing PayPal capture request");
            $response = $this->client->execute($request);
            error_log("PayPal capture response: " . print_r($response, true));

            return [
                'success' => true,
                'order_id' => $response->result->id,
                'status' => $response->result->status
            ];
        } catch (\Exception $e) {
            error_log('PayPal Capture Order Error: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            return [
                'success' => false,
                'message' => 'Failed to capture PayPal order: ' . $e->getMessage()
            ];
        }
    }
}
