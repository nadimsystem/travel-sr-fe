<template>
  <!-- Main Layout Wrapper to emulate "dashboard.php" layout -->
  <div 
    class="flex h-screen bg-slate-50 font-sans overflow-hidden dark:bg-slate-900 transition-colors duration-300"
    @touchstart="handleTouchStart"
    @touchmove="handleTouchMove"
    @touchend="handleTouchEnd"
  >
    <!-- Mobile Overlay Backdrop -->
    <div 
        v-if="isSidebarOpen" 
        @click="isSidebarOpen = false"
        class="fixed inset-0 bg-black/50 z-20 md:hidden backdrop-blur-sm transition-opacity"
    ></div>

    <!-- Sidebar -->
    <aside 
        class="w-64 bg-white dark:bg-slate-800 border-r border-slate-200 dark:border-slate-700 flex-shrink-0 flex flex-col transform fixed md:relative z-30 h-full" 
        :class="{ '-translate-x-full md:translate-x-0': !isSidebarOpen, 'transition-transform duration-300': !isDragging }"
        :style="sidebarStyle"
    >
      
      <!-- H16 Header -->
      <div class="h-16 flex items-center justify-center border-b border-slate-100 dark:border-slate-700 flex-shrink-0">
          <div class="text-xl font-bold text-slate-800 dark:text-white tracking-tight flex items-center gap-2">
              <img src="/logo.webp" alt="Sutan Raya" class="w-8 h-8 object-contain"> Keuangan
          </div>
      </div>

      <!-- Nav -->
      <nav class="flex-1 overflow-y-auto p-3 space-y-1 custom-scrollbar">
        <!-- Section: UTAMA -->
        <div class="text-[10px] font-bold text-slate-400 uppercase px-3 mb-2 mt-2 tracking-wider">Utama</div>
        <router-link to="/dashboard" class="flex items-center px-3 py-2.5 rounded-lg text-sm transition-colors group text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700" active-class="bg-blue-50 dark:bg-slate-700 text-blue-700 dark:text-blue-300 font-bold">
           <i class="bi bi-grid-1x2-fill w-6"></i> Dashboard
        </router-link>
        <router-link to="/booking-history" class="flex items-center px-3 py-2.5 rounded-lg text-sm transition-colors text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700" active-class="bg-blue-50 dark:bg-slate-700 text-blue-700 dark:text-blue-300 font-bold">
            <i class="bi bi-journal-text w-6"></i> Riwayat Booking
        </router-link>

        <!-- Section: LAPORAN -->
        <div class="text-[10px] font-bold text-slate-400 uppercase px-3 mb-2 mt-6 tracking-wider">Laporan & Statistik</div>
        <router-link to="/manifest" class="flex items-center px-3 py-2.5 rounded-lg text-sm transition-colors text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700" active-class="bg-blue-50 dark:bg-slate-700 text-blue-700 dark:text-blue-300 font-bold">
             <i class="bi bi-file-earmark-spreadsheet-fill w-6"></i> Laporan Harian
        </router-link>
        <router-link to="/reports" class="flex items-center px-3 py-2.5 rounded-lg text-sm transition-colors text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700" active-class="bg-blue-50 dark:bg-slate-700 text-blue-700 dark:text-blue-300 font-bold">
            <i class="bi bi-bar-chart-fill w-6"></i> Statistik & Grafik
        </router-link>
        <router-link to="/proofs" class="flex items-center px-3 py-2.5 rounded-lg text-sm transition-colors text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700" active-class="bg-blue-50 dark:bg-slate-700 text-blue-700 dark:text-blue-300 font-bold">
            <i class="bi bi-images w-6"></i> Bukti TF
        </router-link>
        <router-link to="/ktm-proofs" class="flex items-center px-3 py-2.5 rounded-lg text-sm transition-colors text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700" active-class="bg-blue-50 dark:bg-slate-700 text-blue-700 dark:text-blue-300 font-bold">
            <i class="bi bi-person-badge-fill w-6"></i> Bukti KTM
        </router-link>
        <router-link to="/edit-history" class="flex items-center px-3 py-2.5 rounded-lg text-sm transition-colors text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700" active-class="bg-blue-50 dark:bg-slate-700 text-blue-700 dark:text-blue-300 font-bold">
            <i class="bi bi-clock-history w-6"></i> Riwayat Edit
        </router-link>

        <!-- Section: ASET -->
        <div class="text-[10px] font-bold text-slate-400 uppercase px-3 mb-2 mt-6 tracking-wider">Manajemen Aset</div>
        <router-link to="/inventory" class="flex items-center px-3 py-2.5 rounded-lg text-sm transition-colors text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700" active-class="bg-blue-50 dark:bg-slate-700 text-blue-700 dark:text-blue-300 font-bold">
            <i class="bi bi-collection-fill w-6"></i> Armada & Supir
        </router-link>
        <router-link to="/rute" class="flex items-center px-3 py-2.5 rounded-lg text-sm transition-colors text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700" active-class="bg-blue-50 dark:bg-slate-700 text-blue-700 dark:text-blue-300 font-bold">
            <i class="bi bi-map-fill w-6"></i> Kelola Rute
        </router-link>

        <!-- Section: MANAJEMEN BIAYA -->
        <h3 class="px-3 mt-6 mb-2 text-xs font-semibold text-slate-500 uppercase tracking-wider dark:text-slate-400">
            Manajemen Biaya
        </h3>
        
        <div class="space-y-1">
            <router-link to="/trip-history" class="flex items-center p-2 text-slate-600 rounded-lg dark:text-white hover:bg-slate-100 dark:hover:bg-slate-700 group">
                <i class="bi bi-wallet2 text-lg transition duration-75 group-hover:text-blue-600 dark:group-hover:text-white"></i>
                <span class="ml-3">Gaji Supir</span>
            </router-link>

             <div class="flex items-center justify-between px-3 py-2.5 rounded-lg text-sm text-slate-400 dark:text-slate-500 cursor-not-allowed">
                <div class="flex items-center">
                    <i class="bi bi-cash-coin w-6"></i> Uang Jalan
                </div>
                <span class="text-[10px] bg-slate-100 dark:bg-slate-700 px-1.5 py-0.5 rounded text-slate-500">SOON</span>
            </div>

             <div class="flex items-center justify-between px-3 py-2.5 rounded-lg text-sm text-slate-400 dark:text-slate-500 cursor-not-allowed">
                <div class="flex items-center">
                    <i class="bi bi-fuel-pump w-6"></i> Bahan Bakar
                </div>
                <span class="text-[10px] bg-slate-100 dark:bg-slate-700 px-1.5 py-0.5 rounded text-slate-500">SOON</span>
            </div>
        </div>
        <!-- Section: STAFF -->
        <div v-if="userCanAccessUsers" class="mt-2">
            <router-link to="/users" class="flex items-center px-3 py-2.5 rounded-lg text-sm transition-colors text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700" active-class="bg-blue-50 dark:bg-slate-700 text-blue-700 dark:text-blue-300 font-bold">
                <i class="bi bi-people-fill w-6"></i> User / Staff
            </router-link>
        </div>
      </nav>

      <!-- Bottom Pinned -->
        <div class="p-3 border-t border-slate-100 dark:border-slate-700 bg-white dark:bg-slate-800 space-y-1 z-20">
            <div class="text-[10px] font-bold text-slate-400 uppercase px-3 mb-2 tracking-wider">Pengaturan</div>
            
            <button @click="toggleFullscreen" class="flex items-center px-3 py-2.5 rounded-lg text-sm text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors w-full text-left">
                <div class="w-6 flex justify-center"><i class="bi" :class="isFullscreen ? 'bi-fullscreen-exit' : 'bi-arrows-fullscreen'"></i></div>
                <span>Fullscreen</span>
            </button>


            <!-- Dark Mode -->
            <button @click="reloadData" class="flex items-center px-3 py-2.5 rounded-lg text-sm text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors w-full text-left">
                <div class="w-6 flex justify-center"><i class="bi bi-arrow-clockwise font-bold"></i></div>
                <span>Reload Data</span>
            </button>

            <button @click="toggleDarkMode" class="flex items-center px-3 py-2.5 rounded-lg text-sm text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors w-full text-left">
                <div class="w-6 flex justify-center"><i class="bi" :class="isDarkMode ? 'bi-sun-fill text-yellow-500' : 'bi-moon-stars-fill'"></i></div>
                <span>{{ isDarkMode ? 'Mode Terang' : 'Mode Gelap' }}</span>
            </button>

            <a href="#" @click.prevent="logout" class="flex items-center px-3 py-2.5 rounded-lg text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                <i class="bi bi-box-arrow-right w-6"></i> Logout
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col h-full overflow-hidden relative bg-slate-50 dark:bg-slate-900">
        <!-- Header Mobile -->
        <div class="md:hidden h-14 bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between px-4">
            <div class="flex items-center font-bold text-slate-800 dark:text-white">
                <img src="/logo.webp" class="h-6 mr-2"> Keuangan
            </div>
            <button @click="isSidebarOpen = !isSidebarOpen" class="text-slate-500 dark:text-slate-400 focus:outline-none w-9 h-9 rounded-lg bg-slate-100 dark:bg-slate-700 flex items-center justify-center">
                <i class="bi bi-list text-xl"></i>
            </button>
        </div>

        <main class="flex-1 overflow-x-hidden overflow-y-auto w-full relative">
             <!-- Header Desktop (Sticky) -->
             <header class="h-16 bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between px-6 sticky top-0 z-10 transition-colors duration-300">
                 <div>
                     <h2 class="text-lg font-bold text-slate-800 dark:text-white capitalize">{{ currentRouteName }}</h2>
                 </div>
                 <div class="flex items-center gap-4">
                      <div class="text-right hidden sm:block">
                        <div class="text-xs font-bold text-slate-400 uppercase">{{ currentDate }}</div>
                        <div class="text-lg font-mono font-bold text-slate-800 dark:text-sr-gold leading-none">{{ currentTime }}</div>
                     </div>
                 </div>
             </header>

            <div class="p-4 md:p-8 relative">
                 <router-view :key="refreshKey"></router-view>
            </div>
        </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import Swal from 'sweetalert2';

