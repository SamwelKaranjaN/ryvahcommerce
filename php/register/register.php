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

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    http_response_code(201);
    echo json_encode(['message' => 'User registered successfully']);
} else {
    mysqli_stmt_close($stmt);
    http_response_code(500);
    echo json_encode(['error' => 'Failed to register user: ' . mysqli_error($conn)]);
}