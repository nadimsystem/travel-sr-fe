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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
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
        window.initialView = 'bookingManagement';
    </script>
</head>
<body class="h-screen w-screen bg-slate-50 text-slate-800 dark:bg-slate-900 dark:text-slate-100">

    <div id="app" class="flex h-full w-full" v-cloak>
        
        <?php $currentPage = 'booking_management'; include 'components/sidebar.php'; ?>

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
                

                <div v-if="view === 'bookingManagement'" class="absolute inset-0 flex flex-col bg-white dark:bg-slate-800 animate-fade-in">
                    <div class="p-4 border-b border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 sticky top-0 z-20 flex-shrink-0">
                        <div class="flex justify-between items-center mb-4">
                            <div><h2 class="text-lg font-bold text-slate-800 dark:text-white">Validasi & Cetak Tiket.</h2></div>
                            <div class="flex gap-2">
                                <button @click="bookingManagementTab='travel'" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-colors" :class="bookingManagementTab==='travel'?'bg-sr-blue text-white':'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300'">Travel</button>
                                <button @click="bookingManagementTab='bus'" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-colors" :class="bookingManagementTab==='bus'?'bg-sr-blue text-white':'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300'">Bus</button>
                            </div>
                        </div>
                        <div class="flex justify-between gap-4">
                            <div class="relative flex-1"><i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i><input type="text" v-model="busSearchTerm" placeholder="Cari..." class="w-full pl-9 pr-3 py-1.5 text-sm border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 outline-none"></div>
                            
                            <a href="edit_booking.php" class="flex items-center gap-2 px-3 py-1.5 bg-yellow-100 hover:bg-yellow-200 text-yellow-800 rounded-lg text-xs font-bold transition-colors">
                                <i class="bi bi-pencil-square"></i> Edit Booking
                            </a>

                            <div v-if="bookingManagementTab==='bus'" class="flex bg-slate-100 dark:bg-slate-700 p-0.5 rounded-lg">
                                <button @click="busViewMode='list'" class="px-3 py-1 rounded text-xs font-bold" :class="busViewMode==='list'?'bg-white dark:bg-slate-600 text-blue-700 dark:text-blue-300 shadow':'text-slate-500 dark:text-slate-400'">List</button>
                                <button @click="busViewMode='calendar'" class="px-3 py-1 rounded text-xs font-bold" :class="busViewMode==='calendar'?'bg-white dark:bg-slate-600 text-blue-700 dark:text-blue-300 shadow':'text-slate-500 dark:text-slate-400'">Kalender</button>
                            </div>
                        </div>
                    </div>

                    <!-- Filter Toolbar -->
                    <div class="px-4 py-2 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex flex-wrap gap-2 items-center">
                        <select v-model="filterMethod" class="text-xs border border-slate-300 dark:border-slate-600 rounded px-2 py-1.5 bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-1 focus:ring-blue-500">
                            <option value="All">Semua Metode</option>
                            <option value="Cash">Cash</option>
                            <option value="Transfer">Transfer</option>
                            <option value="DP">DP</option>
                            <option value="Belum Bayar">Belum Bayar</option>
                        </select>

                        <select v-model="filterCategory" class="text-xs border border-slate-300 dark:border-slate-600 rounded px-2 py-1.5 bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-1 focus:ring-blue-500">
                            <option value="">Semua Kategori</option>
                            <option value="Umum">Umum</option>
                            <option value="Pelajar">Pelajar</option>
                        </select>

                        <select v-model="filterSort" class="text-xs border border-slate-300 dark:border-slate-600 rounded px-2 py-1.5 bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-1 focus:ring-blue-500">
                            <option value="Newest">Terbaru</option>
                            <option value="Oldest">Terlama</option>
                        </select>

                        <input type="date" v-model="filterDate" class="text-xs border border-slate-300 dark:border-slate-600 rounded px-2 py-1.5 bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-1 focus:ring-blue-500">

                        <select v-model="filterRoute" class="text-xs border border-slate-300 dark:border-slate-600 rounded px-2 py-1.5 bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-1 focus:ring-blue-500 max-w-[150px]">
                            <option value="All">Semua Rute</option>
                            <option v-for="r in uniqueRoutes" :key="r" :value="r">{{ r }}</option>
                        </select>

                        <button @click="filterMethod='All';filterSort='Newest';filterDate='';filterRoute='All';filterCategory=''" class="text-xs font-bold text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 px-2 py-1.5 transition-colors" title="Reset Filter">
                            <i class="bi bi-arrow-counterclockwise"></i> Reset
                        </button>
                    </div>
                    
                    <div class="flex-1 overflow-y-auto custom-scrollbar relative">
                        <table v-if="bookingManagementTab === 'travel' || busViewMode === 'list'" class="w-full text-left text-sm">
                            <thead class="bg-slate-50 dark:bg-slate-700 text-slate-500 dark:text-slate-300 uppercase text-[10px] sticky top-0 z-10 font-bold">
                                <tr>
                                    <th class="p-4 w-10 text-center">No</th>
                                    <th class="p-4">Waktu</th>
                                    <th class="p-4">Penumpang</th>
                                    <th class="p-4">Kursi</th>
                                    <th class="p-4">Penyemputan & Tujuan</th>
                                    <th class="p-4">Pembayaran Detail</th>
                                    <th class="p-4">Total</th>
                                    <th class="p-4">Sisa Bayar</th>
                                    <th class="p-4">Status</th>
                                    <th class="p-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                <tr v-for="(b, index) in paginatedBookings" :key="b.id" @click="openDetailModal(b)" class="hover:bg-blue-50/30 dark:hover:bg-slate-700/50 transition-colors cursor-pointer group">
                                    <td class="p-4 text-center text-xs font-bold text-slate-400">{{ (currentPage-1)*itemsPerPage + index + 1 }}</td>
                                    <td class="p-4"><div class="font-bold text-slate-800 dark:text-white">{{ formatDate(b.date) }}</div><div class="text-xs text-slate-500 dark:text-slate-400">{{ b.time || b.duration + ' Hari' }}</div></td>
                                    <td class="p-4">
                                        <div class="font-bold text-slate-800 dark:text-white">{{ b.passengerName }}</div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400">{{ b.passengerPhone }}</div>
                                        <span v-if="b.passengerType === 'Pelajar' || b.passengerType === 'Mahasiswa / Pelajar'" class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-yellow-100 text-yellow-700 uppercase mt-1 inline-block">Pelajar</span>
                                    </td>
                                    <td class="p-4">
                                        <div class="font-bold text-lg text-slate-700 dark:text-slate-300">{{ b.seatNumbers || '-' }}</div>
                                        <div class="text-[10px] text-slate-400 uppercase">{{ b.seatCount }} Orang</div>
                                    </td>

                                    
                                    <td class="p-4 max-w-[200px]">
                                        <div class="flex flex-col gap-1.5">
                                            <div class="flex gap-1.5 items-start">
                                                 <i class="bi bi-geo-alt-fill text-slate-400 text-xs mt-0.5"></i>
                                                  <div>
                                                      <div class="text-[10px] font-bold text-slate-500 uppercase">Jemput</div>
                                                      <div class="text-xs text-slate-700 dark:text-slate-300 leading-tight line-clamp-2" :title="b.pickupAddress">
                                                         {{ parseAddress(b.pickupAddress).text }}
                                                         <a v-if="parseAddress(b.pickupAddress).link" :href="parseAddress(b.pickupAddress).link" target="_blank" class="text-blue-500 hover:text-blue-700 ml-1" @click.stop>
                                                             <i class="bi bi-geo-alt-fill"></i>
                                                         </a>
                                                      </div>
                                                  </div>
                                            </div>
                                            <div class="flex gap-1.5 items-start">
                                                 <i class="bi bi-flag-fill text-slate-400 text-xs mt-0.5"></i>
                                                  <div>
                                                      <div class="text-[10px] font-bold text-slate-500 uppercase">Antar</div>
                                                      <div class="text-xs text-slate-700 dark:text-slate-300 leading-tight line-clamp-2" :title="b.dropoffAddress">
                                                         {{ parseAddress(b.dropoffAddress).text }}
                                                         <a v-if="parseAddress(b.dropoffAddress).link" :href="parseAddress(b.dropoffAddress).link" target="_blank" class="text-blue-500 hover:text-blue-700 ml-1" @click.stop>
                                                             <i class="bi bi-flag-fill"></i>
                                                         </a>
                                                      </div>
                                                  </div>
                                            </div>
                                            <div class="mt-1 pt-1 border-t border-dashed border-slate-200">
                                                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-slate-100 text-slate-500 uppercase">{{ b.serviceType }}</span>
                                                <span class="text-[9px] text-slate-400 ml-1">{{ b.routeId }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <div class="flex flex-col gap-1">
                                            <!-- Payment Method Badge -->
                                            <span v-if="b.paymentMethod === 'Cash'" class="text-[10px] font-bold px-2 py-0.5 rounded bg-emerald-100 dark:bg-emerald-900 text-emerald-700 dark:text-emerald-400 uppercase flex w-fit items-center gap-1">
                                                <i class="bi bi-cash"></i> Cash
                                            </span>
                                            <span v-else-if="b.paymentMethod === 'Transfer'" class="text-[10px] font-bold px-2 py-0.5 rounded bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-400 uppercase flex w-fit items-center gap-1">
                                                <i class="bi bi-bank"></i> Transfer
                                                <button v-if="b.paymentProof" @click.stop="viewProof(b.paymentProof)" class="ml-1 hover:bg-blue-200 rounded px-1 transition-colors" title="Lihat Bukti">
                                                    <i class="bi bi-image-fill text-blue-600 text-xs"></i>
                                                </button>
                                            </span>
                                            <span v-else-if="b.paymentMethod === 'DP'" class="text-[10px] font-bold px-2 py-0.5 rounded bg-yellow-100 dark:bg-yellow-900 text-yellow-700 dark:text-yellow-400 uppercase flex w-fit items-center gap-1">
                                                <i class="bi bi-hourglass-split"></i> DP
                                                <button v-if="b.paymentProof" @click.stop="viewProof(b.paymentProof)" class="ml-1 hover:bg-yellow-200 rounded px-1 transition-colors" title="Lihat Bukti DP">
                                                    <i class="bi bi-image-fill text-yellow-700 text-xs"></i>
                                                </button>
                                            </span>
                                            <span v-else class="text-[10px] font-bold px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400 uppercase">{{ b.paymentMethod || '-' }}</span>
                                            
                                            <!-- Payment Details: Location & Receiver for Cash -->
                                            <div v-if="b.paymentMethod === 'Cash' && (b.paymentLocation || b.paymentReceiver)" class="text-[9px] text-slate-600 dark:text-slate-400 space-y-0.5">
                                                <div v-if="b.paymentLocation"><i class="bi bi-geo-alt-fill text-emerald-600"></i> {{ b.paymentLocation }}</div>
                                                <div v-if="b.paymentReceiver"><i class="bi bi-person-fill text-emerald-600"></i> {{ b.paymentReceiver }}</div>
                                            </div>
                                            
                                            <!-- DP Amount Display -->
                                            <div v-if="b.paymentMethod === 'DP' && b.downPaymentAmount > 0" class="text-[9px] text-slate-600 dark:text-slate-400">
                                                <i class="bi bi-cash-coin"></i> DP: Rp {{ (b.downPaymentAmount||0).toLocaleString('id-ID') }}
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <td class="p-4">
                                        <div class="font-bold text-slate-800 dark:text-white">Rp {{ ((b.totalPrice * b.seatCount) || 0).toLocaleString('id-ID') }}</div>
                                        <div class="text-[9px] text-slate-400 mt-0.5">{{ b.seatCount }} x Rp {{ (b.totalPrice||0).toLocaleString('id-ID') }}</div>
                                    </td>
                                    <td class="p-4">
                                        <div v-if="b.validationStatus !== 'Valid' && (b.totalPrice * b.seatCount) > (b.downPaymentAmount || 0)" class="font-bold text-red-600">
                                            Rp {{ (((b.totalPrice * b.seatCount) - (b.downPaymentAmount || 0)) || 0).toLocaleString('id-ID') }}
                                        </div>
                                        <div v-else class="text-xs text-green-600 font-bold">
                                            <i class="bi bi-check-circle-fill"></i> Lunas
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <span v-if="b.validationStatus === 'Valid'" class="text-xs font-bold text-green-600 bg-green-100 px-2 py-1 rounded">Lunas</span>
                                        <span v-else-if="b.paymentStatus === 'DP'" class="text-xs font-bold text-yellow-600 bg-yellow-100 px-2 py-1 rounded">DP</span>
                                        <span v-else-if="b.paymentStatus === 'Belum Bayar'" class="text-xs font-bold text-red-600 bg-red-100 px-2 py-1 rounded">Belum Bayar</span>
                                        <span v-else class="text-xs font-bold text-blue-500 bg-blue-100 px-2 py-1 rounded">Validasi</span>
                                    </td>
                                    <td class="p-4 text-right space-x-1" @click.stop>
                                        <button @click="printTicket(b)" class="text-xs font-bold bg-slate-100 dark:bg-slate-600 text-slate-600 dark:text-slate-300 px-2 py-1.5 rounded hover:bg-slate-200 dark:hover:bg-slate-500 transition-colors" title="Cetak Tiket"><i class="bi bi-printer"></i></button>
                                        <button v-if="b.validationStatus !== 'Valid' && b.paymentStatus !== 'Belum Bayar'" @click="validatePaymentModal(b)" class="p-2 bg-blue-50 text-blue-600 hover:bg-blue-100 rounded-lg transition-colors" title="Validasi Pembayaran">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                        <button v-if="b.paymentStatus === 'Belum Bayar' || b.paymentStatus === 'DP'" @click="openAddPaymentModal(b)" class="p-2 bg-green-50 text-green-600 hover:bg-green-100 rounded-lg transition-colors" title="Tambah Pembayaran">
                                            <i class="bi bi-cash"></i>
                                        </button>
                                        <button v-if="(b.passengerType === 'Pelajar' || b.passengerType === 'Mahasiswa / Pelajar') && b.ktmProof" @click="viewKtm(b)" class="text-xs font-bold bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-300 px-2 py-1.5 rounded hover:bg-blue-200 dark:hover:bg-blue-900/50 transition-colors" title="Cek KTM"><i class="bi bi-card-image"></i> KTM</button>

                                        <button v-if="b.status === 'Pending' && b.serviceType !== 'Bus Pariwisata'" @click="changeView('dispatcher')" class="text-xs font-bold bg-blue-600 text-white px-3 py-1.5 rounded-lg hover:bg-blue-700 shadow-sm transition-transform active:scale-95">🚀 Dispatch</button>
                                        <button v-else-if="['Assigned','On Trip','Tiba'].includes(b.status)" @click="changeView('dashboard')" class="text-xs font-bold bg-green-100 text-green-700 px-3 py-1.5 rounded-lg hover:bg-green-200 shadow-sm transition-colors">📍 Lacak</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        
                        <!-- Pagination Controls -->
                        <div v-if="bookingManagementTab === 'travel' || busViewMode === 'list'" class="fixed bottom-0 right-0 z-30 p-4 border-t border-gray-200 dark:border-slate-700 bg-white/90 dark:bg-slate-800/90 backdrop-blur-sm flex justify-between items-center shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.1)] transition-all duration-300" :class="isSidebarOpen ? 'w-full md:w-[calc(100%-16rem)]' : 'w-full'">
                             <div class="text-xs text-slate-500 dark:text-slate-400 font-medium">
                                 Menampilkan {{ paginatedBookings.length }} dari {{ getFilteredBookings.length }} data <span class="hidden sm:inline">(Halaman {{ currentPage }} dari {{ totalPages }})</span>
                             </div>
                             <div class="flex gap-2">
                                 <select v-model.number="itemsPerPage" class="bg-white dark:bg-slate-700 border border-gray-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 text-xs font-bold rounded-lg px-2 py-1.5 focus:outline-none focus:ring-1 focus:ring-blue-500">
                                     <option value="10">10 Data</option>
                                     <option value="20">20 Data</option>
                                     <option value="30">30 Data</option>
                                     <option value="50">50 Data</option>
                                     <option value="100">100 Data</option>
                                 </select>
                                 <div class="w-px h-4 bg-gray-300 dark:bg-slate-600 hidden sm:block"></div>
                                 <button @click="currentPage--" :disabled="currentPage === 1" class="px-3 py-1.5 rounded-lg border bg-white dark:bg-slate-700 text-xs font-bold disabled:opacity-50 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-600 shadow-sm transition-colors">Prev</button>
                                 <span class="px-3 py-1.5 text-xs font-bold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-900 rounded-lg min-w-[3rem] text-center">{{ currentPage }} / {{ totalPages }}</span>
                                 <button @click="currentPage++" :disabled="currentPage >= totalPages" class="px-3 py-1.5 rounded-lg border bg-white dark:bg-slate-700 text-xs font-bold disabled:opacity-50 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-600 shadow-sm transition-colors">Next</button>
                             </div>
                        </div>
                        <!-- Spacer to prevent content being hidden behind fixed pagination -->
                        <div class="h-20"></div>

                        <div v-if="getFilteredBookings.length === 0" class="p-8 text-center text-slate-400 italic">Tidak ada data.</div>

                        <!-- KTM View Modal -->
                        <div v-if="isKtmModalVisible" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm" @click.self="isKtmModalVisible = false">
                            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden animate-fade-in relative">
                                <button @click="isKtmModalVisible = false" class="absolute top-4 right-4 bg-black/50 text-white w-8 h-8 rounded-full flex items-center justify-center hover:bg-black/70 transition-colors"><i class="bi bi-x-lg"></i></button>
                                <div class="p-1 bg-slate-100 dark:bg-slate-700">
                                    <img :src="activeKtmImage" class="w-full h-auto rounded-xl shadow-inner">
                                </div>
                                <div class="p-4 text-center">
                                    <h3 class="font-bold text-slate-800 dark:text-white">Bukti KTM / Kartu Pelajar</h3>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ activeBookingName }}</p>
                                </div>
                            </div>
                        </div>

                        <div v-if="bookingManagementTab === 'bus' && busViewMode === 'calendar'" class="p-4 h-full flex flex-col">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="font-bold text-lg text-slate-800 dark:text-white">{{ getMonthName(calendarMonth) }} {{ calendarYear }}</h3>
                                <div class="flex gap-2">
                                    <button @click="changeMonth(-1)" class="w-8 h-8 rounded-full hover:bg-slate-100 dark:hover:bg-slate-700 flex items-center justify-center dark:text-white"><i class="bi bi-chevron-left"></i></button>
                                    <button @click="changeMonth(1)" class="w-8 h-8 rounded-full hover:bg-slate-100 dark:hover:bg-slate-700 flex items-center justify-center dark:text-white"><i class="bi bi-chevron-right"></i></button>
                                </div>
                            </div>
                            <div class="grid grid-cols-7 text-center text-xs font-bold text-slate-400 uppercase mb-2"><div>Sen</div><div>Sel</div><div>Rab</div><div>Kam</div><div>Jum</div><div>Sab</div><div>Min</div></div>
                            <div class="calendar-grid flex-1 rounded-lg overflow-hidden bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                                <div v-for="(day, idx) in calendarDays" :key="idx" class="calendar-day dark:bg-slate-800 dark:border-slate-700" :class="{'other-month': !day.isCurrentMonth}">
                                    <div class="text-right text-xs font-bold mb-1" :class="day.isToday ? 'text-blue-600' : 'text-slate-400 dark:text-slate-500'">{{ day.date }}</div>
                                    <div class="flex-1 overflow-y-auto custom-scrollbar space-y-1">
                                        <div v-for="evt in day.events" :key="evt.id" @click="validatePaymentModal(evt)" class="calendar-event text-[9px] px-1 py-0.5 rounded border truncate" :class="evt.paymentStatus==='Lunas'?'bg-green-50 border-green-500 text-green-700':'bg-yellow-50 border-yellow-500 text-yellow-700'">{{ evt.passengerName }}</div>
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
                                <div class="bg-blue-600 text-white p-3 rounded-xl mb-6 flex justify-between items-center shadow-lg shadow-blue-200">
                                    <div class="text-[10px] uppercase font-bold opacity-80">Total Bayar</div>
                                    <div class="font-black text-lg">{{ ticketData.formattedPrice }}</div>
                                </div>
                                  <div class="space-y-3 mb-6 bg-blue-50/50 p-4 rounded-xl border border-blue-100">
                                     <div class="flex gap-3"><i class="bi bi-geo-alt-fill text-blue-300"></i> <div><div class="text-[10px] text-gray-400 uppercase font-bold">Jemput</div><div class="text-xs font-bold text-gray-700">
                                         {{ parseAddress(ticketData.pickupAddress).text }}
                                         <a v-if="parseAddress(ticketData.pickupAddress).link" :href="parseAddress(ticketData.pickupAddress).link" target="_blank" class="text-blue-500 hover:text-blue-700 ml-1">MAPS</a>
                                     </div></div></div>
                                     <div class="flex gap-3"><i class="bi bi-flag-fill text-blue-300"></i> <div><div class="text-[10px] text-gray-400 uppercase font-bold">Tujuan</div><div class="text-xs font-bold text-gray-700">
                                         {{ parseAddress(ticketData.dropoffAddress).text }}
                                         <a v-if="parseAddress(ticketData.dropoffAddress).link" :href="parseAddress(ticketData.dropoffAddress).link" target="_blank" class="text-blue-500 hover:text-blue-700 ml-1">MAPS</a>
                                     </div></div></div>
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
                                <div class="bg-blue-600 text-white p-3 rounded-xl mb-6 flex justify-between items-center shadow-lg shadow-blue-200">
                                    <div class="text-[10px] uppercase font-bold opacity-80">Total Bayar</div>
                                    <div class="font-black text-lg">{{ ticketData?.formattedPrice }}</div>
                                </div>
                                <div class="space-y-3 mb-6 bg-blue-50/50 p-4 rounded-xl border border-blue-100">
                                    <div class="flex gap-3"><i class="bi bi-geo-alt-fill text-blue-300"></i> <div><div class="text-[10px] text-gray-400 uppercase font-bold">Jemput</div><div class="text-xs font-bold text-gray-700">{{ parseAddress(ticketData.pickupAddress).text }}</div></div></div>
                                    <div class="flex gap-3"><i class="bi bi-flag-fill text-blue-300"></i> <div><div class="text-[10px] text-gray-400 uppercase font-bold">Tujuan</div><div class="text-xs font-bold text-gray-700">{{ parseAddress(ticketData.dropoffAddress).text }}</div></div></div>
                                </div>
                                 <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 mb-6 text-center" v-if="ticketData?.isDispatched || ticketData?.driverName !== 'Belum Ditentukan'">
                                    <div class="text-[10px] text-gray-400 uppercase font-bold mb-1">Armada & Driver</div>
                                    <div class="font-bold text-slate-800">{{ ticketData?.fleetName }} ({{ ticketData?.fleetPlate }})</div>
                                    <div class="text-xs text-slate-500">{{ ticketData?.driverName }}</div>
                                </div>
                            </div>
                    </div>
                </div>

                <div v-if="isValidationModalVisible" class="fixed inset-0 bg-black/95 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
                    <div class="bg-white p-6 rounded-2xl text-center max-w-lg w-full shadow-2xl animate-fade-in">
                        <h3 class="text-xl font-bold text-gray-800 mb-1">Validasi Pembayaran</h3>
                        <p class="text-sm text-gray-500 mb-4">Metode: <span class="font-bold text-sutan-blue-900 uppercase">{{ validationData.paymentMethod }}</span></p>
                        
                        <div v-if="validationData.paymentMethod === 'Cash'" class="bg-gray-50 p-6 rounded-xl border border-gray-200 mb-6 text-left space-y-3">
                                <div class="flex justify-between border-b border-gray-100 pb-2"><span class="text-gray-500 text-sm">Lokasi:</span> <span class="font-bold text-gray-800 text-right w-1/2">{{ validationData.paymentLocation || '-' }}</span></div>
                                <div class="flex justify-between border-b border-gray-100 pb-2"><span class="text-gray-500 text-sm">Penerima:</span> <span class="font-bold text-gray-800">{{ validationData.paymentReceiver || '-' }}</span></div>
                                <div class="flex justify-between pt-1">
                                    <span class="text-gray-500 text-sm">Total Tagihan:</span> 
                                    <div class="text-right">
                                        <div class="font-bold text-blue-600">Rp {{ ((validationData.totalPrice * validationData.seatCount)||0).toLocaleString('id-ID') }}</div>
                                        <div class="text-[10px] text-gray-400">{{ validationData.seatCount }} Orang x @ Rp {{ (validationData.totalPrice||0).toLocaleString('id-ID') }}</div>
                                    </div>
                                </div>
                            </div>

                        <div v-if="(validationData.paymentMethod === 'Transfer' || validationData.paymentMethod === 'DP') && validationData.paymentProof" class="mb-6">
                            <div class="bg-slate-100 p-2 rounded-xl mb-2 border border-slate-200">
                                <img :src="validationData.paymentProof" class="w-full h-48 object-cover rounded-lg cursor-pointer hover:opacity-90 transition-opacity" @click="window.open(validationData.paymentProof, '_blank')">
                            </div>
                            <a :href="validationData.paymentProof" target="_blank" class="text-xs font-bold text-blue-600 hover:underline"><i class="bi bi-box-arrow-up-right mr-1"></i> Lihat Ukuran Penuh</a>
                            <div class="mt-4 pt-4 border-t border-slate-100 text-left space-y-2">
                                <div class="flex justify-between text-xs"><span class="text-slate-500">Nominal Transfer/DP:</span> <span class="font-bold text-slate-800">Rp {{ ((validationData.paymentMethod==='DP'?validationData.downPaymentAmount:validationData.totalPrice)||0).toLocaleString('id-ID') }}</span></div>
                                <div v-if="validationData.paymentMethod==='DP'" class="flex justify-between text-xs"><span class="text-red-500">Sisa Tagihan:</span> <span class="font-bold text-red-600">Rp {{ ((validationData.totalPrice||0) - (validationData.downPaymentAmount||0)).toLocaleString('id-ID') }}</span></div>
                            </div>
                        </div>
                        <div v-else-if="(validationData.paymentMethod === 'Transfer' || validationData.paymentMethod === 'DP') && !validationData.paymentProof" class="mb-6 p-4 bg-red-50 text-red-600 rounded-xl text-xs font-bold border border-red-100">
                            <i class="bi bi-exclamation-circle mr-1"></i> Bukti Transfer Belum Diupload
                        </div>

                        <div class="flex gap-3">
                            <button @click="closeValidationModal()" class="flex-1 py-3 rounded-xl bg-gray-100 font-bold text-gray-600 hover:bg-gray-200 transition-colors">Tutup</button>
                            <button @click="confirmValidation()" class="flex-1 py-3 rounded-xl bg-green-600 text-white font-bold hover:bg-green-700 shadow-lg shadow-green-200 transition-transform active:scale-95 flex items-center justify-center gap-2"><i class="bi bi-check-lg"></i> Validasi Lunas</button>
                        </div>
                    </div>
                </div>
                <div v-if="isDetailModalVisible" class="fixed inset-0 bg-black/80 z-50 flex items-center justify-center p-4 backdrop-blur-sm" @click.self="isDetailModalVisible=false">
                    <div class="bg-white dark:bg-slate-800 w-full max-w-2xl rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh] animate-fade-in">
                         <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-slate-50 dark:bg-slate-900/50">
                             <div>
                                 <div class="text-[10px] bg-blue-100 text-blue-600 px-2 py-0.5 rounded font-bold uppercase w-fit mb-1">Booking Detail</div>
                                 <h3 class="text-lg font-bold text-slate-800 dark:text-white">#{{ detailModalData.id }}</h3>
                             </div>
                             <button @click="isDetailModalVisible=false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"><i class="bi bi-x-lg text-xl"></i></button>
                         </div>
                         <div class="p-6 overflow-y-auto custom-scrollbar space-y-6">
                             <!-- Info Utama -->
                             <div class="grid grid-cols-2 gap-6">
                                 <div class="space-y-4">
                                     <div>
                                         <div class="text-xs font-bold text-slate-400 uppercase mb-1">Penumpang</div>
                                         <div class="font-bold text-slate-800 dark:text-white text-lg">{{ detailModalData.passengerName }}</div>
                                         <div class="text-sm text-slate-500">{{ detailModalData.passengerPhone }}</div>
                                         <div class="mt-1"><span class="bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 text-xs px-2 py-0.5 rounded font-bold">{{ detailModalData.passengerType }}</span></div>
                                     </div>
                                     <div>
                                          <div class="text-xs font-bold text-slate-400 uppercase mb-1">Waktu Keberangkatan</div>
                                          <div class="flex items-center gap-2">
                                              <div class="bg-blue-50 text-blue-700 px-3 py-1 rounded-lg font-bold">{{ formatDate(detailModalData.date) }}</div>
                                              <div class="text-lg font-bold text-slate-700 dark:text-slate-300">{{ detailModalData.time }}</div>
                                          </div>
                                     </div>
                                     <div>
                                         <div class="text-xs font-bold text-slate-400 uppercase mb-1">Layanan</div>
                                         <div class="font-bold text-slate-700 dark:text-slate-300">{{ detailModalData.serviceType }} <span class="text-slate-400 font-normal">({{ detailModalData.routeId }})</span></div>
                                     </div>
                                 </div>
                                 <div class="space-y-4 bg-slate-50 dark:bg-slate-700/30 p-4 rounded-xl border border-slate-100 dark:border-slate-700/50 text-sm">
                                       <div>
                                           <div class="text-xs font-bold text-slate-400 uppercase mb-1">Jemput</div>
                                           <div class="font-bold text-slate-800 dark:text-slate-200">
                                               {{ parseAddress(detailModalData.pickupAddress).text }}
                                               <a v-if="parseAddress(detailModalData.pickupAddress).link" :href="parseAddress(detailModalData.pickupAddress).link" target="_blank" class="text-blue-500 hover:text-blue-700 ml-2 inline-flex items-center gap-1">
                                                   <i class="bi bi-geo-alt-fill"></i>
                                                   <span class="text-[10px]">Buka Map</span>
                                               </a>
                                           </div>
                                       </div>
                                       <div>
                                           <div class="text-xs font-bold text-slate-400 uppercase mb-1">Antar</div>
                                           <div class="font-bold text-slate-800 dark:text-slate-200">
                                               {{ parseAddress(detailModalData.dropoffAddress).text }}
                                               <a v-if="parseAddress(detailModalData.dropoffAddress).link" :href="parseAddress(detailModalData.dropoffAddress).link" target="_blank" class="text-blue-500 hover:text-blue-700 ml-2 inline-flex items-center gap-1">
                                                   <i class="bi bi-flag-fill"></i>
                                                   <span class="text-[10px]">Buka Map</span>
                                               </a>
                                           </div>
                                       </div>
                                      <div class="grid grid-cols-2 gap-4 pt-2 mt-2 border-t border-dashed border-slate-200 dark:border-slate-600">
                                          <div>
                                              <div class="text-[10px] font-bold text-slate-400 uppercase">Kursi</div>
                                              <div class="font-bold text-slate-800 dark:text-white">{{ detailModalData.seatNumbers || '-' }}</div>
                                          </div>
                                          <div>
                                              <div class="text-[10px] font-bold text-slate-400 uppercase">Durasi</div>
                                              <div class="font-bold text-slate-800 dark:text-white">{{ detailModalData.duration }} Hari</div>
                                          </div>
                                      </div>
                                 </div>
                             </div>

                             <!-- Payment Info -->
                             <div class="border-t border-slate-100 dark:border-slate-700 pt-6">
                                 <h4 class="font-bold text-slate-800 dark:text-white mb-4">Informasi Pembayaran</h4>
                                 <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden">
                                     <div class="p-4 flex justify-between items-center bg-slate-50 dark:bg-slate-700/30 border-b border-slate-100 dark:border-slate-700">
                                         <div>
                                             <div class="text-xs text-slate-500">Total Tagihan</div>
                                             <div class="text-xl font-bold text-blue-600">Rp {{ ((detailModalData.totalPrice * detailModalData.seatCount)||0).toLocaleString('id-ID') }}</div>
                                             <div class="text-[10px] text-slate-400 mt-0.5 font-medium">{{ detailModalData.seatCount }} Penumpang x @ Rp {{ (detailModalData.totalPrice||0).toLocaleString('id-ID') }}</div>
                                         </div>
                                         <div class="text-right">
                                             <div class="bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full text-xs font-bold uppercase" v-if="detailModalData.validationStatus==='Valid'">Lunas</div>
                                             <div class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-xs font-bold uppercase" v-else-if="detailModalData.paymentStatus==='DP'">DP</div>
                                             <div class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-bold uppercase" v-else>Belum Lunas</div>
                                         </div>
                                     </div>
                                     
                                     <!-- Sisa Bayar & DP Detail -->
                                     <div v-if="detailModalData.validationStatus !== 'Valid'" class="p-3 bg-red-50 dark:bg-red-900/10 border-b border-red-100 dark:border-red-900/30 flex justify-between items-center">
                                         <div class="text-xs font-bold text-red-600 dark:text-red-400">Sisa Pembayaran</div>
                                         <div class="text-base font-extrabold text-red-600 dark:text-red-400">
                                             Rp {{ (((detailModalData.totalPrice * detailModalData.seatCount) - (detailModalData.downPaymentAmount || 0)) || 0).toLocaleString('id-ID') }}
                                         </div>
                                     </div>

                                     <div class="p-4 grid grid-cols-2 gap-4 text-sm">
                                         <div>
                                             <div class="text-xs text-slate-400 uppercase font-bold mb-1">Metode Pembayaran</div>
                                             <div class="font-bold flex items-center gap-2">
                                                 {{ detailModalData.paymentMethod }}
                                                 <span v-if="detailModalData.paymentMethod==='DP'" class="text-xs font-normal text-slate-500">
                                                     (Rp {{ (detailModalData.downPaymentAmount||0).toLocaleString('id-ID') }})
                                                 </span>
                                             </div>
                                         </div>
                                          <div>
                                             <div class="text-xs text-slate-400 uppercase font-bold mb-1">
                                                 {{ detailModalData.paymentMethod === 'Transfer' || (detailModalData.paymentMethod === 'DP' && detailModalData.paymentProof) ? 'Bukti Transfer' : 'Lokasi / Penerima' }}
                                             </div>
                                             <div v-if="detailModalData.paymentProof" class="font-bold">
                                                 <a :href="detailModalData.paymentProof" target="_blank" class="text-blue-600 hover:underline flex items-center gap-1">
                                                     <i class="bi bi-image"></i> Lihat Bukti
                                                 </a>
                                             </div>
                                             <div v-else class="font-bold text-slate-700 dark:text-slate-300">
                                                 {{ detailModalData.paymentLocation || '-' }} / {{ detailModalData.paymentReceiver || '-' }}
                                             </div>
                                         </div>
                                     </div>
                                 </div>
                             </div>

                             <!-- Payment History -->
                             <div class="border-t border-slate-100 dark:border-slate-700 pt-6">
                                 <div class="flex justify-between items-center mb-4">
                                     <h4 class="font-bold text-slate-800 dark:text-white">Riwayat Pembayaran</h4>
                                     <button @click="loadPaymentHistory(detailModalData.id)" class="text-xs text-blue-600 hover:text-blue-700 font-bold">
                                         <i class="bi bi-arrow-clockwise mr-1"></i> Refresh
                                     </button>
                                 </div>
                                 <div v-if="paymentHistory.length > 0" class="space-y-3">
                                     <div v-for="payment in paymentHistory" :key="payment.id" class="bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-xl p-4 hover:shadow-md transition-all">
                                         <div class="flex justify-between items-start mb-3">
                                             <div class="flex-1">
                                                 <div class="flex items-center gap-2 mb-2">
                                                     <span class="px-2.5 py-1 text-xs font-bold rounded-full" :class="payment.payment_method==='Cash'?'bg-emerald-100 text-emerald-700':'bg-blue-100 text-blue-700'">
                                                         <i :class="payment.payment_method==='Cash'?'bi bi-cash':'bi bi-bank'" class="mr-1"></i>
                                                         {{ payment.payment_method }}
                                                     </span>
                                                     <span class="text-xs text-slate-500 dark:text-slate-400">{{ formatDateTime(payment.payment_date) }}</span>
                                                 </div>
                                                 
                                                 <!-- Cash Payment Details -->
                                                 <div v-if="payment.payment_method === 'Cash' && (payment.payment_location || payment.payment_receiver)" class="text-sm text-slate-700 dark:text-slate-300 space-y-1 mb-2">
                                                     <div v-if="payment.payment_location" class="flex items-center gap-2">
                                                         <i class="bi bi-geo-alt-fill text-emerald-600"></i>
                                                         <span class="font-medium">{{ payment.payment_location }}</span>
                                                     </div>
                                                     <div v-if="payment.payment_receiver" class="flex items-center gap-2">
                                                         <i class="bi bi-person-fill text-emerald-600"></i>
                                                         <span class="font-medium">{{ payment.payment_receiver }}</span>
                                                     </div>
                                                 </div>
                                                 
                                                 <!-- Transfer Proof Image -->
                                                 <div v-if="payment.payment_method === 'Transfer' && payment.payment_proof" class="mt-2">
                                                     <a :href="payment.payment_proof" target="_blank" class="inline-block">
                                                         <img :src="payment.payment_proof" class="w-32 h-32 object-cover rounded-lg border-2 border-blue-200 hover:border-blue-400 transition-colors cursor-pointer" alt="Bukti Transfer">
                                                     </a>
                                                     <div class="text-xs text-blue-600 mt-1">
                                                         <i class="bi bi-image-fill mr-1"></i>Klik untuk perbesar
                                                     </div>
                                                 </div>
                                                 
                                                 <div v-if="payment.notes" class="text-sm text-slate-600 dark:text-slate-400 mt-2 italic bg-slate-100 dark:bg-slate-700/50 p-2 rounded">
                                                     <i class="bi bi-chat-left-text mr-1"></i>{{ payment.notes }}
                                                 </div>
                                             </div>
                                             <div class="text-right ml-4">
                                                 <div class="text-2xl font-bold text-green-600">Rp {{ payment.amount.toLocaleString('id-ID') }}</div>
                                             </div>
                                         </div>
                                     </div>
                                 </div>
                                 <div v-else class="text-center py-6 text-slate-400 text-sm">
                                     <i class="bi bi-receipt text-3xl mb-2"></i>
                                     <div>Belum ada riwayat pembayaran</div>
                                     <div class="text-xs mt-1">Pembayaran pertama akan tercatat otomatis</div>
                                 </div>
                                 
                                 <!-- Add Payment Button -->
                                 <button v-if="detailModalData.validationStatus !== 'Valid'" @click="openAddPaymentModal(detailModalData)" class="w-full mt-4 px-4 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-bold rounded-xl hover:from-blue-700 hover:to-blue-800 transition-all shadow-lg flex items-center justify-center gap-2">
                                     <i class="bi bi-plus-circle-fill"></i> Tambah Pembayaran
                                 </button>
                             </div>
                         </div>
                         <div class="p-4 border-t border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 flex justify-end gap-3 filter drop-shadow">
                             <button @click="deleteBooking(detailModalData)" class="px-4 py-2 bg-red-50 text-red-600 font-bold hover:bg-red-100 rounded-lg transition-colors text-sm flex items-center gap-2"><i class="bi bi-trash"></i> Hapus</button>
                             <button @click="printTicket(detailModalData)" class="px-4 py-2 bg-white border border-slate-200 text-slate-600 font-bold hover:bg-slate-50 rounded-lg transition-colors text-sm shadow-sm flex items-center gap-2"><i class="bi bi-printer"></i> Cetak</button>
                             <button @click="isDetailModalVisible=false" class="px-6 py-2 bg-sr-blue dark:bg-blue-600 text-white font-bold rounded-lg shadow-lg hover:bg-slate-800 transition-colors text-sm">Tutup</button>
                         </div>
                     </div>
                 </div>

                <!-- Add Payment Modal -->
                <div v-if="isAddPaymentModalVisible" class="fixed inset-0 bg-black/80 z-[60] flex items-center justify-center p-4 backdrop-blur-sm" @click.self="closeAddPaymentModal">
                    <div class="bg-white dark:bg-slate-800 w-full max-w-lg rounded-2xl shadow-2xl animate-fade-in">
                        <div class="p-6 border-b border-slate-200 dark:border-slate-700">
                            <h3 class="text-xl font-bold text-slate-800 dark:text-white">Tambah Pembayaran</h3>
                            <p class="text-sm text-slate-500 mt-1">{{ activePaymentBooking?.passengerName }}</p>
                        </div>
                        <div class="p-6 space-y-4">
                            <div class="bg-slate-50 dark:bg-slate-700/50 p-4 rounded-xl space-y-2">
                                <div class="flex justify-between text-sm">
                                    <span class="text-slate-500">Total Tagihan:</span>
                                    <span class="font-bold">Rp {{ ((activePaymentBooking?.totalPrice * activePaymentBooking?.seatCount) || 0).toLocaleString('id-ID') }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-slate-500">Sudah Dibayar:</span>
                                    <span class="font-bold text-green-600">Rp {{ (activePaymentBooking?.downPaymentAmount || 0).toLocaleString('id-ID') }}</span>
                                </div>
                                <div class="flex justify-between text-sm pt-2 border-t border-slate-200 dark:border-slate-600">
                                    <span class="text-slate-500 font-bold">Sisa Tagihan:</span>
                                    <span class="font-bold text-red-600">Rp {{ (((activePaymentBooking?.totalPrice * activePaymentBooking?.seatCount) - (activePaymentBooking?.downPaymentAmount || 0)) || 0).toLocaleString('id-ID') }}</span>
                                </div>
                            </div>

                            <div>
                                <label class="text-xs font-bold text-slate-500 uppercase block mb-2">Nominal Pembayaran</label>
                                <input type="number" v-model="addPaymentForm.amount" class="w-full p-3 border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 rounded-xl text-sm outline-none focus:ring-2 focus:ring-blue-500" placeholder="Masukkan nominal">
                            </div>

                            <div>
                                <label class="text-xs font-bold text-slate-500 uppercase block mb-2">Metode Pembayaran</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <button @click="addPaymentForm.payment_method='Cash'" class="p-3 rounded-xl border-2 font-bold text-sm transition-all" :class="addPaymentForm.payment_method==='Cash'?'border-blue-500 bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300':'border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-400'">
                                        <i class="bi bi-cash mr-1"></i> Cash
                                    </button>
                                    <button @click="addPaymentForm.payment_method='Transfer'" class="p-3 rounded-xl border-2 font-bold text-sm transition-all" :class="addPaymentForm.payment_method==='Transfer'?'border-blue-500 bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300':'border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-400'">
                                        <i class="bi bi-bank mr-1"></i> Transfer
                                    </button>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="text-xs font-bold text-slate-500 uppercase block mb-2">Lokasi</label>
                                    <input type="text" v-model="addPaymentForm.payment_location" class="w-full p-3 border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 rounded-xl text-sm outline-none" placeholder="Loket/Mobil">
                                </div>
                                <div>
                                    <label class="text-xs font-bold text-slate-500 uppercase block mb-2">Penerima</label>
                                    <input type="text" v-model="addPaymentForm.payment_receiver" class="w-full p-3 border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 rounded-xl text-sm outline-none" placeholder="Nama Staff">
                                </div>
                            </div>

                            <div>
                                <label class="text-xs font-bold text-slate-500 uppercase block mb-2">Catatan (Opsional)</label>
                                <textarea v-model="addPaymentForm.notes" rows="2" class="w-full p-3 border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 rounded-xl text-sm outline-none" placeholder="Catatan tambahan..."></textarea>
                            </div>
                        </div>
                        <div class="p-6 border-t border-slate-200 dark:border-slate-700 flex gap-3">
                            <button @click="closeAddPaymentModal" class="flex-1 px-4 py-3 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 font-bold rounded-xl hover:bg-slate-200 transition-colors">
                                Batal
                            </button>
                            <button @click="submitAddPayment" class="flex-1 px-4 py-3 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition-all shadow-lg">
                                <i class="bi bi-check-lg mr-1"></i> Simpan Pembayaran
                            </button>
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