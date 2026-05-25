<?php
require_once '../config/session.php';
require_once '../config/koneksi.php';
require_once '../config/fungsi.php';

cekLogin();

$id_pembayaran = $_GET['id_pembayaran'] ?? '';

if ($id_pembayaran) {
    $query = "DELETE FROM tb_pembayaran WHERE id_pembayaran = '$id_pembayaran'";
    if (mysqli_query($koneksi, $query)) {
        redirect('index.php', 'Data pembayaran berhasil dihapus!');
    } else {
        redirect('index.php', 'Error: ' . mysqli_error($koneksi));
    }
} else {
    redirect('index.php', 'ID Pembayaran tidak ditemukan!');
}
?>
