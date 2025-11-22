<?php
require 'backend/config.php';

// Variabel untuk menyimpan pesan yang akan ditampilkan ke user
$message = "";
$message_type = "error";

// 1. Cek apakah ada token di URL
if (isset($_GET['token']) && !empty($_GET['token'])) {
    
    $token = $_GET['token'];

    // 2. Cari booking yang cocok dengan token DAN statusnya masih 'accepted'
    $stmt = $conn->prepare("SELECT id FROM service_bookings WHERE confirmation_token = ? AND status = 'accepted'");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $booking = $result->fetch_assoc();
        $booking_id = $booking['id'];

        // 4. Update status jadi 'confirmed_by_user' dan HAPUS tokennya
        $stmt_update = $conn->prepare("UPDATE service_bookings SET status = 'confirmed_by_user', confirmation_token = NULL WHERE id = ?");
        $stmt_update->bind_param("i", $booking_id);
        
        if ($stmt_update->execute()) {
            $message = "Terima kasih! Booking servis Anda telah berhasil dikonfirmasi. Kami akan menghubungi Anda lebih lanjut jika diperlukan. Sampai jumpa di bengkel!";
            $message_type = "success";
        } else {
            $message = "Terjadi kesalahan saat mengonfirmasi booking Anda. Silakan hubungi admin.";
        }
        $stmt_update->close();

    } else {
        $message = "Link konfirmasi ini tidak valid atau sudah kedaluwarsa.";
    }
    $stmt->close();

} else {
    $message = "Halaman tidak bisa diakses secara langsung. Silakan gunakan link dari email Anda.";
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Booking - EFKA Workshop</title>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f4f7f6; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; padding: 20px; }
        .container { text-align: center; padding: 40px; background-color: #fff; border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); max-width: 500px; }
        .icon { font-size: 50px; margin-bottom: 20px; }
        .success .icon { color: #28a745; }
        .error .icon { color: #dc3545; }
        h1 { margin-bottom: 15px; }
        p { color: #555; line-height: 1.6; }
        .home-link { display: inline-block; margin-top: 25px; padding: 10px 20px; background-color: #FFC72C; color: #1F2937; text-decoration: none; font-weight: bold; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container <?php echo $message_type; ?>">
        <div class="icon">
            <?php echo ($message_type === 'success') ? '&#10004;' : '&#10006;'; ?>
        </div>
        <h1><?php echo ($message_type === 'success') ? 'Konfirmasi Berhasil!' : 'Terjadi Kesalahan'; ?></h1>
        <p><?php echo htmlspecialchars($message); ?></p>
        <a href="index.php" class="home-link">Kembali ke Halaman Utama</a>
    </div>
</body>
</html>