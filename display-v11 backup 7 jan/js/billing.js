// Billing Management JavaScript
// Handle payment management, outstanding bookings, and billing reports

document.addEventListener('DOMContentLoaded', function() {
    if (window.initialView !== 'billingManagement') return;
    
    const { createApp } = Vue;
    
    createApp({
        data() {
            return {
                isDarkMode: localStorage.getItem('darkMode') === 'true',
                currentDate: '',
                currentTime: '',
                activeTab: 'outstanding',
                searchTerm: '',
                
                // Data
                stats: {
                    total_outstanding: 0,
                    total_outstanding_count: 0,
                    total_dp: 0,
                    total_dp_count: 0,
                    total_overdue: 0,
                    total_overdue_count: 0
                },
                outstandingBookings: [],
                recentPayments: [],
                
                // Modal
                isPaymentModalVisible: false,
                activeBooking: null,
                paymentForm: {
                    amount: 0,
                    payment_method: 'Cash',
                    payment_location: '',
                    payment_receiver: '',
                    notes: ''
                }
            };
        },
        computed: {
            filteredOutstanding() {
                if (!this.searchTerm) return this.outstandingBookings;
                const term = this.searchTerm.toLowerCase();
                return this.outstandingBookings.filter(b => 
                    b.passengerName.toLowerCase().includes(term) ||
                    b.passengerPhone.includes(term)
                );
            }
        },
        methods: {
            formatDate(dateStr) {
                if (!dateStr) return '-';
                const d = new Date(dateStr);
                return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
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
                    }
                } catch (error) {
                    console.error('Error loading billing data:', error);
                    Swal.fire('Error', 'Gagal memuat data penagihan', 'error');
                }
            },
            
            openPaymentModal(booking) {
                this.activeBooking = booking;
                this.paymentForm = {
                    amount: booking.remaining_amount,
                    payment_method: 'Cash',
                    payment_location: '',
                    payment_receiver: '',
                    notes: ''
                };
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
            
            // Update clock
            this.updateClock();
            setInterval(this.updateClock, 1000);
            
            // Load data
            this.loadBillingData();
        }
    }).mount('#app');
});
