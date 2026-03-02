document.addEventListener('DOMContentLoaded', () => {
    // Reveal App (Loading Optimizer)
    setTimeout(() => {
        document.getElementById('app').classList.remove('opacity-0');
    }, 100);

    // Initial Date (Local Time)
    const dateInput = document.getElementById('dateInput');
    const d = new Date();
    const y = d.getFullYear();
    const m = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    dateInput.value = `${y}-${m}-${day}`;
    
    dateInput.addEventListener('change', fetchDailyData);

    // Fetch Data
    fetchDailyData(true); // Pass flag for initial load
});

let routes = [];
let bookings = [];

async function fetchDailyData(isInitialLoad = false) {
    const container = document.getElementById('routesContainer');
    const date = document.getElementById('dateInput').value;
    const loader = document.getElementById('customLoader');
    
    // Clear container immediately to give "refresh" feedback (if not initial load)
    if (!isInitialLoad) {
         container.innerHTML = '<div class="flex h-40 items-center justify-center"><div class="text-slate-400 font-bold animate-pulse">Memuat data seat map...</div></div>';
    }
    
    // Update Date Label
    updateDateLabel(date);
    
    const startTime = Date.now();
    
    try {
        const promises = [];
        
        // 1. Fetch Routes ONLY if empty (Cache)
        if (routes.length === 0) {
            console.log("Fetching Routes Config...");
            promises.push(fetch('api.php?action=get_routes').then(res => res.json()).then(data => {
                if(data.routes) routes = data.routes;
                
                // Sort Routes: 
                // 1. Padang - Bukittinggi (Non-Sitinjau) first
                // 2. Others
                // 3. "Via Sitinjau" last
                routes.sort((a, b) => {
                    const nameA = `${a.origin} - ${a.destination}`.toLowerCase();
                    const nameB = `${b.origin} - ${b.destination}`.toLowerCase();
                    
                    const isSitinjauA = nameA.includes('stinjau') || nameA.includes('sitinjau');
                    const isSitinjauB = nameB.includes('stinjau') || nameB.includes('sitinjau');
                    
                    if (isSitinjauA && !isSitinjauB) return 1; // A (Sitinjau) goes after B
                    if (!isSitinjauA && isSitinjauB) return -1; // B (Sitinjau) goes after A
                    
                    // If both are Sitinjau or neither are, sort by Padang-Bukittinggi preference
                    if (nameA.includes('padang') && nameA.includes('bukittinggi') && !isSitinjauA) return -1;
                    if (nameB.includes('padang') && nameB.includes('bukittinggi') && !isSitinjauB) return 1;
                    
                    return 0;
                });
            }));
        }

        // 2. Fetch Daily Bookings (Always)
        console.log(`Fetching Bookings for ${date}...`);
        promises.push(fetch(`api.php?action=get_daily_booked_seats&date=${date}`).then(res => res.json()).then(data => {
            if (data.status === 'success') {
                bookings = data.data;
            }
        }));

        await Promise.all(promises);
        
        // Artificial Delay for "Heavy" Loading (Only on Initial Load if loader exists)
        if (loader && isInitialLoad) {
            const elapsed = Date.now() - startTime;
            const minDelay = 1500; // 1.5 seconds artificial delay
            
            if (elapsed < minDelay) {
                await new Promise(r => setTimeout(r, minDelay - elapsed));
            }
            
            // Force Global Optimizer to finish (Fixes 5s timeout for non-Vue pages)
            document.body.classList.add('sr-app-ready'); 
            const genericSpinner = document.querySelector('.sr-loading-spinner');
            if(genericSpinner) genericSpinner.style.display = 'none';

            // Fade out custom loader
            loader.style.opacity = '0';
            setTimeout(() => {
                loader.style.display = 'none';
            }, 500);
        }

        renderView();
        
        // console.timeEnd('LoadData'); // End Timer (Removed for cleaner logs)

    } catch (e) {
        console.error("Error fetching data:", e);
        container.innerHTML = `<div class="text-center text-red-500 py-10">Gagal memuat data. Silakan coba lagi.</div>`;
    }
}

function updateDateLabel(dateString) {
    if (!dateString) return;
    const [y, m, d] = dateString.split('-').map(Number);
    const dateObj = new Date(y, m - 1, d);
    
    const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    
    const dayName = days[dateObj.getDay()];
    const monthName = months[dateObj.getMonth()];
    
    const label = document.getElementById('currentDateLabel');
    if (label) {
        label.textContent = `Menampilkan data hari ${dayName} tanggal ${d} ${monthName} ${y}`;
    }
}

