<aside class="w-64 bg-[#0c0a27] text-white hidden md:block flex-shrink-0">
    <div class="p-6 flex items-center justify-center border-b border-gray-700">
        <h1 class="text-2xl font-bold text-[#FFC72C] tracking-wider">OTOWORK</h1>
    </div>
    <nav class="mt-6 px-4">
        <a href="<?= BASE_URL ?>/admin/views/dashboard.php" class="flex items-center py-3 px-4 rounded transition-colors <?= ($activeMenu == 'dashboard') ? 'bg-yellow-500 text-black font-bold' : 'hover:bg-gray-700 text-gray-300'; ?>">
            <i class="fas fa-tachometer-alt w-6"></i>
            <span>Dashboard</span>
        </a>

        <a href="<?= BASE_URL ?>/admin/views/spareparts.php" class="flex items-center py-3 px-4 mt-2 rounded transition-colors <?= ($activeMenu == 'spareparts') ? 'bg-yellow-500 text-black font-bold' : 'hover:bg-gray-700 text-gray-300'; ?>">
            <i class="fas fa-cogs w-6"></i>
            <span>Spareparts</span>
        </a>

        <a href="<?= BASE_URL ?>/admin/views/orders.php" class="flex items-center py-3 px-4 mt-2 rounded transition-colors <?= ($activeMenu == 'orders') ? 'bg-yellow-500 text-black font-bold' : 'hover:bg-gray-700 text-gray-300'; ?>">
            <i class="fas fa-shopping-cart w-6"></i>
            <span>Pesanan Masuk</span>
        </a>

        <a href="<?= BASE_URL ?>/admin/views/bookings.php" class="flex items-center py-3 px-4 mt-2 rounded transition-colors <?= ($activeMenu == 'bookings') ? 'bg-yellow-500 text-black font-bold' : 'hover:bg-gray-700 text-gray-300'; ?>">
            <i class="fas fa-calendar-check w-6"></i>
            <span>Booking Servis</span>
        </a>

        <a href="<?= BASE_URL ?>/admin/views/customers.php" class="flex items-center py-3 px-4 mt-2 rounded transition-colors <?= ($activeMenu == 'customers') ? 'bg-yellow-500 text-black font-bold' : 'hover:bg-gray-700 text-gray-300'; ?>">
            <i class="fas fa-users w-6"></i>
            <span>Data Pelanggan</span>
        </a>

        <div class="border-t border-gray-700 my-4"></div>

        <a href="<?= BASE_URL ?>/logic/auth.php?act=logout" class="flex items-center py-3 px-4 rounded hover:bg-red-600 text-red-300 transition-colors">
            <i class="fas fa-sign-out-alt w-6"></i>
            <span>Logout</span>
        </a>
    </nav>
</aside>