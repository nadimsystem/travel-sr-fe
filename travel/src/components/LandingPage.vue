<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

// --- Lazy load TikTok ---
const tiktokRef = ref(null)
const tiktokVisible = ref(false)
let tiktokObserver = null

const emit = defineEmits(['navigate'])

const navigate = (page) => {
    emit('navigate', page)
}

const imageScroller = ref(null)
let scrollInterval = null
const scrollSpeed = 1 // pixels per frame

const startScroll = () => {
    if (scrollInterval) return
    scrollInterval = setInterval(() => {
        if (imageScroller.value) {
            imageScroller.value.scrollLeft += scrollSpeed
            // Reset to start if reached the end for infinite-like feel
            if (imageScroller.value.scrollLeft >= imageScroller.value.scrollWidth - imageScroller.value.clientWidth) {
                imageScroller.value.scrollLeft = 0
            }
        }
    }, 30) // ~33fps
}

const stopScroll = () => {
    if (scrollInterval) {
        clearInterval(scrollInterval)
        scrollInterval = null
    }
}

const loadTikTok = () => {
    if (tiktokVisible.value) return
    tiktokVisible.value = true
    // Dynamically inject TikTok embed script once visible
    if (!document.querySelector('script[src="https://www.tiktok.com/embed.js"]')) {
        const s = document.createElement('script')
        s.src = 'https://www.tiktok.com/embed.js'
        s.async = true
        document.body.appendChild(s)
    } else if (window.tiktok) {
        window.tiktok.embed.init()
    }
}

onMounted(() => {
    startScroll()

    // Observe Feature 3 section for lazy TikTok loading
    tiktokObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                loadTikTok()
                tiktokObserver.disconnect()
            }
        })
    }, { threshold: 0.1 })

    if (tiktokRef.value) {
        tiktokObserver.observe(tiktokRef.value)
    }
})

onUnmounted(() => {
    stopScroll()
    if (tiktokObserver) tiktokObserver.disconnect()
})
</script>

