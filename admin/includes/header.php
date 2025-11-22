<header class="bg-white shadow-md p-4 flex justify-between items-center z-10">
    <button id="mobile-menu-btn" class="md:hidden text-gray-600 focus:outline-none">
        <i class="fas fa-bars fa-lg"></i>
    </button>

    <h2 class="text-xl font-semibold text-gray-700 ml-4 md:ml-0">
        <?= isset($pageTitle) ? $pageTitle : 'Admin Panel' ?>
    </h2>

    <div class="flex items-center gap-3">
        <div class="text-right hidden sm:block">
            <p class="text-sm font-bold text-gray-700"><?= $_SESSION['name'] ?? 'Admin'; ?></p>
            <p class="text-xs text-gray-500">Administrator</p>
        </div>
        <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center overflow-hidden border border-gray-300">
            <i class="fas fa-user-tie text-gray-500 text-lg"></i>
        </div>
    </div>
</header>