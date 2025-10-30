const { createApp } = Vue;

// ===================================================================================
// BAGIAN 1: DATA & FUNGSI PEMBANTU (UTILS)
// Semua data dan fungsi helper digabungkan di sini untuk kemudahan.
// ===================================================================================

function getTodayISO(h, m) {
    const d = new Date();
    d.setHours(h, m, 0, 0);
    const offset = d.getTimezoneOffset();
    const localDate = new Date(d.getTime() - (offset * 60 * 1000));
    return localDate.toISOString().slice(0, 16);
}

function getYesterdayISO(h, m) {
    const d = new Date();
    d.setDate(d.getDate() - 1);
    d.setHours(h, m, 0, 0);
    const offset = d.getTimezoneOffset();
    const localDate = new Date(d.getTime() - (offset * 60 * 1000));
    return localDate.toISOString().slice(0, 16);
}

function getFutureISO(days, h, m) {
    const d = new Date();
    d.setDate(d.getDate() + days);
    d.setHours(h, m, 0, 0);
    const offset = d.getTimezoneOffset();
    const localDate = new Date(d.getTime() - (offset * 60 * 1000));
    return localDate.toISOString().slice(0, 16);
}

function formatRupiah(number) {
    if (isNaN(number)) return 'Rp 0';
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number);
}

function getCurrentDate() {
    return new Date().toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
}

function formatTime(date, withSeconds = false) {
    const d = new Date(date);
    if (isNaN(d.getTime())) return '--:--';
    const options = { hour: '2-digit', minute: '2-digit', hour12: false };
    if (withSeconds) options.second = '2-digit';
    return d.toLocaleTimeString('id-ID', options).replace(/\./g, ':');
}

function formatFullDate(isoString) {
    if (!isoString) return '-';
    return new Date(isoString).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
}

function formatDateTime(isoString) {
    if (!isoString) return '-';
    const d = new Date(isoString);
    if (isNaN(d.getTime())) return '-';
    const datePart = d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short' });
    const timePart = formatTime(d);
    return `${datePart} ${timePart}`;
}

function isServiceDue(nextServiceDate, now) {
    if (!nextServiceDate) return false;
    const nextService = new Date(nextServiceDate);
    if (isNaN(nextService.getTime())) return false;
    const thirtyDaysFromNow = new Date(now);
    thirtyDaysFromNow.setDate(now.getDate() + 30);
    return nextService < thirtyDaysFromNow;
}

function getVehicleStatusClass(status) {
    const classes = { 'Tersedia': 'bg-green-100 text-green-800', 'Dalam Perjalanan': 'bg-blue-100 text-blue-800', 'Perbaikan': 'bg-red-100 text-red-800' };
    return classes[status] || 'bg-gray-100 text-gray-800';
}

function getDriverStatusClass(status) {
    const classes = { 'Standby': 'bg-green-100 text-green-800', 'Dalam Perjalanan': 'bg-blue-100 text-blue-800', 'Libur': 'bg-gray-200 text-gray-700' };
    return classes[status] || 'bg-yellow-100 text-yellow-800';
}

function calculateDurationInDays(start, end) {
    const startDate = new Date(start);
    const endDate = new Date(end);
    if (isNaN(startDate.getTime()) || isNaN(endDate.getTime()) || endDate < startDate) return 1;
    const diffTime = Math.abs(endDate - startDate);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays < 1 ? 1 : diffDays;
}

