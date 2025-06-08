<?php
// Demo Order Email Sender
echo "<!DOCTYPE html><html><head><title>Demo Email Sender</title></head><body>";
echo "<h1>🎯 Demo Order Email Sender - Ryvah Commerce</h1>";
echo "<style>body{font-family:Arial;padding:20px;background:#f5f5f5;line-height:1.6;} .status{padding:15px;margin:10px 0;border-radius:8px;} .success{background:#d4edda;color:#155724;border:1px solid #c3e6cb;} .error{background:#f8d7da;color:#721c24;border:1px solid #f5c6cb;} .info{background:#d1ecf1;color:#0c5460;border:1px solid #bee5eb;}</style>";

try {
    // Start session and include required files
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Include configuration files in correct order
    require_once __DIR__ . '/vendor/autoload.php';
    require_once __DIR__ . '/includes/email_config.php';
    require_once __DIR__ . '/config/database.php';
    require_once __DIR__ . '/includes/functions.php';
    require_once __DIR__ . '/includes/email_functions.php';
    
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    echo "<div class='status info'>";
    echo "<h3>📧 Email Configuration Status</h3>";
    echo "<p><strong>Admin Email:</strong> " . (defined('ADMIN_EMAIL') ? ADMIN_EMAIL : 'Not defined') . "</p>";
    echo "<p><strong>SMTP Host:</strong> " . (defined('SMTP_HOST') ? SMTP_HOST : 'Not defined') . "</p>";
    echo "<p><strong>From Email:</strong> " . (defined('FROM_EMAIL') ? FROM_EMAIL : 'Not defined') . "</p>";
    echo "</div>";

    // Verify all required constants are defined
    if (!defined('ADMIN_EMAIL') || !defined('SMTP_HOST') || !defined('SMTP_USERNAME') || !defined('SMTP_PASSWORD')) {
        throw new Exception('Email configuration constants not properly defined');
    }

    // Create comprehensive demo order data
    $demo_order_data = [
        'order' => [
            'id' => 88888,
            'invoice_number' => 'DEMO-' . date('Y') . '-' . sprintf('%03d', rand(100, 999)),
            'total_amount' => 124.99,
            'tax_amount' => 10.00,
            'created_at' => date('Y-m-d H:i:s'),
            'currency' => 'USD',
            'paypal_order_id' => 'DEMO_' . strtoupper(bin2hex(random_bytes(8))),
            'full_name' => 'Jane Demo Customer',
            'email' => 'jane.demo@example.com',
            'phone' => '+1 (555) 987-6543'
        ],
        'items' => [
            [
                'id' => 1,
                'name' => 'Ultimate Digital Marketing Guide 2024',
                'author' => 'Dr. Marketing Expert',
                'type' => 'ebook',
                'price' => 59.99,
                'quantity' => 1,
                'subtotal' => 59.99
            ],
            [
                'id' => 2,
                'name' => 'Advanced Business Analytics Course',
                'author' => 'Prof. Data Science',
                'type' => 'course',
                'price' => 64.99,
                'quantity' => 1,
                'subtotal' => 64.99
            ]
        ],
        'shipping_address' => [
            'street' => '789 Enterprise Boulevard, Floor 12',
            'city' => 'San Francisco',
            'state' => 'CA',
            'postal_code' => '94105',
            'country' => 'United States'
        ]
    ];

    echo "<div class='status info'>";
    echo "<h3>📦 Demo Order Details</h3>";
    echo "<p><strong>Order Number:</strong> " . $demo_order_data['order']['invoice_number'] . "</p>";
    echo "<p><strong>Customer:</strong> " . $demo_order_data['order']['full_name'] . "</p>";
    echo "<p><strong>Total:</strong> $" . number_format($demo_order_data['order']['total_amount'] + $demo_order_data['order']['tax_amount'], 2) . "</p>";
    echo "<p><strong>Items:</strong> " . count($demo_order_data['items']) . " products</p>";
    echo "</div>";

    // Initialize PHPMailer with proper configuration
    $mail = new PHPMailer(true);
    
    // SMTP Configuration
    $mail->isSMTP();
    $mail->Host = SMTP_HOST;
    $mail->SMTPAuth = SMTP_AUTH;
    $mail->Username = SMTP_USERNAME;
    $mail->Password = SMTP_PASSWORD;
    $mail->SMTPSecure = SMTP_SECURE;
    $mail->Port = SMTP_PORT;
    
    // Set sender and recipient
    $mail->setFrom(FROM_EMAIL, FROM_NAME);
    $mail->addAddress(ADMIN_EMAIL);
    
    // Email content
    $mail->isHTML(true);
    $mail->Subject = '🎯 DEMO TEST - New Order Notification - Order #' . $demo_order_data['order']['invoice_number'];
    
    // Generate email content using the enhanced template
    $mail->Body = generateOrderNotificationHtml($demo_order_data);
    $mail->AltBody = generateOrderNotificationText($demo_order_data);
    
    // Send the email
    $mail->send();
    
    // Success message
    echo "<div class='status success'>";
    echo "<h2>✅ SUCCESS! Demo Email Sent</h2>";
    echo "<p><strong>📧 Sent to:</strong> " . ADMIN_EMAIL . "</p>";
    echo "<p><strong>📋 Order:</strong> " . $demo_order_data['order']['invoice_number'] . "</p>";
    echo "<p><strong>💰 Amount:</strong> $" . number_format($demo_order_data['order']['total_amount'] + $demo_order_data['order']['tax_amount'], 2) . "</p>";
    echo "<p><strong>📅 Sent at:</strong> " . date('Y-m-d H:i:s T') . "</p>";
    echo "<p>🎉 <strong>Please check your inbox at ryvah256@gmail.com for the enhanced order notification!</strong></p>";
    echo "</div>";
    
    echo "<div class='status info'>";
    echo "<h3>📧 Email Features Included</h3>";
    echo "<ul>";
    echo "<li>✅ Professional gradient header with celebration emojis</li>";
    echo "<li>✅ Complete order information in modern grid layout</li>";
    echo "<li>✅ Customer details with contact information</li>";
    echo "<li>✅ Formatted shipping address display</li>";
    echo "<li>✅ Detailed product table with pricing breakdown</li>";
    echo "<li>✅ Order totals with tax calculations</li>";
    echo "<li>✅ <strong>Admin Login Button</strong> - Direct link to https://ryvahcommerce.com/admin/login</li>";
    echo "<li>✅ Mobile-responsive design for all devices</li>";
    echo "<li>✅ Professional footer with Ryvah Commerce branding</li>";
    echo "<li>✅ Action required notice for order processing</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<div class='status info'>";
    echo "<h3>🔄 Next Steps</h3>";
    echo "<ol>";
    echo "<li><strong>Check Email Inbox:</strong> Go to ryvah256@gmail.com and look for the demo order notification</li>";
    echo "<li><strong>Verify Template:</strong> Confirm the email has the enhanced design with professional styling</li>";
    echo "<li><strong>Test Admin Button:</strong> Click the 'Login to Admin Panel' button in the email</li>";
    echo "<li><strong>Test Real Order:</strong> Complete an actual order through the checkout process</li>";
    echo "<li><strong>Monitor System:</strong> Check logs/paypal.log for email delivery confirmations</li>";
    echo "</ol>";
    echo "</div>";
    
    echo "<p><a href='test_checkout_fixed.php' style='display:inline-block;background:#007bff;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;margin:5px;'>🧪 Back to System Tests</a>";
    echo "<a href='admin/login' style='display:inline-block;background:#28a745;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;margin:5px;'>🔐 Test Admin Login</a>";
    echo "<a href='checkout/simple_checkout.php' style='display:inline-block;background:#6f42c1;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;margin:5px;'>🛒 Test Checkout</a></p>";
    
} catch (Exception $e) {
    echo "<div class='status error'>";
    echo "<h2>❌ ERROR: Failed to send demo email</h2>";
    echo "<p><strong>Error Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>File:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
    echo "</div>";
    
    echo "<div class='status info'>";
    echo "<h3>🔧 Troubleshooting</h3>";
    echo "<ul>";
    echo "<li>Verify email configuration in includes/email_config.php</li>";
    echo "<li>Check SMTP credentials and server settings</li>";
    echo "<li>Ensure vendor/autoload.php exists (run composer install if needed)</li>";
    echo "<li>Verify database connection is working</li>";
    echo "</ul>";
    echo "</div>";
}

echo "</body></html>";
?>