<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$act = $_POST['act'] ?? '';

if ($act == 'process_order') {
    // Ambil tipe pengiriman
    $delivery_type = $_POST['delivery_type']; // 'delivery' atau 'pickup'
    $payment_method = $_POST['payment_method'];
    $cart_ids = explode(',', $_POST['cart_ids_str']);

    // LOGIC ALAMAT/TANGGAL
    $final_address = '';

    if ($delivery_type == 'delivery') {
        $final_address = $_POST['address'];
        if (empty($final_address)) {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'Alamat wajib diisi!'];
            header("Location: " . BASE_URL . "/customer/views/cart.php");
            exit;
        }
    } else {
        // Kalo Pickup, kita simpan format khusus
        $pickup_date = $_POST['pickup_date'];
        if (empty($pickup_date)) {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'Tanggal kedatangan wajib diisi!'];
            header("Location: " . BASE_URL . "/customer/views/cart.php");
            exit;
        }
        $final_address = "AMBIL DI BENGKEL (Booking Tgl: $pickup_date)";
    }

    // --- 1. HANDLE UPLOAD BUKTI BAYAR ---
    $payment_proof = null;
    if ($payment_method == 'transfer') {
        if (!empty($_FILES['payment_proof']['name'])) {
            $targetDir = "../assets/img/proofs/";
            if (!file_exists($targetDir)) mkdir($targetDir, 0777, true);

            $fileName = time() . '_' . basename($_FILES['payment_proof']['name']);
            if (move_uploaded_file($_FILES['payment_proof']['tmp_name'], $targetDir . $fileName)) {
                $payment_proof = $fileName;
            }
        } else {
            // Kalo transfer tapi ga upload bukti
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'Wajib upload bukti transfer bro!'];
            header("Location: " . BASE_URL . "/customer/views/cart.php");
            exit;
        }
    }

    // --- 2. HITUNG TOTAL & CEK STOK LAGI (Secure Check) ---
    $total_amount = 0;
    $items_to_process = [];

    // Kita pake Transaction biar aman (Kalau error, batalin semua)
    $conn->begin_transaction();

    try {
        // Loop Cart IDs
        foreach ($cart_ids as $cid) {
            $cid = intval($cid);
            $sql = "SELECT c.quantity, s.id as sparepart_id, s.price, s.stock 
                    FROM carts c JOIN spareparts s ON c.sparepart_id = s.id 
                    WHERE c.id = $cid AND c.user_id = $user_id";
            $res = $conn->query($sql);

            if ($row = $res->fetch_assoc()) {
                if ($row['quantity'] > $row['stock']) {
                    throw new Exception("Stok barang berubah mendadak! Cek keranjang lagi.");
                }
                $total_amount += ($row['price'] * $row['quantity']);
                $items_to_process[] = $row;
            }
        }

        // --- 3. INSERT ORDER HEADER ---
        $stmtOrder = $conn->prepare("INSERT INTO orders (user_id, total_amount, order_date, status, address, payment_method, payment_proof) VALUES (?, ?, NOW(), 'processing', ?, ?, ?)");
        $stmtOrder->bind_param("iisss", $user_id, $total_amount, $final_address, $payment_method, $payment_proof);
        $stmtOrder->execute();
        $order_id = $conn->insert_id;

        // --- 4. INSERT ORDER ITEMS & KURANGI STOK ---
        $stmtItem = $conn->prepare("INSERT INTO order_items (order_id, sparepart_id, quantity, price_at_purchase) VALUES (?, ?, ?, ?)");
        $stmtStock = $conn->prepare("UPDATE spareparts SET stock = stock - ? WHERE id = ?");
        $stmtDelCart = $conn->prepare("DELETE FROM carts WHERE id = ?"); // Hapus dari keranjang pake ID Cart asli

        // Loop item lagi (Kali ini kita perlu ID Cart asli juga, jadi logic di atas agak disesuaikan dikit)
        // Biar simpel, kita delete berdasarkan sparepart_id & user_id aja di keranjang nanti

        foreach ($items_to_process as $item) {
            // Insert Item
            $stmtItem->bind_param("iiid", $order_id, $item['sparepart_id'], $item['quantity'], $item['price']);
            $stmtItem->execute();

            // Kurangi Stok
            $stmtStock->bind_param("ii", $item['quantity'], $item['sparepart_id']);
            $stmtStock->execute();
        }

        // --- 5. BERSIHKAN KERANJANG ---
        // Hapus item yang UDAH dibeli aja
        $cart_ids_str = implode(',', $cart_ids);
        $conn->query("DELETE FROM carts WHERE id IN ($cart_ids_str)");

        // Commit Transaksi (Simpan Permanen)
        $conn->commit();

        $_SESSION['alert'] = ['type' => 'success', 'message' => 'Order berhasil! Tunggu konfirmasi admin ya.'];
        header("Location: " . BASE_URL . "/customer/views/riwayat.php");
    } catch (Exception $e) {
        $conn->rollback(); // Batalin semua perubahan
        $_SESSION['alert'] = ['type' => 'error', 'message' => 'Gagal checkout: ' . $e->getMessage()];
        header("Location: " . BASE_URL . "/customer/views/cart.php");
    }
}
