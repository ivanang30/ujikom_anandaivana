<?php


require_once '../config/session.php';
require_once '../config/koneksi.php';
require_once '../config/fungsi.php';

cekLogin();

$siswa = null;
$pembayaran_belum_lunas = array();
$keyword = '';
$search_type = 'nisn'; 
$error = '';

$bulan_berjalan = date('m');
$tahun_berjalan = date('Y');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'cari') {
    $keyword = validasiInput($_POST['keyword']);
    $search_type = validasiInput($_POST['search_type']);
    
    if ($search_type == 'nisn') {
        $siswa = cariSiswaByNISN($keyword);
    } else {
        $hasil_cari = cariSiswaByNama($keyword);
        if (count($hasil_cari) > 0) {
            $siswa = $hasil_cari[0];
        }
    }
    
    if (!$siswa) {
        $error = "Siswa tidak ditemukan!";
    } else {
        
        $query = "SELECT * FROM tb_pembayaran WHERE nisn = '{$siswa['nisn']}' AND status = 'Belum Lunas' ORDER BY tgl_bayar ASC";
        $result = mysqli_query($koneksi, $query);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $pembayaran_belum_lunas[] = $row;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'bayar') {
    $nisn = validasiInput($_POST['nisn']);
    $tgl_bayar = validasiInput($_POST['tgl_bayar']);
    $jumlah_bayar = (int)$_POST['jumlah_bayar'];
    $kembalian = (int)$_POST['kembalian'];
    $id_spp = validasiInput($_POST['id_spp']);
    
    $bulan_dipilih = isset($_POST['bulan']) ? $_POST['bulan'] : array();
    
    if (empty($bulan_dipilih)) {
        $error = "Pilih minimal 1 bulan untuk dibayar!";
    } else {
        $spp = getSPP($id_spp);
        $nominal_spp = $spp['nominal'];
        $total_harus_bayar = $nominal_spp * count($bulan_dipilih);
        
        if ($jumlah_bayar < $total_harus_bayar) {
            $error = "Jumlah bayar kurang! Harus bayar minimal Rp " . number_format($total_harus_bayar, 0, ',', '.');
        } else if ($kembalian < 0) {
            $error = "Kembalian tidak boleh minus!";
        } else {
            $success_count = 0;
            $error_count = 0;
            $is_first_history = true;
            $id_transaksi = 'TRX' . date('YmdHis') . '-' . substr($nisn, -4) . '-' . mt_rand(100, 999);
            
            $siswa_data = getSiswa($nisn);
            $nama_siswa = $siswa_data['nama'];
            
            foreach ($bulan_dipilih as $id_pembayaran) {
                $id_pembayaran = validasiInput($id_pembayaran);
                
                $query_cek = "SELECT * FROM tb_pembayaran WHERE id_pembayaran = '$id_pembayaran' AND nisn = '$nisn' AND status = 'Belum Lunas'";
                $result_cek = mysqli_query($koneksi, $query_cek);
                
                if (mysqli_num_rows($result_cek) > 0) {
                    $row_cek = mysqli_fetch_assoc($result_cek);
                    $bulan_bayar = date('m', strtotime($row_cek['tgl_bayar']));
                    $tahun_bayar = date('Y', strtotime($row_cek['tgl_bayar']));
                    
                    $query_update = "UPDATE tb_pembayaran 
                                    SET status = 'Sudah Lunas', 
                                        tgl_terakhir_bayar = '$tgl_bayar',
                                        nominal_bayar = '$nominal_spp',
                                        jumlah_bayar = '$nominal_spp',
                                        kembalian = '0'
                                    WHERE id_pembayaran = '$id_pembayaran'";
                    
                    if (mysqli_query($koneksi, $query_update)) {
                        $jumlah_bayar_row = $is_first_history ? $jumlah_bayar : 0;
                        $kembalian_row = $is_first_history ? $kembalian : 0;

                        $query_history = "INSERT INTO tb_history_pembayaran 
                                         (id_transaksi, nisn, nama_siswa, bulan_bayar, tahun_bayar, nominal_spp, jumlah_bayar, kembalian, tgl_pembayaran, id_spp)
                                         VALUES ('$id_transaksi', '$nisn', '$nama_siswa', '$bulan_bayar', '$tahun_bayar', '$nominal_spp', '$jumlah_bayar_row', '$kembalian_row', '$tgl_bayar', '$id_spp')";

                        if (mysqli_query($koneksi, $query_history)) {
                            $success_count++;
                            $is_first_history = false;
                        } else {
                            $error_count++;
                        }
                    } else {
                        $error_count++;
                    }
                }
            }
            
            if ($success_count > 0) {
                redirect('index.php', "Pembayaran berhasil diupdate! ($success_count bulan)");
            } else {
                $error = "Gagal mengupdate pembayaran!";
            }
        }
    }
}

function getNamaBulan($bulan) {
    $bulan_names = array(
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    );
    return $bulan_names[(int)$bulan] ?? '';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Pembayaran - Aplikasi SPP</title>
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
        .info-box { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px; border-radius: 10px; margin-bottom: 20px; }
        .table-container { max-height: 400px; overflow-y: auto; }
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
                    <a href="index.php">Data Pembayaran</a>
                    <a href="tambah.php" class="active">Input Pembayaran</a>
                    <a href="history.php">History Pembayaran</a>
                </div>
                <a href="../cek_pembayaran/index.php"><i class="fas fa-check-circle"></i> Cek Pembayaran</a>
                <?php if ($_SESSION['level'] == 'admin'): ?>
                    <a href="../petugas/index.php"><i class="fas fa-user-tie"></i> Data Petugas</a>
                <?php endif; ?>
            </div>

            <div class="col-md-10" style="padding: 30px;">
                <h2 class="mb-4">Input Pembayaran Siswa</h2>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-search"></i> Cari Siswa untuk Input Pembayaran</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="cari">
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

                <?php if (isset($error) && $error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($siswa): ?>
                    <div class="card mb-4">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="fas fa-user"></i> Data Siswa</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
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
                    </div>

                    <?php if (count($pembayaran_belum_lunas) > 0): ?>
                        <div class="card">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0"><i class="fas fa-money-bill"></i> Pembayaran Belum Lunas (<?php echo count($pembayaran_belum_lunas); ?>)</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <input type="hidden" name="action" value="bayar">
                                    <input type="hidden" name="nisn" value="<?php echo $siswa['nisn']; ?>">
                                    <input type="hidden" name="id_spp" value="<?php echo $siswa['id_spp']; ?>">
                                    
                                    <div class="table-responsive mb-4">
                                        <table class="table table-striped table-hover table-sm">
                                            <thead>
                                                <tr>
                                                    <th style="width: 50px;">
                                                        <input type="checkbox" id="checkAll" onclick="toggleCheckAll(this)">
                                                    </th>
                                                    <th>Bulan</th>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($pembayaran_belum_lunas as $pembayaran): 
                                                    $bulan = date('m', strtotime($pembayaran['tgl_bayar']));
                                                    $nama_bulan = getNamaBulan($bulan);
                                                    $spp = getSPP($pembayaran['id_spp']);
                                                ?>
                                                    <tr>
                                                        <td>
                                                            <input type="checkbox" name="bulan[]" value="<?php echo $pembayaran['id_pembayaran']; ?>" class="bulan-checkbox">
                                                        </td>
                                                        <td><strong><?php echo $nama_bulan; ?></strong></td>
                                                        <td><?php echo formatTanggal($pembayaran['batas_pembayaran']); ?></td>
                                                        <td><?php echo formatRupiah($spp['nominal']); ?></td>
                                                        <td><span class="badge bg-danger">Belum Lunas</span></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Tanggal Pembayaran <span class="text-danger">*</span></label>
                                                <input type="date" class="form-control" name="tgl_bayar" value="<?php echo date('Y-m-d'); ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Total Harus Dibayar</label>
                                                <input type="text" class="form-control" id="totalHarusBayar" value="Rp 0" readonly style="background-color: #f0f0f0; font-weight: bold;">
                                                <small class="text-muted">Otomatis dihitung dari bulan yang dipilih</small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Jumlah Bayar (Rp) <span class="text-danger">*</span></label>
                                                <input type="number" class="form-control" id="jumlahBayar" name="jumlah_bayar" placeholder="0" required onchange="hitungKembalian()" oninput="hitungKembalian()">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Kembalian (Rp)</label>
                                                <input type="text" class="form-control" id="kembalianDisplay" value="Rp 0" readonly style="background-color: #f0f0f0; font-weight: bold;">
                                                <input type="hidden" id="kembalianValue" name="kembalian" value="0">
                                                <small id="kembalianStatus" class="text-muted"></small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-success" id="btnSimpan">
                                            <i class="fas fa-check"></i> Simpan Pembayaran
                                        </button>
                                        <button type="button" class="btn btn-secondary" onclick="location.reload()">
                                            <i class="fas fa-redo"></i> Cari Siswa Lain
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle"></i> 
                            Siswa <strong><?php echo $siswa['nama']; ?></strong> sudah lunas semua pembayaran!
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                <?php elseif ($keyword): ?>
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle"></i> 
                        Siswa dengan <?php echo ($search_type == 'nisn') ? 'NISN' : 'Nama'; ?> "<strong><?php echo $keyword; ?></strong>" tidak ditemukan.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const nominalSPP = <?php 
            if ($siswa) {
                $spp = getSPP($siswa['id_spp']);
                echo $spp['nominal'];
            } else {
                echo '0';
            }
        ?>;

        function toggleCheckAll(checkbox) {
            const checkboxes = document.querySelectorAll('.bulan-checkbox');
            checkboxes.forEach(cb => cb.checked = checkbox.checked);
            hitungTotal();
        }

        document.querySelectorAll('.bulan-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const checkAll = document.getElementById('checkAll');
                const allChecked = document.querySelectorAll('.bulan-checkbox:checked').length === document.querySelectorAll('.bulan-checkbox').length;
                checkAll.checked = allChecked;
                hitungTotal();
            });
        });

        function hitungTotal() {
            const bulanDipilih = document.querySelectorAll('.bulan-checkbox:checked').length;
            const totalHarusBayar = nominalSPP * bulanDipilih;
            
            const totalDisplay = new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(totalHarusBayar);
            
            document.getElementById('totalHarusBayar').value = totalDisplay;
            
            hitungKembalian();
        }

        function hitungKembalian() {
            const jumlahBayar = parseInt(document.getElementById('jumlahBayar').value) || 0;
            const totalHarusBayar = nominalSPP * document.querySelectorAll('.bulan-checkbox:checked').length;
            const kembalian = jumlahBayar - totalHarusBayar;
            
            document.getElementById('kembalianValue').value = kembalian;
            
            const kembalianDisplay = new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(Math.abs(kembalian));
            
            const statusElement = document.getElementById('kembalianStatus');
            const btnSimpan = document.getElementById('btnSimpan');
            
            if (kembalian < 0) {
                document.getElementById('kembalianDisplay').value = '-' + kembalianDisplay;
                document.getElementById('kembalianDisplay').style.color = 'red';
                statusElement.innerHTML = '<span style="color: red;">❌ Bayar kurang sebesar ' + kembalianDisplay + '</span>';
                btnSimpan.disabled = true;
                btnSimpan.style.opacity = '0.5';
            } else if (kembalian === 0) {
                document.getElementById('kembalianDisplay').value = 'Rp 0';
                document.getElementById('kembalianDisplay').style.color = 'black';
                statusElement.innerHTML = '<span style="color: green;">✅ Pas bayar</span>';
                btnSimpan.disabled = false;
                btnSimpan.style.opacity = '1';
            } else {
                document.getElementById('kembalianDisplay').value = kembalianDisplay;
                document.getElementById('kembalianDisplay').style.color = 'green';
                statusElement.innerHTML = '<span style="color: green;">✅ Kembalian ' + kembalianDisplay + '</span>';
                btnSimpan.disabled = false;
                btnSimpan.style.opacity = '1';
            }
        }

        window.addEventListener('load', function() {
            hitungTotal();
        });
    </script>
</body>
</html>
