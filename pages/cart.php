<?php
require_once '../includes/bootstrap.php';
require_once '../includes/cart.php';

$cart_data = getCartItems();
$cart_items = $cart_data['items'];
$cart_total = $cart_data['total'];

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
                    <?php foreach ($cart_items as $item): ?>
                    <div class="cart-item mb-4 <?php echo $item !== end($cart_items) ? 'border-bottom pb-4' : ''; ?>">
                        <div class="row align-items-center">
                            <div class="col-lg-2 col-md-3 col-4 mb-3 mb-md-0">
                                <div class="position-relative">
                                    <img src="<?php echo htmlspecialchars($item['thumbs']); ?>" 
                                         class="img-fluid rounded" 
                                         alt="<?php echo htmlspecialchars($item['name']); ?>">
                                    <?php if ($item['type'] === 'ebook'): ?>
                                    <span class="position-absolute top-0 start-0 badge bg-primary m-2">
                                        E-Book
                                    </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-lg-10 col-md-9 col-8">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h5 class="mb-1">
                                            <?php echo htmlspecialchars($item['name']); ?>
                                        </h5>
                                        <p class="text-muted mb-0">
                                            <small>By <?php echo htmlspecialchars($item['author']); ?></small>
                                        </p>
                                    </div>
                                    <button class="btn btn-link text-danger remove-item p-0" 
                                            data-product-id="<?php echo $item['product_id'] ?? $item['id']; ?>">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <div class="row align-items-center">
                                    <div class="col-lg-4 col-md-5 col-12 mb-3 mb-md-0">
                                        <div class="input-group input-group-sm quantity-control">
                                            <button class="btn btn-outline-secondary decrease-qty" type="button" 
                                                    data-product-id="<?php echo $item['product_id'] ?? $item['id']; ?>">
                                                <i class="fas fa-minus"></i>
                                            </button>
                                            <input type="number" class="form-control text-center item-qty" 
                                                   value="<?php echo $item['quantity']; ?>" min="1"
                                                   data-product-id="<?php echo $item['product_id'] ?? $item['id']; ?>">
                                            <button class="btn btn-outline-secondary increase-qty" type="button"
                                                    data-product-id="<?php echo $item['product_id'] ?? $item['id']; ?>">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-lg-8 col-md-7 col-12">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="text-muted">
                                                <small>Price: $<?php echo number_format($item['price'], 2); ?></small>
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
            
            <div class="d-flex justify-content-between align-items-center mt-4">
                <a href="index" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left me-2"></i>Continue Shopping
                </a>
                <button class="btn btn-outline-secondary" onclick="window.location.reload()">
                    <i class="fas fa-sync-alt me-2"></i>Update Cart
                </button>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h5 class="card-title mb-4">Order Summary</h5>
                    
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Subtotal</span>
                        <span>$<?php echo number_format($cart_total, 2); ?></span>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Shipping</span>
                        <span class="text-success">Free</span>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between mb-4">
                        <strong>Total</strong>
                        <div class="h4 mb-0 text-primary">$<?php echo number_format($cart_total, 2); ?></div>
                    </div>
                    
                    <a href="checkout" class="btn btn-primary btn-lg w-100 mb-3">
                        <i class="fas fa-lock me-2"></i>Proceed to Checkout
                    </a>
                    
                    <div class="text-center">
                        <small class="text-muted">
                            <i class="fas fa-shield-alt me-1"></i>Secure Checkout
                        </small>
                    </div>
                </div>
            </div>
            
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-body p-4">
                    <h6 class="mb-3">We Accept</h6>
                    <div class="d-flex gap-2 payment-methods">
                        <i class="fab fa-cc-visa fa-2x text-muted"></i>
                        <i class="fab fa-cc-mastercard fa-2x text-muted"></i>
                        <i class="fab fa-cc-amex fa-2x text-muted"></i>
                        <i class="fab fa-cc-paypal fa-2x text-muted"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
.cart-item img {
    max-height: 120px;
    object-fit: contain;
    width: 100%;
}

.quantity-control {
    max-width: 150px;
}

.quantity-control .form-control {
    text-align: center;
}

