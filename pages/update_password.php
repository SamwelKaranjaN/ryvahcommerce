<?php
require_once '../includes/bootstrap.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $errors = [];

    // Validate current password
    if (empty($current_password)) {
        $errors[] = "Current password is required";
    }

    // Validate new password
    if (empty($new_password)) {
        $errors[] = "New password is required";
    } elseif (strlen($new_password) < 8) {
        $errors[] = "New password must be at least 8 characters long";
    } elseif (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/', $new_password)) {
        $errors[] = "New password must contain both letters and numbers";
    }

    // Validate password confirmation
    if ($new_password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }

    // If no errors, proceed with password update
    if (empty($errors)) {
        try {
            // First, verify current password
            $sql = "SELECT password FROM users WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if (!password_verify($current_password, $user['password'])) {
                $errors[] = "Current password is incorrect";
            } else {
                // Hash the new password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

                // Update the password
                $sql = "UPDATE users SET password = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("si", $hashed_password, $user_id);

                if ($stmt->execute()) {
                    $_SESSION['success_message'] = "Password updated successfully";
                    header('Location: settings.php');
                    exit();
                } else {
                    throw new Exception("Error updating password");
                }
            }
        } catch (Exception $e) {
            $errors[] = "An error occurred while updating your password. Please try again.";
            error_log("Password update error: " . $e->getMessage());
        }
    }

    // If there are errors, store them in session and redirect back
    if (!empty($errors)) {
        $_SESSION['error_messages'] = $errors;
        header('Location: settings.php');
        exit();
    }
} else {
    // If not POST request, redirect to settings page
    header('Location: settings.php');
    exit();
}