<?php
require_once '../../config/database.php';

// Cek Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "/customer/views/login.php");
    exit;
}

$pageTitle = 'Data Pelanggan';
$activeMenu = 'customers';

// Ambil semua user yang role-nya CUSTOMER
// Kita join sama tabel service_bookings hitung berapa kali dia servis (Opsional, buat keren-kerenan)
$sql = "SELECT u.*, 
        (SELECT COUNT(*) FROM orders WHERE user_id = u.id) as total_order,
        (SELECT COUNT(*) FROM service_bookings WHERE user_id = u.id) as total_servis
        FROM users u 
        WHERE u.role = 'customer' 
        ORDER BY u.name ASC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pelanggan | OtoWork Admin</title>
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

                <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-700">Pelanggan Setia</h3>
                        <p class="text-gray-500 text-sm">Kelola akses dan data customer OtoWork.</p>
                    </div>

                    <div class="relative w-full md:w-64">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                            <i class="fas fa-search text-gray-400"></i>
                        </span>
                        <input type="text" id="searchInput" onkeyup="searchTable()" class="w-full py-2 pl-10 pr-4 bg-white border border-gray-300 rounded-lg focus:outline-none focus:border-yellow-500" placeholder="Cari nama / email...">
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <table class="min-w-full leading-normal" id="customerTable">
                        <thead>
                            <tr class="bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <th class="px-5 py-3">Nama Pelanggan</th>
                                <th class="px-5 py-3">Kontak</th>
                                <th class="px-5 py-3 text-center">Aktivitas</th>
                                <th class="px-5 py-3 text-center">Status Akun</th>
                                <th class="px-5 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-5 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 w-10 h-10">
                                                    <div class="w-full h-full rounded-full bg-yellow-100 text-yellow-600 flex items-center justify-center font-bold text-lg">
                                                        <?= strtoupper(substr($row['name'], 0, 1)) ?>
                                                    </div>
                                                </div>
                                                <div class="ml-3">
                                                    <p class="text-gray-900 font-bold"><?= htmlspecialchars($row['name']) ?></p>
                                                    <p class="text-gray-400 text-xs">ID: #CUST-<?= $row['id'] ?></p>
                                                </div>
                                            </div>
                                        </td>

                                        <td class="px-5 py-4">
                                            <div class="text-sm text-gray-900"><i class="fas fa-envelope w-4 text-gray-400"></i> <?= htmlspecialchars($row['email']) ?></div>
                                            <div class="text-sm text-gray-500 mt-1"><i class="fas fa-phone w-4 text-gray-400"></i> <?= htmlspecialchars($row['phone_number'] ?? '-') ?></div>
                                        </td>

                                        <td class="px-5 py-4 text-center">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mx-1" title="Total Order">
                                                <i class="fas fa-shopping-cart mr-1"></i> <?= $row['total_order'] ?>
                                            </span>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 mx-1" title="Total Servis">
                                                <i class="fas fa-wrench mr-1"></i> <?= $row['total_servis'] ?>
                                            </span>
                                        </td>

                                        <td class="px-5 py-4 text-center">
                                            <?php if ($row['account_status'] == 'active'): ?>
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    Active
                                                </span>
                                            <?php else: ?>
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                    Banned
                                                </span>
                                            <?php endif; ?>
                                        </td>

                                        <td class="px-5 py-4 text-center text-sm font-medium">
                                            <button onclick="toggleUser(<?= $row['id'] ?>, '<?= $row['account_status'] ?>')"
                                                class="text-indigo-600 hover:text-indigo-900 mx-2"
                                                title="<?= ($row['account_status'] == 'active') ? 'Bekukan Akun' : 'Aktifkan Akun' ?>">
                                                <i class="fas <?= ($row['account_status'] == 'active') ? 'fa-ban text-red-400' : 'fa-check-circle text-green-500' ?> text-lg"></i>
                                            </button>

                                            <button onclick="deleteUser(<?= $row['id'] ?>)" class="text-gray-400 hover:text-red-600 mx-2" title="Hapus Permanen">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="px-5 py-5 text-center text-gray-500">Belum ada pelanggan terdaftar.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </main>
        </div>
    </div>

    <script>
        // 1. Fitur Search Table (JavaScript Murni)
        function searchTable() {
            var input, filter, table, tr, td, i, txtValue;
            input = document.getElementById("searchInput");
            filter = input.value.toUpperCase();
            table = document.getElementById("customerTable");
            tr = table.getElementsByTagName("tr");

            // Loop semua baris tabel
            for (i = 0; i < tr.length; i++) {
                // Cek kolom Nama (index 0) dan Email (index 1)
                tdName = tr[i].getElementsByTagName("td")[0];
                tdEmail = tr[i].getElementsByTagName("td")[1];

                if (tdName || tdEmail) {
                    txtValueName = tdName.textContent || tdName.innerText;
                    txtValueEmail = tdEmail.textContent || tdEmail.innerText;

                    if (txtValueName.toUpperCase().indexOf(filter) > -1 || txtValueEmail.toUpperCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                    } else {
                        tr[i].style.display = "none";
                    }
                }
            }
        }

        // 2. Logic Toggle Status
        function toggleUser(id, currentStatus) {
            let actionText = (currentStatus === 'active') ? 'Bekukan (Ban)' : 'Aktifkan Kembali';
            let btnColor = (currentStatus === 'active') ? '#EF4444' : '#10B981';

            Swal.fire({
                title: actionText + ' User?',
                text: "Status login user akan berubah.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: btnColor,
                confirmButtonText: 'Ya, Lakukan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `<?= BASE_URL ?>/logic/customer_master.php?act=toggle&id=${id}`;
                }
            });
        }

        // 3. Logic Hapus User
        function deleteUser(id) {
            Swal.fire({
                title: 'Hapus Permanen?',
                text: "Semua riwayat pesanan & servis user ini mungkin akan error jika dihapus. Sarankan Bekukan saja.",
                icon: 'error', // Icon merah seram
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Hapus Aja',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `<?= BASE_URL ?>/logic/customer_master.php?act=delete&id=${id}`;
                }
            });
        }

        // Alert Session
        <?php if (isset($_SESSION['alert'])): ?>
            Swal.fire({
                icon: '<?= $_SESSION['alert']['type'] ?>',
                title: 'Info',
                text: '<?= $_SESSION['alert']['message'] ?>',
                confirmButtonColor: '#FFC72C'
            });
        <?php unset($_SESSION['alert']);
        endif; ?>
    </script>

</body>

</html>