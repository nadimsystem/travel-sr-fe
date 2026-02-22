<?php require_once 'auth_check_fe.php'; ?>
<!DOCTYPE html>
<html lang="id" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sutan Raya - Lihat Booking (Seat Map)</title>
    <link rel="icon" type="image/webp" href="../image/logo.webp">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="js/loading-optimizer.js?v=2.0"></script>
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        .dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #475569; }
        
        .seat {
            transition: all 0.2s ease;
        }
        
        /* Custom Heavy Loader */
        #customLoader {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background-color: white;
            z-index: 10000; /* Higher than generic loader */
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            transition: opacity 0.5s ease-out;
        }
        .dark #customLoader { background-color: #0f172a; }
    </style>
    <script>
        tailwind.config = { darkMode: 'class', theme: { extend: { colors: { 'sr-blue': '#0f172a', 'sr-gold': '#d4af37' } } } }
    </script>
</head>
<body class="h-screen w-screen bg-slate-50 text-slate-800 dark:bg-slate-900 dark:text-slate-100 overflow-hidden">
    <!-- Loading Overlay handled by loading-optimizer.js -->
    
    <!-- Custom Heavy Loading Screen -->
    <div id="customLoader">
        <div class="relative w-24 h-24 mb-6">
            <div class="absolute inset-0 border-4 border-slate-100 dark:border-slate-800 rounded-full"></div>
            <div class="absolute inset-0 border-4 border-t-blue-600 border-r-transparent border-b-transparent border-l-transparent rounded-full animate-spin"></div>
            <div class="absolute inset-4 bg-white dark:bg-slate-900 rounded-full shadow-lg flex items-center justify-center">
                <i class="bi bi-cpu text-3xl text-blue-600 animate-pulse"></i>
            </div>
        </div>
        <h2 class="text-2xl font-extrabold text-slate-800 dark:text-white mb-2 animate-bounce">Tunggu sejenak, loading data super berat</h2>
        <p class="text-sm font-medium text-slate-500 dark:text-slate-400 max-w-md bg-slate-100 dark:bg-slate-800 py-2 px-4 rounded-full border border-slate-200 dark:border-slate-700">
            <i class="bi bi-cup-hot-fill text-orange-500 mr-2"></i>
            Silahkan siapkan kopi atau minuman favorit, sistem akan membaca seluruh data dan merangkumnya otomatis
        </p>
    </div>

    <div id="app" class="flex h-full w-full opacity-0 transition-opacity duration-300">
        <?php $currentPage = 'view_bookings'; include 'components/sidebar.php'; ?>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col h-full overflow-hidden relative">
            <header class="h-16 bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between px-6 z-10 flex-shrink-0">
                <div class="flex items-center gap-4">
                    <button class="md:hidden text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200" onclick="toggleSidebar()">
                        <i class="bi bi-list text-2xl"></i>
                    </button>
                    <h1 class="text-[10px] font-bold text-slate-800 dark:text-white flex items-center gap-2">
                        <i class="bi bi-grid-3x3-gap-fill text-blue-600"></i> Lihat Seat Map
                    </h1>
                </div>
                
                <div class="flex items-center gap-3">


                    <div class="relative">
                        <input type="date" id="dateInput" class="pl-10 pr-4 py-2 bg-slate-100 dark:bg-slate-700 border-none rounded-lg text-sm font-bold text-slate-700 dark:text-slate-200 outline-none focus:ring-2 focus:ring-blue-500 transition-all shadow-sm">
                        <i class="bi bi-calendar-event absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    </div>
                    <button class="p-2 text-slate-400 hover:text-blue-600 transition-colors" onclick="fetchDailyData()"><i class="bi bi-arrow-clockwise"></i></button>
                </div>
            </header>

            <div class="flex-1 overflow-y-auto p-6 custom-scrollbar bg-slate-50 dark:bg-slate-900 relative">
                
                <!-- Date Label & Filter -->
                <div class="mb-6 pt-2 px-1 flex flex-col md:flex-row md:items-center justify-between gap-4">
                     <div>
                        <h2 id="currentDateLabel" class="text-xl font-bold text-slate-800 dark:text-white leading-tight">Loading data...</h2>
                        <div class="flex items-center gap-2 mt-2">
                            <button id="filterAll" class="px-3 py-1 rounded-full text-xs font-bold bg-blue-600 text-white transition-all shadow-sm">Semua</button>
                            <button id="filterPending" class="px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-500 hover:bg-slate-200 transition-all">
                                Menunggu Konfirmasi <span id="pendingCountBadge" class="hidden ml-1 px-1.5 py-0.5 rounded-full bg-red-500 text-white text-[9px]">0</span>
                            </button>
                        </div>
                     </div>
                     
                     <!-- Route Filter -->
                     <div class="relative w-full md:w-72">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="bi bi-funnel-fill text-slate-400"></i>
                        </div>
                        <select id="routeSelect" class="w-full pl-10 pr-10 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-bold text-slate-700 dark:text-slate-200 outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all shadow-sm appearance-none cursor-pointer">
                            <option value="all">Tampilkan Semua Rute</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <i class="bi bi-chevron-down text-slate-400 text-xs"></i>
                        </div>
                     </div>
                </div>

                <!-- Legend -->
                <div class="flex flex-wrap gap-4 mb-6 bg-white dark:bg-slate-800 p-4 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm sticky top-0 z-30">
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 rounded bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600"></div>
                        <span class="text-xs font-bold text-slate-500">Kosong</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 rounded bg-slate-900 dark:bg-black border border-slate-800 dark:border-slate-700"></div>
                        <span class="text-xs font-bold text-slate-500">Terisi</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 rounded bg-orange-500 border border-orange-600"></div>
                        <span class="text-xs font-bold text-slate-500">Menunggu Validasi</span>
                    </div>
                </div>

                <!-- Content Container -->
                <div id="routesContainer" class="space-y-4">
                    <!-- Dynamic Content Will Be Injected Here -->
                    
                    <!-- Loading Skeleton (Initial State) -->
                    <div class="animate-pulse space-y-8">
                        <?php for($i=0; $i<3; $i++): ?>
                        <div>
                            <div class="h-6 w-48 bg-slate-200 dark:bg-slate-700 rounded mb-4"></div>
                            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                                <div class="h-48 bg-slate-200 dark:bg-slate-700 rounded-xl"></div>
                                <div class="h-48 bg-slate-200 dark:bg-slate-700 rounded-xl"></div>
                            </div>
                        </div>
                        <?php endfor; ?>
                    </div>
                </div>

                <!-- Empty State -->
                <div id="emptyState" class="hidden flex flex-col items-center justify-center py-20 opacity-50">
                    <i class="bi bi-calendar-x text-6xl text-slate-300 mb-4"></i>
                    <p class="text-lg font-bold text-slate-400">Tidak ada jadwal tersedia</p>
                </div>

            </div>
        </main>
    </div>

    <!-- Seat Map Template (Hidden) -->
    <template id="seatMapTemplate">
        <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex justify-between items-start mb-4 border-b border-slate-100 dark:border-slate-700 pb-3">
                <div>
                    <h3 class="text-lg font-extrabold text-sr-blue dark:text-white schedule-time">08:00</h3>
                    <div class="text-xs font-semibold text-slate-400 uppercase tracking-wide batch-name">Armada 1</div>
                </div>
                <div class="text-right">
                    <div class="text-xs text-slate-400">Occupancy</div>
                    <div class="font-mono font-bold text-blue-600 dark:text-blue-400 occupancy-rate">0/7</div>
                </div>
            </div>
            
            <div class="flex justify-center">
                <div class="bg-slate-100 dark:bg-slate-900/50 p-4 rounded-xl border border-slate-200 dark:border-slate-600 relative w-[180px]">
                    
                    <!-- Row 1: CC & Supir -->
                    <div class="grid grid-cols-3 gap-3 mb-4">
                        <div class="w-10 h-10 seat-placeholder" data-seat="CC"></div>
                        <div></div> <!-- Gap -->
                        <div class="w-10 h-10 rounded-lg bg-slate-200 dark:bg-slate-700 flex items-center justify-center text-[10px] font-bold text-slate-500 border border-slate-300 dark:border-slate-600 select-none">
                            SPR
                        </div>
                    </div>
                    
                    <!-- Row 2: 1 & 2 -->
                    <div class="grid grid-cols-3 gap-3 mb-4">
                        <div class="w-10 h-10 seat-placeholder" data-seat="1"></div>
                        <div></div> <!-- Gap -->
                        <div class="w-10 h-10 seat-placeholder" data-seat="2"></div>
                    </div>

                    <!-- Row 3: 3 & 4 -->
                    <div class="grid grid-cols-3 gap-3 mb-4">
                        <div class="w-10 h-10 seat-placeholder" data-seat="3"></div>
                        <div></div> <!-- Gap -->
                        <div class="w-10 h-10 seat-placeholder" data-seat="4"></div>
                    </div>

                    <!-- Row 4: 5, 6, 7 -->
                    <div class="grid grid-cols-3 gap-3">
                        <div class="w-10 h-10 seat-placeholder" data-seat="5"></div>
                        <div class="w-10 h-10 seat-placeholder" data-seat="6"></div>
                        <div class="w-10 h-10 seat-placeholder" data-seat="7"></div>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <!-- Booking Detail Modal -->
    <div id="bookingModal" class="fixed inset-0 z-[60] hidden">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-opacity" onclick="closeBookingModal()"></div>
        <div class="absolute inset-x-0 bottom-0 md:top-1/2 md:left-1/2 md:bottom-auto md:-translate-x-1/2 md:-translate-y-1/2 w-full md:w-[500px] bg-white dark:bg-slate-800 md:rounded-2xl rounded-t-2xl shadow-2xl transform transition-transform duration-300 translate-y-full md:translate-y-0 scale-95 md:scale-100 flex flex-col max-h-[90vh]">
            
            <div class="p-6 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-slate-50 dark:bg-slate-900/50 md:rounded-t-2xl">
                <h3 class="text-lg font-bold text-slate-800 dark:text-white flex items-center gap-2">
                    <i class="bi bi-ticket-perforated-fill text-blue-600"></i> Detail Booking
                </h3>
                <button onclick="closeBookingModal()" class="w-8 h-8 rounded-full bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 flex items-center justify-center transition-colors">
                    <i class="bi bi-x text-lg"></i>
                </button>
            </div>

            <div class="p-6 overflow-y-auto custom-scrollbar space-y-4">
                <div id="modalLoading" class="hidden text-center py-10">
                    <div class="animate-spin w-8 h-8 border-4 border-blue-600 border-t-transparent rounded-full mx-auto mb-2"></div>
                    <p class="text-sm text-slate-500">Memuat data...</p>
                </div>
                
                <div id="modalContent" class="space-y-4">
                    <!-- Dynamic Content -->
                </div>
            </div>

            <div id="modalActions" class="p-4 border-t border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 md:rounded-b-2xl flex gap-3 hidden">
                <button onclick="processBooking('reject')" class="flex-1 py-2.5 rounded-xl font-bold bg-red-100 text-red-600 hover:bg-red-200 transition-colors border border-red-200">
                    <i class="bi bi-x-circle mr-2"></i> Tolak
                </button>
                <button onclick="processBooking('approve')" class="flex-1 py-2.5 rounded-xl font-bold bg-green-600 text-white hover:bg-green-700 shadow-lg shadow-green-200 transition-all">
                    <i class="bi bi-check-circle-fill mr-2"></i> Terima
                </button>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script>
        const { createApp } = Vue;
        createApp({
            data() {
                return {
                    isDarkMode: localStorage.getItem('sutan_v10_dark') === 'true'
                }
            },
            mounted() {
                if (this.isDarkMode) document.documentElement.classList.add('dark');
            },
            methods: {
                toggleDarkMode() {
                    this.isDarkMode = !this.isDarkMode;
                    if (this.isDarkMode) document.documentElement.classList.add('dark');
                    else document.documentElement.classList.remove('dark');
                    localStorage.setItem('sutan_v10_dark', this.isDarkMode);
                }
            }
        }).mount('#app');
    </script>
    <script src="js/view_bookings.js?v=<?= time() ?>"></script>
</body>
</html>
