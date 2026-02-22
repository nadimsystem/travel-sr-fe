<?php require_once 'auth_check_fe.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Pending - Sutan Raya</title>
    <link rel="icon" type="image/webp" href="../image/logo.webp">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { 
            darkMode: 'class',
            theme: { extend: { colors: { 'sr-blue': '#0f172a', 'sr-gold': '#d4af37' } } } 
        }
    </script>
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            transition: background-color 0.3s, color 0.3s; 
            overflow: hidden;
        }
        
        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        
        .dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #475569; }
        .dark .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #64748b; }
        
        @keyframes fade-in {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in { animation: fade-in 0.3s ease-out; }
        
        .bg-sr-blue { background-color: #1e293b; }
    </style>
</head>
<body class="bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-slate-100">
    <div id="app" v-cloak class="flex h-screen overflow-hidden bg-slate-50 dark:bg-slate-900">
        <?php $currentPage = 'booking_pending'; include 'components/sidebar.php'; ?>
        
        <main class="flex-1 flex flex-col h-screen overflow-hidden transition-all duration-300" :class="isSidebarOpen ? 'md:ml-64' : 'md:ml-0'">
            <?php include 'components/topbar.php'; ?>
            
            <div class="flex-1 overflow-y-auto custom-scrollbar p-4">
                <div v-if="view === 'bookingPending'" class="animate-fade-in">
                    <!-- Header -->
                    <div class="mb-4">
                        <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Booking Pending</h1>
                        <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Daftar booking travel yang belum diproses</p>
                    </div>

                    <!-- Filters -->
                    <div class="bg-white dark:bg-slate-800 rounded-xl shadow p-3 mb-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Cari</label>
                                <input 
                                    type="text" 
                                    v-model="busSearchTerm" 
                                    placeholder="Nama atau telepon..." 
                                    class="w-full p-2 border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 rounded-lg text-sm"
                                >
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Tanggal</label>
                                <input 
                                    type="date" 
                                    v-model="filterDate" 
                                    class="w-full p-2 border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 rounded-lg text-sm"
                                >
                            </div>
                        </div>
                    </div>

                    <!-- Grouped by Route -->
                    <div class="space-y-4">
                        <div v-for="(bookings, route) in bookingsByRoute" :key="route" class="bg-white dark:bg-slate-800 rounded-xl shadow overflow-hidden">
                            <!-- Route Header -->
                            <div class="bg-gradient-to-r from-blue-600 to-blue-700 p-3 text-white">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h3 class="text-lg font-bold">{{ route }}</h3>
                                        <p class="text-blue-100 text-xs mt-0.5">{{ bookings.length }} penumpang pending</p>
                                    </div>
                                    <div class="bg-white/20 px-3 py-1.5 rounded-lg">
                                        <i class="bi bi-people-fill text-xl"></i>
                                    </div>
                                </div>
                            </div>

                            <!-- Passenger Cards -->
                            <div class="p-3 space-y-2">
                                <div 
                                    v-for="booking in bookings" 
                                    :key="booking.id"
                                    class="border border-slate-200 dark:border-slate-700 rounded-lg p-3 hover:shadow-md transition-shadow"
                                >
                                    <div class="flex items-start gap-2 mb-2">
                                        <div class="bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 px-2 py-0.5 rounded-full text-xs font-bold">
                                            #{{ booking.id }}
                                        </div>
                                        
                                            <!-- Badge Status (Unified) -->
                                            <div v-if="booking.status === 'Antrian'" class="bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300 px-2 py-0.5 rounded-full text-xs font-bold flex items-center gap-1">
                                                <i class="bi bi-hourglass-split"></i> Antrian
                                            </div>

                                        <span 
                                            class="px-2 py-0.5 rounded-full text-xs font-bold"
                                            :class="{
                                                'bg-green-100 text-green-700': booking.paymentStatus === 'Lunas',
                                                'bg-yellow-100 text-yellow-700': booking.paymentStatus === 'DP',
                                                'bg-red-100 text-red-700': booking.paymentStatus === 'Belum Bayar'
                                            }"
                                        >
                                            {{ booking.paymentStatus }}
                                                </span>

                                            </div>
                                            
                                            <h4 class="text-lg font-bold text-slate-800 dark:text-white mb-1">
                                                {{ booking.passengerName }}
                                            </h4>
                                            
                                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                                                <div>
                                                    <div class="text-xs text-slate-500 uppercase font-bold mb-1">Kontak</div>
                                                    <div class="font-medium text-slate-700 dark:text-slate-300">{{ booking.passengerPhone }}</div>
                                                </div>
                                                <div>
                                                    <div class="text-xs text-slate-500 uppercase font-bold mb-1">Jadwal</div>
                                                    <div class="font-medium text-slate-700 dark:text-slate-300">
                                                        {{ formatDate(booking.date) }} {{ booking.time }}
                                                    </div>
                                                </div>
                                                <div>
                                                    <div class="text-xs text-slate-500 uppercase font-bold mb-1">Kursi</div>
                                                    <div class="font-bold text-blue-600">{{ booking.seatNumbers || '-' }}</div>
                                                </div>
                                                <div>
                                                    <div class="text-xs text-slate-500 uppercase font-bold mb-1">Harga</div>
                                                    <div class="font-bold text-green-600">Rp {{ (booking.totalPrice || 0).toLocaleString('id-ID') }}</div>
                                                </div>
                                            </div>

                                            <div v-if="booking.pickupAddress || booking.dropoffAddress" class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-700 space-y-1 text-xs">
                                                <div v-if="booking.pickupAddress" class="flex items-start gap-2 text-slate-600 dark:text-slate-400">
                                                    <i class="bi bi-geo-alt-fill text-green-600 mt-0.5"></i>
                                                    <span>{{ booking.pickupAddress }}</span>
                                                </div>
                                                <div v-if="booking.dropoffAddress" class="flex items-start gap-2 text-slate-600 dark:text-slate-400">
                                                    <i class="bi bi-flag-fill text-red-600 mt-0.5"></i>
                                                    <span>{{ booking.dropoffAddress }}</span>
                                                </div>
                                            </div>

                                            <!-- Action Buttons -->
                                            <div class="mt-4 flex flex-wrap gap-2">
                                                <button 
                                                    v-if="booking.paymentProof"
                                                    @click="showProof(booking.paymentProof)"
                                                    class="px-3 py-1.5 text-xs font-bold bg-slate-100 text-slate-700 hover:bg-slate-200 rounded-lg flex items-center gap-2 transition"
                                                >
                                                    <i class="bi bi-receipt"></i> Cek Bukti TF
                                                </button>
                                                <button 
                                                    v-if="booking.ktmProof"
                                                    @click="showKtm(booking.ktmProof)"
                                                    class="px-3 py-1.5 text-xs font-bold bg-slate-100 text-slate-700 hover:bg-slate-200 rounded-lg flex items-center gap-2 transition"
                                                >
                                                    <i class="bi bi-person-badge"></i> Cek KTM
                                                </button>
                                                
                                                <div class="flex-1"></div>

                                                <button 
                                                    @click="rejectBooking(booking)"
                                                    class="px-4 py-1.5 text-xs font-bold bg-red-100 text-red-600 hover:bg-red-200 rounded-lg flex items-center gap-2 shadow-sm hover:shadow transition"
                                                >
                                                    <i class="bi bi-x-lg"></i> Tolak
                                                </button>
                                                <button 
                                                    @click="approveBooking(booking)"
                                                    class="px-4 py-1.5 text-xs font-bold bg-green-600 text-white hover:bg-green-700 rounded-lg flex items-center gap-2 shadow-sm hover:shadow transition"
                                                >
                                                    <i class="bi bi-check-lg"></i> Terima Booking
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div v-if="bookings.length === 0" class="text-center py-8 text-slate-400">
                                    Tidak ada penumpang pending
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="Object.keys(bookingsByRoute).length === 0" class="bg-white dark:bg-slate-800 rounded-xl shadow p-12 text-center">
                        <i class="bi bi-inbox text-6xl text-slate-300 dark:text-slate-600 mb-4"></i>
                        <h3 class="text-xl font-bold text-slate-700 dark:text-slate-300 mb-2">Tidak Ada Booking Pending</h3>
                        <p class="text-slate-500 dark:text-slate-400">Semua booking sudah diproses</p>
                    </div>
                </div>
            </div>
        </main>


    <script src="js/loading-optimizer.js"></script>

    <script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script type="module" src="app_pending.js?v=<?= time() ?>"></script>
    <script>
        // Move any inline scripts here if they were inside #app
    </script>
</body>
</html>