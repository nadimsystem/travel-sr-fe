<!DOCTYPE html>
<html lang="id" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Gudang - Purchasing Sutan Raya</title>
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
        .active-card { border-color: #3b82f6; background-color: #eff6ff; }
        .dark .active-card { border-color: #3b82f6; background-color: #1e3a8a; }
    </style>
    <script>
        tailwind.config = { darkMode: 'class' }
    </script>
</head>
<body class="h-screen w-screen bg-slate-50 dark:bg-slate-900">

    <div id="app" class="flex h-full w-full" v-cloak>
        
        <?php $currentPage = 'storage_management'; include 'components/sidebar.php'; ?>

        <main class="flex-1 flex flex-col min-w-0 bg-slate-50 dark:bg-slate-900 overflow-hidden relative">
            <header class="h-16 bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between px-6 shadow-sm z-10 shrink-0">
                <div class="flex items-center gap-3">
                    <button onclick="toggleSidebar()" class="lg:hidden text-slate-600 dark:text-slate-300 hover:text-blue-600 p-2 -ml-2">
                        <i class="bi bi-list text-2xl"></i>
                    </button>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800 dark:text-white">Manajemen Fisikal Inventaris</h2>
                        <!-- <p class="text-xs text-slate-500">Atur lokasi penyimpanan: Ruangan > Lemari > Rak</p> -->
                    </div>
                </div>
            </header>

            <div class="flex-1 overflow-hidden p-6 custom-scrollbar overflow-y-auto">
                
                <!-- STACKED LAYOUT CONTAINER -->
                <div class="space-y-8 pb-20">

                    <!-- SECTION 1: ROOMS (Master) -->
                    <div class="animate-fade-in">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-bold text-slate-800 dark:text-white flex items-center gap-2">
                                <i class="bi bi-building text-blue-600"></i> Daftar Ruangan
                            </h3>
                            <button @click="openAddModal('room')" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl font-bold text-sm transition shadow-lg shadow-blue-600/20 flex items-center gap-2">
                                <i class="bi bi-plus-lg"></i> Tambah Ruangan
                            </button>
                        </div>

                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                            <div v-for="room in rooms" :key="room.id" @click="selectRoom(room)" 
                                 :class="{'ring-4 ring-blue-500/20 border-blue-500 bg-blue-50/50 dark:bg-slate-800': selectedRoom?.id === room.id, 'bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 hover:border-blue-400': selectedRoom?.id !== room.id}"
                                 class="group p-5 rounded-2xl border transition relative flex flex-col gap-3 cursor-pointer">
                                
                                <div class="flex justify-between items-start">
                                    <div class="w-12 h-12 rounded-xl flex items-center justify-center text-2xl transition"
                                         :class="selectedRoom?.id === room.id ? 'bg-blue-100 dark:bg-blue-900/50 text-blue-600' : 'bg-slate-100 dark:bg-slate-700 text-slate-500 group-hover:bg-blue-50 group-hover:text-blue-600'">
                                        <i class="bi bi-building"></i>
                                    </div>
                                    <button @click.stop="deleteItem('room', room.id)" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-400 hover:bg-red-500 hover:text-white flex items-center justify-center transition opacity-0 group-hover:opacity-100">
                                        <i class="bi bi-trash text-xs"></i>
                                    </button>
                                </div>
                                
                                <div>
                                    <h4 class="font-bold text-slate-800 dark:text-white truncate">{{ room.name }}</h4>
                                    <p class="text-xs text-slate-500 truncate">{{ room.notes || 'Tanpa catatan' }}</p>
                                </div>

                                <!-- Stats Badge -->
                                <div class="flex gap-2 text-[10px] font-bold text-slate-500 mt-auto pt-2 border-t border-slate-100 dark:border-slate-700/50">
                                    <span class="bg-slate-100 dark:bg-slate-700 px-2 py-1 rounded-md">
                                        {{ room.cabinet_count || 0 }} Lemari
                                    </span>
                                    <span class="bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 px-2 py-1 rounded-md">
                                        {{ room.item_count || 0 }} Item
                                    </span>
                                </div>
                            </div>

                            <!-- Empty State -->
                            <div v-if="rooms.length === 0" class="col-span-full py-10 text-center text-slate-400 border-2 border-dashed border-slate-200 dark:border-slate-700 rounded-2xl">
                                <i class="bi bi-building-dash text-4xl mb-2 opacity-50 block"></i>
                                Belum ada ruangan
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 2: CABINETS (Detail) -->
                    <div ref="cabinetsSection" v-if="selectedRoom" class="animate-fade-in border-t border-slate-200 dark:border-slate-700 pt-8">
                        <div class="flex justify-between items-center mb-4">
                            <div>
                                <h3 class="text-lg font-bold text-slate-800 dark:text-white flex items-center gap-2">
                                    <i class="bi bi-archive text-indigo-600"></i> Lemari / Space di {{ selectedRoom.name }}
                                </h3>
                                <p class="text-xs text-slate-500">Pilih lemari / Space untuk melihat rak di dalamnya</p>
                            </div>
                            <button @click="openAddModal('cabinet')" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl font-bold text-sm transition shadow-lg shadow-indigo-600/20 flex items-center gap-2">
                                <i class="bi bi-plus-lg"></i> Tambah Lemari / Space
                            </button>
                        </div>

                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                             <div v-for="cab in cabinets" :key="cab.id" @click="selectCabinet(cab)" 
                                  :class="{'ring-4 ring-indigo-500/20 border-indigo-500 bg-indigo-50/50 dark:bg-slate-800': selectedCabinet?.id === cab.id, 'bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 hover:border-indigo-400': selectedCabinet?.id !== cab.id}"
                                  class="group p-5 rounded-2xl border transition relative flex flex-col gap-3 cursor-pointer">
                                
                                <div class="flex justify-between items-start">
                                    <div class="w-12 h-12 rounded-xl flex items-center justify-center text-2xl transition"
                                         :class="selectedCabinet?.id === cab.id ? 'bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600' : 'bg-slate-100 dark:bg-slate-700 text-slate-500 group-hover:bg-indigo-50 group-hover:text-indigo-600'">
                                        <i class="bi bi-archive"></i>
                                    </div>
                                    <button @click.stop="deleteItem('cabinet', cab.id)" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-400 hover:bg-red-500 hover:text-white flex items-center justify-center transition opacity-0 group-hover:opacity-100">
                                        <i class="bi bi-trash text-xs"></i>
                                    </button>
                                </div>
                                
                                <div>
                                    <h4 class="font-bold text-slate-800 dark:text-white truncate">{{ cab.name }}</h4>
                                    <p class="text-xs text-slate-500 truncate">{{ cab.notes || 'Tanpa catatan' }}</p>
                                </div>

                                <!-- Stats Badge -->
                                <div class="flex gap-2 text-[10px] font-bold text-slate-500 mt-auto pt-2 border-t border-slate-100 dark:border-slate-700/50">
                                    <span class="bg-slate-100 dark:bg-slate-700 px-2 py-1 rounded-md">
                                        {{ cab.rack_count || 0 }} Rak
                                    </span>
                                    <span class="bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 px-2 py-1 rounded-md">
                                        {{ cab.item_count || 0 }} Item
                                    </span>
                                </div>
                            </div>

                            <div v-if="cabinets.length === 0" class="col-span-full py-10 text-center text-slate-400 border-2 border-dashed border-slate-200 dark:border-slate-700 rounded-2xl">
                                <i class="bi bi-box-seam text-4xl mb-2 opacity-50 block"></i>
                                Belum ada lemari di sini
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 3: RACKS (Detail) -->
                    <div ref="racksSection" v-if="selectedCabinet" class="animate-fade-in border-t border-slate-200 dark:border-slate-700 pt-8">
                        <div class="flex justify-between items-center mb-4">
                            <div>
                                <h3 class="text-lg font-bold text-slate-800 dark:text-white flex items-center gap-2">
                                    <i class="bi bi-bookshelf text-emerald-600"></i> Rak / Space di {{ selectedCabinet.name }}
                                </h3>
                                <p class="text-xs text-slate-500">Kelola isi barang di dalam rak / Space</p>
                            </div>
                            <button @click="openAddModal('rack')" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-xl font-bold text-sm transition shadow-lg shadow-emerald-600/20 flex items-center gap-2">
                                <i class="bi bi-plus-lg"></i> Tambah Rak / Space
                            </button>
                        </div>

                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                            <div v-for="rack in racks" :key="rack.id" 
                                 class="group p-5 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 hover:border-emerald-500 hover:shadow-xl hover:shadow-emerald-500/10 transition relative flex flex-col justify-between min-h-[160px]">
                                
                                <div class="flex justify-between items-start">
                                    <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-2xl mb-3">
                                        <i class="bi bi-bookshelf"></i>
                                    </div>
                                    <button @click.stop="deleteItem('rack', rack.id)" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-400 hover:bg-red-500 hover:text-white flex items-center justify-center transition opacity-0 group-hover:opacity-100">
                                        <i class="bi bi-trash text-xs"></i>
                                    </button>
                                </div>

                                <div>
                                    <h3 class="font-bold text-slate-800 dark:text-white leading-tight mb-1">{{ rack.name }}</h3>
                                    <p class="text-xs text-slate-500 line-clamp-2">{{ rack.notes || 'Tanpa catatan' }}</p>
                                </div>

                                <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-700/50 flex flex-col gap-2">
                                    <div class="flex items-center gap-2 text-xs font-bold text-slate-500">
                                        <i class="bi bi-box-seam"></i> {{ rack.item_count || 0 }} Item tersimpan
                                    </div>
                                    <div class="flex gap-2">
                                        <button @click.stop="openManageItems(rack)" class="flex-1 py-2 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 font-bold text-xs hover:bg-emerald-600 hover:text-white transition flex items-center justify-center gap-2">
                                            Kelola Isi
                                        </button>
                                        <button @click.stop="openQuickAdd(rack)" class="w-10 py-2 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 font-bold text-xs hover:bg-blue-600 hover:text-white transition flex items-center justify-center" title="Quick Add Item">
                                            <i class="bi bi-plus-lg text-lg"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div v-if="racks.length === 0" class="col-span-full py-10 text-center text-slate-400 border-2 border-dashed border-slate-200 dark:border-slate-700 rounded-2xl">
                                <i class="bi bi-layout-three-columns text-4xl mb-2 opacity-50 block"></i>
                                Belum ada rak / space di sini
                            </div>
                        </div>
                    </div>
                </div>

            <!-- Add Modal -->
            <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm animate-fade-in">
                <div class="bg-white dark:bg-slate-800 w-full max-w-sm rounded-3xl shadow-2xl p-6">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-white mb-4">Tambah {{ modalTitle }}</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="text-xs font-bold text-slate-500 block mb-1">Nama</label>
                            <input ref="nameInput" type="text" v-model="newItem.name" @keyup.enter="$refs.notesInput.focus()" class="w-full p-3 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-sm font-bold focus:ring-2 focus:ring-blue-500/20 outline-none" placeholder="Contoh: Gudang A / Rak 1">
                        </div>
                        <div>
                            <label class="text-xs font-bold text-slate-500 block mb-1">Catatan</label>
                            <input ref="notesInput" type="text" v-model="newItem.notes" @keyup.enter="saveItem" class="w-full p-3 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-sm font-bold focus:ring-2 focus:ring-blue-500/20 outline-none" placeholder="Opsional (Enter untuk Simpan)">
                        </div>
                        <div class="flex gap-2 pt-2">
                            <button @click="showModal = false" class="flex-1 py-2 text-slate-500 font-bold hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition">Batal</button>
                            <button @click="saveItem" class="flex-1 py-2 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition shadow-lg">Simpan</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Manage Items Modal -->
            <div v-if="showManageModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm animate-fade-in">
                <div class="bg-white dark:bg-slate-800 w-full max-w-2xl rounded-3xl shadow-2xl flex flex-col max-h-[90vh]">
                    <div class="p-6 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center">
                        <div>
                            <h3 class="font-bold text-lg text-slate-800 dark:text-white">Kelola Isi Rak</h3>
                            <p class="text-xs text-slate-500">{{ managingRack?.name }}</p>
                        </div>
                        <button @click="closeManageModal" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-500 hover:text-red-500">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    
                    <div class="flex border-b border-slate-100 dark:border-slate-700 px-6">
                        <button @click="manageTab = 'current'" class="px-4 py-3 text-sm font-bold border-b-2 transition" :class="manageTab === 'current' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-700'">
                            Isi Rak / Space Saat Ini
                        </button>
                        <button @click="manageTab = 'add'" class="px-4 py-3 text-sm font-bold border-b-2 transition" :class="manageTab === 'add' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-700'">
                            Tambah Barang
                        </button>
                    </div>

                    <div class="flex-1 overflow-y-auto p-6 custom-scrollbar">
                        <!-- TAB: CURRENT ITEMS -->
                        <div v-if="manageTab === 'current'">
                            <div v-if="rackItems.length === 0" class="text-center py-10 text-slate-400">
                                <i class="bi bi-box-seam text-4xl mb-2 block opacity-50"></i>
                                Rak / Space ini masih kosong
                            </div>
                            <div v-else class="space-y-2">
                                <div v-for="item in rackItems" :key="item.id" class="flex items-center justify-between p-3 rounded-xl border border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-700/30">
                                    <div>
                                        <div class="font-bold text-slate-800 dark:text-white text-sm">{{ item.name }}</div>
                                        <div class="text-xs text-slate-500">{{ item.code }}</div>
                                    </div>
                                    <div class="font-bold text-sm text-blue-600">{{ item.stock }} {{ item.unit }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- TAB: ADD ITEMS -->
                        <div v-if="manageTab === 'add'">
                            <input ref="searchItemInput" type="text" v-model="itemSearch" @keydown.enter="selectFirstItem" placeholder="Cari barang... (Enter untuk pilih pertama)" class="w-full p-3 rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 text-sm mb-4 focus:outline-none" autofocus>
                            
                            <div class="space-y-2 max-h-[300px] overflow-y-auto pr-2 custom-scrollbar">
                                <div v-for="item in filteredAvailableItems" :key="item.id" class="flex items-center gap-3 p-3 rounded-xl border border-slate-100 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 transition cursor-pointer" @click="toggleItemSelection(item.id)">
                                    <div class="w-5 h-5 rounded border border-slate-300 dark:border-slate-500 flex items-center justify-center transition" :class="selectedItemIds.includes(item.id) ? 'bg-blue-600 border-blue-600 text-white' : 'bg-white dark:bg-slate-800'">
                                        <i class="bi bi-check text-xs" v-if="selectedItemIds.includes(item.id)"></i>
                                    </div>
                                    <div class="flex-1">
                                        <div class="font-bold text-slate-800 dark:text-white text-sm">{{ item.name }}</div>
                                        <div class="text-[10px] text-slate-500 flex gap-2">
                                            <span>{{ item.code }}</span>
                                            <span v-if="item.rack_name" class="text-orange-500">• Di: {{ item.rack_name }}</span>
                                            <span v-else class="text-slate-400">• Belum ada rak / space</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 border-t border-slate-100 dark:border-slate-700" v-if="manageTab === 'add'">
                        <button @click="moveItemsToRack" :disabled="selectedItemIds.length === 0" class="w-full py-3 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition shadow-lg disabled:opacity-50 disabled:cursor-not-allowed">
                            Pindahkan {{ selectedItemIds.length }} Barang ke Rak / Space Ini
                        </button>
                    </div>
                </div>
            </div>

            <!-- Quick Add Item Modal -->
            <div v-if="showQuickAddModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm animate-fade-in">
                <div class="bg-white dark:bg-slate-800 w-full max-w-lg rounded-3xl shadow-2xl p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-bold text-slate-800 dark:text-white">Quick Add Item</h3>
                        <button @click="showQuickAddModal = false" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-500 hover:text-red-500"><i class="bi bi-x-lg"></i></button>
                    </div>
                    
                    <div class="mb-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-xl flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-800 text-blue-600 flex items-center justify-center text-xl shrink-0">
                            <i class="bi bi-geo-alt"></i>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">LOKASI PENYIMPANAN</p>
                            <p class="text-sm font-bold text-slate-800 dark:text-white line-clamp-1">
                                {{ selectedRoom?.name }} <i class="bi bi-chevron-right text-[10px]"></i> 
                                {{ selectedCabinet?.name }} <i class="bi bi-chevron-right text-[10px]"></i> 
                                {{ quickAddItemTarget?.name }}
                            </p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-bold text-slate-500 block mb-1">Kode Barang</label>
                                <input type="text" v-model="quickItem.code" class="w-full p-3 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-sm font-bold focus:ring-2 focus:ring-blue-500/20 outline-none" placeholder="Otomatis" readonly>
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-500 block mb-1">Kategori</label>
                                <select v-model="quickItem.category" class="w-full p-3 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-sm font-bold focus:outline-none">
                                    <option value="Suku Cadang">Suku Cadang</option>
                                    <option value="Perlengkapan">Perlengkapan</option>
                                    <option value="ATK">ATK</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="text-xs font-bold text-slate-500 block mb-1">Nama Barang</label>
                            <input ref="quickItemNameInput" type="text" v-model="quickItem.name" @keydown.enter="saveQuickItem" class="w-full p-3 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-sm font-bold focus:ring-2 focus:ring-blue-500/20 outline-none" placeholder="Contoh: Kampas Rem Depan">
                        </div>
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label class="text-xs font-bold text-slate-500 block mb-1">Stok Awal</label>
                                <input type="number" v-model="quickItem.stock" class="w-full p-3 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-sm font-bold focus:ring-2 focus:ring-blue-500/20 outline-none">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-500 block mb-1">Satuan</label>
                                <input type="text" v-model="quickItem.unit" class="w-full p-3 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-sm font-bold focus:ring-2 focus:ring-blue-500/20 outline-none" placeholder="Pcs">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-500 block mb-1">Min. Stok</label>
                                <input type="number" v-model="quickItem.min_stock" class="w-full p-3 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-sm font-bold focus:ring-2 focus:ring-blue-500/20 outline-none">
                            </div>
                        </div>
                        
                        <button @click="saveQuickItem" class="w-full py-3 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition shadow-lg flex items-center justify-center gap-2">
                            <i class="bi bi-save"></i> Simpan ke Rak Ini
                        </button>
                    </div>
                </div>

        </main>
        
        <?php include 'components/sidebar_right.php'; ?>

    </div>

    <script>
        const { createApp, ref, computed, onMounted, nextTick } = Vue;

        createApp({
            setup() {
                const rooms = ref([]);
                const cabinets = ref([]);
                const racks = ref([]);
                
                const selectedRoom = ref(null);
                const selectedCabinet = ref(null);
                
                const showModal = ref(false);
                const modalType = ref(''); // room, cabinet, rack
                const newItem = ref({ name: '', notes: '' });

                const showManageModal = ref(false);
                const managingRack = ref(null);
                const manageTab = ref('current'); // current, add
                const rackItems = ref([]);
                const allItems = ref([]);
                const itemSearch = ref('');
                const selectedItemIds = ref([]);

                const filteredAvailableItems = computed(() => {
                    let items = allItems.value;
                    
                    if(itemSearch.value) {
                        const q = itemSearch.value.toLowerCase();
                        items = items.filter(i => i.name.toLowerCase().includes(q) || i.code.toLowerCase().includes(q));
                    }
                    
                    // Exclude items already in this rack
                    return items.filter(i => i.rack_id != managingRack.value?.id);
                });

                const modalTitle = computed(() => {
                    if(modalType.value === 'room') return 'Ruangan';
                    if(modalType.value === 'cabinet') return 'Lemari';
                    if(modalType.value === 'rack') return 'Rak';
                    return '';
                });

                const fetchRooms = async () => {
                    try {
                        const res = await fetch('api.php?action=get_rooms');
                        const data = await res.json();
                        if(data.status === 'success') rooms.value = data.data;
                    } catch(e) { console.error(e); }
                };

                const fetchCabinets = async (roomId) => {
                    try {
                        const res = await fetch(`api.php?action=get_cabinets&room_id=${roomId}`);
                        const data = await res.json();
                        if(data.status === 'success') cabinets.value = data.data;
                    } catch(e) { console.error(e); }
                };

                const fetchRacks = async (cabinetId) => {
                    try {
                        const res = await fetch(`api.php?action=get_racks&cabinet_id=${cabinetId}`);
                        const data = await res.json();
                        if(data.status === 'success') racks.value = data.data;
                    } catch(e) { console.error(e); }
                };

                const cabinetsSection = ref(null);
                const racksSection = ref(null);

                const selectRoom = (room) => {
                    if(selectedRoom.value?.id === room.id) {
                         // Toggle off? No, usually just stay.
                         // Maybe reset below?
                    }
                    selectedRoom.value = room;
                    selectedCabinet.value = null;
                    racks.value = [];
                    fetchCabinets(room.id);
                    
                    nextTick(() => {
                        cabinetsSection.value?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    });
                };

                const selectCabinet = (cab) => {
                    selectedCabinet.value = cab;
                    fetchRacks(cab.id);
                    nextTick(() => {
                        racksSection.value?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    });
                };

                const openAddModal = (type) => {
                    modalType.value = type;
                    newItem.value = { name: '', notes: '' };
                    showModal.value = true;
                    nextTick(() => {
                        // We use querySelector or template refs. 
                        // Since I added ref="nameInput" in previous step, accessing it via template refs in setup is tricky without return or explicit ref binding.
                        // However, inside methods attached to the instance setup, refs are available if returned.
                        // Or easier:
                        document.querySelector('input[placeholder*="Contoh: Gudang A"]')?.focus();
                    });
                };

                const saveItem = async () => {
                    if(!newItem.value.name) return;
                    
                    let action = '';
                    let payload = { ...newItem.value };

                    if(modalType.value === 'room') {
                        action = 'create_room';
                    } else if(modalType.value === 'cabinet') {
                        action = 'create_cabinet';
                        payload.room_id = selectedRoom.value.id;
                    } else if(modalType.value === 'rack') {
                        action = 'create_rack';
                        payload.cabinet_id = selectedCabinet.value.id;
                    }

                    try {
                         const res = await fetch(`api.php?action=${action}`, {
                            method: 'POST',
                            body: JSON.stringify(payload)
                        });
                        const data = await res.json();
                        if(data.status === 'success') {
                            showModal.value = false;
                            if(modalType.value === 'room') fetchRooms();
                            if(modalType.value === 'cabinet') fetchCabinets(selectedRoom.value.id);
                            if(modalType.value === 'rack') fetchRacks(selectedCabinet.value.id);
                        }
                    } catch(e) { console.error(e); }
                };

                const deleteItem = async (type, id) => {
                    const result = await Swal.fire({
                        title: 'Hapus?',
                        text: "Data akan dihapus permanen.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Hapus',
                        cancelButtonText: 'Batal'
                    });

                    if (result.isConfirmed) {
                        try {
                            const res = await fetch(`api.php?action=delete_${type}&id=${id}`);
                            const data = await res.json();
                            if(data.status === 'success') {
                                if(type === 'room') {
                                    fetchRooms();
                                    if(selectedRoom.value && selectedRoom.value.id === id) {
                                        selectedRoom.value = null;
                                        cabinets.value = [];
                                        racks.value = [];
                                    }
                                }
                                if(type === 'cabinet') {
                                    fetchCabinets(selectedRoom.value.id);
                                    if(selectedCabinet.value && selectedCabinet.value.id === id) {
                                        selectedCabinet.value = null;
                                        racks.value = [];
                                    }
                                }
                                if(type === 'rack') fetchRacks(selectedCabinet.value.id);
                            }
                        } catch(e) { console.error(e); }
                    }
                };

                const openManageItems = (rack) => {
                    managingRack.value = rack;
                    manageTab.value = 'current'; // Default to current, user switches to add
                    // Wait, user might want to go straight to add if empty?
                    // User request: "Default all items visible". 
                    // Let's just focus relevant tab.
                    selectedItemIds.value = [];
                    itemSearch.value = '';
                    showManageModal.value = true;
                    fetchRackItems(rack.id);
                    fetchAllItems();
                    
                    // If focusing on Add Tab later, handle there.
                };

                const closeManageModal = () => {
                    showManageModal.value = false;
                    managingRack.value = null;
                };

                const fetchRackItems = async (rackId) => {
                    try {
                        const res = await fetch(`api.php?action=get_items_by_rack&rack_id=${rackId}`);
                        const data = await res.json();
                        if(data.status === 'success') rackItems.value = data.data;
                    } catch(e) {}
                };

                const fetchAllItems = async () => {
                    try {
                        const res = await fetch('api.php?action=get_items');
                        const data = await res.json();
                        if(data.status === 'success') allItems.value = data.data;
                    } catch(e) {}
                };

                const toggleItemSelection = (id) => {
                    if(selectedItemIds.value.includes(id)) {
                        selectedItemIds.value = selectedItemIds.value.filter(x => x !== id);
                    } else {
                        selectedItemIds.value.push(id);
                    }
                };

                const moveItemsToRack = async () => {
                    if(selectedItemIds.value.length === 0) return;
                    try {
                        const res = await fetch('api.php?action=move_items_to_rack', {
                            method: 'POST',
                            body: JSON.stringify({
                                rack_id: managingRack.value.id,
                                item_ids: selectedItemIds.value
                            })
                        });
                        const data = await res.json();
                        if(data.status === 'success') {
                            Swal.fire({ icon: 'success', title: 'Berhasil', text: 'Barang berhasil dipindahkan', timer: 1500, showConfirmButton: false });
                            fetchRackItems(managingRack.value.id);
                            fetchAllItems(); // Refresh full list
                            selectedItemIds.value = [];
                            manageTab.value = 'current';
                        }
                    } catch(e) {}
                };

                onMounted(() => {
                    fetchRooms();
                });


                // const openManageItems = () => {
                //     showManageModal.value = true;
                //     selectFirstItem();
                // }


                const selectFirstItem = () => {
                    const items = filteredAvailableItems.value;
                    if(items.length > 0) {
                        toggleItemSelection(items[0].id);
                        itemSearch.value = ''; 
                    }
                };

                const resetSelection = () => {
                    selectedRoom.value = null;
                    selectedCabinet.value = null;
                    cabinets.value = [];
                    racks.value = [];
                };

                // --- QUICK ADD ITEM ---
                const showQuickAddModal = ref(false);
                const quickAddItemTarget = ref(null);
                const quickItemNameInput = ref(null);
                const quickItem = ref({
                    code: '',
                    name: '',
                    category: 'Suku Cadang',
                    stock: 1,
                    unit: 'Pcs',
                    min_stock: 5
                });

                const openQuickAdd = (rack) => {
                    quickAddItemTarget.value = rack;
                    // Auto-generate simple code for speed
                    const randomNum = Math.floor(1000 + Math.random() * 9000);
                    quickItem.value = {
                        code: `ITEM-${randomNum}`,
                        name: '',
                        category: 'Suku Cadang',
                        stock: 1,
                        unit: 'Pcs',
                        min_stock: 5
                    };
                    showQuickAddModal.value = true;
                    nextTick(() => {
                        quickItemNameInput.value?.focus();
                    });
                };

                const saveQuickItem = async () => {
                    if(!quickItem.value.name) return Swal.fire('Error', 'Nama barang wajib diisi', 'error');

                    try {
                        const payload = {
                            ...quickItem.value,
                            rack_id: quickAddItemTarget.value.id,
                            location: `${selectedRoom.value.name} > ${selectedCabinet.value.name} > ${quickAddItemTarget.value.name}`
                        };

                        const response = await fetch('api.php?action=create_item', {
                            method: 'POST',
                            body: JSON.stringify(payload)
                        });
                        const data = await response.json();
                        if(data.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: 'Item berhasil ditambahkan ke rak ini!',
                                timer: 1500,
                                showConfirmButton: false,
                                toast: true,
                                position: 'top-end'
                            });
                            showQuickAddModal.value = false;
                            
                            // Refresh logic: Update count manually to avoid full reload or re-fetch
                            // Actually, let's re-fetch racks to get updated item_count
                            fetchRacks(selectedCabinet.value.id);
                        } else {
                            throw new Error(data.message);
                        }
                    } catch(e) {
                        Swal.fire('Error', e.message, 'error');
                    }
                };

                return {
                    rooms, cabinets, racks,
                    selectedRoom, selectedCabinet,
                    showModal, modalTitle, newItem,
                    selectRoom, selectCabinet,
                    openAddModal, saveItem, deleteItem,
                    showManageModal, managingRack, manageTab, rackItems, itemSearch, filteredAvailableItems, selectedItemIds,
                    openManageItems, closeManageModal, toggleItemSelection, moveItemsToRack, selectFirstItem,
                    resetSelection, cabinetsSection, racksSection,
                    showQuickAddModal, quickItem, quickAddItemTarget, openQuickAdd, saveQuickItem, quickItemNameInput
                };
            }
        }).mount('#app');
    </script>
</body>
</html>

