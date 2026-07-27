# KasKelas 🪙

Aplikasi web manajemen kas dan iuran kelas yang transparan, modern, dan mudah digunakan. Aplikasi ini dirancang menggunakan arsitektur MVC sederhana berbasis **PHP Native (PDO PostgreSQL)** dan Bootstrap 5 yang disesuaikan dengan desain visual premium.

Aplikasi ini membagi hak akses pengguna menjadi dua peran utama: **Bendahara** (pengelola kas) dan **Anggota** (transparansi kas).

---

## 🚀 Fitur Utama

### 1. Panel Bendahara (Treasurer Dashboard)
- **Ringkasan Kas Real-time**: Menampilkan total saldo, total pemasukan, total pengeluaran, dan persentase arus kas menggunakan visual progress bar yang dinamis.
- **Pencatatan Transaksi**: Input pemasukan dan pengeluaran berdasarkan kategori tertentu, lengkap dengan tanggal, jumlah nominal, deskripsi, serta fitur upload bukti nota/struk belanja (preview langsung secara instan via JS).
- **Manajemen Iuran & Tunggakan**:
  - Membuat tagihan mingguan baru per bulan untuk anggota kelas.
  - Memantau siapa saja yang belum bayar atau sudah lunas.
  - Menandai pelunasan tagihan hanya dengan satu klik.
- **Laporan Kas & Cetak (Print-ready)**: Filter riwayat transaksi berdasarkan periode tanggal tertentu dan cetak laporan fisik/PDF dengan tata letak yang bersih dan ramah printer (menyembunyikan elemen navigasi saat dicetak).

### 2. Panel Anggota (Member Dashboard)
- **Transparansi Keuangan**: Anggota dapat melihat sisa saldo kas kelas saat ini serta daftar pengeluaran tanpa perlu bertanya ulang kepada bendahara.
- **Riwayat Transaksi Terbaru**: Menampilkan rincian transaksi masuk dan keluar beserta bukti struk jika dilampirkan oleh bendahara.
- **Cek Tagihan Mandiri**: Anggota dapat memantau status iuran mingguan mereka sendiri (apakah sudah lunas atau masih menunggak pada bulan/minggu tertentu).

### 3. Keamanan & Antarmuka Premium
- **PHP Data Objects (PDO PostgreSQL)**: Mencegah celah keamanan *SQL Injection* menggunakan prepared statements bawaan PDO.
- **Password Hashing**: Sandi pengguna disimpan dengan algoritma `BCRYPT` yang aman.
- **Aesthetic UI/UX**: Menggunakan tema modern berbasis Glassmorphism, efek transisi/hover yang halus, dan dynamic ambient SVG artwork pada halaman masuk/daftar.
- **Form Helper**: Fitur pembantu di sisi klien seperti format nominal mata uang otomatis (*money-input preview*) dan deteksi ukuran file bukti transfer.

---

## 📂 Struktur Direktori

Berikut adalah struktur folder utama dari proyek KasKelas:

```text
KasKelas/
├── assets/
│   ├── css/
│   │   └── app.css          # Kustomisasi stylesheet dan desain visual premium
│   ├── js/
│   │   └── app.js           # Logika interaktif frontend (Lucide icons, preview file, money formatter)
│   └── uploads/             # Folder penyimpanan bukti transaksi/nota yang diupload
├── config/
│   └── koneksi.php          # Konfigurasi koneksi database PostgreSQL menggunakan PDO (pdo_pgsql)
├── controllers/
│   ├── auth.php             # Penanganan proses login dan inisialisasi session
│   ├── logout.php           # Penghapusan session dan pengalihan ke halaman login
│   ├── transaksi.php        # Logika penyimpanan transaksi baru dan upload foto
│   └── tagihan.php          # File controller tagihan (bila diperlukan)
├── views/
│   ├── dashboard_anggota.php   # Dashboard transparansi untuk anggota kelas
│   ├── dashboard_bendahara.php # Dashboard kelola kas untuk bendahara
│   ├── kelola_tagihan.php   # Halaman pembuatan dan konfirmasi tagihan iuran
│   ├── laporan.php          # Halaman filter laporan transaksi dan cetak dokumen
│   ├── login.php            # Halaman masuk akun
│   ├── register.php         # Halaman pendaftaran akun anggota baru
│   └── tambah_transaksi.php # Form input transaksi pemasukan/pengeluaran
├── database.sql             # Skema database PostgreSQL dan data awal (seeding)
├── index.php                # Entry point utama (sistem deteksi session dan redirect otomatis)
└── README.md                # Dokumentasi proyek
```

