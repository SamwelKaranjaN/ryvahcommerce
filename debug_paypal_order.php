<?php
/**
 * PayPal Order Diagnostic Script
 * Helps diagnose issues with PayPal order capture
 */

require_once 'includes/config.php';
require_once 'includes/paypal_config.php';

// Only allow access in development
if (PAYPAL_ENVIRONMENT !== 'development' && PAYPAL_ENVIRONMENT !== 'sandbox') {
    die('This diagnostic script is only available in development/sandbox mode.');
}

// Function to diagnose PayPal order
function diagnosePayPalOrder($orderID)
{
    echo "<h2>PayPal Order Diagnostic Report</h2>\n";
    echo "<strong>Order ID:</strong> " . htmlspecialchars($orderID) . "<br>\n";
    echo "<strong>Environment:</strong> " . PAYPAL_ENVIRONMENT . "<br>\n";
    echo "<strong>Timestamp:</strong> " . date('Y-m-d H:i:s') . "<br>\n";
    echo "<hr>\n";

    try {
        // Initialize PayPal environment
        if (PAYPAL_ENVIRONMENT === 'sandbox') {
            $environment = new \PayPalCheckoutSdk\Core\SandboxEnvironment(PAYPAL_CLIENT_ID, PAYPAL_CLIENT_SECRET);
        } else {
            $environment = new \PayPalCheckoutSdk\Core\ProductionEnvironment(PAYPAL_CLIENT_ID, PAYPAL_CLIENT_SECRET);
        }

        $client = new \PayPalCheckoutSdk\Core\PayPalHttpClient($environment);

        // Get order details
        echo "<h3>1. Order Details</h3>\n";
        $request = new \PayPalCheckoutSdk\Orders\OrdersGetRequest($orderID);
        $response = $client->execute($request);
        
        echo "<pre>" . json_encode($response->result, JSON_PRETTY_PRINT) . "</pre>\n";
        
        $order = $response->result;
        echo "<strong>Status:</strong> " . $order->status . "<br>\n";
        echo "<strong>Intent:</strong> " . $order->intent . "<br>\n";
        
        if (isset($order->purchase_units[0])) {
            $purchaseUnit = $order->purchase_units[0];
            echo "<strong>Amount:</strong> " . $purchaseUnit->amount->currency_code . " " . $purchaseUnit->amount->value . "<br>\n";
            
            if (isset($purchaseUnit->payments)) {
                echo "<h4>Payment Information:</h4>\n";
                if (isset($purchaseUnit->payments->authorizations)) {
                    echo "<strong>Authorizations:</strong><br>\n";
                    foreach ($purchaseUnit->payments->authorizations as $auth) {
                        echo "- ID: " . $auth->id . ", Status: " . $auth->status . "<br>\n";
                    }
                }
                if (isset($purchaseUnit->payments->captures)) {
                    echo "<strong>Captures:</strong><br>\n";
                    foreach ($purchaseUnit->payments->captures as $capture) {
                        echo "- ID: " . $capture->id . ", Status: " . $capture->status . "<br>\n";
                    }
                }
            }
        }

        // Try to capture if order is approved but not captured
        if ($order->status === 'APPROVED') {
            echo "<hr>\n";
            echo "<h3>2. Capture Attempt</h3>\n";
            
            try {
                $captureRequest = new \PayPalCheckoutSdk\Orders\OrdersCaptureRequest($orderID);
                $captureRequest->prefer('return=representation');
                
                $captureResponse = $client->execute($captureRequest);
                
                echo "<strong>Capture Status:</strong> " . $captureResponse->result->status . "<br>\n";
                echo "<pre>" . json_encode($captureResponse->result, JSON_PRETTY_PRINT) . "</pre>\n";
                
            } catch (Exception $captureE) {
                echo "<strong>Capture Failed:</strong><br>\n";
                echo "<span style='color: red;'>" . htmlspecialchars($captureE->getMessage()) . "</span><br>\n";
                
                // Try to parse the error
                if (strpos($captureE->getMessage(), '{') !== false) {
                    $jsonStart = strpos($captureE->getMessage(), '{');
                    $jsonPart = substr($captureE->getMessage(), $jsonStart);
                    $errorData = json_decode($jsonPart, true);
                    
                    if ($errorData) {
                        echo "<h4>Error Details:</h4>\n";
                        echo "<pre>" . json_encode($errorData, JSON_PRETTY_PRINT) . "</pre>\n";
                        
                        if (isset($errorData['details'])) {
                            echo "<h4>Specific Issues:</h4>\n";
                            foreach ($errorData['details'] as $detail) {
                                echo "- <strong>" . $detail['issue'] . ":</strong> " . $detail['description'] . "<br>\n";
                            }
                        }
                        
                        if (isset($errorData['debug_id'])) {
                            echo "<strong>Debug ID:</strong> " . $errorData['debug_id'] . " (Use this when contacting PayPal support)<br>\n";
                        }
                    }
                }
            }
        } else {
            echo "<hr>\n";
            echo "<h3>2. Capture Not Attempted</h3>\n";
            echo "Order status is '" . $order->status . "', not 'APPROVED'. Cannot capture until approved by customer.<br>\n";
        }

    } catch (Exception $e) {
        echo "<h3>Error Getting Order Details</h3>\n";
        echo "<span style='color: red;'>" . htmlspecialchars($e->getMessage()) . "</span><br>\n";
    }
}

// Check if order ID is provided
if (isset($_GET['order_id'])) {
    $orderID = $_GET['order_id'];
    if (preg_match('/^[A-Z0-9]{13,17}$/', $orderID)) {
        diagnosePayPalOrder($orderID);
    } else {
        echo "<p style='color: red;'>Invalid PayPal order ID format. Order ID should be 13-17 alphanumeric characters.</p>\n";
    }
} else {
    echo "<h2>PayPal Order Diagnostic Tool</h2>\n";
    echo "<p>Use this tool to diagnose PayPal order issues.</p>\n";
    echo "<form method='GET'>\n";
    echo "    <label for='order_id'>PayPal Order ID:</label><br>\n";
    echo "    <input type='text' id='order_id' name='order_id' placeholder='e.g., 1AB23456CD789012E' style='width: 300px; padding: 8px; margin: 8px 0;'><br>\n";
    echo "    <button type='submit' style='padding: 8px 16px; background: #007bff; color: white; border: none; cursor: pointer;'>Diagnose Order</button>\n";
    echo "</form>\n";
    echo "<p><small>This tool is only available in development/sandbox environments.</small></p>\n";
}
?> 