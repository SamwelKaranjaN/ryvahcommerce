<?php
session_start();

require 'config/config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, filter_var($_POST['email'], FILTER_SANITIZE_EMAIL));
    $password = $_POST['password'];

    // Retrieve user data
    $stmt = $conn->prepare("SELECT id, email, password, encryption_key, salt, iv FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        // Decode stored encryption key, salt, and IV
        $encryption_key = base64_decode($user['encryption_key']);
        $salt = base64_decode($user['salt']);
        $iv = base64_decode($user['iv']);

        // Encrypt the provided password with AES-256-CBC
        $encrypted_password = openssl_encrypt($password, 'aes-256-cbc', $encryption_key, 0, $iv);
        if ($encrypted_password === false) {
            header("Location: login.html?error=" . urlencode("Encryption failed"));
            exit();
        }

        // Verify the encrypted password against the stored bcrypt hash
        if (password_verify($encrypted_password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            header("Location: dashboard.php");
            exit();
        } else {
            header("Location: login.html?error=" . urlencode("Invalid email or password"));
            exit();
        }
    } else {
        header("Location: login.html?error=" . urlencode("Invalid email or password"));
        exit();
    }

    $stmt->close();
}

$conn->close();
