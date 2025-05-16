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
    <title><?php echo SITE_NAME; ?> - Checkout</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../assets/css/main.css" rel="stylesheet">
    <?php if ($current_page === 'checkout'): ?>
    <link href="../assets/css/checkout.css" rel="stylesheet">
    <?php endif; ?>

    <style>
    :root {
        --shadow-md: 0 2px 10px rgba(0, 0, 0, 0.1);
        --shadow-lg: 0 4px 15px rgba(0, 0, 0, 0.1);
        --transition-normal: 0.3s;
        --transition-smooth: ease;
        --transition-bounce: cubic-bezier(0.68, -0.55, 0.265, 1.55);
        --animation-normal: 0.3s;
    }

    .navbar {
        background-color: rgba(255, 255, 255, 0.98);
        box-shadow: var(--shadow-md);
        transition: all var(--transition-normal) var(--transition-smooth);
    }

    .navbar.scrolled {
        background-color: white;
        box-shadow: var(--shadow-lg);
    }

    .navbar-brand {
        font-weight: bold;
        color: #007bff !important;
        font-size: 1.5rem;
        transition: all var(--transition-normal) var(--transition-bounce);
    }

    .navbar-brand:hover {
        transform: translateY(-2px);
    }

    .navbar-brand i {
        transition: transform var(--transition-normal) var(--transition-bounce);
    }

    .navbar-brand:hover i {
        transform: rotate(-15deg);
    }

    .nav-link {
        color: #333 !important;
        font-weight: 500;
        padding: 0.5rem 1rem !important;
        transition: all var(--transition-normal) var(--transition-smooth);
        position: relative;
    }

    .nav-link:after {
        content: '';
        position: absolute;
        width: 0;
        height: 2px;
        bottom: 0;
        left: 50%;
        background-color: #007bff;
        transition: all var(--transition-normal) var(--transition-smooth);
        transform: translateX(-50%);
    }

    .nav-link:hover:after {
        width: 80%;
    }

    .nav-link:hover {
        color: #007bff !important;
        transform: translateY(-2px);
    }

    .nav-link.active {
        color: #007bff !important;
    }

    .nav-link.active:after {
        width: 80%;
    }

    .navbar-toggler {
        border: none;
        padding: 0.5rem;
        transition: all var(--transition-normal) var(--transition-bounce);
    }

    .navbar-toggler:focus {
        box-shadow: none;
    }

    .navbar-toggler:hover {
        transform: rotate(90deg);
    }

    .nav-icon {
        font-size: 1.2rem;
        margin-left: 1.5rem;
        color: #333;
        transition: all var(--transition-normal) var(--transition-smooth);
        position: relative;
        padding: 0.5rem;
    }

    .nav-icon:hover {
        color: #007bff;
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

    .cart-count {
        position: absolute;
        top: -5px;
        right: -5px;
        background: #007bff;
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

    @media (max-width: 991.98px) {
        .navbar-collapse {
            background: white;
            padding: 1.5rem;
            border-radius: 1rem;
            box-shadow: var(--shadow-lg);
            margin-top: 1rem;
            animation: slideDown var(--animation-normal) var(--transition-bounce);
        }

        .nav-icon {
            margin: 0.5rem 0;
            display: inline-block;
        }

        .nav-link {
            padding: 0.8rem 0 !important;
        }

        .nav-link:after {
            display: none;
        }
    }

    /* Checkout-specific styles */
    .checkout-header {
        background: linear-gradient(45deg, #2c3e50, #3498db);
        color: white;
        padding: 3rem 0;
        margin-bottom: 2rem;
        position: relative;
        overflow: hidden;
    }

    .checkout-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(45deg, rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.4));
    }

    .checkout-header .container {
        position: relative;
        z-index: 1;
    }

    .checkout-header h1 {
        font-size: 2.5rem;
        font-weight: 800;
        margin-bottom: 1.5rem;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
    }

    .checkout-steps {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 2rem;
        margin-top: 1rem;
    }

    .step {
        display: flex;
        align-items: center;
        color: rgba(255, 255, 255, 0.8);
        transition: all var(--transition-normal) var(--transition-smooth);
    }

    .step.active {
        color: #ffd700;
        font-weight: 600;
    }

    .step-number {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 0.5rem;
        font-size: 1rem;
        transition: all var(--transition-normal) var(--transition-smooth);
    }

    .step.active .step-number {
        background: #ffd700;
        color: #2c3e50;
        transform: scale(1.1);
    }

    .step-connector {
        width: 60px;
        height: 2px;
        background: rgba(255, 255, 255, 0.2);
        transition: all var(--transition-normal) var(--transition-smooth);
    }

    .step.active+.step-connector {
        background: #ffd700;
    }

    @media (max-width: 768px) {
        .checkout-steps {
            flex-direction: column;
            gap: 1rem;
        }

        .step-connector {
            width: 2px;
            height: 30px;
        }

        .checkout-header h1 {
            font-size: 2rem;
        }
    }

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
            <a class="navbar-brand animate__animated animate__fadeIn" href="../pages/index">
                <i class="fas fa-book-open me-2"></i>Ryvah Books
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../pages/index">
                            <i class="fas fa-home me-1"></i>Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../pages/categories">
                            <i class="fas fa-th-large me-1"></i>Categories
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../pages/about">
                            <i class="fas fa-info-circle me-1"></i>About
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../pages/contact">
                            <i class="fas fa-envelope me-1"></i>Contact
                        </a>
                    </li>
                </ul>
                <div class="d-flex align-items-center">
                    <a href="../pages/cart" class="nav-icon position-relative">
                        <i class="fas fa-shopping-cart"></i>
                    </a>
                    <div class="dropdown">
                        <a href="#" class="nav-icon" title="Profile" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <?php if (isset($_SESSION['user_id'])): ?>
                            <li><a class="dropdown-item" href="../pages/profile"><i class="fas fa-user me-2"></i>My
                                    Profile</a></li>
                            <li><a class="dropdown-item" href="../pages/orders"><i
                                        class="fas fa-shopping-bag me-2"></i>My Orders</a></li>
                            <li><a class="dropdown-item" href="../pages/wishlist"><i
                                        class="fas fa-heart me-2"></i>Wishlist</a></li>
                            <li><a class="dropdown-item" href="../pages/settings"><i
                                        class="fas fa-cog me-2"></i>Settings</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="../pages/logout"><i
                                        class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                            <?php else: ?>
                            <li><a class="dropdown-item" href="../pages/login"><i
                                        class="fas fa-sign-in-alt me-2"></i>Login</a></li>
                            <li><a class="dropdown-item" href="../pages/register"><i
                                        class="fas fa-user-plus me-2"></i>Register</a></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Add padding to body to account for fixed navbar -->
    <div style="padding-top: 76px;"></div>

    <!-- Checkout Header -->
    <header class="checkout-header">
        <div class="container">
            <h1 class="text-center mb-0 animate__animated animate__fadeInDown">Checkout</h1>
            <div class="checkout-steps animate__animated animate__fadeInUp animate__delay-1s">
                <div class="step active">
                    <div class="step-number">1</div>
                    <span>Shipping</span>
                </div>
                <div class="step-connector"></div>
                <div class="step">
                    <div class="step-number">2</div>
                    <span>Payment</span>
                </div>
                <div class="step-connector"></div>
                <div class="step">
                    <div class="step-number">3</div>
                    <span>Confirmation</span>
                </div>
            </div>
        </div>
    </header>

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
    </script>
</body>

</html>