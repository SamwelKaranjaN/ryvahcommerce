<?php
require_once '../includes/bootstrap.php';

// Fetch books first, then paints
try {
    $sql_books = "SELECT * FROM products WHERE type = 'ebook' ORDER BY timestamp DESC";
    $sql_paints = "SELECT * FROM products WHERE type = 'paint' ORDER BY timestamp DESC";

    $result_books = $conn->query($sql_books);
    if (!$result_books) {
        throw new Exception("Error fetching books: " . $conn->error);
    }

    $result_paints = $conn->query($sql_paints);
    if (!$result_paints) {
        throw new Exception("Error fetching paintings: " . $conn->error);
    }
} catch (Exception $e) {
    error_log("Error in index.php: " . $e->getMessage());
    $error_message = handleError("Error loading products", $e->getMessage());
}

include '../includes/layouts/header.php';
?>

<!-- Hero Section with Book-themed Background -->
<section class="hero-section position-relative overflow-hidden">
    <div class="hero-overlay"></div>
    <div class="container position-relative text-center" style="z-index: 2;">
        <h1 class="display-4 fw-bold animate__animated animate__fadeInDown">Welcome to Ryvah Books</h1>
        <p class="lead animate__animated animate__fadeInUp animate__delay-1s">Discover Amazing Books and Artworks</p>
        <a href="#books"
            class="btn btn-primary btn-lg animate__animated animate__fadeInUp animate__delay-2s pulse-animation">Explore
            Books</a>
    </div>
</section>

<!-- Books Section -->
<section id="books" class="py-5">
    <div class="container">
        <h2 class="text-center mb-4 section-title">Featured Books</h2>
        <div class="row g-4">
            <?php
            if ($result_books->num_rows > 0) {
                while ($book = $result_books->fetch_assoc()) {
            ?>
            <div class="col-6 col-md-4 col-lg-3 animate__animated animate__fadeIn">
                <div class="card h-100 book-card">
                    <div class="book-badge">
                        <span class="badge bg-primary">E-Book</span>
                    </div>
                    <div class="book-image-container">
                        <img src="<?php echo htmlspecialchars($book['thumbs']); ?>" class="card-img-top book-image"
                            alt="<?php echo htmlspecialchars($book['name']); ?>">
                        <div class="book-overlay">
                            <button class="btn btn-light btn-sm quick-view" data-bs-toggle="modal"
                                data-bs-target="#productModal" data-id="<?php echo $book['id']; ?>"
                                data-name="<?php echo htmlspecialchars($book['name']); ?>"
                                data-price="<?php echo $book['price']; ?>"
                                data-description="<?php echo htmlspecialchars($book['description']); ?>"
                                data-author="<?php echo htmlspecialchars($book['author']); ?>"
                                data-image="<?php echo htmlspecialchars($book['thumbs']); ?>"
                                data-stock="<?php echo $book['stock_quantity']; ?>">
                                <i class="fas fa-eye"></i> Quick View
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <h6 class="card-title book-title text-truncate"><?php echo htmlspecialchars($book['name']); ?>
                        </h6>
                        <p class="card-text mb-2">
                            <small class="text-muted"><i class="fas fa-pen-fancy"></i>
                                <?php echo htmlspecialchars($book['author']); ?></small><br>
                            <span class="price">$<?php echo number_format($book['price'], 2); ?></span>
                        </p>
                        <button class="btn btn-primary btn-sm w-100 add-to-cart"
                            data-product-id="<?php echo $book['id']; ?>">
                            <i class="fas fa-cart-plus"></i> Add to Cart
                        </button>
                    </div>
                </div>
            </div>
            <?php
                }
            } else {
                echo '<div class="col-12 text-center"><p>No books available at the moment.</p></div>';
            }
            ?>
        </div>
    </div>
</section>

