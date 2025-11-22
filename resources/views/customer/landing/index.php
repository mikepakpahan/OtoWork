<?php
include 'backend/config.php';

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
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans+Condensed:wght@300;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="Pages/customer/landing/style.css" />
    <title>Efka Workshop</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="scripts.js"></script>
  </head>
  <body style="font-family: 'Open Sans Condensed', Arial, sans-serif">
    <?php
      include 'Pages/customer/header.php';
    ?>
    
    <section class="hero-section">
      <div class="hero-overlay"></div>
      <div class="hero-content">
        <div class="hero-title-group hero-title-group-large hero-title-group-left">
          <div class="hero-subtitle">BERKENDARA DENGAN NYAMAN</div>
          <h1 class="hero-title">
            Premium Motor <span class="highlight">Detailing</span><br />
            & Repair <span class="highlight">Solutions</span>
          </h1>
          <div class="hero-desc">Bengkel kami hadir sebagai solusi andal bagi Anda yang menginginkan pelayanan servis kendaraan dengan kualitas terbaik. Di sini, kami tidak hanya memperbaiki, tetapi juga merawat kendaraan Anda dengan penuh ketelitian dan tanggung jawab.</div>
          <div class="hero-actions">
            <?php
            if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true) {
                echo '<a href="#booking-section" class="btn-primary">BUAT JANJI</a>';
            } else {
                echo '<a href="#" onclick="alert(\'Anda harus login terlebih dahulu untuk membuat janji.\');" class="btn-primary">BUAT JANJI</a>';
            }
            ?>
            <a href="#services" class="btn-link">READ MORE &rarr;</a>
          </div>
        </div>
        <div class="hero-features">
          <div class="feature-card">
            <div class="feature-title">Expertise & Profesional</div>
            <div class="feature-desc">Kami memiliki mekanis yang handal dan profesional dalam menangani berbagai jenis motor</div>
          </div>
          <div class="feature-card">
            <div class="feature-title">24/7 Ready Support</div>
            <div class="feature-desc">Layanan kami tersedia 24 jam setiap hari untuk menangani permintaan customer</div>
          </div>
          <div class="feature-card">
            <div class="feature-title">Free Consulting</div>
            <div class="feature-desc">Kami menyediakan layanan konsultasi gratis kepada customer untuk membantu customer merawat motor.</div>
          </div>
        </div>
      </div>
    </section>

    <section class="min-h-screen w-full py-16 px-0" id="services" style="background: #1b2649">
    <div class="max-w-7xl mx-auto px-4">
        <div class="mb-12">
            <div class="text-[#FFC72C] text-base sm:text-lg font-bold mb-2 tracking-widest uppercase">Our Services</div>
            <h2 class="text-white text-4xl sm:text-5xl font-bold mb-2">Delivering <span class="text-[#FFC72C]">Superior</span> Motor Detailing<br />& Repair</h2>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php
            $sql_services = "SELECT service_name, description, image_url FROM services ORDER BY id";
            $result_services = $conn->query($sql_services);

            if ($result_services && $result_services->num_rows > 0) {
                while($row = $result_services->fetch_assoc()) {
                    echo '
                    <div class="bg-transparent">
                        <div class="relative">
                            <img src="' . htmlspecialchars($row["image_url"]) . '" alt="' . htmlspecialchars($row["service_name"]) . '" class="w-full h-56 object-cover rounded-t-md" />
                            <div class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-80 p-4 rounded-b-md">
                                <div class="text-white font-bold text-lg mb-1">' . htmlspecialchars($row["service_name"]) . '</div>
                                <div class="text-gray-200 text-sm">' . htmlspecialchars($row["description"]) . '</div>
                            </div>
                        </div>
                    </div>';
                }
            } else {
                echo '<p class="text-white col-span-3">Saat ini belum ada layanan yang tersedia.</p>';
            }
            ?>
        </div>
    </div>
