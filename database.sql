-- Create Database
CREATE DATABASE IF NOT EXISTS db_spp;
USE db_spp;

-- Table Kelas
CREATE TABLE tb_kelas (
  id_kelas VARCHAR(11) PRIMARY KEY,
  nama_kelas VARCHAR(10) NOT NULL,
  komp_keahlian VARCHAR(50) NOT NULL
);

-- Table SPP
CREATE TABLE tb_spp (
  id_spp VARCHAR(11) PRIMARY KEY,
  tahun INT NOT NULL,
  nominal VARCHAR(40) NOT NULL
);

-- Table Petugas
CREATE TABLE tb_petugas (
  id_petugas VARCHAR(11) PRIMARY KEY,
  username VARCHAR(25) NOT NULL UNIQUE,
  password VARCHAR(25) NOT NULL,
  nama_petugas VARCHAR(25) NOT NULL,
  level ENUM('admin', 'petugas', 'siswa') NOT NULL
);

-- Table Siswa
CREATE TABLE tb_siswa (
  nisn VARCHAR(10) PRIMARY KEY,
  nis VARCHAR(8) NOT NULL UNIQUE,
  nama VARCHAR(50) NOT NULL,
  id_kelas VARCHAR(11) NOT NULL,
  nama_kelas VARCHAR(10) NOT NULL,
  alamat TEXT NOT NULL,
  no_telp VARCHAR(13) NOT NULL,
  id_spp VARCHAR(40) NOT NULL,
  FOREIGN KEY (id_kelas) REFERENCES tb_kelas(id_kelas),
  FOREIGN KEY (id_spp) REFERENCES tb_spp(id_spp)
);

-- Table Pembayaran
CREATE TABLE tb_pembayaran (
  id_pembayaran VARCHAR(11) PRIMARY KEY,
  status ENUM('Belum Lunas', 'Sudah Lunas') NOT NULL,
  nisn VARCHAR(10) NOT NULL,
  tgl_bayar DATE NOT NULL,
  tgl_terakhir_bayar DATE,
  batas_pembayaran DATE NOT NULL,
  jumlah_bulan VARCHAR(10) NOT NULL,
  id_spp VARCHAR(40) NOT NULL,
  nominal_bayar VARCHAR(100) NOT NULL,
  jumlah_bayar VARCHAR(40) NOT NULL,
  kembalian VARCHAR(100),
  FOREIGN KEY (nisn) REFERENCES tb_siswa(nisn),
  FOREIGN KEY (id_spp) REFERENCES tb_spp(id_spp)
);

-- Table Cek Pembayaran
CREATE TABLE tb_cek_pembayaran (
  id_cek INT AUTO_INCREMENT PRIMARY KEY,
  nisn VARCHAR(10) NOT NULL,
  tgl_terakhir_bayar DATE,
  tgl_sekarang DATE NOT NULL,
  status_pembayaran ENUM('Belum Lunas', 'Sudah Lunas') NOT NULL,
  jumlah_bulan VARCHAR(5),
  nama VARCHAR(50) NOT NULL,
  no_telp VARCHAR(13) NOT NULL,
  FOREIGN KEY (nisn) REFERENCES tb_siswa(nisn)
);

-- Table History Pembayaran (BARU)
CREATE TABLE tb_history_pembayaran (
  id_history INT AUTO_INCREMENT PRIMARY KEY,
  id_transaksi VARCHAR(30) NOT NULL DEFAULT '',
  nisn VARCHAR(10) NOT NULL,
  nama_siswa VARCHAR(50) NOT NULL,
  bulan_bayar INT NOT NULL,
  tahun_bayar INT NOT NULL,
  bulan_dibayar VARCHAR(255) NOT NULL DEFAULT '',
  jumlah_bulan INT NOT NULL DEFAULT 0,
  nominal_spp VARCHAR(40) NOT NULL,
  jumlah_bayar VARCHAR(40) NOT NULL,
  kembalian VARCHAR(40) NOT NULL,
  tgl_pembayaran DATE NOT NULL,
  id_spp VARCHAR(40) NOT NULL,
  FOREIGN KEY (nisn) REFERENCES tb_siswa(nisn),
  FOREIGN KEY (id_spp) REFERENCES tb_spp(id_spp)
);

