<template>
  <div class="flex flex-col lg:flex-row gap-4 lg:gap-6 h-[calc(100vh-8rem)]">
    <!-- Left Sidebar: Date List -->
    <div class="w-full lg:w-64 bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden flex flex-col flex-shrink-0 h-auto max-h-48 lg:max-h-full lg:h-auto transition-all">
        <div class="p-3 lg:p-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex flex-col gap-3">
             <div class="flex justify-between items-center cursor-pointer lg:cursor-default" @click="toggleMobileDates">
                <h3 class="font-bold text-slate-700 dark:text-slate-200">Pilih Tanggal</h3>
                <i class="bi bi-chevron-down lg:hidden text-slate-400 transition-transform" :class="{'rotate-180': showMobileDates}"></i>
            </div>
            
            <!-- Month Selector -->
            <div v-if="months.length > 0" class="relative">
                <select v-model="selectedMonth" class="w-full bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg pl-3 pr-8 py-2 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-all appearance-none cursor-pointer">
                    <option v-for="m in months" :key="m.month_value" :value="m.month_value">
                        {{ m.month_label }}
                    </option>
                </select>
                <div class="absolute inset-y-0 right-0 pr-2.5 flex items-center pointer-events-none">
                    <i class="bi bi-chevron-down text-slate-400 text-[10px]"></i>
                </div>
            </div>
        </div>
        <div class="flex-1 overflow-y-auto p-2 space-y-1 custom-scrollbar" :class="{'hidden lg:block': !showMobileDates}">
            <template v-if="loadingDates">
                <div class="p-4 text-center text-slate-400 text-sm">Memuat tanggal...</div>
            </template>
            <template v-else>
                <div v-if="dates.length === 0" class="p-4 text-center text-slate-400 text-xs">
                    Belum ada data bukti
                </div>
                <button 
                    v-for="d in dates" 
                    :key="d.date"
                    @click="selectDate(d.date)"
                    class="w-full text-left px-3 py-2.5 rounded-lg text-sm transition-all flex items-center justify-between group"
                    :class="selectedDate === d.date ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-300 font-bold shadow-sm ring-1 ring-blue-100 dark:ring-blue-800' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700'"
                >
                    <span>{{ formatDate(d.date) }}</span>
                    <span class="text-xs bg-slate-100 dark:bg-slate-700 px-2 py-0.5 rounded-full text-slate-500 group-hover:bg-white dark:group-hover:bg-slate-600 transition-colors">{{ d.count }}</span>
                </button>
            </template>
        </div>
    </div>

    <!-- Main Content: Proofs Grid -->
    <div class="flex-1 bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 flex flex-col overflow-hidden">
        <div class="p-4 border-b border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 sticky top-0 z-10">
            <div class="flex flex-col xl:flex-row justify-between xl:items-center gap-4">
                 <div>
                    <div class="flex items-center gap-3">
                        <div class="bg-blue-50 dark:bg-blue-900/30 p-2 rounded-lg text-blue-600 dark:text-blue-400">
                            <i class="bi bi-images text-xl"></i>
                        </div>
                        <div>
                            <h2 class="font-bold text-lg text-slate-800 dark:text-white leading-tight">Bukti Pembayaran</h2>
                            <p class="text-xs text-slate-500 font-medium mt-0.5" v-if="selectedDate">
                                {{ formatDate(selectedDate) }} &bull; <span class="text-blue-600 dark:text-blue-400">{{ filteredProofs.length }} Item</span>
                            </p>
                        </div>
                    </div>
                 </div>
                 
                 <div class="flex flex-col sm:flex-row gap-3 w-full xl:w-auto">
                    <!-- Route Filter -->
                    <div class="relative min-w-[180px] w-full sm:w-auto">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="bi bi-signpost-split text-slate-400 text-sm"></i>
                        </div>
                        <select v-model="selectedRoute" class="w-full bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg pl-9 pr-8 py-2.5 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-all appearance-none cursor-pointer hover:bg-slate-100 dark:hover:bg-slate-700">
                            <option value="">Semua Rute</option>
                            <option v-for="route in uniqueRoutes" :key="route" :value="route">{{ route }}</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <i class="bi bi-chevron-down text-slate-400 text-xs"></i>
                        </div>
                    </div>

                    <!-- Search Box -->
                    <div class="relative flex-1 sm:w-64 w-full">
                         <input 
                            v-model="searchQuery" 
                            type="text" 
                            placeholder="Cari nama / no. hp..." 
                            class="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-all"
                        >
                        <i class="bi bi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                         <button 
                            v-if="searchQuery"
                            @click="searchQuery = ''"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300"
                        >
                            <i class="bi bi-x-circle-fill text-xs"></i>
                        </button>
                    </div>

                    <button 
                        @click="fetchProofs(selectedDate)" 
                        class="p-2.5 rounded-lg border border-slate-200 dark:border-slate-600 text-slate-500 hover:text-blue-600 hover:bg-blue-50 hover:border-blue-200 dark:hover:bg-slate-700 dark:hover:text-blue-400 transition-all flex-shrink-0 bg-white dark:bg-slate-800" 
                        title="Muat Ulang"
                    >
                        <i class="bi bi-arrow-clockwise text-lg" :class="{'animate-spin': loadingProofs}"></i>
                    </button>
                 </div>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto p-4 lg:p-6 bg-slate-50/50 dark:bg-slate-900/50 relative">
             <div v-if="loadingProofs" class="absolute inset-0 flex items-center justify-center bg-white/50 dark:bg-slate-900/50 z-10 backdrop-blur-sm transition-opacity">
                 <div class="flex flex-col items-center bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-xl">
                    <div class="animate-spin text-blue-500 text-3xl mb-3"><i class="bi bi-arrow-repeat"></i></div>
                    <span class="text-sm font-semibold text-slate-600 dark:text-slate-300">Sedang memuat data...</span>
                 </div>
             </div>

             <div v-if="filteredProofs.length === 0 && !loadingProofs" class="h-full flex flex-col items-center justify-center text-slate-400">
                 <div class="bg-slate-100 dark:bg-slate-800 p-6 rounded-full mb-4">
                    <i class="bi bi-search text-4xl opacity-50"></i>
                 </div>
                 <p class="font-medium">Tidak ada data yang cocok dengan pencarian</p>
             </div>

             <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 lg:gap-6">
                 <div v-for="item in filteredProofs" :key="item.id" class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden hover:shadow-lg hover:-translate-y-1 transition-all duration-300 group">
                      <!-- Image Header -->
                      <div class="h-56 bg-slate-200 dark:bg-slate-700 relative overflow-hidden cursor-pointer" @click="viewImage(getImageUrl(item.paymentProof))">
                          <img :src="getImageUrl(item.paymentProof)" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" loading="lazy">
                          <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-colors duration-300 flex items-center justify-center opacity-0 group-hover:opacity-100">
                              <span class="bg-white/90 text-slate-900 px-4 py-2 rounded-full text-xs font-bold shadow-lg transform translate-y-2 group-hover:translate-y-0 transition-transform duration-300 backdrop-blur-sm">
                                  <i class="bi bi-zoom-in mr-1"></i> Perbesar
                              </span>
                          </div>
                          
                          <!-- Status Badge -->
                          <div class="absolute top-3 right-3 px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider shadow-sm backdrop-blur-md bg-white/90"
                            :class="{
                                'text-green-700 border border-green-200': item.validationStatus === 'Valid',
                                'text-yellow-700 border border-yellow-200': item.validationStatus !== 'Valid'
                            }">
                              <i class="bi" :class="item.validationStatus === 'Valid' ? 'bi-check-circle-fill' : 'bi-clock-fill'"></i>
                              {{ item.validationStatus || 'Pending' }}
                          </div>
                      </div>

                      <!-- Content -->
                      <div class="p-5">
                          <div class="flex justify-between items-start mb-3">
                              <div>
                                  <h4 class="font-bold text-slate-800 dark:text-white line-clamp-1 text-base">{{ item.passengerName }}</h4>
                                  <div class="text-xs text-slate-500 mt-0.5 flex items-center gap-1">
                                      <i class="bi bi-telephone"></i> {{ item.passengerPhone }}
                                  </div>
                              </div>
                              <div class="text-right">
                                  <span class="text-[10px] font-mono text-slate-400 bg-slate-100 dark:bg-slate-700 px-2 py-1 rounded border border-slate-200 dark:border-slate-600">#{{ item.id }}</span>
                              </div>
                          </div>
                          
                          <div class="bg-slate-50 dark:bg-slate-700/30 rounded-lg p-3 border border-slate-100 dark:border-slate-700 mb-4">
                              <div class="flex justify-between items-center mb-2">
                                  <span class="text-xs text-slate-500 uppercase font-bold tracking-wider">Total Booking</span>
                                  <span 
                                    class="text-[10px] px-2 py-0.5 rounded font-bold uppercase tracking-wide border"
                                    :class="{
                                        'bg-blue-100 text-blue-700 border-blue-200': item.serviceType === 'Travel',
                                        'bg-purple-100 text-purple-700 border-purple-200': item.serviceType === 'Carter',
                                        'bg-orange-100 text-orange-700 border-orange-200': item.serviceType === 'Dropping',
                                        'bg-slate-100 text-slate-600': !['Travel', 'Carter', 'Dropping'].includes(item.serviceType)
                                    }"
                                  >
                                    {{ item.serviceType || 'Travel' }}
                                  </span>
                              </div>
                              <div class="flex flex-col">
                                  <div class="font-bold text-xl text-slate-800 dark:text-sr-gold">{{ formatCurrency(item.totalPrice) }}</div>
                                  <div class="text-xs text-slate-500 mt-1 flex items-center gap-1" v-if="item.seatCount > 0 && !['Carter', 'Dropping'].includes(item.serviceType)">
                                      <span class="bg-slate-200 dark:bg-slate-600 px-1.5 py-0.5 rounded text-[10px]">{{ formatCurrency(Math.floor(item.totalPrice / item.seatCount)) }}</span>
                                      <span>x</span>
                                      <span class="font-bold text-slate-700 dark:text-slate-300">{{ item.seatCount }} Seat</span>
                                  </div>
                              </div>

                              <!-- Transfer Details -->
                              <div v-if="item.destinationAccount || item.transferSentDate" class="mt-3 pt-3 border-t border-slate-200 dark:border-slate-600">
                                   <div class="grid grid-cols-2 gap-2">
                                       <div v-if="item.destinationAccount">
                                           <div class="text-[10px] text-slate-400 font-bold uppercase">Ke Rekening</div>
                                           <div class="text-xs font-semibold text-slate-700 dark:text-slate-300">{{ item.destinationAccount }}</div>
                                       </div>
                                       <div v-if="item.transferSentDate">
                                           <div class="text-[10px] text-slate-400 font-bold uppercase">Tanggal TF</div>
                                           <div class="text-xs font-semibold text-slate-700 dark:text-slate-300">{{ formatDateShort(item.transferSentDate) }}</div>
                                       </div>
                                   </div>
                              </div>
                          </div>

                          <div class="flex gap-2">
                              <button 
                                v-if="item.validationStatus !== 'Valid'" 
                                @click="validatePayment(item)" 
                                class="flex-1 bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white text-xs font-bold py-2.5 rounded-lg transition-all shadow-md shadow-blue-500/20 active:scale-95 flex items-center justify-center gap-2">
                                  <i class="bi bi-check-lg"></i> Validasi
                              </button>
                               <button 
                                v-else 
                                disabled 
                                class="flex-1 bg-slate-100 text-slate-400 text-xs font-bold py-2.5 rounded-lg cursor-not-allowed flex items-center justify-center gap-2">
                                  <i class="bi bi-check-all"></i> Terverifikasi
                              </button>
                              
                              <a :href="'https://wa.me/' + formatPhone(item.passengerPhone)" target="_blank" class="w-10 bg-green-50 text-green-600 hover:bg-green-100 border border-green-200 rounded-lg flex items-center justify-center transition-colors">
                                  <i class="bi bi-whatsapp text-lg"></i>
                              </a>
                          </div>
                      </div>
                 </div>
             </div>
        </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { useRoute } from 'vue-router'; // Add this
