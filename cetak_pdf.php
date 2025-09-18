<?php
require('fpdf/fpdf.php');
include 'db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID tidak ditemukan atau tidak valid!");
}

$id = (int)$_GET['id'];
$result = mysqli_query($conn, "SELECT * FROM permohonan_dtse WHERE id = $id");
$p = mysqli_fetch_assoc($result);

if (!$p) {
    die("Data tidak ditemukan!");
}

// Ambil data dari database
$nama   = $p['nama_pemohon'];
$nik    = $p['nik_pemohon'];
$alamat = $p['alamat'];
$nohp   = $p['nohp'];
$anggota = json_decode($p['data_anggota'], true);

//// PDF Kustom ////
class PDF extends FPDF {
    function NbLines($w, $txt) {
        $cw = &$this->CurrentFont['cw'];
        if ($w == 0) $w = $this->w - $this->rMargin - $this->x;
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', $txt);
        $nb = strlen($s);
        if ($nb > 0 and $s[$nb - 1] == "\n") $nb--;
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;
        while ($i < $nb) {
            $c = $s[$i];
            if ($c == "\n") {
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
                continue;
            }
            if ($c == ' ') $sep = $i;
            $l += $cw[$c];
            if ($l > $wmax) {
                if ($sep == -1) {
                    if ($i == $j) $i++;
                } else {
                    $i = $sep + 1;
                }
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
            } else {
                $i++;
            }
        }
        return $nl;
    }

    function RowMultiCell($data, $widths, $height) {
        $nb = 0;
        foreach ($data as $i => $txt) {
            $nb = max($nb, $this->NbLines($widths[$i], $txt));
        }
        $h = $height * $nb;
        $this->CheckPageBreak($h);

        for ($i = 0; $i < count($data); $i++) {
            $w = $widths[$i];
            $x = $this->GetX();
            $y = $this->GetY();
            $this->Rect($x, $y, $w, $h);
            $this->MultiCell($w, $height, $data[$i], 0, 'L');
            $this->SetXY($x + $w, $y);
        }
        $this->Ln($h);
    }

    function CheckPageBreak($h) {
        if ($this->GetY() + $h > $this->PageBreakTrigger)
            $this->AddPage($this->CurOrientation);
    }
}

// Inisialisasi PDF
$pdf = new PDF();
$pdf->AddPage();

// Judul
$pdf->SetFont('Arial','B',14);
$pdf->Cell(0,10,'FORMULIR PERMOHONAN',0,1,'C');

// Garis tebal
$pdf->SetDrawColor(0,0,0);
$pdf->SetLineWidth(1.5);
$pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
$pdf->Ln(5);

// Subjudul
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

// Pernyataan
$text = "Dengan ini mengajukan permohonan cetak Surat Keterangan Terdaftar Data Tunggal Sosial Ekonomi Nasional (DTSEN), sesuai berkas salinan Kartu Keluarga yang saya sertakan atas nama sebagai berikut:";
$pdf->MultiCell(0,6,$text);
$pdf->Ln(3);

// Header Tabel
$pdf->SetDrawColor(0,0,0);
$pdf->SetLineWidth(0.3);
$pdf->SetFont('Arial','B',11);
$header = ['No', 'Nama', 'NIK', 'Alamat', 'Keperluan'];
$widths = [10, 40, 40, 55, 45];

foreach ($header as $i => $col) {
    $pdf->Cell($widths[$i], 8, $col, 1, 0, 'C');
}
$pdf->Ln();

// Isi Tabel
$pdf->SetFont('Arial','',11);
if (is_array($anggota)) {
    foreach ($anggota as $i => $a) {
        $pdf->RowMultiCell([
            $i + 1,
            $a['nama'],
            $a['nik'],
            $a['alamat'],
            $a['keperluan']
        ], $widths, 6);
    }
}

$pdf->Ln(10);
$pdf->MultiCell(0,6,"Demikian surat permohonan ini saya ajukan. Saya bersedia menjaga dan bertanggung jawab terhadap keamanan data dan menghindari penggunaan data oleh pihak yang tidak berkepentingan.");

// Tanggal dan tanda tangan
$pdf->Ln(10);
$bulanIndo = [1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
$pdf->Cell(0,6,'Tanjungpinang, '.date('d').' '.$bulanIndo[(int)date('m')].' '.date('Y'),0,1,'R');
$pdf->Ln(15);
$pdf->Cell(0,6,'('.$nama.')',0,1,'R'); // Nama otomatis

// Output ke browser
$pdf->Output('I', 'Formulir_DTSEN_'.$nik.'.pdf');
?>
