<?php
require_once '../includes/bootstrap.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../pages/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$order_id = isset($_GET['order']) ? (int)$_GET['order'] : 0;

if (!$order_id) {
    header('Location: ../pages/index.php');
    exit;
}

// Get order details
$stmt = $conn->prepare("
    SELECT o.*, u.full_name, u.email 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    WHERE o.id = ? AND o.user_id = ?
");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order || $order['payment_status'] !== 'completed') {
    header('Location: ../pages/index.php');
    exit;
}

// Get order items
$stmt = $conn->prepare("
    SELECT oi.*, p.name, p.author, p.thumbs, p.type, p.filepath
    FROM order_items oi 
    JOIN products p ON oi.product_id = p.id 
    WHERE oi.order_id = ?
");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get download links for digital products
$stmt = $conn->prepare("
    SELECT ed.*, p.name, p.type, p.filepath 
    FROM ebook_downloads ed 
    JOIN products p ON ed.product_id = p.id 
    WHERE ed.order_id = ? AND ed.user_id = ?
");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$downloads = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$shipping_address = json_decode($order['shipping_address'], true);
$billing_address = json_decode($order['billing_address'], true);

include '../includes/layouts/header.php';
?>

<div class="container py-5">
    <!-- Success Header -->
    <div class="row mb-5">
        <div class="col-12 text-center">
            <div class="success-icon mb-4">
                <i class="fas fa-check-circle fa-5x text-success"></i>
            </div>
            <h1 class="text-success mb-3">Order Confirmed!</h1>
            <p class="lead text-muted">Thank you for your purchase. Your order has been successfully processed.</p>
            <div class="alert alert-info d-inline-block">
                <strong>Order #<?php echo htmlspecialchars($order['invoice_number']); ?></strong>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Order Details -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Order Details</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Order Date:</strong><br>
                            <span class="text-muted"><?php echo date('M j, Y g:i A', strtotime($order['order_date'])); ?></span>
                        </div>
                        <div class="col-md-6">
                            <strong>Payment Method:</strong><br>
                            <span class="text-muted">
                                <i class="fab fa-paypal text-primary"></i> PayPal
                            </span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Customer:</strong><br>
                            <span class="text-muted"><?php echo htmlspecialchars($order['full_name']); ?></span><br>
                            <span class="text-muted"><?php echo htmlspecialchars($order['email']); ?></span>
                        </div>
                        <div class="col-md-6">
                            <strong>Order Status:</strong><br>
                            <span class="badge bg-success">Completed</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Items -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Order Items</h5>
                </div>
                <div class="card-body">
                    <?php foreach ($order_items as $item): ?>
                        <div class="order-item d-flex align-items-center mb-3 pb-3 <?php echo !end($order_items) ? 'border-bottom' : ''; ?>">
                            <img src="../admin/<?php echo htmlspecialchars($item['thumbs']); ?>" 
                                 class="img-thumbnail me-3" style="width: 80px; height: 80px; object-fit: cover;">
                            <div class="flex-grow-1">
                                <h6 class="mb-1"><?php echo htmlspecialchars($item['name']); ?></h6>
                                <p class="text-muted mb-1 small">
                                    By <?php echo htmlspecialchars($item['author'] ?? ''); ?>
                                </p>
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-secondary me-2">
                                        <?php echo ucfirst($item['type']); ?>
                                    </span>
                                    <span class="text-muted">Qty: <?php echo $item['quantity']; ?></span>
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="text-muted small">$<?php echo number_format($item['price'], 2); ?> each</div>
                                <strong>$<?php echo number_format($item['subtotal'], 2); ?></strong>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Digital Downloads -->
            <?php if (!empty($downloads)): ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Digital Downloads</h5>
                    <small class="text-muted">Available for 30 days</small>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Your digital products are ready for download. Each item can be downloaded up to 5 times.
                    </div>
                    
                    <?php foreach ($downloads as $download): ?>
                        <div class="download-item p-3 border rounded mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1"><?php echo htmlspecialchars($download['name']); ?></h6>
                                    <div class="text-muted small">
                                        <i class="fas fa-download me-1"></i>
                                        Downloads: <?php echo $download['download_count']; ?>/<?php echo $download['max_downloads']; ?>
                                        <span class="ms-3">
                                            <i class="fas fa-clock me-1"></i>
                                            Expires: <?php echo date('M j, Y', strtotime($download['expires_at'])); ?>
                                        </span>
                                    </div>
                                </div>
                                <div>
                                    <?php if ($download['download_count'] < $download['max_downloads'] && strtotime($download['expires_at']) > time()): ?>
                                        <a href="../includes/download/download.php?token=<?php echo $download['download_token']; ?>" 
                                           class="btn btn-primary btn-sm">
                                            <i class="fas fa-download me-2"></i>Download
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">Download Expired</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Addresses -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Addresses</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Shipping Address</h6>
                            <address class="mb-0">
                                <?php if ($shipping_address): ?>
                                    <strong><?php echo htmlspecialchars($shipping_address['label'] ?? ''); ?></strong><br>
                                    <?php echo htmlspecialchars($shipping_address['street'] ?? ''); ?><br>
                                    <?php echo htmlspecialchars($shipping_address['city'] ?? ''); ?>, 
                                    <?php echo htmlspecialchars($shipping_address['state'] ?? ''); ?> 
                                    <?php echo htmlspecialchars($shipping_address['postal_code'] ?? ''); ?><br>
                                    <?php echo htmlspecialchars($shipping_address['country'] ?? ''); ?>
                                <?php endif; ?>
                            </address>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Billing Address</h6>
                            <address class="mb-0">
                                <?php if ($billing_address): ?>
                                    <strong><?php echo htmlspecialchars($billing_address['label'] ?? ''); ?></strong><br>
                                    <?php echo htmlspecialchars($billing_address['street'] ?? ''); ?><br>
                                    <?php echo htmlspecialchars($billing_address['city'] ?? ''); ?>, 
                                    <?php echo htmlspecialchars($billing_address['state'] ?? ''); ?> 
                                    <?php echo htmlspecialchars($billing_address['postal_code'] ?? ''); ?><br>
                                    <?php echo htmlspecialchars($billing_address['country'] ?? ''); ?>
                                <?php endif; ?>
                            </address>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Summary Sidebar -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Order Summary</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal</span>
                        <span>$<?php echo number_format($order['total_amount'] - $order['tax_amount'], 2); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Tax</span>
                        <span>$<?php echo number_format($order['tax_amount'], 2); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span>Shipping</span>
                        <span class="text-success">Free</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-0">
                        <strong>Total Paid</strong>
                        <strong class="text-success">$<?php echo number_format($order['total_amount'], 2); ?></strong>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-grid gap-2">
                <a href="../pages/index.php" class="btn btn-primary">
                    Continue Shopping
                </a>
                <a href="../pages/account/orders.php" class="btn btn-outline-secondary">
                    View All Orders
                </a>
                <button onclick="window.print()" class="btn btn-outline-secondary">
                    <i class="fas fa-print me-2"></i>Print Receipt
                </button>
            </div>

            <!-- Support -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-body text-center">
                    <h6 class="card-title">Need Help?</h6>
                    <p class="card-text small text-muted">
                        If you have any questions about your order, please contact our support team.
                    </p>
                    <a href="mailto:support@ryvahcommerce.com" class="btn btn-sm btn-outline-primary">
                        Contact Support
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.success-icon {
    animation: checkmark 0.6s ease-in-out;
}

@keyframes checkmark {
    0% {
        transform: scale(0);
        opacity: 0;
    }
    50% {
        transform: scale(1.2);
    }
    100% {
        transform: scale(1);
        opacity: 1;
    }
}

.order-item {
    transition: all 0.2s ease;
}

.order-item:hover {
    background-color: rgba(0,0,0,0.02);
}

.download-item {
    transition: all 0.2s ease;
}

.download-item:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

@media print {
    .btn, .card-header, nav, footer {
        display: none !important;
    }
    
    .container {
        max-width: 100% !important;
    }
    
    .card {
        border: 1px solid #ddd !important;
        box-shadow: none !important;
    }
}

/* Email confirmation styling */
.alert-info {
    border-left: 4px solid #0dcaf0;
}

/* Security badges */
.text-success {
    color: #198754 !important;
}

/* Download item styling */
.download-item {
    background-color: #f8f9fa;
    border: 1px solid #e9ecef !important;
}

.download-item:hover {
    background-color: #e9ecef;
}
</style>

<script>
// Auto-hide success message after 5 seconds
setTimeout(function() {
    const alerts = document.querySelectorAll('.alert-info');
    alerts.forEach(alert => {
        if (alert.textContent.includes('ready for download')) {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0.7';
        }
    });
}, 5000);

// Download tracking
document.querySelectorAll('a[href*="download.php"]').forEach(link => {
    link.addEventListener('click', function(e) {
        // You could add download tracking here
        console.log('Download initiated for:', this.href);
        
        // Show download started message
        const downloadItem = this.closest('.download-item');
        if (downloadItem) {
            const status = document.createElement('small');
            status.className = 'text-success ms-2';
            status.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Download starting...';
            this.parentNode.appendChild(status);
            
            setTimeout(() => {
                status.remove();
            }, 3000);
        }
    });
});
</script>

<?php include '../includes/layouts/footer.php'; ?> 