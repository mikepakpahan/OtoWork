<?php
// 1. Set Timezone biar waktu transaksi akurat (WIB)
date_default_timezone_set('Asia/Jakarta');

// 2. Session Start yang 'Aman'
// Kita cek dulu, kalo session belum mulai, baru kita start. 
// Ini biar gak error "Session already started" kalau file ini terpanggil 2x.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 3. Definisi Konstanta URL (PENTING BANGET!)
// Karena kita pisah folder admin dan customer, kita butuh penunjuk jalan yang pasti.
// Ganti 'http://localhost/bengkel-app' sesuai nama folder lo di htdocs.
define('BASE_URL', 'http://localhost/OtoWork');
define('APP_NAME', 'OtoWork');

// 4. Konfigurasi Database
$host = 'localhost';
$user = 'root';
$pass = '';          // Default XAMPP biasanya kosong
$db   = 'otowork_db'; // Nama database baru kita

// 5. Buat Koneksi
$conn = new mysqli($host, $user, $pass, $db);

// 6. Cek Koneksi
if ($conn->connect_error) {
    // Pake style error yang agak sopan dikit hehe
    die("
        <div style='text-align:center; margin-top:50px; font-family:sans-serif;'>
            <h3>🚫 Oops, Gagal Konek ke Database!</h3>
            <p>Pesan Error: " . $conn->connect_error . "</p>
            <p>Coba cek nyalain XAMPP atau nama databasenya udah bener 'db_otowork' belum?</p>
        </div>
    ");
}

// Opsional: Biar support karakter aneh-aneh (emoji dll)
$conn->set_charset("utf8");
