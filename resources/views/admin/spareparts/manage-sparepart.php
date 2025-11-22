<?php
$pageTitle = 'Management Sparepart';
$activeMenu = 'sparepart';

require '../../../backend/config.php';
include '../template-header.php'; 
?>

<link rel="stylesheet" href="/EfkaWorkshop/assets/libs/sweetalert2/sweetalert2.min.css">
<script src="/EfkaWorkshop/assets/libs/sweetalert2/sweetalert2.all.min.js"></script>

<style>
    html, body {
        height: 100%;
        margin: 0;
        overflow: hidden;
        font-family: 'Inter', sans-serif;
    }

    .page-container {
        display: flex;
        flex-direction: column; 
        height: 100%;
    }

    .top-header {
        flex-shrink: 0;
    }

    .main-body {
        display: flex;
        flex-grow: 1; 
        overflow: hidden;
    }

    .sidebar {
        flex-shrink: 0; 
        width: 250px; 
    }

    .main-content {
        flex-grow: 1;  
        overflow-y: auto;  
        padding: 2rem;
        margin-bottom: 70px;
    }
    .content-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
    .content-header h1 { margin: 0; }
    .content-actions .btn { margin-left: 0.5rem; }
    .feedback-tabs { display: flex; margin-bottom: 1.5rem; border: 1px solid #ddd; border-radius: 8px; overflow: hidden; width: fit-content; }
    .tab-btn { padding: 10px 20px; background-color: #fff; border: none; cursor: pointer; font-weight: 600; color: #555; transition: all 0.3s ease; }
    .tab-btn:first-child { border-right: 1px solid #ddd; }
    .tab-btn.active { background-color: #FFC72C; color: #1F2937; }
    .feedback-viewport { width: 100%; overflow: hidden; }
    .feedback-slider { display: flex; width: 200%; transition: transform 0.4s ease-in-out; }
    .feedback-slider.show-read { transform: translateX(-50%); }
    .feedback-panel { width: 50%; padding: 0 5px; box-sizing: border-box; }
    .product-card {
        cursor: pointer;
        transition: all 0.2s ease-in-out;
    }

    .product-card.selected {
        outline: 3px solid #007bff;
        box-shadow: 0 6px 20px rgba(0, 123, 255, 0.4);
        transform: scale(1.02); 
    }

</style>

<body>
    <div class="page-container">
        <?php include '../template-sidebar.php'; ?>

        <div class="main-body">
            <main class="main-content">
                <div class="content-header">
                    <h1>Management Sparepart</h1>
                    <div class="content-actions">
                        <button id="btn-reactivate" class="btn btn-tambah" style="display: none;">Aktifkan Kembali</button>
                        <button id="btn-hero" class="btn btn-hero">Jadikan Hero</button>
                        <button id="btn-edit" class="btn btn-edit">Edit</button>
                        <button id="btn-archive" class="btn btn-hapus">Arsipkan</button>
                        <button id="btn-tambah" class="btn btn-tambah">Tambah</button>
                    </div>
                </div>

                <div class="feedback-tabs">
                    <button id="active-btn" class="tab-btn active">Produk Aktif</button>
                    <button id="archived-btn" class="tab-btn">Diarsipkan</button>
                </div>

                <div class="feedback-viewport">
                    <div id="product-slider" class="feedback-slider">
                        
                        <div class="feedback-panel">
                            <div class="products-grid" id="active-products-grid">
                                </div>
                        </div>

                        <div class="feedback-panel">
                            <div class="products-grid" id="archived-products-grid">
                                </div>
                        </div>

                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="../script.js"></script> 
</body>
</html>

<?php
$sql = "SELECT id, part_name, description, price, stock, image_url, is_featured, is_active FROM spareparts ORDER BY id DESC";
$result = $conn->query($sql);

$active_products_html = '';
$archived_products_html = '';

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $formatted_price = number_format($row["price"], 0, ',', '.');
        $is_hero_badge = $row['is_featured'] ? '<span class="hero-badge">★ Unggulan</span>' : '';
        
        $card_html = '
        <div class="product-card" data-id="' . $row['id'] . '" data-is-featured="' . $row['is_featured'] . '">
            ' . $is_hero_badge . '
            <img src="' . htmlspecialchars($row["image_url"]) . '" alt="' . htmlspecialchars($row["part_name"]) . '" class="product-img">
            <div class="product-info">
                <h3 class="product-title">' . htmlspecialchars($row["part_name"]) . '</h3>
                <p class="product-description">' . htmlspecialchars($row["description"]) . '</p>
                <div class="product-detail">
                    <span>Harga</span>
                    <strong>Rp ' . $formatted_price . '</strong>
                </div>
                <div class="product-detail">
                    <span>Stok</span>
                    <strong>' . $row['stock'] . '</strong>
                </div>
            </div>
        </div>';

        if ($row['is_active']) {
            $active_products_html .= $card_html;
        } else {
            $archived_products_html .= $card_html;
        }
    }
}

if (empty($active_products_html)) {
    $active_products_html = "<p>Tidak ada produk aktif.</p>";
}

if (empty($archived_products_html)) {
    $archived_products_html = "<p>Tidak ada produk yang diarsipkan.</p>";
}
?>

<script>
document.getElementById('active-products-grid').innerHTML = `<?php echo addslashes($active_products_html); ?>`;
document.getElementById('archived-products-grid').innerHTML = `<?php echo addslashes($archived_products_html); ?>`;

document.addEventListener('DOMContentLoaded', function() {
    const slider = document.getElementById('product-slider');
    const activeBtn = document.getElementById('active-btn');
    const archivedBtn = document.getElementById('archived-btn');
    const productGrid = document.querySelector('.feedback-viewport');
    
    const tambahBtn = document.getElementById('btn-tambah');
    const arsipBtn = document.getElementById('btn-archive');
    const aktifkanBtn = document.getElementById('btn-reactivate');
    const editBtn = document.getElementById('btn-edit');
    const heroBtn = document.getElementById('btn-hero');

    let selectedProductId = null;
    let isArchivedTab = false;

    activeBtn.addEventListener('click', function() {
        slider.classList.remove('show-read'); 
        activeBtn.classList.add('active');
        archivedBtn.classList.remove('active');
        isArchivedTab = false;
        updateButtonVisibility();
    });

    archivedBtn.addEventListener('click', function() {
        slider.classList.add('show-read');
        activeBtn.classList.remove('active');
        archivedBtn.classList.add('active');
        isArchivedTab = true;
        updateButtonVisibility();
    });

    productGrid.addEventListener('click', function(e) {
        const card = e.target.closest('.product-card');
        if (!card) return;

        productGrid.querySelectorAll('.selected').forEach(c => c.classList.remove('selected'));
        card.classList.add('selected');
        selectedProductId = card.dataset.id;
    });

    function updateButtonVisibility() {
        if (isArchivedTab) {
            arsipBtn.style.display = 'none';
            heroBtn.style.display = 'none';
            editBtn.style.display = 'none';
            aktifkanBtn.style.display = 'inline-block';
        } else {
            arsipBtn.style.display = 'inline-block';
            heroBtn.style.display = 'inline-block';
            editBtn.style.display = 'inline-block';
            aktifkanBtn.style.display = 'none';
        }
    }

    tambahBtn.addEventListener('click', () => window.location.href = 'admin-tambah-produk.php');
    
    editBtn.addEventListener('click', () => {
        if (!selectedProductId) return Swal.fire('Oops...', 'Pilih produk untuk diedit.', 'warning');
        window.location.href = 'admin-edit-produk.php?id=' + selectedProductId;
    });

    heroBtn.addEventListener('click', () => {
        if (!selectedProductId) return Swal.fire('Oops...', 'Pilih produk untuk dijadikan hero.', 'warning');
        Swal.fire({
            title: 'Jadikan Produk Unggulan?',
            text: "Produk ini akan jadi banner utama di halaman sparepart.",
            icon: 'info', showCancelButton: true, confirmButtonText: 'Ya, Jadikan Hero!'
        }).then(result => {
            if (result.isConfirmed) window.location.href = '../../../backend/proses_make_hero.php?id=' + selectedProductId;
        });
    });

    arsipBtn.addEventListener('click', () => {
        if (!selectedProductId) return Swal.fire('Oops...', 'Pilih produk untuk diarsipkan.', 'warning');
        Swal.fire({
            title: 'Arsipkan Produk?',
            text: "Produk ini tidak akan tampil di halaman customer, tapi riwayatnya tetap aman.",
            icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya, Arsipkan!'
        }).then(result => {
            if (result.isConfirmed) window.location.href = '../../../backend/admin_hapus_produk.php?id=' + selectedProductId; // Menggunakan skrip lama yang sudah diubah jadi soft-delete
        });
    });

    aktifkanBtn.addEventListener('click', () => {
        if (!selectedProductId) return Swal.fire('Oops...', 'Pilih produk untuk diaktifkan kembali.', 'warning');
        window.location.href = '../../../backend/proses_reactivate_produk.php?id=' + selectedProductId;
    });

    updateButtonVisibility();
});
</script>