<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Get order ID from session or URL
$order_id = $_SESSION['current_order_id'] ?? $_GET['order_id'] ?? null;
$invoice_number = $_SESSION['current_invoice'] ?? $_GET['invoice'] ?? null;

if (!$order_id && !$invoice_number) {
    header('Location: ../index.php');
    exit();
}

$conn = getDBConnection();

// Get order details
$sql = "SELECT o.*, u.email, u.full_name 
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        WHERE " . ($order_id ? "o.id = ?" : "o.invoice_number = ?");
$stmt = $conn->prepare($sql);
$stmt->bind_param($order_id ? "i" : "s", $order_id ? $order_id : $invoice_number);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if (!$order) {
    header('Location: ../index.php');
    exit();
}

// Get order items
$sql = "SELECT oi.*, p.name, p.type, p.thumbs 
        FROM order_items oi 
        JOIN products p ON oi.product_id = p.id 
        WHERE oi.order_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order['id']);
$stmt->execute();
$result = $stmt->get_result();
$items = [];
while ($row = $result->fetch_assoc()) {
    $items[] = $row;
}

// Clear session data
unset($_SESSION['current_order_id']);
unset($_SESSION['current_invoice']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thank You - Order Confirmation</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <div class="container">
        <div class="thank-you-page">
            <h1>Thank You for Your Order!</h1>
            <div class="order-details">
                <h2>Order Details</h2>
                <p><strong>Order Number:</strong> <?php echo htmlspecialchars($order['invoice_number']); ?></p>
                <p><strong>Date:</strong> <?php echo date('F j, Y', strtotime($order['created_at'])); ?></p>
                <p><strong>Total Amount:</strong> $<?php echo number_format($order['total_amount'], 2); ?></p>
                <p><strong>Payment Method:</strong> <?php echo ucfirst($order['payment_method']); ?></p>
                <p><strong>Status:</strong> <?php echo ucfirst($order['payment_status']); ?></p>
            </div>

            <div class="order-items">
                <h2>Order Items</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td>
                                    <?php echo htmlspecialchars($item['name']); ?>
                                    <?php if ($item['type'] === 'digital'): ?>
                                        <br>
                                        <a href="../download.php?product_id=<?php echo $item['product_id']; ?>"
                                            class="download-link">
                                            Download
                                        </a>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td>$<?php echo number_format($item['price'], 2); ?></td>
                                <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="billing-info">
                <h2>Billing Information</h2>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($order['billing_name']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($order['billing_email']); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($order['billing_phone']); ?></p>
                <p><strong>Address:</strong> <?php echo htmlspecialchars($order['billing_address']); ?></p>
                <p>
                    <strong>City:</strong> <?php echo htmlspecialchars($order['billing_city']); ?>,
                    <strong>State:</strong> <?php echo htmlspecialchars($order['billing_state']); ?>,
                    <strong>Postal Code:</strong> <?php echo htmlspecialchars($order['billing_postal']); ?>
                </p>
            </div>

            <div class="actions">
                <a href="../index.php" class="btn">Continue Shopping</a>
                <a href="../account/orders.php" class="btn">View All Orders</a>
            </div>
        </div>
    </div>
</body>

</html>