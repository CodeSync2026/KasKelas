<?php
session_start();
require_once __DIR__ . '/../config/koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'bendahara') {
    echo "<script>alert('Akses Ditolak! Anda bukan bendahara.'); window.location='login.php';</script>";
    exit;
}

$query_ringkasan = "
    SELECT
        COALESCE(SUM(CASE WHEN c.jenis = 'pemasukan' THEN t.jumlah ELSE 0 END), 0) AS total_pemasukan,
        COALESCE(SUM(CASE WHEN c.jenis = 'pengeluaran' THEN t.jumlah ELSE 0 END), 0) AS total_pengeluaran,
        COUNT(t.id_kategori) AS total_transaksi
    FROM transactions t
    JOIN categories c ON t.id_kategori = c.id_kategori
";
$row_ringkasan = $koneksi->query($query_ringkasan)->fetch();
$total_pemasukan = (float) $row_ringkasan['total_pemasukan'];
$total_pengeluaran = (float) $row_ringkasan['total_pengeluaran'];
$total_saldo = $total_pemasukan - $total_pengeluaran;
$total_transaksi = (int) $row_ringkasan['total_transaksi'];

$query_tagihan = "
    SELECT
        COALESCE(SUM(CASE WHEN status = 'belum' THEN 1 ELSE 0 END), 0) AS total_belum,
        COALESCE(SUM(CASE WHEN status = 'lunas' THEN 1 ELSE 0 END), 0) AS total_lunas
    FROM tagihan
";
$row_tagihan = $koneksi->query($query_tagihan)->fetch();
$total_belum = (int) $row_tagihan['total_belum'];
$total_lunas = (int) $row_tagihan['total_lunas'];

