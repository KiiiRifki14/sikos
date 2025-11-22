<?php
require '../inc/koneksi.php';
require '../inc/guard.php';
session_start();
if (!is_admin()) { die('Forbidden'); }
$res = $mysqli->query("SELECT p.*, g.nama, g.email, g.no_hp FROM penghuni p INNER JOIN pengguna g ON p.id_pengguna=g.id_pengguna");
echo "<h2>Data Penghuni</h2>
<table border=1 cellpadding=4>
<tr><th>Nama</th><th>Email</th><th>No HP</th><th>Alamat</th><th>Pekerjaan</th><th>Emergency</th><th>Aksi</th></tr>";
while ($row = $res->fetch_assoc()) {
  echo "<tr>
    <td>{$row['nama']}</td><td>{$row['email']}</td><td>{$row['no_hp']}</td>
    <td>{$row['alamat']}</td><td>{$row['pekerjaan']}</td>
    <td>{$row['emergency_cp']}</td>
    <td>
      <a href='penghuni_edit.php?id={$row['id_penghuni']}'>Edit</a>
    </td>
  </tr>";
}
echo "</table>";
?>
</br><a href="index.php" class="button">Kembali ke Dashboard</a>
