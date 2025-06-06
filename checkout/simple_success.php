<?php

/**
 * Enhanced PayPal Order Success Page
 * Ryvah Commerce - Secure order confirmation with download management
 */

session_start();
require_once '../includes/bootstrap.php';
require_once '../includes/paypal_config.php';
require_once '../includes/security.php';

// Set timezone for consistency
date_default_timezone_set('UTC');

// Enhanced security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Initialize variables
$order = null;
$order_items = [];
$download_links = [];
$address = [];
$currency = PAYPAL_DEFAULT_CURRENCY;
$error_message = null;

try {
    // Comprehensive security validation
    if (!isset($_SESSION['user_id']) || !is_numeric($_SESSION['user_id']) || $_SESSION['user_id'] <= 0) {
        logPayPalError('Unauthorized access attempt to success page', [
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
        header('Location: ../login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }

    // Validate required parameters
    if (!isset($_GET['order_id']) || !isset($_GET['token'])) {
        throw new Exception('Missing required parameters');
    }

    $order_id = filter_var($_GET['order_id'], FILTER_VALIDATE_INT);
    $token = isset($_GET['token']) ? trim(strip_tags($_GET['token'])) : '';

    if (!$order_id || $order_id <= 0) {
        throw new Exception('Invalid order ID');
    }

    // Enhanced token validation
    if (!isset($_SESSION['success_token']) || !hash_equals($_SESSION['success_token'], $token)) {
        logPayPalError('Invalid success token attempt', [
            'order_id' => $order_id,
            'provided_token' => $token,
            'user_id' => $_SESSION['user_id'],
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
        throw new Exception('Invalid access token');
    }

    // Fetch order with comprehensive validation
    $stmt = $conn->prepare("
        SELECT o.id, o.invoice_number, o.total_amount, o.tax_amount, o.payment_status, 
               o.shipping_address, o.created_at, o.currency, o.paypal_order_id,
               u.full_name, u.email
                        FROM orders o
        JOIN users u ON o.user_id = u.id
        WHERE o.id = ? AND o.user_id = ?
    ");

    if (!$stmt) {
        throw new Exception('Database prepare error: ' . $conn->error);
    }

    $stmt->bind_param("ii", $order_id, $_SESSION['user_id']);

    if (!$stmt->execute()) {
        throw new Exception('Database execute error: ' . $stmt->error);
    }

    $order = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$order) {
        throw new Exception('Order not found or unauthorized access');
    }

    if ($order['payment_status'] !== 'completed') {
        logPayPalError('Access attempt to non-completed order', [
            'order_id' => $order_id,
            'payment_status' => $order['payment_status'],
            'user_id' => $_SESSION['user_id']
        ]);
        throw new Exception('Order not completed');
    }

    // Set currency from order or user preference
    $currency = $order['currency'] ?: getUserCurrency($_SESSION['user_id']);

    // Validate and parse shipping address
    $address = json_decode($order['shipping_address'], true);
    if (!is_array($address) || !isset($address['street'], $address['city'], $address['postal_code'], $address['country'])) {
        logPayPalError('Invalid shipping address data', [
            'order_id' => $order_id,
            'shipping_address' => $order['shipping_address']
        ]);
        throw new Exception('Invalid shipping address data');
    }

    // Fetch order items with product details
    $stmt = $conn->prepare("
        SELECT oi.product_id, oi.quantity, oi.price, oi.subtotal, oi.tax_amount,
               p.name, p.type, p.author, p.description
                        FROM order_items oi
                        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?
        ORDER BY p.name
    ");

    if (!$stmt) {
        throw new Exception('Database prepare error for order items: ' . $conn->error);
    }

    $stmt->bind_param("i", $order_id);

    if (!$stmt->execute()) {
        throw new Exception('Database execute error for order items: ' . $stmt->error);
    }

    $order_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    if (empty($order_items)) {
        logPayPalError('No order items found', ['order_id' => $order_id]);
        throw new Exception('No items found for this order');
    }

    // Fetch eBook download links with enhanced security
    foreach ($order_items as $item) {
        if ($item['type'] === 'ebook') {
            $stmt = $conn->prepare("
                SELECT download_token, expires_at, download_count, max_downloads, created_at
                                FROM ebook_downloads
                WHERE order_id = ? AND product_id = ? AND user_id = ?
            ");

            if (!$stmt) {
                logPayPalError('Database prepare error for downloads: ' . $conn->error);
                continue;
            }

            $stmt->bind_param("iii", $order_id, $item['product_id'], $_SESSION['user_id']);

            if (!$stmt->execute()) {
                logPayPalError('Database execute error for downloads: ' . $stmt->error);
                continue;
            }

            $download = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($download) {
                $expires_at = strtotime($download['expires_at']);
                $is_expired = $expires_at <= time();
                $downloads_remaining = $download['max_downloads'] - $download['download_count'];

                if (!$is_expired && $downloads_remaining > 0) {
                    $download_links[$item['product_id']] = [
                        'token' => $download['download_token'],
                        'name' => $item['name'],
                        'author' => $item['author'],
                        'expires_at' => $download['expires_at'],
                        'downloads_remaining' => $downloads_remaining,
                        'max_downloads' => $download['max_downloads'],
                        'created_at' => $download['created_at']
                    ];
                }
            }
        }
    }

    // Log successful access
    logPayPalError('Success page accessed', [
        'order_id' => $order_id,
        'user_id' => $_SESSION['user_id'],
        'invoice_number' => $order['invoice_number']
    ]);
} catch (Exception $e) {
    logPayPalError('Error in success page: ' . $e->getMessage(), [
        'order_id' => $_GET['order_id'] ?? 'unknown',
        'user_id' => $_SESSION['user_id'] ?? 'unknown',
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);

    $error_message = 'Unable to load order details. Please contact support if this issue persists.';

    // Redirect to appropriate page based on error type
    if (strpos($e->getMessage(), 'not found') !== false || strpos($e->getMessage(), 'unauthorized') !== false) {
        header('Location: ../account/orders.php?error=order_not_found');
        exit;
    }
}

// Clear the success token to prevent reuse
if (isset($_SESSION['success_token'])) {
    unset($_SESSION['success_token']);
}

// Include header
require_once '../includes/layouts/header.php';
?>

<!-- Additional styles for success page -->

<style>
:root {
    --success-color: #28a745;
    --primary-color: #007bff;
    --secondary-color: #6c757d;
    --light-color: #f8f9fa;
    --dark-color: #343a40;
    --border-color: #e9ecef;
}

body {
    background-color: var(--light-color);
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.success-container {
    max-width: 1000px;
    margin: 2rem auto;
    padding: 0 1rem;
}

.success-header {
    background: linear-gradient(135deg, var(--success-color), #20c997);
    color: white;
    padding: 3rem 2rem;
    border-radius: 16px 16px 0 0;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.success-header::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: repeating-linear-gradient(45deg,
            transparent,
            transparent 10px,
            rgba(255, 255, 255, 0.1) 10px,
            rgba(255, 255, 255, 0.1) 20px);
    animation: float 20s linear infinite;
}

@keyframes float {
    0% {
        transform: translate(-50%, -50%) rotate(0deg);
    }

    100% {
        transform: translate(-50%, -50%) rotate(360deg);
    }
}

.success-header .content {
    position: relative;
    z-index: 1;
}

.success-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
    display: inline-block;
    animation: bounce 2s infinite;
}

@keyframes bounce {

    0%,
    20%,
    50%,
    80%,
    100% {
        transform: translateY(0);
    }

    40% {
        transform: translateY(-10px);
    }

    60% {
        transform: translateY(-5px);
    }
}

.card {
    border: none;
    border-radius: 0;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    margin-bottom: 0;
}

.card:last-child {
    border-radius: 0 0 16px 16px;
}

.card-header {
    background: white;
    border-bottom: 3px solid var(--primary-color);
    font-weight: 600;
    color: var(--dark-color);
}

.order-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}

.order-info-item {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    border-left: 4px solid var(--primary-color);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.order-info-label {
    font-weight: 600;
    color: var(--secondary-color);
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.5rem;
}

.order-info-value {
    font-size: 1.125rem;
    color: var(--dark-color);
    font-weight: 500;
}

.download-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1rem;
    position: relative;
    overflow: hidden;
}

.download-card::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 100px;
    height: 100px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    transform: translate(30px, -30px);
}

.download-btn {
    background: rgba(255, 255, 255, 0.2);
    border: 2px solid rgba(255, 255, 255, 0.3);
    color: white;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
    font-weight: 500;
}

.download-btn:hover {
    background: rgba(255, 255, 255, 0.3);
    border-color: rgba(255, 255, 255, 0.5);
    color: white;
    transform: translateY(-2px);
}

.download-info {
    font-size: 0.875rem;
    opacity: 0.9;
    margin-top: 0.5rem;
}

.table {
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.table thead th {
    background: var(--light-color);
    border: none;
    font-weight: 600;
    color: var(--dark-color);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-size: 0.875rem;
}

.table tbody tr {
    transition: background-color 0.2s ease;
}

.table tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.05);
}

.action-buttons {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    justify-content: center;
    margin-top: 2rem;
}

.btn {
    border-radius: 8px;
    font-weight: 500;
    padding: 0.75rem 1.5rem;
    transition: all 0.2s ease;
}

.btn:hover {
    transform: translateY(-1px);
}

.btn-primary {
    background: linear-gradient(135deg, var(--primary-color), #0056b3);
    border: none;
}

.btn-outline-primary {
    border: 2px solid var(--primary-color);
    color: var(--primary-color);
}

.btn-outline-primary:hover {
    background: var(--primary-color);
    border-color: var(--primary-color);
}

.alert {
    border: none;
    border-radius: 8px;
    padding: 1rem 1.5rem;
}

.alert-danger {
    background: #fef2f2;
    color: #dc2626;
    border-left: 4px solid #dc2626;
}

@media (max-width: 768px) {
    .success-header {
        padding: 2rem 1rem;
    }

    .order-info-grid {
        grid-template-columns: 1fr;
    }

    .action-buttons {
        flex-direction: column;
    }
}
</style>

<!-- Start main content -->
<div class="success-container">
    <?php if ($error_message): ?>
    <!-- Error Display -->
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <?= htmlspecialchars($error_message); ?>
    </div>
    <div class="text-center mt-4">
        <a href="../account/orders.php" class="btn btn-primary">
            <i class="bi bi-arrow-left me-2"></i>
            View My Orders
        </a>
    </div>
    <?php else: ?>

    <!-- Success Header -->
    <div class="success-header">
        <div class="content">
            <div class="success-icon">
                <i class="bi bi-check-circle-fill"></i>
            </div>
            <h1 class="display-5 fw-bold mb-3">Payment Successful!</h1>
            <p class="lead mb-0">Thank you for your purchase. Your order has been processed successfully.</p>
        </div>
    </div>

    <!-- Order Information -->
    <div class="card">
        <div class="card-body">
            <div class="order-info-grid">
                <div class="order-info-item">
                    <div class="order-info-label">Order Number</div>
                    <div class="order-info-value">#<?= htmlspecialchars($order['invoice_number']); ?></div>
                </div>
                <div class="order-info-item">
                    <div class="order-info-label">Order Date</div>
                    <div class="order-info-value"><?= date('F j, Y \a\t g:i A', strtotime($order['created_at'])); ?>
                    </div>
                </div>
                <div class="order-info-item">
                    <div class="order-info-label">Total Amount</div>
                    <div class="order-info-value">
                        <?= formatCurrency($order['total_amount'] + $order['tax_amount'], $currency); ?></div>
                </div>
                <div class="order-info-item">
                    <div class="order-info-label">Payment Method</div>
                    <div class="order-info-value">
                        <i class="bi bi-paypal text-primary"></i> PayPal
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Download Links -->
    <?php if (!empty($download_links)): ?>
    <div class="card">
        <div class="card-header">
            <h4 class="mb-0">
                <i class="bi bi-download me-2"></i>
                Digital Downloads
            </h4>
        </div>
        <div class="card-body">
            <div class="row">
                <?php foreach ($download_links as $product_id => $link): ?>
                <div class="col-md-6 mb-3">
                    <div class="download-card">
                        <h5 class="mb-2"><?= htmlspecialchars($link['name']); ?></h5>
                        <?php if ($link['author']): ?>
                        <p class="mb-2 opacity-75">by <?= htmlspecialchars($link['author']); ?></p>
                        <?php endif; ?>

                        <a href="../includes/download.php?token=<?= htmlspecialchars($link['token']); ?>"
                            class="download-btn" target="_blank">
                            <i class="bi bi-download"></i>
                            Download Now
                        </a>

                        <div class="download-info">
                            <div><strong><?= $link['downloads_remaining']; ?></strong> downloads remaining</div>
                            <div>Expires: <?= date('M j, Y', strtotime($link['expires_at'])); ?></div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="alert alert-info mt-3">
                <i class="bi bi-info-circle-fill me-2"></i>
                <strong>Download Instructions:</strong>
                Your downloads are available for <?= reset($download_links)['max_downloads']; ?> attempts and expire
                30 days from purchase.
                Please save your files to a secure location.
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Order Items -->
    <div class="card">
        <div class="card-header">
            <h4 class="mb-0">
                <i class="bi bi-basket me-2"></i>
                Order Details
            </h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Type</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order_items as $item): ?>
                        <tr>
                            <td>
                                <div>
                                    <strong><?= htmlspecialchars($item['name']); ?></strong>
                                    <?php if ($item['author']): ?>
                                    <br><small class="text-muted">by
                                        <?= htmlspecialchars($item['author']); ?></small>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-<?= $item['type'] === 'ebook' ? 'primary' : 'secondary'; ?>">
                                    <?= ucfirst($item['type']); ?>
                                </span>
                            </td>
                            <td><?= formatCurrency($item['price'], $currency); ?></td>
                            <td><?= intval($item['quantity']); ?></td>
                            <td><strong><?= formatCurrency($item['subtotal'], $currency); ?></strong></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="4">Subtotal:</th>
                            <th><?= formatCurrency($order['total_amount'], $currency); ?></th>
                        </tr>
                        <tr>
                            <th colspan="4">Tax:</th>
                            <th><?= formatCurrency($order['tax_amount'], $currency); ?></th>
                        </tr>
                        <tr class="table-success">
                            <th colspan="4">Total:</th>
                            <th><?= formatCurrency($order['total_amount'] + $order['tax_amount'], $currency); ?>
                            </th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Shipping Address -->
    <div class="card">
        <div class="card-header">
            <h4 class="mb-0">
                <i class="bi bi-truck me-2"></i>
                Shipping Information
            </h4>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-muted mb-2">Billing & Shipping Address</h6>
                    <address class="mb-0">
                        <strong><?= htmlspecialchars($order['full_name']); ?></strong><br>
                        <?= htmlspecialchars($address['street']); ?><br>
                        <?= htmlspecialchars($address['city']); ?>,
                        <?= htmlspecialchars($address['state']); ?>
                        <?= htmlspecialchars($address['postal_code']); ?><br>
                        <?= htmlspecialchars($address['country']); ?>
                    </address>
                </div>
                <div class="col-md-6">
                    <h6 class="text-muted mb-2">Contact Information</h6>
                    <p class="mb-1">
                        <i class="bi bi-envelope me-2"></i>
                        <?= htmlspecialchars($order['email']); ?>
                    </p>
                    <p class="mb-0">
                        <i class="bi bi-calendar me-2"></i>
                        Order placed on <?= date('F j, Y', strtotime($order['created_at'])); ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="action-buttons">
        <a href="../pages/orders" class="btn btn-primary">
            <i class="bi bi-list-ul me-2"></i>
            View All Orders
        </a>
        <a href="../pages/index" class="btn btn-outline-primary">
            <i class="bi bi-shop me-2"></i>
            Continue Shopping
        </a>
        <a href="../" class="btn btn-outline-primary">
            <i class="bi bi-house me-2"></i>
            Return to Home
        </a>
    </div>

    <?php endif; ?>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous">
</script>

<script>
// Enhanced success page functionality
document.addEventListener('DOMContentLoaded', function() {
    // Add download tracking
    const downloadButtons = document.querySelectorAll('.download-btn');
    downloadButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            // Track download attempt
            const productName = this.closest('.download-card').querySelector('h5')
                .textContent;
            console.log('Download initiated for:', productName);

            // Optional: Add analytics tracking here
            if (typeof gtag !== 'undefined') {
                gtag('event', 'download', {
                    'event_category': 'Digital Product',
                    'event_label': productName
                });
            }
        });
    });

    // Add order info animation
    const orderInfoItems = document.querySelectorAll('.order-info-item');
    orderInfoItems.forEach((item, index) => {
        item.style.opacity = '0';
        item.style.transform = 'translateY(20px)';

        setTimeout(() => {
            item.style.transition = 'all 0.5s ease';
            item.style.opacity = '1';
            item.style.transform = 'translateY(0)';
        }, index * 100);
    });

    // Auto-scroll to downloads if available
    const downloadSection = document.querySelector('.download-card');
    if (downloadSection) {
        setTimeout(() => {
            downloadSection.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
        }, 2000);
    }
});

// Print functionality
function printOrder() {
    window.print();
}

// Copy order number functionality
function copyOrderNumber() {
    const orderNumber = document.querySelector('.order-info-value').textContent;
    navigator.clipboard.writeText(orderNumber).then(() => {
        // Show success message
        const toast = document.createElement('div');
        toast.className = 'alert alert-success position-fixed';
        toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999;';
        toast.innerHTML = '<i class="bi bi-check-circle me-2"></i>Order number copied!';
        document.body.appendChild(toast);

        setTimeout(() => {
            document.body.removeChild(toast);
        }, 3000);
    });
}
</script>

<?php
// Include footer
require_once '../includes/layouts/footer.php';
?>