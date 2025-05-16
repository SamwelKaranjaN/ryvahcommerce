<?php
session_start();
require_once '../config/database.php';

// Get order ID from session
$order_id = isset($_SESSION['current_order_id']) ? $_SESSION['current_order_id'] : null;

// If no order ID, redirect to cart
if (!$order_id) {
    header('Location: ../pages/cart.php');
    exit();
}

// Get order details
$conn = getDBConnection();
$sql = "SELECT o.*, u.email, u.full_name 
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        WHERE o.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Cancelled - Ryvah Commerce</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .payment-status {
            text-align: center;
            padding: 3rem 0;
        }

        .payment-status i {
            font-size: 4rem;
            color: #dc3545;
            margin-bottom: 1rem;
        }

        .order-details {
            background: #f8f9fa;
            border-radius: 0.5rem;
            padding: 1.5rem;
            margin-top: 2rem;
        }
    </style>
</head>

<body>
    <?php include '../includes/header.php'; ?>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="payment-status">
                            <i class="fas fa-times-circle"></i>
                            <h2 class="mb-3">Payment Cancelled</h2>
                            <p class="text-muted mb-4">Your payment process was cancelled. Don't worry, your order is
                                still saved.</p>

                            <?php if ($order): ?>
                                <div class="order-details">
                                    <h5 class="mb-3">Order Details</h5>
                                    <p><strong>Order ID:</strong> #<?php echo $order_id; ?></p>
                                    <p><strong>Amount:</strong> $<?php echo number_format($order['total_amount'], 2); ?></p>
                                    <p><strong>Date:</strong> <?php echo date('F j, Y', strtotime($order['created_at'])); ?>
                                    </p>
                                </div>
                            <?php endif; ?>

                            <div class="mt-4">
                                <a href="checkout.php" class="btn btn-primary me-2">
                                    <i class="fas fa-redo me-2"></i>Try Payment Again
                                </a>
                                <a href="../pages/cart.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-shopping-cart me-2"></i>Return to Cart
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>