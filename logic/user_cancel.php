<?php
session_start();
require_once '../config/database.php';

// Cek Login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$type = $_GET['type'] ?? ''; // 'order' atau 'booking'
$id = $_GET['id'] ?? 0;

if ($id && $type) {

    // --- KASUS 1: BATALIN ORDER SPAREPART ---
    if ($type == 'order') {
        // Cek dulu: Bener gak ini punya user yg login? Dan statusnya masih processing?
        $check = $conn->prepare("SELECT status FROM orders WHERE id = ? AND user_id = ?");
        $check->bind_param("ii", $id, $user_id);
        $check->execute();
        $res = $check->get_result()->fetch_assoc();

        if ($res && $res['status'] == 'processing') {
            // 1. BALIKIN STOK BARANG (RESTOCK)
            // Ambil semua item di order ini
            $items = $conn->query("SELECT sparepart_id, quantity FROM order_items WHERE order_id = $id");
            while ($item = $items->fetch_assoc()) {
                $sid = $item['sparepart_id'];
                $qty = $item['quantity'];
                // Update stok: stok lama + qty yg dibatalin
                $conn->query("UPDATE spareparts SET stock = stock + $qty WHERE id = $sid");
            }

            // 2. UBAH STATUS JADI CANCELLED
            $conn->query("UPDATE orders SET status = 'cancelled' WHERE id = $id");

            $_SESSION['alert'] = ['type' => 'success', 'message' => 'Order berhasil dibatalkan. Stok barang dikembalikan.'];
        } else {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'Gagal! Order tidak ditemukan atau sudah diproses admin.'];
        }
    }

    // --- KASUS 2: BATALIN BOOKING SERVIS ---
    elseif ($type == 'booking') {
        // Cek kepemilikan & status
        $check = $conn->prepare("SELECT status FROM service_bookings WHERE id = ? AND user_id = ?");
        $check->bind_param("ii", $id, $user_id);
        $check->execute();
        $res = $check->get_result()->fetch_assoc();

        if ($res && $res['status'] == 'pending') {
            // Cukup ubah status aja
            $conn->query("UPDATE service_bookings SET status = 'cancelled' WHERE id = $id");

            $_SESSION['alert'] = ['type' => 'success', 'message' => 'Booking servis dibatalkan.'];
        } else {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'Gagal! Booking sudah disetujui admin, tidak bisa batal.'];
        }
    }
}

header("Location: " . BASE_URL . "/customer/views/riwayat.php");
exit;
