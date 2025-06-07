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
<!-- Live Cart Summary (Index Page Only) -->
<div id="live-cart-summary" class="live-cart-summary">
    <div class="cart-summary-content">
        <div class="cart-summary-header">
            <div class="cart-icon">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <div class="cart-info">
                <div class="cart-total-amount" id="live-cart-total">$0.00</div>
                <div class="cart-item-count">
                    <span id="live-cart-count">0</span> items
                </div>
            </div>
        </div>
        <div class="cart-summary-actions">
            <a href="../pages/cart" class="cart-action-btn view-cart-btn">
                <i class="fas fa-shopping-bag"></i>
                View Cart
            </a>
            <a href="../checkout" class="cart-action-btn checkout-btn">
                <i class="fas fa-credit-card"></i>
                Checkout
            </a>
        </div>

    </div>
</div>

<!-- Success Message Toast -->
<?php if (isset($_SESSION['success_message'])): ?>
<div class="position-fixed top-50 end-0 p-3" style="z-index: 1050">
    <div class="toast show" role="alert">
        <div class="toast-header bg-success text-white">
            <i class="fas fa-check-circle me-2"></i>
            <strong class="me-auto">Success</strong>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body">
            <?php
                echo htmlspecialchars($_SESSION['success_message']);
                unset($_SESSION['success_message']);
                ?>
        </div>
    </div>
</div>
<?php endif; ?>
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


<?php include '../includes/layouts/footer.php'; ?>
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
    background: linear-gradient(rgba(0, 0, 0, 0.2), rgba(0, 0, 0, 0.2)), url('../resources/book.jpeg');
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

/* Fix for dropdown visibility on index page */
.navbar {
    overflow: visible !important;
}

.navbar .container {
    overflow: visible !important;
}

.navbar-right {
    overflow: visible !important;
}

.dropdown-menu {
    z-index: 10000 !important;
    position: absolute !important;
    display: none;
}

.dropdown-menu.show {
    display: block !important;
}

.dropdown:hover .dropdown-menu,
.dropdown.show .dropdown-menu {
    z-index: 10000 !important;
}

/* Ensure toast containers don't interfere with dropdowns */
.toast-container {
    pointer-events: none;
}

