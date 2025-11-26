<?php
// Panggil konfigurasi biar kenal BASE_URL
require_once '../../config/database.php';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <link rel="stylesheet" href="<?= BASE_URL ?>/customer/assets/css/login.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <title>Login | OtoWork</title>
</head>

<body>
    <div class="container" id="container">

        <div class="form-container sign-up">
            <form action="<?= BASE_URL ?>/logic/auth.php" method="POST">
                <h1>Join OtoWork</h1>
                <span>Daftar dulu biar mesin panas!</span>

                <input type="hidden" name="act" value="register">

                <input type="text" placeholder="Nama Lengkap" name="name" required />
                <input type="email" placeholder="Email" name="email" required />

                <div class="password-wrapper">
                    <input type="password" id="signup-password" placeholder="Password" name="password" required />
                    <i class="fas fa-eye" id="toggle-password"></i>
                </div>

                <div id="password-criteria" class="password-criteria">
                    <p id="length" class="criteria-item invalid">Minimal 8 karakter</p>
                    <p id="capital" class="criteria-item invalid">Huruf Besar</p>
                    <p id="number" class="criteria-item invalid">Angka</p>
                    <p id="special" class="criteria-item invalid">Simbol (!@#$...)</p>
                </div>
                <button type="submit" id="signup-button">Daftar Sekarang</button>
            </form>
        </div>

        <div class="form-container sign-in">
            <form action="<?= BASE_URL ?>/logic/auth.php" method="POST">
                <h1>OtoWork Login</h1>
                <span>Masuk ke bengkel digitalmu</span>

                <input type="hidden" name="act" value="login">

                <input type="email" placeholder="Email" name="email" required />
                <div class="password-wrapper">
                    <input type="password" id="signin-password" placeholder="Password" name="password" required />
                    <i class="fas fa-eye" id="toggle-password-signin"></i>
                </div>
                <a href="#">Lupa Password?</a>
                <button type="submit">Gas Masuk!</button>
            </form>
        </div>

        <div class="toggle-container">
            <div class="toggle">
                <div class="toggle-panel toggle-left">
                    <h1>Udah punya akun?</h1>
                    <p>Login sini, antrian servis udah nungguin nih!</p>
                    <button class="hidden" id="login">Sign In</button>
                </div>
                <div class="toggle-panel toggle-right">
                    <h1>New Member?</h1>
                    <p>Gabung sama OtoWork sekarang. Solusi bengkel anti ribet, sat set wat wet!</p>
                    <button class="hidden" id="register">Sign Up</button>
                </div>
            </div>
        </div>
    </div>

    <script src="<?= BASE_URL ?>/assets/js/login-script.js"></script>

    <?php
    // Kita cek session alert
    if (isset($_SESSION['alert'])) {
        $type = $_SESSION['alert']['type']; // success atau error
        $message = $_SESSION['alert']['message'];

        echo "
        <script>
            Swal.fire({
                title: '" . ($type == 'success' ? 'Berhasil!' : 'Waduh!') . "',
                text: '$message',
                icon: '$type',
                confirmButtonColor: '#333'
            });
        </script>
        ";
        // Hapus session biar ga muncul terus pas di-refresh
        unset($_SESSION['alert']);
    }
    ?>

</body>

</html>