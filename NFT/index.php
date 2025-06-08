<?php

include '../includes/layouts/header.php'; ?>

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
            <?php
            // NFT data array - same as in detail.php
            $nfts = [
                1 => [
                    'title' => 'NFT #1',
                    'subtitle' => '13x17 painting #1 "P!nk"',
                    'author' => 'Michael J. Leonard',
                    'collection' => 'NFT RY VAH',
                    'status' => '©2023 RYVAH',
                    'main_text' => 'I am strengthening the Constitutional Right to Freedom of Speech by normalizing nudity.',
                    'description' => 'Inspired by album cover of: P!ink.',
                    'character_image' => '../assets/images/nft-character-1.jpg'
                ],
                2 => [
                    'title' => 'NFT #2',
                    'subtitle' => '13x17 painting #2 "Red and the Robot"',
                    'author' => 'Michael J. Leonard',
                    'collection' => 'NFT RY VAH',
                    'status' => '©2023 RYVAH',
                    'main_text' => 'I am strengthening the Constitutional Right to Freedom of Speech by normalizing nudity.',
                    'description' => '"We must never allow nudity to be prohibited. Nudity is Divinity. Nudity is normal"',
                    'character_image' => '../assets/images/nft-character-2.jpg'
                ],
                3 => [
                    'title' => 'NFT #3',
                    'subtitle' => '13x17 painting #3 "Lolita Hathawatts"',
                    'author' => 'Michael J. Leonard',
                    'collection' => 'NFT RY VAH',
                    'status' => '©2023 RYVAH',
                    'main_text' => 'I am strengthening the Constitutional Right to Freedom of Speech by normalizing nudity.',
                    'description' => 'In the cockpit of a mecha defending nudity, love and romance.',
                    'character_image' => '../assets/images/nft-character-3.jpg'
                ],
                4 => [
                    'title' => 'NFT #4',
                    'subtitle' => '13x17 painting #4 "Fraya Hathawatts"',
                    'author' => 'Michael J. Leonard',
                    'collection' => 'NFT RY VAH',
                    'status' => '©2023 RYVAH',
                    'main_text' => 'I am strengthening the Constitutional Right to Freedom of Speech by normalizing nudity.',
                    'description' => 'A large circle of light encompasses the girl in a future vision.',
                    'character_image' => '../assets/images/nft-character-4.jpg'
                ],
                5 => [
                    'title' => 'NFT #5',
                    'subtitle' => '13x17 painting #5 "Lilith & Eve"',
                    'author' => 'Michael J. Leonard',
                    'collection' => 'NFT RY VAH',
                    'status' => '©2023 RYVAH',
                    'main_text' => 'I am strengthening the Constitutional Right to Freedom of Speech by normalizing nudity.',
                    'description' => 'Two girls at play - exploring freedom and divine nudity.',
                    'character_image' => '../assets/images/nft-character-5.jpg'
                ],
                6 => [
                    'title' => 'NFT #6',
                    'subtitle' => '13x17 painting #6 "Yumaria"',
                    'author' => 'Michael J. Leonard',
                    'collection' => 'NFT RY VAH',
                    'status' => '©2023 RYVAH',
                    'main_text' => 'I am strengthening the Constitutional Right to Freedom of Speech by normalizing nudity.',
                    'description' => 'A powerful statement on freedom with dual layers.',
                    'character_image' => '../assets/images/nft-character-6.jpg'
                ],
                7 => [
                    'title' => 'NFT #7',
                    'subtitle' => '13x17 painting #7 "Yumaria and the Robot"',
                    'author' => 'Michael J. Leonard',
                    'collection' => 'NFT RY VAH',
                    'status' => '©2023 RYVAH',
                    'main_text' => 'I am strengthening the Constitutional Right to Freedom of Speech by normalizing nudity.',
                    'description' => 'Constitutional Rights to Free Speech and Bear Arms.',
                    'character_image' => '../assets/images/nft-character-7.jpg'
                ],
                8 => [
                    'title' => 'NFT #8',
                    'subtitle' => '13x17 painting #8 "Clockwork Doll Fraya"',
                    'author' => 'Michael J. Leonard',
                    'collection' => 'NFT RY VAH',
                    'status' => '©2023 RYVAH',
                    'main_text' => 'I am strengthening the Constitutional Right to Freedom of Speech by normalizing nudity.',
                    'description' => 'A robot exploring the nature of nudity and freedom.',
                    'character_image' => '../assets/images/nft-character-8.jpg'
                ],
                9 => [
                    'title' => 'NFT #9',
                    'subtitle' => '13x17 painting #9 "Blonde Fairies"',
                    'author' => 'Michael J. Leonard',
                    'collection' => 'NFT RY VAH',
                    'status' => '©2023 RYVAH',
                    'main_text' => 'I am strengthening the Constitutional Right to Freedom of Speech by normalizing nudity.',
                    'description' => 'Inspired by the movie \'Wizards\' from the \'70s.',
                    'character_image' => '../assets/images/nft-character-9.jpg'
                ],
                10 => [
                    'title' => 'NFT #10',
                    'subtitle' => '13x17 painting #10 "Orange Fairies"',
                    'author' => 'Michael J. Leonard',
                    'collection' => 'NFT RY VAH',
                    'status' => '©2023 RYVAH',
                    'main_text' => 'I am strengthening the Constitutional Right to Freedom of Speech by normalizing nudity.',
                    'description' => 'Artworks from \'Wizards\' representing love and innocence.',
                    'character_image' => '../assets/images/nft-character-10.jpg'
                ],
                11 => [
                    'title' => 'NFT #11',
                    'subtitle' => '13x17 painting #11 "Red Fairies"',
                    'author' => 'Michael J. Leonard',
                    'collection' => 'NFT RY VAH',
                    'status' => '©2023 RYVAH',
                    'main_text' => 'I am strengthening the Constitutional Right to Freedom of Speech by normalizing nudity.',
                    'description' => 'Symbol of reclaiming lost freedoms and rights.',
                    'character_image' => '../assets/images/nft-character-11.jpg'
                ],
                12 => [
                    'title' => 'NFT #12',
                    'subtitle' => '13x17 painting #12 "Elk Horn Succubus"',
                    'author' => 'Michael J. Leonard',
                    'collection' => 'NFT RY VAH',
                    'status' => '©2023 RYVAH',
                    'main_text' => 'I am strengthening the Constitutional Right to Freedom of Speech by normalizing nudity.',
                    'description' => 'Dual images exploring freedom of thought and expression.',
                    'character_image' => '../assets/images/nft-character-12.jpg'
                ],
                13 => [
                    'title' => 'NFT #13',
                    'subtitle' => '13x17 painting #13 "Pink Medusa"',
                    'author' => 'Michael J. Leonard',
                    'collection' => 'NFT RY VAH',
                    'status' => '©2023 RYVAH',
                    'main_text' => 'I am strengthening the Constitutional Right to Freedom of Speech by normalizing nudity.',
                    'description' => 'Exploring how conduct remains unchanged regardless of nudity.',
                    'character_image' => '../assets/images/nft-character-13.jpg'
                ],
                14 => [
                    'title' => 'NFT #14',
                    'subtitle' => '13x17 painting #14 "Ecneconni on wall"',
                    'author' => 'Michael J. Leonard',
                    'collection' => 'NFT RY VAH',
                    'status' => '©2023 RYVAH',
                    'main_text' => 'I am strengthening the Constitutional Right to Freedom of Speech by normalizing nudity.',
                    'description' => 'Eyes and hair grab attention while nudity is simply natural.',
                    'character_image' => '../assets/images/nft-character-14.jpg'
                ],
                15 => [
                    'title' => 'NFT #15',
                    'subtitle' => '13x17 painting #15 "Hippocampus"',
                    'author' => 'Michael J. Leonard',
                    'collection' => 'NFT RY VAH',
                    'status' => '©2023 RYVAH',
                    'main_text' => 'I am strengthening the Constitutional Right to Freedom of Speech by normalizing nudity.',
                    'description' => 'Comparing nude vs. clothed - nature\'s truest form.',
                    'character_image' => '../assets/images/nft-character-15.jpg'
                ],
                16 => [
                    'title' => 'NFT #16',
                    'subtitle' => '13x17 painting #16 "Fairy N31"',
                    'author' => 'Michael J. Leonard',
                    'collection' => 'NFT RY VAH',
                    'status' => '©2023 RYVAH',
                    'main_text' => 'I am strengthening the Constitutional Right to Freedom of Speech by normalizing nudity.',
                    'description' => 'Yellow wings and purple backdrop - nudity as divine default.',
                    'character_image' => '../assets/images/nft-character-16.jpg'
                ],
                17 => [
                    'title' => 'NFT #17',
                    'subtitle' => '13x17 painting #17 "Astral Dragon"',
                    'author' => 'Michael J. Leonard',
                    'collection' => 'NFT RY VAH',
                    'status' => '©2023 RYVAH',
                    'main_text' => 'I am strengthening the Constitutional Right to Freedom of Speech by normalizing nudity.',
                    'description' => 'Emeralds, dragons, and nudity - what should be most common?',
                    'character_image' => '../assets/images/nft-character-17.jpg'
                ],
                18 => [
                    'title' => 'NFT #18',
                    'subtitle' => '13x17 painting #18 "Flourish Chin Cat"',
                    'author' => 'Michael J. Leonard',
                    'collection' => 'NFT RY VAH',
                    'status' => '©2023 RYVAH',
                    'main_text' => 'I am strengthening the Constitutional Right to Freedom of Speech by normalizing nudity.',
                    'description' => 'Nudity should be as unremarkable as race - normal by default.',
                    'character_image' => '../assets/images/nft-character-18.jpg'
                ],
                19 => [
                    'title' => 'NFT #19',
                    'subtitle' => '13x17 painting #19 "Dark Nymph"',
                    'author' => 'Michael J. Leonard',
                    'collection' => 'NFT RY VAH',
                    'status' => '©2023 RYVAH',
                    'main_text' => 'I am strengthening the Constitutional Right to Freedom of Speech by normalizing nudity.',
                    'description' => 'Vampire fairy lesbian - nudity tells truth, clothing lies.',
                    'character_image' => '../assets/images/nft-character-19.jpg'
                ],
                20 => [
                    'title' => 'NFT #20',
                    'subtitle' => '13x17 painting #20 "Fairy Dragon"',
                    'author' => 'Michael J. Leonard',
                    'collection' => 'NFT RY VAH',
                    'status' => '©2023 RYVAH',
                    'main_text' => 'I am strengthening the Constitutional Right to Freedom of Speech by normalizing nudity.',
                    'description' => 'Magic in love depicted through nudity and dragons.',
                    'character_image' => '../assets/images/nft-character-20.jpg'
                ],
                21 => [
                    'title' => 'NFT #21',
                    'subtitle' => '13x17 painting #21 "Dryad"',
                    'author' => 'Michael J. Leonard',
                    'collection' => 'NFT RY VAH',
                    'status' => '©2023 RYVAH',
                    'main_text' => 'I am strengthening the Constitutional Right to Freedom of Speech by normalizing nudity.',
                    'description' => 'Invoking love, joy, and divine connection through nudity.',
                    'character_image' => '../assets/images/nft-character-21.jpg'
                ],
                22 => [
                    'title' => 'NFT #22',
                    'subtitle' => '13x17 painting #22 "Horn Devil"',
                    'author' => 'Michael J. Leonard',
                    'collection' => 'NFT RY VAH',
                    'status' => '©2023 RYVAH',
                    'main_text' => 'I am strengthening the Constitutional Right to Freedom of Speech by normalizing nudity.',
                    'description' => 'Devil girl with wings - eyes pulled to hair and wings.',
                    'character_image' => '../assets/images/nft-character-22.jpg'
                ],
                23 => [
                    'title' => 'NFT #23',
                    'subtitle' => '13x17 painting #23 "Spirit Owl"',
                    'author' => 'Michael J. Leonard',
                    'collection' => 'NFT RY VAH',
                    'status' => '©2023 RYVAH',
                    'main_text' => 'I am strengthening the Constitutional Right to Freedom of Speech by normalizing nudity.',
                    'description' => 'Girl with owl - nudity as natural background element.',
                    'character_image' => '../assets/images/nft-character-23.jpg'
                ],
                24 => [
                    'title' => 'NFT #24',
                    'subtitle' => '13x17 painting #24 "Jaguar Dryad"',
                    'author' => 'Michael J. Leonard',
                    'collection' => 'NFT RY VAH',
                    'status' => '©2023 RYVAH',
                    'main_text' => 'I am strengthening the Constitutional Right to Freedom of Speech by normalizing nudity.',
                    'description' => 'Cat girl - viewer focus says more about viewer than artwork.',
                    'character_image' => '../assets/images/nft-character-24.jpg'
                ]
            ];

            // Loop through each NFT and generate card
            foreach ($nfts as $nft_id => $nft_data):
                $character_img = $nft_data['character_image'];
            ?>
            <!-- NFT #<?php echo $nft_id; ?> Card -->
            <div class="col-md-6 col-lg-4">
                <div class="card nft-card h-100">
                    <div class="nft-preview-container">
                        <?php if (file_exists($character_img)): ?>
                        <img src="<?php echo $character_img; ?>" class="card-img-top nft-preview"
                            alt="<?php echo $nft_data['title']; ?>">
                        <?php else: ?>
                        <div class="nft-preview-placeholder">
                            <div class="text-center">
                                <i class="fas fa-image fa-3x mb-3"></i>
                                <h5><?php echo $nft_data['title']; ?></h5>
                                <small>Character Artwork</small>
                            </div>
                        </div>
                        <?php endif; ?>
                        <div class="nft-badge">
                            <span class="badge bg-success"><?php echo $nft_data['status']; ?></span>
                        </div>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($nft_data['title']); ?></h5>
                        <p class="card-text text-muted">By <?php echo htmlspecialchars($nft_data['author']); ?></p>
                        <p class="card-text">
                            <small><?php echo htmlspecialchars($nft_data['description']); ?></small>
                        </p>
                        <div class="d-flex justify-content-between align-items-center">
                            <small
                                class="text-muted"><?php echo substr($nft_data['subtitle'], 0, 20) . '...'; ?></small>
                            <a href="detail?id=<?php echo $nft_id; ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-eye me-1"></i>View Details
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
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