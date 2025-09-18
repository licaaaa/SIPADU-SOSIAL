<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login_admin.php");
    exit;
}

include 'db.php';

$search = isset($_GET['search']) ? trim(mysqli_real_escape_string($conn, $_GET['search'])) : '';

// Hapus per item (setelah otentikasi)
if (isset($_GET['delete']) && preg_match('/^\d{16}$/', $_GET['delete'])) {
    $del_nik = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM nik_cache WHERE nik='$del_nik'");
    header("Location: admin_nik_cache.php?msg=deleted");
    exit;
}

// Hapus semua (setelah otentikasi)
if (isset($_GET['clear']) && $_GET['clear'] === "all") {
    mysqli_query($conn, "TRUNCATE TABLE nik_cache");
    header("Location: admin_nik_cache.php?msg=cleared");
    exit;
}

// Ambil data cache
$where = $search ? "WHERE nik LIKE '%$search%' OR nama LIKE '%$search%' OR alamat LIKE '%$search%'" : "";
$query = mysqli_query($conn, "SELECT * FROM nik_cache $where ORDER BY updated_at DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no"/>
    <title>Admin | Sistem Register Pelayanan dan Pengaduan Publik</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet" />
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>
        * {margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif;}
        body {background:#f5f7fa;color:#444;min-height:100vh;overflow-x:hidden;}

        /* Hamburger */
        .hamburger {
            display:none;
            position:fixed;top:15px;left:15px;
            background:#3a7ca5;color:white;
            border:none;width:40px;height:40px;
            border-radius:8px;font-size:18px;
            cursor:pointer;z-index:1100;
            box-shadow:0 4px 8px rgba(0,0,0,0.2);
        }

        /* Sidebar */
        .sidebar {
            width:250px;background:#3a7ca5;color:white;
            padding:20px 0;display:flex;flex-direction:column;
            position:fixed;height:100%;z-index:1000;
            top:0;left:0;transition:transform .3s ease;
        }
        .sidebar.hidden {transform:translateX(-100%);}
        .sidebar.active {transform:translateX(0);}
        .sidebar-logo {text-align:center;margin-bottom:20px;}
        .sidebar-logo img {width:70px;height:70px;border-radius:10px;}
        .sidebar-title {text-align:center;font-size:16px;font-weight:600;margin-bottom:30px;color:#e0f0ff;}
        .sidebar a {
            padding:14px 20px;text-decoration:none;color:white;
            font-size:15px;display:flex;align-items:center;gap:12px;
            transition:all .3s ease;
        }
        .sidebar a:hover {
            background:#2f6b8f;border-left:3px solid white;padding-left:17px;
        }

        /* Main Content */
        .main-content {margin-left:250px;padding:30px;transition:margin-left .3s ease;}
        .main-content.full-width {margin-left:0;}

        .container {
            max-width:1000px;margin:0 auto;background:white;
            padding:30px;border-radius:16px;
            box-shadow:0 6px 20px rgba(0,0,0,0.08);
        }

        h2 {font-size:24px;color:#3a7ca5;margin-bottom:20px;text-align:center;}

        .topbar {
            display:flex;justify-content:space-between;flex-wrap:wrap;
            align-items:center;margin-bottom:20px;gap:10px;
        }

        .search-box input {
            padding:12px 14px;border:1px solid #ccc;
            border-radius:8px;width:280px;font-size:15px;
        }

        .btn-clear {
            background:#c44545;color:white;padding:12px 18px;
            border:none;border-radius:8px;cursor:pointer;
            font-size:15px;font-weight:600;
        }
        .btn-clear:hover {background:#a83232;}

        /* Alerts */
        .success {
            background:#d4edda;color:#155724;
            padding:14px;border-radius:8px;margin-bottom:20px;
            font-weight:500;text-align:center;font-size:15px;
        }

        /* Table */
        .table-container {
            overflow-x:auto;-webkit-overflow-scrolling:touch;
            margin:20px 0;border-radius:10px;
            box-shadow:0 4px 12px rgba(0,0,0,0.06);
        }
        table {width:100%;border-collapse:collapse;min-width:700px;background:white;}
        th,td {padding:14px 16px;border:1px solid #ddd;text-align:left;font-size:14px;}
        th {background:#3a7ca5;color:white;font-weight:600;}
        .actions a {color:#dc3545;text-decoration:none;margin-right:8px;font-weight:600;}
        .actions a:hover {text-decoration:underline;}

        /* Modal Otentikasi */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
            z-index: 1200;
        }
        .modal-content {
            background: white;
            padding: 25px;
            border-radius: 12px;
            width: 350px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.2);
            text-align: center;
        }
        .modal-content h3 {
            margin-bottom: 15px;
            color: #3a7ca5;
        }
        .modal-content p {
            margin-bottom: 15px;
            font-size: 14px;
        }
        .modal-content input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            margin-bottom: 15px;
            font-size: 14px;
        }
        .modal-content .btn-group {
            display: flex;
            gap: 10px;
            justify-content: center;
        }
        .modal-content .btn-cancel {
            flex: 1;
            padding: 10px;
            background: #ccc;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        .modal-content .btn-confirm {
            flex: 1;
            padding: 10px;
            background: #3a7ca5;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        /* Responsive */
        @media (max-width:768px){
            .hamburger {display:block;}
            .sidebar {transform:translateX(-100%);}
            .sidebar.active {transform:translateX(0);}
            .main-content {margin-left:0;padding:20px;}
            .container {padding:20px;}
            h2 {font-size:20px;}
            .search-box input {width:100%;}
            th,td {padding:12px 14px;font-size:13px;}
        }
        @media (max-width:480px){
            body {font-size:14px;}
            h2 {font-size:18px;}
            .search-box input {font-size:14px;padding:10px;}
            .btn-clear {font-size:14px;padding:10px;}
            th,td {padding:10px 12px;font-size:12px;}
            .success {font-size:14px;}
        }
    </style>
</head>
<body>

<!-- Hamburger -->
<button class="hamburger" id="hamburger"><i class="fas fa-bars"></i></button>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-logo"><img src="images/kissme.png" alt="Logo Dinas Sosial"></div>
    <div class="sidebar-title">ADMIN PANEL</div>
    <a href="kelola_jenis_layanan.php"><i class="fas fa-cog"></i> Kelola Jenis Layanan</a>
    <a href="admin_nik_cache.php" class="active"><i class="fas fa-database"></i> Viewer Cache NIK</a>
    <a href="riwayat_admin.php"><i class="fas fa-history"></i> Riwayat Permohonan</a>
    <a href="kelola_akun.php"><i class="fas fa-user-cog"></i> Kelola Akun User</a>
    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

<!-- Main Content -->
<div class="main-content" id="main-content">
    <div class="container">
        <h2>Viewer Cache NIK</h2>

        <div class="topbar">
            <form class="search-box" method="GET">
                <input type="text" name="search" placeholder="Cari NIK / Nama / Alamat..." value="<?= htmlspecialchars($search) ?>">
            </form>
            <button type="button" id="clearAllBtn" class="btn-clear"><i class="fas fa-trash-alt"></i> Kosongkan Semua</button>
        </div>

        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
            <div class="success">Cache berhasil dihapus.</div>
        <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'cleared'): ?>
            <div class="success">Semua cache telah dikosongkan.</div>
        <?php endif; ?>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>NIK</th>
                        <th>Nama</th>
                        <th>Alamat</th>
                        <th>No HP</th>
                        <th>Source</th>
                        <th>Update</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (mysqli_num_rows($query) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($query)): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['nik']) ?></td>
                            <td><?= htmlspecialchars($row['nama']) ?></td>
                            <td><?= htmlspecialchars($row['alamat']) ?></td>
                            <td><?= htmlspecialchars($row['nohp']) ?></td>
                            <td><?= htmlspecialchars($row['source']) ?></td>
                            <td><?= $row['updated_at'] ?></td>
                            <td class="actions">
                                <a href="#" data-action="delete" data-nik="<?= $row['nik'] ?>">
                                    <i class="fas fa-trash"></i> Hapus
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="7" style="text-align:center;">Tidak ada data cache ditemukan.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Otentikasi -->
<div id="authModal" class="modal">
    <div class="modal-content">
        <h3>Verifikasi Admin</h3>
        <p>Masukkan kode otentikasi untuk melanjutkan:</p>
        <input type="password" id="authPin" placeholder="Masukkan kode">
        <div class="btn-group">
            <button id="cancelAuth" class="btn-cancel">Batal</button>
            <button id="confirmAuth" class="btn-confirm">Lanjutkan</button>
        </div>
    </div>
</div>

<script>
const modal = document.getElementById('authModal');
const authPinInput = document.getElementById('authPin');
const cancelAuth = document.getElementById('cancelAuth');
const confirmAuth = document.getElementById('confirmAuth');

let pendingAction = null; // { type: 'delete', nik: '...' } atau { type: 'clear' }

function openAuthModal(action) {
    pendingAction = action;
    modal.style.display = 'flex';
    authPinInput.value = '';
    setTimeout(() => authPinInput.focus(), 100);
}

cancelAuth.addEventListener('click', () => {
    modal.style.display = 'none';
    pendingAction = null;
});

window.addEventListener('click', (e) => {
    if (e.target === modal) {
        modal.style.display = 'none';
        pendingAction = null;
    }
});

confirmAuth.addEventListener('click', () => {
    const pin = authPinInput.value.trim();
    if (!pin) {
        alert('Masukkan kode otentikasi.');
        return;
    }

    // Kirim ke server untuk verifikasi
    fetch('verify_auth.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'pin=' + encodeURIComponent(pin)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            if (pendingAction.type === 'delete') {
                window.location.href = '?delete=' + pendingAction.nik;
            } else if (pendingAction.type === 'clear') {
                window.location.href = '?clear=all';
            }
        } else {
            alert('Kode otentikasi salah!');
            authPinInput.value = '';
            authPinInput.focus();
        }
    })
    .catch(() => {
        alert('Gagal terhubung ke server.');
    });
});

// Event listener untuk tombol hapus
document.querySelectorAll('a[data-action="delete"]').forEach(link => {
    link.addEventListener('click', (e) => {
        e.preventDefault();
        const nik = link.getAttribute('data-nik');
        openAuthModal({ type: 'delete', nik });
    });
});

// Event listener untuk tombol "Kosongkan Semua"
document.getElementById('clearAllBtn').addEventListener('click', (e) => {
    e.preventDefault();
    openAuthModal({ type: 'clear' });
});

// Sidebar logic
const hamburger = document.getElementById('hamburger');
const sidebar = document.getElementById('sidebar');
const mainContent = document.getElementById('main-content');

hamburger.addEventListener('click', () => {
    sidebar.classList.toggle('active');
    mainContent.classList.toggle('full-width');
});
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