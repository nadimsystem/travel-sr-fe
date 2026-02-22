const { createApp, ref, computed, onMounted, watch } = Vue;

createApp({
    setup() {
        const isDarkMode = ref(localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches));
        const loading = ref(true);
        // Initialize date from URL or default to today
        const urlParams = new URLSearchParams(window.location.search);
        const initialDate = urlParams.get('date') || new Date().toISOString().split('T')[0];
        const selectedDate = ref(initialDate);

        const activeTab = ref('fleet');
        const searchQuery = ref('');
        const isDragging = ref(false);
        const isDraggingOver = ref(null);

        // Data Store
        const routes = ref([]);
        const drivers = ref([]);
        const fleet = ref([]);
        const trips = ref([]);

        // Drag Staging
        const draggedItem = ref(null);
        const draggedType = ref(null);

        // API Helper
        const fetchData = async () => {
            loading.value = true;
            try {
                const res = await fetch(`api.php?action=get_schedule_data&date=${selectedDate.value}`);
                const data = await res.json();
                
                routes.value = data.routes || [];
                drivers.value = data.drivers || [];
                fleet.value = data.fleet || [];
                trips.value = data.trips || [];
                
            } catch (e) {
                console.error("Fetch error", e);
                Swal.fire('Error', 'Gagal memuat data', 'error');
            } finally {
                loading.value = false;
            }
        };

        watch(selectedDate, (newDate) => {
             // Sync URL
             const url = new URL(window.location);
             url.searchParams.set('date', newDate);
             window.history.pushState({}, '', url);
             fetchData();
        });

        onMounted(() => {
            fetchData();
        });

        const selectedRoute = ref('');

        // Computed
        const visibleRoutes = computed(() => {
            const allRoutes = getUniqueRoutes();
            if (!selectedRoute.value) return allRoutes;
            return allRoutes.filter(r => r === selectedRoute.value);
        });

        const filteredDrivers = computed(() => {
            if (!searchQuery.value) return drivers.value;
            const q = searchQuery.value.toLowerCase();
            return drivers.value.filter(d => d.name.toLowerCase().includes(q) || d.phone.includes(q));
        });

        const filteredFleet = computed(() => {
            if (!searchQuery.value) return fleet.value;
            const q = searchQuery.value.toLowerCase();
            return fleet.value.filter(f => f.name.toLowerCase().includes(q) || f.plate.toLowerCase().includes(q));
        });

        // Dark Mode
        const toggleDarkMode = () => {
            isDarkMode.value = !isDarkMode.value;
            if (isDarkMode.value) {
                document.documentElement.classList.add('dark');
                localStorage.theme = 'dark';
            } else {
                document.documentElement.classList.remove('dark');
                localStorage.theme = 'light';
            }
        };

        // Date Logic
        const changeDate = (days) => {
            const d = new Date(selectedDate.value);
            d.setDate(d.getDate() + days);
            selectedDate.value = d.toISOString().split('T')[0];
        };

        // Core Scheduling Logic
        const getUniqueRoutes = () => {
             const map = new Set();
            routes.value.forEach(r => {
                map.add(`${r.origin} - ${r.destination}`);
            });
            return Array.from(map);
        };

        const getSlotsForRoute = (routeKey) => {
             const routeConfig = routes.value.find(r => `${r.origin} - ${r.destination}` === routeKey);
            if (!routeConfig) return [];

            const rawSchedules = routeConfig.schedules || []; 
            
            // Normalize: handle both string "08:00" and object {time: "08:00", hidden: false}
            // Filter out hidden schedules
            const times = rawSchedules
                .map(s => (typeof s === 'object' && s !== null) ? s : { time: s, hidden: false })
                .filter(s => !s.hidden)
                .map(s => s.time);
            
            return times.map(time => {
                const existingTrip = trips.value.find(t => 
                    t.date === selectedDate.value && 
                    t.time === time && 
                    (t.routeConfig?.id === routeConfig.id || (t.routeConfig?.origin === routeConfig.origin && t.routeConfig?.destination === routeConfig.destination))
                );

                return {
                    id: `${routeConfig.id}-${time}`,
                    time: time,
                    route: routeConfig,
                    tripId: existingTrip ? existingTrip.id : null,
                    driver: existingTrip ? existingTrip.driver : null,
                    fleet: existingTrip ? existingTrip.fleet : null,
                    passengers: existingTrip ? existingTrip.passengers : [],
                    rawTrip: existingTrip // Store full reference
                };
            });
        };

        // Drag & Drop
        const onDragStart = (evt, item, type) => {
            draggedItem.value = item;
            draggedType.value = type;
            isDragging.value = true;
            evt.dataTransfer.effectAllowed = 'copy';
            evt.dataTransfer.setData('text/plain', JSON.stringify(item));
        };

        const onDragOver = (slotId) => {
            isDraggingOver.value = slotId;
        };

        const onDragLeave = () => {
            isDraggingOver.value = null;
        };

        const onDrop = async (evt, slot) => {
            isDraggingOver.value = null;
            isDragging.value = false;
            
            if (!draggedItem.value || !draggedType.value) return;

            const item = draggedItem.value;
            const type = draggedType.value;

            // Optimistic Update
            if (type === 'driver') slot.driver = item;
            if (type === 'fleet') slot.fleet = item;

            // Prepare Payload
            const payload = {
                id: slot.tripId || null,
                routeConfig: slot.route,
                date: selectedDate.value,
                time: slot.time,
                status: slot.rawTrip ? slot.rawTrip.status : 'Scheduled',
                passengers: slot.passengers || [],
                driver: type === 'driver' ? item : (slot.driver || null),
                fleet: type === 'fleet' ? item : (slot.fleet || null),
            };

            // Call API
            try {
                const res = await fetch('api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'save_schedule_assignment',
                        data: payload
                    })
                });
                const result = await res.json();
                if (result.status === 'success') {
                    await fetchData();
                } else {
                    throw new Error(result.message);
                }
            } catch (e) {
                Swal.fire('Error', e.message, 'error');
                await fetchData();
            }

            // Cleanup
            draggedItem.value = null;
            draggedType.value = null;
        };

        const removeResource = async (slot, type) => {
            if (type === 'driver') slot.driver = null;
            if (type === 'fleet') slot.fleet = null;

             const payload = {
                id: slot.tripId, 
                routeConfig: slot.route,
                date: selectedDate.value,
                time: slot.time,
                status: slot.rawTrip ? slot.rawTrip.status : 'Scheduled',
                passengers: slot.passengers || [],
                driver: slot.driver,
                fleet: slot.fleet
            };
            
            if (!payload.id) return;

             try {
                const res = await fetch('api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'save_schedule_assignment',
                        data: payload
                    })
                });
                const result = await res.json();
                if (result.status === 'success') {
                    await fetchData();
                }
            } catch (e) {
                console.error(e);
            }
        };

        const duplicateSchedule = async () => {
            const confirmed = await Swal.fire({
                title: 'Duplikat Jadwal?',
                text: "Jadwal hari ini akan disalin ke hari berikutnya (BESOK). Data besok akan ditimpa jika ada.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Duplikat!',
                cancelButtonText: 'Batal'
            });

            if (confirmed.isConfirmed) {
                loading.value = true;
                try {
                    const res = await fetch('api.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            action: 'duplicate_schedule',
                            date: selectedDate.value
                        })
                    });
                    const result = await res.json();
                    if (result.status === 'success') {
                        await Swal.fire('Berhasil', 'Jadwal berhasil diduplikat ke hari besok.', 'success');
                        
                        // Calculate next date
                        const d = new Date(selectedDate.value);
                        d.setDate(d.getDate() + 1);
                        const nextDate = d.toISOString().split('T')[0];
                        
                        // Force Refresh to Next Date
                        window.location.href = `?date=${nextDate}`;
                    } else {
                        throw new Error(result.message);
                    }
                } catch (e) {
                    Swal.fire('Gagal', e.message || 'Terjadi kesalahan saat duplikat', 'error');
                } finally {
                    loading.value = false;
                }
            }
        };

        return {
            isDarkMode, toggleDarkMode,
            loading,
            selectedDate, changeDate,
            activeTab, searchQuery,
            filteredDrivers, filteredFleet,
            getUniqueRoutes, getSlotsForRoute,
            onDragStart, onDragOver, onDragLeave, onDrop,
            isDraggingOver,
            removeResource,
            selectedRoute, visibleRoutes,
            duplicateSchedule
        };
    }
}).mount('#app');
