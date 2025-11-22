const { createApp } = Vue;

createApp({
    data() {
        return {
            view: "dashboard",
            isFullscreen: false,
            currentTime: "",
            currentDate: "",
            reportDate: new Date().toISOString().slice(0,10),
            
            // Modals
            isBookingModalVisible: false,
            isDispatchModalVisible: false,
            isTicketModalVisible: false,
            isProofModalVisible: false,
            isVehicleModalVisible: false,
            isDriverModalVisible: false,
            isTripControlVisible: false,
            
            activeTripControl: null, 
            bookingMode: "Admin",
            ticketData: null,
            validationData: null,
            
            // New Tab State for Booking Management
            bookingManagementTab: 'travel',

            // Forms
            bookingForm: { data: null, selectedSeats: [] },
            bookingBusForm: { type: 'Medium', routeId: "", seatCapacity: 33, duration: 1, date: "", passengerName: "", passengerPhone: "", totalPrice: 0, priceType: 'Kantor', packageType: 'Unit', paymentMethod: 'Cash', paymentLocation: '', paymentReceiver: '', paymentProof: '', downPaymentAmount: 0 },
            dispatchForm: { group: null, fleetId: "", driverId: "" },
            vehicleModal: { mode: "add", data: null },
            driverModal: { mode: "add", data: null },

            // --- TRAVEL ROUTES ---
            routeConfig: [
                { id: "PDG-BKT", origin: "Padang", destination: "Bukittinggi", prices: { umum: 120000, pelajar: 100000, dropping: 900000, carter: 1500000 }, schedules: ["08:00", "10:00", "12:00", "14:00", "16:00", "18:00", "20:00"] },
                { id: "BKT-PDG", origin: "Bukittinggi", destination: "Padang", prices: { umum: 120000, pelajar: 100000, dropping: 900000, carter: 1500000 }, schedules: ["06:00", "08:00", "10:00", "13:00", "15:00", "17:00", "18:00", "19:00"] },
                { id: "PDG-PYK", origin: "Padang", destination: "Payakumbuh", prices: { umum: 150000, pelajar: 130000, dropping: 1100000, carter: 1800000 }, schedules: ["08:00", "10:00", "14:00", "18:00"] },
                { id: "PYK-PDG", origin: "Payakumbuh", destination: "Padang", prices: { umum: 150000, pelajar: 130000, dropping: 1100000, carter: 1800000 }, schedules: ["05:00", "07:00", "10:00", "14:00", "17:00"] }
            ],
            
            // --- BUS ROUTES ---
            busRouteConfig: [
                { id: "PDG-BKT", name: "Padang - Bukittinggi", minDays: 1, prices: { s33: 2500000, s35: 2600000 }, big: { s45: { kantor: 4000000, agen: 3800000 }, s32: { kantor: 4500000, agen: 4300000 } } },
                { id: "PDG-PYK", name: "Padang - Payakumbuh", minDays: 1, prices: { s33: 2600000, s35: 2700000 }, big: { s45: { kantor: 4300000, agen: 4000000 }, s32: { kantor: 4300000, agen: 4000000 } } },
                { id: "PDG-JKT", name: "Padang - Jakarta", minDays: 6, prices: { s33: 0, s35: 0 }, isLongTrip: true, big: { base: 4500000, allin: 5500000 } },
                { id: "PDG-KNO", name: "Padang - Medan", minDays: 6, prices: { s33: 3500000, s35: 3600000 }, isLongTrip: true, big: { base: 4500000, allin: 5500000 } }
            ],

            seatLayout: [
                { row: 1, seats: [{id:"CC", type:"seat"}, {id:"driver", type:"driver"}], label: "Depan" },
                { row: 2, seats: [{id:"1", type:"seat"}, {id:"2", type:"seat"}], label: "Tengah" },
                { row: 3, seats: [{id:"3", type:"seat"}, {id:"4", type:"seat"}], label: "Tengah" },
                { row: 4, seats: [{id:"5", type:"seat"}, {id:"6", type:"seat"}, {id:"7", type:"seat"}], label: "Belakang" }
            ],

            // --- UNIFIED DATABASE ---
            bookings: [], // Stores ALL bookings (Travel, Carter, Dropping, Bus)
            fleet: [
                { id: 1, name: "Hiace Premio 01", plate: "BA 1001 HP", capacity: 7, status: "Tersedia", icon: "bi-truck-front-fill" },
                { id: 2, name: "Hiace Commuter 02", plate: "BA 1002 HP", capacity: 7, status: "Tersedia", icon: "bi-truck-front-fill" }
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
        currentViewTitle() { const t={dashboard:"Dashboard Eksekutif", bookingManagement:"Kelola Booking", dispatcher:"Flight Deck Dispatcher", bookingTravel:"Reservasi Travel", bookingBus:"Reservasi Bus Pariwisata", manifest:"Laporan Keuangan", assets:"Manajemen Aset"}; return t[this.view] || "Sutan Raya System"; },
        todayRevenue() { return this.bookings.filter(b => b.date === new Date().toISOString().slice(0,10)).reduce((sum, b) => sum + b.totalPrice, 0); },
        todayPax() { return this.bookings.filter(b => b.date === new Date().toISOString().slice(0,10)).length; },
        pendingGroupsCount() { return this.groupedBookings.length; },
        
        activeTrips() { return this.trips.filter(t => !['Tiba', 'Batal'].includes(t.status)); },
        
        pendingValidationCount() { return this.bookings.filter(b => b.validationStatus === 'Menunggu Validasi').length; },
        
        outboundTrips() { return this.activeTrips.filter(t => t.routeConfig.origin === 'Padang' || (t.routeConfig.name && t.routeConfig.name.startsWith('Padang'))); },
        inboundTrips() { return this.activeTrips.filter(t => t.routeConfig.destination === 'Padang' || (t.routeConfig.name && !t.routeConfig.name.startsWith('Padang'))); },

        // Helper for "Kelola Booking" View
        getManagedBookings() {
            if (this.bookingManagementTab === 'travel') {
                return this.bookings.filter(b => ['Travel', 'Carter', 'Dropping'].includes(b.serviceType));
            } else {
                return this.bookings.filter(b => b.serviceType === 'Bus Pariwisata');
            }
        },

        groupedBookings() {
            const groups = {};
            // Only group 'Travel', 'Carter', 'Dropping'. Bus usually handled manually or could be added here.
            // Only showing bookings that are VALID (Paid/DP) and PENDING dispatch.
            const pending = this.bookings.filter(b => b.status === 'Pending' && (b.paymentStatus === 'Lunas' || b.paymentStatus === 'DP') && b.validationStatus === 'Valid');
            
            pending.forEach(b => {
                const key = `${b.routeId}|${b.date}|${b.time || 'Bus'}`;
                if (!groups[key]) {
                    const r = this.routeConfig.find(x => x.id === b.routeId) || this.busRouteConfig.find(x => x.id === b.routeId);
                    groups[key] = { key, routeId: b.routeId, routeOrigin: r?.origin || 'Padang', routeDest: r?.destination || 'Luar Kota', routeConfig: r, date: b.date, time: b.time || 'Flexible', totalPassengers: 0, passengers: [], serviceType: b.serviceType };
                }
                groups[key].passengers.push(b);
                const count = (b.serviceType === 'Dropping' || b.serviceType === 'Carter' || b.serviceType === 'Bus Pariwisata') ? 1 : (b.seatCount || 1);
                groups[key].totalPassengers += count;
            });
            return Object.values(groups).sort((a, b) => a.time.localeCompare(b.time));
        },
        dailyReportData() {
            const date = this.reportDate;
            const report = {};
            // Travel Report
            this.routeConfig.forEach(route => {
                report[route.id] = { route: route, schedules: [], total: { umumNom: 0, pelajarNom: 0, totalNom: 0 } };
                route.schedules.forEach(time => {
                    const bList = this.bookings.filter(b => b.routeId === route.id && b.date === date && b.time === time && b.status !== 'Batal');
                    let row = { time: time, umumPax: 0, umumNom: 0, pelajarPax: 0, pelajarNom: 0, totalNom: 0 };
                    bList.forEach(b => {
                        if(b.passengerType === 'Umum' || b.serviceType === 'Dropping' || b.serviceType === 'Carter') { row.umumPax += b.seatCount; row.umumNom += b.totalPrice; }
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
        selectedRoute() { return this.routeConfig.find(r => r.id === this.bookingForm.data?.routeId); },
        getBusRouteName() { const r = this.busRouteConfig.find(x=>x.id===this.bookingBusForm.routeId); return r ? r.name : '-'; },
        getBusDailyPrice() {
            const r = this.busRouteConfig.find(x=>x.id===this.bookingBusForm.routeId);
            if(!r) return 0;
            if(this.bookingBusForm.type === 'Medium') return this.bookingBusForm.seatCapacity == 33 ? r.prices.s33 : r.prices.s35;
            if(r.isLongTrip) return this.bookingBusForm.packageType === 'AllIn' ? r.big.allin : r.big.base;
            return this.bookingBusForm.seatCapacity == 45 ? (this.bookingBusForm.priceType==='Kantor'?r.big.s45.kantor:r.big.s45.agen) : (this.bookingBusForm.priceType==='Kantor'?r.big.s32.kantor:r.big.s32.agen);
        },
        getBusMinDays() { const r = this.busRouteConfig.find(x=>x.id===this.bookingBusForm.routeId); return r ? r.minDays : 1; },
        isLongTrip() { const r = this.busRouteConfig.find(x=>x.id===this.bookingBusForm.routeId); return r ? r.isLongTrip : false; }
    },
    methods: {
        goToDispatcher(booking) { this.view = 'dispatcher'; },
        
        openBookingModal(mode = 'Admin') {
            this.bookingMode = mode;
            this.bookingForm.data = {
                id: Date.now(), serviceType: "Travel",
                routeId: "", date: new Date().toISOString().slice(0,10), time: "",
                passengerName: "", passengerPhone: "", passengerType: "Umum",
                seatCount: 1, seatNumbers: "", pickupAddress: "", dropoffAddress: "",
                paymentMethod: "Cash", paymentStatus: "Menunggu Validasi", validationStatus: "Menunggu Validasi", totalPrice: 0, 
                ktmProof: null, paymentProof: null, paymentLocation: "", paymentReceiver: "", isMultiStop: false, duration: 1, downPaymentAmount: 0
            };
            this.bookingForm.selectedSeats = [];
            this.view = 'bookingTravel';
        },
        setServiceType(type) { this.bookingForm.data.serviceType = type; if(type==='Dropping' || type==='Carter') { this.bookingForm.data.time = ""; this.bookingForm.selectedSeats = []; } this.calculatePrice(); },
        calculatePrice() {
            const d = this.bookingForm.data; const r = this.selectedRoute; if(!r) return;
            if(d.serviceType === 'Dropping') {
                let price = 1000000; if (r.id.includes('BKT')) price = d.isMultiStop ? 960000 : 900000; else if (r.id.includes('PYK')) price = d.isMultiStop ? 1200000 : 1100000;
                d.totalPrice = price; d.seatCount = 1; d.seatNumbers = "Full Unit";
            } else if (d.serviceType === 'Carter') {
                d.totalPrice = (r.prices.carter || 1500000) * (d.duration || 1); d.seatCount = 1; d.seatNumbers = "Full Unit";
            } else { 
                d.seatCount = this.bookingForm.selectedSeats.length || 1; const price = d.passengerType === 'Umum' ? r.prices.umum : r.prices.pelajar; d.totalPrice = price * d.seatCount; d.seatNumbers = this.bookingForm.selectedSeats.join(", "); 
            }
        },
        toggleSeat(id) { if (this.isSeatOccupied(id)) return alert("Kursi sudah terisi!"); const list = this.bookingForm.selectedSeats; const idx = list.indexOf(id); if(idx === -1) list.push(id); else list.splice(idx, 1); list.sort(); this.calculatePrice(); },
        isSeatOccupied(id) {
            const d = this.bookingForm.data; if (!d.routeId || !d.date || !d.time) return false;
            const existing = this.bookings.filter(b => b.routeId === d.routeId && b.date === d.date && b.time === d.time && b.status !== 'Batal');
            if (existing.some(b => b.serviceType === 'Dropping' || b.serviceType === 'Carter')) return true;
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
            if(d.paymentMethod === 'Cash') { if(!d.paymentLocation || !d.paymentReceiver) return alert("Lengkapi data penerima uang!"); d.paymentStatus = "Lunas"; d.validationStatus = "Valid"; } 
            else if (d.paymentMethod === 'DP') { if(d.downPaymentAmount < 100000) return alert("Minimal DP Rp 100.000!"); if(!d.paymentProof) return alert("Wajib upload bukti DP!"); d.paymentStatus = "DP"; d.validationStatus = "Menunggu Validasi"; }
            else { if(!d.paymentProof) return alert("Wajib upload bukti transfer!"); d.paymentStatus = "Menunggu Validasi"; d.validationStatus = "Menunggu Validasi"; }
            d.status = "Pending"; this.bookings.push({...d});
            // SMART SHORTCUT
            if(confirm("Booking Tersimpan! Lanjut ke Dispatcher sekarang?")) { this.view = 'dispatcher'; } else { this.openBookingModal(this.bookingMode); }
        },

        calculateBusPrice() {
            const r = this.busRouteConfig.find(x => x.id === this.bookingBusForm.routeId); if(!r) return;
            const dailyPrice = this.getBusDailyPrice;
            this.bookingBusForm.totalPrice = dailyPrice * (this.bookingBusForm.duration || 1);
        },
        saveBusBooking() {
            const r = this.busRouteConfig.find(x => x.id === this.bookingBusForm.routeId);
            if(this.bookingBusForm.duration < r.minDays) return alert(`Minimal sewa untuk rute ini adalah ${r.minDays} hari.`);
            if(this.bookingBusForm.type === 'Big' && this.bookingBusForm.totalPrice >= 1000000) { if(!confirm("Perhatian: Big Bus Wajib DP Minimal Rp 1.000.000. Lanjutkan?")) return; }
            
            // UNIFIED: Push to main bookings array
            const newBusBooking = { 
                ...this.bookingBusForm, 
                id: Date.now(), 
                status: 'Pending',
                serviceType: 'Bus Pariwisata',
                routeName: r.name,
                validationStatus: this.bookingBusForm.paymentMethod === 'Cash' ? 'Valid' : 'Menunggu Validasi',
                paymentStatus: this.bookingBusForm.paymentMethod === 'Cash' ? 'Lunas' : (this.bookingBusForm.paymentMethod === 'DP' ? 'DP' : 'Menunggu Validasi')
            };
            
            this.bookings.push(newBusBooking); // Use main array
            if(confirm("Booking Bus Berhasil! Lanjut Kelola Booking?")) { this.view = 'bookingManagement'; } else {
                this.bookingBusForm = { type: 'Medium', routeId: "", seatCapacity: 33, duration: 1, date: "", passengerName: "", passengerPhone: "", totalPrice: 0, priceType:'Kantor', packageType:'Unit', paymentMethod: 'Cash', paymentLocation: '', paymentReceiver: '', paymentProof: '', downPaymentAmount: 0 };
            }
        },

        validatePaymentModal(booking) { this.validationData = booking; this.isProofModalVisible = true; },
        confirmValidation(booking) { 
            if(confirm("Konfirmasi Validasi Pembayaran?")) { 
                booking.validationStatus = 'Valid'; 
                // Update payment status if it was just 'Menunggu Validasi' to 'Lunas'. If DP, keep as DP.
                if (booking.paymentStatus !== 'DP') booking.paymentStatus = 'Lunas';
                this.isProofModalVisible = false; 
            } 
        },

        openDispatchModal(group) { this.dispatchForm.group = group; this.dispatchForm.fleetId = ""; this.dispatchForm.driverId = ""; this.isDispatchModalVisible = true; },
        processDispatch() {
            if(!this.dispatchForm.fleetId || !this.dispatchForm.driverId) return alert("Pilih Aset!");
            
            const unpaid = this.dispatchForm.group.passengers.filter(p => p.paymentStatus === 'DP' || p.paymentStatus === 'Menunggu Validasi');
            if(unpaid.length > 0) return alert(`GAGAL: Ada ${unpaid.length} penumpang belum lunas/valid. Harap validasi pembayaran terlebih dahulu!`);

            const f = this.fleet.find(x=>x.id===this.dispatchForm.fleetId); const d = this.drivers.find(x=>x.id===this.dispatchForm.driverId); const group = this.dispatchForm.group;
            this.trips.push({ id: Date.now(), routeConfig: group, fleet: f, driver: d, passengers: [...group.passengers], status: "On Trip", departureTime: new Date() });
            group.passengers.forEach(p => { const b = this.bookings.find(x=>x.id===p.id); if(b) b.status = "Assigned"; });
            f.status = "On Trip"; d.status = "Jalan"; this.isDispatchModalVisible = false;
            this.view = 'dashboard'; // Redirect to Dashboard to track
        },
        
        openTripControl(trip) { this.activeTripControl = trip; this.isTripControlVisible = true; },
        updateTripStatus(trip, newStatus) {
            trip.status = newStatus;
            const f = this.fleet.find(x=>x.id===trip.fleet.id);
            if(newStatus === 'Tiba') {
                if(confirm("Selesaikan Trip? Armada akan kembali tersedia.")) { 
                    if(f) f.status = "Tersedia"; const d = this.drivers.find(x=>x.id===trip.driver.id); if(d) d.status = "Standby";
                    // Update bookings to 'Tiba'
                    trip.passengers.forEach(p => { const b = this.bookings.find(x=>x.id===p.id); if(b) b.status = "Tiba"; });
                } else { trip.status = 'On Trip'; }
            } else if (newStatus === 'Kendala') { if(f) f.status = "Perbaikan"; }
            this.isTripControlVisible = false;
        },
        completeTrip(trip) { this.updateTripStatus(trip, 'Tiba'); },
        
        openVehicleModal(v) { this.vehicleModal.mode = v?'edit':'add'; this.vehicleModal.data = v?{...v}:{id:Date.now(), name:"", plate:"", capacity:7, status:"Tersedia", icon:"bi-truck-front-fill"}; this.isVehicleModalVisible = true; },
        saveVehicle() { if(this.vehicleModal.mode==='add') this.fleet.push(this.vehicleModal.data); else { const idx = this.fleet.findIndex(x=>x.id===this.vehicleModal.data.id); this.fleet[idx]=this.vehicleModal.data; } this.isVehicleModalVisible=false; },
        openDriverModal(d) { this.driverModal.mode = d?'edit':'add'; this.driverModal.data = d?{...d}:{id:Date.now(), name:"", phone:"", status:"Standby"}; this.isDriverModalVisible = true; },
        saveDriver() { if(this.driverModal.mode==='add') this.drivers.push(this.driverModal.data); else { const idx = this.drivers.findIndex(x=>x.id===this.driverModal.data.id); this.drivers[idx]=this.driverModal.data; } this.isDriverModalVisible=false; },
        closeVehicleModal() { this.isVehicleModalVisible = false; },
        closeDriverModal() { this.isDriverModalVisible = false; },

        saveData() { localStorage.setItem('sutan_v81_bookings', JSON.stringify(this.bookings)); localStorage.setItem('sutan_v81_trips', JSON.stringify(this.trips)); localStorage.setItem('sutan_v81_fleet', JSON.stringify(this.fleet)); localStorage.setItem('sutan_v81_drivers', JSON.stringify(this.drivers)); },
        loadData() {
            if(localStorage.getItem('sutan_v81_bookings')) this.bookings = JSON.parse(localStorage.getItem('sutan_v81_bookings'));
            if(localStorage.getItem('sutan_v81_trips')) this.trips = JSON.parse(localStorage.getItem('sutan_v81_trips'));
            if(localStorage.getItem('sutan_v81_fleet')) this.fleet = JSON.parse(localStorage.getItem('sutan_v81_fleet'));
            if(localStorage.getItem('sutan_v81_drivers')) this.drivers = JSON.parse(localStorage.getItem('sutan_v81_drivers'));
        },
        viewTicket(p) { this.ticketData = p; this.isTicketModalVisible = true; },
        getRouteName(id) { const r = this.routeConfig.find(x=>x.id===id); return r ? `${r.origin} - ${r.destination}` : id; },
        formatRupiah(n) { return new Intl.NumberFormat('id-ID', {style:'currency', currency:'IDR', minimumFractionDigits:0}).format(n); },
        formatDate(d) { return new Date(d).toLocaleDateString('id-ID', {day:'numeric', month:'short'}); },
        toggleFullscreen() { this.isFullscreen = !this.isFullscreen; },
        getVehicleStatusClass(s) { return s==='Tersedia'?'bg-green-100 text-green-700':(s==='On Trip'?'bg-blue-100 text-blue-700':(s==='Perbaikan'?'bg-red-100 text-red-700':'bg-gray-100')); },
        getDriverStatusClass(s) { return s==='Standby'?'bg-green-100 text-green-700':(s==='Jalan'?'bg-blue-100 text-blue-700':'bg-gray-200'); },
        getTripCardClass(s) { if(s==='On Trip') return 'border-blue-100 bg-blue-50/20'; if(s==='Tiba') return 'border-green-100 bg-green-50/20'; if(s==='Kendala') return 'border-red-100 bg-red-50/20'; if(s==='Pending') return 'border-orange-100 bg-orange-50/20'; return 'border-gray-100'; },
        getTripStatusBadge(s) { if(s==='On Trip') return 'bg-blue-500'; if(s==='Tiba') return 'bg-green-500'; if(s==='Kendala') return 'bg-red-500'; if(s==='Pending') return 'bg-orange-500'; return 'bg-gray-400'; },
        updateTime() { const n = new Date(); this.currentTime = n.toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit'}); this.currentDate = n.toLocaleDateString('id-ID', {weekday:'long', day:'numeric', month:'long'}); }
    },
    mounted() { setInterval(this.updateTime, 1000); this.updateTime(); this.openBookingModal(); }
}).mount("#app");