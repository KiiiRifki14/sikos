<?php
session_start();
require 'inc/koneksi.php';
require 'inc/csrf.php';

if (!isset($_SESSION['id_pengguna'])) die('Login dulu!');

// Proses kirim keluhan baru
if ($_SERVER['REQUEST_METHOD']=='POST' && csrf_check($_POST['csrf'])) {
    $judul = htmlspecialchars($_POST['judul']);
    $desk = htmlspecialchars($_POST['deskripsi']);
    $prioritas = $_POST['prioritas'];
    // Cek id_penghuni valid
    $row_penghuni = $mysqli->query("SELECT id_penghuni FROM penghuni WHERE id_pengguna={$_SESSION['id_pengguna']}")->fetch_assoc();
    if (!$row_penghuni) {
        echo "<div class='container'><h2>Keluhan Saya</h2>Anda belum terdaftar sebagai penghuni.</div>";
        exit;
    }
    $id_penghuni = $row_penghuni['id_penghuni'];
    $stmt = $mysqli->prepare("INSERT INTO keluhan (id_penghuni, judul, deskripsi, prioritas, status) VALUES (?, ?, ?, ?, 'BARU')");
    $stmt->bind_param('isss', $id_penghuni, $judul, $desk, $prioritas);
    $stmt->execute();
    echo "Keluhan sukses dikirim.<hr>";
}

// Cek id_penghuni valid sebelum query keluhan
$row_penghuni = $mysqli->query("SELECT id_penghuni FROM penghuni WHERE id_pengguna={$_SESSION['id_pengguna']}")->fetch_assoc();
if (!$row_penghuni) {
    echo "<div class='container'><h2>Keluhan Saya</h2>Anda belum terdaftar sebagai penghuni.<br><a href='penghuni_dashboard.php'>Kembali</a></div>";
    exit;
}
$id_penghuni = $row_penghuni['id_penghuni'];

// Form input keluhan
echo '<form method="post">
    <input type="hidden" name="csrf" value="'.csrf_token().'">
    Judul: <input name="judul" required><br>
    Deskripsi: <textarea name="deskripsi"></textarea><br>
    Prioritas: <select name="prioritas"><option>LOW</option><option>MEDIUM</option><option>HIGH</option></select><br>
    <button type="submit">Kirim Keluhan</button></form>';

// Query keluhan milik user
$res = $mysqli->query("SELECT * FROM keluhan WHERE id_penghuni=$id_penghuni ORDER BY dibuat_at DESC");
echo "<table border=1><tr><th>Judul</th><th>Deskripsi</th><th>Status</th><th>Prioritas</th></tr>";
while($row=$res->fetch_assoc()){ echo "<tr>
<td>".htmlspecialchars($row['judul'])."</td>
<td>".htmlspecialchars($row['deskripsi'])."</td>
<td>{$row['status']}</td>
<td>{$row['prioritas']}</td>
</tr>";}
echo "</table>";
?>