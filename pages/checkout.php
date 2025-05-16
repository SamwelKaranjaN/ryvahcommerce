<?php
require_once '../includes/bootstrap.php';
require_once '../includes/cart.php';

try {
    // Get cart items first
    $cart_data = getCartItems();
    $cart_items = $cart_data['items'];
    $cart_total = $cart_data['total'];

    // Redirect to cart if empty
    if (empty($cart_items)) {
        header('Location: cart.php');
        exit;
    }

    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        // Store cart items in session temporarily
        $_SESSION['temp_cart'] = $cart_items;

        // Store current URL in session for redirect after login
        $_SESSION['redirect_after_login'] = 'checkout.php';

        // Redirect to login
        header('Location: login.php');
        exit;
    }

    // If user is logged in and has temp cart items, merge them
    if (isset($_SESSION['temp_cart'])) {
        try {
            // Merge temp cart items with current cart
            foreach ($_SESSION['temp_cart'] as $item) {
                // Add item to cart
                addToCart($item['id'], $item['quantity']);
            }
            // Clear temp cart
            unset($_SESSION['temp_cart']);
        } catch (Exception $e) {
            error_log("Error merging temp cart: " . $e->getMessage());
            // Continue with checkout even if merge fails
        }
    }

    include '../includes/layouts/header.php';
} catch (Exception $e) {
    error_log("Error in checkout.php: " . $e->getMessage());
    // Redirect to payment.php with error message instead of error.php
    header('Location: payment.php?error=' . urlencode('Unable to process checkout. Please try again.'));
    exit;
}
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

                    <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger">
                        <?php echo htmlspecialchars($_GET['error']); ?>
                    </div>
                    <?php endif; ?>

                    <form id="checkout-form" method="POST" action="payment.php" onsubmit="return validateForm()">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="full_name" name="full_name" required>
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

                            <div class="col-md-6">
                                <label class="form-label">City</label>
                                <input type="text" class="form-control" id="city" name="city" required>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">State</label>
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

                        <button type="submit" form="checkout-form" class="btn btn-primary btn-lg w-100">
                            <i class="fas fa-check me-2"></i>Complete Order
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function validateForm() {
    const requiredFields = ['full_name', 'email', 'phone', 'address', 'city', 'state', 'postal_code'];
    let isValid = true;

    requiredFields.forEach(field => {
        const input = document.getElementById(field);
        if (!input.value.trim()) {
            input.classList.add('is-invalid');
            isValid = false;
        } else {
            input.classList.remove('is-invalid');
        }
    });

    if (!isValid) {
        alert('Please fill in all required fields.');
    }

    return isValid;
}

// Auto-fill user information if available
fetch('../includes/get_user_info.php')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('full_name').value = data.data.full_name || '';
            document.getElementById('email').value = data.data.email || '';
            document.getElementById('phone').value = data.data.phone || '';
            document.getElementById('address').value = data.data.address || '';
            document.getElementById('city').value = data.data.city || '';
            document.getElementById('state').value = data.data.state || '';
            document.getElementById('postal_code').value = data.data.postal_code || '';
        }
    })
    .catch(error => console.error('Error fetching user info:', error));
</script>

<style>
.is-invalid {
    border-color: #dc3545 !important;
}
</style>

<?php include '../includes/layouts/footer.php'; ?>