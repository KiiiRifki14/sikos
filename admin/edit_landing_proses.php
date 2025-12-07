<?php
session_start();
require '../inc/koneksi.php';
require '../inc/guard.php';

if (!is_admin() && !is_owner()) { header('Location: ../login.php'); exit; }

$act = $_GET['act'] ?? '';
$db = new Database();

if ($act == 'update_pengaturan') {
    $wa = $_POST['no_wa'];
    $fb = $_POST['link_fb'];
    $ig = $_POST['link_ig'];
    $footer = $_POST['deskripsi_footer'];
    $logo_path = null;

    // Handle Upload Logo (Opt)
    if (!empty($_FILES['foto_logo']['name'])) {
        $ext = pathinfo($_FILES['foto_logo']['name'], PATHINFO_EXTENSION);
        $nama_file = "logo_sikos_" . time() . "." . $ext;
        $target = "../assets/uploads/" . $nama_file;
        if (move_uploaded_file($_FILES['foto_logo']['tmp_name'], $target)) {
            $logo_path = "assets/uploads/" . $nama_file;
        }
    }

    if ($db->update_pengaturan_landing($wa, $fb, $ig, $footer, $logo_path)) {
        pesan_error('edit_landing.php', '✅ Pengaturan Halaman Utama berhasil disimpan!');
    } else {
        pesan_error('edit_landing.php', 'Gagal menyimpan pengaturan.');
    }
}

if ($act == 'tambah_fasilitas') {
    $judul = $_POST['judul'];
    $deskripsi = $_POST['deskripsi'];
    $icon = $_POST['icon'];

    if ($db->tambah_fasilitas($judul, $deskripsi, $icon)) {
        pesan_error('edit_landing.php', '✅ Fasilitas berhasil ditambahkan!');
    } else {
        pesan_error('edit_landing.php', 'Gagal menambah fasilitas.');
    }
}

if ($act == 'hapus_fasilitas') {
    $id = (int)$_GET['id'];
    if ($db->hapus_fasilitas($id)) {
        pesan_error('edit_landing.php', '✅ Fasilitas dihapus.');
    } else {
        pesan_error('edit_landing.php', 'Gagal menghapus fasilitas.');
    }
}
?>
