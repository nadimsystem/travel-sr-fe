<template>
  <div class="animate-fade-in h-full flex flex-col">
      <div class="bg-white p-2 rounded-[1.5rem] shadow-sm border border-slate-100 mb-6 flex items-center gap-4">
          <div class="flex-1 flex items-center">
              <div class="w-12 h-12 flex items-center justify-center text-slate-400"><i class="bi bi-search text-xl"></i></div>
              <input type="text" v-model="contactSearch" placeholder="Cari nama pelanggan atau nomor handphone..." class="w-full h-12 bg-transparent border-none text-slate-700 font-bold placeholder:font-medium placeholder:text-slate-300 focus:ring-0 text-sm">
          </div>
          <div class="pr-2">
              <select v-model="filterMode" class="bg-slate-50 border border-slate-100 text-slate-600 text-xs font-bold rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-gold/50 cursor-pointer hover:bg-slate-100 transition-colors">
                  <option value="default">Urutkan: Terakhir Aktif</option>
                  <option value="seats">Pemesan Bangku Terbanyak</option>
                  <option value="repeat">Repeat Order Terbanyak</option>
                  <option value="revenue">Total Pembelian Terbanyak</option>
              </select>
          </div>
      </div>

      <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 flex-1 flex flex-col overflow-hidden relative">
           <div class="absolute top-0 left-0 w-full h-10 bg-gradient-to-b from-white to-transparent pointer-events-none z-10"></div>
           
          <div class="overflow-x-auto flex-1 custom-scrollbar">
              <table class="w-full text-left border-collapse min-w-[800px]">
                  <thead class="bg-white text-slate-400 font-bold text-[11px] uppercase tracking-wider sticky top-0 z-20">
                      <tr>
                          <th class="p-6">Profil</th>
                          <th class="p-6">Kontak</th>
                          <th class="p-6 text-center">Statistik</th>
                          <th class="p-6 text-right">Last Seen</th>
                          <th class="p-6 text-center">Action</th>
                      </tr>
                  </thead>
                  <tbody class="divide-y divide-slate-50">
                      <tr v-for="c in filteredContacts" :key="c.phone" class="hover:bg-[#fefce8]/50 transition-colors group">
                          <td class="p-6" @click="openContact(c)">
                              <div class="flex items-center gap-4 cursor-pointer">
                                  <div class="w-12 h-12 rounded-2xl bg-slate-50 text-slate-600 flex items-center justify-center font-black text-sm group-hover:bg-gold group-hover:text-white transition-all shadow-sm">{{ getInitials(c.name) }}</div>
                                  <div class="font-bold text-slate-700 text-sm group-hover:text-slate-900">{{ c.name || 'Tanpa Nama' }}</div>
                              </div>
                          </td>
                          <td class="p-6">
                              <div class="flex items-center gap-3">
                                  <div class="bg-slate-50 px-3 py-1.5 rounded-xl border border-slate-100 text-slate-500 font-mono text-xs font-bold group-hover:border-gold/20 group-hover:bg-gold/5 transition-colors">{{ normalizePhone(c.phone) }}</div>
                                  <a :href="getWaLink(c.phone)" target="_blank" class="w-8 h-8 rounded-full bg-emerald-50 text-emerald-500 hover:bg-emerald-500 hover:text-white flex items-center justify-center transition-all">
                                      <i class="bi bi-whatsapp"></i>
                                  </a>
                              </div>
                          </td>
                          <td class="p-6 text-center">
                              <div class="inline-flex flex-col items-center">
                                  <span v-if="filterMode === 'seats'" class="text-sm font-black text-slate-800">{{ c.totalSeats || 0 }}</span>
                                  <span v-else-if="filterMode === 'revenue'" class="text-sm font-black text-slate-800">{{ formatRupiahSimple(c.totalRevenue) }}</span>
                                  <span v-else class="text-sm font-black text-slate-800">{{ c.totalTrips }}</span>
                                  
                                  <span v-if="filterMode === 'seats'" class="text-[9px] text-slate-400 font-bold uppercase mt-0.5">Kursi</span>
                                  <span v-else-if="filterMode === 'revenue'" class="text-[9px] text-slate-400 font-bold uppercase mt-0.5">IDR</span>
                                  <span v-else class="text-[9px] text-slate-400 font-bold uppercase mt-0.5">Trips</span>
                              </div>
                          </td>
                          <td class="p-6 text-right">
                              <span class="text-xs font-bold text-slate-400">{{ formatDate(c.lastTrip) }}</span>
                          </td>
                          <td class="p-6 text-center">
                              <button @click="openContact(c)" class="w-10 h-10 rounded-xl bg-slate-50 text-slate-400 hover:bg-slate-900 hover:text-white flex items-center justify-center transition-all">
                                  <i class="bi bi-arrow-right-short text-xl"></i>
                              </button>
                          </td>
                      </tr>
                      <tr v-if="filteredContacts.length === 0">
                          <td colspan="5" class="p-16 text-center">
                              <div class="w-20 h-20 bg-slate-50 rounded-full mx-auto flex items-center justify-center text-slate-300 text-3xl mb-4"><i class="bi bi-folder-x"></i></div>
                              <p class="text-slate-400 font-medium">Tidak ada data ditemukan</p>
                          </td>
                      </tr>
                  </tbody>
              </table>
          </div>
      </div>

       <!-- Soft Detail Drawer -->
      <div v-if="selectedContact" class="fixed inset-0 z-[60] flex justify-end">
          <div class="absolute inset-0 bg-slate-900/10 backdrop-blur-sm transition-opacity" @click="closeContact"></div>
          
          <div class="relative w-full max-w-md bg-white h-full shadow-2xl flex flex-col animate-slide-in-right">
              <div class="absolute top-0 left-0 w-full h-40 bg-[#f8fafc] z-0"></div>
              <button @click="closeContact" class="absolute top-6 left-6 z-10 w-10 h-10 bg-white rounded-full shadow-lg text-slate-400 hover:text-slate-800 flex items-center justify-center transition-all"><i class="bi bi-arrow-left"></i></button>

              <div class="relative z-10 px-8 pt-20 pb-8 text-center">
                  <div class="w-28 h-28 mx-auto rounded-[2rem] bg-gradient-to-br from-[#fefce8] to-[#fde047] shadow-xl border-4 border-white flex items-center justify-center text-4xl font-black text-[#854d0e] mb-4">{{ getInitials(selectedContact.name) }}</div>
                  <h2 class="text-2xl font-black text-slate-800 tracking-tight">{{ selectedContact.name }}</h2>
                  <div class="inline-flex items-center gap-2 mt-2 px-4 py-1.5 rounded-full bg-slate-50 border border-slate-100 text-slate-500 font-mono text-xs font-bold">
                      <i class="bi bi-whatsapp text-emerald-500"></i> {{ normalizePhone(selectedContact.phone) }}
                  </div>
                  
                  <div class="flex gap-3 mt-8">
                      <a :href="getWaLink(selectedContact.phone)" target="_blank" class="flex-1 bg-white border border-slate-200 text-slate-600 py-4 rounded-2xl font-bold text-sm hover:bg-slate-50 transition-all flex items-center justify-center gap-2">
                          <i class="bi bi-chat-text-fill"></i> Chat
                      </a>
                  </div>
              </div>

              <div class="flex-1 overflow-y-auto bg-white px-8 pb-8 custom-scrollbar relative">
                   <!-- Stats Grid -->
                  <div class="grid grid-cols-2 gap-4 mb-8">
                      <div class="bg-[#f8fafc] p-5 rounded-3xl border border-slate-50">
                          <div class="text-[10px] text-slate-400 uppercase font-black tracking-widest mb-2">Total Belanja</div>
                          <div class="text-lg font-black text-slate-800">{{ formatRupiahSimple(selectedContact.totalRevenue) }}</div>
                      </div>
                       <div class="bg-[#f8fafc] p-5 rounded-3xl border border-slate-50">
                          <div class="text-[10px] text-slate-400 uppercase font-black tracking-widest mb-2">Perjalanan</div>
                          <div class="text-lg font-black text-gold">{{ selectedContact.totalTrips }}x</div>
                      </div>
                  </div>

                  <h3 class="font-bold text-slate-800 mb-6 flex items-center gap-2 text-sm">
                      <span class="w-1.5 h-6 bg-slate-200 rounded-full"></span> Riwayat Pesanan
                  </h3>

                  <div class="space-y-6 pl-4 border-l-2 border-slate-50 ml-2">
                       <div v-if="isLoadingHistory" class="text-center py-4 text-slate-300 text-sm">Loading history...</div>
                       <div v-for="h in historyData" :key="h.id" class="relative pl-6 pb-2">
                          <div class="absolute -left-[21px] top-1 w-3 h-3 rounded-full border-2 border-white ring-1 ring-slate-100" :class="h.status==='Completed'||h.status==='Tiba'?'bg-emerald-400':'bg-slate-300'"></div>
                          
                          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 mb-1">
                              <div class="font-bold text-slate-700 text-sm">{{ h.routeName }}</div>
                              <span class="w-fit text-[9px] px-2 py-0.5 rounded-md font-bold uppercase tracking-wide" :class="h.status==='Completed'||h.status==='Tiba'?'bg-emerald-50 text-emerald-600':'bg-amber-50 text-amber-600'">{{ h.status }}</span>
                          </div>
                          
                          <div class="flex items-center text-xs text-slate-400 font-medium gap-3 mb-2 flex-wrap">
                              <span><i class="bi bi-calendar4 mr-1"></i> {{ formatDate(h.date) }}</span>
                              <span><i class="bi bi-clock mr-1"></i> {{ h.time }}</span>
                              <span v-if="h.seatNumbers" class="text-slate-500 font-bold bg-slate-50 px-1.5 rounded"><i class="bi bi-grid-3x3-gap-fill mr-1 text-gold"></i> Kursi: {{ h.seatNumbers }}</span>
                          </div>
                          <div class="font-mono font-bold text-slate-600 text-xs bg-slate-50 inline-block px-2 py-1 rounded-lg border border-slate-100">{{ formatRupiah(h.totalPrice) }}</div>
                      </div>
                  </div>
              </div>
          </div>
      </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import api from '../api/axios';

