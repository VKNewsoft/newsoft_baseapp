<?php

$db = new mysqli('localhost', 'root', '', 'newsoft_app');

if ($db->connect_error) {
    die("❌ Koneksi gagal: " . $db->connect_error);
}

echo "📊 Verifikasi Import Database\n";
echo str_repeat('=', 60) . "\n\n";

// Expected total tables
$expectedTableCount = 34;

// Cek jumlah tabel
$tables = $db->query('SHOW TABLES');
$actualTableCount = $tables->num_rows;

echo "Expected: $expectedTableCount tables\n";
echo "Found: $actualTableCount tables\n";

if ($actualTableCount == $expectedTableCount) {
    echo "✅ Jumlah tabel sesuai!\n\n";
} else {
    echo "⚠️  Jumlah tabel tidak sesuai!\n\n";
}

// Cek data penting
$checks = [
    'core_company' => 'SELECT COUNT(*) as c FROM core_company',
    'core_user' => 'SELECT COUNT(*) as c FROM core_user',
    'core_identitas' => 'SELECT COUNT(*) as c FROM core_identitas',
    'core_wilayah_propinsi' => 'SELECT COUNT(*) as c FROM core_wilayah_propinsi',
    'core_wilayah_kabupaten' => 'SELECT COUNT(*) as c FROM core_wilayah_kabupaten',
    'core_wilayah_kecamatan' => 'SELECT COUNT(*) as c FROM core_wilayah_kecamatan',
    'core_wilayah_kelurahan' => 'SELECT COUNT(*) as c FROM core_wilayah_kelurahan',
    'core_bank' => 'SELECT COUNT(*) as c FROM core_bank',
    'core_menu' => 'SELECT COUNT(*) as c FROM core_menu',
    'core_role' => 'SELECT COUNT(*) as c FROM core_role'
];

echo "📋 Jumlah Data per Tabel:\n";
echo str_repeat('-', 60) . "\n";

foreach ($checks as $table => $query) {
    $result = $db->query($query);
    if ($result) {
        $row = $result->fetch_assoc();
        printf("%-35s : %s rows\n", $table, number_format($row['c']));
    }
}

echo "\n" . str_repeat('=', 60) . "\n";
echo "🎉 Database berhasil diimport dengan lengkap!\n";

$db->close();
