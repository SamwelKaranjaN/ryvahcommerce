<?php
session_start();
header('Content-Type: application/json');

// Database connection using mysqli
$conn = mysqli_connect('127.0.0.1', 'root', 'your_secure_password', 'ecommerce');
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

try {
    if (isset($_SESSION['user_id'])) {
        $user_id = (int)$_SESSION['user_id'];
        // Fetch user info
        $stmt = mysqli_prepare($conn, "SELECT username, role FROM users WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'i', $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if ($user) {
            // Fetch notifications (example counts)
            $stmt = mysqli_prepare($conn, "SELECT 
                (SELECT COUNT(*) FROM orders WHERE status = 'pending') AS orders,
                (SELECT COUNT(*) FROM support_tickets WHERE status = 'open') AS support,
                (SELECT COUNT(*) FROM products WHERE stock_quantity < 10 AND type = 'paint') AS low_stock");
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $notifications = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);

            $notifications['total'] = $notifications['orders'] + $notifications['support'] + $notifications['low_stock'];

            echo json_encode([
                'success' => true,
                'user' => $user,
                'notifications' => $notifications
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'User not found']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Not logged in']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

mysqli_close($conn);
?>