<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = $_GET['id'];
$conn = getDBConnection();

// Get order details
$sql = "SELECT o.*, i.invoice_number, i.status as invoice_status 
        FROM orders o 
        LEFT JOIN invoices i ON o.id = i.order_id 
        WHERE o.id = ? AND o.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    header('Location: profile.php');
    exit();
}

// Get order items
$sql = "SELECT oi.*, p.name, p.thumbs 
        FROM order_items oi 
        JOIN products p ON oi.product_id = p.id 
        WHERE oi.order_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - Ryvah Commerce</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .order-header {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }

        @media (max-width: 768px) {
            .order-header {
                padding: 1.5rem 0;
            }

            .order-header h1 {
                font-size: 1.5rem;
            }

            .order-header p {
                font-size: 0.9rem;
            }
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            font-weight: 500;
            font-size: 0.9rem;
            display: inline-block;
        }

        .status-completed {
            background-color: #d1e7dd;
            color: #0f5132;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-cancelled {
            background-color: #f8d7da;
            color: #842029;
        }

        .card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            margin-bottom: 1.5rem;
        }

        .card-header {
            background-color: white;
            border-bottom: 1px solid rgba(0, 0, 0, 0.125);
            padding: 1.25rem;
            border-radius: 1rem 1rem 0 0 !important;
        }

        .card-title {
            font-size: 1.1rem;
            font-weight: 600;
        }

        .order-item {
            padding: 1rem;
            border-bottom: 1px solid #e9ecef;
        }

        .order-item:last-child {
            border-bottom: none;
        }

        .order-item img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 0.5rem;
        }

        .order-item h6 {
            font-size: 1rem;
            margin-bottom: 0.25rem;
        }

        .order-item p {
            font-size: 0.9rem;
        }

        .timeline {
            position: relative;
            padding-left: 3rem;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 1rem;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e9ecef;
        }

        .timeline-item {
            position: relative;
            padding-bottom: 1.5rem;
        }

        .timeline-item h6 {
            font-size: 1rem;
            margin-bottom: 0.25rem;
        }

        .timeline-item p {
            font-size: 0.9rem;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -2.35rem;
            top: 0;
            width: 1rem;
            height: 1rem;
            border-radius: 50%;
            background: #0d6efd;
            border: 2px solid white;
        }

        .timeline-item:last-child {
            padding-bottom: 0;
        }

        .btn {
            font-size: 0.95rem;
            padding: 0.75rem 1.5rem;
        }

        @media (max-width: 768px) {
            .order-item .row {
                flex-direction: column;
            }

            .order-item .col-md-2 {
                margin-bottom: 1rem;
            }

            .order-item .col-md-4 {
                text-align: left !important;
                margin-top: 0.5rem;
            }

            .order-item img {
                width: 60px;
                height: 60px;
            }

            .card-header {
                padding: 1rem;
            }

            .timeline {
                padding-left: 2rem;
            }

            .timeline-item::before {
                left: -1.35rem;
                width: 0.75rem;
                height: 0.75rem;
            }

            .btn {
                width: 100%;
                margin-bottom: 0.5rem;
            }
        }

        @media (max-width: 576px) {
            .order-header {
                padding: 1rem 0;
            }

            .order-header h1 {
                font-size: 1.25rem;
            }

            .card {
                margin-bottom: 1rem;
            }

            .order-item {
                padding: 0.75rem;
            }

            .order-item h6 {
                font-size: 0.95rem;
            }

            .order-item p {
                font-size: 0.85rem;
            }

            .timeline-item h6 {
                font-size: 0.95rem;
            }

            .timeline-item p {
                font-size: 0.85rem;
            }

            .card-title {
                font-size: 1rem;
            }
        }

        /* Improve table-like layouts */
        .d-flex.justify-content-between {
            font-size: 0.95rem;
            padding: 0.5rem 0;
        }

        .d-flex.justify-content-between strong {
            font-size: 1rem;
        }

        @media (max-width: 576px) {
            .d-flex.justify-content-between {
                font-size: 0.9rem;
            }

            .d-flex.justify-content-between strong {
                font-size: 0.95rem;
            }
        }
    </style>
</head>

