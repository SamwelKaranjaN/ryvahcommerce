<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php?redirect=pending_payments.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$conn = getDBConnection();

// Get pending orders
$sql = "SELECT po.*, p.name, p.author, p.thumbs, p.price, p.type
        FROM pending_orders po
        JOIN products p ON po.product_id = p.id
        WHERE po.user_id = ? AND po.status = 'pending'
        ORDER BY po.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$pending_orders = $result->fetch_all(MYSQLI_ASSOC);

include '../includes/layouts/header.php';
?>

<div class="container py-5">
    <div class="row mb-4">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="../index" class="text-decoration-none">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Pending Payments</li>
                </ol>
            </nav>
            <h2 class="mb-0">Pending Payments</h2>
        </div>
    </div>

    <?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php
            echo $_SESSION['success_message'];
            unset($_SESSION['success_message']);
            ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <?php if (empty($pending_orders)): ?>
    <div class="text-center py-5">
        <div class="mb-4">
            <i class="fas fa-clock fa-4x text-muted"></i>
        </div>
        <h3 class="mb-3">No pending payments</h3>
        <p class="text-muted mb-4">You don't have any pending payments at the moment.</p>
        <a href="../index" class="btn btn-primary btn-lg px-5">
            <i class="fas fa-shopping-cart me-2"></i>Continue Shopping
        </a>
    </div>
    <?php else: ?>
    <div class="row g-4">
        <?php foreach ($pending_orders as $order): ?>
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-2 col-4">
                            <img src="../<?php echo htmlspecialchars($order['thumbs']); ?>" class="img-fluid rounded"
                                alt="<?php echo htmlspecialchars($order['name']); ?>">
                        </div>
                        <div class="col-md-7 col-8">
                            <h5 class="card-title mb-1">
                                <?php echo htmlspecialchars($order['name']); ?>
                            </h5>
                            <p class="text-muted mb-2">
                                By <?php echo htmlspecialchars($order['author']); ?>
                            </p>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-warning me-2">Pending Payment</span>
                                <small class="text-muted">
                                    Added on <?php echo date('F j, Y', strtotime($order['created_at'])); ?>
                                </small>
                            </div>
                        </div>
                        <div class="col-md-3 text-md-end mt-3 mt-md-0">
                            <p class="h5 mb-3">$<?php echo number_format($order['price'] * $order['quantity'], 2); ?>
                            </p>
                            <div class="d-grid gap-2">
                                <a href="../checkout/checkout?retry=<?php echo $order['id']; ?>"
                                    class="btn btn-primary">
                                    <i class="fas fa-credit-card me-2"></i>Retry Payment
                                </a>
                                <button class="btn btn-outline-danger"
                                    onclick="removePendingOrder(<?php echo $order['id']; ?>)">
                                    <i class="fas fa-trash me-2"></i>Remove
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<style>
.card {
    transition: transform 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
}

.btn {
    transition: all 0.3s ease;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

@media (max-width: 768px) {
    .card {
        margin-bottom: 1rem;
    }
}
</style>

<script>
async function removePendingOrder(orderId) {
    if (!confirm('Are you sure you want to remove this item from pending payments?')) {
        return;
    }

    try {
        const response = await fetch('../includes/pending_orders.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=remove&order_id=${orderId}`
        });

        const data = await response.json();

        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Failed to remove item');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while removing the item');
    }
}
</script>

<?php include '../includes/layouts/footer.php'; ?>