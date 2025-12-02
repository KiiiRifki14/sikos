<?php
session_start();
require 'inc/koneksi.php';
require 'inc/utils.php'; 

// Cek Login Dasar
if (!isset($_SESSION['id_pengguna'])) {
    // Karena ini file cetak, kalau belum login langsung tutup atau redirect login
    echo "<script>alert('Anda harus login!'); window.close();</script>";
    exit;
}

$id_pembayaran = intval($_GET['id'] ?? 0);
$url_redirect = ($_SESSION['peran'] == 'PENGHUNI') ? 'tagihan_saya.php' : 'admin/keuangan_index.php';

// Validasi ID
if ($id_pembayaran == 0) pesan_error($url_redirect, "ID Pembayaran tidak valid.");

// 1. Ambil Data Pembayaran Dasar
$q = "SELECT * FROM pembayaran WHERE id_pembayaran = $id_pembayaran AND status = 'DITERIMA'";
$res = $mysqli->query($q);
$pembayaran = $res->fetch_assoc();

if (!$pembayaran) pesan_error($url_redirect, "Data pembayaran tidak ditemukan atau belum diverifikasi.");

// 2. Logika Detil Berdasarkan Tipe (Booking / Tagihan)
$judul = "";
$keterangan = "";
$nama_penghuni = "";
$kamar_kode = "";
$id_user_terkait = 0;

if ($pembayaran['ref_type'] == 'TAGIHAN') {
    // Ambil info dari Tagihan -> Kontrak -> Penghuni
    $q_tag = "SELECT t.*, k.kode_kamar, u.nama, u.id_pengguna 
              FROM tagihan t
              JOIN kontrak ko ON t.id_kontrak = ko.id_kontrak
              JOIN kamar k ON ko.id_kamar = k.id_kamar
              JOIN penghuni ph ON ko.id_penghuni = ph.id_penghuni
              JOIN pengguna u ON ph.id_pengguna = u.id_pengguna
              WHERE t.id_tagihan = {$pembayaran['ref_id']}";
    $d_tag = $mysqli->query($q_tag)->fetch_assoc();
    
    if($d_tag) {
        $judul = "PEMBAYARAN SEWA KOST";
        $nama_bulan = date('F Y', strtotime($d_tag['bulan_tagih']));
        $keterangan = "Pembayaran tagihan sewa kamar bulan $nama_bulan";
        $nama_penghuni = $d_tag['nama'];
        $kamar_kode = $d_tag['kode_kamar'];
        $id_user_terkait = $d_tag['id_pengguna'];
    }

} elseif ($pembayaran['ref_type'] == 'BOOKING') {
    // Ambil info dari Booking
    $q_book = "SELECT b.*, k.kode_kamar, u.nama, u.id_pengguna
               FROM booking b
               JOIN kamar k ON b.id_kamar = k.id_kamar
               JOIN pengguna u ON b.id_pengguna = u.id_pengguna
               WHERE b.id_booking = {$pembayaran['ref_id']}";
    $d_book = $mysqli->query($q_book)->fetch_assoc();

    if($d_book) {
        $judul = "BOOKING FEE / DP";
        $keterangan = "Pembayaran uang muka (DP) booking kamar";
        $nama_penghuni = $d_book['nama'];
        $kamar_kode = $d_book['kode_kamar'];
        $id_user_terkait = $d_book['id_pengguna'];
    }
} elseif ($pembayaran['metode'] == 'CASH') {
    // Handle manual cash payment logic if ref missing (fallback)
    $judul = "PEMBAYARAN TUNAI";
}

// 3. Validasi Keamanan: Hanya Admin atau Pemilik Data yang boleh lihat
$is_admin = (isset($_SESSION['peran']) && ($_SESSION['peran']=='ADMIN' || $_SESSION['peran']=='OWNER'));
$is_owner_data = ($_SESSION['id_pengguna'] == $id_user_terkait);

