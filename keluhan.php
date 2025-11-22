<?php
require 'inc/koneksi.php';
require 'inc/csrf.php';
session_start();
if (!isset($_SESSION['id_pengguna'])) die('Login dulu!');
if ($_SERVER['REQUEST_METHOD']=='POST' && csrf_check($_POST['csrf'])) {
    $judul = htmlspecialchars($_POST['judul']);
    $desk = htmlspecialchars($_POST['deskripsi']);
    $stmt = $mysqli->prepare("INSERT INTO keluhan (id_penghuni, judul, deskripsi, prioritas, status) VALUES ((SELECT id_penghuni FROM penghuni WHERE id_pengguna=?),?,?,?,?)");
    $stmt->bind_param('issss', $_SESSION['id_pengguna'], $judul, $desk, $_POST['prioritas'], 'BARU');
    $stmt->execute();
    header('Location: keluhan.php?success=1');
}
?>
<form method="post">
    <input type="hidden" name="csrf" value="<?php echo csrf_token(); ?>">
    <input type="text" name="judul" placeholder="Judul keluhan" required>
    <textarea name="deskripsi" placeholder="Deskripsi"></textarea>
    <select name="prioritas">
        <option value="LOW">Rendah</option>
        <option value="MEDIUM">Sedang</option>
        <option value="HIGH">Tinggi</option>
    </select>
    <button type="submit">Kirim Keluhan</button>
</form>