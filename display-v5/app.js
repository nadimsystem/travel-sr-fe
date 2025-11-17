const { createApp } = Vue;

createApp({
    data() {
        return {
            view: "dashboard", // Default View
            isFullscreen: false,
            currentTime: "",
            currentDate: "",
            
            // Search Terms
            armadaSearchTerm: "",
            driverSearchTerm: "",
            historySearchTerm: "",
            
            // Modals
            isBookingModalVisible: false,
            isDispatchModalVisible: false,
            isTicketModalVisible: false,
            isProofModalVisible: false,
            isVehicleModalVisible: false,
            isDriverModalVisible: false,
            
            // Temp Data
            bookingMode: "Admin", // 'Admin' or 'Online'
            ticketData: null,
            proofData: { url: "", booking: null },
            pricesSavedMessage: "",
            
            // Forms
            bookingForm: { data: null, selectedSeats: [] },
            dispatchForm: { group: null, fleetId: "", driverId: "" },
            vehicleModal: { mode: "add", data: null },
            driverModal: { mode: "add", data: null },

            // --- CONFIGURATION ---
            
            // Route & Price Matrix
            routeConfig: [
                { id: "PDG-BKT", origin: "Padang", destination: "Bukittinggi", prices: { umum: 120000, pelajar: 100000, dropping: 850000 }, schedules: ["08:00", "10:00", "12:00", "14:00", "16:00", "18:00", "20:00"] },
                { id: "BKT-PDG", origin: "Bukittinggi", destination: "Padang", prices: { umum: 120000, pelajar: 100000, dropping: 850000 }, schedules: ["06:00", "08:00", "10:00", "13:00", "15:00", "17:00", "18:00", "19:00"] },
                { id: "PDG-PYK", origin: "Padang", destination: "Payakumbuh", prices: { umum: 150000, pelajar: 130000, dropping: 1000000 }, schedules: ["08:00", "10:00", "14:00", "18:00"] },
                { id: "PYK-PDG", origin: "Payakumbuh", destination: "Padang", prices: { umum: 150000, pelajar: 130000, dropping: 1000000 }, schedules: ["05:00", "07:00", "10:00", "14:00", "17:00"] }
            ],
            
            // Hiace Seat Layout
            seatLayout: [
                { row: 1, seats: [{id:"CC", type:"seat"}, {id:"driver", type:"driver"}], label: "Depan" },
                { row: 2, seats: [{id:"1", type:"seat"}, {id:"2", type:"seat"}], label: "Tengah" },
                { row: 3, seats: [{id:"3", type:"seat"}, {id:"4", type:"seat"}], label: "Tengah" },
                { row: 4, seats: [{id:"5", type:"seat"}, {id:"6", type:"seat"}, {id:"7", type:"seat"}], label: "Belakang" }
            ],

            // --- DATA (DATABASE) ---
            
            // Initial Bookings (Dummy with Validation Status)
            bookings: [
                { 
                    id: 101, serviceType:"Travel", routeId:"PDG-BKT", date: new Date().toISOString().slice(0,10), time:"08:00", 
                    passengerName:"Budi Santoso", passengerPhone:"081234567", passengerType:"Umum", seatCount:1, seatNumbers:"2", 
                    pickupAddress:"Jl. Khatib", pickupMapUrl:"", dropoffAddress:"Jam Gadang", 
                    paymentMethod:"Transfer", paymentStatus:"Lunas", validationStatus: "Valid", // Sudah Valid
                    paymentDueDate:"", paymentProof: "bukti1.jpg", totalPrice:120000, status:"Pending" 
                },
                { 
                    id: 102, serviceType:"Travel", routeId:"PDG-BKT", date: new Date().toISOString().slice(0,10), time:"08:00", 
                    passengerName:"Siti Nurhaliza", passengerPhone:"08111111", passengerType:"Pelajar", seatCount:1, seatNumbers:"3", 
                    pickupAddress:"Jl. Sudirman", pickupMapUrl:"", dropoffAddress:"Ps Ateh", 
                    paymentMethod:"Transfer", paymentStatus:"Menunggu Validasi", validationStatus: "Menunggu Validasi", // Butuh Validasi
                    paymentDueDate:"", paymentProof: "bukti2.jpg", totalPrice:100000, status:"Pending" 
                }
            ],
            
            trips: [],

            fleet: [
                { id: 1, name: "Hiace Premio SR-01", type: "Hiace Premio", plate: "BA 1001 HP", capacity: 7, status: "Tersedia", icon: "bi-truck-front-fill", hargaSewa: 1600000, hargaPerOrang: 150000, biayaOperasional: 600000, requiredLicense: "A Umum", lastService: "2025-08-01", nextService: "2026-02-01" },
                { id: 2, name: "Hiace Premio SR-02", type: "Hiace Premio", plate: "BA 1002 HP", capacity: 7, status: "Tersedia", icon: "bi-truck-front-fill", hargaSewa: 1600000, hargaPerOrang: 150000, biayaOperasional: 600000, requiredLicense: "A Umum", lastService: "2025-08-01", nextService: "2026-02-01" },
                { id: 3, name: "Hiace Commuter SR-03", type: "Hiace Commuter", plate: "BA 1003 HC", capacity: 7, status: "Tersedia", icon: "bi-truck-front-fill", hargaSewa: 1500000, hargaPerOrang: 130000, biayaOperasional: 550000, requiredLicense: "A Umum", lastService: "2025-09-15", nextService: "2026-03-15" },
                { id: 14, name: "Medium Bus SR-21", type: "Medium Bus", plate: "BA 7021 MB", capacity: 33, status: "Tersedia", icon: "bi-bus-front-fill", hargaSewa: 2800000, biayaOperasional: 1100000, requiredLicense: "B1 Umum", lastService: "2025-07-20", nextService: "2025-11-20" },
                { id: 25, name: "Big Bus SR-41", type: "Big Bus", plate: "BA 7041 BB", capacity: 45, status: "Tersedia", icon: "bi-bus-front-fill", hargaSewa: 4000000, biayaOperasional: 1500000, requiredLicense: "B2 Umum", lastService: "2025-09-01", nextService: "2025-12-01" }
            ],
            
            drivers: [
                { id: 101, name: "Budi Santoso", licenseType: "A Umum", phone: "081234567890", status: "Dalam Perjalanan" },
                { id: 102, name: "Joko Susilo", licenseType: "B1 Umum", phone: "081234567891", status: "Dalam Perjalanan" },
                { id: 103, name: "Anton Wijaya", licenseType: "A Umum", phone: "081234567892", status: "Standby" },
                { id: 104, name: "Eko Prasetyo", licenseType: "B2 Umum", phone: "081234567893", status: "Dalam Perjalanan" },
                { id: 106, name: "Doni Firmansyah", licenseType: "A Umum", phone: "081234567895", status: "Standby" }
            ]
        };
    },
    computed: {
        currentViewTitle() {
            const titles = { dashboard: "Dashboard Operasional", process: "Flight Deck Dispatcher", fleet: "Manajemen Armada", drivers: "Manajemen Supir", history: "Riwayat Perjalanan", admin: "Panel Admin", onlineBooking: "Booking Online" };
            return titles[this.view] || "Sutan Raya";
        },
        
        // --- DASHBOARD STATS ---
        todayBookingsCount() { return this.bookings.filter(b => b.date === new Date().toISOString().slice(0,10)).length; },
        pendingGroupsCount() { return this.groupedBookings.length; },
        activeTripsCount() { return this.activeTrips.length; },
        pendingValidationCount() { return this.bookings.filter(b => b.validationStatus === 'Menunggu Validasi').length; },
        todayRevenue() { return this.bookings.filter(b => b.date === new Date().toISOString().slice(0,10)).reduce((sum, b) => sum + b.totalPrice, 0); },
        
        // --- DISPATCHER LOGIC ---
        groupedBookings() {
            const groups = {};
            const pending = this.bookings.filter(b => b.status === 'Pending');
            
            pending.forEach(b => {
                const key = `${b.routeId}|${b.date}|${b.time}`;
                if (!groups[key]) {
                    const r = this.routeConfig.find(x => x.id === b.routeId);
                    groups[key] = {
                        key, routeId: b.routeId, routeOrigin: r?.origin, routeDest: r?.destination,
                        date: b.date, time: b.time, totalPassengers: 0, passengers: []
                    };
                }
                groups[key].passengers.push(b);
                const count = b.serviceType === 'Dropping' ? 1 : b.seatCount;
                groups[key].totalPassengers += count;
            });
            return Object.values(groups).sort((a, b) => a.time.localeCompare(b.time));
        },
        activeTrips() { return this.trips.filter(t => t.status === 'Berangkat'); },
        
        // --- FILTERS ---
        filteredFleet() {
            if (!this.armadaSearchTerm) return this.fleet;
            const term = this.armadaSearchTerm.toLowerCase();
            return this.fleet.filter(v => v.name.toLowerCase().includes(term) || v.plate.toLowerCase().includes(term));
        },
        filteredDrivers() {
            if (!this.driverSearchTerm) return this.drivers;
            const term = this.driverSearchTerm.toLowerCase();
            return this.drivers.filter(d => d.name.toLowerCase().includes(term));
        },
        filteredHistory() {
            const history = this.trips.filter(t => t.status === 'Tiba');
            return history;
        },
        selectedRoute() { return this.routeConfig.find(r => r.id === this.bookingForm.data?.routeId); }
    },
    methods: {
        // --- KETERSEDIAAN KURSI (REAL-TIME) ---
        getOccupiedSeats() {
            const formData = this.bookingForm.data;
            if (!formData.routeId || !formData.date || !formData.time) return [];

            // Filter booking lain di slot yang sama
            const existing = this.bookings.filter(b => 
                b.routeId === formData.routeId &&
                b.date === formData.date &&
                b.time === formData.time &&
                b.status !== 'Batal'
            );

            // Jika ada yang Dropping, blokir semua
            if (existing.some(b => b.serviceType === 'Dropping')) return ['ALL'];

            let occupied = [];
            existing.forEach(b => {
                if (b.serviceType === 'Travel' && b.seatNumbers) {
                    const seats = b.seatNumbers.split(',').map(s => s.trim());
                    occupied.push(...seats);
                }
            });
            return occupied;
        },
        isSeatOccupied(seatId) {
            const occupied = this.getOccupiedSeats();
            if (occupied.includes('ALL')) return true;
            return occupied.includes(seatId);
        },
        resetSeatSelection() {
            this.bookingForm.selectedSeats = [];
            this.bookingForm.data.seatNumbers = "";
            this.bookingForm.data.seatCount = 1;
            this.bookingForm.data.totalPrice = 0;
            
            // Alert jika Dropping
            if (this.getOccupiedSeats().includes('ALL')) {
                alert("INFO: Jadwal ini sudah di-charter penuh oleh penumpang lain.");
            }
        },

        // --- BOOKING ---
        getFreshData(type) {
            return {
                id: Date.now(), serviceType: "Travel",
                routeId: "", date: new Date().toISOString().slice(0,10), time: "",
                passengerName: "", passengerPhone: "", passengerType: "Umum",
                seatCount: 1, seatNumbers: "", pickupAddress: "", pickupMapUrl: "", dropoffAddress: "",
                paymentMethod: type === 'Admin' ? 'Cash' : 'Transfer',
                paymentStatus: type === 'Admin' ? 'Tagihan' : 'Menunggu Validasi',
                validationStatus: type === 'Admin' ? 'Valid' : 'Menunggu Validasi',
                paymentDueDate: "", paymentLocation: "", paymentProof: null, totalPrice: 0
            };
        },
        openBookingModal(mode = 'Admin') {
            this.bookingMode = mode;
            this.bookingForm.data = this.getFreshData(mode);
            this.bookingForm.selectedSeats = [];
            this.isBookingModalVisible = true;
        },
        setServiceType(type) {
            this.bookingForm.data.serviceType = type;
            if(type==='Dropping') { 
                this.bookingForm.data.time = ""; // Reset time
                this.bookingForm.selectedSeats = []; 
            }
            this.calculatePrice();
        },
        calculatePrice() {
            const d = this.bookingForm.data;
            const r = this.selectedRoute;
            if(!r) return;

            if(d.serviceType === 'Dropping') {
                d.totalPrice = r.prices.dropping;
                d.seatCount = 1; 
                d.seatNumbers = "1 Unit";
            } else {
                const seats = this.bookingForm.selectedSeats;
                d.seatCount = seats.length > 0 ? seats.length : 1;
                const price = d.passengerType === 'Umum' ? r.prices.umum : r.prices.pelajar;
                d.totalPrice = price * d.seatCount;
                d.seatNumbers = seats.join(", ");
            }
        },
        toggleSeat(id) {
            // Cek Occupied
            if (this.isSeatOccupied(id)) return alert("Kursi sudah terisi!");

            const list = this.bookingForm.selectedSeats;
            const idx = list.indexOf(id);
            if(idx === -1) list.push(id); else list.splice(idx, 1);
            list.sort();
            this.calculatePrice();
        },
        isSeatSelected(id) { return this.bookingForm.selectedSeats.includes(id); },
        handlePaymentMethodChange() {
            if (this.bookingForm.data.paymentMethod === 'Cash') {
                this.bookingForm.data.paymentStatus = 'Tagihan';
                this.bookingForm.data.validationStatus = 'Valid'; // Cash admin dianggap valid
            } else {
                // Jika admin input transfer, mungkin sudah valid. Jika online, menunggu.
                this.bookingForm.data.paymentStatus = 'Menunggu Validasi';
                this.bookingForm.data.validationStatus = 'Menunggu Validasi';
            }
        },
        handleFileUpload(e) {
            if(e.target.files[0]) this.bookingForm.data.paymentProof = e.target.files[0].name;
        },
        saveBooking() {
            const d = this.bookingForm.data;
            if(!d.routeId || !d.time || !d.passengerName) return alert("Lengkapi Data!");
            if(d.serviceType === 'Travel' && this.bookingForm.selectedSeats.length === 0) return alert("Pilih kursi!");
            
            d.status = "Pending";
            this.bookings.push({...d});
            
            if(this.bookingMode === 'Online') {
                this.viewTicket(d);
            }
            this.isBookingModalVisible = false;
        },

        // --- DISPATCHER ---
        openDispatchModal(group) {
            this.dispatchForm.group = group;
            this.dispatchForm.fleetId = "";
            this.dispatchForm.driverId = "";
            this.isDispatchModalVisible = true;
        },
        processDispatch() {
            if(!this.dispatchForm.fleetId || !this.dispatchForm.driverId) return alert("Pilih Aset!");
            const f = this.fleet.find(x=>x.id===this.dispatchForm.fleetId);
            const d = this.drivers.find(x=>x.id===this.dispatchForm.driverId);
            const group = this.dispatchForm.group;

            this.trips.push({
                id: Date.now(), routeConfig: group, fleet: f, driver: d,
                passengers: [...group.passengers], status: "Berangkat",
                totalRevenue: group.passengers.reduce((a,b)=>a+b.totalPrice,0),
                departureTime: new Date()
            });

            group.passengers.forEach(p => {
                const b = this.bookings.find(x=>x.id===p.id);
                if(b) b.status = "Assigned";
            });
            f.status = "On Trip"; d.status = "Dalam Perjalanan";
            this.isDispatchModalVisible = false;
        },
        completeTrip(t) {
            if(confirm("Trip Selesai? Armada akan kembali tersedia.")) {
                t.status = "Tiba";
                this.fleet.find(f=>f.id===t.fleet.id).status = "Tersedia";
                this.drivers.find(d=>d.id===t.driver.id).status = "Standby";
            }
        },

        // --- VALIDATION & TOOLS ---
        togglePayment(p) {
            // Jika cash tagihan, admin bisa lunaskan
            if(p.paymentStatus === 'Tagihan' && confirm(`Tandai pembayaran ${p.passengerName} LUNAS?`)) {
                p.paymentStatus = 'Lunas';
            }
        },
        viewProof(p) {
            this.proofData = { url: p.paymentProof, booking: p };
            this.isProofModalVisible = true;
        },
        validatePayment(booking) {
            if(confirm("Validasi pembayaran ini sebagai LUNAS?")) {
                booking.validationStatus = 'Valid';
                booking.paymentStatus = 'Lunas';
                this.isProofModalVisible = false;
            }
        },
        copyWa(p) {
            const type = p.serviceType === 'Dropping' ? 'CHARTER' : 'TRAVEL';
            const txt = `*SUTAN RAYA - ${type}*\nJadwal: ${p.time}\nNama: ${p.passengerName}\nKursi: ${p.seatNumbers}\nHP: ${p.passengerPhone}\nJemput: ${p.pickupMapUrl} (${p.pickupAddress})\nAntar: ${p.dropoffAddress}`;
            navigator.clipboard.writeText(txt).then(() => alert("Data disalin ke Clipboard!"));
        },
        viewTicket(p) { this.ticketData = p; this.isTicketModalVisible = true; },
        toggleFullscreen() { this.isFullscreen = !this.isFullscreen; },

        // --- CRUD MODALS ---
        openVehicleModal(v) { 
            this.vehicleModal.mode = v ? 'edit' : 'add';
            this.vehicleModal.data = v ? {...v} : { id: Date.now(), name: "", type: "Hiace Premio", plate: "", capacity: 7, status: "Tersedia" };
            this.isVehicleModalVisible = true;
        },
        closeVehicleModal() { this.isVehicleModalVisible = false; },
        saveVehicle() {
            if(this.vehicleModal.mode==='add') this.fleet.push(this.vehicleModal.data);
            else { const idx = this.fleet.findIndex(x=>x.id===this.vehicleModal.data.id); this.fleet[idx]=this.vehicleModal.data; }
            this.closeVehicleModal();
        },
        openDriverModal(d) {
            this.driverModal.mode = d ? 'edit' : 'add';
            this.driverModal.data = d ? {...d} : { id: Date.now(), name: "", licenseType: "A Umum", phone: "", status: "Standby" };
            this.isDriverModalVisible = true;
        },
        closeDriverModal() { this.isDriverModalVisible = false; },
        saveDriver() {
            if(this.driverModal.mode==='add') this.drivers.push(this.driverModal.data);
            else { const idx = this.drivers.findIndex(x=>x.id===this.driverModal.data.id); this.drivers[idx]=this.driverModal.data; }
            this.closeDriverModal();
        },
        savePrices() { this.pricesSavedMessage="Harga tersimpan!"; setTimeout(()=>this.pricesSavedMessage="", 2000); },

        // --- UTILS ---
        isServiceDue(d) { return new Date(d) < new Date(new Date().setDate(new Date().getDate() + 30)); },
        getVehicleStatusClass(s) { return s==='Tersedia'?'bg-green-100 text-green-700':(s==='On Trip'?'bg-blue-100 text-blue-700':'bg-red-100 text-red-700'); },
        getDriverStatusClass(s) { return s==='Standby'?'bg-green-100 text-green-700':(s.includes('Perjalanan')?'bg-blue-100 text-blue-700':'bg-gray-200'); },
        getRouteName(id) { const r = this.routeConfig.find(x=>x.id===id); return r ? `${r.origin} - ${r.destination}` : id; },
        formatRupiah(n) { return new Intl.NumberFormat('id-ID', {style:'currency', currency:'IDR', minimumFractionDigits:0}).format(n); },
        formatDate(d) { return new Date(d).toLocaleDateString('id-ID', {day:'numeric', month:'short'}); },
        formatFullDate(d) { return new Date(d).toLocaleDateString('id-ID', {day:'2-digit', month:'short', year:'numeric'}); },
        formatTime(d) { return new Date(d).toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit'}); },
        updateTime() {
            const n = new Date();
            this.currentTime = n.toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit'});
            this.currentDate = n.toLocaleDateString('id-ID', {weekday:'long', day:'numeric', month:'long'});
        }
    },
    mounted() { setInterval(this.updateTime, 1000); this.updateTime(); }
}).mount("#app");