import axios from 'axios';
import Swal from 'sweetalert2';

const route = useRoute(); // Add this
const dates = ref([]);
const proofs = ref([]);
const selectedDate = ref(null);
const loadingDates = ref(false);
const loadingProofs = ref(false);
const showMobileDates = ref(false);

const months = ref([]);
const selectedMonth = ref('');

// Filter States
const searchQuery = ref('');
const selectedRoute = ref('');

const toggleMobileDates = () => {
    showMobileDates.value = !showMobileDates.value;
};

// Computed Property for Filtering
const filteredProofs = computed(() => {
    return proofs.value.filter(item => {
        // Query Match
        const query = searchQuery.value.toLowerCase();
        const matchesQuery = 
            (item.passengerName && item.passengerName.toLowerCase().includes(query)) ||
            (item.passengerPhone && item.passengerPhone.toLowerCase().includes(query));

        // Route Match
        const matchesRoute = selectedRoute.value === '' || item.routeName === selectedRoute.value;

        return matchesQuery && matchesRoute;
    });
});

// Extract Unique Routes
const uniqueRoutes = computed(() => {
    const routes = proofs.value.map(p => p.routeName).filter(r => r);
    return [...new Set(routes)];
});

const formatCurrency = (val) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);
};

const formatDate = (dateStr) => {
    if (!dateStr) return '';
    const date = new Date(dateStr);
    return date.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
};

