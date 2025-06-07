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
                    <tr data-order-id="<?php echo $order['id']; ?>" data-status="<?php echo $order['status']; ?>"
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
                                <select name="status" onchange="updateOrderStatus(this)"
                                    class="status-select status-<?php echo $order['status']; ?>">
                                    <option value="pending"
                                        <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="processing"
                                        <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Processing
                                    </option>
                                    <option value="completed"
                                        <?php echo $order['status'] === 'completed' ? 'selected' : ''; ?>>Completed
                                    </option>
                                    <option value="failed"
                                        <?php echo $order['status'] === 'failed' ? 'selected' : ''; ?>>Failed</option>
                                    <option value="refunded"
                                        <?php echo $order['status'] === 'refunded' ? 'selected' : ''; ?>>Refunded
                                    </option>
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
                                <button class="btn btn-sm btn-success"
                                    onclick="printOrder(<?php echo $order['id']; ?>)">
                                    <i class="fas fa-print"></i>
                                </button>
                                <button class="btn btn-sm btn-danger"
                                    onclick="deleteOrder(<?php echo $order['id']; ?>)">
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

<!-- Order Details Modal -->
<div id="orderDetailsModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h2><i class="fas fa-receipt"></i> Order Details</h2>
            <span class="close" onclick="closeOrderModal()">&times;</span>
        </div>
        <div class="modal-body" id="orderDetailsContent">
            <div class="loading-spinner">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Loading order details...</p>
            </div>
        </div>
    </div>
</div>

