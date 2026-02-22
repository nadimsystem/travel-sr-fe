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

            <!-- Refund Options Tabs --> 
            <div class="px-6 pt-6 bg-slate-50 border-t border-slate-100">
                <div class="grid grid-cols-4 gap-2 mb-4">
                    <button onclick="setRefundType('otomatis')" id="btn-otomatis" class="refund-tab active px-3 py-2 rounded-lg text-xs font-bold border transition-all text-center">
                        Otomatis
                    </button>
                     <button onclick="setRefundType('manual')" id="btn-manual" class="refund-tab px-3 py-2 rounded-lg text-xs font-bold border transition-all text-center">
                        Manual
                    </button>
                     <button onclick="setRefundType('full')" id="btn-full" class="refund-tab px-3 py-2 rounded-lg text-xs font-bold border transition-all text-center">
                        Utuh
                    </button>
                     <button onclick="setRefundType('nol')" id="btn-nol" class="refund-tab px-3 py-2 rounded-lg text-xs font-bold border transition-all text-center">
                        Nol (Hangus)
                    </button>
                </div>
                
                <div class="bg-blue-50 text-blue-700 text-xs p-3 rounded-lg border border-blue-100 mb-4 flex gap-3 items-start" id="refund-desc">
                    <i class="bi bi-info-circle-fill mt-0.5"></i>
                    <div>
                        <span class="font-bold block mb-1">Mode Otomatis (Default)</span>
                        Sistem akan memotong 25% dari total pembayaran sebagai biaya administrasi, sisa 75% akan dikembalikan ke penumpang.
                    </div>
                </div>
            </div>

            <!-- Refund Form -->
            <div class="p-6 bg-slate-50 space-y-4 pt-0"> 
                <h3 class="font-bold text-slate-700 flex items-center gap-2"><i class="bi bi-wallet2"></i> Hitung Refund</h3>
                
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-1">Total Terbayar</label>
                        <input type="text" id="totalPaidDisplay" readonly class="w-full bg-slate-200 border border-slate-300 rounded-lg px-3 py-2 text-slate-600 font-bold outline-none cursor-not-allowed">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-1">Potongan / Cancel Fee</label>
                        <input type="number" id="adminFee" oninput="calculateRefund()" readonly class="w-full bg-slate-100 border border-slate-300 rounded-lg px-3 py-2 text-slate-800 font-bold outline-none">
                         <p class="text-[10px] text-slate-400 mt-1" id="fee-note"></p>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-1 text-green-600">Total Refund</label>
                        <input type="number" id="refundAmount" readonly class="w-full bg-green-50 border border-green-200 rounded-lg px-3 py-2 text-green-700 font-bold outline-none cursor-not-allowed">
                        <p class="text-[10px] text-slate-400 mt-1" id="refund-note"></p>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 mb-1">Rekening Tujuan Refund</label>
                    <input type="text" id="refundAccount" placeholder="Bank - No. Rek - Nama" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-slate-800 font-medium focus:ring-2 focus:ring-red-200 outline-none">
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
    
    <style>
        .refund-tab {
            background: white;
            color: #64748b;
            border-color: #e2e8f0;
        }
        .refund-tab:hover {
            background: #f1f5f9;
        }
        .refund-tab.active {
            background: #eff6ff;
            color: #2563eb;
            border-color: #bfdbfe;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }
    </style>

    <script>
        const API_URL = 'api.php';
        let bookingData = null;
        let currentRefundType = 'otomatis'; // Default
        let totalPaidAmount = 0;

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
            
            const total = parseFloat(b.totalPrice) || 0;
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

            // Calculate Paid Amount
            const dpAmount = parseFloat(b.downPaymentAmount) || 0;
            totalPaidAmount = 0;
            
            if (b.paymentStatus === 'Lunas' || b.validationStatus === 'Valid') {
                totalPaidAmount = total;
            } else if (b.paymentStatus === 'DP') {
                totalPaidAmount = dpAmount;
            }

            document.getElementById('totalPaidDisplay').value = 'Rp ' + totalPaidAmount.toLocaleString('id-ID');
            
            // Initialize Defaults
            setRefundType('otomatis');
        }

        function setRefundType(type) {
            currentRefundType = type;
            
            // Update UI Tabs
            document.querySelectorAll('.refund-tab').forEach(el => el.classList.remove('active'));
            document.getElementById('btn-' + type).classList.add('active');
            
            const descEl = document.getElementById('refund-desc');
            const feeInput = document.getElementById('adminFee');
            const feeNote = document.getElementById('fee-note');
            const refundNote = document.getElementById('refund-note');

            // Reset Input State
            feeInput.readOnly = true;
            feeInput.classList.add('bg-slate-100', 'cursor-not-allowed');
            feeInput.classList.remove('bg-white', 'focus:ring-2');

            // Logic Switch
            let fee = 0;
            let title = '';
            let desc = '';
            
            switch(type) {
                case 'otomatis':
                    fee = Math.floor(totalPaidAmount * 0.25);
                    title = 'Mode Otomatis (Potongan 25%)';
                    desc = 'Sistem memotong <b>25%</b> dari total uang masuk sebagai biaya administrasi/pembatalan. Sisanya <b>75%</b> dikembalikan ke penumpang.';
                    feeNote.innerText = '*Auto 25%';
                    refundNote.innerText = '*Sisa 75%';
                    break;
                case 'manual':
                    fee = ''; 
                    title = 'Mode Manual (Input Potongan)';
                    desc = 'Anda input nominal <b>UANG POTONGAN (Masuk PT)</b>. Sisa uang (Total Bayar - Potongan) otomatis menjadi nilai refund ke penumpang.';
                    feeNote.innerText = '*Input Potongan';
                    refundNote.innerText = '*Otomatis Hitung';
                    
                    // Enable Input
                    feeInput.readOnly = false;
                    feeInput.placeholder = '0';
                    feeInput.classList.remove('bg-slate-100', 'cursor-not-allowed');
                    feeInput.classList.add('bg-white', 'focus:ring-2');
                    feeInput.focus();
                    break;
                case 'full':
                    fee = 0;
                    title = 'Refund Utuh (Potongan 0)';
                    desc = 'Uang dikembalikan <b>UTUH 100%</b> ke penumpang. Tidak ada potongan sama sekali.';
                    feeNote.innerText = '*Tidak ada pot';
                    refundNote.innerText = '*Kembali Utuh';
                    break;
                case 'nol':
                    fee = totalPaidAmount;
                    title = 'Hangus (Potongan 100%)';
                    desc = 'Uang <b>HANGUS 100%</b> dan menjadi hak perusahaan. Tidak ada pengembalian dana ke penumpang.';
                    feeNote.innerText = '*Masuk semua ke PT';
                    refundNote.innerText = '*Tidak refund';
                    break;
            }

            // Update DOM
            descEl.innerHTML = `
                <i class="bi bi-info-circle-fill mt-0.5"></i>
                <div>
                    <span class="font-bold block mb-1">${title}</span>
                    <div class="text-slate-600">${desc}</div>
                </div>
            `;

            if (type !== 'manual') {
                feeInput.value = fee;
                calculateRefund();
            } else {
                feeInput.value = '';
                document.getElementById('refundAmount').value = '';
            }
        }

        function calculateRefund() {
            const feeVal = document.getElementById('adminFee').value;
            const fee = feeVal === '' ? 0 : parseFloat(feeVal);
            
            // Validate Max
            if (fee > totalPaidAmount) {
                // Optionally warn or cap
                // document.getElementById('adminFee').value = totalPaidAmount;
            }

            const refund = totalPaidAmount - fee;
            document.getElementById('refundAmount').value = refund < 0 ? 0 : refund;
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
            if (refundAmount === '' || refundAmount < 0) return Swal.fire('Error', 'Nominal refund tidak valid.', 'warning');

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
                        refundType: currentRefundType, // Add Type
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