function renderView() {
    const container = document.getElementById('routesContainer');
    const emptyState = document.getElementById('emptyState');
    
    container.innerHTML = '';

    if (routes.length === 0) {
        emptyState.classList.remove('hidden');
        return;
    }
    emptyState.classList.add('hidden');

    routes.forEach(route => {
        // --- 1. Create Route Header (Always Visible) ---
        const routeSection = document.createElement('div');
        routeSection.className = 'bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden transition-all';
        
        // Count bookings for this route
        const routeBookingsCount = bookings.filter(b => b.routeId === route.id && b.status !== 'Cancelled').length;
        
        const header = document.createElement('div');
        header.className = 'p-5 cursor-pointer flex items-center justify-between hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors group';
        header.innerHTML = `
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-full bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold">
                    <i class="bi bi-geo-alt-fill"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-700 dark:text-white">${route.origin} <i class="bi bi-arrow-right mx-1 text-slate-400"></i> ${route.destination}</h3>
                    <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">${routeBookingsCount} Penumpang Terdaftar</div>
                </div>
            </div>
            <div class="flex items-center gap-3">
                 <span class="text-xs font-bold px-3 py-1 rounded-full ${routeBookingsCount > 0 ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400'}">
                    ${routeBookingsCount > 0 ? 'Aktif' : 'Kosong'}
                 </span>
                 <i class="bi bi-chevron-down text-slate-400 transition-transform duration-300 group-hover:text-blue-500 icon-expand"></i>
            </div>
        `;
        
        // --- 2. Create Content Container (Hidden by Default) ---
        const contentContainer = document.createElement('div');
        contentContainer.className = 'hidden border-t border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/30 p-6 animate-fade-in';
        contentContainer.dataset.rendered = "false"; // Flag for Lazy Loading
        
        // --- 3. Interaction Logic ---
        header.addEventListener('click', () => {
            const isHidden = contentContainer.classList.contains('hidden');
            const icon = header.querySelector('.icon-expand');
            
            if (isHidden) {
                // Expand
                contentContainer.classList.remove('hidden');
                icon.style.transform = 'rotate(180deg)';
                
                // Lazy Render
                if (contentContainer.dataset.rendered === "false") {
                    renderRouteGrid(route, contentContainer);
                    contentContainer.dataset.rendered = "true";
                }
            } else {
                // Collapse
                contentContainer.classList.add('hidden');
                icon.style.transform = 'rotate(0deg)';
            }
        });

        routeSection.appendChild(header);
        routeSection.appendChild(contentContainer);
        container.appendChild(routeSection);
    });
}

function renderRouteGrid(route, container) {
    const template = document.getElementById('seatMapTemplate');
    const grid = document.createElement('div');
    grid.className = 'grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-6';

    if (route.schedules && route.schedules.length > 0) {
        route.schedules.forEach(time => {
            // Find all batches for this time/route
            const slotBookings = bookings.filter(b => b.routeId === route.id && b.time === time && b.status !== 'Cancelled');
            
            // Determine max batch
            let maxBatch = 1;
            slotBookings.forEach(b => {
                const bn = parseInt(b.batchNumber) || 1;
                if (bn > maxBatch) maxBatch = bn;
            });

            // Iterate Batches
            for (let bNum = 1; bNum <= maxBatch; bNum++) {
                 // Filter bookings for this batch
                 const batchBookings = slotBookings.filter(b => (parseInt(b.batchNumber) || 1) === bNum);
                 
                 // Clone Template
                 const clone = template.content.cloneNode(true);
                 
                 // Fill Info
                 clone.querySelector('.schedule-time').textContent = time;
                 clone.querySelector('.batch-name').textContent = `Armada ${bNum}`;
                 
                 // Aggregate Occupied Seats
                 let occupied = [];
                 batchBookings.forEach(bk => {
                     if (bk.seatNumbers) {
                         bk.seatNumbers.split(',').forEach(s => {
                             occupied.push({
                                 seat: s.trim(),
                                 status: bk.validationStatus, 
                                 pName: bk.passengerName
                             });
                         });
                     }
                 });

                 const occupancyEl = clone.querySelector('.occupancy-rate');
                 occupancyEl.textContent = `${occupied.length}/8`;
                 
                 // Color code occupancy rate
                 if (occupied.length >= 8) occupancyEl.className = 'font-mono font-bold text-red-600 dark:text-red-400';
                 else if (occupied.length >= 5) occupancyEl.className = 'font-mono font-bold text-orange-600 dark:text-orange-400';
                 else occupancyEl.className = 'font-mono font-bold text-blue-600 dark:text-blue-400';
                 
                 // Render Seats
                 const seatPlaceholders = clone.querySelectorAll('.seat-placeholder');
                 seatPlaceholders.forEach(el => {
                     const seatNum = el.dataset.seat;
                     const seatInfo = occupied.find(o => o.seat === seatNum);
                     
                     // Create Seat Element
                     const seatEl = document.createElement('div');
                     seatEl.className = 'w-full h-full rounded-lg flex items-center justify-center text-sm font-bold border transition-all cursor-default seat relative group select-none';
                     
                     if (seatInfo) {
                         // Occupied
                         if (seatInfo.status === 'Menunggu Validasi') {
                             seatEl.classList.add('bg-orange-500', 'border-orange-600', 'text-white', 'shadow-sm');
                         } else {
                             // Occupied -> Black
                             seatEl.classList.add('bg-slate-900', 'dark:bg-black', 'border-slate-800', 'dark:border-slate-700', 'text-white', 'shadow-sm');
                         }
                         seatEl.textContent = seatNum;
                         
                         // Tooltip
                         const tooltip = document.createElement('div');
                         tooltip.className = 'absolute bottom-full mb-2 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-[10px] py-1 px-2 rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap z-20 shadow-lg';
                         tooltip.textContent = seatInfo.pName;
                         
                         // Triangle
                         const triangle = document.createElement('div');
                         triangle.className = 'absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-800';
                         tooltip.appendChild(triangle);
                         
                         seatEl.appendChild(tooltip);

                     } else {
                         // Empty
                         seatEl.classList.add('bg-white', 'dark:bg-slate-700', 'border-slate-300', 'dark:border-slate-600', 'text-slate-400');
                         seatEl.textContent = seatNum;
                     }
                     
                     el.replaceWith(seatEl);
                 });
                 
                 grid.appendChild(clone);
            }
        });
    } else {
        const noSched = document.createElement('div');
        noSched.className = 'col-span-full py-10 flex flex-col items-center justify-center text-slate-400 italic bg-white dark:bg-slate-800 rounded-xl border border-dashed border-slate-300 dark:border-slate-700';
        noSched.innerHTML = `<i class="bi bi-calendar-x text-2xl mb-2"></i><span>Tidak ada jadwal terdaftar untuk rute ini.</span>`;
        grid.appendChild(noSched);
    }

    container.appendChild(grid);
}
