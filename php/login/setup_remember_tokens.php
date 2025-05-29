<?php
require_once __DIR__ . '/../db_connect.php';

// Read the SQL file
$sql = file_get_contents(__DIR__ . '/create_remember_tokens_table.sql');

// Execute the SQL
if ($conn->multi_query($sql)) {
    echo "Remember tokens table created successfully\n";
} else {
    echo "Error creating remember tokens table: " . $conn->error . "\n";
}

// Close connection
$conn->close();