<?php
session_start();
require 'inc/koneksi.php';
require 'inc/csrf.php';

if (!isset($_SESSION['id_pengguna'])) { header('Location: login.php'); exit; }

$id_pengguna = $_SESSION['id_pengguna'];
$db = new Database(); 

// Cari kontrak aktif
$q_kontrak = "SELECT id_kontrak FROM kontrak WHERE id_penghuni=(SELECT id_penghuni FROM penghuni WHERE id_pengguna=$id_pengguna) AND status='AKTIF'";
$kontrak = $mysqli->query($q_kontrak)->fetch_assoc();
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
<body class="role-penghuni">
  <?php include 'components/sidebar_penghuni.php'; ?>
  <main class="main-content animate-fade-up">

    <div style="margin-bottom: 25px;">
        <h1 class="text-xl font-bold text-main">Tagihan Saya</h1>
        <p class="text-sm text-muted">Kelola riwayat pembayaran dan tagihan sewa kost Anda.</p>
    </div>

    <div class="card-white" style="padding: 0; overflow:hidden;">
        <?php if(!$kontrak): ?>
            <div style="padding:60px; text-align:center; display:flex; flex-direction:column; align-items:center;">
                <div style="width:80px; height:80px; background:#f1f5f9; border-radius:50%; display:flex; align-items:center; justify-content:center; margin-bottom:20px;">
                    <i class="fa-solid fa-file-invoice" style="font-size:32px; color:#cbd5e1;"></i>
                </div>
                <h3 class="font-bold text-main text-lg mb-2">Belum Ada Tagihan</h3>
                <p class="text-muted text-sm">Anda belum memiliki kontrak aktif saat ini.</p>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Bulan Tagihan</th>
                            <th>Jatuh Tempo</th>
                            <th>Nominal</th>
                            <th>Status Pembayaran</th>
                            <th style="text-align:center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $res = $mysqli->query("SELECT * FROM tagihan WHERE id_kontrak={$kontrak['id_kontrak']} ORDER BY bulan_tagih DESC");
                    if($res->num_rows > 0) {
                        while($row = $res->fetch_assoc()){
                            $statusBayar = $db->cek_status_pembayaran_terakhir($row['id_tagihan']);
                            
                            // Tentukan Warna Badge
                            $badgeClass = 'badge-danger'; $statusText = 'BELUM LUNAS';
                            if($row['status'] == 'LUNAS') { $badgeClass='badge-success'; $statusText='LUNAS'; }
                            elseif($statusBayar == 'PENDING') { $badgeClass='badge-warning'; $statusText='DIPROSES'; }
                    ?>
                        <tr>
                            <td class="font-bold text-main"><?= date('F Y', strtotime($row['bulan_tagih'])) ?></td>
                            <td class="text-muted text-sm"><?= date('d F Y', strtotime($row['jatuh_tempo'])) ?></td>
                            <td class="font-bold text-primary">Rp <?= number_format($row['nominal']) ?></td>
                            <td><span class="badge <?= $badgeClass ?>"><?= $statusText ?></span></td>
                            <td style="text-align:center;">
                                <?php if($row['status']=='BELUM' && $statusBayar!='PENDING'): ?>
                                    <a href="pembayaran_tagihan.php?id=<?= $row['id_tagihan'] ?>" class="btn btn-primary" style="padding: 8px 16px; font-size: 12px; border-radius:8px;">
                                        <i class="fa-solid fa-credit-card mr-1"></i> Bayar
                                    </a>
                                <?php elseif($row['status']=='LUNAS'): ?>
                                    <div style="width:32px; height:32px; background:#dcfce7; color:#166534; border-radius:50%; display:inline-flex; align-items:center; justify-content:center;">
                                        <i class="fa-solid fa-check"></i>
                                    </div>
                                <?php else: ?>
                                    <span class="text-xs font-bold text-muted"><i class="fa-solid fa-clock opacity-50 mr-1"></i> Menunggu Verifikasi</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php 
                        }
                    } else {
                        echo '<tr><td colspan="5" style="text-align:center; padding:40px; color:#94a3b8; font-style:italic;">Tidak ada riwayat tagihan.</td></tr>';
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