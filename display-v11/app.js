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
            
            // Security
            isLocked: true,
            accessCode: '',
            
            // -- State Modals --
            isProofModalVisible: false,
            isDispatchModalVisible: false,
            isVehicleModalVisible: false,
            isDriverModalVisible: false,
            isTripControlVisible: false,
            isTicketModalVisible: false,
            isRouteModalVisible: false,
            isManualAssignModalVisible: false,
            isScheduleModalVisible: false,
            isKtmModalVisible: false,
            activeKtmImage: '',
            activeBookingName: '',
            
            // -- Data Models --
            activeTripControl: null,
            validationData: null,
            ticketData: null,
            manualAssignments: {}, // { bookingId: { fleetId, driverId } }
            manualAssignForm: { bookingId: null, fleetId: '', driverId: '' },
            scheduleForm: { route: null, time: '', fleetId: '', driverId: '', isDefault: false },
            scheduleDefaults: [],
            
            // -- Forms --
            bookingManagementTab: 'travel',
            busSearchTerm: '',
            bookingForm: { 
                data: { id: null, serviceType: 'Travel', routeId: '', date: '', time: '', passengerName: '', passengerPhone: '', passengerType: 'Umum', seatCount: 1, duration: 1, isMultiStop: false },
                selectedSeats: []
            },
            filterMethod: 'All',
            filterSort: 'Newest',
            filterDate: '',
            filterRoute: 'All',
            bookingBusForm: { type: 'Medium', routeId: '', seatCapacity: 33, duration: 1, date: '', passengerName: '', passengerPhone: '', totalPrice: 0, priceType: 'Kantor', packageType: 'Unit', paymentMethod: 'Cash', paymentLocation: '', paymentReceiver: '', paymentProof: '', downPaymentAmount: 0 },
            
            currentPaymentMethod: 'Cash',
            tempPayment: { loc: '', recv: '', proof: '', dpAmount: 0, dpMethod: 'Cash' },
            dispatchForm: { group: null, fleetId: "", driverId: "" },
            vehicleModal: { mode: "add", data: null },
            driverModal: { mode: "add", data: null },
            routeModal: { mode: 'add', data: { id: '', origin: '', destination: '', schedulesInput: '', prices: { umum: 0, pelajar: 0, dropping: 0, carter: 0 } } },

            // Input Memory
            savedReceivers: JSON.parse(localStorage.getItem('sr_receivers') || '[]'),
            savedLocations: JSON.parse(localStorage.getItem('sr_locations') || '[]'),

            // -- Data from Server --
            bookings: [],
            fleet: [],
            drivers: [],
            trips: [],
            routeConfig: [],     // Akan di-load dari API
            busRouteConfig: [],  // Akan di-load dari API
            staffList: [{name:'Owner'}, {name:'Admin'}, {name:'Counter'}],       // Staff Users (Manual)
            
            // -- Static Config --
            seatLayout: [
                { row: 1, seats: [{id:"1", type:"seat"}, {id:"driver", type:"driver"}], label: "Depan" },
                { row: 2, seats: [{id:"2", type:"seat"}, {id:"3", type:"seat"}], label: "Tengah 1" },
                { row: 3, seats: [{id:"4", type:"seat"}, {id:"5", type:"seat"}], label: "Tengah 2" },
                { row: 4, seats: [{id:"6", type:"seat"}, {id:"7", type:"seat"}, {id:"8", type:"seat"}], label: "Belakang" }
            ],
            calendarMonth: new Date().getMonth(),
            calendarYear: new Date().getFullYear(),
            
            // -- Reports --
            period: 'daily',
            reportData: { labels: [], revenue: [], pax: [], details: {} },
            charts: { revenue: null, pax: null },
            manifestDate: new Date().toISOString().slice(0,10),
            detailModal: { isOpen: false, type: 'income', title: '', data: [] },
        };
    },
    created() {
        this.loadData();
        // Auto-refresh setiap 30 detik agar data selalu update tanpa reload
        setInterval(() => { this.loadData(true); }, 30000);
        
        this.updateTime(); 
        setInterval(this.updateTime, 1000);
        
        // Load view preference
        if (window.initialView) {
            this.view = window.initialView;
        } else {
            const savedView = localStorage.getItem('sutan_v10_view');
            if(savedView) this.view = savedView;
            else this.openBookingModal();
        }
    },
    watch: {
        view(val) { localStorage.setItem('sutan_v10_view', val); },
    },
    computed: {
        currentViewTitle() { return {dashboard:"Dashboard",bookingManagement:"Kelola Booking",dispatcher:"Dispatcher",bookingTravel:"Travel",bookingBus:"Bus",manifest:"Laporan",assets:"Aset",routeManagement:"Rute",schedule:"Jadwal"}[this.view] || "Sutan Raya"; },
        todayRevenue() { return this.bookings.filter(b => b.date === new Date().toISOString().slice(0,10) && b.status !== 'Batal').reduce((a,b) => a + (b.totalPrice||0), 0); },
        todayPax() { return this.bookings.filter(b => b.date === new Date().toISOString().slice(0,10) && b.status !== 'Batal').length; },
        pendingValidationCount() { return this.bookings.filter(b => b.validationStatus === 'Menunggu Validasi').length; },
        activeTrips() { 
            return this.trips.filter(t => {
                if (['Tiba', 'Batal'].includes(t.status)) return false;
                // Check for empty trips (0 passengers)
                const pCount = this.getTripPassengerCount(t);
                if (pCount === 0) return false; 
                return true;
            }); 
        },
        pendingGroupsCount() { return this.groupedBookings.length; },
        pendingDispatchCount() { return this.groupedBookings.length; },
        
        groupedBookings() {
            // Logic Dispatcher: Mengelompokkan booking Travel/Carter yang siap jalan
            const groups = {};
            
            // 1. Group by Time + Route
            this.bookings.forEach(b => {
                // BUG FIX: Allow undefined/null status (treat as Pending)
                // If status exists, it must be Pending or Confirmed.
                if (b.status && b.status !== 'Pending' && b.status !== 'Confirmed') return;
                
                // Key: Time + RouteId
                const key = `${b.date}_${b.time}_${b.routeId}`;
                
                if (!groups[key]) {
                    // Find route config
                    const r = this.routeConfig.find(x => x.id === b.routeId) || this.busRouteConfig.find(x => x.id === b.routeId);
                    
                    groups[key] = {
                        key: key,
                        date: b.date,
                        time: b.time,
                        routeId: b.routeId,
                        routeConfig: r,
                        passengers: [],
                        totalPassengers: 0,
                        assignment: null
                    };
                }
                groups[key].passengers.push(b);
                groups[key].totalPassengers += (parseInt(b.seatCount) || 1);
            });

            // 2. Split into Batches (Respecting Seat Conflicts & Capacity)
            const batches = [];
            Object.values(groups).forEach(group => {
                const passengers = group.passengers;
                const batchSize = 8;
                
                // Get Assignment for this Route/Time
                const assignment = this.getAssignment(group.routeId, group.time, group.date);
                group.assignment = assignment;

                const fleetBatches = []; 
                
                passengers.forEach(p => {
                    let placed = false;
                    const pSeats = p.seatNumbers ? p.seatNumbers.split(',').map(s => s.trim()) : [];
                    
                    for (let i = 0; i < fleetBatches.length; i++) {
                        const batch = fleetBatches[i];
                        
                        const currentLoad = batch.reduce((sum, bp) => sum + (parseInt(bp.seatCount) || 1), 0);
                        const pLoad = parseInt(p.seatCount) || 1;
                        
                        if (currentLoad + pLoad > batchSize) continue; 
                        
                        let conflict = false;
                        if (pSeats.length > 0) {
                            const batchSeats = [];
                            batch.forEach(bp => {
                                if (bp.seatNumbers) {
                                    bp.seatNumbers.split(',').forEach(s => batchSeats.push(s.trim()));
                                }
                            });
                            
                            if (pSeats.some(s => batchSeats.includes(s))) {
                                conflict = true;
                            }
                        }
                        
                        if (!conflict) {
                            batch.push(p);
                            placed = true;
                            break;
                        }
                    }
                    
                    if (!placed) {
                        fleetBatches.push([p]);
                    }
                });

                // Convert back to group format
                fleetBatches.forEach((batchPassengers, index) => {
                    const batchNumber = index + 1;
                    batches.push({
                        ...group,
                        key: `${group.key}_batch_${batchNumber}`,
                        passengers: batchPassengers,
                        totalPassengers: batchPassengers.reduce((sum, p) => sum + (parseInt(p.seatCount) || 1), 0),
                        batchNumber: batchNumber,
                        isFullBatch: batchPassengers.reduce((sum, p) => sum + (parseInt(p.seatCount) || 1), 0) >= batchSize,
                        assignment: group.assignment // Propagate assignment
                    });
                });
            });

            // Sort by Date then Time
            return batches.sort((a, b) => {
                if (a.date !== b.date) return new Date(a.date) - new Date(b.date);
                return a.time.localeCompare(b.time);
            });
        },

        groupedDispatcherViews() {
            const batches = this.groupedBookings;
            const routeGroups = {};

            batches.forEach(batch => {
                const routeName = batch.routeConfig ? (batch.routeConfig.name || `${batch.routeConfig.origin} - ${batch.routeConfig.destination}`) : 'Lainnya';
                if (!routeGroups[routeName]) {
                    routeGroups[routeName] = [];
                }
                routeGroups[routeName].push(batch);
            });

            // Sort content inside each group: Newest to Oldest (Date DESC, Time DESC)
            Object.keys(routeGroups).forEach(key => {
                routeGroups[key].sort((a, b) => {
                    if (a.date !== b.date) return new Date(b.date) - new Date(a.date); // Date DESC
                    return b.time.localeCompare(a.time); // Time DESC
                });
            });

            return routeGroups;
        },
        
        // Report Computed
        reversedLabels() { return [...this.reportData.labels].reverse(); },
        reversedRevenue() { return [...this.reportData.revenue].reverse(); },
        reversedRevenueCash() { return [...(this.reportData.revenueCash || [])].reverse(); },
        reversedRevenueTransfer() { return [...(this.reportData.revenueTransfer || [])].reverse(); },
        reversedPax() { return [...this.reportData.pax].reverse(); },
        
        // DEBUGGING TOOL
        debugHiddenBookings() {
            // Find bookings that are NOT in groupedBookings (Pending) AND NOT in activeTrips (On Trip)
            // But exclude Cancelled ones.
            
            const pendingIds = [];
            this.groupedBookings.forEach(batch => {
                batch.passengers.forEach(p => pendingIds.push(p.id));
            });
            
            const onTripIds = [];
            this.activeTrips.forEach(t => {
                if(t.passengers) t.passengers.forEach(p => onTripIds.push(p.id));
            });
            
            return this.bookings.filter(b => {
                if (b.status === 'Batal' || b.status === 'Cancelled') return false; // Expected hidden
                if (b.status === 'Tiba' || b.status === 'Arrived') return false; // Expected hidden (History)
                
                const isPending = pendingIds.includes(b.id);
                const isOnTrip = onTripIds.includes(b.id);
                
                // If it is neither Pending nor On Trip, it is "Hidden" or "Lost"
                return !isPending && !isOnTrip; 
            }).map(b => ({
                id: b.id,
                name: b.passengerName,
                status: b.status,
                paymentStatus: b.paymentStatus,
                date: b.date,
                reason: (!b.status || (b.status !== 'Pending' && b.status !== 'Confirmed')) ? 'Status Invalid' : 'Unknown Logic'
            }));
        },

        getManagedBookings() {
            let items = this.view==='bookingManagement' && this.bookingManagementTab==='bus' 
                ? this.bookings.filter(b=>b.serviceType==='Bus Pariwisata') 
                : this.bookings.filter(b=>['Travel','Carter','Dropping'].includes(b.serviceType));
            
            // Filter: Search
            if(this.busSearchTerm) {
                const term = this.busSearchTerm.toLowerCase();
                items = items.filter(b => (b.passengerName?.toLowerCase().includes(term)) || (b.passengerPhone?.includes(term)));
            }
            
            // Filter: Method
            if(this.filterMethod !== 'All') {
                items = items.filter(b => (b.paymentMethod === this.filterMethod));
            }
            
            // Filter: Date
            if(this.filterDate) {
                items = items.filter(b => b.date === this.filterDate);
            }
            
            // Filter: Route
            if(this.filterRoute !== 'All') {
                items = items.filter(b => (b.routeId === this.filterRoute || b.routeName === this.filterRoute));
            }
            
            // Sort
            if (this.filterSort === 'Newest') {
                return items.sort((a,b) => new Date(b.id) - new Date(a.id));
            } else {
                return items.sort((a,b) => new Date(a.id) - new Date(b.id)); // Oldest
            }
        },
        uniqueRoutes() {
            const routes = new Set();
            this.bookings.forEach(b => {
                if(b.routeId) routes.add(b.routeId);
                if(b.routeName) routes.add(b.routeName);
            });
            // Also include config
            this.routeConfig.forEach(r => routes.add(r.id));
            if (this.busRouteConfig) this.busRouteConfig.forEach(r => routes.add(r.name));
            return Array.from(routes).sort();
        },
        
        selectedRoute() { return this.routeConfig.find(r => r.id === this.bookingForm.data.routeId); },
        currentSchedules() { return this.selectedRoute ? (this.selectedRoute.schedules || []) : []; },
        
        currentTotalPrice() {
            if(this.view==='bookingBus') return this.bookingBusForm.totalPrice;
            const d = this.bookingForm.data; 
            const r = this.selectedRoute; 
            if(!r) return 0;
            
            if(d.serviceType === 'Dropping') {
                // Logic from v9
                let p = 1000000; 
                if(r.id.includes('BKT')) p = d.isMultiStop ? 960000 : 900000; 
                else if(r.id.includes('PYK')) p = d.isMultiStop ? 1200000 : 1100000;
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

        manifestReport() {
            const date = this.manifestDate;
            const report = {
                routes: {},
                charters: [],
                charterTotal: { totalPrice: 0, paidAmount: 0, remainingAmount: 0 },
                recap: [],
                grandTotal: { umumPax: 0, umumNominal: 0, pelajarPax: 0, pelajarNominal: 0, totalPax: 0, totalNominal: 0, unpaidAmount: 0 }
            };

            // Filter bookings for the date
            const dailyBookings = this.bookings.filter(b => b.date === date && b.status !== 'Batal');

            // Process Charters
            report.charters = dailyBookings.filter(b => b.serviceType === 'Carter').map(b => {
                const paid = b.paymentStatus === 'Lunas' ? b.totalPrice : (b.downPaymentAmount || 0);
                const remain = b.totalPrice - paid;
                
                report.charterTotal.totalPrice += (b.totalPrice || 0);
                report.charterTotal.paidAmount += paid;
                report.charterTotal.remainingAmount += remain;

                return {
                    date: b.date,
                    returnDate: null, 
                    route: b.routeName || b.routeId,
                    totalPrice: b.totalPrice || 0,
                    paidAmount: paid,
                    remainingAmount: remain
                };
            });

            // Process Regular Routes
            const regularBookings = dailyBookings.filter(b => b.serviceType === 'Travel');
            
            // Group by Route
            const routeGroups = {};
            regularBookings.forEach(b => {
                const rName = b.routeName || b.routeId || 'Lainnya';
                if (!routeGroups[rName]) routeGroups[rName] = [];
                routeGroups[rName].push(b);
            });

            // Build Route Tables
            for (const [rName, bookings] of Object.entries(routeGroups)) {
                const rows = {}; // Key: time
                const total = { umumPax: 0, umumNominal: 0, pelajarPax: 0, pelajarNominal: 0, totalPax: 0, totalNominal: 0 };

                bookings.forEach(b => {
                    const time = b.time || '00:00';
                    if (!rows[time]) {
                        rows[time] = { time, umumPax: 0, umumNominal: 0, pelajarPax: 0, pelajarNominal: 0, totalPax: 0, totalNominal: 0, notes: '' };
                    }
                    
                    const isPelajar = b.passengerType === 'Pelajar';
                    const pax = parseInt(b.seatCount) || 1;
                    const price = parseFloat(b.totalPrice) || 0;

                    if (isPelajar) {
                        rows[time].pelajarPax += pax;
                        rows[time].pelajarNominal += price;
                        total.pelajarPax += pax;
                        total.pelajarNominal += price;
                    } else {
                        rows[time].umumPax += pax;
                        rows[time].umumNominal += price;
                        total.umumPax += pax;
                        total.umumNominal += price;
                    }
                    
                    rows[time].totalPax += pax;
                    rows[time].totalNominal += price;
                    total.totalPax += pax;
                    total.totalNominal += price;
                });

                // Convert rows object to array and sort by time
                const sortedRows = Object.values(rows).sort((a, b) => a.time.localeCompare(b.time));
                
                report.routes[rName] = {
                    rows: sortedRows,
                    total: total
                };

                // Add to Recap
                const unpaid = bookings.filter(b => b.paymentStatus !== 'Lunas').reduce((sum, b) => sum + (b.totalPrice - (b.downPaymentAmount||0)), 0);
                
                report.recap.push({
                    name: rName,
                    umumPax: total.umumPax,
                    umumNominal: total.umumNominal,
                    pelajarPax: total.pelajarPax,
                    pelajarNominal: total.pelajarNominal,
                    totalPax: total.totalPax,
                    totalNominal: total.totalNominal,
                    unpaidAmount: unpaid
                });

                // Add to Grand Total
                report.grandTotal.umumPax += total.umumPax;
                report.grandTotal.umumNominal += total.umumNominal;
                report.grandTotal.pelajarPax += total.pelajarPax;
                report.grandTotal.pelajarNominal += total.pelajarNominal;
                report.grandTotal.totalPax += total.totalPax;
                report.grandTotal.totalNominal += total.totalNominal;
                report.grandTotal.unpaidAmount += unpaid;
            }

            return report;
        },
    },

    methods: {
        unlockPage() {
            if(this.accessCode === '1111') {
                this.isLocked = false;
                // Optional: Store session
                // localStorage.setItem('sr_session_unlocked', 'true');
            } else {
                Swal.fire('Akses Ditolak', 'Kode akses salah!', 'error');
                this.accessCode = '';
            }
        },
        // ... (Previous methods)
        
        // --- HELPER --
        showToast(title, icon = 'success') {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });
            Toast.fire({ icon: icon, title: title });
        },

        getDayName(dateStr) {
            if (!dateStr) return '';
            const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            return days[new Date(dateStr).getDay()];
        },
        formatNumber(num) {
            return (num || 0).toLocaleString('id-ID');
        },
        changeView(v) { 
            const map = {
                dashboard: 'dashboard.php',
                bookingManagement: 'booking_management.php',
                bookingTravel: 'booking_travel.php',
                bookingBus: 'booking_bus.php',
                dispatcher: 'dispatcher.php',
                manifest: 'manifest.php',
                assets: 'assets.php',
                routeManagement: 'route_management.php'
            };
            if (map[v]) {
                window.location.href = map[v];
            } else {
                this.view = v; 
            }
        },
        toggleDarkMode() { this.isDarkMode = !this.isDarkMode; if(this.isDarkMode) document.documentElement.classList.add('dark'); else document.documentElement.classList.remove('dark'); },
        
        openDetailModal(type) {
            this.detailModal.type = type;
            this.detailModal.isOpen = true;
            
            // Filter bookings for the selected date
            const date = this.manifestDate;
            const bookings = this.bookings.filter(b => b.date === date && b.status !== 'Batal');
            
            if (type === 'income') {
                this.detailModal.title = 'Detail Pendapatan';
                this.detailModal.data = bookings.map(b => ({
                    id: b.id,
                    name: b.passengerName,
                    route: b.routeName || b.routeId,
                    amount: b.totalPrice,
                    status: b.paymentStatus,
                    method: b.paymentMethod,
                    receiver: b.paymentReceiver || '-'
                })).sort((a,b) => b.amount - a.amount);
            } else if (type === 'passengers') {
                this.detailModal.title = 'Daftar Penumpang';
                this.detailModal.data = bookings.map(b => ({
                    id: b.id,
                    name: b.passengerName,
                    phone: b.passengerPhone,
                    route: b.routeName || b.routeId,
                    seat: b.selectedSeats ? b.selectedSeats.join(', ') : '-',
                    type: b.passengerType
                })).sort((a,b) => a.name.localeCompare(b.name));
            } else if (type === 'unpaid') {
                this.detailModal.title = 'Belum Bayar / DP';
                this.detailModal.data = bookings.filter(b => b.paymentStatus !== 'Lunas').map(b => ({
                    id: b.id,
                    name: b.passengerName,
                    phone: b.passengerPhone,
                    route: b.routeName || b.routeId,
                    total: b.totalPrice,
                    paid: b.downPaymentAmount || 0,
                    remaining: b.totalPrice - (b.downPaymentAmount || 0),
                    status: b.paymentStatus
                })).sort((a,b) => b.remaining - a.remaining);
            }
        },
        closeDetailModal() {
            this.detailModal.isOpen = false;
        },
        
        // --- API COMMUNICATION ---


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
            this.changeView('bookingTravel');
        },
        
        setServiceType(t) { this.bookingForm.data.serviceType = t; if(t!=='Travel') { this.bookingForm.data.time=''; this.bookingForm.selectedSeats=[]; } },
        toggleSeat(id) { 
            if(this.isSeatOccupied(id)) return Swal.fire('Kursi Terisi', 'Kursi ini sudah dibooking.', 'warning'); 
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

    handleProofUpload(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                paymentProofBase64 = e.target.result;
                document.getElementById('proofLabel').innerHTML = '<i class="bi bi-check-circle-fill text-green-500 text-lg block mb-1"></i>File Terupload';
            };
            reader.readAsDataURL(input.files[0]);
        }
    },

        saveBooking() {
            const d = this.bookingForm.data;
            if(!d.passengerName || !d.routeId || !d.date) return Swal.fire('Eits!', 'Data booking belum lengkap.', 'error');
            if(d.serviceType === 'Travel' && (!d.time || this.bookingForm.selectedSeats.length === 0)) return Swal.fire('Lupa Jadwal?', 'Silakan pilih jadwal keberangkatan dan kursi.', 'warning');
            
            // Payment Logic
            const pm = this.currentPaymentMethod;
            let pStat = 'Menunggu Validasi', vStat = 'Menunggu Validasi';
            
            if(pm === 'Cash') { 
                if(!this.tempPayment.loc) return Swal.fire('Lokasi?', 'Isi lokasi penjemputan uang cash.', 'info'); 
                pStat = 'Lunas'; vStat = 'Valid'; 
                this.saveInputMemory(this.tempPayment.recv, this.tempPayment.loc);
            } else if (pm === 'DP') {
                 if(this.tempPayment.dpAmount < 50000) return Swal.fire('Minimal DP', 'Minimal DP adalah Rp 50.000', 'warning');
                 pStat = 'DP';
                 if(this.tempPayment.dpMethod === 'Cash') {
                     if(!this.tempPayment.loc) return Swal.fire('Lokasi?', 'Isi lokasi pengambilan DP.', 'info');;
                     this.saveInputMemory(this.tempPayment.recv, this.tempPayment.loc);
                 }
            }

            const newBooking = { 
                id: Date.now(), 
                ...d, 
                status: 'Pending', // Explicitly set status
                totalPrice: this.currentTotalPrice, 
                seatCount: d.serviceType==='Travel'?this.bookingForm.selectedSeats.length:1, 
                seatNumbers: d.serviceType==='Travel'?this.bookingForm.selectedSeats.join(', ') : 'Full Unit', 
                paymentMethod: pm, paymentStatus: pStat, validationStatus: vStat, 
                paymentLocation: this.tempPayment.loc, paymentReceiver: this.tempPayment.recv, paymentProof: this.tempPayment.proof, 
                downPaymentAmount: this.tempPayment.dpAmount,
                type: 'Unit' // Default
            };

            this.isLoading = true;
            this.postToApi('create_booking', { data: newBooking }).then(res => {
                this.isLoading = false;
                if(res.status === 'success') {
                    this.showToast('Booking Berhasil Disimpan!');
                    this.bookings.unshift(newBooking); // Update UI Instan
                    this.tempPayment = { loc: '', recv: '', proof: '', dpAmount: 0, dpMethod: 'Cash' };
                    Swal.fire({
                        title: 'Booking Tersimpan',
                        text: "Lanjutkan ke menu Kelola Booking?",
                        icon: 'success',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Kelola',
                        cancelButtonText: 'Buat Baru'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            this.changeView('bookingManagement');
                        } else {
                            // Reset form to stay and make new
                            this.openBookingModal();
                        }
                    });
                } else {
                    Swal.fire('Gagal', res.message, 'error');
                }
            });
        },

        saveInputMemory(recv, loc) {
            if(recv && !this.savedReceivers.includes(recv)) {
                this.savedReceivers.push(recv);
                localStorage.setItem('sr_receivers', JSON.stringify(this.savedReceivers));
            }
            if(loc && !this.savedLocations.includes(loc)) {
                this.savedLocations.push(loc);
                localStorage.setItem('sr_locations', JSON.stringify(this.savedLocations));
            }
        },

        async saveBusBooking() {
             const r = this.busRouteConfig.find(x => x.id === this.bookingBusForm.routeId);
             if(!r) return Swal.fire('Pilih Rute', 'Mohon pilih rute bus terlebih dahulu.', 'warning');
             
             const pm = this.bookingBusForm.paymentMethod;
             let pStat = pm==='Cash'?'Lunas':'Menunggu Validasi';
             let vStat = pm==='Cash'?'Valid':'Menunggu Validasi';
             if(pm==='DP') pStat = 'DP';

             const newBus = { 
                 ...this.bookingBusForm, 
                 id: Date.now(), 
                 serviceType: 'Bus Pariwisata', 
                 status: 'Pending',
                 routeName: r.name, 
                 paymentStatus: pStat, 
                 validationStatus: vStat,
                 selectedSeats: [] // Bus pariwisata usually doesn't select seats individually here
             };

             this.isLoading = true;
             const res = await this.postToApi('create_booking', { data: newBus });
             this.isLoading = false;

             if(res.status === 'success') {
                 this.showToast('Booking Bus Tersimpan');
                 this.bookings.unshift(newBus);
                 this.changeView('bookingManagement');
             } else {
                 Swal.fire('Aduh Gagal', res.message, 'error');
             }
        },

        // --- MANAJEMEN BOOKING ---
        validatePaymentModal(b) { this.validationData = b; this.isProofModalVisible = true; },
        async confirmValidation(b) {
            const result = await Swal.fire({
                title: 'Validasi Pembayaran?',
                text: "Status akan diubah menjadi LUNAS dan VALID.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Validasi',
                cancelButtonText: 'Batal'
            });

            if(!result.isConfirmed) return;
            
            const res = await this.postToApi('update_payment_status', { 
                id: b.id, 
                paymentStatus: 'Lunas', 
                validationStatus: 'Valid' 
            });
            
            if(res.status === 'success') {
                b.paymentStatus = 'Lunas';
                b.validationStatus = 'Valid';
                this.isProofModalVisible = false;
                this.showToast('Pembayaran Valid!');
            }
        },
        async deleteBooking(b) {
            const result = await Swal.fire({
                title: 'Hapus Booking?',
                text: "Booking ini akan dihapus permanen. Yakin?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Hapus',
                cancelButtonText: 'Batal'
            });
            
            if(!result.isConfirmed) return;

            const res = await this.postToApi('delete_booking', { id: b.id });
            if(res.status === 'success') {
                this.bookings = this.bookings.filter(x => x.id !== b.id);
                this.showToast('Booking Dihapus');
            }
        },

        // --- DISPATCHER ---
        // --- DISPATCHER ---
        openDispatchModal(g) { 
            // Group is already a batch (max 8) thanks to groupedBookings logic
            
            // Generate Schedule Options (Next slots)
            let nextSchedules = [];
            if (g.routeConfig && g.routeConfig.schedules) {
                // Filter schedules after current time
                nextSchedules = g.routeConfig.schedules.filter(t => t > g.time);
            }

            this.dispatchForm = {
                group: g,
                fleetId: "",
                driverId: "",
                passengers: g.passengers,
                remainingCount: 0, // No remaining in this specific batch
                scheduleOption: 'Normal', 
                nextSchedules: nextSchedules,
                isLocked: false,
                assignmentReason: ''
            };
            
            // Check for assignment
            const assignment = this.getAssignment(g.routeId, g.time, g.date);
            if (assignment && assignment.fleet && assignment.driver && assignment.status !== 'Conflict') {
                this.dispatchForm.fleetId = assignment.fleet.id;
                this.dispatchForm.driverId = assignment.driver.id;
                this.dispatchForm.isLocked = true;
                this.dispatchForm.assignmentReason = assignment.type === 'Specific' ? 'Penugasan Khusus' : 'Jadwal Default';
            } else {
                // No valid assignment - this case is now handled by the UI button
                // But for safety:
                this.dispatchForm.isLocked = false;
            }

            this.isDispatchModalVisible = true; 
        },

        async processDispatch() {
            const { group, fleetId, driverId, passengers, scheduleOption } = this.dispatchForm;
            if(!fleetId || !driverId) return Swal.fire('Data Kurang', 'Pilih Armada manual atau driver terlebih dahulu!', 'warning');
            
            const f = this.fleet.find(x=>x.id===fleetId);
            const d = this.drivers.find(x=>x.id===driverId);
            
            // Determine Status/Note based on option
            let tripStatus = 'On Trip';
            let tripNote = '';
            if (scheduleOption !== 'Normal') {
                tripNote = `Tambahan (Geser ${scheduleOption})`;
            }

            const newTrip = {
                id: Date.now(),
                routeConfig: group.routeConfig, // Simpan config rute snapshot
                fleet: f,
                driver: d,
                passengers: passengers, // Only the selected ones (max 8)
                status: tripStatus,
                note: tripNote
            };

            if (this.view === 'dispatcher') {
                // Dispatcher specific updates if needed
            }
            
            if (this.view === 'manifest') {
                this.fetchReports();
            }

            this.isLoading = true;
            const res = await this.postToApi('create_trip', { data: newTrip });
            this.isLoading = false;

            if(res.status === 'success') {
                this.showToast('Trip Berhasil Diberangkatkan!', 'success');
                this.isDispatchModalVisible = false;
                this.loadData(); // Reload full data untuk update status booking & armada
            } else {
                Swal.fire('Gagal Dispatch', res.message, 'error');
            }
        },

        // --- DRAG & DROP DISPATCHER (KANBAN) ---
        onDragStart(evt, passenger, group) {
            evt.dataTransfer.effectAllowed = 'move';
            evt.dataTransfer.dropEffect = 'move';
            
            const payload = JSON.stringify({ 
                id: passenger.id, 
                routeId: group.routeId,
                passengerName: passenger.passengerName,
                originalTime: group.time
            });
            evt.dataTransfer.setData('text/plain', payload);
        },

        onDragEnter(evt) {
            evt.currentTarget.classList.add('ring-4', 'ring-blue-300', 'bg-blue-50');
        },

        onDragLeave(evt) {
            if (!evt.currentTarget.contains(evt.relatedTarget)) {
                evt.currentTarget.classList.remove('ring-4', 'ring-blue-300', 'bg-blue-50');
            }
        },

        async onDrop(evt, targetGroup) {
            evt.preventDefault(); 
            // Remove highlight if any
            const card = evt.currentTarget;
            card.classList.remove('ring-4', 'ring-blue-300', 'bg-blue-50');

            const dataStr = evt.dataTransfer.getData('text/plain');
            if(!dataStr) return;
            
            const src = JSON.parse(dataStr);
            
            // Constraint: Route must be same
            if (src.routeId !== targetGroup.routeId) {
                return Swal.fire('Gagal', 'Hanya bisa memindahkan jadwal di Rute yang sama!', 'error');
            }

            const passenger = this.bookings.find(b => b.id === src.id);
            if (!passenger) return;

            // Check if moving to SAME group (Exact same batch)
            if (targetGroup.passengers.find(p => p.id === src.id)) {
                return; // Already here
            }

            let clearSeat = false;
            let confirmMsg = `Geser penumpang "${src.passengerName}" ke pukul ${targetGroup.time}?`;
            let confirmTitle = 'Geser Jadwal?';

            // SAME TIME LOGIC
            if (src.originalTime === targetGroup.time && passenger.date === targetGroup.date) {
                confirmTitle = 'Gabung Armada?';
                confirmMsg = `Pindah ke Armada ${targetGroup.batchNumber || 'ini'}?`;
                
                // Check if target is Full
                const targetCount = targetGroup.passengers.reduce((sum, p) => sum + (parseInt(p.seatCount)||1), 0);
                const pCount = parseInt(passenger.seatCount) || 1;
                if (targetCount + pCount > 8) {
                    const result = await Swal.fire({
                        title: 'Armada Penuh',
                        text: `Armada ${targetGroup.batchNumber} Penuh (${targetCount}/8). Tetap pindah? (Mungkin akan terpisah kembali)`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Tetap Pindah'
                    });
                    if (!result.isConfirmed) return;
                }

                // Check Seat Conflict
                const pSeats = passenger.seatNumbers ? passenger.seatNumbers.split(',').map(s=>s.trim()) : [];
                if (pSeats.length > 0) {
                    const targetSeats = [];
                    targetGroup.passengers.forEach(p => {
                        if(p.seatNumbers) p.seatNumbers.split(',').forEach(s => targetSeats.push(s.trim()));
                    });
                    
                    const conflict = pSeats.some(s => targetSeats.includes(s));
                    if (conflict) {
                         const result = await Swal.fire({
                            title: 'Konflik Kursi',
                            html: `<div class="flex justify-center">
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=https://sutanraya.com" class="w-16 h-16 mix-blend-multiply opacity-80">
                            </div>Kursi ${passenger.seatNumbers} BENTROK di Armada tujuan. Hapus No Kursi untuk menggabungkan?`,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Ya, Hapus & Gabung',
                            cancelButtonText: 'Batal'
                        });
                        
                        if(!result.isConfirmed) return;
                        clearSeat = true;
                    }
                }
            } else {
                 const result = await Swal.fire({
                    title: confirmTitle,
                    text: confirmMsg,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Pindahkan',
                    cancelButtonText: 'Batal'
                });
                if (!result.isConfirmed) return;
            }

            // Execute Move
            this.moveBookingSchedule(src.id, targetGroup.date, targetGroup.time, clearSeat);
        },

        async moveBookingSchedule(id, date, time, clearSeat = false) {
            this.isLoading = true;
            const res = await this.postToApi('move_booking_schedule', { id, date, time, clear_seat: clearSeat });
            if (res.status === 'success') {
                // Reload to reflect changes
                this.loadData();
                this.showToast('Jadwal Berhasil Diubah', 'success');
            } else {
                this.isLoading = false;
                Swal.fire("Gagal", res.message, 'error');
            }
        },

        openTripControl(trip) {
            this.activeTripControl = trip;
            this.isTripControlVisible = true;
        },
        async startTrip() {
            if(!this.activeTripControl) return;
            const res = await Swal.fire({
                title: 'Mulai Perjalanan?',
                text: "Status trip akan berubah menjadi 'On Trip'",
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Jalan!',
                confirmButtonColor: '#3085d6'
            });
            
            if(!res.isConfirmed) return;

            // Logic update status
            this.updateTripStatus(this.activeTripControl.id, 'On Trip');
        },
        async finishTrip() {
             if(!this.activeTripControl) return;
             const res = await Swal.fire({
                title: 'Selesaikan Trip?',
                text: "Unit akan kembali menjadi 'Tersedia'.",
                icon: 'success',
                showCancelButton: true,
                confirmButtonText: 'Ya, Selesai!',
                confirmButtonColor: '#28a745'
            });
            
            if(!res.isConfirmed) return;
            
            this.updateTripStatus(this.activeTripControl.id, 'Tiba');
        },
        async cancelTrip() {
             if(!this.activeTripControl) return;
             const res = await Swal.fire({
                title: 'Batalkan Trip?',
                text: "PERINGATAN: Trip ini akan dihapus & penumpang kembali ke antrian Dispatcher!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Batalkan Trip',
                confirmButtonColor: '#d33'
            });
            
            if(!res.isConfirmed) return;

             this.updateTripStatus(this.activeTripControl.id, 'Batal');
        },
        async updateTripStatus(tripId, status) {
            this.isLoading = true;
            const res = await this.postToApi('update_trip_status', { id: tripId, status: status });
            this.isLoading = false;
            
            if(res.status === 'success') {
                this.isTripControlVisible = false;
                this.loadData();
                this.showToast('Status Trip Diperbarui');
            } else {
                Swal.fire('Gagal', res.message, 'error');
            }
        },

        // --- ASSETS (FLEET & DRIVER) ---
        openVehicleModal(f = null) {
            if (f) {
                this.vehicleModal = { mode: "edit", data: { ...f } };
            } else {
                this.vehicleModal = { mode: "add", data: { id: '', name: '', plate: '', capacity: 7, status: 'Tersedia', icon: 'bi-truck-front-fill' } };
            }
            this.isVehicleModalVisible = true;
        },
        async saveVehicle() {
            if(!this.vehicleModal.data.name || !this.vehicleModal.data.plate) return Swal.fire('Lengkapi Data', 'Nama dan Plat nomor wajib diisi', 'warning');
            
            const endpoint = this.vehicleModal.mode === 'add' ? 'create_fleet' : 'update_fleet';
            const actionText = this.vehicleModal.mode === 'add' ? 'Ditambahkan' : 'Diupdate';

            if(this.vehicleModal.mode === 'add') this.vehicleModal.data.id = Date.now();

            this.isLoading = true;
            const res = await this.postToApi(endpoint, { data: this.vehicleModal.data });
            this.isLoading = false;

            if(res.status === 'success') {
                this.isVehicleModalVisible = false;
                this.loadData();
                this.showToast(`Armada Berhasil ${actionText}`);
            } else {
                Swal.fire('Gagal', res.message, 'error');
            }
        },
        async deleteVehicle(id) {
            const res = await Swal.fire({
                title: 'Hapus Armada?',
                text: "Data armada ini akan dihapus permanen.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Hapus'
            });
            
            if(!res.isConfirmed) return;
            
            const apiRes = await this.postToApi('delete_fleet', { id });
            if(apiRes.status === 'success') {
                this.loadData();
                this.showToast('Armada Dihapus', 'success');
            }
        },

        openDriverModal(d = null) {
            if (d) {
                this.driverModal = { mode: "edit", data: { ...d } };
            } else {
                this.driverModal = { mode: "add", data: { id: '', name: '', phone: '', licenseType: 'A Umum', status: 'Standby' } };
            }
            this.isDriverModalVisible = true;
        },
        async saveDriver() {
            if(!this.driverModal.data.name) return Swal.fire('Nama Kosong', 'Nama supir wajib diisi', 'warning');

            const endpoint = this.driverModal.mode === 'add' ? 'create_driver' : 'update_driver';
             const actionText = this.driverModal.mode === 'add' ? 'Ditambahkan' : 'Diupdate';

            if(this.driverModal.mode === 'add') this.driverModal.data.id = Date.now();
            
            this.isLoading = true;
            const res = await this.postToApi(endpoint, { data: this.driverModal.data });
            this.isLoading = false;

            if(res.status === 'success') {
                this.isDriverModalVisible = false;
                this.loadData();
                this.showToast(`Supir Berhasil ${actionText}`);
            } else {
                Swal.fire('Gagal', res.message, 'error');
            }
        },
        async deleteDriver(id) {
             const res = await Swal.fire({
                title: 'Hapus Supir?',
                text: "Data supir ini akan dihapus permanen.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Hapus'
            });
            
            if(!res.isConfirmed) return;

            const apiRes = await this.postToApi('delete_driver', { id });
            if(apiRes.status === 'success') {
                this.loadData();
                this.showToast('Supir Dihapus', 'success');
            }
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
            if (!f.origin || !f.destination) return Swal.fire('Lengkapi Data', 'Asal dan Tujuan wajib diisi', 'warning');
            
            // Generate ID if new
            // Generate ID if new
            if (this.routeModal.mode === 'add') {
                const getCode = (name) => {
                    const n = name.toLowerCase();
                    if(n.includes('padang panjang')) return 'PDP';
                    if(n.includes('padang')) return 'PDG';
                    if(n.includes('bukittinggi')) return 'BKT';
                    if(n.includes('payakumbuh')) return 'PYK';
                    if(n.includes('pekanbaru')) return 'PKU';
                    if(n.includes('solok')) return 'SLK';
                    if(n.includes('sawahlunto')) return 'SWL';
                    if(n.includes('batusangkar')) return 'BSK';
                    if(n.includes('pariaman')) return 'PRM';
                    return name.substring(0,3).toUpperCase();
                };
                let newId = `${getCode(f.origin)}-${getCode(f.destination)}`;
                
                // Check for duplicate ID
                if (this.routeConfig.some(r => r.id === newId)) {
                    let counter = 2;
                    while (this.routeConfig.some(r => r.id === `${newId}-${counter}`)) {
                        counter++;
                    }
                    newId = `${newId}-${counter}`;
                }
                f.id = newId;
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
                this.showToast("Rute berhasil disimpan!");
                this.isRouteModalVisible = false; // Assuming this property exists
                this.loadData(); 
            } else {
                Swal.fire("Gagal", res.message, 'error');
            }
        },

        async deleteRoute(id) {
            if(!confirm("Yakin ingin menghapus rute ini?")) return;
            const res = await this.postToApi('delete_route', { id: id });
            if(res.status === 'success') {
                this.showToast("Rute berhasil dihapus!");
                this.loadData();
            } else {
                Swal.fire("Gagal", res.message, 'error');
            }
        },

        // --- SCHEDULE MANAGEMENT ---
        isChartered(fleetId, driverId, date) {
            // Check if fleet/driver is in a Charter trip on this date
            // Charter trips are in this.trips
            // We need to check passengers for 'Carter' service and duration
            
            return this.trips.find(t => {
                if (!t.passengers) return false;
                // Check if this trip uses the fleet/driver
                const sameFleet = t.fleet && t.fleet.id === fleetId;
                const sameDriver = t.driver && t.driver.id === driverId;
                
                if (!sameFleet && !sameDriver) return false;
                
                // Check if any passenger is Carter and date overlaps
                // Note: t.passengers is array of objects.
                // We need to check if ANY passenger implies a charter that covers 'date'.
                // Usually Carter is 1 passenger (the booker).
                
                return t.passengers.some(p => {
                    if (p.serviceType !== 'Carter' && p.serviceType !== 'Dropping') return false;
                    
                    const start = new Date(p.date);
                    const duration = parseInt(p.duration) || 1;
                    const end = new Date(start);
                    end.setDate(end.getDate() + duration - 1);
                    
                    const check = new Date(date);
                    return check >= start && check <= end;
                });
            });
        },

        // NEW: Get ALL assignments for a Route/Time (for Schedule Page)
        // REVERTED: getAssignments removed

        getAssignment(routeId, time, date = null) {
            if (!date) date = this.manifestDate;
            
            // 1. Check Specific Trip (Override)
            const specificTrip = this.trips.find(t => {
                const tDate = t.date || (t.passengers && t.passengers[0] ? t.passengers[0].date : null);
                // Simple equality check for strings/numbers
                return tDate === date && t.time === time && t.routeConfig?.id == routeId;
            });
            
            if (specificTrip) return { ...specificTrip, type: 'Specific' };
            
            // 2. Check Default Schedule
            const def = this.scheduleDefaults.find(d => d.routeId == routeId && d.time === time);
            if (def) {
                const f = this.fleet.find(f => f.id == def.fleetId);
                const d = this.drivers.find(d => d.id == def.driverId);

                if (!f || !d) return null;

                // Check Conflict
                const conflictTrip = this.isChartered(def.fleetId, def.driverId, date);
                if (conflictTrip) {
                    return { 
                        status: 'Conflict', 
                        fleet: f,
                        driver: d,
                        conflictWith: conflictTrip,
                        type: 'Default'
                    };
                }
                
                return {
                    status: 'Scheduled',
                    fleet: f,
                    driver: d,
                    type: 'Default'
                };
            }
            
            return null;
        },

        toggleFullscreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen();
                this.isFullscreen = true;
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                    this.isFullscreen = false;
                }
            }
        },
        
        openScheduleModal(route, time, assignment) {
            this.scheduleForm = {
                route: route,
                time: time,
                fleetId: assignment ? assignment.fleet?.id : '',
                driverId: assignment ? assignment.driver?.id : '',
                isDefault: assignment && assignment.type === 'Default'
            };
            this.isScheduleModalVisible = true;
        },
        
        async saveScheduleAssignment() {
            const { route, time, fleetId, driverId, isDefault } = this.scheduleForm;
            if (!fleetId || !driverId) return Swal.fire('Data Kurang', 'Pilih Armada dan Supir!', 'warning');
            
            if (isDefault) {
                // Check for conflict with other defaults (Same Time)
                const conflict = this.scheduleDefaults.find(d => 
                    (d.fleetId == fleetId || d.driverId == driverId) && 
                    d.time === time &&
                    d.routeId != route.id // Allow updating self
                );
                
                if (conflict) {
                    const conflictRoute = this.routeConfig.find(r => r.id == conflict.routeId);
                    const rName = conflictRoute ? `${conflictRoute.origin}-${conflictRoute.destination}` : conflict.routeId;
                    const result = await Swal.fire({
                        title: 'Konflik Default',
                        text: `Armada/Supir ini sudah menjadi default di rute ${rName} pada jam ${time}. Yakin ingin menimpa/menggunakan ganda?`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Lanjutkan'
                    });
                    if (!result.isConfirmed) return;
                }

                // Save as Default
                const res = await this.postToApi('save_schedule_default', {
                    routeId: route.id,
                    time: time,
                    fleetId: fleetId,
                    driverId: driverId
                });
                if(res.status === 'success') {
                    this.showToast("Jadwal Default Disimpan!");
                    this.isScheduleModalVisible = false;
                    this.loadData();
                } else {
                    Swal.fire("Gagal", res.message, 'error');
                }
            } else {
                // Save as Specific Trip (Override)
                const f = this.fleet.find(x => x.id === fleetId);
                const d = this.drivers.find(x => x.id === driverId);
                
                // Check if updating existing specific trip
                const existingTrip = this.getAssignment(route.id, time, this.manifestDate);
                const tripId = (existingTrip && existingTrip.type === 'Specific') ? existingTrip.id : Date.now();
                
                const tripData = {
                    id: tripId,
                    routeConfig: route,
                    fleet: f,
                    driver: d,
                    passengers: (existingTrip && existingTrip.type === 'Specific') ? existingTrip.passengers : [],
                    status: 'Scheduled',
                    date: this.manifestDate,
                    time: time
                };
                
                const res = await this.postToApi('save_trip', { data: tripData });
                
                if(res.status === 'success') {
                    this.showToast("Penugasan Harian Disimpan!");
                    this.isScheduleModalVisible = false;
                    this.loadData();
                } else {
                    Swal.fire("Gagal", res.message, 'error');
                }
            }
        },

        // --- TICKET & PDF ---
        getTicketData(booking) {
            let fleetName = 'Belum Ditentukan';
            let driverName = 'Belum Ditentukan';
            let plate = '-';
            let isDispatched = false;
            let needManualAssign = false;
            
            // 1. Check if Dispatched (Active Trip in Database)
            for (const trip of this.trips) {
                // Ensure ID comparison is safe (string vs number)
                if (trip.passengers && trip.passengers.some(p => p.id == booking.id)) {
                    fleetName = trip.fleet?.name || '-';
                    plate = trip.fleet?.plate || '-';
                    driverName = trip.driver?.name || '-';
                    isDispatched = true;
                    break;
                }
            }

            // 2. If NOT Dispatched, Check Schedule Assignment (Default/Daily Schedule)
            if (!isDispatched && booking.serviceType === 'Travel') {
                const assignment = this.getAssignment(booking.routeId, booking.time, booking.date);
                if (assignment && assignment.fleet && assignment.driver && assignment.status !== 'Conflict') {
                    fleetName = assignment.fleet.name;
                    plate = assignment.fleet.plate;
                    driverName = assignment.driver.name;
                    // We consider this "Dispatched" for printing purposes
                    isDispatched = true; 
                }
            }

            // 3. If Still NOT Dispatched, check Manual Assignment (LocalStorage/Session)
            if (!isDispatched) {
                const manual = this.manualAssignments[booking.id];
                if (manual) {
                    const f = this.fleet.find(x => x.id === manual.fleetId);
                    const d = this.drivers.find(x => x.id === manual.driverId);
                    if (f) { fleetName = f.name; plate = f.plate; }
                    if (d) { driverName = d.name; }
                } else {
                    needManualAssign = true;
                }
            }

            // Find Route Config
            const r = this.routeConfig.find(x => x.id === booking.routeId) || this.busRouteConfig.find(x => x.id === booking.routeId);

            // Calculate Total Price (Robust Fallback logic as per user request: Price x SeatCount)
            let seatCount = booking.seatCount || 1;
            if (booking.seatNumbers) {
                seatCount = booking.seatNumbers.split(',').length;
            }
            
            const rConfig = r || { origin: 'Asal', destination: 'Tujuan', prices: {umum:0, pelajar:0} };
            let unitPrice = rConfig.prices ? rConfig.prices.umum : 0;
            if (booking.passengerType === 'Pelajar' || booking.passengerType === 'Mahasiswa / Pelajar') {
                unitPrice = rConfig.prices ? rConfig.prices.pelajar : unitPrice;
            }
            
            // Prioritize existing totalPrice, but if it looks like it's just Unit Price (and we have multiple seats), recalculate.
            // User requested: "booking total, dengan cara harga x jumlah seat"
            let finalPrice = booking.totalPrice;
            
            // If total price is missing OR (it equals unit price AND seat count > 1), force calculation
            // We assume that if total price != unitPrice * seats, it might be a manual override, so we keep it unless it matches unit price exactly when it shouldn't.
            if (!finalPrice || (parseInt(finalPrice) === parseInt(unitPrice) && seatCount > 1)) {
                finalPrice = unitPrice * seatCount;
            }

            return {
                ...booking,
                fleetName,
                driverName,
                fleetPlate: plate,
                formattedDate: this.formatDate(booking.date),
                formattedPrice: this.formatRupiah(finalPrice),
                routeConfig: rConfig,
                isDispatched: isDispatched, // Used to toggle "Armada" section in receipt
                needManualAssign: needManualAssign
            };
        },

        viewTicket(booking) {
            const data = this.getTicketData(booking);
            
            if (data.needManualAssign) {
                // 3. If No Manual Assignment, Open Assignment Modal FIRST
                this.openManualAssign(booking);
                return;
            }

            this.ticketData = data;
            this.isTicketModalVisible = true;
        },

        openManualAssign(booking) {
            this.manualAssignForm = {
                bookingId: booking.id,
                fleetId: '',
                driverId: ''
            };
            this.isManualAssignModalVisible = true;
        },

        saveManualAssign() {
            if (!this.manualAssignForm.fleetId || !this.manualAssignForm.driverId) {
                return alert("Pilih Armada dan Supir!");
            }
            this.manualAssignments[this.manualAssignForm.bookingId] = {
                fleetId: this.manualAssignForm.fleetId,
                driverId: this.manualAssignForm.driverId
            };
            this.isManualAssignModalVisible = false;
            
            // Re-open ticket with new data
            const booking = this.bookings.find(b => b.id === this.manualAssignForm.bookingId);
            if (booking) this.viewTicket(booking);
        },

        printTicket(booking) {
            // Check if we have data (it might be passed from viewTicket button OR from table button)
            // If passed from table button, it's a raw booking object.
            // If passed from modal button, it's already ticketData (has fleetName etc)
            
            let data = booking;
            
            // Determine if it's a raw booking or processed ticketData
            if (!booking.fleetName && !booking.isDispatched) {
                // It's likely raw booking (or unassigned ticketData), lets re-process to be sure
                const processed = this.getTicketData(booking);
                if (processed.needManualAssign) {
                    // Not assigned yet -> Go to manual assign flow
                    this.viewTicket(booking);
                    return;
                }
                data = processed;
            }
            
            // Update ticketData state so the hidden template updates
            this.ticketData = data;

            // Wait for Vue DOM update
            this.$nextTick(() => {
                // Panggil fungsi dari js/ticket_printer.js
                // Use 'ticketTemplate' if we want silent print, or 'ticketContent' if modal is open?
                // For "Auto Print" from table, modal is closed, so we MUST use 'ticketTemplate'.
                // For "Print" from modal, modal is open, we can use 'ticketContent' OR 'ticketTemplate'.
                // 'ticketTemplate' is safer as it's always there.
                
                if (typeof generateTicketPDF === 'function') {
                    // Cek apakah modal terbuka? Jika ya, cetak dari modal (WYSIWYG)
                    // Jika tidak, cetak dari hidden template (Auto Print)
                    const sourceId = this.isTicketModalVisible ? 'ticketContent' : 'ticketTemplate';
                    
                    console.log("Printing from source:", sourceId);

                    generateTicketPDF(sourceId, `Ticket-${data.id}.pdf`);
                } else {
                    console.error("generateTicketPDF function not found");
                    alert("Fungsi cetak tiket tidak tersedia.");
                }
            });
        },

        // --- UTILS ---
        copyWa(p) {
            const type = p.serviceType === 'Dropping' ? 'CHARTER' : 'TRAVEL';
            const txt = `*SUTAN RAYA - ${type}*\nJadwal: ${p.time}\nNama: ${p.passengerName}\nKursi: ${p.seatNumbers}\nHP: ${p.passengerPhone}\nJemput: ${p.pickupMapUrl||'-'} (${p.pickupAddress||'-'})\nAntar: ${p.dropoffAddress||'-'}`;
            navigator.clipboard.writeText(txt).then(() => alert("Data disalin ke Clipboard!"));
        },
        formatRupiah(n) { return new Intl.NumberFormat('id-ID', {style:'currency', currency:'IDR', minimumFractionDigits:0}).format(n||0); },
        formatDate(d) { if(!d) return '-'; const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }; return new Date(d).toLocaleDateString('id-ID', options); },
        getWaLink(phone) {
            if (!phone) return '#';
            let p = phone.toString().replace(/\D/g, ''); // Remove non-digits
            if (p.startsWith('0')) {
                p = '62' + p.substring(1);
            }
            return `https://wa.me/${p}`;
        },

        async fetchReports() {
            try {
                const res = await fetch(`api.php?action=get_reports&period=${this.period}`);
                const d = await res.json();
                if(d.reports) {
                    this.reportData = d.reports;
                }
            } catch (e) {
                console.error("Error fetching reports:", e);
            }
        },

        /* async fetchStaff() {
            try {
                const res = await fetch('api.php?action=get_users');
                const d = await res.json();
                if (d.users) {
                    this.staffList = d.users;
                }
            } catch (e) {
                console.error("Error fetching staff:", e);
            }
        }, */

        async loadData(silent = false) {
            if (!silent) this.isLoading = true;
            try {
                const res = await fetch('api.php?action=get_initial_data');
                const data = await res.json();
                
                this.bookings = data.bookings || [];
                this.fleet = data.fleet || [];
                this.drivers = data.drivers || [];
                this.trips = data.trips || [];
                this.routeConfig = data.routes || [];
                this.scheduleDefaults = data.scheduleDefaults || [];
                
                // Also fetch reports if we are in manifest view
                if (this.view === 'manifest') {
                    this.fetchReports();
                }

                // Update sidebar counts after data load
                this.updateSidebarCounts();

                // Fetch Staff for dropwdowns
                // this.fetchStaff();
            } catch (e) {
                console.error("Error loading data", e);
            } finally {
                this.isLoading = false;
            }
        },

        updateSidebarCounts() {
            const elValidation = document.getElementById('pendingValidationCount');
            const elDispatch = document.getElementById('pendingDispatchCount');
            
            if (elValidation) elValidation.innerText = this.pendingValidationCount;
            if (elDispatch) elDispatch.innerText = this.pendingDispatchCount;
        },
        
        getTripPassengerCount(trip) {
            if (!trip.passengers) return 0;
            let passengers = [];
            if (Array.isArray(trip.passengers)) {
                passengers = trip.passengers;
            } else if (typeof trip.passengers === 'object') {
                passengers = Object.values(trip.passengers);
            }
            return passengers.reduce((total, p) => total + (parseInt(p.seatCount) || 1), 0);
        },

        updateTime() { const n=new Date(); this.currentTime=n.toLocaleTimeString('id-ID',{hour:'2-digit',minute:'2-digit'}); this.currentDate=n.toLocaleDateString('id-ID',{weekday:'long',day:'numeric',month:'long'}); },
        
        // CSS Helpers
        getVehicleStatusClass(s) { return s==='Tersedia'?'bg-green-100 text-green-700':(s==='On Trip'?'bg-blue-100 text-blue-700':(s==='Perbaikan'?'bg-red-100 text-red-700':'bg-gray-100')); },
        getDriverStatusClass(s) { return s==='Standby'?'bg-green-100 text-green-700':(s==='Jalan'?'bg-blue-100 text-blue-700':'bg-gray-200'); },
        getTripCardClass(s) { if(s==='On Trip') return 'border-blue-200 bg-blue-50/30'; if(s==='Tiba') return 'border-green-200 bg-green-50/30'; if(s==='Kendala') return 'border-red-200 bg-red-50/30'; return 'border-gray-200'; },
        getTripStatusBadge(s) { if(s==='On Trip') return 'bg-blue-500'; if(s==='Tiba') return 'bg-green-500'; if(s==='Kendala') return 'bg-red-500'; return 'bg-gray-400'; },
        
        // --- PRINT TICKET ---

    }
}).mount("#app");