<?php
require_once '../includes/bootstrap.php';

// Fetch books first, then paints
try {
    $sql_books = "SELECT * FROM products WHERE type = 'book' ORDER BY timestamp DESC";
    $sql_ebooks = "SELECT * FROM products WHERE type = 'ebook' ORDER BY timestamp DESC";
    $sql_paints = "SELECT * FROM products WHERE type = 'paint' ORDER BY timestamp DESC";

    $result_books = $conn->query($sql_books);
    if (!$result_books) {
        throw new Exception("Error fetching books: " . $conn->error);
    }

    $result_ebooks = $conn->query($sql_ebooks);
    if (!$result_ebooks) {
        throw new Exception("Error fetching ebooks: " . $conn->error);
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
<section class="hero-section-bookshelf">
    <div class="hero-overlay"></div>
    <div class="container hero-flex">
        <!-- Left: Fairy Book Cover -->
        <div class="hero-fairy-col">
            <img src="../resources/fairy.jpg" alt="Ryvah Fairy Book" class="hero-fairy-img">
        </div>
        <!-- Center: Main Content -->
        <div class="hero-main-col text-center">
            <img src="../resources/logo.jpeg" alt="RYVAH" class="hero-main-logo mb-3">
            <h1 class="hero-title">Welcome to Ryvah Books</h1>
            <div class="hero-subtitle">Your Gateway to Digital Knowledge</div>
            <div class="hero-description">Discover Amazing Books and Artworks</div>
            <div class="hero-btn-row">
                <a href="#books" class="btn btn-primary btn-lg hero-btn"><i class="fas fa-book"></i> Explore Books</a>
                <a href="#paintings" class="btn btn-dark btn-lg hero-btn"><i class="fas fa-palette"></i> View
                    Artworks</a>
                <a href="contact" class="btn btn-warning btn-lg hero-btn"><i class="fas fa-envelope"></i> Contact Us</a>
            </div>
            <div class="hero-features mt-4">
                <div class="feature"><i class="fas fa-shipping-fast"></i><span>Instant Delivery</span></div>
                <div class="feature"><i class="fas fa-lock"></i><span>Secure Payment</span></div>
                <div class="feature"><i class="fas fa-headset"></i><span>24/7 Support</span></div>
            </div>
        </div>
    </div>
</section>

<!-- Sticky Proceed to Cart Button -->
<button id="proceed-to-cart-btn" class="proceed-to-cart-btn" onclick="window.location.href='../checkout/checkout'">
    <span><i class="fas fa-shopping-cart"></i> Checkout</span>
    <span id="proceed-cart-count" class="cart-count-btn"></span>
</button>

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
                        <span class="badge bg-primary">Book</span>
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

<!-- Ebooks Section -->
<section id="ebooks" class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-4 section-title">Featured Ebooks</h2>
        <div class="row g-4">
            <?php
            if ($result_ebooks->num_rows > 0) {
                while ($ebook = $result_ebooks->fetch_assoc()) {
            ?>
            <div class="col-6 col-md-4 col-lg-3 animate__animated animate__fadeIn">
                <div class="card h-100 book-card">
                    <div class="book-badge">
                        <span class="badge bg-info">E-Book</span>
                    </div>
                    <div class="book-image-container">
                        <img src="../admin/<?php echo htmlspecialchars($ebook['thumbs']); ?>"
                            class="card-img-top book-image" alt="<?php echo htmlspecialchars($ebook['name']); ?>">
                        <div class="book-overlay">
                            <button class="btn btn-light btn-sm quick-view" data-bs-toggle="modal"
                                data-bs-target="#productModal" data-id="<?php echo $ebook['id']; ?>"
                                data-name="<?php echo htmlspecialchars($ebook['name']); ?>"
                                data-price="<?php echo $ebook['price']; ?>"
                                data-description="<?php echo htmlspecialchars($ebook['description']); ?>"
                                data-author="<?php echo htmlspecialchars($ebook['author']); ?>"
                                data-image="../admin/<?php echo htmlspecialchars($ebook['thumbs']); ?>"
                                data-stock="<?php echo $ebook['stock_quantity']; ?>">
                                <i class="fas fa-eye"></i> Quick View
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <h6 class="card-title book-title text-truncate"><?php echo htmlspecialchars($ebook['name']); ?>
                        </h6>
                        <p class="card-text mb-2">
                            <small class="text-muted"><i class="fas fa-pen-fancy"></i>
                                <?php echo htmlspecialchars($ebook['author']); ?></small><br>
                            <span class="price">$<?php echo number_format($ebook['price'], 2); ?></span>
                        </p>
                        <button class="btn btn-primary btn-sm w-100 add-to-cart"
                            data-product-id="<?php echo $ebook['id']; ?>">
                            <i class="fas fa-cart-plus"></i> Add to Cart
                        </button>
                    </div>
                </div>
            </div>
            <?php
                }
            } else {
                echo '<div class="col-12 text-center"><p>No ebooks available at the moment.</p></div>';
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
                <div class="row g-3">
                    <div class="col-md-6 d-flex align-items-center justify-content-center">
                        <img src="" alt="Product Image" class="img-fluid rounded product-modal-image"
                            style="max-height:350px;object-fit:contain;background:#f8f9fa;">
                    </div>
                    <div class="col-md-6">
                        <h4 class="product-modal-name mb-2"></h4>
                        <p class="text-muted mb-1">By <span class="product-modal-author"></span></p>
                        <h5 class="mb-3">Price: $<span class="product-modal-price"></span></h5>
                        <div class="mb-2">
                            <small class="text-muted">Stock: <span class="product-modal-stock"></span></small>
                        </div>
                        <div class="quantity-selector mb-3">
                            <label class="form-label">Quantity:</label>
                            <div class="input-group" style="width: 150px;">
                                <button class="btn btn-outline-secondary modal-decrease-qty" type="button"><i
                                        class="fas fa-minus"></i></button>
                                <input type="number" class="form-control text-center modal-qty" value="1" min="1">
                                <button class="btn btn-outline-secondary modal-increase-qty" type="button"><i
                                        class="fas fa-plus"></i></button>
                            </div>
                        </div>
                        <div class="mb-3">
                            <strong>Total: $<span class="product-modal-total"></span></strong>
                        </div>
                        <button class="btn btn-primary modal-add-to-cart w-100" data-product-id="">
                            <i class="fas fa-cart-plus"></i> Add to Cart
                        </button>
                    </div>
                    <div class="col-12 mt-3">
                        <h6>Description</h6>
                        <div class="product-modal-description-scroll"
                            style="max-height:180px;overflow-y:auto;background:#f8f9fa;padding:1rem;border-radius:8px;">
                            <p class="product-modal-description mb-0"></p>
                        </div>
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
/* Base styles for better responsiveness */
html {
    font-size: 16px;
}

body {
    font-family: 'Montserrat', Arial, sans-serif;
    line-height: 1.6;
    overflow-x: hidden;
}

/* Hero Section Responsive Styles */
.hero-section-bookshelf {
    background: linear-gradient(rgba(0, 0, 0, 0.2), rgba(0, 0, 0, 0.2)), url('https://images.unsplash.com/photo-1507842217343-583bb7270b66?ixlib=rb-4.0.3');
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
    min-height: 100vh;
    display: flex;
    align-items: center;
    color: white;
    position: relative;
    padding: 2rem 0;
    overflow: hidden;
}

.hero-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.3));
    z-index: 1;
}

