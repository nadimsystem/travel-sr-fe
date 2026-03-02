<!DOCTYPE html>
<html lang="id" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Orders - Sutan Raya</title>
    <link rel="icon" type="image/webp" href="../image/logo.webp">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        
        body { font-family: 'Plus Jakarta Sans', sans-serif; transition: background-color 0.3s; }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #475569; }
        [v-cloak] { display: none; }
        input[type="date"]::-webkit-calendar-picker-indicator { cursor: pointer; }
    </style>
    <script>
        tailwind.config = { darkMode: 'class', theme: { extend: { colors: { 'sr-blue': '#0f172a' } } } }
    </script>
</head>
<body class="h-screen w-screen bg-slate-50 text-slate-800 dark:bg-slate-900 dark:text-slate-100">

    <div id="app" class="flex h-full w-full" v-cloak>
        
        <?php $currentPage = 'purchasing_po'; include 'components/sidebar.php'; ?>

        <main class="flex-1 flex flex-col min-w-0 bg-slate-50 dark:bg-slate-900 overflow-hidden relative">
            
            <!-- Header -->
            <header class="bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 p-6 flex flex-col md:flex-row md:items-center justify-between gap-4 z-10 shrink-0">
                <div class="flex items-center gap-4">
                    <button onclick="toggleSidebar()" class="lg:hidden text-slate-500 hover:text-blue-600">
                        <i class="bi bi-list text-2xl"></i>
                    </button>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Purchase Orders</h1>
                        <p class="text-xs text-slate-500 font-medium">Kelola procurement & pemesanan barang</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <button @click="openCreateModal" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl text-sm font-bold flex items-center gap-2 transition-all shadow-lg shadow-blue-200 dark:shadow-none hover:translate-y-[-2px]">
                        <i class="bi bi-plus-lg"></i> <span class="hidden sm:inline">Buat PO Baru</span><span class="sm:hidden">PO Baru</span>
                    </button>
                </div>
            </header>

            <!-- Main Content -->
            <div class="flex-1 overflow-hidden flex flex-col p-6">
                
                <!-- Filters / Tabs -->
                <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">
                    <div class="flex bg-white dark:bg-slate-800 p-1 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm w-full md:w-auto overflow-x-auto">
                        <button v-for="tab in tabs" :key="tab.id" @click="activeTab = tab.id"
                            class="px-4 py-2 rounded-lg text-sm font-bold transition-all whitespace-nowrap"
                            :class="activeTab === tab.id ? 'bg-slate-100 dark:bg-slate-700 text-slate-900 dark:text-white shadow-sm' : 'text-slate-500 hover:text-slate-700 dark:hover:text-slate-300'">
                            {{ tab.label }}
                            <span v-if="tab.count > 0" class="ml-1 px-1.5 py-0.5 rounded-full text-[10px] bg-slate-200 dark:bg-slate-600 text-slate-600 dark:text-slate-300">{{ tab.count }}</span>
                        </button>
                    </div>

                    <div class="relative w-full md:w-64">
                         <input type="text" v-model="searchQuery" placeholder="Cari No PO / Supplier..." class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all font-medium">
                         <i class="bi bi-search absolute left-3.5 top-3 text-slate-400"></i>
                    </div>
                </div>

                <!-- List View (Table) -->
                <div class="flex-1 bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden flex flex-col">
                    <div class="flex-1 overflow-auto custom-scrollbar">
                        <table class="w-full text-left text-sm whitespace-nowrap">
                            <thead class="bg-slate-50 dark:bg-slate-700/50 sticky top-0 z-10 text-[10px] uppercase font-extrabold text-slate-500 tracking-wider">
                                <tr>
                                    <th class="px-6 py-4">PO Number</th>
                                    <th class="px-6 py-4">Supplier</th>
                                    <th class="px-6 py-4">Tanggal</th>
                                    <th class="px-6 py-4 text-center">Items</th>
                                    <th class="px-6 py-4 text-right">Total Amount</th>
                                    <th class="px-6 py-4 text-center">Status</th>
                                    <th class="px-6 py-4 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                                <tr v-for="po in filteredPOs" :key="po.id" @click="openDetail(po)" class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors cursor-pointer group">
                                    <td class="px-6 py-4 font-mono font-bold text-blue-600 group-hover:text-blue-700">{{ po.poNumber }}</td>
                                    <td class="px-6 py-4 font-bold text-slate-700 dark:text-slate-200">{{ po.supplier }}</td>
                                    <td class="px-6 py-4 text-slate-500">{{ formatDate(po.created) }}</td>
                                    <td class="px-6 py-4 text-center"><span class="bg-slate-100 dark:bg-slate-700 px-2 py-1 rounded text-xs font-bold text-slate-600 dark:text-slate-300">{{ po.items }}</span></td>
                                    <td class="px-6 py-4 text-right font-mono font-bold text-slate-700 dark:text-slate-200">{{ formatRupiah(po.total) }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide border" 
                                            :class="getStatusClass(po.status)">
                                            {{ po.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <button @click.stop="deletePO(po)" class="text-slate-400 hover:text-red-600 transition-colors p-2 rounded-full hover:bg-red-50 dark:hover:bg-red-900/30">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="filteredPOs.length === 0">
                                    <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                                        <div class="flex flex-col items-center gap-2">
                                            <i class="bi bi-clipboard-x text-4xl mb-2 opacity-50"></i>
                                            <p class="text-sm font-medium">Tidak ada data PO ditemukan.</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Modal Create PO (Preserved Logic) -->
            <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm animate-fade-in">
                <div class="bg-white dark:bg-slate-800 w-full max-w-4xl rounded-3xl shadow-2xl flex flex-col max-h-[90vh]">
                    <div class="p-6 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-slate-50/50 dark:bg-slate-700/20 rounded-t-3xl">
                        <div>
                        <h2 class="text-lg font-bold text-slate-800 dark:text-white">Purchase Order</h2>
                        <p class="text-xs text-slate-500">Purchase Order (PO) adalah surat pesanan resmi ke Supplier. Di sini kita membuat dokumen untuk memesan barang ke vendor sebelum barang dikirim.</p>
                    </div>
                        <button @click="showCreateModal = false" class="text-slate-400 hover:text-red-500"><i class="bi bi-x-lg text-lg"></i></button>
                    </div>
                    
                    <div class="flex-1 overflow-y-auto p-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Left: Helper & Supplier -->
                        <div class="md:col-span-1 space-y-5">
                            <div>
                                <label class="text-xs font-bold text-slate-500 block mb-1.5 uppercase tracking-wide">Supplier</label>
                                <select v-model="newPO.supplier_id" class="w-full p-3 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-sm font-bold focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900/40 outline-none transition">
                                    <option value="">Pilih Supplier...</option>
                                    <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.name }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-500 block mb-1.5 uppercase tracking-wide">Tanggal PO</label>
                                <input type="date" v-model="newPO.date" class="w-full p-3 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-sm font-bold outline-none">
                            </div>
                            
                            <!-- Pending Request Picker -->
                            <div class="p-4 rounded-2xl bg-orange-50 dark:bg-orange-900/10 border border-orange-100 dark:border-orange-900/30">
                                <div class="flex justify-between items-center mb-3">
                                    <h4 class="text-xs font-bold text-orange-700 dark:text-orange-400 flex items-center gap-2">
                                        <i class="bi bi-stopwatch"></i> Pending Requests
                                    </h4>
                                    <span class="text-[10px] font-bold bg-orange-200 text-orange-800 px-1.5 py-0.5 rounded-md">{{ pendingRequests.length }}</span>
                                </div>
                                <div class="space-y-2 max-h-48 overflow-y-auto custom-scrollbar pr-1">
                                    <div v-for="req in pendingRequests" :key="req.id" @click="addRequestToPO(req)" 
                                        class="group flex flex-col p-2.5 bg-white dark:bg-slate-800 rounded-xl border border-orange-100 dark:border-orange-900/30 cursor-pointer hover:border-blue-400 hover:shadow-sm transition-all relative overflow-hidden">
                                        
                                        <div class="flex justify-between items-start">
                                            <span class="font-bold text-xs text-slate-700 dark:text-slate-200">{{ req.item_name }}</span>
                                            <span class="text-[10px] font-bold bg-slate-100 dark:bg-slate-700 px-1.5 py-0.5 rounded text-slate-500">{{ req.qty }} {{ req.unit }}</span>
                                        </div>
                                        <div class="flex justify-between items-center mt-1 text-[10px] text-slate-400">
                                            <span>Req: {{ req.requester }}</span>
                                            <span class="text-blue-500 font-bold opacity-0 group-hover:opacity-100 transition-opacity flex items-center gap-1">Add <i class="bi bi-plus-circle-fill"></i></span>
                                        </div>
                                    </div>
                                    <div v-if="pendingRequests.length === 0" class="text-center text-xs text-slate-400 py-2 italic">Tidak ada pending request.</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Right: Items Table -->
                        <div class="md:col-span-2 flex flex-col h-full bg-slate-50 dark:bg-slate-900/50 rounded-2xl border border-slate-200 dark:border-slate-700 p-4">
                            <div class="flex justify-between items-center mb-4">
                                <h4 class="text-sm font-bold text-slate-700 dark:text-slate-300">Item Details</h4>
                                <button @click="newPO.items.push({item:'', qty:1, price:0})" class="text-xs font-bold text-blue-600 bg-blue-50 dark:bg-blue-900/30 px-3 py-1.5 rounded-lg hover:bg-blue-100 transition">+ Item Manual</button>
                            </div>
                            
                            <div class="flex-1 overflow-auto custom-scrollbar bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm">
                                <table class="w-full text-left text-xs">
                                    <thead class="bg-slate-50 dark:bg-slate-700 text-slate-500 font-bold uppercase">
                                        <tr>
                                            <th class="p-3">Barang / Deskripsi</th>
                                            <th class="p-3 w-16">Qty</th>
                                            <th class="p-3 w-32">Harga (@)</th>
                                            <th class="p-3 w-32 text-right">Subtotal</th>
                                            <th class="p-3 w-8"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                        <tr v-for="(item, idx) in newPO.items" :key="idx" class="group">
                                            <td class="p-2">
                                                <input type="text" v-model="item.item" placeholder="Nama Barang..." class="w-full p-2 rounded-lg border border-slate-200 dark:border-slate-600 text-xs font-bold">
                                            </td>
                                            <td class="p-2">
                                                <input type="number" v-model="item.qty" class="w-full p-2 rounded-lg border border-slate-200 dark:border-slate-600 text-center text-xs">
                                            </td>
                                            <td class="p-2">
                                                <input type="number" v-model="item.price" class="w-full p-2 rounded-lg border border-slate-200 dark:border-slate-600 text-right text-xs">
                                            </td>
                                            <td class="p-2 text-right font-mono font-bold">{{ formatRupiah(item.qty * item.price) }}</td>
                                            <td class="p-2 text-center">
                                                <button @click="newPO.items.splice(idx, 1)" class="text-slate-400 hover:text-red-500 transition"><i class="bi bi-trash-fill"></i></button>
                                            </td>
                                        </tr>
                                        <tr v-if="newPO.items.length === 0">
                                            <td colspan="5" class="p-10 text-center text-slate-400 flex flex-col items-center">
                                                <i class="bi bi-basket text-2xl mb-2 opacity-50"></i>
                                                <span class="italic">Belum ada item ditambahkan.</span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="mt-4 flex justify-between items-center text-slate-700 dark:text-slate-300">
                                <span class="text-xs font-bold text-slate-400">{{ newPO.items.length }} Items</span>
                                <div class="text-right">
                                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Estimasi</div>
                                    <div class="text-xl font-extrabold text-blue-600">{{ formatRupiah(calculateTotal) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="p-6 border-t border-slate-100 dark:border-slate-700 flex justify-end gap-3 bg-slate-50/50 dark:bg-slate-700/20 rounded-b-3xl">
                        <button @click="showCreateModal = false" class="px-5 py-2.5 rounded-xl font-bold text-slate-500 bg-white border border-slate-200 hover:bg-slate-50 shadow-sm transition">Batal</button>
                        <button class="px-5 py-2.5 rounded-xl font-bold text-slate-700 bg-yellow-400 hover:bg-yellow-500 shadow-lg shadow-yellow-200 dark:shadow-none transition border border-yellow-500/20">Simpan Draft</button>
                        <button @click="savePO" class="px-5 py-2.5 rounded-xl font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-lg shadow-blue-200 dark:shadow-none transition flex items-center gap-2">
                            <i class="bi bi-send-fill"></i> Kirim Order
                        </button>
                    </div>
                </div>
            </div>

        </main>
        
        <?php include 'components/sidebar_right.php'; ?>

    </div>

    <!-- Script Logic -->
    <script>
        const { createApp, ref, computed, onMounted } = Vue;
        
        createApp({
            setup() {
                const showCreateModal = ref(false);
                const activeTab = ref('all');
                const searchQuery = ref('');
                
                const suppliers = ref([]);
                const pendingRequests = ref([]);
                const allPOs = ref([]);
                
                const newPO = ref({ supplier_id: '', date: new Date().toISOString().split('T')[0], items: [] });

                const tabs = [
                    { id: 'all', label: 'Semua PO', count: 0 },
                    { id: 'Draft', label: 'Draft', count: 0 },
                    { id: 'Process', label: 'Diproses / Dikirim', count: 0 },
                    { id: 'Completed', label: 'Selesai', count: 0 }
                ];

                // FETCH DATA
                const loadData = async () => {
                   await Promise.all([fetchSuppliers(), fetchPendingItems(), fetchPOs()]);
                   updateTabCounts();
                };

                const fetchSuppliers = async () => {
                    try {
                        const res = await fetch('api.php?action=get_suppliers');
                        const data = await res.json();
                        if(data.status === 'success') suppliers.value = data.data;
                    } catch(e) { console.error(e); }
                };

                const fetchPendingItems = async () => {
                    try {
                        const res = await fetch('api.php?action=get_pending_pr_items');
                        const data = await res.json();
                        if(data.status === 'success') pendingRequests.value = data.data;
                    } catch(e) { console.error(e); }
                };

                const fetchPOs = async () => {
                    try {
                        const res = await fetch('api.php?action=get_pos');
                        const data = await res.json();
                        if(data.status === 'success') {
                            // Map API snake_case to Frontend camelCase
                            allPOs.value = data.data.map(po => ({
                                id: po.id,
                                poNumber: po.po_number,
                                supplier: po.supplier_name || 'Unknown Supplier',
                                created: po.created_at,
                                items: po.item_count || 0, // Fallback if API doesn't send it yet
                                total: po.total_amount,
                                status: po.status,
                                eta: po.eta || '-', // If DB has this
                                progress: po.progress || 0
                            }));
                        }
                    } catch(e) { console.error(e); }
                };
                
                const updateTabCounts = () => {
                     tabs[0].count = allPOs.value.length;
                     tabs[1].count = allPOs.value.filter(p => p.status === 'Draft').length;
                     tabs[2].count = allPOs.value.filter(p => ['Sent', 'Partial'].includes(p.status)).length;
                     tabs[3].count = allPOs.value.filter(p => ['Closed', 'Completed'].includes(p.status)).length;
                };

                const filteredPOs = computed(() => {
                    let result = allPOs.value;
                    
                    // Tab Filter
                    if (activeTab.value !== 'all') {
                        if(activeTab.value === 'Process') {
                            result = result.filter(p => p.status === 'Sent' || p.status === 'Partial');
                        } else if(activeTab.value === 'Completed') {
                            result = result.filter(p => p.status === 'Closed' || p.status === 'Completed');
                        } else {
                            result = result.filter(p => p.status === activeTab.value);
                        }
                    }
                    
                    // Search
                    if(searchQuery.value) {
                        const q = searchQuery.value.toLowerCase();
                        result = result.filter(p => 
                            p.poNumber.toLowerCase().includes(q) || 
                            p.supplier.toLowerCase().includes(q)
                        );
                    }
                    
                    return result;
                });

                const savePO = async () => {
                    if(!newPO.value.supplier_id || newPO.value.items.length === 0) {
                        Swal.fire('Error', 'Pilih Supplier dan tambahkan item!', 'error');
                        return;
                    }
                    
                    try {
                        const payload = {
                            supplier_id: newPO.value.supplier_id,
                            items: newPO.value.items
                        };

                        const res = await fetch('api.php?action=create_po', {
                            method: 'POST',
                            body: JSON.stringify(payload)
                        });
                        const data = await res.json();

                        if(data.status === 'success') {
                            Swal.fire('Sukses', 'PO Berhasil dibuat: ' + data.po_number, 'success');
                            showCreateModal.value = false;
                            loadData(); // Refresh
                        } else {
                            Swal.fire('Gagal', data.message, 'error');
                        }
                    } catch(e) {
                         Swal.fire('Error', 'Gagal koneksi server', 'error');
                    }
                };
                
                const openCreateModal = () => { 
                    newPO.value = { supplier_id: '', date: new Date().toISOString().split('T')[0], items: [] }; 
                    showCreateModal.value = true; 
                    fetchPendingItems(); 
                };
                const openDetail = (po) => { Swal.fire('PO Detail', `Review detail for ${po.poNumber}`, 'info'); };
                
                const addRequestToPO = (req) => {
                    const existing = newPO.value.items.find(i => i.item === req.item_name);
                    if(existing) {
                        existing.qty += parseInt(req.qty);
                    } else {
                        newPO.value.items.push({ item: req.item_name, qty: parseInt(req.qty), price: 0 });
                    }
                    // Remove from pending UI
                    const idx = pendingRequests.value.findIndex(r => r.id === req.id);
                    if(idx > -1) pendingRequests.value.splice(idx, 1);
                };

                const calculateTotal = computed(() => {
                    return newPO.value.items.reduce((sum, item) => sum + (item.qty * item.price), 0);
                });

                const formatDate = (dateStr) => {
                    if(!dateStr) return '-';
                    return new Date(dateStr).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
                };

                const formatRupiah = (val) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(val);
                
                const getStatusClass = (status) => {
                    if(status === 'Draft') return 'bg-slate-100 text-slate-600 border-slate-200';
                    if(status === 'Sent' || status === 'Partial') return 'bg-blue-50 text-blue-600 border-blue-200';
                    if(status === 'Completed' || status === 'Closed') return 'bg-green-50 text-green-600 border-green-200';
                    return 'bg-slate-100 text-slate-600';
                };

                onMounted(() => {
                    loadData();
                });

                const deletePO = (po) => {
                    Swal.fire({
                        title: 'Hapus PO?',
                        text: `PO ${po.poNumber} akan dihapus permanently.`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Hapus',
                        confirmButtonColor: '#ef4444'
                    }).then(async (result) => {
                        if (result.isConfirmed) {
                            try {
                                const res = await fetch(`api.php?action=delete_purchase_order&id=${po.id}`);
                                const data = await res.json();
                                if(data.status === 'success') {
                                    Swal.fire('Terhapus!', 'PO berhasil dihapus', 'success');
                                    loadData();
                                } else {
                                    Swal.fire('Gagal', data.message, 'error');
                                }
                            } catch(e) { console.error(e); }
                        }
                    });
                };

                return {
                    formatRupiah, formatDate,
                    tabs, activeTab, searchQuery, filteredPOs,
                    showCreateModal, pendingRequests, newPO, suppliers,
                    openCreateModal, openDetail, addRequestToPO, calculateTotal, savePO, getStatusClass, deletePO
                };
            }
        }).mount('#app');
    </script>
</body>
</html>
