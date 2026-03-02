<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sutan Raya - Admin Bus Pariwisata</title>
    
    <!-- TailwindCSS & Libraries -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'bus-gold': '#d4af37', 
                        'bus-dark': '#0f172a', // Slate 900
                        'bus-black': '#000000',
                    }
                }
            }
        }
    </script>
    <style>
        [v-cloak] { display: none; }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 10px; }
        .date-card { transition: all 0.3s; }
        .date-card.active { background-color: #0f172a; color: #d4af37; border-color: #d4af37; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 h-screen w-screen overflow-hidden font-sans">
    <div id="app" class="flex h-full" v-cloak>
        
        <!-- Sidebar -->
        <aside class="w-64 bg-bus-dark text-white border-r border-slate-800 flex flex-col z-20 shadow-lg">
            <div class="p-6 border-b border-slate-700">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-bus-gold text-bus-dark flex items-center justify-center text-xl font-bold shadow-lg shadow-yellow-500/20">
                        <i class="bi bi-bus-front-fill"></i>
                    </div>
                    <div>
                        <h1 class="font-bold text-white text-lg tracking-wide">ADMIN BUS</h1>
                        <p class="text-xs text-slate-400 uppercase tracking-widest">Pariwisata</p>
                    </div>
                </div>
            </div>
            
            <nav class="flex-1 p-4 space-y-2">
                <button @click="view = 'dashboard'" :class="view === 'dashboard' ? 'bg-slate-800 text-bus-gold border-l-4 border-bus-gold' : 'text-slate-400 hover:bg-slate-800 hover:text-white'" class="w-full flex items-center gap-3 px-4 py-3 rounded-r-xl font-bold transition-all text-sm uppercase tracking-wider">
                    <i class="bi bi-calendar-range"></i> Jadwal & Booking
                </button>
                <button @click="view = 'fleet'" :class="view === 'fleet' ? 'bg-slate-800 text-bus-gold border-l-4 border-bus-gold' : 'text-slate-400 hover:bg-slate-800 hover:text-white'" class="w-full flex items-center gap-3 px-4 py-3 rounded-r-xl font-bold transition-all text-sm uppercase tracking-wider">
                    <i class="bi bi-truck-front"></i> Kelola Armada
                </button>
                <button @click="view = 'drivers'" :class="view === 'drivers' ? 'bg-slate-800 text-bus-gold border-l-4 border-bus-gold' : 'text-slate-400 hover:bg-slate-800 hover:text-white'" class="w-full flex items-center gap-3 px-4 py-3 rounded-r-xl font-bold transition-all text-sm uppercase tracking-wider">
                    <i class="bi bi-person-badge"></i> Kelola Supir
                </button>
            </nav>
            
            <div class="p-4 border-t border-slate-700 bg-slate-900/50">
                <button onclick="location.href='../display-v12/index.php'" class="w-full py-2 text-xs font-bold text-slate-500 hover:text-white transition-colors flex items-center justify-center gap-2">
                    <i class="bi bi-arrow-left"></i> KEMBALI KE TRAVEL
                </button>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col min-w-0 bg-slate-50 relative">
            
            <!-- View: Dashboard / Schedule -->
            <div v-if="view === 'dashboard'" class="h-full flex flex-col">
                <!-- Date Picker Header -->
                <header class="bg-white border-b border-slate-200 px-6 py-4 flex-shrink-0 z-10 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="text-2xl font-extrabold text-bus-dark">Jadwal Bus Pariwisata</h2>
                            <p class="text-xs text-slate-500 font-bold">Kelola keberangkatan dan ketersediaan unit</p>
                        </div>
                        <button @click="openBookingModal()" class="px-6 py-2.5 bg-bus-dark text-bus-gold font-bold rounded-xl shadow-lg hover:shadow-xl hover:scale-105 transition-all flex items-center gap-2 border border-slate-700">
                             <i class="bi bi-plus-lg"></i> BOOKING BARU
                        </button>
                    </div>
                    
                    <!-- Date Scroller -->
                    <div class="flex gap-2 overflow-x-auto custom-scrollbar pb-2">
                        <div v-for="d in dateRange" :key="d.date" @click="selectDate(d.date)" 
                             class="date-card w-16 flex-shrink-0 flex flex-col items-center justify-center p-2 rounded-xl border border-slate-200 cursor-pointer bg-white"
                             :class="{ 'active': selectedDate === d.date }">
                            <span class="text-[10px] font-bold uppercase tracking-wider opacity-70">{{ d.dayName.substr(0,3) }}</span>
                            <span class="text-xl font-extrabold">{{ d.day }}</span>
                        </div>
                    </div>
                </header>

                <div class="flex-1 flex overflow-hidden">
                    <!-- Column 1: Bookings List -->
                    <div class="w-1/2 p-6 overflow-y-auto custom-scrollbar border-r border-slate-200 bg-white">
                         <h3 class="font-bold text-slate-800 mb-4 flex items-center gap-2 text-sm uppercase tracking-widest border-b border-slate-100 pb-2">
                            <i class="bi bi-ticket-perforated"></i> Daftar Booking
                        </h3>
                        
                        <div v-if="bookings.length === 0" class="p-12 text-center bg-slate-50 rounded-2xl border border-slate-200 border-dashed">
                            <i class="bi bi-calendar-x text-4xl text-slate-300 mb-2 block"></i>
                            <p class="text-slate-400 font-bold">Tidak ada jadwal jalan.</p>
                        </div>
                        
                        <div v-for="b in bookings" :key="b.id" class="mb-4 bg-white p-0 rounded-2xl shadow-sm border border-slate-200 hover:shadow-md transition-all group overflow-hidden">
                            <div class="bg-slate-900 px-5 py-3 flex justify-between items-center">
                                <span class="font-mono text-xs font-bold text-bus-gold opacity-80">{{ b.bookingCode }}</span>
                                <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider" :class="b.status === 'Confirmed' ? 'bg-green-500 text-white' : 'bg-yellow-500 text-black'">{{ b.status }}</span>
                            </div>
                            <div class="p-5">
                                <div class="flex justify-between items-start mb-4">
                                    <div>
                                        <h4 class="font-bold text-lg text-slate-800">{{ b.customerName }}</h4>
                                        <div class="text-sm font-bold text-slate-500 flex items-center gap-2"><i class="bi bi-telephone-fill text-xs"></i> {{ b.customerPhone }}</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-[10px] text-slate-400 font-bold uppercase">Total Biaya</div>
                                        <div class="font-extrabold text-bus-dark text-lg">{{ formatRupiah(b.totalPrice) }}</div>
                                    </div>
                                </div>
                                
                                <div class="p-3 bg-slate-50 rounded-xl space-y-2 text-sm border border-slate-100 mb-4">
                                    <div class="flex gap-2">
                                        <i class="bi bi-geo-alt-fill text-bus-gold"></i>
                                        <span class="font-bold text-slate-700">{{ b.routeDescription || '-' }}</span>
                                    </div>
                                    <div class="flex gap-2">
                                        <i class="bi bi-calendar-event-fill text-slate-400"></i>
                                        <span class="text-slate-600">{{ formatDate(b.tripDateStart) }} <i class="bi bi-arrow-right text-xs"></i> {{ formatDate(b.tripDateEnd) }} ({{ b.durationDays }} Hari)</span>
                                    </div>
                                </div>
                                
                                <div class="flex items-center justify-between text-xs font-bold pt-2 border-t border-slate-100">
                                    <div v-if="b.fleetName" class="flex items-center gap-2 text-bus-dark bg-slate-100 px-3 py-1.5 rounded-lg">
                                        <i class="bi bi-bus-front-fill"></i> {{ b.fleetName }}
                                    </div>
                                    <div v-else class="text-red-500 flex items-center gap-2 bg-red-50 px-3 py-1.5 rounded-lg">
                                        <i class="bi bi-exclamation-circle-fill"></i> Belum ada Unit
                                    </div>
                                    
                                    <button class="text-slate-400 hover:text-bus-dark transition-colors flex items-center gap-1"><i class="bi bi-pencil-square"></i> DETAIL</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Column 2: Fleet Availability -->
                    <div class="w-1/2 p-6 overflow-y-auto custom-scrollbar bg-slate-50">
                        <h3 class="font-bold text-slate-800 mb-4 flex items-center gap-2 text-sm uppercase tracking-widest border-b border-slate-200 pb-2">
                            <i class="bi bi-check-circle-fill text-green-600"></i> Unit Tersedia
                        </h3>
                        
                        <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
                            <div v-for="f in availableFleet" :key="f.id" class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200 flex items-center gap-4 hover:border-bus-gold transition-colors cursor-pointer group">
                                <div class="w-12 h-12 rounded-full bg-slate-900 text-bus-gold flex items-center justify-center group-hover:bg-bus-gold group-hover:text-black transition-colors">
                                    <i class="bi bi-bus-front-fill text-xl"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-slate-800">{{ f.name }}</h4>
                                    <div class="text-xs font-bold font-mono text-slate-500 bg-slate-100 px-1.5 py-0.5 rounded w-fit mt-1">{{ f.plateNumber }}</div>
                                    <div class="text-xs text-slate-400 mt-1">{{ f.capacity }} Seats</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
             <!-- Modal Booking -->
            <div v-if="isBookingModalVisible" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4 animate-fade-in">
                <div class="bg-white w-full max-w-2xl rounded-3xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
                    <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-900 text-white">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-bus-gold text-black flex items-center justify-center"><i class="bi bi-plus-lg font-bold"></i></div>
                            <h3 class="font-extrabold text-xl tracking-tight">BOOKING BARU</h3>
                        </div>
                        <button @click="isBookingModalVisible = false" class="w-8 h-8 rounded-full bg-white/10 text-white hover:bg-white/20 flex items-center justify-center transition-colors"><i class="bi bi-x-lg"></i></button>
                    </div>
                    <div class="p-8 overflow-y-auto custom-scrollbar bg-white">
                        <div class="grid grid-cols-2 gap-6">
                            <!-- Customer Info -->
                            <div class="col-span-2 space-y-4">
                                <h4 class="text-xs font-extrabold text-bus-dark uppercase tracking-widest mb-2 flex items-center gap-2"><i class="bi bi-person-fill text-bus-gold"></i> Data Pelanggan</h4>
                                <div class="grid grid-cols-2 gap-4">
                                    <div><label class="text-xs font-bold text-slate-500 block mb-1">Nama Pemesan</label><input v-model="bookingForm.customerName" class="w-full p-3 border border-slate-300 rounded-xl text-sm font-bold focus:border-bus-gold focus:ring-1 focus:ring-bus-gold outline-none transition-all"></div>
                                    <div><label class="text-xs font-bold text-slate-500 block mb-1">No WhatsApp</label><input v-model="bookingForm.customerPhone" class="w-full p-3 border border-slate-300 rounded-xl text-sm font-bold focus:border-bus-gold focus:ring-1 focus:ring-bus-gold outline-none transition-all"></div>
                                </div>
                            </div>
                             
                            <!-- Travel Details -->
                            <div class="col-span-2 border-t border-slate-100 pt-6 space-y-4">
                                <h4 class="text-xs font-extrabold text-bus-dark uppercase tracking-widest mb-2 flex items-center gap-2"><i class="bi bi-map-fill text-bus-gold"></i> Detail Perjalanan</h4>
                                <div class="grid grid-cols-3 gap-4">
                                    <div class="col-span-2"><label class="text-xs font-bold text-slate-500 block mb-1">Tanggal Mulai</label><input type="date" v-model="bookingForm.tripDateStart" class="w-full p-3 border border-slate-300 rounded-xl text-sm font-bold focus:border-bus-gold focus:ring-1 focus:ring-bus-gold outline-none transition-all"></div>
                                    <div><label class="text-xs font-bold text-slate-500 block mb-1">Durasi (Hari)</label><input type="number" v-model="bookingForm.durationDays" class="w-full p-3 border border-slate-300 rounded-xl text-sm font-bold focus:border-bus-gold focus:ring-1 focus:ring-bus-gold outline-none transition-all"></div>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                     <div><label class="text-xs font-bold text-slate-500 block mb-1">Lokasi Jemput</label><textarea v-model="bookingForm.pickupLocation" class="w-full p-3 border border-slate-300 rounded-xl text-sm font-bold h-20 focus:border-bus-gold outline-none"></textarea></div>
                                     <div><label class="text-xs font-bold text-slate-500 block mb-1">Tujuan / Rute</label><textarea v-model="bookingForm.routeDescription" class="w-full p-3 border border-slate-300 rounded-xl text-sm font-bold h-20 focus:border-bus-gold outline-none" placeholder="Contoh: Bukittinggi - Painan PP"></textarea></div>
                                </div>
                            </div>
                            
                            <!-- Unit Assignment -->
                            <div class="col-span-2 border-t border-slate-100 pt-6 space-y-4">
                                <h4 class="text-xs font-extrabold text-bus-dark uppercase tracking-widest mb-2 flex items-center gap-2"><i class="bi bi-truck-front-fill text-bus-gold"></i> Penugasan Unit</h4>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="text-xs font-bold text-slate-500 block mb-1">Pilih Bus</label>
                                        <select v-model="bookingForm.fleetId" class="w-full p-3 border border-slate-300 rounded-xl text-sm font-bold bg-white focus:border-bus-gold outline-none">
                                            <option value="">-- Pilih Armada --</option>
                                            <option v-for="f in availableFleet" :key="f.id" :value="f.id">{{ f.name }} ({{ f.capacity }} Seat)</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="text-xs font-bold text-slate-500 block mb-1">Pilih Supir</label>
                                        <select v-model="bookingForm.driverId" class="w-full p-3 border border-slate-300 rounded-xl text-sm font-bold bg-white focus:border-bus-gold outline-none">
                                            <option value="">-- Pilih Supir --</option>
                                            <option v-for="d in drivers" :key="d.id" :value="d.id">{{ d.name }}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Payment -->
                            <div class="col-span-2 border-t border-slate-100 pt-6 space-y-4">
                                <h4 class="text-xs font-extrabold text-bus-dark uppercase tracking-widest mb-2 flex items-center gap-2"><i class="bi bi-wallet-fill text-bus-gold"></i> Pembayaran</h4>
                                <div class="grid grid-cols-2 gap-4">
                                    <div><label class="text-xs font-bold text-slate-500 block mb-1">Total Harga</label><input type="number" v-model="bookingForm.totalPrice" class="w-full p-3 border border-slate-300 rounded-xl text-sm font-extrabold text-bus-dark bg-slate-50 focus:border-bus-gold focus:bg-white outline-none transition-all"></div>
                                    <div><label class="text-xs font-bold text-slate-500 block mb-1">Jumlah DP</label><input type="number" v-model="bookingForm.dpAmount" class="w-full p-3 border border-slate-300 rounded-xl text-sm font-extrabold text-bus-dark bg-slate-50 focus:border-bus-gold focus:bg-white outline-none transition-all"></div>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-4">
                                     <div>
                                         <label class="text-xs font-bold text-slate-500 block mb-1">Metode Bayar</label>
                                         <select v-model="bookingForm.paymentMethod" class="w-full p-3 border border-slate-300 rounded-xl text-sm font-bold bg-white focus:border-bus-gold outline-none">
                                             <option value="Cash">Cash (Tunai)</option>
                                             <option value="Transfer">Transfer Bank</option>
                                         </select>
                                     </div>
                                     <div>
                                         <label class="text-xs font-bold text-slate-500 block mb-1">Status Bayar</label>
                                         <select v-model="bookingForm.paymentStatus" class="w-full p-3 border border-slate-300 rounded-xl text-sm font-bold bg-white focus:border-bus-gold outline-none">
                                             <option value="Belum Lunas">Belum Lunas</option>
                                             <option value="Lunas">Lunas</option>
                                         </select>
                                     </div>
                                </div>
                                
                                <!-- Detailed Payment Info (Visible if Cash or Transfer) -->
                                <div class="grid grid-cols-2 gap-4 animate-fade-in bg-yellow-50 p-4 rounded-xl border border-yellow-100">
                                     <div>
                                        <label class="text-xs font-bold text-slate-500 block mb-1">Diterima Dimana / Via?</label>
                                        <input type="text" v-model="bookingForm.paymentLocation" class="w-full p-2.5 border border-slate-200 rounded-lg text-sm font-bold" placeholder="Contoh: Kantor Padang / BCA">
                                     </div>
                                     <div>
                                        <label class="text-xs font-bold text-slate-500 block mb-1">Diterima Oleh Siapa?</label>
                                        <input type="text" v-model="bookingForm.paymentReceiver" class="w-full p-2.5 border border-slate-200 rounded-lg text-sm font-bold" placeholder="Nama Admin / Staff">
                                     </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="p-5 border-t border-slate-100 bg-slate-50 flex justify-end gap-3 z-10">
                        <button @click="isBookingModalVisible = false" class="px-6 py-3 rounded-xl font-bold text-slate-500 hover:bg-slate-200 transaction-all uppercase tracking-wider text-sm">Batal</button>
                        <button @click="saveBooking" class="px-8 py-3 rounded-xl font-bold bg-bus-dark text-bus-gold shadow-lg hover:shadow-xl hover:bg-black transaction-all uppercase tracking-wider text-sm flex items-center gap-2">
                            <i class="bi bi-check-lg"></i> Simpan
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- View: Fleet Management -->
            <div v-if="view === 'fleet'" class="p-8 h-full overflow-y-auto">
                <div class="max-w-5xl mx-auto">
                    <div class="flex justify-between items-center mb-8">
                        <div>
                            <h2 class="text-2xl font-extrabold text-bus-dark">Kelola Armada</h2>
                            <p class="text-xs text-slate-500 font-bold">Daftar Bus & Status Ketersediaan</p>
                        </div>
                        <button @click="openFleetModal()" class="px-6 py-2.5 bg-bus-dark text-bus-gold font-bold rounded-xl shadow-lg hover:scale-105 transition-all flex items-center gap-2 border border-slate-700">
                            <i class="bi bi-plus-lg"></i> Tambah Armada
                        </button>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                        <div v-for="f in fleet" :key="f.id" class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden group hover:shadow-lg transition-all">
                           <div class="h-40 bg-slate-900 relative flex items-center justify-center">
                               <div class="w-16 h-16 rounded-full bg-bus-gold flex items-center justify-center text-3xl shadow-lg shadow-yellow-500/50">
                                   <i class="bi bi-bus-front-fill text-bus-dark"></i>
                               </div>
                               <div class="absolute top-3 right-3">
                                   <span class="px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wider" :class="f.status === 'Tersedia' ? 'bg-green-500 text-white' : (f.status === 'Jalan' ? 'bg-yellow-500 text-black' : 'bg-red-500 text-white')">{{ f.status }}</span>
                               </div>
                           </div>
                           <div class="p-6">
                               <h3 class="font-bold text-xl text-slate-800 mb-1">{{ f.name }}</h3>
                               <p class="font-mono text-xs font-bold text-slate-400 mb-4 bg-slate-100 w-fit px-2 py-1 rounded">{{ f.plateNumber }}</p>
                               
                               <div class="grid grid-cols-2 gap-4 mb-4 border-b border-slate-100 pb-4">
                                   <div>
                                       <span class="text-[10px] uppercase text-slate-400 font-bold block">Kapasitas</span>
                                       <span class="font-bold text-slate-700">{{ f.capacity }} Seat</span>
                                   </div>
                                    <div>
                                       <span class="text-[10px] uppercase text-slate-400 font-bold block">Harga / Hari</span>
                                       <span class="font-bold text-bus-primary">{{ formatRupiah(f.pricePerDay) }}</span>
                                   </div>
                               </div>
                               
                               <div class="flex justify-end items-center gap-2">
                                    <button @click="openFleetModal(f)" class="px-4 py-2 rounded-lg bg-slate-50 text-slate-600 font-bold text-xs hover:bg-slate-100 transition-colors">Edit</button>
                                   <button @click="deleteFleet(f.id)" class="px-4 py-2 rounded-lg bg-red-50 text-red-600 font-bold text-xs hover:bg-red-100 transition-colors">Hapus</button>
                                </div>
                           </div>
                        </div>
                    </div>
                </div>
            </div>
            
             <!-- View: Drivers Management -->
            <div v-if="view === 'drivers'" class="p-8 h-full overflow-y-auto">
                <div class="max-w-5xl mx-auto">
                    <div class="flex justify-between items-center mb-8">
                        <div>
                            <h2 class="text-2xl font-extrabold text-bus-dark">Kelola Supir</h2>
                            <p class="text-xs text-slate-500 font-bold">Data Crew & Driver</p>
                        </div>
                        <button @click="openDriverModal()" class="px-6 py-2.5 bg-bus-dark text-bus-gold font-bold rounded-xl shadow-lg hover:scale-105 transition-all flex items-center gap-2 border border-slate-700">
                            <i class="bi bi-plus-lg"></i> Tambah Supir
                        </button>
                    </div>
                    
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
                        <table class="w-full text-left">
                            <thead class="bg-slate-50 border-b border-slate-200">
                                <tr>
                                    <th class="p-6 text-xs font-extrabold text-slate-400 uppercase tracking-widest">Nama Lengkap</th>
                                    <th class="p-6 text-xs font-extrabold text-slate-400 uppercase tracking-widest">No. HP</th>
                                    <th class="p-6 text-xs font-extrabold text-slate-400 uppercase tracking-widest">Status</th>
                                    <th class="p-6 text-xs font-extrabold text-slate-400 uppercase tracking-widest text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="d in drivers" :key="d.id" class="hover:bg-slate-50 transition-colors group">
                                    <td class="p-6 font-bold text-slate-800 flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center text-slate-500 font-bold text-xs">{{ d.name.charAt(0) }}</div>
                                        {{ d.name }}
                                    </td>
                                    <td class="p-6 text-sm font-bold text-slate-500 font-mono">{{ d.phone }}</td>
                                    <td class="p-6"><span class="px-3 py-1 rounded text-[10px] font-bold uppercase tracking-wider" :class="d.status === 'Standby' ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500'">{{ d.status }}</span></td>
                                    <td class="p-6 text-right space-x-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button @click="openDriverModal(d)" class="text-blue-600 hover:text-blue-800 text-xs font-bold uppercase tracking-wider">Edit</button>
                                        <button @click="deleteDriver(d.id)" class="text-red-600 hover:text-red-800 text-xs font-bold uppercase tracking-wider">Hapus</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </main>
    </div>
    
    <script type="module" src="js/app.js"></script>
</body>
</html>
