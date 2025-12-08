<?php
session_start();
require '../inc/koneksi.php';
require '../inc/guard.php';

if (!is_admin() && !is_owner()) { die("Akses Ditolak"); }

$msg = "";
$db = new Database();
$mysqli = $db->koneksi;

// --- HANDLE FIX ACTIONS ---
if (isset($_POST['action'])) {
    if ($_POST['action'] === 'fix_orphan_room') {
        $id_kamar = (int)$_POST['id_kamar'];
        $mysqli->query("UPDATE kamar SET status_kamar='TERSEDIA' WHERE id_kamar=$id_kamar");
        $msg = "✅ Kamar berhasil di-reset menjadi TERSEDIA.";
    }
    
    if ($_POST['action'] === 'fix_double_contract') {
        $id_kontrak = (int)$_POST['id_kontrak'];
        $id_kamar = (int)$_POST['id_kamar'];
        
        $mysqli->begin_transaction();
        try {
            // Batalkan kontrak
            $mysqli->query("UPDATE kontrak SET status='BATAL' WHERE id_kontrak=$id_kontrak");
            // Reset kamar
            $mysqli->query("UPDATE kamar SET status_kamar='TERSEDIA' WHERE id_kamar=$id_kamar");
            $mysqli->commit();
            $msg = "✅ Kontrak ganda berhasil dibatalkan dan kamar di-reset.";
        } catch (Exception $e) {
            $mysqli->rollback();
            $msg = "❌ Gagal: " . $e->getMessage();
        }
    }

    if ($_POST['action'] === 'fix_duration') {
        $id_kontrak = (int)$_POST['id_kontrak'];
        $mysqli->query("UPDATE kontrak SET tanggal_selesai = DATE_ADD(tanggal_mulai, INTERVAL 12 MONTH) WHERE id_kontrak=$id_kontrak");
        $msg = "✅ Durasi kontrak diperbaiki menjadi 12 bulan.";
    }
}

// --- DIAGNOSTICS ---

// 1. Orphaned Rooms (Terisi tapi tidak ada kontrak aktif)
$q_orphan = "SELECT k.*, t.nama_tipe 
             FROM kamar k 
             JOIN tipe_kamar t ON k.id_tipe=t.id_tipe
             WHERE k.status_kamar='TERISI' 
             AND k.id_kamar NOT IN (SELECT id_kamar FROM kontrak WHERE status='AKTIF')";
$orphans = $mysqli->query($q_orphan)->fetch_all(MYSQLI_ASSOC);

// 2. Double Bookings (User punya > 1 kontrak aktif)
$q_double = "SELECT u.nama, p.id_penghuni, COUNT(ko.id_kontrak) as jumlah_kontrak 
             FROM penghuni p
             JOIN pengguna u ON p.id_pengguna=u.id_pengguna
             JOIN kontrak ko ON p.id_penghuni=ko.id_penghuni
             WHERE ko.status='AKTIF'
             GROUP BY p.id_penghuni
             HAVING jumlah_kontrak > 1";
$doubles = $mysqli->query($q_double)->fetch_all(MYSQLI_ASSOC);

// Detail Double Bookings
$double_details = [];
foreach($doubles as $d) {
    $idp = $d['id_penghuni'];
    $q_det = "SELECT ko.*, k.kode_kamar FROM kontrak ko JOIN kamar k ON ko.id_kamar=k.id_kamar WHERE ko.id_penghuni=$idp AND ko.status='AKTIF' ORDER BY ko.tanggal_mulai DESC";
    $double_details[$idp] = $mysqli->query($q_det)->fetch_all(MYSQLI_ASSOC);
}

// 3. Invalid Duration (Overflow Integers or > 36 months)
// Kita hitung durasi via selisih tanggal
$q_bad_duration = "SELECT ko.*, u.nama, k.kode_kamar, TIMESTAMPDIFF(MONTH, ko.tanggal_mulai, ko.tanggal_selesai) as durasi_bulan
                   FROM kontrak ko 
                   JOIN penghuni p ON ko.id_penghuni=p.id_penghuni
                   JOIN pengguna u ON p.id_pengguna=u.id_pengguna
                   JOIN kamar k ON ko.id_kamar=k.id_kamar
                   WHERE TIMESTAMPDIFF(MONTH, ko.tanggal_mulai, ko.tanggal_selesai) > 36 
                      OR TIMESTAMPDIFF(MONTH, ko.tanggal_mulai, ko.tanggal_selesai) < 1";
