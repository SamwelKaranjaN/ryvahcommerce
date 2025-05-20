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
    <div class="container position-relative" style="z-index: 2;">
        <div class="hero-content text-center">
            <h1 class="hero-title animate__animated animate__fadeInDown">
                Welcome to Ryvah Books
                <span class="hero-subtitle animate__animated animate__fadeInUp animate__delay-1s">
                    Your Gateway to Digital Knowledge
                </span>
            </h1>
            <p class="hero-description animate__animated animate__fadeInUp animate__delay-1s">
                Discover Amazing Books and Artworks
            </p>
            <div class="hero-buttons animate__animated animate__fadeInUp animate__delay-2s">
                <a href="#books" class="btn btn-primary btn-lg hero-btn">
                    <i class="fas fa-book"></i> Explore Books
                </a>
                <a href="#paintings" class="btn btn-outline-light btn-lg hero-btn ms-3">
                    <i class="fas fa-palette"></i> View Artworks
                </a>
                <a href="contact" class="btn btn-contact btn-lg hero-btn ms-3">
                    <i class="fas fa-envelope"></i> Contact Us
                </a>
            </div>
            <div class="hero-features animate__animated animate__fadeInUp animate__delay-2s">
                <div class="feature">
                    <i class="fas fa-shipping-fast"></i>
                    <span>Instant Delivery</span>
                </div>
                <div class="feature">
                    <i class="fas fa-lock"></i>
                    <span>Secure Payment</span>
                </div>
                <div class="feature">
                    <i class="fas fa-headset"></i>
                    <span>24/7 Support</span>
                </div>
            </div>
        </div>
    </div>
    <div class="hero-wave">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320">
            <path fill="#ffffff" fill-opacity="1"
                d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,122.7C672,117,768,139,864,149.3C960,160,1056,160,1152,138.7C1248,117,1344,75,1392,53.3L1440,32L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z">
            </path>
        </svg>
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
                        <img src="../admin/<?php echo htmlspecialchars($book['thumbs']); ?>"
                            class="card-img-top book-image" alt="<?php echo htmlspecialchars($book['name']); ?>">
                        <div class="book-overlay">
                            <button class="btn btn-light btn-sm quick-view" data-bs-toggle="modal"
                                data-bs-target="#productModal" data-id="<?php echo $book['id']; ?>"
                                data-name="<?php echo htmlspecialchars($book['name']); ?>"
                                data-price="<?php echo $book['price']; ?>"
                                data-description="<?php echo htmlspecialchars($book['description']); ?>"
                                data-author="<?php echo htmlspecialchars($book['author']); ?>"
                                data-image="../admin/<?php echo htmlspecialchars($book['thumbs']); ?>"
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
                        <img src="../admin/<?php echo htmlspecialchars($paint['thumbs']); ?>"
                            class="card-img-top paint-image" alt="<?php echo htmlspecialchars($paint['name']); ?>">
                        <div class="paint-overlay">
                            <button class="btn btn-light btn-sm quick-view" data-bs-toggle="modal"
                                data-bs-target="#productModal" data-id="<?php echo $paint['id']; ?>"
                                data-name="<?php echo htmlspecialchars($paint['name']); ?>"
                                data-price="<?php echo $paint['price']; ?>"
                                data-description="<?php echo htmlspecialchars($paint['description']); ?>"
                                data-author="<?php echo htmlspecialchars($paint['author']); ?>"
                                data-image="../admin/<?php echo htmlspecialchars($paint['thumbs']); ?>"
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
/* Enhanced Hero Section Styles */
.hero-section {
    background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)),
        url('https://images.unsplash.com/photo-1507842217343-583bb7270b66?ixlib=rb-4.0.3');
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
    min-height: 100vh;
    display: flex;
    align-items: center;
    color: white;
    position: relative;
    padding: 100px 0;
}

.hero-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0.4));
}

.hero-content {
    max-width: 800px;
    margin: 0 auto;
    padding: 0 20px;
}

.hero-title {
    font-size: 3.5rem;
    font-weight: 800;
    margin-bottom: 1.5rem;
    line-height: 1.2;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
}

