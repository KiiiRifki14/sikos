<?php
// Panggil file konfigurasi (opsional, jika ingin tetap pakai konstanta)
require_once __DIR__ . '/config.php';

class Database
{
    // PENERAPAN ENKAPSULASI (Pertemuan 7)
    // Property dibuat private agar aman dan hanya bisa diakses class ini    
    // Property koneksi dibuat public agar bisa dipakai di file lain (mysqli query)
    public $koneksi;

    // PENERAPAN CONSTRUCTOR (Pertemuan 6)
    function __construct()
    {
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
    function login($email, $password)
    {
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

    // --- FUNGSI TIPE KAMAR YANG WAJIB ADA ---
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
    // ----------------------------------------

    function ambil_kamar_by_id($id)
    {
        $stmt = $this->koneksi->prepare("SELECT * FROM kamar WHERE id_kamar=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    function tambah_kamar($kode, $tipe, $lantai, $luas, $harga, $foto, $catatan)
    {
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

    function edit_kamar($id, $kode, $tipe, $lantai, $luas, $harga, $foto, $catatan)
    {
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

    function hapus_kamar($id)
    {
        // 1. Cek apakah ada Booking yang masih PENDING
        $cek_booking = $this->koneksi->query("SELECT 1 FROM booking WHERE id_kamar=$id AND status='PENDING'");
        if ($cek_booking->num_rows > 0) {
            return "GAGAL: Tidak bisa dihapus! Kamar ini sedang dalam proses Booking (Pending).";
        }

        // 2. Cek apakah ada Kontrak yang masih AKTIF
        $cek_kontrak = $this->koneksi->query("SELECT 1 FROM kontrak WHERE id_kamar=$id AND status='AKTIF'");
        if ($cek_kontrak->num_rows > 0) {
            return "GAGAL: Tidak bisa dihapus! Kamar ini sedang terisi (Kontrak Aktif).";
        }

        // [FIX] Hapus Data Fisik Gambar (Mencegah Sampah Server)
        // Hapus Cover
        $q_cover = $this->koneksi->query("SELECT foto_cover FROM kamar WHERE id_kamar=$id");
        if ($row = $q_cover->fetch_assoc()) {
            // Gunakan path absolut/relative yang sesuai dari lokasi file ini (inc/) ke assets/
            // __DIR__ . '/../assets...'
            $path_cover = __DIR__ . "/../assets/uploads/kamar/" . $row['foto_cover'];
            if (!empty($row['foto_cover']) && file_exists($path_cover)) {
                unlink($path_cover);
            }
        }

        // Hapus Galeri
        $q_galeri = $this->koneksi->query("SELECT file_nama FROM kamar_foto WHERE id_kamar=$id");
        while ($row = $q_galeri->fetch_assoc()) {
            $path_galeri = __DIR__ . "/../assets/uploads/kamar/" . $row['file_nama'];
            if (!empty($row['file_nama']) && file_exists($path_galeri)) {
                unlink($path_galeri);
            }
        }

        // 3. Hapus data pendukung di Database
        $this->koneksi->query("DELETE FROM kamar_fasilitas WHERE id_kamar=$id");
        $this->koneksi->query("DELETE FROM kamar_foto WHERE id_kamar=$id");

        // 4. Hapus data kamar
        if ($this->koneksi->query("DELETE FROM kamar WHERE id_kamar=$id")) {
            return "SUKSES";
        }

        return "GAGAL: Terjadi kesalahan database saat menghapus.";
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
        if ($cek->get_result()->num_rows > 0) {
            return "DUPLIKAT";
        }

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
                if ($ins_stmt->execute()) {
                    $jumlah_sukses++;
                }
            }
        }
        return $jumlah_sukses;
    }

    // ==========================================
    // 6. MANAJEMEN PEMBAYARAN
    // ==========================================
    function tambah_pembayaran_tagihan($id_tagihan, $jumlah, $bukti, $metode = 'TRANSFER')
    {
        // [SECURITY] Cek apakah ada pembayaran PENDING sebelumnya
        $cek = $this->koneksi->prepare("SELECT id_pembayaran FROM pembayaran WHERE ref_type='TAGIHAN' AND ref_id=? AND status='PENDING'");
        $cek->bind_param('i', $id_tagihan);
        $cek->execute();
        if ($cek->get_result()->num_rows > 0) return "DUPLIKAT";

        $stmt = $this->koneksi->prepare("INSERT INTO pembayaran (ref_type, ref_id, metode, jumlah, bukti_path, status) VALUES ('TAGIHAN', ?, ?, ?, ?, 'PENDING')");
        $stmt->bind_param('isis', $id_tagihan, $metode, $jumlah, $bukti);
        return $stmt->execute() ? true : false;
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
        $q_tagihan = $this->koneksi->query("SELECT nominal FROM tagihan WHERE id_tagihan=$id_tagihan");
        $data_tagihan = $q_tagihan->fetch_assoc();

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
        $this->koneksi->begin_transaction(); // Mulai Transaksi agar data konsisten

        try {
            // 1. Ambil Data Booking
            $q_booking = $this->koneksi->query("SELECT b.*, k.harga FROM booking b JOIN kamar k ON b.id_kamar=k.id_kamar WHERE id_booking = $id_booking");
            $booking = $q_booking->fetch_assoc();

            if (!$booking) throw new Exception("Data booking tidak ditemukan");

            $id_pengguna = $booking['id_pengguna'];
            $id_kamar    = $booking['id_kamar'];
            $tgl_mulai   = $booking['checkin_rencana'];
            $durasi      = $booking['durasi_bulan_rencana'];
            $harga_kamar = $booking['harga'];
            $tgl_selesai = date('Y-m-d', strtotime("+$durasi months", strtotime($tgl_mulai)));

            // 2. Cek / Buat Data Penghuni
            $q_penghuni = $this->koneksi->query("SELECT id_penghuni FROM penghuni WHERE id_pengguna = $id_pengguna");
            if ($q_penghuni->num_rows > 0) {
                $d_penghuni = $q_penghuni->fetch_object();
                $id_penghuni = $d_penghuni->id_penghuni;
            } else {
                $this->koneksi->query("INSERT INTO penghuni (id_pengguna) VALUES ($id_pengguna)");
                $id_penghuni = $this->koneksi->insert_id;
            }

            // 3. Matikan kontrak lama jika ada (agar tidak double active)
            $this->koneksi->query("UPDATE kontrak SET status='SELESAI' WHERE id_penghuni = $id_penghuni AND status='AKTIF'");

            // 4. Buat Kontrak Baru
            $stmt_kontrak = $this->koneksi->prepare("INSERT INTO kontrak (id_penghuni, id_kamar, tanggal_mulai, tanggal_selesai, durasi_bulan, status) VALUES (?, ?, ?, ?, ?, 'AKTIF')");
            $stmt_kontrak->bind_param('iisss', $id_penghuni, $id_kamar, $tgl_mulai, $tgl_selesai, $durasi);
            if (!$stmt_kontrak->execute()) throw new Exception("Gagal buat kontrak");

            $id_kontrak_baru = $this->koneksi->insert_id;

            // 5. Update Status Kamar & Booking
            $this->koneksi->query("UPDATE kamar SET status_kamar='TERISI' WHERE id_kamar = $id_kamar");
            $this->koneksi->query("UPDATE booking SET status='SELESAI' WHERE id_booking = $id_booking");

            // 6. Buat Tagihan Pertama (Sisa Pembayaran)
            $q_bayar = $this->koneksi->query("SELECT sum(jumlah) as total_bayar FROM pembayaran WHERE ref_type='BOOKING' AND ref_id=$id_booking AND status='DITERIMA'");
            $data_bayar = $q_bayar->fetch_assoc();
            $dp_amount = $data_bayar['total_bayar'] ?? 0;

            $sisa_tagihan = $harga_kamar - $dp_amount;
            // Jika DP full/lebih, tagihan bulan pertama 0 atau tetap dibuat lunas?
            // Kita buat tagihan tetap ada, nanti statusnya disesuaikan

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
            error_log("Error Approval: " . $e->getMessage());
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
        $sql = "SELECT l.*, u.nama 
                FROM log_aktivitas l 
                LEFT JOIN pengguna u ON l.id_pengguna = u.id_pengguna 
                ORDER BY l.waktu DESC";
        $res = $this->koneksi->query($sql);
        $hasil = [];
        while ($row = $res->fetch_assoc()) {
            $hasil[] = $row;
        }
        return $hasil;
    }

    // ==========================================
    // 9. SYSTEM AUTOMATION
    // ==========================================
    function auto_batal_booking()
    {
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

    // --- FITUR BARU: PERPANJANG & STOP SEWA ---
    function perpanjang_kontrak($id_penghuni, $durasi_bulan)
    {
        $this->koneksi->begin_transaction();
        try {
            // 1. Ambil Kontrak Aktif
            $q = $this->koneksi->query("SELECT * FROM kontrak WHERE id_penghuni=$id_penghuni AND status='AKTIF'");
            $kontrak = $q->fetch_assoc();
            if (!$kontrak) throw new Exception("Tidak ada kontrak aktif.");

            $id_kontrak = $kontrak['id_kontrak'];
            $tgl_lama   = $kontrak['tanggal_selesai'];

            // 2. Hitung Tanggal Baru
            $tgl_baru = date('Y-m-d', strtotime("+$durasi_bulan months", strtotime($tgl_lama)));

            // 3. Update Kontrak
            $stmt = $this->koneksi->prepare("UPDATE kontrak SET tanggal_selesai=?, durasi_bulan = durasi_bulan + ? WHERE id_kontrak=?");
            $stmt->bind_param('sii', $tgl_baru, $durasi_bulan, $id_kontrak);
            $stmt->execute();

            // 4. Generate Tagihan Baru untuk bulan-bulan tambahan
            // Loop dari bulan setelah tanggal lama sampai tanggal baru
            $start = strtotime("+1 month", strtotime($tgl_lama)); // Mulai bulan depan dari exp lama
            $end   = strtotime($tgl_baru);

            // Ambil harga kamar saat ini (untuk tagihan baru)
            $q_kamar = $this->koneksi->query("SELECT harga FROM kamar WHERE id_kamar={$kontrak['id_kamar']}");
            $harga   = $q_kamar->fetch_object()->harga;

            $current = $start;
            while ($current <= $end) {
                $bulan_tagih = date('Y-m', $current);
                $jatuh_tempo = date('Y-m-d', strtotime(date('Y-m-10', $current))); // Tgl 10 bulan itu

                // Cek duplikat (jika sebelumnya sudah digenerate)
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
            // 1. Ambil Data
            $q = $this->koneksi->query("SELECT id_kontrak, id_kamar FROM kontrak WHERE id_penghuni=$id_penghuni AND status='AKTIF'");
            $data = $q->fetch_object();

            if ($data) {
                // 2. Matikan Kontrak
                $this->koneksi->query("UPDATE kontrak SET status='SELESAI' WHERE id_kontrak={$data->id_kontrak}");

                // 3. Kosongkan Kamar
                $this->koneksi->query("UPDATE kamar SET status_kamar='TERSEDIA' WHERE id_kamar={$data->id_kamar}");

                // 4. (Opsional) Hapus Tagihan MASA DEPAN yang belum dibayar? 
                // Biarkan saja, atau hapus yg > hari ini. Kita biarkan history tagihan tetap ada.
            }

            $this->koneksi->commit();
            return true;
        } catch (Exception $e) {
            $this->koneksi->rollback();
            return false;
        }
    }
    // ------------------------------------------

    function auto_cek_kontrak_habis()
    {
        $today = date('Y-m-d');
        $sql = "SELECT id_kontrak, id_kamar, id_penghuni FROM kontrak 
                WHERE status='AKTIF' AND tanggal_selesai < '$today'";
        $res = $this->koneksi->query($sql);

        $count = 0;
        if ($res && $res->num_rows > 0) {
            while ($row = $res->fetch_assoc()) {
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
    function ambil_pengaturan()
    {
        $res = $this->koneksi->query("SELECT * FROM pengaturan WHERE id=1");
        // Fallback jika tabel pengaturan kosong/belum dibuat
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
                'deskripsi_footer' => 'Platform penyewaan kost modern...',
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

    // [NEW] Update Pengaturan Landing Page
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

    // [NEW] Manajemen Fasilitas Umum
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
    // --- TAMBAHAN UNTUK FIX ERROR PEMBAYARAN ---
    function update_bukti_pembayaran($id_pembayaran, $path)
    {
        // Menggunakan Prepared Statement yang Benar
        $stmt = $this->koneksi->prepare("UPDATE pembayaran SET bukti_path = ?, status = 'PENDING', waktu_verifikasi = NOW() WHERE id_pembayaran = ?");
        $stmt->bind_param('si', $path, $id_pembayaran);
        return $stmt->execute();
    }

    // [REFACTOR] New Methods for Standard Compliance
    function get_tagihan_by_kontrak($id_kontrak)
    {
        $stmt = $this->koneksi->prepare("SELECT * FROM tagihan WHERE id_kontrak=? ORDER BY bulan_tagih DESC");
        $stmt->bind_param('i', $id_kontrak);
        $stmt->execute();
        $res = $stmt->get_result();

        $hasil = [];
        while ($row = $res->fetch_assoc()) {
            $hasil[] = $row;
        }
        return $hasil;
    }

    function get_tagihan_pending_count($id_kontrak)
    {
        $stmt = $this->koneksi->prepare("SELECT COUNT(*) FROM tagihan WHERE id_kontrak=? AND status='BELUM'");
        $stmt->bind_param('i', $id_kontrak);
        $stmt->execute();
        return $stmt->get_result()->fetch_row()[0];
    }

    function get_all_kamar_paginated($start = 0, $limit = 10)
    {
        $stmt = $this->koneksi->prepare("SELECT k.*, t.nama_tipe FROM kamar k JOIN tipe_kamar t ON k.id_tipe=t.id_tipe ORDER BY k.kode_kamar ASC LIMIT ?, ?");
        $stmt->bind_param('ii', $start, $limit);
        $stmt->execute();
        return $stmt->get_result();
    }

    function get_total_kamar()
    {
        return $this->koneksi->query("SELECT COUNT(*) FROM kamar")->fetch_row()[0];
    }


    // ==========================================
    // 11. DASHBOARD ANALYTICS (MVC REFACTOR)
    // ==========================================
    function get_statistik_kamar()
    {
        // [REFACTOR] Tidak perlu params, jadi query langsung aman
        $total = $this->koneksi->query("SELECT COUNT(*) FROM kamar")->fetch_row()[0];
        $terisi = $this->koneksi->query("SELECT COUNT(*) FROM kamar WHERE status_kamar='TERISI'")->fetch_row()[0];
        return [
            'total' => $total,
            'terisi' => $terisi,
            'rate' => ($total > 0) ? round(($terisi / $total) * 100) : 0
        ];
    }

    function get_total_booking()
    {
        return $this->koneksi->query("SELECT COUNT(DISTINCT b.id_booking) 
                                      FROM booking b 
                                      LEFT JOIN pembayaran p ON b.id_booking = p.ref_id AND p.ref_type='BOOKING'
                                      WHERE b.status != 'PENDING' OR (p.bukti_path IS NOT NULL AND p.bukti_path != '')")->fetch_row()[0];
    }

    function get_all_booking_paginated($start, $limit)
    {
        $sql = "SELECT b.*, g.nama, g.no_hp, k.kode_kamar, p.bukti_path as bukti_bayar
                FROM booking b 
                JOIN pengguna g ON b.id_pengguna=g.id_pengguna 
                JOIN kamar k ON b.id_kamar=k.id_kamar 
                LEFT JOIN pembayaran p ON b.id_booking = p.ref_id AND p.ref_type='BOOKING'
                WHERE b.status != 'PENDING' OR (p.bukti_path IS NOT NULL AND p.bukti_path != '')
                ORDER BY b.tanggal_booking DESC 
                LIMIT ?, ?";
        $stmt = $this->koneksi->prepare($sql);
        $stmt->bind_param('ii', $start, $limit);
        $stmt->execute();
        return $stmt->get_result();
    }

    // --- FASILITAS ---
    function get_all_fasilitas_master()
    {
        return $this->koneksi->query("SELECT * FROM fasilitas_master ORDER BY nama_fasilitas ASC");
    }

    // --- PENGHUNI ---
    function get_total_penghuni_filtered($cari = "")
    {
        $sql = "SELECT COUNT(*) FROM penghuni p 
                JOIN pengguna u ON p.id_pengguna = u.id_pengguna 
                LEFT JOIN kontrak ko ON p.id_penghuni = ko.id_penghuni AND ko.status = 'AKTIF'
                LEFT JOIN kamar k ON ko.id_kamar = k.id_kamar";

        if (!empty($cari)) {
            $sql .= " WHERE u.nama LIKE ? OR k.kode_kamar LIKE ?";
            $stmt = $this->koneksi->prepare($sql);
            $param = "%$cari%";
            $stmt->bind_param('ss', $param, $param);
            $stmt->execute();
            return $stmt->get_result()->fetch_row()[0];
        }
        return $this->koneksi->query($sql)->fetch_row()[0];
    }

    function get_all_penghuni_paginated($cari, $start, $limit)
    {
        $sql = "SELECT p.id_penghuni, u.nama, u.no_hp, k.kode_kamar, ko.tanggal_mulai, ko.tanggal_selesai, ko.status
                FROM penghuni p
                JOIN pengguna u ON p.id_pengguna = u.id_pengguna
                LEFT JOIN kontrak ko ON p.id_penghuni = ko.id_penghuni AND ko.status = 'AKTIF'
                LEFT JOIN kamar k ON ko.id_kamar = k.id_kamar";

        if (!empty($cari)) {
            $sql .= " WHERE u.nama LIKE ? OR k.kode_kamar LIKE ?";
            $sql .= " ORDER BY u.nama ASC LIMIT ?, ?";
            $stmt = $this->koneksi->prepare($sql);
            $param = "%$cari%";
            $stmt->bind_param('ssii', $param, $param, $start, $limit);
        } else {
            $sql .= " ORDER BY u.nama ASC LIMIT ?, ?";
            $stmt = $this->koneksi->prepare($sql);
            $stmt->bind_param('ii', $start, $limit);
        }

        $stmt->execute();
        return $stmt->get_result();
    }

    // --- KEUANGAN ---
    function get_total_pembayaran_masuk($bulan = null)
    {
        if ($bulan) {
            $stmt = $this->koneksi->prepare("SELECT SUM(jumlah) FROM pembayaran WHERE status='DITERIMA' AND DATE_FORMAT(waktu_verifikasi, '%Y-%m') = ?");
            $stmt->bind_param('s', $bulan);
            $stmt->execute();
            return $stmt->get_result()->fetch_row()[0] ?? 0;
        }
        return $this->koneksi->query("SELECT SUM(jumlah) FROM pembayaran WHERE status='DITERIMA'")->fetch_row()[0] ?? 0;
    }

    function get_total_pengeluaran($bulan = null)
    {
        if ($bulan) {
            $stmt = $this->koneksi->prepare("SELECT SUM(biaya) FROM pengeluaran WHERE DATE_FORMAT(tanggal, '%Y-%m') = ?");
            $stmt->bind_param('s', $bulan);
            $stmt->execute();
            return $stmt->get_result()->fetch_row()[0] ?? 0;
        }
        return $this->koneksi->query("SELECT SUM(biaya) FROM pengeluaran")->fetch_row()[0] ?? 0;
    }

    // Tagihan Methods
    function count_tagihan_by_month($month)
    {
        $stmt = $this->koneksi->prepare("SELECT COUNT(*) FROM tagihan WHERE bulan_tagih = ?");
        $stmt->bind_param('s', $month);
        $stmt->execute();
        return $stmt->get_result()->fetch_row()[0];
    }

    function get_tagihan_by_month_paginated($month, $start, $limit)
    {
        $sql = "SELECT t.*, u.nama, k.kode_kamar FROM tagihan t 
                JOIN kontrak ko ON t.id_kontrak = ko.id_kontrak
                JOIN penghuni p ON ko.id_penghuni = p.id_penghuni
                JOIN pengguna u ON p.id_pengguna = u.id_pengguna
                JOIN kamar k ON ko.id_kamar = k.id_kamar
                WHERE t.bulan_tagih = ? ORDER BY u.nama ASC 
                LIMIT ?, ?";
        $stmt = $this->koneksi->prepare($sql);
        $stmt->bind_param('sii', $month, $start, $limit);
        $stmt->execute();
        return $stmt->get_result();
    }

    // Pengeluaran Methods
    function count_pengeluaran()
    {
        return $this->koneksi->query("SELECT COUNT(*) FROM pengeluaran")->fetch_row()[0];
    }

    function get_pengeluaran_paginated($start, $limit)
    {
        $stmt = $this->koneksi->prepare("SELECT * FROM pengeluaran ORDER BY tanggal DESC LIMIT ?, ?");
        $stmt->bind_param('ii', $start, $limit);
        $stmt->execute();
        return $stmt->get_result();
    }

    function get_pembayaran_pending()
    {
        $sql = "SELECT p.*, u.nama FROM pembayaran p 
                LEFT JOIN tagihan t ON p.ref_id = t.id_tagihan AND p.ref_type='TAGIHAN'
                LEFT JOIN booking b ON p.ref_id = b.id_booking AND p.ref_type='BOOKING'
                LEFT JOIN kontrak k ON t.id_kontrak = k.id_kontrak
                LEFT JOIN penghuni ph ON k.id_penghuni = ph.id_penghuni
                LEFT JOIN pengguna u ON (ph.id_pengguna = u.id_pengguna OR b.id_pengguna = u.id_pengguna)
                WHERE p.status='PENDING' ORDER BY p.id_pembayaran DESC";
        return $this->koneksi->query($sql);
    }

    function get_cash_flow_report($month_filter)
    {
        // Prepare Statement untuk Union Query yang kompleks ini agak tricky karena parameternya dipakai 2x
        // Jadi kita manual escape saja untuk bulan filter karena formatnya Y-m (cukup aman jika validasi di controller)
        // [SECURITY] Tambahan validasi bulan
        if (!preg_match('/^\d{4}-\d{2}$/', $month_filter)) return false;

        $q_union = "
            SELECT 
                p.waktu_verifikasi as tgl, 
                p.jumlah as nominal, 
                'MASUK' as tipe,
                p.metode as metode,
                CONCAT(
                    COALESCE(u.nama, 'User'), ' - ', 
                    p.ref_type, 
                    IF(km.kode_kamar IS NOT NULL, CONCAT(' (Kamar ', km.kode_kamar, ')'), '')
                ) as deskripsi
            FROM pembayaran p
            LEFT JOIN booking b ON p.ref_id=b.id_booking AND p.ref_type='BOOKING'
            LEFT JOIN tagihan t ON p.ref_id=t.id_tagihan AND p.ref_type='TAGIHAN'
            LEFT JOIN kontrak k ON t.id_kontrak=k.id_kontrak
            LEFT JOIN kamar km ON (b.id_kamar = km.id_kamar OR k.id_kamar = km.id_kamar)
            LEFT JOIN penghuni ph ON k.id_penghuni=ph.id_penghuni
            LEFT JOIN pengguna u ON (ph.id_pengguna=u.id_pengguna OR b.id_pengguna=u.id_pengguna)
            WHERE p.status='DITERIMA' AND DATE_FORMAT(p.waktu_verifikasi, '%Y-%m') = '$month_filter'

            UNION ALL

            SELECT 
                e.tanggal as tgl, 
                e.biaya as nominal, 
                'KELUAR' as tipe,
                'KAS' as metode,
                CONCAT(e.judul, IF(e.deskripsi != '', CONCAT(' - ', e.deskripsi), '')) as deskripsi
            FROM pengeluaran e
            WHERE DATE_FORMAT(e.tanggal, '%Y-%m') = '$month_filter'

            ORDER BY tgl DESC
        ";
        return $this->koneksi->query($q_union);
    }


    function get_statistik_keuangan($bulan, $tahun)
    {
        // [SECURITY] Gunakan Prepared Statement untuk Profit/Loss
        // Pemasukan
        $stmt_in = $this->koneksi->prepare("SELECT SUM(jumlah) FROM pembayaran WHERE status='DITERIMA' AND MONTH(waktu_verifikasi) = ? AND YEAR(waktu_verifikasi) = ?");
        $stmt_in->bind_param('ss', $bulan, $tahun);
        $stmt_in->execute();
        $masuk = $stmt_in->get_result()->fetch_row()[0] ?? 0;

        // Pengeluaran
        $stmt_out = $this->koneksi->prepare("SELECT SUM(biaya) FROM pengeluaran WHERE MONTH(tanggal) = ? AND YEAR(tanggal) = ?");
        $stmt_out->bind_param('ss', $bulan, $tahun);
        $stmt_out->execute();
        $keluar = $stmt_out->get_result()->fetch_row()[0] ?? 0;

        return [
            'omset' => $masuk,
            'keluar' => $keluar,
            'profit' => $masuk - $keluar
        ];
    }

    function get_chart_pendapatan($tahun)
    {
        // [SECURITY] Wajib Prepared Statement karena $tahun input user
        // Query agak kompleks karena loop 1-12 bulan
        $data = [];
        $stmt = $this->koneksi->prepare("SELECT SUM(jumlah) FROM pembayaran WHERE status='DITERIMA' AND MONTH(waktu_verifikasi) = ? AND YEAR(waktu_verifikasi) = ?");

        for ($i = 1; $i <= 12; $i++) {
            $stmt->bind_param('is', $i, $tahun); // i=month integer, s=year string
            $stmt->execute();
            $val = $stmt->get_result()->fetch_row()[0] ?? 0;
            $data[] = $val;
        }
        return $data;
    }

    function get_pending_counts()
    {
        $booking = $this->koneksi->query("SELECT COUNT(*) FROM booking WHERE status='PENDING'")->fetch_row()[0];
        $tagihan = $this->koneksi->query("SELECT COUNT(*) FROM pembayaran WHERE status='PENDING'")->fetch_row()[0];
        return ['booking' => $booking, 'tagihan' => $tagihan];
    }

    function get_booking_terbaru($limit = 5)
    {
        $stmt = $this->koneksi->prepare("SELECT b.*, u.nama, u.no_hp, k.kode_kamar, t.nama_tipe 
                                         FROM booking b 
                                         JOIN pengguna u ON b.id_pengguna=u.id_pengguna 
                                         JOIN kamar k ON b.id_kamar=k.id_kamar 
                                         JOIN tipe_kamar t ON k.id_tipe=t.id_tipe 
                                         WHERE b.status='PENDING' 
                                         ORDER BY b.tanggal_booking DESC LIMIT ?");
        $stmt->bind_param('i', $limit);
        $stmt->execute();
        $res = $stmt->get_result();

        $hasil = [];
        while ($row = $res->fetch_assoc()) {
            $hasil[] = $row;
        }
        return $hasil;
    }

    // --- TENANT HELPERS ---
    function get_user_by_id($id)
    {
        $stmt = $this->koneksi->prepare("SELECT * FROM pengguna WHERE id_pengguna=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    function get_id_penghuni_by_user($id_pengguna)
    {
        // [SECURITY] ID Integer
        $id_pengguna = (int)$id_pengguna;
        $res = $this->koneksi->query("SELECT id_penghuni FROM penghuni WHERE id_pengguna=$id_pengguna");
        return ($res && $res->num_rows > 0) ? $res->fetch_object()->id_penghuni : 0;
    }

    function get_kamar_penghuni_detail($id_penghuni)
    {
        $sql = "SELECT k.*, t.nama_tipe, ko.tanggal_mulai, ko.tanggal_selesai, ko.id_kontrak, ko.status as status_kontrak, k.harga
                FROM kontrak ko 
                JOIN kamar k ON ko.id_kamar = k.id_kamar 
                JOIN tipe_kamar t ON k.id_tipe = t.id_tipe 
                WHERE ko.id_penghuni = ? AND ko.status = 'AKTIF'";
        $stmt = $this->koneksi->prepare($sql);
        $stmt->bind_param('i', $id_penghuni);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    function get_fasilitas_kamar($id_kamar)
    {
        $stmt = $this->koneksi->prepare("SELECT f.nama_fasilitas, f.icon FROM kamar_fasilitas kf JOIN fasilitas_master f ON kf.id_fasilitas=f.id_fasilitas WHERE kf.id_kamar=?");
        $stmt->bind_param('i', $id_kamar);
        $stmt->execute();
        $res = $stmt->get_result();
        $hasil = [];
        while ($row = $res->fetch_assoc()) {
            $hasil[] = $row;
        }
        return $hasil;
    }

    function get_keluhan_by_penghuni($id_penghuni)
    {
        $stmt = $this->koneksi->prepare("SELECT * FROM keluhan WHERE id_penghuni=? ORDER BY dibuat_at DESC");
        $stmt->bind_param('i', $id_penghuni);
        $stmt->execute();
        return $stmt->get_result();
    }

    function insert_keluhan($id_penghuni, $judul, $deskripsi, $prioritas, $foto_path)
    {
        $stmt = $this->koneksi->prepare("INSERT INTO keluhan (id_penghuni, judul, deskripsi, prioritas, status, foto_path) VALUES (?, ?, ?, ?, 'BARU', ?)");
        $stmt->bind_param('issss', $id_penghuni, $judul, $deskripsi, $prioritas, $foto_path);
        return $stmt->execute();
    }

    function get_profil_penghuni($id_pengguna)
    {
        $q = "SELECT u.*, p.alamat, p.pekerjaan, p.emergency_cp, p.foto_profil 
              FROM pengguna u 
              LEFT JOIN penghuni p ON u.id_pengguna = p.id_pengguna 
              WHERE u.id_pengguna = ?";
        $stmt = $this->koneksi->prepare($q);
        $stmt->bind_param('i', $id_pengguna);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    function get_pengumuman_terbaru($limit = 2)
    {
        return $this->koneksi->query("SELECT * FROM pengumuman WHERE is_aktif=1 AND aktif_selesai >= CURDATE() ORDER BY aktif_mulai DESC LIMIT $limit");
    }

    function can_user_book($id_pengguna)
    {
        // 1. Cek Pending Booking
        $stmt = $this->koneksi->prepare("SELECT COUNT(*) FROM booking WHERE id_pengguna=? AND status='PENDING'");
        $stmt->bind_param('i', $id_pengguna);
        $stmt->execute();
        if ($stmt->get_result()->fetch_row()[0] > 0) return false;

        // 2. Cek Active Contract (Tenant)
        // Ambil id_penghuni dulu
        $id_penghuni = $this->get_id_penghuni_by_user($id_pengguna);
        if ($id_penghuni) {
            $stmt2 = $this->koneksi->prepare("SELECT COUNT(*) FROM kontrak WHERE id_penghuni=? AND status='AKTIF'");
            $stmt2->bind_param('i', $id_penghuni);
            $stmt2->execute();
            if ($stmt2->get_result()->fetch_row()[0] > 0) return false;
        }

        return true;
    }
} // <--- Tutup Class Database

// Inisialisasi Objek Global (Biar file lain tinggal pakai $mysqli)
$db = new Database();
$mysqli = $db->koneksi;

// Fungsi Helper Global (Bukan method class, tapi helper biasa)
function pesan_error($url, $pesan)
{
    $sep = (strpos($url, '?') === false) ? '?' : '&';
    // Auto-detect type based on message content
    $type = 'error';
    if (strpos($pesan, '✅') !== false || stripos($pesan, 'berhasil') !== false || stripos($pesan, 'sukses') !== false) {
        $type = 'success';
    }

    $new_url = $url . $sep . "msg=custom&text=" . urlencode($pesan) . "&type=" . $type;
    header("Location: $new_url");
    exit;
}
