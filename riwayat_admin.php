<?php
session_start();

// 🔐 Pastikan hanya admin yang sudah login yang bisa masuk
if (!isset($_SESSION['admin'])) {
    header("Location: login_admin.php");
    exit;
}

include 'db.php'; // Koneksi mysqli ($conn)

// Ambil data
$permohonan_list = [];
$error_message = '';

$sql = "SELECT * FROM permohonan_dtse ORDER BY tanggal_submit DESC";
$result = mysqli_query($conn, $sql);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $permohonan_list[] = $row;
    }
} else {
    $error_message = "Gagal mengambil data: " . mysqli_error($conn);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no" />
    <title>Admin | Sistem Register Pelayanan dan Pengaduan Publik</title>
    <!-- Google Fonts: Poppins -->
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    />
    <!-- Font Awesome -->
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
    />
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
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
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
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
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
            background: #2f6b8f;
        }
        .main-content {
            margin-left: 250px;
            padding: 30px;
            transition: margin-left 0.3s ease;
        }
        .main-content.full-width {
            margin-left: 0;
        }
        .container {
            max-width: 1100px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
        }
        h2 {
            font-size: 24px;
            color: #333;
            margin-bottom: 20px;
            text-align: center;
        }
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
        .table-container {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            margin: 20px 0;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
            background: white;
        }
        th,
        td {
            border: 1px solid #ddd;
            padding: 14px 16px;
            text-align: left;
        }
        th {
            background: #3a7ca5;
            color: white;
            font-weight: 600;
        }
        td .btn {
            padding: 0; /* kita atur ulang untuk tombol icon */
            border-radius: 6px;
            text-decoration: none;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            margin-right: 6px;
            color: white;
        }
        .btn-pdf {
            background: #28a745;
        }
        .btn-pdf:hover {
            background: #218838;
        }
        .btn-detail {
            background: #17a2b8;
        }
        .btn-detail:hover {
            background: #138496;
        }
        .no-data {
            text-align: center;
            color: #666;
            font-style: italic;
        }
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
            th,
            td {
                padding: 12px 14px;
                font-size: 14px;
            }
        }
        @media (max-width: 480px) {
            body {
                font-size: 14px;
            }
            h2 {
                font-size: 18px;
            }
            th,
            td {
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
    <button class="hamburger" id="hamburger"><i class="fas fa-bars"></i></button>
    <div class="sidebar" id="sidebar">
        <div class="sidebar-logo">
            <img src="images/kissme.png" alt="Logo Dinas Sosial" />
        </div>
        <div class="sidebar-title">ADMIN PANEL</div>
        <a href="kelola_jenis_layanan.php"><i class="fas fa-cog"></i> Kelola Jenis Layanan</a>
        <a href="admin_nik_cache.php"><i class="fas fa-database"></i> Viewer Cache NIK</a>
        <a href="riwayat_admin.php" class="active"><i class="fas fa-history"></i> Riwayat Permohonan</a>
        <a href="kelola_akun.php"><i class="fas fa-user-cog"></i> Kelola Akun User</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <div class="main-content" id="main-content">
        <div class="container">
            <h2>Riwayat Permohonan DTSEN</h2>

            <?php if (!empty($error_message)) : ?>
                <div class="alert error"><?= htmlspecialchars($error_message) ?></div>
            <?php endif; ?>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Pemohon</th>
                            <th>NIK Pemohon</th>
                            <th>Anggota</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($permohonan_list)) : ?>
                            <tr>
                                <td colspan="6" class="no-data">Tidak ada data permohonan.</td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($permohonan_list as $i => $p) :
                                $anggota = json_decode($p['data_anggota'], true);
                                $jumlah = is_array($anggota) ? count($anggota) : 0;
                            ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td><?= htmlspecialchars($p['nama_pemohon']) ?></td>
                                    <td><?= htmlspecialchars($p['nik_pemohon']) ?></td>
                                    <td><?= $jumlah ?> orang</td>
                                    <td><?= date('d-m-Y H:i', strtotime($p['tanggal_submit'])) ?></td>
                                    <td>
                                        <a
                                            href="cetak_pdf.php?id=<?= (int)$p['id'] ?>"
                                            class="btn btn-pdf"
                                            target="_blank"
                                            title="Lihat PDF"
                                            ><i class="fas fa-file-pdf"></i
                                        ></a>
                                        <a
                                            href="detail_riwayat_admin.php?id=<?= (int)$p['id'] ?>"
                                            class="btn btn-detail"
                                            title="Detail"
                                            ><i class="fas fa-info-circle"></i
                                        ></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        const hamburger = document.getElementById('hamburger');
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('main-content');
        if (hamburger) {
            hamburger.addEventListener('click', () => {
                sidebar.classList.toggle('active');
                mainContent.classList.toggle('full-width');
            });
        }
        document.addEventListener('click', (e) => {
            if (!sidebar.contains(e.target) && !hamburger.contains(e.target)) {
                if (window.innerWidth <= 768) {
                    sidebar.classList.remove('active');
                    mainContent.classList.remove('full-width');
                }
            }
        });
        window.addEventListener('resize', () => {
            if (window.innerWidth > 768) {
                sidebar.classList.remove('active');
                mainContent.classList.remove('full-width');
            }
        });
    </script>
</body>
</html>
