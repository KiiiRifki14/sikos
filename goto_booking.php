<?php
session_start();
// Dijalankan saat klik "Booking Kamar Ini"
$id_kamar = intval($_POST['id_kamar'] ?? 0);
if ($id_kamar < 1) die('Invalid!');
$_SESSION['next_booking_kamar'] = $id_kamar;
if (!isset($_SESSION['id_pengguna'])) {
    header("Location: login.php?info=Silakan login untuk booking kamar!");
    exit;
} else {
    header("Location: booking.php?id_kamar=$id_kamar");
    exit;
}
?>