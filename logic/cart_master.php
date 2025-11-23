<?php
session_start();
require_once '../config/database.php';

// Set Header JSON (Penting buat AJAX)
header('Content-Type: application/json');

// Cek Login
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Login dulu bosku!']);
    exit;
}

$user_id = $_SESSION['user_id'];
$product_id = $_POST['product_id'] ?? 0;

if (!$product_id) {
    echo json_encode(['status' => 'error', 'message' => 'Produk ga jelas.']);
    exit;
}

// 1. Cek stok dulu, masih ada ga?
$stok_cek = $conn->query("SELECT stock FROM spareparts WHERE id = $product_id");
$data_stok = $stok_cek->fetch_assoc();

if (!$data_stok || $data_stok['stock'] <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Yah, stok habis!']);
    exit;
}

// 2. Cek apakah barang udah ada di keranjang user ini?
$checkCart = $conn->prepare("SELECT id, quantity FROM carts WHERE user_id = ? AND sparepart_id = ?");
$checkCart->bind_param("ii", $user_id, $product_id);
$checkCart->execute();
$resultCart = $checkCart->get_result();

if ($resultCart->num_rows > 0) {
    // Kalo udah ada, Update Quantity (+1)
    $row = $resultCart->fetch_assoc();
    $newQty = $row['quantity'] + 1;

    // Cek lagi cukup ga stoknya kalo nambah
    if ($newQty > $data_stok['stock']) {
        echo json_encode(['status' => 'error', 'message' => 'Stok ga cukup buat nambah lagi.']);
        exit;
    }

    $update = $conn->prepare("UPDATE carts SET quantity = ? WHERE id = ?");
    $update->bind_param("ii", $newQty, $row['id']);
    $update->execute();
} else {
    // Kalo belum ada, Insert Baru
    $insert = $conn->prepare("INSERT INTO carts (user_id, sparepart_id, quantity) VALUES (?, ?, 1)");
    $insert->bind_param("ii", $user_id, $product_id);
    $insert->execute();
}

// 3. Hitung Total Keranjang Baru buat Update Badge Icon
$countQuery = $conn->query("SELECT COUNT(*) as total FROM carts WHERE user_id = $user_id");
$count = $countQuery->fetch_assoc()['total'];

echo json_encode(['status' => 'success', 'message' => 'Masuk keranjang!', 'cart_count' => $count]);
