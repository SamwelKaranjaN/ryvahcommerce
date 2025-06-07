<?php
require_once '../includes/bootstrap.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=orders');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user's orders with their items and status history
$sql = "SELECT o.*, 
        GROUP_CONCAT(DISTINCT p.type) as product_types,
        GROUP_CONCAT(DISTINCT p.name SEPARATOR '||') as product_names,
        GROUP_CONCAT(DISTINCT oi.quantity SEPARATOR '||') as quantities,
        (
            SELECT status 
            FROM order_status_history 
            WHERE order_id = o.id 
            ORDER BY created_at DESC 
            LIMIT 1
        ) as current_status,
        (
            SELECT created_at 
            FROM order_status_history 
            WHERE order_id = o.id 
            ORDER BY created_at DESC 
            LIMIT 1
        ) as last_status_update
        FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        JOIN products p ON oi.product_id = p.id
        WHERE o.user_id = ?
        GROUP BY o.id
        ORDER BY o.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$orders = $result->fetch_all(MYSQLI_ASSOC);

// Fetch user data
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

include '../includes/layouts/header.php';
?>

<div class="profile-container">
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar Navigation -->
            <div class="col-lg-3">
                <div class="profile-sidebar">
                    <div class="profile-header text-center">
                        <div class="profile-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <h4 class="profile-name"><?php echo htmlspecialchars($user['full_name']); ?></h4>
                        <p class="profile-email"><?php echo htmlspecialchars($user['email']); ?></p>
                    </div>
                    <div class="profile-nav">
                        <a href="profile" class="nav-item">
                            <i class="fas fa-user-circle"></i>
                            <span>My Profile</span>
                        </a>
                        <a href="orders" class="nav-item active">
                            <i class="fas fa-shopping-bag"></i>
                            <span>My Orders</span>
                        </a>
                        <a href="pending_orders" class="nav-item">
                            <i class="fas fa-clock"></i>
                            <span>Pending Orders</span>
                        </a>
                        <a href="downloads" class="nav-item">
                            <i class="fas fa-download"></i>
                            <span>My Downloads</span>
                        </a>
                        <a href="settings" class="nav-item">
                            <i class="fas fa-cog"></i>
                            <span>Settings</span>
                        </a>
                        <a href="logout" class="nav-item text-danger">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Logout</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-lg-9">
                <div class="profile-content">
                    <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php
                            echo $_SESSION['success_message'];
                            unset($_SESSION['success_message']);
                            ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php endif; ?>

                    <div class="content-header">
                        <h2>My Orders</h2>
                        <p class="text-muted">View and track all your orders</p>
                    </div>

                    <?php if (empty($orders)): ?>
                    <div class="text-center py-5">
                        <div class="mb-4">
                            <i class="fas fa-shopping-bag fa-4x text-muted"></i>
                        </div>
                        <h3 class="mb-3">No orders found</h3>
                        <p class="text-muted mb-4">You haven't placed any orders yet.</p>
                        <a href="../index" class="btn btn-primary btn-lg px-5">
                            <i class="fas fa-shopping-cart me-2"></i>Start Shopping
                        </a>
                    </div>
                    <?php else: ?>
                    <div class="row g-4">
                        <?php foreach ($orders as $order):
                                $product_names = explode('||', $order['product_names']);
                                $quantities = explode('||', $order['quantities']);
                                $product_types = explode(',', $order['product_types']);
                                $has_ebooks = in_array('ebook', $product_types);
                                $has_physical = in_array('book', $product_types) || in_array('paint', $product_types);
                            ?>
                        <div class="col-12">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div>
                                            <h5 class="card-title mb-1">Order
                                                #<?php echo htmlspecialchars($order['invoice_number'] ?: $order['id']); ?>
                                            </h5>
                                            <p class="text-muted mb-0">
                                                Placed on <?php echo date('F j, Y', strtotime($order['created_at'])); ?>
                                            </p>
                                        </div>
                                        <div class="text-end">
                                            <?php
                                                    $status = $order['current_status'] ?? $order['payment_status'] ?? 'unknown';
                                                    if ($status == 'pending') {
                                                        $statusClass = 'warning';
                                                    } elseif ($status == 'processing') {
                                                        $statusClass = 'info';
                                                    } elseif ($status == 'completed') {
                                                        $statusClass = 'success';
                                                    } elseif ($status == 'failed') {
                                                        $statusClass = 'danger';
                                                    } elseif ($status == 'refunded') {
                                                        $statusClass = 'secondary';
                                                    } else {
                                                        $statusClass = 'secondary';
                                                    }
                                                    ?>
                                            <span class="badge bg-<?php echo $statusClass; ?>">
                                                <?php echo ucfirst($status); ?>
                                            </span>
                                            <p class="text-muted mb-0 mt-1">
                                                <small>Last updated:
                                                    <?php
                                                            $lastUpdate = $order['last_status_update'] ?? $order['created_at'];
                                                            echo $lastUpdate ? date('M j, Y g:i A', strtotime($lastUpdate)) : 'N/A';
                                                            ?></small>
                                            </p>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <h6 class="mb-2">Order Items:</h6>
                                        <ul class="list-unstyled mb-0">
                                            <?php for ($i = 0; $i < count($product_names); $i++): ?>
                                            <li class="mb-1">
                                                <?php echo isset($product_names[$i]) ? htmlspecialchars($product_names[$i]) : 'Unknown Product'; ?>
                                                <span class="text-muted">(Qty:
                                                    <?php echo isset($quantities[$i]) ? $quantities[$i] : '0'; ?>)</span>
                                            </li>
                                            <?php endfor; ?>
                                        </ul>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <p class="mb-0">
                                                <strong>Total Amount:</strong>
                                                $<?php echo number_format($order['total_amount'], 2); ?>
                                            </p>
                                            <p class="mb-0">
                                                <strong>Payment Method:</strong>
                                                <?php echo ucfirst($order['payment_method'] ?? 'unknown'); ?>
                                            </p>
                                        </div>
                                        <div class="text-end">
                                            <?php if ($has_ebooks): ?>
                                            <a href="my_ebooks" class="btn btn-outline-primary me-2">
                                                <i class="fas fa-book me-2"></i>View Ebooks
                                            </a>
                                            <?php endif; ?>
                                            <?php if ($has_physical): ?>
                                            <button class="btn btn-outline-primary" data-bs-toggle="modal"
                                                data-bs-target="#trackingModal<?php echo $order['id']; ?>">
                                                <i class="fas fa-truck me-2"></i>Track Order
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php if ($has_physical): ?>
                        <!-- Tracking Modal -->
                        <div class="modal fade" id="trackingModal<?php echo $order['id']; ?>" tabindex="-1"
                            aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Track Order
                                            #<?php echo htmlspecialchars($order['invoice_number'] ?: $order['id']); ?>
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <?php
                                                    // Get comprehensive order status history and tracking information
                                                    $sql = "SELECT osh.*, 
                                       CASE 
                                           WHEN osh.status = 'pending' THEN 'Order placed and payment initiated'
                                           WHEN osh.status = 'processing' THEN 'Payment confirmed, preparing your order'
                                           WHEN osh.status = 'completed' THEN 'Order completed successfully'
                                           WHEN osh.status = 'failed' THEN 'Payment failed or order cancelled'
                                           WHEN osh.status = 'refunded' THEN 'Order refunded'
                                           ELSE 'Status update'
                                       END as status_description
                                FROM order_status_history osh
                                WHERE osh.order_id = ? 
                                ORDER BY osh.created_at ASC";
                                                    $stmt = $conn->prepare($sql);
                                                    $stmt->bind_param("i", $order['id']);
                                                    $stmt->execute();
                                                    $result = $stmt->get_result();
                                                    $status_history = $result->fetch_all(MYSQLI_ASSOC);

                                                    // Create comprehensive tracking steps for physical products
                                                    $tracking_steps = [
                                                        'pending' => [
                                                            'title' => 'Order Received',
                                                            'description' => 'Your order has been placed and payment is being processed',
                                                            'icon' => 'fas fa-receipt'
                                                        ],
                                                        'processing' => [
                                                            'title' => 'Payment Confirmed',
                                                            'description' => 'Payment successful. Your order is being prepared for packaging',
                                                            'icon' => 'fas fa-credit-card'
                                                        ],
                                                        'packaging' => [
                                                            'title' => 'Packaging',
                                                            'description' => 'Your items are being carefully packaged',
                                                            'icon' => 'fas fa-box'
                                                        ],
                                                        'shipped' => [
                                                            'title' => 'Shipped',
                                                            'description' => 'Your package has been dispatched and is on its way',
                                                            'icon' => 'fas fa-shipping-fast'
                                                        ],
                                                        'in_transit' => [
                                                            'title' => 'In Transit',
                                                            'description' => 'Your package is traveling to your location',
                                                            'icon' => 'fas fa-truck'
                                                        ],
                                                        'out_for_delivery' => [
                                                            'title' => 'Out for Delivery',
                                                            'description' => 'Your package is out for delivery today',
                                                            'icon' => 'fas fa-dolly'
                                                        ],
                                                        'completed' => [
                                                            'title' => 'Delivered',
                                                            'description' => 'Package delivered successfully',
                                                            'icon' => 'fas fa-check-circle'
                                                        ]
                                                    ];

                                                    // If no status history exists, create a basic one from order data
                                                    if (empty($status_history)) {
                                                        $status_history = [
                                                            [
                                                                'status' => $order['payment_status'] ?? 'pending',
                                                                'status_description' => $tracking_steps[$order['payment_status'] ?? 'pending']['description'] ?? 'Order placed',
                                                                'created_at' => $order['created_at'],
                                                                'notes' => 'Order created on ' . date('F j, Y g:i A', strtotime($order['created_at']))
                                                            ]
                                                        ];
                                                    }
                                                    ?>
                                        <div class="timeline">
                                            <?php
                                                        $statusOrder = ['pending', 'processing', 'packaging', 'shipped', 'in_transit', 'out_for_delivery', 'completed'];
                                                        $currentStatusIndex = -1;
                                                        $currentStatus = $order['current_status'] ?? $order['payment_status'] ?? 'pending';

                                                        // Find current status index
                                                        foreach ($status_history as $status) {
                                                            $index = array_search($status['status'], $statusOrder);
                                                            if ($index !== false && $index > $currentStatusIndex) {
                                                                $currentStatusIndex = $index;
                                                            }
                                                        }

                                                        // If current status index is still -1, use the order's current status
                                                        if ($currentStatusIndex == -1) {
                                                            $currentStatusIndex = array_search($currentStatus, $statusOrder);
                                                            if ($currentStatusIndex === false) $currentStatusIndex = 0;
                                                        }

                                                        // Show actual status history first
                                                        foreach ($status_history as $index => $status):
                                                            $statusIndex = array_search($status['status'], $statusOrder);
                                                            $isCompleted = $statusIndex !== false && $statusIndex <= $currentStatusIndex;
                                                            $isCurrent = $statusIndex === $currentStatusIndex;
                                                            $statusClass = '';

                                                            if ($status['status'] == 'failed' || $status['status'] == 'refunded') {
                                                                $statusClass = 'timeline-danger';
                                                            } elseif ($isCompleted) {
                                                                $statusClass = 'timeline-success';
                                                            } elseif ($isCurrent) {
                                                                $statusClass = 'timeline-current';
                                                            } else {
                                                                $statusClass = 'timeline-pending';
                                                            }

                                                            $step_info = $tracking_steps[$status['status']] ?? [
                                                                'title' => ucfirst($status['status']),
                                                                'description' => $status['status_description'],
                                                                'icon' => 'fas fa-info-circle'
                                                            ];
                                                        ?>
                                            <div class="timeline-item <?php echo $statusClass; ?>">
                                                <div class="timeline-marker">
                                                    <?php if ($status['status'] == 'completed'): ?>
                                                    <i class="fas fa-check"></i>
                                                    <?php elseif ($status['status'] == 'failed' || $status['status'] == 'refunded'): ?>
                                                    <i class="fas fa-times"></i>
                                                    <?php elseif ($isCurrent): ?>
                                                    <i class="fas fa-clock"></i>
                                                    <?php else: ?>
                                                    <i class="<?php echo $step_info['icon']; ?>"></i>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="timeline-content">
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <h6 class="mb-0">
                                                            <?php echo $step_info['title']; ?></h6>
                                                        <small class="text-muted">
                                                            <?php echo $status['created_at'] ? date('M j, g:i A', strtotime($status['created_at'])) : 'N/A'; ?>
                                                        </small>
                                                    </div>
                                                    <p class="text-muted mb-1">
                                                        <?php echo $step_info['description']; ?></p>
                                                    <?php if (!empty($status['notes'])): ?>
                                                    <p class="mb-0 small">
                                                        <strong>Note:</strong>
                                                        <?php echo htmlspecialchars($status['notes']); ?>
                                                    </p>
                                                    <?php endif; ?>

                                                    <?php
                                                                    // Add estimated delivery time for processing orders
                                                                    if ($has_physical && in_array($status['status'], ['processing', 'packaging'])):
                                                                        $estimatedDelivery = date('F j, Y', strtotime($status['created_at'] . ' +5 days'));
                                                                    ?>
                                                    <p class="mb-0 small text-info">
                                                        <i class="fas fa-truck me-1"></i>
                                                        Estimated delivery: <?php echo $estimatedDelivery; ?>
                                                    </p>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <?php endforeach; ?>

                                            <!-- Show future tracking steps for pending/processing orders -->
                                            <?php if ($currentStatusIndex < count($statusOrder) - 1 && !in_array($currentStatus, ['failed', 'refunded', 'completed'])): ?>
                                            <?php
                                                            // Show next expected steps
                                                            for ($i = max(1, $currentStatusIndex + 1); $i < count($statusOrder); $i++):
                                                                $futureStep = $statusOrder[$i];
                                                                $step_info = $tracking_steps[$futureStep];
                                                            ?>
                                            <div class="timeline-item timeline-future">
                                                <div class="timeline-marker">
                                                    <i class="<?php echo $step_info['icon']; ?>"></i>
                                                </div>
                                                <div class="timeline-content">
                                                    <h6 class="mb-1 text-muted"><?php echo $step_info['title']; ?></h6>
                                                    <p class="text-muted mb-0 small">
                                                        <?php echo $step_info['description']; ?></p>
                                                    <p class="text-muted mb-0 small"><em>Pending</em></p>
                                                </div>
                                            </div>
                                            <?php endfor; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.profile-container {
    min-height: 100vh;
    background: #f8f9fa;
    padding: 2rem 0;
}

