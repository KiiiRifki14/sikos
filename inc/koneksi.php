<?php
// Panggil file konfigurasi
require_once __DIR__ . '/config.php';

// ===================================================================================
// BAGIAN 1: ABSTRACTION (INTERFACE) [MATERI: ABSTRACTION]
// ===================================================================================
interface SikosDatabaseInterface
{
    public function get_statistik_kamar();
    public function get_total_booking();
}

// ===================================================================================
// BAGIAN 2: PARENT CLASS (INHERITANCE) [MATERI: INHERITANCE]
// ===================================================================================
class KoneksiDasar
{
    // [MATERI: ENCAPSULATION]
    // 'protected': Bisa diakses class ini & class warisan (Database)
    protected $koneksi;

    // 'private': Hanya bisa diakses di class ini saja (KoneksiDasar)
    // [MATERI: ACCESS MODIFIER PRIVATE]
    private $appVersion = "1.0.0";
    private $debugMode = false;

    // [MATERI: POLYMORPHISM - CONSTRUCTOR]
    function __construct()
    {
        // [MATERI: EXCEPTION HANDLING]
        try {
            // @ menahan error native PHP, biar ditangkap catch
            $this->koneksi = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

            if ($this->koneksi->connect_error) {
                throw new Exception("Koneksi Database Gagal: " . $this->koneksi->connect_error);
            }
        } catch (Exception $e) {
            // Jika debugMode aktif (via setter), tampilkan error asli
            if ($this->debugMode) {
                die("Error: " . $e->getMessage());
            } else {
                // Tampilan user friendly
                die('<div style="text-align: center; padding: 50px;">
                        <h1>Layanan Sedang Pemeliharaan</h1>
                        <p>Maaf, saat ini sistem sedang tidak dapat diakses.</p>
                        <p style="color: #666; font-size: 12px;">(Error Code: DB_CONN_ERR)</p>
                    </div>');
            }
        }
    }

    // [MATERI: MAGIC METHODS - DESTRUCTOR]
    // Otomatis dipanggil saat object dihancurkan (misal di akhir script)
    function __destruct()
    {
        if ($this->koneksi) {
            $this->koneksi->close();
        }
    }

    // [MATERI: SETTER & GETTER STANDAR]
    // Digunakan untuk memanipulasi property private $debugMode
    public function setDebugMode($status)
    {
        $this->debugMode = $status;
    }

    public function getAppVersion()
    {
        return $this->appVersion;
    }

    // [MATERI: MAGIC METHODS - GETTER]
    // Agar property 'koneksi' (protected) bisa dibaca dari luar seolah-olah public
    public function __get($name)
    {
        if ($name == 'koneksi') {
            return $this->koneksi;
        }
        return null;
    }
}

// ===================================================================================
// BAGIAN 3: CHILD CLASS (UTAMA) [MATERI: INHERITANCE & POLYMORPHISM]
// ===================================================================================
class Database extends KoneksiDasar implements SikosDatabaseInterface
{
    // [MATERI: OVERRIDING CONSTRUCTOR]
    function __construct()
    {
        // Panggil constructor Parent dulu (super)
        parent::__construct();

        // Tambahan logika khusus Child
        if ($this->koneksi) {
            $this->koneksi->set_charset("utf8mb4");
        }
    }

    // ==========================================
    // CONTOH PEMAKAIAN SWITCH CASE [MATERI: PERCABANGAN]
    // ==========================================
    public function get_status_label($status)
    {
        $label = "";
        // [MATERI: SWITCH CASE]
        switch ($status) {
            case 'PENDING':
                $label = '<span class="badge badge-warning">Menunggu</span>';
                break;
            case 'DITERIMA':
            case 'LUNAS':
            case 'AKTIF':
                $label = '<span class="badge badge-success">Sukses</span>';
                break;
            case 'DITOLAK':
            case 'BATAL':
                $label = '<span class="badge badge-danger">Gagal</span>';
                break;
            default:
                $label = '<span class="badge badge-secondary">Unknown</span>';
                break;
        }
        return $label;
    }

