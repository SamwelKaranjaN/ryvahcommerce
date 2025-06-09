<?php

/**
 * Email Debugging Tool
 * Comprehensive email delivery testing and debugging
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

require_once 'includes/bootstrap.php';

// Include email functions
if (file_exists('includes/email_functions.php')) {
    require_once 'includes/email_functions.php';
}

// Check email config
if (!file_exists('includes/email_config.php')) {
    die('❌ Email configuration file not found');
}

require_once 'includes/email_config.php';
require_once 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Debug Tool</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        margin: 20px;
        background: #f5f5f5;
    }

    .container {
        max-width: 1000px;
        margin: 0 auto;
        background: white;
        padding: 20px;
        border-radius: 8px;
    }

    .debug-section {
        margin: 20px 0;
        padding: 15px;
        border-left: 4px solid #007bff;
        background: #f8f9fa;
    }

    .success {
        border-left-color: #28a745;
        background: #d4edda;
        color: #155724;
    }

    .error {
        border-left-color: #dc3545;
        background: #f8d7da;
        color: #721c24;
    }

    .warning {
        border-left-color: #ffc107;
        background: #fff3cd;
        color: #856404;
    }

    .btn {
        padding: 10px 20px;
        background: #007bff;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        margin: 5px;
    }

    .btn:hover {
        background: #0056b3;
    }

    pre {
        background: #f1f1f1;
        padding: 10px;
        border-radius: 4px;
        overflow-x: auto;
        font-size: 12px;
    }

    .config-item {
        margin: 5px 0;
    }

    h1 {
        color: #007bff;
        text-align: center;
    }

    h2 {
        color: #495057;
        border-bottom: 2px solid #dee2e6;
        padding-bottom: 5px;
    }
    </style>
</head>

<body>
    <div class="container">
        <h1>📧 Email Debugging Tool</h1>

        <h2>📋 Email Configuration Check</h2>
        <div class="debug-section">
            <?php
            $configs = [
                'SMTP_HOST' => defined('SMTP_HOST') ? SMTP_HOST : 'NOT DEFINED',
                'SMTP_PORT' => defined('SMTP_PORT') ? SMTP_PORT : 'NOT DEFINED',
                'SMTP_SECURE' => defined('SMTP_SECURE') ? SMTP_SECURE : 'NOT DEFINED',
                'SMTP_AUTH' => defined('SMTP_AUTH') ? (SMTP_AUTH ? 'TRUE' : 'FALSE') : 'NOT DEFINED',
                'SMTP_USERNAME' => defined('SMTP_USERNAME') ? SMTP_USERNAME : 'NOT DEFINED',
                'SMTP_PASSWORD' => defined('SMTP_PASSWORD') ? (SMTP_PASSWORD ? '[SET - ' . strlen(SMTP_PASSWORD) . ' chars]' : '[EMPTY]') : 'NOT DEFINED',
                'FROM_EMAIL' => defined('FROM_EMAIL') ? FROM_EMAIL : 'NOT DEFINED',
                'FROM_NAME' => defined('FROM_NAME') ? FROM_NAME : 'NOT DEFINED',
                'ADMIN_EMAIL' => defined('ADMIN_EMAIL') ? ADMIN_EMAIL : 'NOT DEFINED'
            ];

            foreach ($configs as $key => $value) {
                $status = ($value !== 'NOT DEFINED' && $value !== '[EMPTY]') ? '✅' : '❌';
                echo "<div class='config-item'>{$status} <strong>{$key}:</strong> " . htmlspecialchars($value) . "</div>";
            }
            ?>
        </div>

        <?php if (isset($_POST['test_simple_email'])): ?>
        <h2>📨 Simple Email Test Results</h2>
        <div class="debug-section">
            <?php
                try {
                    $test_email = $_POST['test_email'] ?? 'test@example.com';

                    $mail = new PHPMailer(true);

                    // Enable verbose debug output
                    $mail->SMTPDebug = 2;
                    $mail->Debugoutput = function ($str, $level) {
                        echo "<div style='color: #666; font-size: 12px;'>[DEBUG] " . htmlspecialchars($str) . "</div>";
                    };

                    // Server settings
                    $mail->isSMTP();
                    $mail->Host = SMTP_HOST;
                    $mail->SMTPAuth = SMTP_AUTH;
                    $mail->Username = SMTP_USERNAME;
                    $mail->Password = SMTP_PASSWORD;
                    $mail->SMTPSecure = SMTP_SECURE;
                    $mail->Port = SMTP_PORT;

                    // Recipients
                    $mail->setFrom(FROM_EMAIL, FROM_NAME);
                    $mail->addAddress($test_email);

                    // Content
                    $mail->isHTML(true);
                    $mail->Subject = 'Test Email from Ryvah Commerce - ' . date('Y-m-d H:i:s');
                    $mail->Body = "
                <h2>✅ Test Email Successful!</h2>
                <p>This is a test email from your Ryvah Commerce system.</p>
                <p><strong>Sent at:</strong> " . date('Y-m-d H:i:s') . "</p>
                <p><strong>From:</strong> " . FROM_EMAIL . "</p>
                <p><strong>Server:</strong> " . SMTP_HOST . ":" . SMTP_PORT . "</p>
                <p>If you receive this email, your email configuration is working correctly!</p>
                ";
                    $mail->AltBody = "Test email from Ryvah Commerce sent at " . date('Y-m-d H:i:s');

                    echo "<h3>🔄 Attempting to send email...</h3>";
                    echo "<pre>";

                    $result = $mail->send();

                    echo "</pre>";

                    if ($result) {
                        echo "<div class='debug-section success'>";
                        echo "<h3>✅ Email Sent Successfully!</h3>";
                        echo "<p><strong>To:</strong> " . htmlspecialchars($test_email) . "</p>";
                        echo "<p><strong>Subject:</strong> " . htmlspecialchars($mail->Subject) . "</p>";
                        echo "<p><strong>Message ID:</strong> " . $mail->getLastMessageID() . "</p>";
                        echo "</div>";

                        echo "<div class='debug-section warning'>";
                        echo "<h3>⚠️ If you don't see the email, check:</h3>";
                        echo "<ul>";
                        echo "<li>📁 <strong>Spam/Junk folder</strong> - Most common issue!</li>";
                        echo "<li>📧 <strong>Email address spelling</strong> - Make sure it's correct</li>";
                        echo "<li>⏰ <strong>Delivery delay</strong> - Some servers take 5-15 minutes</li>";
                        echo "<li>🚫 <strong>Email filters</strong> - Check if blocked by your email provider</li>";
                        echo "<li>🏢 <strong>Corporate firewall</strong> - May block emails from new domains</li>";
                        echo "</ul>";
                        echo "</div>";
                    } else {
                        echo "<div class='debug-section error'>";
                        echo "<h3>❌ Email Failed to Send</h3>";
                        echo "<p>PHPMailer returned false but no exception was thrown.</p>";
                        echo "</div>";
                    }
                } catch (Exception $e) {
                    echo "</pre>";
                    echo "<div class='debug-section error'>";
                    echo "<h3>❌ Email Error</h3>";
                    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
                    echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
                    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
                    echo "</div>";

                    // Common error solutions
                    echo "<div class='debug-section warning'>";
                    echo "<h3>🔧 Common Solutions:</h3>";
                    echo "<ul>";
                    if (strpos($e->getMessage(), 'connect') !== false) {
                        echo "<li>🌐 <strong>Connection Error:</strong> Check SMTP host and port</li>";
                        echo "<li>🔥 <strong>Firewall:</strong> Your server might be blocking outgoing SMTP</li>";
                    }
                    if (strpos($e->getMessage(), 'authentication') !== false || strpos($e->getMessage(), 'Username') !== false) {
                        echo "<li>🔑 <strong>Authentication Error:</strong> Check username and password</li>";
                        echo "<li>📱 <strong>App Password:</strong> Gmail/Outlook may require app-specific passwords</li>";
                    }
                    if (strpos($e->getMessage(), 'TLS') !== false || strpos($e->getMessage(), 'SSL') !== false) {
                        echo "<li>🔒 <strong>Security Error:</strong> Try different SMTP_SECURE setting (tls/ssl)</li>";
                    }
                    echo "</ul>";
                    echo "</div>";
                }
                ?>
        </div>
        <?php endif; ?>

        <?php if (isset($_POST['test_order_email'])): ?>
        <h2>📦 Order Email Test Results</h2>
        <div class="debug-section">
            <?php
                // Check if we have orders
                try {
                    $stmt = $conn->prepare("SELECT id, invoice_number, created_at FROM orders WHERE payment_status = 'completed' ORDER BY created_at DESC LIMIT 3");
                    $stmt->execute();
                    $orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

                    if (!empty($orders)) {
                        $test_order_id = $orders[0]['id'];
                        echo "<p>Testing with Order ID: <strong>{$test_order_id}</strong> (Invoice: {$orders[0]['invoice_number']})</p>";

                        if (function_exists('sendOrderNotificationEmail')) {
                            echo "<h3>🔄 Sending order notification email...</h3>";

                            // Capture any output/errors
                            ob_start();
                            $email_result = sendOrderNotificationEmail($test_order_id);
                            $output = ob_get_clean();

                            if ($output) {
                                echo "<div class='debug-section warning'>";
                                echo "<h4>📝 Function Output:</h4>";
                                echo "<pre>" . htmlspecialchars($output) . "</pre>";
                                echo "</div>";
                            }

                            if ($email_result) {
                                echo "<div class='debug-section success'>";
                                echo "<h3>✅ Order Email Function Completed</h3>";
                                echo "<p>The function returned TRUE, but check the debug output above for actual sending status.</p>";
                                echo "</div>";
                            } else {
                                echo "<div class='debug-section warning'>";
                                echo "<h3>⚠️ Order Email Function Returned False</h3>";
                                echo "<p>This could mean:</p>";
                                echo "<ul>";
                                echo "<li>🔍 Order contains only ebooks (email skipped intentionally)</li>";
                                echo "<li>📧 Email sending failed</li>";
                                echo "<li>🗃️ Order data not found</li>";
                                echo "</ul>";
                                echo "</div>";
                            }
                        } else {
                            echo "<div class='debug-section error'>";
                            echo "<p>❌ <code>sendOrderNotificationEmail()</code> function not found!</p>";
                            echo "</div>";
                        }
                    } else {
                        echo "<div class='debug-section warning'>";
                        echo "<p>⚠️ No completed orders found in database. Please complete a test order first.</p>";
                        echo "</div>";
                    }
                } catch (Exception $e) {
                    echo "<div class='debug-section error'>";
                    echo "<p>❌ Database Error: " . htmlspecialchars($e->getMessage()) . "</p>";
                    echo "</div>";
                }
                ?>
        </div>
        <?php endif; ?>

        <h2>🧪 Email Tests</h2>

        <div class="debug-section">
            <h3>📨 Test 1: Simple Email Test</h3>
            <p>Send a basic test email to verify SMTP configuration:</p>
            <form method="post">
                <input type="email" name="test_email" placeholder="Enter your email address"
                    value="<?= htmlspecialchars($_POST['test_email'] ?? '') ?>" required
                    style="padding: 8px; width: 300px; margin-right: 10px;">
                <button type="submit" name="test_simple_email" class="btn">📧 Send Test Email</button>
            </form>
        </div>

        <div class="debug-section">
            <h3>📦 Test 2: Order Notification Test</h3>
            <p>Test the actual order notification email function:</p>
            <form method="post">
                <button type="submit" name="test_order_email" class="btn">📦 Test Order Email</button>
            </form>
        </div>

        <h2>📋 Email Troubleshooting Checklist</h2>
        <div class="debug-section">
            <h3>✅ Common Issues and Solutions:</h3>
            <ol>
                <li><strong>📁 Check Spam/Junk Folder</strong> - This is the #1 reason emails aren't found!</li>
                <li><strong>📧 Verify Email Address</strong> - Make sure ADMIN_EMAIL is your correct email</li>
                <li><strong>⏰ Wait for Delivery</strong> - Some email servers have delays (5-15 minutes)</li>
                <li><strong>🔑 Gmail/Outlook App Passwords</strong> - Use app-specific passwords, not your main password
                </li>
                <li><strong>🌐 SMTP Settings</strong> - Common ports: 587 (TLS), 465 (SSL), 25 (plain)</li>
                <li><strong>🔥 Server Firewall</strong> - Your hosting provider might block outgoing SMTP</li>
                <li><strong>📝 Email Logs</strong> - Check your server's email logs for delivery attempts</li>
                <li><strong>🏢 Corporate Email</strong> - Work emails often have strict filtering</li>
            </ol>
        </div>

        <h2>🔧 Quick Config Fixes</h2>
        <div class="debug-section">
            <h3>📧 Popular Email Provider Settings:</h3>
            <pre>
<strong>Gmail:</strong>
SMTP_HOST = 'smtp.gmail.com'
SMTP_PORT = 587
SMTP_SECURE = 'tls'
SMTP_USERNAME = 'your-email@gmail.com'
SMTP_PASSWORD = 'your-app-password' (not regular password!)

<strong>Outlook/Hotmail:</strong>
SMTP_HOST = 'smtp-mail.outlook.com'
SMTP_PORT = 587
SMTP_SECURE = 'tls'

<strong>Yahoo:</strong>
SMTP_HOST = 'smtp.mail.yahoo.com'
SMTP_PORT = 587
SMTP_SECURE = 'tls'

<strong>cPanel/Shared Hosting:</strong>
SMTP_HOST = 'mail.yourdomain.com'
SMTP_PORT = 587 or 465
SMTP_SECURE = 'tls' or 'ssl'
            </pre>
        </div>

        <div style="text-align: center; margin-top: 30px;">
            <a href="test_email_success.php" class="btn">📊 Back to Main Test</a>
            <button onclick="location.reload()" class="btn">🔄 Refresh Page</button>
        </div>

        <p style="text-align: center; margin-top: 20px; color: #666;">
            <small>Debug tool created at <?= date('Y-m-d H:i:s') ?></small>
        </p>
    </div>
</body>

</html>