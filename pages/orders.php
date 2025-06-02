<?php
require_once '../includes/bootstrap.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=orders');
    exit();
}

$user_id = $_SESSION['user_id'];

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

// Fetch user data
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

include '../includes/layouts/header.php';
?>

<div class="profile-container">
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar Navigation -->
            <div class="col-lg-3">
                <div class="profile-sidebar">
                    <div class="profile-header text-center">
                        <div class="profile-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <h4 class="profile-name"><?php echo htmlspecialchars($user['full_name']); ?></h4>
                        <p class="profile-email"><?php echo htmlspecialchars($user['email']); ?></p>
                    </div>
                    <div class="profile-nav">
                        <a href="profile.php" class="nav-item">
                            <i class="fas fa-user-circle"></i>
                            <span>My Profile</span>
                        </a>
                        <a href="orders.php" class="nav-item active">
                            <i class="fas fa-shopping-bag"></i>
                            <span>My Orders</span>
                        </a>
                        <a href="pending_orders.php" class="nav-item">
                            <i class="fas fa-clock"></i>
                            <span>Pending Orders</span>
                        </a>
                        <a href="addresses.php" class="nav-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>Addresses</span>
                        </a>
                        <a href="wishlist.php" class="nav-item">
                            <i class="fas fa-heart"></i>
                            <span>Wishlist</span>
                        </a>
                        <a href="settings.php" class="nav-item">
                            <i class="fas fa-cog"></i>
                            <span>Settings</span>
                        </a>
                        <a href="logout.php" class="nav-item text-danger">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Logout</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-lg-9">
                <div class="profile-content">
                    <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php
                echo $_SESSION['success_message'];
                unset($_SESSION['success_message']);
                ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php endif; ?>

                    <div class="content-header">
                        <h2>My Orders</h2>
                        <p class="text-muted">View and track all your orders</p>
                    </div>

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
                                            <h5 class="card-title mb-1">Order
                                                #<?php echo htmlspecialchars($order['invoice_number'] ?: $order['id']); ?>
                                            </h5>
                                            <p class="text-muted mb-0">
                                                Placed on <?php echo date('F j, Y', strtotime($order['created_at'])); ?>
                                            </p>
                                        </div>
                                        <div class="text-end">
                                            <?php
                                                    $status = $order['current_status'] ?? $order['payment_status'] ?? 'unknown';
                                                    if ($status == 'pending') {
                                                        $statusClass = 'warning';
                                                    } elseif ($status == 'processing') {
                                                        $statusClass = 'info';
                                                    } elseif ($status == 'completed') {
                                                        $statusClass = 'success';
                                                    } elseif ($status == 'failed') {
                                                        $statusClass = 'danger';
                                                    } elseif ($status == 'refunded') {
                                                        $statusClass = 'secondary';
                                                    } else {
                                                        $statusClass = 'secondary';
                                                    }
                                                    ?>
                                            <span class="badge bg-<?php echo $statusClass; ?>">
                                                <?php echo ucfirst($status); ?>
                                            </span>
                                            <p class="text-muted mb-0 mt-1">
                                                <small>Last updated:
                                                    <?php
                                                            $lastUpdate = $order['last_status_update'] ?? $order['created_at'];
                                                            echo $lastUpdate ? date('M j, Y g:i A', strtotime($lastUpdate)) : 'N/A';
                                                            ?></small>
                                            </p>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <h6 class="mb-2">Order Items:</h6>
                                        <ul class="list-unstyled mb-0">
                                            <?php for ($i = 0; $i < count($product_names); $i++): ?>
                                            <li class="mb-1">
                                                <?php echo isset($product_names[$i]) ? htmlspecialchars($product_names[$i]) : 'Unknown Product'; ?>
                                                <span class="text-muted">(Qty:
                                                    <?php echo isset($quantities[$i]) ? $quantities[$i] : '0'; ?>)</span>
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
                                                <?php echo ucfirst($order['payment_method'] ?? 'unknown'); ?>
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
                        <div class="modal fade" id="trackingModal<?php echo $order['id']; ?>" tabindex="-1"
                            aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Track Order
                                            #<?php echo htmlspecialchars($order['invoice_number'] ?: $order['id']); ?>
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
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
                                                    <h6 class="mb-1">
                                                        <?php echo ucfirst($status['status'] ?? 'unknown'); ?></h6>
                                                    <p class="text-muted mb-0">
                                                        <?php echo $status['created_at'] ? date('F j, Y g:i A', strtotime($status['created_at'])) : 'N/A'; ?>
                                                    </p>
                                                    <?php if (!empty($status['notes'])): ?>
                                                    <p class="mb-0"><?php echo htmlspecialchars($status['notes']); ?>
                                                    </p>
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
            </div>
        </div>
    </div>
</div>

<style>
.profile-container {
    min-height: 100vh;
    background: #f8f9fa;
    padding: 2rem 0;
}

.profile-sidebar {
    background: white;
    border-radius: 15px;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
    padding: 2rem;
    height: 100%;
}

.profile-header {
    padding-bottom: 2rem;
    border-bottom: 1px solid #eee;
    margin-bottom: 2rem;
}

.profile-avatar {
    width: 120px;
    height: 120px;
    background: linear-gradient(45deg, #007bff, #00bcd4);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
    box-shadow: 0 5px 15px rgba(0, 123, 255, 0.3);
}

.profile-avatar i {
    font-size: 3.5rem;
    color: white;
}

.profile-name {
    color: #2c3e50;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.profile-email {
    color: #6c757d;
    font-size: 0.9rem;
}

.profile-nav {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.nav-item {
    display: flex;
    align-items: center;
    padding: 1rem;
    color: #2c3e50;
    text-decoration: none;
    border-radius: 10px;
    transition: all 0.3s ease;
}

.nav-item i {
    width: 24px;
    margin-right: 1rem;
    font-size: 1.1rem;
}

.nav-item:hover {
    background: #f8f9fa;
    color: #007bff;
}

.nav-item.active {
    background: #007bff;
    color: white;
}

.profile-content {
    padding: 0 1rem;
}

.content-header {
    margin-bottom: 2rem;
}

.content-header h2 {
    color: #2c3e50;
    font-weight: 600;
    margin-bottom: 0.5rem;
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

/* Timeline Styles */
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 10px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}

.timeline-item {
    position: relative;
    margin-bottom: 1.5rem;
}

.timeline-marker {
    position: absolute;
    left: -25px;
    top: 5px;
    width: 12px;
    height: 12px;
    background: #007bff;
    border-radius: 50%;
    border: 2px solid white;
    box-shadow: 0 0 0 2px #dee2e6;
}

.timeline-content {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 8px;
    border-left: 3px solid #007bff;
}

@media (max-width: 991.98px) {
    .profile-sidebar {
        margin-bottom: 2rem;
    }

    .profile-content {
        padding: 0;
    }
}

@media (max-width: 767.98px) {
    .profile-container {
        padding: 1rem;
    }

    .card {
        margin-bottom: 1rem;
    }

    .timeline {
        padding-left: 20px;
    }

    .timeline-marker {
        left: -15px;
        width: 10px;
        height: 10px;
    }

    .text-end {
        text-align: left !important;
        margin-top: 0.5rem;
    }

    .btn {
        width: 100%;
        margin-bottom: 0.5rem;
    }
}
</style>

<?php include '../includes/layouts/footer.php'; ?>