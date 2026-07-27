<?php
session_start();
require_once __DIR__ . '/../config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = htmlspecialchars(trim($_POST['nama']));
    $username = htmlspecialchars(trim($_POST['username']));
    $password = trim($_POST['password']);
    $role = 'anggota';

    $cek_username = $koneksi->prepare("SELECT username FROM users WHERE username = ?");
    $cek_username->execute([$username]);

    if ($cek_username->fetch()) {
        echo "<script>alert('Username sudah terpakai! Silakan gunakan username lain.'); window.history.back();</script>";
        exit;
    }

    $password_hash = password_hash($password, PASSWORD_BCRYPT);
    $query = "INSERT INTO users (nama, username, password_hash, role) VALUES (?, ?, ?, ?)";
    $stmt = $koneksi->prepare($query);

    if ($stmt->execute([$nama, $username, $password_hash, $role])) {
        echo "<script>alert('Pendaftaran berhasil! Silakan login dengan akun baru Anda.'); window.location='login.php';</script>";
        exit;
    }

    echo "<script>alert('Pendaftaran gagal. Terjadi kesalahan pada sistem.'); window.history.back();</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Anggota - Kas Kelas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/app.css" rel="stylesheet">
</head>
<body class="app-body auth-body">
    <div class="auth-layout">
        <aside class="auth-visual">
            <a class="brand-lockup" href="login.php" aria-label="Kas Kelas">
                <span class="brand-mark"><i data-lucide="wallet-cards"></i></span>
                <span class="brand-copy">
                    <span class="brand-title">Kas Kelas</span>
                    <span class="brand-subtitle">Akun anggota baru</span>
                </span>
            </a>

            <div>
                <p class="auth-kicker">Masuk ke ekosistem kas</p>
                <h2 class="auth-heading">Setiap anggota punya akses transparansi sendiri.</h2>
                <p class="auth-copy">
                    Setelah terdaftar, akun otomatis menjadi anggota. Bendahara tetap bisa mengelola transaksi dan tagihan dari panel khusus.
                </p>

                <div class="mini-ledger" aria-hidden="true">
                    <div class="mini-ledger-row">
                        <span>Iuran mingguan</span>
                        <strong>Terpantau</strong>
                    </div>
                    <div class="mini-ledger-row">
                        <span>Pengeluaran kelas</span>
                        <strong>Terbuka</strong>
                    </div>
                    <div class="mini-ledger-row">
                        <span>Status tagihan</span>
                        <strong>Personal</strong>
                    </div>
                </div>
            </div>

            <div class="auth-stats">
                <div class="mini-stat">
                    <strong>1 akun</strong>
                    <span>Untuk cek saldo dan pengeluaran kelas.</span>
                </div>
                <div class="mini-stat">
                    <strong>Role</strong>
                    <span>Otomatis sebagai anggota.</span>
                </div>
                <div class="mini-stat">
                    <strong>Aman</strong>
                    <span>Password disimpan dalam bentuk hash.</span>
                </div>
            </div>
        </aside>

        <main class="auth-main">
            <section class="auth-panel" aria-label="Form daftar anggota">
                <span class="brand-mark mb-3"><i data-lucide="user-plus"></i></span>
                <h1>Daftar akun baru</h1>
                <p class="lead">Isi data anggota dengan username yang mudah diingat dan tanpa spasi.</p>

                <form action="" method="POST" data-loading>
                    <div class="mb-3">
                        <label class="form-label" for="nama">Nama Lengkap</label>
                        <input type="text" id="nama" name="nama" class="form-control" required autocomplete="name" placeholder="Contoh: Budi Sarpras">
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="username">Username</label>
                        <input type="text" id="username" name="username" class="form-control" required autocomplete="username" placeholder="Contoh: budi_sarpras">
                        <span class="field-hint">Gunakan huruf kecil tanpa spasi agar mudah dipakai login.</span>
                    </div>

                    <div class="mb-4">
                        <label class="form-label" for="password">Password</label>
                        <div class="password-field">
                            <input type="password" id="password" name="password" class="form-control" required autocomplete="new-password" placeholder="Buat password">
                            <button type="button" class="password-toggle js-password-toggle" data-target="#password" aria-label="Tampilkan password" title="Tampilkan password">
                                <i data-lucide="eye"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" name="btn_register" class="btn btn-success w-100" data-loading-text="Mendaftarkan akun...">
                        <!-- <i data-lucide="sparkles"></i> -->
                        Daftar Sekarang
                    </button>
                </form>

                <p class="auth-switch">
                    Sudah punya akun? <a href="login.php" class="text-decoration-none fw-bold">Masuk di sini</a>
                </p>
            </section>
        </main>
    </div>

    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="../assets/js/app.js"></script>
</body>
</html>
