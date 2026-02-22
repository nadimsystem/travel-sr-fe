<!DOCTYPE html>
<html lang="id" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal & Penugasan - Sutan Raya</title>
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
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        .dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #475569; }
    </style>
    <script>
        tailwind.config = { darkMode: 'class', theme: { extend: { colors: { 'sr-blue': '#0f172a', 'sr-gold': '#d4af37' } } } }
        window.initialView = 'schedule';
    </script>
</head>
<body class="h-screen w-screen bg-slate-50 text-slate-800 dark:bg-slate-900 dark:text-slate-100 overflow-hidden">
    <div id="app" class="flex h-full w-full" v-cloak>
        <!-- Sidebar -->
        <?php $currentPage = 'schedule'; include 'components/sidebar.php'; ?>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col h-full overflow-hidden relative">
            <!-- Top Bar -->
            <header class="h-16 bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between px-6 z-10">
                <div class="flex items-center gap-4">
                    <h1 class="text-lg font-bold text-slate-800 dark:text-white">Jadwal & Penugasan</h1>
                    <div class="h-6 w-px bg-slate-200 dark:bg-slate-700"></div>
                    <div class="flex items-center gap-2 bg-slate-100 dark:bg-slate-700 rounded-lg p-1">
                        <input type="date" v-model="manifestDate" class="bg-transparent border-none text-sm font-bold text-slate-600 dark:text-slate-300 focus:ring-0 px-2">
                        <span class="text-xs font-bold px-3 py-1 bg-white dark:bg-slate-600 rounded text-slate-500 dark:text-slate-300 shadow-sm uppercase tracking-wider">{{ getDayName(manifestDate) }}</span>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <button @click="toggleDarkMode" class="p-2 text-slate-400 hover:text-blue-600 transition-colors"><i class="bi bi-moon-stars-fill"></i></button>
                    <div class="flex items-center gap-3 pl-4 border-l border-slate-200 dark:border-slate-700">
                        <div class="text-right">
                            <div class="text-xs font-bold text-slate-500 dark:text-slate-400">Halo, Admin</div>
                            <div class="text-[10px] text-slate-400">Administrator</div>
                        </div>
                        <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-xs">A</div>
                    </div>
                </div>
            </header>

            <!-- Scrollable Content -->
            <div class="flex-1 overflow-y-auto p-6 custom-scrollbar">
                <div class="max-w-7xl mx-auto space-y-6 pb-20">
                    
                    <!-- Schedule Grid -->
                    <div v-for="route in routeConfig" :key="route.id" class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
                        <div class="p-4 border-b border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800 flex justify-between items-center">
                            <div>
                                <h3 class="font-bold text-slate-800 dark:text-white">{{ route.origin }} - {{ route.destination }}</h3>
                                <div class="text-xs text-slate-500">{{ (route.schedules || []).length }} Jadwal</div>
                            </div>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left">
                                <thead class="text-xs text-slate-500 uppercase bg-slate-50 dark:bg-slate-700/50">
                                    <tr>
                                        <th class="px-4 py-3 font-bold w-24">Jam</th>
                                        <th class="px-4 py-3 font-bold">Armada</th>
                                        <th class="px-4 py-3 font-bold">Supir</th>
                                        <th class="px-4 py-3 font-bold text-center w-32">Status</th>
                                        <th class="px-4 py-3 font-bold text-right w-24">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                    <tr v-for="time in (route.schedules || [])" :key="time" class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                        <td class="px-4 py-3 font-bold text-slate-800 dark:text-white">{{ time }}</td>
                                        
                                        <!-- Assignment Logic -->
                                        <td class="px-4 py-3">
                                            <div v-if="getAssignment(route.id, time)" class="flex items-center gap-2">
                                                <i class="bi bi-car-front-fill text-slate-400"></i>
                                                <span class="font-bold text-slate-700 dark:text-slate-300">{{ getAssignment(route.id, time).fleet?.name || '-' }}</span>
                                                <span class="text-xs text-slate-400 bg-slate-100 dark:bg-slate-700 px-1.5 py-0.5 rounded">{{ getAssignment(route.id, time).fleet?.plate || '-' }}</span>
                                            </div>
                                            <div v-else class="text-slate-400 italic text-xs">Belum ada armada</div>
                                        </td>
                                        
                                        <td class="px-4 py-3">
                                            <div v-if="getAssignment(route.id, time)" class="flex items-center gap-2">
                                                <i class="bi bi-person-fill text-slate-400"></i>
                                                <span class="font-bold text-slate-700 dark:text-slate-300">{{ getAssignment(route.id, time).driver?.name || '-' }}</span>
                                            </div>
                                            <div v-else class="text-slate-400 italic text-xs">Belum ada supir</div>
                                        </td>
                                        
                                        <td class="px-4 py-3 text-center">
                                            <div v-if="getAssignment(route.id, time)">
                                                <span v-if="getAssignment(route.id, time).status === 'Conflict'" class="bg-red-100 text-red-700 text-[10px] font-bold px-2 py-1 rounded-full flex items-center justify-center gap-1">
                                                    <i class="bi bi-exclamation-triangle-fill"></i> Konflik
                                                </span>
                                                <span v-else :class="getTripStatusBadge(getAssignment(route.id, time).status) + ' text-white text-[10px] font-bold px-2 py-1 rounded-full'">
                                                    {{ getAssignment(route.id, time).status }}
                                                </span>
                                                <div v-if="getAssignment(route.id, time).type === 'Default'" class="text-[9px] text-slate-400 mt-1 uppercase tracking-wider">Default</div>
                                            </div>
                                            <span v-else class="bg-slate-100 dark:bg-slate-700 text-slate-400 text-[10px] font-bold px-2 py-1 rounded-full">Kosong</span>
                                        </td>
                                        
                                        <td class="px-4 py-3 text-right">
                                            <button @click="openScheduleModal(route, time, getAssignment(route.id, time))" class="text-blue-600 hover:text-blue-800 font-bold text-xs">
                                                <i class="bi bi-pencil-square"></i> Edit
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </main>

        <!-- Schedule Assignment Modal -->
        <div v-if="isScheduleModalVisible" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" @click.self="isScheduleModalVisible = false">
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl w-full max-w-md overflow-hidden animate-fade-in">
                <div class="p-4 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-slate-50 dark:bg-slate-700/50">
                    <h3 class="font-bold text-slate-800 dark:text-white">Atur Jadwal</h3>
                    <button @click="isScheduleModalVisible = false" class="p-1 hover:bg-slate-200 dark:hover:bg-slate-600 rounded-full transition-colors"><i class="bi bi-x-lg"></i></button>
                </div>
                <div class="p-6 space-y-4">
                    <div class="bg-blue-50 dark:bg-blue-900/20 p-3 rounded-xl border border-blue-100 dark:border-blue-800">
                        <div class="text-xs text-blue-500 font-bold uppercase mb-1">Rute & Jam</div>
                        <div class="font-bold text-blue-800 dark:text-blue-300">{{ scheduleForm.route?.origin }} - {{ scheduleForm.route?.destination }}</div>
                        <div class="text-sm text-blue-600 dark:text-blue-400">{{ scheduleForm.time }}</div>
                    </div>

                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase block mb-1">Armada</label>
                        <select v-model="scheduleForm.fleetId" class="w-full p-3 border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 rounded-xl text-sm outline-none focus:border-blue-500">
                            <option value="">Pilih Armada</option>
                            <option v-for="f in fleet" :key="f.id" :value="f.id">{{ f.name }} - {{ f.plate }} ({{ f.capacity }} seat)</option>
                        </select>
                    </div>

                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase block mb-1">Supir</label>
                        <select v-model="scheduleForm.driverId" class="w-full p-3 border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 rounded-xl text-sm outline-none focus:border-blue-500">
                            <option value="">Pilih Supir</option>
                            <option v-for="d in drivers" :key="d.id" :value="d.id">{{ d.name }}</option>
                        </select>
                    </div>
                    
                    <div class="flex items-center gap-2 pt-2">
                        <input type="checkbox" id="isDefault" v-model="scheduleForm.isDefault" class="w-4 h-4 text-blue-600 rounded border-slate-300 focus:ring-blue-500">
                        <label for="isDefault" class="text-sm text-slate-600 dark:text-slate-300 select-none">Simpan sebagai Jadwal Harian (Default)</label>
                    </div>
                    <div class="text-[10px] text-slate-400 ml-6">Jika dicentang, jadwal ini akan berlaku setiap hari kecuali diubah manual.</div>
                    
                    <button @click="saveScheduleAssignment" class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-lg shadow-blue-200 dark:shadow-none transition-all active:scale-95">
                        Simpan Penugasan
                    </button>
                </div>
            </div>
        </div>

    </div>
    <script src="app.js?v=<?= time() ?>"></script>
</body>
</html>
