<?php
session_start();
require '../inc/koneksi.php';
require '../inc/guard.php';
if (!is_admin() && !is_owner()) die('Forbidden');

$db = new Database();
$logs = $db->ambil_log_aktivitas();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <title>Log Aktivitas</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="../assets/css/app.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="dashboard-body">

  <?php include '../components/sidebar_admin.php'; ?>

  <main class="main-content">
    <div style="margin-bottom:32px;">
        <h1 style="font-size:24px; font-weight:700; color:#1e293b;">Log Aktivitas Sistem</h1>
        <p style="color:#64748b;">Rekam jejak aktivitas pengguna di dalam sistem.</p>
    </div>

    <div class="card-white">
        <table style="width:100%; border-collapse:collapse; font-size:14px;">
            <thead>
                <tr style="background:#f8fafc; text-align:left; border-bottom:2px solid #e2e8f0;">
                    <th style="padding:12px; width:180px;">Waktu</th>
                    <th style="padding:12px; width:150px;">User</th>
                    <th style="padding:12px; width:120px;">Aksi</th>
                    <th style="padding:12px;">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($logs as $log): ?>
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:12px; color:#64748b;"><?= date('d/m/Y H:i', strtotime($log['waktu'])) ?></td>
                    <td style="padding:12px; font-weight:600;"><?= htmlspecialchars($log['nama'] ?? 'System') ?></td>
                    <td style="padding:12px;">
                        <span style="background:#eff6ff; color:#2563eb; padding:4px 8px; border-radius:4px; font-size:12px; font-weight:600;">
                            <?= htmlspecialchars($log['aksi']) ?>
                        </span>
                    </td>
                    <td style="padding:12px; color:#334155;"><?= htmlspecialchars($log['keterangan']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
  </main>
</body>
</html>