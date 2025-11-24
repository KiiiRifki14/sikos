<?php
session_start();
require '../inc/koneksi.php';
require '../inc/guard.php';

if (!is_admin() && !is_owner()) die('Forbidden');

$id_pembayaran = intval($_GET['id']);
$act = $_GET['act']; // terima atau tolak

if ($id_pembayaran > 0) {
    // 1. Ambil info pembayaran dulu untuk tahu ini bayar apa (Tagihan atau Booking)
    $stmt = $mysqli->prepare("SELECT ref_type, ref_id FROM pembayaran WHERE id_pembayaran=?");
    $stmt->bind_param('i', $id_pembayaran);
    $stmt->execute();
    $pay = $stmt->get_result()->fetch_assoc();

    if ($pay) {
        if ($act == 'terima') {
            $mysqli->begin_transaction();
            try {
                // A. Update Status Pembayaran -> DITERIMA
                $upd_pay = $mysqli->query("UPDATE pembayaran SET status='DITERIMA', waktu_verifikasi=NOW(), verifikator_id={$_SESSION['id_pengguna']} WHERE id_pembayaran=$id_pembayaran");

                // B. Jika ini bayar TAGIHAN, update tabel tagihan -> LUNAS
                if ($pay['ref_type'] == 'TAGIHAN') {
                    $id_tagihan = $pay['ref_id'];
                    $mysqli->query("UPDATE tagihan SET status='LUNAS' WHERE id_tagihan=$id_tagihan");
                }
                
                // C. Jika ini bayar BOOKING, update tabel booking -> SELESAI (Opsional, tergantung alur booking Anda)
                // if ($pay['ref_type'] == 'BOOKING') { ... }

                $mysqli->commit();
                header('Location: pembayaran_data.php?msg=success_approve');

            } catch (Exception $e) {
                $mysqli->rollback();
                die("Error Database: " . $e->getMessage());
            }

        } elseif ($act == 'tolak') {
            // Jika ditolak, hanya update status pembayaran -> DITOLAK
            // Status tagihan tetap BELUM (agar penghuni bisa upload ulang)
            $mysqli->query("UPDATE pembayaran SET status='DITOLAK', waktu_verifikasi=NOW(), verifikator_id={$_SESSION['id_pengguna']} WHERE id_pembayaran=$id_pembayaran");
            header('Location: pembayaran_data.php?msg=success_reject');
        }
    }
} else {
    header('Location: pembayaran_data.php');
}
?>