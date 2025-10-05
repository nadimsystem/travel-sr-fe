const { createApp } = Vue;

const app = createApp({
    // Menggabungkan semua method dari eventHandlers ke dalam aplikasi utama
    mixins: [eventHandlers],

    // Bagian 'data' berisi semua state atau variabel yang akan dirender di halaman.
    data() {
        return {
            // State untuk navigasi & tampilan
            view: 'dashboard', // Halaman default yang ditampilkan
            isFullscreen: false,

            // State untuk fitur pencarian
            armadaSearchTerm: '',
            driverSearchTerm: '',
            driverSearchTermInModal: '', // Pencarian supir khusus di dalam modal
            historySearchTerm: '',

            // State untuk waktu & tanggal
            now: new Date(),
            currentTime: formatTime(new Date(), true),
            currentDate: getCurrentDate(),
            clockInterval: null,

            // State untuk mengontrol visibilitas modal
            isTripWizardVisible: false,
            isTripModalVisible: false,
            isVehicleModalVisible: false,
            isDriverModalVisible: false,
            showDriverDropdown: false, // Dropdown supir di dalam modal

            // State untuk data yang sedang di-edit di dalam modal
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
                originalDriverId: null, // Untuk melacak supir awal sebelum diedit
            },
            vehicleModal: {
                mode: 'add',
                data: null
            },
            driverModal: {
                mode: 'add',
                data: null
            },
            
            // State untuk notifikasi
            pricesSavedMessage: '',

            // Data statis untuk UI
            commonTimes: ['07:00', '08:00', '09:00', '10:00', '13:00', '14:00', '15:00', '16:00', '19:00', '20:00', '21:00', '22:00'],
            statusClasses: {
                'On Time': 'bg-green-100 text-green-800',
                'Boarding': 'bg-yellow-100 text-yellow-800 animate-pulse',
                'Berangkat': 'bg-blue-100 text-blue-800',
                'Tiba': 'bg-gray-200 text-gray-800',
                'Tertunda': 'bg-red-100 text-red-800',
                'Batal': 'bg-black text-white',
                'Rental': 'bg-purple-100 text-purple-800'
            },

            // Mengimpor semua data awal dari data.js
            ...initialData
        };
    },

    // Computed properties adalah data yang nilainya dihitung berdasarkan state lain.
    computed: {
        currentViewTitle() {
            const titles = {
                dashboard: 'Dashboard Operasional',
                inventaris: 'Manajemen Inventaris Armada',
                drivers: 'Manajemen Supir',
                history: 'Riwayat Perjalanan',
                display: 'Layar Informasi Publik',
                admin: 'Pengaturan Admin'
            };
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
                return {
                    ...trip, fleet, driver,
                    originCode: trip.type === 'Rute' ? (originInfo?.code || trip.origin) : 'Rental',
                    destinationCode: trip.type === 'Rute' ? (destinationInfo?.code || trip.destination) : trip.notes,
                    tripCost, tripRevenue, tripProfit,
                };
            }).sort((a,b) => new Date(a.departureTime) - new Date(b.departureTime));
        },
        activeTrips() {
            return this.tripsWithDetails.filter(t => t.status !== 'Tiba' && t.status !== 'Batal');
        },
        departures() {
            return this.activeTrips;
        },
        arrivals() {
            return this.activeTrips;
        },
        rental() {
            return this.activeTrips.filter(t => t.type === 'Rental');
        },
        armadaStandby() {
            return this.fleet.filter(f => f.status === 'Tersedia');
        },
        filteredFleet() {
            if (!this.armadaSearchTerm) return this.fleet;
            const term = this.armadaSearchTerm.toLowerCase();
            return this.fleet.filter(v => v.name.toLowerCase().includes(term) || v.plate.toLowerCase().includes(term) || v.status.toLowerCase().includes(term));
        },
        filteredDrivers() {
            if (!this.driverSearchTerm) return this.drivers;
            const term = this.driverSearchTerm.toLowerCase();
            return this.drivers.filter(d => d.name.toLowerCase().includes(term));
        },
        filteredHistory() {
            const historyTrips = this.tripsWithDetails.filter(t => t.status === 'Tiba' || t.status === 'Batal').sort((a, b) => new Date(b.departureTime) - new Date(a.departureTime));
            if (!this.historySearchTerm) return historyTrips;
            const term = this.historySearchTerm.toLowerCase();
            return historyTrips.filter(t => t.fleet.name.toLowerCase().includes(term) || (t.destinationCode && t.destinationCode.toLowerCase().includes(term)));
        },
        upcomingDeparturesForDisplay() {
            return this.activeTrips.filter(t => t.status !== 'Tiba' && t.status !== 'Batal' && t.status !== 'Berangkat').sort((a, b) => new Date(a.departureTime) - new Date(b.departureTime)).slice(0, 10);
        },
        upcomingArrivalsForDisplay() {
            return this.activeTrips.filter(t => t.destination === 'Padang' && t.status !== 'Tiba' && t.status !== 'Batal').sort((a, b) => new Date(a.arrivalTime) - new Date(b.arrivalTime)).slice(0, 10);
        },
        availableFleetForTrip() {
            const trip = this.isTripWizardVisible ? this.wizard.trip : this.modal.trip;
            let available = this.fleet.filter(f => f.status === 'Tersedia');
            if (trip && trip.fleetId) {
                const currentFleet = this.fleet.find(f => f.id === trip.fleetId);
                if (currentFleet && !available.some(f => f.id === currentFleet.id)) {
                    available.unshift(currentFleet);
                }
            }
            return available;
        },
        availableDriversForTrip() {
            const trip = this.isTripWizardVisible ? this.wizard.trip : this.modal.trip;
            if (!trip || !trip.fleetId) return [];
            const selectedFleet = this.fleet.find(f => f.id === trip.fleetId);
            if (!selectedFleet) return [];
            let filtered = this.drivers.filter(d => (d.status === 'Standby' || d.id === trip.driverId) && d.licenseType === selectedFleet.requiredLicense);
            if (this.driverSearchTermInModal) {
                filtered = filtered.filter(d => d.name.toLowerCase().includes(this.driverSearchTermInModal.toLowerCase()));
            }
            return filtered;
        },
    },
    
    watch: {
        'wizard.trip.fleetId'(newFleetId) {
            if (this.wizard.trip) this.wizard.trip.driverId = null;
        },
        'modal.trip.fleetId'(newFleetId) {
            if (this.modal.trip) this.modal.trip.driverId = null;
        }
    },

    // Dijalankan saat aplikasi dibuat
    created() {
        // Menyediakan data dan method ke semua komponen 'anak'
        // Ini adalah bagian dari 'Dependency Injection' pattern di Vue
        this.rootData = this.$data;
        this.rootMethods = this.$options.methods;
    },
    
    mounted() {
        this.clockInterval = setInterval(() => {
            this.now = new Date();
            this.currentTime = formatTime(this.now, true);
        }, 1000);
        this.wizard.date.raw = new Date().toISOString().slice(0, 10);
        this.wizard.endDate.raw = new Date().toISOString().slice(0, 10);
        document.addEventListener('fullscreenchange', () => {
            this.isFullscreen = !!document.fullscreenElement;
        });
    },
    
    beforeUnmount() {
        clearInterval(this.clockInterval);
    }
});

// 'provide' membuat data ini tersedia untuk di-'inject' oleh komponen anak
app.provide('rootData', app.config.globalProperties);
app.provide('rootMethods', app.config.globalProperties);

// Menjalankan fungsi untuk mendaftarkan semua komponen view
setupViewComponents(app);

// Memasang aplikasi Vue ke elemen dengan id="app"
app.mount('#app');