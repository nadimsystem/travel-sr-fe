    <template>
  <div class="h-full flex flex-col custom-scrollbar overflow-hidden">
    <!-- Header -->
    <div class="mb-6">
        <h2 class="text-xl font-bold text-slate-800 dark:text-white">Manajemen Aset</h2>
        <p class="text-sm text-slate-500">Kelola Armada dan Supir Operasional</p>
    </div>

    <!-- Content Split -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 h-full overflow-hidden pb-20">
        
        <!-- FLEETS (ARMADA) -->
        <div class="flex flex-col h-full bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
             <div class="p-4 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-slate-50 dark:bg-slate-900/50">
                <div>
                    <h3 class="font-bold text-slate-800 dark:text-white">Armada</h3>
                    <div class="text-xs text-slate-500">Total: {{ fleets.length }} Unit</div>
                </div>
                <button @click="openFleetModal()" class="bg-blue-600 text-white px-3 py-1.5 rounded-lg text-xs font-bold shadow hover:bg-blue-700 transition-colors flex items-center gap-1">
                    <i class="bi bi-plus-lg"></i> Tambah
                </button>
            </div>
            <div class="flex-1 overflow-y-auto p-4 space-y-3 custom-scrollbar">
                <div v-if="fleets.length === 0" class="text-center text-slate-400 py-10 italic text-sm">Belum ada data armada.</div>
                
                <div v-for="f in fleets" :key="f.id" class="p-3 rounded-lg border border-slate-100 dark:border-slate-700 bg-white dark:bg-slate-700/30 hover:shadow-md transition-all group relative">
                    <div class="flex justify-between items-start">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-slate-100 dark:bg-slate-600 flex items-center justify-center text-slate-500 dark:text-slate-300 text-lg">
                                <i :class="f.icon || 'bi-car-front-fill'"></i>
                            </div>
                            <div>
                                <div class="font-bold text-slate-800 dark:text-white text-sm">{{ f.name }}</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400 font-mono">{{ f.plate }} • {{ f.capacity }} Seat</div>
                            </div>
                        </div>
                        <span class="text-[10px] font-bold px-2 py-1 rounded" :class="getStatusClass(f.status)">{{ f.status }}</span>
                    </div>
                    
                    <!-- Actions -->
                    <div class="absolute top-3 right-3 opacity-0 group-hover:opacity-100 transition-opacity flex gap-1 bg-white dark:bg-slate-800 p-1 rounded-lg shadow-sm border border-slate-100 dark:border-slate-600">
                        <button @click="openFleetModal(f)" class="w-6 h-6 rounded flex items-center justify-center text-blue-600 hover:bg-blue-50 dark:hover:bg-slate-700"><i class="bi bi-pencil-fill text-[10px]"></i></button>
                        <button @click="deleteFleet(f)" class="w-6 h-6 rounded flex items-center justify-center text-red-600 hover:bg-red-50 dark:hover:bg-slate-700"><i class="bi bi-trash-fill text-[10px]"></i></button>
                    </div>
                </div>
            </div>
        </div>

        <!-- DRIVERS (SUPIR) -->
        <div class="flex flex-col h-full bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
             <div class="p-4 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-slate-50 dark:bg-slate-900/50">
                <div>
                    <h3 class="font-bold text-slate-800 dark:text-white">Supir</h3>
                    <div class="text-xs text-slate-500">Total: {{ drivers.length }} Orang</div>
                </div>
                <button @click="openDriverModal()" class="bg-blue-600 text-white px-3 py-1.5 rounded-lg text-xs font-bold shadow hover:bg-blue-700 transition-colors flex items-center gap-1">
                    <i class="bi bi-plus-lg"></i> Tambah
                </button>
            </div>
            <div class="flex-1 overflow-y-auto p-4 space-y-3 custom-scrollbar">
                <div v-if="drivers.length === 0" class="text-center text-slate-400 py-10 italic text-sm">Belum ada data supir.</div>
                
                <div v-for="d in drivers" :key="d.id" class="p-3 rounded-lg border border-slate-100 dark:border-slate-700 bg-white dark:bg-slate-700/30 hover:shadow-md transition-all group relative">
                    <div class="flex justify-between items-start">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-600 flex items-center justify-center text-slate-500 dark:text-slate-300 text-lg">
                                <i class="bi bi-person-fill"></i>
                            </div>
                            <div>
                                <div class="font-bold text-slate-800 dark:text-white text-sm">{{ d.name }}</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400 flex items-center gap-1"><i class="bi bi-whatsapp"></i> {{ d.phone }}</div>
                                <div class="text-[10px] text-slate-400 mt-0.5">SIM: {{ d.licenseType || '-' }}</div>
                            </div>
                        </div>
                        <span class="text-[10px] font-bold px-2 py-1 rounded" :class="getDriverStatusClass(d.status)">{{ d.status }}</span>
                    </div>

                    <!-- Actions -->
                    <div class="absolute top-3 right-3 opacity-0 group-hover:opacity-100 transition-opacity flex gap-1 bg-white dark:bg-slate-800 p-1 rounded-lg shadow-sm border border-slate-100 dark:border-slate-600">
                        <button @click="openDriverModal(d)" class="w-6 h-6 rounded flex items-center justify-center text-blue-600 hover:bg-blue-50 dark:hover:bg-slate-700"><i class="bi bi-pencil-fill text-[10px]"></i></button>
                        <button @click="deleteDriver(d)" class="w-6 h-6 rounded flex items-center justify-center text-red-600 hover:bg-red-50 dark:hover:bg-slate-700"><i class="bi bi-trash-fill text-[10px]"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Fleet Modal -->
    <div v-if="fleetModal.isOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" @click.self="fleetModal.isOpen = false">
        <div class="bg-white dark:bg-slate-800 w-full max-w-md rounded-2xl shadow-xl overflow-hidden animate-fade-in-up">
            <div class="p-4 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-slate-50 dark:bg-slate-800/50">
                <h3 class="font-bold text-lg text-slate-800 dark:text-white">{{ fleetModal.mode === 'add' ? 'Tambah Armada' : 'Edit Armada' }}</h3>
                <button @click="fleetModal.isOpen = false" class="text-slate-400 hover:text-slate-600"><i class="bi bi-x-lg"></i></button>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Nama Unit</label>
                    <input type="text" v-model="fleetModal.data.name" class="w-full p-2.5 border rounded-lg bg-white dark:bg-slate-700 border-slate-200 dark:border-slate-600 text-sm focus:ring-2 focus:ring-blue-500 outline-none text-slate-800 dark:text-white" placeholder="Contoh: Hiace 01">
                </div>
                <div class="grid grid-cols-2 gap-4">
                     <div>
                        <label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Plat Nomor</label>
                        <input type="text" v-model="fleetModal.data.plate" class="w-full p-2.5 border rounded-lg bg-white dark:bg-slate-700 border-slate-200 dark:border-slate-600 text-sm focus:ring-2 focus:ring-blue-500 outline-none text-slate-800 dark:text-white" placeholder="BA 1234 XX">
                    </div>
                     <div>
                        <label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Kapasitas</label>
                        <input type="number" v-model="fleetModal.data.capacity" class="w-full p-2.5 border rounded-lg bg-white dark:bg-slate-700 border-slate-200 dark:border-slate-600 text-sm focus:ring-2 focus:ring-blue-500 outline-none text-slate-800 dark:text-white">
                    </div>
                </div>
                 <div>
                    <label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Status</label>
                    <select v-model="fleetModal.data.status" class="w-full p-2.5 border rounded-lg bg-white dark:bg-slate-700 border-slate-200 dark:border-slate-600 text-sm focus:ring-2 focus:ring-blue-500 outline-none text-slate-800 dark:text-white">
                        <option value="Tersedia">Tersedia</option>
                        <option value="Perbaikan">Perbaikan</option>
                        <option value="On Trip">On Trip</option>
                    </select>
                </div>
                <div>
                     <label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Icon</label>
                     <div class="flex gap-2">
                        <button v-for="icon in ['bi-truck-front-fill', 'bi-bus-front-fill', 'bi-car-front-fill']" 
                            @click="fleetModal.data.icon = icon" 
                            class="w-10 h-10 rounded-lg border flex items-center justify-center text-lg transition-colors"
                            :class="fleetModal.data.icon === icon ? 'bg-blue-50 border-blue-500 text-blue-600' : 'border-slate-200 dark:border-slate-600 text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700'">
                            <i :class="icon"></i>
                        </button>
                     </div>
                </div>
            </div>
            <div class="p-4 border-t border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-end gap-3">
                <button @click="fleetModal.isOpen = false" class="px-4 py-2 text-slate-500 font-bold hover:bg-slate-200 rounded-lg text-sm transition-colors">Batal</button>
                <button @click="saveFleet" class="px-6 py-2 bg-blue-600 text-white font-bold rounded-lg shadow hover:bg-blue-700 text-sm transition-colors">Simpan</button>
            </div>
        </div>
    </div>

    <!-- Driver Modal -->
    <div v-if="driverModal.isOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" @click.self="driverModal.isOpen = false">
        <div class="bg-white dark:bg-slate-800 w-full max-w-md rounded-2xl shadow-xl overflow-hidden animate-fade-in-up">
            <div class="p-4 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-slate-50 dark:bg-slate-800/50">
                <h3 class="font-bold text-lg text-slate-800 dark:text-white">{{ driverModal.mode === 'add' ? 'Tambah Supir' : 'Edit Supir' }}</h3>
                <button @click="driverModal.isOpen = false" class="text-slate-400 hover:text-slate-600"><i class="bi bi-x-lg"></i></button>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Nama Lengkap</label>
                    <input type="text" v-model="driverModal.data.name" class="w-full p-2.5 border rounded-lg bg-white dark:bg-slate-700 border-slate-200 dark:border-slate-600 text-sm focus:ring-2 focus:ring-blue-500 outline-none text-slate-800 dark:text-white" placeholder="Nama Supir">
                </div>
                <div>
                    <label class="text-xs font-bold text-slate-500 uppercase mb-1 block">No. WhatsApp</label>
                    <input type="text" v-model="driverModal.data.phone" class="w-full p-2.5 border rounded-lg bg-white dark:bg-slate-700 border-slate-200 dark:border-slate-600 text-sm focus:ring-2 focus:ring-blue-500 outline-none text-slate-800 dark:text-white" placeholder="08...">
                </div>
                 <div class="grid grid-cols-2 gap-4">
                     <div>
                        <label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Jenis SIM</label>
                         <select v-model="driverModal.data.licenseType" class="w-full p-2.5 border rounded-lg bg-white dark:bg-slate-700 border-slate-200 dark:border-slate-600 text-sm focus:ring-2 focus:ring-blue-500 outline-none text-slate-800 dark:text-white">
                            <option value="A Umum">A Umum</option>
                            <option value="B1 Umum">B1 Umum</option>
                            <option value="B2 Umum">B2 Umum</option>
                        </select>
                    </div>
                     <div>
                        <label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Status</label>
                         <select v-model="driverModal.data.status" class="w-full p-2.5 border rounded-lg bg-white dark:bg-slate-700 border-slate-200 dark:border-slate-600 text-sm focus:ring-2 focus:ring-blue-500 outline-none text-slate-800 dark:text-white">
                            <option value="Standby">Standby</option>
                            <option value="Jalan">Jalan</option>
                            <option value="Libur">Libur</option>
                        </select>
                    </div>
                </div>
            </div>
             <div class="p-4 border-t border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-end gap-3">
                <button @click="driverModal.isOpen = false" class="px-4 py-2 text-slate-500 font-bold hover:bg-slate-200 rounded-lg text-sm transition-colors">Batal</button>
                <button @click="saveDriver" class="px-6 py-2 bg-blue-600 text-white font-bold rounded-lg shadow hover:bg-blue-700 text-sm transition-colors">Simpan</button>
            </div>
        </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

