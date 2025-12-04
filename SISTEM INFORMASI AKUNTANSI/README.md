# Sistem Informasi Akuntansi

Website PHP untuk RANCANGAN BASIS DATA SISTEM INFORMASI AKUNTANSI dengan fitur CRUD lengkap dan sistem role-based access control.

## Fitur

### 1. CRUD Operations (3 Fitur Utama)
- **Akun (Chart of Accounts)** - Manajemen akun akuntansi
- **Jurnal Umum** - Pencatatan jurnal dengan sistem debit-kredit
- **Transaksi** - Manajemen transaksi keuangan

### 2. Role-Based Access Control (3 Role)
- **Admin** - Akses penuh ke semua fitur termasuk manajemen user
- **Akuntan** - Dapat mengelola akun, jurnal, dan transaksi
- **Viewer** - Hanya dapat melihat data (read-only)

### 3. User Management
- CRUD lengkap untuk manajemen pengguna
- Hanya Admin yang dapat mengakses fitur ini

## Instalasi

### 1. Persyaratan
- XAMPP (PHP 7.4+ dan MySQL)
- Web Browser (Chrome, Firefox, Edge, dll)

### 2. Setup Database
1. Buka phpMyAdmin di browser: `http://localhost/phpmyadmin`
2. Buat database baru dengan nama `sistem_akuntansi`
3. Import file `database.sql` ke database tersebut
4. Atau jalankan query SQL dari file `database.sql` secara manual

### 3. Konfigurasi Database
Edit file `config/database.php` jika diperlukan:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'sistem_akuntansi');
```

### 4. Akses Aplikasi
1. Pastikan XAMPP Apache dan MySQL sudah running
2. Buka browser dan akses: `http://localhost/SISTEM INFORMASI AKUNTANSI/login.php`

### 5. Login Default
- **Username:** admin
- **Password:** admin123

## Struktur Database

### Tabel Utama:
- `roles` - Tabel untuk role pengguna
- `users` - Tabel untuk data pengguna
- `akun` - Tabel untuk chart of accounts
- `jurnal` - Tabel untuk jurnal umum
- `detail_jurnal` - Tabel detail jurnal (debit-kredit)
- `transaksi` - Tabel untuk transaksi keuangan

## Fitur CRUD

### 1. Akun (Chart of Accounts)
- **Create:** Tambah akun baru dengan kode, nama, jenis, dan saldo awal
- **Read:** Lihat daftar semua akun dengan fitur pencarian
- **Update:** Edit data akun yang sudah ada
- **Delete:** Hapus akun (dengan validasi jika masih digunakan)

### 2. Jurnal Umum
- **Create:** Buat jurnal baru dengan multiple entries (debit-kredit)
- **Read:** Lihat daftar jurnal dengan detail lengkap
- **Update:** Edit jurnal dan detailnya
- **Delete:** Hapus jurnal beserta detailnya

### 3. Transaksi
- **Create:** Tambah transaksi baru
- **Read:** Lihat daftar transaksi dengan fitur pencarian
- **Update:** Edit data transaksi
- **Delete:** Hapus transaksi

### 4. User Management (Admin Only)
- **Create:** Tambah user baru dengan role tertentu
- **Read:** Lihat daftar semua user
- **Update:** Edit data user termasuk password dan role
- **Delete:** Hapus user (tidak bisa menghapus diri sendiri)

## Hak Akses Berdasarkan Role

### Admin
- ✅ Akses penuh ke semua fitur
- ✅ Manajemen User (CRUD)
- ✅ Manajemen Akun (CRUD)
- ✅ Manajemen Jurnal (CRUD)
- ✅ Manajemen Transaksi (CRUD)

### Akuntan
- ✅ Manajemen Akun (CRUD)
- ✅ Manajemen Jurnal (CRUD)
- ✅ Manajemen Transaksi (CRUD)
- ❌ Tidak dapat mengakses User Management

### Viewer
- ✅ Melihat Dashboard
- ✅ Melihat Akun (Read-only)
- ✅ Melihat Jurnal (Read-only)
- ✅ Melihat Transaksi (Read-only)
- ❌ Tidak dapat Create, Update, atau Delete

## Teknologi yang Digunakan

- **Backend:** PHP (Native)
- **Database:** MySQL
- **Frontend:** HTML, CSS, JavaScript
- **Styling:** Custom CSS dengan design modern

## Catatan Penting

1. Pastikan semua file berada di folder `htdocs` XAMPP
2. Database harus dibuat terlebih dahulu sebelum menjalankan aplikasi
3. Password default untuk semua user adalah `admin123`
4. Disarankan untuk mengubah password setelah login pertama kali

## Troubleshooting

### Error Koneksi Database
- Pastikan MySQL sudah running di XAMPP
- Periksa konfigurasi di `config/database.php`
- Pastikan database `sistem_akuntansi` sudah dibuat

### Error Session
- Pastikan session sudah di-start di setiap halaman
- Periksa file `config/auth.php`

### Error Path/URL
- Pastikan struktur folder sesuai dengan yang ada
- Periksa path relatif di file `includes/header.php`

## Lisensi

Proyek ini dibuat untuk keperluan akademik/pendidikan.

