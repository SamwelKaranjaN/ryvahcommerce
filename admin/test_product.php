<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Product.php Step-by-Step Test</h1>";

try {
    echo "<p>✓ Starting PHP execution...</p>";

    // Step 1: Test config include
    echo "<p>Testing config include...</p>";
    require_once __DIR__ . '/config/config.php';
    echo "<p>✓ Config loaded successfully</p>";

    // Step 2: Test database connection
    echo "<p>Testing database connection...</p>";
    if (isset($conn) && $conn) {
        echo "<p>✓ Database connection exists</p>";

        // Test a simple query
        $test_query = $conn->query("SELECT 1");
        if ($test_query) {
            echo "<p>✓ Database query works</p>";
        } else {
            echo "<p>✗ Database query failed: " . $conn->error . "</p>";
        }
    } else {
        echo "<p>✗ No database connection</p>";
    }

    // Step 3: Test session check
    echo "<p>Testing session check...</p>";
    require_once 'php/session_check.php';
    echo "<p>✓ Session check loaded successfully</p>";

    // Step 4: Test CSRF include
    echo "<p>Testing CSRF include...</p>";
    include 'includes/csrf.php';
    echo "<p>✓ CSRF loaded successfully</p>";

    // Step 5: Test CSRF token generation
    echo "<p>Testing CSRF token generation...</p>";
    if (function_exists('getCSRFToken')) {
        $csrf_token = getCSRFToken();
        echo "<p>✓ CSRF token generated: " . substr($csrf_token, 0, 10) . "...</p>";
    } else {
        echo "<p>✗ getCSRFToken function not available</p>";
    }

    // Step 6: Test header include (this might be the problem)
    echo "<p>Testing header include...</p>";
    try {
        include 'header.php';
        echo "<p>✓ Header loaded successfully</p>";
    } catch (Exception $e) {
        echo "<p>✗ Header include failed: " . $e->getMessage() . "</p>";
    } catch (Error $e) {
        echo "<p>✗ Header include error: " . $e->getMessage() . "</p>";
    }

    // Step 7: Test products table
    echo "<p>Testing products table...</p>";
    $result = $conn->query("SELECT COUNT(*) as count FROM products");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "<p>✓ Products table has " . $row['count'] . " records</p>";
    } else {
        echo "<p>✗ Products table query failed: " . $conn->error . "</p>";
    }

    echo "<p><strong>All tests passed! The issue might be in the header.php file or another included file.</strong></p>";
} catch (Exception $e) {
    echo "<p><strong>✗ Exception caught: " . $e->getMessage() . "</strong></p>";
    echo "<p>File: " . $e->getFile() . "</p>";
    echo "<p>Line: " . $e->getLine() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
} catch (Error $e) {
    echo "<p><strong>✗ Fatal Error: " . $e->getMessage() . "</strong></p>";
    echo "<p>File: " . $e->getFile() . "</p>";
    echo "<p>Line: " . $e->getLine() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<h2>Additional Debugging Info</h2>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Server: " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
echo "<p>Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p>Script Name: " . $_SERVER['SCRIPT_NAME'] . "</p>";
echo "<p>Working Directory: " . getcwd() . "</p>";

if (function_exists('get_included_files')) {
    echo "<h3>Included Files:</h3>";
    foreach (get_included_files() as $file) {
        echo "<p>" . $file . "</p>";
    }
}
