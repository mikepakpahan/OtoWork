<?php
// 1. Cek Active Page biar gak error undefined variable
$activePage = isset($activePage) ? $activePage : '';

// 2. Logic Hitung Keranjang (Cart Count)
$cart_count = 0;
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    // Asumsi $conn sudah didefinisikan di file induk yang meng-include navbar ini
    if (isset($conn)) {
        $sql_count = "SELECT COUNT(id) AS total_items FROM carts WHERE user_id = ?";
        $stmt_count = $conn->prepare($sql_count);

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
}
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/customer/assets/css/navbar.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<header class="site-header">
    <div class="header-container">
        <div class="header-logo">
            <a href="<?= BASE_URL ?>">
                <i class="fas fa-tools logo-icon"></i> 
                <span>OTOWORK</span>
            </a>
        </div>

        <nav class="nav-desktop">
            <a href="<?= BASE_URL ?>/customer/views/home.php" class="nav-link <?= ($activePage == 'home') ? 'active' : ''; ?>">Home</a>

            <a href="<?= BASE_URL ?>/customer/views/spareparts.php" class="nav-link <?= ($activePage == 'spareparts') ? 'active' : ''; ?>">Sparepart</a>

            <div class="dropdown">
                <a href="#" class="nav-link dropdown-toggle<?= ($activePage == 'services') ? 'active' : ''; ?>">
                    Booking <i class="fas fa-chevron-down dropdown-arrow"></i>
                </a>
                <div class="dropdown-content dropdown-menu">
                    <a href="<?= BASE_URL ?>/customer/views/booking.php">Booking Servis</a>
                    <a href="<?= BASE_URL ?>/customer/views/home.php#services">Semua Layanan</a>
                </div>
            </div>

            <a href="#footer" class="nav-link">Tentang Kami</a>
        </nav>

        <div class="nav-actions">
            <?php if (isset($_SESSION["user_id"])): ?>
                <a href="<?= BASE_URL ?>/customer/views/cart.php" class="cart-icon-wrapper">
                    <img class="cart-icon" src="<?= BASE_URL ?>/assets/img/icon/icon-cart.png" alt="Cart">
                    <?php if ($cart_count > 0): ?>
                        <span class="cart-indicator"><?= $cart_count; ?></span>
                    <?php endif; ?>
                </a>

                <div class="dropdown user-dropdown">
                    <a href="#" class="nav-link welcome-text dropdown-toggle">
                        Hi, <?= htmlspecialchars(explode(' ', $_SESSION["name"])[0]); ?>
                        <i class="fas fa-chevron-down dropdown-arrow"></i>
                    </a>
                    <div class="dropdown-content dropdown-menu">
                        <a href="<?= BASE_URL ?>/customer/views/riwayat.php">Riwayat Saya</a>
                        <a href="<?= BASE_URL ?>/customer/views/profil.php">Profil</a>
                        <a href="<?= BASE_URL ?>/logic/auth.php?act=logout">Logout</a>
                    </div>
                </div>

            <?php else: ?>
                <a href="<?= BASE_URL ?>/customer/views/login.php" class="btn-login">Login</a>
            <?php endif; ?>

            <button id="hamburger-btn" class="nav-toggle">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </div>
</header>

<nav id="mobile-menu" class="nav-mobile hidden absolute top-20 left-0 w-full bg-gray-900 p-4 shadow-lg z-50" style="background-color: #1f2937; color: white;">
    <a href="<?= BASE_URL ?>/customer/views/home.php" class="block py-3 px-4 text-white hover:bg-gray-700 rounded">Home</a>
    <a href="<?= BASE_URL ?>/customer/views/spareparts.php" class="block py-3 px-4 text-white hover:bg-gray-700 rounded">Sparepart</a>
    <a href="<?= BASE_URL ?>/customer/views/home.php#booking-section" class="block py-3 px-4 text-white hover:bg-gray-700 rounded">Booking</a>
    <a href="#footer" class="block py-3 px-4 text-white hover:bg-gray-700 rounded">Tentang Kami</a>
    <hr class="my-2 border-gray-700">

    <?php if (isset($_SESSION["user_id"])): ?>
        <a href="<?= BASE_URL ?>/customer/views/riwayat.php" class="block py-3 px-4 text-white hover:bg-gray-700 rounded">Riwayat Saya</a>
        <a href="<?= BASE_URL ?>/logic/logout.php" class="block py-3 px-4 text-red-400 hover:bg-gray-700 rounded">Logout</a>
    <?php else: ?>
        <a href="<?= BASE_URL ?>/customer/views/login.php" class="block py-3 px-4 text-blue-400 font-bold hover:bg-gray-700 rounded">Login</a>
    <?php endif; ?>
</nav>

<div class="header-spacer"></div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const toggles = document.querySelectorAll(".dropdown-toggle");

        toggles.forEach(toggle => {
            toggle.addEventListener("click", function (e) {
                e.preventDefault();

                const menu = this.parentElement.querySelector(".dropdown-menu");

                // Tutup dropdown lain yang terbuka
                document.querySelectorAll(".dropdown-menu.show").forEach(openMenu => {
                    if (openMenu !== menu) openMenu.classList.remove("show");
                });

                // Toggle dropdown saat ini
                menu.classList.toggle("show");
            });
        });

        // Klik di luar → tutup semua dropdown
        document.addEventListener("click", function (e) {
            if (!e.target.closest(".dropdown")) {
                document.querySelectorAll(".dropdown-menu.show").forEach(menu => {
                    menu.classList.remove("show");
                });
            }
        });
    });

    // Script Toggle Mobile Menu
    const hamburgerBtn = document.getElementById('hamburger-btn');
    const mobileMenu = document.getElementById('mobile-menu');

    if (hamburgerBtn && mobileMenu) {
        hamburgerBtn.addEventListener('click', function() {
            // Toggle class 'hidden' (jika pakai tailwind di body) atau ubah display manual
            if(mobileMenu.style.display === 'block' || !mobileMenu.classList.contains('hidden')) {
                mobileMenu.classList.add('hidden');
                mobileMenu.style.display = 'none';
            } else {
                mobileMenu.classList.remove('hidden');
                mobileMenu.style.display = 'block';
            }
        });
    }
</script>