.hero-subtitle {
    display: block;
    font-size: 1.5rem;
    font-weight: 400;
    color: #ffd700;
    margin-top: 0.5rem;
}

.hero-description {
    font-size: 1.25rem;
    margin-bottom: 2rem;
    opacity: 0.9;
}

.hero-buttons {
    display: flex;
    flex-direction: row;
    flex-wrap: nowrap;
    justify-content: center;
    align-items: center;
    gap: 1rem;
    margin-bottom: 3rem;
}

.hero-btn {
    font-size: 1.08rem;
    padding: 0.85rem 1.7rem;
    border-radius: 50px;
    font-weight: 600;
    letter-spacing: 0.5px;
}

.btn-primary {
    background: linear-gradient(45deg, #007bff, #0056b3);
    border: none;
}

.btn-outline-light {
    border-width: 2px;
}

.hero-features {
    display: flex;
    justify-content: center;
    gap: 3rem;
    margin-top: 2rem;
}

.feature {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
}

.feature i {
    font-size: 2rem;
    color: #ffd700;
    margin-bottom: 0.5rem;
}

.feature span {
    font-size: 1rem;
    font-weight: 500;
}

.hero-wave {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    line-height: 0;
}

/* Responsive Styles */
@media (max-width: 992px) {
    .hero-title {
        font-size: 2.8rem;
    }

    .hero-subtitle {
        font-size: 1.3rem;
    }

    .hero-description {
        font-size: 1.1rem;
    }
}

@media (max-width: 768px) {
    .hero-section {
        min-height: 80vh;
        padding: 60px 0;
    }

    .hero-title {
        font-size: 2.2rem;
    }

    .hero-subtitle {
        font-size: 1.1rem;
    }

    .hero-buttons {
        flex-direction: column !important;
        gap: 0.7rem !important;
        align-items: stretch !important;
    }

    .hero-btn {
        width: 100%;
        font-size: 1.05rem;
        padding: 0.9rem 1.2rem;
        margin: 0 !important;
    }

    .hero-features {
        flex-direction: column;
        gap: 1.5rem;
    }
}

@media (max-width: 576px) {
    .hero-title {
        font-size: 1.3rem;
    }

    .hero-subtitle {
        font-size: 0.95rem;
    }

    .hero-description {
        font-size: 0.95rem;
    }

    .hero-btn {
        font-size: 0.98rem;
        padding: 0.8rem 1rem;
    }
}

/* Animation Enhancements */
.animate__animated {
    animation-duration: 1s;
}

.animate__delay-1s {
    animation-delay: 0.5s;
}

.animate__delay-2s {
    animation-delay: 1s;
}

@keyframes float {
    0% {
        transform: translateY(0px);
    }

    50% {
        transform: translateY(-10px);
    }

    100% {
        transform: translateY(0px);
    }
}

.hero-btn {
    animation: float 3s ease-in-out infinite;
}

.hero-btn:nth-child(2) {
    animation-delay: 0.5s;
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
    height: 320px;
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
}

.book-image {
    width: 100%;
    height: 100%;
    max-width: 220px;
    max-height: 300px;
    object-fit: contain;
    transition: transform 0.3s;
    background: white;
    padding: 10px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
    margin: 0 auto;
    display: block;
}

.book-card:hover .book-image {
    transform: scale(1.05);
}

@media (max-width: 992px) {
    .book-image-container {
        height: 220px;
    }

    .book-image {
        max-width: 150px;
        max-height: 180px;
    }
}

@media (max-width: 768px) {
    .book-image-container {
        height: 160px;
        padding: 0.5rem;
    }

    .book-image {
        max-width: 100px;
        max-height: 120px;
        padding: 5px;
    }
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
    height: 300px;
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
}

.paint-image {
    width: 100%;
    height: 100%;
    object-fit: contain;
    transition: transform 0.3s;
    background: white;
    padding: 10px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
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
    max-height: 500px;
    width: 100%;
    object-fit: contain;
    background: white;
    padding: 20px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
}

.modal-qty {
    width: 60px !important;
}

.modal-body .col-md-6:first-child {
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
}

/* Responsive adjustments */
@media (max-width: 768px) {

    .book-image-container,
    .paint-image-container {
        height: 250px;
    }

    .product-modal-image {
        max-height: 300px;
    }
}

.btn-contact {
    background: linear-gradient(90deg, #ff9800, #ff5722);
    color: #fff !important;
    border: none;
    box-shadow: 0 2px 10px rgba(255, 152, 0, 0.15);
    font-weight: 600;
    letter-spacing: 0.5px;
    transition: background 0.3s, box-shadow 0.3s, transform 0.2s;
}

.btn-contact i {
    margin-right: 6px;
}

.btn-contact:hover,
.btn-contact:focus {
    background: linear-gradient(90deg, #ff5722, #ff9800);
    color: #fff;
    box-shadow: 0 4px 20px rgba(255, 87, 34, 0.25);
    transform: translateY(-2px) scale(1.04);
}

/* Checkout Overlay Styles */
#checkout-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(0, 0, 0, 0.35);
    z-index: 3000;
    align-items: center;
    justify-content: center;
}

.checkout-modal {
    min-width: 260px;
    max-width: 90vw;
    border-radius: 1.5rem;
    box-shadow: 0 4px 20px rgba(25, 135, 84, 0.25);
    text-align: center;
}

@media (max-width: 768px) {
    .checkout-modal {
        min-width: unset;
        width: 95vw;
        padding: 1.5rem 0.5rem;
    }
}

@media (max-width: 576px) {
    .checkout-modal {
        border-radius: 0.5rem;
        padding: 1rem 0.2rem;
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
                if (data.items && data.items.length > 0) {
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

    // Show toast notification
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

    // Initial cart count update
    updateCartCount();
});
</script>

<!-- Checkout Overlay (hidden by default) -->
<div id="checkout-overlay"
    style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.35); z-index:3000; align-items:center; justify-content:center;">
    <div class="checkout-modal bg-success text-white shadow-lg rounded-4 p-4 d-flex flex-column align-items-center animate__animated animate__fadeInUp"
        style="min-width:260px; max-width:90vw;">
        <button type="button" class="btn-close btn-close-white align-self-end mb-2" aria-label="Close"
            onclick="toggleCheckoutOverlay(false)"></button>
        <div class="d-flex align-items-center mb-3">
            <i class="fas fa-shopping-cart fa-2x me-3"></i>
            <span class="fs-4 fw-bold">Proceed to Checkout</span>
        </div>
        <div class="mb-3">
            <span id="overlay-cart-total" class="badge bg-light text-dark fs-5 fw-bold"></span>
        </div>
        <button class="btn btn-light btn-lg fw-bold px-4" onclick="window.location.href='../checkout/checkout'">Go to
            Checkout</button>
    </div>
</div>

<script>
// Overlay logic
function toggleCheckoutOverlay(show) {
    document.getElementById('checkout-overlay').style.display = show ? 'flex' : 'none';
}
// Update overlay cart total and show/hide overlay button
function updateCheckoutOverlayBtn() {
    fetch('../includes/cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'action=get'
        })
        .then(response => response.json())
        .then(data => {
            const overlay = document.getElementById('checkout-overlay');
            const total = document.getElementById('overlay-cart-total');
            if (data.items && data.items.length > 0) {
                let sum = 0;
                data.items.forEach(item => {
                    sum += item.price * item.quantity;
                });
                total.textContent = '$' + sum.toFixed(2);
                // Show overlay if not already open
                toggleCheckoutOverlay(true);
                overlay.classList.add('showing');
            } else {
                toggleCheckoutOverlay(false);
                overlay.classList.remove('showing');
            }
        });
}
// Show overlay on page load if cart has items
window.addEventListener('DOMContentLoaded', function() {
    updateCheckoutOverlayBtn();
    document.querySelectorAll('.add-to-cart').forEach(btn => {
        btn.addEventListener('click', function() {
            setTimeout(updateCheckoutOverlayBtn, 700);
        });
    });
});
</script>
</body>

</html>