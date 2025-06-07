<?php
session_start();
define('PAYPAL_ORDER_PROCESSING', true);

// Disable global security headers for this page (we'll set our own CSP)
define('SKIP_GLOBAL_HEADERS', true);

require_once '../includes/bootstrap.php';
require_once '../includes/cart.php';
require_once '../includes/paypal_config.php';

// Custom security headers for checkout
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
// Allow framing for PayPal only
header('X-Frame-Options: SAMEORIGIN');

// Rate limiting (simple implementation)
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$rate_limit_key = 'checkout_' . $ip;
$max_attempts = 10;
$time_window = 300; // 5 minutes

// Check if user is authenticated
if (!isset($_SESSION['user_id']) || !is_numeric($_SESSION['user_id'])) {
    // Preserve cart for checkout if it exists
    if (!empty($_SESSION['cart'])) {
        require_once '../includes/cart.php';
        preserveCartForCheckout();
    }
    header('Location: ../pages/login?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

// Validate PayPal configuration
if (!validatePayPalConfig()) {
    $error_message = 'Payment system is temporarily unavailable. Please try again later.';
    logPayPalError('PayPal configuration validation failed on checkout page');
}

// Generate CSRF token with better security
if (!isset($_SESSION['csrf_token']) || empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Regenerate session ID for security
if (!isset($_SESSION['checkout_session_regenerated'])) {
    session_regenerate_id(true);
    $_SESSION['checkout_session_regenerated'] = true;
}

try {
    // Fetch cart items with error handling
    $cart_data = getCartItems();
    $cart_items = $cart_data['items'] ?? [];

    if (empty($cart_items)) {
        $redirect_url = '../pages/index';
        $error_message = 'Your cart is empty. Please add items to your cart before checkout.';
    }

    // Calculate totals with validation
    $subtotal = 0;
    $valid_items = [];

    foreach ($cart_items as $item) {
        // Validate item data
        if (
            !isset($item['id'], $item['price'], $item['quantity'], $item['name']) ||
            !is_numeric($item['price']) || !is_numeric($item['quantity']) ||
            $item['quantity'] <= 0 || $item['price'] < 0
        ) {
            logPayPalError('Invalid cart item found', $item);
            continue;
        }

        // Check product availability
        $stmt = $conn->prepare("SELECT stock_quantity, name, price FROM products WHERE id = ? AND stock_quantity >= ?");
        $stmt->bind_param("ii", $item['id'], $item['quantity']);
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();

        if (!$product) {
            logPayPalError('Product not available or insufficient stock', $item);
            continue;
        }

        // Verify price hasn't changed
        if (abs($product['price'] - $item['price']) > 0.01) {
            logPayPalError('Product price mismatch', [
                'product_id' => $item['id'],
                'cart_price' => $item['price'],
                'current_price' => $product['price']
            ]);
            $item['price'] = $product['price']; // Update to current price
        }

        $item_total = $item['price'] * $item['quantity'];
        $subtotal += $item_total;
        $valid_items[] = $item;
    }

    $cart_items = $valid_items;

    if (empty($cart_items)) {
        $error_message = 'No valid items in your cart. Please check your cart and try again.';
    }

    // Validate payment amount
    if (!validatePaymentAmount($subtotal)) {
        $error_message = 'Order total is outside acceptable payment range.';
    }

    // Get user currency
    $currency = getUserCurrency($_SESSION['user_id']);

    // Fetch user addresses with validation
    $stmt = $conn->prepare("SELECT id, label, street, city, state, postal_code, country FROM addresses WHERE user_id = ? ORDER BY is_default DESC, created_at DESC");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $addresses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    if ($stmt->error) {
        logPayPalError('Database error fetching addresses: ' . $stmt->error);
        $error_message = 'Unable to load shipping addresses. Please try again.';
    }

    if (empty($addresses)) {
        $redirect_url = '../pages/add_address?redirect=' . urlencode($_SERVER['REQUEST_URI']);
        $error_message = 'No shipping addresses found. Please add a shipping address first.';
    }

    // Tax and shipping will be calculated when user selects an address
    $tax_amount = 0;
    $shipping_amount = 0;
    $total = $subtotal; // Initially just the subtotal, tax and shipping added when address selected

    // Final validation
    if (!validatePaymentAmount($total)) {
        $error_message = 'Order total including tax is outside acceptable payment range.';
    }
} catch (Exception $e) {
    logPayPalError('Error in checkout preparation: ' . $e->getMessage());
    $error_message = 'An error occurred while preparing your order. Please try again.';
}

// If we have errors, redirect or show error
if (isset($error_message)) {
    if (isset($redirect_url)) {
        header('Location: ' . $redirect_url);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Checkout - <?= htmlspecialchars(SITE_NAME); ?></title>

    <!-- Enhanced Security and Performance -->
    <meta http-equiv="Content-Security-Policy"
        content="default-src 'self'; script-src 'self' 'unsafe-inline' https://www.paypal.com https://*.paypal.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com ws://gc.kis.v2.scr.kaspersky-labs.com; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com ws://gc.kis.v2.scr.kaspersky-labs.com; font-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; connect-src 'self' https://api.paypal.com https://www.paypal.com https://*.paypal.com; frame-src 'self' https://www.paypal.com https://*.paypal.com ws://gc.kis.v2.scr.kaspersky-labs.com; child-src 'self' https://www.paypal.com https://*.paypal.com; img-src 'self' data: https: blob:;">

    <!-- Stylesheets -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"
        crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">

    <!-- PayPal SDK with Credit/Debit Cards Enabled -->
    <script
        src="https://www.paypal.com/sdk/js?client-id=<?= htmlspecialchars(PAYPAL_CLIENT_ID); ?>&currency=<?= htmlspecialchars($currency); ?>&intent=capture&enable-funding=venmo,paylater"
        defer></script>

    <style>
        :root {
            --primary-color: #007bff;
            --secondary-color: #6c757d;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #17a2b8;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
        }

        body {
            background-color: var(--light-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .checkout-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 24px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary-color), #0056b3);
            color: white;
            border-radius: 12px 12px 0 0 !important;
            padding: 1.5rem;
            border: none;
        }

        .card-header h3 {
            margin: 0;
            color: white;
            font-weight: 600;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .table {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .table thead th {
            background-color: #f1f3f4;
            border: none;
            font-weight: 600;
            color: var(--dark-color);
        }

        .table tbody tr {
            transition: background-color 0.2s ease;
        }

        .table tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.05);
        }

        .form-select {
            border-radius: 8px;
            border: 2px solid #e9ecef;
            padding: 12px 16px;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .paypal-buttons-container {
            margin-top: 24px;
            padding: 20px;
            background: white;
            border-radius: 12px;
            border: 2px dashed #e9ecef;
            transition: border-color 0.2s ease;
        }

        .paypal-buttons-container:hover {
            border-color: var(--primary-color);
        }

        .alert {
            border-radius: 8px;
            border: none;
            padding: 16px 20px;
            margin-bottom: 20px;
        }

        .alert-danger {
            background-color: #fef2f2;
            color: #dc2626;
            border-left: 4px solid var(--danger-color);
        }

        .alert-warning {
            background-color: #fffbeb;
            color: #d97706;
            border-left: 4px solid var(--warning-color);
        }

        .alert-info {
            background-color: #eff6ff;
            color: #2563eb;
            border-left: 4px solid var(--info-color);
        }

        .loading-spinner {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }

        .spinner {
            width: 50px;
            height: 50px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .security-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #f0f9ff;
            color: #0284c7;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 500;
            border: 1px solid #bae6fd;
        }

        .order-summary {
            position: sticky;
            top: 20px;
        }

        @media (max-width: 768px) {
            .checkout-container {
                padding: 10px;
            }

            .order-summary {
                position: static;
            }
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), #0056b3);
            border: none;
            border-radius: 8px;
            padding: 12px 24px;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
        }

        .total-amount {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        .shipping-breakdown {
            background-color: #f8f9fa;
            border-radius: 6px;
            padding: 12px;
            border-left: 3px solid var(--info-color);
        }

        .shipping-breakdown .breakdown-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 4px 0;
            border-bottom: 1px solid #e9ecef;
            font-size: 0.875rem;
        }

        .shipping-breakdown .breakdown-item:last-child {
            border-bottom: none;
        }

        .shipping-breakdown .breakdown-item .item-name {
            flex: 1;
            color: var(--secondary-color);
            font-weight: 500;
        }

        .shipping-breakdown .breakdown-item .item-cost {
            color: var(--dark-color);
            font-weight: 600;
        }

        .shipping-amount.free {
            color: var(--success-color);
            font-weight: 600;
        }

        /* Reduce overall font sizes */
        h1,
        .h1 {
            font-size: 1.75rem !important;
        }

        h2,
        .h2 {
            font-size: 1.5rem !important;
        }

        h3,
        .h3 {
            font-size: 1.25rem !important;
        }

        .card-header h3 {
            font-size: 1.1rem !important;
            margin-bottom: 0;
        }

        .btn-back-to-cart {
            background: #6c757d;
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 500;
            text-decoration: none;
            color: white;
            transition: all 0.2s ease;
        }

        .btn-back-to-cart:hover {
            background: #5a6268;
            color: white;
            transform: translateY(-1px);
        }
    </style>
</head>

<body>
    <?php require_once '../includes/layouts/header.php'; ?>
    <!-- Loading Spinner -->
    <div class="loading-spinner" id="loadingSpinner">
        <div class="text-center text-white">
            <div class="spinner mx-auto mb-3"></div>
            <p>Processing your payment...</p>
        </div>
    </div>

    <div class="checkout-container">
        <!-- Header -->
        <div class="text-center mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <a href="../pages/cart" class="btn-back-to-cart">
                    <i class="bi bi-arrow-left me-2"></i>
                    Back to Cart
                </a>
                <div class="security-badge">
                    <i class="bi bi-lock-fill"></i>
                    SSL Secured
                </div>
            </div>
            <h1 class="h2 fw-bold text-primary">
                <i class="bi bi-shield-check"></i>
                Secure Checkout
            </h1>
            <p class="text-muted">Review your order and complete your purchase securely</p>
        </div>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <?= htmlspecialchars($error_message); ?>
            </div>
        <?php else: ?>

            <div class="row">
                <!-- Order Details -->
                <div class="col-lg-8">
                    <!-- Cart Summary -->
                    <div class="card">
                        <div class="card-header">
                            <h3>
                                <i class="bi bi-cart3"></i>
                                Order Summary
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Price</th>
                                            <th>Quantity</th>
                                            <th>Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($cart_items as $item): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div>
                                                            <div class="fw-medium"><?= htmlspecialchars($item['name']); ?></div>
                                                            <small class="text-muted">SKU:
                                                                <?= htmlspecialchars($item['sku'] ?? 'N/A'); ?></small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><?= formatCurrency($item['price'], $currency); ?></td>
                                                <td>
                                                    <span class="badge bg-secondary"><?= intval($item['quantity']); ?></span>
                                                </td>
                                                <td class="fw-semibold">
                                                    <?= formatCurrency($item['price'] * $item['quantity'], $currency); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Shipping Address -->
                    <div class="card">
                        <div class="card-header">
                            <h3>
                                <i class="bi bi-truck"></i>
                                Shipping Address
                            </h3>
                        </div>
                        <div class="card-body">
                            <form id="checkout-form">
                                <div class="mb-3">
                                    <label for="address-select" class="form-label fw-semibold">
                                        Select Shipping Address <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="address-select" name="address_id" required>
                                        <option value="">Choose your shipping address...</option>
                                        <?php foreach ($addresses as $address): ?>
                                            <option value="<?= intval($address['id']); ?>"
                                                data-state="<?= htmlspecialchars($address['state']); ?>"
                                                data-country="<?= htmlspecialchars($address['country']); ?>">
                                                <?= htmlspecialchars($address['label'] . ': ' . $address['street'] . ', ' . $address['city'] . ', ' . $address['state'] . ' ' . $address['postal_code'] . ', ' . $address['country']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback" id="address-error"></div>
                                </div>

                                <input type="hidden" name="csrf_token"
                                    value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>">
                                <input type="hidden" name="subtotal" value="<?= number_format($subtotal, 2, '.', ''); ?>">
                                <input type="hidden" name="tax_amount" value="0.00">
                                <input type="hidden" name="shipping_amount" value="0.00">
                                <input type="hidden" name="total" value="<?= number_format($subtotal, 2, '.', ''); ?>">

                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle-fill me-2"></i>
                                    Please select your shipping address to calculate accurate taxes and proceed with
                                    payment.
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Order Total and Payment -->
                <div class="col-lg-4">
                    <div class="order-summary">
                        <div class="card">
                            <div class="card-header">
                                <h3>
                                    <i class="bi bi-receipt"></i>
                                    Order Total
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Subtotal:</span>
                                    <span><?= formatCurrency($subtotal, $currency); ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Tax:</span>
                                    <span id="tax-display"><?= formatCurrency(0, $currency); ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Shipping:</span>
                                    <span id="shipping-display" class="shipping-amount">
                                        <?= formatCurrency(0, $currency); ?>
                                    </span>
                                </div>

                                <!-- Shipping Breakdown -->
                                <div id="shipping-breakdown" class="shipping-breakdown mb-3" style="display: none;">
                                    <small class="text-muted d-block mb-2">Shipping breakdown:</small>
                                    <div id="breakdown-items"></div>
                                </div>

                                <hr>
                                <div class="d-flex justify-content-between mb-3">
                                    <span class="h6">Total:</span>
                                    <span class="total-amount"
                                        id="total-display"><?= formatCurrency($subtotal, $currency); ?></span>
                                </div>

                                <div class="mb-3">
                                    <small class="text-muted">
                                        <i class="bi bi-shield-check me-1"></i>
                                        Your payment is secured by PayPal's industry-leading encryption
                                    </small>
                                </div>

                                <!-- PayPal Button Container -->
                                <div id="paypal-button-container" class="paypal-buttons-container">
                                    <div class="text-center text-muted">
                                        <i class="bi bi-arrow-up"></i>
                                        <p class="small mb-0">Select shipping address to enable payment</p>
                                    </div>
                                </div>

                                <!-- Error Display -->
                                <div id="payment-error" class="alert alert-danger mt-3" style="display: none;">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                    <span id="payment-error-text"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        <?php endif; ?>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous">
    </script>

    <script>
        // Global variables
        let paypalButtonsRendered = false;
        let currentTotal = <?= json_encode($subtotal); ?>;
        let currentTax = 0;
        let currentShipping = 0;
        let subtotal = <?= json_encode($subtotal); ?>;
        let currency = <?= json_encode($currency); ?>;

        // Show/hide loading spinner
        function showLoading() {
            document.getElementById('loadingSpinner').style.display = 'flex';
        }

        function hideLoading() {
            document.getElementById('loadingSpinner').style.display = 'none';
        }

        // Show error message
        function showError(message) {
            const errorDiv = document.getElementById('payment-error');
            const errorText = document.getElementById('payment-error-text');
            errorText.textContent = message;
            errorDiv.style.display = 'block';
            errorDiv.scrollIntoView({
                behavior: 'smooth',
                block: 'nearest'
            });
        }

        // Hide error message
        function hideError() {
            document.getElementById('payment-error').style.display = 'none';
        }

        // Update order totals
        function updateOrderTotals(newTax, newShipping, newTotal, shippingBreakdown = []) {
            document.getElementById('tax-display').textContent = formatCurrency(newTax);

            const shippingDisplay = document.getElementById('shipping-display');
            if (newShipping > 0) {
                shippingDisplay.textContent = formatCurrency(newShipping);
                shippingDisplay.classList.remove('free');
            } else {
                shippingDisplay.textContent = 'Free';
                shippingDisplay.classList.add('free');
            }

            document.getElementById('total-display').textContent = formatCurrency(newTotal);

            // Update shipping breakdown
            updateShippingBreakdown(shippingBreakdown);

            currentTax = newTax;
            currentShipping = newShipping;
            currentTotal = newTotal;
        }

        // Update shipping breakdown display
        function updateShippingBreakdown(breakdown) {
            const breakdownContainer = document.getElementById('shipping-breakdown');
            const breakdownItems = document.getElementById('breakdown-items');

            if (breakdown && breakdown.length > 0) {
                breakdownItems.innerHTML = '';
                breakdown.forEach(item => {
                    const itemDiv = document.createElement('div');
                    itemDiv.className = 'breakdown-item';
                    itemDiv.innerHTML = `
                    <span class="item-name">${item.product_name} (${item.quantity}x)</span>
                    <span class="item-cost">${formatCurrency(item.shipping_fee)}</span>
                `;
                    breakdownItems.appendChild(itemDiv);
                });
                breakdownContainer.style.display = 'block';
            } else {
                breakdownContainer.style.display = 'none';
            }
        }

        // Format currency for display
        function formatCurrency(amount) {
            const formatter = new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: currency,
                minimumFractionDigits: 2
            });
            return formatter.format(amount);
        }

        // Calculate tax and shipping based on selected address
        async function calculateTaxAndShipping(addressId) {
            try {
                console.log('Calculating totals for address ID:', addressId);

                const response = await fetch('calculate_totals.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        address_id: parseInt(addressId),
                        csrf_token: document.querySelector('input[name="csrf_token"]').value
                    })
                });

                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('Server response:', response.status, errorText);
                    throw new Error('Failed to calculate totals: ' + response.status + ' - ' + errorText.substring(0,
                        100));
                }

                const responseText = await response.text();
                console.log('Raw response:', responseText);

                let data;
                try {
                    data = JSON.parse(responseText);
                } catch (parseError) {
                    console.error('JSON parse error:', parseError);
                    console.error('Response was:', responseText.substring(0, 500));
                    throw new Error('Invalid response format. Server returned: ' + responseText.substring(0, 100));
                }
                console.log('Response from calculate_totals.php:', data);

                if (data.success) {
                    return {
                        tax: parseFloat(data.tax_amount) || 0,
                        shipping: parseFloat(data.shipping_amount) || 0,
                        total: parseFloat(data.total) || subtotal,
                        shipping_breakdown: data.shipping_breakdown || []
                    };
                } else {
                    throw new Error(data.message || 'Failed to calculate totals');
                }
            } catch (error) {
                console.error('Error calculating totals:', error);
                showError('Failed to calculate totals: ' + error.message);
                // Fallback to no tax/shipping if API fails
                return {
                    tax: 0,
                    shipping: 0,
                    total: subtotal
                };
            }
        }

        // Address change handler
        document.getElementById('address-select').addEventListener('change', async function() {
            const selectedOption = this.options[this.selectedIndex];
            hideError();

            if (this.value) {
                // Show loading indicator
                showLoading();

                try {
                    // Calculate tax and shipping based on selected address
                    const totals = await calculateTaxAndShipping(this.value);

                    const newTax = totals.tax;
                    const newShipping = totals.shipping;
                    const newTotal = subtotal + newTax + newShipping;
                    const shippingBreakdown = totals.shipping_breakdown || [];

                    updateOrderTotals(newTax, newShipping, newTotal, shippingBreakdown);

                    // Update hidden form fields
                    document.querySelector('input[name="tax_amount"]').value = newTax.toFixed(2);
                    document.querySelector('input[name="shipping_amount"]').value = newShipping.toFixed(2);
                    document.querySelector('input[name="total"]').value = newTotal.toFixed(2);

                    // Update global variables
                    currentTax = newTax;
                    currentShipping = newShipping;
                    currentTotal = newTotal;

                    // Render PayPal buttons if not already rendered
                    if (!paypalButtonsRendered) {
                        renderPayPalButtons();
                    }
                } catch (error) {
                    showError('Failed to calculate order totals. Please try again.');
                    console.error('Address change error:', error);
                } finally {
                    hideLoading();
                }
            } else {
                // Clear PayPal buttons if no address selected
                document.getElementById('paypal-button-container').innerHTML = `
                    <div class="text-center text-muted">
                        <i class="bi bi-arrow-up"></i>
                        <p class="small mb-0">Select shipping address to enable payment</p>
                    </div>
                `;
                paypalButtonsRendered = false;
            }
        });

        // Render PayPal buttons
        function renderPayPalButtons() {
            if (typeof paypal === 'undefined') {
                showError('PayPal SDK not loaded. Please refresh the page and try again.');
                return;
            }

            document.getElementById('paypal-button-container').innerHTML = '';

            paypal.Buttons({
                style: {
                    layout: 'vertical',
                    color: 'blue',
                    shape: 'rect',
                    label: 'paypal',
                    height: 50
                },

                createOrder: function(data, actions) {
                    const addressId = document.getElementById('address-select').value;
                    const csrfToken = document.querySelector('input[name="csrf_token"]').value;

                    if (!addressId) {
                        showError('Please select a shipping address.');
                        return Promise.reject('Address not selected');
                    }

                    hideError();
                    showLoading();

                    return fetch('simple_create_order.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({
                                total: currentTotal.toFixed(2),
                                subtotal: subtotal.toFixed(2),
                                tax_amount: currentTax.toFixed(2),
                                shipping_amount: currentShipping.toFixed(2),
                                address_id: parseInt(addressId),
                                csrf_token: csrfToken,
                                currency: currency
                            })
                        })
                        .then(response => {
                            if (!response.ok) {
                                return response.text().then(text => {
                                    let errorData;
                                    try {
                                        errorData = JSON.parse(text);
                                    } catch (e) {
                                        errorData = {
                                            message: 'Network connection error. Please check your internet connection.'
                                        };
                                    }
                                    throw new Error(errorData.message ||
                                        'Payment system unavailable');
                                });
                            }

                            return response.json();
                        })
                        .then(data => {
                            hideLoading();

                            if (data.error) {
                                throw new Error(data.message || 'Failed to create order');
                            }

                            if (!data.id) {
                                throw new Error('Payment system error - no order ID received');
                            }

                            console.log('PayPal order created successfully:', data.id);
                            return data.id;
                        })
                        .catch(err => {
                            hideLoading();
                            console.error('Payment creation failed:', err);

                            let errorMessage = err.message;

                            // Specific error handling for immediate payment processing
                            if (err.message.includes('Failed to connect') || err.message.includes(
                                    'Couldn\'t connect')) {
                                errorMessage =
                                    'Payment system connection failed. Please check your internet connection and try again immediately.';
                            } else if (err.message.includes('timeout')) {
                                errorMessage =
                                    'Payment processing timeout. Please try again now - no retry delay needed.';
                            } else if (err.message.includes('503') || err.message.includes(
                                    'Service Unavailable')) {
                                errorMessage =
                                    'Payment system temporarily unavailable. Please retry immediately.';
                            } else if (err.message.includes('network') || err.message.includes(
                                    'connectivity')) {
                                errorMessage =
                                    'Network connection issue detected. Please check your internet and try again.';
                            }

                            showError(errorMessage);
                            throw new Error(errorMessage);
                        });
                },

                onApprove: function(data, actions) {
                    const csrfToken = document.querySelector('input[name="csrf_token"]').value;

                    showLoading();
                    hideError();

                    return fetch('simple_capture_order.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({
                                orderID: data.orderID,
                                csrf_token: csrfToken
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            hideLoading();

                            if (data.success) {
                                // Redirect to success page
                                window.location.href = 'simple_success.php?order_id=' + data.order_id +
                                    '&token=' + data.success_token;
                            } else {
                                showError(data.message || 'Payment capture failed');
                            }
                        })
                        .catch(err => {
                            hideLoading();
                            console.error('Capture order error:', err);
                            showError('Payment processing failed. Please try again.');
                        });
                },

                onError: function(err) {
                    hideLoading();
                    console.error('PayPal Error:', err);

                    let message = 'Payment processing failed. Please try again.';

                    if (err && typeof err === 'string') {
                        if (err.includes('INSUFFICIENT_FUNDS')) {
                            message = 'Insufficient funds. Please try another payment method.';
                        } else if (err.includes('DECLINED')) {
                            message = 'Payment was declined. Please try another payment method.';
                        }
                    }

                    showError(message);
                },

                onCancel: function(data) {
                    hideLoading();
                    console.log('Payment cancelled by user');
                    showError('Payment was cancelled. You can try again when ready.');
                }

            }).render('#paypal-button-container').then(() => {
                paypalButtonsRendered = true;
                console.log('PayPal buttons rendered successfully');
            }).catch(err => {
                console.error('PayPal buttons render error:', err);
                showError('Failed to load payment options. Please refresh the page.');
            });
        }

        // Form validation
        document.getElementById('checkout-form').addEventListener('submit', function(e) {
            e.preventDefault();

            const addressSelect = document.getElementById('address-select');
            if (!addressSelect.value) {
                addressSelect.classList.add('is-invalid');
                document.getElementById('address-error').textContent = 'Please select a shipping address.';
                addressSelect.focus();
                return false;
            }

            addressSelect.classList.remove('is-invalid');
            return true;
        });

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Checkout page initialized');

            // Check for CSP violations and warn user
            window.addEventListener('securitypolicyviolation', function(e) {
                if (e.blockedURI && e.blockedURI.includes('paypal.com')) {
                    console.warn('PayPal blocked by CSP - may be antivirus interference');
                    showError(
                        'Payment system may be blocked by antivirus software. Please temporarily disable antivirus or try a different browser.'
                    );
                }
            });

            // Check if PayPal SDK is loaded
            if (typeof paypal === 'undefined') {
                setTimeout(() => {
                    if (typeof paypal === 'undefined') {
                        showError(
                            'PayPal payment system is temporarily unavailable. This may be caused by antivirus software. Please try disabling antivirus or using a different browser.'
                        );
                    }
                }, 5000);
            }
        });
    </script>
</body>
<?php require_once '../includes/layouts/footer.php'; ?>