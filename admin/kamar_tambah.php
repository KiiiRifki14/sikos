<?php
require '../inc/koneksi.php';
require '../inc/guard.php';

if (!is_admin()) { header('Location: ../login.php'); exit; }

$db = new Database();
$data_tipe = $db->tampil_tipe_kamar(); 
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <title>Tambah Kamar Baru</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../assets/css/app.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <script src="../assets/js/main.js"></script>

  <style>
      /* CSS KHUSUS HALAMAN INI AGAR RAPI */
      .form-container {
          max-width: 850px; 
          margin: 0 auto;
          background: white;
          padding: 30px;
          border-radius: 12px;
          box-shadow: 0 4px 20px rgba(0,0,0,0.03);
          border: 1px solid #e2e8f0;
      }
      .form-grid {
          display: grid;
          grid-template-columns: 1fr 1fr; 
          gap: 20px;
          margin-bottom: 15px;
      }
      .form-group { margin-bottom: 15px; }
      .form-group label {
          display: block; font-size: 13px; font-weight: 600; 
          color: #64748b; margin-bottom: 6px; text-transform: uppercase;
      }
      .form-input {
          width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1;
          border-radius: 6px; font-size: 14px; color: #334155;
          transition: 0.2s; box-sizing: border-box; 
      }
      .form-input:focus { border-color: #2563eb; outline: none; box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }
      
      .upload-box {
          border: 2px dashed #cbd5e1; border-radius: 8px;
          padding: 20px; text-align: center; cursor: pointer;
          transition: 0.2s; background: #f8fafc;
      }
      .upload-box:hover { border-color: #2563eb; background: #eff6ff; }
      
      .fasilitas-grid {
          display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 10px;
      }
      .check-item {
          display: flex; align-items: center; gap: 8px; padding: 8px 12px;
          border: 1px solid #e2e8f0; border-radius: 6px; cursor: pointer;
          font-size: 13px; color: #475569;
      }
      .check-item:hover { background: #f1f5f9; }
      
      @media (max-width: 768px) { .form-grid { grid-template-columns: 1fr; } }
  </style>
</head>
<body class="dashboard-body">

  <?php include '../components/sidebar_admin.php'; ?>

  <main class="main-content">
    
    <div class="flex justify-between items-center mb-6" style="max-width: 850px; margin: 0 auto 20px;">
        <h1 class="font-bold text-xl text-slate-800">Tambah Kamar</h1>
        <a href="kamar_data.php" class="btn btn-secondary text-xs" style="padding: 8px 16px;">
            <i class="fa-solid fa-arrow-left mr-2"></i> Kembali
        </a>
    </div>

    <div class="form-container">
        <form method="post" action="kamar_proses.php?act=tambah" enctype="multipart/form-data">
            
            <div class="form-grid">
                <div class="form-group">
                    <label>Kode Kamar</label>
                    <input name="kode_kamar" class="form-input" placeholder="Contoh: A01" required>
                </div>
                <div class="form-group">
                    <label>Tipe Kamar</label>
                    <select name="id_tipe" class="form-input" required>
                        <option value="">-- Pilih Tipe --</option>
                        <?php if (!empty($data_tipe)) {
                            foreach($data_tipe as $t) {
                                echo '<option value="'.$t['id_tipe'].'">'.htmlspecialchars($t['nama_tipe']).'</option>';
                            }
                        } ?>
                    </select>
                </div>
            </div>

            <div class="form-grid" style="grid-template-columns: 2fr 1fr 1fr;">
                <div class="form-group">
                    <label>Harga (Per Bulan)</label>
                    <div style="position:relative;">
                        <span style="position:absolute; left:10px; top:10px; color:#64748b; font-size:14px;">Rp</span>
                        <input name="harga" type="number" class="form-input" style="padding-left: 35px; font-weight:bold; color:#2563eb;" placeholder="0" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Lantai</label>
                    <input name="lantai" type="number" class="form-input" value="1" required>
                </div>
                <div class="form-group">
                    <label>Luas (m²)</label>
                    <input name="luas_m2" type="number" step="0.1" class="form-input" value="9" required>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label>Foto Cover</label>
                    <label class="upload-box">
                        <input type="file" name="foto_cover" style="display:none;" accept="image/*" onchange="previewImage(this)">
                        <i class="fa-solid fa-cloud-arrow-up" style="font-size: 24px; color:#cbd5e1; margin-bottom:5px;"></i>
                        <div style="font-size:12px; font-weight:bold; color:#2563eb;">Klik Upload Foto</div>
                        <img id="img-prev" src="" style="max-height:100px; margin: 10px auto 0; display:none; border-radius:6px;">
                    </label>
                </div>

                <div class="form-group">
                    <label>Catatan / Fasilitas Khusus</label>
                    <textarea name="catatan" class="form-input" rows="5" placeholder="Tulis deskripsi kamar..."></textarea>
                </div>
            </div>

            <div class="form-group" style="margin-top: 10px; border-top: 1px solid #f1f5f9; padding-top: 15px;">
                <label style="margin-bottom: 10px;">Fasilitas Kamar</label>
                <div class="fasilitas-grid">
                    <?php
                    $q_fas = $mysqli->query("SELECT * FROM fasilitas_master ORDER BY nama_fasilitas ASC");
                    while($f = $q_fas->fetch_assoc()):
                    ?>
                        <label class="check-item">
                            <input type="checkbox" name="fasilitas[]" value="<?= $f['id_fasilitas'] ?>">
                            <i class="fa-solid <?= $f['icon'] ?> text-slate-400"></i> 
                            <?= htmlspecialchars($f['nama_fasilitas']) ?>
                        </label>
                    <?php endwhile; ?>
                </div>
            </div>

            <div style="text-align: right; margin-top: 30px;">
                <button type="submit" class="btn btn-primary" style="padding: 12px 40px;">Simpan Data</button>
            </div>
        </form>
    </div>
  </main>

  <script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('img-prev').src = e.target.result;
                document.getElementById('img-prev').style.display = 'block';
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
  </script>
</body>
</html>