<?php
// [OOP: Session]
session_start();
// [OOP: Dependencies] Load semua helper yang dibutuhkan
require '../inc/utils.php'; // Helper umum
require '../inc/koneksi.php'; // DB Class
require '../inc/guard.php'; // Auth guard
require '../inc/upload.php'; // File uploader helper

// [Security] Hanya admin yang boleh manipulasi data kamar
if (!is_admin()) {
    die('Forbidden');
}

// Init Database
$db = new Database();
$act = $_GET['act'] ?? ''; // Menentukan jenis aksi (tambah/edit/hapus)

// ==========================================================================
// LOGIKA 1: TAMBAH KAMAR BARU
// ==========================================================================
if ($act == 'tambah') {
    $foto_cover = null;
    // [File Upload Handling] Cek apakah user upload cover?
    if (!empty($_FILES['foto_cover']['name'])) {
        // Panggil helper upload untuk validasi & pemindahan file
        $foto_cover = upload_process($_FILES['foto_cover'], 'kamar');
    }

    // [Database: Prepared Statement] Wajib pakai ini untuk mencegah SQL Injection
    // Masukkan data kamar baru ke tabel `kamar`
    $stmt = $mysqli->prepare("INSERT INTO kamar (kode_kamar, id_tipe, lantai, luas_m2, harga, status_kamar, foto_cover, catatan) VALUES (?, ?, ?, ?, ?, 'TERSEDIA', ?, ?)");
    $stmt->bind_param('siidiss', $_POST['kode_kamar'], $_POST['id_tipe'], $_POST['lantai'], $_POST['luas_m2'], $_POST['harga'], $foto_cover, $_POST['catatan']);

    if ($stmt->execute()) {
        // Ambil ID kamar yang baru saja terbentuk (auto-increment id)
        $id_kamar_baru = $stmt->insert_id;

        // [Relational Data] Simpan Fasilitas yang dipilih (Checkboxes)
        // Hubungan Many-to-Many (Kamar <-> Fasilitas)
        if (isset($_POST['fasilitas']) && is_array($_POST['fasilitas'])) {
            $stmt_fas = $mysqli->prepare("INSERT INTO kamar_fasilitas (id_kamar, id_fasilitas) VALUES (?, ?)");
            foreach ($_POST['fasilitas'] as $id_fas) {
                // Loop setiap ID fasilitas yang dicentang dan simpan ke DB
                $stmt_fas->bind_param('ii', $id_kamar_baru, $id_fas);
                $stmt_fas->execute();
            }
        }

        // [Multi-Upload] Simpan Galeri Foto (Banyak File sekaligus)
        if (!empty($_FILES['foto_galeri']['name'][0])) {
            $files = $_FILES['foto_galeri'];
            $count = count($files['name']); // Hitung berapa file yg diupload

            $stmt_gal = $mysqli->prepare("INSERT INTO kamar_foto (id_kamar, file_nama) VALUES (?, ?)");

            for ($i = 0; $i < $count; $i++) {
                if ($files['error'][$i] === 0) {
                    // Normalize array structure agar kompatibel dengan fungsi upload_process
                    $file_item = [
                        'name'     => $files['name'][$i],
                        'type'     => $files['type'][$i],
                        'tmp_name' => $files['tmp_name'][$i],
                        'error'    => $files['error'][$i],
                        'size'     => $files['size'][$i]
                    ];

                    $uploaded = upload_process($file_item, 'kamar'); // Reuse helper
                    if ($uploaded) {
                        $stmt_gal->bind_param('is', $id_kamar_baru, $uploaded);
                        $stmt_gal->execute();
                    }
                }
            }
        }

        // [Audit Trail] Catat siapa yang menambah kamar ini
        $db->catat_log($_SESSION['id_pengguna'], 'TAMBAH KAMAR', "Menambah kamar " . $_POST['kode_kamar']);
        header('Location: kamar_data.php');
    } else {
        // [Cleanup] Jika insert DB gagal, wajib hapus file yang sudah terlanjur diupload
        // Supaya tidak jadi file sampah (junk files) di server
        if ($foto_cover && file_exists("../assets/uploads/kamar/$foto_cover")) {
            unlink("../assets/uploads/kamar/$foto_cover");
        }
        echo "<script>alert('Gagal tambah! Kode kamar mungkin duplikat.'); window.history.back();</script>";
    }
}

