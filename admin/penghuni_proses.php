<?php
session_start();
require '../inc/koneksi.php';
require '../inc/guard.php';

if (!is_admin() && !is_owner()) { die('Forbidden'); }

$db = new Database();
$act = $_GET['act'] ?? '';

// 1. EDIT DATA PENGHUNI (Biodata)
if ($act == 'edit') {
    $id = intval($_POST['id']);
    $alamat = $_POST['alamat'];
    $pekerjaan = $_POST['pekerjaan'];
    $emergency = $_POST['emergency_cp'];

    $stmt = $mysqli->prepare("UPDATE penghuni SET alamat_asal=?, pekerjaan=?, emergency_cp=? WHERE id_penghuni=?");
    $stmt->bind_param('sssi', $alamat, $pekerjaan, $emergency, $id);
    
    if ($stmt->execute()) {
        $db->catat_log($_SESSION['id_pengguna'], 'EDIT PENGHUNI', "Update data penghuni ID $id");
        header("Location: penghuni_data.php?msg=updated");
    } else {
        echo "Gagal update";
    }
}

// 2. PERPANJANG SEWA
elseif ($act == 'perpanjang') {
    $id = intval($_POST['id_penghuni']);
    $durasi = intval($_POST['durasi']); // bulan

    if ($id > 0 && $durasi > 0) {
        if ($db->perpanjang_kontrak($id, $durasi)) {
            $db->catat_log($_SESSION['id_pengguna'], 'PERPANJANG KONTRAK', "Perpanjang kontrak ID Penghuni $id selama $durasi bulan");
            header("Location: penghuni_data.php?msg=extended");
        } else {
            echo "<script>alert('Gagal memperpanjang kontrak.'); window.history.back();</script>";
        }
    } else {
        header("Location: penghuni_data.php");
    }
}

// 3. STOP SEWA (CHECKOUT)
elseif ($act == 'stop') {
    $id = intval($_GET['id']);
    
    if ($id > 0) { // Validasi sederhana
        if ($db->stop_kontrak($id)) {
            $db->catat_log($_SESSION['id_pengguna'], 'STOP KONTRAK', "Memberhentikan sewa (Checkout) Penghuni ID $id");
            header("Location: penghuni_data.php?msg=stopped");
        } else {
            echo "<script>alert('Gagal memberhentikan kontrak.'); window.history.back();</script>";
        }
    } else {
        header("Location: penghuni_data.php");
    }
}

else {
    header("Location: penghuni_data.php");
}
?>