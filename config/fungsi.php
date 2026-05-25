<?php


require_once 'koneksi.php';


function generateID($prefix, $table, $field) {
    global $koneksi;
    

    $query = "SELECT MAX(CAST(SUBSTRING($field, " . (strlen($prefix) + 1) . ") AS UNSIGNED)) as max_id 
              FROM $table WHERE $field LIKE '$prefix%'";
    
    $result = mysqli_query($koneksi, $query);
    $row = mysqli_fetch_assoc($result);
    
   
    $max_id = $row['max_id'] ?? 0;
    
    return $prefix . str_pad($max_id + 1, 3, '0', STR_PAD_LEFT);
}

function generateNIS() {
    global $koneksi;
    
    // Query untuk mencari NIS maksimal
    $query = "SELECT MAX(CAST(nis AS UNSIGNED)) as max_nis FROM tb_siswa";
    $result = mysqli_query($koneksi, $query);
    $row = mysqli_fetch_assoc($result);
    
    // Jika tidak ada data, mulai dari 0
    $max_nis = $row['max_nis'] ?? 0;
    
    // Return NIS dengan pad 0 hingga 8 digit
    return str_pad($max_nis + 1, 8, '0', STR_PAD_LEFT);
}

 
function cekNIS($nis) {
    global $koneksi;
    
    // Query untuk menghitung NIS yang sama
    $query = "SELECT COUNT(*) as total FROM tb_siswa WHERE nis = '$nis'";
    $result = mysqli_query($koneksi, $query);
    $row = mysqli_fetch_assoc($result);
    
    // Return true jika ada, false jika tidak
    return $row['total'] > 0;
}


function formatRupiah($nominal) {
    return "Rp " . number_format($nominal, 0, ',', '.');
}


function formatTanggal($tanggal) {
    $bulan = array(
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    );
    
    $split = explode('-', $tanggal);
    
    // Return format: DD Bulan YYYY
    return $split[2] . ' ' . $bulan[(int)$split[1]] . ' ' . $split[0];
}

/**
 * FUNGSI: getSiswa()
 * DESKRIPSI: Ambil data siswa berdasarkan NISN
 * PARAMETER:
 *   - $nisn (string): NISN siswa yang dicari
 * RETURN: Array data siswa atau null jika tidak ditemukan
 * CONTOH: getSiswa("0001234567") => array(nisn, nis, nama, ...)
 * ALGORITMA:
 *   1. Query SELECT dengan WHERE nisn = $nisn
 *   2. Fetch hasil query sebagai associative array
 *   3. Return data siswa
 */
function getSiswa($nisn) {
    global $koneksi;
    
    // Query untuk mencari siswa berdasarkan NISN
    $query = "SELECT * FROM tb_siswa WHERE nisn = '$nisn'";
    $result = mysqli_query($koneksi, $query);
    
    // Return data siswa
    return mysqli_fetch_assoc($result);
}

/**
 * FUNGSI: getSPP()
 * DESKRIPSI: Ambil data SPP berdasarkan ID SPP
 * PARAMETER:
 *   - $id_spp (string): ID SPP yang dicari
 * RETURN: Array data SPP atau null jika tidak ditemukan
 * CONTOH: getSPP("SPP001") => array(id_spp, tahun, nominal)
 * ALGORITMA:
 *   1. Query SELECT dengan WHERE id_spp = $id_spp
 *   2. Fetch hasil query sebagai associative array
 *   3. Return data SPP
 */
function getSPP($id_spp) {
    global $koneksi;
    
    // Query untuk mencari SPP berdasarkan ID
    $query = "SELECT * FROM tb_spp WHERE id_spp = '$id_spp'";
    $result = mysqli_query($koneksi, $query);
    
    // Return data SPP
    return mysqli_fetch_assoc($result);
}

/**
 * FUNGSI: getPembayaran()
 * DESKRIPSI: Ambil data pembayaran berdasarkan ID Pembayaran
 * PARAMETER:
 *   - $id_pembayaran (string): ID Pembayaran yang dicari
 * RETURN: Array data pembayaran atau null jika tidak ditemukan
 * CONTOH: getPembayaran("PB001") => array(id_pembayaran, nisn, status, ...)
 * ALGORITMA:
 *   1. Query SELECT dengan WHERE id_pembayaran = $id_pembayaran
 *   2. Fetch hasil query sebagai associative array
 *   3. Return data pembayaran
 */
