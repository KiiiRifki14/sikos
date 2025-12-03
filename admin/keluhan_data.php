<?php
session_start();
require '../inc/koneksi.php';
require '../inc/guard.php';
if (!is_admin() && !is_owner()) die('Forbidden');

// Pagination Setup
$batas = 10;
$halaman = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;
$halaman_awal = ($halaman > 1) ? ($halaman * $batas) - $batas : 0;

$total_data = $mysqli->query("SELECT COUNT(*) FROM keluhan")->fetch_row()[0];
$total_halaman = ceil($total_data / $batas);

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
      /* Modal Styles */
      .modal { display: none; position: fixed; z-index: 100; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5); backdrop-filter: blur(2px); }
      .modal-content { background-color: #fff; margin: 3% auto; padding: 0; border: none; width: 90%; max-width: 700px; border-radius: 16px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); animation: slideDown 0.3s ease-out; overflow: hidden; }
      @keyframes slideDown { from {transform: translateY(-20px); opacity: 0;} to {transform: translateY(0); opacity: 1;} }
      
      /* Stepper Styles (Modal & Table) */
      .step-circle { border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; color: white; position: relative; z-index: 10; transition: all 0.3s; }
      .step-line { flex: 1; height: 3px; background-color: #e2e8f0; margin: 0 -2px; z-index: 0; transition: all 0.3s; }
      
      /* Status Colors */
      .step-active-red { background-color: #ef4444; border: 2px solid #fca5a5; }
      .step-active-yellow { background-color: #eab308; border: 2px solid #fde047; }
      .step-active-green { background-color: #22c55e; border: 2px solid #86efac; }
      .step-inactive { background-color: #cbd5e1; color: #fff; }
      
      .line-active-yellow { background-color: #eab308; }
      .line-active-green { background-color: #22c55e; }
  </style>
</head>
<body class="dashboard-body">

  <?php include '../components/sidebar_admin.php'; ?>
  <main class="main-content">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Laporan Keluhan</h1>
            <p class="text-sm text-slate-500">Kelola komplain dan perbaikan fasilitas</p>
        </div>
    </div>

    <div class="card-white overflow-hidden">
        <table style="width:100%; border-collapse:collapse; font-size:14px;">
            <thead>
                <tr style="background:#f8fafc; text-align:left; border-bottom:1px solid #e2e8f0;">
                    <th class="p-4 text-slate-500 text-xs uppercase font-bold text-center">No</th>
                    <th class="p-4 text-slate-500 text-xs uppercase font-bold">Info Pelapor</th>
                    <th class="p-4 text-slate-500 text-xs uppercase font-bold">Masalah</th>
                    <th class="p-4 text-slate-500 text-xs uppercase font-bold">Prioritas</th>
                    <th class="p-4 text-slate-500 text-xs uppercase font-bold text-center" style="width:180px;">Status Progress</th>
                    <th class="p-4 text-slate-500 text-xs uppercase font-bold text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php
            if ($res->num_rows > 0) {
                while ($row = $res->fetch_assoc()) {
                    // Logic Warna Prioritas
                    $prio_color = ($row['prioritas']=='HIGH') ? 'text-red-600 font-bold bg-red-50 px-2 py-1 rounded' : 'text-slate-600';

                    // --- LOGIC MINI STEPPER UNTUK TABEL ---
                    $s1_cls = 'step-inactive'; $l1_cls = 'bg-slate-200';
                    $s2_cls = 'step-inactive'; $l2_cls = 'bg-slate-200';
                    $s3_cls = 'step-inactive';
                    $status_text = '';

                    if($row['status'] == 'BARU') {
                        $s1_cls = 'step-active-red'; 
                        $status_text = '<div class="text-[10px] font-bold text-red-500 mt-1">BARU</div>';
                    } elseif ($row['status'] == 'PROSES') {
                        $s1_cls = 'step-active-yellow';
                        $l1_cls = 'line-active-yellow';
                        $s2_cls = 'step-active-yellow';
                        $status_text = '<div class="text-[10px] font-bold text-yellow-600 mt-1">DIPROSES</div>';
                    } elseif ($row['status'] == 'SELESAI') {
                        $s1_cls = 'step-active-green';
                        $l1_cls = 'line-active-green';
                        $s2_cls = 'step-active-green';
                        $l2_cls = 'line-active-green';
                        $s3_cls = 'step-active-green';
                        $status_text = '<div class="text-[10px] font-bold text-green-600 mt-1">SELESAI</div>';
                    }
            ?>
            <tr style="border-bottom:1px solid #f1f5f9; transition:0.2s;" class="hover:bg-slate-50">
                <td class="p-4 text-center text-slate-500"><?= $nomor++ ?></td>
                <td class="p-4">
                    <div class="font-bold text-slate-800"><?= htmlspecialchars($row['nama_penghuni']) ?></div>
                    <div class="text-xs text-blue-600 font-medium">Kamar <?= $row['kode_kamar'] ?? '-' ?></div>
                    <div class="text-[10px] text-slate-400 mt-1"><?= date('d M Y H:i', strtotime($row['dibuat_at'])) ?></div>
                </td>
                <td class="p-4">
                    <div class="font-semibold text-slate-700"><?= htmlspecialchars($row['judul']) ?></div>
                    <div class="text-xs text-slate-500 max-w-[200px] truncate"><?= htmlspecialchars($row['deskripsi']) ?></div>
                </td>
                <td class="p-4">
                    <span class="<?= $prio_color ?> text-xs"><?= $row['prioritas'] ?></span>
                </td>
                
                <td class="p-4 text-center">
                    <div class="flex items-center justify-center w-full px-2">
                        <div class="step-circle <?= $s1_cls ?> w-6 h-6 text-[10px]">1</div>
                        <div class="step-line <?= $l1_cls ?> h-1"></div>
                        <div class="step-circle <?= $s2_cls ?> w-6 h-6 text-[10px]">2</div>
                        <div class="step-line <?= $l2_cls ?> h-1"></div>
                        <div class="step-circle <?= $s3_cls ?> w-6 h-6 text-[10px]">3</div>
                    </div>
                    <?= $status_text ?>
                </td>

                <td class="p-4 text-center">
                    <button class="btn-primary open-modal shadow-sm hover:shadow-md transition-all" 
                            data-id="<?= $row['id_keluhan'] ?>"
                            data-judul="<?= htmlspecialchars($row['judul']) ?>"
                            data-penghuni="<?= htmlspecialchars($row['nama_penghuni']) ?>"
                            data-kamar="<?= $row['kode_kamar'] ?? '-' ?>"
                            data-deskripsi="<?= htmlspecialchars($row['deskripsi']) ?>"
                            data-status="<?= $row['status'] ?>"
                            data-tanggapan="<?= htmlspecialchars($row['tanggapan_admin'] ?? '') ?>"
                            data-foto="<?= $row['foto_path'] ? '../assets/uploads/keluhan/' . $row['foto_path'] : '' ?>"
                            style="padding:8px 14px; font-size:12px; border-radius:8px;">
                        <i class="fa-solid fa-eye mr-1"></i> Detail
                    </button>
                </td>
            </tr>
            <?php 
                } 
            } else {
                echo "<tr><td colspan='6' class='text-center py-10 text-slate-400 italic'>Belum ada keluhan masuk.</td></tr>";
            }
            ?>
            </tbody>
        </table>
        
        <div class="p-4 border-t border-slate-100 flex justify-center">
            <nav class="flex gap-2">
                <a href="?halaman=<?= max(1, $halaman-1) ?>" class="px-3 py-1 border rounded hover:bg-slate-50 text-sm text-slate-600 <?= ($halaman <= 1) ? 'pointer-events-none opacity-50' : '' ?>">Prev</a>
                <?php for($x = 1; $x <= $total_halaman; $x++): ?>
                    <a href="?halaman=<?= $x ?>" class="px-3 py-1 border rounded text-sm <?= ($halaman == $x) ? 'bg-blue-600 text-white border-blue-600' : 'hover:bg-slate-50 text-slate-600' ?>"><?= $x ?></a>
                <?php endfor; ?>
                <a href="?halaman=<?= min($total_halaman, $halaman+1) ?>" class="px-3 py-1 border rounded hover:bg-slate-50 text-sm text-slate-600 <?= ($halaman >= $total_halaman) ? 'pointer-events-none opacity-50' : '' ?>">Next</a>
            </nav>
        </div>
    </div>

    <div id="modalKeluhan" class="modal">
        <div class="modal-content">
            <div class="bg-slate-50 p-6 border-b border-slate-200">
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <h2 class="text-xl font-bold text-slate-800">Detail & Tindakan</h2>
                        <p class="text-sm text-slate-500">ID Keluhan: #<span id="m_id_disp"></span></p>
                    </div>
                    <span class="close text-slate-400 hover:text-red-500 text-3xl cursor-pointer font-bold leading-none">&times;</span>
                </div>

                <div class="flex items-center justify-between w-full max-w-md mx-auto mb-2">
                    <div class="flex flex-col items-center relative">
                        <div id="step_1" class="step-circle step-inactive w-10 h-10"><i class="fa-solid fa-exclamation"></i></div>
                        <span class="text-xs font-bold mt-2 text-slate-600">BARU</span>
                    </div>
                    <div id="line_1" class="step-line h-1"></div>
                    <div class="flex flex-col items-center relative">
                        <div id="step_2" class="step-circle step-inactive w-10 h-10"><i class="fa-solid fa-screwdriver-wrench"></i></div>
                        <span class="text-xs font-bold mt-2 text-slate-600">PROSES</span>
                    </div>
                    <div id="line_2" class="step-line h-1"></div>
                    <div class="flex flex-col items-center relative">
                        <div id="step_3" class="step-circle step-inactive w-10 h-10"><i class="fa-solid fa-check"></i></div>
                        <span class="text-xs font-bold mt-2 text-slate-600">SELESAI</span>
                    </div>
                </div>
            </div>
            
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm mb-4">
                        <h4 class="text-xs font-bold text-slate-400 uppercase mb-3 tracking-wider">Informasi Masalah</h4>
                        
                        <div class="mb-3">
                            <label class="text-xs text-slate-500 block">Judul</label>
                            <div id="m_judul" class="font-bold text-slate-800 text-lg"></div>
                        </div>
                        
                        <div class="flex gap-4 mb-3">
                            <div>
                                <label class="text-xs text-slate-500 block">Pelapor</label>
                                <div id="m_penghuni" class="font-semibold text-slate-700 text-sm"></div>
                            </div>
                            <div>
                                <label class="text-xs text-slate-500 block">Kamar</label>
                                <div id="m_kamar" class="font-semibold text-blue-600 text-sm bg-blue-50 px-2 rounded"></div>
                            </div>
                        </div>

                        <div>
                            <label class="text-xs text-slate-500 block mb-1">Deskripsi</label>
                            <div id="m_deskripsi" class="text-sm text-slate-600 bg-slate-50 p-3 rounded-lg border border-slate-100"></div>
                        </div>
                    </div>

                    <div id="m_foto_container" style="display:none;">
                        <h4 class="text-xs font-bold text-slate-400 uppercase mb-2 tracking-wider">Bukti Foto</h4>
                        <a id="m_foto_link" href="#" target="_blank" class="group relative block overflow-hidden rounded-xl border border-slate-200">
                            <img id="m_foto_img" src="" class="w-full h-40 object-cover transition transform group-hover:scale-105">
                            <div class="absolute inset-0 bg-black/30 flex items-center justify-center opacity-0 group-hover:opacity-100 transition">
                                <span class="text-white font-bold text-sm"><i class="fa-solid fa-magnifying-glass"></i> Perbesar</span>
                            </div>
                        </a>
                    </div>
                </div>

                <div class="bg-slate-50 p-5 rounded-xl border border-slate-200 h-fit">
                    <h4 class="text-sm font-bold text-slate-700 mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-user-gear text-blue-600"></i> Tindakan Admin
                    </h4>

                    <form action="keluhan_proses.php" method="POST">
                        <input type="hidden" name="id_keluhan" id="m_id">
                        
                        <div class="mb-4">
                            <label class="block text-xs font-bold text-slate-600 mb-1">Update Status</label>
                            <select name="status" id="m_status" class="w-full p-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-200 outline-none">
                                <option value="BARU">⭕ Baru (Belum Ditangani)</option>
                                <option value="PROSES">🛠️ Sedang Diproses</option>
                                <option value="SELESAI">✅ Selesai</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="block text-xs font-bold text-slate-600 mb-1">Pesan Balasan (Untuk Penghuni)</label>
                            <textarea name="tanggapan" class="w-full p-3 border border-slate-300 rounded-lg text-sm h-24 focus:ring-2 focus:ring-blue-200 outline-none" placeholder="Tulis update perbaikan disini..."></textarea>
                        </div>

                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 rounded-lg text-sm shadow-lg shadow-blue-200 transition transform hover:-translate-y-0.5">
                            <i class="fa-regular fa-paper-plane mr-2"></i> Simpan & Kirim
                        </button>
                    </form>
                    
                    <div class="mt-6 pt-4 border-t border-slate-200">
                        <label class="block text-xs font-bold text-slate-400 mb-2 uppercase">Riwayat Komunikasi</label>
                        <div id="m_history" class="text-xs text-slate-600 bg-white p-3 border border-slate-200 rounded-lg max-h-32 overflow-y-auto leading-relaxed"></div>
                    </div>

                    <div class="mt-4 text-center">
                        <a id="btn_hapus" href="#" onclick="return confirm('Yakin hapus data ini permanen?')" class="text-xs text-red-500 hover:text-red-700 hover:underline">Hapus Keluhan Ini</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

  </main>

  <script>
    const modal = document.getElementById("modalKeluhan");
    const span = document.getElementsByClassName("close")[0];

    // Helper: Reset Class Stepper (Modal)
    function resetStepper() {
        ['step_1', 'step_2', 'step_3'].forEach(id => {
            document.getElementById(id).className = 'step-circle step-inactive w-10 h-10';
        });
        ['line_1', 'line_2'].forEach(id => {
            document.getElementById(id).className = 'step-line bg-slate-200 h-1';
        });
    }

    // Helper: Update Stepper Visual (Modal)
    function updateStepper(status) {
        resetStepper();
        const s1 = document.getElementById('step_1');
        const s2 = document.getElementById('step_2');
        const s3 = document.getElementById('step_3');
        const l1 = document.getElementById('line_1');
        const l2 = document.getElementById('line_2');

        const baseClass = "step-circle w-10 h-10 ";

        if (status === 'BARU') {
            s1.className = baseClass + 'step-active-red';
        } else if (status === 'PROSES') {
            s1.className = baseClass + 'step-active-yellow'; 
            l1.className = 'step-line line-active-yellow h-1';
            s2.className = baseClass + 'step-active-yellow';
        } else if (status === 'SELESAI') {
            s1.className = baseClass + 'step-active-green';
            l1.className = 'step-line line-active-green h-1';
            s2.className = baseClass + 'step-active-green';
            l2.className = 'step-line line-active-green h-1';
            s3.className = baseClass + 'step-active-green';
        }
    }

    document.querySelectorAll('.open-modal').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('m_id').value = this.getAttribute('data-id');
            document.getElementById('m_id_disp').textContent = this.getAttribute('data-id');
            document.getElementById('m_penghuni').textContent = this.getAttribute('data-penghuni');
            document.getElementById('m_kamar').textContent = this.getAttribute('data-kamar');
            document.getElementById('m_judul').textContent = this.getAttribute('data-judul');
            document.getElementById('m_deskripsi').textContent = this.getAttribute('data-deskripsi');
            
            const status = this.getAttribute('data-status');
            document.getElementById('m_status').value = status;
            
            updateStepper(status);

            let history = this.getAttribute('data-tanggapan');
            document.getElementById('m_history').innerHTML = history ? history : '<span class="italic text-slate-400">Belum ada tanggapan.</span>';

            let foto = this.getAttribute('data-foto');
            if(foto) {
                document.getElementById('m_foto_container').style.display = 'block';
                document.getElementById('m_foto_img').src = foto;
                document.getElementById('m_foto_link').href = foto;
            } else {
                document.getElementById('m_foto_container').style.display = 'none';
            }

            document.getElementById('btn_hapus').href = 'keluhan_proses.php?act=hapus&id=' + this.getAttribute('data-id');
            modal.style.display = "block";
        });
    });

    span.onclick = function() { modal.style.display = "none"; }
    window.onclick = function(event) {
        if (event.target == modal) { modal.style.display = "none"; }
    }
  </script>
</body>
</html>