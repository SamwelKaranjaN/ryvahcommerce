<?php
require_once __DIR__ . '../../config/settings.php';
require_once __DIR__ . '../../config/database.php';

// Check if user is already logged in
if (isLoggedIn()) {
    redirect('index');
}

// Include header
require_once __DIR__ . '../../includes/layouts/header.php';
?>

<!-- Registration Hero Section -->
<section class="login-hero position-relative overflow-hidden">
    <div class="hero-overlay"></div>
    <div class="container position-relative text-center" style="z-index: 2;">
        <h1 class="display-4 fw-bold animate__animated animate__fadeInDown">Create Account</h1>
        <p class="lead animate__animated animate__fadeInUp animate__delay-1s">Join us to start shopping</p>
    </div>
</section>

<!-- Registration Form Section -->
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card border-0 shadow-lg">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <i class="fas fa-user-plus fa-3x text-primary mb-3"></i>
                            <h3 class="fw-bold">To Purchase Book Please Register</h3>
                            <p class="text-muted">Fill in your details to create an account</p>
                        </div>

                        <div class="success-message" id="success-message" style="display: none;">
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i> Registration successful! Redirecting...
                            </div>
                        </div>

                        <form id="registerForm" novalidate>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-floating mb-4">
                                        <input type="text" class="form-control" id="full_name" name="full_name" placeholder="Full Name" required>
                                        <label for="full_name"><i class="fas fa-user me-2"></i>Full Name</label>
                                        <div class="invalid-feedback" id="full_name-error"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating mb-4">
                                        <input type="email" class="form-control" id="email" name="email" placeholder="Email" required>
                                        <label for="email"><i class="fas fa-envelope me-2"></i>Email</label>
                                        <div class="invalid-feedback" id="email-error"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-floating mb-4">
                                        <input type="tel" class="form-control" id="phone" name="phone" placeholder="Phone Number" maxlength="15" pattern="[0-9]{1,15}" required>
                                        <label for="phone"><i class="fas fa-phone me-2"></i>Phone Number</label>
                                        <div class="invalid-feedback" id="phone-error"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating mb-4">
                                        <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                                        <label for="password"><i class="fas fa-lock me-2"></i>Password</label>
                                        <span class="password-toggle position-absolute end-0 top-50 translate-middle-y me-3" onclick="togglePassword('password')">
                                            <i class="far fa-eye"></i>
                                        </span>
                                        <div class="invalid-feedback" id="password-error"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-floating mb-4">
                                <textarea class="form-control" id="address" name="address" placeholder="Shipping Address" style="height: 100px" required></textarea>
                                <label for="address"><i class="fas fa-map-marker-alt me-2"></i>Shipping Address</label>
                                <div class="invalid-feedback" id="address-error"></div>
                            </div>

                            <div class="form-floating mb-4">
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>
                                <label for="confirm_password"><i class="fas fa-lock me-2"></i>Confirm Password</label>
                                <span class="password-toggle position-absolute end-0 top-50 translate-middle-y me-3" onclick="togglePassword('confirm_password')">
                                    <i class="far fa-eye"></i>
                                </span>
                                <div class="invalid-feedback" id="confirm_password-error"></div>
                            </div>

                            <input type="hidden" id="role" name="role" value="Client">

                            <div class="alert alert-danger" id="form-error" style="display: none;"></div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg" id="submit-btn">
                                    <span id="btn-text">Create Account</span>
                                    <span class="spinner-border spinner-border-sm d-none" id="spinner" role="status" aria-hidden="true"></span>
                                </button>
                                <a href="login" class="btn btn-outline-primary btn-lg">
                                    <i class="fas fa-sign-in-alt me-2"></i>Already have an account? Sign In
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
:root {
    --primary-bg: #e0e7ff;
    --secondary-bg: #c4b5fd;
    --container-bg: #ffffff;
    --text-primary: #1f2937;
    --text-secondary: #4b5563;
    --error-color: #dc2626;
    --border-color: #e5e7eb;
    --input-bg: #f9fafb;
    --accent-primary: #8b5cf6;
    --accent-secondary: #a78bfa;
    --shadow-color: rgba(0, 0, 0, 0.15);
    --accent-shadow: rgba(139, 92, 246, 0.1);
    --accent-hover-shadow: rgba(139, 92, 246, 0.4);
}

.login-hero {
    background: linear-gradient(135deg, var(--primary-bg), var(--secondary-bg));
    padding: 100px 0 50px;
    margin-top: -76px;
    position: relative;
}

.hero-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.1);
}

