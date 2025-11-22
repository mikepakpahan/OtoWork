<?php

$cart_count = 0;
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    $user_id = $_SESSION['user_id'];
    $sql_count = "SELECT COUNT(id) AS total_items FROM carts WHERE user_id = ?";
    $stmt_count = $conn->prepare($sql_count);
    $stmt_count->bind_param("i", $user_id);
    $stmt_count->execute();
    $result_count = $stmt_count->get_result();
    if ($result_count) {
        $row_count = $result_count->fetch_assoc();
        $cart_count = $row_count['total_items'];
    }
}
?>

<link rel="stylesheet" href="/EfkaWorkshop/assets/libs/sweetalert2/sweetalert2.min.css">
<script src="/EfkaWorkshop/assets/libs/sweetalert2/sweetalert2.all.min.js"></script>

<link rel="stylesheet" href="/EfkaWorkshop/Pages/customer/header.css">

<header class="site-header">
    <div class="header-container">
        <div class="header-logo">
            <a href="/EfkaWorkshop/index.php">
                <img src="/EfkaWorkshop/assets/logo-efka.png" alt="EFKA Workshop Logo" />
                <span>EFKA WORKSHOP</span>
            </a>
        </div>

        <nav class="nav-desktop">
            <a href="/EfkaWorkshop/index.php" class="nav-link <?php if($activePage == 'home') echo 'active'; ?>">Home</a>
            <a href="/EfkaWorkshop/Pages/customer/spareparts/sparepart.php" class="nav-link <?php if($activePage == 'spareparts') echo 'active'; ?>">Spareparts</a>
            <div class="dropdown">
                <a href="#" class="nav-link">Layanan <i class="fas fa-chevron-down dropdown-arrow"></i></a>
                <div class="dropdown-content">
                    <a href="/EfkaWorkshop/index.php#services">Semua Layanan</a>
                    <a href="/EfkaWorkshop/#booking-section">Booking Servis</a>
                </div>
            </div>
            <a href="/EfkaWorkshop/index.php#footer" class="nav-link">Kontak</a>
        </nav>

        <div class="nav-actions">
            <?php if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true): ?>
                <a href="/EfkaWorkshop/Pages/customer/checkout/checkout.php" class="cart-icon-wrapper">
                    <img class="cart-icon" src="/EfkaWorkshop/assets/icons/icon-cart.png" alt="">
                    <?php if ($cart_count > 0): ?>
                        <span class="cart-indicator"><?php echo $cart_count; ?></span>
                    <?php endif; ?>
                </a>
                <div class="dropdown user-dropdown">
                    <a href="#" class="nav-link welcome-text">
                        Hi, <?php echo htmlspecialchars(explode(' ', $_SESSION["user_name"])[0]); ?>
                        <i class="fas fa-chevron-down dropdown-arrow"></i>
                    </a>
                    <div class="dropdown-content">
                        <a href="/EfkaWorkshop/Pages/customer/history/riwayat_saya.php">Riwayat Saya</a>
                        <a href="/EfkaWorkshop/Pages/customer/profile/profil.php">Profil</a>
                        <a href="/EfkaWorkshop/backend/logout.php">Logout</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="/EfkaWorkshop/Pages/login/login-page.php" class="btn-primary">Login</a>
            <?php endif; ?>
        </div>

        <button id="hamburger-btn" class="nav-toggle">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </div>
</header>

<nav id="mobile-menu" class="nav-mobile">
    <a href="/EfkaWorkshop/index.php" class="nav-link">Home</a>
    <a href="/EfkaWorkshop/Pages/customer/spareparts/sparepart.php" class="nav-link">Spareparts</a>
    <a href="/EfkaWorkshop/index.php#services" class="nav-link">Layanan</a>
    <a href="/EfkaWorkshop/index.php#footer" class="nav-link">Kontak</a>
    <hr>
    <?php if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true): ?>
        <a href="/EfkaWorkshop/Pages/customer/history/riwayat_saya.php" class="nav-link">Riwayat Saya</a>
        <a href="/EfkaWorkshop/backend/logout.php" class="nav-link" style="color: #ffc107;">Logout</a>
    <?php else: ?>
        <a href="/EfkaWorkshop/Pages/login/login-page.php" class="btn-primary">Login / Daftar</a>
    <?php endif; ?>
</nav>

<div class="header-spacer"></div>

<script>
    const hamburgerBtn = document.getElementById('hamburger-btn');
    const mobileMenu = document.getElementById('mobile-menu');

    if (hamburgerBtn && mobileMenu) {
        hamburgerBtn.addEventListener('click', function() {
            this.classList.toggle('is-active');
            mobileMenu.classList.toggle('is-open');
        });
    }
</script>