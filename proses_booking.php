<?php
session_start();
require 'inc/koneksi.php';
require 'inc/csrf.php';
require 'inc/upload.php';

if (!isset($_SESSION['id_pengguna']) || $_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_check($_POST['csrf'])) {
    pesan_error("index.php", "Akses ditolak atau sesi habis. Silakan login ulang.");
}

$id_kamar = intval($_POST['id_kamar']);

// --- SECURITY: TRANSAKSI & ROW LOCKING ---
$mysqli->begin_transaction();

// [SECURITY] Konversi ke int
$checkin_rencana = $_POST['checkin_rencana'];
$durasi = (int)$_POST['durasi_bulan_rencana'];

try {
    // 0. Validasi Aturan Bisnis
    // A. Cek Kepemilikan (Satu kamar per user)
    if (!$db->can_user_book($_SESSION['id_pengguna'])) {
        throw new Exception("Anda sudah memiliki booking aktif atau sedang menyewa kamar. Tidak bisa memesan lebih dari satu kamar.");
    }
    // B. Cek Durasi
    if ($durasi < 1 || $durasi > 36) {
        throw new Exception("Durasi sewa minimal 1 bulan dan maksimal 36 bulan.");
    }
    // 1. Cek Ketersediaan & Lock Row (Cegah Race Condition)
    // Menggunakan FOR UPDATE agar proses lain menunggu sampai transaksi ini selesai
    $q_cek = "SELECT 
                (SELECT status_kamar FROM kamar WHERE id_kamar=? FOR UPDATE) as status_fisik,
                (SELECT COUNT(*) FROM booking WHERE id_kamar=? AND status='PENDING') as booking_pending";

    $stmt_cek = $mysqli->prepare($q_cek);
    $stmt_cek->bind_param('ii', $id_kamar, $id_kamar);
    $stmt_cek->execute();
    $result_cek = $stmt_cek->get_result()->fetch_assoc();

    // Validasi
    if (!$result_cek || $result_cek['status_fisik'] != 'TERSEDIA') {
        throw new Exception("Gagal Booking! Kamar ini sudah terisi atau tidak tersedia.");
    }
    if ($result_cek['booking_pending'] > 0) {
        throw new Exception("Maaf, kamar ini baru saja dibooking orang lain dan sedang menunggu konfirmasi admin.");
    }

    // 2. Proses Upload KTP
    $ktp_path = null;
    if (!empty($_FILES['ktp_opt']['name'])) {
        $ktp_path = upload_process($_FILES['ktp_opt'], 'ktp');
        if (!$ktp_path) throw new Exception("Gagal upload KTP.");
    }

    // 3. Insert Booking
    $stmt = $mysqli->prepare("INSERT INTO booking (id_pengguna, id_kamar, checkin_rencana, durasi_bulan_rencana, status, ktp_path_opt, tanggal_booking) VALUES (?, ?, ?, ?, 'PENDING', ?, NOW())");
    $stmt->bind_param('iisis', $_SESSION['id_pengguna'], $id_kamar, $checkin_rencana, $durasi, $ktp_path);

    if (!$stmt->execute()) {
        throw new Exception("Terjadi kesalahan sistem saat menyimpan booking.");
    }
    $id_booking = $stmt->insert_id;

    // 4. Insert Pembayaran Fee
    $harga_fee = 100000;
    $stmt2 = $mysqli->prepare("INSERT INTO pembayaran (ref_type, ref_id, metode, jumlah, status, waktu_verifikasi) VALUES ('BOOKING', ?, 'TRANSFER', ?, 'PENDING', NOW())");
    $stmt2->bind_param('ii', $id_booking, $harga_fee);

    if (!$stmt2->execute()) {
        throw new Exception("Gagal membuat tagihan booking fee.");
    }

    // 5. Commit Transaksi
    $mysqli->commit();

    header('Location: pembayaran.php?booking=' . $id_booking);
} catch (Exception $e) {
    $mysqli->rollback(); // Batalkan semua perubahan jika ada error

    // [FIX] Hapus file sampah jika transaksi gagal
    if ($ktp_path && file_exists(__DIR__ . "/assets/uploads/ktp/" . $ktp_path)) {
        unlink(__DIR__ . "/assets/uploads/ktp/" . $ktp_path);
    }

    pesan_error("index.php", $e->getMessage());
}
