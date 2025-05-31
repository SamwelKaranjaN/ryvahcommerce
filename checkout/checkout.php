<?php


require_once '../includes/bootstrap.php';
require_once '../includes/cart.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../page/login.php?redirect=checkout');
    exit;
}

$user_id = $_SESSION['user_id'];

// Get cart data with error handling
try {
    $cart_data = getCartItems();
    if (!$cart_data || empty($cart_data['items'])) {
        throw new Exception('Cart is empty');
    }
    $cart_items = $cart_data['items'];
    $cart_total = $cart_data['total'];
} catch (Exception $e) {
    error_log('Cart Error: ' . $e->getMessage());
    header('Location: ../cart.php?error=empty');
    exit;
}

// Calculate tax
$tax_rates = [];
$tax_amount = 0;
$stmt = $conn->prepare("SELECT product_type, tax_rate FROM tax_settings WHERE is_active = 1");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $tax_rates[$row['product_type']] = $row['tax_rate'];
}

foreach ($cart_items as $item) {
    if (isset($tax_rates[$item['type']])) {
        $item_tax = ($item['price'] * $item['quantity']) * ($tax_rates[$item['type']] / 100);
        $tax_amount += $item_tax;
    }
}
$grand_total = $cart_total + $tax_amount;

// Get user addresses
$addresses = [];
$stmt = $conn->prepare("SELECT * FROM addresses WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $addresses[] = $row;
}
$default_address = array_filter($addresses, function ($addr) {
    return $addr['is_default'];
});
$default_address = !empty($default_address) ? reset($default_address) : null;

// Add CSRF token generation
$csrf_token = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrf_token;

// Get PayPal client ID from config
$paypal_config = require __DIR__ . '/config/paypal.php';
$paypal_client_id = $paypal_config['client_id'];

// Debug information
error_log("PayPal Client ID: " . $paypal_client_id);
error_log("Cart Total: " . $grand_total);
error_log("CSRF Token: " . $csrf_token);

include '../includes/layouts/header.php';
?>

<div class="container py-5">
    <h2 class="mb-4">Checkout</h2>
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="mb-4">Shipping Address</h5>
                    <form id="checkout-form">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="street" class="form-label">Street</label>
                                <input type="text" class="form-control" id="street" name="street"
                                    value="<?php echo htmlspecialchars($default_address['street'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="city" class="form-label">City</label>
                                <input type="text" class="form-control" id="city" name="city"
                                    value="<?php echo htmlspecialchars($default_address['city'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="state" class="form-label">State</label>
                                <input type="text" class="form-control" id="state" name="state"
                                    value="<?php echo htmlspecialchars($default_address['state'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="postal_code" class="form-label">Postal Code</label>
                                <input type="text" class="form-control" id="postal_code" name="postal_code"
                                    value="<?php echo htmlspecialchars($default_address['postal_code'] ?? ''); ?>"
                                    required>
                            </div>
                            <div class="col-md-6">
                                <label for="country" class="form-label">Country</label>
                                <input type="text" class="form-control" id="country" name="country"
                                    value="<?php echo htmlspecialchars($default_address['country'] ?? ''); ?>" required>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="same_billing"
                                        name="same_billing" checked>
                                    <label class="form-check-label" for="same_billing">
                                        Billing address same as shipping
                                    </label>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h5 class="mb-4">Order Items</h5>
                    <?php foreach ($cart_items as $item): ?>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h6 class="mb-1"><?php echo htmlspecialchars($item['name']); ?></h6>
                            <small class="text-muted">Quantity: <?php echo $item['quantity']; ?></small>
                        </div>
                        <div>
                            $<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm sticky-top" style="top: 2rem;">
                <div class="card-body p-4">
                    <h5 class="card-title mb-4">Order Summary</h5>
                    <div class="d-flex justify-content-between mb-3">
                        <span>Subtotal</span>
                        <span>$<?php echo number_format($cart_total, 2); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span>Tax</span>
                        <span>$<?php echo number_format($tax_amount, 2); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span>Shipping</span>
                        <span class="text-success">Free</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-4">
                        <strong>Total</strong>
                        <strong>$<?php echo number_format($grand_total, 2); ?></strong>
                    </div>
                    <?php if (!empty($paypal_client_id)): ?>
                    <div id="paypal-button-container"></div>
                    <div id="paypal-error" class="alert alert-danger d-none"></div>
                    <?php else: ?>
                    <div class="alert alert-danger">PayPal is not available. Please contact support.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Debug information -->
<div id="debug-info" style="display: none;">
    <pre>
    PayPal Client ID: <?php echo htmlspecialchars($paypal_client_id); ?>
    Cart Total: <?php echo $grand_total; ?>
    CSRF Token: <?php echo htmlspecialchars($csrf_token); ?>
    </pre>
</div>

<!-- Load PayPal SDK -->
<script>
// Function to load PayPal SDK
function loadPayPalSDK() {
    console.log('Starting PayPal SDK load...');
    return new Promise((resolve, reject) => {
        const script = document.createElement('script');
        script.src = `https://www.paypal.com/sdk/js?client-id=<?php echo $paypal_client_id; ?>&currency=USD`;
        script.async = true;

        script.onload = () => {
            console.log('PayPal SDK loaded successfully');
            resolve();
        };

        script.onerror = (error) => {
            console.error('Failed to load PayPal SDK:', error);
            document.getElementById('paypal-error').classList.remove('d-none');
            document.getElementById('paypal-error').textContent =
                'Failed to load PayPal. Please refresh the page.';
            reject(error);
        };

        document.body.appendChild(script);
    });
}

