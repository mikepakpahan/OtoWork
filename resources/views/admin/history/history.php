<?php
$pageTitle = 'Transaction History';
$activeMenu = 'history';

require '../../../backend/config.php';
include '../template-header.php';
include '../template-sidebar.php';

$filter = $_GET['filter'] ?? 'all'; 

$sql = "SELECT 
            h.id, h.transaction_type, h.description, h.final_price, h.completion_date,
            u.name AS user_name
        FROM history h
        LEFT JOIN users u ON h.user_id = u.id";

$where_clauses = [];
if ($filter === 'service') {
    $where_clauses[] = "h.transaction_type = 'service'";
} elseif ($filter === 'sparepart') {
    $where_clauses[] = "h.transaction_type = 'sparepart'";
}

if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(' AND ', $where_clauses);
}

$sql .= " ORDER BY h.completion_date DESC";

$result = $conn->query($sql);
?>
<link rel="stylesheet" href="../style.css">
<style>
    .main-content { 
        padding: 2rem;
        flex-grow: 1;
        overflow-y: auto;
    }
    .table-container { background-color: #FFFFFF; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05); }
    .history-table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
    .history-table thead th { text-align: left; padding: 12px 16px; border-bottom: 2px solid #E5E7EB; background-color: #F9FAFB; font-weight: 600; }
    .history-table tbody td { padding: 12px 16px; border-bottom: 1px solid #F3F4F6; vertical-align: middle; }
    .history-table tbody tr:nth-child(even) { background-color: #F9FAFB; }
    .type-badge { padding: 4px 8px; border-radius: 12px; font-weight: 600; font-size: 0.75rem; color: white; }
    .type-service { background-color: #007bff; }
    .type-sparepart { background-color: #28a745; }
    .type-cancelled { background-color: #dc3545; }
    .filter-buttons { margin-bottom: 1.5rem; display: flex; gap: 1rem; }
    .filter-btn { padding: 8px 16px; border: 1px solid #ccc; background-color: #fff; border-radius: 6px; cursor: pointer; font-weight: 600; text-decoration: none; color: #333; }
    .filter-btn.active { background-color: #FFC72C; border-color: #FFC72C; color: #1F2937; }
</style>

<div class="main-content">

    <div class="filter-buttons">
        <a href="history.php?filter=all" class="filter-btn <?php if ($filter == 'all') echo 'active'; ?>">Semua</a>
        <a href="history.php?filter=service" class="filter-btn <?php if ($filter == 'service') echo 'active'; ?>">Hanya Service</a>
        <a href="history.php?filter=sparepart" class="filter-btn <?php if ($filter == 'sparepart') echo 'active'; ?>">Hanya Sparepart</a>
    </div>

    <div class="table-container">
        <table class="history-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Customer</th>
                    <th>Tipe Transaksi</th>
                    <th>Deskripsi</th>
                    <th>Total Harga (Rp)</th>
                    <th>Tanggal Selesai</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result && $result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        
                        $badge_class = '';
                        $badge_text = '';

                        // Cek dulu apakah transaksi ini dibatalkan
                        if (str_starts_with($row['description'], '[DIBATALKAN]')) {
                            $badge_class = 'type-cancelled';
                            $badge_text = 'Dibatalkan';
                        } else {
                            // Jika tidak, baru cek tipe transaksinya
                            if ($row['transaction_type'] === 'service') {
                                $badge_class = 'type-service';
                                $badge_text = 'Service';
                            } else {
                                $badge_class = 'type-sparepart';
                                $badge_text = 'Sparepart';
                            }
                        }

                        echo "<tr>";
                        echo "<td>" . $row['id'] . "</td>";
                        echo "<td>" . htmlspecialchars($row['user_name'] ?? 'User Dihapus') . "</td>";
                        echo "<td><span class='type-badge " . $badge_class . "'>" . $badge_text . "</span></td>";
                        echo "<td>" . htmlspecialchars($row['description']) . "</td>";
                        echo "<td style='text-align:right;'><strong>" . number_format($row['final_price'], 0, ',', '.') . "</strong></td>";
                        echo "<td>" . date('d M Y, H:i', strtotime($row['completion_date'])) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6' style='text-align:center; padding: 20px;'>Tidak ada riwayat transaksi yang cocok dengan filter ini.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<script src="../script.js"></script>