const fleets = ref([]);
const drivers = ref([]);


const fleetModal = ref({
    isOpen: false,
    mode: 'add',
    data: { id: '', name: '', plate: '', capacity: 14, status: 'Tersedia', icon: 'bi-car-front-fill' }
});


const driverModal = ref({
    isOpen: false,
    mode: 'add',
    data: { id: '', name: '', phone: '', licenseType: 'A Umum', status: 'Standby' }
});

onMounted(() => {
    fetchData();
});

const fetchData = async () => {
    try {
        const res = await axios.get('api.php?action=get_initial_data');
        if (res.data.fleet) fleets.value = res.data.fleet;
        if (res.data.drivers) drivers.value = res.data.drivers;
    } catch (e) {
        console.error(e);
    }
};

const getStatusClass = (status) => {
    if (status === 'Tersedia') return 'bg-green-100 text-green-700';
    if (status === 'Perbaikan') return 'bg-red-100 text-red-700';
    if (status === 'On Trip') return 'bg-blue-100 text-blue-700';
    return 'bg-slate-100 text-slate-600';
};

const getDriverStatusClass = (status) => {
    if (status === 'Standby') return 'bg-green-100 text-green-700';
    if (status === 'Jalan') return 'bg-blue-100 text-blue-700';
    if (status === 'Libur') return 'bg-red-100 text-red-700';
    return 'bg-slate-100 text-slate-600';
};

