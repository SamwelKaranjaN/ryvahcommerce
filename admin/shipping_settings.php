<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once 'php/session_check.php';
require_once 'php/db_connect.php';

// Helper functions
function getPageTitle()
{
    return "Shipping Settings - Admin Panel";
}

function renderCSSLinks()
{
    echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">';
    echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">';
    echo '<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">';
}

$conn = getDBConnection();
$message = '';
$error = '';

// Handle AJAX status update
if (isset($_POST['action']) && $_POST['action'] === 'update_status') {
    try {
        $product_type = $_POST['product_type'];
        $is_active = $_POST['is_active'];

        $stmt = $conn->prepare("UPDATE shipping_fees SET is_active = ? WHERE product_type = ?");
        $stmt->bind_param("is", $is_active, $product_type);
        $stmt->execute();

        // Log the change
        $stmt = $conn->prepare("INSERT INTO admin_logs (admin_id, action, details) VALUES (?, 'update_shipping_status', ?)");
        $details = json_encode([
            'product_type' => $product_type,
            'is_active' => $is_active
        ]);
        $stmt->bind_param("is", $_SESSION['user_id'], $details);
        $stmt->execute();

        echo json_encode(['success' => true]);
        exit;
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['action'])) {
    try {
        $conn->begin_transaction();

        // Handle new shipping fee addition
        if (isset($_POST['new_product_type']) && !empty($_POST['new_product_type'])) {
            $new_type = trim($_POST['new_product_type']);
            $new_fee = floatval($_POST['new_shipping_fee']);
            $applies_after_tax = isset($_POST['new_applies_after_tax']) ? 1 : 0;
            $description = trim($_POST['new_description']);

            // Validate new product type
            if (!in_array($new_type, ['paint', 'ebook', 'book'])) {
                throw new Exception("Invalid product type");
            }

            if ($new_fee < 0) {
                throw new Exception("Shipping fee must be 0 or greater");
            }

            // Check if product type already exists
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM shipping_fees WHERE product_type = ?");
            $stmt->bind_param("s", $new_type);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            if ($row['count'] > 0) {
                throw new Exception("Shipping fee for '$new_type' already exists");
            }

            // Insert new shipping fee
            $stmt = $conn->prepare("INSERT INTO shipping_fees (product_type, shipping_fee, applies_after_tax, description, is_active) VALUES (?, ?, ?, ?, 1)");
            $stmt->bind_param("sdis", $new_type, $new_fee, $applies_after_tax, $description);
            $stmt->execute();

            // Log the addition
            $stmt = $conn->prepare("INSERT INTO admin_logs (admin_id, action, details) VALUES (?, 'add_shipping_fee', ?)");
            $details = json_encode([
                'product_type' => $new_type,
                'shipping_fee' => $new_fee,
                'applies_after_tax' => $applies_after_tax,
                'description' => $description
            ]);
            $stmt->bind_param("is", $_SESSION['user_id'], $details);
            $stmt->execute();
        }

        // Handle existing shipping fees updates
        if (isset($_POST['shipping_fees'])) {
            foreach ($_POST['shipping_fees'] as $product_type => $fee) {
                // Only update if the fee has changed
                if ($fee != $_POST['old_fees'][$product_type]) {
                    // Validate shipping fee
                    if (!is_numeric($fee) || $fee < 0) {
                        throw new Exception("Invalid shipping fee for $product_type");
                    }

                    // Update shipping fee
                    $stmt = $conn->prepare("UPDATE shipping_fees SET shipping_fee = ? WHERE product_type = ?");
                    $stmt->bind_param("ds", $fee, $product_type);
                    $stmt->execute();

                    // Log the change
                    $stmt = $conn->prepare("INSERT INTO admin_logs (admin_id, action, details) VALUES (?, 'update_shipping_fee', ?)");
                    $details = json_encode([
                        'product_type' => $product_type,
                        'old_fee' => $_POST['old_fees'][$product_type],
                        'new_fee' => $fee
                    ]);
                    $stmt->bind_param("is", $_SESSION['user_id'], $details);
                    $stmt->execute();
                }
            }
        }

        // Handle applies_after_tax updates
        if (isset($_POST['applies_after_tax'])) {
            foreach ($_POST['applies_after_tax'] as $product_type => $applies_after_tax) {
                $stmt = $conn->prepare("UPDATE shipping_fees SET applies_after_tax = ? WHERE product_type = ?");
                $stmt->bind_param("is", $applies_after_tax, $product_type);
                $stmt->execute();
            }
        }

        // Handle description updates
        if (isset($_POST['descriptions'])) {
            foreach ($_POST['descriptions'] as $product_type => $description) {
                if ($description != $_POST['old_descriptions'][$product_type]) {
                    $stmt = $conn->prepare("UPDATE shipping_fees SET description = ? WHERE product_type = ?");
                    $stmt->bind_param("ss", $description, $product_type);
                    $stmt->execute();
                }
            }
        }

        // Handle shipping fee deletion
        if (isset($_POST['delete_shipping']) && is_array($_POST['delete_shipping'])) {
            foreach ($_POST['delete_shipping'] as $product_type) {
                $stmt = $conn->prepare("DELETE FROM shipping_fees WHERE product_type = ?");
                $stmt->bind_param("s", $product_type);
                $stmt->execute();

                // Log the deletion
                $stmt = $conn->prepare("INSERT INTO admin_logs (admin_id, action, details) VALUES (?, 'delete_shipping_fee', ?)");
                $details = json_encode(['product_type' => $product_type]);
                $stmt->bind_param("is", $_SESSION['user_id'], $details);
                $stmt->execute();
            }
        }

        $conn->commit();
        $message = "Shipping settings updated successfully!";
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Error updating shipping settings: " . $e->getMessage();
    }
}

