<?php
session_start();
require_once '../config/database.php';

// Header JSON
header('Content-Type: application/json');

// Cek Login
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Sesi habis, login lagi ya!']);
    exit;
}

$act = $_POST['act'] ?? '';
$cart_id = $_POST['cart_id'] ?? 0;

// --- 1. UPDATE QUANTITY ---
if ($act == 'update_qty') {
    $qty = intval($_POST['quantity']);

    if ($qty < 1) {
        echo json_encode(['status' => 'error', 'message' => 'Minimal beli 1 dong.']);
        exit;
    }

    // Cek Stok Dulu (Security Check)
    // Kita perlu join ke spareparts buat tau stok aslinya
    $sqlCheck = "SELECT s.stock, c.sparepart_id 
                 FROM carts c 
                 JOIN spareparts s ON c.sparepart_id = s.id 
                 WHERE c.id = ?";
    $stmtCheck = $conn->prepare($sqlCheck);
    $stmtCheck->bind_param("i", $cart_id);
    $stmtCheck->execute();
    $resCheck = $stmtCheck->get_result()->fetch_assoc();

    if ($qty > $resCheck['stock']) {
        echo json_encode(['status' => 'error', 'message' => 'Stok cuma sisa ' . $resCheck['stock'] . ' pcs!']);
        exit;
    }

    // Eksekusi Update
    $stmt = $conn->prepare("UPDATE carts SET quantity = ? WHERE id = ?");
    $stmt->bind_param("ii", $qty, $cart_id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Qty update']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal update db']);
    }
}

// --- 2. HAPUS ITEM ---
elseif ($act == 'delete') {
    $stmt = $conn->prepare("DELETE FROM carts WHERE id = ?");
    $stmt->bind_param("i", $cart_id);

    if ($stmt->execute()) {
        // Hitung ulang jumlah item di keranjang buat update badge navbar
        $uid = $_SESSION['user_id'];
        $count = $conn->query("SELECT COUNT(*) as total FROM carts WHERE user_id = $uid")->fetch_assoc()['total'];
        echo json_encode(['status' => 'success', 'message' => 'Item dihapus', 'cart_count' => $count]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal hapus item']);
    }
}
