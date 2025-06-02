<?php
require_once 'db_connect.php';

// Create tax_settings table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS tax_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_type VARCHAR(50) NOT NULL UNIQUE,
    tax_rate DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Table tax_settings checked/created successfully\n";
    
    // Insert default tax rates if table is empty
    $result = $conn->query("SELECT COUNT(*) as count FROM tax_settings");
    $row = $result->fetch_assoc();
    
    if ($row['count'] == 0) {
        $default_rates = [
            ['standard', 0.00],
            ['reduced', 0.00],
            ['zero', 0.00]
        ];
        
        $stmt = $conn->prepare("INSERT INTO tax_settings (product_type, tax_rate) VALUES (?, ?)");
        
        foreach ($default_rates as $rate) {
            $stmt->bind_param("sd", $rate[0], $rate[1]);
            $stmt->execute();
        }
        
        echo "Default tax rates inserted successfully\n";
    }
} else {
    echo "Error creating table: " . $conn->error . "\n";
}

$conn->close();
?> 