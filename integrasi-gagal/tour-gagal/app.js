const { createApp } = Vue;

createApp({
    data() {
        return {
            // Data utama dari DataManager
            fleet: [],
            drivers: [],
            tourData: {
                costs: {},
                cities: [],
                allAccommodations: [],
                allDestinations: []
            },
            // State untuk aplikasi tour planner
            tour: {
                name: '',
                pax: 10,
                duration: 3,
                startDate: new Date().toISOString().slice(0, 10),
                fleetId: '',
                driverId: '',
                includeMeals: true,
                includeLodge: true,
                itinerary: []
            },
            markupPercentage: 20,
            saveStatus: null
        };
    },
    computed: {
        costs() {
            return this.tourData.costs;
        },
        availableFleet() {
            // Filter armada yang cocok untuk pariwisata
            return this.fleet.filter(v => v.status === 'Tersedia' && (v.type.includes('Hiace') || v.type.includes('Bus')));
        },
        availableDrivers() {
            return this.drivers.filter(d => d.status === 'Standby');
        },
        availableDestinations() {
            return this.tourData.allDestinations;
        },
        selectedFleet() {
            return this.fleet.find(f => f.id === this.tour.fleetId);
        },
        selectedFleetName(){
             return this.selectedFleet ? this.selectedFleet.name : 'Belum Dipilih';
        },
        selectedDriverName(){
            const driver = this.drivers.find(d => d.id === this.tour.driverId);
            return driver ? driver.name : 'Belum Dipilih';
        },
        totalFleetCost() {
            if (!this.selectedFleet) return 0;
            return this.selectedFleet.hargaSewa * this.tour.duration;
        },
        totalAccommodationCost() {
            return this.tour.itinerary.reduce((total, day) => {
                if (day.accommodation) {
                    // Asumsi jumlah kamar = pax / 2 (jika hotel) atau 1 (jika villa)
                    const roomCount = day.accommodation.type === 'Hotel' ? Math.ceil(this.tour.pax / 2) : 1;
                    return total + (day.accommodation.pricePerNight * roomCount);
                }
                return total;
            }, 0);
        },
        totalDriverCost() {
            let total = 0;
            if (this.tour.includeMeals) {
                total += this.costs.driverMeal * this.tour.duration;
            }
            if (this.tour.includeLodge) {
                // Biaya penginapan driver hanya untuk malam hari (durasi - 1)
                total += this.costs.driverLodge * (this.tour.duration - 1);
            }
            return total;
        },
        totalCost() {
            return this.totalFleetCost + this.totalAccommodationCost + this.totalDriverCost;
        },
        markupAmount() {
            return this.totalCost * (this.markupPercentage / 100);
        },
        finalPrice() {
            return this.totalCost + this.markupAmount;
        },
        pricePerPax() {
            if (this.tour.pax === 0) return 0;
            return this.finalPrice / this.tour.pax;
        },
        isReadyToSave() {
            return this.tour.name && this.tour.fleetId && this.tour.driverId && this.tour.startDate;
        }
    },
    methods: {
        initializeItinerary() {
            this.tour.itinerary = [];
            for (let i = 0; i < this.tour.duration; i++) {
                this.tour.itinerary.push({
                    day: i + 1,
                    destinations: [],
                    accommodation: null,
                    city: ''
                });
            }
        },
        addDestination(dayIndex, destination) {
            // Mencegah duplikasi
            if (!this.tour.itinerary[dayIndex].destinations.find(d => d.name === destination.name)) {
                this.tour.itinerary[dayIndex].destinations.push(destination);
                // Otomatis set kota hari itu berdasarkan destinasi pertama
                if(this.tour.itinerary[dayIndex].destinations.length === 1){
                    this.tour.itinerary[dayIndex].city = destination.city;
                }
            }
        },
        removeDestination(dayIndex, destIndex){
            this.tour.itinerary[dayIndex].destinations.splice(destIndex, 1);
        },
        availableAccommodations(day) {
            if (!day.city) return [];
            return this.tourData.allAccommodations.filter(acc => acc.city === day.city);
        },
        setAccommodation(dayIndex, accommodation) {
            this.tour.itinerary[dayIndex].accommodation = accommodation;
        },
        saveTourPackage() {
            if (!this.isReadyToSave) {
                alert("Harap lengkapi Nama Paket, Armada, Driver, dan Tanggal Mulai.");
                return;
            }

            const startDate = new Date(this.tour.startDate);
            const endDate = new Date(startDate);
            endDate.setDate(startDate.getDate() + this.tour.duration -1);

            // Membuat catatan itinerary untuk disimpan di trip
            let itineraryNotes = `Paket Tur ${this.tour.duration} Hari ${this.tour.pax} Pax. \n`;
            this.tour.itinerary.forEach((day, index) => {
                itineraryNotes += `Hari ${index+1}: ${day.destinations.map(d => d.name).join(', ')}. Menginap di ${day.accommodation ? day.accommodation.name : 'Tidak menginap'}. \n`;
            });
            itineraryNotes += `Harga Jual: ${this.formatCurrency(this.finalPrice)}`;


            const newTrip = {
                fleetId: this.tour.fleetId,
                driverId: this.tour.driverId,
                type: 'Rental',
                origin: 'Padang', // Asumsi mulai dari Padang
                destination: `Tour: ${this.tour.name}`,
                departureTime: startDate.toISOString(),
                arrivalTime: endDate.toISOString(),
                pax: 'Carter',
                notes: itineraryNotes,
                // Tambahkan detail keuangan
                revenue: this.finalPrice,
                cost: this.totalCost,
            };

            try {
                DataManager.addTrip(newTrip);
                
                // Update status supir dan armada
                const data = DataManager.loadData();
                const driver = data.drivers.find(d => d.id === this.tour.driverId);
                const vehicle = data.fleet.find(f => f.id === this.tour.fleetId);
                if (driver) driver.status = 'Dalam Perjalanan';
                if (vehicle) vehicle.status = 'Dalam Perjalanan';
                DataManager.saveData(data);

                this.saveStatus = { success: true, message: 'Paket tur berhasil disimpan dan dijadwalkan!' };
                setTimeout(() => { this.saveStatus = null; }, 5000);
            } catch (error) {
                this.saveStatus = { success: false, message: 'Gagal menyimpan paket tur.' };
                console.error("Error saving tour package:", error);
            }
        },
        formatCurrency(value) {
            if (typeof value !== 'number') return 'Rp 0';
            return 'Rp ' + value.toLocaleString('id-ID');
        }
    },
    watch: {
        'tour.duration'(newDuration, oldDuration) {
            if (newDuration > 0) {
                this.initializeItinerary();
            }
        }
    },
    mounted() {
        console.log("Tour App mounted. Loading data...");
        const data = DataManager.loadData();
        this.fleet = data.fleet;
        this.drivers = data.drivers;
        if (data.tourData) {
            this.tourData = data.tourData;
        } else {
            console.error("Tour data not found in DataManager!");
        }
        this.initializeItinerary();
    }
}).mount('#tourApp');