.hero-flex {
    position: relative;
    z-index: 2;
    display: flex;
    align-items: flex-start;
    justify-content: flex-start;
    gap: 2rem;
    min-height: 100vh;
    padding: 1rem;
    width: 100%;
}

.hero-fairy-col {
    flex: 0 0 300px;
    display: flex;
    align-items: flex-start;
    justify-content: flex-start;
    margin-left: 0;
    margin-top: 0;
    position: relative;
    z-index: 1;
}

.hero-fairy-img {
    max-width: 100%;
    max-height: 520px;
    border-radius: 5px;
    box-shadow: 0 4px 24px rgba(0, 0, 0, 0.5);
    object-fit: contain;
}

.hero-main-col {
    flex: 1 1 500px;
    color: #fff;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    margin-left: auto;
    margin-right: auto;
    padding: 1rem;
    position: relative;
    z-index: 2;
    background: rgba(0, 0, 0, 0.55);
    border-radius: 12px;
    box-shadow: 0 4px 24px rgba(0, 0, 0, 0.2);
}

.hero-main-logo {
    max-width: 280px;
    width: 100%;
    margin-bottom: 1.5rem;
}

.hero-title {
    font-size: clamp(2rem, 5vw, 3rem);
    font-weight: 700;
    margin-bottom: 0.5rem;
    line-height: 1.2;
}

