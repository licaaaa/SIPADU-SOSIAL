<?php
session_start();
include 'db.php';

if (!isset($_SESSION['admin'])) {
    header("Location: login_admin.php");
    exit;
}

$msg = '';
$msg_type = '';

// --- GANTI PASSWORD ---
if (isset($_POST['change_password'])) {
    $id = (int)$_POST['id'];
    $password = $_POST['password'];
    if (empty($password)) {
        $msg = "Password harus diisi.";
        $msg_type = "error";
    } elseif (strlen($password) < 6) {
        $msg = "Password minimal 6 karakter.";
        $msg_type = "error";
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashed, $id);
        if ($stmt->execute()) {
            $msg = "Password berhasil direset.";
            $msg_type = "success";
            header("Location: kelola_akun.php?msg=password_changed");
            exit;
        } else {
            $msg = "Gagal mengganti password.";
            $msg_type = "error";
        }
        $stmt->close();
    }
}

// --- EDIT USERNAME ---
if (isset($_POST['edit'])) {
    $id = (int)$_POST['id'];
    $username = trim($_POST['username']);
    if (empty($username)) {
        $msg = "Username harus diisi.";
        $msg_type = "error";
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        $stmt->bind_param("si", $username, $id);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $msg = "Username sudah digunakan.";
            $msg_type = "error";
        } else {
            $stmt2 = $conn->prepare("UPDATE users SET username = ? WHERE id = ?");
            $stmt2->bind_param("si", $username, $id);
            if ($stmt2->execute()) {
                $msg = "Username berhasil diperbarui.";
                $msg_type = "success";
                header("Location: kelola_akun.php?msg=updated");
                exit;
            } else {
                $msg = "Gagal memperbarui.";
                $msg_type = "error";
            }
            $stmt2->close();
        }
        $stmt->close();
    }
}

// --- TAMBAH USER ---
if (isset($_POST['tambah'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    if (empty($username) || empty($password)) {
        $msg = "Semua field harus diisi.";
        $msg_type = "error";
    } elseif (strlen($password) < 6) {
        $msg = "Password minimal 6 karakter.";
        $msg_type = "error";
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $msg = "Username sudah digunakan!";
            $msg_type = "error";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt2 = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $stmt2->bind_param("ss", $username, $hashed);
            if ($stmt2->execute()) {
                $msg = "User berhasil ditambahkan.";
                $msg_type = "success";
                header("Location: kelola_akun.php?msg=added");
                exit;
            } else {
                $msg = "Gagal menambahkan.";
                $msg_type = "error";
            }
            $stmt2->close();
        }
        $stmt->close();
    }
}

// --- HAPUS USER ---
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $msg = "User berhasil dihapus.";
        $msg_type = "success";
        header("Location: kelola_akun.php?msg=deleted");
        exit;
    } else {
        $msg = "Gagal menghapus.";
        $msg_type = "error";
    }
    $stmt->close();
}

// --- AMBIL DATA USER ---
$result = $conn->query("SELECT id, username FROM users ORDER BY id");

