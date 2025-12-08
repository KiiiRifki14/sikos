<?php
require 'inc/koneksi.php';
$db = new Database();

echo "<h1>Debug Kamar Duplikat</h1>";

// 1. Cek Duplikat di Tabel Kamar
echo "<h2>1. Cek Duplikat Kode Kamar</h2>";
$res = $db->koneksi->query("SELECT kode_kamar, COUNT(*) as c FROM kamar GROUP BY kode_kamar HAVING c > 1");
if ($res->num_rows > 0) {
    while($row = $res->fetch_assoc()) {
        echo "DUPLIKAT: " . $row['kode_kamar'] . " (Jumlah: " . $row['c'] . ")<br>";
    }
} else {
    echo "Tidak ada duplikat kode kamar.<br>";
}

// 2. Cek Join Result
echo "<h2>2. Cek Query Index (6 Kamar Pertama)</h2>";
$sql = "SELECT k.id_kamar, k.kode_kamar, k.status_kamar, t.nama_tipe 
        FROM kamar k 
        JOIN tipe_kamar t ON k.id_tipe=t.id_tipe
        WHERE k.id_kamar NOT IN (SELECT id_kamar FROM booking WHERE status='PENDING')
        ORDER BY k.status_kamar ASC, k.kode_kamar ASC
        LIMIT 10"; // Cek 10 biar kelihatan
$res = $db->koneksi->query($sql);
echo "<table border='1'><tr><th>ID</th><th>Kode</th><th>Status</th><th>Tipe</th></tr>";
while($row = $res->fetch_assoc()) {
    echo "<tr>";
    echo "<td>{$row['id_kamar']}</td>";
    echo "<td>{$row['kode_kamar']}</td>";
    echo "<td>{$row['status_kamar']}</td>";
    echo "<td>{$row['nama_tipe']}</td>";
    echo "</tr>";
}
echo "</table>";

// 3. Cek Status A03 (yang dilaporkan hilang)
echo "<h2>3. Status Kamar A03</h2>";
$res = $db->koneksi->query("SELECT * FROM kamar WHERE kode_kamar='A03'");
$kamarTarget = $res->fetch_assoc();
if ($kamarTarget) {
    echo "ID: " . $kamarTarget['id_kamar'] . "<br>";
    echo "Status: " . $kamarTarget['status_kamar'] . "<br>";
    
    // Cek Booking Pending
    $res2 = $db->koneksi->query("SELECT * FROM booking WHERE id_kamar=" . $kamarTarget['id_kamar'] . " AND status='PENDING'");
    if ($res2->num_rows > 0) {
        $b = $res2->fetch_assoc();
        echo "⚠️ Ada Booking Pending ID: " . $b['id_booking'] . " oleh User ID: " . $b['id_pengguna'] . " (Ini penyebab kamar tidak muncul di landing page)<br>";
    } else {
        echo "Tidak ada booking pending.<br>";
    }
} else {
    echo "Kamar A03 tidak ditemukan.<br>";
}
?>
