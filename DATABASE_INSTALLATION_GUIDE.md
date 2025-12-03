# Panduan Instalasi Database

## Ringkasan

Sistem ini menyediakan **2 metode instalasi database** yang bisa Anda pilih sesuai kebutuhan:

1. **Web Installer** - Interface grafis melalui browser (Recommended)
2. **Command Line** - Script PHP manual via terminal

---

## Metode 1: Web Installer (Recommended)

### Langkah-langkah:

1. **Pastikan XAMPP/MySQL sudah running**
   - Start Apache & MySQL di XAMPP Control Panel

2. **Akses aplikasi via browser**
   ```
   http://localhost/newsoft/base_app
   ```

3. **Sistem akan otomatis detect** jika database belum ada
   - Redirect otomatis ke `/installer`

3. **Isi form konfigurasi:**
   | Field | Default | Keterangan |
   |-------|---------|------------|
   | Database Host | localhost | Alamat MySQL server |
   | Database Port | 3306 | Port MySQL (default 3306) |
   | Database Username | root | User MySQL |
   | Database Password | (kosong) | Password MySQL (XAMPP default kosong) |
   | Nama Database | newsoft_app | Nama database yang akan dibuat |

5. **Klik "Install Database"**
   - Proses import ~1-2 menit
   - Import dari `app/Database/newsoft_base.sql`
   - 34 tabel akan dibuat
   - 82,000+ data wilayah diimport

6. **Selesai!**
   - Redirect ke halaman sukses
   - Kredensial login ditampilkan
   - Klik "Masuk ke Aplikasi"

### Kelebihan Web Installer:
- ✅ User-friendly (tidak perlu terminal)
- ✅ Validasi input otomatis
- ✅ Error handling jelas
- ✅ Auto-update `app/Config/Database.php`
- ✅ Cocok untuk non-developer

---

## Metode 2: Command Line

### Langkah-langkah:

1. **Buka terminal di folder manual installer**
   ```bash
   cd manual_installer
   ```

2. **Opsi A: Gunakan installer interaktif (Windows)**
   ```bash
   install.bat
   ```
   
   Menu yang muncul:
   - [1] Install Database
   - [2] Verify Import  
   - [3] Check Tables
   - [4] Exit

3. **Opsi B: Jalankan script manual**
   ```bash
   php import_sql.php
   ```

4. **Tunggu hingga selesai**
   - Progress ditampilkan setiap 100 queries
   - Total ~99 queries

5. **Verifikasi hasil import**
   ```bash
   php verify_import.php
   ```
   
   Output yang diharapkan:
   ```
   Expected: 34 tables
   Found: 34 tables
   ✅ Jumlah tabel sesuai!
   ```

6. **Cek detail semua tabel**
   ```bash
   php check_tables.php
   ```
   
   Output: Checklist 34 tabel dengan status ✅/❌

7. **Konfigurasi manual Database.php**
   Edit file `app/Config/Database.php`:
   ```php
   public $default = [
       'hostname' => 'localhost',
       'username' => 'root',
       'password' => '',
       'database' => 'newsoft_app',
       'port'     => 3306,
   ];
   ```

### Kelebihan Command Line:
- ✅ Lebih cepat untuk developer
- ✅ Bisa otomasi via script
- ✅ Troubleshooting lebih detail
- ✅ Cocok untuk deployment server
- ✅ Installer interaktif (.bat) untuk Windows

---

## Troubleshooting

### Error: "Connection refused" atau "Can't connect to MySQL"

**Penyebab:** MySQL server belum running

**Solusi:**
1. Buka XAMPP Control Panel
2. Start MySQL service
3. Coba lagi install

---

### Error: "Access denied for user 'root'@'localhost'"

**Penyebab:** Username/password salah

**Solusi Web Installer:**
- Cek kredensial MySQL Anda
- XAMPP default: username=`root`, password=(kosong)
- Masukkan sesuai konfigurasi MySQL Anda
**Solusi Command Line:**
- Edit script `manual_installer/import_sql.php`
- Ubah baris:
  ```php
  $db = new mysqli('localhost', 'root', '');
  ```
  Sesuaikan username/password

---
---

### Error: "Database already exists"

**Penyebab:** Database `newsoft_app` sudah ada

**Solusi:**
- Web Installer akan **auto-drop** dan recreate
- Command Line: Script sudah handle drop database
- Manual: Hapus database via phpMyAdmin
- File SQL: `app/Database/newsoft_base.sql`

---

### Error: "Table 'core_module' doesn't exist"

**Penyebab:** Import tidak lengkap
**Solusi:**
1. Jalankan ulang installer
2. Atau gunakan `cd manual_installer; php import_sql.php` (sudah fix dengan multi_query)
3. Verifikasi dengan `php check_tables.php`

---
---

### Web Installer tidak muncul / langsung error

**Penyebab:** Filter InstallerCheck error

**Solusi:**
1. Cek `app/Config/Database.php` - pastikan database salah/tidak ada
2. Akses manual: `http://localhost/newsoft/base_app/installer`
3. Cek error log di `writable/logs/`

---

## Data Default Setelah Instalasi

| Item | Jumlah | Keterangan |
|------|--------|------------|
| Tabel | 34 | Struktur lengkap |
| User Admin | 1 | Email: admin@payday.indopasifik.co.id |
| Password | - | 123456 |
| Bank Indonesia | 141 | Data bank nasional |
| Provinsi | 34 | Seluruh provinsi Indonesia |
| Kabupaten/Kota | 514 | Data kabupaten |
| Kecamatan | 7,097 | Data kecamatan |
| Kelurahan/Desa | 82,503 | Data kelurahan lengkap |
| Menu | 18 | Menu default sistem |
| Role | 1 | Role admin default |

---

## File-file Installer

| File | Fungsi |
|------|--------|
| `manual_installer/install.bat` | Installer interaktif Windows |
| `manual_installer/import_sql.php` | Script CLI import database |
| `manual_installer/verify_import.php` | Verifikasi hasil import (summary) |
| `manual_installer/check_tables.php` | Checklist detail 34 tabel |
| `manual_installer/README.md` | Dokumentasi manual installer |
| `app/Database/newsoft_base.sql` | File SQL sumber (103,585 lines) |
| `app/Controllers/Installer.php` | Web installer controller |
| `app/Views/installer/index.php` | Form konfigurasi database |
| `app/Views/installer/success.php` | Halaman sukses instalasi |
| `app/Filters/InstallerCheck.php` | Auto-redirect ke installer |

---

## FAQ

**Q: Apakah installer bisa dijalankan ulang?**
A: Ya, installer akan drop dan recreate database. Data lama akan hilang.

**Q: Bagaimana cara disable installer setelah produksi?**
A: Hapus filter `installer` dari `app/Config/Filters.php` di globals->before

**Q: Bisa custom nama database?**
A: Ya, isi form web installer atau edit `import_sql.php` dan `app/Config/Database.php`

**Q: Installer muncul terus meski database sudah ada?**
A: Cek apakah tabel `core_user` ada. Filter mendeteksi via tabel ini.

**Q: Bisa import database production?**
A: Tidak. Installer hanya untuk fresh install. Untuk production gunakan backup/restore.

---

## Tips

💡 **Gunakan Web Installer** jika Anda tidak familiar dengan command line

💡 **Backup database** sebelum menjalankan installer ulang

💡 **Catat kredensial** yang Anda masukkan untuk akses selanjutnya

💡 **Test login** segera setelah instalasi untuk memastikan semua berfungsi
