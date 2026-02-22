<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import axios from 'axios'
import Swal from 'sweetalert2'

const emit = defineEmits(['booking-created'])

// Form State
const loading = ref(false)
const error = ref(null)
const success = ref(false)

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
  paymentMethod: 'Transfer',
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
        console.log("Response Get Seats:", res.data)
        
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
            availableBatches.value = Array.from({length: maxBatchProcessed}, (_, i) => i + 1)
            
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
const bankAccounts = [
    { value: 'BCA PADANG', label: 'BCA PADANG (7425888781)' },
    { value: 'BCA BUKITTINGGI', label: 'BCA BUKITTINGGI (7425888722)' },
    { value: 'BCA PAYAKUMBUH', label: 'BCA PAYAKUMBUH (7425888943)' }
]

const selectedBankAccount = computed(() => {
    return bankAccounts.find(b => b.value === formData.value.destinationAccount)
})

const copyToClipboard = (text) => {
    navigator.clipboard.writeText(text).then(() => {
        const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 2000, timerProgressBar: true })
        Toast.fire({ icon: 'success', title: 'Nomor Rekening Tersalin' })
    })
}

// Price Calculation
const totalPrice = computed(() => {
    if (!selectedRoute.value) return 0
    let basePrice = 0
    
    if (formData.value.passengerType === 'Mahasiswa / Pelajar') {
        basePrice = parseInt(selectedRoute.value.price_pelajar) || 0
    } else {
        basePrice = parseInt(selectedRoute.value.price_umum) || 0
    }
    
    // Apply discount if student
    if(formData.value.passengerType === 'Mahasiswa / Pelajar' && !selectedRoute.value.price_pelajar) {
        // Fallback backward compatibility in case API doesn't have it
        basePrice -= 20000 
    }
    
    const total = basePrice * selectedSeats.value.length
    return isNaN(total) ? 0 : total
})

const formatRupiah = (number) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number)
}

// Lifecycle
onMounted(async () => {
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
})

// Dynamic Times based on Route Selection
const availableTimes = computed(() => {
    if(!selectedRoute.value || !selectedRoute.value.schedules) return []
    // Schedules are stored as JSON string array in DB, parsed by PHP API
    return selectedRoute.value.schedules
})

