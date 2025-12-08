<!DOCTYPE html>
<html lang="id" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sutan Raya - Business OS V8.5</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            transition: background-color 0.3s, color 0.3s; 
            overflow: hidden; /* Prevent body scroll */
        }
        
        /* Custom Scrollbar */
        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        .dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #475569; }
        
        /* Calendar Grid */
        .calendar-grid { display: grid; grid-template-columns: repeat(7, 1fr); border-top: 1px solid #e5e7eb; border-left: 1px solid #e5e7eb; }
        .dark .calendar-grid { border-color: #374151; }
        .calendar-day { min-height: 100px; padding: 0.5rem; display: flex; flex-direction: column; border-right: 1px solid #e5e7eb; border-bottom: 1px solid #e5e7eb; }
        .dark .calendar-day { border-color: #374151; background-color: #1e293b; }
        .calendar-day.other-month { background-color: #f9fafb; color: #d1d5db; }
        .dark .calendar-day.other-month { background-color: #0f172a; color: #475569; }
        .calendar-event { font-size: 0.65rem; padding: 3px 5px; border-radius: 3px; margin-bottom: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; cursor: pointer; border-left: 3px solid; transition: transform 0.1s; font-weight: 600; }
        .calendar-event:hover { transform: scale(1.02); z-index: 10; }

        /* Ticket Visuals */
        .ticket { background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%); border-radius: 1.5rem 1.5rem 0 0; }
        .ticket-cutout { position: relative; background: #fff; border-radius: 0 0 1.5rem 1.5rem; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); }
        .ticket-cutout:before, .ticket-cutout:after { content: ''; position: absolute; width: 2rem; height: 2rem; border-radius: 50%; top: -1rem; background: #111827; }
        .dark .ticket-cutout:before, .dark .ticket-cutout:after { background: #0f172a; } /* Match dark bg */
        .ticket-cutout:before { left: -1rem; } .ticket-cutout:after { right: -1rem; }

        /* Utility */
        [v-cloak] { display: none; }
        .animate-fade-in { animation: fadeIn 0.3s ease-out forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
    </style>
    
    <script>
        tailwind.config = { 
            darkMode: 'class',
            theme: { extend: { colors: { 'sr-blue': '#0f172a', 'sr-gold': '#d4af37' } } } 
        }
        window.initialView = 'routeManagement';
    </script>
</head>
<body class="h-screen w-screen bg-slate-50 text-slate-800 dark:bg-slate-900 dark:text-slate-100">

    <div id="app" class="flex h-full w-full" v-cloak>
        
        <?php $currentPage = 'route_management'; include 'components/sidebar.php'; ?>

        <main class="flex-1 flex flex-col min-w-0 bg-slate-50 dark:bg-slate-900 relative h-full overflow-hidden transition-colors duration-300">
            <header class="h-16 bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between px-6 shadow-sm z-10 flex-shrink-0 transition-colors duration-300">
                <div>
                    <h2 class="text-lg font-bold text-slate-800 dark:text-white">{{ currentViewTitle }}</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Sistem Operasional V8.5 Ultimate</p>
                </div>
                <div class="flex items-center gap-4">
                    <div class="text-right hidden sm:block">
                        <div class="text-xs font-bold text-slate-400 uppercase">{{ currentDate }}</div>
                        <div class="text-lg font-mono font-bold text-sr-blue dark:text-sr-gold leading-none">{{ currentTime }}</div>
                    </div>
                    <button @click="toggleDarkMode" class="w-9 h-9 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-yellow-400 hover:bg-slate-200 dark:hover:bg-slate-600 transition-all flex items-center justify-center" title="Mode Gelap/Terang">
                        <i :class="isDarkMode ? 'bi-sun-fill' : 'bi-moon-stars-fill'"></i>
                    </button>
                    <button @click="toggleFullscreen" class="w-9 h-9 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 transition-all flex items-center justify-center">
                        <i :class="isFullscreen ? 'bi-arrows-angle-contract' : 'bi-arrows-fullscreen'"></i>
                    </button>
                </div>
            </header>

            <div class="flex-1 relative overflow-y-auto w-full">
                



                <div v-if="view === 'routeManagement'" class="absolute inset-0 p-6 custom-scrollbar space-y-6">
                    <div class="flex justify-between items-center">
                        <h2 class="text-2xl font-bold text-sr-blue dark:text-white">Kelola Rute</h2>
                        <button @click="openRouteModal()" class="bg-sr-blue dark:bg-blue-600 text-white px-4 py-2 rounded-lg font-bold shadow-lg hover:bg-slate-800 transition-colors"><i class="bi bi-plus-lg mr-2"></i> Tambah Rute</button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                        <div v-for="r in routeConfig" :key="r.id" class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-5 relative overflow-hidden group">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <div class="text-lg font-bold text-slate-800 dark:text-white">{{ r.origin }} <i class="bi bi-arrow-right mx-1 text-slate-400"></i> {{ r.destination }}</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400 font-mono mt-1">{{ r.id }}</div>
                                </div>
                                <div class="flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button @click="openRouteModal(r)" class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 hover:bg-blue-100 flex items-center justify-center"><i class="bi bi-pencil-fill text-xs"></i></button>
                                    <button @click="deleteRoute(r.id)" class="w-8 h-8 rounded-full bg-red-50 text-red-600 hover:bg-red-100 flex items-center justify-center"><i class="bi bi-trash-fill text-xs"></i></button>
                                </div>
                            </div>
                            
                            <div class="space-y-3">
                                <div class="bg-slate-50 dark:bg-slate-700/50 p-3 rounded-lg">
                                    <div class="text-[10px] font-bold text-slate-400 uppercase mb-2">Jadwal Keberangkatan</div>
                                    <div class="flex flex-wrap gap-2">
                                        <span v-for="t in r.schedules" class="bg-white dark:bg-slate-600 border border-slate-200 dark:border-slate-500 px-2 py-1 rounded text-xs font-bold text-slate-600 dark:text-slate-300">{{ t }}</span>
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="bg-slate-50 dark:bg-slate-700/50 p-3 rounded-lg">
                                        <div class="text-[10px] font-bold text-slate-400 uppercase mb-1">Umum</div>
                                        <div class="font-bold text-slate-700 dark:text-slate-200">{{ formatRupiah(r.prices.umum) }}</div>
                                    </div>
                                    <div class="bg-slate-50 dark:bg-slate-700/50 p-3 rounded-lg">
                                        <div class="text-[10px] font-bold text-slate-400 uppercase mb-1">Pelajar</div>
                                        <div class="font-bold text-slate-700 dark:text-slate-200">{{ formatRupiah(r.prices.pelajar) }}</div>
                                    </div>
                                    <div class="bg-slate-50 dark:bg-slate-700/50 p-3 rounded-lg">
                                        <div class="text-[10px] font-bold text-slate-400 uppercase mb-1">Dropping</div>
                                        <div class="font-bold text-slate-700 dark:text-slate-200">{{ formatRupiah(r.prices.dropping) }}</div>
                                    </div>
                                    <div class="bg-slate-50 dark:bg-slate-700/50 p-3 rounded-lg">
                                        <div class="text-[10px] font-bold text-slate-400 uppercase mb-1">Carter</div>
                                        <div class="font-bold text-slate-700 dark:text-slate-200">{{ formatRupiah(r.prices.carter) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Vehicle Modal -->
                <div v-if="isVehicleModalVisible" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm animate-fade-in">
                    <div class="bg-white dark:bg-slate-800 w-full max-w-md rounded-2xl shadow-2xl overflow-hidden flex flex-col">
                        <div class="p-5 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-slate-50 dark:bg-slate-900/50">
                            <h3 class="text-lg font-bold text-slate-800 dark:text-white">{{ vehicleModal.mode === 'add' ? 'Tambah Armada' : 'Edit Armada' }}</h3>
                            <button @click="closeVehicleModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"><i class="bi bi-x-lg"></i></button>
                        </div>
                        <div class="p-6 space-y-4">
                            <div><label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Nama Unit</label><input type="text" v-model="vehicleModal.data.name" class="w-full p-2.5 border rounded-lg dark:bg-slate-700 dark:border-slate-600 dark:text-white text-sm" placeholder="Contoh: Hiace Premio 01"></div>
                            <div class="grid grid-cols-2 gap-4">
                                <div><label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Plat Nomor</label><input type="text" v-model="vehicleModal.data.plate" class="w-full p-2.5 border rounded-lg dark:bg-slate-700 dark:border-slate-600 dark:text-white text-sm" placeholder="BA 1234 XX"></div>
                                <div><label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Kapasitas</label><input type="number" v-model="vehicleModal.data.capacity" class="w-full p-2.5 border rounded-lg dark:bg-slate-700 dark:border-slate-600 dark:text-white text-sm"></div>
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Status</label>
                                <select v-model="vehicleModal.data.status" class="w-full p-2.5 border rounded-lg dark:bg-slate-700 dark:border-slate-600 dark:text-white text-sm">
                                    <option value="Tersedia">Tersedia</option>
                                    <option value="Perbaikan">Perbaikan</option>
                                    <option value="On Trip">On Trip</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Icon</label>
                                <div class="flex gap-2">
                                    <button v-for="icon in ['bi-truck-front-fill', 'bi-bus-front-fill', 'bi-car-front-fill']" @click="vehicleModal.data.icon = icon" class="w-10 h-10 rounded-lg border flex items-center justify-center text-lg transition-colors" :class="vehicleModal.data.icon === icon ? 'bg-blue-50 border-blue-500 text-blue-600' : 'border-slate-200 dark:border-slate-600 text-slate-400 hover:bg-slate-50'"><i :class="icon"></i></button>
                                </div>
                            </div>
                        </div>
                        <div class="p-5 border-t border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 flex justify-end gap-3">
                            <button @click="closeVehicleModal" class="px-4 py-2 text-slate-600 font-bold hover:bg-slate-200 rounded-lg transition-colors text-sm">Batal</button>
                            <button @click="saveVehicle" class="px-6 py-2 bg-sr-blue dark:bg-blue-600 text-white font-bold rounded-lg shadow-lg hover:bg-slate-800 transition-colors text-sm">Simpan</button>
                        </div>
                    </div>
                </div>

                <!-- Driver Modal -->
                <div v-if="isDriverModalVisible" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm animate-fade-in">
                    <div class="bg-white dark:bg-slate-800 w-full max-w-md rounded-2xl shadow-2xl overflow-hidden flex flex-col">
                        <div class="p-5 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-slate-50 dark:bg-slate-900/50">
                            <h3 class="text-lg font-bold text-slate-800 dark:text-white">{{ driverModal.mode === 'add' ? 'Tambah Supir' : 'Edit Supir' }}</h3>
                            <button @click="closeDriverModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"><i class="bi bi-x-lg"></i></button>
                        </div>
                        <div class="p-6 space-y-4">
                            <div><label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Nama Lengkap</label><input type="text" v-model="driverModal.data.name" class="w-full p-2.5 border rounded-lg dark:bg-slate-700 dark:border-slate-600 dark:text-white text-sm" placeholder="Nama Supir"></div>
                            <div><label class="text-xs font-bold text-slate-500 uppercase mb-1 block">No. WhatsApp</label><input type="text" v-model="driverModal.data.phone" class="w-full p-2.5 border rounded-lg dark:bg-slate-700 dark:border-slate-600 dark:text-white text-sm" placeholder="08..."></div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Jenis SIM</label>
                                    <select v-model="driverModal.data.licenseType" class="w-full p-2.5 border rounded-lg dark:bg-slate-700 dark:border-slate-600 dark:text-white text-sm">
                                        <option value="A Umum">A Umum</option>
                                        <option value="B1 Umum">B1 Umum</option>
                                        <option value="B2 Umum">B2 Umum</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Status</label>
                                    <select v-model="driverModal.data.status" class="w-full p-2.5 border rounded-lg dark:bg-slate-700 dark:border-slate-600 dark:text-white text-sm">
                                        <option value="Standby">Standby</option>
                                        <option value="Jalan">Jalan</option>
                                        <option value="Libur">Libur</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="p-5 border-t border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 flex justify-end gap-3">
                            <button @click="closeDriverModal" class="px-4 py-2 text-slate-600 font-bold hover:bg-slate-200 rounded-lg transition-colors text-sm">Batal</button>
                            <button @click="saveDriver" class="px-6 py-2 bg-sr-blue dark:bg-blue-600 text-white font-bold rounded-lg shadow-lg hover:bg-slate-800 transition-colors text-sm">Simpan</button>
                        </div>
                    </div>
                </div>

                <!-- Route Modal -->
                <div v-if="isRouteModalVisible" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm animate-fade-in">
                    <div class="bg-white dark:bg-slate-800 w-full max-w-2xl rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
                        <div class="p-6 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-slate-50 dark:bg-slate-900/50">
                            <h3 class="text-lg font-bold text-slate-800 dark:text-white">{{ routeModalMode === 'add' ? 'Tambah Rute Baru' : 'Edit Rute' }}</h3>
                            <button @click="isRouteModalVisible = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"><i class="bi bi-x-lg text-xl"></i></button>
                        </div>
                        <div class="p-6 overflow-y-auto custom-scrollbar space-y-6">
                            <div class="grid grid-cols-2 gap-4">
                                <div><label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Asal (Origin)</label><input type="text" v-model="routeForm.origin" class="w-full p-2 border rounded-lg dark:bg-slate-700 dark:border-slate-600 dark:text-white"></div>
                                <div><label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Tujuan (Destination)</label><input type="text" v-model="routeForm.destination" class="w-full p-2 border rounded-lg dark:bg-slate-700 dark:border-slate-600 dark:text-white"></div>
                            </div>
                            
                            <div>
                                <label class="text-xs font-bold text-slate-500 uppercase mb-2 block">Jadwal (Pisahkan dengan koma)</label>
                                <input type="text" v-model="routeForm.schedulesInput" placeholder="Contoh: 08:00, 10:00, 14:00" class="w-full p-2 border rounded-lg dark:bg-slate-700 dark:border-slate-600 dark:text-white font-mono">
                                <div class="mt-2 flex flex-wrap gap-2">
                                    <span v-for="t in routeForm.schedulesInput.split(',').map(s=>s.trim()).filter(s=>s)" class="bg-blue-50 text-blue-600 px-2 py-1 rounded text-xs font-bold">{{ t }}</span>
                                </div>
                            </div>

                            <div class="space-y-4 border-t pt-4 dark:border-slate-700">
                                <h4 class="font-bold text-slate-700 dark:text-slate-300">Harga Tiket</h4>
                                <div class="grid grid-cols-2 gap-4">
                                    <div><label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Umum</label><input type="number" v-model="routeForm.prices.umum" class="w-full p-2 border rounded-lg dark:bg-slate-700 dark:border-slate-600 dark:text-white"></div>
                                    <div><label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Pelajar</label><input type="number" v-model="routeForm.prices.pelajar" class="w-full p-2 border rounded-lg dark:bg-slate-700 dark:border-slate-600 dark:text-white"></div>
                                    <div><label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Dropping</label><input type="number" v-model="routeForm.prices.dropping" class="w-full p-2 border rounded-lg dark:bg-slate-700 dark:border-slate-600 dark:text-white"></div>
                                    <div><label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Carter</label><input type="number" v-model="routeForm.prices.carter" class="w-full p-2 border rounded-lg dark:bg-slate-700 dark:border-slate-600 dark:text-white"></div>
                                </div>
                            </div>
                        </div>
                        <div class="p-6 border-t border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 flex justify-end gap-3">
                            <button @click="isRouteModalVisible = false" class="px-4 py-2 text-slate-600 font-bold hover:bg-slate-200 rounded-lg transition-colors">Batal</button>
                            <button @click="saveRoute()" class="px-6 py-2 bg-sr-blue dark:bg-blue-600 text-white font-bold rounded-lg shadow-lg hover:bg-slate-800 transition-colors">Simpan Rute</button>
                        </div>
                    </div>
                </div>

                <div v-if="isTicketModalVisible" class="fixed inset-0 bg-black/90 z-50 flex items-center justify-center p-4 backdrop-blur-sm" @click.self="isTicketModalVisible=false">
                    <div class="w-full max-w-sm transform transition-all scale-100 animate-fade-in">
                        <div class="text-white/70 text-center mb-4 text-sm font-medium cursor-pointer hover:text-white" @click="isTicketModalVisible=false">Klik area gelap untuk tutup</div>
                        
                        <div class="shadow-2xl rounded-[1.5rem] overflow-hidden relative">
                            <div class="bg-gradient-to-br from-blue-800 to-blue-600 p-8 text-white relative overflow-hidden rounded-t-[1.5rem]">
                                <div class="absolute top-0 right-0 w-32 h-32 bg-white opacity-10 rounded-full -mr-10 -mt-10"></div>
                                <div class="flex justify-between items-start mb-8 relative z-10">
                                    <div>
                                        <div class="text-3xl font-bold tracking-tight">Sutan<span class="font-light">Raya</span></div>
                                        <div class="text-[10px] opacity-80 uppercase tracking-widest mt-1 font-bold">E-Ticket</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-[10px] opacity-60 uppercase font-bold">Booking ID</div>
                                        <div class="font-mono text-lg font-bold">#{{ ticketData.id.toString().slice(-6) }}</div>
                                    </div>
                                </div>
                                <div class="flex justify-between items-end relative z-10">
                                    <div>
                                        <div class="text-[10px] opacity-60 uppercase font-bold mb-1">Penumpang</div>
                                        <div class="text-xl font-bold truncate max-w-[160px]">{{ ticketData.passengerName }}</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-[10px] opacity-60 uppercase font-bold mb-1">Waktu</div>
                                        <div class="text-xl font-bold">{{ ticketData.time || 'Bus' }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-white p-8 pt-10 relative rounded-b-[1.5rem]">
                                <div class="absolute top-0 left-0 w-full h-4 -mt-2 bg-white" style="clip-path: polygon(0 0, 100% 0, 100% 100%, 0 100%, 0 0, 1rem 0.5rem, 2rem 0, 3rem 0.5rem, 4rem 0, 5rem 0.5rem, 6rem 0, 7rem 0.5rem, 8rem 0, 9rem 0.5rem, 10rem 0, 11rem 0.5rem, 12rem 0, 13rem 0.5rem, 14rem 0, 15rem 0.5rem, 16rem 0, 17rem 0.5rem, 18rem 0, 19rem 0.5rem, 20rem 0, 21rem 0.5rem, 22rem 0, 23rem 0.5rem, 24rem 0);"></div>
                                <div class="flex justify-between mb-5 border-b border-dashed border-gray-200 pb-5">
                                    <div><div class="text-xs text-gray-400 uppercase font-bold">Rute</div><div class="font-bold text-gray-800 text-sm">{{ ticketData.routeId }}</div></div>
                                    <div class="text-right"><div class="text-xs text-gray-400 uppercase font-bold">Tanggal</div><div class="font-bold text-gray-800 text-sm">{{ formatDate(ticketData.date) }}</div></div>
                                </div>
                                <div class="grid grid-cols-3 gap-2 mb-6">
                                    <div class="bg-gray-50 p-2 rounded text-center"><div class="text-[10px] text-gray-400 uppercase font-bold">Kursi</div><div class="font-bold text-gray-800 text-sm">{{ ticketData.seatNumbers || 'UNIT' }}</div></div>
                                    <div class="bg-gray-50 p-2 rounded text-center"><div class="text-[10px] text-gray-400 uppercase font-bold">Layanan</div><div class="font-bold text-blue-600 text-sm">{{ ticketData.serviceType }}</div></div>
                                    <div class="bg-gray-50 p-2 rounded text-center"><div class="text-[10px] text-gray-400 uppercase font-bold">Validasi</div><div class="font-bold text-sm" :class="ticketData.validationStatus==='Valid'?'text-green-600':(ticketData.validationStatus==='Menunggu Validasi'?'text-red-500':'text-gray-600')">{{ ticketData.validationStatus === 'Valid' ? 'VALID' : 'WAIT' }}</div></div>
                                </div>
                                <div class="space-y-3 mb-6 bg-blue-50/50 p-4 rounded-xl border border-blue-100">
                                    <div class="flex gap-3"><i class="bi bi-geo-alt-fill text-blue-300"></i> <div><div class="text-[10px] text-gray-400 uppercase font-bold">Jemput</div><div class="text-xs font-bold text-gray-700">{{ ticketData.pickupAddress || 'Sesuai Maps' }}</div></div></div>
                                    <div class="flex gap-3"><i class="bi bi-flag-fill text-blue-300"></i> <div><div class="text-[10px] text-gray-400 uppercase font-bold">Tujuan</div><div class="text-xs font-bold text-gray-700">{{ ticketData.dropoffAddress || '-' }}</div></div></div>
                                </div>
                                <button @click="printTicket(ticketData)" class="w-full py-3 rounded-xl border-2 border-gray-100 text-gray-500 font-bold text-sm hover:bg-gray-50 hover:text-gray-800 transition-colors flex items-center justify-center gap-2"><i class="bi bi-printer"></i> Cetak Tiket</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="isProofModalVisible" class="fixed inset-0 bg-black/95 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
                    <div class="bg-white p-6 rounded-2xl text-center max-w-lg w-full shadow-2xl animate-fade-in">
                        <h3 class="text-xl font-bold text-gray-800 mb-1">Validasi Pembayaran</h3>
                        <p class="text-sm text-gray-500 mb-4">Metode: <span class="font-bold text-sutan-blue-900 uppercase">{{ validationData.paymentMethod }}</span></p>
                        
                        <div v-if="validationData.paymentMethod === 'Cash'" class="bg-gray-50 p-6 rounded-xl border border-gray-200 mb-6 text-left space-y-3">
                            <div class="flex justify-between"><span class="text-gray-500 text-sm">Lokasi:</span> <span class="font-bold text-gray-800">{{ validationData.paymentLocation || '-' }}</span></div>
                            <div class="flex justify-between"><span class="text-gray-500 text-sm">Penerima:</span> <span class="font-bold text-gray-800">{{ validationData.paymentReceiver || '-' }}</span></div>
                        </div>

                        <div v-if="(validationData.paymentMethod === 'Transfer' || validationData.paymentMethod === 'DP') && validationData.paymentProof" class="mb-6">
                            <div class="bg-slate-100 p-2 rounded-xl mb-2 border border-slate-200">
                                <img :src="'transfer/' + validationData.paymentProof" class="w-full h-48 object-cover rounded-lg cursor-pointer hover:opacity-90 transition-opacity" @click="window.open('transfer/' + validationData.paymentProof, '_blank')">
                            </div>
                            <a :href="'transfer/' + validationData.paymentProof" target="_blank" class="text-xs font-bold text-blue-600 hover:underline"><i class="bi bi-box-arrow-up-right mr-1"></i> Lihat Ukuran Penuh</a>
                        </div>
                        <div v-else-if="(validationData.paymentMethod === 'Transfer' || validationData.paymentMethod === 'DP') && !validationData.paymentProof" class="mb-6 p-4 bg-red-50 text-red-600 rounded-xl text-xs font-bold border border-red-100">
                            <i class="bi bi-exclamation-circle mr-1"></i> Bukti Transfer Belum Diupload
                        </div>

                        <div class="flex gap-3">
                            <button @click="isProofModalVisible=false" class="flex-1 py-3 rounded-xl bg-gray-100 font-bold text-gray-600 hover:bg-gray-200 transition-colors">Tutup</button>
                            <button @click="confirmValidation(validationData)" class="flex-1 py-3 rounded-xl bg-green-600 text-white font-bold hover:bg-green-700 shadow-lg shadow-green-200 transition-transform active:scale-95 flex items-center justify-center gap-2"><i class="bi bi-check-lg"></i> Validasi Lunas</button>
                        </div>
                    </div>
                </div>
                <div v-if="isTripControlVisible" class="fixed inset-0 bg-black/80 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
                    <div class="bg-white w-full max-w-sm rounded-2xl shadow-2xl p-6 animate-fade-in">
                        <div class="text-center mb-6">
                            <h3 class="text-xl font-bold text-gray-900">Kontrol Perjalanan</h3>
                            <p class="text-sm text-gray-500 mt-1">{{ activeTripControl?.routeConfig?.routeId }} • {{ activeTripControl?.fleet?.name }}</p>
                        </div>
                        <div class="space-y-3">
                            <button @click="updateTripStatus(activeTripControl, 'Tiba')" class="w-full py-3 rounded-xl bg-green-100 text-green-700 font-bold hover:bg-green-200 transition-colors flex items-center justify-center gap-2">
                                <i class="bi bi-check-circle-fill"></i> Selesai (Tiba)
                            </button>
                            <button @click="updateTripStatus(activeTripControl, 'Kendala')" class="w-full py-3 rounded-xl bg-red-100 text-red-700 font-bold hover:bg-red-200 transition-colors flex items-center justify-center gap-2">
                                <i class="bi bi-exclamation-triangle-fill"></i> Accident / Kendala
                            </button>
                        </div>
                        <div class="mt-6">
                            <button @click="isTripControlVisible=false" class="w-full py-3 rounded-xl bg-gray-100 font-bold text-gray-600 hover:bg-gray-200 transition-colors">Tutup</button>
                        </div>
                    </div>
                </div>

                <div v-if="isDispatchModalVisible" class="fixed inset-0 bg-black/80 z-50 flex items-center justify-center p-4 backdrop-blur-sm"><div class="bg-white w-full max-w-md rounded-2xl shadow-2xl p-6 animate-fade-in"><div class="text-center mb-6"><h3 class="text-xl font-bold text-gray-900">Dispatch Armada</h3></div><div class="space-y-4"><div><label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Armada</label><select v-model="dispatchForm.fleetId" class="w-full border-gray-200 p-3 rounded-xl bg-white outline-none font-semibold text-gray-700"><option value="" disabled>-- Pilih Mobil --</option><option v-for="f in fleet.filter(x=>x.status==='Tersedia')" :value="f.id">{{ f.name }} ({{f.plate}})</option></select></div><div><label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Supir</label><select v-model="dispatchForm.driverId" class="w-full border-gray-200 p-3 rounded-xl bg-white outline-none font-semibold text-gray-700"><option value="" disabled>-- Pilih Supir --</option><option v-for="d in drivers.filter(x=>x.status==='Standby')" :value="d.id">{{ d.name }}</option></select></div></div><div class="mt-8 flex gap-3"><button @click="isDispatchModalVisible=false" class="flex-1 bg-white border border-gray-200 hover:bg-gray-50 text-gray-600 py-3 rounded-xl font-bold">Batal</button><button @click="processDispatch" class="flex-1 bg-sr-blue hover:bg-slate-800 text-white py-3 rounded-xl font-bold shadow-lg">Proses</button></div></div></div>

            </div>
        </main>
    </div>
    <script src="app.js?v=<?= time() ?>"></script>
</body>
</html>