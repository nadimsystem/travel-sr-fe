<template>
  <div class="flex h-screen bg-[#f8fafc] text-slate-600 font-sans overflow-hidden selection:bg-gold/20 selection:text-gold-dark">
      <!-- Sidebar for authenticated users -->
      <Sidebar v-if="!$route.meta.guest" :mobileMenuOpen="mobileMenuOpen" @close-menu="mobileMenuOpen = false" />

      <!-- Main Content -->
      <main class="flex-1 flex flex-col min-w-0 bg-[#f8fafc] overflow-hidden relative">
          <!-- Soft Background Pattern -->
          <div class="absolute inset-0 z-0 opacity-40 pointer-events-none" style="background-image: radial-gradient(#cbd5e1 1px, transparent 1px); background-size: 32px 32px;"></div>
          
          <!-- Header -->
          <header v-if="!$route.meta.guest" class="h-24 bg-transparent flex items-center justify-between px-6 lg:px-10 z-10 sticky top-0">
              <div class="flex items-center gap-4">
                  <button @click="mobileMenuOpen = !mobileMenuOpen" class="w-10 h-10 bg-white rounded-xl shadow-sm text-slate-500 lg:hidden flex items-center justify-center active:scale-95 transition-transform">
                      <i class="bi bi-list text-xl"></i>
                  </button>
                  <div>
                      <h1 class="text-2xl lg:text-3xl font-extrabold text-slate-800 tracking-tight capitalize">{{ $route.name || 'Dashboard' }}</h1>
                      <p class="text-xs text-slate-400 font-bold uppercase tracking-widest mt-1 hidden sm:block">{{ currentDate }}</p>
                  </div>
              </div>
              <div class="flex items-center gap-3">
                  <div class="bg-white px-5 py-2.5 rounded-full shadow-sm text-xs font-bold border border-slate-100 flex items-center gap-2 text-slate-600">
                      <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span> <span class="hidden sm:inline">Online</span>
                  </div>
                  <div class="w-10 h-10 bg-white rounded-full border border-slate-100 shadow-sm flex items-center justify-center">
                        <i class="bi bi-bell-fill text-slate-400"></i>
                  </div>
              </div>
          </header>

          <router-view class="flex-1 overflow-y-auto px-6 lg:px-10 pb-10 z-10 custom-scrollbar"></router-view>
      </main>

      <!-- Mobile Overlay -->
      <div v-if="mobileMenuOpen" class="fixed inset-0 bg-slate-900/20 backdrop-blur-sm z-40 lg:hidden" @click="mobileMenuOpen = false"></div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import Sidebar from './components/Sidebar.vue';
import BroadcastFilter from './components/BroadcastFilter.vue';

const mobileMenuOpen = ref(false);

const currentDate = computed(() => {
    return new Date().toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
});
</script>