const route = useRoute();
const router = useRouter();
const isSidebarOpen = ref(false);
const isFullscreen = ref(false);
const userCanAccessUsers = ref(true); // Should check role/session
const refreshKey = ref(0);

// Touch Handling State for Draggable Sidebar
const touchStart = ref({ x: 0, y: 0 });
const touchCurrent = ref({ x: 0, y: 0 });
const isDragging = ref(false);
const dragOffset = ref(0);

const handleTouchStart = (e) => {
    const touch = e.touches[0];
    touchStart.value = { x: touch.clientX, y: touch.clientY };
    touchCurrent.value = { x: touch.clientX, y: touch.clientY };
    
    // Check if touch started on a scrollable element (table, etc)
    const target = e.target;
    const isScrollable = target.closest('table, .overflow-auto, .overflow-x-auto, .overflow-y-auto, [style*="overflow"]');
    
    // Only start drag if:
    // 1. Starting from a zone 15px-60px from left edge (not at the very edge, but "tengah dikit") AND not on scrollable element
    // 2. OR if sidebar is already open (allow closing from anywhere)
    if ((touch.clientX > 15 && touch.clientX < 60 && !isScrollable) || isSidebarOpen.value) {
        isDragging.value = true;
    }
};

const handleTouchMove = (e) => {
    if (!isDragging.value) return;
    
    const touch = e.touches[0];
    touchCurrent.value = { x: touch.clientX, y: touch.clientY };
    
    const deltaX = touch.clientX - touchStart.value.x;
    const deltaY = touch.clientY - touchStart.value.y;
    
    // Only track horizontal drags (prevent vertical scroll interference)
    if (Math.abs(deltaX) > Math.abs(deltaY)) {
        e.preventDefault(); // Prevent scroll while dragging
        
        // Calculate sidebar offset
        const sidebarWidth = 256; // w-64 = 16rem = 256px
        
        if (isSidebarOpen.value) {
            // Sidebar is open, allow dragging left to close
            dragOffset.value = Math.max(-sidebarWidth, Math.min(0, deltaX));
        } else {
            // Sidebar is closed, allow dragging right to open
            dragOffset.value = Math.max(0, Math.min(sidebarWidth, deltaX));
        }
    }
};

