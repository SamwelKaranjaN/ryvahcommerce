<?php
require_once __DIR__ . '/../../config/database.php';

// Read the SQL file
$sql = file_get_contents(__DIR__ . '/create_users_table.sql');

// Execute the SQL
if ($conn->multi_query($sql)) {
    echo "Users table created successfully\n";
} else {
    echo "Error creating users table: " . $conn->error . "\n";
}

// Close connection
$conn->close();