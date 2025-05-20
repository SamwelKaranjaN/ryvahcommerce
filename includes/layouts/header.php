<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Ryvah Books - Your Gateway to Digital Knowledge">
    <meta name="theme-color" content="#007bff">
    <title>Ryvah Books</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/assets/images/favicon.png">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="/assets/css/transitions.css">

    <style>
        :root {
            --navbar-height: 76px;
            --primary-color: #007bff;
            --secondary-color: #6c757d;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
            --transition-normal: 0.3s;
            --transition-smooth: cubic-bezier(0.4, 0, 0.2, 1);
            --transition-bounce: cubic-bezier(0.68, -0.55, 0.265, 1.55);
            --shadow-sm: 0 2px 4px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.1);
        }

        /* Navbar Styles */
        .navbar {
            background-color: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            box-shadow: var(--shadow-md);
            transition: all var(--transition-normal) var(--transition-smooth);
            height: var(--navbar-height);
        }

        .navbar.scrolled {
            background-color: white;
            box-shadow: var(--shadow-lg);
        }

        .navbar-brand {
            font-weight: 800;
            color: var(--primary-color) !important;
            font-size: 1.5rem;
            transition: all var(--transition-normal) var(--transition-bounce);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .navbar-brand:hover {
            transform: translateY(-2px);
        }

        .navbar-brand i {
            transition: transform var(--transition-normal) var(--transition-bounce);
            font-size: 1.8rem;
        }

        .navbar-brand:hover i {
            transform: rotate(-15deg);
        }

        .nav-link {
            color: var(--dark-color) !important;
            font-weight: 500;
            padding: 0.5rem 1rem !important;
            transition: all var(--transition-normal) var(--transition-smooth);
            position: relative;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .nav-link i {
            font-size: 1.1rem;
            transition: transform var(--transition-normal) var(--transition-bounce);
        }

        .nav-link:hover i {
            transform: translateY(-2px);
        }

        .nav-link:after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 50%;
            background-color: var(--primary-color);
            transition: all var(--transition-normal) var(--transition-smooth);
            transform: translateX(-50%);
        }

        .nav-link:hover:after {
            width: 80%;
        }

        .nav-link:hover {
            color: var(--primary-color) !important;
            transform: translateY(-2px);
        }

        .nav-link.active {
            color: var(--primary-color) !important;
        }

        .nav-link.active:after {
            width: 80%;
        }

        /* Nav Icons */
        .nav-icon {
            font-size: 1.2rem;
            margin-left: 1.5rem;
            color: var(--dark-color);
            transition: all var(--transition-normal) var(--transition-smooth);
            position: relative;
            padding: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .nav-icon:hover {
            color: var(--primary-color);
            transform: translateY(-2px);
        }

        .nav-icon:after {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: rgba(0, 123, 255, 0.1);
            transform: scale(0);
            transition: transform var(--transition-normal) var(--transition-bounce);
            z-index: -1;
        }

        .nav-icon:hover:after {
            transform: scale(1.5);
        }

        /* Cart Badge */
        .cart-count {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--primary-color);
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all var(--transition-normal) var(--transition-bounce);
            animation: pulse 2s infinite;
        }

        /* Checkout Button */
        .checkout-btn {
            position: relative;
            overflow: hidden;
            transition: all var(--transition-normal) var(--transition-smooth);
            z-index: 1000;
            box-shadow: var(--shadow-md);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
            padding: 0.5rem 1.2rem;
            border-radius: 50px;
        }

        .checkout-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .checkout-btn::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0.2));
            transform: translateX(-100%);
            transition: transform 0.6s ease;
        }

        .checkout-btn:hover::after {
            transform: translateX(100%);
        }

        /* Dropdown Menu */
        .dropdown-menu {
            border: none;
            box-shadow: var(--shadow-lg);
            border-radius: 1rem;
            padding: 0.5rem;
            animation: slideDown 0.3s var(--transition-bounce);
        }

        .dropdown-item {
            border-radius: 0.5rem;
            padding: 0.7rem 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all var(--transition-normal) var(--transition-smooth);
        }

        .dropdown-item:hover {
            background-color: rgba(0, 123, 255, 0.1);
            transform: translateX(5px);
        }

        .dropdown-item i {
            width: 1.2rem;
            text-align: center;
        }

        /* Toast Message */
        .toast {
            background: white;
            border-radius: 1rem;
            box-shadow: var(--shadow-lg);
            animation: slideIn 0.3s var(--transition-bounce);
            overflow: hidden;
        }

        .toast-header {
            border-top-left-radius: 1rem;
            border-top-right-radius: 1rem;
            padding: 0.8rem 1rem;
        }

        .toast-body {
            padding: 1rem;
            font-size: 0.95rem;
        }

        /* Responsive Styles */
        @media (max-width: 991.98px) {
            .navbar-collapse {
                background: white;
                padding: 1.5rem;
                border-radius: 1rem;
                box-shadow: var(--shadow-lg);
                margin-top: 1rem;
                animation: slideDown var(--transition-normal) var(--transition-bounce);
            }

            .nav-icon {
                margin: 0.5rem 0;
            }

            .nav-link {
                padding: 0.8rem 0 !important;
            }

            .nav-link:after {
                display: none;
            }

            .navbar-nav {
                flex-direction: column;
                align-items: flex-start;
                width: 100%;
            }

            .nav-item {
                width: 100%;
                margin: 0.2rem 0;
            }

            .nav-link,
            .btn-warning {
                width: 100%;
                text-align: left;
                font-size: 1.1rem;
                padding: 0.8rem 1rem !important;
            }

            .nav-item.d-flex {
                justify-content: center !important;
                margin: 0.5rem 0;
            }

            .btn-warning {
                font-size: 1rem;
                margin: 0.5rem 0;
            }

            .d-flex.align-items-center {
                flex-direction: row;
                width: 100%;
                justify-content: flex-end;
            }

            .checkout-btn {
                margin: 0.5rem 0;
                width: 100%;
            }
        }

        @media (max-width: 576px) {
            .navbar-brand {
                font-size: 1.1rem;
            }

            .btn-warning {
                font-size: 0.95rem;
                padding: 0.5rem 0.7rem;
            }

            .nav-link,
            .btn-warning {
                font-size: 1rem;
                padding: 0.7rem 0.8rem !important;
            }
        }

        /* Animations */
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }

            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.1);
            }

            100% {
                transform: scale(1);
            }
        }
    </style>
