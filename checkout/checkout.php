<?php
session_start();
require_once '../config/database.php';
require_once '../vendor/autoload.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$conn = getDBConnection();

// Get cart items
$sql = "SELECT c.*, p.name, p.price, p.type, p.thumbs 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$total = 0;
$items = [];

while ($row = $result->fetch_assoc()) {
    $items[] = $row;
    $total += $row['price'] * $row['quantity'];
}

// PayPal Configuration
$paypal_client_id = 'AfnlzZIeFsOcmqTOfERncgTQJqRV6vo6eLHfP1zf7G2S7WwSLV6-uUdvaK99zQ6mNk5D8A2qpjQbhkhE';
$paypal_secret = 'EJWttkx-dg39VMrg4C76-vLfVMT8Fg3DFtpKDD54skY-_AoHMlA-6iBkObGoNISXCsvJUiVgbzO6gGmO';

// Stripe Configuration
$stripe_secret_key = 'YOUR_STRIPE_SECRET_KEY';
$stripe_publishable_key = 'YOUR_STRIPE_PUBLISHABLE_KEY';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Ryvah Commerce</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://js.stripe.com/v3/"></script>
    <script src="https://www.paypal.com/sdk/js?client-id=<?php echo $paypal_client_id; ?>"></script>
    <style>
    .checkout-steps {
        position: relative;
        margin: 2rem 0;
    }

    .checkout-steps::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 0;
        right: 0;
        height: 2px;
        background: #e9ecef;
        z-index: 1;
    }

    .step {
        position: relative;
        z-index: 2;
        text-align: center;
        width: 33.33%;
    }

    .step-icon {
        width: 40px;
        height: 40px;
        background: #fff;
        border: 2px solid #e9ecef;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 0.5rem;
    }

    .step.active .step-icon {
        background: #0d6efd;
        border-color: #0d6efd;
        color: #fff;
    }

    .step-text {
        font-size: 0.875rem;
        color: #6c757d;
    }

    .step.active .step-text {
        color: #0d6efd;
        font-weight: 500;
    }

    .main-content {
        min-height: calc(100vh - 300px);
        /* Adjust based on your header/footer height */
        padding: 2rem 0;
    }
    </style>
</head>

