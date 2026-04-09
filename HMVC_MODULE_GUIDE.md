# HMVC Module Guide

Project ini sekarang menggunakan arsitektur HMVC dengan struktur utama:

```text
app/
  Modules/
    NamaModul/
      Config/
        Routes.php
      Controllers/
        NamaModul.php
      Models/
        NamaModulModel.php
      Views/
        themes/
          modern/
            nama-modul.php
```

## Menambah Modul Baru

Gunakan generator:

```bash
php tools/create_hmvc_module.php NamaModul nama-modul
```

Contoh:

```bash
php tools/create_hmvc_module.php Produk produk
```

Generator akan membuat:

- `app/Modules/Produk/Config/Routes.php`
- `app/Modules/Produk/Controllers/Produk.php`
- `app/Modules/Produk/Models/ProdukModel.php`
- `app/Modules/Produk/Views/themes/modern/produk.php`

## Aturan Penulisan

- Controller module harus extend `\App\Modules\Common\Controllers\BaseController`
- Model module harus extend `\App\Modules\Common\Models\BaseModel`
- View module disimpan di dalam folder modul masing-masing
- Route modul diletakkan di `Config/Routes.php` milik modul
- Query yang menerima input user harus memakai parameter binding atau Query Builder
- Hindari membuat file baru lagi di `app/Controllers` dan `app/Models`

## Catatan

- Main route loader di `app/Config/Routes.php` sudah otomatis memuat file route dari setiap modul.
- Untuk konsistensi project berikutnya, perlakukan `app/Modules` sebagai titik masuk utama untuk fitur baru.
