<?php
require_once '../config/session.php';
require_once '../config/koneksi.php';
require_once '../config/fungsi.php';

cekLogin();

$id_kelas = $_GET['id_kelas'] ?? '';

if ($id_kelas) {
    $query = "DELETE FROM tb_kelas WHERE id_kelas = '$id_kelas'";
    if (mysqli_query($koneksi, $query)) {
        redirect('index.php', 'Data kelas berhasil dihapus!');
    } else {
        redirect('index.php', 'Error: ' . mysqli_error($koneksi));
    }
} else {
    redirect('index.php', 'ID Kelas tidak ditemukan!');
}
?>
