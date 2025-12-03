# **Admin Panel — Built-in Management Control**

Admin Panel ini dirancang sebagai pusat kendali yang mengatur seluruh aktivitas pengguna dan modul di dalam aplikasi. Sistem kontrol internalnya dibuat menyeluruh agar setiap proses manajemen dapat berjalan lebih terarah, aman, dan fleksibel mengikuti kebutuhan operasional.

Tujuan utama sistem ini adalah sebagai Web Sec (Web Security) yang bertugas untuk mengontrol role, user, dan menu pada setiap aplikasi yang akan dikembangkan. Dengan demikian, sistem ini memastikan setiap aplikasi memiliki pengelolaan akses yang terstruktur dan aman. Namun, fungsionalitasnya tidak terbatas hanya pada integrasi dengan aplikasi lain; sistem ini juga dapat dikembangkan lebih lanjut sebagai solusi standalone yang berdiri sendiri sesuai kebutuhan pengembangan sistem Anda.

Sistem ini juga sudah mendukung **multi level role** dan **multi company**, sehingga pengaturan role akses dan user menjadi lebih dinamis. Setiap user dan role dapat diatur berdasarkan level hierarki maupun perusahaan yang berbeda, memungkinkan fleksibilitas tinggi dalam pengelolaan akses pada berbagai skenario organisasi.

Aplikasi ini dikembangkan sebagai _starter kit_ yang dapat digunakan sebagai fondasi awal dalam pembuatan berbagai sistem, seperti CMS, aplikasi manajemen, maupun sistem lainnya, baik untuk skala kecil maupun besar. Dengan struktur yang modular dan mudah dikembangkan, Anda dapat menyesuaikan dan memperluas fitur sesuai kebutuhan proyek Anda.

> **Stack:**  
> - **CodeIgniter:** versi 4.x  
> - **Database:** MySQL versi 8.x

---

## **Quick Start**

1. **Clone/Download** project ini
2. **Jalankan** XAMPP (Apache + MySQL)
3. **Akses** aplikasi via browser: `http://localhost/newsoft/base_app`
4. **Sistem otomatis mendeteksi** database belum ada → Redirect ke **Web Installer**
5. **Isi form** konfigurasi database (default: localhost, root, no password)
6. **Klik Install** → Tunggu import selesai (34 tabel + 82,000+ data)
7. **Login** dengan kredensial default
8. **Done!** 🎉

> 💡 **Tidak perlu terminal/command line** - Semua bisa dilakukan via browser!

---

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

Database sudah disediakan dalam file `app/Database/newsoft_base.sql` yang berisi:
- **34 Tabel** struktur database lengkap
- **82,503+ Data** wilayah Indonesia, bank, user admin, dan konfigurasi awal

### **📖 Panduan Instalasi Lengkap**

Pilih metode instalasi sesuai kebutuhan Anda:

- **[📘 Panduan Instalasi Database](INSTALLATION.md)** - Tutorial step-by-step lengkap
- **[🔧 Database Installation Guide](DATABASE_INSTALLATION_GUIDE.md)** - Dokumentasi teknis, troubleshooting, dan FAQ

### **Instalasi Cepat**

**Metode 1: Web Installer (Recommended)** ⭐
1. Akses aplikasi via browser: `http://localhost/newsoft/base_app`
2. Sistem otomatis redirect ke installer
3. Isi form konfigurasi database
4. Klik "Install Database"
5. Login dengan kredensial default

**Metode 2: Command Line**
```bash
cd manual_installer
install.bat    # Windows (interactive)
# atau
php import_sql.php    # Manual
```

### **Kredensial Default**

Setelah instalasi selesai, login dengan:
- **Username:** `admin`
- **Password:** `123456`

---

## **Pengembangan Selanjutnya**

Database yang sudah terinstall bersifat **final** dan sudah siap digunakan. Untuk perubahan struktur database di masa mendatang, gunakan **CodeIgniter Migrations** agar setiap perubahan dapat dilacak dan didokumentasikan dengan baik.

