<template>
  <div class="min-h-screen bg-gray-50 flex flex-col items-center justify-center p-6 selection:bg-red-100 selection:text-red-900 font-sans">
    
    <!-- Card Container -->
    <div class="w-full max-w-[480px] bg-white rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] overflow-hidden border border-gray-100/50 transition-all duration-300 hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)]">
      
      <!-- Minimalist Header -->
      <div class="pt-10 pb-6 px-8 text-center bg-gradient-to-b from-white to-gray-50/50">
        <div class="w-16 h-16 bg-white rounded-2xl shadow-sm border border-gray-100 flex items-center justify-center mx-auto mb-6 transform transition-transform hover:scale-105 duration-300">
          <img :src="logo" alt="Sutan Raya Logo" class="w-10 h-10 object-contain">
        </div>
        <h1 class="text-gray-900 text-2xl font-bold tracking-tight mb-2">Sutan Raya Travel</h1>
        <p class="text-gray-500 text-sm font-medium leading-relaxed max-w-sm mx-auto">
          "Bantu kami meningkatkan pelayanan dengan kritik dan saran Anda. Hadiah menarik menanti sebagai bentuk apresiasi kami."
        </p>
      </div>

      <!-- Form -->
      <form @submit.prevent="submitReview" class="px-8 pb-10 space-y-6">
        
        <!-- Admin Contact Dropdown -->
        <div class="space-y-1.5 top-0 w-full z-20">
          <label class="block text-xs font-semibold uppercase tracking-wider text-gray-400 ml-1">Kontak mana yang barusan dihubungi?</label>
          <div class="relative group">
            <button 
              type="button"
              @click="isDropdownOpen = !isDropdownOpen"
              class="w-full px-5 py-3.5 bg-gray-50 border border-transparent rounded-xl text-left text-sm font-medium focus:bg-white focus:border-gray-200 focus:ring-4 focus:ring-gray-100 focus:outline-none transition-all duration-300 flex justify-between items-center group-hover:bg-gray-50/80"
              :class="{'text-gray-900': form.admin_contact, 'text-gray-400': !form.admin_contact}"
            >
              <span class="truncate">{{ form.admin_contact || 'Pilih Admin' }}</span>
              <svg 
                xmlns="http://www.w3.org/2000/svg" 
                class="h-5 w-5 text-gray-400 transition-transform duration-300"
                :class="{'rotate-180': isDropdownOpen}"
                fill="none" 
                viewBox="0 0 24 24" 
                stroke="currentColor" 
                stroke-width="2"
              >
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
              </svg>
            </button>
            
            <!-- Dropdown Menu -->
            <transition
              enter-active-class="transition ease-out duration-200"
              enter-from-class="opacity-0 translate-y-2"
              enter-to-class="opacity-100 translate-y-0"
              leave-active-class="transition ease-in duration-150"
              leave-from-class="opacity-100 translate-y-0"
              leave-to-class="opacity-0 translate-y-2"
            >
              <div v-show="isDropdownOpen" class="absolute left-0 right-0 mt-2 bg-white rounded-xl shadow-xl border border-gray-100 z-30 overflow-hidden">
                <ul class="py-2">
                  <li v-for="admin in adminOptions" :key="admin">
                    <button 
                      type="button"
                      @click="selectAdmin(admin)"
                      class="w-full text-left px-5 py-3 text-sm font-medium text-gray-700 hover:bg-red-50 hover:text-red-700 transition-colors duration-200 flex items-center justify-between"
                    >
                      <span>{{ admin }}</span>
                      <svg v-if="form.admin_contact === admin" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-red-600" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                      </svg>
                    </button>
                  </li>
                </ul>
              </div>
            </transition>
          </div>
        </div>

        <!-- Name Input -->
        <div class="space-y-1.5">
          <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-gray-400 ml-1">Nama Lengkap</label>
          <div class="relative group">
            <input 
              v-model="form.name"
              id="name" 
              type="text" 
              required 
              placeholder="Masukkan nama Anda"
              class="w-full px-5 py-3.5 bg-gray-50 border border-transparent rounded-xl text-gray-900 text-sm font-medium placeholder-gray-400 focus:bg-white focus:border-gray-200 focus:ring-4 focus:ring-gray-100 focus:outline-none transition-all duration-300 group-hover:bg-gray-50/80"
            >
          </div>
        </div>

        <!-- Phone Input -->
        <div class="space-y-1.5">
          <label for="phone" class="block text-xs font-semibold uppercase tracking-wider text-gray-400 ml-1">Nomor HP / WhatsApp</label>
          <div class="relative group">
            <input 
              v-model="form.phone"
              id="phone" 
              type="tel" 
              required 
              placeholder="Contoh: 08123456789"
              class="w-full px-5 py-3.5 bg-gray-50 border border-transparent rounded-xl text-gray-900 text-sm font-medium placeholder-gray-400 focus:bg-white focus:border-gray-200 focus:ring-4 focus:ring-gray-100 focus:outline-none transition-all duration-300 group-hover:bg-gray-50/80"
            >
          </div>
        </div>

        <!-- Rating Input -->
        <div class="space-y-3 pt-2">
          <label class="block text-center text-sm font-semibold text-gray-700">Bagaimana pengalaman Anda?</label>
          <div class="flex justify-center items-center gap-2">
            <template v-for="star in 5" :key="star">
              <button 
                type="button" 
                @click="setRating(star)"
                @mouseenter="hoverRating = star"
                @mouseleave="hoverRating = 0"
                class="group relative focus:outline-none p-1"
              >
                <svg 
                  xmlns="http://www.w3.org/2000/svg" 
                  class="h-9 w-9 transition-all duration-300 transform group-hover:scale-110 drop-shadow-sm"
                  :class="(star <= (hoverRating || form.rating)) ? 'text-yellow-400 fill-yellow-400' : 'text-gray-200 fill-gray-50'"
                  viewBox="0 0 24 24" 
                  stroke="currentColor" 
                  stroke-width="1.5"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.519 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.519-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                </svg>
              </button>
            </template>
          </div>
          <div class="h-6 text-center">
            <span 
              class="inline-block px-3 py-1 rounded-full text-xs font-semibold tracking-wide transition-all duration-300"
              :class="form.rating > 0 ? 'bg-red-50 text-red-600' : 'opacity-0'"
            >
              {{ ratingLabel }}
            </span>
          </div>
        </div>

        <!-- Comment Input -->
        <div class="space-y-1.5">
          <label for="comment" class="block text-xs font-semibold uppercase tracking-wider text-gray-400 ml-1">Kritik & Masukan</label>
          <div class="relative group">
            <textarea 
              v-model="form.comment"
              id="comment" 
              rows="4" 
              placeholder="Ceritakan detail pengalaman Anda..."
              class="w-full px-5 py-3.5 bg-gray-50 border border-transparent rounded-xl text-gray-900 text-sm font-medium placeholder-gray-400 focus:bg-white focus:border-gray-200 focus:ring-4 focus:ring-gray-100 focus:outline-none transition-all duration-300 resize-none group-hover:bg-gray-50/80"
            ></textarea>
          </div>
        </div>

        <!-- Submit Button -->
        <button 
          type="submit" 
          :disabled="isSubmitting || form.rating === 0"
          class="group w-full relative flex items-center justify-center py-4 px-6 rounded-xl text-white font-bold text-sm tracking-wide transition-all duration-300 shadow-[0_10px_20px_-10px_rgba(200,16,46,0.5)] hover:shadow-[0_20px_40px_-15px_rgba(200,16,46,0.6)] hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed disabled:shadow-none disabled:translate-y-0 bg-red-600 hover:bg-red-700"
        >
          <span v-if="!isSubmitting" class="flex items-center gap-2">
            Kirim Ulasan
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
            </svg>
          </span>
          <svg v-else class="animate-spin h-5 w-5 text-white/90" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
        </button>
      </form>
    </div>

    <!-- Minimalist Footer -->
    <div class="mt-8 text-center">
      <p class="text-xs text-gray-400 font-medium tracking-wide">
        &copy; {{ new Date().getFullYear() }} Sutan Raya Travel
      </p>
    </div>

    <!-- Modern Success Modal -->
    <Teleport to="body">
      <div v-if="showSuccess" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-gray-900/20 backdrop-blur-sm transition-opacity" @click="resetForm"></div>
        
        <!-- Modal Content -->
        <div class="relative bg-white rounded-3xl shadow-[0_20px_60px_-15px_rgba(0,0,0,0.1)] p-8 max-w-sm w-full text-center transform transition-all scale-100 border border-gray-100">
          <div class="w-16 h-16 bg-green-50 rounded-full flex items-center justify-center mx-auto mb-6 ring-4 ring-green-50/50">
            <svg class="h-8 w-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"></path>
            </svg>
          </div>
          <h3 class="text-xl font-bold text-gray-900 mb-2">Terima Kasih!</h3>
          <p class="text-gray-500 text-sm mb-8 leading-relaxed">
            Masukan Anda sangat berarti bagi kami untuk terus meningkatkan pelayanan.
          </p>
          <button @click="resetForm" class="w-full bg-gray-900 hover:bg-black text-white font-bold py-3.5 px-6 rounded-xl transition-all duration-300 transform hover:-translate-y-0.5 hover:shadow-lg text-sm tracking-wide">
            Tutup
          </button>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import logo from './assets/logo.png'

