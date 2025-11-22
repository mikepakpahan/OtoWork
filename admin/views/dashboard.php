<?php
// 1. Konfigurasi & Session Check
require_once '../../config/database.php';

// Cek apakah user berhak ada disini
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "/customer/views/login.php");
    exit;
}

// Set Variabel untuk Sidebar Active
$pageTitle = 'Dashboard Overview';
$activeMenu = 'dashboard';

// --- LOGIC PHP DARI FILE LAMA (Diadaptasi dikit) ---

// A. Grafik Pendapatan 7 Hari
$weekly_revenue_labels = [];
$weekly_revenue_data = [];
// Loop 7 hari ke belakang
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $weekly_revenue_labels[] = date('D, d M', strtotime($date));
    $weekly_revenue_data[date('Y-m-d', strtotime($date))] = 0;
}

// Query (Pastikan tabel 'history' ada isinya atau ganti nama tabel jika perlu)
$sql_weekly = "SELECT DATE(completion_date) as date, SUM(final_price) as total 
               FROM history 
               WHERE completion_date >= CURDATE() - INTERVAL 7 DAY
               GROUP BY DATE(completion_date)";
$result_weekly = $conn->query($sql_weekly);

if ($result_weekly && $result_weekly->num_rows > 0) {
    while ($row = $result_weekly->fetch_assoc()) {
        // Pastikan format tanggal match dengan key array
        $weekly_revenue_data[$row['date']] = $row['total'];
    }
}
$weekly_revenue_data = array_values($weekly_revenue_data);


// B. Grafik Pie Chart (Tipe Transaksi)
$sql_type = "SELECT transaction_type, SUM(final_price) as total FROM history GROUP BY transaction_type";
$result_type = $conn->query($sql_type);
$type_labels = [];
$type_data = [];

if ($result_type && $result_type->num_rows > 0) {
    while ($row = $result_type->fetch_assoc()) {
        $type_labels[] = ucfirst($row['transaction_type']);
        $type_data[] = $row['total'];
    }
}

// C. Info Boxes (Ringkasan)
// Pake operator ?? 0 biar gak error kalo null
$total_revenue = $conn->query("SELECT SUM(final_price) AS total FROM history")->fetch_assoc()['total'] ?? 0;
$pending_orders = $conn->query("SELECT COUNT(id) AS total FROM orders WHERE status = 'processing'")->fetch_assoc()['total'] ?? 0;
$pending_services = $conn->query("SELECT COUNT(id) AS total FROM service_bookings WHERE status = 'pending'")->fetch_assoc()['total'] ?? 0;
// Customer check berdasarkan role
$total_users = $conn->query("SELECT COUNT(id) AS total FROM users WHERE role = 'customer'")->fetch_assoc()['total'] ?? 0;

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | OtoWork Admin</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="bg-gray-100 font-sans antialiased">

    <div class="flex h-screen overflow-hidden">

        <?php require_once '../includes/sidebar.php'; ?>

        <div class="flex-1 flex flex-col w-full">

            <?php require_once '../includes/header.php'; ?>

            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-green-500 flex items-center">
                        <div class="p-3 rounded-full bg-green-100 text-green-600 mr-4">
                            <i class="fas fa-money-bill-wave fa-2x"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 uppercase font-semibold">Total Pendapatan</p>
                            <h3 class="text-2xl font-bold text-gray-800">Rp <?= number_format($total_revenue, 0, ',', '.') ?></h3>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-blue-500 flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-4">
                            <i class="fas fa-box-open fa-2x"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 uppercase font-semibold">Order Baru</p>
                            <h3 class="text-2xl font-bold text-gray-800"><?= $pending_orders ?></h3>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-yellow-500 flex items-center">
                        <div class="p-3 rounded-full bg-yellow-100 text-yellow-600 mr-4">
                            <i class="fas fa-tools fa-2x"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 uppercase font-semibold">Servis Pending</p>
                            <h3 class="text-2xl font-bold text-gray-800"><?= $pending_services ?></h3>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-purple-500 flex items-center">
                        <div class="p-3 rounded-full bg-purple-100 text-purple-600 mr-4">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 uppercase font-semibold">Total Customer</p>
                            <h3 class="text-2xl font-bold text-gray-800"><?= $total_users ?></h3>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div class="bg-white p-6 rounded-xl shadow-sm lg:col-span-2">
                        <h3 class="text-lg font-bold text-gray-700 mb-4">📊 Pendapatan 7 Hari Terakhir</h3>
                        <canvas id="weeklyRevenueChart"></canvas>
                    </div>

                    <div class="bg-white p-6 rounded-xl shadow-sm">
                        <h3 class="text-lg font-bold text-gray-700 mb-4">🍰 Komposisi Pendapatan</h3>
                        <div class="relative" style="height: 250px;">
                            <canvas id="revenueTypeChart"></canvas>
                        </div>
                    </div>
                </div>

            </main>
        </div>
    </div>

    <script>
        // Pass data dari PHP ke JS
        const weeklyLabels = <?= json_encode($weekly_revenue_labels); ?>;
        const weeklyData = <?= json_encode($weekly_revenue_data); ?>;
        const typeLabels = <?= json_encode($type_labels); ?>;
        const typeData = <?= json_encode($type_data); ?>;

        // Chart 1: Bar Chart
        const ctxWeekly = document.getElementById('weeklyRevenueChart').getContext('2d');
        new Chart(ctxWeekly, {
            type: 'bar',
            data: {
                labels: weeklyLabels,
                datasets: [{
                    label: 'Pendapatan (Rp)',
                    data: weeklyData,
                    backgroundColor: '#FFC72C',
                    borderRadius: 5,
                    barThickness: 30
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#f3f4f6'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Chart 2: Doughnut Chart
        const ctxType = document.getElementById('revenueTypeChart').getContext('2d');
        new Chart(ctxType, {
            type: 'doughnut',
            data: {
                labels: typeLabels.length ? typeLabels : ['Belum ada data'],
                datasets: [{
                    data: typeData.length ? typeData : [1], // Dummy data kalo kosong
                    backgroundColor: ['#3B82F6', '#10B981', '#F59E0B'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>
</body>

</html>