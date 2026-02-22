<template>
  <div class="h-full flex flex-col">
    <!-- Filter Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
         <!-- Summary Title -->
         <div>
            <h3 class="text-xs sm:text-sm font-bold text-slate-500 uppercase tracking-wider">Ringkasan Hari Ini</h3>
             <div class="text-[10px] text-slate-400 font-medium mt-0.5">Data Realtime</div>
        </div>

        <!-- Filter Controls -->
        <div class="flex items-center gap-2 bg-white p-1 rounded-lg border border-slate-200 shadow-sm">
             <div class="flex bg-slate-100 rounded-md p-0.5">
                 <button 
                    @click="setFilterType('month')" 
                    class="px-3 py-1.5 text-xs font-bold rounded-md transition-all"
                    :class="filterType === 'month' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-500 hover:text-slate-700'"
                 >
                    Bulanan
                 </button>
                 <button 
                    @click="setFilterType('year')" 
                    class="px-3 py-1.5 text-xs font-bold rounded-md transition-all"
                    :class="filterType === 'year' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-500 hover:text-slate-700'"
                 >
                    Tahunan
                 </button>
             </div>
             
             <div class="h-full w-px bg-slate-200 mx-1"></div>

             <div v-if="filterType === 'month'">
                 <input type="month" v-model="selectedDate" @change="fetchData" class="text-xs font-bold text-slate-700 border-none focus:ring-0 bg-transparent py-1 pl-1 pr-2 w-32 cursor-pointer">
             </div>
             <div v-else>
                 <select v-model="selectedDate" @change="fetchData" class="text-xs font-bold text-slate-700 border-none focus:ring-0 bg-transparent py-1 pl-1 pr-6 cursor-pointer">
                     <option v-for="year in availableYears" :key="year" :value="year">{{ year }}</option>
                 </select>
             </div>
             
             <button @click="fetchData" class="p-1.5 text-slate-400 hover:text-blue-600 transition-colors">
                 <i class="bi bi-arrow-clockwise text-lg" :class="{'animate-spin': isLoading}"></i>
             </button>
        </div>
    </div>

    <div v-if="isLoading && !stats.today" class="flex-1 flex items-center justify-center min-h-[400px]">
        <div class="flex flex-col items-center gap-3">
            <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-600"></div>
            <p class="text-slate-500 text-sm font-medium">Memuat data dashboard...</p>
        </div>
    </div>

    <div v-else class="space-y-6 animate-fade-in-up">
        <!-- Today Summary Section -->
        <div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6">
            <!-- Today Revenue -->
            <div class="col-span-2 sm:col-span-1 bg-white p-3 sm:p-5 rounded-xl border border-slate-100 shadow-sm relative group">
                <div class="flex justify-between items-center mb-2">
                    <div>
                        <div class="text-[10px] sm:text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Pendapatan Hari Ini</div>
                        <div class="text-xl sm:text-2xl font-extrabold text-slate-800">{{ formatRupiah(stats.today?.revenue || 0) }}</div>
                    </div>
                    <div class="w-10 h-10 flex items-center justify-center bg-blue-50 text-blue-600 rounded-lg text-xl">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                </div>
                <!-- Breakdown -->
                <div class="text-[10px] text-slate-500 space-y-0.5 pt-2 border-t border-slate-50 mt-1">
                    <div class="flex justify-between"><span>Travel:</span> <span class="font-bold">{{ formatRupiah(stats.today?.breakdown?.travel || 0) }}</span></div>
                    <div class="flex justify-between"><span>Paket:</span> <span class="font-bold">{{ formatRupiah(stats.today?.breakdown?.package || 0) }}</span></div>
                    <div class="flex justify-between"><span>Refund:</span> <span class="font-bold">{{ formatRupiah(stats.today?.breakdown?.refund || 0) }}</span></div>
                </div>
            </div>

            <!-- Today Pax -->
            <div class="bg-white p-3 sm:p-5 rounded-xl border border-slate-100 shadow-sm flex flex-col justify-center">
                 <div class="flex justify-between items-center">
                    <div>
                        <div class="text-[10px] sm:text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Penumpang Hari Ini</div>
                        <div class="text-xl sm:text-2xl font-extrabold text-slate-800">{{ stats.today?.pax || 0 }}</div>
                    </div>
                    <div class="w-10 h-10 flex items-center justify-center bg-indigo-50 text-indigo-600 rounded-lg text-xl">
                        <i class="bi bi-people-fill"></i>
                    </div>
                </div>
                 <div class="text-[10px] text-slate-500 pt-2 mt-auto">
                    Menunggu Validasi: <span class="font-bold text-orange-500">{{ stats.today?.pendingValidation || 0 }}</span>
                </div>
            </div>

        </div>

        <!-- Period Overview -->
        <div>
            <div class="flex items-end gap-2 mb-2 sm:mb-4">
                <h3 class="text-xs sm:text-sm font-bold text-slate-500 uppercase tracking-wider">Laporan Periode</h3>
                <span class="text-xs font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded uppercase">{{ displayPeriodLabel }}</span>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 sm:gap-6">
                <!-- Total Revenue Period -->
                <div class="bg-gradient-to-br from-blue-600 to-blue-800 p-6 rounded-xl shadow-lg shadow-blue-500/20 text-white relative overflow-hidden group">
                     <div class="relative z-10">
                        <div class="text-blue-100 text-xs font-bold uppercase mb-2">Total Pendapatan</div>
                        <div class="text-3xl font-extrabold mb-3">{{ formatRupiah(stats.period?.revenue || 0) }}</div>
                        
                         <!-- Breakdown -->
                        <div class="text-xs text-blue-100/90 space-y-1 bg-white/10 p-2 rounded-lg backdrop-blur-sm">
                            <div class="flex justify-between"><span>Travel:</span> <span class="font-bold">{{ formatRupiah(stats.period?.breakdown?.travel || 0) }}</span></div>
                            <div class="flex justify-between"><span>Paket:</span> <span class="font-bold">{{ formatRupiah(stats.period?.breakdown?.package || 0) }}</span></div>
                            <div class="flex justify-between"><span>Refund:</span> <span class="font-bold">{{ formatRupiah(stats.period?.breakdown?.refund || 0) }}</span></div>
                        </div>
                    </div>
                    <i class="bi bi-graph-up absolute bottom-[-10px] right-[-10px] text-8xl text-white/10 group-hover:scale-110 transition-transform"></i>
                </div>

                <!-- Total Pax Period -->
                <div class="bg-white p-6 rounded-xl border border-slate-100 shadow-sm flex flex-col justify-center">
                    <div class="text-xs font-bold text-slate-400 uppercase mb-2">Total Penumpang</div>
                    <div class="flex items-end gap-2">
                        <div class="text-3xl font-extrabold text-slate-800">{{ stats.period?.pax || 0 }}</div>
                        <div class="text-sm font-bold text-slate-500 mb-1.5">Kursi Terjual</div>
                    </div>
                     <div class="mt-4 pt-4 border-t border-slate-50">
                        <div class="text-xs text-slate-500">
                             Total akumulasi penumpang untuk periode yang dipilih.
                        </div>
                    </div>
                </div>

                <!-- Empty Slot or Additional Stat -->
                 <div class="bg-white p-6 rounded-xl border border-slate-100 shadow-sm flex flex-col justify-center items-center text-center opacity-50">
                    <i class="bi bi-pie-chart text-4xl text-slate-300 mb-2"></i>
                    <div class="text-xs font-bold text-slate-400">Analisis Lanjutan Segera Hadir</div>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white p-6 rounded-xl border border-slate-100 shadow-sm">
                <div class="flex justify-between items-center mb-4">
                     <h3 class="text-xs sm:text-sm font-bold text-slate-500 uppercase tracking-wider">Tren Pendapatan</h3>
                     <!-- Legend or Filter Hint -->
                </div>
                <div class="h-64 sm:h-80 w-full relative">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
            
             <div class="bg-white p-6 rounded-xl border border-slate-100 shadow-sm">
                <h3 class="text-xs sm:text-sm font-bold text-slate-500 uppercase tracking-wider mb-4">Tren Penumpang</h3>
                <div class="h-64 sm:h-80 w-full relative">
                    <canvas id="paxChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Pie Charts -->
        <h3 class="text-xs sm:text-sm font-bold text-slate-500 uppercase tracking-wider mb-2 mt-2">Distribusi Per Rute (Total Periode)</h3>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 pb-10">
            <div class="bg-white p-6 rounded-xl border border-slate-100 shadow-sm">
                <h3 class="text-xs sm:text-sm font-bold text-slate-500 uppercase tracking-wider mb-4">Pendapatan per Rute</h3>
                <div class="h-64 sm:h-72 w-full relative flex justify-center">
                    <canvas id="pieRevenueChart"></canvas>
                </div>
            </div>
            
             <div class="bg-white p-6 rounded-xl border border-slate-100 shadow-sm">
                <h3 class="text-xs sm:text-sm font-bold text-slate-500 uppercase tracking-wider mb-4">Penumpang per Rute</h3>
                 <div class="h-64 sm:h-72 w-full relative flex justify-center">
                    <canvas id="piePaxChart"></canvas>
                </div>
            </div>
        </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import axios from 'axios';
