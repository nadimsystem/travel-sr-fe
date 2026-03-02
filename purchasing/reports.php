<!DOCTYPE html>
<html lang="id" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports & Analysis - Purchasing Sutan Raya</title>
    <link rel="icon" type="image/webp" href="../image/logo.webp">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; transition: background-color 0.3s; }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #475569; }
        [v-cloak] { display: none; }
    </style>
    <script>
        tailwind.config = { darkMode: 'class', theme: { extend: { colors: { 'sr-blue': '#0f172a' } } } }
    </script>
</head>
<body class="h-screen w-screen bg-slate-50 text-slate-800 dark:bg-slate-900 dark:text-slate-100">

    <div id="app" class="flex h-full w-full" v-cloak>
        
        <?php $currentPage = 'purchasing_reports'; include 'components/sidebar.php'; ?>

        <main class="flex-1 flex flex-col min-w-0 bg-slate-50 dark:bg-slate-900 overflow-hidden relative">
            <header class="h-16 bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between px-4 md:px-6 shadow-sm z-10 shrink-0">
                <div class="flex items-center gap-3">
                    <button onclick="toggleSidebar()" class="lg:hidden text-slate-600 dark:text-slate-300 hover:text-blue-600 p-2 -ml-2">
                        <i class="bi bi-list text-2xl"></i>
                    </button>
                    <div>
                        <h2 class="text-base md:text-lg font-bold text-slate-800 dark:text-white">Cost Analysis & Reports</h2>
                        <p class="text-xs text-slate-500 hidden sm:block">Decision Support System untuk Top Management</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <select class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-bold">
                        <option>Bulan Ini</option>
                        <option>Quarter 1</option>
                        <option>Tahun Ini</option>
                    </select>
                    <button class="bg-blue-600 text-white px-3 py-1.5 rounded-lg text-sm font-bold shadow hover:bg-blue-700 transition"><i class="bi bi-printer-fill"></i> PDF</button>
                </div>
            </header>

            <div class="flex-1 overflow-y-auto custom-scrollbar p-6 space-y-8">
                
                <!-- TOP LEVEL SUMMARY -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white dark:bg-slate-800 p-6 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-sm relative overflow-hidden">
                        <div class="absolute right-0 top-0 p-6 opacity-5"><i class="bi bi-cash-coin text-6xl text-blue-600"></i></div>
                        <div class="relative z-10">
                            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Belanja (Bulan Ini)</h3>
                            <div class="text-3xl font-extrabold text-slate-800 dark:text-white mt-2">Rp 45.2 Juta</div>
                            <div class="mt-2 text-xs font-bold text-green-500 flex items-center gap-1"><i class="bi bi-arrow-down-right"></i> Hemat 5% vs Bulan Lalu</div>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-slate-800 p-6 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-sm relative overflow-hidden">
                        <div class="absolute right-0 top-0 p-6 opacity-5"><i class="bi bi-tools text-6xl text-orange-600"></i></div>
                        <div class="relative z-10">
                            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Unit Paling Boros</h3>
                            <div class="text-2xl font-extrabold text-orange-600 mt-2">BA 7099 XXX</div>
                            <div class="mt-2 text-xs font-bold text-slate-500">Total Biaya: Rp 15.000.000 (Mesin)</div>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-slate-800 p-6 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-sm relative overflow-hidden">
                        <div class="absolute right-0 top-0 p-6 opacity-5"><i class="bi bi-cart-check text-6xl text-green-600"></i></div>
                        <div class="relative z-10">
                            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Efisiensi Pembelian</h3>
                            <div class="text-3xl font-extrabold text-green-600 mt-2">92%</div>
                            <div class="mt-2 text-xs font-bold text-slate-500">Pengadaan tepat waktu & sesuai budget</div>
                        </div>
                    </div>
                </div>

                <!-- CHARTS AREA -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white dark:bg-slate-800 p-6 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-sm">
                         <h3 class="font-bold text-slate-800 dark:text-white mb-6">Biaya Maintenance per Kategori</h3>
                         <div class="h-64"><canvas id="categoryChart"></canvas></div>
                    </div>
                    <div class="bg-white dark:bg-slate-800 p-6 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-sm">
                         <h3 class="font-bold text-slate-800 dark:text-white mb-6">Top 5 Unit Cost Tertinggi</h3>
                         <div class="h-64"><canvas id="fleetChart"></canvas></div>
                    </div>
                </div>

                <!-- LEMON DETECTOR TABLE -->
                <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
                    <div class="p-6 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-red-50 dark:bg-red-900/10">
                        <div>
                            <h3 class="font-extrabold text-lg text-red-700 dark:text-red-400 flex items-center gap-2">
                                <i class="bi bi-exclamation-triangle-fill"></i> Lemon Detector
                            </h3>
                            <p class="text-xs text-red-600/70 dark:text-red-400/70">Unit yang biaya perawatannya melebihi batas wajar revenue (Rekomendasi: Jual/Ganti)</p>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-slate-50 dark:bg-slate-700/50 text-[10px] uppercase font-bold text-slate-500">
                                <tr>
                                    <th class="p-4">Unit Armada</th>
                                    <th class="p-4">Tipe</th>
                                    <th class="p-4">Usia</th>
                                    <th class="p-4 text-right">Total Maintenance (YTD)</th>
                                    <th class="p-4 text-right">Rasio Cost/Revenue</th>
                                    <th class="p-4 text-center">Rekomendasi System</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                                    <td class="p-4 font-bold text-slate-700 dark:text-white">BA 7099 XXX</td>
                                    <td class="p-4">Mercedes Benz OH 1526</td>
                                    <td class="p-4">12 Tahun</td>
                                    <td class="p-4 text-right font-mono font-bold text-red-600">Rp 120.000.000</td>
                                    <td class="p-4 text-right font-bold text-red-600">45% (Critical)</td>
                                    <td class="p-4 text-center">
                                        <span class="inline-block px-3 py-1 rounded-full bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300 font-bold text-xs uppercase">
                                            REPLACE / SELL
                                        </span>
                                    </td>
                                </tr>
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                                    <td class="p-4 font-bold text-slate-700 dark:text-white">BA 7050 OAU</td>
                                    <td class="p-4">Hiace Commuter</td>
                                    <td class="p-4">5 Tahun</td>
                                    <td class="p-4 text-right font-mono font-bold text-slate-600 dark:text-slate-300">Rp 12.000.000</td>
                                    <td class="p-4 text-right font-bold text-yellow-600">15% (Warning)</td>
                                    <td class="p-4 text-center">
                                        <span class="inline-block px-3 py-1 rounded-full bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300 font-bold text-xs uppercase">
                                            MONITOR
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <script>
        const { createApp, ref, onMounted } = Vue;

        createApp({
            setup() {
                const totalAssetValue = ref('Loading...');

                const fetchStats = async () => {
                     try {
                        const res = await fetch('api.php?action=get_inventory_stats');
                        const data = await res.json();
                        if(data.status === 'success') {
                            totalAssetValue.value = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(data.data.asset_value);
                        }
                    } catch(e) { console.error(e); }
                };

                onMounted(() => {
                    fetchStats();
                    
                    const ctxCat = document.getElementById('categoryChart');
                    if(ctxCat) {
                        new Chart(ctxCat, {
                            type: 'doughnut',
                            data: {
                                labels: ['Sparepart', 'Service Mesin', 'Ban', 'Oli', 'Lainnya'],
                                datasets: [{
                                    data: [35, 25, 20, 15, 5],
                                    backgroundColor: ['#2563eb', '#dc2626', '#d97706', '#16a34a', '#64748b']
                                }]
                            },
                        });
                    }

                    const ctxFleet = document.getElementById('fleetChart');
                    if(ctxFleet) {
                        new Chart(ctxFleet, {
                            type: 'bar',
                            data: {
                                labels: ['BA 7099 XXX', 'BA 7083 AU', 'BA 7056 OAU', 'BA 7053 OAU', 'BA 7063 OAU'],
                                datasets: [{
                                    label: 'Biaya Maintenance (Juta)',
                                    data: [15, 8, 5, 4.5, 3],
                                    backgroundColor: '#2563eb',
                                    borderRadius: 8
                                }]
                            },
                        });
                    }
                });
                return { totalAssetValue };
            }
        }).mount('#app');
    </script>
</body>
</html>
