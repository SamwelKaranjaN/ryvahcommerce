<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    $_SESSION['error_message'] = "Please login to access this page.";
    header("Location: ../admin/login.php");
    exit();
}

// Function to check if user has required role
function checkRole($required_role) {
    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== $required_role) {
        $_SESSION['error_message'] = "You don't have permission to access this page.";
        header("Location: ../admin/login.php");
        exit();
    }
}

// Function to get current user's role
function getCurrentUserRole() {
    return $_SESSION['user_role'] ?? null;
}

// Function to get current user's name
function getCurrentUserName() {
    return $_SESSION['user_name'] ?? 'User';
}

// Function to get current user's email
function getCurrentUserEmail() {
    return $_SESSION['user_email'] ?? '';
}

// Function to get current user's ID
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}
?> 