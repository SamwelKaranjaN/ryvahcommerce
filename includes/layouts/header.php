<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ryvah Books</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <!-- Custom Transitions -->
    <link rel="stylesheet" href="/css/transitions.css">

    <style>
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
    </style>
</head>

<body class="page-enter">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top">
        <div class="container">
            <a class="navbar-brand animate__animated animate__fadeIn" href="index">
                <i class="fas fa-book-open me-2"></i>Ryvah Books
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index' ? 'active' : ''; ?>"
                            href="index">
                            <i class="fas fa-home me-1"></i>Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'categories' ? 'active' : ''; ?>"
                            href="categories">
                            <i class="fas fa-th-large me-1"></i>Categories
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'about' ? 'active' : ''; ?>"
                            href="about">
                            <i class="fas fa-info-circle me-1"></i>About
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'contact' ? 'active' : ''; ?>"
                            href="contact">
                            <i class="fas fa-envelope me-1"></i>Contact
                        </a>
                    </li>
                </ul>
                <div class="d-flex align-items-center">
                    <a href="cart" class="nav-icon position-relative">
                        <i class="fas fa-shopping-cart"></i>
                        <?php
                        require_once dirname(__DIR__) . '/cart.php';
                        $cart_data = getCartItems();
                        $cart_count = count($cart_data['items']);
                        if ($cart_count > 0):
                        ?>
                        <span class="cart-count"><?php echo $cart_count; ?></span>
                        <?php endif; ?>
                    </a>
                    <div class="dropdown">
                        <a href="#" class="nav-icon" title="Profile" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i>My
                                    Profile</a></li>
                            <li><a class="dropdown-item" href="orders.php"><i class="fas fa-shopping-bag me-2"></i>My
                                    Orders</a></li>
                            <li><a class="dropdown-item" href="wishlist.php"><i
                                        class="fas fa-heart me-2"></i>Wishlist</a></li>
                            <li><a class="dropdown-item" href="settings.php"><i class="fas fa-cog me-2"></i>Settings</a>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="logout.php"><i
                                        class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Add padding to body to account for fixed navbar -->
    <div style="padding-top: 76px;"></div>

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

    // Update cart count via AJAX
    function updateCartCount() {
        fetch('../includes/cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=get'
            })
            .then(response => response.json())
            .then(data => {
                const cartCount = document.querySelector('.cart-count');
                if (data.items.length > 0) {
                    if (!cartCount) {
                        const span = document.createElement('span');
                        span.className = 'cart-count';
                        span.textContent = data.items.length;
                        document.querySelector('.nav-icon.position-relative').appendChild(span);
                    } else {
                        cartCount.textContent = data.items.length;
                    }
                } else if (cartCount) {
                    cartCount.remove();
                }
            });
    }

    // Update cart count every 5 seconds
    setInterval(updateCartCount, 5000);
    </script>
</body>

</html>