function getPembayaran($id_pembayaran) {
    global $koneksi;
    
    // Query untuk mencari pembayaran berdasarkan ID
    $query = "SELECT * FROM tb_pembayaran WHERE id_pembayaran = '$id_pembayaran'";
    $result = mysqli_query($koneksi, $query);
    
    // Return data pembayaran
    return mysqli_fetch_assoc($result);
}

/**
 * FUNGSI: hitungSisaPembayaran()
 * DESKRIPSI: Hitung jumlah bulan pembayaran yang belum lunas untuk siswa tertentu
 * PARAMETER:
 *   - $nisn (string): NISN siswa
 * RETURN: Integer jumlah bulan yang belum lunas
 * CONTOH: hitungSisaPembayaran("0001234567") => 3
 * ALGORITMA:
 *   1. Query COUNT untuk menghitung pembayaran dengan status "Belum Lunas"
 *   2. Filter berdasarkan NISN siswa
 *   3. Return jumlah bulan yang belum lunas
 */
function hitungSisaPembayaran($nisn) {
    global $koneksi;

    $batas_bulan = date('Y-m-t');
    
    // Query untuk menghitung pembayaran belum lunas
    $query = "SELECT COUNT(*) as bulan_belum_bayar FROM tb_pembayaran 
              WHERE nisn = '$nisn' AND status = 'Belum Lunas' AND batas_pembayaran <= '$batas_bulan'";
    $result = mysqli_query($koneksi, $query);
    $row = mysqli_fetch_assoc($result);
    
    // Return jumlah bulan belum lunas
    return $row['bulan_belum_bayar'];
}

/**
 * FUNGSI: validasiInput()
 * DESKRIPSI: Validasi dan sanitasi input dari user untuk mencegah XSS dan SQL Injection
 * PARAMETER:
 *   - $data (string): Data yang akan divalidasi
 * RETURN: String data yang sudah divalidasi
 * CONTOH: validasiInput("<script>alert('xss')</script>") => "&lt;script&gt;alert('xss')&lt;/script&gt;"
 * ALGORITMA:
 *   1. Trim whitespace di awal dan akhir
 *   2. Remove backslashes (stripslashes)
 *   3. Convert special characters ke HTML entities (htmlspecialchars)
 *   4. Return data yang sudah aman
 */
function validasiInput($data) {
    // Trim whitespace
    $data = trim($data);
    
    // Remove backslashes
    $data = stripslashes($data);
    
    // Convert special characters ke HTML entities
    $data = htmlspecialchars($data);
    
    // Return data yang sudah aman
    return $data;
}

/**
 * FUNGSI: redirect()
 * DESKRIPSI: Redirect ke halaman lain dengan pesan (opsional)
 * PARAMETER:
 *   - $url (string): URL tujuan redirect
 *   - $pesan (string): Pesan yang akan ditampilkan (opsional)
 * RETURN: Tidak ada (exit)
 * CONTOH: redirect("index.php", "Data berhasil disimpan!")
 * ALGORITMA:
 *   1. Jika ada pesan, simpan ke session
 *   2. Redirect ke URL tujuan menggunakan header()
 *   3. Exit untuk menghentikan eksekusi script
 */
function redirect($url, $pesan = '') {
    // Jika ada pesan, simpan ke session
    if ($pesan) {
        $_SESSION['pesan'] = $pesan;
    }
    
    // Redirect ke URL tujuan
    header("Location: $url");
    exit();
}

/**
 * FUNGSI: tampilPesan()
 * DESKRIPSI: Tampilkan pesan dari session (biasanya setelah redirect)
 * PARAMETER: Tidak ada
 * RETURN: HTML alert atau tidak ada jika tidak ada pesan
 * CONTOH: tampilPesan() => <div class="alert alert-info">Data berhasil disimpan!</div>
 * ALGORITMA:
 *   1. Cek apakah ada pesan di session
 *   2. Jika ada, tampilkan dalam alert box
 *   3. Hapus pesan dari session setelah ditampilkan
 */
function tampilPesan() {
    // Cek apakah ada pesan di session
    if (isset($_SESSION['pesan'])) {
        // Tampilkan pesan dalam alert box
        echo '<div class="alert alert-info alert-dismissible fade show" role="alert">';
        echo $_SESSION['pesan'];
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        echo '</div>';
        
        // Hapus pesan dari session
        unset($_SESSION['pesan']);
    }
}

