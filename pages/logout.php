<?php
require_once '../includes/bootstrap.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Store user's name for the success message if available
$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '';

// Clear all session variables
$_SESSION = array();

// Destroy the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Destroy the session
session_destroy();

// Set success message in a new session
session_start();
$_SESSION['success_message'] = $user_name ? "Goodbye, $user_name! You have been successfully logged out." : "You have been successfully logged out.";

// Redirect to home page
header('Location: index.php');
exit;