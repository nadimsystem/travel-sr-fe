export const reportMixin = {
    computed: {
        reversedLabels() { return [...this.reportData.labels].reverse(); },
        reversedRevenue() { return [...this.reportData.revenue].reverse(); },
        reversedRevenueCash() { return [...(this.reportData.revenueCash || [])].reverse(); },
        reversedRevenueTransfer() { return [...(this.reportData.revenueTransfer || [])].reverse(); },
        reversedPax() { return [...this.reportData.pax].reverse(); },

        manifestReport() {
            // New logic: Report based on bookings (not trips)
            const report = {
                routes: {},
                charters: [],
                charterTotal: { totalPrice: 0, paidAmount: 0, remainingAmount: 0 },
                recap: [],
                grandTotal: { umumPax: 0, umumNominal: 0, pelajarPax: 0, pelajarNominal: 0, totalPax: 0, totalNominal: 0, unpaidAmount: 0 }
            };

            const dailyBookings = this.bookings.filter(b => b.date === this.manifestDate && b.status !== 'Batal');

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

            // testing
            // console.log('report.charters', report.charters);

            // Process Regular Routes
            const regularBookings = dailyBookings.filter(b => b.serviceType === 'Travel');
            
            // Group by Route
            const routeGroups = {};
            regularBookings.forEach(b => {
                let rName = b.routeName || b.routeId || 'Lainnya';
                // Group Custom Routes
                if (b.routeId && String(b.routeId).startsWith('CUSTOM_')) {
                    rName = 'Carter Khusus';
                }
                
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

            // ADD CHARTER TOTAL TO GRAND TOTAL
            if (report.charters.length > 0) {
                report.grandTotal.totalNominal += report.charterTotal.totalPrice;
                report.grandTotal.unpaidAmount += report.charterTotal.remainingAmount;
                // Add passengers from charters only if seatCount is reliable, usually it is
                // report.grandTotal.totalPax += report.charters.reduce((sum, c) => sum + (parseInt(c.seatCount)||1), 0); 
                // But wait, the map above didn't preserve seatCount. Let's fix map above first or just sum from dailyBookings filter
                const charterPax = dailyBookings.filter(b => b.serviceType === 'Carter').reduce((sum, b) => sum + (parseInt(b.seatCount) || 1), 0);
                report.grandTotal.totalPax += charterPax;
            }

            return report;
        },
    },
    
    methods: {
        openDetailModal(type_or_booking) {
            if (typeof type_or_booking === 'string') {
                // Type mode (Income, Passengers, Unpaid)
                const type = type_or_booking;
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
            } else {
                // Booking Object mode
                const b = type_or_booking;
                this.detailModalData = b;
                this.isDetailModalVisible = true;
                this.loadPaymentHistory(b.id); // Auto-load payment history
            }
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
        
        closeDetailModal() {
            // Check which modal is open
            if (this.detailModal.isOpen) {
                this.detailModal.isOpen = false;
            }
            if (this.isDetailModalVisible) {
                this.isDetailModalVisible = false;
                this.detailModalData = null;
                this.paymentHistory = [];
            }
        }
    }
};
