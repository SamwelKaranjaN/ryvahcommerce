<?php
require_once '../includes/bootstrap.php';
require_once '../includes/cart.php';
require_once '../config/stripe.php';

// Define Stripe public key
define('STRIPE_PUBLIC_KEY', 'pk_test_your_stripe_public_key');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Check if we have shipping details in POST
if (empty($_POST)) {
    header('Location: checkout.php');
    exit;
}

// Store shipping details in session
$_SESSION['shipping_details'] = $_POST;

$cart_data = getCartItems();
$cart_items = $cart_data['items'];
$cart_total = $cart_data['total'];

// Get any payment errors from session
$payment_error = $_SESSION['payment_error'] ?? null;
unset($_SESSION['payment_error']);

include '../includes/layouts/header.php';
?>

<div class="container py-5">
    <!-- Add error alert container -->
    <div id="payment-error" class="alert alert-danger d-none" role="alert"></div>

    <div class="row mb-4">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index" class="text-decoration-none">Home</a></li>
                    <li class="breadcrumb-item"><a href="cart" class="text-decoration-none">Cart</a></li>
                    <li class="breadcrumb-item"><a href="checkout" class="text-decoration-none">Checkout</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Payment</li>
                </ol>
            </nav>
            <h2 class="mb-0">Select Payment Method</h2>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <?php if ($payment_error): ?>
            <div class="alert alert-danger mb-4">
                <?php echo htmlspecialchars($payment_error); ?>
            </div>
            <?php endif; ?>

            <!-- Progress Bar -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between checkout-steps">
                        <div class="step completed">
                            <div class="step-icon">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <div class="step-text">Cart</div>
                        </div>
                        <div class="step completed">
                            <div class="step-icon">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="step-text">Details</div>
                        </div>
                        <div class="step active">
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

            <!-- Payment Methods -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h5 class="card-title mb-4">
                        <i class="fas fa-credit-card me-2 text-primary"></i>Choose Payment Method
                    </h5>

                    <form id="payment-form" action="../includes/process_payment.php" method="POST">
                        <div class="payment-methods">
                            <!-- PayPal Option -->
                            <div class="payment-option mb-4">
                                <div class="payment-card" data-payment="paypal">
                                    <input type="radio" name="payment_method" value="paypal" id="paypal" class="d-none">
                                    <label for="paypal" class="card-body p-4">
                                        <div class="d-flex align-items-center">
                                            <img src="https://www.paypalobjects.com/webstatic/mktg/logo/pp_cc_mark_111x69.jpg"
                                                alt="PayPal" height="40" class="me-3">
                                            <div>
                                                <h6 class="mb-1">Pay with PayPal</h6>
                                                <p class="text-muted mb-0">
                                                    <small>Safe payment with your PayPal account</small>
                                                </p>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Credit Card Option -->
                            <div class="payment-option">
                                <div class="payment-card" data-payment="card">
                                    <input type="radio" name="payment_method" value="card" id="card" class="d-none">
                                    <label for="card" class="card-body p-4">
                                        <div class="d-flex align-items-center">
                                            <i class="far fa-credit-card fa-2x text-primary me-3"></i>
                                            <div>
                                                <h6 class="mb-1">Credit or Debit Card</h6>
                                                <p class="text-muted mb-0">
                                                    <small>Safe payment with Stripe</small>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="card-logos mt-3">
                                            <img src="https://b.stripecdn.com/site-srv/assets/img/v3/payment_methods-c0d30fdf5c2b6d108a4b6f5eb5a7a19e.png"
                                                alt="Accepted Cards" height="30">
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Back Button -->
            <div class="d-flex justify-content-between align-items-center mt-4">
                <a href="checkout" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Checkout
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
                                <img src="<?php echo htmlspecialchars($item['thumbs']); ?>" class="rounded me-3" alt=""
                                    style="width: 50px; height: 50px; object-fit: cover;">
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

                        <button type="button" class="btn btn-primary btn-lg w-100" id="pay-now-btn" disabled>
                            <i class="fas fa-lock me-2"></i>Pay Now
                        </button>

                        <div class="text-center mt-3">
                            <small class="text-muted">
                                <i class="fas fa-shield-alt me-1"></i>Secure Payment
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
                            <h6 class="mb-1">Secure Payment</h6>
                            <small class="text-muted">Your payment information is encrypted</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Existing styles... */

.payment-card {
    border: 2px solid #dee2e6;
    border-radius: 1rem;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-bottom: 1rem;
}

.payment-card:hover {
    border-color: #007bff;
    transform: translateY(-2px);
}

.payment-card label {
    cursor: pointer;
    margin: 0;
    width: 100%;
}

input[type="radio"]:checked+label .payment-card {
    border-color: #007bff;
    background-color: #f8f9fa;
}

.card-logos img {
    max-width: 100%;
    height: auto;
}

/* Mobile Responsiveness */
@media (max-width: 767.98px) {
    .payment-card {
        margin-bottom: 1rem;
    }

    .card-body {
        padding: 1rem;
    }

    .card-logos img {
        height: 24px;
    }
}

/* Additional mobile styles... */
</style>

<!-- Load Stripe.js -->
<script src="https://js.stripe.com/v3/"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const paymentForm = document.getElementById('payment-form');
    const payNowBtn = document.getElementById('pay-now-btn');
    const paymentCards = document.querySelectorAll('.payment-card');
    let selectedPaymentMethod = null;

    // Handle payment method selection
    paymentCards.forEach(card => {
        card.addEventListener('click', function() {
            const radio = this.querySelector('input[type="radio"]');
            radio.checked = true;
            selectedPaymentMethod = radio.value;

            // Enable pay now button
            payNowBtn.disabled = false;

            // Update UI to show selected card
            paymentCards.forEach(c => c.classList.remove('selected'));
            this.classList.add('selected');
        });
    });

    // Handle payment submission
    payNowBtn.addEventListener('click', async function(e) {
        e.preventDefault();

        if (!selectedPaymentMethod) {
            const errorDiv = document.getElementById('payment-error');
            errorDiv.textContent = 'Please select a payment method';
            errorDiv.classList.remove('d-none');
            return;
        }

        // Disable button to prevent double submission
        payNowBtn.disabled = true;

        try {
            const formData = new FormData();
            formData.append('payment_method', selectedPaymentMethod);

            // Add a flag to indicate this is a payment request
            formData.append('is_payment', '1');

            const response = await fetch('../includes/process_payment.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (!data.success) {
                throw new Error(data.message);
            }

            if (selectedPaymentMethod === 'paypal') {
                window.location.href = data.redirect_url;
            } else if (selectedPaymentMethod === 'card') {
                const stripe = Stripe('<?php echo STRIPE_PUBLIC_KEY; ?>');
                const {
                    error
                } = await stripe.redirectToCheckout({
                    sessionId: data.session_id
                });

                if (error) {
                    throw new Error(error.message);
                }
            }
        } catch (error) {
            console.error('Payment processing error:', error);
            const errorDiv = document.getElementById('payment-error');
            errorDiv.textContent = error.message ||
                'An unexpected error occurred. Please try again.';
            errorDiv.classList.remove('d-none');
            payNowBtn.disabled = false;
        }
    });

    // Clear error message when payment method changes
    paymentCards.forEach(card => {
        card.addEventListener('click', function() {
            const errorDiv = document.getElementById('payment-error');
            errorDiv.classList.add('d-none');
        });
    });
});
</script>

<?php include '../includes/layouts/footer.php'; ?>