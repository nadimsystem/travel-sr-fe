const { createApp } = Vue;

const processApp = createApp({
    data() {
        return {
            // Data waktu
            now: new Date(),
            currentTime: formatTime(new Date(), true),
            currentDate: getCurrentDate(),
            clockInterval: null,
            
            // State khusus untuk halaman POS
            selectedCategory: 'Hiace', // Kategori default
            selectedArmada: null, // Armada yang dipilih
            tripDetails: {
                type: 'Rute',
                origin: 'Padang',
                destination: 'Bukittinggi',
                notes: '',
                pax: '',
                driverId: null,
                fleetId: null,
            },
            
            // Mengimpor semua data dari data.js
            ...initialData
        };
    },
    
    computed: {
        // --- Computed Properties yang Relevan untuk Halaman Proses ---
        
        // Mengambil daftar armada yang standby (status: 'Tersedia')
        armadaStandby() {
            return this.fleet.filter(f => f.status === 'Tersedia');
        },
        // Menyaring armada standby berdasarkan kategori
        standbyHiace() {
            return this.armadaStandby.filter(f => f.type.toLowerCase().includes('hiace'));
        },
        standbyMediumBus() {
            return this.armadaStandby.filter(f => f.type.toLowerCase().includes('medium bus'));
        },
        standbyBigBus() {
            return this.armadaStandby.filter(f => f.type.toLowerCase().includes('big bus'));
        },
        
        // Menampilkan armada yang tersedia berdasarkan kategori yang dipilih
        availableArmada() {
            if (!this.selectedCategory) return [];
            const category = this.selectedCategory.toLowerCase();
            if (category === 'hiace') return this.standbyHiace;
            if (category === 'medium bus') return this.standbyMediumBus;
            if (category === 'big bus') return this.standbyBigBus;
            return [];
        },
        
        // Menyaring supir yang standby dan memiliki SIM yang sesuai dengan armada terpilih
        availableDriversForTrip() {
            if (!this.selectedArmada) return [];
            const requiredLicense = this.selectedArmada.requiredLicense;
            return this.drivers.filter(d => d.status === 'Standby' && d.licenseType === requiredLicense);
        },
        
        // Mengecek apakah semua form sudah diisi dan siap untuk diproses
        isTripReady() {
            if (!this.selectedArmada || !this.tripDetails.driverId || !this.tripDetails.pax) return false;
            if (this.tripDetails.type === 'Rute' && (!this.tripDetails.origin || !this.tripDetails.destination)) return false;
            if (this.tripDetails.type === 'Rental' && !this.tripDetails.notes) return false;
            return true;
        }
    },
    
    methods: {
        // --- Metode khusus untuk halaman POS ---
        
        // Mengubah kategori yang aktif
        selectCategory(category) {
            this.selectedCategory = category;
            // Tidak mereset pilihan, agar jika salah klik, detail tidak hilang
        },
        
        // Memilih armada
        selectArmada(armada) {
            this.selectedArmada = armada;
            this.tripDetails.fleetId = armada.id;
        },
        
        // Membersihkan semua pilihan di sidebar kanan
        clearSelection() {
            this.selectedArmada = null;
            this.tripDetails = {
                type: 'Rute',
                origin: 'Padang',
                destination: 'Bukittinggi',
                notes: '',
                pax: '',
                driverId: null,
                fleetId: null,
            };
        },
        
        // Memproses dan "memberangkatkan" armada
        processTrip() {
            if (!this.isTripReady) {
                alert("Harap lengkapi semua detail perjalanan terlebih dahulu.");
                return;
            }
            
            // Membuat objek trip baru
            const newTrip = {
                id: Date.now(),
                fleetId: this.tripDetails.fleetId,
                driverId: this.tripDetails.driverId,
                type: this.tripDetails.type,
                origin: this.tripDetails.type === 'Rute' ? this.tripDetails.origin : 'Padang',
                destination: this.tripDetails.type === 'Rute' ? this.tripDetails.destination : 'Rental',
                notes: this.tripDetails.notes,
                pax: this.tripDetails.pax,
                departureTime: getTodayISO(new Date().getHours(), new Date().getMinutes()),
                // Estimasi waktu tiba +2.5 jam untuk Rute, +8 jam untuk Rental
                arrivalTime: this.tripDetails.type === 'Rute' ? getTodayISO(new Date().getHours() + 2, new Date().getMinutes() + 30) : getTodayISO(new Date().getHours() + 8, new Date().getMinutes()),
                status: 'Berangkat' // Langsung berangkat
            };
            
            // Peringatan: Kode di bawah ini akan mengubah data, tetapi tidak menyimpannya secara permanen.
            // Saat halaman di-refresh, data akan kembali seperti semula.
            // Untuk penyimpanan permanen, diperlukan koneksi ke database/backend.
            
            // 1. Mengubah status armada
            const fleetIndex = this.fleet.findIndex(f => f.id === newTrip.fleetId);
            if (fleetIndex !== -1) this.fleet[fleetIndex].status = 'Dalam Perjalanan';
            
            // 2. Mengubah status supir
            const driverIndex = this.drivers.findIndex(d => d.id === newTrip.driverId);
            if (driverIndex !== -1) this.drivers[driverIndex].status = 'Dalam Perjalanan';
            
            // 3. Menambahkan trip baru ke daftar (opsional, karena halaman ini tidak menampilkan daftar trip)
            this.trips.push(newTrip);
            
            alert(`Armada ${this.selectedArmada.name} dengan supir ${this.drivers.find(d => d.id === newTrip.driverId).name} telah diberangkatkan!`);
            
            // Membersihkan form setelah berhasil
            this.clearSelection();
        },
    },

    mounted() {
        // Timer untuk jam digital
        this.clockInterval = setInterval(() => {
            this.now = new Date();
            this.currentTime = formatTime(this.now, true);
        }, 1000);
    },
    
    beforeUnmount() {
        clearInterval(this.clockInterval);
    }
});

processApp.mount('#app');