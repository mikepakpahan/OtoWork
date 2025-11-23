<?php
session_start();
require_once '../config/database.php';

// 1. CEK LOGIN (Berlaku buat Admin & Customer)
// Siapapun yang masuk sini harus udah login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

// Ambil Action (Bisa dari GET link admin, atau POST form customer)
$act = $_REQUEST['act'] ?? '';

// ==================================================
// 🟢 ZONA CUSTOMER (BUAT BOOKING BARU)
// ==================================================
if ($act == 'create') {
    // Ambil data dari Form
    $user_id        = $_SESSION['user_id'];
    $phone_number   = htmlspecialchars($_POST['phone_number']);
    $motor_type     = htmlspecialchars($_POST['motor_type']);
    $license_plate  = htmlspecialchars($_POST['license_plate']); // Input Baru
    $booking_date   = htmlspecialchars($_POST['booking_date']);
    $booking_time   = htmlspecialchars($_POST['booking_time']);  // Input Baru
    $complaint      = htmlspecialchars($_POST['complaint']);

    // A. Update No HP User (Opsional tapi bagus)
    // Biar database user kita makin lengkap
    if (!empty($phone_number)) {
        $stmtUser = $conn->prepare("UPDATE users SET phone_number = ? WHERE id = ?");
        $stmtUser->bind_param("si", $phone_number, $user_id);
        $stmtUser->execute();
        // Update session juga biar gak perlu logout-login
        $_SESSION['phone_number'] = $phone_number;
    }

    // B. Insert Booking ke Database
    $stmt = $conn->prepare("INSERT INTO service_bookings (user_id, motor_type, license_plate, booking_date, booking_time, complaint, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");

    // Perhatikan urutan tipe datanya: i (int), s (string) x 5
    $stmt->bind_param("isssss", $user_id, $motor_type, $license_plate, $booking_date, $booking_time, $complaint);

    if ($stmt->execute()) {
        $_SESSION['alert'] = ['type' => 'success', 'message' => 'Booking berhasil dikirim! Tunggu konfirmasi admin ya.'];
        // Lempar ke Riwayat biar customer bisa liat statusnya
        header("Location: " . BASE_URL . "/customer/views/riwayat.php");
    } else {
        $_SESSION['alert'] = ['type' => 'error', 'message' => 'Gagal booking: ' . $conn->error];
        header("Location: " . BASE_URL . "/customer/views/home.php#booking-section");
    }
    exit;
}

// ==================================================
// 🔴 ZONA ADMIN (APPROVE / REJECT / COMPLETE)
// ==================================================
// Cek lagi: Beneran Admin gak nih? Jangan sampe customer iseng ngetik URL approve.
if ($_SESSION['role'] !== 'admin') {
    $_SESSION['alert'] = ['type' => 'error', 'message' => 'Anda tidak punya akses ke fitur ini!'];
    header("Location: ../index.php");
    exit;
}

// Ambil ID dari GET (Link tombol admin)
$id = $_GET['id'] ?? 0;

if ($id) {
    $msg = "";
    $type = "success";

    if ($act == 'approve') {
        // --- TERIMA BOOKING ---
        $stmt = $conn->prepare("UPDATE service_bookings SET status = 'approved' WHERE id = ?");
        $stmt->bind_param("i", $id);
        $msg = "Booking disetujui! Masuk ke antrian bengkel.";
    } elseif ($act == 'reject') {
        // --- TOLAK BOOKING ---
        $reason = $_GET['reason'] ?? 'Maaf, jadwal penuh.';
        $stmt = $conn->prepare("UPDATE service_bookings SET status = 'cancelled', rejection_reason = ? WHERE id = ?");
        $stmt->bind_param("si", $reason, $id);
        $msg = "Booking ditolak dengan alasan: " . htmlspecialchars($reason);
        $type = "warning"; // Ganti warna alert jadi kuning/orange

    } elseif ($act == 'complete') {
        // --- SELESAI SERVIS ---
        $price = $_GET['price'] ?? 0;
        $stmt = $conn->prepare("UPDATE service_bookings SET status = 'completed', price = ? WHERE id = ?");
        $stmt->bind_param("ii", $price, $id);
        $msg = "Servis selesai! Total tagihan: Rp " . number_format($price, 0, ',', '.');
    }

    // Eksekusi Query Admin
    if (isset($stmt) && $stmt->execute()) {
        $_SESSION['alert'] = ['type' => $type, 'message' => $msg];
    } else {
        $_SESSION['alert'] = ['type' => 'error', 'message' => 'Gagal update status.'];
    }
}

// Balik ke Dashboard Admin
header("Location: " . BASE_URL . "/admin/views/bookings.php");
exit;
