<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../php_errors.log');

// Set default timezone
date_default_timezone_set('UTC');

// Set character encoding
header('Content-Type: text/html; charset=utf-8');

// Start output buffering
ob_start();

// Load configuration
$config = require_once __DIR__ . '/../config/config.php';

// Load database connection
require_once __DIR__ . '/../config/db.php';

// Load functions
require_once __DIR__ . '/functions.php';

// Function to clean output buffer and send headers
function cleanOutput() {
    if (ob_get_length()) {
        ob_end_clean();
    }
}

// Register shutdown function
register_shutdown_function(function() {
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