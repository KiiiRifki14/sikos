<?php
session_start();
require '../inc/koneksi.php';
require '../inc/guard.php';
require '../inc/upload.php';

if (!is_admin()) { die('Forbidden'); }

// --- TAMBAH KAMAR ---
if ($_GET['act'] == 'tambah') {
    $kode_kamar = $_POST['kode_kamar'];
    // Validasi kode unik
    $cek = $mysqli->prepare("SELECT 1 FROM kamar WHERE kode_kamar=?");
    $cek->bind_param('s', $kode_kamar);
    $cek->execute();
    if ($cek->get_result()->fetch_assoc()) {
        echo "<div class='container'><h2>Error</h2>
        Kode kamar <b>" . htmlspecialchars($kode_kamar) . "</b> sudah pernah dipakai!
        <br><a href='kamar_tambah.php'>Kembali</a></div>";
        exit;
    }
    $foto_cover = null;
    if (!empty($_FILES['foto_cover']['name'])) {
        $foto_cover = upload_process($_FILES['foto_cover'], 'kamar');
    }
    $stmt = $mysqli->prepare("INSERT INTO kamar (kode_kamar, id_tipe, lantai, luas_m2, harga, status_kamar, foto_cover, catatan)
      VALUES (?, ?, ?, ?, ?, 'TERSEDIA', ?, ?)");
    $stmt->bind_param(
        'siidiss',
        $_POST['kode_kamar'],
        $_POST['id_tipe'],
        $_POST['lantai'],
        $_POST['luas_m2'],
        $_POST['harga'],
        $foto_cover,
        $_POST['catatan']
    );
    $stmt->execute();
    header('Location: kamar_data.php');
    exit;
}

// --- HAPUS KAMAR ---
elseif ($_GET['act'] == 'hapus') {
    $id = intval($_GET['id']);
    $mysqli->query("DELETE FROM kamar WHERE id_kamar=$id");
    header('Location: kamar_data.php');
    exit;
}

// --- EDIT KAMAR ---
elseif ($_GET['act'] == 'edit') {
    $id_kamar = intval($_POST['id_kamar']);

    // Validasi kode_kamar unik, kecuali milik sendiri!
    $kode_kamar = $_POST['kode_kamar'];
    $cek = $mysqli->prepare("SELECT 1 FROM kamar WHERE kode_kamar=? AND id_kamar!=?");
    $cek->bind_param('si', $kode_kamar, $id_kamar);
    $cek->execute();
    if ($cek->get_result()->fetch_assoc()) {
        echo "<div class='container'><h2>Error</h2>
        Kode kamar <b>" . htmlspecialchars($kode_kamar) . "</b> sudah pernah dipakai!
        <br><a href='kamar_edit.php?id=$id_kamar'>Kembali</a></div>";
        exit;
    }
    $foto_cover = null;
    if (!empty($_FILES['foto_cover']['name'])) {
        $foto_cover = upload_process($_FILES['foto_cover'], 'kamar');
    }

    if ($foto_cover) {
        $stmt = $mysqli->prepare(
            "UPDATE kamar SET kode_kamar=?, id_tipe=?, lantai=?, luas_m2=?, harga=?, foto_cover=?, catatan=? WHERE id_kamar=?"
        );
        $stmt->bind_param(
            'siidissi',
            $_POST['kode_kamar'],
            $_POST['id_tipe'],
            $_POST['lantai'],
            $_POST['luas_m2'],
            $_POST['harga'],
            $foto_cover,
            $_POST['catatan'],
            $id_kamar
        );
    } else {
        $stmt = $mysqli->prepare(
            "UPDATE kamar SET kode_kamar=?, id_tipe=?, lantai=?, luas_m2=?, harga=?, catatan=? WHERE id_kamar=?"
        );
        $stmt->bind_param(
            'siidssi',
            $_POST['kode_kamar'],
            $_POST['id_tipe'],
            $_POST['lantai'],
            $_POST['luas_m2'],
            $_POST['harga'],
            $_POST['catatan'],
            $id_kamar
        );
    }
    $stmt->execute();
    header('Location: kamar_data.php');
    exit;
}

// Jika act tidak dikenali
header('Location: kamar_data.php');
exit;
?>  