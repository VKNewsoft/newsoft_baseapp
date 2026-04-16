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
3. **Akses** aplikasi via browser
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
1. Akses aplikasi via browser
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

### **v1.1.1 (16-04-2026)**

- Optimasi performa initial render pada module `dashboard`, `securitymonitor`, `company`, `builtin/menu`, `builtin/module`, `builtin/permission`, `builtin/user`, `builtin/menu-role`, `builtin/role`, `builtin/role-permission`, `builtin/user-role`, `builtin/setting-app`, dan `builtin/setting-layout`.
- Prioritas above-the-fold diperkuat dengan skeleton/loading ringan, defer section non-kritis, dan pengurangan DOM/reflow pada halaman list utama.
- Standardisasi helper performa di asset `Common` untuk defer inisialisasi non-kritis, lazy `select2`, dan kontrol skeleton tabel lintas module.
- DataTable list utama dipastikan hanya memuat data sesuai pagination aktif dengan pendekatan server-side processing yang lebih ringan saat initial load.
- Query list dioptimasi dengan `select` field seperlunya, pembatasan lookup relasi ke row halaman aktif, dan pengurangan pola N+1 pada role, menu-role, user-role, user, company, module, dan permission.
- Asset berat non-kritis seperti `Chart.js` dan `select2` dipindahkan ke lazy/deferred load agar hero, toolbar, dan tabel utama tampil lebih cepat.
- Duplikasi asset lintas module dikurangi dengan memanfaatkan asset shared `Common` dan menghapus preload yang tidak dibutuhkan saat render awal.
- Stabilitas render font diperbaiki dengan preload font kritikal multi-weight, inline `@font-face` kritikal, versi asset yang stabil, dan penguatan cache asset agar tidak terjadi blink saat reload atau pindah halaman.
- Cache dynamic asset diperbaiki dengan menghapus cache-buster berbasis `time()` pada layout utama sehingga browser dapat memanfaatkan cache CSS/JS/font secara optimal.
- Perbaikan bug `Undefined index: id_module` pada aksi data user tanpa mengubah logic bisnis utama.
- Seluruh perubahan difokuskan pada percepatan render awal dan stabilitas UI tanpa mengubah alur bisnis utama.

### **v1.1.0 (15-04-2026)**

- Refactor arsitektur menjadi **full HMVC modular** (controller, model, view, asset dalam satu module).
- Implementasi **module asset loader (module-assets)** dengan resolver otomatis + MIME type fix (CSS/JS tidak lagi terbaca sebagai text/plain).
- Perbaikan routing asset HMVC untuk mendukung path bertingkat.
- Migrasi & penghapusan folder legacy:
  - `app/Views/themes/modern`
  - `public/themes/modern`

- Standardisasi **view resolver ke namespaced view** (`App\Modules\...\Views\...`) tanpa dependency ke path filesystem.
- Perbaikan layout Login/Register/Recovery (HTML valid + asset ter-load normal).
- Konsolidasi asset ke **Common shared** (CSS/JS reusable lintas module).
- Penghapusan asset duplikat di berbagai module.

- Pembuatan **global design system (site.css)**:
  - page-shell, page-hero, page-toolbar, page-card, form-card, card-table-wrap
  - konsistensi spacing, typography, dan responsive behavior
- Refactor seluruh view ke class global (hapus class lama seperti role-list-card, result-list-toolbar, dll).

- Standardisasi tampilan **DataTable**:
  - wrapper seragam (card-table-wrap)
  - alignment header, search, pagination lebih rapi & konsisten
  - responsive mobile diperbaiki

- Refactor UI/UX **Module & Role Permission**:
  - tampilan berbasis card + chip permission
  - grouping per role (accordion)
  - penambahan search/filter role
  - badge jumlah permission per role
  - summary counter & empty state
  - peningkatan readability & navigasi

- Refactor UI **Security Monitor**:
  - dashboard-style ala panel modern
  - blocked IP management lebih clean & terstruktur
  - perbaikan hierarchy, spacing, dan mobile UX

- Modularisasi JavaScript:
  - pemindahan JS inline ke file module (sidebar.js, user.js, setting-layout.js, dll)
  - penambahan fitur filter/search sidebar & realtime UI state

- Performa dashboard HR:
  - optimasi query (eliminasi N+1, redundant query, JOIN optimization)
  - pengurangan jumlah query saat load dashboard

- Perbaikan bug & stabilitas:
  - perbaikan namespace legacy model
  - perbaikan routing HMVC
  - normalisasi path view & asset
  - perbaikan Content-Type asset

- Tidak ada perubahan pada logic bisnis utama (no breaking change)