</section>

    <section class="min-h-screen w-full pt-20 pb-16 px-0" id="aboutus" style="background: #0c0a27">
      <div class="max-w-7xl mx-auto px-4 flex flex-col md:flex-row items-center gap-8">
        <div class="flex-1 text-white about-left-top">
          <div class="text-[#FFC72C] text-base sm:text-lg font-bold mb-2 tracking-widest uppercase">WHO WE ARE ?</div>
          <h2 class="whitespace-nowrap text-3xl sm:text-4xl md:text-5xl font-bold mb-4 leading-tight">Motor Detailing And Repair<br />Services You Can Rely On</h2>
          <div class="text-gray-200 text-base sm:text-lg mb-6 max-w-xl">lapet ni gadong kocak lapet ni gadong kocak lapet ni gadong kocak lapet ni gadong kocak lapet ni gadong kocak lapet ni gadong kocak lapet ni gadong kocak</div>
          <div class="flex flex-wrap gap-8 mb-6">
          <div class="flex items-center gap-3">
              <img src="assets/icon-staff.png" alt="Staff" class="w-8 h-8" />
              <div>
                  <div class="font-semibold text-white">Professional & Creative Staff</div>
              </div>
          </div>
          
          <div class="flex items-center gap-3">
              <img src="assets/icon-warranty.png" alt="Warranty" class="w-8 h-8" />
              <div>
                  <div class="font-semibold text-white">Warranties & Guarantees</div>
              </div>
          </div>

          <div class="flex items-center gap-3">
              <img src="assets/icons/icon-star.png" alt="Rating" class="w-8 h-8" /> <div>
                  <div class="font-semibold text-white">Rating Kepuasan</div>
                  <div class="flex items-center gap-2">
                      <div class="rating-stars-display">
                          <?php
                          for ($i = 1; $i <= 5; $i++) {
                              if ($i <= $average_rating) {
                                  echo '<i class="fas fa-star"></i>';
                              } else {
                                  echo '<i class="far fa-star"></i>';
                              }
                          }
                          ?>
                      </div>
                      <span class="rating-text text-white"><?php echo $average_rating; ?> dari <?php echo $total_reviews; ?> ulasan</span>
                  </div>
              </div>
          </div>
          </div>
          <div class="flex items-center gap-3 mt-2">
            <img src="assets/telephone.png" alt="Telepon" class="w-6 h-6" />
            <span class="text-white text-base font-semibold">+62 812 3456 7890</span>
          </div>
        </div>

        <div class="about-right">
            <form class="request-form" id="feedback-form" action="backend/proses_feedback.php" method="POST">
                <input type="text" name="name" placeholder="Nama Anda" class="form-input" 
                      value="<?php echo isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : ''; ?>" required readonly>
                <input type="email" name="email" placeholder="Email Anda" class="form-input"
                      value="<?php echo isset($_SESSION['user_email']) ? htmlspecialchars($_SESSION['user_email']) : ''; ?>" required readonly>
                <textarea id="message" name="message" placeholder="Tuliskan masukan atau keluhan Anda di sini..." rows="6" class="form-textarea" required></textarea>
                <?php
                if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true) {
                    echo '<button type="submit" class="form-btn">Kirim Feedback</button>';
                } else {
                    echo '<button type="button" onclick="alert(\'Anda harus login untuk mengirim feedback.\');" class="form-btn" style="background-color:#ccc; cursor:not-allowed;">Kirim Feedback</button>';
                }
                ?>
            </form>
        </div>
      </div>
    </section>

    <section class="py-16" id="booking-section" style="background: #0D1117;" id="booking-section">
    <div class="max-w-4xl mx-auto px-4 text-center">
        <h2 class="text-white text-3xl sm:text-4xl font-bold mb-4">Buat Janji Servis Anda Sekarang</h2>
        <p class="text-gray-400 mb-8">Silakan isi form di bawah ini. Pastikan Anda sudah login untuk melanjutkan.</p>

        <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
            <form id="booking-form" action="/EfkaWorkshop/backend/proses_booking.php" method="POST" class="max-w-xl mx-auto text-left">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-300 mb-1">Nama</label>
                        <input type="text" id="name" name="name" class="w-full p-3 bg-gray-800 border border-gray-700 rounded-md text-white" value="<?php echo htmlspecialchars($_SESSION['user_name']); ?>" readonly>
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-300 mb-1">Email</label>
                        <input type="email" id="email" name="email" class="w-full p-3 bg-gray-800 border border-gray-700 rounded-md text-white" value="<?php echo htmlspecialchars($_SESSION['user_email']); ?>" readonly>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="motor_type" class="block text-sm font-medium text-gray-300 mb-1">Merk Motor</label>
                        <select id="motor_type" name="motor_type" class="w-full p-3 bg-gray-800 border border-gray-700 rounded-md text-white" required>
                            <option value="" disabled selected>-- Pilih Merk Motor --</option>
                            <option value="Honda">Honda</option>
                            <option value="Yamaha">Yamaha</option>
                            <option value="Suzuki">Suzuki</option>
                            <option value="Kawasaki">Kawasaki</option>
                            <option value="Vespa">Vespa</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div>
                        <label for="booking_date" class="block text-sm font-medium text-gray-300 mb-1">Tanggal Booking</label>
                        <input type="date" id="booking_date" name="booking_date" class="w-full p-3 bg-gray-800 border border-gray-700 rounded-md text-white" required min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                    </div>
                </div>
                <div class="mb-6">
                    <label for="complaint" class="block text-sm font-medium text-gray-300 mb-1">Keluhan / Layanan yang Diinginkan</label>
                    <textarea id="complaint" name="complaint" rows="4" class="w-full p-3 bg-gray-800 border border-gray-700 rounded-md text-white" placeholder="Contoh: Ganti oli, servis rem, dan cek kelistrikan." required></textarea>
                </div>
                <div>
                    <button type="submit" class="w-full py-3 px-4 bg-[#FFC72C] text-black font-bold rounded-md hover:bg-yellow-400 transition-colors">Kirim Jadwal Booking</button>
                </div>
            </form>
        <?php else: ?>
            <div class="mt-8">
                <p class="text-yellow-400 mb-4">Anda harus login untuk dapat membuat janji servis.</p>
                <a href="Pages/login/login-page.php" class="py-3 px-8 bg-[#FFC72C] text-black font-bold rounded-md hover:bg-yellow-400 transition-colors">Login atau Daftar Sekarang</a>
            </div>
        <?php endif; ?>
    </div>
</section>
    <button onclick="goHome()" class="fixed bottom-6 right-6 hover:bg-gray-700 text-white p-4 rounded-full shadow-2xl hover:shadow-3xl transition-all duration-300 z-50 hover:scale-110">
      <img src="assets/arrow.png" alt="Home" class="w-10 h-10" />
    </button>
    <script src="Pages/customer/landing/script.js"></script>
    <script>
      function goHome() {
        window.scrollTo({ top: 0, behavior: "smooth" });
      }
    </script>
    <?php
      include 'Pages/customer/footer.php';
    ?>
  </body>

  <script>
const navToggle = document.getElementById("navToggle");
const mobileNav = document.getElementById("mobileNav");
navToggle.addEventListener("click", function () {
  if (mobileNav.style.display === "flex") {
    mobileNav.style.display = "none";
  } else {
    mobileNav.style.display = "flex";
  }
});
window.addEventListener("click", function (e) {
  if (
    mobileNav.style.display === "flex" &&
    !mobileNav.contains(e.target) &&
    !navToggle.contains(e.target)
  ) {
    mobileNav.style.display = "none";
  }
});
  </script>
</html>
