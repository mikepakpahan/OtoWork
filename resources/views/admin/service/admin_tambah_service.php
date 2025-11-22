<?php
require '../../../backend/config.php';
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die("Akses ditolak. Halaman ini hanya untuk Admin.");
}
$pageTitle = 'Tambah Layanan';
$activeMenu = 'service';
include '../template-header.php';
include '../template-sidebar.php';
?>

<link rel="stylesheet" href="../style.css"> <div class="main-content">
    <div class="form-container">
        <h1>Tambah Layanan Baru</h1>
        <form action="/EfkaWorkshop/backend/proses_tambah_service.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="service_name">Nama Layanan:</label>
                <input type="text" id="service_name" name="service_name" required>
            </div>
            <div class="form-group">
                <label for="description">Deskripsi:</label>
                <textarea id="description" name="description" rows="5" required></textarea>
            </div>
            <div class="form-group">
                <label for="service_image">Gambar Layanan:</label>
                <input type="file" id="service_image" name="service_image" accept="image/*" required>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-tambah">Simpan Layanan</button>
                <a href="manage-service.php" class="btn btn-batal">Batal</a>
            </div>
        </form>
    </div>
</div>

<style>
    .form-container {
        max-width: 700px;
        margin: 2rem auto;
        padding: 2rem;
        background-color: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    .form-group {
        margin-bottom: 1.5rem;
    }
    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
    }
    .form-group input[type="text"], .form-group textarea, .form-group input[type="file"] {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #ccc;
        border-radius: 6px;
        box-sizing: border-box;
    }
    .form-actions {
        display: flex;
        gap: 1rem;
        margin-top: 2rem;
    }
    .btn {
        padding: 0.75rem 1.5rem;
        border: none;
        border-radius: 6px;
        font-weight: 700;
        cursor: pointer;
    }
    .btn-batal {
        background-color: #6c757d;
        color: white;
        text-decoration: none;
        text-align: center;
    }
</style>