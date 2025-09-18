<?php
require('fpdf/fpdf.php');
include 'db.php'; // Koneksi mysqli ($conn)

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'];
    $nik = $_POST['nik'];
    $alamat = $_POST['alamat'];
    $nohp = $_POST['nohp'];
    $tabel_nama = $_POST['tabel_nama'];
    $tabel_nik = $_POST['tabel_nik'];
    $tabel_alamat = $_POST['tabel_alamat']; // Tambahan
    $tabel_keperluan = $_POST['tabel_keperluan'];

    // Simpan data anggota ke array dengan alamat
    $data_anggota = [];
    for ($i = 0; $i < count($tabel_nama); $i++) {
        if (trim($tabel_nama[$i]) != '') {
            $data_anggota[] = [
                'nama' => $tabel_nama[$i],
                'nik' => $tabel_nik[$i],
                'alamat' => $tabel_alamat[$i], // Tambahkan alamat
                'keperluan' => $tabel_keperluan[$i]
            ];
        }
    }

    // Simpan ke database
    $stmt = $pdo->prepare("INSERT INTO permohonan_dtse (nama_pemohon, nik_pemohon, alamat, nohp, data_anggota) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        $nama,
        $nik,
        $alamat,
        $nohp,
        json_encode($data_anggota, JSON_UNESCAPED_UNICODE)
    ]);

    // Generate PDF
    $pdf = new FPDF();
    $pdf->AddPage();

    // Judul
    $pdf->SetFont('Arial','B',14);
    $pdf->Cell(0,10,'FORMULIR PERMOHONAN',0,1,'C');

    $pdf->SetDrawColor(0,0,0);
    $pdf->SetLineWidth(1.5);
    $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
    $pdf->Ln(5);

    $pdf->SetFont('Arial','',12);
    $pdf->Cell(0,7,'Keterangan Terdaftar Data Tunggal Sosial Ekonomi Nasional (DTSEN)',0,1,'C');
    $pdf->Ln(5);

    // Data Pemohon
    $pdf->SetFont('Arial','',11);
    $pdf->Cell(0,7,'Saya yang bertanda tangan di bawah ini:',0,1);
    $pdf->Cell(40,7,'Nama',0,0);
    $pdf->Cell(0,7,': '.$nama,0,1);
    $pdf->Cell(40,7,'NIK',0,0);
    $pdf->Cell(0,7,': '.$nik,0,1);
    $pdf->Cell(40,7,'Alamat',0,0);
    $pdf->MultiCell(0,7,': '.$alamat,0,1);
    $pdf->Cell(40,7,'No. HP',0,0);
    $pdf->Cell(0,7,': '.$nohp,0,1);
    $pdf->Ln(5);

    $text = "Dengan ini mengajukan permohonan cetak Surat Keterangan Terdaftar Data Tunggal Sosial Ekonomi Nasional (DTSEN), sesuai berkas salinan Kartu Keluarga yang saya sertakan atas nama sebagai berikut:";
    $pdf->MultiCell(0,6,$text);
    $pdf->Ln(3);

    // Tabel
    $pdf->SetDrawColor(0,0,0);
    $pdf->SetLineWidth(0.3);
    $pdf->SetFont('Arial','B',11);
    $pdf->Cell(10,8,'No',1,0,'C');
    $pdf->Cell(50,8,'Nama',1,0,'C');
    $pdf->Cell(40,8,'NIK',1,0,'C');
    $pdf->Cell(60,8,'Alamat',1,0,'C'); // Tambahkan kolom alamat
    $pdf->Cell(40,8,'Keperluan',1,0,'C');
    $pdf->Ln();

    $pdf->SetFont('Arial','',11);
    foreach ($data_anggota as $index => $anggota) {
        $pdf->Cell(10,8,$index+1,1,0,'C');
        $pdf->Cell(50,8,$anggota['nama'],1);
        $pdf->Cell(40,8,$anggota['nik'],1);
        $pdf->Cell(60,8,$anggota['alamat'],1); // Tampilkan alamat
        $pdf->Cell(40,8,$anggota['keperluan'],1);
        $pdf->Ln();
    }

    $pdf->Ln(10);
    $pdf->SetFont('Arial','',11);
    $pdf->MultiCell(0,6,"Demikian surat permohonan ini saya ajukan. Saya bersedia menjaga dan bertanggung jawab terhadap keamanan data dan menghindari penggunaan data oleh pihak yang tidak berkepentingan.");

    // Tanggal & tanda tangan
    $bulanIndo = [
        1=>'Januari','Februari','Maret','April','Mei','Juni',
        'Juli','Agustus','September','Oktober','November','Desember'
    ];
    $pdf->Ln(10);
    $pdf->Cell(0,6,'Tanjungpinang, '.date('d').' '.$bulanIndo[(int)date('m')].' '.date('Y'),0,1,'R');
    $pdf->Ln(15);
    $pdf->Cell(0,6,'(Pemohon)',0,1,'R');

    $pdf->Output('I', 'Formulir_DTSEN_' . $nik . '.pdf');
} else {
    echo "Akses tidak sah!";
}
?>
