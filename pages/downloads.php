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

// Fetch purchased digital products
$sql = "SELECT p.*, up.download_count, up.last_download, up.purchase_date,
        o.payment_status, o.invoice_number
        FROM user_purchases up
        JOIN products p ON up.product_id = p.id
        JOIN orders o ON up.order_id = o.id
        WHERE up.user_id = ? AND p.type = 'ebook'
        AND o.payment_status = 'completed'
        ORDER BY up.purchase_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$downloads = $result->fetch_all(MYSQLI_ASSOC);

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
                        <a href="pending_orders.php" class="nav-item">
                            <i class="fas fa-clock"></i>
                            <span>Pending Orders</span>
                        </a>
                        <a href="downloads.php" class="nav-item active">
                            <i class="fas fa-download"></i>
                            <span>My Downloads</span>
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
                        <h2>My Downloads</h2>
                        <p class="text-muted">Access your purchased digital products</p>
                    </div>

                    <?php if (empty($downloads)): ?>
                    <div class="text-center py-5">
                        <div class="mb-4">
                            <i class="fas fa-book fa-4x text-muted"></i>
                        </div>
                        <h3 class="mb-3">No Downloads Available</h3>
                        <p class="text-muted mb-4">You haven't purchased any digital products yet.</p>
                        <a href="../index.php" class="btn btn-primary btn-lg px-5">
                            <i class="fas fa-shopping-cart me-2"></i>Browse Products
                        </a>
                    </div>
                    <?php else: ?>
                    <div class="row g-4">
                        <?php foreach ($downloads as $download): ?>
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="download-icon me-3">
                                            <i class="fas fa-file-pdf fa-2x text-danger"></i>
                                        </div>
                                        <div>
                                            <h5 class="card-title mb-1">
                                                <?php echo htmlspecialchars($download['name']); ?></h5>
                                            <p class="text-muted mb-0">
                                                <small>By <?php echo htmlspecialchars($download['author']); ?></small>
                                            </p>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between text-muted small mb-2">
                                            <span>File Size:</span>
                                            <span><?php echo $download['file_size']; ?></span>
                                        </div>
                                        <div class="d-flex justify-content-between text-muted small mb-2">
                                            <span>Purchased:</span>
                                            <span><?php echo date('M j, Y', strtotime($download['purchase_date'])); ?></span>
                                        </div>
                                        <div class="d-flex justify-content-between text-muted small">
                                            <span>Downloads:</span>
                                            <span><?php echo $download['download_count']; ?> times</span>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <?php if ($download['last_download']): ?>
                                            <small class="text-muted">
                                                Last downloaded:
                                                <?php echo date('M j, Y', strtotime($download['last_download'])); ?>
                                            </small>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <a href="download_file.php?id=<?php echo $download['id']; ?>"
                                                class="btn btn-primary">
                                                <i class="fas fa-download me-2"></i>Download
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

.download-icon {
    width: 50px;
    height: 50px;
    background: #fff3f3;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
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