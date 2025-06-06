<!-- Footer -->
<footer class="bg-dark text-light py-5">
    <div class="container">
        <div class="row g-4">
            <!-- Company Info -->
            <div class="col-lg-4">
                <h5 class="mb-4">About Ryvah Books</h5>
                <p class="mb-4">Your premier destination for digital books and original artwork. Discover amazing
                    stories and beautiful art pieces from talented creators worldwide.</p>
                <div class="social-links">
                    <a href="#" class="text-light me-3 social-icon"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="text-light me-3 social-icon"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-light me-3 social-icon"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="text-light social-icon"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="col-lg-4">
                <h5 class="mb-4">Quick Links</h5>
                <ul class="list-unstyled footer-links">
                    <li class="mb-2">
                        <a href="index" class="text-light text-decoration-none">
                            <i class="fas fa-angle-right me-2"></i>Home
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="categories" class="text-light text-decoration-none">
                            <i class="fas fa-angle-right me-2"></i>Categories
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="about" class="text-light text-decoration-none">
                            <i class="fas fa-angle-right me-2"></i>About Us
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="contact" class="text-light text-decoration-none">
                            <i class="fas fa-angle-right me-2"></i>Contact Us
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Newsletter -->
            <div class="col-lg-4">
                <h5 class="mb-4">Newsletter</h5>
                <p class="mb-4">Subscribe to our newsletter for updates on new releases and exclusive offers.</p>
                <form class="newsletter-form">
                    <div class="input-group mb-3">
                        <input type="email" class="form-control" placeholder="Enter your email" required>
                        <button class="btn btn-primary" type="submit">Subscribe</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Copyright -->
        <hr class="my-4">
        <div class="row">
            <div class="col-md-6 text-center text-md-start">
                <p class="mb-0">&copy; <?php echo date('Y'); ?> Ryvah Books. All rights reserved.</p>
            </div>
            <div class="col-md-6 text-center text-md-end">
                <a href="#" class="text-light text-decoration-none me-3">Privacy Policy</a>
                <a href="#" class="text-light text-decoration-none">Terms & Conditions</a>
            </div>
        </div>
    </div>
</footer>

<style>
.footer-links a {
    transition: all 0.3s ease;
}

.footer-links a:hover {
    color: #007bff !important;
    padding-left: 5px;
}

.social-icon {
    display: inline-block;
    width: 35px;
    height: 35px;
    line-height: 35px;
    text-align: center;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.1);
    transition: all 0.3s ease;
}

.social-icon:hover {
    background: #007bff;
    transform: translateY(-3px);
}

.newsletter-form .form-control {
    background: rgba(255, 255, 255, 0.1);
    border: none;
    color: white;
}

.newsletter-form .form-control:focus {
    background: rgba(255, 255, 255, 0.15);
    box-shadow: none;
    color: white;
}

.newsletter-form .form-control::placeholder {
    color: rgba(255, 255, 255, 0.5);
}

.newsletter-form .btn {
    padding-left: 1.5rem;
    padding-right: 1.5rem;
}
</style>