/**
 * FUNGSI: hitungSiswaLunas()
 * DESKRIPSI: Hitung jumlah siswa yang sudah lunas semua pembayaran
 * PARAMETER: Tidak ada
 * RETURN: Integer jumlah siswa yang sudah lunas
 * CONTOH: hitungSiswaLunas() => 5
 * ALGORITMA:
 *   1. Query untuk mencari siswa yang tidak memiliki pembayaran "Belum Lunas"
 *   2. Gunakan NOT IN untuk exclude siswa dengan pembayaran belum lunas
 *   3. Count jumlah siswa
 */
function hitungSiswaLunas() {
    global $koneksi;

    $batas_bulan = date('Y-m-t');
    
    // Query untuk menghitung siswa yang sudah lunas
    $query = "SELECT COUNT(DISTINCT nisn) as total_lunas FROM tb_siswa 
              WHERE nisn NOT IN (
                  SELECT DISTINCT nisn FROM tb_pembayaran WHERE status = 'Belum Lunas' AND batas_pembayaran <= '$batas_bulan'
              )";
    $result = mysqli_query($koneksi, $query);
    $row = mysqli_fetch_assoc($result);
    
    return $row['total_lunas'] ?? 0;
}

/**
 * FUNGSI: hitungSiswaBelumlLunas()
 * DESKRIPSI: Hitung jumlah siswa yang masih memiliki pembayaran belum lunas
 * PARAMETER: Tidak ada
 * RETURN: Integer jumlah siswa yang belum lunas
 * CONTOH: hitungSiswaBelumlLunas() => 3
 * ALGORITMA:
 *   1. Query untuk mencari siswa yang memiliki pembayaran "Belum Lunas"
 *   2. Gunakan DISTINCT untuk menghindari duplikat
 *   3. Count jumlah siswa
 */
function hitungSiswaBelumlLunas() {
    global $koneksi;

    $batas_bulan = date('Y-m-t');
    
    // Query untuk menghitung siswa yang belum lunas
    $query = "SELECT COUNT(DISTINCT nisn) as total_belum_lunas FROM tb_pembayaran 
              WHERE status = 'Belum Lunas' AND batas_pembayaran <= '$batas_bulan'";
    $result = mysqli_query($koneksi, $query);
    $row = mysqli_fetch_assoc($result);
    
    return $row['total_belum_lunas'] ?? 0;
}

/**
 * FUNGSI: cariSiswaByNama()
 * DESKRIPSI: Cari siswa berdasarkan nama
 * PARAMETER:
 *   - $nama (string): Nama siswa yang dicari
 * RETURN: Array hasil pencarian atau empty array jika tidak ditemukan
 * CONTOH: cariSiswaByNama("Budi") => array(array(...), array(...))
 * ALGORITMA:
 *   1. Query SELECT dengan LIKE untuk pencarian partial
 *   2. Gunakan LIKE '%nama%' untuk mencari nama yang mengandung keyword
 *   3. Fetch semua hasil sebagai array
 */
function cariSiswaByNama($nama) {
    global $koneksi;
    
    // Validasi input
    $nama = validasiInput($nama);
    
    // Query untuk mencari siswa berdasarkan nama
    $query = "SELECT * FROM tb_siswa WHERE nama LIKE '%$nama%' ORDER BY nama ASC";
    $result = mysqli_query($koneksi, $query);
    
    // Fetch semua hasil
    $data = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    
    return $data;
}

/**
 * FUNGSI: cariSiswaByNISN()
 * DESKRIPSI: Cari siswa berdasarkan NISN
 * PARAMETER:
 *   - $nisn (string): NISN siswa yang dicari
 * RETURN: Array data siswa atau null jika tidak ditemukan
 * CONTOH: cariSiswaByNISN("0001234567") => array(...)
 * ALGORITMA:
 *   1. Query SELECT dengan WHERE NISN = $nisn
 *   2. Fetch hasil sebagai associative array
 *   3. Return data siswa
 */
function cariSiswaByNISN($nisn) {
    global $koneksi;
    
    // Validasi input
    $nisn = validasiInput($nisn);
    
    // Query untuk mencari siswa berdasarkan NISN
    $query = "SELECT * FROM tb_siswa WHERE nisn = '$nisn'";
    $result = mysqli_query($koneksi, $query);
    
    // Return data siswa
    return mysqli_fetch_assoc($result);
}

