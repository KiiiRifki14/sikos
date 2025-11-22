<?php
require '../inc/koneksi.php';
require '../inc/guard.php';
session_start();
if (!is_admin()) die('Forbidden');
?>
<h2>Data Pengumuman</h2>
<a href="pengumuman_proses.php?act=tambah">[+] Tambah Pengumuman</a>
<table border=1>
<tr><th>Judul</th><th>Isi</th><th>Periode</th><th>Aktif</th><th>Audiens</th><th>Aksi</th></tr>
<?php
$res = $mysqli->query("SELECT * FROM pengumuman ORDER BY aktif_mulai DESC");
while($row=$res->fetch_assoc()){
  echo "<tr>
    <td>".htmlspecialchars($row['judul'])."</td>
    <td>".htmlspecialchars($row['isi'])."</td>
    <td>{$row['aktif_mulai']} s/d {$row['aktif_selesai']}</td>
    <td>".($row['is_aktif']?"Ya":"Tidak")."</td>
    <td>{$row['audiens']}</td>
    <td>
      <a href='pengumuman_proses.php?act=edit&id={$row['id_pengumuman']}'>Edit</a> |
      <a href='pengumuman_proses.php?act=hapus&id={$row['id_pengumuman']}' onclick=\"return confirm('Hapus?')\">Hapus</a>
    </td>
  </tr>";
}
?>
</table>
</br><a href="index.php" class="button">Kembali ke Dashboard</a>