<body>
    <?php include '../includes/header.php'; ?>

    <div class="main-content">
        <div class="container">
            <div class="row mb-4">
                <div class="col">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="../index.php" class="text-decoration-none">Home</a>
                            </li>
                            <li class="breadcrumb-item"><a href="../pages/cart.php"
                                    class="text-decoration-none">Cart</a></li>
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
                            </div>
                        </div>
                    </div>

                    <!-- Billing Information -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-4">
                            <h5 class="card-title mb-4">
                                <i class="fas fa-user me-2 text-primary"></i>Billing Information
                            </h5>

                            <form id="billing-form">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Full Name</label>
                                        <input type="text" class="form-control" id="billing_name" name="billing_name"
                                            required>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Email</label>
                                        <input type="email" class="form-control" id="billing_email" name="billing_email"
                                            required>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Phone</label>
                                        <input type="tel" class="form-control" id="billing_phone" name="billing_phone"
                                            required>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">Billing Address</label>
                                        <input type="text" class="form-control" id="billing_address"
                                            name="billing_address" required>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">City</label>
                                        <input type="text" class="form-control" id="billing_city" name="billing_city"
                                            required>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">State</label>
                                        <input type="text" class="form-control" id="billing_state" name="billing_state"
                                            required>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Postal Code</label>
                                        <input type="text" class="form-control" id="billing_postal"
                                            name="billing_postal" required>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Payment Options -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <h5 class="card-title mb-4">
                                <i class="fas fa-credit-card me-2 text-primary"></i>Payment Options
                            </h5>

                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <h6 class="card-title">PayPal</h6>
                                            <div id="paypal-button-container"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <h6 class="card-title">Credit Card (Stripe)</h6>
                                            <form id="payment-form">
                                                <div class="mb-3">
                                                    <label for="card-element" class="form-label">Card Details</label>
                                                    <div id="card-element" class="form-control"></div>
                                                    <div id="card-errors" class="text-danger mt-2"></div>
                                                </div>
                                                <button type="submit" class="btn btn-primary w-100">Pay with
                                                    Card</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Back to Cart Button -->
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <a href="../pages/cart.php" class="btn btn-outline-primary">
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
                                <?php foreach ($items as $item): ?>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="d-flex align-items-center">
                                        <img src="<?php echo htmlspecialchars($item['thumbs']); ?>" class="rounded me-3"
                                            alt="" style="width: 50px; height: 50px; object-fit: cover;">
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
                                    <span>$<?php echo number_format($total, 2); ?></span>
                                </div>

                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Shipping</span>
                                    <span class="text-success">Free</span>
                                </div>

                                <hr>

                                <div class="d-flex justify-content-between mb-4">
                                    <strong>Total</strong>
                                    <div class="h4 mb-0 text-primary">$<?php echo number_format($total, 2); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Initialize Stripe
    const stripe = Stripe('<?php echo $stripe_publishable_key; ?>');
    const elements = stripe.elements();
    const card = elements.create('card');
    card.mount('#card-element');

    // Handle Stripe form submission
    const form = document.getElementById('payment-form');
    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        // Validate billing form
        const billingForm = document.getElementById('billing-form');
        if (!validateBillingForm()) {
            alert('Please fill in all billing information fields.');
            return;
        }

        const {
            token,
            error
        } = await stripe.createToken(card);

        if (error) {
            const errorElement = document.getElementById('card-errors');
            errorElement.textContent = error.message;
        } else {
            // Get billing information
            const billingInfo = {
                name: document.getElementById('billing_name').value,
                email: document.getElementById('billing_email').value,
                phone: document.getElementById('billing_phone').value,
                address: document.getElementById('billing_address').value,
                city: document.getElementById('billing_city').value,
                state: document.getElementById('billing_state').value,
                postal_code: document.getElementById('billing_postal').value
            };

            const response = await fetch('process_payment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    token: token.id,
                    amount: <?php echo $total * 100; ?>,
                    payment_method: 'stripe',
                    billing_info: billingInfo
                })
            });

            const result = await response.json();
            if (result.success) {
                window.location.href = 'order_success.php?order_id=' + result.order_id;
            } else {
                alert('Payment failed: ' + result.message);
            }
        }
    });

    // Initialize PayPal
    paypal.Buttons({
        createOrder: function(data, actions) {
            // Validate billing form
            if (!validateBillingForm()) {
                alert('Please fill in all billing information fields.');
                return;
            }

            return actions.order.create({
                purchase_units: [{
                    amount: {
                        value: '<?php echo $total; ?>'
                    }
                }]
            });
        },
        onApprove: function(data, actions) {
            return actions.order.capture().then(function(details) {
                // Get billing information
                const billingInfo = {
                    name: document.getElementById('billing_name').value,
                    email: document.getElementById('billing_email').value,
                    phone: document.getElementById('billing_phone').value,
                    address: document.getElementById('billing_address').value,
                    city: document.getElementById('billing_city').value,
                    state: document.getElementById('billing_state').value,
                    postal_code: document.getElementById('billing_postal').value
                };

                fetch('process_payment.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            payment_id: details.id,
                            amount: <?php echo $total; ?>,
                            payment_method: 'paypal',
                            billing_info: billingInfo
                        })
                    })
                    .then(response => response.json())
                    .then(result => {
                        if (result.success) {
                            window.location.href = 'order_success.php?order_id=' + result
                                .order_id;
                        } else {
                            alert('Payment failed: ' + result.message);
                        }
                    });
            });
        },
        onCancel: function() {
            window.location.href = 'payment_cancelled.php';
        }
    }).render('#paypal-button-container');

    // Function to validate billing form
    function validateBillingForm() {
        const requiredFields = [
            'billing_name', 'billing_email', 'billing_phone',
            'billing_address', 'billing_city', 'billing_state', 'billing_postal'
        ];

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

        return isValid;
    }

    // Auto-fill billing information if available
    fetch('../includes/get_user_info.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('billing_name').value = data.data.full_name || '';
                document.getElementById('billing_email').value = data.data.email || '';
                document.getElementById('billing_phone').value = data.data.phone || '';
                document.getElementById('billing_address').value = data.data.address || '';
                document.getElementById('billing_city').value = data.data.city || '';
                document.getElementById('billing_state').value = data.data.state || '';
                document.getElementById('billing_postal').value = data.data.postal_code || '';
            }
        })
        .catch(error => console.error('Error fetching user info:', error));
    </script>
</body>

</html>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>