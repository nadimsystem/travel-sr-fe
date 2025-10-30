const { createApp } = Vue;

// Helper function untuk deep cloning objek, menghindari mutasi data asli
function clone(obj) {
    return JSON.parse(JSON.stringify(obj));
}

// Fungsi untuk mendapatkan objek trip kosong sebagai template
function getEmptyTripObject() {
    // Mengatur tanggal default ke hari ini dan besok
    const today = new Date();
    const tomorrow = new Date(today);
    tomorrow.setDate(tomorrow.getDate() + 1);

    return {
        id: null,
        customerName: '',
        type: 'Bermalam',
        pax: null,
        budget: null,
        startDate: today.toISOString().slice(0, 10),
        endDate: tomorrow.toISOString().slice(0, 10),
        pickupPoint: '',
        route: [],
        vehicle: '',
        accommodations: [],
        destinations: [],
        addons: {
            outbound: false,
            documentation: false
        },
        rundown: null,
    };
}

createApp({
    data() {
        return {
            currentStep: 'dashboard',
            isEditing: false,
            selectedTour: null,
            savedTours: [],
            isLoadingRundown: false,
            isLoadingSuggestions: false,
            isEditingRundown: false,
            manualRundownInput: [],
            timeSlots: [],
            apiKey: "MASUKKAN_API_KEY_GEMINI_ANDA_DISINI", // Ganti dengan API Key Anda
            // --- DATA MASTER & HARGA ---
            costs: {
                driverMeal: 100000,
                driverLodge: 100000,
                documentation: 2500000,
                outboundPerPax: 150000
            },
            cities: ['Padang', 'Bukittinggi', 'Payakumbuh', 'Batusangkar', 'Alahan Panjang'],
            newRoute: { from: 'Padang', to: '' },
            vehicles: [
                { name: 'Big Bus', capacity: 'Kapasitas 45 seats', icon: 'bi bi-bus-front-fill', pricePerDay: 4000000 },
                { name: 'Medium Bus', capacity: 'Kapasitas 33 seats', icon: 'bi bi-bus-front', pricePerDay: 2800000 },
                { name: 'Hiace Premio / Commuter', capacity: 'Kapasitas 7 seats (Luxury)', icon: 'bi bi-truck-front-fill', pricePerDay: 1600000 },
            ],
            allAccommodations: [
                { name: 'Hotel Santika Bukittinggi', city: 'Bukittinggi', type: 'Hotel', pricePerNight: 550000 }, { name: 'The Hills Bukittinggi Hotel', city: 'Bukittinggi', type: 'Hotel', pricePerNight: 700000 }, { name: 'Grand Rocky Hotel', city: 'Bukittinggi', type: 'Hotel', pricePerNight: 650000 }, { name: 'Villa Ngarai Sianok', city: 'Bukittinggi', type: 'Villa', pricePerNight: 1800000 }, { name: 'Mercure Padang', city: 'Padang', type: 'Hotel', pricePerNight: 800000 }, { name: 'Santika Premiere Padang', city: 'Padang', type: 'Hotel', pricePerNight: 750000 }, { name: 'Emersia Hotel Batusangkar', city: 'Batusangkar', type: 'Hotel', pricePerNight: 600000 }, { name: 'Pagaruyung Hotel', city: 'Batusangkar', type: 'Hotel', pricePerNight: 450000 }, { name: 'Alahan Panjang Resort', city: 'Alahan Panjang', type: 'Villa', pricePerNight: 1500000 }, { name: 'Villa Danau Diatas', city: 'Alahan Panjang', type: 'Villa', pricePerNight: 2000000 }, { name: 'Villa Kayu Putih', city: 'Alahan Panjang', type: 'Villa', pricePerNight: 1700000 },
            ],
            allDestinations: [
                { name: 'Pantai Air Manis', city: 'Padang', category: 'Alam', category_class: 'bg-primary' }, { name: 'Jembatan Siti Nurbaya', city: 'Padang', category: 'Budaya', category_class: 'bg-info' }, { name: 'Masjid Raya Sumbar', city: 'Padang', category: 'Budaya', category_class: 'bg-info' }, { name: 'RM Lamun Ombak', city: 'Padang', category: 'Kuliner', category_class: 'bg-warning text-dark' }, { name: 'Ngarai Sianok', city: 'Bukittinggi', category: 'Alam', category_class: 'bg-primary' }, { name: 'Jam Gadang', city: 'Bukittinggi', category: 'Budaya', category_class: 'bg-info' }, { name: 'Lobang Jepang', city: 'Bukittinggi', category: 'Budaya', category_class: 'bg-info' }, { name: 'Nasi Kapau Pasar Atas', city: 'Bukittinggi', category: 'Kuliner', category_class: 'bg-warning text-dark' }, { name: 'Lembah Harau', city: 'Payakumbuh', category: 'Alam', category_class: 'bg-primary' }, { name: 'Kelok 9', city: 'Payakumbuh', category: 'Alam', category_class: 'bg-primary' }, { name: 'RM Pongek Situjuah', city: 'Payakumbuh', category: 'Kuliner', category_class: 'bg-warning text-dark' }, { name: 'Istano Basa Pagaruyung', city: 'Batusangkar', category: 'Budaya', category_class: 'bg-info' }, { name: 'Kopi Kinikko', city: 'Batusangkar', category: 'Kuliner', category_class: 'bg-warning text-dark' }, { name: 'Danau Kembar (Diatas & Dibawah)', city: 'Alahan Panjang', category: 'Alam', category_class: 'bg-primary' }, { name: 'Kebun Teh Alahan Panjang', city: 'Alahan Panjang', category: 'Alam', category_class: 'bg-primary' },
            ],
            trip: getEmptyTripObject()
        }
    },
    watch: {
        savedTours: {
            handler(newTours) {
                localStorage.setItem('sutanRayaTours', JSON.stringify(newTours));
            },
            deep: true
        }
    },
    created() {
        const toursFromStorage = localStorage.getItem('sutanRayaTours');
        if (toursFromStorage) {
            this.savedTours = JSON.parse(toursFromStorage);
        }
        this.generateTimeSlots();
    },
    computed: {
        availableCities() {
            const usedDestinations = new Set(this.trip.route.map(r => r.to));
            return this.cities.filter(city => !usedDestinations.has(city) && city !== this.newRoute.from);
        },
        routeCities() {
            const citiesInRoute = new Set();
            this.trip.route.forEach(r => { citiesInRoute.add(r.from); citiesInRoute.add(r.to); });
            return Array.from(citiesInRoute);
        }
    },
    methods: {
        isStepValid(step) {
            if (step === 'step1') return this.trip.customerName && this.trip.pax > 0;
            if (step === 'step2') return this.trip.type && this.trip.startDate && this.trip.endDate && this.calculatedDuration(this.trip) > 0;
            if (step === 'step3') return this.trip.route.length > 0;
            if (step === 'step4') return this.trip.vehicle && this.trip.pickupPoint;
            return true; // Step 5 is the last, always valid to proceed to save
        },
        hasRundown(tour) {
            return tour.rundown && tour.rundown.length > 0 && tour.rundown.some(day => day.activities.length > 0);
        },
        generateTimeSlots() {
            const slots = [];
            for (let h = 7; h < 23; h++) {
                for (let m of ['00', '30']) {
                    slots.push(`${String(h).padStart(2, '0')}:${m}`);
                }
            }
            this.timeSlots = slots;
        },
        formatDate(dateString) {
            if (!dateString) return 'N/A';
            const options = { day: 'numeric', month: 'long', year: 'numeric' };
            // Tambah T00:00:00 untuk menghindari masalah timezone
            return new Date(dateString + 'T00:00:00').toLocaleDateString('id-ID', options);
        },
        calculatedDuration(tour) {
            if (!tour.startDate || !tour.endDate) return 0;
            const start = new Date(tour.startDate);
            const end = new Date(tour.endDate);
            if (start > end) return 0;
            const diffTime = Math.abs(end - start);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
            return diffDays > 0 ? diffDays : 0;
        },
        getPriceBreakdown(tour) {
            const duration = this.calculatedDuration(tour);
            if (!tour || !duration || !tour.pax) return {};
            const breakdown = {};
            const nights = duration > 1 ? duration - 1 : 0;
            const vehicleData = this.vehicles.find(v => v.name === tour.vehicle);
            if (vehicleData) {
                breakdown['Sewa Kendaraan'] = vehicleData.pricePerDay * duration;
                let crewCost = this.costs.driverMeal * duration;
                if (tour.type === 'Bermalam' && nights > 0) {
                    crewCost += this.costs.driverLodge * nights;
                }
                breakdown['Biaya Operasional Kru'] = crewCost;
            }
            if (tour.type === 'Bermalam' && tour.accommodations.length > 0) {
                let accommodationCost = 0;
                tour.accommodations.forEach(acc => {
                    const roomsNeeded = acc.type === 'Hotel' ? Math.ceil(tour.pax / 2) : 1;
                    accommodationCost += acc.pricePerNight * roomsNeeded * nights;
                });
                breakdown['Akomodasi Pelanggan'] = accommodationCost;
            }
            let addonsCost = 0;
            if (tour.addons.documentation) addonsCost += this.costs.documentation;
            if (tour.addons.outbound) addonsCost += this.costs.outboundPerPax * tour.pax;
            if (addonsCost > 0) breakdown['Layanan Tambahan'] = addonsCost;
            return breakdown;
        },
        calculateTotalPrice(tour) {
            const breakdown = this.getPriceBreakdown(tour);
            return Object.values(breakdown).reduce((sum, value) => sum + value, 0);
        },
        nextStep() {
            const stepNumber = parseInt(this.currentStep.replace('step', ''));
            // Skip akomodasi jika tipe Harian
            if (stepNumber === 5 && this.trip.type === 'Harian') {
                this.currentStep = `step5`; // Di versi Tailwind, step 5 jadi yang terakhir
            } else {
                this.currentStep = `step${stepNumber + 1}`;
            }
        },
        prevStep() {
            const stepNumber = parseInt(this.currentStep.replace('step', ''));
             // Skip akomodasi jika tipe Harian
            if (stepNumber === 5 && this.trip.type === 'Harian') {
                this.currentStep = 'step4';
            } else {
                this.currentStep = `step${stepNumber - 1}`;
            }
        },
        startPlanner() {
            this.isEditing = false;
            this.trip = getEmptyTripObject();
            this.trip.id = Date.now();
            this.newRoute.from = 'Padang';
            this.currentStep = 'step1';
        },
        viewDetails(tour) {
            this.selectedTour = tour;
            this.isEditingRundown = false;
            this.currentStep = 'details';
        },
        editTour(tourToEdit) {
            this.isEditing = true;
            this.trip = clone(tourToEdit);
            this.newRoute.from = this.trip.route.length > 0 ? this.trip.route[this.trip.route.length - 1].to : 'Padang';
            this.currentStep = 'step1';
        },
        returnToDashboard() {
            this.currentStep = 'dashboard';
            this.isEditing = false;
            this.selectedTour = null;
        },
        addRoute() {
            if (this.newRoute.to) {
                this.trip.route.push({ ...this.newRoute });
                this.newRoute.from = this.newRoute.to;
                this.newRoute.to = '';
            }
        },
        removeRoute(index) {
            this.trip.route.splice(index, 1);
            this.newRoute.from = this.trip.route.length > 0 ? this.trip.route[this.trip.route.length - 1].to : 'Padang';
        },
        destinationsByCity(city) { return this.allDestinations.filter(d => d.city === city); },
        accommodationsByCity(city) { return this.allAccommodations.filter(a => a.city === city); },
        saveTour() {
            if (this.isEditing) {
                const index = this.savedTours.findIndex(tour => tour.id === this.trip.id);
                if (index !== -1) { this.savedTours[index] = this.trip; }
            } else {
                this.savedTours.push(this.trip);
            }
            this.returnToDashboard();
        },
        deleteTour(index) {
            if (confirm('Yakin ingin menghapus paket ini?')) {
                this.savedTours.splice(index, 1);
            }
        }
    }
}).mount('#tourApp');