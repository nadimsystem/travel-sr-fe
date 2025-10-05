// Objek ini berisi semua method yang akan digunakan oleh instance Vue.
// Dengan memisahkannya, kita membuat file app.js utama menjadi lebih rapi.
const eventHandlers = {
    methods: {
        // --- Metode Umum ---
        toggleFullscreen() {
            if (!document.fullscreenElement) {
                this.$root.$el.requestFullscreen();
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                }
            }
        },

        // --- Metode untuk Wizard (Panduan Pembuatan Trip) ---
        openTripWizard(standbyFleetId = null) {
            this.driverSearchTermInModal = '';
            // Reset data wizard ke kondisi awal
            this.wizard.trip = {
                id: Date.now(), // ID unik berdasarkan timestamp
                fleetId: standbyFleetId || null,
                driverId: null,
                type: 'Rute',
                origin: 'Padang',
                destination: 'Bukittinggi',
                notes: '',
                departureTime: '',
                arrivalTime: '',
                pax: '',
                status: 'On Time',
            };
            this.setWizardDate('today'); // Atur tanggal default ke hari ini
            this.wizard.time = '08:00';
            const todayStr = new Date().toISOString().slice(0, 10);
            this.wizard.endDate.raw = todayStr;
            this.wizard.endTime = '17:00';
            this.wizard.step = 1;
            this.isTripWizardVisible = true;
        },
        closeTripWizard() {
            this.isTripWizardVisible = false;
        },
        saveTripFromWizard() {
            // Validasi sebelum menyimpan
            if (!this.wizard.trip.fleetId || !this.wizard.trip.driverId) {
                alert('Silakan pilih Armada dan Supir.');
                this.wizard.step = 3; // Arahkan pengguna ke langkah yang belum selesai
                return;
            }
            // Menggabungkan tanggal dan waktu menjadi format ISO
            if (this.wizard.trip.type === 'Rute') {
                this.wizard.trip.departureTime = `${this.wizard.date.raw}T${this.wizard.time}`;
                // Estimasi waktu tiba sederhana, +2.5 jam
                const departureDate = new Date(this.wizard.trip.departureTime);
                departureDate.setMinutes(departureDate.getMinutes() + 150);
                this.wizard.trip.arrivalTime = departureDate.toISOString().slice(0, 16);
            } else { // Untuk Rental
                this.wizard.trip.departureTime = `${this.wizard.date.raw}T${this.wizard.time}`;
                this.wizard.trip.arrivalTime = `${this.wizard.endDate.raw}T${this.wizard.endTime}`;
            }

            // Menambahkan trip baru ke dalam array utama
            this.trips.push(this.wizard.trip);
            // Mengubah status armada dan supir menjadi 'Dalam Perjalanan'
            this.updateFleetAndDriverStatusOnTripCreation(this.wizard.trip);
            this.closeTripWizard();
        },
        setWizardDate(type) {
            this.wizard.date.type = type;
            const newDate = new Date();
            if (type === 'tomorrow') {
                newDate.setDate(newDate.getDate() + 1);
            }
            this.wizard.date.raw = newDate.toISOString().slice(0, 10);
        },
        // Logika untuk mengubah status armada dan supir saat trip baru dibuat
        updateFleetAndDriverStatusOnTripCreation(trip) {
            const fleetIndex = this.fleet.findIndex(f => f.id === trip.fleetId);
            if (fleetIndex !== -1) {
                this.fleet[fleetIndex].status = 'Dalam Perjalanan';
            }
            const driverIndex = this.drivers.findIndex(d => d.id === trip.driverId);
            if (driverIndex !== -1) {
                this.drivers[driverIndex].status = 'Dalam Perjalanan';
            }
        },


        // --- Metode untuk Modal Edit Trip ---
        openTripModal(trip) {
            // Menggunakan deep copy agar perubahan di modal tidak langsung mengubah data asli
            this.modal.trip = JSON.parse(JSON.stringify(trip));
            // Simpan ID driver asli untuk perbandingan saat menyimpan
            this.modal.originalDriverId = trip.driverId;
            this.isTripModalVisible = true;
        },
        closeTripModal() {
            this.isTripModalVisible = false;
        },
        saveTripFromModal() {
            if (!this.modal.trip) return;
            const index = this.trips.findIndex(t => t.id === this.modal.trip.id);
            if (index !== -1) {
                // Update status supir jika ada perubahan
                this.updateDriverStatusOnTripChange(this.modal.originalDriverId, this.modal.trip.driverId, this.modal.trip.status);
                // Hapus properti tambahan yang tidak ada di data asli sebelum menyimpan
                const { fleet, driver, originCode, destinationCode, tripCost, tripRevenue, tripProfit, totalDurationFormatted, ...cleanedTrip } = this.modal.trip;
                this.trips[index] = cleanedTrip;
            }
            this.closeTripModal();
        },
        deleteTrip(tripId) {
            if (confirm('Apakah Anda yakin ingin menghapus jadwal perjalanan ini? Tindakan ini tidak dapat dibatalkan.')) {
                const tripIndex = this.trips.findIndex(t => t.id === tripId);
                if (tripIndex > -1) {
                    const trip = this.trips[tripIndex];
                    // Kembalikan status supir dan armada ke "Standby" / "Tersedia"
                    this.setFleetAndDriverStatusToStandby(trip.fleetId, trip.driverId);
                    this.trips.splice(tripIndex, 1); // Hapus trip dari array
                }
                this.closeTripModal();
            }
        },
        updateTripStatusInModal(newStatus) {
            if (!this.modal.trip) return;
            const oldStatus = this.modal.trip.status;
            this.modal.trip.status = newStatus;

            // Jika trip selesai (Tiba) atau Batal, bebaskan supir dan armada
            if ((newStatus === 'Tiba' || newStatus === 'Batal') && oldStatus !== 'Tiba' && oldStatus !== 'Batal') {
                this.setFleetAndDriverStatusToStandby(this.modal.trip.fleetId, this.modal.trip.driverId);
            }
            // Jika status kembali aktif dari Tiba/Batal
            else if (newStatus !== 'Tiba' && newStatus !== 'Batal' && (oldStatus === 'Tiba' || oldStatus === 'Batal')) {
                const fleetIndex = this.fleet.findIndex(f => f.id === this.modal.trip.fleetId);
                if (fleetIndex !== -1) this.fleet[fleetIndex].status = 'Dalam Perjalanan';

                const driverIndex = this.drivers.findIndex(d => d.id === this.modal.trip.driverId);
                if (driverIndex !== -1) this.drivers[driverIndex].status = 'Dalam Perjalanan';
            }
        },
        // Logika untuk mengubah status supir saat ada pergantian di modal edit
        updateDriverStatusOnTripChange(originalDriverId, newDriverId, tripStatus) {
            // Jika supir diganti, kembalikan status supir lama ke Standby
            if (originalDriverId && originalDriverId !== newDriverId) {
                const oldDriverIndex = this.drivers.findIndex(d => d.id === originalDriverId);
                if (oldDriverIndex !== -1) this.drivers[oldDriverIndex].status = 'Standby';
            }
            // Atur status supir baru
            const newDriverIndex = this.drivers.findIndex(d => d.id === newDriverId);
            if (newDriverIndex !== -1) {
                // Jika trip sudah selesai, supir baru langsung standby, jika tidak, dalam perjalanan.
                this.drivers[newDriverIndex].status = (tripStatus === 'Tiba' || tripStatus === 'Batal') ? 'Standby' : 'Dalam Perjalanan';
            }
        },
        setFleetAndDriverStatusToStandby(fleetId, driverId) {
            const fleetIndex = this.fleet.findIndex(f => f.id === fleetId);
            if (fleetIndex !== -1) this.fleet[fleetIndex].status = 'Tersedia';

            const driverIndex = this.drivers.findIndex(d => d.id === driverId);
            if (driverIndex !== -1) this.drivers[driverIndex].status = 'Standby';
        },

        // --- Metode untuk Modal Inventaris Armada ---
        openVehicleModal(vehicle) {
            if (vehicle) { // Mode Edit
                this.vehicleModal.mode = 'edit';
                this.vehicleModal.data = JSON.parse(JSON.stringify(vehicle));
            } else { // Mode Tambah
                this.vehicleModal.mode = 'add';
                this.vehicleModal.data = {
                    id: Date.now(),
                    name: '', type: 'Hiace Premio', plate: '', capacity: 7, status: 'Tersedia', icon: 'bi-truck-front-fill',
                    hargaSewa: 1600000, hargaPerOrang: 150000, biayaOperasional: 600000, requiredLicense: 'A Umum',
                    lastService: new Date().toISOString().slice(0, 10),
                    nextService: getFutureISO(180, 0, 0).slice(0, 10)
                };
            }
            this.isVehicleModalVisible = true;
        },
        closeVehicleModal() {
            this.isVehicleModalVisible = false;
        },
        saveVehicle() {
            // Otomatisasi ikon berdasarkan tipe
            if (this.vehicleModal.data.type.toLowerCase().includes('bus')) {
                this.vehicleModal.data.icon = 'bi-bus-front-fill';
            } else {
                this.vehicleModal.data.icon = 'bi-truck-front-fill';
            }

            if (this.vehicleModal.mode === 'add') {
                this.fleet.push(this.vehicleModal.data);
            } else {
                const index = this.fleet.findIndex(f => f.id === this.vehicleModal.data.id);
                if (index !== -1) this.fleet[index] = this.vehicleModal.data;
            }
            this.closeVehicleModal();
        },

        // --- Metode untuk Modal Supir ---
        openDriverModal(driver) {
            if (driver) { // Mode Edit
                this.driverModal.mode = 'edit';
                this.driverModal.data = JSON.parse(JSON.stringify(driver));
            } else { // Mode Tambah
                this.driverModal.mode = 'add';
                this.driverModal.data = {
                    id: Date.now(), name: '', licenseType: 'A Umum', phone: '', status: 'Standby'
                };
            }
            this.isDriverModalVisible = true;
        },
        closeDriverModal() {
            this.isDriverModalVisible = false;
        },
        saveDriver() {
            if (this.driverModal.mode === 'add') {
                this.drivers.push(this.driverModal.data);
            } else {
                const index = this.drivers.findIndex(d => d.id === this.driverModal.data.id);
                if (index !== -1) this.drivers[index] = this.driverModal.data;
            }
            this.closeDriverModal();
        },
        // Logika untuk dropdown pencarian supir di dalam modal
        hideDriverDropdownWithDelay() {
            setTimeout(() => { this.showDriverDropdown = false; }, 200);
        },
        selectDriverInModal(driver) {
            let tripContext = this.isTripWizardVisible ? this.wizard.trip : this.modal.trip;
            if (tripContext) {
                tripContext.driverId = driver.id;
                this.driverSearchTermInModal = driver.name;
            }
            this.showDriverDropdown = false;
        },

        // --- Metode untuk Panel Admin ---
        savePrices() {
            this.pricesSavedMessage = 'Harga berhasil diperbarui!';
            setTimeout(() => { this.pricesSavedMessage = ''; }, 3000);
        },
    }
};