const initialData = {
    fleet: [
        { id: 1, name: 'Hiace Premio SR-01', type: 'Hiace Premio', plate: 'BA 1001 HP', capacity: 7, status: 'Dalam Perjalanan', icon: 'bi-truck-front-fill', hargaSewa: 1600000, hargaPerOrang: 150000, biayaOperasional: 600000, requiredLicense: 'A Umum', lastService: '2025-08-01', nextService: '2026-02-01' },
        { id: 2, name: 'Hiace Premio SR-02', type: 'Hiace Premio', plate: 'BA 1002 HP', capacity: 7, status: 'Tersedia', icon: 'bi-truck-front-fill', hargaSewa: 1600000, hargaPerOrang: 150000, biayaOperasional: 600000, requiredLicense: 'A Umum', lastService: '2025-08-01', nextService: '2026-02-01' },
        { id: 3, name: 'Hiace Commuter SR-03', type: 'Hiace Commuter', plate: 'BA 1003 HC', capacity: 14, status: 'Dalam Perjalanan', icon: 'bi-truck-front-fill', hargaSewa: 1500000, hargaPerOrang: 130000, biayaOperasional: 550000, requiredLicense: 'A Umum', lastService: '2025-09-15', nextService: '2026-03-15' },
        { id: 4, name: 'Hiace Premio SR-04', type: 'Hiace Premio', plate: 'BA 1004 HP', capacity: 7, status: 'Tersedia', icon: 'bi-truck-front-fill', hargaSewa: 1600000, hargaPerOrang: 150000, biayaOperasional: 600000, requiredLicense: 'A Umum', lastService: '2025-08-01', nextService: '2025-10-25' },
        { id: 5, name: 'Hiace Commuter SR-05', type: 'Hiace Commuter', plate: 'BA 1005 HC', capacity: 14, status: 'Tersedia', icon: 'bi-truck-front-fill', hargaSewa: 1500000, hargaPerOrang: 130000, biayaOperasional: 550000, requiredLicense: 'A Umum', lastService: '2025-09-15', nextService: '2026-03-15' },
        { id: 13, name: 'Hiace Premio SR-13', type: 'Hiace Premio', plate: 'BA 1013 HP', capacity: 7, status: 'Perbaikan', icon: 'bi-truck-front-fill', hargaSewa: 1600000, hargaPerOrang: 150000, biayaOperasional: 600000, requiredLicense: 'A Umum', lastService: '2025-08-01', nextService: '2026-02-01' },
        { id: 14, name: 'Medium Bus SR-21', type: 'Medium Bus', plate: 'BA 7021 MB', capacity: 33, status: 'Dalam Perjalanan', icon: 'bi-bus-front-fill', hargaSewa: 2800000, biayaOperasional: 1100000, requiredLicense: 'B1 Umum', lastService: '2025-07-20', nextService: '2025-11-20' },
        { id: 15, name: 'Medium Bus SR-22', type: 'Medium Bus', plate: 'BA 7022 MB', capacity: 33, status: 'Tersedia', icon: 'bi-bus-front-fill', hargaSewa: 2800000, biayaOperasional: 1100000, requiredLicense: 'B1 Umum', lastService: '2025-07-20', nextService: '2025-11-20' },
        { id: 24, name: 'Medium Bus SR-31', type: 'Medium Bus', plate: 'BA 7031 MB', capacity: 33, status: 'Perbaikan', icon: 'bi-bus-front-fill', hargaSewa: 2800000, biayaOperasional: 1100000, requiredLicense: 'B1 Umum', lastService: '2025-07-20', nextService: '2025-11-20' },
        { id: 25, name: 'Big Bus SR-41', type: 'Big Bus', plate: 'BA 7041 BB', capacity: 45, status: 'Dalam Perjalanan', icon: 'bi-bus-front-fill', hargaSewa: 4000000, biayaOperasional: 1500000, requiredLicense: 'B2 Umum', lastService: '2025-09-01', nextService: '2025-12-01' },
        { id: 26, name: 'Big Bus SR-42', type: 'Big Bus', plate: 'BA 7042 BB', capacity: 45, status: 'Dalam Perjalanan', icon: 'bi-bus-front-fill', hargaSewa: 4000000, biayaOperasional: 1500000, requiredLicense: 'B2 Umum', lastService: '2025-09-01', nextService: '2025-12-01' },
        { id: 27, name: 'Big Bus SR-43', type: 'Big Bus', plate: 'BA 7043 BB', capacity: 45, status: 'Tersedia', icon: 'bi-bus-front-fill', hargaSewa: 4000000, biayaOperasional: 1500000, requiredLicense: 'B2 Umum', lastService: '2025-09-01', nextService: '2025-12-01' },
    ],
    drivers: [
        { id: 101, name: 'Budi Santoso', licenseType: 'A Umum', phone: '081234567890', status: 'Dalam Perjalanan' },
        { id: 102, name: 'Joko Susilo', licenseType: 'B1 Umum', phone: '081234567891', status: 'Dalam Perjalanan' },
        { id: 103, name: 'Anton Wijaya', licenseType: 'A Umum', phone: '081234567892', status: 'Dalam Perjalanan' },
        { id: 104, name: 'Eko Prasetyo', licenseType: 'B2 Umum', phone: '081234567893', status: 'Dalam Perjalanan' },
        { id: 105, name: 'Slamet Riyadi', licenseType: 'B2 Umum', phone: '081234567894', status: 'Libur' },
        { id: 106, name: 'Doni Firmansyah', licenseType: 'A Umum', phone: '081234567895', status: 'Standby' },
        { id: 107, name: 'Agus Setiawan', licenseType: 'B1 Umum', phone: '081234567896', status: 'Standby' },
        { id: 108, name: 'Rahmat Hidayat', licenseType: 'A Umum', phone: '081234567897', status: 'Standby' },
        { id: 110, name: 'Fajar Nugraha', licenseType: 'B2 Umum', phone: '081234567899', status: 'Dalam Perjalanan' },
    ],
    trips: [
        { id: 1, fleetId: 1, driverId: 101, type: 'Rute', origin: 'Padang', destination: 'Bukittinggi', departureTime: getTodayISO(15, 0), arrivalTime: getTodayISO(17, 30), pax: '7/7', status: 'Berangkat' },
        { id: 2, fleetId: 14, driverId: 102, type: 'Rental', notes: 'Dinas Pariwisata - Tour de Singkarak', departureTime: getTodayISO(7, 30), arrivalTime: getTodayISO(18, 0), pax: 'Carter', status: 'Rental' },
        { id: 3, fleetId: 3, driverId: 103, type: 'Rute', origin: 'Padang', destination: 'Payakumbuh', departureTime: getTodayISO(16, 0), arrivalTime: getTodayISO(19, 0), pax: '10/14', status: 'Boarding' },
        { id: 4, fleetId: 25, driverId: 104, type: 'Rental', notes: 'Rombongan Kemenkes RI (3 Hari)', departureTime: getYesterdayISO(9, 0), arrivalTime: getFutureISO(1, 17, 0), pax: 'Carter', status: 'Rental' },
        { id: 5, fleetId: 26, driverId: 110, type: 'Rute', origin: 'Bukittinggi', destination: 'Padang', departureTime: getTodayISO(14, 0), arrivalTime: getTodayISO(16, 30), pax: '40/45', status: 'Berangkat' },
        { id: 6, fleetId: 4, driverId: 106, type: 'Rute', origin: 'Padang', destination: 'Solok', departureTime: getTodayISO(17, 30), arrivalTime: getTodayISO(19, 30), pax: 'Penuh', status: 'On Time' },
        { id: 7, fleetId: 2, driverId: 108, type: 'Rute', origin: 'Painan', destination: 'Padang', departureTime: getYesterdayISO(18, 0), arrivalTime: getTodayISO(8, 30), pax: '6/7', status: 'Tiba' },
        { id: 8, fleetId: 15, driverId: 107, type: 'Rental', notes: "Trip Keluarga Bpk. Ahmad", departureTime: getYesterdayISO(9, 0), arrivalTime: getYesterdayISO(20, 0), pax: 'Carter', status: 'Tiba' },
    ],
    locations: [
        { name: 'Padang', code: 'PDG' }, { name: 'Bukittinggi', code: 'BKT' }, { name: 'Payakumbuh', code: 'PYH' }, { name: 'Solok', code: 'SLK' }, { name: 'Pariaman', code: 'PRM' }, { name: 'Painan', code: 'PNN' }, { name: 'Sawahlunto', code: 'SWL' }, { name: 'Batusangkar', code: 'BTS' }
    ],
    statuses: ['On Time', 'Boarding', 'Berangkat', 'Tiba', 'Tertunda', 'Batal', 'Rental'],
};

