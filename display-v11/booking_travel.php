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
    </script>
</head>
<body class="h-screen w-screen bg-slate-50 text-slate-800 dark:bg-slate-900 dark:text-slate-100 overflow-hidden">
    <div id="app" class="flex h-full w-full">
        <?php $currentPage = 'booking_travel'; include 'components/sidebar.php'; ?>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col h-full overflow-hidden relative">
            <header class="h-16 bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between px-6 z-10">
                <h1 class="text-lg font-bold text-slate-800 dark:text-white">Booking Travel</h1>
                <div class="flex items-center gap-3">
                    <button id="toggleDarkModeBtn" class="p-2 text-slate-400 hover:text-blue-600 transition-colors"><i class="bi bi-moon-stars-fill"></i></button>
                    <button id="toggleFullscreenBtn" class="p-2 text-slate-400 hover:text-blue-600 transition-colors"><i class="bi bi-arrows-fullscreen"></i></button>
                </div>
            </header>

            <div class="flex-1 overflow-y-auto p-6 custom-scrollbar">
                <div class="max-w-7xl mx-auto flex flex-col md:flex-row gap-8 pb-20">
                    
                    <!-- Left Column: Form -->
                    <div class="w-full md:w-2/3 bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-8">
                        <h2 class="text-xl font-extrabold text-sr-blue dark:text-white mb-6">Booking Travel</h2>
                        
                        <!-- Service Type Tabs -->
                        <div class="grid grid-cols-3 gap-2 p-1.5 bg-slate-100 dark:bg-slate-700 rounded-xl mb-8">
                            <button class="service-type-btn py-3 rounded-lg text-sm font-bold transition-all bg-white dark:bg-slate-600 shadow-sm text-sr-blue dark:text-white" data-type="Travel">Travel</button>
                            <button class="service-type-btn py-3 rounded-lg text-sm font-bold transition-all text-slate-500 hover:bg-white/50 dark:hover:bg-slate-600" data-type="Carter">Carter</button>
                            <button class="service-type-btn py-3 rounded-lg text-sm font-bold transition-all text-slate-500 hover:bg-white/50 dark:hover:bg-slate-600" data-type="Dropping">Dropping</button>
                        </div>

                        <!-- Passenger Category (Only for Travel) -->
                        <div id="passengerCategorySection" class="mb-6">
                            <label class="text-[10px] font-bold text-slate-400 uppercase block mb-2">Kategori Penumpang</label>
                            <div class="flex gap-4">
                                <label class="flex items-center gap-2 cursor-pointer bg-slate-50 dark:bg-slate-700 px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-600 hover:bg-blue-50 dark:hover:bg-slate-600 transition-colors">
                                    <input type="radio" name="passengerCategory" value="Umum" checked class="w-4 h-4 text-blue-600 focus:ring-blue-500">
                                    <span class="text-sm font-bold text-slate-700 dark:text-white">Umum</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer bg-slate-50 dark:bg-slate-700 px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-600 hover:bg-blue-50 dark:hover:bg-slate-600 transition-colors">
                                    <input type="radio" name="passengerCategory" value="Pelajar" class="w-4 h-4 text-blue-600 focus:ring-blue-500">
                                    <span class="text-sm font-bold text-slate-700 dark:text-white">Mahasiswa / Pelajar</span>
                                </label>
                            </div>
                        </div>
                        
                        <!-- KTM Upload Section (Hidden by default) -->
                        <div id="ktmUploadSection" class="hidden mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800 rounded-xl">
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
                                <span id="ktmFileName" class="text-[10px] text-slate-400 italic">Belum ada file</span>
                            </div>
                            <div class="mt-2 text-[10px] text-blue-400">*Wajib untuk validasi harga pelajar</div>
                        </div>

                        <div class="space-y-5">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1.5">Nama</label>
                                    <input type="text" id="passengerName" class="w-full p-3 border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 dark:text-white rounded-xl text-sm outline-none focus:border-blue-500 transition-colors">
                                </div>
                                <div>
                                    <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1.5">WhatsApp</label>
                                    <input type="text" id="passengerPhone" class="w-full p-3 border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 dark:text-white rounded-xl text-sm outline-none focus:border-blue-500 transition-colors">
                                </div>
                            </div>

                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1.5">Rute</label>
                                <select id="routeSelect" class="w-full p-3 border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 dark:text-white rounded-xl text-sm outline-none focus:border-blue-500 transition-colors">
                                    <option value="" disabled selected>Pilih Rute</option>
                                </select>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1.5">Alamat Jemput</label>
                                    <input type="text" id="pickupAddress" placeholder="Detail lokasi jemput..." class="w-full p-3 border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 dark:text-white rounded-xl text-sm outline-none focus:border-blue-500 transition-colors">
                                </div>
                                <div>
                                    <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1.5">Alamat Antar</label>
                                    <input type="text" id="dropoffAddress" placeholder="Detail lokasi antar..." class="w-full p-3 border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 dark:text-white rounded-xl text-sm outline-none focus:border-blue-500 transition-colors">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1.5">Tanggal</label>
                                    <input type="date" id="dateInput" class="w-full p-3 border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 dark:text-white rounded-xl text-sm outline-none focus:border-blue-500 transition-colors">
                                </div>
                                <div>
                                    <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1.5">Jam</label>
                                    <select id="timeInput" class="w-full p-3 border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 dark:text-white rounded-xl text-sm outline-none focus:border-blue-500 transition-colors">
                                        <option value="08:00">Pagi (08:00)</option>
                                        <option value="10:00">Siang (10:00)</option>
                                        <option value="14:00">Sore (14:00)</option>
                                        <option value="17:00">Sore (17:00)</option>
                                        <option value="20:00">Malam (20:00)</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Additional Options -->
                            <div id="multiDropArea" class="p-4 bg-slate-50 dark:bg-slate-700/50 rounded-xl border border-slate-100 dark:border-slate-700 hidden">
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <input type="checkbox" id="multiDrop" class="w-5 h-5 text-blue-600 rounded focus:ring-blue-500">
                                    <div>
                                        <div class="text-sm font-bold text-slate-700 dark:text-slate-200">Banyak Titik Antar (+Biaya)</div>
                                        <div class="text-xs text-slate-400">Centang jika lokasi antar lebih dari satu.</div>
                                    </div>
                                </label>
                            </div>

                            <!-- Seat Selection Area -->
                            <div id="seatSelectionArea" class="mt-6 pt-6 border-t border-slate-100 dark:border-slate-700">
                                <h3 class="text-sm font-bold text-slate-700 dark:text-white mb-4">Pilih Kursi</h3>
                                <div class="bg-slate-100 dark:bg-slate-700/50 p-6 rounded-2xl flex justify-center">
                                    <div class="bg-white dark:bg-slate-800 p-5 rounded-3xl border border-slate-200 dark:border-slate-600 shadow-sm w-[220px] relative">
                                        <div class="absolute top-3 left-1/2 -translate-x-1/2 w-12 h-1 bg-slate-100 dark:bg-slate-700 rounded-full"></div>
                                        <div class="flex flex-col items-center gap-3 mt-4">
                                            <div class="flex gap-6">
                                                <button id="seat-1" onclick="toggleSeat('1')" class="w-10 h-10 rounded-xl font-bold text-sm border transition-all bg-white dark:bg-slate-700 hover:bg-blue-50 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-600 shadow-sm">1</button>
                                                <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-400 flex items-center justify-center text-[10px] font-bold border border-slate-200 dark:border-slate-600">SUPIR</div>
                                            </div>
                                            <div class="flex gap-6">
                                                <button id="seat-2" onclick="toggleSeat('2')" class="w-10 h-10 rounded-xl font-bold text-sm border transition-all bg-white dark:bg-slate-700 hover:bg-blue-50 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-600 shadow-sm">2</button>
                                                <button id="seat-3" onclick="toggleSeat('3')" class="w-10 h-10 rounded-xl font-bold text-sm border transition-all bg-white dark:bg-slate-700 hover:bg-blue-50 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-600 shadow-sm">3</button>
                                            </div>
                                            <div class="flex gap-6">
                                                <button id="seat-4" onclick="toggleSeat('4')" class="w-10 h-10 rounded-xl font-bold text-sm border transition-all bg-white dark:bg-slate-700 hover:bg-blue-50 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-600 shadow-sm">4</button>
                                                <button id="seat-5" onclick="toggleSeat('5')" class="w-10 h-10 rounded-xl font-bold text-sm border transition-all bg-white dark:bg-slate-700 hover:bg-blue-50 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-600 shadow-sm">5</button>
                                            </div>
                                            <div class="flex gap-3">
                                                <button id="seat-6" onclick="toggleSeat('6')" class="w-10 h-10 rounded-xl font-bold text-sm border transition-all bg-white dark:bg-slate-700 hover:bg-blue-50 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-600 shadow-sm">6</button>
                                                <button id="seat-7" onclick="toggleSeat('7')" class="w-10 h-10 rounded-xl font-bold text-sm border transition-all bg-white dark:bg-slate-700 hover:bg-blue-50 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-600 shadow-sm">7</button>
                                                <button id="seat-8" onclick="toggleSeat('8')" class="w-10 h-10 rounded-xl font-bold text-sm border transition-all bg-white dark:bg-slate-700 hover:bg-blue-50 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-600 shadow-sm">8</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-4 flex gap-4 justify-center">
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

                            <!-- Hidden Inputs for Logic -->
                            <div id="durationInputArea" class="hidden">
                                <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Durasi (Hari)</label>
                                <input type="number" id="durationInput" value="1" min="1" class="w-full p-3 border border-slate-200 dark:border-slate-600 rounded-xl text-sm">
                            </div>
                            <div id="seatCountInputArea" class="hidden">
                                <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Jumlah Kursi</label>
                                <input type="number" id="seatCountInput" value="1" min="1" class="w-full p-3 border border-slate-200 dark:border-slate-600 rounded-xl text-sm">
                            </div>
                            <!-- Hidden Passenger Type Select (Required by JS) -->
                            <select id="passengerTypeSelect" class="hidden"><option value="Umum" selected>Umum</option></select>
                        </div>
                    </div>

                    <!-- Right Column: Payment & Summary -->
                    <div class="w-full md:w-1/3 space-y-6">
                        <h1 id="totalPriceDisplay" class="text-4xl font-extrabold text-sr-blue dark:text-white">Rp 0</h1>
                        
                        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
                            <h3 class="text-xs font-bold text-slate-400 uppercase mb-4 tracking-wider border-b border-slate-100 dark:border-slate-700 pb-2">Pembayaran</h3>
                            
                            <!-- Payment Method Tabs -->
                            <div class="grid grid-cols-3 gap-2 mb-6">
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
                            </div>

                            <!-- Dynamic Payment Fields -->
                            <div id="paymentCash" class="space-y-4 animate-fade-in">
                                <div>
                                    <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Lokasi Pembayaran</label>
                                    <input type="text" id="paymentLoc" placeholder="Loket / Mobil" list="locationList" class="w-full p-3 border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700/50 rounded-xl text-sm outline-none focus:border-blue-500">
                                    <datalist id="locationList"></datalist>
                                </div>
                                <div>
                                    <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Diterima Oleh</label>
                                    <input type="text" id="paymentRecv" placeholder="Nama Staf" list="receiverList" class="w-full p-3 border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700/50 rounded-xl text-sm outline-none focus:border-blue-500">
                                    <datalist id="receiverList"></datalist>
                                </div>
                            </div>

                            <div id="paymentTransfer" class="space-y-4 animate-fade-in hidden">
                                <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-100 dark:border-blue-800 text-center">
                                    <div class="text-[10px] font-bold text-blue-400 uppercase mb-1">Bank Transfer</div>
                                    <div class="text-sm font-bold text-blue-700 dark:text-blue-300">BCA: 123456789 (Sutan Raya)</div>
                                </div>
                                <div>
                                    <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Upload Bukti</label>
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
                                    <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Nominal DP</label>
                                    <input type="number" id="dpAmount" placeholder="Min. 50.000" class="w-full p-3 border border-yellow-200 dark:border-yellow-700 bg-yellow-50 dark:bg-yellow-900/20 rounded-xl text-sm font-bold text-yellow-700 dark:text-yellow-400 outline-none">
                                </div>
                                <div class="text-xs text-slate-400 text-center">Sisa pembayaran akan ditagih saat keberangkatan.</div>
                            </div>
                        </div>

                        <button id="submitBookingBtn" onclick="saveBooking()" disabled class="w-full py-4 bg-sr-blue dark:bg-blue-600 hover:bg-slate-800 disabled:bg-slate-300 text-white font-bold rounded-2xl shadow-lg transition-all transform active:scale-95 text-lg">
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