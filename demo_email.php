<?php
session_start();
require_once 'includes/bootstrap.php';
require_once 'includes/email_functions.php';
require_once 'includes/email_config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

echo "<h1>🎯 Demo Email Sender - Ryvah Commerce</h1>";
echo "<style>body{font-family:Arial;padding:20px;background:#f5f5f5;} .status{padding:15px;margin:10px 0;border-radius:8px;} .success{background:#d4edda;color:#155724;border:1px solid #c3e6cb;} .error{background:#f8d7da;color:#721c24;border:1px solid #f5c6cb;}</style>";

try {
    // Create demo order data
    $demo_data = [
        'order' => [
            'id' => 99999,
            'invoice_number' => 'DEMO-' . date('Y') . '-' . rand(100, 999),
            'total_amount' => 149.99,
            'tax_amount' => 12.00,
            'created_at' => date('Y-m-d H:i:s'),
            'currency' => 'USD',
            'paypal_order_id' => 'DEMO_' . strtoupper(uniqid()),
            'full_name' => 'John Demo Customer',
            'email' => 'demo@example.com',
            'phone' => '+1 (555) 123-4567'
        ],
        'items' => [
            [
                'id' => 1,
                'name' => 'Digital Marketing Mastery Course',
                'author' => 'Marketing Expert',
                'type' => 'ebook',
                'price' => 79.99,
                'quantity' => 1,
                'subtotal' => 79.99
            ],
            [
                'id' => 2,
                'name' => 'Business Growth Strategies',
                'author' => 'Business Coach',
                'type' => 'course',
                'price' => 69.99,
                'quantity' => 1,
                'subtotal' => 69.99
            ]
        ],
        'shipping_address' => [
            'street' => '123 Demo Street, Suite 456',
            'city' => 'Demo City',
            'state' => 'CA',
            'postal_code' => '90210',
            'country' => 'United States'
        ]
    ];

    // Send demo email
    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = SMTP_HOST;
    $mail->SMTPAuth = SMTP_AUTH;
    $mail->Username = SMTP_USERNAME;
    $mail->Password = SMTP_PASSWORD;
    $mail->SMTPSecure = SMTP_SECURE;
    $mail->Port = SMTP_PORT;

    $mail->setFrom(FROM_EMAIL, FROM_NAME);
    $mail->addAddress(ADMIN_EMAIL);

    $mail->isHTML(true);
    $mail->Subject = '🎯 DEMO - New Order Notification - Order #' . $demo_data['order']['invoice_number'];
    $mail->Body = generateOrderNotificationHtml($demo_data);
    $mail->AltBody = generateOrderNotificationText($demo_data);

    $mail->send();

    echo "<div class='status success'>";
    echo "<h2>✅ SUCCESS! Demo Email Sent</h2>";
    echo "<p><strong>Recipient:</strong> " . ADMIN_EMAIL . "</p>";
    echo "<p><strong>Order Number:</strong> " . $demo_data['order']['invoice_number'] . "</p>";
    echo "<p><strong>Total Amount:</strong> $" . number_format($demo_data['order']['total_amount'] + $demo_data['order']['tax_amount'], 2) . "</p>";
    echo "<p><strong>Sent At:</strong> " . date('Y-m-d H:i:s T') . "</p>";
    echo "<p>🎉 <strong>Check your inbox at ryvah256@gmail.com for the enhanced order notification!</strong></p>";
    echo "</div>";

    echo "<h3>📧 Email Features Included:</h3>";
    echo "<ul>";
    echo "<li>✅ Professional gradient header with order number</li>";
    echo "<li>✅ Complete order information in grid layout</li>";
    echo "<li>✅ Customer details and shipping address</li>";
    echo "<li>✅ Itemized product table with pricing</li>";
    echo "<li>✅ Order totals with tax breakdown</li>";
    echo "<li>✅ <strong>Admin Login Button</strong> linking to https://ryvahcommerce.com/admin/login</li>";
    echo "<li>✅ Mobile-responsive design</li>";
    echo "<li>✅ Professional footer with contact information</li>";
    echo "</ul>";

    echo "<p><a href='admin/login' style='display:inline-block;background:#007bff;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;margin:10px 0;'>🔐 Test Admin Login</a></p>";
} catch (Exception $e) {
    echo "<div class='status error'>";
    echo "<h2>❌ ERROR: Failed to send demo email</h2>";
    echo "<p><strong>Error Message:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>SMTP Host:</strong> " . SMTP_HOST . "</p>";
    echo "<p><strong>Target Email:</strong> " . ADMIN_EMAIL . "</p>";
    echo "</div>";
}

echo "<hr>";
echo "<p><strong>Next Steps:</strong></p>";
echo "<ol>";
echo "<li>Check your email inbox at <strong>ryvah256@gmail.com</strong></li>";
echo "<li>Look for the demo order notification with enhanced design</li>";
echo "<li>Click the 'Login to Admin Panel' button in the email</li>";
echo "<li>Test a real order through the checkout process</li>";
echo "</ol>";

echo "<p><a href='test_checkout_fixed.php'>🧪 Back to System Tests</a> | <a href='checkout/simple_checkout.php'>🛒 Test Real Checkout</a></p>";

// Call the function with a test order ID
// This will send email to ryvah256@gmail.com automatically
if (function_exists('sendOrderNotificationEmail')) {
    echo "Sending demo email...<br>";
    $result = sendOrderNotificationEmail(1); // Use any existing order ID
    echo $result ? "✅ Email sent!" : "❌ Email failed";
} else {
    echo "❌ Email function not found";
}
