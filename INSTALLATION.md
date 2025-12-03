# Panduan Instalasi Database

Database sudah disediakan dalam file `app/Database/newsoft_base.sql` yang berisi struktur tabel lengkap beserta data awal yang diperlukan untuk menjalankan sistem.

> 📖 **Dokumentasi Detail:** Lihat [DATABASE_INSTALLATION_GUIDE.md](DATABASE_INSTALLATION_GUIDE.md) untuk troubleshooting dan FAQ lengkap.

---

## **Metode 1: Web Installer (Recommended)** ⭐

Cara termudah untuk install database adalah melalui web installer yang sudah terintegrasi:

### **Langkah-langkah:**

1. **Akses aplikasi** melalui browser (contoh: `http://localhost/newsoft/base_app`)
2. Sistem akan **otomatis mendeteksi** jika database belum terkonfigurasi
3. Anda akan **diarahkan ke halaman installer** secara otomatis
4. **Isi form konfigurasi database:**
   - Database Host (default: `localhost`)
   - Database Port (default: `3306`)
   - Database Username (default: `root`)
   - Database Password (kosongkan jika tidak ada)
   - Nama Database (default: `newsoft_app`)
5. Klik tombol **"Install Database"**
6. Tunggu proses instalasi selesai (import 34 tabel + data)
7. Setelah sukses, Anda akan diarahkan ke halaman login

### **Keuntungan Web Installer:**

- ✅ User-friendly dengan interface yang mudah
- ✅ Validasi input otomatis
- ✅ Konfigurasi database tersimpan otomatis
- ✅ Error handling yang jelas
- ✅ Tidak perlu akses terminal/command line

---

## **Metode 2: Manual via Command Line**

Jika Anda lebih suka menggunakan command line:

### **Windows (Recommended):**

```bash
cd manual_installer
install.bat
```

Pilih menu instalasi interaktif:
1. Install Database
2. Verify Import
3. Check Tables

### **Manual PHP Command:**

```bash
cd manual_installer
php import_sql.php
```

Script ini akan otomatis:
- Drop database `newsoft_app` jika sudah ada
- Create database baru
- Import seluruh struktur tabel dan data dari file SQL
- Menampilkan progress import secara realtime

Proses import memakan waktu sekitar 1-2 menit tergantung spesifikasi server.

**Catatan:** Setelah import manual, Anda perlu mengonfigurasi file `app/Config/Database.php` secara manual.

**Dokumentasi lengkap:** Lihat `manual_installer/README.md`

---

## **Verifikasi Instalasi**

Setelah import selesai, jalankan script verifikasi untuk memastikan semua data berhasil diimport:

```bash
cd manual_installer
php verify_import.php
```

Script ini akan menampilkan:
- Perbandingan jumlah tabel yang diharapkan (34 tabel) vs yang berhasil dibuat
- Jumlah data pada tabel-tabel penting (users, menu, wilayah, dll)
- Konfirmasi bahwa database siap digunakan

### **Cek Detail Tabel**

Untuk melihat daftar lengkap semua tabel dengan checklist, jalankan:

```bash
cd manual_installer
php check_tables.php
```

Script ini akan menampilkan:
- Checklist 34 tabel yang harus ada dari file SQL
- Status masing-masing tabel (✅ ada / ❌ hilang)
- Peringatan jika ada tabel yang tidak terimport

---

## **Data Default**

Setelah instalasi, sistem sudah dilengkapi dengan:

- **1 Company** data perusahaan default
- **1 User Admin** dengan kredensial login awal:  
    - **Username:** `admin`  
    - **Password:** `123456`
- **141 Bank** data bank seluruh Indonesia
- **82,503 Kelurahan** data wilayah lengkap se-Indonesia (Provinsi, Kabupaten, Kecamatan, Kelurahan)
- **Menu & Role** konfigurasi menu dan hak akses default

Login menggunakan kredensial yang tersedia di database tabel `core_user`.

---

## **Troubleshooting**

### **Error: Access denied for user**
- Periksa username dan password MySQL Anda
- Pastikan user MySQL memiliki privilege untuk CREATE DATABASE

### **Error: Unknown database**
- Normal pada instalasi pertama kali
- Web installer akan otomatis create database
- Untuk manual install, jalankan script import_sql.php

### **Error: File tidak ditemukan**
- Pastikan file `newsoft_base.sql` ada di folder `app/Database/`
- Cek path di script sesuai dengan lokasi file

### **Import terlalu lama**
- Normal untuk data 82,000+ rows
- Jangan tutup terminal/browser saat proses berjalan
- Periksa log MySQL jika terjadi timeout

---

## **Pengembangan Selanjutnya**

Database yang sudah terinstall bersifat **final** dan sudah siap digunakan. Untuk perubahan struktur database di masa mendatang, gunakan **CodeIgniter Migrations** agar setiap perubahan dapat dilacak dan didokumentasikan dengan baik.
