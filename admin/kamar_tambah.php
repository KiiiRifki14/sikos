<?php
// Urutan require sangat penting!
require '../inc/koneksi.php'; // 1. Load class Database
require '../inc/guard.php';   // 2. Load guard (ini sudah ada session_start di dalamnya)

// Cek Admin (Fungsi is_admin() berasal dari guard.php)
if (!is_admin()) { 
    header('Location: ../login.php'); 
    exit; 
}

$db = new Database();

// Ambil data tipe kamar untuk dropdown
// Pastikan method tampil_tipe_kamar() SUDAH DITAMBAHKAN di inc/koneksi.php langkah 1!
$data_tipe = $db->tampil_tipe_kamar(); 
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <title>Tambah Kamar - SIKOS Admin</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../assets/css/app.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="dashboard-body">

  <button class="sidebar-toggle" onclick="toggleSidebar()">☰</button>
  <?php include '../components/sidebar_admin.php'; ?>

  <main class="main-content">
    <div class="flex justify-between items-center mb-6">
        <h1 class="font-bold text-xl">Tambah Kamar Baru</h1>
        <a href="kamar_data.php" class="btn btn-secondary text-sm">
            <i class="fa-solid fa-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="card-white" style="max-width:800px;">
        <form method="post" action="kamar_proses.php?act=tambah" enctype="multipart/form-data">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                <div>
                    <label class="form-label">Kode Kamar</label>
                    <input name="kode_kamar" class="form-input" placeholder="Contoh: A01" required>
                </div>
                <div>
                    <label class="form-label">Tipe Kamar</label>
                    <select name="id_tipe" class="form-input" required>
                        <option value="">-- Pilih Tipe --</option>
                        <?php 
                        if (!empty($data_tipe)) {
                            foreach($data_tipe as $t) {
                                // Pastikan nama kolom sesuai tabel kamu (id_tipe, nama_tipe)
                                echo '<option value="'.$t['id_tipe'].'">'.htmlspecialchars($t['nama_tipe']).'</option>';
                            }
                        } else {
                            echo '<option value="" disabled>Data tipe kamar tidak terbaca</option>';
                        }
                        ?>
                    </select>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-4">
                <div>
                    <label class="form-label">Harga (Per Bulan)</label>
                    <input name="harga" type="number" class="form-input" placeholder="0" required>
                </div>
                <div>
                    <label class="form-label">Lantai</label>
                    <input name="lantai" type="number" class="form-input" value="1" min="1" required>
                </div>
                <div>
                    <label class="form-label">Luas (m²)</label>
                    <input name="luas_m2" type="number" step="0.1" class="form-input" value="9" required>
                </div>
            </div>

            <div class="mb-6 bg-slate-50 p-4 rounded border border-slate-200">
                <label class="form-label block mb-2">Foto Cover</label>
                <input type="file" name="foto_cover" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" accept="image/*">
                <p class="text-xs text-muted mt-1">Format: JPG/PNG/WEBP. Maks 2MB.</p>
            </div>

            <div class="mb-6">
                <label class="form-label block mb-3">Fasilitas Kamar</label>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                    <?php
                    // Query manual untuk fasilitas (biar simpel di view)
                    $q_fas = $mysqli->query("SELECT * FROM fasilitas_master ORDER BY nama_fasilitas ASC");
                    if ($q_fas && $q_fas->num_rows > 0) {
                        while($f = $q_fas->fetch_assoc()):
                    ?>
                        <label class="flex items-center gap-2 p-2 border rounded hover:bg-slate-50 cursor-pointer">
                            <input type="checkbox" name="fasilitas[]" value="<?= $f['id_fasilitas'] ?>" class="w-4 h-4 text-blue-600 rounded">
                            <span class="text-sm">
                                <i class="fa-solid <?= $f['icon'] ?> text-slate-400 w-5 text-center"></i> 
                                <?= htmlspecialchars($f['nama_fasilitas']) ?>
                            </span>
                        </label>
                    <?php 
                        endwhile; 
                    } else {
                        echo '<p class="text-sm text-muted col-span-3">Belum ada data fasilitas.</p>';
                    }
                    ?>
                </div>
            </div>

            <div class="mb-6">
                <label class="form-label">Catatan Tambahan (Opsional)</label>
                <textarea name="catatan" class="form-input" rows="3" placeholder="Contoh: Kamar pojok, view taman, dilarang bawa hewan..."></textarea>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                <button type="button" onclick="history.back()" class="btn btn-secondary">Batal</button>
                <button type="submit" class="btn btn-primary px-8">Simpan Kamar</button>
            </div>
        </form>
    </div>
  </main>
</body>
</html>