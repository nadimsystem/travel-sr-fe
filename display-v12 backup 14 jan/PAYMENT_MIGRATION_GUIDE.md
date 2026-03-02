# Payment Management - Migration Guide

## Database Schema Installation

Jalankan SQL berikut melalui **phpMyAdmin** atau **MySQL console**:

### Lokasi File
```
/Applications/XAMPP/xamppfiles/htdocs/travel-sr-fe/display-v11/payment_schema.sql
```

### Cara Install via phpMyAdmin:
1. Buka phpMyAdmin (http://localhost/phpmyadmin)
2. Pilih database `sutanraya_v11`
3. Klik tab "SQL"
4. Copy-paste isi file `payment_schema.sql` atau gunakan "Import"
5. Klik "Go"

### Atau via Terminal (jika MySQL ada di PATH):
```bash
cd /Applications/XAMPP/xamppfiles/htdocs/travel-sr-fe/display-v11
/Applications/XAMPP/xamppfiles/bin/mysql -u root sutanraya_v11 < payment_schema.sql
```

## Fitur yang Ditambahkan

### 1. **Billing Management Dashboard** (`billing_management.php`)
   - Tracking booking belum lunas
   - Statistik: Total Outstanding, DP, Overdue
   - Daftar pembayaran terakhir (7 hari)
   - Form tambah pembayaran untuk booking

### 2. **Backend API** (sudah ditambahkan ke `api.php`)
   - **add_payment**: Tambah pembayaran ke booking
   - **get_payment_history**: Lihat riwayat pembayaran
   - **get_outstanding_bookings**: Daftar booking belum lunas
   - **get_billing_report**: Data dashboard penagihan
   - **update_booking_payment**: Support split payment

###3. **Database Schema** (`payment_schema.sql`)
   - Table `payment_transactions`: Tracking multiple pembayaran per booking
   - New columns di `bookings`: 
     - `payment_remaining`: Sisa tagihan
     - `payment_type`: single/split/installment
     - `last_payment_date`: Tanggal bayar terakhir
     - `is_fully_paid`: Status lunas
   - Views:
     - `v_outstanding_bookings`: Query cepat booking belum lunas
     - `v_payment_summary`: Ringkasan pembayaran per booking

## File yang Dibuat/Dimodifikasi

### Files Baru:
- ✅ `payment_schema.sql` - Database migration
- ✅ `billing_management.php` - Dashboard penagihan
- ✅ `js/billing.js` - JavaScript untuk billing page

### Files Dimodifikasi:
- ✅ `api.php` - Tambah 5 payment endpoints
- ✅ `components/sidebar.php` - Tambah menu "Penagihan"

### Files yang Perlu Update (Next Steps):
- ⏳ `booking_management.php` - Tambah payment history view
- ⏳ `booking_travel.php` - UI untuk split payment
- ⏳ `app.js` - Integrasi payment functions

## Testing Checklist

- [ ] Install database schema via phpMyAdmin
- [ ] Akses billing dashboard: http://localhost/travel-sr-fe/display-v11/billing_management.php
- [ ] Test tambah pembayaran ke booking belum lunas
- [ ] Verify payment transactions tersimpan
- [ ] Check outstanding bookings calculation
- [ ] Test billing statistics accuracy
