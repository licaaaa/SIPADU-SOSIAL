<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}
include 'db.php';

// Ambil data pelapor jika ada NIK dari pelapor.php
$nama = $nik = $alamat = $nohp = '';
if (isset($_GET['nik'])) {
    $nik_param = mysqli_real_escape_string($conn, $_GET['nik']);
    $query = mysqli_query($conn, "SELECT * FROM pelapor WHERE nik = '$nik_param'");
    if (mysqli_num_rows($query) > 0) {
        $data = mysqli_fetch_assoc($query);
        $nik = $data['nik'];
        $nama = $data['nama'];
        $alamat = $data['alamat'];
        $nohp = $data['nohp'];
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Sistem Register Pelayanan dan Pengaduan Publik</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: auto;
            background: #fff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            box-sizing: border-box;
        }
        h2 {
            text-align: center;
            color: #3a7ca5;
            margin-top: 10px;
        }
        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
        }
        input[type="text"], textarea {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
            font-size: 1rem;
        }
        textarea {
            resize: vertical;
        }
        .button {
            margin-top: 20px;
            padding: 12px;
            background: #3a7ca5;
            color: #fff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            width: 100%;
            font-weight: bold;
            font-size: 16px;
        }
        .button:hover {
            background: #2f6b8f;
        }
        .button:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        .table-container {
            margin-top: 25px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: auto;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
            vertical-align: middle;
            word-wrap: break-word;
            white-space: normal;
        }
        th {
            background: #3a7ca5;
            color: #fff;
        }
        .action-btn {
            text-align: center;
            white-space: nowrap;
        }
        .action-btn span {
            cursor: pointer;
            font-weight: bold;
            font-size: 18px;
            user-select: none;
            padding: 0 6px;
        }
        .action-btn span:hover {
            opacity: 0.7;
        }
        .back-btn {
            display: inline-block;
            margin-bottom: 15px;
            padding: 10px 20px;
            background-color: #3a7ca5;
            color: white;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
            transition: background 0.3s;
        }
        .back-btn:hover {
            background-color: #2f6b8f;
        }

        /* Tombol tambah baris di atas tabel */
        .table-actions {
            text-align: right;
            margin-bottom: 8px;
        }
        .add-row-btn {
            background: #3a7ca5;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            font-weight: bold;
        }
        .add-row-btn:hover {
            background: #2f6b8f;
        }

        /* RESPONSIVE */
        @media screen and (max-width: 640px) {
            body { padding: 10px; }
            .container {
                max-width: 100%;
                padding: 15px;
                border-radius: 0;
                box-shadow: none;
            }
            h2 { font-size: 1.5rem; }
            label { margin-top: 10px; font-size: 1rem; }
            input[type="text"], textarea, .button {
                font-size: 1rem;
                padding: 10px;
            }
            .back-btn { padding: 8px 15px; font-size: 1rem; }
            table { font-size: 0.9rem; }
            th, td { padding: 6px 5px; }
            .action-btn span { font-size: 20px; padding: 0 8px; }
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<div class="container">
    <a href="pelapor.php" class="back-btn">&larr; Kembali</a>
    <h2>Form Permohonan DTSEN</h2>

    <form method="POST" action="cetak_dtsen.php" autocomplete="off">
        <!-- Form Pelapor -->
        <label>Nama Lengkap</label>
        <input type="text" name="nama" id="nama" required 
               value="<?= htmlspecialchars($nama) ?>" 
               <?= !empty($nama) ? 'readonly' : '' ?> />

        <label>NIK</label>
        <input type="text" name="nik" id="nik" maxlength="16" required 
               value="<?= htmlspecialchars($nik) ?>" 
               <?= !empty($nik) ? 'readonly' : '' ?> />

        <label>Alamat</label>
        <textarea name="alamat" id="alamat" rows="3" required <?= !empty($alamat) ? 'readonly' : '' ?>><?= htmlspecialchars($alamat) ?></textarea>

        <label>No HP</label>
        <input type="text" name="nohp" id="nohp" maxlength="15" required 
               value="<?= htmlspecialchars($nohp) ?>" 
               <?= !empty($nohp) ? 'readonly' : '' ?> />

        <!-- Tabel: Daftar Nama untuk Keterangan -->
        <div class="table-container">
            <label>Daftar Nama untuk Keterangan</label>

            <!-- Tombol tambah baris -->
            <div class="table-actions">
                <button type="button" class="add-row-btn" onclick="tambahBaris()">+</button>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>NIK</th>
                        <th>Alamat</th>
                        <th>Keperluan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="daftar-keterangan">
                    <tr>
                        <td>1</td>
                        <td><input type="text" name="tabel_nama[]" placeholder="Nama lengkap" class="input-nama" /></td>
                        <td><input type="text" name="tabel_nik[]" placeholder="NIK" class="input-nik" maxlength="16" /></td>
                        <td><input type="text" name="tabel_alamat[]" placeholder="Alamat" class="input-alamat" /></td>
                        <td><input type="text" name="tabel_keperluan[]" placeholder="Contoh: Permohonan" /></td>
                        <td class="action-btn">
                            <span onclick="hapusBaris(this)" style="color:red;">&times;</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <button type="submit" class="button" id="submitBtn">Cetak PDF</button>
    </form>
</div>

<script>
$(document).ready(function() {
    // Autofill untuk NIK di tabel "Daftar Nama untuk Keterangan"
    $('#daftar-keterangan').on('blur', '.input-nik', function() {
        var nikVal = $(this).val();
        var row = $(this).closest('tr');

        if (nikVal.length === 16 && /^\d+$/.test(nikVal)) {
            $.ajax({
                url: 'cek_nip2.php',
                type: 'POST',
                data: { nik: nikVal },
                dataType: 'json',
                success: function(data) {
                    if (data.exists) {
                        row.find('.input-nama').val(data.nama);
                        row.find('.input-alamat').val(data.alamat);
                    } else {
                        alert("NIK tidak ditemukan di database.");
                        row.find('.input-nama').val('');
                        row.find('.input-alamat').val('');
                    }
                },
                error: function() {
                    alert("Gagal menghubungi server.");
                }
            });
        } else if (nikVal !== '') {
            alert("NIK harus 16 digit angka.");
            row.find('.input-nik').val('');
            row.find('.input-nama').val('');
            row.find('.input-alamat').val('');
        }
    });

    // Validasi jumlah baris saat submit
    $('form').on('submit', function() {
        const rowCount = $('#daftar-keterangan tr').length;
        if (rowCount === 0) {
            alert("Minimal satu baris data harus diisi.");
            return false;
        }
    });
});

// Tambah baris baru
function tambahBaris() {
    let tbody = document.getElementById('daftar-keterangan');
    let rowCount = tbody.rows.length;
    let row = tbody.insertRow();

    row.innerHTML = `
        <td>${rowCount + 1}</td>
        <td><input type="text" name="tabel_nama[]" class="input-nama" placeholder="Nama lengkap"></td>
        <td><input type="text" name="tabel_nik[]" class="input-nik" placeholder="NIK" maxlength="16"></td>
        <td><input type="text" name="tabel_alamat[]" class="input-alamat" placeholder="Alamat"></td>
        <td><input type="text" name="tabel_keperluan[]" placeholder="Contoh: Permohonan"></td>
        <td class="action-btn">
            <span onclick="hapusBaris(this)" style="color:red;">&times;</span>
        </td>
    `;
}

// Hapus baris
function hapusBaris(btn) {
    let row = btn.closest('tr');
    let tbody = row.parentNode;
    if (tbody.rows.length > 1) {
        tbody.removeChild(row);
        // Update nomor urut
        for (let i = 0; i < tbody.rows.length; i++) {
            tbody.rows[i].cells[0].textContent = i + 1;
        }
    } else {
        alert("Minimal harus ada satu baris.");
    }
}
</script>
</body>
</html>
