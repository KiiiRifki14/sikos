<?php
session_start();
require '../inc/koneksi.php';
require '../inc/guard.php';
require '../inc/upload.php';

if (!is_admin()) { die('Forbidden'); }

$act = $_GET['act'] ?? '';

// --- TAMBAH KAMAR ---
if ($act == 'tambah') {
    $kode_kamar = $_POST['kode_kamar'];
    // Validasi unik
    $cek = $mysqli->prepare("SELECT 1 FROM kamar WHERE kode_kamar=?");
    $cek->bind_param('s', $kode_kamar);
    $cek->execute();
    if ($cek->get_result()->fetch_assoc()) {
        die("Kode kamar sudah ada! <a href='kamar_tambah.php'>Kembali</a>");
    }

    $foto_cover = null;
    if (!empty($_FILES['foto_cover']['name'])) {
        $foto_cover = upload_process($_FILES['foto_cover'], 'kamar');
    }

    $stmt = $mysqli->prepare("INSERT INTO kamar (kode_kamar, id_tipe, lantai, luas_m2, harga, status_kamar, foto_cover, catatan) VALUES (?, ?, ?, ?, ?, 'TERSEDIA', ?, ?)");
    $stmt->bind_param('siidiss', $_POST['kode_kamar'], $_POST['id_tipe'], $_POST['lantai'], $_POST['luas_m2'], $_POST['harga'], $foto_cover, $_POST['catatan']);
    $stmt->execute();
    
    // Ambil ID kamar barusan untuk upload galeri (opsional jika ingin fitur ini di tambah juga)
    // $id_kamar_baru = $stmt->insert_id;

    header('Location: kamar_data.php');
    exit;
}

// --- HAPUS KAMAR ---
elseif ($act == 'hapus') {
    $id = intval($_GET['id']);
    // Hapus foto cover fisik (opsional: implementasi unlink)
    // Hapus foto galeri fisik (opsional)
    $mysqli->query("DELETE FROM kamar WHERE id_kamar=$id");
    $mysqli->query("DELETE FROM kamar_foto WHERE id_kamar=$id");
    header('Location: kamar_data.php');
    exit;
}

// --- HAPUS FOTO GALERI (Fitur Baru) ---
elseif ($act == 'hapus_foto') {
    $id_foto = intval($_GET['id_foto']);
    $id_kamar = intval($_GET['id_kamar']); // Untuk redirect balik
    
    // Ambil nama file untuk dihapus dari folder
    $q = $mysqli->query("SELECT file_nama FROM kamar_foto WHERE id_foto=$id_foto");
    if ($row = $q->fetch_assoc()) {
        $path = __DIR__ . '/../assets/uploads/kamar/' . $row['file_nama'];
        if (file_exists($path)) unlink($path);
    }
    
    $mysqli->query("DELETE FROM kamar_foto WHERE id_foto=$id_foto");
    header("Location: kamar_edit.php?id=$id_kamar");
    exit;
}

// --- EDIT KAMAR ---
elseif ($act == 'edit') {
    $id_kamar = intval($_POST['id_kamar']);
    $kode_kamar = $_POST['kode_kamar'];

    // Validasi kode unik
    $cek = $mysqli->prepare("SELECT 1 FROM kamar WHERE kode_kamar=? AND id_kamar!=?");
    $cek->bind_param('si', $kode_kamar, $id_kamar);
    $cek->execute();
    if ($cek->get_result()->fetch_assoc()) {
        die("Kode kamar sudah dipakai! <a href='kamar_edit.php?id=$id_kamar'>Kembali</a>");
    }

    // 1. Update Data Utama & Foto Cover
    $foto_cover = null;
    if (!empty($_FILES['foto_cover']['name'])) {
        $foto_cover = upload_process($_FILES['foto_cover'], 'kamar');
    }

    if ($foto_cover) {
        $stmt = $mysqli->prepare("UPDATE kamar SET kode_kamar=?, id_tipe=?, lantai=?, luas_m2=?, harga=?, foto_cover=?, catatan=? WHERE id_kamar=?");
        $stmt->bind_param('siidissi', $kode_kamar, $_POST['id_tipe'], $_POST['lantai'], $_POST['luas_m2'], $_POST['harga'], $foto_cover, $_POST['catatan'], $id_kamar);
    } else {
        $stmt = $mysqli->prepare("UPDATE kamar SET kode_kamar=?, id_tipe=?, lantai=?, luas_m2=?, harga=?, catatan=? WHERE id_kamar=?");
        $stmt->bind_param('siidssi', $kode_kamar, $_POST['id_tipe'], $_POST['lantai'], $_POST['luas_m2'], $_POST['harga'], $_POST['catatan'], $id_kamar);
    }
    $stmt->execute();

    // 2. Proses Upload Galeri (Multiple)
    if (!empty($_FILES['foto_galeri']['name'][0])) {
        $files = $_FILES['foto_galeri'];
        $count = count($files['name']);
        
        // Loop setiap file yang diupload
        for ($i = 0; $i < $count; $i++) {
            if ($files['error'][$i] == 0) {
                // Kita harus menyusun array file manual agar fungsi upload_process menerimanya
                $tmp_file = [
                    'name' => $files['name'][$i],
                    'type' => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error' => $files['error'][$i],
                    'size' => $files['size'][$i]
                ];
                
                // Upload dan simpan nama file
                $nama_baru = upload_process($tmp_file, 'kamar');
                
                // Insert ke tabel kamar_foto
                $ins = $mysqli->prepare("INSERT INTO kamar_foto (id_kamar, file_nama) VALUES (?, ?)");
                $ins->bind_param('is', $id_kamar, $nama_baru);
                $ins->execute();
            }
        }
    }

    header('Location: kamar_data.php');
    exit;
}

header('Location: kamar_data.php');
?>