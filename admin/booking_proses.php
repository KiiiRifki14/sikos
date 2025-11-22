<?php
session_start();
require '../inc/koneksi.php';
require '../inc/utils.php';

if (!is_admin()) { die('Forbidden'); }
$id_booking = intval($_GET['id_booking']);

$mysqli->begin_transaction();
try {
    // Lock row
    $booking = $mysqli->prepare("SELECT * FROM booking WHERE id_booking=? FOR UPDATE");
    $booking->bind_param('i', $id_booking);
    $booking->execute();
    $data = $booking->get_result()->fetch_assoc();
    if (!$data || $data['status']!='PENDING') throw new Exception("Booking tidak bisa diapprove");

    // cek/insert penghuni
    $penghuni = $mysqli->prepare("SELECT id_penghuni FROM penghuni WHERE id_pengguna=?");
    $penghuni->bind_param('i', $data['id_pengguna']);
    $penghuni->execute();
    $row = $penghuni->get_result()->fetch_assoc();
    if (!$row) {
        $mysqli->query("INSERT INTO penghuni (id_pengguna, created_at) VALUES ({$data['id_pengguna']}, NOW())");
        $id_penghuni = $mysqli->insert_id;
    } else {
        $id_penghuni = $row['id_penghuni'];
    }

    // kontrak aktif check: kamar & penghuni
    $ck = $mysqli->query("SELECT id_kontrak FROM kontrak WHERE id_penghuni={$id_penghuni} AND id_kamar={$data['id_kamar']} AND status='AKTIF'");
    if ($ck->num_rows > 0) throw new Exception("Sudah ada kontrak aktif");

    $mulai = $data['checkin_rencana'];
    $durasi = intval($data['durasi_bulan_rencana']);
    $selesai = date('Y-m-d', strtotime("+$durasi months", strtotime($mulai)));
    // insert kontrak
    $stmt = $mysqli->prepare("INSERT INTO kontrak (id_penghuni, id_kamar, tanggal_mulai, tanggal_selesai, durasi_bulan, deposit, status) VALUES (?,?,?,?,?,500000,'AKTIF')");
    $stmt->bind_param("iisii", $id_penghuni, $data['id_kamar'], $mulai, $selesai, $durasi);
    $stmt->execute();

    // kamar terisi
    $mysqli->query("UPDATE kamar SET status_kamar='TERISI' WHERE id_kamar={$data['id_kamar']}");
    // booking selesai
    $mysqli->query("UPDATE booking SET status='SELESAI' WHERE id_booking={$id_booking}");

    $mysqli->commit();
    header('Location: booking_data.php?success=approve');
} catch (Exception $e) {
    $mysqli->rollback();
    error_log('Booking approve fail: ' . $e->getMessage(),3, __DIR__ . '/../logs/app.log');
    header('Location: booking_data.php?error=' . urlencode($e->getMessage()));
}
?>