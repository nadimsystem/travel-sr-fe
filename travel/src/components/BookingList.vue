<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import axios from 'axios'
import Swal from 'sweetalert2'

const loading = ref(true)
const error = ref(null)
const routes = ref([])

const filters = ref({
  date: new Date().toISOString().split('T')[0],
  routeId: '',
  time: ''
})

// Date constraints: min = today, max = 1 month ahead
const today = new Date().toISOString().split('T')[0]
const maxDate = (() => {
  const d = new Date()
  d.setMonth(d.getMonth() + 1)
  return d.toISOString().split('T')[0]
})()

// Fetch all routes mapping and schedule definitions
const fetchRoutes = async () => {
  try {
    const res = await axios.get('api/?action=get_routes')
    if (res.data.status === 'success') {
      routes.value = res.data.routes
    } else {
      error.value = "Gagal memuat daftar rute"
    }
  } catch (err) {
    error.value = "Koneksi ke server gagal"
  }
}

// Format Helper
const formatRupiah = (number) => {
  const parsed = parseInt(String(number || '').replace(/\D/g, '')) || 0;
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(parsed)
}

// Fallback Prices in case DB returns 0
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

const getRoutePrice = (route, type = 'umum') => {
  const rawStr = type === 'umum'
    ? String(route.price_umum || route.price || route.harga || route.tarif || '')
    : String(route.price_pelajar || '')
  const parsed = parseInt(rawStr.replace(/\D/g, '')) || 0
  if (parsed > 0) return parsed
  return fallbackPrices[route.id]?.[type] || 0
}

const formatDate = (dateString) => {
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }
    return new Date(dateString).toLocaleDateString('id-ID', options)
}

// Seat Map Status Data Structure
const schedulesData = ref({}) 
const schedulesLoading = ref(false)

