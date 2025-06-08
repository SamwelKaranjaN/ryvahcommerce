<?php
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

$host = $input['host'] ?? '';
$port = $input['port'] ?? 465;
$username = $input['username'] ?? 'info@ryvahcommerce.com';
$password = $input['password'] ?? 'Meldor1!1';

if (empty($host) || empty($username) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Missing SMTP configuration']);
    exit();
}

// Import PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once '../../vendor/autoload.php';

$mail = new PHPMailer(true);

// Enable error reporting
error_reporting(E_ALL);

try {
    // Server settings
    $mail->isSMTP();
    $mail->Host = $host;
    $mail->SMTPAuth = true;
    $mail->Username = $username;
    $mail->Password = $password;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = intval($port);

    // Set timeout values
    $mail->Timeout = 30;
    $mail->SMTPKeepAlive = false;

    // Enable basic debugging
    $mail->SMTPDebug = 0;

    // Capture debug output
    ob_start();

    // Test the connection
    if (!$mail->smtpConnect()) {
        $debug_output = ob_get_clean();
        throw new Exception('SMTP Connect failed. Debug: ' . $debug_output);
    }

    // Close connection
    $mail->smtpClose();
    ob_end_clean();

    echo json_encode([
        'success' => true,
        'message' => "SMTP connection successful!\nHost: $host\nPort: $port\nEncryption: SSL/TLS"
    ]);
} catch (Exception $e) {
    ob_end_clean();

    $error_message = $e->getMessage();

    // Add more specific error details
    if (empty($error_message) && !empty($mail->ErrorInfo)) {
        $error_message = $mail->ErrorInfo;
    }

    if (empty($error_message)) {
        $error_message = 'Unknown SMTP connection error';
    }

    // Add common troubleshooting tips based on error patterns
    $troubleshooting = '';
    if (strpos($error_message, 'Connection timed out') !== false) {
        $troubleshooting = "\n\nTroubleshooting:\n• Check if port $port is open\n• Verify your internet connection\n• Contact your hosting provider";
    } elseif (strpos($error_message, 'Authentication failed') !== false || strpos($error_message, 'Username and Password not accepted') !== false) {
        $troubleshooting = "\n\nTroubleshooting:\n• Verify your email credentials\n• Check if 2-factor authentication is enabled\n• Use an app-specific password if required";
    } elseif (strpos($error_message, 'Connection refused') !== false) {
        $troubleshooting = "\n\nTroubleshooting:\n• Verify the SMTP server address\n• Check if the correct port is being used\n• Ensure SMTP is enabled on your email account";
    }

    echo json_encode([
        'success' => false,
        'message' => "SMTP connection failed: $error_message$troubleshooting"
    ]);
}