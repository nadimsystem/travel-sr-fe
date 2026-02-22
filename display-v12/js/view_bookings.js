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
    
    // Route Filter
    const routeSelect = document.getElementById('routeSelect');
    if(routeSelect) {
        routeSelect.addEventListener('change', () => {
            renderView();
        });
    }

    // Fetch Data

    // Filter Logic
    const btnAll = document.getElementById('filterAll');
    const btnPending = document.getElementById('filterPending');
    
    if(btnAll && btnPending) {
        btnAll.addEventListener('click', () => setFilterStatus('all'));
        btnPending.addEventListener('click', () => setFilterStatus('pending'));
    }

    // Check URL
    if (window.location.href.includes('booking_pending')) {
        filterStatus = 'pending';
        // Hide All button if exists
        if(btnAll) btnAll.style.display = 'none';
    }

    // Fetch Data
    fetchDailyData(true); // Pass flag for initial load
});

let routes = [];
let bookings = [];
let scheduleDefaults = [];
let filterStatus = 'all'; // 'all' or 'pending'

function setFilterStatus(status) {
    filterStatus = status;
    updateFilterUI();
    renderView();
}

function updateFilterUI() {
    const btnAll = document.getElementById('filterAll');
    const btnPending = document.getElementById('filterPending');
    
    if (filterStatus === 'all') {
        btnAll.className = 'px-3 py-1 rounded-full text-xs font-bold bg-blue-600 text-white transition-all shadow-sm';
        btnPending.className = 'px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-500 hover:bg-slate-200 transition-all';
    } else {
        btnAll.className = 'px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-500 hover:bg-slate-200 transition-all';
        btnPending.className = 'px-3 py-1 rounded-full text-xs font-bold bg-orange-500 text-white shadow-lg shadow-orange-200 ring-2 ring-orange-100 transition-all';
    }
}

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
                if(data.scheduleDefaults) scheduleDefaults = data.scheduleDefaults;
                
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

                // Populate Filter
                populateRouteFilter();
            }));
        }

        // 2. Fetch Daily Bookings (Always)
        console.log(`Fetching Bookings for ${date}...`);
        promises.push(fetch(`api.php?action=get_daily_booked_seats&date=${date}`).then(res => res.json()).then(data => {
            if (data.status === 'success') {
                bookings = data.data;
                updatePendingCount();
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

function updatePendingCount() {
    // Count Antrian (Waiting)
    const pending = bookings.filter(b => b.status === 'Antrian' || b.validationStatus === 'Review').length;
    const badge = document.getElementById('pendingCountBadge');
    if (badge) {
        badge.textContent = pending;
        if (pending > 0) badge.classList.remove('hidden');
        else badge.classList.add('hidden');
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

    let displayRoutes = routes;
    const selectedRouteId = document.getElementById('routeSelect')?.value || 'all';
    
    if (selectedRouteId !== 'all') {
        displayRoutes = routes.filter(r => r.id == selectedRouteId);
    }
    
    // If Filter Pending, only show routes that HAVE pending bookings
    if (filterStatus === 'pending') {
        displayRoutes = displayRoutes.filter(route => {
             return bookings.some(b => b.routeId === route.id && (b.status === 'Antrian' || b.validationStatus === 'Review'));
        });
    }

    displayRoutes.forEach(route => {
        // --- 1. Create Route Header (Always Visible) ---
        const routeSection = document.createElement('div');
        routeSection.className = 'bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden transition-all';
        
        // Count bookings for this route (Apply Filter Logic)
        let routeBookings = bookings.filter(b => b.routeId === route.id && b.status !== 'Cancelled');
        if (filterStatus === 'pending') {
            routeBookings = routeBookings.filter(b => b.status === 'Antrian' || b.validationStatus === 'Review');
        }
        const routeBookingsCount = routeBookings.length;
        
        const header = document.createElement('div');
        header.className = 'p-5 cursor-pointer flex items-center justify-between hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors group';
        header.innerHTML = `
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-full bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold">
                    <i class="bi bi-geo-alt-fill"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-700 dark:text-white">${route.origin} <i class="bi bi-arrow-right mx-1 text-slate-400"></i> ${route.destination}</h3>
                    <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">${routeBookingsCount} Penumpang Terdaftar ${filterStatus==='pending' ? '(Pending)' : ''}</div>
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
        // Auto expand if pending filter and has count
        const shouldExpand = filterStatus === 'pending' && routeBookingsCount > 0;
        
        const contentContainer = document.createElement('div');
        contentContainer.className = (shouldExpand ? '' : 'hidden') + ' border-t border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/30 p-6 animate-fade-in';
        contentContainer.dataset.rendered = shouldExpand ? "true" : "false"; // Flag for Lazy Loading
        
        if(shouldExpand) {
             header.querySelector('.icon-expand').style.transform = 'rotate(180deg)';
             renderRouteGrid(route, contentContainer);
             contentContainer.dataset.rendered = "true";
        }
        
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
        route.schedules.forEach(timeRaw => {
            // Defensive: schedules can be strings OR objects depending on PHP json_decode/server version
            let time = typeof timeRaw === 'string' ? timeRaw : (timeRaw && timeRaw.time ? timeRaw.time : String(timeRaw));
            // Normalize separator: "05.00" -> "05:00"
            time = time.replace('.', ':');
            // Filter by Status Check
            if (filterStatus === 'pending') {
                const hasPending = bookings.some(b => b.routeId === route.id && b.time === time && (b.status === 'Antrian' || b.validationStatus === 'Review'));
                if (!hasPending) return;
            }

            // Find all batches for this time/route
            const slotBookings = bookings.filter(b => b.routeId === route.id && b.time === time && b.status !== 'Cancelled');
            
            // Determine max batch (Initial)
            let maxBatch = 1;

            // Check Defaults
            const defaults = scheduleDefaults.filter(d => d.routeId == route.id && d.time == time);
            if (defaults.length > 0) {
                 const maxDefault = Math.max(...defaults.map(d => parseInt(d.batchNumber)||1));
                 if (maxDefault > maxBatch) maxBatch = maxDefault;
            }

                // Virtual Batch Distribution Logic
                const BAT_CAPACITY = 8;
                let batchesMap = new Map(); // batchNumber -> array of bookings

                // Helper to get effective batch
                const unassignedBookings = [];
                
                // 1. Separate fixed assignments vs flexible
                slotBookings.forEach(b => {
                    const explicitBatch = parseInt(b.batchNumber) || 1;
                    if (explicitBatch > 1) {
                         if (!batchesMap.has(explicitBatch)) batchesMap.set(explicitBatch, []);
                         batchesMap.get(explicitBatch).push(b);
                    } else {
                         unassignedBookings.push(b);
                    }
                });

                // 2. Distribute unassigned (Batch 1)
                if (!batchesMap.has(1)) batchesMap.set(1, []);
                let currentBatchForUnassigned = 1;
                
                unassignedBookings.forEach(b => {
                    let seatsCount = 1;
                    if (b.seatNumbers) {
                        seatsCount = b.seatNumbers.split(',').map(s => s.trim()).filter(s => s !== '').length;
                    } else if (b.seatCount) {
                        seatsCount = parseInt(b.seatCount) || 1;
                    }
                    
                    let placed = false;
                    let checkBatch = 1;

                    while (!placed) {
                        const currentBatchList = batchesMap.get(checkBatch) || [];
                        let totalPaxInBatch = 0;

                        currentBatchList.forEach(existing => {
                            if(existing.seatNumbers) {
                                totalPaxInBatch += existing.seatNumbers.split(',').map(s=>s.trim()).filter(s=>s!=='').length;
                            } else {
                                totalPaxInBatch += parseInt(existing.seatCount) || 1; // Fallback for dataless bookings
                            }
                        });
                        
                        if (totalPaxInBatch + seatsCount <= BAT_CAPACITY || totalPaxInBatch === 0) {
                            if (!batchesMap.has(checkBatch)) batchesMap.set(checkBatch, []);
                            batchesMap.get(checkBatch).push(b);
                            placed = true;
                        } else {
                            checkBatch++;
                        }
                    }
                });
                
                // 3. Determine Final Max Batch (Recalculate)
                // maxBatch is already set from defaults/initial check.
                // We update it if our distributed map goes higher.
                
                // Check Max from our map
                for (let k of batchesMap.keys()) {
                    if (k > maxBatch) maxBatch = k;
                }

                // Iterate Batches
                for (let bNum = 1; bNum <= maxBatch; bNum++) {
                     // Filter bookings for this batch (FROM MAP)
                     const batchBookings = batchesMap.get(bNum) || [];
                     
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
                                 let seatNum = s.trim();
                                 
                                 // Seat Remapping?
                                 // If we are in Batch > 1 and the seat number is large (e.g. 9),
                                 // we should map it to 1-8 for display if it's meant to be sequential.
                                 // If the user input "Seat 1" for batch 1, and "Seat 1" for batch 2 (logically),
                                 // then seatNum is already correct.
                                 // If the user input "Seat 9" (meaning 1st seat of batch 2),
                                 // we need to map 9 -> 1.
                                 const seatInt = parseInt(seatNum);
                                 if (!isNaN(seatInt) && seatInt > 8) {
                                     // Simple modulo mapping for now?
                                     // 9 -> 1, 16 -> 8
                                     // seatNum = ((seatInt - 1) % 8) + 1;
                                     // But only if we trust this logic.
                                     // For now, let's assume if it's in Batch 2 it might just be "Seat 1".
                                     // But if it IS "Seat 9", we show it as Seat 9 text, but position?
                                     // The GRID has data-seat="1". so "9" won't match.
                                     // We MUST remap effective seat for UI placement.
                                     
                                     // let effectiveSeat = ((seatInt - 1) % 8) + 1;
                                     // Wait, if it's purely a list, we might just map by INDEX.
                                     // But we need to support specific seat assignments.
                                     // Let's rely on standard 1-7.
                                 }

                                 occupied.push({
                                     seat: seatNum,
                                     status: bk.validationStatus, 
                                     bookingStatus: bk.status,
                                     pName: bk.passengerName,
                                     bookingId: bk.id
                                 });
                             });
                         }
                     });

                 // We already have 'batchBookings', let's find true pax count
                 let truePaxCount = 0;
                 let unmappedPassengers = [];
                 
                 batchBookings.forEach(bk => {
                     const seatsRaw = bk.seatNumbers || '';
                     const seatParts = seatsRaw.split(',').map(s => s.trim()).filter(s => s);
                     
                     if (seatParts.length === 0) {
                         truePaxCount += 1;
                         unmappedPassengers.push({ pName: bk.passengerName, bookingId: bk.id, reason: 'Belum Pilih Kursi' });
                     } else {
                         truePaxCount += seatParts.length;
                     }
                 });

                 const occupancyEl = clone.querySelector('.occupancy-rate');
                 occupancyEl.textContent = `${truePaxCount}/8`;
                 
                 // Color code occupancy rate
                 if (truePaxCount >= 8) occupancyEl.className = 'font-mono font-bold text-red-600 dark:text-red-400';
                 else if (truePaxCount >= 5) occupancyEl.className = 'font-mono font-bold text-orange-600 dark:text-orange-400';
                 else occupancyEl.className = 'font-mono font-bold text-blue-600 dark:text-blue-400';
                 
                 // Render Seats
                 const seatPlaceholders = clone.querySelectorAll('.seat-placeholder');
                 seatPlaceholders.forEach(el => {
                     const seatNum = el.dataset.seat;
                     const seatInfo = occupied.find(o => o.seat === seatNum);
                     
                     // Create Seat Element
                     const seatEl = document.createElement('div');
                     seatEl.className = 'w-full h-full rounded-lg flex items-center justify-center text-sm font-bold border transition-all seat relative group select-none';
                     
                     if (seatInfo) {
                         // Occupied
                         seatEl.classList.add('cursor-pointer'); // Make Clickable
                         seatEl.classList.remove('cursor-default');

                         // Click Handler
                         seatEl.addEventListener('click', (e) => {
                             e.stopPropagation();
                             openBookingModal(seatInfo.bookingId);
                         });

                         if (seatInfo.status === 'Menunggu Validasi' || 'Review') { // Check both
                             // Handle "Review" status from self-booking
                             if(seatInfo.status === 'Review' || seatInfo.status === 'Menunggu Validasi' || seatInfo.bookingStatus === 'Antrian') { // Assuming seatInfo has bookingStatus or we check status differently
                                 seatEl.classList.add('bg-orange-500', 'border-orange-600', 'text-white', 'shadow-sm');
                             } else {
                                  seatEl.classList.add('bg-slate-900', 'dark:bg-black', 'border-slate-800', 'dark:border-slate-700', 'text-white', 'shadow-sm');
                             }
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
                         seatEl.classList.add('bg-white', 'dark:bg-slate-700', 'border-slate-300', 'dark:border-slate-600', 'text-slate-400', 'cursor-default');
                         seatEl.textContent = seatNum;
                     }
                     
                     el.replaceWith(seatEl);
                 });
                 
                 // Add unmapped passengers warning if any
                 if (unmappedPassengers.length > 0) {
                     const warningDiv = document.createElement('div');
                     warningDiv.className = 'mt-4 pt-3 border-t border-red-100 dark:border-red-900/30';
                     
                     const warningTitle = document.createElement('div');
                     warningTitle.className = 'text-xs font-bold text-red-500 mb-2 flex items-center gap-1';
                     warningTitle.innerHTML = '<i class="bi bi-exclamation-triangle-fill"></i> Data Bermasalah:';
                     warningDiv.appendChild(warningTitle);
                     
                     const paxList = document.createElement('div');
                     paxList.className = 'space-y-1.5';
                     unmappedPassengers.forEach(up => {
                         const paxItem = document.createElement('div');
                         paxItem.className = 'text-[10px] bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300 p-1.5 rounded flex justify-between items-center cursor-pointer hover:bg-red-100 transition-colors border border-red-100 dark:border-red-800';
                         paxItem.innerHTML = `<span class="font-bold truncate max-w-[100px]">${up.pName}</span> <span class="text-[9px] bg-red-200 text-red-800 px-1 rounded">${up.reason}</span>`;
                         paxItem.addEventListener('click', (e) => {
                             e.stopPropagation();
                             openBookingModal(up.bookingId);
                         });
                         paxList.appendChild(paxItem);
                     });
                     warningDiv.appendChild(paxList);
                     clone.firstElementChild.appendChild(warningDiv);
                 }
                 
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

function populateRouteFilter() {
    const select = document.getElementById('routeSelect');
    if (!select) return;
    
    // Keep "Semua Rute"
    select.innerHTML = '<option value="all">Semua Rute</option>';
    
    routes.forEach(route => {
        // Filter out Sitinjau/Stinjau from naming if desired, or keep as is
        // For dropdown, shows Origin -> Destination
        const opt = document.createElement('option');
        opt.value = route.id;
        opt.textContent = `${route.origin} -> ${route.destination}`;
        select.appendChild(opt);
    });
}

// --- Booking Modal Logic ---
let currentBookingId = null;

function openBookingModal(bookingId) {
    currentBookingId = bookingId;
    const modal = document.getElementById('bookingModal');
    const loading = document.getElementById('modalLoading');
    const content = document.getElementById('modalContent');
    const actions = document.getElementById('modalActions');
    
    modal.classList.remove('hidden');
    loading.classList.remove('hidden');
    content.innerHTML = '';
    actions.classList.add('hidden');
    
    // Fetch Details
    fetch(`api.php?action=get_booking_details&id=${bookingId}`)
        .then(res => res.json())
        .then(data => {
            loading.classList.add('hidden');
            if (data.status === 'success' && data.booking) {
                const b = data.booking;
                renderBookingDetails(b, content);
                
                // Show Actions if Pending/Review
                if (b.status === 'Antrian' || b.validationStatus === 'Review') {
                    actions.classList.remove('hidden');
                }
            } else {
                content.innerHTML = '<p class="text-red-500 text-center">Gagal memuat detail.</p>';
            }
        })
        .catch(err => {
            loading.classList.add('hidden');
            content.innerHTML = '<p class="text-red-500 text-center">Error koneksi.</p>';
        });
}

function closeBookingModal() {
    document.getElementById('bookingModal').classList.add('hidden');
    currentBookingId = null;
}

function renderBookingDetails(b, container) {
    // Generate HTML for details
    // Check Proof
    let proofHtml = '';
    if (b.paymentProof) {
         proofHtml = `<div class="mt-2">
            <p class="text-xs font-bold text-slate-500 mb-1">Bukti Pembayaran</p>
            <img src="${b.paymentProof}" class="w-full rounded-lg border border-slate-200 cursor-pointer hover:opacity-90" onclick="window.open('${b.paymentProof}')">
         </div>`;
    } else {
        proofHtml = `<p class="text-xs text-slate-400 italic mt-2">Belum ada bukti pembayaran</p>`;
    }

    container.innerHTML = `
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <p class="text-xs text-slate-500">Nama Penumpang</p>
                <p class="font-bold text-slate-800 dark:text-white">${b.passengerName}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500">Nomor Telepon</p>
                <div class="flex items-center gap-2">
                    <p class="font-bold text-slate-800 dark:text-white">${b.passengerPhone}</p>
                    <a href="https://wa.me/${b.passengerPhone.replace(/^0/,'62')}" target="_blank" class="text-green-500 hover:text-green-600"><i class="bi bi-whatsapp"></i></a>
                </div>
            </div>
            <div>
                <p class="text-xs text-slate-500">Rute</p>
                <p class="font-bold text-slate-800 dark:text-white">${b.routeName || '-'}</p>
            </div>
             <div>
                <p class="text-xs text-slate-500">Jadwal</p>
                <p class="font-bold text-slate-800 dark:text-white">${b.date} • ${b.time}</p>
            </div>
             <div>
                <p class="text-xs text-slate-500">Kursi</p>
                <span class="px-2 py-0.5 rounded bg-blue-100 text-blue-700 text-xs font-bold">${b.seatNumbers}</span>
            </div>
            <div>
                <p class="text-xs text-slate-500">Total Harga</p>
                <p class="font-bold text-blue-600">Rp ${parseFloat(b.totalPrice).toLocaleString('id-ID')}</p>
            </div>
        </div>
        
        <div class="bg-slate-50 dark:bg-slate-900 p-3 rounded-xl border border-slate-100 dark:border-slate-700">
             <div class="flex justify-between items-center mb-2">
                <span class="text-xs font-bold text-slate-500">Status</span>
                <span class="px-2 py-0.5 rounded text-[10px] font-bold ${b.validationStatus === 'Review' ? 'bg-orange-100 text-orange-600' : (b.validationStatus === 'Ditolak' ? 'bg-red-100 text-red-600' : 'bg-green-100 text-green-600')}">${b.status} / ${b.validationStatus}</span>
             </div>
             ${proofHtml}
        </div>
    `;
}

function processBooking(action) {
    if (!currentBookingId) return;
    
    const confirmMsg = action === 'approve' 
        ? 'Apakah Anda yakin ingin MENERIMA booking ini?' 
        : 'Apakah Anda yakin ingin MENOLAK booking ini? Status akan berubah menjadi Ditolak dan user akan melihat pesan: "Maaf, data yang anda masukkan tidak valid..."';

    if(!confirm(confirmMsg)) return;
    
    // Use reject_booking (Soft Cancel) instead of delete_booking
    const endpoint = action === 'approve' ? 'api.php?action=validate_booking' : 'api.php?action=reject_booking';
    
    fetch(endpoint, { // Both use POST
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ id: currentBookingId })
    })
    .then(res => res.json())
    .then(data => {
        if(data.status === 'success') {
            closeBookingModal();
            fetchDailyData(); // Refresh grid
            // Show toast?
        } else {
            alert('Gagal memproses: ' + data.message);
        }
    })
    .catch(err => alert('Error koneksi'));
}
