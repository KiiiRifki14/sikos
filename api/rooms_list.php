<?php
require '../inc/koneksi.php';
$res = $mysqli->query("SELECT * FROM kamar WHERE status_kamar='TERSEDIA'");
$out=[]; while($row=$res->fetch_assoc()) $out[]=$row;
header('Content-type:application/json'); echo json_encode($out);
?>