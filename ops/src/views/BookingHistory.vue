<template>
  <div class="min-h-screen bg-slate-50 dark:bg-slate-900">
    <!-- Header Controls -->
    <div class="bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 p-4 sticky top-0 z-20">
      <div class="flex justify-between items-center mb-4">
        <div>
          <h2 class="text-lg font-bold text-slate-800 dark:text-white">Validasi & Cetak Tiket</h2>
        </div>
        <div class="flex gap-2">
          <button @click="activeTab='travel'" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-colors" :class="activeTab==='travel'?'bg-blue-900 text-white':'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300'">Travel</button>
          <button @click="activeTab='bus'" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-colors" :class="activeTab==='bus'?'bg-blue-900 text-white':'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300'">Bus</button>
        </div>
      </div>
      <div class="flex justify-between gap-4 flex-wrap">
        <div class="relative flex-1 min-w-[200px]">
          <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
          <input type="text" v-model="searchTerm" placeholder="Cari..." class="w-full pl-9 pr-3 py-1.5 text-sm border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
        </div>
        <div class="flex gap-2">
          <!-- <a href="../display-v12/edit_booking.php" class="flex items-center gap-2 px-3 py-1.5 bg-yellow-100 hover:bg-yellow-200 text-yellow-800 rounded-lg text-xs font-bold transition-colors">
            <i class="bi bi-pencil-square"></i> Edit Booking
          </a> -->
          <div v-if="activeTab==='bus'" class="flex bg-slate-100 dark:bg-slate-700 p-0.5 rounded-lg">
            <button @click="busViewMode='list'" class="px-3 py-1 rounded text-xs font-bold" :class="busViewMode==='list'?'bg-white dark:bg-slate-600 text-blue-700 dark:text-blue-300 shadow':'text-slate-500 dark:text-slate-400'">List</button>
            <button @click="busViewMode='calendar'" class="px-3 py-1 rounded text-xs font-bold" :class="busViewMode==='calendar'?'bg-white dark:bg-slate-600 text-blue-700 dark:text-blue-300 shadow':'text-slate-500 dark:text-slate-400'">Kalender</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Filter Toolbar -->
    <div class="px-4 py-2 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex flex-wrap gap-2 items-center">
      <select v-model="filterMethod" class="text-xs border border-slate-300 dark:border-slate-600 rounded px-2 py-1.5 bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-1 focus:ring-blue-500">
        <option value="All">Semua Metode</option>
        <option value="Cash">Cash</option>
        <option value="Transfer">Transfer</option>
        <option value="DP">DP</option>
        <option value="Belum Bayar">Belum Bayar</option>
      </select>
      <select v-model="filterCategory" class="text-xs border border-slate-300 dark:border-slate-600 rounded px-2 py-1.5 bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-1 focus:ring-blue-500">
        <option value="">Semua Kategori</option>
        <option value="Umum">Umum</option>
        <option value="Pelajar">Pelajar</option>
      </select>
      <select v-model="filterSort" class="text-xs border border-slate-300 dark:border-slate-600 rounded px-2 py-1.5 bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-1 focus:ring-blue-500">
        <option value="Newest">Terbaru</option>
        <option value="Oldest">Terlama</option>
      </select>
      <input type="date" v-model="filterDate" class="text-xs border border-slate-300 dark:border-slate-600 rounded px-2 py-1.5 bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-1 focus:ring-blue-500">
      <select v-model="filterRoute" class="text-xs border border-slate-300 dark:border-slate-600 rounded px-2 py-1.5 bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-1 focus:ring-blue-500 max-w-[200px]">
        <option value="All">Semua Rute</option>
        <option v-for="r in uniqueRoutes" :key="r" :value="r">{{ getRouteLabel(r) }}</option>
      </select>
      <button @click="resetFilters" class="text-xs font-bold text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 px-2 py-1.5 transition-colors" title="Reset Filter">
        <i class="bi bi-arrow-counterclockwise"></i> Reset
      </button>
    </div>

    <!-- Table View -->
    <div class="overflow-x-auto">
      <table v-if="activeTab === 'travel' || busViewMode === 'list'" class="w-full text-left text-sm">
        <thead class="bg-slate-50 dark:bg-slate-700 text-slate-500 dark:text-slate-300 uppercase text-[10px] sticky top-0 z-10 font-bold">
          <tr>
            <th class="p-4 w-10 text-center">No</th>
            <th class="p-4">Waktu</th>
            <th class="p-4">Penumpang</th>
            <th class="p-4">Kursi</th>
            <th class="p-4">Penyemputan & Tujuan</th>
            <th class="p-4">Pembayaran Detail</th>
            <th class="p-4">Total</th>
            <th class="p-4">Sisa Bayar</th>
            <th class="p-4">Status</th>
            <th class="p-4">Catatan</th>
            <th class="p-4">Dibuat</th>
            <th class="p-4 text-right">KTM</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
          <tr v-if="isLoading">
            <td colspan="12" class="p-12 text-center">
              <div class="flex flex-col items-center justify-center opacity-70">
                <i class="bi bi-arrow-repeat animate-spin text-4xl text-blue-500 mb-2"></i>
                <span class="text-sm font-bold text-slate-500 animate-pulse">Memuat data booking...</span>
              </div>
            </td>
          </tr>
          <tr v-else-if="paginatedBookings.length > 0" v-for="(b, index) in paginatedBookings" :key="b.id" @click="openDetailModal(b)" class="hover:bg-blue-50/30 dark:hover:bg-slate-700/50 transition-colors cursor-pointer group">
            <td class="p-4 text-center text-xs font-bold text-slate-400">{{ (currentPage-1)*itemsPerPage + index + 1 }}</td>
            <td class="p-4">
              <div class="font-bold text-slate-800 dark:text-white">{{ formatDate(b.date) }}</div>
              <div class="text-xs text-slate-500 dark:text-slate-400">{{ b.time || b.duration + ' Hari' }}</div>
            </td>
            <td class="p-4">
              <div class="font-bold text-slate-800 dark:text-white">{{ b.passengerName }}</div>
              <div class="text-xs text-slate-500 dark:text-slate-400">{{ b.passengerPhone }}</div>
              <span v-if="b.passengerType === 'Pelajar' || b.passengerType === 'Mahasiswa / Pelajar'" class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-yellow-100 text-yellow-700 uppercase mt-1 inline-block">Pelajar</span>
            </td>
            <td class="p-4">
              <div class="font-bold text-lg text-slate-700 dark:text-slate-300">{{ b.seatNumbers || '-' }}</div>
              <div class="text-[10px] text-slate-400 uppercase">{{ b.seatCount }} Orang</div>
            </td>
            <td class="p-4 max-w-[200px]">
              <div class="flex flex-col gap-1.5">
                <div class="flex gap-1.5 items-start">
                  <i class="bi bi-geo-alt-fill text-slate-400 text-xs mt-0.5"></i>
                  <div>
                    <div class="text-[10px] font-bold text-slate-500 uppercase">Jemput</div>
                    <div class="text-xs text-slate-700 dark:text-slate-300 leading-tight line-clamp-2">{{ b.pickupAddress || '-' }}</div>
                  </div>
                </div>
                <div class="flex gap-1.5 items-start">
                  <i class="bi bi-flag-fill text-slate-400 text-xs mt-0.5"></i>
                  <div>
                    <div class="text-[10px] font-bold text-slate-500 uppercase">Antar</div>
                    <div class="text-xs text-slate-700 dark:text-slate-300 leading-tight line-clamp-2">{{ b.dropoffAddress || '-' }}</div>
                  </div>
                </div>
                <div class="mt-1 pt-1 border-t border-dashed border-slate-200">
                  <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-slate-100 text-slate-500 uppercase">{{ b.serviceType }}</span>
                  <span class="text-[9px] text-slate-400 ml-1">{{ getRouteLabel(b.routeId) }}</span>
                </div>
              </div>
            </td>
            <td class="p-4">
              <div class="flex flex-col gap-1">
                <span v-if="b.paymentMethod === 'Cash'" class="text-[10px] font-bold px-2 py-0.5 rounded bg-emerald-100 dark:bg-emerald-900 text-emerald-700 dark:text-emerald-400 uppercase flex w-fit items-center gap-1">
                  <i class="bi bi-cash"></i> Cash
                </span>
                <span v-else-if="b.paymentMethod === 'Transfer'" @click.stop="goToProof(b, 'payment')" class="text-[10px] font-bold px-2 py-0.5 rounded bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-400 uppercase flex w-fit items-center gap-1 cursor-pointer hover:underline">
                  <i class="bi bi-bank"></i> Transfer
                </span>
                <span v-else-if="b.paymentMethod === 'DP'" class="text-[10px] font-bold px-2 py-0.5 rounded bg-yellow-100 dark:bg-yellow-900 text-yellow-700 dark:text-yellow-400 uppercase flex w-fit items-center gap-1">
                  <i class="bi bi-hourglass-split"></i> DP
                </span>
                <span v-else class="text-[10px] font-bold px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400 uppercase">{{ b.paymentMethod || '-' }}</span>
              </div>
            </td>
            <td class="p-4">
              <div class="font-bold text-slate-800 dark:text-white">Rp {{ (b.totalPrice || 0).toLocaleString('id-ID') }}</div>
              <div v-if="b.serviceType === 'Dropping' || b.serviceType === 'Carter'" class="text-[9px] text-slate-400 mt-0.5">Harga Unit / Paket</div>
              <div v-else class="text-[9px] text-slate-400 mt-0.5">{{ b.seatCount }} x Rp {{ (b.seatCount > 0 ? (b.totalPrice / b.seatCount) : 0).toLocaleString('id-ID') }}</div>
            </td>
            <td class="p-4">
              <div v-if="b.validationStatus !== 'Valid' && b.totalPrice > (b.downPaymentAmount || 0)" class="font-bold text-red-600">
                Rp {{ ((b.totalPrice - (b.downPaymentAmount || 0)) || 0).toLocaleString('id-ID') }}
              </div>
              <div v-else class="text-xs text-green-600 font-bold">
                <i class="bi bi-check-circle-fill"></i> Lunas
              </div>
            </td>
            <td class="p-4">
              <span v-if="b.validationStatus === 'Valid'" class="text-xs font-bold text-green-600 bg-green-100 px-2 py-1 rounded">Lunas</span>
              <span v-else-if="b.paymentStatus === 'DP'" class="text-xs font-bold text-yellow-600 bg-yellow-100 px-2 py-1 rounded">DP</span>
              <span v-else-if="b.paymentStatus === 'Belum Bayar'" class="text-xs font-bold text-red-600 bg-red-100 px-2 py-1 rounded">Belum Bayar</span>
              <span v-else class="text-xs font-bold text-blue-500 bg-blue-100 px-2 py-1 rounded">Validasi</span>
            </td>
            <td class="p-4">
              <div class="text-[10px] text-slate-500 italic max-w-[150px] truncate">{{ b.bookingNote || '-' }}</div>
            </td>
            <td class="p-4">
              <div class="text-xs text-slate-600 dark:text-slate-400 font-medium whitespace-nowrap">
                {{ formatDateTime(b.input_date || b.created_at) }}
              </div>
            </td>
            <td class="p-4 text-right space-x-1" @click.stop>
              <button v-if="b.validationStatus !== 'Valid'" @click="validatePayment(b)" class="p-2 bg-blue-50 text-blue-600 hover:bg-blue-100 rounded-lg transition-colors" title="Validasi Pembayaran">
                <i class="bi bi-check-lg"></i>
              </button>
              <button v-if="(b.passengerType === 'Pelajar' || b.passengerType === 'Mahasiswa / Pelajar') && b.ktmProof" @click.stop="goToProof(b, 'ktm')" class="text-xs font-bold bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-300 px-2 py-1.5 rounded hover:bg-blue-200 dark:hover:bg-blue-900/50 transition-colors" title="Cek KTM">
                <i class="bi bi-card-image"></i> KTM
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Empty State -->
      <!-- Empty State -->
      <div v-if="!isLoading && filteredBookings.length === 0" class="p-8 text-center text-slate-400 italic">Tidak ada data.</div>
    </div>

    <!-- Pagination -->
    <div class="fixed bottom-0 right-0 z-30 p-4 border-t border-gray-200 dark:border-slate-700 bg-white/90 dark:bg-slate-800/90 backdrop-blur-sm flex justify-between items-center shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.1)] w-full">
      <div class="text-xs text-slate-500 dark:text-slate-400 font-medium">
        Menampilkan {{ paginatedBookings.length }} dari {{ filteredBookings.length }} data (Halaman {{ currentPage }} dari {{ totalPages }})
      </div>
      <div class="flex gap-2">
        <select v-model.number="itemsPerPage" class="bg-white dark:bg-slate-700 border border-gray-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 text-xs font-bold rounded-lg px-2 py-1.5 focus:outline-none focus:ring-1 focus:ring-blue-500">
          <option value="10">10 Data</option>
          <option value="20">20 Data</option>
          <option value="30">30 Data</option>
          <option value="50">50 Data</option>
          <option value="100">100 Data</option>
        </select>
        <button @click="currentPage--" :disabled="currentPage === 1" class="px-3 py-1.5 rounded-lg border bg-white dark:bg-slate-700 text-xs font-bold disabled:opacity-50 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-600 shadow-sm transition-colors">Prev</button>
        <span class="px-3 py-1.5 text-xs font-bold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-900 rounded-lg min-w-[3rem] text-center">{{ currentPage }} / {{ totalPages }}</span>
        <button @click="currentPage++" :disabled="currentPage >= totalPages" class="px-3 py-1.5 rounded-lg border bg-white dark:bg-slate-700 text-xs font-bold disabled:opacity-50 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-600 shadow-sm transition-colors">Next</button>
      </div>
    </div>

    <!-- Detail Modal (simplified for now) -->
    <div v-if="showDetailModal" @click.self="showDetailModal = false" class="fixed inset-0 bg-black/80 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
      <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl max-w-2xl w-full p-6">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-bold text-slate-800 dark:text-white">Detail Booking #{{ selectedBooking?.id }}</h3>
          <button @click="showDetailModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
            <i class="bi bi-x-lg text-xl"></i>
          </button>
        </div>
        <div class="space-y-4">
          <div class="grid grid-cols-2 gap-4 text-sm">
            <div><strong>Penumpang:</strong> {{ selectedBooking?.passengerName }}</div>
            <div><strong>Telepon:</strong> {{ selectedBooking?.passengerPhone }}</div>
            <div><strong>Tanggal:</strong> {{ formatDate(selectedBooking?.date) }}</div>
            <div><strong>Waktu:</strong> {{ selectedBooking?.time }}</div>
            <div><strong>Service:</strong> {{ selectedBooking?.serviceType }}</div>
            <div><strong>Total:</strong> Rp {{ (selectedBooking?.totalPrice || 0).toLocaleString('id-ID') }}</div>
            <div class="col-span-2"><strong>Waktu Input:</strong> {{ formatDateTime(selectedBooking?.input_date || selectedBooking?.created_at) }}</div>
          </div>
        </div>
        <div class="mt-6 flex justify-end">
          <button @click="showDetailModal = false" class="px-6 py-2 bg-blue-900 text-white font-bold rounded-lg">Tutup</button>
        </div>
      </div>
    </div>

    <!-- KTM Modal -->
    <div v-if="ktmModalUrl" @click.self="ktmModalUrl = null" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
      <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden">
        <button @click="ktmModalUrl = null" class="absolute top-4 right-4 bg-black/50 text-white w-8 h-8 rounded-full flex items-center justify-center hover:bg-black/70 transition-colors z-10">
          <i class="bi bi-x-lg"></i>
        </button>
        <div class="p-1 bg-slate-100 dark:bg-slate-700">
          <img :src="ktmModalUrl" class="w-full h-auto rounded-xl shadow-inner">
        </div>
        <div class="p-4 text-center">
          <h3 class="font-bold text-slate-800 dark:text-white">Bukti KTM / Kartu Pelajar</h3>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import Swal from 'sweetalert2';

