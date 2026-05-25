# Aplikasi Manajemen Pembayaran SPP Siswa

Aplikasi web berbasis PHP Native untuk mengelola pembayaran SPP (Sumbangan Pembinaan Pendidikan) siswa di sekolah.

## 📋 Fitur Utama

- **Dashboard** - Menampilkan statistik pembayaran SPP
- **Data Siswa** - CRUD data siswa dengan informasi lengkap
- **Data Kelas** - Manajemen data kelas dan kompetensi keahlian
- **Data SPP** - Manajemen besaran SPP per tahun ajaran
- **Pembayaran** - Pencatatan dan manajemen pembayaran SPP siswa
- **Cek Pembayaran** - Verifikasi status pembayaran siswa
- **Data Petugas** - Manajemen user dan level akses (Admin only)
- **Sistem Login** - Autentikasi dengan role-based access control

## 🗄️ Struktur Database

### Tabel-tabel yang digunakan:

1. **tb_siswa** - Data siswa
   - nisn (PK), nis, nama, id_kelas, nama_kelas, alamat, no_telp, id_spp

2. **tb_kelas** - Data kelas
   - id_kelas (PK), nama_kelas, komp_keahlian

3. **tb_spp** - Data SPP
   - id_spp (PK), tahun, nominal

4. **tb_pembayaran** - Data pembayaran
   - id_pembayaran (PK), status, nisn, tgl_bayar, tgl_terakhir_bayar, batas_pembayaran, jumlah_bulan, id_spp, nominal_bayar, jumlah_bayar, kembalian

5. **tb_cek_pembayaran** - Verifikasi pembayaran
   - id_cek (PK), nisn, tgl_terakhir_bayar, tgl_sekarang, status_pembayaran, jumlah_bulan, nama, no_telp

6. **tb_petugas** - Data petugas/user
   - id_petugas (PK), username, password, nama_petugas, level (admin/petugas/siswa)

## 🚀 Instalasi

### 1. Setup Database

1. Buka SQLyog atau MySQL client favorit Anda
2. Jalankan query dari file `database.sql`:
   - Copy semua query dari file `database.sql`
   - Paste ke SQLyog dan execute
   - Database `db_spp` akan terbuat beserta semua tabel dan data sample

### 2. Konfigurasi Koneksi Database

Edit file `config/koneksi.php` sesuaikan dengan konfigurasi MySQL Anda:

```php
define('DB_HOST', 'localhost');  // Host MySQL
define('DB_USER', 'root');       // Username MySQL
define('DB_PASS', '');           // Password MySQL
define('DB_NAME', 'db_spp');     // Nama database
```

### 3. Jalankan Aplikasi

1. Letakkan folder aplikasi di folder `htdocs` (XAMPP) atau `www` (WAMP)
2. Buka browser dan akses: `http://localhost/nama-folder/login.php`

## 👤 Akun Demo

Setelah menjalankan `database.sql`, gunakan akun berikut untuk login:

| Username | Password | Level |
|----------|----------|-------|
| admin | admin123 | Admin |
| petugas1 | petugas123 | Petugas |

## 📁 Struktur Folder

```
aplikasi-spp/
├── config/
│   ├── koneksi.php          # Konfigurasi database
│   ├── session.php          # Manajemen session
│   └── fungsi.php           # Fungsi-fungsi umum
├── siswa/
│   ├── index.php            # List siswa
│   ├── tambah.php           # Form tambah siswa
│   ├── edit.php             # Form edit siswa
│   └── hapus.php            # Hapus siswa
├── kelas/
│   ├── index.php            # List kelas
│   ├── tambah.php           # Form tambah kelas
│   ├── edit.php             # Form edit kelas
│   └── hapus.php            # Hapus kelas
├── spp/
│   ├── index.php            # List SPP
│   ├── tambah.php           # Form tambah SPP
│   ├── edit.php             # Form edit SPP
│   └── hapus.php            # Hapus SPP
├── pembayaran/
│   ├── index.php            # List pembayaran
│   ├── tambah.php           # Form tambah pembayaran
│   ├── edit.php             # Form edit pembayaran
│   └── hapus.php            # Hapus pembayaran
├── cek_pembayaran/
│   ├── index.php            # List cek pembayaran
│   ├── tambah.php           # Form tambah cek
│   ├── edit.php             # Form edit cek
│   └── hapus.php            # Hapus cek
├── petugas/
│   ├── index.php            # List petugas (Admin only)
│   ├── tambah.php           # Form tambah petugas
│   ├── edit.php             # Form edit petugas
│   └── hapus.php            # Hapus petugas
├── login.php                # Halaman login
├── index.php                # Dashboard
├── logout.php               # Logout
├── database.sql             # Script database
└── README.md                # Dokumentasi
```

