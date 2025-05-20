<?php
require_once '../config/database.php';

// Initialize database connection
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Create transactions table
$sql = "CREATE TABLE IF NOT EXISTS transactions (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    date DATE NOT NULL,
    type ENUM('revenue', 'expense', 'asset', 'liability', 'equity', 'investment', 'financing', 'receivable', 'payable', 'debit', 'credit') NOT NULL,
    category VARCHAR(100) NOT NULL,
    account_code VARCHAR(20) NOT NULL,
    account_name VARCHAR(100) NOT NULL,
    description TEXT,
    amount DECIMAL(15,2) NOT NULL,
    customer_id INT UNSIGNED NULL,
    customer_name VARCHAR(255) NULL,
    vendor_id INT UNSIGNED NULL,
    vendor_name VARCHAR(255) NULL,
    reference_number VARCHAR(50) NULL,
    payment_method VARCHAR(50) NULL,
    status ENUM('pending', 'completed', 'cancelled', 'reconciled') NOT NULL DEFAULT 'pending',
    created_by INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_date (date),
    INDEX idx_type (type),
    INDEX idx_category (category),
    INDEX idx_account_code (account_code),
    INDEX idx_customer_id (customer_id),
    INDEX idx_vendor_id (vendor_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

if ($conn->query($sql) === TRUE) {
    echo "Table 'transactions' created successfully";
} else {
    echo "Error creating table: " . $conn->error;
}

// Create budgets table for budget vs actual reporting
$sql = "CREATE TABLE IF NOT EXISTS budgets (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    category VARCHAR(100) NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    period_start DATE NOT NULL,
    period_end DATE NOT NULL,
    created_by INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_category (category),
    INDEX idx_period (period_start, period_end)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

if ($conn->query($sql) === TRUE) {
    echo "\nTable 'budgets' created successfully";
} else {
    echo "\nError creating budgets table: " . $conn->error;
}

// Create tax_transactions table for tax reporting
$sql = "CREATE TABLE IF NOT EXISTS tax_transactions (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    date DATE NOT NULL,
    tax_type VARCHAR(50) NOT NULL,
    tax_rate DECIMAL(5,2) NOT NULL,
    taxable_amount DECIMAL(15,2) NOT NULL,
    tax_amount DECIMAL(15,2) NOT NULL,
    transaction_id INT UNSIGNED NOT NULL,
    status ENUM('pending', 'paid', 'refunded') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_date (date),
    INDEX idx_tax_type (tax_type),
    INDEX idx_transaction_id (transaction_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

if ($conn->query($sql) === TRUE) {
    echo "\nTable 'tax_transactions' created successfully";
} else {
    echo "\nError creating tax_transactions table: " . $conn->error;
}

$conn->close();
