<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

include 'db.php';

// --- Ambil data dari form ---
$tabel_nik      = $_POST['tabel_nik'] ?? [];
$tabel_nama     = $_POST['tabel_nama'] ?? [];
$tabel_alamat   = $_POST['tabel_alamat'] ?? [];
$tabel_nohp     = $_POST['tabel_nohp'] ?? []; // jika ada input nohp di form

// --- Simpan setiap baris ke nik_cache ---
foreach ($tabel_nik as $index => $nik_input) {
    $nik_input    = trim($nik_input);
    $nama_input   = trim($tabel_nama[$index] ?? '');
    $alamat_input = trim($tabel_alamat[$index] ?? '');
    $nohp_input   = trim($tabel_nohp[$index] ?? '');

    if (empty($nik_input)) continue; // skip kosong
    if (!preg_match('/^\d{16}$/', $nik_input)) continue; // skip jika NIK invalid
    if (empty($nama_input) || empty($alamat_input)) continue; // skip jika data kurang

    // Insert / Update ke nik_cache
    $stmt = $conn->prepare("
        INSERT INTO nik_cache (nik, nama, alamat, nohp) 
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE 
            nama   = VALUES(nama), 
            alamat = VALUES(alamat),
            nohp   = VALUES(nohp)
    ");
    $stmt->bind_param("ssss", $nik_input, $nama_input, $alamat_input, $nohp_input);
    $stmt->execute();
}

// --- Langsung tampilkan PDF dari cetak_dtsen.php ---
include "cetak_dtsen.php";
exit;
