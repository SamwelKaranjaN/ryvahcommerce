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

// Get order details from session
$order_details = isset($_SESSION['temp_order']) ? $_SESSION['temp_order'] : null;
$billing_info = isset($_SESSION['temp_billing']) ? $_SESSION['temp_billing'] : null;

if (!$order_details || !$billing_info) {
    header('Location: checkout.php');
    exit();
}

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
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - Ryvah Commerce</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_GOOGLE_MAPS_API_KEY&libraries=places"></script>
    <style>
        .confirmation-section {
            border-left: 4px solid #0d6efd;
            padding-left: 1rem;
            margin-bottom: 2rem;
        }

        .edit-btn {
            cursor: pointer;
            color: #0d6efd;
        }

        .edit-btn:hover {
            text-decoration: underline;
        }

        .validation-feedback {
            display: none;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        .is-invalid~.validation-feedback {
            display: block;
        }
    </style>
</head>

<body>
    <div class="container py-5">
        <div class="row mb-4">
            <div class="col">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="../index.php" class="text-decoration-none">Home</a></li>
                        <li class="breadcrumb-item"><a href="checkout.php" class="text-decoration-none">Checkout</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Order Confirmation</li>
                    </ol>
                </nav>
                <h2 class="mb-0">Order Confirmation</h2>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <!-- Billing Information -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-user me-2 text-primary"></i>Billing Information
                            </h5>
                            <button class="btn btn-link edit-btn" onclick="editBillingInfo()">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                        </div>

                        <div id="billing-display">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Name:</strong> <span id="display-name"></span></p>
                                    <p><strong>Email:</strong> <span id="display-email"></span></p>
                                    <p><strong>Phone:</strong> <span id="display-phone"></span></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Address:</strong> <span id="display-address"></span></p>
                                    <p><strong>City:</strong> <span id="display-city"></span></p>
                                    <p><strong>State:</strong> <span id="display-state"></span></p>
                                    <p><strong>Postal Code:</strong> <span id="display-postal"></span></p>
                                </div>
                            </div>
                        </div>

                        <form id="billing-form" style="display: none;">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" class="form-control" id="billing_name" name="billing_name"
                                        required>
                                    <div class="validation-feedback text-danger"></div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" id="billing_email" name="billing_email"
                                        required>
                                    <div class="validation-feedback text-danger"></div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Phone</label>
                                    <input type="tel" class="form-control" id="billing_phone" name="billing_phone"
                                        required>
                                    <div class="validation-feedback text-danger"></div>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Address</label>
                                    <input type="text" class="form-control" id="billing_address" name="billing_address"
                                        required>
                                    <div class="validation-feedback text-danger"></div>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">City</label>
                                    <input type="text" class="form-control" id="billing_city" name="billing_city"
                                        required>
                                    <div class="validation-feedback text-danger"></div>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">State</label>
                                    <input type="text" class="form-control" id="billing_state" name="billing_state"
                                        required>
                                    <div class="validation-feedback text-danger"></div>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Postal Code</label>
                                    <input type="text" class="form-control" id="billing_postal" name="billing_postal"
                                        required>
                                    <div class="validation-feedback text-danger"></div>
                                </div>

                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="save_address">
                                        <label class="form-check-label" for="save_address">
                                            Save this address for future orders
                                        </label>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <button type="button" class="btn btn-primary" onclick="saveBillingInfo()">Save
                                        Changes</button>
                                    <button type="button" class="btn btn-outline-secondary"
                                        onclick="cancelEdit()">Cancel</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

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

            <div class="col-lg-4">
                <!-- Payment Method -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h5 class="card-title mb-4">
                            <i class="fas fa-credit-card me-2 text-primary"></i>Payment Method
                        </h5>

                        <div id="payment-display">
                            <p><strong>Selected Method:</strong> <span id="display-payment-method"></span></p>
                        </div>

                        <form id="payment-form" style="display: none;">
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="paypal"
                                        value="paypal">
                                    <label class="form-check-label" for="paypal">
                                        <i class="fab fa-paypal me-2"></i>PayPal
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="stripe"
                                        value="stripe">
                                    <label class="form-check-label" for="stripe">
                                        <i class="fas fa-credit-card me-2"></i>Credit Card
                                    </label>
                                </div>
                            </div>

                            <div id="stripe-elements" style="display: none;">
                                <div class="mb-3">
                                    <label for="card-element" class="form-label">Card Details</label>
                                    <div id="card-element" class="form-control"></div>
                                    <div id="card-errors" class="text-danger mt-2"></div>
                                </div>
                            </div>

                            <div id="paypal-button-container" style="display: none;"></div>

                            <button type="button" class="btn btn-primary" onclick="savePaymentMethod()">Save
                                Changes</button>
                            <button type="button" class="btn btn-outline-secondary"
                                onclick="cancelPaymentEdit()">Cancel</button>
                        </form>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <button type="button" class="btn btn-primary btn-lg w-100 mb-3" onclick="confirmOrder()">
                            <i class="fas fa-check me-2"></i>Confirm Order
                        </button>
                        <a href="checkout.php" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-arrow-left me-2"></i>Back to Checkout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize Google Places Autocomplete
        function initAutocomplete() {
            const addressInput = document.getElementById('billing_address');
            const autocomplete = new google.maps.places.Autocomplete(addressInput, {
                componentRestrictions: {
                    country: ['us', 'ca']
                },
                fields: ['address_components', 'geometry', 'formatted_address'],
                types: ['address']
            });

            autocomplete.addListener('place_changed', function() {
                const place = autocomplete.getPlace();
                if (!place.geometry) return;

                // Fill in address components
                for (const component of place.address_components) {
                    const componentType = component.types[0];
                    switch (componentType) {
                        case 'street_number':
                            document.getElementById('billing_address').value = component.long_name;
                            break;
                        case 'route':
                            document.getElementById('billing_address').value += ' ' + component.long_name;
                            break;
                        case 'locality':
                            document.getElementById('billing_city').value = component.long_name;
                            break;
                        case 'administrative_area_level_1':
                            document.getElementById('billing_state').value = component.short_name;
                            break;
                        case 'postal_code':
                            document.getElementById('billing_postal').value = component.long_name;
                            break;
                    }
                }
            });
        }

        // Form validation
        function validateForm() {
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

            // Email validation
            const email = document.getElementById('billing_email').value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                document.getElementById('billing_email').classList.add('is-invalid');
                isValid = false;
            }

            // Phone validation
            const phone = document.getElementById('billing_phone').value;
            const phoneRegex = /^\+?[\d\s-]{10,}$/;
            if (!phoneRegex.test(phone)) {
                document.getElementById('billing_phone').classList.add('is-invalid');
                isValid = false;
            }

            // Postal code validation
            const postal = document.getElementById('billing_postal').value;
            const postalRegex = /^[A-Za-z]\d[A-Za-z][ -]?\d[A-Za-z]\d$/;
            if (!postalRegex.test(postal)) {
                document.getElementById('billing_postal').classList.add('is-invalid');
                isValid = false;
            }

            return isValid;
        }

        // Edit billing information
        function editBillingInfo() {
            document.getElementById('billing-display').style.display = 'none';
            document.getElementById('billing-form').style.display = 'block';
            initAutocomplete();
        }

        // Save billing information
        function saveBillingInfo() {
            if (!validateForm()) {
                alert('Please fill in all required fields correctly.');
                return;
            }

            const billingInfo = {
                name: document.getElementById('billing_name').value,
                email: document.getElementById('billing_email').value,
                phone: document.getElementById('billing_phone').value,
                address: document.getElementById('billing_address').value,
                city: document.getElementById('billing_city').value,
                state: document.getElementById('billing_state').value,
                postal: document.getElementById('billing_postal').value,
                save_address: document.getElementById('save_address').checked
            };

            // Update display
            document.getElementById('display-name').textContent = billingInfo.name;
            document.getElementById('display-email').textContent = billingInfo.email;
            document.getElementById('display-phone').textContent = billingInfo.phone;
            document.getElementById('display-address').textContent = billingInfo.address;
            document.getElementById('display-city').textContent = billingInfo.city;
            document.getElementById('display-state').textContent = billingInfo.state;
            document.getElementById('display-postal').textContent = billingInfo.postal;

            // Save to session
            fetch('save_billing.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(billingInfo)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('billing-display').style.display = 'block';
                        document.getElementById('billing-form').style.display = 'none';
                    } else {
                        alert('Error saving billing information: ' + data.message);
                    }
                });
        }

        // Cancel edit
        function cancelEdit() {
            document.getElementById('billing-display').style.display = 'block';
            document.getElementById('billing-form').style.display = 'none';
        }

        // Initialize payment method display
        function initPaymentDisplay() {
            const paymentMethod = localStorage.getItem('selected_payment_method') || 'paypal';
            document.getElementById('display-payment-method').textContent =
                paymentMethod === 'paypal' ? 'PayPal' : 'Credit Card';
        }

        // Edit payment method
        function editPaymentMethod() {
            document.getElementById('payment-display').style.display = 'none';
            document.getElementById('payment-form').style.display = 'block';
        }

        // Save payment method
        function savePaymentMethod() {
            const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
            localStorage.setItem('selected_payment_method', paymentMethod);

            document.getElementById('display-payment-method').textContent =
                paymentMethod === 'paypal' ? 'PayPal' : 'Credit Card';

            document.getElementById('payment-display').style.display = 'block';
            document.getElementById('payment-form').style.display = 'none';
        }

        // Cancel payment edit
        function cancelPaymentEdit() {
            document.getElementById('payment-display').style.display = 'block';
            document.getElementById('payment-form').style.display = 'none';
        }

        // Confirm order
        function confirmOrder() {
            if (!validateForm()) {
                alert('Please fill in all required fields correctly.');
                return;
            }

            const paymentMethod = localStorage.getItem('selected_payment_method');
            if (!paymentMethod) {
                alert('Please select a payment method.');
                return;
            }

            // Proceed with order confirmation
            window.location.href = 'process_payment.php';
        }

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            initPaymentDisplay();
            // Load saved billing information if available
            fetch('../includes/get_user_info.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('display-name').textContent = data.data.full_name || '';
                        document.getElementById('display-email').textContent = data.data.email || '';
                        document.getElementById('display-phone').textContent = data.data.phone || '';
                        document.getElementById('display-address').textContent = data.data.address || '';
                        document.getElementById('display-city').textContent = data.data.city || '';
                        document.getElementById('display-state').textContent = data.data.state || '';
                        document.getElementById('display-postal').textContent = data.data.postal_code || '';
                    }
                });
        });
    </script>
</body>

</html>