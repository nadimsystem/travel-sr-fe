// Billing Management JavaScript
// Handle payment management, outstanding bookings, and billing reports

document.addEventListener('DOMContentLoaded', function() {
    if (window.initialView !== 'penagihan') return;
    
    const { createApp } = Vue;
    
    createApp({
        data() {
            return {
                isDarkMode: localStorage.getItem('darkMode') === 'true',
                currentDate: '',
                currentTime: '',
                activeTab: 'outstanding', // Default tab
                searchTerm: '',
                dateFilter: 'month', // 'all', 'month', 'today'
                quickFilter: null, // 'unpaid', 'overdue', 'dp', 'validation'
                
                // Pagination
                itemsPerPage: 10,
                currentPage: 1,
                
                // Breakdown Modal
                isBreakdownModalVisible: false,
                breakdownTitle: '',
                breakdownItems: [],
                breakdownTotal: 0,
                
                // Data
                // stats: replaced by computedStats
                outstandingBookings: [],
                recentPayments: [],
                
                // Modal
                isPaymentModalVisible: false,
                activeBooking: null,
                receiverList: ['Irma', 'Salma', 'Havid', 'Fanny', 'Bella', 'Ervan'],
                receiverSelect: '',
                locationList: ['Padang', 'Bukittinggi', 'Payakumbuh'],
                locationSelect: '',
                paymentForm: {
                    amount: 0,
                    payment_method: 'Cash',
                    payment_location: '',
                    payment_receiver: '',
                    payment_proof: '',
                    notes: ''
                }
            };
        },
        watch: {
            activeTab() {
                this.currentPage = 1;
            }
        },
        computed: {
            filterDateRange() {
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                
                if (this.dateFilter === 'today') {
                    const endOfDay = new Date(today);
                    endOfDay.setHours(23, 59, 59, 999);
                    return { start: today, end: endOfDay };
                } else if (this.dateFilter === 'month') {
                    const startOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
                    const endOfMonth = new Date(today.getFullYear(), today.getMonth() + 1, 0, 23, 59, 59, 999);
                    return { start: startOfMonth, end: endOfMonth };
                }
                return null; // 'all' - no filter
            },
            
            // Computed items for calculations (filtered by date, ignoring search for stats generally, or should it follow search? 
            // Usually dashboard stats follow the major filters (Date) but not necessarily the search bar (which is for finding rows).
            // I will match 'filteredOutstanding' logic regarding DATE, but ignore SEARCH for the Top Cards, to keep them stable as 'Period Stats'.
            itemsForStats() {
                let items = [...this.outstandingBookings];
                
                // Apply date filter ONLY
                if (this.filterDateRange) {
                    items = items.filter(b => {
                        const bookingDate = new Date(b.date);
                        return bookingDate >= this.filterDateRange.start && bookingDate <= this.filterDateRange.end;
                    });
                }
                return items;
            },

            computedStats() {
                const items = this.itemsForStats;
                
                const stats = {
                    total_outstanding: 0,
                    total_outstanding_count: 0,
                    total_dp: 0,
                    total_dp_count: 0,
                    total_overdue: 0,
                    total_overdue_count: 0,
                    total_unvalidated_count: 0
                };
                
                const today = new Date();
                today.setHours(0,0,0,0);

                // Helper to generate formula string
                const generateFormula = (items, amountField) => {
                    const groups = {};
                    items.forEach(item => {
                        const amount = parseFloat(item[amountField]) || 0;
                        if (amount > 0) {
                            groups[amount] = (groups[amount] || 0) + 1;
                        }
                    });

                    // Sort by count desc, then amount desc
                    const sortedAmounts = Object.keys(groups).map(Number).sort((a, b) => groups[b] - groups[a] || b - a);
                    
                    const chunks = sortedAmounts.map(amount => {
                        const count = groups[amount];
                        // Format: ( 20 ) 150K
                        const amtStr = (amount >= 1000 ? (amount/1000) + 'K' : amount);
                        return `( ${count} ) ${amtStr}`;
                    });

                    return chunks.join(' + ');
                };

                // 1. Unpaid
                const unpaidItems = items.filter(b => (parseFloat(b.remaining_amount) || 0) > 100);
                stats.total_outstanding_count = unpaidItems.length;
                stats.total_outstanding = unpaidItems.reduce((sum, b) => sum + (parseFloat(b.remaining_amount) || 0), 0);
                stats.outstanding_formula = generateFormula(unpaidItems, 'remaining_amount');

                // 2. DP
                const dpItems = items.filter(b => (parseFloat(b.downPaymentAmount) || 0) > 0 && (parseFloat(b.remaining_amount) || 0) > 100);
                stats.total_dp_count = dpItems.length;
                stats.total_dp = dpItems.reduce((sum, b) => sum + (parseFloat(b.downPaymentAmount) || 0), 0);
                stats.dp_formula = generateFormula(dpItems, 'downPaymentAmount');

                // 3. Overdue
                const overdueItems = items.filter(b => {
                    const bDate = new Date(b.date);
                    return bDate < today && (parseFloat(b.remaining_amount) || 0) > 100;
                });
                stats.total_overdue_count = overdueItems.length;
                stats.total_overdue = overdueItems.reduce((sum, b) => sum + (parseFloat(b.remaining_amount) || 0), 0);
                stats.overdue_formula = generateFormula(overdueItems, 'remaining_amount');

                // 4. Unvalidated
                stats.total_unvalidated_count = items.filter(b => 
                    b.validationStatus === 'Menunggu Validasi' || 
                    (!b.validationStatus && b.paymentProof)
                ).length;
                
                return stats;
            },
            
            filteredValidation() {
                // Filter for "Menunggu Validasi"
                let items = this.outstandingBookings.filter(b => 
                    (b.validationStatus === 'Menunggu Validasi' || (!b.validationStatus && b.paymentProof))
                );
                
                // Apply date filter
                if (this.filterDateRange) {
                    items = items.filter(b => {
                        const bookingDate = new Date(b.date);
                        return bookingDate >= this.filterDateRange.start && bookingDate <= this.filterDateRange.end;
                    });
                }
                
                // Apply search
                if (this.searchTerm) {
                    const term = this.searchTerm.toLowerCase();
                    items = items.filter(b => 
                        b.passengerName.toLowerCase().includes(term) || 
                        b.passengerPhone.includes(term)
                    );
                }
                
                return items;
            },
            filteredOutstanding() {
                let items = [...this.outstandingBookings];
                
                // Exclude Valid/Lunas items
                items = items.filter(b => b.validationStatus !== 'Valid' && b.paymentStatus !== 'Lunas');
                
                // Apply quick filter
                if (this.quickFilter === 'unpaid') {
                    items = items.filter(b => b.downPaymentAmount === 0);
                } else if (this.quickFilter === 'overdue') {
                    items = items.filter(b => b.days_overdue > 0);
                } else if (this.quickFilter === 'dp') {
                    items = items.filter(b => b.downPaymentAmount > 0 && b.remaining_amount > 0);
                } else if (this.quickFilter === 'validation') {
                    items = items.filter(b => b.validationStatus === 'Menunggu Validasi' || (!b.validationStatus && b.paymentProof));
                }
                
                // Apply date filter
                if (this.filterDateRange) {
                    items = items.filter(b => {
                        const bookingDate = new Date(b.date);
                        return bookingDate >= this.filterDateRange.start && bookingDate <= this.filterDateRange.end;
                    });
                }
                
                // Apply search
                if (this.searchTerm) {
                    const term = this.searchTerm.toLowerCase();
                    items = items.filter(b => 
                        b.passengerName.toLowerCase().includes(term) ||
                        b.passengerPhone.includes(term)
                    );
                }
                
                return items;
            },
            
            // Pagination Computed
            paginatedValidation() {
                const start = (this.currentPage - 1) * this.itemsPerPage;
                return this.filteredValidation.slice(start, start + this.itemsPerPage);
            },
            paginatedOutstanding() {
                const start = (this.currentPage - 1) * this.itemsPerPage;
                return this.filteredOutstanding.slice(start, start + this.itemsPerPage);
            },
            paginatedRecent() {
                const start = (this.currentPage - 1) * this.itemsPerPage;
                return this.recentPayments.slice(start, start + this.itemsPerPage);
            },
            
            // Current list length (for pagination controls)
            currentTotalItems() {
                if (this.activeTab === 'validation') return this.filteredValidation.length;
                if (this.activeTab === 'outstanding') return this.filteredOutstanding.length;
                if (this.activeTab === 'recent') return this.recentPayments.length;
                return 0;
            },
            
            itemStart() {
                if (this.currentTotalItems === 0) return 0;
                return (this.currentPage - 1) * this.itemsPerPage + 1;
            },
            
            itemEnd() {
                const end = this.currentPage * this.itemsPerPage;
                return Math.min(end, this.currentTotalItems);
            },
            
            maxPage() {
                return Math.ceil(this.currentTotalItems / this.itemsPerPage) || 1;
            },
            
            pages() {
                const pages = [];
                const max = this.maxPage;
                const current = this.currentPage;
                
                if (max <= 7) {
                    for (let i = 1; i <= max; i++) pages.push(i);
                } else {
                    if (current <= 4) {
                        for (let i = 1; i <= 5; i++) pages.push(i);
                        pages.push('...');
                        pages.push(max);
                    } else if (current >= max - 3) {
                        pages.push(1);
                        pages.push('...');
                        for (let i = max - 4; i <= max; i++) pages.push(i);
                    } else {
                        pages.push(1);
                        pages.push('...');
                        for (let i = current - 1; i <= current + 1; i++) pages.push(i);
                        pages.push('...');
                        pages.push(max);
                    }
                }
                return pages;
            },
            // ... (rest of computed if any)
        },
        methods: {
            formatDate(dateStr) {
                if (!dateStr) return '-';
                const d = new Date(dateStr);
                return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
            },
            handlePaymentProofUpload(event) {
                const file = event.target.files[0];
                if (!file) return;

                if (file.size > 5 * 1024 * 1024) {
                    alert('Ukuran file terlalu besar (Maks 5MB)');
                    return;
                }

                const reader = new FileReader();
                reader.onload = (e) => {
                    this.paymentForm.payment_proof = e.target.result;
                };
                reader.readAsDataURL(file);
            },
            censorPhone(phone) {
                if (!phone) return '-';
                // Keep first 4, mask next 4, keep rest
                if (phone.length >= 8) {
                   return phone.substring(0, 4) + '****' + phone.substring(8);
                }
                return phone;
            },
            formatDateTime(dateStr) {
                if (!dateStr) return '-';
                const d = new Date(dateStr);
                return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
            },
            toggleDarkMode() {
                this.isDarkMode = !this.isDarkMode;
                document.documentElement.classList.toggle('dark', this.isDarkMode);
                localStorage.setItem('darkMode', this.isDarkMode);
            },
            updateClock() {
                const now = new Date();
                this.currentDate = now.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
                this.currentTime = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
            },
            
            // Quick filter methods
            setQuickFilter(filter) {
                this.quickFilter = this.quickFilter === filter ? null : filter;
                this.activeTab = 'outstanding'; // Switch to outstanding tab
                this.currentPage = 1;
            },
            
            setDateFilter(filter) {
                this.dateFilter = filter;
                this.currentPage = 1;
            },
            
            clearFilters() {
                this.dateFilter = 'month';
                this.quickFilter = null;
                this.searchTerm = '';
                this.currentPage = 1;
            },
            
            setLimit(limit) {
                this.itemsPerPage = limit === 'all' ? 999999 : limit;
                this.currentPage = 1;
            },
            
            changePage(page) {
                if (page >= 1 && page <= this.maxPage) {
                    this.currentPage = page;
                }
            },

            showBreakdown(type) {
                const items = this.itemsForStats;
                const today = new Date();
                today.setHours(0,0,0,0);
                
                let filtered = [];
                let total = 0;
                let title = '';

                if (type === 'outstanding') {
                    title = 'Rincian Belum Lunas';
                    filtered = items.filter(b => (b.remaining_amount || 0) > 100);
                    total = filtered.reduce((sum, b) => sum + (parseFloat(b.remaining_amount) || 0), 0);
                } else if (type === 'dp') {
                    title = 'Rincian DP';
                    // Matching logic from computedStats
                    filtered = items.filter(b => (b.downPaymentAmount || 0) > 0 && (b.remaining_amount || 0) > 100);
                    // For DP stats, we sum the DP amount, not the remaining? 
                    // Wait, the card says "Total DP". So we should sum the DP amount.
                    total = filtered.reduce((sum, b) => sum + (parseFloat(b.downPaymentAmount) || 0), 0);
                } else if (type === 'overdue') {
                    title = 'Rincian Lewat Tanggal';
                    filtered = items.filter(b => {
                        const bDate = new Date(b.date);
                        return bDate < today && (b.remaining_amount || 0) > 100;
                    });
                    total = filtered.reduce((sum, b) => sum + (parseFloat(b.remaining_amount) || 0), 0);
                }

                this.breakdownTitle = title;
                this.breakdownItems = filtered;
                this.breakdownTotal = total;
                this.isBreakdownModalVisible = true;
            },
            
            closeBreakdownModal() {
                this.isBreakdownModalVisible = false;
                this.breakdownItems = [];
            },
            
            async loadBillingData() {
                try {
                    // Load billing report with stats
                    const reportRes = await fetch('api.php?action=get_billing_report', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({})
                    });
                    const reportData = await reportRes.json();
                    
                    if (reportData.status === 'success') {
                        this.stats = reportData.stats;
                        this.recentPayments = reportData.recent_payments;
                    }
                    
                    // Load outstanding bookings
                    const outstandingRes = await fetch('api.php?action=get_outstanding_bookings', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({})
                    });
                    const outstandingData = await outstandingRes.json();
                    
                    if (outstandingData.status === 'success') {
                        this.outstandingBookings = outstandingData.bookings;
                        
                        // Calculate unvalidated stats manually from downloaded bookings if needed
                        // or trust API stats if updated
                        this.stats.total_unvalidated_count = this.outstandingBookings.filter(b => b.validationStatus === 'Menunggu Validasi' || (!b.validationStatus && b.paymentProof)).length;
                    }
                } catch (error) {
                    console.error('Error loading billing data:', error);
                    Swal.fire('Error', 'Gagal memuat data penagihan', 'error');
                }
            },
            
            async validateBooking(booking) {
                const confirm = await Swal.fire({
                    title: 'Konfirmasi Validasi',
                    text: `Validasi pembayaran untuk ${booking.passengerName}?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Validasi',
                    cancelButtonText: 'Batal'
                });
                
                if (!confirm.isConfirmed) return;
                
                try {
                    const res = await fetch('api.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            action: 'validate_payment', // Need to ensure this action exists in API
                            booking_id: booking.id
                        })
                    });
                     // Using add_payment logic or generic update? 
                     // Actually let's use a specific action or update via generic 'update_booking' if available.
                     // Or better, let's create 'validate_payment' in API if missing.
                    
                    // Fallback to update_booking if validate_payment not explicit
                    // ... Checking API ...
                    // Let's assume validate_payment needs to be created or we use add_payment with 0 amount but specific status? 
                    // No, cleaner to have validate_payment.
                
                } catch (error) {
                    // ...
                }
                
                // RE-IMPLEMENTING validateBooking to allow generic usage
                
                 try {
                    const res = await fetch('api.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            action: 'validate_payment',
                            booking_id: booking.id
                        })
                    });
                    
                    const data = await res.json();
                    
                    if (data.status === 'success') {
                         Swal.fire('Berhasil', 'Pembayaran divalidasi', 'success');
                         this.loadBillingData();
                    } else {
                        // Fallback: If action not found, try generic update?
                        // Let's just alert error for now and I will add the API action.
                        Swal.fire('Error', data.message || 'Gagal memvalidasi', 'error');
                    }
                 } catch (error) {
                     console.error(error);
                     Swal.fire('Error', 'Terjadi kesalahan', 'error');
                 }
            },

            openPaymentModal(booking) {
                this.activeBooking = booking;
                this.paymentForm = {
                    amount: booking.remaining_amount,
                    payment_method: 'Cash',
                    payment_location: '',
                    payment_location: '',
                    payment_location: '',
                    payment_receiver: '',
                    notes: ''
                };
                this.receiverSelect = ''; // Reset select
                this.locationSelect = ''; // Reset select
                this.isPaymentModalVisible = true;
            },
            
            closePaymentModal() {
                this.isPaymentModalVisible = false;
                this.activeBooking = null;
            },
            
            async submitPayment() {
                if (!this.paymentForm.amount || this.paymentForm.amount <= 0) {
                    Swal.fire('Error', 'Nominal pembayaran harus lebih dari 0', 'error');
                    return;
                }
                
                if (this.paymentForm.amount > this.activeBooking.remaining_amount) {
                    const confirm = await Swal.fire({
                        title: 'Nominal Lebih Besar',
                        text: 'Nominal pembayaran lebih besar dari sisa tagihan. Lanjutkan?',
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
                                booking_id: this.activeBooking.id,
                                ...this.paymentForm
                            }
                        })
                    });
                    
                    const data = await res.json();
                    
                    if (data.status === 'success') {
                        Swal.fire({
                            title: 'Berhasil!',
                            text: 'Pembayaran berhasil ditambahkan',
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        });
                        
                        this.closePaymentModal();
                        this.loadBillingData(); // Reload data
                    } else {
                        Swal.fire('Error', data.message || 'Gagal menyimpan pembayaran', 'error');
                    }
                } catch (error) {
                    console.error('Error submitting payment:', error);
                    Swal.fire('Error', 'Terjadi kesalahan saat menyimpan pembayaran', 'error');
                }
            }
        },
        mounted() {
            // Apply dark mode
            document.documentElement.classList.toggle('dark', this.isDarkMode);
            
            // Check for URL parameters (e.g. from Dispatcher shortcut)
            const urlParams = new URLSearchParams(window.location.search);
            const searchParam = urlParams.get('search');
            
            if (searchParam) {
                this.searchTerm = decodeURIComponent(searchParam);
                this.dateFilter = 'all'; // Ensure we search across all dates
                this.activeTab = 'outstanding'; // Ensure we are on the list
                
                // Optional: Show toast
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'info',
                    title: 'Filter otomatis diterapkan: ' + this.searchTerm,
                    showConfirmButton: false,
                    timer: 3000
                });
            }

            // Update clock
            this.updateClock();
            setInterval(this.updateClock, 1000);
            
            // Load data
            this.loadBillingData();
        }
    }).mount('#app');
});
