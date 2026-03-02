<template>
  <div class="space-y-8 animate-fade-in">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <!-- Stats Cards -->
          <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100/50 hover:shadow-xl hover:shadow-slate-200/50 transition-all duration-300 group relative overflow-hidden">
              <div class="absolute top-0 right-0 w-24 h-24 bg-blue-50 rounded-bl-[4rem] transition-transform group-hover:scale-110"></div>
              <div class="relative">
                  <div class="w-12 h-12 bg-blue-50 text-blue-500 rounded-2xl flex items-center justify-center text-xl mb-4 group-hover:scale-110 transition-transform duration-300 shadow-sm"><i class="bi bi-people-fill"></i></div>
                  <div class="text-4xl font-black text-slate-800 tracking-tight mb-1">{{ stats.totalCustomers }}</div>
                  <div class="text-sm font-bold text-slate-400 flex items-center gap-2">Total Konsumen <span class="bg-emerald-50 text-emerald-600 text-[10px] px-2 py-0.5 rounded-full">+{{ stats.newCustomers }} Baru</span></div>
              </div>
          </div>

          <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100/50 hover:shadow-xl hover:shadow-slate-200/50 transition-all duration-300 group relative overflow-hidden">
              <div class="absolute top-0 right-0 w-24 h-24 bg-amber-50 rounded-bl-[4rem] transition-transform group-hover:scale-110"></div>
              <div class="relative">
                  <div class="w-12 h-12 bg-amber-50 text-amber-500 rounded-2xl flex items-center justify-center text-xl mb-4 group-hover:scale-110 transition-transform duration-300 shadow-sm"><i class="bi bi-wallet2"></i></div>
                  <div class="text-4xl font-black text-slate-800 tracking-tight mb-1">{{ formatRupiahSimple(stats.totalRevenue) }}</div>
                  <div class="text-sm font-bold text-slate-400">Total Pendapatan</div>
              </div>
          </div>

          <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100/50 hover:shadow-xl hover:shadow-slate-200/50 transition-all duration-300 group relative overflow-hidden">
              <div class="absolute top-0 right-0 w-24 h-24 bg-purple-50 rounded-bl-[4rem] transition-transform group-hover:scale-110"></div>
              <div class="relative">
                  <div class="w-12 h-12 bg-purple-50 text-purple-500 rounded-2xl flex items-center justify-center text-xl mb-4 group-hover:scale-110 transition-transform duration-300 shadow-sm"><i class="bi bi-arrow-repeat"></i></div>
                  <div class="text-4xl font-black text-slate-800 tracking-tight mb-1">{{ stats.repeatRate }}%</div>
                  <div class="text-sm font-bold text-slate-400">Repeat Order Rate</div>
              </div>
          </div>
      </div>

      <!-- CHAMPIONS SECTION -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <!-- Sultan (Top Spender) -->
          <div v-if="analytics.champion_revenue" class="bg-gradient-to-br from-slate-900 to-slate-800 p-6 rounded-[2rem] shadow-xl text-white relative overflow-hidden group">
              <div class="absolute top-0 right-0 w-32 h-32 bg-gold/10 rounded-full blur-2xl group-hover:bg-gold/20 transition-all"></div>
              <div class="relative z-10">
                  <div class="flex justify-between items-start mb-4">
                      <div class="w-12 h-12 bg-white/10 rounded-2xl flex items-center justify-center text-2xl">🏆</div>
                      <span class="text-[10px] font-bold bg-gold/20 text-gold px-2 py-1 rounded-lg uppercase tracking-wider">The Sultan</span>
                  </div>
                  <div class="text-2xl font-bold mb-1 truncate">{{ analytics.champion_revenue.name }}</div>
                  <div class="text-white/60 text-sm font-medium mb-4">{{ normalizePhone(analytics.champion_revenue.phone) }}</div>
                  <div class="text-3xl font-black text-gold tracking-tight">{{ formatRupiahSimple(analytics.champion_revenue.total) }}</div>
              </div>
          </div>

          <!-- King of Road (Most Trips) -->
          <div v-if="analytics.champion_trips" class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100 relative overflow-hidden group hover:shadow-lg transition-all">
              <div class="absolute top-0 right-0 w-24 h-24 bg-blue-50 rounded-bl-[4rem]"></div>
              <div class="relative z-10">
                  <div class="flex justify-between items-start mb-4">
                      <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center text-2xl">👑</div>
                      <span class="text-[10px] font-bold bg-blue-50 text-blue-600 px-2 py-1 rounded-lg uppercase tracking-wider">Raja Jalanan</span>
                  </div>
                  <div class="text-2xl font-bold text-slate-800 mb-1 truncate">{{ analytics.champion_trips.name }}</div>
                   <div class="text-slate-400 text-sm font-medium mb-4">{{ normalizePhone(analytics.champion_trips.phone) }}</div>
                  <div class="text-3xl font-black text-slate-800 tracking-tight">{{ analytics.champion_trips.total }} <span class="text-lg font-bold text-slate-400">Trips</span></div>
              </div>
          </div>

          <!-- Mass Coordinator (Most Seats) -->
          <div v-if="analytics.champion_seats" class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100 relative overflow-hidden group hover:shadow-lg transition-all">
              <div class="absolute top-0 right-0 w-24 h-24 bg-emerald-50 rounded-bl-[4rem]"></div>
              <div class="relative z-10">
                  <div class="flex justify-between items-start mb-4">
                      <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center text-2xl">🚌</div>
                      <span class="text-[10px] font-bold bg-emerald-50 text-emerald-600 px-2 py-1 rounded-lg uppercase tracking-wider">Koordinator</span>
                  </div>
                  <div class="text-2xl font-bold text-slate-800 mb-1 truncate">{{ analytics.champion_seats.name }}</div>
                  <div class="text-slate-400 text-sm font-medium mb-4">{{ normalizePhone(analytics.champion_seats.phone) }}</div>
                  <div class="text-3xl font-black text-slate-800 tracking-tight">{{ analytics.champion_seats.total }} <span class="text-lg font-bold text-slate-400">Kursi</span></div>
              </div>
          </div>
      </div>

       <!-- CHARTS SECTION -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100">
              <h3 class="font-bold text-slate-800 mb-6 flex items-center gap-2">
                  <span class="w-1.5 h-6 bg-gold rounded-full"></span> Pertumbuhan Pelanggan
              </h3>
              <canvas id="growthChart" height="200"></canvas>
          </div>
          <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100">
              <h3 class="font-bold text-slate-800 mb-6 flex items-center gap-2">
                  <span class="w-1.5 h-6 bg-slate-800 rounded-full"></span> Profil Penumpang
              </h3>
              <div class="h-64 flex justify-center">
                  <canvas id="demoChart"></canvas>
              </div>
          </div>
      </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../api/axios';

