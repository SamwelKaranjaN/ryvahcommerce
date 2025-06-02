<?php include '../includes/layouts/header.php'; ?>

<!-- Link to custom NFT styles -->
<link rel="stylesheet" href="nft-styles.css">

<!-- NFT Browse Section -->
<section class="nft-browse py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h1 class="display-4 fw-bold text-primary">Ryvah NFT Collection</h1>
            <p class="lead">Discover unique digital artwork and collectibles</p>
        </div>

        <!-- NFT Grid -->
        <div class="row g-4">
            <!-- NFT #12 Card -->
            <div class="col-md-6 col-lg-4">
                <div class="card nft-card h-100">
                    <div class="nft-preview-container">
                        <?php
                        $character_img = '../assets/images/nft-character-12.jpg';
                        if (file_exists($character_img)): ?>
                        <img src="<?php echo $character_img; ?>" class="card-img-top nft-preview" alt="NFT #12">
                        <?php else: ?>
                        <div class="nft-preview-placeholder">
                            <div class="text-center">
                                <i class="fas fa-image fa-3x mb-3"></i>
                                <h5>NFT #12</h5>
                                <small>Character Artwork</small>
                            </div>
                        </div>
                        <?php endif; ?>
                        <div class="nft-badge">
                            <span class="badge bg-warning">Coming Soon</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">NFT #12</h5>
                        <p class="card-text text-muted">By Michael J. Leonard</p>
                        <p class="card-text">
                            <small>Constitutional Right to Freedom of Speech through art</small>
                        </p>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">1st Opening #2</small>
                            <a href="detail.php?id=12" class="btn btn-primary btn-sm">
                                <i class="fas fa-eye me-1"></i>View Details
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- NFT #13 Card - Coming Soon -->
            <div class="col-md-6 col-lg-4">
                <div class="card nft-card h-100 coming-soon-card">
                    <div class="nft-preview-placeholder">
                        <div class="text-center">
                            <i class="fas fa-clock fa-3x mb-3"></i>
                            <h5>NFT #13</h5>
                            <small>Coming Soon</small>
                        </div>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">NFT #13</h5>
                        <p class="card-text text-muted">Future Release</p>
                        <p class="card-text">
                            <small>Next artwork in the collection</small>
                        </p>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">Coming Soon</small>
                            <button class="btn btn-secondary btn-sm" disabled>
                                <i class="fas fa-clock me-1"></i>Preview
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- NFT #14 Card - Coming Soon -->
            <div class="col-md-6 col-lg-4">
                <div class="card nft-card h-100 coming-soon-card">
                    <div class="nft-preview-placeholder">
                        <div class="text-center">
                            <i class="fas fa-clock fa-3x mb-3"></i>
                            <h5>NFT #14</h5>
                            <small>Coming Soon</small>
                        </div>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">NFT #14</h5>
                        <p class="card-text text-muted">Future Release</p>
                        <p class="card-text">
                            <small>Upcoming digital artwork</small>
                        </p>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">Coming Soon</small>
                            <button class="btn btn-secondary btn-sm" disabled>
                                <i class="fas fa-clock me-1"></i>Preview
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- NFT #15 Card - Coming Soon -->
            <div class="col-md-6 col-lg-4">
                <div class="card nft-card h-100 coming-soon-card">
                    <div class="nft-preview-placeholder">
                        <div class="text-center">
                            <i class="fas fa-clock fa-3x mb-3"></i>
                            <h5>NFT #15</h5>
                            <small>Coming Soon</small>
                        </div>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">NFT #15</h5>
                        <p class="card-text text-muted">Future Release</p>
                        <p class="card-text">
                            <small>Digital art collection expansion</small>
                        </p>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">Coming Soon</small>
                            <button class="btn btn-secondary btn-sm" disabled>
                                <i class="fas fa-clock me-1"></i>Preview
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- NFT #16 Card - Coming Soon -->
            <div class="col-md-6 col-lg-4">
                <div class="card nft-card h-100 coming-soon-card">
                    <div class="nft-preview-placeholder">
                        <div class="text-center">
                            <i class="fas fa-clock fa-3x mb-3"></i>
                            <h5>NFT #16</h5>
                            <small>Coming Soon</small>
                        </div>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">NFT #16</h5>
                        <p class="card-text text-muted">Future Release</p>
                        <p class="card-text">
                            <small>Exclusive limited edition</small>
                        </p>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">Coming Soon</small>
                            <button class="btn btn-secondary btn-sm" disabled>
                                <i class="fas fa-clock me-1"></i>Preview
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- NFT #17 Card - Coming Soon -->
            <div class="col-md-6 col-lg-4">
                <div class="card nft-card h-100 coming-soon-card">
                    <div class="nft-preview-placeholder">
                        <div class="text-center">
                            <i class="fas fa-clock fa-3x mb-3"></i>
                            <h5>NFT #17</h5>
                            <small>Coming Soon</small>
                        </div>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">NFT #17</h5>
                        <p class="card-text text-muted">Future Release</p>
                        <p class="card-text">
                            <small>Community requested design</small>
                        </p>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">Coming Soon</small>
                            <button class="btn btn-secondary btn-sm" disabled>
                                <i class="fas fa-clock me-1"></i>Preview
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- About NFT Section -->
        <div class="row mt-5">
            <div class="col-lg-8 mx-auto">
                <div class="text-center">
                    <h3 class="mb-4">About Ryvah NFTs</h3>
                    <p class="lead">
                        The Ryvah NFT Collection represents unique digital artwork that explores themes of
                        freedom, expression, and artistic vision. Each piece is carefully crafted to convey
                        meaningful messages while showcasing exceptional digital artistry.
                    </p>
                    <p>
                        Created by Michael J. Leonard, these NFTs are part of the broader Ryvah universe,
                        incorporating elements from the Laws of Ryvah and exploring constitutional rights
                        through artistic expression.
                    </p>
                    <div class="mt-4">
                        <a href="../lawsofryvah/laws" class="btn btn-outline-primary me-3">
                            <i class="fas fa-gavel me-2"></i>Read Laws of Ryvah
                        </a>
                        <a href="../pages/contact" class="btn btn-outline-secondary">
                            <i class="fas fa-envelope me-2"></i>Contact Artist
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.nft-browse {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    min-height: 100vh;
}

.nft-card {
    border: none;
    border-radius: 15px;
    overflow: hidden;
    transition: all 0.3s ease;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.nft-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.nft-preview-container {
    position: relative;
    height: 250px;
    overflow: hidden;
}

.nft-preview {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.nft-card:hover .nft-preview {
    transform: scale(1.1);
}

.nft-preview-placeholder {
    height: 100%;
    background: linear-gradient(45deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

.coming-soon-card .nft-preview-placeholder {
    background: linear-gradient(45deg, #bbb 0%, #999 100%);
}

.nft-badge {
    position: absolute;
    top: 10px;
    right: 10px;
}

.card-body {
    padding: 1.5rem;
}

.card-title {
    color: #8B4513;
    font-weight: bold;
    font-family: 'Georgia', serif;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .nft-preview-container {
        height: 200px;
    }
}

/* Button icon styling */
.btn .fas.fa-eye {
    font-size: 0.8rem;
}
</style>

<?php include '../includes/layouts/footer.php'; ?>