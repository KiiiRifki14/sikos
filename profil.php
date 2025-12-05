<?php
session_start();
require 'inc/koneksi.php';
require 'inc/csrf.php';

if (!isset($_SESSION['id_pengguna'])) { header('Location: login.php'); exit; }
$id_pengguna = $_SESSION['id_pengguna'];

// Ambil Data
$q = "SELECT u.*, p.alamat, p.pekerjaan, p.emergency_cp, p.foto_profil 
      FROM pengguna u 
      LEFT JOIN penghuni p ON u.id_pengguna = p.id_pengguna 
      WHERE u.id_pengguna = ?";
$stmt = $mysqli->prepare($q);
$stmt->bind_param('i', $id_pengguna);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$foto_url = !empty($user['foto_profil']) ? "assets/uploads/profil/".$user['foto_profil'] : "assets/img/avatar.png";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Edit Profil</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="stylesheet" href="assets/css/app.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
  <?php include 'components/sidebar_penghuni.php'; ?>
  <main class="main-content">
    
    <div class="mb-6">
        <h1 style="font-size:20px; font-weight:700; color:#1e293b;">Profil Saya</h1>
        <p style="font-size:13px; color:#64748b;">Perbarui informasi pribadi dan keamanan akun.</p>
    </div>

    <form action="penghuni_proses.php?act=update_profil" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
        
        <div style="display:grid; grid-template-columns: 1fr 2fr; gap:30px; align-items:start;">
            
            <div class="card-white" style="text-align:center;">
                <div style="width:120px; height:120px; margin:0 auto 15px; position:relative;">
                    <img id="preview_foto" src="<?= $foto_url ?>" style="width:100%; height:100%; object-fit:cover; border-radius:50%; border:4px solid #f1f5f9;">
                    <label for="upload_foto" style="position:absolute; bottom:5px; right:5px; background:#2563eb; color:white; width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer; box-shadow:0 4px 6px rgba(0,0,0,0.1);">
                        <i class="fa-solid fa-camera" style="font-size:14px;"></i>
                    </label>
                    <input type="file" name="foto_profil" id="upload_foto" style="display:none;" accept="image/*" onchange="previewImage(this)">
                </div>
                <h3 style="font-size:16px; font-weight:700; margin-bottom:5px;"><?= htmlspecialchars($user['nama']) ?></h3>
                <p style="font-size:12px; color:#64748b;">Penghuni</p>
            </div>

            <div class="card-white">
                <h4 style="font-size:15px; font-weight:700; color:#334155; border-bottom:1px solid #e2e8f0; padding-bottom:10px; margin-bottom:20px;">Data Diri</h4>
                
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px; margin-bottom:15px;">
                    <div>
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="nama" value="<?= htmlspecialchars($user['nama']) ?>" class="form-input w-full" required>
                    </div>
                    <div>
                        <label class="form-label">No. Handphone</label>
                        <input type="text" name="no_hp" value="<?= htmlspecialchars($user['no_hp']) ?>" class="form-input w-full" required>
                    </div>
                </div>

                <div style="margin-bottom:15px;">
                    <label class="form-label">Email (Login)</label>
                    <input type="email" value="<?= htmlspecialchars($user['email']) ?>" class="form-input w-full" style="background:#f8fafc; color:#94a3b8;" readonly>
                </div>

                <div style="margin-bottom:15px;">
                    <label class="form-label">Alamat Asal</label>
                    <textarea name="alamat" class="form-input w-full" rows="2"><?= htmlspecialchars($user['alamat']) ?></textarea>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px; margin-bottom:30px;">
                    <div>
                        <label class="form-label">Pekerjaan</label>
                        <input type="text" name="pekerjaan" value="<?= htmlspecialchars($user['pekerjaan']) ?>" class="form-input w-full">
                    </div>
                    <div>
                        <label class="form-label">Kontak Darurat</label>
                        <input type="text" name="emergency_cp" value="<?= htmlspecialchars($user['emergency_cp']) ?>" class="form-input w-full" placeholder="Nama - No HP">
                    </div>
                </div>

                <h4 style="font-size:15px; font-weight:700; color:#334155; border-bottom:1px solid #e2e8f0; padding-bottom:10px; margin-bottom:20px;">Keamanan (Opsional)</h4>
                
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px; margin-bottom:20px;">
                    <div>
                        <label class="form-label">Password Baru</label>
                        <input type="password" name="pass_baru" class="form-input w-full" placeholder="Kosongkan jika tidak ubah">
                    </div>
                    <div>
                        <label class="form-label">Ulangi Password</label>
                        <input type="password" name="pass_konfirm" class="form-input w-full" placeholder="Ketik ulang password baru">
                    </div>
                </div>

                <div style="text-align:right;">
                    <button type="submit" class="btn btn-primary" style="padding:10px 30px;">Simpan Perubahan</button>
                </div>
            </div>
        </div>
    </form>
  </main>

  <script>
      function previewImage(input) {
          if (input.files && input.files[0]) {
              var reader = new FileReader();
              reader.onload = function(e) {
                  document.getElementById('preview_foto').src = e.target.result;
              }
              reader.readAsDataURL(input.files[0]);
          }
      }
  </script>

  <style>
      @media (max-width: 768px) {
          div[style*="grid-template-columns: 1fr 2fr"] { grid-template-columns: 1fr !important; }
          div[style*="grid-template-columns: 1fr 1fr"] { grid-template-columns: 1fr !important; }
      }
  </style>
</body>
</html>