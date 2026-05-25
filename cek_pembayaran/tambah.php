<?php
require_once '../config/session.php';
require_once '../config/koneksi.php';
require_once '../config/fungsi.php';

cekLogin();

$query_siswa = "SELECT * FROM tb_siswa";
$result_siswa = mysqli_query($koneksi, $query_siswa);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nisn = validasiInput($_POST['nisn']);
    $tgl_terakhir_bayar = validasiInput($_POST['tgl_terakhir_bayar']);
    $tgl_sekarang = validasiInput($_POST['tgl_sekarang']);
    $status_pembayaran = validasiInput($_POST['status_pembayaran']);
    $jumlah_bulan = validasiInput($_POST['jumlah_bulan']);
    $nama = validasiInput($_POST['nama']);
    $no_telp = validasiInput($_POST['no_telp']);
    
    $query = "INSERT INTO tb_cek_pembayaran (nisn, tgl_terakhir_bayar, tgl_sekarang, status_pembayaran, jumlah_bulan, nama, no_telp) 
              VALUES ('$nisn', '$tgl_terakhir_bayar', '$tgl_sekarang', '$status_pembayaran', '$jumlah_bulan', '$nama', '$no_telp')";
    
    if (mysqli_query($koneksi, $query)) {
        redirect('index.php', 'Data cek pembayaran berhasil ditambahkan!');
    } else {
        $error = "Error: " . mysqli_error($koneksi);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Cek Pembayaran - Aplikasi SPP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #f5f5f5; }
        .navbar { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .sidebar { background: white; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); min-height: 100vh; }
        .sidebar a { color: #333; text-decoration: none; padding: 12px 20px; display: block; border-left: 3px solid transparent; transition: all 0.3s; }
        .sidebar a:hover { background-color: #f0f0f0; border-left-color: #667eea; }
        .sidebar a.active { background-color: #f0f0f0; border-left-color: #667eea; color: #667eea; }
        .card { border: none; border-radius: 10px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1); }
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
                <a href="../pembayaran/index.php"><i class="fas fa-credit-card"></i> Pembayaran</a>
                <a href="index.php" class="active"><i class="fas fa-check-circle"></i> Cek Pembayaran</a>
                <?php if ($_SESSION['level'] == 'admin'): ?>
                    <a href="../petugas/index.php"><i class="fas fa-user-tie"></i> Data Petugas</a>
                <?php endif; ?>
            </div>

            <div class="col-md-10" style="padding: 30px;">
                <h2 class="mb-4">Tambah Cek Pembayaran</h2>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label for="nisn" class="form-label">Siswa</label>
                                <select class="form-control" id="nisn" name="nisn" required onchange="updateSiswaData()">
                                    <option value="">-- Pilih Siswa --</option>
                                    <?php 
                                    while ($row = mysqli_fetch_assoc($result_siswa)): 
                                    ?>
                                        <option value="<?php echo $row['nisn']; ?>" data-nama="<?php echo $row['nama']; ?>" data-telp="<?php echo $row['no_telp']; ?>">
                                            <?php echo $row['nisn']; ?> - <?php echo $row['nama']; ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="tgl_terakhir_bayar" class="form-label">Tgl Terakhir Bayar</label>
                                        <input type="date" class="form-control" id="tgl_terakhir_bayar" name="tgl_terakhir_bayar">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="tgl_sekarang" class="form-label">Tgl Sekarang</label>
                                        <input type="date" class="form-control" id="tgl_sekarang" name="tgl_sekarang" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="status_pembayaran" class="form-label">Status Pembayaran</label>
                                        <select class="form-control" id="status_pembayaran" name="status_pembayaran" required>
                                            <option value="Belum Lunas">Belum Lunas</option>
                                            <option value="Sudah Lunas">Sudah Lunas</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="jumlah_bulan" class="form-label">Jumlah Bulan</label>
                                        <input type="text" class="form-control" id="jumlah_bulan" name="jumlah_bulan">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="nama" class="form-label">Nama (Otomatis)</label>
                                        <input type="text" class="form-control" id="nama" name="nama" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="no_telp" class="form-label">No. Telp (Otomatis)</label>
                                        <input type="text" class="form-control" id="no_telp" name="no_telp" readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                                <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function updateSiswaData() {
            const select = document.getElementById('nisn');
            const selectedOption = select.options[select.selectedIndex];
            const nama = selectedOption.getAttribute('data-nama');
            const telp = selectedOption.getAttribute('data-telp');
            document.getElementById('nama').value = nama || '';
            document.getElementById('no_telp').value = telp || '';
        }
    </script>
</body>
</html>
