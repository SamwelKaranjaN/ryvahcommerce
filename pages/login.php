<?php
require_once '../includes/bootstrap.php';

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']) ? true : false;

    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        try {
            // Validate user credentials
            $stmt = $conn->prepare("SELECT id, password, email, full_name FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                if (password_verify($password, $user['password'])) {
                    // Set session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_name'] = $user['full_name'];

                    // ============================================================================
                    // FIRST PRIORITY: Transfer session cart items to user's database cart
                    // This must happen IMMEDIATELY after successful authentication
                    // ============================================================================
                    require_once '../includes/cart.php';
                    $hasSessionCart = !empty($_SESSION['cart']);
                    $hasCheckoutCart = !empty($_SESSION['checkout_cart']);

                    // Transfer ALL session cart items to database FIRST
                    $transferResult = transferSessionCartToDatabase($user['id']);

                    // Log the cart transfer for debugging/monitoring
                    if ($transferResult['transferred'] > 0) {
                        $message = "CART TRANSFER: Successfully transferred {$transferResult['transferred']} items from session to database cart";
                        if ($transferResult['merged'] > 0) {
                            $message .= " (merged {$transferResult['merged']} existing items)";
                        }
                        error_log($message);

                        // Set user-friendly message for cart page
                        if ($transferResult['merged'] > 0) {
                            $_SESSION['cart_merged'] = "Welcome back! Your cart items have been merged with your previous items.";
                        } else {
                            $_SESSION['cart_merged'] = "Welcome back! Your cart items have been restored.";
                        }
                    }

                    // Log any transfer errors
                    if (!$transferResult['success'] && !empty($transferResult['errors'])) {
                        error_log("CART TRANSFER ERRORS: " . implode(', ', $transferResult['errors']));
                    }

                    // Set remember me cookie if requested (after cart transfer)
                    if ($remember) {
                        $token = bin2hex(random_bytes(32));
                        $expires = time() + (30 * 24 * 60 * 60); // 30 days

                        // Store token in database
                        $stmt = $conn->prepare("INSERT INTO remember_tokens (user_id, token, expires) VALUES (?, ?, ?)");
                        $stmt->bind_param("iss", $user['id'], $token, date('Y-m-d H:i:s', $expires));
                        $stmt->execute();

                        // Set cookie
                        setcookie('remember_token', $token, $expires, '/', '', true, true);
                    }

                    // Clean up any orphaned cart items
                    verifyAndCleanCart($user['id']);

                    // Determine redirect URL
                    $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'index';

                    // Check for redirect_after_login in session (set by preserveCartForCheckout)
                    if (isset($_SESSION['redirect_after_login'])) {
                        $redirect = $_SESSION['redirect_after_login'];
                        unset($_SESSION['redirect_after_login']);
                    }

                    // Special handling for checkout flow
                    if ($redirect === 'checkout' && ($hasSessionCart || $hasCheckoutCart || $transferResult['transferred'] > 0)) {
                        // User was redirected from checkout and had items in cart
                        // Redirect to checkout to continue the process
                        $redirect = '../checkout/simple_checkout';
                        error_log("CHECKOUT REDIRECT: Redirecting to checkout after cart transfer");
                    } else if (($hasSessionCart || $transferResult['transferred'] > 0) && $redirect !== 'checkout') {
                        // Regular login with cart items - go to cart page
                        $redirect = 'cart';
                        error_log("CART REDIRECT: Redirecting to cart page after login");
                    }

                    // Handle any remaining temp cart items (legacy support)
                    if (isset($_SESSION['temp_cart'])) {
                        foreach ($_SESSION['temp_cart'] as $item) {
                            if (is_array($item) && isset($item['id'], $item['quantity'])) {
                                addToCart($item['id'], $item['quantity']);
                            }
                        }
                        unset($_SESSION['temp_cart']);
                    }

                    // Ensure the redirect URL is valid
                    if (!preg_match('/^[a-zA-Z0-9_\-\.\/]+$/', $redirect)) {
                        $redirect = 'index';
                    }

                    header("Location: $redirect");
                    exit;
                } else {
                    $error = 'Invalid email or password.';
                }
            } else {
                $error = 'Invalid email or password.';
            }
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            $error = 'An error occurred. Please try again later.';
        }
    }
}

include '../includes/layouts/header.php';
?>

<div id="error" class="alert alert-danger alert-dismissible fade show" style="display: none;" role="alert">
    <div class="d-flex align-items-center">
        <i class="fas fa-exclamation-circle me-2"></i>
        <div id="error-message"></div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>

<div id="success" class="alert alert-success alert-dismissible fade show" style="display: none;" role="alert">
    <div class="d-flex align-items-center">
        <i class="fas fa-check-circle me-2"></i>
        <div id="success-message"></div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>

