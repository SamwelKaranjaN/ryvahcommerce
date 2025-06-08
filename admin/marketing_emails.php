<?php
session_start();
require_once 'php/db_connect.php';

// Check if admin is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Admin') {
    header('Location: login.php');
    exit();
}

require_once 'header.php';

// Fetch customers from both users and customers table, avoiding duplicates by email
$query = "
    SELECT 
        CONCAT('user_', u.id) as unique_id,
        u.id as user_id,
        u.full_name as name,
        u.email,
        u.phone,
        u.created_at,
        'users' as source_table
    FROM users u 
    WHERE u.role = 'Client'
    
    UNION
    
    SELECT 
        CONCAT('customer_', c.id) as unique_id,
        c.id as user_id,
        c.name as name,
        c.email,
        c.phone,
        c.created_at,
        'customers' as source_table
    FROM customers c 
    WHERE c.email NOT IN (
        SELECT email FROM users WHERE role = 'Client' AND email IS NOT NULL
    )
    
    ORDER BY name ASC
";
$customers_result = $conn->query($query);

// Get customer count from both tables
$count_query = "
    SELECT 
        (SELECT COUNT(*) FROM users WHERE role = 'Client') +
        (SELECT COUNT(*) FROM customers WHERE email NOT IN 
            (SELECT email FROM users WHERE role = 'Client' AND email IS NOT NULL)
        ) as total
";
$count_result = $conn->query($count_query);
$total_customers = $count_result->fetch_assoc()['total'];

// Handle SMTP settings save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_smtp'])) {
    $smtp_host = $_POST['smtp_host'] ?? 'smtp.hostinger.com';
    $smtp_port = $_POST['smtp_port'] ?? '465';
    $smtp_username = $_POST['smtp_username'] ?? 'info@ryvahcommerce.com';
    $smtp_password = $_POST['smtp_password'] ?? 'Meldor1!1';
    $from_email = $_POST['from_email'] ?? 'info@ryvahcommerce.com';
    $from_name = $_POST['from_name'] ?? 'Ryvah Commerce';

    // Save to session
    $_SESSION['smtp_settings'] = [
        'host' => $smtp_host,
        'port' => $smtp_port,
        'username' => $smtp_username,
        'password' => $smtp_password,
        'from_email' => $from_email,
        'from_name' => $from_name
    ];

    $success_message = "SMTP settings saved successfully!";
}

// Get saved SMTP settings
$smtp_settings = $_SESSION['smtp_settings'] ?? [
    'host' => 'smtp.hostinger.com',
    'port' => '465',
    'username' => '',
    'password' => '',
    'from_email' => 'info@ryvahcommerce.com',
    'from_name' => 'Ryvah Commerce'
];
?>

