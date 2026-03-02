<template>
  <div class="animate-fade-in h-full flex flex-col gap-6">
      
      <!-- TABS -->
      <div class="flex gap-2 border-b border-slate-100 pb-1">
          <button @click="tab = 'monitor'" class="px-6 py-2 rounded-t-xl font-bold text-sm transition-all relative overflow-hidden" :class="tab==='monitor' ? 'bg-white text-slate-800 shadow-sm border border-b-0 border-slate-100' : 'text-slate-400 hover:bg-slate-50'">
              <i class="bi bi-activity mr-2"></i> Monitor Antrian
          </button>
          <button @click="tab = 'filter'" class="px-6 py-2 rounded-t-xl font-bold text-sm transition-all relative overflow-hidden" :class="tab==='filter' ? 'bg-white text-slate-800 shadow-sm border border-b-0 border-slate-100' : 'text-slate-400 hover:bg-slate-50'">
              <i class="bi bi-funnel mr-2"></i> Cari Target & Filter
          </button>
      </div>

      <!-- TAB: MONITOR (Original Content) -->
      <div v-show="tab === 'monitor'" class="flex flex-col gap-6 h-full min-h-0">
          <!-- Stats Header -->
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
              <div class="bg-slate-900 text-white p-5 rounded-3xl shadow-lg relative overflow-hidden">
                  <i class="bi bi-people absolute -right-2 -bottom-2 text-6xl opacity-10"></i>
                  <div class="text-3xl font-black">{{ broadcastTargets.length }}</div>
                  <div class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Target Audience</div>
              </div>
              <div class="bg-white border border-slate-100 p-5 rounded-3xl shadow-sm">
                  <div class="text-3xl font-black text-gold">{{ broadcastTargets.reduce((a,b)=>a+(Number(b.totalTrips)||0),0) }}</div>
                  <div class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Total Trips</div>
              </div>
          </div>

          <div class="flex flex-col lg:flex-row gap-6 flex-1 min-h-0">
              <!-- Filter Panel -->
              <div class="w-full lg:w-72 flex-none space-y-6 overflow-y-auto custom-scrollbar pr-2 pb-20">
                  
                   <!-- List Preview -->
                  <div class="flex-1 bg-white rounded-[2.5rem] shadow-sm border border-slate-100 flex flex-col overflow-hidden relative min-h-[300px]">
                      <div class="absolute top-0 left-0 w-full h-10 bg-gradient-to-b from-white to-transparent pointer-events-none z-10"></div>
                      <div class="overflow-x-auto flex-1 custom-scrollbar p-4">
                          <div v-if="broadcastTargets.length === 0" class="text-center py-10 text-slate-400 text-sm">
                              Belum ada target dipilih.
                              <button @click="tab = 'filter'" class="block mx-auto mt-2 text-gold font-bold hover:underline">Cari Target</button>
                          </div>
                          <div v-else class="space-y-3">
                              <div v-for="c in broadcastTargets" :key="c.phone" class="flex items-center gap-3 p-3 bg-slate-50 rounded-2xl">
                                  <div class="w-8 h-8 rounded-full bg-white flex items-center justify-center font-bold text-xs shadow-sm">{{ getInitials(c.name) }}</div>
                                  <div class="flex-1 min-w-0">
                                      <div class="truncate text-xs font-bold text-slate-700">{{ c.name }}</div>
                                      <div class="text-[10px] text-slate-400">{{ normalizePhone(c.phone) }}</div>
                                  </div>
                                  <button @click="removeTarget(c)" class="text-slate-300 hover:text-red-400"><i class="bi bi-x-circle-fill"></i></button>
                              </div>
                          </div>
                      </div>
                      <div class="p-4 border-t border-slate-50 bg-slate-50">
                           <button @click="openBroadcastModal" :disabled="broadcastTargets.length === 0" class="w-full py-3 bg-slate-900 text-white rounded-xl font-bold text-sm disabled:opacity-50 hover:bg-slate-800 transition-all shadow-lg">
                              Buat Pesan Broadcast
                           </button>
                      </div>
                  </div>
              </div>

              <!-- Queue Monitor -->
              <div class="flex-1 bg-white rounded-[2.5rem] p-6 shadow-sm border border-slate-100 flex flex-col gap-6">
                  <h3 class="font-bold text-slate-800 text-lg">Antrian & Pengiriman Otomatis</h3>
                  
                  <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                       <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 text-center">
                          <div class="text-2xl font-black text-slate-800">{{ queueStats.stats.pending || 0 }}</div>
                          <div class="text-[10px] uppercase font-bold text-slate-400">Pending</div>
                       </div>
                       <div class="bg-emerald-50 p-4 rounded-xl border border-emerald-100 text-center">
                          <div class="text-2xl font-black text-emerald-500">{{ queueStats.stats.sent || 0 }}</div>
                          <div class="text-[10px] uppercase font-bold text-emerald-600">Terkirim</div>
                       </div>
                        <div class="p-4 rounded-xl border flex items-center justify-between cursor-pointer hover:shadow-lg transition-all" :class="isAutoSending ? 'bg-red-50 border-red-100' : 'bg-slate-900 text-white border-slate-900'" @click="toggleAutoSend">
                          <div>
                              <h3 class="font-bold text-sm" :class="isAutoSending ? 'text-red-500' : 'text-white'">Auto Sender</h3>
                              <p class="text-[10px] opacity-70 mt-0.5">{{ isAutoSending ? 'Running...' : 'Click to Start' }}</p>
                          </div>
                          <div class="text-2xl"><i class="bi" :class="isAutoSending ? 'bi-stop-fill' : 'bi-play-fill'"></i></div>
                      </div>
                  </div>

                   <!-- QUEUE LIST TABLE -->
                   <div class="flex-1 bg-slate-50 rounded-2xl p-4 border border-slate-100 flex flex-col overflow-hidden min-h-[200px]">
                        <div class="flex justify-between items-center mb-3">
                            <h4 class="font-bold text-slate-700 text-sm">Daftar Antrian Pending ({{ queueList.length }})</h4>
                             <button @click="deleteFromQueue(null, true)" v-if="queueList.length > 0" class="text-[10px] font-bold text-red-500 hover:bg-red-50 px-2 py-1 rounded transition-colors">
                                <i class="bi bi-trash mr-1"></i> Cancel Semua
                            </button>
                        </div>
                        <div class="flex-1 overflow-y-auto custom-scrollbar">
                            <table class="w-full text-left text-xs">
                                <thead class="text-[10px] uppercase font-bold text-slate-400 border-b border-slate-200">
                                    <tr>
                                        <th class="pb-2">Target</th>
                                        <th class="pb-2">Pesan</th>
                                        <th class="pb-2 text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <tr v-for="item in queueList" :key="item.id">
                                        <td class="py-2 pr-2">
                                            <div class="font-bold text-slate-700">{{ item.name }}</div>
                                            <div class="text-[10px] text-slate-400 font-mono">{{ normalizePhone(item.phone) }}</div>
                                        </td>
                                        <td class="py-2 pr-2">
                                            <div class="text-slate-500 line-clamp-1" :title="item.message">{{ item.message }}</div>
                                        </td>
                                        <td class="py-2 text-right">
                                             <button @click="deleteFromQueue(item.id)" class="text-slate-300 hover:text-red-500 transition-colors">
                                                <i class="bi bi-x-circle-fill"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="queueList.length === 0">
                                        <td colspan="3" class="py-4 text-center text-slate-400 italic">Tidak ada antrian pending</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                   </div>

                    <!-- Terminal / Log View -->
                  <div class="flex-1 bg-slate-900 rounded-2xl p-4 shadow-inner flex flex-col overflow-hidden relative font-mono text-xs border-4 border-slate-800">
                      <div class="flex justify-between items-center mb-2 pb-2 border-b border-slate-800">
                          <div class="text-slate-400 font-bold flex items-center gap-2"><i class="bi bi-terminal-fill"></i> LOG</div>
                      </div>
                      <div class="flex-1 overflow-y-auto custom-scrollbar space-y-1" id="consoleLog">
                          <div v-for="(log, i) in autoSendLogs" :key="i" class="flex gap-2">
                              <span class="text-slate-600">[{{ log.time }}]</span>
                              <span :class="log.type === 'error' ? 'text-red-400' : (log.type === 'success' ? 'text-emerald-400' : 'text-slate-300')">{{ log.msg }}</span>
                          </div>
                      </div>
                  </div>
              </div>
          </div>
      </div>

      <!-- TAB: FILTER -->
      <div v-show="tab === 'filter'" class="flex-1 min-h-0">
          <BroadcastFilter @add-broadcast="addFromFilter" />
      </div>

       <!-- BROADCAST MODAL -->
      <div v-if="showBroadcastModal" class="fixed inset-0 z-[70] flex items-center justify-center p-4">
          <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showBroadcastModal = false"></div>
          <div class="bg-white w-full max-w-2xl rounded-[2rem] shadow-2xl relative z-10 overflow-hidden flex flex-col max-h-[90vh]">
              <!-- Header -->
              <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                  <div>
                      <h3 class="text-xl font-extrabold text-slate-800">Broadcast Whatsapp</h3>
                      <p class="text-slate-400 text-xs font-bold uppercase tracking-widest mt-1">
                          Kirim ke {{ broadcastTargets.length }} kontak
                      </p>
                  </div>
                  <button @click="showBroadcastModal = false" class="w-10 h-10 rounded-xl bg-slate-100 text-slate-400 hover:bg-slate-200 flex items-center justify-center transition-colors"><i class="bi bi-x-lg"></i></button>
              </div>

              <div class="p-8 overflow-y-auto">
                  <label class="block text-sm font-bold text-slate-700 mb-3">Pesan Broadcast</label>
                  <div class="relative">
                      <textarea id="broadcastMsgInput" v-model="broadcastMessage" rows="6" class="w-full bg-slate-50 border border-slate-200 rounded-2xl p-5 text-slate-700 font-medium focus:ring-2 focus:ring-gold/50 focus:border-gold transition-all resize-none mb-3" placeholder="Tulis pesan anda disini..."></textarea>
                      <div class="flex gap-2 mb-6 overflow-x-auto pb-2">
                          <button @click="insertVariable('{{name}}')" class="px-3 py-1.5 bg-blue-50 text-blue-600 rounded-lg text-xs font-bold hover:bg-blue-100 transition-colors whitespace-nowrap">+ Nama</button>
                          <button @click="insertVariable('{{phone}}')" class="px-3 py-1.5 bg-emerald-50 text-emerald-600 rounded-lg text-xs font-bold hover:bg-emerald-100 transition-colors whitespace-nowrap">+ Nomor HP</button>
                      </div>
                  </div>
              </div>

              <!-- Footer -->
              <div class="p-6 border-t border-slate-100 bg-slate-50 flex justify-end gap-3">
                  <button @click="startBroadcast" class="px-8 py-3 bg-slate-900 text-white rounded-xl font-bold text-sm hover:bg-slate-800 transition-all shadow-lg shadow-slate-200">
                      Simpan ke Antrian <i class="bi bi-arrow-right ml-2"></i>
                  </button>
              </div>
          </div>
      </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import api from '../api/axios';
