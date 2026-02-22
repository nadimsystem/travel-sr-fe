<?php require_once 'auth_check_fe.php'; ?>
<!DOCTYPE html>
<html lang="id" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Booking - Sutan Raya</title>
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
            overflow: hidden; 
        }
        
        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        .dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #475569; }
        
        [v-cloak] { display: none; }
        .animate-fade-in { animation: fadeIn 0.3s ease-out forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
    </style>
    
    <script>
        tailwind.config = { 
            darkMode: 'class',
            theme: { extend: { colors: { 'sr-blue': '#0f172a', 'sr-gold': '#d4af37' } } } 
        }
        window.initialView = 'edit_booking';
        window.activeAdminName = "<?= isset($_SESSION['user']['name']) ? htmlspecialchars($_SESSION['user']['name']) : '' ?>";
    </script>
</head>
<body class="h-screen w-screen bg-slate-50 text-slate-800 dark:bg-slate-900 dark:text-slate-100">

    <div id="app" class="h-full w-full bg-slate-50 dark:bg-slate-900" v-cloak>
        
        <!-- Lock Screen -->
        <div v-if="isLocked" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-100 dark:bg-slate-900 px-4">
            <div class="w-full max-w-sm bg-white dark:bg-slate-800 rounded-2xl shadow-xl overflow-hidden text-center p-8 animate-fade-in border border-slate-200 dark:border-slate-700">
                <div class="w-16 h-16 bg-blue-50 dark:bg-slate-700 rounded-full flex items-center justify-center mx-auto mb-4 text-blue-600 dark:text-blue-400">
                    <i class="bi bi-shield-lock-fill text-3xl"></i>
                </div>
                <h2 class="text-xl font-bold text-slate-800 dark:text-white mb-2">Akses Terbatas</h2>
                <p class="text-sm text-slate-500 mb-6">Masukkan Kode Akses untuk melanjutkan.</p>
                
                <form @submit.prevent="unlockPage">
                    <input type="password" v-model="accessCode" placeholder="Kode Akses" class="w-full text-center text-lg tracking-widest p-3 border rounded-xl bg-slate-50 dark:bg-slate-900 dark:border-slate-600 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none mb-4 transition-all">
                    <button type="submit" class="w-full py-3 bg-sr-blue dark:bg-blue-600 text-white font-bold rounded-xl shadow-lg hover:bg-slate-800 transition-transform active:scale-95">Buka Akses</button>
                </form>
            </div>
        </div>

        <!-- Main Content (Full Screen) -->
        <main v-else class="h-full flex flex-col min-w-0 relative overflow-hidden transition-colors duration-300">
            <header class="h-16 bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between px-6 shadow-sm z-10 flex-shrink-0 transition-colors duration-300">
                <div class="flex items-center gap-4">
                     <a href="booking_management.php" class="w-9 h-9 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 transition-all flex items-center justify-center">
                        <i class="bi bi-arrow-left"></i>
                    </a>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800 dark:text-white">Edit Booking & Log History</h2>
                        <!-- <p class="text-xs text-slate-500 dark:text-slate-400">Mode Fokus • Tanpa Sidebar</p> -->
                    </div>
                </div>
                <div class="flex items-center gap-4">
                     <button @click="toggleDarkMode" class="w-9 h-9 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-yellow-400 hover:bg-slate-200 dark:hover:bg-slate-600 transition-all flex items-center justify-center">
                        <i :class="isDarkMode ? 'bi-sun-fill' : 'bi-moon-stars-fill'"></i>
                    </button>
                </div>
            </header>

            <!-- MAIN CONTENT AREA -->
            <div class="flex-1 relative overflow-hidden w-full custom-scrollbar overflow-y-auto bg-slate-50 dark:bg-slate-900">
                
                <!-- VIEW 1: BOOKING LIST (SEARCH) -->
                <div v-if="!isEditMode" class="p-6 max-w-7xl mx-auto space-y-6">
                    
                    <!-- Search Section -->
                    <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700">
                        <div class="flex flex-col md:flex-row gap-4 items-end">
                            <div class="flex-1 w-full">
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Cari Booking</label>
                                <div class="relative">
                                    <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                    <input type="text" v-model="searchTerm" placeholder="ID Booking / Nama / No. HP" class="w-full pl-10 p-3 border rounded-xl bg-slate-50 dark:bg-slate-900 dark:border-slate-600 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                                </div>
                            </div>
                             <div class="w-full md:w-48">
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Tanggal</label>
                                <input type="date" v-model="filterDate" class="w-full p-3 border rounded-xl bg-slate-50 dark:bg-slate-900 dark:border-slate-600 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                            </div>
                            <button @click="resetFilter" class="px-4 py-3 bg-gray-100 text-gray-600 rounded-xl text-sm font-bold hover:bg-gray-200 transition-colors w-full md:w-auto"><i class="bi bi-arrow-clockwise"></i> Reset</button>
                        </div>
                    </div>

                    <!-- Bookings List -->
                    <div v-if="isLoading" class="text-center py-20">
                        <div class="animate-spin w-10 h-10 border-4 border-blue-500 border-t-transparent rounded-full mx-auto mb-4"></div>
                        <p class="text-slate-500 text-sm font-medium">Memuat Data Booking...</p>
                    </div>

                    <div v-else-if="filteredBookings.length === 0" class="text-center py-24 bg-white dark:bg-slate-800 rounded-2xl border-2 border-dashed border-slate-200 dark:border-slate-700">
                        <div class="w-16 h-16 bg-slate-100 dark:bg-slate-700 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-300 dark:text-slate-500">
                            <i class="bi bi-search text-3xl"></i>
                        </div>
                        <h4 class="text-lg font-bold text-slate-700 dark:text-slate-300 mb-1">Tidak ada booking ditemukan</h4>
                        <p class="text-slate-500 text-sm">Coba ubah kata kunci pencarian atau filter tanggal.</p>
                    </div>

                    <div v-else class="grid grid-cols-1 gap-4">
                        <div v-for="booking in filteredBookings" :key="booking.id" class="group bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 flex flex-col md:flex-row items-center justify-between gap-6 hover:border-blue-300 dark:hover:border-blue-500 transition-all hover:shadow-md">
                            <div class="flex items-center gap-5 flex-1 w-full">
                                <div class="w-14 h-14 rounded-2xl bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                                    <i class="bi bi-ticket-perforated-fill text-2xl"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="flex flex-wrap items-center gap-2 mb-1">
                                        <h3 class="font-bold text-slate-800 dark:text-white text-lg">{{ booking.passengerName }}</h3>
                                        <span class="text-[10px] font-bold bg-slate-100 dark:bg-slate-700 text-slate-500 px-2 py-0.5 rounded uppercase tracking-wider">#{{ booking.id.toString().slice(-6) }}</span>
                                    </div>
                                    <div class="flex flex-wrap items-center gap-4 text-sm text-slate-500 dark:text-slate-400">
                                        <span class="flex items-center gap-1.5"><i class="bi bi-calendar4-week"></i> {{ formatDate(booking.date) }}</span>
                                        <span class="flex items-center gap-1.5"><i class="bi bi-clock"></i> {{ booking.time }}</span>
                                        <span class="flex items-center gap-1.5"><i class="bi bi-geo-alt"></i> {{ booking.routeName || booking.routeId }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex items-center gap-3 w-full md:w-auto">
                                <button @click="startEdit(booking)" class="flex-1 md:flex-none px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl shadow-lg shadow-blue-500/20 text-sm font-bold transition-all active:scale-95 flex items-center justify-center gap-2">
                                    <i class="bi bi-pencil-square"></i> Edit Data
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- VIEW 2: FULLSCREEN EDIT FORM -->
                <div v-else class="min-h-full flex flex-col bg-slate-100 dark:bg-slate-900">
                    <!-- Edit Actions Header -->
                    <div class="bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 px-4 py-3 flex justify-between items-center sticky top-0 z-20 shadow-sm flex-shrink-0">
                        <div class="flex items-center gap-3">
                            <button @click="cancelEdit" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors flex items-center justify-center">
                                <i class="bi bi-arrow-left text-base"></i>
                            </button>
                            <div class="leading-tight">
                                <h2 class="text-base font-bold text-slate-800 dark:text-white">Edit Booking</h2>
                                <p class="text-[10px] text-slate-500">ID: #{{ editForm.id }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button @click="deleteBooking" class="p-2 text-red-600 font-bold hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors text-xs flex items-center gap-1" title="Hapus">
                                <i class="bi bi-trash text-base"></i> <span class="hidden sm:inline">Hapus</span>
                            </button>
                            <button @click="cancelEdit" class="px-3 py-2 text-slate-600 dark:text-slate-300 font-bold hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors text-xs hidden sm:block">Batal</button>
                            <button @click="saveEdit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg shadow-lg shadow-blue-500/30 transition-all active:scale-95 text-xs flex items-center gap-1.5">
                                <i class="bi bi-save"></i> <span>Simpan</span>
                            </button>
                        </div>
                    </div>

                    <!-- Scrollable Content -->
                    <div class="flex-1 w-full max-w-5xl mx-auto p-3 lg:p-6 pb-20 overflow-y-auto custom-scrollbar">
                        
                        <!-- MAIN FORM GRID -->
                        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-4 lg:p-6 mb-6">
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12">
                                <!-- Left Column -->
                                <div class="space-y-6">
                                    <!-- 1. Data Penumpang -->
                                    <div class="space-y-3">
                                        <h4 class="text-sm font-bold text-slate-800 dark:text-white border-b border-slate-200 dark:border-slate-700 pb-2 flex items-center gap-2">
                                            <span class="w-6 h-6 bg-blue-600 text-white text-xs font-bold rounded-md flex items-center justify-center">1</span> 
                                            Data Penumpang
                                        </h4>
                                        <div class="grid grid-cols-2 gap-3">
                                            <div>
                                                <label class="text-[10px] font-bold text-slate-500 uppercase mb-1 block">Nama Penumpang</label>
                                                <input type="text" v-model="editForm.passengerName" class="w-full px-3 py-2 border rounded-lg dark:bg-slate-900 dark:border-slate-700 dark:text-white text-sm font-medium focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                                            </div>
                                            <div>
                                                <label class="text-[10px] font-bold text-slate-500 uppercase mb-1 block">No. WhatsApp</label>
                                                <input type="text" v-model="editForm.passengerPhone" class="w-full px-3 py-2 border rounded-lg dark:bg-slate-900 dark:border-slate-700 dark:text-white text-sm font-medium focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-2 gap-3">
                                            <div>
                                                <label class="text-[10px] font-bold text-slate-500 uppercase mb-1 block">Kategori</label>
                                                <div class="relative">
                                                    <select v-model="editForm.passengerType" @change="calculatePrice" class="w-full px-3 py-2 border rounded-lg dark:bg-slate-900 dark:border-slate-700 dark:text-white text-sm font-medium appearance-none focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                                                        <option value="Umum">Umum</option>
                                                        <option value="Pelajar">Pelajar</option>
                                                        <option value="Mahasiswa / Pelajar">Mahasiswa / Pelajar</option>
                                                    </select>
                                                    <i class="bi bi-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-xs text-slate-400 pointer-events-none"></i>
                                                </div>
                                                
                                                <div v-if="editForm.passengerType === 'Pelajar' || editForm.passengerType === 'Mahasiswa / Pelajar'" class="mt-2 bg-blue-50 dark:bg-blue-900/20 p-2 rounded-lg border border-blue-100 dark:border-blue-800">
                                                    <label class="text-[10px] font-bold text-blue-600 dark:text-blue-400 uppercase mb-1 block">Bukti KTM</label>
                                                    <div class="space-y-1">
                                                        <input type="file" @change="handleFileUpload" accept="image/*" class="block w-full text-[10px] text-slate-500 file:mr-2 file:py-1 file:px-2 file:rounded-full file:border-0 file:text-[10px] file:font-semibold file:bg-white file:text-blue-700 hover:file:bg-blue-50 transition-all"/>
                                                        <div v-if="editForm.ktmProof && !editForm.ktmProof.startsWith('data:')" class="flex items-center gap-1 text-[10px] text-slate-500">
                                                            <i class="bi bi-check-circle-fill text-green-500"></i>
                                                            <a :href="editForm.ktmProof" target="_blank" class="text-blue-600 hover:underline font-bold truncate max-w-[120px]">Lihat Bukti</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div>
                                                <label class="text-[10px] font-bold text-slate-500 uppercase mb-1 block">Alamat Jemput</label>
                                                <textarea v-model="editForm.pickupAddress" rows="2" class="w-full px-3 py-2 border rounded-lg dark:bg-slate-900 dark:border-slate-700 dark:text-white text-sm font-medium focus:ring-2 focus:ring-blue-500 outline-none transition-all resize-none"></textarea>
                                            </div>
                                        </div>
                                        <div>
                                             <label class="text-[10px] font-bold text-slate-500 uppercase mb-1 block">Alamat Antar</label>
                                             <textarea v-model="editForm.dropoffAddress" rows="1" class="w-full px-3 py-2 border rounded-lg dark:bg-slate-900 dark:border-slate-700 dark:text-white text-sm font-medium focus:ring-2 focus:ring-blue-500 outline-none transition-all resize-none"></textarea>
                                        </div>
                                    </div>

                                    <!-- 2. Jadwal & Rute -->
                                    <div class="space-y-3">
                                        <h4 class="text-sm font-bold text-slate-800 dark:text-white border-b border-slate-200 dark:border-slate-700 pb-2 flex items-center gap-2">
                                            <span class="w-6 h-6 bg-blue-600 text-white text-xs font-bold rounded-md flex items-center justify-center">2</span> 
                                            Jadwal & Rute
                                        </h4>
                                        
                                        <div class="bg-blue-50 dark:bg-blue-900/20 p-3 rounded-lg border border-blue-100 dark:border-blue-900/50 flex flex-col sm:flex-row sm:items-center gap-3">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-800 text-blue-600 dark:text-blue-300 flex items-center justify-center">
                                                    <i class="bi bi-calendar-check text-sm"></i>
                                                </div>
                                                <div>
                                                     <div class="text-[10px] text-blue-600 dark:text-blue-400 font-bold uppercase tracking-wider">Jadwal Lama</div>
                                                     <div class="font-bold text-sm text-blue-900 dark:text-blue-100">{{ formatDate(editForm.original?.date) }} • {{ editForm.original?.time }}</div>
                                                     <div class="text-xs text-blue-700 dark:text-blue-300">{{ editForm.original?.routeName }}</div>
                                                </div>
                                            </div>
                                        </div>

                                        <div>
                                            <label class="text-[10px] font-bold text-slate-500 uppercase mb-1 block">Rute Perjalanan</label>
                                            <div class="relative">
                                                <select v-model="editForm.routeId" @change="handleRouteDateChange" class="w-full px-3 py-2 border rounded-lg dark:bg-slate-900 dark:border-slate-700 dark:text-white text-sm font-medium appearance-none focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                                                    <option v-for="r in routeConfig" :value="r.id">{{ r.origin }} - {{ r.destination }}</option>
                                                </select>
                                                <i class="bi bi-card-list absolute right-3 top-1/2 -translate-y-1/2 text-xs text-slate-400 pointer-events-none"></i>
                                            </div>
                                        </div>
                                        
                                        <div class="grid grid-cols-2 gap-3">
                                            <div>
                                                <label class="text-[10px] font-bold text-slate-500 uppercase mb-1 block">Tanggal</label>
                                                <div class="relative">
                                                    <input type="date" v-model="editForm.date" @change="handleRouteDateChange" class="w-full px-3 py-2 border rounded-lg dark:bg-slate-900 dark:border-slate-700 dark:text-white text-sm font-medium focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                                                </div>
                                            </div>
                                            <div>
                                                <label class="text-[10px] font-bold text-slate-500 uppercase mb-1 block">Jam</label>
                                                <div class="relative">
                                                    <select v-model="editForm.time" @change="fetchOccupiedSeats" class="w-full px-3 py-2 border rounded-lg dark:bg-slate-900 dark:border-slate-700 dark:text-white text-sm font-medium appearance-none focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                                                        <option value="">-- Pilih Jam --</option>
                                                        <option v-for="t in availableTimes" :value="t">{{ t }}</option>
                                                    </select>
                                                    <i class="bi bi-clock absolute right-3 top-1/2 -translate-y-1/2 text-xs text-slate-400 pointer-events-none"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Right Column -->
                                <div class="space-y-6 flex flex-col">
                                    <!-- 3. Pilih Kursi -->
                                    <div class="space-y-3 flex-1">
                                        <div class="flex justify-between items-end border-b border-slate-200 dark:border-slate-700 pb-2">
                                            <h4 class="text-sm font-bold text-slate-800 dark:text-white flex items-center gap-2">
                                                <span class="w-6 h-6 bg-blue-600 text-white text-xs font-bold rounded-md flex items-center justify-center">3</span> 
                                                Pilih Kursi
                                            </h4>
                                             <div class="text-[10px] text-slate-500 font-medium bg-slate-100 dark:bg-slate-700 px-2 py-1 rounded border border-slate-200 dark:border-slate-600">
                                                Terpilih: <span class="font-bold text-blue-600 dark:text-blue-400 text-sm ml-1">{{ editForm.selectedSeats.join(', ') || '-' }}</span>
                                             </div>
                                        </div>

                                        <div v-if="isLoadingSeats" class="py-12 text-center text-slate-400 text-xs bg-slate-50 dark:bg-slate-900/50 rounded-2xl border-2 border-dashed border-slate-200 dark:border-slate-700">
                                            <div class="animate-pulse flex flex-col items-center">
                                                <i class="bi bi-arrow-repeat animate-spin text-2xl mb-2 block text-slate-300 dark:text-slate-600"></i>
                                                <span class="font-bold text-slate-400">Loading...</span>
                                            </div>
                                        </div>
                                        <div v-else class="relative w-full max-w-[280px] mx-auto bg-slate-50 dark:bg-slate-900/50 rounded-[2rem] border border-slate-200 dark:border-slate-700 p-6 shadow-inner">
                                            
                                            <!-- Seats Grid -->
                                            <div class="grid grid-cols-3 gap-3 gap-y-4">
                                                <!-- Row 1 -->
                                                <div class="flex justify-center">
                                                    <button @click="toggleSeat('CC')" :id="'seat-CC'" class="w-14 h-14 rounded-xl font-bold text-sm shadow-sm transition-all border border-slate-300 dark:border-slate-600 flex items-center justify-center hover:scale-105 active:scale-95 duration-200" :class="getSeatClass('CC')">CC</button>
                                                </div>
                                                <div></div> 
                                                <div class="flex justify-center">
                                                    <div class="w-14 h-14 rounded-full bg-slate-200 dark:bg-slate-800 border border-slate-300 dark:border-slate-600 flex flex-col items-center justify-center text-slate-400 dark:text-slate-500 font-bold text-[8px] shadow-inner">
                                                        <i class="bi bi-steering-wheel text-xl opacity-50"></i>
                                                        <span class="tracking-widest">SUPIR</span>
                                                    </div>
                                                </div>

                                                <!-- Row 2 -->
                                                <div class="flex justify-center">
                                                    <button @click="toggleSeat('1')" :id="'seat-1'" class="w-14 h-14 rounded-xl font-bold text-lg shadow-sm transition-all border border-slate-300 dark:border-slate-600 flex items-center justify-center hover:scale-105 active:scale-95 duration-200" :class="getSeatClass('1')">1</button>
                                                </div>
                                                <div></div>
                                                <div class="flex justify-center">
                                                    <button @click="toggleSeat('2')" :id="'seat-2'" class="w-14 h-14 rounded-xl font-bold text-lg shadow-sm transition-all border border-slate-300 dark:border-slate-600 flex items-center justify-center hover:scale-105 active:scale-95 duration-200" :class="getSeatClass('2')">2</button>
                                                </div>

                                                <!-- Row 3 -->
                                                 <div class="flex justify-center">
                                                    <button @click="toggleSeat('3')" :id="'seat-3'" class="w-14 h-14 rounded-xl font-bold text-lg shadow-sm transition-all border border-slate-300 dark:border-slate-600 flex items-center justify-center hover:scale-105 active:scale-95 duration-200" :class="getSeatClass('3')">3</button>
                                                </div>
                                                <div></div>
                                                <div class="flex justify-center">
                                                    <button @click="toggleSeat('4')" :id="'seat-4'" class="w-14 h-14 rounded-xl font-bold text-lg shadow-sm transition-all border border-slate-300 dark:border-slate-600 flex items-center justify-center hover:scale-105 active:scale-95 duration-200" :class="getSeatClass('4')">4</button>
                                                </div>

                                                <!-- Row 4 -->
                                                <div class="flex justify-center">
                                                    <button @click="toggleSeat('5')" :id="'seat-5'" class="w-14 h-14 rounded-xl font-bold text-lg shadow-sm transition-all border border-slate-300 dark:border-slate-600 flex items-center justify-center hover:scale-105 active:scale-95 duration-200" :class="getSeatClass('5')">5</button>
                                                </div>
                                                <div class="flex justify-center">
                                                    <button @click="toggleSeat('6')" :id="'seat-6'" class="w-14 h-14 rounded-xl font-bold text-lg shadow-sm transition-all border border-slate-300 dark:border-slate-600 flex items-center justify-center hover:scale-105 active:scale-95 duration-200" :class="getSeatClass('6')">6</button>
                                                </div>
                                                <div class="flex justify-center">
                                                    <button @click="toggleSeat('7')" :id="'seat-7'" class="w-14 h-14 rounded-xl font-bold text-lg shadow-sm transition-all border border-slate-300 dark:border-slate-600 flex items-center justify-center hover:scale-105 active:scale-95 duration-200" :class="getSeatClass(7)">7</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div>
                                         <!-- 4. Harga -->
                                         <div class="space-y-3 mb-6">
                                             <h4 class="text-sm font-bold text-slate-800 dark:text-white border-b border-slate-200 dark:border-slate-700 pb-2 flex items-center gap-2">
                                                <span class="w-6 h-6 bg-blue-600 text-white text-xs font-bold rounded-md flex items-center justify-center">4</span> 
                                                Total Pembayaran
                                             </h4>
                                             <div class="grid grid-cols-2 gap-4">
                                                 <div class="">
                                                     <label class="text-[10px] font-bold text-slate-500 uppercase mb-1 block">Harga Manual</label>
                                                     <div class="relative">
                                                         <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-xs">Rp</span>
                                                         <input type="number" v-model="editForm.totalPrice" class="w-full pl-8 p-2 border rounded-lg dark:bg-slate-900 dark:border-slate-700 dark:text-white text-lg font-mono font-bold bg-slate-50 text-slate-800 focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                                                     </div>
                                                 </div>
                                                 <div class="text-right bg-blue-50 dark:bg-blue-900/20 p-2 px-3 rounded-lg border border-blue-100 dark:border-blue-900/50 flex flex-col justify-center">
                                                     <div class="text-[10px] text-blue-600 dark:text-blue-400 uppercase font-black tracking-widest mb-0.5">TOTAL BAYAR</div>
                                                     <div class="text-lg font-black text-blue-600 dark:text-white">{{ formatRupiah(editForm.totalPrice) }}</div>
                                                 </div>
                                             </div>
                                             
                                             <div class="mt-3 bg-slate-50 dark:bg-slate-900/50 p-3 rounded-xl border border-slate-200 dark:border-slate-700">
                                                <label class="text-[10px] font-bold text-slate-500 uppercase mb-2 block tracking-wider">Bukti Transfer</label>
                                                <div class="flex flex-col gap-2">
                                                    <input type="file" @change="handlePaymentProofUpload" accept="image/*" class="block w-full text-[10px] text-slate-500 file:mr-2 file:py-1 file:px-3 file:rounded-full file:border-0 file:text-[10px] file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition-all"/>
                                                     <div v-if="editForm.paymentProof && !editForm.paymentProof.startsWith('data:')" class="flex items-center gap-2">
                                                        <span class="text-[10px] text-green-600 font-bold flex items-center gap-1"><i class="bi bi-check-circle-fill"></i> Ada</span>
                                                        <a :href="editForm.paymentProof" target="_blank" class="px-2 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded flex items-center gap-1 text-[10px] font-bold hover:bg-blue-200 dark:hover:bg-blue-800 transition-colors">
                                                            <i class="bi bi-eye"></i> Lihat
                                                        </a>
                                                    </div>
                                                </div>
                                             </div>
                                         </div>

                                        <div class="bg-red-50 dark:bg-red-900/10 p-3 rounded-xl border border-dashed border-red-200 dark:border-red-800">
                                            <label class="text-[10px] font-bold text-red-500 uppercase mb-1 block tracking-wider flex items-center gap-1"><i class="bi bi-info-circle-fill"></i> Diedit Oleh (Wajib)</label>
                                            <input type="text" v-model="editForm.adminName" placeholder="Nama Admin" readonly class="w-full px-3 py-2 border rounded-lg border-slate-200 bg-gray-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 font-bold text-sm focus:outline-none cursor-not-allowed shadow-none">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- LOG HISTORY SECTION -->
                        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-4 lg:p-6 mb-8">
                            <h4 class="text-sm font-bold text-slate-800 dark:text-white mb-4 flex items-center gap-2">
                                <i class="bi bi-clock-history text-slate-400"></i> Riwayat Perubahan
                            </h4>
                            
                            <div v-if="historyData.isLoading" class="text-center py-6">
                                <div class="animate-spin w-6 h-6 border-2 border-slate-300 border-t-slate-500 rounded-full mx-auto mb-2"></div>
                                <span class="text-xs text-slate-500">Memuat Riwayat...</span>
                            </div>
                            <div v-else-if="historyData.logs.length === 0" class="text-center py-6 text-slate-400 text-xs">
                                Belum ada riwayat perubahan.
                            </div>
                            <div v-else class="space-y-4 relative pl-3 lg:pl-0">
                                <div class="absolute left-4 top-2 bottom-2 w-px bg-slate-100 dark:bg-slate-700 hidden lg:block"></div>
                                
                                <div v-for="log in historyData.logs" :key="log.id" class="relative lg:pl-10">
                                     <div class="hidden lg:block absolute left-[13px] top-1.5 w-2 h-2 rounded-full bg-slate-400 ring-2 ring-white dark:ring-slate-800"></div>
                                     
                                     <div class="flex flex-col sm:flex-row sm:items-baseline justify-between mb-1">
                                         <div class="font-bold text-slate-800 dark:text-white text-xs">{{ log.action }}</div>
                                         <div class="text-[10px] font-mono text-slate-400">{{ log.timestamp }} • {{ log.admin_name }}</div>
                                     </div>
                                     
                                     <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-[10px] font-mono bg-slate-50 dark:bg-slate-900/50 p-3 rounded-lg border border-slate-100 dark:border-slate-800">
                                         <div>
                                              <div class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Sebelumnya</div>
                                              <div class="text-slate-600 dark:text-slate-400 leading-tight" v-html="formatLogDiff(log.prev_value, 'prev')"></div>
                                         </div>
                                         <div>
                                              <div class="text-[9px] font-black text-green-500 uppercase tracking-widest mb-1">Sesudah</div>
                                              <div class="text-slate-800 dark:text-slate-200 leading-tight" v-html="formatLogDiff(log.new_value, 'new')"></div>
                                         </div>
                                     </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                
            </div>

    </div>

    <!-- Logic -->
    <script src="js/edit_booking.js?v=<?= time() ?>"></script>
</body>
</html>
