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
    header("Location: pelapor.php");
    exit;
}

// Ambil data pelapor
$query = "SELECT * FROM pelapor WHERE nik = '$nik'";
$result = mysqli_query($conn, $query);
$pelapor = mysqli_fetch_assoc($result);

if (!$pelapor) {
    $_SESSION['msg'] = "Data pelapor tidak ditemukan.";
    header("Location: pelapor.php");
    exit;
}

// Ambil semua pengaduan dari pelapor ini
$pengaduan_result = mysqli_query($conn, "SELECT * FROM pengaduan WHERE nik='$nik' ORDER BY tanggal DESC");

// Ambil daftar keterangan layanan
$ket_query = mysqli_query($conn, "SELECT nama FROM keterangan_layanan ORDER BY nama");
$keterangan_options = [];
while ($row = mysqli_fetch_assoc($ket_query)) {
    $keterangan_options[] = htmlspecialchars($row['nama']);
}

// Ambil daftar tindak lanjut
$tindak_query = mysqli_query($conn, "SELECT nama FROM tindak_lanjut ORDER BY nama");
$tindaklanjut_options = [];
while ($row = mysqli_fetch_assoc($tindak_query)) {
    $tindaklanjut_options[] = htmlspecialchars($row['nama']);
}

//AMBIL DAFTAR JENIS LAYANAN DARI DATABASE (SESUAI PENGADUAN.PHP)
$layanan_query = mysqli_query($conn, "SELECT nama FROM jenis_layanan ORDER BY nama");
$layanan_options = [];
while ($row = mysqli_fetch_assoc($layanan_query)) {
    $layanan_options[] = htmlspecialchars($row['nama']);
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    $nohp = mysqli_real_escape_string($conn, $_POST['nohp']);

    // Validasi data pelapor
    if (empty($nama) || empty($alamat) || empty($nohp)) {
        $error = "Semua data pelapor wajib diisi.";
    } else {
        // Update data pelapor
        $update_pelapor = mysqli_query($conn, "UPDATE pelapor SET nama='$nama', alamat='$alamat', nohp='$nohp' WHERE nik='$nik'");
        if (!$update_pelapor) {
            $error = "Gagal memperbarui data pelapor: " . mysqli_error($conn);
        } else {
            $success .= "Data pelapor berhasil diperbarui. ";
        }
    }

    // Proses update pengaduan (jika ada)
    if (isset($_POST['pengaduan'])) {
        foreach ($_POST['pengaduan'] as $id => $data) {
            $id = (int)$id;

            //Handle jenis layanan dari radio button
            $layanan = isset($data['layanan']) ? mysqli_real_escape_string($conn, $data['layanan']) : '';
            if (empty($layanan)) {
                $error .= "Jenis layanan pada pengaduan ID $id belum dipilih. ";
                continue;
            }

            // Handle keterangan
            $keterangan = mysqli_real_escape_string($conn, $data['keterangan']);
            if ($keterangan === 'LAINNYA' && !empty($data['keterangan_lainnya'])) {
                $keterangan = mysqli_real_escape_string($conn, $data['keterangan_lainnya']);
            }

            // Handle tindak lanjut
            $tindaklanjut = mysqli_real_escape_string($conn, $data['tindaklanjut']);
            if ($tindaklanjut === 'LAINNYA' && !empty($data['tindaklanjut_lainnya'])) {
                $tindaklanjut = mysqli_real_escape_string($conn, $data['tindaklanjut_lainnya']);
            }

            if (!empty($layanan) && !empty($keterangan) && !empty($tindaklanjut)) {
                $update_pengaduan = mysqli_query($conn, "UPDATE pengaduan SET 
                    layanan='$layanan', 
                    keterangan='$keterangan', 
                    tindaklanjut='$tindaklanjut' 
                    WHERE id='$id' AND nik='$nik'
                ");
                if (!$update_pengaduan) {
                    $error .= "Gagal update pengaduan ID $id. ";
                } else {
                    $success .= "Pengaduan diubah. ";
                }
            }
        }
    }

    // Simpan pesan
    if ($success) {
        $_SESSION['msg'] = trim($success);
    }
    if ($error) {
        $_SESSION['msg'] = trim($error);
    }

    // Redirect untuk hindari resubmit
    header("Location: pelapor.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no"/>
    <title>Sistem Register Pelayanan dan Pengaduan Publik</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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

        /* 👇 TOMBOL HAMBURGER */
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
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* SIDEBAR */
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
            transform: translateX(-100%);
            transition: transform 0.3s ease;
            top: 0;
            left: 0;
        }
        .sidebar.active {
            transform: translateX(0);
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

        /* MAIN CONTENT */
        .main-content {
            margin-left: 0;
            padding: 30px;
            transition: margin-left 0.3s ease;
        }
        .main-content.sidebar-open {
            margin-left: 250px;
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

        /* Form Card */
        .form-card {
            background: white;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
        }

        /* Form Section */
        .form-section h3 {
            font-size: 18px;
            color: #3a7ca5;
            margin-bottom: 15px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .form-section h3 i {
            background: #3a7ca5;
            color: white;
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-size: 14px;
        }

        /* Input Group */
        .input-group {
            position: relative;
            margin-bottom: 20px;
        }
        .input-group input,
        .input-group textarea,
        .input-group select {
            width: 100%;
            padding: 12px 0 10px 0;
            border: none;
            border-bottom: 2px solid #e0e0e0;
            font-size: 16px;
            outline: none;
            background: transparent;
            color: #333;
            transition: border-color 0.3s ease;
            text-transform: uppercase;
        }
        .input-group select {
            appearance: none;
            background: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23777' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6,9 12,15 18,9'%3e%3c/polyline%3e%3c/svg%3e") no-repeat right 8px center;
            background-size: 14px;
        }
        .input-group input:focus,
        .input-group textarea:focus,
        .input-group select:focus {
            border-color: #3a7ca5;
        }
        .input-group label {
            position: absolute;
            left: 0;
            top: 12px;
            font-size: 16px;
            color: #777;
            pointer-events: none;
            transition: all 0.3s ease;
        }
        .input-group input:focus ~ label,
        .input-group input:not(:placeholder-shown) ~ label,
        .input-group select:focus ~ label,
        .input-group select:not(:invalid) ~ label {
            top: -10px;
            font-size: 12px;
            color: #3a7ca5;
            font-weight: 500;
        }

        /* ✅ RADIO GROUP UNTUK JENIS LAYANAN */
        .radio-group {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }
        .radio-group label {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 15px;
            cursor: pointer;
            color: #555;
            flex: 1;
            min-width: 120px;
            justify-content: center;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background: #f8f9fa;
            transition: all 0.3s ease;
        }
        .radio-group label:hover {
            background: #e9f5ff;
            border-color: #3a7ca5;
        }
        .radio-group input[type="radio"] {
            accent-color: #3a7ca5;
            transform: scale(1.2);
            margin-right: 6px;
        }
        .radio-group input[type="radio"]:checked + span {
            font-weight: 600;
            color: #3a7ca5;
        }

        /* Pengaduan List */
        .pengaduan-list {
            margin-top: 20px;
            border-top: 1px dashed #ccc;
            padding-top: 20px;
        }
        .pengaduan-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 15px;
            border: 1px solid #e0e0e0;
        }
        .pengaduan-item h4 {
            margin-bottom: 10px;
            color: #333;
            font-size: 16px;
        }

        /* Hidden fields for "LAINNYA" */
        .input-group[id$="_lainnya_box"] {
            margin-top: 10px;
            display: none;
        }

        /* Submit Button */
        button[type="submit"] {
            width: 100%;
            padding: 14px;
            background: #3a7ca5;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(58, 124, 165, 0.2);
        }
        button[type="submit"]:hover {
            background: #2f6b8f;
            transform: translateY(-2px);
        }

        /* Error Message */
        .error {
            background: #f9eaea;
            color: #c44545;
            padding: 12px;
            border: 1px solid #eed1d1;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        /* 👇 RESPONSIVE MOBILE */
        @media (max-width: 768px) {
            .hamburger { display: block; }
            .sidebar { transform: translateX(-100%); }
            .sidebar.active { transform: translateX(0); }
            .main-content { margin-left: 0; padding: 20px; }
            .header .logo-container { flex-direction: row; gap: 10px; }
            .header .kota { font-size: 16px; }
            .header .app-title { font-size: 18px; }
            .input-group { margin-bottom: 18px; }
            .radio-group { flex-direction: column; gap: 8px; }
            .radio-group label {
                min-width: auto;
                justify-content: flex-start;
                padding: 8px 12px;
                margin: 4px 0;
            }
            button[type="submit"] { padding: 12px; font-size: 15px; }
        }

        @media (max-width: 480px) {
            body { font-size: 14px; }
            .header .logo { width: 60px; height: 60px; }
            .header .kota { font-size: 15px; }
            .header .app-title { font-size: 15px; }
            .sidebar a { font-size: 14px; padding: 12px 18px; }
            .sidebar-title { font-size: 15px; }
            .form-card { padding: 20px; }
            .input-group input, .input-group select { font-size: 15px; }
            .input-group label { font-size: 15px; }
            .radio-group label { font-size: 14px; padding: 7px 10px; }
        }
    </style>
</head>
<body>
    <!-- 👇 TOMBOL HAMBURGER -->
    <button class="hamburger" id="hamburger"><i class="fas fa-bars"></i></button>

    <!-- SIDEBAR -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-logo">
            <img src="images/dinsos.png" alt="Logo Dinas Sosial">
        </div>
        <div class="sidebar-title">MENU UTAMA</div>
        <a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="pelapor.php"><i class="fas fa-users"></i> Data Pelapor</a>
        <a href="pengaduan.php"><i class="fas fa-file-alt"></i> Form Pengaduan</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content" id="main-content">
        <!-- Kop Instansi -->
        <div class="header">
            <div class="logo-container">
                <img src="images/dinsos.png" alt="Logo Dinas Sosial Kota Tanjungpinang" class="logo">
                <div class="instansi">
                    <div class="kota">DINAS SOSIAL</div>
                    <div class="dinas">Kota Tanjungpinang</div>
                </div>
            </div>
            <div class="app-title">Sentra Pelayanan dan Pengaduan Publik</div>
        </div>

        <h2>Edit Data Pelapor & Pengaduan</h2>
        <p class="subtitle">Perbarui informasi pelapor dan riwayat pengaduannya.</p>

        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="form-card">
            <form method="POST" id="form-edit">
                <!-- Data Pelapor -->
                <div class="form-section">
                    <h3><i class="fas fa-user"></i> Data Pelapor</h3>
                    <p><strong>NIK:</strong> <?= htmlspecialchars($pelapor['nik']) ?></p>
                    <div class="input-group">
                        <input type="text" name="nama" id="nama" placeholder=" " value="<?= htmlspecialchars($pelapor['nama']) ?>" required>
                        <label for="nama">Nama Lengkap</label>
                    </div>
                    <div class="input-group">
                        <input type="text" name="alamat" id="alamat" placeholder=" " value="<?= htmlspecialchars($pelapor['alamat']) ?>" required>
                        <label for="alamat">Alamat</label>
                    </div>
                    <div class="input-group">
                        <input type="tel" name="nohp" id="nohp" placeholder=" " value="<?= htmlspecialchars($pelapor['nohp']) ?>" required>
                        <label for="nohp">No HP</label>
                    </div>
                </div>

                <!-- Riwayat Pengaduan -->
                <?php if (mysqli_num_rows($pengaduan_result) > 0): ?>
                    <div class="form-section pengaduan-list">
                        <h3><i class="fas fa-clipboard-list"></i> Riwayat Pengaduan</h3>
                        <?php while ($p = mysqli_fetch_assoc($pengaduan_result)): ?>
                            <div class="pengaduan-item">
                                <h4>Pengaduan #<?= $p['id'] ?> (<?= date('d-m-Y', strtotime($p['tanggal'])) ?>)</h4>
                                <input type="hidden" name="pengaduan[<?= $p['id'] ?>][id]" value="<?= $p['id'] ?>">

                                <!-- ✅ JENIS LAYANAN — RADIO BUTTON DINAMIS (SESUAI PENGADUAN.PHP) -->
                                <div class="form-section">
                                    <h3 style="margin-bottom: 12px;"><i class="fas fa-clipboard-list"></i> Jenis Layanan</h3>
                                    <div class="radio-group">
                                        <?php foreach ($layanan_options as $opt): ?>
                                            <label>
                                                <input type="radio" name="pengaduan[<?= $p['id'] ?>][layanan]" value="<?= $opt ?>" required <?= $p['layanan'] === $opt ? 'checked' : '' ?>>
                                                <span><?= $opt ?></span>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <!-- Keterangan -->
                                <div class="input-group">
                                    <select name="pengaduan[<?= $p['id'] ?>][keterangan]" id="keterangan_<?= $p['id'] ?>" required>
                                        <option value="" disabled selected></option>
                                        <?php foreach ($keterangan_options as $opt): ?>
                                            <option value="<?= $opt ?>" <?= $p['keterangan'] === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                                        <?php endforeach; ?>
                                        <option value="LAINNYA" <?= $p['keterangan'] === 'LAINNYA' ? 'selected' : '' ?>>LAINNYA</option>
                                    </select>
                                    <label for="keterangan_<?= $p['id'] ?>">Keterangan Tambahan</label>
                                </div>
                                <div class="input-group" id="keterangan_lainnya_<?= $p['id'] ?>_box" style="display: <?= $p['keterangan'] === 'LAINNYA' ? 'block' : 'none' ?>;">
                                    <input type="text" name="pengaduan[<?= $p['id'] ?>][keterangan_lainnya]" id="keterangan_lainnya_<?= $p['id'] ?>" placeholder=" " value="<?= htmlspecialchars($p['keterangan'] === 'LAINNYA' ? $p['keterangan'] : '') ?>">
                                    <label for="keterangan_lainnya_<?= $p['id'] ?>">Isi Keterangan Lainnya</label>
                                </div>

                                <!-- Tindak Lanjut -->
                                <div class="input-group">
                                    <select name="pengaduan[<?= $p['id'] ?>][tindaklanjut]" id="tindaklanjut_<?= $p['id'] ?>" required>
                                        <option value="" disabled selected></option>
                                        <?php foreach ($tindaklanjut_options as $opt): ?>
                                            <option value="<?= $opt ?>" <?= $p['tindaklanjut'] === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                                        <?php endforeach; ?>
                                        <option value="LAINNYA" <?= $p['tindaklanjut'] === 'LAINNYA' ? 'selected' : '' ?>>LAINNYA</option>
                                    </select>
                                    <label for="tindaklanjut_<?= $p['id'] ?>">Tindak Lanjut yang Diharapkan</label>
                                </div>
                                <div class="input-group" id="tindaklanjut_lainnya_<?= $p['id'] ?>_box" style="display: <?= $p['tindaklanjut'] === 'LAINNYA' ? 'block' : 'none' ?>;">
                                    <input type="text" name="pengaduan[<?= $p['id'] ?>][tindaklanjut_lainnya]" id="tindaklanjut_lainnya_<?= $p['id'] ?>" placeholder=" " value="<?= htmlspecialchars($p['tindaklanjut'] === 'LAINNYA' ? $p['tindaklanjut'] : '') ?>">
                                    <label for="tindaklanjut_lainnya_<?= $p['id'] ?>">Isi Tindak Lanjut Lainnya</label>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p><em>Tidak ada riwayat pengaduan untuk pelapor ini.</em></p>
                <?php endif; ?>

                <button type="submit">Simpan Semua Perubahan</button>
            </form>
        </div>
    </div>

    <script>
        // 👇 TOMBOL HAMBURGER TOGGLE
        document.getElementById("hamburger").addEventListener("click", function () {
            document.getElementById("sidebar").classList.toggle("active");
            const mainContent = document.getElementById("main-content");
            if (document.getElementById("sidebar").classList.contains("active")) {
                mainContent.classList.add("sidebar-open");
            } else {
                mainContent.classList.remove("sidebar-open");
            }
        });

        // Toggle "LAINNYA" fields for keterangan
        $('[name^="pengaduan["][name$="[keterangan]"]').change(function() {
            const id = $(this).attr('name').match(/\[(\d+)\]/)[1];
            if ($(this).val() === "LAINNYA") {
                $("#keterangan_lainnya_" + id + "_box").show();
            } else {
                $("#keterangan_lainnya_" + id + "_box").hide();
                $("#keterangan_lainnya_" + id).val("");
            }
        });

        // Toggle "LAINNYA" fields for tindaklanjut
        $('[name^="pengaduan["][name$="[tindaklanjut]"]').change(function() {
            const id = $(this).attr('name').match(/\[(\d+)\]/)[1];
            if ($(this).val() === "LAINNYA") {
                $("#tindaklanjut_lainnya_" + id + "_box").show();
            } else {
                $("#tindaklanjut_lainnya_" + id + "_box").hide();
                $("#tindaklanjut_lainnya_" + id).val("");
            }
        });
    </script>
</body>
</html>