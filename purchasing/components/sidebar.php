<aside id="sidebar" class="fixed lg:relative inset-y-0 left-0 w-64 bg-slate-900 border-r border-slate-700 flex flex-col z-50 flex-shrink-0 shadow-lg lg:shadow-none transition-transform duration-300 -translate-x-full lg:translate-x-0">
    <div class="h-16 flex items-center justify-between px-4 border-b border-slate-800 flex-shrink-0">
        <div class="text-lg font-bold text-white tracking-tight flex items-center gap-2">
            <img src="../image/logo.png" alt="Sutan Raya" class="w-8 h-8 object-contain brightness-0 invert"> 
            <span class="hidden sm:inline">Purchasing</span>
        </div>
        <button onclick="toggleSidebar()" class="lg:hidden text-slate-400 hover:text-white p-2">
            <i class="bi bi-x-lg text-xl"></i>
        </button>
    </div>

    <nav class="flex-1 overflow-y-auto p-3 space-y-1 custom-scrollbar">
        <div class="text-[10px] font-bold text-slate-500 uppercase px-3 mb-2 mt-2 tracking-wider">Main</div>
        <a href="index.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors <?= $currentPage == 'purchasing' ? 'bg-blue-600 text-white font-bold shadow-lg shadow-blue-900/50' : 'text-slate-400 hover:bg-slate-800 hover:text-white' ?>">
            <i class="bi bi-grid-1x2-fill text-lg"></i> 
            <span>Dashboard</span>
        </a>
        <a href="storage_management.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors <?php echo $currentPage == 'storage_management' ? 'bg-blue-600 text-white font-bold shadow-lg shadow-blue-900/50' : 'text-slate-400 hover:bg-slate-800 hover:text-white'; ?>">
                <i class="bi bi-building-gear text-lg"></i>
                <span>Manajemen Inventaris</span>
            </a>
        <a href="request.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors <?= $currentPage == 'purchasing_request' ? 'bg-blue-600 text-white font-bold shadow-lg shadow-blue-900/50' : 'text-slate-400 hover:bg-slate-800 hover:text-white' ?>">
            <i class="bi bi-cart-plus-fill text-lg"></i> 
            <span>Request Part</span>
        </a>
        <a href="po.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors <?= $currentPage == 'purchasing_po' ? 'bg-blue-600 text-white font-bold shadow-lg shadow-blue-900/50' : 'text-slate-400 hover:bg-slate-800 hover:text-white' ?>">
            <i class="bi bi-file-earmark-text-fill text-lg"></i> 
            <span>Purchase Orders</span>
        </a>

        <div class="text-[10px] font-bold text-slate-500 uppercase px-3 mb-2 mt-6 tracking-wider">Inventory</div>
        <a href="inventory.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors <?= $currentPage == 'purchasing_inventory' ? 'bg-blue-600 text-white font-bold shadow-lg shadow-blue-900/50' : 'text-slate-400 hover:bg-slate-800 hover:text-white' ?>">
            <i class="bi bi-boxes text-lg"></i> 
            <span>Stock & Assets</span>
        </a>
        <a href="suppliers.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors <?= $currentPage == 'purchasing_suppliers' ? 'bg-blue-600 text-white font-bold shadow-lg shadow-blue-900/50' : 'text-slate-400 hover:bg-slate-800 hover:text-white' ?>">
            <i class="bi bi-people-fill text-lg"></i> 
            <span>Suppliers</span>
        </a>
        
        <div class="pt-4 mt-4 border-t border-slate-800">
            <h4 class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2 px-3">Bantuan & Lokasi</h4>
            <a href="guide.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors <?php echo $currentPage == 'guide' ? 'bg-blue-600 text-white font-bold shadow-lg shadow-blue-900/50' : 'text-slate-400 hover:bg-slate-800 hover:text-white'; ?>">
                <i class="bi bi-journal-bookmark-fill text-lg"></i>
                <span>Panduan</span>
            </a>
            
            
        </div>
        <a href="implementation.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors <?= $currentPage == 'purchasing_implementation' ? 'bg-blue-600 text-white font-bold shadow-lg shadow-blue-900/50' : 'text-slate-400 hover:bg-slate-800 hover:text-white' ?>">
            <i class="bi bi-truck text-lg"></i> 
            <span>Deployment</span>
        </a>
        
        <div class="text-[10px] font-bold text-slate-500 uppercase px-3 mb-2 mt-6 tracking-wider">Analysis</div>
        <a href="reports.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors <?= $currentPage == 'purchasing_reports' ? 'bg-blue-600 text-white font-bold shadow-lg shadow-blue-900/50' : 'text-slate-400 hover:bg-slate-800 hover:text-white' ?>">
            <i class="bi bi-graph-up-arrow text-lg"></i> 
            <span>Cost Analysis</span>
        </a>
        
        <div class="mt-8 border-t border-slate-800 pt-4 pb-6">
             <!-- Toggles -->
             <div class="flex items-center justify-between px-3 mb-4">
                <button onclick="toggleDarkMode()" class="flex-1 mr-2 px-3 py-2 rounded-lg bg-slate-800 text-slate-400 hover:text-white hover:bg-slate-700 transition flex items-center justify-center gap-2 text-xs font-bold">
                    <i class="bi bi-moon-stars-fill" id="themeIcon"></i> <span id="themeText">Dark</span>
                </button>
                <button onclick="toggleFullscreen()" class="flex-1 ml-2 px-3 py-2 rounded-lg bg-slate-800 text-slate-400 hover:text-white hover:bg-slate-700 transition flex items-center justify-center gap-2 text-xs font-bold">
                    <i class="bi bi-arrows-fullscreen"></i> Full
                </button>
             </div>

             <a href="../display-v11/dashboard.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-slate-500 hover:bg-slate-800 hover:text-white transition-colors">
                <i class="bi bi-arrow-left-circle text-lg"></i> 
                <span>Back to Helper App</span>
            </a>
            <a href="../display-v11/logout.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-red-500 hover:bg-red-900/20 transition-colors">
                <i class="bi bi-box-arrow-right text-lg"></i> 
                <span>Logout</span>
            </a>
        </div>
    </nav>