if (!$is_admin && !$is_owner_data) {
    pesan_error("index.php", "Anda tidak memiliki hak akses untuk kuitansi ini.");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Kuitansi #<?= $id_pembayaran ?></title>
    <style>
        body { font-family: 'Courier New', Courier, monospace; background: #f0f0f0; padding: 40px; }
        .kuitansi {
            max-width: 700px; margin: 0 auto; background: #fff; padding: 40px;
            border: 1px solid #ccc; box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            position: relative;
        }
        .header { text-align: center; border-bottom: 2px dashed #333; padding-bottom: 20px; margin-bottom: 30px; }
        .header h2 { margin: 0; color: #2563eb; }
        .header p { margin: 5px 0 0; font-size: 14px; color: #666; }
        
        .row { display: flex; margin-bottom: 15px; }
        .label { width: 180px; font-weight: bold; color: #555; }
        .value { flex: 1; color: #000; font-weight: 600; }
        
        .amount-box {
            margin-top: 30px; padding: 15px; background: #f8fafc;
            border: 2px solid #2563eb; color: #2563eb;
            font-size: 24px; font-weight: bold; text-align: center;
        }
        
        .footer { margin-top: 50px; text-align: right; }
        .ttd { height: 80px; }
        .stamp {
            position: absolute; bottom: 100px; right: 80px;
            border: 3px solid rgba(37, 99, 235, 0.3); color: rgba(37, 99, 235, 0.3);
            font-size: 40px; font-weight: bold; padding: 10px 20px;
            transform: rotate(-15deg); border-radius: 10px;
            text-transform: uppercase; pointer-events: none;
        }
        
        /* Tambahan styling untuk info check-in */
        .info-checkin {
            margin-top: 20px; border-top: 4px solid #2563eb; padding-top: 20px;
        }

        @media print {
            body { background: white; padding: 0; }
            .kuitansi { box-shadow: none; border: none; width: 100%; max-width: 100%; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="kuitansi">
        <div class="stamp">LUNAS</div>
        
        <div class="header">
            <h2>SIKOS PAADAASIH</h2>
            <p>Jl. Paadaasih No. 123, Cimahi, Jawa Barat</p>
            <p>KUITANSI PEMBAYARAN RESMI</p>
        </div>

        <div class="row">
            <span class="label">No. Kuitansi</span>
            <span class="value">: #INV-<?= str_pad($id_pembayaran, 5, '0', STR_PAD_LEFT) ?></span>
        </div>
        <div class="row">
            <span class="label">Tanggal Terima</span>
            <span class="value">: <?= date('d F Y', strtotime($pembayaran['waktu_verifikasi'])) ?></span>
        </div>
        <div class="row">
            <span class="label">Telah Terima Dari</span>
            <span class="value">: <?= htmlspecialchars($nama_penghuni) ?> (Kamar <?= $kamar_kode ?>)</span>
        </div>
        <div class="row">
            <span class="label">Untuk Pembayaran</span>
            <span class="value">: <?= $keterangan ?></span>
        </div>
        <div class="row">
            <span class="label">Metode Bayar</span>
            <span class="value">: <?= $pembayaran['metode'] ?></span>
        </div>

        <div class="amount-box">
            Rp <?= number_format($pembayaran['jumlah'], 0, ',', '.') ?>,-
        </div>

        <div class="footer">
            <p>Cimahi, <?= date('d F Y') ?></p>
            <div class="ttd"></div>
            <p><b>Admin Keuangan</b></p>
        </div>

        <?php if ($pembayaran['ref_type'] == 'BOOKING'): ?>
        <div class="info-checkin">
            <h3 style="margin-top:0; color:#2563eb;">ℹ️ INSTRUKSI CHECK-IN & PENGAMBILAN KUNCI</h3>
            <ol style="font-size: 14px; line-height: 1.6; color: #333; padding-left: 20px;">
                <li>Harap simpan bukti pembayaran (kuitansi) ini sebagai dokumen sah.</li>
                <li>Saat hari kedatangan (Check-in), silakan menuju ke <b>Pos Penjaga Kos</b> atau <b>Rumah Pengelola</b> (Ibu Kost) di Lantai 1.</li>
                <li>Tunjukkan Kuitansi ini dan KTP Asli untuk verifikasi data.</li>
                <li><b>Penyerahan Kunci Kamar</b> akan dilakukan setelah verifikasi selesai.</li>
                <li>Jika ada kendala saat kedatangan, hubungi pengelola di WhatsApp: <b>0812-3456-7890</b>.</li>
            </ol>
        </div>
        <?php endif; ?>
    </div>

    <div style="text-align:center; margin-top:20px;" class="no-print">
        <button onclick="window.print()" style="padding:10px 20px; background:#2563eb; color:white; border:none; border-radius:5px; cursor:pointer; font-weight:bold;">
            🖨️ Cetak Kuitansi
        </button>
        <button onclick="window.close()" style="padding:10px 20px; background:#64748b; color:white; border:none; border-radius:5px; cursor:pointer; margin-left:10px;">
            Tutup Tab
        </button>
    </div>

</body>
</html>