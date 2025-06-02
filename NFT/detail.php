<?php
// Get NFT ID from URL parameter, default to 12
$nft_id = isset($_GET['id']) ? (int)$_GET['id'] : 12;

// NFT data array - you can expand this for more NFTs
$nfts = [
    12 => [
        'title' => 'NFT #12',
        'subtitle' => '1st Opening #2 "1st Hora Succinate"',
        'author' => 'Michael J. Leonard',
        'collection' => 'NFT RY VAH',
        'status' => 'Coming soon',
        'main_text' => 'I am recognizing the Constitutional Right to Freedom of Speech by normalizing nudity.',
        'description' => 'If a by product of our society will be better then I let it hurt very much like the censorship of our nipples and vagina I absolutely feel like cold steel of a knife stuck in my throat - it does not go down so it hurts. It doesn\'t get wet before it killed by the house drafting. I have been told to pay out a lawyer\'s fee to our nipples and it\'s so freezing! If this is about the speech over the power that reveal allow nudity to be published - nudity is the default of "normality" - M. J. Leonard',
        'additional_text' => [
            'We must never allow nudity to be politicized - nudity is the default of "normality" - M. J. Leonard...',
            'To display correctly copylocks the original and display only by each with the plastic over the reproduction.',
            'Please read and pass the Laws of Ryvah.'
        ],
        'document_image' => '../assets/images/nft-document-12.jpg',
        'character_image' => '../assets/images/nft-character-12.jpg',
        'portrait_image' => '../assets/images/nft-portrait-12.jpg'
    ],
    13 => [
        'title' => 'NFT #13',
        'subtitle' => 'Future Release',
        'author' => 'Michael J. Leonard',
        'collection' => 'NFT RY VAH',
        'status' => 'Coming Soon',
        'main_text' => 'Next artwork in the collection exploring digital rights and expression.',
        'description' => 'This NFT will continue the exploration of constitutional rights and digital freedom.',
        'additional_text' => [
            'More details will be available upon release.',
            'Stay tuned for updates on this upcoming piece.'
        ],
        'document_image' => '../assets/images/nft-document-13.jpg',
        'character_image' => '../assets/images/nft-character-13.jpg',
        'portrait_image' => '../assets/images/nft-portrait-13.jpg'
    ]
    // Add more NFTs as needed
];

// Check if NFT exists, fallback to 12 if not
if (!isset($nfts[$nft_id])) {
    $nft_id = 12;
}

$current_nft = $nfts[$nft_id];

include '../includes/layouts/header.php';
?>

<!-- Link to custom NFT styles -->
<link rel="stylesheet" href="nft-styles.css">

