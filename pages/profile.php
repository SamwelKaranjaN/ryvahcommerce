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

<<<<<<< Updated upstream
<div class="profile-container">
    <div class="container-fluid">
=======
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Ryvah Commerce</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .profile-header {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }

        @media (max-width: 768px) {
            .profile-header {
                padding: 1.5rem 0;
            }

            .profile-avatar {
                width: 80px;
                height: 80px;
                font-size: 2rem;
                margin-bottom: 1rem;
            }
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 4px solid white;
            background-color: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: #6c757d;
            margin: 0 auto 1rem;
        }

        .nav-pills .nav-link {
            color: #495057;
            border-radius: 0.5rem;
            padding: 0.75rem 1.25rem;
            margin-bottom: 0.5rem;
        }

        .nav-pills .nav-link.active {
            background-color: #0d6efd;
            color: white;
        }

        .nav-pills .nav-link i {
            width: 24px;
            text-align: center;
            margin-right: 0.5rem;
        }

        .card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            margin-bottom: 1.5rem;
        }

        .card-header {
            background-color: white;
            border-bottom: 1px solid rgba(0, 0, 0, 0.125);
            padding: 1.25rem;
            border-radius: 1rem 1rem 0 0 !important;
        }

        .order-card {
            transition: transform 0.2s;
        }

        .order-card:hover {
            transform: translateY(-2px);
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            font-weight: 500;
            display: inline-block;
            margin-bottom: 0.5rem;
        }

        .status-completed {
            background-color: #d1e7dd;
            color: #0f5132;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-cancelled {
            background-color: #f8d7da;
            color: #842029;
        }

        .form-control {
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
        }

        .btn-primary {
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
        }

        @media (max-width: 768px) {
            .order-card .row {
                flex-direction: column;
            }

            .order-card .col-md-3 {
                margin-bottom: 1rem;
                text-align: left !important;
            }

            .order-card .text-end {
                text-align: left !important;
            }

            .nav-pills {
                margin-bottom: 1rem;
            }

            .card {
                margin-bottom: 1rem;
            }

            .btn {
                width: 100%;
                margin-bottom: 0.5rem;
            }
        }

        @media (max-width: 576px) {
            .profile-header h1 {
                font-size: 1.5rem;
            }

            .card-header {
                padding: 1rem;
            }

            .form-control {
                padding: 0.5rem 0.75rem;
            }
        }
    </style>
</head>

<body>
    <?php include '../includes/layouts/header.php'; ?>

    <div class="profile-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-2 text-center">
                    <div class="profile-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                </div>
                <div class="col-md-10">
                    <h1 class="mb-2"><?php echo htmlspecialchars($user['full_name']); ?></h1>
                    <p class="mb-0">
                        <i class="fas fa-envelope me-2"></i><?php echo htmlspecialchars($user['email']); ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="container py-4">
>>>>>>> Stashed changes
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
                        <a href="profile.php" class="nav-item active">
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
<<<<<<< Updated upstream
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
                        <h2>Profile Information</h2>
                        <p class="text-muted">Update your personal information and preferences</p>
