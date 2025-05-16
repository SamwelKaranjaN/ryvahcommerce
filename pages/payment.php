<?php
require_once '../includes/bootstrap.php';
require_once '../includes/cart.php';
require_once '../includes/order/OrderProcessor.php';
require_once '../includes/payment/PaymentProcessor.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debug function
function debug($data, $label = '')
{
    error_log(($label ? $label . ': ' : '') . print_r($data, true));
}

// Debug request method and data
debug($_SERVER['REQUEST_METHOD'], 'Request Method');
debug($_POST, 'POST Data');
debug($_SESSION, 'Session Data');

// Step 1: Validate session and POST data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    debug('Processing POST request');

    // Validate required fields
    $required_fields = ['full_name', 'email', 'phone', 'address', 'city', 'state', 'postal_code'];
    $missing_fields = [];

    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            $missing_fields[] = $field;
        }
    }

    if (!empty($missing_fields)) {
        debug($missing_fields, 'Missing Required Fields');
        header('Location: checkout.php?error=' . urlencode('Missing required fields: ' . implode(', ', $missing_fields)));
        exit;
    }

    // Get cart items
    $cart_data = getCartItems();
    debug($cart_data, 'Cart Data Retrieved');

    if (empty($cart_data['items'])) {
        debug('Cart is empty');
        header('Location: checkout.php?error=' . urlencode('Your cart is empty'));
        exit;
    }

    // Validate user session
    if (!isset($_SESSION['user_id'])) {
        debug('No user ID in session');
        header('Location: login.php?redirect=payment.php');
        exit;
    }

    // Get user data
    $user = [
        'id' => $_SESSION['user_id'],
        'email' => $_POST['email'],
        'full_name' => $_POST['full_name']
    ];

    // Create shipping details
    $shipping_details = [
        'full_name' => $_POST['full_name'],
        'email' => $_POST['email'],
        'phone' => $_POST['phone'],
        'address' => $_POST['address'],
        'city' => $_POST['city'],
        'state' => $_POST['state'],
        'postal_code' => $_POST['postal_code']
    ];

    try {
        // Create payment processor
        $paymentProcessor = new PaymentProcessor($conn, $user, $cart_data, $shipping_details);

        // Validate payment data
        $validation = $paymentProcessor->validatePaymentData();
        if (!$validation['valid']) {
            throw new Exception('Invalid payment data: ' . implode(', ', $validation['errors']));
        }

        // Create pending order
        $result = $paymentProcessor->createPendingOrder();
        debug($result, 'Pending Order Creation Result');

        if ($result['success']) {
            // Store invoice number in session
            $_SESSION['current_invoice'] = $result['invoice_number'];
            header('Location: payment.php?invoice=' . urlencode($result['invoice_number']));
            exit;
        } else {
            throw new Exception($result['message']);
        }
    } catch (Exception $e) {
        error_log("Error creating pending order: " . $e->getMessage());
        header('Location: checkout.php?error=' . urlencode($e->getMessage()));
        exit;
    }
}

// Step 2: Handle payment processing
$payment_error = '';
$invoice_number = $_GET['invoice'] ?? null;