.profile-sidebar {
    background: white;
    border-radius: 15px;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
    padding: 2rem;
    height: 100%;
}

.profile-header {
    padding-bottom: 2rem;
    border-bottom: 1px solid #eee;
    margin-bottom: 2rem;
}

.profile-avatar {
    width: 120px;
    height: 120px;
    background: linear-gradient(45deg, #007bff, #00bcd4);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
    box-shadow: 0 5px 15px rgba(0, 123, 255, 0.3);
}

.profile-avatar i {
    font-size: 3.5rem;
    color: white;
}

.profile-name {
    color: #2c3e50;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.profile-email {
    color: #6c757d;
    font-size: 0.9rem;
}

.profile-nav {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.nav-item {
    display: flex;
    align-items: center;
    padding: 1rem;
    color: #2c3e50;
    text-decoration: none;
    border-radius: 10px;
    transition: all 0.3s ease;
}

.nav-item i {
    width: 24px;
    margin-right: 1rem;
    font-size: 1.1rem;
}

.nav-item:hover {
    background: #f8f9fa;
    color: #007bff;
}

.nav-item.active {
    background: #007bff;
    color: white;
}

.profile-content {
    padding: 0 1rem;
}

.content-header {
    margin-bottom: 2rem;
}

.content-header h2 {
    color: #2c3e50;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.card {
    transition: transform 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
}

.btn {
    transition: all 0.3s ease;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

/* Timeline Styles */
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 10px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}

.timeline-item {
    position: relative;
    margin-bottom: 1.5rem;
}

.timeline-marker {
    position: absolute;
    left: -25px;
    top: 5px;
    width: 20px;
    height: 20px;
    background: #6c757d;
    border-radius: 50%;
    border: 2px solid white;
    box-shadow: 0 0 0 2px #dee2e6;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    color: white;
}

.timeline-success .timeline-marker {
    background: #28a745;
}

.timeline-current .timeline-marker {
    background: #007bff;
    animation: pulse 2s infinite;
}

.timeline-danger .timeline-marker {
    background: #dc3545;
}

.timeline-pending .timeline-marker {
    background: #6c757d;
}

.timeline-future .timeline-marker {
    background: #e9ecef;
    border-color: #dee2e6;
}

.timeline-dot {
    width: 8px;
    height: 8px;
    background: currentColor;
    border-radius: 50%;
}

.timeline-content {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 8px;
    border-left: 3px solid #007bff;
}

.timeline-success .timeline-content {
    border-left-color: #28a745;
}

.timeline-current .timeline-content {
    border-left-color: #007bff;
    background: #e3f2fd;
}

.timeline-danger .timeline-content {
    border-left-color: #dc3545;
}

.timeline-future .timeline-content {
    border-left-color: #dee2e6;
    background: #f8f9fa;
    opacity: 0.7;
}

@keyframes pulse {
    0% {
        transform: scale(1);
        box-shadow: 0 0 0 0 rgba(0, 123, 255, 0.7);
    }

    70% {
        transform: scale(1.1);
        box-shadow: 0 0 0 10px rgba(0, 123, 255, 0);
    }

    100% {
        transform: scale(1);
        box-shadow: 0 0 0 0 rgba(0, 123, 255, 0);
    }
}

@media (max-width: 991.98px) {
    .profile-sidebar {
        margin-bottom: 2rem;
    }

    .profile-content {
        padding: 0;
    }
}

@media (max-width: 767.98px) {
    .profile-container {
        padding: 1rem;
    }

    .card {
        margin-bottom: 1rem;
    }

    .timeline {
        padding-left: 20px;
    }

    .timeline-marker {
        left: -15px;
        width: 10px;
        height: 10px;
    }

    .text-end {
        text-align: left !important;
        margin-top: 0.5rem;
    }

    .btn {
        width: 100%;
        margin-bottom: 0.5rem;
    }
}
</style>

<?php include '../includes/layouts/footer.php'; ?>