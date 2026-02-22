<?php require_once 'auth_check_fe.php'; ?>
<!DOCTYPE html>
<html lang="id" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sutan Raya - Booking Travel</title>
    <link rel="icon" type="image/webp" href="../image/logo.webp">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        .dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #475569; }
        .hidden { display: none !important; }
        
        /* Custom Radio Tabs for Payment */
        .payment-radio:checked + label {
            background-color: #eff6ff;
            color: #2563eb;
            border-color: #bfdbfe;
        }
        .dark .payment-radio:checked + label {
            background-color: #1e293b;
            color: #60a5fa;
            border-color: #334155;
        }
    </style>
    <script>
        tailwind.config = { darkMode: 'class', theme: { extend: { colors: { 'sr-blue': '#0f172a', 'sr-gold': '#d4af37' } } } }
        window.currentUserName = "<?= isset($_SESSION['user']['name']) ? $_SESSION['user']['name'] : '' ?>";
    </script>
</head>
<body class="h-screen w-screen bg-slate-50 text-slate-800 dark:bg-slate-900 dark:text-slate-100 overflow-hidden">
    <div id="app" class="flex h-full w-full">
        <?php $currentPage = 'booking_travel'; include 'components/sidebar.php'; ?>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col h-full overflow-hidden relative">
            <header class="h-16 bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between px-4 md:px-6 z-10">
                <div class="flex items-center gap-3">
                    <button onclick="toggleSidebar()" class="md:hidden p-2 text-slate-500 hover:text-blue-600 transition-colors rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700">
                        <i class="bi bi-list text-2xl"></i>
                    </button>
                    <h1 class="text-lg font-bold text-slate-800 dark:text-white">Booking Travel</h1>
                </div>
                <div class="flex items-center gap-3">
                    <button id="toggleDarkModeBtn" class="p-2 text-slate-400 hover:text-blue-600 transition-colors"><i class="bi bi-moon-stars-fill"></i></button>
                    <button id="toggleFullscreenBtn" class="p-2 text-slate-400 hover:text-blue-600 transition-colors"><i class="bi bi-arrows-fullscreen"></i></button>
                </div>
            </header>

            <div class="flex-1 overflow-y-auto p-4 md:p-6 custom-scrollbar">
                <div class="max-w-7xl mx-auto flex flex-col md:flex-row gap-4 md:gap-8 pb-20">
                    
                    <!-- Left Column: Form -->
                    <div class="w-full md:w-2/3 space-y-6">
                        <div class="flex items-center justify-between px-1">
                            <h2 class="text-xl md:text-2xl font-extrabold text-slate-800 dark:text-white">Booking Travel</h2>
                            <!-- Mobile-only tools could go here if needed -->
                        </div>

                        <!-- SECTION 0: LAYANAN & KATEGORI -->
                        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
                            <h3 class="flex items-center gap-3 text-sm font-bold text-slate-700 dark:text-white mb-6 border-b border-slate-100 dark:border-slate-700 pb-4">
                                <span class="w-8 h-8 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center"><i class="bi bi-grid-fill"></i></span>
                                Jenis Layanan
                            </h3>

                            <!-- Service Type Tabs -->
                            <div id="serviceTypeTabs" class="grid grid-cols-3 gap-2 p-1.5 bg-slate-100 dark:bg-slate-700 rounded-xl mb-6">
                                <button class="service-type-btn py-3 rounded-lg text-sm font-bold transition-all bg-white dark:bg-slate-600 shadow-sm text-sr-blue dark:text-white" data-type="Travel">Travel</button>
                                <button class="service-type-btn py-3 rounded-lg text-sm font-bold transition-all text-slate-500 hover:bg-white/50 dark:hover:bg-slate-600" data-type="Carter">Carter</button>
                                <button class="service-type-btn py-3 rounded-lg text-sm font-bold transition-all text-slate-500 hover:bg-white/50 dark:hover:bg-slate-600" data-type="Dropping">Dropping</button>
                            </div>

                            <!-- Passenger Category (Only for Travel) -->
                            <div id="passengerCategorySection" class="mb-6">
                                <label class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase block mb-2">Kategori Penumpang</label>
                                <div class="flex flex-wrap gap-3">
                                    <label class="flex-1 min-w-[140px] flex items-center justify-center gap-2 cursor-pointer bg-slate-50 dark:bg-slate-700/50 px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-600 hover:bg-blue-50 dark:hover:bg-slate-600/50 transition-colors group has-[:checked]:bg-blue-50 has-[:checked]:border-blue-200 has-[:checked]:text-blue-700">
                                        <input type="radio" name="passengerCategory" value="Umum" checked class="hidden peer">
                                        <i class="bi bi-person text-slate-400 group-has-[:checked]:text-blue-500 text-lg"></i>
                                        <span class="text-sm font-bold text-slate-600 dark:text-slate-300 group-has-[:checked]:text-blue-700 dark:group-has-[:checked]:text-blue-400">Umum</span>
                                    </label>
                                    <label class="flex-1 min-w-[140px] flex items-center justify-center gap-2 cursor-pointer bg-slate-50 dark:bg-slate-700/50 px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-600 hover:bg-blue-50 dark:hover:bg-slate-600/50 transition-colors group has-[:checked]:bg-blue-50 has-[:checked]:border-blue-200 has-[:checked]:text-blue-700">
                                        <input type="radio" name="passengerCategory" value="Pelajar" class="hidden peer">
                                        <i class="bi bi- mortarboard text-slate-400 group-has-[:checked]:text-blue-500 text-lg"></i>
                                        <span class="text-sm font-bold text-slate-600 dark:text-slate-300 group-has-[:checked]:text-blue-700 dark:group-has-[:checked]:text-blue-400">Mahasiswa / Pelajar</span>
                                    </label>
                                </div>
                            </div>
    
                            <!-- Custom Route Toggle (Hidden by default, shown only for Carter) -->
                            <div id="customRouteContainer" class="hidden mb-6 p-4 bg-indigo-50 dark:bg-indigo-900/10 rounded-xl border border-indigo-100 dark:border-indigo-800/30">
                                <label class="flex items-center justify-between cursor-pointer">
                                    <div>
                                        <div class="font-bold text-indigo-700 dark:text-indigo-300 text-sm">Rute Custom</div>
                                        <div class="text-xs text-indigo-500 dark:text-indigo-400 mt-0.5">Aktifkan untuk rute manual diluar jadwal reguler</div>
                                    </div>
                                    <div class="relative">
                                        <input type="checkbox" id="customRouteToggle" class="sr-only peer">
                                        <div class="w-10 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                                    </div>
                                </label>
    
                                <!-- Custom Route Inputs (Hidden by default) -->
                                <div id="customRouteSection" class="hidden mt-4 pt-4 border-t border-indigo-100 dark:border-indigo-800 space-y-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="text-xs font-bold text-indigo-500 uppercase block mb-1">Titik Awal (Sumbar)</label>
                                            <select id="customOrigin" class="w-full p-2.5 border border-indigo-200 dark:border-indigo-700 bg-white dark:bg-slate-800 rounded-lg text-sm outline-none focus:border-indigo-500">
                                                <option value="" disabled selected>Pilih Kota/Kabupaten</option>
                                                <option value="Kota Padang">Kota Padang</option>
                                                <option value="Kota Bukittinggi">Kota Bukittinggi</option>
                                                <option value="Kota Payakumbuh">Kota Payakumbuh</option>
                                                <option value="Kota Solok">Kota Solok</option>
                                                <option value="Kota Sawahlunto">Kota Sawahlunto</option>
                                                <option value="Kota Padang Panjang">Kota Padang Panjang</option>
                                                <option value="Kota Pariaman">Kota Pariaman</option>
                                                <option value="Kab. Agam">Kab. Agam</option>
                                                <option value="Kab. Dharmasraya">Kab. Dharmasraya</option>
                                                <option value="Kab. Kepulauan Mentawai">Kab. Kepulauan Mentawai</option>
                                                <option value="Kab. Lima Puluh Kota">Kab. Lima Puluh Kota</option>
                                                <option value="Kab. Padang Pariaman">Kab. Padang Pariaman</option>
                                                <option value="Kab. Pasaman">Kab. Pasaman</option>
                                                <option value="Kab. Pasaman Barat">Kab. Pasaman Barat</option>
                                                <option value="Kab. Pesisir Selatan">Kab. Pesisir Selatan</option>
                                                <option value="Kab. Sijunjung">Kab. Sijunjung</option>
                                                <option value="Kab. Solok">Kab. Solok</option>
                                                <option value="Kab. Solok Selatan">Kab. Solok Selatan</option>
                                                <option value="Kab. Tanah Datar">Kab. Tanah Datar</option>
                                                <option value="manual" class="font-bold text-indigo-600">Lainnya (Input Manual)</option>
                                            </select>
                                            <input type="text" id="customOriginManual" placeholder="Masukkan Lokasi Asal" class="hidden w-full mt-2 p-2.5 border border-indigo-200 dark:border-indigo-700 bg-white dark:bg-slate-800 rounded-lg text-sm outline-none focus:border-indigo-500">
                                        </div>
                                        <div>
                                            <label class="text-xs font-bold text-indigo-500 uppercase block mb-1">Titik Akhir</label>
                                            <input type="text" id="customDestination" placeholder="Contoh: Riau, Jambi..." class="w-full p-2.5 border border-indigo-200 dark:border-indigo-700 bg-white dark:bg-slate-800 rounded-lg text-sm outline-none focus:border-indigo-500">
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="text-xs font-bold text-indigo-500 uppercase block mb-1">Harga / Hari</label>
                                            <input type="number" id="customPrice" placeholder="Rp" class="w-full p-2.5 border border-indigo-200 dark:border-indigo-700 bg-white dark:bg-slate-800 rounded-lg text-sm outline-none focus:border-indigo-500 font-mono">
                                        </div>
                                        <div>
                                            <label class="text-xs font-bold text-indigo-500 uppercase block mb-1">Durasi (Hari)</label>
                                            <input type="number" id="customDuration" value="1" min="1" class="w-full p-2.5 border border-indigo-200 dark:border-indigo-700 bg-white dark:bg-slate-800 rounded-lg text-sm outline-none focus:border-indigo-500 font-mono text-center">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- KTM Upload Section (Hidden by default) -->
                            <div id="ktmUploadSection" class="hidden p-4 bg-blue-50 dark:bg-blue-900/10 border border-blue-100 dark:border-blue-800/30 rounded-xl">
                                <label class="block text-xs font-bold text-blue-600 dark:text-blue-400 uppercase mb-2">Upload Foto KTM / Kartu Pelajar</label>
                                <div class="flex items-center gap-4">
                                    <div id="ktmPreviewContainer" class="hidden w-20 h-14 bg-slate-200 rounded-lg overflow-hidden relative group">
                                        <img id="ktmPreview" src="" class="w-full h-full object-cover">
                                        <button onclick="removeKtm()" class="absolute inset-0 bg-black/50 text-white opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity"><i class="bi bi-x-lg"></i></button>
                                    </div>
                                    <label for="ktmInput" id="ktmUploadLabel" class="cursor-pointer px-4 py-2 bg-white dark:bg-slate-700 border border-blue-200 dark:border-blue-700 rounded-lg text-xs font-bold text-blue-600 dark:text-blue-300 hover:bg-blue-50 dark:hover:bg-blue-900/50 transition-colors">
                                        <i class="bi bi-camera-fill mr-1"></i> Pilih Foto
                                    </label>
                                    <input type="file" id="ktmInput" accept="image/*" class="hidden" onchange="handleKtmUpload(event)">
                                    <span id="ktmFileName" class="text-xs text-slate-400 italic">Belum ada file</span>
                                </div>
                                <div class="mt-2 text-xs text-blue-400">*Wajib untuk validasi harga pelajar</div>
                            </div>
                        </div>

                        <!-- SECTION 1: DATA PENUMPANG -->
                        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 mb-6">
                            <h3 class="flex items-center gap-3 text-sm font-bold text-slate-700 dark:text-white mb-6 border-b border-slate-100 dark:border-slate-700 pb-4">
                                <span class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center"><i class="bi bi-person-fill"></i></span>
                                Data Penumpang
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <label class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase block mb-1.5">Nama Lengkap</label>
                                    <input type="text" id="passengerName" class="w-full p-3 border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700/50 dark:text-white rounded-xl text-base md:text-sm outline-none focus:border-blue-500 focus:bg-white dark:focus:bg-slate-700 transition-colors placeholder:text-slate-400" placeholder="Nama penumpang...">
                                </div>
                                <div>
                                    <label class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase block mb-1.5">No. WhatsApp</label>
                                    <input type="text" id="passengerPhone" class="w-full p-3 border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700/50 dark:text-white rounded-xl text-base md:text-sm outline-none focus:border-blue-500 focus:bg-white dark:focus:bg-slate-700 transition-colors placeholder:text-slate-400" placeholder="08...">
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 2: DETAIL PERJALANAN -->
                        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 mb-6">
                            <h3 class="flex items-center gap-3 text-sm font-bold text-slate-700 dark:text-white mb-6 border-b border-slate-100 dark:border-slate-700 pb-4">
                                <span class="w-8 h-8 rounded-full bg-orange-50 text-orange-600 flex items-center justify-center"><i class="bi bi-map-fill"></i></span>
                                Detail Perjalanan
                            </h3>
                            <div class="space-y-5">
                                <div>
                                    <label class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase block mb-1.5">Rute Perjalanan</label>
                                    <select id="routeSelect" class="w-full p-3 border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700/50 dark:text-white rounded-xl text-base md:text-sm outline-none focus:border-blue-500 focus:bg-white dark:focus:bg-slate-700 transition-colors">
                                        <option value="" disabled selected>Pilih Rute</option>
                                    </select>
    
                                    <!-- Tumpangkan / Inter-Route Feature -->
                                    <div id="interRouteSection" class="mt-4 hidden p-3 bg-orange-50 dark:bg-orange-900/10 rounded-xl border border-orange-100 dark:border-orange-800/30">
                                        <label class="flex items-center gap-2 cursor-pointer mb-2 select-none">
                                            <input type="checkbox" id="interRouteToggle" class="w-4 h-4 text-orange-500 focus:ring-orange-400 rounded border-slate-300">
                                            <span class="text-xs font-bold text-orange-600 dark:text-orange-400 flex items-center gap-1">
                                                <i class="bi bi-shuffle"></i> Tumpangkan (Pindah Armada)
                                            </span>
                                        </label>
                                        <div id="physicalRouteContainer" class="hidden pl-4 border-l-2 border-orange-200 dark:border-orange-800 animate-fade-in-down">
                                            <label class="text-xs font-bold text-slate-400 uppercase block mb-1">Pilih Armada Fisik</label>
                                            <select id="physicalRouteSelect" class="w-full p-2.5 border border-orange-200 dark:border-orange-900 bg-white dark:bg-slate-800 dark:text-white rounded-lg text-base md:text-sm outline-none focus:border-orange-500 transition-colors">
                                                <option value="" disabled selected>Pilih Armada Tujuan...</option>
                                            </select>
                                            <div class="mt-1 flex items-start gap-1.5">
                                                <i class="bi bi-info-circle text-xs text-orange-500 mt-0.5"></i>
                                                <p class="text-xs text-slate-500 leading-tight">
                                                    Penumpang akan masuk ke manifest armada lain, tapi <b>harga tetap mengikuti rute awal</b>.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                    <div>
                                        <label class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase block mb-1.5">Tanggal</label>
                                        <input type="date" id="dateInput" class="w-full p-3 border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700/50 dark:text-white rounded-xl text-base md:text-sm outline-none focus:border-blue-500 focus:bg-white dark:focus:bg-slate-700 transition-colors">
                                    </div>
                                    <div>
                                        <label class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase block mb-1.5">Jam</label>
                                        <select id="timeInput" class="w-full p-3 border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700/50 dark:text-white rounded-xl text-base md:text-sm outline-none focus:border-blue-500 focus:bg-white dark:focus:bg-slate-700 transition-colors">
                                            <option value="08:00">Pagi (08:00)</option>
                                            <option value="10:00">Siang (10:00)</option>
                                            <option value="14:00">Sore (14:00)</option>
                                            <option value="17:00">Sore (17:00)</option>
                                            <option value="20:00">Malam (20:00)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 3: LOKASI ANTAR JEMPUT -->
                        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 mb-6">
                            <h3 class="flex items-center gap-3 text-sm font-bold text-slate-700 dark:text-white mb-6 border-b border-slate-100 dark:border-slate-700 pb-4">
                                <span class="w-8 h-8 rounded-full bg-green-50 text-green-600 flex items-center justify-center"><i class="bi bi-geo-alt-fill"></i></span>
                                Lokasi Antar Jemput
                            </h3>
                            <div class="space-y-6">
                                <!-- Jemput -->
                                <div class="relative pl-6 border-l-2 border-slate-100 dark:border-slate-700">
                                    <div class="absolute -left-[9px] top-0 w-4 h-4 rounded-full bg-blue-500 border-4 border-white dark:border-slate-800 shadow-sm"></div>
                                    <label class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase block mb-2">Alamat Jemput</label>
                                    <input type="text" id="pickupAddress" placeholder="Detail lokasi jemput..." class="w-full p-3 border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700/50 dark:text-white rounded-xl text-base md:text-sm outline-none focus:border-blue-500 focus:bg-white dark:focus:bg-slate-700 transition-colors mb-2">
                                    <input type="text" id="pickupMapLink" placeholder="Link Google Maps (Opsional)" class="w-full p-3 border border-dashed border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-700/50 rounded-xl text-xs outline-none focus:border-blue-500 transition-colors">
                                </div>

                                <!-- Antar -->
                                <div class="relative pl-6 border-l-2 border-slate-100 dark:border-slate-700">
                                    <div class="absolute -left-[9px] top-0 w-4 h-4 rounded-full bg-green-500 border-4 border-white dark:border-slate-800 shadow-sm"></div>
                                    <label class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase block mb-2">Alamat Antar</label>
                                    <input type="text" id="dropoffAddress" placeholder="Detail lokasi antar..." class="w-full p-3 border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700/50 dark:text-white rounded-xl text-base md:text-sm outline-none focus:border-blue-500 focus:bg-white dark:focus:bg-slate-700 transition-colors mb-2">
                                    <input type="text" id="dropoffMapLink" placeholder="Link Google Maps (Opsional)" class="w-full p-3 border border-dashed border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-700/50 rounded-xl text-xs outline-none focus:border-blue-500 transition-colors">
                                    
                                    <!-- Multi Drop Option -->
                                    <div id="multiDropArea" class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-700 hidden">
                                        <label class="flex items-center gap-3 cursor-pointer p-3 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                                            <input type="checkbox" id="multiDrop" class="w-5 h-5 text-blue-600 rounded focus:ring-blue-500">
                                            <div>
                                                <div class="text-sm font-bold text-slate-700 dark:text-slate-200">Banyak Titik Antar (+Biaya)</div>
                                                <div class="text-xs text-slate-400">Centang jika lokasi antar lebih dari satu.</div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 4: PILIH KURSI & ARMADA -->
                        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 mb-6">
                            <h3 class="flex items-center gap-3 text-sm font-bold text-slate-700 dark:text-white mb-6 border-b border-slate-100 dark:border-slate-700 pb-4">
                                <span class="w-8 h-8 rounded-full bg-purple-50 text-purple-600 flex items-center justify-center"><i class="bi bi-car-front-fill"></i></span>
                                Pilih Kursi & Armada
                            </h3>
                            
                            <!-- Batch Selector (Moved Above Seat Map) -->
                            <div id="batchSelectorArea" class="mb-6 hidden">
                                <div class="flex items-center justify-between mb-3">
                                    <label class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase">Pilih Armada</label>
                                    <span class="text-xs text-slate-400 bg-slate-100 dark:bg-slate-700 px-2 py-1 rounded">Armada Tersedia</span>
                                </div>
                                <div id="batchButtons" class="flex flex-wrap gap-2"></div>
                            </div>

                            <!-- Seat Selection Area -->
                            <div id="seatSelectionArea" class="bg-slate-50 dark:bg-slate-900/50 p-6 rounded-2xl flex flex-col items-center border border-slate-200 dark:border-slate-700/50">
                                <div class="bg-white dark:bg-slate-800 p-5 rounded-3xl border border-slate-200 dark:border-slate-600 shadow-sm w-[240px] relative">
                                    <!-- Steering Wheel Indicator -->
                                    <div class="absolute top-4 right-4 text-slate-300 dark:text-slate-600"><i class="bi bi-steering-wheel text-xl"></i></div>
                                    
                                    <div class="flex flex-col items-center gap-3 mt-8">
                                        <!-- Row 1: CC & Driver -->
                                        <div class="flex gap-8 mb-2">
                                            <button id="seat-CC" onclick="toggleSeat('CC')" class="w-12 h-12 rounded-xl font-bold text-sm border transition-all bg-white dark:bg-slate-700 hover:bg-blue-50 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-600 shadow-sm flex items-center justify-center">CC</button>
                                            <div class="w-12 h-12 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-400 flex items-center justify-center text-[10px] font-bold border border-slate-200 dark:border-slate-600 uppercase tracking-wider">Supir</div>
                                        </div>
                                        
                                        <!-- Row 2: Seats 1 & 2 -->
                                        <div class="flex gap-8">
                                            <button id="seat-1" onclick="toggleSeat('1')" class="w-12 h-12 rounded-xl font-bold text-sm border transition-all bg-white dark:bg-slate-700 hover:bg-blue-50 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-600 shadow-sm flex items-center justify-center">1</button>
                                            <button id="seat-2" onclick="toggleSeat('2')" class="w-12 h-12 rounded-xl font-bold text-sm border transition-all bg-white dark:bg-slate-700 hover:bg-blue-50 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-600 shadow-sm flex items-center justify-center">2</button>
                                        </div>

                                        <!-- Row 3: Seats 3 & 4 -->
                                        <div class="flex gap-8">
                                            <button id="seat-3" onclick="toggleSeat('3')" class="w-12 h-12 rounded-xl font-bold text-sm border transition-all bg-white dark:bg-slate-700 hover:bg-blue-50 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-600 shadow-sm flex items-center justify-center">3</button>
                                            <button id="seat-4" onclick="toggleSeat('4')" class="w-12 h-12 rounded-xl font-bold text-sm border transition-all bg-white dark:bg-slate-700 hover:bg-blue-50 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-600 shadow-sm flex items-center justify-center">4</button>
                                        </div>

                                        <!-- Row 4: Seats 5, 6, 7 -->
                                        <div class="flex gap-3 mt-1">
                                            <button id="seat-5" onclick="toggleSeat('5')" class="w-12 h-12 rounded-xl font-bold text-sm border transition-all bg-white dark:bg-slate-700 hover:bg-blue-50 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-600 shadow-sm flex items-center justify-center">5</button>
                                            <button id="seat-6" onclick="toggleSeat('6')" class="w-12 h-12 rounded-xl font-bold text-sm border transition-all bg-white dark:bg-slate-700 hover:bg-blue-50 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-600 shadow-sm flex items-center justify-center">6</button>
                                            <button id="seat-7" onclick="toggleSeat('7')" class="w-12 h-12 rounded-xl font-bold text-sm border transition-all bg-white dark:bg-slate-700 hover:bg-blue-50 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-600 shadow-sm flex items-center justify-center">7</button>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Hidden Seat Option Radios -->
                                <div class="mt-4 flex gap-4 justify-center hidden">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" name="seatOption" value="same" checked onchange="handleSeatOptionChange()" class="w-4 h-4 text-blue-600">
                                        <span class="text-xs font-bold text-slate-600 dark:text-slate-400">Mobil Sama</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" name="seatOption" value="new" onchange="handleSeatOptionChange()" class="w-4 h-4 text-blue-600">
                                        <span class="text-xs font-bold text-slate-600 dark:text-slate-400">Mobil Baru</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Hidden Inputs for Logic -->
                        <div id="durationInputArea" class="hidden">
                            <label class="text-xs font-bold text-slate-400 uppercase block mb-1">Durasi (Hari)</label>
                            <input type="number" id="durationInput" value="1" min="1" class="w-full p-3 border border-slate-200 dark:border-slate-600 rounded-xl text-base md:text-sm">
                        </div>
                        <div id="seatCountInputArea" class="hidden">
                            <label class="text-xs font-bold text-slate-400 uppercase block mb-1">Jumlah Kursi</label>
                            <input type="number" id="seatCountInput" value="1" min="1" class="w-full p-3 border border-slate-200 dark:border-slate-600 rounded-xl text-base md:text-sm">
                        </div>
                        <!-- Hidden Passenger Type Select (Required by JS) -->
                        <select id="passengerTypeSelect" class="hidden"><option value="Umum" selected>Umum</option></select>

                    </div>

                    <!-- Right Column: Payment & Summary -->
                    <div class="w-full md:w-1/3 space-y-6">
                        <h1 id="totalPriceDisplay" class="text-3xl md:text-4xl font-extrabold text-sr-blue dark:text-white">Rp 0</h1>
                        
                        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
                            <h3 class="text-xs font-bold text-slate-400 uppercase mb-4 tracking-wider border-b border-slate-100 dark:border-slate-700 pb-2">Pembayaran</h3>
                            
                            <!-- Payment Method Tabs -->
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-2 mb-6">
                                <div>
                                    <input type="radio" name="paymentMethod" id="pm_cash" value="Cash" class="hidden payment-radio" checked>
                                    <label for="pm_cash" class="block text-center py-2 rounded-lg border border-slate-200 dark:border-slate-600 text-xs font-bold text-slate-500 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-700 transition-all">Cash</label>
                                </div>
                                <div>
                                    <input type="radio" name="paymentMethod" id="pm_transfer" value="Transfer" class="hidden payment-radio">
                                    <label for="pm_transfer" class="block text-center py-2 rounded-lg border border-slate-200 dark:border-slate-600 text-xs font-bold text-slate-500 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-700 transition-all">Transfer</label>
                                </div>
                                <div>
                                    <input type="radio" name="paymentMethod" id="pm_dp" value="DP" class="hidden payment-radio">
                                    <label for="pm_dp" class="block text-center py-2 rounded-lg border border-slate-200 dark:border-slate-600 text-xs font-bold text-slate-500 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-700 transition-all">DP</label>
                                </div>
                                <div>
                                    <input type="radio" name="paymentMethod" id="pm_later" value="Belum Bayar" class="hidden payment-radio">
                                    <label for="pm_later" class="block text-center py-2 rounded-lg border border-slate-200 dark:border-slate-600 text-xs font-bold text-slate-500 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-700 transition-all">Bayar Nanti</label>
                                </div>
                            </div>

                            <!-- Dynamic Payment Fields -->
                            <div id="paymentCash" class="space-y-4 animate-fade-in">
                                <div>
                                    <label class="text-xs font-bold text-slate-400 uppercase block mb-1">Tanggal Uang Diterima</label>
                                    <input type="date" id="paymentReceivedDate" class="w-full p-3 border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700/50 rounded-xl text-base md:text-sm outline-none focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="text-xs font-bold text-slate-400 uppercase block mb-1">Lokasi Pembayaran</label>
                                    <input type="text" id="paymentLoc" placeholder="Loket / Mobil" list="locationList" class="w-full p-3 border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700/50 rounded-xl text-base md:text-sm outline-none focus:border-blue-500">
                                    <datalist id="locationList"></datalist>
                                </div>
                                <div>
                                    <label class="text-xs font-bold text-slate-400 uppercase block mb-1">Diterima Oleh</label>
                                    <input type="text" id="paymentRecv" placeholder="Nama Staf" list="receiverList" class="w-full p-3 border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700/50 rounded-xl text-base md:text-sm outline-none focus:border-blue-500">
                                    <datalist id="receiverList"></datalist>
                                </div>
                            </div>

                            <div id="paymentTransfer" class="space-y-4 animate-fade-in hidden">
                                <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-100 dark:border-blue-800 text-center">
                                    <div class="text-xs font-bold text-blue-400 uppercase mb-1">Bank Transfer</div>
                                    <div id="bankAccountDisplay" class="text-sm font-bold text-blue-700 dark:text-blue-300">Pilih Rekening Tujuan</div>
                                </div>
                                <div>
                                    <label class="text-xs font-bold text-slate-400 uppercase block mb-1">Tanggal Pengiriman Transfer</label>
                                    <input type="date" id="transferSentDate" class="w-full p-3 border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700/50 rounded-xl text-base md:text-sm outline-none focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="text-xs font-bold text-slate-400 uppercase block mb-1">Rekening Tujuan</label>
                                    <select id="destinationAccount" onchange="updateBankAccountDisplay(this.value)" class="w-full p-3 border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700/50 rounded-xl text-base md:text-sm outline-none focus:border-blue-500">
                                        <option value="" disabled selected>Pilih Rekening Tujuan</option>
                                        <option value="BCA Padang">BCA Padang</option>
                                        <option value="BCA Bukittinggi">BCA Bukittinggi</option>
                                        <option value="BCA Payakumbuh">BCA Payakumbuh</option>
                                        <option value="BCA PT">BCA PT (Sutan Raya)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="text-xs font-bold text-slate-400 uppercase block mb-1">Upload Bukti</label>
                                    <label id="uploadProofLabel" class="block w-full text-center border-2 border-dashed border-slate-300 dark:border-slate-600 rounded-xl p-6 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                                        <span id="proofLabel" class="text-slate-400 text-xs font-bold"><i class="bi bi-cloud-upload text-2xl block mb-2"></i>Upload Bukti Transfer</span>
                                        <input type="file" id="paymentProofInput" accept="image/*" class="hidden">
                                    </label>
                                    <div id="proofPreviewContainer" class="hidden relative mt-2">
                                        <img id="proofPreview" class="w-full rounded-lg border border-slate-200 dark:border-slate-600">
                                        <button id="removeProofBtn" class="absolute top-2 right-2 bg-red-500 text-white w-6 h-6 rounded-full flex items-center justify-center shadow-md hover:bg-red-600"><i class="bi bi-x"></i></button>
                                    </div>
                                </div>
                            </div>

                            <div id="paymentDP" class="space-y-4 animate-fade-in hidden">
                                <div>
                                    <label class="text-xs font-bold text-slate-400 uppercase block mb-1">Nominal DP</label>
                                    <input type="number" id="dpAmount" placeholder="Min. 50.000" class="w-full p-3 border border-yellow-200 dark:border-yellow-700 bg-yellow-50 dark:bg-yellow-900/20 rounded-xl text-base md:text-sm font-bold text-yellow-700 dark:text-yellow-400 outline-none">
                                </div>

                                <!-- DP Payment Method Sub-selection -->
                                <div>
                                    <label class="text-xs font-bold text-slate-400 uppercase block mb-2">Metode Pembayaran DP</label>
                                    <div class="grid grid-cols-2 gap-3 mb-3">
                                        <label class="cursor-pointer">
                                            <input type="radio" name="dpMethod" value="Cash" class="peer hidden" checked>
                                            <div class="py-2 px-3 text-xs font-bold text-center rounded-lg border border-slate-200 dark:border-slate-600 text-slate-500 peer-checked:bg-yellow-100 peer-checked:text-yellow-700 peer-checked:border-yellow-300 transition-all">
                                                <i class="bi bi-cash mr-1"></i> Cash
                                            </div>
                                        </label>
                                        <label class="cursor-pointer">
                                            <input type="radio" name="dpMethod" value="Transfer" class="peer hidden">
                                            <div class="py-2 px-3 text-xs font-bold text-center rounded-lg border border-slate-200 dark:border-slate-600 text-slate-500 peer-checked:bg-blue-100 peer-checked:text-blue-700 peer-checked:border-blue-300 transition-all">
                                                <i class="bi bi-bank mr-1"></i> Transfer
                                            </div>
                                        </label>
                                    </div>

                                    <!-- DP Cash Details -->
                                    <div id="dpCashDetails" class="space-y-3">
                                        <div>
                                            <label class="text-xs font-bold text-slate-400 uppercase block mb-1">Tanggal Uang Diterima</label>
                                            <input type="date" id="dpReceivedDate" class="w-full p-3 border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700/50 rounded-xl text-base md:text-sm outline-none focus:border-blue-500">
                                        </div>
                                        <div>
                                            <label class="text-xs font-bold text-slate-400 uppercase block mb-1">Lokasi Terima DP</label>
                                            <input type="text" id="dpLocation" placeholder="Loket / Agency" list="locationList" class="w-full p-3 border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700/50 rounded-xl text-base md:text-sm outline-none focus:border-yellow-500">
                                        </div>
                                        <div>
                                            <label class="text-xs font-bold text-slate-400 uppercase block mb-1">Diterima Oleh</label>
                                            <input type="text" id="dpReceiver" placeholder="Nama Staf" list="receiverList" class="w-full p-3 border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700/50 rounded-xl text-base md:text-sm outline-none focus:border-yellow-500">
                                        </div>
                                    </div>

                                    <!-- DP Transfer Details -->
                                    <div id="dpTransferDetails" class="space-y-3 hidden">
                                        <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-100 dark:border-blue-800 text-center">
                                            <div class="text-xs font-bold text-blue-400 uppercase mb-1">Bank Transfer</div>
                                            <div id="dpBankAccountDisplay" class="text-sm font-bold text-blue-700 dark:text-blue-300">Pilih Rekening Tujuan</div>
                                        </div>
                                        <div>
                                            <label class="text-xs font-bold text-slate-400 uppercase block mb-1">Tanggal Pengiriman Transfer</label>
                                            <input type="date" id="dpTransferSentDate" class="w-full p-3 border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700/50 rounded-xl text-base md:text-sm outline-none focus:border-blue-500">
                                        </div>
                                        <div>
                                            <label class="text-xs font-bold text-slate-400 uppercase block mb-1">Rekening Tujuan</label>
                                            <select id="dpDestinationAccount" onchange="updateDpBankAccountDisplay(this.value)" class="w-full p-3 border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700/50 rounded-xl text-base md:text-sm outline-none focus:border-blue-500">
                                                <option value="" disabled selected>Pilih Rekening Tujuan</option>
                                                <option value="BCA Padang">Padang</option>
                                                <option value="BCA Bukittinggi">Bukittinggi</option>
                                                <option value="BCA Payakumbuh">Payakumbuh</option>
                                                <option value="BCA PT">PT (Sutan Raya)</option>
                                            </select>
                                        </div>
                                        <label class="block w-full text-center border-2 border-dashed border-slate-300 dark:border-slate-600 rounded-xl p-4 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                                            <span id="dpProofLabel" class="text-slate-400 text-xs font-bold"><i class="bi bi-cloud-upload text-xl block mb-1"></i>Upload Bukti DP</span>
                                            <input type="file" id="dpProofInput" accept="image/*" class="hidden">
                                        </label>
                                        <div id="dpProofPreviewContainer" class="hidden relative mt-2">
                                            <img id="dpProofPreview" class="w-full rounded-lg border border-slate-200 dark:border-slate-600 h-32 object-cover">
                                            <button id="removeDpProofBtn" class="absolute top-2 right-2 bg-red-500 text-white w-6 h-6 rounded-full flex items-center justify-center shadow-md hover:bg-red-600"><i class="bi bi-x"></i></button>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-xs text-slate-400 text-center pt-2 border-t border-dashed border-slate-200">Sisa pembayaran akan ditagih saat keberangkatan.</div>
                            </div>


                            <!-- Note Field (Inside Payment Card) -->
                            <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-700 animate-fade-in">
                                <label class="text-xs font-bold text-slate-400 uppercase block mb-1">Catatan Tambahan (Opsional)</label>
                                <textarea id="bookingNote" class="w-full px-3 py-3 bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-xl text-sm outline-none focus:border-blue-500 transition-colors placeholder:text-slate-400" rows="2" placeholder="Contoh: Bawa barang banyak, jemput di gang sempit..."></textarea>
                            </div>
                        </div>

                        <button id="submitBookingBtn" onclick="saveBooking()" disabled class="w-full py-3 md:py-4 bg-sr-blue dark:bg-blue-600 hover:bg-slate-800 disabled:bg-slate-300 text-white font-bold rounded-2xl shadow-lg transition-all transform active:scale-95 text-base md:text-lg">
                            Proses Booking
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script src="js/utils.js"></script>
    <script src="js/booking_travel.js?v=<?= time() ?>"></script>
</body>
</html>