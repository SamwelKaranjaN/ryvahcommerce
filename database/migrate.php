<?php
require_once __DIR__ . '/../config/db.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Read the migration file
    $migration = file_get_contents(__DIR__ . '/migrations/add_shipping_fields.sql');

    // Split the migration into individual statements
    $statements = array_filter(array_map('trim', explode(';', $migration)));

    // Execute each statement
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            if (!$conn->query($statement)) {
                throw new Exception("Error executing statement: " . $conn->error);
            }
            echo "Executed: " . substr($statement, 0, 50) . "...\n";
        }
    }

    echo "Migration completed successfully!\n";
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
