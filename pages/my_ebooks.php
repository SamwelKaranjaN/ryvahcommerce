<?php
session_start();
require_once '../config/database.php';
require_once '../includes/download/DownloadHandler.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php?redirect=my_ebooks.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$conn = getDBConnection();

// Get user's purchased ebooks with proper expiration and download count checking
$sql = "SELECT ed.*, p.name, p.author, p.thumbs, p.price, p.type, p.filepath,
               o.invoice_number, o.order_date, o.payment_status,
               CASE 
                   WHEN ed.expires_at <= NOW() THEN 1 
                   ELSE 0 
               END as is_expired,
               CASE 
                   WHEN ed.download_count >= ed.max_downloads THEN 1 
                   ELSE 0 
               END as limit_reached
        FROM ebook_downloads ed
        JOIN products p ON ed.product_id = p.id
        JOIN orders o ON ed.order_id = o.id
        WHERE ed.user_id = ? AND p.type = 'ebook' AND o.payment_status = 'completed'
        ORDER BY ed.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$ebooks = $result->fetch_all(MYSQLI_ASSOC);

include '../includes/layouts/header.php';
?>

<div class="container py-5">
    <div class="row mb-4">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="../index.php" class="text-decoration-none">Home</a></li>
                    <li class="breadcrumb-item"><a href="orders.php" class="text-decoration-none">My Orders</a></li>
                    <li class="breadcrumb-item active" aria-current="page">My Ebooks</li>
                </ol>
            </nav>
            <h2 class="mb-0">My Ebooks</h2>
            <p class="text-muted">Download your purchased ebooks</p>
        </div>
    </div>

    <?php if (empty($ebooks)): ?>
    <div class="text-center py-5">
        <div class="mb-4">
            <i class="fas fa-book fa-4x text-muted"></i>
        </div>
        <h3 class="mb-3">No ebooks found</h3>
        <p class="text-muted mb-4">You haven't purchased any ebooks yet.</p>
        <a href="../index.php" class="btn btn-primary btn-lg px-5">
            <i class="fas fa-shopping-cart me-2"></i>Browse Ebooks
        </a>
    </div>
    <?php else: ?>
    <div class="row g-4">
        <?php foreach ($ebooks as $ebook): ?>
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <img src="../<?php echo htmlspecialchars($ebook['thumbs']); ?>" class="rounded me-3"
                            alt="<?php echo htmlspecialchars($ebook['name']); ?>"
                            style="width: 80px; height: 80px; object-fit: cover;">
                        <div>
                            <h5 class="card-title mb-1"><?php echo htmlspecialchars($ebook['name']); ?></h5>
                            <p class="text-muted mb-0">By <?php echo htmlspecialchars($ebook['author']); ?></p>
                        </div>
                    </div>

                    <div class="mb-3">
                        <p class="mb-1">
                            <small class="text-muted">Purchased on:</small><br>
                            <?php echo date('F j, Y', strtotime($ebook['order_date'])); ?>
                        </p>
                        <p class="mb-1">
                            <small class="text-muted">Downloads remaining:</small><br>
                            <span class="<?php echo ($ebook['limit_reached']) ? 'text-danger' : 'text-success'; ?>">
                                <?php echo $ebook['max_downloads'] - $ebook['download_count']; ?> of
                                <?php echo $ebook['max_downloads']; ?>
                            </span>
                        </p>
                        <p class="mb-1">
                            <small class="text-muted">Expires on:</small><br>
                            <span class="<?php echo ($ebook['is_expired']) ? 'text-danger' : 'text-info'; ?>">
                                <?php echo date('F j, Y g:i A', strtotime($ebook['expires_at'])); ?>
                                <?php if ($ebook['is_expired']): ?>
                                <i class="fas fa-exclamation-triangle text-danger ms-1" title="Expired"></i>
                                <?php endif; ?>
                            </span>
                        </p>
                        <?php if ($ebook['last_download']): ?>
                        <p class="mb-0">
                            <small class="text-muted">Last downloaded:</small><br>
                            <?php echo date('F j, Y g:i A', strtotime($ebook['last_download'])); ?>
                        </p>
                        <?php endif; ?>
                    </div>

                    <?php if ($ebook['is_expired']): ?>
                    <button class="btn btn-danger w-100" disabled>
                        <i class="fas fa-clock me-2"></i>Download Expired
                    </button>
                    <?php elseif ($ebook['limit_reached']): ?>
                    <button class="btn btn-secondary w-100" disabled>
                        <i class="fas fa-ban me-2"></i>Download Limit Reached
                    </button>
                    <?php else: ?>
                    <a href="../includes/download.php?token=<?php echo htmlspecialchars($ebook['download_token']); ?>"
                        class="btn btn-primary w-100">
                        <i class="fas fa-download me-2"></i>Download Ebook
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<style>
.card {
    transition: transform 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
}

.btn {
    transition: all 0.3s ease;
}

.btn:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.text-success {
    font-weight: 600;
}

.text-danger {
    font-weight: 600;
}

.text-info {
    font-weight: 600;
}

@media (max-width: 768px) {
    .card {
        margin-bottom: 1rem;
    }
}
</style>

<?php include '../includes/layouts/footer.php'; ?>