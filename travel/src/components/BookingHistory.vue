<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import Swal from 'sweetalert2'

const emit = defineEmits(['go-to-booking'])

// State for Login View
const isLoggedIn = ref(false)
const loginPhone = ref('')
const loginName = ref('')
const loginLoading = ref(false)

// demi apapun pusing ya Allah
const bookingHistory = ref([])
const historyLoading = ref(false)
const errorMsg = ref(null)

const handleLogin = async () => {
    errorMsg.value = null
    if (!loginPhone.value || !loginName.value) {
        Swal.fire({
            icon: 'warning',
            title: 'Data Tidak Lengkap',
            text: 'Harap isi Nomor WhatsApp dan Nama Penumpang.',
            confirmButtonColor: '#2563eb'
        })
        return
    }

    loginLoading.value = true
    try {
        const res = await axios.get('api/', {
            params: {
                action: 'get_booking_history',
                phone: loginPhone.value,
                name: loginName.value
            }
        })
        if (res.data.status === 'success') {
            localStorage.setItem('travel_history_session', JSON.stringify({
                phone: loginPhone.value,
                name: loginName.value
            }))
            bookingHistory.value = res.data.data
            isLoggedIn.value = true
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Data Tidak Ditemukan',
                text: res.data.message || 'Pastikan nomor WhatsApp dan Nama sesuai dengan saat pemesanan.',
                confirmButtonColor: '#2563eb'
            })
        }
    } catch (e) {
        console.error("Login Error:", e)
        Swal.fire({
            icon: 'error',
            title: 'Kesalahan Sistem',
            text: 'Gagal terhubung ke server.',
             confirmButtonColor: '#2563eb'
        })
    } finally {
        loginLoading.value = false
    }
}

const loadHistory = async () => {
    errorMsg.value = null
    const session = JSON.parse(localStorage.getItem('travel_history_session'))
    if (!session || !session.phone || !session.name) return

    loginPhone.value = session.phone
    loginName.value = session.name
    
    historyLoading.value = true
    try {
        const res = await axios.get('api/', {
            params: {
                action: 'get_booking_history',
                phone: session.phone,
                name: session.name
            }
        })
        if (res.data.status === 'success') {
            bookingHistory.value = res.data.data
            isLoggedIn.value = true
        } else {
             localStorage.removeItem('travel_history_session')
             isLoggedIn.value = false
        }
    } catch (e) {
        console.error("Fetch History Error:", e)
        errorMsg.value = "Gagal memuat data riwayat pemesanan."
    } finally {
        historyLoading.value = false
    }
}

const logout = () => {
    localStorage.removeItem('travel_history_session')
    isLoggedIn.value = false
    bookingHistory.value = []
    loginPhone.value = ''
    loginName.value = ''
    errorMsg.value = null
}

const getStatusBadgeClass = (status) => {
    if (!status) return 'bg-slate-100 text-slate-500'
    const s = status.toLowerCase()
    if (s.includes('selesai') || s.includes('lunas') || s.includes('sukses')) return 'bg-green-100 text-green-700 border-green-200'
    if (s.includes('batal') || s.includes('ditolak')) return 'bg-red-100 text-red-700 border-red-200'
    if (s.includes('antrian') || s.includes('pending') || s.includes('menunggu')) return 'bg-yellow-100 text-yellow-700 border-yellow-200'
    return 'bg-blue-100 text-blue-700 border-blue-200'
}

const getStatusIcon = (status) => {
    if (!status) return 'bi-info-circle-fill'
    const s = status.toLowerCase()
    if (s.includes('selesai') || s.includes('lunas') || s.includes('sukses')) return 'bi-check-circle-fill'
    if (s.includes('batal') || s.includes('ditolak')) return 'bi-x-circle-fill'
    if (s.includes('antrian') || s.includes('pending') || s.includes('menunggu')) return 'bi-clock-history'
    return 'bi-info-circle-fill'
}

const formatDate = (dateString, format = 'long') => {
    if(!dateString) return '-'
    const d = new Date(dateString)
    if (isNaN(d.getTime())) return dateString
    
    if(format === 'short') {
        return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short' })
    }
    return d.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })
}

