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
    <title>Shopping Cart | OtoWork</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
    
    <style>
        body { font-family: 'Inter', sans-serif; }
        /* Custom Scrollbar untuk tabel jika perlu */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #0B0E17; }
        ::-webkit-scrollbar-thumb { background: #374151; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #4B5563; }
    </style>
</head>

<body class="bg-[#0B0E17] text-gray-300 font-sans min-h-screen flex flex-col">

    <?php require_once '../includes/navbar.php'; ?>

    <div class="container mx-auto px-4 py-12 flex-grow">
        <h1 class="text-3xl font-bold text-white mb-8 flex items-center gap-3">
            <i class="fas fa-shopping-cart text-[#3B82F6]"></i> Your Shopping Cart
        </h1>

        <?php if (!empty($cart_items)): ?>
            <div class="flex flex-col lg:flex-row gap-8">

                <div class="lg:w-2/3">
                    <div class="bg-[#1F2937] rounded-lg shadow-xl overflow-hidden border border-gray-700">
                        <div class="hidden md:grid grid-cols-12 gap-4 p-4 bg-[#374151] text-sm font-semibold text-gray-200 uppercase tracking-wider">
                            <div class="col-span-1 text-center">
                                <input type="checkbox" id="selectAll" checked class="w-4 h-4 cursor-pointer rounded bg-gray-700 border-gray-500 text-blue-600 focus:ring-blue-500">
                            </div>
                            <div class="col-span-5">Product Details</div>
                            <div class="col-span-3 text-center">Quantity</div>
                            <div class="col-span-3 text-right">Total</div>
                        </div>

                        <form id="checkoutForm" action="<?= BASE_URL ?>/customer/views/checkout_confirm.php" method="POST">
                            <?php foreach ($cart_items as $item): ?>
                                <div class="cart-item p-4 border-b border-gray-700 hover:bg-[#374151] transition duration-300" id="row-<?= $item['cart_id'] ?>">
                                    <div class="grid grid-cols-12 gap-4 items-center">

                                        <div class="col-span-1 text-center">
                                            <input type="checkbox" name="cart_ids[]" value="<?= $item['cart_id'] ?>"
                                                class="item-checkbox w-5 h-5 rounded bg-gray-700 border-gray-500 text-blue-600 focus:ring-blue-500 cursor-pointer"
                                                data-price="<?= $item['price'] ?>"
                                                data-qty="<?= $item['quantity'] ?>"
                                                checked>
                                        </div>

                                        <div class="col-span-11 md:col-span-5 flex items-center gap-4">
                                            <div class="w-20 h-20 flex-shrink-0 bg-white rounded-md overflow-hidden p-2 flex items-center justify-center">
                                                <img src="<?= BASE_URL ?>/assets/img/spareparts/<?= $item['image_url'] ?>"
                                                    class="max-w-full max-h-full object-contain" alt="Produk">
                                            </div>
                                            <div>
                                                <h3 class="font-bold text-white text-sm md:text-base mb-1 hover:text-blue-400 transition cursor-pointer">
                                                    <?= htmlspecialchars($item['part_name']) ?>
                                                </h3>
                                                <p class="text-sm text-gray-400">Unit Price: <span class="text-[#3B82F6]">Rp <?= number_format($item['price'], 0, ',', '.') ?></span></p>
                                                
                                                <p class="text-xs text-yellow-400 md:hidden mt-2 font-bold sub-total-mobile">
                                                    Total: Rp <?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?>
                                                </p>
                                            </div>
                                        </div>

                                        <div class="col-span-6 md:col-span-3 flex justify-center mt-4 md:mt-0">
                                            <div class="flex items-center bg-[#111827] rounded-lg border border-gray-600 overflow-hidden">
                                                <button type="button" onclick="updateQty(<?= $item['cart_id'] ?>, 'minus')" 
                                                    class="px-3 py-1 bg-[#374151] hover:bg-gray-600 text-white transition">-</button>
                                                
                                                <input type="text" id="qty-<?= $item['cart_id'] ?>" value="<?= $item['quantity'] ?>" readonly
                                                    class="w-12 text-center text-sm font-bold bg-[#1F2937] text-white border-l border-r border-gray-600 py-1 focus:outline-none">
                                                
                                                <button type="button" onclick="updateQty(<?= $item['cart_id'] ?>, 'plus')" 
                                                    class="px-3 py-1 bg-[#374151] hover:bg-gray-600 text-white transition">+</button>
                                            </div>
                                        </div>

                                        <div class="col-span-6 md:col-span-3 flex items-center justify-end gap-4 mt-4 md:mt-0">
                                            <span class="hidden md:block font-bold text-white sub-total-desktop" id="subtotal-<?= $item['cart_id'] ?>">
                                                Rp <?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?>
                                            </span>
                                            <button type="button" onclick="deleteItem(<?= $item['cart_id'] ?>)" class="text-gray-500 hover:text-red-500 transition p-2" title="Remove Item">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>

                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </form>
                    </div>
                </div>

                <div class="lg:w-1/3">
                    <div class="bg-[#1F2937] p-6 rounded-lg shadow-xl border border-gray-700 sticky top-24">
                        <h3 class="text-lg font-bold text-white mb-6 border-b border-gray-700 pb-4">Order Summary</h3>

                        <div class="flex justify-between items-center mb-3 text-gray-400 text-sm">
                            <span>Total Items</span>
                            <span id="totalItems" class="text-white font-medium">0 Items</span>
                        </div>

                        <div class="border-t border-dashed border-gray-600 my-4 pt-4 flex justify-between items-center">
                            <span class="font-bold text-lg text-white">Grand Total</span>
                            <span class="font-bold text-2xl text-[#EF4444]" id="grandTotal">Rp 0</span>
                        </div>

                        <button onclick="submitCheckout()" class="w-full bg-[#3B82F6] hover:bg-blue-600 text-white font-bold py-3 px-4 rounded-md shadow-lg transform hover:-translate-y-1 transition duration-200 flex justify-center items-center gap-2 mt-2">
                            Proceed to Checkout <i class="fas fa-arrow-right"></i>
                        </button>

                        <a href="spareparts.php" class="block text-center text-gray-500 text-sm mt-6 hover:text-white transition">
                            <i class="fas fa-arrow-left mr-1"></i> Continue Shopping
                        </a>
                    </div>
                </div>

            </div>
        <?php else: ?>
            <div class="text-center py-20 bg-[#1F2937] rounded-lg shadow-xl border border-gray-700">
                <i class="fas fa-shopping-basket text-6xl text-gray-600 mb-6"></i>
                <h2 class="text-2xl font-bold text-white mb-2">Your cart is empty</h2>
                <p class="text-gray-400 mb-8">Looks like you haven't added any parts yet.</p>
                <a href="spareparts.php" class="bg-[#3B82F6] hover:bg-blue-600 text-white font-bold py-3 px-8 rounded-md shadow-lg transition duration-300">
                    Browse Parts
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
            document.getElementById('totalItems').innerText = count + " Items";
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

                        // Mobile Subtotal update (kalau perlu selector lebih spesifik)
                        let mobileSub = document.querySelector(`#row-${cartId} .sub-total-mobile`);
                        if(mobileSub) mobileSub.innerText = 'Total: ' + subTotal;

                        // Recalculate Grand Total
                        calculateTotal();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Failed',
                            text: data.message,
                            background: '#1F2937',
                            color: '#fff'
                        });
                    }
                })
                .catch(err => console.error(err));
        }

        // 4. Hapus Item (AJAX)
        function deleteItem(cartId) {
            Swal.fire({
                title: 'Remove Item?',
                text: "This item will be removed from your cart.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#EF4444',
                cancelButtonColor: '#374151',
                confirmButtonText: 'Yes, remove it!',
                background: '#1F2937',
                color: '#fff'
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
                                // Animasi Fade Out sebelum hapus (Opsional)
                                let row = document.getElementById('row-' + cartId);
                                row.style.opacity = '0';
                                setTimeout(() => {
                                    row.remove();
                                    calculateTotal();
                                    
                                    // Update Badge Navbar
                                    let badge = document.querySelector('.cart-indicator');
                                    if (badge) badge.innerText = data.cart_count;

                                    // Kalo kosong reload
                                    if (document.querySelectorAll('.cart-item').length === 0) {
                                        location.reload();
                                    }
                                }, 300);
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
                Swal.fire({
                    icon: 'info',
                    title: 'No items selected',
                    text: 'Please select at least one item to checkout.',
                    background: '#1F2937',
                    color: '#fff',
                    confirmButtonColor: '#3B82F6'
                });
                return;
            }
            document.getElementById('checkoutForm').submit();
        }

        // Init hitung pas load
        calculateTotal();
    </script>
</body>

</html>