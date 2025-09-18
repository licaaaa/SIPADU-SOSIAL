<?php
session_start();
if (!isset($_SESSION['login'])) {
  header("Location: login.php");
  exit;
}
include 'db.php';
// Hitung total pelapor
$total_pelapor = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM pelapor"))['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no"/>
  <title>Sistem Register Pelayanan dan Pengaduan Publik</title>
  <!-- Google Fonts: Poppins -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }
    body {
      background: #f5f7fa;
      min-height: 100vh;
      color: #444;
      overflow-x: hidden;
    }
    /* Hamburger Button */
    .hamburger {
      display: none;
      position: fixed;
      top: 15px;
      left: 15px;
      background: #3a7ca5;
      color: white;
      border: none;
      width: 40px;
      height: 40px;
      border-radius: 8px;
      font-size: 18px;
      cursor: pointer;
      z-index: 1100;
      box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }
    /* Sidebar */
    .sidebar {
      width: 250px;
      background: #3a7ca5;
      color: white;
      padding: 20px 0;
      display: flex;
      flex-direction: column;
      box-shadow: 3px 0 15px rgba(0, 0, 0, 0.1);
      position: fixed;
      height: 100%;
      z-index: 1000;
      transition: transform 0.3s ease;
      top: 0;
      left: 0;
    }
    .sidebar.hidden {
      transform: translateX(-100%);
    }
    .sidebar-logo {
      text-align: center;
      margin-bottom: 20px;
    }
    .sidebar-logo img {
      width: 70px;
      height: 70px;
      object-fit: contain;
      border-radius: 10px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .sidebar-title {
      text-align: center;
      font-size: 16px;
      font-weight: 600;
      margin-bottom: 30px;
      padding: 0 20px;
      color: #e0f0ff;
    }
    .sidebar a {
      padding: 14px 20px;
      text-decoration: none;
      color: white;
      font-size: 15px;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      gap: 12px;
    }
    .sidebar a:hover {
      background: #2f6b8f;
      border-left: 3px solid white;
      padding-left: 17px;
    }
    .sidebar a.active {
      background: #2d6385;
      font-weight: 600;
    }
    /* Main Content */
    .main-content {
      margin-left: 250px;
      padding: 30px;
      transition: margin-left 0.3s ease;
    }
    .main-content.full-width {
      margin-left: 0;
    }
    .container {
      max-width: 1000px;
      margin: 0 auto;
    }
    /* Kop Instansi */
    .header {
      text-align: center;
      padding: 25px 0;
      margin-bottom: 25px;
      background: white;
      border-radius: 20px;
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
    }
    .header .logo-container {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 15px;
      margin-bottom: 10px;
    }
    .header .logo {
      width: 80px;
      height: 80px;
      object-fit: contain;
      border-radius: 12px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }
    .header .instansi {
      text-align: left;
    }
    .header .kota {
      font-size: 18px;
      font-weight: 700;
      color: #3a7ca5;
    }
    .header .dinas {
      font-size: 15px;
      color: #555;
    }
    .header .app-title {
      font-size: 20px;
      font-weight: 600;
      color: #333;
      margin-top: 10px;
    }
    h2 {
      font-size: 24px;
      font-weight: 600;
      color: #333;
      margin: 30px 0 10px;
    }
    .subtitle {
      font-size: 15px;
      color: #666;
      margin-bottom: 25px;
    }
    /* Top Bar */
    .top-bar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 25px;
      flex-wrap: wrap;
      gap: 15px;
    }
    /* Search Box */
    .search-box {
      display: flex;
      gap: 8px;
      flex: 1;
      max-width: 400px;
    }
    .search-box input[type="text"] {
      flex: 1;
      padding: 12px 14px;
      border: 1px solid #ddd;
      border-radius: 8px;
      outline: none;
      font-size: 15px;
      background: #fafafa;
      transition: border 0.3s;
    }
    .search-box input[type="text"]:focus {
      border-color: #3a7ca5;
      box-shadow: 0 0 0 2px rgba(58, 124, 165, 0.1);
    }
    .search-box button {
      padding: 12px 16px;
      background: #3a7ca5;
      color: white;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-size: 15px;
      transition: background 0.3s;
      display: flex;
      align-items: center;
      gap: 6px;
    }
    .search-box button:hover {
      background: #2f6b8f;
    }
    /* Export Button */
    .export-btn {
      padding: 12px 16px;
      background: #28a745;
      color: white;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-size: 15px;
      display: flex;
      align-items: center;
      gap: 6px;
      transition: background 0.3s;
    }
    .export-btn:hover {
      background: #218838;
    }
    /* Export Form */
    #exportForm {
      display: none;
      background: white;
      padding: 18px;
      border-radius: 12px;
      box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
      margin-bottom: 20px;
      flex-direction: column;
      gap: 12px;
      align-items: flex-start;
      animation: fadeIn 0.3s ease;
    }
    #exportForm label {
      font-size: 15px;
      color: #555;
      display: flex;
      align-items: center;
      gap: 6px;
    }
    #exportForm input[type="date"] {
      padding: 10px 12px;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 15px;
      background: #fdfdfd;
      min-width: 200px;
    }
    #exportForm input[type="date"]:focus {
      border-color: #3a7ca5;
      outline: none;
    }
    #exportForm button {
      padding: 10px 16px;
      background: #3a7ca5;
      color: white;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-size: 15px;
      transition: background 0.3s;
    }
    #exportForm button:hover {
      background: #2f6b8f;
    }
    /* Table */
    .table-container {
      overflow-x: auto;
      -webkit-overflow-scrolling: touch;
      margin: 20px 0;
      border-radius: 16px;
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
    }
    table {
      width: 100%;
      border-collapse: collapse;
      background: white;
      min-width: 600px;
    }
    th, td {
      padding: 16px 18px;
      text-align: left;
    }
    th {
      background: #3a7ca5;
      color: white;
      font-weight: 600;
    }
    tr:nth-child(even) {
      background-color: #f8f9fa;
    }
    tr:hover {
      background-color: #e3f2fd;
    }
    /* Buttons */
    .btn-detail {
      padding: 8px 10px;
      background: #3a7ca5;
      color: white;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-size: 14px;
      transition: background 0.3s;
    }
    .btn-detail:hover {
      background: #2f6b8f;
    }
    .btn-edit {
      padding: 8px 10px;
      background: #ffc107;
      color: #000;
      text-decoration: none;
      border-radius: 6px;
      font-size: 14px;
      margin: 0 4px;
      display: inline-block;
    }
    .btn-edit:hover {
      background: #e0a800;
    }
    .btn-hapus {
      padding: 8px 10px;
      background: #dc3545;
      color: white;
      text-decoration: none;
      border-radius: 6px;
      font-size: 14px;
      margin: 0 4px;
    }
    .btn-hapus:hover {
      background: #c82333;
    }
    .btn-pdf {
      padding: 8px 12px;
      background: #d9534f;
      color: white;
      text-decoration: none;
      border-radius: 6px;
      font-size: 14px;
      margin: 0 5px;
      display: inline-block;
    }
    .btn-pdf:hover {
      background: #c9302c;
    }
    /* Detail Box */
    .detail-box {
      background: #f8f9fa;
      padding: 16px;
      border-radius: 12px;
      border: 1px solid #e0e0e0;
      font-size: 15px;
      line-height: 1.6;
    }
    .detail-box h4 {
      color: #333;
      font-size: 16px;
      font-weight: 600;
      margin: 10px 0 8px;
    }
    .detail-box p {
      margin: 6px 0;
      color: #555;
    }
    /* Sub-table */
    .sub-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 12px;
      font-size: 14px;
    }
    .sub-table th {
      background: #28a745;
      color: white;
      padding: 10px;
    }
    .sub-table td, .sub-table th {
      border: 1px solid #dee2e6;
      padding: 8px;
    }
    /* Warning Message */
    .warning {
      text-align: center;
      padding: 18px;
      background: #f9eaea;
      color: #c44545;
      border: 1px solid #eed1d1;
      border-radius: 10px;
      margin: 20px 0;
      font-size: 15px;
    }
    /* Success Message */
    .alert {
      padding: 16px;
      background: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
      border-radius: 10px;
      margin-bottom: 25px;
      font-size: 15px;
    }
    /* Statistik Box */
    .stat-box {
      background: #e3f2fd;
      padding: 18px;
      border-radius: 12px;
      margin-bottom: 25px;
      text-align: center;
      font-size: 16px;
      color: #3a7ca5;
      font-weight: 500;
      box-shadow: 0 4px 10px rgba(58, 124, 165, 0.1);
    }
    /* Animations */
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-10px); }
      to { opacity: 1; transform: translateY(0); }
    }
    /* RESPONSIF */
    @media (max-width: 768px) {
      .hamburger {
        display: block;
      }
      .sidebar {
        transform: translateX(-100%);
      }
      .sidebar.active {
        transform: translateX(0);
      }
      .main-content {
        margin-left: 0;
        padding: 20px;
      }
      .header .logo-container {
        flex-direction: row;
        gap: 10px;
      }
      .header .kota {
        font-size: 16px;
      }
      .header .dinas {
        font-size: 13px;
      }
      .header .app-title {
        font-size: 17px;
      }
      h2 {
        font-size: 20px;
      }
      .subtitle {
        font-size: 14px;
      }
      .top-bar {
        flex-direction: column;
        align-items: stretch;
      }
      .search-box, .export-btn {
        width: 100%;
      }
      .search-box button, .export-btn {
        justify-content: center;
      }
      #exportForm {
        align-items: stretch;
      }
      #exportForm input[type="date"] {
        min-width: auto;
        width: 100%;
      }
      .table-container {
        margin: 15px 0;
      }
      table {
        font-size: 14px;
      }
      th, td {
        padding: 12px 14px;
      }
      .btn-detail, .btn-edit, .btn-hapus, .btn-pdf {
        padding: 6px 8px;
        font-size: 13px;
        margin: 2px 2px;
      }
      .btn-pdf {
        margin: 4px 0;
        display: block;
        text-align: center;
        width: 100%;
        max-width: 120px;
      }
      .sub-table th, .sub-table td {
        font-size: 13px;
        padding: 6px;
      }
    }
    @media (max-width: 480px) {
      body {
        font-size: 14px;
      }
      .header .logo {
        width: 60px;
        height: 60px;
      }
      .header .kota {
        font-size: 15px;
      }
      .header .app-title {
        font-size: 15px;
      }
      .search-box input[type="text"],
      .search-box button {
        font-size: 14px;
        padding: 10px;
      }
      .export-btn {
        font-size: 14px;
      }
      .stat-box {
        font-size: 15px;
        padding: 16px 12px;
      }
    }
  </style>