const formatRupiah = (number) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number)
}

const viewProof = (path) => {
    if(!path) return
    // Remove any leading slashes first
    let cleanPath = path.replace(/^\/+/, '')
    // Remove known directory prefixes
    cleanPath = cleanPath.replace(/^public\//, '')
    cleanPath = cleanPath.replace(/^uploads\//, '')
    cleanPath = cleanPath.replace(/^buktibayar\//, '')
    cleanPath = cleanPath.replace(/^\/+/, '')
    
    // Auto detect base folder for uploads
    const currentPath = window.location.pathname;
    let baseFolder = '';
    if (currentPath.includes('/display-v11')) {
        baseFolder = '/display-v11';
    } else if (currentPath.includes('/display-v12')) {
        baseFolder = '/display-v12';
    } else if (currentPath.includes('/travel-sr-fe/travel')) {
        baseFolder = '/travel-sr-fe/travel';
    } else {
        const isOnline = window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1';
        baseFolder = isOnline ? '/display-v11' : '/display-v12';
    }
    
    const url = `${baseFolder}/uploads/${cleanPath}`
    
    Swal.fire({
        imageUrl: url,
        imageAlt: 'Bukti Pembayaran',
        imageWidth: '100%',
        title: 'Bukti Pembayaran',
        showConfirmButton: true,
        confirmButtonText: '<i class="bi bi-download"></i> Buka / Download',
        confirmButtonColor: '#2563eb',
        showCloseButton: true,
        didOpen: () => {},
    }).then((result) => {
        if (result.isConfirmed) {
            window.open(url, '_blank')
        }
    })
}

onMounted(() => {
    loadHistory()
})
</script>

<template>
  <div class="booking-history-container max-w-2xl mx-auto py-6 md:py-12 px-5 min-h-screen">
    
    <!-- Login State -->
    <transition name="fade" mode="out-in">
        <div v-if="!isLoggedIn" class="animate-fade-in mt-12">
            <div class="text-center mb-10">
                <div class="w-20 h-20 bg-[#f5f5f7] rounded-[24px] flex items-center justify-center mx-auto mb-6 shadow-sm">
                    <i class="bi bi-ticket-perforated-fill text-4xl text-[#0071e3]"></i>
                </div>
                <h2 class="text-[32px] font-black text-[#1d1d1f] tracking-tight mb-3">Pesanan Saya</h2>
                <p class="text-[#8e8e93] text-[15px] font-medium leading-relaxed px-4">Masukan nama dan nomor WhatsApp untuk memantau tiket perjalanan kamu.</p>
            </div>
            
            <div v-if="errorMsg" class="mb-8 p-5 bg-[#ff3b30]/5 border border-[#ff3b30]/10 rounded-[20px] flex items-start gap-4">
                <div class="w-10 h-10 bg-[#ff3b30] rounded-full flex items-center justify-center shrink-0">
                    <i class="bi bi-exclamation-triangle-fill text-white"></i>
                </div>
                <div>
                    <p class="text-[15px] font-bold text-[#1d1d1f]">Gagal Memuat</p>
                    <p class="text-[13px] text-[#ff3b30] font-medium mt-0.5">{{ errorMsg }}</p>
                </div>
            </div>
            
            <form @submit.prevent="handleLogin" class="space-y-4">
                <div class="bg-white rounded-[24px] shadow-sm border border-[rgba(0,0,0,0.03)] overflow-hidden">
                    <div class="p-1">
                        <div class="relative">
                            <input type="tel" v-model="loginPhone" placeholder="Nomor WhatsApp (08...)" 
                                   class="w-full h-[64px] px-6 bg-transparent border-0 text-[17px] outline-none font-medium text-[#1d1d1f] placeholder:text-[#c7c7cc]">
                        </div>
                        <div class="h-px bg-[#f5f5f7] mx-6"></div>
                        <div class="relative">
                            <input type="text" v-model="loginName" placeholder="Nama Lengkap" 
                                   class="w-full h-[64px] px-6 bg-transparent border-0 text-[17px] outline-none font-medium text-[#1d1d1f] placeholder:text-[#c7c7cc]">
                        </div>
                    </div>
                </div>
                
                <button type="submit" :disabled="loginLoading" 
                        class="w-full h-[64px] bg-[#0071e3] text-white font-bold text-[17px] rounded-[22px] shadow-lg shadow-blue-500/20 active:scale-95 transition-all flex justify-center items-center gap-3">
                    <span v-if="loginLoading" class="w-6 h-6 border-3 border-white/30 border-t-white rounded-full animate-spin"></span>
                    <span v-else>Cek Riwayat Pesanan</span>
                </button>
            </form>
        </div>

        <!-- Logged In / List State -->
        <div v-else class="animate-fade-in space-y-8 pb-safe-area">
            
            <!-- Profile Header Card -->
            <div class="bg-white rounded-[28px] p-6 flex items-center justify-between shadow-sm border border-[rgba(0,0,0,0.03)] border-b-2">
                 <div class="flex items-center gap-4">
                     <div class="w-14 h-14 bg-gradient-to-br from-[#0071e3] to-[#5856d6] rounded-[18px] flex items-center justify-center text-white font-black text-xl shadow-md shadow-blue-500/10">
                         {{ loginName.charAt(0).toUpperCase() }}
                     </div>
                     <div>
                         <h2 class="text-[19px] font-black text-[#1d1d1f] leading-none mb-1.5">{{ loginName }}</h2>
                         <div class="flex items-center gap-1.5">
                             <span class="w-2 h-2 bg-[#34c759] rounded-full"></span>
                             <p class="text-[#8e8e93] text-[13px] font-bold">{{ loginPhone }}</p>
                         </div>
                     </div>
                 </div>
                 <button @click="logout" class="w-12 h-12 bg-[#f5f5f7] rounded-[15px] text-[#ff3b30] flex items-center justify-center active:scale-90 transition-transform">
                     <i class="bi bi-power text-xl"></i>
                 </button>
            </div>

            <div v-if="historyLoading" class="flex flex-col items-center justify-center py-20">
                 <div class="w-12 h-12 border-4 border-[#f5f5f7] border-t-[#0071e3] rounded-full animate-spin mb-6"></div>
                 <p class="text-[#8e8e93] text-[15px] font-bold">Menyinkronkan data...</p>
            </div>
            
            <div v-else-if="bookingHistory.length === 0" class="text-center py-24 bg-white rounded-[32px] border border-[rgba(0,0,0,0.03)] shadow-sm px-8">
                <div class="w-24 h-24 bg-[#f5f5f7] rounded-full flex items-center justify-center mx-auto mb-8">
                    <i class="bi bi-ticket-slash text-4xl text-[#c7c7cc]"></i>
                </div>
                <h3 class="font-black text-[#1d1d1f] text-2xl mb-3">Belum Ada Tiket</h3>
                <p class="text-[#8e8e93] text-[15px] font-medium mb-10 leading-relaxed">Sepertinya kamu belum melakukan pemesanan perjalanan bersama kami.</p>
                <button @click="emit('go-to-booking')" 
                        class="px-8 h-[58px] bg-[#0071e3] text-white font-bold text-[16px] rounded-[20px] shadow-lg shadow-blue-500/15 hover:opacity-90 transition-all active:scale-95">
                    Pesan Tiket Sekarang
                </button>
            </div>

            <div v-else class="space-y-6">
                <div v-for="booking in bookingHistory" :key="booking.id" 
                     class="bg-white rounded-[32px] shadow-sm border border-[rgba(0,0,0,0.03)] overflow-hidden active:scale-[0.98] transition-all border-b-2">
                    
                    <div class="p-7">
                        <!-- Card Header -->
                        <div class="flex items-start justify-between mb-8">
                            <div class="flex items-center gap-4">
                                <div class="w-14 h-14 bg-[#f5f5f7] rounded-[18px] flex items-center justify-center shrink-0">
                                    <i class="bi bi-bus-front-fill text-2xl text-[#1d1d1f]"></i>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2 mb-1">
                                        <h3 class="font-black text-[#1d1d1f] text-xl tracking-tighter">{{ (booking.routeName || booking.route_name || booking.rute || '-').split('➔')[0] }}</h3>
                                        <i class="bi bi-arrow-right text-[#c7c7cc]"></i>
                                        <h3 class="font-black text-[#1d1d1f] text-xl tracking-tighter">{{ (booking.routeName || booking.route_name || booking.rute || '-').split('➔')[1] }}</h3>
                                    </div>
                                    <p class="text-[#0071e3] text-[13px] font-black uppercase tracking-widest">{{ formatDate(booking.date || booking.booking_date || booking.tanggal_berangkat, 'short') }} • {{ booking.time || booking.jam_berangkat }}</p>
                                </div>
                            </div>
                            <!-- Status -->
                            <div class="px-4 py-2 rounded-full text-[10px] font-black uppercase tracking-widest shadow-sm" :class="getStatusBadgeClass(booking.status)">
                                {{ booking.status || 'PROSES' }}
                            </div>
                        </div>

                        <!-- Ticket Body -->
                        <div class="bg-[#f5f5f7] rounded-[24px] p-6 mb-6">
                            <div class="grid grid-cols-2 gap-y-6">
                                <div>
                                    <p class="text-[10px] font-black text-[#8e8e93] uppercase tracking-widest mb-1">KODE BOOKING</p>
                                    <p class="font-black text-[#1d1d1f] text-[17px] tracking-tight">#{{ booking.id }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-[10px] font-black text-[#8e8e93] uppercase tracking-widest mb-1">TIPE</p>
                                    <p class="font-black text-[#1d1d1f] text-[17px] tracking-tight">{{ booking.passengerType || booking.passenger_type || booking.jenis_penumpang || 'Umum' }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-black text-[#8e8e93] uppercase tracking-widest mb-2">KURSI</p>
                                    <div class="flex gap-2 flex-wrap">
                                        <span v-for="seat in (booking.seatNumbers || booking.seat_numbers || booking.kursi || '').split(',')" 
                                              :key="seat" 
                                              class="w-8 h-8 bg-white text-[#1d1d1f] rounded-[10px] text-[12px] font-black flex items-center justify-center border border-[rgba(0,0,0,0.05)] shadow-sm">
                                            {{ seat.trim() }}
                                        </span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-[10px] font-black text-[#8e8e93] uppercase tracking-widest mb-1">TOTAL BAYAR</p>
                                    <p class="font-black text-[#0071e3] text-[20px] tracking-tighter">{{ formatRupiah(booking.totalPrice || booking.total_price || booking.harga) }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Card Footer / Actions -->
                        <div v-if="booking.paymentProof || booking.payment_proof || booking.ktmProof || booking.ktm_proof" 
                             class="flex flex-wrap gap-3">
                             <button v-if="booking.paymentProof || booking.payment_proof" 
                                     @click.prevent="viewProof(booking.paymentProof || booking.payment_proof)" 
                                     class="flex-1 h-12 bg-white border border-[#f5f5f7] rounded-[14px] text-[13px] font-black text-[#1d1d1f] flex items-center justify-center gap-2 active:scale-95 transition-all shadow-sm">
                                 <i class="bi bi-receipt-cutoff text-lg text-[#0071e3]"></i> Bukti Bayar
                             </button>
                             <button v-if="booking.ktmProof || booking.ktm_proof" 
                                     @click.prevent="viewProof(booking.ktmProof || booking.ktm_proof)" 
                                     class="flex-1 h-12 bg-white border border-[#f5f5f7] rounded-[14px] text-[13px] font-black text-[#1d1d1f] flex items-center justify-center gap-2 active:scale-95 transition-all shadow-sm">
                                 <i class="bi bi-person-vcard-fill text-lg text-[#5856d6]"></i> Kartu KTM
                             </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </transition>
  </div>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
.fade-enter-from, .fade-leave-to { opacity: 0; transform: scale(0.98); }

.animate-fade-in {
    animation: fadeIn 0.6s cubic-bezier(0.4, 0, 0.2, 1) forwards;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.pb-safe-area {
    padding-bottom: calc(100px + env(safe-area-inset-bottom));
}

.ml-13 { margin-left: 3.25rem; }
</style>
