<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Kas Kelas</title>
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
                    <span class="brand-subtitle">Manajemen iuran dan pengeluaran</span>
                </span>
            </a>

            <div>
                <p class="auth-kicker">Panel kas yang transparan</p>
                <h2 class="auth-heading">Saldo, transaksi, dan tagihan dalam satu alur yang rapi.</h2>
                <p class="auth-copy">
                    Bendahara bisa mencatat uang masuk dan keluar tanpa ribet, sementara anggota tetap bisa melihat penggunaan kas kelas dengan jelas.
                </p>

                <div class="ledger-art mt-4" aria-hidden="true">
                    <div class="ledger-art-header">
                        <span>Rangkuman kas</span>
                        <span class="js-clock"></span>
                    </div>
                    <div class="ledger-visual">
                        <div class="ledger-sheet">
                            <div class="ledger-line"></div>
                            <div class="ledger-line"></div>
                            <div class="ledger-line"></div>
                            <div class="ledger-line"></div>
                        </div>
                        <div class="bar-stack">
                            <span style="height: 44px"></span>
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="auth-stats">
                <div class="mini-stat">
                    <strong>Rapi</strong>
                    <span>Catatan kas tertata per kategori.</span>
                </div>
                <div class="mini-stat">
                    <strong>Cepat</strong>
                    <span>Akses bendahara dan anggota dipisah.</span>
                </div>
                <div class="mini-stat">
                    <strong>Jelas</strong>
                    <span>Pengeluaran bisa dilengkapi bukti nota.</span>
                </div>
            </div>
        </aside>

        <main class="auth-main">
            <section class="auth-panel" aria-label="Form login">
                <span class="brand-mark mb-3"><i data-lucide="key-round"></i></span>
                <h1>Masuk ke kas kelas</h1>
                <p class="lead">Gunakan akun bendahara atau anggota untuk membuka dashboard yang sesuai.</p>

                <form action="../controllers/auth.php" method="POST" data-loading>
                    <div class="mb-3">
                        <label class="form-label" for="username">Username</label>
                        <input type="text" id="username" name="username" class="form-control" required autocomplete="username" placeholder="Masukkan username">
                    </div>

                    <div class="mb-4">
                        <label class="form-label" for="password">Password</label>
                        <div class="password-field">
                            <input type="password" id="password" name="password" class="form-control" required autocomplete="current-password" placeholder="Masukkan password">
                            <button type="button" class="password-toggle js-password-toggle" data-target="#password" aria-label="Tampilkan password" title="Tampilkan password">
                                <i data-lucide="eye"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" name="btn_login" class="btn btn-primary w-100" data-loading-text="Membuka dashboard...">
                        <i data-lucide="log-in"></i>
                        Masuk
                    </button>

                    <p class="auth-switch">
                        Belum punya akun? <a href="register.php" class="text-decoration-none fw-bold">Daftar sekarang</a>
                    </p>
                </form>
            </section>
        </main>
    </div>

    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="../assets/js/app.js"></script>
</body>
</html>
