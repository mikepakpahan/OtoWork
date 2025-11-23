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
    if ($act == 'mark_read') {
        // Update status jadi 'read'
        $stmt = $conn->prepare("UPDATE feedback SET status = 'read' WHERE id = ?");
        $stmt->bind_param("i", $id);
        $msg = "Pesan ditandai sudah dibaca.";
        $type = "success";
    } elseif ($act == 'delete') {
        // Hapus pesan selamanya
        $stmt = $conn->prepare("DELETE FROM feedback WHERE id = ?");
        $stmt->bind_param("i", $id);
        $msg = "Pesan berhasil dihapus.";
        $type = "warning";
    }

    if (isset($stmt) && $stmt->execute()) {
        $_SESSION['alert'] = ['type' => $type, 'message' => $msg];
    } else {
        $_SESSION['alert'] = ['type' => 'error', 'message' => 'Gagal memproses data.'];
    }
}

header("Location: " . BASE_URL . "/admin/views/feedback.php");
exit;
