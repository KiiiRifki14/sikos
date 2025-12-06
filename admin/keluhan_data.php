<?php
session_start();
require '../inc/koneksi.php';
require '../inc/guard.php';
if (!is_admin() && !is_owner()) die('Forbidden');

// PAGINATION
$batas = 10;
$halaman = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;
if ($halaman < 1) $halaman = 1;
$halaman_awal = ($halaman > 1) ? ($halaman * $batas) - $batas : 0;

// Hitung Total Data
$total_data = $mysqli->query("SELECT COUNT(*) FROM keluhan")->fetch_row()[0];
$total_halaman = $total_data > 0 ? (int)ceil($total_data / $batas) : 1;

// Query Data
$sql = "SELECT k.*, p.nama AS nama_penghuni, km.kode_kamar 
        FROM keluhan k
        JOIN penghuni ph ON k.id_penghuni = ph.id_penghuni
        JOIN pengguna p ON ph.id_pengguna = p.id_pengguna
        LEFT JOIN kontrak ko ON ph.id_penghuni = ko.id_penghuni AND ko.status='AKTIF'
        LEFT JOIN kamar km ON ko.id_kamar = km.id_kamar
        ORDER BY FIELD(k.status, 'BARU', 'PROSES', 'SELESAI'), k.dibuat_at DESC 
        LIMIT $halaman_awal, $batas";

