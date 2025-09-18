<?php
header('Content-Type: application/json');
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['exists' => false]);
    exit;
}

$nik = $_POST['nik'] ?? '';
if (!preg_match('/^\d{16}$/', $nik)) {
    echo json_encode(['exists' => false]);
    exit;
}

// 1. Cek dulu di nik_cache
$stmt = $conn->prepare("SELECT nama, alamat FROM nik_cache WHERE nik = ?");
$stmt->bind_param("s", $nik);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode([
        'exists' => true,
        'nama'   => $row['nama'],
        'alamat' => $row['alamat']
    ]);
    exit;
}

// 2. Jika tidak di cache, cek di pelapor
$stmt2 = $conn->prepare("SELECT nama, alamat, nohp FROM pelapor WHERE nik = ?");
$stmt2->bind_param("s", $nik);
$stmt2->execute();
$result2 = $stmt2->get_result();

if ($row2 = $result2->fetch_assoc()) {
    // Simpan ke nik_cache (termasuk nohp jika ada)
    $insert = $conn->prepare("INSERT INTO nik_cache (nik, nama, alamat, nohp) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE nama=VALUES(nama), alamat=VALUES(alamat), nohp=VALUES(nohp)");
    $insert->bind_param("ssss", $nik, $row2['nama'], $row2['alamat'], $row2['nohp']);
    $insert->execute();

    echo json_encode([
        'exists' => true,
        'nama'   => $row2['nama'],
        'alamat' => $row2['alamat']
    ]);
} else {
    // Tidak ditemukan di mana-mana
    echo json_encode(['exists' => false]);
}
?>