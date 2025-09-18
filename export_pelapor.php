<?php
include 'db.php';

if (isset($_POST['export'])) {
    $tanggal_awal = mysqli_real_escape_string($conn, $_POST['tanggal_awal']);
    $tanggal_akhir = mysqli_real_escape_string($conn, $_POST['tanggal_akhir']);

    $filename = "Data_Pelapor_" . date("Ymd") . ".csv";

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filename);
    header('Pragma: no-cache');
    header('Expires: 0');

    $output = fopen('php://output', 'w');

    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

    fputcsv($output, ['NIK', 'Nama', 'Alamat', 'No HP', 'Layanan', 'Keterangan', 'Tindak Lanjut', 'Tanggal'], ',', '"');

    $query = mysqli_query($conn, "
        SELECT p.nik, p.nama, p.alamat, p.nohp, g.layanan, g.keterangan, g.tindaklanjut, g.tanggal
        FROM pelapor p
        JOIN pengaduan g ON p.nik = g.nik
        WHERE g.tanggal BETWEEN '$tanggal_awal' AND '$tanggal_akhir'
        ORDER BY g.tanggal ASC
    ");

    if (mysqli_num_rows($query) > 0) {
        while ($row = mysqli_fetch_assoc($query)) {
            // Tambahkan apostrophe (') di depan NIK supaya Excel anggap teks, tanpa tanda =
            $nik = "'" . $row['nik'];
            // No HP tetap tambahkan spasi di belakang biar aman
            $nohp = $row['nohp'] . ' ';

            $data = [
                $nik,
                $row['nama'],
                $row['alamat'],
                $nohp,
                $row['layanan'],
                $row['keterangan'],
                $row['tindaklanjut'],
                $row['tanggal']
            ];

            fputcsv($output, $data, ',', '"');
        }
    } else {
        fputcsv($output, ['Tidak ada data dalam rentang tanggal tersebut.'], ',', '"');
    }

    fclose($output);
    exit;
}
?>