.quantity-control .btn {
    width: 32px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}

.payment-methods {
    opacity: 0.7;
}

/* Enhanced Mobile Responsiveness */
@media (max-width: 767.98px) {
    .cart-item {
        padding: 1rem 0;
    }
    
    .cart-item img {
        max-height: 100px;
        margin-bottom: 1rem;
    }
    
    .quantity-control {
        max-width: 120px;
        margin: 1rem 0;
    }
    
    .cart-item .row {
        margin: 0;
    }
    
    .cart-item .col-lg-10 {
        padding: 0;
    }
    
    .remove-item {
        position: absolute;
        top: 0.5rem;
        right: 0.5rem;
    }
    
    .d-flex.justify-content-between.align-items-center.mt-4 {
        flex-direction: column;
        gap: 1rem;
    }
    
    .d-flex.justify-content-between.align-items-center.mt-4 .btn {
        width: 100%;
    }
    
    .card-body {
        padding: 1rem;
    }
    
    .breadcrumb {
        font-size: 0.875rem;
    }
    
    h2 {
        font-size: 1.5rem;
    }
    
    .h4 {
        font-size: 1.25rem;
    }
    
    .payment-methods .fab {
        font-size: 1.5rem;
    }
}

/* Small Mobile Devices */
@media (max-width: 575.98px) {
    .container {
        padding-left: 1rem;
        padding-right: 1rem;
    }
    
    .cart-item {
        margin: 0 -1rem;
        padding: 1rem;
    }
    
    .cart-item:not(:last-child) {
        border-bottom: 1px solid #dee2e6;
    }
    
    .quantity-control {
        max-width: 100px;
    }
    
    .cart-item .price {
        font-size: 0.875rem;
    }
    
    .cart-item h5 {
        font-size: 1rem;
    }
    
    .cart-item .text-muted {
        font-size: 0.75rem;
    }
    
    .order-summary h5 {
        font-size: 1.125rem;
    }
    
    .btn {
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
    }
    
    .btn-lg {
        padding: 0.75rem 1.25rem;
        font-size: 1rem;
    }
}

/* Tablet Devices */
@media (min-width: 768px) and (max-width: 991.98px) {
    .cart-item img {
        max-height: 110px;
    }
    
    .quantity-control {
        max-width: 130px;
    }
    
    .card-body {
        padding: 1.25rem;
    }
}

/* Ensure proper spacing in all viewports */
.row.g-4 {
    margin-left: -0.5rem;
    margin-right: -0.5rem;
}

.row.g-4 > [class^="col-"] {
    padding-left: 0.5rem;
    padding-right: 0.5rem;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update quantity
    function updateQuantity(productId, quantity) {
        fetch('../includes/cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=update&product_id=${productId}&quantity=${quantity}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }
    
    // Remove item with confirmation
    document.querySelectorAll('.remove-item').forEach(button => {
        button.addEventListener('click', function() {
            if (confirm('Are you sure you want to remove this item from your cart?')) {
                const productId = this.dataset.productId;
                fetch('../includes/cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=remove&product_id=${productId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    }
                });
            }
        });
    });
    
    // Quantity controls
    document.querySelectorAll('.decrease-qty').forEach(button => {
        button.addEventListener('click', function() {
            const input = this.parentElement.querySelector('.item-qty');
            const productId = this.dataset.productId;
            if (input.value > 1) {
                input.value = parseInt(input.value) - 1;
                updateQuantity(productId, input.value);
            }
        });
    });
    
    document.querySelectorAll('.increase-qty').forEach(button => {
        button.addEventListener('click', function() {
            const input = this.parentElement.querySelector('.item-qty');
            const productId = this.dataset.productId;
            input.value = parseInt(input.value) + 1;
            updateQuantity(productId, input.value);
        });
    });
    
    document.querySelectorAll('.item-qty').forEach(input => {
        input.addEventListener('change', function() {
            const productId = this.dataset.productId;
            if (this.value < 1) this.value = 1;
            updateQuantity(productId, this.value);
        });
    });
});
</script>

<?php include '../includes/layouts/footer.php'; ?> 