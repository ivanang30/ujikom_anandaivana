<?php
require_once '../config/session.php';
require_once '../config/koneksi.php';
require_once '../config/fungsi.php';

cekLogin();

$id_pembayaran = $_GET['id_pembayaran'] ?? '';
$pembayaran = getPembayaran($id_pembayaran);

if (!$pembayaran) {
    redirect('index.php', 'Data pembayaran tidak ditemukan!');
}

$query_siswa = "SELECT * FROM tb_siswa";
$result_siswa = mysqli_query($koneksi, $query_siswa);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $status = validasiInput($_POST['status']);
    $nisn = validasiInput($_POST['nisn']);
    $tgl_bayar = validasiInput($_POST['tgl_bayar']);
    $tgl_terakhir_bayar = validasiInput($_POST['tgl_terakhir_bayar']);
    $batas_pembayaran = validasiInput($_POST['batas_pembayaran']);
    $jumlah_bulan = validasiInput($_POST['jumlah_bulan']);
    $id_spp = validasiInput($_POST['id_spp']);
    $nominal_bayar = validasiInput($_POST['nominal_bayar']);
    $jumlah_bayar = validasiInput($_POST['jumlah_bayar']);
    $kembalian = validasiInput($_POST['kembalian']);
    
    $query = "UPDATE tb_pembayaran SET status='$status', nisn='$nisn', tgl_bayar='$tgl_bayar', 
              tgl_terakhir_bayar='$tgl_terakhir_bayar', batas_pembayaran='$batas_pembayaran', 
              jumlah_bulan='$jumlah_bulan', id_spp='$id_spp', nominal_bayar='$nominal_bayar', 
              jumlah_bayar='$jumlah_bayar', kembalian='$kembalian' WHERE id_pembayaran='$id_pembayaran'";
    
    if (mysqli_query($koneksi, $query)) {
        redirect('index.php', 'Data pembayaran berhasil diperbarui!');
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
    <title>Edit Pembayaran - Aplikasi SPP</title>
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
                <h2 class="mb-4">Edit Data Pembayaran</h2>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="nisn" class="form-label">Siswa</label>
                                        <select class="form-control" id="nisn" name="nisn" required>
                                            <option value="">-- Pilih Siswa --</option>
                                            <?php 
                                            while ($row = mysqli_fetch_assoc($result_siswa)): 
                                            ?>
                                                <option value="<?php echo $row['nisn']; ?>" <?php echo ($row['nisn'] == $pembayaran['nisn']) ? 'selected' : ''; ?>>
                                                    <?php echo $row['nisn']; ?> - <?php echo $row['nama']; ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="status" class="form-label">Status</label>
                                        <select class="form-control" id="status" name="status" required>
                                            <option value="Belum Lunas" <?php echo ($pembayaran['status'] == 'Belum Lunas') ? 'selected' : ''; ?>>Belum Lunas</option>
                                            <option value="Sudah Lunas" <?php echo ($pembayaran['status'] == 'Sudah Lunas') ? 'selected' : ''; ?>>Sudah Lunas</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="tgl_bayar" class="form-label">Tanggal Bayar</label>
                                        <input type="date" class="form-control" id="tgl_bayar" name="tgl_bayar" value="<?php echo $pembayaran['tgl_bayar']; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="tgl_terakhir_bayar" class="form-label">Tgl Terakhir Bayar</label>
                                        <input type="date" class="form-control" id="tgl_terakhir_bayar" name="tgl_terakhir_bayar" value="<?php echo $pembayaran['tgl_terakhir_bayar']; ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="batas_pembayaran" class="form-label">Batas Pembayaran</label>
                                        <input type="date" class="form-control" id="batas_pembayaran" name="batas_pembayaran" value="<?php echo $pembayaran['batas_pembayaran']; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="jumlah_bulan" class="form-label">Jumlah Bulan</label>
                                        <input type="text" class="form-control" id="jumlah_bulan" name="jumlah_bulan" value="<?php echo $pembayaran['jumlah_bulan']; ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="id_spp" class="form-label">ID SPP</label>
                                        <input type="text" class="form-control" id="id_spp" name="id_spp" value="<?php echo $pembayaran['id_spp']; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="nominal_bayar" class="form-label">Nominal Bayar (Rp)</label>
                                        <input type="number" class="form-control" id="nominal_bayar" name="nominal_bayar" value="<?php echo $pembayaran['nominal_bayar']; ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="jumlah_bayar" class="form-label">Jumlah Bayar (Rp)</label>
                                        <input type="number" class="form-control" id="jumlah_bayar" name="jumlah_bayar" value="<?php echo $pembayaran['jumlah_bayar']; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="kembalian" class="form-label">Kembalian (Rp)</label>
                                        <input type="number" class="form-control" id="kembalian" name="kembalian" value="<?php echo $pembayaran['kembalian']; ?>">
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
</body>
</html>