const contacts = ref([]);
const contactSearch = ref('');
const filterMode = ref('default');
const selectedContact = ref(null);
const historyData = ref([]);
const isLoadingHistory = ref(false);

const fetchData = async () => {
    try {
        const res = await api.get('/contacts');
        if (res.data && res.data.contacts) {
            contacts.value = res.data.contacts;
        }
    } catch (e) {
        console.error("Failed to load contacts", e);
    }
}

const filteredContacts = computed(() => {
    let result = contacts.value;

    // 1. Text Search (Name or Phone)
    if (contactSearch.value) {
        const term = contactSearch.value.toLowerCase();
        result = result.filter(c => {
            return (c.name && c.name.toLowerCase().includes(term)) || 
                   (c.phone && c.phone.includes(term));
        });
    }

    // 2. Facet Filters (Future Implementation based on screenshot: Route, Type, Loyalty)
    // For now we just implement Sort as per current UI, but could add Facets here if `facets` state existed.

    // 3. Sorting
    if (filterMode.value === 'seats') {
        result.sort((a, b) => b.totalSeats - a.totalSeats);
    } else if (filterMode.value === 'repeat') {
        result.sort((a, b) => b.totalTrips - a.totalTrips);
    } else if (filterMode.value === 'revenue') {
        result.sort((a, b) => b.totalRevenue - a.totalRevenue);
    } else {
        // default: last seen
        result.sort((a, b) => new Date(b.lastTrip) - new Date(a.lastTrip));
    }
    return result;
});