</head>
<body>

  <!-- Hamburger Button -->
  <button class="hamburger" id="hamburger">
    <i class="fas fa-bars"></i>
  </button>

  <!-- Sidebar -->
  <div class="sidebar" id="sidebar">
    <div class="sidebar-logo">
      <img src="images/kissme.png" alt="Logo Dinas Sosial">
    </div>
    <div class="sidebar-title">MENU UTAMA</div>
    <a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    <a href="pelapor.php" class="active"><i class="fas fa-users"></i> Data Pengunjung</a>
    <a href="pengaduan.php"><i class="fas fa-file-alt"></i> Form Pengaduan</a>
    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
  </div>

  <!-- Main Content -->
  <div class="main-content" id="main-content">
    <!-- Kop Instansi -->
    <div class="header">
      <div class="logo-container">
        <img src="images/kissme.png" alt="Logo Dinas Sosial Kota Tanjungpinang" class="logo">
        <div class="instansi">
          <div class="kota">DINAS SOSIAL</div>
          <div class="dinas">Kota Tanjungpinang</div>
        </div>
      </div>
      <div class="app-title">Sentra Pelayanan dan Pengaduan Publik</div>
    </div>

    <!-- Tampilkan pesan sukses -->
    <?php if (isset($_SESSION['msg'])): ?>
      <div class="alert">
        <?= htmlspecialchars($_SESSION['msg']); ?>
      </div>
      <?php unset($_SESSION['msg']); ?>
    <?php endif; ?>

    <h2>Data Pengunjung</h2>
    <p class="subtitle">Kelola dan lihat riwayat pengaduan masyarakat berdasarkan NIK atau Nama.</p>

    <!-- Statistik -->
    <div class="stat-box">
      <strong>Total Pengunjung:</strong> <?= $total_pelapor ?>
    </div>

    <!-- Top Bar -->
    <div class="top-bar">
      <!-- Search -->
      <form method="GET" class="search-box">
        <input 
          type="text" 
          name="cari" 
          placeholder="Cari berdasarkan NIK atau Nama..." 
          value="<?= isset($_GET['cari']) ? htmlspecialchars($_GET['cari']) : '' ?>"
          maxlength="30"
          autocomplete="off"
        >
        <button type="submit"><i class="fas fa-search"></i> Cari</button>
      </form>
      <!-- Export Button -->
      <button type="button" class="export-btn" onclick="toggleExportForm()">
        <i class="fas fa-file-export"></i> Export Data
      </button>
    </div>

    <!-- Export Form -->
    <form id="exportForm" method="POST" action="export_pelapor.php">
      <label><i class="far fa-calendar"></i> Dari:</label>
      <input type="date" name="tanggal_awal" required>
      <label><i class="far fa-calendar"></i> Sampai:</label>
      <input type="date" name="tanggal_akhir" required>
      <button type="submit" name="export"><i class="fas fa-download"></i> Export</button>
    </form>

    <?php
    $cari = isset($_GET['cari']) ? mysqli_real_escape_string($conn, $_GET['cari']) : '';
    if ($cari) {
        $query = "SELECT * FROM pelapor 
                  WHERE nik LIKE '%$cari%' 
                     OR nama LIKE '%$cari%' 
                  ORDER BY id DESC";
    } else {
        $query = "SELECT * FROM pelapor ORDER BY id DESC";
    }
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        echo '<div class="table-container">';
        echo "<table>";
        echo "<thead><tr><th>NIK</th><th>Nama</th><th>Aksi</th></tr></thead>";
        echo "<tbody>";
        while ($data = mysqli_fetch_assoc($result)) {
            $nik = htmlspecialchars($data['nik']);
            $nama = htmlspecialchars($data['nama']);
            $alamat = htmlspecialchars($data['alamat']);
            $nohp = htmlspecialchars($data['nohp']);
            echo "<tr>";
            echo "<td><strong>$nik</strong></td>";
            echo "<td>$nama</td>";
            echo "<td>
                    <button class='btn-detail' title='Lihat Detail' onclick=\"toggleDetail('detail-$nik')\">
                      <i class='fas fa-eye'></i>
                    </button>
                    <a href='edit_pelapor.php?nik=" . urlencode($nik) . "' class='btn-edit' title='Edit'>
                      <i class='fas fa-edit'></i>
                    </a>
                    <a href='hapus_pelapor.php?nik=" . urlencode($nik) . "' class='btn-hapus' title='Hapus' onclick=\"return confirm('Yakin ingin menghapus data pelapor ini?')\">
                      <i class='fas fa-trash-alt'></i>
                    </a>
                    <a href='form_dtsen.php?nik=" . urlencode($nik) . "' class='btn-pdf'>Form DTSEN</a>
                  </td>";
            echo "</tr>";
            // Detail Row (Riwayat Pengaduan)
            echo "<tr id='detail-$nik' style='display:none;'><td colspan='3'>";
            echo "<div class='detail-box'>";
            echo "<p><strong>Alamat:</strong> $alamat</p>";
            echo "<p><strong>No HP:</strong> $nohp</p>";
            // Riwayat Pengaduan
            $pengaduan = mysqli_query($conn, "SELECT * FROM pengaduan WHERE nik='$nik' ORDER BY tanggal DESC");
            if (mysqli_num_rows($pengaduan) > 0) {
                echo "<h4>Riwayat Pengaduan</h4>";
                echo "<table class='sub-table'>";
                echo "<thead><tr><th>Layanan</th><th>Keterangan</th><th>Tindak Lanjut</th><th>Tanggal</th></tr></thead>";
                echo "<tbody>";
                while ($p = mysqli_fetch_assoc($pengaduan)) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($p['layanan']) . "</td>";
                    echo "<td>" . htmlspecialchars($p['keterangan']) . "</td>";
                    echo "<td>" . htmlspecialchars($p['tindaklanjut']) . "</td>";
                    echo "<td>" . date('d-m-Y', strtotime($p['tanggal'])) . "</td>";
                    echo "</tr>";
                }
                echo "</tbody></table>";
            } else {
                echo "<p><em>Tidak ada riwayat pengaduan.</em></p>";
            }
            echo "</div></td></tr>";
        }
        echo "</tbody></table>";
        echo '</div>'; // .table-container
    } else {
        echo "<p class='warning'>Tidak ada data pelapor ditemukan.</p>";
    }
    ?>
  </div>

  <script>
    // Toggle Sidebar
    const hamburger = document.getElementById('hamburger');
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('main-content');

    hamburger.addEventListener('click', () => {
      sidebar.classList.toggle('active');
      mainContent.classList.toggle('full-width');
    });

    // Toggle Detail
    function toggleDetail(id) {
      const row = document.getElementById(id);
      if (row.style.display === "table-row") {
        row.style.display = "none";
      } else {
        row.style.display = "table-row";
      }
    }

    // Toggle Export Form
    function toggleExportForm() {
      const form = document.getElementById('exportForm');
      if (form.style.display === "flex" || form.style.display === "block") {
        form.style.display = "none";
      } else {
        form.style.display = window.innerWidth <= 768 ? "block" : "flex";
      }
    }

    // Close sidebar saat klik di luar (opsional)
    document.addEventListener('click', (e) => {
      if (!sidebar.contains(e.target) && !hamburger.contains(e.target)) {
        if (window.innerWidth <= 768) {
          sidebar.classList.remove('active');
          mainContent.classList.remove('full-width');
        }
      }
    });

    // Atur ulang tampilan saat resize
    window.addEventListener('resize', () => {
      const form = document.getElementById('exportForm');
      if (window.innerWidth > 768) {
        form.style.display = "";
        sidebar.classList.remove('active');
        mainContent.classList.remove('full-width');
      }
    });
  </script>
</body>
</html>