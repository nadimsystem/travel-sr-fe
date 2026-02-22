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
    // Adjust path matching backend logic
    const cleanPath = path.replace(/^uploads\//, '')
    const url = `/display-v12/uploads/${cleanPath}`
    window.open(url, '_blank')
}

onMounted(() => {
    loadHistory()
})
</script>

<template>
  <div class="booking-history-container max-w-3xl mx-auto py-6 md:py-10 px-4">
    
    <!-- Login State -->
    <transition name="fade" mode="out-in">
        <div v-if="!isLoggedIn" class="bg-white rounded-[1.5rem] shadow-sm border border-slate-200 p-8 text-center max-w-md mx-auto mt-10">
            <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-6">
                <i class="bi bi-search text-3xl"></i>
            </div>
            <h2 class="text-2xl font-black text-slate-800 tracking-tight mb-2">Cek Riwayat</h2>
            <p class="text-slate-500 text-sm mb-8">Masukkan data Anda untuk melihat tiket.</p>
            
            <form @submit.prevent="handleLogin" class="space-y-5 text-left">
                <div>
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-widest block mb-1.5 px-1">Nomor WhatsApp</label>
                    <div class="relative">
                        <input type="tel" v-model="loginPhone" placeholder="08..." class="w-full pl-11 p-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 transition-all font-medium text-slate-800">
                        <i class="bi bi-whatsapp absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    </div>
                </div>
                <div>
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-widest block mb-1.5 px-1">Nama Pemesan</label>
                    <div class="relative">
                        <input type="text" v-model="loginName" placeholder="Nama Lengkap..." class="w-full pl-11 p-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 transition-all font-medium text-slate-800">
                        <i class="bi bi-person absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    </div>
                </div>
                
                <button type="submit" :disabled="loginLoading" class="w-full bg-blue-600 text-white font-bold p-4 rounded-xl shadow-lg shadow-blue-600/20 hover:shadow-blue-600/40 transition-all mt-4 flex justify-center items-center">
                    <span v-if="loginLoading" class="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                    <span v-else>Lihat Tiket Saya</span>
                </button>
            </form>
        </div>

        <!-- Logged In / List State -->
        <div v-else class="animate-fade-in space-y-6">
            
            <div class="flex items-center justify-between mb-8">
                 <div>
                     <h2 class="text-2xl font-black text-slate-800 tracking-tight">Riwayat Pemesanan</h2>
                     <p class="text-slate-500 text-sm mt-1">Halo <span class="font-bold text-blue-600">{{ loginName }}</span>, ini daftar tiket Anda.</p>
                 </div>
                 <button @click="logout" class="px-4 py-2 bg-slate-100 text-slate-600 font-bold text-xs rounded-lg hover:bg-slate-200 transition-colors flex items-center gap-2">
                     <i class="bi bi-box-arrow-right"></i> Keluar
                 </button>
            </div>

            <div v-if="historyLoading" class="text-center py-20">
                 <div class="w-10 h-10 border-4 border-slate-200 border-t-blue-600 rounded-full animate-spin mx-auto mb-4"></div>
                 <p class="text-slate-500 text-sm font-medium">Memuat tiket...</p>
            </div>
            
            <div v-else-if="errorMsg" class="text-center py-10 bg-red-50 rounded-2xl border border-red-100">
                <p class="text-red-500 text-sm font-medium">{{ errorMsg }}</p>
            </div>

            <div v-else-if="bookingHistory.length === 0" class="text-center py-20 bg-white rounded-[1.5rem] border border-slate-200 shadow-sm">
                <i class="bi bi-ticket-detailed text-5xl text-slate-300 mb-4 block"></i>
                <h3 class="font-bold text-slate-700 text-lg mb-2">Belum Ada Transaksi</h3>
                <p class="text-slate-500 text-sm mb-6">Anda belum memiliki riwayat pemesanan tiket travel.</p>
                <button @click="emit('go-to-booking')" class="px-6 py-2.5 bg-blue-600 text-white font-bold text-sm rounded-xl shadow-md hover:bg-blue-700 transition-colors">
                    Pesan Tiket Sekarang
                </button>
            </div>

            <div v-else class="space-y-4">
                <div v-for="booking in bookingHistory" :key="booking.id" class="bg-white rounded-[1.5rem] shadow-sm border border-slate-200 overflow-hidden relative group hover:border-blue-300 transition-colors">
                    
                    <div class="absolute top-0 right-0 w-24 h-24 bg-slate-50 rounded-bl-full -z-0"></div>

                    <div class="p-6 relative z-10">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
                            <div>
                                <div class="flex items-center gap-3 mb-1">
                                    <span class="text-xs font-bold bg-blue-50 text-blue-700 px-2.5 py-1 rounded-md">ID: {{ booking.id }}</span>
                                    <span class="text-xs font-bold px-2.5 py-1 rounded-md border flex items-center gap-1.5" :class="getStatusBadgeClass(booking.status)">
                                        <i :class="getStatusIcon(booking.status)"></i> {{ booking.status || 'MENUNGGU VERSIFIKASI' }}
                                    </span>
                                </div>
                                <h3 class="font-black text-slate-800 text-xl tracking-tight">{{ booking.routeName || booking.route_name || booking.rute || '-' }}</h3>
                                <p class="text-slate-500 text-sm font-medium mt-1"><i class="bi bi-calendar-event mr-1 text-slate-400"></i> {{ formatDate(booking.date || booking.booking_date || booking.tanggal_berangkat) }}</p>
                            </div>
                            
                            <div class="text-left md:text-right bg-slate-50 md:bg-transparent p-3 md:p-0 rounded-lg">
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Jam Keberangkatan</p>
                                <p class="font-black text-2xl text-blue-600 leading-none">{{ booking.time || booking.jam_berangkat }}</p>
                            </div>
                        </div>

                        <div class="border-t border-dashed border-slate-200 my-4"></div>

                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div>
                                <p class="text-[10px] uppercase font-bold text-slate-400 mb-1 tracking-wider">Penumpang</p>
                                <p class="font-bold text-slate-700 text-sm whitespace-nowrap overflow-hidden text-ellipsis">{{ booking.passengerName || booking.passenger_name || booking.nama || loginName }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] uppercase font-bold text-slate-400 mb-1 tracking-wider">Tipe</p>
                                <p class="font-bold text-slate-700 text-sm">{{ booking.passengerType || booking.passenger_type || booking.jenis_penumpang || 'Umum' }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] uppercase font-bold text-slate-400 mb-1 tracking-wider">Kursi</p>
                                <div class="flex gap-1 flex-wrap">
                                    <span v-for="seat in (booking.seatNumbers || booking.seat_numbers || booking.kursi || '').split(',')" :key="seat" class="w-6 h-6 bg-slate-100 text-slate-700 rounded text-[10px] font-black flex items-center justify-center border border-slate-200">{{ seat.trim() }}</span>
                                </div>
                            </div>
                            <div>
                                <p class="text-[10px] uppercase font-bold text-slate-400 mb-1 tracking-wider">Total Harga</p>
                                <p class="font-bold text-slate-800 text-sm">{{ formatRupiah(booking.totalPrice || booking.total_price || booking.harga) }}</p>
                            </div>
                        </div>

                        <!-- Attachments Action -->
                        <div v-if="booking.paymentProof || booking.payment_proof || booking.ktmProof || booking.ktm_proof" class="mt-4 pt-4 border-t border-slate-100 flex flex-wrap gap-2">
                             <button v-if="booking.paymentProof || booking.payment_proof" @click.prevent="viewProof(booking.paymentProof || booking.payment_proof)" class="text-[11px] font-bold text-blue-600 bg-blue-50 px-3 py-1.5 rounded-md hover:bg-blue-100 transition-colors flex items-center gap-1.5">
                                 <i class="bi bi-receipt"></i> Bukti Pembayaran
                             </button>
                             <button v-if="booking.ktmProof || booking.ktm_proof" @click.prevent="viewProof(booking.ktmProof || booking.ktm_proof)" class="text-[11px] font-bold text-orange-600 bg-orange-50 px-3 py-1.5 rounded-md hover:bg-orange-100 transition-colors flex items-center gap-1.5">
                                 <i class="bi bi-person-vcard"></i> Foto Identitas/KTM
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
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.animate-fade-in {
    animation: fadeIn 0.4s ease-out forwards;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
