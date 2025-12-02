<?php
session_start();
require '../inc/koneksi.php';
require '../inc/guard.php';
if (!is_admin() && !is_owner()) die('Forbidden');

// --- LOGIKA PAGINATION ---
$batas = 10;
$halaman = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;
$halaman_awal = ($halaman > 1) ? ($halaman * $batas) - $batas : 0;

// 1. Hitung Total Data
$total_data = $mysqli->query("SELECT COUNT(*) FROM keluhan")->fetch_row()[0];
$total_halaman = ceil($total_data / $batas);

// 2. Query Data dengan Limit
$q = "SELECT k.*, p.nama AS nama_penghuni, km.kode_kamar 
      FROM keluhan k
      JOIN penghuni ph ON k.id_penghuni = ph.id_penghuni
      JOIN pengguna p ON ph.id_pengguna = p.id_pengguna
      LEFT JOIN kontrak ko ON ph.id_penghuni = ko.id_penghuni AND ko.status='AKTIF'
      LEFT JOIN kamar km ON ko.id_kamar = km.id_kamar
      ORDER BY FIELD(k.status, 'BARU', 'PROSES', 'SELESAI'), k.dibuat_at DESC
      LIMIT $halaman_awal, $batas";

$res = $mysqli->query($q);
$nomor = $halaman_awal + 1;
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <title>Data Keluhan</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="../assets/css/app.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
      .pagination { display: flex; list-style: none; gap: 5px; margin-top: 20px; justify-content: center; }
      .page-link { 
          padding: 8px 14px; border: 1px solid #e2e8f0; background: white; 
          color: #64748b; border-radius: 6px; font-size: 14px; font-weight: 600; text-decoration: none; transition: 0.2s;
      }
      .page-link:hover { background: #f1f5f9; color: #1e293b; }
      .page-item.active .page-link { background: #2563eb; color: white; border-color: #2563eb; }
      .page-item.disabled .page-link { background: #f8fafc; color: #cbd5e1; cursor: not-allowed; }
  </style>
</head>
<body class="dashboard-body">

  <?php include '../components/sidebar_admin.php'; ?>
  <main class="main-content">
    <h1 style="font-size:24px; font-weight:700; color:#1e293b; margin-bottom:32px;">Laporan Keluhan</h1>

    <div class="card-white">
        <table style="width:100%; border-collapse:collapse; font-size:14px;">
            <thead>
                <tr style="background:#f8fafc; text-align:left; border-bottom:1px solid #e2e8f0;">
                    <th style="padding:16px; color:#64748b; font-size:12px;">NO</th>
                    <th style="padding:16px; color:#64748b; font-size:12px;">TANGGAL</th>
                    <th style="padding:16px; color:#64748b; font-size:12px;">PELAPOR</th>
                    <th style="padding:16px; color:#64748b; font-size:12px;">MASALAH</th>
                    <th style="padding:16px; color:#64748b; font-size:12px;">PRIORITAS</th>
                    <th style="padding:16px; color:#64748b; font-size:12px;">STATUS</th>
                    <th style="padding:16px; color:#64748b; font-size:12px;">AKSI</th>
                </tr>
            </thead>
            <tbody>
            <?php
            if ($res->num_rows > 0) {
                while ($row = $res->fetch_assoc()) {
                    $badge = 'background:#fef3c7; color:#d97706;'; // Baru
                    if($row['status']=='PROSES') $badge = 'background:#dbeafe; color:#2563eb;';
                    if($row['status']=='SELESAI') $badge = 'background:#dcfce7; color:#166534;';
                    
                    $prioColor = ($row['prioritas']=='HIGH') ? '#dc2626' : (($row['prioritas']=='MEDIUM') ? '#d97706' : '#64748b');
            ?>
            <tr style="border-bottom:1px solid #f1f5f9;">
                <td style="padding:16px; color:#64748b; text-align:center; width:50px;"><?= $nomor++ ?></td>
                <td style="padding:16px;">
                    <div style="font-weight:600;"><?= date('d M Y', strtotime($row['dibuat_at'])) ?></div>
                    <div style="font-size:12px; color:#94a3b8;"><?= date('H:i', strtotime($row['dibuat_at'])) ?></div>
                </td>
                <td style="padding:16px;">
                    <div style="font-weight:700; color:#1e293b;"><?= htmlspecialchars($row['nama_penghuni']) ?></div>
                    <div style="font-size:12px; color:#2563eb;">Kamar <?= $row['kode_kamar'] ?? '-' ?></div>
                </td>
                <td style="padding:16px;">
                    <div style="font-weight:600;"><?= htmlspecialchars($row['judul']) ?></div>
                    <div style="font-size:12px; color:#64748b; max-width:250px;"><?= htmlspecialchars($row['deskripsi']) ?></div>
                </td>
                <td style="padding:16px; font-weight:700; color:<?= $prioColor ?>; font-size:12px;"><?= $row['prioritas'] ?></td>
                <td style="padding:16px;">
                    <span style="padding:4px 10px; border-radius:6px; font-size:11px; font-weight:700; <?= $badge ?>"><?= $row['status'] ?></span>
                </td>
                <td style="padding:16px;">
                    <button class="btn-primary open-modal" 
                    data-id="<?= $row['id_keluhan'] ?>"
                    data-judul="<?= htmlspecialchars($row['judul']) ?>"
                    data-penghuni="<?= htmlspecialchars($row['nama_penghuni']) ?>"
                    data-deskripsi="<?= htmlspecialchars($row['deskripsi']) ?>"
                    data-status="<?= $row['status'] ?>"
                    data-tanggapan="<?= htmlspecialchars($row['tanggapan_admin'] ?? '') ?>"
                    data-foto="<?= $row['foto_path'] ? '../assets/uploads/keluhan/' . $row['foto_path'] : '' ?>"
                    style="padding:6px 12px; font-size:12px;">
                <i class="fa-solid fa-eye"></i> Detail & Respon
            </button>
                </td>
            </tr>
            <?php 
                } 
            } else {
                echo "<tr><td colspan='7' class='text-center py-8 text-slate-400'>Belum ada keluhan masuk.</td></tr>";
            }
            ?>
            </tbody>
        </table>

        <nav>
            <ul class="pagination">
                <li class="page-item <?= ($halaman <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= ($halaman > 1) ? "?halaman=".($halaman-1) : '#' ?>">Previous</a>
                </li>
                <?php for($x = 1; $x <= $total_halaman; $x++): ?>
                    <li class="page-item <?= ($halaman == $x) ? 'active' : '' ?>">
                        <a class="page-link" href="?halaman=<?= $x ?>"><?= $x ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= ($halaman >= $total_halaman) ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= ($halaman < $total_halaman) ? "?halaman=".($halaman+1) : '#' ?>">Next</a>
                </li>
            </ul>
        </nav>
        
        <div style="text-align:center; margin-top:10px; font-size:12px; color:#94a3b8;">
            Menampilkan halaman <?= $halaman ?> dari <?= $total_halaman ?> (Total <?= $total_data ?> keluhan)
        </div>
    </div>
  </main>
  <div id="modalKeluhan" style="display:none; position:fixed; z-index:100; left:0; top:0; width:100%; height:100%; overflow:auto; background-color:rgba(0,0,0,0.5); backdrop-filter:blur(2px);">
    <div style="background-color:#fff; margin:5% auto; padding:24px; width:90%; max-width:600px; border-radius:12px; box-shadow:0 10px 25px rgba(0,0,0,0.2);">
        <span class="close" style="float:right; font-size:28px; cursor:pointer;" onclick="document.getElementById('modalKeluhan').style.display='none'">&times;</span>
        <h2 style="font-size:20px; font-weight:700; margin-bottom:16px;">Detail Keluhan</h2>
        
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
            <div>
                <div style="margin-bottom:10px;"><b>Pelapor:</b> <span id="m_penghuni"></span></div>
                <div style="margin-bottom:10px;"><b>Masalah:</b> <div id="m_judul"></div></div>
                <div style="margin-bottom:10px;"><b>Deskripsi:</b> <div id="m_deskripsi" style="background:#f8fafc; p:8px; font-size:13px;"></div></div>
                
                <div id="m_foto_container" style="display:none; margin-top:10px;">
                    <b>Bukti Foto:</b><br>
                    <a id="m_foto_link" href="#" target="_blank">
                        <img id="m_foto_img" src="" style="width:100%; height:100px; object-fit:cover; border-radius:8px; margin-top:5px;">
                    </a>
                </div>
            </div>

            <div style="background:#f0f9ff; padding:15px; border-radius:8px;">
                <form action="keluhan_proses.php" method="POST">
                    <input type="hidden" name="id_keluhan" id="m_id">
                    
                    <label style="font-size:12px; font-weight:bold;">Update Status</label>
                    <select name="status" id="m_status" style="width:100%; padding:8px; margin-bottom:10px; border-radius:4px;">
                        <option value="BARU">Baru</option>
                        <option value="PROSES">Sedang Diproses</option>
                        <option value="SELESAI">Selesai</option>
                    </select>

                    <label style="font-size:12px; font-weight:bold;">Balasan Admin</label>
                    <textarea name="tanggapan" style="width:100%; padding:8px; height:80px; border-radius:4px;" placeholder="Tulis pesan untuk penghuni..."></textarea>

                    <button type="submit" class="btn-primary" style="width:100%; margin-top:10px;">Simpan</button>
                </form>
                <div style="text-align:center; margin-top:10px;">
                    <a id="btn_hapus" href="#" onclick="return confirm('Hapus permanen?')" style="color:red; font-size:12px;">Hapus Keluhan</a>
                </div>
            </div>
        </div>
        
        <div style="margin-top:20px; border-top:1px solid #eee; padding-top:10px;">
            <b>Riwayat Chat:</b>
            <div id="m_history" style="font-size:12px; color:#555; background:#eee; padding:10px; margin-top:5px; border-radius:5px;"></div>
        </div>
    </div>
</div>

<script>
// Script untuk mengisi data Modal saat tombol diklik
document.querySelectorAll('.open-modal').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('m_id').value = this.getAttribute('data-id');
        document.getElementById('m_penghuni').textContent = this.getAttribute('data-penghuni');
        document.getElementById('m_judul').textContent = this.getAttribute('data-judul');
        document.getElementById('m_deskripsi').textContent = this.getAttribute('data-deskripsi');
        document.getElementById('m_status').value = this.getAttribute('data-status');
        
        // Handle History Chat
        let history = this.getAttribute('data-tanggapan');
        document.getElementById('m_history').innerHTML = history ? history : '- Belum ada balasan -';

        // Handle Foto
        let foto = this.getAttribute('data-foto');
        if(foto) {
            document.getElementById('m_foto_container').style.display = 'block';
            document.getElementById('m_foto_img').src = foto;
            document.getElementById('m_foto_link').href = foto;
        } else {
            document.getElementById('m_foto_container').style.display = 'none';
        }

        // Handle Tombol Hapus
        document.getElementById('btn_hapus').href = 'keluhan_proses.php?act=hapus&id=' + this.getAttribute('data-id');

        document.getElementById('modalKeluhan').style.display = 'block';
    });
});
</script>
</body>
</html>