import BroadcastFilter from '../components/BroadcastFilter.vue'; // We need this import

const tab = ref('monitor');
const broadcastTargets = ref([]);
const broadcastMessage = ref('Halo Kak {{name}},\n\nTerima kasih telah menjadi pelanggan setia Sutan Raya Travel.\n\nHubungi kami untuk info lebih lanjut.');
const showBroadcastModal = ref(false);
const queueStats = ref({ stats: {}, items: [] });
const queueList = ref([]); // State for queue list
const isAutoSending = ref(false);
const autoSendLogs = ref([]);
let processTimer = null;

// ...

const fetchQueueList = async () => {
    try {
        const res = await api.get('/queue/list');
        if (res.data && res.data.queue) {
            queueList.value = res.data.queue;
        } else {
            queueList.value = [];
        }
    } catch(e) {}
};

const deleteFromQueue = async (id, all = false) => {
    if (!confirm(all ? 'Hapus SEMUA antrian pending?' : 'Hapus item ini dari antrian?')) return;
    try {
        await api.post('/queue/delete', { id, delete_all: all });
        fetchQueueList();
        fetchQueueStats();
    } catch (e) {
        alert('Gagal menghapus antrian');
    }
};

const fetchQueueStats = async () => {
    try {
        const res = await api.get('/queue/stats');
        if(res.data) queueStats.value = res.data;
        fetchQueueList(); // Refresh list too
    } catch(e) {}
};
    const stored = localStorage.getItem('broadcast_targets');
    if(stored) {
        broadcastTargets.value = JSON.parse(stored);
    }
};

