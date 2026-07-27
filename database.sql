-- Database Schema untuk KasKelas (PostgreSQL)
-- Eksekusi script ini pada PostgreSQL Server Anda (misal via psql atau pgAdmin)

-- 1. Membuat Database (Jalankan terpisah jika diperlukan)
-- CREATE DATABASE db_kas_kelas;
-- \c db_kas_kelas;

-- --------------------------------------------------------
-- 1. Struktur Tabel `users`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
  id_user SERIAL PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  username VARCHAR(50) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role VARCHAR(20) NOT NULL DEFAULT 'anggota' CHECK (role IN ('bendahara', 'anggota'))
);

-- --------------------------------------------------------
-- 2. Struktur Tabel `categories`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS categories (
  id_kategori SERIAL PRIMARY KEY,
  nama_kategori VARCHAR(100) NOT NULL,
  jenis VARCHAR(20) NOT NULL CHECK (jenis IN ('pemasukan', 'pengeluaran'))
);

-- --------------------------------------------------------
-- 3. Struktur Tabel `transactions`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS transactions (
  id_transaksi SERIAL PRIMARY KEY,
  id_user INT NOT NULL,
  id_kategori INT NOT NULL,
  jumlah NUMERIC(15, 2) NOT NULL,
  tanggal DATE NOT NULL,
  keterangan TEXT NOT NULL,
  bukti_foto VARCHAR(255) DEFAULT NULL,
  CONSTRAINT fk_trans_user FOREIGN KEY (id_user) REFERENCES users (id_user) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_trans_kategori FOREIGN KEY (id_kategori) REFERENCES categories (id_kategori) ON DELETE CASCADE ON UPDATE CASCADE
);

-- --------------------------------------------------------
-- 4. Struktur Tabel `tagihan`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS tagihan (
  id_tagihan SERIAL PRIMARY KEY,
  id_user INT NOT NULL,
  bulan VARCHAR(20) NOT NULL,
  minggu_ke INT NOT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'belum' CHECK (status IN ('belum', 'lunas')),
  CONSTRAINT fk_tagihan_user FOREIGN KEY (id_user) REFERENCES users (id_user) ON DELETE CASCADE ON UPDATE CASCADE
);

-- --------------------------------------------------------
-- Data Seeding (Data Awal)
-- --------------------------------------------------------

-- Seeding Kategori Transaksi Bawaan
INSERT INTO categories (id_kategori, nama_kategori, jenis) VALUES
(1, 'Uang Kas Mingguan', 'pemasukan'),
(2, 'Sumbangan / Donasi', 'pemasukan'),
(3, 'Kebutuhan ATK', 'pengeluaran'),
(4, 'Kebersihan & Sarpras', 'pengeluaran'),
(5, 'Kegiatan Kelas', 'pengeluaran'),
(6, 'Sosial / Menjenguk Teman', 'pengeluaran')
ON CONFLICT (id_kategori) DO UPDATE SET 
  nama_kategori = EXCLUDED.nama_kategori, 
  jenis = EXCLUDED.jenis;

-- Reset sequence untuk serial id_kategori agar insert selanjutnya tidak bentrok
SELECT setval('categories_id_kategori_seq', (SELECT MAX(id_kategori) FROM categories));

-- Seeding Akun Bendahara Utama Default
-- Username: admin_bendahara
-- Password: admin123
INSERT INTO users (nama, username, password_hash, role) VALUES
('Bendahara Utama', 'admin_bendahara', '$2y$12$ACbuRJ9.doKs9noht3zKau3U0s5vdXZYbQANBywlCPMUkNaGivxFW', 'bendahara')
ON CONFLICT (username) DO UPDATE SET 
  nama = EXCLUDED.nama, 
  password_hash = EXCLUDED.password_hash, 
  role = EXCLUDED.role;