const form = ref({
  admin_contact: '',
  name: '',
  phone: '',
  rating: 0,
  comment: ''
})

const isDropdownOpen = ref(false)
const adminOptions = [
  'Admin Padang',
  'Admin Payakumbuh',
  'Admin Bukittinggi',
  'Admin Bus'
]

const selectAdmin = (admin) => {
  form.value.admin_contact = admin
  isDropdownOpen.value = false
}

const hoverRating = ref(0)
const isSubmitting = ref(false)
const showSuccess = ref(false)

const ratingLabel = computed(() => {
  const r = hoverRating.value || form.value.rating
  switch(r) {
    case 1: return 'Sangat Buruk';
    case 2: return 'Buruk';
    case 3: return 'Cukup';
    case 4: return 'Baik';
    case 5: return 'Sangat Baik';
    default: return '';
  }
})

const setRating = (val) => {
  form.value.rating = val
}

const submitReview = async () => {
  if (form.value.rating === 0) return
  
  isSubmitting.value = true
  
  try {
    // Determine API URL based on environment
    // Use relative path for production (served under same domain) and absolute for dev
    const apiUrl = import.meta.env.PROD ? './api_review.php' : 'http://localhost/travel-sr-fe/review/api_review.php'
    
    // For specific user request: deployment at sutanraya.com/review
    // The api_review.php is in the same folder, so relative path is safest.
    
    const response = await fetch(apiUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      mode: 'cors', // Important for cross-origin if dev
      body: JSON.stringify(form.value)
    })

    const result = await response.json()

    if (result.status === 'success') {
      showSuccess.value = true
    } else {
      alert(result.message || 'Terjadi kesalahan.')
    }
  } catch (error) {
    console.error('Error submitting review:', error)
    alert('Gagal mengirim ulasan. Silakan coba lagi.')
  } finally {
    isSubmitting.value = false
  }
}

const resetForm = () => {
  showSuccess.value = false
  form.value = {
    name: '',
    phone: '',
    rating: 0,
    comment: ''
  }
}
</script>
