<?php
require_once '../config/session.php';
require_once '../config/koneksi.php';
require_once '../config/fungsi.php';

cekLogin();

$id_spp = $_GET['id_spp'] ?? '';

if ($id_spp) {
    $query = "DELETE FROM tb_spp WHERE id_spp = '$id_spp'";
    if (mysqli_query($koneksi, $query)) {
        redirect('index.php', 'Data SPP berhasil dihapus!');
    } else {
        redirect('index.php', 'Error: ' . mysqli_error($koneksi));
    }
} else {
    redirect('index.php', 'ID SPP tidak ditemukan!');
}
?>