<!-- Paintings Section -->
<section id="paintings" class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-4 section-title">Featured Paintings</h2>
        <div class="row g-4">
            <?php
            if ($result_paints->num_rows > 0) {
                while ($paint = $result_paints->fetch_assoc()) {
            ?>
            <div class="col-6 col-md-4 col-lg-3 animate__animated animate__fadeIn">
                <div class="card h-100 paint-card">
                    <div class="paint-badge">
                        <span class="badge bg-success">Original Art</span>
                    </div>
                    <div class="paint-image-container">
                        <img src="<?php echo htmlspecialchars($paint['thumbs']); ?>" class="card-img-top paint-image"
                            alt="<?php echo htmlspecialchars($paint['name']); ?>">
                        <div class="paint-overlay">
                            <button class="btn btn-light btn-sm quick-view" data-bs-toggle="modal"
                                data-bs-target="#productModal" data-id="<?php echo $paint['id']; ?>"
                                data-name="<?php echo htmlspecialchars($paint['name']); ?>"
                                data-price="<?php echo $paint['price']; ?>"
                                data-description="<?php echo htmlspecialchars($paint['description']); ?>"
                                data-author="<?php echo htmlspecialchars($paint['author']); ?>"
                                data-image="<?php echo htmlspecialchars($paint['thumbs']); ?>"
                                data-stock="<?php echo $paint['stock_quantity']; ?>">
                                <i class="fas fa-eye"></i> Quick View
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <h6 class="card-title paint-title text-truncate"><?php echo htmlspecialchars($paint['name']); ?>
                        </h6>
                        <p class="card-text mb-2">
                            <small class="text-muted"><i class="fas fa-paint-brush"></i>
                                <?php echo htmlspecialchars($paint['author']); ?></small><br>
                            <span class="price">$<?php echo number_format($paint['price'], 2); ?></span>
                        </p>
                        <button class="btn btn-success btn-sm w-100 add-to-cart"
                            data-product-id="<?php echo $paint['id']; ?>">
                            <i class="fas fa-cart-plus"></i> Add to Cart
                        </button>
                    </div>
                </div>
            </div>
            <?php
                }
            } else {
                echo '<div class="col-12 text-center"><p>No paintings available at the moment.</p></div>';
            }
            ?>
        </div>
    </div>
</section>

<!-- Product Modal -->
<div class="modal fade" id="productModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <img src="" alt="" class="img-fluid rounded product-modal-image">
                    </div>
                    <div class="col-md-6">
                        <h4 class="product-modal-name"></h4>
                        <p class="text-muted">By <span class="product-modal-author"></span></p>
                        <h5 class="mb-3">$<span class="product-modal-price"></span></h5>
                        <p class="product-modal-description"></p>
                        <div class="quantity-selector mb-3">
                            <label class="form-label">Quantity:</label>
                            <div class="input-group" style="width: 150px;">
                                <button class="btn btn-outline-secondary modal-decrease-qty" type="button">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <input type="number" class="form-control text-center modal-qty" value="1" min="1">
                                <button class="btn btn-outline-secondary modal-increase-qty" type="button">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                            <small class="text-muted">Available: <span class="product-modal-stock"></span></small>
                        </div>
                        <div class="mb-3">
                            Total: $<span class="product-modal-total"></span>
                        </div>
                        <button class="btn btn-primary modal-add-to-cart" data-product-id="">
                            <i class="fas fa-cart-plus"></i> Add to Cart
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Newsletter Section with Book-themed Background -->
<section class="py-5 newsletter-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 text-center">
                <h3 class="animate__animated animate__fadeIn">Join Our Book Club</h3>
                <p class="animate__animated animate__fadeIn animate__delay-1s">Get updates on new releases and exclusive
                    offers</p>
                <form class="row g-3 justify-content-center animate__animated animate__fadeIn animate__delay-2s">
                    <div class="col-md-8">
                        <input type="email" class="form-control form-control-lg" placeholder="Enter your email">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary btn-lg w-100 pulse-animation">Subscribe</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="bg-dark text-light py-4">
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <h5>About Ryvah Books</h5>
                <p>Your premier destination for digital books and original artwork. Discover amazing stories and
                    beautiful art pieces.</p>
            </div>
            <div class="col-md-4">
                <h5>Quick Links</h5>
                <ul class="list-unstyled">
                    <li><a href="#" class="text-light"><i class="fas fa-angle-right"></i> About Us</a></li>
                    <li><a href="#" class="text-light"><i class="fas fa-angle-right"></i> Contact Us</a></li>
                    <li><a href="#" class="text-light"><i class="fas fa-angle-right"></i> Terms & Conditions</a></li>
                    <li><a href="#" class="text-light"><i class="fas fa-angle-right"></i> Privacy Policy</a></li>
                </ul>
            </div>
            <div class="col-md-4">
                <h5>Connect With Us</h5>
                <div class="social-links">
                    <a href="#" class="text-light me-2 social-icon"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="text-light me-2 social-icon"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-light me-2 social-icon"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="text-light social-icon"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
        </div>
        <hr class="my-4">
        <div class="text-center">
            <p class="mb-0">&copy; 2024 Ryvah Books. All rights reserved.</p>
        </div>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Animate.css -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">

