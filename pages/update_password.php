<?php
require_once '../includes/bootstrap.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: settings.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Validate input
$errors = [];

if (empty($current_password)) {
    $errors[] = 'Current password is required';
}

if (empty($new_password)) {
    $errors[] = 'New password is required';
} elseif (strlen($new_password) < 8) {
    $errors[] = 'New password must be at least 8 characters long';
} elseif (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/', $new_password)) {
    $errors[] = 'New password must contain both letters and numbers';
}

if ($new_password !== $confirm_password) {
    $errors[] = 'New passwords do not match';
}

if (!empty($errors)) {
    $_SESSION['error_messages'] = $errors;
    header('Location: settings.php');
    exit();
}

// Verify current password and update
$sql = "SELECT password, salt, iv FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    $_SESSION['error_messages'] = ['User not found'];
    header('Location: settings.php');
    exit();
}

// Decrypt and verify current password
$decrypted_password = decrypt_password($current_password, $user['salt'], $user['iv']);

if ($decrypted_password !== $user['password']) {
    $_SESSION['error_messages'] = ['Current password is incorrect'];
    header('Location: settings.php');
    exit();
}

// Generate new salt and IV for the new password
$new_salt = generate_salt();
$new_iv = generate_iv();

// Encrypt new password
$encrypted_password = encrypt_password($new_password, $new_salt, $new_iv);

// Update password in database
$sql = "UPDATE users SET password = ?, salt = ?, iv = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssi", $encrypted_password, $new_salt, $new_iv, $user_id);

if ($stmt->execute()) {
    $_SESSION['success_message'] = 'Password updated successfully';
} else {
    $_SESSION['error_messages'] = ['Failed to update password'];
}

header('Location: settings.php');
exit();
