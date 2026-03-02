<!DOCTYPE html>
<html lang="id" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sutan Raya - Manajemen Penagihan</title>
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
        window.initialView = 'billingManagement';
    </script>
</head>
<body class="h-screen w-screen bg-slate-50 text-slate-800 dark:bg-slate-900 dark:text-slate-100">
    <div id="app" class="flex h-full w-full" v-cloak>
        <?php $currentPage = 'billing_management'; include 'components/sidebar.php'; ?>

        <main class="flex-1 flex flex-col min-w-0 bg-slate-50 dark:bg-slate-900 relative h-full overflow-hidden">
            <header class="h-16 bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between px-6 shadow-sm z-10 flex-shrink-0">
                <div>
                    <h2 class="text-lg font-bold text-slate-800 dark:text-white">Manajemen Penagihan</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Tracking Pembayaran & Outstanding Bookings</p>
                </div>
                <div class="flex items-center gap-4">
                    <div class="text-right hidden sm:block">
                        <div class="text-xs font-bold text-slate-400 uppercase">{{ currentDate }}</div>
                        <div class="text-lg font-mono font-bold text-sr-blue dark:text-sr-gold leading-none">{{ currentTime }}</div>
                    </div>
                    <button @click="toggleDarkMode" class="w-9 h-9 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-yellow-400 hover:bg-slate-200 dark:hover:bg-slate-600 transition-all flex items-center justify-center">
                        <i :class="isDarkMode ? 'bi-sun-fill' : 'bi-moon-stars-fill'"></i>
                    </button>
                </div>
            </header>

            <div class="flex-1 overflow-y-auto p-6 custom-scrollbar">
                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-2xl p-6 text-white shadow-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-xs font-bold uppercase opacity-80 mb-1">Total Belum Lunas</div>
                                <div class="text-3xl font-bold">Rp {{ stats.total_outstanding.toLocaleString('id-ID') }}</div>
                                <div class="text-sm opacity-90 mt-2">{{ stats.total_outstanding_count }} Booking</div>
                            </div>
                            <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center">
                                <i class="bi bi-exclamation-triangle-fill text-3xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-2xl p-6 text-white shadow-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-xs font-bold uppercase opacity-80 mb-1">Total DP</div>
                                <div class="text-3xl font-bold">Rp {{ stats.total_dp.toLocaleString('id-ID') }}</div>
                                <div class="text-sm opacity-90 mt-2">{{ stats.total_dp_count }} Booking</div>
                            </div>
                            <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center">
                                <i class="bi bi-hourglass-split text-3xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-2xl p-6 text-white shadow-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-xs font-bold uppercase opacity-80 mb-1">Lewat Tanggal</div>
                                <div class="text-3xl font-bold">Rp {{ stats.total_overdue.toLocaleString('id-ID') }}</div>
                                <div class="text-sm opacity-90 mt-2">{{ stats.total_overdue_count }} Booking</div>
                            </div>
                            <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center">
                                <i class="bi bi-clock-history text-3xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab Navigation -->
                <div class="flex gap-2 mb-6 bg-white dark:bg-slate-800 p-1 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 w-fit">
                    <button @click="activeTab='outstanding'" class="px-4 py-2 rounded-lg text-sm font-bold transition-all" :class="activeTab==='outstanding'?'bg-sr-blue text-white':'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700'">
                        <i class="bi bi-list-check mr-1"></i> Belum Lunas ({{ outstandingBookings.length }})
                    </button>
                    <button @click="activeTab='recent'" class="px-4 py-2 rounded-lg text-sm font-bold transition-all" :class="activeTab==='recent'?'bg-sr-blue text-white':'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700'">
                        <i class="bi bi-clock-history mr-1"></i> Pembayaran Terakhir
                    </button>
                </div>

                <!-- Outstanding Bookings Table -->
                <div v-if="activeTab==='outstanding'" class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden animate-fade-in">
                    <div class="p-4 border-b border-slate-200 dark:border-slate-700">
                        <div class="flex justify-between items-center">
                            <h3 class="font-bold text-slate-800 dark:text-white">Booking Belum Lunas</h3>
                            <input type="text" v-model="searchTerm" placeholder="Cari nama / phone..." class="px-3 py-1.5 text-sm border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 rounded-lg outline-none focus:ring-2 focus:ring-blue-500">
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
                                    <th class="p-4">Sudah Dibayar</th>
                                    <th class="p-4">Sisa</th>
                                    <th class="p-4">Status</th>
                                    <th class="p-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                <tr v-for="booking in filteredOutstanding" :key="booking.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                                    <td class="p-4">
                                        <div class="font-bold text-slate-800 dark:text-white">{{ booking.passengerName }}</div>
                                        <div class="text-xs text-slate-500">{{ booking.passengerPhone }}</div>
                                    </td>
                                    <td class="p-4">
                                        <div class="font-bold">{{ formatDate(booking.date) }}</div>
                                        <div class="text-xs text-slate-500">{{ booking.time }}</div>
                                    </td>
                                    <td class="p-4">
                                        <div class="text-xs font-bold">{{ booking.routeId }}</div>
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
                <div v-if="activeTab==='recent'" class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden animate-fade-in">
                    <div class="p-4 border-b border-slate-200 dark:border-slate-700">
                        <h3 class="font-bold text-slate-800 dark:text-white">Pembayaran 7 Hari Terakhir</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-slate-50 dark:bg-slate-700 text-slate-500 dark:text-slate-300 uppercase text-xs font-bold">
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
                                <tr v-for="payment in recentPayments" :key="payment.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/50">
                                    <td class="p-4">
                                        <div class="text-xs">{{ formatDateTime(payment.payment_date) }}</div>
                                    </td>
                                    <td class="p-4">
                                        <div class="font-bold">{{ payment.passengerName }}</div>
                                        <div class="text-xs text-slate-500">{{ payment.passengerPhone }}</div>
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

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-bold text-slate-500 uppercase block mb-2">Lokasi</label>
                            <input type="text" v-model="paymentForm.payment_location" class="w-full p-3 border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 rounded-xl text-sm outline-none" placeholder="Loket/Mobil">
                        </div>
                        <div>
                            <label class="text-xs font-bold text-slate-500 uppercase block mb-2">Penerima</label>
                            <input type="text" v-model="paymentForm.payment_receiver" class="w-full p-3 border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 rounded-xl text-sm outline-none" placeholder="Nama Staff">
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
    </div>

    <script src="app.js?v=<?= time() ?>"></script>
    <script src="js/billing.js?v=<?= time() ?>"></script>
</body>
</html>
