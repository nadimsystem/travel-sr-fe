<!DOCTYPE html>
<html lang="id" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Sparepart - Sutan Raya</title>
    <link rel="icon" type="image/webp" href="../image/logo.webp">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        
        body { font-family: 'Plus Jakarta Sans', sans-serif; transition: background-color 0.3s; }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #475569; }
        [v-cloak] { display: none; }
        .animate-fade-in { animation: fadeIn 0.3s ease-out forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
    <script>
        tailwind.config = { darkMode: 'class', theme: { extend: { colors: { 'sr-blue': '#0f172a' } } } }
    </script>
</head>
<body class="h-screen w-screen bg-slate-50 text-slate-800 dark:bg-slate-900 dark:text-slate-100">

    <div class="flex h-full w-full">
        
        <?php $currentPage = 'purchasing_request'; include 'components/sidebar.php'; ?>

        <main id="app" class="flex-1 flex flex-col min-w-0 bg-slate-50 dark:bg-slate-900 overflow-hidden relative" v-cloak>
            <header class="h-16 bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between px-4 md:px-6 shadow-sm z-10 shrink-0">
                <div class="flex items-center gap-3">
                    <button onclick="toggleSidebar()" class="lg:hidden text-slate-600 dark:text-slate-300 hover:text-blue-600 p-2 -ml-2">
                        <i class="bi bi-list text-2xl"></i>
                    </button>
                    <div>
                        <h2 class="text-base md:text-lg font-bold text-slate-800 dark:text-white">Form Permintaan Barang</h2>
                        <p class="text-xs text-slate-500">Halaman ini tempat Anda meminta barang yang dibutuhkan. Jika barang stok habis atau perlu barang baru, buat permintaan di sini agar Manajemen bisa membelikan.</p>
                    </div>
                </div>
                
                <!-- Cart Status (Top Right) -->
                <div class="flex items-center gap-3">
                    <button @click="showCartModal = true" class="relative group flex items-center gap-2 px-3 py-1.5 rounded-full hover:bg-slate-100 dark:hover:bg-slate-700 transition">
                         <span class="text-xs font-bold text-slate-600 dark:text-slate-300 hidden sm:inline">Keranjang</span>
                         <div class="relative">
                            <i class="bi bi-cart3 text-xl text-slate-600 dark:text-slate-300 group-hover:text-blue-600 transition-colors"></i>
                            <span v-if="cart.length > 0" class="absolute -top-1 -right-2 w-5 h-5 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center border-2 border-white dark:border-slate-800 shadow-md transform scale-100 transition-transform">
                                {{ cart.length }}
                            </span>
                         </div>
                    </button>
                </div>
            </header>

            <div class="flex-1 overflow-hidden flex relative">
                <!-- Product/Item Configurator -->
                <div class="flex-1 overflow-y-auto custom-scrollbar p-4 md:p-8">
                    
                    <!-- Search & Filter Area -->
                    <div class="max-w-7xl mx-auto mb-10 text-center">
                        <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 dark:text-white mb-6 tracking-tight">Apa yang Anda butuhkan hari ini?</h1>
                        
                        <div class="relative max-w-2xl mx-auto group z-20">
                            <input type="text" v-model="searchQuery" placeholder="Cari nama barang, kode part, atau tipe..." class="w-full pl-14 pr-6 py-4 rounded-full border-2 border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-lg shadow-slate-200/50 dark:shadow-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:focus:ring-blue-900/30 transition-all font-semibold text-lg">
                            <i class="bi bi-search absolute left-6 top-5 text-xl text-slate-400 group-focus-within:text-blue-500 transition-colors"></i>
                        </div>
                        
                        <div class="flex flex-wrap justify-center gap-2 mt-6">
                            <button v-for="cat in categories" :key="cat" @click="activeCategory = cat" 
                                class="px-5 py-2 rounded-full text-sm font-bold transition-all border"
                                :class="activeCategory === cat ? 'bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-900 border-slate-900 dark:border-slate-100 shadow-lg transform scale-105' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:bg-slate-50'">
                                {{ cat }}
                            </button>
                        </div>
                    </div>

                    <!-- Quick Bundles Section -->
                    <div class="max-w-7xl mx-auto mb-10">
                        <div class="flex items-center gap-2 mb-4">
                            <i class="bi bi-lightning-charge-fill text-yellow-500 text-xl"></i>
                            <h2 class="text-lg font-bold text-slate-800 dark:text-white">Paket Cepat (Bundling)</h2>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div v-for="bundle in bundles" :key="bundle.name" @click="addBundleToCart(bundle)" class="bg-gradient-to-br from-blue-600 to-blue-800 text-white p-5 rounded-2xl cursor-pointer hover:shadow-xl hover:-translate-y-1 transition-all relative overflow-hidden group">
                                <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                                    <i class="bi" :class="bundle.icon" style="font-size: 5rem;"></i>
                                </div>
                                <div class="relative z-10">
                                    <div class="w-12 h-12 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center text-2xl mb-3">
                                        <i class="bi" :class="bundle.icon"></i>
                                    </div>
                                    <h3 class="font-bold text-lg leading-tight mb-1">{{ bundle.name }}</h3>
                                    <p class="text-xs text-blue-100 mb-3">{{ bundle.desc }}</p>
                                    <span class="text-[10px] font-bold bg-white/20 px-2 py-1 rounded-lg">+{{ bundle.items.length }} Items</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Item Grid (Full Width) -->
                    <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6 pb-24">
                        <!-- Add Custom Item Card -->
                        <div @click="openCustomItemModal" class="border-2 border-dashed border-blue-300 dark:border-blue-700 bg-blue-50 dark:bg-blue-900/20 rounded-3xl p-6 flex flex-col items-center justify-center text-center cursor-pointer hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-all min-h-[240px] group">
                            <div class="w-16 h-16 rounded-full bg-blue-200 dark:bg-blue-800 text-blue-600 dark:text-blue-300 flex items-center justify-center text-3xl mb-4 group-hover:scale-110 transition-transform shadow-sm">
                                <i class="bi bi-plus-lg"></i>
                            </div>
                            <h3 class="font-bold text-blue-800 dark:text-blue-200">Item Tidak Ditemukan?</h3>
                            <p class="text-xs text-blue-600 dark:text-blue-400 mt-2 font-medium">Klik untuk request manual</p>
                        </div>

                        <!-- Product Cards -->
                        <div v-for="item in filteredItems" :key="item.id" class="bg-white dark:bg-slate-800 rounded-3xl p-5 border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all group flex flex-col relative overflow-hidden h-full">
                            <!-- Stock Indicator -->
                            <div class="absolute top-4 right-4 z-10 flex flex-col items-end gap-1">
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full" :class="item.stock > 10 ? 'bg-green-100 text-green-700' : (item.stock > 0 ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700')">
                                    {{ item.stock }} {{ item.unit }}
                                </span>
                                <span class="text-[9px] font-mono font-bold px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-500">{{ item.location }}</span>
                            </div>

                            <div class="w-14 h-14 rounded-2xl bg-slate-50 dark:bg-slate-700/50 flex items-center justify-center text-2xl mb-4 text-slate-500 group-hover:bg-blue-50 dark:group-hover:bg-slate-700 group-hover:text-blue-600 transition-colors">
                                <i class="bi" :class="getIcon(item.category)"></i>
                            </div>
                            
                            <h3 class="font-bold text-slate-800 dark:text-white line-clamp-2 leading-tight mb-1 text-sm md:text-base">{{ item.name }}</h3>
                            <p class="text-[10px] text-slate-400 font-mono mb-2">{{ item.code }}</p>

                            <!-- Compatibility Badge -->
                            <div class="mb-3">
                                <span class="inline-flex items-center gap-1 text-[10px] bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 px-2 py-0.5 rounded border border-blue-100 dark:border-blue-800">
                                    <i class="bi bi-truck-front"></i> {{ item.compatibility }}
                                </span>
                            </div>
                            
                            <div class="mt-auto pt-4 border-t border-slate-100 dark:border-slate-700 flex items-center justify-between gap-3">
                                <div class="flex flex-col">
                                    <span class="text-[10px] text-slate-400">Harga Terakhir</span>
                                    <div class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ item.lastPrice }}</div>
                                </div>
                                <button @click="addToCart(item)" class="bg-slate-900 dark:bg-white text-white dark:text-slate-900 w-8 h-8 rounded-full flex items-center justify-center hover:scale-110 active:scale-95 transition-all shadow-lg hover:bg-blue-600 dark:hover:bg-blue-400 hover:text-white">
                                    <i class="bi bi-plus-lg font-bold"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Floating Checkout Button (Mobile Only) -->
                <button v-if="cart.length > 0" @click="showCartModal = true" class="md:hidden fixed bottom-6 right-6 z-30 bg-blue-600 text-white rounded-full w-14 h-14 shadow-2xl flex items-center justify-center animate-bounce">
                    <i class="bi bi-cart-fill text-xl"></i>
                    <span class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 rounded-full text-[10px] font-bold flex items-center justify-center border-2 border-slate-900">{{ cart.length }}</span>
                </button>
            </div>

            <!-- Cart Modal (Replaces Sidebar) -->
            <div v-if="showCartModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm animate-fade-in">
                <div class="bg-white dark:bg-slate-800 w-full max-w-2xl rounded-3xl shadow-2xl flex flex-col max-h-[90vh] relative">
                    <button @click="showCartModal = false" class="absolute top-4 right-4 z-10 w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-500 hover:bg-red-100 hover:text-red-500 flex items-center justify-center transition"><i class="bi bi-x-lg"></i></button>
                    
                    <div class="p-6 border-b border-slate-100 dark:border-slate-700">
                        <h2 class="font-bold text-xl flex items-center gap-2">
                            <i class="bi bi-basket-fill text-blue-600"></i> Keranjang Permintaan
                            <span class="bg-blue-100 text-blue-700 text-xs px-2 py-0.5 rounded-full">{{ cart.length }} Item</span>
                        </h2>
                    </div>

                    <div class="flex-1 overflow-y-auto custom-scrollbar p-6 space-y-4">
                        <div v-if="cart.length === 0" class="text-center py-10 opacity-50">
                            <i class="bi bi-cart-x text-6xl text-slate-300 mb-4 block"></i>
                            <p class="font-bold">Keranjang Kosong</p>
                        </div>
                        
                        <div v-for="(item, idx) in cart" :key="idx" class="flex gap-4 p-4 rounded-2xl bg-slate-50 dark:bg-slate-700/30 border border-slate-100 dark:border-slate-700">
                            <div class="w-12 h-12 rounded-xl bg-white dark:bg-slate-700 flex items-center justify-center text-xl text-slate-400 flex-shrink-0">
                                <i class="bi bi-box-seam"></i>
                            </div>
                            <div class="flex-1">
                                <div class="flex justify-between items-start mb-2">
                                    <h4 class="font-bold text-slate-800 dark:text-white line-clamp-1">{{ item.name }}</h4>
                                    <button @click="removeFromCart(idx)" class="text-slate-300 hover:text-red-500 transition"><i class="bi bi-trash-fill"></i></button>
                                </div>
                                <div class="flex flex-wrap items-center gap-4">
                                    <div class="flex items-center bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-lg overflow-hidden h-8">
                                        <button @click="item.qty > 1 ? item.qty-- : null" class="px-3 bg-slate-50 dark:bg-slate-700 hover:bg-slate-200 transition text-sm font-bold">-</button>
                                        <input type="number" v-model="item.qty" class="w-12 text-center text-sm font-bold bg-transparent focus:outline-none">
                                        <button @click="item.qty++" class="px-3 bg-slate-50 dark:bg-slate-700 hover:bg-slate-200 transition text-sm font-bold">+</button>
                                    </div>
                                    <span class="text-xs font-bold text-slate-500">{{ item.unit }}</span>
                                </div>
                                
                                <!-- Extra Fields for Request -->
                                 <div class="grid grid-cols-2 gap-3 pt-2 mt-2 border-t border-slate-200 dark:border-slate-600/50">
                                    <div>
                                        <label class="text-[10px] font-bold text-slate-400 block mb-1">Kode Armada</label>
                                        <input type="text" v-model="item.busId" placeholder="cth: SR-01" class="w-full px-2 py-1 text-xs rounded border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 focus:outline-none focus:border-blue-500">
                                    </div>
                                    <div>
                                        <label class="text-[10px] font-bold text-slate-400 block mb-1">Urgensi</label>
                                        <select v-model="item.urgency" class="w-full text-xs font-bold px-2 py-1 rounded border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 outline-none"
                                            :class="item.urgency === 'Critical' ? 'text-red-600 bg-red-50' : (item.urgency === 'Urgent' ? 'text-orange-600 bg-orange-50' : 'text-slate-600')">
                                            <option value="Normal">Normal</option>
                                            <option value="Urgent">Urgent (Segera)</option>
                                            <option value="Critical">Critical (Unit Mogok)</option>
                                        </select>
                                    </div>
                                 </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 bg-slate-50 dark:bg-slate-900 border-t border-slate-200 dark:border-slate-700 space-y-4">
                        <div>
                            <label class="text-xs font-bold text-slate-500 mb-1.5 block uppercase tracking-wide">Catatan Permintaan (Tujuan Penggunaan)</label>
                            <input type="text" v-model="requestNote" class="w-full p-3 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-sm font-medium focus:ring-2 focus:ring-blue-500/20 outline-none transition" placeholder="Contoh: Service Besar Hiace 01...">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <button @click="showCartModal = false" class="py-3.5 rounded-xl font-bold text-slate-500 border border-slate-200 hover:bg-white hover:shadow-sm transition">Tambah Lagi</button>
                            <button @click="submitRequest" :disabled="cart.length === 0" class="py-3.5 bg-blue-600 text-white font-bold rounded-xl shadow-lg shadow-blue-200 dark:shadow-none hover:bg-blue-700 hover:-translate-y-0.5 transition-all disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none">
                                Kirim Permintaan <i class="bi bi-send-fill ml-2"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal for Custom Item (Preserved Logic) -->
            <div v-if="showCustomItemModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 animate-fade-in backdrop-blur-sm">
                <div class="bg-white dark:bg-slate-800 w-full max-w-md rounded-3xl p-6 shadow-2xl relative">
                    <button @click="showCustomItemModal = false" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600"><i class="bi bi-x-lg"></i></button>
                    <h3 class="text-lg font-bold mb-4 text-slate-800 dark:text-white">Request Item Baru</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="text-xs font-bold text-slate-500 block mb-1">Nama Barang <span class="text-red-500">*</span></label>
                            <input type="text" v-model="customItem.name" class="w-full p-3 rounded-xl border border-slate-200 dark:border-slate-600 text-sm font-bold bg-slate-50 dark:bg-slate-700 focus:ring-2 focus:ring-blue-500/20 outline-none">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-bold text-slate-500 block mb-1">Estimasi Harga (Opsional)</label>
                                <input type="number" v-model="customItem.price" class="w-full p-3 rounded-xl border border-slate-200 dark:border-slate-600 text-sm bg-slate-50 dark:bg-slate-700 focus:outline-none">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-500 block mb-1">Satuan</label>
                                <select v-model="customItem.unit" class="w-full p-3 rounded-xl border border-slate-200 dark:border-slate-600 text-sm font-bold bg-slate-50 dark:bg-slate-700 focus:outline-none">
                                    <option>Pcs</option>
                                    <option>Set</option>
                                    <option>Liter</option>
                                    <option>Box</option>
                                    <option>Roll</option>
                                    <option>Lembar</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- New Fields -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-bold text-slate-500 block mb-1">Kode Armada</label>
                                <input type="text" v-model="customItem.busId" placeholder="cth: B-01" class="w-full p-3 rounded-xl border border-slate-200 dark:border-slate-600 text-sm bg-slate-50 dark:bg-slate-700 focus:outline-none">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-500 block mb-1">Level Urgensi</label>
                                <select v-model="customItem.urgency" class="w-full p-3 rounded-xl border border-slate-200 dark:border-slate-600 text-sm font-bold bg-slate-50 dark:bg-slate-700 focus:outline-none">
                                    <option value="Normal">Normal</option>
                                    <option value="Urgent">Urgent</option>
                                    <option value="Critical">Critical</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="text-xs font-bold text-slate-500 block mb-1">Foto Referensi (Opsional)</label>
                            <label class="flex flex-col items-center justify-center w-full h-24 border-2 border-dashed border-slate-300 dark:border-slate-600 rounded-xl cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-700/50 transition">
                                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                    <i class="bi bi-camera text-2xl text-slate-400 mb-1"></i>
                                    <p class="text-xs text-slate-500" v-if="!customItem.photo">Klik upload foto</p>
                                    <p class="text-xs text-blue-600 font-bold" v-else>{{ customItem.photo.name }}</p>
                                </div>
                                <input type="file" class="hidden" @change="handleFileUpload" accept="image/*">
                            </label>
                        </div>

                        <div>
                            <label class="text-xs font-bold text-slate-500 block mb-1">Keterangan / Link</label>
                            <textarea v-model="customItem.note" class="w-full p-3 rounded-xl border border-slate-200 dark:border-slate-600 text-sm h-20 bg-slate-50 dark:bg-slate-700 focus:outline-none" placeholder="Merk spesifik, link toko online, dll..."></textarea>
                        </div>
                        <button @click="addCustomItemToCart" class="w-full py-3 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition shadow-lg shadow-blue-200 dark:shadow-none">Tambahkan ke Keranjang</button>
                    </div>
                </div>
            </div>

        </main>
        
        <?php include 'components/sidebar_right.php'; ?>
        
    </div>

    <!-- Page Logic -->
    <script>
        // Check if Vue is loaded
        if (typeof Vue === 'undefined') {
            document.body.innerHTML = '<div class="p-10 text-center text-red-600 font-bold">Error: Vue.js failed to load. Please check your internet connection.</div>';
            throw new Error("Vue not loaded");
        }

        const { createApp, ref, computed, onMounted } = Vue;

        createApp({
            setup() {
                const showCartModal = ref(false);
                const showCustomItemModal = ref(false);
                const searchQuery = ref('');
                const activeCategory = ref('All');
                const requestNote = ref('');
                const isLoading = ref(true);
                
                const categories = [
                    'All', 
                    'Sparepart', 
                    'Oli & Kimia', 
                    'Ban', 
                    'Tools', 
                    'Office',
                    'Elektrikal',
                    'Kaki-Kaki & Sasis',
                    'Body & Glass',
                    'Interior & AC',
                    'Safety & Emergency'
                ];
                
                const items = ref([]);
                const cart = ref([]);
                const customItem = ref({ name: '', price: '', unit: 'Pcs', note: '', qty: 1, urgency: 'Normal', busId: '', photo: null });

                // Utility: Format Rupiah
                const formatRupiah = (val) => {
                     if (!val && val !== 0) return '-';
                     try {
                        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);
                     } catch(e) { return val; }
                };

                // Fetch Items
                const fetchItems = async () => {
                    isLoading.value = true;
                    try {
                        const response = await fetch('api.php?action=get_items');
                        if(!response.ok) throw new Error(`HTTP Error: ${response.status}`);
                        
                        const text = await response.text();
                        let result;
                        try {
                             result = JSON.parse(text);
                        } catch(e) {
                             console.error('API Invalid JSON:', text);
                             throw new Error('Server returned invalid JSON.');
                        }

                        if(result.status === 'success' && Array.isArray(result.data)) {
                            items.value = result.data.map(i => ({
                                id: i.id,
                                code: i.code || 'N/A',
                                name: i.name,
                                category: i.category || 'General',
                                stock: i.stock || 0,
                                unit: i.unit || 'Pcs',
                                lastPrice: i.last_price ? formatRupiah(i.last_price) : '-',
                                // New Attributes
                                compatibility: i.compatibility || 'Universal',
                                location: i.location || '-',
                                condition: i.condition || 'Baru'
                            }));
                        } else {
                            console.warn('API Success but no data:', result);
                            items.value = [];
                        }
                    } catch (error) {
                        console.error('Error fetching items:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Koneksi Gagal',
                            text: 'Gagal mengambil data barang: ' + error.message,
                            toast: true, position: 'top', timer: 5000
                        });
                        // Fallback data for demo if API fails WITH NEW ATTRIBUTES
                        items.value = [
                            // SAFETY & EMERGENCY
                            { id: 101, code: 'SAF-001', name: 'APAR 3kg Dry Chemical Powder', category: 'Safety & Emergency', stock: 12, unit: 'Tabung', lastPrice: 'Rp 350.000', compatibility: 'Universal', location: 'Rak S-01', condition: 'Baru' },
                            { id: 102, code: 'SAF-002', name: 'Palu Pemecah Kaca (Emergency Hammer)', category: 'Safety & Emergency', stock: 50, unit: 'Pcs', lastPrice: 'Rp 45.000', compatibility: 'Universal', location: 'Rak S-02', condition: 'Baru' },
                            { id: 103, code: 'SAF-003', name: 'Kotak P3K Lengkap (Standard Dishub)', category: 'Safety & Emergency', stock: 15, unit: 'Box', lastPrice: 'Rp 125.000', compatibility: 'Universal', location: 'Rak S-03', condition: 'Baru' },
                            
                            // INTERIOR & AC
                            { id: 201, code: 'INT-001', name: 'Pengharum Ruangan Otomatis (Spray)', category: 'Interior & AC', stock: 24, unit: 'Pcs', lastPrice: 'Rp 85.000', compatibility: 'Universal', location: 'Rak I-01', condition: 'Baru' },
                            { id: 202, code: 'INT-002', name: 'Bantal Leher Memory Foam Premium', category: 'Interior & AC', stock: 100, unit: 'Pcs', lastPrice: 'Rp 65.000', compatibility: 'Passenger Seat', location: 'Gudang I-05', condition: 'Baru' },
                            { id: 203, code: 'INT-003', name: 'Selimut Fleece Tebal (Branding SR)', category: 'Interior & AC', stock: 200, unit: 'Pcs', lastPrice: 'Rp 45.000', compatibility: 'Passenger Seat', location: 'Gudang I-06', condition: 'Baru' },
                            { id: 204, code: 'AC-001', name: 'Filter Kabin AC Denso (Hiace)', category: 'Interior & AC', stock: 10, unit: 'Pcs', lastPrice: 'Rp 150.000', compatibility: 'Toyota Hiace Commuter/Premio', location: 'Rak A-12', condition: 'Baru' },
                            { id: 205, code: 'INT-004', name: 'Hand Sanitizer Dispenser Automatic', category: 'Interior & AC', stock: 5, unit: 'Unit', lastPrice: 'Rp 250.000', compatibility: 'Universal', location: 'Rak I-02', condition: 'Baru' },

                            // ELEKTRIKAL & ENTERTAINMENT
                            { id: 301, code: 'EL-010', name: 'Android TV 32 Inch (Unit Penumpang)', category: 'Elektrikal', stock: 3, unit: 'Unit', lastPrice: 'Rp 2.800.000', compatibility: 'Hiace Luxury / Bus', location: 'Gudang E-01', condition: 'Baru' },
                            { id: 302, code: 'EL-011', name: 'Mic Wireless Set (Karaoke System)', category: 'Elektrikal', stock: 4, unit: 'Set', lastPrice: 'Rp 1.500.000', compatibility: 'Bus Audio System', location: 'Rak E-04', condition: 'Baru' },
                            { id: 303, code: 'EL-012', name: 'USB Charger Port Dual Output 2.1A', category: 'Elektrikal', stock: 40, unit: 'Pcs', lastPrice: 'Rp 75.000', compatibility: 'Seat Armrest', location: 'Rak E-02', condition: 'Baru' },
                            
                            // KAKI-KAKI & SASIS
                            { id: 401, code: 'SUS-001', name: 'Air Suspension Bellow (Balon Suspensi)', category: 'Kaki-Kaki & Sasis', stock: 4, unit: 'Pcs', lastPrice: 'Rp 2.500.000', compatibility: 'Hino R260 / Merc O500R', location: 'Gudang K-01', condition: 'Baru' },
                            { id: 402, code: 'BRK-001', name: 'Kampas Rem Depan (Brake Pad)', category: 'Kaki-Kaki & Sasis', stock: 8, unit: 'Set', lastPrice: 'Rp 850.000', compatibility: 'Hino R260', location: 'Rak K-04', condition: 'Baru' },

                            // BODY & GLASS
                            { id: 501, code: 'BDY-001', name: 'Wiper Blade 24 Inch (Frameless)', category: 'Body & Glass', stock: 20, unit: 'Pcs', lastPrice: 'Rp 85.000', compatibility: 'Universal', location: 'Rak B-01', condition: 'Baru' },
                            { id: 502, code: 'BDY-002', name: 'Spion Tanduk Elektrik (Kiri)', category: 'Body & Glass', stock: 1, unit: 'Pcs', lastPrice: 'Rp 3.500.000', compatibility: 'Jetbus 3+', location: 'Gudang B-02', condition: 'Baru' },

                            // OLI & KIMIA
                            { id: 601, code: 'CHE-001', name: 'AdBlue (Cairan Exhaust Diesel) 10L', category: 'Oli & Kimia', stock: 30, unit: 'Jerigen', lastPrice: 'Rp 150.000', compatibility: 'Euro 4 Engines', location: 'Gudang O-01', condition: 'Baru' },
                            { id: 602, code: 'OIL-002', name: 'Oli Mesin Diesel SAE 15W-40', category: 'Oli & Kimia', stock: 24, unit: 'Galon', lastPrice: 'Rp 350.000', compatibility: 'Diesel Utility', location: 'Gudang O-02', condition: 'Baru' },
                            
                            // SPAREPART
                            { id: 701, code: 'SP-001', name: 'Filter Oli (Oil Filter)', category: 'Sparepart', stock: 15, unit: 'Pcs', lastPrice: 'Rp 120.000', compatibility: 'Toyota Hiace', location: 'Rak S-11', condition: 'Baru' },
                            { id: 702, code: 'SP-002', name: 'Fan Belt Set', category: 'Sparepart', stock: 5, unit: 'Set', lastPrice: 'Rp 450.000', compatibility: 'Hino RK8', location: 'Rak S-12', condition: 'Baru' },
                             // OFFICE
                             { id: 801, code: 'OFF-001', name: 'Kertas HVS A4 80gsm', category: 'Office', stock: 50, unit: 'Rim', lastPrice: 'Rp 45.000', compatibility: 'Kantor', location: 'Gudang ATK', condition: 'Baru' }
                        ];
                    } finally {
                        isLoading.value = false;
                    }
                };

                const filteredItems = computed(() => {
                    let result = items.value;
                    if (activeCategory.value !== 'All') {
                        result = result.filter(i => {
                            // Exact match first
                            if(i.category === activeCategory.value) return true;
                            // Partial match for grouped categories logic (simplified)
                            if (activeCategory.value === 'Oli & Kimia' && (i.category === 'Oli' || i.category.includes('Kimia'))) return true;
                            return false;
                        });
                    }
                    if (searchQuery.value) {
                        const q = searchQuery.value.toLowerCase();
                        result = result.filter(i => i.name.toLowerCase().includes(q) || i.code.toLowerCase().includes(q));
                    }
                    return result;
                });

                const getIcon = (cat) => {
                    const map = {
                        'Sparepart': 'bi-gear-wide-connected',
                        'Oli & Kimia': 'bi-droplet-half', 'Oli': 'bi-droplet-half',
                        'Ban': 'bi-vinyl',
                        'Tools': 'bi-tools',
                        'Office': 'bi-printer',
                        'Elektrikal': 'bi-lightning-charge-fill',
                        'Kaki-Kaki & Sasis': 'bi-truck-flatbed',
                        'Body & Glass': 'bi-car-front-fill',
                        'Interior & AC': 'bi-snow',
                        'Safety & Emergency': 'bi-shield-check'
                    };
                    return map[cat] || 'bi-box-seam';
                };

                const addToCart = (item) => {
                    const existing = cart.value.find(c => c.id === item.id);
                    if (existing) {
                        existing.qty++;
                    } else {
                        cart.value.push({ ...item, qty: 1, urgency: 'Normal', busId: '' });
                    }
                    const Toast = Swal.mixin({
                        toast: true, position: 'top-end', showConfirmButton: false, timer: 1500, timerProgressBar: true
                    });
                    Toast.fire({ icon: 'success', title: 'Ditambahkan ke keranjang' });
                };

                const removeFromCart = (idx) => {
                    cart.value.splice(idx, 1);
                };

                const openCustomItemModal = () => {
                    customItem.value = { name: '', price: '', unit: 'Pcs', note: '', qty: 1, urgency: 'Normal', busId: '', photo: null };
                    showCustomItemModal.value = true;
                };

                const bundles = [
                    {
                        name: 'Paket Service Rutin Hiace',
                        icon: 'bi-wrench-adjustable-circle',
                        desc: 'Oli, Filter Oli, Filter Udara, Air Wiper',
                        items: [
                            { name: 'Oli Mesin Diesel SAE 15W-40', qty: 2, unit: 'Galon', category: 'Oli & Kimia' },
                            { name: 'Filter Oli (Oil Filter)', qty: 1, unit: 'Pcs', category: 'Sparepart' },
                            { name: 'Filter Udara', qty: 1, unit: 'Pcs', category: 'Sparepart' },
                            { name: 'Air Wiper Concentrated', qty: 1, unit: 'Botol', category: 'Oli & Kimia' }
                        ]
                    },
                    {
                        name: 'Paket Amenities Luxury Bus',
                        icon: 'bi-stars',
                        desc: 'Selimut, Bantal, Pengharum, Tisu',
                        items: [
                            { name: 'Selimut Fleece Tebal', qty: 30, unit: 'Pcs', category: 'Interior & AC' },
                            { name: 'Bantal Leher Memory Foam', qty: 30, unit: 'Pcs', category: 'Interior & AC' },
                            { name: 'Pengharum Ruangan Otomatis', qty: 2, unit: 'Pcs', category: 'Interior & AC' },
                            { name: 'Kotak Tisu Premium', qty: 10, unit: 'Box', category: 'Interior & AC' }
                        ]
                    },
                    {
                        name: 'Safety Starter Kit',
                        icon: 'bi-shield-exclamation',
                        desc: 'APAR, P3K, Palu, Segitiga',
                        items: [
                            { name: 'APAR 3kg Dry Chemical Powder', qty: 1, unit: 'Tabung', category: 'Safety & Emergency' },
                            { name: 'Kotak P3K Lengkap', qty: 1, unit: 'Box', category: 'Safety & Emergency' },
                            { name: 'Palu Pemecah Kaca', qty: 4, unit: 'Pcs', category: 'Safety & Emergency' },
                            { name: 'Segitiga Pengaman', qty: 2, unit: 'Pcs', category: 'Safety & Emergency' }
                        ]
                    }
                ];

                const addBundleToCart = (bundle) => {
                    Swal.fire({
                        title: `Tambah ${bundle.name}?`,
                        text: `Akan menambahkan ${bundle.items.length} jenis barang ke keranjang.`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Tambahkan',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            bundle.items.forEach(item => {
                                cart.value.push({
                                    id: 'BNDL-' + Date.now() + Math.random(),
                                    name: item.name,
                                    category: item.category,
                                    unit: item.unit,
                                    qty: item.qty,
                                    urgency: 'Normal',
                                    isCustom: true, // Treat as custom so it doesn't need ID matching
                                    note: 'Paket Bundling: ' + bundle.name,
                                    busId: '',
                                    usage: 'Big Bus' // Default
                                });
                            });
                            showCartModal.value = true;
                            const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 1500 });
                            Toast.fire({ icon: 'success', title: 'Paket berhasil ditambahkan!' });
                        }
                    });
                };

                const handleFileUpload = (event) => {
                    const file = event.target.files[0];
                    if(file) customItem.value.photo = file;
                };

                const addCustomItemToCart = () => {
                    if (!customItem.value.name) return;
                    cart.value.push({
                        id: 'CUST-' + Date.now(),
                        name: customItem.value.name,
                        category: 'Custom',
                        unit: customItem.value.unit,
                        qty: customItem.value.qty,
                        urgency: customItem.value.urgency,
                        isCustom: true,
                        note: customItem.value.note,
                        busId: customItem.value.busId,
                        photo: customItem.value.photo ? customItem.value.photo.name : null
                    });
                    showCustomItemModal.value = false;
                    showCartModal.value = true;
                };

                const submitRequest = () => {
                    if(cart.value.length === 0) return;
                    
                    Swal.fire({
                        title: 'Kirim Permintaan?',
                        text: `${cart.value.length} item akan diajukan ke Purchasing.`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#2563eb',
                        confirmButtonText: 'Ya, Kirim Sekarang'
                    }).then(async (result) => {
                        if (result.isConfirmed) {
                            try {
                                const payload = {
                                    notes: requestNote.value,
                                    items: cart.value.map(c => ({
                                        id: c.isCustom ? null : c.id,
                                        name: c.name,
                                        qty: c.qty,
                                        urgency: c.urgency,
                                        bus_id: c.busId || null // Send Bus ID
                                    }))
                                };

                                const response = await fetch('api.php?action=create_request', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json' },
                                    body: JSON.stringify(payload)
                                });
                                
                                // Handle non-JSON response safely
                                const text = await response.text();
                                let resData;
                                try {
                                    resData = JSON.parse(text);
                                } catch(e) { throw new Error("Server returned invalid response: " + text.substring(0, 50)); }

                                if(resData.status === 'success') {
                                    Swal.fire('Terkirim!', 'Permintaan Anda telah masuk antrian approval.', 'success');
                                    cart.value = [];
                                    requestNote.value = '';
                                    showCartModal.value = false;
                                } else {
                                    Swal.fire('Gagal!', resData.message || 'Terjadi kesalahan.', 'error');
                                }
                            } catch (err) {
                                Swal.fire('Error!', err.message, 'error');
                            }
                        }
                    });
                };

                onMounted(() => {
                    fetchItems();
                });

                return {
                    showCartModal, showCustomItemModal, searchQuery, activeCategory, categories,
                    items, cart, filteredItems, customItem, requestNote, isLoading,
                    getIcon, addToCart, removeFromCart, openCustomItemModal, addCustomItemToCart, submitRequest,
                    handleFileUpload, formatRupiah, bundles, addBundleToCart
                };
            }
        }).mount('#app');
    </script>
</body>
</html>