.toast-container .toast {
    pointer-events: auto;
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

/* Live Cart Summary Styles - Compact & Responsive */
.live-cart-summary {
    position: fixed;
    bottom: 15px;
    right: 15px;
    z-index: 2050;
    max-width: 280px;
    width: auto;
    opacity: 0;
    transform: translateX(100%) scale(0.8);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    pointer-events: none;
}

.live-cart-summary.show {
    opacity: 1;
    transform: translateX(0) scale(1);
    pointer-events: auto;
}

.cart-summary-content {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 16px;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    overflow: hidden;
    position: relative;
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.cart-summary-header {
    padding: 12px 16px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.cart-icon {
    background: rgba(255, 255, 255, 0.15);
    border-radius: 50%;
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    flex-shrink: 0;
}

.cart-info {
    flex: 1;
    min-width: 0;
}

.cart-total-amount {
    font-size: 18px;
    font-weight: 700;
    margin-bottom: 1px;
    background: linear-gradient(45deg, #fff, #f8f9fa);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    line-height: 1.2;
}

.cart-item-count {
    font-size: 11px;
    opacity: 0.9;
    font-weight: 500;
    line-height: 1.2;
}

.cart-summary-actions {
    padding: 0 16px 12px;
    display: flex;
    gap: 8px;
}

.cart-action-btn {
    flex: 1;
    padding: 8px 12px;
    border-radius: 10px;
    text-decoration: none;
    text-align: center;
    font-weight: 600;
    font-size: 11px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    position: relative;
    overflow: hidden;
    line-height: 1;
}

.cart-action-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.6s;
}

.cart-action-btn:hover::before {
    left: 100%;
}

.cart-action-btn i {
    font-size: 10px;
}

.view-cart-btn {
    background: rgba(255, 255, 255, 0.15);
    color: white;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.view-cart-btn:hover {
    background: rgba(255, 255, 255, 0.25);
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.checkout-btn {
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
    border: none;
    box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
}

.checkout-btn:hover {
    background: linear-gradient(135deg, #218838, #1aa085);
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(40, 167, 69, 0.4);
}

/* Pulse animation for new items */
@keyframes cartSummaryPulse {
    0% {
        transform: translateX(0) scale(1);
    }

    50% {
        transform: translateX(0) scale(1.03);
    }

    100% {
        transform: translateX(0) scale(1);
    }
}

.live-cart-summary.pulse {
    animation: cartSummaryPulse 0.5s ease;
}

/* Mobile responsive */
@media (max-width: 768px) {
    .live-cart-summary {
        bottom: 12px;
        right: 12px;
        max-width: 150px;
    }

    .cart-summary-header {
        padding: 10px 14px;
        gap: 10px;
    }

    .cart-icon {
        width: 32px;
        height: 32px;
        font-size: 14px;
    }

    .cart-total-amount {
        font-size: 16px;
    }

    .cart-item-count {
        font-size: 10px;
    }

    .cart-summary-actions {
        padding: 0 14px 10px;
        gap: 6px;
    }

    .cart-action-btn {
        padding: 7px 10px;
        font-size: 10px;
    }

    .cart-action-btn i {
        font-size: 9px;
    }
}

@media (max-width: 576px) {
    .live-cart-summary {
        bottom: 10px;
        right: 10px;
        max-width: 180px;
    }

    .cart-summary-header {
        padding: 10px 12px;
    }

    .cart-total-amount {
        font-size: 15px;
    }

    .cart-summary-actions {
        padding: 0 12px 10px;
        gap: 6px;
        flex-direction: column;
    }

    .cart-action-btn {
        padding: 8px 10px;
        font-size: 10px;
        gap: 3px;
    }
}

@media (max-width: 480px) {
    .live-cart-summary {
        max-width: calc(100vw - 20px);
    }

    .cart-summary-actions {
        flex-direction: column;
        /* Make buttons vertical on very small screens */
    }

    .cart-action-btn {
        padding: 6px 8px;
        font-size: 9px;
    }

    .cart-total-amount {
        font-size: 14px;
    }

    .cart-item-count {
        font-size: 9px;
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

// --- Live Cart Summary Logic (Index Page Only) ---
function updateLiveCartSummary(data) {
    const cartSummary = document.getElementById('live-cart-summary');
    const cartCount = document.getElementById('live-cart-count');
    const cartTotal = document.getElementById('live-cart-total');

    if (!cartSummary) return; // Only on index page

    if (data.items && data.items.length > 0) {
        let totalItems = 0;
        let totalAmount = 0;

        data.items.forEach(item => {
            totalItems += parseInt(item.quantity);
            totalAmount += parseFloat(item.price) * parseInt(item.quantity);
        });

        // Update summary content
        if (cartCount) {
            cartCount.textContent = totalItems;
        }
        if (cartTotal) {
            cartTotal.textContent = '$' + totalAmount.toFixed(2);
        }

        // Show summary with smooth animation
        showLiveCartSummary();

        // Add pulse effect for new items
        cartSummary.classList.add('pulse');
        setTimeout(() => {
            cartSummary.classList.remove('pulse');
        }, 600);
    } else {
        // Hide summary when cart is empty
        hideLiveCartSummary();
    }
}

function showLiveCartSummary() {
    const cartSummary = document.getElementById('live-cart-summary');
    if (cartSummary && !cartSummary.classList.contains('show')) {
        cartSummary.classList.add('show');
    }
}

function hideLiveCartSummary() {
    const cartSummary = document.getElementById('live-cart-summary');
    if (cartSummary && cartSummary.classList.contains('show')) {
        cartSummary.classList.remove('show');
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
        .then(response => {
            // Check if response is ok
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            // Get the raw text first to debug JSON issues
            return response.text();
        })
        .then(text => {
            // Try to parse as JSON
            try {
                const data = JSON.parse(text);
                updateLiveCartSummary(data);
                if (showToastType && toastMsg) showToast(showToastType, toastMsg);
            } catch (parseError) {
                console.error('JSON Parse Error:', parseError);
                console.error('Raw response:', text);
                console.error('Response length:', text.length);
                // Try to identify the issue
                if (text.includes('<!DOCTYPE') || text.includes('<html')) {
                    console.error('Response contains HTML instead of JSON');
                }
            }
        })
        .catch(error => {
            console.error('Error fetching cart data:', error);
        });
}

// Cart functionality is now handled by the global header functions

// On page load, update cart UI with a small delay to ensure everything is loaded
window.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        fetchCartAndUpdateUI();
    }, 500); // 500ms delay to ensure page is fully loaded
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


<script>
function continueShopping() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}
</script>

<script>
// Use the global cart system for real-time updates
document.addEventListener('DOMContentLoaded', function() {
    // Fix dropdown visibility issue on index page
    setTimeout(() => {
        console.log('Fixing dropdown visibility...');

        // Ensure Bootstrap dropdowns are properly initialized
        const dropdownTriggers = document.querySelectorAll('[data-bs-toggle="dropdown"]');
        console.log('Found dropdown triggers:', dropdownTriggers.length);

        dropdownTriggers.forEach((trigger, index) => {
            console.log(`Initializing dropdown ${index}:`, trigger);

            // Remove any existing event listeners to avoid conflicts
            if (trigger.hasAttribute('data-dropdown-initialized')) {
                trigger.removeAttribute('data-dropdown-initialized');
            }

            // Create new dropdown instance
            try {
                const dropdown = new bootstrap.Dropdown(trigger);
                trigger.setAttribute('data-dropdown-initialized', 'true');
                console.log(`Dropdown ${index} initialized successfully`);

                // Add manual click handler as backup
                trigger.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    console.log('Dropdown clicked manually');
                    dropdown.toggle();
                });
            } catch (error) {
                console.error(`Error initializing dropdown ${index}:`, error);
            }
        });

        // Remove any interfering elements with high z-index
        const toastContainer = document.querySelector('.toast-container');
        if (toastContainer && !toastContainer.querySelector('.toast.show')) {
            toastContainer.style.pointerEvents = 'none';
            console.log('Toast container set to non-interactive');
        }

        // Ensure dropdown menus have proper z-index and visibility
        document.querySelectorAll('.dropdown-menu').forEach((menu, index) => {
            menu.style.zIndex = '10000';
            menu.style.position = 'absolute';
            console.log(`Dropdown menu ${index} z-index set to 10000`);
        });

        // Force show dropdown on manual trigger for testing
        window.forceShowDropdown = function() {
            const dropdown = document.querySelector('.dropdown-menu');
            if (dropdown) {
                dropdown.classList.add('show');
                dropdown.style.display = 'block';
                console.log('Dropdown forced to show');
            }
        };

        // Add fallback click handler for dropdown that doesn't rely on Bootstrap
        document.querySelectorAll('.nav-icon[data-bs-toggle="dropdown"]').forEach(trigger => {
            trigger.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                const dropdown = this.nextElementSibling;
                if (dropdown && dropdown.classList.contains('dropdown-menu')) {
                    // Close any other open dropdowns
                    document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                        if (menu !== dropdown) {
                            menu.classList.remove('show');
                            menu.style.display = 'none';
                        }
                    });

                    // Toggle current dropdown
                    if (dropdown.classList.contains('show')) {
                        dropdown.classList.remove('show');
                        dropdown.style.display = 'none';
                        console.log('Dropdown hidden');
                    } else {
                        dropdown.classList.add('show');
                        dropdown.style.display = 'block';
                        console.log('Dropdown shown');
                    }
                }
            });
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.dropdown')) {
                document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                    menu.classList.remove('show');
                    menu.style.display = 'none';
                });
            }
        });

    }, 200);

    // Replace local addToCart function with global one and add live summary updates
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const productId = this.dataset.productId;
            window.addToCart(productId, 1).then(() => {
                // Refresh live cart summary after adding item
                fetchCartAndUpdateUI();
            });
        });
    });

    // Update modal add to cart button  
    const modalAddBtn = document.querySelector('.modal-add-to-cart');
    if (modalAddBtn) {
        modalAddBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const productId = this.dataset.productId;
            const quantity = document.querySelector('.modal-qty').value || 1;
            window.addToCart(productId, parseInt(quantity)).then(() => {
                // Refresh live cart summary after adding item
                fetchCartAndUpdateUI();
                // Close modal after successful add
                const modal = bootstrap.Modal.getInstance(document.getElementById(
                    'productModal'));
                if (modal) modal.hide();
            });
        });
    }

    // Initialize cart summary on page load with delay
    setTimeout(() => {
        fetchCartAndUpdateUI();
    }, 500);

    // Also add a backup initialization after window load
    window.addEventListener('load', function() {
        setTimeout(() => {
            fetchCartAndUpdateUI();
        }, 1000);
    });
});
</script>

