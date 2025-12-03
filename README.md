# **Admin Panel — Built-in Management Control**

Admin Panel ini dirancang sebagai pusat kendali yang mengatur seluruh aktivitas pengguna dan modul di dalam aplikasi. Sistem kontrol internalnya dibuat menyeluruh agar setiap proses manajemen dapat berjalan lebih terarah, aman, dan fleksibel mengikuti kebutuhan operasional.

Tujuan utama sistem ini adalah sebagai Web Sec (Web Security) yang bertugas untuk mengontrol role, user, dan menu pada setiap aplikasi yang akan dikembangkan. Dengan demikian, sistem ini memastikan setiap aplikasi memiliki pengelolaan akses yang terstruktur dan aman. Namun, fungsionalitasnya tidak terbatas hanya pada integrasi dengan aplikasi lain; sistem ini juga dapat dikembangkan lebih lanjut sebagai solusi standalone yang berdiri sendiri sesuai kebutuhan pengembangan sistem Anda.

Sistem ini juga sudah mendukung **multi level role** dan **multi company**, sehingga pengaturan role akses dan user menjadi lebih dinamis. Setiap user dan role dapat diatur berdasarkan level hierarki maupun perusahaan yang berbeda, memungkinkan fleksibilitas tinggi dalam pengelolaan akses pada berbagai skenario organisasi.

Aplikasi ini dikembangkan sebagai _starter kit_ yang dapat digunakan sebagai fondasi awal dalam pembuatan berbagai sistem, seperti CMS, aplikasi manajemen, maupun sistem lainnya, baik untuk skala kecil maupun besar. Dengan struktur yang modular dan mudah dikembangkan, Anda dapat menyesuaikan dan memperluas fitur sesuai kebutuhan proyek Anda.

> **Stack:**  
> - **CodeIgniter:** versi 4.x  
> - **Database:** MySQL versi 8.x

## **Fitur Utama**

### **1. User Management**

Mengelola akun login menjadi jauh lebih mudah. Admin dapat membuat, memperbarui, menonaktifkan, hingga menghapus user sesuai kebutuhan. Setiap perubahan langsung tercatat agar menjaga ketertiban penggunaan.

### **2. Module Management**

Setiap modul yang tersedia dapat diaktifkan atau dinonaktifkan sesuai kebutuhan perusahaan. Pendekatan ini memastikan aplikasi tetap ringan dan hanya memuat fitur yang benar-benar dipakai dalam workflow harian.

### **3. Menu Configuration**

Struktur menu bisa disusun mengikuti alur kerja perusahaan. Fleksibel untuk digunakan di berbagai jenis organisasi, sehingga navigasi tetap konsisten dan mudah dipahami oleh seluruh pengguna.

### **4. Role Access & Permissions**

Sistem hak akses dibuat sangat detail: mulai dari lihat, buat, edit, hapus, hingga fungsi khusus. Hal ini memastikan setiap user hanya dapat mengakses menu dan modul sesuai otoritasnya. Dukungan multi level role dan multi company memungkinkan pengaturan hak akses yang lebih granular dan sesuai kebutuhan organisasi yang kompleks.

---

## **Keunggulan Sistem**

* Meningkatkan keamanan data melalui akses yang terkontrol.
* Menjaga konsistensi alur kerja antar pengguna.
* Memudahkan proses administrasi dan pengaturan aplikasi tanpa perlu perubahan di sisi kode.
* Siap digunakan untuk setup perusahaan kecil, menengah, hingga skala besar.
* Cocok sebagai _starter_ untuk pengembangan sistem baru dengan stack modern dan dokumentasi yang jelas.

---

## **Instalasi Database**

Database sudah disediakan dalam file `newsoft_base.sql` yang berisi struktur tabel lengkap beserta data awal yang diperlukan untuk menjalankan sistem.

### **Cara Install Database**

Jalankan perintah berikut di root folder project:

```bash
php import_sql.php
```

Script ini akan otomatis:
- Drop database `newsoft_app` jika sudah ada
- Create database baru
- Import seluruh struktur tabel dan data dari file SQL
- Menampilkan progress import secara realtime

Proses import займе sekitar 1-2 menit tergantung spesifikasi server.

### **Verifikasi Database**

Setelah import selesai, jalankan script verifikasi untuk memastikan semua data berhasil diimport:

```bash
php verify_import.php
```

Script ini akan menampilkan:
- Perbandingan jumlah tabel yang diharapkan (34 tabel) vs yang berhasil dibuat
- Jumlah data pada tabel-tabel penting (users, menu, wilayah, dll)
- Konfirmasi bahwa database siap digunakan

### **Cek Detail Tabel**

Untuk melihat daftar lengkap semua tabel dengan checklist, jalankan:

```bash
php check_tables.php
```

Script ini akan menampilkan:
- Checklist 34 tabel yang harus ada dari file SQL
- Status masing-masing tabel (✅ ada / ❌ hilang)
- Peringatan jika ada tabel yang tidak terimport

### **Data Default**

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

## **Pengembangan Selanjutnya**

Database yang sudah terinstall bersifat **final** dan sudah siap digunakan. Untuk perubahan struktur database di masa mendatang, gunakan **CodeIgniter Migrations** agar setiap perubahan dapat dilacak dan didokumentasikan dengan baik.

