<?php
header('Content-Type: application/json');
require_once '../../includes/bootstrap.php';

// Get JSON data from request
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Validate required fields
if (!isset($data['email']) || !isset($data['password'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Email and password are required']);
    exit;
}

$email = $data['email'];
$password = $data['password'];
$remember = isset($data['remember']) ? $data['remember'] : false;

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

            // Handle remember me functionality
            if ($remember) {
                $token = bin2hex(random_bytes(32));
                $expires = time() + (30 * 24 * 60 * 60); // 30 days

                // Store token in database
                $stmt = $conn->prepare("INSERT INTO remember_tokens (user_id, token, expires) VALUES (?, ?, ?)");
                $stmt->bind_param("iss", $user['id'], $token, date('Y-m-d H:i:s', $expires));
                $stmt->execute();

                // Set secure cookie
                setcookie('remember_token', $token, [
                    'expires' => $expires,
                    'path' => '/',
                    'secure' => false, // Set to true in production with HTTPS
                    'httponly' => true,
                    'samesite' => 'Strict'
                ]);
            }

            // Transfer session cart to database if exists
            require_once '../../includes/cart.php';
            $hasSessionCart = !empty($_SESSION['cart']);
            $transferResult = transferSessionCartToDatabase($user['id']);

            // Clean up any orphaned cart items
            verifyAndCleanCart($user['id']);

            // Log any transfer errors
            if (!$transferResult['success']) {
                error_log("Session cart transfer errors: " . implode(', ', $transferResult['errors']));
            } else if ($transferResult['transferred'] > 0) {
                error_log("Successfully transferred {$transferResult['transferred']} items from session cart to database");
            }

            // Handle temp cart merge
            if (isset($_SESSION['temp_cart'])) {
                foreach ($_SESSION['temp_cart'] as $item) {
                    addToCart($item['id'], $item['quantity']);
                }
                unset($_SESSION['temp_cart']);
            }

            // Determine redirect URL
            $redirect = 'index';

            // Check for redirect URL from request
            if (isset($data['redirect']) && !empty($data['redirect'])) {
                $redirect = $data['redirect'];
            }

            // Check for redirect_after_login in session
            if (isset($_SESSION['redirect_after_login'])) {
                $redirect = $_SESSION['redirect_after_login'];
                unset($_SESSION['redirect_after_login']);
            }

            // If there was a session cart, redirect to cart page
            if ($hasSessionCart) {
                $redirect = 'cart';
            }

            // Ensure the redirect URL is valid (alphanumeric, hyphens, underscores, dots, slashes)
            if (!preg_match('/^[a-zA-Z0-9_\-\.\/]+$/', $redirect)) {
                $redirect = 'index';
            }

            // Add .php extension if not present and not a directory-style URL
            if (!str_contains($redirect, '.') && !str_contains($redirect, '/')) {
                $redirect = $redirect . '.php';
            }

            // Return success response
            echo json_encode([
                'success' => true,
                'message' => 'Login successful',
                'redirect' => $redirect
            ]);
            exit;
        }
    }

    // If we get here, authentication failed
    http_response_code(401);
    echo json_encode(['error' => 'Invalid email or password']);
} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'An error occurred. Please try again later.']);
}