<div class="login-container">
    <div class="login-wrapper">
        <!-- Left side - Image/Illustration -->
        <div class="login-left">
            <div class="login-image">
                <div class="overlay"></div>
                <div class="content">
                    <h2>Welcome Back!</h2>
                    <p>Discover amazing books and artworks at Ryvah Books</p>
                </div>
            </div>
        </div>

        <!-- Right side - Login Form -->
        <div class="login-right">
            <div class="login-form-container">
                <div class="login-header">
                    <h1>Login</h1>
                    <p>Enter your credentials to access your account</p>
                </div>

                <form id="loginForm" class="login-form">
                    <div class="form-floating mb-3">
                        <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com"
                            required>
                        <label for="email">Email address</label>
                    </div>

                    <div class="form-floating mb-3 password-container">
                        <input type="password" class="form-control" id="password" name="password" placeholder="Password"
                            required>
                        <label for="password">Password</label>
                        <button type="button" class="password-toggle" onclick="togglePassword()">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember">Remember me</label>
                        </div>
                        <a href="forgot-password.php" class="forgot-password">Forgot Password?</a>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100 login-btn">
                        <span class="btn-text">Login</span>
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    </button>
                </form>

                <div class="register-link">
                    <p>Don't have an account? <a href="register">Register here</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.login-container {
    min-height: 100vh;
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
}

.login-wrapper {
    display: flex;
    width: 700px;
    max-width: 100%;
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
    min-height: 0;
}

.login-left {
    flex: 1;
    position: relative;
    display: none;
}

.login-image {
    height: 100%;
    background: linear-gradient(45deg, #2c3e50, #3498db);
    position: relative;
    overflow: hidden;
}

.login-image::before {
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

.login-right {
    flex: 1;
    padding: 1.5rem 1rem;
    min-width: 0;
}

.login-form-container {
    max-width: 350px;
    margin: 0 auto;
}

.login-header {
    text-align: center;
    margin-bottom: 1.2rem;
}

.login-header h1 {
    font-size: 1.5rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 0.3rem;
}

.login-header p {
    color: #6c757d;
    font-size: 1rem;
}

.form-floating {
    position: relative;
}

.form-floating>.form-control {
    padding: 0.5rem 0.75rem;
    height: calc(2.2rem + 2px);
    line-height: 1.1;
    font-size: 0.98rem;
}

.form-floating>label {
    padding: 0.5rem 0.75rem;
    font-size: 0.98rem;
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

.login-btn {
    padding: 0.6rem;
    font-size: 1rem;
}

.login-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 123, 255, 0.3);
}

.forgot-password {
    color: #6c757d;
    text-decoration: none;
    font-size: 0.9rem;
}

.forgot-password:hover {
    color: #007bff;
    text-decoration: underline;
}

.register-link {
    text-align: center;
    margin-top: 1rem;
    font-size: 0.98rem;
}

.register-link a {
    color: #007bff;
    text-decoration: none;
    font-weight: 600;
}

.register-link a:hover {
    text-decoration: underline;
}

@media (min-width: 992px) {
    .login-left {
        display: block;
    }
}

@media (max-width: 991px) {
    .login-wrapper {
        width: 100%;
        max-width: 400px;
    }

    .login-right {
        padding: 1rem 0.5rem;
    }

    .login-header h1 {
        font-size: 1.2rem;
    }
}

@media (max-width: 600px) {
    .login-wrapper {
        flex-direction: column;
        width: 100%;
        max-width: 100%;
        border-radius: 0;
    }

    .login-right {
        padding: 0.5rem 0.2rem;
    }

    .login-form-container {
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
    const form = document.getElementById('loginForm');
    const loginBtn = document.querySelector('.login-btn');
    const btnText = loginBtn.querySelector('.btn-text');
    const spinner = loginBtn.querySelector('.spinner-border');
    const errorDiv = document.getElementById('error');
    const successDiv = document.getElementById('success');
    const errorMessage = document.getElementById('error-message');
    const successMessage = document.getElementById('success-message');

    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        // Show loading state
        btnText.classList.add('d-none');
        spinner.classList.remove('d-none');
        loginBtn.disabled = true;
        errorDiv.style.display = 'none';
        successDiv.style.display = 'none';

        // Get redirect parameter from URL
        const urlParams = new URLSearchParams(window.location.search);
        const redirectParam = urlParams.get('redirect');

        const formData = {
            email: document.getElementById('email').value,
            password: document.getElementById('password').value,
            remember: document.getElementById('remember').checked,
            redirect: redirectParam || 'index'
        };

        try {
            const response = await fetch('../php/login/login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            });
            const data = await response.json();

            if (response.ok) {
                successMessage.textContent = 'Login successful! Redirecting...';
                successDiv.style.display = 'block';

                // Store any success message in session storage
                if (data.message) {
                    sessionStorage.setItem('successMessage', data.message);
                }

                // Log debug info to console
                if (data.debug) {
                    console.log('Cart Transfer Debug Info:', data.debug);
                }

                // Redirect after a short delay
                setTimeout(() => {
                    window.location.href = data.redirect || 'index';
                }, 1000);
            } else {
                errorMessage.textContent = data.error || 'Invalid email or password';
                errorDiv.style.display = 'block';
            }
        } catch (err) {
            errorMessage.textContent = 'Failed to connect to the server';
            errorDiv.style.display = 'block';
        } finally {
            // Reset button state
            btnText.classList.remove('d-none');
            spinner.classList.add('d-none');
            loginBtn.disabled = false;
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

    // Check for success message in session storage
    const successMsg = sessionStorage.getItem('successMessage');
    if (successMsg) {
        successMessage.textContent = successMsg;
        successDiv.style.display = 'block';
        sessionStorage.removeItem('successMessage');
    }
});
</script>

<?php include '../includes/layouts/footer.php'; ?>