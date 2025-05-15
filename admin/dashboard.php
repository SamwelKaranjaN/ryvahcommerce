<?php
require_once 'header.php';
require_once 'php/db_connect.php';

// Fetch dashboard statistics
$stats = [
    'total_orders' => $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'],
    'total_revenue' => $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE status != 'cancelled'")->fetch_assoc()['total'] ?? 0,
    'total_customers' => $conn->query("SELECT COUNT(*) as count FROM customers")->fetch_assoc()['count'],
    'total_products' => $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count']
];

// Fetch recent orders
$recent_orders = $conn->query("
    SELECT o.*, c.first_name, c.last_name 
    FROM orders o 
    JOIN customers c ON o.customer_id = c.id 
    ORDER BY o.created_at DESC 
    LIMIT 5
");

// Fetch low stock products
$low_stock_products = $conn->query("
    SELECT * FROM products 
    WHERE stock_quantity <= 10 
    ORDER BY stock_quantity ASC 
    LIMIT 5
");

// Fetch monthly revenue data for chart
$monthly_revenue = $conn->query("
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month,
        SUM(total_amount) as total
    FROM orders 
    WHERE status != 'cancelled'
    AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY month
    ORDER BY month ASC
");

$chart_labels = [];
$chart_data = [];
while ($row = $monthly_revenue->fetch_assoc()) {
    $chart_labels[] = date('M Y', strtotime($row['month'] . '-01'));
    $chart_data[] = $row['total'];
}
?>

<div class="main-content">
    <div class="page-header">
        <h2>Dashboard</h2>
        <div class="header-actions">
            <button class="btn btn-primary" onclick="refreshDashboard()">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <div class="stat-details">
                <h3>Total Orders</h3>
                <p class="stat-number"><?php echo number_format($stats['total_orders']); ?></p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="stat-details">
                <h3>Total Revenue</h3>
                <p class="stat-number">$<?php echo number_format($stats['total_revenue'], 2); ?></p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-details">
                <h3>Total Customers</h3>
                <p class="stat-number"><?php echo number_format($stats['total_customers']); ?></p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-box"></i>
            </div>
            <div class="stat-details">
                <h3>Total Products</h3>
                <p class="stat-number"><?php echo number_format($stats['total_products']); ?></p>
            </div>
        </div>
    </div>

    <!-- Revenue Chart -->
    <div class="content-card">
        <h3>Revenue Overview</h3>
        <canvas id="revenueChart"></canvas>
    </div>

    <div class="dashboard-grid">
        <!-- Recent Orders -->
        <div class="content-card">
            <h3>Recent Orders</h3>
            <div class="table-responsive">
                <table class="data-table">
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
                        <?php while ($order = $recent_orders->fetch_assoc()): ?>
                        <tr>
                            <td>#<?php echo $order['id']; ?></td>
                            <td><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></td>
                            <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo $order['status']; ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Low Stock Products -->
        <div class="content-card">
            <h3>Low Stock Products</h3>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Stock</th>
                            <th>Price</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($product = $low_stock_products->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td>
                                <span class="stock-badge <?php echo $product['stock_quantity'] <= 5 ? 'critical' : 'low'; ?>">
                                    <?php echo $product['stock_quantity']; ?>
                                </span>
                            </td>
                            <td>$<?php echo number_format($product['price'], 2); ?></td>
                            <td>
                                <button class="btn btn-sm btn-primary" onclick="restockProduct(<?php echo $product['id']; ?>)">
                                    Restock
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 20px;
}

.stat-icon {
    width: 50px;
    height: 50px;
    background: #3498db;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 1.5em;
}

.stat-details h3 {
    font-size: 0.9em;
    color: #666;
    margin-bottom: 5px;
}

.stat-number {
    font-size: 1.5em;
    font-weight: 600;
    color: #2c3e50;
}

.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 20px;
    margin-top: 30px;
}

.status-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.8em;
    font-weight: 500;
}

.status-pending { background: #f1c40f; color: #fff; }
.status-processing { background: #3498db; color: #fff; }
.status-shipped { background: #9b59b6; color: #fff; }
.status-delivered { background: #2ecc71; color: #fff; }
.status-cancelled { background: #e74c3c; color: #fff; }

.stock-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.8em;
    font-weight: 500;
}

.stock-badge.critical { background: #e74c3c; color: #fff; }
.stock-badge.low { background: #f39c12; color: #fff; }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Initialize Revenue Chart
const ctx = document.getElementById('revenueChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($chart_labels); ?>,
        datasets: [{
            label: 'Monthly Revenue',
            data: <?php echo json_encode($chart_data); ?>,
            borderColor: '#3498db',
            backgroundColor: 'rgba(52, 152, 219, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '$' + value.toLocaleString();
                    }
                }
            }
        }
    }
});

function refreshDashboard() {
    location.reload();
}

function restockProduct(productId) {
    window.location.href = `edit-product.php?id=${productId}`;
}
</script>

<?php require_once 'footer.php'; ?> 