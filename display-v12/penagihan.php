<?php require_once 'auth_check_fe.php'; ?>
<!DOCTYPE html>
<html lang="id" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sutan Raya - Penagihan</title>
    <link rel="icon" type="image/webp" href="../image/logo.webp">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
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
        tailwind.config = { darkMode: 'class', theme: { extend: { colors: { 'sr-blue': '#0f172a', 'sr-gold': '#d4af37' } } } }
        window.initialView = 'penagihan';
    </script>
</head>
<body class="h-screen w-screen bg-slate-50 text-slate-800 dark:bg-slate-900 dark:text-slate-100">
    <div id="app" class="flex h-full w-full" v-cloak>
        <?php $currentPage = 'penagihan'; include 'components/sidebar.php'; ?>

        <main class="flex-1 flex flex-col min-w-0 bg-slate-50 dark:bg-slate-900 relative h-full overflow-hidden">
            <header class="h-16 bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between px-6 shadow-sm z-10 flex-shrink-0">
                <div class="flex items-center gap-3">
                    <button onclick="toggleSidebar()" class="md:hidden p-2 text-slate-500 hover:text-blue-600 transition-colors rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700">
                        <i class="bi bi-list text-2xl"></i>
                    </button>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800 dark:text-white">Penagihan & Validasi</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Validasi pembayaran dan penagihan tagihan</p>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <div class="text-right hidden sm:block">
                        <div class="text-xs font-bold text-slate-400 uppercase">{{ currentDate }}</div>
                        <div class="text-lg font-mono font-bold text-sr-blue dark:text-sr-gold leading-none">{{ currentTime }}</div>
                    </div>
                </div>
            </header>

            <div class="flex-1 overflow-y-auto p-6 custom-scrollbar">
                <!-- Date Filter Toggles -->
                <div class="flex items-center justify-between mb-4">
                    <div class="flex gap-2 bg-white dark:bg-slate-800 p-1 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
                        <button @click="setDateFilter('all')" class="px-4 py-2 rounded-lg text-sm font-bold transition-all" :class="dateFilter==='all'?'bg-sr-blue text-white shadow-md':'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700'">
                            <i class="bi bi-infinity mr-1"></i> Semua
                        </button>
                        <button @click="setDateFilter('month')" class="px-4 py-2 rounded-lg text-sm font-bold transition-all" :class="dateFilter==='month'?'bg-sr-blue text-white shadow-md':'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700'">
                            <i class="bi bi-calendar-month mr-1"></i> Bulan Ini
                        </button>
                        <button @click="setDateFilter('today')" class="px-4 py-2 rounded-lg text-sm font-bold transition-all" :class="dateFilter==='today'?'bg-sr-blue text-white shadow-md':'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700'">
                            <i class="bi bi-calendar-day mr-1"></i> Hari Ini
                        </button>
                    </div>
                    <button v-if="quickFilter || searchTerm" @click="clearFilters" class="px-4 py-2 rounded-lg text-sm font-bold bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 transition-all">
                        <i class="bi bi-x-circle mr-1"></i> Clear Filter
                    </button>
                </div>

                <!-- Statistics Cards -->
                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div @click="setQuickFilter('unpaid')" class="relative bg-gradient-to-br from-red-500 to-red-600 rounded-2xl p-6 text-white shadow-lg cursor-pointer transform transition-all hover:scale-105 hover:shadow-xl" :class="quickFilter==='unpaid'?'ring-4 ring-red-300':''">

                        <div class="flex items-center justify-between mt-2">
                            <div>
                                <div class="text-xs font-bold uppercase opacity-80 mb-1">Total Belum Lunas</div>
                                <div class="text-3xl font-bold">Rp {{ computedStats.total_outstanding.toLocaleString('id-ID') }}</div>
                                <div class="transition-all duration-300 font-mono tracking-tighter mt-1" :class="quickFilter==='unpaid' ? 'text-sm font-bold opacity-100 whitespace-normal' : 'text-[10px] opacity-70 truncate max-w-[150px]'">{{ computedStats.outstanding_formula }}</div>
                                <div class="text-sm opacity-90 mt-1 font-bold">{{ computedStats.total_outstanding_count }} Booking</div>
                            </div>
                            <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center">
                                <i class="bi bi-exclamation-triangle-fill text-3xl"></i>
                            </div>
                        </div>
                        <div v-if="quickFilter==='unpaid'" class="mt-3 pt-3 border-t border-white/20 text-xs font-bold">
                            <i class="bi bi-filter-circle-fill mr-1"></i> Filter Aktif
                        </div>
                    </div>

                    <div @click="setQuickFilter('dp')" class="relative bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-2xl p-6 text-white shadow-lg cursor-pointer transform transition-all hover:scale-105 hover:shadow-xl" :class="quickFilter==='dp'?'ring-4 ring-yellow-300':''">

                        <div class="flex items-center justify-between mt-2">
                            <div>
                                <div class="text-xs font-bold uppercase opacity-80 mb-1">Total DP</div>
                                <div class="text-3xl font-bold">Rp {{ computedStats.total_dp.toLocaleString('id-ID') }}</div>
                                <div class="transition-all duration-300 font-mono tracking-tighter mt-1" :class="quickFilter==='dp' ? 'text-sm font-bold opacity-100 whitespace-normal' : 'text-[10px] opacity-70 truncate max-w-[150px]'">{{ computedStats.dp_formula }}</div>
                                <div class="text-sm opacity-90 mt-1 font-bold">{{ computedStats.total_dp_count }} Booking</div>
                            </div>
                        </div>
                        <div v-if="quickFilter==='dp'" class="mt-3 pt-3 border-t border-white/20 text-xs font-bold">
                            <i class="bi bi-filter-circle-fill mr-1"></i> Filter Aktif
                        </div>
                    </div>

                    <div @click="setQuickFilter('overdue')" class="relative bg-gradient-to-br from-orange-500 to-orange-600 rounded-2xl p-6 text-white shadow-lg cursor-pointer transform transition-all hover:scale-105 hover:shadow-xl" :class="quickFilter==='overdue'?'ring-4 ring-orange-300':''">

                        <div class="flex items-center justify-between mt-2">
                            <div>
                                <div class="text-xs font-bold uppercase opacity-80 mb-1">Lewat Tanggal</div>
                                <div class="text-3xl font-bold">Rp {{ computedStats.total_overdue.toLocaleString('id-ID') }}</div>
                                <div class="transition-all duration-300 font-mono tracking-tighter mt-1" :class="quickFilter==='overdue' ? 'text-sm font-bold opacity-100 whitespace-normal' : 'text-[10px] opacity-70 truncate max-w-[150px]'">{{ computedStats.overdue_formula }}</div>
                                <div class="text-sm opacity-90 mt-1 font-bold">{{ computedStats.total_overdue_count }} Booking</div>
                            </div>
                        </div>
                        <div v-if="quickFilter==='overdue'" class="mt-3 pt-3 border-t border-white/20 text-xs font-bold">
                            <i class="bi bi-filter-circle-fill mr-1"></i> Filter Aktif
                        </div>
                    </div>
                    <div @click="setQuickFilter('validation')" class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl p-6 text-white shadow-lg cursor-pointer transform transition-all hover:scale-105 hover:shadow-xl" :class="quickFilter==='validation'?'ring-4 ring-blue-300':''">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-xs font-bold uppercase opacity-80 mb-1">Perlu Validasi</div>
                                <div class="text-3xl font-bold">{{ computedStats.total_unvalidated_count || 0 }}</div>
                                <div class="text-sm opacity-90 mt-2">Menunggu Konfirmasi</div>
                            </div>
                            <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center">
                                <i class="bi bi-check-circle-fill text-3xl"></i>
                            </div>
                        </div>
                        <div v-if="quickFilter==='validation'" class="mt-3 pt-3 border-t border-white/20 text-xs font-bold">
                            <i class="bi bi-filter-circle-fill mr-1"></i> Filter Aktif
                        </div>
                    </div>
                </div>

                <!-- Active Filter Indicator -->
                <div v-if="quickFilter" class="mb-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-3 flex items-center justify-between animate-fade-in">
                    <div class="flex items-center gap-2 text-sm">
                        <i class="bi bi-funnel-fill text-blue-600"></i>
                        <span class="font-bold text-slate-700 dark:text-slate-200">Filter Aktif:</span>
                        <span class="px-2 py-0.5 bg-blue-600 text-white rounded-full text-xs font-bold">
                            {{ quickFilter === 'unpaid' ? 'Belum Bayar' : quickFilter === 'dp' ? 'DP' : quickFilter === 'overdue' ? 'Lewat Tanggal' : 'Perlu Validasi' }}
                        </span>
                        <span class="text-slate-500 dark:text-slate-400">•</span>
                        <span class="text-slate-600 dark:text-slate-300">{{ filteredOutstanding.length }} hasil</span>
                    </div>
                    <button @click="clearFilters" class="text-xs font-bold text-blue-600 hover:text-blue-700 dark:text-blue-400">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                <!-- Tab Navigation -->
                <div class="flex gap-2 mb-6 bg-white dark:bg-slate-800 p-1 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 w-fit">
                    <button @click="activeTab='validation'" class="px-4 py-2 rounded-lg text-sm font-bold transition-all" :class="activeTab==='validation'?'bg-sr-blue text-white':'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700'">
                        <i class="bi bi-shield-check mr-1"></i> Validasi ({{ filteredValidation.length }})
                    </button>
                    <button @click="activeTab='outstanding'" class="px-4 py-2 rounded-lg text-sm font-bold transition-all" :class="activeTab==='outstanding'?'bg-sr-blue text-white':'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700'">
                        <i class="bi bi-list-check mr-1"></i> Belum Lunas ({{ outstandingBookings.length }})
                    </button>
                    <button @click="activeTab='recent'" class="px-4 py-2 rounded-lg text-sm font-bold transition-all" :class="activeTab==='recent'?'bg-sr-blue text-white':'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700'">
                        <i class="bi bi-clock-history mr-1"></i> Pembayaran Terakhir
                    </button>
                </div>

                <!-- Validation Table -->
                <div v-if="activeTab==='validation'" class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden animate-fade-in flex flex-col h-full max-h-[600px]">
                    <div class="p-4 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center bg-slate-50/50 dark:bg-slate-800">
                        <h3 class="font-bold text-slate-800 dark:text-white">Menunggu Validasi</h3>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-slate-500 font-bold uppercase">Tampil</span>
                            <select @change="setLimit($event.target.value === 'all' ? 'all' : parseInt($event.target.value))" class="text-xs p-1.5 rounded border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 outline-none font-bold">
                                <option :value="10" :selected="itemsPerPage===10">10</option>
                                <option :value="20" :selected="itemsPerPage===20">20</option>
                                <option :value="50" :selected="itemsPerPage===50">50</option>
                                <option :value="100" :selected="itemsPerPage===100">100</option>
                                <option value="all" :selected="itemsPerPage>1000">Semua</option>
                            </select>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-slate-50 dark:bg-slate-700 text-slate-500 dark:text-slate-300 uppercase text-xs font-bold">
                                <tr>
                                    <th class="p-4">Penumpang</th>
                                    <th class="p-4">Tanggal</th>
                                    <th class="p-4">Rute</th>
                                    <th class="p-4">Total Tagihan</th>
                                    <th class="p-4">Bukti</th>
                                    <th class="p-4">Status</th>
                                    <th class="p-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                <tr v-for="booking in paginatedValidation" :key="booking.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                                    <td class="p-4">
                                        <div class="font-bold text-slate-800 dark:text-white">{{ booking.passengerName }}</div>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="text-xs text-slate-500">{{ censorPhone(booking.passengerPhone) }}</span>
                                            <a :href="'https://wa.me/' + booking.passengerPhone.replace(/^0/, '62').replace(/\D/g, '')" target="_blank" class="inline-flex items-center gap-1 px-2 py-0.5 bg-green-500 hover:bg-green-600 text-white text-xs font-bold rounded transition-colors">
                                                <i class="bi bi-whatsapp"></i> WA
                                            </a>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <div class="font-bold">{{ formatDate(booking.date) }}</div>
                                        <div class="text-xs text-slate-500">{{ booking.time }}</div>
                                    </td>
                                    <td class="p-4">
                                        <div class="text-xs font-bold">{{ (booking.routeId && String(booking.routeId).startsWith('CUSTOM_')) ? 'Carter Khusus' : booking.routeId }}</div>
                                        <div class="text-xs text-slate-500">{{ booking.serviceType }}</div>
                                    </td>
                                    <td class="p-4">
                                        <div class="font-bold text-green-600 dark:text-green-400">Rp {{ booking.total_bill.toLocaleString('id-ID') }}</div>
                                        <div v-if="booking.downPaymentAmount > 0 && booking.downPaymentAmount < booking.total_bill" class="text-[10px] text-slate-500 font-normal">
                                            DP: Rp {{ booking.downPaymentAmount.toLocaleString('id-ID') }}
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <a v-if="booking.paymentProof" :href="booking.paymentProof" target="_blank" class="text-blue-600 underline text-xs">Lihat Bukti</a>
                                        <span v-else class="text-xs text-slate-400">-</span>
                                    </td>
                                    <td class="p-4">
                                        <span class="px-2 py-1 text-xs font-bold bg-orange-100 text-orange-700 rounded-full">
                                            {{ booking.validationStatus || 'Menunggu Validasi' }}
                                        </span>
                                    </td>
                                    <td class="p-4 text-right">
                                        <button @click="validateBooking(booking)" class="px-3 py-1.5 bg-green-600 text-white text-xs font-bold rounded-lg hover:bg-green-700 transition-all">
                                            <i class="bi bi-check-lg mr-1"></i> Validasi
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="filteredValidation.length === 0">
                                    <td colspan="7" class="p-8 text-center text-slate-500">
                                        <i class="bi bi-check-circle text-4xl mb-2 block text-slate-300"></i>
                                        Tidak ada booking menunggu validasi
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Outstanding Bookings Table -->
                <div v-if="activeTab==='outstanding'" class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden animate-fade-in flex flex-col h-full max-h-[600px]">
                    <div class="p-4 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center bg-slate-50/50 dark:bg-slate-800">
                        <div class="flex items-center gap-4">
                            <h3 class="font-bold text-slate-800 dark:text-white">Booking Belum Lunas</h3>
                            <div class="flex items-center gap-2">
                                <span class="text-xs text-slate-500 font-bold uppercase">Tampil</span>
                                <select @change="setLimit($event.target.value === 'all' ? 'all' : parseInt($event.target.value))" class="text-xs p-1.5 rounded border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 outline-none font-bold">
                                    <option :value="10" :selected="itemsPerPage===10">10</option>
                                    <option :value="20" :selected="itemsPerPage===20">20</option>
                                    <option :value="50" :selected="itemsPerPage===50">50</option>
                                    <option :value="100" :selected="itemsPerPage===100">100</option>
                                    <option value="all" :selected="itemsPerPage>1000">Semua</option>
                                </select>
                            </div>
                        </div>
                        <input type="text" v-model="searchTerm" placeholder="Cari nama / phone..." class="px-3 py-1.5 text-sm border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 rounded-lg outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-slate-50 dark:bg-slate-700 text-slate-500 dark:text-slate-300 uppercase text-xs font-bold">
                                <tr>
                                    <th class="p-4">Penumpang</th>
                                    <th class="p-4">Tanggal</th>
                                    <th class="p-4">Rute</th>
                                    <th class="p-4">Total Tagihan</th>
                                    <th class="p-4">Sudah Dibayar</th>
                                    <th class="p-4">Sisa</th>
                                    <th class="p-4">Status</th>
                                    <th class="p-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                <tr v-for="booking in paginatedOutstanding" :key="booking.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                                    <td class="p-4">
                                        <div class="font-bold text-slate-800 dark:text-white">{{ booking.passengerName }}</div>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="text-xs text-slate-500">{{ censorPhone(booking.passengerPhone) }}</span>
                                            <a :href="'https://wa.me/' + booking.passengerPhone.replace(/^0/, '62').replace(/\D/g, '')" target="_blank" class="inline-flex items-center gap-1 px-2 py-0.5 bg-green-500 hover:bg-green-600 text-white text-xs font-bold rounded transition-colors">
                                                <i class="bi bi-whatsapp"></i> WA
                                            </a>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <div class="font-bold">{{ formatDate(booking.date) }}</div>
                                        <div class="text-xs text-slate-500">{{ booking.time }}</div>
                                    </td>
                                    <td class="p-4">
                                        <div class="text-xs font-bold">{{ (booking.routeId && String(booking.routeId).startsWith('CUSTOM_')) ? 'Carter Khusus' : booking.routeId }}</div>
                                        <div class="text-xs text-slate-500">{{ booking.serviceType }}</div>
                                    </td>
                                    <td class="p-4 font-bold">Rp {{ booking.total_bill.toLocaleString('id-ID') }}</td>
                                    <td class="p-4 text-green-600 dark:text-green-400 font-bold">Rp {{ booking.downPaymentAmount.toLocaleString('id-ID') }}</td>
                                    <td class="p-4 text-red-600 dark:text-red-400 font-bold">Rp {{ booking.remaining_amount.toLocaleString('id-ID') }}</td>
                                    <td class="p-4">
                                        <span v-if="booking.days_overdue > 0" class="px-2 py-1 text-xs font-bold bg-red-100 text-red-700 rounded-full">
                                            Lewat {{ booking.days_overdue }} hari
                                        </span>
                                        <span v-else-if="booking.downPaymentAmount > 0" class="px-2 py-1 text-xs font-bold bg-yellow-100 text-yellow-700 rounded-full">
                                            DP
                                        </span>
                                        <span v-else class="px-2 py-1 text-xs font-bold bg-slate-100 text-slate-700 rounded-full">
                                            Belum Bayar
                                        </span>
                                    </td>
                                    <td class="p-4 text-right">
                                        <button @click="openPaymentModal(booking)" class="px-3 py-1.5 bg-blue-600 text-white text-xs font-bold rounded-lg hover:bg-blue-700 transition-all">
                                            <i class="bi bi-cash-coin mr-1"></i> Bayar
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Recent Payments Table -->
                <div v-if="activeTab==='recent'" class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden animate-fade-in flex flex-col h-full max-h-[600px]">
                    <div class="p-4 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center bg-slate-50/50 dark:bg-slate-800">
                        <h3 class="font-bold text-slate-800 dark:text-white">Pembayaran 7 Hari Terakhir</h3>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-slate-500 font-bold uppercase">Tampil</span>
                            <select @change="setLimit($event.target.value === 'all' ? 'all' : parseInt($event.target.value))" class="text-xs p-1.5 rounded border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 outline-none font-bold">
                                <option :value="10" :selected="itemsPerPage===10">10</option>
                                <option :value="20" :selected="itemsPerPage===20">20</option>
                                <option :value="50" :selected="itemsPerPage===50">50</option>
                                <option :value="100" :selected="itemsPerPage===100">100</option>
                                <option value="all" :selected="itemsPerPage>1000">Semua</option>
                            </select>
                        </div>
                    </div>
                    <div class="overflow-x-auto flex-1 custom-scrollbar">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-slate-50 dark:bg-slate-700 text-slate-500 dark:text-slate-300 uppercase text-xs font-bold sticky top-0 z-10">
                                <tr>
                                    <th class="p-4">Tanggal</th>
                                    <th class="p-4">Penumpang</th>
                                    <th class="p-4">Metode</th>
                                    <th class="p-4">Nominal</th>
                                    <th class="p-4">Lokasi</th>
                                    <th class="p-4">Penerima</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                <tr v-for="payment in paginatedRecent" :key="payment.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/50">
                                    <td class="p-4">
                                        <div class="text-xs">{{ formatDateTime(payment.payment_date) }}</div>
                                    </td>
                                    <td class="p-4">
                                        <div class="font-bold">{{ payment.passengerName }}</div>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="text-xs text-slate-500">{{ censorPhone(payment.passengerPhone) }}</span>
                                            <a :href="'https://wa.me/' + payment.passengerPhone.replace(/^0/, '62').replace(/\D/g, '')" target="_blank" class="inline-flex items-center gap-1 px-2 py-0.5 bg-green-500 hover:bg-green-600 text-white text-xs font-bold rounded transition-colors">
                                                <i class="bi bi-whatsapp"></i> WA
                                            </a>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <span class="px-2 py-1 text-xs font-bold rounded-full" :class="payment.payment_method==='Cash'?'bg-emerald-100 text-emerald-700':'bg-blue-100 text-blue-700'">
                                            {{ payment.payment_method }}
                                        </span>
                                    </td>
                                    <td class="p-4 font-bold text-green-600">Rp {{ payment.amount.toLocaleString('id-ID') }}</td>
                                    <td class="p-4 text-xs">{{ payment.payment_location || '-' }}</td>
                                    <td class="p-4 text-xs">{{ payment.payment_receiver || '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>

        <!-- Payment Modal -->
        <div v-if="isPaymentModalVisible" class="fixed inset-0 bg-black/80 z-50 flex items-center justify-center p-4 backdrop-blur-sm" @click.self="closePaymentModal">
            <div class="bg-white dark:bg-slate-800 w-full max-w-lg rounded-2xl shadow-2xl animate-fade-in">
                <div class="p-6 border-b border-slate-200 dark:border-slate-700">
                    <h3 class="text-xl font-bold text-slate-800 dark:text-white">Tambah Pembayaran</h3>
                    <p class="text-sm text-slate-500 mt-1">{{ activeBooking?.passengerName }}</p>
                </div>
                <div class="p-6 space-y-4">
                    <div class="bg-slate-50 dark:bg-slate-700/50 p-4 rounded-xl space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500">Total Tagihan:</span>
                            <span class="font-bold">Rp {{ (activeBooking?.total_bill || 0).toLocaleString('id-ID') }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500">Sudah Dibayar:</span>
                            <span class="font-bold text-green-600">Rp {{ (activeBooking?.downPaymentAmount || 0).toLocaleString('id-ID') }}</span>
                        </div>
                        <div class="flex justify-between text-sm pt-2 border-t border-slate-200 dark:border-slate-600">
                            <span class="text-slate-500 font-bold">Sisa Tagihan:</span>
                            <span class="font-bold text-red-600">Rp {{ (activeBooking?.remaining_amount || 0).toLocaleString('id-ID') }}</span>
                        </div>
                    </div>

                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase block mb-2">Nominal Pembayaran</label>
                        <input type="number" v-model="paymentForm.amount" class="w-full p-3 border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 rounded-xl text-sm outline-none focus:ring-2 focus:ring-blue-500" placeholder="Masukkan nominal">
                    </div>

                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase block mb-2">Metode Pembayaran</label>
                        <div class="grid grid-cols-2 gap-2">
                            <button @click="paymentForm.payment_method='Cash'" class="p-3 rounded-xl border-2 font-bold text-sm transition-all" :class="paymentForm.payment_method==='Cash'?'border-blue-500 bg-blue-50 text-blue-700':'border-slate-200 dark:border-slate-600 text-slate-600'">
                                <i class="bi bi-cash mr-1"></i> Cash
                            </button>
                            <button @click="paymentForm.payment_method='Transfer'" class="p-3 rounded-xl border-2 font-bold text-sm transition-all" :class="paymentForm.payment_method==='Transfer'?'border-blue-500 bg-blue-50 text-blue-700':'border-slate-200 dark:border-slate-600 text-slate-600'">
                                <i class="bi bi-bank mr-1"></i> Transfer
                            </button>
                        </div>
                    </div>

                    <!-- Conditional Fields -->
                    <div v-if="paymentForm.payment_method === 'Transfer'" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-bold text-slate-500 uppercase block mb-2">Nama Pengirim</label>
                                <input type="text" v-model="paymentForm.payment_receiver" class="w-full p-3 border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 rounded-xl text-sm outline-none" placeholder="a.n. Siapa?">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-500 uppercase block mb-2">Bank Tujuan</label>
                                <select v-model="paymentForm.payment_location" class="w-full p-3 border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 rounded-xl text-sm outline-none">
                                    <option value="" disabled selected>-- Pilih Rekening Tujuan --</option>
                                    <option value="BCA Padang">BCA Padang</option>
                                    <option value="BCA Bukittinggi">BCA Bukittinggi</option>
                                    <option value="BCA Payakumbuh">BCA Payakumbuh</option>
                                    <option value="BCA PT">BCA PT (Sutan Raya)</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="text-xs font-bold text-slate-500 uppercase block mb-2">Bukti Transfer (Foto/Screenshot)</label>
                            <input type="file" @change="handlePaymentProofUpload" accept="image/*" class="w-full p-2 border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 rounded-xl text-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        </div>
                        <div v-if="paymentForm.payment_proof" class="relative w-fit">
                            <img :src="paymentForm.payment_proof" class="h-24 rounded-lg border">
                            <button @click="paymentForm.payment_proof=''" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs"><i class="bi bi-x"></i></button>
                        </div>
                    </div>

                    <!-- Cash Generic Fields -->
                    <div v-else class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-bold text-slate-500 uppercase block mb-2">Lokasi</label>
                            <select v-model="locationSelect" @change="paymentForm.payment_location = (locationSelect === 'manual' ? '' : locationSelect)" class="w-full p-3 border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 rounded-xl text-sm outline-none mb-2">
                                <option value="" disabled>-- Pilih Lokasi --</option>
                                <option v-for="loc in locationList" :value="loc">{{ loc }}</option>
                                <option value="manual">Lainnya (Input Manual)</option>
                            </select>
                            <input v-if="locationSelect === 'manual'" type="text" v-model="paymentForm.payment_location" class="w-full p-3 border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 rounded-xl text-sm outline-none animate-fade-in" placeholder="Masukkan Lokasi...">
                        </div>
                        <div>
                            <label class="text-xs font-bold text-slate-500 uppercase block mb-2">Penerima</label>
                            <select v-model="receiverSelect" @change="paymentForm.payment_receiver = (receiverSelect === 'manual' ? '' : receiverSelect)" class="w-full p-3 border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 rounded-xl text-sm outline-none mb-2">
                                <option value="" disabled>-- Pilih Staff --</option>
                                <option v-for="name in receiverList" :value="name">{{ name }}</option>
                                <option value="manual">Lainnya (Input Manual)</option>
                            </select>
                            <input v-if="receiverSelect === 'manual'" type="text" v-model="paymentForm.payment_receiver" class="w-full p-3 border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 rounded-xl text-sm outline-none animate-fade-in" placeholder="Masukkan Nama Staff...">
                        </div>
                    </div>

                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase block mb-2">Catatan (Opsional)</label>
                        <textarea v-model="paymentForm.notes" rows="2" class="w-full p-3 border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 rounded-xl text-sm outline-none" placeholder="Catatan tambahan..."></textarea>
                    </div>
                </div>
                <div class="p-6 border-t border-slate-200 dark:border-slate-700 flex gap-3">
                    <button @click="closePaymentModal" class="flex-1 px-4 py-3 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 font-bold rounded-xl hover:bg-slate-200 transition-colors">
                        Batal
                    </button>
                    <button @click="submitPayment" class="flex-1 px-4 py-3 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition-all shadow-lg">
                        <i class="bi bi-check-lg mr-1"></i> Simpan Pembayaran
                    </button>
                </div>
            </div>
        </div>

        <!-- Breakdown Modal -->
        <div v-if="isBreakdownModalVisible" class="fixed inset-0 bg-black/80 z-50 flex items-center justify-center p-4 backdrop-blur-sm" @click.self="closeBreakdownModal">
            <div class="bg-white dark:bg-slate-800 w-full max-w-2xl rounded-2xl shadow-2xl animate-fade-in flex flex-col max-h-[90vh]">
                <div class="p-6 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center">
                    <div>
                        <h3 class="text-xl font-bold text-slate-800 dark:text-white">{{ breakdownTitle }}</h3>
                        <p class="text-sm text-slate-500 mt-1">{{ breakdownItems.length }} Booking - Total: Rp {{ breakdownTotal.toLocaleString('id-ID') }}</p>
                    </div>
                    <button @click="closeBreakdownModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                        <i class="bi bi-x-lg text-xl"></i>
                    </button>
                </div>
                <div class="p-0 overflow-y-auto custom-scrollbar flex-1">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-700 text-slate-500 dark:text-slate-300 uppercase text-xs font-bold sticky top-0 z-10">
                            <tr>
                                <th class="p-4 bg-slate-50 dark:bg-slate-700">Tanggal</th>
                                <th class="p-4 bg-slate-50 dark:bg-slate-700">Penumpang</th>
                                <th class="p-4 bg-slate-50 dark:bg-slate-700 text-right">Nominal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            <tr v-for="item in breakdownItems" :key="item.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/50">
                                <td class="p-4">
                                    <div class="font-bold">{{ formatDate(item.date) }}</div>
                                    <div class="text-xs text-slate-500">{{ item.time }}</div>
                                </td>
                                <td class="p-4">
                                    <div class="font-bold text-slate-800 dark:text-white">{{ item.passengerName }}</div>
                                    <div class="text-xs text-slate-500">{{ item.routeId }}</div>
                                </td>
                                <td class="p-4 text-right font-bold font-mono">
                                    Rp {{ (breakdownTitle === 'Rincian DP' ? item.downPaymentAmount : item.remaining_amount).toLocaleString('id-ID') }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="p-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 rounded-b-2xl">
                    <div class="flex justify-between items-center text-sm font-bold">
                        <span class="text-slate-500">Total Perhitungan:</span>
                        <span class="text-xl text-sr-blue dark:text-white">Rp {{ breakdownTotal.toLocaleString('id-ID') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="js/penagihan.js?v=<?= time() ?>"></script>
</body>
</html>
