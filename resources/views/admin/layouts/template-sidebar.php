<?php
if (!isset($activeMenu)) {
    $activeMenu = '';
}
?>

<link rel="stylesheet" href="style.css">

<div class="main-body">
    <aside class="sidebar">
        <nav class="sidebar-menu">
            <a href="../dashboard/dashboard.php" class="sidebar-link <?php if ($activeMenu == 'dashboard') echo 'active'; ?>">
                <img src="/EfkaWorkshop/assets/icons/dashboard.png" alt="Dashboard icon">
                <span>Dashboard</span>
            </a>
            <a href="../spareparts/manage-sparepart.php" class="sidebar-link <?php if ($activeMenu == 'sparepart') echo 'active'; ?>">
                <img src="/EfkaWorkshop/assets/icons/manage-spareparts.png" alt="Management Sparepart icon">
                <span>Management Sparepart</span>
            </a>
            <a href="../service/manage-service.php" class="sidebar-link <?php if ($activeMenu == 'service') echo 'active'; ?>">
                <img src="/EfkaWorkshop/assets/icons/manage-services.png" alt="Management Service icon">
                <span>Management Service</span>
            </a>
            <a href="../spareparts/sparepart-order.php    " class="sidebar-link <?php if ($activeMenu == 'order') echo 'active'; ?>">
                <img src="/EfkaWorkshop/assets/icons/sparepart-order.png" alt="Sparepart Order icon">
                <span>Sparepart Order</span>
            </a>
            <a href="../service/pending-service.php" class="sidebar-link <?php if ($activeMenu == 'pending') echo 'active'; ?>">
                <img src="/EfkaWorkshop/assets/icons/pending-service.png" alt="Pending Service icon">
                <span>Pending Service</span>
            </a>
            <a href="../queue/queue.php" class="sidebar-link <?php if ($activeMenu == 'queue') echo 'active'; ?>">
                <img src="/EfkaWorkshop/assets/icons/queue.png" alt="Queue icon">
                <span>Queue</span>
            </a>
            <a href="../user/user.php" class="sidebar-link <?php if ($activeMenu == 'user') echo 'active'; ?>">
                <img src="/EfkaWorkshop/assets/icons/user.png" alt="User icon">
                <span>User</span>
            </a>
            <a href="../history/history.php" class="sidebar-link <?php if ($activeMenu == 'history') echo 'active'; ?>">
                <i class="fas fa-history"></i>
                <span>History Orders</span>
            </a>
            <a href="../feedback/feedback.php" class="sidebar-link <?php if ($activeMenu == 'feedback') echo 'active'; ?>">
                <i class="fas fa-comment-dots"></i>
                <span>Feedback Customer</span>
            </a>
            <a href="/EfkaWorkshop/backend/logout.php" class="sidebar-link sidebar-link-logout">
                <i class="fas fa-sign-out-alt" style="width: 24px; text-align: center;"></i> 
                <span>Logout</span>
            </a>
        </nav>
    </aside>