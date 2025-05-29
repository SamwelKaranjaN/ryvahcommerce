<?php
session_start();
require_once '../config/database.php';
require_once '../vendor/autoload.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$user_id = $_SESSION['user_id'];
$conn = getDBConnection();

try {
    // Start transaction
    $conn->begin_transaction();

    // Create order
    $sql = "INSERT INTO orders (user_id, total_amount, payment_status, payment_method, billing_address) 
            VALUES (?, ?, 'pending', ?, ?)";
    $stmt = $conn->prepare($sql);

    // Format billing address
    $billing_address = sprintf(
        "%s, %s, %s %s, %s, Phone: %s",
        $data['billing_info']['postal_code'],
        $data['billing_info']['city'],
        $data['billing_info']['address'],
        $data['billing_info']['postal_code'],
        $data['billing_info']['city'],
        $data['billing_info']['phone']
    );

    $stmt->bind_param("idss", $user_id, $data['amount'], $data['payment_method'], $billing_address);
    $stmt->execute();
    $order_id = $conn->insert_id;

    // Get cart items
    $sql = "SELECT c.*, p.name, p.price, p.type 
            FROM cart c 
            JOIN products p ON c.product_id = p.id 
            WHERE c.user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $cart_items = $result->fetch_all(MYSQLI_ASSOC);

    // Insert order items and create user purchases for ebooks
    foreach ($cart_items as $item) {
        // Insert into order_items
        $sql = "INSERT INTO order_items (order_id, product_id, quantity, price, subtotal) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $subtotal = $item['price'] * $item['quantity'];
        $stmt->bind_param("iiids", $order_id, $item['product_id'], $item['quantity'], $item['price'], $subtotal);
        $stmt->execute();

        // If it's an ebook, create user_purchases entry
        if ($item['type'] === 'ebook') {
            $sql = "INSERT INTO user_purchases (user_id, product_id, order_id, download_count) 
                    VALUES (?, ?, ?, 0)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iii", $user_id, $item['product_id'], $order_id);
            $stmt->execute();
        }
    }

    // Add initial status to order_status_history
    $sql = "INSERT INTO order_status_history (order_id, status, notes) VALUES (?, 'pending', 'Order created')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();

    // Process payment based on method
    if ($data['payment_method'] === 'stripe') {
        // Process Stripe payment
        $stripe_config = require_once '../config/stripe.php';
        \Stripe\Stripe::setApiKey($stripe_config['secret_key']);

        try {
            $charge = \Stripe\Charge::create([
                'amount' => $data['amount'] * 100, // Convert to cents
                'currency' => 'usd',
                'source' => $data['token'],
                'description' => "Order #$order_id"
            ]);

            // Update order status to completed
            $sql = "UPDATE orders SET payment_status = 'completed' WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $order_id);
            $stmt->execute();

            // Add status to history
            $sql = "INSERT INTO order_status_history (order_id, status, notes) VALUES (?, 'completed', 'Payment successful via Stripe')";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $order_id);
            $stmt->execute();

            // Record payment
            $sql = "INSERT INTO order_payments (order_id, payment_method, transaction_id, amount, status) 
                    VALUES (?, 'stripe', ?, ?, 'completed')";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isd", $order_id, $charge->id, $data['amount']);
            $stmt->execute();

            // Clear cart
            $sql = "DELETE FROM cart WHERE user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();

            $conn->commit();
            echo json_encode(['success' => true, 'order_id' => $order_id, 'message' => 'Payment successful']);
        } catch (\Stripe\Exception\CardException $e) {
            $conn->rollback();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    } else if ($data['payment_method'] === 'paypal') {
        // Process PayPal payment
        $paypal_config = require_once '../config/paypal.php';

        // Verify PayPal payment
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.paypal.com/v2/checkout/orders/" . $data['payment_id']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer " . $paypal_config['access_token'],
            "Content-Type: application/json"
        ]);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code === 200) {
            $payment_data = json_decode($response, true);

            if ($payment_data['status'] === 'COMPLETED') {
                // Update order status to completed
                $sql = "UPDATE orders SET payment_status = 'completed' WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $order_id);
                $stmt->execute();

                // Add status to history
                $sql = "INSERT INTO order_status_history (order_id, status, notes) VALUES (?, 'completed', 'Payment successful via PayPal')";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $order_id);
                $stmt->execute();

                // Record payment
                $sql = "INSERT INTO order_payments (order_id, payment_method, transaction_id, amount, status) 
                        VALUES (?, 'paypal', ?, ?, 'completed')";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("isd", $order_id, $data['payment_id'], $data['amount']);
                $stmt->execute();

                // Clear cart
                $sql = "DELETE FROM cart WHERE user_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $user_id);
                $stmt->execute();

                $conn->commit();
                echo json_encode(['success' => true, 'order_id' => $order_id, 'message' => 'Payment successful']);
            } else {
                $conn->rollback();
                echo json_encode(['success' => false, 'message' => 'PayPal payment not completed']);
            }
        } else {
            $conn->rollback();
            echo json_encode(['success' => false, 'message' => 'Failed to verify PayPal payment']);
        }
    }
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}