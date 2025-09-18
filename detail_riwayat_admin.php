<?php
session_start();

// 🔐 Hanya admin yang sudah login
if (!isset($_SESSION['admin'])) {
    header("Location: login_admin.php");
    exit;
}

include 'db.php'; // koneksi mysqli ($conn)

// Pastikan ada ID
if (!isset($_GET['id'])) {
    die("ID tidak ditemukan.");
}

$id = (int) $_GET['id'];

// Ambil data berdasarkan ID
$sql = "SELECT * FROM permohonan_dtse WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$p = mysqli_fetch_assoc($result);

if (!$p) {
    die("Data tidak ditemukan.");
}

// Decode data anggota (JSON)
$data_anggota = json_decode($p['data_anggota'], true);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Detail Permohonan DTSEN</title>
  
  <!-- Font Awesome untuk ikon -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

  <style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f7fa; color: #333; }
    .container { max-width: 900px; margin: auto; background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }

    /* Tombol back seperti form_dtsen.php */
    .back-btn {
      display: inline-block;
      margin-bottom: 20px;
      padding: 10px 16px;
      background: #555;
      color: white;
      text-decoration: none;
      border-radius: 8px;
      font-size: 15px;
      transition: background 0.3s;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .back-btn:hover {
      background: #444;
      transform: translateY(-1px);
    }

    h2 { text-align: center; margin-bottom: 20px; color: #3a7ca5; }
    h3 { margin-top: 20px; color: #555; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
    th { background: #3a7ca5; color: white; font-weight: 600; }
    tr:nth-child(even) { background: #f2f2f2; }
    tr:hover { background: #e9ecef; }

    @media (max-width: 600px) {
      th, td { padding: 8px; font-size: 13px; }
      .back-btn { padding: 6px 12px; font-size: 13px; }
    }
  </style>
</head>
<body>
  <div class="container">

    <a href="riwayat_admin.php" class="back-btn"><i class="fas fa-arrow-left"></i> Kembali</a>

    <h2>Detail Permohonan DTSEN</h2>

    <h3>Data Pemohon</h3>
    <table>
      <tr><th>Nama</th><td><?= htmlspecialchars($p['nama_pemohon']) ?></td></tr>
      <tr><th>NIK</th><td><?= htmlspecialchars($p['nik_pemohon']) ?></td></tr>
      <tr><th>Alamat</th><td><?= nl2br(htmlspecialchars($p['alamat'])) ?></td></tr>
      <tr><th>No HP</th><td><?= htmlspecialchars($p['nohp']) ?></td></tr>
      <tr><th>Tanggal Submit</th><td><?= date('d-m-Y H:i', strtotime($p['tanggal_submit'])) ?></td></tr>
    </table>

    <h3>Data Anggota</h3>
    <table>
      <tr><th>No</th><th>Nama</th><th>NIK</th><th>Alamat</th><th>Keperluan</th></tr>
      <?php if (!empty($data_anggota)) : ?>
        <?php foreach ($data_anggota as $i => $anggota) : ?>
          <tr>
            <td><?= $i + 1 ?></td>
            <td><?= htmlspecialchars($anggota['nama']) ?></td>
            <td><?= htmlspecialchars($anggota['nik']) ?></td>
            <td><?= htmlspecialchars($anggota['alamat'] ?? '-') ?></td>
            <td><?= htmlspecialchars($anggota['keperluan']) ?></td>
          </tr>
        <?php endforeach; ?>
      <?php else : ?>
        <tr><td colspan="5" style="text-align:center;">Tidak ada data anggota.</td></tr>
      <?php endif; ?>
    </table>

  </div>
</body>
</html>
