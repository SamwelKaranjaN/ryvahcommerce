<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php?redirect=my_ebooks.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$conn = getDBConnection();

// Get user's purchased ebooks - show ALL ebooks regardless of expiry status
$sql = "SELECT ed.*, p.name, p.author, p.thumbs, p.price, p.type, p.filepath,
               o.invoice_number, o.created_at as order_date, o.payment_status,
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
        WHERE ed.user_id = ? AND p.type = 'ebook' 
        ORDER BY ed.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$ebooks = $result->fetch_all(MYSQLI_ASSOC);

include '../includes/layouts/header.php';
?>

<div class="ebooks-container">
    <div class="container-fluid px-4">
        <div class="row mb-4">
            <div class="col">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="../index">Home</a></li>
                        <li class="breadcrumb-item"><a href="orders">My Orders</a></li>
                        <li class="breadcrumb-item active" aria-current="page">My Ebooks</li>
                    </ol>
                </nav>
                <div class="page-header">
                    <h2><i class="fas fa-book-open me-2"></i>My Ebooks Library</h2>
                    <p class="text-muted">Access and download your purchased digital books</p>
                </div>
            </div>
        </div>

        <?php if (empty($ebooks)): ?>
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-book-open"></i>
                </div>
                <h3>No Ebooks Found</h3>
                <p>You haven't purchased any ebooks yet. Start building your digital library!</p>
                <a href="../index" class="btn btn-primary btn-lg">
                    <i class="fas fa-shopping-cart me-2"></i>Browse Ebooks
                </a>
            </div>
        <?php else: ?>
            <div class="ebooks-grid">
                <?php foreach ($ebooks as $ebook): ?>
                    <div class="ebook-card">
                        <div class="ebook-thumbnail">
                            <?php if (!empty($ebook['thumbs'])): ?>
                                <?php
                                // Handle different thumbnail path formats
                                $thumb_path = $ebook['thumbs'];
                                if (strpos($thumb_path, 'Uploads/') === 0) {
                                    // New format: Uploads/thumbs/filename.jpg -> ../admin/Uploads/thumbs/filename.jpg
                                    $image_src = '../admin/' . $thumb_path;
                                } else if (strpos($thumb_path, 'thumbs/') === 0) {
                                    // Old format: thumbs/filename.jpg -> ../ + thumbs/filename.jpg
                                    $image_src = '../' . $thumb_path;
                                } else {
                                    // Fallback
                                    $image_src = '../' . $thumb_path;
                                }
                                ?>
                                <img src="<?php echo htmlspecialchars($image_src); ?>"
                                    alt="<?php echo htmlspecialchars($ebook['name']); ?>"
                                    onerror="this.src='../assets/images/default-book.png'; this.onerror=null;">
                            <?php else: ?>
                                <div class="default-thumbnail">
                                    <i class="fas fa-book"></i>
                                </div>
                            <?php endif; ?>

                            <div class="status-badges">
                                <span class="status-badge status-<?php echo $ebook['payment_status']; ?>">
                                    <?php echo ucfirst($ebook['payment_status']); ?>
                                </span>
                                <?php if ($ebook['is_expired']): ?>
                                    <span class="status-badge status-expired">Expired</span>
                                <?php endif; ?>
                                <?php if ($ebook['limit_reached']): ?>
                                    <span class="status-badge status-limit">Limit Reached</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="ebook-content">
                            <h6 class="ebook-title"><?php echo htmlspecialchars($ebook['name']); ?></h6>
                            <p class="ebook-author">by <?php echo htmlspecialchars($ebook['author']); ?></p>

                            <div class="ebook-meta">
                                <div class="meta-item">
                                    <small>Purchased: <?php echo date('M j, Y', strtotime($ebook['order_date'])); ?></small>
                                </div>
                                <div class="meta-item">
                                    <small>Downloads:
                                        <?php echo max(0, $ebook['max_downloads'] - $ebook['download_count']); ?>/<?php echo $ebook['max_downloads']; ?>
                                        remaining</small>
                                </div>
                                <div class="meta-item">
                                    <small>Expires: <?php echo date('M j, Y', strtotime($ebook['expires_at'])); ?></small>
                                </div>
                                <?php if (!empty($ebook['updated_at']) && $ebook['download_count'] > 0): ?>
                                    <div class="meta-item">
                                        <small>Last activity: <?php echo date('M j, Y', strtotime($ebook['updated_at'])); ?></small>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="ebook-actions">
                                <?php if ($ebook['payment_status'] !== 'completed'): ?>
                                    <button class="btn btn-warning btn-sm w-100" disabled>
                                        <i class="fas fa-clock me-1"></i>Payment Pending
                                    </button>
                                <?php else: ?>
                                    <a href="../includes/download?token=<?php echo htmlspecialchars($ebook['download_token']); ?>"
                                        class="btn btn-primary btn-sm w-100">
                                        <i class="fas fa-download me-1"></i>
                                        <?php if ($ebook['is_expired'] || $ebook['limit_reached']): ?>
                                            Try Download
                                        <?php else: ?>
                                            Download
                                        <?php endif; ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    .ebooks-container {
        min-height: 100vh;
        background: #f8f9fb;
        padding: 2rem 0;
    }

    .page-header {
        text-align: center;
        margin-bottom: 2rem;
    }

    .page-header h2 {
        color: #2c3e50;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .breadcrumb {
        background: transparent;
        padding: 0;
        margin-bottom: 1rem;
    }

    .breadcrumb a {
        color: #6c757d;
        text-decoration: none;
    }

    .breadcrumb a:hover {
        color: #007bff;
    }

    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        background: white;
        border-radius: 15px;
        box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
        max-width: 500px;
        margin: 0 auto;
    }

    .empty-icon {
        font-size: 4rem;
        color: #6c757d;
        margin-bottom: 1.5rem;
    }

    .ebooks-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 1.5rem;
        max-height: 70vh;
        overflow-y: auto;
        padding-right: 8px;
    }

    .ebooks-grid::-webkit-scrollbar {
        width: 6px;
    }

    .ebooks-grid::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    .ebooks-grid::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 10px;
    }

    .ebooks-grid::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }

    .ebook-card {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        height: fit-content;
    }

    .ebook-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }

    .ebook-thumbnail {
        position: relative;
        height: 160px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .ebook-thumbnail img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .default-thumbnail {
        color: white;
        font-size: 3rem;
    }

    .status-badges {
        position: absolute;
        top: 8px;
        right: 8px;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .status-badge {
        padding: 2px 6px;
        border-radius: 12px;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-completed {
        background: #28a745;
        color: white;
    }

    .status-pending {
        background: #ffc107;
        color: #000;
    }

    .status-failed {
        background: #dc3545;
        color: white;
    }

    .status-expired {
        background: #ff6b6b;
        color: white;
    }

    .status-limit {
        background: #6c757d;
        color: white;
    }

    .ebook-content {
        padding: 1rem;
    }

    .ebook-title {
        color: #2c3e50;
        font-weight: 600;
        margin-bottom: 0.25rem;
        font-size: 0.9rem;
        line-height: 1.3;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .ebook-author {
        color: #6c757d;
        font-size: 0.8rem;
        margin-bottom: 0.75rem;
        font-style: italic;
    }

    .ebook-meta {
        margin-bottom: 1rem;
    }

    .meta-item {
        margin-bottom: 0.25rem;
    }

    .meta-item small {
        color: #6c757d;
        font-size: 0.75rem;
    }

    .ebook-actions .btn {
        font-size: 0.8rem;
        padding: 0.5rem;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.2s ease;
    }

    .ebook-actions .btn:hover:not(:disabled) {
        transform: translateY(-1px);
    }

    @media (max-width: 768px) {
        .ebooks-container {
            padding: 1rem 0;
        }

        .ebooks-grid {
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1rem;
            max-height: 60vh;
        }

        .ebook-thumbnail {
            height: 140px;
        }

        .page-header {
            text-align: left;
        }

        .page-header h2 {
            font-size: 1.5rem;
        }
    }

    @media (max-width: 576px) {
        .ebooks-grid {
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        }

        .ebook-thumbnail {
            height: 120px;
        }

        .default-thumbnail {
            font-size: 2rem;
        }
    }
</style>

<?php include '../includes/layouts/footer.php'; ?>