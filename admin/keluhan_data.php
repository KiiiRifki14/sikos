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

// Query Data Keluhan (SESUAI STRUKTUR ASLI)
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
    .modal { display: none; position: fixed; z-index: 100; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5); backdrop-filter: blur(2px); }
    .modal-content { background-color: #fff; margin: 10% auto; padding: 24px; border: none; width: 90%; max-width: 500px; border-radius: 12px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); }
    .pagination-nav a { margin: 0 6px; }
  </style>
</head>
<body class="dashboard-body">

  <button class="sidebar-toggle" onclick="toggleSidebar()">☰</button>
  <?php include '../components/sidebar_admin.php'; ?>

  <main class="main-content">
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
                        // Logic Warna Badge Status
                        $statusBg = 'background:#f1f5f9; color:#64748b;'; // Default
                        if($row['status'] == 'BARU') $statusBg = 'background:#fee2e2; color:#dc2626;'; // Merah
                        if($row['status'] == 'PROSES') $statusBg = 'background:#fef3c7; color:#d97706;'; // Kuning
                        if($row['status'] == 'SELESAI') $statusBg = 'background:#dcfce7; color:#166534;'; // Hijau
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
                            <div class="text-xs text-muted" style="max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                <?= htmlspecialchars($row['deskripsi']) ?>
                            </div>
                            
                            <?php if(!empty($row['foto_path'])): ?>
                                <a href="../assets/uploads/keluhan/<?= htmlspecialchars($row['foto_path']) ?>" target="_blank" class="text-xs" style="color:var(--primary); text-decoration:underline;">
                                    <i class="fa-solid fa-paperclip"></i> Lihat Foto
                                </a>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span style="<?= $statusBg ?> padding:4px 8px; border-radius:4px; font-size:10px; font-weight:bold; text-transform:uppercase;">
                                <?= htmlspecialchars($row['status']) ?>
                            </span>
                        </td>
                        <td>
                            <div class="flex gap-2">
                                <button onclick="openModal(<?= htmlspecialchars($row['id_keluhan']) ?>, '<?= htmlspecialchars($row['status']) ?>')" class="btn btn-primary text-xs" style="padding:6px 10px;">
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

        <!-- Pagination (mempertahankan query string lain jika ada) -->
        <?php
            $total_halaman = max(1, (int)$total_halaman);
            $prev = max(1, $halaman - 1);
            $next = min($total_halaman, $halaman + 1);
        ?>
        <div class="flex justify-center mt-6 gap-2">
            <?php $qs = $_GET; $qs['halaman'] = $prev; ?>
            <a href="?<?= http_build_query($qs) ?>" class="btn btn-secondary text-xs" style="<?= ($halaman <= 1) ? 'opacity:0.5; pointer-events:none;' : '' ?> padding:6px 12px;">Previous</a>

            <?php for($x = 1; $x <= $total_halaman; $x++):
                $qs = $_GET; $qs['halaman'] = $x;
            ?>
                <a href="?<?= http_build_query($qs) ?>" class="btn btn-secondary text-xs <?= ($halaman == $x) ? 'btn-primary' : '' ?>" style="padding:6px 12px;"><?= $x ?></a>
            <?php endfor; ?>

            <?php $qs = $_GET; $qs['halaman'] = $next; ?>
            <a href="?<?= http_build_query($qs) ?>" class="btn btn-secondary text-xs" style="<?= ($halaman >= $total_halaman) ? 'opacity:0.5; pointer-events:none;' : '' ?> padding:6px 12px;">Next</a>
        </div>

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
                    <select name="status" id="modal_status" class="form-input">
                        <option value="BARU">⭕ Baru (Belum Dikerjakan)</option>
                        <option value="PROSES">🛠️ Sedang Dikerjakan</option>
                        <option value="SELESAI">✅ Selesai</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="form-label">Respon Admin / Catatan</label>
                    <textarea name="tanggapan" class="form-input" rows="3" placeholder="Tulis tanggapan untuk penghuni..."></textarea>
                </div>

                <button type="submit" class="btn btn-primary w-full">Simpan Perubahan</button>
            </form>
        </div>
    </div>

  </main>

  <script>
      function openModal(id, status) {
          document.getElementById('modal_id').value = id;
          document.getElementById('modal_status').value = status;
          document.getElementById('modalUpdate').style.display = 'block';
      }
      
      function closeModal() {
          document.getElementById('modalUpdate').style.display = 'none';
      }
      
      // Tutup modal jika klik di luar
      window.onclick = function(event) {
          if (event.target == document.getElementById('modalUpdate')) {
              closeModal();
          }
      }
  </script>

</body>
</html>