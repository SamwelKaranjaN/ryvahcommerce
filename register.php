<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join Us</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
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
                        <h2>Join Our Community!</h2>
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
                                placeholder="name@example.com" required>
                            <label for="email">Email address</label>
                        </div>

                        <div class="form-floating mb-3 password-container">
                            <input type="password" class="form-control" id="password" name="password"
                                placeholder="Password" required>
                            <label for="password">Password</label>
                            <button type="button" class="password-toggle" onclick="togglePassword()">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="tel" class="form-control" id="phone" name="phone" placeholder="Phone Number">
                            <label for="phone">Phone Number</label>
                        </div>

                        <div class="form-floating mb-3">
                            <textarea class="form-control" id="address" name="address" placeholder="Address"
                                style="height: 100px"></textarea>
                            <label for="address">Address</label>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="city" name="city" placeholder="City">
                                    <label for="city">City</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="state" name="state" placeholder="State">
                                    <label for="state">State</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="postal_code" name="postal_code"
                                placeholder="Postal Code">
                            <label for="postal_code">Postal Code</label>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100 register-btn">
                            <span class="btn-text">Create Account</span>
                            <span class="spinner-border spinner-border-sm d-none" role="status"
                                aria-hidden="true"></span>
                        </button>
                    </form>

                    <div class="login-link">
                        <p>Already have an account? <a href="login.php">Login here</a></p>
                    </div>
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
        max-width: 500px;
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
    }

    .form-floating>label {
        padding: 1rem 0.75rem;
    }

    .password-container {
        position: relative;
    }

    .password-toggle {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #6c757d;
        cursor: pointer;
        z-index: 10;
    }

    .register-btn {
        position: relative;
        padding: 0.8rem;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .register-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 123, 255, 0.3);
    }

    .login-link {
        text-align: center;
        margin-top: 1.5rem;
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
            max-width: 500px;
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
    </style>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
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
                const response = await fetch('php/register/register.php', {
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
                        window.location.href = 'login.php';
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
</body>

</html>