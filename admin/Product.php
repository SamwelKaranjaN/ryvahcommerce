<?php
// Include config first to set up session and database connection
require_once __DIR__ . '/config/config.php';
require_once 'php/session_check.php';

// Then include other files
include 'includes/csrf.php';
include 'header.php';

// Initialize variables
$action = isset($_GET['action']) ? $_GET['action'] : '';
$search_query = isset($_GET['query']) ? $conn->real_escape_string(trim($_GET['query'])) : '';
$message = '';
$message_type = '';
$product = [
    'id' => '', 'type' => 'paint', 'sku' => '', 'name' => '', 'author' => '',
    'description' => '', 'price' => '', 'stock_quantity' => '', 'file_size' => '',
    'filepath' => '', 'thumbs' => ''
];

// Get CSRF token
$csrf_token = getCSRFToken();

// Add this function at the top of your file
function handleDatabaseError($conn, $operation) {
    if ($conn->error) {
        return [
            'message' => "Error during $operation: " . $conn->error,
            'type' => 'error'
        ];
    }
    return [
        'message' => "$operation successful",
        'type' => 'success'
    ];
}

// Add this at the top of your file after includes
if (isset($_GET['debug'])) {
    echo "<pre>";
    echo "Session Status: " . session_status() . "\n";
    echo "Session ID: " . session_id() . "\n";
    echo "CSRF Token: " . $csrf_token . "\n";
    echo "Database Connection: " . ($conn ? "Connected" : "Not Connected") . "\n";
    echo "Session Data:\n";
    print_r($_SESSION);
    echo "</pre>";
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    if (!validateCSRFToken($_POST['csrf_token'])) {
        $message = 'Invalid CSRF token. Please refresh the page and try again.';
        $message_type = 'error';
    } else {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $type = $conn->real_escape_string($_POST['type'] ?? '');
        $sku = $conn->real_escape_string($_POST['sku'] ?? '');
        $name = $conn->real_escape_string($_POST['name'] ?? '');
        $author = $conn->real_escape_string($_POST['author'] ?? '');
        $description = $conn->real_escape_string($_POST['description'] ?? '');
        $price = isset($_POST['price']) ? (float)$_POST['price'] : 0;
        $stock_quantity = isset($_POST['stock_quantity']) ? (int)$_POST['stock_quantity'] : 0;
        $file_size = $conn->real_escape_string($_POST['file_size'] ?? '');
        $filepath = $conn->real_escape_string($_POST['existing_filepath'] ?? '');
        $thumbs = $conn->real_escape_string($_POST['existing_thumbs'] ?? '');

        // Enhanced server-side validation
        $errors = [];
        if (empty($name)) $errors[] = 'Product name is required';
        if (empty($author)) $errors[] = 'Author name is required';
        if (empty($description)) $errors[] = 'Description is required';
        if ($price <= 0) $errors[] = 'Price must be greater than 0';
        if ($stock_quantity < 0) $errors[] = 'Stock quantity cannot be negative';
        if (empty($sku)) $errors[] = 'SKU is required';

        // Check if SKU is unique (except for current product when editing)
        $sku_check = $conn->query("SELECT id FROM products WHERE sku = '$sku' AND id != $id");
        if ($sku_check && $sku_check->num_rows > 0) {
            $errors[] = 'SKU must be unique';
        }

        if (empty($errors)) {
            // Handle file uploads
            if (isset($_FILES['filepath']['tmp_name']) && $_FILES['filepath']['tmp_name']) {
                if ($_FILES['filepath']['size'] > 100 * 1024 * 1024) {
                    $errors[] = 'PDF too large (max 100MB)';
                } else {
                    $filename = basename($_FILES['filepath']['name']);
                    $upload_dir = __DIR__ . '/../Uploads/pdfs/';
                    
                    // Create directory if it doesn't exist
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    $filepath = 'Uploads/pdfs/' . $filename;
                    $full_path = $upload_dir . $filename;
                    
                    if (!move_uploaded_file($_FILES['filepath']['tmp_name'], $full_path)) {
                        $errors[] = 'Failed to upload PDF';
                    } else {
                        // Update file size
                        $file_size = formatFileSize($_FILES['filepath']['size']);
                    }
                }
            }

            if (isset($_FILES['thumbs']['tmp_name']) && $_FILES['thumbs']['tmp_name']) {
                if ($_FILES['thumbs']['size'] > 20 * 1024 * 1024) {
                    $errors[] = 'Thumbnail too large (max 20MB)';
                } else {
                    $filename = basename($_FILES['thumbs']['name']);
                    $upload_dir = __DIR__ . '/../Uploads/thumbs/';
                    
                    // Create directory if it doesn't exist
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    $thumbs = 'Uploads/thumbs/' . $filename;
                    $full_path = $upload_dir . $filename;
                    
                    if (!move_uploaded_file($_FILES['thumbs']['tmp_name'], $full_path)) {
                        $errors[] = 'Failed to upload thumbnail';
                    }
                }
            }

            // Save or update product if no errors
            if (empty($errors)) {
                if ($id) {
                    // Update existing product
                    $sql = "UPDATE products SET 
                            type = '$type',
                            sku = '$sku',
                            name = '$name',
                            author = '$author',
                            description = '$description',
                            price = $price,
                            stock_quantity = $stock_quantity,
                            file_size = '$file_size'";
                    
                    // Only update file paths if new files were uploaded
                    if (isset($_FILES['filepath']['tmp_name']) && $_FILES['filepath']['tmp_name']) {
                        $sql .= ", filepath = '$filepath'";
                    }
                    if (isset($_FILES['thumbs']['tmp_name']) && $_FILES['thumbs']['tmp_name']) {
                        $sql .= ", thumbs = '$thumbs'";
                    }
                    
                    $sql .= " WHERE id = $id";
                    
                    if ($conn->query($sql)) {
                        $_SESSION['message'] = 'Product updated successfully';
                        $_SESSION['message_type'] = 'success';
                        echo "<script>window.location.href = '" . $_SERVER['PHP_SELF'] . "';</script>";
                        exit;
                    } else {
                        $message = 'Error updating product: ' . $conn->error;
                        $message_type = 'error';
                    }
                } else {
                    // Insert new product
                    $sql = "INSERT INTO products 
                            (type, sku, name, author, description, price, stock_quantity, filepath, thumbs, file_size) 
                            VALUES ('$type', '$sku', '$name', '$author', '$description', $price, 
                                    $stock_quantity, '$filepath', '$thumbs', '$file_size')";
                    
                    if ($conn->query($sql)) {
                        $_SESSION['message'] = 'Product added successfully';
                        $_SESSION['message_type'] = 'success';
                        echo "<script>window.location.href = '" . $_SERVER['PHP_SELF'] . "';</script>";
                        exit;
                    } else {
                        $message = 'Error adding product: ' . $conn->error;
                        $message_type = 'error';
                    }
                }
            } else {
                $message = implode('<br>', $errors);
                $message_type = 'error';
            }
        } else {
            $message = implode('<br>', $errors);
            $message_type = 'error';
        }

        // Populate form with submitted data if there's an error
        if ($message_type === 'error') {
            $product = [
                'id' => $id, 'type' => $type, 'sku' => $sku, 'name' => $name, 'author' => $author,
                'description' => $description, 'price' => $price, 'stock_quantity' => $stock_quantity,
                'file_size' => $file_size, 'filepath' => $filepath, 'thumbs' => $thumbs
            ];
        }
    }
}

