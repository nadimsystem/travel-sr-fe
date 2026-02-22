<!DOCTYPE html>
<html lang="id" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sutan Raya - Business OS V11</title>
    <link rel="icon" type="image/webp" href="../image/logo.webp">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    
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

    <div id="app" class="flex h-full w-full">
        
        <?php $currentPage = 'dashboard'; include 'components/sidebar.php'; ?>

        <main class="flex-1 flex flex-col min-w-0 bg-slate-50 dark:bg-slate-900 relative h-full overflow-hidden transition-colors duration-300">
            <header class="h-16 bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between px-6 shadow-sm z-10 flex-shrink-0 transition-colors duration-300">
                <div>
                    <h2 class="text-lg font-bold text-slate-800 dark:text-white">Dashboard</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Sistem Operasional V11 Ultimate</p>
                </div>
                <div class="flex items-center gap-4">
                    <div class="text-right hidden sm:block">
                        <div id="currentDate" class="text-xs font-bold text-slate-400 uppercase"></div>
                        <div id="currentTime" class="text-lg font-mono font-bold text-sr-blue dark:text-sr-gold leading-none"></div>
                    </div>
                    <button id="toggleDarkModeBtn" class="w-9 h-9 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-yellow-400 hover:bg-slate-200 dark:hover:bg-slate-600 transition-all flex items-center justify-center" title="Mode Gelap/Terang">
                        <i class="bi bi-moon-stars-fill"></i>
                    </button>
                    <button id="toggleFullscreenBtn" class="w-9 h-9 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 transition-all flex items-center justify-center">
                        <i class="bi bi-arrows-fullscreen"></i>
                    </button>
                </div>
            </header>

            <div class="flex-1 relative overflow-hidden w-full">
                
                <div class="absolute inset-0 overflow-y-auto p-6 md:p-8 custom-scrollbar animate-fade-in bg-slate-50 dark:bg-slate-900">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                        <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm flex justify-between items-center transition-all hover:shadow-md">
                            <div><div class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Pendapatan Hari Ini</div><div id="todayRevenue" class="text-2xl font-extrabold text-slate-800 dark:text-white">Rp 0</div></div>
                            <div class="w-12 h-12 flex items-center justify-center bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400 rounded-xl"><i class="bi bi-cash-stack text-2xl"></i></div>
                        </div>
                        <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm flex justify-between items-center transition-all hover:shadow-md">
                            <div><div class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Total Order</div><div id="todayOrderCount" class="text-2xl font-extrabold text-slate-800 dark:text-white">0 Pesanan</div></div>
                            <div class="w-12 h-12 flex items-center justify-center bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 rounded-xl"><i class="bi bi-receipt text-2xl"></i></div>
                        </div>
                        <a href="booking_management.php" class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm flex justify-between items-center cursor-pointer hover:shadow-md hover:border-red-200 dark:hover:border-red-900 transition-all group">
                            <div><div class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1 group-hover:text-red-500 transition-colors">Validasi Pending</div><div class="text-2xl font-extrabold text-slate-800 dark:text-white group-hover:text-red-600 transition-colors">Lihat</div></div>
                            <div class="w-12 h-12 flex items-center justify-center bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 rounded-xl group-hover:bg-red-100 dark:group-hover:bg-red-900/40 transition-colors"><i class="bi bi-exclamation-circle text-2xl"></i></div>
                        </a>
                        <a href="dispatcher.php" class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm flex justify-between items-center cursor-pointer hover:shadow-md hover:border-orange-200 dark:hover:border-orange-900 transition-all group">
                            <div><div class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1 group-hover:text-orange-500 transition-colors">Antrian Dispatch</div><div class="text-2xl font-extrabold text-slate-800 dark:text-white group-hover:text-orange-600 transition-colors">Lihat</div></div>
                            <div class="w-12 h-12 flex items-center justify-center bg-orange-50 dark:bg-orange-900/20 text-orange-600 dark:text-orange-400 rounded-xl group-hover:bg-orange-100 dark:group-hover:bg-orange-900/40 transition-colors"><i class="bi bi-clock-history text-2xl"></i></div>
                        </a>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 h-[calc(100%-140px)] min-h-[400px]">
                        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 flex flex-col shadow-sm overflow-hidden h-full transition-colors">
                            <div class="p-3 border-b border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800 flex justify-between items-center">
                                <h3 class="font-bold text-slate-700 dark:text-slate-200 text-sm">Keberangkatan</h3>
                                <span class="text-[10px] bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 px-2 py-0.5 rounded font-bold">Dari Padang</span>
                            </div>
                            <div id="outboundTripsList" class="flex-1 overflow-y-auto p-3 space-y-3 custom-scrollbar">
                                <!-- JS Injected -->
                            </div>
                        </div>
                        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 flex flex-col shadow-sm overflow-hidden h-full transition-colors">
                            <div class="p-3 border-b border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800 flex justify-between items-center">
                                <h3 class="font-bold text-slate-700 dark:text-slate-200 text-sm">Kedatangan</h3>
                                <span class="text-[10px] bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300 px-2 py-0.5 rounded font-bold">Ke Padang</span>
                            </div>
                            <div id="inboundTripsList" class="flex-1 overflow-y-auto p-3 space-y-3 custom-scrollbar">
                                <!-- JS Injected -->
                            </div>
                        </div>
                        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 flex flex-col shadow-sm overflow-hidden h-full transition-colors">
                            <div class="p-3 border-b border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800"><h3 class="font-bold text-slate-700 dark:text-slate-200 text-sm">Armada Standby</h3></div>
                            <div id="fleetStatusList" class="flex-1 overflow-y-auto p-3 space-y-2 custom-scrollbar">
                                <!-- JS Injected -->
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>
    
    <script src="js/utils.js"></script>
    <script src="js/dashboard.js"></script>
</body>
</html>