<?php
require_once '../../config/database.php';
$activePage = 'spareparts'; // Buat nandain menu navbar

// Ambil Hero Product (Featured)
$hero_product = null;
$res_hero = $conn->query("SELECT * FROM spareparts WHERE is_featured = 1 AND is_active = 1 LIMIT 1");
if ($res_hero && $res_hero->num_rows > 0) {
    $hero_product = $res_hero->fetch_assoc();
}

// Ambil Semua Produk Aktif
$products = [];
$res_prod = $conn->query("SELECT * FROM spareparts WHERE is_active = 1 AND stock > 0 ORDER BY id DESC");
if ($res_prod && $res_prod->num_rows > 0) {
    while ($row = $res_prod->fetch_assoc()) {
        $products[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Belanja Sparepart | OtoWork</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans+Condensed:wght@300;700&display=swap" rel="stylesheet" />
</head>

<body class="bg-gray-50 font-sans">

    <?php require_once '../includes/navbar.php'; ?>

    <?php if ($hero_product): ?>
        <section class="relative bg-[#0c0a27] text-white py-16 overflow-hidden">
            <div class="absolute inset-0 opacity-10" style="background-image: url('<?= BASE_URL ?>/assets/img/pattern.png');"></div>

            <div class="container mx-auto px-6 relative z-10 flex flex-col md:flex-row items-center gap-10">
                <div class="flex-1 text-center md:text-left">
                    <span class="bg-yellow-500 text-black text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider mb-4 inline-block">Produk Unggulan</span>
                    <h1 class="text-4xl md:text-5xl font-bold mb-4 leading-tight text-white">
                        <?= htmlspecialchars($hero_product['part_name']) ?>
                    </h1>
                    <p class="text-gray-300 text-lg mb-6 line-clamp-3">
                        <?= htmlspecialchars($hero_product['description']) ?>
                    </p>
                    <div class="flex flex-col md:flex-row gap-4 justify-center md:justify-start">
                        <button onclick="addToCart(<?= $hero_product['id'] ?>)" class="bg-[#FFC72C] hover:bg-yellow-500 text-black font-bold py-3 px-8 rounded-lg shadow-lg transform hover:scale-105 transition flex items-center justify-center gap-2">
                            <i class="fas fa-shopping-cart"></i> BELI SEKARANG
                        </button>
                        <span class="text-2xl font-bold text-white flex items-center">
                            Rp <?= number_format($hero_product['price'], 0, ',', '.') ?>
                        </span>
                    </div>
                </div>
                <div class="flex-1 flex justify-center">
                    <div class="relative w-80 h-80 md:w-96 md:h-96 bg-white rounded-full p-4 shadow-2xl animate-pulse-slow">
                        <img src="<?= BASE_URL ?>/assets/img/spareparts/<?= $hero_product['image_url'] ?>"
                            class="w-full h-full object-contain hover:scale-110 transition duration-500"
                            alt="Hero Product">
                    </div>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <section class="container mx-auto px-6 py-12">

        <div class="flex flex-col md:flex-row justify-between items-center mb-10 gap-4">
            <div>
                <h2 class="text-3xl font-bold text-gray-800 border-l-4 border-yellow-500 pl-4">Katalog Sparepart</h2>
                <p class="text-gray-500 mt-1 pl-4">Temukan onderdil terbaik buat motormu.</p>
            </div>

            <div class="relative w-full md:w-96">
                <input type="text" id="searchInput" onkeyup="filterProducts()" placeholder="Cari knalpot, oli, ban..."
                    class="w-full pl-12 pr-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:border-yellow-500 shadow-sm transition">
                <i class="fas fa-search absolute left-4 top-4 text-gray-400"></i>
            </div>
        </div>

        <div id="productGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
            <?php if (!empty($products)): ?>
                <?php foreach ($products as $prod): ?>
                    <div class="product-card bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition duration-300 group flex flex-col h-full border border-gray-100">
                        <div class="relative h-56 overflow-hidden bg-gray-100 p-4 flex items-center justify-center">
                            <img src="<?= BASE_URL ?>/assets/img/spareparts/<?= $prod['image_url'] ?>"
                                alt="<?= htmlspecialchars($prod['part_name']) ?>"
                                class="max-h-full max-w-full object-contain group-hover:scale-110 transition duration-500">
                            <?php if ($prod['stock'] < 5): ?>
                                <span class="absolute top-2 right-2 bg-red-500 text-white text-xs px-2 py-1 rounded font-bold">Stok Menipis!</span>
                            <?php endif; ?>
                        </div>

                        <div class="p-5 flex-1 flex flex-col">
                            <h3 class="product-name text-lg font-bold text-gray-800 mb-2 group-hover:text-yellow-600 transition">
                                <?= htmlspecialchars($prod['part_name']) ?>
                            </h3>
                            <p class="text-gray-500 text-sm mb-4 line-clamp-2 flex-1">
                                <?= htmlspecialchars($prod['description']) ?>
                            </p>

                            <div class="flex justify-between items-center mt-auto pt-4 border-t border-gray-100">
                                <span class="text-lg font-bold text-gray-900">
                                    Rp <?= number_format($prod['price'], 0, ',', '.') ?>
                                </span>

                                <?php if (isset($_SESSION['user_id'])): ?>
                                    <button onclick="addToCart(<?= $prod['id'] ?>)" class="bg-gray-900 hover:bg-yellow-500 hover:text-black text-white p-3 rounded-full shadow transition-all duration-300 transform active:scale-95" title="Tambah ke Keranjang">
                                        <i class="fas fa-cart-plus"></i>
                                    </button>
                                <?php else: ?>
                                    <button onclick="mintaLogin()" class="bg-gray-200 text-gray-400 p-3 rounded-full cursor-not-allowed" title="Login dulu bos">
                                        <i class="fas fa-lock"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-span-full text-center py-20">
                    <img src="<?= BASE_URL ?>/assets/img/empty-box.png" class="w-32 mx-auto mb-4 opacity-50" alt="Kosong">
                    <p class="text-gray-500 text-xl">Yah, belum ada barang nih di gudang.</p>
                </div>
            <?php endif; ?>
        </div>

        <div id="noResult" class="hidden col-span-full text-center py-10">
            <p class="text-gray-500 text-lg">Waduh, barang yang lo cari gak ketemu bro. Coba kata kunci lain?</p>
        </div>

    </section>

    <?php require_once '../includes/footer.php'; ?>

    <script>
        // 1. Search Filter Logic (Client Side)
        function filterProducts() {
            let input = document.getElementById('searchInput').value.toLowerCase();
            let cards = document.getElementsByClassName('product-card');
            let hasResult = false;

            for (let i = 0; i < cards.length; i++) {
                let name = cards[i].querySelector('.product-name').innerText.toLowerCase();
                if (name.includes(input)) {
                    cards[i].style.display = ""; // Show
                    hasResult = true;
                } else {
                    cards[i].style.display = "none"; // Hide
                }
            }

            // Tampilkan pesan kosong kalo ga ada hasil
            document.getElementById('noResult').style.display = hasResult ? "none" : "block";
        }

        // 2. Add to Cart Logic (AJAX Fetch)
        function addToCart(productId) {
            let formData = new FormData();
            formData.append('product_id', productId);

            fetch('<?= BASE_URL ?>/logic/cart_master.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Toast Sukses
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: 'Masuk Keranjang!',
                            showConfirmButton: false,
                            timer: 1500,
                            background: '#10B981',
                            color: '#fff'
                        });

                        // Update Badge Keranjang di Navbar
                        let badge = document.querySelector('.cart-indicator');
                        if (badge) {
                            badge.innerText = data.cart_count;
                            badge.style.display = 'block';
                        } else {
                            // Kalo badge belum ada (0 item), reload aja biar muncul wkwk
                            location.reload();
                        }
                    } else {
                        // Error (Stok habis / belum login)
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: data.message,
                            confirmButtonColor: '#EF4444'
                        });
                    }
                })
                .catch(err => console.error(err));
        }

        function mintaLogin() {
            Swal.fire({
                title: 'Login Dulu Yuk!',
                text: "Buat belanja, kamu harus login dulu.",
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#FFC72C',
                confirmButtonText: 'Ke Halaman Login',
                cancelButtonText: 'Nanti Aja'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '<?= BASE_URL ?>/customer/views/login.php';
                }
            });
        }
    </script>
</body>

</html>