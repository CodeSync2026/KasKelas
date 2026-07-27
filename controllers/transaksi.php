<?php
session_start();
require_once '../config/koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'bendahara') {
    header("Location: ../views/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tanggal = $_POST['tanggal'];
    $id_kategori = intval($_POST['id_kategori']);
    $jumlah = floatval($_POST['jumlah']);
    $keterangan = htmlspecialchars(trim($_POST['keterangan']));
    $id_user = $_SESSION['id_user'];
    
    // Logika Upload File
    $nama_file_baru = NULL;
    if (isset($_FILES['bukti_foto']) && $_FILES['bukti_foto']['error'] === 0) {
        $ekstensi_diizinkan = ['jpg', 'jpeg', 'png'];
        $nama_file = $_FILES['bukti_foto']['name'];
        $ukuran_file = $_FILES['bukti_foto']['size'];
        $tmp_file = $_FILES['bukti_foto']['tmp_name'];
        
        $ekstensi_file = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));
        
        if (in_array($ekstensi_file, $ekstensi_diizinkan) && $ukuran_file <= 2000000) { // Maks 2MB
            $nama_file_baru = uniqid() . '.' . $ekstensi_file;
            $path_tujuan = '../assets/uploads/' . $nama_file_baru;
            move_uploaded_file($tmp_file, $path_tujuan);
        } else {
            echo "<script>alert('Gagal upload! Pastikan file JPG/PNG dan di bawah 2MB.'); window.history.back();</script>";
            exit;
        }
    }

    $query = "INSERT INTO transactions (id_user, id_kategori, jumlah, tanggal, keterangan, bukti_foto) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $koneksi->prepare($query);
    
    if ($stmt->execute([$id_user, $id_kategori, $jumlah, $tanggal, $keterangan, $nama_file_baru])) {
        echo "<script>alert('Transaksi berhasil dicatat!'); window.location='../views/dashboard_bendahara.php';</script>";
    } else {
        echo "<script>alert('Gagal mencatat transaksi.'); window.history.back();</script>";
    }
}
?>