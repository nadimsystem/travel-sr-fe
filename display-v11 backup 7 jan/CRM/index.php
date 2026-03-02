<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM & Data Konsumen - Sutan Raya</title>
    <link rel="icon" type="image/webp" href="../../image/logo.webp">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
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
        <!-- Sidebar -->
        <aside class="w-64 bg-white dark:bg-slate-800 border-r border-slate-200 dark:border-slate-700 flex flex-col z-20 flex-shrink-0 h-full shadow-sm">
            <div class="h-16 flex items-center justify-center border-b border-slate-100 dark:border-slate-700">
                <div class="text-xl font-extrabold text-sr-blue dark:text-white tracking-tight flex items-center gap-2">
                    <img src="../../image/logo.png" alt="Sutan Raya" class="w-8 h-8 object-contain"> Sutan<span class="text-blue-600 dark:text-blue-400">Raya</span>
                </div>
            </div>
            <nav class="flex-1 overflow-y-auto p-3 space-y-1">
                <a href="../index.php" class="flex items-center px-3 py-2.5 rounded-lg text-sm text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                    <i class="bi bi-arrow-left-circle-fill w-6"></i> Kembali ke Dashboard
                </a>
                <div class="text-[10px] font-bold text-slate-400 uppercase px-3 mb-2 mt-6 tracking-wider">CRM</div>
                <a href="#" class="flex items-center px-3 py-2.5 rounded-lg text-sm bg-blue-50 dark:bg-slate-700 text-blue-700 dark:text-blue-300 font-bold transition-colors">
                    <i class="bi bi-people-fill w-6"></i> Data Konsumen
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col min-w-0 bg-slate-50 dark:bg-slate-900 relative h-full overflow-hidden">
            <header class="h-16 bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between px-6 shadow-sm z-10">
                <h2 class="text-lg font-bold text-slate-800 dark:text-white">Bank Data Konsumen</h2>
                <div class="relative w-64">
                    <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="text" v-model="searchQuery" placeholder="Cari Nama / No. HP..." class="w-full pl-10 pr-4 py-2 text-sm bg-slate-100 dark:bg-slate-700 border-none rounded-lg outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </header>

            <div class="flex-1 overflow-y-auto p-6 custom-scrollbar">
                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-xl"><i class="bi bi-person-lines-fill"></i></div>
                        <div>
                            <div class="text-xs font-bold text-slate-400 uppercase mb-1">Total Konsumen</div>
                            <div class="text-2xl font-extrabold text-slate-800 dark:text-white">{{ filteredCustomers.length }}</div>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full bg-green-50 text-green-600 flex items-center justify-center text-xl"><i class="bi bi-cash-stack"></i></div>
                        <div>
                            <div class="text-xs font-bold text-slate-400 uppercase mb-1">Total Transaksi</div>
                            <div class="text-2xl font-extrabold text-slate-800 dark:text-white">{{ formatRupiah(totalTransactionValue) }}</div>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center text-xl"><i class="bi bi-star-fill"></i></div>
                        <div>
                            <div class="text-xs font-bold text-slate-400 uppercase mb-1">Top Spender</div>
                            <div class="text-sm font-extrabold text-slate-800 dark:text-white truncate w-32">{{ topSpenderName }}</div>
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-slate-50 dark:bg-slate-700 text-slate-500 dark:text-slate-300 font-bold uppercase text-xs">
                            <tr>
                                <th class="px-6 py-4">Nama Pelanggan</th>
                                <th class="px-6 py-4">Kontak</th>
                                <th class="px-6 py-4 text-center">Total Trip</th>
                                <th class="px-6 py-4 text-right">Total Nilai</th>
                                <th class="px-6 py-4 text-right">Terakhir Transaksi</th>
                                <th class="px-6 py-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            <tr v-for="c in filteredCustomers" :key="c.phone" class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                                <td class="px-6 py-4 font-bold text-slate-800 dark:text-white">{{ c.name || 'Tanpa Nama' }}</td>
                                <td class="px-6 py-4 font-mono text-slate-600 dark:text-slate-300">{{ c.phone }}</td>
                                <td class="px-6 py-4 text-center font-bold text-blue-600 bg-blue-50 dark:bg-slate-700 rounded-lg">{{ c.totalTrips }}</td>
                                <td class="px-6 py-4 text-right font-mono font-bold text-green-600">{{ formatRupiah(c.totalRevenue) }}</td>
                                <td class="px-6 py-4 text-right text-slate-500">{{ formatDate(c.lastTrip) }}</td>
                                <td class="px-6 py-4 text-center">
                                    <button @click="viewHistory(c)" class="text-blue-600 hover:text-blue-800 font-bold text-xs bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-lg transition-colors">
                                        <i class="bi bi-eye-fill mr-1"></i> Detail
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="filteredCustomers.length === 0">
                                <td colspan="6" class="px-6 py-10 text-center text-slate-400 italic">Data tidak ditemukan.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>

        <!-- Detail Modal -->
        <div v-if="selectedCustomer" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" @click.self="closeHistory">
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl w-full max-w-3xl max-h-[80vh] flex flex-col overflow-hidden animate-fade-in-up">
                <div class="p-6 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-slate-50 dark:bg-slate-800/50">
                    <div>
                        <h3 class="text-xl font-bold text-slate-800 dark:text-white">{{ selectedCustomer.name }}</h3>
                        <p class="text-slate-500 text-sm mt-1 font-mono">{{ selectedCustomer.phone }}</p>
                    </div>
                    <button @click="closeHistory" class="p-2 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-full transition-colors">
                        <i class="bi bi-x-lg text-slate-500"></i>
                    </button>
                </div>
                <div class="p-6 overflow-y-auto flex-1 custom-scrollbar">
                    <h4 class="font-bold text-slate-700 dark:text-slate-200 mb-4">Riwayat Perjalanan</h4>
                    <div v-if="isLoadingHistory" class="flex justify-center py-10">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                    </div>
                    <div v-else class="space-y-3">
                        <div v-for="h in historyData" :key="h.id" class="p-4 border border-slate-100 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-700/50 flex justify-between items-center">
                            <div>
                                <div class="font-bold text-slate-800 dark:text-white mb-1">{{ formatDate(h.date) }} <span class="text-slate-400 mx-1">•</span> <span class="font-mono bg-slate-100 dark:bg-slate-600 px-1.5 rounded text-xs">{{ h.time }}</span></div>
                                <div class="text-sm text-slate-600 dark:text-slate-300">{{ h.routeName }}</div>
                            </div>
                            <div class="text-right">
                                <div class="font-bold text-green-600 font-mono">{{ formatRupiah(h.totalPrice) }}</div>
                                <div class="text-xs mt-1">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold" :class="h.status === 'Completed' || h.status === 'Tiba' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'">{{ h.status }}</span>
                                </div>
                            </div>
                        </div>
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
                    customers: [],
                    searchQuery: '',
                    selectedCustomer: null,
                    historyData: [],
                    isLoadingHistory: false
                }
            },
            computed: {
                filteredCustomers() {
                    const q = this.searchQuery.toLowerCase();
                    return this.customers.filter(c => 
                        (c.name && c.name.toLowerCase().includes(q)) || 
                        (c.phone && c.phone.includes(q))
                    );
                },
                totalTransactionValue() {
                    return this.filteredCustomers.reduce((sum, c) => sum + parseFloat(c.totalRevenue), 0);
                },
                topSpenderName() {
                    if (this.customers.length === 0) return '-';
                    const top = [...this.customers].sort((a,b) => parseFloat(b.totalRevenue) - parseFloat(a.totalRevenue))[0];
                    return top ? top.name : '-';
                }
            },
            mounted() {
                this.fetchCustomers();
            },
            methods: {
                fetchCustomers() {
                    fetch('../api.php?action=get_crm_data')
                        .then(res => res.json())
                        .then(d => {
                            if(d.customers) {
                                this.customers = d.customers;
                            }
                        });
                },
                viewHistory(c) {
                    this.selectedCustomer = c;
                    this.isLoadingHistory = true;
                    fetch(`../api.php?action=get_customer_history&phone=${encodeURIComponent(c.phone)}`)
                        .then(res => res.json())
                        .then(d => {
                            this.historyData = d.history || [];
                            this.isLoadingHistory = false;
                        });
                },
                closeHistory() {
                    this.selectedCustomer = null;
                    this.historyData = [];
                },
                formatRupiah(n) { return new Intl.NumberFormat('id-ID', {style:'currency', currency:'IDR', minimumFractionDigits:0}).format(n||0); },
                formatDate(d) { 
                    if(!d) return '-'; 
                    return new Date(d).toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
                }
            }
        }).mount('#app');
    </script>
</body>
</html>