import Chart from 'chart.js/auto';
import ChartDataLabels from 'chartjs-plugin-datalabels';

// State
const isLoading = ref(true);
const filterType = ref('month'); // 'month' or 'year'
const selectedDate = ref(''); // YYYY-MM or YYYY

// Constants
const availableYears = [2024, 2025, 2026, 2027];

// Format Date Init
const today = new Date();
const currentYear = today.getFullYear();
const currentMonth = String(today.getMonth() + 1).padStart(2, '0');
selectedDate.value = `${currentYear}-${currentMonth}`; // Default YYYY-MM

const setFilterType = (type) => {
    filterType.value = type;
    if (type === 'year') {
        selectedDate.value = currentYear;
    } else {
        selectedDate.value = `${currentYear}-${currentMonth}`;
    }
    fetchData();
};

const displayPeriodLabel = computed(() => {
    if (filterType.value === 'year') {
        return `Tahun ${selectedDate.value}`;
    }
    // Format YYYY-MM to Month Name YYYY
    if (!selectedDate.value) return '-';
    const [y, m] = selectedDate.value.split('-');
    const date = new Date(y, m - 1);
    return date.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });
});

const stats = ref({
    today: { revenue: 0, pax: 0, pendingValidation: 0 },
    period: { revenue: 0, pax: 0 }, // Was 'month'
    unpaid: { amount: 0, count: 0 },
    pendingDispatch: 0,
    graph: { labels: [], revenue: [], pax: [] },
    pie_stats: { labels: [], revenue: [], pax: [], colors: [] }
});

