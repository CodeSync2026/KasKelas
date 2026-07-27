<?php
$file = isset($_GET['file']) ? $_GET['file'] : 'index.php';
$file = ltrim($file, '/'); // Hilangkan slash di awal jika ada

// Cegah path traversal (keamanan)
if (strpos($file, '..') !== false) {
    http_response_code(403);
    exit("Forbidden");
}

$path = realpath(__DIR__ . '/../' . $file);

// Jika file PHP ditemukan, jalankan
if ($path && is_file($path) && pathinfo($path, PATHINFO_EXTENSION) === 'php') {
    // Ubah direktori kerja ke folder asli file tersebut agar include relatif tetap jalan (jika ada)
    chdir(dirname($path));
    require $path;
} else {
    http_response_code(404);
    echo "404 Not Found - File: " . htmlspecialchars($file);
}
