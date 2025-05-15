<?php
require_once 'header.php';
require_once 'php/db_connect.php';

// Handle order status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];
    $notes = $_POST['notes'] ?? '';
    
    // Update order status
    $stmt = $conn->prepare("UPDATE orders SET payment_status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $order_id);
    $stmt->execute();
    
    // Add to status history
    $stmt = $conn->prepare("INSERT INTO order_status_history (order_id, status, notes) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $order_id, $new_status, $notes);
    $stmt->execute();
}

// Get filter parameters
$status_filter = $_GET['status'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$search = $_GET['search'] ?? '';

// Build query with filters
$query = "SELECT o.*, u.full_name, u.email, u.phone, o.payment_status as status 
          FROM orders o 
          JOIN users u ON o.user_id = u.id 
          WHERE 1=1";

if ($status_filter) {
    $query .= " AND o.payment_status = '$status_filter'";
}
if ($date_from) {
    $query .= " AND DATE(o.created_at) >= '$date_from'";
}
if ($date_to) {
    $query .= " AND DATE(o.created_at) <= '$date_to'";
}
if ($search) {
    $query .= " AND (o.id LIKE '%$search%' OR u.full_name LIKE '%$search%' OR u.email LIKE '%$search%')";
}

$query .= " ORDER BY o.created_at DESC";
$result = mysqli_query($conn, $query);

// Get order statistics
$stats_query = "SELECT 
    COUNT(*) as total_orders,
    SUM(CASE WHEN payment_status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
    SUM(CASE WHEN payment_status = 'processing' THEN 1 ELSE 0 END) as processing_orders,
    SUM(CASE WHEN payment_status = 'completed' THEN 1 ELSE 0 END) as completed_orders,
    SUM(total_amount) as total_revenue
    FROM orders";
$stats_result = mysqli_query($conn, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);
?>

<div class="main-content" id="main-content">
    <div class="page-header">
        <h2><i class="fas fa-shopping-cart"></i> Orders Management</h2>
        <div class="header-actions">
            <button class="btn btn-primary" onclick="exportOrders()">
                <i class="fas fa-download"></i> Export Orders
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-cards">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-shopping-bag"></i></div>
            <div class="stat-info">
                <h3>Total Orders</h3>
                <p id="totalOrders"><?php echo number_format($stats['total_orders']); ?></p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-clock"></i></div>
            <div class="stat-info">
                <h3>Pending Orders</h3>
                <p id="pendingOrders"><?php echo number_format($stats['pending_orders']); ?></p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-spinner"></i></div>
            <div class="stat-info">
                <h3>Processing</h3>
                <p id="processingOrders"><?php echo number_format($stats['processing_orders']); ?></p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
            <div class="stat-info">
                <h3>Completed</h3>
                <p id="completedOrders"><?php echo number_format($stats['completed_orders']); ?></p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-dollar-sign"></i></div>
            <div class="stat-info">
                <h3>Total Revenue</h3>
                <p id="totalRevenue">$<?php echo number_format($stats['total_revenue'], 2); ?></p>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="filters-section">
        <form id="filtersForm" class="filters-form">
            <div class="filter-group">
                <input type="text" id="searchInput" placeholder="Search orders..." class="search-input">
            </div>
            <div class="filter-group">
                <select id="statusFilter" class="filter-select">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="processing">Processing</option>
                    <option value="completed">Completed</option>
                    <option value="failed">Failed</option>
                    <option value="refunded">Refunded</option>
                </select>
            </div>
            <div class="filter-group">
                <input type="date" id="dateFrom" class="date-input" placeholder="From Date">
            </div>
            <div class="filter-group">
                <input type="date" id="dateTo" class="date-input" placeholder="To Date">
            </div>
            <button type="button" class="btn btn-secondary" onclick="resetFilters()">Reset</button>
        </form>
    </div>

    <!-- Orders Table -->
    <div class="content-card">
        <div class="table-responsive">
            <table class="data-table" id="ordersTable">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Contact</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = mysqli_fetch_assoc($result)): ?>
                    <tr data-order-id="<?php echo $order['id']; ?>"
                        data-status="<?php echo $order['status']; ?>"
                        data-date="<?php echo $order['created_at']; ?>"
                        data-customer="<?php echo htmlspecialchars($order['full_name'] . ' ' . $order['email']); ?>">
                        <td>#<?php echo $order['id']; ?></td>
                        <td>
                            <div class="customer-info">
                                <strong><?php echo htmlspecialchars($order['full_name']); ?></strong>
                                <small><?php echo htmlspecialchars($order['email']); ?></small>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($order['phone']); ?></td>
                        <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                        <td>
                            <form method="POST" class="status-form">
                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                <select name="status" onchange="updateOrderStatus(this)" class="status-select status-<?php echo $order['status']; ?>">
                                    <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                    <option value="completed" <?php echo $order['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                    <option value="failed" <?php echo $order['status'] === 'failed' ? 'selected' : ''; ?>>Failed</option>
                                    <option value="refunded" <?php echo $order['status'] === 'refunded' ? 'selected' : ''; ?>>Refunded</option>
                                </select>
                                <input type="hidden" name="update_status" value="1">
                            </form>
                        </td>
                        <td>
                            <div class="date-info">
                                <span class="date"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></span>
                                <span class="time"><?php echo date('h:i A', strtotime($order['created_at'])); ?></span>
                            </div>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-sm btn-info" onclick="viewOrder(<?php echo $order['id']; ?>)">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-success" onclick="printOrder(<?php echo $order['id']; ?>)">
                                    <i class="fas fa-print"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteOrder(<?php echo $order['id']; ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <div id="noResults" class="no-results" style="display: none;">
            <i class="fas fa-search"></i>
            <p>No orders found matching your criteria</p>
        </div>
    </div>
</div>

<style>
/* Base Styles */
.main-content {
    padding: var(--spacing-xl);
    margin-left: 250px;
    margin-top: 50px;
    min-height: calc(100vh - 60px);
    background: var(--bg-tertiary);
    transition: var(--transition-normal);
}

.main-content.collapsed {
    margin-left: 60px;
}

/* Enhanced Header Section */
.page-header {
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

/* Statistics Cards */
.stats-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: #fff;
    border-radius: 1rem;
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 12px rgba(0,0,0,0.15);
}

.stat-icon {
    width: 3rem;
    height: 3rem;
    border-radius: 0.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    background: rgba(52, 152, 219, 0.1);
    color: #3498db;
}

.stat-info h3 {
    font-size: 0.875rem;
    color: #666;
    margin-bottom: 0.5rem;
}

.stat-info p {
    font-size: 1.5rem;
    font-weight: 600;
    color: #2c3e50;
    margin: 0;
}

/* Filters Section */
.filters-section {
    background: #fff;
    padding: 1.5rem;
    border-radius: 1rem;
    margin-bottom: 2rem;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.filters-form {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    align-items: center;
}

.filter-group {
    flex: 1;
    min-width: 200px;
}

.search-input,
.filter-select,
.date-input {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 1px solid #e2e8f0;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
}

.search-input:focus,
.filter-select:focus,
.date-input:focus {
    border-color: #3498db;
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
    outline: none;
}

/* Table Styles */
.table-responsive {
    overflow-x: auto;
    background: #fff;
    border-radius: 1rem;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.data-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}

.data-table th {
    background: #f8f9fa;
    padding: 1rem;
    text-align: left;
    font-weight: 600;
    color: #2c3e50;
    border-bottom: 2px solid #e2e8f0;
    white-space: nowrap;
}

.data-table td {
    padding: 1rem;
    border-bottom: 1px solid #e2e8f0;
    vertical-align: middle;
}

/* Status Select */
.status-select {
    padding: 0.5rem 1rem;
    border-radius: 2rem;
    border: none;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
}

.status-pending { background: #fff3cd; color: #856404; }
.status-processing { background: #cce5ff; color: #004085; }
.status-completed { background: #d4edda; color: #155724; }
.status-failed { background: #f8d7da; color: #721c24; }
.status-refunded { background: #e2e3e5; color: #383d41; }

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: 0.5rem;
}

.btn-sm {
    padding: 0.5rem;
    font-size: 0.875rem;
    border-radius: 0.5rem;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2rem;
    height: 2rem;
}

.btn-info { background: #17a2b8; color: #fff; }
.btn-success { background: #28a745; color: #fff; }
.btn-danger { background: #dc3545; color: #fff; }

.btn-sm:hover {
    transform: translateY(-2px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

/* No Results Message */
.no-results {
    text-align: center;
    padding: 3rem;
    color: #666;
}

.no-results i {
    font-size: 3rem;
    margin-bottom: 1rem;
    color: #ccc;
}

/* Responsive Design */
@media (max-width: 1024px) {
    .main-content {
        margin-left: 60px;
        padding: 1rem;
    }
    
    .stats-cards {
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    }
}

@media (max-width: 768px) {
    .main-content {
        margin-left: 0;
        padding: 1rem;
    }

    .page-header {
        flex-direction: column;
        align-items: stretch;
        gap: 1rem;
    }

    .filters-form {
        flex-direction: column;
    }

    .filter-group {
        width: 100%;
    }

    .action-buttons {
        flex-wrap: wrap;
    }
}

@media (max-width: 480px) {
    .stat-card {
        padding: 1rem;
    }

    .stat-icon {
        width: 2.5rem;
        height: 2.5rem;
        font-size: 1.25rem;
    }

    .stat-info p {
        font-size: 1.25rem;
    }

    .btn-sm {
        width: 1.75rem;
        height: 1.75rem;
        padding: 0.375rem;
    }
}
</style>

<script>
// Store all orders data
let allOrders = [];
let filteredOrders = [];

// Initialize the orders data
document.addEventListener('DOMContentLoaded', function() {
    // Get all orders from the table
    const table = document.getElementById('ordersTable');
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
    
    allOrders = Array.from(rows).map(row => ({
        element: row,
        id: row.dataset.orderId,
        status: row.dataset.status,
        date: row.dataset.date,
        customer: row.dataset.customer.toLowerCase()
    }));
    
    filteredOrders = [...allOrders];
    
    // Add event listeners for filters
    document.getElementById('searchInput').addEventListener('input', filterOrders);
    document.getElementById('statusFilter').addEventListener('change', filterOrders);
    document.getElementById('dateFrom').addEventListener('change', filterOrders);
    document.getElementById('dateTo').addEventListener('change', filterOrders);
});

function filterOrders() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const statusFilter = document.getElementById('statusFilter').value;
    const dateFrom = document.getElementById('dateFrom').value;
    const dateTo = document.getElementById('dateTo').value;
    
    filteredOrders = allOrders.filter(order => {
        // Search filter
        if (searchTerm && !order.customer.includes(searchTerm)) {
            return false;
        }
        
        // Status filter
        if (statusFilter && order.status !== statusFilter) {
            return false;
        }
        
        // Date range filter
        const orderDate = new Date(order.date);
        if (dateFrom && orderDate < new Date(dateFrom)) {
            return false;
        }
        if (dateTo && orderDate > new Date(dateTo + 'T23:59:59')) {
            return false;
        }
        
        return true;
    });
    
    updateTable();
    updateStats();
}

function updateTable() {
    const tbody = document.getElementById('ordersTable').getElementsByTagName('tbody')[0];
    const noResults = document.getElementById('noResults');
    
    // Clear existing rows
    tbody.innerHTML = '';
    
    if (filteredOrders.length === 0) {
        noResults.style.display = 'block';
    } else {
        noResults.style.display = 'none';
        filteredOrders.forEach(order => {
            tbody.appendChild(order.element.cloneNode(true));
        });
    }
}

function updateStats() {
    const stats = {
        total: filteredOrders.length,
        pending: filteredOrders.filter(order => order.status === 'pending').length,
        processing: filteredOrders.filter(order => order.status === 'processing').length,
        completed: filteredOrders.filter(order => order.status === 'completed').length
    };
    
    document.getElementById('totalOrders').textContent = stats.total;
    document.getElementById('pendingOrders').textContent = stats.pending;
    document.getElementById('processingOrders').textContent = stats.processing;
    document.getElementById('completedOrders').textContent = stats.completed;
}

function resetFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('statusFilter').value = '';
    document.getElementById('dateFrom').value = '';
    document.getElementById('dateTo').value = '';
    
    filteredOrders = [...allOrders];
    updateTable();
    updateStats();
}

function updateOrderStatus(select) {
    const form = select.closest('form');
    const orderId = form.querySelector('input[name="order_id"]').value;
    const newStatus = select.value;
    
    // Show confirmation dialog
    if (confirm(`Are you sure you want to update the order status to "${newStatus}"?`)) {
        // Add notes prompt
        const notes = prompt('Add a note for this status change (optional):');
        
        // Create hidden input for notes
        let notesInput = form.querySelector('input[name="notes"]');
        if (!notesInput) {
            notesInput = document.createElement('input');
            notesInput.type = 'hidden';
            notesInput.name = 'notes';
            form.appendChild(notesInput);
        }
        notesInput.value = notes || '';
        
        // Submit the form
        form.submit();
    } else {
        // Reset select to previous value
        select.value = select.getAttribute('data-previous-value');
    }
}

function viewOrder(orderId) {
    window.location.href = `order-details.php?id=${orderId}`;
}

function printOrder(orderId) {
    window.open(`print-order.php?id=${orderId}`, '_blank');
}

function deleteOrder(orderId) {
    if (confirm('Are you sure you want to delete this order? This action cannot be undone.')) {
        fetch('php/delete_order.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ order_id: orderId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error deleting order: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the order.');
        });
    }
}

function exportOrders() {
    window.location.href = 'php/export_orders.php';
}

// Store previous status value before change
document.querySelectorAll('.status-select').forEach(select => {
    select.addEventListener('focus', function() {
        this.setAttribute('data-previous-value', this.value);
    });
});

document.addEventListener('DOMContentLoaded', function() {
    // Handle sidenav collapse
    const sidenav = document.getElementById('sidenav');
    const mainContent = document.getElementById('main-content');
    
    if (sidenav && sidenav.classList.contains('collapsed')) {
        mainContent.classList.add('collapsed');
    }

    // Add event listener for sidenav toggle
    const toggleBtn = document.querySelector('.navbar-toggler');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function() {
            if (mainContent) {
                mainContent.classList.toggle('collapsed');
            }
        });
    }
});
</script>

<?php require_once 'footer.php'; ?> 