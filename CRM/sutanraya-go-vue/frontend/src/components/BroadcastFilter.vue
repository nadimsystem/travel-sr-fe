<template>
  <div class="h-full flex flex-col lg:flex-row gap-6 animate-fade-in">
      <!-- SIDEBAR FILTERS (Matches screenshot) -->
      <div class="w-full lg:w-64 flex-none space-y-6 overflow-y-auto custom-scrollbar pb-20">
          
          <!-- Kpi Card -->
           <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-100">
              <div class="text-3xl font-black text-slate-800 tracking-tight">{{ totalContacts }}</div>
              <div class="text-[10px] uppercase font-bold text-slate-400 tracking-widest mt-1">Total Audience</div>
          </div>

          <!-- Filter Group: Tipe Penumpang -->
          <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100">
              <h3 class="font-bold text-slate-800 mb-3 flex items-center gap-2 text-sm">
                  <span class="w-1 h-4 bg-gold rounded-full"></span> Tipe Penumpang
              </h3>
              <div class="space-y-2">
                  <label class="flex items-center gap-2 cursor-pointer group">
                      <input type="checkbox" v-model="filters.types" value="Mahasiswa / Pelajar" class="rounded border-slate-300 text-gold focus:ring-gold">
                      <span class="text-xs font-bold text-slate-500 group-hover:text-slate-700">Mahasiswa / Pelajar</span>
                  </label>
                  <label class="flex items-center gap-2 cursor-pointer group">
                      <input type="checkbox" v-model="filters.types" value="Umum" class="rounded border-slate-300 text-gold focus:ring-gold">
                      <span class="text-xs font-bold text-slate-500 group-hover:text-slate-700">Umum</span>
                  </label>
              </div>
          </div>

          <!-- Filter Group: Riwayat Rute -->
          <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100">
               <h3 class="font-bold text-slate-800 mb-3 flex items-center gap-2 text-sm">
                  <span class="w-1 h-4 bg-blue-500 rounded-full"></span> Riwayat Rute
              </h3>
              <div class="space-y-2 max-h-48 overflow-y-auto custom-scrollbar pr-2">
                  <label v-for="route in availableRoutes" :key="route" class="flex items-center gap-2 cursor-pointer group">
                      <input type="checkbox" v-model="filters.routes" :value="route" class="rounded border-slate-300 text-gold focus:ring-gold">
                      <span class="text-[11px] font-bold text-slate-500 group-hover:text-slate-700 truncate" :title="route">{{ route }}</span>
                  </label>
              </div>
          </div>

          <!-- Filter Group: Loyalitas (Mock) -->
          <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100">
               <h3 class="font-bold text-slate-800 mb-3 flex items-center gap-2 text-sm">
                  <span class="w-1 h-4 bg-emerald-500 rounded-full"></span> Loyalitas
              </h3>
               <div class="space-y-4">
                  <div>
                      <div class="flex justify-between text-[10px] font-bold text-slate-400 mb-1">
                          <span>Minimal Perjalanan</span>
                          <span>{{ filters.minTrips }}x</span>
                      </div>
                      <input type="range" v-model.number="filters.minTrips" min="0" max="20" class="w-full accent-emerald-500 h-1 bg-slate-100 rounded-lg appearance-none cursor-pointer">
                  </div>
               </div>
          </div>
      </div>

      <!-- MAIN CONTENT: CONTACT LIST -->
      <div class="flex-1 flex flex-col bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden relative">
          <!-- Soft Header -->
           <div class="p-6 border-b border-slate-50 flex items-center justify-between bg-white z-20">
               <div class="flex items-center gap-4 flex-1">
                   <div class="relative flex-1 max-w-md">
                       <i class="bi bi-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                       <input type="text" v-model="search" placeholder="Cari nama atau nomor HP..." class="w-full pl-10 pr-4 py-3 bg-slate-50 border-none rounded-xl text-sm font-bold text-slate-700 focus:ring-2 focus:ring-gold/50">
                   </div>
               </div>
               
               <!-- Broadcast Action -->
               <div v-if="selectedContacts.length > 0" class="flex items-center gap-3 animate-fade-in">
                   <span class="text-xs font-bold text-slate-400"><span class="text-gold font-black text-lg">{{ selectedContacts.length }}</span> terpilih</span>
                   <button @click="emitBroadcast" class="bg-slate-900 text-white px-5 py-2.5 rounded-xl font-bold text-xs hover:bg-slate-800 shadow-lg shadow-slate-200 transition-all">
                       <i class="bi bi-megaphone-fill mr-2"></i> Broadcast
                   </button>
               </div>
           </div>

           <!-- Table -->
           <div class="flex-1 overflow-auto custom-scrollbar relative">
              <table class="w-full text-left border-collapse min-w-[800px]">
                  <thead class="bg-slate-50/50 text-slate-400 font-bold text-[10px] uppercase tracking-wider sticky top-0 z-10 backdrop-blur-sm">
                      <tr>
                          <th class="p-4 w-10 text-center"><input type="checkbox" @change="toggleSelectAll" class="rounded border-slate-300 text-gold focus:ring-gold"></th>
                          <th class="p-4">Target</th>
                          <th class="p-4">Tags & History</th>
                          <th class="p-4 text-center">Stats</th>
                      </tr>
                  </thead>
                  <tbody class="divide-y divide-slate-50">
                      <tr v-for="c in filteredContacts" :key="c.phone" class="hover:bg-[#fefce8]/30 transition-colors group">
                           <td class="p-4 text-center">
                              <input type="checkbox" :value="c" v-model="selectedContacts" class="rounded border-slate-300 text-gold focus:ring-gold">
                          </td>
                          <td class="p-4">
                              <div class="flex items-center gap-3">
                                  <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-500 flex items-center justify-center font-black text-xs">{{ getInitials(c.name) }}</div>
                                  <div>
                                      <div class="font-bold text-slate-700 text-sm">{{ c.name }}</div>
                                      <div class="text-[10px] font-mono text-slate-400">{{ normalizePhone(c.phone) }}</div>
                                  </div>
                              </div>
                          </td>
                          <td class="p-4">
                              <div class="flex flex-wrap gap-2">
                                  <!-- Type Tag -->
                                  <span v-if="c.historyTypes && c.historyTypes.includes('Mahasiswa')" class="px-2 py-0.5 bg-amber-50 text-amber-600 rounded-md text-[9px] font-bold uppercase tracking-wide border border-amber-100">Pelajar</span>
                                  <span v-else class="px-2 py-0.5 bg-slate-50 text-slate-500 rounded-md text-[9px] font-bold uppercase tracking-wide border border-slate-100">Umum</span>
                                  
                                  <!-- Route Tags (Limit 2) -->
                                  <span v-for="r in (c.historyRoutes ? c.historyRoutes.split(', ').slice(0, 2) : [])" :key="r" class="px-2 py-0.5 bg-blue-50 text-blue-600 rounded-md text-[9px] font-bold border border-blue-100 max-w-[150px] truncate">
                                      {{ r }}
                                  </span>
                              </div>
                          </td>
                          <td class="p-4 text-center">
                               <div class="font-black text-slate-800 text-sm">{{ c.totalTrips }} <span class="text-[9px] font-bold text-slate-400 uppercase">Trips</span></div>
                          </td>
                      </tr>
                  </tbody>
              </table>

               <!-- Empty State -->
              <div v-if="filteredContacts.length === 0" class="p-20 text-center">
                  <div class="text-slate-300 text-4xl mb-3"><i class="bi bi-filter-circle"></i></div>
                  <p class="text-slate-400 font-bold text-sm">Tidak ada kontak yang sesuai filter.</p>
              </div>
           </div>
      </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import api from '../api/axios';

