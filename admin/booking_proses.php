<?php
// [OOP: Session] Memulai session
session_start();

// [OOP: Modularization] Load database connection and security helper
require '../inc/koneksi.php';
require '../inc/guard.php';

// [Security: RBAC] Cek apakah user admin/owner
if (!is_admin() && !is_owner()) {
    pesan_error("../login.php", "Akses Ditolak.");
}

// [OOP: Object Instantiation] Membuat objek Database baru
$db = new Database(); // Pastikan DB di-init
// [Input Handling] Menangkap parameter aksi (approve/reject) dan ID booking
$act = $_GET['act'] ?? '';
$id  = intval($_GET['id'] ?? 0);

// [Validation] ID Booking wajib ada
if ($id == 0) pesan_error("booking_data.php", "ID Booking tidak valid.");

// [Data Retrieval: Safe Method] Mengambil detail booking untuk keperluan Logging
// Gunakan helper fetch_row_assoc agar aman dari crash jika data tidak ditemukan
$info = $db->fetch_row_assoc("SELECT u.nama, k.kode_kamar FROM booking b JOIN pengguna u ON b.id_pengguna=u.id_pengguna JOIN kamar k ON b.id_kamar=k.id_kamar WHERE b.id_booking=$id");
// Siapkan string keterangan untuk dicatat di log aktivitas
$ket_log = $info ? "Booking: {$info['nama']} - Kamar {$info['kode_kamar']}" : "Booking ID $id";

// ==========================================================================
// LOGIKA 1: APPROVE BOOKING (MENERIMA CALON PENGHUNI)
// ==========================================================================
if ($act == 'approve') {
    // [OOP: Method Call] Memanggil method kompleks di Class Database
    // Method ini otomatis:
    // 1. Ubah status booking jadi SELESAI
    // 2. Ubah status kamar jadi TERISI
    // 3. Buat kontrak sewa baru
    // 4. Generate tagihan pertama
    $sukses = $db->setujui_booking_dan_buat_kontrak($id);

    if ($sukses) {
        // [Audit Trail] Catat log sukses
        $db->catat_log($_SESSION['id_pengguna'], 'APPROVE BOOKING', "Menyetujui $ket_log");
        pesan_error("booking_data.php", "✅ Booking Diterima! Kontrak otomatis aktif & Tagihan dibuat.");
    } else {
        // Error Handling jika kamar penuh atau data tidak valid
        pesan_error("booking_data.php", "❌ Gagal memproses kontrak. Cek apakah kamar sudah terisi?");
    }
}
// ==========================================================================
// LOGIKA 2: REJECT / BATAL BOOKING
// ==========================================================================
else if ($act == 'batal' || $act == 'reject') {
    // [Database Transaction] Update status booking jadi BATAL
    $mysqli->query("UPDATE booking SET status='BATAL' WHERE id_booking=$id");
    // Batalkan juga pembayaran terkait jika sudah ada yang diupload
    $mysqli->query("UPDATE pembayaran SET status='DITOLAK' WHERE ref_type='BOOKING' AND ref_id=$id");

    // [Audit Trail] Catat log pembatalan
    $db->catat_log($_SESSION['id_pengguna'], 'REJECT BOOKING', "Menolak $ket_log");
    pesan_error("booking_data.php", "Booking telah dibatalkan/ditolak.");
} else {
    // Fallback jika aksi tidak dikenal
    pesan_error("booking_data.php", "Aksi tidak dikenali.");
}
