<?php
require '../../../backend/config.php';
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die("Akses ditolak.");
}

if (!isset($_GET['id'])) {
    header("Location: manage-service.php");
    exit();
}

$service_id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT service_name, description, image_url FROM services WHERE id = ?");
$stmt->bind_param("i", $service_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Layanan tidak ditemukan.");
}

$service = $result->fetch_assoc();

$pageTitle = 'Edit Layanan';
$activeMenu = 'service';
include '../template-header.php';
include '../template-sidebar.php';
?>

<link rel="stylesheet" href="../style.css"> <div class="main-content">
    <div class="form-container">
        <h1>Edit Layanan</h1>
        <form action="/EfkaWorkshop/backend/proses_edit_service.php" method="POST" enctype="multipart/form-data">
            
            <input type="hidden" name="service_id" value="<?php echo $service_id; ?>">
            <input type="hidden" name="old_image_path" value="<?php echo htmlspecialchars($service['image_url']); ?>">

            <div class="form-group">
                <label for="service_name">Nama Layanan:</label>
                <input type="text" id="service_name" name="service_name" value="<?php echo htmlspecialchars($service['service_name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="description">Deskripsi:</label>
                <textarea id="description" name="description" rows="5" required><?php echo htmlspecialchars($service['description']); ?></textarea>
            </div>
            <div class="form-group">
                <label for="service_image">Gambar Layanan:</label>
                <p>Gambar Saat Ini:</p>
                <img src="/EfkaWorkshop/<?php echo htmlspecialchars($service['image_url']); ?>" alt="Gambar Layanan" style="max-width: 200px; margin-bottom: 10px;">
                <p>Upload gambar baru untuk mengganti (biarkan kosong jika tidak ingin ganti):</p>
                <input type="file" id="service_image" name="service_image" accept="image/*">
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-tambah">Simpan Perubahan</button>
                <a href="manage-service.php" class="btn btn-batal">Batal</a>
            </div>
        </form>
    </div>
</div>

<style>
    .form-container { max-width: 700px; margin: 2rem auto; padding: 2rem; background-color: #fff; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
    .form-group { margin-bottom: 1.5rem; }
    .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; }
    .form-group input[type="text"], .form-group textarea, .form-group input[type="file"] { width: 100%; padding: 0.75rem; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; }
    .form-actions { display: flex; gap: 1rem; margin-top: 2rem; }
    .btn { padding: 0.75rem 1.5rem; border: none; border-radius: 6px; font-weight: 700; cursor: pointer; text-decoration: none; text-align: center; }
    .btn-batal { background-color: #6c757d; color: white; }
</style>