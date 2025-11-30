<?php
session_start();
require '../inc/koneksi.php';
require '../inc/guard.php';
require '../inc/upload.php'; // Pastikan file ini ada fungsi upload_process()

if (!is_admin()) { die('Forbidden'); }

$db = new Database();
$act = $_GET['act'] ?? '';

// ==========================================================
// 1. PROSES TAMBAH KAMAR
// ==========================================================
if ($act == 'tambah') {
    $foto_cover = null;
    if (!empty($_FILES['foto_cover']['name'])) {
        $foto_cover = upload_process($_FILES['foto_cover'], 'kamar');
    }

    // A. Insert Data Kamar
    $stmt = $mysqli->prepare("INSERT INTO kamar (kode_kamar, id_tipe, lantai, luas_m2, harga, status_kamar, foto_cover, catatan) VALUES (?, ?, ?, ?, ?, 'TERSEDIA', ?, ?)");
    $stmt->bind_param('siidiss', $_POST['kode_kamar'], $_POST['id_tipe'], $_POST['lantai'], $_POST['luas_m2'], $_POST['harga'], $foto_cover, $_POST['catatan']);
    
    if ($stmt->execute()) {
        $id_kamar_baru = $stmt->insert_id;

        // B. Simpan Fasilitas (Checklist)
        if (isset($_POST['fasilitas']) && is_array($_POST['fasilitas'])) {
            $stmt_fas = $mysqli->prepare("INSERT INTO kamar_fasilitas (id_kamar, id_fasilitas) VALUES (?, ?)");
            foreach ($_POST['fasilitas'] as $id_fas) {
                $stmt_fas->bind_param('ii', $id_kamar_baru, $id_fas);
                $stmt_fas->execute();
            }
        }

        // Catat Log
        $db->catat_log($_SESSION['id_pengguna'], 'TAMBAH KAMAR', "Menambah kamar " . $_POST['kode_kamar']);
        header('Location: kamar_data.php');
    } else {
        echo "<script>alert('Gagal tambah! Kode kamar mungkin duplikat.'); window.history.back();</script>";
    }
}

// ==========================================================
// 2. PROSES EDIT KAMAR (PERBAIKAN UTAMA DISINI)
// ==========================================================
elseif ($act == 'edit') {
    $id_kamar = intval($_POST['id_kamar']);
    
    // A. Cek Upload Foto Cover Baru
    $foto_cover = null;
    if (!empty($_FILES['foto_cover']['name'])) {
        $foto_cover = upload_process($_FILES['foto_cover'], 'kamar');
    }

    // B. Update Data Utama
    $query = "UPDATE kamar SET kode_kamar=?, id_tipe=?, lantai=?, luas_m2=?, harga=?, catatan=?";
    if ($foto_cover) { $query .= ", foto_cover=?"; } // Update foto jika ada upload baru
    $query .= " WHERE id_kamar=?";
    
    $stmt = $mysqli->prepare($query);
    
    if ($foto_cover) {
        $stmt->bind_param('siidissi', $_POST['kode_kamar'], $_POST['id_tipe'], $_POST['lantai'], $_POST['luas_m2'], $_POST['harga'], $_POST['catatan'], $foto_cover, $id_kamar);
    } else {
        $stmt->bind_param('siidisi', $_POST['kode_kamar'], $_POST['id_tipe'], $_POST['lantai'], $_POST['luas_m2'], $_POST['harga'], $_POST['catatan'], $id_kamar);
    }

    if ($stmt->execute()) {
        // --- BAGIAN PENTING: UPDATE FASILITAS ---
        
        // 1. Hapus SEMUA fasilitas lama milik kamar ini
        $mysqli->query("DELETE FROM kamar_fasilitas WHERE id_kamar = $id_kamar");

        // 2. Masukkan ulang fasilitas yang baru dicentang
        if (isset($_POST['fasilitas']) && is_array($_POST['fasilitas'])) {
            $stmt_fas = $mysqli->prepare("INSERT INTO kamar_fasilitas (id_kamar, id_fasilitas) VALUES (?, ?)");
            foreach ($_POST['fasilitas'] as $id_fas) {
                $stmt_fas->bind_param('ii', $id_kamar, $id_fas);
                $stmt_fas->execute();
            }
        }

        // --- TAMBAHAN: UPDATE GALERI FOTO (Multiple) ---
        if (!empty($_FILES['foto_galeri']['name'][0])) {
            $total = count($_FILES['foto_galeri']['name']);
            $stmt_gal = $mysqli->prepare("INSERT INTO kamar_foto (id_kamar, file_nama) VALUES (?, ?)");
            
            for ($i = 0; $i < $total; $i++) {
                if ($_FILES['foto_galeri']['error'][$i] == 0) {
                    // Siapkan array temporary menyerupai $_FILES tunggal agar fungsi upload_process bisa dipakai
                    $file_tmp = [
                        'name'     => $_FILES['foto_galeri']['name'][$i],
                        'type'     => $_FILES['foto_galeri']['type'][$i],
                        'tmp_name' => $_FILES['foto_galeri']['tmp_name'][$i],
                        'error'    => $_FILES['foto_galeri']['error'][$i],
                        'size'     => $_FILES['foto_galeri']['size'][$i]
                    ];
                    
                    $nama_file = upload_process($file_tmp, 'kamar'); // Upload
                    if ($nama_file) {
                        $stmt_gal->bind_param('is', $id_kamar, $nama_file);
                        $stmt_gal->execute();
                    }
                }
            }
        }

        $db->catat_log($_SESSION['id_pengguna'], 'EDIT KAMAR', "Update kamar ID: $id_kamar");
        header('Location: kamar_data.php');
    } else {
        echo "<script>alert('Gagal update data!'); window.history.back();</script>";
    }
}

// ==========================================================
// 3. PROSES HAPUS KAMAR
// ==========================================================
elseif ($act == 'hapus') {
    $id = intval($_GET['id']);
    $db->hapus_kamar($id); // Pastikan fungsi ini menghapus data di kamar, kamar_fasilitas, dan kamar_foto
    header('Location: kamar_data.php');
}

// ==========================================================
// 4. PROSES HAPUS SATU FOTO GALERI
// ==========================================================
elseif ($act == 'hapus_foto') {
    $id_foto = intval($_GET['id_foto']);
    $id_kamar = intval($_GET['id_kamar']);

    // Ambil nama file untuk dihapus dari folder
    $q = $mysqli->query("SELECT file_nama FROM kamar_foto WHERE id_foto=$id_foto");
    if ($row = $q->fetch_assoc()) {
        $path = "../assets/uploads/kamar/" . $row['file_nama'];
        if (file_exists($path)) unlink($path); // Hapus file fisik
    }

    // Hapus dari database
    $mysqli->query("DELETE FROM kamar_foto WHERE id_foto=$id_foto");
    
    // Kembali ke halaman edit
    header("Location: kamar_edit.php?id=$id_kamar");
}
?>