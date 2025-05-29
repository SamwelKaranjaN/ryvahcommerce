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

<div class="settings-main-container">
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar Navigation -->
            <div class="col-lg-3">
                <div class="settings-sidebar-card">
                    <div class="settings-header text-center">
                        <div class="settings-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <h4 class="settings-name"><?php echo htmlspecialchars($user['full_name']); ?></h4>
                        <p class="settings-email"><?php echo htmlspecialchars($user['email']); ?></p>
                    </div>
                    <div class="settings-nav">
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
                        <a href="downloads.php" class="nav-item">
                            <i class="fas fa-download"></i>
                            <span>My Downloads</span>
                        </a>
                        <a href="wishlist.php" class="nav-item">
                            <i class="fas fa-heart"></i>
                            <span>Wishlist</span>
                        </a>
                        <a href="settings.php" class="nav-item active">
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
                <div class="settings-content-card">
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

                    <div class="settings-content-header">
                        <h2>Account Settings</h2>
                        <p class="text-muted">Manage your account preferences and security</p>
                    </div>

                    <div class="settings-section">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-body p-4">
                                <h5 class="card-title mb-4">Change Password</h5>
                                <form action="update_password.php" method="POST" class="needs-validation" novalidate>
                                    <div class="row g-3">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="form-label">Current Password</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                                    <input type="password" class="form-control" name="current_password"
                                                        required>
                                                    <button class="btn btn-outline-secondary toggle-password"
                                                        type="button">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">New Password</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="fas fa-key"></i></span>
                                                    <input type="password" class="form-control" name="new_password"
                                                        required pattern="^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$">
                                                    <button class="btn btn-outline-secondary toggle-password"
                                                        type="button">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </div>
                                                <div class="form-text">Password must be at least 8 characters long and
                                                    include both letters and numbers.</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">Confirm New Password</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="fas fa-key"></i></span>
                                                    <input type="password" class="form-control" name="confirm_password"
                                                        required>
                                                    <button class="btn btn-outline-secondary toggle-password"
                                                        type="button">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-4">
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="fas fa-save me-2"></i>Update Password
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-body p-4">
                                <h5 class="card-title mb-4">Notification Preferences</h5>
                                <form action="update_notifications.php" method="POST">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="emailNotifications"
                                            name="email_notifications" checked>
                                        <label class="form-check-label" for="emailNotifications">Email
                                            Notifications</label>
                                        <div class="form-text">Receive updates about your orders and account activity
                                        </div>
                                    </div>
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="marketingEmails"
                                            name="marketing_emails">
                                        <label class="form-check-label" for="marketingEmails">Marketing Emails</label>
                                        <div class="form-text">Receive special offers and promotions</div>
                                    </div>
                                    <div class="mt-4">
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="fas fa-save me-2"></i>Save Preferences
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-4">
                                <h5 class="card-title mb-4">Account Security</h5>
                                <div class="d-flex align-items-center mb-3">
                                    <div class="settings-security-icon me-3">
                                        <i class="fas fa-shield-alt"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">Two-Factor Authentication</h6>
                                        <p class="text-muted mb-0">Add an extra layer of security to your account</p>
                                    </div>
                                    <div class="ms-auto">
                                        <button class="btn btn-outline-primary" disabled>
                                            Coming Soon
                                        </button>
                                    </div>
                                </div>
                                <hr>
                                <div class="d-flex align-items-center">
                                    <div class="settings-security-icon me-3">
                                        <i class="fas fa-history"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">Login History</h6>
                                        <p class="text-muted mb-0">View your recent login activity</p>
                                    </div>
                                    <div class="ms-auto">
                                        <button class="btn btn-outline-primary" disabled>
                                            Coming Soon
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .settings-main-container {
        min-height: 100vh;
        background: #f8f9fa;
        padding: 2rem 0;
    }

    .settings-main-container .settings-sidebar-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 2px 16px rgba(0, 0, 0, 0.04);
        padding: 2rem 1.5rem;
        height: 100%;
        position: sticky;
        top: 90px;
    }

    .settings-main-container .settings-header {
        padding-bottom: 2rem;
        border-bottom: 1px solid #eee;
        margin-bottom: 2rem;
    }

    .settings-main-container .settings-avatar {
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

    .settings-main-container .settings-avatar i {
        font-size: 3rem;
        color: #fff;
    }

    .settings-main-container .settings-name {
        color: #2c3e50;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .settings-main-container .settings-email {
        color: #6c757d;
        font-size: 0.95rem;
    }

    .settings-main-container .settings-nav {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        margin-top: 2rem;
    }

    .settings-main-container .nav-item {
        display: flex;
        align-items: center;
        padding: 0.9rem 1rem;
        color: #2c3e50;
        text-decoration: none;
        border-radius: 10px;
        transition: all 0.2s;
        font-weight: 500;
    }

    .settings-main-container .nav-item i {
        width: 22px;
        margin-right: 1rem;
        font-size: 1.1rem;
    }

    .settings-main-container .nav-item:hover {
        background: #f8f9fa;
        color: #007bff;
    }

    .settings-main-container .nav-item.active {
        background: #007bff;
        color: #fff;
    }

    .settings-main-container .settings-content-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 2px 16px rgba(0, 0, 0, 0.04);
        padding: 2.5rem 2rem;
        min-height: 600px;
    }

    .settings-main-container .settings-content-header h2 {
        color: #2c3e50;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .settings-main-container .settings-content-header p {
        color: #6c757d;
        margin-bottom: 2rem;
    }

    .settings-main-container .settings-section .card {
        transition: transform 0.3s ease;
    }

    .settings-main-container .settings-section .card:hover {
        transform: translateY(-5px);
    }

    .settings-main-container .settings-security-icon {
        width: 50px;
        height: 50px;
        background: #f8f9fa;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .settings-main-container .settings-security-icon i {
        font-size: 1.5rem;
        color: #007bff;
    }

    .settings-main-container .btn {
        transition: all 0.3s ease;
    }

    .settings-main-container .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .settings-main-container .input-group-text {
        background-color: #f8f9fa;
        border-right: none;
        color: #6c757d;
    }

    .settings-main-container .form-control {
        border-left: none;
    }

    .settings-main-container .form-control:focus {
        border-color: #dee2e6;
        box-shadow: none;
    }

    .settings-main-container .input-group:focus-within {
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }

    @media (max-width: 991.98px) {
        .settings-main-container .settings-sidebar-card {
            margin-bottom: 2rem;
            position: static;
        }

        .settings-main-container .settings-content-card {
            padding: 1.5rem 0.5rem;
        }
    }

    @media (max-width: 767.98px) {
        .settings-main-container {
            padding: 1rem;
        }

        .settings-main-container .settings-content-card {
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
    // Password visibility toggle
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const input = this.previousElementSibling;
            const icon = this.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });
</script>

<?php include '../includes/layouts/footer.php'; ?>