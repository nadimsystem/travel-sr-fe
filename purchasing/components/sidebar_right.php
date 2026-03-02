<aside class="w-64 bg-slate-50 dark:bg-slate-900 border-l border-slate-200 dark:border-slate-700 hidden xl:flex flex-col shrink-0">
    <div class="p-6">
        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Quick Actions</h3>
        
        <div class="space-y-3">
            <!-- Request Order -->
            <a href="request.php" class="block p-4 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-md hover:-translate-y-1 transition-all group">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-300 flex items-center justify-center text-lg group-hover:scale-110 transition-transform">
                        <i class="bi bi-cart-plus-fill"></i>
                    </div>
                    <div>
                        <div class="font-bold text-slate-800 dark:text-white text-sm">Request Order</div>
                        <div class="text-[10px] text-slate-500">Ajukan pengadaan</div>
                    </div>
                </div>
            </a>

            <!-- Barang Masuk -->
            <a href="receiving.php" class="block p-4 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-md hover:-translate-y-1 transition-all group">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-full bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-300 flex items-center justify-center text-lg group-hover:scale-110 transition-transform">
                        <i class="bi bi-box-arrow-in-down"></i>
                    </div>
                    <div>
                        <div class="font-bold text-slate-800 dark:text-white text-sm">Barang Masuk</div>
                        <div class="text-[10px] text-slate-500">Terima dari PO / Langsung</div>
                    </div>
                </div>
            </a>

            <!-- Barang Keluar -->
            <a href="implementation.php" class="block p-4 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-md hover:-translate-y-1 transition-all group">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-full bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-300 flex items-center justify-center text-lg group-hover:scale-110 transition-transform">
                        <i class="bi bi-box-arrow-right"></i>
                    </div>
                    <div>
                        <div class="font-bold text-slate-800 dark:text-white text-sm">Barang Keluar</div>
                        <div class="text-[10px] text-slate-500">Penggunaan / Implementasi</div>
                    </div>
                </div>
            </a>
        </div>
    </div>
    
    <!-- Pending Tasks / Notifications Area -->
    <div class="p-6 pt-0 mt-auto">
        <div class="p-4 rounded-2xl bg-gradient-to-br from-slate-800 to-slate-900 text-white relative overflow-hidden">
            <div class="absolute -top-6 -right-6 w-24 h-24 bg-blue-500 opacity-20 rounded-full blur-2xl"></div>
             <h4 class="font-bold text-sm mb-2 relative z-10">Butuh Bantuan?</h4>
             <p class="text-[10px] text-slate-300 mb-3 relative z-10">Hubungi tim IT Support jika ada kendala sistem.</p>
             <a href="https://wa.me/6282282425862?text=bang%20ini%20dari%20aplikasi%20Purchasing%20Ada%20kendala" target="_blank" class="block w-full text-center py-2 bg-white/10 hover:bg-white/20 rounded-lg text-xs font-bold transition">
                 <i class="bi bi-whatsapp mr-1"></i> Kontak IT
             </a>
        </div>
    </div>
</aside>