.hero-subtitle {
    font-size: clamp(1.2rem, 3vw, 1.5rem);
    font-weight: 600;
    color: #ffd700;
    margin-bottom: 0.5rem;
}

.hero-description {
    font-size: clamp(1rem, 2.5vw, 1.2rem);
    margin-bottom: 1.5rem;
}

.hero-btn-row {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
    margin-bottom: 1.5rem;
}

.hero-btn {
    font-size: clamp(0.9rem, 2vw, 1.1rem);
    font-weight: 600;
    padding: 0.75rem 1.5rem;
    border-radius: 50px;
    white-space: nowrap;
}

.hero-features {
    display: flex;
    gap: 2rem;
    justify-content: center;
    flex-wrap: wrap;
}

.feature {
    display: flex;
    flex-direction: column;
    align-items: center;
    color: #ffd700;
    font-size: clamp(0.9rem, 2vw, 1.1rem);
}

.feature i {
    font-size: clamp(1.5rem, 3vw, 2rem);
    margin-bottom: 0.3rem;
}

/* Responsive Breakpoints */
@media (max-width: 992px) {
    .hero-section-bookshelf {
        background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('../resources/fairy.jpg') center center/cover no-repeat !important;
    }

    .hero-fairy-col,
    .hero-main-logo {
        display: none !important;
    }

    .hero-main-col {
        width: 100%;
        max-width: 600px;
        /* background: rgba(0, 0, 0, 0.5); */
        border-radius: 10px;
        backdrop-filter: blur(5px);
        padding: 2rem;
        margin: 0 auto;
        z-index: 2;
    }

    .hero-flex {
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 100vh;
        padding: 1rem;
    }
}

@media (max-width: 768px) {
    .hero-section-bookshelf {
        padding: 1rem 0;
    }

    .hero-flex {
        padding: 0.5rem;
    }

    .hero-main-col {
        padding: 1.5rem;
    }

    .hero-btn-row {
        gap: 0.75rem;
    }

    .hero-features {
        gap: 1.5rem;
    }
}

@media (max-width: 576px) {
    .hero-section-bookshelf {
        padding: 0.5rem 0;
    }

    .hero-flex {
        padding: 0.25rem;
    }

    .hero-main-col {
        padding: 1rem 0.5rem;
        max-width: 98vw;
    }

    .hero-btn {
        width: 100%;
        max-width: 250px;
    }

    .hero-features {
        gap: 1rem;
    }

    .hero-fairy-col {
        opacity: 0.2;
    }
}

/* Product Cards Responsive Styles */
.book-card,
.paint-card {
    height: 100%;
    transition: transform 0.3s ease;
}

.book-image-container,
.paint-image-container {
    height: clamp(200px, 30vw, 320px);
    padding: 0.75rem;
}

.book-image,
.paint-image {
    max-height: 100%;
    width: auto;
    margin: 0 auto;
}

.book-title,
.paint-title {
    font-size: clamp(0.9rem, 2vw, 1.1rem);
    margin-bottom: 0.5rem;
}

.price {
    font-size: clamp(1rem, 2.5vw, 1.2rem);
}

/* Newsletter Section Responsive Styles */
.newsletter-section {
    padding: clamp(2rem, 5vw, 4rem) 0;
}

.newsletter-section h3 {
    font-size: clamp(1.5rem, 4vw, 2rem);
}

.newsletter-section p {
    font-size: clamp(1rem, 2.5vw, 1.2rem);
}

/* Footer Responsive Styles */
footer {
    font-size: clamp(0.9rem, 2vw, 1rem);
}

footer h5 {
    font-size: clamp(1.1rem, 2.5vw, 1.3rem);
    margin-bottom: 1rem;
}

.social-icon {
    width: clamp(30px, 5vw, 35px);
    height: clamp(30px, 5vw, 35px);
    line-height: clamp(30px, 5vw, 35px);
    font-size: clamp(0.9rem, 2vw, 1rem);
}

