<?php
require_once '../includes/bootstrap.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Fetch user's orders
$user_id = $_SESSION['user_id'];
$sql = "SELECT o.*, 
        COUNT(oi.id) as total_items,
        SUM(oi.quantity) as total_quantity
        FROM orders o 
        LEFT JOIN order_items oi ON o.id = oi.order_id 
        WHERE o.user_id = ? 
        GROUP BY o.id 
        ORDER BY o.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

include '../includes/layouts/header.php';
?>

<div class="container mt-5 pt-5">
    <div class="row">
        <!-- Profile Navigation -->
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-center mb-4">
                        <img src="<?php echo !empty($user['profile_image']) ? htmlspecialchars($user['profile_image']) : '../assets/images/default-avatar.png'; ?>"
                            class="rounded-circle mb-3" style="width: 100px; height: 100px; object-fit: cover;">
                        <h5 class="card-title"><?php echo htmlspecialchars($user['name']); ?></h5>
                        <p class="text-muted"><?php echo htmlspecialchars($user['email']); ?></p>
                    </div>
                    <div class="list-group">
                        <a href="profile.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-user me-2"></i>My Profile
                        </a>
                        <a href="orders.php" class="list-group-item list-group-item-action active">
                            <i class="fas fa-shopping-bag me-2"></i>My Orders
                        </a>
                        <a href="wishlist.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-heart me-2"></i>Wishlist
                        </a>
                        <a href="settings.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-cog me-2"></i>Settings
                        </a>
                        <a href="logout.php" class="list-group-item list-group-item-action text-danger">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Orders Content -->
        <div class="col-md-9">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-4">My Orders</h4>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($order = $result->fetch_assoc()): ?>
                            <div class="card mb-3">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div>
                                            <h6 class="mb-0">Order #<?php echo $order['id']; ?></h6>
                                            <small class="text-muted">Placed on
                                                <?php echo date('F j, Y', strtotime($order['created_at'])); ?></small>
                                        </div>
                                        <span class="badge bg-<?php
                                                                echo $order['status'] == 'completed' ? 'success' : ($order['status'] == 'processing' ? 'primary' : ($order['status'] == 'cancelled' ? 'danger' : 'warning'));
                                                                ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <p class="mb-1"><strong>Total Items:</strong> <?php echo $order['total_items']; ?>
                                            </p>
                                            <p class="mb-1"><strong>Total Quantity:</strong>
                                                <?php echo $order['total_quantity']; ?></p>
                                        </div>
                                        <div class="col-md-4">
                                            <p class="mb-1"><strong>Total Amount:</strong>
                                                $<?php echo number_format($order['total_amount'], 2); ?></p>
                                            <p class="mb-1"><strong>Payment Method:</strong>
                                                <?php echo ucfirst($order['payment_method']); ?></p>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <a href="order_details.php?id=<?php echo $order['id']; ?>"
                                                class="btn btn-outline-primary btn-sm">
                                                View Details
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-shopping-bag fa-3x text-muted mb-3"></i>
                            <h5>No Orders Yet</h5>
                            <p class="text-muted">You haven't placed any orders yet.</p>
                            <a href="index.php" class="btn btn-primary">Start Shopping</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .list-group-item {
        border: none;
        padding: 0.8rem 1rem;
        transition: all 0.3s ease;
    }

    .list-group-item:hover {
        background-color: #f8f9fa;
        transform: translateX(5px);
    }

    .list-group-item.active {
        background-color: #007bff;
        border-color: #007bff;
    }

    .card {
        border: none;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        border-radius: 10px;
    }

    .badge {
        padding: 0.5em 1em;
        font-weight: 500;
    }
</style>

<?php include '../includes/layouts/footer.php'; ?>