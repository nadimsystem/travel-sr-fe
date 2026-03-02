<?php
// Force browser to not cache this page
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

include 'auth_check.php';
?>
<!DOCTYPE html>
<html lang="id" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sutan Raya - Business OS V11</title>
    <link rel="icon" type="image/webp" href="image/logo.webp">
    <script src="js/loading-optimizer.js?v=<?= time() ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            transition: background-color 0.3s, color 0.3s; 
            overflow: hidden; /* Prevent body scroll */
        }
        
        /* Custom Scrollbar */
        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        .dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #475569; }
        
        /* Utility */
        .hidden { display: none !important; }
        .animate-fade-in { animation: fadeIn 0.3s ease-out forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
    </style>
    
    <script>
        tailwind.config = { 
            darkMode: 'class',
            theme: { extend: { colors: { 'sr-blue': '#0f172a', 'sr-gold': '#d4af37' } } } 
        }
    </script>
</head>
<body class="h-screen w-screen bg-slate-50 text-slate-800 dark:bg-slate-900 dark:text-slate-100">
    <script>document.body.classList.add('sr-app-ready'); // Dashboard is vanilla JS, skip Vue check</script>
    <div id="app" class="flex h-full w-full">
        
        <?php $currentPage = 'dashboard'; include 'components/sidebar.php'; ?>

        <main class="flex-1 flex flex-col min-w-0 bg-slate-50 dark:bg-slate-900 relative h-full overflow-hidden transition-colors duration-300">
            <header class="h-14 sm:h-16 bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between px-3 sm:px-6 shadow-sm z-10 flex-shrink-0 transition-colors duration-300">
                <!-- Mobile Menu Button -->
                <button onclick="toggleMobileSidebar()" class="md:hidden w-9 h-9 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 transition-all flex items-center justify-center mr-2">
                    <i class="bi bi-list text-xl"></i>
                </button>
                
                <div>
                    <h2 class="text-base sm:text-lg font-bold text-slate-800 dark:text-white">Dashboard</h2>
                    <p class="hidden">Sistem Operasional V11 Ultimate</p>
                </div>
                <div class="flex items-center gap-2 sm:gap-4">
                    <div class="text-right hidden sm:block">
                        <div id="currentDate" class="text-[10px] sm:text-xs font-bold text-slate-400 uppercase"></div>
                        <div id="currentTime" class="text-sm sm:text-lg font-mono font-bold text-sr-blue dark:text-sr-gold leading-none"></div>
                    </div>
                    <button id="toggleFullscreenBtn" class="w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 transition-all flex items-center justify-center">
                        <i class="bi bi-arrows-fullscreen"></i>
                    </button>
                </div>
            </header>

            <div class="flex-1 relative overflow-hidden w-full">
                
                <!-- Skeleton Loader -->
                <div id="dashboardSkeleton" class="absolute inset-0 overflow-y-auto p-3 sm:p-6 md:p-8 custom-scrollbar bg-slate-50 dark:bg-slate-900 z-0">
                    <!-- Summary Skeleton -->
                    <div class="mb-4 sm:mb-8 animate-pulse">
                         <div class="h-4 w-32 bg-slate-200 dark:bg-slate-700 rounded mb-4"></div>
                         <div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6">
                            <div class="col-span-2 sm:col-span-1 bg-white dark:bg-slate-800 p-3 sm:p-5 rounded-xl border border-slate-100 dark:border-slate-700 h-24"></div>
                            <div class="bg-white dark:bg-slate-800 p-3 sm:p-5 rounded-xl border border-slate-100 dark:border-slate-700 h-24"></div>
                            <div class="bg-white dark:bg-slate-800 p-3 sm:p-5 rounded-xl border border-slate-100 dark:border-slate-700 h-24"></div>
                            <div class="col-span-2 sm:col-span-1 bg-white dark:bg-slate-800 p-3 sm:p-5 rounded-xl border border-slate-100 dark:border-slate-700 h-24"></div>
                         </div>
                    </div>
                    <!-- Monthly Skeleton -->
                    <div class="mb-4 sm:mb-8 animate-pulse">
                         <div class="h-4 w-48 bg-slate-200 dark:bg-slate-700 rounded mb-4"></div>
                         <div class="grid grid-cols-1 md:grid-cols-3 gap-3 sm:gap-6">
                             <div class="h-40 bg-slate-200 dark:bg-slate-700 rounded-xl"></div>
                             <div class="h-40 bg-white dark:bg-slate-800 rounded-xl border border-slate-100 dark:border-slate-700"></div>
                             <div class="h-40 bg-white dark:bg-slate-800 rounded-xl border border-slate-100 dark:border-slate-700"></div>
                         </div>
                    </div>
                </div>

                <!-- Actual Content (Hidden Initially) -->
                <div id="dashboardContent" class="absolute inset-0 overflow-y-auto p-3 sm:p-6 md:p-8 custom-scrollbar animate-fade-in bg-slate-50 dark:bg-slate-900 hidden opacity-0 transition-opacity duration-500">
                    
                    <!-- SUMMARY SECTION (Laporan Umum) -->
                    <div class="mb-4 sm:mb-8">
                        <h3 class="text-xs sm:text-sm font-bold text-slate-500 uppercase tracking-wider mb-2 sm:mb-4">Ringkasan Hari Ini</h3>
                        <div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6">
                            <!-- Today Revenue -->
                            <div class="col-span-2 sm:col-span-1 bg-white dark:bg-slate-800 p-3 sm:p-5 rounded-xl sm:rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm flex justify-between items-center transition-all">
                                <div><div class="text-[9px] sm:text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-0.5 sm:mb-1">Pendapatan Hari Ini</div><div id="todayRevenue" class="text-base sm:text-2xl font-extrabold text-slate-800 dark:text-white">Rp 0</div></div>
                                <div class="w-8 h-8 sm:w-12 sm:h-12 flex items-center justify-center bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 rounded-lg sm:rounded-xl"><i class="bi bi-cash-stack text-lg sm:text-2xl"></i></div>
                            </div>
                            <!-- Today Pax -->
                            <div class="bg-white dark:bg-slate-800 p-3 sm:p-5 rounded-xl sm:rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm flex justify-between items-center transition-all">
                                <div><div class="text-[9px] sm:text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-0.5 sm:mb-1">Penumpang</div><div id="todayPax" class="text-base sm:text-2xl font-extrabold text-slate-800 dark:text-white">0</div></div>
                                <div class="w-8 h-8 sm:w-12 sm:h-12 flex items-center justify-center bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400 rounded-lg sm:rounded-xl"><i class="bi bi-people-fill text-lg sm:text-2xl"></i></div>
                            </div>
                            <!-- Validation Pending -->
                            <a href="booking_management.php" class="bg-white dark:bg-slate-800 p-3 sm:p-5 rounded-xl sm:rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm flex justify-between items-center cursor-pointer hover:border-red-200 dark:hover:border-red-900 transition-all group">
                                <div><div class="text-[9px] sm:text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-0.5 sm:mb-1 group-hover:text-red-500 transition-colors">Validasi</div><div id="pendingValidationCount" class="text-base sm:text-2xl font-extrabold text-slate-800 dark:text-white group-hover:text-red-600 transition-colors">0</div></div>
                                <div class="w-8 h-8 sm:w-12 sm:h-12 flex items-center justify-center bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 rounded-lg sm:rounded-xl group-hover:bg-red-100 dark:group-hover:bg-red-900/40 transition-colors"><i class="bi bi-exclamation-circle text-lg sm:text-2xl"></i></div>
                            </a>
                            <!-- Dispatch Pending -->
                            <a href="dispatcher.php" class="col-span-2 sm:col-span-1 bg-white dark:bg-slate-800 p-3 sm:p-5 rounded-xl sm:rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm flex justify-between items-center cursor-pointer hover:border-orange-200 dark:hover:border-orange-900 transition-all group">
                                <div><div class="text-[9px] sm:text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-0.5 sm:mb-1 group-hover:text-orange-500 transition-colors">Antrian Dispatch</div><div id="pendingDispatchCount" class="text-base sm:text-2xl font-extrabold text-slate-800 dark:text-white group-hover:text-orange-600 transition-colors">0</div></div>
                                <div class="w-8 h-8 sm:w-12 sm:h-12 flex items-center justify-center bg-orange-50 dark:bg-orange-900/20 text-orange-600 dark:text-orange-400 rounded-lg sm:rounded-xl group-hover:bg-orange-100 dark:group-hover:bg-orange-900/40 transition-colors"><i class="bi bi-clock-history text-lg sm:text-2xl"></i></div>
                            </a>
                        </div>
                    </div>

                    <!-- MONTHLY OVERVIEW (Laporan Keseluruhan) -->
                    <div class="mb-4 sm:mb-8">
                        <h3 class="text-xs sm:text-sm font-bold text-slate-500 uppercase tracking-wider mb-2 sm:mb-4">Laporan Bulan Ini (Global)</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 sm:gap-6">
                            <div class="bg-gradient-to-br from-blue-600 to-blue-800 p-4 sm:p-6 rounded-xl sm:rounded-2xl shadow-lg shadow-blue-500/20 text-white relative overflow-hidden group">
                                <div class="relative z-10">
                                    <div class="text-blue-100 text-[10px] sm:text-xs font-bold uppercase mb-1 sm:mb-2">Total Pendapatan Bulan Ini</div>
                                    <div id="monthRevenue" class="text-xl sm:text-3xl font-extrabold mb-0.5 sm:mb-1">Rp 0</div>
                                    <div class="text-blue-200 text-[9px] sm:text-xs">Akumulasi semua rute</div>
                                </div>
                                <i class="bi bi-graph-up absolute bottom-[-10px] right-[-10px] text-6xl sm:text-8xl text-white/10 group-hover:scale-110 transition-transform"></i>
                            </div>

                            <div class="bg-white dark:bg-slate-800 p-4 sm:p-6 rounded-xl sm:rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm flex flex-col justify-center">
                                <div class="text-[10px] sm:text-xs font-bold text-slate-400 uppercase mb-1 sm:mb-2">Total Penumpang Bulan Ini</div>
                                <div class="flex items-end gap-2 sm:gap-3">
                                    <div id="monthPax" class="text-xl sm:text-3xl font-extrabold text-slate-800 dark:text-white">0</div>
                                    <div class="text-xs sm:text-sm font-bold text-slate-500 mb-1 sm:mb-1.5">Kursi Terjual</div>
                                </div>
                            </div>

                            <div class="bg-white dark:bg-slate-800 p-4 sm:p-6 rounded-xl sm:rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm flex flex-col justify-center">
                                <div class="text-[10px] sm:text-xs font-bold text-red-400 uppercase mb-1 sm:mb-2">Total Belum Bayar (Piutang)</div>
                                <div class="flex items-end gap-2 sm:gap-3">
                                    <div id="totalUnpaidAmount" class="text-xl sm:text-3xl font-extrabold text-slate-800 dark:text-white">Rp 0</div>
                                    <div id="totalUnpaidCount" class="text-xs sm:text-sm font-bold text-red-500 mb-1 sm:mb-1.5">(0 Booking)</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- CHART SECTION (Universal Trend) -->
                    <div class="mb-4 sm:mb-8 grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
                        <!-- Revenue Chart -->
                        <div class="bg-white dark:bg-slate-800 p-4 sm:p-6 rounded-xl sm:rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm">
                            <h3 class="text-xs sm:text-sm font-bold text-slate-500 uppercase tracking-wider mb-4">Tren Pendapatan (30 Hari)</h3>
                            <div class="h-64 sm:h-80 w-full relative">
                                <canvas id="dashboardChart"></canvas>
                            </div>
                        </div>
                        
                        <!-- Pax Chart -->
                        <div class="bg-white dark:bg-slate-800 p-4 sm:p-6 rounded-xl sm:rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm">
                            <h3 class="text-xs sm:text-sm font-bold text-slate-500 uppercase tracking-wider mb-4">Tren Penumpang (30 Hari)</h3>
                            <div class="h-64 sm:h-80 w-full relative">
                                <canvas id="dashboardPaxChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <!-- OPERATIONAL LISTS (Hidden, Reduced height) -->
                    <div class="hidden h-[300px] sm:h-[400px] min-h-[300px] sm:min-h-[400px] grid grid-cols-1 lg:grid-cols-2 gap-3 sm:gap-6 mb-20">
                         <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 flex flex-col shadow-sm overflow-hidden h-full">
                            <div class="p-3 sm:p-4 border-b border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800 flex justify-between items-center">
                                <h3 class="font-bold text-slate-700 dark:text-slate-200 text-xs sm:text-sm"><i class="bi bi-bus-front mr-1 sm:mr-2 text-blue-500"></i>Keberangkatan</h3>
                            </div>
                            <div id="outboundTripsList" class="flex-1 overflow-y-auto p-3 sm:p-4 space-y-2 sm:space-y-3 custom-scrollbar">
                                 <!-- JS Injected -->
                            </div>
                        </div>
                         <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 flex flex-col shadow-sm overflow-hidden h-full">
                            <div class="p-3 sm:p-4 border-b border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800 flex justify-between items-center">
                                <h3 class="font-bold text-slate-700 dark:text-slate-200 text-xs sm:text-sm"><i class="bi bi-arrow-return-left mr-1 sm:mr-2 text-green-500"></i>Kedatangan</h3>
                            </div>
                            <div id="inboundTripsList" class="flex-1 overflow-y-auto p-3 sm:p-4 space-y-2 sm:space-y-3 custom-scrollbar">
                                 <!-- JS Injected -->
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>
    
    <script src="js/utils.js"></script>
    <script src="js/dashboard.js?v=<?= time() ?>"></script>
</body>
</html>