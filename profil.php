<?php
session_start();
require 'inc/koneksi.php';
require 'inc/csrf.php';

if (!isset($_SESSION['id_pengguna'])) { header('Location: login.php'); exit; }
$id_pengguna = $_SESSION['id_pengguna'];

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
    <style>
        .profil-layout {
            display: grid; grid-template-columns: 1fr 2fr; gap: 25px;
            align-items: start;
        }
        .foto-box {
            background: white; padding: 30px; border-radius: 12px;
            border: 1px solid #e2e8f0; text-align: center;
        }
        .foto-wrapper {
            width: 120px; height: 120px; margin: 0 auto 15px; position: relative;
        }
        .foto-profil {
            width: 100%; height: 100%; object-fit: cover; border-radius: 50%; border: 4px solid #f1f5f9;
        }
        .btn-upload {
            position: absolute; bottom: 0; right: 0;
            background: #2563eb; color: white;
            width: 32px; height: 32px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; transition: 0.2s;
        }
        .btn-upload:hover { transform: scale(1.1); }
        
        .form-box {
            background: white; padding: 30px; border-radius: 12px; border: 1px solid #e2e8f0;
        }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .form-group { margin-bottom: 15px; }
        .form-label { display: block; font-size: 12px; font-weight: 700; color: #64748b; margin-bottom: 5px; text-transform: uppercase; }
        .form-input {
            width: 100%; padding: 10px; border: 1px solid #cbd5e1;
            border-radius: 6px; font-size: 14px; color: #334155;
        }
        
        @media (max-width: 768px) {
            .profil-layout { grid-template-columns: 1fr; }
            .form-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
  <?php include 'components/sidebar_penghuni.php'; ?>
  <main class="main-content">
    
    <div style="margin-bottom: 30px;">
        <h1 style="font-size: 20px; font-weight: 700; color: #1e293b;">Profil Saya</h1>
    </div>

    <form action="penghuni_proses.php?act=update_profil" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
        
        <div class="profil-layout">
            <div class="foto-box">
                <div class="foto-wrapper">
                    <img id="preview" src="<?= $foto_url ?>" class="foto-profil">
                    <label class="btn-upload">
                        <i class="fa-solid fa-camera"></i>
                        <input type="file" name="foto_profil" style="display:none;" accept="image/*" onchange="loadFile(event)">
                    </label>
                </div>
                <h3 style="font-size:16px; font-weight:700; margin-bottom:5px;"><?= htmlspecialchars($user['nama']) ?></h3>
                <span style="font-size:12px; background:#eff6ff; color:#2563eb; padding:4px 10px; border-radius:10px;">Penghuni</span>
            </div>

            <div class="form-box">
                <h4 style="font-weight:700; padding-bottom:10px; border-bottom:1px solid #f1f5f9; margin-bottom:20px;">Informasi Pribadi</h4>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="nama" value="<?= htmlspecialchars($user['nama']) ?>" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">No. Handphone</label>
                        <input type="text" name="no_hp" value="<?= htmlspecialchars($user['no_hp']) ?>" class="form-input" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Alamat Asal</label>
                    <textarea name="alamat" class="form-input" rows="2"><?= htmlspecialchars($user['alamat']) ?></textarea>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Pekerjaan</label>
                        <input type="text" name="pekerjaan" value="<?= htmlspecialchars($user['pekerjaan']) ?>" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Kontak Darurat</label>
                        <input type="text" name="emergency_cp" value="<?= htmlspecialchars($user['emergency_cp']) ?>" class="form-input" placeholder="Nama - No HP">
                    </div>
                </div>

                <h4 style="font-weight:700; padding-bottom:10px; border-bottom:1px solid #f1f5f9; margin-bottom:20px; margin-top:20px;">Ganti Password (Opsional)</h4>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Password Baru</label>
                        <input type="password" name="pass_baru" class="form-input" placeholder="Kosongkan jika tetap">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Ulangi Password</label>
                        <input type="password" name="pass_konfirm" class="form-input" placeholder="Ketik ulang password">
                    </div>
                </div>

                <div style="text-align:right; margin-top:20px;">
                    <button type="submit" class="btn btn-primary" style="padding:12px 30px;">Simpan Perubahan</button>
                </div>
            </div>
        </div>
    </form>

  </main>
  <script>
    var loadFile = function(event) {
        var output = document.getElementById('preview');
        if(event.target.files && event.target.files[0]) {
            output.src = URL.createObjectURL(event.target.files[0]);
        }
    };
  </script>
</body>
</html>