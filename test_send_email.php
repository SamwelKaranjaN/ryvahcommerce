<?php

/**
 * Test Send Email - Order Notification Test
 * This file sends a test order notification email to verify the email system is working
 */

session_start();
require_once 'includes/bootstrap.php';
require_once 'includes/email_functions.php';

// Check if this is a POST request to send the email
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_test'])) {
    try {
        // Create sample order data for testing
        $test_order_data = [
            'order' => [
                'id' => 999,
                'invoice_number' => 'TEST-' . date('Y') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
                'total_amount' => 127.50,
                'tax_amount' => 10.20,
                'created_at' => date('Y-m-d H:i:s'),
                'currency' => 'USD',
                'paypal_order_id' => 'TEST_' . strtoupper(bin2hex(random_bytes(8))),
                'full_name' => 'Test Customer',
                'email' => 'test.customer@example.com',
                'phone' => '+1 (555) 999-0000'
            ],
            'items' => [
                [
                    'id' => 1,
                    'name' => 'Test Product - Digital Marketing eBook',
                    'author' => 'Test Author',
                    'type' => 'ebook',
                    'price' => 39.99,
                    'quantity' => 2,
                    'subtotal' => 79.98
                ],
                [
                    'id' => 2,
                    'name' => 'Premium Business Course',
                    'author' => null,
                    'type' => 'course',
                    'price' => 47.52,
                    'quantity' => 1,
                    'subtotal' => 47.52
                ]
            ],
            'shipping_address' => [
                'street' => '123 Test Street, Unit 456',
                'city' => 'Test City',
                'state' => 'CA',
                'postal_code' => '90210',
                'country' => 'United States'
            ]
        ];

        // Use PHPMailer to send test email
        use PHPMailer\PHPMailer\PHPMailer;
        use PHPMailer\PHPMailer\SMTP;
        use PHPMailer\PHPMailer\Exception;

        $mail = new PHPMailer(true);
        
        // Server settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = SMTP_AUTH;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Port       = SMTP_PORT;
        
        // Recipients
        $mail->setFrom(FROM_EMAIL, FROM_NAME);
        $mail->addAddress(ADMIN_EMAIL); // This should be ryvah256@gmail.com
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = '🧪 TEST - New Order Notification - Order #' . $test_order_data['order']['invoice_number'];
        $mail->Body = generateOrderNotificationHtml($test_order_data);
        $mail->AltBody = generateOrderNotificationText($test_order_data);
        
        $mail->send();
        
        $success_message = "✅ Test email sent successfully to " . ADMIN_EMAIL;
        
        // Log the test email
        logPayPalError('Test order notification email sent', [
            'test_order_id' => $test_order_data['order']['id'],
            'recipient' => ADMIN_EMAIL,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        
    } catch (Exception $e) {
        $error_message = "❌ Failed to send test email: " . $e->getMessage();
        
        // Log the error
        logPayPalError('Test email failed: ' . $e->getMessage(), [
            'error_type' => 'test_email_failure',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Send Email - Ryvah Commerce</title>
    <style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        margin: 0;
        padding: 20px;
        background-color: #f8f9fa;
        color: #333;
    }

    .container {
        max-width: 800px;
        margin: 0 auto;
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .header {
        background: linear-gradient(135deg, #007bff, #0056b3);
        color: white;
        padding: 30px;
        text-align: center;
    }

    .header h1 {
        margin: 0 0 10px 0;
        font-size: 2.5rem;
    }

    .header p {
        margin: 0;
        opacity: 0.9;
        font-size: 1.1rem;
    }

    .content {
        padding: 40px;
    }

    .status-card {
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 25px;
        border-left: 4px solid;
    }

    .status-success {
        background: #d4edda;
        border-left-color: #28a745;
        color: #155724;
    }

    .status-error {
        background: #f8d7da;
        border-left-color: #dc3545;
        color: #721c24;
    }

    .status-info {
        background: #d1ecf1;
        border-left-color: #17a2b8;
        color: #0c5460;
    }

    .config-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin: 25px 0;
    }

    .config-item {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        border-left: 4px solid #007bff;
    }

    .config-item h4 {
        margin: 0 0 10px 0;
        color: #007bff;
    }

    .config-item p {
        margin: 5px 0;
        font-family: monospace;
        background: white;
        padding: 5px 8px;
        border-radius: 4px;
        font-size: 0.9rem;
    }

    .btn {
        display: inline-block;
        padding: 15px 30px;
        margin: 10px 5px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        text-decoration: none;
        font-weight: 600;
        font-size: 16px;
        transition: all 0.3s ease;
    }

    .btn-primary {
        background: #007bff;
        color: white;
    }

    .btn-primary:hover {
        background: #0056b3;
        transform: translateY(-2px);
    }

    .btn-success {
        background: #28a745;
        color: white;
    }

    .btn-success:hover {
        background: #1e7e34;
        transform: translateY(-2px);
    }

    .btn-secondary {
        background: #6c757d;
        color: white;
    }

    .btn-secondary:hover {
        background: #545b62;
        transform: translateY(-2px);
    }

    .form-section {
        background: #f8f9fa;
        padding: 25px;
        border-radius: 8px;
        margin: 25px 0;
        text-align: center;
    }

    .instructions {
        background: #fff3cd;
        border: 1px solid #ffeaa7;
        color: #856404;
        padding: 20px;
        border-radius: 8px;
        margin: 25px 0;
    }

    .instructions h3 {
        margin-top: 0;
        color: #856404;
    }

    .instructions ol {
        text-align: left;
        margin: 15px 0;
    }

    .instructions li {
        margin: 8px 0;
        line-height: 1.5;
    }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>📧 Test Email System</h1>
            <p>Send a test order notification email to verify the system is working</p>
        </div>

        <div class="content">
            <?php if (isset($success_message)): ?>
            <div class="status-card status-success">
                <h3><?= $success_message ?></h3>
                <p>Please check your inbox at <strong><?= ADMIN_EMAIL ?></strong> for the test email.</p>
                <p><small>📅 Sent at: <?= date('Y-m-d H:i:s T') ?></small></p>
            </div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
            <div class="status-card status-error">
                <h3><?= $error_message ?></h3>
                <p>Please check your SMTP configuration and try again.</p>
            </div>
            <?php endif; ?>

            <div class="status-card status-info">
                <h3>🎯 Email Configuration Status</h3>
                <p><strong>Target Email:</strong> <?= ADMIN_EMAIL ?></p>
                <p><strong>From Email:</strong> <?= FROM_EMAIL ?></p>
                <p><strong>SMTP Host:</strong> <?= SMTP_HOST ?></p>
                <p>✅ All settings configured for production use</p>
            </div>

            <div class="config-grid">
                <div class="config-item">
                    <h4>📧 Email Settings</h4>
                    <p><strong>Admin Email:</strong> <?= ADMIN_EMAIL ?></p>
                    <p><strong>From Name:</strong> <?= FROM_NAME ?></p>
                    <p><strong>From Email:</strong> <?= FROM_EMAIL ?></p>
                </div>
                <div class="config-item">
                    <h4>🔧 SMTP Configuration</h4>
                    <p><strong>Host:</strong> <?= SMTP_HOST ?></p>
                    <p><strong>Port:</strong> <?= SMTP_PORT ?></p>
                    <p><strong>Security:</strong> <?= SMTP_SECURE ?></p>
                </div>
                <div class="config-item">
                    <h4>🔐 Authentication</h4>
                    <p><strong>Username:</strong> <?= SMTP_USERNAME ?></p>
                    <p><strong>Auth:</strong> <?= SMTP_AUTH ? 'Enabled' : 'Disabled' ?></p>
                    <p><strong>Password:</strong> [Protected]</p>
                </div>
            </div>

            <div class="form-section">
                <h3>🚀 Send Test Email</h3>
                <p>Click the button below to send a test order notification email to <strong><?= ADMIN_EMAIL ?></strong></p>
                
                <form method="POST" style="margin: 20px 0;">
                    <input type="hidden" name="send_test" value="1">
                    <button type="submit" class="btn btn-success">
                        📤 Send Test Email Now
                    </button>
                </form>

                <div style="margin-top: 20px;">
                    <a href="test_checkout_fixed.php" class="btn btn-primary">🧪 System Test</a>
                    <a href="admin/login" class="btn btn-secondary">🔐 Admin Login</a>
                </div>
            </div>

            <div class="instructions">
                <h3>📋 How to Test the Email System</h3>
                <ol>
                    <li><strong>Send Test Email:</strong> Click the "Send Test Email Now" button above</li>
                    <li><strong>Check Inbox:</strong> Check the inbox at ryvah256@gmail.com for the test email</li>
                    <li><strong>Verify Template:</strong> Ensure the email has the enhanced design and admin login button</li>
                    <li><strong>Test Admin Link:</strong> Click the "Login to Admin Panel" button in the email</li>
                    <li><strong>Live Test:</strong> Complete a real order to trigger automatic email notifications</li>
                    <li><strong>Monitor Logs:</strong> Check logs/paypal.log for email sending confirmations</li>
                </ol>
                
                <h4>🔍 What to Look For in the Test Email:</h4>
                <ul style="margin-top: 10px;">
                    <li>✅ Professional header with gradient background</li>
                    <li>✅ Complete order information in grid layout</li>
                    <li>✅ Customer details and shipping address</li>
                    <li>✅ Itemized product table with styling</li>
                    <li>✅ "Login to Admin Panel" button that redirects to admin login</li>
                    <li>✅ Mobile-responsive design</li>
                    <li>✅ Professional footer with contact information</li>
                </ul>
            </div>
        </div>
    </div>
</body>

</html> 