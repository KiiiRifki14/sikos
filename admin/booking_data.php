<?php
session_start();
require '../inc/koneksi.php';
require '../inc/guard.php';
if (!is_admin() && !is_owner()) die('Forbidden');
echo "<h2>Data Booking</h2>";
$res = $mysqli->query("SELECT b.*, g.nama, k.kode_kamar FROM booking b 
  JOIN pengguna g ON b.id_pengguna=g.id_pengguna
  JOIN kamar k ON b.id_kamar=k.id_kamar
  ORDER BY b.tanggal_booking DESC");
echo "<table border=1>
<tr><th>Nama</th><th>Kode Kamar</th><th>Check-in</th><th>Durasi</th><th>Status</th><th>KTP</th><th>Aksi</th></tr>";
while($row=$res->fetch_assoc()){
  echo "<tr>
    <td>{$row['nama']}</td>
    <td>{$row['kode_kamar']}</td>
    <td>{$row['checkin_rencana']}</td>
    <td>{$row['durasi_bulan_rencana']} bulan</td>
    <td>{$row['status']}</td>
    <td>".($row['ktp_path_opt']?"<a href='../assets/uploads/ktp/{$row['ktp_path_opt']}'>View</a>":"-")."</td>
    <td>";
    if($row['status']=='PENDING'){
      echo "<a href='booking_proses.php?act=approve&id_booking={$row['id_booking']}'>Approve</a> | 
            <a href='booking_proses.php?act=batal&id_booking={$row['id_booking']}' onclick=\"return confirm('Yakin batal?')\">Batal</a>";
    } else {
      echo "-";
    }
  echo "</td></tr>";
}
echo "</table>";
?>
</br><a href="index.php" class="button">Kembali ke Dashboard</a>