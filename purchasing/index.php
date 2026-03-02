<!DOCTYPE html>
<html lang="id" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchasing - Sutan Raya</title>
    <!-- Use base path for assets -->
    <link rel="icon" type="image/webp" href="../image/logo.webp">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            transition: background-color 0.3s, color 0.3s; 
            overflow: hidden; 
        }
        
        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        .dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #475569; }

        [v-cloak] { display: none; }
        .animate-fade-in { animation: fadeIn 0.4s ease-out forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
    
    <script>
        tailwind.config = { 
            darkMode: 'class',
            theme: { extend: { colors: { 'sr-blue': '#0f172a', 'sr-gold': '#d4af37' } } } 
        }
    </script>
</head>
<body class="h-screen w-screen bg-slate-50 text-slate-800 dark:bg-slate-900 dark:text-slate-100">

    <div id="app" class="flex h-full w-full" v-cloak>
        
        <?php 
        $currentPage = 'purchasing'; 
        include 'components/sidebar.php'; 
        ?>

        <main class="flex-1 flex flex-col min-w-0 bg-slate-50 dark:bg-slate-900 relative h-full overflow-hidden transition-colors duration-300">
            <!-- Header -->
            <header class="h-16 flex items-center justify-between px-6 md:px-8 py-4 z-10 flex-shrink-0 transition-colors duration-300">
                <div class="flex items-center gap-4">
                    <button onclick="toggleSidebar()" class="lg:hidden text-slate-600 dark:text-slate-300 hover:text-blue-600">
                        <i class="bi bi-list text-2xl"></i>
                    </button>
                    <div>
                        <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Overview</h1>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Purchasing & Maintenance Control</p>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <button @click="toggleDarkMode" class="w-10 h-10 rounded-full bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-yellow-400 hover:scale-105 transition-all flex items-center justify-center">
                        <i :class="isDarkMode ? 'bi-sun-fill' : 'bi-moon-stars-fill'"></i>
                    </button>
                </div>
            </header>

            <!-- Content -->
            <div class="flex-1 overflow-y-auto p-6 md:p-8 custom-scrollbar animate-fade-in">
                
                <div class="max-w-6xl mx-auto space-y-8">
                    
                    <!-- 1. GLOBAL SEARCH -->
                    <div class="relative group z-50">
                        <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                            <i class="bi bi-search text-slate-400 text-xl group-focus-within:text-blue-500 transition-colors"></i>
                        </div>
                        <input type="text" 
                            v-model="globalSearch" 
                            @keyup.enter="performSearch"
                            @focus="showDropdown = true"
                            @input="showDropdown = true"
                            placeholder="Cari barang atau aset... (Tekan Enter)" 
                            class="block w-full pl-14 pr-4 py-5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-3xl text-lg font-bold shadow-lg shadow-slate-200/50 dark:shadow-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all dark:text-white"
                        >
                        <div class="absolute inset-y-0 right-4 flex items-center">
                            <span class="text-xs font-bold text-slate-400 bg-slate-100 dark:bg-slate-700 px-2 py-1 rounded-lg">⌘ + K</span>
                        </div>

                        <!-- INSTANT DROPDOWN -->
                        <div v-if="globalSearch && showDropdown && searchResults.length > 0" 
                             class="absolute top-full left-0 right-0 mt-2 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-2xl overflow-hidden animate-fade-in z-50 p-2">
                            <div v-for="item in searchResults" :key="item.id" @click="goToItem(item)" 
                                 class="flex items-center gap-4 p-3 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 cursor-pointer transition">
                                <div class="w-10 h-10 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-600 flex items-center justify-center text-lg shrink-0">
                                    <i class="bi bi-box-seam"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-bold text-slate-800 dark:text-white truncate">{{ item.name }}</h4>
                                    <p class="text-xs text-slate-500 truncate flex items-center gap-2">
                                        <span class="font-mono bg-slate-100 dark:bg-slate-700 px-1 rounded">{{ item.code }}</span>
                                        <span v-if="item.location && item.location !== 'Belum ditempatkan'" class="text-emerald-600 font-bold flex items-center gap-1">
                                            <i class="bi bi-geo-alt-fill text-[10px]"></i> {{ item.location }}
                                        </span>
                                        <span v-else class="text-orange-500 italic text-[10px]">Belum ditempatkan</span>
                                    </p>
                                </div>
                                <div class="text-right shrink-0">
                                    <div class="font-bold text-slate-800 dark:text-white">{{ item.stock }} <span class="text-xs text-slate-500 font-normal">{{ item.unit }}</span></div>
                                </div>
                            </div>
                            <div class="p-2 border-t border-slate-100 dark:border-slate-700 text-center">
                                <button @click="performSearch" class="text-xs font-bold text-blue-600 hover:text-blue-700">Lihat semua hasil</button>
                            </div>
                        </div>
                    </div>

                    <!-- 2. STATS OVERVIEW -->
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
                        
                        <!-- Requests -->
                        <a href="request.php" class="relative group p-6 rounded-3xl bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all overflow-hidden">
                            <div class="flex justify-between items-start mb-4">
                                <div class="w-12 h-12 rounded-2xl bg-blue-50 dark:bg-blue-900/20 text-blue-600 flex items-center justify-center text-xl">
                                    <i class="bi bi-cart-plus-fill"></i>
                                </div>
                                <div v-if="stats.pending_requests > 0" class="px-2.5 py-1 rounded-full bg-red-100 text-red-600 text-xs font-bold">
                                    {{ stats.pending_requests }} Pending
                                </div>
                            </div>
                            <h3 class="text-slate-500 dark:text-slate-400 text-sm font-bold uppercase tracking-wider mb-1">Permintaan</h3>
                            <div class="text-2xl font-extrabold text-slate-800 dark:text-white">{{ stats.pending_requests > 0 ? stats.pending_requests + ' Item' : 'Tidak ada' }}</div>
                        </a>

                        <!-- POs -->
                        <a href="po.php" class="relative group p-6 rounded-3xl bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all overflow-hidden">
                            <div class="flex justify-between items-start mb-4">
                                <div class="w-12 h-12 rounded-2xl bg-purple-50 dark:bg-purple-900/20 text-purple-600 flex items-center justify-center text-xl">
                                    <i class="bi bi-file-earmark-text-fill"></i>
                                </div>
                                <div class="px-2.5 py-1 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-500 text-xs font-bold">
                                    {{ stats.active_pos }} Aktif
                                </div>
                            </div>
                            <h3 class="text-slate-500 dark:text-slate-400 text-sm font-bold uppercase tracking-wider mb-1">Purchase Orders</h3>
                            <div class="text-2xl font-extrabold text-slate-800 dark:text-white">{{ stats.active_pos }} Invoice</div>
                        </a>

                        <!-- Storage / Inventory -->
                        <a href="storage_management.php" class="relative group p-6 rounded-3xl bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all overflow-hidden">
                            <div class="flex justify-between items-start mb-4">
                                <div class="w-12 h-12 rounded-2xl bg-orange-50 dark:bg-orange-900/20 text-orange-600 flex items-center justify-center text-xl">
                                    <i class="bi bi-box-seam-fill"></i>
                                </div>
                                <div class="px-2.5 py-1 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-500 text-xs font-bold">
                                    {{ stats.total_items }} Item
                                </div>
                            </div>
                            <h3 class="text-slate-500 dark:text-slate-400 text-sm font-bold uppercase tracking-wider mb-1">Storage Space</h3>
                            <div class="text-2xl font-extrabold text-slate-800 dark:text-white">{{ stats.total_racks }} Rak/Lemari</div>
                        </a>

                        <!-- Total Assets -->
                        <a href="inventory.php?view=assets" class="relative group p-6 rounded-3xl bg-emerald-500 text-white shadow-lg shadow-emerald-200 dark:shadow-none hover:shadow-xl hover:-translate-y-1 transition-all overflow-hidden">
                            <i class="bi bi-wallet2 absolute -right-6 -bottom-6 text-9xl opacity-10 rotate-12"></i>
                            <div class="flex justify-between items-start mb-4 relative z-10">
                                <div class="w-12 h-12 rounded-2xl bg-white/20 flex items-center justify-center text-xl backdrop-blur-sm">
                                    <i class="bi bi-cash-stack"></i>
                                </div>
                            </div>
                            <h3 class="text-emerald-100 text-sm font-bold uppercase tracking-wider mb-1 relative z-10">Total Aset</h3>
                            <div class="text-2xl font-extrabold relative z-10">{{ formatCurrency(stats.total_asset_value) }}</div>
                        </a>

                    </div>

                    <!-- 3. ALERTS & SHORTCUTS -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        
                        <!-- Low Stock Alerts -->
                        <div class="lg:col-span-2 space-y-4">
                            <div class="flex items-center justify-between">
                                <h2 class="text-lg font-bold text-slate-800 dark:text-white flex items-center gap-2">
                                    <i class="bi bi-exclamation-circle-fill text-red-500"></i> Perlu Perhatian
                                </h2>
                                <span v-if="stats.low_stock_count > 0" class="px-3 py-1 bg-red-50 text-red-600 rounded-full text-xs font-bold">{{ stats.low_stock_count }} Item menipis</span>
                            </div>
                            
                            <!-- Alert List -->
                            <div v-if="stats.low_stock_items && stats.low_stock_items.length > 0" class="grid gap-3">
                                <div v-for="item in stats.low_stock_items" :key="item.id" class="flex items-center justify-between p-4 bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-full bg-red-50 dark:bg-red-900/20 text-red-500 flex items-center justify-center text-lg">
                                            <i class="bi bi-capsule"></i>
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-slate-800 dark:text-white">{{ item.name }}</h4>
                                            <p class="text-xs text-red-500 font-bold">Sisa Stok: {{ item.stock }} {{ item.unit }} <span class="text-slate-400 font-normal">(Min: {{ item.min_stock }})</span></p>
                                        </div>
                                    </div>
                                    <a href="request.php" class="px-4 py-2 bg-slate-50 dark:bg-slate-700 text-slate-600 dark:text-slate-300 text-xs font-bold rounded-lg hover:bg-slate-200 dark:hover:bg-slate-600 transition">Order</a>
                                </div>
                            </div>
                            <div v-else class="p-8 bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 text-center">
                                <i class="bi bi-check-circle-fill text-4xl text-green-500 mb-2 inline-block"></i>
                                <p class="font-bold text-slate-800 dark:text-white">Aman Terkendali</p>
                                <p class="text-sm text-slate-500">Stok barang aman, tidak ada yang dibawah batas minimum.</p>
                            </div>
                        </div>

                        <!-- Quick Links -->
                        <div class="space-y-4">
                            <h2 class="text-lg font-bold text-slate-800 dark:text-white">Aksi Cepat</h2>
                            <div class="grid grid-cols-1 gap-3">
                                <a href="receiving.php" class="p-4 bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm hover:shadow-md transition flex items-center gap-4 group">
                                    <div class="w-10 h-10 rounded-xl bg-green-50 text-green-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                                        <i class="bi bi-box-arrow-in-down"></i>
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-800 dark:text-white">Terima Barang</div>
                                        <div class="text-xs text-slate-500">Catat barang masuk</div>
                                    </div>
                                </a>
                                <a href="implementation.php" class="p-4 bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm hover:shadow-md transition flex items-center gap-4 group">
                                    <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                                        <i class="bi bi-box-arrow-right"></i>
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-800 dark:text-white">Pakai Barang</div>
                                        <div class="text-xs text-slate-500">Implementasi ke armada</div>
                                    </div>
                                </a>
                                <a href="suppliers.php" class="p-4 bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm hover:shadow-md transition flex items-center gap-4 group">
                                    <div class="w-10 h-10 rounded-xl bg-slate-50 text-slate-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                                        <i class="bi bi-people-fill"></i>
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-800 dark:text-white">Supplier</div>
                                        <div class="text-xs text-slate-500">Kelola kontak</div>
                                    </div>
                                </a>
                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </main>

        <?php include 'components/sidebar_right.php'; ?>

    </div>

    <!-- App Logic -->
    <script src="app.js"></script>
    <script>
        const { createApp, ref, computed, onMounted } = Vue;

        createApp({
            setup() {
                const isDarkMode = ref(localStorage.getItem('theme') === 'dark');
                const stats = ref({
                    pending_requests: 0,
                    active_pos: 0,
                    low_stock_count: 0,
                    total_asset_value: 0,
                    total_items: 0,
                    total_racks: 0,
                    low_stock_items: []
                });
                const globalSearch = ref('');
                const allItems = ref([]);
                const showDropdown = ref(false);

                const toggleDarkMode = () => {
                    isDarkMode.value = !isDarkMode.value;
                    if (isDarkMode.value) {
                        document.documentElement.classList.add('dark');
                        localStorage.setItem('theme', 'dark');
                    } else {
                        document.documentElement.classList.remove('dark');
                        localStorage.setItem('theme', 'light');
                    }
                };
                
                const fetchDashboardStats = async () => {
                    try {
                        const res = await fetch('api.php?action=get_dashboard_stats');
                        const data = await res.json();
                        if(data.status === 'success') {
                            stats.value = data.data;
                        }
                    } catch(e) { console.error('Error fetching stats', e); }
                };

                const fetchAllItems = async () => {
                    try {
                        const res = await fetch('api.php?action=get_items');
                        const data = await res.json();
                        if(data.status === 'success') {
                            allItems.value = data.data;
                        }
                    } catch(e) { console.error('Error fetching items', e); }
                };

                const searchResults = computed(() => {
                    if (!globalSearch.value.trim()) return [];
                    const q = globalSearch.value.toLowerCase();
                    return allItems.value.filter(item => 
                        item.name.toLowerCase().includes(q) || 
                        item.code.toLowerCase().includes(q)
                    ).slice(0, 8); // Limit to 8 results for performance/UI
                });

                const formatCurrency = (val) => {
                    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(val || 0);
                };

                const performSearch = () => {
                    if(globalSearch.value.trim()) {
                        window.location.href = `inventory.php?search=${encodeURIComponent(globalSearch.value)}`;
                    }
                };

                const goToItem = (item) => {
                   window.location.href = `inventory.php?search=${encodeURIComponent(item.code)}`; // Search by unique code to find exact itm
                };

                // Click outside to close dropdown
                const closeDropdown = (e) => {
                     // Simple check if click is outside search container (not perfect but works for now)
                     if (!e.target.closest('.group')) {
                         showDropdown.value = false;
                     }
                };


                onMounted(() => {
                    if (isDarkMode.value) document.documentElement.classList.add('dark');
                    fetchDashboardStats();
                    fetchAllItems();
                    
                    window.addEventListener('click', closeDropdown);

                    window.addEventListener('keydown', (e) => {
                        if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
                            e.preventDefault();
                            document.querySelector('input[placeholder*="Cari barang"]').focus();
                        }
                    });
                });

                return {
                    isDarkMode, toggleDarkMode,
                    stats, formatCurrency,
                    globalSearch, performSearch,
                    searchResults, showDropdown, goToItem
                };
            }
        }).mount('#app');
    </script>
</body>
</html>
