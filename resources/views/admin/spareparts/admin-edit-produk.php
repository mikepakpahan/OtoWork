<?php
include '../../../backend/config.php';
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die("Akses ditolak.");
}

if (!isset($_GET['id'])) {
    header("Location: manage-sparepart.php");
    exit();
}

$product_id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT part_name, description, price, stock, image_url FROM spareparts WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Produk tidak ditemukan.";
    exit();
}

$product = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Produk - Admin</title>
    <link rel="stylesheet" href="path/to/your/admin_form_style.css"> <style> /* Atau gunakan style inline ini lagi */
        body { font-family: 'Inter', sans-serif; margin: 0; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 50px auto; padding: 20px; background-color: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { text-align: center; color: #333; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group textarea { width: 97%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; }
        .form-group img { max-width: 150px; margin-top: 10px; display: block; }
        .btn { display: block; width: 100%; padding: 10px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        .btn:hover { background-color: #0056b3; }
        .btn-back { display: inline-block; margin-top: 15px; color: #007bff; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Edit Sparepart</h1>
        <form action="/EfkaWorkshop/backend/proses-edit-produk.php" method="POST" enctype="multipart/form-data">
            
            <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
            <input type="hidden" name="old_image_path" value="<?php echo htmlspecialchars($product['image_url']); ?>">

            <div class="form-group">
                <label for="part_name">Nama Produk:</label>
                <input type="text" id="part_name" name="part_name" value="<?php echo htmlspecialchars($product['part_name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="description">Deskripsi:</label>
                <textarea id="description" name="description" rows="4" required><?php echo htmlspecialchars($product['description']); ?></textarea>
            </div>
            <div class="form-group">
                <label for="price">Harga:</label>
                <input type="number" id="price" name="price" value="<?php echo $product['price']; ?>" required>
            </div>
            <div class="form-group">
                <label for="stock">Stok:</label>
                <input type="number" id="stock" name="stock" value="<?php echo $product['stock']; ?>" required>
            </div>
            <div class="form-group">
                <label for="product_image">Gambar Produk:</label>
                <p>Gambar Saat Ini:</p>
                <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="Gambar Produk">
                <p style="margin-top:10px;">Upload gambar baru untuk mengganti (biarkan kosong jika tidak ingin ganti):</p>
                <input type="file" id="product_image" name="product_image" accept="image/png, image/jpeg, image/jpg">
            </div>
            <button type="submit" class="btn">Simpan Perubahan</button>
        </form>
        <a href="manage-sparepart.php" class="btn-back">Batal dan Kembali</a>
    </div>
</body>
</html>