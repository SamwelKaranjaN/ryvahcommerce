<?php
session_start();
require_once 'php/db_connect.php';

// Check if admin is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Admin') {
    header('Location: login.php');
    exit();
}

require_once 'header.php';

// Get summary statistics
$stats_query = "
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as sent,
        SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed
    FROM marketing_email_logs
";
$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();
$success_rate = $stats['total'] > 0 ? round(($stats['sent'] / $stats['total']) * 100, 1) : 0;
?>

<div class="main-content" id="main-content">
    <div class="page-header">
        <h2><i class="fas fa-chart-bar"></i> Email Delivery Reports</h2>
        <div class="header-actions">
            <button class="btn btn-secondary" onclick="location.reload()">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
            <a href="marketing_emails.php" class="btn btn-primary">
                <i class="fas fa-envelope"></i> Send New Email
            </a>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="content-card">
        <div class="card-header">
            <h3><i class="fas fa-chart-line"></i> Delivery Statistics</h3>
        </div>
        <div class="stats-content">
            <div class="stat-item">
                <div class="stat-number"><?php echo $stats['total']; ?></div>
                <div class="stat-label">Total Emails</div>
            </div>
            <div class="stat-item success">
                <div class="stat-number"><?php echo $stats['sent']; ?></div>
                <div class="stat-label">Successfully Sent</div>
            </div>
            <div class="stat-item failed">
                <div class="stat-number"><?php echo $stats['failed']; ?></div>
                <div class="stat-label">Failed</div>
            </div>
            <div class="stat-item rate">
                <div class="stat-number"><?php echo $success_rate; ?>%</div>
                <div class="stat-label">Success Rate</div>
            </div>
        </div>
    </div>

    <!-- Delivery Reports Table -->
    <div class="content-card">
        <div class="card-header">
            <h3><i class="fas fa-list-alt"></i> Delivery History</h3>
        </div>

        <div class="reports-content">
            <!-- Search and Filter Controls -->
            <div class="reports-filters">
                <div class="filter-row">
                    <div class="search-group">
                        <label>Search Reports</label>
                        <input type="text" id="search-reports" class="form-control"
                            placeholder="Search by email, subject..." onkeyup="filterReports()">
                    </div>

                    <div class="date-group">
                        <label>From Date</label>
                        <input type="date" id="date-from" class="form-control" onchange="filterReports()">
                    </div>

                    <div class="date-group">
                        <label>To Date</label>
                        <input type="date" id="date-to" class="form-control" onchange="filterReports()">
                    </div>

                    <div class="status-group">
                        <label>Status</label>
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
            <div class="table-container">
                <table class="reports-table" id="reports-table">
                    <thead>
                        <tr>
                            <th onclick="sortTable(0)">Date & Time <i class="fas fa-sort"></i></th>
                            <th onclick="sortTable(1)">Recipient Email <i class="fas fa-sort"></i></th>
                            <th onclick="sortTable(2)">Subject <i class="fas fa-sort"></i></th>
                            <th onclick="sortTable(3)">Status <i class="fas fa-sort"></i></th>
                            <th onclick="sortTable(4)">Admin <i class="fas fa-sort"></i></th>
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
                                mel.sent_at,
                                u.full_name as admin_name
                            FROM marketing_email_logs mel
                            LEFT JOIN users u ON mel.admin_id = u.id
                            ORDER BY mel.sent_at DESC
                            LIMIT 100
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

                            <td>
                                <div class="date"><?php echo date('M d, Y', strtotime($report['sent_at'])); ?></div>
                                <div class="time"><?php echo date('h:i A', strtotime($report['sent_at'])); ?></div>
                            </td>

                            <td>
                                <i class="fas fa-envelope"></i>
                                <?php echo htmlspecialchars($report['recipient_email']); ?>
                            </td>

                            <td title="<?php echo htmlspecialchars($report['subject']); ?>">
                                <?php echo htmlspecialchars(substr($report['subject'], 0, 40)); ?>
                                <?php if (strlen($report['subject']) > 40): ?>...<?php endif; ?>
                            </td>

                            <td>
                                <span class="status-badge <?php echo $status_class; ?>">
                                    <i class="fas <?php echo $status_icon; ?>"></i>
                                    <?php echo ucfirst($report['status']); ?>
                                </span>
                            </td>

                            <td><?php echo htmlspecialchars($report['admin_name'] ?? 'Unknown'); ?></td>

                            <td>
                                <?php if ($report['status'] === 'failed' && $report['error_message']): ?>
                                <button class="btn btn-sm btn-outline-danger"
                                    onclick="showError('<?php echo htmlspecialchars(addslashes($report['error_message'])); ?>')">
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
                            <td colspan="6" style="text-align: center; padding: 40px;">
                                <div>
                                    <i class="fas fa-inbox"
                                        style="font-size: 3em; color: #adb5bd; margin-bottom: 15px;"></i>
                                    <h4>No Email Reports Found</h4>
                                    <p>No marketing emails have been sent yet. <a href="marketing_emails.php">Send your
                                            first email</a></p>
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
            <h3><i class="fas fa-exclamation-triangle"></i> Error Details</h3>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>
        <div class="modal-body">
            <div id="error-message"></div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal()">Close</button>
        </div>
    </div>
