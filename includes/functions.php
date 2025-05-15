<?php
// Load configuration
$config = require_once __DIR__ . '/../config/config.php';

// Function to sanitize input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to validate email
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Function to validate password strength
function validate_password($password) {
    // At least 8 characters, 1 uppercase, 1 lowercase, 1 number
    return strlen($password) >= 8 &&
           preg_match('/[A-Z]/', $password) &&
           preg_match('/[a-z]/', $password) &&
           preg_match('/[0-9]/', $password);
}

// Function to generate random token
function generate_token($length = 32) {
    return bin2hex(random_bytes($length));
}

// Function to encrypt data
function encrypt_data($data, $key, $iv) {
    return openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);
}

// Function to decrypt data
function decrypt_data($encrypted_data, $key, $iv) {
    return openssl_decrypt($encrypted_data, 'AES-256-CBC', $key, 0, $iv);
}

// Function to get the base URL
function getBaseUrl() {
    return $config['site']['url'];
}

// Function to get the site name
function getSiteName() {
    return $config['site']['name'];
}

// Function to get the site email
function getSiteEmail() {
    return $config['site']['email'];
}

// Function to sanitize output
function h($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to get current user ID
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

// Function to get user data
function getUserData($userId) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Function to format price
function formatPrice($price) {
    return number_format($price, 2);
}

// Function to get cart count
function getCartCount() {
    return isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
}

// Function to get cart total
function getCartTotal() {
    global $conn;
    $total = 0;
    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $productId => $quantity) {
            $stmt = $conn->prepare("SELECT price FROM products WHERE id = ?");
            $stmt->bind_param("i", $productId);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($product = $result->fetch_assoc()) {
                $total += $product['price'] * $quantity;
            }
        }
    }
    return $total;
}

// Function to check if file exists in uploads
function fileExistsInUploads($filename, $type = 'pdf') {
    $uploadDir = $config['upload']['upload_dir'][$type];
    return file_exists(__DIR__ . '/../' . $uploadDir . $filename);
}

// Function to get file URL
function getFileUrl($filename, $type = 'pdf') {
    $uploadDir = $config['upload']['upload_dir'][$type];
    return getBaseUrl() . '/' . $uploadDir . $filename;
}

// Function to handle errors
function handleError($message, $error = null) {
    error_log($message . ($error ? ": " . $error : ""));
    return "An error occurred. Please try again later.";
}

// Function to validate email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Function to generate random string
function generateRandomString($length = 10) {
    return bin2hex(random_bytes($length));
}

// Function to check if request is AJAX
function isAjaxRequest() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

// Function to send JSON response
function sendJsonResponse($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
?> 