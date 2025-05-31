<?php
require_once '../includes/bootstrap.php';
require_once '../includes/security.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}

// Get order details
$order_id = $_GET['order_id'] ?? '';
if (empty($order_id)) {
    header('Location: /');
    exit;
}

try {
    // Get order information
    $stmt = $conn->prepare("
        SELECT o.*, op.transaction_id 
        FROM orders o 
        LEFT JOIN order_payments op ON o.id = op.order_id 
        WHERE o.id = ? AND o.user_id = ?
    ");
    $stmt->bind_param("ii", $order_id, $_SESSION['user_id']);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();

    if (!$order) {
        throw new Exception('Order not found');
    }

    // Get order items
    $stmt = $conn->prepare("
        SELECT oi.*, p.name, p.sku 
        FROM order_items oi 
        JOIN products p ON oi.product_id = p.id 
        WHERE oi.order_id = ?
    ");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    // Log error
    logSecurityEvent('order_view_error', [
        'user_id' => $_SESSION['user_id'],
        'order_id' => $order_id,
        'error' => $e->getMessage()
    ]);

    header('Location: /');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - Ryvah Commerce</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>

<body>
    <?php include '../includes/header.php'; ?>

    <main class="container">
        <div class="success-page">
            <div class="success-header">
                <h1>Thank You for Your Order!</h1>
                <p>Your order has been successfully placed.</p>
            </div>

            <div class="order-details">
                <div class="order-info">
                    <h2>Order Information</h2>
                    <p><strong>Order Number:</strong> <?php echo htmlspecialchars($order['invoice_number']); ?></p>
                    <p><strong>Date:</strong> <?php echo date('F j, Y', strtotime($order['order_date'])); ?></p>
                    <p><strong>Payment Method:</strong> PayPal</p>
                    <p><strong>Transaction ID:</strong> <?php echo htmlspecialchars($order['transaction_id']); ?></p>
                </div>

                <div class="shipping-info">
                    <h2>Shipping Information</h2>
                    <?php
                    $shipping = json_decode($order['shipping_address'], true);
                    if ($shipping):
                    ?>
                        <p><?php echo htmlspecialchars($shipping['street']); ?></p>
                        <p><?php echo htmlspecialchars($shipping['city'] . ', ' . $shipping['state'] . ' ' . $shipping['postal_code']); ?></p>
                        <p><?php echo htmlspecialchars($shipping['country']); ?></p>
                    <?php endif; ?>
                </div>

                <div class="order-summary">
                    <h2>Order Summary</h2>
                    <table class="order-items">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>SKU</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                                    <td><?php echo htmlspecialchars($item['sku']); ?></td>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td>$<?php echo number_format($item['price'], 2); ?></td>
                                    <td>$<?php echo number_format($item['subtotal'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" class="text-right"><strong>Subtotal:</strong></td>
                                <td>$<?php echo number_format($order['total_amount'] - $order['tax_amount'], 2); ?></td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-right"><strong>Tax:</strong></td>
                                <td>$<?php echo number_format($order['tax_amount'], 2); ?></td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-right"><strong>Total:</strong></td>
                                <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="next-steps">
                <h2>What's Next?</h2>
                <p>You will receive an email confirmation with your order details.</p>
                <p>We'll notify you when your order ships.</p>
                <div class="action-buttons">
                    <a href="/account/orders.php" class="btn btn-primary">View All Orders</a>
                    <a href="/" class="btn btn-secondary">Continue Shopping</a>
                </div>
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>

</html>