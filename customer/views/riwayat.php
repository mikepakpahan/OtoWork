<?php
require_once '../../config/database.php';

// Cek Login
if (!isset($_SESSION['user_id'])) {
    $_SESSION['alert'] = ['type' => 'warning', 'message' => 'Please login to view history!'];
    header("Location: " . BASE_URL . "/customer/views/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$activePage = 'riwayat'; 

// --- QUERY GABUNGAN (UNION) ---
// Menggabungkan tabel 'orders' dan 'service_bookings'
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
    <title>Activity History | OtoWork</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
    
    <style>
        body { font-family: 'Inter', sans-serif; }
        /* Style scrollbar */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #0B0E17; }
        ::-webkit-scrollbar-thumb { background: #374151; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #4B5563; }
    </style>
</head>

<body class="bg-[#0B0E17] text-gray-300 font-sans min-h-screen flex flex-col">

    <?php require_once '../includes/navbar.php'; ?>

    <div class="container mx-auto px-4 py-12 flex-grow max-w-4xl">

        <div class="mb-10 text-center">
            <h1 class="text-3xl font-bold text-white mb-2">Activity History</h1>
            <p class="text-gray-400">Track your orders and service appointments here.</p>
        </div>

        <div class="flex justify-center space-x-3 mb-10">
            <button onclick="filterList('all')" class="filter-btn active px-6 py-2 rounded-full text-sm font-semibold bg-[#3B82F6] text-white border border-[#3B82F6] transition hover:shadow-lg hover:bg-blue-600">
                All Activity
            </button>
            <button onclick="filterList('order')" class="filter-btn px-6 py-2 rounded-full text-sm font-semibold bg-[#1F2937] text-gray-300 border border-gray-600 hover:bg-gray-700 hover:text-white transition">
                Sparepart Orders
            </button>
            <button onclick="filterList('booking')" class="filter-btn px-6 py-2 rounded-full text-sm font-semibold bg-[#1F2937] text-gray-300 border border-gray-600 hover:bg-gray-700 hover:text-white transition">
                Service Booking
            </button>
        </div>

        <div class="space-y-5" id="history-list">
            <?php if (!empty($history)): ?>
                <?php foreach ($history as $item): ?>
                    <?php
                    // Logic Warna Status untuk Dark Mode
                    $statusColor = 'bg-gray-700 text-gray-300';
                    $statusLabel = $item['status']; // default

                    if ($item['status'] == 'processing' || $item['status'] == 'pending') {
                        $statusColor = 'bg-yellow-900/50 text-yellow-300 border border-yellow-700';
                        $statusLabel = 'Processing';
                    } elseif ($item['status'] == 'approved') {
                        $statusColor = 'bg-blue-900/50 text-blue-300 border border-blue-700';
                        $statusLabel = 'Approved / Queue';
                    } elseif ($item['status'] == 'completed') {
                        $statusColor = 'bg-green-900/50 text-green-300 border border-green-700';
                        $statusLabel = 'Completed';
                    } elseif ($item['status'] == 'cancelled') {
                        $statusColor = 'bg-red-900/50 text-red-300 border border-red-700';
                        $statusLabel = 'Cancelled';
                    }

                    // Tentukan Icon & Warna Latar Icon
                    $icon = ($item['type'] == 'order') ? 'fa-shopping-bag' : 'fa-wrench';
                    $bgIcon = ($item['type'] == 'order') ? 'bg-orange-500/20 text-orange-400' : 'bg-purple-500/20 text-purple-400';
                    ?>

                    <div class="history-card bg-[#1F2937] p-6 rounded-lg shadow-lg border border-gray-700 hover:border-gray-500 transition cursor-pointer"
                        data-type="<?= $item['type'] ?>"
                        onclick="showDetail('<?= $item['type'] ?>', <?= $item['id'] ?>)">

                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                            <div class="flex gap-5 items-center">
                                <div class="w-14 h-14 rounded-full flex items-center justify-center <?= $bgIcon ?> flex-shrink-0 border border-gray-600/30">
                                    <i class="fas <?= $icon ?> text-xl"></i>
                                </div>
                                
                                <div>
                                    <h4 class="font-bold text-white text-lg">
                                        <?= ($item['type'] == 'order') ? 'Sparepart Order' : 'Service Booking (' . htmlspecialchars($item['motor']) . ')' ?>
                                    </h4>
                                    <div class="flex items-center text-xs text-gray-400 mb-1 mt-1">
                                        <i class="far fa-clock mr-2"></i> <?= date('d M Y • H:i', strtotime($item['date'])) ?>
                                    </div>
                                    <p class="text-sm text-gray-400 line-clamp-1 max-w-xs sm:max-w-md">
                                        <?= ($item['type'] == 'order') ? '<span class="text-gray-500">Address:</span> ' . htmlspecialchars($item['info']) : '<span class="text-gray-500">Issue:</span> ' . htmlspecialchars($item['info']) ?>
                                    </p>
                                </div>
                            </div>

                            <div class="w-full sm:w-auto flex flex-row sm:flex-col justify-between sm:items-end gap-2 sm:gap-1 mt-2 sm:mt-0 border-t sm:border-0 border-gray-700 pt-3 sm:pt-0">
                                <span class="inline-block px-3 py-1 rounded-full text-xs font-bold mb-1 text-center <?= $statusColor ?>">
                                    <?= ucfirst($statusLabel) ?>
                                </span>
                                
                                <p class="text-white font-bold text-lg">
                                    <?= ($item['total'] > 0) ? 'Rp ' . number_format($item['total'], 0, ',', '.') : '<span class="text-sm text-gray-500 italic">Estimated...</span>' ?>
                                </p>

                                <?php
                                $canCancel = ($item['status'] == 'processing' || $item['status'] == 'pending');
                                ?>

                                <?php if ($canCancel): ?>
                                    <button onclick="event.stopPropagation(); cancelTransaction('<?= $item['type'] ?>', <?= $item['id'] ?>)"
                                        class="mt-2 text-red-400 border border-red-500/50 hover:bg-red-500/10 text-xs font-bold px-3 py-1 rounded transition w-full sm:w-auto">
                                        Cancel
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center py-20 bg-[#1F2937] rounded-lg border border-gray-700">
                    <i class="fas fa-history text-6xl text-gray-600 mb-6"></i>
                    <p class="text-gray-400 text-lg mb-4">You have no transaction history yet.</p>
                    <a href="home.php" class="inline-block bg-[#3B82F6] hover:bg-blue-600 text-white font-bold py-2 px-6 rounded-full transition">
                        Start Shopping / Booking
                    </a>
                </div>
            <?php endif; ?>
        </div>

    </div>

    <?php require_once '../includes/footer.php'; ?>

    <script>
        function filterList(type) {
            // 1. Update Tombol Active
            document.querySelectorAll('.filter-btn').forEach(btn => {
                // Reset style ke inactive (dark mode style)
                btn.classList.remove('bg-[#3B82F6]', 'text-white', 'border-[#3B82F6]');
                btn.classList.add('bg-[#1F2937]', 'text-gray-300', 'border-gray-600');
            });
            
            // Set clicked button to active
            event.target.classList.remove('bg-[#1F2937]', 'text-gray-300', 'border-gray-600');
            event.target.classList.add('bg-[#3B82F6]', 'text-white', 'border-[#3B82F6]');

            // 2. Filter List Card
            let cards = document.querySelectorAll('.history-card');
            let hasItem = false;
            cards.forEach(card => {
                if (type === 'all' || card.dataset.type === type) {
                    card.style.display = 'block';
                    hasItem = true;
                } else {
                    card.style.display = 'none';
                }
            });
        }

        function showDetail(type, id) {
            if (type === 'order') {
                Swal.fire({
                    icon: 'info',
                    title: 'Order Details',
                    text: 'Order details view is currently under development.',
                    background: '#1F2937',
                    color: '#fff',
                    confirmButtonColor: '#3B82F6'
                });
            } else {
                Swal.fire({
                    icon: 'info',
                    title: 'Booking Status',
                    text: 'Please check the status on this card.',
                    background: '#1F2937',
                    color: '#fff',
                    confirmButtonColor: '#3B82F6'
                });
            }
        }
        
        // Dummy Cancel Function (Perlu Backend Logic)
        function cancelTransaction(type, id) {
             Swal.fire({
                title: 'Cancel Transaction?',
                text: "Are you sure you want to cancel this?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#EF4444',
                cancelButtonColor: '#374151',
                background: '#1F2937',
                color: '#fff'
            }).then((result) => {
                if (result.isConfirmed) {
                     Swal.fire({
                        title: 'Cancelled!', 
                        text: 'Your transaction has been requested to cancel.', 
                        icon: 'success',
                        background: '#1F2937',
                        color: '#fff',
                        confirmButtonColor: '#3B82F6'
                     });
                     // Disini tambahkan logic fetch/ajax ke backend utk update status jadi cancelled
                }
            })
        }

        // Alert Session
        <?php if (isset($_SESSION['alert'])): ?>
            Swal.fire({
                icon: '<?= $_SESSION['alert']['type'] ?>',
                title: 'Info',
                text: '<?= $_SESSION['alert']['message'] ?>',
                background: '#1F2937',
                color: '#fff',
                confirmButtonColor: '#3B82F6'
            });
        <?php unset($_SESSION['alert']);
        endif; ?>
    </script>

</body>

</html>