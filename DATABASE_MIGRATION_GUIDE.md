# 📋 **DATABASE MIGRATION GUIDE**

## Overview

Migration files sudah berhasil di-generate otomatis dari schema HTML database `newsoft_base`.

**Total tabel:** 34
**Generated:** 2025-12-03

---

## 🗂️ **List of Tables**

### Core System Tables

1. `blocked_ips`
2. `core_bank`
3. `core_config`
4. `core_file_picker`
5. `core_gudang`
6. `core_identitas`
7. `core_kategori`
8. `core_level_karyawan`
9. `core_menu`
10. `core_menu_kategori`
11. `core_menu_role`
12. `core_module`
13. `core_module_permission`
14. `core_module_status`
15. `core_role`
16. `core_role_module_permission`
17. `core_setting`
18. `core_setting_user`
19. `core_company`
20. `core_user`
21. `core_user_device`
22. `core_user_login_activity`
23. `core_user_role`
24. `core_user_token`

### Regional Data Tables

25. `core_wilayah_kabupaten`
26. `core_wilayah_kecamatan`
27. `core_wilayah_kelurahan`
28. `core_wilayah_propinsi`

### Security & Logging Tables

29. `hrm_log_activity_block`
30. `list_block_ip`
31. `log_firebase_notifications`
32. `offline_log`
33. `security_logs`
34. `whitelist_ips`

---

## 🚀 **Cara Menggunakan Migration**

### 1. Menjalankan Semua Migration

```bash
php spark migrate
```

Output biasanya seperti ini:

```
Running: 2025-12-03-031821_create_blocked_ips
Running: 2025-12-03-031822_create_core_bank
...
All migrations ran successfully!
```

### 2. Cek Status Migration

```bash
php spark migrate:status
```

### 3. Rollback Migration

Rollback batch terakhir:

```bash
php spark migrate:rollback
```

Rollback beberapa batch:

```bash
php spark migrate:rollback -b 2
```

Rollback semua:

```bash
php spark migrate:rollback -all
```

### 4. Refresh Migration

Rollback semua lalu jalankan ulang:

```bash
php spark migrate:refresh
```

---

## 📝 **Lokasi File Migration**

```
app/Database/Migrations/
├── 20251203031821_create_blocked_ips.php
├── 20251203031822_create_core_bank.php
├── ...
└── 20251203031854_create_whitelist_ips.php
```

---

## ⚙️ **Konfigurasi Database**

### Database.php

```php
public $default = [
    'hostname' => 'localhost',
    'username' => 'root',
    'password' => '',
    'database' => 'newsoft_base',
    'DBDriver' => 'MySQLi',
];
```

### Atau via `.env`

```env
database.default.hostname = localhost
database.default.database = newsoft_base
database.default.username = root
database.default.password = 
database.default.DBDriver = MySQLi
```

---

## 🔧 **Advanced Usage**

### Menjalankan Migration Group/Namespace Tertentu

```bash
php spark migrate -g default -n "App\Database\Migrations"
```

### Membuat Migration Baru

```bash
php spark make:migration create_new_table
```

### Menambahkan Foreign Key Manual

Migration auto-generate **tidak** menyertakan FK.

Contoh:

```php
$this->forge->addForeignKey(
    'id_company',
    'core_company',
    'id_company',
    'CASCADE',
    'CASCADE'
);
```

---

## 🎯 **Best Practices**

### 1. Backup sebelum run di production

```bash
mysqldump -u root -p newsoft_base > backup_$(date +%Y%m%d_%H%M%S).sql
```

### 2. Test dulu di development

```bash
CI_ENVIRONMENT=development php spark migrate
```

### 3. Pastikan sudah di-review sebelum commit ke Git

### 4. Migration sudah transaksi-based

Kalau ada error, otomatis rollback.

---

## 📊 **Urutan Eksekusi Migration**

Urutan berdasarkan timestamp:

1. 20251203031821 – blocked_ips
2. 20251203031822 – core_bank
   …
3. 20251203031854 – whitelist_ips

---

## 🔍 **Troubleshooting**

### Error: Table Already Exists

Solusi:

* Drop tabel manual, lalu migrate ulang

```bash
mysql -u root -p newsoft_base -e "DROP TABLE IF EXISTS core_user;"
php spark migrate
```

### Error: Access Denied

Cek credential di Database.php atau .env

### Error: Database Not Found

```bash
mysql -u root -p -e "CREATE DATABASE newsoft_base;"
```

### Migration Stuck / Locked

```bash
php spark migrate:unlock
```

---

## 📦 **Seeding Data**

### Membuat Seeder

```bash
php spark make:seeder UserSeeder
```

### Contoh Seeder

```php
public function run()
{
    $this->db->table('core_user')->insert([
        'username' => 'admin',
        'email' => 'admin@example.com',
        'password' => password_hash('admin123', PASSWORD_DEFAULT),
    ]);
}
```

### Menjalankan Seeder

```bash
php spark db:seed UserSeeder
```

---

## 🆘 **Emergency Rollback**

Jika migration menyebabkan error serius:

```bash
php spark migrate:rollback -all
mysql -u root -p newsoft_base < backup_20251203.sql
php spark migrate:status
```

---

## 📌 **Catatan Penting Sebelum Production**

* Sudah dites di staging
* Database backup tersedia
* Waktu maintenance sudah dijadwalkan
* Pastikan semua migration aman dijalankan
* Rollback plan siap
* Informasikan tim dan user

---

## 🔗 **Perintah Terkait**

```bash
php spark list
php spark db:table tablename
php spark db:seed
php spark migrate:create
php spark migrate:version
```

---

## 🎉 **Selesai**

Jika semua proses lancar, akan muncul:

```
✅ All migrations ran successfully!
✅ 34 tables created
✅ Database structure ready
```

Database siap dipakai. 🚀
