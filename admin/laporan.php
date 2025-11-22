<?php
require '../inc/koneksi.php';
session_start();
if ($_POST['act']=='generate') {
    $id_kontrak = intval($_POST['id_kontrak']);
    $bulan = $_POST['bulan_tagih'];
    $stmt = $mysqli->prepare("SELECT 1 FROM tagihan WHERE id_kontrak=? AND bulan_tagih=?");
    $stmt->bind_param('is', $id_kontrak, $bulan);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows==0) {
        $row = $mysqli->query("SELECT harga FROM kamar INNER JOIN kontrak ON kamar.id_kamar=kontrak.id_kamar WHERE kontrak.id_kontrak=$id_kontrak")->fetch_assoc();
        $stmt2 = $mysqli->prepare("INSERT INTO tagihan (id_kontrak, bulan_tagih, nominal, jatuh_tempo) VALUES (?, ?, ?, DATE_ADD(CURDATE(), INTERVAL 10 DAY))");
        $stmt2->bind_param('isi', $id_kontrak, $bulan, $row['harga']);
        $stmt2->execute();
        echo "Tagihan sukses!";
    } else {
        echo "Tagihan bulan sudah ada!";
    }
}
?>
<form method="post">
    <select name="id_kontrak">
        <!-- TODO: list kontrak -->
    </select>
    <input type="month" name="bulan_tagih">
    <input type="hidden" name="act" value="generate">
    <button type="submit">Generate Tagihan</button>
</form>