const formatDateShort = (dateStr) => {
    if (!dateStr) return '-';
    const date = new Date(dateStr);
    return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
};

const formatPhone = (phone) => {
    if (!phone) return '';
    let p = phone.replace(/\D/g, '');
    if (p.startsWith('0')) p = '62' + p.substring(1);
    return p;
};

const getImageUrl = (path) => {
    if (!path) return '/placeholder-image.jpg'; // Add a placeholder
    if (path.startsWith('data:image')) return path;
    if (path.startsWith('http')) return path;

    // Determine hostname
    const hostname = window.location.hostname;
    const isLocalhost = hostname === 'localhost' || hostname === '127.0.0.1';

    // If we are developing locally (Vite usually runs on port 5173, but let's be safe)
    // We can't use relative '../display-v12' because Vite serves from root.
    // We should point to the Apache server directly.
    
    // Clean path (remove leading slash if present)
    const cleanPath = path.startsWith('/') ? path.substring(1) : path;

    if (isLocalhost) {
        // Assume default XAMPP/Apache structure where 'display-v12' is a sibling of 'ops'
        // And Apache runs on standard port (usually 80, so we don't specify port 5173)
        // Adjust this base URL if your local setup is different
        return `http://${hostname}/travel-sr-fe/display-v12/${cleanPath}`;
    } else {
        // Online / Production
        // If online, use display-v11 as requested
        return `../display-v11/${cleanPath}`;
    }
};

