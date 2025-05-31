<?php
// Database configuration
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '@X6js1488';
$db_name = 'ryvahcommerce';

// Create connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8mb4
$conn->set_charset("utf8mb4");

// Function to get database connection
function getDBConnection()
{
    global $conn;
    return $conn;
}