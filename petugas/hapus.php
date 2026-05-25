<?php
require_once '../config/session.php';
require_once '../config/koneksi.php';
require_once '../config/fungsi.php';

cekLogin();
cekLevel(['admin']);

$id_petugas = $_GET['id_petugas'] ?? '';

if ($id_petugas) {
    $query = "DELETE FROM tb_petugas WHERE id_petugas = '$id_petugas'";
    if (mysqli_query($koneksi, $query)) {
        redirect('index.php', 'Data petugas berhasil dihapus!');
    } else {
        redirect('index.php', 'Error: ' . mysqli_error($koneksi));
    }
} else {
    redirect('index.php', 'ID Petugas tidak ditemukan!');
}
?>