const fetchMonths = async () => {
    try {
        const res = await axios.get('api.php?action=get_proof_months');
        if (res.data.status === 'success') {
            months.value = res.data.months;
            
            let targetMonth = null;
            if (route.query.date) {
                const queryMonth = route.query.date.substring(0, 7);
                if (months.value.some(m => m.month_value === queryMonth)) {
                    targetMonth = queryMonth;
                }
            }
            
            if (!targetMonth && months.value.length > 0) {
                targetMonth = months.value[0].month_value;
            }
            
            if (targetMonth) {
                selectedMonth.value = targetMonth;
            }
        }
    } catch (e) {
        console.error(e);
    }
};

const fetchDates = async () => {
    loadingDates.value = true;
    try {
        let url = 'api.php?action=get_payment_proofs';
        if (selectedMonth.value) {
            url += `&month=${selectedMonth.value}`;
        }
        
        const res = await axios.get(url);
        if (res.data.status === 'success') {
            dates.value = res.data.dates;
            
            // Check query params
            const queryDate = route.query.date;
            const querySearch = route.query.search;

            if (querySearch) {
                searchQuery.value = querySearch;
            }

            if (queryDate && dates.value.find(d => d.date === queryDate)) {
                 selectDate(queryDate);
            } else if (dates.value.length > 0) {
                // Only select first date if we just changed month or initial load
               // selectDate(dates.value[0].date); // Optional: Auto-select first date
            }
        }
    } catch (e) {
        console.error(e);
        Swal.fire('Error', 'Gagal memuat daftar tanggal', 'error');
    } finally {
        loadingDates.value = false;
    }
};

