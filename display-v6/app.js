const { createApp } = Vue;

const appConfig = {
    data() {
        return {
            // System State
            view: "dashboard", // dashboard, process, reports, fleet, drivers, onlineBooking (di booking.html)
            isPublicMode: false, // True jika dibuka di booking.html
            currentTime: "",
            currentDate: "",
            
            // Filters
            reportDate: new Date().toISOString().slice(0,10),
            
            // Modals State
            isBookingModalVisible: false,
            isDispatchModalVisible: false,
            isTicketModalVisible: false,
            isProofModalVisible: false,
            isVehicleModalVisible: false,
            isDriverModalVisible: false,
            
            // Temp Data
            ticketData: null,
            proofData: { url: "", booking: null },
            
            // Forms
            bookingForm: { data: null, selectedSeats: [] },
            dispatchForm: { group: null, fleetId: "", driverId: "" },
            vehicleModal: { mode: "add", data: null },
            driverModal: { mode: "add", data: null },

            // --- KONFIGURASI RUTE & HARGA (UPDATED) ---
            // Harga Carter dihitung dinamis di method calculatePrice
            routeConfig: [
                { id: "PDG-BKT", origin: "Padang", destination: "Bukittinggi", prices: { umum: 120000, pelajar: 100000 }, schedules: ["08:00", "10:00", "12:00", "14:00", "16:00", "18:00", "20:00"] },
                { id: "BKT-PDG", origin: "Bukittinggi", destination: "Padang", prices: { umum: 120000, pelajar: 100000 }, schedules: ["06:00", "08:00", "10:00", "13:00", "15:00", "17:00", "18:00", "19:00"] },
                { id: "PDG-PYK", origin: "Padang", destination: "Payakumbuh", prices: { umum: 150000, pelajar: 130000 }, schedules: ["08:00", "10:00", "14:00", "18:00"] },
                { id: "PYK-PDG", origin: "Payakumbuh", destination: "Padang", prices: { umum: 150000, pelajar: 130000 }, schedules: ["05:00", "07:00", "10:00", "14:00", "17:00"] }
            ],
            
            seatLayout: [
                { row: 1, seats: [{id:"CC", type:"seat"}, {id:"driver", type:"driver"}], label: "Depan" },
                { row: 2, seats: [{id:"1", type:"seat"}, {id:"2", type:"seat"}], label: "Tengah" },
                { row: 3, seats: [{id:"3", type:"seat"}, {id:"4", type:"seat"}], label: "Tengah" },
                { row: 4, seats: [{id:"5", type:"seat"}, {id:"6", type:"seat"}, {id:"7", type:"seat"}], label: "Belakang" }
            ],

            // --- DATABASE (LOCAL STORAGE) ---
            bookings: [],
            fleet: [],
            drivers: [],
            trips: []
        };
    },
    created() {
        this.loadFromLS(); // Load data saat aplikasi mulai
        
        // Deteksi jika ini halaman public (booking.html)
        if(window.location.pathname.includes('booking.html')) {
            this.isPublicMode = true;
            this.view = 'onlineBooking';
            this.openBookingModal('Online'); // Auto open form
        }
    },
    watch: {
        // Auto Save ke LocalStorage setiap ada perubahan data
        bookings: { handler() { this.saveToLS(); }, deep: true },
        fleet: { handler() { this.saveToLS(); }, deep: true },
        drivers: { handler() { this.saveToLS(); }, deep: true },
        trips: { handler() { this.saveToLS(); }, deep: true }
    },
    computed: {
        currentViewTitle() {
            const titles = { dashboard: "Dashboard Eksekutif", process: "Dispatcher & Operasional", reports: "Laporan Harian", inventaris: "Manajemen Armada", drivers: "Data Supir", onlineBooking: "Booking Online" };
            return titles[this.view] || "Sutan Raya";
        },
        
        // --- REPORTING LOGIC (MIRIP EXCEL) ---
        dailyReportData() {
            const date = this.reportDate;
            const report = {};

            this.routeConfig.forEach(route => {
                report[route.id] = {
                    route: route,
                    schedules: [],
                    total: { umumPax: 0, umumNom: 0, pelajarPax: 0, pelajarNom: 0, totalPax: 0, totalNom: 0 }
                };

                route.schedules.forEach(time => {
                    // Ambil booking untuk rute+jam+tanggal ini
                    const routeBookings = this.bookings.filter(b => 
                        b.routeId === route.id && 
                        b.date === date && 
                        b.time === time && 
                        b.serviceType === 'Travel' && // Hanya Travel Reguler yg masuk tabel trayek
                        b.status !== 'Batal'
                    );

                    let row = {
                        time: time,
                        umumPax: 0, umumNom: 0,
                        pelajarPax: 0, pelajarNom: 0,
                        totalPax: 0, totalNom: 0
                    };

                    routeBookings.forEach(b => {
                        if(b.passengerType === 'Umum') {
                            row.umumPax += b.seatCount;
                            row.umumNom += b.totalPrice;
                        } else {
                            row.pelajarPax += b.seatCount;
                            row.pelajarNom += b.totalPrice;
                        }
                    });

                    row.totalPax = row.umumPax + row.pelajarPax;
                    row.totalNom = row.umumNom + row.pelajarNom;

                    // Add to Grand Total Route
                    report[route.id].total.umumPax += row.umumPax;
                    report[route.id].total.umumNom += row.umumNom;
                    report[route.id].total.pelajarPax += row.pelajarPax;
                    report[route.id].total.pelajarNom += row.pelajarNom;
                    report[route.id].total.totalPax += row.totalPax;
                    report[route.id].total.totalNom += row.totalNom;

                    report[route.id].schedules.push(row);
                });
            });
            return report;
        },
        dailyCharterReport() {
            // Laporan khusus Carteran / Dropping
            return this.bookings.filter(b => 
                b.date === this.reportDate && 
                b.serviceType === 'Dropping' && 
                b.status !== 'Batal'
            );
        },
        dailyCharterTotal() {
            return this.dailyCharterReport.reduce((sum, b) => sum + b.totalPrice, 0);
        },

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
        
        // --- OTHER COMPUTED ---
        selectedRoute() { return this.routeConfig.find(r => r.id === this.bookingForm.data?.routeId); },
        activeTrips() { return this.trips.filter(t => t.status === 'Berangkat'); },
        pendingValidationCount() { return this.bookings.filter(b => b.validationStatus === 'Menunggu Validasi').length; },
        filteredFleet() { return this.fleet; }, // Bisa ditambah search logic
        filteredDrivers() { return this.drivers; } // Bisa ditambah search logic
    },
    methods: {
        // --- LOCAL STORAGE MANAGEMENT ---
        saveToLS() {
            localStorage.setItem('sutan_bookings', JSON.stringify(this.bookings));
            localStorage.setItem('sutan_fleet', JSON.stringify(this.fleet));
            localStorage.setItem('sutan_drivers', JSON.stringify(this.drivers));
            localStorage.setItem('sutan_trips', JSON.stringify(this.trips));
        },
        loadFromLS() {
            if(localStorage.getItem('sutan_bookings')) this.bookings = JSON.parse(localStorage.getItem('sutan_bookings'));
            if(localStorage.getItem('sutan_fleet')) this.fleet = JSON.parse(localStorage.getItem('sutan_fleet'));
            else this.initDummyData(); // Init data jika kosong
            if(localStorage.getItem('sutan_drivers')) this.drivers = JSON.parse(localStorage.getItem('sutan_drivers'));
            if(localStorage.getItem('sutan_trips')) this.trips = JSON.parse(localStorage.getItem('sutan_trips'));
        },
        initDummyData() {
            // Data awal jika LocalStorage bersih
            this.fleet = [
                { id: 1, name: "Hiace Premio 01", plate: "BA 1001 HP", capacity: 7, status: "Tersedia" },
                { id: 2, name: "Hiace Commuter 02", plate: "BA 1002 HP", capacity: 7, status: "Tersedia" },
                { id: 3, name: "Medium Bus 21", plate: "BA 7021 MB", capacity: 33, status: "Tersedia" }
            ];
            this.drivers = [
                { id: 101, name: "Pak Budi", licenseType: "A Umum", phone: "0812345678", status: "Standby" },
                { id: 102, name: "Bang Rahmat", licenseType: "A Umum", phone: "0812999999", status: "Standby" }
            ];
        },

        // --- BOOKING & PRICING LOGIC (UPDATED) ---
        getFreshData(type) {
            return {
                id: Date.now(), serviceType: "Travel", // Travel / Dropping
                isMultiStop: false, // Checkbox untuk dropping banyak titik
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
            this.bookingForm.data = this.getFreshData(mode);
            this.bookingForm.selectedSeats = [];
            this.isBookingModalVisible = true;
        },
        setServiceType(type) {
            this.bookingForm.data.serviceType = type;
            if(type==='Dropping') { 
                this.bookingForm.data.time = ""; // Reset time for picker
                this.bookingForm.selectedSeats = []; 
            }
            this.calculatePrice();
        },
        calculatePrice() {
            const d = this.bookingForm.data;
            const r = this.selectedRoute;
            if(!r) return;

            if(d.serviceType === 'Dropping') {
                // LOGIKA HARGA CARTER BARU
                // PDG-BKT: 900k (Single) / 960k (Multi)
                // PDG-PYK: 1.1jt (Single) / 1.2jt (Multi)
                
                let price = 0;
                if (r.id.includes('BKT')) {
                    price = d.isMultiStop ? 960000 : 900000;
                } else if (r.id.includes('PYK')) {
                    price = d.isMultiStop ? 1200000 : 1100000;
                } else {
                    price = 1000000; // Default fallback
                }
                
                d.totalPrice = price;
                d.seatCount = 1; 
                d.seatNumbers = "1 Unit (Full)";
            } else {
                // Travel (Per Seat)
                const seats = this.bookingForm.selectedSeats;
                d.seatCount = seats.length > 0 ? seats.length : 1;
                const price = d.passengerType === 'Umum' ? r.prices.umum : r.prices.pelajar;
                d.totalPrice = price * d.seatCount;
                d.seatNumbers = seats.join(", ");
            }
        },
        toggleSeat(id) {
            if (this.isSeatOccupied(id)) return alert("Kursi sudah terisi!");
            const list = this.bookingForm.selectedSeats;
            const idx = list.indexOf(id);
            if(idx === -1) list.push(id); else list.splice(idx, 1);
            list.sort();
            this.calculatePrice();
        },
        
        // --- SEAT AVAILABILITY ---
        getOccupiedSeats() {
            const d = this.bookingForm.data;
            if (!d.routeId || !d.date || !d.time) return [];
            const existing = this.bookings.filter(b => b.routeId === d.routeId && b.date === d.date && b.time === d.time && b.status !== 'Batal');
            if (existing.some(b => b.serviceType === 'Dropping')) return ['ALL'];
            let occupied = [];
            existing.forEach(b => {
                if (b.seatNumbers) occupied.push(...b.seatNumbers.split(',').map(s => s.trim()));
            });
            return occupied;
        },
        isSeatOccupied(id) {
            const occupied = this.getOccupiedSeats();
            return occupied.includes('ALL') || occupied.includes(id);
        },
        resetSeatSelection() {
            this.bookingForm.selectedSeats = [];
            this.bookingForm.data.seatNumbers = "";
            this.calculatePrice();
        },

        // --- ACTIONS ---
        saveBooking() {
            const d = this.bookingForm.data;
            if(!d.routeId || !d.time || !d.passengerName) return alert("Lengkapi Data!");
            if(d.serviceType === 'Travel' && this.bookingForm.selectedSeats.length === 0) return alert("Pilih kursi!");
            if(this.isPublicMode && !d.paymentProof) return alert("Wajib Upload Bukti Transfer");

            d.status = "Pending";
            this.bookings.push({...d}); // Save to array (watcher will save to LS)
            
            if(this.isPublicMode) {
                this.viewTicket(d); // Show ticket immediately for user
            }
            this.isBookingModalVisible = false;
        },
        validatePayment(booking) {
            if(confirm("Validasi pembayaran ini sebagai LUNAS?")) {
                booking.validationStatus = 'Valid';
                booking.paymentStatus = 'Lunas';
                this.isProofModalVisible = false;
            }
        },
        
        // --- DISPATCHER & ASSETS ---
        openDispatchModal(group) { this.dispatchForm.group = group; this.isDispatchModalVisible = true; },
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
            f.status = "On Trip"; d.status = "Jalan";
            this.isDispatchModalVisible = false;
        },
        completeTrip(t) {
            if(confirm("Trip Selesai?")) {
                t.status = "Tiba";
                this.fleet.find(f=>f.id===t.fleet.id).status = "Tersedia";
                this.drivers.find(d=>d.id===t.driver.id).status = "Standby";
            }
        },
        
        // --- ASSET CRUD ---
        openVehicleModal(v) { this.vehicleModal.data = v ? {...v} : {id:Date.now(), name:"", plate:"", capacity:7, status:"Tersedia"}; this.vehicleModal.mode=v?'edit':'add'; this.isVehicleModalVisible=true; },
        saveVehicle() {
            if(this.vehicleModal.mode==='add') this.fleet.push(this.vehicleModal.data);
            else { const idx = this.fleet.findIndex(x=>x.id===this.vehicleModal.data.id); this.fleet[idx]=this.vehicleModal.data; }
            this.isVehicleModalVisible=false;
        },
        openDriverModal(d) { this.driverModal.data = d ? {...d} : {id:Date.now(), name:"", phone:"", status:"Standby"}; this.driverModal.mode=d?'edit':'add'; this.isDriverModalVisible=true; },
        saveDriver() {
            if(this.driverModal.mode==='add') this.drivers.push(this.driverModal.data);
            else { const idx = this.drivers.findIndex(x=>x.id===this.driverModal.data.id); this.drivers[idx]=this.driverModal.data; }
            this.isDriverModalVisible=false;
        },

        // --- HELPER ---
        togglePayment(p) { if(p.paymentStatus==='Tagihan' && confirm("Lunaskan?")) p.paymentStatus='Lunas'; },
        copyWa(p) { navigator.clipboard.writeText(`*SUTAN RAYA*\nNama: ${p.passengerName}\nKursi: ${p.seatNumbers}\nJemput: ${p.pickupMapUrl}`); alert("Disalin!"); },
        viewTicket(p) { this.ticketData = p; this.isTicketModalVisible = true; },
        viewProof(p) { this.proofData = {url: p.paymentProof, booking: p}; this.isProofModalVisible = true; },
        printTicket() { window.print(); },
        handleFileUpload(e) { if(e.target.files[0]) this.bookingForm.data.paymentProof = e.target.files[0].name; },
        handlePaymentMethodChange() { 
            this.bookingForm.data.paymentStatus = this.bookingForm.data.paymentMethod === 'Cash' ? 'Tagihan' : 'Menunggu Validasi'; 
            this.bookingForm.data.validationStatus = this.bookingForm.data.paymentMethod === 'Cash' ? 'Valid' : 'Menunggu Validasi';
        },
        
        // Utils
        getRouteName(id) { const r = this.routeConfig.find(x=>x.id===id); return r ? `${r.origin} - ${r.destination}` : id; },
        formatRupiah(n) { return new Intl.NumberFormat('id-ID', {style:'currency', currency:'IDR', minimumFractionDigits:0}).format(n); },
        formatDate(d) { return new Date(d).toLocaleDateString('id-ID', {day:'numeric', month:'short'}); },
        updateTime() { const n = new Date(); this.currentTime = n.toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit'}); this.currentDate = n.toLocaleDateString('id-ID', {weekday:'long', day:'numeric', month:'long'}); }
    },
    mounted() { setInterval(this.updateTime, 1000); this.updateTime(); }
};

createApp(appConfig).mount("#app");