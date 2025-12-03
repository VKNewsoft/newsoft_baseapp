<?php
/**
 * Import database dari newsoft_base.sql
 * 
 * @author VKNewsoft - Newsoft Developer, 2025
 */

$sqlFile = dirname(__DIR__) . '/app/Database/newsoft_base.sql';

if (!file_exists($sqlFile)) {
    die("❌ File newsoft_base.sql tidak ditemukan!\n");
}

echo "📖 Membaca file SQL...\n";
$sql = file_get_contents($sqlFile);

echo "🗑️  Dropping dan creating database...\n";
$db = new mysqli('localhost', 'root', '');

if ($db->connect_error) {
    die("❌ Koneksi gagal: " . $db->connect_error . "\n");
}

$db->query('DROP DATABASE IF EXISTS newsoft_app');
$db->query('CREATE DATABASE newsoft_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
$db->select_db('newsoft_app');

echo "📥 Importing struktur dan data (ini mungkin memakan waktu)...\n";

// Set proper modes
$db->query('SET FOREIGN_KEY_CHECKS=0');
$db->query('SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO"');
$db->query('SET AUTOCOMMIT=0');
$db->query('START TRANSACTION');

// Execute the entire SQL file as multi-query
if ($db->multi_query($sql)) {
    $queryCount = 0;
    do {
        $queryCount++;
        if ($queryCount % 100 == 0) {
            echo "⏳ Processed: $queryCount queries...\n";
        }
        
        // Store result if any
        if ($result = $db->store_result()) {
            $result->free();
        }
        
        // Check for errors
        if ($db->errno) {
            echo "⚠️  Error at query #$queryCount: {$db->error}\n";
        }
        
    } while ($db->more_results() && $db->next_result());
    
    echo "\n✅ Total queries executed: $queryCount\n";
} else {
    die("❌ Error executing SQL: {$db->error}\n");
}

$db->query('COMMIT');
$db->query('SET FOREIGN_KEY_CHECKS=1');

echo "✅ Import selesai!\n";
