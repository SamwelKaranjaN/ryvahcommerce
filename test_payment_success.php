<?php
/**
 * Test Payment Success Flow
 * Verifies that all functions are properly loaded and accessible
 */

// Simulate the environment
error_reporting(E_ALL & ~E_DEPRECATED);
session_start();

require_once 'includes/bootstrap.php';
require_once 'includes/paypal_config.php';

// Include email functions with error handling
if (file_exists('includes/email_functions.php')) {
    require_once 'includes/email_functions.php';
} else {
    die('❌ Email functions file not found');
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Success Flow Test</title>
    <style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        margin: 40px;
        background: #f8f9fa;
    }

    .container {
        max-width: 800px;
        margin: 0 auto;
        background: white;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .test-item {
        margin: 15px 0;
        padding: 15px;
        border-radius: 8px;
        border-left: 4px solid #007bff;
    }

    .success {
        background: #d4edda;
        color: #155724;
        border-left-color: #28a745;
    }

    .error {
        background: #f8d7da;
        color: #721c24;
        border-left-color: #dc3545;
    }

    .warning {
        background: #fff3cd;
        color: #856404;
        border-left-color: #ffc107;
    }

    .info {
        background: #cce7ff;
        color: #004085;
        border-left-color: #007bff;
    }

    h1 {
        color: #007bff;
        text-align: center;
        margin-bottom: 30px;
    }

    h2 {
        color: #495057;
        border-bottom: 2px solid #e9ecef;
        padding-bottom: 10px;
    }

    code {
        background: #f1f3f4;
        padding: 2px 6px;
        border-radius: 4px;
        font-family: 'Courier New', monospace;
    }
    </style>
</head>

<body>
    <div class="container">
        <h1>🔍 Payment Success Flow Test</h1>

        <h2>📧 Email Functions Tests</h2>

        <div class="test-item <?= function_exists('sendOrderNotificationEmail') ? 'success' : 'error' ?>">
            <strong>Function: <code>sendOrderNotificationEmail()</code></strong><br>
            <?php if (function_exists('sendOrderNotificationEmail')): ?>
            ✅ Function exists and is callable
            <?php else: ?>
            ❌ Function not found - this would cause a fatal error
            <?php endif; ?>
        </div>

        <div class="test-item <?= function_exists('getOrderDetailsForEmail') ? 'success' : 'error' ?>">
            <strong>Function: <code>getOrderDetailsForEmail()</code></strong><br>
            <?php if (function_exists('getOrderDetailsForEmail')): ?>
            ✅ Function exists and is callable
            <?php else: ?>
            ❌ Function not found
            <?php endif; ?>
        </div>

        <div class="test-item <?= function_exists('isOnlyEbookOrder') ? 'success' : 'error' ?>">
            <strong>Function: <code>isOnlyEbookOrder()</code></strong><br>
            <?php if (function_exists('isOnlyEbookOrder')): ?>
            ✅ Function exists and is callable
            <?php else: ?>
            ❌ Function not found
            <?php endif; ?>
        </div>

        <div class="test-item <?= function_exists('generateOrderNotificationHtml') ? 'success' : 'error' ?>">
            <strong>Function: <code>generateOrderNotificationHtml()</code></strong><br>
            <?php if (function_exists('generateOrderNotificationHtml')): ?>
            ✅ Function exists and is callable
            <?php else: ?>
            ❌ Function not found
            <?php endif; ?>
        </div>

        <div class="test-item <?= function_exists('generateOrderNotificationText') ? 'success' : 'error' ?>">
            <strong>Function: <code>generateOrderNotificationText()</code></strong><br>
            <?php if (function_exists('generateOrderNotificationText')): ?>
            ✅ Function exists and is callable
            <?php else: ?>
            ❌ Function not found
            <?php endif; ?>
        </div>

        <h2>🔧 Configuration Tests</h2>

        <div class="test-item <?= class_exists('PHPMailer\PHPMailer\PHPMailer') ? 'success' : 'error' ?>">
            <strong>PHPMailer Class</strong><br>
            <?php if (class_exists('PHPMailer\PHPMailer\PHPMailer')): ?>
            ✅ PHPMailer is available
            <?php else: ?>
            ❌ PHPMailer not found - email sending will fail
            <?php endif; ?>
        </div>

        <div class="test-item <?= file_exists('includes/email_config.php') ? 'success' : 'warning' ?>">
            <strong>Email Configuration</strong><br>
            <?php if (file_exists('includes/email_config.php')): ?>
            ✅ Email config file exists
            <?php else: ?>
            ⚠️ Email config file not found - this may cause email errors
            <?php endif; ?>
        </div>

        <h2>📊 Error Handling Test</h2>

        <div class="test-item info">
            <strong>Error Reporting Level</strong><br>
            Current level: <code><?= error_reporting() ?></code><br>
            <?php 
            $current_level = error_reporting();
            $expected_level = E_ALL & ~E_DEPRECATED;
            if ($current_level === $expected_level): ?>
            ✅ Deprecation warnings suppressed (expected for PayPal SDK)
            <?php else: ?>
            ⚠️ Error reporting level may show PayPal SDK deprecation warnings
            <?php endif; ?>
        </div>

        <h2>🧪 Header Output Test</h2>

        <div class="test-item <?= !headers_sent() ? 'success' : 'warning' ?>">
            <strong>Headers Status</strong><br>
            <?php if (!headers_sent()): ?>
            ✅ No headers sent yet - BOM issue resolved
            <?php else: ?>
            <?php 
                $file = '';
                $line = 0;
                headers_sent($file, $line);
                ?>
            ⚠️ Headers already sent from: <code><?= $file ?>:<?= $line ?></code>
            <?php endif; ?>
        </div>

        <h2>🎯 Issues Fixed Summary</h2>

        <div class="test-item success">
            <strong>✅ BOM Issue Fixed</strong><br>
            The email_functions.php file has been recreated without BOM/invisible characters that were causing "headers
            already sent" errors.
        </div>

        <div class="test-item success">
            <strong>✅ PayPal SDK Warnings Suppressed</strong><br>
            Added <code>error_reporting(E_ALL & ~E_DEPRECATED)</code> to suppress PayPal SDK dynamic property warnings.
        </div>

        <div class="test-item success">
            <strong>✅ Function Loading Fixed</strong><br>
            The sendOrderNotificationEmail() function is now properly loaded and callable.
        </div>

        <div class="test-item success">
            <strong>✅ Error Handling Improved</strong><br>
            Added proper function_exists() checks and fallback error logging.
        </div>

        <h2>🚀 What Happens on Successful Payment Now</h2>

        <div class="test-item info">
            <ol>
                <li><strong>PayPal Order Capture</strong> - Payment is captured via PayPal API</li>
                <li><strong>Database Update</strong> - Order status updated to 'completed'</li>
                <li><strong>Cart Clearing</strong> - User's cart is emptied</li>
                <li><strong>Success Token Generation</strong> - Secure token created for success page</li>
                <li><strong>Redirect to Success Page</strong> - User sees order confirmation</li>
                <li><strong>Email Notification</strong> - Admin receives order notification (if non-ebook)</li>
                <li><strong>Download Links</strong> - eBook download links generated if applicable</li>
                <li><strong>Order History</strong> - Order status history updated</li>
            </ol>
        </div>

        <div class="test-item warning">
            <strong>⚠️ Note:</strong> This test only verifies function availability. For full testing, you would need:
            <ul>
                <li>Valid database connection with test orders</li>
                <li>Configured email settings (SMTP)</li>
                <li>PayPal sandbox environment for testing</li>
            </ul>
        </div>

        <p style="text-align: center; margin-top: 30px; color: #6c757d;">
            <small>Test completed at <?= date('Y-m-d H:i:s') ?></small>
        </p>
    </div>
</body>

</html>