<!DOCTYPE html>
<html lang="id" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sutan Raya - Business OS V11</title>
    <link rel="icon" type="image/webp" href="../image/logo.webp">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    
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
        window.initialView = 'dispatcher';
    </script>
</head>
<body class="h-screen w-screen bg-slate-50 text-slate-800 dark:bg-slate-900 dark:text-slate-100">

    <div id="app" class="flex h-full w-full" v-cloak>
        
        <?php $currentPage = 'dispatcher'; include 'components/sidebar.php'; ?>

        <main class="flex-1 flex flex-col min-w-0 bg-slate-50 dark:bg-slate-900 relative h-full overflow-hidden transition-colors duration-300">
            <header class="h-16 bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between px-6 shadow-sm z-10 flex-shrink-0 transition-colors duration-300">
                <div>
                    <h2 class="text-lg font-bold text-slate-800 dark:text-white">{{ currentViewTitle }}</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Sistem Operasional V11 Ultimate</p>
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

            <div class="flex-1 relative overflow-hidden w-full">
                



                <div v-if="view === 'dispatcher'" class="absolute inset-0 p-6 custom-scrollbar space-y-8 overflow-y-auto">
                    
                    <div>
                        <h2 class="font-bold text-gray-700 mb-4 text-lg flex items-center"><i class="bi bi-inbox-fill mr-2 text-orange-500"></i> Antrian Booking</h2>
                        
                        <div v-if="pendingGroupsCount === 0" class="p-12 text-center border-2 border-dashed border-gray-300 rounded-2xl text-gray-400 bg-white">
                            <i class="bi bi-check-circle-fill text-4xl mb-2 block text-green-200"></i>
                            <p>Semua jadwal sudah di-dispatch atau belum ada booking.</p>
                        </div>
                        

                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                            <div v-for="group in groupedBookings" :key="group.key" class="bg-white rounded-2xl shadow-sm border border-gray-200 p-0 relative overflow-hidden hover:shadow-lg transition-all group flex flex-col">
                                <div class="p-5 border-b border-gray-50 bg-gray-50/50 flex justify-between items-start relative">
                                    <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-orange-500"></div>
                                    <div>
                                        <div class="text-3xl font-bold text-gray-900">{{ group.time }}</div>
                                        <div class="text-xs text-gray-500 font-bold mt-1 uppercase tracking-wide">{{ formatDate(group.date) }}</div>
                                        <div class="text-xs font-semibold text-gray-600 mt-1 flex items-center gap-1"><span class="bg-white px-1 rounded border">{{ group.routeConfig?.origin || 'Custom' }}</span> <i class="bi bi-arrow-right text-gray-400"></i> <span class="bg-white px-1 rounded border">{{ group.routeConfig?.destination || group.routeConfig?.name }}</span></div>
                                    </div>
                                    <div class="flex flex-col items-end gap-1">
                                        <span class="bg-orange-100 text-orange-700 text-[10px] font-bold px-2 py-1 rounded uppercase">Pending</span>
                                        <span v-if="group.batchNumber" class="bg-blue-100 text-blue-700 text-[10px] font-bold px-2 py-1 rounded uppercase">Armada {{ group.batchNumber }}</span>
                                    </div>
                                </div>
                                <div class="flex-1 overflow-y-auto p-4 custom-scrollbar min-h-[16rem] max-h-[28rem] bg-white relative">
                                    <div class="space-y-3 mb-6">
                                        <div v-for="p in group.passengers" class="bg-slate-50 border border-slate-100 p-3 rounded-xl">
                                            <div class="flex justify-between items-start">
                                                <div>
                                                    <div class="font-bold text-gray-800 text-sm flex items-center gap-2">
                                                        {{ p.passengerName }}
                                                        <span v-if="p.seatNumbers" class="bg-gray-200 text-gray-600 px-1.5 py-0.5 rounded text-[10px] font-bold">{{ p.seatNumbers }}</span>
                                                    </div>
                                                    <div class="text-xs text-gray-400 mt-1"><i class="bi bi-whatsapp mr-1"></i> {{ p.passengerPhone }}</div>
                                                </div>
                                                <div>
                                                    <a :href="getWaLink(p.passengerPhone)" target="_blank" class="w-8 h-8 rounded-full bg-green-50 text-green-600 flex items-center justify-center hover:bg-green-100 transition-colors"><i class="bi bi-whatsapp"></i></a>
                                                </div>
                                            </div>
                                            <div class="mt-2 flex gap-2">
                                                <span v-if="p.validationStatus==='Valid'" class="px-2 py-0.5 bg-green-100 text-green-700 rounded-lg text-[10px] font-bold flex items-center gap-1"><i class="bi bi-check-circle-fill"></i> Lunas</span>
                                                <span v-else class="px-2 py-0.5 bg-red-100 text-red-700 rounded-lg text-[10px] font-bold flex items-center gap-1"><i class="bi bi-exclamation-circle-fill"></i> Cek Bukti</span>
                                                <button @click="viewTicket(p)" class="px-2 py-0.5 bg-blue-50 text-blue-600 rounded-lg text-[10px] font-bold"><i class="bi bi-ticket-detailed-fill"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Assignment Info using computed assignment from groupedBookings -->
                                    <div v-if="group.assignment && group.assignment.fleet && group.assignment.driver" class="mb-4 bg-blue-50 border border-blue-100 rounded-xl p-3">
                                        <div class="text-[10px] font-bold text-blue-400 uppercase mb-2">Armada Terjadwal</div>
                                        <div class="flex items-center gap-3 mb-2">
                                            <div class="w-8 h-8 rounded-full bg-white flex items-center justify-center text-blue-600 shadow-sm"><i class="bi bi-car-front-fill"></i></div>
                                            <div>
                                                <div class="text-xs font-bold text-blue-900">{{ group.assignment.fleet.name }}</div>
                                                <div class="text-[10px] text-blue-600">{{ group.assignment.fleet.plate }}</div>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-white flex items-center justify-center text-blue-600 shadow-sm"><i class="bi bi-person-fill"></i></div>
                                            <div>
                                                <div class="text-xs font-bold text-blue-900">{{ group.assignment.driver.name }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-else class="mb-4 bg-yellow-50 border border-yellow-100 rounded-xl p-3 flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-yellow-100 text-yellow-600 flex items-center justify-center"><i class="bi bi-exclamation-triangle-fill"></i></div>
                                        <div>
                                            <div class="text-xs font-bold text-yellow-800">Belum Ada Jadwal</div>
                                            <div class="text-[10px] text-yellow-600">Atur armada & supir dulu.</div>
                                        </div>
                                    </div>

                                </div> <!-- End of scrollable content -->
                                
                                <!-- Fixed Footer (Outside scroll) -->
                                <div class="p-4 border-t border-gray-100 bg-gray-50/50">
                                    <div class="flex items-center justify-between">
                                        <div class="flex flex-col">
                                            <span class="text-[10px] uppercase font-bold text-gray-400">Total Muatan</span>
                                            <span class="text-sm font-extrabold text-gray-900">{{ group.totalPassengers }} <span class="text-[10px] font-normal text-gray-500">Orang</span></span>
                                        </div>
                                        
                                        <button v-if="group.assignment && group.assignment.fleet && group.assignment.driver" 
                                                @click="openDispatchModal(group)" 
                                                class="bg-green-500 hover:bg-green-600 text-white px-5 py-2 rounded-lg text-xs font-bold shadow-lg shadow-green-200 transition-all active:scale-95 flex items-center gap-2">
                                            <span>Berangkatkan</span> <i class="bi bi-send-fill"></i>
                                        </button>
                                        <button v-else 
                                                @click="openScheduleModal(group.routeConfig, group.time)" 
                                                class="bg-yellow-400 hover:bg-yellow-500 text-yellow-900 px-5 py-2 rounded-lg text-xs font-bold shadow-lg shadow-yellow-200 transition-all active:scale-95 flex items-center gap-2">
                                            <span>Atur Jadwal</span> <i class="bi bi-gear-fill"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <div v-if="activeTrips.length > 0">
                        <h2 class="font-bold text-gray-700 mb-4 text-lg flex items-center"><i class="bi bi-broadcast mr-2 text-green-500"></i> Sedang Berjalan</h2>
                        <div class="space-y-4">
                            <div v-for="trip in activeTrips" :key="trip.id" class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4 hover:border-blue-300 transition-colors">
                                <div class="flex items-center gap-6">
                                    <div class="text-center px-4 border-r border-gray-100">
                                        <div class="text-2xl font-bold text-gray-800">{{ trip.routeConfig.time }}</div>
                                        <div class="text-xs text-gray-500 font-bold uppercase">{{ trip.routeConfig.routeId }}</div>
                                    </div>
                                    <div>
                                        <div class="font-bold text-lg text-blue-900 flex items-center gap-2">
                                            {{ trip.fleet.name }} <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded font-mono border">{{ trip.fleet.plate }}</span>
                                        </div>
                                        <div class="text-sm text-gray-500 font-medium mt-1"><i class="bi bi-person-badge-fill mr-1 text-gray-400"></i> {{ trip.driver.name }} • {{ trip.passengers.length }} Tiket / {{ getTripPassengerCount(trip) }} Orang</div>
                                    </div>
                                </div>
                                <button @click="openTripControl(trip)" class="px-6 py-2 bg-green-50 text-green-700 rounded-lg font-bold text-sm hover:bg-green-100 transition-colors border border-green-200 flex items-center gap-2"><i class="bi bi-gear-fill"></i> Kontrol</button>
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

                <!-- Schedule Modal -->
                <div v-if="isScheduleModalVisible" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm animate-fade-in">
                    <div class="bg-white dark:bg-slate-800 w-full max-w-md rounded-2xl shadow-2xl overflow-hidden flex flex-col">
                        <div class="p-5 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-slate-50 dark:bg-slate-900/50">
                            <h3 class="text-lg font-bold text-slate-800 dark:text-white">Atur Jadwal / Penugasan</h3>
                            <button @click="isScheduleModalVisible = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"><i class="bi bi-x-lg"></i></button>
                        </div>
                        <div class="p-6 space-y-4">
                            <div class="bg-yellow-50 p-3 rounded-lg border border-yellow-100 mb-4">
                                <div class="text-xs text-yellow-800 font-bold flex justify-between">
                                    <span>{{ scheduleForm.route?.origin }} - {{ scheduleForm.route?.destination }}</span>
                                    <span>{{ scheduleForm.time }}</span>
                                </div>
                            </div>

                            <div>
                                <label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Pilih Armada</label>
                                <select v-model="scheduleForm.fleetId" class="w-full p-2.5 border rounded-lg dark:bg-slate-700 dark:border-slate-600 dark:text-white text-sm">
                                    <option value="" disabled>Pilih Armada...</option>
                                    <option v-for="f in fleet" :value="f.id" :key="f.id">{{ f.name }} - {{ f.plate }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Pilih Supir</label>
                                <select v-model="scheduleForm.driverId" class="w-full p-2.5 border rounded-lg dark:bg-slate-700 dark:border-slate-600 dark:text-white text-sm">
                                    <option value="" disabled>Pilih Supir...</option>
                                    <option v-for="d in drivers" :value="d.id" :key="d.id">{{ d.name }}</option>
                                </select>
                            </div>
                            <div class="flex items-center gap-2 pt-2 border-t border-dashed">
                                <input type="checkbox" id="isDefault" v-model="scheduleForm.isDefault" class="w-4 h-4 rounded text-blue-600">
                                <label for="isDefault" class="text-sm text-slate-700 font-bold select-none cursor-pointer">Simpan sebagai Default (Jadwal Tetap)</label>
                            </div>
                            <p class="text-[10px] text-slate-400 ml-6">Jika dicentang, armada & supir ini akan otomatis terpilih untuk jadwal ini setiap hari.</p>
                        </div>
                        <div class="p-5 border-t border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 flex justify-end gap-3">
                            <button @click="isScheduleModalVisible = false" class="px-4 py-2 text-slate-600 font-bold hover:bg-slate-200 rounded-lg transition-colors text-sm">Batal</button>
                            <button @click="saveScheduleAssignment" class="px-6 py-2 bg-yellow-500 hover:bg-yellow-600 text-white font-bold rounded-lg shadow-lg transition-colors text-sm">Simpan Penugasan</button>
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

                <div v-if="isTicketModalVisible" class="fixed inset-0 bg-black/80 z-50 flex items-center justify-center p-4 backdrop-blur-sm" @click.self="isTicketModalVisible=false">
                    <div class="w-full max-w-sm transform transition-all scale-100 animate-fade-in">
                        <div class="text-white/70 text-center mb-4 text-sm font-medium cursor-pointer hover:text-white" @click="isTicketModalVisible=false">Klik area gelap untuk tutup</div>
                        
                        <div id="ticketContent" class="shadow-2xl rounded-[1.5rem] overflow-hidden relative">
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
                                <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 mb-6 text-center" v-if="ticketData.isDispatched || ticketData.driverName !== 'Belum Ditentukan'">
                                    <div class="text-[10px] text-gray-400 uppercase font-bold mb-1">Armada & Driver</div>
                                    <div class="font-bold text-slate-800">{{ ticketData.fleetName }} ({{ ticketData.fleetPlate }})</div>
                                    <div class="text-xs text-slate-500">{{ ticketData.driverName }}</div>
                                </div>
                                <button @click="printTicket(ticketData)" class="w-full py-3 rounded-xl border-2 border-gray-100 text-gray-500 font-bold text-sm hover:bg-gray-50 hover:text-gray-800 transition-colors flex items-center justify-center gap-2"><i class="bi bi-printer"></i> Cetak Tiket</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Hidden Template for Auto-Printing -->
                <div id="ticketTemplate" class="fixed top-0 left-0 w-[380px] bg-white pointer-events-none opacity-0" style="z-index: -9999;">
                     <div v-if="ticketData" class="bg-white rounded-[1.5rem] overflow-hidden relative shadow-none border border-slate-100">
                            <div class="bg-gradient-to-br from-blue-800 to-blue-600 p-8 text-white relative overflow-hidden rounded-t-[1.5rem]">
                                <div class="absolute top-0 right-0 w-32 h-32 bg-white opacity-10 rounded-full -mr-10 -mt-10"></div>
                                <div class="flex justify-between items-start mb-8 relative z-10">
                                    <div>
                                        <div class="text-3xl font-bold tracking-tight">Sutan<span class="font-light">Raya</span></div>
                                        <div class="text-[10px] opacity-80 uppercase tracking-widest mt-1 font-bold">E-Ticket</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-[10px] opacity-60 uppercase font-bold">Booking ID</div>
                                        <div class="font-mono text-lg font-bold">#{{ ticketData?.id?.toString().slice(-6) }}</div>
                                    </div>
                                </div>
                                <div class="flex justify-between items-end relative z-10">
                                    <div>
                                        <div class="text-[10px] opacity-60 uppercase font-bold mb-1">Penumpang</div>
                                        <div class="text-xl font-bold truncate max-w-[160px]">{{ ticketData?.passengerName }}</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-[10px] opacity-60 uppercase font-bold mb-1">Waktu</div>
                                        <div class="text-xl font-bold">{{ ticketData?.time || 'Bus' }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-white p-8 pt-10 relative rounded-b-[1.5rem]">
                                <div class="absolute top-0 left-0 w-full h-4 -mt-2 bg-white" style="clip-path: polygon(0 0, 100% 0, 100% 100%, 0 100%, 0 0, 1rem 0.5rem, 2rem 0, 3rem 0.5rem, 4rem 0, 5rem 0.5rem, 6rem 0, 7rem 0.5rem, 8rem 0, 9rem 0.5rem, 10rem 0, 11rem 0.5rem, 12rem 0, 13rem 0.5rem, 14rem 0, 15rem 0.5rem, 16rem 0, 17rem 0.5rem, 18rem 0, 19rem 0.5rem, 20rem 0, 21rem 0.5rem, 22rem 0, 23rem 0.5rem, 24rem 0);"></div>
                                <div class="flex justify-between mb-5 border-b border-dashed border-gray-200 pb-5">
                                    <div><div class="text-xs text-gray-400 uppercase font-bold">Rute</div><div class="font-bold text-gray-800 text-sm">{{ ticketData?.routeId }}</div></div>
                                    <div class="text-right"><div class="text-xs text-gray-400 uppercase font-bold">Tanggal</div><div class="font-bold text-gray-800 text-sm">{{ formatDate(ticketData?.date) }}</div></div>
                                </div>
                                <div class="grid grid-cols-3 gap-2 mb-6">
                                    <div class="bg-gray-50 p-2 rounded text-center"><div class="text-[10px] text-gray-400 uppercase font-bold">Kursi</div><div class="font-bold text-gray-800 text-sm">{{ ticketData?.seatNumbers || 'UNIT' }}</div></div>
                                    <div class="bg-gray-50 p-2 rounded text-center"><div class="text-[10px] text-gray-400 uppercase font-bold">Layanan</div><div class="font-bold text-blue-600 text-sm">{{ ticketData?.serviceType }}</div></div>
                                    <div class="bg-gray-50 p-2 rounded text-center"><div class="text-[10px] text-gray-400 uppercase font-bold">Validasi</div><div class="font-bold text-sm" :class="ticketData?.validationStatus==='Valid'?'text-green-600':(ticketData?.validationStatus==='Menunggu Validasi'?'text-red-500':'text-gray-600')">{{ ticketData?.validationStatus === 'Valid' ? 'VALID' : 'WAIT' }}</div></div>
                                </div>
                                <div class="space-y-3 mb-6 bg-blue-50/50 p-4 rounded-xl border border-blue-100">
                                    <div class="flex gap-3"><i class="bi bi-geo-alt-fill text-blue-300"></i> <div><div class="text-[10px] text-gray-400 uppercase font-bold">Jemput</div><div class="text-xs font-bold text-gray-700">{{ ticketData?.pickupAddress || 'Sesuai Maps' }}</div></div></div>
                                    <div class="flex gap-3"><i class="bi bi-flag-fill text-blue-300"></i> <div><div class="text-[10px] text-gray-400 uppercase font-bold">Tujuan</div><div class="text-xs font-bold text-gray-700">{{ ticketData?.dropoffAddress || '-' }}</div></div></div>
                                </div>
                                 <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 mb-6 text-center" v-if="ticketData?.isDispatched || ticketData?.driverName !== 'Belum Ditentukan'">
                                    <div class="text-[10px] text-gray-400 uppercase font-bold mb-1">Armada & Driver</div>
                                    <div class="font-bold text-slate-800">{{ ticketData?.fleetName }} ({{ ticketData?.fleetPlate }})</div>
                                    <div class="text-xs text-slate-500">{{ ticketData?.driverName }}</div>
                                </div>
                            </div>
                     </div>
                </div>

                <!-- Manual Assignment Modal -->
                <div v-if="isManualAssignModalVisible" class="fixed inset-0 bg-black/80 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
                    <div class="bg-white w-full max-w-sm rounded-2xl shadow-2xl p-6 animate-fade-in">
                        <div class="text-center mb-6">
                            <h3 class="text-lg font-bold text-gray-900">Set Armada & Supir</h3>
                            <p class="text-xs text-gray-500">Untuk keperluan cetak tiket</p>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Armada</label>
                                <select v-model="manualAssignForm.fleetId" class="w-full border-gray-200 p-3 rounded-xl bg-white outline-none font-semibold text-gray-700 text-sm">
                                    <option value="" disabled>-- Pilih Armada --</option>
                                    <option v-for="f in fleet" :value="f.id">{{ f.name }} ({{f.plate}})</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Supir</label>
                                <select v-model="manualAssignForm.driverId" class="w-full border-gray-200 p-3 rounded-xl bg-white outline-none font-semibold text-gray-700 text-sm">
                                    <option value="" disabled>-- Pilih Supir --</option>
                                    <option v-for="d in drivers" :value="d.id">{{ d.name }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-8 flex gap-3">
                            <button @click="isManualAssignModalVisible=false" class="flex-1 bg-white border border-gray-200 hover:bg-gray-50 text-gray-600 py-3 rounded-xl font-bold text-sm">Batal</button>
                            <button @click="saveManualAssign" class="flex-1 bg-sr-blue hover:bg-slate-800 text-white py-3 rounded-xl font-bold shadow-lg text-sm">Lanjut Cetak</button>
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

                <div v-if="isDispatchModalVisible" class="fixed inset-0 bg-black/80 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
                    <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl p-6 animate-fade-in">
                        <div class="text-center mb-6">
                            <h3 class="text-xl font-bold text-gray-900">Dispatch Armada</h3>
                            <div v-if="dispatchForm.isLocked" class="bg-blue-50 text-blue-600 px-3 py-1 rounded-lg text-xs font-bold mt-2 inline-block">
                                <i class="bi bi-lock-fill mr-1"></i> {{ dispatchForm.assignmentReason }}
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Armada</label>
                                <select v-model="dispatchForm.fleetId" :disabled="dispatchForm.isLocked" class="w-full border-gray-200 p-3 rounded-xl bg-white outline-none font-semibold text-gray-700 transition-colors disabled:bg-gray-100 disabled:text-gray-500">
                                    <option value="" disabled>-- Pilih Mobil --</option>
                                    <option v-for="f in fleet.filter(x=>x.status==='Tersedia' || x.id === dispatchForm.fleetId)" :value="f.id">{{ f.name }} ({{f.plate}})</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Supir</label>
                                <select v-model="dispatchForm.driverId" :disabled="dispatchForm.isLocked" class="w-full border-gray-200 p-3 rounded-xl bg-white outline-none font-semibold text-gray-700 transition-colors disabled:bg-gray-100 disabled:text-gray-500">
                                    <option value="" disabled>-- Pilih Supir --</option>
                                    <option v-for="d in drivers.filter(x=>x.status==='Standby' || x.id === dispatchForm.driverId)" :value="d.id">{{ d.name }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-8 flex gap-3">
                            <button @click="isDispatchModalVisible=false" class="flex-1 bg-white border border-gray-200 hover:bg-gray-50 text-gray-600 py-3 rounded-xl font-bold">Batal</button>
                            <button @click="processDispatch" class="flex-1 bg-sr-blue hover:bg-slate-800 text-white py-3 rounded-xl font-bold shadow-lg">Proses</button>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>
    <script src="js/ticket_printer.js?v=<?= time() ?>"></script>
    <script src="app.js?v=<?= time() ?>"></script>
</body>
</html>