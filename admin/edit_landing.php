<?php
session_start();
require '../inc/koneksi.php';
require '../inc/guard.php';

if (!is_admin() && !is_owner()) { header('Location: ../login.php'); exit; }

$db = new Database();
// Pastikan ambil_pengaturan sudah di-update di koneksi.php untuk select all fields
// Kita pakai query manual dulu kalau ragu, tapi di step sebelumnya kita tidak ubah ambil_pengaturan querynya (select * sudah aman)
// Cuman default valuenya yg di update.
$pengaturan = $db->ambil_pengaturan();
$fasilitas = $db->get_fasilitas_umum();

// Helper Pesan
$msg = $_GET['msg'] ?? '';
$txt = $_GET['text'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Landing Page - Admin</title>
  <link rel="stylesheet" href="../assets/css/app.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="../assets/js/main.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="dashboard-body">
  <?php if($msg == 'custom'): ?>
    <script>
      Swal.fire({
        icon: '<?= $_GET['type'] ?? 'info' ?>',
        title: '<?= ($txt) ?>',
        showConfirmButton: false,
        timer: 1500
      });
    </script>
  <?php endif; ?>

  <button class="sidebar-toggle" onclick="toggleSidebar()">☰</button>
  <?php include '../components/sidebar_admin.php'; ?>

  <main class="main-content animate-fade-up">
    <div class="mb-8">
        <h1 class="font-bold text-xl">Edit Landing Page</h1>
        <p class="text-muted text-sm">Sesuaikan konten yang tampil di halaman depan website.</p>
    </div>

    <!-- 1. GENERAL SETTINGS -->
    <div class="card-white">
        <h3 class="font-bold text-lg mb-4">Pengaturan Kontak & Footer</h3>
        <form action="edit_landing_proses.php?act=update_pengaturan" method="POST" enctype="multipart/form-data">
            <div class="grid grid-cols-2 gap-4">
                <div class="form-group mb-4">
                    <label class="form-label">Nomor WhatsApp</label>
                    <input type="text" name="no_wa" class="form-input" value="<?= htmlspecialchars($pengaturan['no_wa'] ?? '') ?>" placeholder="62812345678">
                    <small class="text-xs text-muted">Format: 628xxx (tanpa + atau 0 di depan)</small>
                </div>
                <div class="form-group mb-4">
                    <label class="form-label">Link Facebook</label>
                    <input type="text" name="link_fb" class="form-input" value="<?= htmlspecialchars($pengaturan['link_fb'] ?? '') ?>" placeholder="https://facebook.com/users">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div class="form-group mb-4">
                    <label class="form-label">Link Instagram</label>
                    <input type="text" name="link_ig" class="form-input" value="<?= htmlspecialchars($pengaturan['link_ig'] ?? '') ?>" placeholder="https://instagram.com/users">
                </div>
                <div class="form-group mb-4">
                    <label class="form-label">Logo Website</label>
                    <input type="file" name="foto_logo" class="form-input">
                    <?php if(!empty($pengaturan['foto_logo'])): ?>
                        <div class="mt-2 text-xs">
                           Logo Aktif: <a href="../<?= $pengaturan['foto_logo'] ?>" target="_blank" class="text-blue-500">Lihat</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="form-group mb-4">
                <label class="form-label">Deskripsi Footer</label>
                <textarea name="deskripsi_footer" class="form-input" rows="3"><?= htmlspecialchars($pengaturan['deskripsi_footer'] ?? '') ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </form>
    </div>

    <!-- 2. FACILITIES CARD MANAGEMENT -->
    <div class="card-white">
        <div class="flex justify-between items-center mb-6">
            <h3 class="font-bold text-lg">Kelola Fasilitas Bangunan</h3>
            <button onclick="document.getElementById('modalFasilitas').style.display='block'" class="btn btn-sm btn-success">
                <i class="fa-solid fa-plus"></i> Tambah
            </button>
        </div>

        <div class="grid-stats" style="grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));">
            <?php foreach($fasilitas as $f): ?>
            <div class="card-white text-center" style="position:relative; margin-bottom:0;">
                <a href="edit_landing_proses.php?act=hapus_fasilitas&id=<?= $f['id'] ?>" class="btn btn-sm btn-danger" style="position:absolute; top:10px; right:10px; padding:4px 8px;" onclick="return confirm('Hapus fasilitas ini?')">
                    <i class="fa-solid fa-trash"></i>
                </a>
                <div style="background: var(--primary-light); width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px auto;">
                    <i class="fa-solid <?= $f['icon'] ?>" style="font-size:24px; color:var(--primary);"></i>
                </div>
                <h4 class="font-bold text-md mb-2"><?= htmlspecialchars($f['judul']) ?></h4>
                <p class="text-xs text-muted"><?= htmlspecialchars($f['deskripsi']) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
        
        <?php if(empty($fasilitas)): ?>
            <p class="text-center text-muted">Belum ada data fasilitas.</p>
        <?php endif; ?>
    </div>

    <!-- MODAL ADD FASILITAS -->
    <div id="modalFasilitas" class="modal" style="display:none; position:fixed; z-index:100; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.5);">
        <div class="modal-content animate-fade-up" style="background:white; margin:10% auto; padding:24px; width:90%; max-width:400px; border-radius:12px;">
            <div class="flex justify-between items-center mb-4 border-b pb-2">
                <h3 class="font-bold text-lg">Tambah Fasilitas</h3>
                <span onclick="document.getElementById('modalFasilitas').style.display='none'" style="cursor:pointer; font-size:24px;">&times;</span>
            </div>
            <form action="edit_landing_proses.php?act=tambah_fasilitas" method="POST">
                <div class="form-group mb-3">
                    <label class="form-label">Judul Fasilitas</label>
                    <input type="text" name="judul" class="form-input" required placeholder="Contoh: Parkir Luas">
                </div>
                <div class="form-group mb-3">
                    <label class="form-label">Deskripsi Singkat</label>
                    <textarea name="deskripsi" class="form-input" rows="2" required placeholder="Penjelasan singkat..."></textarea>
                </div>
                <div class="form-group mb-4">
                    <label class="form-label">Icon (Font Awesome Class)</label>
                    <select name="icon" class="form-input">
                        <option value="fa-shield-halved">🛡️ Keamanan (Shield)</option>
                        <option value="fa-wifi">📶 WiFi</option>
                        <option value="fa-shower">🚿 Shower</option>
                        <option value="fa-car">🚗 Mobil/Parkir</option>
                        <option value="fa-motorcycle">🛵 Motor</option>
                        <option value="fa-kitchen-set">🍳 Dapur</option>
                        <option value="fa-snowflake">❄️ AC</option>
                        <option value="fa-bolt">⚡ Listrik</option>
                        <option value="fa-tv">📺 TV</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary w-full">Simpan</button>
            </form>
        </div>
    </div>

  </main>
  
  <script>
    window.onclick = function(e) {
        var modal = document.getElementById('modalFasilitas');
        if (e.target == modal) modal.style.display = "none";
    }
  </script>
</body>
</html>
