<?php
/**
 * FILE: cek_pembayaran/index.php
 * DESKRIPSI: Halaman cek pembayaran dengan fitur search dan tabel siswa lunas/belum lunas
 * FITUR:
 *   - Cari siswa berdasarkan NISN atau Nama
 *   - Tampilkan tabel siswa yang sudah lunas
 *   - Tampilkan tabel siswa yang belum lunas
 *   - Tampilkan detail pembayaran siswa
 * ALGORITMA:
 *   1. Cek login user
 *   2. Jika ada parameter pencarian, cari siswa
 *   3. Query list siswa lunas dan belum lunas
 *   4. Tampilkan hasil pencarian dan tabel
 */

require_once '../config/session.php';
require_once '../config/koneksi.php';
require_once '../config/fungsi.php';

// Cek login
cekLogin();

// Inisialisasi variabel
$siswa = null;
$detail_pembayaran = array('lunas' => array(), 'belum_lunas' => array());
$keyword = '';
$search_type = 'nisn'; // nisn atau nama

// Cek apakah ada parameter pencarian
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $keyword = validasiInput($_POST['keyword']);
    $search_type = validasiInput($_POST['search_type']);
    
    // Cari siswa berdasarkan tipe pencarian
    if ($search_type == 'nisn') {
        // Cari berdasarkan NISN
        $siswa = cariSiswaByNISN($keyword);
    } else {
        // Cari berdasarkan Nama
        $hasil_cari = cariSiswaByNama($keyword);
        if (count($hasil_cari) > 0) {
            $siswa = $hasil_cari[0]; // Ambil hasil pertama
        }
    }
    
    // Jika siswa ditemukan, ambil detail pembayaran
    if ($siswa) {
        $detail_pembayaran = getDetailPembayaranSiswa($siswa['nisn']);
    }
}

