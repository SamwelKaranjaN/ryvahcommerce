<?php
require_once '../includes/bootstrap.php';
require_once '../includes/cart.php';

// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    header('Location: ../pages/login?redirect=checkout');
    exit;
}

// Get cart data
$cart_data = getCartItems();
$cart_items = $cart_data['items'];
$cart_total = $cart_data['total'];

// Calculate tax
$tax_rates = [];
$tax_amount = 0;

// Get tax rates from database
$stmt = $conn->prepare("SELECT product_type, tax_rate FROM tax_settings WHERE is_active = 1");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $tax_rates[$row['product_type']] = $row['tax_rate'];
}

// Calculate tax for each item
foreach ($cart_items as $item) {
    if (isset($tax_rates[$item['type']])) {
        $item_tax = ($item['price'] * $item['quantity']) * ($tax_rates[$item['type']] / 100);
        $tax_amount += $item_tax;
    }
}

$grand_total = $cart_total + $tax_amount;

// Get user's addresses
$stmt = $conn->prepare("SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC, created_at DESC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$addresses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get user info
$stmt = $conn->prepare("SELECT full_name, email, phone FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user_info = $stmt->get_result()->fetch_assoc();

include '../includes/layouts/header.php';
?>

<div class="container py-4">
    <!-- Progress Indicator -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="progress-wrapper">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="progress-step completed">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="d-none d-md-inline">Cart</span>
                    </span>
                    <div class="progress-line completed"></div>
                    <span class="progress-step active">
                        <i class="fas fa-credit-card"></i>
                        <span class="d-none d-md-inline">Checkout</span>
                    </span>
                    <div class="progress-line"></div>
                    <span class="progress-step">
                        <i class="fas fa-check"></i>
                        <span class="d-none d-md-inline">Complete</span>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="../pages/index" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item"><a href="../pages/cart" class="text-decoration-none">Cart</a></li>
            <li class="breadcrumb-item active" aria-current="page">Checkout</li>
        </ol>
    </nav>

    <?php if (empty($cart_items)): ?>
    <!-- Empty Cart State -->
    <div class="empty-cart-container text-center py-5">
        <div class="empty-cart-icon mb-4">
            <i class="fas fa-shopping-cart fa-5x text-muted"></i>
        </div>
        <h3 class="mb-3">Your cart is empty</h3>
        <p class="text-muted mb-4">Add some amazing products to your cart before proceeding to checkout.</p>
        <a href="../pages/index" class="btn btn-primary btn-lg px-5">
            <i class="fas fa-arrow-left me-2"></i>Continue Shopping
        </a>
    </div>
    <?php else: ?>

    <!-- Checkout Form -->
    <div class="row g-4">
        <!-- Main Checkout Form -->
        <div class="col-lg-8">

            <!-- Customer Information -->
            <div class="checkout-section mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0">
                            <i class="fas fa-user me-2 text-primary"></i>Customer Information
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Full Name</label>
                                <input type="text" class="form-control"
                                    value="<?php echo htmlspecialchars($user_info['full_name']); ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control"
                                    value="<?php echo htmlspecialchars($user_info['email']); ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone</label>
                                <input type="text" class="form-control"
                                    value="<?php echo htmlspecialchars($user_info['phone'] ?? 'Not provided'); ?>"
                                    readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Shipping Address -->
            <div class="checkout-section mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0">
                            <i class="fas fa-map-marker-alt me-2 text-primary"></i>Shipping Address
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <?php if (!empty($addresses)): ?>
                        <div id="address-selection">
                            <?php foreach ($addresses as $index => $address): ?>
                            <div class="address-option mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="selected_address"
                                        id="address_<?php echo $address['id']; ?>" value="<?php echo $address['id']; ?>"
                                        <?php echo $address['is_default'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label w-100" for="address_<?php echo $address['id']; ?>">
                                        <div class="address-card p-3 border rounded">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h6 class="mb-1">
                                                        <?php echo htmlspecialchars($address['label']); ?>
                                                        <?php if ($address['is_default']): ?>
                                                        <span class="badge bg-primary ms-2">Default</span>
                                                        <?php endif; ?>
                                                    </h6>
                                                    <p class="mb-1"><?php echo htmlspecialchars($address['street']); ?>
                                                    </p>
                                                    <p class="mb-0 text-muted">
                                                        <?php echo htmlspecialchars($address['city'] . ', ' . $address['state'] . ' ' . $address['postal_code']); ?>
                                                    </p>
                                                    <p class="mb-0 text-muted">
                                                        <?php echo htmlspecialchars($address['country']); ?></p>
                                                </div>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="mt-3">
                            <a href="../pages/addresses" class="btn btn-outline-primary">
                                <i class="fas fa-plus me-2"></i>Add New Address
                            </a>
                        </div>
                        <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-map-marker-alt fa-3x text-muted mb-3"></i>
                            <h6 class="mb-3">No shipping address found</h6>
                            <p class="text-muted mb-3">You need to add a shipping address to proceed with your order.
                            </p>
                            <a href="../pages/addresses" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Add Shipping Address
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Order Items -->
            <div class="checkout-section mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0">
                            <i class="fas fa-box me-2 text-primary"></i>Order Items (<?php echo count($cart_items); ?>
                            items)
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="order-items">
                            <?php foreach ($cart_items as $item): ?>
                            <div class="order-item d-flex align-items-center mb-3 pb-3 border-bottom">
                                <div class="item-image me-3">
                                    <img src="../admin/<?php echo htmlspecialchars($item['thumbs']); ?>" class="rounded"
                                        alt="<?php echo htmlspecialchars($item['name']); ?>"
                                        style="width: 80px; height: 80px; object-fit: cover;">
                                </div>
                                <div class="item-details flex-grow-1">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($item['name']); ?></h6>
                                    <p class="text-muted mb-1">
                                        <small>Type: <?php echo ucfirst($item['type']); ?></small>
                                    </p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted">Quantity: <?php echo $item['quantity']; ?></span>
                                        <strong
                                            class="text-primary">$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></strong>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="text-end mt-3">
                            <a href="../pages/cart" class="btn btn-outline-secondary">
                                <i class="fas fa-edit me-2"></i>Edit Cart
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Summary Sidebar -->
        <div class="col-lg-4">
            <div class="order-summary-card">
                <div class="card border-0 shadow-sm sticky-top" style="top: 2rem;">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-file-invoice-dollar me-2"></i>Order Summary
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <!-- Price Breakdown -->
                        <div class="price-breakdown mb-4">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal</span>
                                <span>$<?php echo number_format($cart_total, 2); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Tax</span>
                                <span>$<?php echo number_format($tax_amount, 2); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span>Shipping</span>
                                <span class="text-success">Free</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-4">
                                <strong class="h5">Total</strong>
                                <strong class="h5 text-primary">$<?php echo number_format($grand_total, 2); ?></strong>
                            </div>
                        </div>

                        <!-- Payment Section -->
                        <div class="payment-section">
                            <?php if (!empty($addresses)): ?>
                            <div class="payment-methods mb-3">
                                <h6 class="mb-3">Payment Method</h6>
                                <div class="payment-option">
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="radio" name="payment_method"
                                            id="paypal_method" value="paypal" checked>
                                        <label class="form-check-label" for="paypal_method">
                                            <i class="fab fa-paypal me-2 text-primary"></i>PayPal or Debit/Credit Card
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- PayPal Button Container -->
                            <div id="paypal-button-container" class="mb-3"></div>
                            <?php else: ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Please add a shipping address to proceed with payment.
                            </div>
                            <?php endif; ?>

                            <!-- Security Info -->
                            <div class="security-info">
                                <small class="text-muted d-flex align-items-center">
                                    <i class="fas fa-lock me-2"></i>
                                    Your payment information is secure and encrypted
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- PayPal SDK with better error handling -->
<script>
// Browser compatibility check
function checkBrowserCompatibility() {
    // Check for required features
    const requiredFeatures = [
        'Promise',
        'fetch',
        'JSON',
        'addEventListener'
    ];

    for (let feature of requiredFeatures) {
        if (typeof window[feature] === 'undefined') {
            console.error(`Browser missing required feature: ${feature}`);
            return false;
        }
    }
    return true;
}

// PayPal SDK Configuration and Loading
window.paypalLoadAttempts = 0;
window.maxPaypalLoadAttempts = 3;

function loadPayPalSDK() {
    return new Promise((resolve, reject) => {
        // Check browser compatibility first
        if (!checkBrowserCompatibility()) {
            reject(new Error('Browser not compatible with PayPal SDK'));
            return;
        }

        // Check if PayPal is already loaded and properly initialized
        if (typeof window.paypal !== 'undefined' && window.paypal && window.paypal.Buttons && typeof window
            .paypal.Buttons === 'function') {
            console.log('PayPal already loaded and initialized');
            resolve(window.paypal);
            return;
        }

        // Remove any existing PayPal scripts to avoid conflicts
        const existingScripts = document.querySelectorAll('script[src*="paypal.com/sdk"]');
        existingScripts.forEach(script => script.remove());

        // Clear any existing paypal object
        if (typeof window.paypal !== 'undefined') {
            delete window.paypal;
        }

        console.log('Loading PayPal SDK...');

        // Create script element
        const script = document.createElement('script');

        // PayPal Sandbox Configuration - using explicit sandbox parameters
        script.src = 'https://www.paypal.com/sdk/js?' +
            'client-id=ARb4izn3jwTWc2j2x6UDmompOiO2Uq3HQKodHTR3Y6UKUN61daJD09G8JVrx6UWz11-CL2fcty8UJ2CJ' +
            '&currency=USD' +
            '&intent=capture' +
            '&debug=true' +
            '&buyer-country=US' +
            '&enable-funding=venmo,paylater';

        script.async = true;
        script.defer = true;
        script.crossOrigin = 'anonymous';

        // Add additional attributes for better loading
        script.setAttribute('data-partner-attribution-id', 'RyvahCommerce_SP_PPCP');
        script.setAttribute('data-namespace', 'PayPalSDK');

        let initCheckTimeout;
        let initCheckInterval;

        script.onload = function() {
            console.log('PayPal SDK script loaded successfully');

            // Add window debugging for sandbox
            console.log('Window PayPal object status:', typeof window.paypal);
            console.log('Available funding sources:', window.paypal?.getFundingSources?.() ||
                'Not available yet');

            // Clear any existing timeouts
            if (initCheckTimeout) clearTimeout(initCheckTimeout);
            if (initCheckInterval) clearInterval(initCheckInterval);

            // Wait for PayPal to initialize with multiple checks
            let checkCount = 0;
            const maxChecks = 100; // 10 seconds total (100 * 100ms)

            initCheckInterval = setInterval(() => {
                checkCount++;
                console.log(`Checking PayPal initialization (${checkCount}/${maxChecks})...`);

                // More detailed debugging
                if (typeof window.paypal !== 'undefined') {
                    console.log('PayPal object found:', window.paypal);
                    console.log('PayPal.Buttons available:', typeof window.paypal.Buttons);
                    console.log('PayPal version:', window.paypal.version || 'Unknown');
                }

                if (typeof window.paypal !== 'undefined' &&
                    window.paypal &&
                    window.paypal.Buttons &&
                    typeof window.paypal.Buttons === 'function') {

                    clearInterval(initCheckInterval);
                    console.log('PayPal SDK initialized successfully');
                    console.log('PayPal object methods:', Object.keys(window.paypal));
                    resolve(window.paypal);
                    return;
                }

                if (checkCount >= maxChecks) {
                    clearInterval(initCheckInterval);
                    console.error('PayPal SDK failed to initialize within timeout period');
                    console.error('Final paypal object state:', typeof window.paypal, window
                        .paypal);

                    // Try to get more debugging info
                    if (typeof window.paypal !== 'undefined') {
                        console.error('PayPal object exists but Buttons not available');
                        console.error('PayPal object keys:', Object.keys(window.paypal));
                    }

                    reject(new Error(
                        'PayPal SDK initialization timeout - paypal.Buttons not available after 10 seconds'
                    ));
                }
            }, 100);

            // Also set a backup timeout
            initCheckTimeout = setTimeout(() => {
                clearInterval(initCheckInterval);
                if (typeof window.paypal === 'undefined' || !window.paypal || !window.paypal
                    .Buttons) {
                    console.error('PayPal SDK backup timeout triggered');
                    console.error('PayPal state at timeout:', {
                        typeofPaypal: typeof window.paypal,
                        paypalExists: !!window.paypal,
                        buttonsExists: !!(window.paypal && window.paypal.Buttons),
                        windowKeys: Object.keys(window).filter(k => k.toLowerCase()
                            .includes('paypal'))
                    });
                    reject(new Error(
                        'PayPal SDK loaded but failed to initialize within 15 seconds'));
                }
            }, 15000);
        };

        script.onerror = function(error) {
            console.error('Failed to load PayPal SDK script:', error);
            clearTimeout(initCheckTimeout);
            clearInterval(initCheckInterval);
            reject(new Error('Failed to load PayPal SDK script from server'));
        };

        // Handle script loading timeout
        setTimeout(() => {
            if (!script.onload.called) {
                script.onerror(new Error('Script loading timeout'));
            }
        }, 15000);

        document.head.appendChild(script);
    });
}

// PayPal Integration with improved error handling
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing PayPal...');

    // Error logging function
    function logError(error, context) {
        console.log('Logging error:', error, 'Context:', context);

        const errorData = {
            error: error.toString(),
            context: context,
            timestamp: new Date().toISOString(),
            userAgent: navigator.userAgent,
            url: window.location.href,
            browserInfo: {
                language: navigator.language,
                platform: navigator.platform,
                cookieEnabled: navigator.cookieEnabled,
                onLine: navigator.onLine
            }
        };

        fetch('log_error.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(errorData)
            })
            .then(response => response.json())
            .then(result => {
                console.log('Error logged successfully:', result);
            })
            .catch(err => {
                console.error('Failed to log error:', err);
                // Fallback: log to console if error logging fails
                console.error('Original error that failed to log:', error);
            });
    }

    const paypalContainer = document.getElementById('paypal-button-container');

    if (!paypalContainer) {
        console.error('PayPal button container not found!');
        logError(new Error('PayPal button container not found'), 'DOM Setup');
        return;
    }

    // Add loading indicator
    paypalContainer.innerHTML = `
            <div class="text-center py-3">
                <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                <span>Loading PayPal...</span>
            </div>
        `;

    // Load PayPal SDK with retry mechanism
    function attemptPayPalLoad() {
        window.paypalLoadAttempts++;
        console.log(`PayPal loading attempt ${window.paypalLoadAttempts}/${window.maxPaypalLoadAttempts}`);

        loadPayPalSDK()
            .then(paypal => {
                console.log('PayPal SDK loaded successfully, initializing buttons...');
                initializePayPalButtons(paypal);
            })
            .catch(error => {
                console.error('PayPal SDK loading error:', error);
                logError(error, 'PayPal SDK Loading');

                if (window.paypalLoadAttempts < window.maxPaypalLoadAttempts) {
                    console.log(
                        `Retrying PayPal load in 2 seconds (attempt ${window.paypalLoadAttempts + 1}/${window.maxPaypalLoadAttempts})...`
                    );
                    setTimeout(attemptPayPalLoad, 2000);
                } else {
                    console.error('All PayPal loading attempts failed');
                    logError(new Error('All PayPal loading attempts failed after ' + window
                        .maxPaypalLoadAttempts + ' tries'), 'PayPal SDK Loading Final Failure');

                    // Check if this is localhost and offer test mode
                    const isLocalhost = window.location.hostname === 'localhost' || window.location
                        .hostname === '127.0.0.1';
                    if (isLocalhost) {
                        showLocalhostTestMode();
                    } else {
                        showPayPalError(
                            'Failed to load PayPal payment system. This may be due to network issues or browser compatibility. Please try refreshing the page or using a different browser.'
                        );
                    }
                }
            });
    }

    function initializePayPalButtons(paypal) {
        try {
            console.log('Initializing PayPal buttons...');
            paypalContainer.innerHTML = ''; // Clear loading indicator

            // Verify PayPal object is still valid
            if (!paypal || !paypal.Buttons || typeof paypal.Buttons !== 'function') {
                throw new Error('PayPal object became invalid during initialization');
            }

            paypal.Buttons({
                    style: {
                        layout: 'vertical',
                        color: 'blue',
                        shape: 'rect',
                        label: 'paypal',
                        height: 45
                    },

                    createOrder: function(data, actions) {
                        console.log('Creating PayPal order...');

                        // Check if address is selected
                        const selectedAddress = document.querySelector(
                            'input[name="selected_address"]:checked');
                        if (!selectedAddress) {
                            alert('Please select a shipping address');
                            return Promise.reject('No address selected');
                        }

                        const total = <?php echo $grand_total; ?>;
                        console.log('Order total:', total);

                        return fetch('create_order.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify({
                                    total: total,
                                    address_id: selectedAddress.value
                                })
                            })
                            .then(response => {
                                console.log('Create order response status:', response.status);

                                return response.text().then(text => {
                                    console.log('Raw response:', text);

                                    try {
                                        const jsonData = JSON.parse(text);
                                        if (!response.ok) {
                                            throw new Error(jsonData.error ||
                                                'Failed to create order');
                                        }
                                        return jsonData;
                                    } catch (parseError) {
                                        console.error('JSON parse error:', parseError);
                                        console.error('Response text:', text);
                                        throw new Error(
                                            'Server returned invalid response: ' + text
                                            .substring(0, 100));
                                    }
                                });
                            })
                            .then(order => {
                                console.log('Order created:', order);
                                if (!order.id) {
                                    throw new Error('Invalid order response - missing ID');
                                }
                                return order.id;
                            })
                            .catch(err => {
                                console.error('Order creation error:', err);
                                logError(err, 'PayPal Order Creation');
                                alert('Error creating order: ' + err.message);
                                throw err;
                            });
                    },

                    onApprove: function(data, actions) {
                        console.log('PayPal payment approved:', data);

                        return fetch('capture_order.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify({
                                    orderID: data.orderID
                                })
                            })
                            .then(response => {
                                console.log('Capture response status:', response.status);

                                return response.text().then(text => {
                                    console.log('Capture raw response:', text);

                                    try {
                                        const jsonData = JSON.parse(text);
                                        if (!response.ok) {
                                            throw new Error(jsonData.error ||
                                                'Failed to capture payment');
                                        }
                                        return jsonData;
                                    } catch (parseError) {
                                        console.error('JSON parse error in capture:',
                                            parseError);
                                        console.error('Response text:', text);
                                        throw new Error(
                                            'Server returned invalid response during capture'
                                        );
                                    }
                                });
                            })
                            .then(details => {
                                console.log('Payment captured:', details);
                                if (details.status === 'COMPLETED') {
                                    // Show success message
                                    showSuccessMessage();

                                    // Redirect to success page
                                    setTimeout(() => {
                                        window.location.href = 'success.php?order=' +
                                            details.order_id;
                                    }, 2000);
                                } else {
                                    throw new Error('Payment capture failed');
                                }
                            })
                            .catch(err => {
                                console.error('Payment capture error:', err);
                                logError(err, 'PayPal Payment Capture');
                                alert('Error processing payment: ' + err.message);
                            });
                    },

                    onError: function(err) {
                        console.error('PayPal button error:', err);
                        logError(err, 'PayPal Button Error');
                        alert('An error occurred during payment processing. Please try again.');
                    },

                    onCancel: function(data) {
                        console.log('Payment cancelled:', data);
                        alert('Payment was cancelled. You can try again when ready.');
                    }
                }).render('#paypal-button-container')
                .then(() => {
                    console.log('PayPal button rendered successfully');
                })
                .catch(err => {
                    console.error('PayPal button render error:', err);
                    logError(err, 'PayPal Button Render');
                    showPayPalError('Could not render PayPal button. Please refresh the page.');
                });

        } catch (error) {
            console.error('PayPal initialization error:', error);
            logError(error, 'PayPal Initialization');
            showPayPalError('Failed to initialize PayPal. Please refresh the page.');
        }
    }

    function showPayPalError(message) {
        paypalContainer.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Payment System Error:</strong> ${message}
                    <div class="mt-3">
                        <button class="btn btn-primary me-2" onclick="location.reload()">
                            <i class="fas fa-redo me-2"></i>Retry Payment Loading
                        </button>
                        <button class="btn btn-outline-secondary" onclick="window.open('https://www.paypal.com/signin', '_blank')">
                            <i class="fab fa-paypal me-2"></i>Pay with PayPal Directly
                        </button>
                    </div>
                    <div class="mt-2">
                        <small class="text-muted">
                            If this error persists, please ensure JavaScript is enabled and try using a different browser.
                        </small>
                    </div>
                </div>
            `;
    }

    function showLocalhostTestMode() {
        paypalContainer.innerHTML = `
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Development Mode:</strong> PayPal SDK failed to load on localhost.
                    <div class="mt-3">
                        <button class="btn btn-warning me-2" onclick="simulatePayPalPayment()">
                            <i class="fas fa-vial me-2"></i>Simulate PayPal Payment (Test)
                        </button>
                        <button class="btn btn-primary me-2" onclick="location.reload()">
                            <i class="fas fa-redo me-2"></i>Retry PayPal Loading
                        </button>
                        <button class="btn btn-outline-info" onclick="showPayPalTroubleshooting()">
                            <i class="fas fa-tools me-2"></i>Troubleshooting
                        </button>
                    </div>
                    <div class="mt-2">
                        <small class="text-muted">
                            <strong>For developers:</strong> This is normal on localhost. Deploy to a public domain for real PayPal integration.
                            <br>The test mode will simulate a successful payment for development purposes.
                        </small>
                    </div>
                </div>
            `;
    }

    // Add troubleshooting function
    window.showPayPalTroubleshooting = function() {
        paypalContainer.innerHTML = `
            <div class="alert alert-info">
                <i class="fas fa-tools me-2"></i>
                <strong>PayPal Sandbox Troubleshooting:</strong>
                <div class="mt-3">
                    <h6>Common Issues:</h6>
                    <ul class="mb-3">
                        <li><strong>Network:</strong> Check if you can access paypal.com</li>
                        <li><strong>Firewall:</strong> Corporate firewall might block PayPal</li>
                        <li><strong>AdBlocker:</strong> Browser extensions might interfere</li>
                        <li><strong>Cookies:</strong> Third-party cookies must be enabled</li>
                        <li><strong>HTTPS:</strong> Some PayPal features require HTTPS</li>
                    </ul>
                    <div class="d-flex gap-2 flex-wrap">
                        <button class="btn btn-primary" onclick="location.reload()">
                            <i class="fas fa-redo me-2"></i>Try Again
                        </button>
                        <button class="btn btn-warning" onclick="simulatePayPalPayment()">
                            <i class="fas fa-vial me-2"></i>Use Test Mode
                        </button>
                        <button class="btn btn-success" onclick="window.open('https://developer.paypal.com/developer/applications/', '_blank')">
                            <i class="fab fa-paypal me-2"></i>Check PayPal App
                        </button>
                    </div>
                </div>
            </div>
        `;
    };

    // Simulate PayPal payment for localhost testing
    window.simulatePayPalPayment = function() {
        // Check if address is selected
        const selectedAddress = document.querySelector('input[name="selected_address"]:checked');
        if (!selectedAddress) {
            alert('Please select a shipping address');
            return;
        }

        if (confirm('This will simulate a successful PayPal payment. Continue?')) {
            console.log('Simulating PayPal payment...');

            // Show loading
            paypalContainer.innerHTML = `
                    <div class="alert alert-info">
                        <i class="fas fa-spinner fa-spin me-2"></i>
                        Simulating payment processing...
                    </div>
                `;

            // Simulate the payment process
            setTimeout(() => {
                fetch('create_order.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            total: <?php echo $grand_total; ?>,
                            address_id: selectedAddress.value,
                            test_mode: true
                        })
                    })
                    .then(response => response.json())
                    .then(order => {
                        console.log('Test order created:', order);

                        // Simulate successful payment
                        setTimeout(() => {
                            fetch('capture_order.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                    },
                                    body: JSON.stringify({
                                        orderID: order.id || 'TEST_ORDER_' +
                                            Date.now(),
                                        test_mode: true
                                    })
                                })
                                .then(response => response.json())
                                .then(details => {
                                    console.log('Test payment captured:', details);
                                    showSuccessMessage();

                                    setTimeout(() => {
                                        window.location.href =
                                            'success.php?order=' + (details
                                                .order_id || 'TEST_ORDER');
                                    }, 2000);
                                })
                                .catch(err => {
                                    console.error('Test capture error:', err);
                                    alert('Test payment simulation failed: ' + err
                                        .message);
                                });
                        }, 1000);
                    })
                    .catch(err => {
                        console.error('Test order creation error:', err);
                        alert('Test order creation failed: ' + err.message);
                    });
            }, 1000);
        }
    };

    // Start PayPal loading
    attemptPayPalLoad();

    // Success message function
    function showSuccessMessage() {
        const successAlert = document.createElement('div');
        successAlert.className = 'alert alert-success alert-dismissible fade show';
        successAlert.innerHTML = `
            <i class="fas fa-check-circle me-2"></i>
            <strong>Payment Successful!</strong> Redirecting to order confirmation...
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        document.querySelector('.container').insertBefore(successAlert, document.querySelector('.container')
            .firstChild);
    }

    // Address selection validation
    document.addEventListener('change', function(e) {
        if (e.target.name === 'selected_address') {
            console.log('Address selected:', e.target.value);

            // Enable/disable PayPal button based on address selection
            const paypalContainer = document.getElementById('paypal-button-container');
            if (paypalContainer && e.target.value) {
                paypalContainer.style.opacity = '1';
                paypalContainer.style.pointerEvents = 'auto';
            }
        }
    });
});
</script>

