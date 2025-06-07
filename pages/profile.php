<?php
require_once '../includes/bootstrap.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Fetch user data
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

include '../includes/layouts/header.php';
?>

<div class="profile-main-container">
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar Navigation -->
            <div class="col-lg-3">
                <div class="profile-sidebar-card">
                    <div class="profile-header text-center">
                        <div class="profile-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <h4 class="profile-name"><?php echo htmlspecialchars($user['full_name']); ?></h4>
                        <p class="profile-email"><?php echo htmlspecialchars($user['email']); ?></p>
                    </div>
                    <div class="profile-nav">
                        <a href="profile" class="nav-item active">
                            <i class="fas fa-user-circle"></i>
                            <span>My Profile</span>
                        </a>
                        <a href="orders" class="nav-item">
                            <i class="fas fa-shopping-bag"></i>
                            <span>My Orders</span>
                        </a>
                        <a href="pending_orders" class="nav-item">
                            <i class="fas fa-clock"></i>
                            <span>Pending Orders</span>
                        </a>
                        <a href="downloads" class="nav-item">
                            <i class="fas fa-download"></i>
                            <span>My Downloads</span>
                        </a>
                        <a href="settings" class="nav-item">
                            <i class="fas fa-cog"></i>
                            <span>Settings</span>
                        </a>
                        <a href="logout" class="nav-item text-danger">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Logout</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-lg-9">
                <div class="profile-content-card">
                    <?php if (isset($_SESSION['success_message'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php
                            echo $_SESSION['success_message'];
                            unset($_SESSION['success_message']);
                            ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['error_messages'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <ul class="mb-0">
                                <?php
                                foreach ($_SESSION['error_messages'] as $error) {
                                    echo "<li>" . htmlspecialchars($error) . "</li>";
                                }
                                unset($_SESSION['error_messages']);
                                ?>
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <div class="profile-content-header">
                        <h2>Profile Overview</h2>
                        <p class="text-muted">Manage your personal information and see your stats</p>
                    </div>

                    <?php
                    // Get user's order statistics
                    $stats_sql = "SELECT 
                        COUNT(*) as total_orders,
                        COUNT(CASE WHEN payment_status = 'completed' THEN 1 END) as completed_orders,
                        COUNT(CASE WHEN payment_status = 'pending' THEN 1 END) as pending_orders,
                        COUNT(CASE WHEN payment_status = 'processing' THEN 1 END) as processing_orders,
                        SUM(CASE WHEN payment_status = 'completed' THEN total_amount ELSE 0 END) as total_spent
                        FROM orders WHERE user_id = ?";
                    $stmt = $conn->prepare($stats_sql);
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $stats = $stmt->get_result()->fetch_assoc();
                    ?>

                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <div class="profile-info-card">
                                <h5 class="mb-3">Personal Information</h5>
                                <form action="update_profile.php" method="POST" class="needs-validation" novalidate>
                                    <div class="mb-3">
                                        <label class="form-label">Full Name</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                                            <input type="text" class="form-control" name="full_name"
                                                value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Email</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                            <input type="email" class="form-control"
                                                value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Phone</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                            <input type="tel" class="form-control" name="phone"
                                                value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                                                pattern="[0-9]{10}" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Address</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                            <textarea class="form-control" name="address" rows="1"
                                                required><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100 mt-2">
                                        <i class="fas fa-save me-2"></i>Save Changes
                                    </button>
                                </form>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="profile-stats-card">
                                <h5 class="mb-3">Account Stats</h5>
                                <div class="row g-3">
                                    <div class="col-4">
                                        <div class="stat-box">
                                            <i class="fas fa-shopping-bag"></i>
                                            <h4><?php echo $stats['total_orders']; ?></h4>
                                            <span>Total Orders</span>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="stat-box">
                                            <i class="fas fa-check-circle"></i>
                                            <h4><?php echo $stats['completed_orders']; ?></h4>
                                            <span>Completed</span>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="stat-box">
                                            <i class="fas fa-dollar-sign"></i>
                                            <h4>$<?php echo number_format($stats['total_spent'] ?? 0, 0); ?></h4>
                                            <span>Total Spent</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php
                    // Get user's recent orders
                    $orders_sql = "SELECT o.*, 
                        GROUP_CONCAT(DISTINCT p.type) as product_types,
                        GROUP_CONCAT(DISTINCT p.name SEPARATOR '||') as product_names,
                        GROUP_CONCAT(DISTINCT oi.quantity SEPARATOR '||') as quantities,
                        COUNT(oi.id) as item_count
                        FROM orders o
                        LEFT JOIN order_items oi ON o.id = oi.order_id
                        LEFT JOIN products p ON oi.product_id = p.id
                        WHERE o.user_id = ?
                        GROUP BY o.id
                        ORDER BY o.created_at DESC
                        LIMIT 5";
                    $stmt = $conn->prepare($orders_sql);
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $recent_orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                    ?>

                    <div class="profile-orders-card">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Recent Orders</h5>
                            <a href="orders" class="btn btn-outline-primary btn-sm">View All Orders</a>
                        </div>

                        <?php if (empty($recent_orders)): ?>
                            <div class="order-history-empty text-center p-5">
                                <i class="fas fa-shopping-bag fa-3x text-muted mb-3"></i>
                                <h5>No Orders Yet</h5>
                                <p class="text-muted">Start shopping to see your orders here</p>
                                <a href="../index" class="btn btn-primary">Start Shopping</a>
                            </div>
                        <?php else: ?>
                            <div class="order-list">
                                <?php foreach ($recent_orders as $order):
                                    $product_names = $order['product_names'] ? explode('||', $order['product_names']) : [];
                                    $quantities = $order['quantities'] ? explode('||', $order['quantities']) : [];
                                    $has_ebooks = strpos($order['product_types'], 'ebook') !== false;
                                ?>
                                    <div class="order-item">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="order-info">
                                                <h6 class="mb-1">
                                                    Order
                                                    #<?php echo htmlspecialchars($order['invoice_number'] ?: $order['id']); ?>
                                                </h6>
                                                <p class="text-muted mb-1">
                                                    <?php echo date('F j, Y', strtotime($order['created_at'])); ?>
                                                </p>
                                                <p class="mb-1">
                                                    <small class="text-muted">
                                                        <?php echo $order['item_count']; ?> item(s) •
                                                        $<?php echo number_format($order['total_amount'] ?? 0, 2); ?>
                                                    </small>
                                                </p>
                                            </div>
                                            <div class="order-status text-end">
                                                <?php
                                                $status = $order['payment_status'];
                                                $statusClass = 'secondary';
                                                if ($status == 'pending') $statusClass = 'warning';
                                                elseif ($status == 'processing') $statusClass = 'info';
                                                elseif ($status == 'completed') $statusClass = 'success';
                                                elseif ($status == 'failed') $statusClass = 'danger';
                                                elseif ($status == 'refunded') $statusClass = 'secondary';
                                                ?>
                                                <span class="badge bg-<?php echo $statusClass; ?> mb-2">
                                                    <?php echo ucfirst($status); ?>
                                                </span>

                                                <?php if ($status === 'completed' && $has_ebooks): ?>
                                                    <br>
                                                    <a href="my_ebooks" class="btn btn-outline-primary btn-sm">
                                                        <i class="fas fa-book me-1"></i>View Ebooks
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <?php if (!empty($product_names)): ?>
                                            <div class="order-items mt-2">
                                                <small class="text-muted">Items:
                                                    <?php
                                                    $displayItems = array_slice($product_names, 0, 2);
                                                    echo htmlspecialchars(implode(', ', $displayItems));
                                                    if (count($product_names) > 2) {
                                                        echo ' and ' . (count($product_names) - 2) . ' more';
                                                    }
                                                    ?>
                                                </small>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Profile Page Unique Styles */
    .profile-main-container {
        min-height: 100vh;
        background: #f8f9fa;
        padding: 2rem 0;
    }

    .profile-main-container .profile-sidebar-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 2px 16px rgba(0, 0, 0, 0.04);
        padding: 2rem 1.5rem;
        height: 100%;
        position: sticky;
        top: 90px;
    }

    .profile-main-container .profile-header {
        padding-bottom: 2rem;
        border-bottom: 1px solid #eee;
        margin-bottom: 2rem;
    }

    .profile-main-container .profile-avatar {
        width: 110px;
        height: 110px;
        background: linear-gradient(45deg, #007bff, #00bcd4);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.2rem;
        box-shadow: 0 5px 15px rgba(0, 123, 255, 0.15);
    }

    .profile-main-container .profile-avatar i {
        font-size: 3rem;
        color: #fff;
    }

    .profile-main-container .profile-name {
        color: #2c3e50;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .profile-main-container .profile-email {
        color: #6c757d;
        font-size: 0.95rem;
    }

    .profile-main-container .profile-nav {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        margin-top: 2rem;
    }

    .profile-main-container .nav-item {
        display: flex;
        align-items: center;
        padding: 0.9rem 1rem;
        color: #2c3e50;
        text-decoration: none;
        border-radius: 10px;
        transition: all 0.2s;
        font-weight: 500;
    }

    .profile-main-container .nav-item i {
        width: 22px;
        margin-right: 1rem;
        font-size: 1.1rem;
    }

    .profile-main-container .nav-item:hover {
        background: #f8f9fa;
        color: #007bff;
    }

    .profile-main-container .nav-item.active {
        background: #007bff;
        color: #fff;
    }

    .profile-main-container .profile-content-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 2px 16px rgba(0, 0, 0, 0.04);
        padding: 2.5rem 2rem;
        min-height: 600px;
    }

    .profile-main-container .profile-content-header h2 {
        color: #2c3e50;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .profile-main-container .profile-content-header p {
        color: #6c757d;
        margin-bottom: 2rem;
    }

    .profile-main-container .profile-info-card {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 1.5rem 1.2rem;
        box-shadow: 0 1px 6px rgba(0, 0, 0, 0.03);
    }

    .profile-main-container .profile-stats-card {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 1.5rem 1.2rem;
        box-shadow: 0 1px 6px rgba(0, 0, 0, 0.03);
    }

    .profile-main-container .stat-box {
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.04);
        padding: 1.2rem 0.5rem;
        text-align: center;
        margin-bottom: 0.5rem;
    }

    .profile-main-container .stat-box i {
        color: #007bff;
        font-size: 1.3rem;
        margin-bottom: 0.3rem;
    }

    .profile-main-container .stat-box h4 {
        color: #2c3e50;
        font-weight: 700;
        margin: 0.2rem 0 0.3rem 0;
    }

    .profile-main-container .stat-box span {
        color: #6c757d;
        font-size: 0.95rem;
    }

    .profile-main-container .profile-orders-card {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 2rem 1.5rem;
        box-shadow: 0 1px 6px rgba(0, 0, 0, 0.03);
        margin-top: 2rem;
    }

    .profile-main-container .order-history-empty {
        color: #6c757d;
    }

    .profile-main-container .order-list {
        max-height: 400px;
        overflow-y: auto;
    }

    .profile-main-container .order-item {
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 0.75rem;
        transition: all 0.2s ease;
    }

    .profile-main-container .order-item:hover {
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        transform: translateY(-1px);
    }

    .profile-main-container .order-item:last-child {
        margin-bottom: 0;
    }

    .profile-main-container .order-info h6 {
        color: #2c3e50;
        font-weight: 600;
    }

    .profile-main-container .order-items {
        padding-top: 0.5rem;
        border-top: 1px solid #f8f9fa;
    }

    .profile-main-container .btn {
        transition: all 0.3s ease;
    }

    .profile-main-container .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .profile-main-container .input-group-text {
        background-color: #f8f9fa;
        border-right: none;
        color: #6c757d;
    }

    .profile-main-container .form-control {
        border-left: none;
    }

    .profile-main-container .form-control:focus {
        border-color: #dee2e6;
        box-shadow: none;
    }

    .profile-main-container .input-group:focus-within {
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }

    @media (max-width: 991.98px) {
        .profile-main-container .profile-sidebar-card {
            margin-bottom: 2rem;
            position: static;
        }

        .profile-main-container .profile-content-card {
            padding: 1.5rem 0.5rem;
        }
    }

    @media (max-width: 767.98px) {
        .profile-main-container {
            padding: 1rem;
        }

        .profile-main-container .profile-content-card {
            padding: 1rem 0.2rem;
        }
    }
</style>

<script>
    // Form validation
    (function() {
        'use strict';
        const forms = document.querySelectorAll('.needs-validation');
        Array.from(forms).forEach(form => {
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            });
        });
    })();
</script>

<?php include '../includes/layouts/footer.php'; ?>