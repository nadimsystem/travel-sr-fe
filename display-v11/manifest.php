<!DOCTYPE html>
<html lang="id" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Harian - Sutan Raya</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        [v-cloak] { display: none; }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        .dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #475569; }
    </style>
    <script>
        tailwind.config = { darkMode: 'class', theme: { extend: { colors: { 'sr-blue': '#0f172a', 'sr-gold': '#d4af37' } } } }
    </script>
</head>
<body class="h-screen w-screen bg-slate-50 text-slate-800 dark:bg-slate-900 dark:text-slate-100 overflow-hidden">
    <div id="app" class="flex h-full w-full" v-cloak>
        <!-- Sidebar -->
        <?php $currentPage = 'manifest'; include 'components/sidebar.php'; ?>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col h-full overflow-hidden relative">
            <!-- Top Bar -->
            <header class="h-16 bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between px-6 z-10">
                <div class="flex items-center gap-4">
                    <h1 class="text-lg font-bold text-slate-800 dark:text-white">Laporan Harian</h1>
                    <div class="h-6 w-px bg-slate-200 dark:bg-slate-700"></div>
                    <div class="flex items-center gap-2 bg-slate-100 dark:bg-slate-700 rounded-lg p-1">
                        <input type="date" v-model="manifestDate" class="bg-transparent border-none text-sm font-bold text-slate-600 dark:text-slate-300 focus:ring-0 px-2">
                        <span class="text-xs font-bold px-3 py-1 bg-white dark:bg-slate-600 rounded text-slate-500 dark:text-slate-300 shadow-sm uppercase tracking-wider">{{ getDayName(manifestDate) }}</span>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <button onclick="window.print()" class="p-2 text-slate-400 hover:text-blue-600 transition-colors"><i class="bi bi-printer-fill text-xl"></i></button>
                    <div class="flex items-center gap-3 pl-4 border-l border-slate-200 dark:border-slate-700">
                        <div class="text-right">
                            <div class="text-xs font-bold text-slate-500 dark:text-slate-400">Halo, Admin</div>
                            <div class="text-[10px] text-slate-400">Administrator</div>
                        </div>
                        <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-xs">A</div>
                    </div>
                </div>
            </header>

            <!-- Scrollable Content -->
            <div class="flex-1 overflow-y-auto p-6 custom-scrollbar">
                <div class="max-w-7xl mx-auto space-y-6 pb-20">
                    
                    <!-- Hero Stats -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <!-- Total Revenue -->
                        <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm relative overflow-hidden group">
                            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                                <i class="bi bi-cash-stack text-6xl text-blue-600"></i>
                            </div>
                            <div class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-1">Total Pendapatan</div>
                            <div class="text-2xl font-black text-slate-800 dark:text-white">Rp {{ formatNumber(manifestReport.grandTotal.totalNominal) }}</div>
                            <div class="mt-2 flex items-center gap-2 text-xs">
                                <span class="text-green-500 font-bold bg-green-50 dark:bg-green-900/30 px-2 py-0.5 rounded-full">Lunas: {{ formatNumber(manifestReport.grandTotal.totalNominal - manifestReport.grandTotal.unpaidAmount) }}</span>
                            </div>
                        </div>

                        <!-- Total Pax -->
                        <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm relative overflow-hidden group">
                            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                                <i class="bi bi-people-fill text-6xl text-purple-600"></i>
                            </div>
                            <div class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-1">Total Penumpang</div>
                            <div class="text-2xl font-black text-slate-800 dark:text-white">{{ manifestReport.grandTotal.totalPax }} <span class="text-sm font-medium text-slate-400">Org</span></div>
                            <div class="mt-2 flex items-center gap-2 text-xs text-slate-500">
                                <span>Umum: <b>{{ manifestReport.grandTotal.umumPax }}</b></span>
                                <span class="w-1 h-1 bg-slate-300 rounded-full"></span>
                                <span>Pelajar: <b>{{ manifestReport.grandTotal.pelajarPax }}</b></span>
                            </div>
                        </div>

                        <!-- Unpaid -->
                        <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm relative overflow-hidden group">
                            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                                <i class="bi bi-exclamation-circle-fill text-6xl text-orange-600"></i>
                            </div>
                            <div class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-1">Belum Bayar</div>
                            <div class="text-2xl font-black text-orange-600">Rp {{ formatNumber(manifestReport.grandTotal.unpaidAmount) }}</div>
                            <div class="mt-2 text-xs text-slate-500">Perlu ditagih ke agen/penumpang</div>
                        </div>

                        <!-- Reconciliation -->
                        <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm flex flex-col items-center justify-center text-center">
                            <div class="w-12 h-12 rounded-full bg-green-100 text-green-600 flex items-center justify-center text-2xl mb-2">
                                <i class="bi bi-check-lg"></i>
                            </div>
                            <div class="text-sm font-bold text-slate-800 dark:text-white">Data Sinkron</div>
                            <div class="text-xs text-slate-400">Selisih: Rp 0</div>
                        </div>
                    </div>

                    <!-- Route Details -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div v-for="(data, routeName) in manifestReport.routes" :key="routeName" class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden flex flex-col">
                            <div class="p-4 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-slate-50/50 dark:bg-slate-800">
                                <div>
                                    <h3 class="font-bold text-slate-800 dark:text-white">{{ routeName }}</h3>
                                    <div class="text-xs text-slate-500">{{ data.rows.length }} Jadwal Perjalanan</div>
                                </div>
                                <div class="text-right">
                                    <div class="font-bold text-blue-600 dark:text-blue-400">Rp {{ formatNumber(data.total.totalNominal) }}</div>
                                    <div class="text-[10px] text-slate-400">{{ data.total.totalPax }} Penumpang</div>
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm text-left">
                                    <thead class="text-xs text-slate-500 uppercase bg-slate-50 dark:bg-slate-700/50">
                                        <tr>
                                            <th class="px-4 py-3 font-bold">Jam</th>
                                            <th class="px-4 py-3 font-bold text-center">Umum</th>
                                            <th class="px-4 py-3 font-bold text-center">Pelajar</th>
                                            <th class="px-4 py-3 font-bold text-right">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                        <tr v-for="row in data.rows" :key="row.time" class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                            <td class="px-4 py-3 font-medium text-slate-800 dark:text-slate-200">{{ row.time }}</td>
                                            <td class="px-4 py-3 text-center">
                                                <div class="font-bold text-slate-700 dark:text-slate-300">{{ row.umumPax }}</div>
                                                <div class="text-[10px] text-slate-400">Rp {{ formatNumber(row.umumNominal) }}</div>
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <div class="font-bold text-slate-700 dark:text-slate-300">{{ row.pelajarPax }}</div>
                                                <div class="text-[10px] text-slate-400">Rp {{ formatNumber(row.pelajarNominal) }}</div>
                                            </td>
                                            <td class="px-4 py-3 text-right">
                                                <div class="font-bold text-blue-600 dark:text-blue-400">Rp {{ formatNumber(row.totalNominal) }}</div>
                                                <div class="text-[10px] text-slate-400">{{ row.totalPax }} Org</div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Charter Card (Only if exists) -->
                        <div v-if="manifestReport.charters.length > 0" class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden flex flex-col">
                            <div class="p-4 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-yellow-50/50 dark:bg-yellow-900/10">
                                <div>
                                    <h3 class="font-bold text-slate-800 dark:text-white">Carteran & Dropping</h3>
                                    <div class="text-xs text-slate-500">{{ manifestReport.charters.length }} Transaksi</div>
                                </div>
                                <div class="text-right">
                                    <div class="font-bold text-yellow-600 dark:text-yellow-400">Rp {{ formatNumber(manifestReport.charterTotal.totalPrice) }}</div>
                                    <div class="text-[10px] text-slate-400">Sisa: Rp {{ formatNumber(manifestReport.charterTotal.remainingAmount) }}</div>
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm text-left">
                                    <thead class="text-xs text-slate-500 uppercase bg-slate-50 dark:bg-slate-700/50">
                                        <tr>
                                            <th class="px-4 py-3 font-bold">Rute</th>
                                            <th class="px-4 py-3 font-bold text-right">Total</th>
                                            <th class="px-4 py-3 font-bold text-right">Bayar</th>
                                            <th class="px-4 py-3 font-bold text-right">Sisa</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                        <tr v-for="c in manifestReport.charters" :key="c.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                            <td class="px-4 py-3 font-medium text-slate-800 dark:text-slate-200">{{ c.route }}</td>
                                            <td class="px-4 py-3 text-right font-bold">Rp {{ formatNumber(c.totalPrice) }}</td>
                                            <td class="px-4 py-3 text-right text-green-600">Rp {{ formatNumber(c.paidAmount) }}</td>
                                            <td class="px-4 py-3 text-right text-red-500">Rp {{ formatNumber(c.remainingAmount) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script src="app.js?v=<?= time() ?>"></script>
</body>
</html>