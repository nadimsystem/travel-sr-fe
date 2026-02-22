# Loading Optimizer - Panduan Penggunaan

## Deskripsi
File `js/loading-optimizer.js` adalah sistem loading optimization opsional untuk mengatasi masalah:
- Flash UI error sebelum Vue mount
- Loading popup yang muncul tiba-tiba
- Pengalaman loading yang tidak smooth
- Halaman terasa berat saat pertama kali dibuka

## Fitur
✅ **Prevent FOUC** - Menghilangkan flash UI sebelum aplikasi siap  
✅ **Skeleton Loading** - Loading animation yang smooth  
✅ **Resource Preloading** - Preload font, CSS, dan library penting  
✅ **Cache Management** - Cache resource untuk kunjungan berikutnya  
✅ **Error Suppression** - Menyembunyikan error console saat loading  
✅ **Smooth Transitions** - Transisi halaman yang lebih halus  

## Cara Mengaktifkan

### Opsi 1: Include di setiap file PHP (Recommended)
Tambahkan di bagian `<head>` **sebelum** script lain:

```html
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sutan Raya</title>
    
    <!-- Loading Optimizer - HARUS DI ATAS -->
    <script src="js/loading-optimizer.js"></script>
    
    <!-- Script lainnya -->
    <script src="https://cdn.tailwindcss.com"></script>
    ...
</head>
```

### Opsi 2: Auto-include via base.php
Jika menggunakan `base.php` atau file include lain, tambahkan di sana:

```php
// base.php
<!DOCTYPE html>
<html>
<head>
    <script src="<?= $base_path ?>js/loading-optimizer.js"></script>
    <!-- ... -->
</head>
```

## Testing

### Sebelum Menggunakan
1. Buka halaman (misal: `paket.php`)
2. Perhatikan ada "flash" atau "kedip" UI sebelum halaman stabil
3. Kadang muncul popup error atau loading sebelum masuk halaman

### Sesudah Menggunakan
1. Halaman akan show loading spinner yang smooth
2. Tidak ada flash UI atau error popup
3. Transisi lebih cepat dan mulus
4. Repeat visit lebih cepat karena caching

## Cara Menonaktifkan

**Super Mudah!** Ada 2 cara:

### Cara 1: Hapus Include Script
Hapus baris ini dari file HTML/PHP:
```html
<script src="js/loading-optimizer.js"></script>
```

### Cara 2: Hapus File
Hapus file `js/loading-optimizer.js` sepenuhnya.

❗ **PENTING**: Aplikasi akan tetap berjalan normal tanpa file ini. Tidak ada dependency atau breaking changes.

## Kustomisasi

### Mengubah Durasi Loading Spinner
Edit di `loading-optimizer.js` baris ~108:

```javascript
const MAX_CHECKS = 50; // 50 * 100ms = 5 detik maksimal
```

Ubah sesuai kebutuhan:
- `30` = 3 detik
- `50` = 5 detik (default)
- `100` = 10 detik

### Mengaktifkan Error Suppression
Uncomment baris 213-216 di `loading-optimizer.js`:

```javascript
// Sebelum (dinonaktifkan):
// suppressInitialErrors();
// document.addEventListener('DOMContentLoaded', () => {
//     setTimeout(restoreConsoleErrors, 2000);
// });

// Sesudah (aktif):
suppressInitialErrors();
document.addEventListener('DOMContentLoaded', () => {
    setTimeout(restoreConsoleErrors, 2000);
});
```

### Custom Loading Spinner
Edit styles di baris ~42 untuk mengubah tampilan spinner.

## Clear Cache

Jika ada masalah cache, buka console browser dan ketik:

```javascript
SRCacheManager.clear()
```

Atau hapus localStorage dan refresh:
```javascript
localStorage.clear()
location.reload()
```

## File Yang Perlu Diupdate

Untuk implementasi penuh, tambahkan script loading optimizer di file-file berikut:

1. ✅ `paket.php`
2. ✅ `package_shipping.php`  
3. ✅ `index.php` (dashboard)
4. ✅ `booking_management.php`
5. ✅ `booking_travel.php`
6. ✅ `booking_bus.php`
7. ✅ `dispatcher.php`
8. ✅ `schedule.php`
9. ✅ `assets.php`
10. ✅ `route_management.php`
11. ✅ `reports.php`
12. ✅ `penagihan.php`
13. ✅ `pembatalan.php`
14. ✅ `manifest.php`

## FAQ

### Q: Apakah wajib digunakan?
**A:** Tidak. File ini 100% opsional. Aplikasi berjalan normal tanpanya.

### Q: Apakah mempengaruhi performance?
**A:** Justru meningkatkan! File ini kecil (~8KB) tapi mengoptimasi loading dan caching.

### Q: Bagaimana jika ada bug?
**A:** Cukup hapus include script atau file-nya. Tidak akan break aplikasi.

### Q: Apakah kompatibel dengan semua browser?
**A:** Ya. Menggunakan vanilla JavaScript dan fallback untuk browser lama.

### Q: Perlu install dependency?
**A:** Tidak. Pure JavaScript, no dependencies.

## Support

Jika ada masalah atau pertanyaan, cek:
1. Console browser (F12) untuk error
2. Performance monitor di console (localhost only)
3. Network tab untuk lihat resource loading

---

**Version:** 12.12  
**Last Updated:** 2026-01-14  
**Status:** Production Ready ✅
