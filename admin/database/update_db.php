<?php
// Include database configuration
require_once __DIR__ . '/../config/config.php';

// Read and execute the SQL file
$sql = file_get_contents(__DIR__ . '/remove_last_logout.sql');

if ($conn->multi_query($sql)) {
    echo "Successfully removed last_logout column from users table.\n";
} else {
    echo "Error updating database: " . $conn->error . "\n";
}

// Close the connection
$conn->close();
?> 