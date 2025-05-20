<?php
session_start();

// Database connection
require '../config/config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = mysqli_real_escape_string($conn, filter_var($_POST['full_name'] ?? '', FILTER_SANITIZE_STRING));
    $email = mysqli_real_escape_string($conn, filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL));
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $phone = mysqli_real_escape_string($conn, filter_var($_POST['phone'] ?? '', FILTER_SANITIZE_STRING)) ?: null;
    $address = mysqli_real_escape_string($conn, filter_var($_POST['address'] ?? '', FILTER_SANITIZE_STRING)) ?: null;
    $role = 'Client'; // Default role for new registrations

    // Server-side validation
    if (empty($full_name) || empty($email) || empty($password) || empty($confirm_password)) {
        echo json_encode(['success' => false, 'error' => 'All required fields must be filled']);
        exit();
    }

    if ($password !== $confirm_password) {
        echo json_encode(['success' => false, 'error' => 'Passwords do not match']);
        exit();
    }

    if (strlen($password) < 8) {
        echo json_encode(['success' => false, 'error' => 'Password must be at least 8 characters long']);
        exit();
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        echo json_encode(['success' => false, 'error' => 'Email already registered']);
        exit();
    }
    $stmt->close();

    // Generate AES-256 encryption key, salt, and IV
    $encryption_key = openssl_random_pseudo_bytes(32); // 256-bit key
    $salt = openssl_random_pseudo_bytes(16); // Unique salt per user
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc')); // IV for AES-256-CBC

    // Encrypt password with AES-256-CBC
    $encrypted_password = openssl_encrypt($password, 'aes-256-cbc', $encryption_key, 0, $iv);
    if ($encrypted_password === false) {
        echo json_encode(['success' => false, 'error' => 'Encryption failed']);
        exit();
    }

    // Hash the encrypted password with bcrypt
    $hashed_password = password_hash($encrypted_password, PASSWORD_DEFAULT);

    // Store encryption key, salt, and IV securely (base64 encoded for database storage)
    $encryption_key_b64 = base64_encode($encryption_key);
    $salt_b64 = base64_encode($salt);
    $iv_b64 = base64_encode($iv);

    // Insert user into database
    $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, phone, address, role, encryption_key, salt, iv) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssss", $full_name, $email, $hashed_password, $phone, $address, $role, $encryption_key_b64, $salt_b64, $iv_b64);

    if ($stmt->execute()) {
        $_SESSION['user_id'] = $conn->insert_id;
        $_SESSION['email'] = $email;
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Registration failed: ' . $conn->error]);
    }

    $stmt->close();
}

$conn->close();