<?php
require_once 'bootstrap.php';
require_once 'cart.php';
require_once '../config/stripe.php';
require_once '../config/paypal.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'User not logged in'
    ]);
    exit;
}

// Check if payment method is set
if (!isset($_POST['payment_method'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Payment method not specified'
    ]);
    exit;
}

$payment_method = $_POST['payment_method'];
$cart_data = getCartItems();
$cart_total = $cart_data['total'];

// Get shipping details from session
$shipping_details = $_SESSION['shipping_details'] ?? null;
if (!$shipping_details) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Shipping details not found'
    ]);
    exit;
}

// Validate payment method
if (!in_array($payment_method, ['paypal', 'card'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid payment method'
    ]);
    exit;
}

try {
    // Generate a unique order ID
    $order_id = uniqid('ORD-');

    switch ($payment_method) {
        case 'paypal':
            require_once 'vendor/autoload.php';

            try {
                // PayPal API configuration
                $paypal = new \PayPal\Rest\ApiContext(
                    new \PayPal\Auth\OAuthTokenCredential(
                        PAYPAL_CLIENT_ID,
                        PAYPAL_CLIENT_SECRET
                    )
                );

                // Create payment
                $payment = new \PayPal\Api\Payment();
                $payment->setIntent('sale')
                    ->setPayer(
                        (new \PayPal\Api\Payer())
                            ->setPaymentMethod('paypal')
                    )
                    ->setTransactions([
                        (new \PayPal\Api\Transaction())
                            ->setAmount(
                                (new \PayPal\Api\Amount())
                                    ->setTotal($cart_total)
                                    ->setCurrency('USD')
                            )
                            ->setDescription("Order #" . $order_id)
                    ])
                    ->setRedirectUrls(
                        (new \PayPal\Api\RedirectUrls())
                            ->setReturnUrl(SITE_URL . '/pages/payment_success.php')
                            ->setCancelUrl(SITE_URL . '/pages/payment_cancel.php')
                    );

                $payment->create($paypal);

                // Store payment information in session
                $_SESSION['payment_method'] = 'paypal';
                $_SESSION['payment_id'] = $payment->getId();
                $_SESSION['order_id'] = $order_id;

                echo json_encode([
                    'success' => true,
                    'redirect_url' => $payment->getApprovalLink()
                ]);
                exit;
            } catch (Exception $e) {
                error_log($e->getMessage());
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => 'PayPal payment failed. Please try again.'
                ]);
                exit;
            }
            break;

        case 'card':
            require_once 'vendor/autoload.php';
            \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

            try {
                // Create Stripe session
                $session = \Stripe\Checkout\Session::create([
                    'payment_method_types' => ['card'],
                    'line_items' => [[
                        'price_data' => [
                            'currency' => 'usd',
                            'unit_amount' => $cart_total * 100, // Convert to cents
                            'product_data' => [
                                'name' => 'Order #' . $order_id,
                            ],
                        ],
                        'quantity' => 1,
                    ]],
                    'mode' => 'payment',
                    'success_url' => SITE_URL . '/pages/payment_success.php',
                    'cancel_url' => SITE_URL . '/pages/payment_cancel.php',
                    'customer_email' => $shipping_details['email'],
                    'metadata' => [
                        'order_id' => $order_id
                    ]
                ]);

                // Store payment information in session
                $_SESSION['payment_method'] = 'stripe';
                $_SESSION['payment_id'] = $session->payment_intent;
                $_SESSION['order_id'] = $order_id;

                echo json_encode([
                    'success' => true,
                    'session_id' => $session->id
                ]);
                exit;
            } catch (Exception $e) {
                error_log($e->getMessage());
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => 'Stripe payment failed. Please try again.'
                ]);
                exit;
            }
            break;
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Payment processing failed. Please try again.'
    ]);
    exit;
}