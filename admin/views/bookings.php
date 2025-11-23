<?php
require_once '../../config/database.php';
// Cek Admin...
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "/customer/views/login.php");
    exit;
}

$pageTitle = 'Jadwal Booking';
$activeMenu = 'bookings';

// AMBIL SEMUA DATA (Kita pilah pake PHP biar gak kebanyakan query)
$sql = "SELECT b.*, u.name as user_name, u.email, u.phone_number 
        FROM service_bookings b 
        JOIN users u ON b.user_id = u.id 
        ORDER BY b.booking_date ASC";
$result = $conn->query($sql);

$pending = [];
$queue = [];
$history = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        if ($row['status'] == 'pending') {
            $pending[] = $row;
        } elseif ($row['status'] == 'approved') {
            $queue[] = $row;
        } else {
            $history[] = $row; // completed atau cancelled
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Booking | OtoWork Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* Animasi Tab Transisi */
        .tab-content {
            display: none;
            animation: fadeIn 0.3s ease-in-out;
        }

        .tab-content.active {
            display: block;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body class="bg-gray-100 font-sans">

    <div class="flex h-screen overflow-hidden">
        <?php require_once '../includes/sidebar.php'; ?>

        <div class="flex-1 flex flex-col w-full">
            <?php require_once '../includes/header.php'; ?>

            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">

                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-2xl font-bold text-gray-700">Manajemen Booking Servis</h3>
                </div>

                <div class="flex space-x-2 border-b border-gray-300 mb-6">
                    <button onclick="switchTab('pending')" id="tab-pending" class="tab-btn px-4 py-2 text-sm font-medium text-yellow-600 border-b-2 border-yellow-500 focus:outline-none">
                        <i class="fas fa-clock mr-2"></i> Permintaan Baru (<?= count($pending) ?>)
                    </button>
                    <button onclick="switchTab('queue')" id="tab-queue" class="tab-btn px-4 py-2 text-sm font-medium text-gray-500 hover:text-gray-700 focus:outline-none">
                        <i class="fas fa-wrench mr-2"></i> Antrian Bengkel (<?= count($queue) ?>)
                    </button>
                    <button onclick="switchTab('history')" id="tab-history" class="tab-btn px-4 py-2 text-sm font-medium text-gray-500 hover:text-gray-700 focus:outline-none">
                        <i class="fas fa-history mr-2"></i> Riwayat (<?= count($history) ?>)
                    </button>
                </div>

                <div id="view-pending" class="tab-content active">
                    <?php if (!empty($pending)): ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <?php foreach ($pending as $row): ?>
                                <div class="bg-white p-5 rounded-lg shadow-md border-l-4 border-yellow-400">
                                    <div class="flex justify-between items-start mb-2">
                                        <h4 class="font-bold text-gray-800"><?= htmlspecialchars($row['user_name']) ?></h4>
                                        <span class="bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded-full">Pending</span>
                                    </div>
                                    <p class="text-sm text-gray-600 mb-1"><i class="fas fa-calendar-alt w-4"></i> <?= date('d M Y', strtotime($row['booking_date'])) ?></p>
                                    <p class="text-sm text-gray-600 mb-1"><i class="fas fa-motorcycle w-4"></i> <?= htmlspecialchars($row['motor_type']) ?></p>
                                    <div class="bg-gray-50 p-2 rounded text-sm text-gray-500 italic mb-4">
                                        "<?= htmlspecialchars($row['complaint']) ?>"
                                    </div>
                                    <div class="flex gap-2">
                                        <button onclick="updateStatus(<?= $row['id'] ?>, 'reject')" class="flex-1 px-3 py-2 bg-white border border-red-500 text-red-500 rounded hover:bg-red-50 text-sm font-bold">Tolak</button>
                                        <button onclick="updateStatus(<?= $row['id'] ?>, 'approve')" class="flex-1 px-3 py-2 bg-green-500 text-white rounded hover:bg-green-600 text-sm font-bold">Terima</button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-center text-gray-500 py-10">Tidak ada permintaan booking baru.</p>
                    <?php endif; ?>
                </div>

                <div id="view-queue" class="tab-content">
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <table class="min-w-full leading-normal">
                            <thead>
                                <tr class="bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase">
                                    <th class="px-5 py-3">Tgl Booking</th>
                                    <th class="px-5 py-3">Nama Customer</th>
                                    <th class="px-5 py-3">Motor</th>
                                    <th class="px-5 py-3">Keluhan</th>
                                    <th class="px-5 py-3 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($queue)): foreach ($queue as $row): ?>
                                        <tr class="border-b border-gray-200">
                                            <td class="px-5 py-4 text-sm"><?= date('d/m/Y', strtotime($row['booking_date'])) ?></td>
                                            <td class="px-5 py-4 text-sm font-bold"><?= htmlspecialchars($row['user_name']) ?></td>
                                            <td class="px-5 py-4 text-sm"><?= htmlspecialchars($row['motor_type']) ?></td>
                                            <td class="px-5 py-4 text-sm text-gray-500"><?= htmlspecialchars($row['complaint']) ?></td>
                                            <td class="px-5 py-4 text-center">
                                                <button onclick="updateStatus(<?= $row['id'] ?>, 'complete')" class="bg-blue-500 hover:bg-blue-600 text-white text-xs py-1 px-3 rounded shadow">
                                                    <i class="fas fa-check-double mr-1"></i> Selesai Servis
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach;
                                else: ?>
                                    <tr>
                                        <td colspan="5" class="px-5 py-4 text-center text-gray-500">Antrian bengkel kosong.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="view-history" class="tab-content">
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <table class="min-w-full leading-normal">
                            <thead>
                                <tr class="bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase">
                                    <th class="px-5 py-3">Tgl Booking</th>
                                    <th class="px-5 py-3">Nama</th>
                                    <th class="px-5 py-3">Motor</th>
                                    <th class="px-5 py-3 text-center">Status Akhir</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($history)): foreach ($history as $row): ?>
                                        <tr class="border-b border-gray-200">
                                            <td class="px-5 py-4 text-sm"><?= date('d/m/Y', strtotime($row['booking_date'])) ?></td>
                                            <td class="px-5 py-4 text-sm"><?= htmlspecialchars($row['user_name']) ?></td>
                                            <td class="px-5 py-4 text-sm"><?= htmlspecialchars($row['motor_type']) ?></td>
                                            <td class="px-5 py-4 text-center">
                                                <?php if ($row['status'] == 'completed'): ?>
                                                    <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full font-bold">Selesai</span>
                                                <?php else: ?>
                                                    <span class="bg-red-100 text-red-800 text-xs px-2 py-1 rounded-full font-bold">Dibatalkan</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach;
                                else: ?>
                                    <tr>
                                        <td colspan="4" class="px-5 py-4 text-center text-gray-500">Belum ada riwayat servis.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </main>
        </div>
    </div>

    <script>
        // 1. Logic Tab Switcher (Sama kayak sebelumnya)
        function switchTab(tabName) {
            document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('text-yellow-600', 'border-b-2', 'border-yellow-500');
                btn.classList.add('text-gray-500');
            });
            document.getElementById('view-' + tabName).classList.add('active');
            const activeBtn = document.getElementById('tab-' + tabName);
            activeBtn.classList.remove('text-gray-500');
            activeBtn.classList.add('text-yellow-600', 'border-b-2', 'border-yellow-500');
        }

        // 2. Logic Update Status CANGGIH (Pake Input)
        function updateStatus(id, action) {

            // --- KASUS 1: REJECT (Butuh Alasan) ---
            if (action === 'reject') {
                Swal.fire({
                    title: 'Tolak Booking?',
                    input: 'text', // Munculin input text
                    inputLabel: 'Alasan Penolakan',
                    inputPlaceholder: 'Contoh: Bengkel penuh, Mekanik sakit...',
                    inputValidator: (value) => {
                        if (!value) {
                            return 'Alasan wajib diisi bro!';
                        }
                    },
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#EF4444',
                    confirmButtonText: 'Tolak Booking',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Kirim ID + Alasan ke logic
                        window.location.href = `<?= BASE_URL ?>/logic/booking_master.php?act=reject&id=${id}&reason=${encodeURIComponent(result.value)}`;
                    }
                });
            }

            // --- KASUS 2: COMPLETE (Butuh Harga) ---
            else if (action === 'complete') {
                Swal.fire({
                    title: 'Servis Selesai?',
                    text: 'Masukkan total biaya servis final:',
                    input: 'number', // Munculin input angka
                    inputAttributes: {
                        min: 0,
                        step: 1000
                    },
                    inputPlaceholder: 'Contoh: 150000',
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonColor: '#3B82F6',
                    confirmButtonText: 'Simpan & Selesai',
                    cancelButtonText: 'Batal',
                    inputValidator: (value) => {
                        if (!value) {
                            return 'Harga harus diisi!';
                        }
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Kirim ID + Harga ke logic
                        window.location.href = `<?= BASE_URL ?>/logic/booking_master.php?act=complete&id=${id}&price=${result.value}`;
                    }
                });
            }

            // --- KASUS 3: APPROVE (Standar) ---
            else if (action === 'approve') {
                Swal.fire({
                    title: 'Terima Booking?',
                    text: 'Masuk antrian bengkel sekarang.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#10B981',
                    confirmButtonText: 'Ya, Terima!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = `<?= BASE_URL ?>/logic/booking_master.php?act=approve&id=${id}`;
                    }
                });
            }
        }

        // 3. Alert dari Session PHP
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