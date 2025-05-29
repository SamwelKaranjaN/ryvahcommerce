<?php
header('Content-Type: application/json');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['logged_out' => true]);
    exit;
}

// Check if last activity is set
if (isset($_SESSION['last_activity'])) {
    // Calculate time difference in seconds
    $time_diff = time() - $_SESSION['last_activity'];

    // If more than 2 minutes (120 seconds) have passed
    if ($time_diff > 120) {
        // Clear all session variables
        $_SESSION = array();

        // Destroy the session cookie
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }

        // Destroy the session
        session_destroy();

        echo json_encode(['logged_out' => true]);
        exit;
    }
}

// Update last activity time
$_SESSION['last_activity'] = time();
echo json_encode(['logged_out' => false]);