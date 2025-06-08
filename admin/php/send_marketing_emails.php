<?php
require_once 'db_connect.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Get the posted data
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Invalid input data']);
    exit();
}

$subject = $input['subject'] ?? '';
$body = $input['body'] ?? '';
$recipients = $input['recipients'] ?? [];

if (empty($subject) || empty($body) || empty($recipients)) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

// Get SMTP settings
$smtp_settings = $_SESSION['smtp_settings'] ?? [];

if (empty($smtp_settings['host']) || empty($smtp_settings['username']) || empty($smtp_settings['password'])) {
    echo json_encode(['success' => false, 'message' => 'SMTP settings not configured']);
    exit();
}

// Import PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once '../../vendor/autoload.php';

$sent_count = 0;
$failed_count = 0;
$errors = [];

foreach ($recipients as $recipient) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = $smtp_settings['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $smtp_settings['username'];
        $mail->Password = $smtp_settings['password'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = intval($smtp_settings['port']);

        // Recipients
        $mail->setFrom($smtp_settings['from_email'], $smtp_settings['from_name']);
        $mail->addAddress($recipient['email'], $recipient['name']);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;

        // Personalize the message
        $personalized_body = str_replace('{name}', $recipient['name'], $body);
        $mail->Body = $personalized_body;
        $mail->AltBody = strip_tags($personalized_body);

        $mail->send();
        $sent_count++;

        // Log successful send
        $log_query = "INSERT INTO marketing_email_logs (admin_id, recipient_email, subject, status, sent_at) VALUES (?, ?, ?, 'sent', NOW())";
        $stmt = $conn->prepare($log_query);
        if ($stmt) {
            $stmt->bind_param("iss", $_SESSION['user_id'], $recipient['email'], $subject);
            $stmt->execute();
        }
    } catch (Exception $e) {
        $failed_count++;
        $errors[] = "Failed to send to {$recipient['email']}: {$mail->ErrorInfo}";

        // Log failed send
        $log_query = "INSERT INTO marketing_email_logs (admin_id, recipient_email, subject, status, error_message, sent_at) VALUES (?, ?, ?, 'failed', ?, NOW())";
        $stmt = $conn->prepare($log_query);
        if ($stmt) {
            $stmt->bind_param("isss", $_SESSION['user_id'], $recipient['email'], $subject, $mail->ErrorInfo);
            $stmt->execute();
        }
    }

    // Small delay to avoid overwhelming the SMTP server
    usleep(100000); // 0.1 second delay
}

// Return results
echo json_encode([
    'success' => $sent_count > 0,
    'sent' => $sent_count,
    'failed' => $failed_count,
    'errors' => $errors,
    'message' => $sent_count > 0 ? "Successfully sent $sent_count emails" : "Failed to send any emails"
]);