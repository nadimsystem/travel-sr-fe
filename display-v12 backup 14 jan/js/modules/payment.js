export const paymentMixin = {
    methods: {
        viewKtm(booking) {
            if (booking.ktmProof) {
                this.activeKtmImage = booking.ktmProof;
                this.activeBookingName = booking.passengerName;
                this.isKtmModalVisible = true;
            } else {
                Swal.fire('Info', 'Bukti KTM/Karyawan belum diupload.', 'info');
            }
        },

        openBillingShortcut(booking) {
            if (!booking) return;
            const name = booking.passengerName || '';
            if (!name) {
                Swal.fire('Error', 'Nama Penumpang Kosong', 'error');
                return;
            }
            const url = 'penagihan.php?search=' + encodeURIComponent(name);
            console.log('Opening billing shortcut for:', name);
            window.open(url, '_blank');
            this.closeValidationModal();
        },

        validatePaymentModal(b) {
            // Alias for compatibility with template
            this.openValidationModal(b);
        },

        openValidationModal(b) {
            this.validationData = b;
            this.isValidationModalVisible = true;
        },

        closeValidationModal() {
            this.isValidationModalVisible = false;
            this.validationData = null;
        },

        async confirmValidation(overrideAction = null) {
            if (!this.validationData) return;
            
            const bookingId = this.validationData.id;
            let action = overrideAction;
            if (!action) {
                action = document.querySelector('input[name="validationAction"]:checked')?.value;
            }
            
            if (!action) return Swal.fire('Pilih Aksi', 'Valid atau Tolak?', 'warning');
            
            let status = 'Valid';
            let bookingStatus = 'Confirmed';
            let paymentStatus = 'Lunas';
            
            if (action === 'Valid') {
                if (this.validationData.paymentMethod === 'DP') {
                    paymentStatus = 'DP';
                }
            } else {
                status = 'Ditolak';
                bookingStatus = 'Pending'; // Reset? Or Cancelled?
                paymentStatus = 'Menunggu Validasi';
            }
            
            this.isLoading = true;
            const res = await this.postToApi('update_payment_status', {
                id: bookingId,
                validationStatus: status,
                paymentStatus: paymentStatus
            });
            this.isLoading = false;
            
            if (res.status === 'success') {
                await this.loadData();
                this.closeValidationModal();
                this.showToast(`Validasi: ${status}`, status==='Valid'?'success':'error');
                
                // Refresh Detail Modal if open and matches this booking
                if (this.isDetailModalVisible && this.detailModalData && this.detailModalData.id == bookingId) {
                    const freshBooking = this.bookings.find(b => b.id == bookingId);
                    if (freshBooking) this.detailModalData = freshBooking;
                }
            } else {
                Swal.fire('Gagal', res.message, 'error');
            }
        },

        async loadPaymentHistory(bookingId) {
            this.paymentHistory = [];
            const res = await this.postToApi('get_payment_history', { bookingId });
            if (res.status === 'success') {
                this.paymentHistory = res.data;
            }
        },

        openAddPaymentModal(booking) {
            this.activePaymentBooking = booking;
            this.addPaymentForm = {
                amount: 0,
                payment_method: 'Cash',
                payment_location: '',
                payment_receiver: '',
                notes: ''
            };
            
            // Auto fill remaining amount
            const paid = booking.downPaymentAmount || 0;
            const total = booking.totalPrice || 0;
            this.addPaymentForm.amount = total - paid;
            
            // Logic for pre-filling location/receiver based on last usage?
            // (Skipped for brevity, can add later)
            
            this.isAddPaymentModalVisible = true;
        },

        closeAddPaymentModal() {
            this.isAddPaymentModalVisible = false;
        },

        async handlePaymentProofUpload(event) {
            const file = event.target.files[0];
            if (!file) return;
            
            const reader = new FileReader();
            reader.onload = (e) => {
                this.addPaymentForm.payment_proof = e.target.result; // Base64
            };
            reader.readAsDataURL(file);
        },

        async submitAddPayment() {
            if (!this.activePaymentBooking) return;
            if (this.addPaymentForm.amount <= 0) return Swal.fire('Error', 'Jumlah pembayaran harus > 0', 'error');
            
            this.isLoading = true;
            const payload = {
                bookingId: this.activePaymentBooking.id,
                ...this.addPaymentForm
            };
            
            const res = await this.postToApi('add_payment', payload);
            this.isLoading = false;
            
            if (res.status === 'success') {
                this.showToast('Pembayaran Berhasil Ditambahkan');
                this.closeAddPaymentModal();
                this.loadData();
                // Optionally reload detail modal if open
                if (this.isDetailModalVisible && this.detailModalData && this.detailModalData.id == this.activePaymentBooking.id) {
                     this.loadPaymentHistory(this.activePaymentBooking.id);
                }
            } else {
                Swal.fire("Gagal", res.message, 'error');
            }
        }
    }
};
