<!DOCTYPE html>
<html lang="id" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sutan Raya - Business OS V11</title>
    <link rel="icon" type="image/webp" href="../image/logo.webp">
    <meta name="description" content="Sutan Raya - Business OS V11">
    <meta name="keywords" content="Sutan Raya, Business OS, V11">
    <meta name="author" content="Sutan Raya">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            transition: background-color 0.3s, color 0.3s; 
            overflow: hidden; /* Prevent body scroll */
        }
        
        /* Custom Scrollbar */
        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        .dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #475569; }
        
        /* Calendar Grid */
        .calendar-grid { display: grid; grid-template-columns: repeat(7, 1fr); border-top: 1px solid #e5e7eb; border-left: 1px solid #e5e7eb; }
        .dark .calendar-grid { border-color: #374151; }
        .calendar-day { min-height: 100px; padding: 0.5rem; display: flex; flex-direction: column; border-right: 1px solid #e5e7eb; border-bottom: 1px solid #e5e7eb; }
        .dark .calendar-day { border-color: #374151; background-color: #1e293b; }
        .calendar-day.other-month { background-color: #f9fafb; color: #d1d5db; }
        .dark .calendar-day.other-month { background-color: #0f172a; color: #475569; }
        .calendar-event { font-size: 0.65rem; padding: 3px 5px; border-radius: 3px; margin-bottom: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; cursor: pointer; border-left: 3px solid; transition: transform 0.1s; font-weight: 600; }
        .calendar-event:hover { transform: scale(1.02); z-index: 10; }

        /* Ticket Visuals */
        .ticket { background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%); border-radius: 1.5rem 1.5rem 0 0; }
        .ticket-cutout { position: relative; background: #fff; border-radius: 0 0 1.5rem 1.5rem; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); }
        .ticket-cutout:before, .ticket-cutout:after { content: ''; position: absolute; width: 2rem; height: 2rem; border-radius: 50%; top: -1rem; background: #111827; }
        .dark .ticket-cutout:before, .dark .ticket-cutout:after { background: #0f172a; } /* Match dark bg */
        .ticket-cutout:before { left: -1rem; } .ticket-cutout:after { right: -1rem; }

        /* Utility */
        [v-cloak] { display: none; }
        .animate-fade-in { animation: fadeIn 0.3s ease-out forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
    </style>
    
    <script>
        tailwind.config = { 
            darkMode: 'class',
            theme: { extend: { colors: { 'sr-blue': '#0f172a', 'sr-gold': '#d4af37' } } } 
        }
    </script>
</head>
<body class="h-screen w-screen bg-slate-50 text-slate-800 dark:bg-slate-900 dark:text-slate-100">

    <div id="app" class="flex h-full w-full" v-cloak>
        
        <aside class="w-64 bg-white dark:bg-slate-800 border-r border-slate-200 dark:border-slate-700 flex flex-col z-20 flex-shrink-0 h-full shadow-sm transition-colors duration-300">
            <div class="h-16 flex items-center justify-center border-b border-slate-100 dark:border-slate-700 flex-shrink-0">
                <div class="text-xl font-extrabold text-sr-blue dark:text-white tracking-tight flex items-center gap-2">
                    <img src="../image/logo.png" alt="Sutan Raya" class="w-8 h-8 object-contain"> Sutan<span class="text-blue-600 dark:text-blue-400">Raya</span>
                </div>
            </div>

            <nav class="flex-1 overflow-y-auto p-3 space-y-1 custom-scrollbar">
                <div class="text-[10px] font-bold text-slate-400 uppercase px-3 mb-2 mt-2 tracking-wider">Utama</div>
                <a href="#" @click.prevent="changeView('dashboard')" :class="view==='dashboard'?'bg-blue-50 dark:bg-slate-700 text-blue-700 dark:text-blue-300 font-bold':'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700'" class="flex items-center px-3 py-2.5 rounded-lg text-sm transition-colors">
                    <i class="bi bi-grid-1x2-fill w-6"></i> Dashboard
                </a>
                <a href="#" @click.prevent="changeView('bookingManagement')" :class="view==='bookingManagement'?'bg-blue-50 dark:bg-slate-700 text-blue-700 dark:text-blue-300 font-bold':'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700'" class="flex items-center px-3 py-2.5 rounded-lg text-sm transition-colors justify-between">
                    <div class="flex items-center"><i class="bi bi-journal-text w-6"></i> Kelola Booking</div>
                    <span v-if="pendingValidationCount > 0" class="bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full animate-pulse">{{ pendingValidationCount }}</span>
                </a>

                <div class="text-[10px] font-bold text-slate-400 uppercase px-3 mb-2 mt-6 tracking-wider">Reservasi</div>
                <a href="#" @click.prevent="changeView('bookingTravel')" :class="view==='bookingTravel'?'bg-blue-50 dark:bg-slate-700 text-blue-700 dark:text-blue-300 font-bold':'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700'" class="flex items-center px-3 py-2.5 rounded-lg text-sm transition-colors">
                    <i class="bi bi-car-front-fill w-6"></i> Travel & Carter
                </a>
                <a href="#" @click.prevent="changeView('bookingBus')" :class="view==='bookingBus'?'bg-blue-50 dark:bg-slate-700 text-blue-700 dark:text-blue-300 font-bold':'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700'" class="flex items-center px-3 py-2.5 rounded-lg text-sm transition-colors">
                    <i class="bi bi-bus-front-fill w-6"></i> Bus Pariwisata
                </a>

                <div class="text-[10px] font-bold text-slate-400 uppercase px-3 mb-2 mt-6 tracking-wider">Operasional</div>
                <a href="#" @click.prevent="changeView('dispatcher')" :class="view==='dispatcher'?'bg-blue-50 dark:bg-slate-700 text-blue-700 dark:text-blue-300 font-bold':'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700'" class="flex items-center px-3 py-2.5 rounded-lg text-sm transition-colors justify-between">
                    <div class="flex items-center"><i class="bi bi-kanban-fill w-6"></i> Dispatcher</div>
                    <span v-if="pendingGroupsCount > 0" class="bg-orange-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">{{ pendingGroupsCount }}</span>
                </a>
                <a href="#" @click.prevent="changeView('manifest')" :class="view==='manifest'?'bg-blue-50 dark:bg-slate-700 text-blue-700 dark:text-blue-300 font-bold':'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700'" class="flex items-center px-3 py-2.5 rounded-lg text-sm transition-colors">
                    <i class="bi bi-file-earmark-spreadsheet-fill w-6"></i> Laporan Harian
                </a>
                <a href="reports.php" class="flex items-center px-3 py-2.5 rounded-lg text-sm text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                    <i class="bi bi-bar-chart-fill w-6"></i> Statistik & Grafik
                </a>
                
                <div class="text-[10px] font-bold text-slate-400 uppercase px-3 mb-2 mt-6 tracking-wider">Aset</div>
                <a href="#" @click.prevent="changeView('assets')" :class="view==='assets'?'bg-blue-50 dark:bg-slate-700 text-blue-700 dark:text-blue-300 font-bold':'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700'" class="flex items-center px-3 py-2.5 rounded-lg text-sm transition-colors">
                    <i class="bi bi-collection-fill w-6"></i> Armada & Supir
                </a>
                <a href="#" @click.prevent="changeView('routeManagement')" :class="view==='routeManagement'?'bg-blue-50 dark:bg-slate-700 text-blue-700 dark:text-blue-300 font-bold':'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700'" class="flex items-center px-3 py-2.5 rounded-lg text-sm transition-colors">
                    <i class="bi bi-map-fill w-6"></i> Kelola Rute
                </a>
            </nav>
        </aside>

        <main class="flex-1 flex flex-col min-w-0 bg-slate-50 dark:bg-slate-900 relative h-full overflow-hidden transition-colors duration-300">
            <header class="h-16 bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between px-6 shadow-sm z-10 flex-shrink-0 transition-colors duration-300">
                <div>
                    <h2 class="text-lg font-bold text-slate-800 dark:text-white">{{ currentViewTitle }}</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Sistem Operasional V11 Ultimate</p>
                </div>
                <div class="flex items-center gap-4">
                    <div class="text-right hidden sm:block">
                        <div class="text-xs font-bold text-slate-400 uppercase">{{ currentDate }}</div>
                        <div class="text-lg font-mono font-bold text-sr-blue dark:text-sr-gold leading-none">{{ currentTime }}</div>
                    </div>
                    <button @click="toggleDarkMode" class="w-9 h-9 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-yellow-400 hover:bg-slate-200 dark:hover:bg-slate-600 transition-all flex items-center justify-center" title="Mode Gelap/Terang">
                        <i :class="isDarkMode ? 'bi-sun-fill' : 'bi-moon-stars-fill'"></i>
                    </button>
                    <button @click="toggleFullscreen" class="w-9 h-9 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 transition-all flex items-center justify-center">
                        <i :class="isFullscreen ? 'bi-arrows-angle-contract' : 'bi-arrows-fullscreen'"></i>
                    </button>
                </div>
            </header>

            <div class="flex-1 relative overflow-hidden w-full">
                
                <div v-if="view === 'dashboard'" class="absolute inset-0 overflow-y-auto p-6 md:p-8 custom-scrollbar animate-fade-in bg-slate-50 dark:bg-slate-900">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                        <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm flex justify-between items-center transition-all hover:shadow-md">
                            <div><div class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Pendapatan Hari Ini</div><div class="text-2xl font-extrabold text-slate-800 dark:text-white">{{ formatRupiah(todayRevenue) }}</div></div>
                            <div class="w-12 h-12 flex items-center justify-center bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400 rounded-xl"><i class="bi bi-cash-stack text-2xl"></i></div>
                        </div>
                        <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm flex justify-between items-center transition-all hover:shadow-md">
                            <div><div class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Total Order</div><div class="text-2xl font-extrabold text-slate-800 dark:text-white">{{ todayPax + busBookings.length }} <span class="text-sm font-medium text-slate-400">Pax</span></div></div>
                            <div class="w-12 h-12 flex items-center justify-center bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 rounded-xl"><i class="bi bi-receipt text-2xl"></i></div>
                        </div>
                        <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm flex justify-between items-center cursor-pointer hover:shadow-md hover:border-red-200 dark:hover:border-red-900 transition-all group" @click="changeView('bookingManagement')">
                            <div><div class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1 group-hover:text-red-500 transition-colors">Validasi Pending</div><div class="text-2xl font-extrabold text-slate-800 dark:text-white group-hover:text-red-600 transition-colors">{{ pendingValidationCount }}</div></div>
                            <div class="w-12 h-12 flex items-center justify-center bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 rounded-xl group-hover:bg-red-100 dark:group-hover:bg-red-900/40 transition-colors"><i class="bi bi-exclamation-circle text-2xl"></i></div>
                        </div>
                        <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm flex justify-between items-center cursor-pointer hover:shadow-md hover:border-orange-200 dark:hover:border-orange-900 transition-all group" @click="changeView('dispatcher')">
                            <div><div class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1 group-hover:text-orange-500 transition-colors">Antrian Dispatch</div><div class="text-2xl font-extrabold text-slate-800 dark:text-white group-hover:text-orange-600 transition-colors">{{ pendingGroupsCount }}</div></div>
                            <div class="w-12 h-12 flex items-center justify-center bg-orange-50 dark:bg-orange-900/20 text-orange-600 dark:text-orange-400 rounded-xl group-hover:bg-orange-100 dark:group-hover:bg-orange-900/40 transition-colors"><i class="bi bi-clock-history text-2xl"></i></div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 h-[calc(100%-140px)] min-h-[400px]">
                        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 flex flex-col shadow-sm overflow-hidden h-full transition-colors">
                            <div class="p-3 border-b border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800 flex justify-between items-center">
                                <h3 class="font-bold text-slate-700 dark:text-slate-200 text-sm">Keberangkatan</h3>
                                <span class="text-[10px] bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 px-2 py-0.5 rounded font-bold">Dari Padang</span>
                            </div>
                            <div class="flex-1 overflow-y-auto p-3 space-y-3 custom-scrollbar">
                                <div v-if="outboundTrips.length === 0" class="h-full flex flex-col items-center justify-center text-slate-300 dark:text-slate-600"><i class="bi bi-slash-circle text-2xl mb-1"></i><span class="text-xs">Kosong</span></div>
                                <div v-for="trip in outboundTrips" :key="trip.id" class="p-3 rounded-lg border border-slate-100 dark:border-slate-700 bg-white dark:bg-slate-700 hover:shadow-md transition-all group">
                                    <div class="flex justify-between mb-2">
                                        <div><div class="text-lg font-bold text-blue-900 dark:text-blue-200">{{ trip.routeConfig?.time }}</div><div class="text-[10px] text-slate-400 font-bold">{{ trip.routeConfig?.routeId }}</div></div>
                                        <button @click="openTripControl(trip)" class="text-[10px] font-bold px-2 py-1 rounded text-white h-fit shadow-sm" :class="getTripStatusBadge(trip.status)">{{ trip.status }} <i class="bi bi-caret-down-fill"></i></button>
                                    </div>
                                    <div class="text-xs font-bold text-slate-700 dark:text-slate-300 mb-1"><i class="bi bi-truck-front text-blue-500"></i> {{ trip.fleet?.name }}</div>
                                    <div class="text-[10px] text-slate-500 dark:text-slate-400 pt-2 border-t border-slate-50 dark:border-slate-600">Driver: <strong>{{ trip.driver?.name }}</strong></div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 flex flex-col shadow-sm overflow-hidden h-full transition-colors">
                            <div class="p-3 border-b border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800 flex justify-between items-center">
                                <h3 class="font-bold text-slate-700 dark:text-slate-200 text-sm">Kedatangan</h3>
                                <span class="text-[10px] bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300 px-2 py-0.5 rounded font-bold">Ke Padang</span>
                            </div>
                            <div class="flex-1 overflow-y-auto p-3 space-y-3 custom-scrollbar">
                                <div v-if="inboundTrips.length === 0" class="h-full flex flex-col items-center justify-center text-slate-300 dark:text-slate-600"><i class="bi bi-slash-circle text-2xl mb-1"></i><span class="text-xs">Kosong</span></div>
                                <div v-for="trip in inboundTrips" :key="trip.id" class="p-3 rounded-lg border border-slate-100 dark:border-slate-700 bg-white dark:bg-slate-700 hover:shadow-md transition-all group">
                                    <div class="flex justify-between mb-2">
                                        <div><div class="text-lg font-bold text-green-900 dark:text-green-200">{{ trip.routeConfig?.time }}</div><div class="text-[10px] text-slate-400 font-bold">{{ trip.routeConfig?.routeId }}</div></div>
                                        <button @click="openTripControl(trip)" class="text-[10px] font-bold px-2 py-1 rounded text-white h-fit shadow-sm" :class="getTripStatusBadge(trip.status)">{{ trip.status }} <i class="bi bi-caret-down-fill"></i></button>
                                    </div>
                                    <div class="text-xs font-bold text-slate-700 dark:text-slate-300 mb-1"><i class="bi bi-truck-front text-green-500"></i> {{ trip.fleet?.name }}</div>
                                    <div class="text-[10px] text-slate-500 dark:text-slate-400 pt-2 border-t border-slate-50 dark:border-slate-600">Driver: <strong>{{ trip.driver?.name }}</strong></div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 flex flex-col shadow-sm overflow-hidden h-full transition-colors">
                            <div class="p-3 border-b border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800"><h3 class="font-bold text-slate-700 dark:text-slate-200 text-sm">Armada Standby</h3></div>
                            <div class="flex-1 overflow-y-auto p-3 space-y-2 custom-scrollbar">
                                <div v-for="f in fleet" :key="f.id" class="flex justify-between items-center p-2 rounded border border-slate-100 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                                    <div class="flex items-center gap-3"><div class="w-8 h-8 rounded bg-slate-100 dark:bg-slate-600 flex items-center justify-center text-slate-400 dark:text-slate-300"><i :class="f.icon"></i></div><div><div class="text-xs font-bold text-slate-800 dark:text-slate-200">{{ f.name }}</div><div class="text-[10px] text-slate-400">{{ f.plate }}</div></div></div>
                                    <div class="text-[10px] font-bold px-2 py-1 rounded bg-slate-200 dark:bg-slate-600 text-slate-600 dark:text-slate-300">{{ f.status }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="view === 'bookingManagement'" class="absolute inset-0 flex flex-col bg-white dark:bg-slate-800 animate-fade-in">
                    <div class="p-4 border-b border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 sticky top-0 z-20 flex-shrink-0">
                        <div class="flex justify-between items-center mb-4">
                            <div><h2 class="text-lg font-bold text-slate-800 dark:text-white">Kelola Booking</h2><p class="text-xs text-slate-500 dark:text-slate-400">Validasi & Cetak Tiket.</p></div>
                            <div class="flex gap-2">
                                <button @click="bookingManagementTab='travel'" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-colors" :class="bookingManagementTab==='travel'?'bg-sr-blue text-white':'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300'">Travel</button>
                                <button @click="bookingManagementTab='bus'" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-colors" :class="bookingManagementTab==='bus'?'bg-sr-blue text-white':'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300'">Bus</button>
                            </div>
                        </div>
                        <div class="flex justify-between gap-4">
                            <div class="relative flex-1"><i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i><input type="text" v-model="busSearchTerm" placeholder="Cari..." class="w-full pl-9 pr-3 py-1.5 text-sm border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 outline-none"></div>
                            <div v-if="bookingManagementTab==='bus'" class="flex bg-slate-100 dark:bg-slate-700 p-0.5 rounded-lg">
                                <button @click="busViewMode='list'" class="px-3 py-1 rounded text-xs font-bold" :class="busViewMode==='list'?'bg-white dark:bg-slate-600 text-blue-700 dark:text-blue-300 shadow':'text-slate-500 dark:text-slate-400'">List</button>
                                <button @click="busViewMode='calendar'" class="px-3 py-1 rounded text-xs font-bold" :class="busViewMode==='calendar'?'bg-white dark:bg-slate-600 text-blue-700 dark:text-blue-300 shadow':'text-slate-500 dark:text-slate-400'">Kalender</button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex-1 overflow-y-auto custom-scrollbar relative">
                        <table v-if="bookingManagementTab === 'travel' || busViewMode === 'list'" class="w-full text-left text-sm">
                            <thead class="bg-slate-50 dark:bg-slate-700 text-slate-500 dark:text-slate-300 uppercase text-[10px] sticky top-0 z-10 font-bold"><tr><th class="p-4">Waktu</th><th class="p-4">Penumpang</th><th class="p-4">Layanan</th><th class="p-4">Status</th><th class="p-4 text-right">Aksi</th></tr></thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                <tr v-for="b in getManagedBookings" :key="b.id" class="hover:bg-blue-50/30 dark:hover:bg-slate-700/50 transition-colors">
                                    <td class="p-4"><div class="font-bold text-slate-800 dark:text-white">{{ formatDate(b.date) }}</div><div class="text-xs text-slate-500 dark:text-slate-400">{{ b.time || b.duration + ' Hari' }}</div></td>
                                    <td class="p-4"><div class="font-bold text-slate-800 dark:text-white">{{ b.passengerName }}</div><div class="text-xs text-slate-500 dark:text-slate-400">{{ b.passengerPhone }}</div></td>
                                    <td class="p-4"><span class="text-[10px] font-bold px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-600 text-slate-600 dark:text-slate-300 uppercase">{{ b.serviceType }}</span><div class="text-[10px] text-slate-400 mt-1">{{ b.routeId || b.routeName }}</div></td>
                                    <td class="p-4">
                                        <span v-if="b.validationStatus === 'Valid'" class="text-xs font-bold text-green-600 flex items-center gap-1"><i class="bi bi-check-circle-fill"></i> Lunas</span>
                                        <span v-else-if="b.paymentStatus === 'DP'" class="text-xs font-bold text-yellow-600 flex items-center gap-1"><i class="bi bi-hourglass-split"></i> DP</span>
                                        <span v-else class="text-xs font-bold text-red-500 flex items-center gap-1"><i class="bi bi-exclamation-circle-fill"></i> Validasi</span>
                                    </td>
                                    <td class="p-4 text-right space-x-1">
                                        <button @click="printTicket(b)" class="text-xs font-bold bg-slate-100 dark:bg-slate-600 text-slate-600 dark:text-slate-300 px-2 py-1.5 rounded hover:bg-slate-200 dark:hover:bg-slate-500 transition-colors" title="Cetak Tiket"><i class="bi bi-printer"></i></button>
                                        <button v-if="b.validationStatus !== 'Valid'" @click="validatePaymentModal(b)" class="text-xs font-bold bg-white border border-red-200 text-red-600 px-3 py-1.5 rounded-lg hover:bg-red-50 transition-colors">Validasi</button>
                                        <button v-else-if="b.status === 'Pending' && b.serviceType !== 'Bus Pariwisata'" @click="changeView('dispatcher')" class="text-xs font-bold bg-blue-600 text-white px-3 py-1.5 rounded-lg hover:bg-blue-700 shadow-sm transition-transform active:scale-95">🚀 Dispatch</button>
                                        <button v-else-if="['Assigned','On Trip','Tiba'].includes(b.status)" @click="changeView('dashboard')" class="text-xs font-bold bg-green-100 text-green-700 px-3 py-1.5 rounded-lg hover:bg-green-200 shadow-sm transition-colors">📍 Lacak</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div v-if="getManagedBookings.length === 0" class="p-8 text-center text-slate-400 italic">Tidak ada data.</div>

                        <div v-if="bookingManagementTab === 'bus' && busViewMode === 'calendar'" class="p-4 h-full flex flex-col">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="font-bold text-lg text-slate-800 dark:text-white">{{ getMonthName(calendarMonth) }} {{ calendarYear }}</h3>
                                <div class="flex gap-2">
                                    <button @click="changeMonth(-1)" class="w-8 h-8 rounded-full hover:bg-slate-100 dark:hover:bg-slate-700 flex items-center justify-center dark:text-white"><i class="bi bi-chevron-left"></i></button>
                                    <button @click="changeMonth(1)" class="w-8 h-8 rounded-full hover:bg-slate-100 dark:hover:bg-slate-700 flex items-center justify-center dark:text-white"><i class="bi bi-chevron-right"></i></button>
                                </div>
                            </div>
                            <div class="grid grid-cols-7 text-center text-xs font-bold text-slate-400 uppercase mb-2"><div>Sen</div><div>Sel</div><div>Rab</div><div>Kam</div><div>Jum</div><div>Sab</div><div>Min</div></div>
                            <div class="calendar-grid flex-1 rounded-lg overflow-hidden bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                                <div v-for="(day, idx) in calendarDays" :key="idx" class="calendar-day dark:bg-slate-800 dark:border-slate-700" :class="{'other-month': !day.isCurrentMonth}">
                                    <div class="text-right text-xs font-bold mb-1" :class="day.isToday ? 'text-blue-600' : 'text-slate-400 dark:text-slate-500'">{{ day.date }}</div>
                                    <div class="flex-1 overflow-y-auto custom-scrollbar space-y-1">
                                        <div v-for="evt in day.events" :key="evt.id" @click="validatePaymentModal(evt)" class="calendar-event text-[9px] px-1 py-0.5 rounded border truncate" :class="evt.paymentStatus==='Lunas'?'bg-green-50 border-green-500 text-green-700':'bg-yellow-50 border-yellow-500 text-yellow-700'">{{ evt.passengerName }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="view === 'bookingTravel' || view === 'bookingBus'" class="absolute inset-0 flex justify-center p-6 overflow-y-auto custom-scrollbar">
                   <div class="w-full max-w-5xl bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-700 flex flex-col md:flex-row h-fit min-h-[600px]">
                        <div class="w-full md:w-7/12 p-8 border-r border-slate-100 dark:border-slate-700">
                            <div class="mb-6 border-b border-slate-100 dark:border-slate-700 pb-4"><h2 class="text-xl font-extrabold text-sr-blue dark:text-white">{{ view === 'bookingTravel' ? 'Booking Travel' : 'Booking Bus Pariwisata' }}</h2></div>
                             <div class="space-y-4">
                                <div v-if="view === 'bookingTravel'" class="grid grid-cols-3 gap-2 p-1 bg-slate-50 dark:bg-slate-700 rounded-xl border border-slate-200 dark:border-slate-600">
                                    <button v-for="type in ['Travel','Carter','Dropping']" @click="setServiceType(type)" class="py-2 rounded-lg text-xs font-bold transition-all" :class="bookingForm.data.serviceType===type?'bg-white dark:bg-slate-600 shadow-sm text-sr-blue dark:text-white':'text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-600'">{{ type }}</button>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                     <div>
                                         <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Nama</label>
                                         <input v-if="view==='bookingTravel'" type="text" v-model="bookingForm.data.passengerName" class="w-full p-2.5 border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 dark:text-white rounded-lg text-sm outline-none focus:border-blue-500">
                                         <input v-else type="text" v-model="bookingBusForm.passengerName" class="w-full p-2.5 border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 dark:text-white rounded-lg text-sm outline-none focus:border-blue-500">
                                     </div>
                                     <div>
                                         <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">WhatsApp</label>
                                         <input v-if="view==='bookingTravel'" type="text" v-model="bookingForm.data.passengerPhone" class="w-full p-2.5 border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 dark:text-white rounded-lg text-sm outline-none focus:border-blue-500">
                                         <input v-else type="text" v-model="bookingBusForm.passengerPhone" class="w-full p-2.5 border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 dark:text-white rounded-lg text-sm outline-none focus:border-blue-500">
                                     </div>
                                </div>
                                <div v-if="view==='bookingTravel' && bookingForm.data.serviceType==='Travel'" class="flex gap-4">
                                    <label class="flex items-center gap-2 p-2.5 border border-slate-200 dark:border-slate-600 rounded-lg cursor-pointer flex-1 hover:bg-slate-50 dark:hover:bg-slate-700"><input type="radio" value="Umum" v-model="bookingForm.data.passengerType" @change="calculatePrice" class="text-blue-600"><span class="text-xs font-bold text-slate-700 dark:text-slate-300">Umum</span></label>
                                    <label class="flex items-center gap-2 p-2.5 border border-slate-200 dark:border-slate-600 rounded-lg cursor-pointer flex-1 hover:bg-slate-50 dark:hover:bg-slate-700"><input type="radio" value="Pelajar" v-model="bookingForm.data.passengerType" @change="calculatePrice" class="text-blue-600"><span class="text-xs font-bold text-slate-700 dark:text-slate-300">Pelajar</span></label>
                                </div>
                                <div v-if="bookingForm.data.passengerType === 'Pelajar' && bookingForm.data.serviceType==='Travel'" class="p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded-lg animate-fade-in">
                                    <label class="text-[10px] font-bold text-yellow-700 dark:text-yellow-400 uppercase mb-2 block flex items-center"><i class="bi bi-person-vcard mr-2"></i> Upload KTM (Wajib)</label>
                                    <div class="flex items-center gap-3">
                                        <label class="cursor-pointer bg-white dark:bg-slate-800 border border-yellow-300 dark:border-yellow-600 text-yellow-700 dark:text-yellow-400 px-3 py-1.5 rounded text-[10px] font-bold hover:bg-yellow-100 dark:hover:bg-slate-700 shadow-sm flex items-center gap-2"><i class="bi bi-upload"></i> Pilih File <input type="file" accept="image/*" @change="handleKTMUpload" class="hidden"></label>
                                        <span v-if="bookingForm.data.ktmProof" class="text-[10px] text-green-600 dark:text-green-400 font-bold flex items-center"><i class="bi bi-check-circle-fill mr-1"></i> {{ bookingForm.data.ktmProof }}</span>
                                    </div>
                                </div>
                                <div v-if="view==='bookingTravel'" class="grid grid-cols-2 gap-4">
                                    <div class="col-span-2">
                                        <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Rute</label>
                                        <select v-model="bookingForm.data.routeId" class="w-full p-2.5 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg font-bold text-sm text-slate-800 dark:text-white outline-none">
                                            <option :value="''" disabled>Pilih Rute</option>
                                            <option v-for="r in routeConfig" :value="r.id">{{ r.origin }} ⇄ {{ r.destination }}</option>
                                        </select>
                                    </div>
                                    <div><label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Tanggal</label><input type="date" v-model="bookingForm.data.date" class="w-full p-2.5 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg font-bold text-sm text-slate-800 dark:text-white outline-none"></div>
                                    <div>
                                        <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Jam</label>
                                        <select v-if="bookingForm.data.serviceType === 'Travel'" v-model="bookingForm.data.time" @change="resetSeatSelection" class="w-full p-2.5 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg font-bold text-sm text-slate-800 dark:text-white outline-none">
                                            <option value="" disabled>Pilih</option>
                                            <option v-for="t in currentSchedules" :value="t">{{ t }}</option>
                                        </select>
                                        <input v-else type="time" v-model="bookingForm.data.time" class="w-full p-2.5 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg font-bold text-sm text-slate-800 dark:text-white outline-none">
                                    </div>
                                </div>
                                <div v-if="view==='bookingTravel' && bookingForm.data.serviceType==='Dropping'" class="col-span-2 animate-fade-in">
                                    <label class="flex items-center gap-2 p-3 border border-slate-200 dark:border-slate-600 rounded-lg cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                                        <input type="checkbox" v-model="bookingForm.data.isMultiStop" @change="calculatePrice" class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500">
                                        <div><div class="text-xs font-bold text-slate-700 dark:text-slate-300">Banyak Titik Antar (+Biaya)</div><div class="text-[10px] text-slate-400">Centang jika lokasi antar lebih dari satu.</div></div>
                                    </label>
                                </div>
                                <div v-if="view==='bookingBus'" class="grid grid-cols-2 gap-4">
                                     <div class="col-span-2"><label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Rute Bus</label><select v-model="bookingBusForm.routeId" @change="calculateBusPrice" class="w-full p-2.5 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg font-bold text-sm text-slate-800 dark:text-white outline-none"><option value="" disabled>Pilih Tujuan</option><option v-for="r in busRouteConfig" :value="r.id">{{ r.name }}</option></select></div>
                                     <div><label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Jenis Bus</label><select v-model="bookingBusForm.type" @change="calculateBusPrice" class="w-full p-2.5 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg font-bold text-sm text-slate-800 dark:text-white outline-none"><option value="Medium">Medium Bus</option><option value="Big">Big Bus</option></select></div>
                                     <div v-if="bookingBusForm.type==='Medium'"><label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Kapasitas</label><select v-model="bookingBusForm.seatCapacity" @change="calculateBusPrice" class="w-full p-2.5 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg font-bold text-sm text-slate-800 dark:text-white outline-none"><option :value="33">33 Seat</option><option :value="35">35 Seat</option></select></div>
                                     <div v-if="bookingBusForm.type==='Big'"><label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Kelas</label><select v-model="bookingBusForm.seatCapacity" @change="calculateBusPrice" class="w-full p-2.5 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg font-bold text-sm text-slate-800 dark:text-white outline-none"><option :value="45">Standard (45 Seat)</option><option :value="32">Executive (32 Legrest)</option></select></div>
                                     <div v-if="bookingBusForm.type==='Big' && !isLongTrip"><label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Tarif</label><select v-model="bookingBusForm.priceType" @change="calculateBusPrice" class="w-full p-2.5 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg font-bold text-sm text-slate-800 dark:text-white outline-none"><option value="Kantor">Kantor (Public)</option><option value="Agen">Agen (Contract)</option></select></div>
                                     <div v-if="bookingBusForm.type==='Big' && isLongTrip"><label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Paket</label><select v-model="bookingBusForm.packageType" @change="calculateBusPrice" class="w-full p-2.5 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg font-bold text-sm text-slate-800 dark:text-white outline-none"><option value="Unit">Unit Only</option><option value="AllIn">All In</option></select></div>
                                     <div><label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Tanggal</label><input type="date" v-model="bookingBusForm.date" class="w-full p-2.5 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg font-bold text-sm text-slate-800 dark:text-white outline-none"></div>
                                     <div><label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Durasi (Hari)</label><input type="number" v-model="bookingBusForm.duration" @change="calculateBusPrice" class="w-full p-2.5 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg font-bold text-sm text-slate-800 dark:text-white outline-none"></div>
                                </div>
                                <div v-if="view==='bookingBus' && bookingBusForm.type==='Big'" class="p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-lg flex items-start gap-3 animate-fade-in">
                                    <i class="bi bi-exclamation-triangle-fill text-red-500 mt-0.5"></i>
                                    <div><div class="text-xs font-bold text-red-700 dark:text-red-400">Wajib DP Min. Rp 1.000.000</div><div class="text-[10px] text-red-600/80 dark:text-red-400/80">Untuk booking Big Bus, DP bersifat wajib untuk mengunci jadwal.</div></div>
                                </div>
                             </div>
                        </div>
                        <div class="w-full md:w-5/12 bg-slate-50 dark:bg-slate-900 p-8 flex flex-col overflow-y-auto custom-scrollbar">
                             <div class="flex justify-center mb-6">
                                <div v-if="bookingForm.data.routeId && bookingForm.data.serviceType === 'Travel'" class="bg-white dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-600 rounded-[2rem] p-5 shadow-sm relative w-[200px]">
                                    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-16 h-1 bg-slate-200 dark:bg-slate-600 rounded-b-lg"></div>
                                    <div class="space-y-3 mt-2">
                                        <div v-for="row in seatLayout" :key="row.row">
                                            <div class="text-[8px] text-slate-300 dark:text-slate-500 text-center uppercase tracking-widest mb-1">{{ row.label }}</div>
                                            <div class="flex justify-between gap-2">
                                                <div v-for="seat in row.seats" :key="seat.id" class="flex-1 flex justify-center">
                                                    <div v-if="seat.type==='driver'" class="w-9 h-9 bg-slate-200 dark:bg-slate-700 rounded-lg flex items-center justify-center text-[10px] font-bold text-slate-500 dark:text-slate-400">Supir</div>
                                                    <button v-else @click="toggleSeat(seat.id)" :disabled="isSeatOccupied(seat.id)" class="w-9 h-9 rounded-lg font-bold text-xs border transition-all relative" :class="[isSeatOccupied(seat.id) ? 'bg-slate-800 dark:bg-slate-600 text-white cursor-not-allowed border-slate-800 dark:border-slate-600' : (isSeatSelected(seat.id) ? 'bg-sr-blue dark:bg-blue-600 text-white border-sr-blue dark:border-blue-600 shadow-md scale-105' : 'bg-white dark:bg-slate-700 hover:bg-yellow-50 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-600')]">{{ seat.id }}</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                             </div>
                                 <h3 class="text-lg font-bold text-sr-blue dark:text-white mt-2">{{ formatRupiah(currentTotalPrice) }}</h3>
                             <div class="bg-white dark:bg-slate-800 p-4 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 space-y-3">
                                 <h4 class="text-[10px] font-bold text-slate-400 uppercase border-b dark:border-slate-600 pb-2">Pembayaran</h4>
                                 <div class="flex gap-2 text-[10px] font-bold text-slate-500 dark:text-slate-300">
                                    <label class="flex-1 cursor-pointer text-center"><input type="radio" value="Cash" v-model="currentPaymentMethod" class="hidden peer"><div class="py-2 rounded border border-slate-200 dark:border-slate-600 peer-checked:bg-blue-50 dark:peer-checked:bg-blue-900 peer-checked:text-blue-700 dark:peer-checked:text-blue-300">Cash</div></label>
                                    <label class="flex-1 cursor-pointer text-center"><input type="radio" value="Transfer" v-model="currentPaymentMethod" class="hidden peer"><div class="py-2 rounded border border-slate-200 dark:border-slate-600 peer-checked:bg-blue-50 dark:peer-checked:bg-blue-900 peer-checked:text-blue-700 dark:peer-checked:text-blue-300">Transfer</div></label>
                                    <label class="flex-1 cursor-pointer text-center"><input type="radio" value="DP" v-model="currentPaymentMethod" class="hidden peer"><div class="py-2 rounded border border-slate-200 dark:border-slate-600 peer-checked:bg-yellow-50 dark:peer-checked:bg-yellow-900/50 peer-checked:text-yellow-700 dark:peer-checked:text-yellow-300">DP</div></label>
                                </div>
                                <div v-if="currentPaymentMethod === 'Cash'" class="space-y-2 animate-fade-in">
                                    <div><label class="text-[10px] font-bold text-slate-400 uppercase mb-1 block">Lokasi</label><input type="text" v-model="tempPayment.loc" placeholder="Loket / Mobil" class="w-full p-2 border border-slate-200 dark:border-slate-600 rounded-lg text-xs bg-white dark:bg-slate-700 dark:text-white"></div>
                                    <div><label class="text-[10px] font-bold text-slate-400 uppercase mb-1 block">Penerima</label><input type="text" v-model="tempPayment.recv" placeholder="Nama Staf" class="w-full p-2 border border-slate-200 dark:border-slate-600 rounded-lg text-xs bg-white dark:bg-slate-700 dark:text-white"></div>
                                </div>
                                <div v-else-if="currentPaymentMethod === 'Transfer'" class="space-y-2 animate-fade-in">
                                    <div class="text-[10px] text-center bg-slate-100 dark:bg-slate-700 p-2 rounded text-slate-600 dark:text-slate-300 font-mono">BCA: 123456789 (Sutan Raya)</div>
                                    <label class="block w-full text-xs text-center border-2 border-dashed border-slate-300 dark:border-slate-600 rounded-lg p-3 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                                        <span v-if="!tempPayment.proof" class="text-slate-400"><i class="bi bi-cloud-upload text-lg block mb-1"></i>Upload Bukti</span>
                                        <span v-else class="text-green-600 dark:text-green-400 font-bold flex flex-col items-center"><i class="bi bi-check-circle-fill text-lg mb-1"></i> {{ tempPayment.proof }}</span>
                                        <input type="file" accept="image/*" @change="handlePaymentProofUpload" class="hidden">
                                    </label>
                                </div>
                                <div v-else-if="currentPaymentMethod === 'DP'" class="space-y-2 animate-fade-in">
                                    <div><label class="text-[10px] font-bold text-slate-400 uppercase mb-1 block">Nominal DP</label><input type="number" v-model="tempPayment.dpAmount" placeholder="Min. 100rb" class="w-full p-2 border border-yellow-200 dark:border-yellow-700 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg text-xs font-bold text-yellow-700 dark:text-yellow-400"></div>
                                    <div class="flex gap-2">
                                        <label class="flex-1 cursor-pointer text-center"><input type="radio" value="Cash" v-model="tempPayment.dpMethod" class="hidden peer"><div class="py-1.5 rounded border border-slate-200 dark:border-slate-600 text-[10px] font-bold peer-checked:bg-slate-800 peer-checked:text-white dark:peer-checked:bg-white dark:peer-checked:text-slate-900">DP Cash</div></label>
                                        <label class="flex-1 cursor-pointer text-center"><input type="radio" value="Transfer" v-model="tempPayment.dpMethod" class="hidden peer"><div class="py-1.5 rounded border border-slate-200 dark:border-slate-600 text-[10px] font-bold peer-checked:bg-slate-800 peer-checked:text-white dark:peer-checked:bg-white dark:peer-checked:text-slate-900">DP Transfer</div></label>
                                    </div>
                                    <div v-if="tempPayment.dpMethod === 'Cash'" class="space-y-2 animate-fade-in">
                                        <div><label class="text-[10px] font-bold text-slate-400 uppercase mb-1 block">Lokasi</label><input type="text" v-model="tempPayment.loc" placeholder="Loket / Mobil" class="w-full p-2 border border-slate-200 dark:border-slate-600 rounded-lg text-xs bg-white dark:bg-slate-700 dark:text-white"></div>
                                        <div><label class="text-[10px] font-bold text-slate-400 uppercase mb-1 block">Penerima</label><input type="text" v-model="tempPayment.recv" placeholder="Nama Staf" class="w-full p-2 border border-slate-200 dark:border-slate-600 rounded-lg text-xs bg-white dark:bg-slate-700 dark:text-white"></div>
                                    </div>
                                    <div v-else class="space-y-2 animate-fade-in">
                                        <div class="text-[10px] text-center bg-slate-100 dark:bg-slate-700 p-2 rounded text-slate-600 dark:text-slate-300 font-mono">BCA: 123456789 (Sutan Raya)</div>
                                        <label class="block w-full text-xs text-center border-2 border-dashed border-slate-300 dark:border-slate-600 rounded-lg p-3 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                                            <span v-if="!tempPayment.proof" class="text-slate-400"><i class="bi bi-cloud-upload text-lg block mb-1"></i>Upload Bukti DP</span>
                                            <span v-else class="text-green-600 dark:text-green-400 font-bold flex flex-col items-center"><i class="bi bi-check-circle-fill text-lg mb-1"></i> {{ tempPayment.proof }}</span>
                                            <input type="file" accept="image/*" @change="handlePaymentProofUpload" class="hidden">
                                        </label>
                                    </div>
                                </div>
                             </div>
                             <div class="mt-auto pt-6 border-t border-slate-200 dark:border-slate-700">
                                <button @click="view==='bookingTravel'?saveBooking():saveBusBooking()" :disabled="currentTotalPrice === 0" class="w-full py-3 bg-sr-blue dark:bg-blue-600 hover:bg-slate-800 disabled:bg-slate-300 text-white font-bold rounded-xl shadow-lg">Proses Booking</button>
                            </div>
                        </div>
                   </div>
                </div>

                <div v-if="view === 'dispatcher'" class="absolute inset-0 p-6 custom-scrollbar space-y-8">
                    
                    <div>
                        <h2 class="font-bold text-gray-700 mb-4 text-lg flex items-center"><i class="bi bi-inbox-fill mr-2 text-orange-500"></i> Antrian Booking (Perlu Armada)</h2>
                        
                        <div v-if="pendingGroupsCount === 0" class="p-12 text-center border-2 border-dashed border-gray-300 rounded-2xl text-gray-400 bg-white">
                            <i class="bi bi-check-circle-fill text-4xl mb-2 block text-green-200"></i>
                            <p>Semua jadwal sudah di-dispatch atau belum ada booking.</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                            <div v-for="group in groupedBookings" :key="group.key" class="bg-white rounded-2xl shadow-sm border border-gray-200 p-0 relative overflow-hidden hover:shadow-lg transition-all group flex flex-col h-full">
                                <div class="p-5 border-b border-gray-50 bg-gray-50/50 flex justify-between items-start relative">
                                    <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-orange-500"></div>
                                    <div>
                                        <div class="text-3xl font-bold text-gray-900">{{ group.time }}</div>
                                        <div class="text-xs text-gray-500 font-bold mt-1 uppercase tracking-wide">{{ formatDate(group.date) }}</div>
                                        <div class="text-xs font-semibold text-gray-600 mt-1 flex items-center gap-1"><span class="bg-white px-1 rounded border">{{ group.routeConfig?.origin || 'Custom' }}</span> <i class="bi bi-arrow-right text-gray-400"></i> <span class="bg-white px-1 rounded border">{{ group.routeConfig?.destination || group.routeConfig?.name }}</span></div>
                                    </div>
                                    <span class="bg-orange-100 text-orange-700 text-[10px] font-bold px-2 py-1 rounded uppercase">Pending</span>
                                </div>
                                <div class="flex-1 overflow-y-auto p-2 custom-scrollbar max-h-64 bg-white">
                                    <div v-for="p in group.passengers" class="p-3 border-b border-gray-50 last:border-0 hover:bg-gray-50 transition-colors rounded-lg">
                                        <div class="flex justify-between items-start">
                                            <div class="flex-1">
                                                <div class="font-bold text-sm text-gray-800 flex items-center">
                                                    {{ p.passengerName }} 
                                                    <span v-if="p.serviceType==='Travel'" class="ml-2 text-[10px] bg-gray-200 px-1.5 rounded font-mono">{{ p.seatNumbers || '-' }}</span>
                                                    <span v-else class="ml-2 text-[10px] bg-purple-100 text-purple-700 px-1.5 rounded font-bold border border-purple-200">Charter</span>
                                                </div>
                                                <div class="text-[11px] text-gray-500 mt-0.5"><i class="bi bi-whatsapp mr-1"></i> {{ p.passengerPhone }}</div>
                                                <div class="text-[10px] mt-2 flex gap-1 flex-wrap items-center">
                                                    <button v-if="p.validationStatus === 'Menunggu Validasi'" @click="viewProof(p)" class="px-2 py-0.5 rounded bg-red-100 text-red-700 border border-red-200 font-bold flex items-center gap-1 animate-pulse">
                                                        <i class="bi bi-exclamation-circle-fill"></i> Cek Bukti
                                                    </button>
                                                    <span v-else-if="p.validationStatus === 'Valid'" class="px-2 py-0.5 rounded bg-green-100 text-green-700 border border-green-200 font-bold flex items-center gap-1">
                                                        <i class="bi bi-check-circle-fill"></i> Lunas
                                                    </span>
                                                    <span v-else class="px-2 py-0.5 rounded bg-gray-100 text-gray-600 border border-gray-200 font-bold">{{ p.paymentStatus }}</span>
                                                </div>
                                            </div>
                                            <div class="flex flex-col gap-1">
                                                <button @click="copyWa(p)" class="w-7 h-7 flex items-center justify-center rounded bg-green-50 text-green-600 hover:bg-green-100 border border-green-100"><i class="bi bi-whatsapp"></i></button>
                                                <button @click="viewTicket(p)" class="w-7 h-7 flex items-center justify-center rounded bg-blue-50 text-blue-600 hover:bg-blue-100 border border-blue-100"><i class="bi bi-ticket-perforated"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="p-4 bg-gray-50 border-t border-gray-200 flex justify-between items-center">
                                    <div class="text-xs text-gray-500">Total Muatan: <strong class="text-gray-900 text-sm">{{ group.totalPassengers }}</strong></div>
                                    <button @click="openDispatchModal(group)" class="bg-sr-blue text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-slate-800 shadow-lg active:scale-95 transition-all">Assign Armada</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="activeTrips.length > 0">
                        <h2 class="font-bold text-gray-700 mb-4 text-lg flex items-center"><i class="bi bi-broadcast mr-2 text-green-500"></i> Sedang Berjalan</h2>
                        <div class="space-y-4">
                            <div v-for="trip in activeTrips" :key="trip.id" class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4 hover:border-blue-300 transition-colors">
                                <div class="flex items-center gap-6">
                                    <div class="text-center px-4 border-r border-gray-100">
                                        <div class="text-2xl font-bold text-gray-800">{{ trip.routeConfig.time }}</div>
                                        <div class="text-xs text-gray-500 font-bold uppercase">{{ trip.routeConfig.routeId }}</div>
                                    </div>
                                    <div>
                                        <div class="font-bold text-lg text-blue-900 flex items-center gap-2">
                                            {{ trip.fleet.name }} <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded font-mono border">{{ trip.fleet.plate }}</span>
                                        </div>
                                        <div class="text-sm text-gray-500 font-medium mt-1"><i class="bi bi-person-badge-fill mr-1 text-gray-400"></i> {{ trip.driver.name }} • {{ trip.passengers.length }} Pax</div>
                                    </div>
                                </div>
                                <button @click="openTripControl(trip)" class="px-6 py-2 bg-green-50 text-green-700 rounded-lg font-bold text-sm hover:bg-green-100 transition-colors border border-green-200 flex items-center gap-2"><i class="bi bi-gear-fill"></i> Kontrol</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-if="view === 'routeManagement'" class="absolute inset-0 p-6 custom-scrollbar space-y-6">
                    <div class="flex justify-between items-center">
                        <h2 class="text-2xl font-bold text-sr-blue dark:text-white">Kelola Rute</h2>
                        <button @click="openRouteModal()" class="bg-sr-blue dark:bg-blue-600 text-white px-4 py-2 rounded-lg font-bold shadow-lg hover:bg-slate-800 transition-colors"><i class="bi bi-plus-lg mr-2"></i> Tambah Rute</button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                        <div v-for="r in routeConfig" :key="r.id" class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-5 relative overflow-hidden group">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <div class="text-lg font-bold text-slate-800 dark:text-white">{{ r.origin }} <i class="bi bi-arrow-right mx-1 text-slate-400"></i> {{ r.destination }}</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400 font-mono mt-1">{{ r.id }}</div>
                                </div>
                                <div class="flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button @click="openRouteModal(r)" class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 hover:bg-blue-100 flex items-center justify-center"><i class="bi bi-pencil-fill text-xs"></i></button>
                                    <button @click="deleteRoute(r.id)" class="w-8 h-8 rounded-full bg-red-50 text-red-600 hover:bg-red-100 flex items-center justify-center"><i class="bi bi-trash-fill text-xs"></i></button>
                                </div>
                            </div>
                            
                            <div class="space-y-3">
                                <div class="bg-slate-50 dark:bg-slate-700/50 p-3 rounded-lg">
                                    <div class="text-[10px] font-bold text-slate-400 uppercase mb-2">Jadwal Keberangkatan</div>
                                    <div class="flex flex-wrap gap-2">
                                        <span v-for="t in r.schedules" class="bg-white dark:bg-slate-600 border border-slate-200 dark:border-slate-500 px-2 py-1 rounded text-xs font-bold text-slate-600 dark:text-slate-300">{{ t }}</span>
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="bg-slate-50 dark:bg-slate-700/50 p-3 rounded-lg">
                                        <div class="text-[10px] font-bold text-slate-400 uppercase mb-1">Umum</div>
                                        <div class="font-bold text-slate-700 dark:text-slate-200">{{ formatRupiah(r.prices.umum) }}</div>
                                    </div>
                                    <div class="bg-slate-50 dark:bg-slate-700/50 p-3 rounded-lg">
                                        <div class="text-[10px] font-bold text-slate-400 uppercase mb-1">Pelajar</div>
                                        <div class="font-bold text-slate-700 dark:text-slate-200">{{ formatRupiah(r.prices.pelajar) }}</div>
                                    </div>
                                    <div class="bg-slate-50 dark:bg-slate-700/50 p-3 rounded-lg">
                                        <div class="text-[10px] font-bold text-slate-400 uppercase mb-1">Dropping</div>
                                        <div class="font-bold text-slate-700 dark:text-slate-200">{{ formatRupiah(r.prices.dropping) }}</div>
                                    </div>
                                    <div class="bg-slate-50 dark:bg-slate-700/50 p-3 rounded-lg">
                                        <div class="text-[10px] font-bold text-slate-400 uppercase mb-1">Carter</div>
                                        <div class="font-bold text-slate-700 dark:text-slate-200">{{ formatRupiah(r.prices.carter) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="view === 'assets'" class="absolute inset-0 p-6 custom-scrollbar space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Armada Section -->
                        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm flex flex-col h-[calc(100vh-140px)]">
                            <div class="p-4 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-slate-50 dark:bg-slate-900/50 rounded-t-xl">
                                <div>
                                    <h3 class="font-bold text-slate-800 dark:text-white">Armada</h3>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Kelola Data Kendaraan</p>
                                </div>
                                <button @click="openVehicleModal()" class="bg-sr-blue dark:bg-blue-600 text-white px-3 py-1.5 rounded-lg text-xs font-bold shadow hover:bg-slate-800 transition-colors"><i class="bi bi-plus-lg mr-1"></i> Tambah</button>
                            </div>
                            <div class="flex-1 overflow-y-auto p-4 space-y-3 custom-scrollbar">
                                <div v-if="fleet.length === 0" class="text-center text-slate-400 py-10 italic text-sm">Belum ada data armada.</div>
                                <div v-for="f in fleet" :key="f.id" class="p-3 rounded-lg border border-slate-100 dark:border-slate-700 bg-white dark:bg-slate-700/50 hover:shadow-md transition-all group relative">
                                    <div class="flex justify-between items-start">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-lg bg-slate-100 dark:bg-slate-600 flex items-center justify-center text-slate-500 dark:text-slate-300 text-lg"><i :class="f.icon"></i></div>
                                            <div>
                                                <div class="font-bold text-slate-800 dark:text-white text-sm">{{ f.name }}</div>
                                                <div class="text-xs text-slate-500 dark:text-slate-400 font-mono">{{ f.plate }} • {{ f.capacity }} Seat</div>
                                            </div>
                                        </div>
                                        <span class="text-[10px] font-bold px-2 py-1 rounded" :class="getVehicleStatusClass(f.status)">{{ f.status }}</span>
                                    </div>
                                    <div class="absolute top-3 right-3 opacity-0 group-hover:opacity-100 transition-opacity flex gap-1 bg-white dark:bg-slate-800 p-1 rounded-lg shadow-sm border border-slate-100 dark:border-slate-600">
                                        <button @click="openVehicleModal(f)" class="w-6 h-6 rounded flex items-center justify-center text-blue-600 hover:bg-blue-50 dark:hover:bg-slate-700"><i class="bi bi-pencil-fill text-[10px]"></i></button>
                                        <button @click="deleteVehicle(f.id)" class="w-6 h-6 rounded flex items-center justify-center text-red-600 hover:bg-red-50 dark:hover:bg-slate-700"><i class="bi bi-trash-fill text-[10px]"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Supir Section -->
                        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm flex flex-col h-[calc(100vh-140px)]">
                            <div class="p-4 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-slate-50 dark:bg-slate-900/50 rounded-t-xl">
                                <div>
                                    <h3 class="font-bold text-slate-800 dark:text-white">Supir</h3>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Kelola Data Pengemudi</p>
                                </div>
                                <button @click="openDriverModal()" class="bg-sr-blue dark:bg-blue-600 text-white px-3 py-1.5 rounded-lg text-xs font-bold shadow hover:bg-slate-800 transition-colors"><i class="bi bi-plus-lg mr-1"></i> Tambah</button>
                            </div>
                            <div class="flex-1 overflow-y-auto p-4 space-y-3 custom-scrollbar">
                                <div v-if="drivers.length === 0" class="text-center text-slate-400 py-10 italic text-sm">Belum ada data supir.</div>
                                <div v-for="d in drivers" :key="d.id" class="p-3 rounded-lg border border-slate-100 dark:border-slate-700 bg-white dark:bg-slate-700/50 hover:shadow-md transition-all group relative">
                                    <div class="flex justify-between items-start">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-600 flex items-center justify-center text-slate-500 dark:text-slate-300 text-lg"><i class="bi bi-person-fill"></i></div>
                                            <div>
                                                <div class="font-bold text-slate-800 dark:text-white text-sm">{{ d.name }}</div>
                                                <div class="text-xs text-slate-500 dark:text-slate-400"><i class="bi bi-whatsapp mr-1"></i> {{ d.phone }}</div>
                                                <div class="text-[10px] text-slate-400 mt-0.5">SIM: {{ d.licenseType || '-' }}</div>
                                            </div>
                                        </div>
                                        <span class="text-[10px] font-bold px-2 py-1 rounded" :class="getDriverStatusClass(d.status)">{{ d.status }}</span>
                                    </div>
                                    <div class="absolute top-3 right-3 opacity-0 group-hover:opacity-100 transition-opacity flex gap-1 bg-white dark:bg-slate-800 p-1 rounded-lg shadow-sm border border-slate-100 dark:border-slate-600">
                                        <button @click="openDriverModal(d)" class="w-6 h-6 rounded flex items-center justify-center text-blue-600 hover:bg-blue-50 dark:hover:bg-slate-700"><i class="bi bi-pencil-fill text-[10px]"></i></button>
                                        <button @click="deleteDriver(d.id)" class="w-6 h-6 rounded flex items-center justify-center text-red-600 hover:bg-red-50 dark:hover:bg-slate-700"><i class="bi bi-trash-fill text-[10px]"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Vehicle Modal -->
                <div v-if="isVehicleModalVisible" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm animate-fade-in">
                    <div class="bg-white dark:bg-slate-800 w-full max-w-md rounded-2xl shadow-2xl overflow-hidden flex flex-col">
                        <div class="p-5 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-slate-50 dark:bg-slate-900/50">
                            <h3 class="text-lg font-bold text-slate-800 dark:text-white">{{ vehicleModal.mode === 'add' ? 'Tambah Armada' : 'Edit Armada' }}</h3>
                            <button @click="closeVehicleModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"><i class="bi bi-x-lg"></i></button>
                        </div>
                        <div class="p-6 space-y-4">
                            <div><label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Nama Unit</label><input type="text" v-model="vehicleModal.data.name" class="w-full p-2.5 border rounded-lg dark:bg-slate-700 dark:border-slate-600 dark:text-white text-sm" placeholder="Contoh: Hiace Premio 01"></div>
                            <div class="grid grid-cols-2 gap-4">
                                <div><label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Plat Nomor</label><input type="text" v-model="vehicleModal.data.plate" class="w-full p-2.5 border rounded-lg dark:bg-slate-700 dark:border-slate-600 dark:text-white text-sm" placeholder="BA 1234 XX"></div>
                                <div><label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Kapasitas</label><input type="number" v-model="vehicleModal.data.capacity" class="w-full p-2.5 border rounded-lg dark:bg-slate-700 dark:border-slate-600 dark:text-white text-sm"></div>
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Status</label>
                                <select v-model="vehicleModal.data.status" class="w-full p-2.5 border rounded-lg dark:bg-slate-700 dark:border-slate-600 dark:text-white text-sm">
                                    <option value="Tersedia">Tersedia</option>
                                    <option value="Perbaikan">Perbaikan</option>
                                    <option value="On Trip">On Trip</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Icon</label>
                                <div class="flex gap-2">
                                    <button v-for="icon in ['bi-truck-front-fill', 'bi-bus-front-fill', 'bi-car-front-fill']" @click="vehicleModal.data.icon = icon" class="w-10 h-10 rounded-lg border flex items-center justify-center text-lg transition-colors" :class="vehicleModal.data.icon === icon ? 'bg-blue-50 border-blue-500 text-blue-600' : 'border-slate-200 dark:border-slate-600 text-slate-400 hover:bg-slate-50'"><i :class="icon"></i></button>
                                </div>
                            </div>
                        </div>
                        <div class="p-5 border-t border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 flex justify-end gap-3">
                            <button @click="closeVehicleModal" class="px-4 py-2 text-slate-600 font-bold hover:bg-slate-200 rounded-lg transition-colors text-sm">Batal</button>
                            <button @click="saveVehicle" class="px-6 py-2 bg-sr-blue dark:bg-blue-600 text-white font-bold rounded-lg shadow-lg hover:bg-slate-800 transition-colors text-sm">Simpan</button>
                        </div>
                    </div>
                </div>

                <!-- Driver Modal -->
                <div v-if="isDriverModalVisible" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm animate-fade-in">
                    <div class="bg-white dark:bg-slate-800 w-full max-w-md rounded-2xl shadow-2xl overflow-hidden flex flex-col">
                        <div class="p-5 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-slate-50 dark:bg-slate-900/50">
                            <h3 class="text-lg font-bold text-slate-800 dark:text-white">{{ driverModal.mode === 'add' ? 'Tambah Supir' : 'Edit Supir' }}</h3>
                            <button @click="closeDriverModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"><i class="bi bi-x-lg"></i></button>
                        </div>
                        <div class="p-6 space-y-4">
                            <div><label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Nama Lengkap</label><input type="text" v-model="driverModal.data.name" class="w-full p-2.5 border rounded-lg dark:bg-slate-700 dark:border-slate-600 dark:text-white text-sm" placeholder="Nama Supir"></div>
                            <div><label class="text-xs font-bold text-slate-500 uppercase mb-1 block">No. WhatsApp</label><input type="text" v-model="driverModal.data.phone" class="w-full p-2.5 border rounded-lg dark:bg-slate-700 dark:border-slate-600 dark:text-white text-sm" placeholder="08..."></div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Jenis SIM</label>
                                    <select v-model="driverModal.data.licenseType" class="w-full p-2.5 border rounded-lg dark:bg-slate-700 dark:border-slate-600 dark:text-white text-sm">
                                        <option value="A Umum">A Umum</option>
                                        <option value="B1 Umum">B1 Umum</option>
                                        <option value="B2 Umum">B2 Umum</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Status</label>
                                    <select v-model="driverModal.data.status" class="w-full p-2.5 border rounded-lg dark:bg-slate-700 dark:border-slate-600 dark:text-white text-sm">
                                        <option value="Standby">Standby</option>
                                        <option value="Jalan">Jalan</option>
                                        <option value="Libur">Libur</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="p-5 border-t border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 flex justify-end gap-3">
                            <button @click="closeDriverModal" class="px-4 py-2 text-slate-600 font-bold hover:bg-slate-200 rounded-lg transition-colors text-sm">Batal</button>
                            <button @click="saveDriver" class="px-6 py-2 bg-sr-blue dark:bg-blue-600 text-white font-bold rounded-lg shadow-lg hover:bg-slate-800 transition-colors text-sm">Simpan</button>
                        </div>
                    </div>
                </div>

                <!-- Route Modal -->
                <div v-if="isRouteModalVisible" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm animate-fade-in">
                    <div class="bg-white dark:bg-slate-800 w-full max-w-2xl rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
                        <div class="p-6 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-slate-50 dark:bg-slate-900/50">
                            <h3 class="text-lg font-bold text-slate-800 dark:text-white">{{ routeModalMode === 'add' ? 'Tambah Rute Baru' : 'Edit Rute' }}</h3>
                            <button @click="isRouteModalVisible = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"><i class="bi bi-x-lg text-xl"></i></button>
                        </div>
                        <div class="p-6 overflow-y-auto custom-scrollbar space-y-6">
                            <div class="grid grid-cols-2 gap-4">
                                <div><label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Asal (Origin)</label><input type="text" v-model="routeForm.origin" class="w-full p-2 border rounded-lg dark:bg-slate-700 dark:border-slate-600 dark:text-white"></div>
                                <div><label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Tujuan (Destination)</label><input type="text" v-model="routeForm.destination" class="w-full p-2 border rounded-lg dark:bg-slate-700 dark:border-slate-600 dark:text-white"></div>
                            </div>
                            
                            <div>
                                <label class="text-xs font-bold text-slate-500 uppercase mb-2 block">Jadwal (Pisahkan dengan koma)</label>
                                <input type="text" v-model="routeForm.schedulesInput" placeholder="Contoh: 08:00, 10:00, 14:00" class="w-full p-2 border rounded-lg dark:bg-slate-700 dark:border-slate-600 dark:text-white font-mono">
                                <div class="mt-2 flex flex-wrap gap-2">
                                    <span v-for="t in routeForm.schedulesInput.split(',').map(s=>s.trim()).filter(s=>s)" class="bg-blue-50 text-blue-600 px-2 py-1 rounded text-xs font-bold">{{ t }}</span>
                                </div>
                            </div>

                            <div class="space-y-4 border-t pt-4 dark:border-slate-700">
                                <h4 class="font-bold text-slate-700 dark:text-slate-300">Harga Tiket</h4>
                                <div class="grid grid-cols-2 gap-4">
                                    <div><label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Umum</label><input type="number" v-model="routeForm.prices.umum" class="w-full p-2 border rounded-lg dark:bg-slate-700 dark:border-slate-600 dark:text-white"></div>
                                    <div><label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Pelajar</label><input type="number" v-model="routeForm.prices.pelajar" class="w-full p-2 border rounded-lg dark:bg-slate-700 dark:border-slate-600 dark:text-white"></div>
                                    <div><label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Dropping</label><input type="number" v-model="routeForm.prices.dropping" class="w-full p-2 border rounded-lg dark:bg-slate-700 dark:border-slate-600 dark:text-white"></div>
                                    <div><label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Carter</label><input type="number" v-model="routeForm.prices.carter" class="w-full p-2 border rounded-lg dark:bg-slate-700 dark:border-slate-600 dark:text-white"></div>
                                </div>
                            </div>
                        </div>
                        <div class="p-6 border-t border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 flex justify-end gap-3">
                            <button @click="isRouteModalVisible = false" class="px-4 py-2 text-slate-600 font-bold hover:bg-slate-200 rounded-lg transition-colors">Batal</button>
                            <button @click="saveRoute()" class="px-6 py-2 bg-sr-blue dark:bg-blue-600 text-white font-bold rounded-lg shadow-lg hover:bg-slate-800 transition-colors">Simpan Rute</button>
                        </div>
                    </div>
                </div>

                <div v-if="isTicketModalVisible" class="fixed inset-0 bg-black/90 z-50 flex items-center justify-center p-4 backdrop-blur-sm" @click.self="isTicketModalVisible=false">
                    <div class="w-full max-w-sm transform transition-all scale-100 animate-fade-in">
                        <div class="text-white/70 text-center mb-4 text-sm font-medium cursor-pointer hover:text-white" @click="isTicketModalVisible=false">Klik area gelap untuk tutup</div>
                        
                        <div class="shadow-2xl rounded-[1.5rem] overflow-hidden relative">
                            <div class="bg-gradient-to-br from-blue-800 to-blue-600 p-8 text-white relative overflow-hidden rounded-t-[1.5rem]">
                                <div class="absolute top-0 right-0 w-32 h-32 bg-white opacity-10 rounded-full -mr-10 -mt-10"></div>
                                <div class="flex justify-between items-start mb-8 relative z-10">
                                    <div>
                                        <div class="text-3xl font-bold tracking-tight">Sutan<span class="font-light">Raya</span></div>
                                        <div class="text-[10px] opacity-80 uppercase tracking-widest mt-1 font-bold">E-Ticket</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-[10px] opacity-60 uppercase font-bold">Booking ID</div>
                                        <div class="font-mono text-lg font-bold">#{{ ticketData.id.toString().slice(-6) }}</div>
                                    </div>
                                </div>
                                <div class="flex justify-between items-end relative z-10">
                                    <div>
                                        <div class="text-[10px] opacity-60 uppercase font-bold mb-1">Penumpang</div>
                                        <div class="text-xl font-bold truncate max-w-[160px]">{{ ticketData.passengerName }}</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-[10px] opacity-60 uppercase font-bold mb-1">Waktu</div>
                                        <div class="text-xl font-bold">{{ ticketData.time || 'Bus' }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-white p-8 pt-10 relative rounded-b-[1.5rem]">
                                <div class="absolute top-0 left-0 w-full h-4 -mt-2 bg-white" style="clip-path: polygon(0 0, 100% 0, 100% 100%, 0 100%, 0 0, 1rem 0.5rem, 2rem 0, 3rem 0.5rem, 4rem 0, 5rem 0.5rem, 6rem 0, 7rem 0.5rem, 8rem 0, 9rem 0.5rem, 10rem 0, 11rem 0.5rem, 12rem 0, 13rem 0.5rem, 14rem 0, 15rem 0.5rem, 16rem 0, 17rem 0.5rem, 18rem 0, 19rem 0.5rem, 20rem 0, 21rem 0.5rem, 22rem 0, 23rem 0.5rem, 24rem 0);"></div>
                                <div class="flex justify-between mb-5 border-b border-dashed border-gray-200 pb-5">
                                    <div><div class="text-xs text-gray-400 uppercase font-bold">Rute</div><div class="font-bold text-gray-800 text-sm">{{ ticketData.routeId }}</div></div>
                                    <div class="text-right"><div class="text-xs text-gray-400 uppercase font-bold">Tanggal</div><div class="font-bold text-gray-800 text-sm">{{ formatDate(ticketData.date) }}</div></div>
                                </div>
                                <div class="grid grid-cols-3 gap-2 mb-6">
                                    <div class="bg-gray-50 p-2 rounded text-center"><div class="text-[10px] text-gray-400 uppercase font-bold">Kursi</div><div class="font-bold text-gray-800 text-sm">{{ ticketData.seatNumbers || 'UNIT' }}</div></div>
                                    <div class="bg-gray-50 p-2 rounded text-center"><div class="text-[10px] text-gray-400 uppercase font-bold">Layanan</div><div class="font-bold text-blue-600 text-sm">{{ ticketData.serviceType }}</div></div>
                                    <div class="bg-gray-50 p-2 rounded text-center"><div class="text-[10px] text-gray-400 uppercase font-bold">Validasi</div><div class="font-bold text-sm" :class="ticketData.validationStatus==='Valid'?'text-green-600':(ticketData.validationStatus==='Menunggu Validasi'?'text-red-500':'text-gray-600')">{{ ticketData.validationStatus === 'Valid' ? 'VALID' : 'WAIT' }}</div></div>
                                </div>
                                <div class="space-y-3 mb-6 bg-blue-50/50 p-4 rounded-xl border border-blue-100">
                                    <div class="flex gap-3"><i class="bi bi-geo-alt-fill text-blue-300"></i> <div><div class="text-[10px] text-gray-400 uppercase font-bold">Jemput</div><div class="text-xs font-bold text-gray-700">{{ ticketData.pickupAddress || 'Sesuai Maps' }}</div></div></div>
                                    <div class="flex gap-3"><i class="bi bi-flag-fill text-blue-300"></i> <div><div class="text-[10px] text-gray-400 uppercase font-bold">Tujuan</div><div class="text-xs font-bold text-gray-700">{{ ticketData.dropoffAddress || '-' }}</div></div></div>
                                </div>
                                <button @click="printTicket(ticketData)" class="w-full py-3 rounded-xl border-2 border-gray-100 text-gray-500 font-bold text-sm hover:bg-gray-50 hover:text-gray-800 transition-colors flex items-center justify-center gap-2"><i class="bi bi-printer"></i> Cetak Tiket</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="isProofModalVisible" class="fixed inset-0 bg-black/95 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
                    <div class="bg-white p-6 rounded-2xl text-center max-w-lg w-full shadow-2xl animate-fade-in">
                        <h3 class="text-xl font-bold text-gray-800 mb-1">Validasi Pembayaran</h3>
                        <p class="text-sm text-gray-500 mb-4">Metode: <span class="font-bold text-sutan-blue-900 uppercase">{{ validationData.paymentMethod }}</span></p>
                        
                        <div v-if="validationData.paymentMethod === 'Cash'" class="bg-gray-50 p-6 rounded-xl border border-gray-200 mb-6 text-left space-y-3">
                            <div class="flex justify-between"><span class="text-gray-500 text-sm">Lokasi:</span> <span class="font-bold text-gray-800">{{ validationData.paymentLocation || '-' }}</span></div>
                            <div class="flex justify-between"><span class="text-gray-500 text-sm">Penerima:</span> <span class="font-bold text-gray-800">{{ validationData.paymentReceiver || '-' }}</span></div>
                        </div>

                        <div v-if="(validationData.paymentMethod === 'Transfer' || validationData.paymentMethod === 'DP') && validationData.paymentProof" class="mb-6">
                            <div class="bg-slate-100 p-2 rounded-xl mb-2 border border-slate-200">
                                <img :src="'transfer/' + validationData.paymentProof" class="w-full h-48 object-cover rounded-lg cursor-pointer hover:opacity-90 transition-opacity" @click="window.open('transfer/' + validationData.paymentProof, '_blank')">
                            </div>
                            <a :href="'transfer/' + validationData.paymentProof" target="_blank" class="text-xs font-bold text-blue-600 hover:underline"><i class="bi bi-box-arrow-up-right mr-1"></i> Lihat Ukuran Penuh</a>
                        </div>
                        <div v-else-if="(validationData.paymentMethod === 'Transfer' || validationData.paymentMethod === 'DP') && !validationData.paymentProof" class="mb-6 p-4 bg-red-50 text-red-600 rounded-xl text-xs font-bold border border-red-100">
                            <i class="bi bi-exclamation-circle mr-1"></i> Bukti Transfer Belum Diupload
                        </div>

                        <div class="flex gap-3">
                            <button @click="isProofModalVisible=false" class="flex-1 py-3 rounded-xl bg-gray-100 font-bold text-gray-600 hover:bg-gray-200 transition-colors">Tutup</button>
                            <button @click="confirmValidation(validationData)" class="flex-1 py-3 rounded-xl bg-green-600 text-white font-bold hover:bg-green-700 shadow-lg shadow-green-200 transition-transform active:scale-95 flex items-center justify-center gap-2"><i class="bi bi-check-lg"></i> Validasi Lunas</button>
                        </div>
                    </div>
                </div>
                <div v-if="isTripControlVisible" class="fixed inset-0 bg-black/80 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
                    <div class="bg-white w-full max-w-sm rounded-2xl shadow-2xl p-6 animate-fade-in">
                        <div class="text-center mb-6">
                            <h3 class="text-xl font-bold text-gray-900">Kontrol Perjalanan</h3>
                            <p class="text-sm text-gray-500 mt-1">{{ activeTripControl?.routeConfig?.routeId }} • {{ activeTripControl?.fleet?.name }}</p>
                        </div>
                        <div class="space-y-3">
                            <button @click="updateTripStatus(activeTripControl, 'Tiba')" class="w-full py-3 rounded-xl bg-green-100 text-green-700 font-bold hover:bg-green-200 transition-colors flex items-center justify-center gap-2">
                                <i class="bi bi-check-circle-fill"></i> Selesai (Tiba)
                            </button>
                            <button @click="updateTripStatus(activeTripControl, 'Kendala')" class="w-full py-3 rounded-xl bg-red-100 text-red-700 font-bold hover:bg-red-200 transition-colors flex items-center justify-center gap-2">
                                <i class="bi bi-exclamation-triangle-fill"></i> Accident / Kendala
                            </button>
                        </div>
                        <div class="mt-6">
                            <button @click="isTripControlVisible=false" class="w-full py-3 rounded-xl bg-gray-100 font-bold text-gray-600 hover:bg-gray-200 transition-colors">Tutup</button>
                        </div>
                    </div>
                </div>

                <div v-if="isDispatchModalVisible" class="fixed inset-0 bg-black/80 z-50 flex items-center justify-center p-4 backdrop-blur-sm"><div class="bg-white w-full max-w-md rounded-2xl shadow-2xl p-6 animate-fade-in"><div class="text-center mb-6"><h3 class="text-xl font-bold text-gray-900">Dispatch Armada</h3></div><div class="space-y-4"><div><label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Armada</label><select v-model="dispatchForm.fleetId" class="w-full border-gray-200 p-3 rounded-xl bg-white outline-none font-semibold text-gray-700"><option value="" disabled>-- Pilih Mobil --</option><option v-for="f in fleet.filter(x=>x.status==='Tersedia')" :value="f.id">{{ f.name }} ({{f.plate}})</option></select></div><div><label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Supir</label><select v-model="dispatchForm.driverId" class="w-full border-gray-200 p-3 rounded-xl bg-white outline-none font-semibold text-gray-700"><option value="" disabled>-- Pilih Supir --</option><option v-for="d in drivers.filter(x=>x.status==='Standby')" :value="d.id">{{ d.name }}</option></select></div></div><div class="mt-8 flex gap-3"><button @click="isDispatchModalVisible=false" class="flex-1 bg-white border border-gray-200 hover:bg-gray-50 text-gray-600 py-3 rounded-xl font-bold">Batal</button><button @click="processDispatch" class="flex-1 bg-sr-blue hover:bg-slate-800 text-white py-3 rounded-xl font-bold shadow-lg">Proses</button></div></div></div>

            </div>
        </main>
    </div>
    <script src="app.js?v=<?= time() ?>"></script>
</body>
</html>