<?php
session_start();
if (!isset($_SESSION['login'])) {
  header("Location: login.php");
  exit;
}
include 'db.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no"/>
  <title>Sistem Register Pelayanan dan Pengaduan Publik</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <style>
    /* Styles sama seperti sebelumnya */
    * {margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif;}
    body {background:#f5f7fa;min-height:100vh;color:#444;overflow-x:hidden;}
    .hamburger {display:none;position:fixed;top:15px;left:15px;background:#3a7ca5;color:white;border:none;width:40px;height:40px;border-radius:8px;font-size:18px;cursor:pointer;z-index:1100;box-shadow:0 4px 8px rgba(0,0,0,0.2);}
    .sidebar {width:250px;background:#3a7ca5;color:white;padding:20px 0;display:flex;flex-direction:column;box-shadow:3px 0 15px rgba(0,0,0,0.1);position:fixed;height:100%;z-index:1000;transition:transform 0.3s ease;top:0;left:0;}
    .sidebar.hidden {transform:translateX(-100%);}
    .sidebar-logo {text-align:center;margin-bottom:20px;}
    .sidebar-logo img {width:70px;height:70px;object-fit:contain;border-radius:10px;box-shadow:0 4px 8px rgba(0,0,0,0.1);}
    .sidebar-title {text-align:center;font-size:16px;font-weight:600;margin-bottom:30px;padding:0 20px;color:#e0f0ff;}
    .sidebar a {padding:14px 20px;text-decoration:none;color:white;font-size:15px;transition:all 0.3s ease;display:flex;align-items:center;gap:12px;}
    .sidebar a:hover {background:#2f6b8f;border-left:3px solid white;padding-left:17px;}
    .main-content {margin-left:250px;padding:30px;transition:margin-left 0.3s ease;}
    .main-content.full-width {margin-left:0;}
    .container {max-width:700px;margin:0 auto;}
    .header {text-align:center;padding:25px 0;margin-bottom:25px;background:white;border-radius:20px;box-shadow:0 6px 20px rgba(0,0,0,0.08);}
    .header .logo-container {display:flex;align-items:center;justify-content:center;gap:15px;}
    .header .logo {width:80px;height:80px;object-fit:contain;border-radius:12px;box-shadow:0 4px 10px rgba(0,0,0,0.1);}
    .header .instansi {text-align:left;}
    .header .kota {font-size:18px;font-weight:700;color:#3a7ca5;}
    .header .dinas {font-size:15px;color:#555;}
    .header .app-title {font-size:20px;font-weight:600;color:#333;margin-top:10px;}
    h2 {font-size:24px;font-weight:600;color:#333;margin:30px 0 10px;}
    .subtitle {font-size:15px;color:#666;margin-bottom:25px;}
    .form-card {background:white;padding:30px;border-radius:16px;box-shadow:0 6px 20px rgba(0,0,0,0.08);}
    .form-section h3 {font-size:18px;color:#3a7ca5;margin-bottom:15px;font-weight:600;display:flex;align-items:center;gap:8px;}
    .form-section h3 i {background:#3a7ca5;color:white;width:28px;height:28px;display:flex;align-items:center;justify-content:center;border-radius:50%;font-size:14px;}
    .input-group {position:relative;margin-bottom:22px;}
    .input-group input,.input-group textarea,.input-group select {width:100%;padding:12px 0 10px 0;border:none;border-bottom:2px solid #e0e0e0;font-size:16px;outline:none;background:transparent;color:#333;transition:border-color 0.3s ease;text-transform:uppercase;}
    .input-group select {appearance:none;background:url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23777' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6,9 12,15 18,9'%3e%3c/polyline%3e%3c/svg%3e") no-repeat right 8px center;background-size:14px;}
    .input-group input:focus,.input-group textarea:focus,.input-group select:focus {border-color:#3a7ca5;}
    .input-group label {position:absolute;left:0;top:12px;font-size:16px;color:#777;pointer-events:none;transition:all 0.3s ease;}
    .input-group input:focus ~ label,.input-group input:not(:placeholder-shown) ~ label,.input-group select:focus ~ label,.input-group select:not(:invalid) ~ label {top:-10px;font-size:12px;color:#3a7ca5;font-weight:500;}
    .radio-group {display:flex;gap:20px;margin-bottom:15px;flex-wrap:wrap;}
    .radio-group label {display:flex;align-items:center;gap:6px;font-size:15px;cursor:pointer;color:#555;}
    .radio-group input[type="radio"] {accent-color:#3a7ca5;transform:scale(1.1);}
    button[type="submit"] {width:100%;padding:16px;background:#3a7ca5;color:white;border:none;border-radius:10px;font-size:17px;font-weight:600;cursor:pointer;transition:all 0.3s ease;box-shadow:0 4px 12px rgba(58,124,165,0.2);}
    button[type="submit"]:hover {background:#2f6b8f;transform:translateY(-2px);}
    button[type="submit"]:active {transform:translateY(0);}
    .success {text-align:center;padding:16px;background:#d4edda;color:#155724;border:1px solid #c3e6cb;border-radius:10px;margin:20px 0;font-size:15px;font-weight:500;}
    .success[style*="background:#f9eaea"] {background:#f9eaea;color:#c44545;border-color:#eed1d1;}
    #hasil-nik {font-size:14px;margin-top:6px;font-style:italic;}

    /* Modal Popup */
    .modal {
        display: none;
        position: fixed;
        z-index: 2000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        justify-content: center;
        align-items: center;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    .modal.show {
        display: flex;
        opacity: 1;
    }
    .modal-content {
        background-color: white;
        padding: 30px;
        border-radius: 16px;
        width: 90%;
        max-width: 400px;
        text-align: center;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        transform: scale(0.9);
        transition: transform 0.3s ease;
    }
    .modal.show .modal-content {
        transform: scale(1);
    }
    .modal-icon {
        font-size: 50px;
        color: #28a745;
        margin-bottom: 15px;
    }
    .modal-title {
        font-size: 20px;
        font-weight: 600;
        color: #333;
        margin-bottom: 10px;
    }
    .modal-message {
        font-size: 15px;
        color: #666;
        margin-bottom: 20px;
    }
    .btn-ok-modal {
        padding: 10px 20px;
        background: #3a7ca5;
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 15px;
        cursor: pointer;
        font-weight: 500;
        transition: background 0.3s ease;
    }
    .btn-ok-modal:hover {
        background: #2f6b8f;
    }

    @media(max-width:768px){
        .hamburger{display:block;}.sidebar{transform:translateX(-100%);}.sidebar.active{transform:translateX(0);}.main-content{margin-left:0;padding:20px;}.header .logo-container{flex-direction:row;gap:10px;}.header .kota{font-size:16px;}.header .dinas{font-size:13px;}.header .app-title{font-size:17px;}h2{font-size:20px;}.form-card{padding:20px;}.input-group{margin-bottom:20px;}.radio-group{gap:15px;flex-direction:column;}button[type="submit"]{padding:14px;font-size:16px;}
    }
    @media(max-width:480px){
        body{font-size:14px;}.header .logo{width:60px;height:60px;}.header .kota{font-size:15px;}.header .app-title{font-size:15px;}.input-group input,.input-group select{font-size:15px;}.input-group label{font-size:15px;}#hasil-nik{font-size:13px;}
    }
  </style>
</head>
<body>
  <button class="hamburger" id="hamburger"><i class="fas fa-bars"></i></button>
  <div class="sidebar" id="sidebar">
    <div class="sidebar-logo"><img src="images/kissme.png" alt="Logo Dinas Sosial"></div>
    <div class="sidebar-title">MENU UTAMA</div>
    <a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    <a href="pelapor.php"><i class="fas fa-users"></i> Data Pengunjung</a>
    <a href="pengaduan.php" class="active"><i class="fas fa-file-alt"></i> Form Pengaduan</a>
    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
  </div>

  <div class="main-content" id="main-content">
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

    <h2>Form Pengaduan</h2>
    <p class="subtitle">Isi formulir pengaduan masyarakat secara lengkap dan akurat.</p>

    <div class="form-card">
      <form method="POST" id="form-pengaduan">
        <div class="form-section">
          <h3><i class="fas fa-user"></i> Data Pengunjung</h3>
          <div class="input-group">
            <input type="text" name="nik" id="nik" placeholder=" " value="<?= isset($_GET['nik']) ? htmlspecialchars($_GET['nik']) : '' ?>" required minlength="16" maxlength="16" inputmode="numeric" autocomplete="off">
            <label for="nik">NIK (16 digit)</label>
          </div>
          <div id="hasil-nik"></div>
          <div class="input-group"><input type="text" name="nama" id="nama" placeholder=" " required><label for="nama">Nama Lengkap</label></div>
          <div class="input-group"><input type="text" name="alamat" id="alamat" placeholder=" " required><label for="alamat">Alamat</label></div>
          <div class="input-group"><input type="tel" name="nohp" id="nohp" placeholder=" " inputmode="tel"><label for="nohp">No HP (Opsional)</label></div>
        </div>

        <div class="form-section">
          <h3><i class="fas fa-clipboard-list"></i> Jenis Layanan</h3>
          <div class="radio-group">
            <?php
            $layanan_query = mysqli_query($conn, "SELECT * FROM jenis_layanan ORDER BY nama");
            while ($row = mysqli_fetch_assoc($layanan_query)) {
                $value = htmlspecialchars($row['nama']);
                echo "<label><input type='radio' name='layanan' value='$value' required> $value</label>";
            }
            ?>
          </div>

          <div class="input-group">
            <select name="keterangan" id="keterangan" required>
              <option value="" disabled selected></option>
              <?php
              $ket_query = mysqli_query($conn, "SELECT * FROM keterangan_layanan ORDER BY nama");
              while ($row = mysqli_fetch_assoc($ket_query)) {
                  $value = htmlspecialchars($row['nama']);
                  echo "<option value='$value'>$value</option>";
              }
              ?>
              <option value="LAINNYA">LAINNYA</option>
            </select>
            <label for="keterangan">Keterangan</label>
          </div>
          <div class="input-group" id="keterangan_lainnya_box" style="display:none;">
            <input type="text" name="keterangan_lainnya" id="keterangan_lainnya" placeholder=" ">
            <label for="keterangan_lainnya">Isi Keterangan Lainnya</label>
          </div>

          <div class="input-group">
            <select name="tindaklanjut" id="tindaklanjut" required>
              <option value="" disabled selected></option>
              <?php
              $tindak_query = mysqli_query($conn, "SELECT * FROM tindak_lanjut ORDER BY nama");
              while ($row = mysqli_fetch_assoc($tindak_query)) {
                  $value = htmlspecialchars($row['nama']);
                  echo "<option value='$value'>$value</option>";
              }
              ?>
              <option value="LAINNYA">LAINNYA</option>
            </select>
            <label for="tindaklanjut">Tindak Lanjut yang Diharapkan</label>
          </div>
          <div class="input-group" id="tindaklanjut_lainnya_box" style="display:none;">
            <input type="text" name="tindaklanjut_lainnya" id="tindaklanjut_lainnya" placeholder=" ">
            <label for="tindaklanjut_lainnya">Isi Tindak Lanjut Lainnya</label>
          </div>
        </div>
        <button type="submit" name="submit">Simpan</button>
      </form>

      <?php
      if (isset($_POST['submit'])) {
          $nama = isset($_POST['nama']) ? strtoupper(trim(mysqli_real_escape_string($conn, $_POST['nama']))) : '';
          $nik = isset($_POST['nik']) ? strtoupper(trim(mysqli_real_escape_string($conn, $_POST['nik']))) : '';
          $alamat = isset($_POST['alamat']) ? strtoupper(trim(mysqli_real_escape_string($conn, $_POST['alamat']))) : '';
          $nohp = !empty($_POST['nohp']) ? mysqli_real_escape_string($conn, $_POST['nohp']) : NULL;
          $layanan = isset($_POST['layanan']) ? strtoupper(mysqli_real_escape_string($conn, $_POST['layanan'])) : '';
          $keterangan = isset($_POST['keterangan']) ? strtoupper(mysqli_real_escape_string($conn, $_POST['keterangan'])) : '';
          if ($keterangan === 'LAINNYA' && !empty($_POST['keterangan_lainnya'])) {
              $keterangan = strtoupper(mysqli_real_escape_string($conn, $_POST['keterangan_lainnya']));
          }
          $tindaklanjut = isset($_POST['tindaklanjut']) ? strtoupper(mysqli_real_escape_string($conn, $_POST['tindaklanjut'])) : '';
          if ($tindaklanjut === 'LAINNYA' && !empty($_POST['tindaklanjut_lainnya'])) {
              $tindaklanjut = strtoupper(mysqli_real_escape_string($conn, $_POST['tindaklanjut_lainnya']));
          }

          $errors = [];
          if (!preg_match('/^\d{16}$/', $nik)) {$errors[] = "NIK harus 16 digit angka.";}
          if (empty($nama)) {$errors[] = "Nama wajib diisi.";}
          elseif (!preg_match('/^[a-zA-Z\s.]+$/', $nama)) {$errors[] = "Nama hanya boleh huruf dan spasi.";}
          elseif (strlen($nama) < 3) {$errors[] = "Nama minimal 3 karakter.";}
          if (empty($alamat)) {$errors[] = "Alamat wajib diisi.";}
          if (empty($layanan)) {$errors[] = "Jenis layanan harus dipilih.";}
          if (empty($keterangan)) {$errors[] = "Keterangan harus dipilih.";}
          if (empty($tindaklanjut)) {$errors[] = "Tindak lanjut harus dipilih.";}

          if (!empty($errors)) {
              foreach ($errors as $error) {
                  echo "<p class='success' style='background:#f9eaea;color:#c44545;border-color:#eed1d1;'>$error</p>";
              }
          } else {
              $cek = mysqli_query($conn, "SELECT * FROM pelapor WHERE nik='$nik'");
              if (mysqli_num_rows($cek) == 0) {
                  $insert_pelapor = mysqli_query($conn, "INSERT INTO pelapor (nama, nik, alamat, nohp) VALUES ('$nama', '$nik', '$alamat', '$nohp')");
                  if ($insert_pelapor) {
                      $insert_cache = mysqli_query($conn, "INSERT INTO nik_cache (nik, nama, alamat, nohp) VALUES ('$nik', '$nama', '$alamat', '$nohp')");
                      if (!$insert_cache) {
                          echo "<p class='success' style='background:#f9eaea;color:#c44545;'>Gagal simpan ke nik_cache: " . mysqli_error($conn) . "</p>";
                      }
                  } else {
                      echo "<p class='success' style='background:#f9eaea;color:#c44545;'>Gagal simpan data pelapor: " . mysqli_error($conn) . "</p>";
                  }
              }

              $insert_pengaduan = mysqli_query($conn, "INSERT INTO pengaduan (nik, layanan, keterangan, tindaklanjut) VALUES ('$nik', '$layanan', '$keterangan', '$tindaklanjut')");
              if ($insert_pengaduan) {
                  // Tampilkan modal, tidak otomatis hilang
                  echo "
                  <script>
                      $(document).ready(function() {
                          $('#successModal').addClass('show');
                      });
                  </script>";
              } else {
                  echo "<p class='success' style='background:#f9eaea;color:#c44545;'>Gagal menyimpan pengaduan: " . mysqli_error($conn) . "</p>";
              }
          }
      }
      ?>
    </div>
  </div>

  <!-- Modal Popup Sukses -->
  <div class="modal" id="successModal">
    <div class="modal-content">
      <div class="modal-icon">
        <i class="fas fa-check-circle"></i>
      </div>
      <div class="modal-title">Berhasil!</div>
      <div class="modal-message">Pengaduan Anda berhasil disimpan.<br>Terima kasih.</div>
      <button id="btn-ok-modal" class="btn-ok-modal">OK, Mengerti</button>
    </div>
  </div>

  <script>
    document.getElementById("hamburger").addEventListener("click", function () {
      document.getElementById("sidebar").classList.toggle("active");
    });

    // Batasi input NIK hanya angka 16 digit
    document.getElementById("nik").addEventListener("input", function () {
      this.value = this.value.replace(/[^0-9]/g, "").slice(0, 16);
    });

    // Auto-fill data pelapor berdasarkan NIK
    $("#nik").on("input", function() {
      let nik = $(this).val();
      if (nik.length === 16) {
        $.ajax({
          url: "cek_nik.php",
          method: "GET",
          data: { nik: nik },
          dataType: "json",
          success: function(response) {
            if(response.found) {
              $("#nama").val(response.nama).prop("readonly", true);
              $("#alamat").val(response.alamat).prop("readonly", true);
              $("#nohp").val(response.nohp).prop("readonly", false);
              $("#hasil-nik").text("Data pelapor ditemukan, otomatis terisi.").css("color","#28a745");
            } else {
              $("#nama, #alamat, #nohp").val("").prop("readonly", false);
              $("#hasil-nik").text("NIK belum terdaftar, silakan isi manual.").css("color","#dc3545");
            }
          },
          error: function() {
            $("#hasil-nik").text("Terjadi kesalahan saat memeriksa NIK.").css("color","#dc3545");
          }
        });
      } else {
        $("#nama, #alamat, #nohp").val("").prop("readonly", false);
        $("#hasil-nik").text("");
      }
    });

    // Keterangan lainnya
    $("#keterangan").change(function(){
      if($(this).val() === "LAINNYA"){
        $("#keterangan_lainnya_box").show();
      } else {
        $("#keterangan_lainnya_box").hide();
        $("#keterangan_lainnya").val("");
      }
    });

    // Tindak Lanjut lainnya
    $("#tindaklanjut").change(function(){
      if($(this).val() === "LAINNYA"){
        $("#tindaklanjut_lainnya_box").show();
      } else {
        $("#tindaklanjut_lainnya_box").hide();
        $("#tindaklanjut_lainnya").val("");
      }
    });

    // Tutup modal saat tombol OK diklik
    $(document).on('click', '#btn-ok-modal', function() {
        $('#successModal').removeClass('show');
        document.getElementById('form-pengaduan').reset();
        $('#keterangan_lainnya_box, #tindaklanjut_lainnya_box').hide();
    });
  </script>
</body>
</html>