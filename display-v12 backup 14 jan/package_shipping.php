<!DOCTYPE html>
<html lang="id" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sutan Raya - Kirim Paket</title>
    <link rel="icon" type="image/webp" href="../image/logo.webp">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    
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

        .price-card {
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .price-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
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
        
        <?php $currentPage = 'package_shipping'; include 'components/sidebar.php'; ?>

        <main class="flex-1 flex flex-col min-w-0 bg-slate-50 dark:bg-slate-900 relative h-full overflow-hidden transition-colors duration-300">
            <header class="h-16 bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between px-6 shadow-sm z-10 flex-shrink-0 transition-colors duration-300">
                <div>
                    <h2 class="text-lg font-bold text-slate-800 dark:text-white">Layanan Kirim Paket</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Daftar Tarif & Ketentuan Pengiriman</p>
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
                
                <!-- Tab Switcher -->
                <div class="max-w-6xl mx-auto mb-8 bg-white dark:bg-slate-800 p-1.5 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 flex gap-2 w-fit">
                    <button @click="packageView = 'info'" :class="packageView === 'info' ? 'bg-blue-600 text-white shadow-lg shadow-blue-200 dark:shadow-none' : 'text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-700'" class="px-6 py-2.5 rounded-xl text-sm font-bold transition-all flex items-center gap-2">
                        <i class="bi bi-info-circle-fill"></i> Info & Tarif
                    </button>
                    <button @click="packageView = 'booking'" :class="packageView === 'booking' ? 'bg-blue-600 text-white shadow-lg shadow-blue-200 dark:shadow-none' : 'text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-700'" class="px-6 py-2.5 rounded-xl text-sm font-bold transition-all flex items-center gap-2">
                        <i class="bi bi-plus-circle-fill"></i> Buat Pesanan
                    </button>
                    <button @click="packageView = 'history'" :class="packageView === 'history' ? 'bg-blue-600 text-white shadow-lg shadow-blue-200 dark:shadow-none' : 'text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-700'" class="px-6 py-2.5 rounded-xl text-sm font-bold transition-all flex items-center gap-2">
                        <i class="bi bi-clock-history"></i> Riwayat Pengiriman
                    </button>
                </div>

                <!-- Tab: Info & Tarif -->
                <div v-if="packageView === 'info'" class="space-y-8">
                    <!-- Section: Tarif Pool to Pool -->

                    <!-- <section class="max-w-6xl mx-auto mb-5">
                        <p class="text-[34px] font-bold text-slate-800 dark:text-white text-center">Informasi ini digunakan untuk mengetahui tarif <br> pengiriman paket antar pool dan kepada pelanggan</p>
                    </section> -->
                    <section class="max-w-6xl mx-auto">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center shadow-lg shadow-blue-200 dark:shadow-none">
                                <i class="bi bi-box-seam-fill text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-extrabold text-slate-800 dark:text-white">1. Layanan Pool to Pool</h3>
                                <p class="text-sm text-slate-500">Pengiriman antar titik kumpul (pool) keberangkatan.</p>
                            </div>
                        </div>
                        
                        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700">
                                        <th class="p-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider w-12 text-center">No</th>
                                        <th class="p-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Uraian Barang</th>
                                        <th class="p-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Pdg - Bkt</th>
                                        <th class="p-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Pdg - Pyk</th>
                                        <th class="p-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Bkt - Pyk</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                        <td class="p-4 text-center font-bold text-slate-400">1</td>
                                        <td class="p-4 font-bold text-slate-700 dark:text-slate-200">Surat / Dokumen</td>
                                        <td class="p-4 font-bold text-blue-600">Rp30.000</td>
                                        <td class="p-4 font-bold text-blue-600">Rp40.000</td>
                                        <td class="p-4 font-bold text-blue-600">Rp15.000</td>
                                    </tr>
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                        <td class="p-4 text-center font-bold text-slate-400">2</td>
                                        <td class="p-4 font-bold text-slate-700 dark:text-slate-200">Barang Kardus</td>
                                        <td class="p-4 font-bold text-blue-600">Rp30.000</td>
                                        <td class="p-4 font-bold text-blue-600">Rp40.000</td>
                                        <td class="p-4 font-bold text-blue-600">Rp15.000</td>
                                    </tr>
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                        <td class="p-4 text-center font-bold text-slate-400">3</td>
                                        <td class="p-4 font-bold text-slate-700 dark:text-slate-200">Barang Lainnya / Big Size</td>
                                        <td class="p-4 font-bold text-blue-600">Rp100.000</td>
                                        <td class="p-4 font-bold text-blue-600">Rp130.000</td>
                                        <td class="p-4 font-bold text-blue-600">Rp50.000</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>
                    <!-- Section: Tarif Door to Door -->
                    <section class="max-w-6xl mx-auto">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 rounded-xl bg-orange-600 text-white flex items-center justify-center shadow-lg shadow-orange-200 dark:shadow-none">
                                <i class="bi bi-house-door-fill text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-extrabold text-slate-800 dark:text-white">2. Layanan Antar Jemput Alamat</h3>
                                <p class="text-sm text-slate-500">Pengiriman langsung ke alamat pengirim dan penerima.</p>
                            </div>
                        </div>
                        
                        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700">
                                        <th class="p-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider w-12 text-center">No</th>
                                        <th class="p-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Uraian Barang</th>
                                        <th class="p-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Pdg - Bkt</th>
                                        <th class="p-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Pdg - Pyk</th>
                                        <th class="p-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Bkt - Pyk</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                        <td class="p-4 text-center font-bold text-slate-400">1</td>
                                        <td class="p-4 font-bold text-slate-700 dark:text-slate-200">Surat / Dokumen</td>
                                        <td class="p-4 font-bold text-orange-600">Rp60.000</td>
                                        <td class="p-4 font-bold text-orange-600">Rp70.000</td>
                                        <td class="p-4 font-bold text-orange-600">Rp25.000</td>
                                    </tr>
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                        <td class="p-4 text-center font-bold text-slate-400">2</td>
                                        <td class="p-4 font-bold text-slate-700 dark:text-slate-200">Barang Kardus</td>
                                        <td class="p-4 font-bold text-orange-600">Rp60.000</td>
                                        <td class="p-4 font-bold text-orange-600">Rp70.000</td>
                                        <td class="p-4 font-bold text-orange-600">Rp25.000</td>
                                    </tr>
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                        <td class="p-4 text-center font-bold text-slate-400">3</td>
                                        <td class="p-4 font-bold text-slate-700 dark:text-slate-200">Barang Lainnya / Big Size</td>
                                        <td class="p-4 font-bold text-orange-600">Rp120.000</td>
                                        <td class="p-4 font-bold text-orange-600">Rp150.000</td>
                                        <td class="p-4 font-bold text-orange-600">Rp70.000</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>
                    <!-- Section: Syarat & Ketentuan -->
                    <section class="max-w-6xl mx-auto pb-12">
                        <div class="bg-slate-800 dark:bg-slate-800 rounded-3xl p-8 text-white relative overflow-hidden shadow-2xl">
                            <div class="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full -mr-20 -mt-20"></div>
                            <div class="absolute bottom-0 left-0 w-48 h-48 bg-blue-600/20 rounded-full -ml-10 -mb-10"></div>
                            <div class="relative z-10">
                                <h3 class="text-2xl font-extrabold mb-6 flex items-center gap-3"><i class="bi bi-shield-check text-blue-400"></i> Syarat & Ketentuan</h3>
                                <div class="grid md:grid-cols-2 gap-6">
                                    <div class="space-y-4">
                                        <div class="flex gap-4">
                                            <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center font-bold flex-shrink-0">A</div>
                                            <div><h4 class="font-bold text-blue-400 mb-1 uppercase text-xs tracking-wider">Pengemasan</h4><p class="text-sm text-slate-300">Paket wajib dikemas dengan baik dan aman untuk melindungi isi barang selama perjalanan.</p></div>
                                        </div>
                                        <div class="flex gap-4">
                                            <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center font-bold flex-shrink-0">B</div>
                                            <div><h4 class="font-bold text-blue-400 mb-1 uppercase text-xs tracking-wider">Informasi Pengiriman</h4><p class="text-sm text-slate-300">Pengirim wajib memberikan informasi akurat mengenai isi paket, alamat pengiriman, dan kontak penerima.</p></div>
                                        </div>
                                        <div class="flex gap-4">
                                            <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center font-bold flex-shrink-0">C</div>
                                            <div><h4 class="font-bold text-blue-400 mb-1 uppercase text-xs tracking-wider">Biaya Pengiriman</h4><p class="text-sm text-slate-300">Biaya ditentukan berdasarkan berat, ukuran, dan tujuan paket sesuai daftar tarif yang berlaku.</p></div>
                                        </div>
                                    </div>
                                    <div class="space-y-4">
                                        <div class="flex gap-4">
                                            <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center font-bold flex-shrink-0 text-red-400">D</div>
                                            <div><h4 class="font-bold text-red-400 mb-1 uppercase text-xs tracking-wider">Barang yang Dilarang</h4><p class="text-sm text-slate-300">Sutan Raya tidak menerima pengiriman senjata, narkoba, atau barang yang berbau menyengat/berbahaya.</p></div>
                                        </div>
                                        <div class="flex gap-4">
                                            <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center font-bold flex-shrink-0 text-green-400">E</div>
                                            <div><h4 class="font-bold text-green-400 mb-1 uppercase text-xs tracking-wider">Klaim</h4><p class="text-sm text-slate-300">Pengirim dapat mengajukan klaim jika paket hilang atau rusak dengan menyertakan dokumen pendukung.</p></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>

                <!-- <div class="w-100 h-10 bg-slate-200">
                    <div class="text-md font-bold">Pengiriman</div>
                </div> -->

                <!-- Tab: Booking Flow -->
                <div v-if="packageView === 'booking'" class="max-w-5xl mx-auto space-y-6">
                    <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-xl border border-slate-200 dark:border-slate-700 overflow-hidden flex flex-col md:flex-row">
                        <!-- Form Area -->
                        <div class="flex-1 p-8 border-r border-slate-100 dark:border-slate-700">
                            <h3 class="text-xl font-extrabold text-slate-800 dark:text-white mb-6">Informasi Pengiriman</h3>
                            
                            <div class="space-y-6">
                                <!-- Step 1: Pengirim & Penerima -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="space-y-4">
                                        <h4 class="text-[10px] font-bold text-blue-600 uppercase tracking-widest bg-blue-50 dark:bg-blue-900/30 w-fit px-2 py-1 rounded">Pengirim</h4>
                                        <div>
                                            <label class="text-xs font-bold text-slate-500 mb-1 block">Nama Pengirim</label>
                                            <input type="text" v-model="packageForm.senderName" class="w-full p-2.5 border rounded-xl dark:bg-slate-700 dark:border-slate-600 text-sm" placeholder="Contoh: Rian">
                                        </div>
                                        <div>
                                            <label class="text-xs font-bold text-slate-500 mb-1 block">No. WhatsApp</label>
                                            <input type="text" v-model="packageForm.senderPhone" class="w-full p-2.5 border rounded-xl dark:bg-slate-700 dark:border-slate-600 text-sm" placeholder="08...">
                                        </div>
                                    </div>
                                    <div class="space-y-4">
                                        <h4 class="text-[10px] font-bold text-orange-600 uppercase tracking-widest bg-orange-50 dark:bg-orange-900/30 w-fit px-2 py-1 rounded">Penerima</h4>
                                        <div>
                                            <label class="text-xs font-bold text-slate-500 mb-1 block">Nama Penerima</label>
                                            <input type="text" v-model="packageForm.receiverName" class="w-full p-2.5 border rounded-xl dark:bg-slate-700 dark:border-slate-600 text-sm" placeholder="Contoh: Jidud">
                                        </div>
                                        <div>
                                            <label class="text-xs font-bold text-slate-500 mb-1 block">No. WhatsApp</label>
                                            <input type="text" v-model="packageForm.receiverPhone" class="w-full p-2.5 border rounded-xl dark:bg-slate-700 dark:border-slate-600 text-sm" placeholder="08...">
                                        </div>
                                    </div>
                                </div>

                                <!-- Step 2: Barang & Rute -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-slate-100 dark:border-slate-700">
                                    <div class="space-y-4">
                                        <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Detail Barang</h4>
                                        <div>
                                            <label class="text-xs font-bold text-slate-500 mb-1 block">Deskripsi Barang</label>
                                            <input type="text" v-model="packageForm.itemDescription" class="w-full p-2.5 border rounded-xl dark:bg-slate-700 dark:border-slate-600 text-sm" placeholder="Contoh: Dokumen Ijazah, Jaket">
                                        </div>
                                        <div>
                                            <label class="text-xs font-bold text-slate-500 mb-1 block">Jenis Barang</label>
                                            <select v-model="packageForm.itemType" @change="calculatePackagePrice" class="w-full p-2.5 border rounded-xl dark:bg-slate-700 dark:border-slate-600 text-sm font-bold">
                                                <option>Surat / Dokumen</option>
                                                <option>Barang Kardus</option>
                                                <option>Barang Lainnya / Big Size</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="space-y-4">
                                        <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Layanan & Rute</h4>
                                        <div>
                                            <label class="text-xs font-bold text-slate-500 mb-1 block">Kategori Layanan</label>
                                            <select v-model="packageForm.category" @change="calculatePackagePrice" class="w-full p-2.5 border rounded-xl dark:bg-slate-700 dark:border-slate-600 text-sm font-bold">
                                                <option>Pool to Pool</option>
                                                <option>Antar Jemput Alamat</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="text-xs font-bold text-slate-500 mb-1 block">Rute</label>
                                            <select v-model="packageForm.route" @change="calculatePackagePrice" class="w-full p-2.5 border rounded-xl dark:bg-slate-700 dark:border-slate-600 text-sm font-bold">
                                                <option>Padang - Bukittinggi</option>
                                                <option>Padang - Payakumbuh</option>
                                                <option>Bukittinggi - Payakumbuh</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Door to Door Fields -->
                                <div v-if="packageForm.category === 'Antar Jemput Alamat'" class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-slate-100 dark:border-slate-700 animate-fade-in">
                                     <div>
                                         <label class="text-xs font-bold text-slate-500 mb-1 block">Alamat Jemput (Pengirim)</label>
                                         <textarea v-model="packageForm.pickupAddress" class="w-full p-2.5 border rounded-xl dark:bg-slate-700 dark:border-slate-600 text-sm h-20"></textarea>
                                     </div>
                                     <div>
                                         <label class="text-xs font-bold text-slate-500 mb-1 block">Alamat Antar (Penerima)</label>
                                         <textarea v-model="packageForm.dropoffAddress" class="w-full p-2.5 border rounded-xl dark:bg-slate-700 dark:border-slate-600 text-sm h-20"></textarea>
                                     </div>
                                     <div class="md:col-span-2">
                                         <label class="text-xs font-bold text-slate-500 mb-1 block">Link Google Maps</label>
                                         <input type="text" v-model="packageForm.mapLink" class="w-full p-2.5 border rounded-xl dark:bg-slate-700 dark:border-slate-600 text-sm" placeholder="https://maps.google.com/...">
                                     </div>
                                </div>
                            </div>
                        </div>



                        <!-- Summary Column -->
                        <div class="w-full md:w-80 bg-slate-50 dark:bg-slate-900/50 p-8 flex flex-col">
                            <h3 class="text-lg font-bold text-slate-800 dark:text-white mb-6">Ringkasan</h3>
                            
                            <div class="flex-1 space-y-4">
                                <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200 dark:border-slate-700">
                                    <div class="text-[10px] font-bold text-slate-400 uppercase mb-1">Total Biaya</div>
                                    <div class="text-2xl font-extrabold text-blue-600">{{ formatRupiah(packageForm.price) }}</div>
                                </div>

                                <div>
                                    <label class="text-xs font-bold text-slate-500 mb-2 block">Metode Bayar</label>
                                    <div class="grid grid-cols-2 gap-2">
                                        <button @click="packageForm.paymentMethod = 'Cash'" :class="packageForm.paymentMethod === 'Cash' ? 'border-blue-600 bg-blue-50 text-blue-600' : 'border-slate-200'" class="p-2 border rounded-xl text-xs font-bold transition-all">Cash</button>
                                        <button @click="packageForm.paymentMethod = 'Transfer'" :class="packageForm.paymentMethod === 'Transfer' ? 'border-blue-600 bg-blue-50 text-blue-600' : 'border-slate-200'" class="p-2 border rounded-xl text-xs font-bold transition-all">Transfer</button>
                                    </div>
                                </div>

                                <div>
                                    <label class="text-xs font-bold text-slate-500 mb-2 block">Status Bayar</label>
                                    <div class="grid grid-cols-2 gap-2">
                                        <button @click="packageForm.paymentStatus = 'Lunas'" :class="packageForm.paymentStatus === 'Lunas' ? 'border-green-600 bg-green-50 text-green-600' : 'border-slate-200'" class="p-2 border rounded-xl text-xs font-bold transition-all">Lunas</button>
                                        <button @click="packageForm.paymentStatus = 'Menunggu Pembayaran'" :class="packageForm.paymentStatus === 'Menunggu Pembayaran' ? 'border-red-600 bg-red-50 text-red-600' : 'border-slate-200'" class="p-2 border rounded-xl text-xs font-bold transition-all">Belum</button>
                                    </div>
                                </div>
                            </div>

                            <button @click="savePackage" class="w-full py-4 bg-blue-600 text-white font-extrabold rounded-2xl shadow-xl shadow-blue-200 hover:bg-blue-700 transition-all mt-8 flex items-center justify-center gap-2">
                                <i class="bi bi-box-fill"></i> Simpan Pesanan
                            </button>
                        </div>
                    </div>
                </div>
                

                <!-- Tab: History / Management -->
                <div v-if="packageView === 'history'" class="max-w-6xl mx-auto space-y-6">
                    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                         <div>
                             <h3 class="text-xl font-extrabold text-slate-800 dark:text-white">Kelola Pengiriman Paket</h3>
                             <div class="text-sm text-slate-500 font-bold">Total: {{ packages.length }} Kiriman</div>
                         </div>
                         <div class="w-full md:w-auto">
                             <div class="relative">
                                 <input type="text" v-model="packageSearch" placeholder="Cari Resi, Pengirim, atau Penerima..." class="pl-10 pr-4 py-2 border rounded-xl w-full md:w-64 dark:bg-slate-700 dark:border-slate-600 text-sm">
                                 <i class="bi bi-search absolute left-3 top-2.5 text-slate-400"></i>
                             </div>
                         </div>
                    </div>

                    <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700">
                                        <th class="p-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Tgl / ID</th>
                                        <th class="p-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Pengirim / Penerima</th>
                                        <th class="p-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Barang</th>
                                        <th class="p-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Layanan</th>
                                        <th class="p-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Biaya</th>
                                        <th class="p-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Status</th>
                                        <th class="p-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                    <tr v-for="p in filteredPackages" :key="p.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                        <td class="p-4">
                                            <div class="font-bold text-slate-800 dark:text-white text-xs">{{ formatDate(p.bookingDate) }}</div>
                                            <div class="text-[10px] text-slate-400 font-mono">#{{ p.receiptNumber || 'PK'+p.id }}</div>
                                            <div v-if="p.receiptNumber" class="mt-1">
                                                <button @click="printReceipt(p)" class="text-[10px] bg-slate-800 text-white px-2 py-0.5 rounded flex items-center gap-1 hover:bg-slate-700">
                                                    <i class="bi bi-printer-fill"></i> Resi
                                                </button>
                                            </div>
                                        </td>
                                        <td class="p-4">
                                            <div class="flex flex-col gap-1">
                                                <div class="text-xs font-bold flex items-center gap-2">
                                                    <i class="bi bi-person-fill text-blue-400"></i> {{ p.senderName }}
                                                </div>
                                                <div class="text-xs font-bold flex items-center gap-2">
                                                    <i class="bi bi-person-fill text-orange-400"></i> {{ p.receiverName }}
                                                </div>
                                            </div>
                                        </td>
                                        <td class="p-4">
                                            <div class="text-xs font-bold text-slate-700 dark:text-slate-200">{{ p.itemType }}</div>
                                            <div class="text-[10px] text-slate-500 italic">{{ p.itemDescription }}</div>
                                        </td>
                                        <td class="p-4">
                                            <span :class="p.category === 'Pool to Pool' ? 'bg-blue-50 text-blue-600' : 'bg-orange-50 text-orange-600'" class="px-2 py-0.5 rounded text-[10px] font-bold">{{ p.category }}</span>
                                            <div class="text-[10px] font-bold text-slate-400 mt-1">{{ p.route }}</div>
                                            <a v-if="p.mapLink" :href="p.mapLink" target="_blank" class="mt-2 flex items-center gap-1.5 text-[10px] font-bold text-blue-600 hover:text-blue-700 underline decoration-blue-200">
                                                <i class="bi bi-geo-alt-fill"></i> Lihat Map
                                            </a>
                                        </td>
                                        <td class="p-4">
                                            <div class="font-bold text-slate-800 dark:text-white text-xs">{{ formatRupiah(p.price) }}</div>
                                            <span :class="p.paymentStatus === 'Lunas' ? 'text-green-500' : 'text-red-500'" class="text-[10px] font-bold">{{ p.paymentStatus }}</span>
                                        </td>
                                        <td class="p-4">
                                            <span :class="{
                                                'bg-yellow-50 text-yellow-600': p.status === 'Pending',
                                                'bg-blue-50 text-blue-600': p.status === 'Dikirim',
                                                'bg-green-50 text-green-600': p.status === 'Sampai' || p.status === 'Diterima'
                                            }" class="px-2 py-1 rounded-lg text-[10px] font-bold border border-current/20 cursor-pointer hover:opacity-80" @click="openTrackingModal(p)">
                                                {{ p.status }} <i class="bi bi-eye-fill ml-1"></i>
                                            </span>
                                        </td>
                                        <td class="p-4 text-right">
                                            <button @click="openStatusUpdateModal(p)" class="text-[10px] font-bold px-3 py-1.5 rounded bg-blue-100 text-blue-700 hover:bg-blue-200 transition-colors">
                                                Update Status
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="packages.length === 0">
                                        <td colspan="7" class="p-12 text-center text-slate-400 font-bold italic">Belum ada data pengiriman.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modals Area -->
            
            <!-- 1. Tracking Modal -->
            <div v-if="isTrackingModalVisible" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4 animate-fade-in">
                <div class="bg-white dark:bg-slate-800 w-full max-w-lg rounded-3xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
                    <div class="p-6 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center">
                        <h3 class="text-lg font-bold text-slate-800 dark:text-white">Lacak Paket</h3>
                        <button @click="closeTrackingModal" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-500 flex items-center justify-center hover:bg-slate-200 transition-colors"><i class="bi bi-x-lg"></i></button>
                    </div>
                    
                    <div class="p-6 overflow-y-auto custom-scrollbar flex-1">
                        <div class="flex items-center gap-4 mb-6 p-4 bg-slate-50 dark:bg-slate-900 rounded-2xl">
                             <div class="w-12 h-12 bg-blue-600 rounded-xl flex items-center justify-center text-white text-2xl">
                                 <i class="bi bi-box-seam"></i>
                             </div>
                             <div>
                                 <div class="text-xs text-slate-400 uppercase font-bold tracking-wider">No Resi</div>
                                 <div class="text-xl font-bold font-mono text-slate-800 dark:text-white">{{ activePackage.receiptNumber || '-' }}</div>
                             </div>
                        </div>

                        <div class="space-y-6 relative pl-4 border-l-2 border-slate-200 dark:border-slate-700 ml-2">
                             <div v-for="(log, idx) in activePackageLogs" :key="idx" class="relative pl-6">
                                 <div class="absolute -left-[9px] top-0 w-4 h-4 rounded-full border-2 border-white dark:border-slate-800" 
                                      :class="idx === 0 ? 'bg-blue-600 ring-4 ring-blue-100 dark:ring-blue-900/30' : 'bg-slate-300 dark:bg-slate-600'"></div>
                                 
                                 <div class="text-xs text-slate-400 font-bold mb-0.5">{{ formatDateComplete(log.created_at) }}</div>
                                 <h4 class="font-bold text-slate-800 dark:text-white" :class="{'text-blue-600': idx === 0}">{{ log.status }}</h4>
                                 <p class="text-sm text-slate-500 mt-1 bg-slate-50 dark:bg-slate-700/50 p-3 rounded-xl">
                                     {{ log.description }}
                                     <span v-if="log.location && log.location !== '-'" class="block mt-1 text-xs font-bold text-slate-400"><i class="bi bi-geo-alt"></i> {{ log.location }}</span>
                                 </p>
                                 <div class="text-[10px] text-slate-300 mt-1 text-right">Updated by {{ log.admin_name }}</div>
                             </div>
                             <div v-if="activePackageLogs.length === 0" class="pl-6 text-sm text-slate-400 italic">Belum ada riwayat update.</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Update Status Modal -->
            <div v-if="isStatusModalVisible" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4 animate-fade-in">
                <div class="bg-white dark:bg-slate-800 w-full max-w-sm rounded-3xl shadow-2xl p-6">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-white mb-4">Update Status Paket</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="text-xs font-bold text-slate-500 mb-1 block">Status Baru</label>
                            <select v-model="statusForm.status" class="w-full p-2.5 border rounded-xl dark:bg-slate-700 dark:border-slate-600 font-bold">
                                <option value="Pending">Pending</option>
                                <option value="Dikirim">Dikirim (On Process)</option>
                                <option value="Sampai">Sampai (Arrived)</option>
                                <option value="Diterima">Diterima (Completed)</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-bold text-slate-500 mb-1 block">Lokasi Terkini</label>
                            <input type="text" v-model="statusForm.location" class="w-full p-2.5 border rounded-xl dark:bg-slate-700 dark:border-slate-600 text-sm" placeholder="Contoh: Pool Bukittinggi">
                        </div>
                        <div>
                            <label class="text-xs font-bold text-slate-500 mb-1 block">Catatan / Keterangan</label>
                            <textarea v-model="statusForm.description" class="w-full p-2.5 border rounded-xl dark:bg-slate-700 dark:border-slate-600 text-sm h-20" placeholder="Contoh: Paket sedang disortir..."></textarea>
                        </div>
                        <button @click="saveStatusUpdate" class="w-full py-3 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition-all">
                            Simpan Update
                        </button>
                        <button @click="isStatusModalVisible = false" class="w-full py-3 text-slate-500 font-bold rounded-xl hover:bg-slate-100 dark:hover:bg-slate-700 transition-all">
                            Batal
                        </button>
                    </div>
                </div>
            </div>

            <!-- 3. Receipt Printer Area (Hidden) -->
            <div id="receipt-print-area" class="hidden">
                 <!-- Will be injected by JS -->
            </div>

        </main>
    </div>
    <script type="module" src="app.js?v=<?= time() ?>"></script>
    <script>
        // Additional Helper for Printing functionality embedded
        const { createApp } = Vue;
    </script>
</body>
</html>

