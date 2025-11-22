<?php
require '../../../backend/config.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: /EfkaWorkshop/Pages/login/login-page.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$activePage = 'profile';

$stmt = $conn->prepare("SELECT name, email, created_at FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - EFKA Workshop</title>
    <?php include '../header.php'; ?>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="/EfkaWorkshop/assets/libs/sweetaler2/sweetalert2.min.css">
    <script src="/EfkaWorkshop/assets/libs/sweetaler2/sweetalert2.all.min.js"></script>

</head>
<body>

<div class="profile-container">
    <div class="profile-header">
        <h1>Profil Saya</h1>
        <p>Anggota sejak: <?php echo date('d F Y', strtotime($user['created_at'])); ?></p>
    </div>

    <form id="profile-form" action="/EfkaWorkshop/backend/update_profile.php" method="POST">
        <div class="form-group">
            <label for="name">Nama Lengkap</label>
            <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" required>
        </div>
        <div class="form-group">
            <label for="email">Alamat Email</label>
            <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
            <small style="color: #888;">Email tidak dapat diubah.</small>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn-update">Simpan Perubahan</button>
            <button type="button" id="change-password-btn" class="btn-password">Ubah Password</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const changePasswordBtn = document.getElementById('change-password-btn');
    if(changePasswordBtn) {
        changePasswordBtn.addEventListener('click', function() {
            Swal.fire({
                title: 'Ubah Password',
                html: `
                    <input type="password" id="swal-old-password" class="swal2-input" placeholder="Password Lama">
                    <input type="password" id="swal-new-password" class="swal2-input" placeholder="Password Baru">
                    <input type="password" id="swal-confirm-password" class="swal2-input" placeholder="Konfirmasi Password Baru">
                `,
                confirmButtonText: 'Ubah Password',
                confirmButtonColor: '#FFC72C',
                showCancelButton: true,
                cancelButtonText: 'Batal',
                preConfirm: () => {
                    const oldPassword = document.getElementById('swal-old-password').value;
                    const newPassword = document.getElementById('swal-new-password').value;
                    const confirmPassword = document.getElementById('swal-confirm-password').value;
                    if (!oldPassword || !newPassword || !confirmPassword) {
                        Swal.showValidationMessage('Semua kolom wajib diisi');
                        return false;
                    }
                    if (newPassword !== confirmPassword) {
                        Swal.showValidationMessage('Konfirmasi password baru tidak cocok');
                        return false;
                    }
                    return { oldPassword, newPassword };
                }
            }).then(result => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('old_password', result.value.oldPassword);
                    formData.append('new_password', result.value.newPassword);

                    fetch('/EfkaWorkshop/backend/update_password.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            Swal.fire('Berhasil!', data.message, 'success');
                        } else {
                            Swal.fire('Gagal!', data.message, 'error');
                        }
                    });
                }
            });
        });
    }

    const profileForm = document.getElementById('profile-form');
    if(profileForm) {
        profileForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch(this.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                 if (data.status === 'success') {
                    Swal.fire('Berhasil!', data.message, 'success').then(() => {
                        const welcomeText = document.querySelector('.welcome-text');
                        if (welcomeText) {
                            welcomeText.innerHTML = `Hi, ${data.new_name.split(' ')[0]} <i class="fas fa-chevron-down dropdown-arrow"></i>`;
                        }
                    });
                } else {
                    Swal.fire('Gagal!', data.message, 'error');
                }
            });
        });
    }
});
</script>

</body>
</html>