$bad_durations = $mysqli->query($q_bad_duration)->fetch_all(MYSQLI_ASSOC);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Perbaikan Data</title>
    <link rel="stylesheet" href="../assets/css/app.css"/>
    <style>
        .box { background:white; padding:20px; border-radius:10px; margin-bottom:20px; box-shadow:0 2px 5px rgba(0,0,0,0.05); }
        .btn-fix { background: #ef4444; color: white; padding: 5px 10px; border-radius: 5px; text-decoration: none; font-size: 12px; }
        .btn-fix:hover { background: #dc2626; }
        table { width:100%; border-collapse:collapse; margin-top:10px; }
        th, td { padding:10px; border-bottom:1px solid #eee; text-align:left; }
    </style>
</head>
<body class="dashboard-body">
    <?php include '../components/sidebar_admin.php'; ?>
    <main class="main-content" style="margin-left: 260px; padding: 20px;">
        <h1 class="font-bold text-xl mb-4">🔧 Perbaikan Data Sistem</h1>
        
        <?php if($msg): ?>
            <div style="background:#dcfce7; color:#166534; padding:15px; border-radius:8px; margin-bottom:20px;">
                <?= $msg ?>
            </div>
        <?php endif; ?>

        <!-- 1. ORPHANED ROOMS -->
        <div class="box">
            <h2 class="font-bold text-red-600 mb-2">1. Masalah "Kamar Terisi Palsu" (Orphaned Rooms)</h2>
            <p class="text-sm text-gray-600">Kamar statusnya 'TERISI' tapi tidak ada penghuni/kontrak aktif.</p>
            
            <?php if(count($orphans) > 0): ?>
                <table>
                    <thead><tr><th>Kode Kamar</th><th>Tipe</th><th>Aksi</th></tr></thead>
                    <tbody>
                        <?php foreach($orphans as $r): ?>
                        <tr>
                            <td class="font-bold"><?= $r['kode_kamar'] ?></td>
                            <td><?= $r['nama_tipe'] ?></td>
                            <td>
                                <form method="post" onsubmit="return confirm('Reset status kamar ini jadi TERSEDIA?')">
                                    <input type="hidden" name="action" value="fix_orphan_room">
                                    <input type="hidden" name="id_kamar" value="<?= $r['id_kamar'] ?>">
                                    <button type="submit" class="btn-fix">Reset status ke TERSEDIA</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="mt-2 text-green-600 font-bold">✅ Tidak ada masalah ditemukan.</div>
            <?php endif; ?>
        </div>

        <!-- 2. DOUBLE BOOKINGS -->
        <div class="box">
            <h2 class="font-bold text-red-600 mb-2">2. Masalah "Double Booking"</h2>
            <p class="text-sm text-gray-600">Penghuni yang memiliki lebih dari 1 kamar aktif.</p>
            
            <?php if(count($doubles) > 0): ?>
                <?php foreach($doubles as $d): ?>
                    <div style="margin-top:15px; border:1px solid #ccc; padding:10px; border-radius:8px;">
                        <div class="font-bold"><?= htmlspecialchars($d['nama']) ?> (<?= $d['jumlah_kontrak'] ?> Kamar)</div>
                        <table>
                            <thead><tr><th>Kamar</th><th>Mulai</th><th>Aksi</th></tr></thead>
                            <tbody>
                                <?php foreach($double_details[$d['id_penghuni']] as $k): ?>
                                <tr>
                                    <td><?= $k['kode_kamar'] ?></td>
                                    <td><?= $k['tanggal_mulai'] ?></td>
                                    <td>
                                        <form method="post" onsubmit="return confirm('Batalkan kontrak ini?')">
                                            <input type="hidden" name="action" value="fix_double_contract">
                                            <input type="hidden" name="id_kontrak" value="<?= $k['id_kontrak'] ?>">
                                            <input type="hidden" name="id_kamar" value="<?= $k['id_kamar'] ?>">
                                            <button type="submit" class="btn-fix">Batalkan Kontrak Ini</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="mt-2 text-green-600 font-bold">✅ Tidak ada masalah ditemukan.</div>
            <?php endif; ?>
        </div>

        <!-- 3. INVALID DURATIONS -->
        <div class="box">
            <h2 class="font-bold text-red-600 mb-2">3. Masalah Durasi Error (Overflow)</h2>
            <p class="text-sm text-gray-600">Kontrak dengan durasi tidak wajar (misal: 2 miliar bulan).</p>
            
            <?php if(count($bad_durations) > 0): ?>
                <table>
                    <thead><tr><th>Penghuni</th><th>Kamar</th><th>Durasi Error</th><th>Aksi</th></tr></thead>
                    <tbody>
                        <?php foreach($bad_durations as $b): ?>
                        <tr>
                            <td><?= htmlspecialchars($b['nama']) ?></td>
                            <td><?= $b['kode_kamar'] ?></td>
                            <td class="text-red-600 font-bold text-xs"><?= $b['durasi_bulan'] ?> Bulan</td>
                            <td>
                                <form method="post" onsubmit="return confirm('Ubah durasi jadi 12 bulan?')">
                                    <input type="hidden" name="action" value="fix_duration">
                                    <input type="hidden" name="id_kontrak" value="<?= $b['id_kontrak'] ?>">
                                    <button type="submit" class="btn-fix">Ubah jadi 12 Bulan</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="mt-2 text-green-600 font-bold">✅ Tidak ada masalah ditemukan.</div>
            <?php endif; ?>
        </div>

    </main>
</body>
</html>
