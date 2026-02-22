eqt<template>
  <div class="h-full flex flex-col custom-scrollbar overflow-hidden">
    
    <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-800 dark:text-white">Riwayat Edit</h2>
            <p class="text-sm text-slate-500">Log aktivitas perubahan data booking secara global.</p>
        </div>
        
        <div class="flex items-center gap-3">
             <button @click="fetchLogs" class="flex items-center gap-2 px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-600 dark:text-slate-300 font-bold text-sm hover:text-blue-600 transition-colors">
                 <i class="bi bi-arrow-clockwise" :class="{ 'animate-spin': isLoading }"></i> Refresh
             </button>
        </div>
    </div>

    <div v-if="isLoading && logs.length === 0" class="flex-1 flex justify-center items-center">
         <div class="flex flex-col items-center gap-2">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            <span class="text-xs text-slate-400">Memuat Riwayat...</span>
         </div>
    </div>

    <div v-else class="flex-1 overflow-y-auto pb-20 custom-scrollbar">
         <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
             <!-- Desktop Table -->
             <table class="w-full text-sm text-left hidden md:table">
                <thead class="text-xs text-slate-500 uppercase bg-slate-50 dark:bg-slate-700/50">
                    <tr>
                        <th class="px-6 py-4">Waktu</th>
                        <th class="px-4 py-4">Admin</th>
                        <th class="px-4 py-4">Aksi</th>
                        <th class="px-4 py-4">Booking ID</th>
                        <th class="px-6 py-4">Detail Perubahan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                     <tr v-for="log in logs" :key="log.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors align-top">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-mono text-slate-600 dark:text-slate-400 font-bold">{{ formatDate(log.timestamp) }}</div>
                            <div class="text-[10px] text-slate-400">{{ formatTime(log.timestamp) }}</div>
                        </td>
                        <td class="px-4 py-4">
                            <div class="flex items-center gap-1">
                                <i class="bi bi-person-circle text-slate-400"></i>
                                <span class="font-bold text-slate-700 dark:text-slate-200 text-xs">{{ log.admin_name || 'System' }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-4 font-bold text-slate-700 dark:text-slate-300">
                             {{ log.action }}
                        </td>
                        <td class="px-4 py-4 font-mono text-xs text-slate-500">
                             {{ log.booking_id }}
                        </td>
                        <td class="px-6 py-4">
                             <div  class="grid grid-cols-1 gap-2">
                                 <!-- Diff View -->
                                 <div class="text-[10px] font-mono bg-slate-50 dark:bg-slate-900/50 p-2 rounded border border-slate-100 dark:border-slate-700 relative group">
                                      <div class="grid grid-cols-2 gap-4">
                                          <div>
                                              <span class="text-[9px] font-black text-red-400 uppercase tracking-widest block mb-1">Sebelumnya</span>
                                              <div class="text-slate-500 dark:text-slate-400 break-words" v-html="formatDiff(log.prev_value, 'prev')"></div>
                                          </div>
                                          <div class="border-l border-slate-200 dark:border-slate-700 pl-4">
                                              <span class="text-[9px] font-black text-green-500 uppercase tracking-widest block mb-1">Sesudah</span>
                                              <div class="text-slate-800 dark:text-slate-200 break-words font-medium" v-html="formatDiff(log.new_value, 'new')"></div>
                                          </div>
                                      </div>
                                 </div>
                             </div>
                        </td>
                     </tr>
                     <tr v-if="logs.length === 0">
                        <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                            Belum ada riwayat aktivitas.
                        </td>
                     </tr>
                </tbody>
             </table>

             <!-- Mobile View -->
             <div class="md:hidden divide-y divide-slate-100 dark:divide-slate-700">
                 <div v-for="log in logs" :key="log.id" class="p-4 bg-white dark:bg-slate-800">
                     <div class="flex justify-between items-start mb-2">
                         <div>
                             <div class="font-bold text-slate-800 dark:text-white text-sm">{{ log.action }}</div>
                             <div class="text-[10px] text-slate-400 font-mono">{{ formatDate(log.timestamp) }} • {{ formatTime(log.timestamp) }}</div>
                         </div>
                         <div class="text-right">
                             <div class="text-[9px] text-slate-400 uppercase font-bold">Oleh</div>
                             <div class="font-bold text-blue-600 dark:text-blue-400 text-xs">{{ log.admin_name || 'System' }}</div>
                         </div>
                     </div>
                     <div class="text-[10px] text-slate-500 mb-2 font-mono">ID: {{ log.booking_id }}</div>
                     
                     <div class="text-[10px] font-mono bg-slate-50 dark:bg-slate-900/50 p-3 rounded border border-slate-100 dark:border-slate-700">
                          <div class="mb-2 border-b border-slate-100 dark:border-slate-700 pb-2">
                               <span class="text-[9px] font-black text-red-400 uppercase tracking-widest block mb-1">Before</span>
                               <div class="text-slate-500 dark:text-slate-400 break-words" v-html="formatDiff(log.prev_value, 'prev')"></div>
                          </div>
                          <div>
                               <span class="text-[9px] font-black text-green-500 uppercase tracking-widest block mb-1">After</span>
                               <div class="text-slate-800 dark:text-slate-200 break-words font-medium" v-html="formatDiff(log.new_value, 'new')"></div>
                          </div>
                     </div>
                 </div>
             </div>
         </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const logs = ref([]);
const isLoading = ref(false);

const fetchLogs = async () => {
    isLoading.value = true;
    try {
        const res = await axios.get('api.php?action=get_all_booking_logs&limit=100');
        if (res.data.status === 'success') {
            logs.value = res.data.logs;
        }
    } catch (e) {
        console.error("Failed to fetch logs", e);
    } finally {
        isLoading.value = false;
    }
};

const formatDate = (ts) => {
    if (!ts) return '-';
    return new Date(ts).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
};

const formatTime = (ts) => {
    if (!ts) return '-';
    return new Date(ts).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
};

const formatDiff = (jsonStr, type) => {
    if (!jsonStr) return '-';
    try {
        const data = JSON.parse(jsonStr);
        // Important fields only
        const keys = ['passengerName', 'seatNumbers', 'totalPrice', 'date', 'time', 'routeId', 'paymentStatus'];
        
        let html = '';
        keys.forEach(k => {
             if (data[k] !== undefined && data[k] !== null) {
                 let label = k.replace(/([A-Z])/g, ' $1').trim(); // Camel to Title
                 let val = data[k];
                 
                 // Value Formatting
                 if (k === 'totalPrice') val = new Intl.NumberFormat('id-ID').format(val);
                 if (Array.isArray(val)) val = val.join(', ');
                 
                html += `<div><span class="text-slate-500 dark:text-slate-400 select-none mr-1 font-medium text-[10px] uppercase tracking-wide">${label}:</span> <span class="font-bold text-slate-700 dark:text-slate-200">${val}</span></div>`;
             }
        });
        
        return html || '<span class="italic text-slate-400">Data Raw</span>';
    } catch (e) {
        return jsonStr;
    }
};

onMounted(() => {
    fetchLogs();
});
</script>
