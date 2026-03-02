// js/booking_travel.js

document.addEventListener('DOMContentLoaded', () => {
    loadBookingData();
    setupEventListeners();
    updateTime();
    setInterval(updateTime, 1000);
    
    // Set default date to today
    document.getElementById('dateInput').valueAsDate = new Date();

    // Initial fetch for occupied seats if elements are present
    if (document.getElementById('routeSelect') && document.getElementById('dateInput') && document.getElementById('timeInput')) {
        fetchOccupiedSeats();
    }
    
    loadInputMemory();
});

// --- INPUT MEMORY LOGIC ---
function loadInputMemory() {
    const savedReceivers = JSON.parse(localStorage.getItem('savedReceivers') || '[]');
    const savedLocations = JSON.parse(localStorage.getItem('savedLocations') || '[]');
    
    const receiverList = document.getElementById('receiverList');
    if (receiverList) {
        receiverList.innerHTML = '';
        savedReceivers.forEach(item => {
            const opt = document.createElement('option');
            opt.value = item;
            receiverList.appendChild(opt);
        });
    }

    const locationList = document.getElementById('locationList');
    if (locationList) {
        locationList.innerHTML = '';
        savedLocations.forEach(item => {
            const opt = document.createElement('option');
            opt.value = item;
            locationList.appendChild(opt);
        });
    }
}

function saveInputMemory(recv, loc) {
    if (recv) {
        let savedReceivers = JSON.parse(localStorage.getItem('savedReceivers') || '[]');
        if (!savedReceivers.includes(recv)) {
            savedReceivers.push(recv);
            localStorage.setItem('savedReceivers', JSON.stringify(savedReceivers));
        }
    }
    if (loc) {
        let savedLocations = JSON.parse(localStorage.getItem('savedLocations') || '[]');
        if (!savedLocations.includes(loc)) {
            savedLocations.push(loc);
            localStorage.setItem('savedLocations', JSON.stringify(savedLocations));
        }
    }
}
// --- END INPUT MEMORY LOGIC ---

let routes = [];
let currentServiceType = 'Travel';
let selectedSeats = [];
let currentPrice = 0;
let ktmBase64 = null;
let paymentProofBase64 = null;
let dpProofBase64 = null; // New variable for DP proof
let occupiedSeats = []; // List of occupied seat numbers in the current batch

