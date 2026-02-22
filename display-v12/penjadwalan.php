<?php 
require_once 'auth_check_fe.php';
require 'base.php'; 
$currentPage = 'penjadwalan';
?>
<!DOCTYPE html>
<html lang="id" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penjadwalan - Sutan Raya</title>
    <!-- Loading Optimizer -->
    <script src="js/loading-optimizer.js"></script>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            transition: background-color 0.3s, color 0.3s; 
            overflow: hidden; 
        }
        
        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        .dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #475569; }

        [v-cloak] { display: none; }
        .animate-fade-in { animation: fadeIn 0.3s ease-out forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }

        /* Draggable Styling */
        .draggable-source { cursor: grab; user-select: none; }
        .draggable-source:active { cursor: grabbing; }
        .droppable-target { transition: all 0.2s; }
        .droppable-target.drag-over { background-color: #f0f9ff; border-color: #3b82f6; transform: scale(1.01); }
        .dark .droppable-target.drag-over { background-color: #1e293b; border-color: #60a5fa; }
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
        
        <!-- Sidebar -->
        <?php include 'components/sidebar.php'; ?>

        <main class="flex-1 flex flex-col min-w-0 bg-slate-50 dark:bg-slate-900 relative h-full overflow-hidden transition-colors duration-300">
            <!-- Header -->
            <header class="h-16 bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between px-6 shadow-sm z-10 flex-shrink-0">
                <div class="flex items-center gap-3">
                    <button onclick="toggleSidebar()" class="md:hidden p-2 text-slate-500 hover:text-blue-600 transition-colors rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700">
                        <i class="bi bi-list text-2xl"></i>
                    </button>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800 dark:text-white">Penjadwalan Operasional</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Atur armada dan supir dengan mudah</p>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <!-- Route Filter -->
                    <div class="relative min-w-[200px]">
                        <select v-model="selectedRoute" class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-lg text-sm text-slate-700 dark:text-slate-200 py-2 pl-3 pr-8 focus:ring-2 focus:ring-blue-500 outline-none appearance-none cursor-pointer font-medium">
                            <option value="">Semua Rute</option>
                            <option v-for="route in getUniqueRoutes()" :key="route" :value="route">{{ route }}</option>
                        </select>
                        <i class="bi bi-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                    </div>

                    <div class="h-8 w-px bg-slate-200 dark:bg-slate-600"></div>

                    <!-- Duplicate Button -->
                    <button @click="duplicateSchedule" class="flex items-center gap-2 px-3 py-2 bg-blue-50 text-blue-600 hover:bg-blue-100 dark:bg-blue-900/30 dark:text-blue-300 dark:hover:bg-blue-900/50 rounded-lg text-sm font-bold transition-colors">
                        <i class="bi bi-copy"></i>
                        <span class="hidden sm:inline">Duplikat ke Besok</span>
                    </button>

                    <div class="h-8 w-px bg-slate-200 dark:bg-slate-600"></div>

                    <!-- Date Picker -->
                    <div class="flex items-center gap-2 bg-slate-100 dark:bg-slate-700 p-1 rounded-lg border border-slate-200 dark:border-slate-600">
                        <button @click="changeDate(-1)" class="w-8 h-8 flex items-center justify-center rounded-md hover:bg-white dark:hover:bg-slate-600 shadow-sm transition-all"><i class="bi bi-chevron-left"></i></button>
                        <input type="date" v-model="selectedDate" class="bg-transparent border-none text-sm font-bold text-slate-700 dark:text-slate-200 focus:ring-0 outline-none w-32 text-center">
                        <button @click="changeDate(1)" class="w-8 h-8 flex items-center justify-center rounded-md hover:bg-white dark:hover:bg-slate-600 shadow-sm transition-all"><i class="bi bi-chevron-right"></i></button>
                    </div>

                    <button @click="toggleDarkMode" class="w-9 h-9 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-yellow-400 hover:bg-slate-200 dark:hover:bg-slate-600 transition-all flex items-center justify-center">
                        <i :class="isDarkMode ? 'bi-sun-fill' : 'bi-moon-stars-fill'"></i>
                    </button>
                </div>
            </header>

            <!-- Content Grid -->
            <div class="flex-1 flex overflow-hidden">
                
                <!-- LEFT: Schedule Grid (75%) -->
                <div class="flex-1 overflow-y-auto p-6 custom-scrollbar space-y-8 relative">
                    
                    <div v-if="loading" class="absolute inset-0 flex items-center justify-center bg-white/50 dark:bg-slate-900/50 z-20 backdrop-blur-sm">
                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
                    </div>

                    <!-- Routes -->
                    <div v-for="route in visibleRoutes" :key="route" class="animate-fade-in">
                        <div class="flex items-center gap-3 mb-4 sticky top-0 bg-slate-50 dark:bg-slate-900 py-2 z-10">
                            <div class="h-8 w-1 bg-blue-500 rounded-full"></div>
                            <h3 class="font-bold text-lg text-slate-700 dark:text-white uppercase tracking-wide">{{ route }}</h3>
                            <div class="h-px bg-slate-200 dark:bg-slate-700 flex-1"></div>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-4">
                            <!-- Timeslots -->
                            <div v-for="(slot, idx) in getSlotsForRoute(route)" :key="idx"
                                 class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-4 shadow-sm droppable-target flex flex-col relative overflow-hidden group min-h-[160px]"
                                 :class="{'drag-over': isDraggingOver === slot.id}"
                                 @dragover.prevent="onDragOver(slot.id)" 
                                 @dragleave="onDragLeave"
                                 @drop="onDrop($event, slot)">
                                
                                <!-- Header Time -->
                                <div class="flex justify-between items-start mb-3">
                                    <div>
                                        <div class="text-2xl font-black text-slate-800 dark:text-white">{{ slot.time }}</div>
                                        <div class="text-[10px] font-bold text-slate-400 uppercase">WIB</div>
                                    </div>
                                    <div v-if="slot.tripId" class="px-2 py-1 rounded bg-green-100 text-green-700 text-[10px] font-bold border border-green-200">
                                        Active
                                    </div>
                                    <div v-else class="px-2 py-1 rounded bg-slate-100 text-slate-500 text-[10px] font-bold border border-slate-200">
                                        Empty
                                    </div>
                                </div>

                                <!-- Slots for Drop -->
                                <div class="space-y-2 mt-auto">
                                    <!-- Fleet Slot -->
                                    <div class="relative">
                                        <div v-if="slot.fleet" class="bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800 rounded-xl p-2 flex items-center gap-3 group/item">
                                            <div class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-800 text-blue-600 dark:text-blue-200 flex items-center justify-center text-lg">
                                                <i :class="slot.fleet.icon || 'bi-car-front-fill'"></i>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="font-bold text-sm text-blue-900 dark:text-blue-100 truncate">{{ slot.fleet.name }}</div>
                                                <div class="text-[10px] text-blue-600 dark:text-blue-300 font-mono">{{ slot.fleet.plate }}</div>
                                            </div>
                                            <button @click="removeResource(slot, 'fleet')" class="w-6 h-6 rounded-full bg-blue-200 text-blue-700 hover:bg-red-500 hover:text-white flex items-center justify-center transition-colors opacity-0 group-hover/item:opacity-100"><i class="bi bi-x"></i></button>
                                        </div>
                                        <div v-else class="h-12 rounded-xl border-2 border-dashed border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-400 text-xs font-bold gap-2 bg-slate-50/50 dark:bg-slate-800/50">
                                            <i class="bi bi-car-front"></i> Seret Armada Kesini
                                        </div>
                                    </div>

                                    <!-- Driver Slot -->
                                    <div class="relative">
                                        <div v-if="slot.driver" class="bg-amber-50 dark:bg-amber-900/20 border border-amber-100 dark:border-amber-800 rounded-xl p-2 flex items-center gap-3 group/item">
                                            <div class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-800 text-amber-600 dark:text-amber-200 flex items-center justify-center text-lg">
                                                <i class="bi bi-person-fill"></i>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="font-bold text-sm text-amber-900 dark:text-amber-100 truncate">{{ slot.driver.name }}</div>
                                                <div class="text-[10px] text-amber-600 dark:text-amber-300">{{ slot.driver.phone }}</div>
                                            </div>
                                            <button @click="removeResource(slot, 'driver')" class="w-6 h-6 rounded-full bg-amber-200 text-amber-700 hover:bg-red-500 hover:text-white flex items-center justify-center transition-colors opacity-0 group-hover/item:opacity-100"><i class="bi bi-x"></i></button>
                                        </div>
                                        <div v-else class="h-12 rounded-xl border-2 border-dashed border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-400 text-xs font-bold gap-2 bg-slate-50/50 dark:bg-slate-800/50">
                                            <i class="bi bi-person"></i> Seret Supir Kesini
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="h-20"></div> <!-- Spacer -->
                </div>

                <!-- RIGHT: Resources Sidebar (25% or 320px) -->
                <aside class="w-80 bg-white dark:bg-slate-800 border-l border-slate-200 dark:border-slate-700 flex flex-col shadow-xl z-20">
                    <!-- Tabs -->
                    <div class="flex border-b border-slate-200 dark:border-slate-700">
                        <button @click="activeTab = 'fleet'" class="flex-1 py-3 text-center text-sm font-bold transition-colors border-b-2"
                            :class="activeTab === 'fleet' ? 'border-blue-500 text-blue-600 bg-blue-50 dark:bg-slate-700 dark:text-blue-300' : 'border-transparent text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-700'">
                            <i class="bi bi-car-front-fill mr-2"></i> Armada
                        </button>
                        <button @click="activeTab = 'drivers'" class="flex-1 py-3 text-center text-sm font-bold transition-colors border-b-2"
                            :class="activeTab === 'drivers' ? 'border-amber-500 text-amber-600 bg-amber-50 dark:bg-slate-700 dark:text-amber-300' : 'border-transparent text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-700'">
                            <i class="bi bi-person-badge-fill mr-2"></i> Supir
                        </button>
                    </div>

                    <!-- Search -->
                    <div class="p-4 border-b border-slate-100 dark:border-slate-700">
                        <div class="relative">
                            <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <input type="text" v-model="searchQuery" placeholder="Cari..." class="w-full pl-9 pr-4 py-2 bg-slate-100 dark:bg-slate-700 rounded-lg text-sm border-none focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                    </div>

                    <!-- List Content -->
                    <div class="flex-1 overflow-y-auto p-4 custom-scrollbar space-y-3">
                        
                        <!-- FLEET LIST -->
                        <div v-if="activeTab === 'fleet'">
                            <div v-for="item in filteredFleet" :key="item.id" 
                                 draggable="true" @dragstart="onDragStart($event, item, 'fleet')"
                                 class="bg-white dark:bg-slate-700 p-3 rounded-xl border border-slate-200 dark:border-slate-600 hover:shadow-md hover:border-blue-400 transition-all cursor-move draggable-source group">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-slate-600 text-blue-600 dark:text-blue-300 flex items-center justify-center text-xl shadow-sm">
                                        <i :class="item.icon || 'bi-car-front-fill'"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="font-bold text-sm text-slate-800 dark:text-white truncate">{{ item.name }}</div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400 font-mono bg-slate-100 dark:bg-slate-600 px-1.5 py-0.5 rounded inline-block">{{ item.plate }}</div>
                                    </div>
                                    <div class="w-2 h-8 rounded-full bg-slate-200 dark:bg-slate-600 group-hover:bg-blue-400"></div>
                                </div>
                            </div>
                        </div>

                        <!-- DRIVER LIST -->
                        <div v-if="activeTab === 'drivers'">
                            <div v-for="item in filteredDrivers" :key="item.id" 
                                 draggable="true" @dragstart="onDragStart($event, item, 'driver')"
                                 class="bg-white dark:bg-slate-700 p-3 rounded-xl border border-slate-200 dark:border-slate-600 hover:shadow-md hover:border-amber-400 transition-all cursor-move draggable-source group">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-amber-100 dark:bg-slate-600 text-amber-600 dark:text-amber-300 flex items-center justify-center text-xl shadow-sm">
                                        <i class="bi bi-person-fill"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="font-bold text-sm text-slate-800 dark:text-white truncate">{{ item.name }}</div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400">{{ item.phone }}</div>
                                    </div>
                                    <div class="w-2 h-8 rounded-full bg-slate-200 dark:bg-slate-600 group-hover:bg-amber-400"></div>
                                </div>
                            </div>
                        </div>

                    </div>
                </aside>

            </div>
        </main>
    </div>

    <!-- Logic Script -->
    <script src="js/modules/penjadwalan.js?v=<?= time() ?>"></script>
</body>
</html>
