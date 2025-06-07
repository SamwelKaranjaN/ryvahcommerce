<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include session management functions
require_once 'php/session_check.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ryvah</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
    /* Base Styles */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Poppins', sans-serif;
    }

    body {
        min-height: 100vh;
        background: #f0f2f5;
    }

    /* Topnav Styles */
    .topnav {
        background: linear-gradient(90deg, #2c3e50, #3498db);
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 20px;
        position: fixed;
        width: 100%;
        height: 60px;
        top: 0;
        z-index: 1000;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
    }

    .topnav .toggle-btn {
        padding: 8px;
        cursor: pointer;
        font-size: 1.3em;
        color: #ffffff;
        display: none;
        transition: all 0.3s ease;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .topnav .toggle-btn:hover {
        background: rgba(255, 255, 255, 0.1);
        color: #f1c40f;
    }

    .topnav .logo {
        font-size: 1.4em;
        font-weight: 600;
        color: #ffffff;
        display: flex;
        align-items: center;
        gap: 15px;
        text-decoration: none;
        transition: opacity 0.3s ease;
    }

    .topnav .logo:hover {
        opacity: 0.9;
    }

    .topnav .actions {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .topnav .nav-links {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .topnav .nav-links a {
        color: #ffffff;
        text-decoration: none;
        font-size: 0.9em;
        font-weight: 500;
        position: relative;
        transition: all 0.3s ease;
        padding: 8px 12px;
        border-radius: 4px;
    }

    .topnav .nav-links a:hover {
        background: rgba(255, 255, 255, 0.1);
        color: #f1c40f;
    }

    .topnav .user-profile {
        position: relative;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 15px;
        border-radius: 20px;
        transition: all 0.3s ease;
        background: rgba(255, 255, 255, 0.1);
    }

    .topnav .user-profile:hover {
        background: rgba(255, 255, 255, 0.2);
    }

    .topnav .user-profile #username {
        font-size: 0.95em;
        font-weight: 500;
        color: #ffffff;
    }

    .topnav .user-profile .dropdown {
        display: none;
        position: absolute;
        top: 100%;
        right: 0;
        background: #ffffff;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        min-width: 200px;
        margin-top: 8px;
        z-index: 1001;
        opacity: 0;
        transform: translateY(-10px);
        transition: all 0.3s ease;
    }

    .topnav .user-profile:hover .dropdown {
        display: block;
        opacity: 1;
        transform: translateY(0);
    }

    .topnav .dropdown a {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 15px;
        color: #2c3e50;
        text-decoration: none;
        font-size: 0.9em;
        transition: all 0.3s ease;
    }

    .topnav .dropdown a:hover {
        background: #3498db;
        color: #ffffff;
    }

    .topnav .dropdown a i {
        font-size: 1.1em;
        width: 20px;
        text-align: center;
    }

    /* Sidenav Styles */
    .sidenav {
        width: 260px;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        height: calc(100vh - 60px);
        position: fixed;
        top: 60px;
        left: 0;
        overflow-y: auto;
        transition: all 0.3s ease;
        box-shadow: 2px 0 8px rgba(0, 0, 0, 0.1);
        z-index: 999;
    }

    .sidenav.collapsed {
        width: 60px;
    }

    .sidenav.collapsed .nav-text,
    .sidenav.collapsed .submenu {
        display: none;
    }

    .sidenav a {
        display: flex;
        align-items: center;
        padding: 12px 20px;
        color: #2c3e50;
        text-decoration: none;
        font-size: 0.95em;
        position: relative;
        transition: all 0.3s ease;
        border-radius: 4px;
        margin: 4px 8px;
    }

    .sidenav a i {
        margin-right: 12px;
        font-size: 1.1em;
        width: 20px;
        text-align: center;
    }

    .sidenav a:hover,
    .sidenav a.active {
        background: rgba(52, 152, 219, 0.1);
        color: #3498db;
    }

    .sidenav .submenu {
        display: none;
        padding-left: 20px;
        background: rgba(0, 0, 0, 0.02);
        border-radius: 4px;
        margin: 0 8px;
    }

    .sidenav .submenu.active {
        display: block;
    }

    .sidenav .submenu a {
        padding: 10px 20px;
        font-size: 0.9em;
    }

    .sidenav a .badge {
        background: #e74c3c;
        color: #ffffff;
        padding: 2px 6px;
        border-radius: 10px;
        font-size: 0.75em;
        margin-left: auto;
    }

    /* Responsive Styles */
    @media (max-width: 992px) {
        .topnav .nav-links {
            display: none;
        }

        .topnav .toggle-btn {
            display: flex;
        }

        .sidenav {
            transform: translateX(-100%);
        }

        .sidenav.active {
            transform: translateX(0);
        }

        .main-content {
            margin-left: 0 !important;
        }
    }

    @media (max-width: 768px) {
        .topnav .logo {
            font-size: 1.3em;
        }

        .topnav .user-profile #username {
            display: none;
        }

        .topnav .user-profile {
            padding: 8px;
        }

        .sidenav {
            width: 100%;
            max-width: 280px;
        }
    }

    @media (max-width: 480px) {
        .topnav {
            padding: 0 10px;
        }

        .topnav .logo {
            font-size: 1.2em;
        }

        .topnav .user-profile .dropdown {
            position: fixed;
            top: 60px;
            left: 0;
            right: 0;
            width: 100%;
            border-radius: 0;
            margin-top: 0;
        }
    }

    /* Animation */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .fade-in {
        animation: fadeIn 0.3s ease forwards;
    }

    /* Updated Sidenav Styles */
    .nav-section {
        padding: 15px 0;
        border-bottom: 1px solid rgba(0, 0, 0, 0.1);
    }

    .nav-section:last-child {
        border-bottom: none;
    }

    .nav-section h3 {
        padding: 0 20px;
        font-size: 0.8em;
        text-transform: uppercase;
        color: #7f8c8d;
        margin-bottom: 10px;
        letter-spacing: 1px;
    }

    .nav-item {
        display: flex;
        align-items: center;
        padding: 12px 20px;
        color: #2c3e50;
        text-decoration: none;
        transition: all 0.3s ease;
        border-left: 3px solid transparent;
    }

    .nav-item:hover {
        background: rgba(52, 152, 219, 0.1);
        color: #3498db;
        border-left-color: #3498db;
    }

    .nav-item.active {
        background: rgba(52, 152, 219, 0.1);
        color: #3498db;
        border-left-color: #3498db;
    }

    .nav-item i {
        width: 20px;
        text-align: center;
        margin-right: 10px;
        font-size: 1.1em;
    }

    .nav-text {
        font-size: 0.9em;
        font-weight: 500;
    }

    /* Collapsed State */
    .sidenav.collapsed .nav-section h3 {
        display: none;
    }

    .sidenav.collapsed .nav-item {
        padding: 12px;
        justify-content: center;
    }

    .sidenav.collapsed .nav-item i {
        margin-right: 0;
        font-size: 1.2em;
    }

    /* Logout specific styles */
    .logout-link {
        color: #e74c3c !important;
        border-top: 1px solid #eee;
        margin-top: 5px;
        padding-top: 10px;
    }

    .logout-link:hover {
        background: #e74c3c !important;
        color: #ffffff !important;
    }
    </style>
    <script>
    // Handle sidebar collapse
    document.addEventListener('DOMContentLoaded', function() {
        const mainContent = document.getElementById('main-content');
        const sidebarToggle = document.querySelector('.toggle-btn');
        const sidenav = document.querySelector('.sidenav');

        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function() {
                mainContent.classList.toggle('collapsed');
                sidenav.classList.toggle('collapsed');
            });
        }

        // Check if sidebar should be collapsed on page load
        const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        if (isCollapsed) {
            mainContent.classList.add('collapsed');
            sidenav.classList.add('collapsed');
        }

        // Save sidebar state
        sidebarToggle.addEventListener('click', function() {
            const isCollapsed = sidenav.classList.contains('collapsed');
            localStorage.setItem('sidebarCollapsed', isCollapsed);
        });
    });
    </script>
