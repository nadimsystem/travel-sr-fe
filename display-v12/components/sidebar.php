<aside id="mainSidebar" class="w-64 bg-white dark:bg-slate-800 border-r border-slate-200 dark:border-slate-700 flex flex-col fixed inset-y-0 left-0 z-50 md:static md:inset-auto md:translate-x-0 transform -translate-x-full transition-transform duration-300 h-full shadow-sm flex-shrink-0">
    <div class="h-16 flex items-center justify-center border-b border-slate-100 dark:border-slate-700 flex-shrink-0">
        <div class="text-xl font-bold text-sr-blue dark:text-white tracking-tight flex items-center gap-2">
            <img src="../image/logo.png" alt="Sutan Raya" class="w-8 h-8 object-contain"> SutanRaya
        </div>
    </div>

    <nav class="flex-1 overflow-y-auto p-3 space-y-1 custom-scrollbar">
        <div class="text-[10px] font-bold text-slate-400 uppercase px-3 mb-2 mt-2 tracking-wider">Operasional</div>
        
        <a href="booking_mandiri" class="flex items-center px-3 py-2.5 rounded-lg text-sm transition-colors mb-1 <?= $currentPage == 'booking_mandiri' ? 'bg-blue-50 dark:bg-slate-700 text-blue-700 dark:text-blue-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700' ?>">
            <i class="bi bi-hourglass-split w-6"></i> Booking Mandiri
        </a>
        
        <a href="view_bookings" class="flex items-center px-3 py-2.5 rounded-lg text-sm transition-colors mb-1 <?= $currentPage == 'view_bookings' ? 'bg-blue-50 dark:bg-slate-700 text-blue-700 dark:text-blue-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700' ?>">
            <i class="bi bi-grid-3x3-gap-fill w-6"></i> Lihat Booking
        </a>

        <a href="booking_management" class="flex items-center px-3 py-2.5 rounded-lg text-sm transition-colors justify-between <?= $currentPage == 'booking_management' ? 'bg-blue-50 dark:bg-slate-700 text-blue-700 dark:text-blue-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700' ?>">
            <div class="flex items-center"><i class="bi bi-journal-text w-6"></i> Riwayat Booking</div>
        </a>

        <div class="text-[10px] font-bold text-slate-400 uppercase px-3 mb-2 mt-6 tracking-wider">Reservasi</div>
        <a href="booking_travel" class="flex items-center px-3 py-2.5 rounded-lg text-sm transition-colors <?= $currentPage == 'booking_travel' ? 'bg-blue-50 dark:bg-slate-700 text-blue-700 dark:text-blue-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700' ?>">
            <i class="bi bi-car-front-fill w-6"></i> Travel & Carter
        </a>
        <a href="booking_bus" class="flex items-center px-3 py-2.5 rounded-lg text-sm transition-colors <?= $currentPage == 'booking_bus' ? 'bg-blue-50 dark:bg-slate-700 text-blue-700 dark:text-blue-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700' ?>">
            <i class="bi bi-bus-front-fill w-6"></i> Bus Pariwisata
        </a>
        <a href="package_shipping" class="flex items-center px-3 py-2.5 rounded-lg text-sm transition-colors <?= $currentPage == 'package_shipping' ? 'bg-blue-50 dark:bg-slate-700 text-blue-700 dark:text-blue-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700' ?>">
            <i class="bi bi-box-seam-fill w-6"></i> Kirim Paket
        </a>

        <div class="text-[10px] font-bold text-slate-400 uppercase px-3 mb-2 mt-6 tracking-wider">Dispathcer</div>
        <a href="dispatcher" class="flex items-center px-3 py-2.5 rounded-lg text-sm transition-colors justify-between <?= $currentPage == 'dispatcher' ? 'bg-blue-50 dark:bg-slate-700 text-blue-700 dark:text-blue-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700' ?>">
            <div class="flex items-center"><i class="bi bi-kanban-fill w-6"></i> Keberangkatan</div>
            <span id="pendingDispatchCount" class="bg-orange-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">0</span>
        </a>
        <a href="schedule" class="flex items-center px-3 py-2.5 rounded-lg text-sm transition-colors <?= $currentPage == 'schedule' ? 'bg-blue-50 dark:bg-slate-700 text-blue-700 dark:text-blue-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700' ?>">
            <i class="bi bi-calendar-week-fill w-6"></i> Jadwal
        </a>
        <a href="penjadwalan" class="flex items-center px-3 py-2.5 rounded-lg text-sm transition-colors <?= $currentPage == 'penjadwalan' ? 'bg-blue-50 dark:bg-slate-700 text-blue-700 dark:text-blue-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700' ?>">
            <i class="bi bi-calendar-check-fill w-6"></i> Penjadwalan
        </a>
        <a href="penagihan" class="flex items-center px-3 py-2.5 rounded-lg text-sm transition-colors <?= $currentPage == 'penagihan' ? 'bg-blue-50 dark:bg-slate-700 text-blue-700 dark:text-blue-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700' ?>">
            <i class="bi bi-receipt-cutoff w-6"></i> Penagihan
        </a>

        <!-- 
        <div class="text-[10px] font-bold text-slate-400 uppercase px-3 mb-2 mt-6 tracking-wider">Admin</div>
        <a href="../keuangan/dashboard.php" class="flex items-center px-3 py-2.5 rounded-lg text-sm text-slate-600 dark:text-slate-400 hover:bg-blue-50 dark:hover:bg-slate-700 transition-colors">
            <i class="bi bi-shield-lock-fill w-6"></i> Menu Admin & Keuangan
        </a>
        -->
    </nav>

    <!-- Bottom Section (Pinned) -->
    <div class="p-3 border-t border-slate-100 dark:border-slate-700 bg-white dark:bg-slate-800 space-y-1 z-20">
        <div class="text-[10px] font-bold text-slate-400 uppercase px-3 mb-2 tracking-wider">Pengaturan</div>
        
        <!-- Fullscreen Toggle -->
        <button onclick="toggleAppFullscreen()" id="sidebarToggleFullscreen" class="flex items-center px-3 py-2.5 rounded-lg text-sm text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors w-full text-left">
            <div class="w-6 flex justify-center"><i id="sidebarFullscreenIcon" class="bi bi-arrows-fullscreen"></i></div>
            <span id="sidebarFullscreenText">Fullscreen</span>
        </button>

        <!-- Dark Mode Toggle -->
        <?php if (isset($currentPage) && $currentPage == 'dashboard'): ?>
        <button id="sidebarToggleDark" class="flex items-center px-3 py-2.5 rounded-lg text-sm text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors w-full text-left">
            <div class="w-6 flex justify-center"><i id="sidebarDarkIcon" class="bi bi-moon-stars-fill"></i></div>
            <span id="sidebarDarkText">Mode Gelap</span>
        </button>
        <script>
            // Dark Mode Logic
            document.getElementById('sidebarToggleDark').addEventListener('click', function() {
                if(typeof toggleDarkMode === 'function') {
                    toggleDarkMode(); 
                    updateSidebarUI();
                }
            });
            
            function updateSidebarUI() {
                const isDark = document.documentElement.classList.contains('dark');
                const icon = document.getElementById('sidebarDarkIcon');
                const text = document.getElementById('sidebarDarkText');
                if(isDark) {
                    icon.className = 'bi bi-sun-fill text-yellow-500';
                    text.textContent = 'Mode Terang';
                } else {
                    icon.className = 'bi bi-moon-stars-fill';
                    text.textContent = 'Mode Gelap';
                }
            }
            updateSidebarUI();
        </script>
        <?php else: ?>
        <button @click="toggleDarkMode" class="flex items-center px-3 py-2.5 rounded-lg text-sm text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors w-full text-left">
            <div class="w-6 flex justify-center"><i :class="isDarkMode ? 'bi-sun-fill text-yellow-500' : 'bi-moon-stars-fill'"></i></div>
            <span>{{ isDarkMode ? 'Mode Terang' : 'Mode Gelap' }}</span>
        </button>
        <?php endif; ?>

        <!-- Logout -->
        <a href="logout.php" class="flex items-center px-3 py-2.5 rounded-lg text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
            <i class="bi bi-box-arrow-right w-6"></i> Logout
        </a>
    </div>

    <!-- Fullscreen Logic (Global) -->
    <script>
        // Define global function to survive framework re-renders
        window.toggleAppFullscreen = function() {
            const elem = document.documentElement;
            if (!document.fullscreenElement && !document.webkitFullscreenElement) {
                if (elem.requestFullscreen) {
                    elem.requestFullscreen().catch(err => console.log(err));
                } else if (elem.webkitRequestFullscreen) {
                    elem.webkitRequestFullscreen();
                }
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                } else if (document.webkitExitFullscreen) {
                    document.webkitExitFullscreen();
                }
            }
        };

        // Update UI on change
        function updateFullscreenUI() {
            const fsIcon = document.getElementById('sidebarFullscreenIcon');
            const fsText = document.getElementById('sidebarFullscreenText');
            
            // Check if elements exist (might be removed by page transitions)
            if (!fsIcon || !fsText) return;

            const isFullscreen = document.fullscreenElement || document.webkitFullscreenElement;

            if (isFullscreen) {
                fsIcon.className = 'bi bi-fullscreen-exit';
                fsText.textContent = 'Exit Fullscreen';
            } else {
                fsIcon.className = 'bi bi-arrows-fullscreen';
                fsText.textContent = 'Fullscreen';
            }
        }

        document.addEventListener('fullscreenchange', updateFullscreenUI);
        document.addEventListener('webkitfullscreenchange', updateFullscreenUI);
        
        // Initial check
        updateFullscreenUI();
    </script>

</aside>

<!-- Mobile Backdrop -->
<div id="sidebarBackdrop" onclick="toggleSidebar()" class="fixed inset-0 bg-black/50 z-40 hidden md:hidden backdrop-blur-sm transition-opacity"></div>

<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('mainSidebar');
        const backdrop = document.getElementById('sidebarBackdrop');
        if (sidebar && backdrop) {
            sidebar.classList.toggle('-translate-x-full');
            backdrop.classList.toggle('hidden');
        }
    }
</script>
