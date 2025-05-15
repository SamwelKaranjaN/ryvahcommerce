<?php
// Include session configuration
require_once __DIR__ . '/session_config.php';

// Database connection
$host = 'localhost';
$username = 'root';  // Your database username
$password = '@X6js1488';      // Your database password
$database = 'ryvahcommerce';  // Your database name

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8mb4
$conn->set_charset("utf8mb4");
?>