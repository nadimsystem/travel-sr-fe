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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                    <button @click="view='broadcast'; mobileMenuOpen=false" :class="view==='broadcast'?'bg-[#fefce8] text-[#854d0e] inner-shadow':'text-slate-500 hover:bg-slate-50 hover:text-slate-900'" class="w-full flex items-center p-4 rounded-[1.2rem] transition-all group relative overflow-hidden">
                        <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-8 bg-gold rounded-r-full transition-all duration-300" :class="view==='broadcast'?'opacity-100':'opacity-0'"></div>
                        <i class="bi bi-megaphone-fill text-xl lg:mr-4 mr-3 transition-colors" :class="view==='broadcast'?'text-gold':'text-slate-300 group-hover:text-slate-500'"></i> 
                        <span class="font-bold text-sm tracking-wide">Broadcast</span>
                    </button>
                    <button @click="view='queue'; mobileMenuOpen=false" :class="view==='queue'?'bg-[#fefce8] text-[#854d0e] inner-shadow':'text-slate-500 hover:bg-slate-50 hover:text-slate-900'" class="w-full flex items-center p-4 rounded-[1.2rem] transition-all group relative overflow-hidden">
                        <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-8 bg-gold rounded-r-full transition-all duration-300" :class="view==='queue'?'opacity-100':'opacity-0'"></div>
                        <i class="bi bi-collection-play-fill text-xl lg:mr-4 mr-3 transition-colors" :class="view==='queue'?'text-gold':'text-slate-300 group-hover:text-slate-500'"></i> 
                        <span class="font-bold text-sm tracking-wide">Antrian & Auto-Send</span>
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

                        <!-- CHAMPIONS SECTION -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Sultan (Top Spender) -->
                            <div v-if="analytics.champion_revenue" class="bg-gradient-to-br from-slate-900 to-slate-800 p-6 rounded-[2rem] shadow-xl text-white relative overflow-hidden group">
                                <div class="absolute top-0 right-0 w-32 h-32 bg-gold/10 rounded-full blur-2xl group-hover:bg-gold/20 transition-all"></div>
                                <div class="relative z-10">
                                    <div class="flex justify-between items-start mb-4">
                                        <div class="w-12 h-12 bg-white/10 rounded-2xl flex items-center justify-center text-2xl">🏆</div>
                                        <span class="text-[10px] font-bold bg-gold/20 text-gold px-2 py-1 rounded-lg uppercase tracking-wider">The Sultan</span>
                                    </div>
                                    <div class="text-2xl font-bold mb-1 truncate">{{ analytics.champion_revenue.name }}</div>
                                    <div class="text-white/60 text-sm font-medium mb-4">{{ normalizePhone(analytics.champion_revenue.phone) }}</div>
                                    <div class="text-3xl font-black text-gold tracking-tight">{{ formatRupiahSimple(analytics.champion_revenue.total) }}</div>
                                    <div class="text-[10px] text-white/40 uppercase font-bold mt-1">Total Contribution</div>
                                </div>
                            </div>

                            <!-- King of Road (Most Trips) -->
                            <div v-if="analytics.champion_trips" class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100 relative overflow-hidden group hover:shadow-lg transition-all">
                                <div class="absolute top-0 right-0 w-24 h-24 bg-blue-50 rounded-bl-[4rem]"></div>
                                <div class="relative z-10">
                                    <div class="flex justify-between items-start mb-4">
                                        <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center text-2xl">👑</div>
                                        <span class="text-[10px] font-bold bg-blue-50 text-blue-600 px-2 py-1 rounded-lg uppercase tracking-wider">Raja Jalanan</span>
                                    </div>
                                    <div class="text-2xl font-bold text-slate-800 mb-1 truncate">{{ analytics.champion_trips.name }}</div>
                                    <div class="text-slate-400 text-sm font-medium mb-4">{{ normalizePhone(analytics.champion_trips.phone) }}</div>
                                    <div class="text-3xl font-black text-slate-800 tracking-tight">{{ analytics.champion_trips.total }} <span class="text-lg font-bold text-slate-400">Trips</span></div>
                                </div>
                            </div>

                            <!-- Mass Coordinator (Most Seats) -->
                            <div v-if="analytics.champion_seats" class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100 relative overflow-hidden group hover:shadow-lg transition-all">
                                <div class="absolute top-0 right-0 w-24 h-24 bg-emerald-50 rounded-bl-[4rem]"></div>
                                <div class="relative z-10">
                                    <div class="flex justify-between items-start mb-4">
                                        <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center text-2xl">🚌</div>
                                        <span class="text-[10px] font-bold bg-emerald-50 text-emerald-600 px-2 py-1 rounded-lg uppercase tracking-wider">Koordinator</span>
                                    </div>
                                    <div class="text-2xl font-bold text-slate-800 mb-1 truncate">{{ analytics.champion_seats.name }}</div>
                                    <div class="text-slate-400 text-sm font-medium mb-4">{{ normalizePhone(analytics.champion_seats.phone) }}</div>
                                    <div class="text-3xl font-black text-slate-800 tracking-tight">{{ analytics.champion_seats.total }} <span class="text-lg font-bold text-slate-400">Kursi</span></div>
                                </div>
                            </div>
                        </div>

                        <!-- CHARTS SECTION -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100">
                                <h3 class="font-bold text-slate-800 mb-6 flex items-center gap-2">
                                    <span class="w-1.5 h-6 bg-gold rounded-full"></span> Pertumbuhan Pelanggan
                                </h3>
                                <canvas id="growthChart" height="200"></canvas>
                            </div>
                            <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100">
                                <h3 class="font-bold text-slate-800 mb-6 flex items-center gap-2">
                                    <span class="w-1.5 h-6 bg-slate-800 rounded-full"></span> Profil Penumpang
                                </h3>
                                <div class="h-64 flex justify-center">
                                    <canvas id="demoChart"></canvas>
                                </div>
                            </div>
                        </div>

                        <!-- Top Customers List (Existing) -->
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
                        <div class="bg-white p-2 rounded-[1.5rem] shadow-sm border border-slate-100 mb-6 flex items-center gap-4">
                            <div class="flex-1 flex items-center">
                                <div class="w-12 h-12 flex items-center justify-center text-slate-400"><i class="bi bi-search text-xl"></i></div>
                                <input type="text" v-model="contactSearch" placeholder="Cari nama pelanggan atau nomor handphone..." class="w-full h-12 bg-transparent border-none text-slate-700 font-bold placeholder:font-medium placeholder:text-slate-300 focus:ring-0 text-sm">
                            </div>
                            <div class="pr-2">
                                <select v-model="filterMode" class="bg-slate-50 border border-slate-100 text-slate-600 text-xs font-bold rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-gold/50 cursor-pointer hover:bg-slate-100 transition-colors">
                                    <option value="default">Urutkan: Terakhir Aktif</option>
                                    <option value="seats">Pemesan Bangku Terbanyak</option>
                                    <option value="repeat">Repeat Order Terbanyak</option>
                                    <option value="revenue">Total Pembelian Terbanyak</option>
                                </select>
                            </div>
                        </div>

                        <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 flex-1 flex flex-col overflow-hidden relative">
                             <div class="absolute top-0 left-0 w-full h-10 bg-gradient-to-b from-white to-transparent pointer-events-none z-10"></div>
                             
                            <div class="overflow-x-auto flex-1 custom-scrollbar">
                                <table class="w-full text-left border-collapse min-w-[800px]">
                                    <thead class="bg-white text-slate-400 font-bold text-[11px] uppercase tracking-wider sticky top-0 z-20">
                                        <tr>
                                            <th class="p-6 w-16 text-center">
                                                <input type="checkbox" :checked="selectedContacts.length > 0 && selectedContacts.length === filteredContacts.length" @click="toggleSelectAll" class="w-4 h-4 rounded border-slate-300 text-gold focus:ring-gold cursor-pointer">
                                            </th>
                                            <th class="p-6">Profil</th>
                                            <th class="p-6">Kontak</th>
                                            <th class="p-6 text-center">Statistik</th>
                                            <th class="p-6 text-right">Last Seen</th>
                                            <th class="p-6 text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50">
                                        <tr v-for="c in filteredContacts" :key="c.phone" class="hover:bg-[#fefce8]/50 transition-colors group">
                                            <td class="p-6 text-center">
                                                <input type="checkbox" :value="c.phone" v-model="selectedContacts" class="w-4 h-4 rounded border-slate-300 text-gold focus:ring-gold cursor-pointer">
                                            </td>
                                            <td class="p-6" @click="openContact(c)">
                                                <div class="flex items-center gap-4 cursor-pointer">
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
                                                    <span v-if="filterMode === 'seats'" class="text-sm font-black text-slate-800">{{ c.totalSeats || 0 }}</span>
                                                    <span v-else-if="filterMode === 'revenue'" class="text-sm font-black text-slate-800">{{ formatRupiahSimple(c.totalRevenue) }}</span>
                                                    <span v-else class="text-sm font-black text-slate-800">{{ c.totalTrips }}</span>
                                                    
                                                    <span v-if="filterMode === 'seats'" class="text-[9px] text-slate-400 font-bold uppercase mt-0.5">Kursi</span>
                                                    <span v-else-if="filterMode === 'revenue'" class="text-[9px] text-slate-400 font-bold uppercase mt-0.5">IDR</span>
                                                    <span v-else class="text-[9px] text-slate-400 font-bold uppercase mt-0.5">Trips</span>
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

                    <!-- Broadcast View -->
                    <div v-if="view === 'broadcast'" class="animate-fade-in h-full flex flex-col gap-6">
                        <!-- Stats Header -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div class="bg-slate-900 text-white p-5 rounded-3xl shadow-lg relative overflow-hidden">
                                <i class="bi bi-people absolute -right-2 -bottom-2 text-6xl opacity-10"></i>
                                <div class="text-3xl font-black">{{ broadcastTargets.length }}</div>
                                <div class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Target Audience</div>
                            </div>
                            <div class="bg-white border border-slate-100 p-5 rounded-3xl shadow-sm">
                                <div class="text-3xl font-black text-gold">{{ broadcastTargets.reduce((a,b)=>a+(Number(b.totalTrips)||0),0) }}</div>
                                <div class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Total Trips</div>
                            </div>
                        </div>

                        <div class="flex flex-col lg:flex-row gap-6 flex-1 min-h-0">
                            <!-- Filter Panel -->
                            <div class="w-full lg:w-72 flex-none space-y-6 overflow-y-auto custom-scrollbar pr-2 pb-20">
                                
                                <!-- Type Filter -->
                                <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
                                    <h4 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
                                        <i class="bi bi-person-badge text-gold"></i> Tipe Penumpang
                                    </h4>
                                    <div class="space-y-3">
                                        <label v-for="t in allTypes" :key="t" class="flex items-center gap-3 cursor-pointer group">
                                            <div class="relative flex items-center">
                                                <input type="checkbox" :checked="broadcastFilters.types.includes(t)" @change="toggleFilter('types', t)" class="peer appearance-none w-5 h-5 border-2 border-slate-200 rounded-lg checked:bg-gold checked:border-gold transition-all">
                                                <i class="bi bi-check text-white absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-xs opacity-0 peer-checked:opacity-100 pointer-events-none transition-opacity"></i>
                                            </div>
                                            <span class="text-xs font-bold text-slate-600 group-hover:text-slate-900">{{ t || 'Umum' }}</span>
                                        </label>
                                    </div>
                                </div>

                                <!-- Route Filter -->
                                <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
                                    <h4 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
                                        <i class="bi bi-map text-blue-500"></i> Riwayat Rute
                                    </h4>
                                    <div class="space-y-3 max-h-60 overflow-y-auto custom-scrollbar pr-2">
                                        <label v-for="r in allRoutes" :key="r" class="flex items-center gap-3 cursor-pointer group">
                                            <div class="relative flex items-center">
                                                <input type="checkbox" :checked="broadcastFilters.routes.includes(r)" @change="toggleFilter('routes', r)" class="peer appearance-none w-5 h-5 border-2 border-slate-200 rounded-lg checked:bg-blue-500 checked:border-blue-500 transition-all">
                                                <i class="bi bi-check text-white absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-xs opacity-0 peer-checked:opacity-100 pointer-events-none transition-opacity"></i>
                                            </div>
                                            <span class="text-xs font-bold text-slate-600 group-hover:text-slate-900 leading-tight">{{ r }}</span>
                                        </label>
                                    </div>
                                </div>

                                <!-- Min Trips Filter -->
                                <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
                                    <h4 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
                                        <i class="bi bi-activity text-emerald-500"></i> Loyalitas
                                    </h4>
                                    <div class="space-y-4">
                                        <div>
                                            <div class="flex justify-between text-xs font-bold text-slate-500 mb-2">
                                                <span>Minimal Perjalanan</span>
                                                <span class="text-slate-900 text-sm">{{ broadcastFilters.minTrips }}x</span>
                                            </div>
                                            <input type="range" v-model.number="broadcastFilters.minTrips" min="0" max="20" class="w-full h-2 bg-slate-100 rounded-lg appearance-none cursor-pointer accent-slate-900">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Filtered Results -->
                            <div class="flex-1 bg-white rounded-[2.5rem] shadow-sm border border-slate-100 flex flex-col overflow-hidden relative">
                                <div class="absolute top-0 left-0 w-full h-10 bg-gradient-to-b from-white to-transparent pointer-events-none z-10"></div>
                                <div class="overflow-x-auto flex-1 custom-scrollbar">
                                    <table class="w-full text-left border-collapse min-w-[600px]">
                                        <thead class="bg-white text-slate-400 font-bold text-[11px] uppercase tracking-wider sticky top-0 z-20">
                                            <tr>
                                                <th class="p-6 w-16 text-center">
                                                    <input type="checkbox" :checked="selectedContacts.length > 0 && selectedContacts.length === broadcastTargets.length" @click="toggleSelectAll" class="w-4 h-4 rounded border-slate-300 text-gold focus:ring-gold cursor-pointer">
                                                </th>
                                                <th class="p-6">Target</th>
                                                <th class="p-6">Tags & History</th>
                                                <th class="p-6 text-center">Stats</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-50">
                                            <tr v-for="c in broadcastTargets" :key="c.phone" class="hover:bg-[#fefce8]/50 transition-colors group">
                                                <td class="p-6 text-center">
                                                    <input type="checkbox" :value="c.phone" v-model="selectedContacts" class="w-4 h-4 rounded border-slate-300 text-gold focus:ring-gold cursor-pointer">
                                                </td>
                                                <td class="p-6">
                                                    <div class="flex items-center gap-4">
                                                        <div class="w-10 h-10 rounded-xl bg-slate-50 text-slate-600 flex items-center justify-center font-black text-xs group-hover:bg-gold group-hover:text-white transition-all">{{ getInitials(c.name) }}</div>
                                                        <div>
                                                            <div class="font-bold text-slate-700 text-sm group-hover:text-slate-900">{{ c.name }}</div>
                                                            <div class="text-xs font-mono text-slate-400">{{ normalizePhone(c.phone) }}</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="p-6">
                                                    <div class="flex flex-wrap gap-2 max-w-md">
                                                        <!-- Type Tags -->
                                                        <span v-for="t in (c.historyTypes||'Umum').split(', ')" class="px-2 py-1 rounded-lg bg-slate-50 border border-slate-100 text-[10px] font-bold text-slate-500 uppercase tracking-wide">{{ t }}</span>
                                                        <!-- Route Tags (Limit 2) -->
                                                        <span v-for="r in (c.historyRoutes||'').split(', ').slice(0, 2)" class="px-2 py-1 rounded-lg bg-blue-50 text-blue-600 text-[10px] font-bold">{{ r }}</span>
                                                        <span v-if="(c.historyRoutes||'').split(', ').length > 2" class="px-2 py-1 text-slate-400 text-[10px] font-bold">+{{ (c.historyRoutes||'').split(', ').length - 2 }}</span>
                                                    </div>
                                                </td>
                                                <td class="p-6 text-center">
                                                    <span class="font-black text-slate-800">{{ c.totalTrips }}</span> <span class="text-xs text-slate-400">Trips</span>
                                                </td>
                                            </tr>
                                            <tr v-if="broadcastTargets.length === 0">
                                                <td colspan="4" class="p-16 text-center">
                                                    <div class="w-16 h-16 bg-slate-50 rounded-full mx-auto flex items-center justify-center text-slate-300 text-2xl mb-4"><i class="bi bi-filter"></i></div>
                                                    <p class="text-slate-400 font-medium">Tidak ada target yang cocok dengan filter</p>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Queue Monitor View -->
                    <div v-if="view === 'queue'" class="animate-fade-in h-full flex flex-col gap-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                             <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100 relative overflow-hidden">
                                <div class="relative z-10">
                                    <div class="text-sm font-bold text-slate-400 mb-1">Pending</div>
                                    <div class="text-4xl font-black text-slate-800">{{ queueStats.stats.pending || 0 }}</div>
                                </div>
                                <div class="absolute right-4 bottom-4 text-slate-100 text-6xl"><i class="bi bi-hourglass-split"></i></div>
                            </div>
                            <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100 relative overflow-hidden">
                                <div class="relative z-10">
                                    <div class="text-sm font-bold text-slate-400 mb-1">Terkirim</div>
                                    <div class="text-4xl font-black text-emerald-500">{{ queueStats.stats.sent || 0 }}</div>
                                </div>
                                <div class="absolute right-4 bottom-4 text-emerald-50 text-6xl"><i class="bi bi-check-circle-fill"></i></div>
                            </div>
                            <div class="p-6 rounded-[2rem] shadow-sm border border-slate-100 flex items-center justify-between" :class="isAutoSending ? 'bg-red-50 border-red-100' : 'bg-slate-900 text-white'">
                                <div>
                                    <h3 class="font-bold text-lg" :class="isAutoSending ? 'text-red-500' : 'text-white'">Auto Sender</h3>
                                    <p class="text-xs opacity-70 mt-1">{{ isAutoSending ? 'Sedang berjalan... Jangan tutup tab.' : 'Klik play untuk memulai' }}</p>
                                </div>
                                <button @click="toggleAutoSend" class="w-14 h-14 rounded-full flex items-center justify-center text-2xl shadow-lg transition-all" :class="isAutoSending ? 'bg-red-500 text-white animate-pulse' : 'bg-gold text-white hover:scale-110'">
                                    <i class="bi" :class="isAutoSending ? 'bi-stop-fill' : 'bi-play-fill'"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Terminal / Log View -->
                        <div class="flex-1 bg-slate-900 rounded-[2.5rem] p-8 shadow-xl flex flex-col overflow-hidden relative font-mono text-sm border-4 border-slate-800">
                            <div class="flex justify-between items-center mb-4 pb-4 border-b border-slate-800">
                                <div class="text-slate-400 font-bold flex items-center gap-2"><i class="bi bi-terminal-fill"></i> Activity Log</div>
                                <div v-if="nextProcessTime > 0" class="text-gold font-bold animate-pulse">Next run in: {{ Math.ceil(nextProcessTime/1000) }}s</div>
                            </div>
                            <div class="flex-1 overflow-y-auto custom-scrollbar space-y-2" id="consoleLog">
                                <div v-for="(log, i) in autoSendLogs" :key="i" class="flex gap-3">
                                    <span class="text-slate-500">[{{ log.time }}]</span>
                                    <span :class="log.type === 'error' ? 'text-red-400' : (log.type === 'success' ? 'text-emerald-400' : 'text-slate-300')">{{ log.msg }}</span>
                                </div>
                                <div v-if="autoSendLogs.length === 0" class="text-slate-600 italic">Menunggu giliran...</div>
                            </div>
                        </div>
                        
                        <!-- List Preview -->
                        <div class="bg-white rounded-[2rem] border border-slate-100 p-6">
                            <h3 class="font-bold text-slate-800 mb-4">Antrian Berikutnya</h3>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-sm">
                                    <thead class="text-slate-400 font-bold uppercase text-[10px]">
                                        <tr>
                                            <th class="pb-3">Nama</th>
                                            <th class="pb-3">Status</th>
                                            <th class="pb-3 text-right">Attempts</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50">
                                        <tr v-for="item in queueStats.items" :key="item.id">
                                            <td class="py-3 font-bold text-slate-700">{{ item.name }} <span class="text-slate-400 font-normal">({{ item.phone }})</span></td>
                                            <td class="py-3">
                                                <span class="px-2 py-1 rounded text-[10px] font-bold uppercase" :class="{
                                                    'bg-slate-100 text-slate-500': item.status === 'pending',
                                                    'bg-blue-50 text-blue-500': item.status === 'processing',
                                                    'bg-emerald-50 text-emerald-500': item.status === 'sent',
                                                    'bg-red-50 text-red-500': item.status === 'failed'
                                                }">{{ item.status }}</span>
                                            </td>
                                            <td class="py-3 text-right text-slate-400 font-mono">{{ item.attempts }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

            <!-- BROADCAST ACTION BAR -->
            <div v-if="(view === 'contacts' || view === 'broadcast') && selectedContacts.length > 0" class="fixed bottom-6 left-1/2 -translate-x-1/2 z-40 bg-slate-900 text-white pl-6 pr-3 py-3 rounded-full shadow-2xl flex items-center gap-6 animate-fade-in border border-slate-700">
                <div class="font-bold text-sm">
                    <span class="text-gold text-lg mr-1">{{ selectedContacts.length }}</span> Terpilih
                </div>
                <button @click="openBroadcastModal" class="bg-gold text-white px-5 py-2 rounded-full font-bold text-xs hover:bg-yellow-500 transition-colors shadow-lg">
                    <i class="bi bi-megaphone-fill mr-1"></i> Broadcast Chat
                </button>
                <button @click="selectedContacts = []" class="w-8 h-8 rounded-full bg-slate-800 text-slate-400 hover:text-white flex items-center justify-center transition-colors">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <!-- BROADCAST MODAL -->
            <div v-if="showBroadcastModal" class="fixed inset-0 z-[70] flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showBroadcastModal = false"></div>
                <div class="bg-white w-full max-w-2xl rounded-[2rem] shadow-2xl relative z-10 overflow-hidden flex flex-col max-h-[90vh]">
                    <!-- Header -->
                    <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                        <div>
                            <h3 class="text-xl font-extrabold text-slate-800">Broadcast Whatsapp</h3>
                            <p class="text-slate-400 text-xs font-bold uppercase tracking-widest mt-1">
                                Step {{ broadcastStep }} of 2: {{ broadcastStep === 1 ? 'Tulis Pesan' : 'Konfirmasi' }}
                            </p>
                        </div>
                        <button @click="showBroadcastModal = false" class="w-10 h-10 rounded-xl bg-slate-100 text-slate-400 hover:bg-slate-200 flex items-center justify-center transition-colors"><i class="bi bi-x-lg"></i></button>
                    </div>

                    <!-- STEP 1: COMPOSE -->
                    <div v-if="broadcastStep === 1" class="p-8 overflow-y-auto">
                        <label class="block text-sm font-bold text-slate-700 mb-3">Pesan Broadcast</label>
                        <div class="relative">
                            <textarea id="broadcastMsgInput" v-model="broadcastMessage" rows="6" class="w-full bg-slate-50 border border-slate-200 rounded-2xl p-5 text-slate-700 font-medium focus:ring-2 focus:ring-gold/50 focus:border-gold transition-all resize-none mb-3" placeholder="Tulis pesan anda disini..."></textarea>
                            <div class="flex gap-2 mb-6 overflow-x-auto pb-2">
                                <button @click="insertVariable('{{name}}')" class="px-3 py-1.5 bg-blue-50 text-blue-600 rounded-lg text-xs font-bold hover:bg-blue-100 transition-colors whitespace-nowrap">+ Nama Lengkap</button>
                                <button @click="insertVariable('{{panggilan}}')" class="px-3 py-1.5 bg-blue-50 text-blue-600 rounded-lg text-xs font-bold hover:bg-blue-100 transition-colors whitespace-nowrap">+ Nama Panggilan</button>
                                <button @click="insertVariable('{{phone}}')" class="px-3 py-1.5 bg-emerald-50 text-emerald-600 rounded-lg text-xs font-bold hover:bg-emerald-100 transition-colors whitespace-nowrap">+ Nomor HP</button>
                            </div>
                        </div>

                        <div class="bg-amber-50 rounded-2xl p-5 border border-amber-100">
                             <div class="text-[10px] uppercase font-bold text-amber-500 tracking-widest mb-2">Preview (Example)</div>
                             <div class="text-slate-600 text-sm whitespace-pre-line bg-white/50 p-4 rounded-xl border border-amber-100/50">
                                {{ previewMessage }}
                             </div>
                        </div>
                    </div>

                    <!-- STEP 2: QUEUE -->
                    <div v-if="broadcastStep === 2" class="flex-1 overflow-hidden flex flex-col">
                        <div class="flex-1 flex flex-col items-center justify-center p-10 text-center">
                            <div class="w-24 h-24 bg-yellow-50 rounded-full flex items-center justify-center text-yellow-500 text-4xl mb-6 animate-bounce">
                                <i class="bi bi-box-arrow-in-right"></i>
                            </div>
                            <h3 class="text-2xl font-black text-slate-800 mb-2">Simpan ke Antrian?</h3>
                            <p class="text-slate-500 font-medium max-w-sm mx-auto mb-8">
                                Anda akan menambahkan <strong class="text-slate-800">{{ broadcastQueue.length }} kontak</strong> ke antrian broadcast server.
                                Proses pengiriman akan dilakukan secara otomatis melalui menu "Antrian".
                            </p>
                            
                            <button @click="confirmAddToQueue" class="px-8 py-4 bg-slate-900 text-white rounded-2xl font-bold hover:scale-105 transition-transform shadow-xl shadow-slate-200">
                                <i class="bi bi-check-lg mr-2"></i> Ya, Simpan ke Antrian
                            </button>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="p-6 border-t border-slate-100 bg-slate-50 flex justify-end gap-3">
                        <button v-if="broadcastStep === 1" @click="startBroadcast" class="px-8 py-3 bg-slate-900 text-white rounded-xl font-bold text-sm hover:bg-slate-800 transition-all shadow-lg shadow-slate-200">
                            Lanjut ke Pengiriman <i class="bi bi-arrow-right ml-2"></i>
                        </button>
                        <button v-if="broadcastStep === 2" @click="showBroadcastModal = false" class="px-8 py-3 bg-white border border-slate-200 text-slate-600 rounded-xl font-bold text-sm hover:bg-slate-50 transition-all">
                            Selesai / Tutup
                        </button>
                    </div>
                </div>
            </div>

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
                            <a :href="getWaLink(selectedContact.phone)" target="_blank" class="flex-1 bg-white border border-slate-200 text-slate-600 py-4 rounded-2xl font-bold text-sm hover:bg-slate-50 transition-all flex items-center justify-center gap-2">
                                <i class="bi bi-chat-text-fill"></i> Chat
                            </a>
                            <button @click="sendThankYou(selectedContact)" class="flex-[1.5] bg-gradient-to-r from-gold to-yellow-400 text-white py-4 rounded-2xl font-bold text-sm shadow-lg shadow-yellow-500/20 hover:shadow-xl hover:-translate-y-1 transition-all flex items-center justify-center gap-2">
                                <i class="bi bi-gift-fill"></i> Kirim Ucapan
                            </button>
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
                                
                                <div class="flex items-center text-xs text-slate-400 font-medium gap-3 mb-2 flex-wrap">
                                    <span><i class="bi bi-calendar4 mr-1"></i> {{ formatDate(h.date) }}</span>
                                    <span><i class="bi bi-clock mr-1"></i> {{ h.time }}</span>
                                    <span v-if="h.seatNumbers" class="text-slate-500 font-bold bg-slate-50 px-1.5 rounded"><i class="bi bi-grid-3x3-gap-fill mr-1 text-gold"></i> Kursi: {{ h.seatNumbers }}</span>
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
                    filterMode: 'default',
                    analytics: {
                        champion_revenue: null,
                        champion_trips: null,
                        champion_seats: null,
                        demographics: [],
                        growth: []
                    },
                    // Broadcast State
                    selectedContacts: [],
                    showBroadcastModal: false,
                    broadcastStep: 1, // 1: Compose, 2: Queue
                    broadcastMessage: 'Halo Kak {{name}},\n\nTerima kasih telah menjadi pelanggan setia Sutan Raya Travel. Kami memiliki penawaran spesial untuk Anda!\n\nHubungi kami untuk info lebih lanjut.',
                    broadcastQueue: [],
                    broadcastFilters: { routes: [], types: [], minTrips: 0 },
                    
                    // Auto Send State
                    queueStats: { stats: {}, items: [] },
                    isAutoSending: false,
                    autoSendLogs: [],
                    nextProcessTime: 0,
                    processTimer: null,
                    countDownTimer: null,

                    charts: { growth: null, demo: null },
                    currentDate: new Date().toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })
                }
            },
            computed: {
                pageTitle() {
                    const titles = {
                        'dashboard': 'Overview',
                        'contacts': 'Data Konsumen',
                        'dashboard': 'Overview',
                        'contacts': 'Data Konsumen',
                        'broadcast': 'Broadcast Filter',
                        'queue': 'Monitor Antrian'
                    };
                    return titles[this.view] || 'Sutan Raya CRM';
                },
                allRoutes() {
                    const routes = new Set();
                    this.contacts.forEach(c => {
                        if(c.historyRoutes) c.historyRoutes.split(', ').forEach(r => routes.add(r.trim()));
                    });
                    return Array.from(routes).sort();
                },
                allTypes() {
                    const types = new Set();
                    this.contacts.forEach(c => {
                         if(c.historyTypes) c.historyTypes.split(', ').forEach(t => types.add(t.trim()));
                    });
                    return Array.from(types).sort();
                },
                broadcastTargets() {
                    return this.contacts.filter(c => {
                         if(this.broadcastFilters.minTrips > 0 && c.totalTrips < this.broadcastFilters.minTrips) return false;
                         
                         // Check Type
                         if(this.broadcastFilters.types.length > 0) {
                             if(!c.historyTypes) return false;
                             const cTypes = c.historyTypes.split(', ');
                             const match = this.broadcastFilters.types.some(t => cTypes.includes(t));
                             if(!match) return false;
                         }

                         // Check Route
                         if(this.broadcastFilters.routes.length > 0) {
                             if(!c.historyRoutes) return false;
                             const cRoutes = c.historyRoutes.split(', ');
                             const match = this.broadcastFilters.routes.some(r => cRoutes.includes(r));
                             if(!match) return false;
                         }

                         return true;
                    });
                },
                filteredContacts() {
                    let res = this.contacts;
                    
                    if (this.contactSearch) {
                        const q = this.contactSearch.toLowerCase();
                        res = res.filter(c => 
                            (c.name && c.name.toLowerCase().includes(q)) || 
                            (c.phone && c.phone.replace(/\D/g,'').includes(q))
                        );
                    }

                    // Sort Logic
                    if (this.filterMode === 'seats') {
                        return res.sort((a,b) => (b.totalSeats||0) - (a.totalSeats||0));
                    } else if (this.filterMode === 'repeat') {
                        return res.sort((a,b) => b.totalTrips - a.totalTrips);
                    } else if (this.filterMode === 'revenue') {
                        return res.sort((a,b) => b.totalRevenue - a.totalRevenue);
                    }
                    
                    return res; // Default: Last Trip (Original SQL Order)
                },
                topCustomers() {
                     return [...this.contacts].sort((a,b) => b.totalRevenue - a.totalRevenue).slice(0, 5);
                },
                previewMessage() {
                    return this.broadcastMessage
                        .replace(/{{name}}/g, 'Budi Santoso')
                        .replace(/{{panggilan}}/g, 'Budi')
                        .replace(/{{phone}}/g, '628123456789');
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
                async doLogin() {
                    if(!this.loginForm.user || !this.loginForm.pass) {
                         this.loginForm.error = 'Mohon isi username dan password';
                         return;
                    }

                    try {
                        const res = await fetch('api.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                action: 'login',
                                username: this.loginForm.user,
                                password: this.loginForm.pass
                            })
                        });
                        const data = await res.json();
                        
                        if(data.status === 'success') {
                            this.isAuthenticated = true;
                            localStorage.setItem('crm_auth_session', 'active');
                            localStorage.setItem('crm_user_name', data.user.name);
                            this.initApp();
                        } else {
                            this.loginForm.error = data.message || 'Login gagal';
                        }
                    } catch(e) {
                         this.loginForm.error = 'Terjadi kesalahan sistem';
                         console.error(e);
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
                    this.loadAnalytics();
                    this.loadDashboard();
                    this.loadContacts();
                    this.loadAnalytics();
                    this.loadQueueStats();
                    setInterval(() => { this.loadQueueStats() }, 5000); // Polling queue
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
                async loadAnalytics() {
                    try {
                        const res = await fetch('api.php?action=get_crm_analytics');
                        this.analytics = await res.json();
                        this.$nextTick(() => { this.initCharts(); });
                    } catch(e) {} 
                },
                initCharts() {
                    if(this.view !== 'dashboard') return;
                    
                    const ctxGrowth = document.getElementById('growthChart');
                    const ctxDemo = document.getElementById('demoChart');

                    if(ctxGrowth && this.analytics.growth) {
                        if(this.charts.growth) this.charts.growth.destroy();
                        this.charts.growth = new Chart(ctxGrowth, {
                            type: 'line',
                            data: {
                                labels: this.analytics.growth.map(g => g.month),
                                datasets: [{
                                    label: 'Pelanggan Baru',
                                    data: this.analytics.growth.map(g => g.count),
                                    borderColor: '#d4af37',
                                    backgroundColor: 'rgba(212, 175, 55, 0.1)',
                                    tension: 0.4,
                                    fill: true
                                }]
                            },
                            options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { display: false } }, x: { grid: { display: false } } } }
                        });
                    }

                    if(ctxDemo && this.analytics.demographics) {
                         if(this.charts.demo) this.charts.demo.destroy();
                         this.charts.demo = new Chart(ctxDemo, {
                            type: 'doughnut',
                            data: {
                                labels: this.analytics.demographics.map(d => d.passengerType || 'Umum'),
                                datasets: [{
                                    data: this.analytics.demographics.map(d => d.count),
                                    backgroundColor: ['#d4af37', '#1e293b', '#64748b', '#94a3b8']
                                }]
                            },
                            options: { plugins: { legend: { position: 'right' } } }
                         });
                    }
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

                sendThankYou(c) {
                    if(!c) return;
                    let phone = c.phone.toString().replace(/\D/g, '');
                    if(phone.startsWith('08')) phone = '62' + phone.substring(1);
                    
                    const message = `Halo Kak ${c.name}, Terima kasih telah mempercayakan perjalanan Anda kepada Sutan Raya.

Berikut ringkasan perjalanan Kakak bersama kami:
🗓 Total Perjalanan: ${c.totalTrips}x
💰 Total Transaksi: ${this.formatRupiah(c.totalRevenue)}

Kami tunggu perjalanan berikutnya ya Kak! 🙏😊`;

                    window.open(`https://wa.me/${phone}?text=${encodeURIComponent(message)}`, '_blank');
                },

                // --- BROADCAST ---
                toggleSelectAll() {
                    const targetList = this.view === 'broadcast' ? this.broadcastTargets : this.filteredContacts;
                    
                    if (this.selectedContacts.length === targetList.length) {
                        this.selectedContacts = [];
                    } else {
                        this.selectedContacts = targetList.map(c => c.phone);
                    }
                },
                toggleFilter(type, val) {
                    const arr = this.broadcastFilters[type];
                    const idx = arr.indexOf(val);
                    if(idx === -1) arr.push(val);
                    else arr.splice(idx, 1);
                },
                openBroadcastModal() {
                    if(this.selectedContacts.length === 0) return;
                    this.broadcastStep = 1;
                    this.showBroadcastModal = true;
                },
                insertVariable(v) {
                    const textarea = document.getElementById('broadcastMsgInput');
                    if(textarea) {
                        const start = textarea.selectionStart;
                        const end = textarea.selectionEnd;
                        const text = this.broadcastMessage;
                        this.broadcastMessage = text.substring(0, start) + v + text.substring(end);
                        this.$nextTick(() => {
                            textarea.focus();
                            textarea.selectionStart = textarea.selectionEnd = start + v.length;
                        });
                    } else {
                        this.broadcastMessage += v;
                    }
                },
                startBroadcast() {
                    // Prepare Data for Queue
                    const targets = this.contacts.filter(c => this.selectedContacts.includes(c.phone));
                    this.broadcastQueue = targets.map(c => ({
                        phone: c.phone,
                        name: c.name
                    }));
                    this.broadcastStep = 2;
                },
                async confirmAddToQueue() {
                    try {
                        const res = await fetch('api.php?action=add_to_queue', {
                            method: 'POST',
                            body: JSON.stringify({
                                targets: this.broadcastQueue,
                                message: this.broadcastMessage
                            })
                        });
                        const d = await res.json();
                        if(d.status === 'success') {
                            alert(`Berhasil menambahkan ${d.count} kontak ke antrian!`);
                            this.showBroadcastModal = false;
                            this.selectedContacts = []; // Reset selection
                            this.view = 'queue';
                            this.loadQueueStats();
                        } else {
                            alert('Gagal: ' + d.message);
                        }
                    } catch(e) {
                         alert('Jaringan error');
                    }
                },
                
                // --- QUEUE & AUTO SENDER ---
                async loadQueueStats() {
                    if(this.view !== 'queue') return;
                    try {
                        const res = await fetch('api.php?action=get_queue_stats');
                        this.queueStats = await res.json();
                    } catch(e) {}
                },
                
                toggleAutoSend() {
                    this.isAutoSending = !this.isAutoSending;
                    if(this.isAutoSending) {
                        this.addLog('Starting auto-sender...', 'info');
                        this.processQueueLoop();
                    } else {
                        this.addLog('Auto-sender stopped.', 'info');
                        clearTimeout(this.processTimer);
                        clearInterval(this.countDownTimer);
                        this.nextProcessTime = 0;
                    }
                },

                async processQueueLoop() {
                    if(!this.isAutoSending) return;

                    // 1. Fetch Item
                    this.addLog('Fetching next item...', 'info');
                    try {
                        const res = await fetch('api.php?action=get_next_queue_item');
                        const d = await res.json();
                        
                        if(d.status === 'found') {
                            const item = d.item;
                            this.addLog(`Processing: ${item.name} (${item.phone})`, 'info');
                            
                            // 2. Send (Open Tab)
                            this.sendBroadcastItem(item); // Re-use logic but modify to take simple object
                            
                            // 3. Mark Sent
                            await fetch('api.php?action=update_queue_status', {
                                method: 'POST',
                                body: JSON.stringify({ id: item.id, status: 'sent' })
                            });
                            this.addLog('Marked as SENT. Waiting for cooldown...', 'success');
                            this.loadQueueStats();

                            // 4. Cooldown (15s - 45s)
                            const delay = Math.floor(Math.random() * (45000 - 15000 + 1) + 15000);
                            this.startCountdown(delay);
                            
                            this.processTimer = setTimeout(() => {
                                this.processQueueLoop();
                            }, delay);

                        } else if (d.status === 'empty') {
                            this.addLog('Queue empty. Waiting 10s...', 'info');
                            this.processTimer = setTimeout(() => {
                                this.processQueueLoop();
                            }, 10000);
                        }
                    } catch(e) {
                        this.addLog('Error processing. Retrying in 10s...', 'error');
                         this.processTimer = setTimeout(() => {
                            this.processQueueLoop();
                        }, 10000);
                    }
                },

                startCountdown(ms) {
                    this.nextProcessTime = ms;
                    if(this.countDownTimer) clearInterval(this.countDownTimer);
                    this.countDownTimer = setInterval(() => {
                        this.nextProcessTime -= 1000;
                        if(this.nextProcessTime <= 0) clearInterval(this.countDownTimer);
                    }, 1000);
                },

                addLog(msg, type='info') {
                    const time = new Date().toLocaleTimeString();
                    this.autoSendLogs.unshift({ time, msg, type });
                    if(this.autoSendLogs.length > 50) this.autoSendLogs.pop();
                },

                sendBroadcastItem(item) {
                     // Compatible with both Modal Item and Queue Item
                     let phone = (item.phone || '').toString().replace(/\D/g, '');
                     if(phone.startsWith('08')) phone = '62' + phone.substring(1);
                     
                     let listName = item.name || 'Pelanggan';
                     let shortName = listName.split(' ')[0];
                     
                     // If message is in item (from DB) use it, else use template from state (preview mode)
                     let rawMsg = item.message || this.broadcastMessage;

                     let msg = rawMsg
                        .replace(/{{name}}/g, listName)
                        .replace(/{{panggilan}}/g, shortName)
                        .replace(/{{phone}}/g, item.phone || phone);
                        
                     // Force Web URL to ensure it opens in Browser (for Userscript to work)
                     const url = `https://web.whatsapp.com/send?phone=${phone}&text=${encodeURIComponent(msg)}`;
                     
                     // STRATEGY CHANGE: Use a POPUP window with a specific name ('wa_popup').
                     // This reuses the SAME window for every message, so we don't need to close tabs!
                     // The next message will simply replace the content of this window.
                     const win = window.open(url, 'wa_popup', 'width=1024,height=768,menubar=no,toolbar=no,location=no,status=no');
                     
                     if(!win) {
                         this.addLog('Popup blocked! Please allow popups.', 'error');
                         this.isAutoSending = false;
                     }
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
