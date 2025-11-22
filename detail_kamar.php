<?php
session_start();
require 'inc/koneksi.php';

// Ambil id_kamar dari GET
$id_kamar = intval($_GET['id'] ?? 0);
if ($id_kamar <= 0) die("Kamar tidak ditemukan!");

// Ambil detail kamar
$stmt = $mysqli->prepare("SELECT k.*, t.nama_tipe FROM kamar k JOIN tipe_kamar t ON k.id_tipe=t.id_tipe WHERE k.id_kamar=?");
$stmt->bind_param('i', $id_kamar);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
if (!$row) die("Kamar tidak ditemukan!");

// Foto-foto
$foto = [];
$resf = $mysqli->query("SELECT file_nama FROM kamar_foto WHERE id_kamar=$id_kamar");
while($f=$resf->fetch_assoc()){ $foto[] = $f['file_nama']; }
?>
<!DOCTYPE html>
<html>
<head>
    <title>Detail Kamar <?= htmlspecialchars($row['kode_kamar']) ?></title>
    <link rel="stylesheet" href="assets/css/app.css"/>
</head>
<body>
<div class="container">
    <h2>Detail Kamar <?= htmlspecialchars($row['kode_kamar']) ?></h2>
    <table border=1 cellpadding=5>
        <tr><th>Kode</th><td><?= htmlspecialchars($row['kode_kamar']) ?></td></tr>
        <tr><th>Tipe</th><td><?= htmlspecialchars($row['nama_tipe']) ?></td></tr>
        <tr><th>Lantai</th><td><?= htmlspecialchars($row['lantai']) ?></td></tr>
        <tr><th>Luas</th><td><?= $row['luas_m2'] ?> m2</td></tr>
        <tr><th>Harga</th><td>Rp<?= number_format($row['harga'],0,',','.') ?></td></tr>
        <tr><th>Status</th><td><?= $row['status_kamar'] ?></td></tr>
        <tr><th>Catatan</th><td><?= htmlspecialchars($row['catatan']) ?></td></tr>
        <tr><th>Foto Kamar</th><td>
        <?php
        if(count($foto)>0){
            foreach($foto as $fn){
                echo "<img src='assets/uploads/kamar/$fn' width='180' style='margin:4px'>";
            }
        }else if($row['foto_cover']){
            echo "<img src='assets/uploads/kamar/{$row['foto_cover']}' width='180'>";
        }else{
            echo "Belum ada foto";
        }
        ?>
        </td></tr>
    </table>
    <br>
    <?php if($row['status_kamar']=='TERSEDIA'){ ?>
    <form method="post" action="goto_booking.php">
      <input type="hidden" name="id_kamar" value="<?= $id_kamar ?>">
      <button type="submit">Booking Kamar Ini</button>
    </form>
    <?php } else { ?>
        <div style="color:red;font-weight:bold;">Kamar sudah terisi.</div>
    <?php } ?>
        <br>
    <a href="index.php" class="button">Kembali</a>
</div>
</body>
</html>