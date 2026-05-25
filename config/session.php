<?php
session_start();


function cekLogin() {
    if (!isset($_SESSION['id_petugas'])) {
        header("Location: login.php");
        exit();
    }
}


function cekLevel($level_yang_diizinkan) {
    if (!in_array($_SESSION['level'], $level_yang_diizinkan)) {
        header("Location: index.php");
        exit();
    }
}

function logout() {
    session_destroy();
    header("Location: login.php");
    exit();
}
?>
