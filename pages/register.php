<?php
require_once '../includes/bootstrap.php';

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Include header
include '../includes/layouts/header.php';
?>

<div class="register-container">
    <div class="register-wrapper">
        <!-- Left side - Image/Illustration -->
        <div class="register-left">
            <div class="register-image">
                <div class="overlay"></div>
                <div class="content">
                    <h2>Join Our Community!</h2>
                    <p>Create an account to start your journey with Ryvah Books</p>
                </div>
            </div>
        </div>

        <!-- Right side - Registration Form -->
        <div class="register-right">
            <div class="register-form-container">
                <div class="register-header">
                    <h1>Create Account</h1>
                    <p>Fill in your details to get started</p>
                </div>

                <div class="success-message" id="success-message" style="display: none;">
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> Registration successful! Redirecting...
                    </div>
                </div>

                <form id="registerForm" novalidate>
                    <div class="form-floating mb-4">
                        <input type="text" class="form-control" id="full_name" name="full_name" placeholder="Full Name"
                            required>
                        <label for="full_name"><i class="fas fa-user me-2"></i>Full Name</label>
                        <div class="invalid-feedback" id="full_name-error"></div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-floating mb-4">
                                <input type="email" class="form-control" id="email" name="email" placeholder="Email"
                                    required>
                                <label for="email"><i class="fas fa-envelope me-2"></i>Email</label>
                                <div class="invalid-feedback" id="email-error"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating mb-4">
                                <input type="tel" class="form-control" id="phone" name="phone"
                                    placeholder="Phone Number" maxlength="15" pattern="[0-9]{1,15}" required>
                                <label for="phone"><i class="fas fa-phone me-2"></i>Phone Number</label>
                                <div class="invalid-feedback" id="phone-error"></div>
                            </div>
                        </div>
                    </div>

                    <div class="form-floating mb-4">
                        <textarea class="form-control" id="address" name="address" placeholder="Shipping Address"
                            style="height: 100px" required></textarea>
                        <label for="address"><i class="fas fa-map-marker-alt me-2"></i>Shipping Address</label>
                        <div class="invalid-feedback" id="address-error"></div>
                    </div>

                    <div class="form-floating mb-4">
                        <input type="password" class="form-control" id="password" name="password" placeholder="Password"
                            required>
                        <label for="password"><i class="fas fa-lock me-2"></i>Password</label>
                        <span class="password-toggle position-absolute end-0 top-50 translate-middle-y me-3"
                            onclick="togglePassword('password')">
                            <i class="far fa-eye"></i>
                        </span>
                        <div class="invalid-feedback" id="password-error"></div>
                    </div>

                    <div class="form-floating mb-4">
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                            placeholder="Confirm Password" required>
                        <label for="confirm_password"><i class="fas fa-lock me-2"></i>Confirm Password</label>
                        <span class="password-toggle position-absolute end-0 top-50 translate-middle-y me-3"
                            onclick="togglePassword('confirm_password')">
                            <i class="far fa-eye"></i>
                        </span>
                        <div class="invalid-feedback" id="confirm_password-error"></div>
                    </div>

                    <input type="hidden" id="role" name="role" value="Client">

                    <div class="alert alert-danger" id="form-error" style="display: none;"></div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg" id="submit-btn">
                            <span id="btn-text">Create Account</span>
                            <span class="spinner-border spinner-border-sm d-none" id="spinner" role="status"
                                aria-hidden="true"></span>
                        </button>
                        <a href="login.php" class="btn btn-outline-primary btn-lg">
                            <i class="fas fa-sign-in-alt me-2"></i>Already have an account? Sign In
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.register-container {
    min-height: 100vh;
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
}

.register-wrapper {
    display: flex;
    width: 1000px;
    max-width: 100%;
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
}

.register-left {
    flex: 1;
    position: relative;
    display: none;
}

.register-image {
    height: 100%;
    background: linear-gradient(45deg, #2c3e50, #3498db);
    position: relative;
    overflow: hidden;
}

.register-image::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('https://images.unsplash.com/photo-1507842217343-583bb7270b66?ixlib=rb-4.0.3') center/cover;
    opacity: 0.2;
}

.overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, rgba(44, 62, 80, 0.9), rgba(52, 152, 219, 0.9));
}

