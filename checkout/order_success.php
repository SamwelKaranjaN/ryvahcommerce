<?php
session_start();
require_once 'config/database.php';

if (!isset($_GET['order_id'])) {
    header('Location: index.php');
    exit();
}

$order_id = $_GET['order_id'];
$user_id = 19; // Using user_id 19 as requested

// Get order details
$sql = "SELECT o.*, oi.product_id, oi.quantity, oi.price, p.name 
        FROM orders o 
        JOIN order_items oi ON o.id = oi.order_id 
        JOIN products p ON oi.product_id = p.id 
        WHERE o.id = ? AND o.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

$order_items = [];
while ($row = $result->fetch_assoc()) {
    $order_items[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Success</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<<<<<<< Updated upstream
</head>

<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h3 class="mb-0">Order Successful!</h3>
            </div>
            <div class="card-body">
                <h4>Order #<?php echo $order_id; ?></h4>
                <p>Thank you for your purchase!</p>

                <h5 class="mt-4">Order Details:</h5>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order_items as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td>$<?php echo number_format($item['price'], 2); ?></td>
                            <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                        </tr>
=======
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .success-icon {
            font-size: 4rem;
            color: #28a745;
        }

        .order-details {
            background-color: #f8f9fa;
            border-radius: 0.5rem;
            padding: 1.5rem;
        }

        .item-image {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 0.25rem;
        }
    </style>
</head>

<body>
    <?php include '../includes/layouts/header.php'; ?>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="text-center mb-4">
                    <i class="fas fa-check-circle success-icon"></i>
                    <h2 class="mt-3">Order Successful!</h2>
                    <p class="text-muted">Thank you for your purchase. Your order has been processed successfully.</p>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="card-title mb-0">Order Details</h5>
                            <div>
                                <a href="<?php echo $invoice_path; ?>" class="btn btn-primary" target="_blank">
                                    <i class="fas fa-eye me-2"></i>View Invoice
                                </a>
                                <a href="<?php echo $invoice_path; ?>" class="btn btn-outline-primary" download>
                                    <i class="fas fa-download me-2"></i>Download Invoice
                                </a>
                            </div>
                        </div>

                        <div class="order-details mb-4">
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Order Number:</strong></p>
                                    <p class="text-muted">#<?php echo $order['id']; ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Invoice Number:</strong></p>
                                    <p class="text-muted"><?php echo $order['invoice_number']; ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Order Date:</strong></p>
                                    <p class="text-muted"><?php echo date('F j, Y', strtotime($order['order_date'])); ?>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Payment Method:</strong></p>
                                    <p class="text-muted"><?php echo ucfirst($order['payment_method']); ?></p>
                                </div>
                            </div>
                        </div>

                        <h6 class="mb-3">Items Purchased</h6>
                        <?php foreach ($items as $item): ?>
                            <div class="d-flex align-items-center mb-3">
                                <img src="<?php echo htmlspecialchars($item['thumbs']); ?>" alt="" class="item-image me-3">
                                <div class="flex-grow-1">
                                    <h6 class="mb-0"><?php echo htmlspecialchars($item['name']); ?></h6>
                                    <small class="text-muted">Quantity: <?php echo $item['quantity']; ?></small>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold">$<?php echo number_format($item['subtotal'], 2); ?></div>
                                    <small class="text-muted">$<?php echo number_format($item['price'], 2); ?> each</small>
                                </div>
                            </div>
>>>>>>> Stashed changes
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-end"><strong>Total:</strong></td>
                            <td><strong>$<?php echo number_format($order_items[0]['total_amount'], 2); ?></strong></td>
                        </tr>
                    </tfoot>
                </table>

                <div class="mt-4">
                    <a href="index.php" class="btn btn-primary">Continue Shopping</a>
                </div>
            </div>
        </div>
    </div>

<<<<<<< Updated upstream
=======
    <?php include '../includes/layouts/footer.php'; ?>

>>>>>>> Stashed changes
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>