<?php
session_start();
require '../inc/koneksi.php';
require '../inc/guard.php';
if (!is_admin() && !is_owner()) die('Forbidden');
?>
<!DOCTYPE html>
<html>
<head>
  <title>Data Kamar - SIKOS Admin</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="../assets/css/app.css"/>
</head>
<body>
<div class="container">
  <h2>Data Kamar</h2>
  <table>
    <tr>
      <th>Kode</th><th>Tipe</th><th>Lantai</th><th>Luas</th><th>Harga</th><th>Status</th><th>Aksi</th>
    </tr>
    <?php
    $res = $mysqli->query("SELECT k.*, t.nama_tipe FROM kamar k JOIN tipe_kamar t ON k.id_tipe=t.id_tipe ORDER BY k.kode_kamar ASC");
    while($row = $res->fetch_assoc()){
      echo "<tr>
        <td>{$row['kode_kamar']}</td>
        <td>{$row['nama_tipe']}</td>
        <td>{$row['lantai']}</td>
        <td>{$row['luas_m2']}</td>
        <td>Rp".number_format($row['harga'],0,',','.')."</td>
        <td>{$row['status_kamar']}</td>
        <td>
          <a href='kamar_edit.php?id={$row['id_kamar']}'>Edit</a> |
          <a href='kamar_proses.php?act=hapus&id={$row['id_kamar']}' onclick=\"return confirm('Hapus kamar?')\">Hapus</a>
        </td>
      </tr>";
    }
    ?>
  </table>
  <a href="kamar_tambah.php" class="button">Tambah Kamar</a>
  <a href="index.php" class="button">Kembali ke Dashboard</a>
</div>
</body>
</html>