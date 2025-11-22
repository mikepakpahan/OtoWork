<?php
require '../../../backend/config.php';

$pageTitle = 'Queue Service';
$activeMenu = 'queue';

$sql = "SELECT 
            sb.id, 
            u.name, 
            u.email, 
            sb.motor_type, 
            sb.booking_date,
            sb.price, 
            sb.complaint 
        FROM service_bookings sb
        JOIN users u ON sb.user_id = u.id
        WHERE sb.status = 'confirmed_by_user'
        ORDER BY sb.booking_date ASC";

$result = $conn->query($sql);

include '../template-header.php'; 
include '../template-sidebar.php';
?>

<link rel="stylesheet" href="../style.css">
<link rel="stylesheet" href="queue.css">

<main class="main-content">
    <div class="table-container">
        <table class="queue-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>Tipe Motor</th>
                    <th>Tanggal Booking</th>
                    <th>Keluhan</th>
                    <th>Harga (Rp)</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result && $result->num_rows > 0) {
                    $counter = 1;
                    while($row = $result->fetch_assoc()) {
                        $formatted_date = date('d/m/Y', strtotime($row['booking_date']));
                        $formatted_price = number_format($row['price'], 0, ',', '.');
                        
                        echo "<tr>";
                        echo "<td>" . $counter . "</td>";
                        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['motor_type']) . "</td>";
                        echo "<td>" . $formatted_date . "</td>";
                        echo "<td class='description-cell'>" . htmlspecialchars($row['complaint']) . "</td>";
                        echo "<td><strong>" . $formatted_price . "</strong></td>";
                        echo "<td class='action-cell'>
                                <button class='btn-done' data-booking-id='" . $row['id'] . "'>Done</button>
                                <button class='btn-delete' data-booking-id='" . $row['id'] . "'>Delete</button>
                              </td>";
                        echo "</tr>";
                        $counter++;
                    }
                } else {
                    echo "<tr><td colspan='7' style='text-align:center; padding: 20px;'>Belum ada antrian servis untuk saat ini.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</main>
<script src="script.js"></script>
<script src="../script.js"></script>