if (isset($_GET['pay']) && $_GET['pay'] === 'success' && $invoice_number) {
    debug($_GET, 'Payment Success Parameters');

    // Validate user session
    if (!isset($_SESSION['user_id'])) {
        debug('No user ID in session');
        header('Location: login.php?redirect=payment.php');
        exit;
    }

    // Get user data
    $user = [
        'id' => $_SESSION['user_id'],
        'email' => $_SESSION['user_email'] ?? '',
        'full_name' => $_SESSION['user_name'] ?? ''
    ];

    try {
        // Get order data from database
        $stmt = $conn->prepare("
            SELECT o.*, u.email, u.full_name
            FROM orders o
            JOIN users u ON o.user_id = u.id
            WHERE o.invoice_number = ? AND o.user_id = ?
        ");
        $stmt->bind_param("si", $invoice_number, $user['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $order = $result->fetch_assoc();

        if (!$order) {
            throw new Exception('Order not found');
        }

        // Get cart items
        $cart_data = getCartItems();

        // Create payment processor
        $paymentProcessor = new PaymentProcessor($conn, $user, $cart_data, [
            'full_name' => $order['full_name'],
            'email' => $order['email'],
            'phone' => '', // These would be stored in the order
            'address' => '',
            'city' => '',
            'state' => '',
            'postal_code' => ''
        ]);

        // Process payment
        $result = $paymentProcessor->processPayment($invoice_number);
        debug($result, 'Payment Processing Result');

        if ($result['success']) {
            // Clear session data
            unset($_SESSION['current_invoice']);
            header('Location: order-confirmation.php?invoice=' . urlencode($invoice_number));
            exit;
        } else {
            $payment_error = $result['message'];
            debug($payment_error, 'Payment Processing Error');
        }
    } catch (Exception $e) {
        error_log("Error processing payment: " . $e->getMessage());
        $payment_error = "An error occurred while processing your payment. Please try again.";
        debug($e->getMessage(), 'Exception in Payment Processing');
    }
} elseif (isset($_GET['pay']) && $_GET['pay'] === 'fail') {
    $payment_error = 'Payment failed. Please try again.';
}

// Step 3: Get order details for display
$order = null;
if ($invoice_number) {
    $stmt = $conn->prepare("
        SELECT o.*, u.email, u.full_name
        FROM orders o
        JOIN users u ON o.user_id = u.id
        WHERE o.invoice_number = ? AND o.user_id = ?
    ");
    $stmt->bind_param("si", $invoice_number, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();

    if ($order) {
        // Get order items
        $stmt = $conn->prepare("
            SELECT oi.*, p.name
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = ?
        ");
        $stmt->bind_param("i", $order['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $order_items = [];
        while ($item = $result->fetch_assoc()) {
            $order_items[] = $item;
        }
    }
}

// Step 4: Render invoice and payment options
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <?php include '../includes/header.php'; ?>
    <main class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <h2 class="mb-4">Review & Payment</h2>
                <?php if ($payment_error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?php echo htmlspecialchars($payment_error); ?>
                    </div>
                <?php endif; ?>
                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?php echo htmlspecialchars($_GET['error']); ?>
                    </div>
                <?php endif; ?>
                <?php if ($order): ?>
                    <div class="card mb-4">
                        <div class="card-header">
                            <strong>Invoice #<?php echo htmlspecialchars($order['invoice_number']); ?></strong>
                        </div>
                        <div class="card-body">
                            <p><strong>Order for:</strong>
                                <?php echo htmlspecialchars($order['full_name']); ?><br>
                                <strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?><br>
                                <strong>Shipping:</strong>
                                <?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?>
                            </p>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Qty</th>
                                        <th>Price</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($order_items as $item): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                                            <td><?php echo (int)$item['quantity']; ?></td>
                                            <td>$<?php echo number_format($item['price'], 2); ?></td>
                                            <td>$<?php echo number_format($item['subtotal'], 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="3" class="text-end">Total</th>
                                        <th>$<?php echo number_format($order['total_amount'], 2); ?></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    <div class="mb-4">
                        <h4>Choose Payment Method</h4>
                        <div class="d-flex gap-3">
                            <a href="payment.php?pay=success&invoice=<?php echo urlencode($order['invoice_number']); ?>"
                                class="btn btn-primary">
                                <i class="fab fa-paypal me-2"></i>Pay with PayPal (Simulated)
                            </a>
                            <a href="payment.php?pay=success&invoice=<?php echo urlencode($order['invoice_number']); ?>"
                                class="btn btn-secondary">
                                <i class="fas fa-credit-card me-2"></i>Pay with Debit Card (Simulated)
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        No order found. Please complete the checkout process.
                    </div>
                    <div class="text-center mt-4">
                        <a href="checkout.php" class="btn btn-primary">
                            <i class="fas fa-shopping-cart me-2"></i>Return to Checkout
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
    <?php include '../includes/footer.php'; ?>
</body>

</html>