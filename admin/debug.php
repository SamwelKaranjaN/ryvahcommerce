<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Diagnostic Script</h1>";
echo "<h2>PHP Information</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Session Status: " . session_status() . "<br>";

echo "<h2>File Structure Check</h2>";

// Check if critical files exist
$critical_files = [
    'config/config.php',
    'php/session_check.php',
    'includes/csrf.php',
    'header.php'
];

foreach ($critical_files as $file) {
    $path = __DIR__ . '/' . $file;
    echo "File: $file - " . (file_exists($path) ? "EXISTS" : "MISSING") . "<br>";
}

echo "<h2>Directory Permissions</h2>";

$directories = [
    'Uploads/',
    'Uploads/pdfs/',
    'Uploads/thumbs/'
];

foreach ($directories as $dir) {
    $path = __DIR__ . '/' . $dir;
    if (is_dir($path)) {
        $perms = substr(sprintf('%o', fileperms($path)), -4);
        echo "Directory: $dir - Permissions: $perms - Writable: " . (is_writable($path) ? "YES" : "NO") . "<br>";
    } else {
        echo "Directory: $dir - NOT FOUND<br>";
    }
}

echo "<h2>Database Connection Test</h2>";

try {
    // Test database connection
    $host = 'localhost';
    $username = 'u963846660_ryvahcommerce';
    $password = '@X6js1488_SKN';
    $database = 'u963846660_ryvahcommerce';
    
    $conn = new mysqli($host, $username, $password, $database);
    
    if ($conn->connect_error) {
        echo "Database Connection: FAILED - " . $conn->connect_error . "<br>";
    } else {
        echo "Database Connection: SUCCESS<br>";
        
        // Test if products table exists
        $result = $conn->query("SHOW TABLES LIKE 'products'");
        echo "Products Table: " . ($result && $result->num_rows > 0 ? "EXISTS" : "NOT FOUND") . "<br>";
        
        $conn->close();
    }
} catch (Exception $e) {
    echo "Database Error: " . $e->getMessage() . "<br>";
}

echo "<h2>Session Test</h2>";

try {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    echo "Session Start: SUCCESS<br>";
    echo "Session ID: " . session_id() . "<br>";
} catch (Exception $e) {
    echo "Session Error: " . $e->getMessage() . "<br>";
}

echo "<h2>Include Test</h2>";

try {
    require_once __DIR__ . '/config/config.php';
    echo "Config Include: SUCCESS<br>";
} catch (Exception $e) {
    echo "Config Include Error: " . $e->getMessage() . "<br>";
}

try {
    require_once 'php/session_check.php';
    echo "Session Check Include: SUCCESS<br>";
} catch (Exception $e) {
    echo "Session Check Include Error: " . $e->getMessage() . "<br>";
}

try {
    include 'includes/csrf.php';
    echo "CSRF Include: SUCCESS<br>";
} catch (Exception $e) {
    echo "CSRF Include Error: " . $e->getMessage() . "<br>";
}

echo "<h2>Function Test</h2>";

if (function_exists('getCSRFToken')) {
    try {
        $token = getCSRFToken();
        echo "CSRF Token Generation: SUCCESS<br>";
    } catch (Exception $e) {
        echo "CSRF Token Error: " . $e->getMessage() . "<br>";
    }
} else {
    echo "CSRF Function: NOT AVAILABLE<br>";
}

echo "<h2>Error Log Check</h2>";
$error_log = ini_get('error_log');
echo "Error Log Location: " . ($error_log ? $error_log : "Not specified") . "<br>";

if (file_exists($error_log)) {
    echo "Recent errors:<br>";
    $errors = file_get_contents($error_log);
    $lines = explode("\n", $errors);
    $recent_lines = array_slice($lines, -10);
    foreach ($recent_lines as $line) {
        if (!empty(trim($line))) {
            echo htmlspecialchars($line) . "<br>";
        }
    }
}

echo "<h2>Server Information</h2>";
echo "Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "<br>";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "Script Path: " . __FILE__ . "<br>";
?>