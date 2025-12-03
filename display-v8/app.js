const { createApp } = Vue;

createApp({
    data() {
        return {
            STORAGE_KEY: 'sutan_v85_data_final', 
            VIEW_KEY: 'sutan_v85_view',
            THEME_KEY: 'sutan_v85_theme',

            isDarkMode: false,
            view: 'dashboard',
            isFullscreen: false,
            currentTime: "",
            currentDate: "",
            reportDate: new Date().toISOString().slice(0,10),
            
            // Modals
            isProofModalVisible: false,
            isDispatchModalVisible: false,
            isVehicleModalVisible: false,
            isDriverModalVisible: false,
            isTripControlVisible: false,
            
            activeTripControl: null,
            validationData: null,
            
            bookingManagementTab: 'travel',
            busSearchTerm: '',
            busViewMode: 'list', 
            calendarMonth: new Date().getMonth(),
            calendarYear: new Date().getFullYear(),

            bookingForm: { data: { id: null, serviceType: 'Travel', routeId: '', date: '', time: '', passengerName: '', passengerPhone: '', passengerType: 'Umum', seatCount: 1, selectedSeats: [], duration: 1 } },
            bookingBusForm: { type: 'Medium', routeId: '', seatCapacity: 33, duration: 1, date: '', passengerName: '', passengerPhone: '', totalPrice: 0, priceType: 'Kantor', packageType: 'Unit', paymentMethod: 'Cash', paymentLocation: '', paymentReceiver: '', paymentProof: '', downPaymentAmount: 0 },
            
            currentPaymentMethod: 'Cash',
            tempPayment: { loc: '', recv: '', proof: '', dpAmount: 0 },
            
            dispatchForm: { group: null, fleetId: "", driverId: "" },
            vehicleModal: { mode: "add", data: null },
            driverModal: { mode: "add", data: null },

            // Data Containers
            bookings: [], fleet: [], drivers: [], trips: [],
            
            // Configs (Immutable)
            routeConfig: [
                { id: "PDG-BKT", origin: "Padang", destination: "Bukittinggi", prices: { umum: 120000, pelajar: 100000, dropping: 900000, carter: 1500000 }, schedules: ["08:00", "10:00", "12:00", "14:00", "16:00", "18:00", "20:00"] },
                { id: "PDG-PYK", origin: "Padang", destination: "Payakumbuh", prices: { umum: 150000, pelajar: 130000, dropping: 1100000, carter: 1800000 }, schedules: ["08:00", "10:00", "14:00", "18:00"] }
            ],
            busRouteConfig: [
                { id: "PDG-BKT", name: "Padang - Bukittinggi", minDays: 1, prices: { s33: 2500000, s35: 2600000 }, big: { s45: { kantor: 4000000, agen: 3800000 }, s32: { kantor: 4500000, agen: 4300000 } } },
                { id: "PDG-JKT", name: "Padang - Jakarta", minDays: 6, prices: { s33: 0, s35: 0 }, isLongTrip: true, big: { base: 4500000, allin: 5500000 } }
            ],
            seatLayout: [{ row: 1, seats: [{id:"CC", type:"seat"}, {id:"driver", type:"driver"}], label: "Depan" }, { row: 2, seats: [{id:"1", type:"seat"}, {id:"2", type:"seat"}], label: "Tengah" }, { row: 3, seats: [{id:"3", type:"seat"}, {id:"4", type:"seat"}], label: "Belakang" }]
        };
    },
    created() {
        this.loadData();
        const savedView = localStorage.getItem(this.VIEW_KEY);
        if(savedView) this.view = savedView;
        
        const savedTheme = localStorage.getItem(this.THEME_KEY);
        this.isDarkMode = savedTheme === 'dark';
        if(this.isDarkMode) document.documentElement.classList.add('dark');
        
        this.updateTime(); setInterval(this.updateTime, 1000);
        this.openBookingModal();
    },
    watch: {
        view(val) { localStorage.setItem(this.VIEW_KEY, val); },
        isDarkMode(val) { 
            localStorage.setItem(this.THEME_KEY, val ? 'dark' : 'light'); 
            val ? document.documentElement.classList.add('dark') : document.documentElement.classList.remove('dark');
        },
        bookings: { handler() { this.saveData(); }, deep: true },
        fleet: { handler() { this.saveData(); }, deep: true },
        drivers: { handler() { this.saveData(); }, deep: true },
        trips: { handler() { this.saveData(); }, deep: true }
    },
    computed: {
        currentViewTitle() { return {dashboard:"Dashboard",bookingManagement:"Kelola Booking",dispatcher:"Dispatcher",bookingTravel:"Travel",bookingBus:"Bus",manifest:"Laporan",assets:"Aset"}[this.view] || "Sutan Raya"; },
        todayRevenue() { return (this.bookings||[]).filter(b => b.date === new Date().toISOString().slice(0,10)).reduce((a,b) => a + (b.totalPrice||0), 0); },
        todayPax() { return (this.bookings||[]).filter(b => b.date === new Date().toISOString().slice(0,10)).length; },
        pendingValidationCount() { return (this.bookings||[]).filter(b => b.validationStatus === 'Menunggu Validasi').length; },
        activeTrips() { return (this.trips||[]).filter(t => !['Tiba', 'Batal'].includes(t.status)); },
        outboundTrips() { return this.activeTrips.filter(t => t.routeConfig?.origin === 'Padang' || (t.routeConfig?.name && t.routeConfig.name.startsWith('Padang'))); },
        inboundTrips() { return this.activeTrips.filter(t => t.routeConfig?.destination === 'Padang' || (t.routeConfig?.name && !t.routeConfig.name.startsWith('Padang'))); },
        
        groupedBookings() {
            const groups = {};
            const pending = (this.bookings||[]).filter(b => b.status === 'Pending' && (b.paymentStatus === 'Lunas' || b.paymentStatus === 'DP') && b.validationStatus === 'Valid');
            pending.forEach(b => {
                const key = `${b.routeId}|${b.date}|${b.time||'Bus'}`;
                if (!groups[key]) {
                    const r = this.routeConfig.find(x=>x.id===b.routeId) || this.busRouteConfig.find(x=>x.id===b.routeId);
                    groups[key] = { key, routeId: b.routeId, routeConfig: r, date: b.date, time: b.time||'Flexible', totalPassengers: 0, passengers: [], serviceType: b.serviceType };
                }
                groups[key].passengers.push(b);
                groups[key].totalPassengers += (b.serviceType.includes('Bus') || b.serviceType==='Carter') ? 1 : (b.seatCount||1);
            });
            return Object.values(groups).sort((a, b) => (a.time || '').localeCompare(b.time || ''));
        },
        pendingGroupsCount() { return this.groupedBookings.length; },

        getManagedBookings() {
            let items = this.view==='bookingManagement' && this.bookingManagementTab==='bus' ? this.bookings.filter(b=>b.serviceType==='Bus Pariwisata') : this.bookings.filter(b=>['Travel','Carter','Dropping'].includes(b.serviceType));
            if(this.busSearchTerm) {
                const term = this.busSearchTerm.toLowerCase();
                items = items.filter(b => (b.passengerName?.toLowerCase().includes(term)) || (b.routeId?.toLowerCase().includes(term)));
            }
            return items.sort((a,b) => new Date(b.id) - new Date(a.id));
        },
        busBookings() { return (this.bookings||[]).filter(b => b.serviceType === 'Bus Pariwisata'); },
        calendarDays() {
            const year = this.calendarYear; const month = this.calendarMonth;
            const daysInMonth = new Date(year, month + 1, 0).getDate();
            const firstDay = new Date(year, month, 1).getDay(); const startDay = firstDay === 0 ? 6 : firstDay - 1;
            const days = [];
            for (let i=startDay-1; i>=0; i--) days.push({date:'', isCurrentMonth:false, events:[]});
            for (let i=1; i<=daysInMonth; i++) {
                const dStr = `${year}-${String(month+1).padStart(2,'0')}-${String(i).padStart(2,'0')}`;
                const dayEvents = this.busBookings.filter(b => b.date === dStr);
                days.push({date:i, isCurrentMonth:true, isToday: new Date().toDateString() === new Date(year,month,i).toDateString(), events: dayEvents});
            }
            while(days.length < 42) days.push({date:'', isCurrentMonth:false, events:[]});
            return days;
        },
        
        selectedRoute() { return this.routeConfig.find(r => r.id === this.bookingForm.data.routeId); },
        getBusRouteName() { const r = this.busRouteConfig.find(x=>x.id===this.bookingBusForm.routeId); return r ? r.name : '-'; },
        getBusDailyPrice() {
            const r = this.busRouteConfig.find(x=>x.id===this.bookingBusForm.routeId); if(!r) return 0;
            if(this.bookingBusForm.type==='Medium') return this.bookingBusForm.seatCapacity==33 ? r.prices.s33 : r.prices.s35;
            const isLong = r.isLongTrip || false;
            if(isLong) return this.bookingBusForm.packageType==='AllIn' ? r.big.allin : r.big.base;
            return this.bookingBusForm.seatCapacity==45 ? (this.bookingBusForm.priceType==='Kantor'?r.big.s45.kantor:r.big.s45.agen) : (this.bookingBusForm.priceType==='Kantor'?r.big.s32.kantor:r.big.s32.agen);
        },
        getBusMinDays() { return this.busRouteConfig.find(x=>x.id===this.bookingBusForm.routeId)?.minDays || 1; },
        isLongTrip() { return this.busRouteConfig.find(x=>x.id===this.bookingBusForm.routeId)?.isLongTrip || false; },

        currentTotalPrice() {
            if(this.view==='bookingBus') return this.bookingBusForm.totalPrice;
            const d = this.bookingForm.data; const r = this.selectedRoute; if(!r) return 0;
            if(d.serviceType === 'Dropping') {
                let p = 1000000; if(r.id.includes('BKT')) p = d.isMultiStop ? 960000:900000; else if(r.id.includes('PYK')) p = d.isMultiStop ? 1200000:1100000;
                return p;
            } else if(d.serviceType === 'Carter') return (r.prices.carter||1500000) * (d.duration||1);
            return (this.bookingForm.selectedSeats?.length||1) * (d.passengerType==='Umum'?r.prices.umum:r.prices.pelajar);
        },
        
        dailyReportData() {
            const date = this.reportDate; const report = {};
            this.routeConfig.forEach(route => {
                report[route.id] = { route: route, schedules: [], total: { umumNom: 0, pelajarNom: 0, totalNom: 0 } };
                route.schedules.forEach(time => {
                    const bList = (this.bookings||[]).filter(b => b.routeId === route.id && b.date === date && b.time === time && b.status !== 'Batal');
                    let row = { time: time, umumPax: 0, umumNom: 0, pelajarPax: 0, pelajarNom: 0, totalNom: 0 };
                    bList.forEach(b => {
                        if(b.passengerType === 'Umum' || b.serviceType === 'Dropping' || b.serviceType === 'Carter') { row.umumPax += (b.seatCount||1); row.umumNom += (b.totalPrice||0); }
                        else { row.pelajarPax += (b.seatCount||1); row.pelajarNom += (b.totalPrice||0); }
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
        toggleDarkMode() { this.isDarkMode = !this.isDarkMode; },
        toggleFullscreen() { this.isFullscreen = !this.isFullscreen; },
        changeMonth(s) { this.calendarMonth+=s; if(this.calendarMonth>11){this.calendarMonth=0;this.calendarYear++} else if(this.calendarMonth<0){this.calendarMonth=11;this.calendarYear--} },
        getMonthName(m) { return ["Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","November","Desember"][m]; },

        openBookingModal() {
            this.bookingForm.data = { id: Date.now(), serviceType: 'Travel', routeId: '', date: '', time: '', passengerName: '', passengerPhone: '', passengerType: 'Umum', seatCount: 1, selectedSeats: [], duration: 1 };
            this.bookingForm.selectedSeats = [];
            this.view = 'bookingTravel';
        },
        setServiceType(t) { this.bookingForm.data.serviceType = t; if(t!=='Travel') { this.bookingForm.data.time=''; this.bookingForm.selectedSeats=[]; } },
        resetSeatSelection() { this.bookingForm.selectedSeats = []; },
        toggleSeat(id) { 
            if(this.isSeatOccupied(id)) return alert('Kursi Terisi'); 
            const s=this.bookingForm.selectedSeats; const i=s.indexOf(id); if(i===-1)s.push(id);else s.splice(i,1); 
        },
        isSeatOccupied(id) { 
            const d=this.bookingForm.data; if(!d.routeId||!d.date||!d.time) return false;
            const ex=(this.bookings||[]).filter(b=>b.routeId===d.routeId && b.date===d.date && b.time===d.time && b.status!=='Batal');
            if(ex.some(b=>b.serviceType!=='Travel')) return true;
            let occ=[]; ex.forEach(b=>{ if(b.seatNumbers) occ.push(...b.seatNumbers.split(', ')); });
            return occ.includes(id);
        },
        isSeatSelected(id) { return this.bookingForm.selectedSeats.includes(id); },
        calculatePrice() { }, calculateBusPrice() { },
        
        handleKTMUpload(e) { if(e.target.files[0]) this.bookingForm.data.ktmProof = e.target.files[0].name; },
        handlePaymentProofUpload(e) { if(e.target.files[0]) this.tempPayment.proof = e.target.files[0].name; },
        
        saveBooking() {
            const d = this.bookingForm.data;
            if(!d.routeId || !d.time || !d.passengerName) return alert("Data Belum Lengkap!");
            if(d.serviceType === 'Travel' && this.bookingForm.selectedSeats.length === 0) return alert("Pilih Kursi!");
            const pm = this.currentPaymentMethod;
            let pStat = 'Menunggu Validasi', vStat = 'Menunggu Validasi';
            if(pm === 'Cash') { if(!this.tempPayment.loc) return alert("Info Cash!"); pStat = 'Lunas'; vStat = 'Valid'; } 
            else if (pm === 'DP') { if(this.tempPayment.dpAmount < 100000) return alert("Min DP 100rb!"); pStat = 'DP'; }
            
            const newBooking = { id: Date.now(), ...d, totalPrice: this.currentTotalPrice, seatCount: d.serviceType==='Travel'?this.bookingForm.selectedSeats.length:1, seatNumbers: d.serviceType==='Travel'?this.bookingForm.selectedSeats.join(', ') : 'Full Unit', paymentMethod: pm, paymentStatus: pStat, validationStatus: vStat, paymentLocation: this.tempPayment.loc, paymentReceiver: this.tempPayment.recv, paymentProof: this.tempPayment.proof, status: 'Pending', serviceType: d.serviceType, routeId: d.routeId };
            this.bookings.push(newBooking);
            alert("Booking Tersimpan!");
            if(confirm("Lanjut Kelola?")) this.view = 'bookingManagement';
            this.bookingForm.selectedSeats = [];
            this.tempPayment = { loc: '', recv: '', proof: '', dpAmount: 0 };
        },
        saveBusBooking() {
            const r = this.busRouteConfig.find(x => x.id === this.bookingBusForm.routeId);
            if((this.bookingBusForm.duration||1) < (r?.minDays||1)) return alert(`Min. ${r.minDays} Hari`);
            const pm = this.bookingBusForm.paymentMethod;
            let pStat = 'Menunggu Validasi', vStat = 'Menunggu Validasi';
            if(pm === 'Cash') { pStat='Lunas'; vStat='Valid'; } else if(pm === 'DP') { pStat='DP'; }
            const newBus = { ...this.bookingBusForm, id: Date.now(), status: 'Pending', serviceType: 'Bus Pariwisata', routeName: r.name, paymentStatus: pStat, validationStatus: vStat };
            this.bookings.push(newBus);
            alert("Booking Bus Tersimpan!");
            if(confirm("Lanjut ke menu Kelola Booking?")) this.view = 'bookingManagement';
        },

        validatePaymentModal(b) { this.validationData = b; this.isProofModalVisible = true; },
        confirmValidation(b) { if(confirm("Validasi?")) { b.validationStatus='Valid'; if(b.paymentStatus!=='DP') b.paymentStatus='Lunas'; this.isProofModalVisible=false; } },
        
        openDispatchModal(g) { this.dispatchForm.group = g; this.dispatchForm.fleetId=""; this.dispatchForm.driverId=""; this.isDispatchModalVisible=true; },
        processDispatch() {
            const { group, fleetId, driverId } = this.dispatchForm;
            if(!fleetId || !driverId) return alert("Pilih Aset!");
            const f = this.fleet.find(x=>x.id===fleetId); const d = this.drivers.find(x=>x.id===driverId);
            if(group.serviceType==='Carter' && !f.name.includes('Hiace')) return alert("Carter hanya Hiace!");
            this.trips.push({ id: Date.now(), routeConfig: group, fleet: f, driver: d, passengers: [...group.passengers], status: "On Trip" });
            group.passengers.forEach(p => { const b = this.bookings.find(x=>x.id===p.id); if(b) b.status = 'On Trip'; });
            f.status = "On Trip"; d.status = "Jalan";
            this.isDispatchModalVisible = false; this.view = 'dashboard';
        },
        
        openTripControl(t) { this.activeTripControl = t; this.isTripControlVisible = true; },
        updateTripStatus(t, s) {
            t.status = s;
            const f = this.fleet.find(x=>x.id===t.fleet.id);
            const d = this.drivers.find(x=>x.id===t.driver.id);
            if(s === 'Tiba') { 
                if(confirm("Selesai?")) { f.status = "Tersedia"; d.status = "Standby"; t.passengers.forEach(p => { const b = this.bookings.find(x=>x.id===p.id); if(b) b.status = 'Tiba'; }); } else t.status='On Trip'; 
            } else if(s === 'Kendala') f.status = "Perbaikan";
            this.isTripControlVisible = false;
        },

        // PRINT TICKET LOGIC
        printTicket(b) {
            const printWindow = window.open('', '', 'width=300,height=600');
            printWindow.document.write(`
                <html><head><style>
                    body{font-family:monospace;width:58mm;font-size:12px;margin:0;padding:10px}
                    .header{text-align:center;font-weight:bold;font-size:14px;margin-bottom:5px}
                    .divider{border-top:1px dashed #000;margin:5px 0}
                    .item{display:flex;justify-content:space-between}
                    .footer{text-align:center;margin-top:10px;font-size:10px}
                </style></head><body>
                <div class="header">SUTAN RAYA</div>
                <div style="text-align:center">${b.serviceType.toUpperCase()}</div>
                <div class="divider"></div>
                <div class="item"><span>ID:</span><span>#${b.id.toString().slice(-6)}</span></div>
                <div class="item"><span>Tgl:</span><span>${this.formatDate(b.date)}</span></div>
                <div class="item"><span>Jam:</span><span>${b.time || '-'}</span></div>
                <div class="divider"></div>
                <div class="item"><span>Penumpang:</span><span>${b.passengerName}</span></div>
                <div class="item"><span>Kursi:</span><span>${b.seatNumbers || '1 Unit'}</span></div>
                <div class="divider"></div>
                <div class="item" style="font-weight:bold;font-size:14px"><span>TOTAL:</span><span>${this.formatRupiah(b.totalPrice)}</span></div>
                <div class="footer">Terima Kasih<br>Simpan struk ini sebagai bukti sah.</div>
                </body></html>
            `);
            printWindow.document.close();
            printWindow.focus();
            printWindow.print();
            printWindow.close();
        },

        // CRUD Assets
        openVehicleModal(v) { this.vehicleModal = { mode: v?'edit':'add', data: v?{...v}:{id:Date.now(), name:"", plate:"", capacity:7, status:"Tersedia", icon:"bi-truck-front-fill"} }; this.isVehicleModalVisible=true; },
        saveVehicle() { if(this.vehicleModal.mode==='add') this.fleet.push(this.vehicleModal.data); else { const i=this.fleet.findIndex(x=>x.id===this.vehicleModal.data.id); this.fleet[i]=this.vehicleModal.data; } this.isVehicleModalVisible=false; },
        openDriverModal(d) { this.driverModal = { mode: d?'edit':'add', data: d?{...d}:{id:Date.now(), name:"", phone:"", status:"Standby"} }; this.isDriverModalVisible=true; },
        saveDriver() { if(this.driverModal.mode==='add') this.drivers.push(this.driverModal.data); else { const i=this.drivers.findIndex(x=>x.id===this.driverModal.data.id); this.drivers[i]=this.driverModal.data; } this.isDriverModalVisible=false; },
        closeVehicleModal() { this.isVehicleModalVisible = false; }, closeDriverModal() { this.isDriverModalVisible = false; },
        deleteVehicle(id) { if(confirm('Hapus?')) this.fleet = this.fleet.filter(f=>f.id!==id); },
        deleteDriver(id) { if(confirm('Hapus?')) this.drivers = this.drivers.filter(d=>d.id!==id); },

        // System
        saveData() { localStorage.setItem(this.STORAGE_KEY, JSON.stringify({b:this.bookings, f:this.fleet, d:this.drivers, t:this.trips})); },
        loadData() {
            try {
                const d = JSON.parse(localStorage.getItem(this.STORAGE_KEY));
                if(d) { this.bookings=d.b||[]; this.fleet=d.f||[]; this.drivers=d.d||[]; this.trips=d.t||[]; }
                else {
                    this.fleet = [{id:1,name:"Hiace Premio 01",plate:"BA 1001 HP",capacity:7,status:"Tersedia",icon:"bi-truck-front-fill"},{id:2,name:"Medium Bus 21",plate:"BA 7021 MB",capacity:33,status:"Tersedia",icon:"bi-bus-front-fill"}];
                    this.drivers = [{id:101,name:"Pak Budi",phone:"0812345678",status:"Standby"}];
                }
            } catch(e) { console.log('Reset DB'); }
        },
        
        formatRupiah(n) { return new Intl.NumberFormat('id-ID', {style:'currency', currency:'IDR', minimumFractionDigits:0}).format(n||0); },
        formatDate(d) { return d ? new Date(d).toLocaleDateString('id-ID', {day:'numeric', month:'short'}) : '-'; },
        updateTime() { const n=new Date(); this.currentTime=n.toLocaleTimeString('id-ID',{hour:'2-digit',minute:'2-digit'}); this.currentDate=n.toLocaleDateString('id-ID',{weekday:'long',day:'numeric',month:'long'}); },
        getVehicleStatusClass(s) { return s==='Tersedia'?'bg-green-100 text-green-700':(s==='On Trip'?'bg-blue-100 text-blue-700':(s==='Perbaikan'?'bg-red-100 text-red-700':'bg-gray-100')); },
        getDriverStatusClass(s) { return s==='Standby'?'bg-green-100 text-green-700':(s==='Jalan'?'bg-blue-100 text-blue-700':'bg-gray-200'); },
        getTripCardClass(s) { if(s==='On Trip') return 'border-blue-200 bg-blue-50/30'; if(s==='Tiba') return 'border-green-200 bg-green-50/30'; if(s==='Kendala') return 'border-red-200 bg-red-50/30'; return 'border-gray-200'; },
        getTripStatusBadge(s) { if(s==='On Trip') return 'bg-blue-500'; if(s==='Tiba') return 'bg-green-500'; if(s==='Kendala') return 'bg-red-500'; return 'bg-gray-400'; }
    }
}).mount("#app");