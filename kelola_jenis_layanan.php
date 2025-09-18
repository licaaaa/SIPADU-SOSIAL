<?php
session_start();

// 🔐 Pastikan hanya admin yang sudah login yang bisa masuk
if (!isset($_SESSION['admin'])) {
    header("Location: login_admin.php");
    exit;
}

include 'db.php'; // Harus menggunakan $conn dari db.php

$message = '';
$success = false;

// --- TAMBAH DATA ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    $nama = trim($_POST['nama'] ?? '');

    if (empty($nama)) {
        $message = "Nama tidak boleh kosong.";
    } else {
        $nama = mysqli_real_escape_string($conn, $nama);

        if ($action == 'tambah_layanan') {
            $check = mysqli_query($conn, "SELECT * FROM jenis_layanan WHERE nama = '$nama'");
            if (mysqli_num_rows($check) > 0) {
                $message = "Jenis layanan sudah ada!";
            } else {
                mysqli_query($conn, "INSERT INTO jenis_layanan (nama) VALUES ('$nama')");
                $message = "Berhasil tambah jenis layanan.";
                $success = true;
            }
        }

        elseif ($action == 'tambah_keterangan') {
            $check = mysqli_query($conn, "SELECT * FROM keterangan_layanan WHERE nama = '$nama'");
            if (mysqli_num_rows($check) > 0) {
                $message = "Keterangan sudah ada!";
            } else {
                mysqli_query($conn, "INSERT INTO keterangan_layanan (nama) VALUES ('$nama')");
                $message = "Berhasil tambah keterangan.";
                $success = true;
            }
        }

        elseif ($action == 'tambah_tindaklanjut') {
            $check = mysqli_query($conn, "SELECT * FROM tindak_lanjut WHERE nama = '$nama'");
            if (mysqli_num_rows($check) > 0) {
                $message = "Tindak lanjut sudah ada!";
            } else {
                mysqli_query($conn, "INSERT INTO tindak_lanjut (nama) VALUES ('$nama')");
                $message = "Berhasil tambah tindak lanjut.";
                $success = true;
            }
        } else {
            $message = "Aksi tidak valid.";
        }
    }
}

