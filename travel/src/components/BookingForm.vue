<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import axios from 'axios'
import Swal from 'sweetalert2'

const emit = defineEmits(['booking-created'])

// Form State
const loading = ref(false)
const error = ref(null)
const success = ref(false)
const isReady = ref(false) // Defer heavy rendering to prevent transition lag

const routes = ref([])
const formData = ref({
  passengerName: '',
  passengerPhone: '',
  routeId: '',
  date: new Date().toISOString().split('T')[0],
  time: '',
  pickupAddress: '',
  pickupMapLink: '',
  dropoffAddress: '',
  dropoffMapLink: '',
  passengerType: 'Umum',
  ktmProof: '', // Base64
  paymentMethod: 'QRIS',
  destinationAccount: '',
  transferSentDate: new Date().toISOString().split('T')[0],
  paymentProof: '', // Base64
  bookingNote: ''
})

const ktmInputRed = ref(null)
const proofInputRef = ref(null)

// Computed for dynamic values based on route
const selectedRoute = computed(() => {
    return routes.value.find(r => r.id === formData.value.routeId)
})

// Validation for Passenger Type
watch(() => formData.value.passengerType, (newType) => {
    if (newType === 'Umum') {
        formData.value.ktmProof = ''
    }
})

// Seat Map State
const occupiedSeats = ref([])
const selectedSeats = ref([])
const currentBatch = ref(1)
const availableBatches = ref([1]) // Which batches can user see/select. Defaults to [1].
const seatLoading = ref(false)
const batchesData = ref({}) // Stores data: { 1: ['1','2'], 2: ['3'] }

// Reset seating when schedule changes
watch([() => formData.value.date, () => formData.value.time, () => formData.value.routeId], () => {
    occupiedSeats.value = []
    selectedSeats.value = []
    currentBatch.value = 1
    availableBatches.value = [1]
    batchesData.value = {}
    if(formData.value.routeId && formData.value.date && formData.value.time) {
        fetchOccupiedSeats()
    }
})

// Update displayed occupied seats when changing batch view
const updateSeatMap = () => {
    occupiedSeats.value = batchesData.value[currentBatch.value] || []
}

watch(currentBatch, updateSeatMap)

const fetchOccupiedSeats = async () => {
    if(!formData.value.routeId || !formData.value.date || !formData.value.time) return
    seatLoading.value = true
    try {
        const url = `api/?action=get_daily_booked_seats&date=${formData.value.date}`
        const res = await axios.get(url)
        if (res.data.status === 'success') {
            const allDbBookings = res.data.data;
            
            // Filter specific to selected route and time
            const slotBookings = allDbBookings.filter(b => 
                (b.routeId === formData.value.routeId || b.physicalRouteId === formData.value.routeId) 
                && b.time === formData.value.time
            );
            
            const BAT_CAPACITY = 8;
            const batchesMap = new Map(); // batchNum -> [{booking}]
            const unassignedBookings = [];
            
            // Separate fixed batch assignments vs unassigned (batch 1)
            slotBookings.forEach(b => {
                const explicitBatch = parseInt(b.batchNumber) || 1;
                if (explicitBatch > 1) {
                    if (!batchesMap.has(explicitBatch)) batchesMap.set(explicitBatch, []);
                    batchesMap.get(explicitBatch).push(b);
                } else {
                    unassignedBookings.push(b);
                }
            });
            
            if (!batchesMap.has(1)) batchesMap.set(1, []);
            
            // Distribute unassignedBookings
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
                            totalPaxInBatch += parseInt(existing.seatCount) || 1;
                        }
                    });
                    
                    if (totalPaxInBatch + seatsCount <= BAT_CAPACITY || totalPaxInBatch === 0) {
                        batchesMap.set(checkBatch, [...currentBatchList, b]);
                        placed = true;
                    } else {
                        checkBatch++;
                        if (!batchesMap.has(checkBatch)) {
                            batchesMap.set(checkBatch, []);
                        }
                    }
                }
            });

            // Convert batchesMap into seat arrays formats
            const seatsAndBatches = {};
            let maxBatchProcessed = 1;
            
            batchesMap.forEach((batchArr, batchNum) => {
                seatsAndBatches[batchNum] = [];
                if(batchNum > maxBatchProcessed) maxBatchProcessed = batchNum;
                
                batchArr.forEach(bk => {
                    if (bk.seatNumbers) {
                        bk.seatNumbers.split(',').forEach(s => {
                            let seatNum = s.trim();
                            if (seatNum && seatNum.toLowerCase() !== 'pending' && seatNum.toLowerCase() !== 'menunggu') {
                                if(!seatsAndBatches[batchNum].includes(seatNum)) {
                                    seatsAndBatches[batchNum].push(seatNum);
                                }
                            }
                        });
                    }
                });
            });

            batchesData.value = seatsAndBatches
            
            // Logic: Minimal 5 orang di armada terakhir baru buka armada baru
            let allowedBatches = [];
            for (let i = 1; i <= Math.max(1, maxBatchProcessed); i++) {
                allowedBatches.push(i);
                const paxCount = seatsAndBatches[i] ? seatsAndBatches[i].length : 0;
                // Jika armada ini ada kurang dari 5 orang, DAN kita sedang mengecek armada terakhir yang ada datanya, 
                // maka jangan buka armada kosong berikutnya TAPI tetap pertahankan armada yang eksplisit diminta admin (maxBatchProcessed)
                if (paxCount < 5 && i >= maxBatchProcessed) {
                     break; 
                }
            }
            
            // Jika batch terakhir penuh (8) atau minimal 5, kita selalu sediakan 1 armada kosong extra
            const lastAllowed = allowedBatches[allowedBatches.length - 1];
            if (seatsAndBatches[lastAllowed] && seatsAndBatches[lastAllowed].length >= 5) {
                // If the last armada has >= 5 pax, we can open the next one
                allowedBatches.push(lastAllowed + 1);
            }
            
            availableBatches.value = allowedBatches;
            
            updateSeatMap()
        }
    } catch (e) {
        console.error("Failed to load seats", e)
    } finally {
        seatLoading.value = false
    }
}

// Seat Interaction Logic
const toggleSeat = (seatId) => {
    if(occupiedSeats.value.includes(seatId)) return // Disallow interacting with booked seats
    
    // Max 8 passengers per batch
    if(selectedSeats.value.length >= 8 && !selectedSeats.value.includes(seatId)) {
        Swal.fire({
            icon: 'warning',
            title: 'Batas Maksimal',
            text: 'Anda hanya dapat memesan maksimal 8 kursi dalam satu armada.',
            confirmButtonColor: '#2563eb'
        })
        return
    }

    if(selectedSeats.value.includes(seatId)) {
        selectedSeats.value = selectedSeats.value.filter(s => s !== seatId)
    } else {
        selectedSeats.value.push(seatId)
    }
}

