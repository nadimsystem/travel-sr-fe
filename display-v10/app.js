// FILE: app.js
// Display v10 - Frontend Logic
const { createApp } = Vue;

createApp({
    data() {
        return {
            // -- State UI --
            view: 'dashboard',
            isDarkMode: false,
            isLoading: false,
            currentTime: "",
            currentDate: "",
            
            // -- State Modals --
            isProofModalVisible: false,
            isDispatchModalVisible: false,
            isVehicleModalVisible: false,
            isDriverModalVisible: false,
            isTripControlVisible: false,
            isTicketModalVisible: false,
            isRouteModalVisible: false,
            
            // -- Data Models --
            activeTripControl: null,
            validationData: null,
            ticketData: null,
            
            // -- Forms --
            bookingManagementTab: 'travel',
            busSearchTerm: '',
            bookingForm: { 
                data: { id: null, serviceType: 'Travel', routeId: '', date: '', time: '', passengerName: '', passengerPhone: '', passengerType: 'Umum', seatCount: 1, duration: 1, isMultiStop: false },
                selectedSeats: []
            },
            bookingBusForm: { type: 'Medium', routeId: '', seatCapacity: 33, duration: 1, date: '', passengerName: '', passengerPhone: '', totalPrice: 0, priceType: 'Kantor', packageType: 'Unit', paymentMethod: 'Cash', paymentLocation: '', paymentReceiver: '', paymentProof: '', downPaymentAmount: 0 },
            
            currentPaymentMethod: 'Cash',
            tempPayment: { loc: '', recv: '', proof: '', dpAmount: 0, dpMethod: 'Cash' },
            dispatchForm: { group: null, fleetId: "", driverId: "" },
            vehicleModal: { mode: "add", data: null },
            driverModal: { mode: "add", data: null },
            routeModal: { mode: 'add', data: { id: '', origin: '', destination: '', schedulesInput: '', prices: { umum: 0, pelajar: 0, dropping: 0, carter: 0 } } },

            // -- Data from Server --
            bookings: [],
            fleet: [],
            drivers: [],
            trips: [],
            routeConfig: [],     // Akan di-load dari API
            busRouteConfig: [],  // Akan di-load dari API
            
            // -- Static Config --
            seatLayout: [
                { row: 1, seats: [{id:"1", type:"seat"}, {id:"driver", type:"driver"}], label: "Depan" },
                { row: 2, seats: [{id:"2", type:"seat"}, {id:"3", type:"seat"}], label: "Tengah 1" },
                { row: 3, seats: [{id:"4", type:"seat"}, {id:"5", type:"seat"}], label: "Tengah 2" },
                { row: 4, seats: [{id:"6", type:"seat"}, {id:"7", type:"seat"}, {id:"8", type:"seat"}], label: "Belakang" }
            ],
            calendarMonth: new Date().getMonth(),
            calendarYear: new Date().getFullYear(),
        };
    },
    created() {
        this.loadData();
        // Auto-refresh setiap 30 detik agar data selalu update tanpa reload
        setInterval(() => { this.loadData(true); }, 30000);
        
        this.updateTime(); 
        setInterval(this.updateTime, 1000);
        
        // Load view preference
        const savedView = localStorage.getItem('sutan_v10_view');
        if(savedView) this.view = savedView;
        else this.openBookingModal();
    },
    watch: {
        view(val) { localStorage.setItem('sutan_v10_view', val); },
    },
    computed: {
        currentViewTitle() { return {dashboard:"Dashboard",bookingManagement:"Kelola Booking",dispatcher:"Dispatcher",bookingTravel:"Travel",bookingBus:"Bus",manifest:"Laporan",assets:"Aset",routeManagement:"Rute"}[this.view] || "Sutan Raya"; },
        todayRevenue() { return this.bookings.filter(b => b.date === new Date().toISOString().slice(0,10) && b.status !== 'Batal').reduce((a,b) => a + (b.totalPrice||0), 0); },
        todayPax() { return this.bookings.filter(b => b.date === new Date().toISOString().slice(0,10) && b.status !== 'Batal').length; },
        pendingValidationCount() { return this.bookings.filter(b => b.validationStatus === 'Menunggu Validasi').length; },
        activeTrips() { return this.trips.filter(t => !['Tiba', 'Batal'].includes(t.status)); },
        
        groupedBookings() {
            // Logic Dispatcher: Mengelompokkan booking Travel/Carter yang siap jalan
            const groups = {};
            // Filter: Status Pending, Validasi Valid, dan Payment Lunas/DP
            const ready = this.bookings.filter(b => 
                b.status === 'Pending' && 
                b.validationStatus === 'Valid' && 
                ['Lunas', 'DP'].includes(b.paymentStatus)
            );
            
            ready.forEach(b => {
                const key = `${b.routeId}|${b.date}|${b.time||'Bus'}`;
                if (!groups[key]) {
                    const r = this.routeConfig.find(x=>x.id===b.routeId) || this.busRouteConfig.find(x=>x.id===b.routeId);
                    groups[key] = { 
                        key, routeId: b.routeId, 
                        routeConfig: r, 
                        date: b.date, 
                        time: b.time||'Flexible', 
                        totalPassengers: 0, 
                        passengers: [], 
                        serviceType: b.serviceType 
                    };
                }
                groups[key].passengers.push(b);
                groups[key].totalPassengers += (b.serviceType.includes('Bus') || b.serviceType==='Carter') ? 1 : (b.seatCount||1);
            });
            return Object.values(groups).sort((a, b) => (a.time || '').localeCompare(b.time || ''));
        },
        
        getManagedBookings() {
            let items = this.view==='bookingManagement' && this.bookingManagementTab==='bus' 
                ? this.bookings.filter(b=>b.serviceType==='Bus Pariwisata') 
                : this.bookings.filter(b=>['Travel','Carter','Dropping'].includes(b.serviceType));
            
            if(this.busSearchTerm) {
                const term = this.busSearchTerm.toLowerCase();
                items = items.filter(b => (b.passengerName?.toLowerCase().includes(term)) || (b.passengerPhone?.includes(term)));
            }
            return items.sort((a,b) => new Date(b.id) - new Date(a.id));
        },
        
        selectedRoute() { return this.routeConfig.find(r => r.id === this.bookingForm.data.routeId); },
        currentSchedules() { return this.selectedRoute ? (this.selectedRoute.schedules || []) : []; },
        
        currentTotalPrice() {
            if(this.view==='bookingBus') return this.bookingBusForm.totalPrice;
            const d = this.bookingForm.data; 
            const r = this.selectedRoute; 
            if(!r) return 0;
            
            if(d.serviceType === 'Dropping') {
                let p = r.prices.dropping || 1000000;
                // Logika harga khusus dropping jika ada
                return p;
            } else if(d.serviceType === 'Carter') {
                return (r.prices.carter||1500000) * (d.duration||1);
            }
            return (this.bookingForm.selectedSeats?.length||1) * (d.passengerType==='Umum' ? r.prices.umum : r.prices.pelajar);
        },
        
        // Helpers Bus
        getBusRouteName() { const r = this.busRouteConfig.find(x=>x.id===this.bookingBusForm.routeId); return r ? r.name : '-'; },
        getBusDailyPrice() {
            const r = this.busRouteConfig.find(x=>x.id===this.bookingBusForm.routeId); if(!r) return 0;
            if(this.bookingBusForm.type==='Medium') return this.bookingBusForm.seatCapacity==33 ? r.prices.s33 : r.prices.s35;
            const isLong = r.isLongTrip || false;
            if(isLong) return this.bookingBusForm.packageType==='AllIn' ? r.big.allin : r.big.base;
            return this.bookingBusForm.seatCapacity==45 ? (this.bookingBusForm.priceType==='Kantor'?r.big.s45.kantor:r.big.s45.agen) : (this.bookingBusForm.priceType==='Kantor'?r.big.s32.kantor:r.big.s32.agen);
        },

        // Route Form Alias
        routeForm: { get() { return this.routeModal.data; }, set(v) { this.routeModal.data = v; } },
        routeModalMode() { return this.routeModal.mode; },
    },
    methods: {
        changeView(v) { this.view = v; },
        toggleDarkMode() { this.isDarkMode = !this.isDarkMode; if(this.isDarkMode) document.documentElement.classList.add('dark'); else document.documentElement.classList.remove('dark'); },
        
        // --- API COMMUNICATION ---
        async loadData(silent = false) {
            if(!silent) this.isLoading = true;
            try {
                const res = await fetch('api.php');
                const data = await res.json();
                
                this.bookings = data.bookings || [];
                this.fleet = data.fleet || [];
                this.drivers = data.drivers || [];
                this.trips = data.trips || [];
                
                if(data.routes && data.routes.length > 0) this.routeConfig = data.routes;
                if(data.busRoutes && data.busRoutes.length > 0) this.busRouteConfig = data.busRoutes;
                
            } catch (e) {
                console.error("Load Failed:", e);
                if(!silent) alert("Gagal mengambil data dari server.");
            } finally {
                if(!silent) this.isLoading = false;
            }
        },

        async postToApi(action, data) {
            try {
                const res = await fetch('api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action, ...data })
                });
                return await res.json();
            } catch (e) {
                console.error("API Error", e);
                return { status: 'error', message: e.message };
            }
        },

        // --- BOOKING LOGIC ---
        openBookingModal() {
            const today = new Date().toISOString().slice(0,10);
            Object.assign(this.bookingForm.data, { id: null, serviceType: 'Travel', routeId: '', date: today, time: '', passengerName: '', passengerPhone: '', passengerType: 'Umum', seatCount: 1, duration: 1, isMultiStop: false });
            this.bookingForm.selectedSeats = [];
            this.view = 'bookingTravel';
        },
        
        setServiceType(t) { this.bookingForm.data.serviceType = t; if(t!=='Travel') { this.bookingForm.data.time=''; this.bookingForm.selectedSeats=[]; } },
        toggleSeat(id) { 
            if(this.isSeatOccupied(id)) return alert('Kursi Terisi'); 
            const s=this.bookingForm.selectedSeats; const i=s.indexOf(id); if(i===-1)s.push(id);else s.splice(i,1); 
        },
        isSeatOccupied(id) { 
            const d=this.bookingForm.data; if(!d.routeId||!d.date||!d.time) return false;
            const ex=this.bookings.filter(b=>b.routeId===d.routeId && b.date===d.date && b.time===d.time && b.status!=='Batal');
            if(ex.some(b=>b.serviceType!=='Travel')) return true;
            let occ=[]; ex.forEach(b=>{ if(b.seatNumbers) occ.push(...b.seatNumbers.split(', ')); });
            return occ.includes(id);
        },
        isSeatSelected(id) { return this.bookingForm.selectedSeats.includes(id); },

        async saveBooking() {
            const d = this.bookingForm.data;
            if(!d.passengerName || !d.routeId || !d.date) return alert("Data Belum Lengkap!");
            if(d.serviceType === 'Travel' && (!d.time || this.bookingForm.selectedSeats.length === 0)) return alert("Pilih Jadwal & Kursi!");
            
            // Payment Logic
            const pm = this.currentPaymentMethod;
            let pStat = 'Menunggu Validasi', vStat = 'Menunggu Validasi';
            if(pm === 'Cash') { 
                if(!this.tempPayment.loc) return alert("Isi Lokasi Penjemputan Cash!"); 
                pStat = 'Lunas'; vStat = 'Valid'; 
            } else if (pm === 'DP') {
                 if(this.tempPayment.dpAmount < 50000) return alert("Minimal DP Rp 50.000");
                 pStat = 'DP';
            }

            const newBooking = { 
                id: Date.now(), 
                ...d, 
                totalPrice: this.currentTotalPrice, 
                seatCount: d.serviceType==='Travel'?this.bookingForm.selectedSeats.length:1, 
                seatNumbers: d.serviceType==='Travel'?this.bookingForm.selectedSeats.join(', ') : 'Full Unit', 
                paymentMethod: pm, paymentStatus: pStat, validationStatus: vStat, 
                paymentLocation: this.tempPayment.loc, paymentReceiver: this.tempPayment.recv, paymentProof: this.tempPayment.proof, 
                downPaymentAmount: this.tempPayment.dpAmount,
                type: 'Unit' // Default
            };

            this.isLoading = true;
            const res = await this.postToApi('create_booking', { data: newBooking });
            this.isLoading = false;

            if(res.status === 'success') {
                alert("Booking Berhasil Disimpan!");
                this.bookings.unshift(newBooking); // Update UI Instan
                this.tempPayment = { loc: '', recv: '', proof: '', dpAmount: 0, dpMethod: 'Cash' };
                if(confirm("Lanjut ke menu Kelola Booking?")) this.view = 'bookingManagement';
            } else {
                alert("Gagal Simpan: " + res.message);
            }
        },

        async saveBusBooking() {
             const r = this.busRouteConfig.find(x => x.id === this.bookingBusForm.routeId);
             if(!r) return alert("Pilih Rute");
             
             const pm = this.bookingBusForm.paymentMethod;
             let pStat = pm==='Cash'?'Lunas':'Menunggu Validasi';
             let vStat = pm==='Cash'?'Valid':'Menunggu Validasi';
             if(pm==='DP') pStat = 'DP';

             const newBus = { 
                 ...this.bookingBusForm, 
                 id: Date.now(), 
                 serviceType: 'Bus Pariwisata', 
                 routeName: r.name, 
                 paymentStatus: pStat, 
                 validationStatus: vStat,
                 selectedSeats: [] // Bus pariwisata usually doesn't select seats individually here
             };

             this.isLoading = true;
             const res = await this.postToApi('create_booking', { data: newBus });
             this.isLoading = false;

             if(res.status === 'success') {
                 alert("Booking Bus Tersimpan!");
                 this.bookings.unshift(newBus);
                 this.view = 'bookingManagement';
             } else {
                 alert("Gagal: " + res.message);
             }
        },

        // --- MANAJEMEN BOOKING ---
        validatePaymentModal(b) { this.validationData = b; this.isProofModalVisible = true; },
        async confirmValidation(b) {
            if(!confirm("Validasi Pembayaran ini?")) return;
            
            const res = await this.postToApi('update_payment_status', { 
                id: b.id, 
                paymentStatus: 'Lunas', // Atau logic lain jika DP
                validationStatus: 'Valid' 
            });
            
            if(res.status === 'success') {
                b.paymentStatus = 'Lunas';
                b.validationStatus = 'Valid';
                this.isProofModalVisible = false;
                alert("Validasi Berhasil!");
            }
        },
        async deleteBooking(b) {
            if(!confirm("Hapus booking ini permanen?")) return;
            const res = await this.postToApi('delete_booking', { id: b.id });
            if(res.status === 'success') {
                this.bookings = this.bookings.filter(x => x.id !== b.id);
            }
        },

        // --- DISPATCHER ---
        openDispatchModal(g) { this.dispatchForm.group = g; this.dispatchForm.fleetId=""; this.dispatchForm.driverId=""; this.isDispatchModalVisible=true; },
        async processDispatch() {
            const { group, fleetId, driverId } = this.dispatchForm;
            if(!fleetId || !driverId) return alert("Pilih Armada & Driver!");
            
            const f = this.fleet.find(x=>x.id===fleetId);
            const d = this.drivers.find(x=>x.id===driverId);
            
            const newTrip = {
                id: Date.now(),
                routeConfig: group.routeConfig, // Simpan config rute snapshot
                fleet: f,
                driver: d,
                passengers: group.passengers,
                status: 'On Trip'
            };

            this.isLoading = true;
            const res = await this.postToApi('create_trip', { data: newTrip });
            this.isLoading = false;

            if(res.status === 'success') {
                alert("Trip Berhasil Diberangkatkan!");
                this.isDispatchModalVisible = false;
                this.loadData(); // Reload full data untuk update status booking & armada
            } else {
                alert("Gagal Dispatch: " + res.message);
            }
        },

        openTripControl(t) { this.activeTripControl = t; this.isTripControlVisible = true; },
        async updateTripStatus(t, s) {
            if(s === 'Tiba' && !confirm("Trip Selesai? Armada akan tersedia kembali.")) return;
            
            const res = await this.postToApi('update_trip_status', {
                tripId: t.id,
                status: s,
                fleetId: t.fleet.id,
                driverId: t.driver.id,
                passengers: t.passengers
            });

            if(res.status === 'success') {
                this.isTripControlVisible = false;
                this.loadData();
            } else {
                alert("Update Gagal");
            }
        },

        // --- ASSETS (FLEET & DRIVER) ---
        openVehicleModal(v) { this.vehicleModal = { mode: v?'edit':'add', data: v?{...v}:{id:Date.now(), name:"", plate:"", capacity:7, status:"Tersedia", icon:"bi-truck-front-fill"} }; this.isVehicleModalVisible=true; },
        async saveVehicle() {
            const v = this.vehicleModal.data;
            if(!v.name || !v.plate) return alert("Lengkapi Data!");
            const res = await this.postToApi('save_fleet', v);
            if(res.status === 'success') { alert("Armada Disimpan"); this.isVehicleModalVisible=false; this.loadData(); }
            else alert("Gagal: "+res.message);
        },
        async deleteVehicle(id) {
            if(!confirm("Hapus Armada?")) return;
            const res = await this.postToApi('delete_fleet', {id});
            if(res.status === 'success') this.loadData();
        },

        openDriverModal(d) { this.driverModal = { mode: d?'edit':'add', data: d?{...d}:{id:Date.now(), name:"", phone:"", status:"Standby", licenseType: "A Umum"} }; this.isDriverModalVisible=true; },
        async saveDriver() {
            const d = this.driverModal.data;
            if(!d.name || !d.phone) return alert("Lengkapi Data!");
            const res = await this.postToApi('save_driver', d);
            if(res.status === 'success') { alert("Driver Disimpan"); this.isDriverModalVisible=false; this.loadData(); }
            else alert("Gagal: "+res.message);
        },
        async deleteDriver(id) {
            if(!confirm("Hapus Driver?")) return;
            const res = await this.postToApi('delete_driver', {id});
            if(res.status === 'success') this.loadData();
        },

        // --- ROUTE MANAGEMENT ---
        openRouteModal(r = null) {
            this.routeModal.mode = r ? 'edit' : 'add';
            if (r) {
                this.routeModal.data = JSON.parse(JSON.stringify(r));
                this.routeModal.data.schedulesInput = (r.schedules || []).join(', ');
            } else {
                this.routeModal.data = { id: '', origin: '', destination: '', schedulesInput: '', prices: { umum: 0, pelajar: 0, dropping: 0, carter: 0 } };
            }
            this.isRouteModalVisible = true; // Need to add this to data or use modals object
        },
        async saveRoute() {
            const f = this.routeModal.data;
            if (!f.origin || !f.destination) return alert("Asal dan Tujuan wajib diisi!");
            
            // Generate ID if new
            if (this.routeModal.mode === 'add') {
                const getCode = (name) => {
                    if(name.toLowerCase().includes('padang')) return 'PDG';
                    if(name.toLowerCase().includes('bukittinggi')) return 'BKT';
                    if(name.toLowerCase().includes('payakumbuh')) return 'PYK';
                    if(name.toLowerCase().includes('pekanbaru')) return 'PKU';
                    return name.substring(0,3).toUpperCase();
                };
                f.id = `${getCode(f.origin)}-${getCode(f.destination)}`;
            }

            const schedules = f.schedulesInput.split(',').map(s => s.trim()).filter(s => s);
            
            const payload = {
                id: f.id,
                origin: f.origin,
                destination: f.destination,
                schedules: schedules,
                prices: f.prices
            };

            const res = await this.postToApi('save_route', payload);
            if(res.status === 'success') {
                alert("Rute berhasil disimpan!");
                this.isRouteModalVisible = false; // Assuming this property exists
                this.loadData(); 
            } else {
                alert("Gagal menyimpan rute: " + res.message);
            }
        },
        async deleteRoute(id) {
            if(!confirm("Yakin ingin menghapus rute ini?")) return;
            const res = await this.postToApi('delete_route', { id: id });
            if(res.status === 'success') {
                alert("Rute berhasil dihapus!");
                this.loadData();
            } else {
                alert("Gagal menghapus rute: " + res.message);
            }
        },

        // --- UTILS ---
        copyWa(p) {
            const type = p.serviceType === 'Dropping' ? 'CHARTER' : 'TRAVEL';
            const txt = `*SUTAN RAYA - ${type}*\nJadwal: ${p.time}\nNama: ${p.passengerName}\nKursi: ${p.seatNumbers}\nHP: ${p.passengerPhone}\nJemput: ${p.pickupMapUrl||'-'} (${p.pickupAddress||'-'})\nAntar: ${p.dropoffAddress||'-'}`;
            navigator.clipboard.writeText(txt).then(() => alert("Data disalin ke Clipboard!"));
        },
        formatRupiah(n) { return new Intl.NumberFormat('id-ID', {style:'currency', currency:'IDR', minimumFractionDigits:0}).format(n||0); },
        formatDate(d) { return d ? new Date(d).toLocaleDateString('id-ID', {day:'numeric', month:'short'}) : '-'; },
        updateTime() { const n=new Date(); this.currentTime=n.toLocaleTimeString('id-ID',{hour:'2-digit',minute:'2-digit'}); this.currentDate=n.toLocaleDateString('id-ID',{weekday:'long',day:'numeric',month:'long'}); },
        
        // CSS Helpers
        getVehicleStatusClass(s) { return s==='Tersedia'?'bg-green-100 text-green-700':(s==='On Trip'?'bg-blue-100 text-blue-700':(s==='Perbaikan'?'bg-red-100 text-red-700':'bg-gray-100')); },
        getDriverStatusClass(s) { return s==='Standby'?'bg-green-100 text-green-700':(s==='Jalan'?'bg-blue-100 text-blue-700':'bg-gray-200'); },
        getTripCardClass(s) { if(s==='On Trip') return 'border-blue-200 bg-blue-50/30'; if(s==='Tiba') return 'border-green-200 bg-green-50/30'; if(s==='Kendala') return 'border-red-200 bg-red-50/30'; return 'border-gray-200'; },
        getTripStatusBadge(s) { if(s==='On Trip') return 'bg-blue-500'; if(s==='Tiba') return 'bg-green-500'; if(s==='Kendala') return 'bg-red-500'; return 'bg-gray-400'; },
        
        // --- PRINT TICKET ---
        printTicket(b) {
            const w = window.open('', '', 'width=300,height=600');
            w.document.write(`<html><body style="font-family:monospace;width:58mm;font-size:12px">
            <center><h3>SUTAN RAYA</h3></center>
            <hr>
            ID: #${b.id}<br>
            Nama: ${b.passengerName}<br>
            Rute: ${b.routeName || b.routeId}<br>
            Tgl: ${this.formatDate(b.date)} - ${b.time}<br>
            Kursi: ${b.seatNumbers}<br>
            <hr>
            <b>Total: ${this.formatRupiah(b.totalPrice)}</b><br>
            Status: ${b.paymentStatus}
            </body></html>`);
            w.print();
        }
    }
}).mount("#app");