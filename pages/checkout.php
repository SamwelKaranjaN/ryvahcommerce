<?php
require_once '../includes/bootstrap.php';
require_once '../includes/cart.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Store current URL in session for redirect after login
    $_SESSION['redirect_after_login'] = 'checkout.php';
    header('Location: login.php');
    exit;
}

$cart_data = getCartItems();
$cart_items = $cart_data['items'];
$cart_total = $cart_data['total'];

// Redirect to cart if empty
if (empty($cart_items)) {
    header('Location: cart.php');
    exit;
}

include '../includes/layouts/header.php';
?>

<div class="container py-5">
    <div class="row mb-4">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index" class="text-decoration-none">Home</a></li>
                    <li class="breadcrumb-item"><a href="cart" class="text-decoration-none">Cart</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Checkout</li>
                </ol>
            </nav>
            <h2 class="mb-0">Checkout</h2>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <!-- Progress Bar -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between checkout-steps">
                        <div class="step active">
                            <div class="step-icon">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <div class="step-text">Cart</div>
                        </div>
                        <div class="step active">
                            <div class="step-icon">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="step-text">Details</div>
                        </div>
                        <div class="step">
                            <div class="step-icon">
                                <i class="fas fa-credit-card"></i>
                            </div>
                            <div class="step-text">Payment</div>
                        </div>
                        <div class="step">
                            <div class="step-icon">
                                <i class="fas fa-check"></i>
                            </div>
                            <div class="step-text">Confirmation</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Shipping Information -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-map-marker-alt me-2 text-primary"></i>Shipping Information
                        </h5>
                    </div>
                    
                    <form id="checkout-form" method="POST" action="payment.php">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">First Name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Phone</label>
                                <input type="tel" class="form-control" id="phone" name="phone" required>
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label">Address</label>
                                <input type="text" class="form-control" id="address" name="address" required>
                            </div>
                            
                            <div class="col-md-5">
                                <label class="form-label">City</label>
                                <input type="text" class="form-control" id="city" name="city" required>
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label">State/Region</label>
                                <input type="text" class="form-control" id="state" name="state" required>
                            </div>
                            
                            <div class="col-md-3">
                                <label class="form-label">Postal Code</label>
                                <input type="text" class="form-control" id="postal_code" name="postal_code" required>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Back to Cart Button -->
            <div class="d-flex justify-content-between align-items-center mt-4">
                <a href="cart" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Cart
                </a>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Order Summary -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h5 class="card-title mb-4">
                        <i class="fas fa-shopping-basket me-2 text-primary"></i>Order Summary
                    </h5>
                    
                    <div class="order-items mb-4">
                        <?php foreach ($cart_items as $item): ?>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <img src="<?php echo htmlspecialchars($item['thumbs']); ?>" 
                                     class="rounded me-3" alt="" style="width: 50px; height: 50px; object-fit: cover;">
                                <div>
                                    <div class="fw-bold"><?php echo htmlspecialchars($item['name']); ?></div>
                                    <small class="text-muted">Qty: <?php echo $item['quantity']; ?></small>
                                </div>
                            </div>
                            <span>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="summary-details">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Subtotal</span>
                            <span>$<?php echo number_format($cart_total, 2); ?></span>
                        </div>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Shipping</span>
                            <span class="text-success">Free</span>
                        </div>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between mb-4">
                            <strong>Total</strong>
                            <div class="h4 mb-0 text-primary">$<?php echo number_format($cart_total, 2); ?></div>
                        </div>
                        
                        <button type="submit" form="checkout-form" class="btn btn-primary btn-lg w-100">
                            <i class="fas fa-credit-card me-2"></i>Proceed to Payment
                        </button>
                        
                        <div class="text-center mt-3">
                            <small class="text-muted">
                                <i class="fas fa-shield-alt me-1"></i>Secure Checkout
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Security Badge -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-shield-alt fa-2x text-success me-3"></i>
                        <div>
                            <h6 class="mb-1">Secure Checkout</h6>
                            <small class="text-muted">Your payment information is encrypted</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.checkout-steps {
    position: relative;
    width: 100%;
}

.checkout-steps::before {
    content: '';
    position: absolute;
    top: 24px;
    left: 0;
    right: 0;
    height: 2px;
    background: #e9ecef;
    z-index: 1;
}

.step {
    position: relative;
    z-index: 2;
    background: white;
    text-align: center;
    width: 50px;
}

.step-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: #e9ecef;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 0.5rem;
    color: #6c757d;
    transition: all 0.3s ease;
}

.step.active .step-icon {
    background: #007bff;
    color: white;
}

.step-text {
    font-size: 0.875rem;
    color: #6c757d;
}

.step.active .step-text {
    color: #007bff;
    font-weight: 600;
}

.custom-radio {
    padding: 1rem;
    border: 1px solid #dee2e6;
    border-radius: 0.5rem;
    margin-bottom: 1rem;
    transition: all 0.3s ease;
}

.custom-radio:hover {
    border-color: #007bff;
    background: #f8f9fa;
}

.custom-radio .form-check-input:checked ~ .form-check-label {
    color: #007bff;
}

.order-items {
    max-height: 300px;
    overflow-y: auto;
}