// Seat Visual Helper
const getSeatClass = (seatId) => {
    if(occupiedSeats.value.includes(seatId)) return 'bg-slate-900 border-slate-800 text-white cursor-not-allowed shadow-sm'
    if(selectedSeats.value.includes(seatId)) return 'bg-blue-600 text-white border-blue-700 shadow-md ring-2 ring-blue-300 ring-offset-1 transform scale-105 transition-all'
    return 'bg-white text-slate-700 border-slate-300 hover:border-blue-400 hover:bg-blue-50 cursor-pointer shadow-sm transition-all'
}

// Bank Account Selection
const allBankAccounts = ref([])

const bankAccounts = computed(() => {
    if (!selectedRoute.value) return []
    const routeId = selectedRoute.value.id
    
    // Filter accounts for this route
    const routeAccounts = allBankAccounts.value.filter(a => a.route_id === routeId || a.route_id === 'ALL')
    
    return routeAccounts
        .sort((a, b) => (a.sort_order || 0) - (b.sort_order || 0))
        .map(b => ({
            value: b.bank_name,
            label: `${b.bank_name} (${b.account_number})`
        }))
})

// Group Routes (by origin city)
const groupedRoutes = computed(() => {
    const groups = {}
    routes.value.forEach(route => {
        const origin = route.origin || '';
        const dest = route.destination || '';
        // Skip routes that contain 'via sitinjau' (case-insensitive)
        if (origin.toLowerCase().includes('via sitinjau') || dest.toLowerCase().includes('via sitinjau')) {
            return
        }

        // Create base key, normalizing 'via' texts to only get the base city
        let o = origin.toLowerCase().replace(/ via .*/, '').replace(/ \(.*\)/, '').trim()
        
        // Capitalize words
        o = o.split(' ').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ')
        
        const key = `${o}`
        if(!groups[key]) groups[key] = []
        groups[key].push(route)
    })
    
    // Convert object to sorted array
    return Object.keys(groups).sort().map(key => ({
        label: `Keberangkatan: ${key}`,
        routes: groups[key]
    }))
})

// Auto-select bank account if only one is available
watch(bankAccounts, (newVal) => {
    if (newVal && newVal.length === 1) {
        formData.value.destinationAccount = newVal[0].value
    } else if (newVal && newVal.length > 1 && !newVal.find(b => b.value === formData.value.destinationAccount)) {
        formData.value.destinationAccount = ''
    } else if (!newVal || newVal.length === 0) {
        formData.value.destinationAccount = ''
    }
}, { immediate: true })

const selectedBankAccount = computed(() => {
    return bankAccounts.value.find(b => b.value === formData.value.destinationAccount)
})

const copyToClipboard = (text) => {
    navigator.clipboard.writeText(text).then(() => {
        const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 2000, timerProgressBar: true })
        Toast.fire({ icon: 'success', title: 'Nomor Rekening Tersalin' })
    })
}

// Fallback Prices in case DB returns 0 (e.g. BKT-PDG)
const fallbackPrices = {
    'BKT-PDG': { umum: 120000, pelajar: 100000 },
    'BKT-PDG-2': { umum: 250000, pelajar: 250000 },
    'PDG-BKT': { umum: 120000, pelajar: 100000 },
    'PDG-BKT-2': { umum: 120000, pelajar: 100000 },
    'PDG-PYK': { umum: 150000, pelajar: 130000 },
    'PDG-PYK-2': { umum: 250000, pelajar: 250000 },
    'PYK-PDG': { umum: 150000, pelajar: 130000 },
    'PYK-PDG-2': { umum: 250000, pelajar: 250000 },
    'BKT-PKU': { umum: 220000, pelajar: 220000 },
    'PKU-BKT': { umum: 220000, pelajar: 220000 }
}

// Price Calculation
const totalPrice = computed(() => {
    if (!selectedRoute.value) return 0
    let basePrice = 0
    
    if (formData.value.passengerType === 'Mahasiswa / Pelajar') {
        const p = String(selectedRoute.value.price_pelajar || selectedRoute.value.harga_pelajar || selectedRoute.value.price_student || '').replace(/\D/g, '')
        basePrice = parseInt(p) || 0
        if (basePrice === 0 && fallbackPrices[selectedRoute.value.id]) {
            basePrice = fallbackPrices[selectedRoute.value.id].pelajar
        }
    } else {
        const p = String(selectedRoute.value.price_umum || selectedRoute.value.price || selectedRoute.value.harga || selectedRoute.value.tarif || selectedRoute.value.harga_umum || selectedRoute.value.price_normal || '').replace(/\D/g, '')
        basePrice = parseInt(p) || 0
        if (basePrice === 0 && fallbackPrices[selectedRoute.value.id]) {
            basePrice = fallbackPrices[selectedRoute.value.id].umum
        }
    }
    
    // Apply discount if student
    if(formData.value.passengerType === 'Mahasiswa / Pelajar' && basePrice === 0) {
        // Fallback if price_pelajar is 0 or empty, we calculate from price_umum
        const pUmum = String(selectedRoute.value.price_umum || selectedRoute.value.price || selectedRoute.value.harga || selectedRoute.value.tarif || selectedRoute.value.harga_umum || '').replace(/\D/g, '')
        let calculatedUmum = parseInt(pUmum) || 0
        if (calculatedUmum === 0 && fallbackPrices[selectedRoute.value.id]) {
            calculatedUmum = fallbackPrices[selectedRoute.value.id].umum
        }
        basePrice = calculatedUmum - 20000
    } else if (formData.value.passengerType === 'Mahasiswa / Pelajar' && !selectedRoute.value.price_pelajar && !selectedRoute.value.harga_pelajar) {
        // Fallback backward compatibility in case API doesn't have it
        basePrice -= 20000 
    }
    
    const total = basePrice * selectedSeats.value.length
    return isNaN(total) ? 0 : Math.max(0, total) // Prevent negative price
})

const formatRupiah = (number) => {
    const parsed = parseInt(String(number || '').replace(/\D/g, '')) || 0;
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(parsed)
}

// Lifecycle
onMounted(async () => {
    // Defer rendering of the heavy form to allow page transition to run smoothly
    setTimeout(() => {
        isReady.value = true
    }, 150) // small delay to let fade-in start

    // Fetch Routes
    try {
        const res = await axios.get('api/?action=get_routes')
        if (res.data.status === 'success') {
            routes.value = res.data.routes
        }
    } catch (e) {
        console.error("Failed to fetch routes", e)
        error.value = "Gagal memuat rute. Silakan refresh."
    }

    // Fetch Bank Accounts
    try {
        const res = await axios.get('api/?action=get_bank_accounts')
        if (res.data.status === 'success' && Array.isArray(res.data.data)) {
            allBankAccounts.value = res.data.data
        }
    } catch (e) {
        console.error("Failed to fetch bank accounts", e)
    }
})

// Dynamic Times based on Route Selection
const availableTimes = computed(() => {
    if(!selectedRoute.value || !selectedRoute.value.schedules) return []
    
    const times = []
    const schedules = selectedRoute.value.schedules;
    
    if (!Array.isArray(schedules)) return [];

    for (const s of schedules) {
        if (typeof s === 'string') {
            try {
                if (s.includes('{')) {
                    const obj = JSON.parse(s);
                    if (obj && obj.hidden !== true && obj.hidden !== 'true' && obj.hidden !== 1) {
                        times.push(obj.time || s);
                    }
                } else {
                    times.push(s);
                }
            } catch (e) {
                times.push(s);
            }
        } else if (typeof s === 'object' && s !== null) {
            if (s.hidden !== true && s.hidden !== 'true' && s.hidden !== 1) {
                times.push(s.time);
            }
        }
    }
    return times
})

