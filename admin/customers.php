<?php
require_once 'header.php';
require_once 'php/db_connect.php';

// Handle customer deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_customer'])) {
    $user_id = $_POST['user_id'];
    $stmt = $conn->prepare("DELETE FROM customers WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
}

// Fetch customers with their order count
$query = "SELECT c.*, 
          COUNT(o.id) as total_orders,
          SUM(o.total_amount) as total_spent
          FROM customers c
          LEFT JOIN orders o ON c.id = o.user_id
          GROUP BY c.id
          ORDER BY c.created_at DESC";
$result = $conn->query($query);
?>

<div class="main-content">
    <div class="page-header">
        <h2>Customers Management</h2>
        <div class="header-actions">
            <button class="btn btn-primary" onclick="exportCustomers()">
                <i class="fas fa-download"></i> Export Customers
            </button>
        </div>
    </div>

    <div class="content-card">
        <!-- Search and Filter Section -->
        <div class="search-filter-section">
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Search customers..." class="form-control">
                <i class="fas fa-search search-icon"></i>
            </div>
            <div class="filter-box">
                <select id="filterOrders" class="form-control">
                    <option value="">All Orders</option>
                    <option value="0">No Orders</option>
                    <option value="1-5">1-5 Orders</option>
                    <option value="6-10">6-10 Orders</option>
                    <option value="10+">10+ Orders</option>
                </select>
                <select id="filterSpent" class="form-control">
                    <option value="">All Spending</option>
                    <option value="0-100">$0 - $100</option>
                    <option value="101-500">$101 - $500</option>
                    <option value="501-1000">$501 - $1000</option>
                    <option value="1000+">$1000+</option>
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table class="data-table" id="customersTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Total Orders</th>
                        <th>Total Spent</th>
                        <th>Joined Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($customer = $result->fetch_assoc()): ?>
                    <tr>
                        <td>#<?php echo $customer['id']; ?></td>
                        <td>
                            <?php echo htmlspecialchars($customer['name']); ?>
                        </td>
                        <td><?php echo htmlspecialchars($customer['email']); ?></td>
                        <td><?php echo htmlspecialchars($customer['phone']); ?></td>
                        <td><?php echo $customer['total_orders']; ?></td>
                        <td>$<?php echo number_format($customer['total_spent'] ?? 0, 2); ?></td>
                        <td><?php echo date('M d, Y', strtotime($customer['created_at'])); ?></td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-sm btn-info" onclick="viewCustomer(<?php echo $customer['id']; ?>)">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-warning" onclick="editCustomer(<?php echo $customer['id']; ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteCustomer(<?php echo $customer['id']; ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
/* General Styles */
.main-content {
    padding: 20px;
    max-width: 1400px;
    margin: 0 auto;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.content-card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    padding: 20px;
}

/* Search and Filter Section */
.search-filter-section {
    display: flex;
    gap: 15px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.search-box {
    position: relative;
    flex: 1;
    min-width: 250px;
}

.search-box input {
    padding-left: 35px;
    border-radius: 20px;
}

.search-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #666;
}

.filter-box {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.filter-box select {
    min-width: 150px;
    border-radius: 20px;
}

/* Table Styles */
.table-responsive {
    overflow-x: auto;
    margin-top: 20px;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}

.data-table th,
.data-table td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #eee;
}

.data-table th {
    background-color: #f8f9fa;
    font-weight: 600;
}

.data-table tbody tr:hover {
    background-color: #f8f9fa;
}

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: 5px;
    justify-content: flex-start;
}

.btn-sm {
    padding: 6px 10px;
    font-size: 0.875rem;
    border-radius: 4px;
    transition: all 0.3s ease;
}

.btn-sm:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

/* Responsive Design */
@media (max-width: 768px) {
    .search-filter-section {
        flex-direction: column;
    }
    
    .search-box,
    .filter-box {
        width: 100%;
    }
    
    .filter-box select {
        width: 100%;
    }
    
    .data-table th,
    .data-table td {
        padding: 8px;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .btn-sm {
        width: 100%;
        margin: 2px 0;
    }
}
</style>

<script>
// Search and Filter Functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const filterOrders = document.getElementById('filterOrders');
    const filterSpent = document.getElementById('filterSpent');
    const table = document.getElementById('customersTable');
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

    function filterTable() {
        const searchText = searchInput.value.toLowerCase();
        const ordersFilter = filterOrders.value;
        const spentFilter = filterSpent.value;

        for (let row of rows) {
            const name = row.cells[1].textContent.toLowerCase();
            const email = row.cells[2].textContent.toLowerCase();
            const phone = row.cells[3].textContent.toLowerCase();
            const orders = parseInt(row.cells[4].textContent);
            const spent = parseFloat(row.cells[5].textContent.replace('$', '').replace(',', ''));

            // Search filter
            const matchesSearch = name.includes(searchText) || 
                                email.includes(searchText) || 
                                phone.includes(searchText);

            // Orders filter
            let matchesOrders = true;
            if (ordersFilter) {
                switch(ordersFilter) {
                    case '0':
                        matchesOrders = orders === 0;
                        break;
                    case '1-5':
                        matchesOrders = orders >= 1 && orders <= 5;
                        break;
                    case '6-10':
                        matchesOrders = orders >= 6 && orders <= 10;
                        break;
                    case '10+':
                        matchesOrders = orders > 10;
                        break;
                }
            }

            // Spent filter
            let matchesSpent = true;
            if (spentFilter) {
                switch(spentFilter) {
                    case '0-100':
                        matchesSpent = spent >= 0 && spent <= 100;
                        break;
                    case '101-500':
                        matchesSpent = spent > 100 && spent <= 500;
                        break;
                    case '501-1000':
                        matchesSpent = spent > 500 && spent <= 1000;
                        break;
                    case '1000+':
                        matchesSpent = spent > 1000;
                        break;
                }
            }

            // Show/hide row based on all filters
            row.style.display = matchesSearch && matchesOrders && matchesSpent ? '' : 'none';
        }
    }

    // Add event listeners
    searchInput.addEventListener('input', filterTable);
    filterOrders.addEventListener('change', filterTable);
    filterSpent.addEventListener('change', filterTable);
});

function viewCustomer(customerId) {
    window.location.href = `customer-details.php?id=${customerId}`;
}

function editCustomer(customerId) {
    window.location.href = `edit-customer.php?id=${customerId}`;
}

function deleteCustomer(customerId) {
    if (confirm('Are you sure you want to delete this customer? This action cannot be undone.')) {
        fetch('php/delete_customer.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ user_id: customerId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error deleting customer: ' + data.message);
            }
        });
    }
}

function exportCustomers() {
    window.location.href = 'php/export_customers.php';
}
</script>

<?php require_once 'footer.php'; ?> 