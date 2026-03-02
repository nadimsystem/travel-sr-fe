
const { createApp } = Vue;

const app = createApp({
    data() {
        return {
            view: 'dashboard',
            
            // Data
            bookings: [],
            fleet: [],
            drivers: [],
            availableFleet: [],
            
            // Date State
            selectedDate: new Date().toISOString().slice(0,10),
            dateRange: [],
            
            // Forms
            isBookingModalVisible: false,
            bookingForm: {
                customerName: '',
                customerPhone: '',
                tripDateStart: '',
                durationDays: 1,
                routeDescription: '',
                totalPrice: 0,
                dpAmount: 0,
                fleetId: '',
                driverId: '',
                pickupLocation: '',
                dropoffLocation: '',
                paymentMethod: 'Cash',
                paymentLocation: '',
                paymentReceiver: '',
                paymentStatus: 'Belum Lunas',
                status: 'Confirmed',
                notes: ''
            }
        }
    },
    
    created() {
        this.generateDateRange();
        this.loadInitialData();
        this.loadBookings();
    },
    
    watch: {
        selectedDate() {
            this.loadBookings();
        },
        view(val) {
            if (val === 'fleet' || val === 'drivers') this.loadInitialData();
        }
    },
    
    methods: {
        generateDateRange() {
            const days = [];
            const today = new Date();
            for(let i=-5; i<25; i++) { // -5 days to +25 days
                const d = new Date(today);
                d.setDate(today.getDate() + i);
                days.push({
                    date: d.toISOString().slice(0,10),
                    day: d.getDate(),
                    dayName: d.toLocaleDateString('id-ID', {weekday:'long'})
                });
            }
            this.dateRange = days;
        },
        
        selectDate(date) {
            this.selectedDate = date;
        },
        
        async loadInitialData() {
            // Load Fleet & Drivers
            const resF = await this.post('get_fleet');
            if (resF.status === 'success') this.fleet = resF.data;
            
            const resD = await this.post('get_drivers');
            if (resD.status === 'success') this.drivers = resD.data;
        },
        
        async loadBookings() {
            const res = await this.post('get_bookings', { date: this.selectedDate });
            if (res.status === 'success') {
                this.bookings = res.bookings;
                this.availableFleet = res.availableFleet; // Correctly filtered from backend
            }
        },
        
        openBookingModal() {
            this.bookingForm = {
                customerName: '',
                customerPhone: '',
                tripDateStart: this.selectedDate,
                durationDays: 1,
                routeDescription: '',
                totalPrice: 0,
                dpAmount: 0,
                fleetId: '',
                driverId: '',
                pickupLocation: '',
                dropoffLocation: '',
                paymentMethod: 'Cash',
                paymentLocation: '',
                paymentReceiver: '',
                paymentStatus: 'Belum Lunas',
                status: 'Confirmed',
                notes: ''
            };
            this.isBookingModalVisible = true;
        },
        
        async saveBooking() {
            // Validation
            if (!this.bookingForm.customerName || !this.bookingForm.tripDateStart) {
                return Swal.fire('Error', 'Mohon lengkapi data wajib', 'error');
            }
            
            const res = await this.post('create_booking', { data: this.bookingForm });
            if (res.status === 'success') {
                Swal.fire('Berhasil', 'Booking berhasil dibuat', 'success');
                this.isBookingModalVisible = false;
                this.loadBookings();
            } else {
                Swal.fire('Gagal', res.message || 'Terjadi kesalahan', 'error');
            }
        },
        
        // Utils
        async post(action, data = {}) {
            try {
                const res = await fetch('api.php?action=' + action, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                return await res.json();
            } catch(e) {
                console.error(e);
                return { status: 'error', message: e.message };
            }
        },
        
        formatRupiah(n) {
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(n);
        },
        
        formatDate(d) {
            return new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
        },

        // --- Fleet CRUD ---
        openFleetModal(f = null) {
            const isEdit = !!f;
            Swal.fire({
                title: isEdit ? 'Edit Armada' : 'Tambah Armada',
                html: `
                    <div class="flex flex-col gap-3 text-left">
                        <div>
                            <label class="text-xs font-bold text-slate-500">Nama Bus</label>
                            <input id="swal-name" class="swal2-input !m-0 !w-full" placeholder="Nama Bus" value="${f ? f.name : ''}">
                        </div>
                        <div>
                            <label class="text-xs font-bold text-slate-500">No Polisi</label>
                            <input id="swal-plate" class="swal2-input !m-0 !w-full" placeholder="No Polisi" value="${f ? f.plateNumber : ''}">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-xs font-bold text-slate-500">Kapasitas</label>
                                <input id="swal-cap" type="number" class="swal2-input !m-0 !w-full" placeholder="Seat" value="${f ? f.capacity : ''}">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-500">Harga / Hari</label>
                                <input id="swal-price" type="number" class="swal2-input !m-0 !w-full" placeholder="Rp" value="${f ? f.pricePerDay || 0 : ''}">
                            </div>
                        </div>
                        <div>
                            <label class="text-xs font-bold text-slate-500">Status</label>
                            <select id="swal-status" class="swal2-input !m-0 !w-full">
                                <option value="Tersedia" ${f && f.status==='Tersedia'?'selected':''}>Tersedia</option>
                                <option value="Jalan" ${f && f.status==='Jalan'?'selected':''}>Jalan</option>
                                <option value="Perbaikan" ${f && f.status==='Perbaikan'?'selected':''}>Perbaikan</option>
                            </select>
                        </div>
                    </div>
                `,
                focusConfirm: false,
                preConfirm: () => {
                    return {
                        id: f ? f.id : null,
                        name: document.getElementById('swal-name').value,
                        plateNumber: document.getElementById('swal-plate').value,
                        capacity: document.getElementById('swal-cap').value,
                        pricePerDay: document.getElementById('swal-price').value,
                        status: document.getElementById('swal-status').value
                    }
                }
            }).then(async (result) => {
                if (result.isConfirmed) {
                    const res = await this.post('save_fleet', { data: result.value });
                    if (res.status === 'success') {
                        Swal.fire('Berhasil', 'Armada disimpan', 'success');
                        this.loadInitialData();
                    } else {
                        Swal.fire('Gagal', res.message, 'error');
                    }
                }
            });
        },

        async deleteFleet(id) {
            if (confirm('Hapus Armada ini?')) {
                const res = await this.post('delete_fleet', { id });
                if (res.status === 'success') this.loadInitialData();
            }
        },

        // --- Driver CRUD ---
        openDriverModal(d = null) {
            const isEdit = !!d;
            Swal.fire({
                title: isEdit ? 'Edit Supir' : 'Tambah Supir',
                html: `
                    <div class="flex flex-col gap-3 text-left">
                         <div>
                            <label class="text-xs font-bold text-slate-500">Nama Supir</label>
                            <input id="swal-dname" class="swal2-input !m-0 !w-full" placeholder="Nama Supir" value="${d ? d.name : ''}">
                        </div>
                        <div>
                            <label class="text-xs font-bold text-slate-500">No HP</label>
                            <input id="swal-dphone" class="swal2-input !m-0 !w-full" placeholder="No HP" value="${d ? d.phone : ''}">
                        </div>
                        <div>
                            <label class="text-xs font-bold text-slate-500">Status</label>
                            <select id="swal-dstatus" class="swal2-input !m-0 !w-full">
                                <option value="Standby" ${d && d.status==='Standby'?'selected':''}>Standby</option>
                                <option value="Jalan" ${d && d.status==='Jalan'?'selected':''}>Jalan</option>
                                <option value="Cuti" ${d && d.status==='Cuti'?'selected':''}>Cuti</option>
                            </select>
                        </div>
                    </div>
                `,
                focusConfirm: false,
                preConfirm: () => {
                    return {
                        id: d ? d.id : null,
                        name: document.getElementById('swal-dname').value,
                        phone: document.getElementById('swal-dphone').value,
                        status: document.getElementById('swal-dstatus').value
                    }
                }
            }).then(async (result) => {
                if (result.isConfirmed) {
                    const res = await this.post('save_driver', { data: result.value });
                    if (res.status === 'success') {
                        Swal.fire('Berhasil', 'Supir disimpan', 'success');
                        this.loadInitialData();
                    } else {
                        Swal.fire('Gagal', res.message, 'error');
                    }
                }
            });
        },

        async deleteDriver(id) {
            if (confirm('Hapus Supir ini?')) {
                const res = await this.post('delete_driver', { id });
                if (res.status === 'success') this.loadInitialData();
            }
        }
    }
});

app.mount('#app');
