<?php
$pageTitle = 'Dashboard';
$activeMenu = 'dashboard';

require '../../../backend/config.php';
include '../template-header.php';
include '../template-sidebar.php';

// 1. Data untuk Grafik Pendapatan Mingguan (7 Hari Terakhir)
$weekly_revenue_labels = [];
$weekly_revenue_data = [];
// Buat label untuk 7 hari ke belakang (dari kemarin sampai 7 hari lalu)
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $weekly_revenue_labels[] = date('D, d M', strtotime($date)); // Format: Mon, 16 Jun
    $weekly_revenue_data[date('Y-m-d', strtotime($date))] = 0; // Inisialisasi pendapatan dengan 0
}

$sql_weekly = "SELECT DATE(completion_date) as date, SUM(final_price) as total 
               FROM history 
               WHERE completion_date >= CURDATE() - INTERVAL 7 DAY
               GROUP BY DATE(completion_date)";
$result_weekly = $conn->query($sql_weekly);
if ($result_weekly && $result_weekly->num_rows > 0) {
    while($row = $result_weekly->fetch_assoc()) {
        $weekly_revenue_data[$row['date']] = $row['total'];
    }
}
// Ubah data dari array asosiatif menjadi array numerik biasa
$weekly_revenue_data = array_values($weekly_revenue_data);


// 2. Data untuk Grafik Perbandingan Tipe Transaksi
$sql_type = "SELECT transaction_type, SUM(final_price) as total 
             FROM history 
             GROUP BY transaction_type";
$result_type = $conn->query($sql_type);
$type_labels = [];
$type_data = [];
if ($result_type && $result_type->num_rows > 0) {
    while($row = $result_type->fetch_assoc()) {
        $type_labels[] = ucfirst($row['transaction_type']);
        $type_data[] = $row['total'];
    }
}

// 3. Data untuk Info Box (Contoh: Total Pendapatan, Order Baru, dll)
$sql_total_revenue = "SELECT SUM(final_price) AS total FROM history";
$total_revenue = $conn->query($sql_total_revenue)->fetch_assoc()['total'] ?? 0;

$sql_pending_orders = "SELECT COUNT(id) AS total FROM orders WHERE status = 'processing'";
$pending_orders = $conn->query($sql_pending_orders)->fetch_assoc()['total'] ?? 0;

$sql_pending_services = "SELECT COUNT(id) AS total FROM service_bookings WHERE status = 'pending'";
$pending_services = $conn->query($sql_pending_services)->fetch_assoc()['total'] ?? 0;

$sql_total_users = "SELECT COUNT(id) AS total FROM users WHERE role = 'customer'";
$total_users = $conn->query($sql_total_users)->fetch_assoc()['total'] ?? 0;

?>
<link rel="stylesheet" href="../style.css">
<style>
    .main-content { 
        padding: 2rem;
        flex-grow: 1;
        overflow-y: auto; 
    }
    .dashboard-grid { display: grid; gap: 2rem; }
    .info-boxes { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem; }
    .info-box { background: #fff; padding: 1.5rem; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
    .info-box-title { font-size: 0.9rem; color: #666; margin: 0 0 0.5rem 0; text-transform: uppercase; }
    .info-box-number { font-size: 2rem; font-weight: 700; margin: 0; }
    .chart-container { background: #fff; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
    .chart-container h3 { margin-top: 0; }
</style>

<div class="main-content">
    <h1>Dashboard</h1>
    
    <div class="info-boxes">
        <div class="info-box">
            <p class="info-box-title">Total Pendapatan</p>
            <p class="info-box-number">Rp <?php echo number_format($total_revenue, 0, ',', '.'); ?></p>
        </div>
        <div class="info-box">
            <p class="info-box-title">Order Sparepart Baru</p>
            <p class="info-box-number"><?php echo $pending_orders; ?></p>
        </div>
        <div class="info-box">
            <p class="info-box-title">Booking Servis Pending</p>
            <p class="info-box-number"><?php echo $pending_services; ?></p>
        </div>
        <div class="info-box">
            <p class="info-box-title">Total Customer</p>
            <p class="info-box-number"><?php echo $total_users; ?></p>
        </div>
    </div>

    <div class="dashboard-grid" style="margin-top: 2rem; grid-template-columns: 2fr 1fr;">
        <div class="chart-container">
            <h3>Pendapatan 7 Hari Terakhir</h3>
            <canvas id="weeklyRevenueChart"></canvas>
        </div>

        <div class="chart-container">
            <h3>Komposisi Pendapatan</h3>
            <canvas id="revenueTypeChart"></canvas>
        </div>
    </div>
</div>
<script src="../script.js"></script>
<script>
// "Menerjemahkan" data dari PHP ke JavaScript
const weeklyLabels = <?php echo json_encode($weekly_revenue_labels); ?>;
const weeklyData = <?php echo json_encode($weekly_revenue_data); ?>;
const typeLabels = <?php echo json_encode($type_labels); ?>;
const typeData = <?php echo json_encode($type_data); ?>;

// Inisialisasi Grafik 1: Pendapatan Mingguan (Bar Chart)
const ctxWeekly = document.getElementById('weeklyRevenueChart').getContext('2d');
const weeklyRevenueChart = new Chart(ctxWeekly, {
    type: 'bar',
    data: {
        labels: weeklyLabels,
        datasets: [{
            label: 'Pendapatan (Rp)',
            data: weeklyData,
            backgroundColor: 'rgba(255, 199, 44, 0.5)',
            borderColor: 'rgba(255, 199, 44, 1)',
            borderWidth: 1
        }]
    },
    options: {
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value, index, values) {
                        return 'Rp ' + value.toLocaleString('id-ID');
                    }
                }
            }
        }
    }
});

// Inisialisasi Grafik 2: Komposisi Pendapatan (Doughnut Chart)
const ctxType = document.getElementById('revenueTypeChart').getContext('2d');
const revenueTypeChart = new Chart(ctxType, {
    type: 'doughnut',
    data: {
        labels: typeLabels,
        datasets: [{
            label: 'Pendapatan',
            data: typeData,
            backgroundColor: [
                'rgba(0, 123, 255, 0.7)',
                'rgba(40, 167, 69, 0.7)',
            ],
            borderColor: [
                'rgba(0, 123, 255, 1)',
                'rgba(40, 167, 69, 1)',
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top',
            }
        }
    }
});
</script>