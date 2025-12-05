<?php
// Panggil file konfigurasi (opsional, jika ingin tetap pakai konstanta)
require_once __DIR__ . '/config.php';

class Database {
    // PENERAPAN ENKAPSULASI (Pertemuan 7)
    // Property dibuat private agar aman dan hanya bisa diakses class ini    
    // Property koneksi dibuat public agar bisa dipakai di file lain (mysqli query)
    public $koneksi;

    // PENERAPAN CONSTRUCTOR (Pertemuan 6)
    function __construct() {
        // PENERAPAN EXCEPTION HANDLING (Pertemuan 8)
        // Menggunakan Try-Catch untuk menangkap error koneksi
        try {
            // @ sebelum new mysqli berguna untuk menahan error bawaan PHP agar ditangkap catch
            $this->koneksi = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

            // Cek manual jika ada error koneksi
            if ($this->koneksi->connect_error) {
                throw new Exception("Koneksi Database Gagal: " . $this->koneksi->connect_error);
            }
        } catch (Exception $e) {
            // [UPDATE] Error Handling Profesional
            // 1. Log error ke file server (agar admin bisa cek, tapi user tidak lihat)
            error_log("Database Error: " . $e->getMessage());

            // 2. Tampilkan pesan user-friendly
            die('<div style="font-family: sans-serif; text-align: center; padding: 50px;">
                    <h1>Layanan Sedang Pemeliharaan</h1>
                    <p>Maaf, saat ini sistem sedang tidak dapat diakses.</p>
                    <p style="color: #666; font-size: 12px;">(Error Code: DB_CONN_ERR)</p>
                </div>');
        }
    }

    // ==========================================
    // 1. AUTHENTICATION (Login)
    // ==========================================
    function login($email, $password) {
        // Menggunakan Prepared Statement untuk keamanan (SQL Injection)
        $stmt = $this->koneksi->prepare("SELECT id_pengguna, password_hash, peran, status FROM pengguna WHERE email=?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();

        if ($res && $res['status'] == 1 && password_verify($password, $res['password_hash'])) {
            return $res; 
        }
        return false; 
    }
    
    function register($nama, $email, $hp, $password) {
        $cek = $this->koneksi->prepare("SELECT id_pengguna FROM pengguna WHERE email=?");
        $cek->bind_param('s', $email);
        $cek->execute();
        if ($cek->get_result()->num_rows > 0) return "DUPLIKAT";

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->koneksi->prepare("INSERT INTO pengguna (nama, email, no_hp, password_hash, peran, status) VALUES (?, ?, ?, ?, 'PENGHUNI', 1)");
        $stmt->bind_param('ssss', $nama, $email, $hp, $hash);
        return $stmt->execute();
    }

    // =========================================
    // 2. MANAJEMEN KAMAR (CRUD)
    // ==========================================
    function tampil_kamar() {
        $query = "SELECT k.*, t.nama_tipe 
                  FROM kamar k 
                  JOIN tipe_kamar t ON k.id_tipe=t.id_tipe 
                  ORDER BY k.kode_kamar ASC";
        $res = mysqli_query($this->koneksi, $query);
        
        $hasil = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $hasil[] = $row;
        }
        return $hasil;
    }

    // --- FUNGSI TIPE KAMAR YANG WAJIB ADA ---
    function tampil_tipe_kamar() {
        $query = "SELECT * FROM tipe_kamar ORDER BY nama_tipe ASC";
        $res = $this->koneksi->query($query);
        
        $hasil = [];
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $hasil[] = $row;
            }
        }
        return $hasil;
    }
    // ----------------------------------------

    function ambil_kamar_by_id($id) {
        $stmt = $this->koneksi->prepare("SELECT * FROM kamar WHERE id_kamar=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    function tambah_kamar($kode, $tipe, $lantai, $luas, $harga, $foto, $catatan) {
        // Cek Kode Kamar
        $cek = $this->koneksi->prepare("SELECT 1 FROM kamar WHERE kode_kamar=?");
        $cek->bind_param('s', $kode);
        $cek->execute();
        if ($cek->get_result()->fetch_assoc()) return false;

        $status = 'TERSEDIA';
        $stmt = $this->koneksi->prepare("INSERT INTO kamar (kode_kamar, id_tipe, lantai, luas_m2, harga, status_kamar, foto_cover, catatan) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('siidisss', $kode, $tipe, $lantai, $luas, $harga, $status, $foto, $catatan);
        return $stmt->execute();
    }

    function edit_kamar($id, $kode, $tipe, $lantai, $luas, $harga, $foto, $catatan) {
        // Cek Kode Kamar (kecuali punya sendiri)
        $cek = $this->koneksi->prepare("SELECT 1 FROM kamar WHERE kode_kamar=? AND id_kamar!=?");
        $cek->bind_param('si', $kode, $id);
        $cek->execute();
        if ($cek->get_result()->fetch_assoc()) return false;

        if (!empty($foto)) {
            $stmt = $this->koneksi->prepare("UPDATE kamar SET kode_kamar=?, id_tipe=?, lantai=?, luas_m2=?, harga=?, foto_cover=?, catatan=? WHERE id_kamar=?");
            $stmt->bind_param('siidissi', $kode, $tipe, $lantai, $luas, $harga, $foto, $catatan, $id);
        } else {
            $stmt = $this->koneksi->prepare("UPDATE kamar SET kode_kamar=?, id_tipe=?, lantai=?, luas_m2=?, harga=?, catatan=? WHERE id_kamar=?");
            $stmt->bind_param('siidssi', $kode, $tipe, $lantai, $luas, $harga, $catatan, $id);
        }
        return $stmt->execute();
    }

    function hapus_kamar($id) {
        // 1. Cek apakah ada Booking yang masih PENDING
        $cek_booking = $this->koneksi->query("SELECT 1 FROM booking WHERE id_kamar=$id AND status='PENDING'");
        if($cek_booking->num_rows > 0) {
            return "GAGAL: Tidak bisa dihapus! Kamar ini sedang dalam proses Booking (Pending).";
        }

        // 2. Cek apakah ada Kontrak yang masih AKTIF
        $cek_kontrak = $this->koneksi->query("SELECT 1 FROM kontrak WHERE id_kamar=$id AND status='AKTIF'");
        if($cek_kontrak->num_rows > 0) {
            return "GAGAL: Tidak bisa dihapus! Kamar ini sedang terisi (Kontrak Aktif).";
        }

        // 3. Hapus data pendukung
        $this->koneksi->query("DELETE FROM kamar_fasilitas WHERE id_kamar=$id");
        $this->koneksi->query("DELETE FROM kamar_foto WHERE id_kamar=$id");
        
        // 4. Hapus data kamar
        if($this->koneksi->query("DELETE FROM kamar WHERE id_kamar=$id")){
            return "SUKSES";
        }
        
        return "GAGAL: Terjadi kesalahan database saat menghapus.";
    }

    // ==========================================
    // 3. MANAJEMEN BOOKING
    // ==========================================
    function tampil_booking_admin() {
        $sql = "SELECT b.*, g.nama, g.no_hp, k.kode_kamar FROM booking b 
                JOIN pengguna g ON b.id_pengguna=g.id_pengguna
                JOIN kamar k ON b.id_kamar=k.id_kamar
                ORDER BY b.tanggal_booking DESC";
        $res = $this->koneksi->query($sql);
        $hasil = [];
        while($row = $res->fetch_assoc()) { $hasil[] = $row; }
        return $hasil;
    }

    function tambah_booking($id_user, $id_kamar, $checkin, $durasi, $ktp) {
        $stmt = $this->koneksi->prepare("INSERT INTO booking (id_pengguna, id_kamar, checkin_rencana, durasi_bulan_rencana, status, ktp_path_opt) VALUES (?, ?, ?, ?, 'PENDING', ?)");
        $stmt->bind_param('iisis', $id_user, $id_kamar, $checkin, $durasi, $ktp);
        return $stmt->execute();
    }

    function verifikasi_booking($id_booking, $status) {
        $stmt = $this->koneksi->prepare("UPDATE booking SET status=? WHERE id_booking=?");
        $stmt->bind_param('si', $status, $id_booking);
        return $stmt->execute();
    }

    // ==========================================
    // 4. MANAJEMEN PENGHUNI
    // ==========================================
    function tampil_penghuni() {
        $sql = "SELECT p.id_penghuni, u.nama, k.kode_kamar, ko.tanggal_mulai, ko.tanggal_selesai, ko.status
                FROM penghuni p
                JOIN pengguna u ON p.id_pengguna = u.id_pengguna
                LEFT JOIN kontrak ko ON p.id_penghuni = ko.id_penghuni AND ko.status = 'AKTIF'
                LEFT JOIN kamar k ON ko.id_kamar = k.id_kamar
                ORDER BY u.nama ASC";
        
        $res = $this->koneksi->query($sql);
        $hasil = [];
        while($row = $res->fetch_assoc()) { $hasil[] = $row; }
        return $hasil;
    }

    // ==========================================
    // 5. MANAJEMEN KEUANGAN (TAGIHAN)
    // ==========================================
    function get_list_kontrak_aktif() {
        $sql = "SELECT k.id_kontrak, ka.kode_kamar, p.nama 
                FROM kontrak k 
                JOIN kamar ka ON k.id_kamar=ka.id_kamar
                JOIN penghuni ph ON k.id_penghuni=ph.id_penghuni
                JOIN pengguna p ON ph.id_pengguna=p.id_pengguna
                WHERE k.status='AKTIF'";
        return $this->koneksi->query($sql)->fetch_all(MYSQLI_ASSOC);
    }

    function generate_tagihan($id_kontrak, $bulan) {
        $stmt = $this->koneksi->prepare("SELECT ka.harga FROM kontrak k JOIN kamar ka ON k.id_kamar=ka.id_kamar WHERE k.id_kontrak=?");
        $stmt->bind_param('i', $id_kontrak);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        
        if (!$res) return false; 
        
        $nominal = $res['harga'];
        $jatuh_tempo = $bulan . "-10"; 

        $cek = $this->koneksi->prepare("SELECT id_tagihan FROM tagihan WHERE id_kontrak=? AND bulan_tagih=?");
        $cek->bind_param('is', $id_kontrak, $bulan);
        $cek->execute();
        if ($cek->get_result()->num_rows > 0) {
            return "DUPLIKAT"; 
        }

        $ins = $this->koneksi->prepare("INSERT INTO tagihan (id_kontrak, bulan_tagih, nominal, jatuh_tempo, status) VALUES (?, ?, ?, ?, 'BELUM')");
        $ins->bind_param('isis', $id_kontrak, $bulan, $nominal, $jatuh_tempo);
        return $ins->execute();
    }

    function generate_tagihan_masal($bulan_tagih) {
        $sql = "SELECT k.id_kontrak, km.harga 
                FROM kontrak k 
                JOIN kamar km ON k.id_kamar = km.id_kamar 
                WHERE k.status = 'AKTIF'";
        
        $res = $this->koneksi->query($sql);
        $jumlah_sukses = 0;
        $jatuh_tempo = $bulan_tagih . "-10"; 

        $cek_stmt = $this->koneksi->prepare("SELECT id_tagihan FROM tagihan WHERE id_kontrak=? AND bulan_tagih=?");
        $ins_stmt = $this->koneksi->prepare("INSERT INTO tagihan (id_kontrak, bulan_tagih, nominal, jatuh_tempo, status) VALUES (?, ?, ?, ?, 'BELUM')");

        while($row = $res->fetch_assoc()) {
            $id_kontrak = $row['id_kontrak'];
            $harga      = $row['harga']; 

            $cek_stmt->bind_param('is', $id_kontrak, $bulan_tagih);
            $cek_stmt->execute();
            $cek_stmt->store_result();

            if($cek_stmt->num_rows == 0) {
                $ins_stmt->bind_param('isis', $id_kontrak, $bulan_tagih, $harga, $jatuh_tempo);
                if($ins_stmt->execute()) {
                    $jumlah_sukses++;
                }
            }
        }
        return $jumlah_sukses;
    }

    // ==========================================
    // 6. MANAJEMEN PEMBAYARAN
    // ==========================================
    function tambah_pembayaran_tagihan($id_tagihan, $jumlah, $bukti, $metode = 'TRANSFER') {
        $stmt = $this->koneksi->prepare("INSERT INTO pembayaran (ref_type, ref_id, metode, jumlah, bukti_path, status) VALUES ('TAGIHAN', ?, ?, ?, ?, 'PENDING')");
        $stmt->bind_param('isis', $id_tagihan, $metode, $jumlah, $bukti);
        return $stmt->execute();
    }

    function cek_status_pembayaran_terakhir($id_tagihan) {
        $stmt = $this->koneksi->prepare("SELECT status FROM pembayaran WHERE ref_type='TAGIHAN' AND ref_id=? ORDER BY id_pembayaran DESC LIMIT 1");
        $stmt->bind_param('i', $id_tagihan);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        return $res ? $res['status'] : null;
    }

    function bayar_tagihan_cash($id_tagihan, $catatan_admin = 'Pembayaran Tunai ke Admin') {
        $q_tagihan = $this->koneksi->query("SELECT nominal FROM tagihan WHERE id_tagihan=$id_tagihan");
        $data_tagihan = $q_tagihan->fetch_assoc();
        
        if(!$data_tagihan) return false;
        $jumlah = $data_tagihan['nominal'];

        $stmt = $this->koneksi->prepare("INSERT INTO pembayaran (ref_type, ref_id, metode, jumlah, status, waktu_verifikasi) VALUES ('TAGIHAN', ?, 'CASH', ?, 'DITERIMA', NOW())");
        $stmt->bind_param('ii', $id_tagihan, $jumlah);
        
        if($stmt->execute()) {
            $this->koneksi->query("UPDATE tagihan SET status='LUNAS' WHERE id_tagihan=$id_tagihan");
            return true;
        }
        return false;
    }

    // ==========================================
    // 7. APPROVE BOOKING & KONTRAK
    // ==========================================
    function setujui_booking_dan_buat_kontrak($id_booking) {
        $q_booking = $this->koneksi->query("SELECT b.*, k.harga FROM booking b JOIN kamar k ON b.id_kamar=k.id_kamar WHERE id_booking = $id_booking");
        $booking = $q_booking->fetch_assoc();
        
        if (!$booking) return false;

        $id_pengguna = $booking['id_pengguna'];
        $id_kamar    = $booking['id_kamar'];
        $tgl_mulai   = $booking['checkin_rencana'];
        $durasi      = $booking['durasi_bulan_rencana'];
        $harga_kamar = $booking['harga'];
        $tgl_selesai = date('Y-m-d', strtotime("+$durasi months", strtotime($tgl_mulai)));

        $q_penghuni = $this->koneksi->query("SELECT id_penghuni FROM penghuni WHERE id_pengguna = $id_pengguna");
        if ($q_penghuni->num_rows > 0) {
            $d_penghuni = $q_penghuni->fetch_object();
            $id_penghuni = $d_penghuni->id_penghuni;
        } else {
            $this->koneksi->query("INSERT INTO penghuni (id_pengguna) VALUES ($id_pengguna)");
            $id_penghuni = $this->koneksi->insert_id;
        }

        $this->koneksi->query("UPDATE kontrak SET status='SELESAI' WHERE id_penghuni = $id_penghuni AND status='AKTIF'");

        $stmt_kontrak = $this->koneksi->prepare("INSERT INTO kontrak (id_penghuni, id_kamar, tanggal_mulai, tanggal_selesai, durasi_bulan, status) VALUES (?, ?, ?, ?, ?, 'AKTIF')");
        $stmt_kontrak->bind_param('iisss', $id_penghuni, $id_kamar, $tgl_mulai, $tgl_selesai, $durasi);
        
        if ($stmt_kontrak->execute()) {
            $id_kontrak_baru = $this->koneksi->insert_id;

            $this->koneksi->query("UPDATE kamar SET status_kamar='TERISI' WHERE id_kamar = $id_kamar");
            $this->koneksi->query("UPDATE booking SET status='SELESAI' WHERE id_booking = $id_booking");

            // DP Logic
            $q_bayar = $this->koneksi->query("SELECT jumlah FROM pembayaran WHERE ref_type='BOOKING' AND ref_id=$id_booking AND status='DITERIMA'");
            $data_bayar = $q_bayar->fetch_assoc();
            $dp_amount = $data_bayar['jumlah'] ?? 0;

            $sisa_tagihan = $harga_kamar - $dp_amount;
            if($sisa_tagihan < 0) $sisa_tagihan = 0;

            $bulan_pertama = date('Y-m', strtotime($tgl_mulai));
            $jatuh_tempo_pertama = date('Y-m-d', strtotime($tgl_mulai . ' + 5 days')); 

            $stmt_tagihan = $this->koneksi->prepare("INSERT INTO tagihan (id_kontrak, bulan_tagih, nominal, jatuh_tempo, status) VALUES (?, ?, ?, ?, 'BELUM')");
            $stmt_tagihan->bind_param('isis', $id_kontrak_baru, $bulan_pertama, $sisa_tagihan, $jatuh_tempo_pertama);
            $stmt_tagihan->execute();

            return true;
        }
        return false;
    }

    // ==========================================
    // 8. LOG AKTIVITAS
    // ==========================================
    function catat_log($id_user, $aksi, $keterangan) {
        $stmt = $this->koneksi->prepare("INSERT INTO log_aktivitas (id_pengguna, aksi, keterangan, waktu) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param('iss', $id_user, $aksi, $keterangan);
        $stmt->execute();
    }

    function ambil_log_aktivitas() {
        $sql = "SELECT l.*, u.nama 
                FROM log_aktivitas l 
                LEFT JOIN pengguna u ON l.id_pengguna = u.id_pengguna 
                ORDER BY l.waktu DESC";
        $res = $this->koneksi->query($sql);
        $hasil = [];
        while($row = $res->fetch_assoc()) { $hasil[] = $row; }
        return $hasil;
    }

    // ==========================================
    // 9. SYSTEM AUTOMATION
    // ==========================================
    function auto_batal_booking() {
        $batas_waktu = date('Y-m-d H:i:s', strtotime('-24 hours'));
        $query_cek = "SELECT id_booking, id_pengguna FROM booking 
                      WHERE status='PENDING' AND tanggal_booking < '$batas_waktu'";
        $res = $this->koneksi->query($query_cek);

        $jumlah_batal = 0;
        if ($res && $res->num_rows > 0) {
            while ($row = $res->fetch_assoc()) {
                $id_b = $row['id_booking'];
                $id_u = $row['id_pengguna'];

                $this->koneksi->query("UPDATE booking SET status='BATAL' WHERE id_booking=$id_b");
                $this->koneksi->query("UPDATE pembayaran SET status='DITOLAK' WHERE ref_type='BOOKING' AND ref_id=$id_b");
                $this->catat_log($id_u, 'SYSTEM AUTO CANCEL', "Booking ID $id_b dibatalkan otomatis oleh sistem (Timeout 24 Jam).");
                
                $jumlah_batal++;
            }
        }
        return $jumlah_batal;
    }

    function auto_cek_kontrak_habis() {
        $today = date('Y-m-d');
        $sql = "SELECT id_kontrak, id_kamar, id_penghuni FROM kontrak 
                WHERE status='AKTIF' AND tanggal_selesai < '$today'";
        $res = $this->koneksi->query($sql);
        
        $count = 0;
        if($res && $res->num_rows > 0) {
            while($row = $res->fetch_assoc()) {
                $id_k = $row['id_kontrak'];
                $id_r = $row['id_kamar'];
                $id_p = $row['id_penghuni']; 
                
                $this->koneksi->query("UPDATE kontrak SET status='SELESAI' WHERE id_kontrak=$id_k");
                $this->koneksi->query("UPDATE kamar SET status_kamar='TERSEDIA' WHERE id_kamar=$id_r");
                
                $q_u = $this->koneksi->query("SELECT id_pengguna FROM penghuni WHERE id_penghuni=$id_p");
                $uid = $q_u->fetch_object()->id_pengguna ?? 0;
                
                $this->catat_log($uid, 'SYSTEM AUTO END', "Masa sewa habis. Kontrak #$id_k selesai & Kamar tersedia kembali.");
                $count++;
            }
        }
        return $count;
    }

    // ==========================================
    // 10. PENGATURAN SISTEM
    // ==========================================
    function ambil_pengaturan() {
        $res = $this->koneksi->query("SELECT * FROM pengaturan WHERE id=1");
        // Fallback jika tabel pengaturan kosong/belum dibuat
        if (!$res || $res->num_rows == 0) {
            return [
                'nama_kos' => 'SIKOS Default',
                'alamat' => 'Alamat Default',
                'no_hp' => '081234567890',
                'email' => 'admin@sikos.com',
                'rek_bank' => 'BCA 123456',
                'pemilik' => 'Admin'
            ];
        }
        return $res->fetch_assoc();
    }

    function update_pengaturan($nama, $alamat, $hp, $email, $rek, $pemilik) {
        $stmt = $this->koneksi->prepare("UPDATE pengaturan SET nama_kos=?, alamat=?, no_hp=?, email=?, rek_bank=?, pemilik=? WHERE id=1");
        $stmt->bind_param('ssssss', $nama, $alamat, $hp, $email, $rek, $pemilik);
        return $stmt->execute();
    }

} // <--- Tutup Class Database

// Inisialisasi Objek Global (Biar file lain tinggal pakai $mysqli)
$db = new Database();
$mysqli = $db->koneksi;

// Fungsi Helper Global (Bukan method class, tapi helper biasa)
function pesan_error($url, $pesan) {
    echo "<script>alert('$pesan'); window.location.href='$url';</script>";
    exit;
}
?>