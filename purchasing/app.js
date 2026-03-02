/**
 * Purchasing Module Application Logic
 * Sutan Raya Fleet Management System
 */

console.log('Purchasing Module Loaded');

// Global helper functions can be added here
function formatRupiah(value) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(value);
}
