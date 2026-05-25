<?php


require_once '../config/session.php';
require_once '../config/koneksi.php';
require_once '../config/fungsi.php';

cekLogin();

$siswa = null;
$history_pembayaran = array();
$keyword = '';
$search_type = 'nisn';
$error = '';


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
        // Ambil history pembayaran
        $query = "SELECT * FROM tb_history_pembayaran WHERE nisn = '{$siswa['nisn']}' ORDER BY tgl_pembayaran DESC, id_history DESC";
        $result = mysqli_query($koneksi, $query);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $history_pembayaran[] = $row;
        }

        $history_by_transaksi = array();
        foreach ($history_pembayaran as $row) {
            $transaksi_key = $row['id_transaksi'] ?? '';
            if (!$transaksi_key) {
                $transaksi_key = 'LEGACY-' . $row['id_history'];
            }

            if (!isset($history_by_transaksi[$transaksi_key])) {
                $history_by_transaksi[$transaksi_key] = array(
                    'tgl_pembayaran' => $row['tgl_pembayaran'],
                    'rows' => array(),
                    'total_bayar' => 0,
                    'total_kembalian' => 0
                );
            }

            $history_by_transaksi[$transaksi_key]['rows'][] = $row;
            $history_by_transaksi[$transaksi_key]['total_bayar'] += (int)$row['jumlah_bayar'];
            $history_by_transaksi[$transaksi_key]['total_kembalian'] += (int)$row['kembalian'];
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
    <title>History Pembayaran - Aplikasi SPP</title>
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
        .info-box { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 10px; margin-bottom: 20px; }
        .table-container { max-height: 600px; overflow-y: auto; }
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
                    <a href="tambah.php">Input Pembayaran</a>
                    <a href="history.php" class="active">History Pembayaran</a>
                </div>
                <a href="../cek_pembayaran/index.php"><i class="fas fa-check-circle"></i> Cek Pembayaran</a>
                <?php if ($_SESSION['level'] == 'admin'): ?>
                    <a href="../petugas/index.php"><i class="fas fa-user-tie"></i> Data Petugas</a>
                <?php endif; ?>
            </div>

            <div class="col-md-10" style="padding: 30px;">
                <h2 class="mb-4">History Pembayaran</h2>

                <!-- Form Pencarian -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-search"></i> Cari Siswa</h5>
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

                <!-- Hasil Pencarian -->
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

                    <?php if (count($history_pembayaran) > 0): ?>
                        <!-- History Pembayaran -->
                        <div class="card mb-4">
                            <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="fas fa-history"></i> History Pembayaran (<?php echo count($history_by_transaksi); ?>)</h5>
                            </div>
                        </div>

                        <?php foreach ($history_by_transaksi as $transaksi_id => $transaksi): ?>
                            <?php $dom_id = preg_replace('/[^a-zA-Z0-9_-]/', '-', $transaksi_id); ?>
                            <div class="card mb-4">
                                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0"><i class="fas fa-receipt"></i> Transaksi <?php echo $transaksi_id; ?> - <?php echo formatTanggal($transaksi['tgl_pembayaran']); ?></h5>
                                    <button class="btn btn-light btn-sm" onclick="previewTransaksi('<?php echo $dom_id; ?>', '<?php echo $transaksi_id; ?>')">
                                        <i class="fas fa-file-pdf"></i> Preview PDF
                                    </button>
                                </div>
                                <div class="card-body">
                                    <div class="table-container">
                                        <table class="table table-striped table-hover table-sm">
                                            <thead>
                                                <tr>
                                                    <th>No</th>
                                                    <th>Bulan</th>
                                                    <th>Tahun</th>
                                                    <th>Nominal SPP</th>
                                                    <th>Jumlah Bayar</th>
                                                    <th>Kembalian</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $no = 1; ?>
                                                <?php foreach ($transaksi['rows'] as $row_index => $history): ?>
                                                    <tr>
                                                        <td><?php echo $no++; ?></td>
                                                        <td><?php echo getNamaBulan($history['bulan_bayar']); ?></td>
                                                        <td><?php echo $history['tahun_bayar']; ?></td>
                                                        <td><?php echo formatRupiah($history['nominal_spp']); ?></td>
                                                        <td><?php echo $row_index === 0 ? formatRupiah($transaksi['total_bayar']) : '-'; ?></td>
                                                        <td><?php echo $row_index === 0 ? formatRupiah($transaksi['total_kembalian']) : '-'; ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                            <tfoot>
                                                <tr style="background-color: #f0f0f0; font-weight: bold;">
                                                    <td colspan="4">TOTAL PEMBAYARAN</td>
                                                    <td><?php echo formatRupiah($transaksi['total_bayar']); ?></td>
                                                    <td><?php echo formatRupiah($transaksi['total_kembalian']); ?></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div id="pdf-<?php echo $dom_id; ?>" style="display: none; width: 1100px; background: white;">
                                <div style="padding: 16px; font-family: Arial, sans-serif; color: #222;">
                                    <div style="text-align: center; margin-bottom: 10px;">
                                        <div style="font-size: 18px; font-weight: bold;">LAPORAN PEMBAYARAN SPP</div>
                                        <div style="font-size: 12px; color: #555;">Transaksi: <?php echo $transaksi_id; ?></div>
                                    </div>
                                    <div style="border: 1px solid #ddd; padding: 10px; margin-bottom: 12px; border-radius: 6px;">
                                        <table style="width: 100%; font-size: 12px;">
                                            <tr>
                                                <td style="width: 12%;"><strong>NISN</strong></td>
                                                <td style="width: 38%;">: <?php echo $siswa['nisn']; ?></td>
                                                <td style="width: 12%;"><strong>NIS</strong></td>
                                                <td style="width: 38%;">: <?php echo $siswa['nis']; ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Nama</strong></td>
                                                <td>: <?php echo $siswa['nama']; ?></td>
                                                <td><strong>Kelas</strong></td>
                                                <td>: <?php echo $siswa['nama_kelas']; ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Tanggal</strong></td>
                                                <td>: <?php echo formatTanggal($transaksi['tgl_pembayaran']); ?></td>
                                                <td><strong>Transaksi</strong></td>
                                                <td>: <?php echo $transaksi_id; ?></td>
                                            </tr>
                                        </table>
                                    </div>
                                    <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
                                        <thead>
                                            <tr>
                                                <th style="border: 1px solid #333; padding: 6px; background: #f2f2f2;">No</th>
                                                <th style="border: 1px solid #333; padding: 6px; background: #f2f2f2;">Bulan</th>
                                                <th style="border: 1px solid #333; padding: 6px; background: #f2f2f2;">Tahun</th>
                                                <th style="border: 1px solid #333; padding: 6px; background: #f2f2f2;">Nominal SPP</th>
                                                <th style="border: 1px solid #333; padding: 6px; background: #f2f2f2;">Jumlah Bayar</th>
                                                <th style="border: 1px solid #333; padding: 6px; background: #f2f2f2;">Kembalian</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $no = 1; ?>
                                            <?php foreach ($transaksi['rows'] as $row_index => $history): ?>
                                                <tr>
                                                    <td style="border: 1px solid #333; padding: 6px; text-align: center;"> <?php echo $no++; ?> </td>
                                                    <td style="border: 1px solid #333; padding: 6px;"> <?php echo getNamaBulan($history['bulan_bayar']); ?> </td>
                                                    <td style="border: 1px solid #333; padding: 6px; text-align: center;"> <?php echo $history['tahun_bayar']; ?> </td>
                                                    <td style="border: 1px solid #333; padding: 6px; text-align: right;"> <?php echo formatRupiah($history['nominal_spp']); ?> </td>
                                                    <td style="border: 1px solid #333; padding: 6px; text-align: right;"> <?php echo $row_index === 0 ? formatRupiah($transaksi['total_bayar']) : '-'; ?> </td>
                                                    <td style="border: 1px solid #333; padding: 6px; text-align: right;"> <?php echo $row_index === 0 ? formatRupiah($transaksi['total_kembalian']) : '-'; ?> </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="4" style="border: 1px solid #333; padding: 6px; font-weight: bold; text-align: right;">TOTAL PEMBAYARAN</td>
                                                <td style="border: 1px solid #333; padding: 6px; font-weight: bold; text-align: right;"> <?php echo formatRupiah($transaksi['total_bayar']); ?> </td>
                                                <td style="border: 1px solid #333; padding: 6px; font-weight: bold; text-align: right;"> <?php echo formatRupiah($transaksi['total_kembalian']); ?> </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="alert alert-info alert-dismissible fade show" role="alert">
                            <i class="fas fa-info-circle"></i> 
                            Siswa <strong><?php echo $siswa['nama']; ?></strong> belum memiliki history pembayaran.
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
        function previewTransaksi(domId, transaksiId) {
            const element = document.getElementById('pdf-' + domId);
            if (!element) {
                return;
            }

            const previewWindow = window.open('', '_blank');
            if (!previewWindow) {
                return;
            }

            previewWindow.document.open();
            previewWindow.document.write(`
                <html>
                    <head>
                        <title>Preview Pembayaran ${transaksiId}</title>
                        <style>
                            body { font-family: Arial, sans-serif; color: #222; margin: 20px; }
                            table { width: 100%; border-collapse: collapse; font-size: 12px; }
                            th, td { border: 1px solid #333; padding: 6px; }
                            th { background: #f2f2f2; }
                            .toolbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
                            .toolbar button { background: #0d6efd; color: #fff; border: none; padding: 8px 12px; border-radius: 6px; cursor: pointer; }
                            .toolbar .close-btn { background: #6c757d; }
                            .toolbar span { color: #555; font-size: 12px; }
                            @media print { .toolbar { display: none; } body { margin: 0; } }
                        </style>
                    </head>
                    <body>
                        <div class="toolbar">
                            <strong>Preview Pembayaran ${transaksiId}</strong>
                            <div>
                                <span>Pilih destination: Save as PDF</span>
                                <button onclick="window.print()">Download PDF</button>
                                <button class="close-btn" onclick="window.close()">Tutup</button>
                            </div>
                        </div>
                        ${element.innerHTML}
                    </body>
                </html>
            `);
            previewWindow.document.close();
        }
    </script>
</body>
</html>
