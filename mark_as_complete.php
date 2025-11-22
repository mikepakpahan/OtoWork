<?php
require 'backend/config.php';

$message = "Token tidak valid atau pesanan sudah pernah diselesaikan.";

if (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = $_GET['token'];

    // Kita pakai transaction lagi untuk keamanan
    $conn->begin_transaction();
    try {
        // Ambil data order berdasarkan token
        $stmt_select = $conn->prepare("SELECT id, user_id, total_amount FROM orders WHERE completion_token = ? AND status = 'processing'");
        $stmt_select->bind_param("s", $token);
        $stmt_select->execute();
        $result = $stmt_select->get_result();

        if ($result->num_rows === 1) {
            $order = $result->fetch_assoc();
            $order_id = $order['id'];
            
            // Ambil detail itemnya untuk dimasukkan ke history
            $stmt_items = $conn->prepare("SELECT part_name, quantity, price_at_purchase FROM order_items oi JOIN spareparts s ON oi.sparepart_id = s.id WHERE oi.order_id = ?");
            $stmt_items->bind_param("i", $order_id);
            $stmt_items->execute();
            $items_result = $stmt_items->get_result();
            $items = $items_result->fetch_all(MYSQLI_ASSOC);

            // Buat deskripsi untuk tabel history
            $description_parts = [];
            foreach ($items as $item) {
                $description_parts[] = $item['part_name'] . ' (x' . $item['quantity'] . ')';
            }
            $description = implode(', ', $description_parts);

            // Masukkan ke tabel history
            $stmt_history = $conn->prepare("INSERT INTO history (user_id, transaction_type, description, final_price) VALUES (?, 'sparepart', ?, ?)");
            $stmt_history->bind_param("isi", $order['user_id'], $description, $order['total_amount']);
            $stmt_history->execute();

            // Ubah status order menjadi 'completed' dan hapus tokennya
            $stmt_update = $conn->prepare("UPDATE orders SET status = 'completed', completion_token = NULL WHERE id = ?");
            $stmt_update->bind_param("i", $order_id);
            $stmt_update->execute();
            
            $conn->commit();
            $message = "SUCCESS! Pesanan dengan ID EFKA-" . str_pad($order_id, 5, '0', STR_PAD_LEFT) . " telah berhasil diselesaikan.";
        }
    } catch (Exception $e) {
        $conn->rollback();
        $message = "Terjadi error: " . $e->getMessage();
    }
}
// Tampilkan pesan sederhana untuk petugas/admin yang melakukan scan
echo "<div style='font-family: sans-serif; text-align: center; padding: 50px; font-size: 24px;'>" . htmlspecialchars($message) . "</div>";
?>