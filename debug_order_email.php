<?php

/**
 * Detailed Order Email Debug Tool
 * Step-by-step debugging of order email functionality
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

require_once 'includes/bootstrap.php';

// Include email functions
if (file_exists('includes/email_functions.php')) {
    require_once 'includes/email_functions.php';
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
    <title>Order Email Debug</title>
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

        .info {
            border-left-color: #17a2b8;
            background: #d1ecf1;
            color: #0c5460;
        }

        pre {
            background: #f1f1f1;
            padding: 10px;
            border-radius: 4px;
            overflow-x: auto;
            font-size: 12px;
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

        .step {
            margin: 10px 0;
            padding: 10px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
        }

        .step-title {
            font-weight: bold;
            color: #007bff;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>🔍 Detailed Order Email Debug</h1>
        
        <?php
        // Test with Order ID 79 (the one that failed)
        $test_order_id = 79;
        
        echo "<div class='debug-section info'>";
        echo "<h2>📦 Testing Order ID: {$test_order_id}</h2>";
        echo "</div>";
        
        // Step 1: Check if order exists
        echo "<div class='step'>";
        echo "<h3>Step 1: 🗃️ Check Order Exists</h3>";
        try {
            $stmt = $conn->prepare("
                SELECT o.id, o.invoice_number, o.total_amount, o.tax_amount, o.payment_status, 
                       o.shipping_address, o.created_at, o.currency, o.paypal_order_id,
                       u.full_name, u.email, u.phone
                FROM orders o
                JOIN users u ON o.user_id = u.id
                WHERE o.id = ? AND o.payment_status = 'completed'
            ");
            
            $stmt->bind_param("i", $test_order_id);
            $stmt->execute();
            $order = $stmt->get_result()->fetch_assoc();
            
            if ($order) {
                echo "<div class='success'>✅ Order found!</div>";
                echo "<pre>" . htmlspecialchars(json_encode($order, JSON_PRETTY_PRINT)) . "</pre>";
            } else {
                echo "<div class='error'>❌ Order not found or not completed</div>";
                
                // Check if order exists at all
                $stmt2 = $conn->prepare("SELECT id, invoice_number, payment_status FROM orders WHERE id = ?");
                $stmt2->bind_param("i", $test_order_id);
                $stmt2->execute();
                $basic_order = $stmt2->get_result()->fetch_assoc();
                
                if ($basic_order) {
                    echo "<div class='warning'>⚠️ Order exists but status is: " . htmlspecialchars($basic_order['payment_status']) . "</div>";
                } else {
                    echo "<div class='error'>❌ Order ID {$test_order_id} does not exist at all</div>";
                }
            }
        } catch (Exception $e) {
            echo "<div class='error'>❌ Database Error: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
        echo "</div>";
        
        if (!isset($order) || !$order) {
            echo "<div class='debug-section error'>";
            echo "<h3>🛑 Cannot continue - Order not found</h3>";
            echo "<p>Please check:</p>";
            echo "<ul>";
            echo "<li>Order ID {$test_order_id} exists</li>";
            echo "<li>Order payment_status is 'completed'</li>";
            echo "<li>Order has associated user</li>";
            echo "</ul>";
            echo "</div>";
            echo "</div></body></html>";
            exit;
        }
        
        // Step 2: Check order items
        echo "<div class='step'>";
        echo "<h3>Step 2: 📋 Check Order Items</h3>";
        try {
            $stmt = $conn->prepare("
                SELECT oi.product_id, oi.quantity, oi.price, oi.subtotal, oi.tax_amount,
                       p.name, p.type, p.author, p.description
                FROM order_items oi
                JOIN products p ON oi.product_id = p.id
                WHERE oi.order_id = ?
                ORDER BY p.name
            ");
            
            $stmt->bind_param("i", $test_order_id);
            $stmt->execute();
            $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            
            if (!empty($items)) {
                echo "<div class='success'>✅ Found " . count($items) . " order items</div>";
                echo "<pre>" . htmlspecialchars(json_encode($items, JSON_PRETTY_PRINT)) . "</pre>";
                
                // Check if it's ebook-only
                $has_physical = false;
                foreach ($items as $item) {
                    if ($item['type'] !== 'ebook') {
                        $has_physical = true;
                        break;
                    }
                }
                
                if ($has_physical) {
                    echo "<div class='success'>✅ Order contains physical items - email should be sent</div>";
                } else {
                    echo "<div class='warning'>⚠️ Order contains only ebooks - email will be skipped by design</div>";
                }
            } else {
                echo "<div class='error'>❌ No order items found</div>";
            }
        } catch (Exception $e) {
            echo "<div class='error'>❌ Error fetching order items: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
        echo "</div>";
        
        // Step 3: Test email functions loading
        echo "<div class='step'>";
        echo "<h3>Step 3: 📧 Check Email Functions</h3>";
        
        if (file_exists('includes/email_functions.php')) {
            echo "<div class='success'>✅ email_functions.php file exists</div>";
            
            try {
                require_once 'includes/email_functions.php';
                echo "<div class='success'>✅ email_functions.php loaded successfully</div>";
                
                $functions_to_check = [
                    'sendOrderNotificationEmail',
                    'getOrderDetailsForEmail', 
                    'isOnlyEbookOrder',
                    'generateOrderNotificationHtml',
                    'generateOrderNotificationText'
                ];
                
                foreach ($functions_to_check as $func) {
                    if (function_exists($func)) {
                        echo "<div class='success'>✅ {$func}() function available</div>";
                    } else {
                        echo "<div class='error'>❌ {$func}() function missing</div>";
                    }
                }
            } catch (Exception $e) {
                echo "<div class='error'>❌ Error loading email_functions.php: " . htmlspecialchars($e->getMessage()) . "</div>";
            }
        } else {
            echo "<div class='error'>❌ email_functions.php file not found</div>";
        }
        echo "</div>";
        
        // Step 4: Test individual function components
        echo "<div class='step'>";
        echo "<h3>Step 4: 🧪 Test Individual Components</h3>";
        
        if (function_exists('getOrderDetailsForEmail')) {
            echo "<h4>Testing getOrderDetailsForEmail():</h4>";
            try {
                $order_data = getOrderDetailsForEmail($test_order_id);
                if ($order_data) {
                    echo "<div class='success'>✅ Order data retrieved successfully</div>";
                    echo "<pre>" . htmlspecialchars(json_encode($order_data, JSON_PRETTY_PRINT)) . "</pre>";
                } else {
                    echo "<div class='error'>❌ getOrderDetailsForEmail() returned null</div>";
                }
            } catch (Exception $e) {
                echo "<div class='error'>❌ getOrderDetailsForEmail() error: " . htmlspecialchars($e->getMessage()) . "</div>";
                echo "<div class='error'>Stack trace: " . htmlspecialchars($e->getTraceAsString()) . "</div>";
            }
        }
        
        if (function_exists('isOnlyEbookOrder') && isset($items)) {
            echo "<h4>Testing isOnlyEbookOrder():</h4>";
            try {
                $is_ebook_only = isOnlyEbookOrder($items);
                echo "<div class='info'>📊 Is ebook-only order: " . ($is_ebook_only ? 'Yes' : 'No') . "</div>";
            } catch (Exception $e) {
                echo "<div class='error'>❌ isOnlyEbookOrder() error: " . htmlspecialchars($e->getMessage()) . "</div>";
            }
        }
        echo "</div>";
        
        // Step 5: Test email configuration
        echo "<div class='step'>";
        echo "<h3>Step 5: ⚙️ Test Email Configuration</h3>";
        
        if (file_exists('includes/email_config.php')) {
            echo "<div class='success'>✅ email_config.php exists</div>";
            require_once 'includes/email_config.php';
            
            $configs = ['SMTP_HOST', 'SMTP_PORT', 'SMTP_USERNAME', 'FROM_EMAIL', 'ADMIN_EMAIL'];
            foreach ($configs as $config) {
                if (defined($config)) {
                    echo "<div class='success'>✅ {$config} is defined</div>";
                } else {
                    echo "<div class='error'>❌ {$config} is not defined</div>";
                }
            }
        } else {
            echo "<div class='error'>❌ email_config.php not found</div>";
        }
        echo "</div>";
        
        // Step 6: Test PHPMailer
        echo "<div class='step'>";
        echo "<h3>Step 6: 📬 Test PHPMailer Availability</h3>";
        
        if (file_exists('vendor/autoload.php')) {
            echo "<div class='success'>✅ Composer autoload exists</div>";
            require_once 'vendor/autoload.php';
            
            if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
                echo "<div class='success'>✅ PHPMailer class available</div>";
            } else {
                echo "<div class='error'>❌ PHPMailer class not found</div>";
            }
        } else {
            echo "<div class='error'>❌ Composer autoload not found</div>";
        }
        echo "</div>";
        
        // Step 7: Actually try to send email with full error catching
        echo "<div class='step'>";
        echo "<h3>Step 7: 📨 Attempt Email Send with Full Error Catching</h3>";
        
        if (function_exists('sendOrderNotificationEmail')) {
            echo "<h4>🔄 Calling sendOrderNotificationEmail({$test_order_id})...</h4>";
            
            // Capture ALL output and errors
            ob_start();
            $error_occurred = false;
            $result = false;
            
            try {
                // Set custom error handler to catch all errors
                set_error_handler(function($severity, $message, $file, $line) {
                    throw new ErrorException($message, 0, $severity, $file, $line);
                });
                
                $result = sendOrderNotificationEmail($test_order_id);
                
                // Restore normal error handler
                restore_error_handler();
                
            } catch (Exception $e) {
                $error_occurred = true;
                echo "<div class='error'>";
                echo "<h4>❌ Exception Caught:</h4>";
                echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
                echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
                echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
                echo "<p><strong>Stack Trace:</strong></p>";
                echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
                echo "</div>";
                
                restore_error_handler();
            } catch (Error $e) {
                $error_occurred = true;
                echo "<div class='error'>";
                echo "<h4>❌ Fatal Error Caught:</h4>";
                echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
                echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
                echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
                echo "</div>";
                
                restore_error_handler();
            }
            
            $output = ob_get_clean();
            
            if ($output) {
                echo "<div class='info'>";
                echo "<h4>📝 Function Output:</h4>";
                echo "<pre>" . htmlspecialchars($output) . "</pre>";
                echo "</div>";
            }
            
            if (!$error_occurred) {
                if ($result) {
                    echo "<div class='success'>";
                    echo "<h4>✅ Function completed successfully!</h4>";
                    echo "<p>Return value: TRUE</p>";
                    echo "<p>This means the email should have been sent (unless it was skipped for being ebook-only).</p>";
                    echo "</div>";
                } else {
                    echo "<div class='warning'>";
                    echo "<h4>⚠️ Function completed but returned FALSE</h4>";
                    echo "<p>This usually means:</p>";
                    echo "<ul>";
                    echo "<li>Order contains only ebooks (email skipped by design)</li>";
                    echo "<li>Email sending failed</li>";
                    echo "<li>Order data was invalid</li>";
                    echo "</ul>";
                    echo "</div>";
                }
            }
        } else {
            echo "<div class='error'>❌ sendOrderNotificationEmail() function not available</div>";
        }
        echo "</div>";
        
        echo "<div class='debug-section info'>";
        echo "<h3>🎯 Summary & Next Steps</h3>";
        echo "<p>Based on the debugging above, you can now see exactly where the issue is occurring.</p>";
        echo "<p>If the function is working but emails aren't arriving, run the <a href='debug_email.php'>simple email test</a> to check SMTP connectivity.</p>";
        echo "</div>";
        ?>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="debug_email.php" style="padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 4px;">📧 Test SMTP Settings</a>
            <a href="test_email_success.php" style="padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 4px; margin-left: 10px;">📊 Back to Main Test</a>
        </div>
    </div>
</body>

</html>