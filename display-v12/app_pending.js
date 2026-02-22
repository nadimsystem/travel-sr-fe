
const { createApp } = Vue;

import { dataMixin } from './js/modules/data.js?v=12.20';
import { utilsMixin } from './js/modules/utils.js?v=12.20';
import { authMixin } from './js/modules/auth.js?v=12.20';
import { uiMixin } from './js/modules/ui.js?v=12.20';
import { bookingMixin } from './js/modules/booking.js?v=12.21';
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
        
        // Initialize Dark Mode
        this.isDarkMode = localStorage.getItem('sutan_v10_dark') === 'true';
        if (this.isDarkMode) document.documentElement.classList.add('dark');
        else document.documentElement.classList.remove('dark');
        
        // Explicitly set view for this page
        this.view = 'bookingMandiri';

        // Load Filters - Force Clear for Pending Page
        this.filterDate = '';
        this.busSearchTerm = '';
        this.filterMethod = 'All';
        this.filterRoute = 'All';
        this.filterSort = 'Newest'; 
        
        // Modal state is now in js/modules/data.js for reactivity

        // Auto-refresh 
        setInterval(() => { 
            this.loadData(true); 
        }, 30000);
        
        this.updateTime(); 
        setInterval(this.updateTime, 1000);
    },

    watch: {
        view(val) { localStorage.setItem('sutan_v10_view', val); },
        busSearchTerm(val) { localStorage.setItem('sr_filter_search', val); this.currentPage = 1; },
        filterMethod(val) { localStorage.setItem('sr_filter_method', val); this.currentPage = 1; },
        filterDate(val) { localStorage.setItem('sr_filter_date', val); this.currentPage = 1; },
        filterRoute(val) { localStorage.setItem('sr_filter_route', val); this.currentPage = 1; },
    },
    
    methods: {
        // Wrapper to fix naming conflict between Booking Form and Move Modal
        isSeatOccupied(arg) {
            if (this.isMoveModalVisible) {
                return this.isMoveSeatOccupied(arg);
            }
            return bookingMixin.methods.isSeatOccupied.call(this, arg);
        },

        async loadData(silent = false) {
            if (!silent) this.isLoading = true;
            try {
                const res = await fetch(`api.php?action=get_initial_data&include_antrian=true&_=${new Date().getTime()}`);
                const data = await res.json();
                
                this.bookings = data.bookings || [];
                this.fleet = data.fleet || [];
                this.drivers = data.drivers || [];
                this.trips = data.trips || [];
                this.routeConfig = data.routes || [];
                this.scheduleDefaults = data.scheduleDefaults || [];

                this.trips.sort((a, b) => parseFloat(b.id) - parseFloat(a.id));
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
        
        toggleDarkMode() {
            this.isDarkMode = !this.isDarkMode;
            if (this.isDarkMode) {
                document.documentElement.classList.add('dark');
                document.documentElement.classList.remove('light');
            } else {
                document.documentElement.classList.remove('dark');
                document.documentElement.classList.add('light');
            }
            localStorage.setItem('sutan_v10_dark', this.isDarkMode);
        },
        
        openScheduleModal() {
            this.isScheduleModalOpen = true;
        },

        normalizeSchedules(schedules) {
            if (!schedules || schedules.length === 0) return [];
            return schedules.map(s => {
                if (typeof s === 'string') return { time: s, hidden: false };
                return { time: s.time, hidden: !!s.hidden };
            });
        },

        async toggleRouteSchedule(route, time) {
            this.loadingSchedules = true;
            
            // Get current normalized schedules
            const normalized = this.normalizeSchedules(route.schedules);
            
            // Toggle the one that was clicked
            const updated = normalized.map(s => {
                if (s.time === time) return { ...s, hidden: !s.hidden };
                return s;
            });
            
            // Re-format payload for saving (prices and schedules)
            // The display-v12 API expects a specific payload format similar to how ops saves it:
            // price_umum, price_pelajar, dll. But in our frontend it's deeply nested.
            // Let's create proper prices prop since save_route expects $prices['umum'], etc.
            const pricesPayload = {
                umum: route.price_umum || 0,
                pelajar: route.price_pelajar || 0,
                dropping: route.price_dropping || 0,
                carter: route.price_carter || 0
            };

            const payload = {
                action: 'save_route',
                id: route.id,
                origin: route.origin,
                destination: route.destination,
                schedules: updated,
                prices: pricesPayload
            };

            try {
                const res = await fetch('api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                
                if (data.status === 'success') {
                    // Update UI immediately
                    const routeIndex = this.routeConfig.findIndex(r => r.id === route.id);
                    if (routeIndex !== -1) {
                        this.routeConfig[routeIndex].schedules = updated;
                    }
                } else {
                    Swal.fire('Gagal', data.message || 'Gagal menyimpan', 'error');
                }
            } catch (e) {
                console.error(e);
                Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
            } finally {
                this.loadingSchedules = false;
            }
        }
    },
    computed: {
        currentViewTitle() { return "Booking Pending"; },
        todayRevenue() { const today = this.manifestDate; return this.bookings.filter(b => b.date === today && b.status !== 'Batal').reduce((a,b) => a + (b.totalPrice||0), 0); },
        todayPax() { const today = this.manifestDate; return this.bookings.filter(b => b.date === today && b.status !== 'Batal').length; },
        pendingValidationCount() { return this.bookings.filter(b => b.validationStatus === 'Menunggu Validasi').length; },

        getFilteredBookings() {
            let items = [];
            // Pending Page: Show all bookings awaiting confirmation (Menunggu Konfirmasi)
            // This includes: Antrian status (New), Pending (Legacy), Menunggu Validasi, or incomplete payment
            items = this.bookings.filter(b => 
                ['Travel','Carter','Dropping'].includes(b.serviceType) && 
                // Only Antrian (Waiting) should be here. Pending is now "Accepted".
                b.status === 'Antrian'
            );
            
            // Filter: Search
            if(this.busSearchTerm) {
                const term = this.busSearchTerm.toLowerCase();
                items = items.filter(b => 
                    (b.passengerName?.toLowerCase().includes(term)) || 
                    (b.passengerPhone?.includes(term)) ||
                    (b.id?.toString().includes(term)) || 
                    (b.routeId?.toLowerCase().includes(term)) || 
                    (b.seatNumbers?.toString().includes(term)) || 
                    (b.pickupAddress?.toLowerCase().includes(term)) || 
                    (b.dropoffAddress?.toLowerCase().includes(term)) 
                );
            }

            // Filter: Date
            if(this.filterDate) {
                items = items.filter(b => b.date === this.filterDate);
            }
            
            // Sort
            if (this.filterSort === 'Newest') {
                return items.sort((a,b) => new Date(b.id) - new Date(a.id));
            } else {
                return items.sort((a,b) => new Date(a.id) - new Date(b.id)); // Oldest
            }
        }
    }
}).mount("#app");
console.log('App Pending logic loaded');
