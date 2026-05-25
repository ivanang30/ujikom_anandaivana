<?php
require_once '../config/session.php';
require_once '../config/koneksi.php';
require_once '../config/fungsi.php';

cekLogin();

$nisn = $_GET['nisn'] ?? '';

if ($nisn) {
    $nisn_safe = mysqli_real_escape_string($koneksi, $nisn);

    mysqli_begin_transaction($koneksi);
    try {
        $query_pembayaran = "DELETE FROM tb_pembayaran WHERE nisn = '$nisn_safe'";
        if (!mysqli_query($koneksi, $query_pembayaran)) {
            throw new Exception('Gagal hapus pembayaran: ' . mysqli_error($koneksi));
        }

        $query_siswa = "DELETE FROM tb_siswa WHERE nisn = '$nisn_safe'";
        if (!mysqli_query($koneksi, $query_siswa)) {
            throw new Exception('Gagal hapus siswa: ' . mysqli_error($koneksi));
        }

        mysqli_commit($koneksi);
        redirect('index.php', 'Data siswa berhasil dihapus!');
    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        redirect('index.php', 'Error: ' . $e->getMessage());
    }
} else {
    redirect('index.php', 'NISN tidak ditemukan!');
}
?>