/**
 * FUNGSI: getDetailPembayaranSiswa()
 * DESKRIPSI: Ambil detail pembayaran siswa (sudah lunas dan belum lunas)
 * PARAMETER:
 *   - $nisn (string): NISN siswa
 * RETURN: Array dengan keys 'lunas' dan 'belum_lunas'
 * CONTOH: getDetailPembayaranSiswa("0001234567") => array('lunas' => array(...), 'belum_lunas' => array(...))
 * ALGORITMA:
 *   1. Query pembayaran yang sudah lunas
 *   2. Query pembayaran yang belum lunas
 *   3. Return array dengan kedua hasil
 */
function getDetailPembayaranSiswa($nisn) {
    global $koneksi;
    
    // Validasi input
    $nisn = validasiInput($nisn);

    $batas_bulan = date('Y-m-t');
    
    // Query pembayaran yang sudah lunas
    $query_lunas = "SELECT * FROM tb_pembayaran WHERE nisn = '$nisn' AND status = 'Sudah Lunas' ORDER BY tgl_bayar DESC";
    $result_lunas = mysqli_query($koneksi, $query_lunas);
    
    $data_lunas = array();
    while ($row = mysqli_fetch_assoc($result_lunas)) {
        $data_lunas[] = $row;
    }
    
    // Query pembayaran yang belum lunas
    $query_belum = "SELECT * FROM tb_pembayaran WHERE nisn = '$nisn' AND status = 'Belum Lunas' AND batas_pembayaran <= '$batas_bulan' ORDER BY tgl_bayar ASC";
    $result_belum = mysqli_query($koneksi, $query_belum);
    
    $data_belum = array();
    while ($row = mysqli_fetch_assoc($result_belum)) {
        $data_belum[] = $row;
    }
    
    // Return array dengan kedua hasil
    return array(
        'lunas' => $data_lunas,
        'belum_lunas' => $data_belum
    );
}

/**
 * FUNGSI: getSiswaLunas()
 * DESKRIPSI: Ambil list siswa yang sudah lunas semua pembayaran
 * PARAMETER: Tidak ada
 * RETURN: Array list siswa yang sudah lunas
 * CONTOH: getSiswaLunas() => array(array(...), array(...))
 * ALGORITMA:
 *   1. Query siswa yang tidak memiliki pembayaran "Belum Lunas"
 *   2. Join dengan tb_pembayaran untuk verifikasi
 *   3. Fetch semua hasil
 */
function getSiswaLunas() {
    global $koneksi;

    $batas_bulan = date('Y-m-t');
    
    // Query untuk mendapatkan siswa yang sudah lunas
    $query = "SELECT DISTINCT s.* FROM tb_siswa s 
              WHERE s.nisn NOT IN (
                  SELECT DISTINCT nisn FROM tb_pembayaran WHERE status = 'Belum Lunas' AND batas_pembayaran <= '$batas_bulan'
              ) ORDER BY s.nama ASC";
    $result = mysqli_query($koneksi, $query);
    
    $data = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    
    return $data;
}

/**
 * FUNGSI: getSiswaBelumlLunas()
 * DESKRIPSI: Ambil list siswa yang masih memiliki pembayaran belum lunas
 * PARAMETER: Tidak ada
 * RETURN: Array list siswa yang belum lunas
 * CONTOH: getSiswaBelumlLunas() => array(array(...), array(...))
 * ALGORITMA:
 *   1. Query siswa yang memiliki pembayaran "Belum Lunas"
 *   2. Join dengan tb_pembayaran
 *   3. Fetch semua hasil dengan DISTINCT
 */
function getSiswaBelumlLunas() {
    global $koneksi;

    $batas_bulan = date('Y-m-t');
    
    // Query untuk mendapatkan siswa yang belum lunas
    $query = "SELECT DISTINCT s.* FROM tb_siswa s 
              INNER JOIN tb_pembayaran p ON s.nisn = p.nisn 
              WHERE p.status = 'Belum Lunas' AND p.batas_pembayaran <= '$batas_bulan' ORDER BY s.nama ASC";
    $result = mysqli_query($koneksi, $query);
    
    $data = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    
    return $data;
}
?>
