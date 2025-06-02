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

// Fetch pending orders
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
        WHERE o.user_id = ? AND o.payment_status = 'pending'
        GROUP BY o.id
        ORDER BY o.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$pending_orders = $result->fetch_all(MYSQLI_ASSOC);

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
                        <a href="orders.php" class="nav-item">
                            <i class="fas fa-shopping-bag"></i>
                            <span>My Orders</span>
                        </a>
                        <a href="pending_orders.php" class="nav-item active">
                            <i class="fas fa-clock"></i>
                            <span>Pending Orders</span>
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

                    <div class="content-header">
                        <h2>Pending Orders</h2>
                        <p class="text-muted">Complete payment for your pending orders</p>
        </div>

        <?php if (empty($pending_orders)): ?>
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <i class="fas fa-check-circle fa-4x text-success"></i>
                            </div>
                            <h3 class="mb-3">No Pending Orders</h3>
                            <p class="text-muted mb-4">You have no orders waiting for payment.</p>
                            <a href="../index.php" class="btn btn-primary btn-lg px-5">
                                <i class="fas fa-shopping-cart me-2"></i>Start Shopping
                            </a>
            </div>
        <?php else: ?>
            <div class="row g-4">
                            <?php foreach ($pending_orders as $order):
                                $product_names = explode('||', $order['product_names']);
                                $quantities = explode('||', $order['quantities']);
                                $product_types = explode(',', $order['product_types']);
                            ?>
                                <div class="col-12">
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                <div>
                                                    <h5 class="card-title mb-1">Order
                                                        #<?php echo htmlspecialchars($order['id']); ?></h5>
                                                    <p class="text-muted mb-0">
                                                        Placed on <?php echo $order['created_at'] ? date('F j, Y', strtotime($order['created_at'])) : 'N/A'; ?>
                                                    </p>
                                </div>
                                                <div class="text-end">
                                                    <span class="badge bg-warning">Pending Payment</span>
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
                                    </div>
                                                <div class="text-end">
                                                    <a href="../checkout/checkout.php?order_id=<?php echo $order['id']; ?>"
                                                        class="btn btn-primary">
                                                        <i class="fas fa-credit-card me-2"></i>Complete Payment
                                    </a>
                                                </div>
                                </div>
                            </div>
                        </div>
                    </div>
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
    }
</style>

    <?php include '../includes/layouts/footer.php'; ?>