// Get message from session if exists
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'];
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}

// Helper function to format file size
function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

// Handle delete action
if ($action === 'delete' && isset($_GET['id'])) {
    if (!validateCSRFToken($_GET['csrf_token'])) {
        $message = 'Invalid CSRF token. Please refresh the page and try again.';
        $message_type = 'error';
    } else {
        $id = (int)$_GET['id'];
        
        // Get product details before deletion
        $result = $conn->query("SELECT filepath, thumbs FROM products WHERE id = $id");
        if ($result && $row = $result->fetch_assoc()) {
            // Delete files if they exist
            if (!empty($row['filepath']) && file_exists(__DIR__ . '/../' . $row['filepath'])) {
                unlink(__DIR__ . '/../' . $row['filepath']);
            }
            if (!empty($row['thumbs']) && file_exists(__DIR__ . '/../' . $row['thumbs'])) {
                unlink(__DIR__ . '/../' . $row['thumbs']);
            }
        }
        
        // Delete from database
        $sql = "DELETE FROM products WHERE id = $id";
        if ($conn->query($sql)) {
            $_SESSION['message'] = 'Product deleted successfully';
            $_SESSION['message_type'] = 'success';
            echo "<script>window.location.href = '" . $_SERVER['PHP_SELF'] . "';</script>";
            exit;
        } else {
            $message = 'Error deleting product: ' . $conn->error;
            $message_type = 'error';
        }
    }
}

// Handle edit action
if ($action === 'edit' && isset($_GET['id'])) {
    if (!validateCSRFToken($_GET['csrf_token'])) {
        $message = 'Invalid CSRF token. Please refresh the page and try again.';
        $message_type = 'error';
    } else {
    $id = (int)$_GET['id'];
        $result = $conn->query("SELECT * FROM products WHERE id = $id");
    if ($result && $row = $result->fetch_assoc()) {
        $product = $row;
    } else {
        $message = 'Error loading product: ' . $conn->error;
        $message_type = 'error';
        }
    }
}

// Load products (with search if applicable)
$products = [];
$sql = $search_query 
    ? "SELECT * FROM products WHERE name LIKE '%$search_query%' OR sku LIKE '%$search_query%' OR author LIKE '%$search_query%' ORDER BY id DESC"
    : "SELECT * FROM products ORDER BY id DESC";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
} else {
    $message = 'Error loading products: ' . $conn->error;
    $message_type = 'error';
}
?>