</div>

<style>
.main-content {
    padding: 20px;
    margin-left: 260px;
    margin-top: 60px;
    background: #f8f9fa;
    transition: margin-left 0.3s ease;
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

.header-actions {
    display: flex;
    gap: 10px;
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
}

.card-header h3 {
    margin: 0;
}

.stats-content {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    padding: 20px;
}

.stat-item {
    text-align: center;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
}

.stat-item.success {
    border-left: 4px solid #28a745;
}

.stat-item.failed {
    border-left: 4px solid #dc3545;
}

.stat-item.rate {
    border-left: 4px solid #ffc107;
}

.stat-number {
    font-size: 2em;
    font-weight: bold;
    color: #2c3e50;
}

.stat-label {
    color: #6c757d;
    margin-top: 5px;
}

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
}

.form-control {
    padding: 10px;
    border: 2px solid #e9ecef;
    border-radius: 6px;
    font-size: 14px;
}

.form-control:focus {
    outline: none;
    border-color: #667eea;
}

.filter-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.btn {
    padding: 8px 16px;
    border: none;
    border-radius: 6px;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 14px;
}

.btn-primary {
    background: #667eea;
    color: white;
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-outline-secondary {
    background: transparent;
    color: #6c757d;
    border: 1px solid #6c757d;
}

.btn-outline-danger {
    background: transparent;
    color: #dc3545;
    border: 1px solid #dc3545;
    padding: 4px 8px;
    font-size: 12px;
}

.btn-sm {
    padding: 6px 12px;
    font-size: 12px;
}

.table-container {
    overflow-x: auto;
    border: 1px solid #e9ecef;
    border-radius: 8px;
}

.reports-table {
    width: 100%;
    border-collapse: collapse;
}

.reports-table th {
    background: #f8f9fa;
    padding: 15px 12px;
    text-align: left;
    font-weight: 600;
    color: #2c3e50;
    border-bottom: 2px solid #e9ecef;
    cursor: pointer;
}

.reports-table th:hover {
    background: #e9ecef;
}

.reports-table td {
    padding: 12px;
    border-bottom: 1px solid #f1f3f4;
}

.report-row:hover {
    background: #f8f9fa;
}

.date {
    font-weight: 600;
    color: #2c3e50;
}

.time {
    font-size: 12px;
    color: #6c757d;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
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
    color: #6c757d;
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
    margin: 10% auto;
    border-radius: 10px;
    width: 80%;
    max-width: 600px;
}

.modal-header {
    background: #dc3545;
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
    cursor: pointer;
}

.modal-body {
    padding: 20px;
}

.modal-footer {
    padding: 20px;
    border-top: 1px solid #e9ecef;
    text-align: right;
}

#error-message {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 6px;
    padding: 15px;
    font-family: monospace;
    font-size: 13px;
}
</style>

<script>
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

        if (searchTerm && !email.includes(searchTerm) && !subject.includes(searchTerm)) {
            visible = false;
        }

        if (dateFrom && date < dateFrom) {
            visible = false;
        }
        if (dateTo && date > dateTo) {
            visible = false;
        }

        if (statusFilter && status !== statusFilter) {
            visible = false;
        }

        row.style.display = visible ? '' : 'none';
        if (visible) visibleCount++;
    });

    document.getElementById('visible-count').textContent = visibleCount;
    document.getElementById('total-count').textContent = rows.length;
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

        if (columnIndex === 0) {
            return new Date(bVal) - new Date(aVal); // Newest first
        } else {
            return aVal.localeCompare(bVal);
        }
    });

    rows.forEach(row => tbody.appendChild(row));
}

function showError(errorMessage) {
    document.getElementById('error-message').textContent = errorMessage;
    document.getElementById('error-modal').style.display = 'block';
}

function closeModal() {
    document.getElementById('error-modal').style.display = 'none';
}

document.addEventListener('DOMContentLoaded', function() {
    filterReports();

    window.addEventListener('click', function(event) {
        const modal = document.getElementById('error-modal');
        if (event.target === modal) {
            closeModal();
        }
    });
});
</script>

<?php require_once 'footer.php'; ?>