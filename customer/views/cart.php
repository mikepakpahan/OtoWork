<?php
require_once '../../config/database.php';

// Cek Login
if (!isset($_SESSION['user_id'])) {
    $_SESSION['alert'] = ['type' => 'warning', 'message' => 'Login dulu buat liat keranjang!'];
    header("Location: " . BASE_URL . "/customer/views/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$activePage = 'cart';

// Ambil Data Keranjang
$sql = "SELECT 
            c.id AS cart_id, 
            c.quantity,
            s.id AS product_id, 
            s.part_name, 
            s.price, 
            s.stock,
            s.image_url
        FROM carts c
        JOIN spareparts s ON c.sparepart_id = s.id
        WHERE c.user_id = ?
        ORDER BY c.id DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$cart_items = [];
while ($row = $result->fetch_assoc()) {
    $cart_items[] = $row;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Saya | OtoWork</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gray-50 font-sans">

    <?php require_once '../includes/navbar.php'; ?>

    <div class="container mx-auto px-4 py-10">
        <h1 class="text-3xl font-bold text-gray-800 mb-8 flex items-center gap-3">
            <i class="fas fa-shopping-cart text-[#FFC72C]"></i> Keranjang Belanja
        </h1>

        <?php if (!empty($cart_items)): ?>
            <div class="flex flex-col lg:flex-row gap-8">

                <div class="lg:w-2/3">
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
                        <div class="hidden md:grid grid-cols-12 gap-4 p-4 bg-gray-100 text-sm font-bold text-gray-600 uppercase">
                            <div class="col-span-1 text-center"><input type="checkbox" id="selectAll" checked class="w-4 h-4 cursor-pointer"></div>
                            <div class="col-span-5">Produk</div>
                            <div class="col-span-3 text-center">Jumlah</div>
                            <div class="col-span-3 text-right">Total</div>
                        </div>

                        <form id="checkoutForm" action="<?= BASE_URL ?>/customer/views/checkout_confirm.php" method="POST">
                            <?php foreach ($cart_items as $item): ?>
                                <div class="cart-item p-4 border-b border-gray-100 hover:bg-yellow-50 transition duration-300" id="row-<?= $item['cart_id'] ?>">
                                    <div class="grid grid-cols-12 gap-4 items-center">

                                        <div class="col-span-1 text-center">
                                            <input type="checkbox" name="cart_ids[]" value="<?= $item['cart_id'] ?>"
                                                class="item-checkbox w-5 h-5 text-yellow-500 rounded focus:ring-yellow-500 cursor-pointer"
                                                data-price="<?= $item['price'] ?>"
                                                data-qty="<?= $item['quantity'] ?>"
                                                checked>
                                        </div>

                                        <div class="col-span-11 md:col-span-5 flex items-center gap-4">
                                            <div class="w-16 h-16 flex-shrink-0 bg-gray-200 rounded-md overflow-hidden">
                                                <img src="<?= BASE_URL ?>/assets/img/spareparts/<?= $item['image_url'] ?>"
                                                    class="w-full h-full object-cover" alt="Produk">
                                            </div>
                                            <div>
                                                <h3 class="font-bold text-gray-800 text-sm md:text-base"><?= htmlspecialchars($item['part_name']) ?></h3>
                                                <p class="text-sm text-gray-500">Rp <?= number_format($item['price'], 0, ',', '.') ?></p>
                                                <p class="text-xs text-red-500 md:hidden mt-1 font-bold sub-total-mobile">
                                                    Total: Rp <?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?>
                                                </p>
                                            </div>
                                        </div>

                                        <div class="col-span-6 md:col-span-3 flex justify-center mt-4 md:mt-0">
                                            <div class="flex items-center border border-gray-300 rounded-lg overflow-hidden">
                                                <button type="button" onclick="updateQty(<?= $item['cart_id'] ?>, 'minus')" class="px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold">-</button>
                                                <input type="text" id="qty-<?= $item['cart_id'] ?>" value="<?= $item['quantity'] ?>" readonly
                                                    class="w-12 text-center text-sm font-bold border-l border-r border-gray-300 py-1 focus:outline-none">
                                                <button type="button" onclick="updateQty(<?= $item['cart_id'] ?>, 'plus')" class="px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold">+</button>
                                            </div>
                                        </div>

                                        <div class="col-span-6 md:col-span-3 flex items-center justify-end gap-4 mt-4 md:mt-0">
                                            <span class="hidden md:block font-bold text-gray-800 sub-total-desktop" id="subtotal-<?= $item['cart_id'] ?>">
                                                Rp <?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?>
                                            </span>
                                            <button type="button" onclick="deleteItem(<?= $item['cart_id'] ?>)" class="text-gray-400 hover:text-red-500 transition" title="Hapus Item">
                                                <i class="fas fa-trash-alt fa-lg"></i>
                                            </button>
                                        </div>

                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </form>
                    </div>
                </div>

                <div class="lg:w-1/3">
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 sticky top-24">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">Ringkasan Belanja</h3>

                        <div class="flex justify-between items-center mb-2 text-gray-600">
                            <span>Total Item</span>
                            <span id="totalItems">0 Barang</span>
                        </div>

                        <div class="border-t border-gray-200 my-4 pt-4 flex justify-between items-center">
                            <span class="font-bold text-lg text-gray-800">Total Harga</span>
                            <span class="font-bold text-2xl text-[#FFC72C]" id="grandTotal">Rp 0</span>
                        </div>

                        <button onclick="submitCheckout()" class="w-full bg-[#FFC72C] hover:bg-yellow-500 text-black font-bold py-3 px-4 rounded-lg shadow-lg transform hover:scale-105 transition duration-200 flex justify-center items-center gap-2">
                            Checkout Sekarang <i class="fas fa-arrow-right"></i>
                        </button>

                        <a href="spareparts.php" class="block text-center text-gray-500 text-sm mt-4 hover:text-yellow-600 font-medium">
                            Lanjut Belanja Dulu
                        </a>
                    </div>
                </div>

            </div>
        <?php else: ?>
            <div class="text-center py-20 bg-white rounded-xl shadow-sm">
                <img src="<?= BASE_URL ?>/assets/img/empty-cart.png" class="w-48 mx-auto mb-6 opacity-75" alt="Empty Cart" onerror="this.src='https://via.placeholder.com/150?text=Empty'">
                <h2 class="text-2xl font-bold text-gray-700 mb-2">Keranjangmu Masih Kosong</h2>
                <p class="text-gray-500 mb-8">Yuk isi dengan sparepart keren buat motormu!</p>
                <a href="spareparts.php" class="bg-[#FFC72C] text-black font-bold py-3 px-8 rounded-full shadow-lg hover:bg-yellow-500 transition">
                    Mulai Belanja
                </a>
            </div>
        <?php endif; ?>

    </div>

    <?php require_once '../includes/footer.php'; ?>

    <script>
        // 1. Format Rupiah JS
        const formatter = new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        });

        // 2. Hitung Total Real-time
        function calculateTotal() {
            let total = 0;
            let count = 0;

            document.querySelectorAll('.item-checkbox:checked').forEach(cb => {
                let price = parseInt(cb.getAttribute('data-price'));
                let qty = parseInt(cb.getAttribute('data-qty'));
                total += (price * qty);
                count += qty;
            });

            document.getElementById('grandTotal').innerText = formatter.format(total);
            document.getElementById('totalItems').innerText = count + " Barang";
        }

        // 3. Update Quantity (AJAX)
        function updateQty(cartId, action) {
            let input = document.getElementById('qty-' + cartId);
            let currentQty = parseInt(input.value);
            let checkbox = document.querySelector(`.item-checkbox[value="${cartId}"]`);
            let price = parseInt(checkbox.getAttribute('data-price'));

            let newQty = (action === 'plus') ? currentQty + 1 : currentQty - 1;
            if (newQty < 1) return; // Ga boleh 0

            let formData = new FormData();
            formData.append('act', 'update_qty');
            formData.append('cart_id', cartId);
            formData.append('quantity', newQty);

            // Disable input biar ga spam klik
            input.disabled = true;

            fetch('<?= BASE_URL ?>/logic/cart_action.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    input.disabled = false;
                    if (data.status === 'success') {
                        // Update UI Angka
                        input.value = newQty;
                        checkbox.setAttribute('data-qty', newQty);

                        // Update Subtotal Text
                        let subTotal = formatter.format(price * newQty);
                        document.getElementById('subtotal-' + cartId).innerText = subTotal;

                        // Recalculate Grand Total
                        calculateTotal();
                    } else {
                        Swal.fire('Gagal', data.message, 'error');
                    }
                })
                .catch(err => console.error(err));
        }

        // 4. Hapus Item (AJAX)
        function deleteItem(cartId) {
            Swal.fire({
                title: 'Hapus Item?',
                text: "Barang ini bakal ilang dari keranjang.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#EF4444',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    let formData = new FormData();
                    formData.append('act', 'delete');
                    formData.append('cart_id', cartId);

                    fetch('<?= BASE_URL ?>/logic/cart_action.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.status === 'success') {
                                // Hapus baris HTML
                                document.getElementById('row-' + cartId).remove();

                                // Update Badge Navbar (kalo ada elemennya)
                                let badge = document.querySelector('.cart-indicator');
                                if (badge) badge.innerText = data.cart_count;

                                calculateTotal();

                                // Kalo kosong reload biar muncul gambar kosong
                                if (document.querySelectorAll('.cart-item').length === 0) {
                                    location.reload();
                                }
                            } else {
                                Swal.fire('Error', data.message, 'error');
                            }
                        });
                }
            });
        }

        // 5. Select All Logic
        document.getElementById('selectAll')?.addEventListener('change', function() {
            let checked = this.checked;
            document.querySelectorAll('.item-checkbox').forEach(cb => {
                cb.checked = checked;
            });
            calculateTotal();
        });

        // Listen checkbox individual change
        document.querySelectorAll('.item-checkbox').forEach(cb => {
            cb.addEventListener('change', calculateTotal);
        });

        // 6. Submit Checkout
        function submitCheckout() {
            if (document.querySelectorAll('.item-checkbox:checked').length === 0) {
                Swal.fire('Pilih Dulu', 'Minimal pilih satu barang buat dicheckout bro.', 'info');
                return;
            }
            document.getElementById('checkoutForm').submit();
        }

        // Init hitung pas load
        calculateTotal();
    </script>
</body>

</html>