=======
            <div class="col-md-9">
                <div class="tab-content">
                    <!-- Orders Tab -->
                    <div class="tab-pane fade show active" id="orders">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Order History</h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($orders)): ?>
                                    <div class="text-center py-4">
                                        <i class="fas fa-shopping-bag fa-3x text-muted mb-3"></i>
                                        <h5>No Orders Yet</h5>
                                        <p class="text-muted">Start shopping to see your orders here</p>
                                        <a href="../index.php" class="btn btn-primary">Start Shopping</a>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($orders as $order): ?>
                                        <div class="card order-card mb-3">
                                            <div class="card-body">
                                                <div class="row align-items-center">
                                                    <div class="col-md-3">
                                                        <h6 class="mb-1">Order #<?php echo $order['id']; ?></h6>
                                                        <small class="text-muted">
                                                            <?php echo date('M d, Y', strtotime($order['created_at'])); ?>
                                                        </small>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <span
                                                            class="status-badge status-<?php echo strtolower($order['payment_status'] ?? 'pending'); ?>">
                                                            <?php echo ucfirst($order['payment_status'] ?? 'pending'); ?>
                                                        </span>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <h6 class="mb-0">
                                                            $<?php echo number_format($order['total_amount'] ?? 0, 2); ?></h6>
                                                        <small
                                                            class="text-muted"><?php echo ucfirst($order['payment_method'] ?? 'unknown'); ?></small>
                                                    </div>
                                                    <div class="col-md-3 text-end">
                                                        <a href="order_details.php?id=<?php echo $order['id']; ?>"
                                                            class="btn btn-outline-primary btn-sm">
                                                            View Details
                                                        </a>
                                                        <?php if ($order['invoice_number']): ?>
                                                            <a href="../invoices/<?php echo $order['invoice_number']; ?>.html"
                                                                class="btn btn-outline-secondary btn-sm" target="_blank">
                                                                <i class="fas fa-file-invoice"></i> Invoice
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
>>>>>>> Stashed changes
                    </div>

                    <div class="profile-card">
                        <form action="update_profile.php" method="POST" id="profileForm" class="needs-validation"
                            novalidate>
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Full Name</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                                            <input type="text" class="form-control" name="full_name"
                                                value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                                        </div>
                                        <div class="invalid-feedback">Please enter your full name.</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Email</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                            <input type="email" class="form-control"
                                                value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Phone</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                            <input type="tel" class="form-control" name="phone"
                                                value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                                                pattern="[0-9]{10}" required>
                                        </div>
                                        <div class="invalid-feedback">Please enter a valid 10-digit phone number.</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Address</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                            <textarea class="form-control" name="address" rows="1"
                                                required><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                                        </div>
                                        <div class="invalid-feedback">Please enter your address.</div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Save Changes
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="profile-stats">
                        <div class="row g-4">
                            <div class="col-md-4">
                                <div class="stat-card">
                                    <div class="stat-icon">
                                        <i class="fas fa-shopping-bag"></i>
                                    </div>
                                    <div class="stat-info">
                                        <h3>0</h3>
                                        <p>Total Orders</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="stat-card">
                                    <div class="stat-icon">
                                        <i class="fas fa-heart"></i>
                                    </div>
                                    <div class="stat-info">
                                        <h3>0</h3>
                                        <p>Wishlist Items</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="stat-card">
                                    <div class="stat-icon">
                                        <i class="fas fa-star"></i>
                                    </div>
                                    <div class="stat-info">
                                        <h3>0</h3>
                                        <p>Reviews</p>
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
.profile-container {
    min-height: 100vh;
    background: #f8f9fa;
    padding: 2rem 0;
}

<<<<<<< Updated upstream
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

.profile-stats {
    margin-top: 2rem;
}

.stat-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1.5rem;
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.stat-icon {
    width: 50px;
    height: 50px;
    background: linear-gradient(45deg, #007bff, #00bcd4);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.stat-icon i {
    font-size: 1.5rem;
    color: white;
}

.stat-info h3 {
    color: #2c3e50;
    font-weight: 600;
    margin: 0;
    font-size: 1.5rem;
}

.stat-info p {
    color: #6c757d;
    margin: 0;
    font-size: 0.9rem;
}

@media (max-width: 991.98px) {
    .profile-sidebar {
        margin-bottom: 2rem;
    }

    .profile-content {
        padding: 0;
    }
}
=======
    <?php include '../includes/layouts/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Handle form submissions with AJAX
        document.getElementById('profile-form').addEventListener('submit', function(e) {
            e.preventDefault();
            // Add AJAX submission logic
        });

        document.getElementById('password-form').addEventListener('submit', function(e) {
            e.preventDefault();
            // Add AJAX submission logic
        });

        document.getElementById('address-form').addEventListener('submit', function(e) {
            e.preventDefault();
            // Add AJAX submission logic
        });

        // Load addresses
        function loadAddresses() {
            fetch('get_addresses.php')
                .then(response => response.json())
                .then(data => {
                    const addressList = document.getElementById('address-list');
                    // Add address rendering logic
                });
        }

        // Load addresses on page load
        loadAddresses();
    </script>
</body>
>>>>>>> Stashed changes

@media (max-width: 767.98px) {
    .profile-container {
        padding: 1rem;
    }

    .stat-card {
        margin-bottom: 1rem;
    }
}
</style>

<script>
// Form validation
(function() {
    'use strict';
    const form = document.getElementById('profileForm');

    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        form.classList.add('was-validated');
    });
})();
</script>

<?php include '../includes/layouts/footer.php'; ?>