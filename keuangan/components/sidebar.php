<!-- Mobile Overlay (shown when sidebar is open) -->
<div id="sidebarOverlay" class="fixed inset-0 bg-black/50 z-30 md:hidden hidden" onclick="toggleMobileSidebar()"></div>

<!-- Sidebar -->
<aside id="mobileSidebar" class="fixed md:relative w-64 bg-white dark:bg-slate-800 border-r border-slate-200 dark:border-slate-700 flex flex-col z-40 md:z-20 flex-shrink-0 h-full shadow-sm transition-all duration-300 -translate-x-full md:translate-x-0">

    <div class="h-16 flex items-center justify-center border-b border-slate-100 dark:border-slate-700 flex-shrink-0">
        <div class="text-xl font-bold text-sr-blue dark:text-white tracking-tight flex items-center gap-2">
            <img src="../image/logo.png" alt="Sutan Raya" class="w-8 h-8 object-contain"> Keuangan
        </div>
    </div>

    <nav class="flex-1 overflow-y-auto p-3 space-y-1 custom-scrollbar">
        <!-- Link Balik ke Operasional -->
        <!-- <a href="../display-v11/booking_management.php" class="flex items-center px-3 py-2.5 rounded-lg text-sm bg-blue-600 text-white font-bold hover:bg-blue-700 transition-colors mb-4 shadow-lg shadow-blue-500/30">
            <i class="bi bi-arrow-left-circle w-6"></i> Kembali ke Operasional
        </a> -->

        <div class="text-[10px] font-bold text-slate-400 uppercase px-3 mb-2 mt-2 tracking-wider">Utama</div>
        <a href="dashboard.php" class="flex items-center px-3 py-2.5 rounded-lg text-sm transition-colors <?= $currentPage == 'dashboard' ? 'bg-blue-50 dark:bg-slate-700 text-blue-700 dark:text-blue-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700' ?>">
            <i class="bi bi-grid-1x2-fill w-6"></i> Dashboard
        </a>

        <div class="text-[10px] font-bold text-slate-400 uppercase px-3 mb-2 mt-6 tracking-wider">Laporan & Statistik</div>
        <a href="manifest.php" class="flex items-center px-3 py-2.5 rounded-lg text-sm transition-colors <?= $currentPage == 'manifest' ? 'bg-blue-50 dark:bg-slate-700 text-blue-700 dark:text-blue-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700' ?>">
            <i class="bi bi-file-earmark-spreadsheet-fill w-6"></i> Laporan Harian
        </a>
        <a href="reports.php" class="flex items-center px-3 py-2.5 rounded-lg text-sm transition-colors <?= $currentPage == 'reports' ? 'bg-blue-50 dark:bg-slate-700 text-blue-700 dark:text-blue-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700' ?>">
            <i class="bi bi-bar-chart-fill w-6"></i> Statistik & Grafik
        </a>
        
        <div class="text-[10px] font-bold text-slate-400 uppercase px-3 mb-2 mt-6 tracking-wider">Manajemen Aset</div>
        <a href="assets.php" class="flex items-center px-3 py-2.5 rounded-lg text-sm transition-colors <?= $currentPage == 'assets' ? 'bg-blue-50 dark:bg-slate-700 text-blue-700 dark:text-blue-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700' ?>">
            <i class="bi bi-collection-fill w-6"></i> Armada & Supir
        </a>
        <a href="route_management.php" class="flex items-center px-3 py-2.5 rounded-lg text-sm transition-colors <?= $currentPage == 'route_management' ? 'bg-blue-50 dark:bg-slate-700 text-blue-700 dark:text-blue-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700' ?>">
            <i class="bi bi-map-fill w-6"></i> Kelola Rute
        </a>

        <div class="text-[10px] font-bold text-slate-400 uppercase px-3 mb-2 mt-6 tracking-wider">Manajemen Biaya</div>
        <a href="#" class="flex items-center px-3 py-2.5 rounded-lg text-sm text-slate-400 dark:text-slate-500 cursor-not-allowed opacity-60">
            <i class="bi bi-cash-coin w-6"></i> Uang Jalan <span class="ml-auto text-[8px] bg-slate-200 dark:bg-slate-700 text-slate-500 px-1.5 py-0.5 rounded font-bold uppercase">Soon</span>
        </a>
        <a href="#" class="flex items-center px-3 py-2.5 rounded-lg text-sm text-slate-400 dark:text-slate-500 cursor-not-allowed opacity-60">
            <i class="bi bi-wallet2 w-6"></i> Gaji Supir <span class="ml-auto text-[8px] bg-slate-200 dark:bg-slate-700 text-slate-500 px-1.5 py-0.5 rounded font-bold uppercase">Soon</span>
        </a>
        <a href="#" class="flex items-center px-3 py-2.5 rounded-lg text-sm text-slate-400 dark:text-slate-500 cursor-not-allowed opacity-60">
            <i class="bi bi-fuel-pump w-6"></i> Bahan Bakar <span class="ml-auto text-[8px] bg-slate-200 dark:bg-slate-700 text-slate-500 px-1.5 py-0.5 rounded font-bold uppercase">Soon</span>
        </a>

        <?php if(isset($currentPage) && $currentPage == 'users'): ?>
        <a href="users.php" class="flex items-center px-3 py-2.5 rounded-lg text-sm transition-colors bg-blue-50 dark:bg-slate-700 text-blue-700 dark:text-blue-300 font-bold">
            <i class="bi bi-people-fill w-6"></i> User / Staff
        </a>
        <?php else: ?>
         <a href="users.php" class="flex items-center px-3 py-2.5 rounded-lg text-sm transition-colors text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700">
            <i class="bi bi-people-fill w-6"></i> User / Staff
        </a>
        <?php endif; ?>
        
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
        <button id="sidebarToggleDark" class="flex items-center px-3 py-2.5 rounded-lg text-sm text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors w-full text-left">
            <div class="w-6 flex justify-center"><i id="sidebarDarkIcon" class="bi bi-moon-stars-fill"></i></div>
            <span id="sidebarDarkText">Mode Gelap</span>
        </button>

        <!-- Logout -->
        <a href="logout.php" class="flex items-center px-3 py-2.5 rounded-lg text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
            <i class="bi bi-box-arrow-right w-6"></i> Logout
        </a>
    </div>

    <!-- Fullscreen Logic (Global) -->
    <script>
        window.toggleAppFullscreen = function() {
            const elem = document.documentElement;
            if (!document.fullscreenElement && !document.webkitFullscreenElement) {
                if (elem.requestFullscreen) { elem.requestFullscreen().catch(err => console.log(err)); } 
                else if (elem.webkitRequestFullscreen) { elem.webkitRequestFullscreen(); }
            } else {
                if (document.exitFullscreen) { document.exitFullscreen(); } 
                else if (document.webkitExitFullscreen) { document.webkitExitFullscreen(); }
            }
        };

        function updateFullscreenUI() {
            const fsIcon = document.getElementById('sidebarFullscreenIcon');
            const fsText = document.getElementById('sidebarFullscreenText');
            if (!fsIcon || !fsText) return;
            const isFullscreen = document.fullscreenElement || document.webkitFullscreenElement;
            if (isFullscreen) { fsIcon.className = 'bi bi-fullscreen-exit'; fsText.textContent = 'Exit Fullscreen'; } 
            else { fsIcon.className = 'bi bi-arrows-fullscreen'; fsText.textContent = 'Fullscreen'; }
        }
        document.addEventListener('fullscreenchange', updateFullscreenUI);
        document.addEventListener('webkitfullscreenchange', updateFullscreenUI);
        updateFullscreenUI();
        
        // Dark Mode Logic for non-Vue pages
        document.getElementById('sidebarToggleDark')?.addEventListener('click', function() {
            if(window.app && window.app.toggleDarkMode) {
                window.app.toggleDarkMode(); 
            } else {
                // Fallback basic JS toggle
                document.documentElement.classList.toggle('dark');
                localStorage.setItem('darkMode', document.documentElement.classList.contains('dark'));
            }
            updateSidebarUI();
        });
            
        function updateSidebarUI() {
            const isDark = document.documentElement.classList.contains('dark');
            const icon = document.getElementById('sidebarDarkIcon');
            const text = document.getElementById('sidebarDarkText');
            if(icon && text) {
                if(isDark) { icon.className = 'bi bi-sun-fill text-yellow-500'; text.textContent = 'Mode Terang'; } 
                else { icon.className = 'bi bi-moon-stars-fill'; text.textContent = 'Mode Gelap'; }
            }
        }
        updateSidebarUI();
        
        // Mobile Sidebar Toggle
        window.toggleMobileSidebar = function() {
            const sidebar = document.getElementById('mobileSidebar');
            const overlay = document.getElementById('sidebarOverlay');
            
            if (sidebar && overlay) {
                const isOpen = !sidebar.classList.contains('-translate-x-full');
                
                if (isOpen) {
                    // Close sidebar
                    sidebar.classList.add('-translate-x-full');
                    overlay.classList.add('hidden');
                } else {
                    // Open sidebar
                    sidebar.classList.remove('-translate-x-full');
                    overlay.classList.remove('hidden');
                }
            }
        };
    </script>
</aside>
