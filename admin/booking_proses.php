<?php
session_start();
require '../inc/koneksi.php';
require '../inc/guard.php';

if (!is_admin() && !is_owner()) {
    pesan_error("../login.php", "Akses Ditolak.");
}

$db = new Database(); // Pastikan DB di-init
$act = $_GET['act'] ?? '';
$id  = intval($_GET['id'] ?? 0);

if ($id == 0) pesan_error("booking_data.php", "ID Booking tidak valid.");

// Ambil info booking dulu untuk log yang detail (SAFE REFACTOR)
$info = $db->fetch_row_assoc("SELECT u.nama, k.kode_kamar FROM booking b JOIN pengguna u ON b.id_pengguna=u.id_pengguna JOIN kamar k ON b.id_kamar=k.id_kamar WHERE b.id_booking=$id");
$ket_log = $info ? "Booking: {$info['nama']} - Kamar {$info['kode_kamar']}" : "Booking ID $id";

if ($act == 'approve') {
    $sukses = $db->setujui_booking_dan_buat_kontrak($id);

    if ($sukses) {
        // Log Sukses
        $db->catat_log($_SESSION['id_pengguna'], 'APPROVE BOOKING', "Menyetujui $ket_log");
        pesan_error("booking_data.php", "✅ Booking Diterima! Kontrak otomatis aktif & Tagihan dibuat.");
    } else {
        pesan_error("booking_data.php", "❌ Gagal memproses kontrak. Cek apakah kamar sudah terisi?");
    }
} else if ($act == 'batal' || $act == 'reject') {
    $mysqli->query("UPDATE booking SET status='BATAL' WHERE id_booking=$id");
    $mysqli->query("UPDATE pembayaran SET status='DITOLAK' WHERE ref_type='BOOKING' AND ref_id=$id");

    // Log Batal
    $db->catat_log($_SESSION['id_pengguna'], 'REJECT BOOKING', "Menolak $ket_log");
    pesan_error("booking_data.php", "Booking telah dibatalkan/ditolak.");
} else {
    pesan_error("booking_data.php", "Aksi tidak dikenali.");
}
