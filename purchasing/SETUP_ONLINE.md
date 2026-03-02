# 🌐 Setup Database Online - Panduan Cepat

## Langkah-langkah Setup Database di Hosting Online

### 1. Login ke cPanel
- Buka browser dan login ke cPanel hosting Anda
- Biasanya: `https://namadomain.com/cpanel`

### 2. Buka phpMyAdmin
- Di bagian "Databases", klik **phpMyAdmin**
- Pilih database `sutanraya_v11` (atau nama database yang Anda gunakan di hosting)

### 3. Import File SQL
- Klik tab **"Import"** di bagian atas
- Klik tombol **"Choose File"** atau **"Browse"**
- Pilih file: `purchasing_online_setup.sql`
- Scroll ke bawah dan klik **"Go"** atau **"Import"**

### 4. Tunggu Proses Selesai
- Akan muncul pesan sukses: "Import has been successfully finished"
- Jika ada error, catat pesan errornya

### 5. Verifikasi Tables
- Di sidebar kiri phpMyAdmin, klik database Anda
- Pastikan tabel-tabel ini sudah ada:
  - ✅ purchasing_items
  - ✅ purchasing_assets
  - ✅ purchasing_requests
  - ✅ purchasing_request_items
  - ✅ purchasing_deployments
  - ✅ purchasing_receiving
  - ✅ suppliers
  - ✅ purchasing_orders  
  - ✅ purchasing_order_items

### 6. Update Konfigurasi API
Edit file `api.php` dengan kredensial database online Anda:

```php
$host = "localhost";              // Biasanya localhost
$username = "nama_user_db";       // Ganti dengan username database Anda
$password = "password_db";        // Ganti dengan password database Anda
$database = "sutanraya_v11";      // Ganti dengan nama database Anda
```

### 7. Test API
Buka browser dan akses:
```
https://namadomain.com/travel-sr-fe/purchasing/api.php?action=get_items
```

Harusnya muncul JSON response dengan data items.

## 🚨 Troubleshooting

### Error: Table already exists
- Tabel sudah ada sebelumnya
- Aman diabaikan, data lama tidak akan terhapus

### Error: Access denied
- Username atau password database salah
- Cek kembali konfigurasi di `api.php`

### Error: Database not found
- Nama database salah
- Pastikan database sudah dibuat di cPanel

### Error: Max file size exceeded
- File SQL terlalu besar untuk di-upload
- Gunakan fitur "SQL" di phpMyAdmin
- Copy-paste isi file `purchasing_online_setup.sql` ke text area
- Klik "Go"

## 📋 Catatan Penting

1. **Backup Database**: Selalu backup database sebelum import
2. **Charset**: Gunakan `utf8mb4_unicode_ci` untuk support emoji dan karakter khusus
3. **Permissions**: Pastikan user database punya permission untuk CREATE dan INSERT
4. **File Upload Limit**: Jika file terlalu besar, gunakan metode copy-paste SQL

## ✅ Verifikasi Sukses

Setelah import berhasil, Anda akan punya:
- **30+ sample items** (sparepart, oli, ban, dll)
- **6 sample assets** (kendaraan, mesin, dll)
- **8 sample suppliers** dengan data lengkap
- Semua tabel siap digunakan

## 🎯 Next Steps

1. Login ke aplikasi purchasing
2. Cek apakah data sample sudah muncul
3. Mulai input data real
4. Setup user permissions jika diperlukan

## 📞 Butuh Bantuan?

Jika mengalami kendala:
1. Screenshot pesan error
2. Catat langkah yang sudah dilakukan
3. Hubungi tim IT atau developer

---

**File SQL**: `purchasing_online_setup.sql`  
**Lokasi**: `/purchasing/purchasing_online_setup.sql`  
**Terakhir diupdate**: 2026-01-04
