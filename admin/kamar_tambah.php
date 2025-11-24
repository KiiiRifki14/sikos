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

  <aside class="sidebar">
        <div class="mb-8 px-2 flex items-center gap-3">
            <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center text-white font-bold text-xl">A</div>
            <div>
                <h1 class="font-bold text-slate-800 text-lg">SIKOS Admin</h1>
            </div>
        </div>
        <nav style="flex:1;">
            <a href="kamar_data.php" class="sidebar-link"><i class="fa-solid fa-arrow-left w-6"></i> Kembali</a>
        </nav>
  </aside>

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
                <label class="form-label">Catatan / Fasilitas</label>
                <textarea name="catatan" class="form-input" rows="3" placeholder="Tulis fasilitas kamar..."></textarea>
            </div>

            <button type="submit" class="btn-primary" style="width:100%; justify-content:center;">Simpan Kamar</button>
        </form>
    </div>
  </main>
</body>
</html>