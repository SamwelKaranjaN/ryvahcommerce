<?php

/**
 * Demo: Priority Cart Transfer System
 * 
 * This demonstrates how cart transfer happens as the FIRST PRIORITY
 * immediately after a guest user logs in.
 */

session_start();
require_once 'includes/bootstrap.php';
require_once 'includes/cart.php';
require_once 'includes/cart_priority.php';

echo "<h1>🚨 Priority Cart Transfer Demo</h1>";

// Step 1: Simulate guest user with cart items
echo "<h2>Step 1: Guest User Adds Items to Cart</h2>";
$_SESSION['cart'] = [
    1 => 2,  // Book ID 1, quantity 2
    3 => 1,  // Book ID 3, quantity 1
    5 => 3   // Book ID 5, quantity 3
];

echo "<div class='highlight'>Guest has " . count($_SESSION['cart']) . " different products in session cart</div>";
echo "<pre>" . print_r($_SESSION['cart'], true) . "</pre>";

// Step 2: User goes to checkout
echo "<h2>Step 2: Guest Tries to Checkout</h2>";
echo "<div class='info'>Guest clicks 'Proceed to Checkout' → redirected to login</div>";

// Simulate cart preservation for checkout
preserveCartForCheckout();
echo "<div class='success'>✅ Cart preserved for checkout process</div>";

// Step 3: User logs in - CART TRANSFER IS FIRST PRIORITY
echo "<h2>Step 3: 🚨 USER LOGS IN - CART TRANSFER FIRST PRIORITY</h2>";

// Simulate user authentication success
$test_user_id = 1;
$_SESSION['user_id'] = $test_user_id;

echo "<div class='priority'>";
echo "<strong>IMMEDIATELY after authentication, BEFORE anything else:</strong><br>";
echo "1. Session variables set ✅<br>";
echo "2. 🚨 CART TRANSFER STARTS NOW (FIRST PRIORITY) 🚨<br>";
echo "</div>";

// This happens FIRST - before remember me, before redirects, before anything
$transfer_result = immediateCartTransfer($test_user_id);

echo "<h3>Cart Transfer Results:</h3>";
echo "<div class='result'>";
echo "✅ Transfer Success: " . ($transfer_result['success'] ? 'YES' : 'NO') . "<br>";
echo "📦 Items Transferred: " . $transfer_result['transferred'] . "<br>";
echo "🔄 Items Merged: " . $transfer_result['merged'] . "<br>";
if (!empty($transfer_result['errors'])) {
    echo "❌ Errors: " . implode(', ', $transfer_result['errors']) . "<br>";
}
echo "</div>";

// Clean up
echo "<h2>Cleanup</h2>";
$stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
$stmt->bind_param("i", $test_user_id);
$stmt->execute();
echo "<div class='info'>Test data cleaned up</div>";

?>

<style>
body {
    font-family: Arial, sans-serif;
    margin: 20px;
    line-height: 1.6;
}

h1 {
    color: #d73027;
    border: 3px solid #d73027;
    padding: 15px;
    text-align: center;
    background: #fff5f5;
}

h2 {
    color: #2166ac;
    border-bottom: 2px solid #2166ac;
    padding-bottom: 5px;
}

.highlight {
    background: #ffd700;
    padding: 10px;
    border-radius: 5px;
    font-weight: bold;
    margin: 10px 0;
}

.priority {
    background: #ff4444;
    color: white;
    padding: 15px;
    border-radius: 5px;
    font-weight: bold;
    margin: 10px 0;
    border: 3px solid #cc0000;
}

.success {
    background: #4CAF50;
    color: white;
    padding: 10px;
    border-radius: 5px;
    margin: 10px 0;
}

.info {
    background: #2196F3;
    color: white;
    padding: 10px;
    border-radius: 5px;
    margin: 10px 0;
}

.result {
    background: #e8f5e8;
    border: 1px solid #4CAF50;
    padding: 15px;
    border-radius: 5px;
    margin: 10px 0;
}

pre {
    background: #f5f5f5;
    padding: 10px;
    border-radius: 5px;
    overflow-x: auto;
}
</style>