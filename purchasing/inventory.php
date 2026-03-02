<!DOCTYPE html>
<html lang="id" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory & Assets - Purchasing Sutan Raya</title>
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

    <div id="app" class="flex h-full w-full" v-cloak>
        
        <?php $currentPage = 'purchasing_inventory'; include 'components/sidebar.php'; ?>

        <main class="flex-1 flex flex-col min-w-0 bg-slate-50 dark:bg-slate-900 overflow-hidden relative">
            <header class="h-16 bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between px-4 md:px-6 shadow-sm z-10 shrink-0">
                <div class="flex items-center gap-3">
                    <button onclick="toggleSidebar()" class="lg:hidden text-slate-600 dark:text-slate-300 hover:text-blue-600 p-2 -ml-2">
                        <i class="bi bi-list text-2xl"></i>
                    </button>
                    <div>
                        <h2 class="text-base md:text-lg font-bold text-slate-800 dark:text-white">Aset & Inventaris</h2>
                        <p class="text-xs text-slate-500">Gudang data kita. Lihat sisa stok barang habis pakai (Oli, Filter) dan daftar Aset kantor (Laptop, Mesin) di sini.</p>
                    </div>
                </div>
                <!-- View Toggle Buttons -->
                <div class="flex bg-slate-100 dark:bg-slate-700 rounded-lg p-1">
                    <button @click="currentView = 'stock'" :class="currentView === 'stock' ? 'bg-white dark:bg-slate-600 shadow text-blue-600 dark:text-blue-300' : 'text-slate-500 dark:text-slate-400'" class="px-4 py-1.5 rounded-md text-sm font-bold transition-all"><i class="bi bi-box-seam mr-2"></i>Stok</button>
                    <button @click="currentView = 'assets'" :class="currentView === 'assets' ? 'bg-white dark:bg-slate-600 shadow text-blue-600 dark:text-blue-300' : 'text-slate-500 dark:text-slate-400'" class="px-4 py-1.5 rounded-md text-sm font-bold transition-all"><i class="bi bi-buildings mr-2"></i>Aset Tetap</button>
                </div>
            </header>

            <div class="flex-1 overflow-hidden p-4 md:p-6 custom-scrollbar overflow-y-auto">
                
                <!-- VIEW 1: STOCK MANAGEMENT -->
                <div v-show="currentView === 'stock'" class="animate-fade-in space-y-6">
                     <!-- Filters -->
                     <div class="flex flex-wrap gap-4 items-center justify-between">
                        <div class="relative flex-1 max-w-md">
                            <input type="text" v-model="stockSearch" placeholder="Cari nama barang, kode..." class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-sm focus:ring-2 focus:ring-blue-500/20 outline-none text-sm font-medium">
                            <i class="bi bi-search absolute left-3.5 top-3.5 text-slate-400"></i>
                        </div>
                        <button @click="openAddItemModal" class="px-5 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-bold shadow-lg shadow-blue-200 dark:shadow-none transition-all flex items-center gap-2">
                             <i class="bi bi-plus-lg"></i> Item Baru
                        </button>
                     </div>

                     <!-- Stock Grid -->
                     <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                        <div v-for="item in filteredStock" :key="item.id" class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 hover:shadow-md transition-all group">
                            <div class="flex justify-between items-start mb-3">
                                <span class="px-2 py-1 rounded bg-slate-100 dark:bg-slate-700 text-[10px] font-bold text-slate-500 tracking-wide uppercase">{{ item.category }}</span>
                                <span class="text-[10px] font-mono text-slate-400">{{ item.code }}</span>
                            </div>
                            <h3 class="font-bold text-slate-800 dark:text-white line-clamp-2 mb-2 group-hover:text-blue-600 transition-colors">{{ item.name }}</h3>
                            <div class="flex items-end justify-between mt-4 mb-3">
                                <div>
                                    <div class="text-[10px] text-slate-400 mb-0.5">Stok Tersedia</div>
                                    <div class="text-xl font-extrabold" :class="item.stock <= item.min_stock ? 'text-red-500' : 'text-slate-800 dark:text-white'">
                                        {{ item.stock }} <span class="text-sm font-normal text-slate-400 ml-1">{{ item.unit }}</span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-[10px] text-slate-400 mb-0.5">Lokasi</div>
                                    <div class="text-xs font-bold text-slate-600 dark:text-slate-300 bg-slate-50 dark:bg-slate-700 px-2 py-1 rounded">{{ item.location }}</div>
                                </div>
                            </div>
                            <!-- Quick Actions -->
                            <div class="flex gap-2">
                                <button v-if="item.location === 'Belum ditempatkan'" @click="openMoveModal(item)" class="px-3 py-1.5 bg-orange-50 text-orange-600 rounded-lg text-xs font-bold hover:bg-orange-100 transition flex items-center justify-center gap-1" title="Pindahkan ke Rak">
                                    <i class="bi bi-arrow-right-circle-fill"></i> Pindahkan
                                </button>
                                <button @click="adjustStock(item, -1)" class="flex-1 py-1.5 bg-red-50 text-red-600 rounded-lg text-xs font-bold hover:bg-red-100 transition flex items-center justify-center gap-1">
                                    <i class="bi bi-dash-lg"></i>
                                </button>
                                <button @click="adjustStock(item, 1)" class="flex-1 py-1.5 bg-green-50 text-green-600 rounded-lg text-xs font-bold hover:bg-green-100 transition flex items-center justify-center gap-1">
                                    <i class="bi bi-plus-lg"></i>
                                </button>
                                <button @click="openStockCard(item)" class="w-8 h-8 flex items-center justify-center bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition">
                                    <i class="bi bi-card-list"></i>
                                </button>
                                <button @click="deleteItem(item)" class="w-8 h-8 flex items-center justify-center bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            </div>
                        </div>
                     </div>
                </div>

                <!-- VIEW 2: ASSET MANAGEMENT -->
                <div v-show="currentView === 'assets'" class="animate-fade-in space-y-8">
                    
                    <!-- Asset Stats -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                         <div class="bg-gradient-to-br from-indigo-500 to-purple-600 p-6 rounded-3xl text-white shadow-xl shadow-indigo-200 dark:shadow-none relative overflow-hidden">
                             <div class="relative z-10">
                                 <div class="text-xs font-bold opacity-80 uppercase tracking-widest mb-1">Total Aset Tetap</div>
                                 <div class="text-3xl font-extrabold">{{ assets.length }} <span class="text-base font-normal opacity-70">Unit</span></div>
                             </div>
                             <i class="bi bi-buildings absolute -bottom-4 -right-4 text-9xl opacity-10 rotate-12"></i>
                         </div>
                         <div class="bg-white dark:bg-slate-800 p-6 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-sm relative overflow-hidden group">
                             <div class="flex justify-between items-start mb-4">
                                 <div>
                                     <div class="text-xs font-bold text-slate-400 uppercase tracking-widest">Total Nilai Aset</div>
                                     <div class="text-2xl font-extrabold text-slate-800 dark:text-white mt-1">{{ totalAssetValue }}</div>
                                 </div>
                                 <div class="w-10 h-10 rounded-full bg-green-100 text-green-600 flex items-center justify-center"><i class="bi bi-cash-stack"></i></div>
                             </div>
                             <div class="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden">
                                 <div class="bg-green-500 h-full rounded-full" style="width: 75%"></div>
                             </div>
                             <div class="flex justify-between mt-2 text-[10px] text-slate-400">
                                 <span>Depresiasi: 25%</span>
                                 <span>Valuasi 2024</span>
                             </div>
                         </div>
                         <div class="bg-white dark:bg-slate-800 p-6 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-sm flex items-center justify-between">
                              <button @click="openAddAssetModal" class="w-full h-full border-2 border-dashed border-slate-300 dark:border-slate-600 rounded-xl flex flex-col items-center justify-center text-slate-400 hover:text-blue-600 hover:border-blue-400 hover:bg-blue-50 dark:hover:bg-slate-700 transition gap-2">
                                  <i class="bi bi-plus-circle-fill text-3xl"></i>
                                  <span class="font-bold text-sm">Tambah Aset Baru</span>
                              </button>
                         </div>
                    </div>

                    <!-- Asset Grid -->
                    <div>
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="font-bold text-xl text-slate-800 dark:text-white">Daftar Aset</h3>
                            <div class="flex gap-2">
                                <select v-model="assetFilter" class="px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-bold">
                                    <option value="All">Semua Kategori</option>
                                    <option value="Kendaraan">Kendaraan</option>
                                    <option value="Elektronik">Elektronik</option>
                                    <option value="Properti">Properti</option>
                                    <option value="Mesin">Mesin & Tools</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                            <div v-for="asset in filteredAssets" :key="asset.id" class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-5 hover:shadow-lg transition-all group relative">
                                <!-- Status Badge -->
                                <div class="absolute top-5 right-5">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide border shadow-sm"
                                    :class="getAssetStatusColor(asset.status)">
                                        {{ asset.status }}
                                    </span>
                                </div>

                                <div class="flex items-start gap-4 mb-4">
                                    <div class="w-12 h-12 rounded-xl flex items-center justify-center text-xl text-white shadow-lg" :class="getAssetIconBg(asset.category)">
                                        <i class="bi" :class="getAssetIcon(asset.category)"></i>
                                    </div>
                                    <div>
                                        <div class="text-[10px] font-bold text-slate-400">{{ asset.code }}</div>
                                        <h4 class="font-bold text-slate-800 dark:text-white text-lg">{{ asset.name }}</h4>
                                    </div>
                                </div>

                                <div class="space-y-3 mb-5">
                                    <div class="flex justify-between text-sm py-1 border-b border-slate-50 dark:border-slate-700/50">
                                        <span class="text-slate-500">Nilai Perolehan</span>
                                        <span class="font-bold font-mono">{{ formatCurrency(asset.value) }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm py-1 border-b border-slate-50 dark:border-slate-700/50">
                                        <span class="text-slate-500">Lokasi</span>
                                        <span class="font-bold">{{ asset.location }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm py-1">
                                        <span class="text-slate-500">Penanggung Jawab</span>
                                        <span class="font-bold text-blue-600">{{ asset.pic }}</span>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2">
                                    <button @click="showAssetHistory(asset)" class="flex-1 py-2 bg-slate-50 dark:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-lg text-xs font-bold hover:bg-slate-100 transition">History</button>
                                    <button @click="editAsset(asset)" class="w-8 h-8 flex items-center justify-center bg-blue-50 dark:bg-blue-900/30 text-blue-600 rounded-lg hover:bg-blue-100 transition"><i class="bi bi-pencil-fill text-xs"></i></button>
                                    <button @click="deleteAsset(asset)" class="w-8 h-8 flex items-center justify-center bg-red-50 dark:bg-red-900/30 text-red-600 rounded-lg hover:bg-red-100 transition"><i class="bi bi-trash-fill text-xs"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stock Card Modal -->
            <div v-if="showStockCard" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm animate-fade-in">
                 <div class="bg-white dark:bg-slate-800 w-full max-w-2xl rounded-3xl shadow-2xl flex flex-col max-h-[85vh]">
                    <div class="p-6 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-slate-50 dark:bg-slate-900 rounded-t-3xl">
                        <div>
                            <h3 class="font-bold text-lg text-slate-800 dark:text-white">Kartu Stok: {{ activeItem.name }}</h3>
                            <p class="text-xs text-slate-500 font-mono">{{ activeItem.code }} - {{ activeItem.location }}</p>
                        </div>
                        <button @click="showStockCard = false" class="w-8 h-8 rounded-full bg-white dark:bg-slate-700 flex items-center justify-center shadow-sm text-slate-500 hover:text-red-500"><i class="bi bi-x-lg"></i></button>
                    </div>
                    <div class="flex-1 overflow-y-auto custom-scrollbar p-6">
                         <div class="text-center text-slate-500">
                             <i class="bi bi-clock-history text-4xl mb-3 block"></i>
                             <p class="font-bold">Riwayat Transaksi Stock</p>
                             <p class="text-sm mt-2">Fitur ini akan menampilkan history masuk/keluar barang</p>
                         </div>
                    </div>
                 </div>
            </div>

            <!-- Move Item Modal -->
            <div v-if="showMoveModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm animate-fade-in">
                <div class="bg-white dark:bg-slate-800 w-full max-w-md rounded-3xl shadow-2xl p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold text-slate-800 dark:text-white">Pindahkan Barang</h3>
                        <button @click="showMoveModal = false" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-500 hover:text-red-500"><i class="bi bi-x-lg"></i></button>
                    </div>
                    
                    <div class="mb-4 p-3 bg-slate-50 dark:bg-slate-700/50 rounded-xl flex items-center gap-3 border border-slate-100 dark:border-slate-700">
                        <i class="bi bi-box-seam text-2xl text-blue-500"></i>
                        <div>
                            <div class="font-bold text-slate-800 dark:text-white">{{ moveTargetItem?.name }}</div>
                            <div class="text-xs text-slate-500">{{ moveTargetItem?.code }}</div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="text-xs font-bold text-slate-500 block mb-2">Pilih Lokasi Tujuan (Cari nama rak)</label>
                        <select v-model="targetRack" class="w-full p-3 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-sm font-bold focus:ring-2 focus:ring-blue-500/20 outline-none">
                            <option :value="null" disabled>Pilih Rak...</option>
                            <option v-for="rack in allRacks" :key="rack.id" :value="rack">
                                {{ rack.full_path }}
                            </option>
                        </select>
                    </div>

                    <button @click="saveMove" :disabled="!targetRack" class="w-full py-3 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition shadow-lg disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="bi bi-check-lg mr-2"></i> Konfirmasi Pindah
                    </button>
                </div>
            </div>

            <!-- Add Asset Modal -->
            <div v-if="showAddAssetModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm animate-fade-in">
                <div class="bg-white dark:bg-slate-800 w-full max-w-2xl rounded-3xl shadow-2xl p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-bold text-slate-800 dark:text-white">Tambah Aset Baru</h3>
                        <button @click="closeAddAssetModal" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-500 hover:text-red-500"><i class="bi bi-x-lg"></i></button>
                    </div>
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-bold text-slate-500 block mb-1">Kode Aset</label>
                                <input type="text" v-model="newAsset.code" class="w-full p-3 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-sm font-bold focus:ring-2 focus:ring-blue-500/20 outline-none" placeholder="AST-XXX-001">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-500 block mb-1">Kategori</label>
                                <select v-model="newAsset.category" class="w-full p-3 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-sm font-bold focus:outline-none">
                                    <option value="Kendaraan">Kendaraan</option>
                                    <option value="Elektronik">Elektronik</option>
                                    <option value="Properti">Properti</option>
                                    <option value="Mesin">Mesin & Tools</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="text-xs font-bold text-slate-500 block mb-1">Nama Aset</label>
                            <input type="text" v-model="newAsset.name" class="w-full p-3 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-sm font-bold focus:ring-2 focus:ring-blue-500/20 outline-none" placeholder="Nama lengkap aset">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-bold text-slate-500 block mb-1">Nilai Perolehan (Rp)</label>
                                <input type="number" v-model="newAsset.value" class="w-full p-3 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-sm font-bold focus:ring-2 focus:ring-blue-500/20 outline-none" placeholder="0">
                            </div>
                            <!-- Removed the old 'Lokasi' input here -->
                        </div>
                        <!-- Location Cascading Dropdown -->
                        <div>
                            <label class="text-xs font-bold text-slate-500 block mb-1">Lokasi Penyimpanan</label>
                            <div class="grid grid-cols-3 gap-2">
                                <select v-model="selectedLocation.room_id" @change="loadCabinets" class="w-full p-3 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-sm focus:outline-none">
                                    <option value="">Ruangan</option>
                                    <option v-for="r in rooms" :key="r.id" :value="r.id">{{ r.name }}</option>
                                </select>
                                <select v-model="selectedLocation.cabinet_id" @change="loadRacks" :disabled="!selectedLocation.room_id" class="w-full p-3 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-sm focus:outline-none disabled:bg-slate-100">
                                    <option value="">Lemari</option>
                                    <option v-for="c in cabinets" :key="c.id" :value="c.id">{{ c.name }}</option>
                                </select>
                                <select v-model="newAsset.rack_id" :disabled="!selectedLocation.cabinet_id" class="w-full p-3 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-sm focus:outline-none disabled:bg-slate-100">
                                    <option value="">Rak</option>
                                    <option v-for="r in racks" :key="r.id" :value="r.id">{{ r.name }}</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="text-xs font-bold text-slate-500 block mb-1">Penanggung Jawab</label>
                            <input type="text" v-model="newAsset.pic" class="w-full p-3 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-sm font-bold focus:ring-2 focus:ring-blue-500/20 outline-none" placeholder="Nama PIC">
                        </div>
                        <button @click="saveNewAsset" class="w-full py-3 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition shadow-lg">
                            <i class="bi bi-save mr-2"></i> Simpan Aset
                        </button>
                    </div>
                </div>
            </div>

            <!-- Add Item Modal -->
            <div v-if="showAddItemModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm animate-fade-in">
                <div class="bg-white dark:bg-slate-800 w-full max-w-lg rounded-3xl shadow-2xl p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-bold text-slate-800 dark:text-white">Tambah Barang Baru</h3>
                        <button @click="showAddItemModal = false" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-500 hover:text-red-500"><i class="bi bi-x-lg"></i></button>
                    </div>
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-bold text-slate-500 block mb-1">Kode Barang</label>
                                <input type="text" v-model="newItem.code" class="w-full p-3 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-sm font-bold" placeholder="PART-001">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-500 block mb-1">Kategori</label>
                                <select v-model="newItem.category" class="w-full p-3 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-sm font-bold">
                                    <option value="Sparepart">Sparepart</option>
                                    <option value="Oli">Oli & Kimia</option>
                                    <option value="Ban">Ban</option>
                                    <option value="Tools">Tools</option>
                                    <option value="Elektrikal">Elektrikal</option>
                                    <option value="Body">Body Parts</option>
                                    <option value="Interior">Interior</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="text-xs font-bold text-slate-500 block mb-1">Nama Barang</label>
                            <input type="text" v-model="newItem.name" class="w-full p-3 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-sm font-bold" placeholder="Nama sparepart / barang">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                             <div>
                                <label class="text-xs font-bold text-slate-500 block mb-1">Stok Awal</label>
                                <input type="number" v-model="newItem.stock" class="w-full p-3 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-sm font-bold">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-500 block mb-1">Satuan</label>
                                <input type="text" v-model="newItem.unit" class="w-full p-3 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-sm font-bold" placeholder="Pcs, Ltr, Set">
                            </div>
                        </div>
                         <div class="grid grid-cols-2 gap-4">
                             <div>
                                <label class="text-xs font-bold text-slate-500 block mb-1">Min. Stock Alert</label>
                                <input type="number" v-model="newItem.min_stock" class="w-full p-3 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-sm font-bold">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-500 block mb-1">Lokasi Rak</label>
                                <input type="text" v-model="newItem.location" class="w-full p-3 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-sm font-bold" placeholder="Rak A1">
                            </div>
                        </div>
                        <button @click="saveNewItem" class="w-full py-3 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition shadow-lg mt-2">
                            <i class="bi bi-save mr-2"></i> Simpan Barang
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
                const currentView = ref('stock'); 
                const stockSearch = ref('');
                const assetFilter = ref('All');
                const showStockCard = ref(false);
                const showAddAssetModal = ref(false);
                const activeItem = ref({});
                
                const assets = ref([]);
                const stockItems = ref([]);
                
                const newAsset = ref({
                    code: '',
                    name: '',
                    category: 'Kendaraan',
                    value: 0,
                    location: '',
                    pic: '',
                    status: 'Active'
                });

                const showAddItemModal = ref(false);
                const rooms = ref([]);
                const cabinets = ref([]);
                const racks = ref([]);
                
                const selectedLocation = ref({ room_id: '', cabinet_id: '' });

                const newItem = ref({
                    code: '', name: '', category: 'Part Mesin',
                    stock: 0, unit: 'Pcs', min_stock: 5,
                    location: '', rack_id: '', // Added rack_id
                    compatibility: 'Universal', last_price: 0
                });

                const fetchRooms = async () => {
                    try {
                        const res = await fetch('api.php?action=get_rooms');
                        const data = await res.json();
                        if(data.status === 'success') rooms.value = data.data;
                    } catch(e) {}
                };

                const loadCabinets = async () => {
                    selectedLocation.value.cabinet_id = '';
                    newItem.value.rack_id = '';
                    cabinets.value = [];
                    racks.value = [];
                    if(!selectedLocation.value.room_id) return;
                    try {
                        const res = await fetch(`api.php?action=get_cabinets&room_id=${selectedLocation.value.room_id}`);
                        const data = await res.json();
                        if(data.status === 'success') cabinets.value = data.data;
                    } catch(e) {}
                };

                const loadRacks = async () => {
                    newItem.value.rack_id = '';
                    racks.value = [];
                    if(!selectedLocation.value.cabinet_id) return;
                    try {
                        const res = await fetch(`api.php?action=get_racks&cabinet_id=${selectedLocation.value.cabinet_id}`);
                        const data = await res.json();
                        if(data.status === 'success') racks.value = data.data;
                    } catch(e) {}
                };

                const saveNewItem = async () => {
                    if(!newItem.value.name || !newItem.value.code || !newItem.value.rack_id) {
                        Swal.fire('Error', 'Nama, Kode Barang, dan Lokasi Rak wajib diisi', 'error');
                        return;
                    }
                    // Logic to save item
                    try {
                        const res = await fetch('api.php?action=create_item', {
                            method: 'POST',
                            body: JSON.stringify(newItem.value)
                        });
                        const data = await res.json();
                        if (data.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: 'Item baru berhasil ditambahkan'
                            });
                            // Close modal and refresh
                            showAddItemModal.value = false;
                            fetchStock(); // Changed from fetchItems() to fetchStock() to match existing function
                            // Reset form
                            newItem.value = { code: '', name: '', category: 'Part Mesin', stock: 0, unit: 'Pcs', min_stock: 5, location: '', rack_id: '', compatibility: 'Universal', last_price: 0 };
                            selectedLocation.value = { room_id: '', cabinet_id: '' };
                        } else {
                            Swal.fire('Gagal', data.message, 'error');
                        }
                    } catch (error) {
                        Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
                    }
                };

                const deleteItem = (item) => {
                    Swal.fire({
                        title: 'Hapus Item?',
                        text: item.name,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Hapus'
                    }).then(async (result) => {
                        if(result.isConfirmed) {
                            try {
                                const res = await fetch(`api.php?action=delete_item&id=${item.id}`);
                                const data = await res.json();
                                if(data.status === 'success') {
                                    Swal.fire('Terhapus', 'Item dihapus', 'success');
                                    fetchStock();
                                }
                            } catch(e) { console.error(e); }
                        }
                    });
                };

                // Fetch Stock from API
                const fetchStock = async () => {
                    try {
                        const res = await fetch('api.php?action=get_items');
                        const data = await res.json();
                        if(data.status === 'success' && data.data.length > 0) {
                            stockItems.value = data.data;
                        }
                    } catch(e) { console.error('Stock fetch error', e); }
                };

                // Fetch Assets from API
                const fetchAssets = async () => {
                    try {
                        const res = await fetch('api.php?action=get_assets');
                        const data = await res.json();
                        if(data.status === 'success' && data.data.length > 0) {
                            assets.value = data.data;
                        }
                    } catch(e) { console.error('Assets fetch error', e); }
                };

                const filteredStock = computed(() => {
                    if(!stockSearch.value) return stockItems.value;
                    return stockItems.value.filter(i => i.name.toLowerCase().includes(stockSearch.value.toLowerCase()) || i.code.toLowerCase().includes(stockSearch.value.toLowerCase()));
                });

                const filteredAssets = computed(() => {
                    if(assetFilter.value === 'All') return assets.value;
                    return assets.value.filter(a => a.category === assetFilter.value);
                });

                const totalAssetValue = computed(() => {
                     const total = assets.value.reduce((sum, a) => sum + parseFloat(a.value || 0), 0);
                     return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(total);
                });

                const formatCurrency = (val) => {
                    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);
                };

                // Asset Helpers
                const getAssetIcon = (cat) => {
                    if(cat === 'Kendaraan') return 'bi-bus-front';
                    if(cat === 'Elektronik') return 'bi-laptop';
                    if(cat === 'Properti') return 'bi-building';
                    if(cat === 'Mesin') return 'bi-gear-wide-connected';
                    return 'bi-box-seam';
                };
                const getAssetIconBg = (cat) => {
                    if(cat === 'Kendaraan') return 'bg-blue-500';
                    if(cat === 'Elektronik') return 'bg-purple-500';
                    if(cat === 'Properti') return 'bg-emerald-500';
                    if(cat === 'Mesin') return 'bg-orange-500';
                    return 'bg-slate-500';
                };
                const getAssetStatusColor = (status) => {
                    if(status === 'Active') return 'bg-green-100 text-green-700 border-green-200';
                    if(status === 'Maintenance') return 'bg-yellow-100 text-yellow-700 border-yellow-200';
                    if(status === 'Broken') return 'bg-red-100 text-red-700 border-red-200';
                    return 'bg-slate-100 text-slate-700';
                };

                // Functions
                const openAddAssetModal = () => {
                    newAsset.value = { code: '', name: '', category: 'Kendaraan', value: 0, location: '', pic: '', status: 'Active' };
                    showAddAssetModal.value = true;
                };

                const closeAddAssetModal = () => {
                    showAddAssetModal.value = false;
                };

                const saveNewAsset = async () => {
                    if(!newAsset.value.name || !newAsset.value.code) {
                        Swal.fire('Error', 'Nama dan Kode Aset wajib diisi', 'error');
                        return;
                    }
                    
                    const action = newAsset.value.id ? 'update_asset' : 'create_asset';

                    try {
                        const res = await fetch(`api.php?action=${action}`, {
                            method: 'POST',
                            body: JSON.stringify(newAsset.value)
                        });
                        const data = await res.json();
                        
                        if(data.status === 'success') {
                            Swal.fire('Sukses', `Aset berhasil ${newAsset.value.id ? 'diupdate' : 'ditambahkan'}`, 'success');
                            closeAddAssetModal();
                            fetchAssets();
                        } else {
                            Swal.fire('Gagal', data.message, 'error');
                        }
                    } catch(e) {
                        Swal.fire('Error', 'Gagal koneksi server', 'error');
                    }
                };

                const openAddItemModal = () => {
                    // Reset form for new item
                    newItem.value = { code: '', name: '', category: 'Sparepart', stock: 0, unit: 'Pcs', min_stock: 5, location: 'Gudang' };
                    showAddItemModal.value = true;
                };
                
                const openStockCard = (item) => {
                    activeItem.value = item;
                    showStockCard.value = true;
                };

                const adjustStock = async (item, delta) => {
                    // Optimistic update
                    const oldStock = item.stock;
                    const newStock = parseInt(item.stock) + delta;
                    if(newStock < 0) return;

                    item.stock = newStock; // Update UI immediately

                    // Prepare full update payload as api expects it, or use a specific adjust endpoint if available.
                    // Since we only have update_item, we send all data.
                    try {
                        const payload = { ...item, stock: newStock };
                        const res = await fetch('api.php?action=update_item', {
                            method: 'POST',
                            body: JSON.stringify(payload)
                        });
                        const data = await res.json();
                        if(data.status !== 'success') {
                            item.stock = oldStock; // Revert on error
                            Swal.fire('Error', 'Gagal update stok', 'error');
                        } else {
                             Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: delta > 0 ? 'Stok ditambah' : 'Stok dikurangi',
                                showConfirmButton: false,
                                timer: 1500
                            });
                        }
                    } catch(e) {
                        item.stock = oldStock;
                        console.error(e);
                    }
                };

                const showAssetHistory = (asset) => {
                    Swal.fire({
                        title: 'History: ' + asset.name,
                        html: '<p class="text-sm text-slate-500">Riwayat maintenance dan transfer aset akan muncul di sini</p>',
                        icon: 'info'
                    });
                };

                const editAsset = (asset) => {
                    newAsset.value = { ...asset }; // Copy object
                    showAddAssetModal.value = true;
                };

                const deleteAsset = (asset) => {
                    Swal.fire({
                        title: 'Hapus Aset?',
                        text: asset.name,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Hapus',
                        cancelButtonText: 'Batal'
                    }).then(async (result) => {
                        if(result.isConfirmed) {
                            try {
                                const res = await fetch(`api.php?action=delete_asset&id=${asset.id}`);
                                const data = await res.json();
                                if(data.status === 'success') {
                                    Swal.fire('Terhapus!', 'Aset berhasil dihapus', 'success');
                                    fetchAssets();
                                } else {
                                    Swal.fire('Gagal', data.message, 'error');
                                }
                            } catch(e) {
                                Swal.fire('Error', 'Gagal koneksi server', 'error');
                            }
                        }
                    });
                };

                // COMPONENT: MOVE ITEM LOGIC
                const showMoveModal = ref(false);
                const moveTargetItem = ref(null);
                const targetRack = ref(null);
                const allRacks = ref([]);

                const fetchAllRacks = async () => {
                    try {
                        const res = await fetch('api.php?action=get_all_racks_flat');
                        const data = await res.json();
                        if(data.status === 'success') allRacks.value = data.data;
                    } catch(e) {}
                };

                const openMoveModal = (item) => {
                    moveTargetItem.value = item;
                    targetRack.value = null; // Reset selection
                    showMoveModal.value = true;
                };

                const saveMove = async () => {
                    if(!moveTargetItem.value || !targetRack.value) return;

                    try {
                        // Reuse move_items_to_rack logic
                        const res = await fetch('api.php?action=move_items_to_rack', {
                            method: 'POST',
                            body: JSON.stringify({
                                rack_id: targetRack.value.id,
                                item_ids: [moveTargetItem.value.id]
                            })
                        });
                        const data = await res.json();
                        
                        if(data.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil Dipindah',
                                text: `${moveTargetItem.value.name} dipindah ke ${targetRack.value.room_name} > ${targetRack.value.cabinet_name} > ${targetRack.value.name}`,
                                timer: 2000,
                                showConfirmButton: false
                            });
                            showMoveModal.value = false;
                            fetchStock(); // Refresh list to show updated location
                        } else {
                            throw new Error(data.message);
                        }
                    } catch(e) {
                        Swal.fire('Error', 'Gagal memindahkan barang: ' + e.message, 'error');
                    }
                };

                onMounted(() => {
                    fetchStock();
                    fetchAssets();
                    fetchRooms();
                    fetchAllRacks();

                    // Handle URL Query Params
                    const urlParams = new URLSearchParams(window.location.search);
                    const search = urlParams.get('search');
                    if(search) {
                        stockSearch.value = search;
                    }
                    
                    const view = urlParams.get('view');
                    if(view && ['stock', 'assets'].includes(view)) {
                        currentView.value = view;
                    }
                });

                return {
                    currentView, stockSearch, assetFilter,
                    stockItems, filteredStock, assets, filteredAssets, totalAssetValue,
                    getAssetIcon, getAssetIconBg, getAssetStatusColor, formatCurrency,
                    showStockCard, activeItem, openStockCard, adjustStock,
                    showAddAssetModal, newAsset, openAddAssetModal, closeAddAssetModal, saveNewAsset,
                    openAddItemModal, showAssetHistory, editAsset, deleteAsset,
                    showAddItemModal, newItem, saveNewItem, deleteItem,
                    rooms, cabinets, racks, selectedLocation, loadCabinets, loadRacks,
                    showMoveModal, moveTargetItem, targetRack, allRacks, openMoveModal, saveMove
                };
            }
        }).mount('#app');
    </script>
</body>
</html>
