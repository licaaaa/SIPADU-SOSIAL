<?php
include 'db.php';
header('Content-Type: application/json');

if (isset($_GET['nik'])) {
    $nik = mysqli_real_escape_string($conn, $_GET['nik']);

    // Cek di tabel pelapor terlebih dahulu
    $result = mysqli_query($conn, "SELECT nama, alamat, nohp FROM pelapor WHERE nik='$nik'");
    if ($row = mysqli_fetch_assoc($result)) {
        echo json_encode([
            'found' => true,
            'nama' => $row['nama'],
            'alamat' => $row['alamat'],
            'nohp' => $row['nohp']
        ]);
        exit;
    }

    // Jika tidak ditemukan di pelapor, cek di nik_cache
    $cache = mysqli_query($conn, "SELECT nama, alamat, nohp FROM nik_cache WHERE nik='$nik'");
    if ($row = mysqli_fetch_assoc($cache)) {
        echo json_encode([
            'found' => true,
            'nama' => $row['nama'],
            'alamat' => $row['alamat'],
            'nohp' => $row['nohp']
        ]);
        exit;
    }

    // Jika tidak ditemukan di keduanya
    echo json_encode(['found' => false]);
}
?>