<!-- Add this in the head section of your header.php -->
    <style>
    /* Enhanced Responsive Styles */
    :root {
        /* Enhanced Color Palette */
        --primary-color: #4f46e5;
        --primary-hover: #4338ca;
        --secondary-color: #0ea5e9;
        --success-color: #10b981;
        --danger-color: #ef4444;
        --warning-color: #f59e0b;
        --text-primary: #1f2937;
        --text-secondary: #4b5563;
        --text-light: #9ca3af;
        --bg-primary: #ffffff;
        --bg-secondary: #f3f4f6;
        --bg-tertiary: #f9fafb;
        --border-color: #e5e7eb;
        
        /* Enhanced Shadows */
        --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
        --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
        --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
        
        /* Transitions */
        --transition-fast: 150ms cubic-bezier(0.4, 0, 0.2, 1);
        --transition-normal: 300ms cubic-bezier(0.4, 0, 0.2, 1);
        --transition-slow: 500ms cubic-bezier(0.4, 0, 0.2, 1);
        
        /* Spacing */
        --spacing-xs: 0.25rem;
        --spacing-sm: 0.5rem;
        --spacing-md: 1rem;
        --spacing-lg: 1.5rem;
        --spacing-xl: 2rem;
        --spacing-2xl: 3rem;

        /* Additional Modern UI Enhancements */
        /* Additional Colors */
        --gradient-primary: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        --gradient-success: linear-gradient(135deg, #10b981 0%, #059669 100%);
        --gradient-warning: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        --gradient-danger: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        
        /* Additional Shadows */
        --shadow-inner: inset 0 2px 4px 0 rgb(0 0 0 / 0.05);
        --shadow-ring: 0 0 0 3px rgba(79, 70, 229, 0.2);
        
        /* Additional Transitions */
        --transition-bounce: cubic-bezier(0.68, -0.55, 0.265, 1.55);
    }

    /* Base Styles */
    .product-container {
        padding: var(--spacing-xl);
        margin-left: 250px;
        margin-top: 50px;
        min-height: calc(100vh - 60px);
        background: var(--bg-tertiary);
        transition: var(--transition-normal);
    }

    .product-container.collapsed {
        margin-left: 60px;
    }

    /* Enhanced Header Section */
    .product-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: var(--spacing-2xl);
        flex-wrap: wrap;
        gap: var(--spacing-lg);
        background: var(--bg-primary);
        padding: var(--spacing-lg);
        border-radius: 1rem;
        box-shadow: var(--shadow-md);
    }

    .product-title {
        font-size: 1.875rem;
        font-weight: 700;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: var(--spacing-sm);
    }

    .product-title i {
        color: var(--primary-color);
    }

    /* Enhanced Search Bar */
    .search-container {
        position: relative;
        max-width: 400px;
        width: 100%;
    }

    .search-bar {
        position: relative;
        width: 100%;
    }

    .search-bar input {
        width: 100%;
        padding: 0.875rem 1rem 0.875rem 3rem;
        border: 2px solid var(--border-color);
        border-radius: 9999px;
        font-size: 0.875rem;
        transition: var(--transition-normal);
        background: var(--bg-primary);
    }

    .search-bar i {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-light);
        transition: var(--transition-normal);
    }

    .search-bar input:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
    }

    .search-bar input:focus + i {
        color: var(--primary-color);
    }

    /* Enhanced Action Buttons */
    .action-buttons {
        display: flex;
        gap: var(--spacing-sm);
    }

    .btn {
        padding: 0.75rem 1.5rem;
        border: none;
        border-radius: 9999px;
        font-size: 0.875rem;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: var(--spacing-sm);
        cursor: pointer;
        transition: var(--transition-normal);
        white-space: nowrap;
    }

    .btn-primary {
        background: var(--primary-color);
        color: white;
    }

    .btn-primary:hover {
        background: var(--primary-hover);
        transform: translateY(-1px);
        box-shadow: var(--shadow-md);
    }

    .btn-secondary {
        background: var(--secondary-color);
        color: white;
    }

    .btn-secondary:hover {
        background: #0284c7;
        transform: translateY(-1px);
        box-shadow: var(--shadow-md);
    }

    /* Enhanced Product Grid */
    .product-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: var(--spacing-xl);
        margin-top: var(--spacing-xl);
    }

    .product-card {
        background: var(--bg-primary);
        border-radius: 1rem;
        overflow: hidden;
        box-shadow: var(--shadow-sm);
        transition: var(--transition-normal);
        position: relative;
        display: flex;
        flex-direction: column;
    }

    .product-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-lg);
    }

    .product-image-container {
        position: relative;
        width: 100%;
        height: 200px;
        overflow: hidden;
        background: var(--bg-secondary);
    }

    .product-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: var(--transition-normal);
    }

    .product-card:hover .product-image {
        transform: scale(1.05);
    }

    .product-badge {
        position: absolute;
        top: var(--spacing-sm);
        right: var(--spacing-sm);
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 500;
        background: var(--primary-color);
        color: white;
        z-index: 1;
    }

    .product-info {
        padding: var(--spacing-lg);
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .product-name {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: var(--spacing-xs);
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .product-author {
        color: var(--text-secondary);
        font-size: 0.875rem;
        margin-bottom: var(--spacing-sm);
    }

    .product-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: auto;
        padding-top: var(--spacing-md);
        border-top: 1px solid var(--border-color);
    }

    .product-price {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--success-color);
    }

    .product-stock {
        font-size: 0.875rem;
        color: var(--text-secondary);
        display: flex;
        align-items: center;
        gap: var(--spacing-xs);
    }

    .product-stock i {
        color: var(--success-color);
    }

    .product-actions {
        display: flex;
        gap: var(--spacing-sm);
        margin-top: var(--spacing-md);
    }

    .action-btn {
        flex: 1;
        padding: 0.75rem;
        border: none;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        transition: var(--transition-normal);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: var(--spacing-xs);
    }

    .edit-btn {
        background: var(--warning-color);
        color: white;
    }

    .delete-btn {
        background: var(--danger-color);
        color: white;
    }

    .action-btn:hover {
        opacity: 0.9;
        transform: translateY(-1px);
    }

    /* Enhanced Modal Styles */
    .modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1000;
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(4px);
    }

    .modal-overlay.active {
        display: flex;
        animation: fadeIn 0.3s ease-out;
    }

    .modal-content {
        background: var(--bg-primary);
        border-radius: 1rem;
        padding: var(--spacing-xl);
        box-shadow: var(--shadow-xl);
        max-width: 600px;
        width: 90%;
        max-height: 85vh;
        overflow-y: auto;
        position: relative;
        animation: slideUp 0.3s ease-out;
    }

    .modal-close {
        position: absolute;
        top: 1rem;
        right: 1rem;
        background: none;
        border: none;
        font-size: 1.5rem;
        color: var(--text-secondary);
        cursor: pointer;
        padding: 0.5rem;
        line-height: 1;
        transition: var(--transition-normal);
        z-index: 1;
    }

    .modal-close:hover {
        color: var(--danger-color);
        transform: rotate(90deg);
    }

    /* Enhanced Form Styles */
    .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: var(--spacing-md);
    }

    .form-group {
        margin-bottom: var(--spacing-md);
    }

    .form-group.full-width {
        grid-column: 1 / -1;
    }

    .form-label {
        display: block;
        margin-bottom: var(--spacing-xs);
        font-weight: 500;
        color: var(--text-primary);
        font-size: 0.875rem;
    }

    .form-input {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 2px solid var(--border-color);
        border-radius: 0.5rem;
        font-size: 0.875rem;
        transition: var(--transition-normal);
        background: var(--bg-primary);
    }

    .form-input:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
    }

    .form-input:disabled {
        background-color: var(--bg-secondary);
        cursor: not-allowed;
    }

    .file-input-wrapper {
        position: relative;
        display: flex;
        align-items: center;
        gap: var(--spacing-sm);
        flex-wrap: wrap;
    }

    .file-input-wrapper input[type="file"] {
        flex: 1;
        min-width: 200px;
    }

    .file-preview {
        max-width: 100px;
        max-height: 100px;
        object-fit: cover;
        border-radius: 0.5rem;
        display: none;
        box-shadow: var(--shadow-sm);
    }

    .file-preview.visible {
        display: block;
    }

    /* Enhanced Message Styles */
    .message {
        position: fixed;
        top: 50px;
        right: 20px;
        padding: var(--spacing-lg);
        border-radius: 0.5rem;
        font-weight: 500;
        animation: slideIn 0.3s ease-out;
        display: flex;
        align-items: center;
        gap: var(--spacing-sm);
        z-index: 1000;
        max-width: 400px;
        box-shadow: var(--shadow-lg);
    }

    .message i {
        font-size: 1.25rem;
    }

    .success {
        background: #dcfce7;
        color: #166534;
        border: 1px solid #86efac;
    }

    .error {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fca5a5;
    }

    /* Loading State */
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        opacity: 0;
        visibility: hidden;
        transition: var(--transition-normal);
        backdrop-filter: blur(4px);
    }

    .loading-overlay.active {
        opacity: 1;
        visibility: visible;
    }

    .loading-spinner {
        width: 40px;
        height: 40px;
        border: 3px solid var(--border-color);
        border-top-color: var(--primary-color);
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    /* Animations */
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }

    @keyframes slideUp {
        from {
            transform: translateY(20px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }

    /* Responsive Design */
    @media (max-width: 1280px) {
        .product-grid {
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        }
    }

    @media (max-width: 1024px) {
        .product-container {
            margin-left: 60px;
            padding: var(--spacing-lg);
        }

        .form-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .product-container {
            margin-left: 0;
            padding: var(--spacing-md);
        }

        .product-header {
            flex-direction: column;
            align-items: stretch;
            gap: var(--spacing-md);
        }

        .search-container {
            max-width: 100%;
        }

        .action-buttons {
            flex-direction: column;
        }

        .btn {
            width: 100%;
            justify-content: center;
        }

        .product-grid {
            grid-template-columns: 1fr;
        }

        .modal-content {
            width: 95%;
            padding: var(--spacing-lg);
        }
    }

    @media (max-width: 480px) {
        .product-meta {
            flex-direction: column;
            align-items: flex-start;
            gap: var(--spacing-sm);
        }

        .product-actions {
            flex-direction: column;
        }

        .action-btn {
            width: 100%;
        }

        .message {
            width: calc(100% - 40px);
            right: 20px;
            left: 20px;
        }
    }

    /* Enhanced Product Container */
    .product-container {
        background: linear-gradient(to bottom right, var(--bg-tertiary), var(--bg-secondary));
        min-height: 100vh;
    }

    /* Enhanced Header */
    .product-header {
        background: var(--bg-primary);
        border-radius: 1.5rem;
        box-shadow: var(--shadow-lg);
        padding: var(--spacing-xl);
        margin-bottom: var(--spacing-2xl);
        border: 1px solid rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
    }

    .product-title {
        background: var(--gradient-primary);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        font-size: 2.25rem;
    }

    /* Enhanced Search Bar */
    .search-bar input {
        background: var(--bg-secondary);
        border: 2px solid transparent;
        box-shadow: var(--shadow-inner);
        transition: all 0.3s var(--transition-bounce);
    }

    .search-bar input:focus {
        background: var(--bg-primary);
        border-color: var(--primary-color);
        box-shadow: var(--shadow-ring);
        transform: translateY(-1px);
    }

    /* Enhanced Buttons */
    .btn {
        position: relative;
        overflow: hidden;
        transition: all 0.3s var(--transition-bounce);
    }

    .btn::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        transform: translate(-50%, -50%);
        transition: width 0.6s, height 0.6s;
    }

    .btn:hover::after {
        width: 300px;
        height: 300px;
    }

    .btn-primary {
        background: var(--gradient-primary);
    }

    .btn-secondary {
        background: var(--gradient-success);
    }

    /* Enhanced Product Cards */
    .product-card {
        background: var(--bg-primary);
        border: 1px solid rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        transition: all 0.3s var(--transition-bounce);
    }

    .product-card:hover {
        transform: translateY(-8px) scale(1.02);
        box-shadow: var(--shadow-xl);
    }

    .product-image-container {
        position: relative;
        overflow: hidden;
        border-radius: 1rem 1rem 0 0;
    }

    .product-image-container::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(to bottom, transparent 50%, rgba(0, 0, 0, 0.1));
        pointer-events: none;
    }

    .product-badge {
        background: var(--gradient-primary);
        box-shadow: var(--shadow-md);
        transform: translateY(0);
        transition: transform 0.3s var(--transition-bounce);
    }

    .product-card:hover .product-badge {
        transform: translateY(-2px);
    }

    /* Enhanced Form Elements */
    .form-input {
        background: var(--bg-secondary);
        border: 2px solid transparent;
        box-shadow: var(--shadow-inner);
        transition: all 0.3s var(--transition-bounce);
    }

    .form-input:focus {
        background: var(--bg-primary);
        border-color: var(--primary-color);
        box-shadow: var(--shadow-ring);
        transform: translateY(-1px);
    }

    .form-label {
        position: relative;
        display: inline-block;
    }

    .form-label::after {
        content: '*';
        color: var(--danger-color);
        margin-left: 4px;
        opacity: 0;
        transition: opacity 0.3s;
    }

    .form-label.required::after {
        opacity: 1;
    }

    /* Enhanced Modal */
    .modal-overlay {
        backdrop-filter: blur(8px);
    }

    .modal-content {
        background: var(--bg-primary);
        border: 1px solid rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        animation: modalSlideUp 0.4s var(--transition-bounce);
    }

    @keyframes modalSlideUp {
        from {
            transform: translateY(40px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    /* Enhanced Message Notifications */
    .message {
        background: var(--bg-primary);
        border: 1px solid rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        box-shadow: var(--shadow-xl);
        animation: messageSlideIn 0.4s var(--transition-bounce);
    }

    .success {
        background: var(--gradient-success);
        color: white;
    }

    .error {
        background: var(--gradient-danger);
        color: white;
    }

    @keyframes messageSlideIn {
        from {
            transform: translateX(100%) scale(0.8);
            opacity: 0;
        }
        to {
            transform: translateX(0) scale(1);
            opacity: 1;
        }
    }

    /* Enhanced Loading State */
    .loading-overlay {
        backdrop-filter: blur(8px);
    }

    .loading-spinner {
        border: 4px solid rgba(255, 255, 255, 0.1);
        border-top-color: var(--primary-color);
        box-shadow: var(--shadow-lg);
    }

    /* Enhanced File Upload */
    .file-input-wrapper {
        position: relative;
        border: 2px dashed var(--border-color);
        border-radius: 1rem;
        padding: var(--spacing-md);
        transition: all 0.3s var(--transition-bounce);
    }

    .file-input-wrapper:hover {
        border-color: var(--primary-color);
        background: var(--bg-secondary);
    }

    .file-preview {
        border-radius: 0.75rem;
        box-shadow: var(--shadow-md);
        transition: all 0.3s var(--transition-bounce);
    }

    .file-preview:hover {
        transform: scale(1.05);
        box-shadow: var(--shadow-lg);
    }

    /* Enhanced Action Buttons */
    .action-btn {
        position: relative;
        overflow: hidden;
        transition: all 0.3s var(--transition-bounce);
    }

    .action-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.1);
        transform: translateX(-100%);
        transition: transform 0.3s;
    }

    .action-btn:hover::before {
        transform: translateX(0);
    }

    .edit-btn {
        background: var(--gradient-warning);
    }

    .delete-btn {
        background: var(--gradient-danger);
    }

    /* Enhanced Responsive Design */
    @media (max-width: 768px) {
        .product-header {
            padding: var(--spacing-lg);
            border-radius: 1rem;
        }

        .product-title {
            font-size: 1.75rem;
        }

        .modal-content {
            width: 95%;
            margin: var(--spacing-md);
            border-radius: 1rem;
        }

        .file-input-wrapper {
            padding: var(--spacing-sm);
        }
    }

    @media (max-width: 480px) {
        .product-header {
            padding: var(--spacing-md);
        }

        .btn {
            padding: 0.875rem 1.25rem;
        }

        .message {
            width: calc(100% - 32px);
            margin: 0 16px;
            border-radius: 0.75rem;
        }
    }

    /* Enhanced Scrollbar */
    ::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }

    ::-webkit-scrollbar-track {
        background: var(--bg-secondary);
        border-radius: 4px;
    }

    ::-webkit-scrollbar-thumb {
        background: var(--primary-color);
        border-radius: 4px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: var(--primary-hover);
    }
    </style>