    // ==========================================
    // 1. AUTHENTICATION (Login)
    // ==========================================
    function login($email, $password)
    {
        $stmt = $this->koneksi->prepare("SELECT id_pengguna, password_hash, peran, status FROM pengguna WHERE email=?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();

        if ($res && $res['status'] == 1 && password_verify($password, $res['password_hash'])) {
            return $res;
        }
        return false;
    }

    function register($nama, $email, $hp, $password)
    {
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
    function tampil_kamar()
    {
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

    function tampil_tipe_kamar()
    {
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

    function ambil_kamar_by_id($id)
    {
        $stmt = $this->koneksi->prepare("SELECT * FROM kamar WHERE id_kamar=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    function tambah_kamar($kode, $tipe, $lantai, $luas, $harga, $foto, $catatan)
    {
        $cek = $this->koneksi->prepare("SELECT 1 FROM kamar WHERE kode_kamar=?");
        $cek->bind_param('s', $kode);
        $cek->execute();
        if ($cek->get_result()->fetch_assoc()) return false;

        $status = 'TERSEDIA';
        $stmt = $this->koneksi->prepare("INSERT INTO kamar (kode_kamar, id_tipe, lantai, luas_m2, harga, status_kamar, foto_cover, catatan) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('siidisss', $kode, $tipe, $lantai, $luas, $harga, $status, $foto, $catatan);
        return $stmt->execute();
    }

    function edit_kamar($id, $kode, $tipe, $lantai, $luas, $harga, $foto, $catatan)
    {
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

    function hapus_kamar($id)
    {
        $cek_booking = $this->koneksi->query("SELECT 1 FROM booking WHERE id_kamar=$id AND status='PENDING'");
        if ($cek_booking->num_rows > 0) return "GAGAL: Kamar sedang dipesan (Booking Pending).";

        $cek_kontrak = $this->koneksi->query("SELECT 1 FROM kontrak WHERE id_kamar=$id AND status='AKTIF'");
        if ($cek_kontrak->num_rows > 0) return "GAGAL: Kamar sedang terisi (Kontrak Aktif).";

        // Hapus Cover
        $q_cover = $this->koneksi->query("SELECT foto_cover FROM kamar WHERE id_kamar=$id");
        if ($row = $q_cover->fetch_assoc()) {
            $path_cover = __DIR__ . "/../assets/uploads/kamar/" . $row['foto_cover'];
            if (!empty($row['foto_cover']) && file_exists($path_cover)) unlink($path_cover);
        }

        // Hapus Galeri
        $q_galeri = $this->koneksi->query("SELECT file_nama FROM kamar_foto WHERE id_kamar=$id");
        while ($row = $q_galeri->fetch_assoc()) {
            $path_galeri = __DIR__ . "/../assets/uploads/kamar/" . $row['file_nama'];
            if (!empty($row['file_nama']) && file_exists($path_galeri)) unlink($path_galeri);
        }

        $this->koneksi->query("DELETE FROM kamar_fasilitas WHERE id_kamar=$id");
        $this->koneksi->query("DELETE FROM kamar_foto WHERE id_kamar=$id");

        if ($this->koneksi->query("DELETE FROM kamar WHERE id_kamar=$id")) return "SUKSES";
        return "GAGAL: Database Error.";
    }

    // ==========================================
    // 3. MANAJEMEN BOOKING
    // ==========================================
    function tampil_booking_admin()
    {
        $sql = "SELECT b.*, g.nama, g.no_hp, k.kode_kamar FROM booking b 
                JOIN pengguna g ON b.id_pengguna=g.id_pengguna
                JOIN kamar k ON b.id_kamar=k.id_kamar
                ORDER BY b.tanggal_booking DESC";
        $res = $this->koneksi->query($sql);
        $hasil = [];
        while ($row = $res->fetch_assoc()) {
            $hasil[] = $row;
        }
        return $hasil;
    }

    function tambah_booking($id_user, $id_kamar, $checkin, $durasi, $ktp)
    {
        $stmt = $this->koneksi->prepare("INSERT INTO booking (id_pengguna, id_kamar, checkin_rencana, durasi_bulan_rencana, status, ktp_path_opt) VALUES (?, ?, ?, ?, 'PENDING', ?)");
        $stmt->bind_param('iisis', $id_user, $id_kamar, $checkin, $durasi, $ktp);
        return $stmt->execute();
    }

    function verifikasi_booking($id_booking, $status)
    {
        $stmt = $this->koneksi->prepare("UPDATE booking SET status=? WHERE id_booking=?");
        $stmt->bind_param('si', $status, $id_booking);
        return $stmt->execute();
    }

    // ==========================================
    // 4. MANAJEMEN PENGHUNI
    // ==========================================
    function tampil_penghuni()
    {
        $sql = "SELECT p.id_penghuni, u.nama, k.kode_kamar, ko.tanggal_mulai, ko.tanggal_selesai, ko.status
                FROM penghuni p
                JOIN pengguna u ON p.id_pengguna = u.id_pengguna
                LEFT JOIN kontrak ko ON p.id_penghuni = ko.id_penghuni AND ko.status = 'AKTIF'
                LEFT JOIN kamar k ON ko.id_kamar = k.id_kamar
                ORDER BY u.nama ASC";
        $res = $this->koneksi->query($sql);
        $hasil = [];
        while ($row = $res->fetch_assoc()) {
            $hasil[] = $row;
        }
        return $hasil;
    }

    // ==========================================
    // 5. MANAJEMEN KEUANGAN (TAGIHAN)
    // ==========================================
    function get_list_kontrak_aktif()
    {
        $sql = "SELECT k.id_kontrak, ka.kode_kamar, p.nama 
                FROM kontrak k 
                JOIN kamar ka ON k.id_kamar=ka.id_kamar
                JOIN penghuni ph ON k.id_penghuni=ph.id_penghuni
                JOIN pengguna p ON ph.id_pengguna=p.id_pengguna
                WHERE k.status='AKTIF'";
        return $this->koneksi->query($sql)->fetch_all(MYSQLI_ASSOC);
    }

    function generate_tagihan($id_kontrak, $bulan)
    {
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
        if ($cek->get_result()->num_rows > 0) return "DUPLIKAT";

        $ins = $this->koneksi->prepare("INSERT INTO tagihan (id_kontrak, bulan_tagih, nominal, jatuh_tempo, status) VALUES (?, ?, ?, ?, 'BELUM')");
        $ins->bind_param('isis', $id_kontrak, $bulan, $nominal, $jatuh_tempo);
        return $ins->execute();
    }

    function generate_tagihan_masal($bulan_tagih)
    {
        $sql = "SELECT k.id_kontrak, km.harga 
                FROM kontrak k 
                JOIN kamar km ON k.id_kamar = km.id_kamar 
                WHERE k.status = 'AKTIF'";

        $res = $this->koneksi->query($sql);
        $jumlah_sukses = 0;
        $jatuh_tempo = $bulan_tagih . "-10";

        $cek_stmt = $this->koneksi->prepare("SELECT id_tagihan FROM tagihan WHERE id_kontrak=? AND bulan_tagih=?");
        $ins_stmt = $this->koneksi->prepare("INSERT INTO tagihan (id_kontrak, bulan_tagih, nominal, jatuh_tempo, status) VALUES (?, ?, ?, ?, 'BELUM')");

        while ($row = $res->fetch_assoc()) {
            $id_kontrak = $row['id_kontrak'];
            $harga      = $row['harga'];

            $cek_stmt->bind_param('is', $id_kontrak, $bulan_tagih);
            $cek_stmt->execute();
            $cek_stmt->store_result();

            if ($cek_stmt->num_rows == 0) {
                $ins_stmt->bind_param('isis', $id_kontrak, $bulan_tagih, $harga, $jatuh_tempo);
                if ($ins_stmt->execute()) $jumlah_sukses++;
            }
        }
        return $jumlah_sukses;
    }

    // ==========================================
    // 6. MANAJEMEN PEMBAYARAN
    // ==========================================
    function tambah_pembayaran_tagihan($id_tagihan, $jumlah, $bukti, $metode = 'TRANSFER')
    {
        $cek = $this->koneksi->prepare("SELECT id_pembayaran FROM pembayaran WHERE ref_type='TAGIHAN' AND ref_id=? AND status='PENDING'");
        $cek->bind_param('i', $id_tagihan);
        $cek->execute();
        if ($cek->get_result()->num_rows > 0) return "DUPLIKAT";

        $stmt = $this->koneksi->prepare("INSERT INTO pembayaran (ref_type, ref_id, metode, jumlah, bukti_path, status) VALUES ('TAGIHAN', ?, ?, ?, ?, 'PENDING')");
        $stmt->bind_param('isis', $id_tagihan, $metode, $jumlah, $bukti);
        return $stmt->execute();
    }

    function cek_status_pembayaran_terakhir($id_tagihan)
    {
        $stmt = $this->koneksi->prepare("SELECT status FROM pembayaran WHERE ref_type='TAGIHAN' AND ref_id=? ORDER BY id_pembayaran DESC LIMIT 1");
        $stmt->bind_param('i', $id_tagihan);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        return $res ? $res['status'] : null;
    }

    function bayar_tagihan_cash($id_tagihan, $catatan_admin = 'Pembayaran Tunai ke Admin')
    {
        $data_tagihan = $this->koneksi->query("SELECT nominal FROM tagihan WHERE id_tagihan=$id_tagihan")->fetch_assoc();
        if (!$data_tagihan) return false;
        $jumlah = $data_tagihan['nominal'];

        $stmt = $this->koneksi->prepare("INSERT INTO pembayaran (ref_type, ref_id, metode, jumlah, status, waktu_verifikasi) VALUES ('TAGIHAN', ?, 'CASH', ?, 'DITERIMA', NOW())");
        $stmt->bind_param('ii', $id_tagihan, $jumlah);
        if ($stmt->execute()) {
            $this->koneksi->query("UPDATE tagihan SET status='LUNAS' WHERE id_tagihan=$id_tagihan");
            return true;
        }
        return false;
    }

    // ==========================================
    // 7. APPROVE BOOKING & KONTRAK
    // ==========================================
    function setujui_booking_dan_buat_kontrak($id_booking)
    {
        $this->koneksi->begin_transaction();
        try {
            // Ambil Info Booking
            $booking = $this->koneksi->query("SELECT b.*, k.harga FROM booking b JOIN kamar k ON b.id_kamar=k.id_kamar WHERE id_booking = $id_booking")->fetch_assoc();
            if (!$booking) throw new Exception("Booking tidak valid");

            $id_pengguna = $booking['id_pengguna'];
            $id_kamar    = $booking['id_kamar'];
            $tgl_mulai   = $booking['checkin_rencana'];
            $durasi      = $booking['durasi_bulan_rencana'];
            $tgl_selesai = date('Y-m-d', strtotime("+$durasi months", strtotime($tgl_mulai)));
            $harga_kamar = $booking['harga'];

            // Cek Penghuni
            $q_penghuni = $this->koneksi->query("SELECT id_penghuni FROM penghuni WHERE id_pengguna = $id_pengguna");
            if ($q_penghuni->num_rows > 0) {
                $id_penghuni = $q_penghuni->fetch_object()->id_penghuni;
            } else {
                $this->koneksi->query("INSERT INTO penghuni (id_pengguna) VALUES ($id_pengguna)");
                $id_penghuni = $this->koneksi->insert_id;
            }

            // Matikan Kontrak Lama
            $this->koneksi->query("UPDATE kontrak SET status='SELESAI' WHERE id_penghuni = $id_penghuni AND status='AKTIF'");

            // Buat Kontrak
            $stmt_kontrak = $this->koneksi->prepare("INSERT INTO kontrak (id_penghuni, id_kamar, tanggal_mulai, tanggal_selesai, durasi_bulan, status) VALUES (?, ?, ?, ?, ?, 'AKTIF')");
            $stmt_kontrak->bind_param('iisss', $id_penghuni, $id_kamar, $tgl_mulai, $tgl_selesai, $durasi);
            $stmt_kontrak->execute();
            $id_kontrak_baru = $this->koneksi->insert_id;

            // Update Status
            $this->koneksi->query("UPDATE kamar SET status_kamar='TERISI' WHERE id_kamar = $id_kamar");
            $this->koneksi->query("UPDATE booking SET status='SELESAI' WHERE id_booking = $id_booking");

            // Tagihan Awal (Hitung sisa jika ada DP)
            $dp_amount = $this->koneksi->query("SELECT sum(jumlah) FROM pembayaran WHERE ref_type='BOOKING' AND ref_id=$id_booking AND status='DITERIMA'")->fetch_row()[0] ?? 0;
            $sisa_tagihan = $harga_kamar - $dp_amount;

            $bulan_pertama = date('Y-m', strtotime($tgl_mulai));
            $jatuh_tempo = date('Y-m-d', strtotime($tgl_mulai . ' + 5 days'));

            if ($sisa_tagihan > 0) {
                $stmt_tagihan = $this->koneksi->prepare("INSERT INTO tagihan (id_kontrak, bulan_tagih, nominal, jatuh_tempo, status) VALUES (?, ?, ?, ?, 'BELUM')");
                $stmt_tagihan->bind_param('isis', $id_kontrak_baru, $bulan_pertama, $sisa_tagihan, $jatuh_tempo);
                $stmt_tagihan->execute();
            }

            $this->koneksi->commit();
            return true;
        } catch (Exception $e) {
            $this->koneksi->rollback();
            return false;
        }
    }

    // ==========================================
    // 8. LOG AKTIVITAS
    // ==========================================
    function catat_log($id_user, $aksi, $keterangan)
    {
        $stmt = $this->koneksi->prepare("INSERT INTO log_aktivitas (id_pengguna, aksi, keterangan, waktu) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param('iss', $id_user, $aksi, $keterangan);
        $stmt->execute();
    }

    function ambil_log_aktivitas()
    {
        $sql = "SELECT l.*, u.nama FROM log_aktivitas l LEFT JOIN pengguna u ON l.id_pengguna = u.id_pengguna ORDER BY l.waktu DESC";
        $res = $this->koneksi->query($sql);
        $hasil = [];
        while ($row = $res->fetch_assoc()) $hasil[] = $row;
        return $hasil;
    }

    // ==========================================
    // 9. SYSTEM AUTOMATION
    // ==========================================
    function auto_batal_booking()
    {
        $batas_waktu = date('Y-m-d H:i:s', strtotime('-24 hours'));
        $res = $this->koneksi->query("SELECT id_booking, id_pengguna FROM booking WHERE status='PENDING' AND tanggal_booking < '$batas_waktu'");
        $jumlah_batal = 0;
        if ($res && $res->num_rows > 0) {
            while ($row = $res->fetch_assoc()) {
                $id_b = $row['id_booking'];
                $id_u = $row['id_pengguna'];
                $this->koneksi->query("UPDATE booking SET status='BATAL' WHERE id_booking=$id_b");
                $this->koneksi->query("UPDATE pembayaran SET status='DITOLAK' WHERE ref_type='BOOKING' AND ref_id=$id_b");
                $this->catat_log($id_u, 'SYSTEM AUTO CANCEL', "Booking #$id_b expired.");
                $jumlah_batal++;
            }
        }
        return $jumlah_batal;
    }

    function perpanjang_kontrak($id_penghuni, $durasi_bulan)
    {
        $this->koneksi->begin_transaction();
        try {
            $kontrak = $this->koneksi->query("SELECT * FROM kontrak WHERE id_penghuni=$id_penghuni AND status='AKTIF'")->fetch_assoc();
            if (!$kontrak) throw new Exception("No active contract");

            $id_kontrak = $kontrak['id_kontrak'];
            $tgl_baru = date('Y-m-d', strtotime("+$durasi_bulan months", strtotime($kontrak['tanggal_selesai'])));

            $stmt = $this->koneksi->prepare("UPDATE kontrak SET tanggal_selesai=?, durasi_bulan = durasi_bulan + ? WHERE id_kontrak=?");
            $stmt->bind_param('sii', $tgl_baru, $durasi_bulan, $id_kontrak);
            $stmt->execute();

            // Generate tagihan baru steps here... simplified for brevity, assume logic same
            // (Re-using logic from original file)
            $start = strtotime("+1 month", strtotime($kontrak['tanggal_selesai']));
            $end   = strtotime($tgl_baru);
            $harga = $this->koneksi->query("SELECT harga FROM kamar WHERE id_kamar={$kontrak['id_kamar']}")->fetch_object()->harga;

            $current = $start;
            while ($current <= $end) {
                $bulan_tagih = date('Y-m', $current);
                $jatuh_tempo = date('Y-m-d', strtotime(date('Y-m-10', $current)));

                $cek = $this->koneksi->query("SELECT 1 FROM tagihan WHERE id_kontrak=$id_kontrak AND bulan_tagih='$bulan_tagih'");
                if ($cek->num_rows == 0) {
                    $ins = $this->koneksi->prepare("INSERT INTO tagihan (id_kontrak, bulan_tagih, nominal, jatuh_tempo, status) VALUES (?, ?, ?, ?, 'BELUM')");
                    $ins->bind_param('isis', $id_kontrak, $bulan_tagih, $harga, $jatuh_tempo);
                    $ins->execute();
                }
                $current = strtotime("+1 month", $current);
            }

            $this->koneksi->commit();
            return true;
        } catch (Exception $e) {
            $this->koneksi->rollback();
            return false;
        }
    }

    function stop_kontrak($id_penghuni)
    {
        $this->koneksi->begin_transaction();
        try {
            $data = $this->koneksi->query("SELECT id_kontrak, id_kamar FROM kontrak WHERE id_penghuni=$id_penghuni AND status='AKTIF'")->fetch_object();
            if ($data) {
                $this->koneksi->query("UPDATE kontrak SET status='SELESAI' WHERE id_kontrak={$data->id_kontrak}");
                $this->koneksi->query("UPDATE kamar SET status_kamar='TERSEDIA' WHERE id_kamar={$data->id_kamar}");
            }
            $this->koneksi->commit();
            return true;
        } catch (Exception $e) {
            $this->koneksi->rollback();
            return false;
        }
    }

    function auto_cek_kontrak_habis()
    {
        $today = date('Y-m-d');
        $res = $this->koneksi->query("SELECT id_kontrak, id_kamar, id_penghuni FROM kontrak WHERE status='AKTIF' AND tanggal_selesai < '$today'");
        $count = 0;
        if ($res && $res->num_rows > 0) {
            while ($row = $res->fetch_assoc()) {
                $this->koneksi->query("UPDATE kontrak SET status='SELESAI' WHERE id_kontrak={$row['id_kontrak']}");
                $this->koneksi->query("UPDATE kamar SET status_kamar='TERSEDIA' WHERE id_kamar={$row['id_kamar']}");
                $count++;
            }
        }
        return $count;
    }

    // ==========================================
    // 10. PENGATURAN & LAINNYA
    // ==========================================
    function ambil_pengaturan()
    {
        $res = $this->koneksi->query("SELECT * FROM pengaturan WHERE id=1");
        if (!$res || $res->num_rows == 0) {
            return [
                'nama_kos' => 'SIKOS Default',
                'alamat' => 'Alamat Default',
                'no_hp' => '081234567890',
                'email' => 'admin@sikos.com',
                'rek_bank' => 'BCA 123456',
                'pemilik' => 'Admin',
                'no_wa' => '62881011201664',
                'link_fb' => '#',
                'link_ig' => '#',
                'deskripsi_footer' => 'Footer Default',
                'foto_logo' => ''
            ];
        }
        return $res->fetch_assoc();
    }

    function update_pengaturan($nama, $alamat, $hp, $email, $rek, $pemilik)
    {
        $stmt = $this->koneksi->prepare("UPDATE pengaturan SET nama_kos=?, alamat=?, no_hp=?, email=?, rek_bank=?, pemilik=? WHERE id=1");
        $stmt->bind_param('ssssss', $nama, $alamat, $hp, $email, $rek, $pemilik);
        return $stmt->execute();
    }

    function update_pengaturan_landing($wa, $fb, $ig, $footer, $logo = null)
    {
        if ($logo) {
            $stmt = $this->koneksi->prepare("UPDATE pengaturan SET no_wa=?, link_fb=?, link_ig=?, deskripsi_footer=?, foto_logo=? WHERE id=1");
            $stmt->bind_param('sssss', $wa, $fb, $ig, $footer, $logo);
        } else {
            $stmt = $this->koneksi->prepare("UPDATE pengaturan SET no_wa=?, link_fb=?, link_ig=?, deskripsi_footer=? WHERE id=1");
            $stmt->bind_param('ssss', $wa, $fb, $ig, $footer);
        }
        return $stmt->execute();
    }

    function get_fasilitas_umum()
    {
        return $this->koneksi->query("SELECT * FROM fasilitas_umum ORDER BY id ASC")->fetch_all(MYSQLI_ASSOC);
    }
    function tambah_fasilitas($judul, $deskripsi, $icon)
    {
        $stmt = $this->koneksi->prepare("INSERT INTO fasilitas_umum (judul, deskripsi, icon) VALUES (?, ?, ?)");
        $stmt->bind_param('sss', $judul, $deskripsi, $icon);
        return $stmt->execute();
    }
    function hapus_fasilitas($id)
    {
        return $this->koneksi->query("DELETE FROM fasilitas_umum WHERE id=$id");
    }

    function update_bukti_pembayaran($id_pembayaran, $path)
    {
        $stmt = $this->koneksi->prepare("UPDATE pembayaran SET bukti_path = ?, status = 'PENDING', waktu_verifikasi = NOW() WHERE id_pembayaran = ?");
        $stmt->bind_param('si', $path, $id_pembayaran);
        return $stmt->execute();
    }

    // Helper Retrieve Data (Simplified)
    function get_tagihan_by_kontrak($id)
    {
        return $this->koneksi->query("SELECT * FROM tagihan WHERE id_kontrak=$id ORDER BY bulan_tagih DESC")->fetch_all(MYSQLI_ASSOC);
    }
    function get_tagihan_pending_count($id)
    {
        return $this->koneksi->query("SELECT COUNT(*) FROM tagihan WHERE id_kontrak=$id AND status='BELUM'")->fetch_row()[0];
    }
    function get_all_kamar_paginated($start = 0, $limit = 10)
    {
        return $this->koneksi->query("SELECT k.*, t.nama_tipe FROM kamar k JOIN tipe_kamar t ON k.id_tipe=t.id_tipe ORDER BY k.kode_kamar ASC LIMIT $start, $limit");
    }
    function get_total_kamar()
    {
        return $this->koneksi->query("SELECT COUNT(*) FROM kamar")->fetch_row()[0];
    }

    // Analytics
    function get_statistik_kamar()
    {
        $total = $this->get_total_kamar();
        $terisi = $this->koneksi->query("SELECT COUNT(*) FROM kamar WHERE status_kamar='TERISI'")->fetch_row()[0];
        return ['total' => $total, 'terisi' => $terisi, 'rate' => ($total > 0) ? round(($terisi / $total) * 100) : 0];
    }

    function get_total_booking()
    {
        return $this->koneksi->query("SELECT COUNT(DISTINCT b.id_booking) FROM booking b LEFT JOIN pembayaran p ON b.id_booking = p.ref_id AND p.ref_type='BOOKING' WHERE b.status != 'PENDING' OR (p.bukti_path IS NOT NULL)")->fetch_row()[0];
    }

    function get_all_booking_paginated($start, $limit)
    {
        // ... (Keep implementation but shortened for brevity if possible, keeping safety)
        $sql = "SELECT b.*, g.nama, g.no_hp, k.kode_kamar, p.bukti_path as bukti_bayar FROM booking b JOIN pengguna g ON b.id_pengguna=g.id_pengguna JOIN kamar k ON b.id_kamar=k.id_kamar LEFT JOIN pembayaran p ON b.id_booking = p.ref_id AND p.ref_type='BOOKING' WHERE b.status != 'PENDING' OR (p.bukti_path IS NOT NULL) ORDER BY b.tanggal_booking DESC LIMIT $start, $limit";
        return $this->koneksi->query($sql);
    }

    function get_all_fasilitas_master()
    {
        return $this->koneksi->query("SELECT * FROM fasilitas_master ORDER BY nama_fasilitas ASC");
    }

    function get_total_penghuni_filtered($cari = "")
    {
        /* simplified */
        $sql = "SELECT COUNT(*) FROM penghuni p JOIN pengguna u ON p.id_pengguna = u.id_pengguna LEFT JOIN kontrak ko ON p.id_penghuni = ko.id_penghuni AND ko.status = 'AKTIF' LEFT JOIN kamar k ON ko.id_kamar = k.id_kamar";
        if ($cari) $sql .= " WHERE u.nama LIKE '%$cari%' OR k.kode_kamar LIKE '%$cari%'";
        return $this->koneksi->query($sql)->fetch_row()[0];
    }
    function get_all_penghuni_paginated($cari, $start, $limit)
    {
        $sql = "SELECT p.id_penghuni, u.nama, u.no_hp, k.kode_kamar, ko.tanggal_mulai, ko.tanggal_selesai, ko.status FROM penghuni p JOIN pengguna u ON p.id_pengguna = u.id_pengguna LEFT JOIN kontrak ko ON p.id_penghuni = ko.id_penghuni AND ko.status = 'AKTIF' LEFT JOIN kamar k ON ko.id_kamar = k.id_kamar";
        if ($cari) $sql .= " WHERE u.nama LIKE '%$cari%' OR k.kode_kamar LIKE '%$cari%'";
        $sql .= " ORDER BY u.nama ASC LIMIT $start, $limit";
        return $this->koneksi->query($sql);
    }

    function get_total_pembayaran_masuk($bulan = null)
    {
        if ($bulan) return $this->koneksi->query("SELECT SUM(jumlah) FROM pembayaran WHERE status='DITERIMA' AND DATE_FORMAT(waktu_verifikasi, '%Y-%m') = '$bulan'")->fetch_row()[0] ?? 0;
        return $this->koneksi->query("SELECT SUM(jumlah) FROM pembayaran WHERE status='DITERIMA'")->fetch_row()[0] ?? 0;
    }
    function get_total_pengeluaran($bulan = null)
    {
        if ($bulan) return $this->koneksi->query("SELECT SUM(biaya) FROM pengeluaran WHERE DATE_FORMAT(tanggal, '%Y-%m') = '$bulan'")->fetch_row()[0] ?? 0;
        return $this->koneksi->query("SELECT SUM(biaya) FROM pengeluaran")->fetch_row()[0] ?? 0;
    }

    // Charting Helpers
    function get_statistik_keuangan($bulan, $tahun)
    {
        $masuk = $this->koneksi->query("SELECT SUM(jumlah) FROM pembayaran WHERE status='DITERIMA' AND MONTH(waktu_verifikasi) = $bulan AND YEAR(waktu_verifikasi) = $tahun")->fetch_row()[0] ?? 0;
        $keluar = $this->koneksi->query("SELECT SUM(biaya) FROM pengeluaran WHERE MONTH(tanggal) = $bulan AND YEAR(tanggal) = $tahun")->fetch_row()[0] ?? 0;
        return ['omset' => $masuk, 'keluar' => $keluar, 'profit' => $masuk - $keluar];
    }

    function get_chart_pendapatan($tahun)
    {
        $data = [];
        // [OPTIMIZATION] Loop 12x is allowed for small dataset
        for ($i = 1; $i <= 12; $i++) {
            $data[] = $this->koneksi->query("SELECT SUM(jumlah) FROM pembayaran WHERE status='DITERIMA' AND MONTH(waktu_verifikasi) = $i AND YEAR(waktu_verifikasi) = $tahun")->fetch_row()[0] ?? 0;
        }
        return $data;
    }

    function get_chart_pendapatan_harian($bulan, $tahun)
    {
        $data = [];
        $days = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);
        for ($d = 1; $d <= $days; $d++) {
            $data[] = $this->koneksi->query("SELECT SUM(jumlah) FROM pembayaran WHERE status='DITERIMA' AND DAY(waktu_verifikasi) = $d AND MONTH(waktu_verifikasi) = $bulan AND YEAR(waktu_verifikasi) = $tahun")->fetch_row()[0] ?? 0;
        }
        return $data;
    }

    function get_pending_counts()
    {
        return [
            'booking' => $this->koneksi->query("SELECT COUNT(*) FROM booking WHERE status='PENDING'")->fetch_row()[0],
            'tagihan' => $this->koneksi->query("SELECT COUNT(*) FROM pembayaran WHERE status='PENDING'")->fetch_row()[0]
        ];
    }

    function get_booking_terbaru($limit = 5)
    {
        return $this->koneksi->query("SELECT b.*, u.nama, u.no_hp, k.kode_kamar, t.nama_tipe FROM booking b JOIN pengguna u ON b.id_pengguna=u.id_pengguna JOIN kamar k ON b.id_kamar=k.id_kamar JOIN tipe_kamar t ON k.id_tipe=t.id_tipe WHERE b.status='PENDING' ORDER BY b.tanggal_booking DESC LIMIT $limit");
    }

    // Tenant Helpers
    function get_user_by_id($id)
    {
        return $this->koneksi->query("SELECT * FROM pengguna WHERE id_pengguna=$id")->fetch_assoc();
    }
    function get_id_penghuni_by_user($id)
    {
        return $this->koneksi->query("SELECT id_penghuni FROM penghuni WHERE id_pengguna=$id")->fetch_object()->id_penghuni ?? 0;
    }
    function get_kamar_penghuni_detail($id)
    {
        return $this->koneksi->query("SELECT k.*, t.nama_tipe, ko.tanggal_mulai, ko.tanggal_selesai, ko.id_kontrak, ko.status as status_kontrak, k.harga FROM kontrak ko JOIN kamar k ON ko.id_kamar = k.id_kamar JOIN tipe_kamar t ON k.id_tipe = t.id_tipe WHERE ko.id_penghuni = $id AND ko.status = 'AKTIF'")->fetch_assoc();
    }
    function get_fasilitas_kamar($id)
    {
        return $this->koneksi->query("SELECT f.nama_fasilitas, f.icon FROM kamar_fasilitas kf JOIN fasilitas_master f ON kf.id_fasilitas=f.id_fasilitas WHERE kf.id_kamar=$id");
    }
    function get_keluhan_by_penghuni($id)
    {
        return $this->koneksi->query("SELECT * FROM keluhan WHERE id_penghuni=$id ORDER BY dibuat_at DESC");
    }
    function insert_keluhan($id, $j, $d, $p, $f)
    {
        $stmt = $this->koneksi->prepare("INSERT INTO keluhan (id_penghuni, judul, deskripsi, prioritas, status, foto_path) VALUES (?, ?, ?, ?, 'BARU', ?)");
        $stmt->bind_param('issss', $id, $j, $d, $p, $f);
        return $stmt->execute();
    }
    function get_profil_penghuni($id)
    {
        return $this->koneksi->query("SELECT u.*, p.alamat, p.pekerjaan, p.emergency_cp, p.foto_profil FROM pengguna u LEFT JOIN penghuni p ON u.id_pengguna = p.id_pengguna WHERE u.id_pengguna = $id")->fetch_assoc();
    }
    function get_pengumuman_terbaru($limit = 2)
    {
        return $this->koneksi->query("SELECT * FROM pengumuman WHERE is_aktif=1 AND aktif_selesai >= CURDATE() ORDER BY aktif_mulai DESC LIMIT $limit");
    }

    function can_user_book($id)
    {
        if ($this->koneksi->query("SELECT COUNT(*) FROM booking WHERE id_pengguna=$id AND status='PENDING'")->fetch_row()[0] > 0) return false;
        $id_p = $this->get_id_penghuni_by_user($id);
        if ($id_p && $this->koneksi->query("SELECT COUNT(*) FROM kontrak WHERE id_penghuni=$id_p AND status='AKTIF'")->fetch_row()[0] > 0) return false;
        return true;
    }
}

// Inisialisasi Objek Global
$db = new Database();
$mysqli = $db->koneksi; // Akses via Magic Method __get()

function pesan_error($url, $pesan)
{
    $sep = (strpos($url, '?') === false) ? '?' : '&';
    $type = (strpos($pesan, '✅') !== false || stripos($pesan, 'sukses') !== false) ? 'success' : 'error';
    header("Location: $url" . $sep . "msg=custom&text=" . urlencode($pesan) . "&type=" . $type);
    exit;
}
