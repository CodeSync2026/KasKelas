<?php
$host = "localhost";
$port = "5432";
$username = "postgres"; // default username PostgreSQL
$password = "postgres"; // default password PostgreSQL
$database = "db_kas_kelas"; // nama db

try {
    $dsn = "pgsql:host={$host};port={$port};dbname={$database}";
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