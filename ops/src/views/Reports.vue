<template>
  <div class="h-full flex flex-col custom-scrollbar overflow-hidden">

    <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-800 dark:text-white">{{ currentReportTitle }}</h2>
            <p class="text-sm text-slate-500">Ringkasan Kinerja & Statistik</p>
        </div>
        
        <div class="flex items-center gap-3">

             <select v-model="selectedRoute" @change="fetchReports" class="bg-white dark:bg-slate-800 border-none rounded-xl text-xs font-bold px-3 py-1.5 outline-none text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700 shadow-sm h-full">
                <option value="">Semua Rute</option>
                <option value="Padang - Bukittinggi">Padang - Bukittinggi</option>
                <option value="Bukittinggi - Padang">Bukittinggi - Padang</option>
                <option value="Padang - Payakumbuh">Padang - Payakumbuh</option>
                <option value="Payakumbuh - Padang">Payakumbuh - Padang</option>
                <option value="Carter">Carter</option>
                <option value="Dropping">Dropping</option>
            </select>

             <div class="flex items-center gap-2 bg-white dark:bg-slate-800 p-1 rounded-xl border border-slate-200 dark:border-slate-700">
                <input v-if="period === 'daily' || period === 'weekly'" type="month" v-model="selectedMonth" @change="fetchReports" class="bg-transparent border-none text-xs font-bold text-slate-700 dark:text-slate-200 outline-none px-2 py-1">
                <select v-model="period" @change="fetchReports" class="bg-slate-100 dark:bg-slate-700 border-none rounded-lg text-xs font-bold px-3 py-1.5 outline-none text-slate-700 dark:text-slate-200">
                    <option value="daily">Harian</option>
                    <option value="weekly">Mingguan</option>
                    <option value="monthly">Bulanan</option>
                </select>
             </div>
        </div>
    </div>


     <div class="flex-1 overflow-y-auto pb-20 custom-scrollbar">

         <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
                <div class="text-[10px] font-bold text-slate-400 uppercase mb-1">Total Pendapatan (Global)</div>
                <div class="text-lg md:text-xl font-black text-blue-600 dark:text-blue-400">{{ formatRupiah(totalRevenue) }}</div>
                <div class="text-[10px] text-slate-400 mt-1">Travel + Paket + Refund</div>
            </div>
            <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
                <div class="text-[10px] font-bold text-slate-400 uppercase mb-1">Total Penumpang</div>
                <div class="text-lg md:text-xl font-black text-slate-800 dark:text-white">{{ totalPax }} <span class="text-xs font-medium text-slate-400">Org</span></div>
            </div>
             <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
                <div class="text-[10px] font-bold text-slate-400 uppercase mb-1">Periode</div>
                <div class="text-lg md:text-xl font-black text-slate-800 dark:text-white capitalize truncate">{{ periodLabel }}</div>
            </div>
         </div>

         <!-- Charts -->
         <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm h-80 relative">
                 <h3 class="font-bold text-slate-700 dark:text-slate-200 mb-4 text-sm">Grafik Pendapatan</h3>
                 <canvas id="revenueChart"></canvas>
            </div>
            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm h-80 relative">
                 <h3 class="font-bold text-slate-700 dark:text-slate-200 mb-4 text-sm">Grafik Penumpang</h3>
                 <canvas id="paxChart"></canvas>
            </div>
         </div>
         
         <!-- Route Summary Table (Moved from Dashboard) -->
         <div v-if="reportData.routeStats && reportData.routeStats.length > 0" class="mb-8">
            <h3 class="font-bold text-slate-700 dark:text-slate-200 text-sm mb-3">Ringkasan Rute (Periode Ini)</h3>
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-slate-500 uppercase bg-slate-50 dark:bg-slate-700/50 border-b border-slate-100 dark:border-slate-700">
                            <tr>
                                <th class="px-6 py-3 font-bold tracking-wider">Rute Group</th>
                                <th class="px-6 py-3 font-bold text-center tracking-wider">Penumpang</th>
                                <th class="px-6 py-3 font-bold text-right tracking-wider">Pendapatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 dark:divide-slate-700">
                            <tr v-for="(route, index) in reportData.routeStats" :key="index" class="hover:bg-slate-50/50 dark:hover:bg-slate-700/30 transition-colors">
                                <td class="px-6 py-4 font-bold text-slate-800 dark:text-white">{{ route.label || 'Tanpa Rute' }}</td>
                                <td class="px-6 py-4 text-center font-medium text-slate-600 dark:text-slate-300">{{ route.pax }}</td>
                                <td class="px-6 py-4 text-right font-bold text-slate-800 dark:text-blue-400 tracking-wide">{{ formatRupiah(route.revenue) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
         </div>

         <!-- Detailed Table -->
         <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100 dark:border-slate-700">
                <h3 class="font-bold text-slate-800 dark:text-white">Laporan Harian Detail</h3>
                <p class="text-xs text-slate-500">Klik baris tanggal untuk melihat rincian booking travel.</p>
            </div>
             <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-slate-500 uppercase bg-slate-50 dark:bg-slate-700/50">
                        <tr>
                            <th class="px-6 py-4">Tanggal</th>
                            <th class="px-4 py-4 text-right">Travel</th>
                            <th class="px-4 py-4 text-right">Paket</th>
                            <th class="px-4 py-4 text-right">Refund</th>
                            <th class="px-6 py-4 text-right font-bold text-blue-600">Total</th>
                            <th class="px-6 py-4 text-center">Kursi</th>
                            <th class="px-6 py-4 text-center">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                         <tr v-for="(label, index) in reversedLabels" :key="index" @click="openDetail(label)" class="hover:bg-blue-50 dark:hover:bg-slate-700/30 cursor-pointer transition-colors group">
                            <td class="px-6 py-4 font-bold text-slate-800 dark:text-white">
                                {{ formatDate(label) }}
                            </td>
                            <td class="px-4 py-4 text-right font-mono text-slate-600 dark:text-slate-400">
                                {{ formatRupiah(reversedTravelRevenue[index]) }}
                            </td>
                            <td class="px-4 py-4 text-right font-mono text-slate-600 dark:text-slate-400">
                                {{ formatRupiah(reversedPackageRevenue[index]) }}
                            </td>
                            <td class="px-4 py-4 text-right font-mono text-orange-500">
                                {{ formatRupiah(reversedRefundRevenue[index]) }}
                            </td>
                            <td class="px-6 py-4 text-right font-mono font-bold text-blue-600 dark:text-blue-400">
                                {{ formatRupiah(reversedRevenue[index]) }}
                            </td>
                             <td class="px-6 py-4 text-center font-bold text-slate-700 dark:text-slate-300">
                                {{ reversedPax[index] }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <button class="text-blue-600 bg-blue-50 dark:bg-slate-700 p-2 rounded-full opacity-0 group-hover:opacity-100 transition-opacity"><i class="bi bi-eye-fill"></i></button>
                            </td>
                         </tr>
                    </tbody>
                </table>
             </div>
         </div>
    </div>

    <!-- Detail Modal -->
    <div v-if="detailModal.isOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" @click.self="detailModal.isOpen = false">
        <div class="bg-white dark:bg-slate-800 w-full max-w-4xl max-h-[90vh] rounded-2xl shadow-xl flex flex-col animate-fade-in-up">
            <div class="p-4 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-slate-50 dark:bg-slate-800/50">
                <div>
                     <h3 class="font-bold text-lg text-slate-800 dark:text-white">Detail Transaksi Travel</h3>
                     <p class="text-xs text-slate-500">{{ formatDate(detailModal.date) }}</p>
                </div>
                <button @click="detailModal.isOpen = false" class="text-slate-400 hover:text-slate-600"><i class="bi bi-x-lg"></i></button>
            </div>
            
            <div class="p-6 overflow-y-auto custom-scrollbar flex-1">
                 <!-- Stats MiniCards in Modal -->
                 <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <div class="bg-blue-50 dark:bg-blue-900/20 p-3 rounded-xl border border-blue-100 dark:border-blue-800">
                        <div class="text-[10px] font-bold text-blue-500 uppercase">Omset Travel</div>
                        <div class="text-lg font-black text-slate-800 dark:text-white">{{ formatRupiah(detailModal.stats.travelRevenue) }}</div>
                    </div>
                     <div class="bg-green-50 dark:bg-green-900/20 p-3 rounded-xl border border-green-100 dark:border-green-800">
                        <div class="text-[10px] font-bold text-green-500 uppercase">Cash</div>
                        <div class="text-lg font-black text-slate-800 dark:text-white">{{ formatRupiah(detailModal.stats.revenueCash) }}</div>
                    </div>
                     <div class="bg-indigo-50 dark:bg-indigo-900/20 p-3 rounded-xl border border-indigo-100 dark:border-indigo-800">
                        <div class="text-[10px] font-bold text-indigo-500 uppercase">Transfer</div>
                        <div class="text-lg font-black text-slate-800 dark:text-white">{{ formatRupiah(detailModal.stats.revenueTransfer) }}</div>
                    </div>
                 </div>

                 <!-- Route Summary -->
                 <div v-if="groupedDetailBookings.length > 0" class="mb-6">
                    <h4 class="font-bold text-slate-700 dark:text-slate-200 text-sm mb-2">Ringkasan Rute</h4>
                    <div class="overflow-x-auto rounded-xl border border-slate-100 dark:border-slate-700">
                        <table class="w-full text-sm text-left">
                             <thead class="bg-slate-50 dark:bg-slate-700/50 text-xs text-slate-500 uppercase">
                                <tr>
                                    <th class="px-4 py-2">Rute Group</th>
                                    <th class="px-4 py-2 text-center">Penumpang</th>
                                    <th class="px-4 py-2 text-right">Pendapatan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                <tr v-for="g in groupedDetailBookings" :key="g.name" class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                                    <td class="px-4 py-2 font-bold dark:text-slate-200">{{ g.name }}</td>
                                    <td class="px-4 py-2 text-center">{{ g.count }}</td>
                                    <td class="px-4 py-2 text-right font-mono">{{ formatRupiah(g.revenue) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                 </div>

                <!-- Cancelled / Refund List -->
                 <div v-if="detailModal.cancelled.length > 0" class="mb-6">
                    <h4 class="font-bold text-red-600 dark:text-red-400 text-sm mb-2">Riwayat Pembatalan / Refund</h4>
                    <div class="overflow-x-auto rounded-xl border border-red-100 dark:border-red-900/30">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-red-50 dark:bg-red-900/20 text-xs text-red-600 dark:text-red-400 uppercase">
                                <tr>
                                    <th class="px-4 py-2">Waktu</th>
                                    <th class="px-4 py-2">Nama</th>
                                    <th class="px-4 py-2 text-center">Kursi</th>
                                    <th class="px-4 py-2 text-right">Refund</th>
                                    <th class="px-4 py-2 text-right">Potongan</th>
                                    <th class="px-4 py-2 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-red-50 dark:divide-red-900/20">
                                <tr v-for="c in detailModal.cancelled" :key="c.id" class="hover:bg-red-50/50 dark:hover:bg-red-900/10">
                                    <td class="px-4 py-2 text-slate-500 font-mono">{{ c.time }}</td>
                                    <td class="px-4 py-2 font-bold text-slate-700 dark:text-slate-200">{{ c.passengerName }}</td>
                                    <td class="px-4 py-2 text-center font-bold text-slate-700 dark:text-slate-200">{{ c.seatCount }}</td>
                                    <td class="px-4 py-2 text-right font-mono text-slate-600">{{ formatRupiah(c.refund_amount) }}</td> 
                                    <td class="px-4 py-2 text-right font-mono font-bold text-red-600">{{ formatRupiah((c.totalPrice || c.downPaymentAmount) - c.refund_amount) }}</td>
                                    <td class="px-4 py-2 text-center">
                                        <span class="px-2 py-1 rounded bg-red-100 text-red-700 font-bold text-xs uppercase">{{ c.reason || 'Batal' }}</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                 </div>

                 <!-- Loader -->
                 <div v-if="detailModal.isLoading" class="flex justify-center py-10">
                    <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-600"></div>
                 </div>

                 <!-- List -->
                 <div v-else class="overflow-x-auto rounded-xl border border-slate-100 dark:border-slate-700">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-slate-50 dark:bg-slate-700/50 text-xs text-slate-500 uppercase">
                            <tr>
                                <th class="px-4 py-3">Waktu</th>
                                <th class="px-4 py-3">Rute</th>
                                <th class="px-4 py-3">Penumpang</th>
                                <th class="px-4 py-3 text-center">Kursi</th>
                                <th class="px-4 py-3 text-right">Total</th>
                                <th class="px-4 py-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            <tr v-for="b in detailModal.bookings" :key="b.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                                <td class="px-4 py-3 font-mono text-slate-500">{{ b.time }}</td>
                                <td class="px-4 py-3 text-[12px] dark:text-slate-200">{{ b.routeName }}</td>
                                <td class="px-4 py-3">
                                    <div class="font-bold text-slate-700 text-[12px] dark:text-white">{{ b.passengerName }}</div>
                                    <div v-if="b.paymentStatus !== 'Lunas'" class="text-[10px] text-red-500 font-bold">Belum Lunas</div>
                                </td>
                                 <td class="px-4 py-3 text-center">
                                    <div class="text-xs font-bold">{{ b.seatNumbers }}</div>
                                    <div class="text-[10px] text-slate-400">{{ b.seatCount }} Kursi</div>
                                </td>
                                <td class="px-4 py-3 text-right font-bold font-mono">{{ formatRupiah(b.totalPrice) }}</td>
                                <td class="px-4 py-3 text-center text-xs">
                                     <span v-if="b.paymentStatus === 'Lunas'" class="px-2 py-1 rounded bg-green-100 text-green-700 font-bold">{{ b.paymentMethod }}</span>
                                     <span v-else class="px-2 py-1 rounded bg-red-100 text-red-700 font-bold">Belum Lunas</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                 </div>
            </div>
        </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, nextTick } from 'vue';
import Chart from 'chart.js/auto';
import axios from 'axios';

const period = ref('daily');
const selectedMonth = ref(new Date().toISOString().slice(0, 7));
const selectedRoute = ref(''); // New Route Filter
const reportData = ref({ 
    labels: [], 
    revenue: [], // Total
    travelRevenue: [], // Travel
    packageRevenue: [], // Pkg
    refundRevenue: [], // Refund
    pax: [], 
    revenueCash: [], 
    revenueTransfer: [], 
    revenueCash: [], 
    revenueTransfer: [], 
    unpaidAmount: [],
    routeStats: [] // NEW
});
const currentReportTitle = ref('Statistik Universal');

const detailModal = ref({
    isOpen: false,
    date: null,
    isLoading: false,
    bookings: [],
    cancelled: [], // NEW
    stats: { revenue: 0, travelRevenue: 0, revenueCash: 0, revenueTransfer: 0, unpaidAmount: 0 }
});

const charts = { revenue: null, pax: null };

// Computed Stats
const totalRevenue = computed(() => reportData.value.revenue.reduce((a, b) => a + b, 0));
const totalPax = computed(() => reportData.value.pax.reduce((a, b) => a + b, 0));
const totalUnpaidAmount = computed(() => (reportData.value.unpaidAmount || []).reduce((a, b) => a + b, 0));
const totalUnpaid = computed(() => (reportData.value.unpaid || []).reduce((a, b) => a + b, 0));

// Reversed for Table (Newest First)
const reversedLabels = computed(() => [...reportData.value.labels].reverse());
const reversedRevenue = computed(() => [...reportData.value.revenue].reverse()); // Total
const reversedTravelRevenue = computed(() => [...(reportData.value.travelRevenue || [])].reverse());
const reversedPackageRevenue = computed(() => [...(reportData.value.packageRevenue || [])].reverse());
const reversedRefundRevenue = computed(() => [...(reportData.value.refundRevenue || [])].reverse());

const reversedPax = computed(() => [...reportData.value.pax].reverse());

const periodLabel = computed(() => {
    if (period.value === 'daily' || period.value === 'weekly') {
        const d = new Date(selectedMonth.value + '-01');
        return (period.value === 'weekly' ? 'Mingguan - ' : '') + d.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });
    }
    return 'Tahunan';
});

// Grouping Logic for Detail Modal
// Helper to determine Group Name
// Helper to determine Group Name
const getRouteGroup = (name) => {
    if (!name) return 'Tanpa Rute';
    const n = name.toLowerCase();

    if (n.includes('carter')) return 'Carter';
    if (n.includes('dropping')) return 'Dropping';
    
    // Just return original name without class info
    // Remove " (Normal)", " (Executive)", " (Royal)" etc
    return name.replace(/\s*\((Normal|Ekonomi|Executive|Royal|Sutan|Bisnis)\)/i, '').trim(); 
};

// Grouping Logic for Detail Modal
const groupedDetailBookings = computed(() => {
    if (!detailModal.value.bookings || detailModal.value.bookings.length === 0) return [];
    
    const groups = {};

    detailModal.value.bookings.forEach(b => {
        const rawName = b.routeName || '';
        const groupName = getRouteGroup(rawName);
        
        if (!groups[groupName]) groups[groupName] = { count: 0, revenue: 0 };
        
        groups[groupName].count += (b.seatCount || 0);
        groups[groupName].revenue += (b.totalPrice || 0);
    });

    // Sort by Revenue Descending
    return Object.entries(groups)
        .map(([name, val]) => ({ name, ...val }))
        .sort((a, b) => b.revenue - a.revenue);
});

const formatRupiah = (val) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val || 0);

const formatDate = (d) => {
    if (!d) return '-';
    // If Weekly string (e.g. "Minggu 1")
    if (d.toString().includes('Minggu')) return d;

    // If YYYY-MM
    if (d.length === 7) {
        const date = new Date(d + '-01');
        return date.toLocaleDateString('id-ID', { year: 'numeric', month: 'long' });
    }
    // If YYYY-MM-DD
    const date = new Date(d);
    return date.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
};

const fetchReports = async () => {
    try {
        const monthParam = (period.value === 'daily' || period.value === 'weekly') ? `&month=${selectedMonth.value}` : '';
        const routeParam = selectedRoute.value ? `&routeKeyword=${encodeURIComponent(selectedRoute.value)}` : '';
        const res = await axios.get(`api.php?action=get_reports&period=${period.value}${monthParam}${routeParam}`);
        
        if (res.data.reports) {
            reportData.value = res.data.reports;
            // Ensure routeStats is initialized if missing
            if (!reportData.value.routeStats) reportData.value.routeStats = [];
            nextTick(() => updateCharts());
        }
    } catch (e) {
        console.error(e);
    }
};

const updateCharts = () => {
    const ctxRev = document.getElementById('revenueChart');
    const ctxPax = document.getElementById('paxChart');

    if (!ctxRev || !ctxPax) return;

    if (charts.revenue) charts.revenue.destroy();
    if (charts.pax) charts.pax.destroy();

    const formatCurrencyAxis = (value) => {
        if (value >= 1000000000) return (value / 1000000000).toFixed(1).replace(/\.0$/, '') + ' M';
        if (value >= 1000000) return (value / 1000000).toFixed(2).replace(/\.00$/, '').replace(/\.0$/, '') + ' jt';
        if (value >= 1000) return (value / 1000).toFixed(0) + 'k';
        return value;
    };

    const formatDateAxis = (val) => {
        // Expecting YYYY-MM-DD
        if (!val || val.length < 10) return val;
        const parts = val.split('-');
        if (parts.length === 3) {
            const d = new Date(val);
            return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short' }); // "25 Jan"
        }
        return val;
    };

    const commonOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            y: { 
                beginAtZero: true, 
                grid: { color: '#e2e8f0' },
                ticks: {
                    callback: formatCurrencyAxis
                }
            },
            x: { 
                grid: { display: false },
                ticks: {
                    maxRotation: 90,
                    minRotation: 90,
                     callback: function(value, index, values) {
                        try {
                             return formatDateAxis(this.getLabelForValue(value));
                        } catch (e) { return value; }
                    }
                }
            }
        }
    };
    
    // Pax chart doesn't need currency formatting on Y
    const paxOptions = {
        ...commonOptions,
        scales: {
            ...commonOptions.scales,
            y: {
                ...commonOptions.scales.y,
                ticks: { callback: (val) => val } // Normal numbers for pax
            }
        }
    };

    charts.revenue = new Chart(ctxRev, {
        type: 'line',
        data: {
            labels: reportData.value.labels,
            datasets: [{
                label: 'Pendapatan',
                data: reportData.value.revenue,
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37, 99, 235, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: commonOptions
    });

    charts.pax = new Chart(ctxPax, {
        type: 'bar',
        data: {
            labels: reportData.value.labels,
            datasets: [{
                label: 'Penumpang',
                data: reportData.value.pax,
                backgroundColor: '#d4af37',
                borderRadius: 4
            }]
        },
        options: paxOptions
    });
};

const openDetail = async (date) => {
    detailModal.value.date = date;
    detailModal.value.bookings = [];
    detailModal.value.cancelled = []; // NEW
    detailModal.value.isLoading = true;
    detailModal.value.isOpen = true;

    // Find stats from existing data
    const idx = reportData.value.labels.indexOf(date);
    if (idx !== -1) {
        detailModal.value.stats = {
            revenue: reportData.value.revenue[idx],
            travelRevenue: (reportData.value.travelRevenue || [])[idx] || 0,
            revenueCash: (reportData.value.revenueCash || [])[idx] || 0,
            revenueTransfer: (reportData.value.revenueTransfer || [])[idx] || 0,
            unpaidAmount: (reportData.value.unpaidAmount || [])[idx] || 0
        };
    }

    try {
        const routeParam = selectedRoute.value ? `&routeKeyword=${encodeURIComponent(selectedRoute.value)}` : '';
        const res = await axios.get(`api.php?action=get_report_details&date=${date}${routeParam}`);
        if (res.data.bookings) {
            detailModal.value.bookings = res.data.bookings;
        }
        if (res.data.cancelled) {
            detailModal.value.cancelled = res.data.cancelled;
        }
    } catch (e) {
        console.error(e);
    } finally {
        detailModal.value.isLoading = false;
    }
};

onMounted(() => {
    fetchReports();
});
</script>