// --- PESAN DARI REDIRECT ---
if (isset($_GET['msg'])) {
    switch ($_GET['msg']) {
        case 'added':
            $msg = "User berhasil ditambahkan.";
            $msg_type = "success";
            break;
        case 'updated':
            $msg = "Username berhasil diperbarui.";
            $msg_type = "success";
            break;
        case 'deleted':
            $msg = "User berhasil dihapus.";
            $msg_type = "success";
            break;
        case 'password_changed':
            $msg = "Password user berhasil direset.";
            $msg_type = "success";
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no"/>
    <title>Admin | Sistem Register Pelayanan dan Pengaduan Publik</title>
    <!-- Google Fonts: Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
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

        h2, h3 {
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

        /* Form Tambah & Edit */
        .form-tambah, .form-edit {
            margin-bottom: 35px;
            padding: 24px;
            border: 1px solid #eee;
            border-radius: 12px;
            background: #f9f9f9;
        }

        .form-tambah h3, .form-edit h3 {
            margin-bottom: 18px;
            color: #3a7ca5;
            font-size: 18px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-bottom: 12px;
            font-size: 16px;
            background: #fff;
            transition: border 0.3s;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            border-color: #3a7ca5;
            outline: none;
            box-shadow: 0 0 0 2px rgba(58, 124, 165, 0.1);
        }

        button {
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

        button:hover {
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
            min-width: 600px;
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
            color: #007BFF;
            text-decoration: none;
            font-weight: 600;
            margin-right: 12px;
            transition: color 0.3s;
        }

        td a:hover {
            color: #0056b3;
            text-decoration: underline;
        }

        .action-delete {
            color: #dc3545 !important;
        }

        .action-delete:hover {
            color: #c82333 !important;
        }

        .action-password {
            color: #28a745 !important;
        }

        .action-password:hover {
            color: #1e7e34 !important;
        }

        /* Form Inline (Edit & Password) */
        .inline-form {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
            padding: 12px;
            background: #f8f9fa;
            border-radius: 8px;
            font-size: 14px;
        }

        .inline-form input {
            flex: 1;
            min-width: 150px;
        }

        .inline-form a {
            color: #6c757d;
            text-decoration: none;
            font-size: 14px;
        }

        .inline-form a:hover {
            color: #495057;
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
            h2, h3 {
                font-size: 20px;
            }
            .form-tambah, .form-edit {
                padding: 20px;
            }
            input[type="text"],
            input[type="password"] {
                font-size: 15px;
                padding: 10px 12px;
            }
            button {
                font-size: 15px;
                padding: 10px 16px;
            }
            th, td {
                padding: 12px;
                font-size: 14px;
            }
            .table-container {
                margin: 15px 0;
            }
            .inline-form {
                flex-direction: column;
            }
            .inline-form input {
                min-width: 100%;
            }
        }

        @media (max-width: 480px) {
            body {
                font-size: 14px;
            }
            h2 {
                font-size: 18px;
            }
            .alert {
                font-size: 14px;
            }
            th, td {
                font-size: 13px;
                padding: 10px 8px;
            }
            .inline-form a {
                font-size: 13px;
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
        <a href="kelola_jenis_layanan.php"><i class="fas fa-cog"></i> Kelola Jenis Layanan</a>
        <a href="admin_nik_cache.php"><i class="fas fa-database"></i> Viewer Cache NIK</a>
        <a href="riwayat_admin.php" class="active"><i class="fas fa-history"></i> Riwayat Permohonan</a>
        <a href="kelola_akun.php" class="active"><i class="fas fa-user-cog"></i> Kelola Akun User</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <!-- Main Content -->
    <div class="main-content" id="main-content">
        <div class="container">
            <h3><i class="fas fa-user-cog"></i> Kelola Akun User</h3>

            <?php if (!empty($msg)): ?>
                <div class="alert <?= $msg_type ?>">
                    <?= htmlspecialchars($msg) ?>
                </div>
            <?php endif; ?>

            <!-- Form Tambah -->
            <div class="form-tambah">
                <h3><i class="fas fa-user-plus"></i> Tambah User Baru</h3>
                <form method="post">
                    <input type="text" name="username" placeholder="Username" required minlength="3" maxlength="50" autocomplete="off">
                    <input type="password" name="password" placeholder="Password (min 6 karakter)" required minlength="6">
                    <button type="submit" name="tambah">Tambah User</button>
                </form>
            </div>

            <!-- Daftar User -->
            <h3><i class="fas fa-list"></i> Daftar User</h3>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['id']) ?></td>
                                <td><?= htmlspecialchars($row['username']) ?></td>
                                <td>
                                    <!-- Edit -->
                                    <a href="#" onclick="toggleEdit(<?= $row['id'] ?>); return false;" class="action-edit">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    
                                    <!-- Ganti Password -->
                                    <a href="#" onclick="togglePassword(<?= $row['id'] ?>); return false;" class="action-password">
                                        <i class="fas fa-key"></i> Ganti Password
                                    </a>
                                    
                                    <!-- Hapus -->
                                    <a href="?hapus=<?= $row['id'] ?>" class="action-delete"
                                       onclick="return confirm('Yakin hapus user: <?= htmlspecialchars(addslashes($row['username'])) ?>?')">
                                       <i class="fas fa-trash"></i> Hapus
                                    </a>

                                    <!-- Form Edit -->
                                    <div id="edit-form-<?= $row['id'] ?>" class="inline-form" style="display:none;">
                                        <form method="post" style="display:contents;">
                                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                            <input type="text" name="username" value="<?= htmlspecialchars($row['username']) ?>" required minlength="3" maxlength="50">
                                            <button type="submit" name="edit">Simpan</button>
                                            <a href="#" onclick="toggleEdit(<?= $row['id'] ?>); return false;">Batal</a>
                                        </form>
                                    </div>

                                    <!-- Form Ganti Password -->
                                    <div id="password-form-<?= $row['id'] ?>" class="inline-form" style="display:none;">
                                        <form method="post" style="display:contents;">
                                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                            <input type="password" name="password" placeholder="Password baru" required minlength="6">
                                            <button type="submit" name="change_password">Simpan</button>
                                            <a href="#" onclick="togglePassword(<?= $row['id'] ?>); return false;">Batal</a>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function toggleEdit(id) {
            const el = document.getElementById('edit-form-' + id);
            el.style.display = el.style.display === 'none' || el.style.display === '' ? 'flex' : 'none';
        }

        function togglePassword(id) {
            const el = document.getElementById('password-form-' + id);
            el.style.display = el.style.display === 'none' || el.style.display === '' ? 'flex' : 'none';
        }

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