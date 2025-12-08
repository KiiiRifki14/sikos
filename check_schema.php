<?php
require 'inc/koneksi.php';
$db = new Database();

$res = $db->koneksi->query("DESCRIBE pembayaran");
echo "<pre>";
while($row = $res->fetch_assoc()) {
    print_r($row);
}
echo "</pre>";
?>
