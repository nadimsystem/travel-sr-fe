// js/dashboard.js

document.addEventListener('DOMContentLoaded', () => {
    // Check if we are actually on the dashboard page by looking for a unique element
    if (!document.getElementById('dashboardContent')) return;

    loadDashboardData();
    setInterval(loadDashboardData, 30000); // Auto refresh
    updateTime();
    setInterval(updateTime, 1000);

    // Event Listeners
    const fsBtn = document.getElementById('toggleFullscreenBtn');
    if (fsBtn) fsBtn.addEventListener('click', toggleFullscreen);
});

async function loadDashboardData() {
    // 1. Fetch Fast Stats & Graph
    try {
        const res = await fetch('api.php?action=get_dashboard_summary');
        const summary = await res.json();
        
        // Slight delay to show skeleton (optional, for feel)
        setTimeout(() => {
            if (summary.status === 'success' && summary.data) {
                updateStatsFast(summary.data);
                initDashboardChart(summary.data.graph);
            }
            // Toggle Skeleton
            const skeleton = document.getElementById('dashboardSkeleton');
            const content = document.getElementById('dashboardContent');
            
            if (skeleton) skeleton.classList.add('hidden');
            if (content) {
                content.classList.remove('hidden');
                setTimeout(() => content.classList.remove('opacity-0'), 50); // Fade in
            }
        }, 500); 

    } catch (e) {
        console.error('Failed to load stats', e);
        // Fallback: show content even if error
        const skeleton = document.getElementById('dashboardSkeleton');
        const content = document.getElementById('dashboardContent');
        if (skeleton) skeleton.classList.add('hidden');
        if (content) content.classList.remove('hidden', 'opacity-0');
    }
}

async function fetchTripList() {
    // Optional: Only call if user requests
    return [];
}

function updateStatsFast(data) {
    // Helper to safely set text content
    const setText = (id, text) => {
        const el = document.getElementById(id);
        if (el) el.textContent = text;
    };

    // Monthly (Global) - TOP PRIORITY
    setText('monthRevenue', formatRupiah(data.month.revenue));
    setText('monthPax', data.month.pax);
    
    // Unpaid
    setText('totalUnpaidAmount', formatRupiah(data.unpaid.amount));
    setText('totalUnpaidCount', `(${data.unpaid.count} Booking)`);

    // Today
    setText('todayRevenue', formatRupiah(data.today.revenue));
    setText('todayPax', `${data.today.pax} Kursi`);
    
    // Pending
    setText('pendingValidationCount', data.today.pendingValidation);
    setText('pendingDispatchCount', data.pendingDispatch > 0 ? data.pendingDispatch : '0');
}

let dashboardChartInstance = null;
let dashboardPaxChartInstance = null;

function initDashboardChart(graphData) {
    const ctx = document.getElementById('dashboardChart');
    const ctxPax = document.getElementById('dashboardPaxChart');
    
    // 1. REVENUE CHART
    if(ctx) {
        if(dashboardChartInstance) dashboardChartInstance.destroy();
        dashboardChartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: graphData.labels,
                datasets: [{
                    label: 'Pendapatan Harian',
                    data: graphData.revenue,
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37, 99, 235, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#e2e8f0', drawBorder: false },
                        ticks: { 
                        callback: function(value) { 
                            if (value === 0) return 'Rp 0';
                            if (value >= 1000000) return 'Rp ' + (value/1000000).toLocaleString('id-ID', {maximumFractionDigits: 1}) + ' jt';
                            return 'Rp ' + (value/1000).toLocaleString('id-ID') + 'k'; 
                        } 
                    }
                    },
                    x: {
                        grid: { display: false, drawBorder: false },
                        ticks: { 
                            maxTicksLimit: 10,
                            maxRotation: 90,
                            minRotation: 90,
                            callback: function(value) {
                                 const label = this.getLabelForValue(value);
                                 if (label && label.length >= 10) return label.substring(5);
                                 return label;
                            }
                        }
                    }
                }
            }
        });
    }

    // 2. PASSENGER CHART
    if(ctxPax && graphData.pax) {
        if(dashboardPaxChartInstance) dashboardPaxChartInstance.destroy();
        dashboardPaxChartInstance = new Chart(ctxPax, {
            type: 'bar', // Bar Chart for People
            data: {
                labels: graphData.labels,
                datasets: [{
                    label: 'Penumpang',
                    data: graphData.pax,
                    backgroundColor: '#d4af37', // Gold for Pax
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#e2e8f0', drawBorder: false },
                        ticks: { precision: 0 } // No decimals for people
                    },
                    x: {
                        grid: { display: false, drawBorder: false },
                        ticks: { 
                            maxTicksLimit: 10,
                            maxRotation: 90,
                            minRotation: 90,
                            callback: function(value) {
                                 const label = this.getLabelForValue(value);
                                 if (label && label.length >= 10) return label.substring(5);
                                 return label;
                            }
                        }
                    }
                }
            }
        });
    }
}

function renderOutboundTrips(trips) {
    const container = document.getElementById('outboundTripsList');
    if (!container) return;
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
    if (!container) return;
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
    if (!container) return;
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
    const dDate = document.getElementById('currentDate');
    const dTime = document.getElementById('currentTime');
    if (!dDate || !dTime) return;

    const now = new Date();
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    dDate.textContent = now.toLocaleDateString('id-ID', options);
    dTime.textContent = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
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
