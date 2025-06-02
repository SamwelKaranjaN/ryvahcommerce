<?php
require_once '../includes/bootstrap.php';
require_once '../includes/cart.php';

// Check if user is logged in
$is_logged_in = isset($_SESSION['user_id']);

// Get cart data
$cart_data = getCartItems();
$cart_items = $cart_data['items'];
$cart_total = $cart_data['total'];

// Calculate tax
$tax_rates = [];
$tax_amount = 0;

// Get tax rates from database
$stmt = $conn->prepare("SELECT product_type, tax_rate FROM tax_settings WHERE is_active = 1");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $tax_rates[$row['product_type']] = $row['tax_rate'];
}

// Calculate tax for each item
foreach ($cart_items as $item) {
    if (isset($tax_rates[$item['type']])) {
        $item_tax = ($item['price'] * $item['quantity']) * ($tax_rates[$item['type']] / 100);
        $tax_amount += $item_tax;
    }
}

$grand_total = $cart_total + $tax_amount;

include '../includes/layouts/header.php';
?>

<div class="container py-5">
    <div class="row mb-4">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index" class="text-decoration-none">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Shopping Cart</li>
                </ol>
            </nav>
            <h2 class="mb-0">Shopping Cart</h2>
        </div>
    </div>

    <?php if (empty($cart_items)): ?>
        <div class="text-center py-5">
            <div class="mb-4">
                <i class="fas fa-shopping-cart fa-4x text-muted"></i>
            </div>
            <h3 class="mb-3">Your cart is empty</h3>
            <p class="text-muted mb-4">Looks like you haven't added anything to your cart yet.</p>
            <a href="index" class="btn btn-primary btn-lg px-5">
                <i class="fas fa-arrow-left me-2"></i>Continue Shopping
            </a>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div id="cart-items">
                            <?php foreach ($cart_items as $item): ?>
                                <div class="cart-item mb-4 pb-4 border-bottom" id="cart-item-<?php echo $item['id']; ?>">
                                    <div class="row align-items-center">
                                        <div class="col-lg-2 col-md-3 col-4">
                                            <img src="../admin/<?php echo htmlspecialchars($item['thumbs']); ?>"
                                                class="img-fluid rounded" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                        </div>
                                        <div class="col-lg-10 col-md-9 col-8">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <div>
                                                    <h5 class="mb-1">
                                                        <?php echo htmlspecialchars($item['name']); ?>
                                                    </h5>
                                                    <p class="text-muted mb-0">
                                                        <small>By <?php echo htmlspecialchars($item['author'] ?? ''); ?></small>
                                                    </p>
                                                </div>
                                                <button class="btn btn-link text-danger remove-item p-0"
                                                    data-product-id="<?php echo $item['id']; ?>"
                                                    onclick="removeItem(<?php echo $item['id']; ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                            <div class="row align-items-center">
                                                <div class="col-lg-4 col-md-5 col-12 mb-3 mb-md-0">
                                                    <div class="input-group input-group-sm quantity-control">
                                                        <button class="btn btn-outline-secondary" type="button"
                                                            onclick="updateQuantity(<?php echo $item['id']; ?>, 'decrease', null, <?php echo $item['quantity']; ?>)">
                                                            <i class="fas fa-minus"></i>
                                                        </button>
                                                        <input type="number" class="form-control text-center item-qty"
                                                            value="<?php echo $item['quantity']; ?>" min="1"
                                                            onchange="updateQuantity(<?php echo $item['id']; ?>, 'set', this.value)">
                                                        <button class="btn btn-outline-secondary" type="button"
                                                            onclick="updateQuantity(<?php echo $item['id']; ?>, 'increase')">
                                                            <i class="fas fa-plus"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="col-lg-8 col-md-7 col-12">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div class="text-muted">
                                                            <small>Price:
                                                                $<?php echo number_format($item['price'], 2); ?></small>
                                                        </div>
                                                        <div class="h5 mb-0 text-end">
                                                            $<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-4">
                    <a href="index" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left me-2"></i>Continue Shopping
                    </a>
                    <button class="btn btn-outline-secondary" onclick="refreshCart()">
                        <i class="fas fa-sync-alt me-2"></i>Update Cart
                    </button>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm sticky-top" style="top: 2rem;">
                    <div class="card-body p-4">
                        <h5 class="card-title mb-4">Order Summary</h5>
                        <div class="d-flex justify-content-between mb-3">
                            <span>Subtotal</span>
                            <span id="cart-subtotal">$<?php echo number_format($cart_total, 2); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span>Tax (7.75%)</span>
                            <span id="cart-tax">$<?php echo number_format($tax_amount, 2); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span>Shipping</span>
                            <span class="text-success">Free</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-4">
                            <strong>Total</strong>
                            <strong id="cart-total">$<?php echo number_format($grand_total, 2); ?></strong>
                        </div>
                        <?php if (!$is_logged_in): ?>
                            <div class="alert alert-info mb-4">
                                <i class="fas fa-info-circle me-2"></i>
                                Please <a href="login.php?redirect=cart" class="alert-link">login</a> to proceed with checkout.
                            </div>
                        <?php else: ?>
                            <a href="../checkout/index.php" class="btn btn-primary w-100">
                                Proceed to Checkout
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
    .cart-item {
        transition: all 0.3s ease;
    }

    .cart-item img {
        width: 100%;
        height: 120px;
        object-fit: cover;
        border-radius: 8px;
    }

    .quantity-control {
        max-width: 150px;
    }

    .quantity-control .form-control {
        text-align: center;
        font-weight: 500;
    }

    .quantity-control .btn {
        padding: 0.375rem 0.75rem;
    }

    .cart-item .btn-link {
        opacity: 0.7;
        transition: opacity 0.2s ease;
    }

    .cart-item .btn-link:hover {
        opacity: 1;
    }

    @media (max-width: 767.98px) {
        .cart-item {
            padding: 1rem 0;
        }

        .cart-item img {
            height: 100px;
            margin-bottom: 1rem;
        }

        .quantity-control {
            max-width: 100%;
        }

        .cart-item .h5.mb-0.text-end {
            margin-top: 0.5rem;
        }
    }

    /* Loading animation */
    .loading {
        position: relative;
        pointer-events: none;
    }

    .loading::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.8);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 1;
    }

    .loading::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 30px;
        height: 30px;
        border: 3px solid #f3f3f3;
        border-top: 3px solid #007bff;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        z-index: 2;
    }

    @keyframes spin {
        0% {
            transform: translate(-50%, -50%) rotate(0deg);
        }

        100% {
            transform: translate(-50%, -50%) rotate(360deg);
        }
    }

    /* Toast notification */
    .toast-container {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 1050;
    }

    .toast {
        background: white;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        margin-bottom: 10px;
        min-width: 300px;
    }

    .toast-header {
        border-bottom: none;
        padding: 0.75rem 1rem;
    }

    .toast-body {
        padding: 0.75rem 1rem;
    }
