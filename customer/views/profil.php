<?php
// Start Session
session_start();

require_once '../../config/database.php';

// Cek Login
if (!isset($_SESSION['user_id'])) {
    $_SESSION['alert'] = ['type' => 'warning', 'message' => 'Please login to view your profile!'];
    header("Location: " . BASE_URL . "/customer/views/login.php");
    exit;
}

$activePage = 'profile';

$user_id = $_SESSION['user_id'];

// --- LOGIC: AMBIL DATA DARI DATABASE (DISESUAIKAN DENGAN TABEL USERS) ---
// ASUMSI: $conn adalah objek koneksi database yang didefinisikan di config/database.php

$sql = "SELECT id, name, email, phone_number FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$current_user = [
    'id' => $user_id,
    'name' => 'N/A',
    'email' => 'N/A',
    'phone_number' => '', // Default kosong, karena di tabel 'phone_number' Boleh NULL
    // 'address' dihapus karena tidak ada di tabel users, atau diasumsikan ada di tabel terpisah
    'profile_picture' => $_SESSION['profile_picture'] ?? '', // Placeholder/dari session
];

if ($result && $result->num_rows > 0) {
    $db_user = $result->fetch_assoc();
    $current_user['name'] = $db_user['name'];
    $current_user['email'] = $db_user['email'];
    // Gunakan phone_number dari database
    $current_user['phone_number'] = $db_user['phone_number'] ?? ''; 
}

$stmt->close();
// --- END LOGIC AMBIL DATA ---

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | OtoWork</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />

    <style>
        body { font-family: 'Inter', sans-serif; }
        .file-upload-label {
            cursor: pointer;
            background-color: #3B82F6;
            color: white;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 14px;
            transition: background-color 0.2s;
        }
        .file-upload-label:hover {
            background-color: #3174e0;
        }
        #profile_picture_upload {
            display: none;
        }
    </style>
</head>

