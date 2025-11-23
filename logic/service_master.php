<?php
session_start();
require_once '../config/database.php';

// Cek Admin (Satpam)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

$act = $_POST['act'] ?? $_GET['act'] ?? '';

// --- FUNGSI UPLOAD GAMBAR (Reusable) ---
// Kita pisah folder gambarnya di 'assets/img/services/' biar rapi
function uploadServiceImage($file)
{
    $targetDir = "../assets/img/services/";
    if (!file_exists($targetDir)) mkdir($targetDir, 0777, true);

    $fileName = time() . '_' . basename($file["name"]);
    $targetFilePath = $targetDir . $fileName;
    $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
    $allowTypes = array('jpg', 'png', 'jpeg', 'webp');

    if (in_array(strtolower($fileType), $allowTypes)) {
        if (move_uploaded_file($file["tmp_name"], $targetFilePath)) {
            return $fileName;
        }
    }
    return false;
}

// --- 1. TAMBAH SERVIS ---
if ($act == 'add') {
    $name = $_POST['service_name'];
    $desc = $_POST['description'];
    $price = $_POST['price'];

    // Default image kalau admin males upload
    $image_url = 'default_service.jpg';
    if (!empty($_FILES["image"]["name"])) {
        $upload = uploadServiceImage($_FILES["image"]);
        if ($upload) $image_url = $upload;
    }

    $stmt = $conn->prepare("INSERT INTO services (service_name, description, price, image_url) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssis", $name, $desc, $price, $image_url);

    if ($stmt->execute()) {
        $_SESSION['alert'] = ['type' => 'success', 'message' => 'Layanan baru berhasil ditambahkan!'];
    } else {
        $_SESSION['alert'] = ['type' => 'error', 'message' => 'Gagal: ' . $conn->error];
    }
    header("Location: " . BASE_URL . "/admin/views/services.php");
}

// --- 2. UPDATE SERVIS ---
elseif ($act == 'update') {
    $id = $_POST['id'];
    $name = $_POST['service_name'];
    $desc = $_POST['description'];
    $price = $_POST['price'];

    if (!empty($_FILES["image"]["name"])) {
        $image_url = uploadServiceImage($_FILES["image"]);
        $stmt = $conn->prepare("UPDATE services SET service_name=?, description=?, price=?, image_url=? WHERE id=?");
        $stmt->bind_param("ssisi", $name, $desc, $price, $image_url, $id);
    } else {
        $stmt = $conn->prepare("UPDATE services SET service_name=?, description=?, price=? WHERE id=?");
        $stmt->bind_param("ssii", $name, $desc, $price, $id);
    }

    if ($stmt->execute()) {
        $_SESSION['alert'] = ['type' => 'success', 'message' => 'Data layanan diupdate!'];
    } else {
        $_SESSION['alert'] = ['type' => 'error', 'message' => 'Gagal update: ' . $conn->error];
    }
    header("Location: " . BASE_URL . "/admin/views/services.php");
}

// --- 3. HAPUS SERVIS ---
elseif ($act == 'delete') {
    $id = $_GET['id'];
    $stmt = $conn->prepare("DELETE FROM services WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $_SESSION['alert'] = ['type' => 'success', 'message' => 'Layanan dihapus.'];
    } else {
        $_SESSION['alert'] = ['type' => 'error', 'message' => 'Gagal hapus.'];
    }
    header("Location: " . BASE_URL . "/admin/views/services.php");
}
