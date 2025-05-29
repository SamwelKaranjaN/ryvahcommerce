<?php
header('Content-Type: application/json');
require_once '../db_connect.php';

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
            // Start session if not already started
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

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
                    'secure' => true,
                    'httponly' => true,
                    'samesite' => 'Strict'
                ]);
            }

            // Transfer session cart to database if exists
            if (isset($_SESSION['cart'])) {
                require_once '../../includes/cart.php';
                transferSessionCartToDatabase($user['id']);
            }

            // Get redirect URL from session or default to index
            $redirect = isset($_SESSION['redirect_after_login']) ? $_SESSION['redirect_after_login'] : 'index.php';
            unset($_SESSION['redirect_after_login']);

            // Ensure the redirect URL is valid
            if (!preg_match('/^[a-zA-Z0-9_\-\.\/]+$/', $redirect)) {
                $redirect = 'index.php';
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