// File Upload Handlers (Base64 conversion)
const handleFileUpload = (event, field) => {
    const file = event.target.files[0]
    if (!file) return

    if (!['image/jpeg', 'image/png'].includes(file.type)) {
        Swal.fire({
            icon: 'error',
            title: 'Format Salah',
            text: 'Harap unggah file gambar format JPG atau PNG saja.',
            confirmButtonColor: '#2563eb'
        })
        event.target.value = ''
        return
    }

    if (file.size > 2 * 1024 * 1024) { // 2MB restriction
        Swal.fire({
            icon: 'error',
            title: 'File Terlalu Besar',
            text: 'Ukuran maksimal file gambar adalah 2MB.',
            confirmButtonColor: '#2563eb'
        })
        event.target.value = ''
        return
    }

    const reader = new FileReader()
    reader.onload = (e) => {
        formData.value[field] = e.target.result
    }
    reader.readAsDataURL(file)
}

const removeFile = (field) => {
     formData.value[field] = ''
    if (field === 'ktmProof' && ktmInputRed.value) ktmInputRed.value.value = ''
    if (field === 'paymentProof' && proofInputRef.value) proofInputRef.value.value = ''
}


// UI Flow Management
const currentStep = ref(1)
const steps = [
    { title: 'Data Penumpang', icon: 'bi-person-fill' },
    { title: 'Pilih Perjalanan', icon: 'bi-map-fill' },
    { title: 'Pilih Kursi', icon: 'bi-grid-fill' },
    { title: 'Pembayaran', icon: 'bi-wallet-fill' },
    { title: 'Konfirmasi', icon: 'bi-check-circle-fill' }
]

const validateStep = (step) => {
    if (step === 1) {
        if (!formData.value.passengerName || !formData.value.passengerPhone) return "Nama dan No. WhatsApp wajib diisi."
        
        // Remove spaces/dashes before validation
        const cleanedPhone = formData.value.passengerPhone.replace(/[\s-]/g, '')
        // Validate valid phone formats (e.g. +62812... or 0812...) - at least 9 digits, max 15 digits
        const phoneRegex = /^(?:\+?\d{9,15})$/
        
        if (!phoneRegex.test(cleanedPhone)) {
            return "No. WhatsApp tidak valid. Pastikan hanya memasukkan angka (minimal 9 digit), boleh menggunakan awalan +."
        }
        
        if (formData.value.passengerType === 'Mahasiswa / Pelajar' && !formData.value.ktmProof) return "Bagi mahasiswa/pelajar, foto KTM wajib diunggah."
    }
    if (step === 2) {
        if (!formData.value.routeId || !formData.value.date || !formData.value.time) return "Harap lengkapi Rute, Tanggal, dan Jam."
    }
    if (step === 3) {
        if (selectedSeats.value.length === 0) return "Silakan pilih minimal 1 kursi keberangkatan."
    }
    if (step === 4) {
        if (!formData.value.pickupAddress) return "Alamat Penjemputan wajib diisi."
        if (formData.value.paymentMethod === 'Transfer' && !formData.value.destinationAccount) return "Pilih rekening tujuan."
        if (!formData.value.paymentProof) return "Unggah bukti pembayaran."
    }
    return true
}

const nextStep = () => {
    const isValid = validateStep(currentStep.value)
    if (isValid === true) {
        currentStep.value++
        window.scrollTo({ top: 0, behavior: 'smooth' })
    } else {
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: isValid,
            confirmButtonColor: '#2563eb',
            showClass: {
                popup: 'animate__animated animate__shakeX'
            }
        })
    }
}

const prevStep = () => {
    if (currentStep.value > 1) {
        currentStep.value--
        window.scrollTo({ top: 0, behavior: 'smooth' })
    }
}

// Submission
const submitBooking = async () => {
    const isStep1Valid = validateStep(1)
    const isStep2Valid = validateStep(2)
    const isStep3Valid = validateStep(3)
    const isStep4Valid = validateStep(4)
    
    if (isStep1Valid !== true || isStep2Valid !== true || isStep3Valid !== true || isStep4Valid !== true) {
        Swal.fire('Error', 'Mohon lengkapi semua data dengan benar sebelum submit.', 'error')
        return
    }

    // Prepare payload
    const payload = {
        ...formData.value,
        seatNumbers: selectedSeats.value.join(','), // CSV format
        batchNumber: currentBatch.value,
        totalPrice: totalPrice.value,
        totalPassengers: selectedSeats.value.length
    }

    // Late night warning
    const t = formData.value.time
    if(t && (t.startsWith('00:') || t.startsWith('01:') || t.startsWith('02:'))) {
        Swal.fire({
            title: 'Keberangkatan Dini Hari',
            text: 'Admin tidak standby jam 00:00 - 05:00. Verifikasi tiket mungkin tertunda hingga pagi hari. Kami sarankan pesan sore hari sebelumnya. Lanjutkan?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#2563eb',
            cancelButtonColor: '#ef4444',
            confirmButtonText: 'Ya, Lanjutkan'
        }).then((result) => {
            if (result.isConfirmed) processBooking(payload)
        })
    } else {
        processBooking(payload)
    }
}

const processBooking = async (payload) => {
    loading.value = true
    try {
        const res = await axios.post('api/?action=create_booking', { data: payload }, {
            headers: { 'Content-Type': 'application/json' }
        })
        
        if (res.data.status === 'success') {
            success.value = true

            Swal.fire({
                icon: 'success',
                title: 'Yeay, Tiket Berhasil Dipesan!',
                html: `<div class="text-sm">
                           Terima kasih telah mempercayakan perjalanan Anda bersama <b>Sutan Raya Travel</b>.<br/><br/>
                           Kursi Anda sudah aman! Silakan klik tombol di bawah untuk melihat detail tiket dan status perjalanan Anda.
                       </div>`,
                confirmButtonColor: '#2563eb',
                confirmButtonText: 'Lihat Tiket Saya <i class="bi bi-arrow-right"></i>'
            }).then(() => {
                // Auto login user into history session
                localStorage.setItem('travel_history_session', JSON.stringify({
                    phone: formData.value.passengerPhone,
                    name: formData.value.passengerName
                }))
                // Auto switch tab to history via App.vue
                emit('booking-created', {
                    phone: formData.value.passengerPhone,
                    name: formData.value.passengerName
                })
            })
        } else {
            Swal.fire({ icon: 'error', title: 'Pemesan Gagal', text: res.data.message || 'Terjadi kesalahan sistem.' })
        }
    } catch (e) {
        console.error("Booking Error:", e)
        Swal.fire({ icon: 'error', title: 'Pemesan Gagal', text: e.response?.data?.message || 'Gagal berkomunikasi dengan server.' })
    } finally {
        loading.value = false
    }
}

