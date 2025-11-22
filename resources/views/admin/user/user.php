<?php
$pageTitle = 'User Management';
$activeMenu = 'user';

require '../../../backend/config.php';
include '../template-header.php';
include '../template-sidebar.php';

$sql = "SELECT id, name, email, account_status FROM users WHERE role != 'admin' ORDER BY name ASC";
$result = $conn->query($sql);
?>

<link rel="stylesheet" href="user.css">
<link rel="stylesheet" href="../style.css">
<style>
    .status-dot {
        height: 10px;
        width: 10px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 8px;
    }
    .status-active { background-color: #28a745; }
    .status-inactive { background-color: #dc3545; }
    .delete-icon-link { cursor: pointer; }
</style>

<div class="main-content">
    <div class="user-container">
        <div class="search-bar">
            </div>

        <div class="user-list">
            <?php
            if ($result && $result->num_rows > 0) {
                while($user = $result->fetch_assoc()) {
                    // Tentukan class untuk status dot berdasarkan data dari DB
                    $status_class = ($user["account_status"] === 'active') ? 'status-active' : 'status-inactive';

                    echo '
                    <div class="user-card">
                        <div class="user-info">
                            <div>
                                <div class="user-name">
                                    <span class="status-dot ' . $status_class . '"></span>
                                    ' . htmlspecialchars($user["name"]) . '
                                </div>
                                <div class="user-email">' . htmlspecialchars($user["email"]) . '</div>
                            </div>
                        </div>
                        <a onclick="return confirm(\'Anda yakin ingin menonaktifkan user ini?\')" href="/EfkaWorkshop/backend/proses_deactivate_user.php?id=' . $user['id'] . '">
                            <img src="/EfkaWorkshop/assets/delete.png" alt="Deactivate User" class="delete-icon">
                        </a>
                    </div>';
                }
            } else {
                echo "<p>Tidak ada data user customer.</p>";
            }
            ?>
        </div>
    </div>
</div>

<script src="../script.js"></script>