---

## 🛠️ Persyaratan Sistem

Sebelum menjalankan aplikasi ini, pastikan komputer Anda telah terpasang:
- **Web Server local** (Apache/Nginx dengan PHP)
- **PHP 7.4** ke atas (ekstensi `pdo_pgsql` aktif)
- **PostgreSQL Server** (versi 10+)
- **pgAdmin** atau utilitas CLI `psql`

---

## ⚙️ Petunjuk Pemasangan

Ikuti langkah-langkah berikut untuk menjalankan aplikasi KasKelas di komputer Anda:

### 1. Salin Proyek
Pindahkan atau clone folder `KasKelas` ke direktori web root server Anda (misal `htdocs` atau `www`).

### 2. Konfigurasi Database PostgreSQL
1. Buka **pgAdmin** atau terminal `psql`.
2. Buat database baru bernama `db_kas_kelas`:
   ```sql
   CREATE DATABASE db_kas_kelas;
   ```
3. Eksekusi file [database.sql](file:///d:/kodonf/github/KasKelas/database.sql) pada database `db_kas_kelas` untuk membuat tabel dan data seeding awal:
   ```bash
   psql -U postgres -d db_kas_kelas -f database.sql
   ```
   *(Atau jalankan skrip SQL di query tool pgAdmin).*

### 3. Sesuaikan Kredensial Database
Buka file konfigurasi database di [config/koneksi.php](file:///d:/kodonf/github/KasKelas/config/koneksi.php) dan sesuaikan pengaturannya dengan server PostgreSQL Anda:
```php
$host = "localhost";
$port = "5432";
$username = "postgres"; // Username PostgreSQL Anda
$password = "postgres"; // Password PostgreSQL Anda
$database = "db_kas_kelas";
```

### 4. Akses Aplikasi
Buka browser Anda dan akses alamat berikut:
```text
http://localhost/KasKelas/
```

---

## 🔐 Akun Akses Default

Untuk mempermudah pengujian awal, Anda dapat langsung masuk menggunakan akun bawaan yang telah di-seed di database:

### Akun Bendahara (Treasurer)
- **Username**: `admin_bendahara`
- **Password**: `admin123`
- **Akses**: Penuh (Dashboard Bendahara, Input Transaksi, Cetak Laporan, Kelola Tunggakan).

### Akun Anggota (Member)
- Anda dapat membuat akun anggota baru secara mandiri melalui menu **Daftar sekarang** di halaman Login.
- **Akses**: Terbatas (Transparansi Kas, Daftar Pengeluaran, Cek Tagihan Sendiri).

---

## 🛢️ Skema Database PostgreSQL (Ringkasan)

Aplikasi ini menggunakan 4 tabel utama yang saling berelasi:
1. **`users`**: Menyimpan data pengguna (id_user SERIAL, nama, username UNIQUE, password_hash, role dengan CHECK constraint).
2. **`categories`**: Menyimpan kategori transaksi dengan jenis (`pemasukan` atau `pengeluaran`).
3. **`transactions`**: Menyimpan catatan keuangan detail lengkap dengan FK ke users & categories, nilai nominal (NUMERIC 15,2), keterangan, serta bukti foto.
4. **`tagihan`**: Melacak status iuran mingguan anggota kelas per bulan (status CHECK 'belum' / 'lunas').

---

*Dibuat untuk memudahkan tata kelola administrasi keuangan kelas yang bersih, modern, dan transparan.* 🚀