const addFromFilter = (selected) => {
    // Add unique targets
    const currentPhones = new Set(broadcastTargets.value.map(t => t.phone));
    let addedCount = 0;
    selected.forEach(c => {
        if(!currentPhones.has(c.phone)) {
            broadcastTargets.value.push({ name: c.name, phone: c.phone, totalTrips: c.totalTrips });
            currentPhones.add(c.phone);
            addedCount++;
        }
    });
    localStorage.setItem('broadcast_targets', JSON.stringify(broadcastTargets.value));
    tab.value = 'monitor';
    alert(`Berhasil menambahkan ${addedCount} kontak ke list broadcast.`);
};

const removeTarget = (c) => {
    broadcastTargets.value = broadcastTargets.value.filter(t => t.phone !== c.phone);
    localStorage.setItem('broadcast_targets', JSON.stringify(broadcastTargets.value));
};

const getInitials = (name) => name ? name.substring(0, 2).toUpperCase() : '?';
const normalizePhone = (p) => p || '-';

const insertVariable = (v) => {
    broadcastMessage.value += v;
};

const openBroadcastModal = () => {
    showBroadcastModal.value = true;
};

const startBroadcast = async () => {
    try {
        const res = await api.post('/queue/add', {
            targets: broadcastTargets.value.map(t => ({  name: t.name, phone: t.phone })),
            message: broadcastMessage.value
        });
        if(res.data.status === 'success') {
            alert(`Berhasil menambahkan ${res.data.count} antrian!`);
            showBroadcastModal.value = false;
            broadcastTargets.value = [];
            localStorage.removeItem('broadcast_targets');
            fetchQueueStats();
            fetchQueueList();
        }
    } catch (e) {
        alert('Gagal menambahkan antrian');
    }
};

