<?php
require_once __DIR__ . '/../includes/bootstrap.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Starting migration...\n";

try {
    // Start transaction
    if (!$conn->begin_transaction()) {
        throw new Exception("Failed to start transaction");
    }

    // Add new columns
    $queries = [
        "ALTER TABLE orders
        ADD COLUMN invoice_number VARCHAR(20) UNIQUE AFTER id",

        "CREATE INDEX idx_orders_invoice ON orders(invoice_number)",

        "UPDATE orders 
        SET invoice_number = CONCAT('INV-', DATE_FORMAT(created_at, '%Y%m%d'), '-', LPAD(id, 5, '0'))
        WHERE invoice_number IS NULL",

        "ALTER TABLE orders
        MODIFY COLUMN invoice_number VARCHAR(20) NOT NULL"
    ];

    // Execute each query
    foreach ($queries as $query) {
        echo "Executing: " . substr($query, 0, 50) . "...\n";
        if (!$conn->query($query)) {
            throw new Exception("Query failed: " . $conn->error);
        }
    }

    // Commit transaction
    if (!$conn->commit()) {
        throw new Exception("Failed to commit transaction");
    }

    echo "Migration completed successfully!\n";
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
