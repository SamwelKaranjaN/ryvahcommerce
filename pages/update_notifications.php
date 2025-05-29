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
$email_notifications = isset($_POST['email_notifications']) ? 1 : 0;
$marketing_emails = isset($_POST['marketing_emails']) ? 1 : 0;

// Update notification preferences
$sql = "UPDATE users SET 
        email_notifications = ?,
        marketing_emails = ?
        WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $email_notifications, $marketing_emails, $user_id);

if ($stmt->execute()) {
    $_SESSION['success_message'] = 'Notification preferences updated successfully';
} else {
    $_SESSION['error_messages'] = ['Failed to update notification preferences'];
}

header('Location: settings.php');
exit();
