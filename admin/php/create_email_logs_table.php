<?php
require_once 'db_connect.php';

// Create marketing_email_logs table
$sql = "CREATE TABLE IF NOT EXISTS marketing_email_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    recipient_email VARCHAR(255) NOT NULL,
    subject VARCHAR(500) NOT NULL,
    status ENUM('sent', 'failed') NOT NULL,
    error_message TEXT NULL,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_admin_id (admin_id),
    INDEX idx_recipient_email (recipient_email),
    INDEX idx_status (status),
    INDEX idx_sent_at (sent_at)
) ENGINE=MyISAM DEFAULT CHARSET=latin1";

if ($conn->query($sql) === TRUE) {
    echo "Marketing email logs table created successfully or already exists.\n";
} else {
    echo "Error creating table: " . $conn->error . "\n";
}

$conn->close();