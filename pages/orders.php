<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php?redirect=orders.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$conn = getDBConnection();

// Get user's orders with their items and status history
$sql = "SELECT o.*, 
        GROUP_CONCAT(DISTINCT p.type) as product_types,
        GROUP_CONCAT(DISTINCT p.name SEPARATOR '||') as product_names,
        GROUP_CONCAT(DISTINCT oi.quantity SEPARATOR '||') as quantities,
        (
            SELECT status 
            FROM order_status_history 
            WHERE order_id = o.id 
            ORDER BY created_at DESC 
            LIMIT 1
        ) as current_status,
        (
            SELECT created_at 
            FROM order_status_history 
            WHERE order_id = o.id 
            ORDER BY created_at DESC 
            LIMIT 1
        ) as last_status_update
        FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        JOIN products p ON oi.product_id = p.id
        WHERE o.user_id = ?
        GROUP BY o.id
        ORDER BY o.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$orders = $result->fetch_all(MYSQLI_ASSOC);

include '../includes/layouts/header.php';
?>

<div class="container py-5">
    <div class="row mb-4">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="../index.php" class="text-decoration-none">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">My Orders</li>
                </ol>
            </nav>
            <h2 class="mb-0">My Orders</h2>
        </div>
    </div>

    <?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php
            echo $_SESSION['success_message'];
            unset($_SESSION['success_message']);
            ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <?php if (empty($orders)): ?>
    <div class="text-center py-5">
        <div class="mb-4">
            <i class="fas fa-shopping-bag fa-4x text-muted"></i>
        </div>
        <h3 class="mb-3">No orders found</h3>
        <p class="text-muted mb-4">You haven't placed any orders yet.</p>
        <a href="../index.php" class="btn btn-primary btn-lg px-5">
            <i class="fas fa-shopping-cart me-2"></i>Start Shopping
        </a>
    </div>
    <?php else: ?>
    <div class="row g-4">
        <?php foreach ($orders as $order):
                $product_names = explode('||', $order['product_names']);
                $quantities = explode('||', $order['quantities']);
                $product_types = explode(',', $order['product_types']);
                $has_ebooks = in_array('ebook', $product_types);
                $has_physical = in_array('book', $product_types) || in_array('paint', $product_types);
            ?>
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="card-title mb-1">Order #<?php echo htmlspecialchars($order['invoice_number']); ?>
                            </h5>
                            <p class="text-muted mb-0">
                                Placed on <?php echo date('F j, Y', strtotime($order['created_at'])); ?>
                            </p>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-<?php
                                                            echo match ($order['current_status']) {
                                                                'pending' => 'warning',
                                                                'processing' => 'info',
                                                                'shipped' => 'primary',
                                                                'delivered' => 'success',
                                                                'cancelled' => 'danger',
                                                                default => 'secondary'
                                                            };
                                                            ?>">
                                <?php echo ucfirst($order['current_status']); ?>
                            </span>
                            <p class="text-muted mb-0 mt-1">
                                <small>Last updated:
                                    <?php echo date('M j, Y g:i A', strtotime($order['last_status_update'])); ?></small>
                            </p>
                        </div>
                    </div>

                    <div class="mb-3">
                        <h6 class="mb-2">Order Items:</h6>
                        <ul class="list-unstyled mb-0">
                            <?php for ($i = 0; $i < count($product_names); $i++): ?>
                            <li class="mb-1">
                                <?php echo htmlspecialchars($product_names[$i]); ?>
                                <span class="text-muted">(Qty: <?php echo $quantities[$i]; ?>)</span>
                            </li>
                            <?php endfor; ?>
                        </ul>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-0">
                                <strong>Total Amount:</strong>
                                $<?php echo number_format($order['total_amount'], 2); ?>
                            </p>
                            <p class="mb-0">
                                <strong>Payment Method:</strong>
                                <?php echo ucfirst($order['payment_method']); ?>
                            </p>
                        </div>
                        <div class="text-end">
                            <?php if ($has_ebooks): ?>
                            <a href="my_ebooks.php" class="btn btn-outline-primary me-2">
                                <i class="fas fa-book me-2"></i>View Ebooks
                            </a>
                            <?php endif; ?>
                            <?php if ($has_physical): ?>
                            <button class="btn btn-outline-primary" data-bs-toggle="modal"
                                data-bs-target="#trackingModal<?php echo $order['id']; ?>">
                                <i class="fas fa-truck me-2"></i>Track Order
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($has_physical): ?>
        <!-- Tracking Modal -->
        <div class="modal fade" id="trackingModal<?php echo $order['id']; ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Track Order #<?php echo htmlspecialchars($order['invoice_number']); ?>
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <?php
                                    // Get order status history
                                    $sql = "SELECT * FROM order_status_history 
                                WHERE order_id = ? 
                                ORDER BY created_at DESC";
                                    $stmt = $conn->prepare($sql);
                                    $stmt->bind_param("i", $order['id']);
                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                    $status_history = $result->fetch_all(MYSQLI_ASSOC);
                                    ?>
                        <div class="timeline">
                            <?php foreach ($status_history as $status): ?>
                            <div class="timeline-item">
                                <div class="timeline-marker"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1"><?php echo ucfirst($status['status']); ?></h6>
                                    <p class="text-muted mb-0">
                                        <?php echo date('F j, Y g:i A', strtotime($status['created_at'])); ?>
                                    </p>
                                    <?php if ($status['notes']): ?>
                                    <p class="mb-0"><?php echo htmlspecialchars($status['notes']); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<style>
.timeline {
    position: relative;
    padding: 20px 0;
}

.timeline-item {
    position: relative;
    padding-left: 40px;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: 0;
    top: 0;
    width: 15px;
    height: 15px;
    border-radius: 50%;
    background: #007bff;
    border: 3px solid #fff;
    box-shadow: 0 0 0 2px #007bff;
}

.timeline-item:not(:last-child)::before {
    content: '';
    position: absolute;
    left: 7px;
    top: 15px;
    height: calc(100% + 5px);
    width: 2px;
    background: #e9ecef;
}

.timeline-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 4px;
}

.card {
    transition: transform 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
}

.btn {
    transition: all 0.3s ease;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

@media (max-width: 768px) {
    .card {
        margin-bottom: 1rem;
    }
}
</style>

<?php include '../includes/layouts/footer.php'; ?>