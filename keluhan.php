<?php
session_start();
require 'inc/koneksi.php';
require 'inc/csrf.php';

if (!isset($_SESSION['id_pengguna'])) { header('Location: login.php'); exit; }

$id_pengguna = $_SESSION['id_pengguna'];
$user = $mysqli->query("SELECT nama FROM pengguna WHERE id_pengguna=$id_pengguna")->fetch_assoc();

// Proses Tambah
$msg = '';
// --- KODE BARU: START ---
if ($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['act']) && $_POST['act']=='tambah') {
    require 'inc/upload.php'; // Panggil fungsi upload
    
    if (!csrf_check($_POST['csrf'])) {
        $msg = '<div class="alert-red">Token tidak valid! Refresh halaman.</div>';
    } else {
        $judul = htmlspecialchars($_POST['judul']);
        $desk = htmlspecialchars($_POST['deskripsi']);
        $prioritas = $_POST['prioritas'];
        
        // 1. Proses Upload Foto
        $foto_path = null;
        if (!empty($_FILES['foto']['name'])) {
            $foto_path = upload_process($_FILES['foto'], 'keluhan'); 
        }

        // 2. Ambil ID Penghuni
        $row_penghuni = $mysqli->query("SELECT id_penghuni FROM penghuni WHERE id_pengguna=$id_pengguna")->fetch_assoc();
        
        if ($row_penghuni) {
            // 3. Ambil ID Kamar dari Kontrak Aktif (Otomatis)
            $id_penghuni = $row_penghuni['id_penghuni'];
            $q_kamar = $mysqli->query("SELECT id_kamar FROM kontrak WHERE id_penghuni = $id_penghuni AND status='AKTIF'");
            $id_kamar = ($q_kamar->num_rows > 0) ? $q_kamar->fetch_object()->id_kamar : null;

            // 4. Simpan ke Database
            $stmt = $mysqli->prepare("INSERT INTO keluhan (id_penghuni, id_kamar, judul, deskripsi, prioritas, status, foto_path) VALUES (?, ?, ?, ?, ?, 'BARU', ?)");
            $stmt->bind_param('iissss', $id_penghuni, $id_kamar, $judul, $desk, $prioritas, $foto_path);
            
            if ($stmt->execute()) {
                $msg = '<div style="background:#dcfce7; color:#166534; padding:12px; border-radius:8px; margin-bottom:24px;">✅ Keluhan berhasil dikirim!</div>';
            } else {
                $msg = '<div style="background:#fee2e2; color:#dc2626; padding:12px; border-radius:8px; margin-bottom:24px;">Gagal menyimpan data.</div>';
            }
        }
    }
}
// --- KODE BARU: END ---
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Keluhan - SIKOS</title>
    <link rel="stylesheet" href="assets/css/app.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="dashboard-body">

  <aside class="sidebar">
    <div class="mb-8 flex items-center gap-3">
        <div style="width:40px; height:40px; background:#eff6ff; color:#2563eb; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:bold;">
            <?= substr($user['nama'],0,1) ?>
        </div>
        <div>
            <div style="font-weight:700; color:#1e293b; font-size:14px;"><?= htmlspecialchars($user['nama']) ?></div>
            <div style="font-size:12px; color:#64748b;">Penghuni</div>
        </div>
    </div>
    <nav style="flex:1;">
        <a href="penghuni_dashboard.php" class="sidebar-link"><i class="fa-solid fa-chart-pie w-6"></i> Dashboard</a>
        <a href="kamar_saya.php" class="sidebar-link"><i class="fa-solid fa-bed w-6"></i> Kamar Saya</a>
        <a href="tagihan_saya.php" class="sidebar-link"><i class="fa-solid fa-credit-card w-6"></i> Tagihan</a>
        <a href="keluhan.php" class="sidebar-link active"><i class="fa-solid fa-triangle-exclamation w-6"></i> Keluhan</a>
        <a href="pengumuman.php" class="sidebar-link"><i class="fa-solid fa-bullhorn w-6"></i> Info</a>
    </nav>
    <a href="logout.php" class="sidebar-link" style="color:#dc2626; margin-top:auto;">
        <i class="fa-solid fa-right-from-bracket w-6"></i> Logout
    </a>
  </aside>

  <main class="main-content">
    <h2 style="font-size:24px; font-weight:700; color:#1e293b; margin-bottom:24px;">Layanan Keluhan</h2>
    <?= $msg ?>

    <div class="card-white" style="margin-bottom:32px;">
        <h3 style="font-weight:700; color:#1e293b; margin-bottom:16px;">Ajukan Keluhan Baru</h3>
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
            <input type="hidden" name="act" value="tambah">
            
            <div style="margin-bottom:16px;">
                <label class="form-label">Judul Masalah</label>
                <input type="text" name="judul" class="form-input" placeholder="Contoh: AC Bocor" required>
            </div>
            
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px; margin-bottom:24px;">
                <div>
                    <label class="form-label">Prioritas</label>
                    <select name="prioritas" class="form-input">
                        <option value="LOW">Rendah</option>
                        <option value="MEDIUM" selected>Sedang</option>
                        <option value="HIGH">Tinggi (Urgent)</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Deskripsi</label>
                    <input type="text" name="deskripsi" class="form-input" placeholder="Jelaskan detailnya...">
                </div>
            </div>
            <div style="margin-bottom:24px;">
            <label class="form-label">Foto Bukti (Opsional)</label>
            <input type="file" name="foto" class="form-input" accept="image/*" style="padding:10px;">
            <p style="font-size:12px; color:#94a3b8; margin-top:4px;">Max 2MB (JPG, PNG, WEBP)</p>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; padding:12px;">
                <i class="fa-solid fa-paper-plane"></i> Kirim Laporan
            </button>
        </form>
    </div>

    <div class="card-white">
        <h3 style="font-weight:700; color:#1e293b; margin-bottom:16px;">Riwayat Keluhan</h3>
        <table style="width:100%; border-collapse:collapse; font-size:14px;">
            <thead>
                <tr style="background:#f8fafc; text-align:left;">
                    <th style="padding:12px; font-size:12px; color:#64748b;">TANGGAL</th>
                    <th style="padding:12px; font-size:12px; color:#64748b;">MASALAH</th>
                    <th style="padding:12px; font-size:12px; color:#64748b;">PRIORITAS</th>
                    <th style="padding:12px; font-size:12px; color:#64748b;">STATUS</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $idp = $mysqli->query("SELECT id_penghuni FROM penghuni WHERE id_pengguna=$id_pengguna")->fetch_object()->id_penghuni ?? 0;
            $res = $mysqli->query("SELECT * FROM keluhan WHERE id_penghuni=$idp ORDER BY dibuat_at DESC");
            while($row=$res->fetch_assoc()){
                $badge = 'background:#fef3c7; color:#d97706;'; // Pending
                if($row['status']=='SELESAI') $badge='background:#dcfce7; color:#166534;';
                if($row['status']=='PROSES') $badge='background:#dbeafe; color:#2563eb;';
            ?>
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:16px 12px;"><?= date('d/m/Y', strtotime($row['dibuat_at'])) ?></td>
                    <td style="padding:16px 12px;">
                        <div style="font-weight:600;"><?= htmlspecialchars($row['judul']) ?></div>
                        <div style="font-size:12px; color:#64748b;"><?= htmlspecialchars($row['deskripsi']) ?></div>
                    </td>
                    <td style="padding:16px 12px;"><?= $row['prioritas'] ?></td>
                    <td style="padding:16px 12px;">
                        <span style="padding:4px 10px; border-radius:99px; font-size:11px; font-weight:700; <?= $badge ?>"><?= $row['status'] ?></span>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
  </main>
</body>
</html>