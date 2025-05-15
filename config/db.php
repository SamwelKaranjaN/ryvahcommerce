<?php
// Load configuration
$config = require __DIR__ . '/config.php';

// Check if config is valid
if (!is_array($config) || !isset($config['database'])) {
    error_log("Invalid configuration file structure");
    die("Configuration error. Please contact the administrator.");
}

// Create connection
try {
    $conn = new mysqli(
        $config['database']['host'],
        $config['database']['username'],
        $config['database']['password'],
        $config['database']['dbname'],
        $config['database']['port']
    );

    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Set charset to utf8mb4
    if (!$conn->set_charset("utf8mb4")) {
        throw new Exception("Error loading character set utf8mb4: " . $conn->error);
    }

    // Set SQL mode
    $conn->query("SET SESSION sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'");

} catch (Exception $e) {
    // Log error
    error_log("Database connection error: " . $e->getMessage());
    
    // Show user-friendly message
    if (defined('AJAX_REQUEST') && AJAX_REQUEST) {
        header('Content-Type: application/json');
        die(json_encode(['error' => 'Database connection error']));
    } else {
        die("Sorry, there was a problem connecting to the database. Please try again later.");
    }
}

// Function to safely close the database connection
function closeConnection() {
    global $conn;
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}

// Register shutdown function to close connection
register_shutdown_function('closeConnection');
?> 