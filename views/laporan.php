<?php
session_start();
require_once '../config/koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'bendahara') {
    header("Location: login.php");
    exit;
}

function tanggal_valid($tanggal)
{
    return is_string($tanggal) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal);
}

$tanggal_awal = isset($_GET['tanggal_awal']) && tanggal_valid($_GET['tanggal_awal']) ? $_GET['tanggal_awal'] : date('Y-m-01');
$tanggal_akhir = isset($_GET['tanggal_akhir']) && tanggal_valid($_GET['tanggal_akhir']) ? $_GET['tanggal_akhir'] : date('Y-m-d');

if ($tanggal_awal > $tanggal_akhir) {
    $sementara = $tanggal_awal;
    $tanggal_awal = $tanggal_akhir;
    $tanggal_akhir = $sementara;
}

$stmt_summary = $koneksi->prepare("
    SELECT
        COALESCE(SUM(CASE WHEN c.jenis = 'pemasukan' THEN t.jumlah ELSE 0 END), 0) AS total_pemasukan,
        COALESCE(SUM(CASE WHEN c.jenis = 'pengeluaran' THEN t.jumlah ELSE 0 END), 0) AS total_pengeluaran,
        COUNT(t.id_kategori) AS total_transaksi
    FROM transactions t
    JOIN categories c ON t.id_kategori = c.id_kategori
    WHERE t.tanggal BETWEEN ? AND ?
");
$stmt_summary->execute([$tanggal_awal, $tanggal_akhir]);
$row_summary = $stmt_summary->fetch();
$total_pemasukan = (float) $row_summary['total_pemasukan'];
$total_pengeluaran = (float) $row_summary['total_pengeluaran'];
$saldo_periode = $total_pemasukan - $total_pengeluaran;
$total_transaksi = (int) $row_summary['total_transaksi'];

$stmt_transaksi = $koneksi->prepare("
    SELECT t.tanggal, t.keterangan, t.jumlah, t.bukti_foto, c.nama_kategori, c.jenis, u.nama
    FROM transactions t
    JOIN categories c ON t.id_kategori = c.id_kategori
    JOIN users u ON t.id_user = u.id_user
    WHERE t.tanggal BETWEEN ? AND ?
    ORDER BY t.tanggal ASC, c.jenis ASC
");
$stmt_transaksi->execute([$tanggal_awal, $tanggal_akhir]);
$rows_transaksi = $stmt_transaksi->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Kas - Kas Kelas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/app.css" rel="stylesheet">
</head>
<body class="app-body">
    <nav class="app-nav">
        <div class="nav-inner">
            <a class="nav-brand" href="dashboard_bendahara.php" aria-label="Panel Bendahara">
                <span class="brand-mark"><i data-lucide="file-text"></i></span>
                <span class="brand-copy">
                    <span class="brand-title">Laporan Kas</span>
                    <span class="brand-subtitle">Rekap transaksi periode</span>
                </span>
            </a>

            <button class="nav-toggle js-nav-toggle" type="button" data-target="#laporanNav" aria-expanded="false" aria-label="Buka menu" title="Buka menu">
                <i data-lucide="menu"></i>
            </button>

            <div class="nav-links" id="laporanNav">
                <span class="nav-user"><i data-lucide="user-round"></i><?= htmlspecialchars($_SESSION['nama']) ?></span>
                <a href="dashboard_bendahara.php" class="btn btn-soft"><i data-lucide="layout-dashboard"></i>Dashboard</a>
                <a href="tambah_transaksi.php" class="btn btn-soft"><i data-lucide="plus"></i>Transaksi</a>
                <a href="../controllers/logout.php" class="btn btn-secondary"><i data-lucide="log-out"></i>Keluar</a>
            </div>
        </div>
    </nav>

    <main class="page-shell">
        <div class="print-header">
            <h1>Laporan Kas Kelas</h1>
            <p>Periode <?= date('d M Y', strtotime($tanggal_awal)) ?> sampai <?= date('d M Y', strtotime($tanggal_akhir)) ?></p>
        </div>

        <div class="page-title-row">
            <div>
                <span class="page-kicker">Laporan periode</span>
                <h1 class="page-title">Rekap kas siap dicetak.</h1>
                <p class="page-subtitle">Filter tanggal untuk melihat pemasukan, pengeluaran, dan rincian transaksi yang masuk ke periode tertentu.</p>
            </div>
            <button type="button" class="btn btn-primary no-print" onclick="window.print()"><i data-lucide="printer"></i>Cetak</button>
        </div>

        <section class="form-panel wide-panel mb-4 no-print" aria-label="Filter laporan">
            <form action="" method="GET">
                <div class="filter-form">
                    <div>
                        <label class="form-label" for="tanggal_awal">Tanggal Awal</label>
                        <input type="date" id="tanggal_awal" name="tanggal_awal" class="form-control" value="<?= htmlspecialchars($tanggal_awal) ?>">
                    </div>
                    <div>
                        <label class="form-label" for="tanggal_akhir">Tanggal Akhir</label>
                        <input type="date" id="tanggal_akhir" name="tanggal_akhir" class="form-control" value="<?= htmlspecialchars($tanggal_akhir) ?>">
                    </div>
                    <div>
                        <button type="submit" class="btn btn-primary w-100"><i data-lucide="filter"></i>Terapkan Filter</button>
                    </div>
                    <div>
                        <a href="laporan.php" class="btn btn-soft w-100"><i data-lucide="rotate-ccw"></i>Reset</a>
                    </div>
                </div>
            </form>
        </section>

        <section class="stat-grid" aria-label="Ringkasan laporan">
            <article class="stat-card">
                <span class="stat-label"><i data-lucide="landmark"></i>Saldo Periode</span>
                <strong class="stat-value">Rp <?= number_format($saldo_periode, 0, ',', '.') ?></strong>
                <span class="stat-caption">Pemasukan dikurangi pengeluaran periode ini.</span>
            </article>
            <article class="stat-card is-income">
                <span class="stat-label"><i data-lucide="trending-up"></i>Pemasukan</span>
                <strong class="stat-value">Rp <?= number_format($total_pemasukan, 0, ',', '.') ?></strong>
                <span class="stat-caption">Total uang masuk dalam filter.</span>
            </article>
            <article class="stat-card is-expense">
                <span class="stat-label"><i data-lucide="trending-down"></i>Pengeluaran</span>
                <strong class="stat-value">Rp <?= number_format($total_pengeluaran, 0, ',', '.') ?></strong>
                <span class="stat-caption">Total uang keluar dalam filter.</span>
            </article>
            <article class="stat-card is-violet">
                <span class="stat-label"><i data-lucide="list-checks"></i>Transaksi</span>
                <strong class="stat-value"><?= $total_transaksi ?></strong>
                <span class="stat-caption">Jumlah catatan pada periode ini.</span>
            </article>
        </section>

        <section class="surface-panel">
            <div class="table-toolbar">
                <div>
                    <h2 class="section-title">Rincian Transaksi</h2>
                    <p class="section-note">Periode <?= date('d M Y', strtotime($tanggal_awal)) ?> sampai <?= date('d M Y', strtotime($tanggal_akhir)) ?>.</p>
                </div>
                <label class="search-box no-print">
                    <i data-lucide="search"></i>
                    <input type="search" class="form-control js-table-search" data-target="#reportTable" placeholder="Cari transaksi">
                </label>
            </div>

            <div class="table-responsive">
                <table class="table table-hover" id="reportTable">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Kategori</th>
                            <th>Jenis</th>
                            <th>Keterangan</th>
                            <th>Dicatat Oleh</th>
                            <th>Jumlah</th>
                            <th class="no-print">Nota</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (!empty($rows_transaksi)) {
                            foreach ($rows_transaksi as $row) {
                                $is_pemasukan = $row['jenis'] === 'pemasukan';
                                $tanda = $is_pemasukan ? '+' : '-';
                                $amount_class = $is_pemasukan ? 'is-income' : 'is-expense';
                                $badge_class = $is_pemasukan ? 'bg-success' : 'bg-danger';

                                echo "<tr>";
                                echo "<td>" . date('d M Y', strtotime($row['tanggal'])) . "</td>";
                                echo "<td>" . htmlspecialchars($row['nama_kategori']) . "</td>";
                                echo "<td><span class='badge {$badge_class}'>" . htmlspecialchars(ucfirst($row['jenis'])) . "</span></td>";
                                echo "<td>" . htmlspecialchars($row['keterangan']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['nama']) . "</td>";
                                echo "<td class='amount {$amount_class}'>{$tanda} Rp " . number_format($row['jumlah'], 0, ',', '.') . "</td>";

                                if ($row['bukti_foto']) {
                                    $bukti = htmlspecialchars(basename($row['bukti_foto']));
                                    echo "<td class='no-print'><a href='../assets/uploads/{$bukti}' target='_blank' class='btn btn-sm btn-soft'><i data-lucide='external-link'></i>Lihat</a></td>";
                                } else {
                                    echo "<td class='no-print'><span class='badge bg-secondary'>Tidak ada</span></td>";
                                }

                                echo "</tr>";
                            }
                        } else {
                            echo "<tr data-empty='true'><td colspan='7' class='empty-state'>Tidak ada transaksi pada periode ini.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="../assets/js/app.js"></script>
</body>
</html>