// Get current shipping settings
$stmt = $conn->prepare("SELECT * FROM shipping_fees ORDER BY product_type");
$stmt->execute();
$result = $stmt->get_result();
$shipping_settings = $result->fetch_all(MYSQLI_ASSOC);

$page_title = "Shipping Settings - Admin Panel";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo getPageTitle(); ?></title>
    <?php renderCSSLinks(); ?>
    <style>
    :root {
        --primary-color: #3498db;
        --success-color: #2ecc71;
        --danger-color: #e74c3c;
        --warning-color: #f1c40f;
        --text-color: #2c3e50;
        --light-bg: #f8f9fa;
        --border-color: #dee2e6;
        --sidebar-width: 250px;
        --header-height: 60px;
    }

    body {
        font-family: 'Poppins', sans-serif;
        background: #f0f2f5;
        transition: all 0.3s ease;
    }

    /* Header Styles */
    .admin-header {
        position: fixed;
        top: 0;
        right: 0;
        left: 0;
        height: var(--header-height);
        background: white;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        z-index: 1000;
        transition: all 0.3s ease;
    }

    .admin-header .container-fluid {
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .logo-section {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .logo-section img {
        height: 40px;
        width: auto;
    }

    .logo-section h1 {
        font-size: 1.5rem;
        margin: 0;
        color: var(--text-color);
    }

    .user-section {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .user-section .user-info {
        text-align: right;
    }

    .user-section .user-name {
        font-weight: 600;
        color: var(--text-color);
        margin: 0;
    }

    .user-section .user-role {
        font-size: 0.8rem;
        color: #6c757d;
        margin: 0;
    }

    /* Main Content Styles */
    .main-content {
        margin-left: var(--sidebar-width);
        margin-top: var(--header-height);
        padding: 2rem;
        transition: margin-left 0.3s ease-in-out;
    }

    body.sidebar-collapsed .main-content {
        margin-left: 70px;
    }

    .shipping-settings-card {
        max-width: 1200px;
        margin: 0 auto;
        border: none;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        border-radius: 15px;
        transition: all 0.3s ease;
    }

    .card-header {
        background: linear-gradient(135deg, #27ae60, #2ecc71);
        color: white;
        border-radius: 15px 15px 0 0 !important;
        padding: 1.5rem;
    }

    .add-shipping-form {
        background: var(--light-bg);
        padding: 1.5rem;
        border-radius: 10px;
        margin-bottom: 2rem;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }

    .shipping-fee-input {
        max-width: 150px;
    }

    .form-control {
        border-radius: 8px;
        border: 1px solid var(--border-color);
        padding: 0.6rem 1rem;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        border-color: var(--success-color);
        box-shadow: 0 0 0 0.2rem rgba(46, 204, 113, 0.25);
    }

    .btn {
        border-radius: 8px;
        padding: 0.6rem 1.2rem;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-success {
        background: var(--success-color);
        border: none;
    }

    .btn-success:hover {
        background: #27ae60;
        transform: translateY(-1px);
    }

    .btn-danger {
        background: var(--danger-color);
        border: none;
    }

    .btn-danger:hover {
        background: #c0392b;
        transform: translateY(-1px);
    }

    .form-switch {
        padding-left: 2.5em;
    }

    .form-switch .form-check-input {
        width: 2.5em;
        height: 1.25em;
        margin-left: -2.5em;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='-4 -4 8 8'%3e%3ccircle r='3' fill='rgba%280, 0, 0, 0.25%29'/%3e%3c/svg%3e");
        background-position: left center;
        border-radius: 2em;
        transition: all 0.3s ease;
    }

    .form-switch .form-check-input:checked {
        background-color: var(--success-color);
        border-color: var(--success-color);
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='-4 -4 8 8'%3e%3ccircle r='3' fill='%23fff'/%3e%3c/svg%3e");
    }

    .product-type-badge {
        font-size: 0.9em;
        padding: 0.5em 1em;
        border-radius: 20px;
        background: #e9ecef;
        color: var(--text-color);
        font-weight: 500;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    }

    .shipping-fee-badge {
        font-size: 0.9em;
        padding: 0.5em 1em;
        border-radius: 20px;
        background: #d4edda;
        color: #155724;
        font-weight: 500;
    }

    /* Status Badge Styles */
    .status-badge {
        font-size: 0.9em;
        padding: 0.5em 1em;
        border-radius: 20px;
        font-weight: 500;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .status-badge i {
        font-size: 0.8em;
    }

    .status-badge.active {
        background: #d4edda;
        color: #155724;
    }

    .status-badge.inactive {
        background: #f8d7da;
        color: #721c24;
    }

    .table {
        margin-bottom: 0;
    }

    .table th {
        font-weight: 600;
        color: var(--text-color);
        border-bottom: 2px solid var(--border-color);
    }

    .table td {
        vertical-align: middle;
        padding: 1rem;
    }

    .alert {
        border: none;
        border-radius: 10px;
        padding: 1rem 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }

    .alert-success {
        background: #d4edda;
        color: #155724;
    }

    .alert-danger {
        background: #f8d7da;
        color: #721c24;
    }

    .delete-checkbox {
        width: 20px;
        height: 20px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .delete-checkbox:checked {
        background-color: var(--danger-color);
        border-color: var(--danger-color);
    }

    /* Toast Notification Styles */
    .toast-container {
        position: fixed;
        top: calc(var(--header-height) + 20px);
        right: 20px;
        z-index: 1050;
    }

    .toast {
        background: white;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        margin-bottom: 10px;
        min-width: 300px;
        border: none;
    }

    /* Loading Spinner */
    .loading-spinner {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.8);
        z-index: 9999;
        justify-content: center;
        align-items: center;
    }

    .spinner {
        width: 40px;
        height: 40px;
        border: 4px solid #f3f3f3;
        border-top: 4px solid var(--success-color);
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    /* Responsive Styles */
    @media (max-width: 768px) {
        .main-content {
            margin-left: 0;
            padding: 1rem;
        }

        body.sidebar-collapsed .main-content {
            margin-left: 0;
        }

        .shipping-settings-card {
            margin: 0;
        }

        .logo-section h1 {
            display: none;
        }

        .user-section .user-info {
            display: none;
        }

        .add-shipping-form {
            padding: 1rem;
        }

        .table-responsive {
            margin: 0 -1rem;
        }

        .btn {
            width: 100%;
            margin-bottom: 0.5rem;
        }

        .d-flex.justify-content-between {
            flex-direction: column;
        }

        .d-flex.justify-content-between .btn {
            margin-bottom: 0.5rem;
        }

        .table td,
        .table th {
            padding: 0.5rem;
            font-size: 0.9em;
        }

        .shipping-fee-input {
            max-width: 100%;
        }

        .table th:nth-child(6),
        .table td:nth-child(6) {
            display: none;
            /* Hide Last Updated column on small screens */
        }
    }
    </style>
</head>

<body>
    <?php include 'header.php'; ?>

    <div class="main-content" id="main-content">

        <!-- Toast Container -->
        <div class="toast-container"></div>

        <!-- Loading Spinner -->
        <div class="loading-spinner">
            <div class="spinner"></div>
        </div>

        <?php if ($message): ?>
        <div class="status-badge active mb-3">
            <i class="fas fa-check-circle"></i>
            <?php echo $message; ?>
        </div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="status-badge inactive mb-3">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo $error; ?>
        </div>
        <?php endif; ?>

        <div class="card shipping-settings-card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-shipping-fast me-2"></i>Shipping Settings
                </h5>
            </div>
            <div class="card-body">
                <!-- Add New Shipping Fee Form -->
                <div class="add-shipping-form">
                    <h6 class="mb-3"><i class="fas fa-plus-circle me-2"></i>Add New Shipping Fee</h6>
                    <form method="POST" action="" class="row g-3">
                        <div class="col-md-3 col-12">
                            <select class="form-control" name="new_product_type" required>
                                <option value="">Select Product Type</option>
                                <option value="book">Book</option>
                                <option value="ebook">Ebook</option>
                                <option value="paint">Paint</option>
                            </select>
                        </div>
                        <div class="col-md-3 col-12">
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" name="new_shipping_fee" step="0.01" min="0"
                                    placeholder="Shipping fee" required>
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <input type="text" class="form-control" name="new_description"
                                placeholder="Description (optional)">
                        </div>
                        <div class="col-md-2 col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="new_applies_after_tax" checked>
                                <label class="form-check-label" style="font-size: 0.9rem;">
                                    After Tax
                                </label>
                            </div>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-plus me-2"></i>Add Shipping Fee
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Existing Shipping Fees -->
                <form method="POST" action="" id="shippingForm">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Product Type</th>
                                    <th>Shipping Fee ($)</th>
                                    <th>Status</th>
                                    <th>Apply After Tax</th>
                                    <th>Description</th>
                                    <th>Last Updated</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($shipping_settings as $setting): ?>
                                <tr>
                                    <td>
                                        <span class="product-type-badge">
                                            <?php echo ucfirst($setting['product_type']); ?>
                                        </span>
                                        <input type="hidden" name="old_fees[<?php echo $setting['product_type']; ?>]"
                                            value="<?php echo $setting['shipping_fee']; ?>">
                                        <input type="hidden"
                                            name="old_descriptions[<?php echo $setting['product_type']; ?>]"
                                            value="<?php echo htmlspecialchars($setting['description']); ?>">
                                    </td>
                                    <td>
                                        <div class="input-group shipping-fee-input">
                                            <span class="input-group-text">$</span>
                                            <input type="number" class="form-control" step="0.01" min="0"
                                                name="shipping_fees[<?php echo $setting['product_type']; ?>]"
                                                value="<?php echo $setting['shipping_fee']; ?>" required>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input status-switch" type="checkbox" role="switch"
                                                name="is_active[<?php echo $setting['product_type']; ?>]"
                                                <?php echo $setting['is_active'] ? 'checked' : ''; ?>
                                                data-product-type="<?php echo $setting['product_type']; ?>">
                                            <label class="form-check-label">
                                                <span
                                                    class="status-badge <?php echo $setting['is_active'] ? 'active' : 'inactive'; ?>">
                                                    <?php echo $setting['is_active'] ? 'Active' : 'Inactive'; ?>
                                                </span>
                                            </label>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch"
                                                name="applies_after_tax[<?php echo $setting['product_type']; ?>]"
                                                value="1" <?php echo $setting['applies_after_tax'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label">
                                                <?php echo $setting['applies_after_tax'] ? 'Yes' : 'No'; ?>
                                            </label>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control"
                                            name="descriptions[<?php echo $setting['product_type']; ?>]"
                                            value="<?php echo htmlspecialchars($setting['description']); ?>"
                                            placeholder="Description">
                                    </td>
                                    <td>
                                        <?php echo date('M j, Y H:i', strtotime($setting['updated_at'])); ?>
                                    </td>
                                    <td>
                                        <div class="form-check">
                                            <input class="form-check-input delete-checkbox" type="checkbox"
                                                name="delete_shipping[]" value="<?php echo $setting['product_type']; ?>"
                                                onchange="confirmDelete(this)">
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between mt-3">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save me-2"></i>Save Changes
                        </button>
                        <button type="button" class="btn btn-danger" onclick="confirmDeleteSelected()">
                            <i class="fas fa-trash me-2"></i>Delete Selected
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Shipping History -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-history me-2"></i>Shipping Fee History
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Action</th>
                                <th>Product Type</th>
                                <th>Old Fee</th>
                                <th>New Fee</th>
                                <th>Changed By</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $conn->prepare("
                                SELECT al.*, u.full_name 
                                FROM admin_logs al 
                                JOIN users u ON al.admin_id = u.id 
                                WHERE al.action IN ('update_shipping_fee', 'add_shipping_fee', 'delete_shipping_fee', 'update_shipping_status')
                                ORDER BY al.created_at DESC 
                                LIMIT 10
                            ");
                            $stmt->execute();
                            $result = $stmt->get_result();
                            while ($log = $result->fetch_assoc()):
                                $details = json_decode($log['details'], true);
                                $action_text = [
                                    'update_shipping_fee' => 'Updated',
                                    'add_shipping_fee' => 'Added',
                                    'delete_shipping_fee' => 'Deleted',
                                    'update_shipping_status' => 'Status Changed'
                                ][$log['action']] ?? $log['action'];
                            ?>
                            <tr>
                                <td><?php echo date('M j, Y H:i', strtotime($log['created_at'])); ?></td>
                                <td>
                                    <span
                                        class="badge bg-<?php echo $log['action'] === 'delete_shipping_fee' ? 'danger' : ($log['action'] === 'add_shipping_fee' ? 'success' : ($log['action'] === 'update_shipping_status' ? 'warning' : 'info')); ?>">
                                        <?php echo $action_text; ?>
                                    </span>
                                </td>
                                <td><?php echo ucfirst($details['product_type']); ?></td>
                                <td><?php echo isset($details['old_fee']) ? '$' . number_format($details['old_fee'], 2) : '-'; ?>
                                </td>
                                <td><?php echo isset($details['new_fee']) ? '$' . number_format($details['new_fee'], 2) : (isset($details['shipping_fee']) ? '$' . number_format($details['shipping_fee'], 2) : '-'); ?>
                                </td>
                                <td><?php echo htmlspecialchars($log['full_name']); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Show toast notification
    function showToast(message, type = 'success') {
        const toast = `
            <div class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header">
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body">
                    <i class="fas fa-${type === 'success' ? 'check-circle text-success' : 'exclamation-circle text-danger'}"></i>
                    ${message}
                </div>
            </div>
        `;

        $('.toast-container').append(toast);
        const toastElement = $('.toast').last();
        const bsToast = new bootstrap.Toast(toastElement, {
            delay: 3000
        });
        bsToast.show();

        toastElement.on('hidden.bs.toast', function() {
            $(this).remove();
        });
    }

    // Show loading spinner
    function showLoading() {
        $('.loading-spinner').css('display', 'flex');
    }

    // Hide loading spinner
    function hideLoading() {
        $('.loading-spinner').css('display', 'none');
    }

    // Add input validation
    document.querySelectorAll('input[type="number"]').forEach(input => {
        input.addEventListener('change', function() {
            if (this.value < 0) this.value = 0;
        });
    });

    // Handle status switch changes
    $('.status-switch').on('change', function() {
        const productType = $(this).data('product-type');
        const isActive = $(this).prop('checked');
        const label = $(this).next().find('.status-badge');

        showLoading();

        $.ajax({
            url: window.location.href,
            method: 'POST',
            data: {
                action: 'update_status',
                product_type: productType,
                is_active: isActive ? 1 : 0
            },
            success: function(response) {
                const data = JSON.parse(response);
                if (data.success) {
                    if (isActive) {
                        label.text('Active');
                        label.removeClass('inactive').addClass('active');
                    } else {
                        label.text('Inactive');
                        label.removeClass('active').addClass('inactive');
                    }
                    showToast('Status updated successfully');
                } else {
                    showToast(data.error || 'Error updating status', 'error');
                    // Revert the switch
                    $(this).prop('checked', !isActive);
                }
            },
            error: function() {
                showToast('Error updating status', 'error');
                // Revert the switch
                $(this).prop('checked', !isActive);
            },
            complete: function() {
                hideLoading();
            }
        });
    });

    // Confirm delete for individual shipping fees
    function confirmDelete(checkbox) {
        if (checkbox.checked) {
            const productType = checkbox.value;
            if (!confirm(`Are you sure you want to delete the shipping fee for "${productType}"?`)) {
                checkbox.checked = false;
            }
        }
    }

    // Confirm delete for selected shipping fees
    function confirmDeleteSelected() {
        const checkboxes = document.querySelectorAll('input[name="delete_shipping[]"]:checked');
        if (checkboxes.length === 0) {
            showToast('Please select at least one shipping fee to delete', 'error');
            return;
        }

        const productTypes = Array.from(checkboxes).map(cb => cb.value);
        if (confirm(
                `Are you sure you want to delete the following shipping fee(s)?\n${productTypes.join('\n')}`
            )) {
            showLoading();
            document.getElementById('shippingForm').submit();
        }
    }

    // Handle form submission
    $('#shippingForm').on('submit', function() {
        showLoading();
    });
    </script>
</body>

</html>