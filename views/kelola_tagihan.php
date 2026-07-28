<?php
session_start();
require_once __DIR__ . '/../config/koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'bendahara') {
    header("Location: login.php");
    exit;
}

if (isset($_GET['bayar_id'])) {
    $id_tagihan = intval($_GET['bayar_id']);
    $stmt_update = $koneksi->prepare("UPDATE tagihan SET status = 'lunas' WHERE id_tagihan = ?");
    $stmt_update->execute([$id_tagihan]);

    header("Location: kelola_tagihan.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btn_tambah_tagihan'])) {
    $id_user_tagih = intval($_POST['id_user']);
    $bulan = htmlspecialchars(trim($_POST['bulan']));
    $minggu_ke = intval($_POST['minggu_ke']);

    $query_insert = "INSERT INTO tagihan (id_user, bulan, minggu_ke, status) VALUES (?, ?, ?, 'belum')";
    $stmt = $koneksi->prepare($query_insert);

    if ($stmt->execute([$id_user_tagih, $bulan, $minggu_ke])) {
        echo "<script>alert('Tagihan berhasil dibuat!'); window.location='kelola_tagihan.php';</script>";
        exit;
    }

    echo "<script>alert('Gagal membuat tagihan.'); window.history.back();</script>";
    exit;
}

$query_siswa = "SELECT id_user, nama FROM users WHERE role = 'anggota' ORDER BY nama ASC";
$result_siswa = $koneksi->query($query_siswa);

$row_anggota = $koneksi->query("SELECT COUNT(*) AS total_anggota FROM users WHERE role = 'anggota'")->fetch();
$total_anggota = (int) $row_anggota['total_anggota'];

$row_status = $koneksi->query("
    SELECT
        COUNT(*) AS total_tagihan,
        COALESCE(SUM(CASE WHEN status = 'belum' THEN 1 ELSE 0 END), 0) AS total_belum,
        COALESCE(SUM(CASE WHEN status = 'lunas' THEN 1 ELSE 0 END), 0) AS total_lunas
    FROM tagihan
")->fetch();
$total_tagihan = (int) $row_status['total_tagihan'];
$total_belum = (int) $row_status['total_belum'];
$total_lunas = (int) $row_status['total_lunas'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Tunggakan - Kas Kelas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/app.css" rel="stylesheet">
</head>
<body class="app-body">
    <nav class="app-nav">
        <div class="nav-inner">
            <a class="nav-brand" href="dashboard_bendahara.php" aria-label="Panel Bendahara">
                <span class="brand-mark"><i data-lucide="clipboard-check"></i></span>
                <span class="brand-copy">
                    <span class="brand-title">Kelola Tunggakan</span>
                    <span class="brand-subtitle">Tagihan kas anggota</span>
                </span>
            </a>

            <button class="nav-toggle js-nav-toggle" type="button" data-target="#tagihanNav" aria-expanded="false" aria-label="Buka menu" title="Buka menu">
                <i data-lucide="menu"></i>
            </button>

            <div class="nav-links" id="tagihanNav">
                <span class="nav-user"><i data-lucide="user-round"></i><?= htmlspecialchars($_SESSION['nama']) ?></span>
                <a href="dashboard_bendahara.php" class="btn btn-soft"><i data-lucide="layout-dashboard"></i>Dashboard</a>
                <a href="kelola_pengguna.php" class="btn btn-soft"><i data-lucide="users"></i>Pengguna</a>
                <a href="laporan.php" class="btn btn-soft"><i data-lucide="printer"></i>Laporan</a>
                <a href="../controllers/logout.php" class="btn btn-secondary"><i data-lucide="log-out"></i>Keluar</a>
            </div>
        </div>
    </nav>

    <main class="page-shell">
        <div class="page-title-row">
            <div>
                <span class="page-kicker">Tagihan anggota</span>
                <h1 class="page-title">Buat tagihan dan pantau pelunasan.</h1>
                <p class="page-subtitle">Kelola iuran per bulan dan minggu, lalu tandai tagihan yang sudah dibayar agar dashboard anggota ikut terbarui.</p>
            </div>
            <a href="dashboard_bendahara.php" class="btn btn-soft"><i data-lucide="arrow-left"></i>Kembali</a>
        </div>

        <section class="stat-grid" aria-label="Ringkasan tagihan">
            <article class="stat-card">
                <span class="stat-label"><i data-lucide="users-round"></i>Anggota</span>
                <strong class="stat-value"><?= $total_anggota ?></strong>
                <span class="stat-caption">Akun anggota yang bisa ditagih.</span>
            </article>
            <article class="stat-card is-violet">
                <span class="stat-label"><i data-lucide="files"></i>Total Tagihan</span>
                <strong class="stat-value"><?= $total_tagihan ?></strong>
                <span class="stat-caption">Semua tagihan yang pernah dibuat.</span>
            </article>
            <article class="stat-card is-warning">
                <span class="stat-label"><i data-lucide="circle-alert"></i>Menunggak</span>
                <strong class="stat-value"><?= $total_belum ?></strong>
                <span class="stat-caption">Masih perlu ditindaklanjuti.</span>
            </article>
            <article class="stat-card is-income">
                <span class="stat-label"><i data-lucide="badge-check"></i>Lunas</span>
                <strong class="stat-value"><?= $total_lunas ?></strong>
                <span class="stat-caption">Tagihan sudah terselesaikan.</span>
            </article>
        </section>

        <section class="form-panel wide-panel mb-4" aria-label="Form tambah tagihan">
            <div class="section-head">
                <div>
                    <h2 class="section-title">Buat Tagihan Kas Baru</h2>
                    <p class="section-note">Pilih anggota, bulan, dan minggu tagihan.</p>
                </div>
            </div>

            <form action="" method="POST" data-loading>
                <div class="filter-form">
                    <div>
                        <label class="form-label" for="id_user">Pilih Anggota</label>
                        <select id="id_user" name="id_user" class="form-select" required>
                            <option value="">Pilih anggota</option>
                            <?php while ($siswa = $result_siswa->fetch()) : ?>
                                <option value="<?= (int) $siswa['id_user'] ?>"><?= htmlspecialchars($siswa['nama']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div>
                        <label class="form-label" for="bulan">Bulan</label>
                        <input type="text" id="bulan" name="bulan" class="form-control" required autocomplete="off" placeholder="Juli 2026">
                    </div>
                    <div>
                        <label class="form-label" for="minggu_ke">Minggu Ke-</label>
                        <input type="number" id="minggu_ke" name="minggu_ke" class="form-control" min="1" max="5" required placeholder="1">
                    </div>
                    <div>
                        <button type="submit" name="btn_tambah_tagihan" class="btn btn-primary w-100" data-loading-text="Membuat tagihan...">
                            <i data-lucide="plus"></i>
                            Buat Tagihan
                        </button>
                    </div>
                </div>
            </form>
        </section>

        <section class="surface-panel">
            <div class="table-toolbar">
                <div>
                    <h2 class="section-title">Data Tunggakan Kas Siswa</h2>
                    <p class="section-note">Cari nama, bulan, minggu, atau status tagihan.</p>
                </div>
                <label class="search-box">
                    <i data-lucide="search"></i>
                    <input type="search" class="form-control js-table-search" data-target="#billsTable" placeholder="Cari tagihan">
                </label>
            </div>

            <div class="table-responsive">
                <table class="table table-hover" id="billsTable">
                    <thead>
                        <tr>
                            <th>Nama Siswa</th>
                            <th>Bulan</th>
                            <th>Minggu</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query_tagihan = "
                            SELECT t.id_tagihan, u.nama, t.bulan, t.minggu_ke, t.status
                            FROM tagihan t
                            JOIN users u ON t.id_user = u.id_user
                            ORDER BY t.bulan DESC, t.minggu_ke ASC, u.nama ASC
                        ";
                        $result_tagihan = $koneksi->query($query_tagihan)->fetchAll();

                        if (!empty($result_tagihan)) {
                            foreach ($result_tagihan as $row) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['nama']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['bulan']) . "</td>";
                                echo "<td>Minggu ke-" . htmlspecialchars($row['minggu_ke']) . "</td>";

                                if ($row['status'] === 'lunas') {
                                    echo "<td><span class='badge bg-success'>Lunas</span></td>";
                                    echo "<td><span class='status-pill is-muted'>Selesai</span></td>";
                                } else {
                                    $id_tagihan = (int) $row['id_tagihan'];
                                    echo "<td><span class='badge bg-danger'>Menunggak</span></td>";
                                    echo "<td><a href='?bayar_id={$id_tagihan}' class='btn btn-sm btn-success js-confirm' data-message='Tandai tagihan ini sebagai lunas?'><i data-lucide='check'></i>Tandai Lunas</a></td>";
                                }

                                echo "</tr>";
                            }
                        } else {
                            echo "<tr data-empty='true'><td colspan='5' class='empty-state'>Belum ada data tagihan. Buat dari form di atas.</td></tr>";
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
