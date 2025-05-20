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

                    // Set remember me cookie if requested
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

                    // Transfer session cart to database if exists
                    require_once '../includes/cart.php';
                    transferSessionCartToDatabase($user['id']);

                    // Check for redirect URL
                    $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php';

                    // If there's a temp cart, merge it with the database cart
                    if (isset($_SESSION['temp_cart'])) {
                        foreach ($_SESSION['temp_cart'] as $item) {
                            addToCart($item['id'], $item['quantity']);
                        }
                        unset($_SESSION['temp_cart']);
                    }

                    // Check for redirect_after_login in session
                    if (isset($_SESSION['redirect_after_login'])) {
                        $redirect = $_SESSION['redirect_after_login'];
                        unset($_SESSION['redirect_after_login']);
                    }

                    // Ensure the redirect URL is valid
                    if (!preg_match('/^[a-zA-Z0-9_\-\.\/]+$/', $redirect)) {
                        $redirect = 'index.php';
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

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <div><?php echo htmlspecialchars($error); ?></div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-check-circle me-2"></i>
                            <div><?php echo htmlspecialchars($success); ?></div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form method="POST"
                    action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . (isset($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : '')); ?>"
                    class="login-form">
                    <div class="form-floating mb-3">
                        <input type="email" class="form-control <?php echo $error ? 'is-invalid' : ''; ?>" id="email"
                            name="email" placeholder="name@example.com"
                            value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                        <label for="email">Email address</label>
                    </div>

                    <div class="form-floating mb-3 password-container">
                        <input type="password" class="form-control <?php echo $error ? 'is-invalid' : ''; ?>"
                            id="password" name="password" placeholder="Password" required>
                        <label for="password">Password</label>
                        <button type="button" class="password-toggle" onclick="togglePassword()">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember"
                                <?php echo isset($_POST['remember']) ? 'checked' : ''; ?>>
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
                    <p>Don't have an account? <a href="register.php">Register here</a></p>
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
        padding: 2rem;
    }

    .login-wrapper {
        display: flex;
        width: 1000px;
        max-width: 100%;
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
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
        padding: 3rem;
    }

    .login-form-container {
        max-width: 400px;
        margin: 0 auto;
    }

    .login-header {
        text-align: center;
        margin-bottom: 2rem;
    }

    .login-header h1 {
        font-size: 2rem;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 0.5rem;
    }

    .login-header p {
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

    .login-btn {
        position: relative;
        padding: 0.8rem;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .login-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 123, 255, 0.3);
    }

    .divider {
        text-align: center;
        margin: 1.5rem 0;
        position: relative;
    }

    .divider::before,
    .divider::after {
        content: '';
        position: absolute;
        top: 50%;
        width: calc(50% - 30px);
        height: 1px;
        background: #dee2e6;
    }

    .divider::before {
        left: 0;
    }

    .divider::after {
        right: 0;
    }

    .divider span {
        background: white;
        padding: 0 1rem;
        color: #6c757d;
        font-size: 0.9rem;
    }

    .social-login {
        display: flex;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .social-btn {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.8rem;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .social-btn:hover {
        background: #f8f9fa;
        transform: translateY(-2px);
    }

    .social-btn i {
        font-size: 1.2rem;
    }

    .register-link {
        text-align: center;
        margin-top: 1.5rem;
    }

    .register-link a {
        color: #007bff;
        text-decoration: none;
        font-weight: 600;
    }

    .register-link a:hover {
        text-decoration: underline;
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

    @media (min-width: 992px) {
        .login-left {
            display: block;
        }
    }

    @media (max-width: 991px) {
        .login-wrapper {
            width: 100%;
            max-width: 500px;
        }

        .login-right {
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

    /* Add these new styles */
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

    .form-control.is-invalid {
        border-color: #dc3545;
        background-image: none;
    }

    .form-control.is-invalid:focus {
        border-color: #dc3545;
        box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25);
    }

    /* Toast notification styles */
    .toast-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1050;
    }

    .toast {
        background: white;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        margin-bottom: 10px;
    }

    .toast-header {
        border-bottom: none;
        padding: 0.75rem 1rem;
    }

    .toast-body {
        padding: 0.75rem 1rem;
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
        F
        // Form submission handling
        const form = document.querySelector('.login-form');
        const loginBtn = document.querySelector('.login-btn');
        const btnText = loginBtn.querySelector('.btn-text');
        const spinner = loginBtn.querySelector('.spinner-border');

        form.addEventListener('submit', function(e) {
            // Show loading state
            btnText.classList.add('d-none');
            spinner.classList.remove('d-none');
            loginBtn.disabled = true;
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