// ===================================================================================
// BAGIAN 2: TEMPLATE HTML UNTUK SETIAP KOMPONEN/HALAMAN
// Ini adalah KUNCI untuk memperbaiki masalah menu tidak tampil.
// ===================================================================================

const dashboardTemplate = `
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 fade-slide-up">
    <div class="lg:col-span-2 space-y-8">
        <div class="bg-white p-6 rounded-xl shadow-lg">
            <h2 class="text-2xl font-bold text-sutan-blue-900 mb-4 flex items-center"><i class="bi bi-box-arrow-up-right mr-3 text-sutan-gold"></i>Keberangkatan</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="text-xs text-gray-500 uppercase"><tr class="border-b"><th class="p-3">Waktu</th><th class="p-3">Tujuan</th><th class="p-3">Armada</th><th class="p-3">Supir</th><th class="p-3">Status</th></tr></thead>
                    <tbody>
                        <tr v-for="trip in departures" :key="trip.id" @click="$root.openTripModal(trip)" class="border-b hover:bg-gray-50 cursor-pointer">
                            <td class="p-3 font-semibold text-lg">{{ formatTime(trip.departureTime) }}</td>
                            <td class="p-3 font-bold">{{ trip.destinationCode }}</td>
                            <td class="p-3 text-sm text-gray-600">{{ trip.fleet.name }}</td>
                            <td class="p-3 text-sm text-gray-600">{{ trip.driver.name }}</td>
                            <td class="p-3"><span :class="$root.statusClasses[trip.status]" class="px-2 py-1 rounded-full text-xs font-semibold">{{ trip.status }}</span></td>
                        </tr>
                        <tr v-if="departures.length === 0"><td colspan="5" class="p-8 text-center text-gray-400">Tidak ada jadwal keberangkatan aktif.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-lg">
            <h2 class="text-2xl font-bold text-sutan-blue-900 mb-4 flex items-center"><i class="bi bi-box-arrow-in-down-left mr-3 text-sutan-gold"></i>Kedatangan</h2>
             <div class="overflow-x-auto">
                <table class="w-full text-left">
                   <thead class="text-xs text-gray-500 uppercase"><tr class="border-b"><th class="p-3">ETA</th><th class="p-3">Asal</th><th class="p-3">Armada</th><th class="p-3">Supir</th><th class="p-3">Status</th></tr></thead>
                    <tbody>
                        <tr v-for="trip in arrivals" :key="trip.id" @click="$root.openTripModal(trip)" class="border-b hover:bg-gray-50 cursor-pointer">
                            <td class="p-3 font-semibold text-lg">{{ formatTime(trip.arrivalTime) }}</td>
                            <td class="p-3 font-bold">{{ trip.originCode }}</td>
                            <td class="p-3 text-sm text-gray-600">{{ trip.fleet.name }}</td>
                            <td class="p-3 text-sm text-gray-600">{{ trip.driver.name }}</td>
                            <td class="p-3"><span :class="$root.statusClasses[trip.status]" class="px-2 py-1 rounded-full text-xs font-semibold">{{ trip.status }}</span></td>
                        </tr>
                        <tr v-if="arrivals.length === 0"><td colspan="5" class="p-8 text-center text-gray-400">Tidak ada jadwal kedatangan aktif.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="space-y-8">
        <div class="bg-white p-6 rounded-xl shadow-lg">
            <h2 class="text-2xl font-bold text-sutan-blue-900 mb-4 flex items-center"><i class="bi bi-stickies-fill mr-3 text-sutan-gold"></i>Rental Aktif</h2>
            <div class="space-y-4 max-h-64 overflow-y-auto">
                <div v-for="trip in rental" :key="trip.id" @click="$root.openTripModal(trip)" class="p-4 border rounded-lg hover:bg-gray-50 cursor-pointer">
                    <div class="flex justify-between items-center">
                        <span class="font-bold text-sutan-blue-800 truncate pr-2">{{ trip.notes }}</span>
                        <span class="text-xs font-semibold bg-purple-100 text-purple-800 px-2 py-1 rounded-full flex-shrink-0">Rental</span>
                    </div>
                    <p class="text-sm text-gray-600">{{ trip.fleet.name }}</p>
                    <div class="text-xs text-gray-500 mt-2">
                        <span>Berangkat: {{ formatDateTime(trip.departureTime) }}</span><br>
                        <span>Kembali: {{ formatDateTime(trip.arrivalTime) }}</span>
                    </div>
                </div>
                <div v-if="rental.length === 0" class="text-center text-gray-400 pt-8"><p>Tidak ada armada yang sedang dirental.</p></div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-lg">
            <h2 class="text-2xl font-bold text-sutan-blue-900 mb-4 flex items-center"><i class="bi bi-truck-front mr-3 text-sutan-gold"></i>Armada Standby</h2>
            <button @click="$root.openTripWizard()" class="w-full bg-sutan-gold text-sutan-blue-900 font-bold py-3 rounded-lg mb-4 hover:opacity-90 transition-opacity tile"><i class="bi bi-plus-circle-fill mr-2"></i>Buat Jadwal Baru</button>
            <div class="space-y-2 max-h-64 overflow-y-auto">
                <div v-for="armada in armadaStandby" :key="armada.id" @click="$root.openTripWizard(armada.id)" class="flex items-center justify-between p-2 bg-green-50 rounded-lg cursor-pointer hover:bg-green-100 tile">
                    <div class="flex items-center"><i :class="armada.icon" class="text-green-700 text-2xl mr-3"></i><div><span class="font-semibold text-sm">{{ armada.name }}</span><p class="text-xs text-gray-500">{{ armada.plate }}</p></div></div>
                    <i class="bi bi-chevron-right text-gray-400"></i>
                </div>
                <div v-if="armadaStandby.length === 0" class="text-center text-gray-400 pt-4"><p>Semua armada sedang beroperasi.</p></div>
            </div>
        </div>
    </div>
</div>`;