// --- SEAT AVAILABILITY LOGIC ---
function handleProofUpload(e) {
    const input = e.target;
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            paymentProofBase64 = e.target.result;
            
            // Show Preview
            const previewImg = document.getElementById('proofPreview');
            const previewContainer = document.getElementById('proofPreviewContainer');
            const uploadLabel = document.getElementById('uploadProofLabel');
            
            if (previewImg && previewContainer) {
                previewImg.src = paymentProofBase64;
                previewContainer.classList.remove('hidden');
                uploadLabel.classList.add('hidden'); 
            }
            
            const labelText = document.getElementById('proofLabel');
            if(labelText) labelText.innerHTML = '<i class="bi bi-check-circle-fill text-green-500 text-lg block mb-1"></i>Ganti File';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function handleDpProofUpload(e) {
    const input = e.target;
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            dpProofBase64 = e.target.result;
            
            // Show Preview
            const previewImg = document.getElementById('dpProofPreview');
            const previewContainer = document.getElementById('dpProofPreviewContainer');
            const uploadLabel = document.getElementById('dpProofLabel').parentElement;
            
            if (previewImg && previewContainer) {
                previewImg.src = dpProofBase64;
                previewContainer.classList.remove('hidden');
                uploadLabel.classList.add('hidden');
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function removeProof() {
    paymentProofBase64 = null;
    document.getElementById('proofPreviewContainer').classList.add('hidden');
    document.getElementById('uploadProofLabel').classList.remove('hidden');
    document.getElementById('proofLabel').innerHTML = 'Upload Bukti Transfer';
    document.getElementById('paymentProofInput').value = ''; 
}

function removeDpProof() {
    dpProofBase64 = null;
    document.getElementById('dpProofPreviewContainer').classList.add('hidden');
    document.getElementById('dpProofLabel').parentElement.classList.remove('hidden');
    document.getElementById('dpProofInput').value = '';
}

// --- KTM UPLOAD LOGIC ---
function handleKtmUpload(e) {
    const input = e.target;
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            ktmBase64 = e.target.result;
            
            // Show Preview
            const previewImg = document.getElementById('ktmPreview');
            const previewContainer = document.getElementById('ktmPreviewContainer');
            const uploadLabel = document.getElementById('ktmUploadLabel');
            const fileName = document.getElementById('ktmFileName');
            
            if (previewImg && previewContainer) {
                previewImg.src = ktmBase64;
                previewContainer.classList.remove('hidden');
                uploadLabel.classList.add('hidden');
                if(fileName) fileName.textContent = input.files[0].name;
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function removeKtm() {
    ktmBase64 = null;
    document.getElementById('ktmPreviewContainer').classList.add('hidden');
    document.getElementById('ktmUploadLabel').classList.remove('hidden');
    document.getElementById('ktmInput').value = '';
    const fileName = document.getElementById('ktmFileName');
    if(fileName) fileName.textContent = 'Belum ada file';
}
// --- END KTM UPLOAD LOGIC ---

let currentBatch = 1;
let availableBatches = [1];

async function fetchOccupiedSeats() {
    const routeId = document.getElementById('routeSelect').value;
    const date = document.getElementById('dateInput').value;
    const time = document.getElementById('timeInput').value;
    
    if (!routeId || !date || !time) {
        occupiedSeats = [];
        availableBatches = [1];
        currentBatch = 1;
        renderBatchSelector();
        renderSeatAvailability();
        return;
    }

    try {
        const res = await fetch(`api.php?action=get_occupied_seats&routeId=${routeId}&date=${date}&time=${time}`);
        const bookings = await res.json();
        
        processOccupiedSeats(bookings);
    } catch (e) {
        console.error("Error fetching seats:", e);
        occupiedSeats = [];
        availableBatches = [1];
        currentBatch = 1;
        renderBatchSelector();
        renderSeatAvailability();
    }
}

function processOccupiedSeats(bookings) {
    // 1. Group bookings by batchNumber
    const batchesData = {};
    let maxBatch = 1;

    bookings.forEach(b => {
        const bn = parseInt(b.batchNumber) || 1;
        if (bn > maxBatch) maxBatch = bn;
        if (!batchesData[bn]) batchesData[bn] = [];
        
        const seats = b.seatNumbers ? b.seatNumbers.split(',').map(s => s.trim()) : [];
        seats.forEach(s => batchesData[bn].push(s));
    });

    // 2. Determine available batches
    availableBatches = [];
    for (let i = 1; i <= maxBatch; i++) {
        availableBatches.push(i);
    }

    // Always offer a "New Batch" (maxBatch + 1)
    availableBatches.push(maxBatch + 1);

    // 3. Auto-select batch if current car is full and we just loaded this schedule
    const selectorArea = document.getElementById('batchSelectorArea');
    if (availableBatches.length > 1) {
        selectorArea.classList.remove('hidden');
    } else {
        selectorArea.classList.add('hidden');
    }

    // Determine current batch if not explicitly set by user interaction
    // Or if previous batch is now full
    if (batchesData[currentBatch] && batchesData[currentBatch].length >= 8) {
        // If current batch is full, find the next one that has space or the last one (new)
        let foundNew = false;
        for (let i = 1; i <= maxBatch + 1; i++) {
            if (!batchesData[i] || batchesData[i].length < 8) {
                currentBatch = i;
                foundNew = true;
                break;
            }
        }
        if (!foundNew) currentBatch = maxBatch + 1;
    }

    occupiedSeats = batchesData[currentBatch] || [];
    
    renderBatchSelector();
    renderSeatAvailability();
}

function renderBatchSelector() {
    const container = document.getElementById('batchButtons');
    if (!container) return;
    container.innerHTML = '';

    availableBatches.forEach(bn => {
        const btn = document.createElement('button');
        btn.textContent = `Armada ${bn}`;
        btn.className = `px-4 py-2 rounded-xl text-xs font-bold transition-all border ${
            currentBatch === bn 
            ? 'bg-blue-600 text-white border-blue-600 shadow-md' 
            : 'bg-white dark:bg-slate-700 text-slate-500 border-slate-200 dark:border-slate-600 hover:bg-slate-50'
        }`;
        btn.onclick = (e) => {
            e.preventDefault();
            currentBatch = bn;
            fetchOccupiedSeats(); // Re-fetch to apply filtering
        };
        container.appendChild(btn);
    });
}

function handleSeatOptionChange() {
    // This function is still called by UI but logic is handled by fetchOccupiedSeats
    fetchOccupiedSeats();
}

function renderSeatAvailability() {
    const seatIds = ['CC', '1', '2', '3', '4', '5', '6', '7'];
    seatIds.forEach(id => {
        const btn = document.getElementById(`seat-${id}`);
        if (!btn) return;
        
        if (occupiedSeats.includes(id)) {
            btn.disabled = true;
            btn.classList.remove('bg-white', 'dark:bg-slate-700', 'text-slate-600', 'dark:text-slate-300', 'bg-sr-blue', 'text-white', 'scale-105');
            btn.classList.add('bg-black', 'text-gray-500', 'cursor-not-allowed', 'opacity-50');
            if (selectedSeats.includes(id)) {
                selectedSeats = selectedSeats.filter(s => s !== id);
                calculatePrice();
            }
        } else {
            btn.disabled = false;
            btn.classList.remove('bg-black', 'text-gray-500', 'cursor-not-allowed', 'opacity-50');
            if (!selectedSeats.includes(id)) {
                btn.classList.add('bg-white', 'dark:bg-slate-700', 'text-slate-600', 'dark:text-slate-300');
            } else {
                btn.classList.remove('bg-white', 'dark:bg-slate-700', 'text-slate-600', 'dark:text-slate-300');
                btn.classList.add('bg-sr-blue', 'dark:bg-blue-600', 'text-white', 'scale-105');
            }
        }
    });
}
// --- END SEAT AVAILABILITY LOGIC ---

async function loadBookingData() {
    const data = await fetchData();
    if (!data) return;

    routes = data.routes || [];
    
    // Sort: Padang - Bukittinggi first
    routes.sort((a, b) => {
        const nameA = `${a.origin} - ${a.destination}`.toLowerCase();
        const nameB = `${b.origin} - ${b.destination}`.toLowerCase();
        if (nameA.includes('padang') && nameA.includes('bukittinggi')) return -1;
        if (nameB.includes('padang') && nameB.includes('bukittinggi')) return 1;
        return 0;
    });

    renderRouteOptions();
    
    // Calculate and update Sidebar Counts
    const bookings = data.bookings || [];
    const pendingValidation = bookings.filter(b => b.validationStatus === 'Menunggu Validasi').length;
    
    // Grouping Logic to match app.js (Dispatcher)
    // 1. Group by Time + Route
    // 2. Split into Batches (Max 8)
    const groups = {};
    bookings.forEach(b => {
        if (b.status !== 'Pending' && b.status !== 'Confirmed') return; 
        
        const key = `${b.date}_${b.time}_${b.routeId}`;
        if (!groups[key]) {
            groups[key] = [];
        }
        groups[key].push(b);
    });

    let pendingDispatchGroupCount = 0;
    Object.values(groups).forEach(groupPassengers => {
        const batchSize = 8;
        const fleetBatches = []; 
        
        groupPassengers.forEach(p => {
             let placed = false;
             const pSeats = p.seatNumbers ? p.seatNumbers.split(',').map(s => s.trim()) : [];
             
             for (let i = 0; i < fleetBatches.length; i++) {
                 const batch = fleetBatches[i];
                 const currentLoad = batch.reduce((sum, bp) => sum + (parseInt(bp.seatCount) || 1), 0);
                 const pLoad = parseInt(p.seatCount) || 1;
                 
                 if (currentLoad + pLoad > batchSize) continue; 
                 

                 let conflict = false;
                 if (pSeats.length > 0) {
                     const batchSeats = [];
                     batch.forEach(bp => {
                         if (bp.seatNumbers) bp.seatNumbers.split(',').forEach(s => batchSeats.push(s.trim()));
                     });
                     if (pSeats.some(s => batchSeats.includes(s))) conflict = true;
                 }
                 
                 if (!conflict) {
                     batch.push(p);
                     placed = true;
                     break;
                 }
             }
             
             if (!placed) fleetBatches.push([p]);
        });
        
        pendingDispatchGroupCount += fleetBatches.length;
    });

    const elValidation = document.getElementById('pendingValidationCount');
    const elDispatch = document.getElementById('pendingDispatchCount');
    
    if (elValidation) elValidation.innerText = pendingValidation;
    if (elDispatch) elDispatch.innerText = pendingDispatchGroupCount; 
}

function setupEventListeners() {
    // Service Type Buttons
    document.querySelectorAll('.service-type-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            // Update UI
            document.querySelectorAll('.service-type-btn').forEach(b => {
                b.classList.remove('bg-white', 'dark:bg-slate-600', 'shadow-sm', 'text-sr-blue', 'dark:text-white');
                b.classList.add('text-slate-500', 'hover:bg-white/50', 'dark:hover:bg-slate-600');
            });
            btn.classList.remove('text-slate-500', 'hover:bg-white/50', 'dark:hover:bg-slate-600');
            btn.classList.add('bg-white', 'dark:bg-slate-600', 'shadow-sm', 'text-sr-blue', 'dark:text-white');
            
            currentServiceType = btn.dataset.type;
            

            const multiDropArea = document.getElementById('multiDropArea');
            const ktmSection = document.getElementById('ktmUploadSection');
            const seatSelection = document.getElementById('seatSelectionArea');
            const batchSelection = document.getElementById('batchSelectorArea');
            const durationInput = document.getElementById('durationInputArea');
            const seatCountInput = document.getElementById('seatCountInputArea');
            const categorySection = document.getElementById('passengerCategorySection');


            seatSelection.classList.add('hidden');
            batchSelection.classList.add('hidden');
            durationInput.classList.add('hidden');
            seatCountInput.classList.add('hidden');
            if (multiDropArea) multiDropArea.classList.add('hidden');
            if (ktmSection) ktmSection.classList.add('hidden');
            if (categorySection) categorySection.classList.add('hidden');

            if (currentServiceType === 'Travel') {
                seatSelection.classList.remove('hidden');
                if (availableBatches.length > 1) batchSelection.classList.remove('hidden');
                if (categorySection) categorySection.classList.remove('hidden');
                // Check current category to show/hide KTM
                const category = document.querySelector('input[name="passengerCategory"]:checked').value;
                if (category === 'Pelajar' && ktmSection) ktmSection.classList.remove('hidden');
            } else if (currentServiceType === 'Dropping') {
                if(multiDropArea) multiDropArea.classList.remove('hidden');
            } else if (currentServiceType === 'Carter') {
                durationInput.classList.remove('hidden');
            }
            
            // Reset Multi Drop Checkbox
            const multiDropCheck = document.getElementById('multiDrop');
            if(multiDropCheck) multiDropCheck.checked = false;
            
            calculatePrice();
        });
    });

    // Passenger Category Radio Buttons
    document.querySelectorAll('input[name="passengerCategory"]').forEach(radio => {
        radio.addEventListener('change', (e) => {
            const category = e.target.value;
            const ktmSection = document.getElementById('ktmUploadSection');
            if (ktmSection) {
                if (category === 'Pelajar') {
                    ktmSection.classList.remove('hidden');
                } else {
                    ktmSection.classList.add('hidden');
                    removeKtm();
                }
            }
            calculatePrice();
        });
    });

    // Form Inputs
    const routeSelect = document.getElementById('routeSelect');
    if (routeSelect) {
        routeSelect.addEventListener('change', () => {
            updateTimeOptions();
            fetchOccupiedSeats();
            calculatePrice();
        });
    }

    const ktmInput = document.getElementById('ktmInput');
    if (ktmInput) ktmInput.addEventListener('change', handleKtmUpload);
    
    const seatCountInput = document.getElementById('seatCountInput');
    if (seatCountInput) seatCountInput.addEventListener('input', calculatePrice);

    const durationInput = document.getElementById('durationInput');
    if (durationInput) durationInput.addEventListener('input', calculatePrice);


    const dateInput = document.getElementById('dateInput');
    if (dateInput) dateInput.addEventListener('change', fetchOccupiedSeats);

    const timeInput = document.getElementById('timeInput');
    if (timeInput) timeInput.addEventListener('change', fetchOccupiedSeats);


    document.querySelectorAll('input[name="paymentMethod"]').forEach(radio => {
        radio.addEventListener('change', (e) => {
            const cashDiv = document.getElementById('paymentCash');
            const transferDiv = document.getElementById('paymentTransfer');
            const dpDiv = document.getElementById('paymentDP');
            
            if (cashDiv) cashDiv.classList.add('hidden');
            if (transferDiv) transferDiv.classList.add('hidden');
            if (dpDiv) dpDiv.classList.add('hidden');
            
            if (e.target.value === 'Cash' && cashDiv) cashDiv.classList.remove('hidden');
            else if (e.target.value === 'Transfer' && transferDiv) transferDiv.classList.remove('hidden');
            else if (e.target.value === 'DP' && dpDiv) dpDiv.classList.remove('hidden');
            else if (e.target.value === 'Belum Bayar') {

            }
        });
    });


    document.querySelectorAll('input[name="dpMethod"]').forEach(radio => {
        radio.addEventListener('change', (e) => {
            const dpCashDiv = document.getElementById('dpCashDetails');
            const dpTransferDiv = document.getElementById('dpTransferDetails');
            
            if (e.target.value === 'Cash') {
                if (dpCashDiv) dpCashDiv.classList.remove('hidden');
                if (dpTransferDiv) dpTransferDiv.classList.add('hidden');
            } else {
                if (dpCashDiv) dpCashDiv.classList.add('hidden');
                if (dpTransferDiv) dpTransferDiv.classList.remove('hidden');
            }
        });
    });

    // Submit Button
    const submitBtn = document.getElementById('submitBookingBtn');
    if (submitBtn) submitBtn.addEventListener('click', saveBooking);
    
    // Payment Proof Upload (Main)
    const proofInput = document.getElementById('paymentProofInput');
    if (proofInput) proofInput.addEventListener('change', handleProofUpload);
    
    // Payment Proof Upload (DP)
    const dpProofInput = document.getElementById('dpProofInput');
    if (dpProofInput) dpProofInput.addEventListener('change', handleDpProofUpload);
    
    const removeProofBtn = document.getElementById('removeProofBtn');
    if (removeProofBtn) removeProofBtn.addEventListener('click', removeProof);
    
    const removeDpProofBtn = document.getElementById('removeDpProofBtn');
    if (removeDpProofBtn) removeDpProofBtn.addEventListener('click', removeDpProof);
    
    // Multi-Drop Checkbox
    const multiDrop = document.getElementById('multiDrop');
    if (multiDrop) multiDrop.addEventListener('change', calculatePrice);

    // Dark Mode & Fullscreen
    const darkModeBtn = document.getElementById('toggleDarkModeBtn');
    if (darkModeBtn) darkModeBtn.addEventListener('click', toggleDarkMode);
    
    const fullscreenBtn = document.getElementById('toggleFullscreenBtn');
    if (fullscreenBtn) fullscreenBtn.addEventListener('click', toggleFullscreen);
}


function setServiceType(type) {
    currentServiceType = type;
    const seatSelection = document.getElementById('seatSelectionArea');
    const batchSelection = document.getElementById('batchSelectorArea');
    const durationInput = document.getElementById('durationInputArea');
    const seatCountInput = document.getElementById('seatCountInputArea');
    const multiDropArea = document.getElementById('multiDropArea');

    if (type === 'Travel') {
        seatSelection.classList.remove('hidden');
        if(availableBatches.length > 1) batchSelection.classList.remove('hidden');
        durationInput.classList.add('hidden');
        seatCountInput.classList.add('hidden');
        if (multiDropArea) multiDropArea.classList.add('hidden');
    } else if (type === 'Dropping') {
        seatSelection.classList.add('hidden');
        batchSelection.classList.add('hidden');
        durationInput.classList.add('hidden');
        seatCountInput.classList.add('hidden');
        if (multiDropArea) multiDropArea.classList.remove('hidden');
    } else {
        // Carter
        seatSelection.classList.add('hidden');
        batchSelection.classList.add('hidden');
        durationInput.classList.remove('hidden');
        seatCountInput.classList.add('hidden');
        if (multiDropArea) multiDropArea.classList.add('hidden');
    }
    calculatePrice();
}

function renderRouteOptions() {
    const select = document.getElementById('routeSelect');
    select.innerHTML = '<option value="" disabled selected>Pilih Rute</option>';
    routes.forEach(route => {
        const option = document.createElement('option');
        option.value = route.id;
        option.textContent = `${route.origin} - ${route.destination}`;
        select.appendChild(option);
    });
}

function toggleSeat(seatId) {
    const btn = document.getElementById(`seat-${seatId}`);
    if (selectedSeats.includes(seatId)) {
        selectedSeats = selectedSeats.filter(id => id !== seatId);
        btn.classList.remove('bg-sr-blue', 'dark:bg-blue-600', 'text-white', 'scale-105');
        btn.classList.add('bg-white', 'dark:bg-slate-700', 'text-slate-600', 'dark:text-slate-300');
    } else {
        selectedSeats.push(seatId);
        btn.classList.remove('bg-white', 'dark:bg-slate-700', 'text-slate-600', 'dark:text-slate-300');
        btn.classList.add('bg-sr-blue', 'dark:bg-blue-600', 'text-white', 'scale-105');
    }
    calculatePrice();
}

// function to update time options based on route
function updateTimeOptions() {
    const routeId = document.getElementById('routeSelect').value;
    const route = routes.find(r => r.id === routeId);
    const timeSelect = document.getElementById('timeInput');
    
    if (timeSelect) {
        timeSelect.innerHTML = '';
        if (route && route.schedules) {
            route.schedules.forEach(time => {
                const option = document.createElement('option');
                option.value = time;
                option.textContent = time;
                timeSelect.appendChild(option);
            });
        }
    }
}

function calculatePrice() {
    const routeId = document.getElementById('routeSelect').value;
    const route = routes.find(r => r.id === routeId);
    if (!route) {
        currentPrice = 0;
        updatePriceDisplay();
        return;
    }

       // Determine Price based on Service Type
    if (currentServiceType === 'Carter') {
        currentPrice = route.prices.carter;
    } else if (currentServiceType === 'Dropping') {
        currentPrice = route.prices.dropping;
    } else {
        // Travel: Check Category
        const category = document.querySelector('input[name="passengerCategory"]:checked').value;
        if (category === 'Pelajar') {
            currentPrice = route.prices.pelajar;
        } else {
            currentPrice = route.prices.umum;
        }
    }
    
    updatePriceDisplay();
}

function updatePriceDisplay() {
    document.getElementById('totalPriceDisplay').textContent = formatRupiah(currentPrice);
    document.getElementById('submitBookingBtn').disabled = currentPrice === 0;
}

let isSaving = false;

async function saveBooking() {
    if (isSaving) return;
    
    try {
        const routeId = document.getElementById('routeSelect').value;
        const route = routes.find(r => r.id === routeId);
        
        if (!routeId) return alert("Pilih Rute!");
        if (currentServiceType === 'Travel' && selectedSeats.length === 0) return alert("Pilih Kursi!");

        let passengerType = 'Umum';
        if (currentServiceType === 'Travel') {
            const categoryInput = document.querySelector('input[name="passengerCategory"]:checked');
            if (!categoryInput) return alert("Pilih Kategori Penumpang!");
            passengerType = categoryInput.value; // 'Umum' or 'Pelajar'
        }

        if (passengerType === 'Pelajar' && !ktmBase64) {
            return alert("Wajib upload bukti KTM / Kartu Pelajar!");
        }

        const paymentMethod = document.querySelector('input[name="paymentMethod"]:checked')?.value || 'Cash';
        let paymentStatus = 'Menunggu Validasi';
        let validationStatus = 'Menunggu Validasi';
        
        if (paymentMethod === 'Cash') {
            if (!document.getElementById('paymentLoc').value) return alert("Isi Lokasi Pembayaran!");
            paymentStatus = 'Lunas';
            validationStatus = 'Valid';
        } else if (paymentMethod === 'Transfer') {
            if (!paymentProofBase64) return alert("Wajib Upload Bukti Transfer!");
            paymentStatus = 'Menunggu Validasi';
            validationStatus = 'Menunggu Validasi';
        } else if (paymentMethod === 'DP') {
            const dpAmount = parseInt(document.getElementById('dpAmount').value) || 0;
            if (dpAmount < 50000) return alert("Minimal DP Rp 50.000");
            
            const dpMethod = document.querySelector('input[name="dpMethod"]:checked')?.value || 'Cash';
            if (dpMethod === 'Cash') {
                if (!document.getElementById('dpLocation').value) return alert("Isi Lokasi Terima DP!");
                // Map DP cash fields to main fields for DB
                document.getElementById('paymentLoc').value = document.getElementById('dpLocation').value;
                document.getElementById('paymentRecv').value = document.getElementById('dpReceiver').value;
            } else {
                if (!dpProofBase64) return alert("Wajib Upload Bukti DP!");
                paymentProofBase64 = dpProofBase64; // Map DP proof to main proof variable
            }
            
            paymentStatus = 'DP';
        } else if (paymentMethod === 'Belum Bayar') {
            paymentStatus = 'Belum Bayar';
            validationStatus = 'Menunggu Pembayaran';
            // No proof required
        }

        const manualSeatCount = parseInt(document.getElementById('seatCountInput').value) || 1;
        const duration = parseInt(document.getElementById('durationInput').value) || 1;

        // Save Input Memory (Handle both main and DP fields)
        const finalRecv = document.getElementById('paymentRecv')?.value || document.getElementById('dpReceiver')?.value;
        const finalLoc = document.getElementById('paymentLoc')?.value || document.getElementById('dpLocation')?.value;
        
        saveInputMemory(finalRecv, finalLoc);

        const bookingData = {
            id: Date.now().toString(),
            serviceType: currentServiceType,
            routeId: routeId,
            date: document.getElementById('dateInput').value,
            time: currentServiceType === 'Travel' ? document.getElementById('timeInput').value : '',
            passengerName: document.getElementById('passengerName').value,
            passengerPhone: document.getElementById('passengerPhone').value,
            passengerType: passengerType,
            seatCount: currentServiceType === 'Travel' ? selectedSeats.length : manualSeatCount,
            selectedSeats: selectedSeats,
            duration: duration,
            totalPrice: currentPrice,
            paymentMethod: paymentMethod, // Will be 'DP' (or Cash/Transfer)
            paymentStatus: paymentStatus,
            validationStatus: validationStatus,
            paymentLocation: finalLoc || '',
            paymentReceiver: finalRecv || '',
            paymentProof: paymentProofBase64,
            seatNumbers: currentServiceType === 'Travel' ? selectedSeats.join(', ') : 'Full Unit',
            ktmProof: ktmBase64,
            downPaymentAmount: parseInt(document.getElementById('dpAmount')?.value) || 0,
            type: 'Unit',
            seatCapacity: manualSeatCount,
            priceType: 'Kantor',
            packageType: 'Unit',
            routeName: route ? `${route.origin} - ${route.destination}` : '',
            pickupAddress: document.getElementById('pickupAddress').value + (document.getElementById('pickupMapLink').value ? ', ' + document.getElementById('pickupMapLink').value : ''),
            dropoffAddress: document.getElementById('dropoffAddress').value + (document.getElementById('dropoffMapLink').value ? ', ' + document.getElementById('dropoffMapLink').value : ''),
            batchNumber: currentBatch
        };
        
        isSaving = true;
        const btn = document.getElementById('submitBookingBtn');
        btn.disabled = true;
        btn.textContent = 'Menyimpan...';

        const res = await postData('create_booking', { data: bookingData });
        
        if (res.status === 'success') {
            alert("Booking Berhasil Disimpan!");
            window.location.href = 'booking_management.php';
        } else {
            alert("Gagal Simpan: " + (res.message || JSON.stringify(res)));
            isSaving = false;
            btn.disabled = false;
            btn.textContent = 'Proses Booking';
        }
    } catch (error) {
        console.error(error);
        alert("Terjadi kesalahan sistem: " + error.message);
        isSaving = false;
        document.getElementById('submitBookingBtn').disabled = false;
        document.getElementById('submitBookingBtn').textContent = 'Proses Booking';
    }
}

function updateTime() {
    const now = new Date();
    const timeEl = document.getElementById('currentTime');
    const dateEl = document.getElementById('currentDate');
    if (timeEl) timeEl.textContent = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
    if (dateEl) dateEl.textContent = now.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
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

// function loadingSpinner() {
//     const spinner = document.getElementById('loadingSpinner');
//     spinner.style.display = 'block';

// } 
