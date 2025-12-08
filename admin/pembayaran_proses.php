<?php
session_start();
require '../inc/koneksi.php';
require '../inc/guard.php';

if (!is_admin() && !is_owner()) {
    pesan_error("../login.php", "Akses Ditolak.");
}

$db = new Database();
$act = $_GET['act'] ?? '';
$id  = intval($_GET['id'] ?? 0);

// Helper: Ambil Info Pembayaran untuk Log
function get_pay_info($mysqli, $id)
{
    $q = "SELECT p.jumlah, u.nama FROM pembayaran p 
          LEFT JOIN tagihan t ON p.ref_id = t.id_tagihan AND p.ref_type='TAGIHAN'
          LEFT JOIN booking b ON p.ref_id = b.id_booking AND p.ref_type='BOOKING'
          LEFT JOIN kontrak k ON t.id_kontrak = k.id_kontrak
          LEFT JOIN penghuni ph ON k.id_penghuni = ph.id_penghuni
          LEFT JOIN pengguna u ON (ph.id_pengguna = u.id_pengguna OR b.id_pengguna = u.id_pengguna)
          WHERE p.id_pembayaran = $id";
    return $mysqli->query($q)->fetch_assoc();
}

// 1. TERIMA PEMBAYARAN
if ($act == 'terima' && $id) {
    $info = get_pay_info($mysqli, $id);
    $nominal = number_format($info['jumlah'] ?? 0);
    $nama = $info['nama'] ?? 'User';

    // Update status pembayaran
    $mysqli->query("UPDATE pembayaran SET status='DITERIMA', waktu_verifikasi=NOW() WHERE id_pembayaran=$id");

    // Cek Tipe untuk aksi lanjutan (Booking/Tagihan)
    $cek = $mysqli->query("SELECT ref_type, ref_id FROM pembayaran WHERE id_pembayaran=$id")->fetch_assoc();

    if ($cek['ref_type'] == 'BOOKING') {
        $db->setujui_booking_dan_buat_kontrak($cek['ref_id']);
    } else if ($cek['ref_type'] == 'TAGIHAN') {
        // [SECURITY] Cek Nominal Tagihan Asli
        $q_tag = $mysqli->query("SELECT nominal FROM tagihan WHERE id_tagihan={$cek['ref_id']}");
        $d_tag = $q_tag->fetch_assoc();

        if ($d_tag && $info['jumlah'] < $d_tag['nominal']) {
            // Jika kurang bayar, jangan lunaskan. 
            // Opsional: Bisa partial payment, tapi untuk sekarang kita Block agar aman.
            pesan_error("keuangan_index.php?tab=verifikasi", "❌ Gagal Verifikasi! Nominal bayar (Rp $nominal) lebih kecil dari tagihan asli (Rp " . number_format($d_tag['nominal']) . "). Silakan TOLAK pembayaran ini.");
        }

        $mysqli->query("UPDATE tagihan SET status='LUNAS' WHERE id_tagihan={$cek['ref_id']}");
    }

    // LOG
    $db->catat_log($_SESSION['id_pengguna'], 'VERIFIKASI BAYAR', "Menerima pembayaran Rp $nominal dari $nama (ID: $id)");

    $_SESSION['swal_title'] = "Berhasil Diverifikasi!";
    $_SESSION['swal_text'] = "Pembayaran sebesar Rp $nominal telah diterima.";
    $_SESSION['swal_icon'] = "success";

    header("Location: keuangan_index.php?tab=verifikasi");
    exit;
}
// 2. TOLAK PEMBAYARAN
else if ($act == 'tolak' && $id) {
    $info = get_pay_info($mysqli, $id);
    $nama = $info['nama'] ?? 'User';

    $mysqli->query("UPDATE pembayaran SET status='DITOLAK', waktu_verifikasi=NOW() WHERE id_pembayaran=$id");

    // LOG
    $db->catat_log($_SESSION['id_pengguna'], 'TOLAK BAYAR', "Menolak pembayaran dari $nama (ID: $id)");

    $_SESSION['swal_title'] = "Pembayaran Ditolak";
    $_SESSION['swal_text'] = "Status pembayaran telah diubah menjadi DITOLAK.";
    $_SESSION['swal_icon'] = "error";

    header("Location: keuangan_index.php?tab=verifikasi");
    exit;
}
// 3. GENERATE TAGIHAN MASAL
else if ($act == 'generate_masal' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $bulan = $_POST['bulan_tagih'];
    $jumlah = $db->generate_tagihan_masal($bulan);

    // LOG
    if ($jumlah > 0) {
        $db->catat_log($_SESSION['id_pengguna'], 'GENERATE TAGIHAN', "Membuat $jumlah tagihan otomatis untuk bulan $bulan");
        pesan_error("keuangan_index.php?tab=tagihan&bulan=$bulan", "✅ Sukses membuat $jumlah tagihan baru.");
    } else {
        $db->catat_log($_SESSION['id_pengguna'], 'GENERATE TAGIHAN', "Mencoba generate tagihan bulan $bulan (Tidak ada tagihan baru).");
        pesan_error("keuangan_index.php?tab=tagihan&bulan=$bulan", "ℹ️ Tidak ada tagihan baru yang dibuat.");
    }
}

// 4. BAYAR CASH (Manual)
else if ($act == 'bayar_cash' && $id) {
    // Ambil info tagihan dulu
    $q_tag = $mysqli->query("SELECT t.nominal, u.nama, t.bulan_tagih 
                             FROM tagihan t 
                             JOIN kontrak k ON t.id_kontrak=k.id_kontrak 
                             JOIN penghuni p ON k.id_penghuni=p.id_penghuni 
                             JOIN pengguna u ON p.id_pengguna=u.id_pengguna 
                             WHERE t.id_tagihan=$id");
    $d_tag = $q_tag->fetch_assoc();

    $sukses = $db->bayar_tagihan_cash($id);
    $tgl = $d_tag['bulan_tagih'] ?? date('Y-m');

    if ($sukses) {
        // LOG
        $nominal = number_format($d_tag['nominal']);
        $db->catat_log($_SESSION['id_pengguna'], 'TERIMA CASH', "Menerima pembayaran TUNAI Rp $nominal dari {$d_tag['nama']}");
        pesan_error("keuangan_index.php?tab=tagihan&bulan=$tgl", "💰 Pembayaran Tunai Berhasil Dicatat!");
    } else {
        pesan_error("keuangan_index.php?tab=tagihan&bulan=$tgl", "❌ Gagal mencatat pembayaran.");
    }
}
// 5. HAPUS TAGIHAN
else if ($act == 'hapus_tagihan' && $id) {
    // Cek dulu apakah statusnya BELUM lunas (security)
    $cek = $mysqli->query("SELECT status, bulan_tagih FROM tagihan WHERE id_tagihan=$id")->fetch_assoc();

    if ($cek && $cek['status'] == 'BELUM') {
        $mysqli->query("DELETE FROM tagihan WHERE id_tagihan=$id");
        $db->catat_log($_SESSION['id_pengguna'], 'HAPUS TAGIHAN', "Menghapus tagihan ID $id ({$cek['bulan_tagih']})");
        pesan_error("keuangan_index.php?tab=tagihan&bulan={$cek['bulan_tagih']}", "✅ Tagihan berhasil dihapus.");
    } else {
        pesan_error("keuangan_index.php?tab=tagihan", "❌ Gagal menghapus. Tagihan tidak ditemukan atau sudah LUNAS.");
    }
} else {
    header("Location: keuangan_index.php");
}