## 🔐 Fitur Keamanan

- **Session Management** - Proteksi halaman dengan session check
- **Role-Based Access Control** - Pembatasan akses berdasarkan level user
- **Input Validation** - Validasi dan sanitasi input dari user
- **SQL Injection Prevention** - Menggunakan mysqli_real_escape_string

## 🎨 Teknologi yang Digunakan

- **Backend**: PHP Native (Procedural)
- **Database**: MySQL
- **Frontend**: HTML5, CSS3, Bootstrap 5
- **JavaScript**: jQuery, DataTables
- **Icons**: Font Awesome 6

## 📝 Fitur Setiap Modul

### Dashboard
- Menampilkan statistik total siswa, pembayaran sudah lunas, dan belum lunas
- Informasi user yang sedang login

### Data Siswa
- Tambah, edit, hapus data siswa
- Validasi input dan relasi dengan kelas dan SPP
- Tabel dengan fitur search dan sort

### Data Kelas
- Manajemen kelas dan kompetensi keahlian
- CRUD lengkap dengan validasi

### Data SPP
- Manajemen besaran SPP per tahun
- Format rupiah untuk nominal

### Pembayaran
- Pencatatan pembayaran SPP siswa
- Tracking status pembayaran (Belum Lunas/Sudah Lunas)
- Perhitungan kembalian pembayaran

### Cek Pembayaran
- Verifikasi status pembayaran siswa
- Tracking tanggal pembayaran terakhir
- Informasi jumlah bulan yang belum dibayar

### Data Petugas (Admin Only)
- Manajemen user sistem
- Pengaturan level akses (Admin, Petugas, Siswa)
- CRUD petugas

## 🔧 Fungsi-Fungsi Penting

### config/fungsi.php

- `generateID()` - Generate ID otomatis dengan prefix
- `formatRupiah()` - Format angka menjadi format rupiah
- `formatTanggal()` - Format tanggal ke format Indonesia
- `getSiswa()` - Ambil data siswa berdasarkan NISN
- `getSPP()` - Ambil data SPP berdasarkan ID
- `getPembayaran()` - Ambil data pembayaran berdasarkan ID
- `hitungSisaPembayaran()` - Hitung sisa pembayaran siswa
- `validasiInput()` - Validasi dan sanitasi input
- `redirect()` - Redirect dengan pesan
- `tampilPesan()` - Tampilkan pesan session

## 📊 Contoh Penggunaan

### Login
1. Akses `http://localhost/aplikasi-spp/login.php`
2. Masukkan username: `admin` dan password: `admin123`
3. Klik Login

### Tambah Data Siswa
1. Dari dashboard, klik menu "Data Siswa"
2. Klik tombol "Tambah Siswa"
3. Isi form dengan data siswa
4. Klik "Simpan"

### Catat Pembayaran
1. Dari dashboard, klik menu "Pembayaran"
2. Klik tombol "Tambah Pembayaran"
3. Pilih siswa, isi tanggal dan nominal pembayaran
4. Klik "Simpan"

## ⚠️ Catatan Penting

- Pastikan MySQL server sudah running sebelum mengakses aplikasi
- Sesuaikan konfigurasi database di `config/koneksi.php`
- Gunakan password yang kuat untuk akun admin
- Backup database secara berkala
- Jangan ubah struktur folder tanpa update path di file PHP

## 🐛 Troubleshooting

### Koneksi Database Gagal
- Pastikan MySQL server running
- Cek username dan password di `config/koneksi.php`
- Pastikan database `db_spp` sudah dibuat

### Session Tidak Bekerja
- Pastikan folder `tmp` di server memiliki permission write
- Cek setting `session.save_path` di php.ini

### Halaman Blank
- Cek error log di browser console (F12)
- Cek error log PHP di folder logs

## 📞 Support

Untuk pertanyaan atau masalah, silakan hubungi administrator sistem.

---

**Versi**: 1.0  
**Terakhir Update**: 2026  
**Status**: Production Ready
