<?php
require 'inc/koneksi.php';
$res = $mysqli->query("DESCRIBE pengaturan");
while($row = $res->fetch_assoc()) {
    print_r($row);
}
?>
