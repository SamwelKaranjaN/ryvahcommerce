<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Ryvah Books - Your Gateway to Digital Knowledge">
    <meta name="theme-color" content="#2563eb">
    <title>Ryvah Books</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../assets/images/favicon.jpeg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="/ryvahcommerce/assets/css/main.css">
    <link rel="stylesheet" href="/ryvahcommerce/assets/css/transitions.css">

    <style>
    :root {
        --navbar-height: 55px;
        --primary-color: #2563eb;
        --secondary-color: #64748b;
        --accent-color: #f59e0b;
        --background-color: #ffffff;
        --text-color: #1e293b;
        --border-color: rgba(226, 232, 240, 0.6);
        --transition-base: 0.3s ease;
        --mobile-menu-bg: #ffffff;
        --mobile-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
        --hover-bg: rgba(37, 99, 235, 0.08);
    }

    /* Reset and Base */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        margin: 0;
        padding: 0;
    }

    /* Modern Navbar */
    .navbar {
        background: var(--background-color);
        height: var(--navbar-height);
        border-bottom: 1px solid rgba(0, 0, 0, 0.08);
        transition: all var(--transition-base);
        position: sticky;
        top: 0;
        z-index: 1000;
        padding: 0;
    }

    .navbar.scrolled {
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        backdrop-filter: blur(10px);
        background: rgba(255, 255, 255, 0.95);
    }

    /* Container Layout */
    .navbar .container {
        display: flex;
        align-items: center;
        justify-content: space-between;
        position: relative;
        height: 100%;
        padding: 0 1rem;
    }

    /* Logo Section */
    .navbar-brand {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        font-weight: 700;
        color: var(--text-color) !important;
        text-decoration: none;
        flex-shrink: 0;
        transition: all var(--transition-base);
    }

    .navbar-brand:hover {
        text-decoration: none;
        color: var(--primary-color) !important;
        transform: translateY(-1px);
    }

    .navbar-brand img {
        height: 26px;
        width: auto;
        border-radius: 6px;
        transition: transform var(--transition-base);
    }

    .navbar-brand:hover img {
        transform: scale(1.05);
    }

    .navbar-brand span {
        font-size: 1.15rem;
        background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        font-weight: 800;
    }

    /* Navigation Center */
    .navbar-nav {
        display: flex;
        flex-direction: row;
        align-items: center;
        gap: 0.5rem;
        margin: 0;
        list-style: none;
        padding: 0;
    }

    .nav-item {
        position: relative;
    }

    .nav-link {
        padding: 0.4rem 0.8rem !important;
        color: var(--text-color) !important;
        font-weight: 500;
        font-size: 0.9rem;
        transition: all var(--transition-base);
        border-radius: 8px;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 0.4rem;
        position: relative;
        overflow: hidden;
    }

    .nav-link::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(37, 99, 235, 0.1), transparent);
        transition: left 0.5s;
    }

    .nav-link:hover::before {
        left: 100%;
    }

    .nav-link:hover,
    .nav-link.active {
        color: var(--primary-color) !important;
        background: rgba(37, 99, 235, 0.1);
        text-decoration: none;
        transform: translateY(-1px);
    }

    .nav-link i {
        width: 14px;
        text-align: center;
        font-size: 0.85rem;
    }

    /* Right Section */
    .navbar-right {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        flex-shrink: 0;
    }

    .action-btn.laws-btn-animated {
        animation: bounceLaw 2s infinite;
        font-weight: 600;
        font-size: 0.85rem;
        letter-spacing: 0.3px;
        box-shadow: 0 4px 16px -4px #f59e0b55;
        border: none;
        outline: none;
        background: linear-gradient(135deg, var(--accent-color), #d97706);
        color: #fff;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        border-radius: 20px;
        padding: 0.5rem 1.2rem;
        transition: all var(--transition-base);
        text-decoration: none;
    }

    /* Laws Button */
    .laws-btn {
        background: linear-gradient(135deg, var(--accent-color) 0%, #d97706 100%);
        color: white;
        border: none;
        padding: 0.6rem 1.4rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.9rem;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        transition: all var(--transition-base);
        box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);
        position: relative;
        overflow: hidden;
    }

    .laws-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.6s;
    }

    .laws-btn:hover::before {
        left: 100%;
    }

    .laws-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(245, 158, 11, 0.4);
        color: white;
        text-decoration: none;
    }

    .laws-btn i {
        font-size: 0.85rem;
    }

    /* Navigation Icons */
    .nav-icons {
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }

    .nav-icon {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-color);
        font-size: 1.05rem;
        position: relative;
        transition: all var(--transition-base);
        border-radius: 8px;
        text-decoration: none;
    }

    .nav-icon:hover {
        color: var(--primary-color);
        background: rgba(37, 99, 235, 0.1);
        transform: translateY(-1px);
        text-decoration: none;
    }

    .cart-count {
        position: absolute;
        top: -2px;
        right: -2px;
        background: var(--primary-color);
        color: white;
        font-size: 0.6rem;
        font-weight: 600;
        min-width: 14px;
        height: 14px;
        border-radius: 7px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0 3px;
        border: 1px solid white;
    }

    /* Hamburger Menu */
    .navbar-toggler {
        display: none;
        width: 36px;
        height: 36px;
        border: none;
        background: rgba(248, 250, 252, 0.8);
        border-radius: 8px;
        transition: all var(--transition-base);
        cursor: pointer;
        position: relative;
        z-index: 1100;
        padding: 6px;
        border: 1px solid rgba(226, 232, 240, 0.6);
    }

    .navbar-toggler:hover {
        background: rgba(37, 99, 235, 0.1);
        border-color: rgba(37, 99, 235, 0.3);
        transform: scale(1.05);
    }

    .navbar-toggler:focus {
        outline: none;
        box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.25);
    }

    .navbar-toggler .icon-bar {
        display: block;
        width: 22px;
        height: 3px;
        margin: 4px auto;
        background: var(--text-color);
        border-radius: 3px;
        transition: all 0.3s ease;
        transform-origin: center;
    }

    .navbar-toggler:not(.collapsed) .icon-bar:nth-child(1) {
        transform: rotate(45deg) translate(6px, 6px);
        background: #dc2626;
    }

    .navbar-toggler:not(.collapsed) .icon-bar:nth-child(2) {
        opacity: 0;
        transform: scale(0);
    }

    .navbar-toggler:not(.collapsed) .icon-bar:nth-child(3) {
        transform: rotate(-45deg) translate(6px, -6px);
        background: #dc2626;
    }

    /* Dropdown Menu */
    .dropdown-menu {
        border: none;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        border-radius: 10px;
        padding: 0.4rem;
        min-width: 180px;
        margin-top: 0.4rem;
        backdrop-filter: blur(20px);
        background: rgba(255, 255, 255, 0.95);
    }

    .dropdown-item {
        padding: 0.6rem 0.8rem;
        border-radius: 6px;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: all var(--transition-base);
        color: var(--text-color);
        text-decoration: none;
        font-size: 0.85rem;
        margin-bottom: 2px;
    }

    .dropdown-item:hover {
        background: var(--hover-bg);
        color: var(--primary-color);
        text-decoration: none;
        transform: translateX(3px);
    }

    .dropdown-item i {
        width: 14px;
        text-align: center;
        font-size: 0.8rem;
    }

    .dropdown-divider {
        margin: 0.4rem 0;
        border-color: var(--border-color);
    }

    /* Mobile Responsive */
    @media (max-width: 991.98px) {
        .navbar-toggler {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .navbar-nav,
        .navbar-right {
            display: none;
        }

        /* Modern Side Menu */
        .navbar-collapse {
            position: fixed;
            top: 0;
            left: -100%;
            width: 280px;
            height: 100vh;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.98) 0%, rgba(248, 250, 252, 0.95) 100%);
            backdrop-filter: blur(20px);
            box-shadow: 4px 0 20px rgba(0, 0, 0, 0.15);
            padding: 0;
            transform: translateX(-100%);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1050;
            border-right: 1px solid rgba(255, 255, 255, 0.3);
            overflow-y: auto;
        }

        .navbar-collapse.show {
            left: 0;
            transform: translateX(0);
        }

        .navbar-collapse.collapsing {
            transform: translateX(-50%);
        }

        /* Sidebar Header */
        .sidebar-header {
            padding: 1.5rem 1rem;
            border-bottom: 1px solid rgba(226, 232, 240, 0.4);
            display: flex;
            align-items: center;
            gap: 0.8rem;
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.05) 0%, rgba(59, 130, 246, 0.02) 100%);
            position: sticky;
            top: 0;
            z-index: 1051;
            backdrop-filter: blur(10px);
        }

        .sidebar-header img {
            height: 28px;
            width: auto;
            border-radius: 6px;
        }

        .sidebar-header span {
            font-size: 1.1rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }



        /* Mobile Navigation Items */
        .mobile-nav {
            padding: 1rem;
            margin: 0;
            list-style: none;
        }

        .mobile-nav .nav-item {
            margin-bottom: 0.5rem;
            list-style: none;
        }

        .mobile-nav .nav-item::before {
            display: none;
        }

        .mobile-nav .nav-link {
            padding: 0.7rem 1rem !important;
            margin-bottom: 0.3rem;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(248, 250, 252, 0.8) 100%);
            border: 1px solid rgba(226, 232, 240, 0.6);
            border-radius: 12px;
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--text-color) !important;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            text-decoration: none;
            list-style: none;
        }

        .mobile-nav .nav-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(37, 99, 235, 0.1), transparent);
            transition: left 0.6s;
        }

        .mobile-nav .nav-link:hover::before {
            left: 100%;
        }

        .mobile-nav .nav-link:hover {
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.1) 0%, rgba(59, 130, 246, 0.05) 100%);
            border-color: rgba(37, 99, 235, 0.3);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(37, 99, 235, 0.15);
            color: var(--primary-color) !important;
        }

        .mobile-nav .nav-link i {
            margin-right: 0.6rem;
            font-size: 0.95rem;
            width: 16px;
            text-align: center;
        }

        /* Mobile Laws Button */
        .mobile-laws {
            margin: 0 1rem 1.5rem;
            justify-content: center;
            font-size: 0.9rem;
            font-weight: 600;
            padding: 0.8rem 1.5rem;
            border-radius: 12px;
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 50%, #b45309 100%);
            box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);
            border: none;
            color: white;
            position: relative;
            overflow: hidden;
            text-transform: none;
            letter-spacing: 0.2px;
        }

        .mobile-laws::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.8s;
        }

        .mobile-laws:hover::before {
            left: 100%;
        }

        .mobile-laws:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(245, 158, 11, 0.5);
        }

        .mobile-laws i {
            margin-right: 0.5rem;
            font-size: 0.85rem;
        }

        /* Make sidebar header sticky within the mobile menu */
        .navbar-collapse .sidebar-header {
            position: sticky;
            top: 0;
            z-index: 1051;
            backdrop-filter: blur(10px);
            border-bottom: 2px solid rgba(226, 232, 240, 0.6);
        }

        /* Mobile Icons Section */
        .mobile-icons {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 0.6rem;
            padding: 1rem;
            border-top: 1px solid rgba(226, 232, 240, 0.4);
            border-bottom: 1px solid rgba(226, 232, 240, 0.4);
            margin: 0 1rem;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.6) 0%, rgba(248, 250, 252, 0.4) 100%);
            border-radius: 12px;
            backdrop-filter: blur(10px);
        }

        .mobile-icon {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.3rem;
            padding: 0.7rem 0.4rem;
            border-radius: 10px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            color: var(--text-color);
            background: rgba(255, 255, 255, 0.7);
            border: 1px solid rgba(226, 232, 240, 0.5);
            position: relative;
            overflow: hidden;
        }

        .mobile-icon::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, transparent 0%, rgba(37, 99, 235, 0.1) 50%, transparent 100%);
            opacity: 0;
            transition: opacity 0.3s;
        }

        .mobile-icon:hover::before {
            opacity: 1;
        }

        .mobile-icon:hover {
            background: rgba(255, 255, 255, 0.9);
            border-color: rgba(37, 99, 235, 0.3);
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(37, 99, 235, 0.15);
            color: var(--primary-color);
            text-decoration: none;
        }

        .mobile-icon i {
            font-size: 1.1rem;
            transition: transform 0.3s;
        }

        .mobile-icon:hover i {
            transform: scale(1.1);
        }

        .mobile-icon span {
            font-size: 0.65rem;
            font-weight: 600;
            text-transform: none;
            letter-spacing: 0.2px;
        }

        /* Mobile Auth Section */
        .mobile-auth {
            display: flex;
            flex-direction: row;
            gap: 0.6rem;
            padding: 1rem;
            margin: 0 1rem;
        }

        .mobile-auth-btn {
            padding: 0.8rem 1rem;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.85rem;
            text-align: center;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            position: relative;
            overflow: hidden;
            text-transform: none;
            letter-spacing: 0.2px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.1);
            flex: 1;
        }

        .mobile-auth-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.6s;
        }

        .mobile-auth-btn:hover::before {
            left: 100%;
        }

        .mobile-auth-btn.login {
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.1) 0%, rgba(59, 130, 246, 0.05) 100%);
            color: var(--primary-color);
            border: 2px solid rgba(37, 99, 235, 0.3);
        }

        .mobile-auth-btn.login:hover {
            background: linear-gradient(135deg, var(--primary-color) 0%, #1d4ed8 100%);
            color: white;
            text-decoration: none;
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(37, 99, 235, 0.3);
        }

        .mobile-auth-btn.register {
            background: linear-gradient(135deg, var(--primary-color) 0%, #1d4ed8 100%);
            color: white;
            border: 2px solid var(--primary-color);
        }

        .mobile-auth-btn.register:hover {
            background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
            text-decoration: none;
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(37, 99, 235, 0.4);
        }

        .mobile-auth-btn.logout {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(248, 113, 113, 0.05) 100%);
            color: #dc2626;
            border: 2px solid rgba(239, 68, 68, 0.3);
        }

        .mobile-auth-btn.logout:hover {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            color: white;
            text-decoration: none;
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(239, 68, 68, 0.3);
        }

        .mobile-auth-btn.settings {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(129, 140, 248, 0.05) 100%);
            color: #6366f1;
            border: 2px solid rgba(99, 102, 241, 0.3);
        }

        .mobile-auth-btn.settings:hover {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: white;
            text-decoration: none;
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(99, 102, 241, 0.3);
        }

        .mobile-auth-btn i {
            font-size: 1.1rem;
        }
    }

    /* Desktop - hide mobile elements */
    @media (min-width: 992px) {

        .mobile-nav,
        .mobile-laws,
        .mobile-icons,
        .mobile-auth {
            display: none !important;
        }
    }

    /* Mobile Overlay */
    .mobile-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 998;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }

    .mobile-overlay.show {
        opacity: 1;
        visibility: visible;
    }

    /* Body Padding - removed since navbar is now sticky */
    .main-content {
        min-height: calc(100vh - var(--navbar-height));
    }

    /* Ensure body content flows properly under sticky navbar */
    body {
        margin: 0;
        padding: 0;
    }

    /* Toast Messages */
    .toast {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border: none;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        border-radius: 10px;
    }

    .toast-header {
        border-bottom: 1px solid var(--border-color);
        padding: 0.8rem;
        border-radius: 10px 10px 0 0;
    }

    .toast-body {
        padding: 0.8rem;
    }

    /* Utility Classes */
    .text-gradient {
        background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    /* Animation for mobile menu */
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

    .navbar-collapse.show {
        animation: slideDown 0.3s ease-out;
    }

    /* Modern Checkout Overlay */
    .checkout-overlay {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 1050;
        max-width: 400px;
        width: calc(100vw - 40px);
        opacity: 0;
        transform: translateY(100px);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        pointer-events: none;
    }

    .checkout-overlay.show {
        opacity: 1;
        transform: translateY(0);
        pointer-events: auto;
    }

    .checkout-overlay-content {
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.98) 0%, rgba(248, 250, 252, 0.95) 100%);
        backdrop-filter: blur(20px);
        border-radius: 20px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
        border: 1px solid rgba(255, 255, 255, 0.3);
        overflow: hidden;
        position: relative;
    }

    .checkout-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1.5rem;
        border-bottom: 1px solid rgba(226, 232, 240, 0.4);
    }

    .checkout-icon {
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, var(--primary-color) 0%, #1d4ed8 100%);
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.2rem;
        box-shadow: 0 8px 25px rgba(37, 99, 235, 0.3);
    }

    .checkout-info {
        flex: 1;
    }

    .checkout-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--text-color);
        margin-bottom: 0.25rem;
    }

    .checkout-subtitle {
        font-size: 0.85rem;
        color: var(--secondary-color);
        font-weight: 500;
    }

    .checkout-total {
        text-align: right;
    }

    .total-label {
        font-size: 0.75rem;
        color: var(--secondary-color);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
    }

    .total-amount {
        font-size: 1.3rem;
        font-weight: 800;
        color: var(--primary-color);
        margin-top: 0.25rem;
    }

    .checkout-actions {
        display: flex;
        gap: 0.75rem;
        padding: 1.25rem 1.5rem;
    }

    .btn-overlay {
        flex: 1;
        padding: 0.8rem 1rem;
        border-radius: 12px;
        font-weight: 600;
        font-size: 0.9rem;
        text-decoration: none;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }

    .btn-overlay::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
        transition: left 0.6s;
    }

    .btn-overlay:hover::before {
        left: 100%;
    }

    .btn-view-cart {
        background: rgba(100, 116, 139, 0.1);
        color: var(--secondary-color);
        border: 2px solid rgba(100, 116, 139, 0.2);
    }

    .btn-view-cart:hover {
        background: var(--secondary-color);
        color: white;
        text-decoration: none;
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(100, 116, 139, 0.3);
    }

    .btn-checkout {
        background: linear-gradient(135deg, var(--primary-color) 0%, #1d4ed8 100%);
        color: white;
        border: 2px solid var(--primary-color);
    }

    .btn-checkout:hover {
        background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
        text-decoration: none;
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(37, 99, 235, 0.4);
    }

    .checkout-close {
        position: absolute;
        top: 10px;
        right: 10px;
        width: 30px;
        height: 30px;
        border: none;
        background: rgba(100, 116, 139, 0.1);
        border-radius: 50%;
        color: var(--secondary-color);
        font-size: 0.8rem;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .checkout-close:hover {
        background: rgba(239, 68, 68, 0.1);
        color: #dc2626;
        transform: scale(1.1);
    }

    /* Responsive Design for Checkout Overlay */
    @media (max-width: 768px) {
        .checkout-overlay {
            bottom: 10px;
            right: 10px;
            left: 10px;
            max-width: none;
            width: auto;
        }

        .checkout-header {
            padding: 1.25rem;
            gap: 0.75rem;
        }

        .checkout-icon {
            width: 45px;
            height: 45px;
            font-size: 1.1rem;
        }

        .checkout-title {
            font-size: 1rem;
        }

        .checkout-subtitle {
            font-size: 0.8rem;
        }

        .total-amount {
            font-size: 1.2rem;
        }

        .checkout-actions {
            padding: 1rem 1.25rem;
            gap: 0.5rem;
        }

        .btn-overlay {
            padding: 0.7rem 0.8rem;
            font-size: 0.85rem;
        }
    }

    /* Pulse animation for new items */
    @keyframes checkoutPulse {
        0% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.05);
        }

        100% {
            transform: scale(1);
        }
    }

    .checkout-overlay.pulse {
        animation: checkoutPulse 0.6s ease;
    }

    /* Success Toast Animations */
    @keyframes cartBounce {
        0% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.3);
        }

        100% {
            transform: scale(1);
        }
    }

    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }

        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }

        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }

    .cart-success-toast {
        border-left: 4px solid rgba(255, 255, 255, 0.8);
    }

    .cart-success-toast .success-icon {
        width: 40px;
        height: 40px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .cart-success-toast .btn-close {
        filter: brightness(0) invert(1);
        opacity: 0.8;
    }

    .cart-success-toast .btn-close:hover {
        opacity: 1;
        transform: scale(1.1);
    }

    /* Enhanced cart count styling */
    .cart-count {
        animation: none !important;
        transition: all 0.3s ease;
    }

    .cart-count.updated {
        animation: cartBounce 0.6s ease !important;
    }

    /* Mini Cart Notification */
    @keyframes miniCartBounce {
        0% {
            transform: scale(0) translateY(-10px);
            opacity: 0;
        }

        50% {
            transform: scale(1.1) translateY(-5px);
            opacity: 1;
        }

        100% {
            transform: scale(1) translateY(0);
            opacity: 1;
        }
    }

    @keyframes fadeOut {
        from {
            opacity: 1;
            transform: scale(1);
        }

        to {
            opacity: 0;
            transform: scale(0.8);
        }
    }

    .mini-cart-notification {
        pointer-events: none;
    }

    /* Mobile responsive for success toast */
    @media (max-width: 768px) {
        .cart-success-toast {
            right: 10px !important;
            left: 10px !important;
            min-width: auto !important;
            max-width: calc(100vw - 20px);
        }
    }
    </style>
</head>

<body>
    <?php
    // Include timeout modal if user is logged in
    if (isset($_SESSION['user_id'])) {
        include 'timeout_modal.php';
    }
    ?>

    <!-- Mobile overlay -->
    <div class="mobile-overlay" id="mobileOverlay"></div>

    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <!-- Logo -->
            <a class="navbar-brand" href="../pages/index">
                <img src="../resources/logo.jpeg" alt="RYVAH">
                <span>Ryvah Books</span>
            </a>

            <!-- Desktop Navigation -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="../pages/index">
                        <i class="fas fa-home"></i>Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../NFT/">
                        <i class="fas fa-gem"></i>NFT Collection
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../pages/about">
                        <i class="fas fa-info-circle"></i>About
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../pages/contact">
                        <i class="fas fa-envelope"></i>Contact
                    </a>
                </li>
            </ul>

            <!-- Right Section -->
            <div class="navbar-right">
                <a href="../lawsofryvah/laws" class="laws-btn">
                    <i class="fas fa-gavel"></i>
                    Laws of Ryvah
                </a>

                <div class="nav-icons">
                    <a href="../pages/cart" class="nav-icon" title="Shopping Cart">
                        <i class="fas fa-shopping-cart"></i>
                        <?php if (isset($cart_count) && $cart_count > 0): ?>
                        <span class="cart-count"><?php echo $cart_count; ?></span>
                        <?php endif; ?>
                    </a>

                    <div class="dropdown">
                        <a href="#" class="nav-icon" data-bs-toggle="dropdown" title="User Menu">
                            <i class="fas fa-user"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <?php if (isset($_SESSION['user_id'])): ?>
                            <li><a class="dropdown-item" href="../pages/profile"><i class="fas fa-user"></i>Profile</a>
                            </li>
                            <li><a class="dropdown-item" href="../pages/orders"><i
                                        class="fas fa-shopping-bag"></i>Orders</a></li>
                            <li><a class="dropdown-item" href="../pages/settings"><i class="fas fa-cog"></i>Settings</a>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="../pages/logout"><i
                                        class="fas fa-sign-out-alt"></i>Logout</a></li>
                            <?php else: ?>
                            <li><a class="dropdown-item"
                                    href="../pages/login?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>">
                                    <i class="fas fa-sign-in-alt"></i>Login</a></li>
                            <li><a class="dropdown-item" href="../pages/register"><i
                                        class="fas fa-user-plus"></i>Register</a></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Mobile Hamburger -->
            <button class="navbar-toggler collapsed" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                id="navbarToggler">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>

            <!-- Mobile Menu -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <div class="sidebar-header">
                    <img src="../resources/logo.jpeg" alt="RYVAH">
                    <span>Ryvah Books</span>
                </div>
                <ul class="mobile-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="../pages/index">
                            <i class="fas fa-home"></i>Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../NFT/">
                            <i class="fas fa-gem"></i>NFT Collection
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../pages/about">
                            <i class="fas fa-info-circle"></i>About
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../pages/contact">
                            <i class="fas fa-envelope"></i>Contact
                        </a>
                    </li>
                </ul>

                <a href="../lawsofryvah/laws" class="laws-btn mobile-laws">
                    <i class="fas fa-gavel"></i>
                    Laws of Ryvah
                </a>

                <div class="mobile-icons">
                    <a href="../pages/cart" class="mobile-icon">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Cart</span>
                        <?php if (isset($cart_count) && $cart_count > 0): ?>
                        <span class="cart-count"><?php echo $cart_count; ?></span>
                        <?php endif; ?>
                    </a>
                    <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="../pages/profile" class="mobile-icon">
                        <i class="fas fa-user"></i>
                        <span>Profile</span>
                    </a>
                    <a href="../pages/orders" class="mobile-icon">
                        <i class="fas fa-shopping-bag"></i>
                        <span>Orders</span>
                    </a>
                    <?php endif; ?>
                </div>

                <div class="mobile-auth">
                    <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="../pages/settings" class="mobile-auth-btn settings">
                        <i class="fas fa-cog"></i>Settings
                    </a>
                    <a href="../pages/logout" class="mobile-auth-btn logout">
                        <i class="fas fa-sign-out-alt"></i>Logout
                    </a>
                    <?php else: ?>
                    <a href="../pages/login?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>"
                        class="mobile-auth-btn login">
                        <i class="fas fa-sign-in-alt"></i>Login
                    </a>
                    <a href="../pages/register" class="mobile-auth-btn register">
                        <i class="fas fa-user-plus"></i>Register
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>



    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Session timeout script -->
    <?php if (isset($_SESSION['user_id'])): ?>
    <script src="/ryvahcommerce/js/session-timeout.js"></script>
    <?php endif; ?>

    <script>
    // Navbar scroll effect
    let lastScrollTop = 0;
    window.addEventListener('scroll', function() {
        const navbar = document.querySelector('.navbar');
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;

        if (scrollTop > 20) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }

        lastScrollTop = scrollTop;
    });

    // Mobile menu functionality
    const toggler = document.getElementById('navbarToggler');
    const navCollapse = document.getElementById('navbarNav');
    const overlay = document.getElementById('mobileOverlay');

    // Toggle mobile menu
    toggler.addEventListener('click', function() {
        const isExpanded = !navCollapse.classList.contains('show');

        if (isExpanded) {
            toggler.classList.remove('collapsed');
            overlay.classList.add('show');
        } else {
            toggler.classList.add('collapsed');
            overlay.classList.remove('show');
        }
    });

    // Handle Bootstrap collapse events
    navCollapse.addEventListener('shown.bs.collapse', function() {
        document.body.style.overflow = 'hidden';
        toggler.classList.remove('collapsed');
        overlay.classList.add('show');
    });

    navCollapse.addEventListener('hidden.bs.collapse', function() {
        document.body.style.overflow = '';
        toggler.classList.add('collapsed');
        overlay.classList.remove('show');
    });

    // Close menu when clicking overlay
    overlay.addEventListener('click', function() {
        const bsCollapse = new bootstrap.Collapse(navCollapse, {
            toggle: false
        });
        bsCollapse.hide();
    });

    // Close menu when clicking nav links
    document.querySelectorAll('.mobile-nav .nav-link, .mobile-auth-btn, .mobile-laws').forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth < 992) {
                const bsCollapse = new bootstrap.Collapse(navCollapse, {
                    toggle: false
                });
                bsCollapse.hide();
            }
        });
    });

    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 992 && navCollapse.classList.contains('show')) {
            const bsCollapse = new bootstrap.Collapse(navCollapse, {
                toggle: false
            });
            bsCollapse.hide();
            document.body.style.overflow = '';
        }
    });
    </script>
</body>

</html>