// File Upload Handlers (Base64 conversion)
const handleFileUpload = (event, field) => {
    const file = event.target.files[0]
    if (!file) return

    if (!file.type.match('image.*')) {
        Swal.fire({
            icon: 'error',
            title: 'Format Salah',
            text: 'Harap unggah file gambar (JPG/PNG).',
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
    { title: 'Jadwal & Kursi', icon: 'bi-map-fill' },
    { title: 'Pembayaran', icon: 'bi-wallet-fill' },
    { title: 'Konfirmasi', icon: 'bi-check-circle-fill' }
]

const validateStep = (step) => {
    // Shake animation class flag can be added here if needed
    if (step === 1) {
        if (!formData.value.passengerName || !formData.value.passengerPhone) return "Nama dan No. WhatsApp wajib diisi."
        if (formData.value.passengerType === 'Mahasiswa / Pelajar' && !formData.value.ktmProof) return "Bagi mahasiswa/pelajar, foto KTM wajib diunggah."
    }
    if (step === 2) {
        if (!formData.value.routeId || !formData.value.date || !formData.value.time) return "Harap lengkapi Rute, Tanggal, dan Jam."
        if (selectedSeats.value.length === 0) return "Silakan pilih minimal 1 kursi keberangkatan."
    }
    if (step === 3) {
        if (!formData.value.pickupAddress) return "Alamat Penjemputan wajib diisi."
        if (!formData.value.destinationAccount || !formData.value.paymentProof) return "Pilih rekening tujuan dan unggah bukti transfer."
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
    // Final Validation Check
    const isStep1Valid = validateStep(1)
    const isStep2Valid = validateStep(2)
    const isStep3Valid = validateStep(3)
    
    if (isStep1Valid !== true || isStep2Valid !== true || isStep3Valid !== true) {
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
                title: 'Pesanan Berhasil!',
                html: `Terima kasih! Bukti pemesanan telah diterbitkan. <br/><br/>Simpan ID Booking: <b>${res.data.booking_id || ''}</b>`,
                confirmButtonColor: '#2563eb',
                confirmButtonText: 'Lihat Tiket Saya'
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
  <div class="booking-form-container max-w-2xl mx-auto py-6 md:py-10 px-4 pb-32">
    
    <!-- Step Indicator -->
    <div class="mb-8 relative z-0">
        <div class="absolute left-4 right-4 top-1/2 -translate-y-1/2 h-[2px] bg-slate-200 -z-10 rounded-full"></div>
        <div class="flex items-center justify-between">
            <div v-for="(step, index) in steps" :key="index" class="flex flex-col items-center gap-2">
                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm transition-all duration-300 z-10"
                     :class="currentStep > index + 1 ? 'bg-green-500 text-white border-2 border-white shadow-sm' : 
                             currentStep === index + 1 ? 'bg-blue-600 text-white border-2 border-white shadow-md scale-110' : 
                             'bg-white text-slate-400 border border-slate-200'">
                    <i v-if="currentStep > index + 1" class="bi bi-check-lg"></i>
                    <i v-else :class="step.icon"></i>
                </div>
                <!-- Labels hidden on very small screens -->
                <span class="text-[10px] uppercase font-bold tracking-widest hidden md:block"
                      :class="currentStep === index + 1 ? 'text-blue-600' : 'text-slate-400'">
                    {{ step.title }}
                </span>
            </div>
        </div>
    </div>
    
    <!-- Header Area -->
    <div class="mb-8 flex items-center gap-3">
        <button v-if="currentStep > 1" @click="prevStep" class="w-10 h-10 rounded-full bg-white border border-slate-200 flex flex-shrink-0 items-center justify-center text-slate-500 hover:text-blue-600 hover:border-blue-500 hover:bg-blue-50 transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500/50">
            <i class="bi bi-arrow-left text-lg"></i>
        </button>
        <div>
            <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">{{ steps[currentStep-1].title }}</h2>
            <p class="text-slate-500 text-sm">Masukan informasi yang valid.</p>
        </div>
    </div>

    <!-- Form Content Wrapper -->
    <form @submit.prevent class="bg-white rounded-[1.5rem] shadow-sm border border-slate-200 p-6 md:p-8 relative min-h-[400px]">
        
        <!-- STEP 1: Penumpang -->
        <div v-if="currentStep === 1" class="animate-fade-in space-y-6">
            
            <div class="space-y-3">
                <label class="text-xs font-bold text-slate-500 uppercase tracking-widest block mb-2 px-1">Tipe Penumpang</label>
                <div class="grid grid-cols-2 gap-3">
                    <button type="button" @click="formData.passengerType = 'Umum'" class="relative p-4 rounded-xl border-1.5 transition-all text-left group"
                            :class="formData.passengerType === 'Umum' ? 'bg-blue-50 border-blue-500 shadow-sm' : 'bg-white border-slate-200 hover:border-blue-300'">
                        <div class="flex items-center justify-between mb-1">
                            <span class="font-bold text-slate-800" :class="formData.passengerType === 'Umum' ? 'text-blue-700' : ''">Umum</span>
                            <i class="bi bi-check-circle-fill text-blue-500 text-lg" :class="formData.passengerType === 'Umum' ? 'opacity-100 scale-100' : 'opacity-0 scale-75'"></i>
                        </div>
                        <p class="text-[11px] text-slate-500">Tarif penumpang normal</p>
                    </button>
                    
                    <button type="button" @click="formData.passengerType = 'Mahasiswa / Pelajar'" class="relative p-4 rounded-xl border-1.5 transition-all text-left flex flex-col justify-between group overflow-hidden"
                            :class="formData.passengerType === 'Mahasiswa / Pelajar' ? 'bg-blue-50 border-blue-500 shadow-sm' : 'bg-white border-slate-200 hover:border-blue-300'">
                        <div class="flex items-center justify-between mb-1">
                            <span class="font-bold text-slate-800 flex items-center gap-1.5" :class="formData.passengerType === 'Mahasiswa / Pelajar' ? 'text-blue-700' : ''">Mahasiswa</span>
                             <i class="bi bi-check-circle-fill text-blue-500 text-lg" :class="formData.passengerType === 'Mahasiswa / Pelajar' ? 'opacity-100 scale-100' : 'opacity-0 scale-75'"></i>
                        </div>
                        <div class="py-0.5 px-2 bg-red-500 text-white text-[9px] font-bold rounded uppercase tracking-wider self-start inline-block">Hemat 20rb</div>
                    </button>
                </div>
            </div>

            <div class="space-y-5">
                <div>
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-widest block mb-1.5 px-1">Nama Lengkap</label>
                    <div class="relative">
                        <input type="text" v-model="formData.passengerName" placeholder="Cth: Nadim" class="w-full pl-11 p-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 transition-all text-slate-800 placeholder:text-slate-400">
                        <i class="bi bi-person absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    </div>
                </div>

                <div>
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-widest block mb-1.5 px-1">No. WhatsApp</label>
                    <div class="relative">
                        <input type="tel" v-model="formData.passengerPhone" placeholder="Cth: 081234..." class="w-full pl-11 p-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 transition-all text-slate-800 placeholder:text-slate-400">
                        <i class="bi bi-whatsapp absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-1 pl-1">Pastikan aktif menerima notifikasi WhatsApp.</p>
                </div>
            </div>

            <!-- Conditional KTM Upload -->
            <transition name="expand">
                <div v-if="formData.passengerType === 'Mahasiswa / Pelajar'" class="p-5 bg-orange-50 border border-orange-200 rounded-xl space-y-4">
                    <div class="flex items-start gap-3">
                        <i class="bi bi-info-circle-fill text-orange-500 mt-0.5"></i>
                        <div>
                            <h4 class="font-bold text-orange-800 text-sm">Upload Identitas (Wajib)</h4>
                            <p class="text-xs text-orange-600/80 mt-1">Sertakan foto KTM atau Kartu Pelajar untuk mendapatkan diskon perjalanan Rp 20.000.</p>
                        </div>
                    </div>
                    
                    <div class="relative">
                        <input type="file" ref="ktmInputRef" @change="e => handleFileUpload(e, 'ktmProof')" accept="image/*" class="hidden" id="ktm-upload">
                        
                        <div v-if="!formData.ktmProof" class="w-full">
                            <label for="ktm-upload" class="flex flex-col items-center justify-center w-full h-32 border-2 border-orange-300 border-dashed rounded-xl cursor-pointer bg-white hover:bg-orange-50 transition-colors">
                                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                    <i class="bi bi-cloud-arrow-up text-2xl text-orange-500 mb-2"></i>
                                    <p class="mb-1 text-sm font-semibold text-orange-700">Sentuh untuk Upload KTM</p>
                                    <p class="text-xs text-orange-500 opacity-80">JPG, PNG (Max. 2MB)</p>
                                </div>
                            </label>
                        </div>

                        <div v-else class="relative w-full rounded-xl overflow-hidden border border-orange-200 bg-white p-2 flex items-center gap-4 group">
                             <div class="w-16 h-16 rounded-lg bg-slate-100 border border-slate-200 overflow-hidden relative shrink-0">
                                 <img :src="formData.ktmProof" class="w-full h-full object-cover">
                             </div>
                             <div class="flex-1 min-w-0">
                                 <p class="text-sm font-bold text-slate-700 truncate">KTM Tersimpan</p>
                                 <p class="text-xs text-emerald-600 font-medium flex items-center gap-1"><i class="bi bi-check-circle-fill"></i> Upload Berhasil</p>
                             </div>
                             <button @click.prevent="removeFile('ktmProof')" class="w-8 h-8 rounded-full bg-red-50 text-red-500 flex items-center justify-center hover:bg-red-100 transition-colors" title="Hapus foto">
                                 <i class="bi bi-trash-fill"></i>
                             </button>
                        </div>
                    </div>
                </div>
            </transition>

        </div>

        <!-- STEP 2: Rute, Jadwal, & Kursi -->
        <div v-if="currentStep === 2" class="animate-fade-in space-y-6">
            <div class="space-y-5 flex-1 mt-4">
                
                <div class="relative group">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-widest block mb-1.5 px-1 group-focus-within:text-blue-600 transition-colors">Tujuan Perjalanan</label>
                    <div class="relative">
                        <select v-model="formData.routeId" class="w-full pl-11 p-4 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 transition-all appearance-none text-slate-800 font-semibold cursor-pointer">
                            <option value="" disabled>Pilih Rute Anda...</option>
                            <option v-for="route in routes" :key="route.id" :value="route.id" class="py-2">
                                {{ route.origin }} ➔ {{ route.destination }}
                            </option>
                        </select>
                        <i class="bi bi-geo-alt absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-blue-500 transition-colors"></i>
                        <i class="bi bi-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="relative group">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-widest block mb-1.5 px-1 group-focus-within:text-blue-600 transition-colors">Tanggal Keberangkatan</label>
                        <div class="relative">
                            <!-- Custom Date Input with native datepicker -->
                            <input type="date" v-model="formData.date" class="w-full pl-11 p-4 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 transition-all text-slate-800 font-semibold cursor-pointer">
                            <i class="bi bi-calendar-event absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-blue-500 transition-colors pointer-events-none"></i>
                        </div>
                    </div>

                    <div class="relative group">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-widest block mb-1.5 px-1 group-focus-within:text-blue-600 transition-colors">Jam Keberangkatan</label>
                        <div class="relative">
                            <select v-model="formData.time" :disabled="!formData.routeId" class="w-full pl-11 p-4 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 transition-all appearance-none disabled:opacity-50 disabled:cursor-not-allowed text-slate-800 font-semibold cursor-pointer">
                                <option value="" disabled>Pilih Jam...</option>
                                <option v-for="t in availableTimes" :key="t" :value="t">{{ t }}</option>
                            </select>
                            <i class="bi bi-clock absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-blue-500 transition-colors pointer-events-none"></i>
                            <i class="bi bi-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                        </div>
                    </div>
                </div>

            </div>

            <div class="mt-8 pt-8 border-t border-slate-200">
                <h3 class="text-lg font-bold text-slate-800 mb-6">Pilih Kursi</h3>
            </div>
            
            <div v-if="seatLoading" class="flex flex-col items-center justify-center py-16">
                 <div class="w-10 h-10 border-4 border-slate-100 border-t-blue-600 rounded-full animate-spin mb-4"></div>
                 <p class="text-slate-500 text-sm font-medium animate-pulse">Memuat denah kursi...</p>
            </div>

            <div v-else class="flex flex-col items-center">
                 
                 <!-- Batch UI Selector -->
                 <div v-if="availableBatches.length > 1" class="flex flex-wrap justify-center gap-2 mb-6 w-full px-4">
                     <button type="button" v-for="b in availableBatches" :key="b" @click="currentBatch = b"
                             class="flex-1 md:flex-none border py-2 px-4 rounded-xl text-sm font-bold transition-all whitespace-nowrap"
                             :class="currentBatch === b ? 'bg-blue-600 text-white border-blue-600 shadow-md transform -translate-y-0.5' : 'bg-white text-slate-500 border-slate-200 hover:border-blue-300'">
                        Armada Ke {{ b }}
                     </button>
                 </div>

                 <!-- Physical Seat Map Outline -->
                 <div class="bg-slate-50 p-6 rounded-[2rem] border border-slate-200 shadow-inner max-w-[300px] mx-auto w-full relative">
                     <!-- "Front of vehicle" visual indicator -->
                     <div class="absolute -top-3 left-1/2 -translate-x-1/2 bg-white px-4 py-1 text-[10px] uppercase font-extrabold text-slate-500 rounded-full border border-slate-200 shadow-sm">Kabin Depan</div>

                     <div class="flex justify-center pt-4">
                         <div class="bg-slate-100 p-4 rounded-xl border border-slate-200 relative w-[180px]">
                             
                             <!-- Row 1: Helper / Driver -->
                             <div class="grid grid-cols-3 gap-3 mb-4">
                                 <button type="button" @click="toggleSeat('CC')" class="w-10 h-10 rounded-lg flex items-center justify-center text-sm font-bold border transition-all" :class="getSeatClass('CC')">CC</button>
                                 
                                 <div></div> <!-- Gap -->

                                 <div class="w-10 h-10 rounded-lg bg-slate-200 text-slate-500 border border-slate-300 flex items-center justify-center text-[10px] font-bold select-none pointer-events-none">
                                     SPR
                                 </div>
                             </div>
                             
                             <!-- Row 2:  1 and 2 -->
                             <div class="grid grid-cols-3 gap-3 mb-4">
                                 <button type="button" @click="toggleSeat('1')" class="w-10 h-10 rounded-lg flex items-center justify-center text-sm font-bold border transition-all" :class="getSeatClass('1')">1</button>
                                 <div></div> <!-- Gap -->
                                 <button type="button" @click="toggleSeat('2')" class="w-10 h-10 rounded-lg flex items-center justify-center text-sm font-bold border transition-all" :class="getSeatClass('2')">2</button>
                             </div>

                             <!-- Row 3:  3 and 4 -->
                             <div class="grid grid-cols-3 gap-3 mb-4">
                                 <button type="button" @click="toggleSeat('3')" class="w-10 h-10 rounded-lg flex items-center justify-center text-sm font-bold border transition-all" :class="getSeatClass('3')">3</button>
                                 <div></div> <!-- Gap -->
                                 <button type="button" @click="toggleSeat('4')" class="w-10 h-10 rounded-lg flex items-center justify-center text-sm font-bold border transition-all" :class="getSeatClass('4')">4</button>
                             </div>

                             <!-- Row 4:  5, 6, 7 (Back Seat) -->
                             <div class="grid grid-cols-3 gap-3">
                                 <button type="button" @click="toggleSeat('5')" class="w-10 h-10 rounded-lg flex items-center justify-center text-sm font-bold border transition-all" :class="getSeatClass('5')">5</button>
                                 <button type="button" @click="toggleSeat('6')" class="w-10 h-10 rounded-lg flex items-center justify-center text-sm font-bold border transition-all" :class="getSeatClass('6')">6</button>
                                 <button type="button" @click="toggleSeat('7')" class="w-10 h-10 rounded-lg flex items-center justify-center text-sm font-bold border transition-all" :class="getSeatClass('7')">7</button>
                             </div>

                         </div>
                     </div>
                 </div>

                 <!-- Legend & Status -->
                 <div class="mt-6 flex flex-wrap justify-center gap-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest bg-slate-50 px-4 py-2 rounded-full border border-slate-100">
                     <span class="flex items-center gap-2"><div class="w-3.5 h-3.5 rounded bg-white border border-slate-300"></div> Kosong</span>
                     <span class="flex items-center gap-2"><div class="w-3.5 h-3.5 rounded bg-blue-600 border border-blue-700"></div> Dipilih</span>
                     <span class="flex items-center gap-2"><div class="w-3.5 h-3.5 rounded bg-slate-900 border border-slate-800"></div> Terisi</span>
                 </div>

                 <!-- Price Info -->
                 <div class="mt-8 bg-blue-50 border border-blue-100 rounded-2xl p-4 md:p-5 w-full flex items-center justify-between">
                      <div>
                          <p class="text-[10px] font-bold text-blue-400 uppercase tracking-widest mb-0.5">Total Tagihan</p>
                          <p class="text-xs text-blue-600 font-medium">({{ selectedSeats.length }} Kursi Terpilih)</p>
                      </div>
                      <div class="text-right">
                          <p class="font-bold text-2xl text-blue-700 tracking-tight">{{ formatRupiah(totalPrice) }}</p>
                      </div>
                 </div>

            </div>
        </div>

        <!-- STEP 3: Alamat & Bayar -->
        <div v-if="currentStep === 3" class="animate-fade-in space-y-6">
            
            <div class="flex flex-col md:flex-row gap-6">
                <!-- LEfT: ADDRESSES -->
                <div class="flex-1 space-y-6">
                    <div class="bg-slate-50 p-5 rounded-2xl border border-slate-200 relative overflow-hidden group">
                        <!-- Decorative side line -->
                        <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-blue-500"></div>
                        <h4 class="font-bold text-slate-800 text-sm mb-4 flex items-center gap-2"><i class="bi bi-geo-alt text-blue-500"></i> Detail Penjemputan</h4>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest block mb-1">Alamat Penjemputan</label>
                                <textarea v-model="formData.pickupAddress" rows="2" placeholder="Cth: Jl. Perintis Kemerdekaan No.17, Padang..." class="w-full p-3 bg-white border border-slate-200 rounded-lg text-sm outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all text-slate-800 resize-none shadow-sm"></textarea>
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest block mb-1">Link Google Maps <span class="text-slate-400 lowercase font-normal">(opsional, membantu supir)</span></label>
                                <input type="url" v-model="formData.pickupMapLink" placeholder="https://maps.app.goo.gl/..." class="w-full p-3 bg-white border border-slate-200 rounded-lg text-sm outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all text-slate-800 shadow-sm font-mono placeholder:font-sans">
                            </div>
                        </div>
                    </div>

                    <div class="bg-slate-50 p-5 rounded-2xl border border-slate-200 relative overflow-hidden group">
                        <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-green-500"></div>
                        <h4 class="font-bold text-slate-800 text-sm mb-4 flex items-center gap-2"><i class="bi bi-pin-map text-green-500"></i> Detail Pengantaran <span class="text-[10px] bg-slate-200 text-slate-500 px-2 py-0.5 rounded ml-2 uppercase tracking-wide">Opsional</span></h4>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest block mb-1">Alamat Pengantaran</label>
                                <textarea v-model="formData.dropoffAddress" rows="2" placeholder="Kosongkan jika turun di pool..." class="w-full p-3 bg-white border border-slate-200 rounded-lg text-sm outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all text-slate-800 resize-none shadow-sm"></textarea>
                            </div>
                             <!-- Map Link for dropoff excluded for brevity, can be added similar to pickup if needed -->
                        </div>
                    </div>

                    <div class="bg-slate-50 p-5 rounded-2xl border border-slate-200">
                        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest block mb-1">Catatan Tambahan / Barang Bawaan</label>
                        <input type="text" v-model="formData.bookingNote" placeholder="Cth: Bawa 1 koper besar, dan 1 dus kardus" class="w-full p-3 bg-white border border-slate-200 rounded-lg text-sm outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all text-slate-800 shadow-sm">
                    </div>
                </div>
                
                <!-- RIGHT: PAYMENT -->
                <div class="flex-1 space-y-6">
                    
                    <div>
                        <h4 class="font-bold text-slate-800 text-sm mb-3">Informasi Pembayaran</h4>
                        <div class="bg-blue-600 text-white p-5 rounded-2xl shadow-lg relative overflow-hidden">
                            <!-- decoration -->
                            <div class="absolute right-0 top-0 w-32 h-32 bg-white/10 rounded-full blur-2xl translate-x-10 -translate-y-10"></div>
                            
                            <p class="text-white/80 text-[11px] font-bold uppercase tracking-widest mb-1">Total Tagihan</p>
                            <p class="font-black text-3xl tracking-tight mb-4 flex items-center gap-2">
                                {{ formatRupiah(totalPrice) }}
                                <i class="bi bi-check-circle-fill text-blue-300 text-lg"></i>
                            </p>
                            
                            <div class="space-y-4 relative z-10">
                                <div>
                                    <label class="text-[10px] font-bold text-blue-200 uppercase tracking-widest block mb-1">Rekening Tujuan</label>
                                    <select v-model="formData.destinationAccount" class="w-full p-3 bg-white/10 border border-white/20 rounded-lg text-sm outline-none focus:border-white focus:bg-white/20 transition-all text-white appearance-none h-11">
                                        <option value="" disabled class="text-slate-800">Pilih Rekening BCA...</option>
                                        <option v-for="b in bankAccounts" :key="b.value" :value="b.value" class="text-slate-800">{{ b.label }}</option>
                                    </select>
                                </div>
                                
                                <transition name="fade">
                                    <div v-if="selectedBankAccount" class="bg-white/10 border border-white/10 rounded-xl p-3 flex justify-between items-center backdrop-blur-sm">
                                        <div>
                                            <p class="text-[10px] text-blue-200 uppercase tracking-wider mb-0.5 font-medium">No. Rekening {{ selectedBankAccount.value.split('-')[0].trim() || 'BCA' }}</p>
                                            <p class="font-mono text-lg font-bold">{{ selectedBankAccount.label.match(/\d+/)[0] }}</p>
                                        </div>
                                        <button @click.prevent="copyToClipboard(selectedBankAccount.label.match(/\d+/)[0])" class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center hover:bg-white text-white hover:text-blue-600 transition-colors shadow-sm" title="Salin Rekening">
                                            <i class="bi bi-files"></i>
                                        </button>
                                    </div>
                                </transition>
                            </div>
                        </div>
                    </div>

                    <div class="bg-slate-50 p-5 rounded-2xl border border-slate-200 space-y-4">
                        <div class="flex items-center gap-2 mb-2">
                            <i class="bi bi-cloud-arrow-up-fill text-blue-500"></i>
                            <h4 class="font-bold text-slate-800 text-sm">Upload Bukti Transfer</h4>
                        </div>
                        
                        <div class="relative">
                            <input type="file" ref="proofInputRef" @change="e => handleFileUpload(e, 'paymentProof')" accept="image/*" class="hidden" id="proof-upload">
                            
                            <div v-if="!formData.paymentProof" class="w-full">
                                <label for="proof-upload" class="flex flex-col items-center justify-center w-full h-32 border-2 border-slate-300 border-dashed rounded-xl cursor-pointer bg-white hover:bg-slate-50 hover:border-blue-400 transition-colors shadow-sm">
                                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                        <div class="w-10 h-10 bg-blue-50 text-blue-500 rounded-full flex items-center justify-center mb-2">
                                             <i class="bi bi-images text-xl"></i>
                                        </div>
                                        <p class="mb-0.5 text-sm font-bold text-slate-600">Unggah Gambar</p>
                                        <p class="text-[10px] text-slate-400 font-medium">JPEG atau PNG</p>
                                    </div>
                                </label>
                            </div>

                            <div v-else class="relative w-full rounded-xl overflow-hidden border border-blue-200 bg-white p-2 shadow-sm flex items-center gap-4 group">
                                <div class="w-16 h-16 rounded-lg bg-slate-100 border border-slate-200 overflow-hidden relative shrink-0">
                                    <img :src="formData.paymentProof" class="w-full h-full object-cover">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-bold text-slate-700 truncate">Bukti Transfer</p>
                                    <p class="text-xs text-blue-600 font-medium flex items-center gap-1"><i class="bi bi-check-circle-fill"></i> Selesai Upload</p>
                                </div>
                                <button @click.prevent="removeFile('paymentProof')" class="w-8 h-8 rounded-full bg-red-50 text-red-500 flex items-center justify-center hover:bg-red-100 transition-colors" title="Ubah foto">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>

        <div v-if="currentStep === steps.length" class="text-center py-20 px-4">
             <div class="w-24 h-24 bg-green-100 text-green-500 rounded-full flex items-center justify-center mx-auto mb-6 shadow-inner shadow-green-500/20">
                  <i class="bi bi-check-lg text-5xl"></i>
             </div>
             <h3 class="text-2xl font-black text-slate-800 tracking-tight mb-2">Validasi Data Berhasil</h3>
             <p class="text-slate-500 text-sm max-w-sm mx-auto">Silakan klik tombol di bawah ini untuk mengirimkan detail pemesanan Anda.</p>
        </div>

    </form>

    <!-- Navigation Buttons Outside Card -->
    <div class="mt-6 flex items-center justify-between">
        
        <button v-if="currentStep > 1 && currentStep < steps.length" type="button" @click="prevStep" 
                class="px-5 md:px-8 py-3.5 bg-white border border-slate-200 text-slate-600 font-bold text-sm rounded-xl hover:bg-slate-50 transition-colors shadow-sm">
            Kembali
        </button>
        <div v-else></div> <!-- Spacer -->

        <button v-if="currentStep < steps.length" type="button" @click="nextStep"
                class="px-8 md:px-12 py-3.5 bg-blue-600 text-white font-bold text-sm rounded-xl shadow-md hover:bg-blue-700 hover:-translate-y-0.5 transition-all transform active:scale-95 ml-auto">
            Lanjut
        </button>

        <!-- Submit Button Trigger -->
        <button v-if="currentStep === steps.length" type="button" @click="submitBooking" :disabled="loading"
                class="w-full py-4 bg-green-500 text-white font-bold text-base rounded-2xl shadow-md hover:bg-green-600 hover:-translate-y-1 transition-all transform active:scale-95 flex justify-center items-center gap-2">
            <span v-if="loading" class="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
            <span v-else><i class="bi bi-send-fill mb-0.5"></i> Proses Pemesanan</span>
        </button>

    </div>

    <br><br><br><br>
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
