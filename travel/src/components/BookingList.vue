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

const formatDate = (dateString) => {
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }
    return new Date(dateString).toLocaleDateString('id-ID', options).toUpperCase()
}

// Seat Map Status Data Structure
// Instead of fetching all seats mapped uniquely, the API returns booked seats per route/date/time.
// We manage the API calls proactively based on the selected filters.
const schedulesData = ref({}) // Dictionary mapping routeId -> { time: { batches: { 1: [seats], 2: [seats] } } }
const schedulesLoading = ref(false)

const loadSchedulesForFilter = async () => {
    if(!filters.value.date) return
    schedulesLoading.value = true
    error.value = null
    
    try {
        const url = `api/?action=get_daily_booked_seats&date=${filters.value.date}`
        const res = await axios.get(url)
        
        if (res.data.status === 'success') {
             // 1. Group bookings by routeId and time
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
             
             // 2. Apply Virtual Batch Distribution (matching admin view_bookings.js exactly)
             for (const rId in bookingsByRouteAndTime) {
                 parsedData[rId] = {}
                 for (const t in bookingsByRouteAndTime[rId]) {
                     parsedData[rId][t] = { batches: {} }
                     const slotBookings = bookingsByRouteAndTime[rId][t]
                     
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
                     
                     // Init batch 1
                     if (!batchesMap.has(1)) batchesMap.set(1, []);
                     let currentBatchForUnassigned = 1;
                     
                     // Distribute unassigned using same logic as admin (sum of seatNumbers per booking)
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
                     
                     // Convert batchesMap to parsedData seat objects
                     batchesMap.forEach((batchBookings, bNum) => {
                         let batchPaxCount = 0;
                         const batchSeats = [];
                         batchBookings.forEach(booking => {
                             const seatsRaw = booking.seatNumbers || '';
                             const seatParts = seatsRaw.split(',').filter(s => s.trim());
                             
                             // Count passengers (if no seat given, count as 1 pax fallback)
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
                         
                         // Attach true paxCount to the array object
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

// Group schedules by time, inject seat data
const groupedRoutes = computed(() => {
    return filteredRoutes.value.map(route => {
        // Map available times to schedule objects
        let schedules = []
        if (route.schedules && Array.isArray(route.schedules)) {
             
             let timesToDisplay = parseSchedules(route.schedules)
             if (filters.value.time) {
                 timesToDisplay = timesToDisplay.filter(t => t === filters.value.time)
             }

             schedules = timesToDisplay.map(timeString => {
                  // Normalize format: replace . with : (e.g. 05.00 -> 05:00) so it matches Db bookings
                  timeString = timeString.replace('.', ':')
                  
                  // Look up batches data for this route/date/time from schedulesData
                  const routeData = schedulesData.value[route.id] || {}
                  const timeData = routeData[timeString] || {}
                  const batchesObj = timeData.batches || {}

                      // Always show 3 armadas
                  const batchesArray = [1, 2, 3].map(batchNum => {
                      let occupiedObjects = batchesObj[batchNum] || []
                      let truePaxCount = occupiedObjects.paxCount || 0
                      
                      // Filter out pending string anomalies
                      occupiedObjects = occupiedObjects.filter(obj => obj.seat.toLowerCase() !== 'pending' && obj.seat.toLowerCase() !== 'menunggu')
                      
                      // Deduplicate objects by seat number for VISUAL map
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
    // Run requests in parallel to reduce perceived overall latency
    await Promise.all([
        fetchRoutes(),
        loadSchedulesForFilter()
    ])
    loading.value = false
})

const cleanRouteName = (name) => name ? name.replace(/\s*\(Normal\)/i, '').trim() : ''

const getSeatIconClass = (seatNum, batch) => {
    const isOccupied = batch.occupiedSeats.some(s => s.seat === String(seatNum))
    if (isOccupied) return 'bi-person-fill text-slate-400'
    return 'bi-square text-emerald-500' // Available
}

const getSeatClass = (seatNum, batch) => {
    const seatObj = batch.occupiedSeats.find(s => s.seat === String(seatNum))
    
    if (seatObj) {
        // Pending/Waiting
        if (seatObj.status === 'Review' || seatObj.status === 'Menunggu Validasi' || seatObj.bookingStatus === 'Antrian') {
            return 'bg-orange-500 border-orange-600 text-white shadow-sm'
        }
        // Occupied Confirmed
        return 'bg-slate-900 border-slate-800 text-white shadow-sm'
    }
    
    // Empty
    return 'bg-white border-slate-300 text-slate-400'
}
</script>

<template>
  <div class="min-h-screen bg-slate-50 font-sans pb-24">
    
    <!-- Top Filter & Header -->
    <div class="bg-white px-5 pt-8 pb-6 shadow-sm border-b border-slate-200">
        <h2 class="text-2xl font-black text-slate-800 tracking-tight mb-6">Jadwal & Seat Map</h2>
        
        <div class="space-y-4 max-w-lg mx-auto bg-slate-50 p-4 rounded-2xl border border-slate-200 shadow-sm">
             
             <!-- Date -->
             <div class="relative group">
                <input type="date" v-model="filters.date" class="w-full pl-11 p-3.5 bg-white border border-slate-200 rounded-xl text-sm outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all font-semibold text-slate-800">
                <i class="bi bi-calendar-event absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-blue-500"></i>
             </div>

             <!-- Route -->
             <div class="relative group">
                <select v-model="filters.routeId" class="w-full pl-11 p-3.5 bg-white border border-slate-200 rounded-xl text-sm outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all font-semibold appearance-none text-slate-800">
                    <option value="">Semua Rute</option>
                    <option v-for="route in routes" :key="route.id" :value="route.id">
                        {{ cleanRouteName(route.origin) }} ➔ {{ cleanRouteName(route.destination) }}
                    </option>
                </select>
                <i class="bi bi-geo-alt absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-blue-500"></i>
             </div>

             <!-- Time -->
             <div class="relative group">
                <select v-model="filters.time" :disabled="!filters.routeId" class="w-full pl-11 p-3.5 bg-white border border-slate-200 rounded-xl text-sm outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all font-semibold appearance-none text-slate-800 disabled:opacity-50">
                    <option value="">Semua Jam</option>
                    <option v-for="t in getAvailableTimesForRoute" :key="t" :value="typeof t === 'string' ? t : t.time">{{ typeof t === 'string' ? t : t.time }}</option>
                </select>
                <i class="bi bi-clock absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-blue-500"></i>
             </div>
        </div>
    </div>

    <!-- Active Date Banner -->
    <div class="bg-blue-600 text-white px-5 py-3 shadow-md flex justify-between items-center sticky top-0 z-10 lg:static">
        <span class="text-sm font-bold tracking-widest">{{ formatDate(filters.date) }}</span>
        <button @click="loadSchedulesForFilter" class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center hover:bg-white/30 transition-colors" title="Refresh Jadwal">
            <i class="bi bi-arrow-clockwise" :class="{'animate-spin': schedulesLoading}"></i>
        </button>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 mt-6">
        
        <div v-if="loading" class="text-center py-20">
             <div class="w-10 h-10 border-4 border-slate-200 border-t-blue-600 rounded-full animate-spin mx-auto mb-4"></div>
             <p class="text-slate-500 text-sm font-medium">Memuat data jadwal...</p>
        </div>

        <div v-else-if="error" class="text-center py-20 bg-red-50 rounded-2xl border border-red-100 max-w-lg mx-auto">
            <i class="bi bi-exclamation-triangle text-4xl text-red-500 mb-2"></i>
            <h3 class="font-bold text-red-700 text-lg mb-1">Terjadi Kesalahan</h3>
            <p class="text-red-500 text-sm">{{ error }}</p>
        </div>

        <div v-else class="space-y-8">
            <div v-for="route in groupedRoutes" :key="route.id" class="bg-white rounded-[1.5rem] shadow-sm border border-slate-200 overflow-hidden">
                
                <!-- Route Header -->
                <div class="bg-slate-50 border-b border-slate-100 p-5 flex items-center gap-4">
                     <div class="w-12 h-12 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                          <i class="bi bi-send-fill text-xl"></i>
                     </div>
                     <div>
                         <h3 class="text-lg font-black text-slate-800 tracking-tight">{{ cleanRouteName(route.origin) }} <i class="bi bi-arrow-right text-slate-400 mx-1"></i> {{ cleanRouteName(route.destination) }}</h3>
                         <p class="text-xs text-slate-500 mt-0.5">Tarif Umum: <span class="font-bold text-blue-600">{{ formatRupiah(route.price_umum || route.price || route.harga || route.tarif) }}</span> / kursi &nbsp;·&nbsp; Pelajar: <span class="font-bold text-green-600">{{ formatRupiah(route.price_pelajar) }}</span></p>
                     </div>
                </div>

                <!-- Schedules Horizontal List -->
                <div v-if="route.displaySchedules && route.displaySchedules.length > 0" class="p-5">
                     <div class="flex gap-4 overflow-x-auto pb-4 snap-x snap-mandatory">
                          
                          <template v-for="sched in route.displaySchedules" :key="sched.time">
                              <div v-for="batch in sched.batches" :key="`${sched.time}-${batch.number}`" 
                                   class="min-w-[240px] bg-slate-50 rounded-2xl border border-slate-200 snap-start flex flex-col overflow-hidden group hover:border-blue-300 transition-colors">
                                   
                                   <!-- Batch Header -->
                                   <div class="p-4 border-b border-slate-200 bg-white flex justify-between items-center">
                                       <div>
                                           <p class="text-2xl font-black text-slate-800 tracking-tight leading-none">{{ sched.time }}</p>
                                           <p class="text-[10px] uppercase font-bold text-slate-400 mt-1">Armada {{ batch.number }}</p>
                                       </div>
                                       <div class="text-right">
                                           <span class="text-sm font-black" :class="batch.availableCount > 0 ? 'text-blue-600' : 'text-red-500'">{{ batch.availableCount }}</span>
                                           <span class="text-[10px] text-slate-500 block uppercase font-bold tracking-widest mt-0.5">Sisa Kursi</span>
                                       </div>
                                   </div>

                                   <!-- Miniature Seat Map -->
                                   <div class="p-4 flex justify-center bg-[#f8fafc] flex-1">
                                        <div class="bg-slate-100 p-4 rounded-xl border border-slate-200 relative w-[180px]">
                                            
                                            <!-- Row 1: CC & Supir -->
                                            <div class="grid grid-cols-3 gap-3 mb-4">
                                                <div class="w-10 h-10 rounded-lg flex items-center justify-center text-sm font-bold border transition-all select-none" :class="getSeatClass('CC', batch)">CC</div>
                                                <div></div> <!-- Gap -->
                                                <div class="w-10 h-10 rounded-lg bg-slate-200 flex items-center justify-center text-[10px] font-bold text-slate-500 border border-slate-300 select-none">SPR</div>
                                            </div>
                                            
                                            <!-- Row 2: 1 & 2 -->
                                            <div class="grid grid-cols-3 gap-3 mb-4">
                                                <div class="w-10 h-10 rounded-lg flex items-center justify-center text-sm font-bold border transition-all select-none" :class="getSeatClass('1', batch)">1</div>
                                                <div></div> <!-- Gap -->
                                                <div class="w-10 h-10 rounded-lg flex items-center justify-center text-sm font-bold border transition-all select-none" :class="getSeatClass('2', batch)">2</div>
                                            </div>

                                            <!-- Row 3: 3 & 4 -->
                                            <div class="grid grid-cols-3 gap-3 mb-4">
                                                <div class="w-10 h-10 rounded-lg flex items-center justify-center text-sm font-bold border transition-all select-none" :class="getSeatClass('3', batch)">3</div>
                                                <div></div> <!-- Gap -->
                                                <div class="w-10 h-10 rounded-lg flex items-center justify-center text-sm font-bold border transition-all select-none" :class="getSeatClass('4', batch)">4</div>
                                            </div>

                                            <!-- Row 4: 5, 6, 7 -->
                                            <div class="grid grid-cols-3 gap-3">
                                                <div class="w-10 h-10 rounded-lg flex items-center justify-center text-sm font-bold border transition-all select-none" :class="getSeatClass('5', batch)">5</div>
                                                <div class="w-10 h-10 rounded-lg flex items-center justify-center text-sm font-bold border transition-all select-none" :class="getSeatClass('6', batch)">6</div>
                                                <div class="w-10 h-10 rounded-lg flex items-center justify-center text-sm font-bold border transition-all select-none" :class="getSeatClass('7', batch)">7</div>
                                            </div>
                                        </div>
                                   </div>
                              </div>
                          </template>

                     </div>
                </div>

                <div v-else class="p-10 text-center text-slate-400 font-medium bg-slate-50">
                     <i class="bi bi-calendar-x text-4xl mb-2 block opacity-50"></i>
                     Tidak ada jadwal tiket untuk rute ini pada jam yang dipilih.
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
</style>