const fetchQueueStats = async () => {
    try {
        const res = await api.get('/queue/stats');
        if(res.data) queueStats.value = res.data;
    } catch(e) {}
};

const toggleAutoSend = () => {
    isAutoSending.value = !isAutoSending.value;
    if(isAutoSending.value) {
        processQueue();
    } else {
        clearTimeout(processTimer);
    }
};

const processQueue = async () => {
    if(!isAutoSending.value) return;

    try {
        // log('Checking queue...');
        const res = await api.get('/queue/next');
        if (res.data.status === 'found') {
            const item = res.data.item;
            log(`Sending to ${item.name} (${item.phone})...`, 'info');
            
            // Simulate sending (In real world, call WA Gateway API here)
            await new Promise(r => setTimeout(r, 2000)); 
            
            // Mark Sent
            await api.post('/queue/update', { id: item.id, status: 'sent' });
            log(`Success: Message sent to ${item.name}`, 'success');
            
            fetchQueueStats();
            processTimer = setTimeout(processQueue, 1000); // Next item immediately
        } else {
            // log('Queue empty. Waiting...', 'info');
            fetchQueueStats(); // Refresh stats just in case
            processTimer = setTimeout(processQueue, 5000); // Wait bit longer if empty
        }
    } catch(e) {
        log('Error processing queue', 'error');
        processTimer = setTimeout(processQueue, 5000);
    }
};

const log = (msg, type='info') => {
    const time = new Date().toLocaleTimeString();
    autoSendLogs.value.unshift({ time, msg, type });
    if(autoSendLogs.value.length > 50) autoSendLogs.value.pop();
};

onMounted(() => {
    loadTargets();
    fetchQueueStats();
    fetchQueueList();
    // Poll stats occasionally if not auto sending
    setInterval(() => {
        if(!isAutoSending.value) {
            fetchQueueStats();
            fetchQueueList();
        }
    }, 10000);
});

onUnmounted(() => {
    clearTimeout(processTimer);
});
</script>
