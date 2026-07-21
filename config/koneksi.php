<?php
$host = "localhost";
$username = "root"; // default username bawaan XAMPP
$password = ""; // default password XAMPP biasanya kosong
$database = "db_kas_kelas"; // nama db

// mengaktifkan mode exception untuk mysqli
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $koneksi = new mysqli($host, $username, $password, $database);
    $koneksi->set_charset("utf8mb4");
    
    // echo "Koneksi ke database db_kas_kelas berhasil!";
    
} catch (mysqli_sql_exception $e) {
    // menangkap error tanpa membocorkan struktur path folder di browser
    error_log($e->getMessage());
    exit("Sistem gagal terhubung ke database. Pastikan nama database sudah benar.");
}
?>