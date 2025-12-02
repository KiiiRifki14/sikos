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
    // FUNGSI SAKTI: APPROVE BOOKING + AUTO TAGIHAN DP
    // ==========================================
    function setujui_booking_dan_buat_kontrak($id_booking) {
        // 1. Ambil Data Booking Lengkap
        $q_booking = $this->koneksi->query("SELECT b.*, k.harga FROM booking b JOIN kamar k ON b.id_kamar=k.id_kamar WHERE id_booking = $id_booking");
        $booking = $q_booking->fetch_assoc();
        
        if (!$booking) return false;

        $id_pengguna = $booking['id_pengguna'];
        $id_kamar    = $booking['id_kamar'];
        $tgl_mulai   = $booking['checkin_rencana'];
        $durasi      = $booking['durasi_bulan_rencana'];
        $harga_kamar = $booking['harga'];
        
        // Hitung Tanggal Selesai
        $tgl_selesai = date('Y-m-d', strtotime("+$durasi months", strtotime($tgl_mulai)));

        // 2. Cek User di Tabel Penghuni
        $q_penghuni = $this->koneksi->query("SELECT id_penghuni FROM penghuni WHERE id_pengguna = $id_pengguna");
        if ($q_penghuni->num_rows > 0) {
            $d_penghuni = $q_penghuni->fetch_object();
            $id_penghuni = $d_penghuni->id_penghuni;
        } else {
            $this->koneksi->query("INSERT INTO penghuni (id_pengguna) VALUES ($id_pengguna)");
            $id_penghuni = $this->koneksi->insert_id;
        }

        // 3. Matikan kontrak lama (jika ada)
        $this->koneksi->query("UPDATE kontrak SET status='SELESAI' WHERE id_penghuni = $id_penghuni AND status='AKTIF'");

        // 4. BUAT KONTRAK BARU
        $stmt_kontrak = $this->koneksi->prepare("INSERT INTO kontrak (id_penghuni, id_kamar, tanggal_mulai, tanggal_selesai, durasi_bulan, status) VALUES (?, ?, ?, ?, ?, 'AKTIF')");
        $stmt_kontrak->bind_param('iisss', $id_penghuni, $id_kamar, $tgl_mulai, $tgl_selesai, $durasi);
        
        if ($stmt_kontrak->execute()) {
            $id_kontrak_baru = $this->koneksi->insert_id;

            // 5. Update Status Kamar & Booking
            $this->koneksi->query("UPDATE kamar SET status_kamar='TERISI' WHERE id_kamar = $id_kamar");
            $this->koneksi->query("UPDATE booking SET status='SELESAI' WHERE id_booking = $id_booking");

            // --- LOGIKA BARU: DP MENGURANGI TAGIHAN AWAL ---
            
            // A. Cari berapa uang yang sudah dibayar di Booking ini (DP)
            $q_bayar = $this->koneksi->query("SELECT jumlah FROM pembayaran WHERE ref_type='BOOKING' AND ref_id=$id_booking AND status='DITERIMA'");
            $data_bayar = $q_bayar->fetch_assoc();
            $dp_amount = $data_bayar['jumlah'] ?? 0;

            // B. Hitung Sisa Tagihan Bulan Pertama
            $sisa_tagihan = $harga_kamar - $dp_amount;
            
            // Jika sisa < 0 (misal DP kegedean), anggap 0
            if($sisa_tagihan < 0) $sisa_tagihan = 0;

            // C. Buat Tagihan Bulan Pertama (Sesuai Bulan Check-in)
            $bulan_pertama = date('Y-m', strtotime($tgl_mulai));
            $jatuh_tempo_pertama = date('Y-m-d', strtotime($tgl_mulai . ' + 5 days')); // Jatuh tempo 5 hari setelah checkin

            $stmt_tagihan = $this->koneksi->prepare("INSERT INTO tagihan (id_kontrak, bulan_tagih, nominal, jatuh_tempo, status) VALUES (?, ?, ?, ?, 'BELUM')");
            $stmt_tagihan->bind_param('isis', $id_kontrak_baru, $bulan_pertama, $sisa_tagihan, $jatuh_tempo_pertama);
            $stmt_tagihan->execute();

            return true;
        }
        return false;
    }
    // Tambahkan method ini di dalam class Database (file: inc/koneksi.php)

    // --- FITUR BARU: GENERATE TAGIHAN MASAL ---
    function generate_tagihan_masal($bulan_tagih) {
        // 1. Ambil semua kontrak AKTIF beserta harga kamar saat ini
        // Kita ambil harga dari tabel 'kamar' sesuai permintaanmu
        $sql = "SELECT k.id_kontrak, km.harga 
                FROM kontrak k 
                JOIN kamar km ON k.id_kamar = km.id_kamar 
                WHERE k.status = 'AKTIF'";
        
        $res = $this->koneksi->query($sql);
        $jumlah_sukses = 0;
        
        // Jatuh tempo default tanggal 10 bulan tersebut
        $jatuh_tempo = $bulan_tagih . "-10"; 

        // Prepared statement agar cepat dan aman dalam loop
        $cek_stmt = $this->koneksi->prepare("SELECT id_tagihan FROM tagihan WHERE id_kontrak=? AND bulan_tagih=?");
        $ins_stmt = $this->koneksi->prepare("INSERT INTO tagihan (id_kontrak, bulan_tagih, nominal, jatuh_tempo, status) VALUES (?, ?, ?, ?, 'BELUM')");

        while($row = $res->fetch_assoc()) {
            $id_kontrak = $row['id_kontrak'];
            $harga      = $row['harga']; // Harga diambil dari kamar saat ini

            // 2. Cek apakah tagihan bulan ini sudah ada?
            $cek_stmt->bind_param('is', $id_kontrak, $bulan_tagih);
            $cek_stmt->execute();
            $cek_stmt->store_result();

            if($cek_stmt->num_rows == 0) {
                // 3. Jika belum ada, buat tagihan
                $ins_stmt->bind_param('isis', $id_kontrak, $bulan_tagih, $harga, $jatuh_tempo);
                if($ins_stmt->execute()) {
                    $jumlah_sukses++;
                }
            }
        }
        return $jumlah_sukses;
    }

    // --- FITUR BARU: BAYAR CASH (Manual oleh Admin) ---
    function bayar_tagihan_cash($id_tagihan, $catatan_admin = 'Pembayaran Tunai ke Admin') {
        // 1. Ambil nominal tagihan
        $q_tagihan = $this->koneksi->query("SELECT nominal FROM tagihan WHERE id_tagihan=$id_tagihan");
        $data_tagihan = $q_tagihan->fetch_assoc();
        
        if(!$data_tagihan) return false;
        $jumlah = $data_tagihan['nominal'];

        // 2. Insert ke tabel pembayaran (Metode CASH, Status DITERIMA langsung)
        // Kita kosongkan bukti_path karena cash
        $stmt = $this->koneksi->prepare("INSERT INTO pembayaran (ref_type, ref_id, metode, jumlah, status, waktu_verifikasi) VALUES ('TAGIHAN', ?, 'CASH', ?, 'DITERIMA', NOW())");
        $stmt->bind_param('ii', $id_tagihan, $jumlah);
        
        if($stmt->execute()) {
            // 3. Update status tagihan jadi LUNAS
            $this->koneksi->query("UPDATE tagihan SET status='LUNAS' WHERE id_tagihan=$id_tagihan");
            return true;
        }
        return false;
    }
    function catat_log($id_user, $aksi, $keterangan) {
        $stmt = $this->koneksi->prepare("INSERT INTO log_aktivitas (id_pengguna, aksi, keterangan) VALUES (?, ?, ?)");
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
}

$db = new Database();
$mysqli = $db->koneksi;

// FUNGSI TAMBAHAN UNTUK ERROR HANDLING
function pesan_error($url, $pesan) {
    echo "<script>alert('$pesan'); window.location.href='$url';</script>";
    exit;
}
?>