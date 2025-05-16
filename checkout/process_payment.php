<?php
session_start();
require_once '../config/database.php';
require_once '../vendor/autoload.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Check if billing information exists
if (!isset($_SESSION['temp_billing'])) {
    header('Location: checkout.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$conn = getDBConnection();

// PayPal Configuration
$paypal_client_id = 'AfnlzZIeFsOcmqTOfERncgTQJqRV6vo6eLHfP1zf7G2S7WwSLV6-uUdvaK99zQ6mNk5D8A2qpjQbhkhE';
$paypal_secret = 'EJWttkx-dg39VMrg4C76-vLfVMT8Fg3DFtpKDD54skY-_AoHMlA-6iBkObGoNISXCsvJUiVgbzO6gGmO';

// Stripe Configuration
$stripe_secret_key = 'YOUR_STRIPE_SECRET_KEY';
\Stripe\Stripe::setApiKey($stripe_secret_key);

// Get cart items and calculate total
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

// Format billing address
$billing_info = $_SESSION['temp_billing'];
$billing_address = sprintf(
    "%s, %s, %s %s, Phone: %s",
    $billing_info['address'],
    $billing_info['city'],
    $billing_info['state'],
    $billing_info['postal'],
    $billing_info['phone']
);

// Start transaction
$conn->begin_transaction();

try {
    // Create order record
    $sql = "INSERT INTO orders (user_id, total_amount, payment_status, billing_address, created_at) 
            VALUES (?, ?, 'pending', ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ids", $user_id, $total, $billing_address);
    $stmt->execute();
    $order_id = $conn->insert_id;

    // Insert order items
    $sql = "INSERT INTO order_items (order_id, product_id, quantity, price, subtotal) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    foreach ($items as $item) {
        $subtotal = $item['price'] * $item['quantity'];
        $stmt->bind_param(
            "iiids",
            $order_id,
            $item['product_id'],
            $item['quantity'],
            $item['price'],
            $subtotal
        );
        $stmt->execute();
    }

    // Store payment information
    $payment_method = $_POST['payment_method'] ?? 'paypal';
    $sql = "INSERT INTO order_payments (order_id, payment_method, amount, status, payment_date) 
            VALUES (?, ?, ?, 'pending', NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isd", $order_id, $payment_method, $total);
    $stmt->execute();

    // Add initial order status
    $sql = "INSERT INTO order_status_history (order_id, status, notes) 
            VALUES (?, 'pending', 'Order created')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();

    // Commit transaction
    $conn->commit();

    // Store order ID in session for payment processing
    $_SESSION['current_order_id'] = $order_id;

    // Redirect to appropriate payment processor
    if ($payment_method === 'paypal') {
        header('Location: process_paypal.php');
    } else {
        header('Location: process_stripe.php');
    }
    exit();
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    error_log("Error processing order: " . $e->getMessage());
    header('Location: checkout.php?error=1');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://js.stripe.com/v3/"></script>
    <script src="https://www.paypal.com/sdk/js?client-id=<?php echo $paypal_client_id; ?>"></script>
</head>

<body>
    <div class="container mt-5">
        <h2>Payment Options</h2>
        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h4>PayPal</h4>
                    </div>
                    <div class="card-body">
                        <div id="paypal-button-container"></div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4>Credit Card (Stripe)</h4>
                    </div>
                    <div class="card-body">
                        <form id="payment-form">
                            <div class="mb-3">
                                <label for="card-element" class="form-label">Credit or Debit Card</label>
                                <div id="card-element" class="form-control"></div>
                                <div id="card-errors" class="text-danger mt-2"></div>
                            </div>
                            <button type="submit" class="btn btn-primary">Pay with Card</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Initialize Stripe
    const stripe = Stripe('YOUR_STRIPE_PUBLISHABLE_KEY');
    const elements = stripe.elements();
    const card = elements.create('card');
    card.mount('#card-element');

    // Handle Stripe form submission
    const form = document.getElementById('payment-form');
    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const {
            token,
            error
        } = await stripe.createToken(card);

        if (error) {
            const errorElement = document.getElementById('card-errors');
            errorElement.textContent = error.message;
        } else {
            // Send token to server
            const response = await fetch('process_stripe.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    token: token.id,
                    amount: <?php echo $total * 100; ?>, // Convert to cents
                    order_id: <?php echo $order_id; ?>
                })
            });

            const result = await response.json();
            if (result.success) {
                window.location.href = 'order_success.php?order_id=' + <?php echo $order_id; ?>;
            } else {
                alert('Payment failed: ' + result.message);
            }
        }
    });

    // Initialize PayPal
    paypal.Buttons({
        createOrder: function(data, actions) {
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
                // Send payment details to server
                fetch('process_paypal.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            payment_id: details.id,
                            amount: <?php echo $total; ?>,
                            order_id: <?php echo $order_id; ?>
                        })
                    })
                    .then(response => response.json())
                    .then(result => {
                        if (result.success) {
                            window.location.href = 'order_success.php?order_id=' +
                                <?php echo $order_id; ?>;
                        } else {
                            alert('Payment failed: ' + result.message);
                        }
                    });
            });
        }
    }).render('#paypal-button-container');
    </script>
</body>

</html>