<!DOCTYPE html>
<html lang="id" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penerimaan Barang (Receiving) - Purchasing Sutan Raya</title>
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
        
        <?php $currentPage = 'purchasing_receiving'; include 'components/sidebar.php'; ?>

        <main class="flex-1 flex flex-col min-w-0 bg-slate-50 dark:bg-slate-900 overflow-hidden relative">
            <header class="h-16 bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between px-6 shadow-sm z-10 shrink-0">
                <div class="flex items-center gap-3">
                    <button onclick="toggleSidebar()" class="lg:hidden text-slate-600 dark:text-slate-300 hover:text-blue-600 p-2 -ml-2">
                        <i class="bi bi-list text-2xl"></i>
                    </button>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800 dark:text-white">Penerimaan Barang (Receiving)</h2>
                        <p class="text-xs text-slate-500">Tempat mencatat barang yang baru sampai di kantor. Bisa dari pesanan PO atau beli langsung. Stok akan otomatis bertambah setelah dicatat di sini.</p>
                    </div>
                </div>
                <button @click="openReceiveModal" class="px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white rounded-xl text-sm font-bold shadow-lg flex items-center gap-2">
                    <i class="bi bi-box-arrow-in-down"></i> Terima Barang Baru
                </button>
            </header>

            <div class="flex-1 overflow-hidden p-6 custom-scrollbar overflow-y-auto">
                
                <!-- Receiving History Table -->
                <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
                    <div class="p-6 border-b border-slate-100 dark:border-slate-700">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-bold text-slate-800 dark:text-white">Riwayat Penerimaan</h3>
                            <div class="relative">
                                <input type="text" v-model="searchQuery" placeholder="Cari barang, penerima..." class="pl-10 pr-4 py-2 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-sm focus:ring-2 focus:ring-blue-500/20 outline-none">
                                <i class="bi bi-search absolute left-3 top-2.5 text-slate-400"></i>
                            </div>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-slate-50 dark:bg-slate-700/50 text-xs font-bold text-slate-500 uppercase">
                                <tr>
                                    <th class="p-4">Tanggal Terima</th>
                                    <th class="p-4">Sumber</th>
                                    <th class="p-4">Nama Barang</th>
                                    <th class="p-4">Qty Masuk</th>
                                    <th class="p-4">Penerima</th>
                                    <th class="p-4">Notes</th>
                                    <th class="p-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700 text-sm">
                                <tr v-for="rec in filteredReceiving" :key="rec.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition">
                                    <td class="p-4 text-slate-500">{{ formatDate(rec.received_date) }}</td>
                                    <td class="p-4">
                                        <span v-if="rec.po_id" class="px-2 py-1 rounded-full bg-blue-100 text-blue-700 text-[10px] font-bold">PO #{{ rec.po_id }}</span>
                                        <span v-else class="px-2 py-1 rounded-full bg-slate-100 text-slate-600 text-[10px] font-bold">Direct</span>
                                    </td>
                                    <td class="p-4 font-bold text-slate-700 dark:text-slate-200">
                                        {{ rec.item_name }} <br>
                                        <span class="text-[10px] text-slate-400 font-mono">{{ rec.item_code }}</span>
                                    </td>
                                    <td class="p-4 font-bold text-green-600">+{{ rec.qty_received }}</td>
                                    <td class="p-4 text-slate-600 dark:text-slate-300">{{ rec.received_by }}</td>
                                    <td class="p-4 text-slate-500 text-xs">{{ rec.notes || '-' }}</td>
                                    <td class="p-4 text-right">
                                        <button class="text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 p-2 rounded-lg transition">
                                            <i class="bi bi-eye-fill"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="filteredReceiving.length === 0">
                                    <td colspan="7" class="p-8 text-center text-slate-400">
                                        <i class="bi bi-inbox text-4xl mb-2 block"></i>
                                        Belum ada data penerimaan
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Receive Modal -->
            <div v-if="showReceiveModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm animate-fade-in">
                <div class="bg-white dark:bg-slate-800 w-full max-w-2xl rounded-3xl shadow-2xl p-6 max-h-[90vh] overflow-y-auto custom-scrollbar">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-bold text-slate-800 dark:text-white">Penerimaan Barang</h3>
                        <button @click="closeReceiveModal" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-500 hover:text-red-500">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>

                    <!-- Mode Selection -->
                    <div class="flex p-1 bg-slate-100 dark:bg-slate-700/50 rounded-xl mb-6">
                        <button @click="receiveMode = 'po'" :class="receiveMode === 'po' ? 'bg-white dark:bg-slate-700 shadow-sm text-blue-600 dark:text-white' : 'text-slate-500 hover:text-slate-700'" class="flex-1 py-2 text-sm font-bold rounded-lg transition">
                            Dari PO
                        </button>
                        <button @click="receiveMode = 'direct'" :class="receiveMode === 'direct' ? 'bg-white dark:bg-slate-700 shadow-sm text-blue-600 dark:text-white' : 'text-slate-500 hover:text-slate-700'" class="flex-1 py-2 text-sm font-bold rounded-lg transition">
                            Pengadaan Langsung
                        </button>
                    </div>

                    <div class="space-y-4">
                        
                        <!-- PO Mode Fields -->
                        <div v-if="receiveMode === 'po'">
                             <div>
                                <label class="text-xs font-bold text-slate-500 block mb-1">Pilih PO</label>
                                <select v-model="selectedPOId" @change="loadPOItems" class="w-full p-3 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-sm font-bold focus:outline-none">
                                    <option value="">-- Pilih Purchase Order --</option>
                                    <option v-for="po in pendingPOs" :key="po.id" :value="po.id">
                                        {{ po.po_number }} - {{ po.supplier_name }} ({{ formatDate(po.created_at) }})
                                    </option>
                                </select>
                            </div>
                            
                            <div v-if="selectedPOId && poItems.length > 0" class="mt-4">
                                <label class="text-xs font-bold text-slate-500 block mb-1">Pilih Item dari PO</label>
                                <div class="space-y-2">
                                    <div v-for="item in poItems" :key="item.id" 
                                         class="p-3 border rounded-xl flex justify-between items-center cursor-pointer transition hover:bg-slate-50"
                                         :class="newReceiving.item_code === item.item_name ? 'border-blue-500 bg-blue-50/50' : 'border-slate-200'"
                                         @click="selectPOItem(item)">
                                        <div>
                                            <div class="font-bold text-sm">{{ item.item_name }}</div>
                                            <div class="text-xs text-slate-500">Ordered: {{ item.qty }} {{ item.unit }}</div>
                                        </div>
                                        <div class="text-xs font-bold bg-slate-100 px-2 py-1 rounded">
                                            Select
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Direct Mode Fields -->
                        <div v-if="receiveMode === 'direct'">
                            <div>
                                <label class="text-xs font-bold text-slate-500 block mb-1">Pilih Barang</label>
                                <select v-model="newReceiving.item_id" class="w-full p-3 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-sm font-bold focus:outline-none">
                                    <option value="">-- Pilih Barang --</option>
                                    <option v-for="item in items" :key="item.id" :value="item.id">
                                        {{ item.name }} (Current: {{ item.stock }})
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- Common Fields -->
                        <div v-if="newReceiving.item_id || (receiveMode === 'po' && newReceiving.item_code)">
                             <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="text-xs font-bold text-slate-500 block mb-1">Jumlah Diterima</label>
                                    <input type="number" v-model="newReceiving.qty_received" min="1" class="w-full p-3 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-sm font-bold focus:ring-2 focus:ring-blue-500/20 outline-none">
                                </div>
                                <div>
                                    <label class="text-xs font-bold text-slate-500 block mb-1">Diterima Oleh</label>
                                    <input type="text" v-model="newReceiving.received_by" class="w-full p-3 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-sm font-bold focus:ring-2 focus:ring-blue-500/20 outline-none" placeholder="Nama Staff Gudang">
                                </div>
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-500 block mb-1">Catatan / Kondisi</label>
                                <textarea v-model="newReceiving.notes" class="w-full p-3 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-sm h-20 focus:ring-2 focus:ring-blue-500/20 outline-none" placeholder="Kondisi barang baik..."></textarea>
                            </div>

                            <button @click="saveReceiving" class="w-full py-3 bg-green-600 text-white font-bold rounded-xl hover:bg-green-700 transition shadow-lg mt-2">
                                <i class="bi bi-check-lg mr-2"></i> Konfirmasi Penerimaan
                            </button>
                        </div>

                    </div>
                </div>
            </div>

        </main>
        
        <?php include 'components/sidebar_right.php'; ?>

    </div>

    <script>
        const { createApp, ref, computed, onMounted, watch } = Vue;

        createApp({
            setup() {
                const receivingHistory = ref([]);
                const items = ref([]);
                const pendingPOs = ref([]);
                const poItems = ref([]);
                
                const showReceiveModal = ref(false);
                const receiveMode = ref('direct'); // 'po' or 'direct'
                const searchQuery = ref('');
                const selectedPOId = ref('');
                
                const newReceiving = ref({
                    po_id: null,
                    item_id: '',
                    item_code: '', // used for PO matching display
                    qty_received: 1,
                    received_by: '',
                    notes: ''
                });

                const filteredReceiving = computed(() => {
                    if(!searchQuery.value) return receivingHistory.value;
                    const q = searchQuery.value.toLowerCase();
                    return receivingHistory.value.filter(r => 
                        (r.item_name && r.item_name.toLowerCase().includes(q)) || 
                        (r.received_by && r.received_by.toLowerCase().includes(q))
                    );
                });

                const fetchReceiving = async () => {
                    try {
                        const res = await fetch('api.php?action=get_receiving');
                        const data = await res.json();
                        if(data.status === 'success') receivingHistory.value = data.data;
                    } catch(e) { console.error(e); }
                };

                const fetchItems = async () => {
                    try {
                        const res = await fetch('api.php?action=get_items');
                        const data = await res.json();
                        if(data.status === 'success') items.value = data.data;
                    } catch(e) { console.error(e); }
                };

                const fetchPOs = async () => {
                    try {
                        const res = await fetch('api.php?action=get_purchase_orders');
                        const data = await res.json();
                        if(data.status === 'success') {
                            // Only show Approved POs that are not fully closed (logic simplified for now)
                            pendingPOs.value = data.data.filter(po => po.status === 'Approved'); 
                        }
                    } catch(e) { console.error(e); }
                };

                const loadPOItems = async () => {
                    if(!selectedPOId.value) {
                         poItems.value = [];
                         return;
                    }
                    try {
                        const res = await fetch(`api.php?action=get_purchase_order&id=${selectedPOId.value}`);
                        const data = await res.json();
                        if(data.status === 'success') {
                            poItems.value = data.data.items;
                        }
                    } catch(e) { console.error(e); }
                };

                const selectPOItem = (poItem) => {
                    // Try to match PO item to Items DB
                    // In a real system, PO Items should already be linked to Item IDs.
                    const matchedItem = items.value.find(i => i.name === poItem.item_name);
                    
                    if(matchedItem) {
                        newReceiving.value.item_id = matchedItem.id;
                        newReceiving.value.qty_received = poItem.qty; // Default to full qty
                        newReceiving.value.po_id = selectedPOId.value;
                        newReceiving.value.item_code = poItem.item_name; // Just for display highlighting
                    } else {
                        Swal.fire('Warning', 'Item di PO tidak ditemukan di database Master Barang. Pastikan nama sama.', 'warning');
                    }
                };

                const openReceiveModal = () => {
                    newReceiving.value = { po_id: null, item_id: '', item_code: '', qty_received: 1, received_by: '', notes: '' };
                    selectedPOId.value = '';
                    poItems.value = [];
                    showReceiveModal.value = true;
                };

                const closeReceiveModal = () => {
                    showReceiveModal.value = false;
                };

                const saveReceiving = async () => {
                    if(!newReceiving.value.item_id || !newReceiving.value.received_by) {
                        Swal.fire('Error', 'Barang dan Penerima wajib diisi', 'error');
                        return;
                    }

                    Swal.fire({title: 'Menyimpan...', allowOutsideClick: false, didOpen: () => Swal.showLoading()});

                    try {
                        const payload = { ...newReceiving.value };
                        
                        const res = await fetch('api.php?action=create_receiving', {
                            method: 'POST',
                            body: JSON.stringify(payload)
                        });
                        const data = await res.json();
                        
                        if(data.status === 'success') {
                            Swal.fire('Sukses', 'Penerimaan berhasil dicatat & Stock diupdate', 'success');
                            showReceiveModal.value = false;
                            fetchReceiving();
                        } else {
                            Swal.fire('Gagal', data.message, 'error');
                        }
                    } catch(e) {
                        Swal.fire('Error', 'Gagal koneksi server', 'error');
                    }
                };
                
                const formatDate = (dateStr) => {
                    if(!dateStr) return '-';
                    const d = new Date(dateStr);
                    return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
                };

                onMounted(() => {
                    fetchReceiving();
                    fetchItems();
                    fetchPOs();
                });

                watch(receiveMode, (val) => {
                    // Reset selection when switching modes
                    newReceiving.value.item_id = '';
                    selectedPOId.value = '';
                    poItems.value = [];
                });

                return {
                    receivingHistory, filteredReceiving, items, pendingPOs, poItems,
                    showReceiveModal, receiveMode, searchQuery, selectedPOId,
                    newReceiving,
                    openReceiveModal, closeReceiveModal, saveReceiving,
                    formatDate, loadPOItems, selectPOItem
                };
            }
        }).mount('#app');
    </script>
</body>
</html>
