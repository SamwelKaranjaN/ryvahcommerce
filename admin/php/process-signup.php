<?php
session_start();
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $role = $_POST['role'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validation
    $errors = [];

    if (empty($full_name)) {
        $errors[] = "Full name is required";
    }

    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }

    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }

    if (empty($role)) {
        $errors[] = "Role is required";
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $errors[] = "Email already exists";
    }
    $stmt->close();

    if (empty($errors)) {
        try {
            // Generate encryption key, salt, and IV
            $encryption_key = bin2hex(random_bytes(32));
            $salt = bin2hex(random_bytes(16));
            $iv = bin2hex(random_bytes(16));

            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert user
            $stmt = $conn->prepare("INSERT INTO users (full_name, email, role, password, phone, encryption_key, salt, iv) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssss", $full_name, $email, $role, $hashed_password, $phone, $encryption_key, $salt, $iv);
            
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Registration successful! You can now login.";
                header("Location: ../login");
                exit();
            } else {
                throw new Exception("Error creating user account");
            }
        } catch (Exception $e) {
            $errors[] = "Registration failed: " . $e->getMessage();
        }
    }

    if (!empty($errors)) {
        $_SESSION['error_message'] = implode("<br>", $errors);
        header("Location: ../user-signup");
        exit();
    }
} else {
    header("Location: ../user-signup");
    exit();
}
?> 