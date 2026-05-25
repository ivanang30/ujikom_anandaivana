<?php
require_once '../config/session.php';
require_once '../config/koneksi.php';
require_once '../config/fungsi.php';

cekLogin();

$nisn = $_GET['nisn'] ?? '';
$siswa = getSiswa($nisn);

if (!$siswa) {
    redirect('index.php', 'Data siswa tidak ditemukan!');
}

$query_kelas = "SELECT * FROM tb_kelas";
$result_kelas = mysqli_query($koneksi, $query_kelas);

$query_spp = "SELECT * FROM tb_spp";
$result_spp = mysqli_query($koneksi, $query_spp);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nis = validasiInput($_POST['nis']);
    $nama = validasiInput($_POST['nama']);
    $id_kelas = validasiInput($_POST['id_kelas']);
    $nama_kelas = validasiInput($_POST['nama_kelas']);
    $alamat = validasiInput($_POST['alamat']);
    $no_telp = validasiInput($_POST['no_telp']);
    $id_spp = validasiInput($_POST['id_spp']);
    
    // Validasi NIS tidak boleh kosong
    if (empty($nis)) {
        $error = "NIS tidak boleh kosong!";
    }
    // Validasi NIS sudah ada (jika berbeda dengan NIS lama)
    elseif ($nis != $siswa['nis'] && cekNIS($nis)) {
        $error = "NIS sudah terdaftar! Gunakan NIS yang berbeda.";
    }
    else {
        $query = "UPDATE tb_siswa SET nis='$nis', nama='$nama', id_kelas='$id_kelas', nama_kelas='$nama_kelas', 
                  alamat='$alamat', no_telp='$no_telp', id_spp='$id_spp' WHERE nisn='$nisn'";
        
        if (mysqli_query($koneksi, $query)) {
            redirect('index.php', 'Data siswa berhasil diperbarui!');
        } else {
            // Cek apakah error karena NIS duplikat
            if (strpos(mysqli_error($koneksi), 'Duplicate entry') !== false) {
                $error = "NIS sudah terdaftar! Gunakan NIS yang berbeda.";
            } else {
                $error = "Error: " . mysqli_error($koneksi);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Siswa - Aplikasi SPP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="../index.php">
                <i class="fas fa-graduation-cap"></i> Aplikasi SPP
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <span class="nav-link">Halo, <?php echo $_SESSION['nama_petugas']; ?></span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../logout.php">Logout</a>
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
                <a href="../index.php"><i class="fas fa-home"></i> Dashboard</a>
                <a href="index.php" class="active"><i class="fas fa-users"></i> Data Siswa</a>
                <a href="../kelas/index.php"><i class="fas fa-chalkboard"></i> Data Kelas</a>
                <a href="../spp/index.php"><i class="fas fa-money-bill"></i> Data SPP</a>
                <a href="../pembayaran/index.php"><i class="fas fa-credit-card"></i> Pembayaran</a>
                <a href="../cek_pembayaran/index.php"><i class="fas fa-check-circle"></i> Cek Pembayaran</a>
                <?php if ($_SESSION['level'] == 'admin'): ?>
                    <a href="../petugas/index.php"><i class="fas fa-user-tie"></i> Data Petugas</a>
                <?php endif; ?>
            </div>

            <!-- Main Content -->
            <div class="col-md-10" style="padding: 30px;">
                <h2 class="mb-4">Edit Data Siswa</h2>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="nisn" class="form-label">NISN</label>
                                        <input type="text" class="form-control" id="nisn" value="<?php echo $siswa['nisn']; ?>" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="nis" class="form-label">NIS</label>
                                        <input type="text" class="form-control" id="nis" name="nis" value="<?php echo $siswa['nis']; ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="nama" class="form-label">Nama Siswa</label>
                                <input type="text" class="form-control" id="nama" name="nama" value="<?php echo $siswa['nama']; ?>" required>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="id_kelas" class="form-label">Kelas</label>
                                        <select class="form-control" id="id_kelas" name="id_kelas" required onchange="updateNamaKelas()">
                                            <option value="">-- Pilih Kelas --</option>
                                            <?php 
                                            while ($row = mysqli_fetch_assoc($result_kelas)): 
                                            ?>
                                                <option value="<?php echo $row['id_kelas']; ?>" data-nama="<?php echo $row['nama_kelas']; ?>" 
                                                    <?php echo ($row['id_kelas'] == $siswa['id_kelas']) ? 'selected' : ''; ?>>
                                                    <?php echo $row['nama_kelas']; ?> - <?php echo $row['komp_keahlian']; ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="nama_kelas" class="form-label">Nama Kelas (Otomatis)</label>
                                        <input type="text" class="form-control" id="nama_kelas" name="nama_kelas" value="<?php echo $siswa['nama_kelas']; ?>" readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="alamat" class="form-label">Alamat</label>
                                <textarea class="form-control" id="alamat" name="alamat" rows="3" required><?php echo $siswa['alamat']; ?></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="no_telp" class="form-label">No. Telepon</label>
                                        <input type="text" class="form-control" id="no_telp" name="no_telp" value="<?php echo $siswa['no_telp']; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="id_spp" class="form-label">SPP</label>
                                        <select class="form-control" id="id_spp" name="id_spp" required>
                                            <option value="">-- Pilih SPP --</option>
                                            <?php 
                                            mysqli_data_seek($result_spp, 0);
                                            while ($row = mysqli_fetch_assoc($result_spp)): 
                                            ?>
                                                <option value="<?php echo $row['id_spp']; ?>" <?php echo ($row['id_spp'] == $siswa['id_spp']) ? 'selected' : ''; ?>>
                                                    <?php echo $row['tahun']; ?> - Rp <?php echo number_format($row['nominal'], 0, ',', '.'); ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Simpan
                                </button>
                                <a href="index.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Kembali
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function updateNamaKelas() {
            const select = document.getElementById('id_kelas');
            const selectedOption = select.options[select.selectedIndex];
            const namaKelas = selectedOption.getAttribute('data-nama');
            document.getElementById('nama_kelas').value = namaKelas || '';
        }
    </script>
</body>
</html>
