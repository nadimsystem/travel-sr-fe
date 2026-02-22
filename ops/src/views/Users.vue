<template>
  <div class="h-full flex flex-col custom-scrollbar overflow-hidden">
    <!-- Header -->
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h2 class="text-xl font-bold text-slate-800 dark:text-white">Manajemen User</h2>
            <p class="text-sm text-slate-500">Kelola Akun Akses Sistem</p>
        </div>
        <button @click="openModal('add')" class="bg-blue-600 text-white px-4 py-2 rounded-lg font-bold shadow-lg hover:bg-blue-700 transition-colors flex items-center gap-2">
            <i class="bi bi-plus-lg"></i> Tambah User
        </button>
    </div>

    <!-- User Table -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden flex-1 flex flex-col">
        <div class="overflow-x-auto flex-1 custom-scrollbar">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-slate-500 uppercase bg-slate-50 dark:bg-slate-700/50 sticky top-0 z-10">
                    <tr>
                        <th class="px-6 py-4">Nama Lengkap</th>
                        <th class="px-6 py-4">Username</th>
                        <th class="px-6 py-4">Password</th>
                        <th class="px-6 py-4">Jabatan (Role)</th>
                        <th class="px-6 py-4">Penempatan</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    <tr v-if="users.length === 0">
                        <td colspan="6" class="px-6 py-8 text-center text-slate-400 italic">Belum ada data user.</td>
                    </tr>
                    <tr v-for="user in users" :key="user.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                        <td class="px-6 py-4 font-bold text-slate-800 dark:text-white flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-600 flex items-center justify-center text-slate-500 dark:text-slate-300">
                                <i class="bi bi-person-fill"></i>
                            </div>
                            {{ user.name }}
                        </td>
                        <td class="px-6 py-4 text-slate-600 dark:text-slate-300 font-mono">{{ user.username }}</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <div class="font-mono text-[10px] bg-slate-100 dark:bg-slate-700 p-1 rounded max-w-[150px] break-all" :class="user.showPlain ? '' : 'truncate'">
                                    {{ user.showPlain ? (user.password_plain || 'Hidden') : user.password }}
                                </div>
                                <button @click="user.showPlain = !user.showPlain" class="text-slate-400 hover:text-blue-600 transition-colors" :title="user.showPlain ? 'Sembunyikan' : 'Lihat'">
                                    <i :class="user.showPlain ? 'bi-eye-slash-fill' : 'bi-eye-fill'"></i>
                                </button>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-slate-700 dark:text-slate-200 font-bold">{{ user.position }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-0.5 rounded text-[10px] font-bold uppercase bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800">{{ user.placement }}</span>
                        </td>
                        <td class="px-6 py-4 text-right space-x-2">
                            <button @click="openModal('edit', user)" class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 dark:bg-slate-700 dark:text-blue-400 dark:hover:bg-slate-600 transition-colors"><i class="bi bi-pencil-fill"></i></button>
                            <button @click="deleteUser(user)" class="w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 dark:bg-slate-700 dark:text-red-400 dark:hover:bg-slate-600 transition-colors"><i class="bi bi-trash-fill"></i></button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- User Modal -->
    <div v-if="userModal.isOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" @click.self="userModal.isOpen = false">
        <div class="bg-white dark:bg-slate-800 w-full max-w-md rounded-2xl shadow-xl overflow-hidden animate-fade-in-up">
            <div class="p-4 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-slate-50 dark:bg-slate-800/50">
                <h3 class="font-bold text-lg text-slate-800 dark:text-white">{{ userModal.mode === 'add' ? 'Tambah User Baru' : 'Edit User' }}</h3>
                <button @click="userModal.isOpen = false" class="text-slate-400 hover:text-slate-600"><i class="bi bi-x-lg"></i></button>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Nama Lengkap</label>
                    <input type="text" v-model="userModal.data.name" class="w-full p-2.5 border rounded-lg bg-white dark:bg-slate-700 border-slate-200 dark:border-slate-600 text-sm text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Contoh: Budi Santoso">
                </div>
                <div>
                    <label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Username</label>
                    <input type="text" v-model="userModal.data.username" class="w-full p-2.5 border rounded-lg bg-white dark:bg-slate-700 border-slate-200 dark:border-slate-600 text-sm text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none" placeholder="username_login">
                </div>
                
                <div>
                    <label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Password {{ userModal.mode === 'edit' ? '(Isi untuk reset)' : '' }}</label>
                    <div class="relative">
                        <input :type="showInputPassword ? 'text' : 'password'" v-model="userModal.data.password" class="w-full p-2.5 border rounded-lg bg-white dark:bg-slate-700 border-slate-200 dark:border-slate-600 text-sm text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none" placeholder="••••••">
                        <button @click="showInputPassword = !showInputPassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                            <i :class="showInputPassword ? 'bi-eye-slash-fill' : 'bi-eye-fill'"></i>
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Jabatan (Role)</label>
                        <input type="text" v-model="userModal.data.position" class="w-full p-2.5 border rounded-lg bg-white dark:bg-slate-700 border-slate-200 dark:border-slate-600 text-sm text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Contoh: Admin">
                        <p class="text-[10px] text-slate-400 mt-1">Gunakan 'Keuangan' atau 'IT' untuk akses penuh.</p>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Penempatan</label>
                        <select v-model="userModal.data.placement" class="w-full p-2.5 border rounded-lg bg-white dark:bg-slate-700 border-slate-200 dark:border-slate-600 text-sm text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none">
                            <option value="Padang">Padang</option>
                            <option value="Bukittinggi">Bukittinggi</option>
                            <option value="Payakumbuh">Payakumbuh</option>
                            <option value="Semua">Semua</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="p-4 border-t border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-end gap-3">
                <button @click="userModal.isOpen = false" class="px-4 py-2 text-slate-500 font-bold hover:bg-slate-200 rounded-lg text-sm transition-colors">Batal</button>
                <button @click="saveUser" :disabled="isLoading" class="px-6 py-2 bg-blue-600 text-white font-bold rounded-lg shadow hover:bg-blue-700 text-sm transition-colors disabled:opacity-50">
                    {{ isLoading ? 'Menyimpan...' : 'Simpan' }}
                </button>
            </div>
        </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