<div class="product-container" id="main-content">
    <?php if ($message): ?>
        <div class="message <?php echo $message_type; ?>" id="statusMessage">
            <i class="fas <?php echo $message_type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <div class="product-header">
        <h1 class="product-title">
            <i class="fas fa-box"></i>
            Product Management
        </h1>
        <div class="search-container">
            <div class="search-bar">
                <input type="text" id="search" placeholder="Search products..." value="<?php echo htmlspecialchars($search_query); ?>">
                <i class="fas fa-search"></i>
            </div>
        </div>
        <div class="action-buttons">
            <button class="btn btn-primary" onclick="toggleForm()">
                <i class="fas fa-plus"></i>
                Add Product
            </button>
            <button class="btn btn-secondary" onclick="exportProducts()">
                <i class="fas fa-download"></i>
                Export
            </button>
        </div>
    </div>

    <!-- Add this modal structure after your product-header div -->
    <div class="modal-overlay" id="productModal">
        <div class="modal-content">
            <button class="modal-close" onclick="closeModal()">
                <i class="fas fa-times"></i>
            </button>
            <div class="product-form" id="productForm">
                <form method="POST" enctype="multipart/form-data" action="<?php echo $_SERVER['PHP_SELF']; ?>" onsubmit="return validateForm(this)">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                    <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                    <input type="hidden" name="existing_filepath" value="<?php echo htmlspecialchars($product['filepath']); ?>">
                    <input type="hidden" name="existing_thumbs" value="<?php echo htmlspecialchars($product['thumbs']); ?>">
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label required">Product Type</label>
                            <select name="type" class="form-input" required>
                                <option value="paint" <?php echo $product['type'] === 'paint' ? 'selected' : ''; ?>>Paint</option>
                                <option value="ebook" <?php echo $product['type'] === 'ebook' ? 'selected' : ''; ?>>eBook</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label required">SKU</label>
                            <input type="text" name="sku" class="form-input" value="<?php echo htmlspecialchars($product['sku']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label required">Name</label>
                            <input type="text" name="name" class="form-input" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label required">Author</label>
                            <input type="text" name="author" class="form-input" value="<?php echo htmlspecialchars($product['author']); ?>" required>
                        </div>

                        <div class="form-group full-width">
                            <label class="form-label required">Description</label>
                            <textarea name="description" class="form-input form-textarea" required><?php echo htmlspecialchars($product['description']); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label class="form-label required">Price</label>
                            <input type="number" name="price" class="form-input" step="0.01" value="<?php echo htmlspecialchars($product['price']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label required">Stock Quantity</label>
                            <input type="number" name="stock_quantity" class="form-input" value="<?php echo htmlspecialchars($product['stock_quantity']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">File Size</label>
                            <input type="text" name="file_size" class="form-input" value="<?php echo htmlspecialchars($product['file_size']); ?>" readonly>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Product File (PDF only)</label>
                            <div class="file-input-wrapper">
                                <input type="file" name="filepath" class="form-input" accept=".pdf" onchange="handleFileUpload(this, 'pdf')">
                                <?php if ($product['filepath']): ?>
                                    <a href="<?php echo htmlspecialchars($product['filepath']); ?>" target="_blank" class="text-sm text-blue-500">View PDF</a>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Thumbnail (Image only)</label>
                            <div class="file-input-wrapper">
                                <input type="file" name="thumbs" class="form-input" accept="image/*" onchange="handleFileUpload(this, 'image')">
                                <?php if ($product['thumbs']): ?>
                                    <img src="<?php echo htmlspecialchars($product['thumbs']); ?>" alt="Current thumbnail" class="file-preview visible">
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                        <button type="submit" name="submit" class="btn btn-primary" id="submitButton">
                            <i class="fas fa-save"></i> Save Product
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="product-grid">
        <?php foreach ($products as $product): ?>
            <div class="product-card" data-id="<?php echo $product['id']; ?>">
                <div class="product-image-container">
                    <?php if ($product['thumbs']): ?>
                        <img src="<?php echo htmlspecialchars($product['thumbs']); ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>" 
                             class="product-image">
                    <?php else: ?>
                        <div class="product-image bg-gray-100 flex items-center justify-center">
                            <i class="fas fa-image text-gray-400 text-4xl"></i>
                        </div>
                    <?php endif; ?>
                    <span class="product-badge"><?php echo ucfirst($product['type']); ?></span>
                </div>
                
                <div class="product-info">
                    <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                    <p class="product-author"><?php echo htmlspecialchars($product['author']); ?></p>
                    
                    <div class="product-meta">
                        <span class="product-price">$<?php echo number_format($product['price'], 2); ?></span>
                        <span class="product-stock">
                            <i class="fas fa-box"></i>
                            Stock: <?php echo $product['stock_quantity']; ?>
                        </span>
                    </div>

                    <div class="product-actions">
                        <button onclick="editProduct(<?php echo htmlspecialchars(json_encode($product)); ?>)" 
                               class="action-btn edit-btn">
                            <i class="fas fa-edit"></i>
                            Edit
                        </button>
                        <a href="?action=delete&id=<?php echo $product['id']; ?>&csrf_token=<?php echo htmlspecialchars($csrf_token); ?>" 
                           class="action-btn delete-btn"
                           onclick="return confirm('Are you sure you want to delete this product?')">
                            <i class="fas fa-trash"></i>
                            Delete
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="loading-overlay" id="loadingOverlay">
    <div class="loading-spinner"></div>
