<!DOCTYPE html>
<html lang="id" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sutan Raya - Kirim Paket</title>
    <link rel="icon" type="image/webp" href="../image/logo.webp">
    
    <!-- Loading Optimizer - Prevents visual flash & UI errors -->
    <script src="js/loading-optimizer.js"></script>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
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

        /* Poster Specific Styles */
        .poster-card {
            background: linear-gradient(145deg, #ffffff, #f8fafc);
            transition: all 0.3s ease;
        }
        .dark .poster-card {
            background: linear-gradient(145deg, #1e293b, #0f172a);
        }
        .poster-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px -10px rgba(0,0,0,0.1);
        }
        .glass-effect {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
        }
        .dark .glass-effect {
            background: rgba(15, 23, 42, 0.9);
        }
        
        .price-badge {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: white;
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 800;
            box-shadow: 0 4px 10px rgba(37, 99, 235, 0.3);
        }
    </style>
    
    <script>
        tailwind.config = { 
            darkMode: 'class',
            theme: { extend: { colors: { 'sr-blue': '#0f172a', 'sr-gold': '#d4af37' } } } 
        }
        window.initialView = 'packageShipping';
    </script>
</head>
<body class="h-screen w-screen bg-slate-50 text-slate-800 dark:bg-slate-900 dark:text-slate-100">

    <div id="app" class="flex h-full w-full" v-cloak>
        
        <?php $currentPage = 'paket'; include 'components/sidebar.php'; ?>

        <main class="flex-1 flex flex-col min-w-0 bg-slate-50 dark:bg-slate-900 relative h-full overflow-hidden transition-colors duration-300">
            <header class="h-16 bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between px-6 shadow-sm z-10 flex-shrink-0 transition-colors duration-300">
                <div>
                    <h2 class="text-lg font-bold text-slate-800 dark:text-white">Ekspedisi Sutan Raya</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Kirim Paket Murah, Cepat & Aman</p>
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

            <div class="flex-1 overflow-y-auto p-6 custom-scrollbar animate-fade-in relative">
                
                <!-- Navigation Tabs -->
                <div class="max-w-7xl mx-auto mb-8 flex justify-center">
                    <div class="bg-white dark:bg-slate-800 p-1.5 rounded-full shadow-lg shadow-slate-200/50 dark:shadow-none border border-slate-200 dark:border-slate-700 flex gap-1">
                        <button @click="packageView = 'info'" 
                            :class="packageView === 'info' ? 'bg-blue-600 text-white shadow-md' : 'text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-700'" 
                            class="px-8 py-3 rounded-full text-sm font-bold transition-all flex items-center gap-2">
                            <i class="bi bi-megaphone-fill"></i> Info & Promo
                        </button>
                        <button @click="packageView = 'booking'" 
                            :class="packageView === 'booking' ? 'bg-blue-600 text-white shadow-md' : 'text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-700'" 
                            class="px-8 py-3 rounded-full text-sm font-bold transition-all flex items-center gap-2">
                            <i class="bi bi-box-seam-fill"></i> Kirim Paket
                        </button>
                        <button @click="packageView = 'history'" 
                            :class="packageView === 'history' ? 'bg-blue-600 text-white shadow-md' : 'text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-700'" 
                            class="px-8 py-3 rounded-full text-sm font-bold transition-all flex items-center gap-2">
                            <i class="bi bi-clock-history"></i> Riwayat & Lacak
                        </button>
                    </div>
                </div>

                <!-- VIEW 1: POSTER MODE (INFO & TARIF) -->
                <!-- VIEW 1: POSTER MODE (INFO & TARIF) -->
                <div v-if="packageView === 'info'" class="max-w-5xl mx-auto space-y-12 pb-20">
                    
                    <!-- Hero Section -->
                    <div class="relative rounded-[3rem] overflow-hidden bg-gradient-to-r from-blue-900 to-indigo-800 text-white p-12 md:p-16 shadow-2xl shadow-blue-900/20 isolate">
                        <!-- Abstract Blobs -->
                        <div class="absolute top-0 right-0 w-96 h-96 bg-white/10 rounded-full blur-3xl -mr-20 -mt-20 -z-10"></div>
                        <div class="absolute bottom-0 left-0 w-72 h-72 bg-black/20 rounded-full blur-2xl -ml-20 -mb-20 -z-10"></div>
                        
                        <!-- Vehicle Images -->
                        <img src="../armada/hiace2.webp" alt="Hiace" class="absolute right-0 bottom-0 h-full w-1/2 object-contain object-right-bottom opacity-20 mix-blend-overlay -z-10">
                        <img src="../2.png" alt="Bus" class="absolute left-0 bottom-0 h-full w-1/2 object-contain object-left-bottom opacity-20 mix-blend-overlay -z-10 grayscale">

                        <div class="relative z-10 text-center">
                            <div class="inline-block px-4 py-1.5 rounded-full bg-white/20 backdrop-blur-sm border border-white/30 text-xs font-bold uppercase tracking-widest mb-6">
                                Solusi Logistik Terbaik
                            </div>
                            <h1 class="text-4xl md:text-6xl font-extrabold mb-6 leading-tight tracking-tight drop-shadow-lg">Kirim Paket? <br><span class="text-yellow-400">Sutan Raya</span> Aja!</h1>
                            <p class="text-lg md:text-xl text-blue-100 max-w-2xl mx-auto mb-10 drop-shadow-md">Layanan pengiriman paket kilat antar kota Padang, Bukittinggi, dan Payakumbuh. Aman, Cepat, dan Terpercaya.</p>
                        </div>
                    </div>

                    <!-- Services Grid -->
                    <div class="grid md:grid-cols-2 gap-8">
                        <!-- Service 1 -->
                        <div class="group p-8 rounded-[2.5rem] bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 shadow-xl shadow-slate-200/50 dark:shadow-none hover:shadow-2xl transition-all relative overflow-hidden">
                             <div class="absolute top-0 right-0 p-8 opacity-5 group-hover:opacity-10 transition-opacity transform group-hover:scale-110 duration-500">
                                 <i class="bi bi-building-fill text-9xl text-blue-600"></i>
                             </div>
                             <div class="relative z-10">
                                 <div class="w-16 h-16 rounded-2xl bg-blue-100 dark:bg-blue-900/30 text-blue-600 flex items-center justify-center text-3xl mb-6 shadow-sm">
                                     <i class="bi bi-box-seam-fill"></i>
                                 </div>
                                 <h3 class="text-2xl font-extrabold text-slate-800 dark:text-white mb-2">Pool to Pool</h3>
                                 <p class="text-slate-500 dark:text-slate-400 mb-6">Kirim barang antar kantor perwakilan (Pool). Lebih hemat dan efisien untuk pengambilan mandiri.</p>
                                 
                                 <div class="space-y-3">
                                     <div class="flex justify-between items-center p-3 rounded-xl bg-slate-50 dark:bg-slate-700/50">
                                         <span class="font-bold text-slate-600 dark:text-slate-300 text-sm">Dokumen / Surat</span>
                                         <span class="price-badge">Start from Rp 30.000</span>
                                     </div>
                                     <div class="flex justify-between items-center p-3 rounded-xl bg-slate-50 dark:bg-slate-700/50">
                                         <span class="font-bold text-slate-600 dark:text-slate-300 text-sm">Barang Kardus</span>
                                         <span class="price-badge">Start from Rp 40.000</span>
                                     </div>
                                     <div class="flex justify-between items-center p-3 rounded-xl bg-slate-50 dark:bg-slate-700/50">
                                         <span class="font-bold text-slate-600 dark:text-slate-300 text-sm">Big Size / Lainnya</span>
                                         <span class="price-badge">Start from Rp 100.000</span>
                                     </div>
                                 </div>
                             </div>
                        </div>

                        <!-- Service 2 -->
                        <div class="group p-8 rounded-[2.5rem] bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 shadow-xl shadow-slate-200/50 dark:shadow-none hover:shadow-2xl transition-all relative overflow-hidden">
                             <div class="absolute top-0 right-0 p-8 opacity-5 group-hover:opacity-10 transition-opacity transform group-hover:scale-110 duration-500">
                                 <i class="bi bi-house-heart-fill text-9xl text-orange-600"></i>
                             </div>
                             <div class="relative z-10">
                                 <div class="w-16 h-16 rounded-2xl bg-orange-100 dark:bg-orange-900/30 text-orange-600 flex items-center justify-center text-3xl mb-6 shadow-sm">
                                     <i class="bi bi-bicycle"></i>
                                 </div>
                                 <h3 class="text-2xl font-extrabold text-slate-800 dark:text-white mb-2">Antar Jemput (Door to Door)</h3>
                                 <p class="text-slate-500 dark:text-slate-400 mb-6">Layanan VIP. Tim kami jemput paket di tempat Anda dan antar langsung ke depan pintu penerima.</p>
                                 
                                 <div class="space-y-3">
                                     <div class="flex justify-between items-center p-3 rounded-xl bg-slate-50 dark:bg-slate-700/50">
                                         <span class="font-bold text-slate-600 dark:text-slate-300 text-sm">Dokumen / Surat</span>
                                         <span class="price-badge bg-gradient-to-r from-orange-500 to-red-500">Start from Rp 60.000</span>
                                     </div>
                                     <div class="flex justify-between items-center p-3 rounded-xl bg-slate-50 dark:bg-slate-700/50">
                                         <span class="font-bold text-slate-600 dark:text-slate-300 text-sm">Barang Kardus</span>
                                         <span class="price-badge bg-gradient-to-r from-orange-500 to-red-500">Start from Rp 70.000</span>
                                     </div>
                                     <div class="flex justify-between items-center p-3 rounded-xl bg-slate-50 dark:bg-slate-700/50">
                                         <span class="font-bold text-slate-600 dark:text-slate-300 text-sm">Big Size / Lainnya</span>
                                         <span class="price-badge bg-gradient-to-r from-orange-500 to-red-500">Start from Rp 120.000</span>
                                     </div>
                                 </div>
                             </div>
                        </div>
                    </div>

                    <!-- Footer Info -->
                    <div class="text-center space-y-4">
                        <div class="inline-flex items-center gap-2 p-3 bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700">
                            <i class="bi bi-whatsapp text-green-500 text-xl"></i>
                            <span class="font-bold text-slate-700 dark:text-slate-300">Customer Service:</span>
                            <span class="font-mono font-bold text-slate-900 dark:text-white">0812-3456-7890</span>
                        </div>
                        <p class="text-sm text-slate-400">© 2025 Sutan Raya Expedition. All Rights Reserved.</p>
                    </div>
                </div>

                <!-- VIEW 2: BOOKING FLOW -->
                <div v-if="packageView === 'booking'" class="max-w-5xl mx-auto space-y-6">
                    <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-xl border border-slate-200 dark:border-slate-700 overflow-hidden flex flex-col md:flex-row">
                        <!-- Form Area -->
                        <div class="flex-1 p-8 border-r border-slate-100 dark:border-slate-700">
                            <h3 class="text-xl font-extrabold text-slate-800 dark:text-white mb-6">Formulir Pengiriman</h3>
                            
                            <div class="space-y-6">
                                <!-- Step 1: Pengirim & Penerima -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="space-y-4">
                                        <h4 class="text-[10px] font-bold text-blue-600 uppercase tracking-widest bg-blue-50 dark:bg-blue-900/30 w-fit px-2 py-1 rounded">1. Pengirim</h4>
                                        <div>
                                            <label class="text-xs font-bold text-slate-500 mb-1 block">Nama Pengirim</label>
                                            <input type="text" v-model="packageForm.senderName" class="w-full p-3 border rounded-xl dark:bg-slate-700 dark:border-slate-600 text-sm font-semibold" placeholder="Nama Lengkap">
                                        </div>
                                        <div>
                                            <label class="text-xs font-bold text-slate-500 mb-1 block">No. WhatsApp</label>
                                            <input type="text" v-model="packageForm.senderPhone" class="w-full p-3 border rounded-xl dark:bg-slate-700 dark:border-slate-600 text-sm font-semibold" placeholder="08xxxx">
                                        </div>
                                    </div>
                                    <div class="space-y-4">
                                        <h4 class="text-[10px] font-bold text-orange-600 uppercase tracking-widest bg-orange-50 dark:bg-orange-900/30 w-fit px-2 py-1 rounded">2. Penerima</h4>
                                        <div>
                                            <label class="text-xs font-bold text-slate-500 mb-1 block">Nama Penerima</label>
                                            <input type="text" v-model="packageForm.receiverName" class="w-full p-3 border rounded-xl dark:bg-slate-700 dark:border-slate-600 text-sm font-semibold" placeholder="Nama Lengkap">
                                        </div>
                                        <div>
                                            <label class="text-xs font-bold text-slate-500 mb-1 block">No. WhatsApp</label>
                                            <input type="text" v-model="packageForm.receiverPhone" class="w-full p-3 border rounded-xl dark:bg-slate-700 dark:border-slate-600 text-sm font-semibold" placeholder="08xxxx">
                                        </div>
                                    </div>
                                </div>

                                <!-- Step 2: Detail Barang -->
                                <div class="pt-6 border-t border-slate-100 dark:border-slate-700">
                                    <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-4">3. Detail Paket</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label class="text-xs font-bold text-slate-500 mb-1 block">Jenis Barang</label>
                                            <select v-model="packageForm.itemType" @change="calculatePackagePrice" class="w-full p-3 border rounded-xl dark:bg-slate-700 dark:border-slate-600 text-sm font-bold">
                                                <option>Surat / Dokumen</option>
                                                <option>Barang Kardus</option>
                                                <option>Barang Lainnya / Big Size</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="text-xs font-bold text-slate-500 mb-1 block">Isi Paket (Deskripsi)</label>
                                            <input type="text" v-model="packageForm.itemDescription" class="w-full p-3 border rounded-xl dark:bg-slate-700 dark:border-slate-600 text-sm" placeholder="Contoh: Baju, Makanan Kering, Dokumen Lupa">
                                        </div>
                                    </div>
                                </div>

                                <!-- Step 3: Layanan -->
                                <div class="pt-6 border-t border-slate-100 dark:border-slate-700">
                                    <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-4">4. Rute & Layanan</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label class="text-xs font-bold text-slate-500 mb-1 block">Rute Perjalanan</label>
                                            <select v-model="packageForm.route" @change="calculatePackagePrice" class="w-full p-3 border rounded-xl dark:bg-slate-700 dark:border-slate-600 text-sm font-bold">
                                                <option>Padang - Bukittinggi</option>
                                                <option>Padang - Payakumbuh</option>
                                                <option>Bukittinggi - Payakumbuh</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="text-xs font-bold text-slate-500 mb-1 block">Jenis Layanan</label>
                                            <select v-model="packageForm.category" @change="calculatePackagePrice" class="w-full p-3 border rounded-xl dark:bg-slate-700 dark:border-slate-600 text-sm font-bold text-blue-600">
                                                <option>Pool to Pool</option>
                                                <option>Antar Jemput Alamat</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Address Fields for Door to Door -->
                                    <div v-if="packageForm.category === 'Antar Jemput Alamat'" class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4 animate-fade-in bg-slate-50 dark:bg-slate-700/30 p-4 rounded-2xl">
                                        <div class="md:col-span-2">
                                            <label class="text-xs font-bold text-slate-500 mb-1 block">Link Google Maps (Wajib)</label>
                                            <input type="text" v-model="packageForm.mapLink" class="w-full p-2.5 border rounded-xl dark:bg-slate-700 dark:border-slate-600 text-sm" placeholder="https://maps.google.com/...">
                                        </div>
                                        <div>
                                            <label class="text-xs font-bold text-slate-500 mb-1 block">Alamat Jemput</label>
                                            <textarea v-model="packageForm.pickupAddress" class="w-full p-2.5 border rounded-xl dark:bg-slate-700 dark:border-slate-600 text-sm h-20"></textarea>
                                        </div>
                                        <div>
                                            <label class="text-xs font-bold text-slate-500 mb-1 block">Alamat Antar</label>
                                            <textarea v-model="packageForm.dropoffAddress" class="w-full p-2.5 border rounded-xl dark:bg-slate-700 dark:border-slate-600 text-sm h-20"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment & Action Column -->
                        <div class="w-full md:w-96 bg-slate-50 dark:bg-slate-800/80 p-8 flex flex-col border-l border-slate-200 dark:border-slate-700">
                            <h3 class="text-lg font-bold text-slate-800 dark:text-white mb-6">Pembayaran</h3>
                            
                            <div class="flex-1 space-y-6">
                                <div class="bg-white dark:bg-slate-700 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-600 text-center">
                                    <div class="text-[10px] font-bold text-slate-400 uppercase mb-2">Total Tagihan</div>
                                    <div class="text-3xl font-extrabold text-blue-600 dark:text-blue-400">{{ formatRupiah(packageForm.price) }}</div>
                                </div>

                                <div>
                                    <label class="text-xs font-bold text-slate-500 mb-2 block">Metode Pembayaran</label>
                                    <div class="grid grid-cols-2 gap-3">
                                        <button @click="packageForm.paymentMethod = 'Cash'" :class="packageForm.paymentMethod === 'Cash' ? 'border-blue-600 bg-blue-50 text-blue-600 dark:bg-blue-900/40 dark:text-blue-400' : 'border-slate-200 dark:border-slate-600'" class="p-3 border rounded-xl text-xs font-bold transition-all">
                                            <i class="bi bi-cash"></i> Cash
                                        </button>
                                        <button @click="packageForm.paymentMethod = 'Transfer'" :class="packageForm.paymentMethod === 'Transfer' ? 'border-blue-600 bg-blue-50 text-blue-600 dark:bg-blue-900/40 dark:text-blue-400' : 'border-slate-200 dark:border-slate-600'" class="p-3 border rounded-xl text-xs font-bold transition-all">
                                            <i class="bi bi-bank"></i> Transfer
                                        </button>
                                    </div>
                                </div>

                                <div>
                                    <label class="text-xs font-bold text-slate-500 mb-2 block">Status Pembayaran</label>
                                    <div class="grid grid-cols-2 gap-3">
                                        <button @click="packageForm.paymentStatus = 'Lunas'" :class="packageForm.paymentStatus === 'Lunas' ? 'border-green-600 bg-green-50 text-green-600 dark:bg-green-900/40 dark:text-green-400' : 'border-slate-200 dark:border-slate-600'" class="p-3 border rounded-xl text-xs font-bold transition-all">
                                            Lunas
                                        </button>
                                        <button @click="packageForm.paymentStatus = 'Menunggu Pembayaran'" :class="packageForm.paymentStatus === 'Menunggu Pembayaran' ? 'border-red-600 bg-red-50 text-red-600 dark:bg-red-900/40 dark:text-red-400' : 'border-slate-200 dark:border-slate-600'" class="p-3 border rounded-xl text-xs font-bold transition-all">
                                            Belum Lunas
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <button @click="savePackage" :disabled="isLoading" class="w-full py-5 bg-gradient-to-r from-blue-600 to-indigo-700 text-white font-extrabold rounded-2xl shadow-xl shadow-blue-200 dark:shadow-none hover:shadow-2xl hover:scale-[1.02] transition-all mt-8 flex items-center justify-center gap-3 disabled:opacity-50 disabled:cursor-not-allowed">
                                <span v-if="isLoading"><i class="bi bi-arrow-repeat animate-spin"></i> Memproses...</span>
                                <span v-else><i class="bi bi-send-fill"></i> Buat Resi Sekarang</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- VIEW 3: HISTORY & TRACKING -->
                <div v-if="packageView === 'history'" class="max-w-6xl mx-auto space-y-6">
                    
                    <div class="flex flex-col md:flex-row justify-between items-center gap-4 bg-white dark:bg-slate-800 p-6 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-700">
                         <div>
                             <h3 class="text-xl font-extrabold text-slate-800 dark:text-white">Database Pengiriman</h3>
                             <p class="text-sm text-slate-500">Kelola dan lacak paket Anda di sini.</p>
                         </div>
                         <div class="w-full md:w-auto relative group">
                             <input type="text" v-model="packageSearch" placeholder="Cari No. Resi, Nama, atau No HP..." class="pl-12 pr-4 py-3 border-2 border-slate-200 rounded-2xl w-full md:w-80 dark:bg-slate-700 dark:border-slate-600 text-sm font-semibold focus:border-blue-500 focus:outline-none transition-all">
                             <i class="bi bi-search absolute left-4 top-3.5 text-slate-400 group-focus-within:text-blue-500 transition-colors"></i>
                         </div>
                    </div>

                    <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700">
                                        <th class="p-5 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Info Resi</th>
                                        <th class="p-5 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Pelanggan</th>
                                        <th class="p-5 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Isi Paket</th>
                                        <th class="p-5 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Rute</th>
                                        <th class="p-5 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Pembayaran</th>
                                        <th class="p-5 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Status</th>
                                        <th class="p-5 text-[10px] font-bold text-slate-400 uppercase tracking-wider text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                    <tr v-for="p in filteredPackages" :key="p.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors group">
                                        <td class="p-5">
                                            <div class="font-mono font-bold text-blue-600 dark:text-blue-400 text-sm tracking-tight mb-1">
                                                {{ p.receiptNumber || 'PK-'+p.id }}
                                            </div>
                                            <div class="text-[10px] text-slate-400 font-bold">{{ formatDate(p.bookingDate) }}</div>
                                        </td>
                                        <td class="p-5">
                                            <div class="space-y-1">
                                                <div class="flex items-center gap-2 text-xs font-bold text-slate-700 dark:text-slate-200">
                                                    <div class="w-5 h-5 rounded bg-blue-100 dark:bg-blue-900/50 text-blue-600 flex items-center justify-center text-[10px]"><i class="bi bi-box-arrow-up"></i></div>
                                                    {{ p.senderName }}
                                                </div>
                                                <div class="flex items-center gap-2 text-xs font-bold text-slate-700 dark:text-slate-200">
                                                    <div class="w-5 h-5 rounded bg-orange-100 dark:bg-orange-900/50 text-orange-600 flex items-center justify-center text-[10px]"><i class="bi bi-box-arrow-in-down"></i></div>
                                                    {{ p.receiverName }}
                                                </div>
                                            </div>
                                        </td>
                                        <td class="p-5">
                                            <div class="text-xs font-bold text-slate-800 dark:text-white mb-0.5">{{ p.itemType }}</div>
                                            <div class="text-[10px] text-slate-500">{{ p.itemDescription }}</div>
                                        </td>
                                        <td class="p-5">
                                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold mb-1" :class="p.category === 'Antar Jemput Alamat' ? 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300'">
                                                {{ p.category === 'Antar Jemput Alamat' ? 'Door to Door' : 'Pool to Pool' }}
                                            </span>
                                            <div class="text-[10px] font-bold text-slate-500">{{ p.route }}</div>
                                        </td>
                                        <td class="p-5">
                                            <div class="font-extrabold text-slate-800 dark:text-white text-sm mb-0.5">{{ formatRupiah(p.price) }}</div>
                                            <span class="text-[10px] font-bold" :class="p.paymentStatus === 'Lunas' ? 'text-green-500' : 'text-red-500'">{{ p.paymentStatus }}</span>
                                        </td>
                                        <td class="p-5">
                                            <button @click="openTrackingModal(p)" class="px-3 py-1.5 rounded-lg text-xs font-bold border flex items-center gap-1.5 transition-all w-fit" :class="{
                                                'bg-yellow-50 border-yellow-200 text-yellow-700': p.status === 'Pending',
                                                'bg-blue-50 border-blue-200 text-blue-700': p.status === 'Dikirim',
                                                'bg-green-50 border-green-200 text-green-700': p.status === 'Sampai' || p.status === 'Diterima'
                                            }">
                                                {{ p.status }} <i class="bi bi-clock-history"></i>
                                            </button>
                                        </td>
                                        <td class="p-5 text-right space-y-2">
                                            <button @click="printReceipt(p)" class="w-full py-1.5 rounded-lg text-[10px] font-bold bg-slate-800 text-white hover:bg-slate-700 transition-all flex items-center justify-center gap-1">
                                                <i class="bi bi-printer"></i> Cetak Resi
                                            </button>
                                            <button @click="openStatusUpdateModal(p)" class="w-full py-1.5 rounded-lg text-[10px] font-bold bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-slate-700 dark:text-slate-300 transition-all">
                                                Update Status
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="filteredPackages.length === 0">
                                        <td colspan="7" class="p-16 text-center">
                                            <div class="inline-block p-4 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 text-3xl mb-4"><i class="bi bi-inbox"></i></div>
                                            <h4 class="text-lg font-bold text-slate-700 dark:text-slate-300">Data Tidak Ditemukan</h4>
                                            <p class="text-sm text-slate-500">Coba kata kunci lain atau buat pesanan baru.</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>

             <!-- Modals Area (Reused Logic) -->
            
            <!-- 1. Tracking Modal -->
            <div v-if="isTrackingModalVisible" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-md p-4 animate-fade-in">
                <div class="bg-white dark:bg-slate-800 w-full max-w-lg rounded-3xl shadow-2xl overflow-hidden flex flex-col max-h-[85vh]">
                    <div class="p-6 bg-slate-50 dark:bg-slate-900 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center">
                        <div>
                             <h3 class="text-xl font-extrabold text-slate-800 dark:text-white">Melacak Paket</h3>
                             <p class="text-xs text-slate-500 font-mono mt-1">{{ activePackage.receiptNumber }}</p>
                        </div>
                        <button @click="closeTrackingModal" class="w-9 h-9 rounded-full bg-white dark:bg-slate-700 shadow-sm text-slate-500 flex items-center justify-center hover:bg-slate-100 transition-colors"><i class="bi bi-x-lg"></i></button>
                    </div>
                    
                    <div class="p-8 overflow-y-auto custom-scrollbar flex-1">
                        <div class="space-y-8 relative pl-2">
                             <!-- Timeline Line -->
                             <div class="absolute left-[19px] top-2 bottom-2 w-0.5 bg-slate-200 dark:bg-slate-700"></div>

                             <div v-for="(log, idx) in activePackageLogs" :key="idx" class="relative pl-10 group">
                                 <!-- Dot -->
                                 <div class="absolute left-0 top-0 w-10 h-10 rounded-full border-4 border-white dark:border-slate-800 flex items-center justify-center shadow-sm z-10 transition-transform group-hover:scale-110" 
                                      :class="idx === 0 ? 'bg-blue-600 text-white scale-110' : 'bg-slate-100 dark:bg-slate-700 text-slate-400'">
                                     <i class="bi text-sm" :class="{
                                         'bi-box-seam': log.status === 'Pending',
                                         'bi-truck': log.status === 'Dikirim',
                                         'bi-check-lg': log.status === 'Sampai' || log.status === 'Diterima'
                                     }"></i>
                                 </div>
                                 
                                 <div class="bg-slate-50 dark:bg-slate-700/30 p-4 rounded-2xl border border-slate-100 dark:border-slate-700/50 hover:border-blue-200 transition-colors">
                                     <div class="flex justify-between items-start mb-1">
                                         <h4 class="font-bold text-slate-800 dark:text-white">{{ log.status }}</h4>
                                         <span class="text-[10px] font-bold text-slate-400 bg-white dark:bg-slate-800 px-2 py-1 rounded-lg border border-slate-200 dark:border-slate-600">{{ formatDateComplete(log.created_at) }}</span>
                                     </div>
                                     <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">{{ log.description }}</p>
                                     <div v-if="log.location && log.location !== '-'" class="mt-2 text-xs font-bold text-blue-600 flex items-center gap-1">
                                         <i class="bi bi-geo-alt-fill"></i> {{ log.location }}
                                     </div>
                                     <div class="mt-2 text-[10px] text-slate-400 text-right italic">Updated by: {{ log.admin_name }}</div>
                                 </div>
                             </div>
                             
                             <div v-if="activePackageLogs.length === 0" class="pl-10 text-center py-8 text-slate-400 italic">
                                 Belum ada data tracking tersedia.
                             </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Update Status Modal -->
            <div v-if="isStatusModalVisible" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-md p-4 animate-fade-in">
                <div class="bg-white dark:bg-slate-800 w-full max-w-sm rounded-3xl shadow-2xl p-8">
                    <h3 class="text-xl font-extrabold text-slate-800 dark:text-white mb-6 text-center">Update Status</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="text-xs font-bold text-slate-500 mb-1 block">Status Terkini</label>
                            <select v-model="statusForm.status" class="w-full p-3 border rounded-xl dark:bg-slate-700 dark:border-slate-600 font-bold text-sm bg-slate-50 dark:bg-slate-900">
                                <option value="Pending">Pending (Menunggu)</option>
                                <option value="Dikirim">Dikirim (Sedang Diantar)</option>
                                <option value="Sampai">Sampai (Tiba di Tujuan)</option>
                                <option value="Diterima">Diterima (Selesai)</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-bold text-slate-500 mb-1 block">Lokasi Paket</label>
                            <input type="text" v-model="statusForm.location" class="w-full p-3 border rounded-xl dark:bg-slate-700 dark:border-slate-600 text-sm" placeholder="Misal: Gudang Utama">
                        </div>
                        <div>
                            <label class="text-xs font-bold text-slate-500 mb-1 block">Keterangan</label>
                            <textarea v-model="statusForm.description" class="w-full p-3 border rounded-xl dark:bg-slate-700 dark:border-slate-600 text-sm h-24 resize-none" placeholder="Catatan tambahan..."></textarea>
                        </div>
                        
                        <div class="pt-4 flex gap-3">
                             <button @click="isStatusModalVisible = false" class="flex-1 py-3 text-slate-500 font-bold rounded-xl hover:bg-slate-100 dark:hover:bg-slate-700 transition-all text-sm">
                                Batal
                            </button>
                            <button @click="saveStatusUpdate" class="flex-1 py-3 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 shadow-lg shadow-blue-200 dark:shadow-none transition-all text-sm">
                                Simpan
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hidden Iframe for Printing -->
            <div id="receipt-print-area" class="hidden"></div>
            
        </main>
    </div>
    <script type="module" src="app.js?v=<?= time() ?>"></script>
</body>
</html>
