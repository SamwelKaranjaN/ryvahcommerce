<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$conn = getDBConnection();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'remove':
                if (isset($_POST['pending_order_id'])) {
                    $pending_order_id = $_POST['pending_order_id'];

                    // Start transaction
                    $conn->begin_transaction();

                    try {
                        // Get pending order details
                        $sql = "SELECT * FROM pending_orders WHERE id = ? AND user_id = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("ii", $pending_order_id, $user_id);
                        $stmt->execute();
                        $pending_order = $stmt->get_result()->fetch_assoc();

                        if ($pending_order) {
                            // Add item back to cart
                            $sql = "INSERT INTO cart (user_id, product_id, quantity) 
                                   VALUES (?, ?, ?) 
                                   ON DUPLICATE KEY UPDATE quantity = quantity + ?";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param(
                                "iiii",
                                $user_id,
                                $pending_order['product_id'],
                                $pending_order['quantity'],
                                $pending_order['quantity']
                            );
                            $stmt->execute();

                            // Remove from pending orders
                            $sql = "DELETE FROM pending_orders WHERE id = ?";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("i", $pending_order_id);
                            $stmt->execute();

                            $conn->commit();
                            $success_message = "Item moved back to cart successfully.";
                        }
                    } catch (Exception $e) {
                        $conn->rollback();
                        $error_message = "Error processing request: " . $e->getMessage();
                    }
                }
                break;

            case 'checkout':
                if (isset($_POST['pending_order_ids'])) {
                    $pending_order_ids = explode(',', $_POST['pending_order_ids']);
                    $pending_order_ids = array_filter($pending_order_ids, 'is_numeric');
                    $ids_string = implode(',', array_map('intval', $pending_order_ids));

                    if (!empty($ids_string)) {
                        // Redirect to checkout with selected items
                        header("Location: ../checkout/checkout.php?items=" . urlencode($ids_string));
                        exit();
                    }
                }
                break;
        }
    }
}

// Get user's pending orders
$sql = "SELECT po.*, p.name, p.thumbs, p.type 
        FROM pending_orders po 
        JOIN products p ON po.product_id = p.id 
        WHERE po.user_id = ? AND po.status = 'pending'
        ORDER BY po.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$pending_orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Calculate total
$total = 0;
foreach ($pending_orders as $order) {
    $total += $order['price'] * $order['quantity'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Orders - Ryvah Commerce</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .product-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
        }

        .order-card {
            transition: all 0.3s ease;
        }

        .order-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body>
    <?php include '../includes/layouts/header.php'; ?>

    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Pending Orders</h2>
            <?php if (!empty($pending_orders)): ?>
                <form method="POST" id="checkout-form">
                    <input type="hidden" name="action" value="checkout">
                    <input type="hidden" name="pending_order_ids" id="pending_order_ids">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-shopping-cart me-2"></i>Proceed to Checkout
                    </button>
                </form>
            <?php endif; ?>
        </div>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $success_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (empty($pending_orders)): ?>
            <div class="text-center py-5">
                <i class="fas fa-shopping-basket fa-3x text-muted mb-3"></i>
                <h4>No Pending Orders</h4>
                <p class="text-muted">You don't have any pending orders at the moment.</p>
                <a href="products.php" class="btn btn-primary">
                    <i class="fas fa-shopping-bag me-2"></i>Continue Shopping
                </a>
            </div>
        <?php else: ?>
            <div class="row">
                <div class="col-lg-8">
                    <?php foreach ($pending_orders as $order): ?>
                        <div class="card order-card mb-3">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <input type="checkbox" class="form-check-input order-checkbox"
                                            value="<?php echo $order['id']; ?>">
                                    </div>
                                    <div class="col-auto">
                                        <img src="<?php echo htmlspecialchars($order['thumbs']); ?>" class="product-image"
                                            alt="<?php echo htmlspecialchars($order['name']); ?>">
                                    </div>
                                    <div class="col">
                                        <h5 class="card-title mb-1"><?php echo htmlspecialchars($order['name']); ?></h5>
                                        <p class="text-muted mb-1">Quantity: <?php echo $order['quantity']; ?></p>
                                        <p class="mb-0">$<?php echo number_format($order['price'] * $order['quantity'], 2); ?>
                                        </p>
                                    </div>
                                    <div class="col-auto">
                                        <form method="POST" class="d-inline"
                                            onsubmit="return confirm('Are you sure you want to move this item back to cart?');">
                                            <input type="hidden" name="action" value="remove">
                                            <input type="hidden" name="pending_order_id" value="<?php echo $order['id']; ?>">
                                            <button type="submit" class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-shopping-cart me-1"></i>Return to Cart
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title mb-4">Order Summary</h5>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal</span>
                                <span>$<?php echo number_format($total, 2); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Shipping</span>
                                <span class="text-success">Free</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-4">
                                <strong>Total</strong>
                                <strong class="text-primary">$<?php echo number_format($total, 2); ?></strong>
                            </div>
                            <button type="submit" form="checkout-form" class="btn btn-primary w-100">
                                <i class="fas fa-lock me-2"></i>Proceed to Checkout
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php include '../includes/layouts/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const checkboxes = document.querySelectorAll('.order-checkbox');
            const checkoutForm = document.getElementById('checkout-form');
            const pendingOrderIds = document.getElementById('pending_order_ids');

            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', updateSelectedOrders);
            });

            function updateSelectedOrders() {
                const selectedIds = Array.from(checkboxes)
                    .filter(cb => cb.checked)
                    .map(cb => cb.value);

                pendingOrderIds.value = selectedIds.join(',');

                // Enable/disable checkout button based on selection
                const checkoutButton = checkoutForm.querySelector('button[type="submit"]');
                checkoutButton.disabled = selectedIds.length === 0;
            }

            // Initial update
            updateSelectedOrders();
        });
    </script>
</body>

</html>