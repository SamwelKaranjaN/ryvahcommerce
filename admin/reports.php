<?php
require_once 'php/session_check.php';
require_once '../config/database.php';

// Initialize database connection
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get date range from request or default to current month
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Financial Reports - Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
    :root {
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
        --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
        --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
        --transition-normal: 300ms cubic-bezier(0.4, 0, 0.2, 1);
    }

    .main-content {
        margin-left: 260px;
        padding: 2rem;
        min-height: calc(100vh - 56px);
        background: var(--bg-secondary);
        transition: margin-left var(--transition-normal);
    }

    .main-content.collapsed {
        margin-left: 60px;
    }

    .report-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        background: var(--bg-primary);
        padding: 1.5rem;
        border-radius: 0.5rem;
        box-shadow: var(--shadow-md);
    }

    .date-filter {
        display: flex;
        gap: 1rem;
        align-items: center;
    }

    .date-filter input {
        padding: 0.5rem;
        border: 1px solid var(--border-color);
        border-radius: 0.375rem;
        transition: border-color var(--transition-normal);
    }

    .date-filter input:focus {
        outline: none;
        border-color: var(--primary-color);
    }

    .date-filter button {
        padding: 0.5rem 1rem;
        background: var(--primary-color);
        color: white;
        border: none;
        border-radius: 0.375rem;
        cursor: pointer;
        transition: background-color var(--transition-normal);
    }

    .date-filter button:hover {
        background: var(--primary-hover);
    }

    .reports-container {
        display: flex;
        gap: 2rem;
    }

    .reports-grid {
        flex: 1;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
    }

    .report-card {
        background: var(--bg-primary);
        padding: 1.5rem;
        border-radius: 0.5rem;
        box-shadow: var(--shadow-md);
        transition: transform var(--transition-normal), box-shadow var(--transition-normal);
        position: relative;
        overflow: hidden;
        text-decoration: none;
        color: var(--text-primary);
    }

    .report-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-lg);
    }

    .report-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
    }

    .report-card h3 {
        color: var(--text-primary);
        font-size: 1.25rem;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .report-card p {
        color: var(--text-secondary);
        font-size: 0.875rem;
        margin-bottom: 1rem;
    }

    .report-card .actions {
        display: flex;
        gap: 0.5rem;
    }

    .report-card .actions button {
        padding: 0.5rem 1rem;
        border: none;
        border-radius: 0.375rem;
        cursor: pointer;
        font-size: 0.875rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: background-color var(--transition-normal);
    }

    .report-card .actions .view-btn {
        background: var(--primary-color);
        color: white;
    }

    .report-card .actions .print-btn {
        background: var(--bg-secondary);
        color: var(--text-primary);
    }

    .report-card .actions button:hover {
        opacity: 0.9;
    }

    @media print {
        .main-content {
            margin-left: 0;
            padding: 0;
        }

        .report-header,
        .date-filter,
        .actions {
            display: none;
        }

        .report-card {
            break-inside: avoid;
            page-break-inside: avoid;
        }
    }

    @media (max-width: 768px) {
        .main-content {
            margin-left: 60px;
            padding: 1rem;
        }

        .report-header {
            flex-direction: column;
            gap: 1rem;
        }

        .date-filter {
            width: 100%;
            flex-wrap: wrap;
        }

        .date-filter input {
            flex: 1;
            min-width: 200px;
        }

        .reports-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 1200px) {
        .reports-container {
            flex-direction: column;
        }
    }

    .report-viewer {
        flex: 2;
        background: var(--bg-primary);
        border-radius: 0.5rem;
        box-shadow: var(--shadow-md);
        padding: 1.5rem;
        margin-top: 1rem;
    }

    .report-viewer-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid var(--border-color);
    }

    .report-actions {
        display: flex;
        gap: 0.5rem;
    }

    .action-btn {
        padding: 0.5rem 1rem;
        border: none;
        border-radius: 0.375rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        background: var(--primary-color);
        color: white;
        transition: background-color var(--transition-normal);
    }

    .action-btn:hover {
        background: var(--primary-hover);
    }

    .report-content {
        min-height: 500px;
    }
    </style>
