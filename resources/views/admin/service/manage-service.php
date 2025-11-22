<?php
$pageTitle = 'Manage Service';
$activeMenu = 'service';

require '../../../backend/config.php';
include '../template-header.php';
include '../template-sidebar.php';

$sql = "SELECT id, service_name, description, image_url FROM services ORDER BY id DESC";
$result = $conn->query($sql);
?>

<link rel="stylesheet" href="service.css"> <link rel="stylesheet" href="../style.css">
<style>
    .service-card.selected {
        outline: 3px solid #FFC72C;
        outline-offset: -3px;
    }
    .service-card {
        cursor: pointer;
    }
</style>

<div class="main-content">
    <div class="content-actions">
        <button id="btn-edit" class="btn btn-edit">Edit</button>
        <button id="btn-hapus" class="btn btn-hapus">Hapus</button>
        <button id="btn-tambah" class="btn btn-tambah">Tambah</button>
    </div>

    <div class="service-container">
        <div class="service-grid">
            <?php
            if ($result && $result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo '
                    <div class="service-card" data-id="' . $row['id'] . '">
                        <img src="/EfkaWorkshop/' . htmlspecialchars($row["image_url"]) . '" alt="' . htmlspecialchars($row["service_name"]) . '">
                        <div class="overlay">
                            <h3>' . htmlspecialchars($row["service_name"]) . '</h3>
                            <p>' . htmlspecialchars($row["description"]) . '</p>
                        </div>
                    </div>';
                }
            } else {
                echo "<p>Belum ada data layanan yang ditambahkan.</p>";
            }
            ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const serviceGrid = document.querySelector('.service-grid');
    let selectedServiceId = null;

    serviceGrid.addEventListener('click', function(e) {
        const card = e.target.closest('.service-card');
        if (!card) return;

        document.querySelectorAll('.service-card.selected').forEach(selectedCard => {
            selectedCard.classList.remove('selected');
        });

        card.classList.add('selected');
        selectedServiceId = card.dataset.id; 
    });

    document.getElementById('btn-tambah').addEventListener('click', function() {
        window.location.href = 'admin_tambah_service.php';
    });

    document.getElementById('btn-hapus').addEventListener('click', function() {
        if (!selectedServiceId) {
            alert('Pilih layanan yang ingin dihapus terlebih dahulu.');
            return;
        }
        if (confirm('Anda yakin ingin menghapus layanan ini secara permanen?')) {
            window.location.href = '/EfkaWorkshop/backend/admin_hapus_service.php?id=' + selectedServiceId;
        }
    });

    document.getElementById('btn-edit').addEventListener('click', function() {
        if (!selectedServiceId) {
            alert('Pilih layanan yang ingin diedit terlebih dahulu.');
            return;
        }
        window.location.href = 'admin_edit_service.php?id=' + selectedServiceId;
    });
});
</script>
<script src="../script.js"></script>