<div class="main-content" id="main-content">
    <div class="page-header">
        <h2><i class="fas fa-envelope"></i> Marketing Emails</h2>
        <div class="header-actions">
            <a href="delivery_reports.php" class="btn btn-secondary">
                <i class="fas fa-chart-bar"></i> View Reports
            </a>
            <button class="btn btn-primary" onclick="testSMTPConnection()">
                <i class="fas fa-check-circle"></i> Test SMTP Connection
            </button>
        </div>
    </div>

    <?php if (isset($success_message)): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
        </div>
    <?php endif; ?>

    <!-- SMTP Configuration Section -->
    <div class="content-card">
        <div class="card-header">
            <h3><i class="fas fa-server"></i> SMTP Configuration</h3>
            <button class="btn btn-secondary" onclick="toggleSMTPConfig()">
                <i class="fas fa-cog"></i> Configure SMTP
            </button>
        </div>

        <div id="smtp-config" class="smtp-config" style="display: none;">
            <form method="POST" class="smtp-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="smtp_host">SMTP Host</label>
                        <input type="text" id="smtp_host" name="smtp_host" class="form-control"
                            value="<?php echo htmlspecialchars($smtp_settings['host']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="smtp_port">SMTP Port</label>
                        <input type="number" id="smtp_port" name="smtp_port" class="form-control"
                            value="<?php echo htmlspecialchars($smtp_settings['port']); ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="smtp_username">SMTP Username</label>
                        <input type="text" id="smtp_username" name="smtp_username" class="form-control"
                            value="<?php echo htmlspecialchars($smtp_settings['username']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="smtp_password">SMTP Password</label>
                        <input type="password" id="smtp_password" name="smtp_password" class="form-control"
                            value="<?php echo htmlspecialchars($smtp_settings['password']); ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="from_email">From Email</label>
                        <input type="email" id="from_email" name="from_email" class="form-control"
                            value="<?php echo htmlspecialchars($smtp_settings['from_email']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="from_name">From Name</label>
                        <input type="text" id="from_name" name="from_name" class="form-control"
                            value="<?php echo htmlspecialchars($smtp_settings['from_name']); ?>" required>
                    </div>
                </div>

                <button type="submit" name="save_smtp" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save SMTP Settings
                </button>
            </form>
        </div>
    </div>

    <!-- Email Composition Section -->
    <div class="content-card">
        <div class="card-header">
            <h3><i class="fas fa-edit"></i> Compose Email</h3>
            <div class="recipient-stats">
                <span class="stat-item">
                    <i class="fas fa-users"></i> Total Customers: <?php echo $total_customers; ?>
                </span>
                <span class="stat-item">
                    <i class="fas fa-envelope"></i> Selected: <span id="selected-count">0</span>
                </span>
            </div>
        </div>

        <form id="email-form" class="email-form">
            <!-- Customer Selection -->
            <div class="customer-selection">
                <div class="selection-header">
                    <h4>Select Recipients</h4>
                    <div class="selection-actions">
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAll()">
                            <i class="fas fa-check-double"></i> Select All
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="selectNone()">
                            <i class="fas fa-times"></i> Clear All
                        </button>
                    </div>
                </div>

                <div class="search-customers">
                    <input type="text" id="customer-search" placeholder="Search customers..." class="form-control">
                </div>

                <div class="customers-list" id="customers-list">
                    <?php while ($customer = $customers_result->fetch_assoc()): ?>
                        <div class="customer-item"
                            data-name="<?php echo strtolower(htmlspecialchars($customer['name'])); ?>"
                            data-email="<?php echo strtolower(htmlspecialchars($customer['email'])); ?>">
                            <label class="customer-checkbox">
                                <input type="checkbox" name="recipients[]" value="<?php echo $customer['unique_id']; ?>"
                                    data-email="<?php echo htmlspecialchars($customer['email']); ?>"
                                    data-name="<?php echo htmlspecialchars($customer['name']); ?>"
                                    data-source="<?php echo htmlspecialchars($customer['source_table']); ?>">
                                <span class="checkmark"></span>
                                <div class="customer-info">
                                    <div class="customer-name">
                                        <?php echo htmlspecialchars($customer['name']); ?>
                                        <span class="source-badge <?php echo $customer['source_table']; ?>">
                                            <?php echo $customer['source_table'] === 'users' ? 'User' : 'Customer'; ?>
                                        </span>
                                    </div>
                                    <div class="customer-email"><?php echo htmlspecialchars($customer['email']); ?></div>
                                    <div class="customer-meta">
                                        <span class="join-date">Joined:
                                            <?php echo date('M Y', strtotime($customer['created_at'])); ?></span>
                                        <?php if ($customer['phone']): ?>
                                            <span class="phone">| <?php echo htmlspecialchars($customer['phone']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </label>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>

            <!-- Email Templates -->
            <div class="email-templates" style="padding: 20px; border-bottom: 1px solid #eee;">
                <div class="template-header">
                    <h4>Email Templates</h4>
                    <p style="color: #6c757d; margin: 5px 0 15px 0;">Choose from pre-designed templates or create your
                        own</p>
                </div>
                <div class="template-selection">
                    <select id="template-select" class="form-control" style="margin-bottom: 15px;">
                        <option value="">Select a template...</option>
                        <option value="welcome">Welcome Email</option>
                        <option value="promotion">Special Promotion</option>
                        <option value="newsletter">Monthly Newsletter</option>
                    </select>
                    <button type="button" class="btn btn-outline-primary" onclick="loadTemplate()">
                        <i class="fas fa-download"></i> Load Template
                    </button>
                </div>
            </div>

            <!-- Email Content -->
            <div class="email-content">
                <div class="form-group">
                    <label for="email_subject">Subject</label>
                    <input type="text" id="email_subject" name="email_subject" class="form-control"
                        placeholder="Enter email subject..." required>
                </div>

                <div class="form-group">
                    <label for="email_body">Message</label>
                    <div class="editor-toolbar">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="formatText('bold')">
                            <i class="fas fa-bold"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="formatText('italic')">
                            <i class="fas fa-italic"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary"
                            onclick="formatText('underline')">
                            <i class="fas fa-underline"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary"
                            onclick="insertTemplate('greeting')">
                            <i class="fas fa-smile"></i> Greeting
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary"
                            onclick="insertTemplate('promotion')">
                            <i class="fas fa-percentage"></i> Promotion
                        </button>
                    </div>
                    <div id="email_body" class="form-control email-editor" contenteditable="true"
                        placeholder="Type your message here..."></div>
                    <div class="editor-help">
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i> Use {name} to personalize with customer name
                        </small>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-outline-primary" onclick="previewEmail()">
                        <i class="fas fa-eye"></i> Preview
                    </button>
                    <button type="button" class="btn btn-success" onclick="sendEmails()">
                        <i class="fas fa-paper-plane"></i> Send Emails
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Email Status Section -->
    <div class="content-card" id="email-status" style="display: none;">
        <div class="card-header">
            <h3><i class="fas fa-chart-line"></i> Email Sending Status</h3>
        </div>
        <div id="sending-progress">
            <div class="progress-bar">
                <div class="progress-fill" id="progress-fill"></div>
            </div>
            <div class="progress-text" id="progress-text">Preparing to send...</div>
            <div class="progress-details" id="progress-details"></div>
        </div>
    </div>

    <!-- Delivery Reports Section -->
    <div class="content-card">
        <div class="card-header">
            <h3><i class="fas fa-chart-bar"></i> Email Delivery Reports</h3>
            <div class="header-actions">
                <button class="btn btn-secondary" onclick="refreshReports()">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
                <button class="btn btn-primary" onclick="exportReports()">
                    <i class="fas fa-download"></i> Export CSV
                </button>
            </div>
        </div>

        <div class="reports-content">
            <!-- Search and Filter Controls -->
            <div class="reports-filters">
                <div class="filter-row">
                    <div class="search-group">
                        <label for="search-reports">Search Reports</label>
                        <input type="text" id="search-reports" class="form-control"
                            placeholder="Search by email, subject, or status..." onkeyup="filterReports()">
                    </div>

                    <div class="date-group">
                        <label for="date-from">From Date</label>
                        <input type="date" id="date-from" class="form-control" onchange="filterReports()">
                    </div>

                    <div class="date-group">
                        <label for="date-to">To Date</label>
                        <input type="date" id="date-to" class="form-control" onchange="filterReports()">
                    </div>

                    <div class="status-group">
                        <label for="status-filter">Status</label>
                        <select id="status-filter" class="form-control" onchange="filterReports()">
                            <option value="">All Status</option>
                            <option value="sent">Sent</option>
                            <option value="failed">Failed</option>
                        </select>
                    </div>
                </div>

                <div class="filter-actions">
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="clearFilters()">
                        <i class="fas fa-times"></i> Clear Filters
                    </button>
                    <span class="results-count">Showing <span id="visible-count">0</span> of <span
                            id="total-count">0</span> records</span>
                </div>
            </div>

            <!-- Reports Table -->
            <div class="reports-table-container">
                <table class="reports-table" id="reports-table">
                    <thead>
                        <tr>
                            <th onclick="sortTable(0)">Date & Time <i class="fas fa-sort"></i></th>
                            <th onclick="sortTable(1)">Recipient Email <i class="fas fa-sort"></i></th>
                            <th onclick="sortTable(2)">Subject <i class="fas fa-sort"></i></th>
                            <th onclick="sortTable(3)">Status <i class="fas fa-sort"></i></th>
                            <th onclick="sortTable(4)">Admin <i class="fas fa-sort"></i></th>
                            <th>Error Details</th>
                        </tr>
                    </thead>
                    <tbody id="reports-tbody">
                        <?php
                        // Fetch email delivery reports
                        $reports_query = "
                            SELECT 
                                mel.id,
                                mel.recipient_email,
                                mel.subject,
                                mel.status,
                                mel.error_message,
                                mel.sent_at,
                                u.full_name as admin_name,
                                u.email as admin_email
                            FROM marketing_email_logs mel
                            LEFT JOIN users u ON mel.admin_id = u.id
                            ORDER BY mel.sent_at DESC
                            LIMIT 100
                        ";
                        $reports_result = $conn->query($reports_query);
                        $total_reports = 0;

                        if ($reports_result && $reports_result->num_rows > 0):
                            while ($report = $reports_result->fetch_assoc()):
                                $total_reports++;
                                $status_class = $report['status'] === 'sent' ? 'status-sent' : 'status-failed';
                                $status_icon = $report['status'] === 'sent' ? 'fa-check-circle' : 'fa-times-circle';
                        ?>
                                <tr class="report-row"
                                    data-email="<?php echo strtolower(htmlspecialchars($report['recipient_email'])); ?>"
                                    data-subject="<?php echo strtolower(htmlspecialchars($report['subject'])); ?>"
                                    data-status="<?php echo htmlspecialchars($report['status']); ?>"
                                    data-date="<?php echo date('Y-m-d', strtotime($report['sent_at'])); ?>">

                                    <td class="date-cell">
                                        <div class="date-time">
                                            <div class="date"><?php echo date('M d, Y', strtotime($report['sent_at'])); ?></div>
                                            <div class="time"><?php echo date('h:i A', strtotime($report['sent_at'])); ?></div>
                                        </div>
                                    </td>

                                    <td class="email-cell">
                                        <div class="email-info">
                                            <i class="fas fa-envelope"></i>
                                            <?php echo htmlspecialchars($report['recipient_email']); ?>
                                        </div>
                                    </td>

                                    <td class="subject-cell">
                                        <div class="subject-text" title="<?php echo htmlspecialchars($report['subject']); ?>">
                                            <?php echo htmlspecialchars(substr($report['subject'], 0, 50)); ?>
                                            <?php if (strlen($report['subject']) > 50): ?>...<?php endif; ?>
                                        </div>
                                    </td>

                                    <td class="status-cell">
                                        <span class="status-badge <?php echo $status_class; ?>">
                                            <i class="fas <?php echo $status_icon; ?>"></i>
                                            <?php echo ucfirst($report['status']); ?>
                                        </span>
                                    </td>

                                    <td class="admin-cell">
                                        <div class="admin-info">
                                            <div class="admin-name">
                                                <?php echo htmlspecialchars($report['admin_name'] ?? 'Unknown'); ?></div>
                                            <div class="admin-email">
                                                <?php echo htmlspecialchars($report['admin_email'] ?? ''); ?></div>
                                        </div>
                                    </td>

                                    <td class="error-cell">
                                        <?php if ($report['status'] === 'failed' && $report['error_message']): ?>
                                            <button class="btn btn-sm btn-outline-danger"
                                                onclick="showErrorDetails('<?php echo htmlspecialchars(addslashes($report['error_message'])); ?>')">
                                                <i class="fas fa-exclamation-triangle"></i> View Error
                                            </button>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php
                            endwhile;
                        else:
                            ?>
                            <tr id="no-reports-row">
                                <td colspan="6" class="no-data">
                                    <div class="no-data-message">
                                        <i class="fas fa-inbox"></i>
                                        <h4>No Email Reports Found</h4>
                                        <p>No marketing emails have been sent yet.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination (if needed for large datasets) -->
            <div class="reports-pagination">
                <div class="pagination-info">
                    Showing latest 100 records
                </div>
            </div>
        </div>
    </div>

    <!-- Delivery Reports Section -->
    <div class="content-card">
        <div class="card-header">
            <h3><i class="fas fa-chart-bar"></i> Email Delivery Reports</h3>
        </div>

        <div class="reports-content">
            <!-- Search and Filter Controls -->
            <div class="reports-filters">
                <div class="filter-row">
                    <div class="search-group">
                        <label for="search-reports">Search Reports</label>
                        <input type="text" id="search-reports" class="form-control"
                            placeholder="Search by email, subject..."
                            onkeyup="filterReports()">
                    </div>

                    <div class="date-group">
                        <label for="date-from">From Date</label>
                        <input type="date" id="date-from" class="form-control" onchange="filterReports()">
                    </div>

                    <div class="date-group">
                        <label for="date-to">To Date</label>
                        <input type="date" id="date-to" class="form-control" onchange="filterReports()">
                    </div>

                    <div class="status-group">
                        <label for="status-filter">Status</label>
                        <select id="status-filter" class="form-control" onchange="filterReports()">
                            <option value="">All Status</option>
                            <option value="sent">Sent</option>
                            <option value="failed">Failed</option>
                        </select>
                    </div>
                </div>

                <div class="filter-actions">
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="clearFilters()">
                        <i class="fas fa-times"></i> Clear Filters
                    </button>
                    <span class="results-count">Showing <span id="visible-count">0</span> of <span id="total-count">0</span> records</span>
                </div>
            </div>

            <!-- Reports Table -->
            <div class="table-container">
                <table class="reports-table" id="reports-table">
                    <thead>
                        <tr>
                            <th onclick="sortTable(0)">Date <i class="fas fa-sort"></i></th>
                            <th onclick="sortTable(1)">Email <i class="fas fa-sort"></i></th>
                            <th onclick="sortTable(2)">Subject <i class="fas fa-sort"></i></th>
                            <th onclick="sortTable(3)">Status <i class="fas fa-sort"></i></th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Fetch email delivery reports
                        $reports_query = "
                            SELECT 
                                mel.recipient_email,
                                mel.subject,
                                mel.status,
                                mel.error_message,
                                mel.sent_at
                            FROM marketing_email_logs mel
                            ORDER BY mel.sent_at DESC
                            LIMIT 50
                        ";
                        $reports_result = $conn->query($reports_query);

                        if ($reports_result && $reports_result->num_rows > 0):
                            while ($report = $reports_result->fetch_assoc()):
                                $status_class = $report['status'] === 'sent' ? 'status-sent' : 'status-failed';
                                $status_icon = $report['status'] === 'sent' ? 'fa-check-circle' : 'fa-times-circle';
                        ?>
                                <tr class="report-row"
                                    data-email="<?php echo strtolower(htmlspecialchars($report['recipient_email'])); ?>"
                                    data-subject="<?php echo strtolower(htmlspecialchars($report['subject'])); ?>"
                                    data-status="<?php echo htmlspecialchars($report['status']); ?>"
                                    data-date="<?php echo date('Y-m-d', strtotime($report['sent_at'])); ?>">

                                    <td><?php echo date('M d, Y h:i A', strtotime($report['sent_at'])); ?></td>

                                    <td><?php echo htmlspecialchars($report['recipient_email']); ?></td>

                                    <td title="<?php echo htmlspecialchars($report['subject']); ?>">
                                        <?php echo htmlspecialchars(substr($report['subject'], 0, 30)); ?>
                                        <?php if (strlen($report['subject']) > 30): ?>...<?php endif; ?>
                                    </td>

                                    <td>
                                        <span class="status-badge <?php echo $status_class; ?>">
                                            <i class="fas <?php echo $status_icon; ?>"></i>
                                            <?php echo ucfirst($report['status']); ?>
                                        </span>
                                    </td>

                                    <td>
                                        <?php if ($report['status'] === 'failed' && $report['error_message']): ?>
                                            <button class="btn btn-sm btn-outline-danger"
                                                onclick="showError('<?php echo htmlspecialchars(addslashes($report['error_message'])); ?>')">
                                                <i class="fas fa-exclamation-triangle"></i>
                                            </button>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php
                            endwhile;
                        else:
                            ?>
                            <tr id="no-reports-row">
                                <td colspan="5" style="text-align: center; padding: 40px;">
                                    <div>
                                        <i class="fas fa-inbox" style="font-size: 2em; color: #adb5bd;"></i>
                                        <p>No email reports found</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Error Modal -->
<div id="error-modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Error Details</h3>
            <span class="close" onclick="closeErrorModal()">&times;</span>
        </div>
        <div class="modal-body">
            <div id="error-message"></div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeErrorModal()">Close</button>
        </div>
    </div>
</div>

<!-- Email Preview Modal -->
<div id="email-preview-modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Email Preview</h3>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>
        <div class="modal-body">
            <div class="preview-controls">
                <label for="preview-customer">Preview for customer:</label>
                <select id="preview-customer" class="form-control">
                    <option value="">Select a customer...</option>
                </select>
            </div>
            <div class="email-preview">
                <div class="email-header">
                    <strong>From:</strong> <span id="preview-from"></span><br>
                    <strong>Subject:</strong> <span id="preview-subject"></span>
                </div>
                <div class="email-body-preview" id="preview-body"></div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal()">Close</button>
            <button class="btn btn-success" onclick="closeModal(); sendEmails();">Send Emails</button>
        </div>
    </div>
</div>

<style>
    .main-content {
        padding: 20px;
        margin-left: 260px;
        margin-top: 60px;
        min-height: calc(100vh - 60px);
        background: #f8f9fa;
        transition: margin-left 0.3s ease;
    }

    .main-content.collapsed {
        margin-left: 60px;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .page-header h2 {
        color: #2c3e50;
        margin: 0;
    }

    .content-card {
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
        overflow: hidden;
    }

    .card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .card-header h3 {
        margin: 0;
        font-size: 1.2em;
    }

    .alert {
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .alert-success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .smtp-config {
        padding: 20px;
        border-top: 1px solid #eee;
    }

    .smtp-form .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 20px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    .form-group label {
        font-weight: 600;
        margin-bottom: 5px;
        color: #2c3e50;
    }

    .form-control {
        padding: 12px;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        font-size: 14px;
        transition: border-color 0.3s ease;
    }

    .form-control:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .btn {
        padding: 10px 20px;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 14px;
    }

    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }

    .btn-secondary {
        background: #6c757d;
        color: white;
    }

    .btn-secondary:hover {
        background: #5a6268;
    }

    .btn-success {
        background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
        color: white;
    }

    .btn-success:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(76, 175, 80, 0.4);
    }

    .btn-outline-primary {
        background: transparent;
        color: #667eea;
        border: 2px solid #667eea;
    }

    .btn-outline-primary:hover {
        background: #667eea;
        color: white;
    }

    .btn-outline-secondary {
        background: transparent;
        color: #6c757d;
        border: 1px solid #6c757d;
    }

    .btn-outline-secondary:hover {
        background: #6c757d;
        color: white;
    }

    .btn-sm {
        padding: 6px 12px;
        font-size: 12px;
    }

    .recipient-stats {
        display: flex;
        gap: 20px;
        align-items: center;
    }

    .stat-item {
        color: rgba(255, 255, 255, 0.9);
        font-size: 14px;
    }

    .customer-selection {
        padding: 20px;
        border-bottom: 1px solid #eee;
    }

    .selection-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }

    .selection-header h4 {
        margin: 0;
        color: #2c3e50;
    }

    .selection-actions {
        display: flex;
        gap: 10px;
    }

    .search-customers {
        margin-bottom: 20px;
    }

    .search-customers input {
        background: #f8f9fa;
        border: 2px solid #e9ecef;
    }

    .customers-list {
        max-height: 400px;
        overflow-y: auto;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        background: #fafafa;
    }

    .customer-item {
        border-bottom: 1px solid #e9ecef;
        transition: background-color 0.2s ease;
    }

    .customer-item:hover {
        background: #f0f7ff;
    }

    .customer-item:last-child {
        border-bottom: none;
    }

    .customer-checkbox {
        display: flex;
        align-items: center;
        padding: 15px;
        cursor: pointer;
        margin: 0;
        width: 100%;
    }

    .customer-checkbox input[type="checkbox"] {
        margin-right: 15px;
        width: 18px;
        height: 18px;
        accent-color: #667eea;
    }

    .customer-info {
        flex: 1;
    }

    .customer-name {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 4px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .source-badge {
        font-size: 10px;
        font-weight: 500;
        padding: 2px 6px;
        border-radius: 10px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .source-badge.users {
        background: #e3f2fd;
        color: #1976d2;
    }

    .source-badge.customers {
        background: #f3e5f5;
        color: #7b1fa2;
    }

    .customer-email {
        color: #6c757d;
        margin-bottom: 4px;
        font-size: 14px;
    }

    .customer-meta {
        font-size: 12px;
        color: #adb5bd;
    }

    .email-content {
        padding: 20px;
    }

    .editor-toolbar {
        display: flex;
        gap: 5px;
        margin-bottom: 10px;
        padding: 10px;
        background: #f8f9fa;
        border-radius: 8px 8px 0 0;
        border: 1px solid #e9ecef;
        border-bottom: none;
    }

    .email-editor {
        min-height: 250px;
        border-radius: 0 0 8px 8px;
        border-top: none;
        padding: 20px;
        line-height: 1.6;
    }

    .email-editor:focus {
        outline: none;
    }

    .email-editor[contenteditable]:empty::before {
        content: attr(placeholder);
        color: #adb5bd;
    }

    .editor-help {
        margin-top: 5px;
    }

    .form-actions {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid #e9ecef;
    }

    .progress-bar {
        width: 100%;
        height: 20px;
        background: #e9ecef;
        border-radius: 10px;
        overflow: hidden;
        margin-bottom: 15px;
    }

    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #4CAF50, #45a049);
        width: 0%;
        transition: width 0.3s ease;
    }

    .progress-text {
        text-align: center;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 10px;
    }

    .progress-details {
        font-size: 14px;
        color: #6c757d;
        text-align: center;
    }

    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
    }

    .modal-content {
        background-color: white;
        margin: 5% auto;
        border-radius: 10px;
        width: 80%;
        max-width: 700px;
        max-height: 80vh;
        overflow-y: auto;
    }

    .modal-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-radius: 10px 10px 0 0;
    }

    .modal-header h3 {
        margin: 0;
    }

    .close {
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
        line-height: 1;
    }

    .close:hover {
        opacity: 0.7;
    }

    .modal-body {
        padding: 20px;
    }

    .modal-footer {
        padding: 20px;
        border-top: 1px solid #e9ecef;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }

    .preview-controls {
        margin-bottom: 20px;
    }

    .preview-controls label {
        display: block;
        margin-bottom: 5px;
        font-weight: 600;
    }

    .email-preview {
        border: 1px solid #e9ecef;
        border-radius: 8px;
        overflow: hidden;
    }

    .email-header {
        background: #f8f9fa;
        padding: 15px;
        border-bottom: 1px solid #e9ecef;
        font-size: 14px;
    }

    .email-body-preview {
        padding: 20px;
        min-height: 200px;
        line-height: 1.6;
    }

    /* Reports Section Styles */
    .reports-content {
        padding: 20px;
    }

    .reports-filters {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .filter-row {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr 1fr;
        gap: 15px;
        margin-bottom: 15px;
    }

    .search-group,
    .date-group,
    .status-group {
        display: flex;
        flex-direction: column;
    }

    .search-group label,
    .date-group label,
    .status-group label {
        font-weight: 600;
        margin-bottom: 5px;
        color: #2c3e50;
        font-size: 14px;
    }

    .filter-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .results-count {
        font-size: 14px;
        color: #6c757d;
    }

    .btn-outline-danger {
        background: transparent;
        color: #dc3545;
        border: 1px solid #dc3545;
        padding: 4px 8px;
        font-size: 12px;
    }

    .btn-outline-danger:hover {
        background: #dc3545;
        color: white;
    }

    .table-container {
        overflow-x: auto;
        border: 1px solid #e9ecef;
        border-radius: 8px;
    }

    .reports-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
    }

    .reports-table th {
        background: #f8f9fa;
        padding: 15px 12px;
        text-align: left;
        font-weight: 600;
        color: #2c3e50;
        border-bottom: 2px solid #e9ecef;
        cursor: pointer;
        user-select: none;
        transition: background 0.3s ease;
    }

    .reports-table th:hover {
        background: #e9ecef;
    }

    .reports-table th i {
        margin-left: 5px;
        color: #adb5bd;
    }

    .reports-table td {
        padding: 12px;
        border-bottom: 1px solid #f1f3f4;
        vertical-align: middle;
    }

    .report-row:hover {
        background: #f8f9fa;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-sent {
        background: #d4edda;
        color: #155724;
    }

    .status-failed {
        background: #f8d7da;
        color: #721c24;
    }

    .text-muted {
        color: #6c757d !important;
    }

    #error-modal .modal-header {
        background: #dc3545;
    }

    #error-message {
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 6px;
        padding: 15px;
        font-family: monospace;
        font-size: 13px;
        color: #721c24;
        max-height: 200px;
        overflow-y: auto;
    }

    @media (max-width: 768px) {
        .main-content {
            margin-left: 0;
            padding: 15px;
        }

        .smtp-form .form-row {
            grid-template-columns: 1fr;
        }

        .page-header {
            flex-direction: column;
            gap: 15px;
            align-items: stretch;
        }

        .card-header {
            flex-direction: column;
            gap: 15px;
            align-items: stretch;
        }

        .recipient-stats {
            flex-direction: column;
            gap: 10px;
            align-items: stretch;
        }

        .selection-header {
            flex-direction: column;
            gap: 15px;
            align-items: stretch;
        }

        .form-actions {
            justify-content: stretch;
            flex-direction: column;
        }

        .modal-content {
            width: 95%;
            margin: 10px auto;
        }
    }
