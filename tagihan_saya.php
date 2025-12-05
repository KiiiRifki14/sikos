<?php
session_start();
require 'inc/koneksi.php';
require 'inc/csrf.php';

if (!isset($_SESSION['id_pengguna'])) { header('Location: login.php'); exit; }

$id_pengguna = $_SESSION['id_pengguna'];
$user = $mysqli->query("SELECT nama FROM pengguna WHERE id_pengguna=$id_pengguna")->fetch_assoc();
$db = new Database(); 

// Ambil Kontrak untuk Cek Tagihan
$kontrak = $mysqli->query("SELECT id_kontrak FROM kontrak WHERE id_penghuni=(SELECT id_penghuni FROM penghuni WHERE id_pengguna=$id_pengguna) AND status='AKTIF'")->fetch_assoc();

// Notifikasi Sukses
$status_msg = '';
if (isset($_GET['status']) && $_GET['status'] == 'sukses') {
    $status_msg = '<div style="background:#dcfce7; color:#166534; padding:15px; border-radius:8px; margin-bottom:20px; border:1px solid #bbf7d0; font-size:14px;">
        <i class="fa-solid fa-circle-check mr-2"></i> Bukti pembayaran berhasil dikirim! Mohon tunggu verifikasi admin.
    </div>';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Tagihan Saya</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="stylesheet" href="assets/css/app.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
  <?php include 'components/sidebar_penghuni.php'; ?>
  <main class="main-content">
    
    <div class="mb-6">
        <h1 style="font-size:20px; font-weight:700; color:#1e293b;">Tagihan & Pembayaran</h1>
        <p style="font-size:13px; color:#64748b;">Riwayat tagihan bulanan sewa kamar Anda.</p>
    </div>

    <?= $status_msg ?>

    <div class="card-white" style="padding:0;">
        <?php if(!$kontrak): ?>
            <div style="padding:40px; text-align:center; color:#94a3b8;">
                <i class="fa-solid fa-file-invoice" style="font-size:40px; margin-bottom:15px; display:block;"></i>
                Belum ada kontrak sewa yang aktif.
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table style="width:100%; border-collapse:collapse; min-width:600px;">
                    <thead>
                        <tr style="background:#f8fafc; border-bottom:1px solid #e2e8f0; text-align:left;">
                            <th style="padding:15px 20px; font-size:12px; font-weight:700; color:#64748b; text-transform:uppercase;">Bulan Tagihan</th>
                            <th style="padding:15px 20px; font-size:12px; font-weight:700; color:#64748b; text-transform:uppercase;">Jatuh Tempo</th>
                            <th style="padding:15px 20px; font-size:12px; font-weight:700; color:#64748b; text-transform:uppercase;">Nominal</th>
                            <th style="padding:15px 20px; font-size:12px; font-weight:700; color:#64748b; text-transform:uppercase;">Status</th>
                            <th style="padding:15px 20px; font-size:12px; font-weight:700; color:#64748b; text-transform:uppercase; text-align:right;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $res = $mysqli->query("SELECT * FROM tagihan WHERE id_kontrak={$kontrak['id_kontrak']} ORDER BY bulan_tagih DESC");
                    if($res->num_rows > 0) {
                        while($row = $res->fetch_assoc()){
                            // Cek pembayaran terakhir
                            $statusBayar = $db->cek_status_pembayaran_terakhir($row['id_tagihan']);
                            
                            $badge = '<span style="background:#fee2e2; color:#dc2626; padding:4px 10px; border-radius:20px; font-size:11px; font-weight:700;">BELUM LUNAS</span>';
                            $row_style = '';

                            if($row['status'] == 'LUNAS') {
                                $badge = '<span style="background:#dcfce7; color:#166534; padding:4px 10px; border-radius:20px; font-size:11px; font-weight:700;">LUNAS</span>';
                            } elseif($statusBayar == 'PENDING') {
                                $badge = '<span style="background:#fef9c3; color:#ca8a04; padding:4px 10px; border-radius:20px; font-size:11px; font-weight:700;">DIPROSES</span>';
                                $row_style = 'background:#fffbeb;';
                            }
                    ?>
                        <tr style="border-bottom:1px solid #f1f5f9; <?= $row_style ?>">
                            <td style="padding:15px 20px; font-weight:600; color:#334155;">
                                <?= date('F Y', strtotime($row['bulan_tagih'])) ?>
                            </td>
                            <td style="padding:15px 20px; font-size:13px; color:#64748b;">
                                <?= date('d/m/Y', strtotime($row['jatuh_tempo'])) ?>
                            </td>
                            <td style="padding:15px 20px; font-weight:700; color:#1e293b;">
                                Rp <?= number_format($row['nominal']) ?>
                            </td>
                            <td style="padding:15px 20px;">
                                <?= $badge ?>
                            </td>
                            <td style="padding:15px 20px; text-align:right;">
                                <?php if($row['status']=='BELUM' && $statusBayar!='PENDING'): ?>
                                    <a href="pembayaran_tagihan.php?id=<?= $row['id_tagihan'] ?>" class="btn btn-primary" style="padding:6px 15px; font-size:12px; border-radius:6px;">Bayar Sekarang</a>
                                <?php elseif($row['status']=='LUNAS'): 
                                    // Ambil ID Pembayaran Sukses
                                    $q_pay = $mysqli->query("SELECT id_pembayaran FROM pembayaran WHERE ref_type='TAGIHAN' AND ref_id={$row['id_tagihan']} AND status='DITERIMA' ORDER BY id_pembayaran DESC LIMIT 1");
                                    $d_pay = $q_pay->fetch_row();
                                    if($d_pay):
                                ?>
                                    <a href="cetak_kuitansi.php?id=<?= $d_pay[0] ?>" target="_blank" style="color:#2563eb; font-size:13px; font-weight:600; text-decoration:none;">
                                        <i class="fa-solid fa-print"></i> Kuitansi
                                    </a>
                                <?php endif; endif; ?>
                            </td>
                        </tr>
                    <?php 
                        }
                    } else {
                        echo '<tr><td colspan="5" style="text-align:center; padding:30px; color:#94a3b8;">Tidak ada riwayat tagihan.</td></tr>';
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
  </main>
</body>
</html>