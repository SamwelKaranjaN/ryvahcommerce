<?php

/**
 * Test Email Template for Order Notifications
 * This file allows you to preview the order notification email template
 */

session_start();
require_once 'includes/bootstrap.php';
require_once 'includes/email_functions.php';

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Order Email Template - Ryvah Commerce</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f8f9fa;
        }

        .test-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .preview-section {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .preview-header {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .email-preview {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            overflow: hidden;
            background: #f8f9fa;
            max-height: 600px;
            overflow-y: auto;
        }

        .controls {
            background: #343a40;
            color: white;
            padding: 15px;
            text-align: center;
            margin-bottom: 20px;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            margin: 5px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-weight: 600;
        }

        .btn-primary {
            background: #007bff;
            color: white;
        }

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .test-info {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .test-data {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            padding: 20px;
            border-radius: 6px;
            font-family: monospace;
            font-size: 14px;
            max-height: 300px;
            overflow-y: auto;
        }
    </style>
</head>

<body>
    <div class="test-container">
        <div class="preview-header">
            <h1>📧 Order Email Template Test</h1>
            <p>Preview and test the enhanced order notification email template</p>
        </div>

        <div class="test-info">
            <h3>📋 Test Information</h3>
            <p><strong>Purpose:</strong> This page displays a preview of the order notification email that will be sent
                to <strong>ryvah256@gmail.com</strong> when customers complete their orders.</p>
            <p><strong>Features:</strong> Enhanced design, responsive layout, admin login button, and comprehensive order details.</p>
        </div>

        <div class="controls">
            <a href="test_send_email.php" class="btn btn-success">📤 Send Test Email</a>
            <a href="admin/login" class="btn btn-primary">🔐 Admin Login</a>
            <a href="checkout/simple_checkout.php" class="btn btn-secondary">🛒 Test Checkout</a>
        </div>

        <div class="preview-section">
            <h2>🎨 Email Template Preview</h2>
            <div class="email-preview">
                <?php
                // Create sample order data for testing
                $test_order_data = [
                    'order' => [
                        'id' => 123,
                        'invoice_number' => 'INV-' . date('Y') . '-000123',
                        'total_amount' => 89.99,
                        'tax_amount' => 7.20,
                        'created_at' => date('Y-m-d H:i:s'),
                        'currency' => 'USD',
                        'paypal_order_id' => '8GB67279RC051624C',
                        'full_name' => 'John Smith',
                        'email' => 'john.smith@example.com',
                        'phone' => '+1 (555) 123-4567'
                    ],
                    'items' => [
                        [
                            'id' => 1,
                            'name' => 'Advanced Digital Marketing Strategies',
                            'author' => 'Dr. Sarah Johnson',
                            'type' => 'ebook',
                            'price' => 29.99,
                            'quantity' => 2,
                            'subtotal' => 59.98
                        ],
                        [
                            'id' => 2,
                            'name' => 'Business Analytics Guide',
                            'author' => 'Michael Chen',
                            'type' => 'ebook',
                            'price' => 19.99,
                            'quantity' => 1,
                            'subtotal' => 19.99
                        ],
                        [
                            'id' => 3,
                            'name' => 'Premium Course Bundle',
                            'author' => null,
                            'type' => 'course',
                            'price' => 10.02,
                            'quantity' => 1,
                            'subtotal' => 10.02
                        ]
                    ],
                    'shipping_address' => [
                        'street' => '123 Business Avenue, Suite 456',
                        'city' => 'New York',
                        'state' => 'NY',
                        'postal_code' => '10001',
                        'country' => 'United States'
                    ]
                ];

                // Generate and display the email HTML
                echo generateOrderNotificationHtml($test_order_data);
                ?>
            </div>
        </div>

        <div class="preview-section">
            <h2>📊 Test Data Structure</h2>
            <div class="test-data">
                <?php echo htmlspecialchars(print_r($test_order_data, true)); ?>
            </div>
        </div>

        <div class="preview-section">
            <h2>⚙️ Email Configuration Status</h2>
            <div class="info-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;">
                <div style="background: #d4edda; padding: 15px; border-radius: 6px; border-left: 4px solid #28a745;">
                    <h4>📧 Admin Email</h4>
                    <p><strong><?= ADMIN_EMAIL ?></strong></p>
                    <p><small>✅ Correctly set to ryvah256@gmail.com</small></p>
                </div>
                <div style="background: #cce5ff; padding: 15px; border-radius: 6px; border-left: 4px solid #007bff;">
                    <h4>📤 SMTP Configuration</h4>
                    <p><strong>Host:</strong> <?= SMTP_HOST ?></p>
                    <p><strong>Port:</strong> <?= SMTP_PORT ?></p>
                    <p><small>✅ Using Hostinger SMTP</small></p>
                </div>
                <div style="background: #fff3cd; padding: 15px; border-radius: 6px; border-left: 4px solid #ffc107;">
                    <h4>📧 From Address</h4>
                    <p><strong><?= FROM_EMAIL ?></strong></p>
                    <p><small>✅ Official business email</small></p>
                </div>
            </div>
        </div>

        <div class="preview-section">
            <h2>🧪 How to Test</h2>
            <ol style="line-height: 2;">
                <li><strong>Preview:</strong> The email template is displayed above with sample data</li>
                <li><strong>Send Test:</strong> Click "Send Test Email" to send a real email to ryvah256@gmail.com</li>
                <li><strong>Live Test:</strong> Complete a real order through the checkout process</li>
                <li><strong>Admin Access:</strong> Use the "Login to Admin Panel" button in emails to manage orders</li>
                <li><strong>Verify Receipt:</strong> Check ryvah256@gmail.com inbox for email notifications</li>
            </ol>
        </div>

        <div class="preview-section">
            <h2>✅ Features Included</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;">
                <div style="background: #f8f9fa; padding: 15px; border-radius: 6px; border: 1px solid #e9ecef;">
                    <h4>🎨 Design Features</h4>
                    <ul>
                        <li>Modern responsive design</li>
                        <li>Professional color scheme</li>
                        <li>Mobile-friendly layout</li>
                        <li>Branded header with logo</li>
                    </ul>
                </div>
                <div style="background: #f8f9fa; padding: 15px; border-radius: 6px; border: 1px solid #e9ecef;">
                    <h4>📊 Order Information</h4>
                    <ul>
                        <li>Complete order details</li>
                        <li>Customer information</li>
                        <li>Shipping address</li>
                        <li>Itemized product list</li>
                    </ul>
                </div>
                <div style="background: #f8f9fa; padding: 15px; border-radius: 6px; border: 1px solid #e9ecef;">
                    <h4>🔧 Admin Features</h4>
                    <ul>
                        <li>Direct admin login button</li>
                        <li>Action required notice</li>
                        <li>Order management link</li>
                        <li>Professional footer</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</body>

</html>