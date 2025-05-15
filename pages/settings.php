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
                        <h2>Account Settings</h2>
                        <p class="text-muted">Manage your account preferences and security settings</p>
                    </div>

                    <!-- Change Password Form -->
                    <div class="profile-card">
                        <form action="update_password.php" method="POST" id="passwordForm" class="needs-validation"
                            novalidate>
                            <h5 class="card-title mb-4">
                                <i class="fas fa-lock me-2"></i>Change Password
                            </h5>
                            <div class="row g-4">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="form-label">Current Password</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-key"></i></span>
                                            <input type="password" class="form-control" name="current_password"
                                                required>
                                        </div>
                                        <div class="invalid-feedback">Please enter your current password.</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">New Password</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                            <input type="password" class="form-control" name="new_password"
                                                pattern="^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$" required>
                                        </div>
                                        <div class="invalid-feedback">
                                            Password must be at least 8 characters long and include both letters and
                                            numbers.
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Confirm New Password</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                            <input type="password" class="form-control" name="confirm_password"
                                                required>
                                        </div>
                                        <div class="invalid-feedback">Passwords do not match.</div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-key me-2"></i>Update Password
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Notification Preferences -->
                    <div class="profile-card">
                        <form action="update_notifications.php" method="POST" id="notificationForm">
                            <h5 class="card-title mb-4">
                                <i class="fas fa-bell me-2"></i>Notification Preferences
                            </h5>
                            <div class="preference-group">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="email_notifications"
                                        id="emailNotifications" checked>
                                    <label class="form-check-label" for="emailNotifications">
                                        <i class="fas fa-envelope me-2"></i>Email Notifications
                                    </label>
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="order_updates"
                                        id="orderUpdates" checked>
                                    <label class="form-check-label" for="orderUpdates">
                                        <i class="fas fa-shopping-bag me-2"></i>Order Updates
                                    </label>
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="promotional_emails"
                                        id="promotionalEmails">
                                    <label class="form-check-label" for="promotionalEmails">
                                        <i class="fas fa-bullhorn me-2"></i>Promotional Emails
                                    </label>
                                </div>
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Save Preferences
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Privacy Settings -->
                    <div class="profile-card">
                        <form action="update_privacy.php" method="POST" id="privacyForm">
                            <h5 class="card-title mb-4">
                                <i class="fas fa-shield-alt me-2"></i>Privacy Settings
                            </h5>
                            <div class="preference-group">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="profile_visibility"
                                        id="profileVisibility" checked>
                                    <label class="form-check-label" for="profileVisibility">
                                        <i class="fas fa-eye me-2"></i>Make Profile Public
                                    </label>
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="show_order_history"
                                        id="showOrderHistory" checked>
                                    <label class="form-check-label" for="showOrderHistory">
                                        <i class="fas fa-history me-2"></i>Show Order History
                                    </label>
                                </div>
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Save Privacy Settings
                                </button>
                            </div>
                        </form>
                    </div>
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

.profile-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
    padding: 2rem;
    margin-bottom: 2rem;
}

.card-title {
    color: #2c3e50;
    font-weight: 600;
    display: flex;
    align-items: center;
}

.card-title i {
    color: #007bff;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    color: #2c3e50;
    font-weight: 500;
    margin-bottom: 0.5rem;
}

.input-group-text {
    background-color: #f8f9fa;
    border-right: none;
    color: #6c757d;
}

.form-control {
    border-left: none;
}

.form-control:focus {
    border-color: #dee2e6;
    box-shadow: none;
}

.input-group:focus-within {
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.form-actions {
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid #eee;
}

.btn-primary {
    background: linear-gradient(45deg, #007bff, #00bcd4);
    border: none;
    padding: 0.8rem 2rem;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 123, 255, 0.3);
}

.preference-group {
    padding: 1rem 0;
}

.form-check-input {
    width: 1.2em;
    height: 1.2em;
    margin-top: 0.15em;
}

.form-check-input:checked {
    background-color: #007bff;
    border-color: #007bff;
}

.form-check-label {
    color: #2c3e50;
    font-weight: 500;
    display: flex;
    align-items: center;
    cursor: pointer;
}

.form-check-label i {
    color: #007bff;
    width: 20px;
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

    .profile-card {
        padding: 1.5rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Password form validation
    const passwordForm = document.getElementById('passwordForm');
    const newPassword = passwordForm.querySelector('input[name="new_password"]');
    const confirmPassword = passwordForm.querySelector('input[name="confirm_password"]');

    passwordForm.addEventListener('submit', function(event) {
        if (!passwordForm.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }

        if (newPassword.value !== confirmPassword.value) {
            confirmPassword.setCustomValidity('Passwords do not match');
            event.preventDefault();
            event.stopPropagation();
        } else {
            confirmPassword.setCustomValidity('');
        }

        passwordForm.classList.add('was-validated');
    });

    confirmPassword.addEventListener('input', function() {
        if (this.value === newPassword.value) {
            this.setCustomValidity('');
        }
    });

    // Notification form submission
    document.getElementById('notificationForm').addEventListener('submit', function(event) {
        event.preventDefault();
        showToast('Success', 'Notification preferences updated', 'success');
    });

    // Privacy form submission
    document.getElementById('privacyForm').addEventListener('submit', function(event) {
        event.preventDefault();
        showToast('Success', 'Privacy settings updated', 'success');
    });
});
</script>

<?php include '../includes/layouts/footer.php'; ?>