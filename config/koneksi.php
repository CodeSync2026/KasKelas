<?php
$host = "ep-odd-river-azez4ora-pooler.c-3.ap-southeast-1.aws.neon.tech";
$port = "5432";
$username = "neondb_owner"; // default username PostgreSQL
$password = "npg_UXg4DAqcIy1m"; // default password PostgreSQL
$database = "neondb"; // nama db
try {
    $dsn = "pgsql:host={$host};port={$port};dbname={$database};sslmode=require";
    $koneksi = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
} catch (PDOException $e) {
    error_log($e->getMessage());
    exit("Sistem gagal terhubung ke database PostgreSQL. Pastikan layanan PostgreSQL berjalan dan nama database sudah benar.");
}
?>