</head>

<body>
    <!-- Topnav -->
    <div class="topnav">
        <div class="toggle-btn" onclick="toggleSidenav()">
            <i class="fas fa-bars"></i>
        </div>
        <a href="index" class="logo">Ryvah E Commerce</a>
        <div class="actions">
            <div class="nav-links">
                <a href="index" title="Dashboard"><i class="fas fa-home"></i> Dashboard</a>
                <a href="settings"><i class="fas fa-cog"></i> Settings</a>
            </div>
            <div class="user-profile">
                <i class="fas fa-user-circle"></i>
                <span id="username"><?php echo htmlspecialchars(getCurrentUserName()); ?></span>
                <div class="dropdown">
                    <a href="profile"><i class="fas fa-user"></i> Profile</a>
                    <a href="2fa_setup"><i class="fas fa-shield-alt"></i> 2FA Setup</a>
                    <a href="php/logout" class="logout-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidenav -->
    <div class="sidenav" id="sidenav">
        <div class="nav-section">
            <h3>Main Menu</h3>
            <a href="index.php" class="nav-item">
                <i class="fas fa-home"></i>
                <span class="nav-text">Dashboard</span>
            </a>
            <a href="Product.php" class="nav-item">
                <i class="fas fa-box"></i>
                <span class="nav-text">Products</span>
            </a>
            <a href="orders.php" class="nav-item">
                <i class="fas fa-shopping-cart"></i>
                <span class="nav-text">Orders</span>
            </a>
            <a href="customers.php" class="nav-item">
                <i class="fas fa-users"></i>
                <span class="nav-text">Customers</span>
            </a>
        </div>

        <div class="nav-section">
            <h3>Management</h3>
            <a href="inventory.php" class="nav-item">
                <i class="fas fa-warehouse"></i>
                <span class="nav-text">Inventory</span>
            </a>
            <a href="tax_settings" class="nav-item">
                <i class="fas fa-chart-bar"></i>
                <span class="nav-text">Tax Management</span>
            </a>
            <a href="shipping_settings" class="nav-item">
                <i class="fas fa-chart-bar"></i>
                <span class="nav-text">Shipping Management</span>
            </a>
            <a href="reports.php" class="nav-item">
                <i class="fas fa-chart-bar"></i>
                <span class="nav-text">Reports</span>
            </a>
            <a href="settings.php" class="nav-item">
                <i class="fas fa-cog"></i>
                <span class="nav-text">Settings</span>
            </a>
        </div>

        <div class="nav-section">
            <h3>Account</h3>
            <a href="profile.php" class="nav-item">
                <i class="fas fa-user"></i>
                <span class="nav-text">Profile</span>
            </a>
            <a href="logout.php" class="nav-item">
                <i class="fas fa-sign-out-alt"></i>
                <span class="nav-text">Logout</span>
            </a>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    // Toggle Sidenav
    function toggleSidenav() {
        const sidenav = document.getElementById('sidenav');
        const mainContent = document.getElementById('main-content');

        if (window.innerWidth <= 992) {
            sidenav.classList.toggle('active');
            document.body.style.overflow = sidenav.classList.contains('active') ? 'hidden' : '';
        } else {
            sidenav.classList.toggle('collapsed');
            if (mainContent) {
                mainContent.classList.toggle('collapsed');
            }
        }
    }

    // Toggle Submenu
    function toggleSubmenu(id) {
        const submenu = document.getElementById(id);
        const parent = submenu.previousElementSibling;

        submenu.classList.toggle('active');
        parent.classList.toggle('active');

        // Rotate chevron icon
        const chevron = parent.querySelector('.fa-chevron-down');
        if (chevron) {
            chevron.style.transform = submenu.classList.contains('active') ? 'rotate(180deg)' : '';
        }
    }

    // Handle window resize
    window.addEventListener('resize', function() {
        const sidenav = document.getElementById('sidenav');
        const mainContent = document.getElementById('main-content');

        if (window.innerWidth > 992) {
            sidenav.classList.remove('active');
            document.body.style.overflow = '';
            if (mainContent) {
                mainContent.classList.remove('collapsed');
            }
        }
    });

    // Close sidenav when clicking outside on mobile
    document.addEventListener('click', function(event) {
        const sidenav = document.getElementById('sidenav');
        const toggleBtn = document.querySelector('.toggle-btn');

        if (window.innerWidth <= 992 &&
            !sidenav.contains(event.target) &&
            !toggleBtn.contains(event.target) &&
            sidenav.classList.contains('active')) {
            sidenav.classList.remove('active');
            document.body.style.overflow = '';
        }
    });

    // Fetch Notifications
    function fetchNotifications() {
        $.ajax({
            url: 'php/get_notifications',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#order-count').text(response.notifications.orders);
                    $('#support-count').text(response.notifications.support);
                }
            }
        });
    }

    // Initial fetch and periodic updates
    $(document).ready(function() {
        fetchNotifications();
        setInterval(fetchNotifications, 300000);

        // Highlight active page
        const currentPage = window.location.pathname.split('/').pop();
        $(`.sidenav a[href="${currentPage}"]`).addClass('active');

        // Add fade-in animation to elements
        $('.sidenav a, .topnav .nav-links a').addClass('fade-in');
    });

    document.addEventListener('DOMContentLoaded', function() {
        // Toggle sidenav
        const toggleBtn = document.querySelector('.toggle-btn');
        const sidenav = document.getElementById('sidenav');
        const mainContent = document.getElementById('main-content');

        if (toggleBtn && sidenav && mainContent) {
            toggleBtn.addEventListener('click', function() {
                sidenav.classList.toggle('collapsed');
                mainContent.classList.toggle('collapsed');
            });
        }

        // Add confirmation for logout
        const logoutLink = document.querySelector('.logout-link');
        if (logoutLink) {
            logoutLink.addEventListener('click', function(e) {
                if (!confirm('Are you sure you want to logout?')) {
                    e.preventDefault();
                }
            });
        }
    });
    </script>
</body>

</html>