// Query list siswa lunas dan belum lunas
$list_siswa_lunas = getSiswaLunas();
$list_siswa_belum_lunas = getSiswaBelumlLunas();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Pembayaran - Aplikasi SPP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <style>
        body { background-color: #f5f5f5; }
        .navbar { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .sidebar { background: white; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); min-height: 100vh; }
        .sidebar a { color: #333; text-decoration: none; padding: 12px 20px; display: block; border-left: 3px solid transparent; transition: all 0.3s; }
        .sidebar a:hover { background-color: #f0f0f0; border-left-color: #667eea; }
        .sidebar a.active { background-color: #f0f0f0; border-left-color: #667eea; color: #667eea; }
        .card { border: none; border-radius: 10px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1); }
        .info-box { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 10px; margin-bottom: 20px; }
        .badge-lunas { background-color: #28a745; }
        .badge-belum { background-color: #dc3545; }
        .table-container { max-height: 500px; overflow-y: auto; }
        .sidebar .submenu a { padding-left: 40px; font-size: 0.95rem; }
        .sidebar .menu-toggle { display: flex; justify-content: space-between; align-items: center; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="../index.php"><i class="fas fa-graduation-cap"></i> Aplikasi SPP</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><span class="nav-link">Halo, <?php echo $_SESSION['nama_petugas']; ?></span></li>
                    <li class="nav-item"><a class="nav-link" href="../logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 sidebar">
                <div style="padding: 20px; border-bottom: 1px solid #eee;"><h5>Menu</h5></div>
                <a href="../index.php"><i class="fas fa-home"></i> Dashboard</a>
                <a href="../siswa/index.php"><i class="fas fa-users"></i> Data Siswa</a>
                <a href="../kelas/index.php"><i class="fas fa-chalkboard"></i> Data Kelas</a>
                <a href="../spp/index.php"><i class="fas fa-money-bill"></i> Data SPP</a>
                <a class="menu-toggle" data-bs-toggle="collapse" href="#menuPembayaran" role="button" aria-expanded="false" aria-controls="menuPembayaran">
                    <span><i class="fas fa-credit-card"></i> Pembayaran</span>
                    <i class="fas fa-chevron-down"></i>
                </a>
                <div class="collapse submenu" id="menuPembayaran">
                    <a href="../pembayaran/index.php">Data Pembayaran</a>
                    <a href="../pembayaran/tambah.php">Input Pembayaran</a>
                    <a href="../pembayaran/history.php">History Pembayaran</a>
                </div>
                <a href="index.php" class="active"><i class="fas fa-check-circle"></i> Cek Pembayaran</a>
                <?php if ($_SESSION['level'] == 'admin'): ?>
                    <a href="../petugas/index.php"><i class="fas fa-user-tie"></i> Data Petugas</a>
                <?php endif; ?>
            </div>

            <div class="col-md-10" style="padding: 30px;">
                <h2 class="mb-4">Cek Pembayaran</h2>

                <!-- Form Pencarian -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-search"></i> Pencarian Siswa</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-3">
                                    <label class="form-label">Tipe Pencarian</label>
                                    <select class="form-control" name="search_type" required>
                                        <option value="nisn" <?php echo ($search_type == 'nisn') ? 'selected' : ''; ?>>Cari Berdasarkan NISN</option>
                                        <option value="nama" <?php echo ($search_type == 'nama') ? 'selected' : ''; ?>>Cari Berdasarkan Nama</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Keyword Pencarian</label>
                                    <input type="text" class="form-control" name="keyword" placeholder="Masukkan NISN atau Nama" value="<?php echo $keyword; ?>" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">&nbsp;</label>
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-search"></i> Cari
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Hasil Pencarian -->
                <?php if ($siswa): ?>
                    <!-- Info Siswa -->
                    <div class="info-box">
                        <h5><i class="fas fa-user"></i> Informasi Siswa</h5>
                        <div class="row mt-3">
                            <div class="col-md-3">
                                <strong>NISN:</strong> <?php echo $siswa['nisn']; ?>
                            </div>
                            <div class="col-md-3">
                                <strong>NIS:</strong> <?php echo $siswa['nis']; ?>
                            </div>
                            <div class="col-md-3">
                                <strong>Nama:</strong> <?php echo $siswa['nama']; ?>
                            </div>
                            <div class="col-md-3">
                                <strong>Kelas:</strong> <?php echo $siswa['nama_kelas']; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Tabel Pembayaran Sudah Lunas -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header bg-success text-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-check-circle"></i> 
                                        Pembayaran Sudah Lunas (<?php echo count($detail_pembayaran['lunas']); ?>)
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <?php if (count($detail_pembayaran['lunas']) > 0): ?>
                                        <div class="table-container">
                                            <table class="table table-striped table-hover table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>ID Pembayaran</th>
                                                        <th>Tanggal Bayar</th>
                                                        <th>Jumlah Bulan</th>
                                                        <th>Nominal Bayar</th>
                                                        <th>Jumlah Bayar</th>
                                                        <th>Kembalian</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($detail_pembayaran['lunas'] as $pembayaran): ?>
                                                        <tr>
                                                            <td><?php echo $pembayaran['id_pembayaran']; ?></td>
                                                            <td><?php echo formatTanggal($pembayaran['tgl_bayar']); ?></td>
                                                            <td><?php echo $pembayaran['jumlah_bulan']; ?></td>
                                                            <td><?php echo formatRupiah($pembayaran['nominal_bayar']); ?></td>
                                                            <td><?php echo formatRupiah($pembayaran['jumlah_bayar']); ?></td>
                                                            <td><?php echo formatRupiah($pembayaran['kembalian']); ?></td>
                                                            <td><span class="badge badge-lunas">Sudah Lunas</span></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-muted text-center">Tidak ada pembayaran yang sudah lunas</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabel Pembayaran Belum Lunas -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header bg-danger text-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-hourglass-half"></i> 
                                        Pembayaran Belum Lunas (<?php echo count($detail_pembayaran['belum_lunas']); ?>)
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <?php if (count($detail_pembayaran['belum_lunas']) > 0): ?>
                                        <div class="table-container">
                                            <table class="table table-striped table-hover table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>ID Pembayaran</th>
                                                        <th>Tanggal Bayar</th>
                                                        <th>Batas Pembayaran</th>
                                                        <th>Jumlah Bulan</th>
                                                        <th>Nominal Bayar</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($detail_pembayaran['belum_lunas'] as $pembayaran): ?>
                                                        <tr>
                                                            <td><?php echo $pembayaran['id_pembayaran']; ?></td>
                                                            <td><?php echo formatTanggal($pembayaran['tgl_bayar']); ?></td>
                                                            <td><?php echo formatTanggal($pembayaran['batas_pembayaran']); ?></td>
                                                            <td><?php echo $pembayaran['jumlah_bulan']; ?></td>
                                                            <td><?php echo formatRupiah($pembayaran['nominal_bayar']); ?></td>
                                                            <td><span class="badge badge-belum">Belum Lunas</span></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-muted text-center">Tidak ada pembayaran yang belum lunas</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php elseif ($keyword): ?>
                    <!-- Pesan Tidak Ditemukan -->
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle"></i> 
                        Siswa dengan <?php echo ($search_type == 'nisn') ? 'NISN' : 'Nama'; ?> "<strong><?php echo $keyword; ?></strong>" tidak ditemukan.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php else: ?>
                    <!-- Tampilkan tabel siswa lunas dan belum lunas tanpa pencarian -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-success text-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-check-circle"></i> 
                                        Siswa Sudah Lunas (<?php echo count($list_siswa_lunas); ?>)
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-container">
                                        <table class="table table-striped table-hover table-sm">
                                            <thead>
                                                <tr>
                                                    <th>NISN</th>
                                                    <th>Nama</th>
                                                    <th>Kelas</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (count($list_siswa_lunas) > 0): ?>
                                                    <?php foreach ($list_siswa_lunas as $siswa): ?>
                                                        <tr>
                                                            <td><?php echo $siswa['nisn']; ?></td>
                                                            <td><?php echo $siswa['nama']; ?></td>
                                                            <td><?php echo $siswa['nama_kelas']; ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="3" class="text-center text-muted">Tidak ada data</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-danger text-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-times-circle"></i> 
                                        Siswa Belum Lunas (<?php echo count($list_siswa_belum_lunas); ?>)
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-container">
                                        <table class="table table-striped table-hover table-sm">
                                            <thead>
                                                <tr>
                                                    <th>NISN</th>
                                                    <th>Nama</th>
                                                    <th>Kelas</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (count($list_siswa_belum_lunas) > 0): ?>
                                                    <?php foreach ($list_siswa_belum_lunas as $siswa): ?>
                                                        <tr>
                                                            <td><?php echo $siswa['nisn']; ?></td>
                                                            <td><?php echo $siswa['nama']; ?></td>
                                                            <td><?php echo $siswa['nama_kelas']; ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="3" class="text-center text-muted">Tidak ada data</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
</body>
</html>
