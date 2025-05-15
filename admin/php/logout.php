<?php
// Start the session
session_start();

// Include database configuration
require_once __DIR__ . '/../config/config.php';

// Check if user is logged in
if (isset($_SESSION['user_id'])) {
    // Clear all session variables
    $_SESSION = array();
    
    // Destroy the session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    
    // Destroy the session
    session_destroy();
}

// Close database connection if it exists
if (isset($conn)) {
    $conn->close();
}

// Redirect to login page
header("Location: ../login");
exit();
?> 