<style>
/* Enhanced Styles */
.hero-section {
    background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('https://images.unsplash.com/photo-1507842217343-583bb7270b66?ixlib=rb-4.0.3');
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
    height: 400px;
    display: flex;
    align-items: center;
    color: white;
}

.hero-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.3));
}

.section-title {
    position: relative;
    padding-bottom: 15px;
    margin-bottom: 30px;
}

.section-title:after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 50px;
    height: 3px;
    background: #007bff;
}

/* Book Card Styles */
.book-card {
    transition: transform 0.3s, box-shadow 0.3s;
    border: none;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    background: #fff;
}

.book-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
}

.book-image-container {
    position: relative;
    overflow: hidden;
    padding: 1rem;
    height: 200px;
}

.book-image {
    width: 100%;
    height: 100%;
    object-fit: contain;
    transition: transform 0.3s;
}

.book-card:hover .book-image {
    transform: scale(1.05);
}

/* Paint Card Styles */
.paint-card {
    transition: transform 0.3s, box-shadow 0.3s;
    border: none;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    background: #fff;
}

.paint-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
}

.paint-image-container {
    position: relative;
    overflow: hidden;
    padding: 1rem;
    height: 200px;
}

.paint-image {
    width: 100%;
    height: 100%;
    object-fit: contain;
    transition: transform 0.3s;
}

.paint-card:hover .paint-image {
    transform: scale(1.05);
}

.book-overlay,
.paint-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s;
}

.book-card:hover .book-overlay,
.paint-card:hover .paint-overlay {
    opacity: 1;
}

.book-badge,
.paint-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    z-index: 1;
}

.book-title,
.paint-title {
    font-size: 1rem;
    margin-bottom: 0.5rem;
}

.author,
.artist {
    color: #666;
    font-size: 0.9rem;
}

.price {
    color: #007bff;
    font-weight: bold;
}

.description {
    color: #666;
    font-size: 0.9rem;
    margin-top: 10px;
    display: block;
}

.newsletter-section {
    background: linear-gradient(45deg, #2c3e50, #3498db);
    color: white;
}

.social-icon {
    display: inline-block;
    width: 35px;
    height: 35px;
    line-height: 35px;
    text-align: center;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.1);
    transition: all 0.3s;
}

.social-icon:hover {
    background: #007bff;
    transform: translateY(-3px);
}

.pulse-animation {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% {
        transform: scale(1);
    }

    50% {
        transform: scale(1.05);
    }

    100% {
        transform: scale(1);
    }
}

.quantity-selector .btn {
    padding: 0.375rem 0.75rem;
}

.quantity-selector .form-control {
    border-left: 0;
    border-right: 0;
}

.buy-now {
    transition: all 0.3s;
}

.buy-now:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 123, 255, 0.3);
}

.checkout-btn {
    z-index: 1000;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
    transition: all 0.3s ease;
}

.checkout-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
}

