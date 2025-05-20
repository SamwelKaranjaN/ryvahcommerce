<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../../config/database.php';

// Initialize database connection
$conn = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $errors = [];

    // Validation
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }

    if (empty($password)) {
        $errors[] = "Password is required";
    }

    if (empty($errors)) {
        try {
            // Get user from database
            $stmt = $conn->prepare("SELECT id, full_name, email, role, password, phone FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();

                // Verify password
                if (password_verify($password, $user['password'])) {
                    // Get IP address
                    $ip_address = $_SERVER['REMOTE_ADDR'];
                    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                        $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
                    }

                    // Record login in logs table
                    $logStmt = $conn->prepare("INSERT INTO user_logs (user_id, email, phone, ip_address, login_time) VALUES (?, ?, ?, ?, NOW())");
                    $logStmt->bind_param("isss", $user['id'], $user['email'], $user['phone'], $ip_address);
                    $logStmt->execute();
                    $logStmt->close();

                    // Set session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['full_name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_role'] = $user['role'];
                    $_SESSION['logged_in'] = true;
                    $_SESSION['login_time'] = date('Y-m-d H:i:s');
                    $_SESSION['log_id'] = $conn->insert_id; // Store the log entry ID

                    // Check for redirect URL in session first
                    if (isset($_SESSION['redirect_after_login'])) {
                        $redirect = $_SESSION['redirect_after_login'];
                        unset($_SESSION['redirect_after_login']);
                        header("Location: $redirect");
                        exit();
                    }

                    // Check for redirect parameter in URL
                    if (isset($_GET['redirect'])) {
                        $redirect = $_GET['redirect'];
                        // Validate redirect URL to prevent open redirect vulnerability
                        if (strpos($redirect, 'http') !== 0 && strpos($redirect, '//') !== 0) {
                            header("Location: $redirect");
                            exit();
                        }
                    }

                    // Redirect based on role
                    switch ($user['role']) {
                        case 'Admin':
                            header("Location: ../index");
                            break;
                        case 'Employee':
                            header("Location: ../index");
                            break;
                        default:
                            header("Location: ../index");
                    }
                    exit();
                } else {
                    $errors[] = "Invalid email or password";
                }
            } else {
                $errors[] = "Invalid email or password";
            }
        } catch (Exception $e) {
            $errors[] = "Login failed: " . $e->getMessage();
        }
    }

    if (!empty($errors)) {
        $_SESSION['error_message'] = implode("<br>", $errors);
        header("Location: ../index");
        exit();
    }
} else {
    header("Location: ../index");
    exit();
}