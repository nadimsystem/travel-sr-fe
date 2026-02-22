<script setup>
import { ref, computed, onMounted } from 'vue'
import BookingList from './components/BookingList.vue'
import BookingForm from './components/BookingForm.vue'
import BookingHistory from './components/BookingHistory.vue'
import LandingPage from './components/LandingPage.vue'

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
          <transition name="page" mode="out-in">
            <LandingPage v-if="currentPage === 'landing'" @navigate="switchPage" />
            <BookingList v-else-if="currentPage === 'list'" />
            <BookingForm v-else-if="currentPage === 'form'" @booking-created="handleBookingCreated" @go-home="switchPage('landing')" />
            <BookingHistory v-else-if="currentPage === 'history'" :key="historyKey" @go-to-booking="switchPage('form')" />
          </transition>
        </div>
      </main>

      <!-- Bottom Navigation: Mobile Only -->
      <div class="bottom-nav-container md:hidden">
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
    </div>
  </div>
</template>

<style scoped>
.app-container {
  min-height: 100vh;
  background-color: #f8fafc;
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
}

.app-header {
  height: 64px;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  border-bottom: 1px solid rgba(226, 232, 240, 0.8);
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

.bottom-nav {
  pointer-events: auto;
  min-width: 200px;
  height: 60px;
  background: rgba(30, 41, 59, 0.95); /* slate-800 */
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 30px;
  display: flex;
  justify-content: space-evenly;
  align-items: center;
  box-shadow: 0 10px 40px -10px rgba(0, 0, 0, 0.3);
  padding: 0 20px;
  gap: 20px;
}

.bottom-nav .nav-item {
  display: flex;
  flex-direction: row;
  align-items: center;
  justify-content: center;
  gap: 8px;
  text-decoration: none;
  color: #94a3b8; /* slate-400 */
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  height: 100%;
  padding: 0 12px;
}

.bottom-nav .nav-item .icon-container {
    font-size: 1.2rem;
    display: flex;
    align-items: center;
}

.bottom-nav .nav-item.active {
  color: #ffffff;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 20px;
}

.bottom-nav .nav-item span {
  font-size: 0.8rem;
  font-weight: 600;
  letter-spacing: 0.3px;
}

/* Page Transitions */
.page-enter-active,
.page-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}

.page-enter-from {
  opacity: 0;
  transform: translateY(10px);
}

.page-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
</style>
