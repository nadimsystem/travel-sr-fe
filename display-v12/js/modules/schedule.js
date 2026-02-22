export const scheduleMixin = {
    methods: {
        isChartered(fleetId, driverId, date) {
            return this.trips.find(t => {
                if (!t.passengers) return false;
                const sameFleet = t.fleet && t.fleet.id === fleetId;
                const sameDriver = t.driver && t.driver.id === driverId;
                
                if (!sameFleet && !sameDriver) return false;
                
                return t.passengers.some(p => {
                    if (p.serviceType !== 'Carter' && p.serviceType !== 'Dropping') return false;
                    
                    const start = new Date(p.date);
                    const duration = parseInt(p.duration) || 1;
                    const end = new Date(start);
                    end.setDate(end.getDate() + duration - 1);
                    
                    const check = new Date(date);
                    return check >= start && check <= end;
                });
            });
        },

        getAssignment(routeId, time, date = null) {
            if (!date) date = this.manifestDate;
            const normTime = (t) => t ? String(t).replace('.', ':').trim().substring(0, 5) : '';
            const gTime = normTime(time);
            
            console.log(`Getting Assignment for Route: ${routeId}, Time: ${time} (${gTime})`);
            console.log('Defaults available:', this.scheduleDefaults.length);
            this.scheduleDefaults.forEach(d => console.log(`Def: ${d.routeId} at ${d.time} -> Fleet: ${d.fleetId}, Driver: ${d.driverId}`));

            // 1. Check Specific Trip (Override)
            const specificTrip = this.trips.find(t => {
                const tDate = t.date || (t.passengers && t.passengers[0] ? t.passengers[0].date : null);
                if (!tDate) return false;
                
                const d1 = new Date(tDate).toLocaleDateString('sv');
                const d2 = new Date(date).toLocaleDateString('sv');
                const timeMatch = normTime(t.time) === gTime;
                
                let tRouteId = '';
                if (t.routeConfig && typeof t.routeConfig === 'object') tRouteId = t.routeConfig.id;
                else if (t.routeConfig) tRouteId = t.routeConfig; 
                
                const routeMatch = String(tRouteId).trim() == String(routeId).trim();
                return d1 === d2 && timeMatch && routeMatch;
            });
            
            // 2. Check Default Schedule
            const def = this.scheduleDefaults.find(d => String(d.routeId).trim() == String(routeId).trim() && normTime(d.time) === gTime);
            
            // 3. Logic: Specific Trip Priority > Default > Null
            if (specificTrip) {
                // If specific trip has explicit assignment, use it
                if (specificTrip.fleet && specificTrip.driver) {
                    return { ...specificTrip, type: 'Specific' };
                }
                
                // If specific trip exists but is Pending (no fleet/driver), try to fill with Default
                if (def) {
                    const f = this.fleet.find(f => String(f.id).trim() == String(def.fleetId).trim());
                    const d = this.drivers.find(d => String(d.id).trim() == String(def.driverId).trim());
                    if (f && d) {
                        // Return specific trip but with Default assignment overlay
                        return { ...specificTrip, fleet: f, driver: d, type: 'Default' };
                    }
                }
                
                // If no default, return pending specific trip
                return { ...specificTrip, type: 'Specific' };
            }

            // No specific trip, return pure Default
            if (def) {
                const f = this.fleet.find(f => String(f.id).trim() == String(def.fleetId).trim());
                const d = this.drivers.find(d => String(d.id).trim() == String(def.driverId).trim());

                if (!f || !d) return null;

                const conflictTrip = this.isChartered(def.fleetId, def.driverId, date);
                if (conflictTrip) {
                    return { status: 'Conflict', fleet: f, driver: d, conflictWith: conflictTrip, type: 'Default' };
                }
                
                return { status: 'Scheduled', fleet: f, driver: d, type: 'Default' };
            }
            
            return null;
        },

        openScheduleModal(route, time, date, assignment = null, batchNumber = 1) {
            this.scheduleForm = {
                id: assignment && assignment.type === 'Specific' ? assignment.tripId : null,
                route: route,
                time: time,
                targetDate: date || this.manifestDate,
                fleetId: assignment ? assignment.fleet?.id : '',
                driverId: assignment ? assignment.driver?.id : '',
                isDefault: assignment && assignment.type === 'Default',
                batchNumber: batchNumber
            };
            this.isScheduleModalVisible = true;
        },
        
        async saveScheduleAssignment() {
            let { route, time, fleetId, driverId, isDefault, batchNumber } = this.scheduleForm;
            if (!fleetId || !driverId) return Swal.fire('Data Kurang', 'Pilih Armada dan Supir!', 'warning');
            
            if (batchNumber > 1) isDefault = false;

            const f = this.fleet.find(x => x.id == fleetId);
            const d = this.drivers.find(x => x.id == driverId);
            
            this.isLoading = true;

            try {

            if (isDefault) {
                const conflict = this.scheduleDefaults.find(d => 
                    (d.fleetId == fleetId || d.driverId == driverId) && 
                    d.time === time &&
                    d.routeId != route.id 
                );
                
                if (conflict) {
                    const conflictRoute = this.routeConfig.find(r => r.id == conflict.routeId);
                    const rName = conflictRoute ? `${conflictRoute.origin}-${conflictRoute.destination}` : conflict.routeId;
                    const result = await Swal.fire({
                        title: 'Konflik Default',
                        text: `Armada/Supir ini sudah menjadi default di rute ${rName} pada jam ${time}. Yakin ingin menimpa/menggunakan ganda?`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Lanjutkan'
                    });
                    if (!result.isConfirmed) return;
                }

                const res = await this.postToApi('save_schedule_default', {
                    routeId: route.id, time: time, fleetId: fleetId, driverId: driverId, batchNumber: batchNumber
                });
                
                if(res.status === 'success') {
                    // Update local cache
                    const newDef = { id: Date.now(), routeId: route.id, time: time, fleetId: fleetId, driverId: driverId, batchNumber: batchNumber };
                    // Remove old default for this batch/route/time
                    const existingIdx = this.scheduleDefaults.findIndex(s => String(s.routeId) == String(route.id) && s.time == time && (s.batchNumber||1) == batchNumber);
                    
                    if (existingIdx >= 0) this.scheduleDefaults[existingIdx] = newDef;
                    else this.scheduleDefaults.push(newDef);
                    
                    this.isScheduleModalVisible = false;
                    this.showToast('Jadwal Default Disimpan');
                    this.loadData();
                } else {
                    Swal.fire('Gagal', res.message, 'error');
                }
            } else {
                    // Logic: Save as specific Trip
                    const tripId = this.scheduleForm.id || (Date.now().toString()); // Temp ID if new
                    const existingTrip = this.trips.find(t => t.id === tripId);
                    
                    const tripData = {
                        id: String(tripId),
                        routeConfig: route,
                        fleet: f,
                        driver: d,
                        passengers: existingTrip ? existingTrip.passengers : [],
                        status: 'Scheduled',
                        date: this.scheduleForm.targetDate || this.manifestDate,
                        time: time,
                        batchNumber: batchNumber
                    };
                    
                    const res = await this.postToApi('save_trip', { data: tripData });
                    if(res.status === 'success') {
                        this.isScheduleModalVisible = false;
                        this.showToast('Penugasan Disimpan');
                        
                        // Optimistic Update
                        if (existingTrip) Object.assign(existingTrip, tripData);
                        else this.trips.unshift(tripData);
                        this.loadData();
                    } else {
                        Swal.fire('Gagal', res.message, 'error');
                    }
                }
            } catch(e) {
                console.error(e);
                Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
            } finally {
                this.isLoading = false;
            }
        },
    }
};
