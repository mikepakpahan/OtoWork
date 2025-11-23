<?php
require_once '../../config/database.php';
// Cek Admin logic... (Copy dari dashboard)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "/customer/views/login.php");
    exit;
}

$pageTitle = 'Manajemen Sparepart';
$activeMenu = 'spareparts';

// Ambil Data
$sql = "SELECT * FROM spareparts ORDER BY id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stok Gudang | OtoWork Admin</title>
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
                    <h3 class="text-2xl font-bold text-gray-700">Inventaris Gudang</h3>
                    <button onclick="openModal('add')" class="bg-yellow-400 hover:bg-yellow-500 text-black font-bold py-2 px-4 rounded shadow-lg transform hover:scale-105 transition">
                        <i class="fas fa-plus mr-2"></i> Tambah Barang
                    </button>
                </div>

                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <table class="min-w-full leading-normal">
                        <thead>
                            <tr>
                                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Produk</th>
                                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Harga</th>
                                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Stok</th>
                                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 w-12 h-12">
                                                    <img class="w-full h-full rounded-md object-cover"
                                                        src="<?= BASE_URL ?>/assets/img/spareparts/<?= $row['image_url'] ?>"
                                                        onerror="this.src='https://via.placeholder.com/150?text=No+Img'"
                                                        alt="" />
                                                </div>
                                                <div class="ml-3">
                                                    <p class="text-gray-900 font-bold whitespace-no-wrap"><?= htmlspecialchars($row['part_name']) ?></p>
                                                    <?php if ($row['is_featured']): ?>
                                                        <span class="bg-blue-100 text-blue-800 text-xs px-2 rounded-full">Unggulan</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                            <p class="text-gray-900 whitespace-no-wrap">Rp <?= number_format($row['price'], 0, ',', '.') ?></p>
                                        </td>
                                        <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                            <span class="relative inline-block px-3 py-1 font-semibold <?= $row['stock'] < 5 ? 'text-red-900' : 'text-green-900' ?> leading-tight">
                                                <span aria-hidden class="absolute inset-0 <?= $row['stock'] < 5 ? 'bg-red-200' : 'bg-green-200' ?> opacity-50 rounded-full"></span>
                                                <span class="relative"><?= $row['stock'] ?> pcs</span>
                                            </span>
                                        </td>
                                        <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-center">
                                            <a href="<?= BASE_URL ?>/logic/sparepart_master.php?act=toggle&id=<?= $row['id'] ?>" class="text-xs font-bold px-2 py-1 rounded <?= $row['is_active'] ? 'bg-green-500 text-white' : 'bg-gray-400 text-white' ?>">
                                                <?= $row['is_active'] ? 'AKTIF' : 'NON-AKTIF' ?>
                                            </a>
                                        </td>
                                        <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-center">
                                            <button onclick='openModal("edit", <?= json_encode($row) ?>)' class="text-blue-600 hover:text-blue-900 mr-3">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button onclick="confirmDelete(<?= $row['id'] ?>)" class="text-red-600 hover:text-red-900">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4">Belum ada barang di gudang.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </main>
        </div>
    </div>

    <div id="itemModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 md:w-1/2 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modalTitle">Tambah Barang</h3>

                <form id="itemForm" action="<?= BASE_URL ?>/logic/sparepart_master.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="act" id="formAct" value="add">
                    <input type="hidden" name="id" id="itemId">

                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Nama Sparepart</label>
                            <input type="text" name="part_name" id="part_name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2">Harga (Rp)</label>
                                <input type="number" name="price" id="price" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" required>
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2">Stok Awal</label>
                                <input type="number" name="stock" id="stock" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" required>
                            </div>
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Deskripsi</label>
                            <textarea name="description" id="description" rows="3" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700"></textarea>
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Gambar Produk</label>
                            <input type="file" name="image" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-yellow-50 text-yellow-700 hover:file:bg-yellow-100">
                            <p class="text-xs text-gray-500 mt-1">*Kosongkan jika tidak ingin mengganti gambar (saat edit).</p>
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox" name="is_featured" id="is_featured" value="1" class="w-4 h-4 text-yellow-600 rounded">
                            <label for="is_featured" class="ml-2 text-sm text-gray-900">Jadikan Produk Unggulan (Featured)</label>
                        </div>
                    </div>

                    <div class="items-center px-4 py-3 mt-4 text-right">
                        <button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded mr-2 hover:bg-gray-300">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-[#FFC72C] text-black font-bold rounded hover:bg-yellow-500">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const modal = document.getElementById('itemModal');
        const formAct = document.getElementById('formAct');
        const itemId = document.getElementById('itemId');
        const modalTitle = document.getElementById('modalTitle');

        // Buka Modal (Bisa Mode Add atau Edit)
        function openModal(mode, data = null) {
            modal.classList.remove('hidden');
            if (mode === 'edit' && data) {
                modalTitle.innerText = 'Edit Barang';
                formAct.value = 'update';
                itemId.value = data.id;

                // Isi form otomatis pake ID Javascript
                document.getElementById('part_name').value = data.part_name;
                document.getElementById('price').value = data.price;
                document.getElementById('stock').value = data.stock;
                document.getElementById('description').value = data.description;
                document.getElementById('is_featured').checked = (data.is_featured == 1);
            } else {
                // Mode Add: Reset form
                modalTitle.innerText = 'Tambah Barang Baru';
                formAct.value = 'add';
                document.getElementById('itemForm').reset();
            }
        }

        function closeModal() {
            modal.classList.add('hidden');
        }

        // Konfirmasi Hapus pake SweetAlert
        function confirmDelete(id) {
            Swal.fire({
                title: 'Yakin mau dihapus?',
                text: "Data yang dihapus gak bisa balik lagi loh!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `<?= BASE_URL ?>/logic/sparepart_master.php?act=delete&id=${id}`;
                }
            })
        }

        // Alert Sukses/Gagal dari PHP Session (Copas yang dari login.php)
        <?php if (isset($_SESSION['alert'])): ?>
            Swal.fire({
                icon: '<?= $_SESSION['alert']['type'] ?>',
                title: '<?= $_SESSION['alert']['type'] == 'success' ? 'Berhasil!' : 'Gagal' ?>',
                text: '<?= $_SESSION['alert']['message'] ?>',
                confirmButtonColor: '#FFC72C'
            });
        <?php unset($_SESSION['alert']);
        endif; ?>
    </script>

</body>

</html>