<?php include '../includes/layouts/header.php'; ?>

<!-- Contact Hero Section -->
<section class="contact-hero position-relative overflow-hidden">
    <div class="hero-overlay"></div>
    <div class="container position-relative text-center" style="z-index: 2;">
        <h1 class="display-4 fw-bold animate__animated animate__fadeInDown">Contact Us</h1>
        <p class="lead animate__animated animate__fadeInUp animate__delay-1s">We'd love to hear from you</p>
    </div>
</section>

<!-- Contact Information Section -->
<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="contact-info-card text-center p-4 shadow-sm rounded">
                    <i class="fas fa-map-marker-alt fa-3x text-primary mb-3"></i>
                    <h4>Our Location</h4>
                    <p>123 Book Street<br>Reading City, RC 12345<br>United States</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="contact-info-card text-center p-4 shadow-sm rounded">
                    <i class="fas fa-phone fa-3x text-primary mb-3"></i>
                    <h4>Phone Number</h4>
                    <p>+1 (555) 123-4567<br>+1 (555) 987-6543</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="contact-info-card text-center p-4 shadow-sm rounded">
                    <i class="fas fa-envelope fa-3x text-primary mb-3"></i>
                    <h4>Email Address</h4>
                    <p>info@ryvahbooks.com<br>support@ryvahbooks.com</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact Form Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-5">
                        <h2 class="text-center section-title mb-4">Send Us a Message</h2>
                        <form id="contactForm" class="needs-validation" novalidate>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control" id="name" placeholder="Your Name" required 
                                               minlength="2" maxlength="50" pattern="[A-Za-z\s]+">
                                        <label for="name">Your Name</label>
                                        <div class="invalid-feedback">Please enter a valid name (2-50 characters, letters only)</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating mb-3">
                                        <input type="email" class="form-control" id="email" placeholder="Your Email" required
                                               pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$">
                                        <label for="email">Your Email</label>
                                        <div class="invalid-feedback">Please enter a valid email address</div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control" id="subject" placeholder="Subject" required
                                               minlength="5" maxlength="100">
                                        <label for="subject">Subject</label>
                                        <div class="invalid-feedback">Subject must be between 5 and 100 characters</div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-floating mb-3">
                                        <textarea class="form-control" id="message" placeholder="Your Message" 
                                                  style="height: 150px" required minlength="10" maxlength="1000"></textarea>
                                        <label for="message">Your Message</label>
                                        <div class="invalid-feedback">Message must be between 10 and 1000 characters</div>
                                    </div>
                                </div>
                                <div class="col-12 text-center">
                                    <button type="submit" class="btn btn-primary btn-lg px-5" id="submitBtn">
                                        <span class="spinner-border spinner-border-sm d-none me-2" role="status" aria-hidden="true"></span>
                                        <i class="fas fa-paper-plane me-2"></i>Send Message
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Map Section -->
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="map-container shadow-sm rounded overflow-hidden">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d387193.30591910525!2d-74.25986432970718!3d40.697149422113014!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c24fa5d33f083b%3A0xc80b8f06e177fe62!2sNew%20York%2C%20NY%2C%20USA!5e0!3m2!1sen!2s!4v1645564750986!5m2!1sen!2s" 
                            width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.contact-hero {
    background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('https://images.unsplash.com/photo-1423666639041-f56000c27a9a?ixlib=rb-4.0.3');
    background-size: cover;
    background-position: center;
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
    background: linear-gradient(45deg, rgba(0,0,0,0.7), rgba(0,0,0,0.3));
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

.contact-info-card {
    transition: transform 0.3s;
    background: white;
}

.contact-info-card:hover {
    transform: translateY(-5px);
}

.form-floating > .form-control:focus ~ label,
.form-floating > .form-control:not(:placeholder-shown) ~ label {
    color: #007bff;
}

.form-control:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.25rem rgba(0,123,255,.25);
}

.form-control.is-invalid {
    border-color: #dc3545;
    padding-right: calc(1.5em + 0.75rem);
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right calc(0.375em + 0.1875rem) center;
    background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
}

.form-control.is-valid {
    border-color: #198754;
    padding-right: calc(1.5em + 0.75rem);
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%23198754' d='M2.3 6.73L.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right calc(0.375em + 0.1875rem) center;
    background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
}

.map-container {
    border-radius: 10px;
    overflow: hidden;
}

@media (max-width: 768px) {
    .contact-info-card {
        margin-bottom: 1rem;
    }
    
    .btn-lg {
        padding: 0.5rem 1rem;
        font-size: 1rem;
    }
}

@media (max-width: 480px) {
    .container {
        padding-left: 15px;
        padding-right: 15px;
    }
    
    .card-body {
        padding: 1rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('contactForm');
    const submitBtn = document.getElementById('submitBtn');
    const spinner = submitBtn.querySelector('.spinner-border');
    
    // Real-time validation
    const inputs = form.querySelectorAll('input, textarea');
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            validateInput(this);
        });
        
        input.addEventListener('blur', function() {
            validateInput(this);
        });
    });
    
    function validateInput(input) {
        if (input.checkValidity()) {
            input.classList.remove('is-invalid');
            input.classList.add('is-valid');
        } else {
            input.classList.remove('is-valid');
            input.classList.add('is-invalid');
        }
    }
    
    form.addEventListener('submit', async function(event) {
        event.preventDefault();
        
        if (!form.checkValidity()) {
            event.stopPropagation();
            form.classList.add('was-validated');
            return;
        }
        
        // Show loading state
        submitBtn.disabled = true;
        spinner.classList.remove('d-none');
        
        try {
            // Simulate form submission (replace with actual API call)
            await new Promise(resolve => setTimeout(resolve, 1500));
            
            // Show success message
            const successAlert = document.createElement('div');
            successAlert.className = 'alert alert-success mt-3';
            successAlert.innerHTML = '<i class="fas fa-check-circle me-2"></i>Your message has been sent successfully!';
            form.insertAdjacentElement('afterend', successAlert);
            
            // Reset form
            form.reset();
            form.classList.remove('was-validated');
            inputs.forEach(input => {
                input.classList.remove('is-valid', 'is-invalid');
            });
            
            // Remove success message after 5 seconds
            setTimeout(() => successAlert.remove(), 5000);
        } catch (error) {
            // Show error message
            const errorAlert = document.createElement('div');
            errorAlert.className = 'alert alert-danger mt-3';
            errorAlert.innerHTML = '<i class="fas fa-exclamation-circle me-2"></i>An error occurred. Please try again.';
            form.insertAdjacentElement('afterend', errorAlert);
            
            // Remove error message after 5 seconds
            setTimeout(() => errorAlert.remove(), 5000);
        } finally {
            // Reset button state
            submitBtn.disabled = false;
            spinner.classList.add('d-none');
        }
    });
});
</script>

<?php include '../includes/layouts/footer.php'; ?> 