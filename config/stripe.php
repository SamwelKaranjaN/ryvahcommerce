<?php
// Stripe API Configuration
define('STRIPE_SECRET_KEY', 'YOUR_STRIPE_SECRET_KEY'); // Replace with your Stripe secret key
define('STRIPE_PUBLISHABLE_KEY', 'YOUR_STRIPE_PUBLISHABLE_KEY'); // Replace with your Stripe publishable key
define('STRIPE_CURRENCY', 'usd');

// Enhanced error logging for Stripe
function logStripeError($message, $context = [])
{
    $log_message = date('Y-m-d H:i:s') . " - Stripe Error: " . $message;
    if (!empty($context)) {
        $log_message .= " - Context: " . json_encode($context);
    }
    error_log($log_message, 3, __DIR__ . '/../logs/stripe.log');
}

// Initialize Stripe
$autoloader = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoloader)) {
    require_once $autoloader;
    \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);
} else {
    logStripeError("Stripe autoloader not found");
    die('Please run "composer install" to install the required dependencies.');
}

// Function to create a payment intent with enhanced validation
function createStripePaymentIntent($amount, $order_id)
{
    try {
        // Validate amount
        if (!is_numeric($amount) || $amount <= 0) {
            throw new Exception("Invalid payment amount");
        }

        $payment_intent = \Stripe\PaymentIntent::create([
            'amount' => $amount * 100, // Convert to cents
            'currency' => STRIPE_CURRENCY,
            'metadata' => [
                'order_id' => $order_id
            ],
            'automatic_payment_methods' => [
                'enabled' => true,
            ],
        ]);

        logStripeError("Payment intent created successfully", [
            'payment_intent_id' => $payment_intent->id,
            'order_id' => $order_id
        ]);

        return $payment_intent;
    } catch (Exception $e) {
        logStripeError("Failed to create payment intent: " . $e->getMessage(), [
            'order_id' => $order_id,
            'amount' => $amount
        ]);
        throw new Exception('Error creating Stripe payment intent: ' . $e->getMessage());
    }
}

// Function to confirm a payment intent with enhanced validation
function confirmStripePayment($payment_intent_id)
{
    try {
        if (empty($payment_intent_id)) {
            throw new Exception("Payment intent ID is required");
        }

        $payment_intent = \Stripe\PaymentIntent::retrieve($payment_intent_id);

        // Log payment status
        logStripeError("Payment status retrieved", [
            'payment_intent_id' => $payment_intent_id,
            'status' => $payment_intent->status
        ]);

        return $payment_intent;
    } catch (Exception $e) {
        logStripeError("Failed to confirm payment: " . $e->getMessage(), [
            'payment_intent_id' => $payment_intent_id
        ]);
        throw new Exception('Error confirming Stripe payment: ' . $e->getMessage());
    }
}