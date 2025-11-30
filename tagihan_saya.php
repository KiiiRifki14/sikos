<?php
session_start();
require 'inc/koneksi.php';
require 'inc/csrf.php';

if (!isset($_SESSION['id_pengguna'])) { header('Location: login.php'); exit; }

$id_pengguna = $_SESSION['id_pengguna'];
$user = $mysqli->query("SELECT nama FROM pengguna WHERE id_pengguna=$id_pengguna")->fetch_assoc();
$db = new Database(); 

$kontrak = $mysqli->query("SELECT id_kontrak FROM kontrak 
    WHERE id_penghuni=(SELECT id_penghuni FROM penghuni WHERE id_pengguna=$id_pengguna) 
    AND status='AKTIF'")->fetch_assoc();

$status_msg = '';
if (isset($_GET['status']) && $_GET['status'] == 'sukses') {
    $status_msg = '<div style="background:#dcfce7; color:#166534; padding:12px; border-radius:8px; margin-bottom:24px; font-size:14px;">✅ Bukti pembayaran berhasil dikirim! Menunggu verifikasi admin.</div>';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Tagihan Saya - SIKOS</title>
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
        <a href="tagihan_saya.php" class="sidebar-link active"><i class="fa-solid fa-credit-card w-6"></i> Tagihan</a>
        <a href="keluhan.php" class="sidebar-link"><i class="fa-solid fa-triangle-exclamation w-6"></i> Keluhan</a>
        <a href="pengumuman.php" class="sidebar-link"><i class="fa-solid fa-bullhorn w-6"></i> Info</a>
    </nav>
    <a href="logout.php" class="sidebar-link" style="color:#dc2626; margin-top:auto;">
        <i class="fa-solid fa-right-from-bracket w-6"></i> Logout
    </a>
  </aside>

  <main class="main-content">
    <h2 style="font-size:24px; font-weight:700; color:#1e293b; margin-bottom:24px;">Tagihan & Pembayaran</h2>
    
    <?= $status_msg ?>

    <div class="card-white">
        <div style="border-bottom:1px solid #f1f5f9; padding-bottom:16px; margin-bottom:24px;">
            <h3 style="font-weight:700; color:#1e293b;">Riwayat Tagihan</h3>
        </div>
        
        <?php if(!$kontrak){ ?>
            <p style='color:#64748b; text-align:center; padding:40px;'>Belum ada kontrak aktif.</p>
        <?php } else { ?>
        
        <div style="overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse; font-size:14px;">
                <thead>
                    <tr style="background:#f8fafc; text-align:left;">
                        <th style="padding:12px; color:#64748b; font-size:12px; text-transform:uppercase;">Bulan</th>
                        <th style="padding:12px; color:#64748b; font-size:12px; text-transform:uppercase;">Nominal</th>
                        <th style="padding:12px; color:#64748b; font-size:12px; text-transform:uppercase;">Jatuh Tempo</th>
                        <th style="padding:12px; color:#64748b; font-size:12px; text-transform:uppercase;">Status</th>
                        <th style="padding:12px; color:#64748b; font-size:12px; text-transform:uppercase;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $res = $mysqli->query("SELECT * FROM tagihan WHERE id_kontrak={$kontrak['id_kontrak']} ORDER BY bulan_tagih DESC");
                while($row = $res->fetch_assoc()){
                    $statusBayar = $db->cek_status_pembayaran_terakhir($row['id_tagihan']);
                    
                    $badgeStyle = "background:#fee2e2; color:#991b1b;"; // Default Belum
                    $text = "BELUM BAYAR";
                    
                    if ($row['status'] == 'LUNAS') {
                        $badgeStyle = "background:#dcfce7; color:#166534;";
                        $text = "LUNAS";
                    } elseif ($statusBayar == 'PENDING') {
                        $badgeStyle = "background:#fef9c3; color:#854d0e;";
                        $text = "VERIFIKASI";
                    }
                ?>
                    <tr style="border-bottom:1px solid #f1f5f9;">
                        <td style="padding:16px 12px; font-weight:600;"><?= date('F Y', strtotime($row['bulan_tagih'])) ?></td>
                        <td style="padding:16px 12px;">Rp <?= number_format($row['nominal'], 0, ',', '.') ?></td>
                        <td style="padding:16px 12px; color:#64748b;"><?= date('d M Y', strtotime($row['jatuh_tempo'])) ?></td>
                        
                        <td style="padding:16px 12px;">
                            <span style="padding:4px 10px; border-radius:6px; font-size:11px; font-weight:700; <?= $badgeStyle ?>"><?= $text ?></span>
                        </td>

                        <td style="padding:16px 12px;">
                            <?php if($row['status'] == 'BELUM' && $statusBayar != 'PENDING'): ?>
                                <form method="post" action="pembayaran_tagihan.php" enctype="multipart/form-data" style="display:flex; gap:8px;">
                                    <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                                    <input type="hidden" name="id_tagihan" value="<?= $row['id_tagihan'] ?>">
                                    <input type="hidden" name="jumlah" value="<?= $row['nominal'] ?>">
                                    
                                    <label class="btn-secondary" style="padding:6px 12px; font-size:12px; cursor:pointer;">
                                        📂 Pilih File
                                        <input type="file" name="bukti" required style="display:none;" onchange="this.form.submit()">
                                    </label>
                                </form>

                            <?php elseif($statusBayar == 'PENDING'): ?>
                                <span style="font-size:12px; color:#ca8a04;">⏳ Menunggu</span>

                            <?php else: ?>
                                <div style="display:flex; flex-direction:column; gap:4px;">
                                    <span style="font-size:12px; color:#166534; font-weight:bold;">✔ Selesai</span>
                                    
                                    <?php
                                        // Cari ID Pembayaran sukses untuk tagihan ini
                                        $q_lunas = $mysqli->query("SELECT id_pembayaran FROM pembayaran WHERE ref_type='TAGIHAN' AND ref_id={$row['id_tagihan']} AND status='DITERIMA' ORDER BY id_pembayaran DESC LIMIT 1");
                                        $data_lunas = $q_lunas->fetch_row();
                                        $id_pay = $data_lunas[0] ?? 0;

                                        if($id_pay > 0):
                                    ?>
                                        <a href="cetak_kuitansi.php?id=<?= $id_pay ?>" target="_blank" style="font-size:11px; color:#2563eb; text-decoration:none; background:#eff6ff; padding:2px 6px; border-radius:4px; border:1px solid #bfdbfe; text-align:center;">
                                            🖨️ Kuitansi
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
        <?php } ?>
    </div>
  </main>
</body>
</html>