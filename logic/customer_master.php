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
    // --- FITUR 1: TOGGLE STATUS (BANNED/UNBANNED) ---
    if ($act == 'toggle') {
        // Cek status sekarang apa
        $sql = "SELECT account_status FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            // Kalau Active jadi Inactive, dan sebaliknya
            $newStatus = ($row['account_status'] == 'active') ? 'inactive' : 'active';

            $update = $conn->prepare("UPDATE users SET account_status = ? WHERE id = ?");
            $update->bind_param("si", $newStatus, $id);

            if ($update->execute()) {
                $msg = ($newStatus == 'active') ? "User diaktifkan kembali." : "User berhasil dibekukan (Banned).";
                $_SESSION['alert'] = ['type' => 'success', 'message' => $msg];
            }
        }
    }

    // --- FITUR 2: HAPUS PERMANEN (Ati-ati datanya ilang) ---
    elseif ($act == 'delete') {
        // Hapus user
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            $_SESSION['alert'] = ['type' => 'success', 'message' => 'Data user dihapus selamanya.'];
        } else {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'Gagal menghapus user.'];
        }
    }
}

header("Location: " . BASE_URL . "/admin/views/customers.php");
exit;