$res = $mysqli->query($sql);
$nomor = $halaman_awal + 1;
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <title>Data Keluhan</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../assets/css/app.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="../assets/js/main.js"></script>
  
  <style>
    /* CSS MODAL FIXED: ditaruh di root body, z-index paling tinggi */
    .modal { 
        display: none; 
        position: fixed; 
        z-index: 99999; /* Sangat tinggi supaya di atas sidebar/header */
        left: 0; 
        top: 0; 
        width: 100%; 
        height: 100%; 
        overflow: hidden;
        background-color: rgba(0,0,0,0.6); 
        backdrop-filter: blur(2px);
    }
    
    .modal-content { 
        background-color: #fff; 
        margin: 10vh auto; /* Posisi vertical */
        padding: 24px; 
        border: none; 
        width: 90%; 
        max-width: 500px; 
        border-radius: 12px; 
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3); 
        position: relative;
        animation: slideDown 0.3s ease-out;
    }

    @keyframes slideDown {
        from { transform: translateY(-20px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
  </style>
</head>
<body class="dashboard-body">

  <button class="sidebar-toggle" onclick="toggleSidebar()">☰</button>
  <?php include '../components/sidebar_admin.php'; ?>

  <main class="main-content animate-fade-up">
    <div class="mb-8">
        <h1 class="font-bold text-xl">Laporan Keluhan</h1>
        <p class="text-xs text-muted">Daftar keluhan fasilitas dan perbaikan.</p>
    </div>

    <div class="card-white">
        <div style="overflow-x: auto;">
            <table style="width:100%;">
                <thead>
                    <tr>
                        <th>NO</th>
                        <th>TANGGAL</th>
                        <th>PELAPOR</th>
                        <th>KELUHAN</th>
                        <th>STATUS</th>
                        <th>AKSI</th>
                    </tr>
                </thead>
                <tbody>
                <?php 
                if ($res->num_rows > 0) {
                    while($row = $res->fetch_assoc()){
                        $statusBg = 'background:#f1f5f9; color:#64748b;';
                        if($row['status'] == 'BARU') $statusBg = 'background:#fee2e2; color:#dc2626;';
                        if($row['status'] == 'PROSES') $statusBg = 'background:#fef3c7; color:#d97706;';
                        if($row['status'] == 'SELESAI') $statusBg = 'background:#dcfce7; color:#166534;';
                ?>
                    <tr>
                        <td class="text-center" style="color:var(--text-muted);"><?= $nomor++ ?></td>
                        <td>
                            <div class="text-sm font-bold"><?= date('d M Y', strtotime($row['dibuat_at'])) ?></div>
                            <div class="text-xs text-muted"><?= date('H:i', strtotime($row['dibuat_at'])) ?></div>
                        </td>
                        <td>
                            <div class="font-bold"><?= htmlspecialchars($row['nama_penghuni']) ?></div>
                            <div class="text-xs text-muted">Kamar <?= htmlspecialchars($row['kode_kamar'] ?? '-') ?></div>
                        </td>
                        <td>
                            <div class="font-bold" style="font-size:14px;"><?= htmlspecialchars($row['judul']) ?></div>
                            <div class="text-xs text-muted" style="max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                <?= htmlspecialchars($row['deskripsi']) ?>
                            </div>
                            <?php if(!empty($row['foto_path'])): ?>
                                <a href="../assets/uploads/keluhan/<?= htmlspecialchars($row['foto_path']) ?>" target="_blank" class="text-xs" style="color:var(--primary);">
                                    <i class="fa-solid fa-paperclip"></i> Lihat Foto
                                </a>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span style="<?= $statusBg ?> padding:4px 8px; border-radius:4px; font-size:10px; font-weight:bold;">
                                <?= htmlspecialchars($row['status']) ?>
                            </span>
                        </td>
                        <td>
                            <div class="flex gap-2">
                                <button onclick="openModal(<?= htmlspecialchars($row['id_keluhan']) ?>, '<?= htmlspecialchars($row['status']) ?>', '<?= htmlspecialchars(addslashes($row['tanggapan_admin'] ?? '')) ?>')" class="btn btn-primary text-xs" style="padding:6px 10px;">
                                    <i class="fa-solid fa-pen-to-square"></i> Update
                                </button>

                                <a href="keluhan_proses.php?act=hapus&id=<?= htmlspecialchars($row['id_keluhan']) ?>" class="btn btn-danger text-xs" style="padding:6px 10px;" onclick="return confirm('Hapus data keluhan ini?')">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php 
                    } 
                } else {
                    echo "<tr><td colspan='6' class='text-center p-8 text-muted'>Belum ada laporan keluhan.</td></tr>";
                }
                ?>
                </tbody>
            </table>
        </div>
        
        <div class="pagination-container" style="margin-top: 20px; display:flex; gap:5px; justify-content:center;">
             <?php 
                $prev = ($halaman > 1) ? $halaman - 1 : 1;
                $next = ($halaman < $total_halaman) ? $halaman + 1 : $total_halaman;
             ?>
             
             <a href="?halaman=<?= ($halaman > 1) ? $prev : '#' ?>" 
                class="btn btn-secondary text-xs <?= ($halaman <= 1) ? 'disabled' : '' ?>" style="padding:6px 12px;">
                <i class="fa-solid fa-chevron-left"></i> Prev
             </a>

             <?php for($x = 1; $x <= $total_halaman; $x++): ?>
                <a href="?halaman=<?= $x ?>" 
                   class="btn text-xs <?= ($halaman == $x) ? 'btn-primary' : 'btn-secondary' ?>" style="padding:6px 12px;">
                   <?= $x ?>
                </a>
             <?php endfor; ?>

             <a href="?halaman=<?= ($halaman < $total_halaman) ? $next : '#' ?>" 
                class="btn btn-secondary text-xs <?= ($halaman >= $total_halaman) ? 'disabled' : '' ?>" style="padding:6px 12px;">
                Next <i class="fa-solid fa-chevron-right"></i>
             </a>
        </div>
    </div>
  </main>

  <div id="modalUpdate" class="modal">
      <div class="modal-content">
          <div class="flex justify-between items-center mb-4">
              <h3 class="font-bold text-lg">Update Status Keluhan</h3>
              <span onclick="closeModal()" style="cursor:pointer; font-size:24px;">&times;</span>
          </div>
          
          <form action="keluhan_proses.php" method="POST">
              <input type="hidden" name="id_keluhan" id="modal_id">
              
              <div class="mb-4">
                  <label class="form-label">Status Pengerjaan</label>
                  <select name="status" id="modal_status" class="form-input w-full">
                      <option value="BARU">⭕ Baru (Belum Dikerjakan)</option>
                      <option value="PROSES">🛠️ Sedang Dikerjakan</option>
                      <option value="SELESAI">✅ Selesai</option>
                  </select>
              </div>

              <div class="mb-4">
                  <label class="form-label">Respon Admin / Catatan</label>
                  <textarea name="tanggapan" id="modal_tanggapan" class="form-input w-full" rows="3" placeholder="Tulis tanggapan untuk penghuni..."></textarea>
              </div>

              <button type="submit" class="btn btn-primary w-full">Simpan Perubahan</button>
          </form>
      </div>
  </div>

  <script>
      // Script modal yang lebih robust
      function openModal(id, status, tanggapan) {
          document.getElementById('modal_id').value = id;
          document.getElementById('modal_status').value = status;
          // Optional: Jika ingin mengisi tanggapan sebelumnya
          // document.getElementById('modal_tanggapan').value = tanggapan; 
          document.getElementById('modalUpdate').style.display = 'block';
      }
      
      function closeModal() {
          document.getElementById('modalUpdate').style.display = 'none';
      }
      
      // Tutup modal jika klik di luar area putih
      window.onclick = function(event) {
          if (event.target == document.getElementById('modalUpdate')) {
              closeModal();
          }
      }
  </script>

</body>
</html>     