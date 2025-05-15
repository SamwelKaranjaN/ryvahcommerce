<?php include '../includes/layouts/header.php'; ?>

<!-- About Hero Section -->
<section class="about-hero position-relative overflow-hidden">
    <div class="hero-overlay"></div>
    <div class="container position-relative text-center" style="z-index: 2;">
        <h1 class="display-4 fw-bold animate__animated animate__fadeInDown">About Ryvah Books</h1>
        <p class="lead animate__animated animate__fadeInUp animate__delay-1s">Your Premier Destination for Digital Books and Art</p>
    </div>
</section>

<!-- Our Story Section -->
<section class="py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h2 class="section-title">Our Story</h2>
                <p class="lead">Founded with a passion for literature and art</p>
                <p>Ryvah Books began with a simple mission: to make quality digital books and artwork accessible to everyone. We believe in the power of knowledge and creativity, and we're committed to providing a platform where authors and artists can share their work with the world.</p>
                <p>Our journey started with a small collection of e-books and has grown into a diverse marketplace featuring works from talented authors and artists worldwide. We're proud to be part of the digital revolution in publishing and art distribution.</p>
            </div>
            <div class="col-md-6">
                <img src="https://images.unsplash.com/photo-1507842217343-583bb7270b66?ixlib=rb-4.0.3" alt="Library" class="img-fluid rounded shadow">
            </div>
        </div>
    </div>
</section>

<!-- Our Values Section -->
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center section-title mb-5">Our Values</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-book fa-3x text-primary mb-3"></i>
                        <h4>Quality Content</h4>
                        <p>We carefully curate our collection to ensure the highest quality books and artwork for our customers.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-users fa-3x text-primary mb-3"></i>
                        <h4>Community Focus</h4>
                        <p>We foster a vibrant community of readers, authors, and artists who share our passion for creativity.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-globe fa-3x text-primary mb-3"></i>
                        <h4>Global Reach</h4>
                        <p>We connect creators with audiences worldwide, making quality content accessible to everyone.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Team Section -->
<section class="py-5">
    <div class="container">
        <h2 class="text-center section-title mb-5">Our Team</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card team-card border-0 shadow-sm">
                    <img src="https://images.unsplash.com/photo-1560250097-0b93528c311a?ixlib=rb-4.0.3" class="card-img-top" alt="Team Member">
                    <div class="card-body text-center">
                        <h5 class="card-title">John Smith</h5>
                        <p class="text-muted">Founder & CEO</p>
                        <p class="card-text">Passionate about literature and digital innovation.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card team-card border-0 shadow-sm">
                    <img src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?ixlib=rb-4.0.3" class="card-img-top" alt="Team Member">
                    <div class="card-body text-center">
                        <h5 class="card-title">Sarah Johnson</h5>
                        <p class="text-muted">Content Director</p>
                        <p class="card-text">Expert in digital publishing and content curation.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card team-card border-0 shadow-sm">
                    <img src="https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?ixlib=rb-4.0.3" class="card-img-top" alt="Team Member">
                    <div class="card-body text-center">
                        <h5 class="card-title">Michael Brown</h5>
                        <p class="text-muted">Technical Director</p>
                        <p class="card-text">Ensuring seamless digital experiences.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.about-hero {
    background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('https://images.unsplash.com/photo-1507842217343-583bb7270b66?ixlib=rb-4.0.3');
    background-size: cover;
    background-position: center;
    height: 400px;
    display: flex;
    align-items: center;
    color: white;
}

.hero-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, rgba(0,0,0,0.7), rgba(0,0,0,0.3));
}

.section-title {
    position: relative;
    padding-bottom: 15px;
    margin-bottom: 30px;
}

.section-title:after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 50px;
    height: 3px;
    background: #007bff;
}

.team-card {
    transition: transform 0.3s;
}

.team-card:hover {
    transform: translateY(-5px);
}

.team-card img {
    height: 300px;
    object-fit: cover;
}
</style>

<?php include '../includes/layouts/footer.php'; ?> 