watch(selectedMonth, () => {
    fetchDates();
    selectedDate.value = null; // Reset selected date when month changes
    proofs.value = [];
});

const selectDate = (date) => {
    selectedDate.value = date;
    fetchProofs(date);
};

const fetchProofs = async (date) => {
    loadingProofs.value = true;
    proofs.value = []; 
    try {
        const res = await axios.get(`api.php?action=get_payment_proofs&date=${date}`);
        if (res.data.status === 'success') {
            proofs.value = res.data.proofs;
        }
    } catch (e) {
        console.error(e);
        Swal.fire('Error', 'Gagal memuat bukti pembayaran', 'error');
    } finally {
        loadingProofs.value = false;
    }
};

const viewImage = (src) => {
    if (!src) return;
    Swal.fire({
        imageUrl: src,
        imageAlt: 'Bukti Bayar',
        showConfirmButton: false,
        showCloseButton: true,
        width: 'auto',
        background: '#fff',
        padding: '0',
        customClass: {
            image: 'max-h-[80vh] object-contain rounded-lg',
            popup: 'rounded-xl overflow-hidden'
        }
    });
};

const validatePayment = async (item) => {
     const res = await Swal.fire({
        title: 'Validasi Pembayaran?',
        html: `Apakah Anda yakin ingin memvalidasi pembayaran dari <b>${item.passengerName}</b> senilai <b class="text-lg text-blue-600">${formatCurrency(item.totalPrice)}</b>?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#2563eb',
        cancelButtonColor: '#cbd5e1',
        confirmButtonText: 'Ya, Validasi',
        cancelButtonText: 'Batal'
    });

    if (res.isConfirmed) {
        try {
            // Using existing action logic in finance.php or similar if I moved it? 
            // finance.php handles 'validate_payment'. I kept finance.php as is.
            const resp = await axios.post('api.php?action=validate_payment', { booking_id: item.id });
            if (resp.data.status === 'success') {
                item.validationStatus = 'Valid';
                Swal.fire({
                    icon: 'success',
                    title: 'Tervalidasi',
                    text: 'Status pembayaran berhasil diperbarui',
                    timer: 1500,
                    showConfirmButton: false
                });
            } else {
                Swal.fire('Gagal', resp.data.message, 'error');
            }
        } catch (e) {
             Swal.fire('Error', 'Terjadi kesalahan saat memvalidasi', 'error');
        }
    }
};

onMounted(async () => {
    await fetchMonths();
    // fetchDates is called by watch(selectedMonth) if set, or we call it here if needed.
    // However, fetchMonths sets selectedMonth which triggers watch.
    // If no months, we should still fetch dates (default 60 limit).
    if (months.value.length === 0) {
        fetchDates();
    }
});
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
.dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #475569; }
</style>
