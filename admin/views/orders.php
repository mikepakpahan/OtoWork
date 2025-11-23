<?php
require_once '../../config/database.php';

// Cek Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "/customer/views/login.php");
    exit;
}

$pageTitle = 'Pesanan Masuk';
$activeMenu = 'orders';

// --- LOGIC PENGAMBILAN DATA (Sama kayak kode lama lo, cuma dirapikan) ---
// Tambahin o.address, o.payment_method, o.payment_proof
$sql = "SELECT 
            o.id AS order_id,
            o.total_amount,
            o.order_date,
            o.status,
            o.address,          
            o.payment_method,   
            o.payment_proof,    
            u.name AS user_name,
            u.email AS user_email,
            oi.quantity,
            oi.price_at_purchase,
            s.part_name,
            s.image_url
        FROM orders o
        JOIN users u ON o.user_id = u.id
        JOIN order_items oi ON o.id = oi.order_id
        JOIN spareparts s ON oi.sparepart_id = s.id
        WHERE o.status = 'processing' 
        ORDER BY o.order_date DESC";

$result = $conn->query($sql);

$orders_data = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $order_id = $row['order_id'];

        // Grouping berdasarkan Order ID
        if (!isset($orders_data[$order_id])) {
            $orders_data[$order_id] = [
                "id" => $order_id,
                "user" => [
                    "name" => $row['user_name'],
                    "email" => $row['user_email']
                ],
                "details" => [
                    "date" => $row['order_date'],
                    "total" => $row['total_amount'],
                    "address" => $row['address'],
                    "pay_method" => $row['payment_method'],
                    "pay_proof" => $row['payment_proof']
                ],
                "items" => []
            ];
        }

        // Masukkan item ke dalam list produk
        $orders_data[$order_id]['items'][] = [
            "name" => $row['part_name'],
            "qty" => $row['quantity'],
            "price" => $row['price_at_purchase'],
            "img" => $row['image_url']
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Masuk | OtoWork Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gray-100 font-sans">

    <div class="flex h-screen overflow-hidden">
        <?php require_once '../includes/sidebar.php'; ?>

        <div class="flex-1 flex flex-col w-full">
            <?php require_once '../includes/header.php'; ?>

            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">

                <div class="mb-6">
                    <h3 class="text-2xl font-bold text-gray-700">Pesanan Masuk (Processing)</h3>
                    <p class="text-gray-500">Daftar pesanan customer yang perlu dikonfirmasi.</p>
                </div>

                <?php if (!empty($orders_data)): ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">

                        <?php foreach ($orders_data as $order): ?>
                            <div class="bg-white rounded-xl shadow-md overflow-hidden border-t-4 border-yellow-400 hover:shadow-lg transition">

                                <div class="p-4 bg-gray-50 border-b flex justify-between items-start">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold">
                                            <?= strtoupper(substr($order['user']['name'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-gray-800 text-sm"><?= htmlspecialchars($order['user']['name']) ?></h4>
                                            <p class="text-xs text-gray-500"><?= date('d M Y • H:i', strtotime($order['details']['date'])) ?></p>
                                        </div>
                                    </div>
                                    <span class="bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded-full font-bold">
                                        #ORD-<?= $order['id'] ?>
                                    </span>
                                </div>

                                <div class="p-4">
                                    <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Item Dipesan</div>
                                    <ul class="space-y-3 mb-4 border-b border-gray-100 pb-4">
                                        <?php foreach ($order['items'] as $item): ?>
                                            <li class="flex justify-between items-center text-sm">
                                                <div class="flex items-center gap-2">
                                                    <img src="<?= BASE_URL ?>/assets/img/spareparts/<?= $item['img'] ?>" class="w-8 h-8 rounded object-cover bg-gray-100">
                                                    <span class="font-medium text-gray-700"><?= htmlspecialchars($item['name']) ?></span>
                                                    <span class="text-gray-400 text-xs">x<?= $item['qty'] ?></span>
                                                </div>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>

                                    <div class="bg-gray-50 p-3 rounded-lg text-sm space-y-2 mb-4">

                                        <?php
                                        // Cek apakah alamat mengandung kata kunci "AMBIL DI BENGKEL"
                                        $isPickup = strpos($order['details']['address'], 'AMBIL DI BENGKEL') !== false;
                                        ?>
                                        <div class="flex items-start gap-2">
                                            <div class="mt-1">
                                                <?php if ($isPickup): ?>
                                                    <i class="fas fa-tools text-blue-500" title="Pasang di Bengkel"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-truck text-green-500" title="Kirim ke Rumah"></i>
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <p class="font-bold text-gray-700"><?= $isPickup ? 'Pasang di Bengkel' : 'Home Delivery' ?></p>
                                                <p class="text-xs text-gray-500 leading-tight">
                                                    <?= htmlspecialchars($order['details']['address']) ?>
                                                </p>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-2 border-t border-gray-200 pt-2 mt-2">
                                            <i class="fas fa-wallet text-yellow-600"></i>
                                            <span class="font-bold text-gray-700">
                                                <?= ($order['details']['pay_method'] == 'cod') ? 'COD (Bayar Ditempat)' : 'Transfer Bank' ?>
                                            </span>

                                            <?php if ($order['details']['pay_method'] == 'transfer'): ?>
                                                <button onclick="lihatBukti('<?= BASE_URL ?>/assets/img/proofs/<?= $order['details']['pay_proof'] ?>')" class="text-xs bg-blue-100 text-blue-600 px-2 py-1 rounded hover:bg-blue-200 transition ml-auto font-bold">
                                                    <i class="fas fa-image mr-1"></i> Cek Bukti
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-600 font-bold text-sm">Total Tagihan</span>
                                        <span class="text-xl font-bold text-green-600">Rp<?= number_format($order['details']['total'], 0, ',', '.') ?></span>
                                    </div>
                                </div>

                                <div class="p-4 bg-gray-50 border-t flex gap-2">
                                    <button onclick="prosesOrder(<?= $order['id'] ?>, 'reject')" class="flex-1 bg-white border border-red-500 text-red-500 py-2 rounded hover:bg-red-50 transition font-bold text-sm">
                                        <i class="fas fa-times mr-1"></i> Tolak
                                    </button>
                                    <button onclick="prosesOrder(<?= $order['id'] ?>, 'accept')" class="flex-1 bg-green-500 text-white py-2 rounded hover:bg-green-600 transition font-bold text-sm shadow-sm">
                                        <i class="fas fa-check mr-1"></i> Proses
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>

                    </div>
                <?php else: ?>
                    <div class="flex flex-col items-center justify-center h-64 bg-white rounded-lg shadow-sm">
                        <div class="p-4 rounded-full bg-green-50 text-green-500 mb-3">
                            <i class="fas fa-check-circle text-4xl"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-700">Semua Aman!</h3>
                        <p class="text-gray-500">Tidak ada pesanan baru yang perlu diproses.</p>
                    </div>
                <?php endif; ?>

            </main>
        </div>
    </div>

    <script>
        // Logic Konfirmasi SweetAlert
        function prosesOrder(id, action) {
            let titleText = action === 'accept' ? 'Terima Pesanan?' : 'Tolak Pesanan?';
            let bodyText = action === 'accept' ?
                'Status akan berubah menjadi Completed dan barang siap dikirim.' :
                'Pesanan akan dibatalkan dan hilang dari list ini.';
            let btnColor = action === 'accept' ? '#10B981' : '#EF4444';

            Swal.fire({
                title: titleText,
                text: bodyText,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: btnColor,
                cancelButtonColor: '#6B7280',
                confirmButtonText: 'Ya, Proses!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Redirect ke logic master
                    window.location.href = `<?= BASE_URL ?>/logic/orders_master.php?act=${action}&id=${id}`;
                }
            })
        }

        // FUNGSI LIHAT BUKTI TRANSFER (Popup Image)
        function lihatBukti(imageUrl) {
            Swal.fire({
                title: 'Bukti Transfer Customer',
                imageUrl: imageUrl,
                imageWidth: 400,
                imageAlt: 'Bukti Transfer',
                showCloseButton: true,
                confirmButtonText: 'Tutup',
                confirmButtonColor: '#333'
            });
        }

        // Alert dari PHP Session
        <?php if (isset($_SESSION['alert'])): ?>
            Swal.fire({
                icon: '<?= $_SESSION['alert']['type'] ?>',
                title: '<?= $_SESSION['alert']['type'] == 'success' ? 'Berhasil' : 'Info' ?>',
                text: '<?= $_SESSION['alert']['message'] ?>',
                confirmButtonColor: '#FFC72C'
            });
        <?php unset($_SESSION['alert']);
        endif; ?>
    </script>

</body>

</html>