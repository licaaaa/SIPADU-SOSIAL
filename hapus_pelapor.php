<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}
include 'db.php';

$nik = isset($_GET['nik']) ? mysqli_real_escape_string($conn, $_GET['nik']) : '';

if (!$nik) {
    $_SESSION['msg'] = "NIK tidak valid.";
} else {
    // Hapus riwayat pengaduan terlebih dahulu (opsional)
    mysqli_query($conn, "DELETE FROM pengaduan WHERE nik='$nik'");
    // Hapus pelapor
    $hapus = mysqli_query($conn, "DELETE FROM pelapor WHERE nik='$nik'");
    if ($hapus) {
        $_SESSION['msg'] = "Data pelapor berhasil dihapus.";
    } else {
        $_SESSION['msg'] = "Gagal menghapus data.";
    }
}

header("Location: pelapor.php");
exit;