</aside>

<!-- Mobile Overlay -->
<div id="sidebarOverlay" onclick="toggleSidebar()" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-40 lg:hidden hidden"></div>

<script>
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    
    sidebar.classList.toggle('-translate-x-full');
    overlay.classList.toggle('hidden');
}

// Close sidebar when clicking a link on mobile
document.querySelectorAll('#sidebar a').forEach(link => {
    link.addEventListener('click', () => {
        if (window.innerWidth < 1024) {
            toggleSidebar();
        }
    });
});

// Dark Mode Toggle
function toggleDarkMode() {
    document.documentElement.classList.toggle('dark');
    const isDark = document.documentElement.classList.contains('dark');
    localStorage.setItem('theme', isDark ? 'dark' : 'light');
    updateThemeIcon();
}

function updateThemeIcon() {
    const isDark = document.documentElement.classList.contains('dark');
    const icon = document.getElementById('themeIcon');
    const text = document.getElementById('themeText');
    if(icon && text) {
        icon.className = isDark ? 'bi bi-sun-fill' : 'bi bi-moon-stars-fill';
        text.innerText = isDark ? 'Light' : 'Dark';
    }
}

// Fullscreen Toggle
function toggleFullscreen() {
    if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen().catch(err => {
            console.log(`Error attempting to enable fullscreen: ${err.message}`);
        });
    } else {
        if (document.exitFullscreen) {
            document.exitFullscreen();
        }
    }
}

// Init Theme
if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
    document.documentElement.classList.add('dark');
} else {
    document.documentElement.classList.remove('dark');
}
updateThemeIcon();
</script>
