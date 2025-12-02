<?php
session_start();
require '../inc/koneksi.php';
require '../inc/guard.php';

if (!is_admin() && !is_owner()) {
    pesan_error("../login.php", "Akses Ditolak.");
}

$db = new Database();
$act = $_GET['act'] ?? '';
$id  = intval($_GET['id'] ?? 0);

// 1. TERIMA PEMBAYARAN
if ($act == 'terima' && $id) {
    $cek = $mysqli->query("SELECT * FROM pembayaran WHERE id_pembayaran=$id")->fetch_assoc();
    
    if (!$cek) pesan_error("keuangan_index.php?tab=verifikasi", "Data pembayaran tidak ditemukan.");

    // Update Status Pembayaran
    $mysqli->query("UPDATE pembayaran SET status='DITERIMA', waktu_verifikasi=NOW() WHERE id_pembayaran=$id");

    // LOGIKA LANJUTAN: Apa efeknya setelah diterima?
    if ($cek['ref_type'] == 'BOOKING') {
        // Jika ini DP Booking -> Aktifkan Kontrak
        $id_booking = $cek['ref_id'];
        $sukses = $db->setujui_booking_dan_buat_kontrak($id_booking);
        
        if($sukses) {
            pesan_error("keuangan_index.php?tab=verifikasi", "✅ Pembayaran Booking Diterima & Kontrak Aktif!");
        } else {
            pesan_error("keuangan_index.php?tab=verifikasi", "⚠️ Pembayaran diterima, TAPI gagal aktivasi kontrak (Kamar penuh/Error). Cek data booking.");
        }
    } 
    else if ($cek['ref_type'] == 'TAGIHAN') {
        // Jika ini Tagihan Bulanan -> Lunaskan Tagihan
        $id_tagihan = $cek['ref_id'];
        $mysqli->query("UPDATE tagihan SET status='LUNAS' WHERE id_tagihan=$id_tagihan");
        pesan_error("keuangan_index.php?tab=verifikasi", "✅ Pembayaran Tagihan Diterima & Status LUNAS.");
    }
} 

// 2. TOLAK PEMBAYARAN
else if ($act == 'tolak' && $id) {
    $mysqli->query("UPDATE pembayaran SET status='DITOLAK', waktu_verifikasi=NOW() WHERE id_pembayaran=$id");
    pesan_error("keuangan_index.php?tab=verifikasi", "🚫 Pembayaran telah ditolak.");
}

// 3. GENERATE TAGIHAN MASAL
else if ($act == 'generate_masal' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $bulan = $_POST['bulan_tagih'];
    $jumlah = $db->generate_tagihan_masal($bulan);
    
    if ($jumlah > 0) {
        pesan_error("keuangan_index.php?tab=tagihan&bulan=$bulan", "✅ Sukses membuat $jumlah tagihan baru untuk bulan $bulan.");
    } else {
        pesan_error("keuangan_index.php?tab=tagihan&bulan=$bulan", "ℹ️ Tidak ada tagihan baru yang dibuat. (Mungkin semua sudah ditagih?)");
    }
}

// 4. BAYAR CASH (Manual)
else if ($act == 'bayar_cash' && $id) {
    $sukses = $db->bayar_tagihan_cash($id);
    
    // Ambil bulan untuk redirect
    $q_bln = $mysqli->query("SELECT bulan_tagih FROM tagihan WHERE id_tagihan=$id");
    $tgl = ($q_bln && $q_bln->num_rows > 0) ? $q_bln->fetch_object()->bulan_tagih : date('Y-m');

    if($sukses) {
        pesan_error("keuangan_index.php?tab=tagihan&bulan=$tgl", "💰 Pembayaran Tunai Berhasil Dicatat! Tagihan Lunas.");
    } else {
        pesan_error("keuangan_index.php?tab=tagihan&bulan=$tgl", "❌ Gagal mencatat pembayaran.");
    }
}
else {
    // Fallback jika tidak ada aksi yang cocok
    header("Location: keuangan_index.php");
}
?>