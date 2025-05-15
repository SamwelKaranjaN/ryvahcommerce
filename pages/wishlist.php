<?php
require_once '../includes/bootstrap.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Fetch wishlist items
$user_id = $_SESSION['user_id'];
$sql = "SELECT w.*, p.* FROM wishlist w 
        JOIN products p ON w.product_id = p.id 
        WHERE w.user_id = ? 
        ORDER BY w.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

include '../includes/layouts/header.php';
?>

<div class="container mt-5 pt-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card wishlist-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="card-title mb-0">My Wishlist</h4>
                        <span class="badge bg-primary rounded-pill"><?php echo $result->num_rows; ?> items</span>
                    </div>

                    <?php if ($result->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Type</th>
                                    <th>Price</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($item = $result->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="<?php echo htmlspecialchars($item['thumbs']); ?>"
                                                alt="<?php echo htmlspecialchars($item['name']); ?>"
                                                class="wishlist-item-image me-3">
                                            <div>
                                                <h6 class="mb-1"><?php echo htmlspecialchars($item['name']); ?></h6>
                                                <small class="text-muted">By
                                                    <?php echo htmlspecialchars($item['author']); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span
                                            class="badge <?php echo $item['type'] === 'ebook' ? 'bg-primary' : 'bg-success'; ?>">
                                            <?php echo ucfirst($item['type']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="price">$<?php echo number_format($item['price'], 2); ?></span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <button class="btn btn-sm btn-primary add-to-cart"
                                                data-product-id="<?php echo $item['id']; ?>">
                                                <i class="fas fa-cart-plus"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger remove-from-wishlist"
                                                data-product-id="<?php echo $item['id']; ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-heart-broken fa-3x text-muted mb-3"></i>
                        <h5>Your wishlist is empty</h5>
                        <p class="text-muted">Add items to your wishlist to save them for later</p>
                        <a href="../index.php" class="btn btn-primary mt-3">
                            <i class="fas fa-shopping-bag me-2"></i>Browse Products
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.wishlist-card {
    border: none;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
    border-radius: 15px;
    background: #fff;
}

.card-title {
    color: #2c3e50;
    font-weight: 600;
}

.wishlist-item-image {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.table {
    margin-bottom: 0;
}

.table th {
    font-weight: 600;
    color: #34495e;
    border-top: none;
}

.table td {
    vertical-align: middle;
}

.price {
    color: #007bff;
    font-weight: 600;
}

.btn-group .btn {
    padding: 0.5rem;
    transition: all 0.3s ease;
}

.btn-group .btn:hover {
    transform: translateY(-2px);
}

.btn-primary {
    background: linear-gradient(45deg, #007bff, #0056b3);
    border: none;
}

.btn-danger {
    background: linear-gradient(45deg, #dc3545, #c82333);
    border: none;
}

.badge {
    padding: 0.5rem 1rem;
    font-weight: 500;
}

@media (max-width: 768px) {
    .container {
        padding: 1rem;
    }

    .wishlist-card {
        margin: 0;
    }

    .wishlist-item-image {
        width: 50px;
        height: 50px;
    }

    .table th:nth-child(2),
    .table td:nth-child(2) {
        display: none;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add to cart functionality
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.dataset.productId;

            fetch('../includes/cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=add&product_id=${productId}&quantity=1`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Success', 'Item added to cart', 'success');
                        updateCartCount();
                    } else {
                        showToast('Error', data.message, 'danger');
                    }
                });
        });
    });

    // Remove from wishlist functionality
    document.querySelectorAll('.remove-from-wishlist').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.dataset.productId;
            const row = this.closest('tr');

            fetch('../includes/wishlist.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=remove&product_id=${productId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        row.remove();
                        showToast('Success', 'Item removed from wishlist', 'success');
                        // Update wishlist count
                        const badge = document.querySelector('.badge.bg-primary');
                        const currentCount = parseInt(badge.textContent);
                        badge.textContent = `${currentCount - 1} items`;

                        // If no items left, show empty state
                        if (currentCount - 1 === 0) {
                            location.reload();
                        }
                    } else {
                        showToast('Error', data.message, 'danger');
                    }
                });
        });
    });
});
</script>

<?php include '../includes/layouts/footer.php'; ?>