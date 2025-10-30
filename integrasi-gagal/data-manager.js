// integrasi/data-manager.js

/**
 * Fungsi ini berisi struktur data awal dan default untuk seluruh aplikasi.
 * Ini akan digunakan jika tidak ada data yang tersimpan di localStorage.
 */
function getInitialData() {
    // Helper untuk membuat tanggal dinamis agar data demo tetap relevan
    const getTodayISO = (h, m) => { const d = new Date(); d.setHours(h, m, 0, 0); return d.toISOString().slice(0, 16); };
    const getYesterdayISO = (h, m) => { const d = new Date(); d.setDate(d.getDate() - 1); d.setHours(h, m, 0, 0); return d.toISOString().slice(0, 16); };
    const getFutureISO = (days, h, m) => { const d = new Date(); d.setDate(d.getDate() + days); d.setHours(h, m, 0, 0); return d.toISOString().slice(0, 16); };

    const fleet = [
        // Data Armada Lengkap (40 unit)
        // Hiace - 13 unit
        { id: 1, name: 'Hiace Premio SR-01', type: 'Hiace Premio', plate: 'BA 1001 HP', capacity: 7, status: 'Tersedia', icon: 'bi-truck-front-fill', hargaSewa: 1600000, hargaPerOrang: 150000, biayaOperasional: 600000, requiredLicense: 'A Umum', lastService: '2025-08-01', nextService: '2026-02-01' },
        { id: 2, name: 'Hiace Premio SR-02', type: 'Hiace Premio', plate: 'BA 1002 HP', capacity: 7, status: 'Tersedia', icon: 'bi-truck-front-fill', hargaSewa: 1600000, hargaPerOrang: 150000, biayaOperasional: 600000, requiredLicense: 'A Umum', lastService: '2025-08-01', nextService: '2026-02-01' },
        { id: 3, name: 'Hiace Commuter SR-03', type: 'Hiace Commuter', plate: 'BA 1003 HC', capacity: 7, status: 'Tersedia', icon: 'bi-truck-front-fill', hargaSewa: 1500000, hargaPerOrang: 130000, biayaOperasional: 550000, requiredLicense: 'A Umum', lastService: '2025-09-15', nextService: '2026-03-15' },
        { id: 4, name: 'Hiace Premio SR-04', type: 'Hiace Premio', plate: 'BA 1004 HP', capacity: 7, status: 'Tersedia', icon: 'bi-truck-front-fill', hargaSewa: 1600000, hargaPerOrang: 150000, biayaOperasional: 600000, requiredLicense: 'A Umum', lastService: '2025-08-01', nextService: '2025-10-25' },
        { id: 5, name: 'Hiace Commuter SR-05', type: 'Hiace Commuter', plate: 'BA 1005 HC', capacity: 7, status: 'Tersedia', icon: 'bi-truck-front-fill', hargaSewa: 1500000, hargaPerOrang: 130000, biayaOperasional: 550000, requiredLicense: 'A Umum', lastService: '2025-09-15', nextService: '2026-03-15' },
        { id: 6, name: 'Hiace Premio SR-06', type: 'Hiace Premio', plate: 'BA 1006 HP', capacity: 7, status: 'Tersedia', icon: 'bi-truck-front-fill', hargaSewa: 1600000, hargaPerOrang: 150000, biayaOperasional: 600000, requiredLicense: 'A Umum', lastService: '2025-08-01', nextService: '2026-02-01' },
        { id: 7, name: 'Hiace Premio SR-07', type: 'Hiace Premio', plate: 'BA 1007 HP', capacity: 7, status: 'Tersedia', icon: 'bi-truck-front-fill', hargaSewa: 1600000, hargaPerOrang: 150000, biayaOperasional: 600000, requiredLicense: 'A Umum', lastService: '2025-08-01', nextService: '2026-02-01' },
        { id: 8, name: 'Hiace Commuter SR-08', type: 'Hiace Commuter', plate: 'BA 1008 HC', capacity: 7, status: 'Tersedia', icon: 'bi-truck-front-fill', hargaSewa: 1500000, hargaPerOrang: 130000, biayaOperasional: 550000, requiredLicense: 'A Umum', lastService: '2025-09-15', nextService: '2026-03-15' },
        { id: 9, name: 'Hiace Premio SR-09', type: 'Hiace Premio', plate: 'BA 1009 HP', capacity: 7, status: 'Tersedia', icon: 'bi-truck-front-fill', hargaSewa: 1600000, hargaPerOrang: 150000, biayaOperasional: 600000, requiredLicense: 'A Umum', lastService: '2025-08-01', nextService: '2026-02-01' },
        { id: 10, name: 'Hiace Premio SR-10', type: 'Hiace Premio', plate: 'BA 1010 HP', capacity: 7, status: 'Tersedia', icon: 'bi-truck-front-fill', hargaSewa: 1600000, hargaPerOrang: 150000, biayaOperasional: 600000, requiredLicense: 'A Umum', lastService: '2025-08-01', nextService: '2026-02-01' },
        { id: 11, name: 'Hiace Commuter SR-11', type: 'Hiace Commuter', plate: 'BA 1011 HC', capacity: 7, status: 'Tersedia', icon: 'bi-truck-front-fill', hargaSewa: 1500000, hargaPerOrang: 130000, biayaOperasional: 550000, requiredLicense: 'A Umum', lastService: '2025-09-15', nextService: '2026-03-15' },
        { id: 12, name: 'Hiace Premio SR-12', type: 'Hiace Premio', plate: 'BA 1012 HP', capacity: 7, status: 'Tersedia', icon: 'bi-truck-front-fill', hargaSewa: 1600000, hargaPerOrang: 150000, biayaOperasional: 600000, requiredLicense: 'A Umum', lastService: '2025-08-01', nextService: '2026-02-01' },
        { id: 13, name: 'Hiace Premio SR-13', type: 'Hiace Premio', plate: 'BA 1013 HP', capacity: 7, status: 'Perbaikan', icon: 'bi-truck-front-fill', hargaSewa: 1600000, hargaPerOrang: 150000, biayaOperasional: 600000, requiredLicense: 'A Umum', lastService: '2025-08-01', nextService: '2026-02-01' },
        // Medium Bus - 11 unit
        { id: 14, name: 'Medium Bus SR-21', type: 'Medium Bus', plate: 'BA 7021 MB', capacity: 33, status: 'Tersedia', icon: 'bi-bus-front-fill', hargaSewa: 2800000, biayaOperasional: 1100000, requiredLicense: 'B1 Umum', lastService: '2025-07-20', nextService: '2025-11-20' },
        { id: 15, name: 'Medium Bus SR-22', type: 'Medium Bus', plate: 'BA 7022 MB', capacity: 33, status: 'Tersedia', icon: 'bi-bus-front-fill', hargaSewa: 2800000, biayaOperasional: 1100000, requiredLicense: 'B1 Umum', lastService: '2025-07-20', nextService: '2025-11-20' },
        { id: 16, name: 'Medium Bus SR-23', type: 'Medium Bus', plate: 'BA 7023 MB', capacity: 33, status: 'Tersedia', icon: 'bi-bus-front-fill', hargaSewa: 2800000, biayaOperasional: 1100000, requiredLicense: 'B1 Umum', lastService: '2025-07-20', nextService: '2025-11-20' },
        { id: 17, name: 'Medium Bus SR-24', type: 'Medium Bus', plate: 'BA 7024 MB', capacity: 33, status: 'Tersedia', icon: 'bi-bus-front-fill', hargaSewa: 2800000, biayaOperasional: 1100000, requiredLicense: 'B1 Umum', lastService: '2025-07-20', nextService: '2025-11-20' },
        { id: 18, name: 'Medium Bus SR-25', type: 'Medium Bus', plate: 'BA 7025 MB', capacity: 33, status: 'Tersedia', icon: 'bi-bus-front-fill', hargaSewa: 2800000, biayaOperasional: 1100000, requiredLicense: 'B1 Umum', lastService: '2025-07-20', nextService: '2025-11-20' },
        { id: 19, name: 'Medium Bus SR-26', type: 'Medium Bus', plate: 'BA 7026 MB', capacity: 33, status: 'Tersedia', icon: 'bi-bus-front-fill', hargaSewa: 2800000, biayaOperasional: 1100000, requiredLicense: 'B1 Umum', lastService: '2025-07-20', nextService: '2025-11-20' },
        { id: 20, name: 'Medium Bus SR-27', type: 'Medium Bus', plate: 'BA 7027 MB', capacity: 33, status: 'Tersedia', icon: 'bi-bus-front-fill', hargaSewa: 2800000, biayaOperasional: 1100000, requiredLicense: 'B1 Umum', lastService: '2025-07-20', nextService: '2025-11-20' },
        { id: 21, name: 'Medium Bus SR-28', type: 'Medium Bus', plate: 'BA 7028 MB', capacity: 33, status: 'Tersedia', icon: 'bi-bus-front-fill', hargaSewa: 2800000, biayaOperasional: 1100000, requiredLicense: 'B1 Umum', lastService: '2025-07-20', nextService: '2025-11-20' },
        { id: 22, name: 'Medium Bus SR-29', type: 'Medium Bus', plate: 'BA 7029 MB', capacity: 33, status: 'Tersedia', icon: 'bi-bus-front-fill', hargaSewa: 2800000, biayaOperasional: 1100000, requiredLicense: 'B1 Umum', lastService: '2025-07-20', nextService: '2025-11-20' },
        { id: 23, name: 'Medium Bus SR-30', type: 'Medium Bus', plate: 'BA 7030 MB', capacity: 33, status: 'Tersedia', icon: 'bi-bus-front-fill', hargaSewa: 2800000, biayaOperasional: 1100000, requiredLicense: 'B1 Umum', lastService: '2025-07-20', nextService: '2025-11-20' },
        { id: 24, name: 'Medium Bus SR-31', type: 'Medium Bus', plate: 'BA 7031 MB', capacity: 33, status: 'Perbaikan', icon: 'bi-bus-front-fill', hargaSewa: 2800000, biayaOperasional: 1100000, requiredLicense: 'B1 Umum', lastService: '2025-07-20', nextService: '2025-11-20' },
        // Big Bus - 5 unit
        { id: 25, name: 'Big Bus SR-41', type: 'Big Bus', plate: 'BA 7041 BB', capacity: 45, status: 'Tersedia', icon: 'bi-bus-front-fill', hargaSewa: 4000000, biayaOperasional: 1500000, requiredLicense: 'B2 Umum', lastService: '2025-09-01', nextService: '2025-12-01' },
        { id: 26, name: 'Big Bus SR-42', type: 'Big Bus', plate: 'BA 7042 BB', capacity: 45, status: 'Tersedia', icon: 'bi-bus-front-fill', hargaSewa: 4000000, biayaOperasional: 1500000, requiredLicense: 'B2 Umum', lastService: '2025-09-01', nextService: '2025-12-01' },
        { id: 27, name: 'Big Bus SR-43', type: 'Big Bus', plate: 'BA 7043 BB', capacity: 45, status: 'Tersedia', icon: 'bi-bus-front-fill', hargaSewa: 4000000, biayaOperasional: 1500000, requiredLicense: 'B2 Umum', lastService: '2025-09-01', nextService: '2025-12-01' },
        { id: 28, name: 'Big Bus SR-44', type: 'Big Bus', plate: 'BA 7044 BB', capacity: 45, status: 'Tersedia', icon: 'bi-bus-front-fill', hargaSewa: 4000000, biayaOperasional: 1500000, requiredLicense: 'B2 Umum', lastService: '2025-09-01', nextService: '2025-12-01' },
        { id: 29, name: 'Big Bus SR-45', type: 'Big Bus', plate: 'BA 7045 BB', capacity: 45, status: 'Tersedia', icon: 'bi-bus-front-fill', hargaSewa: 4000000, biayaOperasional: 1500000, requiredLicense: 'B2 Umum', lastService: '2025-09-01', nextService: '2025-12-01' },
    ];

    const drivers = [
        { id: 101, name: 'Budi Santoso', licenseType: 'A Umum', phone: '081234567890', status: 'Dalam Perjalanan' },
        { id: 102, name: 'Joko Susilo', licenseType: 'B1 Umum', phone: '081234567891', status: 'Dalam Perjalanan' },
        { id: 103, name: 'Anton Wijaya', licenseType: 'A Umum', phone: '081234567892', status: 'Standby' },
        { id: 104, name: 'Eko Prasetyo', licenseType: 'B2 Umum', phone: '081234567893', status: 'Dalam Perjalanan' },
        { id: 105, name: 'Slamet Riyadi', licenseType: 'B2 Umum', phone: '081234567894', status: 'Libur' },
        { id: 106, name: 'Doni Firmansyah', licenseType: 'A Umum', phone: '081234567895', status: 'Standby' },
        { id: 107, name: 'Agus Setiawan', licenseType: 'B1 Umum', phone: '081234567896', status: 'Standby' },
        { id: 108, name: 'Rahmat Hidayat', licenseType: 'A Umum', phone: '081234567897', status: 'Standby' },
        { id: 109, name: 'Zainal Abidin', licenseType: 'B1 Umum', phone: '081234567898', status: 'Standby' },
        { id: 110, name: 'Fajar Nugraha', licenseType: 'B2 Umum', phone: '081234567899', status: 'Dalam Perjalanan' },
        { id: 111, name: 'Hendri Saputra', licenseType: 'A Umum', phone: '081234567880', status: 'Standby' },
        { id: 112, name: 'Dedi Kurniawan', licenseType: 'B1 Umum', phone: '081234567881', status: 'Libur' },
    ];

    const trips = [
        { id: 1, fleetId: 1, driverId: 101, type: 'Rute', origin: 'Padang', destination: 'Bukittinggi', departureTime: getTodayISO(8, 0), arrivalTime: getTodayISO(10, 30), pax: '7/7', status: 'Berangkat' },
        { id: 2, fleetId: 14, driverId: 102, type: 'Rental', notes: 'Dinas Pariwisata - Tour de Singkarak', departureTime: getTodayISO(7, 30), arrivalTime: getTodayISO(18, 0), pax: 'Carter', status: 'Rental' },
        { id: 3, fleetId: 3, driverId: 103, type: 'Rute', origin: 'Padang', destination: 'Payakumbuh', departureTime: getTodayISO(10, 0), arrivalTime: getTodayISO(13, 0), pax: '5/7', status: 'Boarding' },
        { id: 4, fleetId: 25, driverId: 104, type: 'Rental', notes: 'Rombongan Kemenkes RI (3 Hari)', departureTime: getTodayISO(9, 0), arrivalTime: getFutureISO(2, 17, 0), pax: 'Carter', status: 'Rental' },
        { id: 5, fleetId: 26, driverId: 110, type: 'Rute', origin: 'Bukittinggi', destination: 'Padang', departureTime: getTodayISO(14, 0), arrivalTime: getTodayISO(16, 30), pax: '40/45', status: 'Berangkat' },
        { id: 6, fleetId: 4, driverId: 106, type: 'Rute', origin: 'Padang', destination: 'Solok', departureTime: getTodayISO(16, 0), arrivalTime: getTodayISO(18, 0), pax: 'Full', status: 'On Time' },
        { id: 7, fleetId: 2, driverId: 108, type: 'Rute', origin: 'Painan', destination: 'Padang', departureTime: getYesterdayISO(18, 0), arrivalTime: getTodayISO(8, 30), pax: '6/7', status: 'Tiba' },
        { id: 8, fleetId: 15, driverId: 107, type: 'Rental', notes: "Trip Keluarga Bpk. Ahmad", departureTime: getYesterdayISO(9, 0), arrivalTime: getYesterdayISO(20, 0), pax: 'Carter', status: 'Tiba' },
    ];
    
    const locations = [
        { name: 'Padang', code: 'PDG' }, { name: 'Bukittinggi', code: 'BKT' }, { name: 'Payakumbuh', code: 'PYH' }, { name: 'Solok', code: 'SLK' }, { name: 'Pariaman', code: 'PRM' }, { name: 'Painan', code: 'PNN' }, { name: 'Sawahlunto', code: 'SWL' }, { name: 'Batusangkar', code: 'BTS' }
    ];

    const statuses = ['On Time', 'Boarding', 'Berangkat', 'Tiba', 'Tertunda', 'Batal', 'Rental'];
    
    // Data untuk aplikasi Tour
    const tourData = {
        costs: {
            driverMeal: 100000,
            driverLodge: 100000,
            documentation: 2500000,
            outboundPerPax: 150000
        },
        cities: ['Padang', 'Bukittinggi', 'Payakumbuh', 'Batusangkar', 'Alahan Panjang'],
        allAccommodations: [
            { name: 'Hotel Santika Bukittinggi', city: 'Bukittinggi', type: 'Hotel', pricePerNight: 550000 }, { name: 'The Hills Bukittinggi Hotel', city: 'Bukittinggi', type: 'Hotel', pricePerNight: 700000 }, { name: 'Grand Rocky Hotel', city: 'Bukittinggi', type: 'Hotel', pricePerNight: 650000 }, { name: 'Villa Ngarai Sianok', city: 'Bukittinggi', type: 'Villa', pricePerNight: 1800000 }, { name: 'Mercure Padang', city: 'Padang', type: 'Hotel', pricePerNight: 800000 }, { name: 'Santika Premiere Padang', city: 'Padang', type: 'Hotel', pricePerNight: 750000 }, { name: 'Emersia Hotel Batusangkar', city: 'Batusangkar', type: 'Hotel', pricePerNight: 600000 }, { name: 'Pagaruyung Hotel', city: 'Batusangkar', type: 'Hotel', pricePerNight: 450000 }, { name: 'Alahan Panjang Resort', city: 'Alahan Panjang', type: 'Villa', pricePerNight: 1500000 }, { name: 'Villa Danau Diatas', city: 'Alahan Panjang', type: 'Villa', pricePerNight: 2000000 }, { name: 'Villa Kayu Putih', city: 'Alahan Panjang', type: 'Villa', pricePerNight: 1700000 },
        ],
        allDestinations: [
            { name: 'Pantai Air Manis', city: 'Padang', category: 'Alam', category_class: 'bg-primary' }, { name: 'Jembatan Siti Nurbaya', city: 'Padang', category: 'Budaya', category_class: 'bg-info' }, { name: 'Masjid Raya Sumbar', city: 'Padang', category: 'Budaya', category_class: 'bg-info' }, { name: 'RM Lamun Ombak', city: 'Padang', category: 'Kuliner', category_class: 'bg-warning text-dark' }, { name: 'Ngarai Sianok', city: 'Bukittinggi', category: 'Alam', category_class: 'bg-primary' }, { name: 'Jam Gadang', city: 'Bukittinggi', category: 'Budaya', category_class: 'bg-info' }, { name: 'Lobang Jepang', city: 'Bukittinggi', category: 'Budaya', category_class: 'bg-info' }, { name: 'Nasi Kapau Pasar Atas', city: 'Bukittinggi', category: 'Kuliner', category_class: 'bg-warning text-dark' }, { name: 'Lembah Harau', city: 'Payakumbuh', category: 'Alam', category_class: 'bg-primary' }, { name: 'Kelok 9', city: 'Payakumbuh', category: 'Alam', category_class: 'bg-primary' }, { name: 'RM Pongek Situjuah', city: 'Payakumbuh', category: 'Kuliner', category_class: 'bg-warning text-dark' }, { name: 'Istano Basa Pagaruyung', city: 'Batusangkar', category: 'Budaya', category_class: 'bg-info' }, { name: 'Kopi Kinikko', city: 'Batusangkar', category: 'Kuliner', category_class: 'bg-warning text-dark' }, { name: 'Danau Kembar (Diatas & Dibawah)', city: 'Alahan Panjang', category: 'Alam', category_class: 'bg-primary' }, { name: 'Kebun Teh Alahan Panjang', city: 'Alahan Panjang', category: 'Alam', category_class: 'bg-primary' },
        ]
    };

    return {
        fleet,
        drivers,
        trips,
        locations,
        statuses,
        tourData // Tambahkan data tur ke data awal
    };
}


const DataManager = {
    loadData: function() {
        const savedData = localStorage.getItem('sutanRayaOperationalData');
        if (savedData) {
            return JSON.parse(savedData);
        } else {
            const initialData = getInitialData();
            this.saveData(initialData);
            return initialData;
        }
    },

    saveData: function(data) {
        localStorage.setItem('sutanRayaOperationalData', JSON.stringify(data));
        window.dispatchEvent(new CustomEvent('storageUpdated'));
    },
    
    addTrip: function(newTripData) {
        const data = this.loadData();
        const newTrip = {
            id: Date.now() + Math.random(), // ID lebih unik
            status: 'On Time',
            ...newTripData
        };
        data.trips.push(newTrip);
        this.saveData(data);
        console.log("Trip added:", newTrip);
        return newTrip;
    },
};