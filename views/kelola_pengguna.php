<?php
session_start();
require_once __DIR__ . '/../config/koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'bendahara') {
    header("Location: login.php");
    exit;
}

// Handler ubah role
if (isset($_GET['ubah_role']) && isset($_GET['to_role'])) {
    $id_target = intval($_GET['ubah_role']);
    $new_role = $_GET['to_role'] === 'bendahara' ? 'bendahara' : 'anggota';

    // Cegah bendahara mengubah jabatannya sendiri jika dia satu-satunya bendahara
    if ($id_target === (int) $_SESSION['id_user'] && $new_role === 'anggota') {
        $count_bendahara = $koneksi->query("SELECT COUNT(*) FROM users WHERE role = 'bendahara'")->fetchColumn();
        if ($count_bendahara <= 1) {
            echo "<script>alert('Tidak dapat mengubah akun Anda menjadi anggota karena Anda adalah satu-satunya bendahara.'); window.location='kelola_pengguna.php';</script>";
            exit;
        }
    }

    $stmt_role = $koneksi->prepare("UPDATE users SET role = ? WHERE id_user = ?");
    $stmt_role->execute([$new_role, $id_target]);

    header("Location: kelola_pengguna.php");
    exit;
}

// Handler tambah akun pengguna
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btn_tambah_user'])) {
    $nama = htmlspecialchars(trim($_POST['nama']));
    $username = htmlspecialchars(trim($_POST['username']));
    $password = trim($_POST['password']);
    $role = isset($_POST['role']) && in_array($_POST['role'], ['bendahara', 'anggota']) ? $_POST['role'] : 'anggota';

    $cek_username = $koneksi->prepare("SELECT username FROM users WHERE username = ?");
    $cek_username->execute([$username]);

    if ($cek_username->fetch()) {
        echo "<script>alert('Username sudah digunakan!'); window.history.back();</script>";
        exit;
    }

    $password_hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt_insert = $koneksi->prepare("INSERT INTO users (nama, username, password_hash, role) VALUES (?, ?, ?, ?)");

    if ($stmt_insert->execute([$nama, $username, $password_hash, $role])) {
        echo "<script>alert('Akun pengguna berhasil ditambahkan!'); window.location='kelola_pengguna.php';</script>";
        exit;
    }

    echo "<script>alert('Gagal menambahkan akun.'); window.history.back();</script>";
    exit;
}

$row_total = $koneksi->query("SELECT COUNT(*) AS total FROM users")->fetch();
$row_bendahara = $koneksi->query("SELECT COUNT(*) AS total FROM users WHERE role = 'bendahara'")->fetch();
$row_anggota = $koneksi->query("SELECT COUNT(*) AS total FROM users WHERE role = 'anggota'")->fetch();

