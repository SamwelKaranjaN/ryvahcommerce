<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login?redirect=downloads.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$conn = getDBConnection();

// Get user's actually downloaded items - only items with download_count > 0
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
        WHERE ed.user_id = ? AND ed.download_count > 0
        ORDER BY ed.updated_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$downloads = $result->fetch_all(MYSQLI_ASSOC);

include '../includes/layouts/header.php';
?>

<div class="downloads-container">
    <div class="container-fluid px-4">
        <div class="row mb-4">
            <div class="col">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="../index">Home</a></li>
                        <li class="breadcrumb-item"><a href="orders">My Orders</a></li>
                        <li class="breadcrumb-item active" aria-current="page">My Downloads</li>
                    </ol>
                </nav>
                <div class="page-header">
                    <h2><i class="fas fa-download me-2"></i>My Downloads</h2>
                    <p class="text-muted">Your downloaded digital content</p>
                </div>
            </div>
        </div>

        <!-- Search Bar -->
        <div class="search-section mb-4">
            <div class="search-container">
                <div class="search-input-wrapper">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" id="searchInput" class="form-control search-input"
                        placeholder="Search your downloads by title, author, or type...">
                    <button class="btn btn-clear" id="clearSearch" style="display: none;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="search-results-count">
                    <span id="resultsCount"><?php echo count($downloads); ?></span> downloads found
                </div>
            </div>
        </div>

        <?php if (empty($downloads)): ?>
        <div class="empty-state">
            <div class="empty-icon">
                <i class="fas fa-cloud-download-alt"></i>
            </div>
            <h3>No Downloads Yet</h3>
            <p>You haven't downloaded any items yet. Visit your ebooks library to start downloading!</p>
            <a href="my_ebooks" class="btn btn-primary btn-lg">
                <i class="fas fa-book me-2"></i>View My Ebooks
            </a>
        </div>
        <?php else: ?>
        <div id="downloadsGrid" class="downloads-grid">
            <?php foreach ($downloads as $download): ?>
            <div class="download-item" data-name="<?php echo strtolower(htmlspecialchars($download['name'])); ?>"
                data-author="<?php echo strtolower(htmlspecialchars($download['author'])); ?>"
                data-type="<?php echo strtolower($download['type']); ?>">

                <div class="download-thumbnail">
                    <?php if (!empty($download['thumbs'])): ?>
                    <?php
                                // Handle different thumbnail path formats
                                $thumb_path = $download['thumbs'];
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
                        alt="<?php echo htmlspecialchars($download['name']); ?>"
                        onerror="this.src='../assets/images/default-book.png'; this.onerror=null;">
                    <?php else: ?>
                    <div class="default-thumbnail">
                        <i class="fas fa-file-<?php echo $download['type'] === 'ebook' ? 'pdf' : 'alt'; ?>"></i>
                    </div>
                    <?php endif; ?>

                    <div class="download-overlay">
                        <div class="download-count">
                            <i class="fas fa-download"></i>
                            <span><?php echo $download['download_count']; ?></span>
                        </div>
                    </div>
                </div>

                <div class="download-content">
                    <div class="download-header">
                        <h6 class="download-title"><?php echo htmlspecialchars($download['name']); ?></h6>
                        <span class="download-type"><?php echo ucfirst($download['type']); ?></span>
                    </div>

                    <p class="download-author">by <?php echo htmlspecialchars($download['author']); ?></p>

                    <div class="download-meta">
                        <div class="meta-row">
                            <span class="meta-label">Downloaded:</span>
                            <span class="meta-value"><?php echo $download['download_count']; ?> times</span>
                        </div>
                        <div class="meta-row">
                            <span class="meta-label">Last download:</span>
                            <span
                                class="meta-value"><?php echo date('M j, Y', strtotime($download['updated_at'])); ?></span>
                        </div>
                        <div class="meta-row">
                            <span class="meta-label">Status:</span>
                            <span class="meta-value">
                                <?php if ($download['is_expired']): ?>
                                <span class="status-badge expired">Expired</span>
                                <?php elseif ($download['limit_reached']): ?>
                                <span class="status-badge limit-reached">Limit Reached</span>
                                <?php else: ?>
                                <span class="status-badge active">Active</span>
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>

                    <div class="download-actions">
                        <a href="../includes/download?token=<?php echo htmlspecialchars($download['download_token']); ?>"
                            class="btn btn-primary btn-sm">
                            <i class="fas fa-download me-1"></i>Download Again
                        </a>
                        <button class="btn btn-outline-secondary btn-sm"
                            onclick="showDetails(<?php echo $download['id']; ?>)">
                            <i class="fas fa-info-circle me-1"></i>Details
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- No Results State -->
        <div id="noResults" class="no-results" style="display: none;">
            <div class="no-results-icon">
                <i class="fas fa-search"></i>
            </div>
            <h4>No downloads found</h4>
            <p>Try adjusting your search terms or browse all your downloads.</p>
            <button class="btn btn-outline-primary" onclick="clearSearch()">
                <i class="fas fa-times me-1"></i>Clear Search
            </button>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.downloads-container {
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

.search-section {
    background: white;
    padding: 1.5rem;
    border-radius: 12px;
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
}

.search-container {
    max-width: 600px;
    margin: 0 auto;
}

.search-input-wrapper {
    position: relative;
    margin-bottom: 0.5rem;
}

.search-input {
    padding: 0.75rem 1rem 0.75rem 3rem;
    border: 2px solid #e9ecef;
    border-radius: 25px;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.search-input:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    outline: none;
}

.search-icon {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #6c757d;
    font-size: 1.1rem;
}

.btn-clear {
    position: absolute;
    right: 0.5rem;
    top: 50%;
    transform: translateY(-50%);
    border: none;
    background: transparent;
    color: #6c757d;
    padding: 0.25rem;
    border-radius: 50%;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-clear:hover {
    background: #f8f9fa;
    color: #495057;
}

.search-results-count {
    text-align: center;
    color: #6c757d;
    font-size: 0.9rem;
}

.empty-state,
.no-results {
    text-align: center;
    padding: 4rem 2rem;
    background: white;
    border-radius: 15px;
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
    max-width: 500px;
    margin: 0 auto;
}

.empty-icon,
.no-results-icon {
    font-size: 4rem;
    color: #6c757d;
    margin-bottom: 1.5rem;
}

.downloads-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 1.5rem;
    max-height: 60vh;
    overflow-y: auto;
    padding-right: 8px;
}

.downloads-grid::-webkit-scrollbar {
    width: 6px;
}

.downloads-grid::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.downloads-grid::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 10px;
}

