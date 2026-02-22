
const { createApp } = Vue;

import { dataMixin } from './js/modules/data.js?v=12.20';
import { utilsMixin } from './js/modules/utils.js?v=12.20';
import { authMixin } from './js/modules/auth.js?v=12.20';
import { uiMixin } from './js/modules/ui.js?v=12.20';
import { bookingMixin } from './js/modules/booking.js?v=12.20';
import { paymentMixin } from './js/modules/payment.js?v=12.20';
import { packageMixin } from './js/modules/package.js?v=12.20';
import { dispatcherMixin } from './js/modules/dispatcher.js?v=12.20';
import { assetsMixin } from './js/modules/assets.js?v=12.20';
import { scheduleMixin } from './js/modules/schedule.js?v=12.20';
import { reportMixin } from './js/modules/report.js?v=12.20';

window.app = createApp({
    mixins: [
        dataMixin, 
        utilsMixin, 
        authMixin, 
        uiMixin, 
        bookingMixin, 
        paymentMixin, 
        packageMixin,
        dispatcherMixin,
        assetsMixin,
        scheduleMixin,
        reportMixin
    ],
    
    created() {
        this.checkSession(); 
        this.loadData();

        // Load Filters
        // Load Filters - Default Date to Today to avoid "late" reading
        // this.filterDate = localStorage.getItem('sr_filter_date') || ''; // REMOVED to fix "telat baca hari"
        const d = new Date();
        const y = d.getFullYear();
        const m = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        this.filterDate = `${y}-${m}-${day}`;
        
        // Preserve other filters
        this.busSearchTerm = localStorage.getItem('sr_filter_search') || '';
        this.filterMethod = localStorage.getItem('sr_filter_method') || 'All';
        this.filterRoute = localStorage.getItem('sr_filter_route') || 'All';
        this.filterSort = 'Newest'; 

        // Auto-refresh 
        setInterval(() => { 
            this.loadData(true); 
            if (this.view === 'packageShipping') this.loadPackages();
        }, 30000);
        
        this.updateTime(); 
        setInterval(this.updateTime, 1000);
        
        const path = window.location.pathname;
        if (path.includes('booking_management.php')) this.view = 'bookingManagement';
        else if (path.includes('booking_pending.php')) this.view = 'bookingPending';
        else if (path.includes('schedule.php')) this.view = 'schedule';
        else if (path.includes('manifest.php')) { this.view = 'manifest'; this.fetchReports(); }
        else if (path.includes('assets.php')) this.view = 'assets';
        else if (path.includes('route_management.php')) this.view = 'routeManagement';
        else if (path.includes('package_shipping.php') || path.includes('paket.php')) { this.view = 'packageShipping'; this.loadPackages(); }
        else if (path.includes('display-v12/dispatcher.php') || window.initialView === 'dispatcher') this.view = 'dispatcher';
    },

    watch: {
        view(val) { localStorage.setItem('sutan_v10_view', val); },
        busSearchTerm(val) { localStorage.setItem('sr_filter_search', val); this.currentPage = 1; },
        filterMethod(val) { localStorage.setItem('sr_filter_method', val); this.currentPage = 1; },
        filterDate(val) { localStorage.setItem('sr_filter_date', val); this.currentPage = 1; },
        filterRoute(val) { localStorage.setItem('sr_filter_route', val); this.currentPage = 1; },
        manifestDate(val) { this.loadData(); },
    },
    
    methods: {
        // Wrapper to fix naming conflict between Booking Form and Move Modal
        isSeatOccupied(arg) {
            if (this.isMoveModalVisible) {
                return this.isMoveSeatOccupied(arg);
            }
            // Logic from bookingMixin
            // But wait, bookingMixin defines isSeatOccupied.
            // When mixed in, the last one wins if they have the same name.
            // I removed isSeatOccupied from dispatcherMixin (renamed to isMoveSeatOccupied).
            // But bookingMixin HAS `isSeatOccupied`.
            // So calling `this.isSeatOccupied` will call the one in bookingMixin.
            // This is correct for Booking Form.
            // But for Move Modal (in dispatcher.php), if it calls `isSeatOccupied(seat)`,
            // it will now call the Booking Logic, which expects Route/Date/Time from bookingForm!
            // This will FAIL for Move Modal.
            
            // So we MUST override `isSeatOccupied` here in the main app to dispatch correctly.
            // Assuming the argument `arg` is the seat ID (string/number).
            
            if (this.isMoveModalVisible) {
                 return this.isMoveSeatOccupied(arg);
            }
            
            // Default to Booking Logic.
            // But since we are overriding, we can't call `super.isSeatOccupied`.
            // We need to call the implementation directly or duplicate it?
            // Or better: Rename `isSeatOccupied` in bookingMixin to `isBookingSeatOccupied` 
            // and have this main method dispatch to either.
            
            // I will rename `isSeatOccupied` in bookingMixin to `isBookingSeatOccupied`.
            // And use this wrapper.
            // Wait, I already wrote bookingMixin with `isSeatOccupied`.
            // I should overwrite booking.js with the renamed method, OR
            // just copy the logic here?
            // Renaming is cleaner.
            
            // Actually, if I don't rename, can I access the mixin's method?
            // `bookingMixin.methods.isSeatOccupied.call(this, arg)`? Yes.
            
            return bookingMixin.methods.isSeatOccupied.call(this, arg);
        },

        async loadData(silent = false) {
            if (!silent) this.isLoading = true;
            try {
                const res = await fetch(`api.php?action=get_initial_data&_=${new Date().getTime()}`);
                const data = await res.json();
                
                this.bookings = data.bookings || [];
                this.fleet = data.fleet || [];
                this.drivers = data.drivers || [];
                this.trips = data.trips || [];
                this.routeConfig = data.routes || [];
                this.scheduleDefaults = data.scheduleDefaults || [];

                this.trips.sort((a, b) => parseFloat(b.id) - parseFloat(a.id));
                
                if (this.view === 'manifest') {
                    this.fetchReports();
                }

                this.updateSidebarCounts();
            } catch (e) {
                console.error("Error loading data", e);
            } finally {
                this.isLoading = false;
            }
        },

        updateSidebarCounts() {
            const elDispatch = document.getElementById('pendingDispatchCount');
            if (elDispatch) elDispatch.innerText = this.pendingDispatchCount;
        },
        
        // Computed properties from App.js that might be missing in mixins?
        // pendingValidationCount -> Not in mixins yet?
        // Let's add them here or in data.js (computed).
        // I'll add them here.
    },
    computed: {
        currentViewTitle() { return {dashboard:"Dashboard",bookingManagement:"Kelola Booking",bookingPending:"Booking Pending",dispatcher:"Dispatcher",bookingTravel:"Travel",bookingBus:"Bus",packageShipping:"Kirim Paket",manifest:"Laporan",assets:"Aset",routeManagement:"Rute",schedule:"Jadwal"}[this.view] || "Sutan Raya"; },
        todayRevenue() { const today = this.manifestDate; return this.bookings.filter(b => b.date === today && b.status !== 'Batal').reduce((a,b) => a + (b.totalPrice||0), 0); },
        todayPax() { const today = this.manifestDate; return this.bookings.filter(b => b.date === today && b.status !== 'Batal').length; },
        pendingValidationCount() { return this.bookings.filter(b => b.validationStatus === 'Menunggu Validasi').length; },
        
        // Ensure dependent computed props work (groupedBookings is in dispatcherMixin)
        // pendingDispatchCount is in dispatcherMixin.
    }
}).mount("#app");