<script>
// Independent dropdown solution for index page - bypasses Bootstrap entirely
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        console.log('Setting up independent dropdown solution...');

        // Find the user profile icon
        const profileIcon = document.querySelector('.nav-icon[data-bs-toggle="dropdown"]');
        const dropdownMenu = document.querySelector('.dropdown-menu');

        if (profileIcon && dropdownMenu) {
            console.log('Profile icon and dropdown found');

            // Remove Bootstrap attributes to prevent conflicts
            profileIcon.removeAttribute('data-bs-toggle');

            // Add custom click handler
            profileIcon.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                console.log('Profile icon clicked');

                // Toggle dropdown visibility
                if (dropdownMenu.style.display === 'block') {
                    dropdownMenu.style.display = 'none';
                    dropdownMenu.classList.remove('show');
                    console.log('Dropdown hidden');
                } else {
                    dropdownMenu.style.display = 'block';
                    dropdownMenu.classList.add('show');
                    dropdownMenu.style.position = 'absolute';
                    dropdownMenu.style.top = '100%';
                    dropdownMenu.style.right = '0';
                    dropdownMenu.style.zIndex = '99999';
                    dropdownMenu.style.minWidth = '180px';
                    dropdownMenu.style.backgroundColor = 'white';
                    dropdownMenu.style.border = '1px solid #ccc';
                    dropdownMenu.style.borderRadius = '8px';
                    dropdownMenu.style.boxShadow = '0 4px 15px rgba(0,0,0,0.15)';
                    console.log('Dropdown shown');
                }
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.dropdown')) {
                    dropdownMenu.style.display = 'none';
                    dropdownMenu.classList.remove('show');
                }
            });

            console.log('Independent dropdown solution initialized');
        } else {
            console.error('Profile icon or dropdown menu not found');
        }
    }, 300);
});
</script>
</body>

</html>