</head>

<body>
    <?php include 'header.php'; ?>

    <div class="main-content" id="main-content">
        <div class="report-header">
            <h1><i class="fas fa-chart-line"></i> Financial Reports</h1>
            <form class="date-filter" method="GET">
                <input type="date" name="start_date" value="<?php echo $start_date; ?>">
                <input type="date" name="end_date" value="<?php echo $end_date; ?>">
                <button type="submit"><i class="fas fa-filter"></i> Apply Filter</button>
            </form>
        </div>

        <div class="reports-container">
            <div class="reports-grid">
                <!-- Income Statement -->
                <div class="report-card" data-report="income_statement">
                    <h3><i class="fas fa-file-invoice-dollar"></i> Income Statement</h3>
                    <p>Shows revenues, expenses, and net profit/loss over a period.</p>
                    <div class="actions">
                        <button class="view-btn" onclick="loadReport('income_statement')">
                            <i class="fas fa-eye"></i> View Report
                        </button>
                    </div>
                </div>

                <!-- Balance Sheet -->
                <div class="report-card" data-report="balance_sheet">
                    <h3><i class="fas fa-balance-scale"></i> Balance Sheet</h3>
                    <p>Displays the company's financial position at a specific point in time.</p>
                    <div class="actions">
                        <button class="view-btn" onclick="loadReport('balance_sheet')">
                            <i class="fas fa-eye"></i> View Report
                        </button>
                    </div>
                </div>

                <!-- Cash Flow Statement -->
                <div class="report-card" data-report="cash_flow">
                    <h3><i class="fas fa-money-bill-wave"></i> Cash Flow Statement</h3>
                    <p>Tracks inflows and outflows of cash.</p>
                    <div class="actions">
                        <button class="view-btn" onclick="loadReport('cash_flow')">
                            <i class="fas fa-eye"></i> View Report
                        </button>
                    </div>
                </div>

                <!-- Accounts Receivable -->
                <div class="report-card" data-report="accounts_receivable">
                    <h3><i class="fas fa-hand-holding-usd"></i> Accounts Receivable</h3>
                    <p>Shows money owed by customers with aging summaries.</p>
                    <div class="actions">
                        <button class="view-btn" onclick="loadReport('accounts_receivable')">
                            <i class="fas fa-eye"></i> View Report
                        </button>
                    </div>
                </div>

                <!-- Accounts Payable -->
                <div class="report-card" data-report="accounts_payable">
                    <h3><i class="fas fa-file-invoice"></i> Accounts Payable</h3>
                    <p>Displays amounts the business owes to suppliers.</p>
                    <div class="actions">
                        <button class="view-btn" onclick="loadReport('accounts_payable')">
                            <i class="fas fa-eye"></i> View Report
                        </button>
                    </div>
                </div>

                <!-- Trial Balance -->
                <div class="report-card" data-report="trial_balance">
                    <h3><i class="fas fa-calculator"></i> Trial Balance</h3>
                    <p>A list of all ledger accounts and their balances.</p>
                    <div class="actions">
                        <button class="view-btn" onclick="loadReport('trial_balance')">
                            <i class="fas fa-eye"></i> View Report
                        </button>
                    </div>
                </div>

                <!-- General Ledger -->
                <div class="report-card" data-report="general_ledger">
                    <h3><i class="fas fa-book"></i> General Ledger</h3>
                    <p>Detailed record of all financial transactions by account.</p>
                    <div class="actions">
                        <button class="view-btn" onclick="loadReport('general_ledger')">
                            <i class="fas fa-eye"></i> View Report
                        </button>
                    </div>
                </div>

                <!-- Budget vs Actual -->
                <div class="report-card" data-report="budget_vs_actual">
                    <h3><i class="fas fa-chart-bar"></i> Budget vs Actual</h3>
                    <p>Compares planned budgets with actual financial performance.</p>
                    <div class="actions">
                        <button class="view-btn" onclick="loadReport('budget_vs_actual')">
                            <i class="fas fa-eye"></i> View Report
                        </button>
                    </div>
                </div>

                <!-- Expense Reports -->
                <div class="report-card" data-report="expense_reports">
                    <h3><i class="fas fa-receipt"></i> Expense Reports</h3>
                    <p>Breakdown of expenses by category or department.</p>
                    <div class="actions">
                        <button class="view-btn" onclick="loadReport('expense_reports')">
                            <i class="fas fa-eye"></i> View Report
                        </button>
                    </div>
                </div>

                <!-- Tax Reports -->
                <div class="report-card" data-report="tax_reports">
                    <h3><i class="fas fa-file-invoice-dollar"></i> Tax Reports</h3>
                    <p>Detailed tax information and calculations.</p>
                    <div class="actions">
                        <button class="view-btn" onclick="loadReport('tax_reports')">
                            <i class="fas fa-eye"></i> View Report
                        </button>
                    </div>
                </div>
            </div>

            <!-- Dynamic Report Viewer -->
            <div id="report-viewer" class="report-viewer" style="display: none;">
                <div class="report-viewer-header">
                    <h2 id="report-title"></h2>
                    <div class="report-actions">
                        <button onclick="printReport()" class="action-btn">
                            <i class="fas fa-print"></i> Print
                        </button>
                        <button onclick="downloadPDF()" class="action-btn">
                            <i class="fas fa-file-pdf"></i> Download PDF
                        </button>
                        <button onclick="closeReport()" class="action-btn">
                            <i class="fas fa-times"></i> Close
                        </button>
                    </div>
                </div>
                <div id="report-content" class="report-content">
                    <!-- Report content will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <script src="js/idle-timeout.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM loaded, initializing idle timeout...');

        // Initialize idle timeout
        const idleTimeout = new IdleTimeout({
            idleTime: 30000, // 30 seconds
            warningTime: 10000, // 10 seconds warning
            logoutUrl: 'logout.php',
            warningMessage: 'You have been inactive for a while. Click anywhere or press "Cancel" to stay logged in. You will be logged out in 10 seconds.'
        });

        // Handle sidebar collapse
        const mainContent = document.getElementById('main-content');
        const sidebarToggle = document.querySelector('.toggle-btn');
        const sidenav = document.querySelector('.sidenav');

        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function() {
                mainContent.classList.toggle('collapsed');
                sidenav.classList.toggle('collapsed');
            });
        }

        // Check if sidebar should be collapsed on page load
        const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        if (isCollapsed) {
            mainContent.classList.add('collapsed');
            sidenav.classList.add('collapsed');
        }

        // Save sidebar state
        sidebarToggle.addEventListener('click', function() {
            const isCollapsed = sidenav.classList.contains('collapsed');
            localStorage.setItem('sidebarCollapsed', isCollapsed);
        });
    });

    // Function to load report content
    function loadReport(reportType) {
        const reportViewer = document.getElementById('report-viewer');
        const reportTitle = document.getElementById('report-title');
        const reportContent = document.getElementById('report-content');

        // Show loading state
        reportContent.innerHTML = '<div class="loading">Loading report...</div>';
        reportViewer.style.display = 'block';

        // Set the report title
        const title = document.querySelector(`[data-report="${reportType}"] h3`).textContent;
        reportTitle.textContent = title;

        // Fetch report content
        fetch(
                `get_report.php?type=${reportType}&start_date=${document.querySelector('input[name="start_date"]').value}&end_date=${document.querySelector('input[name="end_date"]').value}`
            )
            .then(response => response.text())
            .then(html => {
                reportContent.innerHTML = html;
            })
            .catch(error => {
                reportContent.innerHTML = `<div class="error">Error loading report: ${error.message}</div>`;
            });
    }

    // Function to close report viewer
    function closeReport() {
        document.getElementById('report-viewer').style.display = 'none';
    }

    // Function to print report
    function printReport() {
        window.print();
    }

    // Function to download PDF
    function downloadPDF() {
        const reportContent = document.getElementById('report-content');
        const reportTitle = document.getElementById('report-title').textContent;

        // You can implement PDF generation here using a library like jsPDF
        // For now, we'll just show an alert
        alert('PDF download functionality will be implemented here');
    }
    </script>
</body>

</html>