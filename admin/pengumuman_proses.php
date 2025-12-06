<?php
require '../inc/koneksi.php';
require '../inc/guard.php'; // Pastikan guard di-require di sini
session_start();
if (!is_admin()) die('Forbidden');

$db = new Database(); // Init DB

if ($_GET['act']=='tambah') {
  // Form tambah (Sebaiknya dipisah ke file view, tapi biarkan dulu)
  ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <title>Tambah Pengumuman</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="../assets/css/app.css"/>
    </head>
    <body style="background:#f1f5f9; padding:20px;">
        <div class="card-white" style="max-width:600px; margin:0 auto;">
            <h2 class="font-bold text-lg mb-4">Tambah Pengumuman</h2>
            <form method="post" action="pengumuman_proses.php?act=simpan">
            <div class="mb-4">
                <label class="form-label">Judul</label>
                <input name="judul" class="form-input w-full" required>
            </div>
            <div class="mb-4">
                <label class="form-label">Isi</label>
                <textarea name="isi" class="form-input w-full" rows="4" required></textarea>
            </div>
            <div class="mb-4">
                <label class="form-label">Audiens</label>
                <select name="audiens" class="form-input w-full">
                    <option value="ALL">Semua</option>
                    <option value="PENGHUNI">Penghuni</option>
                </select>
            </div>
            <div class="flex gap-4 mb-4">
                <div class="w-full">
                    <label class="form-label">Mulai</label>
                    <input type="date" name="aktif_mulai" class="form-input w-full" value="<?= date('Y-m-d') ?>">
                </div>
                <div class="w-full">
                    <label class="form-label">Selesai</label>
                    <input type="date" name="aktif_selesai" class="form-input w-full" value="<?= date('Y-m-d', strtotime('+3 days')) ?>">
                </div>
            </div>
            <div class="mb-4">
                <input type="checkbox" name="is_aktif" value="1" id="chkAktif" checked> <label for="chkAktif">Langsung Aktifkan?</label>
            </div>
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="pengumuman_data.php" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </body>
    </html>
  <?php
  exit;
}
if ($_GET['act']=='simpan' && $_SERVER['REQUEST_METHOD']=='POST') {
  $stmt = $mysqli->prepare("INSERT INTO pengumuman (judul,isi,audiens,aktif_mulai,aktif_selesai,is_aktif,created_by) VALUES (?,?,?,?,?,?,?)");
  $aktif = isset($_POST['is_aktif'])?1:0;
  $stmt->bind_param('ssssssi', $_POST['judul'], $_POST['isi'], $_POST['audiens'], $_POST['aktif_mulai'], $_POST['aktif_selesai'], $aktif, $_SESSION['id_pengguna']);
  $stmt->execute();
  
  // LOG
  $db->catat_log($_SESSION['id_pengguna'], 'TAMBAH PENGUMUMAN', "Menambah pengumuman: " . $_POST['judul']);
  
  header('Location: pengumuman_data.php');
  exit;
}

if ($_GET['act']=='edit' && isset($_GET['id'])) {
  $id = intval($_GET['id']);
  $row = $mysqli->query("SELECT * FROM pengumuman WHERE id_pengumuman=$id")->fetch_assoc();
  ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <title>Edit Pengumuman</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="../assets/css/app.css"/>
    </head>
    <body style="background:#f1f5f9; padding:20px;">
        <div class="card-white" style="max-width:600px; margin:0 auto;">
            <h2 class="font-bold text-lg mb-4">Edit Pengumuman</h2>
            <form method="post" action="pengumuman_proses.php?act=update">
            <input type="hidden" name="id_pengumuman" value="<?= $row['id_pengumuman'] ?>">
            <div class="mb-4">
                <label class="form-label">Judul</label>
                <input name="judul" value="<?= htmlspecialchars($row['judul']) ?>" class="form-input w-full" required>
            </div>
            <div class="mb-4">
                <label class="form-label">Isi</label>
                <textarea name="isi" class="form-input w-full" rows="4"><?= htmlspecialchars($row['isi']) ?></textarea>
            </div>
            <div class="mb-4">
                <label class="form-label">Audiens</label>
                <select name="audiens" class="form-input w-full">
                    <option value="ALL" <?= $row['audiens']=='ALL'?'selected':'' ?>>Semua</option>
                    <option value="PENGHUNI" <?= $row['audiens']=='PENGHUNI'?'selected':'' ?>>Penghuni</option>
                </select>
            </div>
            <div class="flex gap-4 mb-4">
                <div class="w-full">
                    <label class="form-label">Mulai</label>
                    <input type="date" name="aktif_mulai" value="<?= $row['aktif_mulai'] ?>" class="form-input w-full">
                </div>
                <div class="w-full">
                    <label class="form-label">Selesai</label>
                    <input type="date" name="aktif_selesai" value="<?= $row['aktif_selesai'] ?>" class="form-input w-full">
                </div>
            </div>
            <div class="mb-4">
                <input type="checkbox" name="is_aktif" value="1" <?= $row['is_aktif']?'checked':'' ?> id="chkAktif"> <label for="chkAktif">Aktif?</label>
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="pengumuman_data.php" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </body>
    </html>
  <?php
  exit;
}
if ($_GET['act']=='update' && $_SERVER['REQUEST_METHOD']=='POST') {
  $stmt = $mysqli->prepare("UPDATE pengumuman SET judul=?,isi=?,audiens=?,aktif_mulai=?,aktif_selesai=?,is_aktif=? WHERE id_pengumuman=?");
  $aktif = isset($_POST['is_aktif'])?1:0;
  $stmt->bind_param('ssssssi', $_POST['judul'], $_POST['isi'], $_POST['audiens'], $_POST['aktif_mulai'], $_POST['aktif_selesai'], $aktif, $_POST['id_pengumuman']);
  $stmt->execute();
  
  // LOG
  $db->catat_log($_SESSION['id_pengguna'], 'EDIT PENGUMUMAN', "Update pengumuman ID: " . $_POST['id_pengumuman']);
  
  header('Location: pengumuman_data.php');
  exit;
}

if ($_GET['act']=='hapus' && isset($_GET['id'])) {
  $id = intval($_GET['id']);
  $row = $mysqli->query("SELECT judul FROM pengumuman WHERE id_pengumuman=$id")->fetch_assoc();
  $judul = $row['judul'] ?? 'Unknown';

  $mysqli->query("DELETE FROM pengumuman WHERE id_pengumuman=$id");
  
  // LOG
  $db->catat_log($_SESSION['id_pengguna'], 'HAPUS PENGUMUMAN', "Menghapus pengumuman: $judul");
  
  header('Location: pengumuman_data.php');
  exit;
}
?>