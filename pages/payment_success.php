<?php
require_once '../includes/bootstrap.php';
require_once '../includes/cart.php';
require_once '../config/stripe.php';
require_once '../config/paypal.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$error = null;
$order_id = null;

try {
    // Get payment details from session
    $payment_method = $_SESSION['payment_method'] ?? null;
    $payment_id = $_SESSION['payment_id'] ?? null;

    if (!$payment_method || !$payment_id) {
        throw new Exception('Payment information not found');
    }

    // Verify payment based on payment method
    switch ($payment_method) {
        case 'stripe':
            $payment_intent = confirmStripePayment($payment_id);
            if ($payment_intent->status !== 'succeeded') {
                throw new Exception('Payment not successful');
            }
            $order_id = $payment_intent->metadata->order_id;
            break;

        case 'paypal':
            // Verify PayPal payment
            $payment = makePayPalRequest(PAYPAL_PAYMENTS_URL . '/' . $payment_id, 'GET');
            if ($payment->status !== 'COMPLETED') {
                throw new Exception('Payment not successful');
            }
            $order_id = $payment->purchase_units[0]->reference_id;
            break;

        default:
            throw new Exception('Invalid payment method');
    }

    // Create order in database
    if ($order_id) {
        // Get cart items
        $cart_data = getCartItems();

        // Create order record
        $sql = "INSERT INTO orders (user_id, order_id, total_amount, payment_method, payment_id, status) 
                VALUES (?, ?, ?, ?, ?, 'completed')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isdss", $user_id, $order_id, $cart_data['total'], $payment_method, $payment_id);
        $stmt->execute();

        // Create order items
        $order_items_sql = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($order_items_sql);

        foreach ($cart_data['items'] as $item) {
            $stmt->bind_param("siid", $order_id, $item['id'], $item['quantity'], $item['price']);
            $stmt->execute();

            // Update product inventory
            $update_inventory_sql = "UPDATE products SET stock = stock - ? WHERE id = ?";
            $inventory_stmt = $conn->prepare($update_inventory_sql);
            $inventory_stmt->bind_param("ii", $item['quantity'], $item['id']);
            $inventory_stmt->execute();
        }

        // Send confirmation email
        $user_email = $_SESSION['user_email'];
        $subject = "Order Confirmation - Order #" . $order_id;
        $message = "Thank you for your order!\n\n";
        $message .= "Order ID: " . $order_id . "\n";
        $message .= "Total Amount: $" . number_format($cart_data['total'], 2) . "\n\n";
        $message .= "Your order has been confirmed and will be processed shortly.";

        mail($user_email, $subject, $message);

        // Clear the cart after successful order creation
        clearCart();

        // Clear payment session data
        unset($_SESSION['payment_method']);
        unset($_SESSION['payment_id']);
    }
} catch (Exception $e) {
    $error = $e->getMessage();
    error_log("Payment processing error: " . $e->getMessage());
}

include '../includes/layouts/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-5">
                    <?php if ($error): ?>
                    <div class="mb-4">
                        <i class="fas fa-exclamation-circle text-danger" style="font-size: 4rem;"></i>
                    </div>
                    <h2 class="mb-4">Payment Error</h2>
                    <p class="text-muted mb-4">
                        <?php echo htmlspecialchars($error); ?>
                    </p>
                    <div class="d-grid gap-2">
                        <a href="payment.php" class="btn btn-primary">Try Again</a>
                        <a href="cart.php" class="btn btn-outline-primary">Return to Cart</a>
                    </div>
                    <?php else: ?>
                    <div class="mb-4">
                        <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                    </div>
                    <h2 class="mb-4">Payment Successful!</h2>
                    <p class="text-muted mb-4">
                        Thank you for your purchase. Your order #<?php echo htmlspecialchars($order_id); ?> has been
                        confirmed and will be processed shortly.
                        You will receive an email confirmation with your order details.
                    </p>
                    <div class="d-grid gap-2">
                        <a href="order_history.php" class="btn btn-primary">View Order History</a>
                        <a href="index.php" class="btn btn-outline-primary">Continue Shopping</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/layouts/footer.php'; ?>