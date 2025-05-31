-- Payment Logs Table
CREATE TABLE IF NOT EXISTS `payment_logs` (
    `id` int NOT NULL AUTO_INCREMENT,
    `payment_method` enum('stripe','paypal') NOT NULL,
    `status` enum('attempt','success','error') NOT NULL,
    `error_message` text,
    `metadata` json,
    `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_payment_method` (`payment_method`),
    KEY `idx_status` (`status`),
    KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Refund Logs Table
CREATE TABLE IF NOT EXISTS `refund_logs` (
    `id` int NOT NULL AUTO_INCREMENT,
    `payment_method` enum('stripe','paypal') NOT NULL,
    `payment_id` varchar(255) NOT NULL,
    `amount` decimal(10,2) NOT NULL,
    `reason` text,
    `success` tinyint(1) NOT NULL DEFAULT '0',
    `error_message` text,
    `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_payment_method` (`payment_method`),
    KEY `idx_payment_id` (`payment_id`),
    KEY `idx_success` (`success`),
    KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; 