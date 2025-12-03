<?php
session_start();
require 'inc/koneksi.php';
require 'inc/csrf.php';
require 'inc/upload.php'; 

if (!isset($_SESSION['id_pengguna'])) { header('Location: login.php'); exit; }

$id_pengguna = $_SESSION['id_pengguna'];
$user = $mysqli->query("SELECT nama FROM pengguna WHERE id_pengguna=$id_pengguna")->fetch_assoc();

// Proses Tambah Keluhan
$msg = '';
if ($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['act']) && $_POST['act']=='tambah') {
    if (!csrf_check($_POST['csrf'])) {
        $msg = '<div class="bg-red-100 text-red-700 p-3 rounded mb-4">Token tidak valid! Refresh halaman.</div>';
    } else {
        $judul = htmlspecialchars($_POST['judul']);
        $desk = htmlspecialchars($_POST['deskripsi']);
        $prioritas = $_POST['prioritas'];
        
        $foto_path = null;
        if (!empty($_FILES['foto']['name'])) {
            $foto_path = upload_process($_FILES['foto'], 'keluhan'); 
        }

        $row_penghuni = $mysqli->query("SELECT id_penghuni FROM penghuni WHERE id_pengguna=$id_pengguna")->fetch_assoc();
        
        if ($row_penghuni) {
            $id_penghuni = $row_penghuni['id_penghuni'];
            // Ambil ID Kamar dari Kontrak Aktif
            $q_kamar = $mysqli->query("SELECT id_kamar FROM kontrak WHERE id_penghuni = $id_penghuni AND status='AKTIF'");
            $id_kamar = ($q_kamar->num_rows > 0) ? $q_kamar->fetch_object()->id_kamar : null;

            $stmt = $mysqli->prepare("INSERT INTO keluhan (id_penghuni, id_kamar, judul, deskripsi, prioritas, status, foto_path) VALUES (?, ?, ?, ?, ?, 'BARU', ?)");
            $stmt->bind_param('iissss', $id_penghuni, $id_kamar, $judul, $desk, $prioritas, $foto_path);
            
            if ($stmt->execute()) {
                $msg = '<div class="bg-green-100 text-green-700 p-3 rounded mb-4">✅ Keluhan berhasil dikirim! Admin akan segera merespon.</div>';
            } else {
                $msg = '<div class="bg-red-100 text-red-700 p-3 rounded mb-4">Gagal menyimpan data ke database.</div>';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Keluhan - SIKOS</title>
    <link rel="stylesheet" href="assets/css/app.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
      /* Styles untuk Stepper */
      .modal { display: none; position: fixed; z-index: 100; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5); backdrop-filter: blur(2px); }
      .modal-content { background-color: #fff; margin: 5% auto; padding: 0; border: none; width: 90%; max-width: 600px; border-radius: 16px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); animation: slideDown 0.3s ease-out; overflow: hidden; }
      @keyframes slideDown { from {transform: translateY(-20px); opacity: 0;} to {transform: translateY(0); opacity: 1;} }
      
      /* Base Stepper */
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

  <?php include 'components/sidebar_penghuni.php'; ?>

  <main class="main-content">
    <div class="flex justify-between items-center mb-6">
        <h2 style="font-size:24px; font-weight:700; color:#1e293b;">Layanan Keluhan</h2>
    </div>
    <?= $msg ?>

    <div class="card-white mb-8">       
        <h3 class="font-bold text-slate-800 mb-4 border-b pb-2">Ajukan Keluhan Baru</h3>
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
            <input type="hidden" name="act" value="tambah">
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div class="md:col-span-2">
                    <label class="form-label">Judul Masalah</label>
                    <input type="text" name="judul" class="form-input w-full" placeholder="Contoh: AC Bocor, Lampu Mati" required>
                </div>
                <div>
                    <label class="form-label">Prioritas</label>
                    <select name="prioritas" class="form-input w-full">
                        <option value="LOW">Rendah</option>
                        <option value="MEDIUM" selected>Sedang</option>
                        <option value="HIGH">Tinggi (Urgent)</option>
                    </select>
                </div>
            </div>
            
            <div class="mb-4">
                <label class="form-label">Deskripsi Detail</label>
                <textarea name="deskripsi" class="form-input w-full" rows="3" placeholder="Jelaskan detail kerusakan..." required></textarea>
            </div>

            <div class="mb-6">
                <label class="form-label">Foto Bukti (Opsional)</label>
                <div class="flex items-center gap-2">
                    <input type="file" name="foto" class="block w-full text-sm text-slate-500
                        file:mr-4 file:py-2 file:px-4
                        file:rounded-full file:border-0
                        file:text-sm file:font-semibold
                        file:bg-blue-50 file:text-blue-700
                        hover:file:bg-blue-100
                    " accept="image/*">
                </div>
                <p class="text-xs text-slate-400 mt-1">Maksimal 2MB. Format: JPG, PNG, WEBP.</p>
            </div>
            
            <button type="submit" class="btn-primary w-full py-3 font-bold rounded-lg shadow-lg hover:shadow-xl transition transform hover:-translate-y-0.5">
                <i class="fa-solid fa-paper-plane mr-2"></i> Kirim Laporan
            </button>
        </form>
    </div>

    <div class="card-white">
        <h3 class="font-bold text-slate-800 mb-4">Riwayat Keluhan Saya</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-slate-600">
                <thead class="text-xs text-slate-700 uppercase bg-slate-50">
                    <tr>
                        <th class="px-6 py-3">Tanggal</th>
                        <th class="px-6 py-3">Masalah</th>
                        <th class="px-6 py-3 text-center" style="width:200px;">Status Progress</th>
                        <th class="px-6 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $idp = $mysqli->query("SELECT id_penghuni FROM penghuni WHERE id_pengguna=$id_pengguna")->fetch_object()->id_penghuni ?? 0;
                $res = $mysqli->query("SELECT * FROM keluhan WHERE id_penghuni=$idp ORDER BY dibuat_at DESC");
                
                if ($res->num_rows > 0) {
                    while($row=$res->fetch_assoc()){
                        // --- LOGIC MINI STEPPER ---
                        $s1_cls = 'step-inactive'; $l1_cls = 'bg-slate-200';
                        $s2_cls = 'step-inactive'; $l2_cls = 'bg-slate-200';
                        $s3_cls = 'step-inactive';
                        $status_text = '';

                        if($row['status'] == 'BARU') {
                            $s1_cls = 'step-active-red'; 
                            $status_text = '<span class="text-red-500 font-bold text-[10px]">TERKIRIM</span>';
                        } elseif ($row['status'] == 'PROSES') {
                            $s1_cls = 'step-active-yellow';
                            $l1_cls = 'line-active-yellow';
                            $s2_cls = 'step-active-yellow';
                            $status_text = '<span class="text-yellow-600 font-bold text-[10px]">DIPROSES</span>';
                        } elseif ($row['status'] == 'SELESAI') {
                            $s1_cls = 'step-active-green';
                            $l1_cls = 'line-active-green';
                            $s2_cls = 'step-active-green';
                            $l2_cls = 'line-active-green';
                            $s3_cls = 'step-active-green';
                            $status_text = '<span class="text-green-600 font-bold text-[10px]">SELESAI</span>';
                        }
                ?>
                    <tr class="bg-white border-b hover:bg-slate-50 transition">
                        <td class="px-6 py-4"><?= date('d/m/Y', strtotime($row['dibuat_at'])) ?></td>
                        <td class="px-6 py-4">
                            <div class="font-bold text-slate-800"><?= htmlspecialchars($row['judul']) ?></div>
                            <div class="text-xs text-slate-500 truncate max-w-[200px]"><?= htmlspecialchars($row['deskripsi']) ?></div>
                        </td>
                        
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center w-full px-2">
                                <div class="step-circle <?= $s1_cls ?> w-5 h-5 text-[10px]">1</div>
                                <div class="step-line <?= $l1_cls ?> h-1"></div>
                                <div class="step-circle <?= $s2_cls ?> w-5 h-5 text-[10px]">2</div>
                                <div class="step-line <?= $l2_cls ?> h-1"></div>
                                <div class="step-circle <?= $s3_cls ?> w-5 h-5 text-[10px]">3</div>
                            </div>
                            <div class="mt-1"><?= $status_text ?></div>
                        </td>

                        <td class="px-6 py-4 text-center">
                            <button class="text-blue-600 hover:text-blue-800 font-medium text-xs border border-blue-200 px-3 py-1 rounded hover:bg-blue-50 open-modal transition"
                                data-judul="<?= htmlspecialchars($row['judul']) ?>"
                                data-deskripsi="<?= htmlspecialchars($row['deskripsi']) ?>"
                                data-status="<?= $row['status'] ?>"
                                data-tanggapan="<?= htmlspecialchars($row['tanggapan_admin'] ?? '') ?>"
                                data-foto="<?= $row['foto_path'] ? 'assets/uploads/keluhan/' . $row['foto_path'] : '' ?>">
                                Lihat Detail
                            </button>
                        </td>
                    </tr>
                <?php 
                    } 
                } else {
                    echo '<tr><td colspan="4" class="text-center py-8 text-slate-400">Belum ada riwayat keluhan.</td></tr>';
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="modalDetail" class="modal">
        <div class="modal-content">
            <div class="bg-slate-50 p-6 border-b border-slate-200 flex justify-between items-start">
                <div>
                    <h3 class="font-bold text-lg text-slate-800">Detail Perkembangan</h3>
                    <p class="text-xs text-slate-500 mt-1">Pantau status laporan Anda</p>
                </div>
                <span class="close text-slate-400 hover:text-red-500 text-2xl cursor-pointer font-bold">&times;</span>
            </div>

            <div class="p-6">
                <div class="flex items-center justify-between w-full max-w-sm mx-auto mb-8">
                    <div class="flex flex-col items-center relative">
                        <div id="step_1" class="step-circle step-inactive w-10 h-10 text-sm"><i class="fa-solid fa-file-pen"></i></div>
                        <span class="text-[10px] font-bold mt-2 text-slate-500">TERKIRIM</span>
                    </div>
                    <div id="line_1" class="step-line h-1"></div>
                    <div class="flex flex-col items-center relative">
                        <div id="step_2" class="step-circle step-inactive w-10 h-10 text-sm"><i class="fa-solid fa-wrench"></i></div>
                        <span class="text-[10px] font-bold mt-2 text-slate-500">DIPROSES</span>
                    </div>
                    <div id="line_2" class="step-line h-1"></div>
                    <div class="flex flex-col items-center relative">
                        <div id="step_3" class="step-circle step-inactive w-10 h-10 text-sm"><i class="fa-solid fa-check"></i></div>
                        <span class="text-[10px] font-bold mt-2 text-slate-500">SELESAI</span>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="bg-blue-50 p-4 rounded-lg border border-blue-100">
                        <h4 class="font-bold text-sm text-blue-800 mb-1" id="m_judul"></h4>
                        <p class="text-sm text-blue-600" id="m_deskripsi"></p>
                    </div>

                    <div id="m_foto_box" class="hidden">
                        <p class="text-xs font-bold text-slate-400 uppercase mb-2">Foto Anda</p>
                        <img id="m_foto" src="" class="h-32 rounded-lg border border-slate-200 object-cover">
                    </div>

                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase mb-2">Respon Admin / Teknisi</p>
                        <div id="m_tanggapan" class="bg-white p-4 rounded-lg border border-slate-200 text-sm text-slate-600 max-h-40 overflow-y-auto shadow-inner"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

  </main>

  <script>
    const modal = document.getElementById("modalDetail");
    const span = document.getElementsByClassName("close")[0];

    function updateStepper(status) {
        // Reset styles
        ['step_1', 'step_2', 'step_3'].forEach(id => document.getElementById(id).className = 'step-circle step-inactive w-10 h-10 text-sm');
        ['line_1', 'line_2'].forEach(id => document.getElementById(id).className = 'step-line bg-slate-200 h-1');

        const s1 = document.getElementById('step_1');
        const s2 = document.getElementById('step_2');
        const s3 = document.getElementById('step_3');
        const l1 = document.getElementById('line_1');
        const l2 = document.getElementById('line_2');

        const baseClass = "step-circle w-10 h-10 text-sm ";

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
            document.getElementById('m_judul').textContent = this.getAttribute('data-judul');
            document.getElementById('m_deskripsi').textContent = this.getAttribute('data-deskripsi');
            
            // Tanggapan
            const tanggapan = this.getAttribute('data-tanggapan');
            document.getElementById('m_tanggapan').innerHTML = tanggapan ? tanggapan : '<i class="text-slate-400">Belum ada respon dari admin.</i>';

            // Foto
            const foto = this.getAttribute('data-foto');
            const fotoBox = document.getElementById('m_foto_box');
            if(foto) {
                fotoBox.classList.remove('hidden');
                document.getElementById('m_foto').src = foto;
            } else {
                fotoBox.classList.add('hidden');
            }

            updateStepper(this.getAttribute('data-status'));
            modal.style.display = "block";
        });
    });

    span.onclick = function() { modal.style.display = "none"; }
    window.onclick = function(event) { if (event.target == modal) modal.style.display = "none"; }
  </script>
</body>
</html>