</div>

    <script>
// Enhanced JavaScript functionality
document.addEventListener('DOMContentLoaded', function() {
    // Auto-dismiss success messages after 5 seconds
    const statusMessage = document.getElementById('statusMessage');
    if (statusMessage && statusMessage.classList.contains('success')) {
        setTimeout(() => {
            statusMessage.style.opacity = '0';
            statusMessage.style.transform = 'translateY(-10px)';
            setTimeout(() => statusMessage.remove(), 300);
        }, 5000);
    }

    // Initialize search with debounce
    const searchInput = document.getElementById('search');
    let searchTimeout;

    searchInput.addEventListener('input', function(e) {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            const query = e.target.value.toLowerCase();
            filterProducts(query);
        }, 300);
    });

    // Initialize tooltips
    const tooltips = document.querySelectorAll('[data-tooltip]');
    tooltips.forEach(tooltip => {
        new Tooltip(tooltip);
    });

    // Handle sidenav collapse
    const sidenav = document.getElementById('sidenav');
    const mainContent = document.getElementById('main-content');
    
    if (sidenav && sidenav.classList.contains('collapsed')) {
        mainContent.classList.add('collapsed');
    }
});

function filterProducts(query) {
    const products = document.querySelectorAll('.product-card');
    const loadingOverlay = document.getElementById('loadingOverlay');
    
    loadingOverlay.classList.add('active');
    
    setTimeout(() => {
        products.forEach(product => {
            const name = product.querySelector('.product-name').textContent.toLowerCase();
            const author = product.querySelector('.product-author').textContent.toLowerCase();
            const type = product.querySelector('.product-badge').textContent.toLowerCase();
            const sku = product.querySelector('.product-sku')?.textContent.toLowerCase() || '';
            
            if (name.includes(query) || author.includes(query) || type.includes(query) || sku.includes(query)) {
                product.style.display = 'block';
            } else {
                product.style.display = 'none';
            }
        });
        
        loadingOverlay.classList.remove('active');
    }, 300);
}

