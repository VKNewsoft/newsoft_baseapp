<?php

$db = new mysqli('localhost', 'root', '', 'newsoft_app');

if ($db->connect_error) {
    die("❌ Koneksi gagal: " . $db->connect_error);
}

echo "📊 Verifikasi Tabel Database\n";
echo str_repeat('=', 60) . "\n\n";

// Expected tables from SQL file
$expectedTables = [
    'blocked_ips',
    'core_bank',
    'core_company',
    'core_config',
    'core_file_picker',
    'core_gudang',
    'core_identitas',
    'core_kategori',
    'core_menu',
    'core_menu_kategori',
    'core_menu_role',
    'core_module',
    'core_module_permission',
    'core_module_status',
    'core_role',
    'core_role_module_permission',
    'core_setting',
    'core_setting_user',
    'core_user',
    'core_user_device',
    'core_user_login_activity',
    'core_user_role',
    'core_user_token',
    'core_wilayah_kabupaten',
    'core_wilayah_kecamatan',
    'core_wilayah_kelurahan',
    'core_wilayah_propinsi',
    'hrm_log_activity_block',
    'list_block_ip',
    'log_firebase_notifications',
    'migrations',
    'offline_log',
    'security_logs',
    'whitelist_ips'
];

$result = $db->query('SHOW TABLES');
$actualTables = [];
while ($row = $result->fetch_array()) {
    $actualTables[] = $row[0];
}

echo "Expected: " . count($expectedTables) . " tables\n";
echo "Found: " . count($actualTables) . " tables\n\n";

echo "Checklist Tabel:\n";
echo str_repeat('-', 60) . "\n";

$missing = [];
foreach ($expectedTables as $table) {
    if (in_array($table, $actualTables)) {
        echo "✅ $table\n";
    } else {
        echo "❌ $table (MISSING)\n";
        $missing[] = $table;
    }
}

echo "\n" . str_repeat('=', 60) . "\n";

if (empty($missing)) {
    echo "🎉 Semua tabel berhasil diimport!\n";
} else {
    echo "⚠️  Ada " . count($missing) . " tabel yang hilang:\n";
    foreach ($missing as $table) {
        echo "   - $table\n";
    }
}

$db->close();
