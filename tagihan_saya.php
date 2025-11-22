<?php
require 'inc/koneksi.php';
session_start();
if (!isset($_SESSION['id_pengguna'])) die('Login dulu!');
$id_pengguna = $_SESSION['id_pengguna'];
// Kontrak aktif
$kontrak = $mysqli->query("SELECT id_kontrak FROM kontrak WHERE id_penghuni=(SELECT id_penghuni FROM penghuni WHERE id_pengguna=$id_pengguna) AND status='AKTIF'")->fetch_assoc();
if(!$kontrak){ echo "Belum ada kontrak aktif."; exit; }
$id_kontrak = $kontrak['id_kontrak'];
$res = $mysqli->query("SELECT * FROM tagihan WHERE id_kontrak=$id_kontrak ORDER BY bulan_tagih");
echo "<h2>Tagihan Bulanan</h2>
<table border=1>
<tr><th>Bulan</th><th>Nominal</th><th>Status</th><th>Aksi</th></tr>";
while($row=$res->fetch_assoc()){
  echo "<tr>
    <td>{$row['bulan_tagih']}</td>
    <td>Rp".number_format($row['nominal'],0,',','.')."</td>
    <td>{$row['status']}</td>
    <td>";
    if($row['status']=='BELUM'){
      echo "<form method='post' action='pembayaran_tagihan.php' enctype='multipart/form-data'>
        <input type='hidden' name='id_tagihan' value='{$row['id_tagihan']}'>
        <input type='file' name='bukti' required>
        <input type='number' name='jumlah' value='{$row['nominal']}' required readonly>
        <button type='submit'>Upload Bukti</button></form>";
    } else echo "-";
  echo "</td></tr>";
}
echo "</table>";
?>