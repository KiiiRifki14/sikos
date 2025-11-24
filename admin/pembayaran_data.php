<?php
session_start();
require '../inc/koneksi.php';
require '../inc/guard.php';
if (!is_admin() && !is_owner()) die('Forbidden');
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <title>Data Pembayaran</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="../assets/css/app.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="dashboard-body">

  <aside class="sidebar">
    <div class="mb-8 px-2 flex items-center gap-3">
        <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center text-white font-bold text-xl">A</div>
        <div>
            <h1 class="font-bold text-slate-800 text-lg">SIKOS Admin</h1>
            <p class="text-xs text-slate-400">Management Panel</p>
        </div>
    </div>

    <nav style="flex:1; overflow-y:auto;">
        <a href="index.php" class="sidebar-link"><i class="fa-solid fa-chart-pie w-6 text-blue-500"></i> Dashboard</a>
        <a href="kamar_data.php" class="sidebar-link"><i class="fa-solid fa-house-chimney w-6 text-orange-500"></i> Kelola Kamar</a>
        <a href="booking_data.php" class="sidebar-link"><i class="fa-solid fa-clipboard-list w-6 text-pink-500"></i> Booking</a>
        <a href="pembayaran_data.php" class="sidebar-link active"><i class="fa-solid fa-sack-dollar w-6 text-yellow-500"></i> Pembayaran</a>
        <a href="penghuni_data.php" class="sidebar-link"><i class="fa-solid fa-users w-6 text-purple-500"></i> Penghuni</a>
        <a href="keluhan_data.php" class="sidebar-link"><i class="fa-solid fa-triangle-exclamation w-6 text-red-500"></i> Komplain</a>
        <a href="laporan.php" class="sidebar-link"><i class="fa-solid fa-chart-line w-6 text-teal-500"></i> Laporan</a>
        <a href="settings.php" class="sidebar-link"><i class="fa-solid fa-sliders w-6 text-slate-500"></i> Pengaturan</a>
    </nav>

    <a href="../logout.php" class="sidebar-link text-red-600 hover:bg-red-50 mt-4">
        <i class="fa-solid fa-right-from-bracket w-6"></i> Logout
    </a>
  </aside>

  <main class="main-content">
    <div style="margin-bottom:32px;">
        <h1 style="font-size:24px; font-weight:700; color:#1e293b;">Verifikasi Pembayaran</h1>
    </div>

    <div class="card-white">
        <table style="width:100%; border-collapse:collapse; font-size:14px;">
            <thead>
                <tr style="background:#f8fafc; text-align:left; border-bottom:1px solid #e2e8f0;">
                    <th style="padding:16px; color:#64748b;">ID</th>
                    <th style="padding:16px; color:#64748b;">Tipe</th>
                    <th style="padding:16px; color:#64748b;">Jumlah</th>
                    <th style="padding:16px; color:#64748b;">Bukti</th>
                    <th style="padding:16px; color:#64748b;">Status</th>
                    <th style="padding:16px; color:#64748b;">Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $query = "SELECT p.* FROM pembayaran p WHERE p.status='PENDING' ORDER BY p.id_pembayaran DESC";
            $res = $mysqli->query($query);
            
            if ($res->num_rows > 0) {
                while($row = $res->fetch_assoc()){
            ?>
            <tr style="border-bottom:1px solid #f1f5f9;">
                <td style="padding:16px;">#<?= $row['id_pembayaran'] ?></td>
                <td style="padding:16px;">
                    <span style="background:#eff6ff; color:#2563eb; padding:4px 10px; border-radius:6px; font-size:12px; font-weight:600;">
                        <?= $row['ref_type'] ?>
                    </span>
                </td>
                <td style="padding:16px; font-weight:600; color:#1e293b;">Rp <?= number_format($row['jumlah'], 0, ',', '.') ?></td>
                <td style="padding:16px;">
                    <?php if($row['bukti_path']): ?>
                        <a href="../assets/uploads/bukti_tf/<?= htmlspecialchars($row['bukti_path']) ?>" target="_blank" class="text-blue-600 hover:underline font-medium">Lihat Bukti</a>
                    <?php else: ?>
                        <span style="color:#ef4444;">-</span>
                    <?php endif; ?>
                </td>
                <td style="padding:16px;">
                    <span style="background:#fef3c7; color:#d97706; padding:4px 10px; border-radius:6px; font-size:11px; font-weight:700;">PENDING</span>
                </td>
                <td style="padding:16px;">
                    <div style="display:flex; gap:8px;">
                        <a href="pembayaran_proses.php?act=terima&id=<?= $row['id_pembayaran'] ?>" class="btn-primary" style="font-size:12px; padding:6px 12px; text-decoration:none;" onclick="return confirm('Terima pembayaran ini?')">✓ Terima</a>
                        <a href="pembayaran_proses.php?act=tolak&id=<?= $row['id_pembayaran'] ?>" class="btn-secondary" style="font-size:12px; padding:6px 12px; text-decoration:none; color:#dc2626; border-color:#fca5a5;" onclick="return confirm('Tolak pembayaran ini?')">✕ Tolak</a>
                    </div>
                </td>
            </tr>
            <?php 
                }
            } else {
                echo "<tr><td colspan='6' style='padding:32px; text-align:center; color:#94a3b8;'>Tidak ada pembayaran pending.</td></tr>";
            }
            ?>
            </tbody>
        </table>
    </div>
    
    <div style="margin-top:32px;">
        <h3 style="font-size:18px; font-weight:700; color:#1e293b; margin-bottom:16px;">Riwayat Verifikasi</h3>
        <div class="card-white">
            <table style="width:100%; border-collapse:collapse; font-size:14px;">
                <thead>
                    <tr style="background:#f8fafc; text-align:left;">
                        <th style="padding:12px; color:#64748b; font-size:12px;">TANGGAL</th>
                        <th style="padding:12px; color:#64748b; font-size:12px;">TIPE</th>
                        <th style="padding:12px; color:#64748b; font-size:12px;">JUMLAH</th>
                        <th style="padding:12px; color:#64748b; font-size:12px;">STATUS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $hist = $mysqli->query("SELECT * FROM pembayaran WHERE status!='PENDING' ORDER BY waktu_verifikasi DESC LIMIT 10");
                    while($h = $hist->fetch_assoc()){
                        $statusColor = ($h['status']=='DITERIMA') ? 'color:#16a34a;' : 'color:#dc2626;';
                        $waktu = $h['waktu_verifikasi'] ? date('d/m/Y H:i', strtotime($h['waktu_verifikasi'])) : '-';
                    ?>
                    <tr style="border-bottom:1px solid #f1f5f9;">
                        <td style="padding:12px;"><?= $waktu ?></td>
                        <td style="padding:12px;"><?= $h['ref_type'] ?></td>
                        <td style="padding:12px; font-weight:600;">Rp <?= number_format($h['jumlah']) ?></td>
                        <td style="padding:12px; font-weight:700; font-size:12px; <?= $statusColor ?>"><?= $h['status'] ?></td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

  </main>
</body>
</html>