<?php
// Wajib dipanggil untuk memulai sesi login
session_start(); 
require_once '../config/koneksi.php';

// Cek apakah data dikirim melalui method POST
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Cari user di database berdasarkan username (Gunakan Prepared Statement untuk anti-hacker)
    $query = "SELECT id_user, nama, password_hash, role FROM users WHERE username = ?";
    $stmt = $koneksi->prepare($query);
    $stmt->execute([$username]);

    // Jika username ditemukan di database
    if($row = $stmt->fetch()) {
        
        // Cocokkan password yang diketik dengan password acak (hash) di database
        if(password_verify($password, $row['password_hash'])) {
            
            // Jika cocok, buatkan Sesi (Session) untuk merekam identitasnya
            $_SESSION['id_user'] = $row['id_user'];
            $_SESSION['nama'] = $row['nama'];
            $_SESSION['role'] = $row['role'];

            // Arahkan ke halaman yang sesuai dengan jabatannya
            if($row['role'] == 'bendahara') {
                header("Location: ../views/dashboard_bendahara.php");
            } else {
                // Anggota biasa arahkan ke dashboard anggota
                header("Location: ../views/dashboard_anggota.php");
            }
            exit;
        } else {
            // Jika password salah
            echo "<script>alert('Password salah!'); window.location='../views/login.php';</script>";
        }
    } else {
        // Jika username tidak ada
        echo "<script>alert('Username tidak ditemukan!'); window.location='../views/login.php';</script>";
    }
} else {
    // Jika file ini diakses langsung tanpa lewat form login, tendang balik ke halaman login
    header("Location: ../views/login.php");
    exit;
}
?>