</style>

<script>
    let isUpdating = false;

    function showToast(title, message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = 'toast show';
        toast.innerHTML = `
        <div class="toast-header bg-${type} text-white">
            <strong class="me-auto">${title}</strong>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body">
            ${message}
        </div>
    `;

        const container = document.querySelector('.toast-container') || document.createElement('div');
        container.className = 'toast-container';
        container.appendChild(toast);
        document.body.appendChild(container);

        setTimeout(() => {
            toast.remove();
            if (container.children.length === 0) {
                container.remove();
            }
        }, 3000);
    }

    async function updateQuantity(productId, action, value = null, currentQty = null) {
        if (isUpdating) return;

        const item = document.getElementById(`cart-item-${productId}`);
        const input = item.querySelector('.item-qty');
        let quantity = parseInt(input.value);

        if (action === 'increase') {
            quantity++;
        } else if (action === 'decrease') {
            if (quantity === 1 || currentQty === 1) {
                // Remove item if quantity is 1 and user tries to decrease
                removeItem(productId);
                return;
            } else {
                quantity--;
            }
        } else if (action === 'set' && value !== null) {
            quantity = parseInt(value);
            if (quantity < 1) quantity = 1;
        }

        try {
            isUpdating = true;
            item.classList.add('loading');

            const response = await fetch('../includes/cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=update&product_id=${productId}&quantity=${quantity}`
            });

            const data = await response.json();

            if (data.success) {
                input.value = quantity;
                updateCartTotals();
                showToast('Success', 'Cart updated successfully');
            } else {
                showToast('Error', data.message || 'Failed to update cart', 'danger');
                input.value = quantity;
            }
        } catch (error) {
            console.error('Error:', error);
            showToast('Error', 'Failed to update cart', 'danger');
        } finally {
            isUpdating = false;
            item.classList.remove('loading');
        }
    }

    async function removeItem(productId) {
        const item = document.getElementById(`cart-item-${productId}`);
        try {
            item.classList.add('loading');
            const response = await fetch('../includes/cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=remove&product_id=${productId}`
            });
            const data = await response.json();
            if (data.success) {
                item.remove();
                updateCartTotals();
                showToast('Success', 'Item removed from cart');
                // Check if cart is empty
                if (document.querySelectorAll('.cart-item').length === 0) {
                    location.reload();
                }
            } else {
                showToast('Error', data.message || 'Failed to remove item', 'danger');
            }
        } catch (error) {
            console.error('Error:', error);
            showToast('Error', 'Failed to remove item', 'danger');
        } finally {
            item.classList.remove('loading');
        }
    }

    async function updateCartTotals() {
        try {
            const response = await fetch('../includes/cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=get'
            });

            const data = await response.json();

            if (data.success && data.items) {
                let subtotal = 0;
                let taxAmount = 0;
                const taxRate = 7.75; // 7.75% tax rate

                data.items.forEach(item => {
                    const itemTotal = item.price * item.quantity;
                    subtotal += itemTotal;
                    taxAmount += itemTotal * (taxRate / 100);
                });

                const grandTotal = subtotal + taxAmount;

                document.getElementById('cart-subtotal').textContent = `$${subtotal.toFixed(2)}`;
                document.getElementById('cart-tax').textContent = `$${taxAmount.toFixed(2)}`;
                document.getElementById('cart-total').textContent = `$${grandTotal.toFixed(2)}`;
            }
        } catch (error) {
            console.error('Error updating totals:', error);
        }
    }

    function refreshCart() {
        location.reload();
    }
</script>

<?php include '../includes/layouts/footer.php'; ?>