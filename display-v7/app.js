const { createApp } = Vue;

createApp({
    data() {
        return {
            view: "dashboard",
            isFullscreen: false,
            currentTime: "",
            currentDate: "",
            reportDate: new Date().toISOString().slice(0,10),
            
            isBookingModalVisible: false,
            isDispatchModalVisible: false,
            isTicketModalVisible: false,
            isProofModalVisible: false,
            isVehicleModalVisible: false,
            isDriverModalVisible: false,
            
            bookingMode: "Admin",
            ticketData: null,
            validationData: null,
            
            bookingForm: { data: null, selectedSeats: [] },
            dispatchForm: { group: null, fleetId: "", driverId: "" },
            vehicleModal: { mode: "add", data: null },
            driverModal: { mode: "add", data: null },

            // Config
            routeConfig: [
                { id: "PDG-BKT", origin: "Padang", destination: "Bukittinggi", prices: { umum: 120000, pelajar: 100000, dropping: 900000 }, schedules: ["08:00", "10:00", "12:00", "14:00", "16:00", "18:00", "20:00"] },
                { id: "BKT-PDG", origin: "Bukittinggi", destination: "Padang", prices: { umum: 120000, pelajar: 100000, dropping: 900000 }, schedules: ["06:00", "08:00", "10:00", "13:00", "15:00", "17:00", "18:00", "19:00"] },
                { id: "PDG-PYK", origin: "Padang", destination: "Payakumbuh", prices: { umum: 150000, pelajar: 130000, dropping: 1100000 }, schedules: ["08:00", "10:00", "14:00", "18:00"] },
                { id: "PYK-PDG", origin: "Payakumbuh", destination: "Padang", prices: { umum: 150000, pelajar: 130000, dropping: 1100000 }, schedules: ["05:00", "07:00", "10:00", "14:00", "17:00"] }
            ],
            seatLayout: [
                { row: 1, seats: [{id:"CC", type:"seat"}, {id:"driver", type:"driver"}], label: "Depan" },
                { row: 2, seats: [{id:"1", type:"seat"}, {id:"2", type:"seat"}], label: "Tengah" },
                { row: 3, seats: [{id:"3", type:"seat"}, {id:"4", type:"seat"}], label: "Tengah" },
                { row: 4, seats: [{id:"5", type:"seat"}, {id:"6", type:"seat"}, {id:"7", type:"seat"}], label: "Belakang" }
            ],

            bookings: [],
            fleet: [
                { id: 1, name: "Hiace Premio 01", plate: "BA 1001 HP", capacity: 7, status: "Tersedia", icon: "bi-truck-front-fill" },
                { id: 2, name: "Hiace Commuter 02", plate: "BA 1002 HP", capacity: 7, status: "Tersedia", icon: "bi-truck-front-fill" },
                { id: 3, name: "Medium Bus 21", plate: "BA 7021 MB", capacity: 33, status: "Tersedia", icon: "bi-bus-front-fill" }
            ],
            drivers: [
                { id: 101, name: "Pak Budi", licenseType: "A Umum", phone: "0812345678", status: "Standby" },
                { id: 102, name: "Bang Rahmat", licenseType: "A Umum", phone: "0812999999", status: "Standby" }
            ],
            trips: []
        };
    },
    created() {
        this.loadData();
        this.openBookingModal();
    },
    watch: {
        bookings: { handler() { this.saveData(); }, deep: true },
        fleet: { handler() { this.saveData(); }, deep: true },
        drivers: { handler() { this.saveData(); }, deep: true },
        trips: { handler() { this.saveData(); }, deep: true }
    },
    computed: {
        currentViewTitle() { const t={dashboard:"Dashboard Eksekutif", dispatcher:"Flight Deck Dispatcher", booking:"Reservasi Tiket", manifest:"Laporan Keuangan", assets:"Manajemen Aset"}; return t[this.view] || "Sutan Raya System"; },
        todayRevenue() { return this.bookings.filter(b => b.date === new Date().toISOString().slice(0,10)).reduce((sum, b) => sum + b.totalPrice, 0); },
        todayPax() { return this.bookings.filter(b => b.date === new Date().toISOString().slice(0,10)).length; },
        pendingGroupsCount() { return this.groupedBookings.length; },
        activeTrips() { return this.trips.filter(t => t.status === 'Berangkat'); },
        pendingValidationCount() { return this.bookings.filter(b => b.validationStatus === 'Menunggu Validasi').length; },
        
        groupedBookings() {
            const groups = {};
            const pending = this.bookings.filter(b => b.status === 'Pending');
            pending.forEach(b => {
                const key = `${b.routeId}|${b.date}|${b.time}`;
                if (!groups[key]) {
                    const r = this.routeConfig.find(x => x.id === b.routeId);
                    groups[key] = { key, routeId: b.routeId, routeOrigin: r?.origin, routeDest: r?.destination, routeConfig: r, date: b.date, time: b.time, totalPassengers: 0, passengers: [] };
                }
                groups[key].passengers.push(b);
                const count = b.serviceType === 'Dropping' ? 1 : (b.seatCount || 1);
                groups[key].totalPassengers += count;
            });
            return Object.values(groups).sort((a, b) => a.time.localeCompare(b.time));
        },
        dailyReportData() {
            const date = this.reportDate;
            const report = {};
            this.routeConfig.forEach(route => {
                report[route.id] = { route: route, schedules: [], total: { umumNom: 0, pelajarNom: 0, totalNom: 0 } };
                route.schedules.forEach(time => {
                    const bList = this.bookings.filter(b => b.routeId === route.id && b.date === date && b.time === time && b.status !== 'Batal');
                    let row = { time: time, umumPax: 0, umumNom: 0, pelajarPax: 0, pelajarNom: 0, totalNom: 0 };
                    bList.forEach(b => {
                        if(b.passengerType === 'Umum' || b.serviceType === 'Dropping') { row.umumPax += b.seatCount; row.umumNom += b.totalPrice; }
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
        },
        selectedRoute() { return this.routeConfig.find(r => r.id === this.bookingForm.data?.routeId); }
    },
    methods: {
        openBookingModal(mode = 'Admin') {
            this.bookingMode = mode;
            this.bookingForm.data = {
                id: Date.now(), serviceType: "Travel",
                routeId: "", date: new Date().toISOString().slice(0,10), time: "",
                passengerName: "", passengerPhone: "", passengerType: "Umum",
                seatCount: 1, seatNumbers: "", pickupAddress: "", dropoffAddress: "",
                paymentMethod: "Cash", paymentStatus: "Menunggu Validasi", validationStatus: "Menunggu Validasi", totalPrice: 0, 
                ktmProof: null, paymentProof: null, paymentLocation: "", paymentReceiver: ""
            };
            this.bookingForm.selectedSeats = [];
            this.view = 'booking';
        },
        setServiceType(type) { this.bookingForm.data.serviceType = type; if(type==='Dropping') { this.bookingForm.data.time = ""; this.bookingForm.selectedSeats = []; } this.calculatePrice(); },
        calculatePrice() {
            const d = this.bookingForm.data; const r = this.selectedRoute; if(!r) return;
            if(d.serviceType === 'Dropping') { d.totalPrice = r.prices.dropping; d.seatCount = 1; d.seatNumbers = "Full Unit"; } 
            else { d.seatCount = this.bookingForm.selectedSeats.length || 1; const price = d.passengerType === 'Umum' ? r.prices.umum : r.prices.pelajar; d.totalPrice = price * d.seatCount; d.seatNumbers = this.bookingForm.selectedSeats.join(", "); }
        },
        toggleSeat(id) { if (this.isSeatOccupied(id)) return alert("Kursi sudah terisi!"); const list = this.bookingForm.selectedSeats; const idx = list.indexOf(id); if(idx === -1) list.push(id); else list.splice(idx, 1); list.sort(); this.calculatePrice(); },
        isSeatOccupied(id) {
            const d = this.bookingForm.data; if (!d.routeId || !d.date || !d.time) return false;
            const existing = this.bookings.filter(b => b.routeId === d.routeId && b.date === d.date && b.time === d.time && b.status !== 'Batal');
            if (existing.some(b => b.serviceType === 'Dropping')) return true;
            let occupied = []; existing.forEach(b => { if (b.seatNumbers) occupied.push(...b.seatNumbers.split(',').map(s => s.trim())); });
            return occupied.includes(id);
        },
        isSeatSelected(id) { return this.bookingForm.selectedSeats.includes(id); },
        
        handleKTMUpload(e) { if(e.target.files[0]) this.bookingForm.data.ktmProof = e.target.files[0].name; },
        handlePaymentProofUpload(e) { if(e.target.files[0]) this.bookingForm.data.paymentProof = e.target.files[0].name; },
        
        saveBooking() {
            const d = this.bookingForm.data;
            if(!d.routeId || !d.time || !d.passengerName) return alert("Lengkapi Data!");
            if(d.serviceType === 'Travel' && this.bookingForm.selectedSeats.length === 0) return alert("Pilih kursi!");
            if(d.passengerType === 'Pelajar' && !d.ktmProof) return alert("Wajib upload foto KTM/Pelajar!");
            
            if(d.paymentMethod === 'Cash') {
                if(!d.paymentLocation || !d.paymentReceiver) return alert("Lengkapi data penerima uang!");
                d.paymentStatus = "Lunas"; d.validationStatus = "Valid";
            } else {
                if(!d.paymentProof) return alert("Wajib upload bukti transfer!");
                d.paymentStatus = "Menunggu Validasi"; d.validationStatus = "Menunggu Validasi";
            }

            d.status = "Pending"; this.bookings.push({...d});
            this.viewTicket(d); alert("Booking Tersimpan!"); this.openBookingModal(this.bookingMode);
        },

        validatePaymentModal(booking) { this.validationData = booking; this.isProofModalVisible = true; },
        confirmValidation(booking) { if(confirm("Konfirmasi Validasi Pembayaran?")) { booking.validationStatus = 'Valid'; booking.paymentStatus = 'Lunas'; this.isProofModalVisible = false; } },

        openDispatchModal(group) { this.dispatchForm.group = group; this.dispatchForm.fleetId = ""; this.dispatchForm.driverId = ""; this.isDispatchModalVisible = true; },
        processDispatch() {
            if(!this.dispatchForm.fleetId || !this.dispatchForm.driverId) return alert("Pilih Aset!");
            const f = this.fleet.find(x=>x.id===this.dispatchForm.fleetId); const d = this.drivers.find(x=>x.id===this.dispatchForm.driverId); const group = this.dispatchForm.group;
            this.trips.push({ id: Date.now(), routeConfig: group, fleet: f, driver: d, passengers: [...group.passengers], status: "Berangkat", departureTime: new Date() });
            group.passengers.forEach(p => { const b = this.bookings.find(x=>x.id===p.id); if(b) b.status = "Assigned"; });
            f.status = "On Trip"; d.status = "Jalan"; this.isDispatchModalVisible = false;
        },
        completeTrip(trip) { if(confirm("Trip Selesai?")) { trip.status = "Tiba"; const f = this.fleet.find(x=>x.id===trip.fleet.id); if(f) f.status = "Tersedia"; const d = this.drivers.find(x=>x.id===trip.driver.id); if(d) d.status = "Standby"; } },
        
        openVehicleModal(v) { this.vehicleModal.mode = v?'edit':'add'; this.vehicleModal.data = v?{...v}:{id:Date.now(), name:"", plate:"", capacity:7, status:"Tersedia", icon:"bi-truck-front-fill"}; this.isVehicleModalVisible = true; },
        saveVehicle() { if(this.vehicleModal.mode==='add') this.fleet.push(this.vehicleModal.data); else { const idx = this.fleet.findIndex(x=>x.id===this.vehicleModal.data.id); this.fleet[idx]=this.vehicleModal.data; } this.isVehicleModalVisible=false; },
        openDriverModal(d) { this.driverModal.mode = d?'edit':'add'; this.driverModal.data = d?{...d}:{id:Date.now(), name:"", phone:"", status:"Standby"}; this.isDriverModalVisible = true; },
        saveDriver() { if(this.driverModal.mode==='add') this.drivers.push(this.driverModal.data); else { const idx = this.drivers.findIndex(x=>x.id===this.driverModal.data.id); this.drivers[idx]=this.driverModal.data; } this.isDriverModalVisible=false; },
        closeVehicleModal() { this.isVehicleModalVisible = false; },
        closeDriverModal() { this.isDriverModalVisible = false; },

        saveData() { localStorage.setItem('sutan_v72_bookings', JSON.stringify(this.bookings)); localStorage.setItem('sutan_v72_trips', JSON.stringify(this.trips)); localStorage.setItem('sutan_v72_fleet', JSON.stringify(this.fleet)); localStorage.setItem('sutan_v72_drivers', JSON.stringify(this.drivers)); },
        loadData() {
            if(localStorage.getItem('sutan_v72_bookings')) this.bookings = JSON.parse(localStorage.getItem('sutan_v72_bookings'));
            if(localStorage.getItem('sutan_v72_trips')) this.trips = JSON.parse(localStorage.getItem('sutan_v72_trips'));
            if(localStorage.getItem('sutan_v72_fleet')) this.fleet = JSON.parse(localStorage.getItem('sutan_v72_fleet'));
            if(localStorage.getItem('sutan_v72_drivers')) this.drivers = JSON.parse(localStorage.getItem('sutan_v72_drivers'));
        },
        viewTicket(p) { this.ticketData = p; this.isTicketModalVisible = true; },
        getRouteName(id) { const r = this.routeConfig.find(x=>x.id===id); return r ? `${r.origin} - ${r.destination}` : id; },
        formatRupiah(n) { return new Intl.NumberFormat('id-ID', {style:'currency', currency:'IDR', minimumFractionDigits:0}).format(n); },
        formatDate(d) { return new Date(d).toLocaleDateString('id-ID', {day:'numeric', month:'short'}); },
        toggleFullscreen() { this.isFullscreen = !this.isFullscreen; },
        getVehicleStatusClass(s) { return s==='Tersedia'?'bg-green-100 text-green-700':(s==='On Trip'?'bg-blue-100 text-blue-700':'bg-red-100 text-red-700'); },
        getDriverStatusClass(s) { return s==='Standby'?'bg-green-100 text-green-700':(s==='Jalan'?'bg-blue-100 text-blue-700':'bg-gray-200'); },
        updateTime() { const n = new Date(); this.currentTime = n.toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit'}); this.currentDate = n.toLocaleDateString('id-ID', {weekday:'long', day:'numeric', month:'long'}); }
    },
    mounted() { setInterval(this.updateTime, 1000); this.updateTime(); this.openBookingModal(); }
}).mount("#app");