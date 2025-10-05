// Mengubah angka menjadi format mata uang Rupiah yang rapi.
function formatRupiah(number) {
    if (isNaN(number)) return 'Rp 0';
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(number);
}

// Mendapatkan tanggal hari ini dalam format bahasa Indonesia.
function getCurrentDate() {
    return new Date().toLocaleDateString('id-ID', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

// Mengubah objek Date atau string ISO menjadi format waktu (HH:MM atau HH:MM:SS).
function formatTime(date, withSeconds = false) {
    const d = new Date(date);
    if (isNaN(d.getTime())) return '--:--';
    const options = {
        hour: '2-digit',
        minute: '2-digit',
        hour12: false // Menggunakan format 24 jam
    };
    if (withSeconds) options.second = '2-digit';
    return d.toLocaleTimeString('id-ID', options).replace(/\./g, ':'); // Mengganti titik dengan titik dua
}

// Mengubah string ISO menjadi format tanggal pendek (DD Mmm YYYY).
function formatFullDate(isoString) {
    if (!isoString) return '-';
    return new Date(isoString).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
    });
}

// Menggabungkan tanggal dan waktu dari string ISO.
function formatDateTime(isoString) {
    if (!isoString) return '-';
    const d = new Date(isoString);
    if (isNaN(d.getTime())) return '-';
    const datePart = d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short' });
    const timePart = formatTime(d);
    return `${datePart} ${timePart}`;
}

// Mengecek apakah tanggal servis berikutnya sudah dekat (dalam 30 hari).
function isServiceDue(nextServiceDate, now) {
    if(!nextServiceDate) return false;
    const nextService = new Date(nextServiceDate);
    if(isNaN(nextService.getTime())) return false;

    const thirtyDaysFromNow = new Date(now);
    thirtyDaysFromNow.setDate(now.getDate() + 30);
    return nextService < thirtyDaysFromNow;
}

// Memberikan kelas warna Tailwind berdasarkan status armada.
function getVehicleStatusClass(status) {
    const classes = {
        'Tersedia': 'bg-green-100 text-green-800',
        'Dalam Perjalanan': 'bg-blue-100 text-blue-800',
        'Perbaikan': 'bg-red-100 text-red-800'
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
}

// Memberikan kelas warna Tailwind berdasarkan status supir.
function getDriverStatusClass(status) {
    const classes = {
        'Standby': 'bg-green-100 text-green-800',
        'Dalam Perjalanan': 'bg-blue-100 text-blue-800',
        'Libur': 'bg-gray-200 text-gray-700'
    };
    return classes[status] || 'bg-yellow-100 text-yellow-800'; // Default untuk status lain
}

// Menghitung durasi dalam satuan hari (minimal 1 hari).
function calculateDurationInDays(start, end) {
    const startDate = new Date(start);
    const endDate = new Date(end);
    if (isNaN(startDate.getTime()) || isNaN(endDate.getTime()) || endDate < startDate) return 1;
    const diffTime = Math.abs(endDate - startDate);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays < 1 ? 1 : diffDays;
}