-- Insert Sample Data
INSERT INTO tb_kelas VALUES 
('K001', 'X-A', 'Teknik Informatika'),
('K002', 'X-B', 'Teknik Mesin'),
('K003', 'XI-A', 'Teknik Informatika');

INSERT INTO tb_spp VALUES 
('SPP001', 2024, '500000'),
('SPP002', 2025, '550000');

INSERT INTO tb_petugas VALUES 
('P001', 'admin', 'admin123', 'Admin Sekolah', 'admin'),
('P002', 'petugas1', 'petugas123', 'Petugas SPP', 'petugas');

INSERT INTO tb_siswa VALUES 
('0001234567', '12345', 'Budi Santoso', 'K001', 'X-A', 'Jl. Merdeka No. 10', '081234567890', 'SPP001'),
('0001234568', '12346', 'Siti Nurhaliza', 'K001', 'X-A', 'Jl. Sudirman No. 20', '081234567891', 'SPP001'),
('0001234569', '12347', 'Ahmad Wijaya', 'K002', 'X-B', 'Jl. Gatot Subroto No. 30', '081234567892', 'SPP001'),
('2210117500', '00012348', 'Ananda Ivana Anggraini', 'K001', 'X-A', 'Jl. Ahmad Yani No. 5', '082345678901', 'SPP001'),
('2210117501', '00012349', 'Pepen Supendi', 'K003', 'XI-C', 'Jl. Diponegoro No. 15', '082345678902', 'SPP002');

-- Insert Sample Data Pembayaran
INSERT INTO tb_pembayaran VALUES 
-- Budi Santoso - Sudah Lunas (3 bulan)
('PB001', 'Sudah Lunas', '0001234567', '2025-01-15', '2025-01-15', '2025-01-31', '1', 'SPP001', '500000', '500000', '0'),
('PB002', 'Sudah Lunas', '0001234567', '2025-02-10', '2025-02-10', '2025-02-28', '1', 'SPP001', '500000', '500000', '0'),
('PB003', 'Sudah Lunas', '0001234567', '2025-03-05', '2025-03-05', '2025-03-31', '1', 'SPP001', '500000', '500000', '0'),

-- Siti Nurhaliza - Belum Lunas (2 bulan belum bayar)
('PB004', 'Belum Lunas', '0001234568', '2025-01-01', NULL, '2025-01-31', '1', 'SPP001', '500000', '0', '0'),
('PB005', 'Belum Lunas', '0001234568', '2025-02-01', NULL, '2025-02-28', '1', 'SPP001', '500000', '0', '0'),

-- Ahmad Wijaya - Sudah Lunas (2 bulan)
('PB006', 'Sudah Lunas', '0001234569', '2025-01-20', '2025-01-20', '2025-01-31', '1', 'SPP001', '500000', '500000', '0'),
('PB007', 'Sudah Lunas', '0001234569', '2025-02-15', '2025-02-15', '2025-02-28', '1', 'SPP001', '500000', '500000', '0'),

-- Ananda Ivana Anggraini - Sudah Lunas (1 bulan)
('PB008', 'Sudah Lunas', '2210117500', '2025-03-10', '2025-03-10', '2025-03-31', '1', 'SPP001', '500000', '500000', '0'),

-- Pepen Supendi - Belum Lunas (3 bulan belum bayar)
('PB009', 'Belum Lunas', '2210117501', '2025-01-01', NULL, '2025-01-31', '1', 'SPP002', '550000', '0', '0'),
('PB010', 'Belum Lunas', '2210117501', '2025-02-01', NULL, '2025-02-28', '1', 'SPP002', '550000', '0', '0'),
('PB011', 'Belum Lunas', '2210117501', '2025-03-01', NULL, '2025-03-31', '1', 'SPP002', '550000', '0', '0');
