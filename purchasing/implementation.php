<!DOCTYPE html>
<html lang="id" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Implementasi Barang - Purchasing Sutan Raya</title>
    <link rel="icon" type="image/webp" href="../image/logo.webp">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        [v-cloak] { display: none; }
        .animate-fade-in { animation: fadeIn 0.3s ease-out forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
    <script>
        tailwind.config = { darkMode: 'class' }
    </script>
</head>
<body class="h-screen w-screen bg-slate-50 dark:bg-slate-900">

    <div id="app" class="flex h-full w-full" v-cloak>
        
        <?php $currentPage = 'purchasing_implementation'; include 'components/sidebar.php'; ?>

        <main class="flex-1 flex flex-col min-w-0 bg-slate-50 dark:bg-slate-900 overflow-hidden">
            <header class="h-16 bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between px-6 shadow-sm z-10 shrink-0">
                <div class="flex items-center gap-3">
                    <button onclick="toggleSidebar()" class="lg:hidden text-slate-600 dark:text-slate-300 hover:text-blue-600 p-2 -ml-2">
                        <i class="bi bi-list text-2xl"></i>
                    </button>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800 dark:text-white">Penggunaan Barang</h2>
                        <p class="text-xs text-slate-500">Catat setiap barang yang keluar dari gudang. Entah itu dipasang ke Armada (Service) atau dipakai untuk kebutuhan kantor umum.</p>
                    </div>
                </div>
                <button @click="openDeployModal" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-bold shadow-lg flex items-center gap-2">
                    <i class="bi bi-box-arrow-right"></i> Catat Penggunaan
                </button>
            </header>

            <div class="flex-1 overflow-hidden p-6 custom-scrollbar overflow-y-auto">
                
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
                        <div class="text-xs font-bold text-slate-400 uppercase mb-1">Total Deployment</div>
                        <div class="text-2xl font-extrabold text-slate-800 dark:text-white">{{ deployments.length }}</div>
                    </div>
                    <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
                        <div class="text-xs font-bold text-slate-400 uppercase mb-1">Bulan Ini</div>
                        <div class="text-2xl font-extrabold text-blue-600">{{ deploymentsThisMonth }}</div>
                    </div>
                    <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
                        <div class="text-xs font-bold text-slate-400 uppercase mb-1">Item Digunakan</div>
                        <div class="text-2xl font-extrabold text-green-600">{{ uniqueItemsDeployed }}</div>
                    </div>
                    <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
                        <div class="text-xs font-bold text-slate-400 uppercase mb-1">Armada Terlayani</div>
                        <div class="text-2xl font-extrabold text-purple-600">{{ uniqueFleetServed }}</div>
                    </div>
                </div>

                <!-- Deployment History Table -->
                <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
                    <div class="p-6 border-b border-slate-100 dark:border-slate-700">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-bold text-slate-800 dark:text-white">Riwayat Implementasi</h3>
                            <div class="relative">
                                <input type="text" v-model="searchQuery" placeholder="Cari barang, armada..." class="pl-10 pr-4 py-2 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-sm focus:ring-2 focus:ring-blue-500/20 outline-none">
                                <i class="bi bi-search absolute left-3 top-2.5 text-slate-400"></i>
                            </div>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-slate-50 dark:bg-slate-700/50 text-xs font-bold text-slate-500 uppercase">
                                <tr>
                                    <th class="p-4">Tanggal</th>
                                    <th class="p-4">Nama Barang</th>
                                    <th class="p-4">Qty</th>
                                    <th class="p-4">Armada</th>
                                    <th class="p-4">Diambil Oleh</th>
                                    <th class="p-4">Keperluan</th>
                                    <th class="p-4">Status</th>
                                    <th class="p-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700 text-sm">
                                <tr v-for="dep in filteredDeployments" :key="dep.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition">
                                    <td class="p-4 text-slate-500">{{ formatDate(dep.deployment_date) }}</td>
                                    <td class="p-4 font-bold text-slate-700 dark:text-slate-200">{{ dep.item_name }}</td>
                                    <td class="p-4 font-bold">{{ dep.qty_deployed }} <span class="text-xs text-slate-400">pcs</span></td>
                                    <td class="p-4">
                                        <span class="px-2 py-1 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 text-xs font-bold">
                                            {{ dep.deployed_to_name }}
                                        </span>
                                    </td>
                                    <td class="p-4 text-slate-600 dark:text-slate-300">{{ dep.deployed_by }}</td>
                                    <td class="p-4 text-slate-500 text-xs">{{ dep.reason }}</td>
                                    <td class="p-4">
                                        <span class="px-2 py-1 rounded-full bg-green-100 text-green-700 text-[10px] font-bold">{{ dep.status }}</span>
                                    </td>
                                    <td class="p-4 text-right">
                                        <button @click="viewDeploymentDetail(dep)" class="text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 p-2 rounded-lg transition">
                                            <i class="bi bi-eye-fill"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="filteredDeployments.length === 0">
                                    <td colspan="8" class="p-8 text-center text-slate-400">
                                        <i class="bi bi-inbox text-4xl mb-2 block"></i>
                                        Belum ada data deployment
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Deploy Modal -->
            <div v-if="showDeployModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm animate-fade-in">
                <div class="bg-white dark:bg-slate-800 w-full max-w-2xl rounded-3xl shadow-2xl p-6 max-h-[90vh] overflow-y-auto custom-scrollbar">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-bold text-slate-800 dark:text-white">Catat Penggunaan Barang</h3>
                        <button @click="closeDeployModal" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-500 hover:text-red-500">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="text-xs font-bold text-slate-500 block mb-1">Pilih Barang dari Stok</label>
                            <select v-model="newDeployment.item_id" @change="loadItemDetails" class="w-full p-3 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-sm font-bold focus:outline-none">
                                <option value="">-- Pilih Barang --</option>
                                <option v-for="item in stockItems" :key="item.id" :value="item.id">
                                    {{ item.name }} (Stok: {{ item.stock }} {{ item.unit }})
                                </option>
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-bold text-slate-500 block mb-1">Jumlah</label>
                                <input type="number" v-model="newDeployment.qty_deployed" min="1" :max="selectedItemStock" class="w-full p-3 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-sm font-bold focus:ring-2 focus:ring-blue-500/20 outline-none">
                                <p class="text-[10px] text-slate-400 mt-1">Max: {{ selectedItemStock }}</p>
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-500 block mb-1">Armada (Opsional)</label>
                                <select v-model="newDeployment.deployed_to_fleet_id" @change="updateFleetName" class="w-full p-3 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-sm font-bold focus:outline-none">
                                    <option value="">-- Tanpa Armada / Umum --</option>
                                    <option v-for="bus in fleet" :key="bus.id" :value="bus.id">
                                        {{ bus.name }} - {{ bus.plate }}
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="text-xs font-bold text-slate-500 block mb-1">Penerima / Diambil Oleh</label>
                            <input type="text" v-model="newDeployment.deployed_by" class="w-full p-3 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-sm font-bold focus:ring-2 focus:ring-blue-500/20 outline-none" placeholder="Nama lengkap">
                        </div>
                        <div>
                            <label class="text-xs font-bold text-slate-500 block mb-1">Keperluan / Alasan</label>
                            <textarea v-model="newDeployment.reason" class="w-full p-3 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-sm h-20 focus:ring-2 focus:ring-blue-500/20 outline-none" placeholder="Contoh: Service AC, Ganti Filter Oli, Perbaikan Suspensi"></textarea>
                        </div>
                        <div>
                            <label class="text-xs font-bold text-slate-500 block mb-1">Foto Bukti (Opsional)</label>
                            <label class="flex flex-col items-center justify-center w-full h-24 border-2 border-dashed border-slate-300 dark:border-slate-600 rounded-xl cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-700/50 transition">
                                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                    <i class="bi bi-camera text-2xl text-slate-400 mb-1"></i>
                                    <p class="text-xs text-slate-500" v-if="!newDeployment.photo">Upload foto (opsional)</p>
                                    <p class="text-xs text-blue-600 font-bold" v-else>{{ newDeployment.photo.name }}</p>
                                </div>
                                <input type="file" class="hidden" @change="handlePhotoUpload" accept="image/*">
                            </label>
                        </div>
                        <button @click="saveDeployment" class="w-full py-3 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition shadow-lg">
                            <i class="bi bi-box-arrow-right mr-2"></i> Catat Penggunaan
                        </button>
                    </div>
                </div>
            </div>

        </main>
        
        <?php include 'components/sidebar_right.php'; ?>

    </div>

    <script>
        const { createApp, ref, computed, onMounted } = Vue;

        createApp({
            setup() {
                const deployments = ref([]);
                const stockItems = ref([]);
                const fleet = ref([]);
                const showDeployModal = ref(false);
                const searchQuery = ref('');
                
                const newDeployment = ref({
                    item_id: '',
                    item_name: '',
                    qty_deployed: 1,
                    deployed_to_fleet_id: '',
                    deployed_to_name: '',
                    deployed_by: '',
                    reason: '',
                    photo: null
                });

                const selectedItemStock = computed(() => {
                    const item = stockItems.value.find(i => i.id == newDeployment.value.item_id);
                    return item ? item.stock : 0;
                });

                const deploymentsThisMonth = computed(() => {
                    const now = new Date();
                    return deployments.value.filter(d => {
                        const depDate = new Date(d.deployment_date);
                        return depDate.getMonth() === now.getMonth() && depDate.getFullYear() === now.getFullYear();
                    }).length;
                });

                const uniqueItemsDeployed = computed(() => {
                    return new Set(deployments.value.map(d => d.item_id)).size;
                });

                const uniqueFleetServed = computed(() => {
                    return new Set(deployments.value.filter(d => d.deployed_to_fleet_id).map(d => d.deployed_to_fleet_id)).size;
                });

                const filteredDeployments = computed(() => {
                    if(!searchQuery.value) return deployments.value;
                    const q = searchQuery.value.toLowerCase();
                    return deployments.value.filter(d => 
                        d.item_name.toLowerCase().includes(q) || 
                        d.deployed_to_name.toLowerCase().includes(q) ||
                        d.deployed_by.toLowerCase().includes(q)
                    );
                });

                const fetchDeployments = async () => {
                    // Mock for now - in production would fetch from API
                    deployments.value = [
                        {id: 1, item_id: 101, item_name: 'Filter Oli Hino', qty_deployed: 2, deployed_to_fleet_id: 1, deployed_to_name: 'SR-01 Jetbus', deployed_by: 'Teknisi Budi', reason: 'Service Rutin 10.000 KM', deployment_date: '2025-01-02', status: 'Deployed'}
                    ];
                };

                const fetchStockItems = async () => {
                    try {
                        const res = await fetch('api.php?action=get_items');
                        const data = await res.json();
                        if(data.status === 'success') stockItems.value = data.data;
                    } catch(e) { console.error(e); }
                };

                const fetchFleet = async () => {
                    try {
                        const res = await fetch('api.php?action=get_fleet');
                        const data = await res.json();
                        if(data.status === 'success') fleet.value = data.data;
                    } catch(e) { console.error(e); }
                };

                const openDeployModal = () => {
                    newDeployment.value = { item_id: '', item_name: '', qty_deployed: 1, deployed_to_fleet_id: '', deployed_to_name: '', deployed_by: '', reason: '', photo: null };
                    showDeployModal.value = true;
                };

                const closeDeployModal = () => {
                    showDeployModal.value = false;
                };

                const loadItemDetails = () => {
                    const item = stockItems.value.find(i => i.id == newDeployment.value.item_id);
                    if(item) newDeployment.value.item_name = item.name;
                };

                const updateFleetName = () => {
                    const bus = fleet.value.find(f => f.id == newDeployment.value.deployed_to_fleet_id);
                    if(bus) {
                        newDeployment.value.deployed_to_name = bus.name + ' - ' + bus.plate;
                    } else {
                        newDeployment.value.deployed_to_name = 'Umum / Non-Armada';
                    }
                };

                const handlePhotoUpload = (event) => {
                    newDeployment.value.photo = event.target.files[0];
                };

                const saveDeployment = async () => {
                    if(!newDeployment.value.item_id || !newDeployment.value.deployed_by || !newDeployment.value.reason) {
                        Swal.fire('Error', 'Mohon lengkapi semua field wajib', 'error');
                        return;
                    }
                    
                    if(newDeployment.value.qty_deployed > selectedItemStock.value) {
                        Swal.fire('Error', 'Jumlah melebihi stok tersedia', 'error');
                        return;
                    }

                    Swal.fire({title: 'Menyimpan...', allowOutsideClick: false, didOpen: () => Swal.showLoading()});
                    
                    // Mock save - in production would POST to API
                    deployments.value.unshift({
                        id: Date.now(),
                        ...newDeployment.value,
                        deployment_date: new Date().toISOString(),
                        status: 'Deployed'
                    });
                    
                    // Decrease stock
                    const item = stockItems.value.find(i => i.id == newDeployment.value.item_id);
                    if(item) item.stock -= newDeployment.value.qty_deployed;
                    
                    setTimeout(() => {
                        Swal.fire('Berhasil!', 'Barang berhasil di-deploy', 'success');
                        closeDeployModal();
                    }, 500);
                };

                const formatDate = (dateStr) => {
                    const d = new Date(dateStr);
                    return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
                };

                const viewDeploymentDetail = (dep) => {
                    Swal.fire({
                        title: 'Detail Deployment',
                        html: `
                            <div class="text-left space-y-2 text-sm">
                                <p><strong>Barang:</strong> ${dep.item_name}</p>
                                <p><strong>Qty:</strong> ${dep.qty_deployed} pcs</p>
                                <p><strong>Armada:</strong> ${dep.deployed_to_name}</p>
                                <p><strong>Oleh:</strong> ${dep.deployed_by}</p>
                                <p><strong>Alasan:</strong> ${dep.reason}</p>
                                <p><strong>Tanggal:</strong> ${formatDate(dep.deployment_date)}</p>
                            </div>
                        `,
                        icon: 'info'
                    });
                };

                onMounted(() => {
                    fetchDeployments();
                    fetchStockItems();
                    fetchFleet();
                });

                return {
                    deployments, filteredDeployments, stockItems, fleet, searchQuery,
                    showDeployModal, newDeployment, selectedItemStock,
                    deploymentsThisMonth, uniqueItemsDeployed, uniqueFleetServed,
                    openDeployModal, closeDeployModal, loadItemDetails, updateFleetName,
                    handlePhotoUpload, saveDeployment, formatDate, viewDeploymentDetail
                };
            }
        }).mount('#app');
    </script>
</body>
</html>
