<script setup>
import { ref, computed, onMounted, defineAsyncComponent } from 'vue'
import LandingPage from './components/LandingPage.vue'

// Lazy load heavy components so they don't block initial page load
const BookingList = defineAsyncComponent(() => import('./components/BookingList.vue'))
const BookingForm = defineAsyncComponent(() => import('./components/BookingForm.vue'))
const BookingHistory = defineAsyncComponent(() => import('./components/BookingHistory.vue'))
const FloatingWhatsApp = defineAsyncComponent(() => import('./components/FloatingWhatsApp.vue'))

const currentPage = ref('landing') // 'landing', 'list', 'form', 'history'

const switchPage = (page) => {
  currentPage.value = page
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

const pageTitle = computed(() => {
    switch(currentPage.value) {
        case 'landing': return 'Selamat Datang'
        case 'list': return 'Jadwal & Seat Map'
        case 'form': return 'Pesan Tiket'
        case 'history': return 'Riwayat Perjalanan'
        default: return 'Travel Sutan Raya'
    }
})

// Force re-render of history on fresh booking
const historyKey = ref(0)
const handleBookingCreated = (data) => {
    historyKey.value++ // Force new component mount so onMounted fires again
    switchPage('history')
}

// Handle "Back" navigation for better UX
const goBack = () => {
    if (currentPage.value === 'form' || currentPage.value === 'list' || currentPage.value === 'history') {
        switchPage('landing')
    }
}
</script>

<template>
  <div class="app-container">

    <div class="main-wrapper">
      <!-- Header: Mobile & Desktop -->
      <header class="app-header">
        <div class="container flex items-center justify-between h-full max-w-2xl mx-auto px-4">
          <div class="flex items-center gap-3">
             <button @click="goBack" class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-600 hover:bg-slate-200 transition-colors md:hidden" v-if="currentPage !== 'landing'">
                <i class="bi bi-arrow-left"></i>
             </button>
             <img src="/logo.png" alt="Travel SR" class="h-8 w-auto hidden md:block" />
             <!-- Show Logo on Mobile Landing Page if not covered by other elements, but here we just want navbar persistence -->
             <img src="/logo.png" alt="Travel SR" class="h-8 w-auto md:hidden" v-if="currentPage === 'landing'" /> 

             <span class="font-bold text-lg text-slate-800 tracking-tight">{{ pageTitle }}</span>
          </div>
          
          <!-- Desktop Nav -->
          <nav class="hidden md:flex items-center gap-1 bg-slate-100 p-1 rounded-xl">
             <button @click="switchPage('landing')" 
                class="px-4 py-2 rounded-lg text-sm font-bold transition-all"
                :class="currentPage === 'landing' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-500 hover:text-blue-600'">
                <i class="bi bi-house-door-fill mr-2"></i>Home
             </button>
             <button @click="switchPage('form')"
                class="px-4 py-2 rounded-lg text-sm font-bold transition-all"
                :class="currentPage === 'form' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-500 hover:text-blue-600'">
                <i class="bi bi-ticket-perforated-fill mr-2"></i>Pesan
             </button>
             <button @click="switchPage('history')"
                class="px-4 py-2 rounded-lg text-sm font-bold transition-all"
                :class="currentPage === 'history' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-500 hover:text-blue-600'">
                <i class="bi bi-clock-history mr-2"></i>Riwayat
             </button>
          </nav>
        </div>
      </header>

      <!-- Main Content -->
      <main class="main-content bg-slate-50 min-h-screen" :class="{'pt-0': currentPage === 'landing'}">
        <div class="container mx-auto max-w-2xl px-0 md:px-0 pb-24">
          <Suspense>
            <transition name="page" mode="out-in">
              <LandingPage v-if="currentPage === 'landing'" @navigate="switchPage" />
              <BookingList v-else-if="currentPage === 'list'" />
              <BookingForm v-else-if="currentPage === 'form'" @booking-created="handleBookingCreated" @go-home="switchPage('landing')" />
              <BookingHistory v-else-if="currentPage === 'history'" :key="historyKey" @go-to-booking="switchPage('form')" />
            </transition>
            
            <template #fallback>
              <div class="flex flex-col items-center justify-center min-h-[50vh] text-[#86868b] animate-pulse gap-3">
                 <div class="w-8 h-8 rounded-full border-2 border-current border-t-transparent animate-spin"></div>
                 <p class="font-medium text-sm">Memuat...</p>
              </div>
            </template>
          </Suspense>
        </div>
      </main>

      <!-- Bottom Navigation: Mobile Only -->
      <div class="bottom-nav-container md:hidden" v-if="currentPage !== 'form'">
        <nav class="bottom-nav">
          <!-- Item 1: Home (Back to Landing) -->
          <a href="#" class="nav-item" :class="{ active: currentPage === 'landing' }" @click.prevent="switchPage('landing')">
            <div class="icon-container">
                <i class="bi bi-house-door-fill"></i>
            </div>
            <span>Home</span>
          </a>

          <!-- Item 2: Riwayat -->
          <a href="#" class="nav-item" :class="{ active: currentPage === 'history' }" @click.prevent="switchPage('history')">
            <div class="icon-container">
                <i class="bi" :class="currentPage === 'history' ? 'bi-clock-history' : 'bi-clock'"></i>
            </div>
            <span>Riwayat</span>
          </a>
        </nav>
      </div>
      
      <!-- Global Floating WhatsApp -->
      <FloatingWhatsApp />
    </div>
  </div>
</template>

<style scoped>
.app-container {
  min-height: 100vh;
  background-color: #fcfcfc;
  font-family: -apple-system, BlinkMacSystemFont, 'SF Pro Text', 'Inter', 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
  color: #1d1d1f;
  -webkit-font-smoothing: antialiased;
}

.app-header {
  height: 64px;
  background: rgba(255, 255, 255, 0.72);
  backdrop-filter: saturate(180%) blur(20px);
  -webkit-backdrop-filter: saturate(180%) blur(20px);
  border-bottom: 0.5px solid rgba(0, 0, 0, 0.05);
  position: sticky;
  top: 0;
  z-index: 40;
}

/* Bottom Navigation - Glassmorphism & Floating */
.bottom-nav-container {
  position: fixed;
  bottom: 24px;
  left: 0;
  right: 0;
  display: flex;
  justify-content: center;
  z-index: 50;
  pointer-events: none;
}

/* Apple-Style Liquid Glass Bottom Navigation */
.bottom-nav {
  pointer-events: auto;
  min-width: 200px;
  height: 64px;
  
  /* Extremely sheer base background */
  background: rgba(255, 255, 255, 0.25);
  
  /* High blur and saturation for the vibrant iOS glass feel */
  backdrop-filter: blur(15px) saturate(250%) brightness(1.3);
  -webkit-backdrop-filter: blur(35px) saturate(250%) brightness(1.1);
  
  /* Hairline borders simulating the glass edge */
  border: 0.5px solid rgba(255, 255, 255, 0.4);
  border-top: 0.5px solid rgba(255, 255, 255, 0.8);
  border-bottom: 0.5px solid rgba(0, 0, 0, 0.1);
  
  border-radius: 32px;
  display: flex;
  justify-content: space-evenly;
  align-items: center;
  
  /* Soft, diffused shadow for depth */
  box-shadow: 
    0 20px 40px -10px rgba(0, 0, 0, 0.15),
    inset 0 1px 0 rgba(255, 255, 255, 0.6);
  
  padding: 0 16px;
  gap: 12px;
  position: relative;
  overflow: hidden;
}

.bottom-nav .nav-item {
  display: flex;
  flex-direction: row;
  align-items: center;
  justify-content: center;
  gap: 6px;
  text-decoration: none;
  /* Charcoal gray for high legibility, avoiding harsh solid black */
  color: #333336; 
  transition: all 0.25s cubic-bezier(0.25, 0.1, 0.25, 1);
  position: relative;
  height: 48px;
  padding: 0 18px;
  border-radius: 24px;
}

.bottom-nav .nav-item .icon-container {
    font-size: 1.25rem;
    display: flex;
    align-items: center;
}

.bottom-nav .nav-item.active {
  color: #000000;
  background: rgba(0, 0, 0, 0.05); /* very subtle dark glass tint */
  /* Re-introducing a tiny, sharp white text shadow precisely for dark backgrounds */
  text-shadow: 0 1px 1px rgba(255, 255, 255, 0.8);
}

.bottom-nav .nav-item span {
  font-size: 0.85rem;
  font-weight: 600; /* Apple prefers semibold over black/heavy */
  letter-spacing: -0.3px; /* Signature Apple tight tracking */
}

/* Page Transitions */
.page-enter-active {
  transition: opacity 0.35s cubic-bezier(0.4, 0, 0.2, 1),
              transform 0.35s cubic-bezier(0.4, 0, 0.2, 1);
}

.page-leave-active {
  transition: opacity 0.25s cubic-bezier(0.4, 0, 1, 1),
              transform 0.25s cubic-bezier(0.4, 0, 1, 1);
}

.page-enter-from {
  opacity: 0;
  transform: translateY(16px) scale(0.99);
}

.page-leave-to {
  opacity: 0;
  transform: translateY(-8px) scale(0.99);
}
</style>
