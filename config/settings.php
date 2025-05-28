<?php
// General settings
define('SITE_NAME', 'Ryvah Commerce');
define('BASE_URL', '/ryvahcommerce/');
define('SITE_URL', 'http://localhost/ryvahcommerce');
define('SITE_EMAIL', 'support@ryvahcommerce.com');

// Session settings
define('SESSION_LIFETIME', 86400); // 24 hours
define('SESSION_NAME', 'ryvahcommerce_session');

// File upload settings
define('UPLOAD_DIR', __DIR__ . '/../uploads');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'pdf']);

// Security settings
define('HASH_COST', 12); // For password hashing
define('TOKEN_LIFETIME', 3600); // 1 hour for password reset tokens

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/error.log');

// Time zone
date_default_timezone_set('UTC');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME,
        'path' => '/',
        'domain' => '',
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

// Helper functions
function redirect($url)
{
    header("Location: " . $url);
    exit();
}

function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

function requireLogin()
{
    if (!isLoggedIn()) {
        redirect('login.php');
    }
}

function sanitizeInput($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function generateToken()
{
    return bin2hex(random_bytes(32));
}

function validateToken($token)
{
    return isset($_SESSION['token']) && hash_equals($_SESSION['token'], $token);
}

function setFlashMessage($type, $message)
{
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

function getFlashMessage()
{
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

function formatPrice($price)
{
    return number_format($price, 2, '.', ',');
}

function formatDate($date)
{
    return date('F j, Y', strtotime($date));
}

function getFileExtension($filename)
{
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

function isAllowedFileType($filename)
{
    return in_array(getFileExtension($filename), ALLOWED_FILE_TYPES);
}

function createUploadDirectory()
{
    if (!file_exists(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0777, true);
    }
}

function generateUniqueFilename($filename)
{
    $extension = getFileExtension($filename);
    return uniqid() . '_' . time() . '.' . $extension;
}

function handleFileUpload($file)
{
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('File upload failed');
    }

    if ($file['size'] > MAX_FILE_SIZE) {
        throw new Exception('File size exceeds limit');
    }

    if (!isAllowedFileType($file['name'])) {
        throw new Exception('File type not allowed');
    }

    createUploadDirectory();

    $filename = generateUniqueFilename($file['name']);
    $destination = UPLOAD_DIR . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new Exception('Failed to move uploaded file');
    }

    return $filename;
}

if (!function_exists('ensure_session_and_debug')) {
    function ensure_session_and_debug($page = '')
    {
        if (headers_sent()) {
            error_log('WARNING: Headers already sent before session_start() on ' . $page);
        }
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        error_log('DEBUG: session_id=' . session_id() . ' user_id=' . ($_SESSION['user_id'] ?? 'not set') . ' page=' . $page);
    }
}