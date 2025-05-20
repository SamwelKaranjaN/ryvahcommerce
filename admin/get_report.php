<?php
require_once 'php/session_check.php';
require_once '../config/database.php';

// Initialize database connection
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get parameters
$report_type = isset($_GET['type']) ? $_GET['type'] : '';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');

// Function to format currency
function formatCurrency($amount)
{
    return number_format($amount, 2);
}

// Function to get report data based on type
function getReportData($conn, $report_type, $start_date, $end_date)
{
    switch ($report_type) {
        case 'income_statement':
            return getIncomeStatement($conn, $start_date, $end_date);
        case 'balance_sheet':
            return getBalanceSheet($conn);
        case 'cash_flow':
            return getCashFlow($conn, $start_date, $end_date);
        case 'accounts_receivable':
            return getAccountsReceivable($conn);
        case 'accounts_payable':
            return getAccountsPayable($conn);
        case 'trial_balance':
            return getTrialBalance($conn);
        case 'general_ledger':
            return getGeneralLedger($conn, $start_date, $end_date);
        case 'budget_vs_actual':
            return getBudgetVsActual($conn, $start_date, $end_date);
        case 'expense_reports':
            return getExpenseReports($conn, $start_date, $end_date);
        case 'tax_reports':
            return getTaxReports($conn, $start_date, $end_date);
        default:
            return ['error' => 'Invalid report type'];
    }
}

// Get Income Statement data
function getIncomeStatement($conn, $start_date, $end_date)
{
    $data = [];

    // Get revenue
    $revenue_query = "SELECT SUM(amount) as total FROM transactions 
                     WHERE type = 'revenue' AND date BETWEEN ? AND ?";
    $stmt = $conn->prepare($revenue_query);
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $revenue = $stmt->get_result()->fetch_assoc()['total'] ?? 0;

    // Get expenses
    $expenses_query = "SELECT category, SUM(amount) as total FROM transactions 
                      WHERE type = 'expense' AND date BETWEEN ? AND ?
                      GROUP BY category";
    $stmt = $conn->prepare($expenses_query);
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $expenses_result = $stmt->get_result();
    $expenses = [];
    while ($row = $expenses_result->fetch_assoc()) {
        $expenses[$row['category']] = $row['total'];
    }

    $data['revenue'] = $revenue;
    $data['expenses'] = $expenses;
    $data['net_income'] = $revenue - array_sum($expenses);

    return $data;
}

// Get Balance Sheet data
function getBalanceSheet($conn)
{
    $data = [];

    // Get assets
    $assets_query = "SELECT category, SUM(amount) as total FROM transactions 
                    WHERE type = 'asset'
                    GROUP BY category";
    $result = $conn->query($assets_query);
    $data['assets'] = [];
    while ($row = $result->fetch_assoc()) {
        $data['assets'][$row['category']] = $row['total'];
    }

    // Get liabilities
    $liabilities_query = "SELECT category, SUM(amount) as total FROM transactions 
                         WHERE type = 'liability'
                         GROUP BY category";
    $result = $conn->query($liabilities_query);
    $data['liabilities'] = [];
    while ($row = $result->fetch_assoc()) {
        $data['liabilities'][$row['category']] = $row['total'];
    }

    // Get equity
    $equity_query = "SELECT category, SUM(amount) as total FROM transactions 
                    WHERE type = 'equity'
                    GROUP BY category";
    $result = $conn->query($equity_query);
    $data['equity'] = [];
    while ($row = $result->fetch_assoc()) {
        $data['equity'][$row['category']] = $row['total'];
    }

    return $data;
}

// Get Cash Flow data
function getCashFlow($conn, $start_date, $end_date)
{
    $data = [];

    // Get operating activities
    $operating_query = "SELECT category, SUM(amount) as total FROM transactions 
                       WHERE type IN ('revenue', 'expense') 
                       AND date BETWEEN ? AND ?
                       GROUP BY category";
    $stmt = $conn->prepare($operating_query);
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();
    $data['operating'] = [];
    while ($row = $result->fetch_assoc()) {
        $data['operating'][$row['category']] = $row['total'];
    }

    // Get investing activities
    $investing_query = "SELECT category, SUM(amount) as total FROM transactions 
                       WHERE type = 'investment' 
                       AND date BETWEEN ? AND ?
                       GROUP BY category";
    $stmt = $conn->prepare($investing_query);
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();
    $data['investing'] = [];
    while ($row = $result->fetch_assoc()) {
        $data['investing'][$row['category']] = $row['total'];
    }

    // Get financing activities
    $financing_query = "SELECT category, SUM(amount) as total FROM transactions 
                       WHERE type = 'financing' 
                       AND date BETWEEN ? AND ?
                       GROUP BY category";
    $stmt = $conn->prepare($financing_query);
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();
    $data['financing'] = [];
    while ($row = $result->fetch_assoc()) {
        $data['financing'][$row['category']] = $row['total'];
    }

    return $data;
}

// Get Accounts Receivable data
function getAccountsReceivable($conn)
{
    $data = [];

    $query = "SELECT customer_id, customer_name, SUM(amount) as total, 
              MAX(date) as last_payment 
              FROM transactions 
              WHERE type = 'receivable' 
              GROUP BY customer_id, customer_name";
    $result = $conn->query($query);

    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    return $data;
}