<!-- NFT Detail Section -->
<section class="nft-showcase">
    <div class="container-fluid">
        <!-- Top Section: Split layout for header content -->
        <div class="row no-gutters">
            <!-- Left Panel - NFT Header & Document Image -->
            <div class="col-md-6 nft-details-panel">
                <div class="nft-document">
                    <!-- Back to Collection Link -->
                    <div class="back-link mb-3">
                        <a href="index.php" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left me-2"></i>Back to Collection
                        </a>
                    </div>

                    <!-- Document Header -->
                    <div class="document-header">
                        <h1 class="nft-title"><?php echo htmlspecialchars($current_nft['title']); ?></h1>
                        <div class="coming-soon-badge">
                            <span><?php echo htmlspecialchars($current_nft['status']); ?></span>
                        </div>

                        <!-- Document Image - Below Coming Soon -->
                        <div class="document-image-container mt-3">
                            <?php if (file_exists($current_nft['document_image'])): ?>
                                <img src="<?php echo $current_nft['document_image']; ?>"
                                    alt="<?php echo $current_nft['title']; ?> Document" class="document-image">
                            <?php else: ?>
                                <div class="document-image-placeholder">
                                    <div class="placeholder-content">
                                        <i class="fas fa-file-image fa-2x mb-2"></i>
                                        <p class="mb-0"><small>Document Image</small></p>
                                        <p class="mb-0"><small><?php echo $current_nft['title']; ?></small></p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Credits Section -->
                    <div class="document-credits">
                        <p class="document-text">
                            <?php echo htmlspecialchars($current_nft['title']); ?><br>
                            by <?php echo htmlspecialchars($current_nft['author']); ?><br>
                            <em><?php echo htmlspecialchars($current_nft['collection']); ?></em>
                        </p>

                        <p class="document-text">
                            <strong><?php echo htmlspecialchars($current_nft['subtitle']); ?></strong>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Right Panel - NFT Artwork (Fixed Height) -->
            <div class="col-md-6 nft-artwork-panel">
                <div class="artwork-container">
                    <div class="character-illustration">
                        <!-- RYVAH Branding Header -->
                        <div class="ryvah-branding">
                            <h2 class="ryvah-title">RYVAH</h2>
                        </div>

                        <!-- Main character artwork -->
                        <div class="character-image">
                            <?php if (file_exists($current_nft['character_image'])): ?>
                                <img src="<?php echo $current_nft['character_image']; ?>"
                                    alt="<?php echo $current_nft['title']; ?> Character"
                                    class="img-fluid character-artwork">
                            <?php else: ?>
                                <div class="character-placeholder">
                                    <div class="text-center">
                                        <div class="anime-character-placeholder">
                                            <div class="character-silhouette"></div>
                                            <p class="character-label">Anime
                                                Character<br><?php echo $current_nft['title']; ?></p>
                                            <small class="upload-hint">Upload:
                                                nft-character-<?php echo $nft_id; ?>.jpg</small>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Website watermark -->
                        <div class="website-badge">
                            <span class="site-name">RyvahCommerce.com</span>
                        </div>

                        <!-- Small portrait in corner -->
                        <div class="corner-portrait">
                            <?php if (file_exists($current_nft['portrait_image'])): ?>
                                <img src="<?php echo $current_nft['portrait_image']; ?>" alt="Character Portrait"
                                    class="portrait-img">
                            <?php else: ?>
                                <div class="portrait-placeholder">
                                    <div class="mini-portrait"></div>
                                    <small class="portrait-hint">Portrait</small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Section: Full-width flowing text content -->
        <div class="row no-gutters">
            <div class="col-12">
                <div class="nft-content-flow">
                    <div class="document-content-extended">
                        <div class="document-section">
                            <p class="document-text">
                                <?php echo htmlspecialchars($current_nft['main_text']); ?>
                            </p>

                            <p class="document-text">
                                <?php echo htmlspecialchars($current_nft['description']); ?>
                            </p>

                            <?php foreach ($current_nft['additional_text'] as $text): ?>
                                <p class="document-text">
                                    <?php echo htmlspecialchars($text); ?>
                                </p>
                            <?php endforeach; ?>

                            <!-- Navigation to other NFTs -->
                            <div class="nft-navigation mt-5">
                                <h4 class="mb-3">Explore More NFTs</h4>
                                <div class="row">
                                    <?php foreach ($nfts as $id => $nft_data): ?>
                                        <?php if ($id != $nft_id): ?>
                                            <div class="col-md-4 mb-3">
                                                <a href="detail.php?id=<?php echo $id; ?>"
                                                    class="btn btn-outline-primary w-100">
                                                    <i class="fas fa-eye me-2"></i><?php echo $nft_data['title']; ?>
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    /* Additional styles for detail page */
    .back-link {
        border-bottom: 1px solid #ddd;
        padding-bottom: 15px;
    }

    .nft-navigation {
        border-top: 2px solid #8B4513;
        padding-top: 30px;
        margin-top: 40px;
    }

    .nft-navigation h4 {
        color: #8B4513;
        font-family: 'Georgia', serif;
    }

    .nft-navigation .btn {
        transition: all 0.3s ease;
    }

    .nft-navigation .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    /* Make sure window title shows NFT title */
</style>

<script>
    // Update page title to show current NFT
    document.title = '<?php echo htmlspecialchars($current_nft['title']); ?> - Ryvah NFT Collection';
</script>

<?php include '../includes/layouts/footer.php'; ?>