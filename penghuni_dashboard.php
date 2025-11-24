<?php
session_start();
require 'inc/koneksi.php';

if (!isset($_SESSION['id_pengguna']) || $_SESSION['peran']!='PENGHUNI') {
    header('Location: login.php'); exit;
}

$id_pengguna = $_SESSION['id_pengguna'];
$user = $mysqli->query("SELECT * FROM pengguna WHERE id_pengguna=$id_pengguna")->fetch_assoc();
$id_penghuni = $mysqli->query("SELECT id_penghuni FROM penghuni WHERE id_pengguna=$id_pengguna")->fetch_object()->id_penghuni ?? 0;

// Kontrak Aktif
$kontrak = null;
if($id_penghuni) {
    $kontrak = $mysqli->query("SELECT k.*, km.kode_kamar FROM kontrak k JOIN kamar km ON k.id_kamar=km.id_kamar WHERE k.id_penghuni=$id_penghuni AND k.status='AKTIF'")->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <title>Dashboard Penghuni</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="assets/css/app.css"/>
</head>
<body class="bg-slate-50">

  <div class="dashboard-layout">
    
    <aside class="sidebar">
        <div class="mb-8 px-2">
            <a href="index.php" class="flex items-center gap-3 mb-8">
                <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center text-white font-bold">S</div>
                <span class="font-bold text-slate-800 text-lg">SIKOS</span>
            </a>
            
            <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-xl border border-slate-100">
                <div class="w-10 h-10 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center font-bold">
                    <?= strtoupper(substr($user['nama'],0,2)) ?>
                </div>
                <div class="overflow-hidden">
                    <div class="font-bold text-sm text-slate-700 truncate"><?= htmlspecialchars($user['nama']) ?></div>
                    <div class="text-xs text-slate-400">Penghuni</div>
                </div>
            </div>
        </div>

        <nav class="flex-1">
            <a href="penghuni_dashboard.php" class="nav-link active">📊 Dashboard</a>
            <a href="kamar_saya.php" class="nav-link">🏠 Kamar Saya</a>
            <a href="tagihan_saya.php" class="nav-link">💳 Tagihan</a>
            <a href="keluhan.php" class="nav-link">📢 Keluhan</a>
        </nav>

        <a href="logout.php" class="nav-link text-red-600 hover:bg-red-50 mt-auto">🚪 Logout</a>
    </aside>

    <main class="main-content">
        <header class="mb-8">
            <h1 class="text-2xl font-bold text-slate-800">Overview</h1>
            <p class="text-slate-500 text-sm">Selamat datang kembali, <?= htmlspecialchars($user['nama']) ?>!</p>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="card flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-green-100 text-green-600 flex items-center justify-center text-xl">📝</div>
                <div>
                    <div class="text-xs text-slate-400 font-bold uppercase">Status Kontrak</div>
                    <div class="font-bold text-slate-800 text-lg">
                        <?= $kontrak ? 'AKTIF' : 'TIDAK AKTIF' ?>
                    </div>
                </div>
            </div>

            <div class="card flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xl">🛏️</div>
                <div>
                    <div class="text-xs text-slate-400 font-bold uppercase">Kamar</div>
                    <div class="font-bold text-slate-800 text-lg">
                        <?= $kontrak['kode_kamar'] ?? '-' ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="flex justify-between items-center mb-6">
                <h3 class="font-bold text-slate-800">Tagihan Terbaru</h3>
                <a href="tagihan_saya.php" class="text-sm text-blue-600 font-medium hover:underline">Lihat Semua</a>
            </div>
            <div class="text-center text-slate-400 py-8 text-sm">
                Silakan cek menu Tagihan untuk detail pembayaran.
            </div>
        </div>
    </main>

  </div>

</body>
</html>