<?php
session_start();
require 'inc/koneksi.php';
$today = date('Y-m-d');

// Pakai prepared statement:
$stmt = $mysqli->prepare("SELECT * FROM pengumuman WHERE is_aktif=1 AND aktif_mulai<=? AND aktif_selesai>=?");
$stmt->bind_param('ss', $today, $today);
$stmt->execute();
$res = $stmt->get_result();

while ($row = $res->fetch_assoc()) {
    echo "<h3>".htmlspecialchars($row['judul'])."</h3>";
    echo "<p>".htmlspecialchars($row['isi'])."</p><hr>";
}
?>