const handleTouchEnd = (e) => {
    if (!isDragging.value) return;
    
    isDragging.value = false;
    const sidebarWidth = 256;
    const threshold = sidebarWidth * 0.5; // 50% of sidebar width (increased from 30%)
    
    // Decide whether to snap open or closed
    if (isSidebarOpen.value) {
        // Sidebar was open, check if dragged far enough to close
        if (dragOffset.value < -threshold) {
            isSidebarOpen.value = false;
        }
    } else {
        // Sidebar was closed, check if dragged far enough to open
        if (dragOffset.value > threshold) {
            isSidebarOpen.value = true;
        }
    }
    
    // Reset drag offset
    dragOffset.value = 0;
};

const currentRouteName = computed(() => {
    return route.name || '';
});

// Computed style for sidebar during drag
const sidebarStyle = computed(() => {
    if (!isDragging.value && dragOffset.value === 0) {
        return {};
    }
    
    const sidebarWidth = 256;
    let translateX = 0;
    
    if (isSidebarOpen.value) {
        // Sidebar is open, apply drag offset (negative values move left)
        translateX = dragOffset.value;
    } else {
        // Sidebar is closed (starts at -256px), apply drag offset (positive values move right)
        translateX = -sidebarWidth + dragOffset.value;
    }
    
    return {
        transform: `translateX(${translateX}px)`
    };
});

