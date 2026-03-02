// FILE: app.js
// Display v10 - Frontend Logic
const { createApp } = Vue;

createApp({
    data() {
        return {
            // -- State UI --
            view: 'dashboard',
            isDarkMode: false,
            isFullscreen: false,
            isLoading: false,
            currentTime: "",
            currentDate: "",
            
            // Security
            isLocked: true,
            accessCode: '',
            username: '',

            user: null, // Logged in user data

            
            // -- State Modals --
            isProofModalVisible: false,
            isDispatchModalVisible: false,
            isVehicleModalVisible: false,
            isDriverModalVisible: false,
            isTripControlVisible: false,
            isTicketModalVisible: false,
            isRouteModalVisible: false,
            isMoveModalVisible: false,
            moveModalData: {
                passengerId: null,
                passengerName: null,
                routeId: null,
                currentDate: null,
                currentTime: null,
                seatNumbers: null, // Selected seat(s)
                
                targetTime: null,
                targetBatchIndex: 0,
                
                availableSchedules: [],
                allBatchesForTime: [] // Pre-calculated batches for target time
            },
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
            bookingBusForm: { type: 'Medium', routeId: '', seatCapacity: 33, duration: 1, date: '', passengerName: '', passengerPhone: '', totalPrice: 0, priceType: 'Kantor', packageType: 'Unit', paymentMethod: 'Cash', paymentLocation: '', paymentReceiver: '', paymentProof: '', downPaymentAmount: 0 },
            
            // Pagination
            currentPage: 1,
            itemsPerPage: 10,
            
            bookingManagementTab: 'travel',
            
            // Filters
            busSearchTerm: '',
            filterMethod: 'All',
            filterSort: 'Newest',
            filterDate: '',
            filterRoute: 'All',
            filterCategory: '',
            busViewMode: 'list',
            
            currentPaymentMethod: 'Cash',
            tempPayment: { loc: '', recv: '', proof: '', dpAmount: 0, dpMethod: 'Cash' },
            dispatchForm: { group: null, fleetId: "", driverId: "" },
            
            // Detail Modal State
            isDetailModalVisible: false,
            detailModalData: null,
            
            // Validation Modal State
            isValidationModalVisible: false,

            vehicleModal: { mode: "add", data: null },
            driverModal: { mode: "add", data: null },
            routeModal: { mode: 'add' },
            routeForm: { id: '', origin: '', destination: '', schedulesInput: '', prices: { umum: 0, pelajar: 0, dropping: 0, carter: 0 } },

            // Input Memory
            savedReceivers: JSON.parse(localStorage.getItem('sr_receivers') || '[]'),
            savedLocations: JSON.parse(localStorage.getItem('sr_locations') || '[]'),
            
            // UI State
            isDragging: false,

            // -- Data from Server --
            bookings: [],
            fleet: [],
            drivers: [],
            trips: [], // Trips database (Manifest)
            routeConfig: [],
            scheduleDefaults: [],
            reportData: { period: 'today', revenue: 0, passengers: 0, chart: null },
            
            // New Bus & Route Config
            busBookings: [],
            busRouteConfig: [],
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
            
            // -- Package Features --
            packages: [],
            packageForm: {
                senderName: '',
                senderPhone: '',
                receiverName: '',
                receiverPhone: '',
                itemDescription: '',
                itemType: 'Surat / Dokumen',
                category: 'Pool to Pool',
                route: 'Padang - Bukittinggi',
                price: 30000,
                paymentMethod: 'Cash',
                paymentStatus: 'Lunas',
                status: 'Pending',
                pickupAddress: '',
                dropoffAddress: '',
                mapLink: '',
                bookingDate: new Date().toISOString().slice(0, 10),
                adminName: ''
            },
            packageView: 'info', // 'info' or 'booking' or 'history'
            

            // -- Package UI State --
            activePackage: {},
            activePackageLogs: [],
            isTrackingModalVisible: false,
            isStatusModalVisible: false,
            statusForm: {
                id: '',
                status: '',
                location: '',
                description: '',
                adminName: ''
            },
            packageSearch: '', // Added Search Term

            
            // -- Dispatcher Filter --
            dispatcherRouteFilter: 'All', // 'All' or specific routeName
            
            // -- Payment Management --
            paymentHistory: [],
            isAddPaymentModalVisible: false,
            activePaymentBooking: null,
            addPaymentForm: {
                amount: 0,
                payment_method: 'Cash',
                payment_location: '',
                payment_receiver: '',
                notes: ''
            },
        };
    },
    created() {
        this.checkSession(); // Cek sesi saat startup
        this.loadData();

        // Load Filters
        this.busSearchTerm = localStorage.getItem('sr_filter_search') || '';
        this.filterMethod = localStorage.getItem('sr_filter_method') || 'All';
        this.filterDate = localStorage.getItem('sr_filter_date') || '';
        this.filterRoute = localStorage.getItem('sr_filter_route') || 'All';
        this.filterSort = 'Newest'; // Force Default Newest

        // Auto-refresh setiap 30 detik agar data selalu update tanpa reload
        setInterval(() => { 
            this.loadData(true); 
            if (this.view === 'packageShipping') this.loadPackages();
        }, 30000);
        
        this.updateTime(); 
        setInterval(this.updateTime, 1000);
        
        const path = window.location.pathname;
        if (path.includes('booking_management.php')) this.view = 'bookingManagement';
        else if (path.includes('schedule.php')) this.view = 'schedule';
        else if (path.includes('manifest.php')) { this.view = 'manifest'; this.fetchReports(); }
        else if (path.includes('assets.php')) this.view = 'assets';
        else if (path.includes('route_management.php')) this.view = 'routeManagement';
        else if (path.includes('package_shipping.php') || path.includes('paket.php')) { this.view = 'packageShipping'; this.loadPackages(); }
        else if (path.includes('display-v11/dispatcher.php') || window.initialView === 'dispatcher') this.view = 'dispatcher';
    },
    watch: {
        view(val) { localStorage.setItem('sutan_v10_view', val); },
        // Persist Filters
        busSearchTerm(val) { localStorage.setItem('sr_filter_search', val); this.currentPage = 1; },
        filterMethod(val) { localStorage.setItem('sr_filter_method', val); this.currentPage = 1; },
        filterDate(val) { localStorage.setItem('sr_filter_date', val); this.currentPage = 1; },
        filterRoute(val) { localStorage.setItem('sr_filter_route', val); this.currentPage = 1; },
        manifestDate(val) { this.loadData(); },
    },
    computed: {
        filteredPackages() {
            if (!this.packageSearch) return this.packages || [];
            const q = this.packageSearch.toLowerCase();
            return (this.packages || []).filter(p => 
                (p.receiptNumber && p.receiptNumber.toLowerCase().includes(q)) ||
                (p.senderName && p.senderName.toLowerCase().includes(q)) ||
                (p.receiverName && p.receiverName.toLowerCase().includes(q)) ||
                (p.senderPhone && p.senderPhone.includes(q))
            );
        },
        currentViewTitle() { return {dashboard:"Dashboard",bookingManagement:"Kelola Booking",dispatcher:"Dispatcher",bookingTravel:"Travel",bookingBus:"Bus",packageShipping:"Kirim Paket",manifest:"Laporan",assets:"Aset",routeManagement:"Rute",schedule:"Jadwal"}[this.view] || "Sutan Raya"; },
        todayRevenue() { return this.bookings.filter(b => b.date === new Date().toISOString().slice(0,10) && b.status !== 'Batal').reduce((a,b) => a + (b.totalPrice||0), 0); },
        todayPax() { return this.bookings.filter(b => b.date === new Date().toISOString().slice(0,10) && b.status !== 'Batal').length; },
        pendingValidationCount() { return this.bookings.filter(b => b.validationStatus === 'Menunggu Validasi').length; },
        activeTrips() { 
            return this.trips.filter(t => {
                if (['Tiba', 'Batal'].includes(t.status)) return false;
                // Check for empty trips (0 passengers)
                const pCount = this.getTripPassengerCount(t);
                return pCount > 0;
            }); 
        },
        pendingGroupsCount() { return this.groupedBookings.length; },
        pendingDispatchCount() { return this.groupedBookings.length; },
        
        // Pagination Helpers
        totalPages() {
            return Math.ceil(this.getFilteredBookings.length / this.itemsPerPage);
        },
        
        paginatedBookings() {
            const start = (this.currentPage - 1) * this.itemsPerPage;
            const end = start + this.itemsPerPage;
            return this.getFilteredBookings.slice(start, end);
        },
        
        // --- DATA COMPUTED ---

        // Group bookings by Route -> Time -> Fleet Cap (Batching)
        groupedBookings() {
            // Only Pending Bookings that are travel (not cancelled)
            // Also exclude bookings that are already "On Trip" or "Arrived"/History
            // Check status: 'Pending' or 'Confirmed' are waiting
            // 'On Trip', 'Tiba', 'Kendala', 'Batal', 'Cancelled' are usually not in queue
            
            // Also filter by date? Usually dispatch view shows today & future
            // Let's show all Pending bookings sorted by Date ASC
            
            const pending = this.bookings.filter(b => 
                (b.serviceType === 'Travel' || b.serviceType === 'Carter' || b.serviceType === 'Wisata' || b.serviceType === 'Dropping' || (b.serviceType && b.serviceType.toLowerCase().includes('drop'))) && 
                (b.status === 'Pending' || b.status === 'Confirmed' || b.status === 'Paid' || b.status === 'On Trip') &&
                b.date && b.status !== 'Batal' && b.status !== 'Tiba'
            );
            
            // Group Key: RouteId + Date + Time
            const groups = {};
            
            pending.forEach(b => {
                const key = `${b.routeId}_${b.date}_${b.time}`;
                if (!groups[key]) {
                    // Find route config
                    const route = this.routeConfig.find(r => r.id === b.routeId) || { id: b.routeId, origin: '?', destination: '?' };
                    
                    // Check for assignments in database (Trips) or local manual assignments
                    // Try to find existing Trip record for this batch
                    // Trip ID convention needed? Or search by route/date/time?
                    // Search in trips array
                    
                    // Logic: Find a valid trip in `trips` that matches: routeConfig matches, date, time, status != 'Tiba'/'Cancelled'
                    // For now, simplify: we just group bookings. Assignment is done via Dispatch.
                    // If a trip is already created ("On Trip"), the bookings should have status "On Trip", so they won't appear here (Pending).
                    
                    groups[key] = {
                        key: key,
                        routeId: b.routeId,
                        date: b.date,
                        time: b.time,
                        routeConfig: route,
                        passengers: [],
                        totalPassengers: 0,
                        assignment: null
                    };
                }
                groups[key].passengers.push(b);
                groups[key].totalPassengers += (parseInt(b.seatCount) || 1);
            });

            // Turn into array
            let batches = Object.values(groups);

            // BATCHING LOGIC per Fleet Capacity
            // 1. Get capacity of default fleet for this route-time?
            //    Or just assume standard capacity (e.g. 10) or user splits manually?
            //    For automation: split passengers into chunks of ~12 (Hiace) or ~7 (Innova)
            //    Let's use a "Soft Split": if > 14 passengers, split into Batch 1, Batch 2...
            
            // To do this properly, we iterate existing batches and if one is too big, split it.
            // Simplified: Just use one big group per schedule. Dispatcher manually selects fleet (1, 2, 3...)
            // BUT: The UI displays "Batch" cards.
            // Let's implement dynamic batching based on Seat Numbers if possible, or just simplistic Max 12.
            
            // Sort by Date then Time then ID (FIFO)
            batches.sort((a, b) => {
                if (a.date !== b.date) return new Date(a.date) - new Date(b.date);
                if (a.time !== b.time) return a.time.localeCompare(b.time);
                return 0;
            });

            const finalBatches = [];

            batches.forEach(group => {
                // Sort passengers in the group by ID (Creation Time) - FIFO
                group.passengers.sort((a, b) => parseFloat(a.id) - parseFloat(b.id));

                // Normalization Helper
                const normTime = (t) => t ? String(t).trim().substring(0, 5) : '';
                const gTime = normTime(group.time);

                // 1. Find ALL Specific Trips (Scheduled) for this slot
                const specificTrips = this.trips.filter(t => 
                    t.routeConfig && 
                    String(t.routeConfig.id).trim() === String(group.routeId).trim() && 
                    String(t.date).trim() === String(group.date).trim() && 
                    normTime(t.time) === gTime &&
                    t.status === 'Scheduled'
                );
                
                // Sort by ID to ensure consistent Batch 1 vs Batch 2 mapping
                specificTrips.sort((a,b) => parseFloat(a.id) - parseFloat(b.id));

                // 2. Prepare Default Assignment (fallback for Batch 1)
                let defaultAssignment = null;
                const def = this.scheduleDefaults.find(s => 
                    String(s.routeId).trim() === String(group.routeId).trim() && 
                    normTime(s.time) === gTime
                );
                
                if (def) {
                        const f = this.fleet.find(x => String(x.id) === String(def.fleetId));
                        const d = this.drivers.find(x => String(x.id) === String(def.driverId));
                        if(f && d) defaultAssignment = { fleet: f, driver: d, type: 'Default' };
                }

                // BATCHING LOGIC: Respect batchNumber from DB, then apply Seat Collision & Capacity
                const splits = [];
                
                // Step 1: Group passengers by their explicit batchNumber (if set)
                const batchMap = {}; // { batchNumber: [passengers] }
                const unbatched = []; // Passengers without explicit batchNumber
                
                group.passengers.forEach(b => {
                    const bn = parseInt(b.batchNumber) || 0;
                    if (bn > 0) {
                        if (!batchMap[bn]) batchMap[bn] = [];
                        batchMap[bn].push(b);
                    } else {
                        unbatched.push(b);
                    }
                });
                
                // Step 2: Create batch objects for explicitly assigned passengers
                const batchNumbers = Object.keys(batchMap).map(n => parseInt(n)).sort((a,b) => a - b);
                let nextBatchNum = batchNumbers.length > 0 ? Math.max(...batchNumbers) + 1 : 1;
                
                batchNumbers.forEach(bn => {
                    // Assign Trip based on Batch Index (Sequential)
                    let bi = bn - 1; // Batch 1 -> Index 0
                    let assignedTrip = null;
                    
                    if (specificTrips[bi]) {
                        assignedTrip = { 
                            fleet: specificTrips[bi].fleet, 
                            driver: specificTrips[bi].driver,
                            type: 'Specific',
                            tripId: specificTrips[bi].id
                        };
                    } else if (bn === 1 && defaultAssignment) {
                        assignedTrip = defaultAssignment;
                    }

                    // Auto-fill Passenger Data if Group has Assignment (Visual Validity)
                    if (assignedTrip) {
                        batchMap[bn].forEach(p => {
                            if (!p.fleetName && assignedTrip.fleet) {
                                p.fleetName = assignedTrip.fleet.name;
                                p.fleetId = assignedTrip.fleet.id;
                            }
                            if (!p.driverName && assignedTrip.driver) {
                                p.driverName = assignedTrip.driver.name;
                                p.driverId = assignedTrip.driver.id;
                            }
                            // Relax validation: If assignedTrip exists, treat On Trip as valid contextually
                            p._isValidContext = true; 
                        });
                    }

                    const batch = { 
                        ...group, 
                        passengers: batchMap[bn], 
                        totalPassengers: batchMap[bn].reduce((sum, p) => sum + (parseInt(p.seatCount) || 1), 0), 
                        key: group.key + '_' + bn,
                        batchNumber: bn,
                        assignment: assignedTrip
                    };
                    splits.push(batch);
                });
                
                // Step 3: Distribute unbatched passengers using collision logic
                if (unbatched.length > 0) {
                    // Get or create Batch 1 for unbatched
                    let currentBatch = splits.find(s => s.batchNumber === 1);
                    if (!currentBatch) {
                        currentBatch = { ...group, passengers: [], totalPassengers: 0, key: group.key + '_1', batchNumber: 1, assignment: group.assignment };
                        splits.push(currentBatch);
                        nextBatchNum = Math.max(nextBatchNum, 2);
                    }
                    
                    // Track occupied seats per batch
                    const occupiedPerBatch = {};
                    splits.forEach(s => {
                        occupiedPerBatch[s.batchNumber] = new Set();
                        s.passengers.forEach(p => {
                            if (p.seatNumbers) p.seatNumbers.split(',').forEach(seat => occupiedPerBatch[s.batchNumber].add(seat.trim()));
                        });
                    });
                    
                    unbatched.forEach(b => {
                        const pCount = parseInt(b.seatCount) || 1;
                        const pSeats = b.seatNumbers ? b.seatNumbers.split(',').map(s => s.trim()) : [];
                        
                        // Find a batch where this passenger fits (no collision, under capacity)
                        let targetBatch = null;
                        for (let batch of splits) {
                            const occupied = occupiedPerBatch[batch.batchNumber] || new Set();
                            const hasCollision = pSeats.some(s => occupied.has(s));
                            const overCapacity = batch.totalPassengers + pCount > 14;
                            
                            if (!hasCollision && !overCapacity) {
                                targetBatch = batch;
                                break;
                            }
                        }
                        
                        if (!targetBatch) {
                            // Create new batch
                            targetBatch = { 
                                ...group, 
                                passengers: [], 
                                totalPassengers: 0, 
                                key: group.key + '_' + nextBatchNum,
                                batchNumber: nextBatchNum,
                                assignment: null
                            };
                            splits.push(targetBatch);
                            occupiedPerBatch[nextBatchNum] = new Set();
                            nextBatchNum++;
                        }
                        
                        targetBatch.passengers.push(b);
                        targetBatch.totalPassengers += pCount;
                        pSeats.forEach(s => occupiedPerBatch[targetBatch.batchNumber].add(s));
                    });
                }
                
                // FINAL CHECK: Ensure Batch 1 exists if Default Assignment is available
                // (Fixes issue where Armada 2 exists but Armada 1 (Scheduled/Default) is hidden because empty)
                if (defaultAssignment && !splits.find(s => s.batchNumber === 1)) {
                    splits.push({
                        ...group,
                        passengers: [],
                        totalPassengers: 0,
                        key: group.key + '_1',
                        batchNumber: 1,
                        assignment: defaultAssignment
                    });
                    // Re-sort splits to ensure Batch 1 is first
                    splits.sort((a,b) => a.batchNumber - b.batchNumber);
                }

                
                // Sort splits by batchNumber
                splits.sort((a, b) => a.batchNumber - b.batchNumber);
                
                splits.forEach(s => finalBatches.push(s));
            });
            
            batches = finalBatches;

            return batches;
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

        // NEW: List of Routes for Sidebar
        dispatcherRoutesList() {
            // Extract all route names from groupedDispatcherViews + 'All'
            const routes = Object.keys(this.groupedDispatcherViews);
            // Also include routes from 'activeTrips' if they are not in pending?
            // For completeness, let's scan activeTrips too.
            this.activeTrips.forEach(trip => {
                const name = trip.routeConfig ? trip.routeConfig.name || `${trip.routeConfig.origin} - ${trip.routeConfig.destination}` : 'Lainnya';
                if (!routes.includes(name)) routes.push(name);
            });
            return routes.sort();
        },

        // NEW: Filtered Views
        filteredDispatcherViews() {
            const allGroups = this.groupedDispatcherViews;
            if (this.dispatcherRouteFilter === 'All') {
                return allGroups;
            }
            // Return object with only the selected key
            const filtered = {};
            if (allGroups[this.dispatcherRouteFilter]) {
                filtered[this.dispatcherRouteFilter] = allGroups[this.dispatcherRouteFilter];
            }
            return filtered;
        },

        // NEW: Filtered Active Trips
        filteredActiveTrips() {
            if (this.dispatcherRouteFilter === 'All') return this.activeTrips;
            
            return this.activeTrips.filter(trip => {
                const name = trip.routeConfig ? trip.routeConfig.name || `${trip.routeConfig.origin} - ${trip.routeConfig.destination}` : 'Lainnya';
                return name === this.dispatcherRouteFilter;
            });
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

        getFilteredBookings() {
            let items = this.view==='bookingManagement' && this.bookingManagementTab==='bus' 
                ? this.bookings.filter(b=>b.serviceType==='Bus Pariwisata') 
                : this.bookings.filter(b=>['Travel','Carter','Dropping'].includes(b.serviceType));
            
            // Filter: Search
            if(this.busSearchTerm) {
                const term = this.busSearchTerm.toLowerCase();
                items = items.filter(b => 
                    (b.passengerName?.toLowerCase().includes(term)) || 
                    (b.passengerPhone?.includes(term)) ||
                    (b.id?.toString().includes(term)) || // Search by ID
                    (b.routeId?.toLowerCase().includes(term)) || // Search by Route
                    (b.seatNumbers?.toString().includes(term)) || // Search by Seat
                    (b.pickupAddress?.toLowerCase().includes(term)) || // Search by Pickup
                    (b.dropoffAddress?.toLowerCase().includes(term)) // Search by Dropoff
                );
            }

            // Filter: Category
            if(this.filterCategory) {
                items = items.filter(b => b.passengerType === this.filterCategory);
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
        getManagedBookings() {
            return this.paginatedBookings;
        },
        // Pagination Computed
        totalPages() {
            return Math.ceil(this.getFilteredBookings.length / this.itemsPerPage);
        },
        paginatedBookings() {
            // Ensure getFilteredBookings returns array
            const items = this.getFilteredBookings || [];
            const start = (this.currentPage - 1) * this.itemsPerPage;
            const end = start + this.itemsPerPage;
            return items.slice(start, end);
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

        outboundTrips() {
            // Trips FROM Padang (Origin contains 'Padang' or 'PDG')
            return this.activeTrips.filter(t => {
                const origin = t.routeConfig?.origin?.toLowerCase() || '';
                const id = t.routeConfig?.id?.toLowerCase() || '';
                return (origin.includes('padang') || id.startsWith('pdg')) && !origin.includes('payakumbuh') && !origin.includes('bukittinggi');
            }).sort((a,b) => (a.routeConfig?.time || '').localeCompare(b.routeConfig?.time || ''));
        },

        inboundTrips() {
            // Trips TO Padang (Destination contains 'Padang')
            return this.activeTrips.filter(t => {
                const dest = t.routeConfig?.destination?.toLowerCase() || '';
                const id = t.routeConfig?.id?.toLowerCase() || '';
                // Or checking if ID ends with PDG?
                // Logic based on route IDs: PDG-BKT (Out), BKT-PDG (In)
                return (dest.includes('padang') || id.includes('pdg')) && !id.startsWith('pdg');
            }).sort((a,b) => (a.routeConfig?.time || '').localeCompare(b.routeConfig?.time || ''));
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
        async checkSession() {
            try {
                const res = await fetch('api.php?action=check_session');
                const data = await res.json();
                if (data.status === 'success') {
                    this.user = data.user;
                    this.isLocked = false;
                } else {
                    this.isLocked = true;
                }
            } catch (e) {
                console.error("Session Check Failed", e);
                this.isLocked = true;
            }
        },

        async unlockPage() {
            if (!this.username || !this.accessCode) return Swal.fire('Error', 'Masukkan Username dan Password', 'warning');
            
            this.isLoading = true;
            const res = await this.postToApi('login', { username: this.username, password: this.accessCode });
            this.isLoading = false;
            
            if (res.status === 'success') {
                this.isLocked = false;
                this.user = res.user;
                this.username = '';
                this.accessCode = ''; 
                this.showToast(`Selamat Datang, ${this.user.name}`);
            } else {
                Swal.fire('Akses Ditolak', 'Username atau Password salah!', 'error');
                this.accessCode = '';
            }
        },
        
        async logout() {
            await this.postToApi('logout', {});
            this.user = null;
            this.isLocked = true;
            this.showToast('Logout Berhasil', 'info');
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
        parseAddress(str) {
            if (!str) return { text: '-', link: '' };
            const parts = str.split(',');
            if (parts.length > 1) {
                const lastPart = parts[parts.length - 1].trim();
                // Check if last part looks like a URL
                if (lastPart.startsWith('http') || lastPart.includes('maps.app.goo.gl') || lastPart.includes('google.com/maps')) {
                    const link = lastPart;
                    const text = parts.slice(0, -1).join(',').trim();
                    return { text, link };
                }
            }
            return { text: str.trim(), link: '' };
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
                routeManagement: 'route_management.php',
                packageShipping: 'package_shipping.php'
            };
            if (map[v]) {
                window.location.href = map[v];
            } else {
                this.view = v; 
            }
        },
        toggleDarkMode() {
            this.isDarkMode = !this.isDarkMode;
            if (this.isDarkMode) document.documentElement.classList.add('dark');
            else document.documentElement.classList.remove('dark');
            localStorage.setItem('sutan_v10_dark', this.isDarkMode);
        },

        toggleFullscreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch((e) => console.log(e));
                this.isFullscreen = true;
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                    this.isFullscreen = false;
                }
            }
        },
        
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
            reader.onload = (e) => {
                this.tempPayment.proof = e.target.result;
                // document.getElementById('proofLabel').innerHTML = '<i class="bi bi-check-circle-fill text-green-500 text-lg block mb-1"></i>File Terupload';
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

        viewKtm(booking) {
            this.activeKtmImage = booking.ktmProof;
            this.activeBookingName = booking.passengerName;
            this.isKtmModalVisible = true;
        },
        // Alias for compatibility with template
        validatePaymentModal(b) { this.openValidationModal(b); },
        openValidationModal(b) { this.validationData = b; this.isValidationModalVisible = true; },
        closeValidationModal() { this.isValidationModalVisible = false; this.validationData = null; },
        
        async confirmValidation() {
            const b = this.validationData;
            if (!b) return;

            const result = await Swal.fire({
                title: 'Validasi Pembayaran?',
                text: "Status akan diubah menjadi LUNAS dan VALID.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Validasi',
                cancelButtonText: 'Batal'
            });

            if(!result.isConfirmed) return;
            
            const res = await this.postToApi('validate_booking', { 
                id: b.id 
            });
            
            if(res.status === 'success') {
                b.paymentStatus = 'Lunas';
                b.validationStatus = 'Valid';
                b.status = 'Confirmed';
                
                // Update in main list
                const idx = this.bookings.findIndex(x => x.id === b.id);
                if(idx !== -1) {
                    this.bookings[idx].paymentStatus = 'Lunas';
                    this.bookings[idx].validationStatus = 'Valid';
                    this.bookings[idx].status = 'Confirmed';
                }

                this.isValidationModalVisible = false;
                this.showToast('Pembayaran Valid!');
            } else {
                Swal.fire('Gagal Validasi', res.message || 'Terjadi kesalahan sistem', 'error');
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
                // Close detail modal if open and same booking
                if (this.isDetailModalVisible && this.detailModalData.id === b.id) {
                    this.isDetailModalVisible = false;
                }
            }
        },

        openDetailModal(b) {
            this.detailModalData = b;
            this.isDetailModalVisible = true;
            this.loadPaymentHistory(b.id); // Auto-load payment history
        },
        closeDetailModal() {
            this.isDetailModalVisible = false;
            this.detailModalData = null;
            this.paymentHistory = [];
        },
        
        // ===== PAYMENT MANAGEMENT METHODS =====
        async loadPaymentHistory(bookingId) {
            try {
                const res = await fetch('api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'get_payment_history', booking_id: bookingId })
                });
                const data = await res.json();
                if (data.status === 'success') {
                    this.paymentHistory = data.payments || [];
                } else {
                    this.paymentHistory = [];
                }
            } catch (error) {
                console.error('Error loading payment history:', error);
                this.paymentHistory = [];
            }
        },
        
        openAddPaymentModal(booking) {
            this.activePaymentBooking = booking;
            const remaining = (booking.totalPrice * booking.seatCount) - (booking.downPaymentAmount || 0);
            this.addPaymentForm = {
                amount: remaining,
                payment_method: 'Cash',
                payment_location: '',
                payment_receiver: '',
                notes: ''
            };
            this.isAddPaymentModalVisible = true;
        },
        
        closeAddPaymentModal() {
            this.isAddPaymentModalVisible = false;
            this.activePaymentBooking = null;
        },
        
        async submitAddPayment() {
            if (!this.addPaymentForm.amount || this.addPaymentForm.amount <= 0) {
                Swal.fire('Error', 'Nominal pembayaran harus lebih dari 0', 'error');
                return;
            }
            
            const remaining = (this.activePaymentBooking.totalPrice * this.activePaymentBooking.seatCount) - (this.activePaymentBooking.downPaymentAmount || 0);
            
            if (this.addPaymentForm.amount > remaining) {
                const confirm = await Swal.fire({
                    title: 'Nominal Lebih Besar',
                    text: `Nominal melebihi sisa tagihan (Rp ${remaining.toLocaleString('id-ID')}). Lanjutkan?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Lanjutkan',
                    cancelButtonText: 'Batal'
                });
                
                if (!confirm.isConfirmed) return;
            }
            
            try {
                const res = await fetch('api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'add_payment',
                        data: {
                            booking_id: this.activePaymentBooking.id,
                            ...this.addPaymentForm
                        }
                    })
                });
                
                const data = await res.json();
                
                if (data.status === 'success') {
                    Swal.fire({
                        title: 'Berhasil!',
                        html: `<div class="text-sm space-y-2">
                            <p><strong>Pembayaran berhasil ditambahkan</strong></p>
                            <p>Total Dibayar: <span class="text-green-600 font-bold">Rp ${data.data.total_paid.toLocaleString('id-ID')}</span></p>
                            <p>Sisa: <span class="text-red-600 font-bold">Rp ${data.data.remaining.toLocaleString('id-ID')}</span></p>
                            ${data.data.is_fully_paid ? '<p class="text-green-600 font-bold">✓ LUNAS</p>' : ''}
                        </div>`,
                        icon: 'success',
                        timer: 3000
                    });
                    
                    this.closeAddPaymentModal();
                    this.loadPaymentHistory(this.activePaymentBooking.id);
                    this.loadData(); // Refresh booking data
                } else {
                    Swal.fire('Error', data.message || 'Gagal menyimpan pembayaran', 'error');
                }
            } catch (error) {
                console.error('Error submitting payment:', error);
                Swal.fire('Error', 'Terjadi kesalahan saat menyimpan pembayaran', 'error');
            }
        },
        
        formatDateTime(dateStr) {
            if (!dateStr) return '-';
            const d = new Date(dateStr);
            return d.toLocaleString('id-ID', { 
                day: '2-digit', 
                month: 'short', 
                year: 'numeric', 
                hour: '2-digit', 
                minute: '2-digit' 
            });
        },
        // ===== END PAYMENT MANAGEMENT =====
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
            
            // Fallback to group assignment if not manually selected
            let finalFleetId = fleetId;
            let finalDriverId = driverId;
            
            if (!finalFleetId && group && group.assignment && group.assignment.fleet) {
                finalFleetId = group.assignment.fleet.id;
            }
            if (!finalDriverId && group && group.assignment && group.assignment.driver) {
                finalDriverId = group.assignment.driver.id;
            }

            if(!finalFleetId || !finalDriverId) return Swal.fire('Data Kurang', 'Pilih Armada manual atau driver terlebih dahulu!', 'warning');
            
            const f = this.fleet.find(x=>String(x.id)===String(finalFleetId));
            const d = this.drivers.find(x=>String(x.id)===String(finalDriverId));
            
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

        // --- DRAG & DROP BETWEEN ARMADA CARDS ---
        // --- DRAG & DROP BETWEEN ARMADA CARDS ---
        onDragStartCard(evt, passenger, group) {
            this.isDragging = true;
            evt.dataTransfer.effectAllowed = 'move';
            evt.dataTransfer.dropEffect = 'move';
            
            const payload = JSON.stringify({ 
                id: passenger.id, 
                routeId: group.routeId,
                passengerName: passenger.passengerName,
                // Ensure batchNumber is set (default 1)
                currentBatchNumber: group.batchNumber || 1,
                seatNumbers: passenger.seatNumbers,
                // Include Source Context for Rescheduling checks
                date: group.date,
                time: group.time
            });
            evt.dataTransfer.setData('text/plain', payload);
        },
        
        allowDrop(evt) {
            evt.preventDefault();
            evt.dataTransfer.dropEffect = 'move';
        },

        onDragEndCard(evt) {
            this.isDragging = false;
        },

        onDragEnterCard(evt) {
            evt.currentTarget.classList.add('ring-4', 'ring-blue-300');
        },

        onDragLeaveCard(evt) {
            if (!evt.currentTarget.contains(evt.relatedTarget)) {
                evt.currentTarget.classList.remove('ring-4', 'ring-blue-300');
            }
        },

        async onDropToCard(evt, targetGroup) {
            this.isDragging = false;
            evt.preventDefault();
            evt.currentTarget.classList.remove('ring-4', 'ring-blue-300');

            const dataStr = evt.dataTransfer.getData('text/plain');
            if (!dataStr) {
                console.warn("Drop Failed: No Data");
                return;
            }
            
            let src;
            try {
                src = JSON.parse(dataStr);
            } catch(e) { console.error('Drag parse error', e); return; }

            console.log("DROP EVENT", { src, targetGroup });
            
            // Default batch to 1 if missing
            const targetBatch = parseInt(targetGroup.batchNumber) || 1;
            const srcBatch = parseInt(src.currentBatchNumber) || 1;
            
            // Prevent dropping on exact same context (Batch + Date + Time)
            const isSameBatch = srcBatch == targetBatch;
            const isSameDate = src.date === targetGroup.date;
            const isSameTime = src.time === targetGroup.time;

            if (isSameBatch && isSameDate && isSameTime) {
                console.log("Ignored Drop: Same Context");
                this.showToast('Sudah di armada ini', 'info'); 
                return;
            }

            // --- SEAT CONFLICT DETECTION ---
            const srcSeats = src.seatNumbers ? src.seatNumbers.split(',').map(s => s.trim()) : [];
            const occupiedSeats = new Set();
            targetGroup.passengers.forEach(p => {
                // Ignore self if dropped back to same group (already handled above, but safety)
                if (p.id == src.id) return; 
                if (p.seatNumbers) {
                    p.seatNumbers.split(',').forEach(s => occupiedSeats.add(s.trim()));
                }
            });

            const conflicts = srcSeats.filter(s => occupiedSeats.has(s));
            let finalSeatNumbers = src.seatNumbers;

            if (conflicts.length > 0) {
                const { value: newSeats, isConfirmed } = await Swal.fire({
                    title: 'Bangku Bentrok!',
                    html: `
                        <p class="mb-2 text-sm text-red-600">Bangku <b>${conflicts.join(', ')}</b> sudah terisi di Armada ${targetBatch}.</p>
                        <p class="mb-2 text-sm">Bangku Terpakai: ${Array.from(occupiedSeats).sort().join(', ')}</p>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Masukkan Bangku Baru:</label>
                    `,
                    input: 'text',
                    inputValue: '',
                    inputPlaceholder: 'Contoh: 5,6',
                    showCancelButton: true,
                    confirmButtonText: 'Simpan & Pindah',
                    cancelButtonText: 'Batal',
                    inputValidator: (value) => {
                        if (!value) return 'Nomor bangku harus diisi!';
                        const checkInput = value.split(',').map(s => s.trim());
                        const stillConflict = checkInput.filter(s => occupiedSeats.has(s));
                        if(stillConflict.length > 0) {
                            return `Bangku ${stillConflict.join(', ')} masih bentrok!`;
                        }
                    }
                });

                if (!isConfirmed) return;
                finalSeatNumbers = newSeats;
            } else {
                 // Confirmation for normal move (No Conflict)
                 const result = await Swal.fire({
                    title: 'Pindah Armada?',
                    text: `Pindahkan "${src.passengerName}" ke Armada ${targetBatch}?` + 
                          (!isSameDate ? ` (Tanggal: ${this.formatDate(targetGroup.date)})` : ''),
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Pindah',
                    cancelButtonText: 'Batal'
                });
                if (!result.isConfirmed) return;
            }

            // Call API to update batchNumber (+ new seats if any)
            this.isLoading = true;
            const res = await this.postToApi('move_booking_schedule', {
                id: src.id,
                date: targetGroup.date,
                time: targetGroup.time,
                seatNumbers: finalSeatNumbers, // Send potentially new seats
                batchNumber: targetBatch
            });
            
            if (res.status === 'success') {
                this.loadData();
                this.showToast(`Dipindahkan ke Armada ${targetBatch}`, 'success');
            } else {
                this.isLoading = false;
                Swal.fire("Gagal", res.message, 'error');
            }
        },

        // --- PACKAGE SHIPMENT METHODS ---
        async loadPackages() {
            const res = await this.postToApi('get_packages');
            if (res.packages) {
                this.packages = res.packages;
            }
        },

        calculatePackagePrice() {
            const tarifs = {
                'Pool to Pool': {
                    'Padang - Bukittinggi': { 'Surat / Dokumen': 30000, 'Barang Kardus': 30000, 'Barang Lainnya / Big Size': 100000 },
                    'Padang - Payakumbuh': { 'Surat / Dokumen': 40000, 'Barang Kardus': 40000, 'Barang Lainnya / Big Size': 130000 },
                    'Bukittinggi - Payakumbuh': { 'Surat / Dokumen': 15000, 'Barang Kardus': 15000, 'Barang Lainnya / Big Size': 50000 }
                },
                'Antar Jemput Alamat': {
                    'Padang - Bukittinggi': { 'Surat / Dokumen': 60000, 'Barang Kardus': 60000, 'Barang Lainnya / Big Size': 120000 },
                    'Padang - Payakumbuh': { 'Surat / Dokumen': 70000, 'Barang Kardus': 70000, 'Barang Lainnya / Big Size': 150000 },
                    'Bukittinggi - Payakumbuh': { 'Surat / Dokumen': 25000, 'Barang Kardus': 25000, 'Barang Lainnya / Big Size': 70000 }
                }
            };
            
            const cat = this.packageForm.category === 'Pool to Pool' ? 'Pool to Pool' : 'Antar Jemput Alamat';
            const route = this.packageForm.route;
            const type = this.packageForm.itemType;
            
            if (tarifs[cat] && tarifs[cat][route] && tarifs[cat][route][type]) {
                this.packageForm.price = tarifs[cat][route][type];
            } else {
                this.packageForm.price = 0;
            }
        },

        async savePackage() {
            if (!this.packageForm.senderName || !this.packageForm.receiverName) {
                return Swal.fire('Error', 'Nama Pengirim dan Penerima wajib diisi.', 'error');
            }


            // Include Admin Name in creation (Safe Fallback)
            const adminName = (this.user && this.user.name) ? this.user.name : (this.currentUser && this.currentUser.name ? this.currentUser.name : 'Admin');
            this.packageForm.adminName = adminName;
            
            // Ensure bookingDate is set
            if (!this.packageForm.bookingDate) {
                this.packageForm.bookingDate = new Date().toISOString().slice(0, 10);
            }


            this.isLoading = true;
            const res = await this.postToApi('create_package', { data: this.packageForm });
            this.isLoading = false;

            if (res.status === 'success') {
                this.showToast('Booking Paket Berhasil! Resi: ' + res.receiptNumber, 'success');
                this.packageView = 'history';
                this.loadPackages();
                // Reset Form
                this.packageForm.senderName = '';
                this.packageForm.senderPhone = '';
                this.packageForm.receiverName = '';
                this.packageForm.receiverPhone = '';
                this.packageForm.itemDescription = '';
                this.packageForm.mapLink = '';
            } else {
                Swal.fire('Gagal', res.message, 'error');
            }
        },

        // --- NEW: Package Tracking & Updates ---
        async openTrackingModal(p) {
            this.activePackage = p;
            this.activePackageLogs = []; // Reset
            this.isTrackingModalVisible = true;
            
            // Fetch Details & Logs
            const res = await this.postToApi('get_package_details', { id: p.id });
            if (res.status === 'success') {
                this.activePackage = res.package;
                this.activePackageLogs = res.logs;
            }
        },

        closeTrackingModal() {
            this.isTrackingModalVisible = false;
        },

        openStatusUpdateModal(p) {
            this.activePackage = p;
            this.statusForm = {
                id: p.id,
                status: p.status,
                location: '',
                description: '',
                adminName: this.currentUser.name
            };
            this.isStatusModalVisible = true;
        },

        async saveStatusUpdate() {
            if (!this.statusForm.description) {
                 this.statusForm.description = 'Update status menjadi ' + this.statusForm.status;
            }
            
            this.isLoading = true;
            const res = await this.postToApi('update_package_status', this.statusForm);
            this.isLoading = false;
            
            if (res.status === 'success') {
                this.showToast('Status & Tracking Diperbarui', 'success');
                this.isStatusModalVisible = false;
                this.loadPackages(); // Refresh List
            } else {
                Swal.fire('Gagal', res.message, 'error');
            }
        },


        printReceipt(p) {
            // Receipt Layout (Thermo Printer Friendly 58mm/80mm Style)
            const receiptHtml = `
                <div style="font-family: 'Courier New', monospace; width: 300px; padding: 10px; font-size: 12px;">
                    <div style="text-align: center; margin-bottom: 10px;">
                        <h3 style="margin:0; font-weight:bold; font-size:16px;">SUTAN RAYA</h3>
                        <p style="margin:0; font-size:10px;">Jasa Pengiriman & Travel</p>
                        <hr style="border-top: 1px dashed #000; margin: 5px 0;">
                        <h4 style="margin:0;">RESI PENGIRIMAN</h4>
                        <h2 style="margin:5px 0; letter-spacing: 1px;">${p.receiptNumber || 'PK-'+p.id}</h2>
                        <!-- Barcode Container -->
                        <div style="margin: 5px 0;"><img id="barcode-${p.id}" /></div>
                    </div>
                    
                    <div style="margin-bottom: 10px;">
                        <table style="width: 100%; font-size: 12px;">
                            <tr><td style="width: 60px;">Tgl</td><td>: ${this.formatDate(p.bookingDate)}</td></tr>
                            <tr><td>Asal</td><td>: ${p.route.split(' - ')[0]}</td></tr>
                            <tr><td>Tujuan</td><td>: ${p.route.split(' - ')[1]}</td></tr>
                        </table>
                    </div>
                    
                    <hr style="border-top: 1px dashed #000; margin: 5px 0;">
                    
                    <div style="margin-bottom: 10px;">
                        <div style="font-weight:bold;">PENGIRIM:</div>
                        <div>${p.senderName}</div>
                        <div>${p.senderPhone}</div>
                    </div>
                    
                    <div style="margin-bottom: 10px;">
                        <div style="font-weight:bold;">PENERIMA:</div>
                        <div>${p.receiverName}</div>
                        <div>${p.receiverPhone}</div>
                        <div style="font-size:10px; margin-top:2px;">${p.category === 'Antar Jemput Alamat' ? (p.dropoffAddress || '-') : 'Ambil di Pool'}</div>
                    </div>
                    
                    <hr style="border-top: 1px dashed #000; margin: 5px 0;">
                    
                    <div style="margin-bottom: 10px;">
                        <div style="font-weight:bold;">BARANG:</div>
                        <div>${p.itemType}</div>
                        <div style="font-style:italic;">${p.itemDescription}</div>
                    </div>
                    
                    <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 14px; margin-top: 10px;">
                        <span>TOTAL:</span>
                        <span>${this.formatRupiah(p.price)}</span>
                    </div>
                    <div style="text-align: right; font-size: 10px;">${p.paymentStatus} (${p.paymentMethod})</div>
                    
                    <hr style="border-top: 1px dashed #000; margin: 15px 0;">
                    <div style="text-align: center; font-size: 10px;">
                        <p>Simpan resi ini untuk pengambilan.<br>Cek resi di sutanraya.com</p>
                        <p style="margin-top:5px;">Terima Kasih</p>
                    </div>
                </div>
            `;
            
            // Create iframe for printing
            const printFrame = document.createElement('iframe');
            printFrame.style.position = 'absolute';
            printFrame.style.top = '-1000px';
            document.body.appendChild(printFrame);
            
            printFrame.contentDocument.write('<html><head><title>Print Receipt</title></head><body>');
            printFrame.contentDocument.write(receiptHtml);
            
            // Generate Barcode inside iframe
            const script = printFrame.contentDocument.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js';
            script.onload = () => {
                const win = printFrame.contentWindow;
                win.JsBarcode(`#barcode-${p.id}`, p.receiptNumber || 'PK-'+p.id, {
                    format: "CODE128",
                    lineColor: "#000",
                    width: 2,
                    height: 40,
                    displayValue: false
                });
                
                setTimeout(() => {
                    win.focus();
                    win.print();
                    setTimeout(() => {
                        document.body.removeChild(printFrame);
                    }, 1000);
                }, 500);
            };
            printFrame.contentDocument.head.appendChild(script);
            
            printFrame.contentDocument.write('</body></html>');
            printFrame.contentDocument.close();
        },


        // --- MOVE BOOKING MODAL (ADVANCED) ---
        async openBookingActions(booking, group) {
            // Replaced SweetAlert with Custom Modal
            this.openMoveModal(booking);
        },

        openMoveModal(booking) {
            const route = this.routeConfig.find(r => r.id === booking.routeId);
            const schedules = route ? (route.schedules || []) : [];
            
            this.moveModalData = {
                passengerId: booking.id,
                passengerName: booking.passengerName,
                routeId: booking.routeId,
                currentDate: booking.date,
                currentTime: booking.time,
                seatNumbers: booking.seatNumbers, // Pre-fill current seats
                
                targetTime: booking.time, // Default to current time
                targetBatchIndex: 0, // Default to first batch (or find current)
                
                availableSchedules: schedules,
                allBatchesForTime: []
            };

            // Calculate batches for the initial target time
            this.recalcMoveBatches();

            // Try to find which batch the user is currently in (if time matches)
            if (this.moveModalData.targetTime === booking.time) {
                // Find batch index by matching passenger ID
                const currentBatchIdx = this.moveModalData.allBatchesForTime.findIndex(b => b.passengers.find(p => p.id === booking.id));
                if (currentBatchIdx !== -1) {
                    this.moveModalData.targetBatchIndex = currentBatchIdx;
                }
            }

            this.isMoveModalVisible = true;
        },

        closeMoveModal() {
            this.isMoveModalVisible = false;
        },

        recalcMoveBatches() {
            const targetDate = this.moveModalData.currentDate;
            const targetTime = this.moveModalData.targetTime;
            const routeId = this.moveModalData.routeId;

            // 1. Find existing batches for the Target Time from groupedBookings
            // Note: groupedBookings is a computed property, available as this.groupedBookings
            // It contains all batches (split by Collision/Capacity).
            
            const relevantBatches = this.groupedBookings.filter(b => 
                b.routeConfig && b.routeConfig.id === routeId && // Ensure Route matches
                b.date === targetDate && 
                b.time === targetTime
            );

            // 2. Map visual structure
            // We want to exclude the current passenger if they happen to be in this list (e.g. "Change Seat" Same Time)
            // But groupedBookings is computed from this.bookings.
            // If we are moving Time, we are NOT in the target batch yet.
            // If we are Changing Seat (Same Time), we ARE in the batch.
            // visual logic: show occupied seats.
            // If I am in Batch 1, and I want to change seat in Batch 1, 
            // I should see other people's seats as occupied, but MY seat as "My Selection".
            // My modal logic highlights `seatNumbers`.
            // So `isSeatOccupied` should return false for MY seat.
            // The `isSeatOccupied` function checks `batch.passengers`.
            // So I just need to ensure I don't count MYSELF as "Occupied".
            // The simplest way is to map the batches and filter out myself from `passengers` list.

            this.moveModalData.allBatchesForTime = relevantBatches.map((b, idx) => ({
                name: `Armada ${b.batchNumber || (idx + 1)}`,
                // Assignment info for context?
                isFull: b.passengers.length >= 8, // Visual hint
                passengers: b.passengers.filter(p => p.id !== this.moveModalData.passengerId)
            }));
            
            // If no batches exist for that time (empty schedule), we start with 1 empty batch?
            if (this.moveModalData.allBatchesForTime.length === 0) {
                 this.moveModalData.allBatchesForTime.push({ name: 'Armada 1', passengers: [] });
            }
            
            // Reset selection to first batch when time changes
            this.moveModalData.targetBatchIndex = 0;
        },

        moveModalBatches() {
            // Retrieve calculated batches
            return this.moveModalData.allBatchesForTime || [];
        },

        selectMoveBatch(idx) {
            if (idx === -1) {
                // "New Armada" -> Add a new virtual batch?
                // Or just select a new index that represents "Empty/New"
                const newIdx = this.moveModalData.allBatchesForTime.length;
                // We don't actually push to array yet, just visualize empty.
                // But to make UI consistent, maybe we push?
                // No, just handle handle separate logic.
                // Simpler: Just allow selecting "Batch N" where N > last index.
                // But for UI, let's just push a placeholder if not exists?
                // Or just toggle a "New Armada" mode.
                
                // Let's Add a virtual batch to the list if not exists
                // Limit to sensible amount?
                 this.moveModalData.allBatchesForTime.push({ name: `Armada ${this.moveModalData.allBatchesForTime.length + 1}`, passengers: [] });
                 this.moveModalData.targetBatchIndex = this.moveModalData.allBatchesForTime.length - 1;
            } else {
                this.moveModalData.targetBatchIndex = idx;
            }
        },

        isSeatOccupied(seat) {
            const batchIdx = this.moveModalData.targetBatchIndex;
            const batch = this.moveModalData.allBatchesForTime[batchIdx];
            if (!batch) return false; // New/Empty batch
            
            // Check passengers in this batch
            // Note: `passengers` array here are the OTHER passengers.
            return batch.passengers.some(p => {
                if (!p.seatNumbers) return false;
                const seats = p.seatNumbers.split(',').map(s=>s.trim());
                return seats.includes(seat);
            });
        },
        
        getSeatClass(seat) {
            const selectedSeats = this.moveModalData.seatNumbers 
                ? this.moveModalData.seatNumbers.split(',').map(s => s.trim())
                : [];
            if (selectedSeats.includes(seat)) return 'bg-blue-600 text-white border-blue-600 ring-2 ring-blue-200';
            if (this.isSeatOccupied(seat)) return 'bg-white border-slate-200 text-slate-300';
            return 'bg-white text-slate-600 hover:bg-blue-50 border-slate-200';
        },

        toggleMoveSeat(seat) {
            if (this.isSeatOccupied(seat)) return;
            
            // Multi-select toggle logic
            let currentSeats = this.moveModalData.seatNumbers 
                ? this.moveModalData.seatNumbers.split(',').map(s => s.trim()).filter(s => s)
                : [];
            
            const idx = currentSeats.indexOf(seat);
            if (idx > -1) {
                // Already selected -> remove
                currentSeats.splice(idx, 1);
            } else {
                // Not selected -> add
                currentSeats.push(seat);
            }
            
            this.moveModalData.seatNumbers = currentSeats.join(', ');
        },

        async saveMove() {
            const { passengerId, targetTime, seatNumbers, targetBatchIndex } = this.moveModalData;
            
            if (!seatNumbers) return Swal.fire('Error', 'Pilih kursi terlebih dahulu.', 'error');
            
            // Calculate batchNumber (1-indexed for DB)
            const batchNumber = targetBatchIndex + 1;
            
            this.isLoading = true;
            const res = await this.postToApi('move_booking_schedule', {
                id: passengerId,
                date: this.moveModalData.currentDate,
                time: targetTime,
                seatNumbers: seatNumbers,
                batchNumber: batchNumber
            });
            
            if (res.status === 'success') {
                this.closeMoveModal();
                this.loadData();
                this.showToast('Berhasil dipindahkan ke Armada ' + batchNumber, 'success');
            } else {
                this.isLoading = false;
                Swal.fire("Gagal", res.message, 'error');
            }
        },

        async moveBookingSchedule(id, date, time, seatNumbers = null) {
            this.isLoading = true;
            const payload = { id, date, time };
            if (seatNumbers !== null) payload.seatNumbers = seatNumbers;
            
            const res = await this.postToApi('move_booking_schedule', payload);
            if (res.status === 'success') {
                // Reload to reflect changes
                this.loadData();
                this.showToast('Data Booking Berhasil Diubah', 'success');
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
        async updateTripStatus(trip, status) {
            if (!trip || !trip.id) return Swal.fire('Error', 'Invalid Trip Data', 'error');
            this.isLoading = true;
            const res = await this.postToApi('update_trip_status', { tripId: trip.id, status: status });
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
        closeVehicleModal() {
            this.isVehicleModalVisible = false;
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
        closeDriverModal() {
            this.isDriverModalVisible = false;
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

        // --- SCHEDULE MANAGEMENT ---
        // --- SCHEDULE MANAGEMENT ---


        // --- ROUTE MANAGEMENT ---
        openRouteModal(r = null) {
            this.routeModal.mode = r ? 'edit' : 'add';
            if (r) {
                // Copy data to routeForm
                const data = JSON.parse(JSON.stringify(r));
                this.routeForm = {
                    id: data.id,
                    origin: data.origin,
                    destination: data.destination,
                    schedulesInput: (data.schedules || []).join(', '),
                    prices: data.prices || { umum: 0, pelajar: 0, dropping: 0, carter: 0 }
                };
            } else {
                this.routeForm = { id: '', origin: '', destination: '', schedulesInput: '', prices: { umum: 0, pelajar: 0, dropping: 0, carter: 0 } };
            }
            this.isRouteModalVisible = true; 
        },
        async saveRoute() {
            const f = this.routeForm;
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
            
            const normTime = (t) => t ? String(t).trim().substring(0, 5) : '';
            const gTime = normTime(time);

            // 1. Check Specific Trip (Override)
            const specificTrip = this.trips.find(t => {
                const tDate = t.date || (t.passengers && t.passengers[0] ? t.passengers[0].date : null);
                
                // Robust Date Comparison
                if (!tDate) return false;
                
                // Date Normalization (Local Time Safe)
                const d1 = new Date(tDate).toLocaleDateString('sv'); // YYYY-MM-DD
                const d2 = new Date(date).toLocaleDateString('sv');
                
                const timeMatch = normTime(t.time) === gTime;
                
                // Route ID handling
                let tRouteId = '';
                if (t.routeConfig && typeof t.routeConfig === 'object') tRouteId = t.routeConfig.id;
                else if (t.routeConfig) tRouteId = t.routeConfig; // fallback if string
                
                const routeMatch = String(tRouteId).trim() == String(routeId).trim();
                
                const isMatch = d1 === d2 && timeMatch && routeMatch;

                // DEBUG SPECIFIC FOR 18:00
                if (gTime === '18:00' && (d2 === '2025-12-05' || d2 === '2025-12-02') ) {
                     // console.log(`COMPARE: TripID=${t.id} | D1=${d1} vs D2=${d2} (${d1===d2}) | T1=${normTime(t.time)} vs T2=${gTime} (${timeMatch}) | R1=${tRouteId} vs R2=${routeId} (${routeMatch}) | MATCH=${isMatch}`);
                }
                
                return isMatch;
            });
            
            if (specificTrip) return { ...specificTrip, type: 'Specific' };
            
            // 2. Check Default Schedule
            const def = this.scheduleDefaults.find(d => String(d.routeId).trim() == String(routeId).trim() && normTime(d.time) === gTime);
            if (def) {
                const f = this.fleet.find(f => String(f.id).trim() == String(def.fleetId).trim());
                const d = this.drivers.find(d => String(d.id).trim() == String(def.driverId).trim());

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
        
        openScheduleModal(route, time, date, assignment = null, batchNumber = 1) {
            this.scheduleForm = {
                route: route,
                time: time,
                targetDate: date || this.manifestDate,
                fleetId: assignment ? assignment.fleet?.id : '',
                driverId: assignment ? assignment.driver?.id : '',
                isDefault: assignment && assignment.type === 'Default',
                batchNumber: batchNumber
            };
            this.isScheduleModalVisible = true;
        },
        
        async saveScheduleAssignment() {
            let { route, time, fleetId, driverId, isDefault, batchNumber } = this.scheduleForm;
            if (!fleetId || !driverId) return Swal.fire('Data Kurang', 'Pilih Armada dan Supir!', 'warning');
            
            // Cannot set Default for Armada 2+ (Overflow)
            if (batchNumber > 1) isDefault = false;

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
                    
                    // Force Update Local State immediately
                    const existingIdx = this.scheduleDefaults.findIndex(d => String(d.routeId) == String(route.id) && d.time.substring(0,5) === time.substring(0,5));
                    const newDef = { 
                        id: Date.now(), // Temporary ID until reload
                        routeId: route.id, 
                        time: time, 
                        fleetId: fleetId, 
                        driverId: driverId 
                    };
                    
                    if (existingIdx >= 0) {
                        this.scheduleDefaults[existingIdx] = newDef;
                    } else {
                        this.scheduleDefaults.push(newDef);
                    }
                    
                    // Still reload to be safe
                    this.loadData(true); 
                } else {
                    Swal.fire("Gagal", res.message, 'error');
                }
            } else {
                // Save as Specific Trip (Override)
                const f = this.fleet.find(x => x.id === fleetId);
                const d = this.drivers.find(x => x.id === driverId);
                
                // Normalization Helper
                const normTime = (t) => t ? String(t).trim().substring(0, 5) : '';
                const gTime = normTime(time);

                // Multi-Batch Logic: Find ALL Specific Trips
                 const specificTrips = this.trips.filter(t => 
                    t.routeConfig && 
                    String(t.routeConfig.id).trim() === String(route.id).trim() && 
                    String(t.date).trim() === String(this.scheduleForm.targetDate || this.manifestDate).trim() && 
                    normTime(t.time) === gTime &&
                    t.status === 'Scheduled'
                );
                
                // Sort to find the correct trip for this batch
                specificTrips.sort((a,b) => parseFloat(a.id) - parseFloat(b.id)); // ID Sort
                
                const targetIndex = (batchNumber || 1) - 1;
                const existingTrip = specificTrips[targetIndex];
                
                const tripId = existingTrip ? existingTrip.id : Date.now();
                
                const tripData = {
                    id: tripId,
                    routeConfig: route,
                    fleet: f,
                    driver: d,
                    passengers: existingTrip ? existingTrip.passengers : [],
                    status: 'Scheduled',
                    date: this.scheduleForm.targetDate || this.manifestDate,
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

        showInvalidStatusInfo(p) {
            const hasFleet = p.fleetName || p.fleetId;
            const hasDriver = p.driverName || p.driverId;
            
            let comparisonHtml = `
                <div class="bg-gray-50 p-3 rounded-lg mb-3 text-xs border border-gray-200">
                    <div class="grid grid-cols-2 gap-2">
                        <div class="font-bold text-gray-500 uppercase">Data Saat Ini (Anomali)</div>
                        <div class="font-bold text-gray-500 uppercase">Seharusnya (Lengkap)</div>
                        
                        <div class="border-b pb-1">Status: <span class="text-red-600 font-bold">${p.status}</span></div>
                        <div class="border-b pb-1 text-gray-400">Status: On Trip</div>

                        <div class="border-b pb-1">
                            Armada: <span class="${hasFleet ? 'text-green-600' : 'text-red-600 font-bold'}">${hasFleet ? (p.fleetName || 'Ada ID') : 'KOSONG'}</span>
                        </div>
                        <div class="border-b pb-1 text-gray-400">Armada: Terisi</div>

                        <div class="border-b pb-1">
                            Supir: <span class="${hasDriver ? 'text-green-600' : 'text-red-600 font-bold'}">${hasDriver ? (p.driverName || 'Ada ID') : 'KOSONG'}</span>
                        </div>
                        <div class="border-b pb-1 text-gray-400">Supir: Terisi</div>
                    </div>
                </div>
            `;

            Swal.fire({
                title: 'Analisa Data Invalid',
                html: `
                    <div class="text-left text-sm">
                        <p class="mb-2">Data booking ini tidak lengkap untuk status <b>On Trip</b>.</p>
                        ${comparisonHtml}
                        <ul class="list-disc pl-5 mb-2 text-gray-600 text-xs">
                            <li>Jika Armada/Supir kosong, berarti booking ini dipaksa 'On Trip' tanpa dispatch.</li>
                            <li>Sistem menolaknya dari Dispatcher karena data tidak konsisten.</li>
                        </ul>
                        <p class="font-bold mt-2">Solusi:</p>
                        <p>Klik <b>Perbaiki Data</b> untuk melengkapi Armada/Supir atau kembalikan status ke 'Pending'.</p>
                    </div>
                `,
                icon: 'error',
                showCancelButton: true,
                confirmButtonText: 'Perbaiki Data (Edit)',
                confirmButtonColor: '#d33',
                cancelButtonText: 'Tutup'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.openBookingActions(p);
                }
            });
        },

        sendManifestToDriver(group) {
            console.log("Send Manifest Triggered", group);
            const driver = group.assignment ? group.assignment.driver : null;
            if (!driver || !driver.phone) {
                return Swal.fire('Error', 'Data Supir atau Nomor HP Supir tidak ditemukan.', 'error');
            }

            // Header
            const dateStr = new Date(group.date).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
            let msg = `*MANIFEST PERJALANAN - SUTAN RAYA*\n`;
            msg += `Tanggal: ${dateStr}\n`;
            msg += `Jam: ${group.time}\n`;
            msg += `Rute: ${group.routeConfig.origin} -> ${group.routeConfig.destination}\n`;
            msg += `Armada: ${group.assignment.fleet.name} (${group.assignment.fleet.plate})\n`;
            msg += `Supir: ${driver.name}\n\n`;
            msg += `*DAFTAR PENUMPANG:*\n`;

            // Sort passengers by seat number
            const sortedPassengers = [...group.passengers].sort((a,b) => {
                // Try parse numerical seat
                const sA = parseInt(a.seatNumbers) || 999;
                const sB = parseInt(b.seatNumbers) || 999;
                return sA - sB;
            });

            sortedPassengers.forEach((p, index) => {
                const statusText = (p.paymentStatus === 'Lunas' || p.paymentStatus === 'Paid') ? 'LUNAS' : 'BELUM LUNAS';
                const seatInfo = p.seatNumbers ? `(Kursi ${p.seatNumbers})` : '';
                
                msg += `----------------------------------\n`;
                msg += `${index + 1}. *${p.passengerName}* ${seatInfo}\n`;
                msg += `   HP: ${p.passengerPhone}\n`;
                msg += `   Status: ${statusText}\n`;
                msg += `   Jemput: ${p.pickupAddress || '-'}\n`;
                if(p.pickupAddress) msg += `   Maps: https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(p.pickupAddress)}\n`;
                msg += `   Antar: ${p.dropoffAddress || '-'}\n`;
                if(p.dropoffAddress) msg += `   Maps: https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(p.dropoffAddress)}\n`;
            });

            msg += `\n----------------------------------\n`;
            msg += `Total: ${group.totalPassengers} Orang\n`;
            msg += `\n_Mohon dicek kembali dan lapor admin jika ada ketidaksesuaian._`;

            // Show Preview
            Swal.fire({
                title: 'Kirim Manifest ke Supir',
                input: 'textarea',
                inputLabel: 'Preview Pesan WhatsApp',
                inputValue: msg,
                inputAttributes: {
                    'aria-label': 'Pesan WhatsApp',
                    'style': 'height: 300px; font-size: 12px; font-family: monospace;'
                },
                showCancelButton: true,
                confirmButtonText: '<i class="bi bi-whatsapp"></i> Kirim Sekarang',
                confirmButtonColor: '#25D366',
                cancelButtonText: 'Batal',
                didOpen: () => {
                   // Optional: Auto-select text?
                }
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    const finalMsg = encodeURIComponent(result.value);
                    const waLink = `https://wa.me/${driver.phone.replace(/^0/, '62').replace(/[^0-9]/g, '')}?text=${finalMsg}`;
                    window.open(waLink, '_blank');
                }
            });
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
                const res = await fetch(`api.php?action=get_initial_data&_=${new Date().getTime()}`);
                const data = await res.json();
                
                this.bookings = data.bookings || [];
                this.fleet = data.fleet || [];
                this.drivers = data.drivers || [];
                this.trips = data.trips || [];
                
                // DEBUG: Check specific trip for 2025-12-05
                const debugTrip = this.trips.find(t => t.date === '2025-12-05');
                if (debugTrip) {
                    console.log("DEBUG: Found Trip 2025-12-05", debugTrip);
                   // Swal.fire('Debug', `Found Trip: ${debugTrip.id} - ${debugTrip.date} - ${debugTrip.time}`, 'info');
                } else {
                   // Swal.fire('Debug', 'No Trip found for 2025-12-05', 'warning');
                }

                this.routeConfig = data.routes || [];
                this.scheduleDefaults = data.scheduleDefaults || [];

                // Sort trips by ID DESC (Latest first) to handle potential duplicates
                this.trips.sort((a, b) => parseFloat(b.id) - parseFloat(a.id));
                
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