const inventarisTemplate = `
<div class="fade-slide-up">
    <div class="mb-6 flex justify-between items-center">
        <div class="relative w-1/3"><i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i><input type="text" v-model="$root.armadaSearchTerm" placeholder="Cari nama, plat, atau status armada..." class="w-full p-2 pl-10 border rounded-lg"></div>
        <button @click="$root.openVehicleModal(null)" class="bg-sutan-blue-800 text-white font-bold py-2 px-4 rounded-lg hover:bg-sutan-blue-700 transition-colors tile"><i class="bi bi-plus-lg mr-2"></i>Tambah Armada</button>
    </div>
    <div class="bg-white p-6 rounded-xl shadow-lg">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="text-xs text-gray-500 uppercase"><tr class="border-b"><th class="p-3">Nama Armada</th><th class="p-3">Tipe</th><th class="p-3">No. Polisi</th><th class="p-3">Kapasitas</th><th class="p-3">Status</th><th class="p-3">Jadwal Servis</th><th class="p-3">Aksi</th></tr></thead>
                <tbody>
                    <tr v-for="vehicle in filteredFleet" :key="vehicle.id" class="border-b hover:bg-gray-50">
                        <td class="p-3 font-semibold">{{ vehicle.name }}</td><td class="p-3">{{ vehicle.type }}</td><td class="p-3">{{ vehicle.plate }}</td><td class="p-3">{{ vehicle.capacity }} orang</td>
                        <td class="p-3"><span :class="getVehicleStatusClass(vehicle.status)" class="px-2 py-1 rounded-full text-xs font-semibold">{{ vehicle.status }}</span></td>
                        <td class="p-3"><span :class="{'text-red-500 font-bold': isServiceDue(vehicle.nextService, $root.now)}">{{ formatFullDate(vehicle.nextService) }}</span></td>
                        <td class="p-3"><button @click="$root.openVehicleModal(vehicle)" class="text-sutan-blue-800 hover:text-sutan-gold"><i class="bi bi-pencil-square"></i></button></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>`;

const driversTemplate = `
<div class="fade-slide-up">
    <div class="mb-6 flex justify-between items-center">
        <div class="relative w-1/3"><i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i><input type="text" v-model="$root.driverSearchTerm" placeholder="Cari nama supir..." class="w-full p-2 pl-10 border rounded-lg"></div>
        <button @click="$root.openDriverModal(null)" class="bg-sutan-blue-800 text-white font-bold py-2 px-4 rounded-lg hover:bg-sutan-blue-700 transition-colors tile"><i class="bi bi-person-plus-fill mr-2"></i>Tambah Supir</button>
    </div>
    <div class="bg-white p-6 rounded-xl shadow-lg">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="text-xs text-gray-500 uppercase"><tr class="border-b"><th class="p-3">Nama Supir</th><th class="p-3">Jenis SIM</th><th class="p-3">No. Telepon</th><th class="p-3">Status</th><th class="p-3">Aksi</th></tr></thead>
                <tbody>
                    <tr v-for="driver in filteredDrivers" :key="driver.id" class="border-b hover:bg-gray-50">
                        <td class="p-3 font-semibold">{{ driver.name }}</td><td class="p-3">{{ driver.licenseType }}</td><td class="p-3">{{ driver.phone }}</td>
                        <td class="p-3"><span :class="getDriverStatusClass(driver.status)" class="px-2 py-1 rounded-full text-xs font-semibold">{{ driver.status }}</span></td>
                        <td class="p-3"><button @click="$root.openDriverModal(driver)" class="text-sutan-blue-800 hover:text-sutan-gold"><i class="bi bi-pencil-square"></i></button></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>`;

