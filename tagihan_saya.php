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
    <style>
        /* Tabel Responsive */
        .table-container { 
            overflow-x: auto; 
            border-radius: 12px; 
            border: 1px solid #e2e8f0; 
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }
        table { width: 100%; border-collapse: collapse; min-width: 600px; }
        
        th { 
            background: #f8fafc; 
            padding: 16px 20px; 
            text-align: left; 
            font-size: 12px; 
            color: #64748b; 
            font-weight: 700;
            text-transform: uppercase; 
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e2e8f0; 
        }
        
        td { 
            padding: 16px 20px; 
            border-bottom: 1px solid #f1f5f9; 
            color: #334155; 
            font-size: 14px; 
            vertical-align: middle;
        }
        
        tr:hover td { background-color: #f8fafc; }
        tr:last-child td { border-bottom: none; }

        .badge { 
            padding: 6px 12px; 
            border-radius: 20px; 
            font-size: 11px; 
            font-weight: 700; 
            display: inline-block;
            letter-spacing: 0.3px;
        }
        .bg-lunas { background: #dcfce7; color: #15803d; }
        .bg-pending { background: #fef9c3; color: #a16207; }
        .bg-belum { background: #fee2e2; color: #b91c1c; }
    </style>
</head>
<body class="role-penghuni">
  <?php include 'components/sidebar_penghuni.php'; ?>
  <main class="main-content animate-fade-up">

    <div style="margin-bottom: 30px;">
        <h1 style="font-size: 20px; font-weight: 700; color: #1e293b; margin-bottom: 5px;">Tagihan & Pembayaran</h1>
        <p style="font-size: 13px; color: #64748b;">Riwayat tagihan bulanan Anda.</p>
    </div>

    <div class="card-white" style="padding: 0;">
        <?php if(!$kontrak): ?>
            <div style="padding:40px; text-align:center; color:#94a3b8;">Belum ada kontrak aktif.</div>
        <?php else: ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Bulan</th>
                            <th>Jatuh Tempo</th>
                            <th>Nominal</th>
                            <th>Status</th>
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
                            $badgeClass = 'bg-belum'; $statusText = 'BELUM LUNAS';
                            if($row['status'] == 'LUNAS') { $badgeClass='bg-lunas'; $statusText='LUNAS'; }
                            elseif($statusBayar == 'PENDING') { $badgeClass='bg-pending'; $statusText='DIPROSES'; }
                    ?>
                        <tr>
                            <td style="font-weight: 600;"><?= date('F Y', strtotime($row['bulan_tagih'])) ?></td>
                            <td style="font-size: 13px; color: #64748b;"><?= date('d/m/Y', strtotime($row['jatuh_tempo'])) ?></td>
                            <td style="font-weight: 700;">Rp <?= number_format($row['nominal']) ?></td>
                            <td><span class="badge <?= $badgeClass ?>"><?= $statusText ?></span></td>
                            <td style="text-align:center;">
                                <?php if($row['status']=='BELUM' && $statusBayar!='PENDING'): ?>
                                    <a href="pembayaran_tagihan.php?id=<?= $row['id_tagihan'] ?>" class="btn btn-primary" style="padding: 5px 12px; font-size: 12px;">Bayar</a>
                                <?php elseif($row['status']=='LUNAS'): ?>
                                    <span style="font-size:12px; color:#2563eb;">✔ Selesai</span>
                                <?php else: ?>
                                    <span style="font-size:12px; color:#ca8a04;">Menunggu</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php 
                        }
                    } else {
                        echo '<tr><td colspan="5" style="text-align:center; padding:20px;">Tidak ada data tagihan.</td></tr>';
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