function toggleForm() {
    const modal = document.getElementById('productModal');
    modal.classList.add('active');
    // Clear form when opening
    clearForm();
}

function closeModal() {
    const modal = document.getElementById('productModal');
    const submitButton = document.getElementById('submitButton');
    
    // Reset button text and icon
    submitButton.innerHTML = '<i class="fas fa-save"></i> Save Product';
    
    // Clear form
    clearForm();
    
    // Hide modal
    modal.classList.remove('active');
}

function clearForm() {
    const form = document.querySelector('#productForm form');
    if (form) {
        // Clear all input fields
        form.querySelectorAll('input[type="text"], input[type="number"], textarea, select').forEach(input => {
            input.value = '';
        });
        
        // Reset file inputs
        form.querySelectorAll('input[type="file"]').forEach(input => {
            input.value = '';
        });
        
        // Reset hidden fields
        form.querySelector('input[name="id"]').value = '';
        form.querySelector('input[name="existing_filepath"]').value = '';
        form.querySelector('input[name="existing_thumbs"]').value = '';
        
        // Remove previews
        const previews = form.querySelectorAll('.file-preview');
        previews.forEach(preview => preview.remove());
        
        // Remove PDF links
        const pdfLinks = form.querySelectorAll('a[href*=".pdf"]');
        pdfLinks.forEach(link => link.remove());
        
        // Reset submit button
    const submitButton = document.getElementById('submitButton');
        submitButton.innerHTML = '<i class="fas fa-save"></i> Save Product';
    }
}

