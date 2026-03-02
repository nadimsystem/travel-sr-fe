export const dispatcherMixin = {
    computed: {
        // Group bookings by Route -> Time -> Fleet Cap (Batching)
        groupedBookings() {
            const pending = this.bookings.filter(b => 
                (b.serviceType === 'Travel' || b.serviceType === 'Carter' || b.serviceType === 'Wisata' || b.serviceType === 'Dropping' || (b.serviceType && b.serviceType.toLowerCase().includes('drop'))) && 
                (b.status === 'Pending' || b.status === 'Confirmed' || b.status === 'Paid') &&
                b.date && b.status !== 'Batal' && b.status !== 'Tiba'
            );
            
            const groups = {};
            
            pending.forEach(b => {
                const key = `${b.routeId}_${b.date}_${b.time}`;
                if (!groups[key]) {
                    let route = this.routeConfig.find(r => r.id === b.routeId);
                    if (!route && (b.routeId && String(b.routeId).startsWith('CUSTOM_'))) {
                        route = { id: b.routeId, name: 'Carter Khusus', origin: b.pickupAddress || 'Custom', destination: b.dropoffAddress || 'Custom' };
                    } else if (!route) {
                         route = { id: b.routeId, origin: '?', destination: '?' };
                    }
                    
                    groups[key] = {
                        key: key,
                        routeId: b.routeId,
                        date: b.date,
                        time: b.time,
                        routeConfig: route,
                        passengers: [],
                        totalPassengers: 0,
                        assignment: null
                    };
                }
                groups[key].passengers.push(b);
                groups[key].totalPassengers += (parseInt(b.seatCount) || 1);
            });

            let batches = Object.values(groups);
            batches.sort((a, b) => {
                if (a.date !== b.date) return new Date(a.date) - new Date(b.date);
                if (a.time !== b.time) return a.time.localeCompare(b.time);
                return 0;
            });

            const finalBatches = [];

            batches.forEach(group => {
                group.passengers.sort((a, b) => parseFloat(a.id) - parseFloat(b.id));

                const normTime = (t) => t ? String(t).replace('.', ':').trim().substring(0, 5) : '';
                const gTime = normTime(group.time);

                const specificTrips = this.trips.filter(t => {
                    const tDate = t.date || (t.passengers && t.passengers[0] ? t.passengers[0].date : null);
                    if (!tDate) return false;
                    const d1 = new Date(tDate).toLocaleDateString('sv');
                    const d2 = new Date(group.date).toLocaleDateString('sv');
                    const rMatch = t.routeConfig && String(t.routeConfig.id).trim() === String(group.routeId).trim();
                    const tMatch = normTime(t.time) === gTime;
                    const sMatch = t.status === 'Scheduled';
                    


                    return rMatch && d1 === d2 && tMatch && sMatch;
                });
                specificTrips.sort((a,b) => parseFloat(b.id) - parseFloat(a.id));



                let defaultAssignment = null;
                const def = this.scheduleDefaults.find(s => String(s.routeId).trim() === String(group.routeId).trim() && normTime(s.time) === gTime);
                if (def) {
                    const f = this.fleet.find(x => String(x.id) === String(def.fleetId));
                    const d = this.drivers.find(x => String(x.id) === String(def.driverId));
                    if(f && d) defaultAssignment = { fleet: f, driver: d, type: 'Default' };
                }

                const splits = [];
                const batchMap = {};
                const unbatched = [];
                
                group.passengers.forEach(b => {
                    const bn = parseInt(b.batchNumber) || 0;
                    if (bn > 0) {
                        if (!batchMap[bn]) batchMap[bn] = [];
                        batchMap[bn].push(b);
                    } else {
                        unbatched.push(b);
                    }
                });
                
                const batchNumbers = Object.keys(batchMap).map(n => parseInt(n)).sort((a,b) => a - b);
                let nextBatchNum = batchNumbers.length > 0 ? Math.max(...batchNumbers) + 1 : 1;
                
                batchNumbers.forEach(bn => {
                    let assignedTrip = null;
                    
                    // 1. Try Specific Trip Match (Explicit Batch Number)
                    // If trip has batchNumber, use it. If not (legacy), assume 1? Or rely on index?
                    // Mixed Strategy: 
                    // - Find trip with matching batchNumber
                    // - If not found, and bn > 1, check if we have enough unassigned legacy trips? (Too complex)
                    // - Simple: Just look for match.
                    
                    // console.log(`Batch Loop: bn=${bn} (Type: ${typeof bn})`);

                    let specificMatch = specificTrips.find(t => {
                         const tBn = t.batchNumber || 1;
                         // console.log(`Checking Trip ${t.id}: tBn=${tBn} vs bn=${bn}`);
                         return tBn == bn;
                    });
                    
                    // Fallback to Index if no explicit match found AND all specific trips have batchNumber=1 (Legacy Mode)
                    if (!specificMatch && bn > 1) {
                         const isLegacy = specificTrips.every(t => (t.batchNumber || 1) == 1);
                         if (isLegacy && specificTrips[bn - 1]) {
                             specificMatch = specificTrips[bn - 1];
                         }
                    }

                    if (specificMatch) {
                        assignedTrip = { fleet: specificMatch.fleet, driver: specificMatch.driver, type: 'Specific', tripId: specificMatch.id };
                    } else {
                        // 2. Try Default Schedule (Matched by Batch)
                        const def = this.scheduleDefaults.find(s => {
                            const sBn = s.batchNumber || 1;
                            const rMatch = String(s.routeId).trim() === String(group.routeId).trim();
                            const tMatch = normTime(s.time) === gTime;
                            const bMatch = sBn == bn;
                            // if (rMatch && tMatch) console.log(`Def Match Candidate: Route=${rMatch} Time=${tMatch} Batch=${sBn}vs${bn} Result=${bMatch}`);
                            return rMatch && tMatch && bMatch;
                        });
                        
                        if (def) {
                            const f = this.fleet.find(x => String(x.id) === String(def.fleetId));
                            const d = this.drivers.find(x => String(x.id) === String(def.driverId));
                            if(f && d) assignedTrip = { fleet: f, driver: d, type: 'Default' };
                        }
                    }

                    if (assignedTrip) {
                        batchMap[bn].forEach(p => {
                            if (!p.fleetName && assignedTrip.fleet) { p.fleetName = assignedTrip.fleet.name; p.fleetId = assignedTrip.fleet.id; }
                            if (!p.driverName && assignedTrip.driver) { p.driverName = assignedTrip.driver.name; p.driverId = assignedTrip.driver.id; }
                            p._isValidContext = true; 
                        });
                    }

                    splits.push({ 
                        ...group, 
                        passengers: batchMap[bn], 
                        totalPassengers: batchMap[bn].reduce((sum, p) => sum + (parseInt(p.seatCount) || 1), 0), 
                        key: group.key + '_' + bn,
                        batchNumber: bn,
                        assignment: assignedTrip
                    });
                });
                
                if (unbatched.length > 0) {
                    let currentBatch = splits.find(s => s.batchNumber === 1);
                    if (!currentBatch) {
                        // Create Batch 1 if missing
                        let b1Assignment = null;
                        const sTrip = specificTrips.find(t => (t.batchNumber || 1) === 1);
                        if (sTrip) b1Assignment = { fleet: sTrip.fleet, driver: sTrip.driver, type: 'Specific', tripId: sTrip.id };
                        else {
                             const def = this.scheduleDefaults.find(s => String(s.routeId) === String(group.routeId) && normTime(s.time) === gTime && (s.batchNumber||1) === 1);
                             if (def) {
                                  const f = this.fleet.find(x => String(x.id) === String(def.fleetId));
                                  const d = this.drivers.find(x => String(x.id) === String(def.driverId));
                                  if(f && d) b1Assignment = { fleet: f, driver: d, type: 'Default' };
                             }
                        }
                        
                        currentBatch = { ...group, passengers: [], totalPassengers: 0, key: group.key + '_1', batchNumber: 1, assignment: b1Assignment };
                        splits.push(currentBatch);
                        nextBatchNum = Math.max(nextBatchNum, 2);
                    } else if (!currentBatch.assignment) {
                        if (specificTrips[0]) currentBatch.assignment = { fleet: specificTrips[0].fleet, driver: specificTrips[0].driver, type: 'Specific', tripId: specificTrips[0].id };
                        else if (defaultAssignment) currentBatch.assignment = defaultAssignment;
                    }
                    
                    const occupiedPerBatch = {};
                    splits.forEach(s => {
                        occupiedPerBatch[s.batchNumber] = new Set();
                        s.passengers.forEach(p => {
                            if (p.seatNumbers) p.seatNumbers.split(',').forEach(seat => occupiedPerBatch[s.batchNumber].add(seat.trim()));
                        });
                    });
                    
                    unbatched.forEach(b => {
                        const pCount = parseInt(b.seatCount) || 1;
                        const pSeats = b.seatNumbers ? b.seatNumbers.split(',').map(s => s.trim()) : [];
                        
                        let targetBatch = null;
                        for (let batch of splits) {
                            const occupied = occupiedPerBatch[batch.batchNumber] || new Set();
                            const hasCollision = pSeats.some(s => occupied.has(s));
                            const overCapacity = batch.totalPassengers + pCount > 14;
                            if (!hasCollision && !overCapacity) { targetBatch = batch; break; }
                        }
                        
                        if (!targetBatch) {
                            targetBatch = { ...group, passengers: [], totalPassengers: 0, key: group.key + '_' + nextBatchNum, batchNumber: nextBatchNum, assignment: null };
                            splits.push(targetBatch);
                            occupiedPerBatch[nextBatchNum] = new Set();
                            nextBatchNum++;
                        }
                        
                        targetBatch.passengers.push(b);
                        targetBatch.totalPassengers += pCount;
                        pSeats.forEach(s => occupiedPerBatch[targetBatch.batchNumber].add(s));
                    });
                }
                
                if (defaultAssignment && !splits.find(s => s.batchNumber === 1)) {
                    splits.push({ ...group, passengers: [], totalPassengers: 0, key: group.key + '_1', batchNumber: 1, assignment: defaultAssignment });
                }
                
                splits.sort((a, b) => a.batchNumber - b.batchNumber);
                splits.forEach(s => finalBatches.push(s));
            });
            
            return finalBatches;
        },

        groupedDispatcherViews() {
            const batches = this.groupedBookings;
            const routeGroups = {};
            batches.forEach(batch => {
                const routeName = batch.routeConfig ? (batch.routeConfig.name || `${batch.routeConfig.origin} - ${batch.routeConfig.destination}`) : 'Lainnya';
                if (!routeGroups[routeName]) routeGroups[routeName] = [];
                routeGroups[routeName].push(batch);
            });
            Object.keys(routeGroups).forEach(key => {
                routeGroups[key].sort((a, b) => {
                    if (a.date !== b.date) return new Date(b.date) - new Date(a.date);
                    return b.time.localeCompare(a.time);
                });
            });
            return routeGroups;
        },

        dispatcherRoutesList() {
            const routes = Object.keys(this.groupedDispatcherViews);
            this.activeTrips.forEach(trip => {
                const name = trip.routeConfig ? trip.routeConfig.name || `${trip.routeConfig.origin} - ${trip.routeConfig.destination}` : 'Lainnya';
                if (!routes.includes(name)) routes.push(name);
            });
            return routes.sort();
        },

        filteredDispatcherViews() {
            const allGroups = this.groupedDispatcherViews;
            if (this.dispatcherRouteFilter === 'All') return allGroups;
            const filtered = {};
            if (allGroups[this.dispatcherRouteFilter]) filtered[this.dispatcherRouteFilter] = allGroups[this.dispatcherRouteFilter];
            return filtered;
        },

        activeTrips() { 
            return this.trips.filter(t => {
                if (['Tiba', 'Batal'].includes(t.status)) return false;
                
                // Exclude Carter Khusus from Active View (User Request)
                const routeId = t.routeConfig ? (t.routeConfig.id || '') : '';
                const routeName = t.routeConfig ? (t.routeConfig.name || '') : '';
                if (String(routeId).startsWith('CUSTOM_') || routeName === 'Carter Khusus') return false;

                const pCount = this.getTripPassengerCount(t);
                return pCount > 0;
            }); 
        },

        filteredActiveTrips() {
            if (this.dispatcherRouteFilter === 'All') return this.activeTrips;
            return this.activeTrips.filter(trip => {
                const name = trip.routeConfig ? trip.routeConfig.name || `${trip.routeConfig.origin} - ${trip.routeConfig.destination}` : 'Lainnya';
                return name === this.dispatcherRouteFilter;
            });
        },
        
        pendingGroupsCount() { return this.groupedBookings.length; },
        pendingDispatchCount() { return this.groupedBookings.length; },
        
        outboundTrips() {
            return this.activeTrips.filter(t => {
                const origin = t.routeConfig?.origin?.toLowerCase() || '';
                const id = t.routeConfig?.id?.toLowerCase() || '';
                return (origin.includes('padang') || id.startsWith('pdg')) && !origin.includes('payakumbuh') && !origin.includes('bukittinggi');
            }).sort((a,b) => (a.routeConfig?.time || '').localeCompare(b.routeConfig?.time || ''));
        },

        inboundTrips() {
            return this.activeTrips.filter(t => {
                const dest = t.routeConfig?.destination?.toLowerCase() || '';
                const id = t.routeConfig?.id?.toLowerCase() || '';
                return (dest.includes('padang') || id.includes('pdg')) && !id.startsWith('pdg');
            }).sort((a,b) => (a.routeConfig?.time || '').localeCompare(b.routeConfig?.time || ''));
        },

        debugHiddenBookings() {
            // Find bookings that are valid but not in groupedBookings
            const visibleIds = new Set();
            this.groupedBookings.forEach(g => {
                g.passengers.forEach(p => visibleIds.add(p.id));
            });
            
            return this.bookings.filter(b => 
                (b.status === 'Pending' || b.status === 'Confirmed' || b.status === 'Paid') &&
                b.serviceType !== 'Bus Pariwisata' && // Exclude Bus
                !visibleIds.has(b.id) &&
                b.date >= this.manifestDate // Only future/current
            );
        },
    },

    methods: {
        openDispatchModal(g) {
            this.dispatchForm = {
                group: g,
                fleetId: g.assignment ? g.assignment.fleet.id : "",
                driverId: g.assignment ? g.assignment.driver.id : "",
                passengers: g.passengers.filter(p => p.status !== 'Batal' && p.status !== 'Tiba'),
                remainingCount: 0, 
                scheduleOption: 'Normal',
                nextSchedules: [],
                isLocked: g.assignment && g.assignment.type === 'Default',
                assignmentReason: g.assignment ? g.assignment.type : ''
            };
            this.isDispatchModalVisible = true;
        },

        async processDispatch() {
            if (!this.dispatchForm.fleetId || !this.dispatchForm.driverId) return Swal.fire('Error', 'Pilih Armada dan Supir', 'error');
            
            const passengers = this.dispatchForm.passengers;
            if (passengers.length === 0) return Swal.fire('Error', 'Tidak ada penumpang untuk diberangkatkan', 'error');

            // Find Fleet Data (ensure object)
            const f = this.fleet.find(x => x.id == this.dispatchForm.fleetId);
            const d = this.drivers.find(x => x.id == this.dispatchForm.driverId);
            
            const payload = {
                routeConfig: this.dispatchForm.group.routeConfig, // Object {id, name, ...}
                fleet: f,
                driver: d,
                passengers: passengers.map(p => ({
                    id: p.id, 
                    seatCount: p.seatCount, 
                    seatNumbers: p.seatNumbers, 
                    pickupAddress: p.pickupAddress,
                    dropoffAddress: p.dropoffAddress,
                    passengerName: p.passengerName, 
                    passengerPhone: p.passengerPhone,
                    date: p.date, // Important: Preserve booking date
                    duration: p.duration, // For Carter
                    serviceType: p.serviceType
                })),
                status: 'On Trip',
                date: this.dispatcherDate || this.manifestDate? this.manifestDate : this.dispatchForm.group.date, // Use Manifest Date usually
                time: this.dispatchForm.group.time,
                batchNumber: this.dispatchForm.group.batchNumber,
                id: this.dispatchForm.group.assignment ? this.dispatchForm.group.assignment.tripId : null
            };
            
            // Check Conflict (Same Driver/Fleet Same Time) - Optional Client Side (Backend should check too)

            this.isLoading = true;
            const res = await this.postToApi('dispatch_trip', { data: payload });
            this.isLoading = false;

            if (res.status === 'success') {
                this.isDispatchModalVisible = false;
                this.loadData();
                this.showToast('Trip Berhasil Diberangkatkan', 'success');
            } else {
                Swal.fire('Gagal', res.message, 'error');
            }
        },
        
        // --- DRAG DROP ---
        onDragStartCard(evt, passenger, group) {
             this.isDragging = true;
             // Payload: Passenger ID + Source Context
             const payload = JSON.stringify({
                 id: passenger.id,
                 passengerName: passenger.passengerName, // For confirm dialog
                 date: group.date,
                 time: group.time,
                 currentBatchNumber: group.batchNumber,
                 seatNumbers: passenger.seatNumbers
             });
             evt.dataTransfer.dropEffect = 'move';
             evt.dataTransfer.effectAllowed = 'move';
             evt.dataTransfer.setData('text/plain', payload);
        },

        allowDrop(evt) { evt.preventDefault(); },
        onDragEndCard(evt) { this.isDragging = false; },
        onDragEnterCard(evt) { evt.currentTarget.classList.add('ring-4', 'ring-blue-300'); },
        onDragLeaveCard(evt) { if (!evt.currentTarget.contains(evt.relatedTarget)) evt.currentTarget.classList.remove('ring-4', 'ring-blue-300'); },

        async onDropToCard(evt, targetGroup) {
            this.isDragging = false;
            evt.preventDefault();
            evt.currentTarget.classList.remove('ring-4', 'ring-blue-300');

            const dataStr = evt.dataTransfer.getData('text/plain');
            if (!dataStr) return;
            
            let src;
            try { src = JSON.parse(dataStr); } catch(e) { console.error('Drag parse error', e); return; }

            const targetBatch = parseInt(targetGroup.batchNumber) || 1;
            const srcBatch = parseInt(src.currentBatchNumber) || 1;
            
            if ((srcBatch == targetBatch) && (src.date === targetGroup.date) && (src.time === targetGroup.time)) {
                this.showToast('Sudah di armada ini', 'info'); 
                return;
            }

            // Seat Conflict Logic
            const srcSeats = src.seatNumbers ? src.seatNumbers.split(',').map(s => s.trim()) : [];
            const occupiedSeats = new Set();
            targetGroup.passengers.forEach(p => {
                if (p.id == src.id) return; 
                if (p.seatNumbers) p.seatNumbers.split(',').forEach(s => occupiedSeats.add(s.trim()));
            });

            const conflicts = srcSeats.filter(s => occupiedSeats.has(s));
            let finalSeatNumbers = src.seatNumbers;

            if (conflicts.length > 0) {
                const { value: newSeats, isConfirmed } = await Swal.fire({
                    title: 'Bangku Bentrok!',
                    html: `<p class="mb-2 text-sm text-red-600">Bangku <b>${conflicts.join(', ')}</b> sudah terisi di Armada ${targetBatch}.</p> 
                           <label class="block text-xs font-bold text-gray-700 mb-1">Masukkan Bangku Baru:</label>`,
                    input: 'text',
                    showCancelButton: true,
                    confirmButtonText: 'Simpan & Pindah',
                    inputValidator: (value) => {
                        if (!value) return 'Nomor bangku harus diisi!';
                        const checkInput = value.split(',').map(s => s.trim());
                        if(checkInput.filter(s => occupiedSeats.has(s)).length > 0) return `Bangku masih bentrok!`;
                    }
                });
                if (!isConfirmed) return;
                finalSeatNumbers = newSeats;
            } else {
                 const result = await Swal.fire({
                    title: 'Pindah Armada?',
                    text: `Pindahkan "${src.passengerName}" ke Armada ${targetBatch}?` + (!src.date===targetGroup.date?` (Tanggal: ${this.formatDate(targetGroup.date)})`:''),
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Pindah'
                });
                if (!result.isConfirmed) return;
            }

            this.isLoading = true;
            const res = await this.postToApi('move_booking_schedule', {
                id: src.id,
                date: targetGroup.date,
                time: targetGroup.time,
                seatNumbers: finalSeatNumbers,
                batchNumber: targetBatch
            });
            
            if (res.status === 'success') {
                this.loadData();
                this.showToast(`Dipindahkan ke Armada ${targetBatch}`, 'success');
            } else {
                this.isLoading = false;
                Swal.fire("Gagal", res.message, 'error');
            }
        },

        // --- MOVE BOOKING MODAL ---
        async openBookingActions(booking, group) { this.openMoveModal(booking); },

        openMoveModal(booking) {
            const route = this.routeConfig.find(r => r.id === booking.routeId);
            const schedules = route ? (route.schedules || []) : [];
            this.moveModalData = {
                passengerId: booking.id, passengerName: booking.passengerName, routeId: booking.routeId,
                currentDate: booking.date, currentTime: booking.time, seatNumbers: booking.seatNumbers,
                targetTime: booking.time, targetBatchIndex: 0, availableSchedules: schedules, allBatchesForTime: []
            };
            this.recalcMoveBatches();
            if (this.moveModalData.targetTime === booking.time) {
                const currentBatchIdx = this.moveModalData.allBatchesForTime.findIndex(b => b.passengers.find(p => p.id === booking.id));
                if (currentBatchIdx !== -1) this.moveModalData.targetBatchIndex = currentBatchIdx;
            }
            this.isMoveModalVisible = true;
        },
        closeMoveModal() { this.isMoveModalVisible = false; },

        recalcMoveBatches() {
            const { routeId, currentDate, targetTime } = this.moveModalData;
            const relevantBatches = this.groupedBookings.filter(b => b.routeConfig && b.routeConfig.id === routeId && b.date === currentDate && b.time === targetTime);
            
            this.moveModalData.allBatchesForTime = relevantBatches.map((b, idx) => ({
                name: `Armada ${b.batchNumber || (idx + 1)}`,
                isFull: b.passengers.length >= 8,
                passengers: b.passengers.filter(p => p.id !== this.moveModalData.passengerId)
            }));
            
            if (this.moveModalData.allBatchesForTime.length === 0) this.moveModalData.allBatchesForTime.push({ name: 'Armada 1', passengers: [] });
            this.moveModalData.targetBatchIndex = 0;
        },

        moveModalBatches() { return this.moveModalData.allBatchesForTime || []; },
        selectMoveBatch(idx) {
            if (idx === -1) {
                 this.moveModalData.allBatchesForTime.push({ name: `Armada ${this.moveModalData.allBatchesForTime.length + 1}`, passengers: [] });
                 this.moveModalData.targetBatchIndex = this.moveModalData.allBatchesForTime.length - 1;
            } else {
                this.moveModalData.targetBatchIndex = idx;
            }
        },

        isMoveSeatOccupied(seat) {
            const batchIdx = this.moveModalData.targetBatchIndex;
            const batch = this.moveModalData.allBatchesForTime[batchIdx];
            if (!batch) return false; 
            return batch.passengers.some(p => {
                if (!p.seatNumbers) return false;
                const seats = p.seatNumbers.split(',').map(s=>s.trim());
                return seats.includes(seat);
            });
        },
        
        getSeatClass(seat) {
            const selectedSeats = this.moveModalData.seatNumbers ? this.moveModalData.seatNumbers.split(',').map(s => s.trim()) : [];
            if (selectedSeats.includes(seat)) return 'bg-blue-600 text-white border-blue-600 ring-2 ring-blue-200';
            if (this.isMoveSeatOccupied(seat)) return 'bg-white border-slate-200 text-slate-300';
            return 'bg-white text-slate-600 hover:bg-blue-50 border-slate-200';
        },

        toggleMoveSeat(seat) {
            if (this.isMoveSeatOccupied(seat)) return;
            let currentSeats = this.moveModalData.seatNumbers ? this.moveModalData.seatNumbers.split(',').map(s => s.trim()).filter(s => s) : [];
            const idx = currentSeats.indexOf(seat);
            if (idx > -1) currentSeats.splice(idx, 1); else currentSeats.push(seat);
            this.moveModalData.seatNumbers = currentSeats.join(', ');
        },

        async saveMove() {
            const { passengerId, targetTime, seatNumbers, targetBatchIndex } = this.moveModalData;
            if (!seatNumbers) return Swal.fire('Error', 'Pilih kursi terlebih dahulu.', 'error');
            const batchNumber = targetBatchIndex + 1;
            
            this.isLoading = true;
            const res = await this.postToApi('move_booking_schedule', {
                id: passengerId, date: this.moveModalData.currentDate, time: targetTime, seatNumbers: seatNumbers, batchNumber: batchNumber
            });
            
            if (res.status === 'success') {
                this.closeMoveModal();
                this.loadData();
                this.showToast('Berhasil dipindahkan ke Armada ' + batchNumber, 'success');
            } else {
                this.isLoading = false;
                Swal.fire("Gagal", res.message, 'error');
            }
        },

        // --- MANIFEST ---
        sendManifestToDriver(group) {
            const driver = group.assignment ? group.assignment.driver : null;
            if (!driver || !driver.phone) return Swal.fire('Error', 'Data Supir atau Nomor HP Supir tidak ditemukan.', 'error');

            const dateStr = new Date(group.date).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
            let msg = `*MANIFEST PERJALANAN - SUTAN RAYA*\nTanggal: ${dateStr}\nJam: ${group.time}\nRute: ${group.routeConfig.origin} -> ${group.routeConfig.destination}\nArmada: ${group.assignment.fleet.name} (${group.assignment.fleet.plate})\nSupir: ${driver.name}\n\n*DAFTAR PENUMPANG:*\n`;

            const sortedPassengers = [...group.passengers].sort((a,b) => (parseInt(a.seatNumbers)||999) - (parseInt(b.seatNumbers)||999));
            sortedPassengers.forEach((p, index) => {
                let statusText = 'BELUM LUNAS';
                if (p.validationStatus === 'Valid' || p.paymentStatus === 'Lunas' || p.paymentStatus === 'Paid') {
                    statusText = 'LUNAS';
                } else if (p.paymentMethod === 'Transfer' && (p.paymentProof || p.paymentStatus === 'Menunggu Validasi')) {
                    statusText = 'VALIDASI'; // Shorten for manifest
                }
                const seatInfo = p.seatNumbers ? `(Kursi ${p.seatNumbers})` : '';
                const pickup = this.parseAddress(p.pickupAddress);
                const dropoff = this.parseAddress(p.dropoffAddress);

                msg += `----------------------------------\n${index + 1}. *${p.passengerName}* ${seatInfo}\n   HP: ${p.passengerPhone}\n   Status: ${statusText}\n   Jemput: ${pickup.text || '-'}\n`;
                if(pickup.link) msg += `   Maps: ${pickup.link}\n`;
                msg += `   Antar: ${dropoff.text || '-'}\n`;
                if(dropoff.link) msg += `   Maps: ${dropoff.link}\n`;
            });

            msg += `\n----------------------------------\nTotal: ${group.totalPassengers} Orang\n\n_Mohon dicek kembali dan lapor admin jika ada ketidaksesuaian._`;

            Swal.fire({
                title: 'Kirim Manifest ke Supir',
                input: 'textarea',
                inputLabel: 'Preview Pesan WhatsApp',
                inputValue: msg,
                inputAttributes: { 'style': 'height: 300px; font-size: 12px; font-family: monospace;' },
                showCancelButton: true,
                confirmButtonText: '<i class="bi bi-whatsapp"></i> Kirim Sekarang',
                confirmButtonColor: '#25D366'
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    const finalMsg = encodeURIComponent(result.value);
                    const waLink = `https://wa.me/${driver.phone.replace(/^0/, '62').replace(/[^0-9]/g, '')}?text=${finalMsg}`;
                    window.open(waLink, '_blank');
                }
            });
        },
        
        // --- TRIP CONTROL ---
        openTripControl(trip) { this.activeTripControl = trip; this.isTripControlVisible = true; },
        async startTrip() {
            if(!this.activeTripControl) return;
            if((await Swal.fire({ title: 'Mulai Perjalanan?', text: "Status trip akan berubah menjadi 'On Trip'", icon: 'question', showCancelButton: true, confirmButtonText: 'Ya, Jalan!' })).isConfirmed) {
                this.updateTripStatus(this.activeTripControl.id, 'On Trip');
            }
        },
        async finishTrip() {
             if(!this.activeTripControl) return;
             if((await Swal.fire({ title: 'Selesaikan Trip?', text: "Unit akan kembali menjadi 'Tersedia'.", icon: 'success', showCancelButton: true, confirmButtonText: 'Ya, Selesai!', confirmButtonColor: '#28a745' })).isConfirmed) {
                this.updateTripStatus(this.activeTripControl.id, 'Tiba');
            }
        },
        async cancelTrip() {
             if(!this.activeTripControl) return;
             if((await Swal.fire({ title: 'Batalkan Trip?', text: "PERINGATAN: Trip ini akan dihapus & penumpang kembali ke antrian Dispatcher!", icon: 'warning', showCancelButton: true, confirmButtonText: 'Batalkan Trip', confirmButtonColor: '#d33' })).isConfirmed) {
                 this.updateTripStatus(this.activeTripControl.id, 'Batal');
            }
        },
        async updateTripStatus(tripId, status) {
            this.isLoading = true;
            const res = await this.postToApi('update_trip_status', { tripId: tripId, status: status });
            this.isLoading = false;
            
            if(res.status === 'success') {
                this.isTripControlVisible = false;
                this.loadData();
                this.showToast('Status Trip Diperbarui');
            } else {
                Swal.fire('Gagal', res.message, 'error');
            }
        },
        
        showInvalidStatusInfo(p) { this.openBookingActions(p); }, // Simplified redirect
    }
};