.content {
    position: relative;
    z-index: 1;
    color: white;
    padding: 3rem;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.content h2 {
    font-size: 2.5rem;
    margin-bottom: 1rem;
    font-weight: 700;
}

.content p {
    font-size: 1.1rem;
    opacity: 0.9;
}

.register-right {
    flex: 1;
    padding: 3rem;
}

.register-form-container {
    max-width: 600px;
    margin: 0 auto;
}

.register-header {
    text-align: center;
    margin-bottom: 2rem;
}

.register-header h1 {
    font-size: 2rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.register-header p {
    color: #6c757d;
}

.form-floating {
    position: relative;
}

.form-floating>.form-control {
    padding: 1rem 0.75rem;
    height: calc(3.5rem + 2px);
    line-height: 1.25;
    border: 2px solid #e5e7eb;
    border-radius: 10px;
    transition: all 0.3s ease;
}

.form-floating>textarea.form-control {
    height: auto;
    min-height: 100px;
}

.form-floating>.form-control:focus {
    border-color: #3498db;
    box-shadow: 0 0 0 0.25rem rgba(52, 152, 219, 0.25);
}

.form-floating>label {
    padding: 1rem 0.75rem;
    color: #6c757d;
}

.password-toggle {
    cursor: pointer;
    color: #6c757d;
    transition: color 0.3s ease;
    z-index: 10;
}

.password-toggle:hover {
    color: #3498db;
}

.btn-primary {
    background: linear-gradient(45deg, #3498db, #2980b9);
    border: none;
    padding: 1rem 1.5rem;
    font-weight: 600;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
    border-radius: 10px;
    color: white !important;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    box-shadow: 0 4px 15px rgba(52, 152, 219, 0.2);
}

.btn-primary:hover {
    background: linear-gradient(45deg, #2980b9, #3498db);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(52, 152, 219, 0.3);
}

.btn-outline-primary {
    border: 2px solid #3498db;
    color: #3498db;
    padding: 1rem 1.5rem;
    font-weight: 600;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
    border-radius: 10px;
    background: white;
}

.btn-outline-primary:hover {
    background: #3498db;
    color: white !important;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(52, 152, 219, 0.2);
}

.alert {
    border: none;
    border-radius: 10px;
    padding: 1rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.alert-danger {
    background-color: #fff5f5;
    color: #dc3545;
}

.alert-success {
    background-color: #f0fff4;
    color: #28a745;
}

.alert i {
    font-size: 1.2rem;
}

@media (min-width: 992px) {
    .register-left {
        display: block;
    }
}

@media (max-width: 991px) {
    .register-wrapper {
        width: 100%;
        max-width: 600px;
    }

    .register-right {
        padding: 2rem;
    }
}

/* Animation classes */
.fade-in {
    animation: fadeIn 0.5s ease-in;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Password visibility toggle
    window.togglePassword = function(inputId) {
        const input = document.getElementById(inputId);
        const icon = input.nextElementSibling.querySelector('i');

        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    // Form validation
    function validateForm() {
        let isValid = true;
        const fullName = document.getElementById('full_name').value.trim();
        const email = document.getElementById('email').value.trim();
        const phone = document.getElementById('phone').value.trim();
        const address = document.getElementById('address').value.trim();
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;

        document.querySelectorAll('.invalid-feedback').forEach(el => {
            el.style.display = 'none';
        });

        if (!fullName) {
            document.getElementById('full_name-error').textContent = 'Name is required';
            document.getElementById('full_name-error').style.display = 'block';
            isValid = false;
        }

        if (!email) {
            document.getElementById('email-error').textContent = 'Email is required';
            document.getElementById('email-error').style.display = 'block';
            isValid = false;
        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            document.getElementById('email-error').textContent = 'Please enter a valid email';
            document.getElementById('email-error').style.display = 'block';
            isValid = false;
        }

        if (!phone) {
            document.getElementById('phone-error').textContent = 'Phone number is required';
            document.getElementById('phone-error').style.display = 'block';
            isValid = false;
        } else if (!/^\d{1,15}$/.test(phone)) {
            document.getElementById('phone-error').textContent = 'Please enter a valid phone number';
            document.getElementById('phone-error').style.display = 'block';
            isValid = false;
        }

        if (!address) {
            document.getElementById('address-error').textContent = 'Shipping address is required';
            document.getElementById('address-error').style.display = 'block';
            isValid = false;
        }

        if (!password) {
            document.getElementById('password-error').textContent = 'Password is required';
            document.getElementById('password-error').style.display = 'block';
            isValid = false;
        } else if (password.length < 8) {
            document.getElementById('password-error').textContent = 'Password must be at least 8 characters';
            document.getElementById('password-error').style.display = 'block';
            isValid = false;
        }

        if (!confirmPassword) {
            document.getElementById('confirm_password-error').textContent = 'Please confirm your password';
            document.getElementById('confirm_password-error').style.display = 'block';
            isValid = false;
        } else if (password !== confirmPassword) {
            document.getElementById('confirm_password-error').textContent = 'Passwords do not match';
            document.getElementById('confirm_password-error').style.display = 'block';
            isValid = false;
        }

        return isValid;
    }

    // Form submission
    const form = document.getElementById('registerForm');
    const submitBtn = document.getElementById('submit-btn');
    const btnText = document.getElementById('btn-text');
    const spinner = document.getElementById('spinner');

    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        if (!validateForm()) return;

        submitBtn.disabled = true;
        btnText.textContent = 'Creating Account...';
        spinner.classList.remove('d-none');

        try {
            const formData = new FormData();
            formData.append('full_name', document.getElementById('full_name').value.trim());
            formData.append('email', document.getElementById('email').value.trim());
            formData.append('phone', document.getElementById('phone').value.trim());
            formData.append('address', document.getElementById('address').value.trim());
            formData.append('password', document.getElementById('password').value);
            formData.append('confirm_password', document.getElementById('confirm_password').value);

            const response = await fetch('../php/register/register.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                document.getElementById('success-message').style.display = 'block';
                document.getElementById('registerForm').style.display = 'none';

                // Redirect to login page after 2 seconds with redirect parameter if available
                setTimeout(() => {
                    const redirect = new URLSearchParams(window.location.search).get(
                        'redirect');
                    window.location.href = 'login.php' + (redirect ? '?redirect=' +
                        encodeURIComponent(redirect) : '');
                }, 2000);
            } else {
                const formError = document.getElementById('form-error');
                formError.textContent = result.error || 'An error occurred. Please try again.';
                formError.style.display = 'block';
                submitBtn.disabled = false;
                btnText.textContent = 'Create Account';
                spinner.classList.add('d-none');
            }
        } catch (error) {
            console.error('Registration error:', error);
            const formError = document.getElementById('form-error');
            formError.textContent =
                'An error occurred while processing your request. Please try again.';
            formError.style.display = 'block';
            submitBtn.disabled = false;
            btnText.textContent = 'Create Account';
            spinner.classList.add('d-none');
        }
    });

    // Add animation classes
    document.querySelectorAll('.form-floating').forEach((element, index) => {
        element.style.animationDelay = `${index * 0.1}s`;
        element.classList.add('fade-in');
    });
});
</script>

<?php include '../includes/layouts/footer.php'; ?>