const historyTemplate = `
<div class="fade-slide-up">
    <div class="mb-6"><div class="relative w-1/3"><i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i><input type="text" v-model="$root.historySearchTerm" placeholder="Cari riwayat (armada, tujuan, notes)..." class="w-full p-2 pl-10 border rounded-lg"></div></div>
    <div class="bg-white p-6 rounded-xl shadow-lg">
         <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="text-xs text-gray-500 uppercase"><tr class="border-b"><th class="p-3">Tanggal</th><th class="p-3">Armada</th><th class="p-3">Deskripsi Perjalanan</th><th class="p-3">Status Akhir</th><th class="p-3">Pendapatan</th><th class="p-3">Biaya</th><th class="p-3">Profit</th></tr></thead>
                 <tbody>
                    <tr v-for="trip in filteredHistory" :key="trip.id" class="border-b hover:bg-gray-50">
                        <td class="p-3">{{ formatFullDate(trip.departureTime) }}</td><td class="p-3 font-semibold">{{ trip.fleet.name }}</td>
                        <td class="p-3"><span v-if="trip.type === 'Rute'">Rute: {{ trip.origin }} -> {{ trip.destination }}</span><span v-else>Rental: {{ trip.notes }}</span></td>
                        <td class="p-3"><span :class="$root.statusClasses[trip.status]" class="px-2 py-1 rounded-full text-xs font-semibold">{{ trip.status }}</span></td>
                        <td class="p-3 text-green-600 font-semibold">{{ formatRupiah(trip.tripRevenue) }}</td><td class="p-3 text-red-600">({{ formatRupiah(trip.tripCost) }})</td>
                        <td class="p-3 font-bold" :class="trip.tripProfit > 0 ? 'text-blue-700' : 'text-gray-600'">{{ formatRupiah(trip.tripProfit) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>`;

const displayTemplate = `
<div class="bg-sutan-blue-900 text-white h-full flex flex-col p-8" :class="{'fixed inset-0 z-50 p-4 sm:p-8': $root.isFullscreen}">
    <header class="flex justify-between items-center pb-4 border-b-2 border-sutan-gold">
        <h1 class="text-4xl sm:text-6xl font-extrabold tracking-wider">SUTAN RAYA</h1>
        <div class="text-right"><div class="text-xl sm:text-3xl font-bold">{{ $root.currentTime }}</div><div class="text-base sm:text-xl">{{ $root.currentDate }}</div></div>
    </header>
    <main class="flex-1 grid grid-cols-1 lg:grid-cols-2 gap-8 pt-8 overflow-y-auto">
        <div>
            <h2 class="text-2xl sm:text-4xl font-bold text-sutan-gold mb-4 border-b border-sutan-gold pb-2">KEBERANGKATAN</h2>
            <transition-group tag="div" name="list" class="space-y-4">
                 <div v-for="trip in upcomingDeparturesForDisplay" :key="trip.id" class="bg-sutan-blue-800 p-4 rounded-lg flex items-center shadow-lg">
                    <div class="w-24 text-center"><div class="text-2xl sm:text-3xl font-bold">{{ formatTime(trip.departureTime) }}</div></div>
                    <div class="border-l-2 border-sutan-gold pl-4 flex-1">
                        <div class="text-xl sm:text-2xl font-semibold truncate">{{ trip.type === 'Rute' ? trip.destination : trip.notes }}</div>
                        <div class="text-base sm:text-lg text-gray-300">{{ trip.fleet.name }} - {{ trip.pax }}</div>
                    </div>
                    <div :class="$root.statusClasses[trip.status]" class="px-3 py-1 rounded-full text-base sm:text-lg font-semibold ml-4 flex-shrink-0">{{ trip.status }}</div>
                </div>
            </transition-group>
        </div>
         <div>
            <h2 class="text-2xl sm:text-4xl font-bold text-sutan-gold mb-4 border-b border-sutan-gold pb-2">KEDATANGAN</h2>
            <transition-group tag="div" name="list" class="space-y-4">
                 <div v-for="trip in upcomingArrivalsForDisplay" :key="trip.id" class="bg-sutan-blue-800 p-4 rounded-lg flex items-center shadow-lg">
                    <div class="w-24 text-center"><div class="text-2xl sm:text-3xl font-bold">{{ formatTime(trip.arrivalTime) }}</div></div>
                     <div class="border-l-2 border-sutan-gold pl-4 flex-1">
                        <div class="text-xl sm:text-2xl font-semibold truncate">{{ trip.origin }}</div>
                        <div class="text-base sm:text-lg text-gray-300">{{ trip.fleet.name }}</div>
                    </div>
                    <div :class="$root.statusClasses[trip.status]" class="px-3 py-1 rounded-full text-base sm:text-lg font-semibold ml-4 flex-shrink-0">{{ trip.status }}</div>
                </div>
            </transition-group>
        </div>
    </main>
</div>`;
    
const adminTemplate = `
<div class="max-w-4xl mx-auto space-y-8 fade-slide-up">
    <div class="bg-white p-6 rounded-xl shadow-lg">
        <h2 class="text-2xl font-bold text-sutan-blue-900 mb-4 flex items-center"><i class="bi bi-cash-coin mr-3"></i>Manajemen Harga</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-100 text-gray-700 uppercase text-sm"><tr><th class="p-3">Armada</th><th class="p-3">Harga Sewa/Hari</th><th class="p-3">Harga/Orang (Rute)</th><th class="p-3">Biaya Operasional/Hari</th></tr></thead>
                <tbody>
                    <tr v-for="armada in fleet" :key="armada.id" class="border-b">
                        <td class="p-3 font-semibold">{{ armada.name }}</td>
                        <td class="p-3"><input type="number" v-model.number="armada.hargaSewa" class="w-full p-2 border rounded-md"></td>
                        <td class="p-3"><input type="number" v-model.number="armada.hargaPerOrang" class="w-full p-2 border rounded-md"></td>
                        <td class="p-3"><input type="number" v-model.number="armada.biayaOperasional" class="w-full p-2 border rounded-md"></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <button @click="$root.savePrices" class="mt-4 bg-sutan-gold text-sutan-blue-900 font-bold py-2 px-6 rounded-lg hover:opacity-90 transition-opacity tile">Simpan Perubahan</button>
        <p v-if="$root.pricesSavedMessage" class="text-green-600 mt-2">{{ $root.pricesSavedMessage }}</p>
    </div>
</div>`;

