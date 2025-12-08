<aside class="w-64 bg-white dark:bg-slate-800 border-r border-slate-200 dark:border-slate-700 flex flex-col z-20 flex-shrink-0 h-full shadow-sm transition-colors duration-300">
    <div class="h-16 flex items-center justify-center border-b border-slate-100 dark:border-slate-700 flex-shrink-0">
        <div class="text-xl font-extrabold text-sr-blue dark:text-white tracking-tight flex items-center gap-2">
            <img src="../image/logo.png" alt="Sutan Raya" class="w-8 h-8 object-contain"> Sutan<span class="text-blue-600 dark:text-blue-400">Raya</span>
        </div>
    </div>

    <nav class="flex-1 overflow-y-auto p-3 space-y-1 custom-scrollbar">
        <div class="text-[10px] font-bold text-slate-400 uppercase px-3 mb-2 mt-2 tracking-wider">Utama</div>
        <a href="dashboard.php" class="flex items-center px-3 py-2.5 rounded-lg text-sm transition-colors <?= $currentPage == 'dashboard' ? 'bg-blue-50 dark:bg-slate-700 text-blue-700 dark:text-blue-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700' ?>">
            <i class="bi bi-grid-1x2-fill w-6"></i> Dashboard
        </a>
        <a href="booking_management.php" class="flex items-center px-3 py-2.5 rounded-lg text-sm transition-colors justify-between <?= $currentPage == 'booking_management' ? 'bg-blue-50 dark:bg-slate-700 text-blue-700 dark:text-blue-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700' ?>">
            <div class="flex items-center"><i class="bi bi-journal-text w-6"></i> Kelola Booking</div>
            <span id="pendingValidationCount" class="bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full animate-pulse">0</span>
        </a>

        <div class="text-[10px] font-bold text-slate-400 uppercase px-3 mb-2 mt-6 tracking-wider">Reservasi</div>
        <a href="booking_travel.php" class="flex items-center px-3 py-2.5 rounded-lg text-sm transition-colors <?= $currentPage == 'booking_travel' ? 'bg-blue-50 dark:bg-slate-700 text-blue-700 dark:text-blue-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700' ?>">
            <i class="bi bi-car-front-fill w-6"></i> Travel & Carter
        </a>
        <a href="booking_bus.php" class="flex items-center px-3 py-2.5 rounded-lg text-sm transition-colors <?= $currentPage == 'booking_bus' ? 'bg-blue-50 dark:bg-slate-700 text-blue-700 dark:text-blue-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700' ?>">
            <i class="bi bi-bus-front-fill w-6"></i> Bus Pariwisata
        </a>

        <div class="text-[10px] font-bold text-slate-400 uppercase px-3 mb-2 mt-6 tracking-wider">Operasional</div>
        <a href="dispatcher.php" class="flex items-center px-3 py-2.5 rounded-lg text-sm transition-colors justify-between <?= $currentPage == 'dispatcher' ? 'bg-blue-50 dark:bg-slate-700 text-blue-700 dark:text-blue-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700' ?>">
            <div class="flex items-center"><i class="bi bi-kanban-fill w-6"></i> Dispatcher</div>
            <span id="pendingDispatchCount" class="bg-orange-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">0</span>
        </a>
        <a href="manifest.php" class="flex items-center px-3 py-2.5 rounded-lg text-sm transition-colors <?= $currentPage == 'manifest' ? 'bg-blue-50 dark:bg-slate-700 text-blue-700 dark:text-blue-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700' ?>">
            <i class="bi bi-file-earmark-spreadsheet-fill w-6"></i> Laporan Harian
        </a>
        <a href="reports.php" class="flex items-center px-3 py-2.5 rounded-lg text-sm transition-colors <?= $currentPage == 'reports' ? 'bg-blue-50 dark:bg-slate-700 text-blue-700 dark:text-blue-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700' ?>">
            <i class="bi bi-bar-chart-fill w-6"></i> Statistik & Grafik
        </a>
        
        <div class="text-[10px] font-bold text-slate-400 uppercase px-3 mb-2 mt-6 tracking-wider">Aset</div>
        <a href="assets.php" class="flex items-center px-3 py-2.5 rounded-lg text-sm transition-colors <?= $currentPage == 'assets' ? 'bg-blue-50 dark:bg-slate-700 text-blue-700 dark:text-blue-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700' ?>">
            <i class="bi bi-collection-fill w-6"></i> Armada & Supir
        </a>
        <a href="route_management.php" class="flex items-center px-3 py-2.5 rounded-lg text-sm transition-colors <?= $currentPage == 'route_management' ? 'bg-blue-50 dark:bg-slate-700 text-blue-700 dark:text-blue-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700' ?>">
            <i class="bi bi-map-fill w-6"></i> Kelola Rute
        </a>
    </nav>
</aside>
