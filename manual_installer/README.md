# Database Installer - Manual Mode

Script installer manual untuk database menggunakan command line interface.

## Cara Menggunakan

### Windows (Recommended)

Double-click file `install.bat` untuk membuka installer interaktif:

```
DATABASE INSTALLER - MANUAL MODE

Pilih opsi instalasi:
[1] Install Database (Import newsoft_base.sql)
[2] Verify Import (Cek hasil instalasi)
[3] Check Tables (Detail checklist 34 tabel)
[4] Exit
```

### Manual via Command Line

#### 1. Install Database

```bash
cd manual_installer
php import_sql.php
```

Import seluruh database dari `app/Database/newsoft_base.sql`:
- Drop database `newsoft_app` jika sudah ada
- Create database baru
- Import 34 tabel + 82,000+ data
- Progress ditampilkan setiap 100 queries

#### 2. Verify Installation

```bash
php verify_import.php
```

Tampilkan summary hasil instalasi:
- Total tabel (expected: 34)
- Jumlah data per tabel penting
- Status instalasi

#### 3. Check Tables

```bash
php check_tables.php
```

Checklist detail semua tabel:
- List 34 tabel dari SQL file
- Status masing-masing tabel (✅ ada / ❌ hilang)
- Deteksi tabel yang tidak terimport

## Requirements

- PHP 7.4+
- MySQL/MariaDB running
- XAMPP/LAMPP/WAMP (recommended)

## Default Database Configuration

- Host: `localhost`
- Port: `3306`
- Username: `root`
- Password: (kosong untuk XAMPP)
- Database: `newsoft_app`

## Files

```
manual_installer/
├── install.bat          - Installer interaktif (Windows)
├── import_sql.php       - Script import database
├── verify_import.php    - Script verifikasi
├── check_tables.php     - Script checklist tabel
└── README.md           - Dokumentasi ini
```

## Catatan

**Untuk konfigurasi database custom**, edit file-file PHP di folder ini atau gunakan **Web Installer** yang lebih user-friendly di browser.

## Troubleshooting

### Error: MySQL not found

- Pastikan XAMPP sudah running
- Tambahkan PHP ke PATH atau jalankan dari XAMPP shell

### Error: File SQL tidak ditemukan

- Pastikan file `app/Database/newsoft_base.sql` ada
- Jangan pindahkan folder `manual_installer`

### Error: Access denied

- Cek username/password MySQL
- Edit credential di file `import_sql.php` jika berbeda

---

**Author:** VKNewsoft - Newsoft Developer, 2025
