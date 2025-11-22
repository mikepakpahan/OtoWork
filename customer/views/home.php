<?php
// 1. Panggil Config (Mundur 2 langkah dari customer/views)
require_once '../../config/database.php';

// 2. Logic Ringan (Fetch Data Rating & Service)
// Idealnya ini di file logic terpisah, tapi taruh sini dulu biar simpel buat Tim.

// Ambil Rata-rata Rating
$average_rating = 0;
$total_reviews = 0;
$sql_rating = "SELECT AVG(rating) as avg_rating, COUNT(id) as total_reviews FROM reviews";
$result_rating = $conn->query($sql_rating);

if ($result_rating && $result_rating->num_rows > 0) {
    $rating_data = $result_rating->fetch_assoc();
    $average_rating = number_format($rating_data['avg_rating'] ?? 0, 1);
    $total_reviews = $rating_data['total_reviews'] ?? 0;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>OtoWork | Solusi Motor Kesayangan</title>

    <link href="https://fonts.googleapis.com/css2?family=Open+Sans+Condensed:wght@300;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="<?= BASE_URL ?>/customer/assets/css/home.css" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body style="font-family: 'Open Sans Condensed', Arial, sans-serif">

    <?php require_once '../includes/navbar.php'; ?>

    <section class="hero-section">
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <div class="hero-title-group hero-title-group-large hero-title-group-left">
                <div class="hero-subtitle">BERKENDARA DENGAN NYAMAN</div>
                <h1 class="hero-title">
                    Premium Motor <span class="highlight">Detailing</span><br />
                    & Repair <span class="highlight">Solutions</span>
                </h1>
                <div class="hero-desc">
                    OtoWork hadir sebagai solusi andal bagi Anda yang menginginkan pelayanan servis kendaraan dengan kualitas terbaik.
                    Kami merawat kendaraan Anda dengan penuh ketelitian, teknisi profesional, dan sparepart terjamin.
                </div>
                <div class="hero-actions">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="#booking-section" class="btn-primary">BUAT JANJI</a>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>/customer/views/login.php" class="btn-primary">LOGIN UNTUK BOOKING</a>
                    <?php endif; ?>
                    <a href="#services" class="btn-link">LIHAT LAYANAN &rarr;</a>
                </div>
            </div>

            <div class="hero-features">
                <div class="feature-card">
                    <div class="feature-title">Expert & Profesional</div>
                    <div class="feature-desc">Mekanik handal bersertifikat siap menangani motor Anda.</div>
                </div>
                <div class="feature-card">
                    <div class="feature-title">Garansi Servis</div>
                    <div class="feature-desc">Jaminan kualitas untuk setiap perbaikan yang kami lakukan.</div>
                </div>
                <div class="feature-card">
                    <div class="feature-title">Konsultasi Gratis</div>
                    <div class="feature-desc">Bingung kenapa motor bunyi kletek-kletek? Tanya aja dulu!</div>
                </div>
            </div>
        </div>
    </section>

    <section class="min-h-screen w-full py-16 px-0" id="services" style="background: #1b2649">
        <div class="max-w-7xl mx-auto px-4">
            <div class="mb-12">
                <div class="text-[#FFC72C] text-base sm:text-lg font-bold mb-2 tracking-widest uppercase">Our Services</div>
                <h2 class="text-white text-4xl sm:text-5xl font-bold mb-2">Layanan <span class="text-[#FFC72C]">Unggulan</span> <br />OtoWork</h2>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php
                $sql_services = "SELECT service_name, description, image_url FROM services ORDER BY id";
                $result_services = $conn->query($sql_services);

                if ($result_services && $result_services->num_rows > 0) {
                    while ($row = $result_services->fetch_assoc()) {
                        // Pastikan image_url di database lengkap atau pake BASE_URL/assets/img/...
                        echo '
                        <div class="bg-transparent group">
                            <div class="relative overflow-hidden rounded-md shadow-lg">
                                <img src="' . BASE_URL . '/assets/img/' . htmlspecialchars($row["image_url"]) . '" 
                                     alt="' . htmlspecialchars($row["service_name"]) . '" 
                                     class="w-full h-56 object-cover transition duration-300 group-hover:scale-110" 
                                     onerror="this.src=\'' . BASE_URL . '/assets/img/default-service.jpg\'" /> 
                                <div class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-80 p-4">
                                    <div class="text-white font-bold text-lg mb-1">' . htmlspecialchars($row["service_name"]) . '</div>
                                    <div class="text-gray-300 text-sm">' . htmlspecialchars($row["description"]) . '</div>
                                </div>
                            </div>
                        </div>';
                    }
                } else {
                    echo '<p class="text-white col-span-3 text-center">Belum ada layanan yang diinput Admin.</p>';
                }
                ?>
            </div>
        </div>
    </section>

    <section class="min-h-screen w-full pt-20 pb-16 px-0" id="aboutus" style="background: #0c0a27">
        <div class="max-w-7xl mx-auto px-4 flex flex-col md:flex-row items-center gap-8">
            <div class="flex-1 text-white about-left-top">
                <div class="text-[#FFC72C] text-base sm:text-lg font-bold mb-2 tracking-widest uppercase">TENTANG KAMI</div>
                <h2 class="text-3xl sm:text-4xl md:text-5xl font-bold mb-4 leading-tight">Bengkel Modern untuk<br />Generasi Kekinian</h2>
                <div class="text-gray-200 text-base sm:text-lg mb-6 max-w-xl">
                    OtoWork didirikan dengan satu tujuan: mengubah pengalaman servis motor yang membosankan menjadi transparan, cepat, dan menyenangkan. Kami menggunakan peralatan modern dan sistem booking online agar Anda tidak perlu antre seharian.
                </div>

                <div class="flex flex-wrap gap-8 mb-6">
                    <div class="flex items-center gap-3">
                        <img src="<?= BASE_URL ?>/assets/img/icon/icon-staff.png" alt="Staff" class="w-8 h-8" />
                        <div class="font-semibold text-white">Mekanik Profesional</div>
                    </div>
                    <div class="flex items-center gap-3">
                        <img src="<?= BASE_URL ?>/assets/img/icon/icon-warranty.png" alt="Warranty" class="w-8 h-8" />
                        <div class="font-semibold text-white">Bergaransi</div>
                    </div>
                    <div class="flex items-center gap-3">
                        <i class="fas fa-star text-yellow-400 text-2xl"></i>
                        <div>
                            <div class="font-semibold text-white">Rating Kepuasan</div>
                            <div class="flex items-center gap-2">
                                <span class="text-yellow-400 font-bold"><?php echo $average_rating; ?></span>
                                <span class="text-gray-400 text-sm">/ 5.0 (<?php echo $total_reviews; ?> ulasan)</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="about-right w-full md:w-1/3">
                <div class="bg-gray-800 p-6 rounded-lg shadow-xl">
                    <h3 class="text-xl text-white font-bold mb-4">Kirim Masukan</h3>
                    <form id="feedback-form" action="<?= BASE_URL ?>/logic/feedback.php" method="POST">
                        <input type="text" name="name" placeholder="Nama Anda" class="form-input w-full mb-3 p-2 rounded bg-gray-700 text-white border-none"
                            value="<?php echo isset($_SESSION['name']) ? htmlspecialchars($_SESSION['name']) : ''; ?>"
                            <?php echo isset($_SESSION['user_id']) ? 'readonly' : ''; ?> required>

                        <input type="email" name="email" placeholder="Email Anda" class="form-input w-full mb-3 p-2 rounded bg-gray-700 text-white border-none"
                            value="<?php echo isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : ''; ?>"
                            <?php echo isset($_SESSION['user_id']) ? 'readonly' : ''; ?> required>

                        <textarea name="message" placeholder="Tulis masukan..." rows="4" class="form-textarea w-full mb-3 p-2 rounded bg-gray-700 text-white border-none" required></textarea>

                        <?php if (isset($_SESSION["user_id"])): ?>
                            <button type="submit" class="w-full py-2 bg-[#FFC72C] text-black font-bold rounded hover:bg-yellow-400">Kirim Feedback</button>
                        <?php else: ?>
                            <button type="button" onclick="Swal.fire('Login Dulu','Silahkan login untuk kirim feedback','info')" class="w-full py-2 bg-gray-600 text-gray-300 font-bold rounded cursor-not-allowed">Login untuk Kirim</button>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <section class="py-16" id="booking-section" style="background: #0D1117;">
        <div class="max-w-4xl mx-auto px-4 text-center">
            <h2 class="text-white text-3xl sm:text-4xl font-bold mb-4">Booking Servis Tanpa Antre</h2>
            <p class="text-gray-400 mb-8">Isi form di bawah, datang, langsung dikerjakan.</p>

            <?php if (isset($_SESSION['user_id'])): ?>
                <form action="<?= BASE_URL ?>/logic/booking.php" method="POST" class="max-w-xl mx-auto text-left">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Nama</label>
                            <input type="text" class="w-full p-3 bg-gray-800 border border-gray-700 rounded-md text-white" value="<?php echo htmlspecialchars($_SESSION['name']); ?>" readonly>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Email</label>
                            <input type="email" class="w-full p-3 bg-gray-800 border border-gray-700 rounded-md text-white" value="<?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>" readonly>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Merk Motor</label>
                            <select name="motor_type" class="w-full p-3 bg-gray-800 border border-gray-700 rounded-md text-white" required>
                                <option value="" disabled selected>-- Pilih Merk --</option>
                                <option value="Honda">Honda</option>
                                <option value="Yamaha">Yamaha</option>
                                <option value="Suzuki">Suzuki</option>
                                <option value="Kawasaki">Kawasaki</option>
                                <option value="Vespa">Vespa</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Tanggal Booking</label>
                            <input type="date" name="booking_date" class="w-full p-3 bg-gray-800 border border-gray-700 rounded-md text-white" required min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                        </div>
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-300 mb-1">Keluhan / Request</label>
                        <textarea name="complaint" rows="4" class="w-full p-3 bg-gray-800 border border-gray-700 rounded-md text-white" placeholder="Contoh: Ganti oli dan cek rem depan bunyi." required></textarea>
                    </div>

                    <button type="submit" class="w-full py-3 px-4 bg-[#FFC72C] text-black font-bold rounded-md hover:bg-yellow-400 transition-colors">
                        Kirim Jadwal Booking
                    </button>
                </form>

            <?php else: ?>
                <div class="mt-8 p-8 bg-gray-800 rounded-lg border border-gray-700">
                    <i class="fas fa-lock text-4xl text-yellow-400 mb-4"></i>
                    <p class="text-white text-lg mb-6">Silahkan login terlebih dahulu untuk melakukan booking servis.</p>
                    <a href="<?= BASE_URL ?>/customer/views/login.php" class="inline-block py-3 px-8 bg-[#FFC72C] text-black font-bold rounded-md hover:bg-yellow-400 transition-colors">
                        Login / Daftar Sekarang
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <button onclick="window.scrollTo({top: 0, behavior: 'smooth'});" class="fixed bottom-6 right-6 bg-gray-800 hover:bg-gray-700 text-white p-4 rounded-full shadow-2xl transition-all duration-300 z-50 hover:scale-110 border border-yellow-500">
        <i class="fas fa-arrow-up"></i>
    </button>

    <?php require_once '../includes/footer.php'; ?>

    <?php if (isset($_SESSION['alert'])): ?>
        <script>
            Swal.fire({
                title: '<?= $_SESSION['alert']['type'] == 'success' ? 'Berhasil!' : 'Gagal!' ?>',
                text: '<?= $_SESSION['alert']['message'] ?>',
                icon: '<?= $_SESSION['alert']['type'] ?>',
                confirmButtonColor: '#FFC72C'
            });
        </script>
    <?php unset($_SESSION['alert']);
    endif; ?>

</body>

</html>