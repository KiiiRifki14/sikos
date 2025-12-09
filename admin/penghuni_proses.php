<?php
// [OOP: Session]
session_start();
require '../inc/koneksi.php';
require '../inc/guard.php';

// [Security]
if (!is_admin() && !is_owner()) {
    die('Forbidden');
}

// Init DB
$db = new Database();
$act = $_GET['act'] ?? '';

// ==========================================================================
// 1. EDIT DATA PENGHUNI (BIODATA)
// ==========================================================================
if ($act == 'edit') {
    $id = intval($_POST['id']);
    $alamat = $_POST['alamat'];
    $pekerjaan = $_POST['pekerjaan'];
    $emergency = $_POST['emergency_cp'];

    // Update hanya tabel penghuni (detail profil), bukan akun pengguna
    $stmt = $mysqli->prepare("UPDATE penghuni SET alamat_asal=?, pekerjaan=?, emergency_cp=? WHERE id_penghuni=?");
    $stmt->bind_param('sssi', $alamat, $pekerjaan, $emergency, $id);

    if ($stmt->execute()) {
        // [Audit Trail]
        $db->catat_log($_SESSION['id_pengguna'], 'EDIT PENGHUNI', "Update data penghuni ID $id");
        header("Location: penghuni_data.php?msg=updated");
    } else {
        echo "Gagal update";
    }
}

// ==========================================================================
// 2. PERPANJANG SEWA (EXTEND CONTRACT)
// ==========================================================================
elseif ($act == 'perpanjang') {
    $id = intval($_POST['id_penghuni']);
    $durasi = intval($_POST['durasi']); // durasi tambahan (bulan)

    if ($id > 0 && $durasi > 0) {
        // [OOP: Method Call] perpanjang_kontrak adalah method kompleks:
        // - Update tanggal_selesai di tabel 'kontrak'
        // - Generate tagihan baru untuk bulan-bulan tambahan tersebut
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

// ==========================================================================
// 3. STOP SEWA (CHECKOUT)
// ==========================================================================
elseif ($act == 'stop') {
    $id = intval($_GET['id']);

    if ($id > 0) {
        // [OOP: Method Call] stop_kontrak akan:
        // - Ubah status kontrak jadi 'SELESAI'
        // - Ubah status kamar jadi 'TERSEDIA' (kosongkan kamar)
        if ($db->stop_kontrak($id)) {
            $db->catat_log($_SESSION['id_pengguna'], 'STOP KONTRAK', "Memberhentikan sewa (Checkout) Penghuni ID $id");
            header("Location: penghuni_data.php?msg=stopped");
        } else {
            echo "<script>alert('Gagal memberhentikan kontrak.'); window.history.back();</script>";
        }
    } else {
        header("Location: penghuni_data.php");
    }
} else {
    header("Location: penghuni_data.php");
}
