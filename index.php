<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

include 'db.php';

// Ambil tanggal filter pengaduan (default hari ini)
$tanggal_pengaduan = isset($_GET['tanggal_pengaduan']) ? $_GET['tanggal_pengaduan'] : date('Y-m-d');

// Ambil semua jenis layanan dan total pengaduan per layanan berdasarkan tanggal_pengaduan
$layanan_data = [];
$total_pengaduan = 0;
$sql_layanan = "SELECT layanan, COUNT(*) as total 
                FROM pengaduan 
                WHERE DATE(tanggal) = ?
                GROUP BY layanan 
                ORDER BY layanan";
$stmt_layanan = mysqli_prepare($conn, $sql_layanan);
mysqli_stmt_bind_param($stmt_layanan, "s", $tanggal_pengaduan);
mysqli_stmt_execute($stmt_layanan);
$result_layanan = mysqli_stmt_get_result($stmt_layanan);
while ($row = mysqli_fetch_assoc($result_layanan)) {
    $layanan_data[$row['layanan']] = (int)$row['total'];
    $total_pengaduan += (int)$row['total'];
}

// Ambil tanggal filter DTSEN (default hari ini)
$tanggal = isset($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d');

// Ambil jumlah permohonan DTSEN untuk tanggal terpilih
$riwayat_per_tanggal = [];
$sql = "SELECT DATE(tanggal_submit) as tanggal, COUNT(*) as total
        FROM permohonan_dtse
        WHERE DATE(tanggal_submit) = ?
        GROUP BY DATE(tanggal_submit)";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $tanggal);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

while ($row = mysqli_fetch_assoc($result)) {
    $riwayat_per_tanggal[] = $row;
}

// Untuk chart, siapkan label dan data pengaduan
$chart_labels = array_keys($layanan_data);
$chart_values = array_values($layanan_data);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Sistem Register Pelayanan dan Pengaduan Publik</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
<style>
/* ... (CSS sama seperti sebelumnya) ... */
*{margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif}
body{background:#f5f7fa;color:#444;min-height:100vh;overflow-x:hidden}
.sidebar{width:250px;background:#3a7ca5;color:white;padding:20px 0;display:flex;flex-direction:column;position:fixed;height:100%;z-index:1000;transition:transform .3s ease;top:0;left:0;box-shadow:3px 0 15px rgba(0,0,0,0.1)}
.sidebar-logo{text-align:center;margin-bottom:20px}
.sidebar-logo img{width:70px;height:70px;object-fit:contain;border-radius:10px;box-shadow:0 4px 8px rgba(0,0,0,0.1)}
.sidebar-title{text-align:center;font-size:16px;font-weight:600;margin-bottom:30px;color:#e0f0ff}
.sidebar a{padding:14px 20px;text-decoration:none;color:white;font-size:15px;display:flex;align-items:center;gap:12px;transition:all .3s ease}
.sidebar a:hover{background:#2f6b8f;border-left:3px solid white;padding-left:17px}
.sidebar a.active{background:#2d6385;font-weight:600}
.hamburger{display:none;position:fixed;top:15px;left:15px;background:#3a7ca5;color:white;border:none;width:40px;height:40px;border-radius:8px;font-size:18px;cursor:pointer;z-index:1100;box-shadow:0 4px 8px rgba(0,0,0,0.2)}
.main-content{margin-left:250px;padding:30px;transition:margin-left .3s ease}
.main-content.full-width{margin-left:0}
.container{max-width:900px;margin:0 auto;background:white;padding:30px;border-radius:16px;box-shadow:0 6px 20px rgba(0,0,0,.08)}
h2{font-size:24px;font-weight:600;color:#333;margin:30px 0 10px;text-align:center}
.subtitle{font-size:15px;color:#666;margin-bottom:25px;text-align:center}
.stats{display:flex;justify-content:space-around;margin:40px 0;flex-wrap:wrap;gap:15px}
.stat-card{background:white;padding:20px;border-radius:16px;box-shadow:0 6px 16px rgba(0,0,0,0.06);text-align:center;min-width:130px;flex:1 1 130px;transition:transform .3s ease,box-shadow .3s ease}
.stat-card:hover{transform:translateY(-5px);box-shadow:0 10px 20px rgba(0,0,0,0.1)}
.stat-card h4{font-size:15px;color:#555;margin-bottom:10px;font-weight:500}
.stat-card p{font-size:28px;font-weight:700;color:#3a7ca5}
.table-container{overflow-x:auto;-webkit-overflow-scrolling:touch;margin:20px 0;border-radius:10px;box-shadow:0 4px 12px rgba(0,0,0,0.06)}
table{width:100%;border-collapse:collapse;min-width:400px;background:white}
th,td{border:1px solid #ddd;padding:14px 16px;text-align:left}
th{background:#3a7ca5;color:white;font-weight:600}
.no-data{text-align:center;color:#666;font-style:italic}
form{margin:20px 0;text-align:center}
input[type=date]{padding:6px 10px;border-radius:6px;border:1px solid #ccc;margin-right:10px}
button{padding:6px 12px;border-radius:6px;border:none;background:#3a7ca5;color:white;cursor:pointer}
button:hover{background:#2d6385}
.chart-container{display:flex;justify-content:center;align-items:center;max-width:600px;height:300px;margin:40px auto;}
@media(max-width:768px){.hamburger{display:block}.sidebar{transform:translateX(-100%)}.sidebar.active{transform:translateX(0)}.main-content{margin-left:0;padding:20px}}
@media(max-width:480px){body{font-size:14px}h2{font-size:18px}th,td{padding:10px 12px;font-size:13px}}
</style>
</head>
<body>
<button class="hamburger" id="hamburger"><i class="fas fa-bars"></i></button>

<div class="sidebar" id="sidebar">
    <div class="sidebar-logo"><img src="images/kissme.png" alt="Logo Dinsos"></div>
    <div class="sidebar-title">MENU UTAMA</div>
    <a href="index.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    <a href="pelapor.php"><i class="fas fa-users"></i> Data Pengunjung</a>
    <a href="pengaduan.php"><i class="fas fa-file-alt"></i> Form Pengaduan</a>
    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

<div class="main-content" id="main-content">
<div class="container">
    <h2>Dashboard Pengaduan Publik</h2>
    <p class="subtitle">Ringkasan data pengaduan masyarakat berdasarkan jenis layanan.</p>

    <!-- Form filter tanggal pengaduan -->
    <form method="get" style="text-align:center; margin-bottom: 20px;">
        <label for="tanggal_pengaduan">Filter Tanggal Pengaduan:</label>
        <input type="date" id="tanggal_pengaduan" name="tanggal_pengaduan" value="<?= htmlspecialchars($tanggal_pengaduan) ?>">
        <!-- Supaya nilai filter DTSEN tetap ikut terkirim saat filter pengaduan -->
        <input type="hidden" name="tanggal" value="<?= htmlspecialchars($tanggal) ?>">
        <button type="submit">Filter</button>
    </form>

    <!-- Statistik -->
    <div class="stats">
        <div class="stat-card"><h4>Total Pengaduan</h4><p><?= $total_pengaduan ?></p></div>
        <?php foreach ($layanan_data as $layanan => $jumlah): ?>
            <div class="stat-card"><h4><?= htmlspecialchars($layanan) ?></h4><p><?= $jumlah ?></p></div>
        <?php endforeach; ?>
    </div>

    <!-- Pie Chart Total Pengaduan -->
    <div class="chart-container">
        <canvas id="pieChart"></canvas>
    </div>

    <!-- Form pilih tanggal DTSEN -->
    <h2>Jumlah Penerbitan DTSEN Per Hari</h2>
    <form method="get">
        <input type="date" name="tanggal" value="<?= htmlspecialchars($tanggal) ?>">
        <!-- Supaya nilai filter pengaduan tetap ikut terkirim saat filter DTSEN -->
        <input type="hidden" name="tanggal_pengaduan" value="<?= htmlspecialchars($tanggal_pengaduan) ?>">
        <button type="submit">Tampilkan</button>
    </form>

    <!-- Tabel hasil DTSEN -->
    <div class="table-container">
        <table>
            <thead>
                <tr><th>No</th><th>Tanggal</th><th>Jumlah Penerbitan DTSEN</th></tr>
            </thead>
            <tbody>
            <?php if(empty($riwayat_per_tanggal)): ?>
                <tr><td colspan="3" class="no-data">Tidak ada data untuk tanggal ini</td></tr>
            <?php else: ?>
                <?php foreach($riwayat_per_tanggal as $i=>$row): ?>
                <tr>
                    <td><?= $i+1 ?></td>
                    <td><?= date('d-m-Y', strtotime($row['tanggal'])) ?></td>
                    <td><?= $row['total'] ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</div>

<script>
const hamburger=document.getElementById('hamburger');
const sidebar=document.getElementById('sidebar');
const mainContent=document.getElementById('main-content');
hamburger.addEventListener('click',()=>{sidebar.classList.toggle('active');mainContent.classList.toggle('full-width')});
document.addEventListener('click',(e)=>{if(!sidebar.contains(e.target)&&!hamburger.contains(e.target)&&window.innerWidth<=768){sidebar.classList.remove('active');mainContent.classList.remove('full-width')}});
window.addEventListener('resize',()=>{if(window.innerWidth>768){sidebar.classList.remove('active');mainContent.classList.remove('full-width')}});

// Pie Chart Total Pengaduan
const ctx = document.getElementById('pieChart');
if(ctx){
    new Chart(ctx, {
        type:'pie',
        data:{
            labels: <?= json_encode($chart_labels) ?>,
            datasets:[{
                label:'Jumlah Pengaduan',
                data: <?= json_encode($chart_values) ?>,
                backgroundColor:[
                    'rgba(58,124,165,0.85)',
                    'rgba(255,193,7,0.8)',
                    'rgba(40,167,69,0.8)',
                    'rgba(220,53,69,0.8)',
                    'rgba(23,162,184,0.8)',
                    'rgba(255,99,132,0.8)',
                    'rgba(255,159,64,0.8)'
                ],
                borderColor:'#fff',
                borderWidth:2,
                hoverOffset:12
            }]
        },
        options:{
            responsive:true,
            maintainAspectRatio:false,
            plugins:{
                legend:{
                    position:'bottom',
                    labels:{
                        color:'#444',
                        font:{size:13},
                        usePointStyle:true,
                        padding:12
                    }
                },
                tooltip:{
                    backgroundColor:'rgba(0,0,0,0.85)',
                    titleColor:'#fff',
                    bodyColor:'#fff',
                    cornerRadius:8,
                    padding:8,
                    titleFont:{size:13},
                    bodyFont:{size:12}
                },
                datalabels:{
                    color:'#fff',
                    font:{weight:'bold',size:12},
                    formatter:(value,context)=>{
                        const total = context.dataset.data.reduce((a,b)=>a+b,0);
                        return ((value/total)*100).toFixed(1)+'%';
                    }
                }
            }
        },
        plugins:[ChartDataLabels]
    });
}
</script>
</body>
</html>
