<?php
session_start();
require_once '../config/database.php';

// Cek Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

$act = $_GET['act'] ?? '';
$id  = $_GET['id'] ?? 0;

if ($id) {
    if ($act == 'approve') {
        // --- TERIMA BOOKING ---
        // Status jadi 'approved' (masuk antrian/queue)
        $stmt = $conn->prepare("UPDATE service_bookings SET status = 'approved' WHERE id = ?");
        $stmt->bind_param("i", $id);
        $msg = "Booking disetujui! Masuk ke antrian bengkel.";
    } elseif ($act == 'reject') {
        // --- TOLAK BOOKING + ALASAN ---
        // Ambil alasan dari parameter URL (dikirim via JS nanti)
        $reason = $_GET['reason'] ?? 'Maaf, jadwal penuh.';

        $stmt = $conn->prepare("UPDATE service_bookings SET status = 'cancelled', rejection_reason = ? WHERE id = ?");
        $stmt->bind_param("si", $reason, $id);
        $msg = "Booking ditolak dengan alasan: " . htmlspecialchars($reason);
    } elseif ($act == 'complete') {
        // --- SELESAI SERVIS + HARGA FINAL ---
        // Ambil harga dari parameter URL
        $price = $_GET['price'] ?? 0;

        // Update status jadi 'completed' DAN simpan harga
        $stmt = $conn->prepare("UPDATE service_bookings SET status = 'completed', price = ? WHERE id = ?");
        $stmt->bind_param("ii", $price, $id);
        $msg = "Servis selesai! Total tagihan: Rp " . number_format($price, 0, ',', '.');
    }

    if (isset($stmt) && $stmt->execute()) {
        $_SESSION['alert'] = ['type' => 'success', 'message' => $msg];
    } else {
        $_SESSION['alert'] = ['type' => 'error', 'message' => 'Gagal update status.'];
    }
}

header("Location: " . BASE_URL . "/admin/views/bookings.php");
exit;
