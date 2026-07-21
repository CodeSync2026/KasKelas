<?php
session_start();
require_once '../config/koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'bendahara') {
    header("Location: login.php");
    exit;
}

$query_kategori = "SELECT * FROM categories ORDER BY jenis ASC, nama_kategori ASC";
$result_kategori = $koneksi->query($query_kategori);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catat Transaksi - Kas Kelas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/app.css" rel="stylesheet">
</head>
<body class="app-body">
    <nav class="app-nav">
        <div class="nav-inner">
            <a class="nav-brand" href="dashboard_bendahara.php" aria-label="Panel Bendahara">
                <span class="brand-mark"><i data-lucide="receipt-text"></i></span>
                <span class="brand-copy">
                    <span class="brand-title">Catat Transaksi</span>
                    <span class="brand-subtitle">Pemasukan dan pengeluaran kas</span>
                </span>
            </a>

            <button class="nav-toggle js-nav-toggle" type="button" data-target="#transaksiNav" aria-expanded="false" aria-label="Buka menu" title="Buka menu">
                <i data-lucide="menu"></i>
            </button>

            <div class="nav-links" id="transaksiNav">
                <span class="nav-user"><i data-lucide="user-round"></i><?= htmlspecialchars($_SESSION['nama']) ?></span>
                <a href="dashboard_bendahara.php" class="btn btn-soft"><i data-lucide="layout-dashboard"></i>Dashboard</a>
                <a href="../controllers/logout.php" class="btn btn-secondary"><i data-lucide="log-out"></i>Keluar</a>
            </div>
        </div>
    </nav>

    <main class="page-shell">
        <div class="page-title-row">
            <div>
                <span class="page-kicker">Input transaksi</span>
                <h1 class="page-title">Tambahkan catatan kas baru.</h1>
                <p class="page-subtitle">Pilih kategori, tulis rincian, dan lampirkan bukti nota jika ada. Data ini akan langsung masuk ke dashboard bendahara dan transparansi anggota.</p>
            </div>
            <a href="dashboard_bendahara.php" class="btn btn-soft"><i data-lucide="arrow-left"></i>Kembali</a>
        </div>

        <section class="form-panel" aria-label="Form catat transaksi">
            <form action="../controllers/transaksi.php" method="POST" enctype="multipart/form-data" data-loading>
                <div class="form-grid">
                    <div>
                        <label class="form-label" for="tanggal">Tanggal Transaksi</label>
                        <input type="date" id="tanggal" name="tanggal" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>

                    <div>
                        <label class="form-label" for="id_kategori">Kategori</label>
                        <select id="id_kategori" name="id_kategori" class="form-select" required>
                            <option value="">Pilih kategori transaksi</option>
                            <?php while ($kat = $result_kategori->fetch_assoc()) : ?>
                                <option value="<?= (int) $kat['id_kategori'] ?>">
                                    <?= htmlspecialchars($kat['nama_kategori']) ?> (<?= htmlspecialchars(strtoupper($kat['jenis'])) ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="full-span">
                        <label class="form-label" for="jumlah">Jumlah (Rp)</label>
                        <input type="number" id="jumlah" name="jumlah" class="form-control js-money-input" data-preview="#jumlahPreview" placeholder="Contoh: 50000" min="1" required>
                        <div class="money-preview" id="jumlahPreview"></div>
                    </div>

                    <div class="full-span">
                        <label class="form-label" for="keterangan">Keterangan / Rincian</label>
                        <textarea id="keterangan" name="keterangan" class="form-control" rows="4" placeholder="Misal: Beli sapu dan pel untuk sarpras kelas" required></textarea>
                    </div>

                    <div class="full-span">
                        <label class="form-label" for="bukti_foto">Upload Bukti Nota / Struk</label>
                        <input type="file" id="bukti_foto" name="bukti_foto" class="form-control js-file-input" data-preview="#notaPreview" accept="image/jpeg, image/png, image/jpg">
                        <span class="field-hint">Opsional. Format JPG atau PNG, maksimal 2MB.</span>
                        <div class="file-preview" id="notaPreview">
                            <img src="" alt="Preview nota">
                            <div>
                                <strong data-file-name>Nama file</strong>
                                <span data-file-size>Ukuran file</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between gap-3 mt-4">
                    <a href="dashboard_bendahara.php" class="btn btn-soft"><i data-lucide="x"></i>Batal</a>
                    <button type="submit" name="btn_simpan_transaksi" class="btn btn-success" data-loading-text="Menyimpan transaksi...">
                        <i data-lucide="save"></i>
                        Simpan Transaksi
                    </button>
                </div>
            </form>
        </section>
    </main>

    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="../assets/js/app.js"></script>
</body>
</html>
