<?php
require 'inc/koneksi.php';
require 'inc/csrf.php';
session_start();
if (!isset($_SESSION['id_pengguna'])) {
    header('Location: login.php?next=booking');
    exit;
}
$id_kamar = intval($_GET['id_kamar'] ?? 0);
$res = $mysqli->prepare("SELECT * FROM kamar WHERE id_kamar=? AND status_kamar='TERSEDIA'");
$res->bind_param('i', $id_kamar);
$res->execute();
$data_kamar = $res->get_result()->fetch_assoc();
if (!$data_kamar) {
    die('Kamar tidak tersedia!');
}
?>
<form method="POST" action="proses_booking.php" enctype="multipart/form-data">
    <input type="hidden" name="csrf" value="<?php echo csrf_token(); ?>">
    <input type="hidden" name="id_kamar" value="<?php echo $id_kamar; ?>">
    <label>KTP (opsional, jpg/png/webp, max 2MB):</label>
    <input type="file" name="ktp_opt">
    <label>Check-in Rencana:</label>
    <input type="date" name="checkin_rencana" required>
    <label>Durasi (bulan):</label>
    <input type="number" name="durasi_bulan_rencana" min="1" required>
    <button type="submit">Booking</button>
</form>