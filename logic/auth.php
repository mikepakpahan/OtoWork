<?php
require_once '../config/database.php';

// Ambil action dari hidden input
$act = isset($_POST['act']) ? $_POST['act'] : '';

// --- LOGIC REGISTER ---
if ($act == 'register') {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $password = $_POST['password'];

    // 1. Cek Email udah ada belum?
    $check = $conn->query("SELECT id FROM users WHERE email = '$email'");
    if ($check->num_rows > 0) {
        $_SESSION['alert'] = ['type' => 'error', 'message' => 'Email udah kepake bro!'];
        header("Location: " . BASE_URL . "/customer/views/login.php");
        exit;
    }

    // 2. Enkripsi Password (WAJIB!)
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // 3. Insert ke DB (Role default: customer)
    // Asumsi tabel: users (id, name, email, password, role)
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'customer')");
    $stmt->bind_param("sss", $name, $email, $hashed_password);

    if ($stmt->execute()) {
        $_SESSION['alert'] = ['type' => 'success', 'message' => 'Akun jadi! Silahkan login.'];
    } else {
        $_SESSION['alert'] = ['type' => 'error', 'message' => 'Gagal daftar: ' . $conn->error];
    }
    header("Location: " . BASE_URL . "/customer/views/login.php");
}

// --- LOGIC LOGIN ---
elseif ($act == 'login') {
    $email = htmlspecialchars($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verifikasi Password Hash
        if (password_verify($password, $user['password'])) {
            // Set Session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];

            // Redirect sesuai Kasta (Role)
            if ($user['role'] == 'admin') {
                header("Location: " . BASE_URL . "/admin/views/dashboard.php");
            } else {
                header("Location: " . BASE_URL . "/customer/views/home.php");
            }
            exit;
        }
    }

    // Kalo salah password/email
    $_SESSION['alert'] = ['type' => 'error', 'message' => 'Email atau Password salah, coba inget-inget lagi!'];
    header("Location: " . BASE_URL . "/customer/views/login.php");
}

// --- LOGIC LOGOUT ---
if (isset($_GET['act']) && $_GET['act'] == 'logout') {
    session_destroy();
    header("Location: " . BASE_URL);
    exit;
}
