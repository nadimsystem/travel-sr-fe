<?php include 'auth_check.php'; ?>
<!DOCTYPE html>
<html lang="id" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola User - Sutan Raya</title>
    <link rel="icon" type="image/webp" href="image/logo.webp">
    <script src="js/loading-optimizer.js?v=<?= time() ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        [v-cloak] { display: none; }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #475569; }
    </style>
    <script>
        tailwind.config = { darkMode: 'class', theme: { extend: { colors: { 'sr-blue': '#0f172a', 'sr-gold': '#d4af37' } } } }
    </script>
</head>
<body class="h-screen w-screen bg-slate-50 text-slate-800 dark:bg-slate-900 dark:text-slate-100 overflow-hidden">
    <div id="app" class="flex h-full w-full" v-cloak>
        <!-- Sidebar -->
        <?php $currentPage = 'users'; include 'components/sidebar.php'; ?>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col min-w-0 bg-slate-50 dark:bg-slate-900 relative h-full overflow-hidden">
            <header class="h-16 bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between px-3 sm:px-6 shadow-sm z-10 transition-colors duration-300">
                <div class="flex items-center gap-2">
                     <!-- Mobile Menu Button -->
                    <button onclick="toggleMobileSidebar()" class="md:hidden w-9 h-9 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 transition-all flex items-center justify-center">
                        <i class="bi bi-list text-xl"></i>
                    </button>
                    <h2 class="text-lg font-bold text-slate-800 dark:text-white">Kelola User</h2>
                </div>
                <div class="flex items-center gap-3">
                    <button @click="openModal('add')" class="bg-sr-blue dark:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-bold shadow-lg hover:bg-slate-800 transition-colors">
                        <i class="bi bi-plus-lg mr-2"></i> Tambah User
                    </button>
                </div>
            </header>

            <div class="flex-1 overflow-y-auto p-6 custom-scrollbar">
                
                <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden transition-colors duration-300">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-slate-500 uppercase bg-slate-50 dark:bg-slate-700 dark:text-slate-300">
                                <tr>
                                    <th class="px-6 py-4">Nama Lengkap</th>
                                    <th class="px-6 py-4">Username</th>
                                    <th class="px-6 py-4">Password (Hash)</th>
                                    <th class="px-6 py-4">Jabatan (Role)</th>
                                    <th class="px-6 py-4">Penempatan</th>
                                    <th class="px-6 py-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                <tr v-if="users.length === 0">
                                    <td colspan="6" class="px-6 py-8 text-center text-slate-400 italic">Belum ada data user.</td>
                                </tr>
                                <tr v-for="user in users" :key="user.id" class="bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                                    <td class="px-6 py-4 font-bold text-slate-800 dark:text-white flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-600 flex items-center justify-center text-slate-500 dark:text-slate-300"><i class="bi bi-person-fill"></i></div>
                                        {{ user.name }}
                                    </td>
                                    <td class="px-6 py-4 text-slate-600 dark:text-slate-300 font-mono">{{ user.username }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <div class="font-mono text-[10px] bg-slate-100 dark:bg-slate-700 p-1 rounded max-w-[150px] break-all" :class="user.showPlain ? '' : 'truncate'">
                                                {{ user.showPlain ? (user.password_plain || 'Hidden (Reset Required)') : user.password }}
                                            </div>
                                            <button @click="user.showPlain = !user.showPlain" class="text-slate-400 hover:text-blue-600 transition-colors" :title="user.showPlain ? 'Show Hash' : 'Show Plaintext'">
                                                <i :class="user.showPlain ? 'bi-eye-slash-fill' : 'bi-eye-fill'"></i>
                                            </button>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-slate-700 dark:text-slate-200 font-bold">{{ user.position }}</td>
                                    <td class="px-6 py-4">
                                        <span class="px-2.5 py-0.5 rounded text-[10px] font-bold uppercase bg-blue-100 text-blue-700 border border-blue-200">{{ user.placement }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-right space-x-2">
                                        <button @click="openModal('edit', user)" class="text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 p-2 rounded-lg transition-colors"><i class="bi bi-pencil-fill"></i></button>
                                        <button @click="deleteUser(user)" class="text-red-600 hover:text-red-800 bg-red-50 hover:bg-red-100 p-2 rounded-lg transition-colors"><i class="bi bi-trash-fill"></i></button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

             <!-- User Modal -->
             <div v-if="isModalVisible" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
                <div class="bg-white dark:bg-slate-800 w-full max-w-md rounded-2xl shadow-2xl overflow-hidden flex flex-col animate-fade-in">
                    <div class="p-5 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-slate-50 dark:bg-slate-900/50">
                        <h3 class="text-lg font-bold text-slate-800 dark:text-white">{{ modalMode === 'add' ? 'Tambah User Baru' : 'Edit User' }}</h3>
                        <button @click="closeModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"><i class="bi bi-x-lg"></i></button>
                    </div>
                    <div class="p-6 space-y-4">
                        <div><label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Nama Lengkap</label><input type="text" v-model="form.name" class="w-full p-2.5 border rounded-lg dark:bg-slate-700 dark:border-slate-600 dark:text-white text-sm" placeholder="Contoh: Budi Santoso"></div>
                        <div><label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Username</label><input type="text" v-model="form.username" class="w-full p-2.5 border rounded-lg dark:bg-slate-700 dark:border-slate-600 dark:text-white text-sm" placeholder="username_login"></div>
                        
                        <div>
                            <label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Password {{ modalMode == 'edit' ? '(Isi untuk reset)' : '' }}</label>
                            <div class="relative">
                                <input :type="showInputPassword ? 'text' : 'password'" v-model="form.password" class="w-full p-2.5 border rounded-lg dark:bg-slate-700 dark:border-slate-600 dark:text-white text-sm" placeholder="••••••">
                                <button @click="showInputPassword = !showInputPassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                    <i :class="showInputPassword ? 'bi-eye-slash-fill' : 'bi-eye-fill'"></i>
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Jabatan (Role System)</label>
                                <input type="text" v-model="form.position" class="w-full p-2.5 border rounded-lg dark:bg-slate-700 dark:border-slate-600 dark:text-white text-sm" placeholder="Contoh: Staff Tiketing">
                                <p class="text-[10px] text-slate-400 mt-1">Gunakan 'Keuangan', 'Pimpinan', 'IT' untuk akses Admin.</p>
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Penempatan</label>
                                <select v-model="form.placement" class="w-full p-2.5 border rounded-lg dark:bg-slate-700 dark:border-slate-600 dark:text-white text-sm">
                                    <option value="Padang">Padang</option>
                                    <option value="Bukittinggi">Bukittinggi</option>
                                    <option value="Payakumbuh">Payakumbuh</option>
                                    <option value="Semua">Semua</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="p-5 border-t border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 flex justify-end gap-3">
                        <button @click="closeModal" class="px-4 py-2 text-slate-600 font-bold hover:bg-slate-200 rounded-lg transition-colors text-sm">Batal</button>
                        <button @click="saveUser" :disabled="isLoading" class="px-6 py-2 bg-sr-blue dark:bg-blue-600 text-white font-bold rounded-lg shadow-lg hover:bg-slate-800 transition-colors text-sm disabled:opacity-50">{{ isLoading ? 'Menyimpan...' : 'Simpan' }}</button>
                    </div>
                </div>
            </div>

        </main>
    </div>

    <script>
        const { createApp } = Vue;

        createApp({
            data() {
                return {
                    isDarkMode: localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches),
                    users: [],
                    isModalVisible: false,
                    showInputPassword: false, // For modal
                    modalMode: 'add',
                    isLoading: false,
                    form: {
                        id: '',
                        name: '',
                        username: '',
                        password: '', 
                        position: '',
                        placement: 'Padang'
                    }
                }
            },
            mounted() {
                if (this.isDarkMode) document.documentElement.classList.add('dark');
                this.fetchUsers();
            },
            methods: {
                toggleDarkMode() {
                    this.isDarkMode = !this.isDarkMode;
                    if (this.isDarkMode) {
                        document.documentElement.classList.add('dark');
                        localStorage.theme = 'dark';
                    } else {
                        document.documentElement.classList.remove('dark');
                        localStorage.theme = 'light';
                    }
                },
                fetchUsers() {
                    fetch('api.php?action=get_users')
                        .then(res => res.json())
                        .then(data => {
                            if(data.users) {
                                // Add reactive property for hash toggle
                                this.users = data.users.map(u => ({...u, showPlain: false}));
                            }
                        });
                },
                openModal(mode, data = null) {
                    this.modalMode = mode;
                    this.showInputPassword = false;
                    if (mode === 'edit' && data) {
                        this.form = { ...data, password: '' }; // Don't show hash in input
                    } else {
                        this.form = { id: '', name: '', username: '', password: '', position: '', placement: 'Padang' };
                    }
                    this.isModalVisible = true;
                },
                closeModal() {
                    this.isModalVisible = false;
                },
                saveUser() {
                    if (!this.form.name || !this.form.username || !this.form.position) {
                        Swal.fire('Data Kurang', 'Mohon lengkapi data wajib.', 'warning');
                        return;
                    }
                    
                    if (this.modalMode === 'add' && !this.form.password) {
                        Swal.fire('Password Wajib', 'Password wajib diisi untuk user baru.', 'warning');
                        return;
                    }

                    this.isLoading = true;
                    fetch('api.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({...this.form, action: 'save_user', mode: this.modalMode})
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.isLoading = false;
                        if (data.success) {
                            this.closeModal();
                            this.fetchUsers();
                            Swal.fire('Berhasil', 'Data user berhasil disimpan.', 'success');
                        } else {
                            Swal.fire('Gagal', 'Gagal menyimpan: ' + (data.message || 'Error Unknown'), 'error');
                        }
                    });
                },
                async deleteUser(user) {
                    const result = await Swal.fire({
                        title: 'Hapus User?',
                        text: `Hapus user ${user.name}?`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Hapus',
                        cancelButtonText: 'Batal',
                        confirmButtonColor: '#d33'
                    });

                    if (result.isConfirmed) {
                        fetch('api.php', {
                            method: 'POST',
                            headers: {'Content-Type': 'application/json'},
                            body: JSON.stringify({id: user.id, action: 'delete_user'})
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire('Terhapus!', 'User berhasil dihapus.', 'success');
                                this.fetchUsers();
                            } else {
                                Swal.fire('Gagal', 'Gagal menghapus user.', 'error');
                            }
                        });
                    }
                },
                formatDate(dateStr) {
                    if (!dateStr) return '-';
                    return new Date(dateStr).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
                }
            }
        }).mount('#app');
    </script>
</body>
</html>
