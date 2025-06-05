<?php
// Only set session settings if session hasn't started yet
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.use_strict_mode', 1);
    ini_set('session.use_only_cookies', 1);

    // Start session
    session_start();
}

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1); // Temporarily enable error display for debugging
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../php_errors.log');

// Set default timezone
date_default_timezone_set('UTC');

// Load Composer autoloader
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
} else {
    error_log('Composer autoloader not found. Please run "composer install"');
}

// Only set headers if not already sent and not in CLI mode
if (!headers_sent() && php_sapi_name() !== 'cli') {
    // Set character encoding
    header('Content-Type: text/html; charset=utf-8');
}

// Start output buffering if not already started and not in CLI mode
if (!ob_get_level() && php_sapi_name() !== 'cli') {
    ob_start();
}

// Include security headers only if headers not sent and not in CLI mode
if (!headers_sent() && php_sapi_name() !== 'cli') {
    require_once __DIR__ . '/security_headers.php';
}

// Load configuration
$config = require_once __DIR__ . '/config.php';

// Define getConfig function
function getConfig($key)
{
    global $config;
    return $config[$key] ?? null;
}

// Load unified database connection
require_once __DIR__ . '/../config/database.php';

// Load functions
require_once __DIR__ . '/functions.php';

// Load encryption functions
require_once __DIR__ . '/encryption.php';

// Function to clean output buffer and send headers
function cleanOutput()
{
    if (ob_get_length()) {
        ob_end_clean();
    }
}

// Register shutdown function
register_shutdown_function(function () {
    $error = error_get_last();
    if ($error !== NULL && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        cleanOutput();
        error_log("Fatal Error: " . $error['message'] . " in " . $error['file'] . " on line " . $error['line']);
        if (!isAjaxRequest()) {
            include __DIR__ . '/layouts/error.php';
        } else {
            sendJsonResponse(['error' => 'A fatal error occurred'], 500);
        }
    }
});
