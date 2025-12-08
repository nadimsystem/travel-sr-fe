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
    document.getElementById('paymentProofInput').value = ''; // Reset input
    document.getElementById('proofPreviewContainer').classList.add('hidden');
    document.getElementById('uploadProofLabel').classList.remove('hidden');
    document.getElementById('proofLabel').innerHTML = '<i class="bi bi-cloud-upload text-lg block mb-1"></i>Upload Bukti';
}

async function saveBooking() {
    const routeId = document.getElementById('routeSelect').value;
    const passengerType = document.getElementById('passengerType').value;
    const paymentMethod = document.querySelector('input[name="paymentMethod"]:checked')?.value || 'Cash';
    
    if (!routeId || !date || !time) {
        occupiedSeats = []; // Clear occupied seats if criteria not met
        renderSeatAvailability();
        return;
    }
}

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
    // 1. Flatten all booked seats
    // We need to simulate the dispatcher's batching logic to find the "current" batch
    // But simplified: We just want to know which seats are taken in the *filling* car.
    
    // Actually, to support "Armada Baru" (New Fleet), we need to know if a seat is taken in the *current* fleet.
    // If I pick "Same Fleet", I want to see what's taken.
    // If I pick "New Fleet", I want to see everything empty.
    
    // Let's reconstruct the batches
    let allPassengers = [];
    bookings.forEach(b => {
        const seats = b.seatNumbers ? b.seatNumbers.split(',').map(s => s.trim()) : [];
        seats.forEach(s => allPassengers.push({ seat: s, bookingId: b.id }));
    });

    // Batching logic (Max 8 per batch)
    // We need to group them into batches of 8.
    // But wait, the dispatcher groups by *count*, not by seat number collision (yet).
    // However, physically, we can't have collision.
    // So let's assume the dispatcher WILL handle collision.
    
    // For the UI:
    // "Same Fleet" means: Show me the seats taken in the *last* batch if it's not full.
    // If the last batch is full (8 pax), then start a new batch (all empty).
    
    const batchSize = 8;
    const totalPax = allPassengers.length;
    const fullBatches = Math.floor(totalPax / batchSize);
    const remainder = totalPax % batchSize;
    
    // If remainder == 0 and totalPax > 0, it means the last car is full. Next booking is new car.
    // So occupied is empty (new car).
    // If remainder > 0, the last car has `remainder` passengers. We need to find WHICH seats they occupy.
    // BUT, since we don't store batch ID, we don't strictly know which booking is in which batch 
    // unless we sort them exactly like the dispatcher (Date/Time creation?).
    // The API returns them in some order. Let's assume ID order or creation order.
    
    // Let's just assume we want to avoid *collisions* with ANY existing booking 
    // UNLESS "New Fleet" is selected.
    // Wait, if I have Seat 1 taken in Car 1.
    // If I select "Same Fleet", I CANNOT pick Seat 1.
    // If I select "New Fleet", I CAN pick Seat 1.
    
    // So, for "Same Fleet":
    // We should mark ALL seats that are currently booked as occupied?
    // No, because if Car 1 is full, I should be able to book Seat 1 for Car 2.
    // But "Same Fleet" implies I want to join the *current* car.
    // If Car 1 is full, "Same Fleet" is effectively "Next Fleet".
    
    // Let's try this logic:
    // 1. Sort bookings by ID (proxy for creation time).
    // 2. Assign them to batches.
    // 3. Find the last batch.
    // 4. If last batch is full, occupied = [].
    // 5. If last batch is not full, occupied = seats in that batch.
    
    // Sort bookings (assuming API returns array of objects)
    // We need the full booking objects to sort.
    // The API returns { seatNumbers, seatCount, ... }
    
    // Since we don't have full timestamps here easily without fetching more data,
    // let's try a simpler approach requested by user:
    // "Centang default 'mobil yang sama'... memilih seat yang tersisa"
    
    // Let's map all currently taken seats.
    // If we simply mark ALL taken seats as black, we prevent booking Seat 1 for Car 2.
    // That's bad.
    
    // Let's use the Batch Logic.
    let batches = [[]]; // Array of arrays of seat numbers
    let currentBatchIndex = 0;
    
    // We need to iterate through passengers.
    // We assume the API returns bookings in insertion order (ID ASC).
    // We need to flatten into individual passengers.
    let flatPassengers = [];
    bookings.forEach(b => {
        const seats = b.seatNumbers ? b.seatNumbers.split(',').map(s => s.trim()) : [];
        seats.forEach(s => flatPassengers.push(s));
    });
    
    // Fill batches
    flatPassengers.forEach(seat => {
        if (batches[currentBatchIndex].length >= 8) {
            currentBatchIndex++;
            batches[currentBatchIndex] = [];
        }
        batches[currentBatchIndex].push(seat);
    });
    
    // Now, determine occupied seats for UI
    const seatOption = document.querySelector('input[name="seatOption"]:checked')?.value || 'same'; // Default to 'same'
    
    if (seatOption === 'new') {
        occupiedSeats = []; // All free
    } else {
        // "Same Fleet"
        // If the current batch is full, we are starting a new one -> All free.
        if (batches[currentBatchIndex].length >= 8) {
            occupiedSeats = [];
        } else {
            // Show occupied seats in this batch
            occupiedSeats = batches[currentBatchIndex];
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
    for (let i = 1; i <= 8; i++) {
        const btn = document.getElementById(`seat-${i}`);
        if (!btn) continue;
        
        // Check if occupied
        if (occupiedSeats.includes(i.toString())) {
            btn.disabled = true;
            btn.classList.remove('bg-white', 'dark:bg-slate-700', 'text-slate-600', 'dark:text-slate-300', 'bg-sr-blue', 'text-white', 'scale-105');
            btn.classList.add('bg-black', 'text-gray-500', 'cursor-not-allowed', 'opacity-50');
            // If it was selected, deselect it
            if (selectedSeats.includes(i.toString())) {
                toggleSeat(i.toString()); // This will remove it
            }
        } else {
            btn.disabled = false;
            btn.classList.remove('bg-black', 'text-gray-500', 'cursor-not-allowed', 'opacity-50');
            // Restore normal style if not selected
            if (!selectedSeats.includes(i.toString())) {
                btn.classList.add('bg-white', 'dark:bg-slate-700', 'text-slate-600', 'dark:text-slate-300');
            }
        }
    }
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
}

function setupEventListeners() {
    // Service Type Buttons
    document.querySelectorAll('.service-type-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            setServiceType(e.target.dataset.type);
            document.querySelectorAll('.service-type-btn').forEach(b => {
                b.classList.remove('bg-white', 'dark:bg-slate-600', 'shadow-sm', 'text-sr-blue', 'dark:text-white');
                b.classList.add('text-slate-400');
            });
            e.target.classList.remove('text-slate-400');
            e.target.classList.add('bg-white', 'dark:bg-slate-600', 'shadow-sm', 'text-sr-blue', 'dark:text-white');
        });
    });

    // Form Inputs
    // Form Inputs
    const routeSelect = document.getElementById('routeSelect');
    if (routeSelect) {
        routeSelect.addEventListener('change', calculatePrice);
        routeSelect.addEventListener('change', fetchOccupiedSeats);
    }

    const passengerTypeSelect = document.getElementById('passengerTypeSelect');
    if (passengerTypeSelect) {
        passengerTypeSelect.addEventListener('change', (e) => {
            const type = e.target.value;
            const ktmArea = document.getElementById('ktmUploadArea');
            if (ktmArea) {
                if (type === 'Mahasiswa / Pelajar') {
                    ktmArea.classList.remove('hidden');
                } else {
                    ktmArea.classList.add('hidden');
                }
            }
            calculatePrice();
        });
    }

    const ktmInput = document.getElementById('ktmInput');
    if (ktmInput) ktmInput.addEventListener('change', handleKtmUpload);
    
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



