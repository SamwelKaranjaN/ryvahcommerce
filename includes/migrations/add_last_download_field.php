<?php

/**
 * Migration: Add last_download field to ebook_downloads table
 * Date: 2024-01-01
 * Description: Add last_download timestamp to track when users last downloaded an ebook
 */

require_once '../config/database.php';

try {
    $conn = getDBConnection();

    // Check if the column already exists
    $result = $conn->query("SHOW COLUMNS FROM ebook_downloads LIKE 'last_download'");

    if ($result->num_rows == 0) {
        // Add the last_download column
        $sql = "ALTER TABLE ebook_downloads ADD COLUMN last_download TIMESTAMP NULL DEFAULT NULL AFTER expires_at";

        if ($conn->query($sql) === TRUE) {
            echo "Successfully added last_download column to ebook_downloads table.\n";

            // Update existing records to set last_download to NULL (indicating never downloaded)
            $update_sql = "UPDATE ebook_downloads SET last_download = NULL WHERE download_count = 0";

            if ($conn->query($update_sql) === TRUE) {
                echo "Successfully updated existing records.\n";
            } else {
                echo "Error updating existing records: " . $conn->error . "\n";
            }
        } else {
            echo "Error adding column: " . $conn->error . "\n";
        }
    } else {
        echo "Column last_download already exists in ebook_downloads table.\n";
    }

    $conn->close();
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
