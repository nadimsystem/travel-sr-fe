<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proses Pembatalan - Travel SR</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }</style>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-4">

    <div class="max-w-xl w-full bg-white rounded-2xl shadow-xl overflow-hidden border border-slate-100" id="app">
        <!-- Header -->
        <div class="bg-red-50 p-6 border-b border-red-100 flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center text-red-600 text-xl font-bold">
                <i class="bi bi-x-octagon-fill"></i>
            </div>
            <div>
                <h1 class="text-xl font-bold text-slate-800">Pembatalan Booking</h1>
                <p class="text-sm text-slate-500">Proses refund dan arsip data.</p>
            </div>
        </div>

        <div id="loading" class="p-12 text-center">
            <div class="animate-spin w-8 h-8 border-4 border-red-500 border-t-transparent rounded-full mx-auto mb-3"></div>
            <p class="text-slate-400 text-sm">Memuat data booking...</p>
        </div>

        <div id="content" class="hidden">
            <!-- Booking Info -->
            <div class="p-6 bg-white space-y-4">
                <div class="flex justify-between items-start">
                    <div>
                        <div class="text-xs uppercase font-bold text-slate-400 mb-1">Penumpang</div>
                        <div class="font-bold text-slate-800 text-lg" id="pName">-</div>
                        <div class="text-sm text-slate-500" id="pPhone">-</div>
                    </div>
                    <div class="text-right">
                        <div class="text-xs uppercase font-bold text-slate-400 mb-1">Rute</div>
                        <div class="font-bold text-slate-800" id="pRoute">-</div>
                        <div class="text-sm text-slate-500" id="pDateTime">-</div>
                    </div>
                </div>

                <div class="bg-slate-50 rounded-xl p-4 border border-slate-100 flex justify-between items-center">
                    <div>
                        <div class="text-xs text-slate-500">Total Biaya Awal</div>
                        <div class="font-mono font-bold text-slate-700" id="pTotal">Rp 0</div>
                    </div>
                    <div class="text-right">
                         <div class="text-xs text-slate-500">Status Bayar</div>
                         <span class="px-2 py-1 rounded text-xs font-bold" id="pStatusBadge"></span>
                    </div>
                </div>
            </div>

            <!-- Refund Form -->
            <div class="p-6 bg-slate-50 border-t border-slate-100 space-y-4">
                <h3 class="font-bold text-slate-700 flex items-center gap-2"><i class="bi bi-wallet2"></i> Hitung Refund</h3>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-1">Refund (75%)</label>
                        <input type="number" id="refundAmount" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-slate-800 font-bold focus:ring-2 focus:ring-red-200 outline-none">
                        <p class="text-[10px] text-slate-400 mt-1">*Otomatis dihitung 75%</p>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-1">Rekening Tujuan</label>
                        <input type="text" id="refundAccount" placeholder="Bank - No. Rek - Nama" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-slate-800 font-medium focus:ring-2 focus:ring-red-200 outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 mb-1">Alasan Pembatalan</label>
                    <textarea id="reason" rows="2" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-slate-800 text-sm focus:ring-2 focus:ring-red-200 outline-none" placeholder="Contoh: Penumpang sakit, Double booking, dll..."></textarea>
                </div>
            </div>

            <!-- Actions -->
            <div class="p-6 bg-white border-t border-slate-100 flex justify-between items-center">
                <button onclick="window.close()" class="text-slate-500 font-bold text-sm hover:text-slate-800 px-4">Batal / Tutup</button>
                <button onclick="processCancellation()" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2.5 rounded-xl font-bold shadow-lg shadow-red-200 transition-all transform active:scale-95 flex items-center gap-2">
                    <i class="bi bi-trash3-fill"></i> Proses Pembatalan
                </button>
            </div>
        </div>
        
        <div id="error" class="hidden p-8 text-center">
            <div class="text-red-500 text-5xl mb-4"><i class="bi bi-x-circle"></i></div>
            <h2 class="text-xl font-bold text-slate-800">Booking Tidak Ditemukan</h2>
            <p class="text-slate-500 mt-2">Data booking ini mungkin sudah dihapus atau ID salah.</p>
        </div>
    </div>

    <script>
        const API_URL = 'api.php';
        let bookingData = null;

        // Get ID from URL
        const urlParams = new URLSearchParams(window.location.search);
        const bookingId = urlParams.get('id');

        async function loadData() {
            if(!bookingId) {
                showError();
                return;
            }

            try {
                const res = await fetch(`${API_URL}?action=get_booking_details&id=${bookingId}`);
                const data = await res.json();
                
                if(data.status === 'success' && data.booking) {
                    bookingData = data.booking;
                    renderData();
                } else {
                    showError();
                }
            } catch(e) {
                console.error(e);
                showError();
            }
        }

        function renderData() {
            document.getElementById('loading').classList.add('hidden');
            document.getElementById('content').classList.remove('hidden');

            const b = bookingData;
            document.getElementById('pName').innerText = b.passengerName;
            document.getElementById('pPhone').innerText = b.passengerPhone;
            document.getElementById('pRoute').innerText = b.routeName || b.routeId;
            document.getElementById('pDateTime').innerText = `${b.date} • ${b.time}`;
            
            const total = parseFloat(b.totalPrice) * parseInt(b.seatCount) || 0;
            document.getElementById('pTotal').innerText = 'Rp ' + total.toLocaleString('id-ID');

            // Badge Logic
            const badge = document.getElementById('pStatusBadge');
            let statusText = b.paymentStatus;
            let statusClass = 'px-2 py-1 rounded text-xs font-bold bg-orange-100 text-orange-700';
            
            // Trust Validation Status if Valid
            if (b.validationStatus === 'Valid') {
                statusText = 'Lunas (Valid)'; 
                if (b.paymentStatus === 'Lunas') statusText = 'Lunas';
                statusClass = 'px-2 py-1 rounded text-xs font-bold bg-green-100 text-green-700';
            } else if (b.paymentStatus === 'Lunas') {
                 statusClass = 'px-2 py-1 rounded text-xs font-bold bg-green-100 text-green-700';
            }

            badge.innerText = statusText;
            badge.className = statusClass;

            // Auto Calculate Refund (75%)
            const dpAmount = parseFloat(b.downPaymentAmount) || 0;
            let paidAmount = 0;
            
            // If Valid -> Assume Full Pay unless DP specified? 
            // Usually Valid means Lunas. If just DP, validationStatus might be Valid too?
            // Safer: If Lunas OR Valid -> Use Total. If DP -> Use DP.
            
            if (b.paymentStatus === 'Lunas' || b.validationStatus === 'Valid') {
                paidAmount = total;
            } else if (b.paymentStatus === 'DP') {
                paidAmount = dpAmount;
            }
            
            const refund = Math.floor(paidAmount * 0.75);
            document.getElementById('refundAmount').value = refund;
        }

        function showError() {
            document.getElementById('loading').classList.add('hidden');
            document.getElementById('error').classList.remove('hidden');
        }

        async function processCancellation() {
            const refundAmount = document.getElementById('refundAmount').value;
            const refundAccount = document.getElementById('refundAccount').value;
            const reason = document.getElementById('reason').value;

            if (!reason) return Swal.fire('Error', 'Mohon isi alasan pembatalan.', 'warning');

            const confirm = await Swal.fire({
                title: 'Konfirmasi Batal',
                text: "Data akan dipindahkan ke arsip pembatalan dan dihapus dari jadwal aktif. Lanjutkan?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Ya, Proses!',
                cancelButtonText: 'Batal'
            });

            if (!confirm.isConfirmed) return;

            try {
                // Show loading
                Swal.fire({title: 'Memproses...', didOpen: () => Swal.showLoading()});

                const payload = {
                    action: 'process_cancellation',
                    data: {
                        id: bookingId,
                        reason: reason,
                        refundAccount: refundAccount,
                        refundAmount: refundAmount,
                        cancelledBy: 'Dispatcher' 
                    }
                };

                const res = await fetch(API_URL, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(payload)
                });
                const result = await res.json();

                if (result.status === 'success') {
                    await Swal.fire('Berhasil!', 'Booking telah dibatalkan.', 'success');
                    // Refresh opener if exists
                    if (window.opener && !window.opener.closed) {
                        try { window.opener.app.loadData(); } catch(e){}
                    }
                    window.close();
                } else {
                    Swal.fire('Gagal', result.message, 'error');
                }

            } catch (e) {
                Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error');
            }
        }

        loadData();
    </script>
</body>
</html>