function handleKtmUpload(e) {
    const file = e.target.files[0];
    if (file) {
        document.getElementById('ktmFileName').textContent = file.name;
        const reader = new FileReader();
        reader.onload = function(e) {
            ktmBase64 = e.target.result;
        };
        reader.readAsDataURL(file);
    }
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

    if (currentServiceType === 'Travel') {
        const pricePerSeat = (passengerType === 'Mahasiswa / Pelajar') ? route.prices.pelajar : route.prices.umum;
        currentPrice = selectedSeats.length * pricePerSeat;
    } else if (currentServiceType === 'Dropping') {
        // Dropping Logic
        const routeName = `${route.origin} - ${route.destination}`;
        if (routeName.includes('Bukittinggi') || routeName.includes('Padang Panjang')) {
            currentPrice = isMultiDrop ? 960000 : 900000;
        } else if (routeName.includes('Payakumbuh')) {
            currentPrice = isMultiDrop ? 1200000 : 1100000;
        } else {
            currentPrice = route.prices.dropping || 1000000;
        }
    } else if (currentServiceType === 'Carter') {
        currentPrice = (route.prices.carter || 1500000) * duration;
    }
    
    // Update Schedule Options
    const timeSelect = document.getElementById('timeInput');
    if (route && route.schedules) {
        timeSelect.innerHTML = '';
        route.schedules.forEach(time => {
            const option = document.createElement('option');
            option.value = time;
            option.textContent = time;
            timeSelect.appendChild(option);
        });
    }

    updatePriceDisplay();
}

function updatePriceDisplay() {
    document.getElementById('totalPriceDisplay').textContent = formatRupiah(currentPrice);
    document.getElementById('submitBookingBtn').disabled = currentPrice === 0;
}

async function saveBooking() {
    const routeId = document.getElementById('routeSelect').value;
    const route = routes.find(r => r.id === routeId);
    
    if (!routeId) return alert("Pilih Rute!");
    if (currentServiceType === 'Travel' && selectedSeats.length === 0) return alert("Pilih Kursi!");

    const passengerType = document.getElementById('passengerTypeSelect').value;
    if (passengerType === 'Mahasiswa / Pelajar' && !ktmBase64) {
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
    // paymentProofBase64 is global

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
    const btn = document.getElementById('submitBookingBtn');
    btn.disabled = true;
    btn.textContent = 'Menyimpan...';

    const res = await postData('create_booking', { data: bookingData });
    
    btn.disabled = false;
    btn.textContent = 'Proses Booking';

    if (res.status === 'success') {
        alert("Booking Berhasil Disimpan!");
        window.location.href = 'booking_management.php';
    } else {
        alert("Gagal Simpan: " + (res.message || JSON.stringify(res)));
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
