<template>
  <div class="absolute inset-0 z-[100] bg-[#f8fafc] flex items-center justify-center p-6">
      <div class="w-full max-w-sm bg-white p-8 rounded-[2.5rem] shadow-2xl shadow-slate-200/50 border border-slate-50 animate-fade-in relative overflow-hidden">
          <!-- Decorative Circle -->
          <div class="absolute -top-10 -right-10 w-32 h-32 bg-gold/10 rounded-full blur-2xl"></div>
          <div class="absolute -bottom-10 -left-10 w-32 h-32 bg-blue-500/5 rounded-full blur-2xl"></div>

          <div class="text-center mb-8 relative">
              <div class="w-20 h-20 mx-auto bg-gradient-to-br from-[#fefce8] to-[#fef08a] rounded-3xl flex items-center justify-center shadow-inner mb-4">
                  <!-- <img src="/logo.png" class="w-10 h-10 object-contain"> -->
                  <i class="bi bi-person-fill text-gold text-3xl"></i>
              </div>
              <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Sutan Raya <span class="text-gold">CRM</span></h1>
              <p class="text-xs text-slate-400 mt-2 font-medium">Please sign in to continue</p>
          </div>

          <form @submit.prevent="doLogin" class="space-y-5 relative">
              <div>
                  <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5 ml-1">Username</label>
                  <input type="text" v-model="form.username" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-100 rounded-2xl focus:outline-none focus:ring-2 focus:ring-gold/50 focus:border-gold transition-all text-sm font-bold text-slate-700 placeholder:font-normal placeholder:text-slate-300" placeholder="Enter username">
              </div>
              <div>
                  <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5 ml-1">Password</label>
                  <div class="relative">
                      <input :type="showPass ? 'text' : 'password'" v-model="form.password" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-100 rounded-2xl focus:outline-none focus:ring-2 focus:ring-gold/50 focus:border-gold transition-all text-sm font-bold text-slate-700 placeholder:font-normal placeholder:text-slate-300" placeholder="••••••••">
                      <button type="button" @click="showPass = !showPass" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                          <i class="bi" :class="showPass ? 'bi-eye-slash-fill' : 'bi-eye-fill'"></i>
                      </button>
                  </div>
              </div>
              
              <div v-if="error" class="bg-red-50 text-red-500 text-xs py-3 px-4 rounded-xl flex items-center gap-2">
                  <i class="bi bi-exclamation-circle-fill"></i> {{ error }}
              </div>

              <button type="submit" class="w-full bg-slate-900 text-white py-4 rounded-2xl font-bold text-sm hover:bg-slate-800 hover:shadow-lg hover:shadow-slate-200 hover:-translate-y-0.5 transition-all duration-300">
                  Sign In
              </button>
          </form>
      </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import api from '../api/axios';

const router = useRouter();
const form = ref({ username: '', password: '' });
const error = ref('');
const showPass = ref(false);

const doLogin = async () => {
    try {
        error.value = '';
        const res = await api.post('/login', {
            action: 'login', // keeping old API structure for compatibility if needed
            username: form.value.username,
            password: form.value.password
        });

        if (res.data.status === 'success') {
            localStorage.setItem('user', JSON.stringify(res.data.user));
            router.push('/dashboard');
        } else {
            error.value = res.data.message || 'Login failed';
        }
    } catch (e) {
        error.value = 'Connection error';
        console.error(e);
        
        // Bypass for demo if backend is not reachable
        if (form.value.username === 'admin' && form.value.password === 'admin') {
             localStorage.setItem('user', JSON.stringify({ name: 'Admin Demo' }));
             router.push('/dashboard');
        }
    }
};
</script>