<body>
    <?php include '../includes/layouts/header.php'; ?>

    <div class="order-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-2">Order #<?php echo $order['id']; ?></h1>
                    <p class="mb-0">
                        <i class="fas fa-calendar me-2"></i>
                        Placed on <?php echo date('F j, Y', strtotime($order['created_at'])); ?>
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <span class="status-badge status-<?php echo strtolower($order['payment_status'] ?? 'pending'); ?>">
                        <?php echo ucfirst($order['payment_status'] ?? 'pending'); ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="container py-4">
        <div class="row">
            <div class="col-lg-8">
                <!-- Order Items -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Order Items</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php foreach ($items as $item): ?>
                            <div class="order-item">
                                <div class="row align-items-center">
                                    <div class="col-md-2">
                                        <img src="<?php echo htmlspecialchars($item['thumbs']); ?>"
                                            alt="<?php echo htmlspecialchars($item['name']); ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($item['name']); ?></h6>
                                        <p class="text-muted mb-0">Quantity: <?php echo $item['quantity']; ?></p>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <h6 class="mb-0">
                                            $<?php echo number_format($item['price'] * $item['quantity'], 2); ?></h6>
                                        <small class="text-muted">$<?php echo number_format($item['price'], 2); ?>
                                            each</small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Order Timeline -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Order Timeline</h5>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <div class="timeline-item">
                                <h6 class="mb-1">Order Placed</h6>
                                <p class="text-muted mb-0">
                                    <?php echo date('F j, Y g:i A', strtotime($order['created_at'])); ?>
                                </p>
                            </div>
                            <div class="timeline-item">
                                <h6 class="mb-1">Payment <?php echo ucfirst($order['payment_status'] ?? 'pending'); ?>
                                </h6>
                                <p class="text-muted mb-0">
                                    <?php echo date('F j, Y g:i A', strtotime($order['created_at'])); ?>
                                </p>
                            </div>
                            <?php if (($order['payment_status'] ?? '') === 'completed'): ?>
                                <div class="timeline-item">
                                    <h6 class="mb-1">Order Confirmed</h6>
                                    <p class="text-muted mb-0">
                                        <?php echo date('F j, Y g:i A', strtotime($order['created_at'])); ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Order Summary -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Order Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal</span>
                            <span>$<?php echo number_format($order['total_amount'], 2); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Shipping</span>
                            <span class="text-success">Free</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-2">
                            <strong>Total</strong>
                            <strong>$<?php echo number_format($order['total_amount'], 2); ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Payment Method</span>
                            <span><?php echo ucfirst($order['payment_method']); ?></span>
                        </div>
                        <?php if ($order['invoice_number']): ?>
                            <div class="d-flex justify-content-between">
                                <span>Invoice</span>
                                <a href="../invoices/<?php echo $order['invoice_number']; ?>.html" class="text-primary"
                                    target="_blank">
                                    #<?php echo $order['invoice_number']; ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Billing Information -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Billing Information</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($order['billing_name'])): ?>
                            <p class="mb-1"><?php echo htmlspecialchars($order['billing_name']); ?></p>
                        <?php endif; ?>

                        <?php if (!empty($order['billing_address'])): ?>
                            <p class="mb-1"><?php echo htmlspecialchars($order['billing_address']); ?></p>
                        <?php endif; ?>

                        <?php
                        $billing_location = array_filter([
                            $order['billing_city'] ?? null,
                            $order['billing_state'] ?? null,
                            $order['billing_postal'] ?? null
                        ]);
                        if (!empty($billing_location)):
                        ?>
                            <p class="mb-1"><?php echo htmlspecialchars(implode(', ', $billing_location)); ?></p>
                        <?php endif; ?>

                        <?php if (!empty($order['billing_email'])): ?>
                            <p class="mb-1"><?php echo htmlspecialchars($order['billing_email']); ?></p>
                        <?php endif; ?>

                        <?php if (!empty($order['billing_phone'])): ?>
                            <p class="mb-0"><?php echo htmlspecialchars($order['billing_phone']); ?></p>
                        <?php endif; ?>

                        <?php if (empty($order['billing_name']) && empty($order['billing_address'])): ?>
                            <p class="text-muted mb-0">No billing information available</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Actions -->
                <div class="card">
                    <div class="card-body">
                        <a href="profile.php" class="btn btn-outline-primary w-100 mb-2">
                            <i class="fas fa-arrow-left me-2"></i>Back to Orders
                        </a>
                        <?php if ($order['invoice_number']): ?>
                            <a href="../invoices/<?php echo $order['invoice_number']; ?>.html" class="btn btn-primary w-100"
                                target="_blank">
                                <i class="fas fa-file-invoice me-2"></i>View Invoice
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/layouts/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>