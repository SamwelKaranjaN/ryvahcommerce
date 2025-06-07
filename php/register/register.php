<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Database configuration
require_once "../../config/database.php";

// Get the database connection
$conn = getDBConnection();

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);
$full_name = isset($input['full_name']) ? trim($input['full_name']) : '';
$email = isset($input['email']) ? trim($input['email']) : '';
$password = isset($input['password']) ? $input['password'] : '';
$phone = isset($input['phone']) ? trim($input['phone']) : null;
$address = isset($input['address']) ? trim($input['address']) : null;
$city = isset($input['city']) ? trim($input['city']) : null;
$state = isset($input['state']) ? trim($input['state']) : null;
$postal_code = isset($input['postal_code']) ? trim($input['postal_code']) : null;

// Validate required fields
if (empty($full_name) || empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode(['error' => 'Full name, email, and password are required']);
    exit;
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email format']);
    exit;
}

// Check if email already exists
$stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
mysqli_stmt_bind_param($stmt, 's', $email);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);
if (mysqli_stmt_num_rows($stmt) > 0) {
    mysqli_stmt_close($stmt);
    http_response_code(400);
    echo json_encode(['error' => 'Email already exists']);
    exit;
}
mysqli_stmt_close($stmt);

// Hash password
$hashed_password = password_hash($password, PASSWORD_BCRYPT);

// Generate encryption fields
$encryption_key = bin2hex(openssl_random_pseudo_bytes(32)); // 256-bit key
$salt = bin2hex(openssl_random_pseudo_bytes(16)); // Random salt
$iv = bin2hex(openssl_random_pseudo_bytes(16)); // Initialization vector

// Start transaction
mysqli_autocommit($conn, false);

try {
    // Insert user into database (role is omitted to use table's default 'Client')
    $query = "INSERT INTO users (full_name, email, password, phone, encryption_key, salt, iv, address, city, state, postal_code) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param(
        $stmt,
        'sssssssssss',
        $full_name,
        $email,
        $hashed_password,
        $phone,
        $encryption_key,
        $salt,
        $iv,
        $address,
        $city,
        $state,
        $postal_code
    );

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Failed to register user: ' . mysqli_error($conn));
    }

    // Get the new user ID
    $user_id = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);

    // Insert into customers table
    $customer_query = "INSERT INTO customers (name, email, phone) VALUES (?, ?, ?)";
    $customer_stmt = mysqli_prepare($conn, $customer_query);
    mysqli_stmt_bind_param($customer_stmt, 'sss', $full_name, $email, $phone);

    if (!mysqli_stmt_execute($customer_stmt)) {
        throw new Exception('Failed to create customer record: ' . mysqli_error($conn));
    }
    mysqli_stmt_close($customer_stmt);

    // Insert into addresses table if address information is provided
    if (!empty($address) && !empty($city) && !empty($state) && !empty($postal_code)) {
        $address_query = "INSERT INTO addresses (user_id, full_name, street, city, state, postal_code, is_default) VALUES (?, ?, ?, ?, ?, ?, 1)";
        $address_stmt = mysqli_prepare($conn, $address_query);
        mysqli_stmt_bind_param($address_stmt, 'isssss', $user_id, $full_name, $address, $city, $state, $postal_code);

        if (!mysqli_stmt_execute($address_stmt)) {
            throw new Exception('Failed to create address record: ' . mysqli_error($conn));
        }
        mysqli_stmt_close($address_stmt);
    }

    // Commit transaction
    mysqli_commit($conn);

    http_response_code(201);
    echo json_encode(['message' => 'User registered successfully']);
} catch (Exception $e) {
    // Rollback transaction on error
    mysqli_rollback($conn);

    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    // Restore autocommit
    mysqli_autocommit($conn, true);
}