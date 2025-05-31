<?php
require_once '../includes/bootstrap.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get order ID from URL
$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

if (!$order_id) {
    header('Location: index.php');
    exit();
}

// Get order details
$stmt = $conn->prepare("
    SELECT o.*, op.transaction_id, op.payment_date
    FROM orders o
    LEFT JOIN order_payments op ON o.id = op.order_id
    WHERE o.id = ? AND o.user_id = ?
");
$stmt->bind_param("ii", $order_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if (!$order) {
    header('Location: index.php');
    exit();
}

// Get order items
$stmt = $conn->prepare("
    SELECT oi.*, p.name, p.type, p.thumbs, p.author
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order_items = $result->fetch_all(MYSQLI_ASSOC);

include '../includes/layouts/header.php';
?>

<div class="container py-5">
    <div class="row mb-4">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index" class="text-decoration-none">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Order Confirmation</li>
                </ol>
            </nav>
            <h2 class="mb-0">Order Confirmation</h2>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <i class="fas fa-check-circle text-success fa-4x mb-3"></i>
                        <h3 class="mb-2">Thank You for Your Order!</h3>
                        <p class="text-muted">Your order has been successfully placed.</p>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5 class="mb-3">Order Information</h5>
                            <p class="mb-1"><strong>Order Number:</strong>
                                <?php echo htmlspecialchars($order['invoice_number']); ?></p>
                            <p class="mb-1"><strong>Order Date:</strong>
                                <?php echo date('F j, Y', strtotime($order['order_date'])); ?></p>
                            <p class="mb-1"><strong>Payment Method:</strong>
                                <?php echo ucfirst($order['payment_method']); ?></p>
                            <p class="mb-1"><strong>Transaction ID:</strong>
                                <?php echo htmlspecialchars($order['transaction_id']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <h5 class="mb-3">Payment Summary</h5>
                            <p class="mb-1"><strong>Subtotal:</strong>
                                $<?php echo number_format($order['total_amount'] - $order['tax_amount'], 2); ?></p>
                            <p class="mb-1"><strong>Tax:</strong> $<?php echo number_format($order['tax_amount'], 2); ?>
                            </p>
                            <p class="mb-1"><strong>Total:</strong>
                                $<?php echo number_format($order['total_amount'], 2); ?></p>
                        </div>
                    </div>

                    <h5 class="mb-3">Order Items</h5>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($order_items as $item): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="../admin/<?php echo htmlspecialchars($item['thumbs']); ?>"
                                                class="rounded me-3"
                                                style="width: 50px; height: 50px; object-fit: cover;"
                                                alt="<?php echo htmlspecialchars($item['name']); ?>">
                                            <div>
                                                <h6 class="mb-0"><?php echo htmlspecialchars($item['name']); ?></h6>
                                                <small class="text-muted">By
                                                    <?php echo htmlspecialchars($item['author']); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>$<?php echo number_format($item['price'], 2); ?></td>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td>$<?php echo number_format($item['subtotal'], 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h5 class="card-title mb-4">What's Next?</h5>

                    <?php if ($order['payment_method'] === 'paypal'): ?>
                    <div class="mb-4">
                        <h6 class="mb-3">Digital Downloads</h6>
                        <p class="text-muted mb-3">Your digital items are ready for download. You can access them from
                            your account dashboard.</p>
                        <a href="account/downloads" class="btn btn-primary w-100">
                            <i class="fas fa-download me-2"></i>View Downloads
                        </a>
                    </div>
                    <?php endif; ?>

                    <div class="mb-4">
                        <h6 class="mb-3">Order Status</h6>
                        <p class="text-muted mb-3">Track your order status and get updates on your purchase.</p>
                        <a href="account/orders" class="btn btn-outline-primary w-100">
                            <i class="fas fa-box me-2"></i>View Order Status
                        </a>
                    </div>

                    <div>
                        <h6 class="mb-3">Need Help?</h6>
                        <p class="text-muted mb-3">If you have any questions about your order, please contact our
                            support team.</p>
                        <a href="contact" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-headset me-2"></i>Contact Support
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/layouts/footer.php'; ?>