const reloadData = () => {
    refreshKey.value++;
    // Optional: Toast notification
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 1500,
        timerProgressBar: true
    });
    Toast.fire({
        icon: 'success',
        title: 'Data direfresh'
    });
};

// Time Logic
const currentDate = ref('');
const currentTime = ref('');
let timer = null;

const updateTime = () => {
    const now = new Date();
    currentDate.value = now.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
    currentTime.value = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
};

const isDarkMode = ref(localStorage.getItem('theme') === 'dark');

onMounted(() => {
    updateTime();
    timer = setInterval(updateTime, 1000);
    // Check fullscreen state
    document.addEventListener('fullscreenchange', () => {
        isFullscreen.value = !!document.fullscreenElement;
    });
    
    // Init Dark Mode
    if (isDarkMode.value) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
});

const toggleDarkMode = () => {
    isDarkMode.value = !isDarkMode.value;
    if (isDarkMode.value) {
        document.documentElement.classList.add('dark');
        localStorage.setItem('theme', 'dark');
    } else {
        document.documentElement.classList.remove('dark');
        localStorage.setItem('theme', 'light');
    }
};

onUnmounted(() => {
    if (timer) clearInterval(timer);
});

const toggleFullscreen = () => {
    if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen().catch((e) => console.log(e));
    } else {
        document.exitFullscreen();
    }
};

const logout = () => {
    Swal.fire({
        title: 'Keluar?',
        text: "Anda akan mengakhiri sesi ini.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#0f172a',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, Keluar'
    }).then((result) => {
        if (result.isConfirmed) {
            localStorage.removeItem('is_authenticated');
            // Call API Logout if needed
             // fetch('api.php?action=logout'); 
            router.push('/login');
        }
    })
}
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
.dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #475569; }
</style>
