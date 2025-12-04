-- Database Schema untuk Sistem Informasi Akuntansi
-- Buat database terlebih dahulu: CREATE DATABASE sistem_akuntansi;

USE sistem_akuntansi;

-- Tabel Roles (3 Role: Admin, Akuntan, Viewer)
CREATE TABLE IF NOT EXISTS roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama_role VARCHAR(50) NOT NULL UNIQUE,
    deskripsi TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert 3 Role
INSERT INTO roles (nama_role, deskripsi) VALUES
('Admin', 'Administrator dengan akses penuh'),
('Akuntan', 'Akuntan yang dapat mengelola akun, jurnal, dan transaksi'),
('Viewer', 'Pengguna yang hanya dapat melihat data');

-- Tabel Users
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    role_id INT NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id)
);

-- Insert User Default (password: admin123)
-- Catatan: Hash password akan di-generate otomatis saat insert
-- Jika login gagal, jalankan file update_password.php untuk memperbaiki hash password

INSERT INTO users (username, password, nama_lengkap, email, role_id) VALUES
('admin', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy', 'Administrator', 'admin@example.com', 1),
('akuntan1', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy', 'Akuntan Satu', 'akuntan1@example.com', 2),
('viewer1', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy', 'Viewer Satu', 'viewer1@example.com', 3);

-- Tabel Akun (Chart of Accounts)
CREATE TABLE IF NOT EXISTS akun (
    id INT PRIMARY KEY AUTO_INCREMENT,
    kode_akun VARCHAR(20) NOT NULL UNIQUE,
    nama_akun VARCHAR(100) NOT NULL,
    jenis_akun ENUM('Aset', 'Kewajiban', 'Ekuitas', 'Pendapatan', 'Beban') NOT NULL,
    saldo_awal DECIMAL(15,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert Sample Akun
INSERT INTO akun (kode_akun, nama_akun, jenis_akun, saldo_awal) VALUES
('1001', 'Kas', 'Aset', 50000000.00),
('1002', 'Bank BCA', 'Aset', 100000000.00),
('2001', 'Hutang Usaha', 'Kewajiban', 0.00),
('3001', 'Modal', 'Ekuitas', 150000000.00),
('4001', 'Pendapatan Jasa', 'Pendapatan', 0.00),
('5001', 'Beban Gaji', 'Beban', 0.00);

-- Tabel Jurnal Umum
CREATE TABLE IF NOT EXISTS jurnal (
    id INT PRIMARY KEY AUTO_INCREMENT,
    no_jurnal VARCHAR(50) NOT NULL UNIQUE,
    tanggal DATE NOT NULL,
    keterangan TEXT,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Tabel Detail Jurnal
CREATE TABLE IF NOT EXISTS detail_jurnal (
    id INT PRIMARY KEY AUTO_INCREMENT,
    jurnal_id INT NOT NULL,
    akun_id INT NOT NULL,
    debit DECIMAL(15,2) DEFAULT 0.00,
    kredit DECIMAL(15,2) DEFAULT 0.00,
    FOREIGN KEY (jurnal_id) REFERENCES jurnal(id) ON DELETE CASCADE,
    FOREIGN KEY (akun_id) REFERENCES akun(id)
);

-- Tabel Transaksi
CREATE TABLE IF NOT EXISTS transaksi (
    id INT PRIMARY KEY AUTO_INCREMENT,
    no_transaksi VARCHAR(50) NOT NULL UNIQUE,
    tanggal DATE NOT NULL,
    jenis_transaksi ENUM('Debit', 'Kredit') NOT NULL,
    akun_id INT NOT NULL,
    jumlah DECIMAL(15,2) NOT NULL,
    keterangan TEXT,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (akun_id) REFERENCES akun(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Index untuk performa
CREATE INDEX idx_akun_kode ON akun(kode_akun);
CREATE INDEX idx_jurnal_tanggal ON jurnal(tanggal);
CREATE INDEX idx_transaksi_tanggal ON transaksi(tanggal);
CREATE INDEX idx_transaksi_akun ON transaksi(akun_id);

