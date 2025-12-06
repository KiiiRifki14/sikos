<?php
session_start();
require 'inc/koneksi.php';
require 'inc/csrf.php';
require 'inc/upload.php';

// 1. Cek Login
if (!isset($_SESSION['id_pengguna'])) {
    header('Location: login.php');
    exit;
}

$db = new Database();

// ==========================================
// HANDLE REQUEST POST (PROSES FORM)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 2. Ambil Token CSRF
    $token = $_POST['csrf'] ?? ''; 

    // 3. Validasi Token
    if (!csrf_check($token)) {
        pesan_error("tagihan_saya.php", "Token keamanan tidak valid (CSRF). Silakan refresh halaman.");
    }

    // 4. Ambil Data Form
    $id_tagihan = intval($_POST['id_tagihan']);
    $jumlah     = intval($_POST['jumlah']);
    $metode     = $_POST['metode'] ?? 'TRANSFER';

    // Validasi Data Input
    if ($id_tagihan <= 0 || $jumlah <= 0) {
        pesan_error("tagihan_saya.php", "Data tagihan tidak valid.");
    }

    // 5. Proses Upload File
    $bukti_path = null;
    if (!empty($_FILES['bukti']['name'])) {
        $bukti_path = upload_process($_FILES['bukti'], 'bukti_tf');
    }

    if (!$bukti_path) {
        pesan_error("pembayaran_tagihan.php?id=$id_tagihan", "Wajib upload bukti pembayaran!");
    }

    // 6. Simpan ke Database
    $simpan = $db->tambah_pembayaran_tagihan($id_tagihan, $jumlah, $bukti_path, $metode);

    if ($simpan === "DUPLIKAT") {
        pesan_error("tagihan_saya.php", "Anda sudah mengirim bukti untuk tagihan ini dan sedang menunggu verifikasi admin.");
    }
    else if ($simpan) {
        header('Location: tagihan_saya.php?status=sukses');
        exit;
    } else {
        pesan_error("pembayaran_tagihan.php?id=$id_tagihan", "Terjadi kesalahan database saat menyimpan pembayaran.");
    }
}

// ==========================================
// HANDLE REQUEST GET (TAMPILKAN FORM)
// ==========================================

$id_tagihan = intval($_GET['id'] ?? 0);
if ($id_tagihan < 1) {
    pesan_error("tagihan_saya.php", "ID Tagihan tidak valid.");
}

// Ambil Detail Tagihan
$stmt = $mysqli->prepare("SELECT t.*, k.kode_kamar FROM tagihan t JOIN kontrak ko ON t.id_kontrak=ko.id_kontrak JOIN kamar k ON ko.id_kamar=k.id_kamar WHERE t.id_tagihan=?");
$stmt->bind_param('i', $id_tagihan);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if (!$row) {
    pesan_error("tagihan_saya.php", "Data tagihan tidak ditemukan.");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Bayar Tagihan</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="stylesheet" href="assets/css/app.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .pay-card { max-width: 500px; margin: 40px auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; }
        .pay-header { text-align: center; margin-bottom: 30px; }
        .pay-amount { font-size: 32px; font-weight: 800; color: #2563eb; margin: 10px 0; }
        .pay-detail { background: #f8fafc; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; color: #475569; }
        .pay-detail div { display: flex; justify-content: space-between; margin-bottom: 8px; }
        .pay-detail div:last-child { margin-bottom: 0; }
        .rek-box { border: 2px dashed #cbd5e1; padding: 15px; text-align: center; border-radius: 8px; margin-bottom: 25px; background: #f1f5f9; }
        .btn-upload { width: 100%; padding: 12px; font-size: 16px; font-weight: 600; }
    </style>
</head>
<body>
    <?php include 'components/sidebar_penghuni.php'; ?>
    
    <main class="main-content animate-fade-up" style="display: flex; align-items: center; justify-content: center; min-height: 80vh;">
        
        <div class="pay-card">
            <div class="pay-header">
                <i class="fa-solid fa-file-invoice-dollar" style="font-size: 48px; color: #64748b; margin-bottom: 15px;"></i>
                <h2 style="margin: 0; color: #1e293b;">Konfirmasi Pembayaran</h2>
                <p style="color: #64748b; font-size: 14px;">Silakan transfer sesuai nominal di bawah ini</p>
            </div>

            <div class="pay-amount">Rp <?= number_format($row['nominal'], 0, ',', '.') ?></div>

            <div class="pay-detail">
                <div><span>Kode Kamar</span> <strong><?= htmlspecialchars($row['kode_kamar']) ?></strong></div>
                <div><span>Bulan Tagihan</span> <strong><?= date('F Y', strtotime($row['bulan_tagih'])) ?></strong></div>
                <div><span>Jatuh Tempo</span> <strong><?= date('d M Y', strtotime($row['jatuh_tempo'])) ?></strong></div>
            </div>

            <?php 
            $pengaturan = $db->ambil_pengaturan(); 
            ?>
            <div class="rek-box">
                <div style="font-size:12px; color:#64748b; margin-bottom:5px;">Transfer ke Rekening:</div>
                <div style="font-weight:700; color:#1e293b; font-size:16px;"><?= htmlspecialchars($pengaturan['rek_bank']) ?></div>
                <div style="font-size:13px; color:#334155;">a.n <?= htmlspecialchars($pengaturan['pemilik']) ?></div>
            </div>

            <form action="pembayaran_tagihan.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                <input type="hidden" name="id_tagihan" value="<?= $row['id_tagihan'] ?>">
                <input type="hidden" name="jumlah" value="<?= $row['nominal'] ?>">
                <input type="hidden" name="metode" value="TRANSFER">

                <div style="margin-bottom: 20px;">
                    <label style="display:block; font-size:13px; font-weight:600; color:#334155; margin-bottom:8px;">Upload Bukti Transfer (Image)</label>
                    <input type="file" name="bukti" accept="image/*" class="form-control" required style="padding: 10px; border: 1px solid #cbd5e1; width: 100%; border-radius: 6px;">
                </div>

                <div style="display: flex; gap: 10px;">
                    <a href="tagihan_saya.php" class="btn" style="flex:1; text-align:center; background:#e2e8f0; color:#475569;">Batal</a>
                    <button type="submit" class="btn btn-primary btn-upload" style="flex:2;">Kirim Bukti Bayar</button>
                </div>
            </form>
        </div>

    </main>
</body>
</html>