</style>

<script>
    function toggleSMTPConfig() {
        const config = document.getElementById('smtp-config');
        config.style.display = config.style.display === 'none' ? 'block' : 'none';
    }

    function selectAll() {
        const checkboxes = document.querySelectorAll('input[name="recipients[]"]');
        checkboxes.forEach(checkbox => {
            checkbox.checked = true;
        });
        updateSelectedCount();
    }

    function selectNone() {
        const checkboxes = document.querySelectorAll('input[name="recipients[]"]');
        checkboxes.forEach(checkbox => {
            checkbox.checked = false;
        });
        updateSelectedCount();
    }

    function updateSelectedCount() {
        const selected = document.querySelectorAll('input[name="recipients[]"]:checked').length;
        document.getElementById('selected-count').textContent = selected;
    }

    document.getElementById('customer-search').addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const customers = document.querySelectorAll('.customer-item');

        customers.forEach(customer => {
            const name = customer.dataset.name;
            const email = customer.dataset.email;
            const matches = name.includes(searchTerm) || email.includes(searchTerm);
            customer.style.display = matches ? 'block' : 'none';
        });
    });

    function formatText(command) {
        document.execCommand(command, false, null);
        document.getElementById('email_body').focus();
    }

    function insertTemplate(type) {
        const editor = document.getElementById('email_body');
        let template = '';

        switch (type) {
            case 'greeting':
                template = 'Dear {name},\n\nWe hope this email finds you well. ';
                break;
            case 'promotion':
                template = 'Special offer just for you! Get 20% off your next purchase with code SAVE20. ';
                break;
        }

        const selection = window.getSelection();
        if (selection.rangeCount > 0) {
            const range = selection.getRangeAt(0);
            range.deleteContents();
            range.insertNode(document.createTextNode(template));
            range.collapse(false);
            selection.removeAllRanges();
            selection.addRange(range);
        } else {
            editor.innerHTML += template;
        }

        editor.focus();
    }

    function previewEmail() {
        const subject = document.getElementById('email_subject').value;
        const body = document.getElementById('email_body').innerHTML;
        const selectedCustomers = Array.from(document.querySelectorAll('input[name="recipients[]"]:checked'));

        if (!subject || !body.trim()) {
            alert('Please enter both subject and message.');
            return;
        }

        if (selectedCustomers.length === 0) {
            alert('Please select at least one recipient.');
            return;
        }

        const previewSelect = document.getElementById('preview-customer');
        previewSelect.innerHTML = '<option value="">Select a customer...</option>';

        selectedCustomers.forEach(checkbox => {
            const option = document.createElement('option');
            option.value = checkbox.dataset.name;
            option.textContent = `${checkbox.dataset.name} (${checkbox.dataset.email})`;
            previewSelect.appendChild(option);
        });

        document.getElementById('preview-from').textContent =
            '<?php echo htmlspecialchars($smtp_settings['from_name']); ?> <<?php echo htmlspecialchars($smtp_settings['from_email']); ?>>';
        document.getElementById('preview-subject').textContent = subject;

        document.getElementById('email-preview-modal').style.display = 'block';

        previewSelect.addEventListener('change', function() {
            const customerName = this.value;
            let previewBody = body;
            if (customerName) {
                previewBody = previewBody.replace(/{name}/g, customerName);
            }
            document.getElementById('preview-body').innerHTML = previewBody;
        });
    }

    function closeModal() {
        document.getElementById('email-preview-modal').style.display = 'none';
    }

    function testSMTPConnection() {
        const smtpHost = document.getElementById('smtp_host').value;
        const smtpPort = document.getElementById('smtp_port').value;
        const smtpUsername = document.getElementById('smtp_username').value;
        const smtpPassword = document.getElementById('smtp_password').value;

        if (!smtpHost || !smtpUsername || !smtpPassword) {
            alert('Please fill in all SMTP configuration fields before testing.');
            return;
        }

        const testButton = document.querySelector('.btn[onclick="testSMTPConnection()"]');
        const originalText = testButton.innerHTML;
        testButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Testing...';
        testButton.disabled = true;

        const testData = {
            host: smtpHost,
            port: smtpPort,
            username: smtpUsername,
            password: smtpPassword
        };

        fetch('php/test_smtp.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(testData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ SMTP Connection Successful!\n\nYour SMTP configuration is working correctly.');
                } else {
                    alert('❌ SMTP Connection Failed!\n\nError: ' + data.message);
                }
            })
            .catch(error => {
                alert('❌ Error testing SMTP connection:\n\n' + error.message);
            })
            .finally(() => {
                testButton.innerHTML = originalText;
                testButton.disabled = false;
            });
    }

    function loadTemplate() {
        const templateSelect = document.getElementById('template-select');
        const templateId = templateSelect.value;

        if (!templateId) {
            alert('Please select a template first.');
            return;
        }

        fetch(`php/get_email_templates.php?action=get&id=${templateId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('email_subject').value = data.template.subject;
                    document.getElementById('email_body').innerHTML = data.template.body;
                    alert('Template loaded successfully!');
                } else {
                    alert('Failed to load template: ' + data.message);
                }
            })
            .catch(error => {
                alert('Error loading template: ' + error.message);
            });
    }

    function sendEmails() {
        const subject = document.getElementById('email_subject').value;
        const body = document.getElementById('email_body').innerHTML;
        const selectedCustomers = Array.from(document.querySelectorAll('input[name="recipients[]"]:checked'));

        if (!subject || !body.trim()) {
            alert('Please enter both subject and message.');
            return;
        }

        if (selectedCustomers.length === 0) {
            alert('Please select at least one recipient.');
            return;
        }

        if (!confirm(`Are you sure you want to send this email to ${selectedCustomers.length} customers?`)) {
            return;
        }

        const recipients = selectedCustomers.map(checkbox => ({
            id: checkbox.value,
            email: checkbox.dataset.email,
            name: checkbox.dataset.name,
            source: checkbox.dataset.source
        }));

        const emailData = {
            subject: subject,
            body: body,
            recipients: recipients
        };

        document.getElementById('email-status').style.display = 'block';
        document.getElementById('progress-text').textContent = 'Preparing to send emails...';
        document.getElementById('progress-fill').style.width = '0%';

        fetch('php/send_marketing_emails.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(emailData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('progress-fill').style.width = '100%';
                    document.getElementById('progress-text').textContent = 'All emails sent successfully!';
                    document.getElementById('progress-details').innerHTML =
                        `<div style="color: #4CAF50;">✓ ${data.sent} emails sent successfully</div>` +
                        (data.failed > 0 ? `<div style="color: #f44336;">✗ ${data.failed} emails failed</div>` : '');

                    setTimeout(() => {
                        document.getElementById('email_subject').value = '';
                        document.getElementById('email_body').innerHTML = '';
                        selectNone();
                        document.getElementById('email-status').style.display = 'none';
                    }, 3000);
                } else {
                    document.getElementById('progress-text').textContent = 'Failed to send emails';
                    document.getElementById('progress-details').innerHTML =
                        `<div style="color: #f44336;">Error: ${data.message}</div>`;
                }
            })
            .catch(error => {
                document.getElementById('progress-text').textContent = 'Error occurred';
                document.getElementById('progress-details').innerHTML =
                    `<div style="color: #f44336;">Error: ${error.message}</div>`;
            });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const checkboxes = document.querySelectorAll('input[name="recipients[]"]');
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', updateSelectedCount);
        });

        updateSelectedCount();

        window.addEventListener('click', function(event) {
            const modal = document.getElementById('email-preview-modal');
            if (event.target === modal) {
                closeModal();
            }

            const errorModal = document.getElementById('error-modal');
            if (event.target === errorModal) {
                closeErrorModal();
            }
        });

        // Initialize reports filtering
        filterReports();
    });

    // Reports functionality
    function filterReports() {
        const searchTerm = document.getElementById('search-reports').value.toLowerCase();
        const dateFrom = document.getElementById('date-from').value;
        const dateTo = document.getElementById('date-to').value;
        const statusFilter = document.getElementById('status-filter').value;

        const rows = document.querySelectorAll('.report-row');
        let visibleCount = 0;

        rows.forEach(row => {
            const email = row.dataset.email;
            const subject = row.dataset.subject;
            const status = row.dataset.status;
            const date = row.dataset.date;

            let visible = true;

            // Text search
            if (searchTerm && !email.includes(searchTerm) && !subject.includes(searchTerm)) {
                visible = false;
            }

            // Date range filter
            if (dateFrom && date < dateFrom) {
                visible = false;
            }
            if (dateTo && date > dateTo) {
                visible = false;
            }

            // Status filter
            if (statusFilter && status !== statusFilter) {
                visible = false;
            }

            row.style.display = visible ? '' : 'none';
            if (visible) visibleCount++;
        });

        document.getElementById('visible-count').textContent = visibleCount;
        document.getElementById('total-count').textContent = rows.length;

        // Hide "no data" row if we have visible rows
        const noDataRow = document.getElementById('no-reports-row');
        if (noDataRow) {
            noDataRow.style.display = visibleCount > 0 ? 'none' : '';
        }
    }

    function clearFilters() {
        document.getElementById('search-reports').value = '';
        document.getElementById('date-from').value = '';
        document.getElementById('date-to').value = '';
        document.getElementById('status-filter').value = '';
        filterReports();
    }

    function sortTable(columnIndex) {
        const table = document.getElementById('reports-table');
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('.report-row'));

        rows.sort((a, b) => {
            const aVal = a.cells[columnIndex].textContent.trim();
            const bVal = b.cells[columnIndex].textContent.trim();

            if (columnIndex === 0) { // Date column
                return new Date(bVal) - new Date(aVal); // Newest first
            } else {
                return aVal.localeCompare(bVal);
            }
        });

        // Re-append sorted rows
        rows.forEach(row => tbody.appendChild(row));

        // Update sort indicators
        table.querySelectorAll('th i').forEach(icon => {
            icon.className = 'fas fa-sort';
        });

        const currentIcon = table.querySelectorAll('th')[columnIndex].querySelector('i');
        currentIcon.className = 'fas fa-sort-down';
    }

    function showError(errorMessage) {
        document.getElementById('error-message').textContent = errorMessage;
        document.getElementById('error-modal').style.display = 'block';
    }

    function closeErrorModal() {
        document.getElementById('error-modal').style.display = 'none';
    }
</script>

<?php require_once 'footer.php'; ?>