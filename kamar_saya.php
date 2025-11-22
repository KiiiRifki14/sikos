<?php
session_start();
require 'inc/koneksi.php';

if (!isset($_SESSION['id_pengguna']) || $_SESSION['peran'] != 'PENGHUNI') {
    header('Location: login.php');
    exit;
}

$id_pengguna = $_SESSION['id_pengguna'];
$row_penghuni = $mysqli->query("SELECT id_penghuni FROM penghuni WHERE id_pengguna=$id_pengguna")->fetch_assoc();

if (!$row_penghuni) {
    echo "<div class='container'><h2>Kamar Saya</h2>
    Belum ada data penghuni!
    <br><a href='penghuni_dashboard.php'>Kembali</a></div>";
    exit;
}

$id_penghuni = $row_penghuni['id_penghuni'];
$row_kontrak = $mysqli->query("SELECT * FROM kontrak WHERE id_penghuni=$id_penghuni AND status='AKTIF'")->fetch_assoc();

// JIKA BELUM ADA KONTRAK AKTIF / BELUM SEWA KAMAR
if (!$row_kontrak) {
    echo "<div class='container'>
    <h2>Kamar Saya</h2>
    <p>Anda belum punya kamar yang disewa.<br>Silakan lakukan <b>Booking Kamar</b> terlebih dahulu.</p>
    <a href='index.php' class='button'>Lihat Kamar & Booking</a>
    <br><br>
    <a href='penghuni_dashboard.php' class='button'>Kembali ke Dashboard</a>
    </div>";
    exit;
}

// Data kamar
$id_kamar = $row_kontrak['id_kamar'];
$row_kamar = $mysqli->query("SELECT k.*,t.nama_tipe FROM kamar k JOIN tipe_kamar t ON k.id_tipe=t.id_tipe WHERE k.id_kamar=$id_kamar")->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Kamar Saya - SIKOS Paadaasih</title>
    <link rel="stylesheet" href="assets/css/app.css"/>
</head>
<body>
<div class="container">
    <div class="breadcrumb"><a href="penghuni_dashboard.php">Dashboard</a> &raquo; Kamar Saya</div>
    <h2>Kamar Saya</h2>
    <table border=1 cellpadding=5>
        <tr><th>Kode Kamar</th><td><?= htmlspecialchars($row_kamar['kode_kamar']) ?></td></tr>
        <tr><th>Tipe Kamar</th><td><?= htmlspecialchars($row_kamar['nama_tipe']) ?></td></tr>
        <tr><th>Lantai</th><td><?= htmlspecialchars($row_kamar['lantai']) ?></td></tr>
        <tr><th>Luas</th><td><?= $row_kamar['luas_m2'] ?> m2</td></tr>
        <tr><th>Harga</th><td>Rp<?= number_format($row_kamar['harga']??$row_kamar['harga_default'],0,',','.') ?></td></tr>
        <tr><th>Status</th><td><?= $row_kamar['status_kamar'] ?></td></tr>
        <tr><th>Kontrak</th><td>
            Mulai: <?= $row_kontrak['tanggal_mulai'] ?><br>
            Selesai: <?= $row_kontrak['tanggal_selesai'] ?><br>
            Durasi: <?= $row_kontrak['durasi_bulan'] ?> Bulan<br>
            Deposit: Rp<?= number_format($row_kontrak['deposit'],0,',','.') ?><br>
            Status kontrak: <?= $row_kontrak['status'] ?>
        </td></tr>
        <?php if($row_kamar['foto_cover']){ ?>
        <tr><th>Foto Kamar</th><td><img src="assets/uploads/kamar/<?= htmlspecialchars($row_kamar['foto_cover']) ?>" width="220"></td></tr>
        <?php } ?>
        <tr><th>Catatan</th><td><?= htmlspecialchars($row_kamar['catatan']) ?></td></tr>
    </table>
    <br>
    <a href="penghuni_dashboard.php" class="button">Kembali ke Dashboard</a>
</div>
</body>
</html>