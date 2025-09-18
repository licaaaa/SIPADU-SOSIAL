<?php
include 'db.php';

if (isset($_POST['nik'])) {
  $nik = $_POST['nik'];
  $result = mysqli_query($conn, "SELECT * FROM pelapor WHERE nik = '$nik'");
  if (mysqli_num_rows($result) > 0) {
    $data = mysqli_fetch_assoc($result);
    echo json_encode([
      'found' => true,
      'nama' => $data['nama'],
      'alamat' => $data['alamat'],
      'nohp' => $data['nohp']
    ]);
  } else {
    echo json_encode(['found' => false]);
  }
}
?>