const router = useRouter();

const isLoading = ref(false); // Loading state
const bookings = ref([]);
const masterRoutes = ref([]); // Store master routes
const activeTab = ref('travel');
const busViewMode = ref('list');
const searchTerm = ref('');
const filterMethod = ref('All');
const filterCategory = ref('');
const filterSort = ref('Newest');
const filterDate = ref('');
const filterRoute = ref('All');
const currentPage = ref(1);
const itemsPerPage = ref(20);
const showDetailModal = ref(false);
const selectedBooking = ref(null);
const ktmModalUrl = ref(null);

// Fetch bookings from API
const fetchBookings = async () => {
  isLoading.value = true;
  try {
    const response = await fetch('api.php?action=get_booking_history'); 
    const data = await response.json();
    if (data.status === 'success') {
      bookings.value = data.data || [];
    }
  } catch (error) {
    console.error('Error fetching bookings:', error);
    Swal.fire('Error', 'Gagal memuat data booking', 'error');
  } finally {
    isLoading.value = false;
  }
};

// Fetch master routes
const fetchMasterRoutes = async () => {
  try {
    const response = await fetch('api.php?action=get_initial_data');
    const data = await response.json();
    if (data.routes) {
      masterRoutes.value = data.routes;
    }
  } catch (error) {
    console.error('Error fetching master routes:', error);
  }
};