const formatRupiah = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(value);
};

// Chart Instances
let revenueChartInstance = null;
let paxChartInstance = null;
let pieRevenueChartInstance = null;
let piePaxChartInstance = null;

const initCharts = () => {
    const ctxRevenue = document.getElementById('revenueChart');
    const ctxPax = document.getElementById('paxChart');
    const ctxPieRevenue = document.getElementById('pieRevenueChart');
    const ctxPiePax = document.getElementById('piePaxChart');

    if (revenueChartInstance) revenueChartInstance.destroy();
    if (paxChartInstance) paxChartInstance.destroy();
    if (pieRevenueChartInstance) pieRevenueChartInstance.destroy();
    if (piePaxChartInstance) piePaxChartInstance.destroy();

    // Chart Options Base
    const sharedOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { 
            legend: { display: false },
            datalabels: { display: false } // Disable datalabels for line/bar charts
        },
        scales: {
            y: { 
                beginAtZero: true, 
                grid: { color: '#f1f5f9' },
                ticks: {
                        callback: function(value) { 
                        if (value >= 1000000) return (value/1000000).toLocaleString('id-ID') + ' jt';
                        return (value/1000).toLocaleString('id-ID') + 'k'; 
                    }
                }
            },
                x: { grid: { display: false } }
        }
    };
    
    // Revenue Chart (Line or Bar depending on view? Line is fine for trend)
    if (ctxRevenue) {
        revenueChartInstance = new Chart(ctxRevenue, {
            type: 'line',
            data: {
                labels: stats.value.graph.labels,
                datasets: [{
                    label: 'Pendapatan',
                    data: stats.value.graph.revenue,
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37, 99, 235, 0.1)',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: sharedOptions
        });
    }

    // Pax Chart (Bar for Monthly Distribution helps visual separation)
    if (ctxPax) {
        // Adjust ticks for Pax (Integers)
        const paxOptions = JSON.parse(JSON.stringify(sharedOptions)); // Deep clone
        paxOptions.scales.y.ticks = { precision: 0 };
        
        paxChartInstance = new Chart(ctxPax, {
            type: 'bar',
            data: {
                labels: stats.value.graph.labels,
                datasets: [{
                    label: 'Penumpang',
                    data: stats.value.graph.pax,
                    backgroundColor: '#d4af37',
                    borderRadius: 4
                }]
            },
            options: paxOptions
        });
    }

    // Rich/Darker Palette for Pie
    const richColors = [
        '#2563eb', '#dc2626', '#16a34a', '#d97706', '#9333ea', '#0891b2', 
        '#db2777', '#4f46e5', '#ca8a04', '#0d9488', '#be123c', '#1e293b'
    ];
    
    // Detailed Mapping for Specific Routes (Optional, ensures specific colors for key routes)
    const routeColorMap = {
        'Padang - Bukittinggi': '#2563eb', 
        'Bukittinggi - Padang': '#dc2626',
        'Padang - Payakumbuh': '#16a34a',
        'Payakumbuh - Padang': '#d97706',
        'Carter': '#9333ea',
        'Dropping': '#0891b2'
    };

    const getRouteColor = (label, index) => {
        if (routeColorMap[label]) return routeColorMap[label];
        // Hash string to pick consistency from richColors if not mapped
        let hash = 0;
        for (let i = 0; i < label.length; i++) hash = label.charCodeAt(i) + ((hash << 5) - hash);
        const colorIndex = Math.abs(hash) % richColors.length;
        return richColors[colorIndex];
    };

    const adjustColorOpacity = (hex, alpha) => {
        const r = parseInt(hex.slice(1, 3), 16);
        const g = parseInt(hex.slice(3, 5), 16);
        const b = parseInt(hex.slice(5, 7), 16);
        return `rgba(${r}, ${g}, ${b}, ${alpha})`;
    };

    // Pie Chart: Revenue
    if (ctxPieRevenue && stats.value.pie_stats) {
        const labels = stats.value.pie_stats.labels;
        const backgroundColors = labels.map((label, i) => {
             const color = getRouteColor(label, i);
             const grd = ctxPieRevenue.getContext('2d').createLinearGradient(0, 0, 0, 300);
             grd.addColorStop(0, color);
             grd.addColorStop(1, adjustColorOpacity(color, 0.7));
             return grd;
        });

        pieRevenueChartInstance = new Chart(ctxPieRevenue, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: stats.value.pie_stats.revenue,
                    backgroundColor: backgroundColors,
                    borderColor: '#ffffff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'right', labels: { usePointStyle: true, boxWidth: 10, font: { size: 10 } } },
                    datalabels: {
                        color: '#ffffff',
                        font: { weight: 'bold', size: 14 },
                        formatter: (value, ctx) => {
                            const sum = ctx.dataset.data.reduce((a, b) => a + b, 0);
                            return ((value / sum) * 100).toFixed(1) + '%';
                        }
                    }
                }
            },
            plugins: [ChartDataLabels]
        });
    }

    // Pie Chart: Pax
    if (ctxPiePax && stats.value.pie_stats) {
        const labels = stats.value.pie_stats.labels;
        const backgroundColors = labels.map((label, i) => {
             const color = getRouteColor(label, i);
             const grd = ctxPiePax.getContext('2d').createLinearGradient(0, 0, 0, 300);
             grd.addColorStop(0, color);
             grd.addColorStop(1, adjustColorOpacity(color, 0.7));
             return grd;
        });

        piePaxChartInstance = new Chart(ctxPiePax, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    data: stats.value.pie_stats.pax,
                    backgroundColor: backgroundColors,
                    borderColor: '#ffffff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'right', labels: { usePointStyle: true, boxWidth: 10, font: { size: 10 } } },
                    datalabels: {
                        color: '#ffffff',
                        font: {
                            weight: 'bold',
                            size: 14
                        },
                        formatter: (value, ctx) => {
                            const sum = ctx.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / sum) * 100).toFixed(1);
                            return percentage + '%';
                        }
                    }
                }
            },
            plugins: [ChartDataLabels]
        });
    }
};

const fetchData = async () => {
    isLoading.value = true;
    try {
        const response = await axios.get('api.php', {
            params: {
                action: 'get_dashboard_summary',
                filterType: filterType.value,
                date: selectedDate.value
            }
        });
        
        if (response.data.status === 'success') {
            stats.value = response.data.data;
            // Initialize charts after data load & DOM update
            setTimeout(initCharts, 100);
        } else {
             console.error("API Error Status:", response.data);
        }
    } catch (e) {
        console.error("Failed to load dashboard data", e);
    } finally {
        isLoading.value = false;
    }
};

onMounted(() => {
    fetchData();
});
</script>
