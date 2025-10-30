const { createApp } = Vue;

createApp({
    data() {
        return {
            // State untuk UI, tidak berisi data inti lagi
            view: 'dashboard',
            isFullscreen: false,
            armadaSearchTerm: '',
            driverSearchTerm: '',
            driverSearchTermInModal: '',
            historySearchTerm: '',
            now: new Date(),
            currentTime: this.formatTime(new Date()),
            currentDate: this.getCurrentDate(),
            clockInterval: null,
            isTripWizardVisible: false,
            isTripModalVisible: false,
            isVehicleModalVisible: false,
            isDriverModalVisible: false,
            wizard: {
                step: 1,
                trip: { type: 'Rute', pax: 1, origin: '', destination: '', date: '', departureTime: '', fleetId: null, driverId: null, notes: '' },
                endDate: { raw: '', formatted: '' },
                availableFleet: [],
                availableDrivers: [],
            },
            modal: { trip: null },
            vehicleModal: { mode: 'add', data: { id: null, name: '', type: 'Hiace Commuter', plate: '', capacity: 7, status: 'Tersedia', hargaSewa: 0, hargaPerOrang: 0, biayaOperasional: 0 } },
            driverModal: { mode: 'add', data: { id: null, name: '', licenseType: 'A Umum', phone: '', status: 'Standby' } },
            showDriverDropdown: false,
            pricesSavedMessage: '',
            commonTimes: ['07:00', '08:00', '09:00', '10:00', '13:00', '14:00', '15:00', '16:00', '19:00', '20:00', '21:00', '22:00'],
            
            // Properti ini akan diisi dari DataManager saat aplikasi dimuat
            fleet: [],
            drivers: [],
            trips: [],
            locations: [],
            statuses: [],
            
            // Helper untuk styling
            statusClasses: { 'On Time': 'bg-green-100 text-green-800', 'Boarding': 'bg-yellow-100 text-yellow-800 animate-pulse', 'Berangkat': 'bg-blue-100 text-blue-800', 'Tiba': 'bg-gray-200 text-gray-800', 'Tertunda': 'bg-red-100 text-red-800', 'Batal': 'bg-black text-white', 'Rental': 'bg-purple-100 text-purple-800' },
        };
    },
    computed: {
        // SEMUA COMPUTED PROPERTIES TIDAK PERLU DIUBAH
        // Mereka akan otomatis reaktif terhadap data baru yang dimuat dari DataManager
        currentViewTitle() { /* ... (tanpa perubahan dari file asli) ... */ },
        tripsWithFleet() { /* ... (tanpa perubahan dari file asli) ... */ },
        sortedTrips() { /* ... (tanpa perubahan dari file asli) ... */ },
        todaySchedule() { /* ... (tanpa perubahan dari file asli) ... */ },
        filteredFleet() { /* ... (tanpa perubahan dari file asli) ... */ },
        availableFleet() { /* ... (tanpa perubahan dari file asli) ... */ },
        inServiceFleet() { /* ... (tanpa perubahan dari file asli) ... */ },
        underMaintenanceFleet() { /* ... (tanpa perubahan dari file asli) ... */ },
        standbyDrivers() { /* ... (tanpa perubahan dari file asli) ... */ },
        onDutyDrivers() { /* ... (tanpa perubahan dari file asli) ... */ },
        offDutyDrivers() { /* ... (tanpa perubahan dari file asli) ... */ },
        filteredDrivers() { /* ... (tanpa perubahan dari file asli) ... */ },
        filteredDriversInModal() { /* ... (tanpa perubahan dari file asli) ... */ },
        completedTrips() { /* ... (tanpa perubahan dari file asli) ... */ },
        filteredHistory() { /* ... (tanpa perubahan dari file asli) ... */ },
        fleetForFinance() { /* ... (tanpa perubahan dari file asli) ... */ },
        totalRevenue() { /* ... (tanpa perubahan dari file asli) ... */ },
        totalOperationalCost() { /* ... (tanpa perubahan dari file asli) ... */ },
        netProfit() { /* ... (tanpa perubahan dari file asli) ... */ },
        occupancyRate() { /* ... (tanpa perubahan dari file asli) ... */ },
        fleetUtilization() { /* ... (tanpa perubahan dari file asli) ... */ },
    },
    methods: {
        // FUNGSI BARU: Untuk memuat/menyegarkan data dari pusat data
        refreshData() {
            const data = DataManager.loadData();
            this.fleet = data.fleet;
            this.drivers = data.drivers;
            this.trips = data.trips;
            this.locations = data.locations;
            this.statuses = data.statuses;
            console.log("Data refreshed from DataManager.");
        },
        
        // --- MODIFIKASI FUNGSI YANG MENGUBAH DATA ---

        saveTripFromWizard() {
            if (!this.wizard.trip.fleetId || !this.wizard.trip.driverId || !this.wizard.trip.date || !this.wizard.trip.departureTime) {
                alert('Harap lengkapi semua field yang diperlukan.');
                return;
            }
            
            const departureDateTime = `${this.wizard.trip.date}T${this.wizard.trip.departureTime}`;
            
            const newTrip = {
                fleetId: parseInt(this.wizard.trip.fleetId),
                driverId: parseInt(this.wizard.trip.driverId),
                type: this.wizard.trip.type,
                origin: this.wizard.trip.type === 'Rute' ? this.wizard.trip.origin : 'Rental',
                destination: this.wizard.trip.type === 'Rute' ? this.wizard.trip.destination : this.wizard.trip.notes.substring(0, 20),
                departureTime: departureDateTime,
                notes: this.wizard.trip.notes,
                pax: this.wizard.trip.type === 'Rental' ? 'Carter' : `${this.wizard.trip.pax}/${this.getFleetById(this.wizard.trip.fleetId).capacity}`,
                // Arrival time bisa dikalkulasi di backend atau dibiarkan null
                arrivalTime: null, 
            };
            
            // Gunakan DataManager untuk MENAMBAH trip baru
            DataManager.addTrip(newTrip);

            // Update status supir dan armada secara manual setelah trip ditambahkan
            const data = DataManager.loadData();
            const driver = data.drivers.find(d => d.id === newTrip.driverId);
            const vehicle = data.fleet.find(f => f.id === newTrip.fleetId);
            if(driver) driver.status = 'Dalam Perjalanan';
            if(vehicle) vehicle.status = 'Dalam Perjalanan';
            DataManager.saveData(data); // Simpan perubahan status

            this.closeTripWizard();
        },

        updateTripStatus(tripId, newStatus) {
            const data = DataManager.loadData();
            const trip = data.trips.find(t => t.id === tripId);
            if (!trip) return;

            const oldStatus = trip.status;
            if (oldStatus === newStatus) return;

            trip.status = newStatus;
            
            const isFinished = newStatus === 'Tiba' || newStatus === 'Batal';
            
            // Update status supir dan armada jika perjalanan selesai
            if (isFinished) {
                const driver = data.drivers.find(d => d.id === trip.driverId);
                const vehicle = data.fleet.find(f => f.id === trip.fleetId);
                if (driver) driver.status = 'Standby';
                if (vehicle) vehicle.status = 'Tersedia';
            }
            
            DataManager.saveData(data); // Simpan semua perubahan
            if(this.modal.trip) this.modal.trip.status = newStatus; // update view di modal
        },

        deleteTrip(tripId) {
            if (confirm('Apakah Anda yakin ingin menghapus jadwal perjalanan ini?')) {
                const data = DataManager.loadData();
                const trip = data.trips.find(t => t.id === tripId);

                // Kembalikan status driver dan fleet ke standby/tersedia
                if (trip) {
                    const driver = data.drivers.find(d => d.id === trip.driverId);
                    const vehicle = data.fleet.find(f => f.id === trip.fleetId);
                    if (driver && driver.status === 'Dalam Perjalanan') driver.status = 'Standby';
                    if (vehicle && vehicle.status === 'Dalam Perjalanan') vehicle.status = 'Tersedia';
                }

                data.trips = data.trips.filter(t => t.id !== tripId);
                DataManager.saveData(data);
                this.closeTripModal();
            }
        },
        
        saveVehicle() {
            if (!this.vehicleModal.data.name || !this.vehicleModal.data.plate) {
                alert('Nama dan Plat Nomor tidak boleh kosong.');
                return;
            }
            const data = DataManager.loadData();
            if (this.vehicleModal.mode === 'add') {
                this.vehicleModal.data.id = Date.now(); // ID unik
                data.fleet.push(this.vehicleModal.data);
            } else {
                const index = data.fleet.findIndex(f => f.id === this.vehicleModal.data.id);
                if (index !== -1) data.fleet[index] = { ...this.vehicleModal.data };
            }
            DataManager.saveData(data);
            this.closeVehicleModal();
        },
        
        saveDriver() {
            if (!this.driverModal.data.name || !this.driverModal.data.phone) {
                alert('Nama dan Nomor Telepon tidak boleh kosong.');
                return;
            }
            const data = DataManager.loadData();
            if (this.driverModal.mode === 'add') {
                this.driverModal.data.id = Date.now() + 1; // ID unik
                data.drivers.push(this.driverModal.data);
            } else {
                const index = data.drivers.findIndex(d => d.id === this.driverModal.data.id);
                if (index !== -1) data.drivers[index] = { ...this.driverModal.data };
            }
            DataManager.saveData(data);
            this.closeDriverModal();
        },
        
        savePrices() {
            // Karena harga diubah langsung pada 'this.fleet', kita hanya perlu menyimpan state terbaru
            const data = DataManager.loadData();
            data.fleet = this.fleet; // Timpa data fleet lama dengan yang baru
            DataManager.saveData(data);

            this.pricesSavedMessage = 'Harga berhasil diperbarui!';
            setTimeout(() => { this.pricesSavedMessage = ''; }, 3000);
        },

        // --- METHODS LAINNYA (TIDAK PERLU DIUBAH) ---
        // Semua methods yang hanya membaca data atau mengelola UI tidak perlu diubah
        formatTime(date, withSeconds = false) { /* ... (tanpa perubahan dari file asli) ... */ },
        getCurrentDate() { /* ... (tanpa perubahan dari file asli) ... */ },
        setView(view) { /* ... (tanpa perubahan dari file asli) ... */ },
        toggleFullscreen() { /* ... (tanpa perubahan dari file asli) ... */ },
        getFleetById(id) { /* ... (tanpa perubahan dari file asli) ... */ },
        getDriverById(id) { /* ... (tanpa perubahan dari file asli) ... */ },
        openTripWizard() { /* ... (tanpa perubahan dari file asli) ... */ },
        closeTripWizard() { /* ... (tanpa perubahan dari file asli) ... */ },
        nextWizardStep() { /* ... (tanpa perubahan dari file asli) ... */ },
        prevWizardStep() { /* ... (tanpa perubahan dari file asli) ... */ },
        updateAvailableWizardOptions() { /* ... (tanpa perubahan dari file asli) ... */ },
        openTripModal(trip) { /* ... (tanpa perubahan dari file asli) ... */ },
        closeTripModal() { /* ... (tanpa perubahan dari file asli) ... */ },
        openVehicleModal(mode, vehicle = null) { /* ... (tanpa perubahan dari file asli) ... */ },
        closeVehicleModal() { /* ... (tanpa perubahan dari file asli) ... */ },
        openDriverModal(mode, driver = null) { /* ... (tanpa perubahan dari file asli) ... */ },
        closeDriverModal() { /* ... (tanpa perubahan dari file asli) ... */ },
        selectDriverInModal(driver) { /* ... (tanpa perubahan dari file asli) ... */ },
        formatCurrency(value) { /* ... (tanpa perubahan dari file asli) ... */ },
        formatDateTime(isoString) { /* ... (tanpa perubahan dari file asli) ... */ },
        calculateDuration(start, end) { /* ... (tanpa perubahan dari file asli) ... */ },
    },
    mounted() {
        // 1. Muat data dari DataManager saat aplikasi pertama kali dijalankan
        this.refreshData();

        // 2. Tambahkan event listener untuk mendeteksi perubahan dari aplikasi lain
        window.addEventListener('storageUpdated', this.refreshData);

        // --- Logika UI yang sudah ada sebelumnya ---
        this.clockInterval = setInterval(() => {
            this.now = new Date();
            this.currentTime = this.formatTime(this.now, true);
        }, 1000);
        const today = new Date();
        today.setMinutes(today.getMinutes() - today.getTimezoneOffset());
        this.wizard.trip.date = today.toISOString().slice(0, 10);
        this.wizard.endDate.raw = today.toISOString().slice(0, 10);
    },
    beforeUnmount() {
        // Hapus listener untuk mencegah memory leak
        clearInterval(this.clockInterval);
        window.removeEventListener('storageUpdated', this.refreshData);
    }
}).mount('#app');