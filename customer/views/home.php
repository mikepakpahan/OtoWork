<?php
// Config & Logic PHP tetap ada
require_once '../../config/database.php';

// Logic Session / Auth check
// ... (Logic PHP lainnya biarkan di atas sini)
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>OtoWork | Revolutionizing Auto Service</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <link rel="stylesheet" href="<?= BASE_URL ?>/customer/assets/css/home.css" />
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body>

    <?php 
    $activePage = 'home';
    require_once '../includes/navbar.php'; 
    ?>

    <section class="hero-section">
        <div class="hero-content">
            <h1 class="hero-title">
                Revolutionizing Auto Service & Parts
            </h1>
            <p class="hero-desc">
                Seamlessly book appointments and discover genuine auto parts with our cutting-edge platform.
            </p>
            <div class="hero-buttons">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="#booking-form" class="btn-hero-primary">Book Service Now</a>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>/customer/views/login.php" class="btn-hero-primary">Book Service Now</a>
                <?php endif; ?>
                
                <a href="<?= BASE_URL ?>/customer/views/spareparts.php" class="btn-hero-secondary">Find Parts</a>
            </div>
        </div>
    </section>

    <section class="features-section">
        <div class="section-header">
            <h2 class="section-title">Your Digital Auto Hub</h2>
            <p class="section-subtitle">Experience a smarter way to manage your vehicle's needs with our integrated solutions.</p>
        </div>

        <div class="features-grid">
            <div class="feature-card">
                <div class="icon-wrapper">
                    <i class="fas fa-store"></i>
                </div>
                <h3 class="card-title">Genuine Parts Marketplace</h3>
                <p class="card-desc">Access a vast inventory of authentic spare parts, verified for quality and compatibility.</p>
            </div>

            <div class="feature-card">
                <div class="icon-wrapper">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <h3 class="card-title">Smart Scheduling System</h3>
                <p class="card-desc">Effortlessly book, reschedule, and manage your service appointments online.</p>
            </div>

            <div class="feature-card">
                <div class="icon-wrapper">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h3 class="card-title">AI-Powered Diagnostics</h3>
                <p class="card-desc">Get precise service recommendations and diagnostic insights for your vehicle.</p>
            </div>
        </div>
    </section>

    <section class="steps-section">
        <div class="section-header">
            <h2 class="section-title">How It Works: Seamless Integration</h2>
            <p class="section-subtitle">Getting started is as simple as 1-2-3.</p>
        </div>

        <div class="steps-container">
            <div class="steps-line"></div> <div class="step-item">
                <div class="step-number">1</div>
                <h4 class="step-title">Select Your Slot</h4>
                <p class="step-desc">Choose a convenient date and time that fits your schedule.</p>
            </div>

            <div class="step-item">
                <div class="step-number">2</div>
                <h4 class="step-title">Add Parts (Optional)</h4>
                <p class="step-desc">Specify any parts you require for the service.</p>
            </div>

            <div class="step-item">
                <div class="step-number">3</div>
                <h4 class="step-title">Visit the Workshop</h4>
                <p class="step-desc">Bring your vehicle to the designated workshop at your appointment time.</p>
            </div>
        </div>
    </section>

    <?php require_once '../includes/footer.php'; ?>

    <?php if (isset($_SESSION['alert'])): ?>
        <script>
            Swal.fire({
                title: '<?= $_SESSION['alert']['type'] == 'success' ? 'Success!' : 'Failed!' ?>',
                text: '<?= $_SESSION['alert']['message'] ?>',
                icon: '<?= $_SESSION['alert']['type'] ?>',
                confirmButtonColor: '#3b82f6',
                background: '#151b2b',
                color: '#fff'
            });
        </script>
    <?php unset($_SESSION['alert']); endif; ?>

</body>
</html>