/* Enhanced Mobile Responsiveness */
@media (max-width: 767.98px) {
    .container {
        padding-left: 1rem;
        padding-right: 1rem;
    }

    .checkout-steps {
        display: none;
    }

    .card {
        border-radius: 0.5rem;
    }

    .card-body {
        padding: 1rem;
    }

    h2 {
        font-size: 1.5rem;
    }

    .breadcrumb {
        font-size: 0.875rem;
        margin-bottom: 0.5rem;
    }

    .form-label {
        font-size: 0.875rem;
        margin-bottom: 0.25rem;
    }

    .form-control {
        font-size: 0.875rem;
        padding: 0.5rem 0.75rem;
    }

    .btn {
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
    }

    .btn-lg {
        padding: 0.75rem 1.25rem;
        font-size: 1rem;
    }

    .custom-radio {
        padding: 0.75rem;
        margin-bottom: 0.75rem;
    }

    .custom-radio .fa-2x {
        font-size: 1.5rem;
    }

    .order-items {
        max-height: 250px;
    }

    .order-items img {
        width: 40px;
        height: 40px;
    }

    .order-items .fw-bold {
        font-size: 0.875rem;
    }

    .order-items .text-muted {
        font-size: 0.75rem;
    }

    .summary-details .h4 {
        font-size: 1.25rem;
    }

    .d-flex.justify-content-between.align-items-center.mt-4 {
        flex-direction: column;
        gap: 1rem;
    }

    .d-flex.justify-content-between.align-items-center.mt-4 .btn {
        width: 100%;
    }
}

/* Small Mobile Devices */
@media (max-width: 575.98px) {
    .row.g-3 {
        margin-left: -0.5rem;
        margin-right: -0.5rem;
    }

    .row.g-3 > [class^="col-"] {
        padding-left: 0.5rem;
        padding-right: 0.5rem;
    }

    .card-body {
        padding: 1rem;
    }

    .badge {
        font-size: 0.75rem;
    }

    .custom-radio .form-check-label {
        font-size: 0.875rem;
    }

    .custom-radio .text-muted {
        font-size: 0.75rem;
    }

    .fa-2x {
        font-size: 1.25rem !important;
    }

    .order-items {
        margin: 0 -1rem;
        padding: 0 1rem;
    }

    .summary-details {
        font-size: 0.875rem;
    }
}

/* Tablet Devices */
@media (min-width: 768px) and (max-width: 991.98px) {
    .card-body {
        padding: 1.25rem;
    }

    .custom-radio {
        padding: 0.875rem;
    }

    .order-items img {
        width: 45px;
        height: 45px;
    }
}

/* Ensure proper spacing in all viewports */
.row.g-4 {
    margin-left: -0.5rem;
    margin-right: -0.5rem;
}

.row.g-4 > [class^="col-"] {
    padding-left: 0.5rem;
    padding-right: 0.5rem;
}

/* Sticky Order Summary on Mobile */
@media (max-width: 991.98px) {
    .order-summary-wrapper {
        position: sticky;
        bottom: 0;
        z-index: 1000;
        margin: 0 -1rem -1rem;
        background: white;
        box-shadow: 0 -4px 12px rgba(0, 0, 0, 0.1);
        padding: 1rem;
        border-top-left-radius: 1rem;
        border-top-right-radius: 1rem;
    }

    .order-summary-wrapper .card {
        margin-bottom: 0;
        box-shadow: none !important;
    }

    .order-summary-wrapper .card-body {
        padding: 0;
    }

    .order-items {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease;
    }

    .order-items.expanded {
        max-height: 250px;
    }

    .summary-toggle {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.5rem 0;
        margin-bottom: 1rem;
        cursor: pointer;
    }

    .summary-toggle i {
        transition: transform 0.3s ease;
    }

    .summary-toggle.active i {
        transform: rotate(180deg);
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-fill user information if available
    fetch('../includes/get_user_info.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const user = data.user;
                const [firstName, ...lastNameParts] = user.full_name.split(' ');
                document.getElementById('first_name').value = firstName;
                document.getElementById('last_name').value = lastNameParts.join(' ');
                document.getElementById('email').value = user.email;
                document.getElementById('phone').value = user.phone || '';
                document.getElementById('address').value = user.address || '';
            }
        });

    // Payment method selection
    document.querySelectorAll('[name="payment_method"]').forEach(radio => {
        radio.addEventListener('change', function() {
            document.querySelectorAll('.custom-radio').forEach(container => {
                container.style.borderColor = '#dee2e6';
            });
            this.closest('.custom-radio').style.borderColor = '#007bff';
        });
    });

    // Add order summary toggle functionality for mobile
    if (window.innerWidth < 992) {
        const summaryToggle = document.createElement('div');
        summaryToggle.className = 'summary-toggle';
        summaryToggle.innerHTML = `
            <span>Order Summary (${document.querySelectorAll('.order-items > div').length} items)</span>
            <i class="fas fa-chevron-down"></i>
        `;

        const orderItems = document.querySelector('.order-items');
        orderItems.parentNode.insertBefore(summaryToggle, orderItems);

        summaryToggle.addEventListener('click', function() {
            orderItems.classList.toggle('expanded');
            this.classList.toggle('active');
        });

        // Wrap order summary in sticky container
        const orderSummaryCard = document.querySelector('.col-lg-4 .card');
        const wrapper = document.createElement('div');
        wrapper.className = 'order-summary-wrapper';
        orderSummaryCard.parentNode.insertBefore(wrapper, orderSummaryCard);
        wrapper.appendChild(orderSummaryCard);
    }
});
</script>

<?php include '../includes/layouts/footer.php'; ?> 