<style>
/* Progress Indicator Styles */
.progress-wrapper {
    margin-bottom: 2rem;
}

.progress-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
    padding: 1rem;
    border-radius: 50%;
    background: #f8f9fa;
    color: #6c757d;
    width: 60px;
    height: 60px;
    font-size: 1.2rem;
    transition: all 0.3s ease;
}

.progress-step.completed {
    background: #28a745;
    color: white;
}

.progress-step.active {
    background: #007bff;
    color: white;
    transform: scale(1.1);
}

.progress-line {
    height: 2px;
    background: #dee2e6;
    flex: 1;
    margin: 0 10px;
    position: relative;
    top: 30px;
}

.progress-line.completed {
    background: #28a745;
}

/* Checkout Section Styles */
.checkout-section .card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.checkout-section .card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.1) !important;
}

/* Address Card Styles */
.address-card {
    transition: all 0.3s ease;
    cursor: pointer;
}

.address-card:hover {
    background-color: #f8f9fa;
    border-color: #007bff !important;
}

.form-check-input:checked~.form-check-label .address-card {
    border-color: #007bff !important;
    background-color: rgba(0, 123, 255, 0.1);
}

/* Order Item Styles */
.order-item {
    transition: background-color 0.2s ease;
}

.order-item:hover {
    background-color: #f8f9fa;
    border-radius: 8px;
    padding: 1rem !important;
    margin: -0.5rem -1rem 0.5rem -1rem;
}

