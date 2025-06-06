<?php

/**
 * Secure eBook Download Handler
 * Ryvah Commerce - Protected file downloads with tracking
 */

session_start();
require_once 'bootstrap.php';
require_once 'paypal_config.php';
require_once 'security.php';

// Enhanced security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: no-referrer');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Initialize error tracking
$error_message = null;
$download_info = null;

try {
    // Validate user authentication
    if (!isset($_SESSION['user_id']) || !is_numeric($_SESSION['user_id']) || $_SESSION['user_id'] <= 0) {
        throw new Exception('Authentication required');
    }

    // Validate download token
    if (!isset($_GET['token']) || empty($_GET['token'])) {
        throw new Exception('Download token required');
    }

    $token = isset($_GET['token']) ? trim(strip_tags($_GET['token'])) : '';
    if (strlen($token) < 10) {
        throw new Exception('Invalid download token format');
    }

    // Fetch download record with comprehensive validation
    $stmt = $conn->prepare("
        SELECT ed.id, ed.user_id, ed.order_id, ed.product_id, ed.download_token, 
               ed.download_count, ed.max_downloads, ed.expires_at, ed.created_at,
               p.name, p.filepath, p.file_size, p.author, p.type,
               o.payment_status, o.invoice_number,
               u.full_name, u.email
        FROM ebook_downloads ed
        JOIN products p ON ed.product_id = p.id
        JOIN orders o ON ed.order_id = o.id  
        JOIN users u ON ed.user_id = u.id
        WHERE ed.download_token = ? AND ed.user_id = ?
    ");

    if (!$stmt) {
        throw new Exception('Database prepare error: ' . $conn->error);
    }

    $stmt->bind_param("si", $token, $_SESSION['user_id']);

    if (!$stmt->execute()) {
        throw new Exception('Database execute error: ' . $stmt->error);
    }

    $download_info = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$download_info) {
        logPayPalError('Invalid download attempt', [
            'token' => $token,
            'user_id' => $_SESSION['user_id'],
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
        throw new Exception('Download not found or unauthorized');
    }

    // Validate order status
    if ($download_info['payment_status'] !== 'completed') {
        throw new Exception('Order payment not completed');
    }

    // Validate product type
    if ($download_info['type'] !== 'ebook') {
        throw new Exception('Invalid product type for download');
    }

    // Check download expiration
    $expires_at = strtotime($download_info['expires_at']);
    if ($expires_at <= time()) {
        logPayPalError('Expired download attempt', [
            'token' => $token,
            'user_id' => $_SESSION['user_id'],
            'product_name' => $download_info['name'],
            'expired_at' => $download_info['expires_at']
        ]);
        throw new Exception('Download link has expired');
    }

    // Check download limit
    if ($download_info['download_count'] >= $download_info['max_downloads']) {
        logPayPalError('Download limit exceeded', [
            'token' => $token,
            'user_id' => $_SESSION['user_id'],
            'product_name' => $download_info['name'],
            'downloads' => $download_info['download_count'],
            'max_downloads' => $download_info['max_downloads']
        ]);
        throw new Exception('Download limit exceeded');
    }

    // Validate file exists and is readable
    $file_path = '../' . $download_info['filepath'];
    $real_path = realpath($file_path);

    // Security check: ensure file is within allowed directory
    $allowed_base = realpath('../Uploads/pdfs/');
    if (!$real_path || !$allowed_base || strpos($real_path, $allowed_base) !== 0) {
        logPayPalError('File path security violation', [
            'token' => $token,
            'user_id' => $_SESSION['user_id'],
            'filepath' => $download_info['filepath'],
            'real_path' => $real_path,
            'allowed_base' => $allowed_base
        ]);
        throw new Exception('File access denied');
    }

    if (!file_exists($real_path) || !is_readable($real_path)) {
        logPayPalError('File not accessible', [
            'token' => $token,
            'product_name' => $download_info['name'],
            'filepath' => $real_path,
            'exists' => file_exists($real_path),
            'readable' => is_readable($real_path)
        ]);
        throw new Exception('File not available for download');
    }

    // Update download count
    $stmt = $conn->prepare("
        UPDATE ebook_downloads 
        SET download_count = download_count + 1, 
            last_download = NOW() 
        WHERE id = ?
    ");

    if ($stmt) {
        $stmt->bind_param("i", $download_info['id']);
        $stmt->execute();
        $stmt->close();
    }

    // Log successful download
    logPayPalError('File download initiated', [
        'user_id' => $_SESSION['user_id'],
        'product_name' => $download_info['name'],
        'order_id' => $download_info['order_id'],
        'download_count' => $download_info['download_count'] + 1,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
    ]);

    // Prepare file download
    $file_size = filesize($real_path);
    $file_name = sanitizeFileName($download_info['name']) . '.pdf';

    // Set download headers
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $file_name . '"');
    header('Content-Length: ' . $file_size);
    header('Content-Transfer-Encoding: binary');
    header('Accept-Ranges: bytes');

    // Additional security headers for file download
    header('X-Robots-Tag: noindex, nofollow');
    header('X-Download-Options: noopen');

    // Clear any output buffering
    if (ob_get_level()) {
        ob_end_clean();
    }

    // Stream the file in chunks for better memory management
    $chunk_size = 8192; // 8KB chunks
    $handle = fopen($real_path, 'rb');

    if ($handle) {
        while (!feof($handle)) {
            echo fread($handle, $chunk_size);
            flush();
        }
        fclose($handle);
    } else {
        throw new Exception('Unable to open file for reading');
    }

    exit;
} catch (Exception $e) {
    // Log the error
    logPayPalError('Download error: ' . $e->getMessage(), [
        'token' => $_GET['token'] ?? 'unknown',
        'user_id' => $_SESSION['user_id'] ?? 'unknown',
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);

    $error_message = $e->getMessage();
}

/**
 * Sanitize filename for download
 */
function sanitizeFileName($filename)
{
    // Remove or replace unsafe characters
    $filename = preg_replace('/[^a-zA-Z0-9\s\-_\.]/', '', $filename);
    $filename = preg_replace('/\s+/', '_', $filename);
    $filename = trim($filename, '._-');
    return $filename ?: 'download';
}

// If we reach here, there was an error
// Include header
require_once 'layouts/header.php';
?>

<style>
.error-container {
    max-width: 600px;
    margin: 4rem auto;
    padding: 0 1rem;
}

.error-card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.error-header {
    background: linear-gradient(135deg, #dc3545, #c82333);
    color: white;
    padding: 2rem;
    border-radius: 12px 12px 0 0;
    text-align: center;
}

.error-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
}
</style>
<div class="error-container">
    <div class="error-card">
        <div class="error-header">
            <div class="error-icon">
                <i class="bi bi-exclamation-triangle-fill"></i>
            </div>
            <h2>Download Error</h2>
            <p class="mb-0">Unable to process your download request</p>
        </div>
        <div class="card-body p-4">
            <div class="alert alert-danger">
                <strong>Error:</strong> <?= htmlspecialchars($error_message); ?>
            </div>

            <h5>Possible Solutions:</h5>
            <ul>
                <li>Verify that your order payment has been completed</li>
                <li>Check if your download hasn't expired (valid for 30 days)</li>
                <li>Ensure you haven't exceeded the maximum download limit</li>
                <li>Try accessing the download from your order confirmation email</li>
            </ul>

            <div class="d-grid gap-2">
                <a href="../account/orders.php" class="btn btn-primary">
                    <i class="bi bi-list-ul me-2"></i>
                    View My Orders
                </a>
                <a href="../contact.php" class="btn btn-outline-secondary">
                    <i class="bi bi-envelope me-2"></i>
                    Contact Support
                </a>
                <a href="../" class="btn btn-outline-primary">
                    <i class="bi bi-house me-2"></i>
                    Return to Home
                </a>
            </div>
        </div>
    </div>

    <?php if ($download_info): ?>
    <div class="card mt-3">
        <div class="card-header">
            <h6 class="mb-0">Download Information</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-sm-6">
                    <strong>Product:</strong><br>
                    <?= htmlspecialchars($download_info['name']); ?>
                </div>
                <div class="col-sm-6">
                    <strong>Order:</strong><br>
                    #<?= htmlspecialchars($download_info['invoice_number']); ?>
                </div>
                <div class="col-sm-6 mt-2">
                    <strong>Downloads Used:</strong><br>
                    <?= $download_info['download_count']; ?> of <?= $download_info['max_downloads']; ?>
                </div>
                <div class="col-sm-6 mt-2">
                    <strong>Expires:</strong><br>
                    <?= date('M j, Y', strtotime($download_info['expires_at'])); ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<?php
// Include footer
require_once 'layouts/footer.php';
?>