// FLEET ACTIONS
const openFleetModal = (fleet = null) => {
    if (fleet) {
        fleetModal.value.mode = 'edit';
        fleetModal.value.data = { ...fleet };
    } else {
        fleetModal.value.mode = 'add';
        fleetModal.value.data = { id: Date.now().toString(), name: '', plate: '', capacity: 14, status: 'Tersedia', icon: 'bi-car-front-fill' };
    }
    fleetModal.value.isOpen = true;
};

const saveFleet = async () => {
    try {
        const payload = { ...fleetModal.value.data };
        const action = 'save_fleet'; // Unified endpoint in master_data.php
        
        const res = await axios.post('api.php', {
            action,
            ...payload
        });

        if (res.data.status === 'success') {
            Swal.fire({ icon: 'success', title: 'Berhasil', showConfirmButton: false, timer: 1500 });
            fleetModal.value.isOpen = false;
            fetchData();
        } else {
            Swal.fire('Error', res.data.message || 'Gagal menyimpan', 'error');
        }
    } catch (e) {
        console.error(e);
        Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
    }
};

const deleteFleet = (fleet) => {
    Swal.fire({
        title: 'Hapus Armada?',
        text: `Hapus ${fleet.name}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Ya, Hapus'
    }).then(async (result) => {
        if (result.isConfirmed) {
            const res = await axios.post('api.php', { action: 'delete_fleet', id: fleet.id });
            if (res.data.status === 'success') {
                fetchData();
                Swal.fire('Terhapus!', '', 'success');
            }
        }
    });
};


const openDriverModal = (driver = null) => {
    if (driver) {
        driverModal.value.mode = 'edit';
        driverModal.value.data = { ...driver };
    } else {
        driverModal.value.mode = 'add';
        driverModal.value.data = { id: Date.now().toString(), name: '', phone: '', licenseType: 'A Umum', status: 'Standby' };
    }
    driverModal.value.isOpen = true;
};

const saveDriver = async () => {
    try {
        const payload = { ...driverModal.value.data };
        const action = 'save_driver';

        const res = await axios.post('api.php', {
            action,
            ...payload
        });

        if (res.data.status === 'success') {
            Swal.fire({ icon: 'success', title: 'Berhasil', showConfirmButton: false, timer: 1500 });
            driverModal.value.isOpen = false;
            fetchData();
        } else {
            Swal.fire('Error', res.data.message || 'Gagal menyimpan', 'error');
        }
    } catch (e) {
        console.error(e);
        Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
    }
};

const deleteDriver = (driver) => {
    Swal.fire({
        title: 'Hapus Supir?',
        text: `Hapus ${driver.name}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Ya, Hapus'
    }).then(async (result) => {
        if (result.isConfirmed) {
            const res = await axios.post('api.php', { action: 'delete_driver', id: driver.id });
            if (res.data.status === 'success') {
                fetchData();
                Swal.fire('Terhapus!', '', 'success');
            }
        }
    });
};
</script>