function validateForm(form) {
    const errors = [];
    const name = form.querySelector('input[name="name"]').value.trim();
    const author = form.querySelector('input[name="author"]').value.trim();
    const description = form.querySelector('textarea[name="description"]').value.trim();
    const price = parseFloat(form.querySelector('input[name="price"]').value);
    const stockQuantity = parseInt(form.querySelector('input[name="stock_quantity"]').value);
    const sku = form.querySelector('input[name="sku"]').value.trim();
    const fileInput = form.querySelector('input[name="filepath"]');
    const thumbInput = form.querySelector('input[name="thumbs"]');
    
    // Basic validation
    if (!name) errors.push('Product name is required');
    if (!author) errors.push('Author name is required');
    if (!description) errors.push('Description is required');
    if (!sku) errors.push('SKU is required');
    if (isNaN(price) || price <= 0) errors.push('Price must be greater than 0');
    if (isNaN(stockQuantity) || stockQuantity < 0) errors.push('Stock quantity cannot be negative');
    
    // File validation
    if (fileInput.files.length > 0) {
        const file = fileInput.files[0];
        if (!file.type.includes('pdf')) {
            errors.push('Product file must be a PDF');
        }
        if (file.size > 100 * 1024 * 1024) {
            errors.push('PDF file size must be less than 100MB');
        }
    }
    
    if (thumbInput.files.length > 0) {
        const file = thumbInput.files[0];
        if (!file.type.includes('image')) {
            errors.push('Thumbnail must be an image');
        }
        if (file.size > 20 * 1024 * 1024) {
            errors.push('Thumbnail size must be less than 20MB');
        }
    }
    
    if (errors.length > 0) {
        alert(errors.join('\n'));
        return false;
    }
    
    return true;
}

