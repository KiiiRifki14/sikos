<?php
session_start();
require '../inc/koneksi.php';
require '../inc/guard.php';

// Cek Admin
if (!isset($_SESSION['peran']) || ($_SESSION['peran']!='ADMIN' && $_SESSION['peran']!='OWNER')) {
    header("Location: ../login.php"); exit;
}

$db = new Database(); // Pastikan instansiasi objek DB
$act = $_GET['act'] ?? '';
$id  = $_GET['id'] ?? 0;

// 1. TERIMA PEMBAYARAN (Verifikasi)
if ($act == 'terima' && $id) {
    $cek = $mysqli->query("SELECT * FROM pembayaran WHERE id_pembayaran=$id")->fetch_assoc();
    if ($cek) {
        $mysqli->query("UPDATE pembayaran SET status='DITERIMA', waktu_verifikasi=NOW() WHERE id_pembayaran=$id");

        if ($cek['ref_type'] == 'BOOKING') {
            $id_booking = $cek['ref_id'];
            $db->setujui_booking_dan_buat_kontrak($id_booking);
            echo "<script>alert('Booking Diterima & Kontrak Aktif!'); window.location='keuangan_index.php?tab=verifikasi';</script>";
        } 
        else if ($cek['ref_type'] == 'TAGIHAN') {
            $id_tagihan = $cek['ref_id'];
            $mysqli->query("UPDATE tagihan SET status='LUNAS' WHERE id_tagihan=$id_tagihan");
            echo "<script>alert('Pembayaran Tagihan diterima!'); window.location='keuangan_index.php?tab=verifikasi';</script>";
        }
    }
} 
// 2. TOLAK PEMBAYARAN
else if ($act == 'tolak' && $id) {
    $mysqli->query("UPDATE pembayaran SET status='DITOLAK', waktu_verifikasi=NOW() WHERE id_pembayaran=$id");
    echo "<script>alert('Pembayaran ditolak.'); window.location='keuangan_index.php?tab=verifikasi';</script>";
}

// 3. GENERATE TAGIHAN MASAL (BARU)
else if ($act == 'generate_masal' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $bulan = $_POST['bulan_tagih'];
    $jumlah = $db->generate_tagihan_masal($bulan);
    
    if ($jumlah > 0) {
        echo "<script>alert('Berhasil membuat $jumlah tagihan baru untuk bulan $bulan.'); window.location='keuangan_index.php?tab=tagihan&bulan=$bulan';</script>";
    } else {
        echo "<script>alert('Tidak ada tagihan baru yang dibuat. Mungkin semua kontrak sudah ditagih untuk bulan ini?'); window.location='keuangan_index.php?tab=tagihan&bulan=$bulan';</script>";
    }
}

// 4. BAYAR CASH (BARU)
else if ($act == 'bayar_cash' && $id) {
    $sukses = $db->bayar_tagihan_cash($id);
    if($sukses) {
        // Ambil bulan dari tagihan agar redirectnya enak
        $tgl = $mysqli->query("SELECT bulan_tagih FROM tagihan WHERE id_tagihan=$id")->fetch_object()->bulan_tagih;
        echo "<script>alert('Pembayaran Tunai Berhasil Dicatat! Tagihan Lunas.'); window.location='keuangan_index.php?tab=tagihan&bulan=$tgl';</script>";
    } else {
        echo "<script>alert('Gagal mencatat pembayaran.'); window.history.back();</script>";
    }
}
?>