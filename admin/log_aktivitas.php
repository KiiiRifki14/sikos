<?php
session_start();
require '../inc/koneksi.php';
require '../inc/guard.php';

if (!is_admin() && !is_owner()) {
    header("Location: ../login.php");
    exit;
}

$db = new Database();
$mysqli = $db->koneksi;

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// SAFE REFACTOR
$total_data = $db->fetch_single_value("SELECT COUNT(*) FROM log_aktivitas l LEFT JOIN pengguna u ON l.id_pengguna = u.id_pengguna WHERE u.peran IN ('ADMIN', 'OWNER')");
$total_pages = ceil($total_data / $limit);

$sql = "SELECT l.*, u.nama 
        FROM log_aktivitas l 
        LEFT JOIN pengguna u ON l.id_pengguna = u.id_pengguna 
        WHERE u.peran IN ('ADMIN', 'OWNER')
        ORDER BY l.waktu DESC 
        LIMIT $offset, $limit";
$logs = $mysqli->query($sql);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Aktivitas Admin - SIKOS</title>
    <link rel="stylesheet" href="../assets/css/app.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="../assets/js/main.js"></script>
</head>

<body class="dashboard-body">

    <button class="sidebar-toggle" onclick="toggleSidebar()">☰</button>
    <?php include '../components/sidebar_admin.php'; ?>

    <main class="main-content">
        <div class="mb-8">
            <h1 class="font-bold text-xl">Log Aktivitas Admin</h1>
            <p class="text-xs text-muted">Rekaman jejak aktivitas admin dan owner.</p>
        </div>

        <div class="card-white" style="padding: 0;">
            <div style="overflow-x: auto;">
                <table style="width:100%;">
                    <thead>
                        <tr>
                            <th style="padding: 16px 24px;">WAKTU</th>
                            <th style="padding: 16px 24px;">ADMIN</th>
                            <th style="padding: 16px 24px;">AKSI</th>
                            <th style="padding: 16px 24px;">KETERANGAN</th>
                        </tr>
                    </thead>
                    <tbody style="font-size: 14px;">
                        <?php if ($logs->num_rows > 0): ?>
                            <?php while ($row = $logs->fetch_assoc()): ?>
                                <tr style="border-bottom: 1px solid #f1f5f9;">
                                    <td style="padding: 12px 24px; color: #64748b;">
                                        <?= date('d M Y H:i', strtotime($row['waktu'])) ?>
                                    </td>
                                    <td style="padding: 12px 24px; font-weight: bold;">
                                        <?= htmlspecialchars($row['nama']) ?>
                                    </td>
                                    <td style="padding: 12px 24px;">
                                        <?php
                                        $badgeClass = 'background:#f1f5f9; color:#475569;';
                                        if (strpos($row['aksi'], 'LOGIN') !== false) $badgeClass = 'background:#dbeafe; color:#1e40af;';
                                        elseif (strpos($row['aksi'], 'DELETE') !== false) $badgeClass = 'background:#fee2e2; color:#991b1b;';
                                        elseif (strpos($row['aksi'], 'UPDATE') !== false) $badgeClass = 'background:#fef3c7; color:#92400e;';
                                        ?>
                                        <span style="<?= $badgeClass ?> padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold;">
                                            <?= $row['aksi'] ?>
                                        </span>
                                    </td>
                                    <td style="padding: 12px 24px; color: #475569;">
                                        <?= htmlspecialchars($row['keterangan']) ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="text-align:center; padding: 40px; color: #94a3b8; font-style: italic;">
                                    Belum ada aktivitas terekam.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($total_pages > 1): ?>
                <div style="padding: 20px 24px; display: flex; justify-content: center; gap: 8px;">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?= $i ?>" style="
                        padding: 6px 12px; 
                        border-radius: 6px; 
                        font-size: 12px; 
                        font-weight: bold;
                        text-decoration: none;
                        <?= $i == $page ? 'background: var(--primary); color: white;' : 'background: #f1f5f9; color: #475569;' ?>
                    ">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

</body>

</html>