<?php
class Database {
    // Properties
    var $host = "localhost";
    var $user = "root";
    var $pass = "";
    var $db   = "sikos";
    public $koneksi;

    // Constructor
    function __construct() {
        $this->koneksi = new mysqli($this->host, $this->user, $this->pass, $this->db);
        if ($this->koneksi->connect_errno) {
            die("Database Error: " . $this->koneksi->connect_error);
        }
    }
    // ==========================================
    // 1. AUTHENTICATION (Login & Register)
    // ==========================================
    function login($email, $password) {
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
        // Cek Email Duplikat
        $cek = $this->koneksi->prepare("SELECT id_pengguna FROM pengguna WHERE email=?");
        $cek->bind_param('s', $email);
        $cek->execute();
        if ($cek->get_result()->num_rows > 0) return "DUPLIKAT";

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->koneksi->prepare("INSERT INTO pengguna (nama, email, no_hp, password_hash, peran, status) VALUES (?, ?, ?, ?, 'PENGHUNI', 1)");
        $stmt->bind_param('ssss', $nama, $email, $hp, $hash);
        return $stmt->execute();
    }

    // ==========================================
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
        $stmt->bind_param('siidiss', $kode, $tipe, $lantai, $luas, $harga, $status, $foto, $catatan);
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
        // Hapus galeri dulu (relasi)
        $this->koneksi->query("DELETE FROM kamar_foto WHERE id_kamar=$id");
        // Hapus kamar
        return $this->koneksi->query("DELETE FROM kamar WHERE id_kamar=$id");
    }

    function tampil_tipe_kamar() {
        $data = mysqli_query($this->koneksi, "SELECT id_tipe, nama_tipe FROM tipe_kamar");
        $hasil = [];
        while ($row = mysqli_fetch_assoc($data)) {
            $hasil[] = $row;
        }
        return $hasil;
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
    // ... (kode sebelumnya di dalam class Database) ...

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
        // 1. Ambil harga sewa dari kontrak/kamar
        $stmt = $this->koneksi->prepare("SELECT ka.harga FROM kontrak k JOIN kamar ka ON k.id_kamar=ka.id_kamar WHERE k.id_kontrak=?");
        $stmt->bind_param('i', $id_kontrak);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        
        if (!$res) return false; // Kontrak tidak valid
        
        $nominal = $res['harga'];
        // Jatuh tempo set tanggal 10 bulan tersebut
        $jatuh_tempo = $bulan . "-10"; 

        // 2. Cek apakah tagihan bulan ini sudah ada?
        $cek = $this->koneksi->prepare("SELECT id_tagihan FROM tagihan WHERE id_kontrak=? AND bulan_tagih=?");
        $cek->bind_param('is', $id_kontrak, $bulan);
        $cek->execute();
        if ($cek->get_result()->num_rows > 0) {
            return "DUPLIKAT"; // Sudah ada tagihan bulan ini
        }

        // 3. Insert Tagihan
        $ins = $this->koneksi->prepare("INSERT INTO tagihan (id_kontrak, bulan_tagih, nominal, jatuh_tempo, status) VALUES (?, ?, ?, ?, 'BELUM')");
        $ins->bind_param('isis', $id_kontrak, $bulan, $nominal, $jatuh_tempo);
        return $ins->execute();
    }
    
// ... kode method lain ...

    // ==========================================
    // 6. MANAJEMEN PEMBAYARAN
    // ==========================================
    function tambah_pembayaran_tagihan($id_tagihan, $jumlah, $bukti, $metode = 'TRANSFER') {
        $stmt = $this->koneksi->prepare("INSERT INTO pembayaran (ref_type, ref_id, metode, jumlah, bukti_path, status) VALUES ('TAGIHAN', ?, ?, ?, ?, 'PENDING')");
        $stmt->bind_param('isis', $id_tagihan, $metode, $jumlah, $bukti);
        return $stmt->execute();
    }
    // ... method lain sebelumnya ...

    // Cek status pembayaran terakhir untuk tagihan tertentu
    function cek_status_pembayaran_terakhir($id_tagihan) {
        // Ambil status pembayaran terakhir berdasarkan ID Tagihan
        $stmt = $this->koneksi->prepare("SELECT status FROM pembayaran WHERE ref_type='TAGIHAN' AND ref_id=? ORDER BY id_pembayaran DESC LIMIT 1");
        $stmt->bind_param('i', $id_tagihan);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        
        // Jika ada data, kembalikan statusnya (misal: PENDING, DITERIMA, DITOLAK)
        // Jika tidak ada, kembalikan null
        return $res ? $res['status'] : null;
    }
// ==========================================
    // FUNGSI PERBAIKAN: HAPUS LOGIKA FOTO_KTP
    // ==========================================
    function setujui_booking_dan_buat_kontrak($id_booking) {
        // 1. Ambil Data Booking
        $q_booking = $this->koneksi->query("SELECT * FROM booking WHERE id_booking = $id_booking");
        $booking = $q_booking->fetch_assoc();
        
        if (!$booking) return false;

        $id_pengguna = $booking['id_pengguna'];
        $id_kamar    = $booking['id_kamar'];
        $tgl_mulai   = $booking['checkin_rencana'];
        $durasi      = $booking['durasi_bulan_rencana'];
        
        // Hitung Tanggal Selesai
        $tgl_selesai = date('Y-m-d', strtotime("+$durasi months", strtotime($tgl_mulai)));

        // 2. Cek apakah user ini sudah ada di tabel PENGHUNI?
        $q_penghuni = $this->koneksi->query("SELECT id_penghuni FROM penghuni WHERE id_pengguna = $id_pengguna");
        
        if ($q_penghuni->num_rows > 0) {
            // Jika sudah ada, ambil ID-nya
            $d_penghuni = $q_penghuni->fetch_object();
            $id_penghuni = $d_penghuni->id_penghuni;
        } else {
            // PERBAIKAN DISINI: Hapus 'foto_ktp' dari query INSERT
            // Kita hanya memasukkan id_pengguna saja.
            $this->koneksi->query("INSERT INTO penghuni (id_pengguna) VALUES ($id_pengguna)");
            $id_penghuni = $this->koneksi->insert_id;
        }

        // 3. Matikan kontrak lama user ini (jika ada) agar tidak double
        $this->koneksi->query("UPDATE kontrak SET status='SELESAI' WHERE id_penghuni = $id_penghuni AND status='AKTIF'");

        // 4. BUAT KONTRAK BARU
        $stmt_kontrak = $this->koneksi->prepare("INSERT INTO kontrak (id_penghuni, id_kamar, tanggal_mulai, tanggal_selesai, durasi_bulan, status) VALUES (?, ?, ?, ?, ?, 'AKTIF')");
        $stmt_kontrak->bind_param('iisss', $id_penghuni, $id_kamar, $tgl_mulai, $tgl_selesai, $durasi);
        $sukses_kontrak = $stmt_kontrak->execute();

        // 5. Update Status Kamar jadi TERISI
        $this->koneksi->query("UPDATE kamar SET status_kamar='TERISI' WHERE id_kamar = $id_kamar");

        // 6. Update Status Booking jadi SELESAI
        $this->koneksi->query("UPDATE booking SET status='SELESAI' WHERE id_booking = $id_booking");

        return $sukses_kontrak;
    }
}

$db = new Database();
$mysqli = $db->koneksi;
?>