const loadSchedulesForFilter = async () => {
    if(!filters.value.date) return
    schedulesLoading.value = true
    error.value = null
    
    try {
        const url = `api/?action=get_daily_booked_seats&date=${filters.value.date}`
        const res = await axios.get(url)
        
        if (res.data.status === 'success') {
             const bookingsByRouteAndTime = {}
             
             if (res.data.data && Array.isArray(res.data.data)) {
                 res.data.data.forEach(booking => {
                     const rId = booking.physicalRouteId || booking.routeId
                     const t = booking.time
                     if (!rId || !t) return
                     
                     if (!bookingsByRouteAndTime[rId]) bookingsByRouteAndTime[rId] = {}
                     if (!bookingsByRouteAndTime[rId][t]) bookingsByRouteAndTime[rId][t] = []
                     
                     bookingsByRouteAndTime[rId][t].push(booking)
                 })
             }
             
             const parsedData = {}
             
             for (const rId in bookingsByRouteAndTime) {
                 parsedData[rId] = {}
                 for (const t in bookingsByRouteAndTime[rId]) {
                     parsedData[rId][t] = { batches: {} }
                     const slotBookings = bookingsByRouteAndTime[rId][t]
                     
                     const BAT_CAPACITY = 8;
                     const batchesMap = new Map(); 
                     const unassignedBookings = [];
                     
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
                     let currentBatchForUnassigned = 1;
                     
                     unassignedBookings.forEach(b => {
                         const thisBookingSeats = b.seatNumbers 
                             ? b.seatNumbers.split(',').map(s => s.trim()).filter(s => s)
                             : [];
                         const seatsCount = thisBookingSeats.length || 1;
                         
                         let placed = false;
                         let checkBatch = 1;

                         while (!placed) {
                             const currentBatchList = batchesMap.get(checkBatch) || [];
                             let totalPaxInBatch = 0;
                             
                             currentBatchList.forEach(existing => {
                                 if (existing.seatNumbers) {
                                     totalPaxInBatch += existing.seatNumbers.split(',').filter(s => s.trim() !== '').length;
                                 } else {
                                     totalPaxInBatch += parseInt(existing.seatCount) || 1;
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
                     
                     batchesMap.forEach((batchBookings, bNum) => {
                         let batchPaxCount = 0;
                         const batchSeats = [];
                         batchBookings.forEach(booking => {
                             const seatsRaw = booking.seatNumbers || '';
                             const seatParts = seatsRaw.split(',').filter(s => s.trim());
                             
                             batchPaxCount += seatParts.length > 0 ? seatParts.length : 1;
                             
                             const seats = seatParts.map(s => {
                                 return { 
                                     seat: s.trim(), 
                                     status: booking.validationStatus,
                                     bookingStatus: booking.status
                                 }
                             });
                             
                             batchSeats.push(...seats);
                         });
                         
                         batchSeats.paxCount = batchPaxCount;
                         parsedData[rId][t].batches[bNum] = batchSeats;
                     })
                 }
             }
             
             schedulesData.value = parsedData
        }
    } catch (e) {
        console.error("Gagal load schedules", e)
    } finally {
        schedulesLoading.value = false
    }
}

watch(() => filters.value.date, () => {
    loadSchedulesForFilter()
})

const parseSchedules = (schedules) => {
    const times = [];
    if (!Array.isArray(schedules)) return times;
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
    return times;
}

const getAvailableTimesForRoute = computed(() => {
    const r = routes.value.find(x => x.id === filters.value.routeId)
    return r ? parseSchedules(r.schedules) : []
})

const filteredRoutes = computed(() => {
    let result = routes.value
    if (filters.value.routeId) {
        result = result.filter(r => r.id === filters.value.routeId)
    }
    return result
})

// Grouped routes for the select dropdown
const groupedRoutesForSelect = computed(() => {
    const groups = {}
    routes.value.forEach(route => {
        if (route.origin.toLowerCase().includes('via sitinjau') || route.destination.toLowerCase().includes('via sitinjau')) return
        let o = route.origin.toLowerCase().replace(/ via .*/, '').replace(/ \(.*\)/, '').trim()
        o = o.split(' ').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ')
        if (!groups[o]) groups[o] = []
        groups[o].push(route)
    })
    return Object.keys(groups).sort().map(key => ({ label: `Keberangkatan: ${key}`, routes: groups[key] }))
})

// Group schedules by time, inject seat data
const groupedRoutes = computed(() => {
    return filteredRoutes.value.map(route => {
        let schedules = []
        if (route.schedules && Array.isArray(route.schedules)) {
             
             let timesToDisplay = parseSchedules(route.schedules)
             if (filters.value.time) {
                 timesToDisplay = timesToDisplay.filter(t => t === filters.value.time)
             }

             schedules = timesToDisplay.map(timeString => {
                  timeString = timeString.replace('.', ':')
                  
                  const routeData = schedulesData.value[route.id] || {}
                  const timeData = routeData[timeString] || {}
                  const batchesObj = timeData.batches || {}

                  const batchesArray = [1, 2, 3].map(batchNum => {
                      let occupiedObjects = batchesObj[batchNum] || []
                      let truePaxCount = occupiedObjects.paxCount || 0
                      
                      occupiedObjects = occupiedObjects.filter(obj => obj.seat.toLowerCase() !== 'pending' && obj.seat.toLowerCase() !== 'menunggu')
                      
                      const uniqueOccupied = []
                      const seenSeats = new Set()
                      occupiedObjects.forEach(obj => {
                          if (!seenSeats.has(obj.seat)) {
                              seenSeats.add(obj.seat)
                              uniqueOccupied.push(obj)
                          }
                      })

                      return {
                          number: batchNum,
                          occupiedSeats: uniqueOccupied,
                          availableCount: Math.max(0, 8 - truePaxCount)
                      }
                  })

                  return { time: timeString, batches: batchesArray }
             })
        }
        return { ...route, displaySchedules: schedules }
    })
})

onMounted(async () => {
    loading.value = true
    await Promise.all([
        fetchRoutes(),
        loadSchedulesForFilter()
    ])
    loading.value = false
})

const cleanRouteName = (name) => name ? name.replace(/\s*\(Normal\)/i, '').trim() : ''

const getSeatIconClass = (seatNum, batch) => {
    const isOccupied = batch.occupiedSeats.some(s => s.seat === String(seatNum))
    if (isOccupied) return 'bi-person-fill text-[#a1a1a6]'
    return 'bi-square text-[#34c759]' // Available iOS Green
}

const getSeatClass = (seatNum, batch) => {
    const seatObj = batch.occupiedSeats.find(s => s.seat === String(seatNum))
    
    if (seatObj) {
        // Pending/Waiting
        if (seatObj.status === 'Review' || seatObj.status === 'Menunggu Validasi' || seatObj.bookingStatus === 'Antrian') {
            return 'bg-[#ff9f0a] border-[#ff9f0a] text-white shadow-sm'
        }
        // Occupied Confirmed
        return 'bg-[#1d1d1f] border-[#1d1d1f] text-white shadow-sm cursor-not-allowed'
    }
    
    // Empty
    return 'bg-white border-[#d2d2d7] text-[#1d1d1f]'
}
</script>

<template>
  <div class="min-h-screen font-sans pb-24 bg-[#fbfbfd]">
    
    <!-- Top Filter & Header (Apple Squircles) -->
    <div class="px-4 md:px-6 pt-10 pb-6">
        <h2 class="text-3xl font-semibold text-[#1d1d1f] tracking-tight mb-8 text-center">Jadwal & Seat Map</h2>
        
        <div class="max-w-xl mx-auto space-y-3">
             <!-- Date -->
             <div class="relative group">
                <input type="date" v-model="filters.date" :min="today" :max="maxDate" class="w-full pl-12 p-3.5 bg-white border border-[#d2d2d7] rounded-[14px] text-[15px] outline-none focus:border-[#0071e3] transition-colors font-medium text-[#1d1d1f] shadow-sm">
                <i class="bi bi-calendar-event absolute left-4 top-1/2 -translate-y-1/2 text-[#86868b] group-focus-within:text-[#0071e3] text-lg"></i>
             </div>

             <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                 <!-- Route -->
                 <div class="relative group">
                    <select v-model="filters.routeId" class="w-full pl-12 p-3.5 bg-white border border-[#d2d2d7] rounded-[14px] text-[15px] outline-none focus:border-[#0071e3] transition-colors font-medium appearance-none text-[#1d1d1f] shadow-sm">
                        <option value="">Semua Rute</option>
                        <optgroup v-for="group in groupedRoutesForSelect" :key="group.label" :label="group.label">
                            <option v-for="route in group.routes" :key="route.id" :value="route.id">
                                {{ route.origin }} ➔ {{ route.destination }}
                            </option>
                        </optgroup>
                    </select>
                    <i class="bi bi-geo-alt absolute left-4 top-1/2 -translate-y-1/2 text-[#86868b] group-focus-within:text-[#0071e3] text-lg"></i>
                 </div>

                 <!-- Time -->
                 <div class="relative group">
                    <select v-model="filters.time" :disabled="!filters.routeId" class="w-full pl-12 p-3.5 bg-white border border-[#d2d2d7] rounded-[14px] text-[15px] outline-none focus:border-[#0071e3] transition-colors font-medium appearance-none text-[#1d1d1f] disabled:opacity-50 shadow-sm">
                        <option value="">Semua Waktu</option>
                        <option v-for="t in getAvailableTimesForRoute" :key="t" :value="typeof t === 'string' ? t : t.time">{{ typeof t === 'string' ? t : t.time }}</option>
                    </select>
                    <i class="bi bi-clock absolute left-4 top-1/2 -translate-y-1/2 text-[#86868b] group-focus-within:text-[#0071e3] text-lg"></i>
                 </div>
             </div>
        </div>
    </div>

    <!-- Active Date Display -->
    <div class="max-w-xl mx-auto px-4 mb-6 flex justify-between items-center">
        <span class="text-[#86868b] font-medium text-[15px]">{{ formatDate(filters.date) }}</span>
        <button @click="loadSchedulesForFilter" class="w-8 h-8 rounded-full bg-[#f5f5f7] flex items-center justify-center hover:bg-[#e8e8ed] transition-colors text-[#1d1d1f]" title="Refresh Data">
            <i class="bi bi-arrow-clockwise text-lg" :class="{'animate-spin': schedulesLoading}"></i>
        </button>
    </div>

    <!-- Main Content -->
    <div class="max-w-5xl mx-auto px-4">
        
        <div v-if="loading" class="text-center py-24">
             <div class="w-8 h-8 border-[3px] border-[#e5e5ea] border-t-[#0071e3] rounded-full animate-spin mx-auto mb-4"></div>
             <p class="text-[#86868b] text-[15px] font-medium">Memuat data jadwal...</p>
        </div>

        <div v-else-if="error" class="text-center py-16 bg-[#fff2f2] rounded-[24px] max-w-lg mx-auto">
            <i class="bi bi-exclamation-circle text-3xl text-[#ff3b30] mb-3 block"></i>
            <h3 class="font-semibold text-[#1d1d1f] text-lg mb-1">Terjadi Kesalahan</h3>
            <p class="text-[#ff3b30] text-[15px]">{{ error }}</p>
        </div>

        <div v-else class="space-y-6 md:space-y-8">
            <div v-for="route in groupedRoutes" :key="route.id" class="bg-white rounded-[24px] shadow-[0_4px_24px_rgba(0,0,0,0.03)] border border-[rgba(0,0,0,0.04)] overflow-hidden">
                
                <!-- Route Header -->
                <div class="px-6 py-5 flex items-center gap-4 bg-[#fbfbfd] border-b border-[rgba(0,0,0,0.04)]">
                     <div class="w-12 h-12 rounded-full bg-[#0071e3]/10 text-[#0071e3] flex items-center justify-center shrink-0">
                          <i class="bi bi-signpost-2-fill text-xl"></i>
                     </div>
                     <div>
                        <h3 class="text-xl font-semibold text-[#1d1d1f] tracking-tight">{{ cleanRouteName(route.origin) }} <i class="bi bi-arrow-right text-[#86868b] mx-1"></i> {{ cleanRouteName(route.destination) }}</h3>
                        <p class="text-[13px] text-[#86868b] mt-0.5 font-medium">
                            Tarif Mulai <span class="font-semibold text-[#1d1d1f]">{{ formatRupiah(getRoutePrice(route, 'umum')) }}</span> / pax.
                        </p>
                     </div>
                </div>

                <!-- Schedules Horizontal Gallery -->
                <div v-if="route.displaySchedules && route.displaySchedules.length > 0" class="p-6">
                     <div class="flex gap-4 overflow-x-auto pb-6 snap-x snap-mandatory hide-scroll">
                          
                          <template v-for="sched in route.displaySchedules" :key="sched.time">
                              <div v-for="batch in sched.batches" :key="`${sched.time}-${batch.number}`" 
                                   class="min-w-[260px] bg-[#f5f5f7] rounded-[20px] snap-start flex flex-col group transition-colors">
                                   
                                   <!-- Batch Header -->
                                   <div class="px-5 py-4 flex justify-between items-center border-b border-[rgba(0,0,0,0.05)]">
                                       <div>
                                           <p class="text-[26px] font-semibold text-[#1d1d1f] tracking-tight leading-none">{{ sched.time }}</p>
                                           <p class="text-[11px] font-semibold text-[#86868b] uppercase mt-1">Armada {{ batch.number }}</p>
                                       </div>
                                       <div class="text-right">
                                           <span class="text-lg font-semibold" :class="batch.availableCount > 0 ? 'text-[#34c759]' : 'text-[#ff3b30]'">{{ batch.availableCount }}</span>
                                           <span class="text-[10px] text-[#86868b] block uppercase font-semibold tracking-wide">Kursi</span>
                                       </div>
                                   </div>

                                   <!-- iOS Aesthetic Seat Map -->
                                   <div class="p-5 flex justify-center flex-1 items-center">
                                        <div class="bg-white p-4 rounded-[16px] shadow-sm border border-[rgba(0,0,0,0.03)] w-[180px]">
                                            
                                            <!-- Row 1: CC & Supir -->
                                            <div class="grid grid-cols-3 gap-2.5 mb-3.5">
                                                <div class="w-10 h-10 rounded-full flex items-center justify-center text-[13px] font-semibold border-[1.5px] transition-all" :class="getSeatClass('CC', batch)">CC</div>
                                                <div></div> <!-- Gap -->
                                                <div class="w-10 h-10 rounded-full bg-[#e5e5ea] flex items-center justify-center text-[10px] font-semibold text-[#86868b] pointer-events-none">SPR</div>
                                            </div>
                                            
                                            <!-- Row 2: 1 & 2 -->
                                            <div class="grid grid-cols-3 gap-2.5 mb-3.5">
                                                <div class="w-10 h-10 rounded-[12px] flex items-center justify-center text-[14px] font-semibold border-[1.5px] transition-all" :class="getSeatClass('1', batch)">1</div>
                                                <div></div> <!-- Gap -->
                                                <div class="w-10 h-10 rounded-[12px] flex items-center justify-center text-[14px] font-semibold border-[1.5px] transition-all" :class="getSeatClass('2', batch)">2</div>
                                            </div>

                                            <!-- Row 3: 3 & 4 -->
                                            <div class="grid grid-cols-3 gap-2.5 mb-3.5">
                                                <div class="w-10 h-10 rounded-[12px] flex items-center justify-center text-[14px] font-semibold border-[1.5px] transition-all" :class="getSeatClass('3', batch)">3</div>
                                                <div></div> <!-- Gap -->
                                                <div class="w-10 h-10 rounded-[12px] flex items-center justify-center text-[14px] font-semibold border-[1.5px] transition-all" :class="getSeatClass('4', batch)">4</div>
                                            </div>

                                            <!-- Row 4: 5, 6, 7 -->
                                            <div class="grid grid-cols-3 gap-2.5">
                                                <div class="w-10 h-10 rounded-[12px] flex items-center justify-center text-[14px] font-semibold border-[1.5px] transition-all" :class="getSeatClass('5', batch)">5</div>
                                                <div class="w-10 h-10 rounded-[12px] flex items-center justify-center text-[14px] font-semibold border-[1.5px] transition-all" :class="getSeatClass('6', batch)">6</div>
                                                <div class="w-10 h-10 rounded-[12px] flex items-center justify-center text-[14px] font-semibold border-[1.5px] transition-all" :class="getSeatClass('7', batch)">7</div>
                                            </div>
                                        </div>
                                   </div>
                              </div>
                          </template>

                     </div>
                </div>

                <div v-else class="p-12 text-center text-[#86868b] bg-white">
                     <i class="bi bi-calendar-x text-[40px] mb-3 block opacity-40"></i>
                     <p class="font-medium text-[15px]">Tidak ada jadwal tersedia untuk kriteria ini.</p>
                </div>
            </div>
        </div>
    </div>
  </div>
</template>

<style scoped>
/* Base overrides */
input[type="date"] {
    appearance: none;
    -webkit-appearance: none;
}
input[type="date"]::-webkit-calendar-picker-indicator {
    cursor: pointer;
    opacity: 0.6;
    transition: 0.2s;
}
input[type="date"]::-webkit-calendar-picker-indicator:hover {
    opacity: 1;
}

/* Hide scrollbar for gallery but keep functionality */
.hide-scroll::-webkit-scrollbar {
  display: none;
}
.hide-scroll {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
