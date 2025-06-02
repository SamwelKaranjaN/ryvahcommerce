<?php
// Get database configuration
$db_config = [
    'host' => getConfig('db_host'),
    'name' => getConfig('db_name'),
    'user' => getConfig('db_user'),
    'pass' => getConfig('db_pass')
];

// Create database connection
$conn = new mysqli(
    $db_config['host'],
    $db_config['user'],
    $db_config['pass'],
    $db_config['name']
);

// Check connection
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    die("Connection failed: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8mb4"); 