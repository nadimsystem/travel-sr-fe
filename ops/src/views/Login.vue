<template>
  <div class="h-screen w-screen overflow-hidden flex items-center justify-center bg-gray-50 login-bg font-sans">
    <!-- Overlay -->
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm"></div>

    <div class="relative w-full max-w-md p-6">
      <div class="glass-effect rounded-3xl shadow-2xl p-8 sm:p-10 border border-white/50">
        <!-- Header -->
        <div class="text-center mb-10">
          <img src="/logo.webp" alt="Logo" class="h-16 mx-auto mb-4 drop-shadow-md" />
          <h1 class="text-2xl font-bold text-slate-800">Keuangan Sutan Raya</h1>
          <p class="text-slate-500 text-sm mt-1">Silakan masuk untuk melanjutkan</p>
        </div>

        <!-- Form -->
        <form @submit.prevent="handleLogin" class="space-y-6">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-2">Username</label>
            <div class="relative">
              <input
                v-model="username"
                type="text"
                class="w-full px-4 py-3 bg-white/50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-all font-medium text-slate-800 placeholder:text-slate-400"
                placeholder="username..."
                required
              />
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 mb-2">Password</label>
            <div class="relative">
              <input
                v-model="password"
                :type="showPassword ? 'text' : 'password'"
                class="w-full px-4 py-3 bg-white/50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-all font-medium text-slate-800 placeholder:text-slate-400"
                placeholder="••••••••"
                required
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 focus:outline-none"
              >
                 <svg v-if="!showPassword" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                 </svg>
                 <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                     <path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z" clip-rule="evenodd" />
                     <path d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.742L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.064 7 9.542 7 .847 0 1.669-.105 2.454-.303z" />
                 </svg>
              </button>
            </div>
          </div>

          <div class="pt-2">
            <button
              type="submit"
              :disabled="loading"
              class="w-full bg-slate-800 hover:bg-slate-900 text-white font-bold py-3.5 rounded-xl transition-all transform hover:scale-[1.02] shadow-lg shadow-slate-800/20 active:scale-95 flex items-center justify-center gap-2 disabled:opacity-75 disabled:cursor-not-allowed"
            >
              <span v-if="loading" class="animate-spin mr-2">⟳</span>
              <span v-if="loading">Memproses...</span>
              <span v-else>Masuk Sistem</span>
              <svg v-if="!loading" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
              </svg>
            </button>
            <p class="text-center mt-4 text-xs text-slate-500">
              &copy; 2026 Sutan Raya. Restricted Access.
            </p>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import Swal from 'sweetalert2';

const router = useRouter();

const username = ref('');
const password = ref('');
const showPassword = ref(false);
const loading = ref(false);

const handleLogin = async () => {
    loading.value = true;
    try {
        const response = await axios.post('api.php?action=login', {
            username: username.value,
            password: password.value
        });
        
        const data = response.data;
        
        if (data.status === 'success') {
            localStorage.setItem('is_authenticated', 'true'); // Simple marker
            // Ideally store token/user details
            
            Swal.fire({
                icon: 'success',
                title: 'Login Berhasil',
                text: 'Selamat datang kembali, ' + data.user.name,
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                router.push('/dashboard');
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Gagal Masuk',
                text: data.message || 'Username atau password salah',
                confirmButtonColor: '#0f172a'
            });
        }
    } catch (error) {
        console.error(error);
        Swal.fire({
            icon: 'error',
            title: 'System Error',
            text: 'Terjadi kesalahan koneksi server',
            confirmButtonColor: '#0f172a'
        });
    } finally {
        loading.value = false;
    }
};
</script>

<style scoped>
.glass-effect {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
}
.login-bg {
    background-image: url('https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?q=80&w=2000&auto=format&fit=crop');
    background-size: cover;
    background-position: center;
}
</style>
