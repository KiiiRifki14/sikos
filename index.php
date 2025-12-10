<?php
session_start(); // Memulai sesi untuk menyimpan data pengguna sementara

require 'inc/koneksi.php'; // Menghubungkan file koneksi ke database

$where = []; // Membuat array kosong untuk menampung kondisi WHERE SQL
$params = []; // Membuat array kosong untuk menampung nilai parameter
$types = ''; // Membuat string kosong untuk tipe data parameter (bind_param)

// -- Logika Filter Status --
if (!empty($_GET['status'])) { // Mengecek apakah ada parameter 'status' di URL
  // Validasi: Pastikan nilai status hanya 'TERSEDIA' atau 'TERISI'
  $status = $_GET['status'] === 'TERSEDIA' ? 'TERSEDIA' : ($_GET['status'] === 'TERISI' ? 'TERISI' : null);
  if ($status) { // Jika status valid
    $where[] = "k.status_kamar = ?"; // Tambahkan kondisi ke array WHERE
    $params[] = $status; // Simpan nilai status ke array parameter
    $types .= 's'; // Tambahkan tipe string ('s') ke string types
  }
}

// -- Logika Filter Tipe Kamar --
if (!empty($_GET['tipe'])) { // Mengecek parameter 'tipe'
  $where[] = "k.id_tipe = ?"; // Tambahkan kondisi filter ID tipe
  $params[] = (int)$_GET['tipe']; // Ubah input jadi integer agar aman
  $types .= 'i'; // Tambahkan tipe integer ('i')
}

// -- Logika Filter Harga Maksimal --
if (!empty($_GET['max_harga'])) { // Mengecek parameter 'max_harga'
  $where[] = "k.harga <= ?"; // Filter harga HINGGA maksimal nilai ini
  $params[] = (int)$_GET['max_harga']; // Ubah jadi integer
  $types .= 'i'; // Tipe integer
}

$order_param = $_GET['order'] ?? 'default'; // Ambil parameter order, defaultnya 'default'
// Query dasar untuk pengurutan: Kamar USER TERSEDIA diprioritaskan di atas
$order_sql = "CASE WHEN k.status_kamar = 'TERSEDIA' THEN 1 ELSE 2 END ASC, k.kode_kamar ASC";

// Modifikasi urutan berdasarkan input user
if ($order_param === 'harga_asc') $order_sql = "k.harga ASC"; // Urut harga termurah
elseif ($order_param === 'harga_desc') $order_sql = "k.harga DESC"; // Urut harga termahal
elseif ($order_param === 'terbaru') $order_sql = "k.id_kamar DESC"; // Urut kamar input terakhir

// -- MENYUSUN QUERY UTAMA --
// Pilih data kamar, nama tipe, dan cek apakah sedang ada booking pending
$sql = "SELECT k.*, t.nama_tipe, 
        (SELECT COUNT(*) FROM booking b WHERE b.id_kamar = k.id_kamar AND b.status = 'PENDING') as is_pending
        FROM kamar k 
        JOIN tipe_kamar t ON k.id_tipe=t.id_tipe"; // Join tabel kamar dengan tipe_kamar

// Menggabungkan filter WHERE jika ada
if ($where) $sql .= " WHERE " . implode(" AND ", $where);
// Menambahkan pengurutan dan limit 6 data (Pagination awal)
$sql .= " ORDER BY " . $order_sql . " LIMIT 6";

$stmt = $mysqli->prepare($sql); // Menyiapkan statement SQL (Prepared Statement)
if ($params) $stmt->bind_param($types, ...$params); // Bind parameter jika filter aktif
$stmt->execute(); // Jalankan query
$res = $stmt->get_result(); // Ambil hasil set data

// -- MENGHITUNG TOTAL DATA (UNTUK LOAD MORE) --
$sqlCount = "SELECT COUNT(*) as total FROM kamar k"; // Query hitung total semua kamar
if ($where) $sqlCount .= " WHERE " . implode(" AND ", $where); // Pakai filter yang sama
$stmtCount = $mysqli->prepare($sqlCount); // Siapkan query count
if (!empty($params)) $stmtCount->bind_param($types, ...$params); // Bind param yang sama
$stmtCount->execute(); // Jalankan
$totalKamar = $stmtCount->get_result()->fetch_assoc()['total']; // Ambil angka total
$sisaKamar = $totalKamar - 6; // Hitung sisa kamar yang belum tampil