const stats = ref({
    totalCustomers: 0,
    totalRevenue: 0,
    newCustomers: 0,
    repeatRate: 0
});

const analytics = ref({
    champion_revenue: null,
    champion_trips: null,
    champion_seats: null,
    demographics: [],
    growth: []
});

const formatRupiahSimple = (val) => {
    if (!val) return 'Rp 0';
    if (val >= 1000000000) return 'Rp ' + (val / 1000000000).toFixed(1) + ' M';
    if (val >= 1000000) return 'Rp ' + (val / 1000000).toFixed(1) + ' jt';
    return 'Rp ' + (val / 1000).toFixed(0) + ' rb';
};

const normalizePhone = (phone) => {
    if(!phone) return '-';
    return phone.replace(/(\d{4})(\d{4})(\d+)/, '$1-$2-$3');
};

const fetchStats = async () => {
    try {
        const res = await api.get('/dashboard/stats');
        if (res.data) {
            stats.value = res.data;
        }
    } catch (e) {
        console.error(e);
    }
};

const fetchAnalytics = async () => {
    try {
        const res = await api.get('/analytics');
        if (res.data) {
            analytics.value = res.data;
            renderCharts();
        }
    } catch (e) {
        console.error(e);
    }
}

const renderCharts = () => {
    // Growth Chart
    const ctxGrowth = document.getElementById('growthChart');
    if (ctxGrowth) {
        new Chart(ctxGrowth, {
            type: 'line',
            data: {
                labels: analytics.value.growth.map(g => g.month),
                datasets: [{
                    label: 'Pelanggan Baru',
                    data: analytics.value.growth.map(g => g.count),
                    borderColor: '#d4af37',
                    backgroundColor: 'rgba(212, 175, 55, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: { responsive: true, plugins: { legend: { display: false } } }
        });
    }

    // Demographics Chart
    const ctxDemo = document.getElementById('demoChart');
    if (ctxDemo) {
        new Chart(ctxDemo, {
            type: 'doughnut',
            data: {
                labels: analytics.value.demographics.map(d => d.passengerType || 'Umum'),
                datasets: [{
                    data: analytics.value.demographics.map(d => d.count),
                    backgroundColor: ['#1e293b', '#d4af37', '#cbd5e1', '#b45309']
                }]
            },
            options: { responsive: true }
        });
    }
};

onMounted(() => {
    fetchStats();
    fetchAnalytics();
});
</script>