// Initialize PayPal buttons
async function initPayPal() {
    try {
        console.log('Starting PayPal initialization...');

        // Validate cart data
        if (!<?php echo json_encode($cart_items); ?> || !<?php echo $grand_total; ?>) {
            throw new Error('Invalid cart data');
        }

        // Wait for PayPal SDK to load
        await loadPayPalSDK();

        if (typeof paypal === 'undefined') {
            throw new Error('PayPal SDK not loaded');
        }

        console.log('PayPal SDK loaded, rendering buttons...');

        // Render PayPal buttons
        paypal.Buttons({
                style: {
                    layout: 'vertical',
                    color: 'blue',
                    shape: 'rect',
                    label: 'pay'
                },
                createOrder: async function(data, actions) {
                    console.log('Creating order...');
                    try {
                        const orderData = {
                            items: <?php echo json_encode($cart_items); ?>,
                            total: <?php echo $grand_total; ?>,
                            tax: <?php echo $tax_amount; ?>
                        };
                        console.log('Sending order data:', orderData);

                        const response = await fetch('create_order.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-Token': '<?php echo $csrf_token; ?>'
                            },
                            body: JSON.stringify(orderData)
                        });

                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }

                        const responseData = await response.json();
                        console.log('Order creation response:', responseData);

                        if (!responseData.success) {
                            throw new Error(responseData.message || 'Failed to create order');
                        }

                        return responseData.order_id;
                    } catch (error) {
                        console.error('Error creating order:', error);
                        document.getElementById('paypal-error').classList.remove('d-none');
                        document.getElementById('paypal-error').textContent = error.message ||
                            'Failed to create order. Please try again.';
                        throw error;
                    }
                },
                onApprove: async function(data, actions) {
                    console.log('Payment approved:', data);
                    try {
                        const formData = new FormData(document.getElementById('checkout-form'));
                        formData.append('action', 'process');
                        formData.append('order_id', data.orderID);
                        formData.append('csrf_token', '<?php echo $csrf_token; ?>');

                        console.log('Processing payment...');
                        const response = await fetch('process.php', {
                            method: 'POST',
                            body: formData
                        });

                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }

                        const result = await response.json();
                        console.log('Process response:', result);

                        if (result.success) {
                            window.location.href = 'success.php?order_id=' + result.order_id;
                        } else {
                            throw new Error(result.message || 'Payment processing failed');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        document.getElementById('paypal-error').classList.remove('d-none');
                        document.getElementById('paypal-error').textContent = error.message ||
                            'Payment processing failed. Please try again.';
                    }
                },
                onCancel: function(data) {
                    console.log('Payment cancelled:', data);
                    document.getElementById('paypal-error').classList.remove('d-none');
                    document.getElementById('paypal-error').textContent =
                        'Payment was cancelled. Please try again.';
                },
                onError: function(err) {
                    console.error('PayPal Error:', err);
                    document.getElementById('paypal-error').classList.remove('d-none');
                    document.getElementById('paypal-error').textContent =
                        'An error occurred with PayPal. Please try again.';
                }
            }).render('#paypal-button-container')
            .then(function() {
                console.log('PayPal buttons rendered successfully');
            })
            .catch(function(error) {
                console.error('Failed to render PayPal buttons:', error);
                document.getElementById('paypal-button-container').innerHTML =
                    '<div class="alert alert-danger">Failed to load PayPal buttons. Please refresh the page.</div>';
            });
    } catch (error) {
        console.error('PayPal initialization error:', error);
        document.getElementById('paypal-button-container').innerHTML =
            '<div class="alert alert-danger">Failed to initialize PayPal. Please refresh the page.</div>';
    }
}

// Only initialize PayPal if client ID is present
const paypalClientId = '<?php echo $paypal_client_id; ?>';
if (paypalClientId) {
    window.addEventListener('load', () => {
        console.log('Page loaded, initializing PayPal...');
        // Disable PayPal button until form is valid
        let paypalBtnRendered = false;

        function validateForm() {
            const form = document.getElementById('checkout-form');
            let valid = true;
            ['street', 'city', 'state', 'postal_code', 'country'].forEach(id => {
                if (!form[id].value.trim()) valid = false;
            });
            console.log('Form validation result:', valid);
            return valid;
        }

        function renderPayPalIfValid() {
            if (validateForm() && !paypalBtnRendered) {
                console.log('Form is valid, rendering PayPal buttons...');
                initPayPal();
                paypalBtnRendered = true;
            }
        }
        document.getElementById('checkout-form').addEventListener('input', renderPayPalIfValid);
        renderPayPalIfValid();
    });
} else {
    console.error('PayPal client ID is missing');
    document.getElementById('paypal-error').classList.remove('d-none');
    document.getElementById('paypal-error').textContent = 'PayPal is not configured. Please contact support.';
}
</script>

<?php include '../includes/layouts/footer.php'; ?>