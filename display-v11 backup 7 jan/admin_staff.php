<!DOCTYPE html>
<html lang="id" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Admin & Staff - Sutan Raya</title>
    <link rel="icon" type="image/webp" href="../image/logo.webp">
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
        <aside class="w-64 bg-white dark:bg-slate-800 border-r border-slate-200 dark:border-slate-700 flex flex-col z-20 flex-shrink-0 h-full shadow-sm">
            <div class="h-16 flex items-center justify-center border-b border-slate-100 dark:border-slate-700">
                <div class="text-xl font-extrabold text-sr-blue dark:text-white tracking-tight flex items-center gap-2">
                    <img src="../image/logo.png" alt="Sutan Raya" class="w-8 h-8 object-contain"> Sutan<span class="text-blue-600 dark:text-blue-400">Raya</span>
                </div>
            </div>
            <nav class="flex-1 overflow-y-auto p-3 space-y-1 custom-scrollbar">
                <a href="index.php" class="flex items-center px-3 py-2.5 rounded-lg text-sm text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                    <i class="bi bi-arrow-left-circle-fill w-6"></i> Kembali ke Dashboard
                </a>
                <!-- <div class="text-[10px] font-bold text-slate-400 uppercase px-3 mb-2 mt-6 tracking-wider">Laporan</div>
                 <a href="reports.php" class="flex items-center px-3 py-2.5 rounded-lg text-sm text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                    <i class="bi bi-bar-chart-fill w-6"></i> Statistik & Grafik
                </a> -->
                <div class="text-[10px] font-bold text-slate-400 uppercase px-3 mb-2 mt-6 tracking-wider">Pengaturan</div>
                <a href="#" class="flex items-center px-3 py-2.5 rounded-lg text-sm bg-blue-50 dark:bg-slate-700 text-blue-700 dark:text-blue-300 font-bold transition-colors">
                    <i class="bi bi-people-fill w-6"></i> Admin & Staff
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col min-w-0 bg-slate-50 dark:bg-slate-900 relative h-full overflow-hidden">
            <header class="h-16 bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between px-6 shadow-sm z-10">
                <h2 class="text-lg font-bold text-slate-800 dark:text-white">Kelola Admin & Staff</h2>
                <div class="flex items-center gap-3">
                    <button @click="openModal('add')" class="bg-sr-blue dark:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-bold shadow-lg hover:bg-slate-800 transition-colors">
                        <i class="bi bi-plus-lg mr-2"></i> Tambah User
                    </button>
                    <button @click="toggleDarkMode" class="w-9 h-9 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center"><i :class="isDarkMode ? 'bi-sun-fill' : 'bi-moon-stars-fill'"></i></button>
                </div>
            </header>

            <div class="flex-1 overflow-y-auto p-6 custom-scrollbar">
                
                <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-slate-500 uppercase bg-slate-50 dark:bg-slate-700 dark:text-slate-300">
                                <tr>
                                    <th class="px-6 py-4">Nama User</th>
                                    <th class="px-6 py-4">Username</th>
                                    <th class="px-6 py-4">Jabatan</th>
                                    <th class="px-6 py-4">Penempatan</th>
                                    <th class="px-6 py-4">Terdaftar</th>
                                    <th class="px-6 py-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                <tr v-if="users.length === 0">
                                    <td colspan="5" class="px-6 py-8 text-center text-slate-400 italic">Belum ada data user.</td>
                                </tr>
                                <tr v-for="user in users" :key="user.id" class="bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                                    <td class="px-6 py-4 font-bold text-slate-800 dark:text-white flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-600 flex items-center justify-center text-slate-500 dark:text-slate-300"><i class="bi bi-person-fill"></i></div>
                                        {{ user.name }}
                                    </td>
                                    <td class="px-6 py-4 text-slate-600 dark:text-slate-300 font-mono">{{ user.username }}</td>
                                    <td class="px-6 py-4 text-slate-700 dark:text-slate-200 font-bold">{{ user.position }}</td>
                                    <td class="px-6 py-4">
                                        <span class="px-2.5 py-0.5 rounded text-[10px] font-bold uppercase bg-blue-100 text-blue-700 border border-blue-200">{{ user.placement }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-slate-500 dark:text-slate-400 text-xs">{{ formatDate(user.created_at) }}</td>
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
                        
                        <!-- Password Hidden as requested -->

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Jabatan</label>
                                <input type="text" v-model="form.position" class="w-full p-2.5 border rounded-lg dark:bg-slate-700 dark:border-slate-600 dark:text-white text-sm" placeholder="Contoh: Staff Tiketing">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Penempatan</label>
                                <select v-model="form.placement" class="w-full p-2.5 border rounded-lg dark:bg-slate-700 dark:border-slate-600 dark:text-white text-sm">
                                    <option value="Padang">Padang</option>
                                    <option value="Bukittinggi">Bukittinggi</option>
                                    <option value="Payakumbuh">Payakumbuh</option>
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
                    modalMode: 'add',
                    isLoading: false,
                    form: {
                        id: '',
                        name: '',
                        username: '',
                        password: '',
                        role: 'Staff'
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
                            if(data.users) this.users = data.users;
                        });
                },
                openModal(mode, data = null) {
                    this.modalMode = mode;
                    if (mode === 'edit' && data) {
                        this.form = { ...data, password: '' };
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
                        alert('Mohon lengkapi data wajib.');
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
                        } else {
                            alert('Gagal menyimpan: ' + (data.message || 'Error Unknown'));
                        }
                    });
                },
                deleteUser(user) {
                    if (confirm('Hapus user ' + user.name + '?')) {
                        fetch('api.php', {
                            method: 'POST',
                            headers: {'Content-Type': 'application/json'},
                            body: JSON.stringify({id: user.id, action: 'delete_user'})
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) this.fetchUsers();
                            else alert('Gagal menghapus');
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
