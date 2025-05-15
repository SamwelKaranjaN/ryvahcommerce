<?php
require_once '../../config/database.php';

try {
    // Create user_logs table if it doesn't exist
    $sql = "CREATE TABLE IF NOT EXISTS `user_logs` (
        `id` int NOT NULL AUTO_INCREMENT,
        `user_id` int NOT NULL,
        `email` varchar(255) NOT NULL,
        `phone` varchar(20) DEFAULT NULL,
        `ip_address` varchar(45) NOT NULL,
        `login_time` DATETIME NOT NULL,
        `logout_time` DATETIME DEFAULT NULL,
        `session_duration` INT DEFAULT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `user_id` (`user_id`),
        KEY `email` (`email`),
        KEY `login_time` (`login_time`),
        CONSTRAINT `fk_user_logs_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    if ($conn->query($sql)) {
        echo "Successfully created user_logs table.<br>";
    } else {
        echo "Error creating user_logs table: " . $conn->error . "<br>";
    }

    echo "Database update completed successfully!";
} catch (Exception $e) {
    echo "Error updating database: " . $e->getMessage();
}
?> 