/* Empty Cart Styles */
.empty-cart-container {
    min-height: 50vh;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}

.empty-cart-icon {
    opacity: 0.7;
}

/* Order Summary Card */
.order-summary-card .card {
    border-radius: 15px;
    overflow: hidden;
}

.price-breakdown {
    font-size: 0.95rem;
}

.payment-section {
    border-top: 1px solid #dee2e6;
    padding-top: 1rem;
}

/* Security Info */
.security-info {
    padding: 0.75rem;
    background: #f8f9fa;
    border-radius: 8px;
    margin-top: 1rem;
}

/* PayPal Button Styling */
#paypal-button-container {
    margin: 1rem 0;
}

/* Responsive Design */
@media (max-width: 768px) {
    .progress-step {
        width: 50px;
        height: 50px;
        font-size: 1rem;
        padding: 0.5rem;
    }

    .progress-step span {
        font-size: 0.75rem;
        margin-top: 0.25rem;
    }

    .order-item {
        flex-direction: column;
        text-align: center;
    }

    .item-image {
        margin-bottom: 1rem;
        margin-right: 0 !important;
    }

    .address-card {
        margin-bottom: 1rem;
    }
}

/* Loading Animation */
.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Form Enhancements */
.form-control:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.form-check-input:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}
</style>

<?php include '../includes/layouts/footer.php'; ?>