<?php
session_start();
require_once '../config/koneksi.php';

if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit;
}

$id_user_login = (int) $_SESSION['id_user'];

$query_saldo = "
    SELECT
        COALESCE(SUM(CASE WHEN c.jenis = 'pemasukan' THEN t.jumlah ELSE 0 END), 0) -
        COALESCE(SUM(CASE WHEN c.jenis = 'pengeluaran' THEN t.jumlah ELSE 0 END), 0) AS saldo
    FROM transactions t
    JOIN categories c ON t.id_kategori = c.id_kategori
";
$row_saldo = $koneksi->query($query_saldo)->fetch();
$total_saldo = (float) $row_saldo['saldo'];

$query_pengeluaran = "
    SELECT COALESCE(SUM(t.jumlah), 0) AS total_pengeluaran, COUNT(t.id_kategori) AS jumlah_pengeluaran
    FROM transactions t
    JOIN categories c ON t.id_kategori = c.id_kategori
    WHERE c.jenis = 'pengeluaran'
";
$row_pengeluaran = $koneksi->query($query_pengeluaran)->fetch();
$total_pengeluaran = (float) $row_pengeluaran['total_pengeluaran'];
$jumlah_pengeluaran = (int) $row_pengeluaran['jumlah_pengeluaran'];

$stmt_tagihan_summary = $koneksi->prepare("
    SELECT
        COALESCE(SUM(CASE WHEN status = 'belum' THEN 1 ELSE 0 END), 0) AS total_belum,
        COALESCE(SUM(CASE WHEN status = 'lunas' THEN 1 ELSE 0 END), 0) AS total_lunas
    FROM tagihan
    WHERE id_user = ?
");
$stmt_tagihan_summary->execute([$id_user_login]);
$row_tagihan_summary = $stmt_tagihan_summary->fetch();
$total_belum = (int) $row_tagihan_summary['total_belum'];
$total_lunas = (int) $row_tagihan_summary['total_lunas'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Anggota - Kas Kelas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/app.css" rel="stylesheet">
</head>
<body class="app-body">
    <nav class="app-nav">
        <div class="nav-inner">
            <a class="nav-brand" href="dashboard_anggota.php" aria-label="Dashboard Anggota">
                <span class="brand-mark"><i data-lucide="wallet-cards"></i></span>
                <span class="brand-copy">
                    <span class="brand-title">Transparansi Kas</span>
                    <span class="brand-subtitle">Dashboard anggota</span>
                </span>
            </a>

            <button class="nav-toggle js-nav-toggle" type="button" data-target="#anggotaNav" aria-expanded="false" aria-label="Buka menu" title="Buka menu">
                <i data-lucide="menu"></i>
            </button>

            <div class="nav-links" id="anggotaNav">
                <span class="nav-user"><i data-lucide="user-round"></i><?= htmlspecialchars($_SESSION['nama']) ?></span>
                <a href="../controllers/logout.php" class="btn btn-secondary"><i data-lucide="log-out"></i>Keluar</a>
            </div>
        </div>
    </nav>

    <main class="page-shell">
        <section class="hero-band">
            <div class="hero-copy">
                <div>
                    <span class="eyebrow"><i data-lucide="shield-check"></i>Transparansi anggota</span>
                    <h1 class="hero-title">Semua pengeluaran kelas bisa dicek tanpa bertanya ulang.</h1>
                    <p class="hero-text">
                        Lihat saldo kas terkini, pantau penggunaan dana, dan cek status tagihan pribadi dari satu dashboard.
                    </p>
                </div>

                <div class="hero-actions">
                    <a href="#pengeluaran" class="btn btn-primary"><i data-lucide="receipt"></i>Lihat Pengeluaran</a>
                    <a href="#tagihan" class="btn btn-warning"><i data-lucide="calendar-check"></i>Cek Tagihan Saya</a>
                </div>
            </div>

            <aside class="ledger-art" aria-label="Visual buku kas">
                <div class="ledger-art-header">
                    <span>Saldo kelas saat ini</span>
                    <span class="js-clock"></span>
                </div>
                <div>
                    <span class="stat-label"><i data-lucide="landmark"></i>Total saldo</span>
                    <strong class="stat-value d-block mt-2">Rp <?= number_format($total_saldo, 0, ',', '.') ?></strong>
                    <p class="section-note mt-2">Saldo dihitung dari seluruh pemasukan dikurangi pengeluaran yang tercatat.</p>
                </div>
                <div class="ledger-sheet" aria-hidden="true">
                    <div class="ledger-line"></div>
                    <div class="ledger-line"></div>
                    <div class="ledger-line"></div>
                    <div class="ledger-line"></div>
                </div>
            </aside>
        </section>

        <section class="stat-grid" aria-label="Ringkasan anggota">
            <article class="stat-card">
                <span class="stat-label"><i data-lucide="landmark"></i>Saldo Kelas</span>
                <strong class="stat-value">Rp <?= number_format($total_saldo, 0, ',', '.') ?></strong>
                <span class="stat-caption">Saldo terbaru dari kas kelas.</span>
            </article>
            <article class="stat-card is-expense">
                <span class="stat-label"><i data-lucide="shopping-bag"></i>Pengeluaran</span>
                <strong class="stat-value">Rp <?= number_format($total_pengeluaran, 0, ',', '.') ?></strong>
                <span class="stat-caption"><?= $jumlah_pengeluaran ?> pengeluaran sudah dicatat.</span>
            </article>
            <article class="stat-card is-warning">
                <span class="stat-label"><i data-lucide="circle-alert"></i>Tagihan Belum</span>
                <strong class="stat-value"><?= $total_belum ?></strong>
                <span class="stat-caption">Tagihan yang masih perlu dilunasi.</span>
            </article>
            <article class="stat-card is-income">
                <span class="stat-label"><i data-lucide="circle-check"></i>Tagihan Lunas</span>
                <strong class="stat-value"><?= $total_lunas ?></strong>
                <span class="stat-caption">Riwayat tagihan yang sudah aman.</span>
            </article>
        </section>

        <section class="content-grid">
            <div class="surface-panel" id="pengeluaran">
                <div class="table-toolbar">
                    <div>
                        <h2 class="section-title">Daftar Pengeluaran Kelas</h2>
                        <p class="section-note">Rincian penggunaan kas yang bisa dilihat semua anggota.</p>
                    </div>
                    <label class="search-box">
                        <i data-lucide="search"></i>
                        <input type="search" class="form-control js-table-search" data-target="#expenseTable" placeholder="Cari pengeluaran">
                    </label>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover" id="expenseTable">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Keterangan</th>
                                <th>Jumlah</th>
                                <th>Bukti Nota</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query_history = "
                                SELECT t.tanggal, t.keterangan, t.jumlah, t.bukti_foto
                                FROM transactions t
                                JOIN categories c ON t.id_kategori = c.id_kategori
                                WHERE c.jenis = 'pengeluaran'
                                ORDER BY t.tanggal DESC
                            ";
                            $result = $koneksi->query($query_history)->fetchAll();

                            if (!empty($result)) {
                                foreach ($result as $row) {
                                    echo "<tr>";
                                    echo "<td>" . date('d M Y', strtotime($row['tanggal'])) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['keterangan']) . "</td>";
                                    echo "<td class='amount is-expense'>- Rp " . number_format($row['jumlah'], 0, ',', '.') . "</td>";

                                    if ($row['bukti_foto']) {
                                        $bukti = htmlspecialchars(basename($row['bukti_foto']));
                                        echo "<td><a href='../assets/uploads/{$bukti}' target='_blank' class='btn btn-sm btn-soft'><i data-lucide='external-link'></i>Lihat Nota</a></td>";
                                    } else {
                                        echo "<td><span class='badge bg-secondary'>Tanpa Nota</span></td>";
                                    }

                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr data-empty='true'><td colspan='4' class='empty-state'>Belum ada pengeluaran dicatat.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <aside class="surface-panel" id="tagihan">
                <div class="table-toolbar">
                    <div>
                        <h2 class="section-title">Status Tagihan Saya</h2>
                        <p class="section-note">Pantau iuran per bulan dan minggu.</p>
                    </div>
                    <label class="search-box">
                        <i data-lucide="search"></i>
                        <input type="search" class="form-control js-table-search" data-target="#myBillsTable" placeholder="Cari tagihan">
                    </label>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover" id="myBillsTable">
                        <thead>
                            <tr>
                                <th>Bulan</th>
                                <th>Minggu</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt_tagihan = $koneksi->prepare("SELECT bulan, minggu_ke, status FROM tagihan WHERE id_user = ? ORDER BY bulan DESC, minggu_ke ASC");
                            $stmt_tagihan->execute([$id_user_login]);
                            $rows_tagihan = $stmt_tagihan->fetchAll();

                            if (!empty($rows_tagihan)) {
                                foreach ($rows_tagihan as $row) {
                                    $status_label = $row['status'] === 'lunas' ? 'Lunas' : 'Menunggak';
                                    $status_class = $row['status'] === 'lunas' ? 'bg-success' : 'bg-danger';
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['bulan']) . "</td>";
                                    echo "<td>Minggu ke-" . htmlspecialchars($row['minggu_ke']) . "</td>";
                                    echo "<td><span class='badge {$status_class}'>" . htmlspecialchars($status_label) . "</span></td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr data-empty='true'><td colspan='3' class='empty-state'>Tidak ada tagihan untuk saat ini.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </aside>
        </section>
    </main>

    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="../assets/js/app.js"></script>
</body>
</html>
