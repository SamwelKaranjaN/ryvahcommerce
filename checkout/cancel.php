<?php
require_once '../includes/bootstrap.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../pages/login.php');
    exit;
}

include '../includes/layouts/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-xl-6">
            <div class="text-center mb-5">
                <div class="cancel-icon mb-4">
                    <i class="fas fa-times-circle fa-5x text-warning"></i>
                </div>
                <h1 class="text-warning mb-3">Payment Cancelled</h1>
                <p class="lead text-muted">Your payment has been cancelled and no charges were made to your account.</p>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 text-center">
                    <h5 class="card-title mb-3">What happened?</h5>
                    <p class="text-muted mb-4">
                        You cancelled the payment process before it was completed. Your order was not processed
                        and your cart items are still available.
                    </p>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Don't worry!</strong> Your cart items are still saved and you can complete your purchase
                        anytime.
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-center mt-4">
                        <a href="../pages/cart.php" class="btn btn-primary me-md-2">
                            <i class="fas fa-shopping-cart me-2"></i>Return to Cart
                        </a>
                        <a href="index.php" class="btn btn-outline-primary">
                            <i class="fas fa-redo me-2"></i>Try Again
                        </a>
                    </div>

                    <div class="mt-4 pt-3 border-top">
                        <h6 class="text-muted">Need help with your order?</h6>
                        <p class="small text-muted mb-3">
                            If you experienced any issues during checkout, our support team is here to help.
                        </p>
                        <a href="mailto:support@ryvahcommerce.com" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-envelope me-2"></i>Contact Support
                        </a>
                    </div>
                </div>
            </div>

            <!-- Alternative Payment Methods -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-body p-4">
                    <h6 class="card-title">Having trouble with PayPal?</h6>
                    <p class="text-muted small mb-3">
                        We understand that payment issues can be frustrating. Here are some alternatives:
                    </p>

                    <div class="row text-center">
                        <div class="col-md-4 mb-3">
                            <div class="p-3 border rounded">
                                <i class="fas fa-credit-card fa-2x text-muted mb-2"></i>
                                <h6 class="small">Credit Cards</h6>
                                <p class="text-muted small mb-0">Coming Soon</p>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="p-3 border rounded">
                                <i class="fab fa-paypal fa-2x text-primary mb-2"></i>
                                <h6 class="small">PayPal</h6>
                                <p class="text-muted small mb-0">Available Now</p>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="p-3 border rounded">
                                <i class="fas fa-university fa-2x text-muted mb-2"></i>
                                <h6 class="small">Bank Transfer</h6>
                                <p class="text-muted small mb-0">Coming Soon</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="text-center mt-4">
                <div class="row">
                    <div class="col-md-4">
                        <a href="../pages/index.php" class="text-decoration-none">
                            <i class="fas fa-home text-muted"></i>
                            <div class="small text-muted mt-1">Homepage</div>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="../pages/products.php" class="text-decoration-none">
                            <i class="fas fa-th-large text-muted"></i>
                            <div class="small text-muted mt-1">Browse Products</div>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="../pages/account/index.php" class="text-decoration-none">
                            <i class="fas fa-user text-muted"></i>
                            <div class="small text-muted mt-1">My Account</div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.cancel-icon {
    animation: pulse 2s infinite;
}

@keyframes pulse {
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

.card {
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1) !important;
}

.btn {
    transition: all 0.2s ease;
}

.btn:hover {
    transform: translateY(-1px);
}

/* Payment method cards */
.border.rounded {
    transition: all 0.2s ease;
}

.border.rounded:hover {
    border-color: #007bff !important;
    background-color: rgba(0, 123, 255, 0.05);
}

/* Quick links */
a.text-decoration-none:hover {
    color: #007bff !important;
}

a.text-decoration-none:hover i {
    color: #007bff !important;
}

a.text-decoration-none:hover .text-muted {
    color: #007bff !important;
}
</style>

<script>
// Clear any stored checkout data since payment was cancelled
localStorage.removeItem('checkoutData');

// Optional: Track cancellation for analytics
console.log('Payment cancelled at:', new Date().toISOString());

// Show a brief message about what to do next
setTimeout(function() {
    const alertDiv = document.querySelector('.alert-info');
    if (alertDiv) {
        alertDiv.style.animation = 'highlight 2s ease-in-out';
    }
}, 1000);

// Add highlight animation
const style = document.createElement('style');
style.textContent = `
    @keyframes highlight {
        0% { background-color: #d1ecf1; }
        50% { background-color: #b3d9e6; }
        100% { background-color: #d1ecf1; }
    }
`;
document.head.appendChild(style);

// Auto-redirect timer (optional)
let redirectTimer = null;

function startAutoRedirect() {
    let seconds = 30;
    const timerElement = document.createElement('div');
    timerElement.className = 'text-muted small mt-3';
    timerElement.innerHTML = `Automatically redirecting to cart in <span id="countdown">${seconds}</span> seconds...`;

    document.querySelector('.card-body').appendChild(timerElement);

    redirectTimer = setInterval(function() {
        seconds--;
        document.getElementById('countdown').textContent = seconds;

        if (seconds <= 0) {
            clearInterval(redirectTimer);
            window.location.href = '../pages/cart.php';
        }
    }, 1000);
}

// Uncomment the line below if you want auto-redirect
// startAutoRedirect();

// Cancel auto-redirect if user interacts with the page
document.addEventListener('click', function() {
    if (redirectTimer) {
        clearInterval(redirectTimer);
        const timerElement = document.querySelector('.text-muted.small.mt-3');
        if (timerElement) {
            timerElement.remove();
        }
    }
});
</script>

<?php include '../includes/layouts/footer.php'; ?>