// Get Accounts Payable data
function getAccountsPayable($conn)
{
    $data = [];

    $query = "SELECT vendor_id, vendor_name, SUM(amount) as total, 
              MAX(date) as last_payment 
              FROM transactions 
              WHERE type = 'payable' 
              GROUP BY vendor_id, vendor_name";
    $result = $conn->query($query);

    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    return $data;
}

// Get Trial Balance data
function getTrialBalance($conn)
{
    $data = [];

    $query = "SELECT account_code, account_name, 
              SUM(CASE WHEN type = 'debit' THEN amount ELSE 0 END) as debit,
              SUM(CASE WHEN type = 'credit' THEN amount ELSE 0 END) as credit
              FROM transactions 
              GROUP BY account_code, account_name";
    $result = $conn->query($query);

    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    return $data;
}

// Get General Ledger data
function getGeneralLedger($conn, $start_date, $end_date)
{
    $data = [];

    $query = "SELECT t.date, t.account_code, t.account_name, 
              t.description, t.debit, t.credit, t.balance
              FROM transactions t
              WHERE t.date BETWEEN ? AND ?
              ORDER BY t.date, t.account_code";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    return $data;
}

// Get Budget vs Actual data
function getBudgetVsActual($conn, $start_date, $end_date)
{
    $data = [];

    $query = "SELECT b.category, b.amount as budgeted,
              COALESCE(SUM(t.amount), 0) as actual
              FROM budgets b
              LEFT JOIN transactions t ON b.category = t.category 
              AND t.date BETWEEN ? AND ?
              WHERE b.period_start <= ? AND b.period_end >= ?
              GROUP BY b.category, b.amount";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssss", $start_date, $end_date, $end_date, $start_date);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    return $data;
}

// Get Expense Reports data
function getExpenseReports($conn, $start_date, $end_date)
{
    $data = [];

    $query = "SELECT category, SUM(amount) as total,
              COUNT(*) as transaction_count
              FROM transactions 
              WHERE type = 'expense' 
              AND date BETWEEN ? AND ?
              GROUP BY category";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    return $data;
}

// Get Tax Reports data
function getTaxReports($conn, $start_date, $end_date)
{
    $data = [];

    $query = "SELECT tax_type, tax_rate, 
              SUM(taxable_amount) as taxable_amount,
              SUM(tax_amount) as tax_amount
              FROM tax_transactions 
              WHERE date BETWEEN ? AND ?
              GROUP BY tax_type, tax_rate";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    return $data;
}

// Get the report data
$report_data = getReportData($conn, $report_type, $start_date, $end_date);

// Generate HTML based on report type
switch ($report_type) {
    case 'income_statement':
?>
<div class="report-section">
    <h3>Revenue</h3>
    <p>Total Revenue: $<?php echo formatCurrency($report_data['revenue']); ?></p>

    <h3>Expenses</h3>
    <table class="report-table">
        <thead>
            <tr>
                <th>Category</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($report_data['expenses'] as $category => $amount): ?>
            <tr>
                <td><?php echo htmlspecialchars($category); ?></td>
                <td>$<?php echo formatCurrency($amount); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h3>Net Income</h3>
    <p>Total Net Income: $<?php echo formatCurrency($report_data['net_income']); ?></p>
</div>
<?php
        break;

    case 'balance_sheet':
    ?>
<div class="report-section">
    <h3>Assets</h3>
    <table class="report-table">
        <thead>
            <tr>
                <th>Category</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($report_data['assets'] as $category => $amount): ?>
            <tr>
                <td><?php echo htmlspecialchars($category); ?></td>
                <td>$<?php echo formatCurrency($amount); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h3>Liabilities</h3>
    <table class="report-table">
        <thead>
            <tr>
                <th>Category</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($report_data['liabilities'] as $category => $amount): ?>
            <tr>
                <td><?php echo htmlspecialchars($category); ?></td>
                <td>$<?php echo formatCurrency($amount); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h3>Equity</h3>
    <table class="report-table">
        <thead>
            <tr>
                <th>Category</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($report_data['equity'] as $category => $amount): ?>
            <tr>
                <td><?php echo htmlspecialchars($category); ?></td>
                <td>$<?php echo formatCurrency($amount); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php
        break;

    // Add cases for other report types...
    default:
        echo '<div class="error">Invalid report type</div>';
}
?>

<style>
.report-section {
    padding: 1rem;
}

.report-table {
    width: 100%;
    border-collapse: collapse;
    margin: 1rem 0;
}

.report-table th,
.report-table td {
    padding: 0.75rem;
    border: 1px solid var(--border-color);
    text-align: left;
}

.report-table th {
    background-color: var(--bg-secondary);
    font-weight: 600;
}

.report-table tr:nth-child(even) {
    background-color: var(--bg-tertiary);
}

.error {
    color: var(--danger-color);
    padding: 1rem;
    background-color: #fee2e2;
    border-radius: 0.375rem;
    margin: 1rem 0;
}

.loading {
    text-align: center;
    padding: 2rem;
    color: var(--text-secondary);
}
</style>