$total_user = (int) $row_total['total'];
$total_bendahara = (int) $row_bendahara['total'];
$total_anggota = (int) $row_anggota['total'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pengguna - Kas Kelas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/app.css" rel="stylesheet">
</head>
<body class="app-body">
    <nav class="app-nav">
        <div class="nav-inner">
            <a class="nav-brand" href="dashboard_bendahara.php" aria-label="Panel Bendahara">
                <span class="brand-mark"><i data-lucide="users"></i></span>
                <span class="brand-copy">
                    <span class="brand-title">Kelola Pengguna</span>
                    <span class="brand-subtitle">Hak akses & role akun</span>
                </span>
            </a>

            <button class="nav-toggle js-nav-toggle" type="button" data-target="#penggunaNav" aria-expanded="false" aria-label="Buka menu" title="Buka menu">
                <i data-lucide="menu"></i>
            </button>

            <div class="nav-links" id="penggunaNav">
                <span class="nav-user"><i data-lucide="user-round"></i><?= htmlspecialchars($_SESSION['nama']) ?></span>
                <a href="dashboard_bendahara.php" class="btn btn-soft"><i data-lucide="layout-dashboard"></i>Dashboard</a>
                <a href="kelola_tagihan.php" class="btn btn-soft"><i data-lucide="clipboard-check"></i>Tagihan</a>
                <a href="tambah_transaksi.php" class="btn btn-soft"><i data-lucide="plus"></i>Transaksi</a>
                <a href="laporan.php" class="btn btn-soft"><i data-lucide="printer"></i>Laporan</a>
                <a href="../controllers/logout.php" class="btn btn-secondary"><i data-lucide="log-out"></i>Keluar</a>
            </div>
        </div>
    </nav>

    <main class="page-shell">
        <div class="page-title-row">
            <div>
                <span class="page-kicker">Manajemen Akun</span>
                <h1 class="page-title">Kelola Role & Pengguna Kas Kelas.</h1>
                <p class="page-subtitle">Tambah akun baru atau ubah role pengguna menjadi Bendahara / Anggota.</p>
            </div>
            <a href="dashboard_bendahara.php" class="btn btn-soft"><i data-lucide="arrow-left"></i>Kembali</a>
        </div>

        <section class="stat-grid" aria-label="Ringkasan akun">
            <article class="stat-card">
                <span class="stat-label"><i data-lucide="users"></i>Total Pengguna</span>
                <strong class="stat-value"><?= $total_user ?></strong>
                <span class="stat-caption">Semua akun terdaftar di sistem.</span>
            </article>
            <article class="stat-card is-income">
                <span class="stat-label"><i data-lucide="shield-check"></i>Bendahara</span>
                <strong class="stat-value"><?= $total_bendahara ?></strong>
                <span class="stat-caption">Pengelola kas kelas aktif.</span>
            </article>
            <article class="stat-card is-violet">
                <span class="stat-label"><i data-lucide="user"></i>Anggota</span>
                <strong class="stat-value"><?= $total_anggota ?></strong>
                <span class="stat-caption">Siswa/anggota terdaftar.</span>
            </article>
        </section>

        <section class="form-panel wide-panel mb-4" aria-label="Form tambah pengguna">
            <div class="section-head">
                <div>
                    <h2 class="section-title">Tambah Akun Pengguna Baru</h2>
                    <p class="section-note">Buat akun siswa atau bendahara tambahan.</p>
                </div>
            </div>

            <form action="" method="POST" data-loading>
                <div class="filter-form">
                    <div>
                        <label class="form-label" for="nama">Nama Lengkap</label>
                        <input type="text" id="nama" name="nama" class="form-control" required placeholder="Nama Pengguna">
                    </div>
                    <div>
                        <label class="form-label" for="username">Username</label>
                        <input type="text" id="username" name="username" class="form-control" required placeholder="username">
                    </div>
                    <div>
                        <label class="form-label" for="password">Password</label>
                        <input type="password" id="password" name="password" class="form-control" required placeholder="Password">
                    </div>
                    <div>
                        <label class="form-label" for="role">Role</label>
                        <select id="role" name="role" class="form-select" required>
                            <option value="anggota">Anggota</option>
                            <option value="bendahara">Bendahara</option>
                        </select>
                    </div>
                    <div>
                        <button type="submit" name="btn_tambah_user" class="btn btn-primary w-100" data-loading-text="Menyimpan...">
                            <i data-lucide="user-plus"></i>
                            Tambah Akun
                        </button>
                    </div>
                </div>
            </form>
        </section>

        <section class="surface-panel">
            <div class="table-toolbar">
                <div>
                    <h2 class="section-title">Daftar Akun Pengguna</h2>
                    <p class="section-note">Ubah role pengguna dari Anggota menjadi Bendahara atau sebaliknya.</p>
                </div>
                <label class="search-box">
                    <i data-lucide="search"></i>
                    <input type="search" class="form-control js-table-search" data-target="#usersTable" placeholder="Cari nama/username">
                </label>
            </div>

            <div class="table-responsive">
                <table class="table table-hover" id="usersTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama Lengkap</th>
                            <th>Username</th>
                            <th>Role Saat Ini</th>
                            <th>Aksi Ubah Role</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $result_users = $koneksi->query("SELECT id_user, nama, username, role FROM users ORDER BY role DESC, nama ASC")->fetchAll();

                        if (!empty($result_users)) {
                            foreach ($result_users as $user) {
                                $id_u = (int) $user['id_user'];
                                $is_ben = $user['role'] === 'bendahara';
                                $badge_cls = $is_ben ? 'bg-primary' : 'bg-secondary';
                                $role_lbl = $is_ben ? 'Bendahara' : 'Anggota';

                                echo "<tr>";
                                echo "<td>#{$id_u}</td>";
                                echo "<td>" . htmlspecialchars($user['nama']) . "</td>";
                                echo "<td><code>" . htmlspecialchars($user['username']) . "</code></td>";
                                echo "<td><span class='badge {$badge_cls}'>" . htmlspecialchars($role_lbl) . "</span></td>";
                                
                                echo "<td>";
                                if ($is_ben) {
                                    echo "<a href='?ubah_role={$id_u}&to_role=anggota' class='btn btn-sm btn-soft js-confirm' data-message='Ubah role user ini menjadi Anggota?'><i data-lucide='user'></i>Ubah ke Anggota</a>";
                                } else {
                                    echo "<a href='?ubah_role={$id_u}&to_role=bendahara' class='btn btn-sm btn-primary js-confirm' data-message='Ubah role user ini menjadi Bendahara?'><i data-lucide='shield-check'></i>Ubah ke Bendahara</a>";
                                }
                                echo "</td>";

                                echo "</tr>";
                            }
                        } else {
                            echo "<tr data-empty='true'><td colspan='5' class='empty-state'>Tidak ada pengguna.</td></tr>";
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
