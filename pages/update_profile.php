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
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $errors = [];

    // Validate full name
    if (empty($full_name)) {
        $errors[] = "Full name is required";
    } elseif (strlen($full_name) < 2 || strlen($full_name) > 255) {
        $errors[] = "Full name must be between 2 and 255 characters";
    }

    // Validate phone number
    if (!empty($phone) && !preg_match('/^[0-9]{10}$/', $phone)) {
        $errors[] = "Phone number must be 10 digits";
    }

    // If no errors, update the profile
    if (empty($errors)) {
        try {
            $sql = "UPDATE users SET full_name = ?, phone = ?, address = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssi", $full_name, $phone, $address, $user_id);

            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Profile updated successfully";
                header('Location: profile.php');
                exit();
            } else {
                throw new Exception("Error updating profile");
            }
        } catch (Exception $e) {
            $errors[] = "An error occurred while updating your profile. Please try again.";
            error_log("Profile update error: " . $e->getMessage());
        }
    }

    // If there are errors, store them in session and redirect back
    if (!empty($errors)) {
        $_SESSION['error_messages'] = $errors;
        header('Location: profile.php');
        exit();
    }
} else {
    // If not POST request, redirect to profile page
    header('Location: profile.php');
    exit();
}