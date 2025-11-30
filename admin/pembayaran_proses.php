<?php
session_start();
require '../inc/koneksi.php';
require '../inc/guard.php'; // Pastikan file guard ada, jika tidak, hapus baris ini

// Cek Admin
if (!isset($_SESSION['peran']) || ($_SESSION['peran']!='ADMIN' && $_SESSION['peran']!='PEMILIK')) {
    header("Location: ../login.php"); exit;
}

$act = $_GET['act'] ?? '';
$id  = $_GET['id'] ?? 0;

if ($act == 'terima' && $id) {
    // 1. Ambil data pembayaran
    $cek = $mysqli->query("SELECT * FROM pembayaran WHERE id_pembayaran=$id")->fetch_assoc();
    
    if ($cek) {
        // 2. Update Status Pembayaran
        $mysqli->query("UPDATE pembayaran SET status='DITERIMA', waktu_verifikasi=NOW() WHERE id_pembayaran=$id");

        // 3. LOGIKA VITAL: Jika ini pembayaran BOOKING, maka aktifkan kontrak!
        if ($cek['ref_type'] == 'BOOKING') {
            // Ambil ID Booking dari kolom ref_id
            $id_booking = $cek['ref_id'];
            
            // Panggil fungsi sakti yang kita buat di koneksi.php
            $db->setujui_booking_dan_buat_kontrak($id_booking);
            
            echo "<script>alert('Pembayaran diterima & Kontrak berhasil dibuat otomatis!'); window.location='pembayaran_data.php';</script>";
        } 
        // 4. Jika Pembayaran TAGIHAN bulanan
        else if ($cek['ref_type'] == 'TAGIHAN') {
            $id_tagihan = $cek['ref_id'];
            $mysqli->query("UPDATE tagihan SET status='LUNAS' WHERE id_tagihan=$id_tagihan");
            echo "<script>alert('Pembayaran Tagihan diterima!'); window.location='pembayaran_data.php';</script>";
        }
    }
} 
else if ($act == 'tolak' && $id) {
    $mysqli->query("UPDATE pembayaran SET status='DITOLAK', waktu_verifikasi=NOW() WHERE id_pembayaran=$id");
    
    // Jika booking ditolak pembayarannya, bookingnya statusnya apa? (Opsional: bisa di set BATAL atau tetap PENDING)
    echo "<script>alert('Pembayaran ditolak.'); window.location='pembayaran_data.php';</script>";
}
?>