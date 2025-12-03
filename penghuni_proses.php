<?php
session_start();
require 'inc/koneksi.php';
require 'inc/upload.php'; // Panggil helper upload

$id_pengguna = $_SESSION['id_pengguna'] ?? 0;
if (!$id_pengguna) { header('Location: login.php'); exit; }

// Pastikan data penghuni ada di tabel penghuni
// (Kadang user baru register, data di tabel 'penghuni' belum dibuat)
$cek = $mysqli->query("SELECT id_penghuni, foto_profil FROM penghuni WHERE id_pengguna=$id_pengguna");
if ($cek->num_rows == 0) {
    $mysqli->query("INSERT INTO penghuni (id_pengguna) VALUES ($id_pengguna)");
    $old_foto = null;
} else {
    $old_foto = $cek->fetch_object()->foto_profil;
}

// LOGIC UPDATE PROFIL
if (isset($_POST['act']) && $_POST['act'] == 'update_profil') {
    
    $nama = htmlspecialchars($_POST['nama']);
    $hp   = htmlspecialchars($_POST['no_hp']);
    $alamat = htmlspecialchars($_POST['alamat']);
    $kerja  = htmlspecialchars($_POST['pekerjaan']);
    $emer   = htmlspecialchars($_POST['emergency_cp']);
    
    // 1. Update Data Dasar (Tabel Pengguna)
    $stmt1 = $mysqli->prepare("UPDATE pengguna SET nama=?, no_hp=? WHERE id_pengguna=?");
    $stmt1->bind_param('ssi', $nama, $hp, $id_pengguna);
    $stmt1->execute();

    // 2. Update Detail Penghuni
    // Cek Foto
    $foto_final = $old_foto; // Default pake foto lama
    
    if (!empty($_FILES['foto_profil']['name'])) {
        $upload = upload_process($_FILES['foto_profil'], 'profil'); // Simpan ke folder assets/uploads/profil
        if ($upload) {
            $foto_final = $upload;
            // Hapus foto lama biar hemat storage
            if ($old_foto && file_exists("assets/uploads/profil/$old_foto")) {
                unlink("assets/uploads/profil/$old_foto");
            }
        }
    }

    $stmt2 = $mysqli->prepare("UPDATE penghuni SET alamat=?, pekerjaan=?, emergency_cp=?, foto_profil=? WHERE id_pengguna=?");
    $stmt2->bind_param('ssssi', $alamat, $kerja, $emer, $foto_final, $id_pengguna);
    $stmt2->execute();

    // 3. Update Password (Jika diisi)
    if (!empty($_POST['pass_baru'])) {
        $pass_baru = $_POST['pass_baru'];
        $pass_konf = $_POST['pass_konfirm'];

        if ($pass_baru === $pass_konf && strlen($pass_baru) >= 8) {
            $hash = password_hash($pass_baru, PASSWORD_DEFAULT);
            $stmt3 = $mysqli->prepare("UPDATE pengguna SET password_hash=? WHERE id_pengguna=?");
            $stmt3->bind_param('si', $hash, $id_pengguna);
            $stmt3->execute();
        } else {
            header('Location: profil.php?error=Password tidak sama atau kurang dari 8 karakter');
            exit;
        }
    }

    header('Location: profil.php?msg=updated');
}
?>