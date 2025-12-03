const { createApp } = Vue;

createApp({
    data() {
        return {
            // --- STATE ---
            view: localStorage.getItem('sutan_view_v83') || 'dashboard',
            isFullscreen: false,
            currentTime: "",
            currentDate: "",
            reportDate: new Date().toISOString().slice(0, 10),

            // --- MODALS ---
            isProofModalVisible: false,
            isDispatchModalVisible: false,
            isTripControlVisible: false,
            isVehicleModalVisible: false,
            isDriverModalVisible: false,

            activeTripControl: null,
            validationData: null,

            // --- BOOKING MANAGEMENT ---
            bookingManagementTab: 'travel',
            searchTerm: '',
            busSearchTerm: '',
            busViewMode: 'list',
            calendarMonth: new Date().getMonth(),
            calendarYear: new Date().getFullYear(),

            // --- FORMS ---
            // Unified helper objects for form data
            bookingForm: { data: { id: null, serviceType: 'Travel', routeId: '', date: '', time: '', passengerName: '', passengerPhone: '', passengerType: 'Umum', seatCount: 1, seatNumbers: '', pickupAddress: '', dropoffAddress: '', isMultiStop: false, duration: 1 } },
            bookingBusForm: { type: 'Medium', routeId: '', seatCapacity: 33, duration: 1, date: '', passengerName: '', passengerPhone: '', totalPrice: 0, priceType: 'Kantor', packageType: 'Unit', paymentMethod: 'Cash', paymentLocation: '', paymentReceiver: '', paymentProof: '', downPaymentAmount: 0 },

            // Shared Payment State for current active form
            currentPaymentMethod: 'Cash',
            tempPayment: { loc: '', recv: '', proof: '', dpAmount: 0 },

            dispatchForm: { group: null, fleetId: "", driverId: "" },
            vehicleModal: { mode: "add", data: null },
            driverModal: { mode: "add", data: null },

            // --- CONFIGURATION ---
            routeConfig: [
                { id: "PDG-BKT", origin: "Padang", destination: "Bukittinggi", prices: { umum: 120000, pelajar: 100000, dropping: 900000, carter: 1500000 }, schedules: ["08:00", "10:00", "12:00", "14:00", "16:00", "18:00", "20:00"] },
                { id: "BKT-PDG", origin: "Bukittinggi", destination: "Padang", prices: { umum: 120000, pelajar: 100000, dropping: 900000, carter: 1500000 }, schedules: ["06:00", "08:00", "10:00", "13:00", "15:00", "17:00", "18:00", "19:00"] },
                { id: "PDG-PYK", origin: "Padang", destination: "Payakumbuh", prices: { umum: 150000, pelajar: 130000, dropping: 1100000, carter: 1800000 }, schedules: ["08:00", "10:00", "14:00", "18:00"] },
                { id: "PYK-PDG", origin: "Payakumbuh", destination: "Padang", prices: { umum: 150000, pelajar: 130000, dropping: 1100000, carter: 1800000 }, schedules: ["05:00", "07:00", "10:00", "14:00", "17:00"] }
            ],
            busRouteConfig: [
                { id: "PDG-BKT", name: "Padang - Bukittinggi", minDays: 1, prices: { s33: 2500000, s35: 2600000 }, big: { s45: { kantor: 4000000, agen: 3800000 }, s32: { kantor: 4500000, agen: 4300000 } } },
                { id: "PDG-PYK", name: "Padang - Payakumbuh", minDays: 1, prices: { s33: 2600000, s35: 2700000 }, big: { s45: { kantor: 4300000, agen: 4000000 }, s32: { kantor: 4300000, agen: 4000000 } } },
                { id: "PDG-JKT", name: "Padang - Jakarta", minDays: 6, prices: { s33: 0, s35: 0 }, isLongTrip: true, big: { base: 4500000, allin: 5500000 } },
                { id: "PDG-KNO", name: "Padang - Medan", minDays: 6, prices: { s33: 3500000, s35: 3600000 }, isLongTrip: true, big: { base: 4500000, allin: 5500000 } }
            ],
            seatLayout: [
                { row: 1, seats: [{ id: "CC", type: "seat" }, { id: "driver", type: "driver" }], label: "Depan" },
                { row: 2, seats: [{ id: "1", type: "seat" }, { id: "2", type: "seat" }], label: "Tengah" },
                { row: 3, seats: [{ id: "3", type: "seat" }, { id: "4", type: "seat" }], label: "Tengah" },
                { row: 4, seats: [{ id: "5", type: "seat" }, { id: "6", type: "seat" }, { id: "7", type: "seat" }], label: "Belakang" }
            ],

            // --- DATABASE ---
            bookings: [],
            fleet: [{ id: 1, name: "Hiace Premio 01", plate: "BA 1001 HP", capacity: 7, status: "Tersedia", icon: "bi-truck-front-fill" }, { id: 2, name: "Medium Bus 21", plate: "BA 7021 MB", capacity: 33, status: "Tersedia", icon: "bi-bus-front-fill" }],
            drivers: [{ id: 101, name: "Pak Budi", phone: "0812345678", status: "Standby" }],
            trips: []
        };
    },
    created() { this.loadData(); this.updateTime(); setInterval(this.updateTime, 1000); },
    watch: {
        view(val) { localStorage.setItem('sutan_view_v83', val); }, // Auto-save Page
        bookings: { handler() { this.saveData(); }, deep: true },
        fleet: { handler() { this.saveData(); }, deep: true },
        drivers: { handler() { this.saveData(); }, deep: true },
        trips: { handler() { this.saveData(); }, deep: true }
    },
    computed: {
        currentViewTitle() { const t = { dashboard: "Dashboard", bookingManagement: "Kelola Booking", dispatcher: "Dispatcher", bookingTravel: "Reservasi Travel", bookingBus: "Reservasi Bus", manifest: "Laporan", assets: "Aset" }; return t[this.view] || "Sutan Raya"; },

        // Stats
        todayRevenue() { return this.bookings.filter(b => b.date === new Date().toISOString().slice(0, 10)).reduce((a, b) => a + b.totalPrice, 0); },
        todayPax() { return this.bookings.filter(b => b.date === new Date().toISOString().slice(0, 10)).length; },
        pendingValidationCount() { return this.bookings.filter(b => b.validationStatus === 'Menunggu Validasi').length; },

        // Groups & Trips
        activeTrips() { return this.trips.filter(t => !['Tiba', 'Batal'].includes(t.status)); },
        outboundTrips() { return this.activeTrips.filter(t => t.routeConfig.origin === 'Padang' || (t.routeConfig.name && t.routeConfig.name.startsWith('Padang'))); },
        inboundTrips() { return this.activeTrips.filter(t => t.routeConfig.destination === 'Padang' || (t.routeConfig.name && !t.routeConfig.name.startsWith('Padang'))); },

        groupedBookings() {
            const groups = {};
            // Logic: Only valid payments show up in dispatcher
            const pending = this.bookings.filter(b => b.status === 'Pending' && (b.paymentStatus === 'Lunas' || b.paymentStatus === 'DP') && b.validationStatus === 'Valid');
            pending.forEach(b => {
                const key = `${b.routeId}|${b.date}|${b.time || 'Bus'}`;
                if (!groups[key]) {
                    const r = this.routeConfig.find(x => x.id === b.routeId) || this.busRouteConfig.find(x => x.id === b.routeId);
                    groups[key] = { key, routeId: b.routeId, routeOrigin: r?.origin || 'Padang', routeDest: r?.destination || 'Luar Kota', routeConfig: r, date: b.date, time: b.time || 'Flexible', totalPassengers: 0, passengers: [], serviceType: b.serviceType };
                }
                groups[key].passengers.push(b);
                groups[key].totalPassengers += (b.serviceType.includes('Bus') || b.serviceType === 'Carter' || b.serviceType === 'Dropping') ? 1 : (b.seatCount || 1);
            });
            return Object.values(groups).sort((a, b) => a.time.localeCompare(b.time));
        },
        pendingGroupsCount() { return this.groupedBookings.length; },

        // Booking Management Helper
        getManagedBookings() {
            let items = this.view === 'bookingManagement' && this.bookingManagementTab === 'bus' ? this.bookings.filter(b => b.serviceType === 'Bus Pariwisata') : this.bookings.filter(b => ['Travel', 'Carter', 'Dropping'].includes(b.serviceType));
            if (this.searchTerm) { const t = this.searchTerm.toLowerCase(); items = items.filter(b => b.passengerName?.toLowerCase().includes(t) || b.routeId?.toLowerCase().includes(t)); }
            return items.sort((a, b) => new Date(b.id) - new Date(a.id)); // Newest first
        },
        busBookings() { return this.bookings.filter(b => b.serviceType === 'Bus Pariwisata'); },

        // Form Helpers
        selectedRoute() { return this.routeConfig.find(r => r.id === this.bookingForm.data.routeId); },
        getBusRouteName() { const r = this.busRouteConfig.find(x => x.id === this.bookingBusForm.routeId); return r ? r.name : '-'; },
        getBusDailyPrice() {
            const r = this.busRouteConfig.find(x => x.id === this.bookingBusForm.routeId); if (!r) return 0;
            if (this.bookingBusForm.type === 'Medium') return this.bookingBusForm.seatCapacity == 33 ? r.prices.s33 : r.prices.s35;
            if (r.isLongTrip) return this.bookingBusForm.packageType === 'AllIn' ? r.big.allin : r.big.base;
            return this.bookingBusForm.seatCapacity == 45 ? (this.bookingBusForm.priceType === 'Kantor' ? r.big.s45.kantor : r.big.s45.agen) : (this.bookingBusForm.priceType === 'Kantor' ? r.big.s32.kantor : r.big.s32.agen);
        },
        getBusMinDays() { return this.busRouteConfig.find(x => x.id === this.bookingBusForm.routeId)?.minDays || 1; },
        isLongTrip() { return this.busRouteConfig.find(x => x.id === this.bookingBusForm.routeId)?.isLongTrip || false; },

        // Pricing
        currentTotalPrice() {
            if (this.view === 'bookingBus') return this.bookingBusForm.totalPrice;
            // Travel Pricing
            const d = this.bookingForm.data; const r = this.selectedRoute; if (!r) return 0;
            if (d.serviceType === 'Dropping') {
                let p = 1000000; if (r.id.includes('BKT')) p = d.isMultiStop ? 960000 : 900000; else if (r.id.includes('PYK')) p = d.isMultiStop ? 1200000 : 1100000;
                return p;
            } else if (d.serviceType === 'Carter') return (r.prices.carter || 1500000) * (d.duration || 1);
            return (this.bookingForm.selectedSeats.length || 1) * (d.passengerType === 'Umum' ? r.prices.umum : r.prices.pelajar);
        },

        // Calendar
        calendarDays() {
            const year = this.calendarYear; const month = this.calendarMonth;
            const firstDay = new Date(year, month, 1).getDay(); const startDay = firstDay === 0 ? 6 : firstDay - 1;
            const daysInMonth = new Date(year, month + 1, 0).getDate();
            const days = [];
            for (let i = startDay - 1; i >= 0; i--) days.push({ date: '', isCurrentMonth: false, events: [] });
            for (let i = 1; i <= daysInMonth; i++) {
                const dStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(i).padStart(2, '0')}`;
                days.push({ date: i, isCurrentMonth: true, isToday: new Date().toDateString() === new Date(year, month, i).toDateString(), events: this.busBookings.filter(b => b.date === dStr) });
            }
            while (days.length < 42) days.push({ date: '', isCurrentMonth: false, events: [] });
            return days;
        },
        dailyReportData() {
            const date = this.reportDate; const report = {};
            this.routeConfig.forEach(route => {
                report[route.id] = { route: route, schedules: [], total: { umumNom: 0, pelajarNom: 0, totalNom: 0 } };
                route.schedules.forEach(time => {
                    const bList = this.bookings.filter(b => b.routeId === route.id && b.date === date && b.time === time && b.status !== 'Batal');
                    let row = { time: time, umumPax: 0, umumNom: 0, pelajarPax: 0, pelajarNom: 0, totalNom: 0 };
                    bList.forEach(b => {
                        if (b.passengerType === 'Umum' || b.serviceType === 'Dropping' || b.serviceType === 'Carter') { row.umumPax += b.seatCount; row.umumNom += b.totalPrice; }
                        else { row.pelajarPax += b.seatCount; row.pelajarNom += b.totalPrice; }
                    });
                    row.totalNom = row.umumNom + row.pelajarNom;
                    report[route.id].total.umumNom += row.umumNom;
                    report[route.id].total.pelajarNom += row.pelajarNom;
                    report[route.id].total.totalNom += row.totalNom;
                    report[route.id].schedules.push(row);
                });
            });
            return report;
        }
    },
    methods: {
        changeView(v) { this.view = v; },
        toggleFullscreen() { this.isFullscreen = !this.isFullscreen; },

        // Booking Logic
        setServiceType(t) { this.bookingForm.data.serviceType = t; if (t !== 'Travel') { this.bookingForm.data.time = ''; this.bookingForm.selectedSeats = []; } },
        resetSeatSelection() { this.bookingForm.selectedSeats = []; },
        toggleSeat(id) { if (this.isSeatOccupied(id)) return alert('Terisi'); const s = this.bookingForm.selectedSeats; const i = s.indexOf(id); if (i === -1) s.push(id); else s.splice(i, 1); },
        isSeatOccupied(id) {
            const d = this.bookingForm.data; if (!d.routeId || !d.date || !d.time) return false;
            const ex = this.bookings.filter(b => b.routeId === d.routeId && b.date === d.date && b.time === d.time && b.status !== 'Batal');
            if (ex.some(b => b.serviceType !== 'Travel')) return true;
            let occ = []; ex.forEach(b => { if (b.seatNumbers) occ.push(...b.seatNumbers.split(', ')); });
            return occ.includes(id);
        },
        isSeatSelected(id) { return this.bookingForm.selectedSeats.includes(id); },
        calculatePrice() { /* Computed handled */ },
        calculateBusPrice() { /* Computed handled */ },

        handleKTMUpload(e) { if (e.target.files[0]) this.bookingForm.data.ktmProof = e.target.files[0].name; },
        handlePaymentProofUpload(e) { if (e.target.files[0]) this.tempPayment.proof = e.target.files[0].name; },

        submitBooking() {
            const isBus = this.view === 'bookingBus';
            const d = isBus ? this.bookingBusForm : this.bookingForm.data;
            const price = this.currentTotalPrice;

            if (!d.routeId || !d.date || price === 0) return alert("Data Belum Lengkap!");
            if (!isBus && d.serviceType === 'Travel' && this.bookingForm.selectedSeats.length === 0) return alert("Pilih Kursi!");

            // Payment Validation
            const pm = this.currentPaymentMethod;
            let pStat = 'Menunggu Validasi', vStat = 'Menunggu Validasi';
            if (pm === 'Cash') {
                if (!this.tempPayment.loc || !this.tempPayment.recv) return alert("Lengkapi Info Cash!");
                pStat = 'Lunas'; vStat = 'Valid';
            } else if (pm === 'DP') {
                if (!this.tempPayment.proof || this.tempPayment.dpAmount < 100000) return alert("Upload Bukti & Nominal DP!");
                pStat = 'DP';
            } else {
                if (!this.tempPayment.proof) return alert("Upload Bukti Transfer!");
            }

            // Construct Object
            const newBooking = {
                id: Date.now(),
                ...d,
                totalPrice: price,
                seatCount: isBus ? 1 : (d.serviceType === 'Travel' ? this.bookingForm.selectedSeats.length : 1),
                seatNumbers: isBus ? 'Bus Unit' : (d.serviceType === 'Travel' ? this.bookingForm.selectedSeats.join(', ') : 'Full Unit'),
                paymentMethod: pm, paymentStatus: pStat, validationStatus: vStat,
                paymentLocation: this.tempPayment.loc, paymentReceiver: this.tempPayment.recv, paymentProof: this.tempPayment.proof,
                status: 'Pending',
                serviceType: isBus ? 'Bus Pariwisata' : d.serviceType,
                routeName: isBus ? this.getBusRouteName : d.routeId
            };

            this.bookings.push(newBooking);

            // Reset & Redirect
            alert("Booking Berhasil Disimpan!");
            if (confirm("Lanjut ke menu Kelola Booking?")) this.view = 'bookingManagement';

            // Reset Forms
            this.bookingForm.selectedSeats = [];
            this.tempPayment = { loc: '', recv: '', proof: '', dpAmount: 0 };
        },

        // Operation Logic
        validatePaymentModal(b) { this.validationData = b; this.isProofModalVisible = true; },
        confirmValidation(b) { if (confirm("Validasi Pembayaran?")) { b.validationStatus = 'Valid'; if (b.paymentStatus !== 'DP') b.paymentStatus = 'Lunas'; this.isProofModalVisible = false; } },
        openDispatchModal(g) { this.dispatchForm.group = g; this.dispatchForm.fleetId = ""; this.dispatchForm.driverId = ""; this.isDispatchModalVisible = true; },
        processDispatch() {
            const { group, fleetId, driverId } = this.dispatchForm;
            if (!fleetId || !driverId) return alert("Pilih Armada & Supir");

            // Double Check Payment
            const unpaid = group.passengers.filter(p => p.validationStatus !== 'Valid');
            if (unpaid.length > 0) return alert("Ada penumpang belum validasi pembayaran!");

            const f = this.fleet.find(x => x.id === fleetId); const d = this.drivers.find(x => x.id === driverId);
            this.trips.push({
                id: Date.now(), routeConfig: group, fleet: f, driver: d,
                passengers: [...group.passengers], status: "On Trip", departureTime: new Date()
            });

            // Update Status
            group.passengers.forEach(p => { const b = this.bookings.find(x => x.id === p.id); if (b) b.status = 'On Trip'; });
            f.status = "On Trip"; d.status = "Jalan";

            this.isDispatchModalVisible = false;
            this.view = 'dashboard';
        },
        openTripControl(t) { this.activeTripControl = t; this.isTripControlVisible = true; },
        updateTripStatus(t, s) {
            t.status = s;
            const f = this.fleet.find(x => x.id === t.fleet.id);
            if (s === 'Tiba') {
                if (confirm("Selesaikan Trip?")) {
                    f.status = "Tersedia"; this.drivers.find(x => x.id === t.driver.id).status = "Standby";
                    t.passengers.forEach(p => { const b = this.bookings.find(x => x.id === p.id); if (b) b.status = 'Tiba'; });
                } else t.status = 'On Trip';
            } else if (s === 'Kendala') f.status = "Perbaikan";
            this.isTripControlVisible = false;
        },

        // Utils
        openVehicleModal(v) { this.vehicleModal = { mode: v ? 'edit' : 'add', data: v ? { ...v } : { id: Date.now(), name: "", plate: "", capacity: 7, status: "Tersedia", icon: "bi-truck-front-fill" } }; this.isVehicleModalVisible = true; },
        saveVehicle() { if (this.vehicleModal.mode === 'add') this.fleet.push(this.vehicleModal.data); else { const i = this.fleet.findIndex(x => x.id === this.vehicleModal.data.id); this.fleet[i] = this.vehicleModal.data; } this.isVehicleModalVisible = false; },
        openDriverModal(d) { this.driverModal = { mode: d ? 'edit' : 'add', data: d ? { ...d } : { id: Date.now(), name: "", phone: "", status: "Standby" } }; this.isDriverModalVisible = true; },
        saveDriver() { if (this.driverModal.mode === 'add') this.drivers.push(this.driverModal.data); else { const i = this.drivers.findIndex(x => x.id === this.driverModal.data.id); this.drivers[i] = this.driverModal.data; } this.isDriverModalVisible = false; },
        closeVehicleModal() { this.isVehicleModalVisible = false; }, closeDriverModal() { this.isDriverModalVisible = false; },

        saveData() { localStorage.setItem('sutan_v83_data', JSON.stringify({ b: this.bookings, f: this.fleet, d: this.drivers, t: this.trips })); },
        loadData() {
            const d = JSON.parse(localStorage.getItem('sutan_v83_data'));
            if (d) { this.bookings = d.b || []; this.fleet = d.f || this.fleet; this.drivers = d.d || this.drivers; this.trips = d.t || []; }
        },

        changeMonth(s) { this.calendarMonth += s; if (this.calendarMonth > 11) { this.calendarMonth = 0; this.calendarYear++ } else if (this.calendarMonth < 0) { this.calendarMonth = 11; this.calendarYear-- } },
        getMonthName(m) { return ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"][m]; },
        formatRupiah(n) { return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(n); },
        formatDate(d) { return d ? new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' }) : '-'; },
        updateTime() { const n = new Date(); this.currentTime = n.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }); this.currentDate = n.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long' }); },
        getTripCardClass(s) { return s === 'On Trip' ? 'border-blue-200 bg-blue-50/30' : (s === 'Tiba' ? 'border-green-200 bg-green-50/30' : (s === 'Kendala' ? 'border-red-200 bg-red-50/30' : 'border-orange-200 bg-orange-50/30')); },
        getTripStatusBadge(s) { return s === 'On Trip' ? 'bg-blue-500' : (s === 'Tiba' ? 'bg-green-500' : (s === 'Kendala' ? 'bg-red-500' : 'bg-orange-500')); },
        getVehicleStatusClass(s) { return s === 'Tersedia' ? 'bg-green-100 text-green-700' : (s === 'On Trip' ? 'bg-blue-100 text-blue-700' : (s === 'Perbaikan' ? 'bg-red-100 text-red-700' : 'bg-gray-100')); },
        getDriverStatusClass(s) { return s === 'Standby' ? 'bg-green-100 text-green-700' : (s === 'Jalan' ? 'bg-blue-100 text-blue-700' : 'bg-gray-200'); }
    }
}).mount("#app");