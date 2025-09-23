// app.js
const { createApp, ref, onMounted, computed } = Vue;

const statuses = ["On Time", "Boarding", "Departed", "Delayed", "Arrived", "Cancelled"];
const fleetData = [];

// Simulasi 30 Hiace
for (let i = 1; i <= 30; i++) {
    fleetData.push({ id: `H${i}`, name: `Hiace SR-${i.toString().padStart(2, '0')}`, type: 'Hiace', plate: `BA ${8000 + i} XX` });
}

// Simulasi 11 Bus
for (let i = 1; i <= 11; i++) {
    fleetData.push({ id: `B${i}`, name: `Bus SR-${i.toString().padStart(2, '0')}`, type: i <= 5 ? 'Medium Bus' : 'Big Bus', plate: `BA ${9000 + i} XY` });
}

function generateInitialTrips() {
    const now = new Date();
    const today = (d) => new Date(now.getFullYear(), now.getMonth(), now.getDate(), d.getHours(), d.getMinutes()).toISOString();
    
    return [
        // Rute Reguler
        { id: 1, fleetId: 'H1', type: 'Rute', origin: 'Padang', destination: 'Bukittinggi', departureTime: today(new Date(now.getTime() + 1 * 60 * 60 * 1000)), status: 'On Time' },
        { id: 2, fleetId: 'H2', type: 'Rute', origin: 'Padang', destination: 'Payakumbuh', departureTime: today(new Date(now.getTime() + 2 * 60 * 60 * 1000)), status: 'On Time' },
        { id: 3, fleetId: 'H3', type: 'Rute', origin: 'Bukittinggi', destination: 'Padang', departureTime: today(new Date(now.getTime() + 3 * 60 * 60 * 1000)), status: 'On Time' },
        { id: 4, fleetId: 'B1', type: 'Rute', origin: 'Padang', destination: 'Payakumbuh', departureTime: today(new Date(now.getTime() + 4 * 60 * 60 * 1000)), status: 'On Time' },
        // Rental
        { id: 5, fleetId: 'H4', type: 'Rental', notes: 'Rental Harian - Client A', departureTime: today(new Date(now.getTime() + 5 * 60 * 60 * 1000)), status: 'On Time' },
        { id: 6, fleetId: 'B2', type: 'Rental', notes: 'Rental 3 Hari - Wisata Sekolah', departureTime: today(new Date(now.getTime() + 6 * 60 * 60 * 1000)), status: 'On Time' },
        { id: 7, fleetId: 'B10', type: 'Rental', notes: 'Rental Mingguan - Korporat', departureTime: today(new Date(now.getTime() + 7 * 60 * 60 * 1000)), status: 'On Time' },
    ];
}

const appLogic = {
    setup() {
        // Data refs
        const trips = ref([]);
        const fleet = ref(fleetData);
        const modal = ref({ data: {}, instance: null });
        const statusModal = ref({ instance: null });
        const selectedTrip = ref(null);
        const currentTime = ref('');

        // Methods
        const loadData = () => {
            const savedTrips = localStorage.getItem('sutanRayaTrips');
            trips.value = savedTrips ? JSON.parse(savedTrips) : generateInitialTrips();
            saveData();
        };

        const saveData = () => {
            localStorage.setItem('sutanRayaTrips', JSON.stringify(trips.value));
        };

        const formatTime = (iso) => iso ? new Date(iso).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) : '';

        const getStatusClass = (status, isButton = false) => {
            const statusClass = 'status-' + status.toLowerCase().replace(' ', '');
            if (isButton) {
                const map = { ontime: 'outline-success', boarding: 'warning', departed: 'info', delayed: 'danger', arrived: 'secondary', cancelled: 'dark' };
                return `btn-${map[status.toLowerCase().replace(' ', '')]} btn-status`;
            }
            return {
                'bg-success': status === 'On Time' || status === 'Available',
                'bg-warning text-dark': status === 'Boarding',
                'bg-info text-dark': status === 'Departed',
                'bg-danger': status === 'Delayed' || status === 'Cancelled',
                'bg-secondary': status === 'Arrived',
            };
        };
        
        const updateStatus = (trip, newStatus) => {
            const index = trips.value.findIndex(t => t.id === trip.id);
            if (index !== -1) {
                trips.value[index].status = newStatus;
                saveData();
            }
            if(statusModal.value.instance) statusModal.value.instance.hide();
        };

        const openModal = (fleetForNewTrip) => {
             modal.value.data = { fleetId: fleetForNewTrip.id, type: 'Rute', status: 'On Time', departureTime: new Date().toISOString().slice(0, 16) };
             modal.value.instance.show();
        };
        
        const selectTrip = (trip) => {
            selectedTrip.value = trip;
            statusModal.value.instance.show();
        };

        const saveTrip = () => { /* Logic to save new trip */ };
        
        // Computed Properties
        const activeTrips = computed(() => {
            return trips.value
                .filter(t => !['Arrived', 'Cancelled'].includes(t.status))
                .sort((a, b) => new Date(a.departureTime) - new Date(b.departureTime))
                .map(t => ({ ...t, fleet: fleet.value.find(f => f.id === t.fleetId) }));
        });
        
        const availableFleet = computed(() => {
            const busyFleetIds = activeTrips.value.map(t => t.fleetId);
            return fleet.value.filter(f => !busyFleetIds.includes(f.id));
        });

        // Lifecycle and Timers
        onMounted(() => {
            if (document.getElementById('posApp')) {
                modal.value.instance = new bootstrap.Modal(document.getElementById('tripModal'));
                statusModal.value.instance = new bootstrap.Modal(document.getElementById('statusModal'));
            }
            loadData();
            window.addEventListener('storage', loadData);
            setInterval(() => {
                currentTime.value = new Date().toLocaleTimeString('id-ID');
            }, 1000);
        });

        return { 
            activeTrips, availableFleet, statuses, selectedTrip, currentTime,
            formatTime, getStatusClass, openModal, saveTrip, updateStatus, selectTrip
        };
    }
};

// Mount logic for different pages
if (document.getElementById('posApp')) {
    createApp(appLogic).mount('#posApp');
} else if (document.getElementById('displayApp')) {
    const displayApp = createApp({
        ...appLogic, // Reuse the logic
        setup() {
            const baseSetup = appLogic.setup();
            const departures = computed(() => {
                 return baseSetup.activeTrips.value
                    .filter(t => !['Arrived', 'Cancelled'].includes(t.status))
                    .slice(0, 10);
            });
            return {
                ...baseSetup,
                departures
            };
        }
    }).mount('#displayApp');
    
    function updateClock() {
        const now = new Date();
        document.getElementById('clock').textContent = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
        document.getElementById('date').textContent = now.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long' });
    }
    setInterval(updateClock, 1000);
    updateClock();
}