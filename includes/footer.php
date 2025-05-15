    </main>

    <!-- Footer -->
    <footer class="bg-light py-4 mt-auto">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5><?php echo SITE_NAME; ?></h5>
                    <p class="text-muted">Your one-stop shop for digital products and services.</p>
                </div>
                <div class="col-md-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="index">Home</a></li>
                        <li><a href="products">Products</a></li>
                        <li><a href="contact">Contact</a></li>
                        <?php if (isLoggedIn()): ?>
                        <li><a href="purchases">My Purchases</a></li>
                        <?php else: ?>
                        <li><a href="login">Login</a></li>
                        <li><a href="signup">Sign Up</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Contact Us</h5>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-envelope"></i> <?php echo SITE_EMAIL; ?></li>
                        <li><i class="fas fa-phone"></i> +1 (555) 123-4567</li>
                        <li><i class="fas fa-map-marker-alt"></i> 123 Commerce St, City, Country</li>
                    </ul>
                    <div class="social-links mt-3">
                        <a href="#" class="me-2"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="me-2"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="me-2"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="me-2"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
            </div>
            <hr>
            <div class="text-center">
                <p class="mb-0">&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JS -->
    <script src="assets/js/main.js"></script>
    <?php if ($current_page === 'cart'): ?>
    <script src="assets/js/cart.js"></script>
    <?php elseif ($current_page === 'checkout'): ?>
    <script src="https://www.paypal.com/sdk/js?client-id=<?php echo PAYPAL_CLIENT_ID; ?>&currency=USD"></script>
    <script src="assets/js/checkout.js"></script>
    <?php endif; ?>
    </body>

    </html>