<?php
session_start();
require 'inc/koneksi.php';
?>
<!DOCTYPE html>
<html>
<head>
  <title>SIKOS Paadaasih - Kost Online Cimahi</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="assets/css/app.css"/>
</head>
<body>
<div class="container">
  <h1>SIKOS Paadaasih</h1>
  <p>Selamat datang di portal kost online Cimahi!</p>
  <table>
    <tr>
      <th>Kode</th><th>Tipe</th><th>Lantai</th><th>Harga</th><th>Status</th><th></th>
    </tr>
    <?php
    // Loop data kamar
    $res = $mysqli->query("SELECT k.*, t.nama_tipe FROM kamar k JOIN tipe_kamar t ON k.id_tipe=t.id_tipe ORDER BY k.kode_kamar ASC");
    while($row = $res->fetch_assoc()){
      echo "<tr>
        <td>{$row['kode_kamar']}</td>
        <td>{$row['nama_tipe']}</td>
        <td>{$row['lantai']}</td>
        <td>Rp ".number_format($row['harga'],0,',','.')."</td>
        <td>{$row['status_kamar']}</td>
        <td><a href='detail_kamar.php?id={$row['id_kamar']}' class='button'>Detail</a></td>
      </tr>";
    }
    ?>
  </table>
  <a href="login.php" class="button">Login Penghuni/Admin</a>
</div>
</body>
</html>