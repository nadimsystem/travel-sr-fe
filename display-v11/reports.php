<!DOCTYPE html>
<html lang="id" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan & Statistik - Sutan Raya</title>
    <link rel="icon" type="image/webp" href="../image/logo.webp">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        [v-cloak] { display: none; }
    </style>
    <script>
        tailwind.config = { darkMode: 'class', theme: { extend: { colors: { 'sr-blue': '#0f172a', 'sr-gold': '#d4af37' } } } }
    </script>
</head>
<body class="h-screen w-screen bg-slate-50 text-slate-800 dark:bg-slate-900 dark:text-slate-100 overflow-hidden">
    <div id="app" class="flex h-full w-full" v-cloak>
        <!-- Sidebar (Consistent with index.php) -->
        <aside class="w-64 bg-white dark:bg-slate-800 border-r border-slate-200 dark:border-slate-700 flex flex-col z-20 flex-shrink-0 h-full shadow-sm">
            <div class="h-16 flex items-center justify-center border-b border-slate-100 dark:border-slate-700">
                <div class="text-xl font-extrabold text-sr-blue dark:text-white tracking-tight flex items-center gap-2">
                    <img src="../image/logo.png" alt="Sutan Raya" class="w-8 h-8 object-contain"> Sutan<span class="text-blue-600 dark:text-blue-400">Raya</span>
                </div>
            </div>
            <nav class="flex-1 overflow-y-auto p-3 space-y-1">
                <a href="index.php" class="flex items-center px-3 py-2.5 rounded-lg text-sm text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                    <i class="bi bi-arrow-left-circle-fill w-6"></i> Kembali ke Dashboard
                </a>
                <div class="text-[10px] font-bold text-slate-400 uppercase px-3 mb-2 mt-6 tracking-wider">Laporan</div>
                <a href="#" class="flex items-center px-3 py-2.5 rounded-lg text-sm bg-blue-50 dark:bg-slate-700 text-blue-700 dark:text-blue-300 font-bold transition-colors">
                    <i class="bi bi-bar-chart-fill w-6"></i> Statistik & Grafik
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col min-w-0 bg-slate-50 dark:bg-slate-900 relative h-full overflow-hidden">
            <header class="h-16 bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between px-6 shadow-sm z-10">
                <h2 class="text-lg font-bold text-slate-800 dark:text-white">Laporan & Statistik</h2>
                <div class="flex items-center gap-3">
                    <select v-model="period" @change="fetchReports" class="bg-slate-100 dark:bg-slate-700 border-none rounded-lg text-sm font-bold px-3 py-2 outline-none">
                        <option value="daily">Harian</option>
                        <option value="weekly">Mingguan</option>
                        <option value="monthly">Bulanan</option>
                        <option value="yearly">Tahunan</option>
                    </select>
                    <button @click="toggleDarkMode" class="w-9 h-9 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center"><i :class="isDarkMode ? 'bi-sun-fill' : 'bi-moon-stars-fill'"></i></button>
                </div>
            </header>

            <div class="flex-1 overflow-y-auto p-6 custom-scrollbar">
                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm">
                        <div class="text-xs font-bold text-slate-400 uppercase mb-1">Total Pendapatan</div>
                        <div class="text-2xl font-extrabold text-slate-800 dark:text-white">{{ formatRupiah(totalRevenue) }}</div>
                    </div>
                    <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm">
                        <div class="text-xs font-bold text-slate-400 uppercase mb-1">Total Penumpang</div>
                        <div class="text-2xl font-extrabold text-slate-800 dark:text-white">{{ totalPax }} <span class="text-sm font-medium text-slate-400">Pax</span></div>
                    </div>
                    <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm">
                        <div class="text-xs font-bold text-slate-400 uppercase mb-1">Periode</div>
                        <div class="text-2xl font-extrabold text-slate-800 dark:text-white capitalize">{{ period }}</div>
                    </div>
                </div>

            <!-- Charts -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm">
                        <h3 class="font-bold text-slate-700 dark:text-slate-200 mb-4">Grafik Pendapatan</h3>
                        <canvas id="revenueChart"></canvas>
                    </div>
                    <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm">
                        <h3 class="font-bold text-slate-700 dark:text-slate-200 mb-4">Grafik Penumpang</h3>
                        <canvas id="paxChart"></canvas>
                    </div>
                </div>

                <!-- Detailed Table with Popover -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden">
                    <div class="p-5 border-b border-slate-100 dark:border-slate-700">
                        <h3 class="font-bold text-slate-800 dark:text-white">Laporan Harian Detail</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-slate-50 dark:bg-slate-700 text-slate-500 dark:text-slate-300 font-bold uppercase text-xs">
                                <tr>
                                    <th class="px-6 py-4">Tanggal</th>
                                    <th class="px-6 py-4">Total Pendapatan</th>
                                    <th class="px-6 py-4 text-green-600">Cash</th>
                                    <th class="px-6 py-4 text-blue-600">Transfer</th>
                                    <th class="px-6 py-4">Penumpang</th>
                                    <th class="px-6 py-4 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                <tr v-for="(label, index) in reversedLabels" :key="index" class="group hover:bg-blue-50 dark:hover:bg-slate-700/50 transition-colors relative cursor-default">
                                    <td class="px-6 py-4 font-bold text-slate-700 dark:text-slate-200">{{ formatDate(label) }}</td>
                                    <td class="px-6 py-4 font-mono text-slate-800 dark:text-white font-bold">{{ formatRupiah(reversedRevenue[index]) }}</td>
                                    <td class="px-6 py-4 font-mono text-green-600 font-bold text-xs">{{ formatRupiah(reversedRevenueCash[index]) }}</td>
                                    <td class="px-6 py-4 font-mono text-blue-600 font-bold text-xs">{{ formatRupiah(reversedRevenueTransfer[index]) }}</td>
                                    <td class="px-6 py-4 font-bold text-slate-600 dark:text-slate-300">{{ reversedPax[index] }} Pax</td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-2 py-1 rounded-full bg-green-100 text-green-700 text-[10px] font-bold">Completed</span>
                                    </td>

                                    <!-- Popover -->
                                    <div class="absolute left-1/2 -translate-x-1/2 bottom-full mb-2 w-72 bg-white dark:bg-slate-800 rounded-xl shadow-xl border border-slate-200 dark:border-slate-600 p-4 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-50 pointer-events-none">
                                        <div class="text-xs font-bold text-slate-400 uppercase mb-2 border-b border-slate-100 pb-2 flex justify-between">
                                            <span>Detail Perjalanan</span>
                                            <span>{{ formatDate(label) }}</span>
                                        </div>
                                        <div v-if="reportData.details && reportData.details[label]" class="space-y-3">
                                            <div v-for="(d, i) in reportData.details[label]" :key="i" class="text-xs border-b border-slate-50 dark:border-slate-700 pb-2 last:border-0 last:pb-0">
                                                <div class="flex justify-between items-center mb-1">
                                                    <div>
                                                        <span class="font-bold text-slate-700 dark:text-slate-200 bg-slate-100 dark:bg-slate-700 px-1.5 rounded">{{ d.time }}</span>
                                                        <span class="text-slate-500 ml-1">{{ d.routeName }}</span>
                                                    </div>
                                                    <span class="bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded font-bold">{{ d.seats }} Pax</span>
                                                </div>
                                                <div class="flex justify-between items-center">
                                                    <span class="text-slate-400">Pendapatan:</span>
                                                    <span class="font-mono font-bold text-green-600">{{ formatRupiah(d.tripRevenue) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div v-else class="text-xs text-slate-400 italic">Tidak ada data detail.</div>
                                        <!-- Arrow -->
                                        <div class="absolute left-1/2 -translate-x-1/2 top-full w-3 h-3 bg-white dark:bg-slate-800 border-r border-b border-slate-200 dark:border-slate-600 transform rotate-45 -mt-1.5"></div>
                                    </div>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        const { createApp } = Vue;
        createApp({
            data() {
                return {
                    isDarkMode: false,
                    period: 'monthly',
                    reportData: { labels: [], revenue: [], pax: [] },
                    charts: { revenue: null, pax: null }
                }
            },
            computed: {
                totalRevenue() { return this.reportData.revenue.reduce((a, b) => a + b, 0); },
                totalPax() { return this.reportData.pax.reduce((a, b) => a + b, 0); },
                // Table needs Newest -> Oldest (Reverse of Chart Data)
                reversedLabels() { return [...this.reportData.labels].reverse(); },
                reversedRevenue() { return [...this.reportData.revenue].reverse(); },
                reversedRevenueCash() { return [...(this.reportData.revenueCash || [])].reverse(); },
                reversedRevenueTransfer() { return [...(this.reportData.revenueTransfer || [])].reverse(); },
                reversedPax() { return [...this.reportData.pax].reverse(); }
            },
            mounted() {
                if(localStorage.getItem('sutan_v85_theme') === 'dark') { this.isDarkMode = true; document.documentElement.classList.add('dark'); }
                this.fetchReports();
            },
            methods: {
                toggleDarkMode() {
                    this.isDarkMode = !this.isDarkMode;
                    if(this.isDarkMode) document.documentElement.classList.add('dark'); else document.documentElement.classList.remove('dark');
                    localStorage.setItem('sutan_v85_theme', this.isDarkMode ? 'dark' : 'light');
                },
                formatRupiah(n) { return new Intl.NumberFormat('id-ID', {style:'currency', currency:'IDR', minimumFractionDigits:0}).format(n||0); },
                formatDate(d) { if(!d) return '-'; const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }; return new Date(d).toLocaleDateString('id-ID', options); },
                
                fetchReports() {
                    fetch(`api.php?action=get_reports&period=${this.period}`)
                        .then(res => res.json())
                        .then(d => {
                            if(d.reports) {
                                this.reportData = d.reports;
                                this.updateCharts();
                            }
                        });
                },
                
                updateCharts() {
                    const ctxRev = document.getElementById('revenueChart');
                    const ctxPax = document.getElementById('paxChart');
                    
                    if(this.charts.revenue) this.charts.revenue.destroy();
                    if(this.charts.pax) this.charts.pax.destroy();
                    
                    const commonOptions = {
                        responsive: true,
                        plugins: { legend: { display: false } },
                        scales: { y: { beginAtZero: true, grid: { color: this.isDarkMode?'#334155':'#e2e8f0' } }, x: { grid: { display: false } } }
                    };

                    this.charts.revenue = new Chart(ctxRev, {
                        type: 'line',
                        data: {
                            labels: this.reportData.labels,
                            datasets: [{
                                label: 'Pendapatan',
                                data: this.reportData.revenue,
                                borderColor: '#2563eb',
                                backgroundColor: 'rgba(37, 99, 235, 0.1)',
                                fill: true,
                                tension: 0.4
                            }]
                        },
                        options: commonOptions
                    });

                    this.charts.pax = new Chart(ctxPax, {
                        type: 'bar',
                        data: {
                            labels: this.reportData.labels,
                            datasets: [{
                                label: 'Penumpang',
                                data: this.reportData.pax,
                                backgroundColor: '#d4af37',
                                borderRadius: 4
                            }]
                        },
                        options: commonOptions
                    });
                }
            }
        }).mount('#app');
    </script>
</body>
</html>
