<?php
session_start();
require 'inc/koneksi.php';
require 'inc/csrf.php';
require 'inc/upload.php'; 

if (!isset($_SESSION['id_pengguna'])) { header('Location: login.php'); exit; }
$id_pengguna = $_SESSION['id_pengguna'];

// Logic Tambah (Sama, disederhanakan tampilannya)
$msg = '';
if ($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['act']) && $_POST['act']=='tambah') {
    if (csrf_check($_POST['csrf'])) {
        $judul = htmlspecialchars($_POST['judul']);
        $desk = htmlspecialchars($_POST['deskripsi']);
        $prioritas = $_POST['prioritas'];
        $foto_path = !empty($_FILES['foto']['name']) ? upload_process($_FILES['foto'], 'keluhan') : null;
        
        // [REFACTOR]
        $id_penghuni = $db->get_id_penghuni_by_user($id_pengguna);
        if ($id_penghuni) {
            if($db->insert_keluhan($id_penghuni, $judul, $desk, $prioritas, $foto_path)) {
                echo "<script>alert('Keluhan terkirim!'); window.location='keluhan.php';</script>";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Lapor Keluhan</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="assets/css/app.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
      /* Modal Custom Khusus Penghuni */
      .modal-penghuni {
          display: none; position: fixed; z-index: 99999; 
          left: 0; top: 0; width: 100%; height: 100%; 
          background-color: rgba(0,0,0,0.6); backdrop-filter: blur(2px);
      }
      .modal-box {
          background: white; margin: 10vh auto; padding: 25px; border-radius: 16px;
          width: 90%; max-width: 500px; box-shadow: 0 20px 60px rgba(0,0,0,0.2);
          animation: slideUp 0.3s ease;
      }
      @keyframes slideUp { from {transform: translateY(50px); opacity:0;} to {transform: translateY(0); opacity:1;} }
  </style>
</head>
<body class="role-penghuni">
  <?php include 'components/sidebar_penghuni.php'; ?>
  <main class="main-content animate-fade-up">
    
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <div>
            <h1 style="font-size:20px; font-weight:700; color:#1e293b;">Layanan Keluhan</h1>
            <p style="font-size:13px; color:#64748b;">Laporkan kerusakan fasilitas di sini.</p>
        </div>
        <button onclick="document.getElementById('modalTambah').style.display='block'" class="btn btn-primary" style="padding:10px 20px; font-size:13px;">
            <i class="fa-solid fa-plus mr-2"></i> Buat Laporan
        </button>
    </div>

    <div style="display:flex; flex-direction:column; gap:15px;">
        <?php
        // [REFACTOR]
        $idp = $db->get_id_penghuni_by_user($id_pengguna);
        $res = $db->get_keluhan_by_penghuni($idp);
        
        if($res->num_rows > 0){
            while($row = $res->fetch_assoc()){
                $st = $row['status'];
                $badgeColor = ($st=='BARU')?'#fee2e2; color:#dc2626':(($st=='PROSES')?'#fef3c7; color:#d97706':'#dcfce7; color:#166534');
        ?>
        <div class="card-white" style="display:flex; gap:15px; padding:20px; border-left:4px solid <?= ($st=='BARU'?'#ef4444':($st=='PROSES'?'#f59e0b':'#22c55e')) ?>;">
            <div style="flex:1;">
                <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                    <span style="font-size:12px; font-weight:600; color:#94a3b8;"><?= date('d M Y, H:i', strtotime($row['dibuat_at'])) ?></span>
                    <span style="font-size:11px; font-weight:700; padding:4px 10px; border-radius:20px; background:<?= $badgeColor ?>"><?= $st ?></span>
                </div>
                <h3 style="font-size:16px; font-weight:700; color:#1e293b; margin-bottom:5px;"><?= htmlspecialchars($row['judul']) ?></h3>
                <p style="font-size:14px; color:#475569; margin-bottom:10px;"><?= htmlspecialchars($row['deskripsi']) ?></p>
                
                <?php if(!empty($row['tanggapan_admin'])): ?>
                    <div style="background:#eff6ff; padding:12px; border-radius:8px; font-size:13px; color:#1e3a8a; border:1px solid #dbeafe;">
                        <strong><i class="fa-solid fa-headset mr-1"></i> Admin:</strong> <?= $row['tanggapan_admin'] ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php }} else { ?>
            <div style="text-align:center; padding:40px; color:#94a3b8; border:2px dashed #cbd5e1; border-radius:12px;">
                Belum ada riwayat keluhan.
            </div>
        <?php } ?>
    </div>
  </main>

  <div id="modalTambah" class="modal-penghuni">
      <div class="modal-box">
          <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
              <h3 style="font-size:18px; font-weight:700;">Buat Laporan Baru</h3>
              <span onclick="document.getElementById('modalTambah').style.display='none'" style="cursor:pointer; font-size:24px;">&times;</span>
          </div>
          
          <form method="post" enctype="multipart/form-data">
              <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
              <input type="hidden" name="act" value="tambah">
              
              <div class="form-group">
                  <label class="form-label">Judul Masalah</label>
                  <input type="text" name="judul" class="form-input" placeholder="Contoh: AC Bocor" required>
              </div>
              
              <div class="form-group">
                  <label class="form-label">Prioritas</label>
                  <select name="prioritas" class="form-input">
                      <option value="LOW">Rendah</option>
                      <option value="MEDIUM" selected>Sedang</option>
                      <option value="HIGH">Darurat</option>
                  </select>
              </div>

              <div class="form-group">
                  <label class="form-label">Deskripsi Detail</label>
                  <textarea name="deskripsi" class="form-input" rows="3" required></textarea>
              </div>

              <div class="form-group">
                  <label class="form-label">Foto (Opsional)</label>
                  <input type="file" name="foto" class="form-input" accept="image/*">
              </div>

              <button type="submit" class="btn btn-primary w-full" style="padding:12px;">Kirim Laporan</button>
          </form>
      </div>
  </div>
</body>
</html>