// js/dashboard.js

document.addEventListener('DOMContentLoaded', () => {
    loadDashboardData();
    setInterval(loadDashboardData, 30000); // Auto refresh
    updateTime();
    setInterval(updateTime, 1000);

    // Event Listeners
    document.getElementById('toggleDarkModeBtn').addEventListener('click', toggleDarkMode);
    document.getElementById('toggleFullscreenBtn').addEventListener('click', toggleFullscreen);
});

async function loadDashboardData() {
    const data = await fetchData();
    if (!data) return;

    const bookings = data.bookings || [];
    const trips = data.trips || [];
    const fleet = data.fleet || [];
    const busBookings = data.busRoutes ? [] : []; // Placeholder if needed

    // 1. Update Stats
    updateStats(bookings, busBookings);

    // 2. Update Trip Lists
    renderOutboundTrips(trips);
    renderInboundTrips(trips);

    // 3. Update Fleet Status
    renderFleetStatus(fleet);
}

function updateStats(bookings, busBookings) {
    const today = new Date().toISOString().slice(0, 10);
    
    // Revenue
    const todayRevenue = bookings
        .filter(b => b.date === today && b.status !== 'Batal')
        .reduce((a, b) => a + (parseFloat(b.totalPrice) || 0), 0);
    document.getElementById('todayRevenue').textContent = formatRupiah(todayRevenue);

    // Order Count
    const todayPax = bookings.filter(b => b.date === today && b.status !== 'Batal').length;
    document.getElementById('todayOrderCount').textContent = `${todayPax} Orang`;

    // Pending Validation
    const pendingValidationCount = bookings.filter(b => b.validationStatus === 'Menunggu Validasi').length;
    document.getElementById('pendingValidationCount').textContent = pendingValidationCount;
    
    // Dispatch Queue (Logic from app.js)
    const readyBookings = bookings.filter(b => 
        b.status === 'Pending' && 
        b.validationStatus === 'Valid' && 
        ['Lunas', 'DP'].includes(b.paymentStatus)
    );
    // Simple grouping count for now
    document.getElementById('pendingDispatchCount').textContent = readyBookings.length > 0 ? '!' : '0'; 
}

function renderOutboundTrips(trips) {
    const container = document.getElementById('outboundTripsList');
    container.innerHTML = '';

    // Filter outbound: Route ID contains PDG- OR Origin is Padang
    const outbound = trips.filter(t => {
        if (['Tiba', 'Batal'].includes(t.status)) return false;
        const routeId = t.routeConfig?.routeId || '';
        const origin = t.routeConfig?.origin || '';
        return routeId.includes('PDG-') || origin.toLowerCase().includes('padang');
    });

    if (outbound.length === 0) {
        container.innerHTML = '<div class="h-full flex flex-col items-center justify-center text-slate-300 dark:text-slate-600"><i class="bi bi-slash-circle text-2xl mb-1"></i><span class="text-xs">Kosong</span></div>';
        return;
    }

    outbound.forEach(trip => {
        const el = createTripCard(trip);
        container.appendChild(el);
    });
}

function renderInboundTrips(trips) {
    const container = document.getElementById('inboundTripsList');
    container.innerHTML = '';

    // Filter inbound: Route ID does NOT contain PDG- AND Origin is NOT Padang
    const inbound = trips.filter(t => {
        if (['Tiba', 'Batal'].includes(t.status)) return false;
        const routeId = t.routeConfig?.routeId || '';
        const origin = t.routeConfig?.origin || '';
        return !routeId.includes('PDG-') && !origin.toLowerCase().includes('padang');
    });

    if (inbound.length === 0) {
        container.innerHTML = '<div class="h-full flex flex-col items-center justify-center text-slate-300 dark:text-slate-600"><i class="bi bi-slash-circle text-2xl mb-1"></i><span class="text-xs">Kosong</span></div>';
        return;
    }

    inbound.forEach(trip => {
        const el = createTripCard(trip);
        container.appendChild(el);
    });
}

