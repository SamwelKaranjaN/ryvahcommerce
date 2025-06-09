<?php

/**
 * Test Email Success - Dummy Order Email Test
 * Simulates successful payment and sends actual email notification
 */

// Suppress PayPal SDK warnings
error_reporting(E_ALL & ~E_DEPRECATED);
session_start();

require_once 'includes/bootstrap.php';
require_once 'includes/paypal_config.php';

// Include email functions
if (file_exists('includes/email_functions.php')) {
    require_once 'includes/email_functions.php';
} else {
    die('❌ Email functions file not found');
}

// Check if email configuration exists
if (!file_exists('includes/email_config.php')) {
    die('❌ Email configuration file not found. Please create includes/email_config.php first.');
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dummy Email Success Test</title>
    <style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        margin: 40px;
        background: #f8f9fa;
    }

    .container {
        max-width: 900px;
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

    .btn {
        background: #007bff;
        color: white;
        padding: 12px 24px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
        margin: 10px 5px;
    }

    .btn:hover {
        background: #0056b3;
    }

    .dummy-order {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 20px;
        margin: 20px 0;
    }

    pre {
        background: #f1f3f4;
        padding: 15px;
        border-radius: 6px;
        overflow-x: auto;
        font-size: 14px;
    }
    </style>
</head>

<body>
    <div class="container">
        <h1>📧 Dummy Email Success Test</h1>

        <?php
        // Check if we should run the test
        if (isset($_POST['send_test_email'])) {
            echo '<div class="test-item info"><h3>🚀 Running Email Test...</h3></div>';

            // Create dummy order data
            $dummy_order_data = [
                'order' => [
                    'id' => 99999,
                    'invoice_number' => 'TEST-' . date('YmdHis'),
                    'total_amount' => 49.99,
                    'tax_amount' => 4.50,
                    'created_at' => date('Y-m-d H:i:s'),
                    'currency' => 'USD',
                    'paypal_order_id' => 'TEST123456789',
                    'full_name' => 'John Doe',
                    'email' => 'customer@example.com',
                    'phone' => '+1234567890'
                ],
                'items' => [
                    [
                        'product_id' => 1,
                        'name' => 'Test Physical Product',
                        'type' => 'physical', // This ensures email will be sent
                        'author' => 'Test Author',
                        'quantity' => 2,
                        'price' => 19.99,
                        'subtotal' => 39.98,
                        'tax_amount' => 3.60
                    ],
                    [
                        'product_id' => 2,
                        'name' => 'Another Test Item',
                        'type' => 'physical',
                        'author' => 'Another Author',
                        'quantity' => 1,
                        'price' => 9.99,
                        'subtotal' => 9.99,
                        'tax_amount' => 0.90
                    ]
                ],
                'shipping_address' => [
                    'street' => '123 Test Street',
                    'city' => 'Test City',
                    'state' => 'TS',
                    'postal_code' => '12345',
                    'country' => 'United States'
                ]
            ];

            echo '<div class="dummy-order">';
            echo '<h4>📦 Dummy Order Data:</h4>';
            echo '<pre>' . htmlspecialchars(json_encode($dummy_order_data, JSON_PRETTY_PRINT)) . '</pre>';
            echo '</div>';

            // Test individual functions first
            echo '<h3>🔧 Function Tests:</h3>';

            // Test isOnlyEbookOrder
            echo '<div class="test-item">';
            echo '<strong>Testing isOnlyEbookOrder():</strong><br>';
            if (function_exists('isOnlyEbookOrder')) {
                $is_ebook_only = isOnlyEbookOrder($dummy_order_data['items']);
                echo $is_ebook_only ?
                    '<span style="color: orange;">⚠️ This order contains only ebooks (email would be skipped)</span>' :
                    '<span style="color: green;">✅ This order contains physical items (email will be sent)</span>';
            } else {
                echo '<span style="color: red;">❌ Function not found</span>';
            }
            echo '</div>';

            // Test HTML generation
            echo '<div class="test-item">';
            echo '<strong>Testing generateOrderNotificationHtml():</strong><br>';
            if (function_exists('generateOrderNotificationHtml')) {
                try {
                    $html_content = generateOrderNotificationHtml($dummy_order_data);
                    $html_length = strlen($html_content);
                    echo "<span style='color: green;'>✅ HTML generated successfully ({$html_length} characters)</span>";

                    // Save HTML for preview
                    file_put_contents('test_email_preview.html', $html_content);
                    echo '<br><a href="test_email_preview.html" target="_blank" class="btn">📖 Preview HTML Email</a>';
                } catch (Exception $e) {
                    echo '<span style="color: red;">❌ Error: ' . htmlspecialchars($e->getMessage()) . '</span>';
                }
            } else {
                echo '<span style="color: red;">❌ Function not found</span>';
            }
            echo '</div>';

            // Test text generation
            echo '<div class="test-item">';
            echo '<strong>Testing generateOrderNotificationText():</strong><br>';
            if (function_exists('generateOrderNotificationText')) {
                try {
                    $text_content = generateOrderNotificationText($dummy_order_data);
                    $text_length = strlen($text_content);
                    echo "<span style='color: green;'>✅ Text generated successfully ({$text_length} characters)</span>";
                    echo '<details><summary>Click to view text email content</summary>';
                    echo '<pre>' . htmlspecialchars($text_content) . '</pre>';
                    echo '</details>';
                } catch (Exception $e) {
                    echo '<span style="color: red;">❌ Error: ' . htmlspecialchars($e->getMessage()) . '</span>';
                }
            } else {
                echo '<span style="color: red;">❌ Function not found</span>';
            }
            echo '</div>';

            // Now test email sending (but we need to simulate database)
            echo '<h3>📧 Email Sending Test:</h3>';
            echo '<div class="test-item warning">';
            echo '<strong>Note:</strong> To actually send email, we would need:<br>';
            echo '• A real order ID in the database<br>';
            echo '• Configured SMTP settings<br>';
            echo '• Valid email configuration<br><br>';

            echo '<strong>Current Email Config Status:</strong><br>';

            // Check email config constants
            $email_configs = [
                'SMTP_HOST' => defined('SMTP_HOST') ? SMTP_HOST : 'Not defined',
                'SMTP_USERNAME' => defined('SMTP_USERNAME') ? SMTP_USERNAME : 'Not defined',
                'ADMIN_EMAIL' => defined('ADMIN_EMAIL') ? ADMIN_EMAIL : 'Not defined',
                'FROM_EMAIL' => defined('FROM_EMAIL') ? FROM_EMAIL : 'Not defined'
            ];

            foreach ($email_configs as $config => $value) {
                $status = ($value !== 'Not defined') ? '✅' : '❌';
                echo "{$status} {$config}: " . htmlspecialchars($value) . "<br>";
            }
            echo '</div>';

            // Try to send email if we have a real order
            echo '<div class="test-item info">';
            echo '<strong>🎯 Real Email Test:</strong><br>';
            echo 'To test with a real order, we need an actual order ID from your database.<br>';

            // Check if we have any completed orders
            try {
                $stmt = $conn->prepare("SELECT id, invoice_number FROM orders WHERE payment_status = 'completed' ORDER BY created_at DESC LIMIT 5");
                $stmt->execute();
                $orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

                if (!empty($orders)) {
                    echo '<br><strong>Recent completed orders found:</strong><br>';
                    foreach ($orders as $order) {
                        echo "• Order #{$order['invoice_number']} (ID: {$order['id']})<br>";
                    }

                    $test_order_id = $orders[0]['id'];
                    echo "<br><strong>Testing with Order ID: {$test_order_id}</strong><br>";

                    if (function_exists('sendOrderNotificationEmail')) {
                        try {
                            $email_result = sendOrderNotificationEmail($test_order_id);
                            if ($email_result) {
                                echo '<span style="color: green;">✅ Email sent successfully!</span>';
                            } else {
                                echo '<span style="color: orange;">⚠️ Email function returned false (may be ebook-only order or config issue)</span>';
                            }
                        } catch (Exception $e) {
                            echo '<span style="color: red;">❌ Email sending failed: ' . htmlspecialchars($e->getMessage()) . '</span>';
                        }
                    } else {
                        echo '<span style="color: red;">❌ sendOrderNotificationEmail function not available</span>';
                    }
                } else {
                    echo '<span style="color: orange;">⚠️ No completed orders found in database</span>';
                }
            } catch (Exception $e) {
                echo '<span style="color: red;">❌ Database error: ' . htmlspecialchars($e->getMessage()) . '</span>';
            }
            echo '</div>';
        } else {
            // Show form to trigger test
        ?>
        <div class="test-item info">
            <h3>📋 Email Function Test</h3>
            <p>This test will:</p>
            <ul>
                <li>✅ Verify all email functions are loaded</li>
                <li>📧 Generate dummy order email content</li>
                <li>🔍 Check email configuration</li>
                <li>📨 Attempt to send actual email (if configuration allows)</li>
            </ul>
        </div>

        <form method="post" style="text-align: center;">
            <button type="submit" name="send_test_email" class="btn">
                🚀 Run Email Test
            </button>
        </form>

        <div class="test-item warning">
            <strong>⚠️ Prerequisites:</strong>
            <ul>
                <li>Make sure your email configuration is set up in <code>includes/email_config.php</code></li>
                <li>Ensure you have completed orders in your database for real testing</li>
                <li>Check that your SMTP settings are correct</li>
            </ul>
        </div>
        <?php
        }
        ?>

        <div style="text-align: center; margin-top: 30px;">
            <a href="test_payment_success.php" class="btn">📊 View Main Test</a>
            <a href="checkout/simple_checkout.php" class="btn">🛒 Go to Checkout</a>
        </div>

        <p style="text-align: center; margin-top: 30px; color: #6c757d;">
            <small>Test created at <?= date('Y-m-d H:i:s') ?></small>
        </p>
    </div>
</body>

</html>