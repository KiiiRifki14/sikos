<?php
session_start();
require 'inc/koneksi.php';
require 'inc/csrf.php';

if (!isset($_SESSION['id_pengguna'])) { header('Location: login.php'); exit; }

$id_pengguna = $_SESSION['id_pengguna'];

// Ambil Data Lengkap (Join tabel pengguna & penghuni)
$q = "SELECT u.*, p.alamat, p.pekerjaan, p.emergency_cp, p.foto_profil 
      FROM pengguna u 
      LEFT JOIN penghuni p ON u.id_pengguna = p.id_pengguna 
      WHERE u.id_pengguna = ?";
$stmt = $mysqli->prepare($q);
$stmt->bind_param('i', $id_pengguna);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Profil Saya - SIKOS</title>
    <link rel="stylesheet" href="assets/css/app.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="dashboard-body">

  <?php include 'components/sidebar_penghuni.php'; ?>

  <main class="main-content">
    <h2 style="font-size:24px; font-weight:700; color:#1e293b; margin-bottom:24px;">Pengaturan Profil</h2>
    
    <?php if(isset($_GET['msg']) && $_GET['msg']=='updated'): ?>
        <div class="bg-green-100 text-green-700 p-3 rounded mb-4">✅ Profil berhasil diperbarui!</div>
    <?php endif; ?>
    <?php if(isset($_GET['error'])): ?>
        <div class="bg-red-100 text-red-700 p-3 rounded mb-4">❌ <?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>

    <form action="penghuni_proses.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
        <input type="hidden" name="act" value="update_profil">
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="card-white text-center p-6 h-fit">
                <div class="w-32 h-32 mx-auto rounded-full overflow-hidden bg-slate-100 border-4 border-white shadow-lg mb-4 relative group">
                    <?php if(!empty($user['foto_profil'])): ?>
                        <img id="preview" src="assets/uploads/profil/<?= htmlspecialchars($user['foto_profil']) ?>" class="w-full h-full object-cover">
                    <?php else: ?>
                        <div id="preview_placeholder" class="w-full h-full flex items-center justify-center text-4xl text-slate-300">👤</div>
                        <img id="preview" src="" class="w-full h-full object-cover hidden">
                    <?php endif; ?>
                    
                    <label class="absolute inset-0 bg-black/50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition cursor-pointer text-white text-xs">
                        <i class="fa-solid fa-camera mr-1"></i> Ganti
                        <input type="file" name="foto_profil" class="hidden" onchange="loadFile(event)">
                    </label>
                </div>
                <p class="text-sm text-slate-500">Klik gambar untuk mengganti.</p>
                <p class="text-xs text-slate-400">Max 2MB (JPG/PNG)</p>
            </div>

            <div class="md:col-span-2">
                <div class="card-white mb-6">
                    <h3 class="font-bold text-slate-800 mb-4 border-b pb-2">Data Pribadi</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="nama" value="<?= htmlspecialchars($user['nama']) ?>" class="form-input w-full" required>
                        </div>
                        <div>
                            <label class="form-label">No. WhatsApp</label>
                            <input type="text" name="no_hp" value="<?= htmlspecialchars($user['no_hp']) ?>" class="form-input w-full" required>
                        </div>
                        <div>
                            <label class="form-label">Email (Login)</label>
                            <input type="email" value="<?= htmlspecialchars($user['email']) ?>" class="form-input w-full bg-slate-100" readonly>
                        </div>
                        <div>
                            <label class="form-label">Pekerjaan / Status</label>
                            <input type="text" name="pekerjaan" value="<?= htmlspecialchars($user['pekerjaan']) ?>" class="form-input w-full">
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label">Alamat Asal (Sesuai KTP)</label>
                        <textarea name="alamat" class="form-input w-full" rows="2"><?= htmlspecialchars($user['alamat']) ?></textarea>
                    </div>
                    
                    <div class="mb-2">
                        <label class="form-label">Kontak Darurat (Emergency)</label>
                        <input type="text" name="emergency_cp" value="<?= htmlspecialchars($user['emergency_cp']) ?>" class="form-input w-full" placeholder="Nama - No HP">
                    </div>
                </div>

                <div class="card-white bg-yellow-50 border-yellow-200">
                    <h3 class="font-bold text-yellow-800 mb-4 border-b border-yellow-200 pb-2">
                        <i class="fa-solid fa-lock mr-1"></i> Ganti Password
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-2">
                        <div>
                            <label class="form-label">Password Baru</label>
                            <input type="password" name="pass_baru" class="form-input w-full" placeholder="Kosongkan jika tidak diganti">
                        </div>
                        <div>
                            <label class="form-label">Ulangi Password</label>
                            <input type="password" name="pass_konfirm" class="form-input w-full" placeholder="Ketik ulang...">
                        </div>
                    </div>
                    <p class="text-xs text-slate-500 mt-2">*Abaikan jika tidak ingin mengganti password.</p>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit" class="btn-primary px-8 py-3 rounded-lg shadow-lg">Simpan Perubahan</button>
                </div>
            </div>
        </div>
    </form>
  </main>

  <script>
    var loadFile = function(event) {
        var output = document.getElementById('preview');
        var placeholder = document.getElementById('preview_placeholder');
        
        if(event.target.files[0]){
            output.src = URL.createObjectURL(event.target.files[0]);
            output.classList.remove('hidden');
            if(placeholder) placeholder.classList.add('hidden');
        }
    };
  </script>
</body>
</html>