</script>

<template>
  <div class="booking-form-container max-w-2xl mx-auto py-6 md:py-10 px-0 md:px-4 pb-32">
    
    <!-- Step Indicator -->
    <div class="mb-8 relative z-0 mt-2 px-6">
        <div class="absolute left-6 right-6 top-1/2 -translate-y-1/2 h-[2px] bg-slate-200/50 -z-10 rounded-full"></div>
        <div class="flex items-center justify-between">
            <div v-for="(step, index) in steps" :key="index" class="flex flex-col items-center gap-2">
                <div class="w-10 h-10 md:w-12 md:h-12 rounded-[12px] md:rounded-[14px] flex items-center justify-center font-bold text-base md:text-lg transition-all duration-300 z-10"
                     :class="currentStep > index + 1 ? 'bg-[#34c759] text-white shadow-sm' : 
                             currentStep === index + 1 ? 'bg-[#0071e3] text-white shadow-lg shadow-blue-500/20 scale-110' : 
                             'bg-white text-[#8e8e93] border border-slate-200 shadow-sm'">
                    <i v-if="currentStep > index + 1" class="bi bi-check-lg text-lg"></i>
                    <i v-else :class="step.icon"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="mb-6 flex items-center gap-4 px-6">
        <button @click="$emit('go-home')" class="w-10 h-10 rounded-full bg-white shadow-sm border border-slate-100 flex items-center justify-center text-[#0071e3] hover:bg-slate-50 transition-colors active:scale-95">
            <i class="bi bi-chevron-left text-lg"></i>
        </button>
        <div>
            <h1 class="text-3xl font-bold text-[#1d1d1f] tracking-tight">{{ steps[currentStep-1].title }}</h1>
        </div>
    </div>

    <!-- Form Content Wrapper (iOS Inset Grouped Style) -->
    <!-- Skeleton Loading Screen -->
    <div v-if="!isReady" class="px-5 py-2 animate-pulse space-y-8">
        <!-- Step Indicator Skeleton -->
        <div class="flex items-center justify-between px-1">
            <div v-for="i in 5" :key="i" class="flex flex-col items-center gap-2">
                <div class="w-10 h-10 rounded-[12px] bg-slate-200"></div>
            </div>
        </div>

        <!-- Title Skeleton -->
        <div class="px-1 space-y-2">
            <div class="h-8 w-48 bg-slate-200 rounded-full"></div>
        </div>

        <!-- Card Skeleton -->
        <div class="bg-white rounded-[20px] border border-slate-100 shadow-sm p-6 space-y-6">
            <div class="h-3 w-36 bg-slate-200 rounded-full"></div>
            <div class="grid grid-cols-2 gap-3">
                <div class="h-24 bg-slate-100 rounded-[16px]"></div>
                <div class="h-24 bg-slate-100 rounded-[16px]"></div>
            </div>
            <div class="h-px bg-slate-100"></div>
            <div class="space-y-2">
                <div class="h-3 w-24 bg-slate-200 rounded-full"></div>
                <div class="h-14 bg-slate-100 rounded-[14px]"></div>
            </div>
            <div class="space-y-2">
                <div class="h-3 w-32 bg-slate-200 rounded-full"></div>
                <div class="h-14 bg-slate-100 rounded-[14px]"></div>
            </div>
        </div>

        <!-- Bottom button skeleton -->
        <div class="h-14 bg-slate-200 rounded-full mt-4"></div>
    </div><form v-else @submit.prevent class="relative min-h-[400px] animate-fade-in">
        
        <!-- STEP 1: Data Penumpang -->
        <div v-if="currentStep === 1" class="animate-fade-in space-y-8 px-5">
            
            <div class="bg-white rounded-[20px] shadow-sm border border-[rgba(0,0,0,0.03)] overflow-hidden">
                <div class="p-6 space-y-6">
                    <div>
                        <label class="text-[11px] font-semibold text-[#8e8e93] uppercase tracking-widest block mb-4">Kategori Penumpang</label>
                        <div class="grid grid-cols-2 gap-3">
                            <button type="button" @click="formData.passengerType = 'Umum'" 
                                    class="p-4 rounded-[16px] border-2 transition-all flex flex-col items-center gap-3 active:scale-95"
                                    :class="formData.passengerType === 'Umum' ? 'bg-[#0071e3]/5 border-[#0071e3] text-[#0071e3]' : 'bg-[#f5f5f7] border-transparent text-[#8e8e93]'">
                                <i class="bi bi-person text-3xl"></i>
                                <span class="font-bold text-sm">Umum</span>
                            </button>
                            
                            <button type="button" @click="formData.passengerType = 'Mahasiswa / Pelajar'" 
                                    class="relative p-4 rounded-[16px] border-2 transition-all flex flex-col items-center gap-3 active:scale-95"
                                    :class="formData.passengerType === 'Mahasiswa / Pelajar' ? 'bg-[#0071e3]/5 border-[#0071e3] text-[#0071e3]' : 'bg-[#f5f5f7] border-transparent text-[#8e8e93]'">
                                <div class="absolute right-0 top-0 bg-[#ff3b30] text-white text-[10px] font-bold px-3 py-1 pb-1.5 rounded-bl-[12px]">Hemat 20k</div>
                                <i class="bi bi-mortarboard text-3xl"></i>
                                <span class="font-bold text-sm">Pelajar</span>
                            </button>
                        </div>
                    </div>

                    <div class="space-y-4 pt-4 border-t border-[rgba(0,0,0,0.03)]">
                        <div>
                            <label class="text-[11px] font-semibold text-[#8e8e93] uppercase tracking-widest block mb-1.5 px-1">Nama Lengkap</label>
                            <input type="text" v-model="formData.passengerName" placeholder="Contoh: John Doe" 
                                   class="w-full h-[54px] px-5 bg-[#f5f5f7] border-0 rounded-[14px] text-base outline-none focus:ring-4 focus:ring-[#0071e3]/10 transition-all font-medium text-[#1d1d1f] placeholder:text-[#c7c7cc]">
                        </div>

                        <div>
                            <label class="text-[11px] font-semibold text-[#8e8e93] uppercase tracking-widest block mb-1.5 px-1">Nomor WhatsApp</label>
                            <input type="tel" v-model="formData.passengerPhone" placeholder="08xxxxxxxxxx" 
                                   class="w-full h-[54px] px-5 bg-[#f5f5f7] border-0 rounded-[14px] text-base outline-none focus:ring-4 focus:ring-[#0071e3]/10 transition-all font-medium text-[#1d1d1f] placeholder:text-[#c7c7cc]">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Conditional KTM Upload -->
            <transition name="expand">
                <div v-if="formData.passengerType === 'Mahasiswa / Pelajar'" class="bg-[#ff9500]/5 border border-[#ff9500]/20 rounded-[20px] p-6 space-y-4">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 bg-[#ff9500] text-white rounded-full flex items-center justify-center shrink-0">
                            <i class="bi bi-person-vcard-fill text-xl"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-[#1d1d1f] text-base">Verifikasi Identitas</h4>
                            <p class="text-sm text-[#8e8e93] mt-1 font-medium">Unggah foto KTM atau Kartu Pelajar untuk klaim diskon.</p>
                        </div>
                    </div>
                    
                    <div class="relative">
                        <input type="file" ref="ktmInputRef" @change="e => handleFileUpload(e, 'ktmProof')" accept="image/png,image/jpeg" class="hidden" id="ktm-upload">
                        
                        <div v-if="!formData.ktmProof" class="w-full">
                            <label for="ktm-upload" class="flex flex-col items-center justify-center w-full h-40 bg-white border-2 border-dashed border-[#ff9500]/30 rounded-[16px] cursor-pointer hover:bg-[#ff9500]/5 hover:border-[#ff9500] transition-all">
                                <i class="bi bi-camera-fill text-3xl text-[#ff9500] mb-3"></i>
                                <p class="text-sm font-bold text-[#ff9500]">Ambil Foto KTM</p>
                                <p class="text-[11px] text-[#8e8e93] mt-1">JPG/PNG (Maks. 2MB)</p>
                            </label>
                        </div>

                        <div v-else class="relative w-full rounded-[16px] overflow-hidden border border-[rgba(0,0,0,0.05)] bg-white p-3 flex items-center gap-4 shadow-sm">
                             <div class="w-16 h-16 rounded-[10px] bg-[#f5f5f7] overflow-hidden shrink-0">
                                 <img :src="formData.ktmProof" class="w-full h-full object-cover">
                             </div>
                             <div class="flex-1 min-w-0">
                                 <p class="text-sm font-bold text-[#1d1d1f] truncate">ID Terunggah</p>
                                 <p class="text-xs text-[#34c759] font-semibold flex items-center gap-1"><i class="bi bi-check-circle-fill"></i> Siap diverifikasi</p>
                             </div>
                             <button @click.prevent="removeFile('ktmProof')" class="w-10 h-10 rounded-full bg-[#ff3b30]/10 text-[#ff3b30] flex items-center justify-center hover:bg-[#ff3b30]/20 transition-colors">
                                 <i class="bi bi-trash3-fill"></i>
                             </button>
                        </div>
                    </div>
                </div>
            </transition>

        </div>

        <!-- STEP 2: Rute & Jadwal -->
        <div v-if="currentStep === 2" class="animate-fade-in space-y-6 px-5">
            
            <div class="bg-white rounded-[20px] shadow-sm border border-[rgba(0,0,0,0.03)] overflow-hidden">
                <div class="p-6 space-y-8">
                    <div>
                        <label class="text-[11px] font-semibold text-[#8e8e93] uppercase tracking-widest block mb-3 px-1">Tujuan Perjalanan</label>
                        <div class="relative">
                            <select v-model="formData.routeId" class="w-full h-[54px] pl-12 pr-10 bg-[#f5f5f7] border-0 rounded-[14px] text-base outline-none focus:ring-4 focus:ring-[#0071e3]/10 transition-all appearance-none font-medium text-[#1d1d1f] cursor-pointer">
                                <option value="" disabled>Pilih Rute Perjalanan</option>
                                <optgroup v-for="group in groupedRoutes" :key="group.label" :label="group.label">
                                    <option v-for="route in group.routes" :key="route.id" :value="route.id">
                                        {{ route.origin }} ➔ {{ route.destination }}
                                    </option>
                                </optgroup>
                            </select>
                            <i class="bi bi-map-fill absolute left-4 top-1/2 -translate-y-1/2 text-[#8e8e93] text-lg"></i>
                            <i class="bi bi-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-[#c7c7cc]"></i>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="text-[11px] font-semibold text-[#8e8e93] uppercase tracking-widest block mb-3 px-1">Tanggal</label>
                            <div class="relative">
                                <input type="date" v-model="formData.date" class="w-full h-[54px] pl-12 pr-4 bg-[#f5f5f7] border-0 rounded-[14px] text-base outline-none focus:ring-4 focus:ring-[#0071e3]/10 transition-all font-medium text-[#1d1d1f] cursor-pointer">
                                <i class="bi bi-calendar-event-fill absolute left-4 top-1/2 -translate-y-1/2 text-[#8e8e93] text-lg"></i>
                            </div>
                        </div>

                        <div>
                            <label class="text-[11px] font-semibold text-[#8e8e93] uppercase tracking-widest block mb-3 px-1">Waktu</label>
                            <div class="relative">
                                <select v-model="formData.time" :disabled="!formData.routeId" class="w-full h-[54px] pl-12 pr-10 bg-[#f5f5f7] border-0 rounded-[14px] text-base outline-none focus:ring-4 focus:ring-[#0071e3]/10 transition-all appearance-none disabled:opacity-40 disabled:cursor-not-allowed font-medium text-[#1d1d1f] cursor-pointer">
                                    <option value="" disabled>Pilih Jam</option>
                                    <option v-for="t in availableTimes" :key="t" :value="t">{{ t }}</option>
                                </select>
                                <i class="bi bi-clock-fill absolute left-4 top-1/2 -translate-y-1/2 text-[#8e8e93] text-lg"></i>
                                <i class="bi bi-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-[#c7c7cc]"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="formData.routeId" class="bg-[#0071e3]/5 border border-[#0071e3]/10 rounded-[20px] p-6 flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-[#0071e3] uppercase tracking-wider mb-1">Estimasi Perjalanan</p>
                    <p class="text-[#1d1d1f] font-bold text-lg leading-tight">Sekitar 2-3 Jam</p>
                </div>
                <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center shadow-sm">
                    <i class="bi bi-lightning-charge-fill text-[#ff9500] text-xl"></i>
                </div>
            </div>

        </div>

        <!-- STEP 3: Pilih Kursi -->
        <div v-if="currentStep === 3" class="animate-fade-in space-y-8 px-5">
            
            <div class="bg-white rounded-[20px] shadow-sm border border-[rgba(0,0,0,0.03)] overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-8">
                        <div>
                            <h3 class="text-xl font-bold text-[#1d1d1f] tracking-tight">Denah Armada</h3>
                            <p class="text-sm text-[#8e8e93] font-medium mt-1">Pilih kursi yang Anda inginkan.</p>
                        </div>
                        <div class="bg-[#f5f5f7] px-3 py-1.5 rounded-full text-[11px] font-bold text-[#8e8e93] uppercase tracking-wider">
                            Armada {{ currentBatch }} / {{ availableBatches.length }}
                        </div>
                    </div>
                    
                    <div v-if="seatLoading" class="flex flex-col items-center justify-center py-16">
                         <div class="w-12 h-12 border-[3px] border-[#0071e3]/10 border-t-[#0071e3] rounded-full animate-spin mb-4"></div>
                         <p class="text-[#8e8e93] text-sm font-bold animate-pulse">Menghubungkan ke armada...</p>
                    </div>

                    <div v-else class="flex flex-col items-center">
                         
                         <!-- Batch Switching UI -->
                         <div v-if="availableBatches.length > 1" class="mb-10 flex gap-2">
                             <button type="button" v-for="b in availableBatches" :key="b" @click="currentBatch = b"
                                     class="w-12 h-12 rounded-[14px] text-sm font-bold transition-all flex items-center justify-center border-2 active:scale-90"
                                     :class="currentBatch === b ? 'bg-[#0071e3] text-white border-[#0071e3] shadow-md shadow-blue-500/20' : 'bg-white text-[#8e8e93] border-[#f5f5f7]'">
                                {{ b }}
                             </button>
                         </div>

                         <!-- iOS Style Seat Map -->
                         <div class="bg-[#f5f5f7] p-8 rounded-[40px] w-fit mx-auto relative border border-[rgba(0,0,0,0.03)] shadow-inner">
                             <!-- Windshield -->
                             <div class="absolute top-4 left-1/2 -translate-x-1/2 w-[60%] h-1 bg-white/50 rounded-full blur-[0.5px]"></div>
                             
                             <div class="flex flex-col gap-6 pt-4 w-[200px]">
                                 <!-- Row 1 -->
                                 <div class="flex justify-between w-full">
                                     <button type="button" @click="toggleSeat('CC')" class="w-14 h-14 shrink-0 rounded-[16px] flex items-center justify-center text-[15px] font-bold transition-all transform active:scale-90 shadow-sm border" :class="getSeatClass('CC')">CC</button>
                                     <div class="w-14 h-14 shrink-0 rounded-[16px] bg-[#e5e5ea] text-[#8e8e93] flex items-center justify-center text-[11px] font-black tracking-tighter shadow-inner opacity-60">SPR</div>
                                 </div>

                                 <!-- Row 2 -->
                                 <div class="flex justify-between w-full">
                                     <button type="button" @click="toggleSeat('1')" class="w-14 h-14 shrink-0 rounded-[16px] flex items-center justify-center text-[15px] font-bold transition-all transform active:scale-90 shadow-sm border" :class="getSeatClass('1')">1</button>
                                     <button type="button" @click="toggleSeat('2')" class="w-14 h-14 shrink-0 rounded-[16px] flex items-center justify-center text-[15px] font-bold transition-all transform active:scale-90 shadow-sm border" :class="getSeatClass('2')">2</button>
                                 </div>

                                 <!-- Row 3 -->
                                 <div class="flex justify-between w-full">
                                     <button type="button" @click="toggleSeat('3')" class="w-14 h-14 shrink-0 rounded-[16px] flex items-center justify-center text-[15px] font-bold transition-all transform active:scale-90 shadow-sm border" :class="getSeatClass('3')">3</button>
                                     <button type="button" @click="toggleSeat('4')" class="w-14 h-14 shrink-0 rounded-[16px] flex items-center justify-center text-[15px] font-bold transition-all transform active:scale-90 shadow-sm border" :class="getSeatClass('4')">4</button>
                                 </div>

                                 <!-- Row 4 (Back Bench) -->
                                 <div class="flex justify-between w-full">
                                     <button type="button" @click="toggleSeat('5')" class="w-14 h-14 shrink-0 rounded-[16px] flex items-center justify-center text-[15px] font-bold transition-all transform active:scale-90 shadow-sm border" :class="getSeatClass('5')">5</button>
                                     <button type="button" @click="toggleSeat('6')" class="w-14 h-14 shrink-0 rounded-[16px] flex items-center justify-center text-[15px] font-bold transition-all transform active:scale-90 shadow-sm border" :class="getSeatClass('6')">6</button>
                                     <button type="button" @click="toggleSeat('7')" class="w-14 h-14 shrink-0 rounded-[16px] flex items-center justify-center text-[15px] font-bold transition-all transform active:scale-90 shadow-sm border" :class="getSeatClass('7')">7</button>
                                 </div>
                             </div>
                         </div>

                         <!-- Legend -->
                         <div class="mt-10 flex flex-wrap justify-center gap-6 text-[12px] font-bold text-[#8e8e93] py-4 border-t border-[rgba(0,0,0,0.03)] w-full">
                             <span class="flex items-center gap-2.5"><div class="w-5 h-5 rounded-[6px] bg-white border shadow-sm"></div> Tersedia</span>
                             <span class="flex items-center gap-2.5"><div class="w-5 h-5 rounded-[6px] bg-[#0071e3] border-[#0071e3] shadow-md shadow-blue-300/30"></div> Dipilih</span>
                             <span class="flex items-center gap-2.5"><div class="w-5 h-5 rounded-[6px] bg-[#1d1d1f] border-[#1d1d1f] shadow-sm"></div> Terisi</span>
                         </div>
                    </div>
                </div>
            </div>

            <!-- Price Summary Box -->
            <div class="bg-white rounded-[20px] shadow-sm border border-[rgba(0,0,0,0.03)] p-6">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-[11px] font-bold text-[#8e8e93] uppercase tracking-widest">SUBTOTAL PEMBAYARAN</p>
                    <p class="text-[12px] font-bold text-[#0071e3]">{{ selectedSeats.length }} Kursi</p>
                </div>
                <div class="flex items-end justify-between">
                    <h4 class="text-3xl font-black text-[#1d1d1f] tracking-tight leading-none">{{ formatRupiah(totalPrice) }}</h4>
                    <p class="text-[11px] text-[#8e8e93] font-medium">Lanjut untuk konfirmasi jemputan</p>
                </div>
            </div>

        </div>

        <!-- STEP 4: Alamat & Bayar -->
        <div v-if="currentStep === 4" class="animate-fade-in space-y-8 px-5">
            
            <div class="bg-white rounded-[24px] shadow-sm border border-[rgba(0,0,0,0.03)] overflow-hidden">
                <div class="p-6 space-y-6">
                    <div>
                        <h4 class="text-[11px] font-semibold text-[#8e8e93] uppercase tracking-widest mb-4 flex items-center gap-2">
                            <i class="bi bi-geo-alt-fill text-[#0071e3]"></i> Penjemputan & Pengantaran
                        </h4>
                        
                        <div class="space-y-6">
                            <div class="space-y-2">
                                <label class="text-[13px] font-bold text-[#1d1d1f] block px-1">Alamat Penjemputan</label>
                                <textarea v-model="formData.pickupAddress" rows="2" placeholder="Detail jalan, patokan, dll..." 
                                          class="w-full p-4 bg-[#f5f5f7] border-0 rounded-[14px] text-[15px] outline-none focus:ring-4 focus:ring-[#0071e3]/10 transition-all font-medium text-[#1d1d1f] placeholder:text-[#c7c7cc] resize-none"></textarea>
                                <input type="url" v-model="formData.pickupMapLink" placeholder="Link Google Maps (Opsional)" 
                                       class="w-full h-12 px-4 bg-[#f5f5f7] border-0 rounded-[12px] text-xs outline-none focus:ring-4 focus:ring-[#0071e3]/10 transition-all font-mono text-[#0071e3] placeholder:text-[#c7c7cc] placeholder:font-sans">
                            </div>

                            <div class="pt-6 border-t border-[rgba(0,0,0,0.03)] space-y-2">
                                <label class="text-[13px] font-bold text-[#1d1d1f] block px-1">Alamat Pengantaran <span class="text-[#8e8e93] font-normal">(Opsional)</span></label>
                                <textarea v-model="formData.dropoffAddress" rows="2" placeholder="Kosongkan jika turun di pool..." 
                                          class="w-full p-4 bg-[#f5f5f7] border-0 rounded-[14px] text-[15px] outline-none focus:ring-4 focus:ring-[#0071e3]/10 transition-all font-medium text-[#1d1d1f] placeholder:text-[#c7c7cc] resize-none"></textarea>
                                <input type="url" v-model="formData.dropoffMapLink" placeholder="Link Google Maps (Opsional)" 
                                       class="w-full h-12 px-4 bg-[#f5f5f7] border-0 rounded-[12px] text-xs outline-none focus:ring-4 focus:ring-[#0071e3]/10 transition-all font-mono text-[#0071e3] placeholder:text-[#c7c7cc] placeholder:font-sans">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Group -->
            <div class="bg-white rounded-[24px] shadow-sm border border-[rgba(0,0,0,0.03)] overflow-hidden">
                <div class="p-6 space-y-6">
                    <div>
                        <h4 class="text-[11px] font-semibold text-[#8e8e93] uppercase tracking-widest mb-4 flex items-center gap-2">
                            <i class="bi bi-credit-card-fill text-[#5856d6]"></i> Metode Pembayaran
                        </h4>
                        
                        <div class="bg-[#f5f5f7] p-1.5 rounded-[16px] flex gap-1.5 mb-6">
                            <button type="button" @click="formData.paymentMethod = 'QRIS'" 
                                    class="flex-1 py-3.5 rounded-[12px] text-[13px] font-bold transition-all active:scale-95" 
                                    :class="formData.paymentMethod === 'QRIS' ? 'bg-white text-[#1d1d1f] shadow-sm' : 'text-[#8e8e93]'">QRIS</button>
                            <button type="button" @click="formData.paymentMethod = 'Transfer'" 
                                    class="flex-1 py-3.5 rounded-[12px] text-[13px] font-bold transition-all active:scale-95" 
                                    :class="formData.paymentMethod === 'Transfer' ? 'bg-white text-[#1d1d1f] shadow-sm' : 'text-[#8e8e93]'">Transfer</button>
                        </div>

                        <transition name="fade" mode="out-in">
                            <div v-if="formData.paymentMethod === 'QRIS'" class="bg-white flex flex-col items-center">
                                <div class="bg-white p-6 rounded-[24px] shadow-inner border border-slate-100 flex flex-col items-center w-full">
                                    <img src="/QR.jpeg" alt="QRIS" class="w-full max-w-[220px] h-auto object-contain rounded-[16px] border border-slate-50 mb-4" />
                                    <a href="/QR.jpeg" download class="text-[13px] font-bold text-[#0071e3] flex items-center gap-2 hover:opacity-70 transition-opacity">
                                        <i class="bi bi-arrow-down-circle-fill"></i> Simpan Gambar QRIS
                                    </a>
                                </div>
                            </div>

                            <div v-else-if="formData.paymentMethod === 'Transfer'" class="space-y-4">
                                <div class="relative">
                                    <select v-model="formData.destinationAccount" class="w-full h-[54px] px-5 bg-[#f5f5f7] border-0 rounded-[14px] text-base outline-none font-medium text-[#1d1d1f] appearance-none cursor-pointer">
                                        <option value="" disabled>Pilih Bank Tujuan</option>
                                        <option v-for="b in bankAccounts" :key="b.value" :value="b.value">{{ b.label }}</option>
                                    </select>
                                    <i class="bi bi-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-[#c7c7cc]"></i>
                                </div>
                                
                                <div v-if="selectedBankAccount" class="bg-[#f5f5f7] rounded-[16px] p-5 flex justify-between items-center border border-[rgba(0,0,0,0.03)]">
                                    <div>
                                        <p class="text-[11px] text-[#8e8e93] font-bold uppercase tracking-wider mb-1">Nomor Rekening</p>
                                        <p class="font-bold text-xl text-[#1d1d1f] tracking-tight">{{ selectedBankAccount.label.match(/\d+/) ? selectedBankAccount.label.match(/\d+/)[0] : '' }}</p>
                                    </div>
                                    <button @click.prevent="copyToClipboard(selectedBankAccount.label.match(/\d+/)[0])" 
                                            class="w-12 h-12 bg-white rounded-[14px] flex items-center justify-center text-[#0071e3] shadow-sm border border-slate-100 active:scale-90 transition-transform">
                                        <i class="bi bi-copy text-lg"></i>
                                    </button>
                                </div>
                            </div>
                        </transition>
                    </div>

                    <div class="pt-8 border-t border-[rgba(0,0,0,0.03)]">
                        <h4 class="text-[11px] font-semibold text-[#8e8e93] uppercase tracking-widest mb-4 flex items-center gap-2">
                            <i class="bi bi-cloud-arrow-up-fill text-[#34c759]"></i> Bukti Pembayaran
                        </h4>
                        
                        <div class="relative">
                            <input type="file" ref="proofInputRef" @change="e => handleFileUpload(e, 'paymentProof')" accept="image/png,image/jpeg" class="hidden" id="proof-upload">
                            
                            <div v-if="!formData.paymentProof" class="w-full">
                                <label for="proof-upload" class="flex items-center gap-5 p-5 bg-[#f5f5f7] rounded-[18px] cursor-pointer hover:bg-[#e5e5ea] transition-all border-1 border-transparent active:scale-[0.98]">
                                    <div class="w-14 h-14 bg-white rounded-[14px] flex items-center justify-center text-[#5856d6] shadow-sm shrink-0">
                                        <i class="bi bi-camera-fill text-2xl"></i>
                                    </div>
                                    <div>
                                        <p class="text-[15px] font-bold text-[#1d1d1f]">Unggah Bukti</p>
                                        <p class="text-xs text-[#8e8e93] font-medium mt-0.5">Ketuk untuk mengambil foto</p>
                                    </div>
                                </label>
                            </div>

                            <div v-else class="relative w-full rounded-[18px] overflow-hidden border border-[rgba(0,0,0,0.03)] bg-[#f5f5f7] p-3 flex items-center gap-4 shadow-inner">
                                <div class="w-16 h-16 rounded-[12px] bg-white overflow-hidden shadow-sm shrink-0">
                                    <img :src="formData.paymentProof" class="w-full h-full object-cover">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-[15px] font-bold text-[#1d1d1f] truncate">Bukti Terupload</p>
                                    <p class="text-xs text-[#34c759] font-bold flex items-center gap-1"><i class="bi bi-check-circle-fill"></i> Siap dikonfirmasi</p>
                                </div>
                                <button @click.prevent="removeFile('paymentProof')" class="w-10 h-10 rounded-full bg-[#ff3b30]/10 text-[#ff3b30] flex items-center justify-center active:scale-90">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- STEP 5: Konfirmasi Data -->
        <div v-if="currentStep === steps.length" class="animate-fade-in space-y-8 px-5 pb-10">
            
            <div class="bg-[#1d1d1f] rounded-[32px] p-8 text-white shadow-2xl relative overflow-hidden">
                <div class="relative z-10">
                    <div class="flex items-center gap-3 mb-8">
                        <div class="w-10 h-10 bg-white/10 rounded-full flex items-center justify-center">
                            <i class="bi bi-ticket-perforated text-xl"></i>
                        </div>
                        <h3 class="text-lg font-bold">Ringkasan Tiket</h3>
                    </div>

                    <div class="space-y-6">
                        <div class="flex justify-between items-start">
                            <div class="space-y-1">
                                <p class="text-[10px] text-[#a1a1a6] font-black uppercase tracking-widest">PENUMPANG</p>
                                <p class="text-lg font-bold tracking-tight">{{ formData.passengerName }}</p>
                                <p class="text-xs text-[#a1a1a6] font-medium">{{ formData.passengerPhone }}</p>
                            </div>
                            <div class="text-right space-y-1">
                                <p class="text-[10px] text-[#a1a1a6] font-black uppercase tracking-widest">TIPE</p>
                                <p class="text-sm font-bold bg-white/10 px-3 py-1 rounded-full inline-block">{{ formData.passengerType }}</p>
                            </div>
                        </div>

                        <div class="h-px bg-white/10 w-full"></div>

                        <div class="grid grid-cols-2 gap-8">
                            <div class="space-y-1">
                                <p class="text-[10px] text-[#a1a1a6] font-black uppercase tracking-widest">DARI</p>
                                <p class="text-xl font-black tracking-tighter">{{ selectedRoute ? selectedRoute.origin : '-' }}</p>
                            </div>
                            <div class="space-y-1 text-right">
                                <p class="text-[10px] text-[#a1a1a6] font-black uppercase tracking-widest">KE</p>
                                <p class="text-xl font-black tracking-tighter">{{ selectedRoute ? selectedRoute.destination : '-' }}</p>
                            </div>
                        </div>

                        <div class="flex justify-between items-end pt-4">
                            <div class="space-y-1">
                                <p class="text-[10px] text-[#a1a1a6] font-black uppercase tracking-widest">JADWAL</p>
                                <p class="text-lg font-bold">{{ formData.date }}</p>
                                <p class="text-lg font-black text-[#0071e3]">{{ formData.time }}</p>
                            </div>
                            <div class="text-right space-y-1">
                                <p class="text-[10px] text-[#a1a1a6] font-black uppercase tracking-widest">KURSI</p>
                                <p class="text-3xl font-black text-white leading-none">#{{ selectedSeats.join(', ') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Abstract shape -->
                <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-[#0071e3] opacity-20 blur-[60px] rounded-full"></div>
            </div>

            <!-- Detailed Inset List for other info -->
            <div class="bg-white rounded-[24px] shadow-sm border border-[rgba(0,0,0,0.03)] overflow-hidden">
                <div class="p-6 space-y-6">
                    <div class="flex justify-between items-center group">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-[#f5f5f7] rounded-[12px] flex items-center justify-center text-[#1d1d1f]">
                                <i class="bi bi-geo-alt-fill"></i>
                            </div>
                            <div>
                                <p class="text-[11px] font-bold text-[#8e8e93] uppercase tracking-wider">Penjemputan</p>
                                <p class="text-[15px] font-bold text-[#1d1d1f] mt-0.5 line-clamp-1">{{ formData.pickupAddress }}</p>
                            </div>
                        </div>
                        <i class="bi bi-chevron-right text-[#c7c7cc]"></i>
                    </div>

                    <div class="h-px bg-[#f5f5f7] w-full ml-13"></div>

                    <div class="flex justify-between items-center group">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-[#f5f5f7] rounded-[12px] flex items-center justify-center text-[#1d1d1f]">
                                <i class="bi bi-wallet2"></i>
                            </div>
                            <div>
                                <p class="text-[11px] font-bold text-[#8e8e93] uppercase tracking-wider">Metode Pembayaran</p>
                                <p class="text-[15px] font-bold text-[#1d1d1f] mt-0.5 uppercase">{{ formData.paymentMethod }}</p>
                            </div>
                        </div>
                        <i class="bi bi-chevron-right text-[#c7c7cc]"></i>
                    </div>
                </div>
            </div>

            <!-- Final Price Fixed Bar at bottom will handle the real button, but we put a summary here too -->
            <div class="bg-[#34c759] rounded-[24px] p-6 text-white text-center shadow-lg active:scale-95 transition-transform" v-if="!loading">
                <p class="text-[11px] font-bold uppercase tracking-widest opacity-80 mb-1">Total yang Dibayarkan</p>
                <p class="text-3xl font-black tracking-tighter">{{ formatRupiah(totalPrice) }}</p>
            </div>
            
        </div>

    </form>

    <!-- Navigation Buttons - Floating Style for Mobile-First experience -->
    <div class="fixed bottom-0 left-0 right-0 bg-white/80 backdrop-blur-2xl border-t border-[rgba(0,0,0,0.05)] p-4 md:p-6 z-50 flex items-center justify-center pb-safe-area">
        <div class="max-w-2xl w-full flex gap-3">
            <button v-if="currentStep > 1" type="button" @click="prevStep()" 
                    class="flex-1 h-[58px] bg-[#f5f5f7] text-[#1d1d1f] font-bold text-[16px] rounded-[18px] transition-all transform active:scale-95">
                Kembali
            </button>
            <button v-else type="button" @click="$emit('go-home')" 
                    class="flex-1 h-[58px] bg-[#f5f5f7] text-[#1d1d1f] font-bold text-[16px] rounded-[18px] transition-all transform active:scale-95">
                Batal
            </button>
            
            <template v-if="currentStep < steps.length">
                <button type="button" @click="nextStep"
                        class="flex-[2] h-[58px] bg-[#0071e3] text-white font-bold text-[16px] rounded-[18px] shadow-lg shadow-blue-500/20 transition-all transform active:scale-95">
                    Lanjutkan
                </button>
            </template>
            
            <template v-else>
                <button type="button" @click="submitBooking" :disabled="loading"
                        class="flex-[2] h-[58px] bg-[#34c759] text-white font-bold text-[16px] rounded-[18px] shadow-lg shadow-green-500/20 transition-all transform active:scale-95 flex items-center justify-center gap-2">
                    <span v-if="loading" class="w-6 h-6 border-3 border-white/30 border-t-white rounded-full animate-spin"></span>
                    <span v-else>Konfirmasi & Bayar</span>
                </button>
            </template>
        </div>
    </div>
<br><br><br>
  </div>
</template>

<style scoped>
.animate-fade-in {
    animation: fadeIn 0.3s ease-out forwards;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s;
}
.fade-enter, .fade-leave-to {
  opacity: 0;
}

.expand-enter-active, .expand-leave-active {
  transition: all 0.3s ease-in-out;
  overflow: hidden;
}
.expand-enter-from, .expand-leave-to {
  height: 0;
  opacity: 0;
  padding-top: 0;
  padding-bottom: 0;
  margin-top: 0;
  margin-bottom: 0;
}
</style>
