<?php
// 1. Cek Active Page biar gak error undefined variable
$activePage = isset($activePage) ? $activePage : '';

// 2. Logic Hitung Keranjang (Cart Count)
// Kita pakai $_SESSION['user_id'] sesuai auth.php baru
$cart_count = 0;
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    // Pastikan tabel 'carts' nanti ada ya di database baru
    $sql_count = "SELECT COUNT(id) AS total_items FROM carts WHERE user_id = ?";
    $stmt_count = $conn->prepare($sql_count);

    // Cek kalo prepare berhasil (jaga-jaga tabel belum dibuat)
    if ($stmt_count) {
        $stmt_count->bind_param("i", $user_id);
        $stmt_count->execute();
        $result_count = $stmt_count->get_result();
        if ($result_count) {
            $row_count = $result_count->fetch_assoc();
            $cart_count = $row_count['total_items'];
        }
    }
}
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/customer/assets/css/navbar.css">

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<header class="site-header">
    <div class="header-container">
        <div class="header-logo">
            <a href="<?= BASE_URL ?>">
                <span>OTOWORK</span>
            </a>
        </div>

        <nav class="nav-desktop">
            <a href="<?= BASE_URL ?>/customer/views/home.php" class="nav-link <?= ($activePage == 'home') ? 'active' : ''; ?>">Home</a>

            <a href="<?= BASE_URL ?>/customer/views/spareparts.php" class="nav-link <?= ($activePage == 'spareparts') ? 'active' : ''; ?>">Spareparts</a>

            <div class="dropdown">
                <a href="#" class="nav-link">Layanan <i class="fas fa-chevron-down dropdown-arrow"></i></a>
                <div class="dropdown-content">
                    <a href="<?= BASE_URL ?>/customer/views/home.php#services">Semua Layanan</a>
                    <a href="<?= BASE_URL ?>/customer/views/home.php#booking-section">Booking Servis</a>
                </div>
            </div>

            <a href="#footer" class="nav-link">Kontak</a>
        </nav>

        <div class="nav-actions">
            <?php if (isset($_SESSION["user_id"])): ?>
                <a href="<?= BASE_URL ?>/customer/views/cart.php" class="cart-icon-wrapper relative">
                    <img class="cart-icon w-6 h-6" src="<?= BASE_URL ?>/assets/img/icon/icon-cart.png" alt="Cart">
                    <?php if ($cart_count > 0): ?>
                        <span class="cart-indicator absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full px-1"><?= $cart_count; ?></span>
                    <?php endif; ?>
                </a>

                <div class="dropdown user-dropdown ml-4">
                    <a href="#" class="nav-link welcome-text flex items-center gap-2">
                        Hi, <?= htmlspecialchars(explode(' ', $_SESSION["name"])[0]); ?>
                        <i class="fas fa-chevron-down dropdown-arrow"></i>
                    </a>
                    <div class="dropdown-content">
                        <a href="<?= BASE_URL ?>/customer/views/riwayat.php">Riwayat Saya</a>
                        <a href="<?= BASE_URL ?>/customer/views/profil.php">Profil</a>
                        <a href="<?= BASE_URL ?>/logic/auth.php?act=logout">Logout</a>
                    </div>
                </div>

            <?php else: ?>
                <a href="<?= BASE_URL ?>/customer/views/login.php" class="btn-primary px-4 py-2 bg-yellow-400 text-black font-bold rounded hover:bg-yellow-500 transition">Login</a>
            <?php endif; ?>
        </div>

        <button id="hamburger-btn" class="nav-toggle md:hidden">
            <span class="block w-6 h-0.5 bg-white mb-1"></span>
            <span class="block w-6 h-0.5 bg-white mb-1"></span>
            <span class="block w-6 h-0.5 bg-white"></span>
        </button>
    </div>
</header>

<nav id="mobile-menu" class="nav-mobile hidden absolute top-16 left-0 w-full bg-gray-900 p-4 shadow-lg z-50">
    <a href="<?= BASE_URL ?>/customer/views/home.php" class="block py-2 text-white hover:text-yellow-400">Home</a>
    <a href="<?= BASE_URL ?>/customer/views/spareparts.php" class="block py-2 text-white hover:text-yellow-400">Spareparts</a>
    <a href="<?= BASE_URL ?>/customer/views/home.php#services" class="block py-2 text-white hover:text-yellow-400">Layanan</a>
    <hr class="my-2 border-gray-700">

    <?php if (isset($_SESSION["user_id"])): ?>
        <a href="<?= BASE_URL ?>/customer/views/riwayat.php" class="block py-2 text-white hover:text-yellow-400">Riwayat Saya</a>
        <a href="<?= BASE_URL ?>/logic/logout.php" class="block py-2 text-red-400">Logout</a>
    <?php else: ?>
        <a href="<?= BASE_URL ?>/customer/views/login.php" class="block py-2 text-yellow-400 font-bold">Login / Daftar</a>
    <?php endif; ?>
</nav>

<div class="header-spacer h-20"></div>

<script>
    // Script Toggle Mobile Menu
    const hamburgerBtn = document.getElementById('hamburger-btn');
    const mobileMenu = document.getElementById('mobile-menu');

    if (hamburgerBtn && mobileMenu) {
        hamburgerBtn.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
        });
    }
</script>