<?php
require_once __DIR__ . '/../db_connect.php';

// Test user data
$full_name = 'Test User';
$email = 'test@example.com';
$password = 'password123';
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Check if user already exists
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Insert test user
    $stmt = $conn->prepare("INSERT INTO users (full_name, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $full_name, $email, $hashed_password);

    if ($stmt->execute()) {
        echo "Test user created successfully\n";
        echo "Email: test@example.com\n";
        echo "Password: password123\n";
    } else {
        echo "Error creating test user: " . $stmt->error . "\n";
    }
} else {
    echo "Test user already exists\n";
    echo "Email: test@example.com\n";
    echo "Password: password123\n";
}

$stmt->close();
$conn->close();