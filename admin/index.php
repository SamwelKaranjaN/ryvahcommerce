<?php
require_once 'php/session_check.php';
require_once '../config/database.php';

// Initialize database connection
$conn = getDBConnection();
?>
<script type="text/javascript">
var gk_isXlsx = false;
var gk_xlsxFileLookup = {};
var gk_fileData = {};

function filledCell(cell) {
    return cell !== '' && cell != null;
}

function loadFileData(filename) {
    if (gk_isXlsx && gk_xlsxFileLookup[filename]) {
        try {
            var workbook = XLSX.read(gk_fileData[filename], {
                type: 'base64'
            });
            var firstSheetName = workbook.SheetNames[0];
            var worksheet = workbook.Sheets[firstSheetName];

            // Convert sheet to JSON to filter blank rows
            var jsonData = XLSX.utils.sheet_to_json(worksheet, {
                header: 1,
                blankrows: false,
                defval: ''
            });
            // Filter out blank rows (rows where all cells are empty, null, or undefined)
            var filteredData = jsonData.filter(row => row.some(filledCell));

            // Heuristic to find the header row by ignoring rows with fewer filled cells than the next row
            var headerRowIndex = filteredData.findIndex((row, index) =>
                row.filter(filledCell).length >= filteredData[index + 1]?.filter(filledCell).length
            );
            // Fallback
            if (headerRowIndex === -1 || headerRowIndex > 25) {
                headerRowIndex = 0;
            }

            // Convert filtered JSON back to CSV
            var csv = XLSX.utils.aoa_to_sheet(filteredData.slice(
                headerRowIndex)); // Create a new sheet from filtered array of arrays
            csv = XLSX.utils.sheet_to_csv(csv, {
                header: 1
            });
            return csv;
        } catch (e) {
            console.error(e);
            return "";
        }
    }
    return gk_fileData[filename] || "";
}
</script>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Paint & eBook Admin</title>
    <style>
    /* Modern Dashboard Styles */
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
        --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
        --transition-fast: 150ms cubic-bezier(0.4, 0, 0.2, 1);
        --transition-normal: 300ms cubic-bezier(0.4, 0, 0.2, 1);
        --transition-slow: 500ms cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Main Content Styles */
    .main-content {
        margin-left: 260px;
        margin-top: 56px;
        padding: 2rem;
        min-height: calc(100vh - 56px);
        background: var(--bg-secondary);
        transition: margin-left var(--transition-normal);
        animation: fadeIn 0.5s ease-in;
    }

    .main-content.collapsed {
        margin-left: 60px;
    }

    .main-content h1 {
        font-size: 2rem;
        color: var(--text-primary);
        margin-bottom: 1.5rem;
        font-weight: 600;
        background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .main-content h2 {
        font-size: 1.5rem;
        color: var(--text-primary);
        margin: 1.5rem 0 1rem;
        font-weight: 500;
    }

    /* Summary Cards */
    .summary-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .card {
        background: var(--bg-primary);
        border-radius: 1rem;
        padding: 1.5rem;
        box-shadow: var(--shadow-lg);
        transition: transform var(--transition-normal), box-shadow var(--transition-normal);
        position: relative;
        overflow: hidden;
        border: 1px solid var(--border-color);
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-xl);
    }

    .card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
    }

    .card h3 {
        font-size: 1.1rem;
        color: var(--text-secondary);
        margin-bottom: 0.75rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .card p {
        font-size: 2rem;
        color: var(--text-primary);
        font-weight: 600;
        margin: 0;
    }

    .card i {
        font-size: 1.5rem;
        color: var(--primary-color);
        background: var(--bg-secondary);
        padding: 0.75rem;
        border-radius: 0.75rem;
    }

    /* Dashboard Sections */
    .dashboard-section {
        background: var(--bg-primary);
        border-radius: 1rem;
        padding: 1.5rem;
        margin-bottom: 2rem;
        box-shadow: var(--shadow-lg);
        border: 1px solid var(--border-color);
    }

    /* Activity Table */
    .activity-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        background: var(--bg-primary);
        border-radius: 0.75rem;
        overflow: hidden;
    }

    .activity-table th,
    .activity-table td {
        padding: 1rem 1.5rem;
        text-align: left;
        font-size: 0.875rem;
    }

    .activity-table th {
        background: var(--bg-secondary);
        color: var(--text-secondary);
        font-weight: 500;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
    }

    .activity-table td {
        border-bottom: 1px solid var(--border-color);
        color: var(--text-primary);
    }

    .activity-table tr:last-child td {
        border-bottom: none;
    }

    .activity-table tr:hover {
        background: var(--bg-secondary);
    }

    /* Status Badges */
    .payment_status-badge {
        padding: 0.5rem 1rem;
        border-radius: 2rem;
        font-size: 0.75rem;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .payment_status-badge.completed {
        background: var(--success-color);
        color: white;
    }

    .payment_status-badge.pending {
        background: var(--warning-color);
        color: white;
    }

    .payment_status-badge.cancelled {
        background: var(--danger-color);
        color: white;
    }

    .payment_status-badge.critical {
        background: var(--danger-color);
        color: white;
    }

    .payment_status-badge.warning {
        background: var(--warning-color);
        color: white;
    }

    /* Responsive Design */
    @media (min-width: 1200px) {
        .main-content {
            max-width: 1400px;
            margin-left: 280px;
            margin-right: auto;
        }

        .main-content.collapsed {
            margin-left: 60px;
        }

        .summary-cards {
            grid-template-columns: repeat(4, 1fr);
        }
    }

    @media (max-width: 992px) {
        .main-content {
            margin-left: 200px;
            padding: 1.5rem;
        }

        .main-content.collapsed {
            margin-left: 60px;
        }

        .summary-cards {
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        }
    }

    @media (max-width: 768px) {
        .main-content {
            margin-left: 60px;
            padding: 1rem;
        }

        .summary-cards {
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        }

        .activity-table {
            display: block;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .dashboard-section {
            padding: 1rem;
        }

        .card {
            padding: 1.25rem;
        }

        .card p {
            font-size: 1.75rem;
        }
    }

    @media (max-width: 480px) {
        .main-content {
            padding: 0.75rem;
        }

        .summary-cards {
            grid-template-columns: 1fr;
        }

        .card {
            padding: 1rem;
        }

        .activity-table th,
        .activity-table td {
            padding: 0.75rem;
            font-size: 0.75rem;
        }

        .payment_status-badge {
            padding: 0.375rem 0.75rem;
            font-size: 0.7rem;
        }
    }

    /* Animations */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Scrollbar Styling */
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
</head>

<body>
    <?php include 'header.php'; ?>

    <!-- Main Content -->
    <div class="main-content" id="main-content">
        <h1>Dashboard Overview</h1>

        <!-- Quick Stats Section -->
        <div class="summary-cards">
            <div class="card">
                <h3><i class="fas fa-box"></i> Total Products</h3>
                <?php
                $query = "SELECT COUNT(*) as total FROM products";
                $result = mysqli_query($conn, $query);
                $total_products = mysqli_fetch_assoc($result)['total'];
                ?>
                <p><?php echo $total_products; ?></p>
            </div>

            <div class="card">
                <h3><i class="fas fa-shopping-cart"></i> Total Orders</h3>
                <?php
                $query = "SELECT COUNT(*) as total FROM orders";
                $result = mysqli_query($conn, $query);
                $total_orders = mysqli_fetch_assoc($result)['total'];
                ?>
                <p><?php echo $total_orders; ?></p>
            </div>

            <div class="card">
                <h3><i class="fas fa-users"></i> Total Customers</h3>
                <?php
                $query = "SELECT COUNT(*) as total FROM users WHERE role = 'client'";
                $result = mysqli_query($conn, $query);
                $total_customers = mysqli_fetch_assoc($result)['total'];
                ?>
                <p><?php echo $total_customers; ?></p>
            </div>

        </div>

        <!-- Recent Orders Section -->
        <div class="dashboard-section">
            <h2>Recent Orders</h2>
            <div class="table-responsive">
                <table class="activity-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "SELECT o.*, u.email 
                                 FROM orders o 
                                 JOIN users u ON o.user_id = u.id 
                                 ORDER BY o.created_at DESC 
                                 LIMIT 5";
                        $result = mysqli_query($conn, $query);
                        while ($order = mysqli_fetch_assoc($result)) {
                            $status_class = strtolower($order['payment_status']);
                            echo "<tr>
                                <td>#{$order['id']}</td>
                                <td>{$order['email']}</td>
                                <td>$" . number_format($order['total_amount'], 2) . "</td>
                                <td><span class='payment_status-badge {$status_class}'>{$order['payment_status']}</span></td>
                                <td>" . date('M d, Y', strtotime($order['created_at'])) . "</td>
                            </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Low Stock Products Section -->
        <div class="dashboard-section">
            <h2>Low Stock Products</h2>
            <div class="table-responsive">
                <table class="activity-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Current Stock</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "SELECT * FROM products WHERE stock_quantity <= 10 ORDER BY stock_quantity ASC LIMIT 5";
                        $result = mysqli_query($conn, $query);
                        while ($product = mysqli_fetch_assoc($result)) {
                            $stock_status = $product['stock_quantity'] <= 5 ? 'critical' : 'warning';
                            echo "<tr>
                                <td>{$product['name']}</td>
                                <td>{$product['stock_quantity']}</td>
                                <td><span class='payment_status-badge {$stock_status}'>" . 
                                    ($stock_status == 'critical' ? 'Critical' : 'Low') . "</span></td>
                                <td><a href='Product.php?action=edit&id={$product['id']}' class='btn-edit'>Update Stock</a></td>
                            </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>