.downloads-grid::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

.download-item {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    height: fit-content;
    display: flex;
    flex-direction: row;
}

.download-item:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.download-thumbnail {
    position: relative;
    width: 120px;
    height: 140px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.download-thumbnail img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.default-thumbnail {
    color: white;
    font-size: 2.5rem;
}

.download-overlay {
    position: absolute;
    top: 8px;
    right: 8px;
}

.download-count {
    background: rgba(0, 0, 0, 0.7);
    color: white;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.75rem;
    display: flex;
    align-items: center;
    gap: 4px;
}

.download-content {
    padding: 1rem;
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.download-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 0.5rem;
}

.download-title {
    color: #2c3e50;
    font-weight: 600;
    margin: 0;
    font-size: 0.95rem;
    line-height: 1.3;
    flex: 1;
    margin-right: 0.5rem;
}

.download-type {
    background: #e9ecef;
    color: #495057;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.download-author {
    color: #6c757d;
    font-size: 0.85rem;
    font-style: italic;
    margin-bottom: 1rem;
}

.download-meta {
    margin-bottom: 1rem;
}

.meta-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.25rem;
    font-size: 0.8rem;
}

.meta-label {
    color: #6c757d;
    font-weight: 500;
}

.meta-value {
    color: #495057;
    font-weight: 600;
}

.status-badge {
    padding: 2px 6px;
    border-radius: 8px;
    font-size: 0.65rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-badge.active {
    background: #d4edda;
    color: #155724;
}

.status-badge.expired {
    background: #f8d7da;
    color: #721c24;
}

.status-badge.limit-reached {
    background: #fff3cd;
    color: #856404;
}

.download-actions {
    display: flex;
    gap: 0.5rem;
}

.download-actions .btn {
    font-size: 0.8rem;
    padding: 0.4rem 0.8rem;
    border-radius: 6px;
    font-weight: 600;
    flex: 1;
}

.download-actions .btn:hover {
    transform: translateY(-1px);
}

@media (max-width: 768px) {
    .downloads-container {
        padding: 1rem 0;
    }

    .downloads-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
        max-height: 50vh;
    }

    .download-item {
        flex-direction: column;
    }

    .download-thumbnail {
        width: 100%;
        height: 120px;
    }

    .page-header {
        text-align: left;
    }

    .page-header h2 {
        font-size: 1.5rem;
    }

    .search-section {
        padding: 1rem;
    }
}

@media (max-width: 576px) {
    .download-actions {
        flex-direction: column;
    }

    .download-actions .btn {
        flex: none;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const clearButton = document.getElementById('clearSearch');
    const resultsCount = document.getElementById('resultsCount');
    const downloadsGrid = document.getElementById('downloadsGrid');
    const noResults = document.getElementById('noResults');
    const downloadItems = document.querySelectorAll('.download-item');

    let totalItems = downloadItems.length;

    function filterDownloads(searchTerm) {
        let visibleCount = 0;
        const term = searchTerm.toLowerCase().trim();

        downloadItems.forEach(item => {
            const name = item.dataset.name;
            const author = item.dataset.author;
            const type = item.dataset.type;

            const matches = name.includes(term) ||
                author.includes(term) ||
                type.includes(term);

            if (matches) {
                item.style.display = 'flex';
                visibleCount++;
            } else {
                item.style.display = 'none';
            }
        });

        // Update results count
        resultsCount.textContent = visibleCount;

        // Show/hide no results message
        if (visibleCount === 0 && term !== '') {
            downloadsGrid.style.display = 'none';
            noResults.style.display = 'block';
        } else {
            downloadsGrid.style.display = 'grid';
            noResults.style.display = 'none';
        }

        // Show/hide clear button
        if (term !== '') {
            clearButton.style.display = 'flex';
        } else {
            clearButton.style.display = 'none';
        }
    }

    // Search on keypress
    searchInput.addEventListener('input', function() {
        filterDownloads(this.value);
    });

    // Clear search
    clearButton.addEventListener('click', function() {
        searchInput.value = '';
        filterDownloads('');
        searchInput.focus();
    });

    // Global clear function
    window.clearSearch = function() {
        searchInput.value = '';
        filterDownloads('');
        searchInput.focus();
    };

    // Details function (placeholder)
    window.showDetails = function(downloadId) {
        // Could open a modal with more details
        alert('Download details for ID: ' + downloadId);
    };
});
</script>

<?php include '../includes/layouts/footer.php'; ?>