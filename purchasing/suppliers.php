<!DOCTYPE html>
<html lang="id" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Management - Purchasing Sutan Raya</title>
    <link rel="icon" type="image/webp" href="../image/logo.webp">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; transition: background-color 0.3s; }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #475569; }
        [v-cloak] { display: none; }
    </style>
    <script>
        tailwind.config = { darkMode: 'class', theme: { extend: { colors: { 'sr-blue': '#0f172a' } } } }
    </script>
</head>
<body class="h-screen w-screen bg-slate-50 text-slate-800 dark:bg-slate-900 dark:text-slate-100">

    <div id="app" class="flex h-full w-full" v-cloak>
        
        <?php $currentPage = 'purchasing_suppliers'; include 'components/sidebar.php'; ?>

        <main class="flex-1 flex flex-col min-w-0 bg-slate-50 dark:bg-slate-900 overflow-hidden relative">
            <header class="h-16 bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between px-4 md:px-6 shadow-sm z-10 shrink-0">
                <div class="flex items-center gap-3">
                    <button onclick="toggleSidebar()" class="lg:hidden text-slate-600 dark:text-slate-300 hover:text-blue-600 p-2 -ml-2">
                        <i class="bi bi-list text-2xl"></i>
                    </button>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800 dark:text-white">Data Supplier</h2>
                        <p class="text-xs text-slate-500">Buku alamat vendor/toko langganan kita. Catat nomor HP dan alamat supplier di sini agar mudah saat mau pesan barang.</p>
                    </div>
                </div>
                <button @click="openModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-3 md:px-4 py-2 rounded-xl text-xs md:text-sm font-bold flex items-center gap-2 transition-all shadow-lg shadow-blue-200 dark:shadow-none">
                    <i class="bi bi-person-plus-fill"></i> <span class="hidden sm:inline">Tambah Supplier</span><span class="sm:hidden">Tambah</span>
                </button>
            </header>

            <div class="flex-1 overflow-y-auto custom-scrollbar p-6">
                <!-- Supplier Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="s in suppliers" :key="s.id" class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-xl transition-all group relative">
                         <!-- Rating Badge -->
                         <div class="absolute top-6 right-6 flex items-center gap-1 bg-yellow-50 dark:bg-yellow-900/20 px-2 py-1 rounded-lg border border-yellow-100 dark:border-yellow-700/30">
                             <i class="bi bi-star-fill text-yellow-400 text-xs"></i>
                             <span class="text-xs font-bold text-yellow-700 dark:text-yellow-400">{{ s.rating }}</span>
                         </div>

                         <div class="w-14 h-14 rounded-2xl bg-blue-100 dark:bg-blue-900/30 text-blue-600 flex items-center justify-center text-2xl mb-4 group-hover:scale-110 transition-transform">
                             <i class="bi bi-shop"></i>
                         </div>
                         
                         <h3 class="font-bold text-lg text-slate-800 dark:text-white mb-1">{{ s.name }}</h3>
                         <div class="flex flex-col gap-2 mt-4 text-sm text-slate-500">
                             <div class="flex items-center gap-3">
                                 <i class="bi bi-person w-5"></i> {{ s.contactPerson }}
                             </div>
                             <div class="flex items-center gap-3">
                                 <i class="bi bi-telephone w-5"></i> {{ s.phone }}
                             </div>
                             <div class="flex items-center gap-3">
                                 <i class="bi bi-envelope w-5"></i> {{ s.email || '-' }}
                             </div>
                             <div class="flex items-start gap-3">
                                 <i class="bi bi-geo-alt w-5 mt-0.5"></i> 
                                 <span class="leading-tight text-xs">{{ s.address }}</span>
                             </div>
                         </div>

                             <button @click="confirmDelete(s)" class="flex-1 py-2 text-xs font-bold text-red-600 bg-red-50 hover:bg-red-100 dark:bg-red-900/30 dark:text-red-300 dark:hover:bg-red-900/50 rounded-xl transition">
                                 Hapus
                             </button>
                             <button @click="editSupplier(s)" class="flex-1 py-2 text-xs font-bold text-blue-600 bg-blue-50 hover:bg-blue-100 dark:bg-blue-900/30 dark:text-blue-300 dark:hover:bg-blue-900/50 rounded-xl transition">
                                 Edit
                             </button>
                         </div>
                    </div>
                </div>
            </div>

            <!-- Modal Add/Edit Supplier -->
            <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm animate-fade-in">
                <div class="bg-white dark:bg-slate-800 w-full max-w-lg rounded-3xl shadow-2xl p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="font-bold text-lg">Input Supplier Baru</h3>
                        <button @click="showModal = false" class="text-slate-400 hover:text-red-500"><i class="bi bi-x-lg"></i></button>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="text-xs font-bold text-slate-500 block mb-1">Nama Toko / PT</label>
                            <input type="text" v-model="form.name" class="w-full p-3 rounded-xl border border-slate-200 dark:border-slate-600 text-sm font-bold">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-bold text-slate-500 block mb-1">Kontak Person</label>
                                <input type="text" v-model="form.contactPerson" class="w-full p-3 rounded-xl border border-slate-200 dark:border-slate-600 text-sm">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-500 block mb-1">No. HP / WA</label>
                                <input type="text" v-model="form.phone" class="w-full p-3 rounded-xl border border-slate-200 dark:border-slate-600 text-sm">
                            </div>
                        </div>
                        <div>
                            <label class="text-xs font-bold text-slate-500 block mb-1">Alamat Lengkap</label>
                            <textarea v-model="form.address" class="w-full p-3 rounded-xl border border-slate-200 dark:border-slate-600 text-sm h-24"></textarea>
                        </div>
                         <div>
                            <label class="text-xs font-bold text-slate-500 block mb-1">Rating Awal</label>
                            <select v-model="form.rating" class="w-full p-3 rounded-xl border border-slate-200 dark:border-slate-600 text-sm font-bold">
                                <option value="5.0">5.0 - Excellent</option>
                                <option value="4.0">4.0 - Good</option>
                                <option value="3.0">3.0 - Average</option>
                            </select>
                        </div>
                        <button @click="saveSupplier" class="w-full py-3 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition shadow-lg mt-4">Simpan Data</button>
                    </div>
                </div>
            </div>

        </main>
        
        <?php include 'components/sidebar_right.php'; ?>
        
    </div>

    <script>
        const { createApp, ref, onMounted } = Vue;

        createApp({
            setup() {
                const showModal = ref(false);
                const form = ref({ name: '', contactPerson: '', phone: '', address: '', rating: '5.0' });
                const suppliers = ref([]);

                const fetchSuppliers = async () => {
                    try {
                        const res = await fetch('api.php?action=get_suppliers');
                        const data = await res.json();
                        if(data.status === 'success') {
                            suppliers.value = data.data.map(s => ({
                                id: s.id,
                                name: s.name,
                                contactPerson: s.contact_person,
                                phone: s.phone,
                                email: s.email || '-',
                                address: s.address,
                                rating: s.rating
                            }));
                        }
                    } catch(e) { console.error(e); }
                };

                const confirmDelete = (supplier) => {
                    Swal.fire({
                        title: 'Hapus Supplier?',
                        text: `Yakin ingin menghapus ${supplier.name}?`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Hapus',
                        cancelButtonText: 'Batal'
                    }).then(async (result) => {
                        if (result.isConfirmed) {
                            try {
                                const res = await fetch(`api.php?action=delete_supplier&id=${supplier.id}`);
                                const data = await res.json();
                                if(data.status === 'success') {
                                    Swal.fire('Terhapus!', 'Supplier berhasil dihapus', 'success');
                                    fetchSuppliers();
                                } else {
                                    Swal.fire('Gagal', data.message, 'error');
                                }
                            } catch(e) {
                                Swal.fire('Error', 'Gagal koneksi server', 'error');
                            }
                        }
                    });
                };

                const editSupplier = (supplier) => {
                    form.value = { 
                        id: supplier.id,
                        name: supplier.name, 
                        contactPerson: supplier.contactPerson, 
                        phone: supplier.phone, 
                        address: supplier.address, 
                        rating: supplier.rating 
                    };
                    showModal.value = true;
                };

                const saveSupplier = async () => {
                    if(!form.value.name || !form.value.phone) {
                        Swal.fire('Error', 'Nama dan No HP Wajib diisi', 'error');
                        return;
                    }

                    const action = form.value.id ? 'update_supplier' : 'create_supplier';
                    
                    // Fix: Map camelCase to snake_case for backend
                    const payload = {
                        ...form.value,
                        contact_person: form.value.contactPerson,
                        code: form.value.code || ('SUP-' + Math.floor(Math.random() * 10000)) // Ensure code exists
                    };

                    try {
                        const res = await fetch(`api.php?action=${action}`, {
                            method: 'POST',
                            body: JSON.stringify(payload)
                        });
                        const data = await res.json();
                        if(data.status === 'success') {
                            Swal.fire('Sukses', `Supplier berhasil ${form.value.id ? 'diupdate' : 'ditambahkan'}`, 'success');
                            showModal.value = false;
                            fetchSuppliers();
                        } else {
                            Swal.fire('Gagal', data.message, 'error');
                        }
                    } catch(e) {
                         Swal.fire('Error', 'Gagal koneksi server', 'error');
                    }
                };

                const openModal = () => {
                    form.value = { name: '', contactPerson: '', phone: '', address: '', rating: '5.0' };
                    showModal.value = true;
                };

                onMounted(() => {
                    fetchSuppliers();
                });

                return { showModal, suppliers, form, openModal, saveSupplier, editSupplier, confirmDelete };
            }
        }).mount('#app');
    </script>
</body>
</html>
