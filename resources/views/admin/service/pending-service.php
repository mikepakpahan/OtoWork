<?php
require '../../../backend/config.php';

$pageTitle = 'Pending Service';
$activeMenu = 'pending';

$sql = "SELECT 
            sb.id, 
            u.name, 
            u.email, 
            sb.motor_type, 
            sb.booking_date, 
            sb.complaint 
        FROM service_bookings sb
        JOIN users u ON sb.user_id = u.id
        WHERE sb.status = 'pending'
        ORDER BY sb.booking_date ASC";

$result = $conn->query($sql);

include '../template-header.php'; 
include '../template-sidebar.php';
?>

<link rel="stylesheet" href="../style.css">
<link rel="stylesheet" href="service.css">
<style>
    .form-group input[type="text"] {
    width: 95%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 6px;
    }

</style>

<main class="main-content">
    <div class="table-container">
        <table class="pending-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Tipe Motor</th>
                    <th>Tanggal Booking</th>
                    <th>Keluhan/Deskripsi</th>
                    <th>Konfirmasi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result && $result->num_rows > 0) {
                    $counter = 1;
                    while($row = $result->fetch_assoc()) {
                        $formatted_date = date('d/m/Y', strtotime($row['booking_date']));
                        echo "<tr>";
                        echo "<td>" . $counter . "</td>";
                        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['motor_type']) . "</td>";
                        echo "<td>" . $formatted_date . "</td>";
                        echo "<td class='description-cell'>" . htmlspecialchars($row['complaint']) . "</td>";
                        echo "<td class='action-cell'>
                                <button class='btn btn-accept' data-booking-id='" . $row['id'] . "'>Accept</button>
                                <button class='btn btn-reject' data-booking-id='" . $row['id'] . "'>Reject</button>
                              </td>";
                        echo "</tr>";
                        $counter++;
                    }
                } else {
                    echo "<tr><td colspan='7' style='text-align:center; padding: 20px;'>Tidak ada service yang sedang pending.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</main>

<div id="overlay-blur" style="display:none;">
  <div class="modal-content">
    <h2>Judul Overlay</h2>
    <p>Ini isi overlay/modal.</p>
    <button id="closeOverlay">Tutup</button>
  </div>
</div>

<div id="modal-overlay" class="modal-overlay"></div>
<div id="accept-modal" class="modal">
    <div class="modal-header">
        <h2>Accept Service Booking</h2>
        <button id="close-modal-btn" class="close-btn">&times;</button>
    </div>
    <div class="modal-body">
        <div class="booking-details">
            <p><strong>Nama:</strong> <span id="modal-name"></span></p>
            <p><strong>Email:</strong> <span id="modal-email"></span></p>
            <p><strong>Tipe Motor:</strong> <span id="modal-motor-type"></span></p>
            <p><strong>Keluhan/Pesan:</strong></p>
            <p id="modal-complaint" class="complaint-text"></p>
        </div>
        <form id="accept-form" action="/EfkaWorkshop/backend/proses_accept_booking.php" method="POST">
            <input type="hidden" name="booking_id" id="modal-booking-id">
            <div class="form-group">
                <label for="service_price">Input Harga Servis (Rp)</label>
                <input type="number" id="service_price" name="service_price" placeholder="Contoh: 150000" required>
            </div>
            <button type="submit" class="btn-kirim">Kirim Konfirmasi</button>
        </form>
    </div>
</div>
<div id="reject-modal" class="modal">
    <div class="modal-header">
        <h2>Reject Service Booking</h2>
        <button id="close-reject-modal-btn" class="close-btn">&times;</button>
    </div>
    <div class="modal-body">
        <div class="booking-details">
            <p><strong>Nama:</strong> <span id="modal-reject-name"></span></p>
            <p><strong>Email:</strong> <span id="modal-reject-email"></span></p>
            <p><strong>Tipe Motor:</strong> <span id="modal-reject-motor-type"></span></p>
        </div>
        
        <form id="reject-form" action="/EfkaWorkshop/backend/proses_reject_booking.php" method="POST">
            <input type="hidden" name="booking_id" id="modal-reject-booking-id">
            
            <div class="form-group">
                <label for="rejection_reason">Alasan Penolakan</label>
                <textarea id="rejection_reason" name="rejection_reason" rows="4" placeholder="Contoh: Jadwal pada tanggal tersebut sudah penuh." required></textarea>
            </div>
            
            <button type="submit" class="btn-kirim" style="background-color: #EF4444; color: white;">Kirim Penolakan</button>
        </form>
    </div>
</div>


<script src="script.js"></script>
<script src="../script.js"></script>