// Helper: Get Route Label
const getRouteLabel = (routeId) => {
  if (!routeId) return '-';
  if (String(routeId).startsWith('CUSTOM_')) return 'Carter / Dropping';
  
  const route = masterRoutes.value.find(r => r.id === routeId);
  if (route) {
    return `${route.origin} - ${route.destination}`;
  }
  return routeId;
};

// Computed: Unique Routes
const uniqueRoutes = computed(() => {
  const routes = bookings.value.map(b => b.routeId).filter(Boolean);
  return [...new Set(routes)].sort();
});

// Computed: Filtered Bookings
const filteredBookings = computed(() => {
  let filtered = bookings.value.filter(b => {
    // Tab filter
    if (activeTab.value === 'travel' && b.type === 'bus') return false;
    if (activeTab.value === 'bus' && b.type !== 'bus') return false;
    
    // Search filter
    if (searchTerm.value && !b.passengerName.toLowerCase().includes(searchTerm.value.toLowerCase()) && !b.passengerPhone.includes(searchTerm.value)) return false;
    
    // Method filter
    if (filterMethod.value !== 'All' && b.paymentMethod !== filterMethod.value) return false;
    
    // Category filter
    if (filterCategory.value && (b.passengerType === 'Pelajar' || b.passengerType === 'Mahasiswa / Pelajar') !== (filterCategory.value === 'Pelajar')) return false;
    
    // Date filter
    if (filterDate.value && b.date !== filterDate.value) return false;
    
    // Route filter
    if (filterRoute.value !== 'All' && b.routeId !== filterRoute.value) return false;
    
    return true;
  });

  // Sort
  if (filterSort.value === 'Newest') {
    filtered.sort((a, b) => new Date(b.date) - new Date(a.date));
  } else {
    filtered.sort((a, b) => new Date(a.date) - new Date(b.date));
  }

  return filtered;
});

