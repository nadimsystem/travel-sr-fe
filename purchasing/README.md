# 📦 Purchasing Module - Sutan Raya Fleet Management

Modul Purchasing untuk mengelola inventaris sparepart, aset, supplier, dan purchase order.

## 🚀 Setup Database

### Local Development (XAMPP)

1. Pastikan XAMPP sudah berjalan (Apache + MySQL)
2. Buka browser dan akses:
   ```
   http://localhost/travel-sr-fe/purchasing/setup_purchasing_db.php
   ```
3. Jalankan juga setup untuk tabel deployment:
   ```
   http://localhost/travel-sr-fe/purchasing/setup_deployment_tables.php
   ```

### Online/Production Database

1. Buka cPanel atau phpMyAdmin di hosting Anda
2. Pilih database `sutanraya_v11` (atau database yang Anda gunakan)
3. Klik tab "Import" atau "SQL"
4. Upload dan jalankan file: `purchasing_online_setup.sql`
5. Tunggu sampai proses selesai

## 📂 Struktur File

```
purchasing/
├── api.php                          # API endpoint untuk semua operasi
├── index.php                        # Dashboard overview
├── request.php                      # Halaman permintaan barang
├── po.php                          # Purchase Orders
├── inventory.php                    # Stock & Assets management
├── suppliers.php                    # Supplier management
├── reports.php                      # Cost analysis & reports
├── implementation.php               # Deployment tracking
├── app.js                          # Shared JavaScript utilities
├── components/
│   └── sidebar.php                 # Navigation sidebar
├── setup_purchasing_db.php         # Setup lokal database
├── setup_deployment_tables.php     # Setup tabel deployment
├── purchasing_online_setup.sql     # Setup online database (PENTING!)
└── README.md                       # File ini

```

## 🔧 Konfigurasi Database

Jika database Anda berbeda, edit file `api.php`:

```php
$host = "localhost";           // Host database
$username = "root";            // Username database
$password = "";                // Password database
$database = "sutanraya_v11";   // Nama database
```

## 📊 Tabel Database

Modul ini menggunakan 9 tabel utama:

1. **purchasing_items** - Katalog sparepart dan barang
2. **purchasing_assets** - Aset perusahaan (kendaraan, mesin, dll)
3. **purchasing_requests** - Request pembelian barang
4. **purchasing_request_items** - Detail item per request
5. **purchasing_deployments** - Log deployment barang ke armada
6. **purchasing_receiving** - Penerimaan barang dari supplier
7. **suppliers** - Data supplier dan kontak
8. **purchasing_orders** - Purchase Order ke supplier
9. **purchasing_order_items** - Detail item per PO

## 🎯 Fitur Utama

### 1. Dashboard (index.php)
- Overview metrics
- Quick navigation cards
- Alerts untuk stok menipis

### 2. Request Part (request.php)
- Buat permintaan barang baru
- Search dari katalog atau tambah item baru
- Track status request
- Pilih armada tujuan

### 3. Purchase Orders (po.php)
- Buat PO ke supplier
- Track status PO
- Generate nomor PO otomatis
- Link ke supplier

### 4. Inventory (inventory.php)
- Kelola stok sparepart
- Manajemen aset perusahaan
- Low stock alerts
- Update stock & harga

### 5. Suppliers (suppliers.php)
- Database supplier
- Rating supplier
- Kontak info lengkap
- Payment terms

### 6. Reports (reports.php)
- Cost analysis
- Purchase history
- Spending trends

### 7. Implementation (implementation.php)
- Track deployment barang ke armada
- Upload foto bukti
- History deployment

## 🔌 API Endpoints

Base URL: `http://localhost/travel-sr-fe/purchasing/api.php`

### Items
- `GET ?action=get_items` - Get all items
- `POST ?action=create_item` - Create new item
- `POST ?action=update_item` - Update item
- `GET ?action=delete_item&id=X` - Delete item

### Assets
- `GET ?action=get_assets` - Get all assets
- `POST ?action=create_asset` - Create new asset
- `POST ?action=update_asset` - Update asset
- `GET ?action=delete_asset&id=X` - Delete asset

### Requests
- `GET ?action=get_requests` - Get all requests
- `GET ?action=get_request&id=X` - Get single request
- `POST ?action=create_request` - Create new request
- `POST ?action=update_request_status` - Update request status

### Suppliers
- `GET ?action=get_suppliers` - Get all suppliers
- `POST ?action=create_supplier` - Create new supplier
- `POST ?action=update_supplier` - Update supplier
- `GET ?action=delete_supplier&id=X` - Delete supplier

### Purchase Orders
- `GET ?action=get_purchase_orders` - Get all POs
- `GET ?action=get_purchase_order&id=X` - Get single PO
- `POST ?action=create_purchase_order` - Create new PO
- `POST ?action=update_purchase_order` - Update PO
- `GET ?action=delete_purchase_order&id=X` - Delete PO

### Deployments
- `GET ?action=get_deployments` - Get all deployments
- `POST ?action=create_deployment` - Record deployment

### Receiving
- `GET ?action=get_receiving` - Get all receiving records
- `POST ?action=create_receiving` - Record item receipt

### Stats
- `GET ?action=get_inventory_stats` - Get inventory statistics
- `GET ?action=get_fleet` - Get fleet list

## 🎨 Tema Dark/Light Mode

Semua halaman support dark mode. Toggle ada di header kanan atas.
Setting tersimpan di localStorage browser.

## 📱 Responsive Design

- Desktop: Full layout dengan sidebar
- Tablet: Sidebar collapsible
- Mobile: Hamburger menu

## 🔒 Security Notes

- Gunakan prepared statements untuk production
- Update real_escape_string menjadi prepared statements
- Implementasikan proper user authentication
- Validasi semua input di server-side

## 🐛 Troubleshooting

### Database connection failed
- Pastikan MySQL berjalan
- Check username dan password di `api.php`
- Pastikan database `sutanraya_v11` exists

### Tabel tidak ditemukan
- Jalankan `setup_purchasing_db.php` untuk lokal
- Import `purchasing_online_setup.sql` untuk online

### API tidak response
- Check browser console untuk error
- Pastikan `api.php` accessible
- Check PHP error log di XAMPP

### Dark mode tidak berfungsi
- Clear browser cache
- Check localStorage browser
- Reload halaman

## 💡 Tips

1. **Stok Otomatis**: Saat deployment, stok berkurang otomatis. Saat receiving, stok bertambah.
2. **Kode Unik**: Gunakan kode yang konsisten (ENG-001, AC-001, dll)
3. **Supplier Rating**: Rate supplier berdasarkan performa (0-5)
4. **Low Stock Alert**: Set `min_stock` yang realistis per item

## 📞 Support

Jika ada masalah atau pertanyaan, hubungi tim developer.

## 📝 Changelog

### Version 1.0.0 (2026-01-04)
- Initial release
- Complete CRUD untuk semua modul
- Dark mode support
- Responsive design
- API lengkap dengan semua endpoint

---

**Last Updated**: 2026-01-04  
**Developer**: Sutan Raya IT Team
