<?php
require_once '../includes/bootstrap.php';

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index');
    exit;
}

$error = '';
$success = '';

include '../includes/layouts/header.php';
?>

<div id="error" class="alert alert-danger alert-dismissible fade show" style="display: none;" role="alert">
    <div class="d-flex align-items-center">
        <i class="fas fa-exclamation-circle me-2"></i>
        <div id="error-message"></div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>

<div id="redirect" class="alert alert-success alert-dismissible fade show" style="display: none;" role="alert">
    <div class="d-flex align-items-center">
        <i class="fas fa-check-circle me-2"></i>
        <div id="redirect-message"></div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>

<div class="register-container">
    <div class="register-wrapper">
        <!-- Left side - Image/Illustration -->
        <div class="register-left">
            <div class="register-image">
                <div class="overlay"></div>
                <div class="content">
                    <h2>Join Our Community</h2>
                    <p>Create an account to explore our collection of books and artworks</p>
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

                <form id="registrationForm" class="register-form">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="full_name" name="full_name"
                            placeholder="John Doe" required>
                        <label for="full_name">Full Name</label>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="email" class="form-control" id="email" name="email"
                            placeholder="info@ryvahcommerce.com" required>
                        <label for="email">Email address</label>
                    </div>

                    <div class="form-floating mb-3 password-container stylish-password">
                        <input type="password" class="form-control" id="password" name="password"
                            placeholder="Password" required>
                        <label for="password">Password</label>
                        <button type="button" class="password-toggle" onclick="togglePassword()" tabindex="-1">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>

                    <div class="form-row mb-3">
                        <div class="form-floating flex-grow-1 me-2">
                            <input type="tel" class="form-control" id="phone" name="phone"
                                placeholder="Phone Number">
                            <label for="phone">Phone Number</label>
                        </div>
                        <div class="form-floating flex-grow-1 me-2">
                            <input type="text" class="form-control" id="city" name="city" placeholder="City">
                            <label for="city">City</label>
                        </div>
                        <div class="form-floating flex-grow-1">
                            <input type="text" class="form-control" id="state" name="state" placeholder="State">
                            <label for="state">State</label>
                        </div>
                    </div>

                    <div class="form-row mb-4">
                        <div class="form-floating flex-grow-2 me-2">
                            <input type="text" class="form-control" id="address" name="address"
                                placeholder="Address">
                            <label for="address">Address</label>
                        </div>
                        <div class="form-floating flex-grow-1">
                            <input type="text" class="form-control" id="postal_code" name="postal_code"
                                placeholder="Postal Code">
                            <label for="postal_code">Postal Code</label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100 register-btn">
                        <span class="btn-text">Create Account</span>
                        <span class="spinner-border spinner-border-sm d-none" role="status"
                            aria-hidden="true"></span>
                    </button>
                </form>

                <div class="login-link">
                    <p>Already have an account? <a href="login">Login here</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .register-container {
        min-height: 350px;
        background: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0.5rem;
    }

    .register-wrapper {
        display: flex;
        width: 900px;
        max-width: 100%;
        background: #fff;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 8px 32px rgba(44, 62, 80, 0.10);
        min-height: 0;
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
        padding: 1.5rem 1.5rem;
        min-width: 0;
        background: #f9fafd;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .register-form-container {
        max-width: 520px;
        margin: 0 auto;
    }

    .form-row {
        display: flex;
        gap: 1rem;
    }

    .form-row .form-floating {
        flex: 1 1 0;
        margin-bottom: 0 !important;
    }

    .form-row .flex-grow-2 {
        flex: 2 1 0;
    }

    .form-floating>.form-control {
        padding: 0.7rem 0.75rem;
        height: 2.7rem;
        font-size: 1.05rem;
        border-radius: 10px;
        background: #f5f7fa;
        border: 1px solid #e0e4ea;
        box-shadow: none;
        transition: border-color 0.2s, background 0.2s;
    }

    .form-floating>.form-control:focus {
        border-color: #3498db;
        background: #fff;
    }

    .form-floating>label {
        padding: 0.7rem 0.75rem;
        font-size: 1.05rem;
        color: #6c757d;
    }

    .form-row+.form-row {
        margin-top: 0.5rem;
    }

    .register-header {
        text-align: center;
        margin-bottom: 2rem;
    }

    .register-header h1 {
        font-size: 2rem;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 0.2rem;
    }

    .register-header p {
        color: #6c757d;
        font-size: 1.1rem;
    }

    .register-btn {
        padding: 0.8rem;
        font-size: 1.1rem;
        font-weight: 600;
        border-radius: 10px;
        background: linear-gradient(90deg, #3498db 0%, #007bff 100%);
        border: none;
        color: #fff;
        box-shadow: 0 2px 8px rgba(52, 152, 219, 0.08);
        transition: background 0.2s, box-shadow 0.2s;
    }

    .register-btn:hover,
    .register-btn:focus {
        background: linear-gradient(90deg, #007bff 0%, #3498db 100%);
        box-shadow: 0 4px 16px rgba(52, 152, 219, 0.15);
    }

    .login-link {
        text-align: center;
        margin-top: 1.2rem;
        font-size: 1rem;
    }

    .login-link a {
        color: #007bff;
        text-decoration: none;
        font-weight: 600;
    }

    .login-link a:hover {
        text-decoration: underline;
    }

    @media (min-width: 992px) {
        .register-left {
            display: block;
        }
    }

    @media (max-width: 991px) {
        .register-wrapper {
            width: 100%;
            max-width: 100%;
        }

        .register-right {
            padding: 1.2rem 0.5rem;
        }

        .register-form-container {
            max-width: 100%;
        }

        .form-row {
            flex-direction: column;
            gap: 0.5rem;
        }

        .form-row .flex-grow-2 {
            flex: 1 1 0;
        }
    }

    @media (max-width: 600px) {
        .register-wrapper {
            flex-direction: column;
            width: 100%;
            max-width: 100%;
            border-radius: 0;
        }

        .register-right {
            padding: 0.5rem 0.2rem;
        }

        .register-form-container {
            max-width: 100%;
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

    .alert {
        position: fixed;
        top: 20px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 1050;
        min-width: 300px;
        max-width: 500px;
        border: none;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .stylish-password {
        position: relative;
    }

    .stylish-password .password-toggle {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #6c757d;
        cursor: pointer;
        z-index: 10;
        padding: 0 0.3rem;
        font-size: 1.2rem;
        height: 2.2rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .stylish-password .password-toggle:focus {
        outline: none;
        color: #3498db;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Password visibility toggle
        window.togglePassword = function() {
            const passwordInput = document.getElementById('password');
            const icon = document.querySelector('.password-toggle i');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Form submission handling
        const form = document.getElementById('registrationForm');
        const registerBtn = document.querySelector('.register-btn');
        const btnText = registerBtn.querySelector('.btn-text');
        const spinner = registerBtn.querySelector('.spinner-border');
        const errorDiv = document.getElementById('error');
        const redirectDiv = document.getElementById('redirect');
        const errorMessage = document.getElementById('error-message');
        const redirectMessage = document.getElementById('redirect-message');

        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            // Show loading state
            btnText.classList.add('d-none');
            spinner.classList.remove('d-none');
            registerBtn.disabled = true;
            errorDiv.style.display = 'none';
            redirectDiv.style.display = 'none';

            const formData = {
                full_name: document.getElementById('full_name').value,
                email: document.getElementById('email').value,
                password: document.getElementById('password').value,
                phone: document.getElementById('phone').value || null,
                address: document.getElementById('address').value || null,
                city: document.getElementById('city').value || null,
                state: document.getElementById('state').value || null,
                postal_code: document.getElementById('postal_code').value || null
            };

            try {
                const response = await fetch('../php/register/register.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });
                const data = await response.json();

                if (response.ok) {
                    redirectMessage.textContent =
                        'Registration successful! Redirecting to login page...';
                    redirectDiv.style.display = 'block';
                    sessionStorage.setItem('successMessage', data.message);
                    setTimeout(() => {
                        window.location.href = 'login';
                    }, 2000);
                } else {
                    errorMessage.textContent = data.error;
                    errorDiv.style.display = 'block';
                }
            } catch (err) {
                errorMessage.textContent = 'Failed to connect to the server';
                errorDiv.style.display = 'block';
            } finally {
                // Reset button state
                btnText.classList.remove('d-none');
                spinner.classList.add('d-none');
                registerBtn.disabled = false;
            }
        });

        // Add animation classes
        document.querySelectorAll('.form-floating').forEach((element, index) => {
            element.style.animationDelay = `${index * 0.1}s`;
            element.classList.add('fade-in');
        });

        // Auto-dismiss alerts after 5 seconds
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                const closeBtn = alert.querySelector('.btn-close');
                if (closeBtn) {
                    closeBtn.click();
                }
            }, 5000);
        });
    });
</script>

<?php include '../includes/layouts/footer.php'; ?>