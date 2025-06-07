<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die('Please log in first');
}

$user_id = $_SESSION['user_id'];
$conn = getDBConnection();

// Get user's ebooks to debug thumbnail paths
$sql = "SELECT ed.*, p.name, p.thumbs, p.type 
        FROM ebook_downloads ed
        JOIN products p ON ed.product_id = p.id
        WHERE ed.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$ebooks = $result->fetch_all(MYSQLI_ASSOC);

echo "<h1>Thumbnail Debug Information</h1>";
echo "<p>Current directory: " . __DIR__ . "</p>";
echo "<p>Parent directory: " . dirname(__DIR__) . "</p>";

foreach ($ebooks as $ebook) {
    echo "<div style='border: 1px solid #ccc; margin: 10px; padding: 10px;'>";
    echo "<h3>" . htmlspecialchars($ebook['name']) . "</h3>";
    echo "<p><strong>Database thumbs value:</strong> " . htmlspecialchars($ebook['thumbs']) . "</p>";

    // Test different path constructions
    $paths_to_test = [
        '../' . $ebook['thumbs'],
        '../admin/' . $ebook['thumbs'],
        $ebook['thumbs'],
        '../../' . $ebook['thumbs']
    ];

    foreach ($paths_to_test as $i => $path) {
        $full_path = realpath($path);
        $exists = file_exists($path);
        echo "<p><strong>Path " . ($i + 1) . ":</strong> $path</p>";
        echo "<p>Full resolved path: " . ($full_path ?: 'Cannot resolve') . "</p>";
        echo "<p>Exists: " . ($exists ? 'YES' : 'NO') . "</p>";

        if ($exists) {
            echo "<img src='$path' style='max-width: 100px; max-height: 100px; border: 1px solid green;' alt='Found image'>";
            echo "<p style='color: green;'>✓ This path works!</p>";
        }
        echo "<hr>";
    }

    echo "</div>";
}

// Also list actual files in common directories
echo "<h2>Files in root directory:</h2>";
$files = glob('../*');
foreach ($files as $file) {
    if (is_dir($file)) {
        echo "<strong>DIR:</strong> " . basename($file) . "<br>";
    } else {
        echo "FILE: " . basename($file) . "<br>";
    }
}

echo "<h2>Files in admin directory:</h2>";
$admin_files = glob('../admin/*');
foreach ($admin_files as $file) {
    if (is_dir($file)) {
        echo "<strong>DIR:</strong> " . basename($file) . "<br>";
    }
}

echo "<h2>Looking for Uploads directory:</h2>";
if (is_dir('../Uploads')) {
    echo "Found ../Uploads directory<br>";
    if (is_dir('../Uploads/thumbs')) {
        echo "Found ../Uploads/thumbs directory<br>";
        $upload_thumbs = glob('../Uploads/thumbs/*');
        foreach ($upload_thumbs as $file) {
            echo "FILE: " . basename($file) . "<br>";
        }
    }
}

if (is_dir('../admin/Uploads')) {
    echo "Found ../admin/Uploads directory<br>";
    if (is_dir('../admin/Uploads/thumbs')) {
        echo "Found ../admin/Uploads/thumbs directory<br>";
        $admin_thumbs = glob('../admin/Uploads/thumbs/*');
        foreach ($admin_thumbs as $file) {
            echo "FILE: " . basename($file) . "<br>";
        }
    }
}

if (is_dir('../thumbs')) {
    echo "Found ../thumbs directory<br>";
    $thumbs = glob('../thumbs/*');
    foreach ($thumbs as $file) {
        echo "FILE: " . basename($file) . "<br>";
    }
}
