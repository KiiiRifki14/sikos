<?php
session_start();
require '../inc/koneksi.php';
require '../inc/guard.php';
if (!is_admin() && !is_owner()) die('Forbidden');

// Pagination
$batas = 10;
$halaman = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;
$halaman_awal = ($halaman > 1) ? ($halaman * $batas) - $batas : 0;

$total_data = $mysqli->query("SELECT COUNT(*) FROM pengeluaran")->fetch_row()[0];
$total_halaman = ceil($total_data / $batas);

$q = "SELECT * FROM pengeluaran ORDER BY tanggal DESC LIMIT $halaman_awal, $batas";
$res = $mysqli->query($q);
$nomor = $halaman_awal + 1;

// Hitung Total Pengeluaran Bulan Ini
$bulan_ini = date('Y-m');
$total_keluar = $mysqli->query("SELECT SUM(biaya) FROM pengeluaran WHERE DATE_FORMAT(tanggal, '%Y-%m') = '$bulan_ini'")->fetch_row()[0] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <title>Data Pengeluaran - SIKOS</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="../assets/css/app.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
      .modal { display: none; position: fixed; z-index: 50; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); backdrop-filter: blur(2px); }
      .modal-content { background-color: #fff; margin: 5% auto; padding: 24px; border-radius: 12px; width: 90%; max-width: 500px; animation: slideDown 0.3s; }
      @keyframes slideDown { from {transform: translateY(-20px); opacity: 0;} to {transform: translateY(0); opacity: 1;} }
  </style>
</head>
<body class="dashboard-body">

  <?php include '../components/sidebar_admin.php'; ?>
  
  <main class="main-content">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Pengeluaran Operasional</h1>
            <p class="text-slate-500 text-sm">Catat biaya listrik, air, perbaikan, dll.</p>
        </div>
        <button onclick="openModal()" class="btn-primary px-4 py-2 rounded-lg shadow-lg hover:-translate-y-0.5 transition">
            <i class="fa-solid fa-plus mr-2"></i> Catat Pengeluaran
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white p-5 rounded-xl border border-red-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 bg-red-50 text-red-600 rounded-full flex items-center justify-center text-xl">
                <i class="fa-solid fa-money-bill-wave"></i>
            </div>
            <div>
                <div class="text-xs font-bold text-slate-400 uppercase">Total Keluar (Bulan Ini)</div>
                <div class="text-xl font-bold text-slate-800">Rp <?= number_format($total_keluar) ?></div>
            </div>
        </div>
    </div>

    <div class="card-white overflow-hidden">
        <table class="w-full text-sm text-left text-slate-600">
            <thead class="text-xs text-slate-700 uppercase bg-slate-50">
                <tr>
                    <th class="px-6 py-3">No</th>
                    <th class="px-6 py-3">Tanggal</th>
                    <th class="px-6 py-3">Keperluan</th>
                    <th class="px-6 py-3">Biaya</th>
                    <th class="px-6 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php 
            if($res->num_rows > 0) {
                while($row = $res->fetch_assoc()): 
            ?>
                <tr class="bg-white border-b hover:bg-slate-50">
                    <td class="px-6 py-4 text-center"><?= $nomor++ ?></td>
                    <td class="px-6 py-4 font-medium"><?= date('d M Y', strtotime($row['tanggal'])) ?></td>
                    <td class="px-6 py-4">
                        <div class="font-bold text-slate-800"><?= htmlspecialchars($row['judul']) ?></div>
                        <div class="text-xs text-slate-400"><?= htmlspecialchars($row['deskripsi']) ?></div>
                    </td>
                    <td class="px-6 py-4 font-bold text-red-600">Rp <?= number_format($row['biaya']) ?></td>
                    <td class="px-6 py-4 text-center">
                        <a href="pengeluaran_proses.php?act=hapus&id=<?= $row['id_pengeluaran'] ?>" onclick="return confirm('Hapus data ini?')" class="text-red-500 hover:text-red-700">
                            <i class="fa-solid fa-trash"></i>
                        </a>
                    </td>
                </tr>
            <?php endwhile; } else { echo "<tr><td colspan='5' class='text-center py-8 text-slate-400'>Belum ada data pengeluaran.</td></tr>"; } ?>
            </tbody>
        </table>
    </div>

    <div id="modalAdd" class="modal">
        <div class="modal-content">
            <div class="flex justify-between items-center mb-4 border-b pb-2">
                <h3 class="font-bold text-lg text-slate-800">Catat Pengeluaran Baru</h3>
                <span onclick="closeModal()" class="cursor-pointer text-slate-400 hover:text-red-500 text-2xl">&times;</span>
            </div>
            <form action="pengeluaran_proses.php" method="POST">
                <input type="hidden" name="act" value="tambah">
                
                <div class="mb-4">
                    <label class="form-label text-xs">Judul Keperluan</label>
                    <input type="text" name="judul" class="form-input w-full" placeholder="Contoh: Bayar Listrik, Beli Sapu..." required>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="form-label text-xs">Tanggal</label>
                        <input type="date" name="tanggal" class="form-input w-full" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div>
                        <label class="form-label text-xs">Jumlah Biaya (Rp)</label>
                        <input type="number" name="biaya" class="form-input w-full" placeholder="0" required>
                    </div>
                </div>

                <div class="mb-6">
                    <label class="form-label text-xs">Catatan Detail (Opsional)</label>
                    <textarea name="deskripsi" class="form-input w-full" rows="2"></textarea>
                </div>

                <button type="submit" class="btn-primary w-full py-3 rounded-lg shadow-lg">Simpan Data</button>
            </form>
        </div>
    </div>

  </main>

  <script>
    function openModal() { document.getElementById('modalAdd').style.display = 'block'; }
    function closeModal() { document.getElementById('modalAdd').style.display = 'none'; }
    window.onclick = function(e) { if(e.target == document.getElementById('modalAdd')) closeModal(); }
  </script>
</body>
</html>