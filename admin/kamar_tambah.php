<?php
require '../inc/koneksi.php';
require '../inc/guard.php';
session_start();
if (!is_admin()) { die('Forbidden'); }
$db = new Database();
$data_tipe = $db->tampil_tipe_kamar();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <title>Tambah Kamar</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="../assets/css/app.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="dashboard-body">

  <?php include '../components/sidebar_admin.php'; ?>
  <main class="main-content">
    <h1 style="font-size:24px; font-weight:700; color:#1e293b; margin-bottom:32px;">Tambah Kamar Baru</h1>

    <div class="card-white" style="max-width:600px;">
        <form method="post" action="kamar_proses.php?act=tambah" enctype="multipart/form-data">
            <div style="margin-bottom:20px;">
                <label class="form-label">Kode Kamar</label>
                <input name="kode_kamar" class="form-input" placeholder="Contoh: A01" required>
            </div>
            
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px; margin-bottom:20px;">
                <div>
                    <label class="form-label">Tipe Kamar</label>
                    <select name="id_tipe" class="form-input">
                        <?php foreach($data_tipe as $t) { ?>
                            <option value="<?= $t['id_tipe'] ?>"><?= $t['nama_tipe'] ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div>
                    <label class="form-label">Harga (Per Bulan)</label>
                    <input name="harga" type="number" class="form-input" placeholder="0">
                </div>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px; margin-bottom:20px;">
                <div>
                    <label class="form-label">Lantai</label>
                    <input name="lantai" type="number" class="form-input" value="1">
                </div>
                <div>
                    <label class="form-label">Luas (m²)</label>
                    <input name="luas_m2" type="number" class="form-input" value="9">
                </div>
            </div>

            <div style="margin-bottom:20px;">
                <label class="form-label">Foto Cover</label>
                <input type="file" name="foto_cover" class="form-input" style="padding:10px;">
            </div>

            <div style="margin-bottom:32px;">
                <label class="form-label">Pilih Fasilitas Kamar</label>
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px; background:#f8fafc; padding:15px; border-radius:8px; border:1px solid #e2e8f0;">
                    <?php
                    $fas = $mysqli->query("SELECT * FROM fasilitas_master ORDER BY nama_fasilitas ASC");
                    while($f = $fas->fetch_assoc()):
                    ?>
                    <label style="display:flex; align-items:center; gap:8px; font-size:14px; cursor:pointer;">
                        <input type="checkbox" name="fasilitas[]" value="<?= $f['id_fasilitas'] ?>" style="width:16px; height:16px;">
                        <span><i class="fa-solid <?= $f['icon'] ?> text-slate-400"></i> <?= $f['nama_fasilitas'] ?></span>
                    </label>
                    <?php endwhile; ?>
                </div>
            </div>

            <div style="margin-bottom:20px;">
                <label class="form-label">Catatan Tambahan (Opsional)</label>
                <textarea name="catatan" class="form-input" rows="2" placeholder="Contoh: Kamar pojok, view taman..."></textarea>
            </div>

            <button type="submit" class="btn-primary" style="width:100%; justify-content:center;">Simpan Kamar</button>
        </form>
    </div>
  </main>
</body>
</html>