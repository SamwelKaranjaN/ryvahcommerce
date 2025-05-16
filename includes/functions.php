<?php
// Load configuration
$config = require_once __DIR__ . '/../config/config.php';

// Function to sanitize input
function sanitize_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to validate email
function validate_email($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Function to validate password strength
function validate_password($password)
{
    // At least 8 characters, 1 uppercase, 1 lowercase, 1 number
    return strlen($password) >= 8 &&
        preg_match('/[A-Z]/', $password) &&
        preg_match('/[a-z]/', $password) &&
        preg_match('/[0-9]/', $password);
}

// Function to generate random token
function generate_token($length = 32)
{
    return bin2hex(random_bytes($length));
}

// Function to encrypt data
function encrypt_data($data, $key, $iv)
{
    return openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);
}

// Function to decrypt data
function decrypt_data($encrypted_data, $key, $iv)
{
    return openssl_decrypt($encrypted_data, 'AES-256-CBC', $key, 0, $iv);
}

// Function to get the base URL
function getBaseUrl()
{
    global $config;
    return isset($config['site']['url']) ? $config['site']['url'] : '';
}

// Function to get the site name
function getSiteName()
{
    global $config;
    return isset($config['site']['name']) ? $config['site']['name'] : 'Ryvah Books';
}

// Function to get the site email
function getSiteEmail()
{
    global $config;
    return isset($config['site']['email']) ? $config['site']['email'] : '';
}

// Function to sanitize output
function h($string)
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Function to check if user is logged in
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

// Function to get current user ID
function getCurrentUserId()
{
    return $_SESSION['user_id'] ?? null;
}

// Function to get user data
function getUserData($userId)
{
    global $conn;
    try {
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $conn->error);
        }

        $stmt->bind_param("i", $userId);
        if (!$stmt->execute()) {
            throw new Exception("Failed to execute statement: " . $stmt->error);
        }

        $result = $stmt->get_result();
        return $result->fetch_assoc();
    } catch (Exception $e) {
        error_log("Error in getUserData: " . $e->getMessage());
        return null;
    }
}

// Function to format price
function formatPrice($price)
{
    return number_format($price, 2);
}

// Function to get cart count
function getCartCount()
{
    return isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
}

// Function to get cart total
function getCartTotal()
{
    global $conn;
    $total = 0;
    try {
        if (isset($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $productId => $quantity) {
                $stmt = $conn->prepare("SELECT price FROM products WHERE id = ?");
                if (!$stmt) {
                    throw new Exception("Failed to prepare statement: " . $conn->error);
                }

                $stmt->bind_param("i", $productId);
                if (!$stmt->execute()) {
                    throw new Exception("Failed to execute statement: " . $stmt->error);
                }

                $result = $stmt->get_result();
                if ($product = $result->fetch_assoc()) {
                    $total += $product['price'] * $quantity;
                }
            }
        }
        return $total;
    } catch (Exception $e) {
        error_log("Error in getCartTotal: " . $e->getMessage());
        return 0;
    }
}

// Function to check if file exists in uploads
function fileExistsInUploads($filename, $type = 'pdf')
{
    global $config;
    try {
        if (!isset($config['upload']['upload_dir'][$type])) {
            throw new Exception("Invalid upload type: $type");
        }
        $uploadDir = $config['upload']['upload_dir'][$type];
        return file_exists(__DIR__ . '/../' . $uploadDir . $filename);
    } catch (Exception $e) {
        error_log("Error in fileExistsInUploads: " . $e->getMessage());
        return false;
    }
}

// Function to get file URL
function getFileUrl($filename, $type = 'pdf')
{
    global $config;
    try {
        if (!isset($config['upload']['upload_dir'][$type])) {
            throw new Exception("Invalid upload type: $type");
        }
        $uploadDir = $config['upload']['upload_dir'][$type];
        return getBaseUrl() . '/' . $uploadDir . $filename;
    } catch (Exception $e) {
        error_log("Error in getFileUrl: " . $e->getMessage());
        return '';
    }
}

// Function to handle errors
function handleError($message, $error = null)
{
    $errorMessage = $message . ($error ? ": " . $error : "");
    error_log($errorMessage);
    return "An error occurred. Please try again later.";
}

// Function to validate email
function isValidEmail($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Function to generate random string
function generateRandomString($length = 10)
{
    return bin2hex(random_bytes($length));
}

// Function to check if request is AJAX
function isAjaxRequest()
{
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

// Function to send JSON response
function sendJsonResponse($data, $status = 200)
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// Function to safely get session value
function getSessionValue($key, $default = null)
{
    return $_SESSION[$key] ?? $default;
}

// Function to safely set session value
function setSessionValue($key, $value)
{
    $_SESSION[$key] = $value;
}

// Function to safely unset session value
function unsetSessionValue($key)
{
    if (isset($_SESSION[$key])) {
        unset($_SESSION[$key]);
    }
}

// Function to check if session is active
function isSessionActive()
{
    return session_status() === PHP_SESSION_ACTIVE;
}

// Function to start session if not active
function startSessionIfNotActive()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Get product details by ID
 * @param int $productId Product ID
 * @return array|null Product details or null if not found
 */
function getProductDetails($productId)
{
    global $conn;

    $stmt = $conn->prepare("
        SELECT id, name, price, thumbs, type, filepath 
        FROM products 
        WHERE id = ?
    ");

    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->fetch_assoc();
}
