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
                uploadLabel.classList.add('hidden'); // Hide upload button when file exists
            }
            
            // Update Label Text (Fallback)
            const labelText = document.getElementById('proofLabel');
            if(labelText) labelText.innerHTML = '<i class="bi bi-check-circle-fill text-green-500 text-lg block mb-1"></i>Ganti File';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function removeProof() {
    paymentProofBase64 = null;
    document.getElementById('proofPreviewContainer').classList.add('hidden');
    document.getElementById('uploadProofLabel').classList.remove('hidden');
    document.getElementById('proofLabel').innerHTML = 'Upload Bukti Transfer';
    document.getElementById('paymentProofInput').value = ''; // Changed input ID to paymentProofInput
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

async function fetchOccupiedSeats() {
    const routeId = document.getElementById('routeSelect').value;
    const date = document.getElementById('dateInput').value;
    const time = document.getElementById('timeInput').value;
    
    if (!routeId || !date || !time) {
        occupiedSeats = []; // Clear occupied seats if criteria not met
        renderSeatAvailability();
        return;
    }

    try {
        const res = await fetch(`api.php?action=get_occupied_seats&routeId=${routeId}&date=${date}&time=${time}`);
        const bookings = await res.json();
        
        // Process bookings to find occupied seats in the *latest* batch
        processOccupiedSeats(bookings);
    } catch (e) {
        console.error("Error fetching seats:", e);
        occupiedSeats = []; // Clear on error
        renderSeatAvailability();
    }
}

function processOccupiedSeats(bookings) {
    let batchesMap = new Map();
    let currentBatchForUnassigned = 1;
    const BAT_CAPACITY = 8;
    
    // Sort by ID to simulate time of booking
    bookings.sort((a, b) => parseInt(a.id) - parseInt(b.id));

    bookings.forEach(b => {
        let rawBatch = parseInt(b.batchNumber) || 1;
        if (rawBatch > 1) {
            if (!batchesMap.has(rawBatch)) batchesMap.set(rawBatch, []);
            batchesMap.get(rawBatch).push(b);
        } else {
            // Find space
            let seatsCount = parseInt(b.seatCount) || 1;
            if (b.seatNumbers) seatsCount = b.seatNumbers.split(',').map(s=>s.trim()).filter(s=>s!=='').length;
            
            let placed = false;
            let checkBatch = 1;
            
            while (!placed) {
                let currentBatchList = batchesMap.get(checkBatch) || [];
                let totalPaxInBatch = 0;
                
                currentBatchList.forEach(existing => {
                    if(existing.seatNumbers) totalPaxInBatch += existing.seatNumbers.split(',').filter(s => s.trim() !== '').length;
                    else totalPaxInBatch += parseInt(existing.seatCount) || 1; // Fallback
                });
                
                if (totalPaxInBatch + seatsCount <= BAT_CAPACITY || totalPaxInBatch === 0) {
                    if (!batchesMap.has(checkBatch)) batchesMap.set(checkBatch, []);
                    batchesMap.get(checkBatch).push(b);
                    placed = true;
                } else {
                    checkBatch++;
                }
            }
        }
    });

    // Determine the "last/active" batch that is currently being filled
    let maxBatchKey = 1;
    for (let k of batchesMap.keys()) {
        if (k > maxBatchKey) maxBatchKey = k;
    }

    const lastBatchBookings = batchesMap.get(maxBatchKey) || [];
    let lastBatchPaxCount = 0;
    let lastBatchOccupiedSeats = [];

    lastBatchBookings.forEach(bk => {
        const seatsRaw = bk.seatNumbers || '';
        const seatParts = seatsRaw.split(',').map(s => s.trim()).filter(s => s);
        if (seatParts.length === 0) {
            lastBatchPaxCount += 1;
        } else {
            lastBatchPaxCount += seatParts.length;
            seatParts.forEach(s => lastBatchOccupiedSeats.push(s));
        }
    });

    const seatOption = document.querySelector('input[name="seatOption"]:checked')?.value || 'same';
    
    if (seatOption === 'new') {
        occupiedSeats = []; // Start fresh array
    } else {
        // "Same Fleet"
        if (lastBatchPaxCount >= BAT_CAPACITY) {
            // If full, force new batch visually
            occupiedSeats = [];
        } else {
            // Show occupied in the current active filling batch
            occupiedSeats = lastBatchOccupiedSeats;
        }
    }
    
    renderSeatAvailability();
}

function handleSeatOptionChange() {
    // Trigger re-calculation
    fetchOccupiedSeats();
}

function renderSeatAvailability() {
    // Reset all seats
    // Reset all seats
    const seatIds = ['CC', '1', '2', '3', '4', '5', '6', '7'];
    seatIds.forEach(id => {
        const btn = document.getElementById(`seat-${id}`);
        if (!btn) return;
        
        // Check if occupied
        if (occupiedSeats.includes(id)) {
            btn.disabled = true;
            btn.classList.remove('bg-white', 'dark:bg-slate-700', 'text-slate-600', 'dark:text-slate-300', 'bg-sr-blue', 'text-white', 'scale-105');
            btn.classList.add('bg-black', 'text-gray-500', 'cursor-not-allowed', 'opacity-50');
            // If it was selected, deselect it
            if (selectedSeats.includes(id)) {
                toggleSeat(id); // This will remove it
            }
        } else {
            btn.disabled = false;
            btn.classList.remove('bg-black', 'text-gray-500', 'cursor-not-allowed', 'opacity-50');
            // Restore normal style if not selected
            if (!selectedSeats.includes(id)) {
                btn.classList.add('bg-white', 'dark:bg-slate-700', 'text-slate-600', 'dark:text-slate-300');
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
                 
                 // Check conflict
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
            
            // Toggle Multi Drop & KTM Section
            const multiDropArea = document.getElementById('multiDropArea');
            const ktmSection = document.getElementById('ktmUploadSection');
            const seatSelection = document.getElementById('seatSelectionArea');
            const durationInput = document.getElementById('durationInputArea');
            const seatCountInput = document.getElementById('seatCountInputArea');
            const categorySection = document.getElementById('passengerCategorySection');

            // Reset visibility for all service type specific areas
            seatSelection.classList.add('hidden');
            durationInput.classList.add('hidden');
            seatCountInput.classList.add('hidden');
            if (multiDropArea) multiDropArea.classList.add('hidden');
            if (ktmSection) ktmSection.classList.add('hidden');
            if (categorySection) categorySection.classList.add('hidden');

            if (currentServiceType === 'Travel') {
                seatSelection.classList.remove('hidden');
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

    const passengerTypeSelect = document.getElementById('passengerTypeSelect');
    if (passengerTypeSelect) {
        passengerTypeSelect.addEventListener('change', (e) => {
            const type = e.target.value;
            const ktmArea = document.getElementById('ktmUploadArea');
            const ktmSection = document.getElementById('ktmUploadSection'); // Use ktmUploadSection for overall visibility
            if (ktmArea && ktmSection) {
                if (type === 'Mahasiswa / Pelajar') {
                    ktmSection.classList.remove('hidden'); // Show the whole section
                } else {
                    ktmSection.classList.add('hidden'); // Hide the whole section
                    removeKtm(); // Clear KTM data if hidden
                }
            }
            calculatePrice();
        });
    }

    const ktmInput = document.getElementById('ktmInput');
    if (ktmInput) ktmInput.addEventListener('change', handleKtmUpload);
    const removeKtmBtn = document.getElementById('removeKtmBtn');
    if (removeKtmBtn) removeKtmBtn.addEventListener('click', removeKtm);
    
    const seatCountInput = document.getElementById('seatCountInput');
    if (seatCountInput) seatCountInput.addEventListener('input', calculatePrice);

    const durationInput = document.getElementById('durationInput');
    if (durationInput) durationInput.addEventListener('input', calculatePrice);

    // Seat Availability Listeners
    const dateInput = document.getElementById('dateInput');
    if (dateInput) dateInput.addEventListener('change', fetchOccupiedSeats);

    const timeInput = document.getElementById('timeInput');
    if (timeInput) timeInput.addEventListener('change', fetchOccupiedSeats);

    // Payment Method
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
        });
    });

    // Submit Button
    const submitBtn = document.getElementById('submitBookingBtn');
    if (submitBtn) submitBtn.addEventListener('click', saveBooking);
    
    // Payment Proof Upload
    const proofInput = document.getElementById('paymentProofInput');
    if (proofInput) {
        proofInput.addEventListener('change', handleProofUpload);
    }
    const removeProofBtn = document.getElementById('removeProofBtn');
    if (removeProofBtn) {
        removeProofBtn.addEventListener('click', removeProof);
    }
    
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
    const durationInput = document.getElementById('durationInputArea');
    const seatCountInput = document.getElementById('seatCountInputArea');
    const multiDropArea = document.getElementById('multiDropArea');

    if (type === 'Travel') {
        seatSelection.classList.remove('hidden');
        durationInput.classList.add('hidden');
        seatCountInput.classList.add('hidden');
        if (multiDropArea) multiDropArea.classList.add('hidden');
    } else if (type === 'Dropping') {
        seatSelection.classList.add('hidden');
        durationInput.classList.add('hidden');
        seatCountInput.classList.add('hidden');
        if (multiDropArea) multiDropArea.classList.remove('hidden');
    } else {
        // Carter
        seatSelection.classList.add('hidden');
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
    const passengerType = document.getElementById('passengerTypeSelect').value;
    const duration = parseInt(document.getElementById('durationInput').value) || 1;
    const manualSeatCount = parseInt(document.getElementById('seatCountInput').value) || 1;
    const isMultiDrop = document.getElementById('multiDrop')?.checked || false;

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
            paymentStatus = 'DP';
        }

        const manualSeatCount = parseInt(document.getElementById('seatCountInput').value) || 1;
        const duration = parseInt(document.getElementById('durationInput').value) || 1;

        // Save Input Memory
        saveInputMemory(
            document.getElementById('paymentRecv')?.value,
            document.getElementById('paymentLoc')?.value
        );

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
            paymentMethod: paymentMethod,
            paymentStatus: paymentStatus,
            validationStatus: validationStatus,
            paymentLocation: document.getElementById('paymentLoc')?.value || '',
            paymentReceiver: document.getElementById('paymentRecv')?.value || '',
            paymentProof: paymentProofBase64,
            seatNumbers: currentServiceType === 'Travel' ? selectedSeats.join(', ') : 'Full Unit',
            ktmProof: ktmBase64,
            downPaymentAmount: parseInt(document.getElementById('dpAmount')?.value) || 0,
            type: 'Unit',
            seatCapacity: manualSeatCount,
            priceType: 'Kantor',
            packageType: 'Unit',
            routeName: route ? `${route.origin} - ${route.destination}` : '',
            pickupAddress: document.getElementById('pickupAddress').value,
            dropoffAddress: document.getElementById('dropoffAddress').value
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
    document.getElementById('currentTime').textContent = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
    document.getElementById('currentDate').textContent = now.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
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
