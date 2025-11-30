<?php
session_start();
require '../inc/koneksi.php';
require '../inc/guard.php';
if (!is_admin() && !is_owner()) { header('Location: ../login.php'); exit; }

// --- LOGIKA STATISTIK ---
$total_kamar = $mysqli->query("SELECT COUNT(*) FROM kamar")->fetch_row()[0];
$terisi = $mysqli->query("SELECT COUNT(*) FROM kamar WHERE status_kamar='TERISI'")->fetch_row()[0];
$occupancy_rate = ($total_kamar > 0) ? round(($terisi / $total_kamar) * 100) : 0;

// Hitung Pendapatan
$bulan_ini = date('m');
$tahun_ini = date('Y');
$query_omset = "SELECT SUM(jumlah) FROM pembayaran WHERE status='DITERIMA' AND MONTH(waktu_verifikasi) = '$bulan_ini' AND YEAR(waktu_verifikasi) = '$tahun_ini'";
$omset_raw = $mysqli->query($query_omset)->fetch_row()[0] ?? 0;

if ($omset_raw >= 1000000) {
    $omset_display = number_format($omset_raw / 1000000, 1) . " Jt";
} else {
    $omset_display = number_format($omset_raw);
}

// Data Pending
$booking = $mysqli->query("SELECT COUNT(*) FROM booking WHERE status='PENDING'")->fetch_row()[0];
$tagihan_pending = $mysqli->query("SELECT COUNT(*) FROM pembayaran WHERE status='PENDING'")->fetch_row()[0];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <title>Admin Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="../assets/css/app.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="dashboard-body">

    <?php include '../components/sidebar_admin.php'; ?>

    <main class="main-content">
        <div style="margin-bottom:32px;">
            <h1 style="font-size:24px; font-weight:700; color:#1e293b;">Dashboard Owner</h1>
        </div>

        <div class="grid-stats">
            <div class="card-white p-6">
                <div style="font-size:12px; font-weight:700; color:#94a3b8; text-transform:uppercase; margin-bottom:8px;">Occupancy Rate</div>
                <div style="font-size:36px; font-weight:700; color:#1e293b; line-height:1; margin-bottom:4px;"><?= $occupancy_rate ?>%</div>
                <div style="font-size:13px; color:#64748b;"><?= $terisi ?> dari <?= $total_kamar ?> kamar</div>
            </div>

            <div class="card-white p-6">
                <div style="font-size:12px; font-weight:700; color:#94a3b8; text-transform:uppercase; margin-bottom:8px;">Pendapatan</div>
                <div style="font-size:36px; font-weight:700; color:#10b981; line-height:1; margin-bottom:4px;"><?= $omset_display ?></div>
                <div style="font-size:13px; color:#64748b;">Bulan ini</div>
            </div>

            <div class="card-white p-6">
                <div style="font-size:12px; font-weight:700; color:#94a3b8; text-transform:uppercase; margin-bottom:8px;">Booking Pending</div>
                <div style="font-size:36px; font-weight:700; color:#f59e0b; line-height:1; margin-bottom:4px;"><?= $booking ?></div>
                <div style="font-size:13px; color:#64748b;">Perlu verifikasi</div>
            </div>

            <div class="card-white p-6">
                <div style="font-size:12px; font-weight:700; color:#94a3b8; text-transform:uppercase; margin-bottom:8px;">Bukti Bayar</div>
                <div style="font-size:36px; font-weight:700; color:#2563eb; line-height:1; margin-bottom:4px;"><?= $tagihan_pending ?></div>
                <div style="font-size:13px; color:#64748b;">Perlu dicek</div>
            </div>
        </div>
        
        <div class="card-white">
            <h3 style="font-size:16px; font-weight:700; color:#1e293b; margin-bottom:20px;">📝 Booking Pending</h3>
            <table style="width:100%; border-collapse:collapse; font-size:14px;">
                <thead>
                    <tr style="background:#f8fafc; text-align:left; border-bottom:1px solid #e2e8f0;">
                        <th style="padding:12px; color:#64748b;">NAMA</th>
                        <th style="padding:12px; color:#64748b;">KAMAR</th>
                        <th style="padding:12px; color:#64748b;">TANGGAL</th>
                        <th style="padding:12px; color:#64748b;">STATUS</th>
                        <th style="padding:12px; color:#64748b;">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $bk = $mysqli->query("SELECT b.*, u.nama, u.no_hp, k.kode_kamar, t.nama_tipe FROM booking b JOIN pengguna u ON b.id_pengguna=u.id_pengguna JOIN kamar k ON b.id_kamar=k.id_kamar JOIN tipe_kamar t ON k.id_tipe=t.id_tipe WHERE b.status='PENDING' ORDER BY b.tanggal_booking DESC LIMIT 5");
                    if($bk->num_rows > 0) {
                        while($b = $bk->fetch_assoc()){
                    ?>
                    <tr style="border-bottom:1px solid #f1f5f9;">
                        <td style="padding:16px 12px;">
                            <div style="font-weight:700; color:#1e293b;"><?= htmlspecialchars($b['nama']) ?></div>
                            <div style="font-size:12px; color:#64748b;">📞 <?= htmlspecialchars($b['no_hp']) ?></div>
                        </td>
                        <td style="padding:16px 12px;">
                            <div><?= $b['kode_kamar'] ?></div>
                            <div style="font-size:12px; color:#64748b;"><?= $b['nama_tipe'] ?></div>
                        </td>
                        <td style="padding:16px 12px;"><?= date('d M Y', strtotime($b['tanggal_booking'])) ?></td>
                        <td style="padding:16px 12px;">
                            <span style="background:#fef3c7; color:#d97706; padding:4px 10px; border-radius:6px; font-size:11px; font-weight:700;">PENDING</span>
                        </td>
                        <td style="padding:16px 12px;">
                            <div style="display:flex; gap:8px;">
                                <a href="booking_proses.php?act=approve&id=<?= $b['id_booking'] ?>" class="btn-primary" style="padding:6px 12px; font-size:12px; text-decoration:none;">✓ Terima</a>
                                <a href="booking_proses.php?act=reject&id=<?= $b['id_booking'] ?>" class="btn-secondary" style="padding:6px 12px; font-size:12px; text-decoration:none;">✕ Tolak</a>
                            </div>
                        </td>
                    </tr>
                    <?php 
                        }
                    } else {
                        echo "<tr><td colspan='5' style='padding:20px; text-align:center; color:#94a3b8;'>Tidak ada booking baru.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </main>

</body>
</html>