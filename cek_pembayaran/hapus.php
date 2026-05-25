<?php
require_once '../config/session.php';
require_once '../config/koneksi.php';
require_once '../config/fungsi.php';

cekLogin();

$id_cek = $_GET['id_cek'] ?? '';

if ($id_cek) {
    $query = "DELETE FROM tb_cek_pembayaran WHERE id_cek = '$id_cek'";
    if (mysqli_query($koneksi, $query)) {
        redirect('index.php', 'Data cek pembayaran berhasil dihapus!');
    } else {
        redirect('index.php', 'Error: ' . mysqli_error($koneksi));
    }
} else {
    redirect('index.php', 'ID Cek tidak ditemukan!');
}
?>
