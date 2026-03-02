<?php include 'auth_check.php'; ?>
<!DOCTYPE html>
<html lang="id" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan & Statistik - Sutan Raya</title>
    <link rel="icon" type="image/webp" href="image/logo.webp">
    <script src="js/loading-optimizer.js?v=<?= time() ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        [v-cloak] { display: none; }
    </style>
    <script>
        tailwind.config = { darkMode: 'class', theme: { extend: { colors: { 'sr-blue': '#0f172a', 'sr-gold': '#d4af37' } } } }
    </script>
</head>
<body class="h-screen w-screen bg-slate-50 text-slate-800 dark:bg-slate-900 dark:text-slate-100 overflow-hidden">
    <div id="app" class="flex h-full w-full" v-cloak>
        <!-- Sidebar (Consistent with index.php) -->
        <!-- Sidebar (Custom for Reports) -->
        <!-- Mobile Overlay -->
        <div id="sidebarOverlay" class="fixed inset-0 bg-black/50 z-30 md:hidden hidden" onclick="toggleMobileSidebar()"></div>
        
        <aside id="mobileSidebar" class="fixed md:relative w-64 bg-white dark:bg-slate-800 border-r border-slate-200 dark:border-slate-700 flex flex-col z-40 md:z-20 flex-shrink-0 h-full shadow-sm transition-all duration-300 -translate-x-full md:translate-x-0">
            <div class="h-16 flex items-center justify-center border-b border-slate-100 dark:border-slate-700">
                <div class="text-xl font-extrabold text-sr-blue dark:text-white tracking-tight flex items-center gap-2">
                    <img src="../image/logo.webp" alt="Sutan Raya" class="w-8 h-8 object-contain"> Sutan<span class="text-blue-600 dark:text-blue-400">Raya</span>
                </div>
            </div>
            <nav class="flex-1 overflow-y-auto p-3 space-y-1">
                <a href="index.php" class="flex items-center px-3 py-2.5 rounded-lg text-sm text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                    <i class="bi bi-arrow-left-circle-fill w-6"></i> Kembali ke Dashboard
                </a>
                <div class="text-[10px] font-bold text-slate-400 uppercase px-3 mb-2 mt-6 tracking-wider">Laporan</div>
                <a href="#" @click.prevent="setRouteFilter('', 'Statistik Universal')" :class="routeFilter === '' ? 'bg-blue-50 dark:bg-slate-700 text-blue-700 dark:text-blue-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700'" class="flex items-center px-3 py-2.5 rounded-lg text-sm transition-colors mb-1">
                    <i class="bi bi-bar-chart-fill w-6"></i> Statistik Universal
                </a>
                
                <div v-if="availableRoutes.length > 0">
                    <div class="text-[10px] font-bold text-slate-400 uppercase px-3 mb-2 mt-4 tracking-wider">Per Rute</div>
                    <a v-for="route in availableRoutes" :key="route" href="#" @click.prevent="setRouteFilter(route, 'Laporan: ' + route)" :class="routeFilter === route ? 'bg-blue-50 dark:bg-slate-700 text-blue-700 dark:text-blue-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700'" class="flex items-center px-3 py-2.5 rounded-lg text-sm transition-colors mb-1">
                        <i class="bi bi-geo-alt-fill w-6"></i> {{ route }}
                    </a>
                </div>
                <a href="#" @click.prevent="setReportType('cancellation')" :class="viewMode === 'cancellation' ? 'bg-red-50 dark:bg-slate-700 text-red-700 dark:text-red-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700'" class="flex items-center px-3 py-2.5 rounded-lg text-sm transition-colors">
                    <i class="bi bi-x-octagon-fill w-6"></i> Laporan Pembatalan
                </a>
                <!-- <div class="text-[10px] font-bold text-slate-400 uppercase px-3 mb-2 mt-6 tracking-wider">Pengaturan</div>
                <a href="admin_staff.php" class="flex items-center px-3 py-2.5 rounded-lg text-sm text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                    <i class="bi bi-people-fill w-6"></i> Admin & Staff
                </a> -->
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col min-w-0 bg-slate-50 dark:bg-slate-900 relative h-full overflow-hidden">
            <header class="h-16 bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between px-3 sm:px-6 shadow-sm z-10">
                <div class="flex items-center gap-3">
                     <!-- Mobile Menu Button -->
                    <button onclick="toggleMobileSidebar()" class="md:hidden w-9 h-9 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 transition-all flex items-center justify-center">
                        <i class="bi bi-list text-xl"></i>
                    </button>
                    <h2 class="text-sm sm:text-lg font-bold text-slate-800 dark:text-white truncate max-w-[200px] sm:max-w-none">{{ currentReportTitle }}</h2>
                </div>
                <div class="flex items-center gap-3">
                    <div v-if="viewMode === 'stats'" class="flex items-center gap-3">
                        <input v-if="period === 'daily'" type="month" v-model="selectedMonth" @change="fetchReports" @input="fetchReports" class="bg-slate-100 dark:bg-slate-700 border-none rounded-lg text-sm font-bold px-3 py-2 outline-none text-slate-600 dark:text-slate-300">
                        <select v-model="period" @change="fetchReports" class="bg-slate-100 dark:bg-slate-700 border-none rounded-lg text-sm font-bold px-3 py-2 outline-none">
                            <option value="daily">Harian</option>
                            <option value="weekly">Mingguan</option>
                            <option value="monthly">Bulanan</option>
                            <option value="yearly">Tahunan</option>
                        </select>
                    </div>
                    <button v-if="viewMode === 'cancellation'" class="bg-green-100 text-green-700 px-3 py-2 rounded-lg text-xs font-bold hover:bg-green-200 transition-colors" onclick="window.print()"><i class="bi bi-printer-fill"></i> Cetak</button>
                    <button @click="toggleDarkMode" class="w-9 h-9 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center"><i :class="isDarkMode ? 'bi-sun-fill' : 'bi-moon-stars-fill'"></i></button>
                </div>
            </header>

            <!-- CANCELLATION REPORT VIEW -->
            <div v-if="viewMode === 'cancellation'" class="flex-1 overflow-y-auto p-6 custom-scrollbar">
                <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden">
                     <div class="p-5 border-b border-slate-100 dark:border-slate-700">
                        <h3 class="font-bold text-slate-800 dark:text-white">Daftar Transaksi Dibatalkan</h3>
                        <p class="text-xs text-slate-500">Menampilkan history booking yang dibatalkan.</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-slate-50 dark:bg-slate-700 text-slate-500 dark:text-slate-300 font-bold uppercase text-xs">
                                <tr>
                                    <th class="px-6 py-4">Waktu Batal</th>
                                    <th class="px-6 py-4">Jadwal Asli</th>
                                    <th class="px-6 py-4">Penumpang</th>
                                    <th class="px-6 py-4">Kursi</th>
                                    <th class="px-6 py-4">Refund</th>
                                    <th class="px-6 py-4">Alasan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                <tr v-for="c in cancellationData" :key="c.id" @click="openEditRefund(c)" class="hover:bg-slate-50 dark:hover:bg-slate-700/50 cursor-pointer group/cancel">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-slate-700 dark:text-slate-200">{{ formatDateFull(c.cancelled_at) }}</div>
                                        <div class="text-[10px] text-slate-400">Oleh: {{ c.cancelled_by }}</div>
                                        <div v-if="c.refund_status === 'Refunded'" class="mt-1 inline-flex items-center gap-1 text-[10px] font-bold text-green-600 bg-green-50 px-1.5 py-0.5 rounded border border-green-100"><i class="bi bi-check-circle-fill"></i> Sudah Dikembalikan</div>
                                        <div v-else class="mt-1 inline-flex items-center gap-1 text-[10px] font-bold text-amber-600 bg-amber-50 px-1.5 py-0.5 rounded border border-amber-100"><i class="bi bi-hourglass-split"></i> Menunggu Refund</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-slate-800 dark:text-white">{{ c.date }} • {{ c.time }}</div>
                                        <div class="text-xs text-slate-500">{{ c.routeId }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-slate-700 dark:text-slate-200">{{ c.passengerName }}</div>
                                        <div class="text-xs text-slate-500">{{ c.passengerPhone }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="bg-slate-100 dark:bg-slate-700 px-2 py-0.5 rounded text-xs font-bold">{{ c.seatNumbers }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-red-600 font-mono">{{ formatRupiah(c.refund_amount) }}</div>
                                        <div class="text-[10px] text-slate-500 max-w-[150px] truncate" :title="c.refund_account">{{ c.refund_account || '-' }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-xs text-slate-600 dark:text-slate-300 italic max-w-[200px]">{{ c.cancellation_reason }}</div>
                                    </td>
                                </tr>
                                <tr v-if="cancellationData.length === 0">
                                    <td colspan="6" class="px-6 py-10 text-center text-slate-400 italic">Belum ada data pembatalan.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div v-if="viewMode === 'stats'" class="flex-1 overflow-y-auto p-6 custom-scrollbar">
                <!-- Summary Cards -->
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4 mb-6 sm:mb-8">
                    <div class="bg-white dark:bg-slate-800 p-4 sm:p-5 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm col-span-2 sm:col-span-1">
                        <div class="text-[10px] sm:text-xs font-bold text-slate-400 uppercase mb-1">Total Pendapatan</div>
                        <div class="text-lg sm:text-xl font-extrabold text-slate-800 dark:text-white">{{ formatRupiah(totalRevenue) }}</div>
                    </div>
                    <div class="bg-white dark:bg-slate-800 p-4 sm:p-5 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm">
                        <div class="text-[10px] sm:text-xs font-bold text-slate-400 uppercase mb-1">Tiket Terjual</div>
                        <div class="text-lg sm:text-xl font-extrabold text-slate-800 dark:text-white">{{ totalPax }} <span class="text-xs sm:text-sm font-medium text-slate-400">Kursi</span></div>
                    </div>
                    <div class="bg-red-50 dark:bg-slate-700/50 p-5 rounded-2xl border border-red-100 dark:border-slate-600 shadow-sm">
                        <div class="text-xs font-bold text-red-500 uppercase mb-1">Belum Bayar</div>
                        <div class="text-xl font-extrabold text-slate-800 dark:text-red-100">{{ formatRupiah(totalUnpaidAmount) }} <span class="text-xs font-medium text-red-300 block mt-1">{{ totalUnpaid }} Booking</span></div>
                    </div>
                    <div class="bg-orange-50 dark:bg-slate-700/50 p-5 rounded-2xl border border-orange-100 dark:border-slate-600 shadow-sm">
                        <div class="text-xs font-bold text-orange-600 uppercase mb-1">Total Refund</div>
                        <div class="text-xl font-extrabold text-slate-800 dark:text-orange-100">{{ formatRupiah(totalRefund) }}</div>
                         <div class="text-[10px] text-green-600 font-bold mt-1">+ {{ formatRupiah(totalRefundRevenue) }} (Pendapatan Batal)</div>
                    </div>
                    <div class="bg-amber-50 dark:bg-slate-700/50 p-5 rounded-2xl border border-amber-100 dark:border-slate-600 shadow-sm">
                        <div class="text-xs font-bold text-amber-500 uppercase mb-1">Perlu Validasi</div>
                        <div class="text-xl font-extrabold text-slate-800 dark:text-amber-100">{{ totalUnvalidated }} <span class="text-sm font-medium text-amber-300">Booking</span></div>
                    </div>
                    <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm">
                        <div class="text-xs font-bold text-slate-400 uppercase mb-1">Periode</div>
                        <div class="text-xl font-extrabold text-slate-800 dark:text-white capitalize truncate" :title="periodLabel">{{ periodLabel }}</div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="reportData.labels.length === 0" class="flex flex-col items-center justify-center py-20 text-center">
                    <div class="bg-slate-100 dark:bg-slate-800 p-6 rounded-full mb-4">
                        <i class="bi bi-calendar-x text-4xl text-slate-400"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-700 dark:text-slate-200 mb-1">Tidak Ada Data Laporan</h3>
                    <p class="text-slate-500 text-sm max-w-xs mx-auto">Belum ada transaksi yang tercatat untuk periode <span class="font-bold text-slate-600 dark:text-slate-400">{{ periodLabel }}</span>.</p>
                </div>

                <!-- Charts & Table -->
                <div v-else>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                        <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm">
                            <h3 class="font-bold text-slate-700 dark:text-slate-200 mb-4">Grafik Pendapatan</h3>
                            <canvas id="revenueChart"></canvas>
                        </div>
                        <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm">
                            <h3 class="font-bold text-slate-700 dark:text-slate-200 mb-4">Grafik Penumpang</h3>
                            <canvas id="paxChart"></canvas>
                        </div>
                    </div>

                    <!-- Detailed Table with Popover -->
                    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden">
                        <div class="p-5 border-b border-slate-100 dark:border-slate-700">
                            <h3 class="font-bold text-slate-800 dark:text-white">Laporan Harian Detail</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-[10px] md:text-sm text-left">
                                <thead class="bg-slate-50 dark:bg-slate-700 text-slate-500 dark:text-slate-300 font-bold uppercase text-[9px] md:text-xs">
                                    <tr>
                                        <th class="px-3 py-3 md:px-6 md:py-4">Tanggal</th>
                                        <th class="px-3 py-3 md:px-6 md:py-4 text-right md:text-left">Pendapatan</th>
                                        <th class="px-3 py-3 md:px-6 md:py-4 hidden sm:table-cell text-green-600">Cash</th>
                                        <th class="px-3 py-3 md:px-6 md:py-4 hidden sm:table-cell text-blue-600">Transfer</th>
                                        <th class="px-3 py-3 md:px-6 md:py-4 hidden md:table-cell text-red-600">Refund</th>
                                        <th class="px-3 py-3 md:px-6 md:py-4 text-center">Kursi</th>
                                        <th class="px-3 py-3 md:px-6 md:py-4 text-center hidden sm:table-cell">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                    <tr v-for="(label, index) in reversedLabels" :key="index" @click="openDetail(label)" class="group hover:bg-blue-50 dark:hover:bg-slate-700/50 transition-colors relative cursor-pointer active:bg-blue-100 dark:active:bg-slate-600">
                                        <td class="px-3 py-3 md:px-6 md:py-4 font-bold text-slate-700 dark:text-slate-200 relative">
                                            <div class="flex items-center gap-1">
                                                <span>{{ formatDate(label) }}</span>
                                                <i class="bi bi-info-circle-fill text-[8px] text-blue-400 md:hidden ml-1"></i>
                                            </div>
                                        </td>
                                        <td class="px-3 py-3 md:px-6 md:py-4 font-mono text-slate-800 dark:text-white font-bold text-right md:text-left">
                                            {{ formatRupiah(reversedRevenue[index]) }}
                                            <div v-if="reversedRefundRevenue[index] > 0" class="text-[8px] md:text-[10px] text-green-600">+ {{ formatRupiah(reversedRefundRevenue[index]) }}</div>
                                        </td>
                                        <td class="px-3 py-3 md:px-6 md:py-4 font-mono text-green-600 font-bold hidden sm:table-cell">{{ formatRupiah(reversedRevenueCash[index]) }}</td>
                                        <td class="px-3 py-3 md:px-6 md:py-4 font-mono text-blue-600 font-bold hidden sm:table-cell">{{ formatRupiah(reversedRevenueTransfer[index]) }}</td>
                                        <td class="px-3 py-3 md:px-6 md:py-4 font-mono hidden md:table-cell">
                                            <div v-if="reversedRefundTotal[index] > 0" class="text-red-600 font-bold">- {{ formatRupiah(reversedRefundTotal[index]) }}</div>
                                            <div v-else class="text-slate-300">-</div>
                                        </td>
                                        <td class="px-3 py-3 md:px-6 md:py-4 font-bold text-slate-600 dark:text-slate-300 text-center">{{ reversedPax[index] }}</td>
                                        <td class="px-3 py-3 md:px-6 md:py-4 text-center hidden sm:table-cell">
                                            <span class="px-2 py-1 rounded-full bg-green-100 text-green-700 text-[10px] font-bold">Completed</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Detail Modal -->
        <div v-if="selectedDate" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" @click.self="closeDetail">
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden animate-fade-in-up">
                <div class="p-6 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-slate-50 dark:bg-slate-800/50">
                    <div>
                        <h3 class="text-xl font-bold text-slate-800 dark:text-white">Detail Transaksi</h3>
                        <p class="text-slate-500 text-sm mt-1">{{ formatDate(selectedDate) }}</p>
                    </div>
                    <button @click="closeDetail" class="p-2 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-full transition-colors">
                        <i class="bi bi-x-lg text-slate-500"></i>
                    </button>
                </div>
                
                <div class="p-6 overflow-y-auto flex-1 custom-scrollbar">
                    <!-- Stats MiniCards -->
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-6">
                        <div class="bg-blue-50 dark:bg-slate-700/50 p-4 rounded-xl border border-blue-100 dark:border-slate-600">
                            <div class="text-[10px] font-bold text-blue-500 uppercase">Total Pendapatan</div>
                            <div class="text-lg font-bold text-slate-800 dark:text-blue-100">{{ formatRupiah(selectedStats.revenue) }}</div>
                        </div>
                        <div class="bg-green-50 dark:bg-slate-700/50 p-4 rounded-xl border border-green-100 dark:border-slate-600">
                            <div class="text-[10px] font-bold text-green-500 uppercase">Cash</div>
                            <div class="text-lg font-bold text-slate-800 dark:text-green-100">{{ formatRupiah(selectedStats.revenueCash) }}</div>
                        </div>
                        <div class="bg-indigo-50 dark:bg-slate-700/50 p-4 rounded-xl border border-indigo-100 dark:border-slate-600">
                            <div class="text-[10px] font-bold text-indigo-500 uppercase">Transfer</div>
                            <div class="text-lg font-bold text-slate-800 dark:text-indigo-100">{{ formatRupiah(selectedStats.revenueTransfer) }}</div>
                        </div>
                         <div class="bg-red-50 dark:bg-slate-700/50 p-4 rounded-xl border border-red-100 dark:border-slate-600">
                            <div class="text-[10px] font-bold text-red-500 uppercase">Belum Bayar</div>
                            <div class="text-lg font-bold text-slate-800 dark:text-red-100">{{ formatRupiah(selectedStats.unpaidAmount) }}</div>
                        </div>
                         <div class="bg-amber-50 dark:bg-slate-700/50 p-4 rounded-xl border border-amber-100 dark:border-slate-600">
                            <div class="text-[10px] font-bold text-amber-500 uppercase">Total Tiket</div>
                            <div class="text-lg font-bold text-slate-800 dark:text-amber-100">{{ selectedStats.pax }} Kursi</div>
                        </div>
                    </div>

                    <!-- Loader -->
                    <div v-if="isLoadingDetails" class="flex justify-center py-10">
                        <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-600"></div>
                    </div>

                    <!-- Filter Tabs -->
                    <div class="flex gap-2 mb-4 border-b border-slate-100 dark:border-slate-700 pb-1">
                        <button @click="modalFilter = 'all'" :class="modalFilter === 'all' ? 'text-blue-600 border-b-2 border-blue-600 font-bold' : 'text-slate-500 hover:text-slate-700'" class="px-4 py-2 text-sm transition-all">Semua</button>
                        <button @click="modalFilter = 'unpaid'" :class="modalFilter === 'unpaid' ? 'text-red-600 border-b-2 border-red-600 font-bold' : 'text-slate-500 hover:text-red-600'" class="px-4 py-2 text-sm transition-all flex items-center gap-2">Belum Bayar <span v-if="unpaidModalCount > 0" class="bg-red-100 text-red-600 px-1.5 rounded-full text-[10px]">{{ unpaidModalCount }}</span></button>
                        <button @click="modalFilter = 'cancelled'" :class="modalFilter === 'cancelled' ? 'text-orange-600 border-b-2 border-orange-600 font-bold' : 'text-slate-500 hover:text-orange-600'" class="px-4 py-2 text-sm transition-all flex items-center gap-2">Refund / Batal <span v-if="selectedCancelled.length > 0" class="bg-orange-100 text-orange-600 px-1.5 rounded-full text-[10px]">{{ selectedCancelled.length }}</span></button>
                    </div>

                    <!-- Table -->
                    <div v-else class="overflow-x-auto rounded-xl border border-slate-100 dark:border-slate-700">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-slate-100 dark:bg-slate-700 font-bold text-slate-600 dark:text-slate-300">
                                <tr>
                                    <th class="px-4 py-3">Waktu</th>
                                    <th class="px-4 py-3">Rute</th>
                                    <th class="px-4 py-3">Nama Penumpang</th>
                                    <th class="px-4 py-3 text-center">Info Kursi</th>
                                    <th class="px-4 py-3 text-right">Harga Total</th>
                                    <th class="px-4 py-3 text-center">Status Pembayaran</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700 bg-white dark:bg-slate-800">
                                <tr v-for="b in filteredModalBookings" :key="b.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/50">
                                    <td class="px-4 py-3 font-mono text-slate-500">{{ b.time }}</td>
                                    <td class="px-4 py-3 font-medium text-slate-800 dark:text-slate-200">{{ b.routeName }}</td>
                                    <td class="px-4 py-3 text-slate-600 dark:text-slate-300">
                                        {{ b.passengerName }}
                                        <div v-if="b.paymentStatus !== 'Lunas'" class="text-[10px] text-red-500 font-bold mt-0.5">Belum Lunas</div>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="flex flex-col items-center">
                                            <span class="text-xs font-bold text-slate-800 dark:text-white mb-0.5">{{ b.seatNumbers || '-' }}</span>
                                            <span class="bg-slate-100 dark:bg-slate-700 px-1.5 py-0.5 rounded text-[10px] text-slate-500 font-medium">{{ b.seatCount }} Kursi</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-right font-mono font-bold text-slate-700 dark:text-white">{{ formatRupiah(b.totalPrice) }}</td>
                                    <td class="px-4 py-3 text-center text-xs">
                                        <div v-if="b.paymentStatus === 'Lunas'">
                                            <span class="px-2 py-1 rounded bg-green-100 text-green-700 font-bold uppercase">{{ b.paymentMethod }} - LUNAS</span>
                                        </div>
                                        <div v-else>
                                             <span class="block px-2 py-0.5 rounded bg-red-100 text-red-700 font-bold uppercase mb-1">{{ b.paymentStatus || 'Belum Bayar' }}</span>
                                             <span class="text-red-500 font-bold">Sisa: {{ formatRupiah(b.totalPrice - (b.downPaymentAmount||0)) }}</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="filteredModalBookings.length === 0">
                                    <td colspan="6" class="px-4 py-8 text-center text-slate-400 italic">Tidak ada data.</td>
                                </tr>
                                <!-- Cancelled Rows -->
                                <tr v-if="modalFilter === 'cancelled' && filteredModalBookings.length > 0" v-for="b in filteredModalBookings" :key="b.id" @click="openEditRefund(b)" class="bg-red-50/30 hover:bg-red-50 dark:hover:bg-red-900/10 cursor-pointer group/cancel">
                                    <td class="px-4 py-3 font-mono text-slate-500">{{ b.time }}</td>
                                    <td class="px-4 py-3 font-medium text-slate-800 dark:text-slate-200">
                                        {{ b.routeName }} <span class="text-[10px] text-red-500 font-bold border border-red-200 px-1 rounded ml-1">BATAL</span>
                                    </td>
                                    <td class="px-4 py-3 text-slate-600 dark:text-slate-300">
                                        {{ b.passengerName }}
                                        <div class="text-[10px] text-slate-400 italic">{{ b.cancellation_reason || '-' }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="flex flex-col items-center">
                                            <span class="text-xs font-bold text-slate-800 dark:text-white mb-0.5">{{ b.seatNumbers || '-' }}</span>
                                            <span class="bg-slate-100 dark:bg-slate-700 px-1.5 py-0.5 rounded text-[10px] text-slate-500 font-medium">{{ b.seatCount }} Kursi</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-right font-mono font-bold text-slate-700 dark:text-slate-300 line-through decoration-red-500">{{ formatRupiah(b.totalPrice) }}</td>
                                    <td class="px-4 py-3 text-center text-xs">
                                         <span class="block px-2 py-0.5 rounded bg-orange-100 text-orange-700 font-bold uppercase mb-1">Refund: {{ formatRupiah(b.refund_amount) }}</span>
                                         <span class="text-green-600 font-bold text-[10px]">Fee: {{ formatRupiah(b.totalPrice - b.refund_amount) }}</span>
                                         <div v-if="b.refund_status === 'Refunded'" class="mt-1 flex justify-center items-center gap-1 text-green-600 font-bold bg-green-50 p-0.5 rounded border border-green-100"><i class="bi bi-check-circle-fill"></i> Sudah Dikembalikan</div>
                                         <div v-else class="mt-1 flex justify-center items-center gap-1 text-amber-600 font-bold bg-amber-50 p-0.5 rounded border border-amber-100"><i class="bi bi-hourglass-split"></i> Menunggu</div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            </div>


        <!-- Edit Refund Modal -->
        <div v-if="isEditRefundModalOpen" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-md overflow-hidden animate-fade-in-up border border-slate-200 dark:border-slate-700">
                <div class="p-5 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-slate-50 dark:bg-slate-800/50">
                    <h3 class="font-bold text-slate-800 dark:text-white">Edit Refund</h3>
                    <button @click="isEditRefundModalOpen = false" class="text-slate-400 hover:text-slate-600"><i class="bi bi-x-lg"></i></button>
                </div>
                <div class="p-6 space-y-4">
                    <!-- Status Checkbox -->
                    <div class="flex items-center gap-3 p-3 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-100 dark:border-green-800">
                        <input type="checkbox" id="refundStatus" v-model="editRefundData.refund_status" :true-value="'Refunded'" :false-value="'Pending'" class="w-5 h-5 text-green-600 rounded focus:ring-green-500 border-gray-300">
                        <label for="refundStatus" class="text-sm font-bold text-slate-700 dark:text-slate-200">Uang Sudah Dikembalikan</label>
                    </div>

                <!-- Protected Fields -->
                    <div @dblclick="unlockRefundEdit" class="relative group cursor-pointer" title="Double klik untuk edit">
                         <!-- Read-only overlay hint -->
                        <div v-if="!isRefundEditable" class="absolute inset-0 z-10 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity bg-slate-100/10 pointer-events-none">
                            <span class="bg-black/70 text-white text-xs px-2 py-1 rounded backdrop-blur-sm">Double klik untuk edit</span>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Nominal Refund <span v-if="!isRefundEditable" class="text-red-500">*</span></label>
                                <input type="number" v-model="editRefundData.refund_amount" :readonly="!isRefundEditable" placeholder="Contoh: 150000" class="w-full border rounded-lg px-3 py-2 text-sm font-bold outline-none transition-all" :class="isRefundEditable ? 'bg-white border-blue-300 focus:ring-2 focus:ring-blue-500' : 'bg-slate-100 border-slate-200 text-slate-500 cursor-not-allowed'">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Rekening Tujuan <span v-if="!isRefundEditable" class="text-red-500">*</span></label>
                                <input type="text" v-model="editRefundData.refund_account" :readonly="!isRefundEditable" placeholder="Bank - No. Rekening - Atas Nama" class="w-full border rounded-lg px-3 py-2 text-sm outline-none transition-all" :class="isRefundEditable ? 'bg-white border-blue-300 focus:ring-2 focus:ring-blue-500' : 'bg-slate-100 border-slate-200 text-slate-500 cursor-not-allowed'">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Alasan Pembatalan <span v-if="!isRefundEditable" class="text-red-500">*</span></label>
                                <textarea v-model="editRefundData.cancellation_reason" rows="2" :readonly="!isRefundEditable" placeholder="Masukkan alasan pembatalan..." class="w-full border rounded-lg px-3 py-2 text-sm outline-none transition-all" :class="isRefundEditable ? 'bg-white border-blue-300 focus:ring-2 focus:ring-blue-500' : 'bg-slate-100 border-slate-200 text-slate-500 cursor-not-allowed'"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="p-5 border-t border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-end gap-3">
                    <button @click="isEditRefundModalOpen = false" class="px-4 py-2 text-sm font-bold text-slate-500 hover:text-slate-700 transition-colors">Batal</button>
                    <button @click="saveRefundEdit" class="bg-blue-600 text-white px-5 py-2 rounded-lg text-sm font-bold hover:bg-blue-700 shadow-lg shadow-blue-500/30 transition-all">Simpan Perubahan</button>
                </div>
            </div>
        </div>
    </div>
    <!-- End App -->

    <script>
        const { createApp } = Vue;
        createApp({
            data() {
                return {
                    isDarkMode: false,
                    period: 'daily',
                    reportData: { labels: [], revenue: [], pax: [], unpaid: [], unpaidAmount: [], unvalidated: [] },
                    charts: { revenue: null, pax: null },
                    selectedDate: null,
                    selectedBookings: [],
                    selectedCancelled: [], // NEW
                    selectedStats: { revenue: 0, revenueCash: 0, revenueTransfer: 0, unpaidAmount: 0, pax: 0 },
                    isLoadingDetails: false,
                    selectedMonth: new Date().toISOString().slice(0, 7),
                    routeFilter: '',
                    currentReportTitle: 'Laporan & Statistik',
                    viewMode: 'stats', // stats | cancellation
                    cancellationData: [],
                    modalFilter: 'all',
                    editRefundData: {}, // NEW
                    isEditRefundModalOpen: false, // NEW
                    isRefundEditable: false, // Double click to unlock
                    availableRoutes: [] // NEW
                }
            },
            computed: {
                totalRevenue() { return this.reportData.revenue.reduce((a, b) => a + b, 0); },
                totalPax() { return this.reportData.pax.reduce((a, b) => a + b, 0); },
                totalUnpaid() { return (this.reportData.unpaid || []).reduce((a, b) => a + b, 0); },
                totalUnpaidAmount() { return (this.reportData.unpaidAmount || []).reduce((a, b) => a + b, 0); },
                totalUnvalidated() { return (this.reportData.unvalidated || []).reduce((a, b) => a + b, 0); },
                totalRefund() { return (this.reportData.refundTotal || []).reduce((a, b) => a + b, 0); },
                totalRefundRevenue() { return (this.reportData.refundRevenue || []).reduce((a, b) => a + b, 0); },
                
                filteredModalBookings() {
                    if (this.modalFilter === 'unpaid') {
                        return this.selectedBookings.filter(b => b.paymentStatus !== 'Lunas');
                    }
                    if (this.modalFilter === 'cancelled') {
                        return this.selectedCancelled;
                    }
                    return this.selectedBookings;
                },
                unpaidModalCount() {
                    return this.selectedBookings.filter(b => b.paymentStatus !== 'Lunas').length;
                },

                // Table needs Newest -> Oldest (Reverse of Chart Data)
                reversedLabels() { return [...this.reportData.labels].reverse(); },
                reversedRevenue() { return [...this.reportData.revenue].reverse(); },
                reversedRevenueCash() { return [...(this.reportData.revenueCash || [])].reverse(); },
                reversedRevenueTransfer() { return [...(this.reportData.revenueTransfer || [])].reverse(); },
                reversedRefundTotal() { return [...(this.reportData.refundTotal || [])].reverse(); },
                reversedRefundRevenue() { return [...(this.reportData.refundRevenue || [])].reverse(); },
                reversedPax() { return [...this.reportData.pax].reverse(); },
                periodLabel() {
                    if (this.period === 'daily') {
                        if (!this.selectedMonth) return 'Harian';
                        const date = new Date(this.selectedMonth + '-01');
                        return date.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });
                    }
                    if (this.period === 'monthly') return 'Tahunan (12 Bulan)';
                    return this.period;
                }
            },
            mounted() {
                if(localStorage.getItem('sutan_v85_theme') === 'dark') { this.isDarkMode = true; document.documentElement.classList.add('dark'); }
                this.fetchReports();
                this.fetchAvailableRoutes();
            },
            methods: {
                toggleDarkMode() {
                    this.isDarkMode = !this.isDarkMode;
                    if(this.isDarkMode) document.documentElement.classList.add('dark'); else document.documentElement.classList.remove('dark');
                    localStorage.setItem('sutan_v85_theme', this.isDarkMode ? 'dark' : 'light');
                },
                formatRupiah(n) { return new Intl.NumberFormat('id-ID', {style:'currency', currency:'IDR', minimumFractionDigits:0}).format(n||0); },
                formatDate(d) { 
                    if(!d) return '-'; 
                    // Handle Monthly (YYYY-MM)
                    if (d.length === 7) {
                        const date = new Date(d + '-01');
                        return date.toLocaleDateString('id-ID', { year: 'numeric', month: 'long' });
                    }
                    // Handle Daily (YYYY-MM-DD)
                    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }; 
                    return new Date(d).toLocaleDateString('id-ID', options); 
                },
                
                handleRouteFilterChange() {
                    this.currentReportTitle = this.routeFilter ? `Laporan: ${this.routeFilter}` : 'Statistik Universal';
                    this.fetchReports();
                },

                fetchAvailableRoutes() {
                     fetch('api.php?action=get_initial_data')
                        .then(res => res.json())
                        .then(d => {
                            if(d.routes) {
                                // 1. Map to names and Normalize
                                const names = d.routes.map(r => {
                                    const name = `${r.origin} - ${r.destination}`;
                                    return name.replace(/ \(Normal\)/gi, '');
                                });
                                
                                // 2. Deduplicate
                                const uniqueRoutes = [...new Set(names)];
                                
                                // 3. Add Fixed Options
                                uniqueRoutes.push('Carteran');
                                uniqueRoutes.push('Dropping');
                                
                                // 4. Custom Sort
                                this.availableRoutes = uniqueRoutes.sort((a, b) => {
                                    const getScore = (r) => {
                                        const lower = r.toLowerCase();
                                        if (r === 'Padang - Bukittinggi') return 1;
                                        if (r === 'Padang - Payakumbuh') return 2;
                                        if (r === 'Bukittinggi - Padang') return 3;
                                        if (r === 'Payakumbuh - Padang') return 4;
                                        if (lower.includes('via sitinjau')) return 5;
                                        if (r === 'Carteran') return 98;
                                        if (r === 'Dropping') return 99;
                                        return 50; // Others in the middle/end
                                    };
                                    
                                    const scoreA = getScore(a);
                                    const scoreB = getScore(b);
                                    
                                    if (scoreA !== scoreB) return scoreA - scoreB;
                                    return a.localeCompare(b);
                                });
                            }
                        });
                },

                setRouteFilter(filter, title) {
                    this.viewMode = 'stats';
                    this.routeFilter = filter;
                    this.currentReportTitle = title;
                    this.fetchReports();
                },

                setReportType(type) {
                    this.viewMode = type;
                    if(type === 'cancellation') {
                        this.currentReportTitle = 'Laporan Pembatalan';
                        this.fetchCancellationReport();
                    } else {
                        // Reset to default stats
                        this.setRouteFilter('', 'Statistik Universal');
                    }
                },

                fetchCancellationReport() {
                    fetch('api.php?action=get_cancelled_report')
                    .then(res => res.json())
                    .then(d => {
                        if(d.status === 'success') {
                            this.cancellationData = d.data;
                        }
                    });
                },
                
                formatDateFull(d) {
                    if (!d) return '-';
                    return new Date(d).toLocaleString('id-ID', {day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute:'2-digit'});
                },
                
                openDetail(date) {
                    this.selectedDate = date;
                    this.selectedBookings = [];
                    this.isLoadingDetails = true;
                    
                    // Find stats from existing data
                    const idx = this.reportData.labels.indexOf(date);
                    if (idx !== -1) {
                         this.selectedStats = {
                             revenue: this.reportData.revenue[idx],
                             revenueCash: (this.reportData.revenueCash || [])[idx] || 0,
                             revenueTransfer: (this.reportData.revenueTransfer || [])[idx] || 0,
                             unpaidAmount: (this.reportData.unpaidAmount || [])[idx] || 0,
                             pax: this.reportData.pax[idx]
                         };
                    }

                    const detailRouteParam = this.routeFilter ? `&routeKeyword=${this.routeFilter}` : '';
                    fetch(`api.php?action=get_report_details&date=${date}${detailRouteParam}`)
                        .then(res => res.json())
                        .then(d => {
                             if(d.bookings) {
                                 this.selectedBookings = d.bookings;
                             }
                             if(d.cancelled) {
                                 this.selectedCancelled = d.cancelled;
                             }
                             this.isLoadingDetails = false;
                        })
                        .catch(e => {
                            console.error(e);
                            this.isLoadingDetails = false;
                        });
                },
                closeDetail() {
                    this.selectedDate = null;
                },
                fetchReports() {
                    const monthParam = this.period === 'daily' ? `&month=${this.selectedMonth}` : '';
                    const routeParam = this.routeFilter ? `&routeKeyword=${this.routeFilter}` : '';
                    const url = `api.php?action=get_reports&period=${this.period}${monthParam}${routeParam}&t=${new Date().getTime()}`;
                    console.log('Fetching:', url);
                    fetch(url)
                        .then(res => res.json())
                        .then(d => {
                            if(d.reports) {
                                this.reportData = d.reports;
                                this.$nextTick(() => {
                                    this.updateCharts();
                                });
                            }
                        });
                },
                
                updateCharts() {
                    const ctxRev = document.getElementById('revenueChart');
                    const ctxPax = document.getElementById('paxChart');
                    
                    // Safety check: Canvas elements might exist but context depends on visibility
                    if (!ctxRev || !ctxPax) return; // Should not happen with v-if logic but good safety

                    
                    if(this.charts.revenue) this.charts.revenue.destroy();
                    if(this.charts.pax) this.charts.pax.destroy();
                    
                    const commonOptions = {
                        responsive: true,
                        plugins: { legend: { display: false } },
                        scales: { 
                            y: { 
                                beginAtZero: true, 
                                grid: { color: this.isDarkMode?'#334155':'#e2e8f0' } 
                            }, 
                            x: { 
                                grid: { display: false },
                                ticks: {
                                    maxRotation: 90,
                                    minRotation: 90,
                                    callback: function(value, index, values) {
                                        const label = this.getLabelForValue(value);
                                        // Format: YYYY-MM-DD -> DD-MM to hide year
                                        if (label && label.length >= 10) {
                                            // Split 2026-01-01 -> [2026, 01, 01]
                                            const parts = label.split('-');
                                            if (parts.length === 3) return `${parts[2]}-${parts[1]}`;
                                            return label.substring(5);
                                        }
                                        return label;
                                    }
                                }
                            } 
                        }
                    };

                    this.charts.revenue = new Chart(ctxRev, {
                        type: 'line',
                        data: {
                            labels: this.reportData.labels,
                            datasets: [{
                                label: 'Pendapatan',
                                data: this.reportData.revenue,
                                borderColor: '#2563eb',
                                backgroundColor: 'rgba(37, 99, 235, 0.1)',
                                fill: true,
                                tension: 0.4
                            }]
                        },
                        options: commonOptions
                    });

                    this.charts.pax = new Chart(ctxPax, {
                        type: 'bar',
                        data: {
                            labels: this.reportData.labels,
                            datasets: [{
                                label: 'Penumpang',
                                data: this.reportData.pax,
                                backgroundColor: '#d4af37',
                                borderRadius: 4
                            }]
                        },
                        options: commonOptions
                    });
                },

                // REFUND EDITING LOGIC
                openEditRefund(b) {
                    this.editRefundData = { ...b };
                    this.isEditRefundModalOpen = true;
                    this.isRefundEditable = false; // Locked by default
                },

                unlockRefundEdit() {
                    if(this.isRefundEditable) return;
                    Swal.fire({
                        title: 'Masukkan Kode Izin Edit',
                        input: 'password',
                        inputAttributes: { autocapitalize: 'off' },
                        showCancelButton: true,
                        confirmButtonText: 'Buka Akses',
                        cancelButtonText: 'Batal',
                        preConfirm: (code) => {
                            if (code === 'izinedit') return true;
                            else Swal.showValidationMessage('Kode Salah!');
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            this.isRefundEditable = true;
                            Swal.fire({ icon: 'success', title: 'Akses Diberikan', timer: 1000, showConfirmButton: false });
                        }
                    });
                },

                async saveRefundEdit() {
                    try {
                         const payload = {
                             action: 'update_cancellation',
                             id: this.editRefundData.id,
                             refundAmount: this.editRefundData.refund_amount,
                             refundAccount: this.editRefundData.refund_account,
                             reason: this.editRefundData.cancellation_reason,
                             refundStatus: this.editRefundData.refund_status
                         };

                         const res = await fetch('api.php', {
                             method: 'POST',
                             headers: {'Content-Type': 'application/json'},
                             body: JSON.stringify(payload)
                         });
                         const data = await res.json();
                         if(data.status === 'success') {
                             await Swal.fire({
                                 icon: 'success',
                                 title: 'Berhasil',
                                 text: 'Data Refund Berhasil Diupdate',
                                 timer: 1500,
                                 showConfirmButton: false
                             });
                             this.isEditRefundModalOpen = false;
                             
                             // Refresh Data
                             if(this.selectedDate) this.openDetail(this.selectedDate); 
                             if(this.viewMode === 'cancellation') this.fetchCancellationReport(); 
                             this.fetchReports(); 
                         } else {
                             Swal.fire('Gagal', 'Gagal update: ' + data.message, 'error');
                         }

                    } catch(e) {
                        console.error(e);
                        Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error');
                    }
                }
            }
        }).mount('#app');

        // Mobile Sidebar Toggle Logic (Vanilla JS outside Vue for simplicity with the Overlay)
        window.toggleMobileSidebar = function() {
            const sidebar = document.getElementById('mobileSidebar');
            const overlay = document.getElementById('sidebarOverlay');
            
            if (sidebar && overlay) {
                const isOpen = !sidebar.classList.contains('-translate-x-full');
                
                if (isOpen) {
                    sidebar.classList.add('-translate-x-full');
                    overlay.classList.add('hidden');
                } else {
                    sidebar.classList.remove('-translate-x-full');
                    overlay.classList.remove('hidden');
                }
            }
        };
    </script>
</body>
</html>
