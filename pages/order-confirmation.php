<?php
require_once '../includes/bootstrap.php';
require_once '../includes/order/OrderProcessor.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get order ID from URL
$orderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
if (!$orderId) {
    header('Location: account.php');
    exit;
}

// Get order details
$orderProcessor = new OrderProcessor($conn, $_SESSION['user_id'], []);
$order = $orderProcessor->getOrderDetails($orderId);

if (!$order) {
    header('Location: account.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <?php include '../includes/header.php'; ?>

    <main class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <!-- Success Message -->
                <div class="card mb-4">
                    <div class="card-body text-center">
                        <i class="fas fa-check-circle text-success fa-3x mb-3"></i>
                        <h1 class="h3 mb-3">Thank You for Your Order!</h1>
                        <p class="text-muted">Your order has been successfully placed and is being processed.</p>
                    </div>
                </div>

                <!-- Order Details -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h2>Order Details</h2>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Order Number:</strong><br><?php echo $order['order_id']; ?></p>
                                <p><strong>Order Status:</strong><br><?php echo ucfirst($order['status']); ?></p>
                                <p><strong>Order
                                        Date:</strong><br><?php echo date('F j, Y', strtotime($order['created_at'])); ?>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Shipping Address:</strong><br>
                                    <?php echo htmlspecialchars($order['shipping_address']); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Items -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h2>Order Items</h2>
                    </div>
                    <div class="card-body">
                        <?php foreach ($order['items'] as $item): ?>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="d-flex align-items-center">
                                    <img src="<?php echo htmlspecialchars($item['thumbs']); ?>" class="rounded me-3" alt=""
                                        style="width: 50px; height: 50px; object-fit: cover;">
                                    <div>
                                        <div class="fw-bold"><?php echo htmlspecialchars($item['name']); ?></div>
                                        <small class="text-muted">Qty: <?php echo $item['quantity']; ?></small>
                                    </div>
                                </div>
                                <span>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                            </div>
                        <?php endforeach; ?>

                        <hr>

                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal</span>
                            <span>$<?php echo number_format($order['subtotal'], 2); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Shipping</span>
                            <span class="text-success">Free</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <strong>Total</strong>
                            <strong>$<?php echo number_format($order['total_amount'], 2); ?></strong>
                        </div>
                    </div>
                </div>

                <!-- Next Steps -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h2>Next Steps</h2>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <i class="fas fa-envelope text-primary me-2"></i>
                                You will receive a confirmation email shortly
                            </li>
                            <?php if ($order['has_digital_products']): ?>
                                <li class="mb-2">
                                    <i class="fas fa-download text-primary me-2"></i>
                                    Download your digital products from your account
                                </li>
                            <?php endif; ?>
                            <li>
                                <i class="fas fa-truck text-primary me-2"></i>
                                Track your order status in your account
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex justify-content-between">
                    <a href="shop.php" class="btn btn-outline-primary">
                        <i class="fas fa-shopping-cart me-2"></i>Continue Shopping
                    </a>
                    <a href="account.php?tab=orders" class="btn btn-primary">
                        <i class="fas fa-user me-2"></i>View Order in Account
                    </a>
                </div>
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>

</html>