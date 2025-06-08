<?php

/**
 * Demo Email Sender - Ryvah Commerce
 * Sends a test order notification email to demonstrate the email system
 */

session_start();
require_once 'includes/bootstrap.php';
require_once 'includes/email_functions.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Check if form was submitted
$emailSent = false;
$errorMessage = '';
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_demo'])) {
    try {
        // Create comprehensive demo order data
        $demo_order_data = [
            'order' => [
                'id' => 12345,
                'invoice_number' => 'DEMO-' . date('Y') . '-' . str_pad(rand(100, 999), 3, '0', STR_PAD_LEFT),
                'total_amount' => 157.45,
                'tax_amount' => 12.60,
                'created_at' => date('Y-m-d H:i:s'),
                'currency' => 'USD',
                'paypal_order_id' => 'DEMO_' . strtoupper(uniqid()),
                'full_name' => 'Demo Customer',
                'email' => 'demo.customer@example.com',
                'phone' => '+1 (555) 123-DEMO'
            ],
            'items' => [
                [
                    'id' => 1,
                    'name' => 'Advanced Digital Marketing Mastery',
                    'author' => 'Sarah Johnson PhD',
                    'type' => 'ebook',
                    'price' => 49.99,
                    'quantity' => 2,
                    'subtotal' => 99.98
                ],
                [
                    'id' => 2,
                    'name' => 'Business Analytics & Data Science',
                    'author' => 'Michael Chen',
                    'type' => 'ebook',
                    'price' => 34.99,
                    'quantity' => 1,
                    'subtotal' => 34.99
                ],
                [
                    'id' => 3,
                    'name' => 'Premium Marketing Tools Bundle',
                    'author' => null,
                    'type' => 'course',
                    'price' => 22.48,
                    'quantity' => 1,
                    'subtotal' => 22.48
                ]
            ],
            'shipping_address' => [
                'street' => '456 Business Plaza, Suite 789',
                'city' => 'Los Angeles',
                'state' => 'CA',
                'postal_code' => '90210',
                'country' => 'United States'
            ]
        ];

        // Initialize PHPMailer
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
        $mail->addAddress(ADMIN_EMAIL); // ryvah256@gmail.com

        // Content
        $mail->isHTML(true);
        $mail->Subject = '🎯 DEMO - New Order Notification - Order #' . $demo_order_data['order']['invoice_number'];
        $mail->Body = generateOrderNotificationHtml($demo_order_data);
        $mail->AltBody = generateOrderNotificationText($demo_order_data);

        // Send email
        $mail->send();

        $emailSent = true;
        $successMessage = '✅ Demo order notification email sent successfully to ' . ADMIN_EMAIL;

        // Log the demo email
        logPayPalError('Demo order notification email sent', [
            'demo_order_id' => $demo_order_data['order']['id'],
            'invoice_number' => $demo_order_data['order']['invoice_number'],
            'recipient' => ADMIN_EMAIL,
            'timestamp' => date('Y-m-d H:i:s T'),
            'type' => 'demo_email'
        ]);
    } catch (Exception $e) {
        $errorMessage = '❌ Failed to send demo email: ' . $e->getMessage();

        // Log the error
        logPayPalError('Demo email failed: ' . $e->getMessage(), [
            'error_type' => 'demo_email_failure',
            'error_details' => $e->getTraceAsString(),
            'timestamp' => date('Y-m-d H:i:s T')
        ]);
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demo Email Sender - Ryvah Commerce</title>
    <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        padding: 20px;
    }

    .container {
        max-width: 900px;
        margin: 0 auto;
        background: white;
        border-radius: 16px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .header {
        background: linear-gradient(135deg, #007bff, #0056b3);
        color: white;
        padding: 40px;
        text-align: center;
        position: relative;
        overflow: hidden;
    }

    .header::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: repeating-linear-gradient(45deg,
                transparent,
                transparent 10px,
                rgba(255, 255, 255, 0.1) 10px,
                rgba(255, 255, 255, 0.1) 20px);
        animation: float 20s linear infinite;
    }

    @keyframes float {
        0% {
            transform: translate(-50%, -50%) rotate(0deg);
        }

        100% {
            transform: translate(-50%, -50%) rotate(360deg);
        }
    }

    .header .content {
        position: relative;
        z-index: 1;
    }

    .header h1 {
        font-size: 3rem;
        margin-bottom: 15px;
        font-weight: 700;
    }

    .header p {
        font-size: 1.2rem;
        opacity: 0.9;
    }

    .main-content {
        padding: 50px;
    }

    .status-card {
        padding: 25px;
        border-radius: 12px;
        margin-bottom: 30px;
        border-left: 5px solid;
        font-size: 1.1rem;
        font-weight: 600;
    }

    .status-success {
        background: linear-gradient(135deg, #d4edda, #c3e6cb);
        border-left-color: #28a745;
        color: #155724;
    }

    .status-error {
        background: linear-gradient(135deg, #f8d7da, #f5c6cb);
        border-left-color: #dc3545;
        color: #721c24;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 25px;
        margin: 30px 0;
    }

    .info-card {
        background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        padding: 25px;
        border-radius: 12px;
        border-left: 4px solid #007bff;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .info-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 123, 255, 0.15);
    }

    .info-card h3 {
        color: #007bff;
        margin-bottom: 15px;
        font-size: 1.3rem;
    }

    .info-card p {
        margin: 8px 0;
        line-height: 1.6;
    }

    .info-card .value {
        font-family: 'Courier New', monospace;
        background: white;
        padding: 8px 12px;
        border-radius: 6px;
        font-weight: 600;
        color: #333;
    }

    .demo-section {
        background: linear-gradient(135deg, #e3f2fd, #bbdefb);
        border: 2px solid #2196f3;
        border-radius: 12px;
        padding: 30px;
        text-align: center;
        margin: 30px 0;
    }

    .demo-section h2 {
        color: #1976d2;
        margin-bottom: 20px;
        font-size: 2rem;
    }

    .demo-data {
        background: white;
        border-radius: 8px;
        padding: 20px;
        margin: 20px 0;
        text-align: left;
        max-height: 300px;
        overflow-y: auto;
        border: 1px solid #e0e0e0;
    }

    .demo-data h4 {
        color: #333;
        margin-bottom: 15px;
        border-bottom: 2px solid #007bff;
        padding-bottom: 8px;
    }

    .demo-data pre {
        font-family: 'Courier New', monospace;
        font-size: 0.9rem;
        line-height: 1.4;
        color: #555;
        margin: 0;
    }

    .btn {
        display: inline-block;
        padding: 15px 30px;
        margin: 10px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        text-decoration: none;
        font-weight: 600;
        font-size: 1.1rem;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }

    .btn-primary {
        background: linear-gradient(135deg, #007bff, #0056b3);
        color: white;
    }

    .btn-success {
        background: linear-gradient(135deg, #28a745, #20c997);
        color: white;
    }

    .btn-secondary {
        background: linear-gradient(135deg, #6c757d, #5a6268);
        color: white;
    }

    .footer {
        background: #343a40;
        color: #adb5bd;
        padding: 30px;
        text-align: center;
    }

    .footer a {
        color: #007bff;
        text-decoration: none;
    }

    .footer a:hover {
        text-decoration: underline;
    }

    .pulse {
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.05);
        }

        100% {
            transform: scale(1);
        }
    }

    @media (max-width: 768px) {
        .header h1 {
            font-size: 2rem;
        }

        .main-content {
            padding: 30px 20px;
        }

        .info-grid {
            grid-template-columns: 1fr;
        }
    }
    </style>
</head>

<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="content">
                <h1>📧 Demo Email Sender</h1>
                <p>Test the enhanced order notification email system</p>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Status Messages -->
            <?php if ($emailSent && $successMessage): ?>
            <div class="status-card status-success pulse">
                <div style="font-size: 1.4rem; margin-bottom: 10px;">🎉 Email Sent Successfully!</div>
                <div><?= $successMessage ?></div>
                <div style="margin-top: 15px; font-size: 1rem; opacity: 0.8;">
                    📅 Sent at: <?= date('l, F j, Y \a\t g:i A T') ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($errorMessage): ?>
            <div class="status-card status-error">
                <div style="font-size: 1.4rem; margin-bottom: 10px;">❌ Email Failed</div>
                <div><?= $errorMessage ?></div>
            </div>
            <?php endif; ?>

            <!-- Email Configuration Info -->
            <div class="info-grid">
                <div class="info-card">
                    <h3>📮 Email Configuration</h3>
                    <p><strong>Target Email:</strong></p>
                    <div class="value"><?= ADMIN_EMAIL ?></div>
                    <p><strong>From Email:</strong></p>
                    <div class="value"><?= FROM_EMAIL ?></div>
                    <p><strong>SMTP Host:</strong></p>
                    <div class="value"><?= SMTP_HOST ?></div>
                </div>

                <div class="info-card">
                    <h3>🎨 Email Features</h3>
                    <p>✅ Enhanced HTML template</p>
                    <p>✅ Responsive mobile design</p>
                    <p>✅ Admin login button</p>
                    <p>✅ Professional branding</p>
                    <p>✅ Order management link</p>
                    <p>✅ Comprehensive order details</p>
                </div>

                <div class="info-card">
                    <h3>🔗 Admin Integration</h3>
                    <p><strong>Admin Panel URL:</strong></p>
                    <div class="value">https://ryvahcommerce.com/admin/login</div>
                    <p><strong>Purpose:</strong></p>
                    <p>Direct access from email to order management</p>
                </div>
            </div>

            <!-- Demo Send Section -->
            <div class="demo-section">
                <h2>🚀 Send Demo Order Email</h2>
                <p style="font-size: 1.1rem; margin-bottom: 25px;">
                    This will send a realistic order notification email to <strong><?= ADMIN_EMAIL ?></strong>
                    <br>using the enhanced template with sample order data.
                </p>

                <?php if (!$emailSent): ?>
                <form method="POST" style="margin: 25px 0;">
                    <input type="hidden" name="send_demo" value="1">
                    <button type="submit" class="btn btn-success pulse">
                        📤 Send Demo Email Now
                    </button>
                </form>
                <?php else: ?>
                <div style="margin: 25px 0;">
                    <p style="color: #28a745; font-weight: 600; font-size: 1.1rem;">
                        ✅ Demo email has been sent! Check your inbox at <?= ADMIN_EMAIL ?>
                    </p>
                    <form method="POST" style="margin-top: 20px;">
                        <input type="hidden" name="send_demo" value="1">
                        <button type="submit" class="btn btn-primary">
                            🔄 Send Another Demo Email
                        </button>
                    </form>
                </div>
                <?php endif; ?>

                <div style="margin-top: 30px;">
                    <a href="test_checkout_fixed.php" class="btn btn-secondary">🧪 System Test</a>
                    <a href="admin/login" class="btn btn-primary">🔐 Admin Login</a>
                    <a href="checkout/simple_checkout.php" class="btn btn-success">🛒 Test Checkout</a>
                </div>
            </div>

            <!-- Sample Data Preview -->
            <div class="demo-data">
                <h4>📋 Sample Order Data Being Sent</h4>
                <pre><?php
                        echo "Demo Order Details:\n";
                        echo "==================\n";
                        echo "Order Number: DEMO-" . date('Y') . "-XXX\n";
                        echo "Customer: Demo Customer\n";
                        echo "Email: demo.customer@example.com\n";
                        echo "Total Amount: $170.05 (including tax)\n";
                        echo "Currency: USD\n";
                        echo "Products: 3 items (eBooks + Course Bundle)\n";
                        echo "Shipping: Los Angeles, CA 90210, United States\n";
                        echo "PayPal Transaction: DEMO_" . strtoupper(substr(uniqid(), 0, 8)) . "...\n";
                        echo "\nEmail Features:\n";
                        echo "- Professional gradient header\n";
                        echo "- Order information grid\n";
                        echo "- Customer & shipping details\n";
                        echo "- Itemized product table\n";
                        echo "- Admin login button\n";
                        echo "- Mobile-responsive design\n";
                        echo "- Branded footer with contact info";
                        ?></pre>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>Ryvah Commerce</strong> | Demo Email System</p>
            <p>📧 <a href="mailto:<?= FROM_EMAIL ?>"><?= FROM_EMAIL ?></a> | 🌐 <a
                    href="https://ryvahcommerce.com">ryvahcommerce.com</a></p>
        </div>
    </div>
</body>

</html>