.toast {
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

/* Modal Styles */
.product-modal-image {
    max-height: 400px;
    object-fit: contain;
}

.modal-qty {
    width: 60px !important;
}

@media (max-width: 768px) {

    .book-image-container,
    .paint-image-container {
        height: 150px;
    }
}
</style>

<!-- Custom JavaScript for quantity and price calculation -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Product Modal Functionality
    const productModal = document.getElementById('productModal');
    const modal = new bootstrap.Modal(productModal);

    productModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;

        // Update modal content
        this.querySelector('.modal-title').textContent = button.dataset.name;
        this.querySelector('.product-modal-name').textContent = button.dataset.name;
        this.querySelector('.product-modal-author').textContent = button.dataset.author;
        this.querySelector('.product-modal-price').textContent = button.dataset.price;
        this.querySelector('.product-modal-description').textContent = button.dataset.description;
        this.querySelector('.product-modal-image').src = button.dataset.image;
        this.querySelector('.product-modal-stock').textContent = button.dataset.stock;
        this.querySelector('.modal-add-to-cart').dataset.productId = button.dataset.id;

        // Reset quantity to 1
        this.querySelector('.modal-qty').value = 1;

        // Set initial total
        updateModalTotal();
    });

    // Quantity controls in modal
    const modalQty = productModal.querySelector('.modal-qty');
    const modalDecreaseBtn = productModal.querySelector('.modal-decrease-qty');
    const modalIncreaseBtn = productModal.querySelector('.modal-increase-qty');

    modalDecreaseBtn.addEventListener('click', function() {
        if (modalQty.value > 1) {
            modalQty.value = parseInt(modalQty.value) - 1;
            updateModalTotal();
        }
    });

    modalIncreaseBtn.addEventListener('click', function() {
        const maxStock = parseInt(productModal.querySelector('.product-modal-stock').textContent);
        if (parseInt(modalQty.value) < maxStock) {
            modalQty.value = parseInt(modalQty.value) + 1;
            updateModalTotal();
        }
    });

    modalQty.addEventListener('change', function() {
        const maxStock = parseInt(productModal.querySelector('.product-modal-stock').textContent);
        if (this.value < 1) this.value = 1;
        if (this.value > maxStock) this.value = maxStock;
        updateModalTotal();
    });

    function updateModalTotal() {
        const price = parseFloat(productModal.querySelector('.product-modal-price').textContent);
        const quantity = parseInt(modalQty.value);
        const total = price * quantity;
        productModal.querySelector('.product-modal-total').textContent = total.toFixed(2);
    }

    function showToast(title, message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = 'position-fixed bottom-0 end-0 p-3';
        toast.style.zIndex = '5';
        toast.innerHTML = `
                <div class="toast show" role="alert">
                    <div class="toast-header bg-${type} text-white">
                        <strong class="me-auto">${title}</strong>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                    </div>
                    <div class="toast-body">
                        ${message}
                    </div>
                </div>
            `;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    }

    // Handle add to cart
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.dataset.productId;
            const quantity = 1; // Default quantity

            fetch('../includes/cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=add&product_id=${productId}&quantity=${quantity}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success toast
                        showToast('Success', 'Item added to cart', 'success');
                        // Update cart count
                        updateCartCount();
                    } else {
                        showToast('Error', data.message, 'danger');
                    }
                });
        });
    });

    // Handle modal add to cart
    document.querySelector('.modal-add-to-cart').addEventListener('click', function() {
        const productId = this.dataset.productId;
        const quantity = document.querySelector('.modal-qty').value;

        fetch('../includes/cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=add&product_id=${productId}&quantity=${quantity}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Hide modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById(
                        'productModal'));
                    modal.hide();
                    // Show success toast
                    showToast('Success', 'Item added to cart', 'success');
                    // Update cart count
                    updateCartCount();
                } else {
                    showToast('Error', data.message, 'danger');
                }
            });
    });

    // Update cart count function
    function updateCartCount() {
        fetch('../includes/cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=get'
            })
            .then(response => response.json())
            .then(data => {
                const cartCount = document.querySelector('.cart-count');
                if (data.items.length > 0) {
                    if (!cartCount) {
                        const span = document.createElement('span');
                        span.className = 'cart-count';
                        span.textContent = data.items.length;
                        document.querySelector('.nav-icon.position-relative').appendChild(span);
                    } else {
                        cartCount.textContent = data.items.length;
                    }
                } else if (cartCount) {
                    cartCount.remove();
                }
            });
    }
});
</script>
</body>

</html>