// Query mengambil semua tipe kamar untuk dropdown filter
$tipeRes = $mysqli->query("SELECT id_tipe, nama_tipe FROM tipe_kamar ORDER BY nama_tipe ASC");
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SIKOS Paadaasih</title>
  <link rel="stylesheet" href="assets/css/app.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>

  <?php include 'components/header.php'; ?>

  <?php
  // -- PENGAMBILAN DATA DINAMIS HALAMAN DEPAN --
  $db = new Database(); // Membuat instance objek Database
  $pengaturan = $db->ambil_pengaturan(); // Mengambil data setting web (WA, footer, dll)
  $fasilitas = $db->get_fasilitas_umum(); // Mengambil list fasilitas dari DB

  // Menyiapkan link WhatsApp. Gunakan default jika kosong.
  $wa_link = "https://wa.me/" . ($pengaturan['no_wa'] ?? '62881011201664');
  ?>
  <section id="beranda" class="hero">
    <div class="hero-content">
      <h1 class="hero-title">Temukan Kos Impian Anda</h1>
      <p class="hero-text">
        Sistem pengelolaan kos yang mudah, aman, dan terpercaya untuk kenyamanan hidup Anda di Cimahi.
      </p>
      <div class="flex justify-center gap-4">
        <a href="#kamar" class="btn btn-primary" style="padding: 12px 30px; font-size: 16px;">
          <i class="fa-solid fa-magnifying-glass"></i> Cari Kamar
        </a>
        <a href="<?= $wa_link ?>" target="_blank" class="btn btn-secondary" style="padding: 12px 30px; font-size: 16px;">
          <i class="fa-brands fa-whatsapp"></i> Hubungi Kami
        </a>
      </div>
    </div>
  </section>

  <section id="kamar" class="section">
    <div class="flex justify-between items-end mb-8">
      <div>
        <h2 class="section-title">Kamar Tersedia</h2>
        <p class="section-subtitle">Pilihan kamar terbaik untuk kenyamanan Anda</p>
      </div>
      <!-- Filter Toggle (Mobile) -->
      <button class="md:hidden btn btn-secondary" onclick="document.getElementById('filter-box').classList.toggle('hidden')">
        <i class="fa-solid fa-filter"></i> Filter
      </button>
    </div>

    <!-- FILTER BOX -->
    <div id="filter-box" class="filter-box hidden md:block animate-fade-up">
      <form id="filterForm" class="filter-grid">
        <!-- Status Filter -->
        <div class="form-group">
          <label class="form-label">Status</label>
          <select name="status" class="form-input">
            <option value="">Semua Status</option>
            <option value="TERSEDIA">Tersedia</option>
            <option value="TERISI">Terisi</option>
          </select>
        </div>
        <!-- Tipe Filter -->
        <div class="form-group">
          <label class="form-label">Tipe Kamar</label>
          <select name="tipe" class="form-input">
            <option value="">Semua Tipe</option>
            <?php
            // Query langsung untuk opsi filter tipe
            $res_tipe = $db->koneksi->query("SELECT * FROM tipe_kamar"); // Ambil semua tipe
            while ($tipe = $res_tipe->fetch_assoc()): // Loop setiap baris data
            ?>
              <option value="<?= $tipe['id_tipe'] ?>"><?= htmlspecialchars($tipe['nama_tipe']) ?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <!-- Harga Filter -->
        <div class="form-group">
          <label class="form-label">Maksimal Harga</label>
          <select name="max_harga" class="form-input">
            <option value="">Semua Harga</option>
            <option value="500000">Di bawah 500rb</option>
            <option value="1000000">Di bawah 1jt</option>
            <option value="2000000">Di bawah 2jt</option>
          </select>
        </div>
        <!-- Button Cari -->
        <div class="form-group">
          <button type="button" onclick="loadKamar(true)" class="btn btn-primary w-full" style="height: 48px; margin-top: auto;">
            <i class="fa-solid fa-search"></i> Terapkan
          </button>
        </div>
      </form>
    </div>

    <!-- ROOM LIST CONTAINER -->
    <div id="kamar-container" class="grid-rooms">
      <?php
      // -- LOGIC PHP UTAMA: MENAMPILKAN CARD KAMAR SECARA MANUAL (SERVER SIDE) --
      // Query ini mirip dengan yang di atas, tapi dijalankan lagi untuk display awal
      $sql_awal = "SELECT k.*, t.nama_tipe,
                     (SELECT COUNT(*) FROM booking b WHERE b.id_kamar = k.id_kamar AND b.status = 'PENDING') as is_pending 
                     FROM kamar k 
                     JOIN tipe_kamar t ON k.id_tipe=t.id_tipe
                     WHERE 1=1
                     ORDER BY CASE WHEN k.status_kamar = 'TERSEDIA' THEN 1 ELSE 2 END ASC, k.kode_kamar ASC
                     LIMIT 6"; // Batas 6 kamar pertama
      $res_awal = $db->koneksi->query($sql_awal);

      // Cek apakah ada data yang ditemukan
      if ($res_awal && $res_awal->num_rows > 0) {
        // Loop data dan render setiap item menggunakan komponen 'card_kamar.php'
        while ($row = $res_awal->fetch_assoc()) {
          include 'components/card_kamar.php'; // Include template kartu
        }
      } else {
        // Tampilkan pesan jika kosong
        echo '<p class="col-span-3 text-center text-muted">Belum ada data kamar.</p>';
      }
      ?>
    </div>

    <!-- LOAD MORE BUTTON -->
    <div id="load-more-wrapper" class="text-center mt-12">
      <button id="btn-load-more" onclick="loadMore()" class="btn btn-secondary px-8 py-3 rounded-full">
        Lihat Lebih Banyak <i class="fa-solid fa-chevron-down ml-2"></i>
      </button>
    </div>
  </section>

  <section id="fasilitas" class="section" style="background: #f8fafc; border-top:1px solid var(--border); border-bottom:1px solid var(--border);">
    <div class="text-center mb-12">
      <h2 class="section-title">Fasilitas Bangunan</h2>
      <p class="section-subtitle">Kenyamanan Anda adalah prioritas kami</p>
    </div>

    <div class="grid-rooms">
      <?php foreach ($fasilitas as $f): // Loop array fasilitas yang diambil di atas 
      ?>
        <div class="card-white text-center" style="border: 1px solid var(--border); transition: transform 0.3s; margin-bottom:0;">
          <div style="background: var(--primary-light); width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px auto;">
            <i class="fa-solid <?= $f['icon'] ?>" style="font-size:32px; color:var(--primary);"></i>
          </div>
          <!-- Tampilkan Judul dengan escaping htmlspecialchars untuk keamanan XSS -->
          <h3 class="font-bold text-lg mb-2"><?= htmlspecialchars($f['judul']) ?></h3>
          <p class="text-sm text-muted"><?= htmlspecialchars($f['deskripsi']) ?></p>
        </div>
      <?php endforeach; ?>

      <?php if (empty($fasilitas)): ?>
        <p class="text-center text-muted col-span-3">Belum ada data fasilitas.</p>
      <?php endif; ?>
    </div>
  </section>

  <?php include 'components/footer.php'; ?>

  <script>
    /* Logic Filter & Load More (JavaScript) */
    let currentOffset = 6; // Variabel penanda jumlah data yg sudah tampil (mulai dari 6)
    const limit = 6; // Jumlah data per load

    // Fungsi utama memuat kamar (baik filter maupun load more)
    function loadKamar(reset = false) {
      const form = document.getElementById('filterForm'); // Ambil elemen form
      const formData = new FormData(form); // Ambil data input form
      const params = new URLSearchParams(formData); // Konversi ke URL parameters

      // Ambil elemen UI
      const spinner = document.getElementById('loading-spinner');
      const btn = document.getElementById('btn-load-more');
      const container = document.getElementById('kamar-container');

      // Jika mode reset (tombol Terapkan Filter diklik)
      if (reset) {
        currentOffset = 0; // Kembalikan offset ke 0
        container.innerHTML = ''; // Kosongkan container
        // Reset tombol load more ke kondisi awal
        document.getElementById('load-more-wrapper').innerHTML = `
                <button id="btn-load-more" onclick="loadMore()" class="btn btn-secondary px-8 py-3 rounded-full">
                    Lihat Lebih Banyak <i class="fa-solid fa-chevron-down ml-2"></i>
                </button>`;
      }

      // Set parameter offset untuk dikirim ke server
      params.set('offset', currentOffset);

      // Tampilkan loading spinner & sembunyikan tombol
      // Request AJAX menggunakan Fetch API ke ajax_kamar.php
      fetch(`ajax_kamar.php?${params.toString()}`)
        .then(response => response.text()) // Konversi response ke text HTML
        .then(data => {

          if (data.trim().length > 0) {
            // Jika ada data, masukkan ke container (append)
            container.insertAdjacentHTML('beforeend', data);
            currentOffset += limit; // Update offset untuk load berikutnya
          } else {
            // Jika data habis / tidak ada
            if (reset) {
              container.innerHTML = '<p class="col-span-3 text-center text-muted">Tidak ada kamar yang cocok.</p>';
            }
            // Hilangkan tombol Load More
            if (document.getElementById('load-more-wrapper')) {
              document.getElementById('load-more-wrapper').innerHTML = '<p class="text-muted text-sm">Semua kamar sudah ditampilkan.</p>';
            }
          }
        })
        .catch(err => {
          console.error(err); // Log error console
          alert('Gagal memuat data.'); // Alert user
        });
    }

    // Fungsi Wrapper untuk tombol Load More
    function loadMore() {
      loadKamar(false); // Panggil loadKamar tanpa reset (append mode)
    }
  </script>
</body>

</html>