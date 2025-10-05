const { createApp } = Vue;

createApp({
    data() {
        return {
            view: 'dashboard', // dashboard, inventaris, history, display, admin
            armadaSearchTerm: '',
            historySearchTerm: '',
            now: new Date(), // Reactive property for real-time updates
            currentTime: this.formatTime(new Date()),
            currentDate: this.getCurrentDate(),
            clockInterval: null,
            isTripModalVisible: false,
            isVehicleModalVisible: false,
            modal: { mode: 'add', trip: null },
            vehicleModal: { mode: 'add', data: null },
            pricesSavedMessage: '',
            fleet: [
                { id: 1, name: 'Hiace Premio SR-01', type: 'Hiace Premio', plate: 'BA 1001 SR', capacity: 7, status: 'Tersedia', icon: 'bi-truck-front-fill', hargaSewa: 1600000, biayaOperasional: 600000 },
                { id: 2, name: 'Hiace Commuter SR-02', type: 'Hiace Commuter', plate: 'BA 1002 SR', capacity: 7, status: 'Tersedia', icon: 'bi-truck-front-fill', hargaSewa: 1500000, biayaOperasional: 550000 },
                { id: 3, name: 'Hiace Premio SR-03', type: 'Hiace Premio', plate: 'BA 1003 SR', capacity: 7, status: 'Tersedia', icon: 'bi-truck-front-fill', hargaSewa: 1600000, biayaOperasional: 600000 },
                { id: 4, name: 'Medium Bus SR-21', type: 'Medium Bus', plate: 'BA 7021 SR', capacity: 33, status: 'Tersedia', icon: 'bi-bus-front-fill', hargaSewa: 2800000, biayaOperasional: 1100000 },
                { id: 5, name: 'Medium Bus SR-22', type: 'Medium Bus', plate: 'BA 7022 SR', capacity: 33, status: 'Perbaikan', icon: 'bi-bus-front-fill', hargaSewa: 2800000, biayaOperasional: 1100000 },
                { id: 6, name: 'Medium Bus SR-23', type: 'Medium Bus', plate: 'BA 7023 SR', capacity: 33, status: 'Tersedia', icon: 'bi-bus-front-fill', hargaSewa: 2800000, biayaOperasional: 1100000 },
                { id: 7, name: 'Big Bus SR-31', type: 'Big Bus', plate: 'BA 7031 SR', capacity: 45, status: 'Tersedia', icon: 'bi-bus-front-fill', hargaSewa: 4000000, biayaOperasional: 1500000 },
                { id: 8, name: 'Big Bus SR-32', type: 'Big Bus', plate: 'BA 7032 SR', capacity: 45, status: 'Tersedia', icon: 'bi-bus-front-fill', hargaSewa: 4000000, biayaOperasional: 1500000 },
            ],
            trips: [
                { id: 1, fleetId: 1, type: 'Rute', origin: 'Padang', destination: 'Bukittinggi', departureTime: this.getTodayISO(8, 0), arrivalTime: this.getTodayISO(10, 30), pax: '7/7', assignee: 'Budi', status: 'Berangkat' },
                { id: 2, fleetId: 4, type: 'Rental', notes: 'Dinas Pariwisata - Tour de Singkarak', departureTime: this.getTodayISO(7, 30), arrivalTime: this.getTodayISO(18, 0), pax: 'Carter', assignee: 'Joko', status: 'Rental' },
                { id: 3, fleetId: 2, type: 'Rute', origin: 'Padang', destination: 'Payakumbuh', departureTime: this.getTodayISO(10, 0), arrivalTime: this.getTodayISO(13, 0), pax: '5/7', assignee: 'Anton', status: 'Boarding' },
                { id: 4, fleetId: 7, type: 'Rental', notes: 'Rombongan Kemenkes RI', departureTime: this.getTodayISO(9, 0), arrivalTime: this.getTomorrowISO(17, 0), pax: 'Carter', assignee: 'Eko', status: 'Rental' },
                { id: 5, fleetId: 8, type: 'Rute', origin: 'Bukittinggi', destination: 'Padang', departureTime: this.getTodayISO(14, 0), arrivalTime: this.getTodayISO(16, 30), pax: '40/45', assignee: 'Slamet', status: 'Berangkat' },
                { id: 6, fleetId: 3, type: 'Rute', origin: 'Padang', destination: 'Solok', departureTime: this.getTodayISO(16, 0), arrivalTime: this.getTodayISO(18, 0), pax: 'Full', assignee: 'Doni', status: 'On Time' },
                { id: 7, fleetId: 1, type: 'Rute', origin: 'Painan', destination: 'Padang', departureTime: this.getYesterdayISO(18, 0), arrivalTime: this.getTodayISO(8, 30), pax: '6/7', assignee: 'Budi', status: 'Tiba' },
            ],
            locations: [{ name: 'Padang', code: 'PDG' }, { name: 'Bukittinggi', code: 'BKT' }, { name: 'Payakumbuh', code: 'PYK' }, { name: 'Solok', code: 'SLK' }, { name: 'Pariaman', code: 'PRM' }, { name: 'Painan', code: 'PNN' }, { name: 'Sawahlunto', code: 'SWL' }, { name: 'Batusangkar', code: 'BTSK' }],
            statuses: ['On Time', 'Boarding', 'Berangkat', 'Tiba', 'Tertunda', 'Batal', 'Rental'],
            statusClasses: { 'On Time': 'bg-green-100 text-green-800', 'Boarding': 'bg-yellow-100 text-yellow-800 animate-pulse', 'Berangkat': 'bg-blue-100 text-blue-800', 'Tiba': 'bg-gray-200 text-gray-800', 'Tertunda': 'bg-red-100 text-red-800', 'Batal': 'bg-black text-white', 'Rental': 'bg-purple-100 text-purple-800' },
        };
    },
    computed: {
        currentViewTitle() { const titles = { dashboard: 'Dashboard Operasional', inventaris: 'Manajemen Inventaris Armada', history: 'Riwayat Perjalanan', display: 'Layar Informasi Publik', admin: 'Pengaturan Admin' }; return titles[this.view] || 'Sutan Raya'; },
        tripsWithFleet() { return this.trips.map(trip => { const originInfo = this.locations.find(l => l.name === trip.origin); const destinationInfo = this.locations.find(l => l.name === trip.destination); return { ...trip, fleet: this.fleet.find(f => f.id === trip.fleetId) || {}, originCode: trip.type === 'Rute' ? (originInfo ? originInfo.code : 'N/A') : 'RENTAL', destinationCode: trip.type === 'Rute' ? (destinationInfo ? destinationInfo.code : 'N/A') : 'RENTAL' } }); },
        activeTrips() { return this.tripsWithFleet.filter(t => t.status !== 'Tiba' && t.status !== 'Batal'); },
        departures() { return this.activeTrips.filter(t => t.origin === 'Padang' && t.type !== 'Rental').sort((a, b) => new Date(a.departureTime) - new Date(b.departureTime)); },
        arrivals() { return this.activeTrips.filter(t => t.destination === 'Padang').sort((a, b) => new Date(a.arrivalTime) - new Date(b.arrivalTime)); },
        rental() {
             return this.activeTrips
                .filter(t => t.type === 'Rental')
                .map(t => ({ // Pre-calculate total duration for efficiency
                    ...t,
                    totalDurationFormatted: this.calculateTotalDuration(t.departureTime, t.arrivalTime)
                }));
        },
        fleetInOperationIds() { return new Set(this.activeTrips.map(t => t.fleetId)); },
        armadaStandby() { return this.fleet.filter(f => !this.fleetInOperationIds.has(f.id) && f.status === 'Tersedia'); },
        armadaKendala() { return this.fleet.filter(f => f.status === 'Perbaikan'); },
        filteredFleet() { if (!this.armadaSearchTerm) return this.fleet; const term = this.armadaSearchTerm.toLowerCase(); return this.fleet.filter(v => v.name.toLowerCase().includes(term) || v.plate.toLowerCase().includes(term) || v.status.toLowerCase().includes(term)); },
        filteredHistory() { const historyTrips = this.tripsWithFleet.filter(t => t.status === 'Tiba' || t.status === 'Batal').sort((a, b) => new Date(b.departureTime) - new Date(a.departureTime)); if (!this.historySearchTerm) return historyTrips; const term = this.historySearchTerm.toLowerCase(); return historyTrips.filter(t => t.fleet.name.toLowerCase().includes(term) || (t.origin && t.origin.toLowerCase().includes(term)) || (t.destination && t.destination.toLowerCase().includes(term)) || (t.notes && t.notes.toLowerCase().includes(term))); },
        upcomingDeparturesForDisplay() { return this.tripsWithFleet.filter(t => (t.origin === 'Padang' || t.type === 'Rental') && t.status !== 'Tiba' && t.status !== 'Batal' && t.status !== 'Berangkat').sort((a, b) => new Date(a.departureTime) - new Date(b.departureTime)); },
        upcomingArrivalsForDisplay() { return this.tripsWithFleet.filter(t => t.destination === 'Padang' && t.status !== 'Tiba' && t.status !== 'Batal').sort((a, b) => new Date(a.arrivalTime) - new Date(b.arrivalTime)); }
    },
    methods: {
        formatRupiah(number) { return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number); },
        getCurrentDate() { return new Date().toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }); },
        formatTime(date) { const d = new Date(date); if (isNaN(d)) return '--:--'; return d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }); },
        formatFullDate(isoString) { if (!isoString) return '-'; return new Date(isoString).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }); },
        formatDateTime(isoString) { if (!isoString) return '-'; const d = new Date(isoString); return `${d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short' })} ${this.formatTime(d)}`; },
        getTodayISO(h, m) { const d = new Date(); d.setHours(h, m, 0, 0); return d.toISOString().slice(0, 16); },
        getYesterdayISO(h, m) { const d = new Date(); d.setDate(d.getDate() - 1); d.setHours(h, m, 0, 0); return d.toISOString().slice(0, 16); },
        getTomorrowISO(h, m) { const d = new Date(); d.setDate(d.getDate() + 1); d.setHours(h, m, 0, 0); return d.toISOString().slice(0, 16); },
        calculateTotalDuration(start, end) {
            const startDate = new Date(start); const endDate = new Date(end);
            let diff = (endDate.getTime() - startDate.getTime()) / 1000;
            if (diff <= 0) return '0 menit';
            const days = Math.floor(diff / 86400); diff -= days * 86400;
            const hours = Math.floor(diff / 3600) % 24; diff -= hours * 3600;
            const minutes = Math.floor(diff / 60) % 60;
            let result = '';
            if (days > 0) result += `${days} hari `;
            if (hours > 0) result += `${hours} jam `;
            if (minutes > 0) result += `${minutes} mnt`;
            return result.trim();
        },
        calculateElapsedTime(start) {
            const startDate = new Date(start);
            if (this.now < startDate) return 'Belum Mulai';
            let diff = (this.now.getTime() - startDate.getTime()) / 1000;
            const hours = Math.floor(diff / 3600); diff -= hours * 3600;
            const minutes = Math.floor(diff / 60) % 60; diff -= minutes * 60;
            const seconds = Math.floor(diff % 60);
            return [hours, minutes, seconds].map(v => v < 10 ? "0" + v : v).join(":");
        },
        calculateProgress(trip) {
            const start = new Date(trip.departureTime);
            const end = new Date(trip.arrivalTime);
            if (this.now < start) return 0;
            if (this.now >= end) return 100;
            const total = end.getTime() - start.getTime();
            if (total <= 0) return 100;
            const elapsed = this.now.getTime() - start.getTime();
            return (elapsed / total) * 100;
        },
        openTripModal(trip, standbyFleetId = null) { if (trip) { this.modal.mode = 'edit'; this.modal.trip = JSON.parse(JSON.stringify(trip)); } else { this.modal.mode = 'add'; this.modal.trip = { id: Date.now(), fleetId: standbyFleetId || '', type: 'Rute', origin: 'Padang', destination: 'Bukittinggi', notes: '', departureTime: this.getTodayISO(new Date().getHours() + 1, 0), arrivalTime: this.getTodayISO(new Date().getHours() + 3, 0), pax: '', assignee: '', status: 'On Time', }; } this.isTripModalVisible = true; },
        closeTripModal() { this.isTripModalVisible = false; this.modal.trip = null; },
        saveTrip() { if (!this.modal.trip.fleetId) { alert('Silakan pilih armada terlebih dahulu.'); return; } if (this.modal.mode === 'add') { this.trips.push(this.modal.trip); } else { const index = this.trips.findIndex(t => t.id === this.modal.trip.id); if (index !== -1) { this.trips[index] = this.modal.trip; } } this.closeTripModal(); },
        deleteTrip(tripId) { if (confirm('Apakah Anda yakin ingin menghapus jadwal perjalanan ini?')) { this.trips = this.trips.filter(t => t.id !== tripId); this.closeTripModal(); } },
        updateTripStatus(tripId, newStatus) { const index = this.trips.findIndex(t => t.id === tripId); if (index !== -1) { this.trips[index].status = newStatus; this.modal.trip.status = newStatus; } },
        isFleetOnTrip(fleetId, currentTripId) { return this.activeTrips.some(t => t.fleetId === fleetId && t.id !== currentTripId); },
        openVehicleModal(vehicle) { if (vehicle) { this.vehicleModal.mode = 'edit'; this.vehicleModal.data = JSON.parse(JSON.stringify(vehicle)); } else { this.vehicleModal.mode = 'add'; this.vehicleModal.data = { id: Date.now(), name: '', type: 'Hiace Premio', plate: '', capacity: 7, status: 'Tersedia', icon: 'bi-truck-front-fill', hargaSewa: 1600000, biayaOperasional: 600000 }; } this.isVehicleModalVisible = true; },
        closeVehicleModal() { this.isVehicleModalVisible = false; this.vehicleModal.data = null; },
        saveVehicle() { if (this.vehicleModal.data.type.toLowerCase().includes('bus')) { this.vehicleModal.data.icon = 'bi-bus-front-fill'; } else { this.vehicleModal.data.icon = 'bi-truck-front-fill'; } if (this.vehicleModal.mode === 'add') { this.fleet.push(this.vehicleModal.data); } else { const index = this.fleet.findIndex(f => f.id === this.vehicleModal.data.id); if (index !== -1) { this.fleet[index] = this.vehicleModal.data; } } this.closeVehicleModal(); },
        savePrices() { this.pricesSavedMessage = 'Harga berhasil diperbarui!'; setTimeout(() => { this.pricesSavedMessage = ''; }, 3000); }
    },
    mounted() {
        this.clockInterval = setInterval(() => {
            this.now = new Date(); // Update 'now' every second for reactivity
            this.currentTime = this.formatTime(this.now);
        }, 1000);
    },
    beforeUnmount() {
        clearInterval(this.clockInterval);
    }
}).mount('#app');