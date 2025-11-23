<?php
session_start();
require_once '../config/database.php';

// Cek Admin (Wajib!)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

// Ambil Action
$act = $_POST['act'] ?? $_GET['act'] ?? '';

// --- FUNGSI UPLOAD GAMBAR (Reusable) ---
function uploadImage($file)
{
    $targetDir = "../assets/img/spareparts/";
    // Pastikan folder ada
    if (!file_exists($targetDir)) mkdir($targetDir, 0777, true);

    $fileName = time() . '_' . basename($file["name"]); // Rename biar unik
    $targetFilePath = $targetDir . $fileName;
    $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

    // Validasi Ekstensi
    $allowTypes = array('jpg', 'png', 'jpeg', 'webp');
    if (in_array(strtolower($fileType), $allowTypes)) {
        if (move_uploaded_file($file["tmp_name"], $targetFilePath)) {
            return $fileName;
        }
    }
    return false;
}

// --- 1. TAMBAH DATA (CREATE) ---
if ($act == 'add') {
    $name = $_POST['part_name'];
    $desc = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_active = 1; // Default aktif pas dibuat

    // Handle Upload
    $image_url = 'default.png'; // Gambar default kalau gak upload
    if (!empty($_FILES["image"]["name"])) {
        $upload = uploadImage($_FILES["image"]);
        if ($upload) $image_url = $upload;
    }

    $stmt = $conn->prepare("INSERT INTO spareparts (part_name, description, price, stock, image_url, is_featured, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssiisii", $name, $desc, $price, $stock, $image_url, $is_featured, $is_active);

    if ($stmt->execute()) {
        $_SESSION['alert'] = ['type' => 'success', 'message' => 'Barang berhasil ditambahkan!'];
    } else {
        $_SESSION['alert'] = ['type' => 'error', 'message' => 'Gagal nambah barang: ' . $conn->error];
    }
    header("Location: " . BASE_URL . "/admin/views/spareparts.php");
}

// --- 2. UPDATE DATA ---
elseif ($act == 'update') {
    $id = $_POST['id'];
    $name = $_POST['part_name'];
    $desc = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;

    // Logic Gambar: Kalau ada upload baru, ganti. Kalau enggak, pake lama.
    if (!empty($_FILES["image"]["name"])) {
        $image_url = uploadImage($_FILES["image"]);
        $sql = "UPDATE spareparts SET part_name=?, description=?, price=?, stock=?, is_featured=?, image_url=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssiisii", $name, $desc, $price, $stock, $is_featured, $image_url, $id);
    } else {
        $sql = "UPDATE spareparts SET part_name=?, description=?, price=?, stock=?, is_featured=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssiiii", $name, $desc, $price, $stock, $is_featured, $id);
    }

    if ($stmt->execute()) {
        $_SESSION['alert'] = ['type' => 'success', 'message' => 'Data barang diupdate!'];
    } else {
        $_SESSION['alert'] = ['type' => 'error', 'message' => 'Gagal update: ' . $conn->error];
    }
    header("Location: " . BASE_URL . "/admin/views/spareparts.php");
}

// --- 3. DELETE DATA ---
elseif ($act == 'delete') {
    $id = $_GET['id'];

    // Opsional: Hapus file gambarnya juga dari folder biar gak nyampah
    // $oldImg = $conn->query("SELECT image_url FROM spareparts WHERE id=$id")->fetch_object()->image_url;
    // if($oldImg != 'default.png') unlink("../assets/img/spareparts/".$oldImg);

    $stmt = $conn->prepare("DELETE FROM spareparts WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $_SESSION['alert'] = ['type' => 'success', 'message' => 'Barang dihapus selamanya.'];
    } else {
        $_SESSION['alert'] = ['type' => 'error', 'message' => 'Gagal hapus.'];
    }
    header("Location: " . BASE_URL . "/admin/views/spareparts.php");
}

// --- 4. TOGGLE ACTIVE/NON-ACTIVE ---
elseif ($act == 'toggle') {
    $id = $_GET['id'];
    // Balik statusnya (Kalau 1 jadi 0, 0 jadi 1)
    $conn->query("UPDATE spareparts SET is_active = NOT is_active WHERE id = $id");
    header("Location: " . BASE_URL . "/admin/views/spareparts.php");
}