.login-hero h1 {
    color: var(--text-primary);
    margin-bottom: 1rem;
}

.login-hero p {
    color: var(--text-secondary);
}

.card {
    border-radius: 20px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1) !important;
}

.form-floating > .form-control {
    padding-left: 1rem;
    height: calc(3.5rem + 2px);
    line-height: 1.25;
    border: 2px solid var(--border-color);
    border-radius: 10px;
    transition: all 0.3s ease;
    background-color: var(--input-bg);
}

.form-floating > textarea.form-control {
    height: auto;
    min-height: 100px;
}

.form-floating > .form-control:focus {
    border-color: var(--accent-primary);
    box-shadow: 0 0 0 0.25rem var(--accent-shadow);
    background-color: white;
}

.form-floating > label {
    padding: 1rem;
    color: var(--text-secondary);
}

.btn-primary {
    background: linear-gradient(45deg, var(--accent-primary), var(--accent-secondary));
    border: none;
    padding: 1rem 1.5rem;
    font-weight: 600;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
    border-radius: 10px;
    color: white !important;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    box-shadow: 0 4px 15px var(--accent-shadow);
}

.btn-primary:hover {
    background: linear-gradient(45deg, var(--accent-secondary), var(--accent-primary));
    transform: translateY(-2px);
    box-shadow: 0 6px 20px var(--accent-hover-shadow);
    color: white !important;
}

.btn-primary:active {
    transform: translateY(0);
    box-shadow: 0 4px 15px var(--accent-shadow);
}

.btn-outline-primary {
    border: 2px solid var(--accent-primary);
    color: var(--accent-primary);
    padding: 1rem 1.5rem;
    font-weight: 600;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
    border-radius: 10px;
    background: white;
}

.btn-outline-primary:hover {
    background: var(--accent-primary);
    color: white !important;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px var(--accent-shadow);
    border-color: var(--accent-primary);
}

.btn-outline-primary:active {
    transform: translateY(0);
    box-shadow: 0 4px 15px var(--accent-shadow);
}

.d-grid {
    gap: 1rem !important;
}

.password-toggle {
    cursor: pointer;
    color: var(--text-secondary);
    transition: color 0.3s ease;
    z-index: 10;
}

.password-toggle:hover {
    color: var(--accent-primary);
}

.alert {
    border-radius: 10px;
    border: none;
    padding: 1rem;
}

.alert-danger {
    background-color: #fee2e2;
    color: #dc2626;
}

.alert-success {
    background-color: #dcfce7;
    color: #16a34a;
}

@media (max-width: 768px) {
    .login-hero {
        padding: 80px 0 40px;
    }
    
    .card-body {
        padding: 2rem !important;
    }
    
    .btn {
        padding: 0.8rem 1.2rem;
    }
}

@media (max-width: 480px) {
    .login-hero {
        padding: 60px 0 30px;
    }
    
    .card-body {
        padding: 1.5rem !important;
    }
}
</style>

<script>
function togglePassword(inputId) {
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

document.getElementById('registerForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    if (!validateForm()) return;

    const submitBtn = document.getElementById('submit-btn');
    const btnText = document.getElementById('btn-text');
    const spinner = document.getElementById('spinner');

    submitBtn.disabled = true;
    btnText.textContent = 'Creating Account...';
    spinner.classList.remove('d-none');

    try {
        const formData = new FormData();
        formData.append('full_name', document.getElementById('full_name').value);
        formData.append('email', document.getElementById('email').value);
        formData.append('phone', document.getElementById('phone').value);
        formData.append('address', document.getElementById('address').value);
        formData.append('password', document.getElementById('password').value);
        formData.append('confirm_password', document.getElementById('confirm_password').value);
        formData.append('role', document.getElementById('role').value);

        const response = await fetch('../php/register/register.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            document.getElementById('success-message').style.display = 'block';
            document.getElementById('registerForm').style.display = 'none';
            setTimeout(() => {
                window.location.href = 'index';
            }, 2000);
        } else {
            const formError = document.getElementById('form-error');
            formError.textContent = result.error || 'An error occurred. Please try again.';
            formError.style.display = 'block';
        }
    } catch (error) {
        console.error('Registration error:', error);
        const formError = document.getElementById('form-error');
        formError.textContent = 'An error occurred. Please try again.';
        formError.style.display = 'block';
    } finally {
        submitBtn.disabled = false;
        btnText.textContent = 'Create Account';
        spinner.classList.add('d-none');
    }
});
</script>

<?php
// Include footer
require_once __DIR__ . '../../includes/layouts/footer.php';
?>