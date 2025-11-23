<?php
require_once '../../config/database.php';

// Cek Login
if (!isset($_SESSION['user_id'])) {
    $_SESSION['alert'] = ['type' => 'warning', 'message' => 'Login dulu buat liat riwayat!'];
    header("Location: " . BASE_URL . "/customer/views/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$activePage = 'riwayat'; // Nanti update navbar biar active

// --- QUERY GABUNGAN (UNION) ---
// Kita gabungin tabel 'orders' (Belanja) dan 'service_bookings' (Servis)
// Kita kasih label 'type' biar tau ini data dari tabel mana

$sql = "
    (SELECT 
        id, 
        'order' as type, 
        order_date as date, 
        status, 
        total_amount as total, 
        address as info, 
        NULL as motor 
    FROM orders WHERE user_id = $user_id)

    UNION ALL

    (SELECT 
        id, 
        'booking' as type, 
        booking_date as date, 
        status, 
        price as total, 
        complaint as info, 
        motor_type as motor 
    FROM service_bookings WHERE user_id = $user_id)

    ORDER BY date DESC
";

$result = $conn->query($sql);
$history = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $history[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Saya | OtoWork</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gray-50 font-sans">

    <?php require_once '../includes/navbar.php'; ?>

    <div class="container mx-auto px-4 py-10 max-w-4xl">

        <div class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-gray-800">Riwayat Aktivitas</h1>
            <p class="text-gray-500 mt-1">Pantau status belanjaan dan jadwal servismu di sini.</p>
        </div>

        <div class="flex justify-center space-x-2 mb-8">
            <button onclick="filterList('all')" class="filter-btn active px-4 py-2 rounded-full text-sm font-bold bg-gray-800 text-white transition">Semua</button>
            <button onclick="filterList('order')" class="filter-btn px-4 py-2 rounded-full text-sm font-bold bg-white text-gray-600 hover:bg-gray-100 transition border">Belanja Sparepart</button>
            <button onclick="filterList('booking')" class="filter-btn px-4 py-2 rounded-full text-sm font-bold bg-white text-gray-600 hover:bg-gray-100 transition border">Booking Servis</button>
        </div>

        <div class="space-y-4" id="history-list">
            <?php if (!empty($history)): ?>
                <?php foreach ($history as $item): ?>
                    <?php
                    $statusColor = 'bg-gray-100 text-gray-600';
                    $statusLabel = $item['status']; // default

                    if ($item['status'] == 'processing' || $item['status'] == 'pending') {
                        $statusColor = 'bg-yellow-100 text-yellow-800';
                        $statusLabel = 'Diproses';
                    } elseif ($item['status'] == 'approved') {
                        $statusColor = 'bg-blue-100 text-blue-800';
                        $statusLabel = 'Diterima / Antrian';
                    } elseif ($item['status'] == 'completed') {
                        $statusColor = 'bg-green-100 text-green-800';
                        $statusLabel = 'Selesai';
                    } elseif ($item['status'] == 'cancelled') {
                        $statusColor = 'bg-red-100 text-red-800';
                        $statusLabel = 'Dibatalkan';
                    }

                    // Tentukan Icon Tipe
                    $icon = ($item['type'] == 'order') ? 'fa-shopping-bag' : 'fa-wrench';
                    $bgIcon = ($item['type'] == 'order') ? 'bg-orange-100 text-orange-500' : 'bg-purple-100 text-purple-500';
                    ?>

                    <div class="history-card bg-white p-5 rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition cursor-pointer"
                        data-type="<?= $item['type'] ?>"
                        onclick="showDetail('<?= $item['type'] ?>', <?= $item['id'] ?>)">

                        <div class="flex justify-between items-start">
                            <div class="flex gap-4">
                                <div class="w-12 h-12 rounded-full flex items-center justify-center <?= $bgIcon ?> flex-shrink-0">
                                    <i class="fas <?= $icon ?> text-xl"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-800">
                                        <?= ($item['type'] == 'order') ? 'Order Sparepart' : 'Booking Servis (' . $item['motor'] . ')' ?>
                                    </h4>
                                    <p class="text-xs text-gray-400 mb-1">
                                        <i class="far fa-clock mr-1"></i> <?= date('d M Y • H:i', strtotime($item['date'])) ?>
                                    </p>

                                    <p class="text-sm text-gray-600 line-clamp-1">
                                        <?= ($item['type'] == 'order') ? 'Alamat: ' . htmlspecialchars($item['info']) : 'Keluhan: ' . htmlspecialchars($item['info']) ?>
                                    </p>
                                </div>
                            </div>

                            <div class="text-right flex flex-col items-end justify-between">

                                <div>
                                    <span class="inline-block px-3 py-1 rounded-full text-xs font-bold mb-2 <?= $statusColor ?>">
                                        <?= ucfirst($statusLabel) ?>
                                    </span>
                                    <p class="text-gray-800 font-bold">
                                        <?= ($item['total'] > 0) ? 'Rp ' . number_format($item['total'], 0, ',', '.') : 'Estimasi...' ?>
                                    </p>
                                </div>

                                <div class="mt-4">
                                    <?php
                                    // Cek apakah status masih 'processing' (Order) atau 'pending' (Booking)
                                    $canCancel = ($item['status'] == 'processing' || $item['status'] == 'pending');
                                    ?>

                                    <?php if ($canCancel): ?>
                                        <button onclick="cancelTransaction('<?= $item['type'] ?>', <?= $item['id'] ?>)"
                                            class="text-red-500 border border-red-500 hover:bg-red-50 text-xs font-bold px-3 py-1 rounded transition">
                                            Batalkan
                                        </button>
                                    <?php endif; ?>

                                </div>

                            </div>
                        </div>
                    </div>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <div class="text-center py-16">
        <img src="<?= BASE_URL ?>/assets/img/empty-history.png" class="w-48 mx-auto mb-4 opacity-50" onerror="this.src='https://via.placeholder.com/150?text=Empty'">
        <p class="text-gray-500 text-lg">Kamu belum pernah transaksi nih.</p>
        <a href="home.php" class="text-[#FFC72C] font-bold hover:underline mt-2 block">Mulai Belanja / Booking yuk!</a>
    </div>
<?php endif; ?>
    </div>

    </div>

    <?php require_once '../includes/footer.php'; ?>

    <script>
        function filterList(type) {
            // 1. Update Tombol Active
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.classList.remove('bg-gray-800', 'text-white');
                btn.classList.add('bg-white', 'text-gray-600', 'hover:bg-gray-100');
            });
            event.target.classList.remove('bg-white', 'text-gray-600', 'hover:bg-gray-100');
            event.target.classList.add('bg-gray-800', 'text-white');

            // 2. Filter List Card
            let cards = document.querySelectorAll('.history-card');
            cards.forEach(card => {
                if (type === 'all' || card.dataset.type === type) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        function showDetail(type, id) {
            // Disini nanti kita bisa bikin Modal Popup buat liat detail order (item apa aja yg dibeli)
            // Tapi buat sekarang, alert dulu aja biar logicnya jalan.
            if (type === 'order') {
                // Opsional: Redirect ke halaman detail order
                // window.location.href = 'detail_order.php?id=' + id;
                Swal.fire('Detail Order', 'Fitur lihat rincian barang sedang dikembangkan!', 'info');
            } else {
                Swal.fire('Detail Booking', 'Cek status booking di tab ini ya.', 'info');
            }
        }

        // Alert Session
        <?php if (isset($_SESSION['alert'])): ?>
            Swal.fire({
                icon: '<?= $_SESSION['alert']['type'] ?>',
                title: 'Info',
                text: '<?= $_SESSION['alert']['message'] ?>',
                confirmButtonColor: '#FFC72C'
            });
        <?php unset($_SESSION['alert']);
        endif; ?>
    </script>

</body>

</html>