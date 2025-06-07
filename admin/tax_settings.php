<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once 'php/session_check.php';
require_once 'php/db_connect.php';

// Helper functions
function getPageTitle()
{
    return "Tax Settings - Admin Panel";
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

        $stmt = $conn->prepare("UPDATE tax_settings SET is_active = ? WHERE product_type = ?");
        $stmt->bind_param("is", $is_active, $product_type);
        $stmt->execute();

        // Log the change
        $stmt = $conn->prepare("INSERT INTO admin_logs (admin_id, action, details) VALUES (?, 'update_tax_status', ?)");
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

        // Handle new tax type addition
        if (isset($_POST['new_tax_type']) && !empty($_POST['new_tax_type'])) {
            $new_type = trim($_POST['new_tax_type']);
            $new_rate = floatval($_POST['new_tax_rate']);

            // Validate new tax type
            if (!preg_match('/^[a-zA-Z0-9\s]+$/', $new_type)) {
                throw new Exception("Tax type can only contain letters, numbers, and spaces");
            }

            if ($new_rate < 0 || $new_rate > 100) {
                throw new Exception("Tax rate must be between 0 and 100");
            }

            // Check if tax type already exists
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM tax_settings WHERE product_type = ?");
            $stmt->bind_param("s", $new_type);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            if ($row['count'] > 0) {
                throw new Exception("Tax type '$new_type' already exists");
            }

            // Insert new tax type
            $stmt = $conn->prepare("INSERT INTO tax_settings (product_type, tax_rate, is_active) VALUES (?, ?, 1)");
            $stmt->bind_param("sd", $new_type, $new_rate);
            $stmt->execute();

            // Log the addition
            $stmt = $conn->prepare("INSERT INTO admin_logs (admin_id, action, details) VALUES (?, 'add_tax', ?)");
            $details = json_encode([
                'product_type' => $new_type,
                'tax_rate' => $new_rate
            ]);
            $stmt->bind_param("is", $_SESSION['user_id'], $details);
            $stmt->execute();
        }

        // Handle existing tax rates updates
        if (isset($_POST['tax_rates'])) {
            foreach ($_POST['tax_rates'] as $product_type => $rate) {
                // Only update if the rate has changed
                if ($rate != $_POST['old_rates'][$product_type]) {
                    // Validate tax rate
                    if (!is_numeric($rate) || $rate < 0 || $rate > 100) {
                        throw new Exception("Invalid tax rate for $product_type");
                    }

                    // Update tax rate
                    $stmt = $conn->prepare("UPDATE tax_settings SET tax_rate = ? WHERE product_type = ?");
                    $stmt->bind_param("ds", $rate, $product_type);
                    $stmt->execute();

                    // Log the change
                    $stmt = $conn->prepare("INSERT INTO admin_logs (admin_id, action, details) VALUES (?, 'update_tax', ?)");
                    $details = json_encode([
                        'product_type' => $product_type,
                        'old_rate' => $_POST['old_rates'][$product_type],
                        'new_rate' => $rate
                    ]);
                    $stmt->bind_param("is", $_SESSION['user_id'], $details);
                    $stmt->execute();
                }
            }
        }

        // Handle tax type deletion
        if (isset($_POST['delete_tax']) && is_array($_POST['delete_tax'])) {
            foreach ($_POST['delete_tax'] as $product_type) {
                $stmt = $conn->prepare("DELETE FROM tax_settings WHERE product_type = ?");
                $stmt->bind_param("s", $product_type);
                $stmt->execute();

                // Log the deletion
                $stmt = $conn->prepare("INSERT INTO admin_logs (admin_id, action, details) VALUES (?, 'delete_tax', ?)");
                $details = json_encode(['product_type' => $product_type]);
                $stmt->bind_param("is", $_SESSION['user_id'], $details);
                $stmt->execute();
            }
        }

        $conn->commit();
        $message = "Tax settings updated successfully!";
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Error updating tax settings: " . $e->getMessage();
    }
}

// Get current tax settings
$stmt = $conn->prepare("SELECT * FROM tax_settings ORDER BY product_type");
$stmt->execute();
$result = $stmt->get_result();
$tax_settings = $result->fetch_all(MYSQLI_ASSOC);

