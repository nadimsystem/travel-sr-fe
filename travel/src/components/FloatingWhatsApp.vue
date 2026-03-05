<script setup>
import { ref } from 'vue'

const isPopupOpen = ref(false)

const togglePopup = () => {
    isPopupOpen.value = !isPopupOpen.value
}

const openWhatsApp = (phone) => {
    // Format phone to standard WA format (replace starting 0 with 62)
    let formattedPhone = phone.replace(/[^0-9]/g, '')
    if (formattedPhone.startsWith('0')) {
        formattedPhone = '62' + formattedPhone.substring(1)
    }
    
    const url = `https://wa.me/${formattedPhone}`
    window.open(url, '_blank')
}

const admins = [
    { name: 'Admin Padang', phone: '0821-9999-1265' },
    { name: 'Admin Payakumbuh', phone: '0821-9999-1244' },
    { name: 'Admin Bukittinggi', phone: '0821-9999-1264' },
    { name: 'Admin Pekanbaru', phone: '0853-6504-9346' },
]
</script>

<template>
  <div class="fixed bottom-24 right-4 md:bottom-8 md:right-8 z-50 flex flex-col items-end">
    
    <!-- WhatsApp Popup Menu -->
    <transition name="pop">
        <div v-if="isPopupOpen" class="bg-white rounded-[24px] shadow-[0_8px_32px_rgba(0,0,0,0.12)] border border-[rgba(0,0,0,0.05)] p-5 mb-4 w-[280px] overflow-hidden transform origin-bottom-right">
            <h3 class="text-[17px] font-bold text-[#1d1d1f] mb-4 tracking-tight px-1">Pilih Kontak WhatsApp</h3>
            
            <div class="space-y-2">
                <button 
                    v-for="admin in admins" 
                    :key="admin.name"
                    @click="openWhatsApp(admin.phone)"
                    class="w-full bg-[#f5f5f7] hover:bg-[#ebebeb] p-3 rounded-[16px] flex items-center gap-3 transition-colors active:scale-95 transform text-left"
                >
                    <div class="w-10 h-10 rounded-full bg-green-500/10 flex items-center justify-center shrink-0">
                        <i class="bi bi-whatsapp text-green-500 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-[15px] font-bold text-[#1d1d1f] leading-tight">{{ admin.name }}</p>
                        <p class="text-[13px] text-[#6e6e73] mt-0.5">{{ admin.phone }}</p>
                    </div>
                </button>
            </div>
        </div>
    </transition>

    <!-- Floating Button -->
    <button 
        @click="togglePopup"
        class="w-14 h-14 md:w-16 md:h-16 bg-[#25D366] hover:bg-[#20bd5a] text-white rounded-full flex items-center justify-center shadow-[0_8px_20px_rgba(37,211,102,0.4)] transition-all transform active:scale-90"
        :class="isPopupOpen ? 'scale-90 bg-[#1d1d1f] hover:bg-black shadow-lg text-white' : ''"
    >
        <i v-if="!isPopupOpen" class="bi bi-whatsapp text-3xl"></i>
        <i v-else class="bi bi-x-lg text-xl"></i>
    </button>
  </div>

  <!-- Overlay to close popup when clicking outside -->
  <transition name="fade">
      <div v-if="isPopupOpen" @click="isPopupOpen = false" class="fixed inset-0 z-40 bg-black/5 backdrop-blur-[2px]"></div>
  </transition>
</template>

<style scoped>
.pop-enter-active {
    transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.pop-leave-active {
    transition: all 0.2s cubic-bezier(0.4, 0, 1, 1);
}
.pop-enter-from,
.pop-leave-to {
    opacity: 0;
    transform: scale(0.8) translateY(20px);
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
