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
                        <a href="profile.php" class="nav-item active">
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
                        <a href="downloads.php" class="nav-item">
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
                                            <h4>0</h4>
                                            <span>Total Orders</span>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="stat-box">
                                            <i class="fas fa-heart"></i>
                                            <h4>0</h4>
                                            <span>Wishlist</span>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="stat-box">
                                            <i class="fas fa-star"></i>
                                            <h4>0</h4>
                                            <span>Reviews</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="profile-orders-card">
                        <h5 class="mb-3">Order History</h5>
                        <div class="order-history-empty text-center p-5">
                            <i class="fas fa-shopping-bag fa-3x text-muted mb-3"></i>
                            <h5>No Orders Yet</h5>
                            <p class="text-muted">Start shopping to see your orders here</p>
                            <a href="../index.php" class="btn btn-primary">Start Shopping</a>
                        </div>
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