<template>
  <aside :class="mobileMenuOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'" class="fixed lg:static inset-y-0 left-0 z-50 w-72 bg-white border-r border-slate-100 flex flex-col transition-transform duration-300 ease-spring">
      <div class="h-24 flex items-center px-8 border-b border-slate-50">
          <div class="text-xl font-extrabold text-slate-900 tracking-tight flex items-center gap-3">
              <!-- <img src="/logo.png" alt="Sutan Raya" class="w-9 h-9 object-contain">  -->
              <div>
                  <span class="block leading-none">Sutan<span class="text-gold">Raya</span></span>
                  <span class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] block mt-1">CRM System</span>
              </div>
          </div>
      </div>
      
      <nav class="flex-1 p-6 space-y-3">
          <router-link to="/dashboard" custom v-slot="{ href, navigate, isActive }">
            <button @click="navigate(); closeMenu()" :class="isActive ? 'bg-[#fefce8] text-[#854d0e] inner-shadow' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900'" class="w-full flex items-center p-4 rounded-[1.2rem] transition-all group relative overflow-hidden">
                <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-8 bg-gold rounded-r-full transition-all duration-300" :class="isActive ? 'opacity-100' : 'opacity-0'"></div>
                <i class="bi bi-grid-fill text-xl lg:mr-4 mr-3 transition-colors" :class="isActive ? 'text-gold' : 'text-slate-300 group-hover:text-slate-500'"></i> 
                <span class="font-bold text-sm tracking-wide">Dashboard</span>
            </button>
          </router-link>

          <router-link to="/contacts" custom v-slot="{ href, navigate, isActive }">
            <button @click="navigate(); closeMenu()" :class="isActive ? 'bg-[#fefce8] text-[#854d0e] inner-shadow' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900'" class="w-full flex items-center p-4 rounded-[1.2rem] transition-all group relative overflow-hidden">
                <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-8 bg-gold rounded-r-full transition-all duration-300" :class="isActive ? 'opacity-100' : 'opacity-0'"></div>
                <i class="bi bi-people-fill text-xl lg:mr-4 mr-3 transition-colors" :class="isActive ? 'text-gold' : 'text-slate-300 group-hover:text-slate-500'"></i> 
                <span class="font-bold text-sm tracking-wide">Data Konsumen</span>
            </button>
          </router-link>

          <router-link to="/broadcast" custom v-slot="{ href, navigate, isActive }">
            <button @click="navigate(); closeMenu()" :class="isActive ? 'bg-[#fefce8] text-[#854d0e] inner-shadow' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900'" class="w-full flex items-center p-4 rounded-[1.2rem] transition-all group relative overflow-hidden">
                <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-8 bg-gold rounded-r-full transition-all duration-300" :class="isActive ? 'opacity-100' : 'opacity-0'"></div>
                <i class="bi bi-megaphone-fill text-xl lg:mr-4 mr-3 transition-colors" :class="isActive ? 'text-gold' : 'text-slate-300 group-hover:text-slate-500'"></i> 
                <span class="font-bold text-sm tracking-wide">Broadcast</span>
            </button>
          </router-link>
      </nav>

      <div class="p-6 border-t border-slate-50">
          <button @click="logout" class="w-full flex items-center justify-center p-4 rounded-[1.2rem] text-red-400 hover:bg-red-50 hover:text-red-500 transition-all font-bold text-xs mb-2">
              <i class="bi bi-box-arrow-right text-lg mr-2"></i> Logout
          </button>
      </div>
  </aside>
</template>

<script setup>
import { useRouter } from 'vue-router';

defineProps(['mobileMenuOpen']);
const emit = defineEmits(['close-menu']);

const router = useRouter();

const closeMenu = () => {
    emit('close-menu');
};

const logout = () => {
    localStorage.removeItem('user');
    router.push('/login');
};
</script>
