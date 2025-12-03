<?php
session_start();
require '../inc/koneksi.php';
require '../inc/guard.php';

// Validasi Admin
if (!is_admin() && !is_owner()) { header("Location: ../login.php"); exit; }

$id = intval($_GET['id'] ?? 0); // ID Penghuni

// Ambil Data Penghuni & Kontrak Aktif (Join 4 Tabel: Penghuni, User, Kontrak, Kamar)
$sql = "SELECT p.*, u.nama, u.no_hp, u.email,
               k.kode_kamar, k.harga, k.luas_m2,
               ko.tanggal_mulai, ko.tanggal_selesai, ko.durasi_bulan
        FROM penghuni p
        JOIN pengguna u ON p.id_pengguna = u.id_pengguna
        JOIN kontrak ko ON p.id_penghuni = ko.id_penghuni
        JOIN kamar k ON ko.id_kamar = k.id_kamar
        WHERE p.id_penghuni = $id AND ko.status = 'AKTIF'";

$res = $mysqli->query($sql);
$data = $res->fetch_assoc();

if(!$data) { 
    echo "<center><h3>⛔ Data kontrak aktif tidak ditemukan.</h3><p>Pastikan penghuni ini memiliki status sewa AKTIF.</p></center>"; 
    exit; 
}

// Load Settings Owner untuk Kop Surat
$app = json_decode(file_get_contents('../inc/settings_data.json'), true);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Kontrak Sewa - <?= htmlspecialchars($data['nama']) ?></title>
    <style>
        /* Styling Kertas A4 Formal */
        body { 
            font-family: 'Times New Roman', serif; 
            line-height: 1.6; 
            padding: 40px 60px; 
            max-width: 800px; 
            margin: auto; 
            color: #000;
        }
        h1, h2, h3, h4 { text-align: center; text-transform: uppercase; margin: 10px 0; }
        h2 { font-size: 18px; text-decoration: underline; }
        
        .header-nomor { text-align: center; font-size: 14px; margin-top: -10px; margin-bottom: 30px; }
        .content { margin-top: 20px; text-align: justify; font-size: 14px; }
        
        /* Layout Data Pihak */
        .pihak-box { margin-bottom: 20px; padding-left: 20px; }
        .row { display: flex; margin-bottom: 4px; }
        .label { width: 140px; font-weight: bold; }
        
        /* Pasal-pasal */
        .pasal-title { font-weight: bold; margin-top: 15px; margin-bottom: 5px; }
        ol { margin: 0; padding-left: 20px; }
        li { margin-bottom: 5px; }

        /* Area Tanda Tangan */
        .ttd-area { margin-top: 60px; display: flex; justify-content: space-between; }
        .ttd-box { text-align: center; width: 40%; }
        .ttd-line { margin-top: 80px; border-bottom: 1px solid black; font-weight: bold; }

        /* Tombol Print (Hilang saat diprint) */
        @media print {
            .no-print { display: none; }
            body { padding: 0; margin: 20mm; }
        }
        .btn { padding: 10px 20px; border-radius: 5px; border: none; cursor: pointer; font-weight: bold; color: white; }
        .btn-print { background: #2563eb; }
        .btn-close { background: #64748b; margin-left: 10px; }
    </style>
</head>
<body>
    <div class="no-print" style="text-align:right; margin-bottom:20px; border-bottom:1px solid #eee; padding-bottom:20px;">
         <button onclick="window.print()" class="btn btn-print">🖨️ Cetak / Simpan PDF</button>
         <button onclick="window.close()" class="btn btn-close">Tutup</button>
    </div>

    <h2>SURAT PERJANJIAN SEWA KOST</h2>
    <p class="header-nomor">Nomor: <?= date('Y') ?>/KOST/<?= $data['kode_kamar'] ?>/<?= $data['id_penghuni'] ?></p>

    <div class="content">
        <p>Pada hari ini, <strong><?= date('l, d F Y') ?></strong>, bertempat di <?= $app['alamat'] ?>, kami yang bertanda tangan di bawah ini:</p>

        <div class="pihak-box">
            <strong>1. PIHAK PERTAMA (PEMILIK)</strong>
            <div class="row"><span class="label">Nama</span>: <?= $app['pemilik'] ?></div>
            <div class="row"><span class="label">Alamat</span>: <?= $app['alamat'] ?></div>
            <div class="row"><span class="label">No. HP</span>: <?= $app['no_hp'] ?></div>
            <div class="row"><span class="label">Bertindak sebagai</span>: Pemilik Kost</div>
        </div>

        <div class="pihak-box">
            <strong>2. PIHAK KEDUA (PENYEWA)</strong>
            <div class="row"><span class="label">Nama</span>: <?= $data['nama'] ?></div>
            <div class="row"><span class="label">No. HP</span>: <?= $data['no_hp'] ?></div>
            <div class="row"><span class="label">Pekerjaan</span>: <?= htmlspecialchars($data['pekerjaan'] ?? '-') ?></div>
            <div class="row"><span class="label">Alamat Asal</span>: <?= htmlspecialchars($data['alamat'] ?? '-') ?></div>
            <div class="row"><span class="label">Bertindak sebagai</span>: Penyewa Kamar</div>
        </div>

        <p>Kedua belah pihak sepakat untuk mengadakan perjanjian sewa menyewa kamar kost dengan ketentuan dan syarat-syarat sebagai berikut:</p>

        <div class="pasal-title">PASAL 1: OBJEK SEWA</div>
        <p>Pihak Pertama menyewakan sebuah kamar kost dengan <strong>Kode Kamar <?= $data['kode_kamar'] ?></strong> (Luas <?= $data['luas_m2'] ?>m²) yang berlokasi di alamat Pihak Pertama kepada Pihak Kedua.</p>

        <div class="pasal-title">PASAL 2: JANGKA WAKTU</div>
        <p>Sewa menyewa ini dilangsungkan untuk jangka waktu <strong><?= $data['durasi_bulan'] ?> Bulan</strong>, terhitung mulai tanggal <strong><?= date('d F Y', strtotime($data['tanggal_mulai'])) ?></strong> sampai dengan tanggal <strong><?= date('d F Y', strtotime($data['tanggal_selesai'])) ?></strong>.</p>

        <div class="pasal-title">PASAL 3: HARGA DAN PEMBAYARAN</div>
        <p>Harga sewa kamar ditetapkan sebesar <strong>Rp <?= number_format($data['harga'], 0, ',', '.') ?> (Terbilang: <i><?= number_format($data['harga']) ?> Rupiah</i>) per bulan</strong>. Pembayaran wajib dilakukan paling lambat setiap tanggal <?= date('d', strtotime($data['tanggal_mulai'])) ?> pada bulan berjalan melalui transfer ke rekening <strong><?= $app['rek_bank'] ?></strong>.</p>

        <div class="pasal-title">PASAL 4: TATA TERTIB & LARANGAN</div>
        <ol>
            <li>Penyewa wajib menjaga kebersihan, keamanan, dan ketertiban lingkungan kost.</li>
            <li>Dilarang keras membawa, menyimpan, atau menggunakan narkoba, minuman keras, dan senjata tajam.</li>
            <li>Dilarang membawa tamu lawan jenis ke dalam kamar (dilarang menginap).</li>
            <li>Dilarang memelihara hewan yang dapat mengganggu penghuni lain.</li>
            <li>Kerusakan fasilitas kamar (AC, Kasur, Lemari) akibat kelalaian Penyewa menjadi tanggung jawab penuh Penyewa.</li>
        </ol>

        <div class="pasal-title">PASAL 5: PEMUTUSAN KONTRAK</div>
        <p>Apabila Pihak Kedua melanggar Pasal 4, Pihak Pertama berhak memutuskan perjanjian ini secara sepihak tanpa pengembalian sisa uang sewa.</p>

        <p>Demikian surat perjanjian ini dibuat rangkap 2 (dua) bermeterai cukup dan memiliki kekuatan hukum yang sama.</p>
    </div>

    <div class="ttd-area">
        <div class="ttd-box">
            <p>Pihak Pertama<br>(Pemilik Kost)</p>
            <div class="ttd-line"><?= $app['pemilik'] ?></div>
        </div>
        <div class="ttd-box">
            <p>Pihak Kedua<br>(Penyewa)</p>
            <div class="ttd-line"><?= $data['nama'] ?></div>
        </div>
    </div>
</body>
</html>