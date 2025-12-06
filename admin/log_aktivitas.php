<?php
session_start();
require '../inc/koneksi.php';
require '../inc/guard.php';

// Validasi Admin/Owner
if (!is_admin() && !is_owner()) die('Forbidden');

$db = new Database();

// --- PAGINATION ---
$batas = 10; // Menampilkan 20 log per halaman
$halaman = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;
$halaman_awal = ($halaman > 1) ? ($halaman * $batas) - $batas : 0;

// Hitung Total Data
$total_data = $mysqli->query("SELECT COUNT(*) FROM log_aktivitas")->fetch_row()[0];
$total_halaman = ceil($total_data / $batas);

// Query Data Log
$sql = "SELECT l.*, u.nama 
        FROM log_aktivitas l 
        LEFT JOIN pengguna u ON l.id_pengguna = u.id_pengguna 
        ORDER BY l.waktu DESC 
        LIMIT $halaman_awal, $batas";

$res = $mysqli->query($sql);
$nomor = $halaman_awal + 1;
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <title>Log Aktivitas Sistem</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../assets/css/app.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="../assets/js/main.js"></script>
</head>
<body class="dashboard-body">

  <button class="sidebar-toggle" onclick="toggleSidebar()">☰</button>

  <?php include '../components/sidebar_admin.php'; ?>

  <main class="main-content">
    <div class="mb-8">
        <h1 class="font-bold text-xl">Log Aktivitas</h1>
        <p class="text-xs text-muted">Rekam jejak aktivitas pengguna di dalam sistem.</p>
    </div>

    <div class="card-white">
        <div style="overflow-x: auto;">
            <table style="width:100%;">
                <thead>
                    <tr>
                        <th>NO</th>
                        <th>WAKTU</th>
                        <th>USER</th>
                        <th>AKSI</th>
                        <th>KETERANGAN</th>
                    </tr>
                </thead>
                <tbody>
                <?php 
                if ($res->num_rows > 0) {
                    while ($log = $res->fetch_assoc()) {
                ?>
                    <tr>
                        <td class="text-center" style="color:var(--text-muted);"><?= $nomor++ ?></td>
                        <td style="white-space: nowrap;">
                            <div class="text-sm font-bold"><?= date('d/m/Y', strtotime($log['waktu'])) ?></div>
                            <div class="text-xs text-muted"><?= date('H:i:s', strtotime($log['waktu'])) ?></div>
                        </td>
                        <td>
                            <div class="font-bold text-sm"><?= htmlspecialchars($log['nama'] ?? 'System') ?></div>
                        </td>
                        <td>
                            <span style="background:#eff6ff; color:var(--primary); padding:4px 8px; border-radius:4px; font-size:10px; font-weight:bold; text-transform:uppercase;">
                                <?= htmlspecialchars($log['aksi']) ?>
                            </span>
                        </td>
                        <td class="text-sm text-muted">
                            <?= htmlspecialchars($log['keterangan']) ?>
                        </td>
                    </tr>
                <?php 
                    }
                } else {
                    echo "<tr><td colspan='5' class='text-center p-8 text-muted'>Belum ada aktivitas terekam.</td></tr>";
                }
                ?>
                </tbody>
            </table>
        </div>

        <div class="pagination-container" style="margin-top: 20px; display:flex; gap:5px; justify-content:center;">
            <?php
                $qs = $_GET;
                $prev = max(1, $halaman - 1);
                $next = min($total_halaman, $halaman + 1);

                $qs['halaman'] = $prev;
                $href_prev = ($halaman > 1) ? '?'.http_build_query($qs) : '#';
                
                $qs['halaman'] = $next;
                $href_next = ($halaman < $total_halaman) ? '?'.http_build_query($qs) : '#';
            ?>
            
            <a href="<?= $href_prev ?>" 
               class="btn btn-secondary text-xs <?= ($halaman <= 1) ? 'disabled' : '' ?>" 
               style="padding:6px 12px;">
               <i class="fa-solid fa-chevron-left"></i> Prev
            </a>

            <?php for($x = 1; $x <= $total_halaman; $x++):
                $qs = $_GET;
                $qs['halaman'] = $x;
                $href_page = '?'.http_build_query($qs);
            ?>
                <a href="<?= $href_page ?>" 
                   class="btn text-xs <?= ($halaman == $x) ? 'btn-primary' : 'btn-secondary' ?>" 
                   style="padding:6px 12px;"><?= $x ?></a>
            <?php endfor; ?>

            <a href="<?= $href_next ?>" 
               class="btn btn-secondary text-xs <?= ($halaman >= $total_halaman) ? 'disabled' : '' ?>" 
               style="padding:6px 12px;">
               Next <i class="fa-solid fa-chevron-right"></i>
            </a>
        </div>
    </div>
  </main>

</body>
</html>