function createTripCard(trip) {
    const div = document.createElement('div');
    div.className = 'p-3 rounded-lg border border-slate-100 dark:border-slate-700 bg-white dark:bg-slate-700 hover:shadow-md transition-all group relative';
    
    let statusBadgeColor = 'bg-blue-500';
    if(trip.status === 'Jalan') statusBadgeColor = 'bg-blue-500';
    else if(trip.status === 'Kendala') statusBadgeColor = 'bg-red-500';
    else if(trip.status === 'Tiba') statusBadgeColor = 'bg-green-500';

    // Ensure passengers is an array
    let passengers = [];
    if (Array.isArray(trip.passengers)) {
        passengers = trip.passengers;
    } else if (trip.passengers && typeof trip.passengers === 'object') {
        // Handle case where it might be an object with numeric keys (JSON object)
        passengers = Object.values(trip.passengers);
    }
    
    // Calculate total passengers by summing seatCount of each booking
    const passengerCount = passengers.reduce((total, p) => total + (parseInt(p.seatCount) || 1), 0);
    const capacity = trip.fleet?.capacity || 7;

    // Generate passenger list HTML for popover
    let passengerListHtml = '';
    if (passengers.length > 0) {
        passengerListHtml = `
            <div class="absolute left-0 bottom-full mb-2 w-64 bg-white dark:bg-slate-800 rounded-lg shadow-xl border border-slate-200 dark:border-slate-600 p-3 z-50 hidden group-hover:block animate-fade-in">
                <div class="text-[10px] font-bold text-slate-400 uppercase mb-2 border-b border-slate-100 dark:border-slate-700 pb-1 flex justify-between">
                    <span>Daftar Penumpang</span>
                    <span>${passengerCount}/${capacity}</span>
                </div>
                <div class="space-y-2 max-h-[200px] overflow-y-auto custom-scrollbar">
                    ${passengers.map(p => `
                        <div class="flex justify-between items-start text-xs border-b border-slate-50 dark:border-slate-700/50 pb-1 last:border-0">
                            <div>
                                <div class="font-bold text-slate-700 dark:text-slate-200">${p.name || 'Tanpa Nama'}</div>
                                <div class="text-[10px] text-slate-400">${p.phone || '-'}</div>
                            </div>
                            <div class="text-right">
                                <div class="bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-300 px-1.5 py-0.5 rounded text-[10px] font-bold whitespace-nowrap mb-0.5">Kursi ${p.seatNumbers || p.seat || '?'}</div>
                                <div class="text-[9px] text-slate-400">${p.seatCount || 1} Orang</div>
                            </div>
                        </div>
                    `).join('')}
                </div>
                <!-- Triangle pointer -->
                <div class="absolute left-4 bottom-[-6px] w-3 h-3 bg-white dark:bg-slate-800 border-b border-r border-slate-200 dark:border-slate-600 transform rotate-45"></div>
            </div>
        `;
    }

    div.innerHTML = `
        ${passengerListHtml}
        <div class="flex justify-between mb-2">
            <div>
                <div class="text-lg font-bold text-blue-900 dark:text-blue-200">${trip.routeConfig?.time || '-'}</div>
                <div class="text-[10px] text-slate-400 font-bold">${trip.routeConfig?.routeId || 'Trip Manual'}</div>
            </div>
            <button class="text-[10px] font-bold px-2 py-1 rounded text-white h-fit shadow-sm ${statusBadgeColor}">${trip.status}</button>
        </div>
        <div class="flex justify-between items-center mb-1">
            <div class="text-xs font-bold text-slate-700 dark:text-slate-300"><i class="bi bi-truck-front text-blue-500"></i> ${trip.fleet?.name || '-'}</div>
            <div class="text-[10px] font-bold bg-slate-100 dark:bg-slate-600 px-2 py-0.5 rounded-full text-slate-600 dark:text-slate-300 flex items-center gap-1">
                <i class="bi bi-people-fill"></i> ${passengerCount}/${capacity}
            </div>
        </div>
        <div class="text-[10px] text-slate-500 dark:text-slate-400 pt-2 border-t border-slate-50 dark:border-slate-600">Driver: <strong>${trip.driver?.name || '-'}</strong></div>
    `;
    return div;
}

function renderFleetStatus(fleet) {
    const container = document.getElementById('fleetStatusList');
    container.innerHTML = '';

    fleet.forEach(f => {
        const div = document.createElement('div');
        div.className = 'flex justify-between items-center p-2 rounded border border-slate-100 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors';
        div.innerHTML = `
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded bg-slate-100 dark:bg-slate-600 flex items-center justify-center text-slate-400 dark:text-slate-300"><i class="${f.icon}"></i></div>
                <div><div class="text-xs font-bold text-slate-800 dark:text-slate-200">${f.name}</div><div class="text-[10px] text-slate-400">${f.plate}</div></div>
            </div>
            <div class="text-[10px] font-bold px-2 py-1 rounded bg-slate-200 dark:bg-slate-600 text-slate-600 dark:text-slate-300">${f.status}</div>
        `;
        container.appendChild(div);
    });
}

function updateTime() {
    const now = new Date();
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    document.getElementById('currentDate').textContent = now.toLocaleDateString('id-ID', options);
    document.getElementById('currentTime').textContent = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
}

function toggleFullscreen() {
    if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen();
    } else {
        if (document.exitFullscreen) {
            document.exitFullscreen();
        }
    }
}