$page_title = "Tax Settings - Admin Panel";
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

    .tax-settings-card {
        max-width: 1200px;
        margin: 0 auto;
        border: none;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        border-radius: 15px;
        transition: all 0.3s ease;
    }

    .card-header {
        background: linear-gradient(135deg, var(--primary-color), #2980b9);
        color: white;
        border-radius: 15px 15px 0 0 !important;
        padding: 1.5rem;
    }

    .add-tax-form {
        background: var(--light-bg);
        padding: 1.5rem;
        border-radius: 10px;
        margin-bottom: 2rem;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }

    .tax-rate-input {
        max-width: 150px;
    }

    .form-control {
        border-radius: 8px;
        border: 1px solid var(--border-color);
        padding: 0.6rem 1rem;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
    }

    .btn {
        border-radius: 8px;
        padding: 0.6rem 1.2rem;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-primary {
        background: var(--primary-color);
        border: none;
    }

    .btn-primary:hover {
        background: #2980b9;
        transform: translateY(-1px);
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

    .tax-type-badge {
        font-size: 0.9em;
        padding: 0.5em 1em;
        border-radius: 20px;
        background: #e9ecef;
        color: var(--text-color);
        font-weight: 500;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    }

    .tax-rate-badge {
        font-size: 0.9em;
        padding: 0.5em 1em;
        border-radius: 20px;
        background: #cce5ff;
        color: #004085;
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

    .tax-history {
        max-height: 400px;
        overflow-y: auto;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }

    .tax-history::-webkit-scrollbar {
        width: 8px;
    }

    .tax-history::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }

    .tax-history::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }

    .tax-history::-webkit-scrollbar-thumb:hover {
        background: #555;
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

    /* Responsive Styles */
    @media (max-width: 768px) {
        .main-content {
            margin-left: 0;
            padding: 1rem;
        }

        body.sidebar-collapsed .main-content {
            margin-left: 0;
        }

        .tax-settings-card {
            margin: 0;
        }

        .logo-section h1 {
            display: none;
        }

        .user-section .user-info {
            display: none;
        }

        .add-tax-form {
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

        .tax-rate-input {
            max-width: 100%;
        }

        .table th:nth-child(4),
        .table td:nth-child(4) {
            display: none;
            /* Hide Last Updated column on small screens */
        }

        .form-check-label .status-badge {
            display: none;
            /* Hide the status text (Active/Inactive) on small screens */
        }
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

    .toast-header {
        border-bottom: none;
        padding: 1rem;
        background: transparent;
    }

    .toast-body {
        padding: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .toast-body i {
        font-size: 1.2em;
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
        border-top: 4px solid var(--primary-color);
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

        <div class="card tax-settings-card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-percentage me-2"></i>Tax Settings
                </h5>
            </div>
            <div class="card-body">
                <!-- Add New Tax Type Form -->
                <div class="add-tax-form">
                    <h6 class="mb-3"><i class="fas fa-plus-circle me-2"></i>Add New Tax Type</h6>
                    <form method="POST" action="" class="row g-3">
                        <div class="col-md-5 col-12">
                            <input type="text" class="form-control" name="new_tax_type"
                                placeholder="Enter tax type name" required>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="input-group">
                                <input type="number" class="form-control" name="new_tax_rate" step="0.01" min="0"
                                    max="100" placeholder="Tax rate" required>
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                        <div class="col-md-3 col-12">
                            <button type="submit" class="btn btn-success w-100">
                                <i class="fas fa-plus me-2"></i>Add Tax Type
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Existing Tax Types -->
                <form method="POST" action="" id="taxForm">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Product Type</th>
                                    <th>Tax Rate (%)</th>
                                    <th>Status</th>
                                    <th>Last Updated</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tax_settings as $setting): ?>
                                <tr>
                                    <td>
                                        <span class="tax-type-badge">
                                            <?php echo ucfirst($setting['product_type']); ?>
                                        </span>
                                        <input type="hidden" name="old_rates[<?php echo $setting['product_type']; ?>]"
                                            value="<?php echo $setting['tax_rate']; ?>">
                                    </td>
                                    <td>
                                        <div class="input-group tax-rate-input">
                                            <input type="number" class="form-control" step="0.01" min="0" max="100"
                                                name="tax_rates[<?php echo $setting['product_type']; ?>]"
                                                value="<?php echo $setting['tax_rate']; ?>" required>
                                            <span class="input-group-text">%</span>
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
                                        <?php echo date('M j, Y H:i', strtotime($setting['updated_at'])); ?>
                                    </td>
                                    <td>
                                        <div class="form-check">
                                            <input class="form-check-input delete-checkbox" type="checkbox"
                                                name="delete_tax[]" value="<?php echo $setting['product_type']; ?>"
                                                onchange="confirmDelete(this)">
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Save Changes
                        </button>
                        <button type="button" class="btn btn-danger" onclick="confirmDeleteSelected()">
                            <i class="fas fa-trash me-2"></i>Delete Selected
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tax History -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-history me-2"></i>Tax Rate History
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive tax-history">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Action</th>
                                <th>Product Type</th>
                                <th>Old Rate</th>
                                <th>New Rate</th>
                                <th>Changed By</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $conn->prepare("
                                SELECT al.*, u.full_name 
                                FROM admin_logs al 
                                JOIN users u ON al.admin_id = u.id 
                                WHERE al.action IN ('update_tax', 'add_tax', 'delete_tax', 'update_tax_status')
                                ORDER BY al.created_at DESC 
                                LIMIT 10
                            ");
                            $stmt->execute();
                            $result = $stmt->get_result();
                            while ($log = $result->fetch_assoc()):
                                $details = json_decode($log['details'], true);
                                $action_text = [
                                    'update_tax' => 'Updated',
                                    'add_tax' => 'Added',
                                    'delete_tax' => 'Deleted',
                                    'update_tax_status' => 'Status Changed'
                                ][$log['action']] ?? $log['action'];
                            ?>
                            <tr>
                                <td><?php echo date('M j, Y H:i', strtotime($log['created_at'])); ?></td>
                                <td>
                                    <span
                                        class="badge bg-<?php echo $log['action'] === 'delete_tax' ? 'danger' : ($log['action'] === 'add_tax' ? 'success' : ($log['action'] === 'update_tax_status' ? 'warning' : 'info')); ?>">
                                        <?php echo $action_text; ?>
                                    </span>
                                </td>
                                <td><?php echo ucfirst($details['product_type']); ?></td>
                                <td><?php echo isset($details['old_rate']) ? $details['old_rate'] . '%' : '-'; ?></td>
                                <td><?php echo isset($details['new_rate']) ? $details['new_rate'] . '%' : (isset($details['tax_rate']) ? $details['tax_rate'] . '%' : '-'); ?>
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
            if (this.value > 100) this.value = 100;
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

    // Confirm delete for individual tax types
    function confirmDelete(checkbox) {
        if (checkbox.checked) {
            const productType = checkbox.value;
            if (!confirm(`Are you sure you want to delete the tax type "${productType}"?`)) {
                checkbox.checked = false;
            }
        }
    }

    // Confirm delete for selected tax types
    function confirmDeleteSelected() {
        const checkboxes = document.querySelectorAll('input[name="delete_tax[]"]:checked');
        if (checkboxes.length === 0) {
            showToast('Please select at least one tax type to delete', 'error');
            return;
        }

        const taxTypes = Array.from(checkboxes).map(cb => cb.value);
        if (confirm(`Are you sure you want to delete the following tax type(s)?\n${taxTypes.join('\n')}`)) {
            showLoading();
            document.getElementById('taxForm').submit();
        }
    }

    // Add animation to status badges
    document.querySelectorAll('.status-badge').forEach(badge => {
        badge.style.transition = 'all 0.3s ease';
    });

    // Handle form submission
    $('#taxForm').on('submit', function() {
        showLoading();
    });

    // Handle sidebar toggle
    function toggleSidebar() {
        document.body.classList.toggle('sidebar-collapsed');
    }

    // Handle sidenav collapse
    const sidenav = document.getElementById('sidenav');
    const mainContent = document.getElementById('main-content');

    // Initialize sidebar state
    document.addEventListener('DOMContentLoaded', function() {
        const sidebarState = localStorage.getItem('sidebarState');
        if (sidebarState === 'collapsed') {
            document.body.classList.add('sidebar-collapsed');
        }
    });

    // Save sidebar state
    document.querySelector('.sidebar-toggle').addEventListener('click', function() {
        const isCollapsed = document.body.classList.contains('sidebar-collapsed');
        localStorage.setItem('sidebarState', isCollapsed ? 'expanded' : 'collapsed');
    });
    </script>
</body>

</html>