<?php
session_start();
require 'inc/koneksi.php';
require 'inc/csrf.php';
require 'inc/upload.php';

if (!isset($_SESSION['id_pengguna']) || $_SERVER['REQUEST_METHOD']!=='POST' || !csrf_check($_POST['csrf'])) {
    pesan_error("index.php", "Akses ditolak atau sesi habis. Silakan login ulang.");
}

$id_kamar = intval($_POST['id_kamar']);

// --- SECURITY PATCH: CEK DOUBLE BOOKING ---
// Selain cek status kamar, cek juga apakah ada booking 'PENDING' untuk kamar ini
// yang belum di-approve admin.
$q_cek = "SELECT 
            (SELECT status_kamar FROM kamar WHERE id_kamar=?) as status_fisik,
            (SELECT COUNT(*) FROM booking WHERE id_kamar=? AND status='PENDING') as booking_pending";

$stmt_cek = $mysqli->prepare($q_cek);
$stmt_cek->bind_param('ii', $id_kamar, $id_kamar);
$stmt_cek->execute();
$result_cek = $stmt_cek->get_result()->fetch_assoc();

if (!$result_cek || $result_cek['status_fisik'] != 'TERSEDIA') {
    pesan_error("index.php", "Gagal Booking! Kamar ini sudah terisi atau tidak tersedia.");
}

if ($result_cek['booking_pending'] > 0) {
    pesan_error("index.php", "Maaf, kamar ini baru saja dibooking orang lain dan sedang menunggu konfirmasi admin. Silakan pilih kamar lain.");
}
// ------------------------------------------

// Proses Upload KTP
$ktp_path = null;
if(!empty($_FILES['ktp_opt']['name'])){
    $ktp_path = upload_process($_FILES['ktp_opt'], 'ktp');
}

// Insert Booking
$stmt = $mysqli->prepare("INSERT INTO booking (id_pengguna, id_kamar, checkin_rencana, durasi_bulan_rencana, status, ktp_path_opt, tanggal_booking) VALUES (?, ?, ?, ?, 'PENDING', ?, NOW())");
$stmt->bind_param('iisis', $_SESSION['id_pengguna'], $id_kamar, $_POST['checkin_rencana'], $_POST['durasi_bulan_rencana'], $ktp_path);

if ($stmt->execute()) {
    $id_booking = $stmt->insert_id;

    // Buat pembayaran booking fee otomatis
    $harga_fee = 100000; // Bisa diambil dari config
    $stmt2 = $mysqli->prepare("INSERT INTO pembayaran (ref_type, ref_id, metode, jumlah, status, waktu_verifikasi) VALUES ('BOOKING', ?, 'TRANSFER', ?, 'PENDING', NOW())");
    $stmt2->bind_param('ii', $id_booking, $harga_fee);
    $stmt2->execute();

    header('Location: pembayaran.php?booking=' . $id_booking);
} else {
    pesan_error("index.php", "Terjadi kesalahan sistem saat menyimpan booking.");
}
?>