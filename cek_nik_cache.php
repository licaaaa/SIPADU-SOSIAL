<?php
session_start();
include 'db.php';
header('Content-Type: application/json');

if (isset($_GET['nik']) && preg_match('/^\d{16}$/', $_GET['nik'])) {
    $nik = $_GET['nik'];
    $stmt = $conn->prepare("SELECT nama, alamat, nohp FROM nik_cache WHERE nik = ?");
    $stmt->bind_param("s", $nik);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        echo json_encode(['found' => true, 'nama' => $row['nama'], 'alamat' => $row['alamat'], 'nohp' => $row['nohp']]);
        exit;
    }
}
echo json_encode(['found' => false]);