const getInitials = (name) => {
    if (!name) return '?';
    return name.substring(0, 2).toUpperCase();
};

const normalizePhone = (phone) => {
    if(!phone) return '-';
    return phone.replace(/(\d{4})(\d{4})(\d+)/, '$1-$2-$3');
};

const getWaLink = (phone) => {
    if (!phone) return '#';
    let p = phone.replace(/\D/g, '');
    if (p.startsWith('0')) p = '62' + p.substring(1);
    return `https://wa.me/${p}`;
};

const formatRupiahSimple = (val) => {
    if (!val) return 'Rp 0';
    if (val >= 1000000000) return 'Rp ' + (val / 1000000000).toFixed(1) + ' M';
    if (val >= 1000000) return 'Rp ' + (val / 1000000).toFixed(1) + ' jt';
    return 'Rp ' + (val / 1000).toFixed(0) + ' rb';
};

const formatRupiah = (val) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);
};

const formatDate = (str) => {
    if(!str) return '-';
    return new Date(str).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
};

const openContact = async (c) => {
    selectedContact.value = c;
    isLoadingHistory.value = true;
    historyData.value = [];
    try {
        const res = await api.get('/customer/detail?phone=' + c.phone);
        if(res.data && res.data.history) {
            historyData.value = res.data.history;
        }
    } catch (e) {
        console.error(e);
    } finally {
        isLoadingHistory.value = false;
    }
};

const closeContact = () => {
    selectedContact.value = null;
};

onMounted(() => {
    fetchData();
});
</script>