</head>

<body class="page-enter">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top">
        <div class="container">
            <a class="navbar-brand animate__animated animate__fadeIn" href="index">
                <i class="fas fa-book-open"></i>
                <span>Ryvah Books</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index' ? 'active' : ''; ?>"
                            href="index">
                            <i class="fas fa-home"></i>
                            <span>Home</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'categories' ? 'active' : ''; ?>"
                            href="categories">
                            <i class="fas fa-th-large"></i>
                            <span>Categories</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'about' ? 'active' : ''; ?>"
                            href="about">
                            <i class="fas fa-info-circle"></i>
                            <span>About</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'contact' ? 'active' : ''; ?>"
                            href="contact">
                            <i class="fas fa-envelope"></i>
                            <span>Contact</span>
                        </a>
                    </li>
                    <li class="nav-item d-flex align-items-center justify-content-center">
                        <a href="/ryvahcommerce/lawsofryvah/laws"
                            class="btn btn-warning fw-bold text-uppercase mx-3 animate__animated animate__pulse animate__infinite">
                            <i class="fas fa-gavel me-2"></i>
                            <span>READ THE LAWS OF RYVAH</span>
                        </a>
                    </li>
                </ul>
                <div class="d-flex align-items-center">
                    <?php
                    try {
                        require_once dirname(__DIR__) . '/cart.php';
                        $cart_data = getCartItems();
                        $cart_items = $cart_data['items'];
                        $cart_count = count($cart_items);
                    ?>
                        <a href="cart" class="nav-icon position-relative">
                            <i class="fas fa-shopping-cart"></i>
                            <?php if ($cart_count > 0): ?>
                                <span class="cart-count"><?php echo $cart_count; ?></span>
                            <?php endif; ?>
                        </a>
                    <?php } catch (Exception $e) {
                        error_log("Error in header.php: " . $e->getMessage());
                    } ?>
                    <div class="dropdown"> <a href="#" class="nav-icon" title="Profile" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <li><a class="dropdown-item" href="profile.php">
                                        <i class="fas fa-user"></i>
                                        <span>My Profile</span>
                                    </a></li>
                                <li><a class="dropdown-item" href="orders.php">
                                        <i class="fas fa-shopping-bag"></i>
                                        <span>My Orders</span>
                                    </a></li>
                                <li><a class="dropdown-item" href="wishlist.php">
                                        <i class="fas fa-heart"></i>
                                        <span>Wishlist</span>
                                    </a></li>
                                <li><a class="dropdown-item" href="settings.php">
                                        <i class="fas fa-cog"></i>
                                        <span>Settings</span>
                                    </a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="logout.php">
                                        <i class="fas fa-sign-out-alt"></i>
                                        <span>Logout</span>
                                    </a></li>
                            <?php else: ?>
                                <li><a class="dropdown-item"
                                        href="login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>">
                                        <i class="fas fa-sign-in-alt"></i>
                                        <span>Login</span>
                                    </a></li>
                                <li><a class="dropdown-item" href="register.php">
                                        <i class="fas fa-user-plus"></i>
                                        <span>Register</span>
                                    </a></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Add padding to body to account for fixed navbar -->
    <div style="padding-top: var(--navbar-height);"></div>

    <!-- Success Message Toast -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="position-fixed top-0 end-0 p-3" style="z-index: 1050">
            <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header bg-success text-white">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong class="me-auto">Success</strong>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"
                        aria-label="Close"></button>
                </div>
                <div class="toast-body">
                    <?php
                    echo htmlspecialchars($_SESSION['success_message']);
                    unset($_SESSION['success_message']);
                    ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Add scroll effect to navbar
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Auto-hide success message after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const toast = document.querySelector('.toast');
            if (toast) {
                setTimeout(() => {
                    const bsToast = new bootstrap.Toast(toast);
                    bsToast.hide();
                }, 5000);
            }
        });

        // Update cart count via AJAX
        function updateCartCount() {
            fetch('../includes/cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=get'
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    const cartCount = document.querySelector('.cart-count');
                    const checkoutBtn = document.querySelector('.checkout-btn');
                    const overlay = document.getElementById('checkout-overlay');
                    const overlayTotal = document.getElementById('overlay-cart-total');

                    if (data.items && data.items.length > 0) {
                        // Update cart count
                        if (!cartCount) {
                            const span = document.createElement('span');
                            span.className = 'cart-count';
                            span.textContent = data.items.length;
                            document.querySelector('.nav-icon.position-relative').appendChild(span);
                        } else {
                            cartCount.textContent = data.items.length;
                        }

                        // Show checkout button if not present
                        if (!checkoutBtn) {
                            const btn = document.createElement('a');
                            btn.href = 'checkout';
                            btn.className = 'btn btn-primary me-3 checkout-btn';
                            btn.innerHTML = '<i class="fas fa-lock"></i><span>Checkout</span>';
                            document.querySelector('.d-flex.align-items-center').insertBefore(
                                btn,
                                document.querySelector('.nav-icon.position-relative')
                            );
                        }

                        // Update checkout overlay
                        if (overlay && overlayTotal) {
                            let total = 0;
                            data.items.forEach(item => {
                                total += item.price * item.quantity;
                            });
                            overlayTotal.textContent = '$' + total.toFixed(2);
                            overlay.style.display = 'flex';
                        }
                    } else {
                        // Remove cart count and checkout button
                        if (cartCount) cartCount.remove();
                        if (checkoutBtn) checkoutBtn.remove();
                        if (overlay) overlay.style.display = 'none';
                    }
                })
                .catch(error => {
                    console.error('Error updating cart:', error);
                });
        }

        // Update cart count every 5 seconds
        setInterval(updateCartCount, 5000);

        // Initial cart count update
        document.addEventListener('DOMContentLoaded', function() {
            updateCartCount();
        });
    </script>
</body>

</html>