/* Utility Classes */
.text-truncate {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.img-fluid {
    max-width: 100%;
    height: auto;
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

.author,
.artist {
    color: #666;
    font-size: 0.9rem;
}

.newsletter-section {
    background: linear-gradient(45deg, #2c3e50, #3498db);
    color: white;
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
.checkout-overlay {
    position: fixed;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 2050;
    display: none;
    justify-content: center;
    pointer-events: none;
}

.checkout-overlay.active {
    display: flex;
    animation: slideUpOverlay 0.5s cubic-bezier(.4, 0, .2, 1);
    pointer-events: auto;
}

@keyframes slideUpOverlay {
    from {
        transform: translateY(100%);
        opacity: 0;
    }

    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.checkout-modal {
    background: linear-gradient(135deg, #007bff, #00c6ff);
    color: #fff;
    border-radius: 2rem 2rem 0 0;
    box-shadow: 0 -4px 24px rgba(0, 0, 0, 0.18);
    min-width: 220px;
    min-height: 80px;
    padding: 1.2rem 2rem 1.2rem 2rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    pointer-events: auto;
}

@media (max-width: 576px) {
    .checkout-modal {
        min-width: 90vw;
        padding: 1rem 0.5rem;
    }
}

.checkout-modal .btn {
    transition: all 0.3s ease;
}

.checkout-modal .btn:hover {
    transform: translateY(-1px);
}

.checkout-modal .btn-outline-light:hover {
    background-color: rgba(255, 255, 255, 0.1);
}

.success-toast {
    z-index: 2000 !important;
}

.toast-content {
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    padding: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.toast-content i {
    font-size: 24px;
    color: #28a745;
    margin-right: 10px;
}

.toast-content span {
    font-size: 18px;
    font-weight: bold;
}

/* Sticky Proceed to Cart Button */
.proceed-to-cart-btn {
    position: fixed;
    bottom: 30px;
    right: 30px;
    z-index: 2000;
    background: linear-gradient(90deg, #007bff 60%, #00c6ff 100%);
    color: #fff;
    border: none;
    border-radius: 50px;
    padding: 1rem 2.2rem 1rem 2.2rem;
    font-size: 1.2rem;
    font-weight: 700;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: background 0.2s, transform 0.2s;
    cursor: pointer;
    outline: none;
    min-width: 220px;
    justify-content: space-between;
}

.proceed-to-cart-btn:hover {
    background: linear-gradient(90deg, #0056b3 60%, #007bff 100%);
    transform: translateY(-2px) scale(1.03);
}

.cart-count-btn {
    background: #fff;
    color: #007bff;
    border-radius: 50%;
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    font-weight: bold;
    margin-left: 10px;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.08);
    animation: popIn 0.3s ease-out;
}

@keyframes popIn {
    0% {
        transform: scale(0);
    }

    50% {
        transform: scale(1.2);
    }

    100% {
        transform: scale(1);
    }
}

@media (max-width: 576px) {
    .proceed-to-cart-btn {
        bottom: 10px;
        right: 10px;
        min-width: 150px;
        padding: 0.7rem 1.2rem;
        font-size: 1rem;
    }

    .cart-count-btn {
        width: 22px;
        height: 22px;
        font-size: 0.95rem;
    }
}

.hero-section-bookshelf {
    animation: fadeInHero 1.2s cubic-bezier(.4, 0, .2, 1);
}

@keyframes fadeInHero {
    from {
        opacity: 0;
        transform: translateY(40px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* --- Modal ScrollNav & Responsiveness --- */
#productModal .modal-dialog {
    max-width: 600px;
    width: 95vw;
}

#productModal .modal-content {
    max-height: 90vh;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

#productModal .modal-body {
    overflow-y: auto;
    flex: 1 1 auto;
    padding-bottom: 0;
}

.product-modal-scrollnav {
    position: sticky;
    top: 0;
    background: rgba(255, 255, 255, 0.95);
    z-index: 2;
    display: flex;
    gap: 1.5rem;
    border-bottom: 1px solid #eee;
    padding: 0.5rem 0;
    margin-bottom: 1rem;
}

.product-modal-scrollnav a {
    color: #007bff;
    font-weight: 600;
    text-decoration: none;
    padding: 0.2rem 0.5rem;
    border-radius: 4px;
}

.product-modal-scrollnav a.active,
.product-modal-scrollnav a:hover {
    background: #e9f5ff;
}

/* --- Hero Section Readability --- */
@media (max-width: 576px) {
    .hero-title {
        font-size: 1.5rem;
    }

    .hero-subtitle {
        font-size: 1.1rem;
    }

    .hero-description {
        font-size: 1rem;
    }
}
</style>

<!-- Custom JavaScript for quantity and price calculation -->
<script>
// --- Modern Toast Logic ---
function showToast(type, message) {
    let toast = document.getElementById('modern-toast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'modern-toast';
        toast.style.position = 'fixed';
        toast.style.bottom = '30px';
        toast.style.right = '30px';
        toast.style.zIndex = '3000';
        toast.style.minWidth = '220px';
        toast.style.maxWidth = '90vw';
        toast.style.display = 'flex';
        toast.style.alignItems = 'center';
        toast.style.gap = '1rem';
        toast.style.padding = '1rem 1.5rem';
        toast.style.borderRadius = '1rem';
        toast.style.boxShadow = '0 4px 24px rgba(0,0,0,0.18)';
        toast.style.fontSize = '1rem';
        toast.style.transition = 'opacity 0.3s, transform 0.3s';
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(40px)';
        toast.innerHTML =
            `<span id="toast-icon"></span><span id="toast-msg"></span><button id="toast-close" style="background:none;border:none;font-size:1.2rem;color:#fff;cursor:pointer;margin-left:auto;">&times;</button>`;
        document.body.appendChild(toast);
        document.getElementById('toast-close').onclick = () => hideToast();
    }
    // Set icon and color
    const icon = document.getElementById('toast-icon');
    const msg = document.getElementById('toast-msg');
    if (type === 'success') {
        toast.style.background = 'linear-gradient(90deg,#28a745,#43e97b)';
        icon.innerHTML = '<i class="fas fa-check-circle"></i>';
    } else {
        toast.style.background = 'linear-gradient(90deg,#dc3545,#ff7675)';
        icon.innerHTML = '<i class="fas fa-times-circle"></i>';
    }
    icon.style.fontSize = '1.5rem';
    icon.style.color = '#fff';
    msg.textContent = message;
    msg.style.color = '#fff';
    toast.style.opacity = '1';
    toast.style.transform = 'translateY(0)';
    setTimeout(hideToast, 3500);
}

function hideToast() {
    const toast = document.getElementById('modern-toast');
    if (toast) {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(40px)';
        setTimeout(() => {
            if (toast.parentNode) toast.parentNode.removeChild(toast);
        }, 400);
    }
}

// --- Cart Count & Overlay Logic (Event-Driven) ---
function updateCartUI(data) {
    // Update cart count in header
    const cartCount = document.querySelector('.cart-count');
    const navIcon = document.querySelector('.nav-icon.position-relative');
    if (data.items && data.items.length > 0) {
        if (!cartCount) {
            const span = document.createElement('span');
            span.className = 'cart-count';
            span.textContent = data.items.length;
            navIcon.appendChild(span);
        } else {
            cartCount.textContent = data.items.length;
        }
    } else if (cartCount) {
        cartCount.remove();
    }
    // Update checkout overlay
    const overlay = document.getElementById('checkout-overlay');
    const total = document.getElementById('overlay-cart-total');
    if (data.items && data.items.length > 0) {
        let sum = 0;
        data.items.forEach(item => {
            sum += item.price * item.quantity;
        });
        total.textContent = '$' + sum.toFixed(2);
        overlay.classList.add('active');
    } else {
        overlay.classList.remove('active');
    }
}

function fetchCartAndUpdateUI(showToastType, toastMsg) {
    fetch('../includes/cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'action=get'
        })
        .then(response => response.json())
        .then(data => {
            updateCartUI(data);
            if (showToastType && toastMsg) showToast(showToastType, toastMsg);
        });
}

// Add to cart event
function addToCart(productId, quantity) {
    fetch('../includes/cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `action=add&product_id=${productId}&quantity=${quantity}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                fetchCartAndUpdateUI('success', 'Item added to cart');
            } else {
                showToast('error', data.message || 'Failed to add item');
            }
        });
}

document.querySelectorAll('.add-to-cart').forEach(button => {
    button.addEventListener('click', function() {
        const productId = this.dataset.productId;
        addToCart(productId, 1);
    });
});

// Modal add to cart
const modalAddBtn = document.querySelector('.modal-add-to-cart');
if (modalAddBtn) {
    modalAddBtn.addEventListener('click', function() {
        const productId = this.dataset.productId;
        const quantity = document.querySelector('.modal-qty').value;
        addToCart(productId, quantity);
    });
}

// On page load, update cart UI
window.addEventListener('DOMContentLoaded', function() {
    fetchCartAndUpdateUI();
});

// Product Modal Functionality (Quick View) - Robust Event Delegation
(function() {
    const productModal = document.getElementById('productModal');
    if (!productModal) return;
    // Use event delegation for all .quick-view buttons
    document.body.addEventListener('click', function(event) {
        const button = event.target.closest('.quick-view');
        if (button) {
            // Set modal fields
            productModal.querySelector('.modal-title').textContent = button.dataset.name || '';
            productModal.querySelector('.product-modal-name').textContent = button.dataset.name || '';
            productModal.querySelector('.product-modal-author').textContent = button.dataset.author || '';
            productModal.querySelector('.product-modal-price').textContent = button.dataset.price || '';
            productModal.querySelector('.product-modal-stock').textContent = button.dataset.stock || '';
            productModal.querySelector('.modal-add-to-cart').dataset.productId = button.dataset.id || '';
            // Set image or placeholder
            const img = productModal.querySelector('.product-modal-image');
            img.src = button.dataset.image && button.dataset.image !== '../admin/' ? button.dataset.image :
                'https://via.placeholder.com/300x400?text=No+Image';
            img.alt = button.dataset.name || 'Product Image';
            // Set description (scrollable)
            productModal.querySelector('.product-modal-description').textContent = button.dataset
                .description || 'No description available.';
            // Reset quantity and total
            productModal.querySelector('.modal-qty').value = 1;
            updateModalTotal();
        }
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
        const maxStock = parseInt(productModal.querySelector('.product-modal-stock').textContent) || 9999;
        if (parseInt(modalQty.value) < maxStock) {
            modalQty.value = parseInt(modalQty.value) + 1;
            updateModalTotal();
        }
    });
    modalQty.addEventListener('change', function() {
        const maxStock = parseInt(productModal.querySelector('.product-modal-stock').textContent) || 9999;
        if (this.value < 1) this.value = 1;
        if (this.value > maxStock) this.value = maxStock;
        updateModalTotal();
    });

    function updateModalTotal() {
        const price = parseFloat(productModal.querySelector('.product-modal-price').textContent) || 0;
        const quantity = parseInt(modalQty.value) || 1;
        const total = price * quantity;
        productModal.querySelector('.product-modal-total').textContent = total.toFixed(2);
    }
})();
</script>

<!-- Checkout Overlay -->
<div id="checkout-overlay" class="checkout-overlay">
    <div
        class="checkout-modal bg-success text-white shadow-lg rounded-4 p-3 d-flex flex-column align-items-center animate__animated animate__fadeInUp">
        <div class="d-flex align-items-center mb-2">
            <i class="fas fa-shopping-cart me-2"></i>
            <span class="fs-6 fw-bold">Your Cart</span>
        </div>
        <div class="mb-2">
            <span id="overlay-cart-total" class="badge bg-light text-dark fs-6 fw-bold"></span>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-light btn-sm fw-bold px-3" onclick="window.location.href='../checkout/checkout'">
                <i class="fas fa-arrow-right me-1"></i> Checkout
            </button>
            <button class="btn btn-outline-light btn-sm fw-bold px-3" onclick="continueShopping()">
                <i class="fas fa-shopping-bag me-1"></i> Continue
            </button>
        </div>
    </div>
</div>

<!-- Success Toast -->
<div id="success-toast" class="success-toast" style="display:none;">
    <div class="toast-content">
        <i class="fas fa-check-circle"></i>
        <span id="success-toast-message"></span>
    </div>
</div>

<script>
function continueShopping() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}
</script>
</body>

</html>