const users = ref([]);
const isLoading = ref(false);
const showInputPassword = ref(false);

const userModal = ref({
    isOpen: false,
    mode: 'add',
    data: {
        id: '',
        name: '',
        username: '',
        password: '',
        position: '',
        placement: 'Padang'
    }
});

onMounted(() => {
    fetchUsers();
});

const fetchUsers = async () => {
    try {
        const res = await axios.get('api.php?action=get_users');
        if (res.data.users) {
            users.value = res.data.users.map(u => ({...u, showPlain: false}));
        }
    } catch (e) {
        console.error(e);
    }
};

const openModal = (mode, user = null) => {
    userModal.value.mode = mode;
    showInputPassword.value = false;
    
    if (mode === 'edit' && user) {
        userModal.value.data = { ...user, password: '' }; // Don't show hash in edit input
    } else {
        userModal.value.data = {
            id: '',
            name: '',
            username: '',
            password: '',
            position: '',
            placement: 'Padang'
        };
    }
    userModal.value.isOpen = true;
};

const saveUser = async () => {
    if (!userModal.value.data.name || !userModal.value.data.username || !userModal.value.data.position) {
        Swal.fire('Data Kurang', 'Mohon lengkapi data wajib.', 'warning');
        return;
    }
    
    if (userModal.value.mode === 'add' && !userModal.value.data.password) {
        Swal.fire('Password Wajib', 'Password wajib diisi untuk user baru.', 'warning');
        return;
    }

    isLoading.value = true;
    try {
        const res = await axios.post('api.php', {
            action: 'save_user',
            mode: userModal.value.mode,
            ...userModal.value.data
        });

        if (res.data.success) {
            Swal.fire({ icon: 'success', title: 'Berhasil', showConfirmButton: false, timer: 1500 });
            userModal.value.isOpen = false;
            fetchUsers();
        } else {
            Swal.fire('Gagal', res.data.message || 'Gagal menyimpan.', 'error');
        }
    } catch (e) {
        console.error(e);
        Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
    } finally {
        isLoading.value = false;
    }
};

const deleteUser = (user) => {
    Swal.fire({
        title: 'Hapus User?',
        text: `Hapus user ${user.name}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Ya, Hapus'
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                const res = await axios.post('api.php', { action: 'delete_user', id: user.id });
                if (res.data.success) {
                    Swal.fire('Terhapus!', '', 'success');
                    fetchUsers();
                } else {
                    Swal.fire('Gagal', res.data.message, 'error');
                }
            } catch (e) {
                console.error(e);
                Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
            }
        }
    });
};
</script>