<template>
  <div class="landing-document max-w-4xl mx-auto  mt-6 py-10  md:mt-12 fade-in">
    
    <div class="flex justify-center mt-[-3rem] py-3">
         <img fetchpriority="high" decoding="async" src="/logo.png" class="h-30 md:h-80 lg:h-96 w-auto  object-cover " alt="Armada Sutan Raya 1">
    </div>

    <!-- Apple-style Hero Section -->
    <div class="text-center md:py-10 px-4 md:px-0 animate-fade-in-up">
        <h1 class="text-[30px] md:text-[5rem] font-bold tracking-tighter text-[#1d1d1f] leading-[1.1] mb-6">
            Solusi Perjalanan,<br>
            <span class="bg-clip-text text-transparent bg-gradient-to-r from-[#0071e3] to-[#42a5f5] saturate-[1.2]">
                Tenang & Nyaman.
            </span>
        </h1>
        <p class="text-lg md:text-2xl text-[#86868b] max-w-2xl mx-auto text-[14px] tracking-tight px-4 leading-relaxed">
           Layanan shuttle luxury Sutan Raya adalah pilihan ideal untuk perjalanan aman, tenang dan nyaman dengan sistem Antar Jemput Alamat (door to door). Nikmati kenyamanan kelas eksekutif terbaik.


        </p>

        
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mt-12 px-6">
            <button @click="navigate('form')" class="w-full sm:w-auto bg-[#0071e3] hover:bg-[#0077ED] text-white font-semibold text-[17px] py-4 px-10 rounded-full transition-all transform active:scale-95 shadow-md shadow-blue-500/10">
                Pesan Tiket Travel
            </button>
            <button @click="navigate('list')" class="w-full sm:w-auto bg-[#f5f5f7] hover:bg-[#e8e8ed] text-[#1d1d1f] font-semibold text-[17px] py-4 px-10 rounded-full transition-all transform active:scale-95">
                Cek Jadwal
            </button>
        </div>
    </div>

     <!-- Armada Gallery Section -->
    <section id="armada" class="overflow-hidden mt-5  " aria-labelledby="galeri-armada">
        <!-- <div class="text-center mb-10">
            <span class="kicker bg-[#f5f5f7] text-[#0071e3] px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider mb-4 inline-block">Galeri Armada</span>
            <h2 id="galeri-armada" class="text-3xl md:text-4xl font-bold text-[#1d1d1f] tracking-tight">Armada Terbaru & Terbaik Kami</h2>
            <p class="text-[#86868b] text-lg mt-3 max-w-xl mx-auto font-medium">
                Kenyamanan dan keamanan Anda adalah prioritas utama kami dalam setiap perjalanan.
            </p>
        </div> -->
        
        <!-- Fluid container: bleeds to edge on mobile (-mx-4), but constrained to normal padding on desktop -->
        <div class="relative group px-0  md:mx-0">
            <div 
                ref="imageScroller" 
                class="image-scroller flex overflow-x-auto  py-4 md:px-0 no-scrollbar"
                role="region" 
                aria-label="Galeri Gambar Armada"
            >
                <img loading="lazy" src="/armada/01.webp" class="h-70 md:h-80 lg:h-96 w-auto  object-cover shadow-lg" alt="Armada Sutan Raya 1">
                <img loading="lazy" src="/armada/02.webp" class="h-70 md:h-80 lg:h-96 w-auto  object-cover shadow-lg" alt="Armada Sutan Raya 2">
                <img loading="lazy" src="/armada/03.webp" class="h-70 md:h-80 lg:h-96 w-auto  object-cover shadow-lg" alt="Armada Sutan Raya 3">
                <img loading="lazy" src="/armada/04.webp" class="h-70 md:h-80 lg:h-96 w-auto  object-cover shadow-lg" alt="Armada Sutan Raya 4">
                <img loading="lazy" src="/armada/05.webp" class="h-70 md:h-80 lg:h-96 w-auto  object-cover shadow-lg" alt="Armada Sutan Raya 5">
                <img loading="lazy" src="/armada/06.webp" class="h-70 md:h-80 lg:h-96 w-auto  object-cover shadow-lg" alt="Armada Sutan Raya 6">
                <img loading="lazy" src="/armada/07.webp" class="h-70 md:h-80 lg:h-96 w-auto  object-cover shadow-lg" alt="Armada Sutan Raya 7">
                <img loading="lazy" src="/armada/08.webp" class="h-70 md:h-80 lg:h-96 w-auto  object-cover shadow-lg" alt="Armada Sutan Raya 8">
                <img loading="lazy" src="/armada/09.webp" class="h-70 md:h-80 lg:h-96 w-auto  object-cover shadow-lg" alt="Armada Sutan Raya 9">
                <img loading="lazy" src="/armada/10.webp" class="h-70 md:h-80 lg:h-96 w-auto  object-cover shadow-lg" alt="Armada Sutan Raya 10">
                <img loading="lazy" src="/armada/14.webp" class="h-70 md:h-80 lg:h-96 w-auto  object-cover shadow-lg" alt="Interior Armada Sutan Raya 1">
                <img loading="lazy" src="/armada/15.webp" class="h-70 md:h-80 lg:h-96 w-auto  object-cover shadow-lg" alt="Interior Armada Sutan Raya 2">
                <img loading="lazy" src="/armada/16.webp" class="h-70 md:h-80 lg:h-96 w-auto  object-cover shadow-lg" alt="Interior Armada Sutan Raya 3">
                <!-- Duplicate for seamless loop if needed, but manual scroll & reset is handled in JS -->
            </div>
        </div>
    </section>
  

    <!-- Apple-style Feature Grid (Bento Box) -->
    <div class="grid grid-cols-1 md:grid-cols-2 px-4 gap-4 md:gap-6 mt-12">
        
        <!-- Feature 1 -->
        <div class="bg-white border border-[rgba(0,0,0,0.05)] shadow-[0_8px_32px_rgba(0,0,0,0.03)] rounded-[28px] p-8 md:p-10 flex flex-col items-center text-center group transition-all duration-500">
             <div class="w-20 h-20 bg-[#f5f5f7] rounded-[22px] flex items-center justify-center mb-6 transform group-hover:rotate-12 transition-transform">
                <i class="bi bi-shield-check text-[#1d1d1f] text-4xl"></i>
             </div>
             <h3 class="text-2xl  font-bold text-[#1d1d1f] tracking-tight mb-3">Kru Profesional</h3>
             <p class="text-[#6e6e73] text-[14px] font-medium leading-relaxed">Driver berpengalaman yang menguasai medan jalan Sumatera Barat dengan pelayanan ramah & solutif.</p>
        </div>

        <!-- Feature 2 -->
        <div class="bg-white border border-[rgba(0,0,0,0.05)] shadow-[0_8px_32px_rgba(0,0,0,0.03)] rounded-[28px] p-8 md:p-10 flex flex-col items-center text-center group transition-all duration-500">
             <div class="w-20 h-20 bg-[#f5f5f7] rounded-[22px] flex items-center justify-center mb-6 transform group-hover:scale-110 transition-transform">
                  <i class="bi bi-house text-[#1d1d1f] text-4xl"></i>
             </div>
             <h3 class="text-2xl font-bold text-[#1d1d1f] tracking-tight mb-3">Door to Door</h3>
             <p class="text-[#6e6e73] font-medium leading-relaxed">Sistem antar jemput alamat yang praktis untuk rute Padang, Bukittinggi, Payakumbuh, & Pekanbaru.</p>
        </div>

        <!-- Feature 3 (Wide) - TikTok lazy loaded on scroll -->
        <div ref="tiktokRef" class="bg-black rounded-[32px] md:col-span-2 shadow-2xl shadow-black/10 overflow-hidden relative group transition-all duration-700 flex justify-center items-center py-5 min-h-[200px]">
            <!-- Placeholder shown until TikTok loads -->
            <div v-if="!tiktokVisible" class="flex flex-col items-center gap-3 text-white/40 py-16">
                <i class="bi bi-tiktok text-5xl"></i>
                <p class="text-sm font-medium">Scroll untuk memuat video</p>
            </div>

            <!-- TikTok embed: only rendered when in view -->
            <blockquote v-if="tiktokVisible" class="tiktok-embed" cite="https://www.tiktok.com/@sutanraya_travel/video/7603609065176059156" data-video-id="7603609065176059156" style="max-width: 605px;min-width: 325px; margin: 0 auto;">
                <section>
                    <a target="_blank" title="@sutanraya_travel" href="https://www.tiktok.com/@sutanraya_travel?refer=embed">@sutanraya_travel</a>
                    Dengan Rahmat Allah SWT. Insyallah Kami Sutan Raya akan Buka Rute Padang - Pekanbaru dan Sebaliknya pada Hari Sabtu 8 Februari 2026.
                    <a target="_blank" title="♬ suara asli - Sutanraya_travel" href="https://www.tiktok.com/music/suara-asli-Sutanrayatravel-7603609155917449992?refer=embed">♬ suara asli - Sutanraya_travel</a>
                </section>
            </blockquote>

            <!-- Abstract background shape -->
            <div class="absolute right-0 top-0 w-96 h-96 bg-[#0071e3] opacity-20 blur-[100px] rounded-full transform translate-x-1/2 -translate-y-1/2 pointer-events-none"></div>
            <div class="absolute left-0 bottom-0 w-64 h-64 bg-purple-500 opacity-10 blur-[80px] rounded-full transform -translate-x-1/2 translate-y-1/2 pointer-events-none"></div>
        </div>

        <!-- Popular Routes section -->
        <div class="mt-20 md:mt-32 px-4">
             <h2 class="text-3xl md:text-4xl font-bold text-[#1d1d1f] tracking-tight text-center mb-12">Rute Populer</h2>
             <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                 <div v-for="route in [
                    { name: 'Padang ⇄ Bukittinggi', type: 'Antar Jemput Alamat (Door to Door)' },
                    { name: 'Padang ⇄ Payakumbuh', type: 'Antar Jemput Alamat (Door to Door)' },
                    { name: 'Padang ⇄ Pekanbaru', type: 'Antar Jemput Alamat (Door to Door)' },
                    { name: 'Payakumbuh ⇄ Pekanbaru', type: 'Antar Jemput Alamat (Door to Door)' },
                    { name: 'Bukittinggi ⇄ Pekanbaru', type: 'Antar Jemput Alamat (Door to Door)' }
                 ]" :key="route.name" class="bg-[#f5f5f7] rounded-[24px] p-6 hover:bg-[#ececef] transition-all transform hover:-translate-y-1">
                    <h4 class="text-xl font-bold text-[#1d1d1f] mb-2">{{ route.name }}</h4>
                    <p class="text-[#6e6e73] font-medium text-sm">{{ route.type }}</p>
                 </div>
             </div>
        </div>
    </div>

  </div>
</template>

<style scoped>
.fade-in {
  animation: fadeIn 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

.animate-fade-in-up {
    animation: fadeInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    opacity: 0;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px) scale(0.98);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

.image-scroller {
    scroll-behavior: auto; /* Important for JS scrollLeft manipulation */
}
</style>
