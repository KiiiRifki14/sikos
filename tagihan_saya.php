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
    $status_msg = '<div role="status" aria-live="polite" style="background:#dcfce7; color:#166534; padding:12px; border-radius:8px; margin-bottom:24px; font-size:14px;">✅ Bukti pembayaran berhasil dikirim! Menunggu verifikasi admin.</div>';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Tagihan Saya - SIKOS</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="stylesheet" href="assets/css/app.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="dashboard-body">

  <?php include 'components/sidebar_penghuni.php'; ?>

  <main class="main-content" aria-labelledby="billing-heading">
    <h2 id="billing-heading" style="font-size:22px; font-weight:700; color:#1e293b; margin-bottom:8px;">Tagihan & Pembayaran</h2>
    <p style="margin-top:0; color:#64748b; margin-bottom:18px;">Kelola dan unggah bukti pembayaran di halaman ini.</p>

    <?= $status_msg ?>

    <div class="card-white">
        <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #f1f5f9; padding-bottom:12px; margin-bottom:18px;">
            <h3 style="font-weight:700; color:#1e293b; margin:0;">Riwayat Tagihan</h3>
            <?php if($kontrak): ?>
                <a href="index.php#kamar" class="btn btn-secondary" style="padding:8px 12px;">Lihat Kamar</a>
            <?php endif; ?>
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
                            <span style="padding:6px 12px; border-radius:10px; font-size:12px; font-weight:700; <?= $badgeStyle ?>"><?= $text ?></span>
                        </td>

                        <td style="padding:16px 12px;">
                            <?php if($row['status'] == 'BELUM' && $statusBayar != 'PENDING'): ?>
                                <form method="post" action="pembayaran_tagihan.php" enctype="multipart/form-data" style="display:flex; gap:8px; align-items:center;">
                                    <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                                    <input type="hidden" name="id_tagihan" value="<?= $row['id_tagihan'] ?>">
                                    <input type="hidden" name="jumlah" value="<?= $row['nominal'] ?>">
                                    
                                    <label class="btn btn-secondary" style="padding:8px 10px; font-size:13px; cursor:pointer;">
                                        📂 Pilih File
                                        <input type="file" name="bukti" required style="display:none;" onchange="this.form.submit()">
                                    </label>
                                    <span style="font-size:13px; color:#64748b;">atau <a href="pembayaran_tagihan.php?id=<?= $row['id_tagihan'] ?>" style="color:var(--primary)">unggah manual</a></span>
                                </form>

                            <?php elseif($statusBayar == 'PENDING'): ?>
                                <span style="font-size:13px; color:#ca8a04;">⏳ Menunggu Verifikasi</span>

                            <?php else: ?>
                                <div style="display:flex; gap:8px; align-items:center;">
                                    <span style="font-size:13px; color:#166534; font-weight:700;">✔ Lunas</span>
                                    <?php
                                        $q_lunas = $mysqli->query("SELECT id_pembayaran FROM pembayaran WHERE ref_type='TAGIHAN' AND ref_id={$row['id_tagihan']} AND status='DITERIMA' ORDER BY id_pembayaran DESC LIMIT 1");
                                        $data_lunas = $q_lunas->fetch_row();
                                        $id_pay = $data_lunas[0] ?? 0;
                                        if($id_pay > 0):
                                    ?>
                                        <a href="cetak_kuitansi.php?id=<?= $id_pay ?>" target="_blank" class="btn btn-secondary" style="padding:6px 10px; font-size:13px;">
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