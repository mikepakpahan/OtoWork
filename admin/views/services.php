<?php
require_once '../../config/database.php';

// Cek Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "/customer/views/login.php");
    exit;
}

$pageTitle = 'Manajemen Layanan Servis';
$activeMenu = 'services'; // Pastikan di Sidebar menunya punya logic active buat ini (opsional)

// Ambil Data
$result = $conn->query("SELECT * FROM services ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Layanan Servis | OtoWork Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gray-100 font-sans">

    <div class="flex h-screen overflow-hidden">
        <?php require_once '../includes/sidebar.php'; ?>

        <div class="flex-1 flex flex-col w-full">
            <?php require_once '../includes/header.php'; ?>

            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">

                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-700">Daftar Layanan Servis</h3>
                        <p class="text-gray-500 text-sm">Update jenis servis yang tampil di Landing Page.</p>
                    </div>
                    <button onclick="openModal('add')" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow-lg transform hover:scale-105 transition">
                        <i class="fas fa-plus mr-2"></i> Tambah Layanan
                    </button>
                </div>

                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <table class="min-w-full leading-normal">
                        <thead>
                            <tr>
                                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Ilustrasi</th>
                                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Nama Servis & Deskripsi</th>
                                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Estimasi Harga</th>
                                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm w-32">
                                            <div class="w-24 h-16 rounded overflow-hidden shadow-sm">
                                                <img class="w-full h-full object-cover"
                                                    src="<?= BASE_URL ?>/assets/img/services/<?= $row['image_url'] ?>"
                                                    onerror="this.src='https://via.placeholder.com/150?text=Service'"
                                                    alt="Service Img" />
                                            </div>
                                        </td>
                                        <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                            <p class="text-gray-900 font-bold text-lg"><?= htmlspecialchars($row['service_name']) ?></p>
                                            <p class="text-gray-500 text-sm mt-1"><?= htmlspecialchars(substr($row['description'], 0, 100)) ?>...</p>
                                        </td>
                                        <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                            <span class="bg-green-100 text-green-800 py-1 px-3 rounded-full text-xs font-bold">
                                                Rp <?= number_format($row['price'], 0, ',', '.') ?>
                                            </span>
                                        </td>
                                        <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-center">
                                            <button onclick='openModal("edit", <?= json_encode($row) ?>)' class="text-blue-600 hover:text-blue-900 mr-3 transition" title="Edit">
                                                <i class="fas fa-edit fa-lg"></i>
                                            </button>
                                            <button onclick="confirmDelete(<?= $row['id'] ?>)" class="text-red-600 hover:text-red-900 transition" title="Hapus">
                                                <i class="fas fa-trash fa-lg"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center py-6 text-gray-500">Belum ada layanan servis yang didaftarkan.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </main>
        </div>
    </div>

    <div id="serviceModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50 flex items-center justify-center">
        <div class="relative p-5 border w-96 md:w-1/2 shadow-2xl rounded-lg bg-white transform transition-all">

            <div class="flex justify-between items-center mb-4 border-b pb-2">
                <h3 class="text-xl font-bold text-gray-800" id="modalTitle">Tambah Layanan</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
            </div>

            <form id="serviceForm" action="<?= BASE_URL ?>/logic/service_master.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="act" id="formAct" value="add">
                <input type="hidden" name="id" id="serviceId">

                <div class="space-y-4">
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">Nama Layanan</label>
                        <input type="text" name="service_name" id="service_name" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500" placeholder="Contoh: Ganti Oli Matic" required>
                    </div>

                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">Estimasi Biaya (Rp)</label>
                        <input type="number" name="price" id="price" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500" placeholder="50000" required>
                    </div>

                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">Deskripsi Layanan</label>
                        <textarea name="description" id="description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500" placeholder="Jelaskan apa saja yang dikerjakan..."></textarea>
                    </div>

                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">Gambar Ilustrasi</label>
                        <input type="file" name="image" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        <p class="text-xs text-gray-400 mt-1">*Format: JPG, PNG. Max 2MB.</p>
                    </div>
                </div>

                <div class="flex justify-end pt-6">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded mr-2 hover:bg-gray-300 font-medium">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white font-bold rounded hover:bg-blue-700 shadow-md">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById('serviceModal');
        const formAct = document.getElementById('formAct');
        const serviceId = document.getElementById('serviceId');
        const modalTitle = document.getElementById('modalTitle');

        // Buka Modal
        function openModal(mode, data = null) {
            modal.classList.remove('hidden');
            if (mode === 'edit' && data) {
                modalTitle.innerText = 'Edit Layanan';
                formAct.value = 'update';
                serviceId.value = data.id;

                // Isi Form
                document.getElementById('service_name').value = data.service_name;
                document.getElementById('price').value = data.price;
                document.getElementById('description').value = data.description;
            } else {
                // Mode Add
                modalTitle.innerText = 'Tambah Layanan Baru';
                formAct.value = 'add';
                document.getElementById('serviceForm').reset();
            }
        }

        function closeModal() {
            modal.classList.add('hidden');
        }

        // Confirm Delete
        function confirmDelete(id) {
            Swal.fire({
                title: 'Hapus Layanan?',
                text: "Layanan ini akan hilang dari halaman depan website.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `<?= BASE_URL ?>/logic/service_master.php?act=delete&id=${id}`;
                }
            })
        }

        // Alert Session
        <?php if (isset($_SESSION['alert'])): ?>
            Swal.fire({
                icon: '<?= $_SESSION['alert']['type'] ?>',
                title: '<?= $_SESSION['alert']['type'] == 'success' ? 'Berhasil' : 'Oops' ?>',
                text: '<?= $_SESSION['alert']['message'] ?>',
                confirmButtonColor: '#3B82F6'
            });
        <?php unset($_SESSION['alert']);
        endif; ?>
    </script>

</body>

</html>