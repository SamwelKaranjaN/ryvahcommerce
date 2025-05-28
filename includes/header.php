<?php
require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/../config/database.php';

// Get current page
$current_page = basename($_SERVER['PHP_SELF']);

// Get cart count
$cart_count = 0;
if (isLoggedIn()) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $cart_count = $row['count'];
    closeDBConnection($conn);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?></title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="assets/css/main.css" rel="stylesheet">
    <?php if ($current_page === 'login' || $current_page === 'signup'): ?>
    <link href="assets/css/auth.css" rel="stylesheet">
    <?php elseif ($current_page === 'cart'): ?>
    <link href="assets/css/cart.css" rel="stylesheet">
    <?php elseif ($current_page === 'cheout/ckcheckout'): ?>
    <link href="assets/css/checkout.css" rel="stylesheet">
    <?php elseif ($current_page === 'contact'): ?>
    <link href="assets/css/contact.css" rel="stylesheet">
    <?php elseif ($current_page === 'edit_profile'): ?>
    <link href="assets/css/profile.css" rel="stylesheet">
    <?php elseif ($current_page === 'purchases'): ?>
    <link href="assets/css/purchases.css" rel="stylesheet">
    <?php endif; ?>
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="index"><?php echo SITE_NAME; ?></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index' ? 'active' : ''; ?>"
                            href="index">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'categories' ? 'active' : ''; ?>"
                            href="categories">Products</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'about' ? 'active' : ''; ?>"
                            href="about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'contact' ? 'active' : ''; ?>"
                            href="contact">Contact</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <?php if (isLoggedIn()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="cart">
                            <i class="fas fa-shopping-cart"></i>
                            <?php if ($cart_count > 0): ?>
                            <span class="badge bg-primary cart-count-badge"><?php echo $cart_count; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                            data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i>
                            <?php if (isset($_SESSION['user']['fullname'])): ?>
                            <?php echo htmlspecialchars($_SESSION['user']['fullname']); ?>
                            <?php endif; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="purchases">My Purchases</a></li>
                            <li><a class="dropdown-item" href="edit_profile">Edit Profile</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="logout">Logout</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>pages/orders">Orders</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>pages/pending_payments">Pending
                            Payments</a>
                    </li>
                    <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="signup">Sign Up</a>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container py-4">
        <?php
        $flash_message = getFlashMessage();
        if ($flash_message): ?>
        <div class="alert alert-<?php echo $flash_message['type']; ?> alert-dismissible fade show">
            <?php echo $flash_message['message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>