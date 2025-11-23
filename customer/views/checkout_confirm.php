<?php
require_once '../../config/database.php';

// Pastikan user login & ada data kiriman dari Cart
if (!isset($_SESSION['user_id']) || !isset($_POST['cart_ids'])) {
    header("Location: cart.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$cart_ids = $_POST['cart_ids']; // Array ID cart yang dipilih
$cart_ids_str = implode(',', array_map('intval', $cart_ids)); // Ubah jadi string "1,2,5" buat query

// Ambil detail barang yang mau dibeli
$sql = "SELECT c.id, c.quantity, s.part_name, s.price, s.image_url 
        FROM carts c JOIN spareparts s ON c.sparepart_id = s.id 
        WHERE c.id IN ($cart_ids_str) AND c.user_id = $user_id";
$result = $conn->query($sql);

$items = [];
$grand_total = 0;
while ($row = $result->fetch_assoc()) {
    $items[] = $row;
    $grand_total += ($row['price'] * $row['quantity']);
}

if (empty($items)) {
    header("Location: cart.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Konfirmasi Pesanan | OtoWork</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
</head>

<body class="bg-gray-100 font-sans">

    <?php require_once '../includes/navbar.php'; ?>

    <div class="container mx-auto px-4 py-10">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-3xl font-bold text-gray-800 mb-8 text-center">Checkout & Pembayaran</h1>

            <form action="<?= BASE_URL ?>/logic/checkout_master.php" method="POST" enctype="multipart/form-data" class="flex flex-col md:flex-row gap-8">
                <input type="hidden" name="act" value="process_order">
                <input type="hidden" name="cart_ids_str" value="<?= $cart_ids_str ?>">

                <div class="w-full md:w-3/5 space-y-6">

                    <div class="bg-white p-6 rounded-xl shadow-sm mb-6">
                        <h3 class="text-lg font-bold mb-4 border-b pb-2"><i class="fas fa-truck text-green-600 mr-2"></i> Opsi Pengiriman</h3>

                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <label class="cursor-pointer">
                                <input type="radio" name="delivery_type" value="delivery" checked onclick="toggleDelivery('delivery')" class="peer sr-only">
                                <div class="p-4 border rounded-lg peer-checked:border-yellow-500 peer-checked:bg-yellow-50 hover:bg-gray-50 transition text-center h-full">
                                    <i class="fas fa-shipping-fast text-2xl mb-2 text-gray-600"></i>
                                    <div class="font-bold text-sm">Kirim ke Rumah</div>
                                </div>
                            </label>

                            <label class="cursor-pointer">
                                <input type="radio" name="delivery_type" value="pickup" onclick="toggleDelivery('pickup')" class="peer sr-only">
                                <div class="p-4 border rounded-lg peer-checked:border-yellow-500 peer-checked:bg-yellow-50 hover:bg-gray-50 transition text-center h-full">
                                    <i class="fas fa-tools text-2xl mb-2 text-gray-600"></i>
                                    <div class="font-bold text-sm">Pasang di Bengkel</div>
                                </div>
                            </label>
                        </div>

                        <div id="address-input">
                            <label class="block text-gray-600 text-sm mb-1">Alamat Lengkap Pengiriman</label>
                            <textarea name="address" id="inp-address" rows="3" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:border-yellow-500" placeholder="Jalan, Nomor Rumah, RT/RW..."></textarea>
                        </div>

                        <div id="date-input" class="hidden">
                            <div class="bg-blue-50 p-4 rounded border border-blue-100 text-sm text-blue-800 mb-3">
                                <i class="fas fa-info-circle mr-1"></i> Silahkan datang ke bengkel pada tanggal yang dipilih. Barang akan kami siapkan.
                            </div>
                            <label class="block text-gray-600 text-sm mb-1">Rencana Tanggal Kedatangan</label>
                            <input type="date" name="pickup_date" id="inp-date" min="<?= date('Y-m-d', strtotime('+1 day')) ?>" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:border-yellow-500">
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-xl shadow-sm">
                        <h3 class="text-lg font-bold mb-4 border-b pb-2"><i class="fas fa-wallet text-blue-500 mr-2"></i> Metode Pembayaran</h3>

                        <div class="space-y-3">
                            <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50 transition has-[:checked]:border-yellow-500 has-[:checked]:bg-yellow-50">
                                <input type="radio" name="payment_method" value="transfer" checked onclick="toggleProof(true)" class="w-5 h-5 text-yellow-500">
                                <div class="ml-3">
                                    <span class="block font-bold text-gray-800">Transfer Bank (BCA/Mandiri)</span>
                                    <span class="block text-sm text-gray-500">Kirim ke: 123-456-7890 a.n OtoWork</span>
                                </div>
                            </label>

                            <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50 transition has-[:checked]:border-yellow-500 has-[:checked]:bg-yellow-50">
                                <input type="radio" name="payment_method" value="cod" onclick="toggleProof(false)" class="w-5 h-5 text-yellow-500">
                                <div class="ml-3">
                                    <span class="block font-bold text-gray-800">Bayar di Tempat (COD)</span>
                                    <span class="block text-sm text-gray-500">Bayar tunai saat kurir sampai.</span>
                                </div>
                            </label>
                        </div>

                        <div id="proof-section" class="mt-4 p-4 bg-blue-50 rounded border border-blue-100">
                            <label class="block text-sm font-bold text-blue-800 mb-2">Upload Bukti Transfer</label>
                            <input type="file" name="payment_proof" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-100 file:text-blue-700 hover:file:bg-blue-200">
                            <p class="text-xs text-gray-500 mt-1">*Format JPG/PNG. Pastikan foto jelas.</p>
                        </div>
                    </div>

                </div>

                <div class="w-full md:w-2/5">
                    <div class="bg-white p-6 rounded-xl shadow-sm sticky top-24">
                        <h3 class="text-lg font-bold mb-4">Ringkasan Pesanan</h3>

                        <div class="space-y-3 max-h-60 overflow-y-auto mb-4 pr-2">
                            <?php foreach ($items as $item): ?>
                                <div class="flex justify-between items-center text-sm">
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-gray-700"><?= $item['quantity'] ?>x</span>
                                        <span class="text-gray-600"><?= htmlspecialchars($item['part_name']) ?></span>
                                    </div>
                                    <span class="font-medium">Rp <?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="border-t border-dashed border-gray-300 pt-4 mt-2">
                            <div class="flex justify-between items-center text-lg font-bold">
                                <span>Total Bayar</span>
                                <span class="text-[#FFC72C]">Rp <?= number_format($grand_total, 0, ',', '.') ?></span>
                            </div>
                        </div>

                        <button type="submit" class="w-full mt-6 bg-[#FFC72C] hover:bg-yellow-500 text-black font-bold py-3 px-4 rounded-lg shadow-lg transition transform hover:scale-105">
                            <i class="fas fa-check-circle mr-2"></i> PROSES PESANAN
                        </button>

                        <a href="cart.php" class="block text-center text-sm text-gray-500 mt-4 hover:underline">Kembali ke Keranjang</a>
                    </div>
                </div>

            </form>
        </div>
    </div>

    <?php require_once '../includes/footer.php'; ?>

    <script>
        function toggleDelivery(type) {
            const addrInput = document.getElementById('address-input');
            const dateInput = document.getElementById('date-input');
            const inpAddr = document.getElementById('inp-address');
            const inpDate = document.getElementById('inp-date');

            if (type === 'delivery') {
                addrInput.classList.remove('hidden');
                dateInput.classList.add('hidden');
                inpAddr.required = true;
                inpDate.required = false;
                inpDate.value = ''; // Reset tanggal
            } else {
                addrInput.classList.add('hidden');
                dateInput.classList.remove('hidden');
                inpAddr.required = false;
                inpDate.required = true;
                inpAddr.value = ''; // Reset alamat
            }
        }
        // Jalankan sekali pas load
        toggleDelivery('delivery');

        function toggleProof(show) {
            const section = document.getElementById('proof-section');
            if (show) {
                section.style.display = 'block';
                section.querySelector('input').setAttribute('required', 'required');
            } else {
                section.style.display = 'none';
                section.querySelector('input').removeAttribute('required');
            }
        }
    </script>
</body>

</html>