<?php
session_start();
require_once '../config/database.php';
require_once '../includes/layout.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Check if order_id is provided
if (!isset($_GET['order_id'])) {
    header('Location: ../index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = $_GET['order_id'];
$conn = getDBConnection();

// Get order details
$sql = "SELECT o.*, 
        GROUP_CONCAT(CONCAT(p.name, ' (', oi.quantity, ')') SEPARATOR ', ') as items,
        GROUP_CONCAT(DISTINCT p.type) as product_types
        FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        JOIN products p ON oi.product_id = p.id
        WHERE o.id = ? AND o.user_id = ? AND o.payment_status = 'completed'
        GROUP BY o.id";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    header('Location: ../index.php');
    exit();
}

// Get downloadable items
$sql = "SELECT p.*, up.download_count, up.id as purchase_id
        FROM user_purchases up
        JOIN products p ON up.product_id = p.id
        WHERE up.order_id = ? AND up.user_id = ? AND p.type = 'ebook'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$downloadable_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Set page title
$page_title = "Order Success - Ryvah Commerce";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo getPageTitle(); ?></title>
    <?php renderCSSLinks(); ?>
    <style>
    .success-icon {
        width: 80px;
        height: 80px;
        background: #d4edda;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
    }

    .success-icon i {
        font-size: 2.5rem;
        color: #28a745;
    }

    .download-card {
        transition: all 0.3s ease;
        border-radius: 1rem;
        overflow: hidden;
    }

    .download-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .download-btn {
        transition: all 0.3s ease;
    }

    .download-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(13, 110, 253, 0.2);
    }
    </style>
</head>

<body>
    <?php include '../includes/layouts/header.php'; ?>

    <div class="container py-5">
        <div class="row mb-4">
            <div class="col">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb bg-white px-3 py-2 rounded shadow-sm">
                        <li class="breadcrumb-item"><a href="../index.php" class="text-decoration-none">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Order Success</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="text-center mb-5">
            <div class="success-icon">
                <i class="fas fa-check"></i>
            </div>
            <h2 class="mb-3">Thank You for Your Order!</h2>
            <p class="text-muted mb-4">Your order has been successfully placed and confirmed.</p>
            <div class="d-flex justify-content-center gap-3">
                <a href="my_orders.php" class="btn btn-primary">
                    <i class="fas fa-box me-2"></i>View My Orders
                </a>
                <a href="../index.php" class="btn btn-outline-primary">
                    <i class="fas fa-shopping-bag me-2"></i>Continue Shopping
                </a>
            </div>
        </div>

        <?php if (strpos($order['product_types'], 'ebook') !== false): ?>
        <div class="row mb-5">
            <div class="col-12">
                <h3 class="mb-4">Download Your Digital Products</h3>
                <div class="row g-4">
                    <?php foreach ($downloadable_items as $item): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card download-card h-100">
                            <div class="card-body">
                                <h5 class="card-title mb-3"><?php echo htmlspecialchars($item['name']); ?></h5>
                                <p class="text-muted mb-3">
                                    <small>
                                        <i class="fas fa-download me-1"></i>
                                        Downloads: <?php echo $item['download_count']; ?>
                                    </small>
                                </p>
                                <a href="../download.php?purchase_id=<?php echo $item['purchase_id']; ?>"
                                    class="btn btn-primary download-btn w-100">
                                    <i class="fas fa-download me-2"></i>Download Now
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h3 class="card-title mb-4">Order Details</h3>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Order Number:</strong> #<?php echo $order['id']; ?></p>
                                <p><strong>Order Date:</strong>
                                    <?php echo date('F d, Y H:i', strtotime($order['created_at'])); ?></p>
                                <p><strong>Payment Method:</strong> <?php echo ucfirst($order['payment_method']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Total Amount:</strong>
                                    $<?php echo number_format($order['total_amount'], 2); ?></p>
                                <p><strong>Items:</strong> <?php echo htmlspecialchars($order['items']); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/layouts/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>