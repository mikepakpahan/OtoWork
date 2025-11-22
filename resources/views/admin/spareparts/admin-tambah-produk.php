<?php
include '../../../backend/config.php';
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die("Akses ditolak. Halaman ini hanya untuk Admin.");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Produk Baru - Admin</title>
    <style>
        body { font-family: 'Inter', sans-serif; margin: 0; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 50px auto; padding: 20px; background-color: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { text-align: center; color: #333; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group textarea { width: 97%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; }
        .form-group input[type="file"] { padding: 3px; }
        .btn { display: block; width: 100%; padding: 10px; background-color: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        .btn:hover { background-color: #218838; }
        .btn-back { display: inline-block; margin-top: 15px; color: #007bff; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Tambah Sparepart Baru</h1>
        <form action="../../../backend/proses_tambah_produk.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="part_name">Nama Produk:</label>
                <input type="text" id="part_name" name="part_name" required>
            </div>
            <div class="form-group">
                <label for="description">Deskripsi:</label>
                <textarea id="description" name="description" rows="4" required></textarea>
            </div>
            <div class="form-group">
                <label for="price">Harga:</label>
                <input type="number" id="price" name="price" required placeholder="Contoh: 250000 (tanpa titik atau koma)">
            </div>
            <div class="form-group">
                <label for="stock">Stok:</label>
                <input type="number" id="stock" name="stock" required>
            </div>
            <div class="form-group">
                <label for="product_image">Gambar Produk:</label>
                <input type="file" id="product_image" name="product_image" accept="image/png, image/jpeg, image/jpg" required>
            </div>
            <button type="submit" class="btn">Simpan Produk</button>
        </form>
        <a href="manage-sparepart.php" class="btn-back">Kembali ke Manajemen Sparepart</a>
    </div>
</body>
</html>