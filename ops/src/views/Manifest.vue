<template>
  <div class="h-full flex flex-col">

    <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">
        <div class="flex items-center gap-3 bg-white dark:bg-slate-800 p-2 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm">
            <input type="date" v-model="manifestDate" @change="loadData" class="bg-transparent border-none text-slate-700 dark:text-slate-200 font-bold text-sm focus:ring-0 outline-none px-2">
            <div class="px-3 py-1 bg-slate-100 dark:bg-slate-700 rounded-lg text-xs font-bold text-slate-500 dark:text-slate-300 uppercase tracking-wider">
                {{ getDayName(manifestDate) }}
            </div>
        </div>

        <div class="flex items-center gap-3">
             <button onclick="window.print()" class="flex items-center gap-2 px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-600 dark:text-slate-300 font-bold text-sm hover:text-blue-600 transition-colors">
                 <i class="bi bi-printer-fill"></i> Cetak
             </button>
        </div>
    </div>


    <div v-if="isLoading" class="flex-1 flex justify-center items-center">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
    </div>

    <div v-else class="space-y-6 pb-20">

         <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
             <!-- Net Income (Revenue - Refunds) -->
             <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm relative overflow-hidden group">
                <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                    <i class="bi bi-cash-stack text-6xl text-blue-600"></i>
                </div>
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Total Bersih</div>
                <div class="text-2xl font-black text-slate-800 dark:text-white">{{ formatRupiah(manifestReport.grandTotal.netIncome) }}</div>
                <div class="mt-2 flex items-center gap-2 text-xs">
                    <span class="text-slate-500">Omset: {{ formatRupiah(manifestReport.grandTotal.totalNominal) }}</span>
                    <span class="text-green-600 font-bold">+ Fee Refund: {{ formatRupiah(manifestReport.grandTotal.totalPotongan) }}</span>
                </div>
             </div>


             <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm relative overflow-hidden group">
                <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                    <i class="bi bi-people-fill text-6xl text-purple-600"></i>
                </div>
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Total Penumpang</div>
                <div class="text-2xl font-black text-slate-800 dark:text-white">{{ manifestReport.grandTotal.totalPax }} <span class="text-sm font-medium text-slate-400">Org</span></div>
                <div class="mt-2 flex items-center gap-2 text-xs text-slate-500">
                    <span class="w-1.5 h-1.5 bg-slate-300 rounded-full"></span>
                    <span>Umum: <b>{{ manifestReport.grandTotal.umumPax }}</b></span>
                    <span class="w-1.5 h-1.5 bg-slate-300 rounded-full"></span>
                    <span>Pelajar: <b>{{ manifestReport.grandTotal.pelajarPax }}</b></span>
                </div>
             </div>
             
             <!-- Package Summary -->
             <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm relative overflow-hidden group">
                <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                    <i class="bi bi-box-seam-fill text-6xl text-orange-600"></i>
                </div>
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Ekspedisi Paket</div>
                <div class="text-2xl font-black text-slate-800 dark:text-white">{{ formatRupiah(manifestReport.grandTotal.totalPackage) }}</div>
                <div class="mt-2 flex items-center gap-2 text-xs text-slate-500">
                    <span>{{ manifestReport.packageCount }} Paket Terkirim</span>
                </div>
             </div>

         </div>


            <!-- PASSENGER ROUTES -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div v-for="(data, routeName) in manifestReport.routes" :key="routeName" class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden flex flex-col">
                    <div class="p-4 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-slate-50 dark:bg-slate-800/50">
                        <div>
                            <h3 class="font-bold text-slate-800 dark:text-white">{{ routeName }}</h3>
                            <div class="text-xs text-slate-500">{{ data.rows.length }} Jadwal Perjalanan</div>
                        </div>
                        <div class="text-right">
                            <div class="font-bold text-blue-600 dark:text-blue-400">{{ formatRupiah(data.total.totalNominal) }}</div>
                            <div class="text-[10px] text-slate-400">{{ data.total.totalPax }} Penumpang</div>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-slate-500 uppercase bg-slate-50 dark:bg-slate-700/50">
                                <tr>
                                    <th class="px-4 py-3 font-bold">Jam</th>
                                    <th class="px-4 py-3 font-bold text-center">Umum</th>
                                    <th class="px-4 py-3 font-bold text-center">Pelajar</th>
                                    <th class="px-4 py-3 font-bold text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                <tr v-for="(row, idx) in data.rows" :key="row.time + '-' + (row.batch||idx)" @click="openRowDetail(row, routeName)" class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors cursor-pointer active:bg-blue-50 dark:active:bg-slate-700">
                                    <td class="px-4 py-3 font-medium text-slate-800 dark:text-slate-200">{{ row.time }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="font-bold text-slate-700 dark:text-slate-300">{{ row.umumPax }}</div>
                                        <div class="text-[10px] text-slate-400">{{ formatRupiah(row.umumNominal) }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="font-bold text-slate-700 dark:text-slate-300">{{ row.pelajarPax }}</div>
                                        <div class="text-[10px] text-slate-400">{{ formatRupiah(row.pelajarNominal) }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="font-bold text-blue-600 dark:text-blue-400">{{ formatRupiah(row.totalNominal) }}</div>
                                        <div class="text-[10px] text-slate-400">{{ row.totalPax }} Org</div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Charter Card -->
                <div v-if="manifestReport.charters.length > 0" class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden flex flex-col">
                    <div class="p-4 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-yellow-50 dark:bg-yellow-900/10">
                        <div>
                            <h3 class="font-bold text-slate-800 dark:text-white">Carteran</h3>
                            <div class="text-xs text-slate-500">{{ manifestReport.charters.length }} Transaksi</div>
                        </div>
                        <div class="text-right">
                            <div class="font-bold text-yellow-600 dark:text-yellow-400">{{ formatRupiah(manifestReport.charterTotal.totalPrice) }}</div>
                            <div class="text-[10px] text-slate-400">Sisa: {{ formatRupiah(manifestReport.charterTotal.remainingAmount) }}</div>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-slate-500 uppercase bg-slate-50 dark:bg-slate-700/50">
                                <tr>
                                    <th class="px-4 py-3 font-bold">Rute</th>
                                    <th class="px-4 py-3 font-bold text-right">Total</th>
                                    <th class="px-4 py-3 font-bold text-right">Bayar</th>
                                    <th class="px-4 py-3 font-bold text-right">Sisa</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                <tr v-for="c in manifestReport.charters" :key="c.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                    <td class="px-4 py-3 font-medium text-slate-800 dark:text-slate-200">{{ c.route }}</td>
                                    <td class="px-4 py-3 text-right font-bold text-slate-800 dark:text-white">{{ formatRupiah(c.totalPrice) }}</td>
                                    <td class="px-4 py-3 text-right text-green-600 font-bold">{{ formatRupiah(c.paidAmount) }}</td>
                                    <td class="px-4 py-3 text-right text-red-500 font-bold">{{ formatRupiah(c.remainingAmount) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Dropping Card -->
                <div v-if="manifestReport.droppings.length > 0" class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden flex flex-col">
                    <div class="p-4 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-purple-50 dark:bg-purple-900/10">
                        <div>
                            <h3 class="font-bold text-slate-800 dark:text-white">Dropping</h3>
                            <div class="text-xs text-slate-500">{{ manifestReport.droppings.length }} Transaksi</div>
                        </div>
                        <div class="text-right">
                            <div class="font-bold text-purple-600 dark:text-purple-400">{{ formatRupiah(manifestReport.droppingTotal.totalPrice) }}</div>
                            <div class="text-[10px] text-slate-400">Sisa: {{ formatRupiah(manifestReport.droppingTotal.remainingAmount) }}</div>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-slate-500 uppercase bg-slate-50 dark:bg-slate-700/50">
                                <tr>
                                    <th class="px-4 py-3 font-bold">Rute</th>
                                    <th class="px-4 py-3 font-bold text-right">Total</th>
                                    <th class="px-4 py-3 font-bold text-right">Bayar</th>
                                    <th class="px-4 py-3 font-bold text-right">Sisa</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                <tr v-for="d in manifestReport.droppings" :key="d.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                    <td class="px-4 py-3 font-medium text-slate-800 dark:text-slate-200">{{ d.route }}</td>
                                    <td class="px-4 py-3 text-right font-bold text-slate-800 dark:text-white">{{ formatRupiah(d.totalPrice) }}</td>
                                    <td class="px-4 py-3 text-right text-green-600 font-bold">{{ formatRupiah(d.paidAmount) }}</td>
                                    <td class="px-4 py-3 text-right text-red-500 font-bold">{{ formatRupiah(d.remainingAmount) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- PACKAGES BY ROUTE -->
            <div v-if="Object.keys(manifestReport.packageRoutes).length > 0" class="mt-6">
                <h3 class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-4">Pengiriman Paket</h3>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                     <div v-for="(items, routeName) in manifestReport.packageRoutes" :key="'pkg-'+routeName" class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden flex flex-col">
                        <div class="p-4 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-orange-50 dark:bg-orange-900/10">
                            <div>
                                <h3 class="font-bold text-slate-800 dark:text-white">{{ routeName }}</h3>
                                <div class="text-xs text-slate-500">{{ items.length }} Paket</div>
                            </div>
                            <div class="text-right">
                                <div class="font-bold text-orange-600 dark:text-orange-400">{{ formatRupiah(items.reduce((s, i) => s + i.price, 0)) }}</div>
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left">
                                <thead class="text-xs text-slate-500 uppercase bg-slate-50 dark:bg-slate-700/50">
                                    <tr>
                                        <th class="px-4 py-3 font-bold">Resi</th>
                                        <th class="px-4 py-3 font-bold">Item</th>
                                        <th class="px-4 py-3 font-bold text-right">Harga</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                    <tr v-for="item in items" :key="item.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                        <td class="px-4 py-3">
                                            <div class="font-medium text-slate-800 dark:text-slate-200">{{ item.receiptNumber }}</div>
                                            <div class="text-[10px] text-slate-400">{{ item.senderName }} -> {{ item.receiverName }}</div>
                                        </td>
                                        <td class="px-4 py-3 text-slate-500">{{ item.itemDescription }}</td>
                                        <td class="px-4 py-3 text-right font-bold text-slate-700 dark:text-slate-300">
                                            {{ formatRupiah(item.price) }}
                                            <div v-if="item.paymentStatus !== 'Lunas'" class="text-[10px] text-red-500">Belum Bayar</div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                     </div>
                </div>
            </div>

            <!-- REFUNDS -->
            <div v-if="manifestReport.refunds.length > 0" class="mt-6">
                <div class="bg-white dark:bg-slate-800 p-0 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden flex flex-col">
                    <div class="p-4 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-red-50 dark:bg-red-900/10">
                        <div>
                            <h3 class="font-bold text-slate-800 dark:text-white">Riwayat Pembatalan / Refund</h3>
                            <div class="text-xs text-slate-500">{{ manifestReport.refunds.length }} Tiket Dibatalkan</div>
                        </div>
                        <div class="text-right">
                            <div class="font-bold text-red-600 dark:text-red-400">{{ formatRupiah(manifestReport.grandTotal.totalPotongan) }}</div>
                            <div class="text-[10px] text-slate-400">Total Pendapatan Fee</div>
                        </div>
                    </div>
                     <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-slate-500 uppercase bg-slate-50 dark:bg-slate-700/50">
                                <tr>
                                    <th class="px-4 py-3 font-bold text-slate-500">WAKTU</th>
                                    <th class="px-4 py-3 font-bold text-slate-500">NAMA</th>
                                    <th class="px-4 py-3 font-bold text-right text-red-500">REFUND</th>
                                    <th class="px-4 py-3 font-bold text-right text-red-500">POTONGAN</th>
                                    <th class="px-4 py-3 font-bold text-center text-slate-500">STATUS</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                <tr v-for="ref in manifestReport.refunds" :key="ref.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                    <td class="px-4 py-3 font-mono text-slate-500">{{ ref.time }}</td>
                                    <td class="px-4 py-3">
                                        <div class="font-bold text-slate-800 dark:text-slate-200">{{ ref.passengerName }}</div>
                                        <div class="text-[10px] text-slate-400">{{ ref.routeName || ref.routeId }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-right font-medium text-slate-500">{{ formatRupiah(ref.refund_amount) }}</td>
                                    <td class="px-4 py-3 text-right font-bold text-red-600">{{ formatRupiah(ref.potongan) }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="bg-red-100 text-red-800 text-[10px] font-bold px-2 py-1 rounded uppercase">BATAL</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

    </div>

    <!-- Modal Logic (Existing + potentially new details) -->
    <div v-if="detailModal.isOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" @click.self="detailModal.isOpen = false">
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl w-full max-w-2xl max-h-[80vh] flex flex-col overflow-hidden animate-fade-in-up">
            <div class="p-4 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-slate-50 dark:bg-slate-800/50">
                <h3 class="font-bold text-lg text-slate-800 dark:text-white">{{ detailModal.title }}</h3>
                <button @click="detailModal.isOpen = false" class="p-2 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-full transition-colors text-slate-500">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div class="overflow-y-auto p-0 md:p-4 custom-scrollbar">
                <!-- Dynamic Tables based on Type -->
                 <table v-if="detailModal.type === 'income'" class="w-full text-sm text-left">
                    <thead class="text-xs text-slate-500 uppercase bg-slate-50 dark:bg-slate-700/50 sticky top-0">
                        <tr>
                            <th class="px-4 py-3">Nama</th>
                            <th class="px-4 py-3">Rute</th>
                             <th class="px-4 py-3">Metode</th>
                            <th class="px-4 py-3 text-right">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        <tr v-for="item in detailModal.data" :key="item.id">
                            <td class="px-4 py-3 font-bold text-slate-800 dark:text-white">{{ item.name }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ item.route }}</td>
                            <td class="px-4 py-3"><span class="px-2 py-1 rounded text-xs font-bold" :class="item.method === 'Cash' ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700'">{{ item.method }}</span></td>
                            <td class="px-4 py-3 text-right font-bold">{{ formatRupiah(item.amount) }}</td>
                        </tr>
                    </tbody>
                 </table>

                 <table v-if="detailModal.type === 'passengers'" class="w-full text-sm text-left">
                    <thead class="text-xs text-slate-500 uppercase bg-slate-50 dark:bg-slate-700/50 sticky top-0">
                        <tr>
                            <th class="px-4 py-3">Nama</th>
                            <th class="px-4 py-3">Rute</th>
                            <th class="px-4 py-3">Kursi</th>
                            <th class="px-4 py-3">No. HP</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        <tr v-for="item in detailModal.data" :key="item.id">
                            <td class="px-4 py-3 font-bold text-slate-800 dark:text-white">{{ item.name }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ item.route }}</td>
                            <td class="px-4 py-3 font-mono font-bold">{{ item.seat }}</td>
                            <td class="px-4 py-3 text-xs text-slate-500">{{ item.phone }}</td>
                        </tr>
                    </tbody>
                 </table>
                 
                 <!-- Departure Detail (Existing) -->
                 <table v-if="detailModal.type === 'departure_detail'" class="w-full text-sm text-left">
                    <thead class="text-xs text-slate-500 uppercase bg-slate-50 dark:bg-slate-700/50 sticky top-0">
                        <tr>
                            <th class="px-4 py-3">Kursi</th>
                            <th class="px-4 py-3">Penumpang</th>
                            <th class="px-4 py-3 text-right">Total</th>
                            <th class="px-4 py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        <tr v-for="item in detailModal.data" :key="item.id">
                            <td class="px-4 py-3 font-mono font-bold text-center bg-slate-50 dark:bg-slate-700/30">{{ item.seat }}</td>
                            <td class="px-4 py-3 text-slate-800 dark:text-white">
                                <span class="font-bold">{{ item.name }}</span>
                                <div class="text-[10px] text-slate-400">{{ item.phone }}</div>
                            </td>
                            <td class="px-4 py-3 text-right font-mono font-bold text-blue-600 dark:text-blue-400">{{ formatRupiah(item.total) }}</td>
                            <td class="px-4 py-3 text-center">
                                <div v-if="item.status === 'Lunas'" class="inline-flex flex-col items-center">
                                    <span class="text-[10px] font-bold text-green-600 bg-green-50 px-2 py-0.5 rounded-full mb-0.5">LUNAS</span>
                                    <span class="text-[9px] text-slate-400 uppercase">{{ item.method }}</span>
                                </div>
                                <div v-else class="inline-flex flex-col items-center">
                                     <span class="text-[10px] font-bold text-red-600 bg-red-50 px-2 py-0.5 rounded-full mb-0.5">BELUM</span>
                                     <span v-if="item.paid > 0" class="text-[9px] text-green-600">DP: {{ formatRupiah(item.paid) }}</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                 </table>
            </div>
        </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

const isLoading = ref(true);
const manifestDate = ref('');
const bookings = ref([]);
const packages = ref([]); // NEW
const refunds = ref([]); // NEW

// Modal State
const detailModal = ref({
    isOpen: false,
    title: '',
    type: 'income',
    data: []
});

// Set default date to today
const today = new Date();
const y = today.getFullYear();
const m = String(today.getMonth() + 1).padStart(2, '0');
const d = String(today.getDate()).padStart(2, '0');
manifestDate.value = `${y}-${m}-${d}`;

const formatRupiah = (val) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val || 0);

const getDayName = (dateStr) => {
    if (!dateStr) return '';
    return new Date(dateStr).toLocaleDateString('id-ID', { weekday: 'long' });
};

// Computed Report Logic
const manifestReport = computed(() => {
    const report = {
        routes: {},
        charters: [],
        droppings: [],
        charterTotal: { totalPrice: 0, paidAmount: 0, remainingAmount: 0 },
        droppingTotal: { totalPrice: 0, paidAmount: 0, remainingAmount: 0 },
        grandTotal: { 
            umumPax: 0, umumNominal: 0, 
            pelajarPax: 0, pelajarNominal: 0, 
            totalPax: 0, totalNominal: 0, 
            unpaidAmount: 0,
            totalPackage: 0,
            totalRefund: 0,
            totalPotongan: 0,
            netIncome: 0 
        },
        packageRoutes: {},
        packageCount: 0,
        refunds: []
    };

    // 1. Process Bookings
    const dailyBookings = bookings.value; // Already filtered by date from backend

    // Charters
    report.charters = dailyBookings.filter(b => b.serviceType === 'Carter').map(b => {
        const paid = b.paymentStatus === 'Lunas' ? b.totalPrice : (b.downPaymentAmount || 0);
        const remain = b.totalPrice - paid;
        
        report.charterTotal.totalPrice += (b.totalPrice || 0);
        report.charterTotal.paidAmount += paid;
        report.charterTotal.remainingAmount += remain;

        return {
            id: b.id,
            date: b.date,
            route: b.routeName || b.routeId,
            totalPrice: b.totalPrice || 0,
            paidAmount: paid,
            remainingAmount: remain
        };
    });

    // Dropping
    report.droppings = dailyBookings.filter(b => b.serviceType === 'Dropping').map(b => {
        const paid = b.paymentStatus === 'Lunas' ? b.totalPrice : (b.downPaymentAmount || 0);
        const remain = b.totalPrice - paid;
        
        report.droppingTotal.totalPrice += (b.totalPrice || 0);
        report.droppingTotal.paidAmount += paid;
        report.droppingTotal.remainingAmount += remain;

        return {
            id: b.id,
            date: b.date,
            route: b.routeName || b.routeId,
            totalPrice: b.totalPrice || 0,
            paidAmount: paid,
            remainingAmount: remain
        };
    });

    // Regular Routes
    const regularBookings = dailyBookings.filter(b => b.serviceType === 'Travel');
    const routeGroups = {};
    regularBookings.forEach(b => {
        let rName = b.routeName || b.routeId || 'Lainnya';
        if (b.routeId && String(b.routeId).startsWith('CUSTOM_')) rName = 'Carter Khusus';
        
        // Grouping Logic (Normalization)
        let normalizedName = rName;
        const rLower = rName.toLowerCase();
        
        if (rLower.includes('carter')) normalizedName = 'Carter';
        else if (rLower.includes('dropping')) normalizedName = 'Dropping';
        else if (rLower.includes('payakumbuh') && rLower.includes('padang')) {
             const idxPadang = rLower.indexOf('padang');
             const idxPayakumbuh = rLower.indexOf('payakumbuh');
             normalizedName = (idxPadang < idxPayakumbuh) ? 'Padang - Payakumbuh' : 'Payakumbuh - Padang';
        } else if (rLower.includes('bukittinggi') && rLower.includes('padang')) {
             const idxPadang = rLower.indexOf('padang');
             const idxBukittinggi = rLower.indexOf('bukittinggi');
             normalizedName = (idxPadang < idxBukittinggi) ? 'Padang - Bukittinggi' : 'Bukittinggi - Padang';
        } else if (rLower.includes('pekanbaru') && rLower.includes('padang')) {
             const idxPadang = rLower.indexOf('padang');
             const idxPekanbaru = rLower.indexOf('pekanbaru');
             normalizedName = (idxPadang < idxPekanbaru) ? 'Padang - Pekanbaru' : 'Pekanbaru - Padang';
        } else if (rLower.includes('pekanbaru') && rLower.includes('bukittinggi')) {
             const idxBkt = rLower.indexOf('bukittinggi');
             const idxPekanbaru = rLower.indexOf('pekanbaru');
             normalizedName = (idxPekanbaru < idxBkt) ? 'Pekanbaru - Bukittinggi' : 'Bukittinggi - Pekanbaru';
        } else if (rLower.includes('pekanbaru') && rLower.includes('payakumbuh')) {
             const idxPyk = rLower.indexOf('payakumbuh');
             const idxPekanbaru = rLower.indexOf('pekanbaru');
             normalizedName = (idxPekanbaru < idxPyk) ? 'Pekanbaru - Payakumbuh' : 'Payakumbuh - Pekanbaru';
        }

        if (!routeGroups[normalizedName]) routeGroups[normalizedName] = [];
        routeGroups[normalizedName].push(b);
    });

    for (const [rName, bks] of Object.entries(routeGroups)) {
        const rows = {};
        const total = { umumPax: 0, umumNominal: 0, pelajarPax: 0, pelajarNominal: 0, totalPax: 0, totalNominal: 0 };

        bks.forEach(b => {
            const time = b.time || '00:00';
            const batch = b.batchNumber || 1;
            const key = `${time}-B${batch}`;
            
            if (!rows[key]) rows[key] = { time, batch, bookings: [], umumPax: 0, umumNominal: 0, pelajarPax: 0, pelajarNominal: 0, totalPax: 0, totalNominal: 0 };
            
            rows[key].bookings.push(b);

            const isPelajar = b.passengerType === 'Pelajar';
            const pax = parseInt(b.seatCount) || 1;
            const price = parseFloat(b.totalPrice) || 0;

            if (isPelajar) {
                rows[key].pelajarPax += pax;
                rows[key].pelajarNominal += price;
                total.pelajarPax += pax;
                total.pelajarNominal += price;
            } else {
                rows[key].umumPax += pax;
                rows[key].umumNominal += price;
                total.umumPax += pax;
                total.umumNominal += price;
            }
            
            rows[key].totalPax += pax;
            rows[key].totalNominal += price;
            total.totalPax += pax;
            total.totalNominal += price;
        });

        report.routes[rName] = {
            rows: Object.values(rows).sort((a, b) => a.time.localeCompare(b.time)),
            total: total
        };

        const unpaid = bks.filter(b => b.paymentStatus !== 'Lunas').reduce((sum, b) => sum + (b.totalPrice - (b.downPaymentAmount||0)), 0);

        report.grandTotal.umumPax += total.umumPax;
        report.grandTotal.umumNominal += total.umumNominal;
        report.grandTotal.pelajarPax += total.pelajarPax;
        report.grandTotal.pelajarNominal += total.pelajarNominal;
        report.grandTotal.totalPax += total.totalPax;
        report.grandTotal.totalNominal += total.totalNominal;
        report.grandTotal.unpaidAmount += unpaid;
    }

    if (report.charters.length > 0) {
        report.grandTotal.totalNominal += report.charterTotal.totalPrice;
        report.grandTotal.unpaidAmount += report.charterTotal.remainingAmount;
        const charterPax = dailyBookings.filter(b => b.serviceType === 'Carter').reduce((sum, b) => sum + (parseInt(b.seatCount) || 1), 0);
        report.grandTotal.totalPax += charterPax;
        report.grandTotal.umumPax += charterPax;
    }

    if (report.droppings.length > 0) {
        report.grandTotal.totalNominal += report.droppingTotal.totalPrice;
        report.grandTotal.unpaidAmount += report.droppingTotal.remainingAmount;
        const droppingPax = dailyBookings.filter(b => b.serviceType === 'Dropping').reduce((sum, b) => sum + (parseInt(b.seatCount) || 1), 0);
        report.grandTotal.totalPax += droppingPax;
        report.grandTotal.umumPax += droppingPax;
    }

    // 2. Process Packages
    report.packageRoutes = {};
    packages.value.forEach(p => {
        const route = p.route || 'Lainnya';
        if (!report.packageRoutes[route]) report.packageRoutes[route] = [];
        report.packageRoutes[route].push(p);
        
        report.grandTotal.totalPackage += p.price;
        report.packageCount++;
    });

    // 3. Process Refunds
    report.refunds = refunds.value.map(r => {
        // Calculate Paid Amount (Logic: same as backend)
        const paid = (r.paymentStatus === 'Lunas' || r.validationStatus === 'Valid') ? r.totalPrice : (r.downPaymentAmount || 0);
        const potongan = paid - r.refund_amount;
        
        report.grandTotal.totalRefund += (r.refund_amount || 0);
        report.grandTotal.totalPotongan += potongan;
        
        return {
            ...r,
            paidAmount: paid,
            potongan: potongan
        };
    });

    // 4. Calculate Net Income
    // Total Nominal accounts for Bookings + Packages.
    report.grandTotal.totalNominal += report.grandTotal.totalPackage;
    
    // Net Income = Bookings + Packages + Potongan (Cancellation Fees)
    // Note: Cancelled Bookings are NOT in 'bookings' (they are in cancelled_bookings), so their 'Original Price' is NOT in totalNominal.
    // So we assume totalNominal is "Revenue from Active Bookings" (Clean).
    // So we just ADD the "Potongan" (Revenue from Cancelled Bookings).
    // And subtract nothing (since RefundAmount is money out from money we don't count here anyway? No wait).
    // If we received 100k, and refunded 75k. We kept 25k.
    // That 25k is the Potongan.
    // So 'Net Income' = Active Revenue + Cancelled Revenue (Potongan).
    report.grandTotal.netIncome = report.grandTotal.totalNominal + report.grandTotal.totalPotongan;

    return report;
});

const loadData = async () => {
    isLoading.value = true;
    try {
        const res = await axios.get('api.php', {
            params: {
                action: 'get_daily_manifest',
                date: manifestDate.value
            }
        });
        
        if (res.data.status === 'success') {
            bookings.value = res.data.bookings || [];
            packages.value = res.data.packages || [];
            refunds.value = res.data.refunds || [];
        }
    } catch (e) {
        console.error(e);
        // Fallback for demo or error handling - clear data
        bookings.value = [];
        packages.value = [];
        refunds.value = [];
    } finally {
        isLoading.value = false;
    }
};

const openRowDetail = (row, routeName) => {
    detailModal.value.type = 'departure_detail';
    detailModal.value.isOpen = true;
    detailModal.value.title = `Detail: ${routeName} (${row.time})`;
    
    detailModal.value.data = row.bookings.map(b => ({
        id: b.id,
        name: b.passengerName,
        phone: b.passengerPhone,
        seat: b.selectedSeats ? b.selectedSeats.join(', ') : (b.seatNumbers || '-'),
        total: b.totalPrice,
        paid: b.paymentStatus === 'Lunas' ? b.totalPrice : (b.downPaymentAmount || 0),
        status: b.paymentStatus,
        method: b.paymentMethod
    })).sort((a,b) => a.seat.localeCompare(b.seat));
};

onMounted(() => {
    loadData();
});
</script>
