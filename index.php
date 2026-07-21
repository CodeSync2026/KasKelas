<?php
session_start();

if (isset($_SESSION['role']) && $_SESSION['role'] === 'bendahara') {
    header("Location: views/dashboard_bendahara.php");
    exit;
}

if (isset($_SESSION['role'])) {
    header("Location: views/dashboard_anggota.php");
    exit;
}

header("Location: views/login.php");
exit;
?>
