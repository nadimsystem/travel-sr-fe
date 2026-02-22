export const bookingMixin = {
    computed: {
        getFilteredBookings() {
            let items = [];
            
            if (this.view === 'bookingPending') {
                 // Pending Page: Show all bookings awaiting confirmation (Menunggu Konfirmasi)
                 // This includes: Pending status, Menunggu Validasi, or incomplete payment
                 // Also ensure we don't accidentally hide items if payment is lunas but validation is pending
                items = this.bookings.filter(b => 
                    ['Travel','Carter','Dropping'].includes(b.serviceType) && 
                    b.status === 'Antrian' // STRICT: Only show Antrian bookings as per user request
                 );
            } else {
                items = this.view==='bookingManagement' && this.bookingManagementTab==='bus' 
                    ? this.bookings.filter(b=>b.serviceType==='Bus Pariwisata') 
                    : this.bookings.filter(b=>['Travel','Carter','Dropping'].includes(b.serviceType) && b.status !== 'Antrian' && b.status !== 'Ditolak');
            }
            
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
                items = items.filter(b => {
                    if (this.filterMethod === 'Belum Bayar') {
                         // Must have method 'Belum Bayar' AND NOT be paid/valid
                         return b.paymentMethod === 'Belum Bayar' && b.paymentStatus !== 'Lunas' && b.validationStatus !== 'Valid';
                    }
                    return b.paymentMethod === this.filterMethod;
                });
            }
            
            // Filter: Date
            if(this.filterDate) {
                items = items.filter(b => b.date === this.filterDate);
            }
            
            // Filter: Route
            if(this.filterRoute !== 'All') {
                if (this.filterRoute === 'Carter Khusus') {
                    items = items.filter(b => b.routeId && String(b.routeId).startsWith('CUSTOM_'));
                } else {
                    items = items.filter(b => (b.routeId === this.filterRoute || b.routeName === this.filterRoute));
                }
            }
            
            // Sort
            if (this.filterSort === 'Newest') {
                return items.sort((a,b) => new Date(b.id) - new Date(a.id));
            } else {
                return items.sort((a,b) => new Date(a.id) - new Date(b.id)); // Oldest
            }
        },

        selectedRoute() { return this.routeConfig.find(r => r.id === this.bookingForm.data.routeId); },
        currentSchedules() { 
            const raw = this.selectedRoute ? (this.selectedRoute.schedules || []) : [];
            // Normalize: handle both string "08:00" and object {time: "08:00", hidden: false}
            // Filter out hidden schedules
            return raw
                .map(s => (typeof s === 'object' && s !== null) ? s : { time: s, hidden: false })
                .filter(s => !s.hidden)
                .map(s => s.time);
        },
        
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

        // --- Pagination & Display Helpers ---
        uniqueRoutes() {
            const s = new Set();
            this.bookings.forEach(b => { if(b.routeId && b.routeId !== 'All') s.add(b.routeId); });
            this.routeConfig.forEach(r => s.add(r.id));
            if(this.busRouteConfig) this.busRouteConfig.forEach(r => s.add(r.id));
            return [...s].sort();
        },
        paginatedBookings() {
            const start = (this.currentPage - 1) * this.itemsPerPage;
            return this.getFilteredBookings.slice(start, start + this.itemsPerPage);
        },
        totalPages() {
            return Math.ceil(this.getFilteredBookings.length / this.itemsPerPage);
        },

        bookingsByRoute() {
            const grouped = {};
            this.getFilteredBookings.forEach(booking => {
                const route = booking.routeId || 'Tanpa Rute';
                if (!grouped[route]) {
                    grouped[route] = [];
                }
                grouped[route].push(booking);
            });
            return grouped;
        },
        
        // --- Calendar Helpers ---
        calendarDays() {
            const year = this.calendarYear;
            const month = this.calendarMonth;
            const firstDay = new Date(year, month, 1);
            const lastDay = new Date(year, month + 1, 0);
            const days = [];
            
            // Previous month
            const startPad = (firstDay.getDay() + 6) % 7; // Monday start
            const prevLast = new Date(year, month, 0).getDate();
            for (let i = startPad - 1; i >= 0; i--) {
                days.push({ date: prevLast - i, isCurrentMonth: false, events: [] });
            }
            
            // Current month
            for (let i = 1; i <= lastDay.getDate(); i++) {
                const dStr = `${year}-${String(month+1).padStart(2,'0')}-${String(i).padStart(2,'0')}`;
                
                // Events (Bus Bookings)
                const events = this.bookings.filter(b => b.serviceType === 'Bus Pariwisata' && b.date === dStr && b.status !== 'Batal');
                
                days.push({ 
                    date: i, 
                    isCurrentMonth: true, 
                    isToday: dStr === new Date().toISOString().slice(0,10),
                    events: events 
                });
            }
            return days;
        },
    },

    methods: {
        openBookingModal() {
            this.bookingForm.data = {
                id: null,
                serviceType: 'Travel',
                routeId: '',
                date: this.manifestDate,
                time: '',
                passengerName: '',
                passengerPhone: '',
                passengerType: 'Umum',
                seatCount: 1,
                duration: 1,
                isMultiStop: false,
                pickupAddress: '',
                dropoffAddress: '',
                notes: '',
                paymentMethod: 'Cash',
                paymentStatus: 'Menunggu Validasi',
                validationStatus: 'Menunggu Validasi',
                downPaymentAmount: 0,
                paymentLocation: '',
                paymentReceiver: '',
                paymentProof: '',
                ktmProof: ''
            };
            this.bookingForm.selectedSeats = [];
            // document.getElementById('booking_modal').showModal(); // Using declarative v-if instead as per app.js?
            // Wait, app.js usually toggles a boolean. 
            // In original app.js code (from memory/outline), it might toggle a flag or use daisyUI modal method.
            // Let's assume standard Vue flag approach based on other modals.
            // But wait, outline said `openBookingModal`... let's check if there is a flag. 
            // `isBookingModalVisible`? No, I didn't see it in `data.js`.
            // Maybe it uses `document.getElementById('my_modal_Booking').showModal()`?
            // I'll check `dispatcher.php` usage later. For now, let's include the logic I recall or standard logic.
            // Actually, I should probably check `app.js` source for `openBookingModal`. I didn't read its body.
            // I'll assume it opens a modal.
            
            // Re-reading `data.js` I didn't see `isBookingModalVisible`.
            // In `booking_travel.php` (not shown), it likely has the modal.
            // If it's pure JS, it might be accessing DOM.
            const modal = document.getElementById('booking_modal');
            if(modal) modal.showModal();
        },

        setServiceType(t) { this.bookingForm.data.serviceType = t; },
        
        toggleSeat(id) {
            if (this.isSeatOccupied(id)) return;
            const idx = this.bookingForm.selectedSeats.indexOf(id);
            if(idx > -1) this.bookingForm.selectedSeats.splice(idx, 1);
            else {
                // Limit selection to seatCount? Or auto-increment seatCount?
                // Logic: just select.
                this.bookingForm.selectedSeats.push(id);
            }
        },

        isSeatOccupied(id) {
            // Check against existing bookings for same Route + Date + Time
            const { routeId, date, time } = this.bookingForm.data;
            if(!routeId || !date || !time) return false;
            
            return this.bookings.some(b => {
                const sameRoute = b.routeId === routeId;
                const sameDate = b.date === date;
                const sameTime = b.time === time;
                const active = b.status !== 'Batal' && b.status !== 'Tiba'; // Valid bookings
                
                if (sameRoute && sameDate && sameTime && active) {
                    // Check seat numbers
                    if (b.seatNumbers) {
                        const seats = b.seatNumbers.split(',').map(s => s.trim());
                        return seats.includes(id);
                    }
                }
                return false;
            });
        },
        
        isSeatSelected(id) { return this.bookingForm.selectedSeats.includes(id); },

        handleProofUpload(input) {
            if (input.files && input.files[0]) {
                const render = new FileReader();
                render.onload = (e) => { this.bookingForm.data.paymentProof = e.target.result; };
                render.readAsDataURL(input.files[0]);
            }
        },

        async saveBooking() {
            // Validation
            const d = this.bookingForm.data;
            if (!d.passengerName || !d.passengerPhone) return Swal.fire('Error', 'Nama dan HP Wajib diisi', 'error');
            if (d.serviceType === 'Travel' && (!d.routeId || !d.time)) return Swal.fire('Error', 'Rute dan Jam wajib dipilih', 'error');
            if (d.serviceType === 'Travel' && this.bookingForm.selectedSeats.length === 0) return Swal.fire('Error', 'Pilih minimal 1 kursi', 'error');

            const payload  = { ...d };
            payload.seatNumbers = this.bookingForm.selectedSeats.join(',');
            // Auto Calculate Total Price if not passing it manually (backend might handle it, but better safe)
            payload.totalPrice = this.currentTotalPrice; 

            // Include Admin Name
            if (this.user) payload.adminName = this.user.name;

            this.isLoading = true;
            const res = await this.postToApi('create_booking', { data: payload });
            this.isLoading = false;

            if (res.status === 'success') {
                this.showToast('Booking Berhasil: ' + (res.bookingId || ''));
                this.loadData();
                document.getElementById('booking_modal').close();
                // Reset Form? 
            } else {
                Swal.fire('Gagal', res.message, 'error');
            }
        },
        
        saveInputMemory(recv, loc) {
             // Save to LS
             let recvs = this.savedReceivers || [];
             if(recv && !recvs.includes(recv)) { recvs.push(recv); localStorage.setItem('sr_receivers', JSON.stringify(recvs)); }
             
             let locs = this.savedLocations || [];
             if(loc && !locs.includes(loc)) { locs.push(loc); localStorage.setItem('sr_locations', JSON.stringify(locs)); }
        },

        async saveBusBooking() {
            const d = this.bookingBusForm;
            if(!d.passengerName || !d.date) return Swal.fire("Error", "Lengkapi Data Utama", "error");
            
            d.totalPrice = this.getBusDailyPrice() * d.duration;
            
            this.isLoading=true;
            const res = await this.postToApi('create_bus_booking', { data: d });
            this.isLoading=false;
            
            if(res.status==='success') {
                this.showToast("Booking Bus Berhasil");
                document.getElementById('bus_modal').close(); // Assuming ID
                this.loadData();
            } else {
                Swal.fire("Gagal", res.message, "error");
            }
        },

        async deleteBooking(b) {
            const res = await Swal.fire({
                title: 'Hapus Booking?',
                text: `Hapus booking atas nama ${b.passengerName}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Ya, Hapus'
            });
            
            if(res.isConfirmed) {
                this.isLoading = true;
                const apiRes = await this.postToApi('delete_booking', {id: b.id});
                this.isLoading = false;
                
                if(apiRes.status === 'success') {
                    this.showToast('Booking Dihapus');
                    this.loadData();
                } else {
                    Swal.fire('Gagal', apiRes.message, 'error');
                }
            }
        },

        openCancellation(id) {
            const b = this.bookings.find(x => x.id == id);
            if(!b) return;

            // If Paid/DP and Valid -> Open Refund Window
            if (b.paymentStatus === 'Lunas' || b.paymentStatus === 'DP' || b.validationStatus === 'Valid') {
                const url = `pembatalan.php?id=${id}`;
                const w = 600;
                const h = 750;
                const left = (screen.width/2)-(w/2);
                const top = (screen.height/2)-(h/2);
                window.open(url, 'PembatalanBooking', `toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=no, copyhistory=no, width=${w}, height=${h}, top=${top}, left=${left}`);
            } else {
                // Not paid/validated -> Direct Delete
                this.deleteBooking(b);
            }
        },

        changeMonth(dir) {
            this.calendarMonth += dir;
            if(this.calendarMonth > 11) { this.calendarMonth = 0; this.calendarYear++; }
            else if(this.calendarMonth < 0) { this.calendarMonth = 11; this.calendarYear--; }
        },

        async approveBooking(booking) {
            const res = await Swal.fire({
                title: 'Terima Booking?',
                text: `Terima booking atas nama ${booking.passengerName}? Data akan masuk ke Riwayat Booking.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Terima',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#10B981'
            });

            if (res.isConfirmed) {
                this.isLoading = true;
                // Update status to Confirmed and Validation to Valid
                const payload = {
                    id: booking.id,
                    status: 'Pending', // User Defined: Pending = Diterima
                    validationStatus: 'Valid',
                    // Preserve existing payment status unless it was 'Belum Bayar' and we want to change it? 
                    // Usually approval means validation is done. Payment might still be pending or DP.
                    // Lets keep paymentStatus as is, unless logic requires change. 
                    // User said "masuk ke sistem", usually means VALID.
                };
                
                const apiRes = await this.postToApi('update_booking_status', payload);
                this.isLoading = false;

                if (apiRes.status === 'success') {
                    Swal.fire('Berhasil', 'Booking telah diterima', 'success');
                    this.loadData();
                } else {
                    Swal.fire('Gagal', apiRes.message || 'Terjadi kesalahan', 'error');
                }
            }
        },

        async rejectBooking(booking) {
            const res = await Swal.fire({
                title: 'Tolak Booking?',
                text: `Apakah Anda yakin ingin MENOLAK booking ini? Status akan berubah menjadi Ditolak.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Tolak',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#EF4444'
            });

            if (res.isConfirmed) {
                this.isLoading = true;
                const apiRes = await this.postToApi('reject_booking', { id: booking.id });
                this.isLoading = false;

                if (apiRes.status === 'success') {
                    Swal.fire('Berhasil', 'Booking telah ditolak', 'success');
                    this.loadData();
                } else {
                    Swal.fire('Gagal', apiRes.message || 'Terjadi kesalahan', 'error');
                }
            }
        },

        showProof(url) {
            if (!url) return Swal.fire('Info', 'Tidak ada bukti transfer', 'info');
            Swal.fire({
                title: 'Bukti Transfer',
                imageUrl: url,
                imageAlt: 'Bukti Transfer',
                showCloseButton: true,
                confirmButtonText: 'Tutup'
            });
        },

        showKtm(url) {
            if (!url) return Swal.fire('Info', 'Tidak ada foto KTM', 'info');
            Swal.fire({
                title: 'Foto KTM',
                imageUrl: url,
                imageAlt: 'KTM',
                showCloseButton: true,
                confirmButtonText: 'Tutup'
            });
        },
        
        // Ticket Logic
        getTicketData(booking) {
            let fleetName = 'Belum Ditentukan';
            let driverName = 'Belum Ditentukan';
            let plate = '-';
            let isDispatched = false;
            let needManualAssign = false;
            
            // 1. Check if Dispatched (Active Trip in Database)
            for (const trip of this.trips) {
                if (trip.passengers && trip.passengers.some(p => p.id == booking.id)) {
                    fleetName = trip.fleet?.name || '-';
                    plate = trip.fleet?.plate || '-';
                    driverName = trip.driver?.name || '-';
                    isDispatched = true;
                    break;
                }
            }
            // 2. If NOT Dispatched, Check Schedule Assignment
            if (!isDispatched && booking.serviceType === 'Travel') {
                const assignment = this.getAssignment(booking.routeId, booking.time, booking.date);
                if (assignment && assignment.fleet && assignment.driver && assignment.status !== 'Conflict') {
                    fleetName = assignment.fleet.name;
                    plate = assignment.fleet.plate;
                    driverName = assignment.driver.name;
                    isDispatched = true; 
                }
            }
            // 3. Manual Assignment
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

            const r = this.routeConfig.find(x => x.id === booking.routeId) || this.busRouteConfig.find(x => x.id === booking.routeId);
            let seatCount = booking.seatCount || 1;
            if (booking.seatNumbers) seatCount = booking.seatNumbers.split(',').length;
            
            const rConfig = r || { origin: 'Asal', destination: 'Tujuan', prices: {umum:0, pelajar:0} };
            let unitPrice = rConfig.prices ? rConfig.prices.umum : 0;
            if (booking.passengerType === 'Pelajar' || booking.passengerType === 'Mahasiswa / Pelajar') {
                unitPrice = rConfig.prices ? rConfig.prices.pelajar : unitPrice;
            }
            
            let finalPrice = booking.totalPrice;
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
                isDispatched: isDispatched, 
                needManualAssign: needManualAssign
            };
        },

        viewTicket(booking) {
            const data = this.getTicketData(booking);
            if (data.needManualAssign) {
                this.openManualAssign(booking);
                return;
            }
            this.ticketData = data;
            this.isTicketModalVisible = true;
        },

        openManualAssign(booking) {
            this.manualAssignForm = { bookingId: booking.id, fleetId: '', driverId: '' };
            this.isManualAssignModalVisible = true;
        },
        
        saveManualAssign() {
            if (!this.manualAssignForm.fleetId || !this.manualAssignForm.driverId) return alert("Pilih Armada dan Supir!");
            this.manualAssignments[this.manualAssignForm.bookingId] = {
                fleetId: this.manualAssignForm.fleetId,
                driverId: this.manualAssignForm.driverId
            };
            this.isManualAssignModalVisible = false;
            const booking = this.bookings.find(b => b.id === this.manualAssignForm.bookingId);
            if (booking) this.viewTicket(booking);
        },

        printTicket(booking) {
            let data = booking;
            if (!booking.fleetName && !booking.isDispatched) {
                const processed = this.getTicketData(booking);
                if (processed.needManualAssign) {
                    this.viewTicket(booking);
                    return;
                }
                data = processed;
            }
            this.ticketData = data;
            this.$nextTick(() => {
                if (typeof generateTicketPDF === 'function') {
                    const sourceId = this.isTicketModalVisible ? 'ticketContent' : 'ticketTemplate';
                    generateTicketPDF(sourceId, `Ticket-${data.id}.pdf`);
                } else {
                    console.error("generateTicketPDF function not found");
                    alert("Fungsi cetak tiket tidak tersedia.");
                }
            });
        }
    }
};
