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

-- Table History Pembayaran (
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

