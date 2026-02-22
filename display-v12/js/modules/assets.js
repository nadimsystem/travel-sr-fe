export const assetsMixin = {
    methods: {
        // --- VEHICLE ---
        openVehicleModal(f = null) {
            if (f) {
                this.vehicleModal = { mode: "edit", data: { ...f } };
            } else {
                this.vehicleModal = { mode: "add", data: { id: '', name: '', plate: '', capacity: 7, status: 'Tersedia', icon: 'bi-truck-front-fill' } };
            }
            this.isVehicleModalVisible = true;
        },
        closeVehicleModal() {
            this.isVehicleModalVisible = false;
        },
        async saveVehicle() {
            if(!this.vehicleModal.data.name || !this.vehicleModal.data.plate) return Swal.fire('Lengkapi Data', 'Nama dan Plat nomor wajib diisi', 'warning');
            
            const endpoint = this.vehicleModal.mode === 'add' ? 'create_fleet' : 'update_fleet';
            const actionText = this.vehicleModal.mode === 'add' ? 'Ditambahkan' : 'Diupdate';

            if(this.vehicleModal.mode === 'add') this.vehicleModal.data.id = Date.now();

            this.isLoading = true;
            const res = await this.postToApi(endpoint, { data: this.vehicleModal.data });
            this.isLoading = false;

            if(res.status === 'success') {
                this.isVehicleModalVisible = false;
                this.loadData();
                this.showToast(`Armada Berhasil ${actionText}`);
            } else {
                Swal.fire('Gagal', res.message, 'error');
            }
        },
        async deleteVehicle(id) {
            const res = await Swal.fire({
                title: 'Hapus Armada?',
                text: "Data armada ini akan dihapus permanen.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Hapus'
            });
            
            if(!res.isConfirmed) return;
            
            const apiRes = await this.postToApi('delete_fleet', { id });
            if(apiRes.status === 'success') {
                this.loadData();
                this.showToast('Armada Dihapus', 'success');
            }
        },

        // --- DRIVER ---
        openDriverModal(d = null) {
            if (d) {
                this.driverModal = { mode: "edit", data: { ...d } };
            } else {
                this.driverModal = { mode: "add", data: { id: '', name: '', phone: '', licenseType: 'A Umum', status: 'Standby' } };
            }
            this.isDriverModalVisible = true;
        },
        closeDriverModal() {
            this.isDriverModalVisible = false;
        },
        async saveDriver() {
            if(!this.driverModal.data.name) return Swal.fire('Nama Kosong', 'Nama supir wajib diisi', 'warning');

            const endpoint = this.driverModal.mode === 'add' ? 'create_driver' : 'update_driver';
             const actionText = this.driverModal.mode === 'add' ? 'Ditambahkan' : 'Diupdate';

            if(this.driverModal.mode === 'add') this.driverModal.data.id = Date.now();
            
            this.isLoading = true;
            const res = await this.postToApi(endpoint, { data: this.driverModal.data });
            this.isLoading = false;

            if(res.status === 'success') {
                this.isDriverModalVisible = false;
                this.loadData();
                this.showToast(`Supir Berhasil ${actionText}`);
            } else {
                Swal.fire('Gagal', res.message, 'error');
            }
        },
        async deleteDriver(id) {
             const res = await Swal.fire({
                title: 'Hapus Supir?',
                text: "Data supir ini akan dihapus permanen.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Hapus'
            });
            
            if(!res.isConfirmed) return;

            const apiRes = await this.postToApi('delete_driver', { id });
            if(apiRes.status === 'success') {
                this.loadData();
                this.showToast('Supir Dihapus', 'success');
            }
        },

        // --- ROUTE ---
        openRouteModal(r = null) {
            this.routeModal.mode = r ? 'edit' : 'add';
            if (r) {
                // Copy data to routeForm
                const data = JSON.parse(JSON.stringify(r));
                this.routeForm = {
                    id: data.id,
                    origin: data.origin,
                    destination: data.destination,
                    schedulesInput: (data.schedules || []).join(', '),
                    prices: data.prices || { umum: 0, pelajar: 0, dropping: 0, carter: 0 }
                };
            } else {
                this.routeForm = { id: '', origin: '', destination: '', schedulesInput: '', prices: { umum: 0, pelajar: 0, dropping: 0, carter: 0 } };
            }
            this.isRouteModalVisible = true; 
        },
        async saveRoute() {
            const f = this.routeForm;
            if (!f.origin || !f.destination) return Swal.fire('Lengkapi Data', 'Asal dan Tujuan wajib diisi', 'warning');
            
            if (this.routeModal.mode === 'add') {
                const getCode = (name) => {
                    const n = name.toLowerCase();
                    if(n.includes('padang panjang')) return 'PDP';
                    if(n.includes('padang')) return 'PDG';
                    if(n.includes('bukittinggi')) return 'BKT';
                    if(n.includes('payakumbuh')) return 'PYK';
                    if(n.includes('pekanbaru')) return 'PKU';
                    if(n.includes('solok')) return 'SLK';
                    if(n.includes('sawahlunto')) return 'SWL';
                    if(n.includes('batusangkar')) return 'BSK';
                    if(n.includes('pariaman')) return 'PRM';
                    return name.substring(0,3).toUpperCase();
                };
                let newId = `${getCode(f.origin)}-${getCode(f.destination)}`;
                
                if (this.routeConfig.some(r => r.id === newId)) {
                    let counter = 2;
                    while (this.routeConfig.some(r => r.id === `${newId}-${counter}`)) {
                        counter++;
                    }
                    newId = `${newId}-${counter}`;
                }
                f.id = newId;
            }

            const schedules = f.schedulesInput.split(',').map(s => s.trim()).filter(s => s);
            
            const payload = {
                id: f.id,
                origin: f.origin,
                destination: f.destination,
                schedules: schedules,
                prices: f.prices
            };

            const res = await this.postToApi('save_route', payload);
            if(res.status === 'success') {
                this.showToast("Rute berhasil disimpan!");
                this.isRouteModalVisible = false;
                this.loadData(); 
            } else {
                Swal.fire("Gagal", res.message, 'error');
            }
        },

        async deleteRoute(id) {
            if(!confirm("Yakin ingin menghapus rute ini?")) return;
            const res = await this.postToApi('delete_route', { id: id });
            if(res.status === 'success') {
                this.showToast("Rute berhasil dihapus!");
                this.loadData();
            } else {
                Swal.fire("Gagal", res.message, 'error');
            }
        },
    }
};
