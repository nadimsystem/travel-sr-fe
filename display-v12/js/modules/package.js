export const packageMixin = {
    computed: {
        filteredPackages() {
            if (!this.packageSearch) return this.packages || [];
            const q = this.packageSearch.toLowerCase();
            return (this.packages || []).filter(p => 
                (p.receiptNumber && p.receiptNumber.toLowerCase().includes(q)) ||
                (p.senderName && p.senderName.toLowerCase().includes(q)) ||
                (p.receiverName && p.receiverName.toLowerCase().includes(q)) ||
                (p.senderPhone && p.senderPhone.includes(q))
            );
        },
    },
    watch: {
        packageView(val) {
            if (val === 'history') {
                this.loadPackages();
            }
        }
    },
    methods: {
        async loadPackages() {
            const res = await this.postToApi('get_packages');
            if (res.packages) {
                this.packages = res.packages;
            }
        },

        calculatePackagePrice() {
            const tarifs = {
                'Pool to Pool': {
                    'Padang - Bukittinggi': { 'Surat / Dokumen': 30000, 'Barang Kardus': 30000, 'Barang Lainnya / Big Size': 100000 },
                    'Padang - Payakumbuh': { 'Surat / Dokumen': 40000, 'Barang Kardus': 40000, 'Barang Lainnya / Big Size': 130000 },
                    'Bukittinggi - Payakumbuh': { 'Surat / Dokumen': 15000, 'Barang Kardus': 15000, 'Barang Lainnya / Big Size': 50000 },
                    'Payakumbuh - Bukittinggi': { 'Surat / Dokumen': 15000, 'Barang Kardus': 15000, 'Barang Lainnya / Big Size': 50000 }
                },
                'Antar Jemput Alamat': {
                    'Padang - Bukittinggi': { 'Surat / Dokumen': 60000, 'Barang Kardus': 60000, 'Barang Lainnya / Big Size': 120000 },
                    'Padang - Payakumbuh': { 'Surat / Dokumen': 70000, 'Barang Kardus': 70000, 'Barang Lainnya / Big Size': 150000 },
                    'Bukittinggi - Payakumbuh': { 'Surat / Dokumen': 25000, 'Barang Kardus': 25000, 'Barang Lainnya / Big Size': 70000 },
                    'Payakumbuh - Bukittinggi': { 'Surat / Dokumen': 25000, 'Barang Kardus': 25000, 'Barang Lainnya / Big Size': 70000 }
                }
            };
            
            const cat = this.packageForm.category === 'Pool to Pool' ? 'Pool to Pool' : 'Antar Jemput Alamat';
            const route = this.packageForm.route;
            const type = this.packageForm.itemType;
            
            if (tarifs[cat] && tarifs[cat][route] && tarifs[cat][route][type]) {
                this.packageForm.price = tarifs[cat][route][type];
            } else {
                this.packageForm.price = 0;
            }
        },

        async savePackage() {
            if (!this.packageForm.senderName || !this.packageForm.receiverName) {
                return Swal.fire('Error', 'Nama Pengirim dan Penerima wajib diisi.', 'error');
            }

            // Include Admin Name in creation (Safe Fallback)
            const adminName = (this.user && this.user.name) ? this.user.name : (this.currentUser && this.currentUser.name ? this.currentUser.name : 'Admin');
            this.packageForm.adminName = adminName;
            
            // Ensure bookingDate is set
            if (!this.packageForm.bookingDate) {
                this.packageForm.bookingDate = new Date().toISOString().slice(0, 10);
            }

            // Sanitize Form Data (Empty strings instead of null/undefined)
            const safeData = { ...this.packageForm };
            Object.keys(safeData).forEach(key => {
                if (safeData[key] === null || safeData[key] === undefined) safeData[key] = '';
            });

            this.isLoading = true;
            const res = await this.postToApi('create_package', { data: safeData });
            this.isLoading = false;

            if (res.status === 'success') {
                this.showToast('Booking Paket Berhasil! Resi: ' + res.receiptNumber, 'success');
                this.packageView = 'history';
                this.loadPackages();
                // Reset Form
                this.packageForm.senderName = '';
                this.packageForm.senderPhone = '';
                this.packageForm.receiverName = '';
                this.packageForm.receiverPhone = '';
                this.packageForm.itemDescription = '';
                this.packageForm.mapLink = '';
            } else {
                Swal.fire('Gagal', res.message, 'error');
            }
        },

        async openTrackingModal(p) {
            this.activePackage = p;
            this.activePackageLogs = []; // Reset
            this.isTrackingModalVisible = true;
            
            // Fetch Details & Logs
            const res = await this.postToApi('get_package_details', { id: p.id });
            if (res.status === 'success') {
                this.activePackage = res.package;
                this.activePackageLogs = res.logs;
            }
        },

        closeTrackingModal() {
            this.isTrackingModalVisible = false;
        },

        async openStatusUpdateModal(p) {
            this.activePackage = p;
            this.activePackageLogs = []; // Reset
            this.statusForm = {
                id: p.id,
                status: p.status,
                location: '',
                description: '',
                adminName: this.currentUser?.name || 'Admin'
            };
            this.isStatusModalVisible = true;

            // Fetch Details & Logs for context
            const res = await this.postToApi('get_package_details', { id: p.id });
            if (res.status === 'success') {
                this.activePackage = res.package;
                this.activePackageLogs = res.logs;
            }
        },

        async saveStatusUpdate() {
            if (!this.statusForm.description) {
                 this.statusForm.description = 'Update status menjadi ' + this.statusForm.status;
            }
            
            this.isLoading = true;
            const res = await this.postToApi('update_package_status', this.statusForm);
            this.isLoading = false;
            
            if (res.status === 'success') {
                Swal.fire({
                    title: 'Berhasil!',
                    text: 'Status paket berhasil diperbarui.',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                });
                this.isStatusModalVisible = false;
                this.loadPackages(); // Refresh List
            } else {
                Swal.fire('Gagal', res.message, 'error');
            }
        },

        printReceipt(p) {
            // Receipt Layout (Thermo Printer Friendly 58mm/80mm Style)
            const receiptHtml = `
                <div style="font-family: 'Courier New', monospace; width: 300px; padding: 10px; font-size: 12px;">
                    <div style="text-align: center; margin-bottom: 10px;">
                        <h3 style="margin:0; font-weight:bold; font-size:16px;">SUTAN RAYA</h3>
                        <p style="margin:0; font-size:10px;">Jasa Pengiriman & Travel</p>
                        <hr style="border-top: 1px dashed #000; margin: 5px 0;">
                        <h4 style="margin:0;">RESI PENGIRIMAN</h4>
                        <h2 style="margin:5px 0; letter-spacing: 1px;">${p.receiptNumber || 'PK-'+p.id}</h2>
                        <!-- Barcode Container -->
                        <div style="margin: 5px 0;"><img id="barcode-${p.id}" /></div>
                    </div>
                    
                    <div style="margin-bottom: 10px;">
                        <table style="width: 100%; font-size: 12px;">
                            <tr><td style="width: 60px;">Tgl</td><td>: ${this.formatDate(p.bookingDate)}</td></tr>
                            <tr><td>Asal</td><td>: ${p.route.split(' - ')[0]}</td></tr>
                            <tr><td>Tujuan</td><td>: ${p.route.split(' - ')[1]}</td></tr>
                        </table>
                    </div>
                    
                    <hr style="border-top: 1px dashed #000; margin: 5px 0;">
                    
                    <div style="margin-bottom: 10px;">
                        <div style="font-weight:bold;">PENGIRIM:</div>
                        <div>${p.senderName}</div>
                        <div>${p.senderPhone}</div>
                    </div>
                    
                    <div style="margin-bottom: 10px;">
                        <div style="font-weight:bold;">PENERIMA:</div>
                        <div>${p.receiverName}</div>
                        <div>${p.receiverPhone}</div>
                        <div style="font-size:10px; margin-top:2px;">${p.category === 'Antar Jemput Alamat' ? (p.dropoffAddress || '-') : 'Ambil di Pool'}</div>
                    </div>
                    
                    <hr style="border-top: 1px dashed #000; margin: 5px 0;">
                    
                    <div style="margin-bottom: 10px;">
                        <div style="font-weight:bold;">BARANG:</div>
                        <div>${p.itemType}</div>
                        <div style="font-style:italic;">${p.itemDescription}</div>
                    </div>
                    
                    <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 14px; margin-top: 10px;">
                        <span>TOTAL:</span>
                        <span>${this.formatRupiah(p.price)}</span>
                    </div>
                    <div style="text-align: right; font-size: 10px;">${p.paymentStatus} (${p.paymentMethod})</div>
                    
                    <hr style="border-top: 1px dashed #000; margin: 15px 0;">
                    <div style="text-align: center; font-size: 10px;">
                        <p>Simpan resi ini untuk pengambilan.<br>Cek resi di sutanraya.com</p>
                        <p style="margin-top:5px;">Terima Kasih</p>
                    </div>
                </div>
            `;
            
            // Create iframe for printing
            const printFrame = document.createElement('iframe');
            printFrame.style.position = 'absolute';
            printFrame.style.top = '-1000px';
            document.body.appendChild(printFrame);
            
            printFrame.contentDocument.write('<html><head><title>Print Receipt</title></head><body>');
            printFrame.contentDocument.write(receiptHtml);
            
            // Generate Barcode inside iframe
            const script = printFrame.contentDocument.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js';
            script.onload = () => {
                const win = printFrame.contentWindow;
                win.JsBarcode(`#barcode-${p.id}`, p.receiptNumber || 'PK-'+p.id, {
                    format: "CODE128",
                    lineColor: "#000",
                    width: 2,
                    height: 40,
                    displayValue: false
                });
                
                setTimeout(() => {
                    win.focus();
                    win.print();
                    setTimeout(() => {
                        document.body.removeChild(printFrame);
                    }, 1000);
                }, 500);
            };
            printFrame.contentDocument.head.appendChild(script);
            
            printFrame.contentDocument.write('</body></html>');
            printFrame.contentDocument.close();
        },
    }
};