// ===================================================================================
// BAGIAN 3: APLIKASI UTAMA VUE
// ===================================================================================

const app = createApp({
    data() {
        return {
            view: 'dashboard',
            isFullscreen: false,
            armadaSearchTerm: '',
            driverSearchTerm: '',
            driverSearchTermInModal: '',
            historySearchTerm: '',
            now: new Date(),
            currentTime: formatTime(new Date(), true),
            currentDate: getCurrentDate(),
            clockInterval: null,
            isTripWizardVisible: false,
            isTripModalVisible: false,
            isVehicleModalVisible: false,
            isDriverModalVisible: false,
            showDriverDropdown: false,
            wizard: {
                trip: null,
                step: 1,
                date: { type: 'today', raw: '' },
                time: '08:00',
                endDate: { raw: '' },
                endTime: '17:00'
            },
            modal: {
                trip: null,
                originalDriverId: null,
            },
            vehicleModal: {
                mode: 'add',
                data: null
            },
            driverModal: {
                mode: 'add',
                data: null
            },
            pricesSavedMessage: '',
            commonTimes: ['07:00', '08:00', '09:00', '10:00', '13:00', '14:00', '15:00', '16:00', '19:00', '20:00', '21:00', '22:00'],
            statusClasses: {
                'On Time': 'bg-green-100 text-green-800', 'Boarding': 'bg-yellow-100 text-yellow-800 animate-pulse', 'Berangkat': 'bg-blue-100 text-blue-800',
                'Tiba': 'bg-gray-200 text-gray-800', 'Tertunda': 'bg-red-100 text-red-800', 'Batal': 'bg-black text-white', 'Rental': 'bg-purple-100 text-purple-800'
            },
            ...initialData
        };
    },

    computed: {
        currentViewTitle() {
            const titles = { dashboard: 'Dashboard Operasional', inventaris: 'Manajemen Inventaris Armada', drivers: 'Manajemen Supir', history: 'Riwayat Perjalanan', display: 'Layar Informasi Publik', admin: 'Pengaturan Admin' };
            return titles[this.view] || 'Sutan Raya';
        },
        tripsWithDetails() {
            return this.trips.map(trip => {
                const fleet = this.fleet.find(f => f.id === trip.fleetId) || {};
                const driver = this.drivers.find(d => d.id === trip.driverId) || {};
                const originInfo = this.locations.find(l => l.name === trip.origin);
                const destinationInfo = this.locations.find(l => l.name === trip.destination);
                const durationDays = calculateDurationInDays(trip.departureTime, trip.arrivalTime);
                const tripCost = durationDays * (fleet.biayaOperasional || 0);
                let tripRevenue = 0;
                if (trip.status === 'Tiba') {
                    if (trip.type === 'Rental') {
                        tripRevenue = durationDays * (fleet.hargaSewa || 0);
                    } else if (trip.type === 'Rute' && fleet.hargaPerOrang) {
                        const paxString = trip.pax || "0/0";
                        if (paxString.toLowerCase() === 'penuh' || paxString.toLowerCase() === 'full') {
                            tripRevenue = (fleet.capacity || 0) * fleet.hargaPerOrang;
                        } else {
                            const paxCount = parseInt(paxString.split('/')[0], 10) || 0;
                            tripRevenue = paxCount * fleet.hargaPerOrang;
                        }
                    }
                }
                const tripProfit = (trip.status === 'Tiba' && tripRevenue > 0) ? tripRevenue - tripCost : 0;
                return { ...trip, fleet, driver, originCode: trip.type === 'Rute' ? (originInfo?.code || trip.origin) : 'Rental', destinationCode: trip.type === 'Rute' ? (destinationInfo?.code || trip.destination) : trip.notes, tripCost, tripRevenue, tripProfit, };
            }).sort((a,b) => new Date(a.departureTime) - new Date(b.departureTime));
        },
        activeTrips() { return this.tripsWithDetails.filter(t => t.status !== 'Tiba' && t.status !== 'Batal'); },
        departures() { return this.activeTrips; },
        arrivals() { return this.activeTrips; },
        rental() { return this.activeTrips.filter(t => t.type === 'Rental'); },
        armadaStandby() { return this.fleet.filter(f => f.status === 'Tersedia'); },
        filteredFleet() { if (!this.armadaSearchTerm) return this.fleet; const term = this.armadaSearchTerm.toLowerCase(); return this.fleet.filter(v => v.name.toLowerCase().includes(term) || v.plate.toLowerCase().includes(term) || v.status.toLowerCase().includes(term)); },
        filteredDrivers() { if (!this.driverSearchTerm) return this.drivers; const term = this.driverSearchTerm.toLowerCase(); return this.drivers.filter(d => d.name.toLowerCase().includes(term)); },
        filteredHistory() { const historyTrips = this.tripsWithDetails.filter(t => t.status === 'Tiba' || t.status === 'Batal').sort((a, b) => new Date(b.departureTime) - new Date(a.departureTime)); if (!this.historySearchTerm) return historyTrips; const term = this.historySearchTerm.toLowerCase(); return historyTrips.filter(t => t.fleet.name.toLowerCase().includes(term) || (t.destinationCode && t.destinationCode.toLowerCase().includes(term))); },
        upcomingDeparturesForDisplay() { return this.activeTrips.filter(t => t.status !== 'Tiba' && t.status !== 'Batal' && t.status !== 'Berangkat').sort((a, b) => new Date(a.departureTime) - new Date(b.departureTime)).slice(0, 10); },
        upcomingArrivalsForDisplay() { return this.activeTrips.filter(t => t.destination === 'Padang' && t.status !== 'Tiba' && t.status !== 'Batal').sort((a, b) => new Date(a.arrivalTime) - new Date(b.arrivalTime)).slice(0, 10); },
        availableFleetForTrip() { const trip = this.isTripWizardVisible ? this.wizard.trip : this.modal.trip; let available = this.fleet.filter(f => f.status === 'Tersedia'); if (trip && trip.fleetId) { const currentFleet = this.fleet.find(f => f.id === trip.fleetId); if (currentFleet && !available.some(f => f.id === currentFleet.id)) { available.unshift(currentFleet); } } return available; },
        availableDriversForTrip() { const trip = this.isTripWizardVisible ? this.wizard.trip : this.modal.trip; if (!trip || !trip.fleetId) return []; const selectedFleet = this.fleet.find(f => f.id === trip.fleetId); if (!selectedFleet) return []; let filtered = this.drivers.filter(d => (d.status === 'Standby' || d.id === trip.driverId) && d.licenseType === selectedFleet.requiredLicense); if (this.driverSearchTermInModal) { filtered = filtered.filter(d => d.name.toLowerCase().includes(this.driverSearchTermInModal.toLowerCase())); } return filtered; },
    },

    methods: {
        formatTime, formatFullDate, formatDateTime, formatRupiah, isServiceDue, getVehicleStatusClass, getDriverStatusClass, // Helper functions
        toggleFullscreen() { if (!document.fullscreenElement) { this.$el.requestFullscreen(); } else { if (document.exitFullscreen) { document.exitFullscreen(); } } },
        openTripWizard(standbyFleetId = null) { this.driverSearchTermInModal = ''; this.wizard.trip = { id: Date.now(), fleetId: standbyFleetId || null, driverId: null, type: 'Rute', origin: 'Padang', destination: 'Bukittinggi', notes: '', departureTime: '', arrivalTime: '', pax: '', status: 'On Time' }; this.setWizardDate('today'); this.wizard.time = '08:00'; const todayStr = new Date().toISOString().slice(0, 10); this.wizard.endDate.raw = todayStr; this.wizard.endTime = '17:00'; this.wizard.step = 1; this.isTripWizardVisible = true; },
        closeTripWizard() { this.isTripWizardVisible = false; },
        saveTripFromWizard() { if (!this.wizard.trip.fleetId || !this.wizard.trip.driverId) { alert('Silakan pilih Armada dan Supir.'); this.wizard.step = 3; return; } if (this.wizard.trip.type === 'Rute') { this.wizard.trip.departureTime = `${this.wizard.date.raw}T${this.wizard.time}`; const departureDate = new Date(this.wizard.trip.departureTime); departureDate.setMinutes(departureDate.getMinutes() + 150); this.wizard.trip.arrivalTime = departureDate.toISOString().slice(0, 16); } else { this.wizard.trip.departureTime = `${this.wizard.date.raw}T${this.wizard.time}`; this.wizard.trip.arrivalTime = `${this.wizard.endDate.raw}T${this.wizard.endTime}`; } this.trips.push(this.wizard.trip); this.updateFleetAndDriverStatusOnTripCreation(this.wizard.trip); this.closeTripWizard(); },
        setWizardDate(type) { this.wizard.date.type = type; const newDate = new Date(); if (type === 'tomorrow') { newDate.setDate(newDate.getDate() + 1); } this.wizard.date.raw = newDate.toISOString().slice(0, 10); },
        updateFleetAndDriverStatusOnTripCreation(trip) { const fleetIndex = this.fleet.findIndex(f => f.id === trip.fleetId); if (fleetIndex !== -1) { this.fleet[fleetIndex].status = 'Dalam Perjalanan'; } const driverIndex = this.drivers.findIndex(d => d.id === trip.driverId); if (driverIndex !== -1) { this.drivers[driverIndex].status = 'Dalam Perjalanan'; } },
        openTripModal(trip) { this.modal.trip = JSON.parse(JSON.stringify(trip)); this.modal.originalDriverId = trip.driverId; this.isTripModalVisible = true; },
        closeTripModal() { this.isTripModalVisible = false; },
        saveTripFromModal() { if (!this.modal.trip) return; const index = this.trips.findIndex(t => t.id === this.modal.trip.id); if (index !== -1) { this.updateDriverStatusOnTripChange(this.modal.originalDriverId, this.modal.trip.driverId, this.modal.trip.status); const { fleet, driver, originCode, destinationCode, tripCost, tripRevenue, tripProfit, ...cleanedTrip } = this.modal.trip; this.trips[index] = cleanedTrip; } this.closeTripModal(); },
        deleteTrip(tripId) { if (confirm('Apakah Anda yakin ingin menghapus jadwal perjalanan ini?')) { const tripIndex = this.trips.findIndex(t => t.id === tripId); if (tripIndex > -1) { const trip = this.trips[tripIndex]; this.setFleetAndDriverStatusToStandby(trip.fleetId, trip.driverId); this.trips.splice(tripIndex, 1); } this.closeTripModal(); } },
        updateTripStatusInModal(newStatus) { if (!this.modal.trip) return; const oldStatus = this.modal.trip.status; this.modal.trip.status = newStatus; if ((newStatus === 'Tiba' || newStatus === 'Batal') && oldStatus !== 'Tiba' && oldStatus !== 'Batal') { this.setFleetAndDriverStatusToStandby(this.modal.trip.fleetId, this.modal.trip.driverId); } else if (newStatus !== 'Tiba' && newStatus !== 'Batal' && (oldStatus === 'Tiba' || oldStatus === 'Batal')) { const fleetIndex = this.fleet.findIndex(f => f.id === this.modal.trip.fleetId); if (fleetIndex !== -1) this.fleet[fleetIndex].status = 'Dalam Perjalanan'; const driverIndex = this.drivers.findIndex(d => d.id === this.modal.trip.driverId); if (driverIndex !== -1) this.drivers[driverIndex].status = 'Dalam Perjalanan'; } },
        updateDriverStatusOnTripChange(originalDriverId, newDriverId, tripStatus) { if (originalDriverId && originalDriverId !== newDriverId) { const oldDriverIndex = this.drivers.findIndex(d => d.id === originalDriverId); if (oldDriverIndex !== -1) this.drivers[oldDriverIndex].status = 'Standby'; } const newDriverIndex = this.drivers.findIndex(d => d.id === newDriverId); if (newDriverIndex !== -1) { this.drivers[newDriverIndex].status = (tripStatus === 'Tiba' || tripStatus === 'Batal') ? 'Standby' : 'Dalam Perjalanan'; } },
        setFleetAndDriverStatusToStandby(fleetId, driverId) { const fleetIndex = this.fleet.findIndex(f => f.id === fleetId); if (fleetIndex !== -1) this.fleet[fleetIndex].status = 'Tersedia'; const driverIndex = this.drivers.findIndex(d => d.id === driverId); if (driverIndex !== -1) this.drivers[driverIndex].status = 'Standby'; },
        openVehicleModal(vehicle) { if (vehicle) { this.vehicleModal.mode = 'edit'; this.vehicleModal.data = JSON.parse(JSON.stringify(vehicle)); } else { this.vehicleModal.mode = 'add'; this.vehicleModal.data = { id: Date.now(), name: '', type: 'Hiace Premio', plate: '', capacity: 7, status: 'Tersedia', icon: 'bi-truck-front-fill', hargaSewa: 1600000, hargaPerOrang: 150000, biayaOperasional: 600000, requiredLicense: 'A Umum', lastService: new Date().toISOString().slice(0, 10), nextService: getFutureISO(180, 0, 0).slice(0, 10) }; } this.isVehicleModalVisible = true; },
        closeVehicleModal() { this.isVehicleModalVisible = false; },
        saveVehicle() { if (this.vehicleModal.data.type.toLowerCase().includes('bus')) { this.vehicleModal.data.icon = 'bi-bus-front-fill'; } else { this.vehicleModal.data.icon = 'bi-truck-front-fill'; } if (this.vehicleModal.mode === 'add') { this.fleet.push(this.vehicleModal.data); } else { const index = this.fleet.findIndex(f => f.id === this.vehicleModal.data.id); if (index !== -1) this.fleet[index] = this.vehicleModal.data; } this.closeVehicleModal(); },
        openDriverModal(driver) { if (driver) { this.driverModal.mode = 'edit'; this.driverModal.data = JSON.parse(JSON.stringify(driver)); } else { this.driverModal.mode = 'add'; this.driverModal.data = { id: Date.now(), name: '', licenseType: 'A Umum', phone: '', status: 'Standby' }; } this.isDriverModalVisible = true; },
        closeDriverModal() { this.isDriverModalVisible = false; },
        saveDriver() { if (this.driverModal.mode === 'add') { this.drivers.push(this.driverModal.data); } else { const index = this.drivers.findIndex(d => d.id === this.driverModal.data.id); if (index !== -1) this.drivers[index] = this.driverModal.data; } this.closeDriverModal(); },
        hideDriverDropdownWithDelay() { setTimeout(() => { this.showDriverDropdown = false; }, 200); },
        selectDriverInModal(driver) { let tripContext = this.isTripWizardVisible ? this.wizard.trip : this.modal.trip; if (tripContext) { tripContext.driverId = driver.id; this.driverSearchTermInModal = driver.name; } this.showDriverDropdown = false; },
        savePrices() { this.pricesSavedMessage = 'Harga berhasil diperbarui!'; setTimeout(() => { this.pricesSavedMessage = ''; }, 3000); },
    },
    
    mounted() {
        this.clockInterval = setInterval(() => { this.now = new Date(); this.currentTime = formatTime(this.now, true); }, 1000);
        this.wizard.date.raw = new Date().toISOString().slice(0, 10); this.wizard.endDate.raw = new Date().toISOString().slice(0, 10);
        document.addEventListener('fullscreenchange', () => { this.isFullscreen = !!document.fullscreenElement; });
    },
    
    beforeUnmount() {
        clearInterval(this.clockInterval);
    }
});

// ===================================================================================
// BAGIAN 4: PENDAFTARAN KOMPONEN
// ===================================================================================

// Mendaftarkan semua halaman sebagai komponen
app.component('dashboard', { template: dashboardTemplate, methods: { formatTime, formatDateTime } });
app.component('inventaris', { template: inventarisTemplate, methods: { formatFullDate, isServiceDue, getVehicleStatusClass } });
app.component('drivers', { template: driversTemplate, methods: { getDriverStatusClass } });
app.component('history', { template: historyTemplate, methods: { formatFullDate, formatRupiah } });
app.component('display', { template: displayTemplate, methods: { formatTime } });
app.component('admin', { template: adminTemplate });

// Mendaftarkan semua modal sebagai komponen
// ... (Kode untuk mendaftarkan modal akan ditambahkan di tahap berikutnya jika diperlukan) ...

app.mount('#app');