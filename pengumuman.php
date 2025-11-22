<?php
require 'inc/koneksi.php';
$today = date('Y-m-d');
$res = $mysqli->query("SELECT * FROM pengumuman WHERE is_aktif=1 AND aktif_mulai<=? AND aktif_selesai>=?", [$today, $today]);
while ($row = $res->fetch_assoc()) {
    echo "<h3>".htmlspecialchars($row['judul'])."</h3>";
    echo "<p>".htmlspecialchars($row['isi'])."</p>";
}
?>