// Computed: Paginated Bookings
const paginatedBookings = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value;
  const end = start + itemsPerPage.value;
  return filteredBookings.value.slice(start, end);
});

// Computed: Total Pages
const totalPages = computed(() => {
  return Math.ceil(filteredBookings.value.length / itemsPerPage.value) || 1;
});

// Format date
const formatDate = (date) => {
  if (!date) return '-';
  const d = new Date(date);
  return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
};

// Format datetime
const formatDateTime = (datetime) => {
  if (!datetime) return '-';
  const d = new Date(datetime);
  return d.toLocaleString('id-ID', { 
    day: '2-digit', 
    month: 'short', 
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

// Actions
const resetFilters = () => {
  filterMethod.value = 'All';
  filterCategory.value = '';
  filterSort.value = 'Newest';
  filterDate.value = '';
  filterRoute.value = 'All';
  searchTerm.value = '';
};

const openDetailModal = (booking) => {
  selectedBooking.value = booking;
  showDetailModal.value = true;
};

const goToProof = (booking, type) => {
  const date = booking.date; // Booking date
  const name = booking.passengerName;
  
  if (type === 'ktm') {
    router.push({ path: '/ktm-proofs', query: { date, search: name } });
  } else if (type === 'payment') {
    router.push({ path: '/proofs', query: { date, search: name } });
  }
};

const validatePayment = async (booking) => {
  const result = await Swal.fire({
    title: 'Validasi Pembayaran',
    text: `Konfirmasi pembayaran untuk ${booking.passengerName}?`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#0f172a',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Ya, Validasi',
    cancelButtonText: 'Batal'
  });

  if (result.isConfirmed) {
    try {
      const response = await fetch('api.php?action=validate_booking', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: booking.id })
      });
      const data = await response.json();
      if (data.status === 'success') {
        await Swal.fire('Berhasil', 'Pembayaran telah divalidasi', 'success');
        fetchBookings();
      } else {
        throw new Error(data.message);
      }
    } catch (error) {
      Swal.fire('Error', error.message || 'Gagal memvalidasi pembayaran', 'error');
    }
  }
};

onMounted(() => {
  fetchBookings();
  fetchMasterRoutes();
});
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
