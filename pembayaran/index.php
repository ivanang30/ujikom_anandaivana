<?php
require_once '../config/session.php';
require_once '../config/koneksi.php';
require_once '../config/fungsi.php';

cekLogin();

$query = "SELECT p.*, s.nama, s.nisn, spp.nominal AS nominal_spp
          FROM tb_pembayaran p
          JOIN tb_siswa s ON p.nisn = s.nisn
          LEFT JOIN tb_spp spp ON p.id_spp = spp.id_spp
          WHERE p.status = 'Sudah Lunas'
          ORDER BY p.tgl_bayar DESC";
$result = mysqli_query($koneksi, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pembayaran - Aplikasi SPP</title>
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
        .sidebar .submenu a { padding-left: 40px; font-size: 0.95rem; }
        .sidebar .menu-toggle { display: flex; justify-content: space-between; align-items: center; }
        .badge-lunas { background-color: #28a745; }
        .badge-belum { background-color: #dc3545; }
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
                <a class="menu-toggle" data-bs-toggle="collapse" href="#menuPembayaran" role="button" aria-expanded="true" aria-controls="menuPembayaran">
                    <span><i class="fas fa-credit-card"></i> Pembayaran</span>
                    <i class="fas fa-chevron-down"></i>
                </a>
                <div class="collapse submenu show" id="menuPembayaran">
                    <a href="index.php" class="active">Data Pembayaran</a>
                    <a href="tambah.php">Input Pembayaran</a>
                    <a href="history.php">History Pembayaran</a>
                </div>
                <a href="../cek_pembayaran/index.php"><i class="fas fa-check-circle"></i> Cek Pembayaran</a>
                <?php if ($_SESSION['level'] == 'admin'): ?>
                    <a href="../petugas/index.php"><i class="fas fa-user-tie"></i> Data Petugas</a>
                <?php endif; ?>
            </div>

            <div class="col-md-10" style="padding: 30px;">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Data Pembayaran</h2>
                </div>

                <div class="card">
                    <div class="card-body">
                        <table id="tabelPembayaran" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID Pembayaran</th>
                                    <th>NISN</th>
                                    <th>Nama Siswa</th>
                                    <th>Tgl Bayar</th>
                                    <th>Jumlah Bayar</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                    <tr>
                                        <td><?php echo $row['id_pembayaran']; ?></td>
                                        <td><?php echo $row['nisn']; ?></td>
                                        <td><?php echo $row['nama']; ?></td>
                                        <td><?php echo formatTanggal($row['tgl_bayar']); ?></td>
                                        <?php
                                            $jumlah_bayar = (int)($row['jumlah_bayar'] ?? 0);
                                            $nominal_bayar = (int)($row['nominal_bayar'] ?? 0);
                                            $nominal_spp = (int)($row['nominal_spp'] ?? 0);
                                            $tampil_bayar = $jumlah_bayar > 0 ? $jumlah_bayar : ($nominal_bayar > 0 ? $nominal_bayar : $nominal_spp);
                                        ?>
                                        <td><?php echo formatRupiah($tampil_bayar); ?></td>
                                        <td>
                                            <?php if ($row['status'] == 'Sudah Lunas'): ?>
                                                <span class="badge badge-lunas">Sudah Lunas</span>
                                            <?php else: ?>
                                                <span class="badge badge-belum">Belum Lunas</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="edit.php?id_pembayaran=<?php echo $row['id_pembayaran']; ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                            <a href="hapus.php?id_pembayaran=<?php echo $row['id_pembayaran']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus?')"><i class="fas fa-trash"></i></a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#tabelPembayaran').DataTable();
        });
    </script>
</body>
</html>
