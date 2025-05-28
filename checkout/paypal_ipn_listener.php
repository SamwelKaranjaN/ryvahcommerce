<?php
require_once '../config/database.php';
require_once '../vendor/autoload.php';
require_once '../includes/functions.php';
require_once '../includes/payment/TransactionLogger.php';

// Load PayPal configuration
$paypal_config = require_once '../config/paypal.php';

// Get POST data
$raw_post_data = file_get_contents('php://input');
$raw_post_array = explode('&', $raw_post_data);
$myPost = array();
foreach ($raw_post_array as $keyval) {
    $keyval = explode('=', $keyval);
    if (count($keyval) == 2) {
        $myPost[$keyval[0]] = urldecode($keyval[1]);
    }
}

// Read POST data
$req = 'cmd=_notify-validate';
foreach ($myPost as $key => $value) {
    $value = urlencode($value);
    $req .= "&$key=$value";
}

// Post back to PayPal system for validation
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $paypal_config['ipn_url']);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
$res = curl_exec($ch);
curl_close($ch);

if (strcmp($res, "VERIFIED") == 0) {
    // IPN verified, process it
    $conn = getDBConnection();
    $transactionLogger = new TransactionLogger($conn);

    // Get transaction details
    $payment_status = $_POST['payment_status'];
    $txn_id = $_POST['txn_id'];
    $receiver_email = $_POST['receiver_email'];
    $payment_amount = $_POST['mc_gross'];
    $payment_currency = $_POST['mc_currency'];
    $invoice_number = $_POST['invoice'];

    // Verify receiver email
    if ($receiver_email != $paypal_config['business_email']) {
        error_log("Invalid receiver email: $receiver_email");
        exit();
    }

    // Get order details
    $stmt = $conn->prepare("SELECT * FROM orders WHERE invoice_number = ?");
    $stmt->bind_param("s", $invoice_number);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();

    if (!$order) {
        error_log("Order not found for invoice: $invoice_number");
        exit();
    }

    // Verify payment amount
    if ($payment_amount != $order['total_amount']) {
        error_log("Payment amount mismatch: $payment_amount != " . $order['total_amount']);
        exit();
    }

    // Process payment based on status
    if ($payment_status == "Completed") {
        try {
            // Start transaction
            $conn->begin_transaction();

            // Update order status
            $stmt = $conn->prepare("
                UPDATE orders 
                SET payment_status = 'completed',
                    payment_method = 'paypal',
                    transaction_id = ?
                WHERE invoice_number = ?
            ");
            $stmt->bind_param("ss", $txn_id, $invoice_number);
            $stmt->execute();

            // Update invoice status
            $stmt = $conn->prepare("
                UPDATE invoices 
                SET status = 'paid'
                WHERE order_id = ?
            ");
            $stmt->bind_param("i", $order['id']);
            $stmt->execute();

            // Add payment record
            $stmt = $conn->prepare("
                INSERT INTO order_payments (
                    order_id,
                    payment_method,
                    transaction_id,
                    amount,
                    status,
                    payment_date
                ) VALUES (?, 'paypal', ?, ?, 'completed', NOW())
            ");
            $stmt->bind_param("isd", $order['id'], $txn_id, $payment_amount);
            $stmt->execute();

            // Add status history
            $stmt = $conn->prepare("
                INSERT INTO order_status_history (
                    order_id,
                    status,
                    notes
                ) VALUES (?, 'completed', 'Payment completed via PayPal')
            ");
            $stmt->bind_param("i", $order['id']);
            $stmt->execute();

            // Clear cart
            $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
            $stmt->bind_param("i", $order['user_id']);
            $stmt->execute();

            // Log transaction
            $transactionLogger->logTransaction(
                $order['id'],
                'paypal',
                'success',
                $txn_id,
                null
            );

            // Commit transaction
            $conn->commit();

            // Log success
            error_log("Payment completed successfully for order: " . $invoice_number);
        } catch (Exception $e) {
            // Rollback transaction
            $conn->rollback();

            // Log error
            error_log("Error processing payment: " . $e->getMessage());

            // Log failed transaction
            $transactionLogger->logTransaction(
                $order['id'],
                'paypal',
                'failed',
                $txn_id,
                $e->getMessage()
            );
        }
    } else if ($payment_status == "Failed" || $payment_status == "Denied" || $payment_status == "Expired") {
        try {
            // Start transaction
            $conn->begin_transaction();

            // Update order status
            $stmt = $conn->prepare("
                UPDATE orders 
                SET payment_status = 'failed'
                WHERE invoice_number = ?
            ");
            $stmt->bind_param("s", $invoice_number);
            $stmt->execute();

            // Add status history
            $stmt = $conn->prepare("
                INSERT INTO order_status_history (
                    order_id,
                    status,
                    notes
                ) VALUES (?, 'failed', 'Payment failed via PayPal: $payment_status')
            ");
            $stmt->bind_param("i", $order['id']);
            $stmt->execute();

            // Log transaction
            $transactionLogger->logTransaction(
                $order['id'],
                'paypal',
                'failed',
                $txn_id,
                "Payment status: $payment_status"
            );

            // Commit transaction
            $conn->commit();

            // Log failure
            error_log("Payment failed for order: " . $invoice_number . " with status: " . $payment_status);
        } catch (Exception $e) {
            // Rollback transaction
            $conn->rollback();

            // Log error
            error_log("Error processing failed payment: " . $e->getMessage());
        }
    }
} else if (strcmp($res, "INVALID") == 0) {
    // IPN invalid, log for manual investigation
    error_log("Invalid IPN received: " . $raw_post_data);
}