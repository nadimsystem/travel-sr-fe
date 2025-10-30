const { createApp } = Vue;

// ===================================================================================
// BAGIAN 1: DATA & FUNGSI PEMBANTU (UTILS)
// ===================================================================================

function getTodayISO(h, m) {
    const d = new Date();
    d.setHours(h, m, 0, 0);
    const offset = d.getTimezoneOffset();
    const localDate = new Date(d.getTime() - (offset * 60 * 1000));
    return localDate.toISOString().slice(0, 16);
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

const initialData = {
    fleet: [
        { id: 1, name: 'Hiace Premio SR-01', type: 'Hiace Premio', plate: 'BA 1001 HP', capacity: 7, status: 'Dalam Perjalanan', icon: 'bi-truck-front-fill', requiredLicense: 'A Umum' },
        { id: 2, name: 'Hiace Premio SR-02', type: 'Hiace Premio', plate: 'BA 1002 HP', capacity: 7, status: 'Tersedia', icon: 'bi-truck-front-fill', requiredLicense: 'A Umum' },
        { id: 3, name: 'Hiace Commuter SR-03', type: 'Hiace Commuter', plate: 'BA 1003 HC', capacity: 14, status: 'Dalam Perjalanan', icon: 'bi-truck-front-fill', requiredLicense: 'A Umum' },
        { id: 4, name: 'Hiace Premio SR-04', type: 'Hiace Premio', plate: 'BA 1004 HP', capacity: 7, status: 'Tersedia', icon: 'bi-truck-front-fill', requiredLicense: 'A Umum' },
        { id: 5, name: 'Hiace Commuter SR-05', type: 'Hiace Commuter', plate: 'BA 1005 HC', capacity: 14, status: 'Tersedia', icon: 'bi-truck-front-fill', requiredLicense: 'A Umum' },
        { id: 13, name: 'Hiace Premio SR-13', type: 'Hiace Premio', plate: 'BA 1013 HP', capacity: 7, status: 'Perbaikan', icon: 'bi-truck-front-fill', requiredLicense: 'A Umum' },
        { id: 14, name: 'Medium Bus SR-21', type: 'Medium Bus', plate: 'BA 7021 MB', capacity: 33, status: 'Dalam Perjalanan', icon: 'bi-bus-front-fill', requiredLicense: 'B1 Umum' },
        { id: 15, name: 'Medium Bus SR-22', type: 'Medium Bus', plate: 'BA 7022 MB', capacity: 33, status: 'Tersedia', icon: 'bi-bus-front-fill', requiredLicense: 'B1 Umum' },
        { id: 24, name: 'Medium Bus SR-31', type: 'Medium Bus', plate: 'BA 7031 MB', capacity: 33, status: 'Perbaikan', icon: 'bi-bus-front-fill', requiredLicense: 'B1 Umum' },
        { id: 25, name: 'Big Bus SR-41', type: 'Big Bus', plate: 'BA 7041 BB', capacity: 45, status: 'Dalam Perjalanan', icon: 'bi-bus-front-fill', requiredLicense: 'B2 Umum' },
        { id: 26, name: 'Big Bus SR-42', type: 'Big Bus', plate: 'BA 7042 BB', capacity: 45, status: 'Dalam Perjalanan', icon: 'bi-bus-front-fill', requiredLicense: 'B2 Umum' },
        { id: 27, name: 'Big Bus SR-43', type: 'Big Bus', plate: 'BA 7043 BB', capacity: 45, status: 'Tersedia', icon: 'bi-bus-front-fill', requiredLicense: 'B2 Umum' },
    ],
    drivers: [
        { id: 101, name: 'Budi Santoso', licenseType: 'A Umum', status: 'Dalam Perjalanan' },
        { id: 102, name: 'Joko Susilo', licenseType: 'B1 Umum', status: 'Dalam Perjalanan' },
        { id: 103, name: 'Anton Wijaya', licenseType: 'A Umum', status: 'Dalam Perjalanan' },
        { id: 104, name: 'Eko Prasetyo', licenseType: 'B2 Umum', status: 'Dalam Perjalanan' },
        { id: 105, name: 'Slamet Riyadi', licenseType: 'B2 Umum', status: 'Libur' },
        { id: 106, name: 'Doni Firmansyah', licenseType: 'A Umum', status: 'Standby' },
        { id: 107, name: 'Agus Setiawan', licenseType: 'B1 Umum', status: 'Standby' },
        { id: 108, name: 'Rahmat Hidayat', licenseType: 'A Umum', status: 'Standby' },
    ],
    locations: [
        { name: 'Padang' }, { name: 'Bukittinggi' }, { name: 'Payakumbuh' }, { name: 'Solok' }, { name: 'Pariaman' }, { name: 'Painan' }, { name: 'Sawahlunto' }, { name: 'Batusangkar' }
    ],
};

// ===================================================================================
// BAGIAN 2: APLIKASI VUE UNTUK HALAMAN PROSES
// ===================================================================================

const processApp = createApp({
    data() {
        return {
            now: new Date(),
            currentTime: formatTime(new Date(), true),
            currentDate: getCurrentDate(),
            clockInterval: null,
            selectedCategory: 'Hiace',
            selectedArmada: null,
            tripDetails: {
                type: 'Rute',
                origin: 'Padang',
                destination: 'Bukittinggi',
                notes: '',
                pax: '',
                driverId: null,
                fleetId: null,
            },
            ...initialData
        };
    },
    
    computed: {
        armadaStandby() { return this.fleet.filter(f => f.status === 'Tersedia'); },
        standbyHiace() { return this.armadaStandby.filter(f => f.type.toLowerCase().includes('hiace')); },
        standbyMediumBus() { return this.armadaStandby.filter(f => f.type.toLowerCase().includes('medium bus')); },
        standbyBigBus() { return this.armadaStandby.filter(f => f.type.toLowerCase().includes('big bus')); },
        availableArmada() {
            if (!this.selectedCategory) return [];
            const category = this.selectedCategory.toLowerCase();
            if (category === 'hiace') return this.standbyHiace;
            if (category === 'medium bus') return this.standbyMediumBus;
            if (category === 'big bus') return this.standbyBigBus;
            return [];
        },
        availableDriversForTrip() {
            if (!this.selectedArmada) return [];
            return this.drivers.filter(d => d.status === 'Standby' && d.licenseType === this.selectedArmada.requiredLicense);
        },
        isTripReady() {
            if (!this.selectedArmada || !this.tripDetails.driverId || !this.tripDetails.pax) return false;
            if (this.tripDetails.type === 'Rute' && (!this.tripDetails.origin || !this.tripDetails.destination)) return false;
            if (this.tripDetails.type === 'Rental' && !this.tripDetails.notes) return false;
            return true;
        }
    },
    
    methods: {
        selectCategory(category) { this.selectedCategory = category; },
        selectArmada(armada) { this.selectedArmada = armada; this.tripDetails.fleetId = armada.id; },
        clearSelection() {
            this.selectedArmada = null;
            this.tripDetails = { type: 'Rute', origin: 'Padang', destination: 'Bukittinggi', notes: '', pax: '', driverId: null, fleetId: null, };
        },
        processTrip() {
            if (!this.isTripReady) {
                alert("Harap lengkapi semua detail perjalanan terlebih dahulu.");
                return;
            }
            // Peringatan: Kode di bawah ini hanya simulasi. Perubahan tidak akan tersimpan permanen.
            const driverName = this.drivers.find(d => d.id === this.tripDetails.driverId).name;
            alert(`SIMULASI BERHASIL:\nArmada ${this.selectedArmada.name} dengan supir ${driverName} telah diberangkatkan!`);
            
            // Mengubah status armada & supir (secara lokal di halaman ini saja)
            const fleetIndex = this.fleet.findIndex(f => f.id === this.tripDetails.fleetId);
            if (fleetIndex !== -1) this.fleet[fleetIndex].status = 'Dalam Perjalanan';
            
            const driverIndex = this.drivers.findIndex(d => d.id === this.tripDetails.driverId);
            if (driverIndex !== -1) this.drivers[driverIndex].status = 'Dalam Perjalanan';
            
            this.clearSelection();
        },
    },

    mounted() {
        this.clockInterval = setInterval(() => { this.now = new Date(); this.currentTime = formatTime(this.now, true); }, 1000);
    },
    
    beforeUnmount() {
        clearInterval(this.clockInterval);
    }
});

processApp.mount('#app');