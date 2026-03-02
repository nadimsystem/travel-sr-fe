
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

    data() {
        return {
            pendingTab: 'antrian'
        }
    },
    
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
        
        // Disable ticking time on this heavy page to improve performance, or make it not reactive to main state
        // this.updateTime(); 
        // setInterval(this.updateTime, 1000);
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
        },

        // --- BANK ACCOUNT MODAL ---
        async openBankModal() {
            this.isBankModalOpen = true;
            this.selectedRouteForBank = null;
            this.editingBankAccounts = [];
            await this.loadRouteBankAccounts();
        },

        async loadRouteBankAccounts() {
            try {
                const res = await fetch('api.php?action=get_route_bank_accounts');
                const data = await res.json();
                if (data.status === 'success') {
                    this.allRouteBankAccounts = data.data || [];
                }
            } catch(e) {
                console.error('Error loading bank accounts', e);
            }
        },

        getRouteBankAccounts(routeId) {
            return this.allRouteBankAccounts.filter(a => a.route_id === routeId);
        },

        selectRouteForBank(route) {
            this.selectedRouteForBank = route;
            // The 3 fixed BCA accounts (always the same)
            const PRESET_ACCOUNTS = [
                { key: 'bca-padang',      bank_name: 'BCA PADANG',      account_number: '7425888781', account_holder: 'PT. Fajar Wisata Langgeng' },
                { key: 'bca-bukittinggi', bank_name: 'BCA BUKITTINGGI', account_number: '7425888722', account_holder: 'PT. Fajar Wisata Langgeng' },
                { key: 'bca-payakumbuh', bank_name: 'BCA PAYAKUMBUH', account_number: '7425888943', account_holder: 'PT. Fajar Wisata Langgeng' },
            ];

            // Load saved config for this route
            const saved = this.getRouteBankAccounts(route.id);

            if (saved.length > 0) {
                // Re-build the list in saved order, with enabled flag
                // Saved records = only the ones that were enabled previously, sort by sort_order
                const sortedSaved = [...saved].sort((a, b) => parseInt(a.sort_order) - parseInt(b.sort_order));

                // Build editingBankAccounts: saved ones first (enabled), then remaining presets (disabled)
                const usedKeys = new Set(sortedSaved.map(s => s.account_number));
                const enabledItems = sortedSaved.map(s => {
                    const preset = PRESET_ACCOUNTS.find(p => p.account_number === s.account_number);
                    return { ...(preset || {}), bank_name: s.bank_name, account_number: s.account_number, account_holder: s.account_holder, enabled: true };
                });
                const disabledItems = PRESET_ACCOUNTS
                    .filter(p => !usedKeys.has(p.account_number))
                    .map(p => ({ ...p, enabled: false }));
                this.editingBankAccounts = [...enabledItems, ...disabledItems];
            } else {
                // No saved config: show all 3 defaults as disabled
                this.editingBankAccounts = PRESET_ACCOUNTS.map(p => ({ ...p, enabled: false }));
            }
        },

        onBankDragStart(idx) {
            this._bankDragIdx = idx;
        },

        onBankDragOver(idx) {
            this.dragOverIndex = idx;
        },

        onBankDrop(targetIdx) {
            if (this._bankDragIdx === undefined || this._bankDragIdx === targetIdx) return;
            const items = [...this.editingBankAccounts];
            const [moved] = items.splice(this._bankDragIdx, 1);
            items.splice(targetIdx, 0, moved);
            this.editingBankAccounts = items;
            this._bankDragIdx = undefined;
            this.dragOverIndex = null;
        },

        async saveBankAccounts() {
            if (!this.selectedRouteForBank) return;
            this.isSavingBank = true;
            try {
                // Only save the enabled ones, in current order
                const accountsToSave = this.editingBankAccounts
                    .filter(a => a.enabled)
                    .map((a, i) => ({
                        bank_name: a.bank_name,
                        account_number: a.account_number,
                        account_holder: a.account_holder,
                        sort_order: i
                    }));

                const payload = {
                    action: 'save_route_bank_accounts',
                    route_id: this.selectedRouteForBank.id,
                    accounts: accountsToSave
                };
                const res = await fetch('api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data.status === 'success') {
                    await this.loadRouteBankAccounts();
                    Swal.fire({ icon: 'success', title: 'Tersimpan!', text: 'Rekening berhasil diperbarui.', timer: 1500, showConfirmButton: false });
                } else {
                    Swal.fire('Gagal', data.message || 'Gagal menyimpan', 'error');
                }
            } catch(e) {
                Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
            } finally {
                this.isSavingBank = false;
            }
        }
    },
    computed: {
        currentViewTitle() { return "Booking Pending"; },
        todayRevenue() { const today = this.manifestDate; return this.bookings.filter(b => b.date === today && b.status !== 'Batal').reduce((a,b) => a + (b.totalPrice||0), 0); },
        todayPax() { const today = this.manifestDate; return this.bookings.filter(b => b.date === today && b.status !== 'Batal').length; },
        pendingValidationCount() { return this.bookings.filter(b => b.validationStatus === 'Menunggu Validasi').length; },

        getRejectedBookings() {
            return this.bookings.filter(b => b.status === 'Cancelled' && b.validationStatus === 'Ditolak')
                                .sort((a,b) => new Date(b.id) - new Date(a.id));
        },

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