function editProduct(product) {
    const modal = document.getElementById('productModal');
    const form = document.querySelector('#productForm form');
    const submitButton = document.getElementById('submitButton');
    
    // Change button text and icon for edit mode
    submitButton.innerHTML = '<i class="fas fa-sync"></i> Update Product';
    
    // Populate form fields
    form.querySelector('input[name="id"]').value = product.id;
    form.querySelector('select[name="type"]').value = product.type;
    form.querySelector('input[name="sku"]').value = product.sku;
    form.querySelector('input[name="name"]').value = product.name;
    form.querySelector('input[name="author"]').value = product.author;
    form.querySelector('textarea[name="description"]').value = product.description;
    form.querySelector('input[name="price"]').value = product.price;
    form.querySelector('input[name="stock_quantity"]').value = product.stock_quantity;
    form.querySelector('input[name="file_size"]').value = product.file_size;
    form.querySelector('input[name="existing_filepath"]').value = product.filepath;
    form.querySelector('input[name="existing_thumbs"]').value = product.thumbs;
    
    // Show existing thumbnail if available
    const thumbPreview = form.querySelector('.file-preview');
    if (product.thumbs) {
        if (!thumbPreview) {
            const newPreview = document.createElement('img');
            newPreview.className = 'file-preview visible';
            newPreview.src = product.thumbs;
            newPreview.style.maxWidth = '100px';
            newPreview.style.maxHeight = '100px';
            newPreview.style.objectFit = 'cover';
            newPreview.style.borderRadius = '4px';
            newPreview.style.marginTop = '10px';
            form.querySelector('input[name="thumbs"]').parentElement.appendChild(newPreview);
        } else {
            thumbPreview.src = product.thumbs;
            thumbPreview.classList.add('visible');
        }
    } else if (thumbPreview) {
        thumbPreview.classList.remove('visible');
    }
    
    // Show existing PDF link if available
    const pdfLink = form.querySelector('a[href*=".pdf"]');
    if (product.filepath) {
        if (!pdfLink) {
            const newLink = document.createElement('a');
            newLink.href = product.filepath;
            newLink.target = '_blank';
            newLink.className = 'text-sm text-blue-500';
            newLink.textContent = 'View PDF';
            form.querySelector('input[name="filepath"]').parentElement.appendChild(newLink);
        } else {
            pdfLink.href = product.filepath;
        }
    } else if (pdfLink) {
        pdfLink.remove();
    }
    
    // Show modal
    modal.classList.add('active');
}

function handleFileUpload(input, type) {
    const file = input.files[0];
    if (!file) return;

    // Validate file type
    if (type === 'pdf' && !file.type.includes('pdf')) {
        alert('Please upload a PDF file');
        input.value = '';
        return;
    }
    if (type === 'image' && !file.type.includes('image')) {
        alert('Please upload an image file');
        input.value = '';
        return;
    }

    // Update file size if it's a PDF
    if (type === 'pdf') {
        const fileSize = formatFileSize(file.size);
        document.querySelector('input[name="file_size"]').value = fileSize;
    }

    // Show preview for images
    if (type === 'image') {
        const preview = input.parentElement.querySelector('.file-preview') || document.createElement('img');
        preview.className = 'file-preview visible';
        preview.src = URL.createObjectURL(file);
        preview.style.maxWidth = '100px';
        preview.style.maxHeight = '100px';
        preview.style.objectFit = 'cover';
        preview.style.borderRadius = '4px';
        preview.style.marginTop = '10px';
        
        if (!input.parentElement.querySelector('.file-preview')) {
            input.parentElement.appendChild(preview);
        }
    }
}

function formatFileSize(bytes) {
    if (bytes >= 1073741824) {
        return number_format(bytes / 1073741824, 2) + ' GB';
    } else if (bytes >= 1048576) {
        return number_format(bytes / 1048576, 2) + ' MB';
    } else if (bytes >= 1024) {
        return number_format(bytes / 1024, 2) + ' KB';
    } else {
        return bytes + ' bytes';
    }
}

function number_format(number, decimals) {
    return parseFloat(number).toFixed(decimals);
}

// Simple Tooltip class
class Tooltip {
    constructor(element) {
        this.element = element;
        this.tooltip = null;
        this.init();
    }

    init() {
        this.element.addEventListener('mouseenter', this.show.bind(this));
        this.element.addEventListener('mouseleave', this.hide.bind(this));
    }

    show() {
        const text = this.element.getAttribute('data-tooltip');
        this.tooltip = document.createElement('div');
        this.tooltip.className = 'tooltip';
        this.tooltip.textContent = text;
        document.body.appendChild(this.tooltip);
        
        const rect = this.element.getBoundingClientRect();
        this.tooltip.style.top = rect.bottom + 5 + 'px';
        this.tooltip.style.left = rect.left + (rect.width - this.tooltip.offsetWidth) / 2 + 'px';
    }

    hide() {
        if (this.tooltip) {
            this.tooltip.remove();
            this.tooltip = null;
        }
    }
}

function exportProducts() {
    const loadingOverlay = document.getElementById('loadingOverlay');
    loadingOverlay.classList.add('active');
    
    // Simulate export process
    setTimeout(() => {
        loadingOverlay.classList.remove('active');
        alert('Products exported successfully!');
    }, 1000);
}
    </script>

<?php $conn->close(); ?>