// --- HAPUS DATA ---
if (isset($_GET['hapus']) && isset($_GET['id']) && isset($_GET['t'])) {
    $id = (int)$_GET['id'];
    $table = $_GET['t'];

    $tableMap = [
        'layanan' => 'jenis_layanan',
        'keterangan' => 'keterangan_layanan',
        'tindaklanjut' => 'tindak_lanjut'
    ];

    if (array_key_exists($table, $tableMap)) {
        $tableName = $tableMap[$table];
        $stmt = mysqli_prepare($conn, "DELETE FROM `$tableName` WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        if (mysqli_stmt_execute($stmt)) {
            $message = "Data berhasil dihapus.";
            $success = true;
        } else {
            $message = "Gagal menghapus data.";
        }
        mysqli_stmt_close($stmt);
    } else {
        $message = "Tabel tidak dikenali.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no"/>
    <title>Admin | Sistem Register Pelayanan dan Pengaduan Publik</title>
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
            color: #444;
            min-height: 100vh;
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
            box-shadow: 3px 0 15px rgba(0,0,0,0.1);
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
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.08);
        }

        h2 {
            font-size: 24px;
            color: #333;
            margin-bottom: 20px;
            text-align: center;
        }

        /* Alert Message */
        .alert {
            padding: 16px;
            margin: 20px 0;
            border-radius: 8px;
            font-size: 15px;
            text-align: center;
            font-weight: 500;
        }

        .alert.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Form Tambah */
        .form-tambah {
            margin-bottom: 35px;
            padding: 24px;
            border: 1px solid #eee;
            border-radius: 12px;
            background: #f9f9f9;
        }

        .form-tambah h3 {
            margin-bottom: 18px;
            color: #3a7ca5;
            font-size: 18px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-tambah input[type="text"] {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-bottom: 12px;
            font-size: 16px;
            background: #fff;
            transition: border 0.3s;
        }

        .form-tambah input[type="text"]:focus {
            border-color: #3a7ca5;
            outline: none;
            box-shadow: 0 0 0 2px rgba(58, 124, 165, 0.1);
        }

        .form-tambah button {
            background: #3a7ca5;
            color: white;
            border: none;
            padding: 12px 18px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 15px;
            font-weight: 600;
            transition: background 0.3s, transform 0.2s;
        }

        .form-tambah button:hover {
            background: #2f6b8f;
            transform: translateY(-1px);
        }

        /* Table */
        .table-container {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            margin: 20px 0;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 500px;
            background: white;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 14px 16px;
            text-align: left;
        }

        th {
            background: #3a7ca5;
            color: white;
            font-weight: 600;
        }

        td a {
            color: #dc3545;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s;
        }

        td a:hover {
            color: #c82333;
            text-decoration: underline;
        }

        /* Responsive */
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
            .container {
                padding: 20px;
            }
            h2 {
                font-size: 20px;
            }
            .form-tambah {
                padding: 20px;
            }
            .form-tambah input[type="text"] {
                font-size: 15px;
                padding: 10px 12px;
            }
            .form-tambah button {
                font-size: 15px;
                padding: 10px 16px;
            }
            th, td {
                padding: 12px 14px;
                font-size: 14px;
            }
            .table-container {
                margin: 15px 0;
            }
        }

        @media (max-width: 480px) {
            body {
                font-size: 14px;
            }
            h2 {
                font-size: 18px;
            }
            .form-tambah h3 {
                font-size: 17px;
            }
            .form-tambah input[type="text"] {
                font-size: 14px;
                padding: 10px;
            }
            .form-tambah button {
                font-size: 14px;
                padding: 10px;
            }
            th, td {
                padding: 10px 12px;
                font-size: 13px;
            }
            .alert {
                font-size: 14px;
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
        <div class="sidebar-title">ADMIN PANEL</div>
        <a href="kelola_jenis_layanan.php" class="active"><i class="fas fa-cog"></i> Kelola Jenis Layanan</a>
        <a href="admin_nik_cache.php"><i class="fas fa-database"></i> Viewer Cache NIK</a>
        <a href="riwayat_admin.php" class="active"><i class="fas fa-history"></i> Riwayat Permohonan</a>
        <a href="kelola_akun.php"><i class="fas fa-user-cog"></i> Kelola Akun User</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <!-- Main Content -->
    <div class="main-content" id="main-content">
        <div class="container">
            <h2>Kelola Opsi Form Pengaduan</h2>

            <!-- Tampilkan pesan -->
            <?php if (!empty($message)): ?>
                <div class="alert <?= $success ? 'success' : 'error' ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <!-- Jenis Layanan -->
            <div class="form-tambah">
                <h3><i class="fas fa-radiation"></i> Jenis Layanan</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="tambah_layanan">
                    <input type="text" name="nama" placeholder="Contoh: BPJS, DTSEN, dll" required>
                    <button type="submit">Tambah</button>
                </form>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $res = mysqli_query($conn, "SELECT * FROM jenis_layanan ORDER BY nama");
                            while ($r = mysqli_fetch_assoc($res)) {
                                echo "<tr>
                                        <td>" . htmlspecialchars($r['nama']) . "</td>
                                        <td>
                                            <a href='?hapus=1&id=" . (int)$r['id'] . "&t=layanan' 
                                               onclick=\"return confirm('Yakin ingin menghapus?')\">
                                                Hapus
                                            </a>
                                        </td>
                                      </tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Keterangan Layanan -->
            <div class="form-tambah">
                <h3><i class="fas fa-info-circle"></i> Keterangan Layanan</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="tambah_keterangan">
                    <input type="text" name="nama" placeholder="Contoh: Permohonan rekom jaminan rawat inap" required>
                    <button type="submit">Tambah</button>
                </form>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $res = mysqli_query($conn, "SELECT * FROM keterangan_layanan ORDER BY nama");
                            while ($r = mysqli_fetch_assoc($res)) {
                                echo "<tr>
                                        <td>" . htmlspecialchars($r['nama']) . "</td>
                                        <td>
                                            <a href='?hapus=1&id=" . (int)$r['id'] . "&t=keterangan' 
                                               onclick=\"return confirm('Yakin ingin menghapus?')\">
                                                Hapus
                                            </a>
                                        </td>
                                      </tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tindak Lanjut -->
            <div class="form-tambah">
                <h3><i class="fas fa-check-circle"></i> Tindak Lanjut</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="tambah_tindaklanjut">
                    <input type="text" name="nama" placeholder="Contoh: Penerbitan surat keterangan DTSEN" required>
                    <button type="submit">Tambah</button>
                </form>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $res = mysqli_query($conn, "SELECT * FROM tindak_lanjut ORDER BY nama");
                            while ($r = mysqli_fetch_assoc($res)) {
                                echo "<tr>
                                        <td>" . htmlspecialchars($r['nama']) . "</td>
                                        <td>
                                            <a href='?hapus=1&id=" . (int)$r['id'] . "&t=tindaklanjut' 
                                               onclick=\"return confirm('Yakin ingin menghapus?')\">
                                                Hapus
                                            </a>
                                        </td>
                                      </tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Toggle Sidebar
        const hamburger = document.getElementById('hamburger');
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('main-content');

        if (hamburger) {
            hamburger.addEventListener('click', () => {
                sidebar.classList.toggle('active');
                mainContent.classList.toggle('full-width');
            });
        }

        // Close sidebar saat klik di luar
        document.addEventListener('click', (e) => {
            if (!sidebar.contains(e.target) && !hamburger.contains(e.target)) {
                if (window.innerWidth <= 768) {
                    sidebar.classList.remove('active');
                    mainContent.classList.remove('full-width');
                }
            }
        });

        // Reset saat resize ke desktop
        window.addEventListener('resize', () => {
            if (window.innerWidth > 768) {
                sidebar.classList.remove('active');
                mainContent.classList.remove('full-width');
            }
        });
    </script>
</body>
</html>