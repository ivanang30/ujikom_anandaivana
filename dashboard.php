<?php


require_once 'config/session.php';
require_once 'config/koneksi.php';
require_once 'config/fungsi.php';

cekLogin();


$query_siswa = "SELECT COUNT(*) as total_siswa FROM tb_siswa";
$result_siswa = mysqli_query($koneksi, $query_siswa);
$data_siswa = mysqli_fetch_assoc($result_siswa);
$total_siswa = $data_siswa['total_siswa'];


$siswa_lunas = hitungSiswaLunas();

$siswa_belum_lunas = hitungSiswaBelumlLunas();

$list_siswa_lunas = getSiswaLunas();


$list_siswa_belum_lunas = getSiswaBelumlLunas();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Aplikasi SPP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f5f5;
        }
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .sidebar {
            background: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            min-height: 100vh;
        }
        .sidebar a {
            color: #333;
            text-decoration: none;
            padding: 12px 20px;
            display: block;
            border-left: 3px solid transparent;
            transition: all 0.3s;
        }
        .sidebar a:hover {
            background-color: #f0f0f0;
            border-left-color: #667eea;
        }
        .sidebar a.active {
            background-color: #f0f0f0;
            border-left-color: #667eea;
            color: #667eea;
        }
        .sidebar .submenu a { padding-left: 40px; font-size: 0.95rem; }
        .sidebar .menu-toggle { display: flex; justify-content: space-between; align-items: center; }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .stat-card {
            padding: 20px;
            color: white;
            border-radius: 10px;
            text-align: center;
        }
        .stat-card.blue {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .stat-card.green {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }
        .stat-card.red {
            background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%);
        }
        .stat-card h3 {
            font-size: 32px;
            font-weight: bold;
            margin: 0;
        }
        .stat-card p {
            margin: 5px 0 0 0;
            opacity: 0.9;
        }
        .table-container {
            max-height: 400px;
            overflow-y: auto;
        }
        .badge-lunas {
            background-color: #28a745;
        }
        .badge-belum {
            background-color: #dc3545;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">Aplikasi SPP</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <span class="nav-link">Halo, <?php echo $_SESSION['nama_petugas']; ?> (<?php echo ucfirst($_SESSION['level']); ?>)</span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar">
                <div style="padding: 20px; border-bottom: 1px solid #eee;">
                    <h5>Menu</h5>
                </div>
                <a href="index.php" class="active">Dashboard</a>
                <a href="siswa/index.php">Data Siswa</a>
                <a href="kelas/index.php">Data Kelas</a>
                <a href="spp/index.php">Data SPP</a>
                <a class="menu-toggle" data-bs-toggle="collapse" href="#menuPembayaran" role="button" aria-expanded="false" aria-controls="menuPembayaran">
                    <span>Pembayaran</span>
                    <i class="fas fa-chevron-down"></i>
                </a>
                <div class="collapse submenu" id="menuPembayaran">
                    <a href="pembayaran/index.php">Data Pembayaran</a>
                    <a href="pembayaran/tambah.php">Input Pembayaran</a>
                    <a href="pembayaran/history.php">History Pembayaran</a>
                </div>
                <a href="cek_pembayaran/index.php">Cek Pembayaran</a>
                <?php if ($_SESSION['level'] == 'admin'): ?>
                    <a href="petugas/index.php">Data Petugas</a>
                <?php endif; ?>
            </div>

            <!-- Main Content -->
            <div class="col-md-10" style="padding: 30px;">
                <h2 class="mb-4">Dashboard</h2>
                
                <!-- Statistik Siswa -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stat-card blue">
                            <h3><?php echo $total_siswa; ?></h3>
                            <p>Total Siswa</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card green">
                            <h3><?php echo $siswa_lunas; ?></h3>
                            <p>Siswa Lunas</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card red">
                            <h3><?php echo $siswa_belum_lunas; ?></h3>
                            <p>Siswa Belum Lunas</p>
                        </div>
                    </div>
                </div>

                <!-- Tabel Siswa Sudah Lunas -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0">Siswa Sudah Lunas (<?php echo count($list_siswa_lunas); ?>)</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-container">
                                    <table class="table table-sm table-hover">
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

                    <!-- Tabel Siswa Belum Lunas -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-danger text-white">
                                <h5 class="mb-0">Siswa Belum Lunas (<?php echo count($list_siswa_belum_lunas); ?>)</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-container">
                                    <table class="table table-sm table-hover">
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

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
