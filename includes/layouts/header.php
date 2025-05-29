<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Ryvah Books - Your Gateway to Digital Knowledge">
    <meta name="theme-color" content="#2563eb">
    <title>Ryvah Books</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/assets/images/favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="/assets/css/transitions.css">

    <style>
    :root {
        --navbar-height: 70px;
        --primary-color: #2563eb;
        --secondary-color: #64748b;
        --accent-color: #f59e0b;
        --background-color: #ffffff;
        --text-color: #1e293b;
        --transition-base: 0.2s ease;
    }

    /* Modern Navbar Styles */
    .navbar {
        background: var(--background-color);
        height: var(--navbar-height);
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        transition: all var(--transition-base);
    }

    .navbar.scrolled {
        box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
    }

    .navbar-brand {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 700;
        color: var(--text-color) !important;
        margin-right: 2rem;
    }

    .navbar-brand img {
        height: 28px;
        width: auto;
        margin-right: 0.3rem;
    }

    .navbar-brand span {
        font-size: 1.2rem;
        background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .navbar-toggler {
        border: none;
        background: transparent;
        outline: none;
        box-shadow: none !important;
        padding: 0.3rem 0.6rem;
        margin-left: 0.5rem;
        z-index: 1100;
    }

    .navbar-toggler .icon-bar,
    .navbar-toggler .icon-close {
        display: block;
        width: 28px;
        height: 3px;
        margin: 5px 0;
        background: var(--text-color);
        border-radius: 2px;
        transition: all 0.3s cubic-bezier(.4, 2, .6, 1);
    }

    .navbar-toggler .icon-close {
        width: 28px;
        height: 28px;
        background: none;
        position: relative;
    }

    .navbar-toggler .icon-close:before,
    .navbar-toggler .icon-close:after {
        content: '';
        position: absolute;
        left: 6px;
        top: 13px;
        width: 16px;
        height: 3px;
        background: var(--text-color);
        border-radius: 2px;
    }

    .navbar-toggler .icon-close:before {
        transform: rotate(45deg);
    }

    .navbar-toggler .icon-close:after {
        transform: rotate(-45deg);
    }

    .navbar .container {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    /* Desktop styles (default) */
    .navbar-collapse {
        text-align: right;
    }

    .navbar-nav {
        flex-direction: row;
        align-items: center;
        gap: 1.2rem;
        margin-right: 2rem;
    }

    .nav-link {
        padding: 0.5rem 1rem !important;
        width: auto;
        text-align: left;
        color: var(--text-color) !important;
        font-weight: 500;
        position: relative;
        transition: color var(--transition-base);
    }

    .nav-link:hover,
    .nav-link.active {
        color: var(--primary-color) !important;
    }

    .action-btn.laws-btn-animated {
        margin: 0 1.5rem 0 0;
        animation: bounceLaw 1.2s infinite alternate;
        font-weight: bold;
        font-size: 1.1rem;
        letter-spacing: 0.5px;
        box-shadow: 0 4px 16px -4px #f59e0b55;
        border: none;
        outline: none;
        background: var(--accent-color);
        color: #fff;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        border-radius: 2rem;
        padding: 0.6rem 1.6rem;
        transition: background 0.2s, box-shadow 0.2s;
    }

    .action-btn.laws-btn-animated:hover {
        background: #d97706;
        box-shadow: 0 8px 24px -6px #f59e0b99;
    }

    @keyframes bounceLaw {
        0% {
            transform: scale(1) translateY(0);
            box-shadow: 0 4px 16px -4px #f59e0b55;
        }

        60% {
            transform: scale(1.05) translateY(-4px);
            box-shadow: 0 8px 24px -6px #f59e0b99;
        }

        100% {
            transform: scale(1) translateY(0);
            box-shadow: 0 4px 16px -4px #f59e0b55;
        }
    }

    .desktop-icons {
        display: flex;
        align-items: center;
        gap: 1.2rem;
    }

    .desktop-icons .nav-icon {
        font-size: 1.4rem;
        color: var(--text-color);
        position: relative;
        transition: color 0.2s;
        padding: 0.3rem 0.5rem;
    }

    .desktop-icons .nav-icon:hover {
        color: var(--primary-color);
    }

    .desktop-icons .cart-count {
        position: absolute;
        top: -6px;
        right: -6px;
        background: var(--primary-color);
        color: white;
        font-size: 0.75rem;
        font-weight: 600;
        width: 18px;
        height: 18px;
        border-radius: 9999px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .icon-row {
        display: none;
    }

    /* Mobile Responsive */
    @media (max-width: 991.98px) {
        .navbar .container {
            flex-direction: row;
            justify-content: flex-end;
        }

        .navbar-brand {
            margin-left: auto;
            margin-right: 0;
        }

        .navbar-nav {
            flex-direction: column !important;
            align-items: flex-start !important;
            width: 100%;
            gap: 0.5rem;
            margin-right: 0;
        }

        .nav-link {
            padding: 0.75rem 1rem !important;
            width: 100%;
            text-align: left;
        }

        .action-btn.laws-btn-animated {
            width: 100%;
            justify-content: center;
            margin: 0.5rem 0;
            border-radius: 2rem;
            padding: 0.7rem 0;
        }

        .icon-row {
            display: flex !important;
            justify-content: center;
            align-items: center;
            gap: 2.5rem;
            width: 100%;
            margin-bottom: 0.5rem;
        }

        .icon-row a {
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: var(--text-color);
            transition: color 0.2s;
        }

        .icon-row a:hover {
            color: var(--primary-color);
        }

        .laws-btn-animated {
            animation: bounceLaw 1.2s infinite alternate;
            font-weight: bold;
            font-size: 1.1rem;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 16px -4px #f59e0b55;
            border: none;
            outline: none;
        }
    }

    /* Action Buttons */
    .action-btn {
        padding: 0.5rem 1.25rem;
        border-radius: 9999px;
        font-weight: 600;
        transition: all var(--transition-base);
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-primary {
        background: var(--primary-color);
        border: none;
    }

    .btn-primary:hover {
        background: #1d4ed8;
        transform: translateY(-1px);
    }

    .btn-warning {
        background: var(--accent-color);
        border: none;
        color: white;
    }

    .btn-warning:hover {
        background: #d97706;
        color: white;
        transform: translateY(-1px);
    }

    /* Cart & Profile Icons */
    .nav-icon {
        position: relative;
        padding: 0.5rem;
        color: var(--text-color);
        transition: all var(--transition-base);
    }

    .nav-icon:hover {
        color: var(--primary-color);
    }

    .cart-count {
        position: absolute;
        top: -5px;
        right: -5px;
        background: var(--primary-color);
        color: white;
        font-size: 0.75rem;
        font-weight: 600;
        width: 20px;
        height: 20px;
        border-radius: 9999px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Dropdown Menu */
    .dropdown-menu {
        border: none;
        box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1);
        border-radius: 0.75rem;
        padding: 0.5rem;
        min-width: 200px;
    }

    .dropdown-item {
        padding: 0.75rem 1rem;
        border-radius: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        transition: all var(--transition-base);
    }

    .dropdown-item:hover {
        background: rgba(37, 99, 235, 0.1);
        color: var(--primary-color);
    }

    .dropdown-item i {
        width: 1.25rem;
        text-align: center;
    }

    /* Toast Message */
    .toast {
        background: white;
        border: none;
        box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1);
        border-radius: 0.75rem;
    }

    .toast-header {
        border-bottom: none;
        padding: 1rem;
    }

    .toast-body {
        padding: 1rem;
    }
    </style>
</head>

<body class="page-enter">
    <?php
    // Include timeout modal if user is logged in
    if (isset($_SESSION['user_id'])) {
        include 'timeout_modal.php';
    }
    ?>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="../pages/index">
                <img src="/resources/logo.jpeg" alt="RYVAH">
                <span>Ryvah Books</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-label="Toggle navigation">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="../pages/index"><i class="fas fa-home"></i> Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../pages/categories"><i class="fas fa-th-large"></i> Categories</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../pages/about"><i class="fas fa-info-circle"></i> About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../pages/contact"><i class="fas fa-envelope"></i> Contact</a>
                    </li>
                </ul>
                <a href="../lawsofryvah/laws" class="btn btn-warning action-btn laws-btn-animated">
                    <i class="fas fa-gavel"></i>
                    <span>Laws of Ryvah</span>
                </a>
                <div class="desktop-icons">
                    <a href="../pages/cart" class="nav-icon">
                        <i class="fas fa-shopping-cart"></i>
                        <?php if (isset($cart_count) && $cart_count > 0): ?>
                        <span class="cart-count"><?php echo $cart_count; ?></span>
                        <?php endif; ?>
                    </a>
                    <div class="dropdown">
                        <a href="#" class="nav-icon" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <?php if (isset($_SESSION['user_id'])): ?>
                            <li><a class="dropdown-item" href="../pages/profile"><i class="fas fa-user"></i> Profile</a>
                            </li>
                            <li><a class="dropdown-item" href="../pages/orders"><i class="fas fa-shopping-bag"></i>
                                    Orders</a></li>
                            <li><a class="dropdown-item" href="../pages/wishlist"><i class="fas fa-heart"></i>
                                    Wishlist</a></li>
                            <li><a class="dropdown-item" href="../pages/settings"><i class="fas fa-cog"></i>
                                    Settings</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="../pages/logout"><i class="fas fa-sign-out-alt"></i>
                                    Logout</a></li>
                            <?php else: ?>
                            <li><a class="dropdown-item"
                                    href="../pages/login?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>">
                                    <i class="fas fa-sign-in-alt"></i> Login</a></li>
                            <li><a class="dropdown-item" href="../pages/register"><i class="fas fa-user-plus"></i>
                                    Register</a></li>
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

    <!-- Include session timeout script if user is logged in -->
    <?php if (isset($_SESSION['user_id'])): ?>
    <script src="js/session-timeout.js"></script>
    <?php endif; ?>

    <script>
    // Navbar scroll effect
    window.addEventListener('scroll', function() {
        const navbar = document.querySelector('.navbar');
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });

    // Hamburger to X toggle
    const toggler = document.querySelector('.navbar-toggler');
    const navCollapse = document.getElementById('navbarNav');

    toggler.addEventListener('click', function(e) {
        setTimeout(() => {
            if (navCollapse.classList.contains('show')) {
                toggler.innerHTML = '<span class="icon-close"></span>';
            } else {
                toggler.innerHTML =
                    '<span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span>';
            }
        }, 200);
    });

    document.addEventListener('click', function(event) {
        if (navCollapse.classList.contains('show') && !navCollapse.contains(event.target) && !toggler.contains(
                event.target)) {
            toggler.click();
        }
    });

    // Auto-hide success message
    document.addEventListener('DOMContentLoaded', function() {
        const toast = document.querySelector('.toast');
        if (toast) {
            setTimeout(() => {
                const bsToast = new bootstrap.Toast(toast);
                bsToast.hide();
            }, 5000);
        }
    });

    // Real-time cart count update
    function updateCartCount() {
        fetch('/includes/cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'action=get'
            })
            .then(response => response.json())
            .then(data => {
                // Find the cart icon specifically (not just the first .nav-icon)
                const cartIcon = document.querySelector('.nav-icon .fa-shopping-cart')?.parentElement;
                let cartCount = cartIcon ? cartIcon.querySelector('.cart-count') : null;
                if (data.items && data.items.length > 0) {
                    if (!cartCount && cartIcon) {
                        const span = document.createElement('span');
                        span.className = 'cart-count';
                        span.textContent = data.items.length;
                        cartIcon.appendChild(span);
                    } else if (cartCount) {
                        cartCount.textContent = data.items.length;
                    }
                } else if (cartCount) {
                    cartCount.remove();
                }
            });
    }

    setInterval(updateCartCount, 5000);
    document.addEventListener('DOMContentLoaded', updateCartCount);
    </script>
</body>

</html>