const props = defineProps(['contacts']); // Pass contacts from parent to avoid re-fetch if possible, or fetch here
const emit = defineEmits(['add-broadcast']);

const contacts = ref([]);
const search = ref('');
const selectedContacts = ref([]);

const filters = ref({
    types: [],
    routes: [],
    minTrips: 0
});

// Mock routes based on Screenshot/Context if backend doesn't provide list
// Ideally backend provides aggregation of available routes
const availableRoutes = ref([
    'Bukittinggi - Padang', 
    'Padang - Bukittinggi', 
    'Padang - Payakumbuh', 
    'Payakumbuh - Padang',
    'Bukittinggi Via Sitinjau - Padang',
    'Kota Bukittinggi - Pdg-Blm',
    'Kota Padang - Agam'
]);

const totalContacts = computed(() => contacts.value.length);

const filteredContacts = computed(() => {
    return contacts.value.filter(c => {
        // 1. Search
        const term = search.value.toLowerCase();
        const matchesSearch = !term || (c.name && c.name.toLowerCase().includes(term)) || (c.phone && c.phone.includes(term));
        if(!matchesSearch) return false;

        // 2. Type Filter
        // Note: c.historyTypes is "Type1, Type2" string
        if (filters.value.types.length > 0) {
            const hasType = filters.value.types.some(t => {
                if(t === 'Mahasiswa / Pelajar') return c.historyTypes && (c.historyTypes.includes('Mahasiswa') || c.historyTypes.includes('Pelajar'));
                return c.historyTypes && c.historyTypes.includes(t);
            });
            if(!hasType) return false;
        }

        // 3. Route Filter
        if (filters.value.routes.length > 0) {
            const hasRoute = filters.value.routes.some(r => c.historyRoutes && c.historyRoutes.includes(r));
            if(!hasRoute) return false;
        }

        // 4. Min Trips
        if (c.totalTrips < filters.value.minTrips) return false;

        return true;
    });
});

const getInitials = (name) => name ? name.substring(0, 2).toUpperCase() : '?';
const normalizePhone = (p) => p || '-';

const toggleSelectAll = (e) => {
    if (e.target.checked) {
        selectedContacts.value = [...filteredContacts.value];
    } else {
        selectedContacts.value = [];
    }
};

const emitBroadcast = () => {
    emit('add-broadcast', selectedContacts.value);
    selectedContacts.value = []; // clear after adding
};

const fetchData = async () => {
    try {
        const res = await api.get('/contacts');
        if (res.data && res.data.contacts) {
            contacts.value = res.data.contacts;
            // dynamically extract routes if needed?
        }
    } catch (e) {
        console.error(e);
    }
};

onMounted(() => {
    fetchData();
});
</script>
