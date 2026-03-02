<!DOCTYPE html>
<html lang="id" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panduan Penggunaan - Purchasing Sutan Raya</title>
    <link rel="icon" type="image/webp" href="../image/logo.webp">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        [v-cloak] { display: none; }
    </style>
    <script>
        tailwind.config = { darkMode: 'class' }
    </script>
</head>
<body class="h-screen w-screen bg-slate-50 dark:bg-slate-900">

    <div id="app" class="flex h-full w-full" v-cloak>
        
        <?php $currentPage = 'guide'; include 'components/sidebar.php'; ?>

        <main class="flex-1 flex flex-col min-w-0 bg-slate-50 dark:bg-slate-900 overflow-hidden relative">
            <header class="h-16 bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between px-6 shadow-sm z-10 shrink-0">
                <div class="flex items-center gap-3">
                    <button onclick="toggleSidebar()" class="lg:hidden text-slate-600 dark:text-slate-300 hover:text-blue-600 p-2 -ml-2">
                        <i class="bi bi-list text-2xl"></i>
                    </button>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800 dark:text-white">Panduan Penggunaan</h2>
                        <p class="text-xs text-slate-500">Cara mudah menggunakan aplikasi Purchasing</p>
                    </div>
                </div>
            </header>

            <div class="flex-1 overflow-hidden p-6 custom-scrollbar overflow-y-auto">
                
                <div class="max-w-4xl mx-auto space-y-8">
                    
                    <!-- Intro -->
                    <div class="bg-gradient-to-br from-blue-600 to-blue-700 rounded-3xl p-8 text-white shadow-lg relative overflow-hidden">
                        <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-white opacity-10 rounded-full blur-3xl"></div>
                        <h3 class="text-2xl font-bold mb-3 relative z-10">Selamat Datang di Purchasing System</h3>
                        <p class="text-blue-100 leading-relaxed max-w-2xl relative z-10">
                            Aplikasi ini dibuat untuk merapikan alur pembelian dan penggunaan barang di Sutan Raya. 
                            Mulai dari permintaan barang baru, pemesanan ke supplier, penerimaan barang, hingga pencatatan pemakaian.
                        </p>
                    </div>

                    <!-- Alur Kerja -->
                    <div>
                        <h3 class="text-xl font-bold text-slate-800 dark:text-white mb-4 flex items-center gap-2">
                            <i class="bi bi-diagram-3-fill text-blue-600"></i> Alur Kerja (Workflow)
                        </h3>
                        <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm">
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 relative">
                                <!-- Line connector for desktop -->
                                <div class="hidden md:block absolute top-12 left-0 right-0 h-1 bg-slate-100 dark:bg-slate-700 -z-0 mx-12"></div>

                                <!-- Step 1 -->
                                <div class="relative z-10 flex flex-col items-center text-center">
                                    <div class="w-16 h-16 rounded-2xl bg-orange-100 text-orange-600 flex items-center justify-center text-2xl mb-4 shadow-sm border-4 border-white dark:border-slate-800">
                                        <i class="bi bi-cart-plus-fill"></i>
                                    </div>
                                    <h4 class="font-bold text-slate-700 dark:text-slate-200 mb-1">1. Request</h4>
                                    <p class="text-xs text-slate-500">Staf meminta barang yang dibutuhkan / habis.</p>
                                </div>

                                <!-- Step 2 -->
                                <div class="relative z-10 flex flex-col items-center text-center">
                                    <div class="w-16 h-16 rounded-2xl bg-blue-100 text-blue-600 flex items-center justify-center text-2xl mb-4 shadow-sm border-4 border-white dark:border-slate-800">
                                        <i class="bi bi-file-earmark-text-fill"></i>
                                    </div>
                                    <h4 class="font-bold text-slate-700 dark:text-slate-200 mb-1">2. Purchase Order</h4>
                                    <p class="text-xs text-slate-500">Admin membuat surat pesanan resmi ke Supplier.</p>
                                </div>

                                <!-- Step 3 -->
                                <div class="relative z-10 flex flex-col items-center text-center">
                                    <div class="w-16 h-16 rounded-2xl bg-green-100 text-green-600 flex items-center justify-center text-2xl mb-4 shadow-sm border-4 border-white dark:border-slate-800">
                                        <i class="bi bi-box-seam-fill"></i>
                                    </div>
                                    <h4 class="font-bold text-slate-700 dark:text-slate-200 mb-1">3. Penerimaan</h4>
                                    <p class="text-xs text-slate-500">Barang datang dicek dan masuk stok sistem.</p>
                                </div>

                                <!-- Step 4 -->
                                <div class="relative z-10 flex flex-col items-center text-center">
                                    <div class="w-16 h-16 rounded-2xl bg-purple-100 text-purple-600 flex items-center justify-center text-2xl mb-4 shadow-sm border-4 border-white dark:border-slate-800">
                                        <i class="bi bi-wrench-adjustable-circle-fill"></i>
                                    </div>
                                    <h4 class="font-bold text-slate-700 dark:text-slate-200 mb-1">4. Penggunaan</h4>
                                    <p class="text-xs text-slate-500">Barang dipakai untuk armada atau kantor.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Penjelasan Fitur -->
                     <div>
                        <h3 class="text-xl font-bold text-slate-800 dark:text-white mb-4 flex items-center gap-2">
                            <i class="bi bi-journal-text text-purple-600"></i> Kamus Istilah & Fitur
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                            <!-- Manajemen Inventaris (NEW) -->
                            <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200 dark:border-slate-700 col-span-1 md:col-span-2 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-slate-800 dark:to-slate-800">
                                <h4 class="font-bold text-slate-800 dark:text-white mb-3 flex items-center gap-2 text-lg">
                                    <i class="bi bi-building-gear text-indigo-600"></i> Manajemen Inventaris (Fisikal)
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <p class="text-sm text-slate-500 mb-2 font-bold">Struktur Penyimpanan:</p>
                                        <ul class="text-sm text-slate-500 space-y-1 list-disc list-inside">
                                            <li><strong>Ruangan:</strong> Lokasi utama (Gudang A, Ruang Meeting).</li>
                                            <li><strong>Lemari / Space:</strong> Tempat di dalam ruangan.</li>
                                            <li><strong>Rak / Space:</strong> Lokasi spesifik barang diletakkan.</li>
                                        </ul>
                                    </div>
                                    <div>
                                        <p class="text-sm text-slate-500 mb-2 font-bold">Fitur Cepat:</p>
                                        <ul class="text-sm text-slate-500 space-y-1">
                                            <li><i class="bi bi-plus-lg text-blue-600"></i> <strong>Quick Add Item:</strong> Klik tombol (+) di kartu Rak untuk menambah barang langsung tanpa berpindah halaman.</li>
                                            <li><i class="bi bi-eye text-blue-600"></i> <strong>Stacked View:</strong> Klik kartu Ruangan -> Lemari untuk melihat isinya secara bertingkat ke bawah.</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Request Order -->
                            <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200 dark:border-slate-700">
                                <h4 class="font-bold text-slate-800 dark:text-white mb-2 flex items-center gap-2">
                                    <i class="bi bi-cart-plus text-orange-500"></i> Request Order (Permintaan)
                                </h4>
                                <p class="text-sm text-slate-500 leading-relaxed">
                                    Menu ini digunakan jika Anda membutuhkan barang yang stoknya habis di gudang. Ajukan di sini agar Tim Purchasing tahu dan segera melakukan pembelian.
                                </p>
                            </div>

                             <!-- Purchase Order -->
                            <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200 dark:border-slate-700">
                                <h4 class="font-bold text-slate-800 dark:text-white mb-2 flex items-center gap-2">
                                    <i class="bi bi-file-earmark-text text-blue-500"></i> Purchase Order (PO)
                                </h4>
                                <p class="text-sm text-slate-500 leading-relaxed">
                                    Dokumen resmi "Janji Beli" kita ke penjual (Supplier). Jangan membeli barang dalam jumlah besar tanpa PO, karena PO ini adalah bukti kesepakatan harga dan barang.
                                </p>
                            </div>

                             <!-- Inventory -->
                            <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200 dark:border-slate-700">
                                <h4 class="font-bold text-slate-800 dark:text-white mb-2 flex items-center gap-2">
                                    <i class="bi bi-boxes text-indigo-500"></i> Inventory (Stok & Aset)
                                </h4>
                                <p class="text-sm text-slate-500 leading-relaxed">
                                    Tempat melihat sisa stok barang kita. 
                                    <br><strong>Stok Item:</strong> Barang habis pakai (Oli, Kertas, Sabun).
                                    <br><strong>Aset:</strong> Barang inventaris jangka panjang (Komputer, Meja, Mesin).
                                </p>
                            </div>

                             <!-- Receiving -->
                            <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200 dark:border-slate-700">
                                <h4 class="font-bold text-slate-800 dark:text-white mb-2 flex items-center gap-2">
                                    <i class="bi bi-box-arrow-in-down text-green-500"></i> Receiving (Barang Masuk)
                                </h4>
                                <p class="text-sm text-slate-500 leading-relaxed">
                                    Setiap ada paket/barang datang dari kurir atau toko, WAJIB dicatat di sini.
                                    Jika tidak dicatat, stok di komputer tidak akan bertambah walau barang fisiknya ada.
                                </p>
                            </div>

                             <!-- Implementation -->
                            <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200 dark:border-slate-700">
                                <h4 class="font-bold text-slate-800 dark:text-white mb-2 flex items-center gap-2">
                                    <i class="bi bi-box-arrow-right text-purple-500"></i> Penggunaan Barang
                                </h4>
                                <p class="text-sm text-slate-500 leading-relaxed">
                                    Saat mengambil barang dari gudang untuk dipakai (misal: Montir ambil sparepart, atau Admin ambil kertas), catat di sini. Ini akan mengurangi stok otomatis.
                                </p>
                            </div>

                             <!-- Suppliers -->
                            <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200 dark:border-slate-700">
                                <h4 class="font-bold text-slate-800 dark:text-white mb-2 flex items-center gap-2">
                                    <i class="bi bi-shop text-pink-500"></i> Suppliers (Vendor)
                                </h4>
                                <p class="text-sm text-slate-500 leading-relaxed">
                                    Daftar toko dan langganan tempat kita biasa belanja. Simpan nomor telepon dan alamat mereka di sini agar tidak hilang.
                                </p>
                            </div>
                            
                            <!-- Personalisasi (NEW) -->
                            <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200 dark:border-slate-700">
                                <h4 class="font-bold text-slate-800 dark:text-white mb-2 flex items-center gap-2">
                                    <i class="bi bi-palette text-teal-500"></i> Personalisasi Tampilan
                                </h4>
                                <p class="text-sm text-slate-500 leading-relaxed">
                                    Anda bisa mengubah tampilan aplikasi menjadi <strong>Mode Gelap (Dark Mode)</strong> agar nyaman di mata saat malam hari. Tombol pengaturan ada di bagian bawah Sidebar.
                                </p>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </main>
        
        <?php include 'components/sidebar_right.php'; ?>

    </div>
</body>
</html>
