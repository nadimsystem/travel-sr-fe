<template>
  <div class="h-full flex flex-col custom-scrollbar overflow-hidden">
    <!-- Header -->
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h2 class="text-xl font-bold text-slate-800 dark:text-white">Kelola Rute</h2>
            <p class="text-sm text-slate-500">Atur Jadwal dan Harga Tiket</p>
        </div>
        <button @click="openRouteModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg font-bold shadow-lg hover:bg-blue-700 transition-colors flex items-center gap-2">
            <i class="bi bi-plus-lg"></i> Tambah Rute
        </button>
    </div>

    <!-- Route Grid -->
    <div class="flex-1 overflow-y-auto pb-20 custom-scrollbar">
        <div v-if="routes.length === 0" class="flex flex-col items-center justify-center h-64 text-slate-400">
            <i class="bi bi-map text-4xl mb-2"></i>
            <p>Belum ada rute terdaftar.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            <div v-for="r in routes" :key="r.id" class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-5 relative overflow-hidden group hover:border-blue-300 dark:hover:border-blue-700 transition-all">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <div class="text-lg font-bold text-slate-800 dark:text-white flex items-center gap-2">
                            {{ r.origin }} <i class="bi bi-arrow-right text-slate-400 text-sm"></i> {{ r.destination }}
                        </div>
                        <div class="text-xs text-slate-500 dark:text-slate-400 font-mono mt-1">{{ r.id }}</div>
                    </div>
                    <div class="flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button @click="openRouteModal(r)" class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 hover:bg-blue-100 dark:bg-slate-700 dark:text-blue-400 dark:hover:bg-slate-600 flex items-center justify-center"><i class="bi bi-pencil-fill text-xs"></i></button>
                        <button @click="deleteRoute(r)" class="w-8 h-8 rounded-full bg-red-50 text-red-600 hover:bg-red-100 dark:bg-slate-700 dark:text-red-400 dark:hover:bg-slate-600 flex items-center justify-center"><i class="bi bi-trash-fill text-xs"></i></button>
                    </div>
                </div>
                
                <div class="space-y-3">
                    <!-- Schedules with Show/Hide Toggle -->
                    <div class="bg-slate-50 dark:bg-slate-700/30 p-3 rounded-lg">
                        <div class="flex items-center justify-between mb-2">
                            <div class="text-[10px] font-bold text-slate-400 uppercase">Jadwal Keberangkatan</div>
                            <div class="text-[10px] text-slate-400 italic">Klik mata untuk tampil/sembunyikan</div>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <div
                                v-for="sched in normalizeSchedules(r.schedules)"
                                :key="sched.time"
                                class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg border text-xs font-bold transition-all cursor-pointer select-none"
                                :class="sched.hidden
                                    ? 'bg-slate-100 dark:bg-slate-600/30 border-slate-200 dark:border-slate-600 text-slate-400 dark:text-slate-500 line-through'
                                    : 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-700 text-green-700 dark:text-green-400'"
                                @click="toggleScheduleVisibility(r, sched.time)"
                                :title="sched.hidden ? 'Sembunyikan dari pelanggan (klik untuk tampilkan)' : 'Tampil ke pelanggan (klik untuk sembunyikan)'"
                            >
                                <i :class="sched.hidden ? 'bi bi-eye-slash-fill text-slate-400' : 'bi bi-eye-fill text-green-500'"></i>
                                {{ sched.time }}
                            </div>
                        </div>
                        <div v-if="!r.schedules || r.schedules.length === 0" class="text-xs text-slate-400 italic">Belum ada jadwal.</div>
                    </div>
                    
                    <!-- Prices -->
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-slate-50 dark:bg-slate-700/30 p-2.5 rounded-lg">
                            <div class="text-[10px] font-bold text-slate-400 uppercase mb-1">Umum</div>
                            <div class="font-bold text-slate-700 dark:text-slate-200 text-sm">{{ formatRupiah(r.prices?.umum || 0) }}</div>
                        </div>
                        <div class="bg-slate-50 dark:bg-slate-700/30 p-2.5 rounded-lg">
                            <div class="text-[10px] font-bold text-slate-400 uppercase mb-1">Pelajar</div>
                            <div class="font-bold text-slate-700 dark:text-slate-200 text-sm">{{ formatRupiah(r.prices?.pelajar || 0) }}</div>
                        </div>
                        <div class="bg-slate-50 dark:bg-slate-700/30 p-2.5 rounded-lg">
                            <div class="text-[10px] font-bold text-slate-400 uppercase mb-1">Dropping</div>
                            <div class="font-bold text-slate-700 dark:text-slate-200 text-sm">{{ formatRupiah(r.prices?.dropping || 0) }}</div>
                        </div>
                        <div class="bg-slate-50 dark:bg-slate-700/30 p-2.5 rounded-lg">
                            <div class="text-[10px] font-bold text-slate-400 uppercase mb-1">Carter</div>
                            <div class="font-bold text-slate-700 dark:text-slate-200 text-sm">{{ formatRupiah(r.prices?.carter || 0) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Route Modal -->
    <div v-if="routeModal.isOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" @click.self="routeModal.isOpen = false">
        <div class="bg-white dark:bg-slate-800 w-full max-w-2xl rounded-2xl shadow-xl flex flex-col max-h-[90vh] animate-fade-in-up">
             <div class="p-6 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-slate-50 dark:bg-slate-800/50">
                <h3 class="font-bold text-lg text-slate-800 dark:text-white">{{ routeModal.mode === 'add' ? 'Tambah Rute Baru' : 'Edit Rute' }}</h3>
                <button @click="routeModal.isOpen = false" class="text-slate-400 hover:text-slate-600"><i class="bi bi-x-lg"></i></button>
            </div>
            
            <div class="p-6 overflow-y-auto custom-scrollbar space-y-6">
                <!-- ID, Origin & Desc -->
                <div class="grid grid-cols-3 gap-4">
                     <div>
                        <label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Kode Rute (ID)</label>
                        <input type="text" v-model="routeModal.data.id" class="w-full p-2.5 border rounded-lg bg-white dark:bg-slate-700 border-slate-200 dark:border-slate-600 text-sm text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Contoh: PDG-PKU">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Asal (Origin)</label>
                        <input type="text" v-model="routeModal.data.origin" class="w-full p-2.5 border rounded-lg bg-white dark:bg-slate-700 border-slate-200 dark:border-slate-600 text-sm text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Kota Asal">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Tujuan (Destination)</label>
                        <input type="text" v-model="routeModal.data.destination" class="w-full p-2.5 border rounded-lg bg-white dark:bg-slate-700 border-slate-200 dark:border-slate-600 text-sm text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Kota Tujuan">
                    </div>
                </div>

                <!-- Schedules -->
                <div>
                     <label class="text-xs font-bold text-slate-500 uppercase mb-2 block">Jadwal (Pisahkan dengan koma)</label>
                     <input type="text" v-model="routeModal.data.schedulesInput" class="w-full p-2.5 border rounded-lg bg-white dark:bg-slate-700 border-slate-200 dark:border-slate-600 text-sm font-mono text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none" placeholder="08:00, 10:00, 14:00">
                     <p class="text-[10px] text-slate-400 mt-1">Tambah jadwal baru di sini. Show/hide diatur dari kartu rute.</p>
                     <div class="mt-2 flex flex-wrap gap-2">
                         <span v-for="t in parsedSchedules" :key="t" class="bg-blue-50 text-blue-600 px-2 py-1 rounded text-xs font-bold">{{ t }}</span>
                     </div>
                </div>

                <!-- Prices -->
                 <div class="space-y-4 border-t pt-4 border-slate-100 dark:border-slate-700">
                    <h4 class="font-bold text-slate-700 dark:text-slate-300">Harga Tiket</h4>
                    <div class="grid grid-cols-2 gap-4">
                         <div><label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Umum</label><input type="number" v-model="routeModal.data.prices.umum" class="w-full p-2.5 border rounded-lg bg-white dark:bg-slate-700 border-slate-200 dark:border-slate-600 text-sm text-slate-800 dark:text-white"></div>
                         <div><label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Pelajar</label><input type="number" v-model="routeModal.data.prices.pelajar" class="w-full p-2.5 border rounded-lg bg-white dark:bg-slate-700 border-slate-200 dark:border-slate-600 text-sm text-slate-800 dark:text-white"></div>
                         <div><label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Dropping</label><input type="number" v-model="routeModal.data.prices.dropping" class="w-full p-2.5 border rounded-lg bg-white dark:bg-slate-700 border-slate-200 dark:border-slate-600 text-sm text-slate-800 dark:text-white"></div>
                         <div><label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Carter</label><input type="number" v-model="routeModal.data.prices.carter" class="w-full p-2.5 border rounded-lg bg-white dark:bg-slate-700 border-slate-200 dark:border-slate-600 text-sm text-slate-800 dark:text-white"></div>
                    </div>
                </div>

                <!-- Payroll Config -->
                 <div class="space-y-4 border-t pt-4 border-slate-100 dark:border-slate-700">
                    <h4 class="font-bold text-slate-700 dark:text-slate-300">Konfigurasi Gaji Supir</h4>
                    <div class="grid grid-cols-2 gap-4">
                         <div>
                            <label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Gaji Penumpang 1-6</label>
                            <input type="number" v-model="routeModal.data.prices.payroll_1_6" class="w-full p-2.5 border rounded-lg bg-white dark:bg-slate-700 border-slate-200 dark:border-slate-600 text-sm text-slate-800 dark:text-white">
                        </div>
                         <div>
                            <label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Gaji Penumpang Full (7+)</label>
                            <input type="number" v-model="routeModal.data.prices.payroll_full" class="w-full p-2.5 border rounded-lg bg-white dark:bg-slate-700 border-slate-200 dark:border-slate-600 text-sm text-slate-800 dark:text-white">
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-4 border-t border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-end gap-3">
                <button @click="routeModal.isOpen = false" class="px-4 py-2 text-slate-500 font-bold hover:bg-slate-200 rounded-lg text-sm transition-colors">Batal</button>
                <button @click="saveRoute" class="px-6 py-2 bg-blue-600 text-white font-bold rounded-lg shadow hover:bg-blue-700 text-sm transition-colors">Simpan Rute</button>
            </div>
        </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

const routes = ref([]);

const routeModal = ref({
    isOpen: false,
    mode: 'add',
    data: {
        id: '',
        original_id: '',
        origin: '',
        destination: '',
        schedulesInput: '',
        prices: { umum: 0, pelajar: 0, dropping: 0, carter: 0, payroll_1_6: 0, payroll_full: 0 }
    }
});

const parsedSchedules = computed(() => {
    if (!routeModal.value.data.schedulesInput) return [];
    return routeModal.value.data.schedulesInput.split(',').map(s => s.trim()).filter(s => s);
});

onMounted(() => {
    fetchRoutes();
});

const formatRupiah = (val) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val || 0);

/**
 * Normalize schedules dari format lama (string[]) ke format baru ({time, hidden}[])
 * Sehingga kompatibel dengan data yang sudah ada di DB.
 */
const normalizeSchedules = (schedules) => {
    if (!schedules || schedules.length === 0) return [];
    return schedules.map(s => {
        if (typeof s === 'string') return { time: s, hidden: false };
        return { time: s.time, hidden: !!s.hidden };
    });
};

const fetchRoutes = async () => {
    try {
        const res = await axios.get('api.php?action=get_initial_data');
        if (res.data.routes) {
            routes.value = res.data.routes;
        }
    } catch (e) {
        console.error(e);
    }
};

const openRouteModal = (route = null) => {
    if (route) {
        // For schedulesInput, use time strings only (normalized)
        const scheduleStrings = normalizeSchedules(route.schedules).map(s => s.time);
        routeModal.value.mode = 'edit';
        routeModal.value.data = {
            id: route.id,
            original_id: route.id,
            origin: route.origin,
            destination: route.destination,
            schedulesInput: scheduleStrings.join(', '),
            prices: { ...route.prices }
        };
    } else {
        routeModal.value.mode = 'add';
        routeModal.value.data = {
            id: '', 
            original_id: '',
            origin: '',
            destination: '',
            schedulesInput: '',
            prices: { umum: 0, pelajar: 0, dropping: 0, carter: 0, payroll_1_6: 0, payroll_full: 0 }
        };
    }
    routeModal.value.isOpen = true;
};

/**
 * Toggle visibilitas satu jadwal pada route tertentu.
 * Mempertahankan status hidden/visible jadwal lain,
 * lalu langsung menyimpan ke backend.
 */
const toggleScheduleVisibility = async (route, time) => {
    // Get current normalized schedules
    const normalized = normalizeSchedules(route.schedules);
    
    // Toggle the one that was clicked
    const updated = normalized.map(s => {
        if (s.time === time) return { ...s, hidden: !s.hidden };
        return s;
    });

    // Update UI immediately (optimistic)
    const routeIndex = routes.value.findIndex(r => r.id === route.id);
    if (routeIndex !== -1) {
        routes.value[routeIndex].schedules = updated;
    }

    // Persist to backend
    try {
        const res = await axios.post('api.php', {
            action: 'save_route',
            id: route.id,
            original_id: route.id,
            origin: route.origin,
            destination: route.destination,
            schedules: updated,
            prices: route.prices
        });

        if (res.data.status !== 'success') {
            // Revert on failure
            if (routeIndex !== -1) {
                routes.value[routeIndex].schedules = normalizeSchedules(route.schedules);
            }
            Swal.fire('Gagal', res.data.message || 'Gagal menyimpan jadwal', 'error');
        }
    } catch (e) {
        console.error(e);
        if (routeIndex !== -1) {
            routes.value[routeIndex].schedules = normalizeSchedules(route.schedules);
        }
        Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
    }
};

const saveRoute = async () => {
    try {
        const route = routes.value.find(r => r.id === routeModal.value.data.original_id);
        const existingNormalized = route ? normalizeSchedules(route.schedules) : [];

        // Parse new time list from input
        const newTimes = (routeModal.value.data.schedulesInput || '')
            .split(',').map(s => s.trim()).filter(s => s);

        // Merge: keep hidden state for existing times, set hidden=false for new ones
        const mergedSchedules = newTimes.map(time => {
            const existing = existingNormalized.find(s => s.time === time);
            return existing ? existing : { time, hidden: false };
        });

        const payload = { ...routeModal.value.data };

        if (routeModal.value.mode === 'add' && !payload.id && payload.origin && payload.destination) {
             payload.id = payload.origin.substring(0,3).toUpperCase() + '-' + payload.destination.substring(0,3).toUpperCase();
        }

        const res = await axios.post('api.php', {
            action: 'save_route',
            ...payload,
            schedules: mergedSchedules,
            original_id: routeModal.value.data.original_id
        });

        if (res.data.status === 'success') {
            Swal.fire({ icon: 'success', title: 'Berhasil', showConfirmButton: false, timer: 1500 });
            routeModal.value.isOpen = false;
            fetchRoutes();
        } else {
            Swal.fire('Error', res.data.message || 'Gagal menyimpan', 'error');
        }
    } catch (e) {
        console.error(e);
        Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
    }
};

const deleteRoute = (route) => {
    Swal.fire({
        title: 'Hapus Rute?',
        text: `Hapus rute ${route.origin} - ${route.destination}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Ya, Hapus'
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                const res = await axios.post('api.php', { action: 'delete_route', id: route.id });
                 if (res.data.status === 'success') {
                    fetchRoutes();
                    Swal.fire('Terhapus!', '', 'success');
                } else {
                    Swal.fire('Gagal', res.data.message, 'error');
                }
            } catch (e) {
                 Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
            }
        }
    });
};
</script>
