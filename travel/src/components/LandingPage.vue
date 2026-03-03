<script setup>
import { ref } from 'vue'

const emit = defineEmits(['navigate'])

const navigate = (page) => {
    emit('navigate', page)
}
</script>

<template>
  <div class="landing-container min-h-[90vh] flex flex-col items-center justify-center p-4">
    
    <div class="text-center w-full max-w-sm mb-12 animate-fade-in-up">
        <!-- Logo Area -->
        <div class="logo-wrapper mb-6 relative inline-block">
            <div class="absolute inset-0 bg-blue-100 rounded-3xl blur-xl opacity-50 transform scale-110"></div>
            <div class="bg-white p-5 rounded-3xl shadow-sm border border-slate-100 relative z-10 w-28 h-28 mx-auto flex items-center justify-center">
                 <img src="/logo.png" alt="Travel Sutan Raya" class="w-20" />
            </div>
        </div>

        <!-- Title Area -->
        <h1 class="text-3xl font-extrabold text-slate-800 mb-3 tracking-tight"> <span class="text-yellow-600">Sutan Raya Travel</span></h1>
        <p class="text-slate-500 text-sm leading-relaxed px-4">Aplikasi resmi pemesanan tiket travel Sutan Raya. Aman, Cepat, dan Nyaman.</p>
    </div>

    <!-- Action Buttons -->
    <div class="w-full max-w-sm space-y-4 animate-fade-in-up" style="animation-delay: 0.1s">
        
        <!-- Primary Action -->
        <button @click="navigate('form')" class="w-full relative group overflow-hidden bg-yellow-600 text-white rounded-2xl p-4 shadow-lg shadow-yellow-600/20 hover:shadow-yellow-600/40 hover:-translate-y-1 transition-all duration-300 transform active:scale-95 text-left border border-yellow-500">
            <div class="absolute inset-0 bg-gradient-to-r from-yellow-600 to-yellow-500 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            <div class="relative z-10 flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-yellow-200 block mb-1">Mulai Perjalanan</span>
                    <span class="text-lg font-bold">Pesan Tiket</span>
                </div>
                <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center backdrop-blur-sm group-hover:bg-white group-hover:text-blue-600 transition-colors">
                    <i class="bi bi-arrow-right text-xl"></i>
                </div>
            </div>
        </button>

        <!-- Secondary Action -->
        <button @click="navigate('list')" class="w-full bg-white border-1.5 border-slate-200 text-slate-700 rounded-2xl p-4 shadow-sm hover:shadow-md hover:border-blue-500 transition-all duration-300 transform active:scale-95 text-left group flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-slate-50 flex items-center justify-center text-slate-400 group-hover:bg-blue-50 group-hover:text-blue-600 transition-colors border border-slate-100">
                <i class="bi bi-map text-xl"></i>
            </div>
            <div>
                <span class="text-[15px] font-bold block mb-0.5 group-hover:text-blue-600 transition-colors">Cek Seat Map</span>
                <span class="text-[11px] text-slate-400 font-medium">Lihat ketersediaan kursi</span>
            </div>
            <i class="bi bi-chevron-right ml-auto text-slate-300 group-hover:text-blue-500 transition-colors"></i>
        </button>

    </div>

  </div>
</template>

<style scoped>
.animate-fade-in-up {
    animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    opacity: 0;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