<body class="bg-[#0B0E17] text-gray-300 font-sans min-h-screen flex flex-col">

    <?php require_once '../includes/navbar.php'; ?>

    <div class="container mx-auto px-4 py-12 flex-grow max-w-4xl">

        <div class="mb-10 text-center">
            <h1 class="text-3xl font-bold text-white mb-2">My Profile</h1>
            <p class="text-gray-400">Manage your personal information and security settings.</p>
        </div>

        <div class="bg-[#1F2937] p-8 rounded-xl shadow-2xl border border-gray-700">

            <div class="flex flex-col items-center border-b border-gray-700 pb-8 mb-8">
                <div class="relative w-32 h-32 mb-4 mx-auto"> 
                    <img id="profile-img-preview" 
                        src="<?= !empty($current_user['profile_picture']) ? $current_user['profile_picture'] : BASE_URL . '/assets/img/user-placeholder.png' ?>" 
                        alt="Profile Picture" 
                        class="w-full h-full object-cover rounded-full border-4 border-[#3B82F6] shadow-lg">
                    
                    <label for="profile_picture_upload" class="absolute bottom-0 right-0 w-8 h-8 flex items-center justify-center bg-[#3B82F6] rounded-full cursor-pointer border-2 border-[#1F2937] hover:bg-blue-600 transition">
                        <i class="fas fa-camera text-white text-sm"></i>
                        <input type="file" id="profile_picture_upload" accept="image/*" onchange="previewImage(event)">
                    </label>
                </div>
                <h2 class="text-xl font-bold text-white"><?= htmlspecialchars($current_user['name']) ?></h2>
                <p class="text-gray-400 text-sm"><?= htmlspecialchars($current_user['email']) ?></p>
            </div>

            <form action="<?= BASE_URL ?>/logic/profile_master.php?act=update_info" method="POST" class="space-y-6">
                
                <h3 class="text-lg font-bold text-white flex items-center gap-2 border-b border-gray-700 pb-3 mb-4">
                    <i class="fas fa-info-circle text-gray-400"></i> Personal Information
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-300 mb-2">Full Name</label>
                        <input type="text" id="name" name="name" value="<?= htmlspecialchars($current_user['name']) ?>" required
                            class="w-full bg-[#111827] border border-gray-600 text-white rounded-lg p-3 focus:outline-none focus:border-[#3B82F6] transition">
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-300 mb-2">Email Address</label>
                        <input type="email" id="email" value="<?= htmlspecialchars($current_user['email']) ?>" readonly
                            class="w-full bg-[#374151] border border-gray-600 text-gray-500 rounded-lg p-3 cursor-not-allowed">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-300 mb-2">Phone Number (WhatsApp)</label>
                        <input type="text" id="phone" name="phone_number" value="<?= htmlspecialchars($current_user['phone_number']) ?>"
                            class="w-full bg-[#111827] border border-gray-600 text-white rounded-lg p-3 focus:outline-none focus:border-[#3B82F6] transition">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Role Status</label>
                        <input type="text" value="<?= $_SESSION['role'] ?? 'customer' ?>" readonly
                            class="w-full bg-[#374151] border border-gray-600 text-gray-500 rounded-lg p-3 cursor-not-allowed">
                    </div>
                </div>

                <div class="pt-4 flex justify-end">
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-8 rounded-lg transition duration-200 shadow-md">
                        <i class="fas fa-save mr-2"></i> Save Changes
                    </button>
                </div>
            </form>
            
            <div class="border-t border-gray-700 pt-8 mt-8"></div>

            <div class="space-y-4">
                <h3 class="text-lg font-bold text-white flex items-center gap-2 border-b border-gray-700 pb-3 mb-4">
                    <i class="fas fa-lock text-gray-400"></i> Security
                </h3>

                <div class="flex justify-between items-center bg-[#111827] p-4 rounded-lg border border-gray-700">
                    <p class="text-gray-300">Change your password for security reasons.</p>
                    <button onclick="showChangePasswordModal()" type="button" class="bg-yellow-600 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200 text-sm">
                        <i class="fas fa-key mr-2"></i> Change Password
                    </button>
                </div>
                
                <div class="flex justify-between items-center bg-red-900/20 p-4 rounded-lg border border-red-700">
                    <p class="text-red-300">Logging out will end your session on this device.</p>
                    <a href="<?= BASE_URL ?>/logic/auth.php?act=logout" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200 text-sm">
                        <i class="fas fa-sign-out-alt mr-2"></i> Log Out
                    </a>
                </div>
            </div>

        </div>

    </div>

    <?php require_once '../includes/footer.php'; ?>

    <script>
        // Function untuk menampilkan preview gambar
        function previewImage(event) {
            const reader = new FileReader();
            reader.onload = function(){
                const output = document.getElementById('profile-img-preview');
                output.src = reader.result;
            };
            reader.readAsDataURL(event.target.files[0]);
        }

        // Function untuk menampilkan modal ganti password
        function showChangePasswordModal() {
            Swal.fire({
                title: 'Change Password',
                html: `
                    <form id="change-password-form" action="<?= BASE_URL ?>/logic/profile_master.php?act=change_password" method="POST" class="mt-4 space-y-4 text-left">
                        <div class="space-y-2">
                            <label for="current_password" class="block text-sm font-medium text-gray-300">Current Password</label>
                            <input type="password" id="current_password" name="current_password" required 
                                class="w-full bg-[#111827] border border-gray-600 text-white rounded-lg p-3 focus:outline-none focus:border-[#3B82F6]">
                        </div>
                        <div class="space-y-2">
                            <label for="new_password" class="block text-sm font-medium text-gray-300">New Password</label>
                            <input type="password" id="new_password" name="new_password" required
                                class="w-full bg-[#111827] border border-gray-600 text-white rounded-lg p-3 focus:outline-none focus:border-[#3B82F6]">
                        </div>
                        <div class="space-y-2">
                            <label for="confirm_password" class="block text-sm font-medium text-gray-300">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" required
                                class="w-full bg-[#111827] border border-gray-600 text-white rounded-lg p-3 focus:outline-none focus:border-[#3B82F6]">
                        </div>
                    </form>
                `,
                showCancelButton: true,
                confirmButtonText: 'Update Password',
                cancelButtonText: 'Cancel',
                focusConfirm: false,
                preConfirm: () => {
                    const newPass = document.getElementById('new_password').value;
                    const confPass = document.getElementById('confirm_password').value;
                    if (newPass !== confPass) {
                        Swal.showValidationMessage('New password and confirmation do not match.');
                        return false;
                    }
                    if (newPass.length < 8) {
                        Swal.showValidationMessage('Password must be at least 8 characters long.');
                        return false;
                    }
                    // Submit form via standard POST
                    document.getElementById('change-password-form').submit();
                    return true;
                },
                background: '#1F2937',
                color: '#fff',
                confirmButtonColor: '#3B82F6',
                cancelButtonColor: '#4B5563'
            });
        }

        // Alert Handler
        <?php if (isset($_SESSION['alert'])): ?>
            Swal.fire({
                icon: '<?= $_SESSION['alert']['type'] ?>',
                title: '<?= ucfirst($_SESSION['alert']['type']) ?>',
                text: '<?= $_SESSION['alert']['message'] ?>',
                background: '#1F2937',
                color: '#fff',
                confirmButtonColor: '#3B82F6'
            });
        <?php unset($_SESSION['alert']); endif; ?>
    </script>

</body>
</html>