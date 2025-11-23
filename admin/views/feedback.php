<?php
require_once '../../config/database.php';

// Cek Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "/customer/views/login.php");
    exit;
}

$pageTitle = 'Feedback Pelanggan';
$activeMenu = 'feedback'; // Nanti kita tambah menu ini di sidebar

// Ambil Data
$sql = "SELECT * FROM feedback ORDER BY submitted_at DESC";
$result = $conn->query($sql);

$unread = [];
$read = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Asumsi di database lama status defaultnya 'new'
        if ($row['status'] === 'new') {
            $unread[] = $row;
        } else {
            $read[] = $row;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inbox Feedback | OtoWork Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .tab-content {
            display: none;
            animation: fadeIn 0.3s ease;
        }

        .tab-content.active {
            display: block;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
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

                <div class="mb-6">
                    <h3 class="text-2xl font-bold text-gray-700">Suara Pelanggan</h3>
                    <p class="text-gray-500">Kritik, saran, dan masukan dari customer.</p>
                </div>

                <div class="flex space-x-4 border-b border-gray-300 mb-6">
                    <button onclick="switchTab('unread')" id="tab-unread" class="tab-btn px-4 py-2 font-bold text-yellow-600 border-b-2 border-yellow-500 transition-colors focus:outline-none relative">
                        <i class="fas fa-envelope mr-2"></i> Belum Dibaca
                        <?php if (count($unread) > 0): ?>
                            <span class="ml-2 bg-red-500 text-white text-xs px-2 py-0.5 rounded-full"><?= count($unread) ?></span>
                        <?php endif; ?>
                    </button>
                    <button onclick="switchTab('read')" id="tab-read" class="tab-btn px-4 py-2 font-medium text-gray-500 hover:text-gray-700 transition-colors focus:outline-none">
                        <i class="fas fa-envelope-open mr-2"></i> Riwayat (<?= count($read) ?>)
                    </button>
                </div>

                <div id="view-unread" class="tab-content active">
                    <?php if (!empty($unread)): ?>
                        <div class="space-y-4">
                            <?php foreach ($unread as $row): ?>
                                <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-yellow-400 hover:shadow-lg transition">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <div class="flex items-center gap-2 mb-1">
                                                <h4 class="font-bold text-lg text-gray-800"><?= htmlspecialchars($row['name']) ?></h4>
                                                <span class="text-xs bg-yellow-100 text-yellow-800 px-2 py-0.5 rounded-full">Baru</span>
                                            </div>
                                            <p class="text-sm text-gray-500 mb-3">
                                                <i class="fas fa-envelope w-4"></i> <?= htmlspecialchars($row['email']) ?> &nbsp;|&nbsp;
                                                <i class="fas fa-clock w-4"></i> <?= date('d M Y • H:i', strtotime($row['submitted_at'])) ?>
                                            </p>
                                        </div>
                                        <button onclick="markAsRead(<?= $row['id'] ?>)" class="text-blue-600 hover:text-blue-800 text-sm font-semibold border border-blue-200 px-3 py-1 rounded hover:bg-blue-50 transition">
                                            <i class="fas fa-check-double mr-1"></i> Tandai Dibaca
                                        </button>
                                    </div>
                                    <div class="bg-gray-50 p-4 rounded text-gray-700 italic border border-gray-100">
                                        "<?= nl2br(htmlspecialchars($row['message'])) ?>"
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-12 bg-white rounded-lg shadow-sm">
                            <i class="fas fa-check-circle text-4xl text-green-400 mb-3"></i>
                            <p class="text-gray-500">Inbox bersih! Tidak ada pesan baru.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <div id="view-read" class="tab-content">
                    <?php if (!empty($read)): ?>
                        <div class="space-y-4">
                            <?php foreach ($read as $row): ?>
                                <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-200 opacity-90 hover:opacity-100 transition">
                                    <div class="flex justify-between items-start mb-2">
                                        <div>
                                            <h4 class="font-bold text-gray-700"><?= htmlspecialchars($row['name']) ?></h4>
                                            <p class="text-xs text-gray-400"><?= date('d M Y • H:i', strtotime($row['submitted_at'])) ?></p>
                                        </div>
                                        <button onclick="deleteFeedback(<?= $row['id'] ?>)" class="text-red-400 hover:text-red-600 transition" title="Hapus Pesan">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                    <p class="text-gray-600 text-sm mt-2"><?= nl2br(htmlspecialchars($row['message'])) ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-center text-gray-500 py-10">Belum ada riwayat pesan yang dibaca.</p>
                    <?php endif; ?>
                </div>

            </main>
        </div>
    </div>

    <script>
        // Tab Logic
        function switchTab(tabName) {
            // Hide all
            document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('font-bold', 'text-yellow-600', 'border-b-2', 'border-yellow-500');
                btn.classList.add('font-medium', 'text-gray-500');
            });

            // Show selected
            document.getElementById('view-' + tabName).classList.add('active');
            const btn = document.getElementById('tab-' + tabName);
            btn.classList.remove('font-medium', 'text-gray-500');
            btn.classList.add('font-bold', 'text-yellow-600', 'border-b-2', 'border-yellow-500');
        }

        // Mark Read Logic
        function markAsRead(id) {
            Swal.fire({
                title: 'Tandai sudah dibaca?',
                text: "Pesan akan pindah ke tab Riwayat.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3B82F6',
                confirmButtonText: 'Ya, Tandai',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `<?= BASE_URL ?>/logic/feedback_master.php?act=mark_read&id=${id}`;
                }
            });
        }

        // Delete Logic
        function deleteFeedback(id) {
            Swal.fire({
                title: 'Hapus Pesan?',
                text: "Pesan ini akan dihapus permanen.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#EF4444',
                confirmButtonText: 'Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `<?= BASE_URL ?>/logic/feedback_master.php?act=delete&id=${id}`;
                }
            });
        }

        // Session Alert
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