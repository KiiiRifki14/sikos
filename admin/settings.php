<?php
session_start();
require '../inc/koneksi.php';
require '../inc/guard.php';
if (!is_admin() && !is_owner()) die('Forbidden');

// Dummy Save Process
$pesan = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pesan = '<div style="background:#dcfce7; color:#166534; padding:12px; border-radius:8px; margin-bottom:24px; font-size:14px;">✅ Pengaturan berhasil disimpan!</div>';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <title>Pengaturan Sistem</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="../assets/css/app.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="dashboard-body">

  <?php include '../components/sidebar_admin.php'; ?>
  <main class="main-content">
    <h1 style="font-size:24px; font-weight:700; color:#1e293b; margin-bottom:32px;">Pengaturan Sistem</h1>
    <?= $pesan ?>

    <div class="card-white" style="max-width:600px;">
        <form method="post">
            <div style="margin-bottom:20px;">
                <label class="form-label">Nama Kost</label>
                <input type="text" name="nama_kos" class="form-input" value="Kost Paadaasih">
            </div>
            <div style="margin-bottom:20px;">
                <label class="form-label">Nomor Telepon Pengelola</label>
                <input type="text" name="telp_kos" class="form-input" value="0812-3456-7890">
            </div>
            <div style="margin-bottom:20px;">
                <label class="form-label">Alamat Lengkap</label>
                <textarea name="alamat_kos" class="form-input" rows="3">Jl. Paadaasih No. 123, Cimahi</textarea>
            </div>
            <div style="margin-bottom:32px;">
                <label class="form-label">Rekening Bank (untuk transfer)</label>
                <input type="text" name="rek_bank" class="form-input" value="BCA 123456789 a.n Owner">
            </div>
            <button type="submit" class="btn-primary">Simpan Perubahan</button>
        </form>
    </div>
  </main>
</body>
</html>