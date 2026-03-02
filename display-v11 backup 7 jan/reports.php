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
                <!-- <div class="text-[10px] font-bold text-slate-400 uppercase px-3 mb-2 mt-6 tracking-wider">Pengaturan</div>
                <a href="admin_staff.php" class="flex items-center px-3 py-2.5 rounded-lg text-sm text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                    <i class="bi bi-people-fill w-6"></i> Admin & Staff
                </a> -->
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col min-w-0 bg-slate-50 dark:bg-slate-900 relative h-full overflow-hidden">
            <header class="h-16 bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between px-6 shadow-sm z-10">
                <h2 class="text-lg font-bold text-slate-800 dark:text-white">Laporan & Statistik</h2>
                <div class="flex items-center gap-3">
                    <input v-if="period === 'daily'" type="month" v-model="selectedMonth" @change="fetchReports" @input="fetchReports" class="bg-slate-100 dark:bg-slate-700 border-none rounded-lg text-sm font-bold px-3 py-2 outline-none text-slate-600 dark:text-slate-300">
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
                        <div class="text-xs font-bold text-slate-400 uppercase mb-1">Total Tiket Terjual</div>
                        <div class="text-2xl font-extrabold text-slate-800 dark:text-white">{{ totalPax }} <span class="text-sm font-medium text-slate-400">Kursi</span></div>
                    </div>
                    <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm">
                        <div class="text-xs font-bold text-slate-400 uppercase mb-1">Periode</div>
                        <div class="text-2xl font-extrabold text-slate-800 dark:text-white capitalize">{{ periodLabel }}</div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="reportData.labels.length === 0" class="flex flex-col items-center justify-center py-20 text-center">
                    <div class="bg-slate-100 dark:bg-slate-800 p-6 rounded-full mb-4">
                        <i class="bi bi-calendar-x text-4xl text-slate-400"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-700 dark:text-slate-200 mb-1">Tidak Ada Data Laporan</h3>
                    <p class="text-slate-500 text-sm max-w-xs mx-auto">Belum ada transaksi yang tercatat untuk periode <span class="font-bold text-slate-600 dark:text-slate-400">{{ periodLabel }}</span>.</p>
                </div>

                <!-- Charts & Table -->
                <div v-else>
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
                                        <th class="px-6 py-4">Tiket Terjual</th>
                                        <th class="px-6 py-4 text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                    <tr v-for="(label, index) in reversedLabels" :key="index" @click="openDetail(label)" class="group hover:bg-blue-50 dark:hover:bg-slate-700/50 transition-colors relative cursor-pointer">
                                        <td class="px-6 py-4 font-bold text-slate-700 dark:text-slate-200">{{ formatDate(label) }}</td>
                                        <td class="px-6 py-4 font-mono text-slate-800 dark:text-white font-bold">{{ formatRupiah(reversedRevenue[index]) }}</td>
                                        <td class="px-6 py-4 font-mono text-green-600 font-bold text-xs">{{ formatRupiah(reversedRevenueCash[index]) }}</td>
                                        <td class="px-6 py-4 font-mono text-blue-600 font-bold text-xs">{{ formatRupiah(reversedRevenueTransfer[index]) }}</td>
                                        <td class="px-6 py-4 font-bold text-slate-600 dark:text-slate-300">{{ reversedPax[index] }} Kursi</td>
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
                                                        <span class="bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded font-bold">{{ d.seats }} Kursi</span>
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
            </div>
        </main>

        <!-- Detail Modal -->
        <div v-if="selectedDate" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" @click.self="closeDetail">
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden animate-fade-in-up">
                <div class="p-6 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-slate-50 dark:bg-slate-800/50">
                    <div>
                        <h3 class="text-xl font-bold text-slate-800 dark:text-white">Detail Transaksi</h3>
                        <p class="text-slate-500 text-sm mt-1">{{ formatDate(selectedDate) }}</p>
                    </div>
                    <button @click="closeDetail" class="p-2 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-full transition-colors">
                        <i class="bi bi-x-lg text-slate-500"></i>
                    </button>
                </div>
                
                <div class="p-6 overflow-y-auto flex-1 custom-scrollbar">
                    <!-- Stats MiniCards -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                        <div class="bg-blue-50 dark:bg-slate-700/50 p-4 rounded-xl border border-blue-100 dark:border-slate-600">
                            <div class="text-[10px] font-bold text-blue-500 uppercase">Total Pendapatan</div>
                            <div class="text-lg font-bold text-slate-800 dark:text-blue-100">{{ formatRupiah(selectedStats.revenue) }}</div>
                        </div>
                        <div class="bg-green-50 dark:bg-slate-700/50 p-4 rounded-xl border border-green-100 dark:border-slate-600">
                            <div class="text-[10px] font-bold text-green-500 uppercase">Cash</div>
                            <div class="text-lg font-bold text-slate-800 dark:text-green-100">{{ formatRupiah(selectedStats.revenueCash) }}</div>
                        </div>
                        <div class="bg-indigo-50 dark:bg-slate-700/50 p-4 rounded-xl border border-indigo-100 dark:border-slate-600">
                            <div class="text-[10px] font-bold text-indigo-500 uppercase">Transfer</div>
                            <div class="text-lg font-bold text-slate-800 dark:text-indigo-100">{{ formatRupiah(selectedStats.revenueTransfer) }}</div>
                        </div>
                         <div class="bg-amber-50 dark:bg-slate-700/50 p-4 rounded-xl border border-amber-100 dark:border-slate-600">
                            <div class="text-[10px] font-bold text-amber-500 uppercase">Total Tiket</div>
                            <div class="text-lg font-bold text-slate-800 dark:text-amber-100">{{ selectedStats.pax }} Kursi</div>
                        </div>
                    </div>

                    <!-- Loader -->
                    <div v-if="isLoadingDetails" class="flex justify-center py-10">
                        <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-600"></div>
                    </div>

                    <!-- Table -->
                    <div v-else class="overflow-x-auto rounded-xl border border-slate-100 dark:border-slate-700">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-slate-100 dark:bg-slate-700 font-bold text-slate-600 dark:text-slate-300">
                                <tr>
                                    <th class="px-4 py-3">Waktu</th>
                                    <th class="px-4 py-3">Rute</th>
                                    <th class="px-4 py-3">Nama Penumpang</th>
                                    <th class="px-4 py-3 text-center">Info Kursi</th>
                                    <th class="px-4 py-3 text-right">Harga Total</th>
                                    <th class="px-4 py-3 text-center">Pembayaran</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700 bg-white dark:bg-slate-800">
                                <tr v-for="b in selectedBookings" :key="b.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/50">
                                    <td class="px-4 py-3 font-mono text-slate-500">{{ b.time }}</td>
                                    <td class="px-4 py-3 font-medium text-slate-800 dark:text-slate-200">{{ b.routeName }}</td>
                                    <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ b.passengerName }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="flex flex-col items-center">
                                            <span class="text-xs font-bold text-slate-800 dark:text-white mb-0.5">{{ b.seatNumbers || '-' }}</span>
                                            <span class="bg-slate-100 dark:bg-slate-700 px-1.5 py-0.5 rounded text-[10px] text-slate-500 font-medium">{{ b.seatCount }} Kursi</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-right font-mono font-bold text-slate-700 dark:text-white">{{ formatRupiah(b.totalPrice * b.seatCount) }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span :class="{'bg-green-100 text-green-700': b.paymentMethod === 'Cash', 'bg-blue-100 text-blue-700': b.paymentMethod !== 'Cash'}" class="px-2 py-1 rounded text-[10px] font-bold uppercase">{{ b.paymentMethod }}</span>
                                    </td>
                                </tr>
                                <tr v-if="selectedBookings.length === 0">
                                    <td colspan="6" class="px-4 py-8 text-center text-slate-400 italic">Tidak ada data transaksi.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const { createApp } = Vue;
        createApp({
            data() {
                return {
                    isDarkMode: false,
                    period: 'daily',
                    reportData: { labels: [], revenue: [], pax: [] },
                    charts: { revenue: null, pax: null },
                    selectedDate: null,
                    selectedBookings: [],
                    selectedStats: { revenue: 0, revenueCash: 0, revenueTransfer: 0, pax: 0 },
                    isLoadingDetails: false,
                    selectedMonth: new Date().toISOString().slice(0, 7)
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
                reversedPax() { return [...this.reportData.pax].reverse(); },
                periodLabel() {
                    if (this.period === 'daily') {
                        if (!this.selectedMonth) return 'Harian';
                        const date = new Date(this.selectedMonth + '-01');
                        return date.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });
                    }
                    if (this.period === 'monthly') return 'Tahunan (12 Bulan)';
                    return this.period;
                }
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
                formatDate(d) { 
                    if(!d) return '-'; 
                    // Handle Monthly (YYYY-MM)
                    if (d.length === 7) {
                        const date = new Date(d + '-01');
                        return date.toLocaleDateString('id-ID', { year: 'numeric', month: 'long' });
                    }
                    // Handle Daily (YYYY-MM-DD)
                    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }; 
                    return new Date(d).toLocaleDateString('id-ID', options); 
                },
                
                openDetail(date) {
                    this.selectedDate = date;
                    this.selectedBookings = [];
                    this.isLoadingDetails = true;
                    
                    // Find stats from existing data
                    const idx = this.reportData.labels.indexOf(date);
                    if (idx !== -1) {
                         this.selectedStats = {
                             revenue: this.reportData.revenue[idx],
                             revenueCash: (this.reportData.revenueCash || [])[idx] || 0,
                             revenueTransfer: (this.reportData.revenueTransfer || [])[idx] || 0,
                             pax: this.reportData.pax[idx]
                         };
                    }

                    fetch(`api.php?action=get_report_details&date=${date}`)
                        .then(res => res.json())
                        .then(d => {
                             if(d.bookings) {
                                 this.selectedBookings = d.bookings;
                             }
                             this.isLoadingDetails = false;
                        })
                        .catch(e => {
                            console.error(e);
                            this.isLoadingDetails = false;
                        });
                },
                closeDetail() {
                    this.selectedDate = null;
                },
                fetchReports() {
                    const monthParam = this.period === 'daily' ? `&month=${this.selectedMonth}` : '';
                    const url = `api.php?action=get_reports&period=${this.period}${monthParam}&t=${new Date().getTime()}`;
                    console.log('Fetching:', url);
                    fetch(url)
                        .then(res => res.json())
                        .then(d => {
                            if(d.reports) {
                                this.reportData = d.reports;
                                this.$nextTick(() => {
                                    this.updateCharts();
                                });
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
