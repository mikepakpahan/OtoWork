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

if ($act == 'accept' && $id) {
    // Ubah status jadi completed
    // (Opsional: Disini bisa tambah logic kurangin stok kalau belum dikurangin pas checkout)
    $stmt = $conn->prepare("UPDATE orders SET status = 'completed' WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $_SESSION['alert'] = ['type' => 'success', 'message' => 'Pesanan berhasil diterima & diproses!'];
    } else {
        $_SESSION['alert'] = ['type' => 'error', 'message' => 'Gagal memproses pesanan.'];
    }
} elseif ($act == 'reject' && $id) {
    // Ubah status jadi cancelled (Jangan dihapus row-nya, sayang datanya)
    $stmt = $conn->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $_SESSION['alert'] = ['type' => 'warning', 'message' => 'Pesanan dibatalkan.'];
    } else {
        $_SESSION['alert'] = ['type' => 'error', 'message' => 'Gagal membatalkan pesanan.'];
    }
}

// Balik ke halaman orders
header("Location: " . BASE_URL . "/admin/views/orders.php");
exit;
