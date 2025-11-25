<?php
require_once '../../config/database.php';
$activePage = 'spareparts'; 

// Ambil Hero Product (Tetap di-query jg tidak apa-apa, meski di desain baru kita pakai banner statis)
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
    <title>SpareParts & Accessories | OtoWork</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans+Condensed:wght@300;700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />

    <style>
        body { font-family: 'Inter', sans-serif; }
        .hero-bg {
            /* Ganti URL ini dengan gambar background bengkel/motor yang gelap sesuai desain */
            background-image: linear-gradient(rgba(11, 14, 23, 0.8), rgba(11, 14, 23, 0.8)), url('<?= BASE_URL ?>/assets/img/hero-bg.jpg');
            background-size: cover;
            background-position: center;
        }
        /* Custom Checkbox Style */
        .custom-checkbox:checked {
            background-color: #3B82F6;
            border-color: #3B82F6;
        }
    </style>
</head>

<body class="bg-[#0B0E17] text-gray-300">

    <?php require_once '../includes/navbar.php'; ?>

    <section class="hero-bg relative text-white py-24 px-6 text-center">
        <div class="container mx-auto relative z-10 max-w-4xl">
            <h1 class="text-4xl md:text-6xl font-bold mb-4 tracking-tight">
                SpareParts And Accessories
            </h1>
            <p class="text-gray-300 text-lg mb-8 font-light">
                Seamlessly book appointments and discover genuine auto parts with our cutting-edge platform.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="<?= BASE_URL ?>/customer/views/booking.php" class="bg-[#3B82F6] hover:bg-blue-600 text-white font-semibold py-3 px-8 rounded-md transition duration-300">
                    Book Service Now
                </a>
                <a href="#product-section" class="bg-[#1F2937] hover:bg-gray-700 border border-gray-600 text-white font-semibold py-3 px-8 rounded-md transition duration-300">
                    Find Parts
                </a>
            </div>
        </div>
    </section>

    <section id="product-section" class="container mx-auto px-6 py-12">
        <div class="flex flex-col lg:flex-row gap-10">

            <aside class="w-full lg:w-1/4 space-y-8 hidden lg:block">
                <div>
                    <h3 class="text-white font-bold text-lg mb-4">Product Categories</h3>
                    <ul class="space-y-3 text-sm text-gray-400">
                        <li class="flex items-center gap-3 cursor-pointer hover:text-blue-500"><div class="w-2 h-2 bg-gray-600 rounded-sm"></div> Air Condition</li>
                        <li class="flex items-center gap-3 cursor-pointer hover:text-blue-500"><div class="w-2 h-2 bg-gray-600 rounded-sm"></div> Bearings</li>
                        <li class="flex items-center gap-3 cursor-pointer hover:text-blue-500"><div class="w-2 h-2 bg-gray-600 rounded-sm"></div> Body</li>
                        <li class="flex items-center gap-3 cursor-pointer hover:text-blue-500"><div class="w-2 h-2 bg-gray-600 rounded-sm"></div> Brakes</li>
                        <li class="flex items-center gap-3 cursor-pointer hover:text-blue-500"><div class="w-2 h-2 bg-gray-600 rounded-sm"></div> Car Accessories</li>
                        <li class="flex items-center gap-3 cursor-pointer hover:text-blue-500"><div class="w-2 h-2 bg-gray-600 rounded-sm"></div> Engine</li>
                        <li class="flex items-center gap-3 cursor-pointer hover:text-blue-500"><div class="w-2 h-2 bg-gray-600 rounded-sm"></div> Headlights & Lighting</li>
                        <li class="flex items-center gap-3 cursor-pointer hover:text-blue-500"><div class="w-2 h-2 bg-gray-600 rounded-sm"></div> Oils and Fluids</li>
                        <li class="flex items-center gap-3 cursor-pointer hover:text-blue-500"><div class="w-2 h-2 bg-gray-600 rounded-sm"></div> Tires & Wheels</li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-white font-bold text-lg mb-4">Brands</h3>
                    <ul class="space-y-3 text-sm text-gray-400">
                        <li class="flex items-center gap-2"><input type="checkbox" class="custom-checkbox rounded bg-gray-700 border-gray-600"> Aisin</li>
                        <li class="flex items-center gap-2"><input type="checkbox" class="custom-checkbox rounded bg-gray-700 border-gray-600"> AutoCheck</li>
                        <li class="flex items-center gap-2"><input type="checkbox" class="custom-checkbox rounded bg-gray-700 border-gray-600"> Castrol</li>
                        <li class="flex items-center gap-2"><input type="checkbox" class="custom-checkbox rounded bg-gray-700 border-gray-600"> Goodyear</li>
                        <li class="flex items-center gap-2"><input type="checkbox" class="custom-checkbox rounded bg-gray-700 border-gray-600"> Yokohama</li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-white font-bold text-lg mb-4">Filter by price</h3>
                    <div class="relative pt-1">
                        <input type="range" class="w-full h-1 bg-gray-700 rounded-lg appearance-none cursor-pointer range-sm" min="0" max="1000000">
                        <div class="flex justify-between text-xs text-gray-500 mt-2">
                            <span>Rp 0</span>
                            <span>Rp 10.000.000</span>
                        </div>
                        <button class="mt-4 px-4 py-1 text-xs bg-gray-700 hover:bg-gray-600 text-white rounded uppercase tracking-wider">Filter</button>
                    </div>
                </div>
            </aside>

            <div class="w-full lg:w-3/4">
                
                <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
                    <div class="text-sm text-gray-400">
                        Showing all <?= count($products) ?> results
                    </div>
                    <div class="flex items-center gap-4 w-full md:w-auto">
                        <div class="relative w-full md:w-64">
                            <input type="text" id="searchInput" onkeyup="filterProducts()" placeholder="Search parts..." 
                                class="w-full bg-[#1F2937] text-white border border-gray-700 rounded px-4 py-2 text-sm focus:outline-none focus:border-blue-500">
                            <i class="fas fa-search absolute right-3 top-2.5 text-gray-500 text-xs"></i>
                        </div>
                        <select class="bg-[#1F2937] text-white border border-gray-700 rounded px-3 py-2 text-sm focus:outline-none">
                            <option>Default sorting</option>
                            <option>Low to High</option>
                            <option>High to Low</option>
                        </select>
                    </div>
                </div>

                <div id="productGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-3 gap-6">
                    <?php if (!empty($products)): ?>
                        <?php foreach ($products as $prod): ?>
                            <div class="product-card bg-white rounded-lg overflow-hidden group hover:shadow-2xl transition duration-300 relative flex flex-col h-full">
                                
                                <div class="relative h-48 p-6 flex items-center justify-center bg-white border-b border-gray-100">
                                    <button class="absolute top-3 right-3 text-gray-400 hover:text-red-500 transition">
                                        <i class="far fa-heart"></i>
                                    </button>
                                    
                                    <?php if ($prod['stock'] < 5): ?>
                                        <span class="absolute top-3 left-3 bg-[#3B82F6] text-white text-[10px] font-bold px-2 py-1 rounded">-15%</span>
                                    <?php endif; ?>

                                    <img src="<?= BASE_URL ?>/assets/img/spareparts/<?= $prod['image_url'] ?>" 
                                         alt="<?= htmlspecialchars($prod['part_name']) ?>" 
                                         class="max-h-full max-w-full object-contain group-hover:scale-105 transition duration-500">
                                </div>

                                <div class="p-4 flex flex-col flex-1">
                                    <div class="flex text-yellow-400 text-xs mb-2">
                                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                                        <span class="text-gray-400 ml-1 text-[10px]">(4)</span>
                                    </div>

                                    <h3 class="product-name text-gray-800 font-bold text-sm mb-1 leading-snug line-clamp-2 min-h-[40px]">
                                        <?= htmlspecialchars($prod['part_name']) ?>
                                    </h3>
                                    
                                    <div class="mt-2 mb-4">
                                        <span class="text-[#10B981] font-bold text-lg">
                                            Rp <?= number_format($prod['price'], 0, ',', '.') ?>
                                        </span>
                                        <span class="text-gray-400 text-xs line-through ml-2">
                                            Rp <?= number_format($prod['price'] * 1.15, 0, ',', '.') // Mockup coret harga ?>
                                        </span>
                                    </div>

                                    <div class="mt-auto">
                                        <?php if (isset($_SESSION['user_id'])): ?>
                                            <button onclick="addToCart(<?= $prod['id'] ?>)" class="w-full bg-[#3B82F6] hover:bg-blue-700 text-white text-sm font-semibold py-2 rounded transition shadow-md">
                                                Add to cart
                                            </button>
                                        <?php else: ?>
                                            <button onclick="mintaLogin()" class="w-full bg-gray-200 text-gray-500 text-sm font-semibold py-2 rounded cursor-not-allowed">
                                                Login to Buy
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-span-full text-center py-20 text-gray-500">
                            <i class="fas fa-box-open text-4xl mb-4 opacity-50"></i>
                            <p>No products found.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <div id="noResult" class="hidden text-center py-10 text-gray-500">
                    <p>No products found matching your search.</p>
                </div>

                <div class="flex justify-center mt-12 gap-2">
                    <button class="w-8 h-8 flex items-center justify-center bg-[#3B82F6] text-white rounded text-sm">1</button>
                    <button class="w-8 h-8 flex items-center justify-center bg-[#1F2937] text-gray-400 hover:text-white rounded text-sm">2</button>
                    <button class="w-8 h-8 flex items-center justify-center bg-[#1F2937] text-gray-400 hover:text-white rounded text-sm">3</button>
                    <button class="w-8 h-8 flex items-center justify-center bg-[#1F2937] text-gray-400 hover:text-white rounded text-sm"><i class="fas fa-chevron-right"></i></button>
                </div>

            </div>
        </div>
    </section>

    <?php require_once '../includes/footer.php'; ?>

    <script>
        // 1. Search Filter Logic (Tetap Sama)
        function filterProducts() {
            let input = document.getElementById('searchInput').value.toLowerCase();
            let cards = document.getElementsByClassName('product-card');
            let hasResult = false;

            for (let i = 0; i < cards.length; i++) {
                let name = cards[i].querySelector('.product-name').innerText.toLowerCase();
                if (name.includes(input)) {
                    cards[i].style.display = ""; // Show flex default
                    hasResult = true;
                } else {
                    cards[i].style.display = "none"; // Hide
                }
            }
            document.getElementById('noResult').style.display = hasResult ? "none" : "block";
        }

        // 2. Add to Cart Logic (Tetap Sama)
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
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: 'Added to Cart!',
                            showConfirmButton: false,
                            timer: 1500,
                            background: '#10B981',
                            color: '#fff'
                        });
                        let badge = document.querySelector('.cart-indicator');
                        if (badge) {
                            badge.innerText = data.cart_count;
                            badge.style.display = 'block';
                        } else {
                            location.reload();
                        }
                    } else {
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
                title: 'Please Login',
                text: "You need to login to purchase items.",
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#3B82F6',
                confirmButtonText: 'Go to Login',
                cancelButtonText: 'Later'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '<?= BASE_URL ?>/customer/views/login.php';
                }
            });
        }
    </script>
</body>
</html>