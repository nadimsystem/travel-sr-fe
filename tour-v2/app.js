const { createApp } = Vue;

function clone(obj) {
    return JSON.parse(JSON.stringify(obj));
}

function getEmptyTripObject() {
    return {
        id: null,
        customerName: '',
        type: '',
        pax: null,
        budget: null,
        startDate: null,
        endDate: null,
        pickupPoint: '',
        route: [],
        vehicleId: null,
        accommodations: [],
        destinations: [],
        addons: { outbound: false, documentation: false },
        rundown: null,
    };
}

createApp({
            data() {
                return {
                    fleet: [
                        { id: 1, name: 'Hiace Premio SR-01', type: 'Hiace Premio', plate: 'BA 1001 HP', capacity: 10, status: 'Tersedia', icon: 'bi-truck-front-fill', hargaSewa: 1600000, biayaOperasional: 600000, requiredLicense: 'A Umum' },
                        { id: 2, name: 'Hiace Premio SR-02', type: 'Hiace Premio', plate: 'BA 1002 HP', capacity: 10, status: 'Tersedia', icon: 'bi-truck-front-fill', hargaSewa: 1600000, biayaOperasional: 600000, requiredLicense: 'A Umum' },
                        { id: 3, name: 'Hiace Commuter SR-03', type: 'Hiace Commuter', plate: 'BA 1003 HC', capacity: 14, status: 'Tersedia', icon: 'bi-truck-front-fill', hargaSewa: 1500000, biayaOperasional: 550000, requiredLicense: 'A Umum' },
                        { id: 4, name: 'Hiace Premio SR-04', type: 'Hiace Premio', plate: 'BA 1004 HP', capacity: 10, status: 'Tersedia', icon: 'bi-truck-front-fill', hargaSewa: 1600000, biayaOperasional: 600000, requiredLicense: 'A Umum' },
                        { id: 5, name: 'Hiace Commuter SR-05', type: 'Hiace Commuter', plate: 'BA 1005 HC', capacity: 14, status: 'Tersedia', icon: 'bi-truck-front-fill', hargaSewa: 1500000, biayaOperasional: 550000, requiredLicense: 'A Umum' },
                        { id: 6, name: 'Hiace Premio SR-06', type: 'Hiace Premio', plate: 'BA 1006 HP', capacity: 10, status: 'Tersedia', icon: 'bi-truck-front-fill', hargaSewa: 1600000, biayaOperasional: 600000, requiredLicense: 'A Umum' },
                        { id: 7, name: 'Hiace Premio SR-07', type: 'Hiace Premio', plate: 'BA 1007 HP', capacity: 10, status: 'Tersedia', icon: 'bi-truck-front-fill', hargaSewa: 1600000, biayaOperasional: 600000, requiredLicense: 'A Umum' },
                        { id: 8, name: 'Hiace Commuter SR-08', type: 'Hiace Commuter', plate: 'BA 1008 HC', capacity: 14, status: 'Tersedia', icon: 'bi-truck-front-fill', hargaSewa: 1500000, biayaOperasional: 550000, requiredLicense: 'A Umum' },
                        { id: 9, name: 'Hiace Premio SR-09', type: 'Hiace Premio', plate: 'BA 1009 HP', capacity: 10, status: 'Tersedia', icon: 'bi-truck-front-fill', hargaSewa: 1600000, biayaOperasional: 600000, requiredLicense: 'A Umum' },
                        { id: 10, name: 'Hiace Premio SR-10', type: 'Hiace Premio', plate: 'BA 1010 HP', capacity: 10, status: 'Tersedia', icon: 'bi-truck-front-fill', hargaSewa: 1600000, biayaOperasional: 600000, requiredLicense: 'A Umum' },
                        { id: 11, name: 'Hiace Commuter SR-11', type: 'Hiace Commuter', plate: 'BA 1011 HC', capacity: 14, status: 'Tersedia', icon: 'bi-truck-front-fill', hargaSewa: 1500000, biayaOperasional: 550000, requiredLicense: 'A Umum' },
                        { id: 12, name: 'Hiace Premio SR-12', type: 'Hiace Premio', plate: 'BA 1012 HP', capacity: 10, status: 'Tersedia', icon: 'bi-truck-front-fill', hargaSewa: 1600000, biayaOperasional: 600000, requiredLicense: 'A Umum' },
                        { id: 13, name: 'Hiace Premio SR-13', type: 'Hiace Premio', plate: 'BA 1013 HP', capacity: 10, status: 'Perbaikan', icon: 'bi-truck-front-fill', hargaSewa: 1600000, biayaOperasional: 600000, requiredLicense: 'A Umum' },
                        { id: 14, name: 'Medium Bus SR-21', type: 'Medium Bus', plate: 'BA 7021 MB', capacity: 33, status: 'Tersedia', icon: 'bi-bus-front-fill', hargaSewa: 2800000, biayaOperasional: 1100000, requiredLicense: 'B1 Umum' },
                        { id: 15, name: 'Medium Bus SR-22', type: 'Medium Bus', plate: 'BA 7022 MB', capacity: 33, status: 'Tersedia', icon: 'bi-bus-front-fill', hargaSewa: 2800000, biayaOperasional: 1100000, requiredLicense: 'B1 Umum' },
                        { id: 16, name: 'Medium Bus SR-23', type: 'Medium Bus', plate: 'BA 7023 MB', capacity: 33, status: 'Tersedia', icon: 'bi-bus-front-fill', hargaSewa: 2800000, biayaOperasional: 1100000, requiredLicense: 'B1 Umum' },
                        { id: 17, name: 'Medium Bus SR-24', type: 'Medium Bus', plate: 'BA 7024 MB', capacity: 33, status: 'Tersedia', icon: 'bi-bus-front-fill', hargaSewa: 2800000, biayaOperasional: 1100000, requiredLicense: 'B1 Umum' },
                        { id: 18, name: 'Medium Bus SR-25', type: 'Medium Bus', plate: 'BA 7025 MB', capacity: 33, status: 'Tersedia', icon: 'bi-bus-front-fill', hargaSewa: 2800000, biayaOperasional: 1100000, requiredLicense: 'B1 Umum' },
                        { id: 19, name: 'Medium Bus SR-26', type: 'Medium Bus', plate: 'BA 7026 MB', capacity: 33, status: 'Tersedia', icon: 'bi-bus-front-fill', hargaSewa: 2800000, biayaOperasional: 1100000, requiredLicense: 'B1 Umum' },
                        { id: 20, name: 'Medium Bus SR-27', type: 'Medium Bus', plate: 'BA 7027 MB', capacity: 33, status: 'Tersedia', icon: 'bi-bus-front-fill', hargaSewa: 2800000, biayaOperasional: 1100000, requiredLicense: 'B1 Umum' },
                        { id: 21, name: 'Medium Bus SR-28', type: 'Medium Bus', plate: 'BA 7028 MB', capacity: 33, status: 'Tersedia', icon: 'bi-bus-front-fill', hargaSewa: 2800000, biayaOperasional: 1100000, requiredLicense: 'B1 Umum' },
                        { id: 22, name: 'Medium Bus SR-29', type: 'Medium Bus', plate: 'BA 7029 MB', capacity: 33, status: 'Tersedia', icon: 'bi-bus-front-fill', hargaSewa: 2800000, biayaOperasional: 1100000, requiredLicense: 'B1 Umum' },
                        { id: 23, name: 'Medium Bus SR-30', type: 'Medium Bus', plate: 'BA 7030 MB', capacity: 33, status: 'Tersedia', icon: 'bi-bus-front-fill', hargaSewa: 2800000, biayaOperasional: 1100000, requiredLicense: 'B1 Umum' },
                        { id: 24, name: 'Medium Bus SR-31', type: 'Medium Bus', plate: 'BA 7031 MB', capacity: 33, status: 'Perbaikan', icon: 'bi-bus-front-fill', hargaSewa: 2800000, biayaOperasional: 1100000, requiredLicense: 'B1 Umum' },
                        { id: 25, name: 'Big Bus SR-41', type: 'Big Bus', plate: 'BA 7041 BB', capacity: 45, status: 'Tersedia', icon: 'bi-bus-front-fill', hargaSewa: 4000000, biayaOperasional: 1500000, requiredLicense: 'B2 Umum' },
                        { id: 26, name: 'Big Bus SR-42', type: 'Big Bus', plate: 'BA 7042 BB', capacity: 45, status: 'Tersedia', icon: 'bi-bus-front-fill', hargaSewa: 4000000, biayaOperasional: 1500000, requiredLicense: 'B2 Umum' },
                        { id: 27, name: 'Big Bus SR-43', type: 'Big Bus', plate: 'BA 7043 BB', capacity: 45, status: 'Tersedia', icon: 'bi-bus-front-fill', hargaSewa: 4000000, biayaOperasional: 1500000, requiredLicense: 'B2 Umum' },
                        { id: 28, name: 'Big Bus SR-44', type: 'Big Bus', plate: 'BA 7044 BB', capacity: 45, status: 'Tersedia', icon: 'bi-bus-front-fill', hargaSewa: 4000000, biayaOperasional: 1500000, requiredLicense: 'B2 Umum' },
                        { id: 29, name: 'Big Bus SR-45', type: 'Big Bus', plate: 'BA 7045 BB', capacity: 45, status: 'Tersedia', icon: 'bi-bus-front-fill', hargaSewa: 4000000, biayaOperasional: 1500000, requiredLicense: 'B2 Umum' },
                    ],
                    trips: [
                        { id: 1, fleetId: 1, departureTime: '2025-10-05T08:00', arrivalTime: '2025-10-05T10:30', status: 'Berangkat' },
                        { id: 2, fleetId: 14, departureTime: '2025-10-05T07:30', arrivalTime: '2025-10-05T18:00', status: 'Rental' },
                        { id: 4, fleetId: 25, departureTime: '2025-10-05T09:00', arrivalTime: '2025-10-07T17:00', status: 'Rental' },
                        { id: 5, fleetId: 26, departureTime: '2025-10-05T14:00', arrivalTime: '2025-10-05T16:30', status: 'Berangkat' },
                    ],
                    currentStep: 'dashboard',
                    isEditing: false,
                    selectedTour: null,
                    savedTours: [],
                    isLoadingRundown: false,
                    isLoadingSuggestions: false,
                    isEditingRundown: false,
                    manualRundownInput: [],
                    timeSlots: [],
                    apiKey: "AIzaSyBenjmDRAgqo8D9T-H4U1qfN_oW2EdhMcw",
                    costs: {
                        driverMeal: 100000,
                        driverLodge: 100000,
                        documentation: 2500000,
                        outboundPerPax: 150000
                    },
                    cities: ['Padang', 'Bukittinggi', 'Payakumbuh', 'Batusangkar', 'Alahan Panjang'],
                    newRoute: { from: 'Padang', to: '' },
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
                        localStorage.setItem('sutanRayaToursV2', JSON.stringify(newTours));
                    },
                    deep: true
                }
            },
            created() {
                const toursFromStorage = localStorage.getItem('sutanRayaToursV2');
                if (toursFromStorage) {
                    this.savedTours = JSON.parse(toursFromStorage);
                }
                this.generateTimeSlots();
            },
            computed: {
                fleetInOperationIds() {
                    const now = new Date();
                    const activeFleetIds = this.trips
                        .filter(trip => {
                            if (!trip.departureTime || !trip.arrivalTime) return false;
                            const start = new Date(trip.departureTime);
                            const end = new Date(trip.arrivalTime);
                            return trip.status !== 'Tiba' && trip.status !== 'Batal' && now >= start && now <= end;
                        })
                        .map(trip => trip.fleetId);
                    return new Set(activeFleetIds);
                },
                armadaStandby() {
                    return this.fleet.filter(f => !this.fleetInOperationIds.has(f.id) && f.status === 'Tersedia');
                },
                availableCities() {
                    const usedDestinations = new Set(this.trip.route.map(r => r.to));
                    return this.cities.filter(city => !usedDestinations.has(city) && city !== this.newRoute.from);
                },
                routeCities() {
                    if (!this.trip || !this.trip.route) return [];
                    const citiesInRoute = new Set();
                    this.trip.route.forEach(r => {
                        citiesInRoute.add(r.from);
                        citiesInRoute.add(r.to);
                    });
                    return Array.from(citiesInRoute);
                }
            },
            methods: {
                parseActivityString(activityString) {
                    const match = activityString.match(/(\d{2}:\d{2})\s*-\s*(\d{2}:\d{2}):\s*(.*)/);
                    if (match) {
                        return { id: Date.now() + Math.random(), startTime: match[1], endTime: match[2], description: match[3].trim() };
                    }
                    return { id: Date.now() + Math.random(), startTime: '', endTime: '', description: activityString };
                },
                getVehicleNameById(vehicleId) {
                    if (!vehicleId) return 'N/A';
                    const vehicle = this.fleet.find(f => f.id === vehicleId);
                    return vehicle ? vehicle.name : 'Kendaraan Tidak Ditemukan';
                },
                hasRundown(tour) {
                    return tour && tour.rundown && tour.rundown.length > 0 && tour.rundown.some(day => day.activities && day.activities.length > 0);
                },
                generateTimeSlots() {
                    const slots = [];
                    for (let h = 7; h < 23; h++) { for (let m of['00', '30']) { slots.push(`${String(h).padStart(2, '0')}:${m}`); } }
                    this.timeSlots = slots;
                },
                availableStartTimes(dayIndex, activityIndex) {
                    if (activityIndex === 0) return this.timeSlots;
                    const prevActivity = this.manualRundownInput[dayIndex].activities[activityIndex - 1];
                    if (!prevActivity || !prevActivity.endTime) return this.timeSlots;
                    const prevEndTimeIndex = this.timeSlots.indexOf(prevActivity.endTime);
                    if (prevEndTimeIndex === -1) return this.timeSlots;
                    return this.timeSlots.slice(prevEndTimeIndex);
                },
                availableEndTimes(dayIndex, activityIndex) {
                    const activity = this.manualRundownInput[dayIndex].activities[activityIndex];
                    if (!activity || !activity.startTime) return [];
                    const startTimeIndex = this.timeSlots.indexOf(activity.startTime);
                    return this.timeSlots.slice(startTimeIndex + 1);
                },
                addActivity(dayIndex) { this.manualRundownInput[dayIndex].activities.push({ id: Date.now(), startTime: '', endTime: '', description: '' }); },
                removeActivity(dayIndex, activityIndex) { this.manualRundownInput[dayIndex].activities.splice(activityIndex, 1); },
                formatDate(dateString) {
                    if (!dateString) return '';
                    const options = { day: 'numeric', month: 'long', year: 'numeric' };
                    return new Date(dateString).toLocaleDateString('id-ID', options);
                },
                calculatedDuration(tour) {
                    if (!tour || !tour.startDate || !tour.endDate) return 0;
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
                    const vehicleData = this.fleet.find(v => v.id === tour.vehicleId);
                    if (vehicleData) {
                        breakdown['Sewa Kendaraan'] = vehicleData.hargaSewa * duration;
                        let crewCost = this.costs.driverMeal * duration;
                        if (tour.type === 'Bermalam' && nights > 0) { crewCost += this.costs.driverLodge * nights; }
                        breakdown['Biaya Operasional Kru'] = crewCost;
                    }
                    if (tour.type === 'Bermalam' && tour.accommodations && tour.accommodations.length > 0) {
                        let accommodationCost = 0;
                        tour.accommodations.forEach(acc => {
                            const roomsNeeded = acc.type === 'Hotel' ? Math.ceil(tour.pax / 2) : 1;
                            accommodationCost += acc.pricePerNight * roomsNeeded * nights;
                        });
                        breakdown['Akomodasi Pelanggan'] = accommodationCost;
                    }
                    let addonsCost = 0;
                    if (tour.addons && tour.addons.documentation) addonsCost += this.costs.documentation;
                    if (tour.addons && tour.addons.outbound) addonsCost += this.costs.outboundPerPax * tour.pax;
                    if (addonsCost > 0) breakdown['Layanan Tambahan'] = addonsCost;
                    return breakdown;
                },
                calculateTotalPrice(tour) {
                    if (!tour) return 0;
                    const breakdown = this.getPriceBreakdown(tour);
                    return Object.values(breakdown).reduce((sum, value) => sum + value, 0);
                },
                async generateRundownAI(tour) {
                    this.isLoadingRundown = true;
                    if (!this.apiKey || this.apiKey === "AIzaSyBenjmDRAgqo8D9T-H4U1qfN_oW2EdhMcw") {
                        alert("API Key untuk AI belum diatur.");
                        this.isLoadingRundown = false;
                        return;
                    }
                    const duration = this.calculatedDuration(tour);
                    const routeString = tour.route.map(r => r.to).join(' -> ');
                    const firstFrom = tour.route.length > 0 ? tour.route[0].from : (tour.pickupPoint || 'Padang');
                    const destinationsString = tour.destinations.map(d => `${d.name} (${d.city})`).join(', ');
                    const accommodationsString = tour.accommodations.map(a => `${a.name} (${a.city})`).join(', ') || 'Tidak ada';
                    const vehicleName = this.getVehicleNameById(tour.vehicleId);
                    const prompt = `Anda adalah seorang tour planner ahli untuk Sutan Raya Tour di Sumatera Barat. Buatkan rundown (itinerary) perjalanan yang detail, logis, dan menarik.
                                                    **Data Perjalanan:**
                                                    - Durasi: ${duration} hari, dari ${this.formatDate(tour.startDate)} sampai ${this.formatDate(tour.endDate)}.
                                                    - Rute: ${firstFrom} -> ${routeString}
                                                    - Jemput: ${tour.pickupPoint}
                                                    - Peserta: ${tour.pax} orang
                                                    - Kendaraan: ${vehicleName}
                                                    - Akomodasi: ${accommodationsString}
                                                    - Destinasi Wajib: ${destinationsString}
                                                    - Layanan Tambahan: ${tour.addons.outbound ? 'Outbound' : 'Tidak ada'}, ${tour.addons.documentation ? 'Dokumentasi' : 'Tidak ada'}
                                                    **Instruksi:**
                                                    1. Buat jadwal hari per hari dari Hari 1 sampai ${duration}.
                                                    2. Atur urutan destinasi secara logis berdasarkan jarak.
                                                    3. Sertakan waktu realistis untuk setiap kegiatan (contoh: 08:00 - 09:00).
                                                    4. Masukkan jadwal istirahat, makan siang, dan makan malam.
                                                    5. Jika 'Bermalam', masukkan jadwal check-in dan check-out hotel.
                                                    6. Hari terakhir harus berakhir dengan pengantaran kembali ke titik awal (${firstFrom}).
                                                    **Format Output (JSON WAJIB):**
                                                    { "rundown": [ { "day": "Hari 1: Judul Hari", "activities": [ "08:00 - 09:00: Deskripsi", "dst..." ] } ] }`;

                    const payload = {
                            //     contents: [{ parts: [{ text: prompt }] }],
                            //     generationConfig: { responseMimeType: "application/json", responseSchema: { type: "OBJECT", properties: { rundown: { type: "ARRAY", items: { type: "OBJECT", properties: { day: { type: "STRING" }, activities: { type: "ARRAY", items: { type: "STRING" } } } } } } } };
                            //     try {
                            //         const apiUrl = `https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-preview-05-20:generateContent?key=${this.apiKey}`;
                            //         const response = await fetch(apiUrl, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
                            //         if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                            //         const result = await response.json();
                            //         const jsonText = result.candidates[0].content.parts[0].text;
                            //         const parsedJson = JSON.parse(jsonText);
                            //         if (parsedJson.rundown) {
                            //             tour.rundown = this.parseRundownFromAI(parsedJson.rundown);
                            //             const index = this.savedTours.findIndex(t => t.id === tour.id);
                            //             if (index !== -1) { this.savedTours[index].rundown = tour.rundown; }
                            //         } else { alert('AI tidak memberikan format rundown yang benar.'); }
                            //     } catch (error) {
                            //         console.error('Error fetching from Gemini API:', error);
                            //         alert('Gagal membuat rundown otomatis. Periksa konsol untuk detail error.');
                            //     } finally { this.isLoadingRundown = false; }
                            // },
                            // // async suggestDestinationsAI() {
                            // //     this.isLoadingSuggestions = true;
                            // //     if (!this.apiKey || this.apiKey === "AIzaSyBenjmDRAgqo8D9T-H4U1qfN_oW2EdhMcw") {
                            // //         alert("API Key untuk AI belum diatur.");
                            // //         this.isLoadingSuggestions = false;
                            // //         return;
                            // //     }
                            // //     const availableDestinations = this.routeCities.flatMap(city => this.destinationsByCity(city));
                            // //     const destinationList = availableDestinations.map(d => d.name).join(', ');
                            // //     const prompt = `Anda adalah tour planner. Berdasarkan rute di kota: ${this.routeCities.join(', ')}. Pilih 4-6 destinasi terbaik dari daftar ini: ${destinationList}. Prioritaskan yang ikonik dan bervariasi (alam, budaya, kuliner). Format output HARUS JSON: { "destinations": ["Nama Destinasi 1", "Nama Destinasi 2"] }`;
                            // //     const payload = { contents: [{ parts: [{ text: prompt }] }], generationConfig: { responseMimeType: "application/json", responseSchema: { type: "OBJECT", properties: { destinations: { type: "ARRAY", items: { type: "STRING" } } } } } };
                            // //     try {
                            // //         const apiUrl = `https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-preview-05-20:generateContent?key=${this.apiKey}`;
                            // //         const response = await fetch(apiUrl, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
                            // //         if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                            // //         const result = await response.json();
                            // //         const jsonText = result.candidates[0].content.parts[0].text;
                            // //         const parsedJson = JSON.parse(jsonText);
                            // //         if (parsedJson.destinations) { this.trip.destinations = this.allDestinations.filter(d => parsedJson.destinations.includes(d.name)); }
                            // //     } catch (error) {
                            // //         console.error('Error suggesting destinations:', error);
                            // //         alert('Gagal mendapatkan rekomendasi destinasi.');
                            // //     } finally { this.isLoadingSuggestions = false; }
                            // // },
                            // // parseRundownFromAI(aiRundown) {
                            // //     return aiRundown.map(day => ({ day: day.day, activities: day.activities.map(this.parseActivityString) }));
                            // // },
                            startManualRundown(tour) {
                                this.isEditingRundown = true;
                                const duration = this.calculatedDuration(tour);
                                let processedRundown = clone(tour.rundown || []);
                                if (!processedRundown || processedRundown.length !== duration) {
                                    processedRundown = Array.from({ length: duration }, (_, i) => ({ day: `Hari ${i + 1}`, activities: [] }));
                                }
                                this.manualRundownInput = processedRundown.map(day => ({...day, activities: day.activities.map(act => (typeof act === 'string' ? this.parseActivityString(act) : act)) }));
                            },
                            saveManualRundown(tour) {
                                const newRundown = clone(this.manualRundownInput);
                                tour.rundown = newRundown;
                                const index = this.savedTours.findIndex(t => t.id === tour.id);
                                if (index !== -1) { this.savedTours[index].rundown = newRundown; }
                                this.isEditingRundown = false;
                            },
                            cancelEditRundown() {
                                this.isEditingRundown = false;
                                this.manualRundownInput = [];
                            },
                            nextStep() {
                                if (!this.currentStep.startsWith('step')) { this.currentStep = 'step1'; return; }
                                const stepNumber = parseInt(this.currentStep.replace('step', ''));
                                if (isNaN(stepNumber)) { this.currentStep = 'step1'; return; }
                                if (stepNumber === 5 && this.trip.type === 'Harian') { this.currentStep = 'step7'; } else { this.currentStep = `step${stepNumber + 1}`; }
                            },
                            prevStep() {
                                if (!this.currentStep.startsWith('step')) { this.currentStep = 'dashboard'; return; }
                                const stepNumber = parseInt(this.currentStep.replace('step', ''));
                                if (isNaN(stepNumber)) { this.currentStep = 'dashboard'; return; }
                                if (stepNumber === 7 && this.trip.type === 'Harian') { this.currentStep = 'step5'; } else { this.currentStep = `step${stepNumber - 1}`; }
                            },
                            startPlanner() {
                                this.isEditing = false;
                                this.trip = getEmptyTripObject();
                                this.trip.id = Date.now() + Math.random();
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
                                    this.trip.route.push({...this.newRoute });
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
                                if (confirm('Yakin ingin menghapus paket ini?')) { this.savedTours.splice(index, 1); }
                            }
                        }, // Closing bracket for methods
                }, // Closing bracket for computed
            };);
        .mount('#app');