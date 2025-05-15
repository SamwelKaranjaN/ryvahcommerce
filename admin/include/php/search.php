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
    if (isset($_POST['query']) && !empty($_POST['query'])) {
        $query = '%' . mysqli_real_escape_string($conn, $_POST['query']) . '%';
        $stmt = mysqli_prepare($conn, "
            SELECT 'product' AS type, id, name FROM products WHERE name LIKE ? OR sku LIKE ?
            UNION
            SELECT 'order' AS type, id, status AS name FROM orders WHERE id LIKE ?
            UNION
            SELECT 'customer' AS type, id, name FROM customers WHERE name LIKE ? OR email LIKE ?
        ");
        mysqli_stmt_bind_param($stmt, 'sssss', $query, $query, $query, $query, $query);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $results = mysqli_fetch_all($result, MYSQLI_ASSOC);
        mysqli_stmt_close($stmt);

        echo json_encode(['success' => true, 'results' => $results]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Empty query']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

mysqli_close($conn);