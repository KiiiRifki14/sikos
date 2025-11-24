<?php
session_start();
require 'inc/koneksi.php';

if (!isset($_SESSION['id_pengguna']) || $_SESSION['peran']!='PENGHUNI') {
    header('Location: login.php'); exit;
}

$id_pengguna = $_SESSION['id_pengguna'];

// Ambil Data User
$user = $mysqli->query("SELECT * FROM pengguna WHERE id_pengguna=$id_pengguna")->fetch_assoc();

// Cari ID Penghuni
$penghuni = $mysqli->query("SELECT id_penghuni FROM penghuni WHERE id_pengguna=$id_pengguna")->fetch_assoc();
$id_penghuni = $penghuni['id_penghuni'] ?? 0;

// Cari Kontrak Aktif
$kontrak = null;
if($id_penghuni) {
    $kontrak = $mysqli->query("SELECT k.*, km.kode_kamar, t.nama_tipe 
                               FROM kontrak k 
                               JOIN kamar km ON k.id_kamar=km.id_kamar 
                               JOIN tipe_kamar t ON km.id_tipe=t.id_tipe
                               WHERE k.id_penghuni=$id_penghuni AND k.status='AKTIF'")->fetch_assoc();
}

// Hitung Tagihan Pending
$tagihan_count = 0;
if($id_penghuni && $kontrak) {
    $q_tagihan = $mysqli->query("SELECT COUNT(*) FROM tagihan WHERE id_kontrak={$kontrak['id_kontrak']} AND status='BELUM'");
    $tagihan_count = $q_tagihan->fetch_row()[0] ?? 0;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Penghuni</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="assets/css/app.css"/>
</head>
<body>

<nav class="nav">
   <div class="nav-container">
     <a href="index.php" class="nav-logo">🏠 SIKOS</a>
     <div class="nav-links">
        <span class="text-sm text-gray-500">Selamat datang, <b><?= htmlspecialchars($user['nama']) ?></b></span>
     </div>
   </div>
</nav>

<div class="dashboard">
    <aside class="sidebar">
        <div class="text-center mb-6 pb-6 border-b border-gray-100">
            <div class="w-16 h-16 bg-blue-600 rounded-full flex items-center justify-center text-white text-xl font-bold mx-auto mb-3 shadow-lg">
                <?= strtoupper(substr($user['nama'], 0, 2)) ?>
            </div>
            <h3 class="font-bold text-gray-800"><?= htmlspecialchars($user['nama']) ?></h3>
            <p class="text-xs text-muted">Penghuni</p>
        </div>
        <nav class="flex flex-col gap-1">
            <a href="penghuni_dashboard.php" class="sidebar-link active">📊 Dashboard</a>
            <a href="kamar_saya.php" class="sidebar-link">🏠 Kamar Saya</a>
            <a href="tagihan_saya.php" class="sidebar-link">💳 Tagihan</a>
            <a href="keluhan.php" class="sidebar-link">📢 Keluhan</a>
            <a href="logout.php" class="sidebar-link text-red-500 hover:bg-red-50">🚪 Logout</a>
        </nav>
    </aside>

    <main>
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-2xl font-bold text-gray-800">Overview</h1>
            <span class="text-sm text-muted"><?= date('d M Y') ?></span>
        </div>

        <div class="grid grid-3 mb-8">
            <div class="card p-6 relative overflow-hidden">
                <div class="text-sm text-muted mb-1 font-medium uppercase">Status Kontrak</div>
                <?php if($kontrak): ?>
                    <div class="text-3xl font-bold text-green-500 mb-1">AKTIF</div>
                    <div class="text-xs text-gray-400">Berakhir: <?= date('d M Y', strtotime($kontrak['tanggal_selesai'])) ?></div>
                <?php else: ?>
                    <div class="text-2xl font-bold text-gray-400">TIDAK ADA</div>
                    <div class="text-xs text-gray-400"><a href="index.php" class="text-blue-500">Cari kamar?</a></div>
                <?php endif; ?>
            </div>

            <div class="card p-6">
                <div class="text-sm text-muted mb-1 font-medium uppercase">Kamar</div>
                <?php if($kontrak): ?>
                    <div class="text-3xl font-bold text-gray-800 mb-1"><?= $kontrak['kode_kamar'] ?></div>
                    <div class="text-xs text-gray-400"><?= $kontrak['nama_tipe'] ?></div>
                <?php else: ?>
                    <div class="text-xl font-bold text-gray-400">-</div>
                <?php endif; ?>
            </div>

            <div class="card p-6">
                <div class="text-sm text-muted mb-1 font-medium uppercase">Tagihan Pending</div>
                <?php if($tagihan_count > 0): ?>
                    <div class="text-3xl font-bold text-amber-500 mb-1"><?= $tagihan_count ?></div>
                    <div class="text-xs text-gray-400">Segera lunasi</div>
                <?php else: ?>
                    <div class="text-3xl font-bold text-blue-500 mb-1">0</div>
                    <div class="text-xs text-gray-400">Semua lunas</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="flex justify-between items-center mb-6 px-6 pt-2">
                <h2 class="text-lg font-bold text-gray-800">💳 Tagihan Terbaru</h2>
                <a href="tagihan_saya.php" class="text-sm text-blue-600 font-medium hover:underline">Lihat Semua</a>
            </div>
            
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Bulan</th>
                            <th>Jumlah</th>
                            <th>Jatuh Tempo</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    if($kontrak) {
                        // Ambil 3 tagihan terakhir
                        $res = $mysqli->query("SELECT * FROM tagihan WHERE id_kontrak={$kontrak['id_kontrak']} ORDER BY bulan_tagih DESC LIMIT 3");
                        if($res->num_rows > 0) {
                            while($row = $res->fetch_assoc()){
                                $statusClass = ($row['status'] == 'LUNAS') ? 'badge-success' : 'badge-danger';
                                if($row['status']=='BELUM') {
                                     // Cek pembayaran pending (memanfaatkan logika yg sebelumnya)
                                     $p = $mysqli->query("SELECT status FROM pembayaran WHERE ref_type='TAGIHAN' AND ref_id={$row['id_tagihan']} ORDER BY id_pembayaran DESC LIMIT 1")->fetch_assoc();
                                     if($p && $p['status']=='PENDING') $row['status'] = 'PENDING VERIF';
                                }
                    ?>
                        <tr>
                            <td class="font-medium"><?= date('F Y', strtotime($row['bulan_tagih'])) ?></td>
                            <td>Rp <?= number_format($row['nominal'],0,',','.') ?></td>
                            <td class="text-gray-500"><?= date('d M Y', strtotime($row['jatuh_tempo'])) ?></td>
                            <td><span class="badge <?= ($row['status']=='LUNAS')?'badge-success':'badge-danger' ?>"><?= $row['status'] ?></span></td>
                            <td>
                                <?php if($row['status'] == 'BELUM'): ?>
                                    <a href="tagihan_saya.php" class="btn btn-primary btn-sm">Bayar</a>
                                <?php else: ?>
                                    <span class="text-gray-400 text-xs">Selesai</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php 
                            }
                        } else {
                            echo "<tr><td colspan='5' class='text-center text-muted py-6'>Tidak ada data tagihan.</td></tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5' class='text-center text-muted py-6'>Belum ada kontrak sewa.</td></tr>";
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>
</div>

</body>
</html>