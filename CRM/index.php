<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sutan Raya CRM</title>
    <!-- Tailwind CSS -->
    <!-- Meta Tags -->
    <meta name="description" content="Aplikasi Manajemen Hubungan Pelanggan (CRM) Sutan Raya. Kelola data konsumen, riwayat transaksi, dan statistik penjualan dalam satu dashboard terintegrasi.">
    <meta name="keywords" content="sutan raya, crm, travel, manajemen pelanggan, padang, sumatera barat">
    <meta name="author" content="Sutan Raya Travel">
    <meta name="theme-color" content="#6366f1">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <!-- <meta property="og:url" content="http://localhost/travel-sr-fe/CRM/"> -->
    <meta property="og:title" content="Sutan Raya CRM - Customer Relationship Management">
    <meta property="og:description" content="Platform manajemen data konsumen dan analisis penjualan travel Sutan Raya.">
    <meta property="og:image" content="../image/logo.png">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <!-- <meta property="twitter:url" content="http://localhost/travel-sr-fe/CRM/"> -->
    <meta property="twitter:title" content="Sutan Raya CRM">
    <meta property="twitter:description" content="Platform manajemen data konsumen dan analisis penjualan travel Sutan Raya.">
    <meta property="twitter:image" content="../image/logo.png">

    <!-- Favicon -->
    <link rel="icon" type="image/webp" href="../image/logo.webp">
    <link rel="apple-touch-icon" href="../image/logo.png">
      
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Vue.js -->
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        [v-cloak] { display: none; }
        .glass-panel {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }
    </style>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#1e293b', // Soft Black (Slate 800)
                        gold: '#d4af37', // SR Gold
                        'gold-light': '#fefce8', // Pastel Yellow
                        'gold-dark': '#b45309', 
                        accent: '#d4af37',
                    },
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-[#f8fafc] text-slate-600 font-sans h-screen overflow-hidden selection:bg-gold/20 selection:text-gold-dark">
    <div id="app" class="flex h-full relative" v-cloak>
        
        <!-- LOGIN SCREEN -->
        <div v-if="!isAuthenticated" class="absolute inset-0 z-[100] bg-[#f8fafc] flex items-center justify-center p-6">
            <div class="w-full max-w-sm bg-white p-8 rounded-[2.5rem] shadow-2xl shadow-slate-200/50 border border-slate-50 animate-fade-in relative overflow-hidden">
                <!-- Decorative Circle -->
                <div class="absolute -top-10 -right-10 w-32 h-32 bg-gold/10 rounded-full blur-2xl"></div>
                <div class="absolute -bottom-10 -left-10 w-32 h-32 bg-blue-500/5 rounded-full blur-2xl"></div>

                <div class="text-center mb-8 relative">
                    <div class="w-20 h-20 mx-auto bg-gradient-to-br from-[#fefce8] to-[#fef08a] rounded-3xl flex items-center justify-center shadow-inner mb-4">
                        <img src="../image/logo.png" class="w-10 h-10 object-contain">
                    </div>
                    <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Sutan Raya <span class="text-gold">CRM</span></h1>
                    <p class="text-xs text-slate-400 mt-2 font-medium">Please sign in to continue</p>
                </div>

                <form @submit.prevent="doLogin" class="space-y-5 relative">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5 ml-1">Username</label>
                        <input type="text" v-model="loginForm.user" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-100 rounded-2xl focus:outline-none focus:ring-2 focus:ring-gold/50 focus:border-gold transition-all text-sm font-bold text-slate-700 placeholder:font-normal placeholder:text-slate-300" placeholder="Enter username">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5 ml-1">Password</label>
                        <div class="relative">
                            <input :type="loginForm.showPass ? 'text' : 'password'" v-model="loginForm.pass" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-100 rounded-2xl focus:outline-none focus:ring-2 focus:ring-gold/50 focus:border-gold transition-all text-sm font-bold text-slate-700 placeholder:font-normal placeholder:text-slate-300" placeholder="••••••••">
                            <button type="button" @click="loginForm.showPass = !loginForm.showPass" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                <i class="bi" :class="loginForm.showPass ? 'bi-eye-slash-fill' : 'bi-eye-fill'"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div v-if="loginForm.error" class="bg-red-50 text-red-500 text-xs py-3 px-4 rounded-xl flex items-center gap-2">
                        <i class="bi bi-exclamation-circle-fill"></i> {{ loginForm.error }}
                    </div>

                    <button type="submit" class="w-full bg-slate-900 text-white py-4 rounded-2xl font-bold text-sm hover:bg-slate-800 hover:shadow-lg hover:shadow-slate-200 hover:-translate-y-0.5 transition-all duration-300">
                        Sign In
                    </button>
                </form>
            </div>
        </div>

        <!-- APP CONTENT -->
        <template v-else>
            <!-- Mobile Sidebar Overlay -->
            <div v-if="mobileMenuOpen" class="fixed inset-0 bg-slate-900/20 backdrop-blur-sm z-40 lg:hidden" @click="mobileMenuOpen = false"></div>

            <!-- Sidebar -->
            <aside :class="mobileMenuOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'" class="fixed lg:static inset-y-0 left-0 z-50 w-72 bg-white border-r border-slate-100 flex flex-col transition-transform duration-300 ease-spring">
                <div class="h-24 flex items-center px-8 border-b border-slate-50">
                    <div class="text-xl font-extrabold text-slate-900 tracking-tight flex items-center gap-3">
                        <img src="../image/logo.png" alt="Sutan Raya" class="w-9 h-9 object-contain"> 
                        <div>
                            <span class="block leading-none">Sutan<span class="text-gold">Raya</span></span>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] block mt-1">CRM System</span>
                        </div>
                    </div>
                </div>
                
                <nav class="flex-1 p-6 space-y-3">
                    <button @click="view='dashboard'; mobileMenuOpen=false" :class="view==='dashboard'?'bg-[#fefce8] text-[#854d0e] inner-shadow':'text-slate-500 hover:bg-slate-50 hover:text-slate-900'" class="w-full flex items-center p-4 rounded-[1.2rem] transition-all group relative overflow-hidden">
                        <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-8 bg-gold rounded-r-full transition-all duration-300" :class="view==='dashboard'?'opacity-100':'opacity-0'"></div>
                        <i class="bi bi-grid-fill text-xl lg:mr-4 mr-3 transition-colors" :class="view==='dashboard'?'text-gold':'text-slate-300 group-hover:text-slate-500'"></i> 
                        <span class="font-bold text-sm tracking-wide">Dashboard</span>
                    </button>
                    <button @click="view='contacts'; mobileMenuOpen=false" :class="view==='contacts'?'bg-[#fefce8] text-[#854d0e] inner-shadow':'text-slate-500 hover:bg-slate-50 hover:text-slate-900'" class="w-full flex items-center p-4 rounded-[1.2rem] transition-all group relative overflow-hidden">
                        <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-8 bg-gold rounded-r-full transition-all duration-300" :class="view==='contacts'?'opacity-100':'opacity-0'"></div>
                        <i class="bi bi-people-fill text-xl lg:mr-4 mr-3 transition-colors" :class="view==='contacts'?'text-gold':'text-slate-300 group-hover:text-slate-500'"></i> 
                        <span class="font-bold text-sm tracking-wide">Data Konsumen</span>
                    </button>
                </nav>

                <div class="p-6 border-t border-slate-50">
                    <button @click="logout" class="w-full flex items-center justify-center p-4 rounded-[1.2rem] text-red-400 hover:bg-red-50 hover:text-red-500 transition-all font-bold text-xs mb-2">
                        <i class="bi bi-box-arrow-right text-lg mr-2"></i> Logout
                    </button>
                    <a href="../display-v11/index.php" class="w-full flex items-center justify-center p-4 rounded-[1.2rem] bg-slate-900 text-white hover:bg-slate-800 transition-all font-bold text-xs shadow-lg shadow-slate-200">
                        <i class="bi bi-arrow-left text-lg mr-2"></i> Back to App
                    </a>
                </div>
            </aside>

            <!-- Main Content -->
            <main class="flex-1 flex flex-col min-w-0 bg-[#f8fafc] overflow-hidden relative">
                <!-- Soft Background Pattern -->
                <div class="absolute inset-0 z-0 opacity-40 pointer-events-none" style="background-image: radial-gradient(#cbd5e1 1px, transparent 1px); background-size: 32px 32px;"></div>
                
                <!-- Header -->
                <header class="h-24 bg-transparent flex items-center justify-between px-6 lg:px-10 z-10 sticky top-0">
                    <div class="flex items-center gap-4">
                        <button @click="mobileMenuOpen = !mobileMenuOpen" class="w-10 h-10 bg-white rounded-xl shadow-sm text-slate-500 lg:hidden flex items-center justify-center active:scale-95 transition-transform">
                            <i class="bi bi-list text-xl"></i>
                        </button>
                        <div>
                            <h1 class="text-2xl lg:text-3xl font-extrabold text-slate-800 tracking-tight">{{ pageTitle }}</h1>
                            <p class="text-xs text-slate-400 font-bold uppercase tracking-widest mt-1 hidden sm:block">{{ currentDate }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="bg-white px-5 py-2.5 rounded-full shadow-sm text-xs font-bold border border-slate-100 flex items-center gap-2 text-slate-600">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span> <span class="hidden sm:inline">Online</span>
                        </div>
                        <div class="w-10 h-10 bg-white rounded-full border border-slate-100 shadow-sm flex items-center justify-center">
                             <i class="bi bi-bell-fill text-slate-400"></i>
                        </div>
                    </div>
                </header>

                <!-- Content Area -->
                <div class="flex-1 overflow-y-auto px-6 lg:px-10 pb-10 z-10 custom-scrollbar">
                    
                    <!-- Dashboard View -->
                    <div v-if="view === 'dashboard'" class="animate-fade-in space-y-8">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Soft Gradient Cards -->
                            <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100/50 hover:shadow-xl hover:shadow-slate-200/50 transition-all duration-300 group relative overflow-hidden">
                                <div class="absolute top-0 right-0 w-24 h-24 bg-blue-50 rounded-bl-[4rem] transition-transform group-hover:scale-110"></div>
                                <div class="relative">
                                    <div class="w-12 h-12 bg-blue-50 text-blue-500 rounded-2xl flex items-center justify-center text-xl mb-4 group-hover:scale-110 transition-transform duration-300 shadow-sm"><i class="bi bi-people-fill"></i></div>
                                    <div class="text-4xl font-black text-slate-800 tracking-tight mb-1">{{ stats.totalCustomers }}</div>
                                    <div class="text-sm font-bold text-slate-400 flex items-center gap-2">Total Konsumen <span class="bg-emerald-50 text-emerald-600 text-[10px] px-2 py-0.5 rounded-full">+{{ stats.newCustomers }} Baru</span></div>
                                </div>
                            </div>

                            <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100/50 hover:shadow-xl hover:shadow-slate-200/50 transition-all duration-300 group relative overflow-hidden">
                                <div class="absolute top-0 right-0 w-24 h-24 bg-amber-50 rounded-bl-[4rem] transition-transform group-hover:scale-110"></div>
                                <div class="relative">
                                    <div class="w-12 h-12 bg-amber-50 text-amber-500 rounded-2xl flex items-center justify-center text-xl mb-4 group-hover:scale-110 transition-transform duration-300 shadow-sm"><i class="bi bi-wallet2"></i></div>
                                    <div class="text-4xl font-black text-slate-800 tracking-tight mb-1">{{ formatRupiahSimple(stats.totalRevenue) }}</div>
                                    <div class="text-sm font-bold text-slate-400">Total Pendapatan</div>
                                </div>
                            </div>

                            <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100/50 hover:shadow-xl hover:shadow-slate-200/50 transition-all duration-300 group relative overflow-hidden">
                                <div class="absolute top-0 right-0 w-24 h-24 bg-purple-50 rounded-bl-[4rem] transition-transform group-hover:scale-110"></div>
                                <div class="relative">
                                    <div class="w-12 h-12 bg-purple-50 text-purple-500 rounded-2xl flex items-center justify-center text-xl mb-4 group-hover:scale-110 transition-transform duration-300 shadow-sm"><i class="bi bi-arrow-repeat"></i></div>
                                    <div class="text-4xl font-black text-slate-800 tracking-tight mb-1">{{ stats.repeatRate }}%</div>
                                    <div class="text-sm font-bold text-slate-400">Repeat Order Rate</div>
                                </div>
                            </div>
                        </div>

                        <!-- Top Customers Card -->
                        <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100/50 overflow-hidden">
                            <div class="p-8 border-b border-slate-50 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                                <div>
                                    <h3 class="font-extrabold text-xl text-slate-800">Top Pelanggan Sultan</h3>
                                    <p class="text-slate-400 text-sm font-medium mt-1">5 pelanggan dengan transaksi tertinggi</p>
                                </div>
                                <button @click="view='contacts'" class="px-5 py-2.5 bg-slate-50 text-slate-600 rounded-xl text-xs font-bold hover:bg-gold hover:text-white transition-all">Lihat Semua</button>
                            </div>
                            <div class="p-4 grid grid-cols-1 gap-3">
                                <div v-for="(c, i) in topCustomers" :key="i" class="flex items-center justify-between p-4 hover:bg-[#f8fafc] rounded-3xl transition-all cursor-pointer group" @click="openContact(c)">
                                    <div class="flex items-center gap-4">
                                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-[#fefce8] to-[#fde047] text-[#854d0e] flex items-center justify-center font-black text-lg shadow-inner">{{ getInitials(c.name) }}</div>
                                        <div>
                                            <div class="font-bold text-slate-800 text-base mb-1 group-hover:text-gold transition-colors">{{ c.name }}</div>
                                            <div class="flex gap-2">
                                                 <span class="bg-slate-100 text-slate-500 text-[10px] font-bold px-2.5 py-1 rounded-lg">{{ c.totalTrips }} Perjalanan</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="font-mono font-bold text-slate-800 text-sm md:text-base">{{ formatRupiah(c.totalRevenue) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contacts View -->
                    <div v-if="view === 'contacts'" class="animate-fade-in h-full flex flex-col">
                        <div class="bg-white p-2 rounded-[1.5rem] shadow-sm border border-slate-100 mb-6 flex items-center">
                            <div class="w-12 h-12 flex items-center justify-center text-slate-400"><i class="bi bi-search text-xl"></i></div>
                            <input type="text" v-model="contactSearch" placeholder="Cari nama pelanggan atau nomor handphone..." class="w-full h-12 bg-transparent border-none text-slate-700 font-bold placeholder:font-medium placeholder:text-slate-300 focus:ring-0 text-sm">
                        </div>

                        <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 flex-1 flex flex-col overflow-hidden relative">
                             <div class="absolute top-0 left-0 w-full h-10 bg-gradient-to-b from-white to-transparent pointer-events-none z-10"></div>
                             
                            <div class="overflow-x-auto flex-1 custom-scrollbar">
                                <table class="w-full text-left border-collapse min-w-[800px]">
                                    <thead class="bg-white text-slate-400 font-bold text-[11px] uppercase tracking-wider sticky top-0 z-20">
                                        <tr>
                                            <th class="p-6">Profil</th>
                                            <th class="p-6">Kontak</th>
                                            <th class="p-6 text-center">Statistik</th>
                                            <th class="p-6 text-right">Last Seen</th>
                                            <th class="p-6 text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50">
                                        <tr v-for="c in filteredContacts" :key="c.phone" class="hover:bg-[#fefce8]/50 transition-colors group">
                                            <td class="p-6">
                                                <div class="flex items-center gap-4">
                                                    <div class="w-12 h-12 rounded-2xl bg-slate-50 text-slate-600 flex items-center justify-center font-black text-sm group-hover:bg-gold group-hover:text-white transition-all shadow-sm">{{ getInitials(c.name) }}</div>
                                                    <div class="font-bold text-slate-700 text-sm group-hover:text-slate-900">{{ c.name || 'Tanpa Nama' }}</div>
                                                </div>
                                            </td>
                                            <td class="p-6">
                                                <div class="flex items-center gap-3">
                                                    <div class="bg-slate-50 px-3 py-1.5 rounded-xl border border-slate-100 text-slate-500 font-mono text-xs font-bold group-hover:border-gold/20 group-hover:bg-gold/5 transition-colors">{{ normalizePhone(c.phone) }}</div>
                                                    <a :href="getWaLink(c.phone)" target="_blank" class="w-8 h-8 rounded-full bg-emerald-50 text-emerald-500 hover:bg-emerald-500 hover:text-white flex items-center justify-center transition-all">
                                                        <i class="bi bi-whatsapp"></i>
                                                    </a>
                                                </div>
                                            </td>
                                            <td class="p-6 text-center">
                                                <div class="inline-flex flex-col items-center">
                                                    <span class="text-sm font-black text-slate-800">{{ c.totalTrips }}</span>
                                                    <span class="text-[9px] text-slate-400 font-bold uppercase mt-0.5">Trips</span>
                                                </div>
                                            </td>
                                            <td class="p-6 text-right">
                                                <span class="text-xs font-bold text-slate-400">{{ formatDate(c.lastTrip) }}</span>
                                            </td>
                                            <td class="p-6 text-center">
                                                <button @click="openContact(c)" class="w-10 h-10 rounded-xl bg-slate-50 text-slate-400 hover:bg-slate-900 hover:text-white flex items-center justify-center transition-all">
                                                    <i class="bi bi-arrow-right-short text-xl"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <tr v-if="filteredContacts.length === 0">
                                            <td colspan="5" class="p-16 text-center">
                                                <div class="w-20 h-20 bg-slate-50 rounded-full mx-auto flex items-center justify-center text-slate-300 text-3xl mb-4"><i class="bi bi-folder-x"></i></div>
                                                <p class="text-slate-400 font-medium">Tidak ada data ditemukan</p>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </main>

            <!-- Soft Detail Drawer -->
            <div v-if="selectedContact" class="fixed inset-0 z-[60] flex justify-end">
                <div class="absolute inset-0 bg-slate-900/10 backdrop-blur-sm transition-opacity" @click="closeContact"></div>
                
                <div class="relative w-full max-w-md bg-white h-full shadow-2xl flex flex-col animate-slide-in-right">
                    <div class="absolute top-0 left-0 w-full h-40 bg-[#f8fafc] z-0"></div>
                    <button @click="closeContact" class="absolute top-6 left-6 z-10 w-10 h-10 bg-white rounded-full shadow-lg text-slate-400 hover:text-slate-800 flex items-center justify-center transition-all"><i class="bi bi-arrow-left"></i></button>

                    <div class="relative z-10 px-8 pt-20 pb-8 text-center">
                        <div class="w-28 h-28 mx-auto rounded-[2rem] bg-gradient-to-br from-[#fefce8] to-[#fde047] shadow-xl border-4 border-white flex items-center justify-center text-4xl font-black text-[#854d0e] mb-4">{{ getInitials(selectedContact.name) }}</div>
                        <h2 class="text-2xl font-black text-slate-800 tracking-tight">{{ selectedContact.name }}</h2>
                        <div class="inline-flex items-center gap-2 mt-2 px-4 py-1.5 rounded-full bg-slate-50 border border-slate-100 text-slate-500 font-mono text-xs font-bold">
                            <i class="bi bi-whatsapp text-emerald-500"></i> {{ normalizePhone(selectedContact.phone) }}
                        </div>
                        
                        <div class="flex gap-3 mt-8">
                            <a :href="getWaLink(selectedContact.phone)" target="_blank" class="flex-1 bg-gradient-to-r from-emerald-500 to-emerald-400 text-white py-4 rounded-2xl font-bold text-sm shadow-lg shadow-emerald-500/20 hover:shadow-xl hover:-translate-y-1 transition-all flex items-center justify-center gap-2">
                                <i class="bi bi-chat-text-fill"></i> Chat WhatsApp
                            </a>
                        </div>
                    </div>

                    <div class="flex-1 overflow-y-auto bg-white px-8 pb-8 custom-scrollbar relative">
                         <!-- Stats Grid -->
                        <div class="grid grid-cols-2 gap-4 mb-8">
                            <div class="bg-[#f8fafc] p-5 rounded-3xl border border-slate-50">
                                <div class="text-[10px] text-slate-400 uppercase font-black tracking-widest mb-2">Total Belanja</div>
                                <div class="text-lg font-black text-slate-800">{{ formatRupiahSimple(selectedContact.totalRevenue) }}</div>
                            </div>
                             <div class="bg-[#f8fafc] p-5 rounded-3xl border border-slate-50">
                                <div class="text-[10px] text-slate-400 uppercase font-black tracking-widest mb-2">Perjalanan</div>
                                <div class="text-lg font-black text-gold">{{ selectedContact.totalTrips }}x</div>
                            </div>
                        </div>

                        <h3 class="font-bold text-slate-800 mb-6 flex items-center gap-2 text-sm">
                            <span class="w-1.5 h-6 bg-slate-200 rounded-full"></span> Riwayat Pesanan
                        </h3>

                        <div class="space-y-6 pl-4 border-l-2 border-slate-50 ml-2">
                             <div v-if="isLoadingHistory" class="text-center py-4 text-slate-300 text-sm">Loading history...</div>
                             <div v-for="h in historyData" :key="h.id" class="relative pl-6 pb-2">
                                <div class="absolute -left-[21px] top-1 w-3 h-3 rounded-full border-2 border-white ring-1 ring-slate-100" :class="h.status==='Completed'||h.status==='Tiba'?'bg-emerald-400':'bg-slate-300'"></div>
                                
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 mb-1">
                                    <div class="font-bold text-slate-700 text-sm">{{ h.routeName }}</div>
                                    <span class="w-fit text-[9px] px-2 py-0.5 rounded-md font-bold uppercase tracking-wide" :class="h.status==='Completed'||h.status==='Tiba'?'bg-emerald-50 text-emerald-600':'bg-amber-50 text-amber-600'">{{ h.status }}</span>
                                </div>
                                
                                <div class="flex items-center text-xs text-slate-400 font-medium gap-3 mb-2">
                                    <span><i class="bi bi-calendar4 mr-1"></i> {{ formatDate(h.date) }}</span>
                                    <span><i class="bi bi-clock mr-1"></i> {{ h.time }}</span>
                                </div>
                                <div class="font-mono font-bold text-slate-600 text-xs bg-slate-50 inline-block px-2 py-1 rounded-lg border border-slate-100">{{ formatRupiah(h.totalPrice) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>

    </div>

    <!-- Soft Animations & Scrollbar -->
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        
        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #e2e8f0; border-radius: 20px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background-color: #cbd5e1; }
        
        .ease-spring { transition-timing-function: cubic-bezier(0.175, 0.885, 0.32, 1.275); }

        .inner-shadow { box-shadow: inset 0 2px 4px 0 rgb(0 0 0 / 0.05); }

        @keyframes fade-in { from { opacity: 0; transform: scale(0.98); } to { opacity: 1; transform: scale(1); } }
        .animate-fade-in { animation: fade-in 0.4s ease-out forwards; }

        @keyframes slide-in-right { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        .animate-slide-in-right { animation: slide-in-right 0.5s cubic-bezier(0.19, 1, 0.22, 1) forwards; }
    </style>

    <script>
        const { createApp } = Vue;
        createApp({
            data() {
                return {
                    isAuthenticated: false,
                    loginForm: { user: '', pass: '', error: '', showPass: false },
                    mobileMenuOpen: false,

                    view: 'dashboard',
                    stats: { totalCustomers: 0, totalRevenue: 0, newCustomers: 0, repeatRate: 0 },
                    contacts: [],
                    contactSearch: '',
                    selectedContact: null,
                    historyData: [],
                    isLoadingHistory: false,
                    currentDate: new Date().toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })
                }
            },
            computed: {
                pageTitle() {
                    return this.view === 'dashboard' ? 'Overview' : 'Data Konsumen';
                },
                filteredContacts() {
                    if (!this.contactSearch) return this.contacts;
                    const q = this.contactSearch.toLowerCase();
                    return this.contacts.filter(c => 
                        (c.name && c.name.toLowerCase().includes(q)) || 
                        (c.phone && c.phone.replace(/\D/g,'').includes(q))
                    );
                },
                topCustomers() {
                     return [...this.contacts].sort((a,b) => b.totalRevenue - a.totalRevenue).slice(0, 5);
                }
            },
            mounted() {
                // Check Session
                if(localStorage.getItem('crm_auth_session') === 'active') {
                    this.isAuthenticated = true;
                    this.initApp();
                }
            },
            methods: {
                doLogin() {
                    if(this.loginForm.user === 'marketing' && this.loginForm.pass === '1234') {
                        this.isAuthenticated = true;
                        localStorage.setItem('crm_auth_session', 'active');
                        this.initApp();
                    } else {
                        this.loginForm.error = 'Login gagal. Periksa username atau password.';
                    }
                },
                logout() {
                    localStorage.removeItem('crm_auth_session');
                    this.isAuthenticated = false;
                    this.loginForm = { user: '', pass: '', error: '', showPass: false };
                    this.mobileMenuOpen = false;
                },

                initApp() {
                    this.loadDashboard();
                    this.loadContacts();
                },
                async loadDashboard() {
                    try {
                        const res = await fetch('api.php?action=get_dashboard_stats');
                        this.stats = await res.json();
                    } catch(e) {}
                },
                async loadContacts() {
                    try {
                        const res = await fetch('api.php?action=get_contacts');
                        const d = await res.json();
                        if(d.contacts) this.contacts = d.contacts;
                    } catch(e) {}
                },
                
                openContact(c) {
                    this.selectedContact = c;
                    this.isLoadingHistory = true;
                    fetch(`api.php?action=get_customer_detail&phone=${encodeURIComponent(c.phone)}`)
                        .then(res => res.json())
                        .then(d => {
                             this.historyData = d.history || [];
                             this.isLoadingHistory = false;
                        });
                },
                closeContact() {
                    this.selectedContact = null;
                    this.historyData = [];
                },

                // --- UTILS ---
                getInitials(name) {
                    if(!name) return '?';
                    return name.split(' ').map(n=>n[0]).slice(0,2).join('').toUpperCase();
                },
                normalizePhone(p) {
                    if(!p) return '-';
                    let phone = p.toString().replace(/\D/g, '');
                    if(phone.startsWith('08')) {
                        phone = '62' + phone.substring(1);
                    }
                    return '+' + phone;
                },
                getWaLink(p) {
                    if(!p) return '#';
                    let phone = p.toString().replace(/\D/g, '');
                    if(phone.startsWith('08')) phone = '62' + phone.substring(1);
                    return `https://wa.me/${phone}`;
                },
                formatRupiah(n) { return new Intl.NumberFormat('id-ID', {style:'currency', currency:'IDR', minimumFractionDigits:0}).format(n||0); },
                formatRupiahSimple(n) { 
                    if(n >= 1000000000) return (n/1000000000).toFixed(1) + 'M';
                    if(n >= 1000000) return (n/1000000).toFixed(1) + 'jt';
                    return new Intl.NumberFormat('id-ID').format(n||0); 
                },
                formatDate(d) { 
                    if(!d) return '-'; 
                    return new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
                }
            }
        }).mount('#app');
    </script>
</body>
</html>