<style>
/* Base Styles */
.main-content {
    padding: var(--spacing-xl);
    margin-left: 260px;
    margin-top: 60px;
    min-height: calc(100vh - 60px);
    background: var(--bg-tertiary);
    transition: margin-left 0.3s ease;
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
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
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
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
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
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
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

/* Modal Styles */
.modal {
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(5px);
    animation: fadeIn 0.3s ease;
}

.modal-content {
    background-color: #fff;
    margin: 2% auto;
    padding: 0;
    border-radius: 1rem;
    width: 90%;
    max-width: 900px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
    animation: slideIn 0.3s ease;
}

.modal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 1.5rem 2rem;
    border-radius: 1rem 1rem 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h2 {
    margin: 0;
    font-size: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.close {
    color: white;
    font-size: 2rem;
    font-weight: bold;
    cursor: pointer;
    transition: opacity 0.3s ease;
}

.close:hover {
    opacity: 0.7;
}

.modal-body {
    padding: 2rem;
}

.loading-spinner {
    text-align: center;
    padding: 3rem;
    color: #666;
}

.loading-spinner i {
    font-size: 3rem;
    color: #3498db;
    margin-bottom: 1rem;
}

/* Order Details Content */
.order-header {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
    padding: 1.5rem;
    background: #f8f9fa;
    border-radius: 0.75rem;
}

.order-info-card {
    background: white;
    padding: 1.25rem;
    border-radius: 0.5rem;
    border-left: 4px solid #3498db;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.order-info-card h4 {
    margin: 0 0 0.5rem 0;
    color: #2c3e50;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.order-info-card p {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 600;
    color: #34495e;
}

.order-items {
    margin-bottom: 2rem;
}

.order-items h3 {
    margin-bottom: 1rem;
    color: #2c3e50;
    border-bottom: 2px solid #ecf0f1;
    padding-bottom: 0.5rem;
}

.item-card {
    display: flex;
    gap: 1rem;
    padding: 1.5rem;
    background: white;
    border-radius: 0.75rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    margin-bottom: 1rem;
    border: 1px solid #e2e8f0;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.item-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
}

.item-image {
    width: 80px;
    height: 80px;
    border-radius: 0.5rem;
    object-fit: cover;
    background: #f8f9fa;
    border: 2px solid #e2e8f0;
}

.item-details {
    flex: 1;
}

.item-name {
    font-weight: 600;
    font-size: 1.1rem;
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.item-description {
    color: #666;
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
    line-height: 1.4;
}

.item-meta {
    display: flex;
    gap: 1rem;
    font-size: 0.9rem;
    color: #666;
    flex-wrap: wrap;
}

.item-price {
    font-weight: 600;
    color: #27ae60;
    font-size: 1.1rem;
    align-self: flex-start;
}

/* Shipping Information Styles */
.shipping-info {
    margin-bottom: 2rem;
}

.shipping-info h3 {
    margin-bottom: 1rem;
    color: #2c3e50;
    border-bottom: 2px solid #ecf0f1;
    padding-bottom: 0.5rem;
}

.shipping-card {
    background: white;
    border-radius: 0.75rem;
    padding: 1.5rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    border: 1px solid #e2e8f0;
}

.shipping-details {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.shipping-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.shipping-field {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.shipping-field strong {
    color: #2c3e50;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.shipping-field span {
    color: #34495e;
    font-weight: 500;
}

.shipping-address {
    padding-top: 1rem;
    border-top: 1px solid #e2e8f0;
}

.shipping-address strong {
    color: #2c3e50;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
}

.address-text {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 0.5rem;
    border-left: 4px solid #3498db;
    font-weight: 500;
    color: #34495e;
    line-height: 1.5;
}

/* Product Type Styles */
.item-type-info {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
}

.product-type {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.375rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.875rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.product-type.ebook {
    background: #e3f2fd;
    color: #1565c0;
    border: 1px solid #bbdefb;
}

.product-type.book {
    background: #f3e5f5;
    color: #7b1fa2;
    border: 1px solid #ce93d8;
}

.product-type.paint {
    background: #e8f5e8;
    color: #2e7d32;
    border: 1px solid #a5d6a7;
}

.delivery-type {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.25rem 0.5rem;
    border-radius: 0.5rem;
    font-size: 0.8rem;
    font-weight: 500;
}

.delivery-type.digital {
    background: #fff3e0;
    color: #ef6c00;
    border: 1px solid #ffcc02;
}

.delivery-type.physical {
    background: #e1f5fe;
    color: #0277bd;
    border: 1px solid #81d4fa;
}

/* Order Actions */
.order-actions {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
    margin-top: 2rem;
}

.action-section {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 0.75rem;
    border: 1px solid #e2e8f0;
}

.action-section h4 {
    margin: 0 0 1rem 0;
    color: #2c3e50;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.status-update-form {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.form-group label {
    font-weight: 600;
    color: #34495e;
    font-size: 0.9rem;
}

.form-select,
.form-textarea {
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 0.5rem;
    font-size: 0.9rem;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
}

.form-select:focus,
.form-textarea:focus {
    border-color: #3498db;
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
    outline: none;
}

.form-textarea {
    min-height: 100px;
    resize: vertical;
}

.btn-update {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 0.5rem;
    font-weight: 600;
    cursor: pointer;
    transition: transform 0.2s ease;
}

.btn-update:hover {
    transform: translateY(-2px);
}

.btn-update:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

.notes-section {
    margin-top: 1rem;
}

.notes-history {
    max-height: 200px;
    overflow-y: auto;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 0.5rem;
    padding: 1rem;
}

.note-item {
    padding: 0.75rem 0;
    border-bottom: 1px solid #f0f0f0;
}

.note-item:last-child {
    border-bottom: none;
}

.note-date {
    font-size: 0.8rem;
    color: #666;
    margin-bottom: 0.25rem;
}

.note-text {
    color: #2c3e50;
    font-size: 0.9rem;
}

/* Animations */
@keyframes fadeIn {
    from {
        opacity: 0;
    }

    to {
        opacity: 1;
    }
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-50px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes slideInRight {
    from {
        opacity: 0;
        transform: translateX(100px);
    }

    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes slideOutRight {
    from {
        opacity: 1;
        transform: translateX(0);
    }

    to {
        opacity: 0;
        transform: translateX(100px);
    }
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

.status-pending {
    background: #fff3cd;
    color: #856404;
}

.status-processing {
    background: #cce5ff;
    color: #004085;
}

.status-completed {
    background: #d4edda;
    color: #155724;
}

.status-failed {
    background: #f8d7da;
    color: #721c24;
}

.status-refunded {
    background: #e2e3e5;
    color: #383d41;
}

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

.btn-info {
    background: #17a2b8;
    color: #fff;
}

.btn-success {
    background: #28a745;
    color: #fff;
}

.btn-danger {
    background: #dc3545;
    color: #fff;
}

.btn-sm:hover {
    transform: translateY(-2px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
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

/* Modal Styles */
.modal {
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(5px);
    animation: fadeIn 0.3s ease;
}

.modal-content {
    background-color: #fff;
    margin: 2% auto;
    padding: 0;
    border-radius: 1rem;
    width: 90%;
    max-width: 900px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
    animation: slideIn 0.3s ease;
}

.modal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 1.5rem 2rem;
    border-radius: 1rem 1rem 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h2 {
    margin: 0;
    font-size: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.close {
    color: white;
    font-size: 2rem;
    font-weight: bold;
    cursor: pointer;
    transition: opacity 0.3s ease;
}

.close:hover {
    opacity: 0.7;
}

.modal-body {
    padding: 2rem;
}

.loading-spinner {
    text-align: center;
    padding: 3rem;
    color: #666;
}

.loading-spinner i {
    font-size: 3rem;
    color: #3498db;
    margin-bottom: 1rem;
}

/* Order Details Content */
.order-header {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
    padding: 1.5rem;
    background: #f8f9fa;
    border-radius: 0.75rem;
}

.order-info-card {
    background: white;
    padding: 1.25rem;
    border-radius: 0.5rem;
    border-left: 4px solid #3498db;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.order-info-card h4 {
    margin: 0 0 0.5rem 0;
    color: #2c3e50;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.order-info-card p {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 600;
    color: #34495e;
}

.order-items {
    margin-bottom: 2rem;
}

.order-items h3 {
    margin-bottom: 1rem;
    color: #2c3e50;
    border-bottom: 2px solid #ecf0f1;
    padding-bottom: 0.5rem;
}

.item-card {
    display: flex;
    gap: 1rem;
    padding: 1.5rem;
    background: white;
    border-radius: 0.75rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    margin-bottom: 1rem;
    border: 1px solid #e2e8f0;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.item-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
}

.item-image {
    width: 80px;
    height: 80px;
    border-radius: 0.5rem;
    object-fit: cover;
    background: #f8f9fa;
}

.item-details {
    flex: 1;
}

.item-name {
    font-weight: 600;
    font-size: 1.1rem;
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.item-description {
    color: #666;
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
    line-height: 1.4;
}

.item-meta {
    display: flex;
    gap: 1rem;
    font-size: 0.9rem;
    color: #666;
}

.item-price {
    font-weight: 600;
    color: #27ae60;
    font-size: 1.1rem;
}

/* Order Actions */
.order-actions {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
    margin-top: 2rem;
}

.action-section {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 0.75rem;
    border: 1px solid #e2e8f0;
}

.action-section h4 {
    margin: 0 0 1rem 0;
    color: #2c3e50;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.status-update-form {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.form-group label {
    font-weight: 600;
    color: #34495e;
    font-size: 0.9rem;
}

.form-select,
.form-textarea {
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 0.5rem;
    font-size: 0.9rem;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
}

.form-select:focus,
.form-textarea:focus {
    border-color: #3498db;
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
    outline: none;
}

.form-textarea {
    min-height: 100px;
    resize: vertical;
}

.btn-update {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 0.5rem;
    font-weight: 600;
    cursor: pointer;
    transition: transform 0.2s ease;
}

.btn-update:hover {
    transform: translateY(-2px);
}

.notes-section {
    margin-top: 1rem;
}

.notes-history {
    max-height: 200px;
    overflow-y: auto;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 0.5rem;
    padding: 1rem;
}

.note-item {
    padding: 0.75rem 0;
    border-bottom: 1px solid #f0f0f0;
}

.note-item:last-child {
    border-bottom: none;
}

.note-date {
    font-size: 0.8rem;
    color: #666;
    margin-bottom: 0.25rem;
}

.note-text {
    color: #2c3e50;
    font-size: 0.9rem;
}

/* Animations */
@keyframes fadeIn {
    from {
        opacity: 0;
    }

    to {
        opacity: 1;
    }
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-50px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Shipping Information Styles */
.shipping-info {
    margin-bottom: 2rem;
}

.shipping-info h3 {
    margin-bottom: 1rem;
    color: #2c3e50;
    border-bottom: 2px solid #ecf0f1;
    padding-bottom: 0.5rem;
}

.shipping-card {
    background: white;
    border-radius: 0.75rem;
    padding: 1.5rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    border: 1px solid #e2e8f0;
}

.shipping-details {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.shipping-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.shipping-field {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.shipping-field strong {
    color: #2c3e50;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.shipping-field span {
    color: #34495e;
    font-weight: 500;
}

.shipping-address {
    padding-top: 1rem;
    border-top: 1px solid #e2e8f0;
}

.shipping-address strong {
    color: #2c3e50;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
}

.address-text {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 0.5rem;
    border-left: 4px solid #3498db;
    font-weight: 500;
    color: #34495e;
    line-height: 1.5;
}

/* Product Type Styles */
.item-type-info {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
}

.product-type {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.375rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.875rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.product-type.ebook {
    background: #e3f2fd;
    color: #1565c0;
    border: 1px solid #bbdefb;
}

.product-type.book {
    background: #f3e5f5;
    color: #7b1fa2;
    border: 1px solid #ce93d8;
}

.product-type.paint {
    background: #e8f5e8;
    color: #2e7d32;
    border: 1px solid #a5d6a7;
}

.delivery-type {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.25rem 0.5rem;
    border-radius: 0.5rem;
    font-size: 0.8rem;
    font-weight: 500;
}

.delivery-type.digital {
    background: #fff3e0;
    color: #ef6c00;
    border: 1px solid #ffcc02;
}

.delivery-type.physical {
    background: #e1f5fe;
    color: #0277bd;
    border: 1px solid #81d4fa;
}

/* Responsive Design */
@media (max-width: 768px) {
    .modal-content {
        width: 95%;
        margin: 1% auto;
    }

    .modal-header {
        padding: 1rem 1.5rem;
    }

    .modal-header h2 {
        font-size: 1.25rem;
    }

    .modal-body {
        padding: 1.5rem;
    }

    .order-header {
        grid-template-columns: 1fr;
    }

    .order-actions {
        grid-template-columns: 1fr;
    }

    .item-card {
        flex-direction: column;
        text-align: center;
    }

    .item-image {
        width: 100px;
        height: 100px;
        margin: 0 auto;
    }

    .item-meta {
        justify-content: center;
    }

    .shipping-row {
        grid-template-columns: 1fr;
    }

    .item-type-info {
        align-items: center;
    }

    .delivery-type {
        text-align: center;
    }
}

@media (max-width: 480px) {
    .modal-content {
        max-height: 95vh;
    }

    .modal-header {
        padding: 1rem;
    }

    .modal-body {
        padding: 1rem;
    }

    .order-info-card {
        padding: 1rem;
    }

    .item-card {
        padding: 1rem;
    }

    .action-section {
        padding: 1rem;
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
    // Show modal
    document.getElementById('orderDetailsModal').style.display = 'block';

    // Fetch order details
    fetch('php/get_order_details.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                order_id: orderId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayOrderDetails(data.order, data.items, data.notes);
            } else {
                document.getElementById('orderDetailsContent').innerHTML = `
                    <div class="error-message" style="text-align: center; padding: 2rem; color: #e74c3c;">
                        <i class="fas fa-exclamation-triangle" style="font-size: 2rem; margin-bottom: 1rem;"></i>
                        <p>Error loading order details: ${data.message}</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('orderDetailsContent').innerHTML = `
                <div class="error-message" style="text-align: center; padding: 2rem; color: #e74c3c;">
                    <i class="fas fa-exclamation-triangle" style="font-size: 2rem; margin-bottom: 1rem;"></i>
                    <p>Failed to load order details. Please try again.</p>
                </div>
            `;
        });
}

function displayOrderDetails(order, items, notes) {
    // Parse shipping address if it exists
    let shippingAddress = null;
    if (order.shipping_address) {
        try {
            shippingAddress = JSON.parse(order.shipping_address);
        } catch (e) {
            console.error('Error parsing shipping address:', e);
        }
    }

    const content = `
            <div class="order-header">
                <div class="order-info-card">
                    <h4><i class="fas fa-hashtag"></i> Order ID</h4>
                    <p>#${order.id}</p>
                </div>
                <div class="order-info-card">
                    <h4><i class="fas fa-file-invoice"></i> Invoice Number</h4>
                    <p>${order.invoice_number}</p>
                </div>
                <div class="order-info-card">
                    <h4><i class="fas fa-user"></i> Customer</h4>
                    <p>${order.full_name}</p>
                </div>
                <div class="order-info-card">
                    <h4><i class="fas fa-envelope"></i> Email</h4>
                    <p>${order.email}</p>
                </div>
                <div class="order-info-card">
                    <h4><i class="fas fa-phone"></i> Phone</h4>
                    <p>${order.phone}</p>
                </div>
                <div class="order-info-card">
                    <h4><i class="fas fa-dollar-sign"></i> Total Amount</h4>
                    <p>$${parseFloat(order.total_amount).toFixed(2)}</p>
                </div>
                <div class="order-info-card">
                    <h4><i class="fas fa-calendar"></i> Order Date</h4>
                    <p>${new Date(order.created_at).toLocaleDateString()}</p>
                </div>
                <div class="order-info-card">
                    <h4><i class="fas fa-info-circle"></i> Status</h4>
                    <p class="status-${order.payment_status}">${order.payment_status.charAt(0).toUpperCase() + order.payment_status.slice(1)}</p>
                </div>
            </div>

            ${shippingAddress ? `
            <div class="shipping-info">
                <h3><i class="fas fa-shipping-fast"></i> Shipping Information</h3>
                <div class="shipping-card">
                    <div class="shipping-details">
                        <div class="shipping-row">
                            <div class="shipping-field">
                                <strong><i class="fas fa-user"></i> Name:</strong>
                                <span>${shippingAddress.first_name || ''} ${shippingAddress.last_name || ''}</span>
                            </div>
                            <div class="shipping-field">
                                <strong><i class="fas fa-envelope"></i> Email:</strong>
                                <span>${order.email}</span>
                            </div>
                            <div class="shipping-field">
                                <strong><i class="fas fa-phone"></i> Phone:</strong>
                                <span>${order.phone}</span>
                            </div>
                        </div>
                        <div class="shipping-address">
                            <strong><i class="fas fa-map-marker-alt"></i> Shipping Address:</strong>
                            <div class="address-text">
                                ${shippingAddress.street || ''}<br>
                                ${shippingAddress.city || ''}, ${shippingAddress.state || ''} ${shippingAddress.postal_code || ''}<br>
                                ${shippingAddress.country || ''}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            ` : ''}

            <div class="order-items">
                <h3><i class="fas fa-shopping-bag"></i> Order Items</h3>
                ${items.map(item => {
                    const productType = item.type.charAt(0).toUpperCase() + item.type.slice(1);
                    const deliveryInfo = item.type === 'ebook' ? 
                        '<span class="delivery-type digital"><i class="fas fa-download"></i> Digital Download</span>' : 
                        '<span class="delivery-type physical"><i class="fas fa-shipping-fast"></i> Physical Item</span>';
                    
                    // Debug image path
                    console.log('Image path for item:', item.name, 'Path:', item.thumbs);
                    
                    return `
                    <div class="item-card">
                        <img src="${item.thumbs ? '../' + item.thumbs : 'https://via.placeholder.com/80x80/f8f9fa/6c757d?text=No+Image'}" alt="${item.name}" class="item-image" onerror="this.src='https://via.placeholder.com/80x80/f8f9fa/6c757d?text=No+Image'"
                        <div class="item-details">
                            <div class="item-name">${item.name}</div>
                            <div class="item-type-info">
                                <span class="product-type ${item.type}"><i class="fas fa-${item.type === 'ebook' ? 'file-pdf' : item.type === 'book' ? 'book' : 'palette'}"></i> ${productType}</span>
                                ${deliveryInfo}
                            </div>
                            <div class="item-meta">
                                <span><strong>SKU:</strong> ${item.sku}</span>
                                <span><strong>Quantity:</strong> ${item.quantity}</span>
                                ${item.author ? `<span><strong>Author:</strong> ${item.author}</span>` : ''}
                            </div>
                        </div>
                        <div class="item-price">$${parseFloat(item.price).toFixed(2)}</div>
                    </div>
                `;
                }).join('')}
            </div>

            <div class="order-actions">
                <div class="action-section">
                    <h4><i class="fas fa-edit"></i> Update Order Status</h4>
                    <form class="status-update-form" onsubmit="updateModalOrderStatus(event, ${order.id})">
                        <div class="form-group">
                            <label for="statusSelect">Order Status</label>
                            <select id="statusSelect" name="status" class="form-select" required>
                                <option value="pending" ${order.payment_status === 'pending' ? 'selected' : ''}>Pending</option>
                                <option value="processing" ${order.payment_status === 'processing' ? 'selected' : ''}>Processing</option>
                                <option value="completed" ${order.payment_status === 'completed' ? 'selected' : ''}>Completed</option>
                                <option value="failed" ${order.payment_status === 'failed' ? 'selected' : ''}>Failed</option>
                                <option value="refunded" ${order.payment_status === 'refunded' ? 'selected' : ''}>Refunded</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="updateNote">Add Note (Optional)</label>
                            <textarea id="updateNote" name="note" class="form-textarea" placeholder="Add a note about this status change..."></textarea>
                        </div>
                        <button type="submit" class="btn-update">
                            <i class="fas fa-save"></i> Update Order
                        </button>
                    </form>
                </div>

                <div class="action-section">
                    <h4><i class="fas fa-sticky-note"></i> Order Notes</h4>
                    <div class="notes-section">
                        <div class="notes-history">
                            ${notes.length > 0 ? notes.map(note => `
                                <div class="note-item">
                                    <div class="note-date">${new Date(note.created_at).toLocaleString()}</div>
                                    <div class="note-text">${note.notes || 'Status updated'}</div>
                                </div>
                            `).join('') : '<p style="color: #666; font-style: italic;">No notes available</p>'}
                        </div>
                    </div>
                </div>
            </div>
        `;

    document.getElementById('orderDetailsContent').innerHTML = content;
}

function updateModalOrderStatus(event, orderId) {
    event.preventDefault();

    const formData = new FormData(event.target);
    const status = formData.get('status');
    const note = formData.get('note');

    // Show loading state
    const submitBtn = event.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
    submitBtn.disabled = true;

    fetch('php/update_order_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                order_id: orderId,
                status: status,
                note: note
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                showNotification('Order status updated successfully!', 'success');

                // Refresh the order details
                viewOrder(orderId);

                // Refresh the main table
                location.reload();
            } else {
                showNotification('Error updating order: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Failed to update order status', 'error');
        })
        .finally(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
}

function closeOrderModal() {
    document.getElementById('orderDetailsModal').style.display = 'none';
}

function showNotification(message, type) {
    // Create notification element
    const notification = document.createElement('div');
    notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 1rem 1.5rem;
            border-radius: 0.5rem;
            color: white;
            font-weight: 600;
            z-index: 10000;
            animation: slideInRight 0.3s ease;
            background: ${type === 'success' ? '#27ae60' : '#e74c3c'};
        `;
    notification.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
            ${message}
        `;

    document.body.appendChild(notification);

    // Remove notification after 3 seconds
    setTimeout(() => {
        notification.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('orderDetailsModal');
    if (event.target === modal) {
        closeOrderModal();
    }
}

// Close modal with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeOrderModal();
    }
});

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
                body: JSON.stringify({
                    order_id: orderId
                })
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

<style>
/* Additional Modal Styles for Shipping and Product Types */
.shipping-info {
    margin-bottom: 2rem;
}

.shipping-info h3 {
    margin-bottom: 1rem;
    color: #2c3e50;
    border-bottom: 2px solid #ecf0f1;
    padding-bottom: 0.5rem;
}

.shipping-card {
    background: white;
    border-radius: 0.75rem;
    padding: 1.5rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    border: 1px solid #e2e8f0;
}

.shipping-details {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.shipping-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.shipping-field {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.shipping-field strong {
    color: #2c3e50;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.shipping-field span {
    color: #34495e;
    font-weight: 500;
}

.shipping-address {
    padding-top: 1rem;
    border-top: 1px solid #e2e8f0;
}

.shipping-address strong {
    color: #2c3e50;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
}

.address-text {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 0.5rem;
    border-left: 4px solid #3498db;
    font-weight: 500;
    color: #34495e;
    line-height: 1.5;
}

.item-type-info {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
}

.product-type {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.375rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.875rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.product-type.ebook {
    background: #e3f2fd;
    color: #1565c0;
    border: 1px solid #bbdefb;
}

.product-type.book {
    background: #f3e5f5;
    color: #7b1fa2;
    border: 1px solid #ce93d8;
}

.product-type.paint {
    background: #e8f5e8;
    color: #2e7d32;
    border: 1px solid #a5d6a7;
}

.delivery-type {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.25rem 0.5rem;
    border-radius: 0.5rem;
    font-size: 0.8rem;
    font-weight: 500;
}

.delivery-type.digital {
    background: #fff3e0;
    color: #ef6c00;
    border: 1px solid #ffcc02;
}

.delivery-type.physical {
    background: #e1f5fe;
    color: #0277bd;
    border: 1px solid #81d4fa;
}

@media (max-width: 768px) {
    .shipping-row {
        grid-template-columns: 1fr;
    }

    .item-type-info {
        align-items: center;
        text-align: center;
    }
}
</style>

<?php require_once 'footer.php'; ?>