$arus_total = max(1, $total_pemasukan + $total_pengeluaran);
$pemasukan_pct = round(($total_pemasukan / $arus_total) * 100);
$pengeluaran_pct = round(($total_pengeluaran / $arus_total) * 100);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Bendahara - Kas Kelas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/app.css" rel="stylesheet">
</head>
<body class="app-body">
    <nav class="app-nav">
        <div class="nav-inner">
            <a class="nav-brand" href="dashboard_bendahara.php" aria-label="Panel Bendahara">
                <span class="brand-mark"><i data-lucide="wallet-cards"></i></span>
                <span class="brand-copy">
                    <span class="brand-title">Panel Bendahara</span>
                    <span class="brand-subtitle">Kas Kelas</span>
                </span>
            </a>

            <button class="nav-toggle js-nav-toggle" type="button" data-target="#bendaharaNav" aria-expanded="false" aria-label="Buka menu" title="Buka menu">
                <i data-lucide="menu"></i>
            </button>

            <div class="nav-links" id="bendaharaNav">
                <span class="nav-user"><i data-lucide="user-round"></i><?= htmlspecialchars($_SESSION['nama']) ?></span>
                <a href="tambah_transaksi.php" class="btn btn-soft"><i data-lucide="plus"></i>Transaksi</a>
                <a href="laporan.php" class="btn btn-soft"><i data-lucide="printer"></i>Laporan</a>
                <a href="../controllers/logout.php" class="btn btn-secondary"><i data-lucide="log-out"></i>Keluar</a>
            </div>
        </div>
    </nav>

    <main class="page-shell">
        <section class="hero-band">
            <div class="hero-copy">
                <div>
                    <span class="eyebrow"><i data-lucide="sparkles"></i>Dashboard aktif</span>
                    <h1 class="hero-title">Kas kelas terkendali, catatan siap dicek.</h1>
                    <p class="hero-text">
                        Pantau saldo, input transaksi, kelola tunggakan, dan cetak laporan dari satu ruang kerja bendahara.
                    </p>
                </div>

                <div class="hero-actions">
                    <a href="tambah_transaksi.php" class="btn btn-primary"><i data-lucide="plus-circle"></i>Catat Transaksi Baru</a>
                    <a href="kelola_tagihan.php" class="btn btn-warning"><i data-lucide="badge-check"></i>Kelola Tunggakan</a>
                </div>
            </div>

            <aside class="ledger-art" aria-label="Visual arus kas">
                <div class="ledger-art-header">
                    <span>Arus kas</span>
                    <span><?= $total_transaksi ?> transaksi</span>
                </div>
                <div class="flow-meter">
                    <div class="meter-row">
                        <div class="meter-meta">
                            <span>Pemasukan</span>
                            <strong>Rp <?= number_format($total_pemasukan, 0, ',', '.') ?></strong>
                        </div>
                        <div class="meter-track">
                            <div class="meter-fill is-income" style="width: <?= $pemasukan_pct ?>%"></div>
                        </div>
                    </div>
                    <div class="meter-row">
                        <div class="meter-meta">
                            <span>Pengeluaran</span>
                            <strong>Rp <?= number_format($total_pengeluaran, 0, ',', '.') ?></strong>
                        </div>
                        <div class="meter-track">
                            <div class="meter-fill is-expense" style="width: <?= $pengeluaran_pct ?>%"></div>
                        </div>
                    </div>
                </div>

                <div class="ledger-visual" aria-hidden="true">
                    <div class="ledger-sheet">
                        <div class="ledger-line"></div>
                        <div class="ledger-line"></div>
                        <div class="ledger-line"></div>
                        <div class="ledger-line"></div>
                    </div>
                    <div class="bar-stack">
                        <span style="height: 54px"></span>
                        <span style="height: 92px"></span>
                        <span style="height: 118px"></span>
                        <span style="height: 72px"></span>
                    </div>
                </div>
            </aside>
        </section>

        <section class="stat-grid" aria-label="Ringkasan kas">
            <article class="stat-card">
                <span class="stat-label"><i data-lucide="landmark"></i>Total Saldo</span>
                <strong class="stat-value">Rp <?= number_format($total_saldo, 0, ',', '.') ?></strong>
                <span class="stat-caption">Saldo bersih dari semua transaksi.</span>
            </article>
            <article class="stat-card is-income">
                <span class="stat-label"><i data-lucide="trending-up"></i>Pemasukan</span>
                <strong class="stat-value">Rp <?= number_format($total_pemasukan, 0, ',', '.') ?></strong>
                <span class="stat-caption">Total uang masuk yang tercatat.</span>
            </article>
            <article class="stat-card is-expense">
                <span class="stat-label"><i data-lucide="trending-down"></i>Pengeluaran</span>
                <strong class="stat-value">Rp <?= number_format($total_pengeluaran, 0, ',', '.') ?></strong>
                <span class="stat-caption">Total penggunaan kas kelas.</span>
            </article>
            <article class="stat-card is-warning">
                <span class="stat-label"><i data-lucide="circle-alert"></i>Menunggak</span>
                <strong class="stat-value"><?= $total_belum ?> tagihan</strong>
                <span class="stat-caption"><?= $total_lunas ?> tagihan sudah lunas.</span>
            </article>
        </section>

        <section class="content-grid">
            <div class="surface-panel">
                <div class="table-toolbar">
                    <div>
                        <h2 class="section-title">Riwayat Transaksi Terbaru</h2>
                        <p class="section-note">Lima transaksi terakhir yang masuk ke buku kas.</p>
                    </div>
                    <label class="search-box">
                        <i data-lucide="search"></i>
                        <input type="search" class="form-control js-table-search" data-target="#recentTransactions" placeholder="Cari transaksi">
                    </label>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover" id="recentTransactions">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Kategori</th>
                                <th>Keterangan</th>
                                <th>Jenis</th>
                                <th>Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query_history = "
                                SELECT t.tanggal, t.keterangan, t.jumlah, c.nama_kategori, c.jenis
                                FROM transactions t
                                JOIN categories c ON t.id_kategori = c.id_kategori
                                ORDER BY t.tanggal DESC
                                LIMIT 5
                            ";
                            $result_history = $koneksi->query($query_history)->fetchAll();

                            if (!empty($result_history)) {
                                foreach ($result_history as $row) {
                                    $is_pemasukan = $row['jenis'] === 'pemasukan';
                                    $tanda = $is_pemasukan ? '+' : '-';
                                    $amount_class = $is_pemasukan ? 'is-income' : 'is-expense';
                                    $badge_class = $is_pemasukan ? 'bg-success' : 'bg-danger';
                                    echo "<tr>";
                                    echo "<td>" . date('d M Y', strtotime($row['tanggal'])) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['nama_kategori']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['keterangan']) . "</td>";
                                    echo "<td><span class='badge {$badge_class}'>" . htmlspecialchars(ucfirst($row['jenis'])) . "</span></td>";
                                    echo "<td class='amount {$amount_class}'>{$tanda} Rp " . number_format($row['jumlah'], 0, ',', '.') . "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr data-empty='true'><td colspan='5' class='empty-state'>Belum ada transaksi.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <aside class="stack">
                <section class="surface-panel">
                    <div class="section-head">
                        <div>
                            <h2 class="section-title">Aksi Cepat</h2>
                            <p class="section-note">Shortcut kerja harian bendahara.</p>
                        </div>
                    </div>

                    <div class="action-grid action-list">
                        <a href="tambah_transaksi.php" class="action-tile">
                            <span class="action-icon"><i data-lucide="receipt-text"></i></span>
                            <span>
                                <span class="action-title d-block">Catat Transaksi</span>
                                <span class="action-copy d-block">Input pemasukan atau pengeluaran baru.</span>
                            </span>
                        </a>
                        <a href="kelola_tagihan.php" class="action-tile">
                            <span class="action-icon"><i data-lucide="clipboard-check"></i></span>
                            <span>
                                <span class="action-title d-block">Kelola Tagihan</span>
                                <span class="action-copy d-block">Buat tagihan dan tandai yang sudah lunas.</span>
                            </span>
                        </a>
                        <a href="laporan.php" class="action-tile">
                            <span class="action-icon"><i data-lucide="file-down"></i></span>
                            <span>
                                <span class="action-title d-block">Cetak Laporan</span>
                                <span class="action-copy d-block">Siapkan rekap kas untuk dibagikan.</span>
                            </span>
                        </a>
                    </div>
                </section>
            </aside>
        </section>
    </main>

    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="../assets/js/app.js"></script>
</body>
</html>