// ==========================================================================
// LOGIKA 2: UPDATE / EDIT KAMAR
// ==========================================================================
elseif ($act == 'edit') {
    $id_kamar = intval($_POST['id_kamar']);
    $foto_cover = null;

    // Logic: Jika ada upload foto cover baru, foto LAMA wajib dihapus
    if (!empty($_FILES['foto_cover']['name'])) {
        // 1. Cari nama foto lama di database
        $q_lama = $mysqli->query("SELECT foto_cover FROM kamar WHERE id_kamar=$id_kamar");
        $d_lama = $q_lama->fetch_assoc();
        $file_lama = $d_lama['foto_cover'];

        // 2. Hapus file fisik lama dari folder uploads
        // Menggunakan unlink()
        if ($file_lama && file_exists("../assets/uploads/kamar/$file_lama")) {
            unlink("../assets/uploads/kamar/$file_lama");
        }

        // 3. Upload foto baru pengganti
        $foto_cover = upload_process($_FILES['foto_cover'], 'kamar');
    }

    // Build Query dinamis (tergantung apakah foto cover diganti atau tidak)
    $query = "UPDATE kamar SET kode_kamar=?, id_tipe=?, lantai=?, luas_m2=?, harga=?, catatan=?";
    if ($foto_cover) {
        $query .= ", foto_cover=?";
    }
    $query .= " WHERE id_kamar=?";

    // Execute Statement
    $stmt = $mysqli->prepare($query);
    if ($foto_cover) {
        $stmt->bind_param('siidissi', $_POST['kode_kamar'], $_POST['id_tipe'], $_POST['lantai'], $_POST['luas_m2'], $_POST['harga'], $_POST['catatan'], $foto_cover, $id_kamar);
    } else {
        $stmt->bind_param('siidisi', $_POST['kode_kamar'], $_POST['id_tipe'], $_POST['lantai'], $_POST['luas_m2'], $_POST['harga'], $_POST['catatan'], $id_kamar);
    }

    if ($stmt->execute()) {
        // [Feature: Sync Fasilitas]
        // Cara termudah update Checkbox adalah: HAPUS SEMUA dulu, lalu INSERT ULANG yang baru
        $mysqli->query("DELETE FROM kamar_fasilitas WHERE id_kamar = $id_kamar");
        if (isset($_POST['fasilitas']) && is_array($_POST['fasilitas'])) {
            $stmt_fas = $mysqli->prepare("INSERT INTO kamar_fasilitas (id_kamar, id_fasilitas) VALUES (?, ?)");
            foreach ($_POST['fasilitas'] as $id_fas) {
                $stmt_fas->bind_param('ii', $id_kamar, $id_fas);
                $stmt_fas->execute();
            }
        }

        // Upload Galeri Foto Tambahan (Jika ada)
        if (!empty($_FILES['foto_galeri']['name'][0])) {
            $files = $_FILES['foto_galeri'];
            $count = count($files['name']);

            $stmt_gal = $mysqli->prepare("INSERT INTO kamar_foto (id_kamar, file_nama) VALUES (?, ?)");

            for ($i = 0; $i < $count; $i++) {
                if ($files['error'][$i] === 0) {
                    $file_item = [
                        'name'     => $files['name'][$i],
                        'type'     => $files['type'][$i],
                        'tmp_name' => $files['tmp_name'][$i],
                        'error'    => $files['error'][$i],
                        'size'     => $files['size'][$i]
                    ];
                    $uploaded = upload_process($file_item, 'kamar');
                    if ($uploaded) {
                        $stmt_gal->bind_param('is', $id_kamar, $uploaded);
                        $stmt_gal->execute();
                    }
                }
            }
        }

        // [Audit Trail] Log update
        $db->catat_log($_SESSION['id_pengguna'], 'EDIT KAMAR', "Update data kamar " . $_POST['kode_kamar']);
        header('Location: kamar_data.php');
    } else {
        echo "<script>alert('Gagal update data!'); window.history.back();</script>";
    }
}


// ==========================================================================
// LOGIKA 3: HAPUS KAMAR
// ==========================================================================
elseif ($act == 'hapus') {
    $id = intval($_GET['id']);

    // Ambil kode kamar dulu untuk keperluan pencatatan log (sebelum datanya hilang)
    $k = $mysqli->query("SELECT kode_kamar FROM kamar WHERE id_kamar=$id")->fetch_object();
    $kode = $k ? $k->kode_kamar : 'Unknown';

    // [OOP: Method Call] Panggil fungsi hapus yang aman (memastikan tidak ada relasi anak)
    $hasil = $db->hapus_kamar($id);

    if ($hasil == "SUKSES") {
        // Jika aman, log sukses
        $db->catat_log($_SESSION['id_pengguna'], 'HAPUS KAMAR', "Menghapus kamar $kode");
        header('Location: kamar_data.php?msg=deleted');
    } else {
        // Jika gagal karena ada data anak (Foreign Key Constraint), beri tahu user
        echo "<script>
            alert('$hasil'); 
            window.location.href = 'kamar_data.php';
        </script>";
    }
}

// ==========================================================================
// LOGIKA 4: HAPUS FOTO GALERI (SATUAN)
// ==========================================================================
elseif ($act == 'hapus_foto') {
    $id_foto = intval($_GET['id_foto']);
    $id_kamar = intval($_GET['id_kamar']);

    // Cari nama file
    $q = $mysqli->query("SELECT file_nama FROM kamar_foto WHERE id_foto=$id_foto");
    if ($row = $q->fetch_assoc()) {
        // Hapus fisik file
        $path = "../assets/uploads/kamar/" . $row['file_nama'];
        if (file_exists($path)) unlink($path);
    }
    // Hapus record database
    $mysqli->query("DELETE FROM kamar_foto WHERE id_foto=$id_foto");

    // [Audit Trail] Log penghapusan foto
    $db->catat_log($_SESSION['id_pengguna'], 'HAPUS FOTO', "Menghapus foto galeri kamar ID $id_kamar");

    header("Location: kamar_edit.php?id=$id_kamar");
}
