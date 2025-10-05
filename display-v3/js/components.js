// Objek ini akan berisi definisi semua komponen halaman.
const viewComponents = {
    // Setiap kunci (misal: 'dashboard') harus sama dengan nama view di data() app.js
    dashboard: {
        template: '#dashboard-view',
    },
    inventaris: {
        template: '#inventaris-view',
    },
    drivers: {
        template: '#drivers-view',
    },
    history: {
        template: '#history-view',
    },
    display: {
        template: '#display-view',
    },
    admin: {
        template: '#admin-view',
    },
};

// Fungsi untuk menyiapkan semua komponen view
// Ini akan membuat setiap komponen bisa mengakses data dan method dari instance Vue utama.
// Ini adalah kunci agar data seperti 'filteredFleet' bisa diakses di dalam template 'inventaris-view'.
function setupViewComponents(app) {
    for (const [name, component] of Object.entries(viewComponents)) {
        app.component(name, {
            ...component,
            // 'inject' memungkinkan komponen "anak" menerima properti dari "induk"
            inject: ['rootData', 'rootMethods'],
            data() {
                // Membuat data dari root tersedia di dalam komponen ini
                return { rootData: this.rootData };
            },
            computed: {
                // Membuat semua method dari root tersedia di dalam komponen ini
                rootMethods() {
                    return this.rootMethods;
                }
            },
            // Menambahkan method-method helper agar bisa dipanggil langsung di template
            // Contoh: {{ formatTime(trip.departureTime) }}
            methods: {
                formatRupiah,
                formatTime,
                formatFullDate,
                formatDateTime,
                isServiceDue,
                getVehicleStatusClass,
                getDriverStatusClass
            }
        });
    }
}