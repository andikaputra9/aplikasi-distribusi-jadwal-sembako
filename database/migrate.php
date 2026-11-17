<?php
/**
 * Skrip Setup / Migrasi Otomatis Database
 * Menjalankan schema.sql dan sample_data.sql ke MySQL
 * 
 * Cara menjalankan:
 * - Lewat Terminal: php database/migrate.php
 * - Lewat Browser: http://localhost/aplikasi-distribusi-jadwal-sembako/database/migrate.php
 */

require_once __DIR__ . '/../config/database.php';
$config = Database::getConfig();

$host = $config['host'] ?? '127.0.0.1';
$port = $config['port'] ?? 3306;
$user = $config['username'] ?? 'root';
$pass = $config['password'] ?? '';
$dbname = $config['dbname'] ?? 'distribusi_sembako';

$isCli = (php_sapi_name() === 'cli');

function output($message, $isCli) {
    if ($isCli) {
        echo $message . "\n";
    } else {
        echo nl2br(htmlspecialchars($message)) . "<br>";
    }
}

if (!$isCli) {
    echo "<!doctype html><html><head><meta charset='utf-8'><title>Migrasi Database</title>";
    echo "<style>body{font-family:sans-serif;padding:24px;background:#f8fafc;} pre{background:#fff;padding:16px;border-radius:8px;border:1px solid #e2e8f0;}</style></head><body><h2>Setup & Migrasi Database Distribusi Sembako</h2><pre>";
}

try {
    output("1. Menghubungkan ke MySQL server ({$host}:{$port})...", $isCli);
    $pdo = new PDO("mysql:host={$host};port={$port}", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    output("   -> Berhasil terhubung ke MySQL server.", $isCli);

    // Jalankan schema.sql
    output("2. Menjalankan 'database/schema.sql' (membuat database & tabel)...", $isCli);
    $schemaFile = __DIR__ . '/schema.sql';
    if (!file_exists($schemaFile)) {
        throw new Exception("File schema.sql tidak ditemukan di {$schemaFile}");
    }
    $schemaSql = file_get_contents($schemaFile);
    $pdo->exec($schemaSql);
    output("   -> Skema dan tabel berhasil dibuat.", $isCli);

    // Jalankan sample_data.sql
    output("3. Menjalankan 'database/sample_data.sql' (mengisi data awal/seeder)...", $isCli);
    $sampleFile = __DIR__ . '/sample_data.sql';
    if (!file_exists($sampleFile)) {
        throw new Exception("File sample_data.sql tidak ditemukan di {$sampleFile}");
    }
    $sampleSql = file_get_contents($sampleFile);
    $pdo->exec($sampleSql);
    output("   -> Data awal berhasil dimasukkan.", $isCli);

    // Verifikasi tabel dan jumlah baris
    output("4. Memverifikasi isi database '{$dbname}':", $isCli);
    $pdo->exec("USE `{$dbname}`");
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $t) {
        $count = $pdo->query("SELECT COUNT(*) FROM `{$t}`")->fetchColumn();
        output("   - Tabel '{$t}': {$count} data", $isCli);
    }

    output("\nSELESAI! Database telah siap digunakan.", $isCli);

} catch (Exception $e) {
    output("\n[ERROR] Gagal melakukan migrasi: " . $e->getMessage(), $isCli);
}

if (!$isCli) {
    echo "</pre><a href='../index.php'>&larr; Kembali ke Halaman Login</a></body></html>";
}
