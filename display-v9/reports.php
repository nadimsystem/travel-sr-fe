<!DOCTYPE html>
<html lang="id" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan & Statistik - Sutan Raya</title>
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
                    <i class="bi bi-building-fill text-sr-gold"></i> Sutan<span class="text-blue-600 dark:text-blue-400">Raya</span>
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
                totalPax() { return this.reportData.pax.reduce((a, b) => a + b, 0); }
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
